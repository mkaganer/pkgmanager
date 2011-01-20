<?php
// B.H.

class login_provider_simple extends login_provider {
    
    private static $token_salt = '770n#2vvx$&aaP2D$Rv770';
    
    protected $login_data;

    public function __construct($login_data) {
        $this->login_data = $login_data;
    }
    
    public function get_session() {
        if (!empty($this->_session)) return $this->_session;
        $sdata =& $this->init_php_session();
        $d = $this->get_session_data($sdata);
        if (!$d) {
            $sdata = array(); // invalidate the session 
            return $this->_session = $this->get_anonymous_session();
        }
        return $this->_session = new login_session($this,$d['username'],$d['level'],$d['roles']);
    }
    
    private function get_session_data(&$sdata) {
        if (empty($sdata) || empty($sdata['username']) || empty($sdata['token'])) return false;
        
        $username = (string)$sdata['username'];
        if (!isset($this->login_data[$username])) return false;
        $ldata = $this->login_data[$username];
        
        if ($sdata['token']!=md5(self::$token_salt.$username.$ldata['password'])) return false;
        
        return array(
            'username' => $username,
            'level' => empty($ldata['level'])?login_session::USER:intval($ldata['level']),
            'roles' => empty($ldata['roles'])?array():$ldata['roles'],
        );
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
        
        $sdata['username'] = $username;
        $sdata['token'] = md5(self::$token_salt.$credetials['username'].$credetials['password']);
        return $this->get_session();
    }

    public function logout() {
        if ($this->_session) $this->_session = null;
        $sdata =& $this->init_php_session();
        foreach ($sdata as $k => $v) unset($sdata[$k]); 
        return $this->_session = $this->get_anonymous_session();
    }
    
}