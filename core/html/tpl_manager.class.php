<?php
// B.H.

class html_tpl_manager {
  private $tpl_path;
  private $cache = array(); // holds compiled templates (html_tpl_compiled) cache
  
  /**
   * @return pkgmanager_package
   */
  public static function get_pkg() {
      static $_pkg;
      if (empty($_pkg)) return $_pkg = pkgman_manager::getp('html');
      return $_pkg;
  }
  
  public function __construct($path=null) {
    $pkg = self::get_pkg();
    if (empty($path)) $path = $pkg->config['tpl_path'];
    if (empty($path)) throw new Exception("Template path not specified!");
    $path = realpath(rtrim($path,'/\\'));
    if (!is_dir($path)) throw new Exception("Template path [$path] is not valid");
    $this->tpl_path = $path.'/';
  }
  
  private function load_template($name) {
    global $lang;
    if (!preg_match('#^[a-z0-9_\\-/]+$#iu',$name)) throw new Exception("Bad template name [$name]!");
    $base_path = $this->tpl_path.$name;
    $path = $base_path.".${lang}.html";
    if (!file_exists($path)) {
      $path = $this->tpl_path.$name.'.html';
      if (!file_exists($path)) throw new Exception("Non-existent or non-readable template [$path]!");
    }
    return ($this->cache[$name] = new html_tpl_compiled($this,$name,file_get_contents($path)));
  }
  
  public function get_tpl($name) {
    if (isset($this->cache[$name])) return $this->cache[$name];
    return $this->load_template($name);
  }
  
}
?>