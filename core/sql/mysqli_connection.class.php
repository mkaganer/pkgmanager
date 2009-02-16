<?php
// B.H.

// MYSQLI driver
class sql_mysqli_connection extends sql_connection {

  private $link;

  public function __construct($con_info,$params=null) {
    global $_pkgman;
    $pkg = $_pkgman->get("sql");
    if ((!is_array($con_info)) || (count($con_info)<4)) throw new Exception("Bad connection info!");
    // default parameters
    $this->params = array(
      'unbuffered' => false,
    );
    if (is_array($params)) $this->params = array_merge($this->params,$params);
    $link = new mysqli($con_info[1],$con_info[2],$con_info[3],$con_info[4]);
    if (!$link) throw new Exception("MYSQLi connection failed!");
    $charset = null;
    if (isset($pkg->config['charset'])) $charset = $pkg->config['charset'];
    if (isset($this->params['charset'])) $charset = $this->params['charset'];
    if (!empty($charset)) $link->set_charset($charset);
    $this->link = $link;
  }
  
  public function __destruct() {
    if ($this->link) $this->link->close();
  }
  
  // returns sql_mysqli_result or true on success or FALSE on failure
  public function query($query) {
    global $_trace_log;
    if (defined('_TRACE_QUERIES')&&empty($_trace_log)) $_trace_log = new utils_microtimer();
    $snap = microtime();
    $mode = $this->params['unbuffered']?MYSQLI_USE_RESULT:MYSQLI_STORE_RESULT;
    $res = $this->link->query($query,$mode);
    if (($res===FALSE) || (is_null($res))) {
      if ($this->throw_on_error) {
        throw new sql_error($this->link->error,$this->link);
      } else {
        return FALSE;
      }
    }
    if (defined('_TRACE_QUERIES')) $_trace_log->snapshot('QUERY: '.$query,$snap);
    if ($res===TRUE) return TRUE;
    $res = new sql_mysqli_result($this->link,$res);
    return $res;
  }
  
  public function isError() {
    return ($this->link->errno>0);
  }
  public function ErrorMsg() {
    return $this->link->error;
  }
  public function insert_id() {
    return $this->link->insert_id;
  }
  
  public function escape($str) {
    return addslashes($str);
  }
  
  // driver-specific:
  
  public function get_link() {
    return $this->link;
  }

}