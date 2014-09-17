<?php
session_start();

$GLOBALS['config'] = array(
    'mysql'    => array(
        'host'     => '127.0.0.1',
        'username' => 'root',
        'password' => '',
        'db'       => 'OOP_login'
    ),
    'remember' => array(
        'cookie_name'   => 'hash',
        'cookie_expiry' => 604800
    ),
    'session'  => array(
        'session_name' => 'user',
        'token_name'   => 'token'
    )  
); 

spl_autoload_register( function($class) {
    require_once 'classes/' . $class . '.php';
});

require_once 'functions/sanitize.php';

if(Cookie::exists(Config::get('remember/cookie_name')) && !Session::exists(Config::get('session/session_name'))){
    
    // Check if token does exists on the local client machine
    // Check if there isn't any session on local machine currently
    // If there's a cookie and no session, grab the value in the cookie and lookup in the database{table-> users_session}
    // Grab the user's ID in the users_session table and log the user in. 
    
    $hash = Cookie::get(Config::get('remember/cookie_name'));
    $hashCheck = DB::getInstance()->get('users_session', array('hash','=', $hash));
    
    if($hashCheck->count()) {
        $user = new User($hashCheck->first()->user_id);
        $user->login();
    }
}
?>

