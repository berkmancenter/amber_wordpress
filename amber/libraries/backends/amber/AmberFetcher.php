<?php

require_once dirname( __FILE__ ) . '/../../AmberInterfaces.php';
require_once dirname( __FILE__ ) . '/../../AmberNetworkUtils.php';

class AmberFetcher implements iAmberFetcher {

  /**
   * @param $storage AmberStorage that will be used to save the item
   */
  function __construct(iAmberStorage $storage, array $options) {
    $this->storage = $storage;
    $this->assetHelper = new AmberAssetHelper($storage);
    $this->maxFileSize = isset($options['amber_max_file']) ? $options['amber_max_file'] : 1000;
    $this->headerText = isset($options['header_text']) ? $options['header_text'] : "You are viewing a snapshot of <a style='font-weight:bold !important; color:white !important' href='{{url}}'>{{url}}</a> created on {{date}}";
    $this->excludedContentTypes = isset($options['amber_excluded_formats']) ? $options['amber_excluded_formats'] : array();
  }

  /**
   * Fetch the URL and associated assets and pass it on to the designated Storage service
   * @param $url
   * @return 
   */
  public function fetch($url) {

    if (!$url) {
      throw new RuntimeException("Empty URL");
    }

    // Check the robots.txt
    if (!AmberRobots::robots_allowed($url)) {
      throw new RuntimeException("Blocked by robots.txt");
    }

    // Send a GET request
    $root_item = AmberNetworkUtils::open_url($url);

    // Decide whether the item should be cached
    if (!$this->cacheable_item($root_item, $reason)) {
      throw new RuntimeException($reason);
    }

    $size = $root_item['info']['size_download'];
    if ($size == 0) {
      throw new RuntimeException("Empty document"); 
    }
    // Get other assets
    if (isset($root_item['headers']['Content-Type']) &&
        ($content_type = $root_item['headers']['Content-Type']) &&
        AmberNetworkUtils::is_html_mime_type($content_type)) {

      $body = $root_item['body'];

      $asset_paths = $this->assetHelper->extract_assets($body);
      /* Use the url of the document we end up downloading as a reference point for
         relative asset references, since we may have been redirected from the one
         we originally requested. */ 
      $assets = $this->assetHelper->expand_asset_references($root_item['info']['url'], $asset_paths, 
                                                            $this->assetHelper->extract_base_tag($body));
      $assets = $this->download_assets($assets, $root_item['info']['url']); 
      $assets = $this->download_css_assets_recursive($assets, $root_item['info']['url'], $size);
      $body = $this->assetHelper->rewrite_links($body, $assets);
      $body = $this->assetHelper->insert_banner($body, $this->headerText, array("url" => $url, "date" => date('Y/m/d')));
      $root_item['body'] = $body;

      /* Check total size of the file combined with its assets */
      if ($size > ($this->maxFileSize * 1024)) {
        throw new RuntimeException("File size of document + assets too large");
      }
    }

    if ($this->storage && $root_item) {
      $result = $this->storage->save($url, $root_item['body'], $root_item['headers'], isset($assets) ? $assets : array());
      if (!$result) {
        throw new RuntimeException("Could not save cache");  
      }
      $storage_metadata = $this->storage->get_metadata($url);
      if (!$storage_metadata || empty($storage_metadata)) {
        throw new RuntimeException("Could not retrieve metadata");   
      }
      //TODO: If cannot retrieve storage metadata, or id/url/cache not populated (perhaps due to permissions errors
      //      in saving the cache), fail more gracefully instead of with errors because the keys are not set
      return array (
        'id' => $storage_metadata['id'],
        'url' => $storage_metadata['url'],
        'type' => isset($storage_metadata['type']) ? $storage_metadata['type'] : 'application/octet-stream',
        'date' => strtotime($storage_metadata['cache']['amber']['date']),
        'location' => $storage_metadata['cache']['amber']['location'],
        'size' => $size,
        'provider' => $this->storage->provider_id(),
        'provider_id' => $storage_metadata['id'],
      );
    } else {
      throw new RuntimeException("Content empty or could not save to disk");
    }
  }

  /**
   * Given a list of assets (CSS, images, javascript, etc.) to download, also download any assets referenced
   * from any CSS files in the list. For example, background images, or incldued CSS files. 
   * Repeat until all referenced CSS files have been processed
   * @param $assets array of assets to scan for referenced assets
   * @param $base string with the URL of the document that included the assets (needed for relative paths)
   * @param $size integer with the total size of all downloaded assets
   * @param $max_depth integer with the maximum number of times to recurse to find additional assets
   */
  private function download_css_assets_recursive(&$assets, $base, &$size, $max_depth = 5) {
    if ($max_depth <= 0) {
      return $assets;
    }
    $all_css_assets = array();
    $url = $base;

    foreach ($assets as &$value) {
      $size += $value['info']['size_download'];
      /* For CSS assets, parse the CSS file to find and download any referenced images, and rewrite the CSS file to use them */

      if (isset($value['headers']['Content-Type']) && (strpos($value['headers']['Content-Type'],'text/css') !== FALSE)) {
        $css_body = $value['body'];
        $css_asset_paths = $this->assetHelper->extract_css_assets($css_body);
        $css_assets = $this->assetHelper->expand_asset_references($value['url'], $css_asset_paths);
        $css_assets = $this->download_assets($css_assets, $url);

        if (!empty($css_assets)) {
          $css_assets = $this->download_css_assets_recursive($css_assets,$value['url'],$size,$max_depth - 1);  
        } 

        $all_css_assets = array_merge($all_css_assets, $css_assets);
        $css_body = $this->assetHelper->rewrite_links($css_body, $css_assets, $value['url']);
        $value['body'] = $css_body;
      }
    }
    $assets = array_merge($assets, $all_css_assets);
    return $assets;
  }

  /** 
   * Tell if a file should be cached or not
   */
  private function cacheable_item($data, &$reason) {    
    $reason = "";
    if ($data['info']['size_download'] > ($this->maxFileSize * 1024)) {
      $reason = "File size too large";
      return FALSE;
    }
    if (isset($data['headers']['Content-Type']) && !empty($this->excludedContentTypes)) {
      $content_type = $data['headers']['Content-Type'];
      foreach ($this->excludedContentTypes as $exclude) {
        if (trim($exclude) && (strpos(strtolower($content_type), trim($exclude)) !== FALSE)) {
          $reason = "Content type not allowed";
          return FALSE;  
        }
      }      
    }
    if (AmberNetworkUtils::find_meta_no_archive($data['body'])) {
      $reason = "noarchive/noindex meta tag found";
      return FALSE;
    }
    return TRUE;
  }

  /**
   * Download a list of assets (img,css,js) that are used by a page
   * @param $assets array of strings of relative paths of assets
   * @param $url string path to the page from which the assets are referenced
   * @return array where keys are asset paths, and values are body/header dictionaries returned from open_url, along with
   *         another key containing the absolute path to the asset
   */
  private function download_assets($assets, $url = '') {
    $result = array();
    /* Commented out code is the one-asset-at-a-time version */
    // foreach ($assets as $key => $asset) {
    //   $f = AmberNetworkUtils::open_url($asset['url'], array(CURLOPT_REFERER => $url));
    //   if ($f) {
    //     $result[$key] = array_merge($f,$asset);
    //   }
    // }
    $urls = array();
    $keys = array();
    foreach ($assets as $key => $asset) {
      $urls[] = $asset['url'];
      $keys[$asset['url']] = $key;
    }
    $response = AmberNetworkUtils::open_multi_url($urls, array(CURLOPT_REFERER => $url));
    foreach ($assets as $key => $asset) {
      if (is_array($response[$asset['url']])) {
        $result[$key] = array_merge($response[$asset['url']],$asset);        
      }
    }
    return $result;
  }

}

class AmberAssetHelper {

  function __construct(iAmberStorage $storage) {
    $this->storage = $storage;
  }

  /**
   * Extract a list of assets to be downloaded to go along with an HTML file
   * @param $file
   */
  public function extract_assets($body, $dom = null) {
    if ($body) {
      if (!$dom) {
        $dom = $this->get_dom($body);        
      }

      $refs = $this->extract_dom_tag_attributes($dom, 'img', 'src');
      $refs = array_merge($refs,$this->extract_dom_tag_attributes($dom, 'script', 'src'));
      $refs = array_merge($refs,$this->extract_dom_tag_attributes($dom, 'input', 'src'));
      $refs = array_merge($refs,$this->extract_dom_tag_attributes($dom, 'embed', 'src'));
      $refs = array_merge($refs,$this->extract_dom_link_references($dom));
      $refs = array_merge($refs,$this->extract_dom_style_references($dom));
      $refs = array_merge($refs,$this->extract_css_assets($body));
      $refs = array_filter($refs, array($this, "filter_css_asset_names"));
      return $refs;
    } else {
      return array();
    }
  }

  public function get_dom($body) {
    $dom = new DOMDocument;
    $old_setting = libxml_use_internal_errors(true);
    $dom->loadHTML($body);
    libxml_clear_errors();
    libxml_use_internal_errors($old_setting);
    return $dom;    
  }

  public function extract_css_assets($body) {
    $refs = $this->extract_css_assets_urls($body);
    $refs = array_merge($refs,$this->extract_css_asset_imports($body));
    $refs = array_filter($refs, array($this, "filter_css_asset_names"));
    return $refs;    
  }

  public function extract_css_assets_urls($body) {
    if ($body) {
      $re = '/url\(\s*["\']?([^\v()<>{}\[\]"\']+)[\'"]?\s*\)/';
      $count = preg_match_all($re, $body, $matches);
      return $count ? array_unique($matches[1]) : array();
    } else {
      return array();
    }
  }

  public function extract_css_asset_imports($body) {
    if ($body) {
      $re = '/@import\s*["\']?([^\v()<>{}\[\]"\']+)[\'"]?\s*/';
      $count = preg_match_all($re, $body, $matches);
      return $count ? array_unique($matches[1]) : array();
    } else {
      return array();
    }
  }

  /* The CSS asset detection function relies on regular expressions, not on
     parsing the DOM. Therefore, when called on HTML files that contain embedded
     javscript, there can be false positives where a function signature looks
     like a file reference from a CSS file. For example: given 
     "function url(link)", the text 'link' gets incorrectly detected as an asset. This
     shouldn't be a problem, since the asset won't get downloaded, UNLESS the
     site doesn't serve a 404 when it's reqested. (bad! bad!). In such a case, 
     we replace all instances of "link" in the HTML doc with the asset path, 
     leading to predictably messy results.

     Therefore, this function, which filters out some 'known bad' asset names, 
     those that if applied through search-replace to the HTML document would
     cause problems. This is a partial solution to a broader problem, which is
     that our current search-replace logic is vulnerable to collisions between 
     asset paths and HTML text that doesn't represent an asset path. A more 
     complete solution could involve smarter regexes, or using DOM parsing logic
     as part of the search-replace process.
  */
  public function filter_css_asset_names($val) {
    return !in_array($val,array("link","/"));
  }

  /**
   * Given a page URL and a list of assets referenced from that page, return an array list of absolute URIs
   * to each of the assets keyed by the path used to reference it. 
   * @param $page_url
   * @param $assets
   * @param $html_base string with the contents of the <base> tag from the page, if exists
   */
  public function expand_asset_references($page_url, $assets, $html_base = "") {
    $result = array();
    $p = parse_url($page_url);
    if ($p) {
      $path_array = explode('/',isset($p['path']) ? $p['path'] : "");
      array_pop($path_array);
      $server = $p['scheme'] . "://" . $p['host'] . (isset($p['port']) ? ":" . $p['port'] : '');
      $page_url = $server . join('/',$path_array);
      foreach ($assets as $asset) {
        $asset_copy = $asset;
        if (strpos($asset,"//") === 0) {
          /* Ensure that every URL has a scheme. Must be done before running parse_url due to bug in PHP < 5.4.7 */
          /* Workaround for bug in parse_url: http://us2.php.net/parse_url#refsect1-function.parse-url-changelog */
          $asset_copy = "${p['scheme']}:${asset_copy}";
        }
        /* Skip data assets that don't reference external resources */
        if (strpos($asset,";base64") !== FALSE) {
          continue;
        }
        $parsed_asset_copy = parse_url($asset_copy);
        $asset_path = $asset_copy;
        if ($parsed_asset_copy) {
          if (!isset($parsed_asset_copy['host'])) {          
            if (!($asset_path && $asset_path[0] == '/')) {
              /* Relative path */
              if ($html_base) {
                $result[$asset]['url'] = $html_base . $asset_path;
                continue;
              } else {
                $asset_path = AmberNetworkUtils::full_relative_path(join('/',$path_array), $asset_path);
              }
            }
            $asset_path = preg_replace("/^\\//","", $asset_path); /* Remove leading '/' */
            $asset_path = join('/',array($server, $asset_path));            
          }
          $result[$asset]['url'] = $asset_path;
        }
      }
    }
    return $result;
  }

  /**
   * Rewrite all asset links in an HTML document to point to a file in the asset directory, with a hash of the URL
   * as the filename, with the extension added
   * @param $body string HTML document
   * @param array $assets keyed by the url as it appears in the document, with the absolute URL as the value
   * @param $relative_to string the URL from which the asset's relative path is in relation to
   * @return mixed
   */
  public function rewrite_links($body, array $assets, $relative_to = '') {
    $result = $body;
    if ($body && !empty($assets)) {
      $base = "assets";
      if ($relative_to) {
          $base = '../' . $base;
      }
      foreach ($assets as $key => $asset) {
        /* Don't rewrite a link which points to an asset that we weren't able to fetch. 
           That could indicate that we've flagged something that's not actually a link. */
        if (!empty($asset['body'])) {
          $p = join("/",array($base, $this->storage->build_asset_path($asset)));
          $result = str_replace($key,$p,$result,$count);
          if ($count == 0) {
            /* Try again if there were no matches, since the $key made have had its HTML
               special characters decoded when extracted by parsing the DOM */
            $result = str_replace(htmlspecialchars($key),$p,$result,$count);
          }
        }
      }
    }
    $result = $this->rewrite_base_tag($result);
    // $result = $this->insert_breakout_buster($result);
    return $result;
  }

  /** 
   * Rewrite the "<base href='foo'/>" tag in the header if it exists. This tag sets the base URL from which 
   * relative URLs are relative to, which gives us problems if it refers back to the original site.
   **/ 
  public function rewrite_base_tag($body) {
    $body = preg_replace('/<base\s+href=[\'"]\S+[\'"]\s*\/?>/','',$body,1);
    return $body;
  }

  public function insert_breakout_buster($body)
  {
    $script = <<<EOD
<script type="text/javascript">window.onbeforeunload = function(e) { return "This page is trying to beat it"; }; window.onload = function() { window.onbeforeunload=null; }</script>
EOD;
    $result = str_ireplace("<head>", "<head>$script", $body);
    return $result;
  }

  public function insert_banner($body, $text, $replace_params) {

    $banner = <<<EOD

<div style="position:fixed;top:0;left:0;width:100%;height:45px;z-index:2147483647;background-color:rgba(253,147,38,0.90) !important;color:black !important;text-align:right !important;font:normal 12px/45px Arial, sans-serif !important;border-radius:0 !important;margin:0 !important;max-width:100% !important;background-repeat: no-repeat !important;background-position: 15px center !important;
background-image: url(data:image/png;base64,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);
"><span style="padding-right:15px;">${text}</span></div>
EOD;
    foreach ($replace_params as $key => $value) {
      $banner = str_replace("{{{$key}}}", $value, $banner);
    }
    $close_body_tag = "</body>";
    /* We want to replace only the LAST instance of </body>. It's possible to have multiple
       </body> tags (for example, in an inline iframe) */
    $pos = strripos($body, $close_body_tag);
    if ($pos !== FALSE) {
      $result = substr_replace($body, "${banner}${close_body_tag}", $pos, strlen($close_body_tag));
    } else {
      $result = $body . $banner;
    }
    return $result;
  }

  /**
   * Extract references to external files that use an @import directive in  <style> tag
   * @param $dom
   * @return array
   */
  private function extract_dom_style_references($dom) {
    $attributes = array();
    foreach ($dom->getElementsByTagName('style') as $t) {
      if (preg_match("/@import\s*['\"](.*)['\"]/",$t->nodeValue,$matches)) {
        $attributes[] = $matches[1];
      }
    }
    return $attributes;
  }

  private function extract_dom_tag_attributes($dom, $tag, $attribute) {
    $attributes = array();
    foreach ($dom->getElementsByTagName($tag) as $t) {
      if ($t->hasAttribute($attribute)) {
        $a = trim($t->getAttribute($attribute));
        /* Ignore data: URIs */
        if (strpos($a,'data:') !== 0) {
          $attributes[] = $a;
        }
      }
    }
    return $attributes;
  }

  private function extract_dom_link_references($dom) {
    $attributes = array();
    foreach ($dom->getElementsByTagName('link') as $t) {
      if ($t->hasAttribute('rel') && (strtolower($t->getAttribute('rel')) == 'stylesheet')) {
        $attributes[] = trim($t->getAttribute('href'));
      }
    }
    return $attributes;
  }

  public function extract_base_tag($body, $dom="") {
    $attribute = "";
    if ($body) {
      if (!$dom) {
        $dom = $this->get_dom($body);        
      }
      foreach ($dom->getElementsByTagName('base') as $t) {
        if ($t->hasAttribute('href')) {
          $attribute = trim($t->getAttribute('href'));
        }
      }
    }
    return $attribute;
  }

}



class AmberRobots {
  /**
   * Is the URL allowed by the robots.txt file.
   * @param $robots
   * @param $url
   * @return bool
   */
  public static function url_permitted($robots, $url) { 
    /* Sanity check to ensure that this actually a robots.txt file */
    if ((stripos($robots, "user-agent") === FALSE) || (stripos($robots, "disallow") === FALSE))
      return true;
    require_once("robotstxtparser.php");
    $parser = new robotstxtparser($robots);
    return (!$parser->isDisallowed($url,"amber") || $parser->isAllowed($url,"amber"));
  }

  /**
   * Find out if the access to the given URL is permitted by the robots.txt
   * @param $url
   * @return bool
   */
  public static function robots_allowed($url) {
    $p = parse_url($url);
    $p['path'] = "robots.txt";
    $robots_url = $p['scheme'] . "://" . $p['host'] . (isset($p['port']) ? ":" . $p['port'] : '') . '/robots.txt';
    $data = AmberNetworkUtils::open_url($robots_url, array(CURLOPT_FAILONERROR => FALSE));
    if (isset($data['info']['http_code']) && ($data['info']['http_code'] == 200)) {
      $body = $data['body'];
      return (!$body || AmberRobots::url_permitted($body, $url));
    } 
    return true;
  }

}
?>
