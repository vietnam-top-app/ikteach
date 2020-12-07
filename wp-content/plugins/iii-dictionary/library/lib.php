<?php
/*
 * General functions
 */

 /*
 * this function get current route
 */
function get_route()
{
	$r = get_query_var('r', 0 );
	if($r == 0) {
		if( $_SERVER['REQUEST_URI'] == '/dic-download') {
			$r = 'dictionary/collegiate';
		}
	}
	$r = strtolower($r);
	//$r = str_replace('-', '_', $r);
	$r = explode('/', $r);
	
	return $r;
}

// get short language code
function get_short_lang_code()
{
	$locale_code = explode('_', get_locale());

	return $locale_code[0];
}

/*
 * clear user session
 */
function clear_user_sesstion()
{
	// re init user subscription
	ik_init_user_subscription();

	// set referer to home page
	$_SESSION['mw_referer'] = locale_home_url();

	// clear the system message queue
	ik_clear_message_queue();

	// clear dictionary search history
	clear_search_history();

	// empty shopping cart
	// empty_cart();

	// clear pages filter values
	clear_page_filter_session();
}

/*
 * home url with language code
 */
function locale_home_url($path = '')
{
	$path = ltrim($path, '/');
	$locale_parts = explode('_', get_locale());
	$locale_code = empty($path) ? $locale_parts[0] : $locale_parts[0] . '/';
	return home_url($locale_code . $path);
}

function site_home_url()
{
	$subdomain = get_subdomain();
	$subdomain = empty($subdomain) ? '' : $subdomain . '.';

	$url = str_replace($subdomain, '', locale_home_url());
	return $url;
}

function site_math_url()
{
	$url = site_url();

	if(strpos($url, '//www.') !== false) {
		$url = str_replace('//www.', '//www.math.', $url);
	}
	else {
		$url = str_replace('//', '//math.', $url);
	}

	return $url . '/' . get_short_lang_code();
}

function site_admin_url()
{
	$url = site_url();

	if(strpos($url, '//www.') !== false) {
		$url = str_replace('//www.', '//www.admin.', $url);
	}
	else {
		$url = str_replace('//', '//admin.', $url);
	}

	return $url;
}

function home_url_ssl()
{
	$return_url =  is_math_panel() ? site_math_url() : site_home_url(); 
	//return locale_home_url();
	//return str_replace('http://', 'https://', $return_url);
	
}

/*
 * get cloud server url of info tabs
 *
 * @param string $file_name		image file name
 *
 * @return string
 */
function get_info_tab_cloud_url($file_name)
{
	return 'http://mwd.s3.amazonaws.com/infortabwide/' . get_short_lang_code() . '/' . $file_name;
}
/*
 * get cloud server url of info tabs - for math
 *
 * @param string $file_name		image file name
 *
 * @return string
 */
 function get_info_tab_cloud_url_math($file_name)
{
	return 'http://mwd.s3.amazonaws.com/infortabmath/' . get_short_lang_code() . '/' . $file_name;
}
 
/*
 * strip all slashes added by wordpress
 */
function ik_stripslashes_deep($data)
{
	return array_map('stripslashes_deep', $data);
}

function get_dictionary_name($dictionary)
{
	$dictionary_name = array(
		'elearner' => 'English Learner\'s Dictionary',
		'collegiate' => 'Collegiate Dictionary &amp; Thesaurus',
		'medical' => 'Medical Desk Dictionary',
		'intermediate' => 'Intermediate Dictionary',
		'elementary' => 'Elementary Dictionary'
	);

	return $dictionary_name[$dictionary];
}

/*
 * check if is home 
 * return boolean
 */
function is_home_page() {
	//check exist r
	$route = get_route();
	
	$flag = ($route[0] === '0') ? true : false;
	
	if($flag && is_admin_panel()) {
		$flag = false;
	}
	return $flag;
}


/*
 * check if user is in admin panel
 *
 * @return boolean
 */
function is_admin_panel()
{
	return get_subdomain() == 'admin';
}

/*
 * check if user is in math site
 *
 * @return boolean
 */
function is_math_panel()
{
	return get_subdomain() == 'math';
}

// check if current user's role is Wordpress admin
function is_wp_admin()
{
	$user = wp_get_current_user();

	if ( isset( $user->roles ) && is_array( $user->roles ) ) {
		//check role
		if ( in_array( 'administrator', $user->roles ) ) {
			return true;
		} else {
			return false;
		}
	} else {
		return false;
	}
}

// check if current user's role is IK Super Admin
function is_mw_super_admin($user_id = 0)
{
	$user = $user_id ? get_user_by('id', $user_id) : wp_get_current_user();

	if ( isset( $user->roles ) && is_array( $user->roles ) ) {
		//check role
		if ( in_array( 'mw_super_admin', $user->roles ) ) {
			return true;
		} else {
			return false;
		}
	} else {
		return false;
	}
}

// check if current user's role is IK Admin
function is_mw_admin($user_id = 0)
{
	$user = $user_id ? get_user_by('id', $user_id) : wp_get_current_user();

	if ( isset( $user->roles ) && is_array( $user->roles ) ) {
		//check role
		if ( in_array( 'mw_admin', $user->roles ) ) {
			return true;
		} else {
			return false;
		}
	} else {
		return false;
	}
}

// check if current user's role is Register Teacher
function is_mw_registered_teacher($id = null, $type = 0)
{
	if(is_null($id)) {
		$user = wp_get_current_user();
	}
	else {
		$user =  get_user_by( 'id', $id );
	}

	if ( isset( $user->roles ) && is_array( $user->roles ) ) {
		//check role
		switch($type) {
			case 1  : 
				return in_array( 'mw_registered_math_teacher', $user->roles ) ?  true :  false;
			default : 
				return in_array( 'mw_registered_teacher', 		$user->roles ) ?  true :  false;
		}
	} else {
		return false;
	}
}


// check if current user's role is Qualified Teacher
function is_mw_qualified_teacher($id = null, $type = 0)
{
	if(is_null($id)) {
		$user = wp_get_current_user();
	}
	else {
		$user =  get_user_by( 'id', $id );
	}

	if ( isset( $user->roles ) && is_array( $user->roles ) ) {
		//check role
		switch($type) {
			case 1 	:
				return in_array( 'mw_qualified_math_teacher', 	$user->roles ) ?  true :  false;
			default : 
				return in_array( 'mw_qualified_teacher', 		$user->roles ) ?  true :  false;
		}
	} else {
		return false;
	}
}

// check if current user's role is Student (Default Role)
function is_mw_student($id = null)
{
	if(is_null($id)) {
		$user = wp_get_current_user();
	}
	else {
		$user =  get_user_by( 'id', $id );
	}

	if ( isset( $user->roles ) && is_array( $user->roles ) ) {
		//check role
		if ( in_array( 'mw_student', $user->roles ) ) {
			return true;
		} else {
			return false;
		}
	} else {
		return false;
	}
}

/*
 * get user type
 *
 * Possible value:
 * 	+ Student	: default type
 * 	+ Sub		: user has subscribed something
 * 	+ Teacher	: user has subscribed Teacher's Tool
 * 	+ T-Reg		: user has registered as Teacher
 * 	+ T-Qual	: user has registered as Teacher and qualified to teach class
 */
function ik_get_user_type($user_id)
{
	global $wpdb;
	$sub_type = '';
	$teacher_type = 'Student';
	$user =  get_user_by( 'id', $user_id );

	// check teacher tool subscription
	$subscribed_check = $wpdb->get_col(
		'SELECT COUNT(*) FROM ' . $wpdb->prefix . 'dict_user_subscription WHERE activated_by = ' . $user_id . ' AND typeid = ' . SUB_TEACHER_TOOL
	);

	if(!empty($subscribed_check[0])) {
		$sub_type = 'Sub';
	}

	// check teacher role
	if(isset($user->roles) && is_array($user->roles)) {
		//check role
		if(in_array('mw_qualified_teacher', $user->roles)) {
			$teacher_type = 'T-Qual';
		}else if(in_array('mw_registered_teacher', $user->roles)) {
			$teacher_type = 'T-Reg';
		}else if(in_array('mw_registered_math_teacher', $user->roles)) {
			$teacher_type = 'T-M-Reg';
		}else if(in_array('mw_qualified_math_teacher', $user->roles)) {
			$teacher_type = 'T-M-Qual';
		}else {
			$created_group = $wpdb->get_col(
				'SELECT COUNT(*) FROM ' . $wpdb->prefix . 'dict_groups WHERE created_by = ' . $user_id
			);

			if(!empty($created_group[0])) {
				$teacher_type = 'Teacher';
			}
		}
	}

	return empty($sub_type) ? $teacher_type : $teacher_type . '-' . $sub_type;
}

function ik_check_user_student($user_id){
	global $wpdb;
	$sub_type = '';
	$teacher_type = 'Student';
	$user =  get_user_by( 'id', $user_id );

	// check teacher role
	if(isset($user->roles) && is_array($user->roles)) {
		//check role
		if(in_array('mw_qualified_teacher', $user->roles)) {
			$teacher_type = 'T-Qual';
		}else if(in_array('mw_registered_teacher', $user->roles)) {
			$teacher_type = 'T-Reg';
		}else if(in_array('mw_registered_math_teacher', $user->roles)) {
			$teacher_type = 'T-M-Reg';
		}else if(in_array('mw_qualified_math_teacher', $user->roles)) {
			$teacher_type = 'T-M-Qual';
		}else {
			$created_group = $wpdb->get_col(
				'SELECT COUNT(*) FROM ' . $wpdb->prefix . 'dict_groups WHERE created_by = ' . $user_id
			);

			if(!empty($created_group[0])) {
				$teacher_type = 'Teacher';
			}
		}
	}
	if($teacher_type == 'Student')
		return true;
	else
		return false;
}

/*
 * Return list of user id with mw super admin role
 */
function get_mw_super_admins_id()
{
	global $wp_roles, $wpdb;
	$ids = array();

	$query = "SELECT ID FROM $wpdb->users WHERE ID = ANY (SELECT user_id FROM $wpdb->usermeta WHERE meta_key = 'wp_capabilities' AND meta_value RLIKE 'mw_super_admin')";
	
	foreach($wpdb->get_results($query) as $row)
	{
		$ids[] = $row->ID;
	}
	
	return $ids;
}

/*
 * Return list of user id with mw admin role
 */
function get_mw_admins_id()
{
	global $wp_roles, $wpdb;
	$ids = array();

	$query = "SELECT ID FROM $wpdb->users WHERE ID = ANY (SELECT user_id FROM $wpdb->usermeta WHERE meta_key = 'wp_capabilities' AND meta_value RLIKE 'mw_admin')";
	
	foreach($wpdb->get_results($query) as $row)
	{
		$ids[] = $row->ID;
	}
	
	return $ids;
}

/*
 * return list of admin pages
 */
function ik_get_admin_pages()
{
	$pages = array(
		'admin-homework-creator' => 'Admin Homework Creator',
		'admin-manager' => 'Admin Manager',
		'check-groups' => 'Check Groups',
		'check-sales' => 'Check Sales',
		'create-group' => 'Create Groups',
		'check-users' => 'Check Users',
		'view-user' => 'View User Detail',
		'create-credit-codes' => 'Create Credit Code',
		'verify-credit-codes' => 'Verify Credit Status',
		'feedback-from-users' => 'Feedback From Users',
		'payment-requests' => 'Payment Requests',
		'price-manager' => 'Price Manager',
		'create-math-level' => 'Create Math Level',
        'math-create-credit-codes' => 'Math Credit Code',
		'media-manager' => 'Media Manager'
	);

	return $pages;
}

/*
 * check if user has joined a specific group or any group
 *
 * @param int $student_id
 * @param int $group_id
 *
 * @return boolean
 */
function is_student_in_group($student_id = 0, $group_id = 0)
{
	global $wpdb;
	
	$student_id = $student_id ? $student_id : get_current_user_id();

	$query = 'SELECT gs.group_id 
			  FROM ' . $wpdb->prefix . 'dict_group_students AS gs 
			  JOIN ' . $wpdb->prefix . 'dict_groups AS g ON g.id = gs.group_id
			  WHERE g.active = 1 AND gs.absented = 0 AND student_id = ' . $student_id;

	if($group_id) {
		$query .= ' AND group_id = ' . $group_id;
	}

	$result = $wpdb->get_results($query);

	return !empty($result);
}

/*
 * get sheet name from a group and current user's score
 *
 * @param int $group_id
 *
 * @return array
 */
function get_sat_class_score($group_id)
{
	global $wpdb;

	$query = 'SELECT sheet_name , score, submitted_on, for_practice
			FROM ' . $wpdb->prefix . 'dict_homeworks AS h
			LEFT JOIN ' . $wpdb->prefix . 'dict_homework_results AS hr ON hr.homework_id = h.id
			JOIN ' . $wpdb->prefix . 'dict_sheets AS s ON s.id = h.sheet_id
			WHERE h.group_id = ' . $group_id . ' AND (hr.userid IS NULL OR hr.userid = ' . get_current_user_id() . ')';
	$results = $wpdb->get_results($query);

	return $results;
}
 
/*
 * check to see if user has completed a SAT class
 *
 * @param array $results 		SAT results, return by get_sat_class_score() function
 *
 * @return boolean
 */
function is_sat_class_completed($results)
{
	$is = array(
	'is_practice' 	=> 0,
	'is_test' 		=> 0
	);
	
	foreach($results as $result)
	{
		($result->for_practice == 0) ? $is['is_test']++ : $is['is_practice']++;
		if(is_null($result->score) && $result->for_practice == 0) {
			return false;
		}
	}
	if($is['is_practice']++ == count($results)) { 
			return false;
	}
			return true;
}
/* calculate the average score
 * @param all assigment in group $homeworks->items
 * return average
 */
 function average_test_homework($worksheets) {
	$var = array(
	'sum_test' 	=> 0,
	'sum_score' => 0
	);
	foreach($worksheets as $hw) {
		if(!is_null($hw->score) && $hw->for_practice != 1) {
			$var['sum_score'] += $hw->score;
			$var['sum_test']++;
 		}
	}
	return $var['sum_score'] == 0 ? 0 : $var['sum_score'] / $var['sum_test'];
 }
// this function check if student has unfinished homework. 
// return false if there's more than $n homework unfinished. Default to 2
function is_homework_unfinished($n = 2)
{
	global $wpdb;
	
	$result = $wpdb->get_row('SELECT COUNT(*) AS count FROM ' . $wpdb->prefix . 'dict_homework_results WHERE userid = ' . get_current_user_id() . ' AND finished = 0');
	
	return $result->count >= $n ? true : false;
}

// this function check if user have specific role.
function ik_check_user_role( $role, $user_id = null ) {
 
    if ( is_numeric( $user_id ) )
	$user = get_userdata( $user_id );
    else
        $user = wp_get_current_user();
 
    if ( empty( $user ) )
	return false;
 
    return in_array( $role, (array) $user->roles );
}

/*
 * check user permissions
 * this function only check on user site
 *
 * @param string $page		page slug
 *
 * @return boolean
 */
function ik_user_can_view($page)
{
	// array of pages that's not required login
	$ignored = array(
		'home', 'login', 'signup', 'ajax', 'api', // english site
		'dictionary', 'dictionary/elearner', 'dictionary/collegiate', 'dictionary/medical', 'dictionary/intermediate', 'dictionary/elementary', // english site
		'spelling_practice', 'vocabulary_practice', 'reading_comprehension', 'writing_practice', 'sat_preparation', // english site
		'teaching', 'teaching/register', 'about_us', // english site

		'calculus', 'geometry', 'algebra_ii', 'algebra_i', 'arithmetics', 'math_homework', 'sat_preparation/sat1prep', 'sat_preparation/sat2prep' // math site
	);

	if(in_array($page, $ignored) === true) {
		return true;
	}

	if(!is_user_logged_in()) {
		ik_enqueue_messages(__('Please log in and get more function and content.', 'iii-dictionary'), 'checklogin');
		return false;
	}

	return true;
}

/*
 * set user's avatar
 *
 * @param int $user_id
 * @param array $file		A file from the global $_FILES variable
 */
function ik_set_user_avatar($user_id, $file)
{
	$user = get_user_by('id', $user_id);
	$user_dir = $user->user_login;

	if($file['error'] == UPLOAD_ERR_OK) {
		$allowedTypes = array('image/png', 'image/jpeg', 'image/gif');
		$error = !in_array($file['type'], $allowedTypes);
		if(!$error) {
			$wp_upload_dir = wp_upload_dir();
			$avatar_file_name = str_replace(' ', '_', $file['name']);
			$upload_dir = $wp_upload_dir['basedir'] . '/' . $user_dir . '/';

			if(!is_dir($upload_dir)) {
				mkdir($upload_dir);
			}

			if(move_uploaded_file($file['tmp_name'], $upload_dir . $avatar_file_name)) {
				$avatar_url = $wp_upload_dir['baseurl'] . '/' . $user_dir . '/' . $avatar_file_name;
				update_user_meta($user_id, 'ik_user_avatar', $avatar_url);

				return true;
			}
		}
	}

	return false;
}

/*
 * return user's avatar url
 *
 * @param int $user_id
 */
function ik_get_user_avatar($user_id)
{
	return get_user_meta($user_id, 'ik_user_avatar', true);
}

/*
 * Generate User's Self study group name.
 * This function will take care of name duplication too.
 */
function generate_self_study_group_name()
{
	global $wpdb;

	$user_info = get_userdata(get_current_user_id());

	$group_name = 'Self-study_' . $user_info->user_email;

	$exist_name = $wpdb->get_results(
		'SELECT name FROM ' . $wpdb->prefix . 'dict_groups WHERE name LIKE \'' . $group_name . '%\' ORDER BY id'
	);

	if(!empty($exist_name)) {
		$newest_name = end($exist_name);
		$parts = explode('_', $newest_name->name);
		$serial = end($parts);
		$serial = is_numeric($serial) ? $serial + 1 : 1;
		$group_name .= '_' . $serial;
	}

	return $group_name;
}

/*
 * return formated date string
 *
 * @param string $date
 * @param string $format
 *
 * @return string
 */
function ik_date_format($date, $format = 'm/d/Y')
{
	
	if(!$timestamp = strtotime($date)) {
		return 'N/A';
	}

	return date($format, $timestamp);
}

/*
 * this function return file size of remote file
 * return -1 on not found
 */
function curl_get_file_size($url)
{
	// Assume failure.
	$result = -1;

	$curl = curl_init( $url );

	// Issue a HEAD request and follow any redirects.
	curl_setopt( $curl, CURLOPT_NOBODY, true );
	curl_setopt( $curl, CURLOPT_HEADER, true );
	curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );

	$data = curl_exec( $curl );
	curl_close( $curl );

	if( $data ) {
		$content_length = "unknown";
		$status = "unknown";

		if( preg_match( "/^HTTP\/1\.[01] (\d\d\d)/", $data, $matches ) ) {
			$status = (int)$matches[1];
		}

		if( preg_match( "/Content-Length: (\d+)/", $data, $matches ) ) {
			$content_length = (int)$matches[1];
		}

		if( $status == 200 || ($status > 300 && $status <= 308) ) {
			$result = $content_length;
		}
	}

	return $result;
}

/*
 * push an item into searched words history queue
 */
function save_search_history($entry, $dictionary)
{       
	if(empty($_SESSION['search_history'][$dictionary])) {
		$_SESSION['search_history'][$dictionary][$entry] = $entry;
	}
	else {
		unset($_SESSION['search_history'][$dictionary][$entry]);
		$_SESSION['search_history'][$dictionary] = array($entry => $entry) + $_SESSION['search_history'][$dictionary];
		if(count($_SESSION['search_history'][$dictionary]) == 50) {
			array_pop($_SESSION['search_history'][$dictionary]);
		}
	}
       
	// store the history in user meta if the use has subscribed
	if(is_dictionary_subscribed($dictionary)) {
		update_user_meta(get_current_user_id(), 'search-words-history', json_encode($_SESSION['search_history']));
	}
}

/*
 * return searched words history
 */
function get_search_history($dictionary)
{
	if(is_dictionary_subscribed($dictionary) && empty($_SESSION['search_history'][$dictionary])) {
		$cached = json_decode(get_user_meta(get_current_user_id(), 'search-words-history', true));
		$_SESSION['search_history'][$dictionary] = (array) $cached->$dictionary;
	}

	$obj = new stdClass;
	$obj->items = (array) $_SESSION['search_history'][$dictionary];        
	$obj->count = count($_SESSION['search_history'][$dictionary]);

	return $obj;
}

/*
 * remove an item from searched words history
 */
function remove_search_history_item($entry, $dictionary)
{
	unset($_SESSION['search_history'][$dictionary][$entry]);
}

/*
 * clear search history
 */
function clear_search_history()
{
	$_SESSION['search_history'] = null;
}

/*
 * this function return dictionary table name
 */
function get_dictionary_table($d)
{
	switch($d) {
		case 1 :
		case 'elearner' : return 'dict_elearner';
			break;
		case 2 :
		case 'collegiate' : return 'dict_collegiate';
			break;
		case 3 :
		case 'medical' : return 'dict_medical';
			break;
		case 4 :
		case 'intermediate' : return 'dict_intermediate';
			break;
		case 5 :
		case 'elementary' : return 'dict_elementary';
			break;
	}
}

// return dictionary id by it's slug
function get_dictionary_id_by_slug($slug)
{
	$ids['elearner'] = 1;
	$ids['collegiate'] = 2;
	$ids['medical'] = 3;
	$ids['intermediate'] = 4;
	$ids['elementary'] = 5;

	return $ids[$slug];
}

/*
 * setting option
 */
function mw_set_option($name, $value)
{
	global $wpdb;

	if($name == '') {
		return false;
	}

	$result = $wpdb->update(
					$wpdb->prefix . 'dict_options',
					array('option_value' => $value),
					array('option_name' => $name)
			);

	// if update return false or 0, we check if option_name exist
	if(!$result)
	{
		$result = $wpdb->get_row('SELECT id FROM ' . $wpdb->prefix . 'dict_options WHERE option_name = \'' . $name . '\'');
		
		if(is_null($result))
		{
			// option name not existed. We create it
			$result = $wpdb->insert(
						$wpdb->prefix . 'dict_options',
						array(
							'option_name' => $name,
							'option_value' => $value
						)
					);
		}
		else
		{
			// option name existed but we cannot update it
			return false;
		}
	}

	return $result ? true : false;
}

/*
 * getting option
 */
function mw_get_option($name)
{
	global $wpdb;

	$row = $wpdb->get_row('SELECT option_value FROM ' . $wpdb->prefix . 'dict_options WHERE option_name = \'' . $name . '\'');

	return !is_null($row) ? $row->option_value : '';
}

/*
 * Add message to the message queue
 *
 * @param $msg
 * @param $type
 * @param $title
 */
function ik_enqueue_messages($msg, $type = 'message', $title = 'Messages', $other = array() )
{
	$message = new stdClass;
	$message->type = $type;
	$message->title = $title;
	$message->msg = $msg;
	$message->other = $other;

	switch($type)
	{
		case 'error': $message->label = __('Error', 'iii-dictionary');
			break;
		case 'success': $message->label = __('Success', 'iii-dictionary');
			break;
		case 'failed': $message->label = __('Failed', 'iii-dictionary');
			break;
		case 'message': $message->label = __('Message', 'iii-dictionary');
			break;
		case 'notice': $message->label = __('Notice', 'iii-dictionary');
			break;
		case 'note' : $message->label = __('Note', 'iii-dictionary');
			break;
		case 'checklogin' : $message->label = __('checklogin', 'iii-dictionary');
			break;
		case 'other' : $message->label = '';
			break;
	}

	$_SESSION['message_queue'][] = $message;
}

/* get other in message
 * @result $message->other
 * @pram $message
 */
function ik_get_other_queue($messages) {
	foreach($messages as $msg) {
		if(!empty($msg->other)) {
			return $msg->other;
		}
	}
	return false;
}

/*
 * Return current message queue, empty the queue after that.
 * Return false if the queue is empty
 *
 * @return mixed
 */
function ik_get_message_queue()
{
	$current_user_id = get_current_user_id();

	$messages = get_user_meta($current_user_id, 'ik-messages', true);

	if(empty($messages)) {
		$messages = empty($_SESSION['message_queue']) ? false : $_SESSION['message_queue'];
	}
	else {
		update_user_meta($current_user_id, 'ik-messages', array());
	}

	$_SESSION['message_queue'] = array();

	return $messages;
}

/*
 * Clear message queue
 */
function ik_clear_message_queue()
{
	$_SESSION['message_queue'] = array();
}

/*
 * enqueue js message.
 * those messages will be print at bottom of the page. And can be accessed vie JS_MESSAGES js global object
 *
 * @param string $name		access key
 * @param string $text 		message
 */
function ik_enqueue_js_messages($name, $text)
{
	$_SESSION['js_message_queue'][$name] = $text;
}

/*
 * return js message queue
 */
function ik_get_js_message_queue()
{
	return (array) $_SESSION['js_message_queue'];
}

/*
 * clear js message queue
 */
function ik_clear_js_message_queue()
{
	$_SESSION['js_message_queue'] = array();
}

/*
 * print js messages
 */
function print_js_messages()
{
	$js_messages = ik_get_js_message_queue();
	foreach($js_messages as $name => $msg) {
		$items[] = $name . ': "' . $msg . '"';
	}
	ik_clear_js_message_queue();

	if(!empty($items)) : ?>
<script>var JS_MESSAGES = {<?php echo implode(',', $items) ?>};</script>
<?php endif;
}

/*
 *	set lock page
 */
function set_lockpage_dialog($title, $body, $return_url)
{
	$_SESSION['lock-page'] = array(
		'title' => $title,
		'body' => $body,
		'return_url' => $return_url
	);
}

/*
 * check if date is valid
 */
function ik_validate_date($date)
{
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') == $date;
}

/*
 * send private message
 *
 * @param mixed $recipient			Recipient user name or user id. 'Support' mean this is a feedback to site support
 * @param string $subject
 * @param string $message
 *
 * @return boolean
 */
function ik_send_private_message($recipient, $subject, $message, $system_message = false, $display_at_login = 0)
{
	global $wpdb;

	// get recipient user id
	if(is_numeric($recipient)) {
		$recipient_id = $recipient;
	}
	else {
		// get recipient id from db
		$users = get_users(array('fields' => array( 'ID')));
		foreach($users as $data) {
			if($data->ID != 1) {
				$recipient_id[] = $data->ID;
			}
		}
	}

	
	$created_on = date('Y-m-d H:i:s', time());

	if($system_message) {
		$user_id = 0;
		$system_message = 1;
	}
	else {
		$system_message = 0;
		$user_id = get_current_user_id();
	}
	
	$message_id = MWDB::insert_private_message(array(
		'subject' => $subject,
		'message' => $message,
		'created_on' => $created_on
	));

	if($message_id) {
		$data = array(
			'user_id' => $recipient_id,
			'message_id' => $message_id,
			'sender_id' => $user_id,
			'received_on' => $created_on,
			'status' => MESSAGE_STATUS_UNREAD,
			'moderation_status' => MESSAGE_MOD_STATUS_ACTIVE,
			'system_message' => $system_message,
			'display_at_login' => $display_at_login
		);

		MWDB::insert_private_message_inbox($data);

		$data_outbox = array(
			'user_id' => $user_id,
			'message_id' => $message_id,
			'recipient_id' => $recipient_id,
			'sent_on' => $created_on,
			'system_message' => $system_message,
			'display_at_login' => $display_at_login
		);

		MWDB::insert_private_message_outbox($data_outbox);

		ik_enqueue_messages(__('Your message has been sent.', 'iii-dictionary'), 'success');
		return true;
	}
	else {
		ik_enqueue_messages(__('An error occured, cannot send your message.', 'iii-dictionary'), 'error');
		return false;
	}
}

/*
 * update moderation status of a message
 *
 * @param mixed $message_ids
 * @param int $status
 *
 * @return mixed
 */
function ik_update_message_mod_status($message_ids, $status)
{
	global $wpdb;

	if(!is_array($message_ids)) {
		$message_ids = array($message_ids);
	}

	$result = $wpdb->query(
		'UPDATE ' . $wpdb->prefix . 'dict_private_message_inbox
		SET moderation_status = ' . $status . ' WHERE id IN (' . implode(',', $message_ids) . ')'
	);

	return $result;
}

/*
 * update status of a message
 *
 * @param mixed $message_ids
 * @param int $status
 *
 * @return mixed
 */
function ik_update_message_status($message_ids, $status)
{
	global $wpdb;

	if(!is_array($message_ids)) {
		$message_ids = array($message_ids);
	}

	$result = $wpdb->query(
		'UPDATE ' . $wpdb->prefix . 'dict_private_message_inbox SET status = ' . $status . ' WHERE id IN (' . implode(',', $message_ids) . ')'
	);

	return $result;
}

/*
 * return current page filter values
 */
function get_page_filter_session()
{
	$route = get_route();

	if($route[0] != 'ajax') {
		$current_page_slug = $route[0];
		if(!empty($route[1])) {
			$current_page_slug .= '/' . $route[1];
		}
		if(is_admin_panel()) {
			$current_page_slug = 'admin/' . $current_page_slug;
		}
		
		return $_SESSION['filter'][$current_page_slug];
	}

	return false;
}

/*
 * set current page filter values
 */
function set_page_filter_session($filter)
{
	$route = get_route();

	if($route[0] != 'ajax') {
		$current_page_slug = $route[0];
		if(!empty($route[1])) {
			$current_page_slug .= '/' . $route[1];
		}
		if(is_admin_panel()) {
			$current_page_slug = 'admin/' . $current_page_slug;
		}

		$_SESSION['filter'][$current_page_slug] = $filter;
	}
}

/*
 * clear pages filter values
 */
function clear_page_filter_session()
{
	$_SESSION['filter'] = array();
}

/*
 * cut string without break word
 */
function ik_cut_str($str, $length)
{
	$line = $str;
	if (preg_match('/^.{1,' . $length . '}\b/s', $str, $match))
	{
		$line=$match[0];
	}

	return $line;
}
/* check expire of subscription
 * if expire < 10 days return true
 * @param : (string) => 'distance between current date and expire of subscription'
 * @return boolean
 */
 function check_exp_subscription($date) {
	$params = explode(' ', $date);
	
	if($params['0'] == '-') {
		return false;
	}else {
		if($params['1'] > 10) {
			return false;
		}
	}	
	if(!isset($_SESSION['exp_sb'])) { $_SESSION['exp_sb'] = true; }
	return true;
 }
 
 /* check user have message from admin
  * @pram : (int)  => user_id
  * return  boolean
  */
 function check_message_user() {
	$filter['type'] = 'received';
	$filter['user_id'] = get_current_user_id();
	$filter['status'] 	= MESSAGE_STATUS_UNREAD;
	$prvm = MWDB::get_private_messages($filter['type'], $filter);
	foreach($prvm->items as $data) {
		if($data->system_message == 1 && $data->display_at_login != 2) {
			return true;
		}
	}
	return false;
 }
/* get and check first homework
 * @pram id_group
 * return list
 */
function is_first_assign($group_id) {
	$filter['homework_result'] = false;
	$filter['is_active'] = 1;
	$filter['offset'] = 0;
	$filter['items_per_page'] = 1;
	
	$homeworks = MWDB::get_group_homeworks($group_id, $filter, $filter['offset'], $filter['items_per_page']);
	return $homeworks;
}

/* get option :  name with lang
* @pram short lang
* @result : string
*/
function get_option_name_link() {
	$get_name_link = !is_math_panel() ? LINK_LANG_ENGLISH . '-' . get_short_lang_code() : LINK_LANG_MATH . '-' . get_short_lang_code() ;
	return  mw_get_option($get_name_link);
}

/* get HW_TOOL AND SELF-STUDY HOMEWORK
* @pram : null
* @result : status
*/
function get_ws_subscribed() {
	return !is_math_panel() ? ($_SESSION['subscription']['HW_TOOL'] || $_SESSION['subscription']['SELF_STUDY']) 
							: ($_SESSION['subscription']['HW_TOOL_MATH'] || $_SESSION['subscription']['SELF_STUDY_MATH']);
}

function compare_fraction($answer, $result) {
	$_parse_result 	= explode(' ', $result);
	$_case 			= 0;
	$_result 		= false;
	if(count($_parse_result) > 1) { /* 1 : - , 2 : 2 2/3*/ 
	$_case = (!is_numeric($_parse_result[0])) ? 1 : 2; }

	switch($_case) {
		case 1 	: 
			$_result = (str_replace('- ', '-',$answer)) == str_replace('- ', '-', $result)  ? true : false;
			break;
		case 2 	: 
			$_result = $answer == $result ? true : false;
			if(!$_result) { 
				$_parse_part 	= explode('/',$_parse_result[1]);
				
				$result 		= (($_parse_result[0] * $_parse_part[1]) + $_parse_part[0]) . '/' . $_parse_part[1];
				$_result 		= (str_replace('- ', '-',$answer)) ==  $result ? true : false;
			}
			break;
		default : 
			$_result = (str_replace('- ', '-',$answer)) == str_replace('- ', '-', $result)  ? true : false;
		break;
	} 
	return $_result;
}

function is_sat_special_group($user_id = 0) {
	
	global $wpdb;
	if(is_mw_admin() || is_mw_super_admin()) {
		return true;
	}
	
	$user 			= $user_id ? $user_id : get_current_user_id();
	$query = 'SELECT COUNT(*) AS result 
			 FROM 		' . $wpdb->prefix . 'dict_group_students AS wdgs
			 RIGHT JOIN ' . $wpdb->prefix . 'dict_groups AS wdg ON wdgs.group_id = wdg.id
			 WHERE (wdgs.student_id = '. $user .' || wdg.created_by = '. $user .') AND wdg.special_group = 1'
	;
	$result = $wpdb->get_results($query);
	
	return  $result[0]->result > 0 ? true : false;
}

function add_history_email_marketing($success,$fail,$word){
	global $wpdb;
	$wpdb->insert(
		$wpdb->prefix.'dict_history_email_marketing',
		array(
			'success' => $success,
			'fail' => $fail,
			'word' => $word
		));
}

function get_language_type_of_user($user_id){
	global $wpdb;
	$user_info = get_user_meta($user_id,'language_type',true);
	if($user_info){
		switch ($user_info) {
			case 'ja':
				echo 'J';
				break;
			case 'vi':
				echo 'V';
				break;
			case 'ko':
				echo 'K';
				break;
			case 'zh':
				echo 'Z';
				break;
			case 'zh-tw':
				echo 'ZT';
				break;	
			default:
				echo 'E';
				break;
		}
	}else return 'E';
}

function get_location_timezone(){

 	//$data = json_decode(@file_get_contents('http://ip-api.io/api/json'));
 	$data = @unserialize(@file_get_contents('http://www.geoplugin.net/php.gp?ip=' . $_SERVER['REMOTE_ADDR']));
	if($data['geoplugin_status'] == 200){
	  	$date_time_zone = new DateTimeZone($data['geoplugin_timezone']);
	  	$tz_offset = timezone_offset_get($date_time_zone, date_create());  
	}else{
	  	$tz_offset = 0;
	}
 	return $tz_offset;
}

function convert_timezone_to_location2($str){
	switch ($str) {
		case '-6':
			$location = 'Minneapolis of United States';
			break;

		case '-5':
			$location = 'Colorada of United States';
			break;

		case '-7':
			$location = 'San Francisco of United States';
			break;

		case '-10':
			$location = 'Hawaii of United States';
			break;

		case '+10':
			$location = 'Guam of United States';
			break;

		case '+9':
			$location = 'Tokyo of Japan';
			break;

		case '+9':
			$location = 'Seoul of Korea';
			break;

		case '+8':
			$location = 'Beijing of China';
			break;

		case '+7':
			$location = 'Hanoi of Viet nam';
			break;

		case '+7':
			$location = 'Bangkok of Thailand';
			break;

		case '+7':
			$location = 'Myanmar';
			break;

		case '+6':
			$location = 'Bangladesh';
			break;

		case '+5':
			$location = 'Sri Lanka';
			break;

		case '+5':
			$location = 'New Delhi of India';
			break;

		case '+5':
			$location = 'Mumbai of India';
			break;

		case '0':
			$location = 'London of England';
			break;

		case '+5':
			$location = 'Sydney of Australia';
			break;

		default:
			$location = 'London of England';
			break;
	}
	return $location;
}
function convert_timezone_to_location($str){
	switch ($str) {
		case '0':
			$location = 'London';
			break;

		case '1':
			$location = 'New York';
			break;

		case '2':
			$location = 'Minneapolis';
			break;

		case '3':
			$location = 'Colorado';
			break;

		case '4':
			$location = 'San Francisco';
			break;

		case '5':
			$location = 'Hawaii';
			break;

		case '6':
			$location = 'Guam';
			break;

		case '7':
			$location = 'Tokyo';
			break;

		case '8':
			$location = 'Seoul';
			break;

		case '9':
			$location = 'Beijing';
			break;

		case '10':
			$location = 'Xianyang';
			break;

		case '11':
			$location = 'Hanoi';
			break;

		case '12':
			$location = 'Bangkok';
			break;

		case '13':
			$location = 'Myanmar';
			break;

		case '14':
			$location = 'Bangladesh';
			break;

		case '15':
			$location = 'Sri Lanka';
			break;

		case '16':
			$location = 'New Delhi';
			break;

		case '17':
			$location = 'Mumbai';
			break;

		case '18':
			$location = 'London';
			break;

		case '19':
			$location = 'Sydney';
			break;

		default:
			$location = 'London';
			break;
	}
	return $location;
}
function convert_timezone_to_name($index){
	switch ($index) {
		case '0':
			$name = 'Europe/London';
			break;

		case '1':
			$name = 'America/New_York';
			break;

		case '2':
			$name = 'America/Chicago';
			break;

		case '3':
			$name = 'America/Denver';
			break;

		case '4':
			$name = 'America/Los_Angeles';
			break;

		case '5':
			$name = 'Pacific/Honolulu';
			break;

		case '6':
			$name = 'Pacific/Guam';
			break;

		case '7':
			$name = 'Asia/Tokyo';
			break;

		case '8':
			$name = 'Asia/Seoul';
			break;

		case '9':
		case '10':
			$name = 'Asia/Shanghai';
			break;

		case '11':
			$name = 'Asia/Ho_Chi_Minh';
			break;

		case '12':
			$name = 'Asia/Bangkok';
			break;

		case '13':
			$name = 'Asia/Rangoon';
			break;

		case '14':
			$name = 'Asia/Dhaka';
			break;

		case '15':
			$name = 'Asia/Colombo';
			break;

		case '16':
		case '17':
			$name = 'Asia/Kolkata';
			break;

		case '18':
			$name = 'Europe/London';
			break;

		case '19':
			$name = 'Australia/Sydney';
			break;

		default:
			$name = 'Europe/London';
			break;
	}
	return $name;
}
?>