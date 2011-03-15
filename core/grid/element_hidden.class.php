<?php
// B.H.

class grid_element_hidden extends grid_element {

    public function init_element() {
        $this->element = new html_element('input',array('type'=>'hidden','id'=>$this->id,
            'name'=>$this->name));
    }
    
    public function set_value($value) {
        $this->element->attr['value'] = $value;
    }
    
    protected function render($param=null) {
        return $this->element->get_html();
    }
    
}