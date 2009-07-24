<?php
// B.H.

/**
 * @desc Builds SQL condition expression that may be inserted in WHERE clause.
 * Conditions may be grouped with AND or OR operator.
 * NOTE: this class generates SQL in MYSQL syntax, for other engines we may have to write another
 * implementation.
 * @author mkaganer
 */
class sql_mysql_filter extends sql_filter {

    /**
     * @desc Create a filter object with a given filter data
     * @param $filters
     */
    public function __construct($link,$filters,$operator=null,$prefix=null) {
    	$this->link = $link;
    	$this->filters = $filters;
    	if (!empty($operator)) $this->operator = $operator;
    	if (!empty($prefix)) $this->prefix = $prefix;
    }

    public function add_filters($filters) {
    	$this->filters = array_merge_recursive($this->filters,$filters);
    }

    private function parse_key(&$key) {
    	// note: in regex some 2-char operators must be before 1-char,
    	// otherwise > will be matched instead of >=, = instead of =0 e.t.c
    	// Maybe, there's better solution?
        if (preg_match('/^(<=|>=|<>|~|=0|=1|#!|=|<|>|#)?([^<>=~].*)$/',$key,$m)) {
        	$key = $m[2];
        	return (empty($m[1]))?"=":$m[1];
        } else {
        	return "=";
        }
    }

    public function render() {
        $res = array();
        if (empty($this->filters)) $res[] = "true";
        else {
            foreach ($this->filters as $key => $value) {
                if (is_object($value)) $value = $value->__toString();
                if (is_numeric($key)) {
                	// numeric filter keys designate "raw" conditions.
                	$res[] = $value; continue;
                }
                $raw_val = false;
                // ! prefix designate "raw" (SQL expression) value
                if ($key[0]=='!') { $raw_val = true; $key = substr($key,1); }
                $op = $this->parse_key($key);
                // add prefix if needed, we do not escape column names
                $key = (empty($this->prefix))?"`$key`":"{$this->prefix}.`$key`";
                $expr = "";
                if (is_bool($value)) $value = (int)$value;
                else if ((!is_numeric($value)) && (!is_array($value)) && (!$raw_val)) {
                	$value = '"'.$this->link->escape($value).'"';
                }
                if (is_array($value)) {
                	if ($op=='=') $op='#';
                	else if ($op=='<>') $op='#!';
                	if ($op=='#'||$op=='#!') {
                	    if (empty($value)) {
                	        // special case where we get an empty array
                	        // for "in" always false, for "not in" always true...
                	        $expr = ($op=='#')?'false':'true';
                	    } else {
                            $op = ($op=='#')?'in':'not in';
                            $val_list = array();
                            foreach ($value as $val) {
                                $val_v = (is_object($val))?$val->__toString():$val;
                                if ((!is_numeric($val_v)) && (!$raw_val))
                                    $val_v = '"'.$this->link->escape($val_v).'"';
                                $val_list[] = $val_v;
                            }
                            $expr = "$key $op (".implode(",",$val_list).")";
                	    }
                	} else
                	    throw new Exception("Operator '$op' is not supported with array value");
                } else switch ($op) {
                	case '=':
                	case '>':
                	case '<':
                	case '<=':
                    case '>=':
                    case '<>':
                		$expr = "$key $op $value";
                		break;
                    case '~':
                    	$expr = "$key like $value";
                    	break;
                    case '=0':
                    	$expr = "$key is null";
                    	break;
                    case '=1':
                        $expr = "$key is not null";
                        break;
                    default:
                    	throw new Exception("Unsupported operator '$op'");
                }
                $res[] = ($this->inner_parences)?"( $expr )":$expr;
            }
        }
        $res = implode(" ".$this->operator." ",$res);
        return ($this->outer_parences)?"( $res )":$res;
    }

    public function __toString() {
    	return $this->render();
    }
}

?>