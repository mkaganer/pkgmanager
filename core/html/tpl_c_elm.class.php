<?php
// B.H.

// compiled template's element (variable, etc)
abstract class html_tpl_c_elm {

  public $plaintext = false; // if true, the value must be rendered using htmlspecialchars

  public static function create_elm($tpl,$type,$expr) {
    switch($type) {
    case 'var':
      return new html_tpl_c_var($tpl,$expr);
    case 'lang':
      return new html_tpl_c_lang($expr);
    case 'url':
      return new html_tpl_c_url($expr);
    case 'include':
      return new html_tpl_c_include($tpl->tplman->get_tpl($expr));
    case 'openx':
      return new html_tpl_c_openx($expr);
    default:
      throw new Exception("Invalid type [$type]!");
    }
  }
  
  abstract function render($block);
}
