<?php
require_once 'core/init.php';

if (Input::exists()) {
    if (Token::check(Input::get('token'))) {
        
        $validate = new Validate();
        $validation = $validate->check($_POST, array(
            'username' => array(
                'required' => TRUE,
                'min' => 2,
                'max' => 20,
                'unique' => 'users'
            ),
            'password' => array(
                'required' => TRUE,
                'min' => 6,
            ),
            'password_again' => array(
                'required' => TRUE,
                'matches' => 'password'
            ),
            'name' => array(
                'required' => TRUE,
                'min' => 2,
                'max' => 50
            )
        ));

        if ($validation->passed()) {
            //Register User
            $user = new User();
            $salt = Hash::salt(30); 
            
            try {
                $user->create(array(
                    'username' => Input::get('username'),
                    'password' => Hash::make(Input::get('password'), $salt),
                    'salt' => $salt,
                    'name' => Input::get('name'),
                    'joined' => date('Y-m-d H:i:s'),
                    'group' => 1,
                ));
                
                Session::flash('home','You have been registered and can now log in.');
                Redirect::to(404);
                
            } catch (Exception $exc) {
                // catch exception
                die ($exc->getMessage());
            }
                } else {
            // Output Errors
            foreach ($validation->errors() as $error) {
                echo $error . "<br/>";
            }
        }
    }
}
?>

<form action="" method="POST" autocomplete="off">
    <div class="field">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" value="<?php if(Input::exists())echo escape(Input::get('username')); ?>" autocomplete="off"
    </div>
    
    <div class="field">
        <label for="password">Choose a Password</label>
        <input type="password" name="password" id="password">
    </div>
    
    <div class="field">
        <label for="password_again>">Enter Password again</label>
        <input type="password" name="password_again" id="password_again">
    </div>
    
    <div class="field">
        <label for="name">Enter your name</label>
        <input type="text" name="name" id="name" value="<?php if(Input::exists())echo escape(Input::get('name')); ?>"> 
    </div>
    <input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
    <input type="submit" value="Register">
</form>