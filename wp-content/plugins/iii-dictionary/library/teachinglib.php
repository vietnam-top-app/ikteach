<?php
/*
 *	SAT and Teaching related function
 */


/*
 * init sat preparation progress of a user
 */
function ik_init_sat_progress($user_id = 0)
{
	$user_id = $user_id ? $user_id : get_current_user_id();

	$progress = array(
					CLASS_GRAMMAR => 0,
					CLASS_WRITING => 0,
					CLASS_SAT1 => 0,
					CLASS_SAT2 => 0,
					CLASS_SAT3 => 0,
					CLASS_SAT4 => 0,
					CLASS_SAT5 => 0
				);

	update_user_meta($user_id, 'sat-progress', json_encode($progress));
}

/*
 * get current user sat progress
 */
function ik_get_sat_progress($user_id = 0)
{
	$user_id = $user_id ? $user_id : get_current_user_id();

	return json_decode(get_user_meta($user_id, 'sat-progress',true), true);
}

/*
 * increase the sat progress
 */
function ik_increase_sat_progress($class_id, $user_id = 0)
{
	$user_id = $user_id ? $user_id : get_current_user_id();

	$progress = ik_get_sat_progress($user_id);

	$progress[$class_id] = $progress[$class_id] + 1;

	update_user_meta($user_id, 'sat-progress', json_encode($progress));
}

/*
 * check if user qualify to join SAT class
 */
function ik_is_user_qualify_for_sat_class($class_id, $user_id = 0, $show_list = false)
{
	global $wpdb;

	$user_id = $user_id ? $user_id : get_current_user_id();

	$g = MWDB::get_group($class_id, 'id');

	$homework_results = $wpdb->get_results(
		'SELECT score, s.sheet_name
		FROM ' . $wpdb->prefix . 'dict_group_details AS gc
		LEFT JOIN ' . $wpdb->prefix . 'dict_homeworks AS h ON h.group_id = gc.group_id
		LEFT JOIN ' . $wpdb->prefix . 'dict_homework_results AS hr ON hr.homework_id = h.id
		LEFT JOIN ' . $wpdb->prefix . 'dict_sheets AS s ON s.id = h.sheet_id
		WHERE class_type_id = ' . $g->class_type_id . ' AND ordering = ' . ($g->ordering - 1)
	);

	$qualified = true;

	// No worksheet assigned to the group
	if(empty($homework_results[0]->sheet_name)) {
		$qualified = false;
		ik_enqueue_messages(__('No Worksheet assigned to this class yet.', 'iii-dictionary'), 'error');
	}
	// There's homework assigned
	else {
		$not_passed_worksheets = array();
		foreach($homework_results as $result) {
			if(!empty($result->score)) {
				if($result->score < 80) {
					$qualified = false;
					$not_passed_worksheets[] = array('score' => $result->score, 'name' => $result->sheet_name);
				}
			}
			else {
				$qualified = false;
				$not_passed_worksheets[] = array('score' => 0, 'name' => $result->sheet_name);
			}
		}

		if($show_list) {
			if(!empty($not_passed_worksheets)) {
				$msg = __('Not passed Worksheets:', 'iii-dictionary');

				foreach($not_passed_worksheets as $sheet) {
					$msg .= '<br>- ' . $sheet['name'] . ' - ' . sprintf(__('Latest Score: %s', 'iii-dictionary'), $sheet['score']);
				}
			}

			ik_enqueue_messages($msg, 'error');
		}
	}

	return $qualified;
}

/*
 * check if the user has agreed to the latest agreement (registration agreement).
 */
function ik_is_teacher_agreement_uptodate($type = '')
{
	if(empty($type)) {
		$agreement_update_date = mw_get_option('agreement-update-date');
		$user_agreed_ver = get_user_meta(get_current_user_id(), 'teacher_agreement_ver', true);
	}else {
		$agreement_update_date = mw_get_option('agreement-update-date');
		$user_agreed_ver = get_user_meta(get_current_user_id(), 'math_teacher_agreement_ver', true);
	}
	return $agreement_update_date == $user_agreed_ver;
}


/*
 * check if the teacher has agreed to the teaching agreement 
 */
function ik_is_teaching_agreement_agreed($type = '')
{
	if(empty($type)) {
		$date = get_user_meta(get_current_user_id(), 'teaching_agreement_ver', true);
	}else {
		$date = get_user_meta(get_current_user_id(), 'math_teaching_agreement_ver', true);
	}
	return !empty($date);
}

/*
 * agree teaching agreement
 */
function ik_agree_teaching_agreement($type = '')
{
	$agreement_update_date = mw_get_option('agreement-update-date');
	if(empty($type)) {
		update_user_meta(get_current_user_id(), 'teaching_agreement_ver', $agreement_update_date);
	}else {
		update_user_meta(get_current_user_id(), 'math_teaching_agreement_ver', $agreement_update_date);
	}
}

/*
 *	request worksheet grading
 *
 * @param int $homework_result_id		homework result need grading
 * @param int $homework_id
 * @param int $user_id
 *
 * @return boolean
 */
function ik_request_worksheet_grading($homework_result_id, $homework_id, $user_id = 0)
{
	$user_id = $user_id ? $user_id : get_current_user_id();

	$user_points = ik_get_user_points($user_id);

	$homework = MWDB::get_homework_assignment_by_id($homework_id);

	if($user_points < $homework->grading_price) {
		ik_enqueue_messages(__('You don\'t have enough points to request grading for this homework.', 'iii-dictionary'), 'error');
		return false;
	}

	global $wpdb;

	$result = $wpdb->insert(
		$wpdb->prefix . 'dict_worksheet_grading_requests',
		array(
			'requested_by' => $user_id,
			'homework_result_id' => $homework_result_id,
			'homework_id' => $homework_id,
			'paid_amount' => $homework->grading_price,
			'requested_on' => date('Y-m-d', time())
		)
	);

	if($result) {
		ik_deduct_user_points($homework->grading_price, $user_id);

		ik_enqueue_messages(__('Your request has been saved.', 'iii-dictionary'), 'success');
		return true;
	}
	else {
		ik_enqueue_messages(__('Unknown error, cannot request grading.', 'iii-dictionary'), 'error');
	}

	return false;
}

/*
 * finish grading request
 *
 * @param int $request_id
 *
 * @return boolean
 */
function ik_finish_grading_request($request_id,$status=0,$grading=0)
{
	global $wpdb;
        
        if($status==1){
	$request = MWDB::get_worksheet_grading_request_self_study($request_id);
        }else{
	$request = MWDB::get_worksheet_grading_request($request_id);
        }
	if($request)
	{
		$user_id = get_current_user_id();
                if($grading!=1){
		$teacher_earning = ik_calc_grading_earning($request->paid_amount);

		// increase teacher points
		ik_add_user_points($teacher_earning, $user_id);

		$data = array(
			'user_id' => $user_id,
			'point_transaction_type_id' => POINT_TXN_GRADING_WORKSHEET,
			'grading_worksheet_txn_id' => $request_id,
			'purchasing_worksheet_txn_id' => 0,
			'amount' => $teacher_earning,
			'note' => 'Graded worksheet "' . $request->sheet_name . '"'
		);

		MWDB::store_user_point_transaction($data);
                $_SESSION['status']=0;
                $_SESSION['grading']=0;
                }
		$finish_date = date('Y-m-d', time());

		// update request record
		$result = $wpdb->update(
			$wpdb->prefix . 'dict_worksheet_grading_requests',
			array(
				'accepted_by' => $user_id,
				'finished' => 1,
				'accepted_on' => $finish_date,
				'finished_on' => $finish_date
			),
			array(
				'id' => $request_id
			)
		);

		if($result !== false) {
			$_SESSION['grading_homework'] = array();
			ik_enqueue_messages(sprintf(__('Finished grading homework. You\'ve earned %s pts.', 'iii-dictionary'), $teacher_earning), 'success');
			return true;
		}
		else {
			ik_enqueue_messages(__('Unknown error occurs.', 'iii-dictionary'), 'error');
		}
	}
	else
	{
		ik_enqueue_messages(__('Unknown error.', 'iii-dictionary'), 'error');
	}

	return false;
}

/*
 * calculate offer price of a worksheet after margin
 *
 * @param int $price
 *
 * @return int
 */
function ik_calc_offer_worksheet_price($price)
{
	$margin = mw_get_option('teacher-sheet-price-margin');

	return round($price / (1 - ($margin / 100)), 1);
}

/*
 * calculate earning amount for teacher after margin
 *
 * @param int $price
 *
 * @return int
 */
function ik_calc_grading_earning($price)
{
	$margin = mw_get_option('teacher-grading-price-margin');

	return round($price * (1 - ($margin / 100)), 1);
}

/*
 * purchase a worksheet
 *
 * @param int $offer_id
 *
 * @return boolean
 */
function ik_purchase_worksheet($offer_id)
{
	$user_id = get_current_user_id();
	$offer = MWDB::get_worksheet_offer($offer_id);

	// calculate amount of points user has to pay
	$paid_amount = ik_calc_offer_worksheet_price($offer->offered_price);
	
	if(ik_deduct_user_points($paid_amount) !== false)
	{
		// increase number of points in the saler's account
		ik_add_user_points($offer->offered_price, $offer->user_id);

		// copy worksheet to user's library
		$new_sheet_id = MWDB::copy_worksheet($offer->sheet_id);

		if($new_sheet_id)
		{
			// save purchase history
			$data = array(
					'offer_id' => $offer_id,
					'purchased_by' => $user_id,
					'copied_sheet_id' => $new_sheet_id,
					'paid_amount' => $paid_amount
			);

			$transaction_id = MWDB::store_worksheet_purchase_history($data);

			$data = array(
				'user_id' => $user_id,
				'point_transaction_type_id' => POINT_TXN_PURCHASING_WORKSHEET,
				'grading_worksheet_txn_id' => 0,
				'purchasing_worksheet_txn_id' => $transaction_id,
				'amount' => $paid_amount,
				'note' => 'Purchased Worksheet "' . $offer->sheet_name . '"'
			);

			MWDB::store_user_point_transaction($data);

			ik_enqueue_messages(__('Successfully purchase worksheet.', 'iii-dictionary'), 'success');

			return true;
		}
		else
		{
			ik_enqueue_messages(__('Cannot save worksheet to your library.', 'iii-dictionary'), 'error');
		}
	}
	else
	{
		ik_enqueue_messages(__('You don\'t have enough points to purchase this worksheet.', 'iii-dictionary'), 'notice');
	}

	return false;
}