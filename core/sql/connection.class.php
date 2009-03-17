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
  protected $config;

  /**
   * @desc this is a "class factory" function. It creates a new instance of sql_connection extending
   * class according to the connection config given
   * $con_config = array('driver'=>'engine',...)
   * See driver's docs for possible config values
   * @param array $con_config - connection config, if null $pkg->config['connection'] will be used
   * @return sql_connection open connection class
   */
  static public function open($con_config=null) {
    global $_pkgman;
    $pkg = $_pkgman->get('sql');
    if (empty($con_config)) {
      if (!isset($pkg->config['connection'])) throw new Exception("No connection info specified!");
      $con_config = $_pkgman->get('sql')->config['connection'];
    }
    $driver = $con_config['driver'];
    if (!isset($pkg->config['drivers'][$driver])) throw new Exception("Unknown SQL engine $driver");

    $con_class = $pkg->config['drivers'][$driver];
    return new $con_class($con_config);
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

  // select only 1 value from result
  public function select_s($query,$col_idx=0) {
    $res = $this->query($query);
    if (!is_object($res)) return false;
    $is_ok = $res->row($data);
    unset($res);
    return $is_ok?$data[$col_idx]:false;
  }

  // returns 1 row as array or false
  public function select_r($query) {
    $res = $this->query($query);
    if (!is_object($res)) return false;
    if (!$res->arow($r)) $r = false;
    unset($res);
    return $r;
  }

  // returns a 2-dimentional array each row is an assoc. array fetched from result's row
  // (like DataSet.Fill of .NET)
  // if $primary_key is specified, it's column's key to be used as output array's key
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

  // select 2 columns as hash (PHP array):
  // first column is a key, and the second is the value
  public function select_h($query) {
    $res = $this->query($query);
    if (!is_object($res)) return false;
    $a = array();
    while($res->row($data)) $a[$data[0]] = $data[1];
    unset($res);
    return $a;
  }

  // construct UPDATE statement
  // $values is an assoc. array ('column'=>'value',...)
  // if 'column' starts with ! then 'value' is not quoted but threated as an SQL expression
  // example $link->update('tab1',array('name'=>'Moshe','!timestamp'=>'now()','id=770');
  public function update($table,$values,$where) {
    $where = trim($where);
    if (empty($where)) throw new Exception("update(where) is empty!");
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