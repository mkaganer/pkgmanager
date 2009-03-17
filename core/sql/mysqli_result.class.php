<?php
// B.H.

class sql_mysqli_result extends sql_result {

  /**
   * @var sql_mysqli_connection
   */
  private $connection;

  /**
   * @var mysqli_result
   */
  private $result;

  // should be called by sql_mysqli_connection::query
  public function __construct($connection,$result) {
    if (empty($result)) throw new Exception("Empty result from a query");
    $this->connection = $connection;
    $this->result = $result;
  }

  public function __destruct() {
    $this->result->free();
  }

  function num_rows() {
    return $this->result->num_rows;
  }
  function row(&$data) {
    $data = $this->result->fetch_array(MYSQLI_NUM);
    return !(empty($data));
  }
  function arow(&$data) {
    $data = $this->result->fetch_array(MYSQLI_ASSOC);
    return !(empty($data));
  }

}