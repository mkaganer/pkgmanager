<?php
// B.H.

class html_block_path_ex extends Exception {
  public $path;
  public function __construct($path) {
    $this->path = $path;
    parent::__construct("[$path]");
  }
}
?>