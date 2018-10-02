<?php
require_once 'core/init.php';
include 'includes/header.php';
$user = new User();
if (!$user->isLoggedIn()) {
	Redirect::to('index.php');
}
if (Session::exists('success')) {
	echo' <div class="alert alert-success">
			<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
			'. Session::flash('success') .'
	</div>';
}
if (Input::exists()) {
	if (Token::check(Input::get('token'))) {
		$validate = new Validate();
		$validation = $validate->check($_POST, array(
			'current_password' => array(
				'name' => 'Password',
				'required' => true,
				'min' => 4,
				'max' => 50
			),
			'new_password' => array(
				'name' => 'New Password',
				'required' => true,
				'min' => 6
			),
			'password_new_confirm' => array(
				'name' => 'New Password Confirmation',
				'required' => true,
				'min' => 6,
				'matches' => 'new_password'
			)
		));
		if ($validation->passed()) {
			
			if (Hash::make(Input::get('current_password'), $user->data()->salt) !== $user->data()->password) {
				echo '
				<div class="alert alert-danger">
					<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
					<strong>Please correct the following issues: <br></strong>
					<ul>
						<li> Your current password was entered incorrectly. </li>
					</ul>
				</div>';
			} else {
				$salt = Hash::salt(32);

				$user->update(array(
					'password' => Hash::make(Input::get('new_password'), $salt),
					'salt' => $salt
				));

				Session::flash('success', 'Your password has been updated.');
				Redirect::to('settings.php');
			}

		} else {
			echo '
			<div class="alert alert-danger">
				<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
				<strong>Please correct the following issues: <br></strong>
				<ul>';
				foreach ($validation->errors() as $error) {
					echo '<li>'. $error .'</li>';
				}
				echo '</ul>
			</div>';
		}
	}
}
?>
<form action="" method="POST" role="form">
	<legend>Update your password:</legend>
	<div class="row">
		<div class="col-xs-12 col-sm-12 col-md-12">
			<div class="form-group">
				<label for="current_password"> Current Password </label>
				<input type="password" class="form-control" id="current_password" name="current_password" placeholder="Password">
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-xs-12 col-sm-6 col-md-6">
			<div class="form-group">
				<label for="new_password"> New Password </label>
				<input type="password" class="form-control" id="new_password" name="new_password" placeholder="Password">
			</div>
		</div>
		<div class="col-xs-12 col-sm-6 col-md-6">
			<div class="form-group">
				<label for="password_new_confirm"> Password Confirmation </label>
				<input type="password" class="form-control" id="password_new_confirm" name="password_new_confirm" placeholder="Confirm Password">
			</div>
		</div>
	</div>
	
	<input type="hidden" name="token" value="<?= Token::generate(); ?>">
	<button type="submit" class="btn btn-primary"> Change Password </button>
</form>
<?php
include 'includes/footer.php';
?>?>