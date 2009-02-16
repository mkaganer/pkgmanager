<?php
// B.H.

class pkgman_package {

  public $name; // package name (like "html/form")
  public $prefix; // classes prefix (like "html_form_")
  public $path; // path to the package
  
  public $config;

  public function __construct($pkg_name,$prefix,$path,$config=null) {
    $this->name = $pkg_name;
    $this->prefix = $prefix;
    $this->path = $path;
    $this->config = $config;
    if (!is_array($this->config)) $this->config = array();
  }
  
  // simple implementation of merge_config, but i hope it is sufficient
  public function merge_config($conf) {
    if (!is_array($conf)) return;
    $this->config = array_merge_recursive($this->config,$conf);
  }
  
  // called by the autoload engine to load a specific class
  // must return true on success, or false if class is not found
  // if $test_only == true, then do not parse the class's file, only check if it is present
  public function load_class($class_name,$test_only=false) {
    if (strcspn($class_name,"#=\"\\?*:/@|<>.\$") != strlen($class_name))
      die("Invalid class name: [$class_name]");
    $prefix = $this->prefix;
    if (substr($class_name,0,strlen($prefix)) == $prefix)
      $class_name = substr($class_name,strlen($prefix));
      else return false;
    $file = $this->path."/".$class_name.".class.php";
    if (!file_exists($file)) return false;
    if (!$test_only) include($file);
    return true;
  } 

}

?>