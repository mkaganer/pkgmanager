<?php
// B.H.

/**
 * Abstract base class to be inherited by all login provider classes. Defines basic API and common functions
 * for login providers 
 * @author mkaganer
 */
class login_provider {
    
    /**
     * @desc session cache
     * @var login_session
     */
    protected $_session;
    
    protected $session_key = 'pkgman_login_data';
    
    /**
     * @return login_session
     */
    public abstract function get_session(); 
    
    /**
     * @param array $credetials
     * @return login_session
     */
    public abstract function login($credentials);
    
    /**
     * @return login_session
     */
    public abstract function logout();
    
    public function is_anonymous() {
        return $this->get_session()->is_anonymous();
    }
    
    public function is_admin() {
        return $this->get_session()->is_admin();
    }
    
    /**
     * Check if the current user session has sufficient privileges. 
     * Note that the SUPERADMIN level is considered as if he has all the roles (no role check done)
     * @param int $level minimal access (one of login_session:: level constants)
     * @param array|string $roles one (as string) or more (as array) roles that are required
     * @return boolean
     */
    public function is_granted($level,$roles=null) {
        return $this->get_session()->is_granted($level,$roles);
    }
    
    
    protected function get_anonymous_session() {
        return new login_session(null,login_session::ANONYMOUS);
    }
    
    protected function & init_php_session() {
        if (!session_id()) session_start();
        if (!isset($_SESSION[$this->session_key]) || !is_array($_SESSION[$this->session_key]))
            $_SESSION[$this->session_key] = array();
        return $_SESSION[$this->session_key];
    }
    
}
