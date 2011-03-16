<?php
// B.H.

/**
 * @desc Base class for the package engine implementation. Maintains a list of package "roots" locations
 * where the system will look for a package directories to import.
 * @author mkaganer
 */
class pkgman_manager {

  /**
   * @desc array of root paths for packages library
   * @var array of string
   */
  public $roots;

  /**
   * @desc array of ("pkg" => pkgman_package) for all imported packages
   * @var array
   */
  private $imported;

  // enforce single instance

  /**
   * @var pkgman_manager
   */
  public static $instance = null;

  /**
   * @return pkgman_manager
   */
  public static function get_instance() {
    if (!empty(self::$instance)) return self::$instance;
    return self::$instance = new self();
  }

  /**
   * @desc Optimized shortcut to pkgman_manager::get_instance()->get()
   * @param string $package package name
   * @return pkgman_package
   */
  public static function getp($package) {
    if (!empty(self::$instance)) return self::$instance->get($package);
  	self::$instance = new self();
  	return self::$instance->get($package);
  }

  protected function __construct() {
    $this->roots = array(_PKGMAN_ROOT);
    $this->imported = array();
  }

  /**
   * @desc Add file system path where to look for the directories with packages
   * @param string $root
   */
  public function add_root($root) {
    $root = realpath(rtrim($root,"\\/"));
    if (!is_dir($root)) throw new Exception("add_root($root) is not a directory!");
    if (in_array($root,$this->roots)) return;
    $this->roots[] = $root;
  }

  /**
   * @desc Get package's descriptor class
   * @param string $package
   * @return pkgman_package
   */
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

  /**
   * @desc Looks for a package directory under the defined root paths and if found performs
   * package's initialization loading config.php (if exists) and creating a pkgman_package instance.
   * Also, if the package states that it depends on some other packages, they will be imported
   * automatically.
   * @param string $pkg - package to import
   * @param array $config - custom config passed by application
   */
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

  /**
   * @desc Accepts argument list where each argument can be a package name to import or array.
   * For array arguments, each entry may be a package name or key=>value pair like this:
   * $_pkgman->import('html',array('sql'=>$config,'utils','app'));
   */
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

  /**
   * @desc Called from  __autoload() to load a specific class must. Returns true on success,
   * or false if class is not found.
   * @param string $class_name - a class to load
   * @param bool $test_only - if true, do not actually load class, only check if it is present
   * @return bool
   */
  public function load_class($class,$test_only=false) {
  	$base = microtime();
  	// when called not from __autoload, the required class may be already defined
  	if (class_exists($class,false)||interface_exists($class,false)) return true;
    foreach($this->imported as $pkg) {
      if (strpos($class,$pkg->prefix)!==0) continue;
      if ($pkg->load_class($class,$test_only)) {
        if (defined("_TRACE_LOAD_CLASS"))
            $GLOBALS['_trace_log']->snapshot("load_class($class)",$base);
      	return true;
      }
    }
    if (defined("_TRACE_LOAD_CLASS"))
        $GLOBALS['_trace_log']->snapshot("load_class($class)",$base);
    return false;
  }

}

?>