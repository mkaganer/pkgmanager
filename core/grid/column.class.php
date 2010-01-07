<?php
// B.H.

/**
 * @author mkaganer
 * @desc Base class for grid_table columns 
 */
class grid_column {
    
    /**
     * @desc the parent table object
     * @var grid_table
     */
    public $table;
    
    /**
     * @var string
     */
    public $name;
    
    /**
     * @var array
     */
    public $params;
    
    /**
     * @desc Note: all custom column implementations must have the same constructor paramters
     * Called from grid_table::add_columns()
     * @param string $name
     * @param array $params
     */
    public function __construct($table, $name, $params) {
        $this->table = $table;
        $this->name = $name;
        $this->params = $params;
    }
    
    /**
     * @return string
     */
    public function render_th() {
        
    }
    
    /**
     * @return string
     */
    public function render_td() {
        
    }
}