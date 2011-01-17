<?php
// B.H.

class login_provider_simple extends login_provider {
    
    protected $login_data;

    public function __construct($login_data) {
        $this->login_data = $login_data;
    }
    
    public function get_session() {
        if (!empty($this->_session)) return $this->_session;
        $sdata =& $this->init_php_session();
        if (empty($sdata) || empty($sdata['username']) || empty($sdata['level'])) 
            return $this->_session = $this->get_anonymous_session();
        
        return $this->_session = new login_session($sdata['username'],$sdata['level'],
            isset($sdata['roles'])?$sdata['roles']:null);
    }
    
    /**
     * @param array $credetials
     * @return login_session
     */
    public function login($credetials) {
        if ($this->_session) $this->_session = null;
        $sdata =& $this->init_php_session();
        foreach ($sdata as $k => $v) unset($sdata[$k]); 
        
        $username = (string)@$credetials['username'];
        if (!isset($this->login_data[$username])) return false;
        $ldata = $this->login_data[$username];
        if ($credetials['password']!=$ldata['password']) return false;
        $level = empty($ldata['level'])?login_session::USER:intval($ldata['level']);
        $roles = empty($ldata['roles'])?array():array_flip($ldata['roles']);
        
        $sdata['username'] = $username;
        $sdata['level'] = $level;
        $sdata['roles'] = $roles;
        return $this->get_session();
    }

    public function logout() {
        if ($this->_session) $this->_session = null;
        $sdata =& $this->init_php_session();
        foreach ($sdata as $k => $v) unset($sdata[$k]); 
        return $this->_session = $this->get_anonymous_session();
    }
    
}