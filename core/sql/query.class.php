<?php
// B.H.

/**
 * @author mkaganer
 * @desc Generic SQL query wrapper class. Supports query parameters substitution and also
 * global parameters from package's config value 'query_params'.
 * Parameter names may be numeric or string 
 */
class sql_query {
    
    /**
     * @var string
     */
    public $query;
    
    /**
     * @var array
     */
    public $params;
    
    /**
     * @var sql_connection
     */
    public $xlink;
    
    /**
     * @var pkgman_package
     */
    private $_pkg;
    
    public function __construct($query,$xlink=null) {
        if ($xlink instanceof sql_connection) $this->xlink = $xlink;
        else $this->xlink = sql_connection::get_connection();
        
        $this->query = $query;
        $this->params = array();
    }
    
    /**
     * @desc Renders and executes the query and returns the result object 
     * @return sql_result|bool
     */
    public function execute() {
        $sql = $this->render();
        return $this->xlink->query($sql);
    }
    
    /**
     * @desc Usage assign('param1','val') or assign(array('p1'=>'v1',...))
     * @param array|string $params if array this will be merged into $this->params, else parameter name as string
     * @param string $val if $params is string/int then this is a value to assign
     * @return void
     */
    public function assign($params,$val=null) {
        if (is_array($params)) {
            $this->params = array_merge($this->params, $params);
        } else {
            // $params is scalar, it's a key
            $this->params[$params] = $val;
        }
    }
    
    /**
     * @desc Renders and returns the SQL statement with all parameters already substituted 
     * @return string
     */
    public function render() {
        return $this->substitute_params($this->query);
    }
    
    public function __toString() {
        return $this->render();
    }
    
    protected function substitute_params($sql) {
        $this->_pkg = pkgman_manager::getp('sql');
        if (empty($this->_pkg->config['global_params'])) $this->_pkg = null;
        $sql = preg_replace_callback('#\\${(!?[a-zA-Z0-9_\\-|]+)}#u',
            array($this,'substitute_params_callback'), $sql
        );
        return $sql;
    }
    
    public function substitute_params_callback($m) {
        $cmd = $m[1];
        $raw = false;
        if ($cmd[0]=='!') {
            $cmd = substr($cmd,1);
            $raw = true;
        }
        $data = explode('|',$cmd);
        if (isset($this->params[$data[0]])) $res = $this->params[$data[0]];
        elseif ($this->_pkg && isset($this->_pkg->config['global_params'][$data[0]])) {
            $res = $this->_pkg->config['global_params'][$data[0]];
        }
        else return $m[0]; // if the variable

        if (!$raw) $res = $this->xlink->escape($res);
        if (count($data)>1) for($i=1;isset($data[$i]);$i++) {
            switch ($data[$i]) {
                case 'int':
                    $res = intval($res);
                    break;
            }
        }
        return $res;
    }
    
}