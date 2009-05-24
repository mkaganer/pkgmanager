<?php
// B.H.

/**
 * @desc Package config file (optional)
 * Predefined variables: $pkg_name, $path, $prefix
 */

/**
 * @desc Package descriptor - Default config for the package
 * @var array $config
 */
$config = array(

  // default charset to be used by the connection
  'charset' => 'utf8',

  // If true,  throw sql_exception's on SQL errors
  // otherwise query will return false
  'throw_on_error' => true,

  'drivers' => array(
    'mysqli' => 'sql_mysqli_connection',
    'mysql' => 'sql_mysql_connection'
  ),

  'default_model_provider' => 'sql_model_provider',

);

/*
     * Optional: package control class (must inherit pkgman_package)
     Example:
     class <name> extends pkgman_package {
     ....
     }
     $package = new <name>($pkg_name,$prefix,$path,$config)

     * (if $package is not defined, pkgman_manager will automatically
     * create an instance of a generic pkgman_package class)
*/
?>
