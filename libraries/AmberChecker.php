<?php

interface iAmberChecker {
  public function up($url);
  public function check($last_check, $force = false);
}

class AmberChecker implements iAmberChecker {

  /**
   * Check to see if a given URL is available (if it returns 200 status code)
   * @param $url
   */
  public function up($url) {

    $item = AmberNetworkUtils::open_url($url,  array(CURLOPT_FAILONERROR => FALSE));
    if (isset($item['info']['http_code'])) {
      return ($item['info']['http_code'] == 200);
    } else {
      return false;
    }
  }

  /** 
   * Look at the results from fetching a URL, tell whether the site is
   * up or not.
   */
  private function is_up($fetch_result) {
    if (isset($fetch_result['info']['http_code'])) {
      return ($fetch_result['info']['http_code'] == 200);
    } else {
      return false;
    }
  }

  /**
   * Check whether a URL is available, and update the status of the URL in the database
   * @param $last_check array of the data from the last check for the URL
   * @param bool $force true if the check should be forced to happen, even if it's not yet scheduled
   * @return array|bool
   */
  public function check($last_check, $force = false) {
    $url = $last_check['url'];
    $id = isset($last_check['id']) ? $last_check['id'] : md5($url); //TODO: Unify ID generation

    /* Make sure we're still scheduled to check the $url */
    $next_check_timestamp = isset($last_check['next_check']) ? $last_check['next_check'] : 0;
    if (!$force && $next_check_timestamp > time()) {
      return false;
    }

    $date = new DateTime();
    if (!AmberRobots::robots_allowed($url)) {
      /* If blocked by robots.txt, schedule next check for 6 months out */
      $next = $date->add(new DateInterval("P6M"))->getTimestamp();
      $status = isset($last_check['status']) ? $last_check['status'] : NULL;
      error_log(join(":", array(__FILE__, __METHOD__, "Blocked by robots.txt", $url)));
      $message = "Blocked by robots.txt";
    } else {
      $fetch_result = AmberNetworkUtils::open_url($url,  array(CURLOPT_FAILONERROR => FALSE));
      $status = $this->is_up($fetch_result);
      $next = $this->next_check_date(isset($last_check['status']) ? $last_check['status'] : NULL,
                                     isset($last_check['last_checked']) ? $last_check['last_checked'] : NULL,
                                     isset($last_check['next_check']) ? $last_check['next_check'] : NULL,
                                     $status);
    }

    $now = new DateTime();
    $result = array(
            'id' => $id,
            'url' => $url,
            'last_checked' => $now->getTimestamp(),
            'next_check' => $next,
            'status' => isset($status) ? ($status ? 1 : 0) : NULL,
            'message' => isset($message) ? $message : NULL,
            'details' => isset($fetch_result) ? $fetch_result : NULL,
          );

    return $result;
  }

  /**
   * Get the unix timestamp for the date the url should next be checked, based on the new status and the previous
   * interval between checks
   * @param $status bool with the previous status for the URL from the database
   * @param $last_checked_timestamp integer with the timestamp of the previous check
   * @param $next_check_timestamp integer with the timestamp of the next scheduled check (which we're doing now)
   * @param $new_status bool with the current status of the URL
   * @return int with the unix timestamp of the date after which the url can be checked again
   */
  public function next_check_date($status, $last_checked_timestamp, $next_check_timestamp, $new_status) {
    $date = new DateTime();
    if (is_null($status) || ($new_status != (bool)($status)) || is_null($last_checked_timestamp)) {
      $next_timestamp = $date->add(new DateInterval("P1D"))->getTimestamp();
    } else {
      $last = new DateTime();
      $last->setTimestamp($last_checked_timestamp);
      $old_next = new DateTime();
      $old_next->setTimestamp($next_check_timestamp);
      $diff = $last->diff($old_next,true);
      if ($diff->days >= 30) {
        $next_timestamp = $date->add(new DateInterval("P30D"))->getTimestamp();
      } else {
        $next_timestamp = $date->add($diff)->add(new DateInterval("P1D"))->getTimestamp();
      }
    }
    return $next_timestamp;
  }

} 