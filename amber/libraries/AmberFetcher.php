<?php

//TODO: Namespace

interface iAmberFetcher {
  public function fetch($url);
}

class AmberFetcher implements iAmberFetcher {

  /**
   * @param $storage AmberStorage that will be used to save the item
   */
  function __construct(iAmberStorage $storage, array $options) {
    $this->storage = $storage;
    $this->assetHelper = new AmberAssetHelper($storage);
    $this->maxFileSize = isset($options['amber_max_file']) ? $options['amber_max_file'] : 1000;
    $this->headerText = isset($options['header_text']) ? $options['header_text'] : "This is a cached page";
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
      $body = $this->assetHelper->insert_banner($body, $this->headerText);
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
    if ($data['headers']['Content-Type'] && !empty($this->excludedContentTypes)) {
      $content_type = $data['headers']['Content-Type'];
      foreach ($this->excludedContentTypes as $exclude) {
        if (trim($exclude) && (strpos(strtolower($content_type), trim($exclude)) !== FALSE)) {
          $reason = "Content type not allowed";
          return FALSE;  
        }
      }      
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
    return !in_array($val,array("link"));
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

  public function insert_banner($body, $text) {
    $banner = <<<EOD
<div style="position:fixed;top:0;left:0;width:100%;height:30px;z-index:2147483647;background-color:rgba(0,0,0,0.75) !important;color:white !important;text-align:center !important;font:bold 18px/30px Arial, sans-serif !important;border-radius:0 !important;margin:0 !important;max-width:100% !important;">${text}</div>
EOD;
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

class AmberNetworkUtils {

  private static function curl_installed() {
    return in_array("curl", get_loaded_extensions());
  }

  public static function full_relative_path($base, $url) {
    $dict = parse_url($url);
    if (isset($dict['path'])) {
      $result = AmberNetworkUtils::clean_up_path(join('/',array($base,$dict['path'])));
    } else {
      $result = $base;  
    }
    $result .= isset($dict['query']) ? '?' . $dict['query'] : '';
    return $result;
  }

  /**
   * Remove '../' elements from the path. This is only necessary because sometimes the
   * relative paths go above the 'root' level, which browsers handle by ignoring any
   * '..' elements which go above the root. We need to replicate this behavior 
   * because many pages in the wild depend on it.
   * @pararm $path_string string with the path component of the URL
   */
  public static function clean_up_path($path_string) {
    $path = explode("/",$path_string);
    $done = false;
    $i = 0;
    while ($i < count($path)) {
      if ($path[$i] == "..") {
        if ($i == 0) {
          unset($path[$i]);          
        } else if ($path[$i-1] != "..") {
          unset($path[$i]);          
          unset($path[$i-1]);       
          $i -= 1;   
        } else {
          $i += 1;
        }
        $path = array_values($path);
      } else {
        $i += 1;
      }
    }
    return join("/",$path);
  }

  /**
   * Transform raw HTTP headers into a dictionary
   * @param $raw_headers string of headers from the HTTP response header
   * @return array
   */
  public static function extract_headers($raw_headers) {
    $headers = array();
      if ($raw_headers) {
      foreach (explode(PHP_EOL,$raw_headers) as $line) {
        $header = explode(":",$line);
        if (count($header) == 2) {
          if (strtolower($header[0]) == "content-type") {
            $header[0] = "Content-Type"; /* Fix up case if necessary */
          }
          $headers[$header[0]] = trim($header[1]);
        }
      }
    }
    return $headers;
  }

  /** 
   * Given a mime-type, determine if it would be rendered as HTML by a browser.
   * If this is true, we will attempt to parse it for related assets (css, images, etc.)
   */
  public static function is_html_mime_type($mime) {
    return (
      (strpos(strtolower($mime),"text/html") !== FALSE) ||
      (strpos(strtolower($mime),"application/xhtml+xml") !== FALSE)
      );
  }


  /**
   * Open one or more URL, and return an array of arrays with dictionary of header information and a stream to the contents of the URL
   * @param $urls array of strings of resource to download
   * @return array of dictionaries of header information and a stream to the contents of the URL
   */
  public static function open_multi_url($urls, $additional_options = array()) {
    if (AmberNetworkUtils::curl_installed()) {
      $result = array();
      try {
        $options = array(
          CURLOPT_FAILONERROR => TRUE,      /* Don't ignore HTTP errors */
          CURLOPT_FOLLOWLOCATION => TRUE,   /* Follow redirects */
          CURLOPT_MAXREDIRS => 10,          /* No more than 10 redirects */
          CURLOPT_CONNECTTIMEOUT => 5,     /* 10 second connection timeout */
          CURLOPT_TIMEOUT => 10,            /* 30 second timeout for any CURL function */
          CURLOPT_RETURNTRANSFER => 1,      /* Return the output as a string */
          CURLOPT_HEADER => TRUE,           /* Return header information as part of the file */
          CURLOPT_USERAGENT => "Amber 1.0/compatible",
          CURLOPT_ENCODING => '',           /* Handle compressed data */
          // CURLOPT_VERBOSE => true,
          // CURLOPT_PROXY => 'localhost:8889',
          // CURLOPT_PROXYTYPE => CURLPROXY_SOCKS5,
        );

        $multi = curl_multi_init();
        $channels = array();

        foreach ($urls as $url) {
          if (($ch = curl_init($url)) === FALSE) {
            error_log(join(":", array(__FILE__, __METHOD__, $url, "CURL init error")));
            return FALSE;
          }
          if (curl_setopt_array($ch, $additional_options + $options) === FALSE) {
            throw new RuntimeException(join(":", array(__FILE__, __METHOD__, "Error setting CURL options", $url, curl_error($ch))));
          }
          curl_multi_add_handle($multi, $ch);
          $channels[$url] = $ch;
        }

        /* While we're still active, execute curl over all the channels */
        $active = null;
        do {
          $mrc = curl_multi_exec($multi, $active);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);

        while ($active && $mrc == CURLM_OK) {
            curl_multi_select($multi);
            do {
                $mrc = curl_multi_exec($multi, $active);
            } while ($mrc == CURLM_CALL_MULTI_PERFORM);
        }

        /* Now we should have all of the data */
        foreach ($channels as $url => $channel) {

          /* Get the CURL result */
          $data = curl_multi_getcontent($channel);
          $response_info = curl_getinfo($channel);

          /* Split into header and body */
          $header_size = $response_info['header_size'];
          $header = substr($data, 0, $header_size-1);
          $body = substr($data, $header_size);
          $headers = AmberNetworkUtils::extract_headers($header);
          $result[$url] = array("headers" => $headers, "body" => $body, "info" => $response_info);
          curl_multi_remove_handle($multi, $channel); 
        }
        curl_multi_close($multi);
        return $result;
      } catch (RuntimeException $e) {
        error_log($e->getMessage());
        curl_multi_close($multi);
        return FALSE;
      }

    } else {
      // TODO: If curl is not installed, see if remote file opening is enabled, and fall back to that method
      error_log(join(":", array(__FILE__, __METHOD__, "CURL not installed")));
      return FALSE;
    }
  }

  /**
   * Open a URL, and return an array with dictionary of header information and a stream to the contents of the URL
   * @param $url string of resource to download
   * @return array dictionary of header information and a stream to the contents of the URL
   */
  public static function open_url($url, $additional_options = array()) {
    $result = AmberNetworkUtils::open_multi_url(array($url), $additional_options);    
    if (count($result) == 1) {
      return array_pop($result);
    } else {
      return FALSE;
    }
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
    if (!(preg_match("/User.*/", $robots) && preg_match("/Disallow:.*/", $robots)))
      return true;
    require_once("robotstxtparser.php");
    $parser = new robotstxtparser($robots);
    return !$parser->isDisallowed($url,"Amber 1.0/compatible");
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