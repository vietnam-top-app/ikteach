<?php
/**
* Plugin Name: III Dictionary
* Plugin URI: http://3i.com.vn
* Description: Dictionary plugin.
* Version: 1.0.0
* Author: Nguyen Dang Thang
* Author URI: http://3i.com.vn
* Text Domain: iii-dictionary
*/ 

/* config php.ini
	max_file_uploads = 1000 
	upload_max_filesize = 320M 
	post_max_size = 320M 
	max_execution_time = 300 
	max_input_time = 60 
	max_input_vars = 1000 
	memory_limit = 320M 
*/

// store real GPC data since wordpress automatically add slashes to GPC
$_REAL_GET     = $_GET;
$_REAL_POST    = $_POST;
$_REAL_COOKIE  = $_COOKIE;
$_REAL_REQUEST = $_REQUEST;

// plugin root dir
define('IK_PLUGIN_DIR', dirname(__FILE__));
// student reports dir
define('IK_STUDENT_REPORT_DIR', IK_PLUGIN_DIR . '/reports');

/**
 * Load plugin textdomain
 */
add_action('plugins_loaded', 'ik_load_plugin_textdomain');
function ik_load_plugin_textdomain()
{
	load_plugin_textdomain('iii-dictionary', false, dirname(plugin_basename( __FILE__ ) ) . '/langs');
}

// dictionary tables name
define('ELEARNER_DICT', 'dict_elearner');
define('COLLEGIATE_DICT', 'dict_collegiate');
define('THESAURUS_DICT', 'dict_thesaurus');
define('MEDICAL_DICT', 'dict_medical');
define('INTERMEDIATE_DICT', 'dict_intermediate');
define('ELEMENTARY_DICT', 'dict_elementary');

// subscription config
define('STUDENT_MULTIPLIER', 5);
define('DICTIONARY_MONTHS_MULTIPLIER', 6);

// define homework assignments id constants
define('ASSIGNMENT_SPELLING', 1);
define('ASSIGNMENT_VOCAB_GRAMMAR', 2);
define('ASSIGNMENT_READING', 3);
define('ASSIGNMENT_WRITING', 4);
define('ASSIGNMENT_VOCAB_BUILDER', 5);
define('ASSIGNMENT_REPORT', 6);

// define homework types id constants
define('HOMEWORK_PUBLIC', 1);
define('HOMEWORK_MY_OWN', 2);
define('HOMEWORK_SUBSCRIBED', 3);
define('HOMEWORK_LICENSED', 4);
define('HOMEWORK_CLASS', 5);

// define default flashcard folder id
define('TEACHER_FLASHCARD_FOLDER', 1);
define('SAMPLE_FLASHCARD_FOLDER', 2);

// define subscription type id
define('SUB_TEACHER_TOOL', 1);
define('SUB_DICTIONARY', 2);
define('SUB_SAT_PREPARATION', 3);
define('SUB_POINTS_PURCHASE_MATH', 0);
define('SUB_POINTS_PURCHASE', 4);
define('SUB_SELF_STUDY', 5);
define('SUB_TEACHER_TOOL_MATH', 6);
define('SUB_MATH_SAT_I_PREP', 7);
define('SUB_MATH_SAT_II_PREP', 8);
define('SUB_SELF_STUDY_MATH', 9);
define('SUB_GROUP', 10);

// define payment methods
define('PAYMENT_METHOD_CREDIT_CARD', 1);
define('PAYMENT_METHOD_PAYPAL', 2);
define('PAYMENT_METHOD_POINTS', 3);

// define group type id
define('GROUP_FREE', 1);
define('GROUP_CLASS', 2);
define('GROUP_SUBSCRIBED', 3);

// define class type id
define('CLASS_GRAMMAR', 1);
define('CLASS_WRITING', 2);
define('CLASS_SAT1', 3);
define('CLASS_SAT2', 4);
define('CLASS_SAT3', 5);
define('CLASS_SAT4', 6);
define('CLASS_SAT5', 7);
define('CLASS_OTHERS', 8);
define('CLASS_MATH_SAT1PREP', 9);
define('CLASS_MATH_SAT1A', 10);
define('CLASS_MATH_SAT1B', 11);
define('CLASS_MATH_SAT1C', 12);
define('CLASS_MATH_SAT1D', 13);
define('CLASS_MATH_SAT1E', 14);
define('CLASS_MATH_SAT2PREP', 15);
define('CLASS_MATH_SAT2A', 16);
define('CLASS_MATH_SAT2B', 17);
define('CLASS_MATH_SAT2C', 18);
define('CLASS_MATH_SAT2D', 19);
define('CLASS_MATH_SAT2E', 20);
define('CLASS_KOREAN', 21);
define('CLASS_ENGLISH', 22);
define('CLASS_JAPANESE', 23);
define('CLASS_CHINESE', 24);
define('CLASS_VIETNAMESE', 25);
define('CLASS_MATH_KOREAN', 26);
define('CLASS_MATH_ENGLISH', 27);
define('CLASS_MATH_JAPANESE', 28);
define('CLASS_MATH_CHINESE', 29);
define('CLASS_VIETNAMESE', 30);
define('CLASS_SAT1_SAT1A', 31);

// define point transactions type id
define('POINT_TXN_PURCHASING_WORKSHEET', 1);
define('POINT_TXN_GRADING_WORKSHEET', 2);
define('POINT_TXN_GIFT', 4);

// define teacher request status
define('TEACHER_REQ_PENDING', 1);
define('TEACHER_REQ_PAIDOUT', 2);
define('TEACHER_REQ_CANCELLED', 3);

// define private message status
define('MESSAGE_STATUS_READ', 1);
define('MESSAGE_STATUS_UNREAD', 2);

// define private message moderation status
define('MESSAGE_MOD_STATUS_ACTIVE', 1);
define('MESSAGE_MOD_STATUS_REPLIED', 2);
define('MESSAGE_MOD_STATUS_DONE', 3);

// define Math main level
define('MATH_CALCULS', 16);
define('MATH_GEOMETRY', 17);
define('MATH_ALGEBRA_II', 18);
define('MATH_ALGEBRA_I', 19);
define('MATH_ARITHMETIC', 20);

// define Math assignments id
define('MATH_ASSIGNMENT_SINGLE_DIGIT', 7);
define('MATH_ASSIGNMENT_TWO_DIGIT_MUL', 8);
define('MATH_ASSIGNMENT_SINGLE_DIGIT_DIV', 9);
define('MATH_ASSIGNMENT_TWO_DIGIT_DIV', 10);
define('MATH_ASSIGNMENT_FLASHCARD', 11);
define('MATH_ASSIGNMENT_FRACTION', 12);
define('MATH_ASSIGNMENT_WORD_PROB', 13);
define('MATH_ASSIGNMENT_QUESTION_BOX', 14);
define('MATH_ASSIGNMENT_EQUATION', 15);

// include libs
include_once ABSPATH . '/wp-admin/includes/user.php';
include_once 'library/lib.php';
include_once 'library/init.php';
include_once 'library/single-user-login.php';
include_once 'library/disable-users.php';
include_once 'library/admin.php';
include_once 'library/template.php';
include_once 'library/MWDB.php';
include_once 'library/MWHtml.php';
include_once 'library/subscriptionlib.php';
include_once 'library/teachinglib.php';


// define by dev3i OMG
define('MNS_URL', locale_home_url() . "/?r=manage-subscription");
define('DIC_DWN_URL', home_url() . "/dic-download");
define('CLASS_DIFF', 8);
define('LANGUAGE_CLASS_DIFF', 21);
define('MATH_SAT2_S', 14);
define('MATH_SAT2_E', 20);
define('SUBJECT_MESSAGE_FOR_ALL', 'Support');
define('RECIPIENT_MESSAGE_FOR_ALL', 'ALL');
define('DISPLAY_AT_LOGIN', 2);
define('SYSTEM_MESSAGE', 0);
define('LINK_LANG_ENGLISH','english-link');
define('LINK_LANG_MATH','math-link');
define('NO_DICTIONARY_ID', 9);
define('SAT_GRADE', 15);


//other class 
define('CSS_CHANGE_POS', ' omg_change-pos');
/*
 * this function add custom query var to wp query vars
 */
function add_query_vars_filter( $vars )
{
	$vars[] = 'admin';
	$vars[] = 'r';
	$vars[] = 'mode';
	$vars[] = 'iframe';
	$vars[] = 'page2';

	return $vars;
}
add_filter( 'query_vars', 'add_query_vars_filter' );

// show admin bar for wp admin only
add_action('after_setup_theme', 'remove_admin_bar');
function remove_admin_bar() {
	if (!current_user_can('administrator') && !is_admin()) {
		show_admin_bar(false);
	}
}

/*
 * init user status after user has logged in
 */
function init_user_status($user_login, $user) {
	// update subscription status
	update_user_subscription($user->ID);

	// set SAT progress
	/* $sat_progress = ik_get_sat_progress($user->ID);
	if(empty($sat_progress)) {
		ik_init_sat_progress($user->ID);
	} */
}
add_action('wp_login', 'init_user_status', 10, 2);

/*
 * this function redirect user to new url after logged out
 */
add_action('wp_logout','auto_redirect_after_logout');
function auto_redirect_after_logout() {
	
	// clear user session
	clear_user_sesstion();

	//wp_redirect(locale_home_url() . '/?r=login');

	wp_redirect(locale_home_url());
	?>
	<div class="modal fade modal-login" id="myModal-login" role="dialog">
				<div class="modal-dialog modal-lg modal-login">
				  <div class="modal-content modal-content-login">
				  	<div class="title-div" style="">
				  		<img class="icon-close-classes-created" src="<?php echo get_template_directory_uri(); ?>/library/images/close_blue.png">
					      		<h4 class="modal-title text-uppercase">Login</h4>
				  	</div>
				  	
				    <div class="modal-body-login">
				    	
				    	<div class="">
				    		<div class="">
							<div class="col-md-12">

								<?php switch($action) :
									case 'login' : ?>

									<form action="<?php echo locale_home_url() ?>/?r=login" name="loginform" method="post">
										<div class="row">
											<div class="row md-login-r">
												<div class="col-xs-12 col-sm-6 col-md-6">
													<div class="form-group">
														<label for="username"><?php _e('Username (e-mail address)', 'iii-dictionary') ?></label>
														<input type="text" class="form-control" id="username" name="log" value="">
													</div>     
												</div>
												<div class="col-xs-12 col-sm-6 col-md-6">
													<div class="form-group">
														<label for="password"><?php _e('Password', 'iii-dictionary') ?></label>
														<input type="password" class="form-control" id="password" name="pwd" value="">
													</div>     
												</div>
												<div class="clearfix"></div>
												<div class="col-sm-6 col-md-6">
													<div class="form-group" style="height: 50px; margin-top: -20px;">
														<label>&nbsp;</label>
														<button type="submit" class="btn-dark-blue" name="wp-submit"><!-- <span class="icon-user"> --></span><?php _e('Create a New Class', 'iii-dictionary') ?></button>
													</div>     
												</div>
												<div class="col-sm-6 col-md-6">
													<div class="form-group">
														<label>&nbsp;</label>
														<a href="<?php echo locale_home_url() ?>/?r=signup" class="btn button-grey"><!-- <span class="icon-pencil"> --></span><?php _e('Sign-Up', 'iii-dictionary') ?></a>
													</div>
												</div>
												<!-- <div class="col-sm-5 col-md-4 col-sm-offset-5 col-md-offset-4 text-right">
													<div class="form-group">
														<a href="<?php echo locale_home_url() ?>/?r=login&amp;action=forgotpassword" class="lblForgot"><?php _e('Forgot password?', 'iii-dictionary') ?> &gt;</a>
													</div>
												</div> -->
											</div>
											<div class="clearfix"></div>
											<div style="padding-top: 20px;">
												<div class="footer-modal-login">
													<div class="pull-left" style="margin-right: 15px">
														<img alt="" src="<?php echo get_template_directory_uri() ?>/library/images/desktop-shortcut.png">
													</div>
													<div class="pull-left right-pull-left">
														<p class="instructions-text"><?php _e('Get to the site faster! Download desktop startup icon. ', 'iii-dictionary') ?></p>
														<span class="downloads-block"><!-- <span class="icon-download"> --></span><!--  <?php _e('DOWNLOAD:', 'iii-dictionary') ?> -->
															<span class="downloads-links"><a href="<?php echo $link_url['mac']; ?>"><?php _e('MAC', 'iii-dictionary') ?></a> / <a href="<?php echo $link_url['win']; ?>"><?php _e('WINDOWS', 'iii-dictionary') ?></a></span>
														</span>
														<p class="instructions-text instr-text"><?php _e('( For mobile, visit Iklearn.com)') ?></p>
													</div>
												</div>
												<div class="">
													<div style="padding-top: 10px;">
														<div class="form-group forgot-password-form">
															<a class="forgot-password-a" data-toggle="modal" data-target="#site-messages-modal-forgot-password" class="lblForgot"><img src="<?php echo get_template_directory_uri() ?>/library/images/Forgot-icon.png"><?php _e('Forgot password?', 'iii-dictionary') ?></a>
														</div>
													</div>
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
											<div class="col-xs-12 col-sm-6 col-md-6">
												<div class="form-group">
													<label for="user_login" ><?php _e('Username or E-mail', 'iii-dictionary') ?></label>
													<input type="text" name="user_login" id="user_login" class="form-control" value="">
												</div>
											</div>
										</div>
										<div class="row">
											<div class="col-xs-12 col-sm-6 col-md-6">
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
				    	</div>
						
				    </div>
				    <!-- <div class="modal-footer">
				      <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				    </div> -->
				  </div>
				</div>
			</div>
	<script>(function($){ $(function(){ $("#myModal-login").modal("show"); }); })(jQuery);</script>
	<?php
	exit();
}

// modify log out url
add_filter( 'logout_url', 'my_logout_page', 10, 2 );
function my_logout_page( $logout_url, $redirect ) {
	$logout_url = str_replace(site_url(), home_url(), $logout_url);

    return $logout_url;
}

// prevents polylang plugin's home redirection if user access admin panel
add_filter('pll_redirect_home', 'no_home_redirection');
function no_home_redirection($redirect) {
	if(strpos(home_url(), 'admin') !== false) {
		return false;
	}

	if(is_math_panel()) {
		return site_math_url() . '/';
	}

	return $redirect;
}

/////////// add audio button to tiny mce //////////////////////

/*
 * register audio button
 */
function ik_register_audio_button($buttons) {
	array_push($buttons, 'ik_audio_button');

	return $buttons;
}
add_filter('mce_buttons_2', 'ik_register_audio_button');

/*
 * register audio button js file
 */
function ik_add_tinymce_plugin($plugin_array) {
    $plugin_array['ik_audio_button'] = plugins_url('js/audio-button.js', __FILE__);

    return $plugin_array;
}
add_filter('mce_external_plugins', 'ik_add_tinymce_plugin');

