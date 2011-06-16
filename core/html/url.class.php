<?php
// B.H.

class html_url {
    /**
     * @var boolean is the url absolute or relative
     */
    public $is_absolute; 
    /**
     * @var string url schema like http/https/mailto/javascript
     */
    public $schema;
    /**
     * @var string note: makes sence only for http/https
     */
    public $host;
    /**
     * @var string
     */
    public $path;
    /**
     * @var array
     */
    public $query = array();
    /**
     * @var string
     */
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
        if (preg_match('#^(http|https|mailto|javascript):(.*)$#i',$path,$m)) {
          $this->is_absolute = true;
          $this->schema = $m[1];
          switch($m[1]) {
              case 'http':
              case 'https':
                  $m2 = ltrim($m[2],'/');
                  $vals = explode('/',$m2,2);
                  $this->host = trim($vals[0],'/');
                  $this->path = isset($vals[1])?ltrim($vals[1],'/'):'';
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
            $v = explode('=',$qstr,2);
            if (empty($v[0])) continue;
            $v[0] = urldecode($v[0]);
            if (strpos($v[0],'[')===false) $this->query[$v[0]] = isset($v[1])?urldecode($v[1]):null;
            else {
                $v1 = explode('[',$v[0]);
                $a =& $this->query;
                for($i=0;$i<count($v1)-1;$i++) {
                    $k = trim($v1[$i],'[]');
                    if (!isset($a[$k])) $a[$k] = array();
                    $a =& $a[$k];
                }
                $k = trim($v1[$i],'[]');
                $a[$k] = isset($v[1])?urldecode($v[1]):null;
            }
        }
    }
    
    private function query_serialize($array, $prefix=null) {
        $res = '';
        foreach ($array as $name => $val) {
            if (!empty($res)) $res .= '&';
            if (!is_null($prefix)) $name = $prefix.'['.$name.']'; 
            if (is_array($val)) $res .= $this->query_serialize($val,$name);
            elseif (is_null($val)) $res .= $name;
            else $res .= $name."=".urlencode($val);
        }
        return $res;
    }
    
    private function query_to_str($query_merge=null) {
        $query = $this->query;
        if (empty($query)) $query = $query_merge;
        elseif (is_array($query_merge)) $query = array_merge($query,$query_merge);
        if (empty($query)) return '';
        return $this->query_serialize($query);
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
