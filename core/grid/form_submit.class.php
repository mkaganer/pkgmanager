<?php
// B.H.

class grid_form_submit extends html_element {
    
    public $pkg;
    
    public function __construct($text=null,$name='submit') {
        $pkg = pkgman_manager::getp('grid');
        if (empty($text)) $text = $pkg->config['submit_text'];
        $class = 'grid-button';
        $form_id = $pkg->config['details_from_id'];
        parent::__construct('input',array(
            'class' => $class,
            'type' => 'submit',
            'name' => $name,
            'value' => $text,
        ));
    }
    
}