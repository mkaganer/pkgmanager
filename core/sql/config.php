<?php
// B.H.

// Package config file (optional):

// Predefined variables:
// $pkg_name, $path, $prefix

// Default config for the package
$config = array(
  // default charset to be used by the connection
  'charset' => 'utf-8',
  
  'drivers' => array(
    'mysqli' => 'sql_mysqli_connection',
  ),

);

// * Optional: package control class (must inherit pkgman_package)
// Example:
// class <name> extends pkgman_package {
// ....
// }
// $package = new <name>($pkg_name,$prefix,$path,$config)
//
// * (if $package is not defined, pkgman_manager will automatically
// * create an instance of a generic pkgman_package class)
 
?>
