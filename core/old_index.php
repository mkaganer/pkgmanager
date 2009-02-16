<?php
// B.H.

if (defined("__PKGMAN_ACTIVE")) throw new Exception("PKGMAN: double include!");
if (!is_array($pkgman_import)) throw new Exception("PKGMAN: include without \$pkgman_import defined!");

define("__PKGMAN_DIR", (dirname(realpath(__FILE__)).'/'));
define("__PKGMAN_ACTIVE",true);

function pkgman_import_package($pkg) {
  global $pkgman_packages,$pkgman_default_config,$pkgman_config;
  if (!preg_match('#[./a-zA-Z0-9\\-]+#',$pkg)) throw new Exception("PKGMAN: Bad package name [$pkg]!");
  $parts = explode('/',$pkg);
  foreach($parts as $part) {
    if (strlen($part)<1) throw new Exception("PKGMAN: Bad package name [$pkg]!");
  }
  $pkg = implode('/',$parts);
  $pkg_prefix = implode('_',$parts).'_'; // package's class name prefix
  $pkg_path = __PKGMAN_DIR.$pkg;
  if (!is_dir($pkg_path)) throw new Exception("PKGMAN: Importing non-existent package [$pkg]!");
  if (isset($pkgman_packages[$pkg])) return; // already present
  $pkg_conf = isset($pkgman_default_config[$pkg])?$pkgman_default_config[$pkg]:array();
  if ((isset($pkgman_config[$pkg])) && is_array($pkgman_config[$pkg])) {
    $pkg_conf = array_merge($pkg_conf,$pkgman_config[$pkg]);
  }
  if (is_array($pkg_conf['_depends'])) {
    foreach($pkg_conf['_depends'] as $dep_pkg) pkgman_import_package($dep_pkg);
  }
  $pkgman_packages[$pkg] = array(
    'path' => $pkg_path.'/',
    'prefix' => $pkg_prefix,
    'conf' => $pkg_conf,
  );
  if (file_exists($pkg_path.'/init.php')) require($pkg_path.'/init.php');
}

// load any default package configurations
$pkgman_default_config = array();
if (file_exists(__PKGMAN_DIR.'pkgman_config.php')) require(__PKGMAN_DIR.'pkgman_config.php');

$pkgman_packages = array();
foreach($pkgman_import as $pkg) {
  pkgman_import_package($pkg);
}

// finally, we define the __autoload function to catch non-existent class loading
// class names are in a form of pkg_subpkg_class
function __autoload($class) {
  global $pkgman_packages;
  foreach($pkgman_packages as $pkg) {
    $prefix = $pkg['prefix'];
    if (strlen($class)<=strlen($prefix)) continue;
    if (substr_compare($class,$prefix,0,strlen($prefix))==0) {
      $class_file = $pkg['path'].$class.'.class.php';
      if (!file_exists($class_file)) continue;
      //echo "<pre dir=\"ltr\">PKGMAN: load $class_file</pre>";
      require($class_file);
      return;
    }
  }
}

?>
