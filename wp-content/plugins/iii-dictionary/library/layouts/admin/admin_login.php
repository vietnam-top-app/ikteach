<?php

	if(isset($_POST['wp-submit']))
	{
		$creds['user_login'] = $_POST['log'];
		$creds['user_password'] = $_POST['pwd'];
		//$creds['remember'] = true;
		$user = wp_signon( $creds, false );
		if(is_wp_error($user))
		{
			ik_enqueue_messages('Please check your Login Email address or Password and try it again.', 'error');					
		}
		else {
			$redirect_to = isset($_SESSION['mw_referer']) ? $_SESSION['mw_referer'] : home_url();
			wp_redirect($redirect_to);
			exit;
		}
	}
?>
<?php get_dict_header('Admin Login') ?>
<?php get_dict_page_title('Admin Login', 'admin-page') ?>

	<div class="row">
		<div class="col-md-9">
			<form action="<?php echo home_url() ?>/?r=admin-login" method="post">
				<div class="row">
					<div class="col-md-12">
						<div class="form-group">
							<label for="user_login">Username or e-mail</label>
							<input type="text" class="form-control" id="user_login" name="log">
						</div>     
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<div class="form-group">
							<label for="user_password">Password</label>
							<input type="password" class="form-control" id="user_password" name="pwd">
						</div>     
					</div>
				</div>

				<div class="row">
					<div class="col-md-9">
						<div class="form-group">
							<button name="wp-submit" type="submit" class="btn btn-default btn-block orange login"><span class="icon-user"></span>Login</button>
						</div>     
					</div>
				</div>
				<input name="redirect_to" value="<?php echo home_url() ?>" type="hidden">
			</form>
		</div>
	</div>

<?php get_dict_footer() ?>