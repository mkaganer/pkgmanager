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
        if (!empty($params[0]) && is_string($params[0])) $this->title = $params[0];
        else $this->title = empty($params['title'])?$name:$params['title'];
        if (!empty($params['width']) && ($params['width']>0)) $this->width = intval($params['width']);
    }
    
    /**
     * @return string|null if not empty value is returned it will be inserted into the cell element
     */
    public function render_th() {
        $res = '';
        $cell = $this->table->cell;
        if (!empty($this->params['header_icon'])) $res .= '<span class="ui-icon '.$this->params['header_icon'].
            '" style="float:left;margin-left:-5px"></span>';
        if (!empty($this->params['header_link'])) {
            $cell->attr['onclick'] = "document.location='{$this->params['header_link']}'";
            $cell->attr['class'] = trim(@$cell->attr['class'].' clickable');
        }
        $res .= htmlspecialchars($this->title);
        return $res;
    }
    
    /**
     * @return string|null if not empty value is returned it will be inserted into the cell element
     */
    public function render_td($value) {
        if (empty($this->params['callback'])) return htmlspecialchars($value);
        return call_user_func($this->params['callback'],$value, $this);
    }
}