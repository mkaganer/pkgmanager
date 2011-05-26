<?php
// B.H.

class sql_mysql_result extends sql_result {

  /**
   * @var sql_mysql_connection
   */
  private $connection;

  /**
   * @var mysql result resource
   */
  private $result;

  // should be called by sql_mysql_connection::query
  public function __construct($connection,$result) {
    if (empty($result)) throw new Exception("Empty result from a query");
    $this->connection = $connection;
    $this->result = $result;
  }

  public function __destruct() {
  	mysql_free_result($this->result);
  }
  
  public function get_raw_result() {
      return $this->result;
  }

  function num_rows() {
  	return mysql_num_rows($this->result);
  }

  function row(&$data) {
  	$data = mysql_fetch_array($this->result,MYSQL_NUM);
    return !(empty($data));
  }
  function arow(&$data) {
    $data = mysql_fetch_array($this->result,MYSQL_ASSOC);
    return !(empty($data));
  }

}
?>