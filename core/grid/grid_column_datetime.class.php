<?php
// B.H.

class grid_column_datetime extends grid_column {
    
    public function __construct(grid_table $table, $name, $params) {
        parent::__construct($table, $name, $params);
        if (empty($this->params['format'])) $this->params['format'] = 'd/m/Y H:i';
    }
    
    public function render_td($value) {
        if (empty($value)) return '';
        if (is_numeric($value)) $ts = intval($value);
        else $ts = strtotime($value);
        return date($this->params['format'],$ts);
    }
}