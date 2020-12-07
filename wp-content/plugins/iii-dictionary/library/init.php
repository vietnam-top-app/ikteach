<?php
/*
 * Adding custom roles
 */
/*
add_role('mw_super_admin', 'IK Super Admin', array(
    'read' => true,
    'edit_posts' => false,
    'delete_posts' => false,
));

add_role('mw_admin', 'IK Admin', array(
    'read' => true,
    'edit_posts' => false,
    'delete_posts' => false,
));

add_role('mw_registered_teacher', 'IK Registered Teacher', array(
    'read' => true,
    'edit_posts' => false,
    'delete_posts' => false,
));

add_role('mw_qualified_teacher', 'IK Qualified Teacher', array(
    'read' => true,
    'edit_posts' => false,
    'delete_posts' => false,
));


add_role('mw_student', 'IK Student', array(
    'read' => true,
    'edit_posts' => false,
    'delete_posts' => false,
)); 

add_role('mw_registered_math_teacher', 'IK Registered Math Teacher', array(
    'read' => true,
    'edit_posts' => false,
    'delete_posts' => false,
));


add_role('mw_qualified_math_teacher', 'IK Qualified Math Teacher', array(
    'read' => true,
    'edit_posts' => false,
    'delete_posts' => false,
));
*/

function get_subdomain()
{
	//	Get the URL that was entered
	$URL = $_SERVER['HTTP_HOST'];
	//	Extract the subdomain component
	$fulldomain = explode('.',$URL);
	$subdomain = $fulldomain[1];
	$subdomain1 = $fulldomain[0];
	if(in_array($subdomain, array('admin', 'math')) === true) {
		return $subdomain;
	}
	if(in_array($subdomain1, array('admin', 'math')) === true) {
		return $subdomain1;
	}else{
		$URL = $_SERVER['REQUEST_URI'];
		$segment = explode('/',$URL);
		if($segment[1] == ''){
			$segment[1] = 'en';
			header('Location: '.site_url().'/en');
			exit;
		}elseif($fulldomain[0] == 'www'){
			header('Location: '.site_url().'/en');
			exit;
		}
	}

	return '';
}

if(get_subdomain() == 'admin') {
	$is_admin = true;
}
else {
	$is_admin = false;
}


/*
 * init dictionary
 */
add_action('init', 'dictionary_init', 1);
// add_action('wp_logout', 'dictionary_EndSession');
// add_action('wp_login', 'dictionary_EndSession');

function dictionary_init() {
    if(!session_id()) {
		session_name('IKLEARN');
		ini_set( 'session.cookie_domain', str_replace('http://', '',site_url()) );
        session_start();
    }

	init_subscription_status();

	// init counting variable for dictionary popup
	if(!isset($_SESSION['remind_count'])) {
		// dictionary type slug
		$d = array('elearner', 'collegiate', 'medical', 'intermediate', 'elementary');
		foreach($d as $i) {
			$_SESSION['remind_count'][$i] = 0;
		}
	}
}

/* function dictionary_EndSession() {
    //session_destroy();
} */

/*
 * Menu Page
 */
// add_action( 'admin_menu', 'register_dictionary_menu_page' );

function register_dictionary_menu_page() {

	add_menu_page( 'MW Dictionary', 'MW Dictionary', 'manage_options', 'iii-dictionary/admin/admin.php', '', plugins_url( 'myplugin/images/icon.png' ), 6 );
	

}

/* add_action('admin_menu', 'register_dictionary_submenu_page');

function register_dictionary_submenu_page() {
	add_submenu_page( 'iii-dictionary/admin/admin.php', 'Editor', 'MW Dictionary Editor', 'manage_options', 'dictionary-editor', 'my_custom_submenu_page_callback' );
}

function my_custom_submenu_page_callback() {
?>
	<div class="wrap">
		<h2>Your Plugin Page Title</h2>
		<form method="post" action="options.php"> 
			
		<?php submit_button(); ?>
		</form>
	</div>
<?php
} */
add_action('wp_footer','check_notice_user');
function check_notice_user() {
	MWHtml::notice_sub_mes();
}