<?php
// B.H.

abstract class sql_result {

  abstract function num_rows();
  abstract function row(&$data); // fetch a row as an indexed array
  abstract function arow(&$data);// fetch a row as an associative array
  
}
