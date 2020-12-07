<?php 
	
	//link download of apps with system of user.
	$link_url = ik_link_apps();
	$is_math_panel = is_math_panel();
	
	if(isset($_GET['action'])) {
		$action = $_GET['action'];
	}
	else {
		$action = 'login';
		$page_header_title = __('Login', 'iii-dictionary');
	}

	switch($action)
	{
		case 'login':

			$page_title_tag = __('Login', 'iii-dictionary');

			if(isset($_POST['wp-submit']))
			{
				$creds['user_login'] = $_POST['log'];
				$creds['user_password'] = $_POST['pwd'];
				//$creds['remember'] = true;
				$user = wp_signon($creds, false);

				if(is_wp_error($user))
				{
					ik_enqueue_messages(__('Please check your Login Email address or Password and try it again.', 'iii-dictionary'), 'error');

					if(!isset($_SESSION['login_tries'])) {
						$_SESSION['login_tries'] = 1;
					} else {
						$_SESSION['login_tries'] += 1;

						if($_SESSION['login_tries'] >= 3) {
							ik_enqueue_messages(__('Did you forget your password? Please try "Forgot Password"', 'iii-dictionary'), 'message');
						}
					}					
				}
				else {
					$user_id = wp_get_current_user();
					if(!$user_id->language_type){
						update_user_meta( $user_id->ID, 'language_type','en');	
					} 
					$_SESSION['notice-dialog'] = 1;
					if(isset($_SESSION['mw_referer'])){
						$segment = explode('/',$_SESSION['mw_referer']);
						if(isset($segment[3]) && $segment[3] == 'wp-content'){
							$_SESSION['mw_referer'] = locale_home_url();
						}
					}
					$_SESSION['mw_referer'] = isset($_SESSION['mw_referer']) ? $_SESSION['mw_referer'] : locale_home_url();

					wp_redirect($_SESSION['mw_referer']);
					exit;
				}
			}

			break;

		case 'forgotpassword' :
		
			$page_header_title = __('Lost Password', 'iii-dictionary');
			$page_title_tag = __('Lost Password', 'iii-dictionary');

			if(isset($_POST['wp-submit']))
			{
				$has_err = false;
				if(empty($_POST['user_login'])) {
					ik_enqueue_messages(__('Please enter a username or e-mail address.', 'iii-dictionary'), 'error');
					$has_err = true;
				}
				else if(is_email($_POST['user_login'])) {
					$user_data = get_user_by('email', trim($_POST['user_login']));
					if(empty($user_data)) {
						ik_enqueue_messages(__('There is no user registered with that email address.', 'iii-dictionary'), 'error');
						$has_err = true;
					}
				}
				else {
					$login = trim($_POST['user_login']);
					$user_data = get_user_by('login', $login);
				}

				if (!$user_data) {
					ik_enqueue_messages(__('Invalid username or e-mail.', 'iii-dictionary'), 'error');
					$has_err = true;
				}

				if(!$has_err)
				{
					// Redefining user_login ensures we return the right case in the email.
					$user_login = $user_data->user_login;
					$user_email = $user_data->user_email;

					// Generate something random for a password reset key.
					$key = wp_generate_password( 20, false );

					// Now insert the key, hashed, into the DB.
					if ( empty( $wp_hasher ) ) {
						require_once ABSPATH . WPINC . '/class-phpass.php';
						$wp_hasher = new PasswordHash( 8, true );
					}
					//$hashed = $wp_hasher->HashPassword( $key );
					$hashed = time() . ':' . $wp_hasher->HashPassword( $key );
					$wpdb->update( $wpdb->users, array( 'user_activation_key' => $hashed ), array( 'user_login' => $user_login ) );

					$message =  '<p>';
					$message .= __('Someone requested that the password be reset for the following account:', 'iii-dictionary') . " ";
					$message .= network_home_url() . " ";
					$message .= sprintf(__('Username: %s', 'iii-dictionary'), $user_login) . " </p><p></p><p>";
					$message .= __('If this was a mistake, just ignore this email and nothing will happen.', 'iii-dictionary') . " </p><p></p><p>";
					$message .= __('To reset your password, visit the following address:', 'iii-dictionary') . " </p><p></p><p>";
					$message .= '' . network_site_url('?r=login&action=resetpass&key=' . $key . '&login=' . rawurlencode($user_login)) . " </p>";

					$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

					$title = sprintf( __('[%s] Password Reset', 'iii-dictionary'), $blogname );

					$title = apply_filters( 'retrieve_password_title', $title );

					$message = apply_filters( 'retrieve_password_message', $message, $key, $user_login, $user_data );

					if ( $message && !wp_mail( $user_email, wp_specialchars_decode( $title ), $message ) ) {
						ik_enqueue_messages(__('The e-mail could not be sent.', 'iii-dictionary') . "<br>\n" . __('Possible reason: your host may have disabled the mail() function.', 'iii-dictionary'), 'error');
					}
					else {
						ik_enqueue_messages(__('Please check your e-mail for the confirmation link.', 'iii-dictionary'), 'message');
					}

					wp_redirect(locale_home_url() . '/?r=login');
					exit;
				}
			}
			else {
				if(isset( $_GET['error'])) {
					if('invalidkey' == $_GET['error']) {
						ik_enqueue_messages(__('Sorry, that key does not appear to be valid.', 'iii-dictionary'), 'error');
					}
					else if('expiredkey' == $_GET['error']) {
						ik_enqueue_messages(__('Sorry, that key has expired. Please try again.', 'iii-dictionary'), 'error');
					}
				}
			}

			break;

		case 'resetpass' :

			$page_header_title = __('Reset Password', 'iii-dictionary');
			$page_title_tag = __('Reset Password', 'iii-dictionary');

			if(isset($_GET['key']) && isset($_GET['login'])) {
				$rp_login = esc_html( stripslashes($_GET['login']) );
				$rp_key   = esc_html( $_GET['key'] );
				$user = check_password_reset_key( $rp_key, $rp_login );
			}
			else if(isset($_POST['rp_key']) && isset($_POST['rp_login'])) {
				$rp_login = esc_html( stripslashes($_POST['rp_login']) );
				$rp_key   = esc_html( $_POST['rp_key'] );
				$user = check_password_reset_key( $rp_key, $rp_login );
			}
			else {
				$user = false;
			}

			if(!$user || is_wp_error($user)) {
				if($user && $user->get_error_code() === 'expired_key')
					wp_redirect(site_url( '?r=login&action=forgotpassword&error=expiredkey' ));
				else
					wp_redirect(site_url( '?r=login&action=forgotpassword&error=invalidkey' ));
				exit;
			}

			if(isset($_POST['wp-submit']))
			{
				$has_err = false;
				if(isset($_POST['pass1']) && $_POST['pass1'] != $_POST['pass2']) {
					ik_enqueue_messages(__('The passwords do not match.', 'iii-dictionary'), 'error');
					$has_err = true;
				}

				if(!$has_err && isset( $_POST['pass1'] ) && !empty( $_POST['pass1'])) {
					reset_password($user, $_POST['pass1']);
					ik_enqueue_messages(__('Your password has been reset.', 'iii-dictionary'), 'success');

					wp_redirect(locale_home_url() . '/?r=login');
					exit;
				}
			}

			break;
	}
?>
<?php if(!$is_math_panel) : ?>
	<?php get_dict_header($page_title_tag) ?>
<?php else : ?>
	<?php get_math_header($page_title_tag, 'red-brown') ?>
<?php endif ?>
<?php get_dict_page_title($page_header_title) ?>

	<div class="row">
		<div class="col-md-12">

			<?php switch($action) :
				case 'login' : ?>

				<form action="<?php echo locale_home_url() ?>/?r=login" name="loginform" method="post">
					<div class="row">
						<div class="col-sm-10 col-md-8">
							<div class="form-group">
								<label for="username"><?php _e('Username (e-mail address)', 'iii-dictionary') ?></label>
								<input type="text" class="form-control" id="username" name="log" value="">
							</div>     
						</div>
						<div class="col-sm-10 col-md-8">
							<div class="form-group">
								<label for="password"><?php _e('Password', 'iii-dictionary') ?></label>
								<input type="password" class="form-control" id="password" name="pwd" value="">
							</div>     
						</div>
						<div class="clearfix"></div>
						<div class="col-sm-5 col-md-4">
							<div class="form-group">
								<label>&nbsp;</label>
								<button type="submit" class="btn btn-default btn-block orange login" name="wp-submit"><span class="icon-user"></span><?php _e('Login', 'iii-dictionary') ?></button>
							</div>     
						</div>
						<div class="col-sm-5 col-md-4">
							<div class="form-group">
								<label>&nbsp;</label>
								<a href="<?php echo locale_home_url() ?>/?r=signup" class="btn btn-default btn-block grey signup"><span class="icon-pencil"></span><?php _e('Sign-up', 'iii-dictionary') ?></a>
							</div>
						</div>
						<div class="col-sm-5 col-md-4 col-sm-offset-5 col-md-offset-4 text-right">
							<div class="form-group">
								<a href="<?php echo locale_home_url() ?>/?r=login&amp;action=forgotpassword" class="lblForgot"><?php _e('Forgot password?', 'iii-dictionary') ?> &gt;</a>
							</div>
						</div>
						<div class="clearfix"></div>
						<div class="col-sm-12">
							<div class="pull-left" style="margin-right: 15px">
								<img alt="" src="<?php echo get_template_directory_uri() ?>/library/images/desktop-shortcut.png">
							</div>
							<div class="pull-left">
								<p class="instructions-text"><?php _e('To make it easy to start up this site, download Desktop icon', 'iii-dictionary') ?></p>
								<span class="downloads-block"><span class="icon-download"></span> <?php _e('DOWNLOAD:', 'iii-dictionary') ?>
									<span class="downloads-links"><a href="<?php echo $link_url['mac']; ?>"><?php _e('Mac', 'iii-dictionary') ?></a> / <a href="<?php echo $link_url['win']; ?>"><?php _e('Windows', 'iii-dictionary') ?></a></span>
								</span>
								<p class="instructions-text"><?php _e('Mobile device, search for iklearn.com', 'iii-dictionary') ?></p>
							</div>
						</div>
					</div>  
					<input name="redirect_to" value="<?php echo locale_home_url() ?>" type="hidden">
				</form>

			<?php break;
				case 'forgotpassword' : ?>

				<div class="row">
					<div class="col-md-12">
						<div class="form-group has-error">
							<label class="control-label"><?php _e('Please enter your username or email address. You will receive a link to create a new password via email.', 'iii-dictionary') ?></label>
						</div>
					</div>
				</div>
				<form name="lostpasswordform" id="lostpasswordform" action="<?php echo esc_url(network_site_url('?r=login&action=forgotpassword')); ?>" method="post">
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label for="user_login" ><?php _e('Username or E-mail', 'iii-dictionary') ?></label>
								<input type="text" name="user_login" id="user_login" class="form-control" value="">
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<button type="submit" name="wp-submit" id="wp-submit" class="btn btn-default btn-block sky-blue">
									<span class="icon-switch"></span><?php esc_attr_e('Get New Password', 'iii-dictionary') ?>
								</button>
							</div>
						</div>
					</div>
				</form>

			<?php break;
				case 'resetpass' : ?>

				<div class="row">
					<div class="col-md-12">
						<div class="form-group has-error">
							<label class="control-label"><?php _e('Enter your new password below.', 'iii-dictionary') ?></label>
						</div>
					</div>
				</div>
				<form name="resetpassform" id="resetpassform" action="<?php echo esc_url( network_site_url( '?r=login&action="resetpass' ) ); ?>" method="post">
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label for="pass1"><?php _e('New password', 'iii-dictionary') ?></label>
								<input type="password" name="pass1" id="pass1" class="form-control" size="20" value="">
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label for="pass2"><?php _e('Confirm new password', 'iii-dictionary') ?></label>
								<input type="password" name="pass2" id="pass2" class="form-control" size="20" value="">
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<button type="submit" name="wp-submit" id="wp-submit" class="btn btn-default btn-block sky-blue">
									<span class="icon-switch"></span><?php esc_attr_e('Reset Password', 'iii-dictionary') ?>
								</button>
							</div>
						</div>
					</div>
					<input type="hidden" name="rp_key" value="<?php echo $rp_key ?>">
					<input type="hidden" name="rp_login" value="<?php echo $rp_login ?>">
				</form>

			<?php break;
			endswitch ?>

		</div>
	</div>

<?php if(!$is_math_panel) : ?>
	<?php get_dict_footer() ?>
<?php else : ?>
	<?php get_math_footer() ?>
<?php endif ?>