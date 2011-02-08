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
     * @var string
     */
    public $default_orderby;
    
    /**
     * @desc Helper query to get the total number of rows availiable in $this->query result
     * @var string
     */
    public $count_query;
    
    /**
     * URL pointing back to the current page to allow paging and sorting links
     * @var html_url
     */
    public $base_url;
    
    /* see config.php for explanation of theese parameters */
    
    /**
     * default table element attributes
     * @var array
     */
    public $table_attr;
    
    /**
     * @var array
     */
    public $tr_classes;
    
    /**
     * @var boolean
     */
    public $use_paging;
    
    /**
     * @var string
     */
    public $paging_var;
    
    /**
     * @var string
     */
    public $order_var;
    
    /**
     * @var int
     */
    public $rows_per_page;
    
    /**
     * @var string
     */
    public $id_column;

    /**
     * @var pkgman_package
     */
    private $pkg;
    
    
    /**
     * The current row data
     * @var array
     */
    public $row;
    
    /**
     * the primary key value for the current row
     * @var mixed
     */
    public $id_value;
    
    /**
     * @var html_element
     */
    public $table;
    
    /**
     * @var html_element
     */
    public $tr;
    
    /**
     * @var html_element
     */
    public $cell;
    
    /**
     * @param string $query
     * @param string $count_query
     * @param html_url $base_url
     * @param sql_connection $xlink
     */
    public function __construct($query, $default_orderby, $count_query,html_url $base_url) {
        $this->pkg = pkgman_manager::getp('grid');
        $c =& $this->pkg->config;
        $this->columns = array();
        $this->query = $query;
        $this->default_orderby = $default_orderby;
        $this->count_query = $count_query;
        $this->base_url = $base_url;
        $this->table_attr = $c['table_attr'];
        $this->tr_classes = (array)$c['tr_classes'];
        $this->use_paging = $c['use_paging'];
        $this->paging_var = $c['paging_var'];
        $this->order_var = $c['order_var'];
        $this->rows_per_page = $c['rows_per_page'];
        $this->id_column = $c['default_id_column'];
        if (!empty($xlink)) $this->xlink = $xlink;
        elseif (is_object($c['xlink'])) $this->xlink = $c['xlink'];
        else $this->xlink = sql_connection::get_connection();
        if (empty($this->xlink)) throw new Exception('No SQL connection');
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
        foreach ($columns  as $name => $params) {
            if (isset($params['type'])) $type = $this->pkg->config['column_types'][$params['type']];
            else $type = $this->pkg->config['column_types'][0];
            
            if (!class_exists($type,true)) 
                throw new Exception("Invalid column [$name] - class [$type] does not exist");
            $column = new $type($this,$name, $params);
            $this->columns[$name] = $column;
        }
    }
    
    
    /**
     * @return html_block
     */
    public function render() {
        $res = new html_block();
        $this->table = new html_element('table');
        $this->table->attr = $this->table_attr;
        $res->add($this->table);
        
        $this->table->add($thead = new html_element('thead'));
        $this->table->add($tbody = new html_element('tbody'));
        $thead->add($this->tr = new html_element('tr')); 
        
        // create the header row:
        foreach ($this->columns as $col_name => $col) {
            $this->tr->add($this->cell = new html_element('th'));
            if ($col->width>0) $this->cell->attr['width'] = $col->width;
            
            $inner = $col->render_th();
            if (!empty($inner)) $this->cell->add($inner);
        }
        
        // fetch the data
        $full_sql = $this->query.' order by '.$this->default_orderby;
        $query = $this->xlink->query($full_sql);
        $tr_class_cnt = 0; 
        while($query->arow($this->row)) {
            $this->id_value = $this->row[$this->id_column];
            
            $tbody->add($this->tr = new html_element('tr'));
            if (!empty($this->tr_classes[$tr_class_cnt])) $this->tr->attr['class'] = $this->tr_classes[$tr_class_cnt];
            if (!isset($this->tr_classes[++$tr_class_cnt])) $tr_class_cnt = 0;
            
            foreach ($this->columns as $col_name => $col) {
                $this->tr->add($this->cell = new html_element('td'));
                if ($col->width>0) $this->cell->attr['width'] = $col->width;
                
                $inner = $col->render_td($this->row[$col_name]);
                if (!empty($inner)) $this->cell->add($inner);
            }
        }
        
        return $res;
    }
    
}
