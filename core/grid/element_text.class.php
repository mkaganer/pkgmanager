<?php
// B.H.

class grid_element_text extends grid_element {

    public function init_element() {
        $this->element = new html_element('input',array('type'=>'text'));
        $this->add($this->element);
    }
    
}