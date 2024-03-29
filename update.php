<?php
require_once 'core/init.php';

$user = new User();

if(!$user->isLoggedIn()) {
    Redirect::to('index.php');
}

if(Input::exists()) {
    if(Token::check(Input::get('token'))) {
        $validate = new Validate();
        $validation = $validate->check($_POST, array(
            'name' => array(
                'required' => TRUE,
                'min'      => 2,
                'max'      => 50
            )
        ));
        
        if ($validation->passed()) {
            // Try and catch method because the user class will throw an exception
            
            try {
                $user->update(array(
                    'name' => Input::get('name')
                ));
                
                Session::flash('home', 'Your details have been updated.');
                Redirect::to('index.php');
                
            } catch (Exception $exc) {
                die ($exc->getMessage());
            }
                    
        } else {
            foreach ($validation->errors() as $error) {
                echo $error ."<br />";
            }
        }
    }
}

?>

<form action="" method="POST">
    <div class="field">
        <label for="name">Name</label>
        <input type="text" name="name" id="name" value="<?php echo escape($user->data()->name); ?>">
        
        <input type="submit" value="Update">
        <input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
    </div>
</form>
