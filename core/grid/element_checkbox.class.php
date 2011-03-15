<?php
// B.H.

class grid_element_checkbox extends grid_element {

    private $checkbox_elm;
    
    public function init_element() {
        $this->element = new html_block();
        $this->checkbox_elm = new html_element('input',array('type'=>'checkbox','id'=>$this->id,
            'name'=>$this->name,'class'=>'grid-element-checkbox', 'value'=>1));
        $this->element->add($this->checkbox_elm);
        if (!empty($this->params['label'])) {
            $this->element->add('<label class="grid-element-checkbox" for="'.htmlspecialchars($this->id).'">'.
                htmlspecialchars($this->params['label']).'</label>');
        }
    }
    
    public function set_value($value) {
        $v = trim(strtolower($value));
        if (!empty($this->params['is_yesno'])) $v = ($v=='y')?1:0;
        else $v = intval($v);
        if ($v) $this->checkbox_elm->attr['checked'] = 'checked';
        else unset($this->checkbox_elm->attr['checked']);
    }
    
    public function get_value(&$data, &$err_code) {
        $res = empty($data[$this->name])?0:1;
        return (empty($this->params['is_yesno'])?$res:($res?'Y':'N'));
    }
    
}