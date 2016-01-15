<?php

class AmberNetworkUtils {

  private static function curl_installed() {
    return in_array("curl", get_loaded_extensions());
  }

  private static function curl_redirects_allowed() {
    return !ini_get('open_basedir') && filter_var(ini_get('safe_mode'), FILTER_VALIDATE_BOOLEAN) === false;
  }

  private static function get_user_agent_string() {
    /* In the future, we could increment this automatically, but version number is not currently
       included in the code as part of our build process. */
    $version = "1.0";     
    $hostname = gethostname();
    $result = "Amber/${version} (+http://${hostname} http://amberlink.org/fetcher)";
    return $result;
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
        $header = explode(":",$line, 2);
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
   * @return array of dictionaries of header information and the contents of the URL
   */
  public static function open_multi_url($urls, $additional_options = array()) {
    if (AmberNetworkUtils::curl_installed()) {
      $result = array();
      try {
        $options = array(
          CURLOPT_FAILONERROR => TRUE,      /* Don't ignore HTTP errors */
                                            /* Follow redirects? */
          CURLOPT_FOLLOWLOCATION => AmberNetworkUtils::curl_redirects_allowed(),   
          CURLOPT_MAXREDIRS => 10,          /* No more than 10 redirects */
          CURLOPT_CONNECTTIMEOUT => 5,      /* Connection timeout */
          CURLOPT_TIMEOUT => 10,            /* Timeout for any CURL function */
          CURLOPT_RETURNTRANSFER => 1,      /* Return the output as a string */
          CURLOPT_HEADER => TRUE,           /* Return header information as part of the file */
          CURLOPT_USERAGENT => AmberNetworkUtils::get_user_agent_string(),
          CURLOPT_ENCODING => '',           /* Handle compressed data */
          CURLINFO_HEADER_OUT => 1,           /* Preserve outgoing header information */
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

        /* It's possible that one or more of these responses may require a redirect
           that hasn't yet been followed. Some cases where this could happen:
           - The webserver has safe_mode or open_basedir set, so we couldn't set CURLOPT_FOLLOWLOCATION
           - The redirect is triggered by a META tag in the HTML
           - The redirect is triggered by Javascript (We do NOT handle this case)
           For the first two cases, which we can handle, we find URLs that still need redirection,
           and fetch them. */
        $redirects_required = AmberNetworkUtils::find_urls_requiring_redirects($result);
        foreach ($redirects_required as $url => $data) {
          $a = AmberNetworkUtils::open_single_url($url, $additional_options);
          if ($a) {
            $result[$url] = $a;
          }
        }
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
   * Open a URL, and return an array with dictionary of header information and the contents of the URL
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

  /**
   * Open a single URL, and return an array with dictionary of header information and the contents 
   * of the URL. Handle redirects ourselves, rather than using CURLOPT_FOLLOWLOCATION
   * Adapted from http://slopjong.de/2012/03/31/curl-follow-locations-with-safe_mode-enabled-or-open_basedir-set/
   * @param $url string of resource to download
   * @return array dictionary of header information and a stream to the contents of the URL
   */
  public static function open_single_url($url, $additional_options = array(), $follow_redirects = TRUE) {
    $options = array(
      CURLOPT_FAILONERROR => TRUE,      /* Don't ignore HTTP errors */
      CURLOPT_FOLLOWLOCATION => FALSE,  /* Don't follow redirects */ 
      CURLOPT_CONNECTTIMEOUT => 5,      /* Connection timeout */
      CURLOPT_TIMEOUT => 10,            /* Timeout for any CURL function */
      CURLOPT_RETURNTRANSFER => 1,      /* Return the output as a string */
      CURLOPT_HEADER => TRUE,           /* Return header information as part of the file */
      CURLOPT_USERAGENT => AmberNetworkUtils::get_user_agent_string(),
      CURLOPT_ENCODING => '',           /* Handle compressed data */
    );

    $max_redirects = 5;    
    try {

      $ch = curl_init($url);
      if (curl_setopt_array($ch, $additional_options + $options) === FALSE) {
        throw new RuntimeException(join(":", array(__FILE__, __METHOD__, "Error setting CURL options", $url, curl_error($ch))));
      }
      $original_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
      $newurl = $original_url;
      
      do {
        curl_setopt($ch, CURLOPT_URL, $newurl);
        $response = curl_exec($ch);
        $response_info = curl_getinfo($ch);
        if ($response_info['http_code'] == 301 || $response_info['http_code'] == 302) {
          $newurl = $response_info['redirect_url'];
        } else if ($meta = AmberNetworkUtils::find_meta_redirect($response)) {
          $newurl = $meta;
        } else { 
          break; // Not a redirect, so we're done
        }
        // if no scheme is present then the new url is a relative path and thus needs some extra care        
        if (!preg_match("/^https?:/i", $newurl)) {          
          $last_slash = strrpos($original_url,"/",9); // Starting at position 9 starts search past http://
          if ($last_slash == (strlen($original_url) - 1)) {
            $newurl = $original_url . $newurl;  
          } else if ($last_slash === FALSE) {
            $newurl = join("/",array($original_url, $newurl));
          } else {
            $newurl = join("/",array(substr($original_url, 0, $last_slash), $newurl));
          }          
        }    
      } while ((--$max_redirects) && $follow_redirects);     
      curl_close($ch);

    } catch (RuntimeException $e) {
      error_log($e->getMessage());
      curl_close($ch);
      return FALSE;
    }
    
    if (!$max_redirects) {
      return FALSE; // We ran out of redirects without getting a result
    } else {
      /* Split into header and body */
      $header_size = $response_info['header_size'];
      $header = substr($response, 0, $header_size-1);
      $body = substr($response, $header_size);
      $headers = AmberNetworkUtils::extract_headers($header);
      return array("headers" => $headers, "body" => $body, "info" => $response_info);
    }
  }

  /**
   * Look at the results from a lookup using curl_multi, and identify urls that we need 
   * to query again, because a redirect is needed but was not followed
   * @param  $urls associative array of url lookups, keyed by url
   * @return associative array of the subset of items that need to be queried again
   */
  public static function find_urls_requiring_redirects($urls) {
    $result = array();
    foreach ($urls as $url => $data) {
      if (($data['info']['http_code'] == 301) || ($data['info']['http_code'] == 302)) {
        $result[$url] = $data;
      } else if (AmberNetworkUtils::find_meta_redirect($data['body'])) {
        $result[$url] = $data;
      }
    }  
    return $result;
  }

  /**
   * Extract the HTML <head> from the document
   * @param  string $body HTML document to extract head from
   * @return string       contents of the <head> element, or the full document if <head> not found
   */
  private static function get_head($body) {
    $head_size = stripos($body, "</head>");
    if ($head_size === FALSE) {
      $head = $body;
    } else {
      $head = substr($body,0,$head_size);
    }
    return $head;
  }

  /**
   * Find the META refresh tags with redirects (if any) in the HEAD of an HTML document
   * Sample meta refresh tags:
   *   <meta http-equiv="refresh" content="30; URL=http://www.example.org/login">
   *   <meta http-equiv="REFRESH" content="0; url=http://www.example.org/login">
   *   <meta http-equiv="refresh" content="5"> (NOT A REDIRECT)
   * @param  $body strong with HTML 
   * @return string with URL if a redirect is found, or FALSE if one is not found
   */
  public static function find_meta_redirect($body) {
    $head = AmberNetworkUtils::get_head($body);
    if (preg_match("/http-equiv\s*=\s*['\"]refresh['\"].*url\s*=\s*(.*)['\"]/i", $head, $matches)) {
      return $matches[1];
    } else {
      return FALSE;
    }
  }

  /**
   * Respect the "noarchive" meta tag as described here: http://noarchive.net/meta/
   * Sample tags that will prevent archiving:
   *   <meta name="robots" content="noarchive">
   *   <meta name="amber" content="noarchive">
   *   <meta name="robots" content="noarchive, noindex">
   *   <meta name="amber" content="noindex">
   * @param  string $body HTML document to example
   * @return boolean       true if there is an application no-archive tag, false otherwise
   */
  public static function find_meta_no_archive($body) {
    $head = AmberNetworkUtils::get_head($body);
    if (preg_match("/<meta\s+name\s*=\s*['\"](robots|amber)['\"].*content\s*=\s*['\"].*(noarchive|noindex).*['\"]/i", $head, $matches)) {
      return TRUE;
    } else {
      return FALSE;
    }
  }

}