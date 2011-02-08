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
    
    
    public $title;
    
    public $width = null;
    
    /**
     * @desc Note: all custom column implementations must have the same constructor paramters
     * Called from grid_table::add_columns()
     * @param string $name
     * @param array $params
     */
    public function __construct(grid_table $table, $name, $params) {
        $this->table = $table;
        $this->name = $name;
        $this->params = $params;
        $this->title = empty($params['title'])?$name:$params['title'];
        if (!empty($params['width']) && ($params['width']>0)) $this->width = intval($params['width']);
    }
    
    /**
     * @return string|null if not empty value is returned it will be inserted into the cell element
     */
    public function render_th() {
        return htmlspecialchars($this->title);
    }
    
    /**
     * @return string|null if not empty value is returned it will be inserted into the cell element
     */
    public function render_td($value) {
        if (empty($this->params['callback'])) return htmlspecialchars($value);
        return call_user_func($this->params['callback'],$value, $this);
    }
}