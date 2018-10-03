<?php
require_once '../../core/init.php';
include '../includes/header.php';
if (Input::exists()) { // Has form been submitted?
	if (Token::check(Input::get('token'))) {
		$validate = new Validate();
		$validation = $validate->check($_POST, array(
			'email' => array(
				'name' => 'Email Address',
				'required' => true
			),
			'password' => array(
				'name' => 'Password',
				'required' => true
			)
		));
		if ($validation->passed()) {
			// Log user in
			$user = new User();
			
			$remember = (Input::get('remember') === 'on') ? true : false;

			$login = $user->login(Input::get('email'), Input::get('password'), $remember);

			if ($login) {
				Redirect::to('index.php');
			} else {
				echo 'Could not log you in';
			}
		} else {
			foreach ($validation->errors() as $error) {
				echo $error, '<br>';
			}
		}
	}
}	

?>
<form action="" method="POST" role="form">
	<legend>Login</legend>
	<div class="form-group">
		<label for="email">Email Address</label>
		<input type="text" name="email" class="form-control" id="email" placeholder="Email Address">
	</div>
	<div class="form-group">
		<label for="password">Password</label>
		<input type="password" name="password" class="form-control" id="password" placeholder="Password">
	</div>
	<div class="form-group">
		<div class="checkbox">
			<label><input type="checkbox" name="remember">Remember Me</label>
		</div>
	</div>
	<input type="hidden" name="token" value="<?= Token::generate(); ?>">
	<button type="submit" class="btn btn-primary"> Login </button>
</form>
<?php include '../includes/footer.php'; ?>