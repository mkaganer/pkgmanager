<?php
// B.H.

/**
 * @desc mysqli driver connector
 * @author mkaganer
 */
class sql_mysqli_connection extends sql_connection {

  /**
   * @var mysqli - reference to a wrapped mysqli object
   */
  public $link;

  /**
   * @desc Connect to a mysql using mysqli extention.
   * $con_config common params and default values:
   *   'host' => ini_get("mysqli.default_host"), 'port' => ini_get("mysqli.default_port"),
   *   'user' => ini_get("mysqli.default_user"), 'pass' => ini_get("mysqli.default_pw"),
   *   'db' => '',
   * if $con_config['link'] is set to mysqli object, do not open new connection
   * just wrap already open mysqli connection with this class
   * @param $con_config - connection config
   */
  public function __construct($con_config) {
    parent::__construct();
    $pkg = pkgman_manager::getp("sql");

    $default_config = array(
      'host' => ini_get("mysqli.default_host"),
      'port' => ini_get("mysqli.default_port"),
      'user' => ini_get("mysqli.default_user"),
      'pass' => ini_get("mysqli.default_pw"),
      'db' => '',
      'socket' => ini_get("mysqli.default_socket"),
      'charset' => @$pkg->config['charset'],
      'buffered' => true,
      'throw_on_error' => @$pkg->config['throw_on_error'],
    );

    // merge all config arrays together
    if (empty($con_config)) $con_config = array();
    $this->config = $config = array_merge($this->config,$default_config,$con_config);

    if (!empty($config['link']))
      $link = $config['link'];
      else $link = new mysqli($config['host'],$config['user'],$config['pass'],
                              $config['db'],$config['port'],$config['socket']);
    if (!$link) throw new Exception("MYSQLi connection failed!");
    $this->link = $link;

    if (!empty($config['charset'])) $link->set_charset($config['charset']);
  }

  public function __destruct() {
    if ($this->link) $this->link->close();
  }

  public function select_db($db) {
  	$this->link->select_db($db);
  }

  /* (non-PHPdoc)
   * @see sql/sql_connection#query()
   */
  public function query($query) {
    global $_trace_log;
    // render query object if needed and implicitly convert to a string (PHP >=5.2 needed!)
    $query = ($query instanceof sql_query)?$query->render():((string)$query);
    if (defined('_TRACE_QUERIES')&&empty($_trace_log)) $_trace_log = new utils_microtimer();
    $snap = microtime();
    $mode = $this->config['buffered']?MYSQLI_STORE_RESULT:MYSQLI_USE_RESULT;
    $res = $this->link->query($query,$mode);
    if (($res===false) || (is_null($res))) {
      if ($this->config['throw_on_error'])
        throw new sql_error($this->link->error,$this->link);
      return false;
    }
    if (defined('_TRACE_QUERIES')) $_trace_log->snapshot('QUERY: '.$query,$snap);
    if ($res===true) return true;
    return new sql_mysqli_result($this,$res);
  }

  public function is_error() {
    return ($this->link->errno>0);
  }
  public function error_msg() {
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
?>