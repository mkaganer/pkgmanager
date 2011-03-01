<?php
// B.H.

class grid_header extends html_element {
    
    public function __construct($text,$icon=null) {
        $class = 'grid_pannel_header ui-widget-header ui-helper-clearfix';
        parent::__construct('h2',array('class'=>$class));
        $this->add($text);
    }
    
}