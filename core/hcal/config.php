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
    'lang' => 'he',
    'location' => 'jerusalem',
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
