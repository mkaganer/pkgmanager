<?php
// B.H.

/**
 * @author mkaganer
 * @desc Thrown by the connection class when SQL error is encoutered
 */
class sql_error extends Exception {
    public $connection;
    public $query;
    
    public function __construct($message, $con, $query=null) {
        parent::__construct($message."\n The query was: $query");
        $this->connection = $con;
        $this->query = $query;
    }
    
}
