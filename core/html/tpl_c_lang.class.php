<?php
// B.H.

class html_tpl_c_lang extends html_tpl_c_elm {

  private $path;

  public function __construct($path) {
    $this->path = $path;
  }
  
  public function render($block) {
    if (!function_exists('_lang')) 
      throw new Exception('You need to define _lang() function in order to use ${lang:...} template construct');
    return _lang($this->path);
  }
}
