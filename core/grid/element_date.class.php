<?php
// B.H.

class grid_element_date extends grid_element {
    
    private $elm_style;
    
    /**
     * @var html_element
     */
    private $element_1; // the datepicker
    
    /**
     * @var html_element
     */
    private $element_2; // optional time field
    
    public function init_element() {
        $this->element = new html_block();
        $this->element_1 = new html_element('input',array('type'=>'text','id'=>$this->id,
            'name'=>$this->name,'class'=>'grid-element-datepicker'));
        $this->element->add($this->element_1);
        if (!empty($this->params['with_time'])) {
            $this->element_2 = new html_element('input',array('type'=>'text',
                'name'=>$this->name.'__time','class'=>'grid-element-time'));
            $this->element->add($this->element_2);
            
        }
    }
    
    public function set_value($value) {
        $ts = strtotime($value);
        $this->element_1->attr['value'] = date('d/m/Y',$ts);
        if (!empty($this->element_2)) $this->element_2->attr['value'] = date('H:i',$ts);
    }
    
    public function get_value(&$data, &$err_code) {
        $val = trim($data[$this->name]); // the date part
        if (!preg_match('#^(\\d{2})/(\\d{2})/(\\d{4})$#u',$val,$m)) {
            // cannot parse the date
            $err_code = 'invalid_date';
            return null;
        }
        $sql = "$m[3]-$m[2]-$m[1]";
        
        if (!empty($this->element_2)) {
            $val2 = trim($data[$this->name.'__time']);
            if (preg_match('#^(\\d{2}):(\\d{2})$#u',$val2,$m)) $sql .= " $m[1]:$m[2]";
            elseif (preg_match('#^(\\d{2}):(\\d{2}):(\\d{2})$#u',$val2,$m)) $sql .= " $m[1]:$m[2]:$m[3]";
            elseif (!empty($val2)) $err_code = 'invalid_date';
        }
        return $sql;
    }   
    
}