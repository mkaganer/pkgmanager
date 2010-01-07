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

    // default get parameter for 'from' limit value (see grid_table::param_from) 
    'param_from' => 'grdf',

    // default rows per page
    'rows_per_page' => 20,

    'default_id_column' => 'id',

    'column_types' => array(
        0 => 'grid_column', // default column class
        'int' => 'grid_column_int', 
    ),

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
