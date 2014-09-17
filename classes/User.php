<?php

class User {
    private $_db,
            $_data,
            $_sessionName,
            $_isLoggedIn,
            $_cookieName;
    

    public function __construct($user = NULL) {
        $this->_db = DB::getInstance();
        $this->_sessionName = Config::get('session/session_name');
        $this->_cookieName  = Config::get('remember/cookie_name');
        
        
        if(!$user) {
            if(Session::exists($this->_sessionName)) {
                $user = Session::get($this->_sessionName);
                
                if($this->find($user)) {
                    $this->_isLoggedIn = TRUE;
                } else {
                    // process logout
                }
            }
        } else  {
            $this->find($user);
        }
    }
    
    public function create($fields = array()) {
        if(!$this->_db->insert('users', $fields)) {
            throw new Exception('There was a problem creating an account.');
        }
    }
    
    public function find($user = NULL) {
        if($user) {
            $field = (is_numeric($user)) ? 'id' : 'username';
            $data = $this->_db->get('users', array($field, '=', $user));
            
            if($data->count()){
                $this->_data = $data->first();
                return TRUE;
            }
        }
        return FALSE;
    }
    
    public function login($username = NULL, $password = NULL, $remember = FALSE) {

        $user = $this->find($username);

        if(!$username && !$password && $this->exists()) {
            Session::put($this->_sessionName, $this->data()->id);
        } else {

            if ($user) {
                if ($this->data()->password === Hash::make($password, $this->data()->salt)) {
                    Session::put($this->_sessionName, $this->data()->id);

                    if ($remember) {
                        $hash = Hash::unique();

                        // Check if a Hash is stored in the database in the table "users_session"
                        $hashCheck = $this->_db->get('users_session', array('user_id', '=', $this->data()->id));

                        // if no Hash is found in the table "users_session", insert a Hash with the hash that is generated above.
                        if (!$hashCheck->count()) {
                            $this->_db->insert('users_session', array(
                                'user_id' => $this->data()->id,
                                'hash' => $hash
                            ));
                        } else {
                            // If a Hash is FOUND in the table "users_session" store the HASH value in the variable $hash.
                            $hash = $hashCheck->first()->hash;
                        }

                        Cookie::put($this->_cookieName, $hash, Config::get('remember/cookie_expiry'));
                    }

                    return TRUE;
                }
            }
        }

        return FALSE;
    }
    
    // Update Method for user's class
    
    public function update($fields = array(), $id = NULL) {
        
        if(!$id && $this->isLoggedIn()) {
            $id = $this->data()->id;
        }
        
        if(!$this->_db->update('users', $id , $fields)) {
            throw new Exception('There was a problem updating.');
        }
    }

    public function logout() {
        
        /* This logout functionality will 
         *  - Delete the Hash stored in the database for that user
         *  - Delete the current Session
         *  - Delete the cookie stored in the browser
         */
        
        $this->_db->delete('users_session', array('user_id', '=', $this->data()->id));
        Session::delete($this->_sessionName);
        Cookie::delete($this->_cookieName);
    }
    
    public function data() {
        return $this->_data;
    }
    
    public function isLoggedIn() {
        return $this->_isLoggedIn;
    }
    
    public function exists() {
        return (!empty($this->_data)) ? TRUE : FALSE;
    }
    
    public function hasPermission($key) {
        $group = $this->_db->get('groups', array('id', '=', $this->data()->group));
        
        if($group->count()) {
            $permissions = json_decode($group->first()->permissions, TRUE);
            
            if($permissions[$key] == TRUE) {
                return TRUE;
            }
        }
        return FALSE;
    }
}

?>