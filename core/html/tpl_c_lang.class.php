<?php
// B.H.

class html_tpl_c_lang extends html_tpl_c_elm {

  private $path;

  public function __construct($path) {
    $this->path = $path;
  }
  
  public function render($block) {
    global $__lang;
    return $__lang[$this->path];
  }
}
