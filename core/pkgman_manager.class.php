<?php
// B.H.

class pkgman_manager {

  public $roots; // array of root paths for packages library

  private $imported; // array of ("pkg" => pkgman_package) for all imported packages
  
  // enforce single instance
  private static $instance = null;
  
  public static function get_instance() {
    if (!empty(self::$instance)) return self::$instance;
    return self::$instance = new self();
  }
  
  protected function __construct() {
    $this->roots = array(_PKGMAN_ROOT);
    $this->imported = array();
  }
  
  public function add_root($root) {
    $root = realpath(rtrim($root,"\\/"));
    if (!is_dir($root)) throw new Exception("add_root($root) is not a directory!");
    if (in_array($root,$this->roots)) return;
    $this->roots[] = $root;
  }
  
  public function get($package) {
    if (!isset($this->imported[$package])) return null;
    return $this->imported[$package];
  }
  
  private function init_package($pkg,$prefix,$path,&$_depends) {
    $pkg_name = $pkg;
    $path = realpath($path);
    if (empty($path)) throw new Exception("PKGMAN: Invalid path!");
    $config = null; // may be overriden by the included file
    $package = null;
    $depends = null;
    if (file_exists($path."/config.php")) require($path."/config.php");
    if (!is_object($package)) $package = new pkgman_package($pkg_name,$prefix,$path,$config);
    $_depends = $depends; 
    return $package;
  }
  
  public function import_package($pkg,$config=null) {
    if (strspn($pkg,"abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789/-") != strlen($pkg))
      throw new Exception("PKGMAN: Bad package name [$pkg]!");
    $parts = explode('/',trim($pkg,"/"));
    foreach($parts as $part) {
      if (strlen($part)<1) throw new Exception("PKGMAN: Bad package name [$pkg]!");
    }
    $pkg = implode('/',$parts);
    if (!isset($this->imported[$pkg])) {
      $pkg_prefix = implode('_',$parts).'_'; // package's class name prefix
      $path = null;
      foreach($this->roots as $r) if (is_dir($r."/".$pkg)) { $path = $r."/".$pkg; break; }
      if (empty($path)) throw new Exception("PKGMAN: Package [$pkg] not found!");
      $depends = null;
      $package = $this->init_package($pkg,$pkg_prefix,$path,$depends);
      $this->imported[$pkg] = $package;
      if (is_array($depends)) {
        foreach($depends as $dep_pkg) $this->import_package($dep_pkg);
      }
    } else {
      $package = $this->imported[$pkg];
    }
    // add/replace config data for the package 
    if (is_array($config)) $package->merge_config($config);
  }
  
  // accepts argument list where each entry may be a package name or array of ('package_name'=>config)
  public function import() {
    foreach(func_get_args() as $arg) {
      if (is_array($arg)) {
        foreach($arg as $key => $val) {
          if (is_int($key)) $this->import_package($val);
          else $this->import_package($key,$val);
        }
      } else {
        $this->import_package($arg);
      }
    }
  }
  
  // called from __autoload()
  // if success, return true, else false
  // if $test_only == true, then do not parse the class's file, only check if it is present
  public function load_class($class,$test_only=false) {
    foreach($this->imported as $pkg) {
      if (strpos($class,$pkg->prefix)!==0) continue;
      if ($pkg->load_class($class,$test_only)) return true;
    }
    return false;
  }

}

?>