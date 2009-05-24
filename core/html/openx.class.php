<?php
// B.H.

// represents a placeholder for openx's zone (very basic)
// requires openx instalation, and it's API
class html_openx extends html_block {

  public $active = true;
  
  public $zone;

  public function __construct($zone) {
    global $phpAds_context;
    parent::__construct();

    $this->zone = $zone;
    
    $phpAds_raw = view_local('', $this->zone, 0, 0, '', '', '0', $phpAds_context, '');
    if (!empty($phpAds_raw['html'])) {
      $phpAds_context[] = array('!=' => 'bannerid:'.$phpAds_raw['bannerid']);
      $html = $phpAds_raw['html'];
      $this->add(iconv('utf-8','windows-1255',$html));
    } else {
      $this->active = false;
    }
  }

  public static function get_zone_html($zone) {
    global $phpAds_context;
    $phpAds_raw = view_local('', $zone, 0, 0, '', '', '0', $phpAds_context, '');
    if (!empty($phpAds_raw['html'])) {
      $phpAds_context[] = array('!=' => 'bannerid:'.$phpAds_raw['bannerid']);
      $html = $phpAds_raw['html'];
      return (iconv('utf-8','windows-1255',$html));
    } else {
      return "";
    }
  }

}

?>
