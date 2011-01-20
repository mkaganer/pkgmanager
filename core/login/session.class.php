<?php
// B.H.

/**
 * Encapsulates the data which describes the currently logged in user session.
 * This class is provider-independant, and should be created by the specific login provider class 
 * @author mkaganer
 */
class login_session {
    
    /*
     * Levels:
     */
    const ANONYMOUS = 0;
    const BOT = 10;
    const USER = 20;
    const ADMIN = 50;
    const SUPERADMIN = 100;
    
    /**
     * @var login_provider
     */
    public $provider;
    
    /**
     * @var string
     */
    private $username;
    
    /**
     * Session-related data to be set by the session providers such as session id and so on
     * @var array
     */
    public $metadata;
    
    /**
     * @var unknown_type
     */
    private $level;
    
    /**
     * @var array array('role1'=>X,'role2'=>X,...)
     */
    private $roles;
    
    /**
     * @param string $username
     * @param int $level
     * @param array $roles
     */
    public function __construct($provider, $username, $level, $roles=null) {
        $this->provider = $provider;
        $this->username = $username;
        $this->metadata = array();
        $this->level = intval($level);
        $this->roles = array_flip(self::parse_roles($roles));
    }
    
    public function get_username() {
        return $this->username;
    }
    
    /**
     * Get the session access level
     * @return int
     */
    public function get_level() {
        return $this->level;
    }
    
    public function get_roles() {
        return $this->roles;
    }

    /**
     * @return boolean
     */
    public function is_anonymous() {
        return ($this->level==self::ANONYMOUS);
    }
    
    /**
     * @return boolean
     */
    public function is_admin() {
        return ($this->level>=self::ADMIN);
    }
    
    /**
     * Check if the current user session has sufficient privileges. 
     * Note that the SUPERADMIN level is considered as if he has all the roles (no role check done)
     * @param int $level minimal access (one of login_session:: level constants)
     * @param array|string $roles one (as string) or more (as array) roles that are required
     * @return boolean
     */
    public function is_granted($level=null,$roles=null) {
        if (!is_null($level) && $this->level<$level) return false;
        if ($this->level==self::SUPERADMIN) return true;
        
        // no roles requirements
        if (empty($roles)) return true;
        $roles = self::parse_roles($roles); 
        foreach ($roles as $role) if (!isset($this->roles[$role])) return false;
        return true;
    }
    
    public static function parse_roles($roles) {
        if (empty($roles)) return array();
        if (is_array($roles)) return $roles;
        $roles = array_map('trim',preg_split('/[\\s\\n,;]+/u',$roles));
        foreach($roles as $i => $r) if (empty($r)) unset($roles[$i]);
        return $roles;
    }
    
}