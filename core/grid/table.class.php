<?php
// B.H.

class grid_table {
    
    /**
     * @var sql_connection
     */
    public $xlink;
    
    /**
     * @var array ( grid_column )
     */
    public $columns;
    
    /**
     * @desc Basic SQL query for showing the grid (w/o limit clause!)
     * @var string
     */
    public $query;
    
    /**
     * @desc Helper query to get the total number of rows availiable in $this->query result
     * @var string
     */
    public $count_query;
    
    /**
     * @var html_url
     */
    public $base_url;
    
    public $param_from;
    
    public $rows_per_page;
    
    /**
     * @var string
     */
    public $id_column;
    
    /**
     * 
     * @var bool
     */
    public $sql_limit = true;
    
    /**
     * @param string $query
     * @param string $count_query
     * @param html_url $base_url
     * @param sql_connection $xlink
     */
    public function __construct($query, $count_query, $base_url, $xlink=null) {
        $pkg = pkgman_manager::getp('grid');
        $this->columns = array();
        $this->query = $query;
        $this->count_query = $count_query;
        $this->base_url = $base_url;
        $this->param_from = $pkg->config['param_from'];
        $this->rows_per_page = $pkg->config['rows_per_page'];
        $this->id_column = $pkg->config['default_id_column'];
        if ($xlink instanceof sql_connection) $this->xlink = $xlink;
        else $this->xlink = sql_connection::get_connection();
    }
    
    /**
     * @desc $columns = array (
     *      'col1' => array(
     *          ['type'=>'column_type',]
     *          ['header' => 'header title text',]
     *          .... custom params for the column
     *      ),
     *      .... more columns
     * )
     * @param array $columns
     * @return void
     */
    public function add_columns($columns) {
        $pkg = pkgman_manager::getp('grid');
        foreach ($columns  as $name => $params) {
            if (is_numeric($name)) throw new Exception("Invalid columns data");
            
            if (isset($params['type'])) $type = $pkg->config['column_types'][$params['type']];
            else $type = $pkg->config['column_types'][0];
            
            if (!class_exists($type,true)) 
                throw new Exception("Invalid column [$name] - class [$type] does not exist");
            $column = new $type($this,$name, $params);
            $this->columns[$name] = $column;
        }
    }
    
    
    public function render() {
        
    }
    
}