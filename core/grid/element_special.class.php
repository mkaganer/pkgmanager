<?php
// B.H.

class grid_element_special extends grid_element {

    public $special_value;
    
    public function init_element() {
        $this->element = null;
        $this->special_value = @$this->params['value'];
        if (empty($this->params['binding'])) $this->binding = array($this,'special_binding');
    }
    
    public function set_value($value) {
    }
    
    public function get_value(&$data) {
        return $this->special_value;
    }
    
    protected function render($param=null) {
        return '';
    }
    
    public function special_binding($binding_mode,grid_details $details, grid_element $elm, &$value, &$db_data) {
        $column_name = empty($elm->params['column_name'])?$elm->name:$elm->params['column_name'];
        if (!empty($this->params['raw_sql'])) {
            $column_name = '!'.$column_name;
        }
        if ($binding_mode==self::BINDING_TODB) {
            // $value => $db_data
            if (!empty($this->params['raw_sql']) && is_string($this->params['raw_sql'])) { 
                $db_data[$column_name] = $this->params['raw_sql'];
            } elseif (!is_null($value)) $db_data[$column_name] = $this->value;
        } elseif ($binding_mode==self::BINDING_FROMDB) {
            // $db_data => $value
            $value = @$this->special_value;
        }
    } 
    
}