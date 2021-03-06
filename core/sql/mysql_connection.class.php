<?php
// B.H.

/**
 * @desc legacy mysql driver connector
 * @author mkaganer
 */
class sql_mysql_connection extends sql_connection {

  /**
   * @var mysql link resource
   */
  public $link = null;

  /**
   * @desc Connect to a mysql using mysqli extention.
   * $con_config some common params and default values:
   *   'host' => ini_get("mysql.default_host"),
   *   'user' => ini_get("mysql.default_user"),
   *   'pass' => ini_get("mysql.default_password"),
   *   'db' => '',
   * if $con_config['link'] is set to mysql link resource, do not open
   * a new connection, just wrap already open mysql connection with this class
   * @param $con_config - connection config
   */
  public function __construct($con_config) {
    parent::__construct();
    $pkg = pkgman_manager::getp("sql");

    $default_config = array(
      'host' => ini_get("mysql.default_host"),
      'user' => ini_get("mysql.default_user"),
      'pass' => ini_get("mysql.default_password"),
      'db' => '',
      'new_link' => false,
      'charset' => @$pkg->config['charset'],
      'buffered' => true,
      'throw_on_error' => @$pkg->config['throw_on_error'],
      'on_demand' => false, // if true will actually connect only when some query is executed
    );

    // merge all config arrays together
    if (empty($con_config)) $con_config = array();
    $this->config = array_merge($this->config,$default_config,$con_config);

    if (empty($this->config['on_demand'])) $this->set_link();
  }

  public function __destruct() {
    if ($this->link  && (!empty($this->config['close_on_desctruct']))) mysql_close($this->link);
  }
  
  protected function set_link() {
    $this->link = (!empty($this->config['link']))?$this->config['link']:
        @mysql_connect($this->config['host'],$this->config['user'],$this->config['pass'],
            $this->config['new_link']);
    if (empty($this->link)) throw new Exception("mysql connection failed!");

    if (!empty($this->config['charset'])) mysql_set_charset($this->config['charset'],$this->link);
    if (!empty($this->config['db'])) $this->select_db($this->config['db']);
  }

  public function select_db($db) {
  	mysql_select_db($db,$this->link);
  }

  /* (non-PHPdoc)
   * @see sql/sql_connection#query()
   */
  public function query($query) {
    global $_trace_log;
    if (empty($this->link)) $this->set_link();
    // render query object if needed and implicitly convert to a string (PHP >=5.2 needed!)
    $query = ($query instanceof sql_query)?$query->render():((string)$query);
    if (defined('_TRACE_QUERIES')&&empty($_trace_log)) $_trace_log = new utils_microtimer();
    $snap = microtime();
    $query_func = $this->config['buffered']?"mysql_query":"mysql_unbuffered_query";
    $res = call_user_func($query_func,$query,$this->link);
    if (($res===false) || (is_null($res))) {
      if ($this->config['throw_on_error'])
        throw new sql_error($this->error_msg(),$this->link, $query);
      return false;
    }
    if (defined('_TRACE_QUERIES')) $_trace_log->snapshot('QUERY: '.$query,$snap);
    if ($res===true) return true;
    return new sql_mysql_result($this,$res);
  }

  public function is_error() {
    return  (mysql_errno($this->link)>0);
  }

  public function error_msg() {
  	return mysql_error($this->link);
  }
  
  public function insert_id() {
  	return mysql_insert_id($this->link);
  }
  
  public function affected_rows() {
  	return mysql_affected_rows($this->link);
  }

  public function escape($str) {
    return addslashes($str);
  }

  // driver-specific:

  public function get_link() {
    return $this->link;
  }

}
?>