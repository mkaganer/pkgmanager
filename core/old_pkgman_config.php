<?php
// B.H.

$pkgman_default_config = array (
  'sql' => array(
    'default_connection' => array('mysqli','localhost','user','pass','db'),
    'default_charset' => 'utf-8',
  ),
  'sql/xml' => array(
    '_depends' => array('sql'),
  ),
  'html' => array(
    'form_element_map' => array(
      'text' => 'html_form_text', 'password' => 'html_form_text', 'hidden' => 'html_form_text',
      'radio' => 'html_form_radio', 'checkbox' => 'html_form_checkbox',
      'select' => 'html_form_select', 'mradio' => 'html_form_select',
      'textarea' => 'html_form_textarea',
      'submit' => 'html_form_submit',
      'image' => 'html_form_image',
    ),
    'show_flash' => "<script type=\"text/javascript\">show_flash('%s',%d,%d,'#ffffff','transparent');</script>",
    'phpthumb_url' => '/pTb/phpThumb.php',
    'thumb_sizes' => array(
      'test64' => array('w'=>64),
      'test320' => array('w'=>320,'h'=>240,'zc'=>1),
    ),
  ),
);

?>