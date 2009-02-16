<?php
// B.H.

class sql_error extends Exception {
  public $connection;
  
  public function __construct($message,$con) {
    parent::__construct($message);
    $this->connection = $con;
  }
}