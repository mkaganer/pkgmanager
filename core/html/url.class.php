<?php
// B.H.

class html_url {
  public $is_absolute; // is the url absolute or relative
  public $schema; // http/https/mailto/javascript/skype
  public $host; // makes sence only for http/https
  public $path;
  public $query;
  public $hash;
  
  public function __construct($url) {
    $url = trim($url);
    if (strpos($url,'#')!==false) {
      list($url,$hash) = explode('#',$url,2);
      $this->hash = $hash;
    } else $this->hash = null;
    if (strpos($url,'?')!==false) {
      list($path,$query) = explode('?',$url,2); 
      if (!empty($query)) $this->parse_query($query);
    } else {
      $path = $url;
    }
    if (preg_match('#^(http|https|mailto|javascript|skype):(.*)$#i',$path,$m)) {
      $this->is_absolute = true;
      $this->schema = $m[1];
      switch($m[1]) {
          case 'http':
          case 'https':
              $m2 = ltrim($m[2],'/');
              $vals = explode('/',$m2,2);
              $this->host = trim($vals[0],'/');
              $this->path = isset($vals[1])?trim($vals[1],'/'):'';
              break;
          default:
              $this->host = null;
              $this->path = $m[2];
      }
    } else {
      $this->is_absolute = false;
      $this->path = $path;
    }
  }
  
  private function parse_query($query) {
    $q = explode('&',$query);
    foreach($q as $qstr) {
      list($name,$val) = explode('=',$qstr);
      if (!empty($name)) $this->query[$name] = urldecode($val);
    }
  }
  
  private function query_to_str($query_merge=null) {
    $query = $this->query;
    if (empty($query)) $query = $query_merge;
    elseif (is_array($query_merge)) $query = array_merge($query,$query_merge);
    if (empty($query)) return '';
    $res = array();
    foreach ($query as $name => $val) $res[] = $name."=".urlencode($val);
    return implode('&',$res);
  }
  
  /**
   * @desc Get url as a string
   * @param $query_merge array
   * @return string
   */
  public function get_url($query_merge=null) {
    if ($this->is_absolute) {
      switch($this->schema) {
          case 'http':
          case 'https':
              $str = "{$this->schema}://{$this->host}/{$this->path}";
              break;
          default:
              $str = "{$this->schema}:{$this->path}";
      }
      $qstr = $this->query_to_str($query_merge);
      if (!empty($qstr)) $str .= '?'.$qstr;
    } else {
      $str = $this->path;
      $qstr = $this->query_to_str($query_merge);
      if (!empty($qstr)) $str .= '?'.$qstr;
    }
    if (!empty($this->hash)) $str.='#'.$this->hash;
    return $str;
  }
  
  public function __toString() {
    return $this->get_url();
  }
  
  /**
   * @param $query_merge array
   * @return html_url
   */
  public function clone_url($query_merge=null) {
    $new_url = clone $this;
    if (empty($new_url->query)) $new_url->query = $query_merge;
    elseif (is_array($query_merge)) $new_url->query = array_merge($new_url->query,$query_merge);
    return $new_url;
  }
  
  public function set_host($schema,$host) {
    $this->is_absolute = true;
    $this->schema = $schema;
    $this->host = $host;
    $this->path = ltrim($this->path,'/');
  }
  
  /**
   * @desc Merge the specified values into $this->query array, overriding old values 
   * @param $new_query array values to be array_merge'd into $this->query
   * @return void
   */
  public function merge_query($new_query) {
      if (!is_array($this->query)) $this->query = array();
      $this->query = array_merge($this->query,$new_query);
  }
}

?>