<?php
/*
  B.H.

  pkgmanager - A Class packages manager and class library for PHP 5
  Copyright (C) 2009-2011 Mordechay Kaganer (mkaganer@gmail.com)

  This program is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program.  If not, see <http://www.gnu.org/licenses/>.

  Note: this GNU GPL license disclaimer applies to all files contained
  in this directory and all it's subdirectories
*/

// Main initialization script that is used to initialize the package system

if (version_compare("5.1.0",PHP_VERSION,">")) die("pkgmanager requires PHP5 or later!");

define("_PKGMAN_ROOT",rtrim(dirname(__FILE__),"\\/"));

// if true, will use spl_autoload_register(), else will define __autoload functions
if (!defined('_PKGMAN_USE_SPL_AUTOLOAD')) define("_PKGMAN_USE_SPL_AUTOLOAD", true);

require(_PKGMAN_ROOT."/pkgman_manager.class.php");
require(_PKGMAN_ROOT."/pkgman_package.class.php");

/* @var pkgman_manager */
$_pkgman = pkgman_manager::get_instance();

if (_PKGMAN_USE_SPL_AUTOLOAD) {
    function _pkgman_autoload($class) {
        pkgman_manager::$instance->load_class($class);
    }
    
    spl_autoload_register('_pkgman_autoload');
} else {
    // if not using spl_autoload
    function __autoload($class) {
        pkgman_manager::$instance->load_class($class);
    }
}
