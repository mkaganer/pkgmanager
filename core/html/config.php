<?php
// B.H.

// Package config file (optional):

// Predefined variables:
// $pkg_name, $path, $prefix

// Default config for the package
$config = array(
  'form_element_map' => array(
    'text' => 'html_form_text',
    'password' => 'html_form_text',
    'hidden' => 'html_form_text',
    'radio' => 'html_form_radio',
    'checkbox' => 'html_form_checkbox',
    'select' => 'html_form_select',
    'mradio' => 'html_form_select',
    'mselect' => 'html_form_mselect',
    'textarea' => 'html_form_textarea',
    'submit' => 'html_form_submit',
    'image' => 'html_form_image',
  ),
  'show_flash' => "<script type=\"text/javascript\">show_flash('%s',%d,%d,'#ffffff','transparent');</script>",
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
