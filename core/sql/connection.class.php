<?php
// B.H.

/**
 * @desc Base class for SQL connections
 * @author mkaganer
 */
abstract class sql_connection {

  /**
   * @desc Connection-specific config. Required parameter:
   *   'driver' => one of $pkg->config['drivers']
   * See specific driver connection class for more params
   * @var array
   */
  public $config;

  /**
   * @desc Model provider instance, created by the first call to $this->get_model()
   * @var sql_model_provider
   */
  protected $model_provider = null;

  /**
   * @desc Cached model objects from $this->get_model()
   * @var array of sql_model
   */
  protected $model_cache = array();

  /**
   * @desc this is a "class factory" function. It creates a new instance of sql_connection extending
   * class according to the connection config given
   * $con_config = array('driver'=>'engine',...)
   * See driver's docs for possible config values
   * @param array $con_config - connection config, if null $pkg->config['connection'] will be used
   * @return sql_connection open connection class
   */
  static public function open($con_config=null) {
    $pkg = pkgman_manager::getp('sql');
  	if (empty($con_config)) {
      if (!isset($pkg->config['connection'])) throw new Exception("No connection info specified!");
      $con_config = $pkg->config['connection'];
    }
    $driver = $con_config['driver'];
    if (!isset($pkg->config['drivers'][$driver])) throw new Exception("Unknown SQL engine $driver");

    $con_class = $pkg->config['drivers'][$driver];
    return new $con_class($con_config);
  }

  /**
   * @desc Must be called by all specific DB driver constructors
   */
  protected function __construct() {
  	$pkg = pkgman_manager::getp('sql');
  	$this->config = array(
      'model_provider' => @$pkg->config['default_model_provider'],
  	);
  }

  /**
   * @desc Get model object by model's name using the defined model_provider to load the model's class.
   * On the first call, model provider object is created.
   * If $cache is true (default), will reuse previously created model classes
   * with the same $model_name instead of creating a new instance.
   * @param string $model_name
   * @param boolean $cache
   * @return sql_model
   */
  public function get_model($model_name,$cache=true) {
    if (isset($this->model_cache[$model_name])) return $this->model_cache[$model_name];
  	if (empty($this->model_provider)) {
  		$provider_class = $this->config['model_provider'];
  		$this->model_provider = new $provider_class($this);
  	}
  	$mod = $this->model_provider->get_model($model_name);
  	if (empty($mod)||(!($mod instanceof sql_model)))
  	    throw new Exception("Model '$model_name' invalid or not found!");
  	return ($this->model_cache[$model_name] = $mod);
  }

  // DB driver's interface definition:

  /**
   * @desc switch to another database
   * @param string $db - database name
   */
  abstract public function select_db($db);

  /**
   * @desc Basic sql query method
   * @param $query
   * @return sql_mysqli_result or true on success or false on failure
   */
  abstract public function query($query);

  /**
   * @return true if last operation resulted in error
   */
  abstract public function is_error();

  /**
   * @return last error message text
   */
  abstract public function error_msg();

  /**
   * @return last insert id
   */
  abstract public function insert_id();

  /**
   * @param $str - input string
   * @return properly escaped string for SQL data values
   */
  abstract public function escape($str);

  /**
   * @desc Creates a new filter instance. See sql_mysql_filter class for more info
   * @param array $filters
   * @param string $operator - default is AND
   * @param string $prefix - default is none
   * @return sql_mysql_filter
   */
  public function filter($filters,$operator=null,$prefix=null) {
  	 // currently, we default to mysql-style syntax
  	 // In the future, this should be extended to support more
  	 // db engines
     return new sql_mysql_filter($this,$filters,$operator,$prefix);
  }

  /**
   * @desc build a field list for select (and other) statements. If prefix is not null, it will be
   * prepended to each name as a table/database alias
   * @param array $list
   * @return string
   */
  public function field_list($list,$prefix=null) {
  	$res = array();
  	foreach($list as $fld) {
  		$res[] = (empty($prefix))?"`$fld`":"${prefix}.`$fld`";
  	}
  	return implode(", ",$res);
  }

  /**
   * @desc select 1 column of results from the query
   * @param $query - SQL
   * @param int $col_idx=0 - column index
   * @return array
   */
  public function select_v($query,$col_idx=0) {
    $res = $this->query($query);
    if (empty($res)) return false;
    $arr = array();
    while($res->row($data)) $arr[] = $data[$col_idx];
    unset($res);
    return $arr;
  }

  /**
   * @desc select only 1 value from result ($col_idx column in the first row)
   * @param $query - SQL
   * @param int $col_idx=0 - column index
   * @return mixed value
   */
  public function select_s($query,$col_idx=0) {
    $res = $this->query($query);
    if (!is_object($res)) return false;
    $is_ok = $res->row($data);
    unset($res);
    return $is_ok?$data[$col_idx]:false;
  }

  /**
   * @desc returns 1 row as array or false
   * @param $query
   * @return array|boolean
   */
  public function select_r($query) {
    $res = $this->query($query);
    if (!is_object($res)) return false;
    if (!$res->arow($r)) $r = false;
    unset($res);
    return $r;
  }

  /**
   * @desc Returns a 2-dimentional array each row is an assoc. array fetched from result's row.
   * If $primary_key is specified, it's value will be used as a keys for the outer array
   * @param $query
   * @param $primary_key
   * @return array
   */
  public function select_table($query,$primary_key=null) {
    $res = $this->query($query);
    if (!is_object($res)) return false;
    $a = array();
    if (!empty($primary_key))
      while($res->arow($data)) $a[$data[$primary_key]] = $data;
      else while($res->arow($data)) $a[] = $data;
    unset($res);
    return $a;
  }

  //
  /**
   * @desc Select 2 columns as hash (PHP assoc. array):
   * first column is a key, and the second is the value
   * @param string $query
   * @return array
   */
  public function select_h($query) {
    $res = $this->query($query);
    if (!is_object($res)) return false;
    $a = array();
    while($res->row($data)) $a[$data[0]] = $data[1];
    unset($res);
    return $a;
  }

  /**
   * @desc Construct an UPDATE SQL statement:
   * $values is an assoc. array ('column'=>'value',...)
   * If 'column' starts with ! then 'value' is not quoted but threated as an SQL expression:
   *   Example:
   *   <code>$link->update('tab1',array('name'=>'Moshe','!timestamp'=>'now()','id=770');</code>
   * $where may also be any object that defines __toString() method, which is usable for passing
   * sql_filter instance
   * @param string $table
   * @param array $values - array('col1'=>'val1',...'!time'=>'now()')
   * @param string $where - a string to use in the WHERE clause
   * @return boolean - true on success
   */
  public function update($table,$values,$where) {
  	if (is_object($where)) $where = $where->__toString();
    $where = trim($where);
    if (empty($where)) throw new Exception("update() \$where is empty!");
    $sql = "update `$table` set ";
    $psik = false;
    foreach ($values as $col => $val) {
      if ($psik) $sql .= ', ';
      $psik = true;
      if ($col[0] == '!') {
        $col = substr($col,1);
        $sql .= "`$col`=($val)";
      } else {
        $sql .= sprintf("`%s`='%s'",$col,$this->escape($val));
      }
    }
    $sql .= " where ".$where;
    $res = $this->query($sql);
    return ($res!=false);
  }

  /**
   * @desc Construct an INSERT SQL statement:
   * $values is an assoc. array ('column'=>'value',...)
   * If 'column' starts with ! then 'value' is not quoted but threated as an SQL expression:
   *   Example:
   *   <code>$link->insert('tab1',array('name'=>'Moshe','!timestamp'=>'now()');</code>
   * @param string $table
   * @param array $values - array('col1'=>'val1',...'!time'=>'now()')
   * @return boolean - true on success
   */
  public function insert($table,$values) {
    $sql = "insert into `$table` (";
    $psik = false;
    foreach ($values as $col => $val) {
      if ($psik) $sql .= ', ';
      $psik = true;
      if ($col[0] == '!') $col = substr($col,1);
      $sql .= "`$col`";
    }
    $sql .= ") values (";
    $psik = false;
    foreach ($values as $col => $val) {
      if ($psik) $sql .= ', ';
      $psik = true;
      if ($col[0] == '!') {
        $sql .= "($val)";
      } else {
        $sql .= sprintf("'%s'",$this->escape($val));
      }
    }
    $sql .= ")";
    $res = $this->query($sql);
    return ($res!=false);
  }

}
?>