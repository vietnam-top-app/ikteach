<?php
/*
 * General database access class
 */

class MWDB {

	/*
	 * filter order by
	 *
	 * @param array $whitelist		Whitelist of column name
	 * @param string $column		Column to check
	 * @param string $dir			Direction
	 *
	 * @return string
	 */
	public static function filter_order_by($whitelist, $column, $dir = null)
	{
		if(in_array($column, $whitelist) === true) {
			$query = ' ORDER BY ' . $column;

			if(!empty($dir) && in_array(strtolower($dir), array('asc', 'desc')) === true) {
				$query .= ' ' . $dir;
			}
		}

		return $query;
	}

	/*
	 * randomly return a trivia from vocabulary sheet
	 *
	 * @param string $dictionary 		the dictionary slug
	 * @param int sheet_category		sheet category
	 *
	 * @return object
	 */
	public static function random_quiz($dictionary, $sheet_category)
	{
		global $wpdb;

		switch($dictionary) {

			case 'intermediate' :
			case 'elementary' :
				$from = 1; $to = 8;
				break;
			case 'elearner' :
			case 'collegiate' :
			case 'medical' :
				$from = 9; $to = 12;
				break;
		}

		$active_cond = '';
		if($sheet_category == 1) {
			$active_cond = ' AND active = 1 ';
		}

		$sheet = $wpdb->get_row('SELECT s.*, gr.name AS grade FROM ' . $wpdb->prefix . 'dict_sheets AS s
								 JOIN ' . $wpdb->prefix . 'dict_grades AS gr ON gr.id = s.grade_id
								 WHERE assignment_id = ' . ASSIGNMENT_VOCAB_GRAMMAR . '
									AND gr.name BETWEEN ' . $from . ' AND ' . $to . '
									AND category_id = ' . esc_sql($sheet_category) . $active_cond . '
								 ORDER BY RAND()');

		$quiz = json_decode($sheet->questions);
		$index = rand(0, count($quiz->question) - 1);
		$obj = new stdCLass;
		$obj->level = $sheet->grade;
		$obj->lesson = $sheet->sheet_name;
		$obj->quiz = array('sentence' => $quiz->question[$index],
							'choice' => array(
									$quiz->c_answer[$index],
									$quiz->w_answer1[$index],
									$quiz->w_answer2[$index]
								),
							'q' => $quiz->quiz[$index],
							'ca' => $quiz->c_answer[$index]);
		return $obj;
	}

	/*
	 * Update user data
	 *
	 * @param $user		the user object
	 *
	 * @return boolean
	 */
	public static function update_user($user = null, $local = 0)
	{
		global $_REAL_POST;

		if(is_null($user)) {
			$user = wp_get_current_user();
		}

		$has_err = false;

		if($_REAL_POST['new-email'] != '')
		{
			if( !is_email($_REAL_POST['new-email']) ) {
				ik_enqueue_messages(__('New email is not valid.', 'iii-dictionary'), 'error');
				$has_err = true;
			}
			$userdata['user_email'] = $_REAL_POST['new-email'];
		}

		if($_REAL_POST['new-password'] != '')
		{
			if ( strlen( $_REAL_POST['old-password'] ) === 0 ) {
				ik_enqueue_messages(__('Please enter your current password.', 'iii-dictionary'), 'error');
				$has_err = true;
			}
			if( !wp_check_password($_REAL_POST['old-password'], $user->user_pass) ) {
				ik_enqueue_messages(__('Current password not match.', 'iii-dictionary'), 'error');
				$has_err = true;
			}
			if ( strlen( $_REAL_POST['new-password'] ) === 0 ) {
				ik_enqueue_messages(__('New passwords must not be empty.', 'iii-dictionary'), 'error');
				$has_err = true;
			}
			if ( $_REAL_POST['new-password'] !== $_REAL_POST['confirm-password'] ) {
				ik_enqueue_messages(__('Passwords must match.', 'iii-dictionary'), 'error');
				$has_err = true;
			}
			if ( strlen( $_REAL_POST['new-password'] ) < 6 ) {
				ik_enqueue_messages(__('Passwords must be at least six characters long.', 'iii-dictionary'), 'error');
				$has_err = true;
			}
			$userdata['user_pass'] = $_REAL_POST['new-password'];
		}

		if(trim($_REAL_POST['first-name']) != '')
		{
			$userdata['first_name'] = $_REAL_POST['first-name'];

			$userdata['display_name'] = $userdata['first_name'] . ' ' . $user->last_name;
		}

		if(trim($_REAL_POST['last-name']) != '')
		{
			$userdata['last_name'] = $_REAL_POST['last-name'];

			$userdata['display_name'] = $user->first_name . ' ' . $userdata['last_name'];
		}

		if(isset($userdata['first_name']) && isset($userdata['last_name']))
		{
			$userdata['display_name'] = $userdata['first_name'] . ' ' . $userdata['last_name'];
		}

		if($_REAL_POST['birth-m'] != '00' && $_REAL_POST['birth-d'] != '00' && $_REAL_POST['birth-y'] != '')
		{
			if(strlen($_REAL_POST['birth-y']) != 4) {
				ik_enqueue_messages(__('Your birth year must be 4 digits. Please enter a valid birth year.', 'iii-dictionary'), 'error');
				$has_err = true;
			}
			else {
				if(checkdate($_REAL_POST['birth-m'], $_REAL_POST['birth-d'], $_REAL_POST['birth-y'])) {
					$usermeta['date_of_birth'] = $_REAL_POST['birth-m'] . '/' . $_REAL_POST['birth-d'] . '/' . $_REAL_POST['birth-y'];
				}
				else {
					ik_enqueue_messages(__('Invalid date of birth.', 'iii-dictionary'), 'error');
					$has_err = true;
				}
			}
		}

		$usermeta['language_type'] = $_REAL_POST['language_type'];
		
		// user want to register as a teacher. Validate required fields
		if(isset($_POST['update-teacher']) || isset($_POST['i-agree-update']))
		{
			if(empty($_REAL_POST['language_teach'])){
				ik_enqueue_messages(__('You must choose language teach','iii-dictionary'));
				$has_err = true;
			}else{
				$language_teach = $_REAL_POST['language_teach'];
				$str_lang = '';
				foreach ($language_teach as $lang) {
					$str_lang .= $lang.',';
				}
				$str_lang = substr($str_lang,0 ,-1);
				$usermeta['language_teach'] = $str_lang;
			}

			if(trim($_REAL_POST['mobile-number']) == '') {
				ik_enqueue_messages(__('Mobile phone number field cannot be empty', 'iii-dictionary'), 'error');
				$has_err = true;
			}
			else {
				$usermeta['mobile_number'] = $_REAL_POST['mobile-number'];
			}

			if(trim($_REAL_POST['email-paypal']) == '') {
				ik_enqueue_messages(__('Email for paypal field cannot be empty', 'iii-dictionary'), 'error');
				$has_err = true;
			}elseif(!is_email($_REAL_POST['email-paypal'])){
				ik_enqueue_messages(__('Email for paypal is not valid.', 'iii-dictionary'), 'error');
				$has_err = true;
			}else {
				$usermeta['email_paypal'] = $_REAL_POST['email-paypal'];
			}

			if(trim($_REAL_POST['last-school']) == '') {
				ik_enqueue_messages(__('Last school field cannot be empty', 'iii-dictionary'), 'error');
				$has_err = true;
			}
			else {
				$usermeta['last_school'] = $_REAL_POST['last-school'];
			}

			if(trim($_REAL_POST['previous-school']) == '') {
				ik_enqueue_messages(__('Latest school you tought field cannot be empty', 'iii-dictionary'), 'error');
				$has_err = true;
			}
			else {
				$usermeta['previous_school'] = $_REAL_POST['previous-school'];
			}
			if(!isset($_POST['agree-english-teacher']) && !isset($_POST['agree-math-teacher'])){
				ik_enqueue_messages(__('You must agree "Teacher\'s Registration Agreement"', 'iii-dictionary'), 'error');
				$has_err = true;
			}

			$image = $_FILES['input-image'];
		}

		if(!$has_err)
		{
			$userdata['ID'] = $user->ID;
			
			$result = wp_update_user($userdata);

			if(!is_wp_error($result)) {
				foreach($usermeta as $meta_key => $meta_value) {
					update_user_meta($user->ID, $meta_key, $meta_value);
				}
				if(isset($_POST['update-teacher']) || isset($_POST['i-agree-update'])) {
                                        ik_set_user_avatar($user->ID, $image);
                                        $user_avatar = ik_get_user_avatar($user->ID);
                                        if ($user_avatar == '' ) {
                                            ik_enqueue_messages(__('Please upload your face photo when you can.', 'iii-dictionary'), 'error');
                                        }
					$agreement_update_date = mw_get_option('agreement-update-date');
					if(isset($_POST['agree-math-teacher'])&&$_POST['agree-math-teacher']==2){
						$user->add_role('mw_registered_math_teacher');
						update_user_meta($user->ID, 'math_teacher_agreement_ver', $agreement_update_date);
					}
					if(isset($_POST['agree-english-teacher'])&&$_POST['agree-english-teacher']==1){
                                                $user->add_role('mw_registered_teacher');
						update_user_meta($user->ID, 'teacher_agreement_ver', $agreement_update_date);
					}
                                        
					if(!isset($_POST['update-teacher'])) {
						update_user_meta($user->ID, 'teacher_registered_on', date('Y-m-d', time()));

						ik_enqueue_messages(__('Successfully register as teacher.', 'iii-dictionary'), 'success');
					}
					else {
						ik_enqueue_messages(__('Successfully updated.', 'iii-dictionary'), 'success');
					}

					$agreement_update_date = mw_get_option('agreement-update-date');
					update_user_meta($user->ID, 'teacher_agreement_ver', $agreement_update_date);

				}
				else {
					ik_enqueue_messages(__('Successfully update user.', 'iii-dictionary'), 'success');
				}

				return true;
			}
			else {
				ik_enqueue_messages(__($result->get_error_message(), 'iii-dictionary'), 'error');
			}
		}

		return false;
	}

	/*
	 * return all sheets
	 *
	 * @return array
	 */
	public static function get_all_sheets()
	{
		global $wpdb;

		$sheets = $wpdb->get_results(
			'SELECT s.*, gr.name AS grade
			FROM ' . $wpdb->prefix . 'dict_sheets AS s
			JOIN ' . $wpdb->prefix . 'dict_grades AS gr ON gr.id = s.grade_id'
		);

		// $where[] = 'category_id <> 5'; // sheet type filter
		return $sheets;
	}

	/*
	 * return all available english sheets
	 *
	 * @param array 	$filter 		filter value
	 * @param boolean 	$active_only	get active sheets only
	 * @param boolean   $admin_panel	flag to determine results is for admin panel
	 *
	 * @return array
	 */
	public static function get_sheets($filter, $active_only = false, $admin_panel = false)
	{
		global $wpdb;

		$query = 'SELECT COUNT(*)
				  FROM ' . $wpdb->prefix . 'dict_sheets AS t
				  JOIN ' . $wpdb->prefix . 'dict_grades AS gr ON gr.id = t.grade_id
				  JOIN ' . $wpdb->prefix . 'dict_homework_assignments AS ha ON ha.id = t.assignment_id
				  JOIN ' . $wpdb->prefix . 'dict_homework_assignment_langs AS hal ON hal.assignment_id = ha.id AND hal.lang = \'' . get_short_lang_code() . '\'
				  JOIN ' . $wpdb->prefix . 'dict_homework_types AS ht ON ht.id = t.homework_type_id
				  LEFT JOIN ' . $wpdb->prefix . 'dict_dictionaries AS d ON d.id = t.dictionary_id
				  ';
		if($admin_panel) {
			$query .= 'LEFT JOIN ' . $wpdb->users . ' AS u ON u.iD = t.created_by';
		}else {
			$query .= 'JOIN ' . $wpdb->users . ' AS u ON u.iD = t.created_by';
		}
		$where[] = 'category_id <> 5'; // exclude Math sheets

		if($filter['sheet-name'] != '') {
			$where[] = 'sheet_name LIKE \'%' . esc_sql($filter['sheet-name']) . '%\'';
		}

		if($filter['assignment-id'] != '') {
			$where[] = 't.assignment_id = ' . esc_sql($filter['assignment-id']);
		}

		if($filter['grade'] != '') {
			$where[] = 't.grade_id = \'' . esc_sql($filter['grade']) . '\'';
		}

		if($filter['homework-types'] != '') {
			$where[] = 'homework_type_id = ' . esc_sql($filter['homework-types']);

			if($filter['homework-types'] == HOMEWORK_MY_OWN || $filter['homework-types'] == HOMEWORK_LICENSED) {
				$where[] = 't.created_by = ' . get_current_user_id();
			}
		}
		else {
			$excluded_types[] = HOMEWORK_MY_OWN;
			$excluded_types[] = HOMEWORK_LICENSED;

			/* if(!is_homework_tools_subscribed()) {
				$excluded_types[] = HOMEWORK_SUBSCRIBED;
			} */

			if(!is_mw_admin() && !is_mw_super_admin() && !is_sat_special_group()) {
				$excluded_types[] = HOMEWORK_CLASS;
			}

			if(!empty($excluded_types)) {
				$where[] = '(homework_type_id NOT IN (' . implode(',', $excluded_types) . ')
							 OR ((homework_type_id = ' . HOMEWORK_MY_OWN . ' OR homework_type_id = ' . HOMEWORK_LICENSED . ') AND created_by = ' . get_current_user_id() . '))';
			}
			
		}

		if(!$admin_panel) {
			$where[] = 'trivia_exclusive = 0';
		}
		else {
			if($filter['trivia-exclusive'] != '') {
				$where[] = 'trivia_exclusive = ' . $filter['trivia-exclusive'];
			}
		}

		if($active_only) {
			$where[] = 'active = 1';
		}
		else {
			if($filter['active'] != '') {
				$where[] = 'active = ' . $filter['active'];
			}
		}

		if($filter['lang'] != '') {
			$where[] = 't.lang = \'' . esc_sql($filter['lang']) . '\'';
		}
		else {
			if(!is_mw_admin() && !is_mw_super_admin() && $filter['assignment-id'] != ASSIGNMENT_REPORT) {
				$where[] = 't.lang IN ( \'en\', \'' . get_short_lang_code() . '\')';
			}
		}

		if(!empty($where)) {
			$query .= ' WHERE ' . implode(' AND ', $where);
		}

		$offset = isset($filter['offset']) ? $filter['offset'] : 0;
		$items_per_page = isset($filter['items_per_page']) ? $filter['items_per_page'] : 15;

		// get total row
		$total = $wpdb->get_col($query);

		$columns = 't.*, gr.name AS grade , ha.type AS type, d.id as did, d.name, hal.name AS assignment, ht.name AS homework_type, u.display_name';
		if(!empty($optional_columns)) {
			$columns .= ',' . implode(',', $optional_columns);
		}

		$query = str_replace('COUNT(*)', $columns, $query);

		if(!empty($filter['orderby'])) {
			if($filter['orderby'] == 'grade') {
				$query .= ' ORDER BY LPAD(grade, 10, \'0\') ' . $filter['order-dir'];
			}
			else {
				$query .= ' ORDER BY ' . $filter['orderby'] . ' ' . $filter['order-dir'];
			}
		}

		$query .= ' LIMIT ' . $offset . ',' . $items_per_page;

		$sheets = $wpdb->get_results($query);
		$obj = new stdCLass;
		$obj->items = $sheets;
		$obj->total = $total[0];
                

		return $obj;
	}

	/*
	 * get offering or offered worksheets
	 *
	 * @param string $type		'to_offer' or 'offered'
	 * @param array $filter
	 * @param int $offset
	 * @param int $items_per_page
	 *
	 * @return object
	 */
	public static function get_worksheet_offering($type, $filter = array(), $offset = 0, $items_per_page = 99999999)
	{
		global $wpdb;

		$query = 'SELECT COUNT(*)
				  FROM ' . $wpdb->prefix . 'dict_sheets AS s
				  JOIN ' . $wpdb->prefix . 'dict_grades AS gr ON gr.id = s.grade_id
				  JOIN ' . $wpdb->prefix . 'dict_homework_assignments AS ha ON ha.id = s.assignment_id
				  JOIN ' . $wpdb->prefix . 'dict_homework_assignment_langs AS hal ON hal.assignment_id = ha.id AND hal.lang = \'' . get_short_lang_code() . '\'
				  JOIN ' . $wpdb->prefix . 'dict_dictionaries AS d ON d.id = s.dictionary_id
				  JOIN ' . $wpdb->users . ' AS u ON u.iD = s.created_by';

		if($type == 'offered') {
			$query .= ' JOIN ' . $wpdb->prefix . 'dict_offered_sheets AS os ON os.sheet_id = s.id';

			$where[] = 'os.removed = 0';

			if($filter['user_offer']) {
				$where[] = 'os.user_id = ' . $filter['user_id'];
			}
			else {
				$query .= ' LEFT JOIN ' . $wpdb->prefix . 'dict_worksheet_purchase_history AS wph ON wph.offer_id = os.id AND wph.purchased_by = ' . $filter['user_id'];
				$optional_columns[] = 'wph.offer_id AS purchased_offer_id';
				$group_by[] = 'os.id';
			}

			$optional_columns[] = 'os.id AS offer_id';
			$optional_columns[] = 'offered_price';
			$optional_columns[] = 'offered_on';
		}

		if($type == 'to_offer') {
			$where[] = 's.id NOT IN (SELECT sheet_id FROM ' . $wpdb->prefix . 'dict_offered_sheets WHERE removed = 0)';

			if(!is_mw_admin() && !is_mw_super_admin()) {
				$where[] = 'homework_type_id = ' . HOMEWORK_MY_OWN . ' AND s.created_by = ' . $filter['user_id'];
			}
			else {
				$where[] = '(homework_type_id = ' . HOMEWORK_MY_OWN . ' AND s.created_by = ' . $filter['user_id'] . '
								OR homework_type_id IN (' . HOMEWORK_SUBSCRIBED . ',' . HOMEWORK_CLASS .'))';
			}
		}

		if($filter['assignment-id'] != '') {
			$where[] = 's.assignment_id = ' . esc_sql($filter['assignment-id']);
		}

		if($filter['grade'] != '') {
			$where[] = 's.grade_id = \'' . esc_sql($filter['grade']) . '\'';
		}

		$where[] = 's.active = 1';
		
		if(!empty($where)) {
			$query .= ' WHERE ' . implode(' AND ', $where);
		}

		if(!empty($group_by)) {
			$query .= ' GROUP BY ' . implode(',', $group_by);
		}

		// get total row
		$total = $wpdb->get_col($query);

		$query .= ' ORDER BY LPAD(grade, 10, \'0\'), sheet_name';

		$columns = 's.id, s.assignment_id, gr.name AS grade, s.sheet_name, s.created_by, s.description, d.name AS dictionary_name, hal.name AS assignment, u.display_name AS creator';
		if(!empty($optional_columns)) {
			$columns .= ',' . implode(',', $optional_columns);
		}

		$query = str_replace('COUNT(*)', $columns, $query);
		$query .= ' LIMIT ' . $offset . ',' . $items_per_page;

		$sheets = $wpdb->get_results($query);

		$obj = new stdCLass;
		$obj->items = $sheets;
		$obj->total = $total[0];

		return $obj;
	}

	/*
	 * get list of grading requests
	 *
	 * @param array $filter
	 * @param int $offset
	 * @param int $items_per_page
	 *
	 * @return object
	 */
	public static function get_worksheet_grading_requests($filter, $offset = 0, $items_per_page = 99999999)
	{
		global $wpdb;

		$query = 'SELECT COUNT(*)
				FROM ' . $wpdb->prefix . 'dict_worksheet_grading_requests AS gr
				JOIN ' . $wpdb->prefix . 'dict_homework_results AS hr ON hr.id = gr.homework_result_id
				JOIN ' . $wpdb->prefix . 'dict_homeworks AS h ON h.id = hr.homework_id
				JOIN ' . $wpdb->prefix . 'dict_sheets AS s ON s.id = h.sheet_id
				JOIN ' . $wpdb->prefix . 'dict_grades AS sgr ON sgr.id = s.grade_id
				JOIN ' . $wpdb->prefix . 'users AS u ON u.ID = gr.requested_by
				';

		if(!empty($filter['grade'])) {
			$where[] = 's.grade_id = \'' . esc_sql($filter['grade']) . '\'';
		}

		if(!empty($where)) {
			$query .= ' AND ' . implode(' AND ', $where);
		}

		$total = $wpdb->get_col($query);

		$query = str_replace('COUNT(*)', 'gr.requested_by,gr.finished,gr.id,gr.homework_id as idsheets,gr.status,gr.accepted_by,gr.requested_on,gr.accepted_on,gr.paid_amount,gr.id AS request_id, sgr.name AS grade, s.sheet_name, u.display_name AS requester, gr.paid_amount', $query);
		$query .= ' LIMIT ' . $offset . ',' . $items_per_page;
		
		$requests = $wpdb->get_results($query);

		$obj = new stdCLass;
		$obj->items = $requests;
		$obj->total = $total[0];

		return $obj;
	}
	/*
	 * get list of grading requests
	 *
	 * @param array $filter
	 * @param int $offset
	 * @param int $items_per_page
	 *
	 * @return object
	 */
	public static function get_worksheet_grading_requests_writing_practice($filter, $offset = 0, $items_per_page = 99999999)
	{
		global $wpdb;

		$query = 'SELECT COUNT(*)
				FROM ' . $wpdb->prefix . 'dict_worksheet_grading_requests AS gr
				JOIN ' . $wpdb->prefix . 'dict_homework_results AS hr ON hr.id = gr.homework_result_id
				JOIN ' . $wpdb->prefix . 'dict_sheets AS s ON s.id = gr.homework_id
				JOIN ' . $wpdb->prefix . 'users AS u ON u.ID = gr.requested_by
				WHERE gr.finished = 0';

		if(!empty($filter['grade'])) {
			$where[] = 's.grade_id = \'' . esc_sql($filter['grade']) . '\'';
		}

		if(!empty($where)) {
			$query .= ' AND ' . implode(' AND ', $where);
		}

		$total = $wpdb->get_col($query);

		$query = str_replace('COUNT(*)', 'gr.requested_by,gr.finished,gr.id,gr.requested_on,gr.accepted_on,gr.paid_amount,gr.id AS request_id, s.sheet_name, u.display_name AS requester, gr.paid_amount', $query);
		$query .= ' LIMIT ' . $offset . ',' . $items_per_page;
		$requests = $wpdb->get_results($query);

		$obj = new stdCLass;
		$obj->items = $requests;
		$obj->total = $total[0];

		return $obj;
	}
        
	/*
	 * get a grading request
	 *
	 * @param int $request_id
	 *
	 * @return object
	 */
	public static function get_worksheet_grading_request($request_id)
	{
		global $wpdb;

		$request = $wpdb->get_row(
			'SELECT gr.*, sheet_name
			FROM ' . $wpdb->prefix . 'dict_worksheet_grading_requests AS gr
			JOIN ' . $wpdb->prefix . 'dict_homeworks AS h ON h.id = gr.homework_id
			JOIN ' . $wpdb->prefix . 'dict_sheets AS s ON s.id = h.sheet_id
			WHERE gr.id = ' . esc_sql($request_id)
		);

		return $request;
	}
	/*
	 * get a grading request from self-study
	 *
	 * @param int $request_id
	 *
	 * @return object
	 */
	public static function get_worksheet_grading_request_self_study($request_id)
	{
		global $wpdb;

		$request = $wpdb->get_row(
			'SELECT gr.*, sheet_name
			FROM ' . $wpdb->prefix . 'dict_worksheet_grading_requests AS gr
			JOIN ' . $wpdb->prefix . 'dict_sheets AS s ON s.id = gr.homework_id
			WHERE gr.id = ' . esc_sql($request_id)
		);

		return $request;
	}
        
        public static function get_sheet_name($request_id)
	{
		global $wpdb;

		$request = $wpdb->get_row(
			'SELECT *
			FROM ' . $wpdb->prefix . 'dict_sheets s
			WHERE id = ' .$request_id
		);
		return $request->sheet_name;
	}

	/*
	 * return all practice sheets made by admins
	 *
	 * @param int $assignment_id
	 * @return array
	 */
	public static function get_practice_sheets($assignment_id)
	{
		global $wpdb;

		$current_user_id = get_current_user_id();

		$sub_query = '';

		if(is_mw_student() || !is_user_logged_in()) {
			$homework_type_id[] = HOMEWORK_PUBLIC;

			//if(is_homework_tools_subscribed()) {
				$homework_type_id[] = HOMEWORK_SUBSCRIBED;
			//}
			
			if(!empty($_GET['hid'])){
				$homework_type_id[] = HOMEWORK_MY_OWN;
				$homework_type_id[] = HOMEWORK_LICENSED; 
				$homework_type_id[] = HOMEWORK_CLASS;
			}

			// query to select homework sheets assigned to subscrbed groups that the user has joined
			$sub_query = ' OR s.id IN (SELECT s.id
						  FROM ' . $wpdb->prefix . 'dict_sheets AS s
						  JOIN ' . $wpdb->prefix . 'dict_homeworks AS h ON h.sheet_id = s.id
						  JOIN ' . $wpdb->prefix . 'dict_group_students AS gs ON gs.group_id = h.group_id
						  JOIN ' . $wpdb->prefix . 'dict_user_subscription AS us ON us.group_id = h.group_id
						  WHERE student_id = ' . $current_user_id . ' AND expired_on > NOW()
						  GROUP BY s.id)';
		}

		$query = 'SELECT s.id AS sheet_id, assignment_id, homework_type_id, s.ws_default ,gr.name AS grade, sheet_name, dictionary_id, questions, passages, p.id AS pid, p.answers AS practice_answers
				  FROM ' . $wpdb->prefix . 'dict_sheets AS s
				  JOIN ' . $wpdb->prefix . 'dict_grades AS gr ON gr.id = s.grade_id
				  LEFT JOIN ' . $wpdb->prefix . 'dict_practice_results AS p ON p.sheet_id = s.id AND user_id = ' . $current_user_id . '
				  WHERE assignment_id = ' . $assignment_id . ' AND active = 1 AND category_id = 1';

		if(!empty($homework_type_id)) {
			$query .= ' AND (homework_type_id IN (' . implode(',', $homework_type_id) . ') 
							 OR (homework_type_id = ' . HOMEWORK_MY_OWN . ' AND created_by = ' . $current_user_id . ')' . $sub_query . ')';
		}
		else {
			if(is_user_logged_in() && empty($_GET['hid'])) {
				$query .= ' AND homework_type_id <> ' . HOMEWORK_CLASS;
			}
			// user isn't loggedin, return public homework only
			/*
			else {
				$query .= ' AND homework_type_id = ' . HOMEWORK_PUBLIC;
			}
			*/
		}

		$query .= ' AND s.lang IN (\'en\', \'' . get_short_lang_code() . '\')';

		$query .= ' ORDER BY LPAD(grade, 10, \'0\'), homework_type_id, sheet_name';

		$sheets = $wpdb->get_results($query);
		
		return $sheets;
	}

	/*
	 * get a sheet based on sheet id
	 *
	 * @param int $id 		the sheet id
	 * @param int $group_id		the group id that the sheet is assigned to. In case we want to check
	 *
	 * @return object
	 */
	public static function get_sheet($id, $group_id = 0)
	{
		global $wpdb;

		$query = 'SELECT s.*, gr.name AS grade, s.id AS sheet_id, h.id AS homework_id, hr.id AS homework_result_id, finished_question, hr.answers
					FROM ' . $wpdb->prefix . 'dict_sheets AS s
					JOIN ' . $wpdb->prefix . 'dict_grades AS gr ON gr.id = s.grade_id
					LEFT JOIN ' . $wpdb->prefix . 'dict_homeworks AS h ON h.sheet_id = s.id
					LEFT JOIN ' . $wpdb->prefix . 'dict_homework_results AS hr ON hr.homework_id = h.id AND userid = ' . get_current_user_id() . '
					WHERE s.id = %d';

		if($group_id) {
			$query .= ' AND h.group_id = ' . $group_id;
		}

		$sheet = $wpdb->get_row($wpdb->prepare($query, $id));
		return $sheet;
	}

	/*
	 * return all homework sheets assigned in student's groups
	 *
	 * @param int $assignment_id
	 * @param boolean $include_practice		include practice homework
	 *
	 * @return array
	 */
	public static function get_homework_sheets($assignment_id, $include_practice = false)
	{
		global $wpdb;

		$teacher_test_group = mw_get_option('teacher-test-group');
		$current_uid = get_current_user_id();

		$query = 'SELECT h.id AS homework_id, s.id AS sheet_id, s.assignment_id, s.homework_type_id, gr.name AS grade, sheet_name, dictionary_id, questions, passages, private, hs.id AS homework_result_id, userid, finished_question, finished, hs.answers, description
					 FROM ' . $wpdb->prefix . 'dict_homeworks AS h
					 JOIN ' . $wpdb->prefix . 'dict_sheets AS s ON s.id = h.sheet_id
					 JOIN ' . $wpdb->prefix . 'dict_grades AS gr ON gr.id = s.grade_id
					 LEFT JOIN ' . $wpdb->prefix . 'dict_homework_results AS hs ON hs.homework_id = h.id AND userid = ' . $current_uid . '
					 WHERE s.active = 1
						AND group_id IN (SELECT group_id 
										FROM ' . $wpdb->prefix . 'dict_group_students AS gs 
										JOIN ' . $wpdb->prefix . 'dict_groups AS g ON g.id = gs.group_id
										WHERE g.active = 1 AND gs.absented = 0 AND student_id = ' . $current_uid . ' AND group_id <> ' . $teacher_test_group . ')
						AND s.assignment_id = ' . $assignment_id . '
						AND (finished = 0 OR finished IS NULL)';

		if(!$include_practice) {
			$query .= ' AND h.for_practice = 0';
		}

		$query .= ' ORDER BY LPAD(grade, 10, \'0\'), sheet_name';

		$sheets = $wpdb->get_results($query);
		
		return $sheets;
	}

	/*
	 * get a worksheet offer
	 *
	 * @param int $offer_id
	 *
	 * @return object
	 */
	public static function get_worksheet_offer($offer_id)
	{
		global $wpdb;

		$offer = $wpdb->get_row(
			'SELECT os.*, sheet_name
			FROM ' . $wpdb->prefix . 'dict_offered_sheets AS os
			JOIN ' . $wpdb->prefix . 'dict_sheets AS s ON s.id = os.sheet_id
			WHERE os.id = ' . $offer_id
		);

		return $offer;
	}

	/*
	 * offer worksheet for sales
	 *
	 * @param array $data
	 *
	 * @return boolean
	 */
	public static function offer_worksheet($data)
	{
		global $wpdb;

		$valid = true;
		if(empty($data['offered_price'])) {
			ik_enqueue_messages(__('Offer price cannot be blank.', 'iii-dictionary'), 'error');
			$valid = false;
		}
		else if($data['offered_price'] < 0 || $data['offered_price'] > mw_get_option('teacher-max-point')) {
			ik_enqueue_messages(__('Invalid offer price.', 'iii-dictionary'), 'error');
			$valid = false;
		}

		if($valid) {
			$result = $wpdb->insert(
				$wpdb->prefix . 'dict_offered_sheets',
				array(
					'sheet_id' => $data['sid'],
					'user_id' => $data['user_id'],
					'offered_price' => $data['offered_price'],
					'offered_on' => date('Y-m-d', time())
				)
			);

			if($result) {
				ik_enqueue_messages(__('Successfully offer worksheet.', 'iii-dictionary'), 'success');
				return true;
			}
			else {
				ik_enqueue_messages(__('Cannot offer worksheet.', 'iii-dictionary'), 'error');
			}
		}

		return false;
	}

	/*
	 * remove a sheet offer
	 *
	 * @param int $sheet_id
	 *
	 * @return boolean
	 */
	public static function remove_offered_worksheet($offer_id)
	{
		global $wpdb;

		$result = $wpdb->update(
			$wpdb->prefix . 'dict_offered_sheets',
			array(
				'removed' => 1,
				'removed_on' => date('Y-m-d', time())
			),
			array('id' => $offer_id)
		);

		if($result) {
			ik_enqueue_messages(__('Successfully remove offer.', 'iii-dictionary'), 'success');
			return true;
		}
		else {
			ik_enqueue_messages(__('Cannot remove offer.', 'iii-dictionary'), 'error');
		}

		return false;
	}

	/*
	 * copy a sheet to current user library
	 *
	 * @param int $original_id		the sheet id to copy from
	 *
	 * @return mixed 				return insert id or false on error
	 */
	public static function copy_worksheet($original_id)
	{
		global $wpdb;

		$query = 'INSERT INTO ' . $wpdb->prefix . 'dict_sheets (
						assignment_id, homework_type_id, category_id, trivia_exclusive, grade_id, sheet_name, dictionary_id,
						active, questions, passages, description, created_by, created_on
					)
					SELECT assignment_id,' . HOMEWORK_LICENSED . ', category_id, trivia_exclusive, grade_id, sheet_name, dictionary_id,
						active, questions, passages, description, ' . get_current_user_id() . ', \'' . date('Y-m-d', time()) . '\'
					FROM ' . $wpdb->prefix . 'dict_sheets WHERE id = ' . $original_id;

		$result = $wpdb->query($query);

		return $result ? $wpdb->insert_id : false;
	}

	/*
	 * store worksheet purchase history
	 *
	 * @param array $data
	 *
	 * @return mixed		Return last insert id or false on error
	 */
	public static function store_worksheet_purchase_history($data)
	{
		global $wpdb;

		$result = $wpdb->insert(
			$wpdb->prefix . 'dict_worksheet_purchase_history',
			array(
				'offer_id' => $data['offer_id'],
				'purchased_by' => $data['purchased_by'],
				'copied_sheet_id' => $data['copied_sheet_id'],
				'paid_amount' => $data['paid_amount'],
				'purchased_on' => date('Y-m-d', time())
			)
		);

		if($result) {
			return $wpdb->insert_id;
		}
		else {
		}

		return false;
	}

	/*
	 * store point transactions history
	 *
	 * @param array $data
	 *
	 * @return mixed		Return last insert id or false on error
	 */
	public static function store_user_point_transaction($data)
	{
		global $wpdb;

		$result = $wpdb->insert(
			$wpdb->prefix . 'dict_user_point_transactions',
			array(
				'user_id' => $data['user_id'],
				'point_transaction_type_id' => $data['point_transaction_type_id'],
				'grading_worksheet_txn_id' => $data['grading_worksheet_txn_id'],
				'purchasing_worksheet_txn_id' => $data['purchasing_worksheet_txn_id'],
				'amount' => $data['amount'],
				'transaction_date' => date('Y-m-d H:i:s', time()),
				'note' => $data['note']
			)
		);

		if($result) {
			return $wpdb->insert_id;
		}
		else {
		}

		return false;
	}

	/*
	 * get user point transactions
	 *
	 * @param array $filter
	 * @param int $offset
	 * @param int $items_per_page
	 *
	 * @return object
	 */
	public static function get_user_point_transactions($filter, $offset = 0, $items_per_page = 99999999)
	{
		global $wpdb;

		$query = 'SELECT COUNT(*)
				FROM ' . $wpdb->prefix . 'dict_user_point_transactions AS upt
				JOIN ' . $wpdb->prefix . 'dict_point_transaction_types AS ptt ON ptt.id = upt.point_transaction_type_id
				LEFT JOIN ' . $wpdb->prefix . 'dict_worksheet_grading_requests AS gr ON gr.id = upt.grading_worksheet_txn_id
				LEFT JOIN ' . $wpdb->prefix . 'dict_worksheet_purchase_history AS ph ON ph.id = upt.purchasing_worksheet_txn_id
				WHERE upt.user_id = ' . get_current_user_id();

		$total = $wpdb->get_col($query);

		$query = str_replace('COUNT(*)', 'transaction_date AS txn_date, point_transaction_type_id AS txn_type_id, ptt.name AS txn_type, amount, upt.note', $query);
		$query .= ' LIMIT ' . $offset . ',' . $items_per_page;

		$transactions = $wpdb->get_results($query);

		$obj = new stdCLass;
		$obj->total = $total[0];
		$obj->items = $transactions;

		return $obj;
	}

	/*
	 * assign homework to a group
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public static function assign_homework(&$data)
	{
		global $wpdb;

		$has_err = false;

		if($data['group'] == '') {
			ik_enqueue_messages(__('Please select a Group.', 'iii-dictionary'), 'error');
			$has_err = true;
		}

		if($data['sheet_id'] == '') {
			ik_enqueue_messages(__('Invalid sheet id.', 'iii-dictionary'), 'error');
			$has_err = true;
		}

		// user didn't choose deadline
		if($data['deadline'] == '') {
			$deadline = '0000-00-00';
		}
		else {
			$deadline = date('Y-m-d', strtotime($data['deadline']));
		}

		if(empty($data['is_retryable'])) {
			$data['is_retryable'] = 0;
		}

		if(!$has_err) {
			$current_user_id = get_current_user_id();
			$created_on = date('Y-m-d', time());
			$result = $wpdb->insert(
				$wpdb->prefix . 'dict_homeworks', 
				array(
					'sheet_id' => $data['sheet_id'],
					'group_id' => $data['group'],
					'name' => $data['name'],
					'deadline' => $deadline,
					'is_retryable' => $data['is_retryable'],
					'for_practice' => $data['for_practice'],
					'created_by' => $current_user_id,
					'created_on' => $created_on,
					'active' => 1,
					'adminlastpage' => $data['adminlastpage'],
					'teacherlastpage' => $data['teacherlastpage']
				)
			);

			if($result) {
				return $wpdb->insert_id;
			}
		}

		return false;
	}

	/*
	 * assign homework to a group NEW
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public static function assign_homework_new(&$data)
	{
		global $wpdb;

		$has_err = false;

		if($data['group'] == '') {
			ik_enqueue_messages(__('Please select a Group.', 'iii-dictionary'), 'error');
			$has_err = true;
		}

		if($data['sheet_id'] == '') {
			ik_enqueue_messages(__('Invalid sheet id.', 'iii-dictionary'), 'error');
			$has_err = true;
		}

		// user didn't choose deadline
		if($data['deadline'] == '') {
			$deadline = '0000-00-00';
		}
		else {
			$deadline = date('Y-m-d', strtotime($data['deadline']));
		}

		if(empty($data['is_retryable'])) {
			$data['is_retryable'] = 0;
		}

		if(!$has_err) {
			$current_user_id = get_current_user_id();
			$created_on = date('Y-m-d', time());
			$result = $wpdb->insert(
				$wpdb->prefix . 'dict_homeworks', 
				array(
					'sheet_id' => $data['sheet_id'],
					'group_id' => $data['group'],
					'name' => $data['name'],
					'deadline' => $deadline,
					'is_retryable' => $data['is_retryable'],
					'for_practice' => $data['for_practice'],
					'created_by' => $current_user_id,
					'created_on' => $created_on,
					'active' => 2, // its not visable to Student yet
					'adminlastpage' => $data['adminlastpage'],
					'teacherlastpage' => $data['teacherlastpage']
				)
			);

			if($result) {
				return $wpdb->insert_id;
			}
		}

		return false;
	}

	/*
	 * update homework assignment
	 *
	 * @param array $data
	 *
	 * @return mixed		update id or false on error
	 */
	public static function update_homework_assignment($data)
	{
		global $wpdb;

		$id = $data['id'];
		unset($data['id']);
		
		if(!empty($data)) {
			$result = $wpdb->update(
				$wpdb->prefix . 'dict_homeworks',
				$data,
				array('id' => $id)
			);

			if($result !== false) {
				return $id;
			}
		}
		else {
			return $id;
		}

		return false;
	}

	/*
	 * remove a homework assignment
	 *
	 * @param int $id
	 *
	 * @return boolean
	 */
	public static function remove_homework($id)
	{
		global $wpdb;

		// make sure no user working on this homework
		//but now chagne it will remove user working on this homework
		$check = $wpdb->get_results(
			'SELECT h.id
			FROM ' . $wpdb->prefix . 'dict_homeworks AS h
			JOIN ' . $wpdb->prefix . 'dict_homework_results AS hr ON hr.homework_id = h.id
			WHERE h.id = ' . $id
		);

		if(!empty($check)) {
			//ik_enqueue_messages(__('Sorry, you can not remove this homework now. Some students have already started working on this homework.', 'iii-dictionary'), 'error');
			//return false;
			$flag =	$wpdb->delete(
				$wpdb->prefix . 'dict_homework_results',
				array('homework_id' => $id)
			);
			
			if($flag === false) {
				ik_enqueue_messages(__('An error occurred during this operation.', 'iii-dictionary'), 'error');
				return false;
			}	
		}

		// delete the homework
		$result = $wpdb->delete(
			$wpdb->prefix . 'dict_homeworks',
			array('id' => $id)
		);

		if($result !== false) {
			ik_enqueue_messages(__('Successfully remove homework.', 'iii-dictionary'), 'success');
		}
		else {
			ik_enqueue_messages(__('An error occurred during this operation.', 'iii-dictionary'), 'error');
		}

		return $result;
	}

	/*
	 * english sheet
	 * insert a sheet to database if $data['id'] = 0, otherwise update
	 *
	 * @param array $data 		the sheet data
	 *
	 * @return boolean
	 */
	public static function store_sheet($data)
	{
		global $wpdb;
		$has_err = false;

		if($data['grade'] === '') {
			$has_err = true;
			ik_enqueue_messages(__('Please select Grade', 'iii-dictionary'), 'error');
		}
		if($data['sheet-name'] === '') {
			$has_err = true;
			ik_enqueue_messages(__('Sheet name cannot be blank', 'iii-dictionary'), 'error');
		}
		if($data['dictionary'] === '' && $data['assignment-id'] != ASSIGNMENT_REPORT) {
			$has_err = true;
			ik_enqueue_messages(__('Please select a Dictionary', 'iii-dictionary'), 'error');
		}
		if($data['homework-types'] === '' && empty($data['id'])) {
			$has_err = true;
			ik_enqueue_messages(__('Please select Homework Type', 'iii-dictionary'), 'error');
		}

		switch($data['assignment-id'])
		{
			case ASSIGNMENT_SPELLING:
				if(!$data['wordchecked']) {
					$has_err = true;
					ik_enqueue_messages(__('Please validate and correct the words before submiting', 'iii-dictionary'), 'error');
				}
				break;
			case ASSIGNMENT_VOCAB_GRAMMAR:
			case ASSIGNMENT_READING:
				if(count($data['questions']) != 7) {
					$has_err = true;
					ik_enqueue_messages(__('Incorrect sheet format', 'iii-dictionary'), 'error');
				}
				break;
		}

		if($data['assignment-id'] != ASSIGNMENT_REPORT) {
			// escape data
			foreach((array)$data['questions'] as $key => $item)
			{
				if( !is_array($item) ) {
					$data['questions'][$key] = trim(esc_html($item));
				}
				else {
					foreach($item as $key2 => $i) {
						$i = trim($i);
						if($key == 'question' && $i == '') {
							unset($data['questions']['question'][$key2]);
							unset($data['questions']['c_answer'][$key2]);
							unset($data['questions']['w_answer1'][$key2]);
							unset($data['questions']['w_answer2'][$key2]);
							unset($data['questions']['quiz'][$key2]);
						}
						else if(!is_null($data['questions'][$key][$key2])) {
							$data['questions'][$key][$key2] = esc_html($i);
						}
					}
				}
			}

			// encode sheet in json and remove continuous space characters
			$question_json = preg_replace('!\s+!', ' ', json_encode($data['questions']));
		}
		else {
			$question_json = $data['questions'];
		}

		// prepare some default value

		// set default sheet category to English
		if(!isset($data['sheet-categories'])) {
			$data['sheet-categories'] = 1;
		}

		if(empty($data['grading-price'])) {
			$data['grading-price'] = 0;
		}

		// if active is not set, the sheet must be created by teacher, set it to active state
		if(!isset($data['active'])) {
			$data['active'] = 1;
		}

		if(empty($data['next-worksheet-id'])) {
			$data['next-worksheet-id'] = 0;
		}

		// finished validating data
		if(!$has_err)
		{
			if($data['id'])
			{
				// we have an id, update it

				$update_data = array( 
					'assignment_id' => $data['assignment-id'],
					'category_id' => $data['sheet-categories'],
					'trivia_exclusive' => $data['trivia-exclusive'],
					'ws_default' => $data['ws_default'],
					'grade_id' => $data['grade'],
					'sheet_name' => $data['sheet-name'],
					'grading_price' => $data['grading-price'],
					'dictionary_id' => $data['dictionary'],
					'questions' => $question_json,
					'passages' => $data['reading_passage'],
					'description' => $data['description'],
					'lang' => $data['lang']
				);

				if(!empty($data['homework-types'])) {
					$update_data['homework_type_id'] = $data['homework-types'];
				}

				$result = $wpdb->update( 
					$wpdb->prefix . 'dict_sheets', 
					$update_data,
					array('id' => $data['id'])
				);
			}
			else
			{
				// insert new
				$result = $wpdb->insert(
					$wpdb->prefix . 'dict_sheets', 
					array( 
						'assignment_id' => $data['assignment-id'],
						'homework_type_id' => $data['homework-types'],
						'category_id' => $data['sheet-categories'],
						'trivia_exclusive' => $data['trivia-exclusive'],
						'ws_default' => $data['ws_default'],
						'grade_id' => $data['grade'],
						'sheet_name' => $data['sheet-name'],
						'grading_price' => $data['grading-price'],
						'dictionary_id' => $data['dictionary'],
						'questions' => $question_json,
						'passages' => $data['reading_passage'],
						'description' => $data['description'],
						'created_by' => get_current_user_id(),
						'active' => $data['active'],
						'created_on' => date('Y-m-d', time()),
						'lang' => $data['lang']
					)
				);
			}

			if($result !== false)
			{
				ik_enqueue_messages(__('Successfully save homework', 'iii-dictionary'), 'success');
				return true;
			}

			// error occur, return false
			ik_enqueue_messages(__('An error occur, cannot save homework', 'iii-dictionary'), 'error');
			return false;
		}

		return false;
	}
        
	

	/*
	 * english sheet
	 * toggle active value of a sheet
	 *
	 * @param mixed $ids 			the sheet id
	 *
	 * @return boolean
	 */
	public static function toggle_active_sheets($cid)
	{
		global $wpdb;

		$cid = is_array($cid) ? $cid : array($cid);

		foreach($cid as $id)
		{
			$result = $wpdb->query($wpdb->prepare('UPDATE ' . $wpdb->prefix . 'dict_sheets SET active = IF(active = 1, 0, 1) WHERE id = %d', $id));
			if($result === false) {
				return false;
			}
		}

		return true;
	}

	/*
	 * english sheet
	 * delete sheets based on $cid
	 *
	 * @param mixed $cid 			the sheet id
	 * @param bool $delete_own		flag to allow user delete his own worksheet only
	 *
	 * @return boolean
	 */
	public static function delete_sheets($cid, $delete_own = false)
	{
		global $wpdb;

		$cid = is_array($cid) ? $cid : array($cid);

		foreach($cid as $key => $id) {
			$cid[$key] = esc_sql($id);
		}

		$query = 'DELETE FROM ' . $wpdb->prefix . 'dict_sheets WHERE id IN (' . implode(',', $cid) . ')';
		
		if($delete_own) {
			$query .= ' AND created_by = ' . get_current_user_id();
		}

		if($wpdb->query($query))
		{
			ik_enqueue_messages(__('Successfully delete ' . count($cid) . ' sheets', 'iii-dictionary'), 'success');
			return true;
		}

		ik_enqueue_messages(__('An error occur or you don\'t have permission to delete this sheet.', 'iii-dictionary'), 'error');

		return false;
	}

	/*
	 * get list of math worksheet
	 */
	public static function get_math_sheets($filter, $offset = 0, $items_per_page = 99999999)
	{
		global $wpdb;
		$current_user_id = get_current_user_id();

		$query = 'SELECT ms.*, gr.name AS sublevel_name, level_name,ht.name as type, level_category_name
				  FROM ' . $wpdb->prefix . 'dict_sheets AS ms
				  JOIN ' . $wpdb->prefix . 'dict_homework_assignments AS ha ON ha.id = ms.assignment_id
				  JOIN ' . $wpdb->prefix . 'dict_homework_assignment_langs AS hal ON hal.assignment_id = ha.id AND hal.lang = \'' . get_short_lang_code() . '\'
				  JOIN ' . $wpdb->prefix . 'dict_homework_types AS ht ON ht.id = ms.homework_type_id
				  JOIN ' . $wpdb->prefix . 'dict_grades AS gr ON gr.id = ms.grade_id
				  JOIN (
					SELECT id, name AS level_name, parent_id AS level_parent_id 
					FROM ' . $wpdb->prefix . 'dict_grades WHERE level = 1
				  ) AS lgr ON lgr.id = gr.parent_id
				  JOIN (
					SELECT id, name AS level_category_name 
					FROM ' . $wpdb->prefix . 'dict_grades WHERE level = 0
				  ) AS cgr ON cgr.id = lgr.level_parent_id';

		$where[] = 'category_id = 5'; // select Math category

		if($filter['sheet-name'] != '') {
			$where[] = 'sheet_name LIKE \'%' . esc_sql($filter['sheet-name']) . '%\'';
		}

		if($filter['assignment-id'] != '') {
			$where[] = 'ms.assignment_id = ' . esc_sql($filter['assignment-id']);
		}

		if($filter['homework-types'] != '') {
			$where[] = 'homework_type_id = ' . esc_sql($filter['homework-types']);

			if($filter['homework-types'] == HOMEWORK_MY_OWN || $filter['homework-types'] == HOMEWORK_LICENSED) {
				$where[] = 't.created_by = ' . $current_user_id;
			}
		}
		else {
			$excluded_types[] = HOMEWORK_MY_OWN;
			$excluded_types[] = HOMEWORK_LICENSED;

			/* if(!is_math_homework_tools_subscribed()) {
				$excluded_types[] = HOMEWORK_SUBSCRIBED;
			} */

			if(!is_mw_admin() && !is_mw_super_admin()) {
				$excluded_types[] = HOMEWORK_CLASS;
			}

			if(!empty($excluded_types)) {
				$where[] = '(homework_type_id NOT IN (' . implode(',', $excluded_types) . ')
							 OR ((homework_type_id = ' . HOMEWORK_MY_OWN . ' OR homework_type_id = ' . HOMEWORK_LICENSED . ') AND created_by = ' . $current_user_id . '))';
			}
		}

		if($filter['cat-level'] != '') {
			$where[] = 'cgr.id = ' . esc_sql($filter['cat-level']);
		}

		if($filter['level'] != '') {
			$where[] = 'lgr.id = ' . esc_sql($filter['level']);
		}

		if($filter['sublevel'] != '') {
			$where[] = 'gr.id = ' . esc_sql($filter['sublevel']);
		}

		if($filter['lang'] != '') {
			$where[] = 'ms.lang = \'' . esc_sql($filter['lang']) . '\'';
		}
		else {
			if(!is_mw_admin() && !is_mw_super_admin()) {
				$where[] = 'ms.lang IN (\'en\', \'' . get_short_lang_code() . '\')';
			}
		}
                if($active_only) {
			$where[] = 'active = 1';
		}
		else {
			if($filter['active'] != '') {
				$where[] = 'active = ' . $filter['active'];
			}
		}

		if(!empty($where)) {
			$query .= ' WHERE ' . implode(' AND ', $where);
		}

		$order_column_list = array('active','level_name', 'sublevel_name', 'sheet_name', 'ordering');

		$query .= MWDB::filter_order_by($order_column_list, $filter['orderby'], $filter['order-dir']);

		$query .= ' LIMIT ' . $offset . ',' . $items_per_page;

		$sheets = $wpdb->get_results($query);

		$obj = new stdCLass;
		$obj->items = $sheets;
		$obj->total = 0;
                //var_dump($query);die;
		return $obj;
	}

	/*
	 * math sheet
	 * insert or update math sheet
	 *
	 * @param array $data
	 *
	 * @return boolean
	 */
	public static function store_math_sheet($data)
	{
		global $wpdb;

		$valid = true;

		if(empty($data['homework_type_id']) && empty($data['id'])) {
			ik_enqueue_messages(__('Please select Homework Type', 'iii-dictionary'), 'error');
			$valid = false;
		}

		if(empty($data['grade_id'])) {
			ik_enqueue_messages(__('Please select Level', 'iii-dictionary'), 'error');
			$valid = false;
		}

		if(empty($data['sheet_name'])) {
			ik_enqueue_messages(__('Sheet name cannot be blank', 'iii-dictionary'), 'error');
			$valid = false;
		}
		
		
		if($valid)
		{
			$data['questions'] = json_encode($data['questions']);
			if(empty($data['id'])) {
				$result = $wpdb->insert(
					$wpdb->prefix . 'dict_sheets', $data
				);
			}
			else {
				$id = $data['id'];
				unset($data['id']);

				$result = $wpdb->update(
					$wpdb->prefix . 'dict_sheets', $data, array('id' => $id)
				);
			}

			if($result !== false) {
				ik_enqueue_messages(__('Successfully store sheet', 'iii-dictionary'), 'success');
				return $wpdb->insert_id ? $wpdb->insert_id : $id;
			}
			else {
				ik_enqueue_messages(__('An error occurred', 'iii-dictionary'), 'error');
			}
		}

		return false;
	}

	/*
	 * get math sheet
	 *
	 * @param int $sheet_id
	 *
	 * @return object
	 */
	public static function get_math_sheet_by_id($sheet_id)
	{
		global $wpdb;

		$homework = $wpdb->get_row(
			'SELECT ms.*, gr.name AS sublevel_name, lgr.id AS level_id, level_name, cgr.id AS category_level_id, level_category_name
			FROM ' . $wpdb->prefix . 'dict_sheets AS ms
			JOIN ' . $wpdb->prefix . 'dict_grades AS gr ON gr.id = ms.grade_id
			JOIN (
				SELECT id, name AS level_name, parent_id AS level_parent_id 
				FROM ' . $wpdb->prefix . 'dict_grades WHERE level = 1
			) AS lgr ON lgr.id = gr.parent_id
			JOIN (
				SELECT id, name AS level_category_name 
				FROM ' . $wpdb->prefix . 'dict_grades WHERE level = 0
			) AS cgr ON cgr.id = lgr.level_parent_id
			WHERE ms.id = ' . $sheet_id
		);

		return $homework;
	}

	/*
	 * move math worksheet order up by one
	 *
	 * @param int $id	the sheet id
	 */
	public static function set_math_sheet_order_up($id)
	{
		global $wpdb;

		$sheet = MWDB::get_math_sheet_by_id($id);
		if($sheet->ordering > 1) {
			// move the higher sheet down by one
			$wpdb->query(
				'UPDATE ' . $wpdb->prefix . 'dict_sheets 
				SET ordering = ordering + 1 WHERE grade_id = ' . $sheet->grade_id . ' AND ordering = ' . ($sheet->ordering - 1)
			);

			// move the sheet up by one
			$wpdb->query(
				'UPDATE ' . $wpdb->prefix . 'dict_sheets 
				SET ordering = ordering - 1 WHERE id = ' . $id
			);
		}
	}

	/*
	 * move math worksheet order down by one
	 *
	 * @param int $id	the sheet id
	 */
	public static function set_math_sheet_order_down($id)
	{
		global $wpdb;

		$sheet = MWDB::get_math_sheet_by_id($id);

		// move the lower sheet up by one
		$wpdb->query(
			'UPDATE ' . $wpdb->prefix . 'dict_sheets
			SET ordering = ordering - 1 WHERE grade_id = ' . $sheet->grade_id . ' AND ordering = ' . ($sheet->ordering + 1)
		);

		// move the sheet down by one
		$wpdb->query(
			'UPDATE ' . $wpdb->prefix . 'dict_sheets 
			SET ordering = ordering + 1 WHERE id = ' . $id
		);
	}

	/*
	 * math sheet
	 * toggle active value of a sheet
	 *
	 * @param mixed $ids 			the sheet id
	 *
	 * @return boolean
	 */
	public static function toggle_active_math_sheets($cid)
	{
		global $wpdb;

		$cid = is_array($cid) ? $cid : array($cid);

		foreach($cid as $id)
		{
			$result = $wpdb->query($wpdb->prepare(
				'UPDATE ' . $wpdb->prefix . 'dict_sheets SET active = IF(active = 1, 0, 1) WHERE id = %d', $id
			));

			if($result === false) {
				return false;
			}
		}

		return true;
	}

	/*
	 * math sheet
	 * delete sheets based on $cid
	 *
	 * @param mixed $cid 			the sheet id
	 * @param bool $delete_own		flag to allow user delete his own worksheet only
	 *
	 * @return boolean
	 */
	public static function delete_math_sheets($cid, $delete_own = false)
	{
		global $wpdb;

		$cid = is_array($cid) ? $cid : array($cid);

		foreach($cid as $key => $id) {
			$cid[$key] = esc_sql($id);
		}

		$query = 'DELETE FROM ' . $wpdb->prefix . 'dict_sheets WHERE id IN (' . implode(',', $cid) . ')';
		
		if($delete_own) {
			$query .= ' AND created_by = ' . get_current_user_id();
		}

		if($wpdb->query($query))
		{
			ik_enqueue_messages(__('Successfully delete ' . count($cid) . ' sheets', 'iii-dictionary'), 'success');
			return true;
		}

		ik_enqueue_messages(__('An error occur or you don\'t have permission to delete this sheet.', 'iii-dictionary'), 'error');

		return false;
	}

	/*
	 * return group list of the current user, including subscription status of the group
	 *
	 * @return array
	 */
	public static function get_current_user_groups()
	{
		global $wpdb;

		$group = $wpdb->get_results('SELECT us.sat_class_id,g.id, g.name, g.size, MAX(us.expired_on) AS expired_date
									 FROM ' . $wpdb->prefix . 'dict_groups AS g
									 INNER JOIN ' . $wpdb->prefix . 'dict_user_subscription AS us ON us.group_id = g.id
									 WHERE g.active = 1 AND g.created_by = ' . get_current_user_id() . '
									 GROUP BY g.id');
		return $group;
	}
        
        
        /*
	 * return group list of the current user subscription
	 *
	 * @return array
	 */
	public static function get_current_user_groups_subscription()
	{
		global $wpdb;

		$group = $wpdb->get_results('select gt.group_id,gt.student_id,g.name,us.sat_class_id,us.expired_on from ' . $wpdb->prefix . 'dict_group_students as gt 
                                                                inner join ' . $wpdb->prefix . 'dict_groups as g on gt.group_id=g.id
                                                                inner join ' . $wpdb->prefix . 'dict_user_subscription as us on us.activated_by=gt.student_id
                                                                where gt.student_id=' . get_current_user_id() . ' and  curdate() < us.expired_on group by (gt.group_id)');
		return $group;
	}
        
        
        
          /*
	 * return group list of the current user create
	 *
	 * @return array
	 */
	public static function get_current_user_groups_create()
	{
		global $wpdb;

		$group = $wpdb->get_results('select name from ' . $wpdb->prefix . 'dict_groups where created_by=' . get_current_user_id());
		return $group;
	}
        
        
        /*
	 * return group list of the joined user
	 *
	 * @return array
	 */
        
        public static function get_join_user_groups()
	{
		global $wpdb;

		$group = $wpdb->get_results('SELECT *
									 FROM ' . $wpdb->prefix . 'dict_group_students AS g
									 INNER JOIN ' . $wpdb->prefix . 'dict_groups AS us ON us.id = g.group_id
									 WHERE us.active = 1 AND g.absented=0 AND g.student_id = ' . get_current_user_id() . '
									 GROUP BY g.id');
		return $group;
	}
        
        /*
	 * return all homeworks from a group. This function also return homework results of a user based on filter values
	 *
	 * @param int $group_id 		Group id
	 * @param array $filter
	 *
	 * @return object
	 */
	public static function get_group_homeworks($group_id, $filter = array(), $offset = 0, $items_per_page = 99999999)
	{
		global $wpdb;

		$query = 'SELECT COUNT(*)
				   FROM ' . $wpdb->prefix . 'dict_homeworks AS h
				   JOIN ' . $wpdb->prefix . 'dict_sheets AS s ON s.id = h.sheet_id
				   JOIN ' . $wpdb->prefix . 'dict_grades AS sgr ON sgr.id = s.grade_id
				   JOIN ' . $wpdb->prefix . 'dict_homework_assignments AS ha ON ha.id = s.assignment_id
				   JOIN ' . $wpdb->prefix . 'dict_homework_assignment_langs AS hal ON hal.assignment_id = ha.id AND hal.lang = \'' . get_short_lang_code() . '\'';

		$columns = 'h.id AS hid, h.name AS homework_name, deadline, h.for_practice, h.is_retryable, h.created_on, h.sheet_id, s.assignment_id, homework_type_id, sheet_name, sgr.name AS grade, grading_price, hal.name AS assignment_type, description';

		if($filter['homework_result']) {
			$columns .= ', hr.*, hr.id AS homework_result_id, requested_on, accepted_on, finished_on, dpr.id as practice_id';

			$query .= ' LEFT JOIN ' . $wpdb->prefix . 'dict_homework_results AS hr ON hr.homework_id = h.id AND userid = ' . $filter['user_id'] . '
						LEFT JOIN ' . $wpdb->prefix . 'dict_worksheet_grading_requests AS gr ON gr.homework_result_id = hr.id
						LEFT JOIN ' . $wpdb->prefix .'dict_practice_results AS dpr ON  dpr.practice_id = h.id AND user_id = ' . $filter['user_id'];
			
		}
		
		$query .= ' WHERE h.group_id = ' . $group_id;
		
		if(!empty($filter['is_active'])) {
			$query .= ' AND h.active = ' . $filter['is_active'];
		}
		$total = $wpdb->get_col($query);

		$query = str_replace('COUNT(*)', $columns, $query);
		$query .= ' LIMIT ' . $offset . ',' . $items_per_page;

		$homeworks = $wpdb->get_results($query);

		$obj = new stdCLass;
		$obj->total = $total[0];
		$obj->items = $homeworks;
		
		return $obj;		
	}

	/*
	 * get list of homeworks waiting for grading.
	 * Filter by group owner and group
	 *
	 * @param int $teacher_id		group owner's id
	 * @param int $group_id
	 *
	 * @return array
	 */
	public static function get_waiting_grading_homeworks($teacher_id = 0, $group_id = 0)
	{
		global $wpdb;

		$query = 'SELECT g.name AS group_name, u.display_name AS user_name, attempted_on, submitted_on, message, sheet_name, h.id AS hid, hr.userid, hr.id AS homework_result_id, hr.report_file
					FROM ' . $wpdb->prefix . 'dict_groups AS g
					JOIN ' . $wpdb->prefix . 'dict_homeworks AS h ON h.group_id = g.id
					JOIN ' . $wpdb->prefix . 'dict_sheets AS s ON s.id = h.sheet_id
					LEFT JOIN ' . $wpdb->prefix . 'dict_homework_results AS hr ON hr.homework_id = h.id
					LEFT JOIN ' . $wpdb->users . ' AS u ON u.ID = hr.userid';

		$where[] = 'hr.graded = 0';

		if($teacher_id) {
			$where[] = 'g.created_by = ' . esc_sql($teacher_id);
		}

		if($group_id) {
			$where[] = 'g.id = ' . esc_sql($group_id);
		}

		if(!empty($where)) {
			$query .= ' WHERE ' . implode(' AND ', $where);
		}

		$homeworks = $wpdb->get_results($query);

		return $homeworks;
	}

	/*
	 * get list of users who joined the group
	 *
	 * @param int $group_id
	 *
	 * @return array
	 */
	public static function get_group_students($group_id)
	{
		global $wpdb;

		$students = $wpdb->get_results('SELECT u.*, gs.joined_date, COUNT(hr.id) as homeworks_done
										FROM ' . $wpdb->users . ' AS u
										JOIN ' . $wpdb->prefix . 'dict_group_students AS gs ON gs.student_id = u.ID
										LEFT JOIN ' . $wpdb->prefix . 'dict_homeworks AS h ON h.group_id = gs.group_id
										LEFT JOIN ' . $wpdb->prefix . 'dict_homework_results AS hr ON hr.homework_id = h.id AND hr.userid = u.ID
										WHERE gs.group_id = ' . $group_id . '
										GROUP BY u.ID');

		return $students;
	}

	public static function get_percentage_paid_to_teacher()
	{
		global $wpdb;

		$group_name = $wpdb->get_results("SELECT option_value FROM " . $wpdb->prefix . "dict_options WHERE option_name = 'percentage-paid-to-teacher'");

		return $group_name;
	}

	/*
	 * return teacher's group list, data consist of:
	 * group name, password, number of students, number of homeworks, subscription status
	 *
	 * @param int $user_id
	 * @param array $filter
	 *
	 * @return array
	 */
	public static function get_teacher_groups($user_id = 0, $filter)
	{
		global $wpdb;

		$user_id = $user_id ? $user_id : get_current_user_id();

		$total = $wpdb->get_results('SELECT g.id
									  FROM ' . $wpdb->prefix . 'dict_groups AS g
									  LEFT JOIN ' . $wpdb->prefix . 'dict_group_students AS gs ON gs.group_id = g.id
									  LEFT JOIN ' . $wpdb->prefix . 'dict_homeworks AS h ON h.group_id = g.id
									  LEFT JOIN ' . $wpdb->prefix . 'dict_user_subscription AS us ON us.group_id = g.id
									  WHERE g.active = 1 AND g.created_by = ' . $user_id . ' GROUP BY g.id');

		$group_list = $wpdb->get_results('SELECT g.id, g.name, g.password, COUNT(DISTINCT gs.id) AS student_num, COUNT(DISTINCT h.id) AS hk_num, MAX(us.expired_on) AS expired_date
									  FROM ' . $wpdb->prefix . 'dict_groups AS g
									  LEFT JOIN ' . $wpdb->prefix . 'dict_group_students AS gs ON gs.group_id = g.id
									  LEFT JOIN ' . $wpdb->prefix . 'dict_homeworks AS h ON h.group_id = g.id
									  LEFT JOIN ' . $wpdb->prefix . 'dict_user_subscription AS us ON us.group_id = g.id
									  WHERE g.active = 1 AND g.created_by = ' . $user_id . ' GROUP BY g.id 
									  LIMIT ' . $filter['offset'] . ',' . $filter['items_per_page']);

		$obj = new stdCLass;
		$obj->total = count($total);
		$obj->items = $group_list;

		return $obj;
	}

	/*
	 * return list of homeworks
	 *
	 * @param string $filter	Search filter, accept: user_id, group_id
	 * @param mixed $value
	 *
	 * @return array
	 */
	public static function get_homeworks_by($filter, $value)
	{
		global $wpdb;

		$query = 'SELECT h.*, g.name AS group_name, s.assignment_id, gr.name AS grade, s.sheet_name, hal.name AS assignment
 				  FROM ' . $wpdb-> prefix . 'dict_homeworks AS h
				  JOIN ' . $wpdb->prefix . 'dict_groups AS g ON g.id = h.group_id
				  JOIN ' . $wpdb->prefix . 'dict_sheets AS s ON s.id = h.sheet_id
				  JOIN ' . $wpdb->prefix . 'dict_grades AS gr ON gr.id = s.grade_id
				  JOIN ' . $wpdb->prefix . 'dict_homework_assignments AS ha ON ha.id = s.assignment_id
				  JOIN ' . $wpdb->prefix . 'dict_homework_assignment_langs AS hal ON hal.assignment_id = ha.id AND hal.lang = \'' . get_short_lang_code() . '\'';

		$where = '';
		if($filter == 'user_id') {
			$where = ' WHERE h.created_by = ' . $value;
		}
		else {
			$where = ' WHERE g.id = \'' . esc_sql($value) . '\'';
		}
 
		$homeworks = $wpdb->get_results($query . $where);

		return $homeworks;
	}

	/*
	 * get a homework assignment by id
	 *
	 * @param int $id
	 *
	 * @return object
	 */
	public static function get_homework_assignment_by_id($id)
	{
		global $wpdb;

		$query = 'SELECT h.*, g.id AS gid, g.name AS group_name, s.assignment_id, gr.name AS grade, s.sheet_name, s.grading_price, hal.name AS assignment, h.for_practice
 				  FROM ' . $wpdb-> prefix . 'dict_homeworks AS h
				  JOIN ' . $wpdb->prefix . 'dict_groups AS g ON g.id = h.group_id
				  JOIN ' . $wpdb->prefix . 'dict_sheets AS s ON s.id = h.sheet_id
				  JOIN ' . $wpdb->prefix . 'dict_grades AS gr ON gr.id = s.grade_id
				  JOIN ' . $wpdb->prefix . 'dict_homework_assignments AS ha ON ha.id = s.assignment_id
				  JOIN ' . $wpdb->prefix . 'dict_homework_assignment_langs AS hal ON hal.assignment_id = ha.id AND hal.lang = \'' . get_short_lang_code() . '\'
				  WHERE h.id = ' . $id;
 
		$homework = $wpdb->get_row($query);

		return $homework;
	}

	/*
	 * return list of homework assignments
	 *
	 * @param array $filter
	 *
	 * @return object
	 */
	public static function get_homework_assignments($filter, $offset = 0, $items_per_page = 99999999)
	{
            clearstatcache();
		global $wpdb;

		$query = 'SELECT COUNT(*)
 				  FROM ' . $wpdb-> prefix . 'dict_homeworks AS h
				  JOIN ' . $wpdb->prefix . 'dict_groups AS g ON g.id = h.group_id
				  JOIN ' . $wpdb->prefix . 'dict_sheets AS s ON s.id = h.sheet_id
				  JOIN ' . $wpdb->prefix . 'dict_grades AS gr ON gr.id = s.grade_id
				  JOIN ' . $wpdb->prefix . 'dict_homework_assignments AS ha ON ha.id = s.assignment_id
				  JOIN ' . $wpdb->prefix . 'dict_homework_assignment_langs AS hal ON hal.assignment_id = ha.id AND hal.lang = \'' . get_short_lang_code() . '\'';
                
		if($filter['check_result']) {
			$query .= ' LEFT JOIN ' . $wpdb->prefix . 'dict_homework_results AS hr ON hr.homework_id = h.id';
			$optional_columns[] = 'COUNT(hr.id) AS no_results';
		}

		if(!empty($filter['group_id'])) {
			$where[] = 'h.group_id = ' . esc_sql($filter['group_id']);
		}

		if(!empty($filter['created_by'])) {
			$where[] = 'h.created_by = ' . $filter['created_by'];
		}
		
		if(!empty($filter['sheet_id'])) {
			$where[]  = 'h.sheet_id = ' . $filter['sheet_id'];
		}
		
		if(!empty($where)) {
			$query .= ' WHERE ' . implode(' AND ', $where);
		}

		$total = $wpdb->get_col($query);

		$columns = 'h.*, g.name AS group_name, s.assignment_id, gr.name AS grade, s.sheet_name, hal.name AS assignment,s.ordering AS ordering';

		if(!empty($optional_columns)) {
			$columns .= ',' . implode(',', $optional_columns);
		}

		$query = str_replace('COUNT(*)', $columns, $query);
		$query .= ' GROUP BY h.id';
		$query .= ' LIMIT ' . $offset . ',' . $items_per_page;

		$assignments = $wpdb->get_results($query);
		$obj = new stdCLass;
		$obj->total = $total[0];
		$obj->items = $assignments;
//		echo $query;
		return $obj;
	}

	/* 
	 * return homeworks results of a user
	 *
	 * @param int $homework_id
	 * @param int $user_id
	 *
	 * @return array
	 */
	public static function get_homework_results($homework_id, $user_id = false)
	{
		global $wpdb;

		$query = 'SELECT hr.id AS homework_result_id, h.id AS homework_id, g.id AS group_id, gs.student_id AS userid, display_name, answers, teacher_comments, questions, s.assignment_id, gr.name AS grade, s.sheet_name, h.deadline, g.name AS group_name, passages, s.dictionary_id, d.name AS dictionary, graded, report_file, hr.graded_by,
						  IF(attempted_on IS NULL, "N/A", attempted_on) AS attempted_on,
						  IF(submitted_on IS NULL, "N/A", submitted_on) AS submitted_on,
						  IF(correct_answers_count IS NULL, 0, correct_answers_count) AS correct_answers_count, 
						  IF(score IS NULL, 0, score) AS score, IF(message IS NULL OR message = "", "No message", message) AS message
				 FROM ' . $wpdb->prefix . 'dict_homeworks AS h
				 JOIN ' . $wpdb->prefix . 'dict_sheets AS s ON s.id = h.sheet_id
				 JOIN ' . $wpdb->prefix . 'dict_grades AS gr ON gr.id = s.grade_id
				 JOIN ' . $wpdb->prefix . 'dict_groups AS g ON g.id = h.group_id
				 JOIN ' . $wpdb->prefix . 'dict_group_students AS gs ON gs.group_id = h.group_id
				 LEFT JOIN ' . $wpdb->prefix . 'dict_homework_results AS hr ON hr.userid = gs.student_id AND hr.homework_id = ' . $homework_id . '
				 LEFT JOIN ' . $wpdb->users . ' AS u ON u.ID = gs.student_id
				 LEFT JOIN ' . $wpdb->prefix . 'dict_dictionaries AS d ON d.id = s.dictionary_id
				 WHERE h.id = ' . $homework_id;

		if($user_id) {
			$query .= ' AND hr.userid = ' . $user_id;
		}

		$results = $wpdb->get_results($query);
		
		return $results;
	}

	/* 
	 * get single homeworks result
	 *
	 * @param int $homework_result_id
	 *
	 * @return object
	 */
	public static function get_homework_result($homework_result_id)
	{
		global $wpdb;

		$result = $wpdb->get_row(
			'SELECT hr.*, s.questions, s.sheet_name, display_name
			FROM ' . $wpdb->prefix . 'dict_homework_results AS hr
			JOIN ' . $wpdb->prefix . 'dict_homeworks AS h ON h.id = hr.homework_id
			JOIN ' . $wpdb->prefix . 'dict_sheets AS s ON s.id = h.sheet_id
			JOIN ' . $wpdb->prefix . 'users AS u ON u.ID = hr.userid
			WHERE hr.id = ' . esc_sql($homework_result_id)
		);
                
		return $result;
	}
	/* 
	 * get single homeworks result
	 *
	 * @param int $homework_result_id
	 *
	 * @return object
	 */
	public static function get_homework_result_status($homework_result_id)
	{
		global $wpdb;

		$result = $wpdb->get_row(
			'SELECT hr.*, s.questions, s.sheet_name, display_name
			FROM ' . $wpdb->prefix . 'dict_homework_results AS hr
			JOIN ' . $wpdb->prefix . 'dict_sheets AS s ON s.id = hr.homework_id
			JOIN ' . $wpdb->prefix . 'users AS u ON u.ID = hr.userid
			WHERE hr.id = ' . esc_sql($homework_result_id)
		);
                
		return $result;
	}

	/*
	 * delete homework result
	 *
	 * @param int $id
	 *
	 * @return boolean
	 */
	public static function delete_homework_result($id)
	{
		global $wpdb;

		$result = $wpdb->delete(
			$wpdb->prefix . 'dict_homework_results',
			array('id' => $id)
		);

		return $result;
	}

	/*
	 * automatically grade students homework
	 *
	 * @param array $data
	 *
	 * @return mixed
	 */
	public static function auto_grade_homework($data)
	{
		global $wpdb;

		$result = $wpdb->insert(
			$wpdb->prefix . 'dict_homework_results', $data
		);

		return $result;
	}
	/* manually grade student homework answers when register teacher math
 	 * @param array $data
	 */
	public static function manually_grade_homework(&$data, $id) {
		global $wpdb;

		$result = $wpdb->update(
			$wpdb->prefix . 'dict_homework_results', $data , array('id' => $id)
		);
		
		return $result;
	}
	
	/*
	 * manually grade students homework answers
	 *
	 * @param array $data
	 */
	public static function grade_homework(&$data)
	{
		global $wpdb;

		$result = $wpdb->update(
			$wpdb->prefix . 'dict_homework_results',
			array(
				'answers' => json_encode($data['graded_answers']),
				'teacher_comments' => json_encode($data['comments']),
				'score' => $data['score'],
				'graded' => 1,
				'graded_by' => $data['graded_by']
			),
			array('id' => $data['hrid'])
		);

		if($result !== false) {
			ik_enqueue_messages(__('Successfully graded.', 'iii-dictionary'), 'success');
			return true;
		}

		return false;
	}

	/*
	 * return groups list.
	 *
	 * @param array $filter
	 *
	 * @return array
	 */
	public static function get_groups($filter = array(), $offset = 0, $items_per_page = 99999999)
	{
		global $wpdb;
		$output = new stdCLass;
                $class_types = MWDB::get_group_class_types();

		$query = 'SELECT COUNT(DISTINCT g.id)
				  FROM ' . $wpdb->prefix . 'dict_groups AS g
				  LEFT JOIN ' . $wpdb->prefix . 'dict_group_types AS gt ON gt.id = g.group_type_id
				  LEFT JOIN (
						SELECT group_id, COUNT(student_id) AS no_of_student FROM ' . $wpdb->prefix . 'dict_group_students GROUP BY group_id
				  ) AS gs ON gs.group_id = g.id
				  LEFT JOIN (
						SELECT group_id, COUNT(group_id) AS no_homeworks FROM ' . $wpdb->prefix . 'dict_homeworks GROUP BY group_id
				  ) AS h ON h.group_id = g.id
				  LEFT JOIN ' . $wpdb->users . ' AS u ON u.ID = g.created_by';

		$where = array();

		if($filter['group_type'] == GROUP_CLASS || $filter['fetch_classes']) {
			$query .= ' LEFT JOIN ' . $wpdb->prefix . 'dict_group_details AS gc ON gc.group_id = g.id
						LEFT JOIN ' . $wpdb->prefix . 'dict_group_class_types AS ct ON ct.id = gc.class_type_id';

			$order_by['class_type_id'] = 'class_type_id';
			$order_by['ordering'] = 'ordering';
			
			$_select = ', slug, price';
		}

		if($filter['subscription_status']) {
			$query .= ' LEFT JOIN ' . $wpdb->prefix . 'dict_user_subscription AS us ON us.group_id = g.id';
		}
                
                
                 if($filter['class_type']==count($class_types)+1){
                     
                 }else{
		if(!empty($filter['created_by'])) {
			if($filter['fetch_classes']) {
				$where[] = '(g.created_by = ' . esc_sql($filter['created_by']) . ' OR g.group_type_id = ' . GROUP_CLASS . ')';
			}
			else {
				$where[] = 'g.created_by = ' . esc_sql($filter['created_by']);
			}
                 }}

		if($filter['state'] != '') {
			$where[] = 'g.active = ' . esc_sql($filter['state']);
		}

		if(!empty($filter['group-name'])) {
			$where[] = 'g.name LIKE \'%' . $filter['group-name'] . '%\'';
		}

		if(!empty($filter['owner-name'])) {
			$where[] = 'u.display_name LIKE \'%' . $filter['owner-name'] . '%\'';
		}
		

		if(!empty($filter['group_type'])) {
			if($filter['group_type'] == GROUP_CLASS) {
				if((is_mw_super_admin() || is_mw_admin()) && isset($filter['is_admin_create_group'])) {
					if($filter['class_type'] == '0' && !is_null($filter['class_type'])) {
						$where[] = 'g.group_type_id IN (' . GROUP_FREE .')';
						if(empty($filter['created_by'])) {
							$where[] = 'g.id NOT IN(SELECT group_id FROM ' . $wpdb->prefix . 'dict_user_subscription WHERE group_id <> 0)';
						}
					}else {
						$where[] = 'g.group_type_id IN (' . GROUP_CLASS . ',' . GROUP_FREE .')';
						$optional_columns[] = 'class_type_id';
						$optional_columns[] = 'content';
						$optional_columns[] = 'detail';
						$optional_columns[] = 'ct.name AS class_name';
						$optional_columns[] = 'ordering';
					}
				}else {
					$where[] = 'g.group_type_id = ' . GROUP_CLASS;
					$optional_columns[] = 'class_type_id';
					$optional_columns[] = 'content';
					$optional_columns[] = 'detail';
					$optional_columns[] = 'ct.name AS class_name';
					$optional_columns[] = 'ordering';
				}
			}
			else {
				$where[] = 'g.group_type_id = ' . GROUP_FREE;

				if($filter['group_type'] == GROUP_FREE) {
					if(empty($filter['created_by'])) {
						$where[] = 'g.id NOT IN(SELECT group_id FROM ' . $wpdb->prefix . 'dict_user_subscription WHERE group_id <> 0)';
					}
				}
				// get subscrbed groups
				else {
					$where[] = 'g.id IN(SELECT group_id FROM ' . $wpdb->prefix . 'dict_user_subscription WHERE group_id <> 0)';
				}
			}
		}
                
		if(!empty($filter['class_type'])) {
                        if($filter['class_type']==count($class_types)+1){
                            $where[] = ' g.id not in ( select group_id FROM '. $wpdb->prefix . 'dict_group_details) ';
                        }else{
                            $where[] = 'class_type_id = ' . $filter['class_type'];
                        }
		}
		
		if(!empty($filter['lang'])) {
			$where[] = ' slug = \'' . $filter['lang'] . '\'';
		}
		
		if(!empty($where)) {
			$query .= ' WHERE ' . implode(' AND ', $where);
		}

		

		$total = $wpdb->get_col($query);

		if($filter['subscription_status']) {
			$optional_columns[] = 'expired_on';
		}

		$columns = 'g.id, g.name, g.password, g.created_on,g.about_class, g.created_by AS uid, gt.name AS group_type, display_name, g.active, no_of_student, no_homeworks';
		if(isset($_select) && !empty($_select)) $columns .= $_select;
		if(!empty($optional_columns)) {
			$columns .= ',' . implode(',', $optional_columns);
		}

		$query = str_replace('COUNT(DISTINCT g.id)', $columns, $query);
		$query .= ' GROUP BY g.id';
		
		if(!empty($filter['lang'])) {
			$query .= ' HAVING slug = \'' . $filter['lang'] . '\'';
		}

		if(!empty($filter['orderby'])) {
			$query .= ' ORDER BY ' . esc_sql($filter['orderby']) . ' ' . esc_sql($filter['order-dir']);
		}else{
                        $query .= ' ORDER BY g.name';
                }
		$query .= ' LIMIT ' . $offset . ',' . $items_per_page;

		$groups = $wpdb->get_results($query);

		$output->total = $total[0];
		$output->items = $groups;
	//var_dump($query);die;
		return $output;
	}

	/* 
	 * get single group
	 *
	 * @param mixed $search			Group id or group name
	 * @param string $filter		Search filter. Accept id or name
	 *
	 * @return array
	 */
	public static function get_group($search, $filter = 'name')
	{
		global $wpdb;

		$query = 'SELECT g.*, gc.class_type_id, gc.content, gc.detail, gc.ordering, gc.price, u.ID AS userid, u.display_name, g.special_group
					FROM ' . $wpdb->prefix . 'dict_groups AS g
					LEFT JOIN ' . $wpdb->prefix . 'dict_group_details AS gc ON gc.group_id = g.id
					JOIN ' . $wpdb->users . ' AS u ON u.ID = g.created_by
					WHERE ';

		if($filter == 'name') {
			$where = 'g.name = %s';
		}
		else {
			$where = 'g.id = %d';			
		}

		$group = $wpdb->get_row($wpdb->prepare($query . $where, $search));

		return $group;
	}
	public static function get_group_detail($group_id)
	{
		global $wpdb;

		$query = 'SELECT * FROM ' . $wpdb->prefix . 'dict_group_details WHERE group_id ='.$group_id;

		$group_detail = $wpdb->get_results($query);

		return $group_detail;
	}

	/*
	 * return all groups the user has joined
	 *
	 * @param int $userid 			User id. Default to current user
	 * @return array
	 */
	public static function get_user_joined_groups($userid = 0, $offset = 0, $items_per_page = 99999999, $is_active = false)
	{
		global $wpdb;

		$userid = $userid ? $userid : get_current_user_id();

		$query = 'SELECT gs.group_id
			   FROM ' . $wpdb->prefix . 'dict_group_students AS gs
			   JOIN ' . $wpdb->prefix . 'dict_groups AS g ON g.id = gs.group_id
			   LEFT JOIN ' . $wpdb->prefix . 'dict_group_details AS gc ON gc.group_id = g.id
			   LEFT JOIN ' . $wpdb->prefix . 'dict_homeworks AS h ON h.group_id = g.id
			   LEFT JOIN (SELECT homework_id, finished FROM ' . $wpdb->prefix . 'dict_homework_results WHERE userid = ' . $userid . ') AS hr ON hr.homework_id = h.id
			   JOIN ' . $wpdb->prefix . 'users AS u ON u.ID = g.created_by';
		$query .= ' WHERE g.active = 1 AND student_id = ' . $userid . ' AND gs.absented = 0 AND (gc.class_type_id IS NULL OR gc.class_type_id <> ' . CLASS_OTHERS . ')';
		if($is_active) {
		$query .= ' AND (h.active IS NULL OR h.active = 1)';
		}
		$query .= ' GROUP BY g.id';
		$total = $wpdb->get_results($query);

		$query = str_replace('SELECT gs.group_id','SELECT gs.group_id, g.group_type_id, g.name AS group_name, g.is_default, u.display_name AS teacher, COUNT(h.id) AS no_of_homework, COUNT(IF(hr.finished = 1, 1, NULL)) AS completed_homework', $query);
		$query .= ' LIMIT ' . $offset . ',' . $items_per_page;

		$groups = $wpdb->get_results($query);

		$obj = new stdCLass;
		$obj->total = count($total);
		$obj->items = $groups;
		
		return $obj;
	}

	/*
	 * get member list of a group
	 *
	 * @param int $group_id
	 *
	 * @return array
	 */
	public static function get_group_members($group_id)
	{
		global $wpdb;

		$query = 'SELECT u.*, gs.joined_date
					FROM ' . $wpdb->users . ' AS u
					JOIN ' . $wpdb->prefix . 'dict_group_students AS gs ON gs.student_id = u.ID
					WHERE group_id = %d
					ORDER BY u.display_name';

		$members = $wpdb->get_results($wpdb->prepare($query, $group_id));

		return $members;
	}

	/*
	 * get list of group message board that the user has joined
	 *
	 * @param int $user_id
	 * @param array $filter
	 *
	 * @return array
	 */
	public static function get_user_group_messageboard($user_id, $filter = array())
	{
		global $wpdb;

		$query = 'SELECT g.id, g.name AS group_name, COUNT(posted_by) AS replies, user_login AS poster, posted_on
			FROM ' . $wpdb->prefix . 'dict_groups AS g
			LEFT JOIN ' . $wpdb->prefix . 'dict_group_details AS gc ON gc.group_id = g.id
			JOIN ' .$wpdb->prefix . 'dict_group_students AS gs ON gs.group_id = g.id AND gs.student_id = ' . $user_id . '
			LEFT JOIN (
					SELECT * FROM ' . $wpdb->prefix . 'dict_group_messages ORDER BY posted_on DESC
				) AS gm ON gm.group_id = g.id
			LEFT JOIN ' . $wpdb->users . ' AS u ON u.ID = posted_by
			WHERE (gc.class_type_id IS NULL OR gc.class_type_id <> ' . CLASS_OTHERS . ')
			GROUP BY g.id';

		if(!empty($filter['orderby'])) {
			$query .= ' ORDER BY ' . $filter['orderby'] . ' ' . $filter['order-dir'];
		}

		$boards = $wpdb->get_results($query);

		return $boards;
	}

	/*
	 * get list of messages from a group
	 *
	 * @param int $group_id
	 * @param string search
	 *
	 * @return array
	 */
	public static function get_group_messages($group_id, $search = '')
	{
		global $wpdb;

		if(trim($search) != ''){ 			
 			$where = ' AND (gm.subject LIKE \'%'.$search.'%\' OR gm.message LIKE \'%'.$search.'%\')';
 		}else{
 			$where = '';
 		}

		$query = 'SELECT gm.*, u.user_login AS poster, u.display_name AS student_name
				FROM ' . $wpdb->prefix . 'dict_group_messages AS gm
				JOIN ' . $wpdb->users . ' AS u ON u.ID = gm.posted_by
				WHERE gm.group_id = ' . esc_sql($group_id) . $where. '
				ORDER BY gm.posted_on DESC';

		$messages = $wpdb->get_results($query);

		return $messages;
	}

	/*
	 * insert group message
	 *
	 * @param array $data
	 *
	 * @return mixed
	 */
	public static function insert_group_message($data)
	{
		global $wpdb;

		$result = $wpdb->insert($wpdb->prefix . 'dict_group_messages', $data);

		return $result;
	}

	/*
	 * create/update group
	 *
	 * @param array $data 			The data to store
	 *
	 * @return boolean
	 */
	public static function store_group($data)
	{
		global $wpdb;

		// init form valid status
		$valid = true;

		if(trim($data['change_students_no']) == 0 && trim($data['change_students_yes']) == 0) {
			ik_enqueue_messages(__('<b>Will you charge students to join class?</b> section is Not Completed.'), 'error');
			$valid = false;
		}

		if(trim($data['gname']) == '' || trim($data['gpass']) == '') {
			ik_enqueue_messages(__('Group name and Passwords must not be empty.', 'iii-dictionary'), 'error');
			$valid = false;
		}

		//if(strpos($data['gname'], ' ') !== false) {
		//	ik_enqueue_messages(__('Group name cannot contain spacing!', 'iii-dictionary'), 'error');
		//	$valid = false;
		//}

		// check for duplication in group name
		$query = 'SELECT * FROM ' . $wpdb->prefix . 'dict_groups WHERE name = \'' . esc_sql($data['gname']) . '\'';
		if(!empty($data['id'])) {
			$query .= ' AND id <> ' . esc_sql($data['id']);
		}

		if($wpdb->query($query)) {
			ik_enqueue_messages(__('The name, <em>' . $data['gname'] . '</em>, is already used. Please try it again with a different name.', 'iii-dictionary'), 'error');
			$valid = false;
		}

		// auto set ordering if user doesn't enter value or the value conflic
		if($data['group_type_id'] == GROUP_CLASS) {
			$ordering = $data['ordering'];
			// check for ordering conflic
			$query = 'SELECT id FROM ' . $wpdb->prefix . 'dict_group_details 
				WHERE class_type_id = ' . esc_sql($data['class_type_id']) . ' AND ordering = \'' . esc_sql($ordering) . '\'';

			// exclude currently updating group
			if(!empty($data['id'])) {
				$query .= ' AND group_id <> ' . esc_sql($data['id']);
			}

			$conflic = $wpdb->get_row($query);
			
			if($ordering == '' || (!empty($conflic) && $ordering != 0)) {
				$max = $wpdb->get_col('SELECT MAX(ordering) FROM ' . $wpdb->prefix . 'dict_group_details WHERE class_type_id = ' . esc_sql($data['class_type_id']));
				$ordering = is_null($max[0]) ? 1 : $max[0] + 1;
			}

		}
		
		if(empty($data['is_default'])) {
			$data['is_default'] = 0;
		}

		// default group type to free
		if(empty($data['group_type_id'])) {
			$data['group_type_id'] = GROUP_FREE;
		}
		if($valid)
		{
			
			// insert new group
			if(empty($data['id']))
			{
				$result = $wpdb->insert(
					$wpdb->prefix . 'dict_groups', 
					array(
						'group_type_id' => $data['group_type_id'],
						'name' => $data['gname'], 
						'about_class' => $data['about_class'],
						'password' => $data['gpass'],
						'created_by' => get_current_user_id(),
						'created_on' => date('Y-m-d', time()),
						'active' => 1,
						'is_default' => $data['is_default'],
						'special_group' => $data['special_group']
					)
				);

				if($result !== false) {
					ik_enqueue_messages(__('Successfully create Class: <em>' . $data['gname'] . '</em>', 'iii-dictionary'), 'success');
					$new_group_id = $wpdb->insert_id;
					// if($data['group_type_id'] == GROUP_CLASS) {
						$result = $wpdb->insert(
							$wpdb->prefix . 'dict_group_details', 
							array(
								'group_id' => $new_group_id,
								'class_type_id' => $data['class_type_id'],
								'content' => $data['content'],
								'detail' => $data['detail'],
								'ordering' => $ordering,
								'price'    => $data['price']
							)
						);
						$result = $wpdb->insert(
							$wpdb->prefix . 'dict_user_subscription',
							array(
								'activation_code_id' => $data['activation_code_id'],
								'activated_by' 		 => $data['user_id'],
								'activated_on' 		 => $data['starting_date'],
								'expired_on' 		 => $data['expired_date'],
								'typeid' 			 => $data['code_typeid'],
								'group_id' 			 => $new_group_id,
								'sat_class_id' 		 => $data['sat_class_id'],
								'number_of_students' => $data['number_of_students'],
								'number_of_months' 	 => $data['number_of_months'],
								'dictionary_id' 	 => $data['dictionary_id'],
								'salt' 				 => $data['salt']
							)
						);
					// }

					return $new_group_id;
				}
				else {
					ik_enqueue_messages(__('Can not create Group', 'iii-dictionary'), 'error');
				}
			
			}
			// update existing group
			else
			{
				$result = $wpdb->update(
					$wpdb->prefix . 'dict_groups',
					array(
						'group_type_id' => $data['group_type_id'],
						'name' => $data['gname'],
						'password' => $data['gpass'],
						'special_group' => $data['special_group']
					),
					array('id' => $data['id'])
				);

				if($result !== false) {
					ik_enqueue_messages(__('Successfully update Class', 'iii-dictionary'), 'success');

					if($data['group_type_id'] == GROUP_CLASS) {
						$result = $wpdb->update(
							$wpdb->prefix . 'dict_group_details',
							array(
								'class_type_id' => $data['class_type_id'],
								'content' => $data['content'],
								'detail' => $data['detail'],
								'ordering' => $ordering,
								'price'    => $data['price']
							),
							array('group_id' => $data['id'])
						);
					}

					return true;
				}
				else {
					ik_enqueue_messages(__('Can not update Group', 'iii-dictionary'), 'error');
				}
			}
		}

		return false;
	}
        
        
         public static function store_sheet_page($data)
	{
		global $wpdb;
		$valid = true;
		if($valid) {
			if($data['id'])
			{       
				$result = $wpdb->update(
					$wpdb->prefix . 'dict_sheets',
					$data,
					array('id' => $data['id'])
				);
				if($result !== false) {
					return $data['id'];
				}
			}
		}

		return false;
	}


	/*
	 * join a group
	 *
	 * @param string $gname_or_id		The group name or id
	 * @param string $gpass				The group password
	 * @param int $student_id			THe student id to join. Default to current loggedin user
	 *
	 * @return boolean
	 */
	public static function join_group($gname_or_id, $gpass = '', $student_id = 0)
	{
		global $wpdb;

		$student_id = $student_id ? $student_id : get_current_user_id();

		if(is_numeric($gname_or_id)) {
			$group = $wpdb->get_row(
				$wpdb->prepare('SELECT id, password, size, active FROM ' . $wpdb->prefix . 'dict_groups WHERE id = %d', $gname_or_id)
			);

			$gpass = $group->password;
		}
		else {
			$group = $wpdb->get_row(
				$wpdb->prepare('SELECT id, password, size, active FROM ' . $wpdb->prefix . 'dict_groups WHERE name = %s', $gname_or_id)
			);
		}

		if(!is_null($group)) {
			if($gpass == $group->password) {
				if($group->active) {
					$exist = $wpdb->get_row($wpdb->prepare('SELECT absented FROM ' . $wpdb->prefix . 'dict_group_students 
															WHERE group_id = %d AND student_id = %d',
															$group->id, $student_id));
					if(is_null($exist)) 
					{
						// we check if the group is full
						$current_joined = $wpdb->get_col('SELECT COUNT(*) FROM ' . $wpdb->prefix . 'dict_group_students WHERE group_id = ' . $group->id);

						if($group->size > $current_joined[0] || $group->size == 0) {
							$result = $wpdb->insert(
								$wpdb->prefix . 'dict_group_students', 
								array( 
									'group_id' => $group->id,
									'student_id' => $student_id,
									'joined_date' => date('Y-m-d', time())
								)
							);

							if($result) {
								$other = array('group_id' => $group->id, 'order' => 1);
								$is_homework = is_first_assign($other['group_id']);
								ik_enqueue_messages(__('Successfully joined group/class.', 'iii-dictionary'), 'success', 'Messages', $other);
								if(!empty($is_homework->items)) {
									ik_enqueue_messages(__('Would you like to start program now?', 'iii-dictionary'), 'other');
									ik_enqueue_messages(__('You may restart the program at any time from Homework Status', 'iii-dictionary'), 'note');
								}
								// updating subscription status
								update_user_subscription();
							}
							else {
								ik_enqueue_messages(__('An error has occur, cannot join group/class.', 'iii-dictionary'), 'error');
							}
						}
						else {
							ik_enqueue_messages(__('Cannot join group. The group/class is full.', 'iii-dictionary'), 'error');
						}
					}
					else
					{						
						if($exist->absented) {
							// the user has joined this group before but left. So we update the absented state
							$result = $wpdb->update(
								$wpdb->prefix . 'dict_group_students',
								array('absented' => 0),
								array( 
									'group_id' => $group->id,
									'student_id' => $student_id
								)
							);

							if($result) {
								ik_enqueue_messages(__('Successfully rejoined group/class.', 'iii-dictionary'), 'success');
								// updating subscription status
								update_user_subscription();
							}
							else {
								ik_enqueue_messages(__('An error has occur, cannot rejoin Group.', 'iii-dictionary'), 'error');
							}
						}
						else {
							ik_enqueue_messages(__('You already joined this group/class.', 'iii-dictionary'), 'error');
						}
					}
				}
				else {
					ik_enqueue_messages(__('This group is no longer available.', 'iii-dictionary'), 'error');
				}
			}
			else {
				ik_enqueue_messages(__('Incorrect group password.', 'iii-dictionary'), 'error');
			}
		}
		else {
			ik_enqueue_messages(__('Group does not exist.', 'iii-dictionary'), 'error');
		}

		return $result;
	}

	/*
	 * leave a group
	 *
	 * @param int $group_id
	 * @param int $student_id
	 *
	 * @return boolean
	 */
	public static function leave_group($group_id, $student_id = 0)
	{
		global $wpdb;
		
		$student_id = $student_id ? $student_id : get_current_user_id();

		$result = $wpdb->update( 
					$wpdb->prefix . 'dict_group_students', 
					array( 'absented' => 1),
					array( 'group_id' => $group_id, 'student_id' => $student_id)
				);

		return $result;
	}

	/*
	 * move group order up by one
	 *
	 * @param int $id	the group id
	 */
	public static function set_group_order_up($id)
	{
		global $wpdb;

		$group = MWDB::get_group($id, 'id');
		if($group->ordering > 1) {
			// move the higher group down by one
			$wpdb->query(
				'UPDATE ' . $wpdb->prefix . 'dict_group_details 
				SET ordering = ordering + 1 WHERE class_type_id = ' . $group->class_type_id . ' AND ordering = ' . ($group->ordering - 1)
			);

			// move the group up by one
			$wpdb->query(
				'UPDATE ' . $wpdb->prefix . 'dict_group_details 
				SET ordering = ordering - 1 WHERE group_id = ' . $id
			);
		}
	}

	/*
	 * move group order down by one
	 *
	 * @param int $id	the group id
	 */
	public static function set_group_order_down($id)
	{
		global $wpdb;

		$group = MWDB::get_group($id, 'id');

		// move the higher group down by one
		$wpdb->query(
			'UPDATE ' . $wpdb->prefix . 'dict_group_details 
			SET ordering = ordering - 1 WHERE class_type_id = ' . $group->class_type_id . ' AND ordering = ' . ($group->ordering + 1)
		);

		// move the group up by one
		$wpdb->query(
			'UPDATE ' . $wpdb->prefix . 'dict_group_details 
			SET ordering = ordering + 1 WHERE group_id = ' . $id
		);
	}

	/*
	 * get all group types
	 *
	 * @return array
	 */
	public static function get_group_types()
	{
		global $wpdb;

		$group_types = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . 'dict_group_types');

		return $group_types;
	}

	/*
	 * get all group class types
	 *
	 * @return array
	 */
	public static function get_group_class_types($others = true, $seprate = 0)
	{
		global $wpdb;

		$query = 'SELECT * FROM ' . $wpdb->prefix . 'dict_group_class_types';

		// ugly hack
		if(!$others) {
			$query .= ' WHERE id <> ' . CLASS_OTHERS;
		}
		if(!$others && !empty($seprate)) {
			switch($seprate) {
				case 1 : 
					$query .= ' AND local = 0';
					break;
				case 2 :
					$query .= ' AND local = 1 AND id NOT BETWEEN 21 AND 30';
					break;
				default :
					$query .= ' AND local = 1';
					break;
			}  
		}

		$class_types = $wpdb->get_results($query);

		return $class_types;
	}

	/*
	 * get group class type based on id
	 *
	 * @param string $field			The field to search
	 * @param mixed $value			The value to search
	 *
	 * @return object
	 */
	public static function get_group_class_type_by($field, $value)
	{
		global $wpdb;

		switch($field) {
			case 'id':
				$class = $wpdb->get_row(
					$wpdb->prepare(
						'SELECT * FROM ' . $wpdb->prefix . 'dict_group_class_types WHERE id = %d', $value
					)
				);
				break;

			case 'slug':
				$class = $wpdb->get_row(
					$wpdb->prepare(
						'SELECT * FROM ' . $wpdb->prefix . 'dict_group_class_types WHERE slug = %s', $value
					)
				);
				break;
		}

		return $class;
	}

	/*
	 * store the credit code to the database 
	 *
	 * @param array $data 			The data to store
	 *
	 * @return boolean
	 */
	public static function store_credit_code(&$data)
	{
		global $wpdb;

		$insert_data = array( 
			'original_code' 			=> $data['original_code'],
			'encoded_code' 				=> $data['encoded_code'],
			'typeid' 					=> $data['typeid'],
			'no_of_students' 			=> $data['no_of_students'],
			'no_of_months_dictionary' 	=> $data['no_of_months_dictionary'],
			'num_points' 				=> $data['num_points'],
			'active' 					=> 1,
			'created_by' 				=> get_current_user_id(),
			'created_on' 				=> date('Y-m-d', time())
		);

		if(!empty($data['no_of_months_teacher_tool'])) {
			$insert_data['no_of_months_teacher_tool'] = $data['no_of_months_teacher_tool'];
		}

		if(!empty($data['dictionary_id'])) {
			$insert_data['dictionary_id'] = $data['dictionary_id'];
		}

		if(!empty($data['no_of_months_sat'])) {
			$insert_data['no_of_months_sat'] = $data['no_of_months_sat'];
		}

		if(!empty($data['sat_class_id'])) {
			$insert_data['sat_class_id'] = $data['sat_class_id'];
		}

		if(!empty($data['auto_generated'])) {
			$insert_data['auto_generated'] = $data['auto_generated'];
		}

		$result = $wpdb->insert(
			$wpdb->prefix . 'dict_credit_codes', 
			$insert_data
		);
		
		if($data['is_math']) {
			$wpdb->query(
				'UPDATE ' . $wpdb->prefix . 'dict_credit_code_serial SET snm = ' . $data['sn']
			);
		}else {
			$wpdb->query(
				'UPDATE ' . $wpdb->prefix . 'dict_credit_code_serial SET sn = ' . $data['sn']
			);
		}
		

		return $result;
	}

	/*
	 * get get all credit codes with filter
	 *
	 * @param array $filter 			search filter
	 *
	 * @return array
	 */
	public static function get_credit_codes($filter)
	{
		global $wpdb;

		$query = 'SELECT COUNT(*) 
				  FROM ' . $wpdb->prefix . 'dict_credit_codes AS c
				  JOIN ' . $wpdb->prefix . 'dict_subscription_type AS ct ON ct.id = c.typeid
				  LEFT JOIN ' . $wpdb->prefix . 'dict_user_subscription AS us ON us.activation_code_id = c.id
				  JOIN ' . $wpdb->users . ' AS u ON u.ID = c.created_by
				  WHERE auto_generated = 0';

		if($filter['search-value'] != '') {
			$query .= ' AND encoded_code LIKE \'%' . esc_sql($filter['search-value']) . '%\'';
		}
		if($filter['type'] != '') {
			$query .= ' AND c.typeid = ' . $filter['type'];
		}

		// 0 - not used
		// 1 - inused
		// 2 - expired
		// 3 - disabled
		if($filter['status'] == '0') {
			$query .= ' AND activated_on IS NULL';
		}
		if($filter['status'] == '1') {
			$query .= ' AND \'' . date('Y-m-d', time()) . '\' < expired_on';
		}
		if($filter['status'] == '2') {
			$query .= ' AND \'' . date('Y-m-d', time()) . '\' > expired_on';
		}
		if($filter['status'] == '3') {
			$query .= ' AND active = 0';
		}

		$total = $wpdb->get_col($query);

		$query = str_replace('COUNT(*)', 'c.*, us.activated_by, us.activated_on, us.expired_on, ct.name AS type, u.display_name', $query);
		$query .= ' GROUP BY c.id LIMIT ' . $filter['offset'] . ',' . $filter['items_per_page'];

		$codes = $wpdb->get_results($query);

		$return = new stdCLass;
		$return->total = $total[0];
		$return->list = $codes;

		return $return;
	}

	/*
	 * get get all credit codes of given user
	 *
	 * @param int $user_id
	 * @param array $filter
	 *
	 * @return array
	 */
	public static function get_user_subscriptions($user_id, $filter)
	{
		global $wpdb;

		$query = 'SELECT COUNT(*)
				  FROM ' . $wpdb->prefix . 'dict_user_subscription AS us
				  LEFT JOIN ' . $wpdb->prefix . 'dict_credit_codes AS c ON c.id = us.activation_code_id
				  LEFT JOIN ' . $wpdb->prefix . 'dict_subscription_type AS ct ON ct.id = us.typeid
				  LEFT JOIN ' . $wpdb->prefix . 'dict_groups AS g ON g.id = us.group_id
				  LEFT JOIN ' . $wpdb->prefix . 'dict_dictionaries AS d ON d.id = us.dictionary_id
				  LEFT JOIN ' . $wpdb->prefix . 'dict_group_class_types AS gct ON gct.id = us.sat_class_id
				  WHERE us.activated_by = ' . $user_id;

		$total = $wpdb->get_col($query);
		$query = str_replace('COUNT(*)', 'us.*, ct.name AS type, gct.name AS sat_class, g.name AS group_name, d.name AS dictionary', $query);
		if(!empty($filter['orderby'])) {
			$query .= ' ORDER BY ' . esc_sql($filter['orderby']) . ' ' . esc_sql($filter['order-dir']);
		}
		$query .= ' LIMIT ' . $filter['offset'] . ',' . $filter['items_per_page'];

		$subscriptions = $wpdb->get_results($query);
		
		$return = new stdCLass;
		$return->total = $total[0];
		$return->items = $subscriptions;     
		return $return;
	}

	/*
	 * get user's subscription detail
	 *
	 * @param int $id	subscription id
	 *
	 * @return array
	 */
	public static function get_user_subscription_details($id)
	{
		global $wpdb;

		$query = 'SELECT us.*, ct.name AS code_type, gct.name AS sat_class, u.display_name, d.name AS dictionary, g.name AS group_name, c.encoded_code
				   FROM ' . $wpdb->prefix . 'dict_user_subscription AS us
				   LEFT JOIN ' . $wpdb->prefix . 'dict_credit_codes AS c ON c.id = us.activation_code_id
				   JOIN ' . $wpdb->prefix . 'dict_subscription_type AS ct ON ct.id = us.typeid
				   LEFT JOIN ' . $wpdb->prefix . 'dict_group_class_types AS gct ON gct.id = us.sat_class_id
				   LEFT JOIN ' . $wpdb->prefix . 'dict_dictionaries AS d ON d.id = us.dictionary_id
				   LEFT JOIN ' . $wpdb->prefix . 'dict_groups AS g ON g.id = us.group_id
				   LEFT JOIN ' . $wpdb->users . ' AS u ON u.ID = us.activated_by
				   WHERE us.id = ' . esc_sql($id);

		$code = $wpdb->get_row($query);
		if(!empty($code)) {
			$no_activation = $wpdb->get_col('SELECT COUNT(activation_code_id) 
											FROM ' . $wpdb->prefix . 'dict_user_subscription 
											WHERE activation_code_id = ' . $code->activation_code_id);

			$code->no_activation = $no_activation[0];
		}

		return $code;
	}

	/*
	 * get dictionary inherit subscription if user joined a teacher tool subscrbed group
	 *
	 * @param int $user_id
	 *
	 * @return array
	 */
	public static function get_user_inherit_subscriptions($user_id = 0)
	{
		global $wpdb;

		$user_id = $user_id ? $user_id : get_current_user_id();

		$subs = $wpdb->get_results(
			'SELECT g.name AS group_name, "Dictionary (inherit)" AS type, "1" AS inherit, d.name AS dictionary
			FROM ' . $wpdb->prefix . 'dict_group_students AS gs
			JOIN ' . $wpdb->prefix . 'dict_user_subscription AS us ON us.group_id = gs.group_id
			JOIN ' . $wpdb->prefix . 'dict_groups AS g ON g.id = gs.group_id
			JOIN ' . $wpdb->prefix . 'dict_dictionaries AS d ON d.id = us.dictionary_id
			WHERE gs.student_id = ' . $user_id
		);

		return $subs;
	}

	/*
	 * get credit code detail
	 *
	 * @param mixed $search 		search value
	 * @param string $filter		search filter
	 *
	 * @return object
	 */
	public static function get_credit_code($search, $filter = 'encoded_code')
	{
		global $wpdb;

		$query = 'SELECT c.*, ct.name AS code_type, u.display_name, d.name AS dictionary, us.id AS user_subscription_id, us.activated_by, us.activated_on, us.expired_on, us.group_id, g.name AS group_name
				   FROM ' . $wpdb->prefix . 'dict_credit_codes AS c
				   JOIN ' . $wpdb->prefix . 'dict_subscription_type AS ct ON ct.id = c.typeid
				   LEFT JOIN ' . $wpdb->prefix . 'dict_user_subscription AS us ON us.activation_code_id = c.id
				   LEFT JOIN ' . $wpdb->prefix . 'dict_dictionaries AS d ON d.id = c.dictionary_id
				   LEFT JOIN ' . $wpdb->prefix . 'dict_groups AS g ON g.id = us.group_id
				   LEFT JOIN ' . $wpdb->users . ' AS u ON u.ID = us.activated_by
				   WHERE ';

		if($filter == 'encoded_code') {
			$where = 'encoded_code = %s';
		}
		else {
			$where = 'us.id = %d';
		}

		$code = $wpdb->get_row($wpdb->prepare($query . $where, $search));

		return $code;
	}

	/*
	 * set a code to inactive state
	 *
	 * @param string $encoded_code 			The encoded code
	 *
	 * @return boolean
	 */
	public static function disable_credit_code($encoded_code)
	{
		global $wpdb;
		
		$result = $wpdb->update(
						$wpdb->prefix . 'dict_credit_codes',
						array('active' => 0),
						array('encoded_code' => $encoded_code)
					);
					
		if($result) {
			ik_enqueue_messages(__('Successfully set inactive', 'iii-dictionary'), 'success');
		}

		return $result;
	}

	/*
	 * add a credit code
	 *
	 * @param array $data 			The POST data
	 *
	 * @return mixed
	 */
	public static function add_credit_code(&$data)
	{
		global $wpdb;

		$has_err = false;
		
		$data['credit-code'] = !empty($data['credit-code']) ? trim($data['credit-code']) : trim($data['activation-code']);
                var_dump($data['credit-code']);die;
		$code = MWDB::get_credit_code($data['credit-code']);
		if(is_null($code)) {
			ik_enqueue_messages(__('Invalid code number.', 'iii-dictionary'), 'error');
			$has_err = true;
		}

		if($data['starting-date'] == '') {
			ik_enqueue_messages(__('Invalid starting date.', 'iii-dictionary'), 'error');
			$has_err = true;
		}

		if($has_err) {
			return false;
		}

		$user = wp_get_current_user();

		// Homework Tool
		if($code->typeid == 1 || $code->typeid == 6)
		{
			$no_of_months = $code->no_of_months_teacher_tool;

			// check to see if user select an existing group or want to create new group
			if($data['assoc-group'] != '' && $data['group-name'] == '')
			{
				// user selected a group
				$group_id = $data['assoc-group'];
			}
			else
			{
				// no group id, check if user enter group name
				// create new group
				if($data['group-name'] != '' && $data['group-pass'] != '') {
					$group_id = MWDB::store_group(array('gname' => $data['group-name'], 'gpass' => $data['group-pass']));

					if(!$group_id) {
						ik_enqueue_messages(__('Cannot create group.', 'iii-dictionary'), 'error');
						$has_err = true;
					}
				}
				else {
					ik_enqueue_messages(__('Invalid group name/password.', 'iii-dictionary'), 'error');
					$has_err = true;
				}
			}
		}
		// Dictionary
		else if($code->typeid == 2 || $code->typeid == 9)
		{
			$group_id = 0;
			$no_of_months = $code->no_of_months_dictionary;

			// check to see if user can still add this code
			$result = $wpdb->get_col('SELECT COUNT(*) 
									  FROM ' . $wpdb->prefix . 'dict_user_subscription
									  WHERE activation_code_id = ' . $code->id);

			if(!empty($result) && $result[0] >= $code->no_of_students) {
				// max number of activation reached
				ik_enqueue_messages(__('Number of license is used up for this activation code.', 'iii-dictionary'), 'error');
				$has_err = true;
			}
		} 
		//Point 
		else if($code->typeid == 4) {
			$cur_points = ik_get_user_points();
			$cur_points += $code->num_points;
			update_user_meta($user->ID, 'user_points', $cur_points);
		}
		// SAT Preparation		
		else{
			$no_of_months = $code->no_of_months_sat;
		}

		// does user has to choose dictionary for this code
		if(!$code->dictionary_id && $code->typeid != 3)
		{
			if($data['dictionary-id'] != '') {
				$row_data['dictionary_id'] = $data['dictionary-id'];
				$dictionary_id = $data['dictionary-id'];
			}
			else {
				ik_enqueue_messages(__('Please choose a Dictionary.', 'iii-dictionary'), 'error');
				$has_err = true;
			}
		}
		else
		{
			$dictionary_id = $code->dictionary_id;
		}

		// finish validating

		// calculate expired date
		$starting_date = date('Y-m-d', strtotime($data['starting-date']));
		$expired_date = date('Y-m-d', strtotime('+' . $no_of_months . ' months', strtotime($starting_date)));

		if(!$has_err)
		{
			if(!empty($row_data['dictionary_id'])) {
				$result = $wpdb->update(
					$wpdb->prefix . 'dict_credit_codes',
					$row_data,
					array('id' => $code->id)
				);
			}

			// store user's subscription
			$sub_data['activation_code_id'] = $code->id;
			$sub_data['user_id'] = $user->ID;
			$sub_data['starting_date'] = $starting_date;
			$sub_data['expired_date'] = $expired_date;
			$sub_data['code_typeid'] = $code->typeid;
			$sub_data['group_id'] = $group_id;
			$sub_data['sat_class_id'] = 0;
			$sub_data['number_of_students'] = $code->no_of_students;
			$sub_data['number_of_months'] = $no_of_months;
			$sub_data['dictionary_id'] = $dictionary_id;
			
			if(!empty($code->sat_class_id)) {
				$sub_data['sat_class_id'] = $code->sat_class_id;
			}

			$subscription_id = MWDB::add_user_subscription($sub_data);

			if($subscription_id) {
				// update subscription status
				update_user_subscription();

				ik_enqueue_messages(__('Successfully add subscription.', 'iii-dictionary'), 'success');

				return $subscription_id;
			}
			else {
				ik_enqueue_messages(__('Cannot add subscription.', 'iii-dictionary'), 'error');
			}
		}

		return false;
	}

	/*
	 * add user subscription
	 *
	 * @param array $data 	
	 *
	 * @return mixed
	 */
	public static function add_user_subscription($data)
	{
		global $wpdb;
		if(in_array('salt', $data) === false) {
			$data['salt'] = '';
		}
//                var_dump($data);die;
		$result = $wpdb->insert(
			$wpdb->prefix . 'dict_user_subscription',
			array(
				'activation_code_id' => $data['activation_code_id'],
				'activated_by' 		 => $data['user_id'],
				'activated_on' 		 => $data['starting_date'],
				'expired_on' 		 => $data['expired_date'],
				'typeid' 			 => $data['code_typeid'],
				'group_id' 			 => $data['group_id'],
				'sat_class_id' 		 => $data['sat_class_id'],
				'number_of_students' => $data['number_of_students'],
				'number_of_months' 	 => $data['number_of_months'],
				'dictionary_id' 	 => $data['dictionary_id'],
				'salt' 				 => $data['salt']
			)
		);

		$subscription_id = $wpdb->insert_id;

		if($subscription_id) {
			if($data['code_typeid'] == 1) {
				// update group size
				$wpdb->update($wpdb->prefix . 'dict_groups', array('size' => $data['number_of_students']), array('id' => $data['group_id']));
			}

			return $subscription_id;
		}

		return false;
	}

	/*
	 * update user subscription
	 *
	 * @param array $data 	
	 *
	 * @return user subscription id or false on error
	 */
	public static function update_user_subscription($data)
	{
		global $wpdb;

		// increase number of students
		if($data['extend_students'])
		{
			$result = $wpdb->query(
				'UPDATE ' . $wpdb->prefix . 'dict_user_subscription 
				SET number_of_students = number_of_students + ' . $data['no_students'] . '
				WHERE id = ' . $data['id']
			);

			if($result) {
				// update group size
				$wpdb->query(
					'UPDATE ' . $wpdb->prefix . 'dict_groups
					SET size = size + ' . $data['no_students'] . '
					WHERE id = ' . $data['group_id']
				);

				return $data['id'];
			}
		}
		// increase number of months
		else
		{
			if($data['typeid'] != SUB_DICTIONARY) {
				$current_sub = $wpdb->get_row($wpdb->prepare(
					'SELECT expired_on FROM ' . $wpdb->prefix . 'dict_user_subscription WHERE id = %d', $data['id']
				));
			}

			switch($data['typeid']) {
				// update Dictionary subscription
				case SUB_DICTIONARY:
					$current_subs = $wpdb->get_results($wpdb->prepare(
						'SELECT id, expired_on FROM ' . $wpdb->prefix . 'dict_user_subscription WHERE activation_code_id = %d', $data['activation_code_id']
					));

					// update the credit code
					$wpdb->query(
						'UPDATE ' . $wpdb->prefix . 'dict_credit_codes
						SET no_of_months_dictionary = no_of_months_dictionary + ' . $data['no_months_dict'] . '
						WHERE id = ' . $data['activation_code_id']
					);

					// update user subscription
					foreach($current_subs as $sub) {
						$update_value = 'expired_on';
						if(strtotime($sub->expired_on) < time()) {
							$update_value = 'CURDATE()';
						}

						$result = $wpdb->query(
							'UPDATE ' . $wpdb->prefix . 'dict_user_subscription
							SET expired_on = DATE_ADD(' . $update_value . ', INTERVAL ' . $data['no_months_dict'] . ' MONTH), number_of_months = number_of_months + ' . $data['no_months_dict'] . '
							WHERE id = ' . $sub->id
						);
					}
					break;
				// update SAT Preparation
				case SUB_SAT_PREPARATION:
				case SUB_MATH_SAT_I_PREP:
				case SUB_MATH_SAT_II_PREP:
					$update_value = 'expired_on';
					if(strtotime($current_sub->expired_on) < time()) {
						$update_value = 'CURDATE()';
					}

					// update user subscription
					$result = $wpdb->query(
						'UPDATE ' . $wpdb->prefix . 'dict_user_subscription
						SET expired_on = DATE_ADD(' . $update_value . ', INTERVAL ' . $data['sat_months'] . ' MONTH), number_of_months = number_of_months + ' . $data['sat_months'] . '
						WHERE id = ' . $data['id']
					);
					break;
				// update Self study subscription
				case SUB_TEACHER_TOOL:
				case SUB_TEACHER_TOOL_MATH:
				case SUB_SELF_STUDY:
				case SUB_SELF_STUDY_MATH:
					$update_value = 'expired_on';
					if(strtotime($current_sub->expired_on) < time()) {
						$update_value = 'CURDATE()';
					}
					// update user subscription
					$result = $wpdb->query(
						'UPDATE ' . $wpdb->prefix . 'dict_user_subscription
						SET expired_on = DATE_ADD(' . $update_value . ', INTERVAL ' . $data['no_months_dict'] . ' MONTH), number_of_months = number_of_months + ' . $data['no_months_dict'] . '
						WHERE id = ' . $data['id']
					);
					break;
			}

			if($result) {
				return $data['id'];
			}
		}

		ik_enqueue_messages(__('Cannot update subscription.', 'iii-dictionary'), 'error');

		return false;
	}

	/*
	 * get list of payment receiving methods
	 *
	 * @return array
	 */
	public static function get_payment_receiving_methods()
	{
		global $wpdb;

		$methods = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . 'dict_payment_receiving_methods');

		return $methods;
	}

	/*
	 * insert payment request
	 *
	 * @param array $data
	 *
	 * @return boolean
	 */
	public static function store_payment_request($data)
	{
		global $wpdb;

		$result = $wpdb->insert(
			$wpdb->prefix . 'dict_payment_requests',
			$data
		);

		return $result;
	}

	/*
	 * get list of payment requests
	 *
	 * @param array $filter
	 * @param int $offset
	 * @param int $items_per_page
	 *
	 * @return object
	 */
	public static function get_payment_requests($filter, $offset = 0, $items_per_page = 99999999)
	{
		global $wpdb;

		$query = 'SELECT COUNT(*)
				FROM ' . $wpdb->prefix . 'dict_payment_requests AS pr
				JOIN ' . $wpdb->prefix . 'dict_payment_receiving_methods AS prm ON prm.id = pr.receiving_method_id
				JOIN ' . $wpdb->prefix . 'dict_payment_request_status AS prs ON prs.id = pr.status_id
				JOIN ' . $wpdb->users . ' AS u ON u.ID = pr.requested_by';

		if(!empty($filter['email'])) {
			$where[] = 'receiving_email LIKE \'%' . esc_sql($filter['email']) . '%\'';
		}

		if(!empty($filter['method'])) {
			$where[] = 'receiving_method_id = ' . $filter['method'];
		}

		if(!empty($filter['status'])) {
			$where[] = 'status_id = ' . $filter['status'];
		}

		if(!empty($where)) {
			$query .= ' WHERE ' . implode(' AND ', $where);
		}

		$total = $wpdb->get_col($query);

		$query = str_replace('COUNT(*)', 'pr.id, display_name AS requester, prm.name AS method, receiving_email, amount, status_id, prs.name AS status, requested_on, processed_on', $query);
		$query .= ' LIMIT ' . $offset . ',' . $items_per_page;

		$requests = $wpdb->get_results($query);

		$obj = new stdCLass;
		$obj->total = $total[0];
		$obj->items = $requests;

		return $obj;
	}

	/*
	 * update a payment request
	 *
	 * @param array $data
	 *
	 * @return boolean
	 */
	public static function update_payment_request($request_id, $data)
	{
		global $wpdb;

		$result = $wpdb->update(
			$wpdb->prefix . 'dict_payment_requests',
			$data,
			array('id' => $request_id)
		);

		if($result !== false) {
			return $request_id;
		}
		else {
			return false;
		}
	}

	/*
	 * get users list
	 *
	 * @param array $filter
	 *
	 * @return object
	 */
	public static function get_users($filter = array(), $offset = 0, $items_per_page = 99999999)
	{
		global $wpdb;

		$query = 'SELECT u.ID AS count
				  FROM ' . $wpdb->users . ' AS u
				  LEFT JOIN (SELECT user_id, meta_value AS date_of_birth FROM ' . $wpdb->usermeta . ' WHERE meta_key = \'date_of_birth\') AS um ON um.user_id = u.ID
				  LEFT JOIN (SELECT user_id, meta_value AS capabilities FROM ' . $wpdb->usermeta . ' WHERE meta_key = \'' . $wpdb->prefix . 'capabilities\') AS uc ON uc.user_id = u.ID';';
				  // LEFT JOIN ' . $wpdb->prefix . 'dict_user_subscription AS us ON us.activated_by = u.ID';

		if(empty($filter['roles'])) {
			$filter['roles'] = array('mw_student');
		}

		foreach($filter['roles'] as $role) {
			$role_cond[] = 'meta_key = \'' . $wpdb->prefix . 'capabilities\' AND meta_value LIKE \'%' . $role . '%\'';
		}

		$where = array(
			'u.ID IN (SELECT user_id FROM ' . $wpdb->usermeta . ' WHERE ' . implode(' OR ', $role_cond) . ')'
		);

		if($filter['state'] != '') {
			$where[] = 'u.ID IN (SELECT user_id FROM ' . $wpdb->usermeta . ' WHERE meta_key = \'ik_disable_user\' AND meta_value = ' . $filter['state'] . ')';
		}

		if(!empty($filter['user-name'])) {
			$where[] = 'u.display_name LIKE \'%' . esc_sql($filter['user-name']) . '%\' OR u.user_login LIKE \'%' . esc_sql($filter['user-name']) . '%\'';
		}

		if(!empty($filter['user-email'])) {
			$where[] = 'u.user_email LIKE \'%' . esc_sql($filter['user-email']) . '%\'';
		}

		if(!empty($filter['user-sub'])) {
			switch($filter['user-sub']) {
				case 'no':
					$where[] = 'u.ID NOT IN (SELECT activated_by FROM ' . $wpdb->prefix . 'dict_user_subscription)';
					break;
				case 'teacher':
					$where[] = 'u.ID IN (SELECT activated_by FROM ' . $wpdb->prefix . 'dict_user_subscription WHERE typeid = ' . SUB_TEACHER_TOOL . ')';
					break;
				case 'dictionary':
					$where[] = 'u.ID IN (SELECT activated_by FROM ' . $wpdb->prefix . 'dict_user_subscription WHERE typeid = ' . SUB_DICTIONARY . ')';
					break;
				case 'sat':
					$where[] = 'u.ID IN (SELECT activated_by FROM ' . $wpdb->prefix . 'dict_user_subscription WHERE typeid = ' . SUB_SAT_PREPARATION . ')';
					break;
			}
		}

		if(!empty($filter['user-type'])) {
			switch($filter['user-type']) {
				case 'user':
					$where[] = 'u.ID NOT IN (SELECT user_id FROM ' . $wpdb->usermeta . ' 
												WHERE meta_key = \'' . $wpdb->prefix . 'capabilities\' 
												AND (meta_value LIKE \'%mw_registered_teacher%\' OR meta_value LIKE \'%mw_qualified_teacher%\'))';
					break;
				case 'r-teacher':
					$where[] = 'u.ID IN (SELECT user_id FROM ' . $wpdb->usermeta . ' WHERE meta_key = \'' . $wpdb->prefix . 'capabilities\' AND meta_value LIKE \'%mw_registered_teacher%\')';
					break;
				case 'q-teacher':
					$where[] = 'u.ID IN (SELECT user_id FROM ' . $wpdb->usermeta . ' WHERE meta_key = \'' . $wpdb->prefix . 'capabilities\' AND meta_value LIKE \'%mw_qualified_teacher%\')';
					break;
				case 'mr-teacher':
					$where[] = 'u.ID IN (SELECT user_id FROM ' . $wpdb->usermeta . ' WHERE meta_key = \'' . $wpdb->prefix . 'capabilities\' AND meta_value LIKE \'%mw_registered_math_teacher%\')';
					break;
				case 'mq-teacher' :
					$where[] = 'u.ID IN (SELECT user_id FROM ' . $wpdb->usermeta . ' WHERE meta_key = \'' . $wpdb->prefix . 'capabilities\' AND meta_value LIKE \'%mw_qualified_math_teacher%\')';
					break;
			}
		}

		if($filter['took-test'] != '') {
			$test_group = mw_get_option('teacher-test-group');
			$sub_sql = 'SELECT userid
						FROM ' . $wpdb->prefix . 'dict_homeworks AS h
						JOIN ' . $wpdb->prefix . 'dict_homework_results AS hr ON hr.homework_id = h.id
						WHERE hr.graded = 0 AND h.group_id = ' . $test_group;
			if($filter['took-test']) {
				$where[] = 'u.ID IN (' . $sub_sql . ')';
			}
			else {
				$where[] = 'u.ID NOT IN (' . $sub_sql . ')';
			}
		}

		$query .= ' WHERE ' . implode(' AND ', $where);
		$query .= ' GROUP BY u.ID';

		$total_result = $wpdb->get_results($query);

		$query  = str_replace('u.ID AS count', 'u.*, date_of_birth, capabilities', $query); // , COUNT(us.id) AS no_of_subscription

		if(!empty($filter['orderby'])) {
			if($filter['orderby'] == 'date_of_birth') {
				$query .= ' ORDER BY STR_TO_DATE(date_of_birth, "%m/%d/%Y") ' . $filter['order-dir'];
			}
			else {
				$query .= ' ORDER BY ' . $filter['orderby'] . ' ' . $filter['order-dir'];
			}
		}

		$query .= ' LIMIT ' . $offset . ',' . $items_per_page;

		$items = $wpdb->get_results($query);

		$obj = new stdCLass;
		$obj->total = count($total_result);
		$obj->items = $items;

		return $obj;
	}

	/*
	 * get single user's subscription
	 *
	 * @return object
	 */
	public static function get_user_subscription($user_id)
	{
		global $wpdb;

		$subscriptions = $wpdb->get_results(
			'SELECT us.*, cct.name AS subscription_name, d.name AS dictionary_name, g.name AS group_name
			 FROM ' . $wpdb->prefix . 'dict_user_subscription AS us
			 JOIN ' . $wpdb->prefix . 'dict_subscription_type AS cct ON cct.id = us.typeid
			 LEFT JOIN ' . $wpdb->prefix . 'dict_dictionaries AS d ON d.id = us.dictionary_id
			 LEFT JOIN ' . $wpdb->prefix . 'dict_groups AS g ON g.id = us.group_id
			 WHERE activated_by = ' . esc_sql($user_id)
		);

		return $subscriptions;
	}

	public static function get_user_subscription_by_group_id($group_id)
	{
		global $wpdb;
                $query='SELECT us.*,count(s.group_id) AS count, cct.name AS subscription_name, d.name AS dictionary_name, g.name AS group_name
			 FROM ' . $wpdb->prefix . 'dict_user_subscription AS us
			 JOIN ' . $wpdb->prefix . 'dict_subscription_type AS cct ON cct.id = us.typeid
			 LEFT JOIN ' . $wpdb->prefix . 'dict_dictionaries AS d ON d.id = us.dictionary_id
			 LEFT JOIN ' . $wpdb->prefix . 'dict_groups AS g ON g.id = us.group_id
                            LEFT JOIN ' . $wpdb->prefix . 'dict_group_students AS s ON g.id = s.group_id
			 WHERE s.group_id = ' . $group_id;
//                var_dump($query);die;
               $subscriptions = $wpdb->get_results($query);
		return $subscriptions;
	}

	/* 
	 * get list of users by roles
	 *
	 * @param array $roles
	 * @param int $offset
	 * @param int $limit
	 *
	 * @return array
	 */
	public static function get_users_with_role( $roles, $offset = 0, $limit = 99999999) 
	{
		global $wpdb;
		if ( ! is_array( $roles ) )
			$roles = array_walk( explode( ",", $roles ), 'trim' );

		$sql = '
			SELECT COUNT(*)
			FROM        ' . $wpdb->users . ' INNER JOIN ' . $wpdb->usermeta . '
			ON          ' . $wpdb->users . '.ID             =       ' . $wpdb->usermeta . '.user_id
			WHERE       ' . $wpdb->usermeta . '.meta_key        =       \'' . $wpdb->prefix . 'capabilities\'
			AND     (
		';
		$i = 1;
		foreach ( $roles as $role ) {
			$sql .= ' ' . $wpdb->usermeta . '.meta_value    LIKE    \'%"' . $role . '"%\' ';
			if ( $i < count( $roles ) ) $sql .= ' OR ';
			$i++;
		}
		$sql .= ' ) ';

		$total = $wpdb->get_col($sql);
		$return['total'] = $total[0];

		$sql = '
			SELECT ' . $wpdb->users . '.*, meta_value
			FROM        ' . $wpdb->users . ' INNER JOIN ' . $wpdb->usermeta . '
			ON          ' . $wpdb->users . '.ID             =       ' . $wpdb->usermeta . '.user_id
			WHERE       ' . $wpdb->usermeta . '.meta_key        =       \'' . $wpdb->prefix . 'capabilities\'
			AND     (
		';
		$i = 1;
		foreach ( $roles as $role ) {
			$sql .= ' ' . $wpdb->usermeta . '.meta_value    LIKE    \'%"' . $role . '"%\' ';
			if ( $i < count( $roles ) ) $sql .= ' OR ';
			$i++;
		}
		$sql .= ' ) ';
		$sql .= ' ORDER BY ID ';
		$sql .= ' LIMIT ' . $offset . ', ' . $limit;

		$return['list'] = $wpdb->get_results( $sql );
		return $return;
	}

	/*
	 * get user purchase history
	 *
	 * @param int $user_id
	 *
	 * @return array
	 */
	public static function get_user_purchase_history($user_id)
	{
		global $wpdb;

		$query = 'SELECT ph.typeid AS sub_type_id, cct.name AS purchased_item_name, ph.amount, ph.purchased_on, pm.name AS payment_method, dcc.encoded_code
					FROM ' .$wpdb->prefix . 'dict_purchase_subscription_history AS ph
					JOIN ' . $wpdb->prefix . 'dict_subscription_type AS cct ON cct.id = ph.typeid
					JOIN ' . $wpdb->prefix . 'dict_purchase_payment_methods AS pm ON pm.id = ph.payment_method_id
					LEFT JOIN '. $wpdb->prefix .'dict_credit_codes AS dcc ON dcc.id = ph.credit_code_id
					WHERE ph.user_id = ' . $user_id . '
					ORDER BY purchased_on DESC';

		$results = $wpdb->get_results($query);

		return $results;
	}

	/*
	 * get total sales
	 */
	public static function get_total_sales()
	{
		global $wpdb;

		$result = array();
		for($i = 1; $i <= 8; $i++)
		{
			switch($i){
				case 1: $index = 'total'; $period = '1'; break;
				case 2: $index = 'this_month'; $period = 'MONTH(purchased_on) = MONTH(CURRENT_DATE) AND YEAR(purchased_on) = YEAR(CURRENT_DATE)'; break;
				case 3: $index = 'last_month'; $period = 'MONTH(purchased_on) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH) AND YEAR(purchased_on) = YEAR(CURRENT_DATE - INTERVAL 1 MONTH)'; break;
				case 4: $index = '2m_ago'; $period = 'MONTH(purchased_on) = MONTH(CURRENT_DATE - INTERVAL 2 MONTH) AND YEAR(purchased_on) = YEAR(CURRENT_DATE - INTERVAL 2 MONTH)'; break;
				case 5: $index = '3m_ago'; $period = 'MONTH(purchased_on) = MONTH(CURRENT_DATE - INTERVAL 3 MONTH) AND YEAR(purchased_on) = YEAR(CURRENT_DATE - INTERVAL 3 MONTH)'; break;
				case 6: $index = '4m_ago'; $period = 'MONTH(purchased_on) = MONTH(CURRENT_DATE - INTERVAL 4 MONTH) AND YEAR(purchased_on) = YEAR(CURRENT_DATE - INTERVAL 4 MONTH)'; break;
				case 7: $index = '6m_ago'; $period = 'MONTH(purchased_on) = MONTH(CURRENT_DATE - INTERVAL 6 MONTH) AND YEAR(purchased_on) = YEAR(CURRENT_DATE - INTERVAL 6 MONTH)'; break;
				case 8: $index = 'below_6m'; $period = 'purchased_on <= DATE_SUB(CURRENT_DATE, INTERVAL 6 MONTH)'; break;
			}

			// Total sales
			$result[$index]['all'] = $wpdb->get_row('SELECT COALESCE(SUM(amount), 0) AS amount FROM ' . $wpdb->prefix . 'dict_purchase_subscription_history 
											  WHERE ' . $period);

			// Total Teacher's Tool sales
			$result[$index]['teacher'] = $wpdb->get_row('SELECT COALESCE(SUM(amount), 0) AS amount FROM ' . $wpdb->prefix . 'dict_purchase_subscription_history 
											  WHERE typeid = 1 AND ' . $period);

			// Total Elearner Dictionary sales
			$result[$index]['learner'] = $wpdb->get_row('SELECT COALESCE(SUM(amount), 0) AS amount FROM ' . $wpdb->prefix . 'dict_purchase_subscription_history 
											  WHERE typeid = 2 AND dictionary_id = 1 AND ' . $period);

			// Total Collgiate Dictionary sales
			$result[$index]['collegiate'] = $wpdb->get_row('SELECT COALESCE(SUM(amount), 0) AS amount FROM ' . $wpdb->prefix . 'dict_purchase_subscription_history 
											  WHERE typeid = 2 AND dictionary_id = 2 AND ' . $period);

			// Total Medical Dictionary sales
			$result[$index]['medical'] = $wpdb->get_row('SELECT COALESCE(SUM(amount), 0) AS amount FROM ' . $wpdb->prefix . 'dict_purchase_subscription_history 
											  WHERE typeid = 2 AND dictionary_id = 3 AND ' . $period);

			// Total Intermediate Dictionary sales
			$result[$index]['intermediate'] = $wpdb->get_row('SELECT COALESCE(SUM(amount), 0) AS amount FROM ' . $wpdb->prefix . 'dict_purchase_subscription_history 
											  WHERE typeid = 2 AND dictionary_id = 4 AND ' . $period);

			// Total Elementary Dictionary sales
			$result[$index]['elementary'] = $wpdb->get_row('SELECT COALESCE(SUM(amount), 0) AS amount FROM ' . $wpdb->prefix . 'dict_purchase_subscription_history 
											  WHERE typeid = 2 AND dictionary_id = 5 AND ' . $period);
											  
			// Total Subscription ALL Dictionary sales
			$result[$index]['alldic'] = $wpdb->get_row('SELECT COALESCE(SUM(amount), 0) AS amount FROM ' . $wpdb->prefix . 'dict_purchase_subscription_history 
											  WHERE typeid = 2 AND dictionary_id = 6 AND ' . $period);

			// Total Point sales
			$result[$index]['point'] = $wpdb->get_row(
				'SELECT COALESCE(SUM(amount), 0) AS amount FROM ' . $wpdb->prefix . 'dict_purchase_subscription_history 
				 WHERE typeid = ' . SUB_POINTS_PURCHASE . ' AND ' . $period
			);

			// Total SAT Subscription sales 
				// Grammar
			$result[$index]['sat']['grammar'] = $wpdb->get_row(
				'SELECT COALESCE(SUM(amount), 0) AS amount 
				 FROM ' . $wpdb->prefix . 'dict_purchase_subscription_history AS h
				 JOIN ' . $wpdb->prefix . 'dict_user_subscription AS us ON us.id = h.user_subscription_id
				 WHERE h.typeid = ' . SUB_SAT_PREPARATION . ' AND us.sat_class_id = ' . CLASS_GRAMMAR . ' AND ' . $period
			);
				// Writing
			$result[$index]['sat']['writing'] = $wpdb->get_row(
				'SELECT COALESCE(SUM(amount), 0) AS amount 
				 FROM ' . $wpdb->prefix . 'dict_purchase_subscription_history AS h
				 JOIN ' . $wpdb->prefix . 'dict_user_subscription AS us ON us.id = h.user_subscription_id
				 WHERE h.typeid = ' . SUB_SAT_PREPARATION . ' AND us.sat_class_id = ' . CLASS_WRITING . ' AND ' . $period
			);
				// SAT Test
			$sat_test_ids = array(CLASS_SAT1, CLASS_SAT2, CLASS_SAT3, CLASS_SAT4, CLASS_SAT5);
			$result[$index]['sat']['sat_test'] = $wpdb->get_row(
				'SELECT COALESCE(SUM(amount), 0) AS amount 
				 FROM ' . $wpdb->prefix . 'dict_purchase_subscription_history AS h
				 JOIN ' . $wpdb->prefix . 'dict_user_subscription AS us ON us.id = h.user_subscription_id
				 WHERE h.typeid = ' . SUB_SAT_PREPARATION . ' AND us.sat_class_id IN(' . implode(',', $sat_test_ids) . ') AND ' . $period
			);

			// Total Self-study Subscription sales 5
			$result[$index]['self_study'] = $wpdb->get_row(
				'SELECT COALESCE(SUM(amount), 0) AS amount FROM ' . $wpdb->prefix . 'dict_purchase_subscription_history 
				 WHERE typeid = ' . SUB_SELF_STUDY . ' AND ' . $period
			);
			
			// Total MATH-Self-study Subscription 
			$result[$index]['math_self_study'] = $wpdb->get_row(
				'SELECT COALESCE(SUM(amount), 0) AS amount FROM ' . $wpdb->prefix . 'dict_purchase_subscription_history 
				 WHERE typeid = ' . SUB_SELF_STUDY_MATH . ' AND ' . $period
			);
			// Total MATH Teacher Tool Subscription 
			$result[$index]['math_teacher'] = $wpdb->get_row(
				'SELECT COALESCE(SUM(amount), 0) AS amount FROM ' . $wpdb->prefix . 'dict_purchase_subscription_history 
				 WHERE typeid = ' . SUB_TEACHER_TOOL_MATH . ' AND ' . $period
			);
			// Total MATH SAT I Subscription 
				//pre
			$result[$index]['sati']['pre'] = $wpdb->get_row(
				'SELECT COALESCE(SUM(amount), 0) AS amount 
				 FROM ' . $wpdb->prefix . 'dict_purchase_subscription_history AS h
				 JOIN ' . $wpdb->prefix . 'dict_user_subscription AS us ON us.id = h.user_subscription_id
				 WHERE h.typeid = ' . SUB_MATH_SAT_I_PREP . ' AND us.sat_class_id = ' . CLASS_MATH_SAT1PREP . ' AND ' . $period
			);
				//test
			$sati_test_ids = array(CLASS_MATH_SAT1A, CLASS_MATH_SAT1B, CLASS_MATH_SAT1C, CLASS_MATH_SAT1D, CLASS_MATH_SAT1E);
			$result[$index]['sati']['test'] = $wpdb->get_row(
				'SELECT COALESCE(SUM(amount), 0) AS amount 
				 FROM ' . $wpdb->prefix . 'dict_purchase_subscription_history AS h
				 JOIN ' . $wpdb->prefix . 'dict_user_subscription AS us ON us.id = h.user_subscription_id
				 WHERE h.typeid = ' . SUB_MATH_SAT_I_PREP . ' AND us.sat_class_id IN(' . implode(',', $sati_test_ids) . ') AND ' . $period
			);
			
			// Total MATH SAT II Subscription 
				//pre
			$result[$index]['satii']['pre'] = $wpdb->get_row(
				'SELECT COALESCE(SUM(amount), 0) AS amount 
				 FROM ' . $wpdb->prefix . 'dict_purchase_subscription_history AS h
				 JOIN ' . $wpdb->prefix . 'dict_user_subscription AS us ON us.id = h.user_subscription_id
				 WHERE h.typeid = ' . SUB_MATH_SAT_II_PREP . ' AND us.sat_class_id = ' . CLASS_MATH_SAT2PREP . ' AND ' . $period
			);
				//test
			$satii_test_ids = array(CLASS_MATH_SAT2A, CLASS_MATH_SAT2B, CLASS_MATH_SAT2C, CLASS_MATH_SAT2D, CLASS_MATH_SAT2E);
			$result[$index]['satii']['test'] = $wpdb->get_row(
				'SELECT COALESCE(SUM(amount), 0) AS amount 
				 FROM ' . $wpdb->prefix . 'dict_purchase_subscription_history AS h
				 JOIN ' . $wpdb->prefix . 'dict_user_subscription AS us ON us.id = h.user_subscription_id
				 WHERE h.typeid = ' . SUB_MATH_SAT_II_PREP . ' AND us.sat_class_id IN(' . implode(',', $satii_test_ids) . ') AND ' . $period
			);
				
		}

		return $result;
	}

	/*
	 * return flashcard folders list
	 *
	 * @param int $user_id
	 $ @param bool $teacher_folder		include teacher folder
	 *
	 * @return array
	 */
	public static function get_flashcard_folders($user_id, $teacher_folder = false)
	{
		global $wpdb;

		$query = 'SELECT *
				  FROM ' . $wpdb->prefix . 'dict_flashcard_folders
				  WHERE user_id IN (0,' . $user_id . ')';

		if(!$teacher_folder) {
			$query .= ' AND id <> ' . TEACHER_FLASHCARD_FOLDER;
		}

		$folders = $wpdb->get_results($query);

		return $folders;
	}

	/*
	 * return flashcards list
	 *
	 * @param int $user_id
	 *
	 * @return array
	 */
	public static function get_flashcards($user_id)
	{
		global $wpdb;

		$flashcards = $wpdb->get_results('SELECT f.*, fu.notes, fu.memorized
										  FROM ' . $wpdb->prefix . 'dict_flashcards AS f
										  LEFT JOIN ' . $wpdb->prefix . 'dict_flashcard_userdata AS fu ON fu.flashcard_id = f.id
										  WHERE created_by = ' . $user_id . ' AND teacher_set_id = 0');

		return $flashcards;
	}

	/*
	 * return teacher sets
	 *
	 * @param $student_id
	 *
	 * @return array
	 */
	public static function get_flashcard_teacher_sets($student_id)
	{
		global $wpdb;

		$sets = $wpdb->get_results('SELECT ts.*, u.display_name, g.name AS group_name
									FROM ' . $wpdb->prefix . 'dict_flashcard_teacher_sets AS ts
									JOIN ' . $wpdb->users . ' AS u ON u.id = ts.teacher_id
									JOIN ' . $wpdb->prefix . 'dict_groups AS g ON g.id = ts.group_id
									WHERE group_id IN (SELECT group_id FROM ' . $wpdb->prefix . 'dict_group_students WHERE student_id = ' . $student_id . ')');

		return $sets;
	}

	/*
	 * return list of flash card sets created by teacher
	 *
	 * @param array $filter
	 *
	 * @return array
	 */
	public static function get_flashcard_sets($filter = array())
	{
		global $wpdb;

		$query = 'SELECT fs.id, header_name AS sheet_name, d.name, teacher_id AS created_by, "Vocab. Builder" AS assignment, "' . ASSIGNMENT_VOCAB_BUILDER . '" AS assignment_id
				FROM ' . $wpdb->prefix . 'dict_flashcard_teacher_sets AS fs
				JOIN ' . $wpdb->prefix . 'dict_flashcards AS f ON f.teacher_set_id = fs.id
				JOIN ' . $wpdb->prefix . 'dict_dictionaries AS d ON d.id = f.dictionary_id
				WHERE teacher_id = ' . get_current_user_id();

		if(!empty($filter['sheet-name'])) {
			$query .= ' AND header_name LIKE \'%' . esc_sql($filter['sheet-name']) . '%\'';
		}

		$query .= ' GROUP BY fs.id';

		$sets = $wpdb->get_results($query);

		$obj = new stdCLass;
		$obj->items = $sets;
		$obj->total = count($sets);

		return $obj;
	}

	/*
	 * return teacher flashcards list
	 *
	 * @param $student_id
	 *
	 * @return array
	 */
	public static function get_teacher_flashcards($student_id)
	{
		global $wpdb;

		$flashcards = $wpdb->get_results('SELECT f.*, fu.notes, fu.memorized
										  FROM ' . $wpdb->prefix . 'dict_flashcards AS f
										  LEFT JOIN ' . $wpdb->prefix . 'dict_flashcard_userdata AS fu ON fu.flashcard_id = f.id
										  WHERE group_id IN (SELECT group_id FROM ' . $wpdb->prefix . 'dict_group_students WHERE student_id = ' . $student_id . ')');

		return $flashcards;
	}

	/*
	 * store flashcards created by teacher
	 * and assign it to a group
	 *
	 * @param array $data
	 *
	 * @return bool
	 */
	public static function assign_teacher_flashcards(&$data)
	{
		global $wpdb;

		$has_err = false;

		if($data['header-name'] == '') {
			$has_err = true;
			ik_enqueue_messages(__('Header name cannot be blank', 'iii-dictionary'), 'error');
		}

		if($data['dictionary'] === '' && $data['assignment-id'] != ASSIGNMENT_REPORT) {
			$has_err = true;
			ik_enqueue_messages(__('Please select a Dictionary', 'iii-dictionary'), 'error');
		}

		// escape data
		foreach($data['questions']['word'] as $key => $item)
		{
			$item = trim($item);
			if($item == '') {
				unset($data['questions']['word'][$key]);
			}
			else {
				$data['questions']['word'][$key] = esc_html($item);
			}
		}

		$current_user_id = get_current_user_id();
		if(!$has_err)
		{
			if(!$data['id']) {
				$wpdb->insert(
					$wpdb->prefix . 'dict_flashcard_teacher_sets',
					array(
						'teacher_id' => $current_user_id,
						'group_id' => $data['group'],
						'header_name' => $data['header-name'],
						'comments' => $data['comments'],
						'created_on' => date('Y-m-d', time())
					)
				);
				$teacher_set_id = $wpdb->insert_id;

				$query = 'INSERT INTO ' . $wpdb->prefix . 'dict_flashcards (created_by, folder_id, group_id, dictionary_id, teacher_set_id, word, teacher_sentence) VALUES ';
				foreach($data['questions']['word'] as $k => $word) {
					$values[] = '(' . $current_user_id . ',1,' . $data['group'] . ',' . $data['dictionary'] . ',' . $teacher_set_id . ',\'' . $word . '\', \'' . $data['questions']['sentence'][$k] . '\')';
				}

				$query .= implode(',', $values);

				$result = $wpdb->query($query);

				if($result !== false) {
					ik_enqueue_messages(__('Successfully send flash cards', 'iii-dictionary'), 'success');
					return true;
				}
				else {
					ik_enqueue_messages(__('An error occurred', 'iii-dictionary'), 'error');
				}
			}
			else {
				$result = $wpdb->update(
					$wpdb->prefix . 'dict_flashcard_teacher_sets',
					array(
						'group_id' => $data['group'],
						'header_name' => $data['header-name'],
						'comments' => $data['comments']
					),
					array(
						'id' => $data['id'],
						'teacher_id' => $current_user_id
					)
				);

				// get old flashcard ids
				$ids = $wpdb->get_col($wpdb->prepare('SELECT id FROM ' . $wpdb->prefix . 'dict_flashcards WHERE teacher_set_id = %s AND created_by = %s', $data['id'], $current_user_id));
				$wpdb->query('DELETE FROM ' . $wpdb->prefix . 'dict_flashcard_userdata WHERE flashcard_id IN (' . implode(',', $ids) . ' )');

				// remove old flashcards
				$wpdb->delete(
					$wpdb->prefix . 'dict_flashcards',
					array(
						'teacher_set_id' => $data['id'],
						'created_by' => $current_user_id
					)
				);

				// insert new flashcards
				$query = 'INSERT INTO ' . $wpdb->prefix . 'dict_flashcards (created_by, folder_id, group_id, dictionary_id, teacher_set_id, word, teacher_sentence) VALUES ';
				foreach($data['questions']['word'] as $k => $word) {
					$values[] = '(' . $current_user_id . ',1,' . $data['group'] . ',' . $data['dictionary'] . ',' . $data['id'] . ',\'' . $word . '\',\'' . $data['questions']['sentence'][$k] . '\')';
				}

				$query .= implode(',', $values);

				$result = $wpdb->query($query);

				if($result !== false) {
					ik_enqueue_messages(__('Successfully update flash card set', 'iii-dictionary'), 'success');
					return true;
				}
				else {
					ik_enqueue_messages(__('An error occurred', 'iii-dictionary'), 'error');
				}
			}
		}

		return false;
	}

	/*
	 * delete teacher flashcard set based on $cid
	 *
	 * @param int $cid
	 *
	 * @return bool
	 */
	public static function delete_teacher_flashcard_set($cid)
	{
		global $wpdb;

		$current_user_id = get_current_user_id();

		$wpdb->delete(
			$wpdb->prefix . 'dict_flashcard_teacher_sets',
			array(
				'id' => $cid,
				'teacher_id' => $current_user_id
			)
		);

		// get flashcard ids of this set
		$ids = $wpdb->get_col($wpdb->prepare('SELECT id FROM ' . $wpdb->prefix . 'dict_flashcards WHERE teacher_set_id = %s AND created_by = %s', $cid, $current_user_id));

		$wpdb->delete(
			$wpdb->prefix . 'dict_flashcards',
			array(
				'teacher_set_id' => $cid,
				'created_by' => $current_user_id
			)
		);

		$wpdb->query('DELETE FROM ' . $wpdb->prefix . 'dict_flashcard_userdata WHERE flashcard_id IN (' . implode(',', $ids) . ' )');

		ik_enqueue_messages(__('Successfully delete flashcard set', 'iii-dictionary'), 'success');
		return true;
	}

	/*
	 * get private message moderation status list
	 *
	 * @return array
	 */
	public static function get_message_mod_status()
	{
		global $wpdb;

		$results = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . 'dict_message_moderation_status');

		return $results;
	}

	/*
	 * get private message status list
	 *
	 * @return array
	 */
	public static function get_message_status()
	{
		global $wpdb;

		$results = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . 'dict_message_status');

		return $results;
	}

	/*
	 * get list of private messages
	 *
	 * @param string $type			Accept: 'sent', 'received', 'feedback'
	 * @param array $filter
	 * @param int $offset
	 * @param int $items_per_page
	 *
	 * @return object
	 */
	public static function get_private_messages($type, $filter = array(), $offset = 0, $items_per_page = 99999999)
	{
		global $wpdb;

		$query = 'SELECT COUNT(*)
				FROM ' . $wpdb->prefix . 'dict_messages AS m';

		switch($type)
		{
			case 'sent':
				$query .= ' JOIN ' . $wpdb->prefix . 'dict_private_message_outbox AS pmo ON pmo.message_id = m.id
							LEFT JOIN ' . $wpdb->users . ' AS u ON u.ID = pmo.recipient_id';

				$where[] = 'pmo.user_id = ' . $filter['user_id'];
				$columns = 'pmo.id, recipient_id, u.user_login AS recipient_login, u.user_email AS recipient_email, sent_on, subject, message';
				$orderby = 'sent_on DESC';
				break;
			case 'received':
				$query .= ' JOIN ' . $wpdb->prefix . 'dict_private_message_inbox AS pmi ON pmi.message_id = m.id
							JOIN ' . $wpdb->prefix . 'dict_message_status AS ms ON ms.id = pmi.status
							JOIN ' . $wpdb->prefix . 'dict_message_moderation_status AS mms ON mms.id = pmi.moderation_status
							LEFT JOIN ' . $wpdb->users . ' AS u ON u.ID = pmi.sender_id';

				$where[] = 'pmi.user_id = ' . $filter['user_id'];
				$columns = 'pmi.id, sender_id, u.user_login AS sender_login, u.user_email AS sender_email, received_on, status, mms.name AS mod_status, moderation_status, subject, message, system_message, display_at_login';
				$orderby = 'received_on DESC';
				break;
		}

		if(!empty($filter['sender-email'])) {
			$where[] = 'u.user_email LIKE \'%' . $filter['sender-email'] . '%\'';
		}

		if(!empty($filter['status'])) {
			$where[] = 'pmi.status = ' . $filter['status'];
		}

		if(!empty($filter['mod-status'])) {
			$where[] = 'pmi.moderation_status = ' . $filter['mod-status'];
		}

		if(!empty($where)) {
			$query .= ' WHERE ' . implode(' AND ', $where);
		}

		$total = $wpdb->get_col($query);

		$query = str_replace('COUNT(*)', $columns, $query);
		$query .= ' ORDER BY ' . $orderby;
		$query .= ' LIMIT ' . $offset . ',' . $items_per_page;

		$results = $wpdb->get_results($query);

		$obj = new stdCLass;
		$obj->total = $total[0];
		$obj->items = $results;

		return $obj;
	}

	/*
	 * get messages from a user inbox
	 *
	 * @param int $id 		inbox id
	 *
	 * return array
	 */
	public static function get_received_private_message($id)
	{
		global $wpdb;

		$message = $wpdb->get_row(
			'SELECT pmi.message_id, m.subject, m.message, sender_id, u.user_login AS sender_login, u.display_name AS sender_name, pmi.status
			FROM ' . $wpdb->prefix . 'dict_private_message_inbox AS pmi
			JOIN ' . $wpdb->prefix . 'dict_messages AS m ON m.id = pmi.message_id
			LEFT JOIN ' . $wpdb->users . ' AS u ON u.ID = pmi.sender_id
			WHERE pmi.id = ' . esc_sql($id)
		);

		return $message;
	}

	/*
	 * get messages from a user outbox
	 *
	 * @param int $id 		outbox id
	 *
	 * return array
	 */
	public static function get_sent_private_message($id)
	{
		global $wpdb;

		$message = $wpdb->get_row(
			'SELECT pmo.message_id, m.subject, m.message, recipient_id, u.user_login AS recipient_login, u.display_name AS recipient_name
			FROM ' . $wpdb->prefix . 'dict_private_message_outbox AS pmo
			JOIN ' . $wpdb->prefix . 'dict_messages AS m ON m.id = pmo.message_id
			LEFT JOIN ' . $wpdb->users . ' AS u ON u.ID = pmo.recipient_id
			WHERE pmo.id = ' . esc_sql($id)
		);

		return $message;
	}

	/*
	 * get a private message
	 *
	 * @param int $message_id
	 *
	 * @return object
	 */
	public static function get_private_message($message_id)
	{
		global $wpdb;

		$message = $wpdb->get_row(
			'SELECT m.*, u.user_login AS sender_login, u.display_name AS sender, ur.user_login AS receiver_login
			FROM ' . $wpdb->prefix . 'dict_messages AS m
			JOIN ' . $wpdb->users . ' AS u ON u.ID = m.sender_id
			LEFT JOIN ' . $wpdb->users . ' AS ur ON ur.ID = m.recipient_id
			WHERE m.id = ' . esc_sql($message_id)
		);

		return $message;
	}

	/*
	 * insert private message
	 *
	 * @param array $data
	 *
	 * @return mixed		Last insert id or false on error
	 */
	public static function insert_private_message($data)
	{
		global $wpdb;

		$result = $wpdb->insert($wpdb->prefix . 'dict_messages', $data);

		if($result) {
			return $wpdb->insert_id;
		}
		else {
			return false;
		}
	}

	/*
	 * insert message to inbox
	 *
	 * @param array $data
	 *
	 * @return mixed		last insert id or false on error
	 */
	public static function insert_private_message_inbox($data)
	{
		global $wpdb;
		
		if(is_array($data['user_id'])) {
			$new_array = $data['user_id'];
			foreach($new_array as $value) {
				$data['user_id'] = $value;
				$result = $wpdb->insert($wpdb->prefix . 'dict_private_message_inbox', $data);
			}
		}else {
			$result = $wpdb->insert($wpdb->prefix . 'dict_private_message_inbox', $data);
		}
		if($result) {
			return $wpdb->insert_id;
		}
		else {
			return false;
		}
	}

	/*
	 * insert message to outbox
	 *
	 * @param array $data
	 *
	 * @return mixed		last insert id or false on error
	 */
	public static function insert_private_message_outbox($data)
	{
		global $wpdb;
		if(is_array($data['recipient_id'])) {
			$new_array = $data['recipient_id'];
			foreach($new_array as $value) {
				$data['recipient_id'] = $value;
				$result = $wpdb->insert($wpdb->prefix . 'dict_private_message_outbox', $data);
			}
		}else {
			$result = $wpdb->insert($wpdb->prefix . 'dict_private_message_outbox', $data);
		}

		if($result) {
			return $wpdb->insert_id;
		}
		else {
			return false;
		}
	}

	/*
	 * get shopping cart of a user
	 *
	 * @param int $user_id
	 *
	 * @return object
	 */
	public static function get_user_shopping_cart($user_id)
	{
		global $wpdb;

		$cart = $wpdb->get_row('SELECT * FROM ' . $wpdb->prefix . 'dict_shopping_carts WHERE user_id = ' . $user_id);

		return $cart;
	}

	/*
	 * insert user cart
	 *
	 * @param array $data
	 *
	 * @return mixed
	 */
	public static function insert_user_shopping_cart($data)
	{
		global $wpdb;

		$result = $wpdb->insert(
			$wpdb->prefix . 'dict_shopping_carts',
			$data
		);

		return $result ? $wpdb->insert_id : $result;
	}

	/*
	 * update user cart
	 *
	 * @param int $user_id
	 * @param array $data
	 *
	 * @return mixed
	 */
	public static function update_user_shopping_cart($user_id, $data)
	{
		global $wpdb;

		$result = $wpdb->update(
			$wpdb->prefix . 'dict_shopping_carts',
			$data,
			array('user_id' => $user_id)
		);

		return $result;
	}

	/*
	 * delete user cart
	 *
	 * @param int $user_id
	 *
	 * @return mixed
	 */
	public static function delete_user_shopping_cart($user_id)
	{
		global $wpdb;

		$result = $wpdb->delete(
			$wpdb->prefix . 'dict_shopping_carts',
			array('user_id' => $user_id)
		);

		return $result;
	}
        
        /*
	 * delete user worksheet grading requests
	 *
	 * @param int $id
	 *
	 * @return mixed
	 */
	public static function delete_worksheet_grading_requests($requests_id)
	{
		global $wpdb;

		$result = $wpdb->delete(
			$wpdb->prefix . 'dict_worksheet_grading_requests',
			array('id' => $requests_id)
		);

		return $result;
	}

	/*
	 * get grades list
	 *
	 * @param array $filter
	 *
	 * @return array
	 */
	public static function get_grades($filter)
	{
		global $wpdb;

		$query = 'SELECT *
				FROM ' . $wpdb->prefix . 'dict_grades AS gr';

		if($filter['level'] == 1) {
			$query .= ' JOIN (SELECT id AS pid, name AS parent_name
								FROM ' . $wpdb->prefix . 'dict_grades
								WHERE level = 0) AS p ON p.pid = gr.parent_id';
		}

		if(isset($filter['level'])) {
			$where[] = 'gr.level = ' . esc_sql($filter['level']);
		}

		if(isset($filter['type']) && $filter['type'] != '') {
			$where[] = 'gr.type = \'' . esc_sql($filter['type']) . '\'';
		}

		if(isset($filter['parent_id']) && $filter['parent_id'] != '') {
			$where[] = 'gr.parent_id = ' . esc_sql($filter['parent_id']);
		}

		if(isset($filter['admin_only']) && !$filter['admin_only']) {
			$where[] = 'admin_only = 0';
		}
                if(isset($filter['cate'])) {
			$where[] = 'gr.id= ' . esc_sql($filter['cate']);
		}

		if(!empty($where)) {
			$query .= ' WHERE ' . implode(' AND ', $where);
		}

		if(!empty($filter['orderby'])) {
			$query .= ' ORDER BY ' . $filter['orderby'] . ' ' . $filter['order-dir'];
		}
               // var_dump($query);
		$grades = $wpdb->get_results($query);

		return $grades;
	}

	/*
	 * get grade by id
	 *
	 * @param int $id
	 *
	 * @return object
	 */
	public static function get_grade_by_id($id)
	{
		global $wpdb;

		$grade = $wpdb->get_row(
			'SELECT * FROM ' . $wpdb->prefix . 'dict_grades WHERE id = ' . esc_sql($id)
		);

		return $grade;
	}

	/*
	 * insert grade
	 *
	 * @param array $data
	 *
	 * @return mixed
	 */
	public static function store_grade($data)
	{
		global $wpdb;
		$valid = true;

		if(empty($data['name'])) {
			ik_enqueue_messages('Name field cannot be empty', 'error');
			$valid = false;
		}

		if($valid) {
			if($data['id'])
			{
				$result = $wpdb->update(
					$wpdb->prefix . 'dict_grades',
					$data,
					array('id' => $data['id'])
				);

				if($result !== false) {
					return $data['id'];
				}
			}
			else
			{
				$current_order = $wpdb->get_col(
					'SELECT MAX(ordering) FROM ' . $wpdb->prefix . 'dict_grades WHERE parent_id = ' . $data['parent_id']
				);

				if($data['ordering'] <= $current_order[0]) {
					$data['ordering'] = $current_order[0] + 1;
				}

				$result = $wpdb->insert(
					$wpdb->prefix . 'dict_grades',
					$data
				);

				if($result !== false) {
					return $wpdb->insert_id;
				}
			}
		}

		return false;
	}

	/*
	 * move grade order up by one
	 *
	 * @param int $id	the grade id
	 */
	public static function set_grade_order_up($id)
	{
		global $wpdb;

		$grade = MWDB::get_grade_by_id($id);

		if($grade->ordering > 1) {
			// move the higher grade down by one
			$wpdb->query(
				'UPDATE ' . $wpdb->prefix . 'dict_grades 
				SET ordering = ordering + 1 WHERE parent_id = ' . $grade->parent_id . ' AND ordering = ' . ($grade->ordering - 1)
			);

			// move the grade up by one
			$wpdb->query(
				'UPDATE ' . $wpdb->prefix . 'dict_grades 
				SET ordering = ordering - 1 WHERE id = ' . $id
			);
		}
	}

	/*
	 * move grade order down by one
	 *
	 * @param int $id	the grade id
	 */
	public static function set_grade_order_down($id)
	{
		global $wpdb;

		$grade = MWDB::get_grade_by_id($id);

		// move the higher grade down by one
		$wpdb->query(
			'UPDATE ' . $wpdb->prefix . 'dict_grades 
			SET ordering = ordering - 1 WHERE parent_id = ' . $grade->parent_id . ' AND ordering = ' . ($grade->ordering + 1)
		);

		// move the grade up by one
		$wpdb->query(
			'UPDATE ' . $wpdb->prefix . 'dict_grades 
			SET ordering = ordering + 1 WHERE id = ' . $id
		);
	}
	
	/* check display at login of message private from support
	 * @param (int) => $id_user
	 * return id for function get_received_private_message()
	 */
	public static function get_id_display_at_login($id = '') {
		global $wpdb;
		
		if(empty($id)) { $id = get_current_user_id(); }
		
		$filter = array(
			'user_id' 			=> $id,
			'sender_id' 		=> SYSTEM_MESSAGE,
			'status' 			=> MESSAGE_STATUS_UNREAD,
			'display_at_login' 	=> DISPLAY_AT_LOGIN
		);
		
		$query = 'SELECT * FROM ' . $wpdb->prefix . 'dict_private_message_inbox';
		
		if(!empty($filter['user_id'])) {
			$where[] = 'user_id = ' . $filter['user_id'];
		}
		
		if(!empty($filter['sender_id'])) {
			$where[] = 'sender_id = ' . $filter['sender_id'];
		}
		
		if(!empty($filter['status'])) {
			$where[] = 'status = ' . $filter['status'];
		}
		
		if(!empty($filter['display_at_login'])) {
			$where[] = 'display_at_login = ' . $filter['display_at_login'];
		}

		if(!empty($where)) {
			$query .= ' WHERE ' . implode(' AND ', $where);
		}
		
		$msg_id = $wpdb->get_row($query);
		
		return isset($msg_id->id)?$msg_id->id:0;
	}
	
	/* function group specific : for user can join this group
	* $param : group_id
	* return : redirect to another page
	*/
	public static function  lang_join_group($data) {

 		$g = MWDB::get_group($data['data-join'], 'id');
		
		if($g->price == 0) {
			if(MWDB::join_group($g->id)) {
				wp_redirect(locale_home_url() . '/?r=payments');
				exit;
			}
		}else {
			//set data to insert to database
			$g_data['sub-type'] 		= SUB_GROUP;
			$g_data['assoc-group'] 		= $g->id;
			$g_data['group-name'] 		= $g->name;			
			$g_data['group-pass'] 		= $g->password;
			$g_data['group-price'] 		= $g->price;
			$g_data['no-students'] 		= 1;
			//insert subscription of group
			$points = ik_get_user_points();
			$cart_items = get_cart_items();
			$is_match = false;
			//check user have a group in shop cart
			if(!empty($cart_items)) {
				foreach($cart_items as $item) {
					$is_match = $item->group_id == $g->id ? true : false;

				}
			}
			
			if($points < $g->price) {
				$_SESSION['method_point'] = $g->price;
			}else {
				//if usser have a group in shop cart ignore it.
				if(!$is_match) {
					ik_add_to_cart($g_data);
				}
				$_SESSION['open_method_point'] = true;
				wp_redirect(locale_home_url() . '/?r=payments');
				exit();
			}
			wp_redirect(locale_home_url() . '/?r=payments');
			exit();
		}
		
		
	}
	
	/* function get do homework/total homework of user and, get type link to worksheet of the group
	* @paramt #group_id, $user_id
	* @return x/y, type_link
	*/
	function get_something_in_group($group_id, $user_id = 0) {
		
		global $wpdb;
		
		$user_id = $user_id ? $user_id : get_current_user_id();
		//get total homework in group
		$qr_total_hw = 'SELECT COUNT(h.id) AS total_hw
						FROM ' . $wpdb->prefix . 'dict_homeworks AS h
						WHERE (h.active IS NULL OR h.active = 1) AND h.group_id = ' . $group_id;
		
		$total_hw = $wpdb->get_results($qr_total_hw, ARRAY_A);
		
		//get step of user
		$qr_step_of_user = 'SELECT  COUNT(IF(hr.finished = 1, 1, NULL) OR IF(dpr.id != 0, 1, NULL)) AS completed_homework
		   FROM ' . $wpdb->prefix . 'dict_group_students AS gs
		   JOIN ' . $wpdb->prefix . 'dict_groups AS g ON g.id = gs.group_id
		   LEFT JOIN ' . $wpdb->prefix . 'dict_group_details AS gc ON gc.group_id = g.id
		   LEFT JOIN ' . $wpdb->prefix . 'dict_homeworks AS h ON h.group_id = g.id
		   LEFT JOIN (SELECT homework_id, finished FROM ' . $wpdb->prefix . 'dict_homework_results WHERE userid = ' . $user_id . ') AS hr ON hr.homework_id = h.id 
		   LEFT JOIN (SELECT practice_id, id FROM '. $wpdb->prefix .'dict_practice_results  WHERE user_id = '. $user_id .') AS dpr ON dpr.practice_id = h.id 
		   JOIN ' . $wpdb->prefix . 'users AS u ON u.ID = g.created_by';
		$qr_step_of_user .= ' WHERE g.active = 1 AND student_id = ' . $user_id . ' AND gs.absented = 0 AND (gc.class_type_id IS NULL OR gc.class_type_id <> ' . CLASS_OTHERS . ')  AND (h.active IS NULL OR h.active = 1) ';
		$qr_step_of_user .= ' GROUP BY g.id';
		$qr_step_of_user .= ' HAVING g.id = ' . $group_id;
		$step_of_user = $wpdb->get_results($qr_step_of_user, ARRAY_A);
		//get step
		$filter['homework_result'] = true;
		$filter['is_active'] = 1;
		$filter['user_id'] = $user_id;
		$all_step = MWDB::get_group_homeworks($group_id, $filter);
		$step = array();
		foreach($all_step->items as $hw) {
			if((!$hw->finished || is_null($hw->finished)) && (!$hw->practice_id || is_null($hw->practice_id))) { 
				$step['id'] = $hw->hid;
				$step['prt'] = $hw->for_practice;
				$step['assg'] = $hw->assignment_id;
				goto EBREAK;
			}
		}
		EBREAK:
		$obj = new stdCLass;
		$obj->total_hw = $total_hw[0];
		$obj->step_of_user = $step_of_user[0];
		$obj->step = $step;
		return $obj;
	
	}
	
	/* Store practice result of math homework
	* @param homework_id, user_id, sheet_id
	* result nothing
	*/
	
	function _store_math_practice($data) {
		global $wpdb;
		//check if exists record.
		$exists = $wpdb->get_row('SELECT id  FROM ' . $wpdb->prefix . 'dict_practice_results 
								WHERE user_id = ' . esc_sql($data['user_id']) . ' 
								AND   sheet_id = ' . esc_sql($data['sheet_id']) . ' 
								AND practice_id = ' . esc_sql($data['practice_id']));
		if(!$exists) { 
			$result = $wpdb->insert(
				$wpdb->prefix . 'dict_practice_results', 
				array(
					'user_id' => $data['user_id'], 
					'sheet_id' => $data['sheet_id'],
					'answers' => '',
					'practice_id' => $data['practice_id']
				)
			);
			$pid = $wpdb->insert_id;
		}
	}
	
	/* Get all name of dict_subscription_type
	 * @param : null
	 * return : array
	 */
	function _get_name_subscription_type() {
		global $wpdb;
		
		$var = $wpdb->get_col('SELECT dst.name FROM '. $wpdb->prefix .'dict_subscription_type AS dst');
		
		return $var;
	}
	
	/* Get all chat session in database
	 * @param filter, offset, items_per_page
	 * return : obj
	 */
	public static function get_chat_session_requests($filter, $offset = 0, $items_per_page = 99999999) {
		global $wpdb;
		
		$query = 'SELECT COUNT(*)
				FROM 		'. $wpdb->prefix .'dict_chat_session AS dcs
				INNER JOIN 	'. $wpdb->prefix .'users AS us ON us.ID = dcs.user_id
				INNER JOIN 	'. $wpdb->prefix .'dict_sheets AS ds ON ds.id = dcs.sheet_id
				INNER JOIN  '. $wpdb->prefix .'dict_grades AS dg ON dg.id = ds.grade_id
				ORDER BY dcs.id  DESC';
				//WHERE dcs.status != 2  ORDER BY dcs.id DESC';
				
		$total 		= $wpdb->get_col($query);
		$query 		= str_replace('COUNT(*)', 'dcs.*, us.user_email AS user, dg.name AS category', $query);
		$query 		.= ' LIMIT ' . $offset . ',' . $items_per_page;
		$requests 	= $wpdb->get_results($query);
		
		
		$obj 		= new stdCLass;
		$obj->items = $requests;
		$obj->total = $total[0];
		
		return $obj;
	}
        public static function update_chat_date($id){
            global $wpdb;
            $wpdb->update( $wpdb->prefix . 'dict_chat_session', array('quit_status' => 0), array('id' => $id) );
        }
        public static function get_order_sheet($id){
            global $wpdb;
            $result = $wpdb->get_row(
                     'SELECT * FROM '. $wpdb->prefix . 'dict_sheets WHERE id='.$id );
            return $result;
        }
  
    public static function update_edit_class($id,$name,$price,$aboutclass,$new_password){
        global $wpdb;
        $wpdb->update( $wpdb->prefix . 'dict_groups', array('about_class' => $aboutclass,'name' => $name,'password'=>$new_password), array('id' => $id) );
        $count=$wpdb->query('SELECT COUNT(*) FROM '.$wpdb->prefix.'dict_group_details WHERE group_id='.$id);
        if($count!=0){
            $wpdb->update( $wpdb->prefix . 'dict_group_details', array('price' => $price), array('group_id' => $id) );
        }
        else{
//            $wpdb->insert($wpdb->prefix . 'dict_group_details',[
//                'group_id'=>$id,
//                ''
//            ]);
        }


    }
	public static function get_message_receive($id, $search = ''){
        global $wpdb;
 
 		$user_id = get_current_user_id();

 		if(trim($search) != ''){
 			$q = ' SELECT * FROM ' . $wpdb->users . 'AS u WHERE u.display_name LIKE \'%' . $search . '%\' OR u.user_login LIKE \'%' . $search . '%\'';
 			$user = $wpdb->get_results($q);
 			if($user)
 				$wh = ' OR pmi.sender_id = '.$user->ID;
 			else
 				$wh = '';
 			$where = ' AND (m.subject LIKE \'%'.$search.'%\' OR m.message LIKE \'%'.$search.'%\''.$wh.')';
 		}else{
 			$where = '';
 		}
 		
        $query=' SELECT pmi.id, pmi.message_id, pmi.sender_id, u.display_name AS display_name, u.user_login AS sender_login, u.user_email AS sender_email, pmi.received_on, pmi.moderation_status, m.subject, m.message, m.timezone, pmi.system_message, pmi.display_at_login, pmi.status, pmi.reply_id  
			FROM ' . $wpdb->prefix . 'dict_private_message_inbox AS pmi
			JOIN ' . $wpdb->prefix . 'dict_messages AS m ON m.id = pmi.message_id
			LEFT JOIN ' . $wpdb->users . ' AS u ON u.ID = pmi.sender_id
			WHERE pmi.group_id ='.$id.' AND pmi.user_id = '.$user_id.$where.' ORDER BY pmi.received_on DESC';

         $results = $wpdb->get_results($query);

         return $results;
    }

    public static function get_message_sent($id, $search = ''){
        global $wpdb;

        $user_id = get_current_user_id();

        if(trim($search) != ''){
 			$q = ' SELECT * FROM ' . $wpdb->users . 'AS u WHERE u.display_name LIKE \'%' . $search . '%\' OR u.user_login LIKE \'%' . $search . '%\'';
 			$user = $wpdb->get_results($q);
 			if($user)
 				$wh = ' OR pmo.sender_id = '.$user->ID;
 			else
 				$wh = '';
 			$where = ' AND (m.subject LIKE \'%'.$search.'%\' OR LIKE m.message \'%'.$search.'%\''.$wh.')';
 		}else{
 			$where = '';
 		}

       	$query=' SELECT pmo.id, pmo.message_id, pmo.recipient_id, u.display_name AS display_name, u.user_login AS recipient_login, u.user_email AS recipient_email, pmo.sent_on, m.subject, m.message, m.timezone, pmo.status, pmo.reply_id 
			FROM ' . $wpdb->prefix . 'dict_private_message_outbox AS pmo
			JOIN ' . $wpdb->prefix . 'dict_messages AS m ON m.id = pmo.message_id
			LEFT JOIN ' . $wpdb->users . ' AS u ON u.ID = pmo.recipient_id
			WHERE pmo.group_id ='.$id .' AND pmo.user_id = '.$user_id.$where.' ORDER BY pmo.sent_on DESC';

         $results = $wpdb->get_results($query);
         return $results;
    }
    public static function add_user($data)
	{
		global $wpdb;
		
//                var_dump($data);die;
		$result = $wpdb->insert(
			$wpdb->prefix . 'dict_user',
			array(
				'user_login' => $data['user_login'],
				'user_pass' => $data['user_pass'],
//				'user_nicename'  => $data['user_nicename'],
//				'user_email' => $data['user_email'],
//				'user_url' => $data['user_url'],
//				'user_registered' => $data['user_registered'],
//				'user_activation_key'=> $data['user_activation_key'],
//				'user_status' => $data['user_status'],
				'display_name' 	 => $data['display_name']
				
			)
		);

		return 1;
	}
 
	public static function get_tutoring_date()
	{
		global $wpdb;
		$query = 'SELECT tp.*, u.display_name AS student_name
            FROM ' . $wpdb->prefix . 'dict_tutoring_plan AS tp
            LEFT JOIN ' . $wpdb->users . ' AS u ON u.ID = tp.id_user
            GROUP BY tp.date
            ORDER BY tp.date ASC';
	    $results = $wpdb->get_results($query);
	    $arr = array();
	    if(count($results) > 0){
	        foreach ($results as $value) {
	            $arr[] = date('Y-m-j',strtotime($value->date));
	        }
	    }
	    return json_encode($arr);
	}

	public static function get_tutoring_date_teach()
    {
        global $wpdb;
        $user_id = get_current_user_id();
        $query = 'SELECT tp.*
            FROM ' . $wpdb->prefix . 'dict_tutoring_available AS tp
            WHERE tp.tutor_id = '.$user_id.' 
            GROUP BY tp.date
            ORDER BY tp.date ASC';
        $results = $wpdb->get_results($query);
        $arr = array();
        $time_zone = get_user_meta($user_id, 'user_timezone', true);
        $time_zone = empty($time_zone) ? 0 : $time_zone;    
        $u_time_zone_index = get_user_meta($user_id, 'time_zone_index', true);
        $u_time_zone_index = empty($u_time_zone_index)? 0 : $u_time_zone_index;
        $time_zone_name = get_user_meta($user_id, 'time_zone_name', true);
        $timezone_name = empty($time_zone_name)? convert_timezone_to_name($u_time_zone_index):$time_zone_name;
        if(count($results) > 0){
            foreach ($results as $value) {
                $date_time = explode('~', $value->time);
				$start = substr(trim($date_time[0]),0,-3).' '.strtoupper(substr(trim($date_time[0]),-2));
				$end = substr(trim($date_time[1]),0,-3).' '.strtoupper(substr(trim($date_time[1]),-2));
				$timezone_scheduled = convert_timezone_to_name($value->time_zone_index);
				$original_datetime = $value->date.' '.$start;
				$original_timezone = new DateTimeZone($timezone_scheduled);
				$datetime = new DateTime($original_datetime, $original_timezone);
				$target_timezone = new DateTimeZone($timezone_name);
				$datetime->setTimeZone($target_timezone);
				$arr[] = $datetime->format('Y-m-d');
            }
        }

        $query4 = "SELECT tp.*
            FROM " . $wpdb->prefix . "dict_tutoring_plan AS tp
            WHERE tp.tutor_id = ".$user_id." 
            ORDER BY tp.date DESC"; //tp.date = '".$date."' AND 
        $results4 = $wpdb->get_results($query4);
        if(count($results4) > 0){
            foreach ($results4 as $value) {
            	$date_time = explode('~', $value->time);
				$start = substr(trim($date_time[0]),0,-3).' '.strtoupper(substr(trim($date_time[0]),-2));
				$end = substr(trim($date_time[1]),0,-3).' '.strtoupper(substr(trim($date_time[1]),-2));
				$timezone_scheduled = convert_timezone_to_name($value->time_zone_index);
				$original_datetime = $value->date.' '.$start;
				$original_timezone = new DateTimeZone($timezone_scheduled);
				$datetime = new DateTime($original_datetime, $original_timezone);
				$target_timezone = new DateTimeZone($timezone_name);
				$datetime->setTimeZone($target_timezone);
				$arr[] = $datetime->format('Y-m-d');
            }
        }

        $arr_uni = array_unique($arr);
		$arr_merge = array();
		if(count($arr_uni) > 0){
			foreach($arr_uni as $v){
				$arr_merge[] = $v;
			}
		}
        return json_encode($arr_merge);
    }
	
	public static function get_my_schedules()
    {
        global $wpdb;
        $user_id = get_current_user_id();
        $query = "SELECT tp.*, u.display_name AS student_name
            FROM " . $wpdb->prefix . "dict_tutoring_plan AS tp
            LEFT JOIN " . $wpdb->users . " AS u ON u.ID = tp.id_user
            WHERE tp.tutor_id = ".$user_id." 
            ORDER BY tp.date DESC"; //tp.date = '".$date."' AND 
        $results = $wpdb->get_results($query);
        $arr = array();
        $pst = mw_get_option('price_schedule_tutoring');

        $user_points = get_user_meta($user_id, 'user_points', true);
        $user_points = empty($user_points) ? 0 : $user_points;
        $time_zone = get_user_meta($user_id, 'user_timezone', true);
        $time_zone = empty($time_zone) ? 0 : $time_zone;    
        $u_time_zone_index = get_user_meta($user_id, 'time_zone_index', true);
        $u_time_zone_index = empty($u_time_zone_index)? 0 : $u_time_zone_index;
        $time_zone_name = get_user_meta($user_id, 'time_zone_name', true);
        $timezone_name = empty($time_zone_name)? convert_timezone_to_name($u_time_zone_index):$time_zone_name;

        $dt = new DateTime('now', new DateTimezone($timezone_name));

        if(count($results) > 0){
            foreach ($results as $value) {            
                //echo date('Y-m-d',$timezone).'-'.$date;
                $date_time = explode('~', $value->time);
                $start = substr(trim($date_time[0]),0,-3).' '.strtoupper(substr(trim($date_time[0]),-2));
                $end = substr(trim($date_time[1]),0,-3).' '.strtoupper(substr(trim($date_time[1]),-2));
                $timezone_scheduled = convert_timezone_to_name($value->time_zone_index);
                
                $original_datetime_st = $value->date.' '.$start;
                $original_timezone_st = new DateTimeZone($timezone_scheduled);
                $datetime_st = new DateTime($original_datetime_st, $original_timezone_st);
                $target_timezone_st = new DateTimeZone($timezone_name);
                $datetime_st->setTimeZone($target_timezone_st);
            
                $original_datetime_ed = $value->date.' '.$end;
                $original_timezone_ed = new DateTimeZone($timezone_scheduled);
                $datetime_ed = new DateTime($original_datetime_ed, $original_timezone_ed);
                $target_timezone_ed = new DateTimeZone($timezone_name);
                $datetime_ed->setTimeZone($target_timezone_ed);
                
                $original_datetime_ct = $value->created_on;
                $original_timezone_ct = new DateTimeZone($timezone_scheduled);
                $datetime_ct = new DateTime($original_datetime_ct, $original_timezone_ct);
                $target_timezone_ct = new DateTimeZone($timezone_name);
                $datetime_ct->setTimeZone($target_timezone_ct);
                
                $time = $datetime_st->format('h:ia').' - '.$datetime_ed->format('h:ia');
                $chour = (int)$dt->format('G');
                $cminute = (int)$dt->format('i');
                $uhour = (int)$datetime_ed->format('G');
                $uminute = (int)$datetime_ed->format('i');
                //echo $datetime_st->format('Y-m-d').'='.$dt->format('Y-m-d').'|'.$chour.'='.$uhour.'|'.$cminute.'='.$uminute.'<br>';
                if((strtotime($datetime_st->format('Y-m-d')) == strtotime($dt->format('Y-m-d')) && ($uhour > $chour || ($uhour = $chour && $uminute >= $cminute))) || strtotime($datetime_st->format('Y-m-d')) > strtotime($dt->format('Y-m-d'))){
                    $user = get_user_by('id', $value->tutor_id);
                    
                    if($user){
                        $tutor_name = $user->display_name;
                    }else{
                        $tutor_name = '';
                    }
                    
                    if($datetime_st->format('G') == '0')
                        $start_id = '12_'.$datetime_st->format('i_a');
                    else
                        $start_id = $datetime_st->format('G_i_a');
                        
                    if($datetime_ed->format('G') == '0')
                        $end_id = '12_'.$datetime_ed->format('i_a');
                    else
                        $end_id = $datetime_ed->format('G_i_a');

                    $type_slide = 'upcoming';                        
                    
                    $total = (int)$value->total_time*$pst/100;
                    $location = convert_timezone_to_location($value->time_zone_index);
                    $arr[] = array(
                                'id' => $value->id,
                                'subject' => $value->subject,
                                'date' => $datetime_st->format('F d, Y'),
                                'stuff' => $datetime_st->format('(l)'),
                                'time' => $datetime_st->format('h:i:a').' ~ '.$datetime_ed->format('h:i:a'),
                                'time_view' => $time,
                                'confirmed' => $value->confirmed,
                                'canceled' => $value->canceled,
                                'time_zone' => $value->time_zone,
                                'id_user' => $value->id_user,
                                'tutor_id' => $value->tutor_id,
                                'private_subject' => $value->private_subject,
                                'short_message' => $value->short_message,                        
                                'student_name' => $value->student_name,
                                'tutor_name' => $tutor_name,
                                'status'    => $value->status,
                                'total' => $total,
                                'total_time' => $value->total_time,
                                'time_start' => $datetime_st->format('Y-m-d / h:i a'),
                                'time_end' => $datetime_ed->format('Y-m-d / h:i a'),
                                'stime' => strtotime($datetime_st->format('Y-m-d H:i:s')),
                                'location' => $location,
                                'start_id' => $start_id,
                                'end_id' => $end_id,
                                'create_on' => $datetime_ct->format('M d, Y (h:i)'),
                                'fromtime' => $datetime_st->format('h:i:a'),
                                'totime' => $datetime_ed->format(' h:i:a'),
                                'fromhour'    => $datetime_st->format('G'),
                                'fromminute'    => $datetime_st->format('i'),
                                'tohour'    => $datetime_ed->format('G'),
                                'tominute'    => $datetime_ed->format('i'),
                                'totype'    => $datetime_ed->format('a'),
                                'day' => $datetime_st->format('Y-m-d'),
                                'created' => date('Y-m-d H:i:s', strtotime($value->created_on)),
                                'type_slide' => $type_slide
                            );
                }
            }
        }
        if(count($arr) > 0){
            array_multisort(
                array_column($arr, 'stime'), SORT_NUMERIC, SORT_ASC,
                $arr
            );
        }

        if(strtotime($arr[0]['day']) == strtotime($dt->format('Y-m-d'))){
            $arr[0]['type_slide'] = 'current';
        }
        return $arr;
    }
}
