<?php
// B.H.

// base class for SQL connections
abstract class sql_connection {

  public $throw_on_error = true; // throw sql_error exception if query results in error
  public $params; // driver-specific parameters in assoc. array

  // this is a "class factory" function. It creates a new instance of sql_connection extending
  // class according to the connection info given
  // $con_info = array('engine','host','user','pass','db')
  static public function open($con_info=null,$params=null) {
    global $_pkgman;
    $pkg = $_pkgman->get('sql');
    if (empty($con_info)) {
      if (!isset($pkg->config['connection'])) throw new Exception("No connection info specified!");
      $con_info = $_pkgman->get('sql')->config['connection'];
    }
    $driver = $con_info[0]; 
    if (!isset($pkg->config['drivers'][$driver])) throw new Exception("Unknown SQL engine $driver");
    
    $con_class = $pkg->config['drivers'][$driver];
    return new $con_class($con_info,$params);
  }
  
  // DB driver's interface definition:
  // returns sql_result or true on success or FALSE on failure
  abstract public function query($query);
  abstract public function isError(); // true if last operation resulted in error
  abstract public function ErrorMsg(); // last error message text
  abstract public function insert_id(); // last insert id
  abstract public function escape($str); // prepare string to be pasted into a query
  
  
  // select 1 column of results from the query
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
    //echo "<div>sql: [$sql]</div>";
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
    //echo "<div>sql: [$sql]</div>";
    $res = $this->query($sql);
    return ($res!=false);
  }

}