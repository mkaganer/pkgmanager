<?php
// B.H.

class sql_mysqli_result {

  private $link,$result;

  // should be called by sql_mysqli_connection::query
  public function __construct($link,$result) {
    if (empty($result)) throw new Exception("Empty result from a query");
    $this->link = $link; $this->result = $result;
  }
  
  public function __destruct() {
    //echo "<div>res: close</div>";
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