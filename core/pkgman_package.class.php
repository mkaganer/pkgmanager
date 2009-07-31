<?php
// B.H.

/**
 * @desc Base class for package descriptors. A package may define a custom descriptor class which
 * must extend this class. In that case, you have to assign a class descriptor to $package varible
 * in config.php in package's directory.
 * @author mkaganer
 */
class pkgman_package {

  /**
   * @desc Package name (like "html/form")
   * @var string
   */
  public $name;


  /**
   * @desc classes prefix (like "html_form_")
   * @var string
   */
  public $prefix;


  /**
   * @desc A file system path to the package
   * @var string
   */
  public $path;

  /**
   * @desc Package's config, merged from default config in config.php and values passed when the class
   * is imported
   * @var array
   */
  public $config;

  public function __construct($pkg_name,$prefix,$path,$config=null) {
    $this->name = $pkg_name;
    $this->prefix = $prefix;
    $this->path = $path;
    $this->config = $config;
    if (!is_array($this->config)) $this->config = array();
  }

  /**
   * @desc Modify package's config using values from $conf.
   * This is very simple implementation of merge_config, but i hope it is sufficient
   * @param string $conf
   */
  public function merge_config($conf) {
    if (!is_array($conf)) return;
    //$this->config = array_merge_recursive($this->config,$conf);
    $my =& $this->config;
    foreach($conf as $k => $v) {
    	if (empty($my[$k])) $my[$k] = $v;
    	elseif (!is_array($my[$k])) $my[$k] = $v;
    	else {
    		if (is_array($v)) $my[$k] = array_merge($my[$k],$v);
    		else $my[$k][] = $v;
    	}
    }
  }

  /**
   * @desc Called by the autoload engine to load a specific class must return true on success,
   * or false if class is not found
   * @param string $class_name - a class to load
   * @param bool $test_only - if true, do not actually load class, only check if it is present
   * @return bool
   */
  public function load_class($class_name,$test_only=false) {
    if (strcspn($class_name,"#=\"\\?*:/@|<>.\$") != strlen($class_name))
      die("Invalid class name: [$class_name]");
    $prefix = $this->prefix;
    // check if the class belongs to our namespace:
    if (substr($class_name,0,strlen($prefix)) == $prefix)
      $class_name2 = substr($class_name,strlen($prefix));
      else return false;
    $file = "{$this->path}/${class_name}.class.php";
    if (!file_exists($file)) $file = "{$this->path}/${class_name2}.class.php";
    if (!file_exists($file)) return false;
    if (!$test_only) include($file);
    return true;
  }

}

?>