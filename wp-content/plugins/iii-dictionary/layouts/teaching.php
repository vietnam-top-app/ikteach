<?php
       
	wp_enqueue_script('nodejs-socket-teaching', 'http://166.62.122.90:8000/socket.io/socket.io.js', array(), '1.0.0', true );
	wp_enqueue_script('chat-main-js-teaching', plugins_url('iii-dictionary/chat/js/chatMain.js'), array(), '1.0.0', true );
	
	$is_math_panel 	= is_math_panel();
	$_page_title 	= __('Earn Money by Teaching', 'iii-dictionary');
	
	$route = get_route();
	if(empty($route[1])) {
		$active_tab = 'register';
	}
	else {
		$active_tab = $route[1];
	}
	//TAB FOR ENGLISH IKLEARN
	$tab_options = array(
		'items' => array(
			'register' => array('url' => locale_home_url() . '/?r=teaching/register', 'text' => __('Register', 'iii-dictionary')),
			'sell-worksheet' => array('url' => locale_home_url() . '/?r=teaching/sell-worksheet', 'text' => __('Sell Worksheet', 'iii-dictionary')),
			'purchase-worksheet' => array('url' => locale_home_url() . '/?r=teaching/purchase-worksheet', 'text' => __('Purchase Worksheet', 'iii-dictionary')),
			'teach-class' => array('url' => locale_home_url() . '/?r=teaching/teach-class', 'text' => __('Teach Class', 'iii-dictionary')),
			'request-payment' => array('url' => locale_home_url() . '/?r=teaching/request-payment', 'text' => __('Request Payment', 'iii-dictionary'))
		),
		'active' => $active_tab
	);
	//TAB FOR MATH IKLEARN
	$math_tab_options = array(
		'items' => array(
			'register' => array('url' => locale_home_url() . '/?r=teaching/register', 'text' => __('Register', 'iii-dictionary')),
			'tutor-math' => array('url' => locale_home_url() . '/?r=teaching/tutor-math', 'text' => __('Tutor Math', 'iii-dictionary')),
			'request-payment' => array('url' => locale_home_url() . '/?r=teaching/request-payment', 'text' => __('Request Payment', 'iii-dictionary'))
		),
		'active' => $active_tab
	);
	$current_user = wp_get_current_user();

	switch($active_tab)
	{
		// Register Tab
		case 'register':
			$tab_title = __('Registration Agreement', 'iii-dictionary');
			$tab_info_url = get_info_tab_cloud_url('Popup_info_25.jpg');

			// teacher register
			if(isset($_POST['i-agree']) || isset($_POST['update-teacher'])) {
				if(MWDB::update_user($current_user, $is_math_panel)) {
					wp_redirect(locale_home_url() . '/?r=teaching');
					exit;
				}
			}

			// teacher agreed to the new agreement
			if(isset($_POST['agree-new-agreement'])) {
				$agreement_update_date = mw_get_option('agreement-update-date');
				update_user_meta($current_user->ID, 'teacher_agreement_ver', $agreement_update_date);
				wp_redirect(locale_home_url() . '/?r=teaching');
				exit;
			}
			// math teacher agreed to the new agreement
			if(isset($_POST['agree-new-agreement-math'])) {
				$agreement_update_date = mw_get_option('agreement-update-date');
				update_user_meta($current_user->ID, 'math_teacher_agreement_ver', $agreement_update_date);
				wp_redirect(locale_home_url() . '/?r=teaching');
				exit;
			}

			break;
		// Sell Worksheet Tab
		case 'sell-worksheet':
			$tab_title = __('List of worksheet offered for sale', 'iii-dictionary');
			$tab_info_url = get_info_tab_cloud_url('Popup_info_26.jpg');

			// remove an offer
			if(isset($_POST['remove-offer'])) {
				if(MWDB::remove_offered_worksheet($_POST['cid'])) {
					wp_redirect(locale_home_url() . '/?r=teaching/sell-worksheet');
					exit;
				}
			}

			// offer a worksheet
			if(isset($_POST['offer-worksheet']))
			{
				$data['sid'] = $_POST['sid'];
				$data['user_id'] = $current_user->ID;
				$data['offered_price'] = $_POST['offer-price'];

				// check if user has subscribed Teacher's tools
				if(is_homework_tools_subscribed()) {
					if(MWDB::offer_worksheet($data)) {
						wp_redirect(locale_home_url() . '/?r=teaching/sell-worksheet');
						exit;
					}
				}
				else {
					ik_enqueue_messages(
						__('You need to subscribe Homework Tool in order to offer Worksheet.', 'iii-dictionary')
						. '<br><br>
						<div class="row">
							<div class="col-sm-6 form-group">
								<a href="' . locale_home_url() . '/?r=manage-subscription#1" class="btn btn-default btn-block orange"><span class="icon-check"></span>Subscribe</a>
							</div>
							<div class="col-sm-6 form-group">
								<a href="#" data-dismiss="modal" aria-hidden="true" class="btn btn-default btn-block grey"><span class="icon-cancel"></span>No</a>
							</div>
						</div>'
					, 'error');
				}
			};

			$page_filter = get_page_filter_session();

			// offered worksheets list
			$current_page = max( 1, get_query_var('page'));
			$filter = $page_filter['filter1'];
			if(empty($filter))
			{
				$filter['items_per_page'] = 20;
				$filter['offset'] = $filter['items_per_page'] * ($current_page - 1);
				$filter['user_id'] = $current_user->ID;
				$filter['user_offer'] = true;
			}
			else {
				if(isset($_POST['filter']['search']))
				{
					$filter['grade'] = $_POST['filter']['grade'];
					$filter['assignment-id'] = $_POST['filter']['assignment-id'];
				}
				$filter['offset'] = $filter['items_per_page'] * ($current_page - 1);
				
			}

			$page_filter['filter1'] = $filter;
			$offered_list = MWDB::get_worksheet_offering('offered', $filter, $filter['offset'], $filter['items_per_page']);
			$total_pages = ceil($offered_list->total / $filter['items_per_page']);

			$pagination = paginate_links(array(
				'format' => '?page=%#%',
				'current' =>  $current_page,
				'total' => $total_pages
			));

			// to offer worksheets list
			$current_page2 = max( 1, get_query_var('page2'));
			$filter2 = $page_filter['filter2'];
			if(empty($filter2))
			{
				$filter2['items_per_page'] = 20;
				$filter2['offset'] = $filter2['items_per_page'] * ($current_page2 - 1);
				$filter2['user_id'] = $current_user->ID;
			}
			else {
				if(isset($_POST['filter2']['search']))
				{
					$filter2['grade'] = $_POST['filter2']['grade'];
					$filter2['assignment-id'] = $_POST['filter2']['assignment-id'];
				}
				$filter2['offset'] = $filter2['items_per_page'] * ($current_page2 - 1);
			}

			$page_filter['filter2'] = $filter2;
			$to_offer_list = MWDB::get_worksheet_offering('to_offer', $filter2, $filter2['offset'], $filter2['items_per_page']);
			$total_pages2 = ceil($to_offer_list->total / $filter2['items_per_page']);

			$pagination2 = paginate_links( array(
				'format' => '?page2=%#%',
				'current' =>  $current_page2,
				'total' => $total_pages2
			) );

			set_page_filter_session($page_filter);

			break;
		// Purchase Worksheet Tab
		case 'purchase-worksheet':
			$tab_title = __('Homework you can add to your library', 'iii-dictionary');
			$tab_info_url = get_info_tab_cloud_url('Popup_info_27.jpg');

			if(isset($_POST['purchase-worksheet'])) {
				if(ik_purchase_worksheet($_POST['cid'])) {
					wp_redirect(locale_home_url() . '/?r=teaching/purchase-worksheet');
					exit;
				}
			}

			$current_page = max( 1, get_query_var('page'));
			$filter = get_page_filter_session();
			if(empty($filter))
			{
				$filter['items_per_page'] = 20;
				$filter['offset'] = $filter['items_per_page'] * ($current_page - 1);
				$filter['user_id'] = $current_user->ID;
			}
			else {
				if(isset($_POST['filter']['search']))
				{
					$filter['grade'] = $_POST['filter']['grade'];
					$filter['assignment-id'] = $_POST['filter']['assignment-id'];
				}
				$filter['offset'] = $filter['items_per_page'] * ($current_page - 1);
			}

			set_page_filter_session($filter);
			$offered_list = MWDB::get_worksheet_offering('offered', $filter, $filter['offset'], $filter['items_per_page']);
			$total_pages = ceil($offered_list->total / $filter['items_per_page']);

			$pagination = paginate_links(array(
				'format' => '?page=%#%',
				'current' =>  $current_page,
				'total' => $total_pages
			));

			break;
		// Teach Class Tab
		case 'teach-class':
			$tab_title = __('Follow the steps to teach classes', 'iii-dictionary');
			$tab_info_url = get_info_tab_cloud_url('Popup_info_28.jpg');

			$is_teaching_agreement_agreed = ik_is_teaching_agreement_agreed();
			$teacher_test_score_threshold = mw_get_option('teacher-test-score-threshold');
			$filter['group_id'] = mw_get_option('teacher-test-group');
		
			$filter['check_result'] = true;
			$filter['user_id_result'] = $current_user->ID;

			$tests = MWDB::get_homework_assignments($filter);
			
			// make sure second test is writing test
			if($tests->items[0]->assignment_id == ASSIGNMENT_WRITING) {
				$tmp = $tests->items[0];
				$tests->items[0] = $tests->items[1];
				$tests->items[1] = $tmp;
			}

			if(!empty($tests->items)) {
				$test1_result = MWDB::get_homework_results($tests->items[0]->id, $current_user->ID);
				$test2_result = MWDB::get_homework_results($tests->items[1]->id, $current_user->ID);
			}
	
			if(isset($_POST['take-test1']) || isset($_POST['take-test2'])) {
				MWDB::join_group($filter['group_id']); ik_clear_message_queue();
				
				if(!empty($tests->items)) {
					// store teacher tests in the session so we can check later
					$_SESSION['teacher_tests'] = array($tests->items[0]->sheet_id, $tests->items[1]->sheet_id);

					if(isset($_POST['take-test1'])) {
						$test_url = MWHtml::get_practice_page_url($tests->items[0]->assignment_id) .'&mode=homework&sid=' . $tests->items[0]->sheet_id;
					}
					if(isset($_POST['take-test2'])) {
						$test_url = MWHtml::get_practice_page_url($tests->items[1]->assignment_id) .'&mode=homework&sid=' . $tests->items[1]->sheet_id;
					}

					if(isset($test_url)) {
						wp_redirect($test_url);
						exit;
					}
				}
			}

			// teacher want to re take test 1
			if(isset($_POST['re-take-test1'])) {
				MWDB::delete_homework_result($test1_result[0]->homework_result_id);

				$test_url = MWHtml::get_practice_page_url($tests->items[0]->assignment_id) .'&mode=homework&sid=' . $tests->items[0]->sheet_id;
				wp_redirect($test_url);
				exit;
			}

			// teacher want to re take test 2
			if(isset($_POST['re-take-test2'])) {
				MWDB::delete_homework_result($test2_result[0]->homework_result_id);

				$test_url = MWHtml::get_practice_page_url($tests->items[1]->assignment_id) .'&mode=homework&sid=' . $tests->items[1]->sheet_id;
				wp_redirect($test_url);
				exit;
			}

			// agree teaching agreement
			if(isset($_POST['i-agree'])) {
				ik_agree_teaching_agreement();

				wp_redirect(locale_home_url() . '/?r=teaching/teach-class');
				exit;
			}

			// accept grading request
			if(isset($_POST['accept-request'])) {
				$request = MWDB::get_worksheet_grading_request($_POST['request-id']);

				$_SESSION['grading_homework'] = array('hrid' => $request->homework_result_id, 'req_id' => $_POST['request-id']);
                                 
                                 
                                $roles = array("r-teacher","q-teacher","mr-teacher","mq-teacher");

                                $subject_email = 'Email notice for the student (When the tutoring is completed)';
                                
                                $message = '<p>Your tutoring is completed.</p></br>';
                               $message .= '<p>(Please check the teachers’ evaluation for the English writing at <a href="http://'.$_SERVER["SERVER_NAME"].'">'.$_SERVER["SERVER_NAME"].'</a>)</p>';
                                $message .= '<p>The site include the “Teacher evaluation Box” Please feel free to evaluate your tutor and write comment in the box.</p>.<br />.<br />';
                                $message .= '<p>Support, iklearn.com.<br /><br />.</p>';
                                $message .= '<p>For math tutoring summary, display the time spent and point used, and the teacher evaluation box with the title “Please write your evaluation of this tutor.”.</p>';
                                $message .= '<p>We have English writing panel, but we do not have the panel for Math tutoring summary. We need to design one. This pop-up panel open when student open “Complete” in the subscription  are of Student box.</p><br />';
                                $message .= '<p>Here is "Student"s Notification Email Text"</p>';
                               

                                $headers = array('Content-Type: text/html; charset=UTF-8');

                                $teaches = MWDB::get_users_with_role($roles);
                                if(count($teaches) > 0){
                                    foreach ($teaches as $key => $value) {
                                        wp_mail($value->user_email, $subject_email, $message, $headers);
                                    }
                                }
				wp_redirect(locale_home_url() . '/?r=grade-homework&hrid=' . $request->homework_result_id);
				exit;
			}

			$current_page = max( 1, get_query_var('page'));
			$filter = get_page_filter_session();
			if(empty($filter))
			{
				$filter['items_per_page'] = 30;
				$filter['offset'] = $filter['items_per_page'] * ($current_page - 1);
			}
			else {
				if(isset($_POST['filter']['search']))
				{
					$filter['grade'] = $_POST['filter']['grade'];
				}
				$filter['offset'] = $filter['items_per_page'] * ($current_page - 1);
			}

			set_page_filter_session($filter);
			$filter['offset'] = 0;
			$filter['items_per_page'] = 99999999;
			$grading_requests = MWDB::get_worksheet_grading_requests($filter, $filter['offset'], $filter['items_per_page']);
			$total_pages = ceil($grading_requests->total / $filter['items_per_page']);

			$pagination = paginate_links(array(
				'format' => '?page=%#%',
				'current' =>  $current_page,
				'total' => $total_pages
			));

			break;
		// Request Payment Tab
		case 'request-payment':
			$tab_title = __('My Earnings', 'iii-dictionary');
			$tab_info_url = get_info_tab_cloud_url('Popup_info_29.jpg');
			$current_user_points = ik_get_user_points($current_user->ID);
			$point_ex_rate = mw_get_option('point-exchange-rate');

			// user want to request payment
			if(isset($_POST['request-payment']))
			{
				// validate data
				$form_valid = true;
				if(empty($_POST['receiving-method'])) {
					ik_enqueue_messages(__('Please choose a Receiving method.', 'iii-dictionary'), 'error');
					$form_valid = false;
				}

				if(empty($_POST['amount-request']) || !is_numeric($_POST['amount-request']) || $_POST['amount-request'] < 1) {
					ik_enqueue_messages(__('Amount requested is invalid.', 'iii-dictionary'), 'error');
					$form_valid = false;
				}
				else {
					$amount = $_POST['amount-request'] * 100 / $point_ex_rate;
					if ($current_user_points < $amount) {
						ik_enqueue_messages(__('You don\'t have enough points for this request.', 'iii-dictionary'), 'notice');
						$form_valid = false;
					}
				}

				if(empty($_POST['receiving-email']) || !is_email($_POST['receiving-email'])) {
					ik_enqueue_messages(__('Email for receiving payment is invalid.', 'iii-dictionary'), 'error');
					$form_valid = false;
				}

				if($form_valid) {
					$data = array(
						'requested_by' => $current_user->ID,
						'receiving_method_id' => $_POST['receiving-method'],
						'status_id' => TEACHER_REQ_PENDING,
						'amount' => $amount,
						'receiving_email' => $_POST['receiving-email'],
						'requested_on' => date('Y-m-d H:i:s', time())
					);

					if(ik_deduct_user_points($amount, $current_user->ID)) {
						if(MWDB::store_payment_request($data)) {
							ik_enqueue_messages(__('Your payment request has been sent.', 'iii-dictionary'), 'success');
							wp_redirect(locale_home_url() . '/?r=teaching/request-payment');
							exit;
						}
						else {
							ik_enqueue_messages(__('An error occured, cannot request payment.', 'iii-dictionary'), 'error');
						}
					}
					else {
						ik_enqueue_messages(__('You don\'t have enough points for this request.', 'iii-dictionary'), 'error');
					}
				}
			}

			$receiving_methods = MWDB::get_payment_receiving_methods();

			$current_page = max( 1, get_query_var('page'));
			$filter = get_page_filter_session();
			if(empty($filter) && !isset($_POST['filter']))
			{
				$filter['items_per_page'] = 20;
				$filter['offset'] = $filter['items_per_page'] * ($current_page - 1);
			}
			else {
				if(isset($_POST['filter']['search']))
				{
					$filter['grade'] = $_POST['filter']['grade'];
				}
				$filter['offset'] = $filter['items_per_page'] * ($current_page - 1);
			}

			set_page_filter_session($filter);
			$user_transactions = MWDB::get_user_point_transactions($filter, $filter['offset'], $filter['items_per_page']);
			$total_pages = ceil($user_transactions->total / $filter['items_per_page']);

			$pagination = paginate_links(array(
				'format' => '?page=%#%',
				'current' =>  $current_page,
				'total' => $total_pages
			));

			break;
		case 'tutor-math' : 
			
			$tab_title = __('Follow the steps to teach classes', 'iii-dictionary');
			$tab_info_url = get_info_tab_cloud_url('//');
			$is_teaching_agreement_agreed_math = ik_is_teaching_agreement_agreed(math);
			$teacher_math_test_score_threshold = mw_get_option('teacher-math-test-score-threshold');
			
			$math_filter['group_id'] = mw_get_option('teacher-math-test-group');
			$math_filter['check_result'] = true;
			$math_filter['user_id_result'] = $current_user->ID;

			$math_tests = MWDB::get_homework_assignments($math_filter);
			if(!empty($math_tests->items)) {
				$math_test1_result = MWDB::get_homework_results($math_tests->items[0]->id, $current_user->ID);
				$math_test2_result = MWDB::get_homework_results($math_tests->items[1]->id, $current_user->ID);
			}

			if(isset($_POST['take-math-test1']) || isset($_POST['take-math-test2'])) {
				MWDB::join_group($math_filter['group_id']); ik_clear_message_queue();
				
				if(!empty($math_tests->items)) {
					// store teacher tests in the session so we can check later
					$_SESSION['teacher_math_tests'] = array($math_tests->items[0]->id, $math_tests->items[1]->id);

					if(isset($_POST['take-math-test1'])) {
						
						$math_test_url = MWHtml::get_practice_page_url($math_tests->items[0]->assignment_id) .'&mode=homework&hid=' . $math_tests->items[0]->id;
					}
					if(isset($_POST['take-math-test2'])) {
						$math_test_url = MWHtml::get_practice_page_url($math_tests->items[1]->assignment_id) .'&mode=homework&hid=' . $math_tests->items[1]->id;
					}

					if(isset($math_test_url)) {
						wp_redirect($math_test_url);
						exit;
					}
				}
			}
			
			// teacher want to re take test 1
			if(isset($_POST['re-take-math-test1'])) {
				MWDB::delete_homework_result($math_test1_result[0]->homework_result_id);

				$math_test_url = MWHtml::get_practice_page_url($math_tests->items[0]->assignment_id) .'&mode=homework&hid=' . $math_tests->items[0]->id;
				wp_redirect($math_test_url);
				exit;
			}

			// teacher want to re take test 2
			if(isset($_POST['re-take-math-test2'])) {
				MWDB::delete_homework_result($math_test2_result[0]->homework_result_id);

				$math_test_url = MWHtml::get_practice_page_url($math_tests->items[1]->assignment_id) .'&mode=homework&hid=' . $math_tests->items[1]->id;
				wp_redirect($math_test_url);
				exit;
			}
			
			// agree teaching agreement
			if(isset($_POST['i-agree-math'])) {
				ik_agree_teaching_agreement(math);

				wp_redirect(locale_home_url() . '/?r=teaching/tutor-math');
				exit;
			}
			
			$current_page = max( 1, get_query_var('page'));
			$filter = get_page_filter_session();
			if(empty($filter) && !isset($_POST['filter']))
			{
				$filter['items_per_page'] = 20;
				$filter['offset'] = $filter['items_per_page'] * ($current_page - 1);
			}
			else {
				if(isset($_POST['filter']['search']))
				{
					$filter['grade'] = $_POST['filter']['grade'];
				}
				$filter['offset'] = $filter['items_per_page'] * ($current_page - 1);
			}

			set_page_filter_session($filter);
			
			
			$filter['offset'] = 0;
			$filter['items_per_page'] = 99999999;
			$chat_requests = MWDB::get_chat_session_requests($filter, $filter['offset'], $filter['items_per_page']);
			$total_pages = ceil($chat_requests->total / $filter['items_per_page']);

			$pagination = paginate_links(array(
				'format' => '?page=%#%',
				'current' =>  $current_page,
				'total' => $total_pages
			));
			
			
		break;
	}

	$is_mw_registered_teacher = (!$is_math_panel) ? is_mw_registered_teacher() : is_mw_registered_teacher(null, 1);
	$is_teaching_agreement_uptodate = ik_is_teacher_agreement_uptodate();
	$is_teaching_agreement_uptodate_math = ik_is_teacher_agreement_uptodate('MATH');

	if((!$is_mw_registered_teacher || (!$is_teaching_agreement_uptodate && !$is_math_panel) || (!$is_teaching_agreement_uptodate_math && $is_math_panel)) && $active_tab != 'register') {
		if(!$is_mw_registered_teacher) {
			$title = __('Registration Required', 'iii-dictionary');
			$body = __('Please register as Teacher in order to access this page.', 'iii-dictionary');
		}
		
		if((!$is_teaching_agreement_uptodate && !$is_math_panel) || (!$is_teaching_agreement_uptodate_math && $is_math_panel) ) {
			$title = __('Agreement Updated', 'iii-dictionary');
			$body = __('Our Agreement is updated, please agree to the new Agreement in order to continue.', 'iii-dictionary');
		}
		$return_url = locale_home_url() . '/?r=teaching/register';

		set_lockpage_dialog($title, $body, $return_url);
	}
	
	
?>
<?php 
	if(!$is_math_panel) : 
		get_dict_header($_page_title);
		get_dict_page_title($_page_title, '', '', $tab_options, $tab_info_url);
	else :
		get_math_header($_page_title, 'red-brown');
		get_dict_page_title($_page_title, '', '', $math_tab_options);
	endif
?>

	<form action="<?php echo locale_home_url() . '/?r=teaching/' . $active_tab ?>" method="post" id="main-form" enctype="multipart/form-data">
		<div class="row">
			<div class="col-sm-12">
				<h2 class="title-border"><?php echo $tab_title ?></h2>
			</div>

			<?php switch($active_tab) : 
				case 'register': ?>
				<?php if(!$is_math_panel) : ?>
					<div class="col-sm-12">
						<div class="form-group">
							<div class="box box-red">
								<div class="scroll-list" style="max-height: 200px; color: #fff">
									<?php echo mw_get_option('registration-agreement') ?>
								</div>
							</div>
						</div>
					</div>
				<?php if($is_mw_registered_teacher) : ?>
					<div class="col-sm-6 col-sm-offset-6">
						<div class="form-group">
							<button type="submit" name="agree-new-agreement" class="btn btn-default btn-block orange form-control"<?php echo $is_teaching_agreement_uptodate ? ' disabled' : '' ?>><?php _e('I AGREE', 'iii-dictionary') ?></button>
						</div>
					</div>
				<?php endif ?>
				<?php else : ?>
					<div class="col-sm-12">
						<div class="form-group">
							<div class="box box-red">
								<div class="scroll-list" style="max-height: 200px; color: #fff">
									<?php echo mw_get_option('math-registration-agreement') ?>
								</div>
							</div>
						</div>
					</div>
				<?php if($is_mw_registered_teacher) : ?>
					<div class="col-sm-6 col-sm-offset-6">
						<div class="form-group">
							<button type="submit" name="agree-new-agreement-math" class="btn btn-default btn-block orange form-control"<?php echo $is_teaching_agreement_uptodate_math ? ' disabled' : '' ?>><?php _e('I AGREE', 'iii-dictionary') ?></button>
						</div>
					</div>
				<?php endif ?>
				<?php endif ?>

				<?php if(is_user_logged_in()) : ?>
					<div class="col-sm-12">
						<div class="form-group">
							<table class="table profile-picture-form">
								<tr>
									<td class="profile-picture">
										<div id="profile-picture">
											<?php 
												$user_avatar = ik_get_user_avatar($current_user->ID);
												  if(!empty($user_avatar)) : ?>
													<img src="<?php echo $user_avatar ?>" width="100" height="100" alt="<?php echo $current_user->display_name ?>">
											<?php else :
													echo get_avatar($current_user->ID, 120);
												  endif ?>
										</div>
									</td>
									<td class="upload-block">
										<label><?php _e('Profile Picture', 'iii-dictionary') ?></label><br>
										<span class="btn btn-default grey btn-file">
											<span class="icon-browse"></span><?php _e('Browse', 'iii-dictionary') ?> <input name="input-image" id="input-image" type="file">
										</span>
									</td>
								</tr>
							</table>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group">
							<label><?php _e('First name', 'iii-dictionary') ?></label>
							<input type="text" class="form-control" name="first-name" value="<?php echo get_user_meta($current_user->ID, 'first_name', true) ?>" required>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group">
							<label><?php _e('Last name', 'iii-dictionary') ?></label>
							<input type="text" class="form-control" name="last-name" value="<?php echo get_user_meta($current_user->ID, 'last_name', true) ?>" required>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group">
							<label><?php _e('Mobile phone number', 'iii-dictionary') ?></label>
							<input type="text" class="form-control" name="mobile-number" value="<?php echo get_user_meta($current_user->ID, 'mobile_number', true) ?>" required>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group">
							<label><?php _e('Driver\'s license number', 'iii-dictionary') ?></label>
							<input type="text" class="form-control" name="driver-license" value="<?php echo get_user_meta($current_user->ID, 'driver_license', true) ?>" required>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group">
							<label><?php _e('Social security number (last 4 digit)', 'iii-dictionary') ?></label>
							<input type="text" class="form-control" name="security-number" value="<?php echo get_user_meta($current_user->ID, 'security_number', true) ?>" required>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group">
							<label><?php _e('Latest school you tought', 'iii-dictionary') ?></label>
							<input type="text" class="form-control" name="previous-school" value="<?php echo get_user_meta($current_user->ID, 'previous_school', true) ?>" required>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group">
							<label><?php _e('Email address', 'iii-dictionary') ?></label>
							<input type="email" class="form-control" name="new-email" value="<?php echo $current_user->user_email ?>" required>
						</div>
					</div>
					<div class="col-xs-2 col-sm-1">
						<div class="form-group">
							<label>&nbsp;</label>
							<div class="radio radio-style1">
								<input id="rdo-agreed" type="radio" name="registered-teacher" value="1"<?php echo $is_mw_registered_teacher ? ' checked' : '' ?>>
								<label for="rdo-agreed"></label>
							</div>
						</div>
					</div>
					<div class="col-xs-10 col-sm-5">
						<div class="form-group">
							<label>&nbsp;</label>
							<?php if($is_mw_registered_teacher) : ?>
								<button type="submit" class="btn btn-default btn-block orange form-control" name="update-teacher"><span class="icon-check"></span> <?php _e('UPDATE', 'iii-dictionary') ?></button>
							<?php else : ?>
								<button type="submit" class="btn btn-default btn-block orange form-control" name="i-agree" id="i-agree"><span class="icon-check"></span> <?php _e('I AGREE', 'iii-dictionary') ?></button>
							<?php endif ?>
						</div>
					</div>
				<?php endif ?>

			<?php break;
				case 'sell-worksheet': ?>

				<div class="col-sm-12">
					<div class="box box-sapphire">
						<div class="row box-header">
							<div class="col-xs-12">
								<div class="row search-tools">
									<div class="col-xs-6 col-sm-3">
										<div class="form-group">
											<?php MWHtml::select_grades('ENGLISH', $filter['grade'], array('class' => 'select-sapphire form-control', 'name' => 'filter[grade]')) ?>
										</div>
									</div>
									<div class="col-xs-6 col-sm-3">
										<div class="form-group">
											<?php MWHtml::sel_assignments($filter['assignment-id'], false, array(), '-Assignment-', 'filter[assignment-id]', 'select-sapphire form-control', 'filter-assignment2', false) ?>
										</div>
									</div>
									<div class="col-xs-12 col-sm-3 col-sm-offset-3">
										<div class="form-group">
											<button type="submit" class="btn btn-default sky-blue form-control" name="filter[search]"><?php _e('Search', 'iii-dictionary') ?></button>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-12">
								<table class="table table-striped table-condensed ik-table1 text-center" id="list-sheets">
									<thead>
										<tr>
											<th class="hidden-xs"><?php _e('Assignment', 'iii-dictionary') ?></th>
											<th class="hidden-xs"><?php _e('Grade', 'iii-dictionary') ?></th>
											<th><?php _e('Sheet Name', 'iii-dictionary') ?></th>
											<th class="hidden-xs"><?php _e('Dictionary', 'iii-dictionary') ?></th>
											<th><?php _e('Price', 'iii-dictionary') ?></th>
											<th><?php _e('Offered Date', 'iii-dictionary') ?></th>
											<th></th>
										</tr>
									</thead>
									<tfoot>
										<tr><td colspan="7"><?php echo $pagination ?></td></tr>
									</tfoot>
									<tbody><?php if(empty($offered_list->items)) : ?>
										<tr><td colspan="7"><?php _e('You haven\'t offered any worksheets yet.', 'iii-dictionary') ?></td></tr>
										<?php else :
											foreach($offered_list->items as $item) : ?>
												<tr>
													<td class="hidden-xs"><?php echo $item->assignment ?></td>
													<td class="hidden-xs"><?php echo $item->grade ?></td>
													<td><?php echo $item->sheet_name ?></td>
													<td class="hidden-xs"><?php echo $item->dictionary_name ?></td>
													<td><?php printf(__('%s pts', 'iii-dictionary'), $item->offered_price) ?></td>
													<td><?php echo ik_date_format($item->offered_on) ?></td>
													<td><button type="submit" name="remove-offer" class="btn btn-default btn-block btn-tiny grey remove-offer" data-oid="<?php echo $item->offer_id ?>"><?php _e('Remove', 'iii-dictionary') ?></button></td>
												</tr>
										<?php endforeach; endif ?>
									</tbody>
								</table>
								<input type="hidden" name="cid" id="rsid">
							</div>
						</div>
					</div>
				</div>

				<div class="col-sm-12">
					<h2 class="title-border"><?php _e('Offer worksheet to other teachers', 'iii-dictionary') ?></h2>
				</div>
				<div class="col-sm-12">
					<div class="box box-sapphire">
						<div class="row box-header">
							<div class="col-xs-12">
								<div class="row search-tools">
									<div class="col-xs-6 col-sm-3">
										<div class="form-group">
											<?php MWHtml::select_grades('ENGLISH', $filter2['grade'], array('class' => 'select-sapphire form-control', 'name' => 'filter2[grade]')) ?>
										</div>
									</div>
									<div class="col-xs-6 col-sm-3">
										<div class="form-group">
											<?php MWHtml::sel_assignments($filter2['assignment-id'], false, array(), '-Assignment-', 'filter2[assignment-id]', 'select-sapphire form-control', 'filter-assignment2', false) ?>
										</div>
									</div>
									<div class="col-xs-12 col-sm-3 col-sm-offset-3">
										<div class="form-group">
											<button type="submit" class="btn btn-default sky-blue form-control" name="filter2[search]"><?php _e('Search', 'iii-dictionary') ?></button>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-12">
								<table class="table table-striped table-condensed ik-table1 text-center">
									<thead>
										<tr>
											<th class="hidden-xs"><?php _e('Assignment', 'iii-dictionary') ?></th>
											<th class="hidden-xs"><?php _e('Grade', 'iii-dictionary') ?></th>
											<th><?php _e('Sheet Name', 'iii-dictionary') ?></th>
											<th class="hidden-xs"><?php _e('Dictionary', 'iii-dictionary') ?></th>
											<th></th>
										</tr>
									</thead>
									<tfoot>
										<tr><td colspan="5"><?php echo $pagination2 ?></td></tr>
									</tfoot>
									<tbody><?php if(empty($to_offer_list->items)) : ?>
										<tr><td colspan="5"><?php _e('You don\'t have any worksheet to offer.', 'iii-dictionary') ?></td></tr>
										<?php else :
											foreach($to_offer_list->items as $item) : ?>
											<tr>
												<td class="hidden-xs"><?php echo $item->assignment ?></td>
												<td class="hidden-xs"><?php echo $item->grade ?></td>
												<td><?php echo $item->sheet_name ?></td>
												<td class="hidden-xs"><?php echo $item->dictionary_name ?></td>
												<td><button type="button" class="btn btn-default btn-block btn-tiny grey offer-worksheet" data-sid="<?php echo $item->id ?>"><?php _e('Offer', 'iii-dictionary') ?></button></td>
											</tr>
										<?php endforeach; endif ?>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>

<div class="modal fade modal-red-brown" id="offer-worksheet-modal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" style="width: 680px;">
	<div class="modal-content">
	  <div class="modal-header">
		<a href="#" data-dismiss="modal" aria-hidden="true" class="close"></a>
		<h3 class="modal-title" id="myModalLabel"><?php _e('Offer Worksheet', 'iii-dictionary') ?></h3>
	  </div>
	  <div class="modal-body">
		<input type="hidden" name="sid" id="osid">
		<div class="row">
			<div class="col-sm-6">
				<div class="form-group">
					<label><?php printf(__('Offer Price <small>(Min: 0 - Max: %s)</small>', 'iii-dictionary'), mw_get_option('teacher-max-point')) ?></label>
					<input type="number" class="form-control" id="offer-price" name="offer-price" min="0" max="<?php echo mw_get_option('teacher-max-point') ?>">
				</div>
			</div>
			<div class="clearfix"></div>
			<div class="col-sm-6">
				<div class="form-group">
					<button type="submit" name="offer-worksheet" class="btn btn-default btn-block orange form-control"><span class="icon-check"></span> <?php _e('Offer', 'iii-dictionary') ?></button>
				</div>
			</div>
			<div class="col-sm-6">
				<div class="form-group">
					<button type="button" data-dismiss="modal" class="btn btn-default btn-block grey form-control"><span class="icon-cancel"></span> <?php _e('Cancel', 'iii-dictionary') ?></button>
				</div>
			</div>
		</div>
	  </div>
	</div>
  </div>
</div>

			<?php break; 
				case 'purchase-worksheet': ?>

				<div class="col-sm-12">
					<div class="box box-sapphire">
						<div class="row box-header">
							<div class="col-xs-12"  style="text-align: right">
								<?php $remaining_pts = get_user_meta($current_user->ID, 'user_points', true); ?>
								<h4 style="color: #0099c1"><span style="color: #0099c1"><u><?php _e('Remaining points:', 'iii-dictionary') ?></u> <?php echo empty($remaining_pts) ? 0 : $remaining_pts ?></span></h4>
								<?php if(empty($remaining_pts) || $remaining_pts == 0) : ?>
									<a href="#purchase-points-dialog" class="btn btn-default btn-tiny grey" data-toggle="modal" title="Click here to purchase points"><?php _e('Purchase points?', 'iii-dictionary') ?></a>
								<?php endif ?>
							</div>
							<div class="col-xs-12">
								<div class="row search-tools">
									<div class="col-xs-6 col-sm-3">
										<div class="form-group">
											<?php MWHtml::select_grades('ENGLISH', $filter['grade'], array('class' => 'select-sapphire form-control', 'name' => 'filter[grade]')) ?>
											
										</div>
									</div>
									<div class="col-xs-6 col-sm-3">
										<div class="form-group">
											<?php MWHtml::sel_assignments($filter['assignment-id'], false, array(), '-Assignment-', 'filter[assignment-id]', 'select-sapphire form-control', 'filter-assignment2', false) ?>
										</div>
									</div>
									<div class="col-xs-12 col-sm-3 col-sm-offset-3">
										<div class="form-group">
											<button type="submit" class="btn btn-default sky-blue form-control" name="filter[search]"><?php _e('Search', 'iii-dictionary') ?></button>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-12">
								<div class="scroll-list2" style="max-height: 600px">
									<table class="table table-striped table-condensed ik-table1 vertical-middle text-center" id="list-sheets">
										<thead>
											<tr>
												<th class="hidden-xs"><?php _e('Assignment', 'iii-dictionary') ?></th>
												<th class="hidden-xs"><?php _e('Grade', 'iii-dictionary') ?></th>
												<th><?php _e('Sheet Name', 'iii-dictionary') ?></th>
												<th class="hidden-xs"><?php _e('Creator', 'iii-dictionary') ?></th>
												<th><?php _e('Price', 'iii-dictionary') ?></th>
												<th></th>
											</tr>
										</thead>
										<tfoot>
											<tr><td colspan="7"><?php echo $pagination ?></td></tr>
										</tfoot>
										<tbody><?php if(empty($offered_list->items)) : ?>
											<tr><td colspan="7"><?php _e('No results', 'iii-dictionary') ?></td></tr>
											<?php else :
													foreach($offered_list->items as $item) : ?>
														<tr data-id="<?php echo $item->id ?>" data-assignment="<?php echo $item->assignment_id ?>">
															<td class="hidden-xs"><?php echo $item->assignment ?></td>
															<td class="hidden-xs"><?php echo $item->grade ?></td>
															<td><?php echo $item->sheet_name ?></td>
															<td class="hidden-xs"><?php
																if(is_mw_admin($item->created_by) || is_mw_super_admin($item->created_by)) : ?>
																	<?php _e('SAT Prep.', 'iii-dictionary') ?>
															<?php else : 
																	echo $item->creator;
																endif ?>
															</td>
															<td><?php printf(__('%s pts', 'iii-dictionary'), ik_calc_offer_worksheet_price($item->offered_price)) ?></td>
															
															<td><?php if($item->assignment_id != ASSIGNMENT_SPELLING) : ?>
																	<button type="button" class="btn btn-default btn-block btn-tiny grey preview-btn"><?php _e('Preview', 'iii-dictionary') ?></button>
																<?php endif ?>
																<button type="button" class="btn btn-default btn-block btn-tiny grey worksheet-des"><?php _e('Details', 'iii-dictionary') ?></button>
																<?php if($current_user->ID != $item->created_by) : ?>
																	<button type="button" class="btn btn-default btn-block btn-tiny grey purchase-worksheet" data-oid="<?php echo $item->offer_id ?>" data-pid="<?php echo $item->purchased_offer_id ?>"><?php _e('Purchase', 'iii-dictionary') ?></button>
																	<div class="hidden" id="ws-des-<?php echo $item->id ?>"><?php echo $item->description ?></div>
																<?php endif ?>
															</td>
														</tr>
												<?php endforeach;
											endif ?>
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>

<div class="modal fade modal-green" id="homework-viewer-modal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <a href="#" data-dismiss="modal" aria-hidden="true" class="close"></a>
        <h3 class="modal-title"><?php _e('Homework Viewer', 'iii-dictionary') ?> <span><span id="homework-detail"></span> <span id="question-i">1</span></span></h3>
      </div>
      <div class="modal-body green">
		<div class="row">
			<div class="col-sm-12" id="quiz-box">
				<span id="quiz"></span>
			</div>
			<div class="col-sm-12" style="display: none" id="passage-block">
				<div class="form-group">
					<label><?php _e('Passage', 'iii-dictionary') ?></label>
					<div id="reading-passage-box" class="scroll-list" style="max-height: 200px">
						<div id="reading-passage"></div>
					</div>
				</div>
			</div>
			<div class="col-sm-12">
				<ul class="select-box multi-choice" id="question-box" data-placement="top" data-trigger="focus">
					<li class="vocab-keyword" id="vocab-question"></li>
					<li><a class="answer"><span class="box-letter">A</span> <span id="answer-a" class="ac"></span></a></li>
					<li><a class="answer"><span class="box-letter">B</span> <span id="answer-b" class="ac"></span></a></li>
					<li><a class="answer"><span class="box-letter">C</span> <span id="answer-c" class="ac"></span></a></li>
				</ul>
				<div class="box box-green" id="writing-subject-block" style="display: none; margin: 20px 0"><div class="scroll-list" style="max-height: 250px"><div id="writing-subject"></div></div></div>
			</div>
			<div class="col-sm-12">
				<div class="form-group">
					<button type="button" id="next-btn" class="btn btn-default btn-block sky-blue"><span class="icon-next"></span><?php _e('Next', 'iii-dictionary') ?></button>
				</div>
			</div>
		</div>
		<input type="hidden" id="current-row" value="1">
		<input type="hidden" id="current-assignment" value="">
	  </div>
    </div>
  </div>
</div>

<div class="modal fade modal-red-brown" id="purchase-worksheet-modal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <a href="#" data-dismiss="modal" aria-hidden="true" class="close"></a>
        <h3 class="modal-title" data-purchase-title="<?php _e('Purchase Worksheet', 'iii-dictionary') ?>" data-details-title="<?php _e('Worksheet Details', 'iii-dictionary') ?>"><?php _e('Purchase Worksheet', 'iii-dictionary') ?></h3>
      </div>
      <div class="modal-body">
		<input type="hidden" name="cid" id="cid">
		<div class="row">
			<div class="col-sm-12">
				<div class="form-group">
					<label><?php _e('Worksheet Description', 'iii-dictionary') ?></label>
					<div class="box">
						<div class="scroll-list" style="max-height: 350px">
							<div id="hw-desc"></div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-sm-6 purchase-controls">
				<div class="form-group">
					<button type="submit" class="btn btn-block orange confirm" name="purchase-worksheet"><span class="icon-purchase" style="vertical-align: top"></span> <?php _e('Purchase', 'iii-dictionary') ?></button>
				</div>
			</div>
			<div class="col-sm-6 purchase-controls">
				<div class="form-group">
					<button type="button" class="btn btn-block grey" data-dismiss="modal"><span class="icon-cancel"></span><?php _e('Cancel', 'iii-dictionary') ?></button>
				</div>
			</div>
		</div>
	  </div>
    </div>
  </div>
</div>

			<?php break;
				case 'teach-class': ?>

				<div class="col-sm-12">
					<div class="step-block">
						<a role="button" data-toggle="collapse" href="#step1-collapse" class="step-number" title="See details">
							<?php printf(__('Step %s', 'iii-dictionary'), '<strong><em>1</em></strong>') ?>
						</a>
						<div id="step1-collapse"<?php echo is_mw_qualified_teacher() ? ' class="collapse"' : '' ?>>
							<div class="step-inst"><?php _e('Let\'s get started with the following steps.', 'iii-dictionary') ?></div>
							<div class="box box-red">
								<ul>
									<li><strong>1)</strong> <?php _e('Take the following two tests. One test will be auto-graded, and you will see your result immediately. Another test will be to edit a sample student\'s essay. We will review your work.', 'iii-dictionary') ?></li>
									<li><strong>2)</strong> <?php _e('Once we review your editing, we will notify you on this site on how to proceed to the next step.', 'iii-dictionary') ?></li>
								</ul>
								<div class="form-group"></div>
								<div class="row">
									<div class="col-sm-5">
										<label><?php _e('Auto-graded Test', 'iii-dictionary') ?></label>
										<?php if(empty($test1_result)) : ?>
											<button type="submit" name="take-test1" class="btn btn-default btn-block grey form-control"><?php _e('Take this test', 'iii-dictionary') ?></button>
										<?php endif ?>
									</div>
									<?php if(!empty($test1_result)) : ?>
										<div class="col-sm-12">
											<?php if($test1_result[0]->graded) : ?>
												<p><?php _e('Your score:', 'iii-dictionary') ?> <strong class="text-<?php echo $test1_result[0]->score >= $teacher_test_score_threshold ? 'success' : 'danger' ?>"><?php echo $test1_result[0]->score ?></strong></p>
											<?php else : ?>
												<p><?php _e('Please check back to see if you are registered as a teacher to teach English writing.', 'iii-dictionary') ?></p>
											<?php endif ?>
										</div>
									<?php endif ?>
									<?php if($test1_result[0]->graded) : ?>
										<div class="col-sm-12">
											<?php if($test1_result[0]->score < $teacher_test_score_threshold) : ?>
												<span class="text-danger"><?php _e('Sorry, you did not pass. Would you like to take the test again?', 'iii-dictionary') ?></span>
												<div class="row" style="margin-top: 10px">
													<div class="col-sm-5">
														<button type="submit" name="re-take-test1" class="btn btn-default btn-block grey form-control"><?php _e('Take this test again', 'iii-dictionary') ?></button>
													</div>
												</div>
											<?php else : ?>
												<span class="text-success"><?php _e('Accepted', 'iii-dictionary') ?></span>
											<?php endif ?>
										</div>
									<?php endif ?>

									<div class="clearfix"></div>

									<div class="col-sm-5" style="margin-top: 15px">
										<label><?php _e('Edit a sample student\'s essay Test', 'iii-dictionary') ?></label>
										<?php if(empty($test2_result)) : ?>
											<button type="submit" name="take-test2" class="btn btn-default btn-block grey form-control"<?php echo $test1_result[0]->score < $teacher_test_score_threshold ? ' disabled' : '' ?>><?php _e('Take this test', 'iii-dictionary') ?></button>
										<?php endif ?>
									</div>
									<?php if(!empty($test2_result)) : ?>
										<div class="col-sm-12">
											<?php if($test2_result[0]->graded) : ?>
												<p><?php _e('Your score:', 'iii-dictionary') ?> <strong class="text-<?php echo $test2_result[0]->score >= 80 ? 'success' : 'danger' ?>"><?php echo $test2_result[0]->score ?></strong></p>
											<?php else : ?>
												<p><?php _e('Please check back to see if you are registered as a teacher to teach English writing.', 'iii-dictionary') ?></p>
											<?php endif ?>
										</div>
									<?php endif ?>
									<?php if($test2_result[0]->graded) : ?>
										<div class="col-sm-12">
											<?php if($test2_result[0]->score < $teacher_test_score_threshold) : ?>
												<span class="text-danger"><?php _e('Sorry, you did not pass. Would you like to take the test again?', 'iii-dictionary') ?></span>
												<div class="row" style="margin-top: 10px">
													<div class="col-sm-5">
														<button type="submit" name="re-take-test2" class="btn btn-default btn-block grey form-control"><?php _e('Take this test again', 'iii-dictionary') ?></button>
													</div>
												</div>
											<?php else : ?>
												<span class="text-success"><?php _e('Accepted', 'iii-dictionary') ?></span>
											<?php endif ?>
										</div>
									<?php endif ?>
								</div>
							</div>
						</div>
					</div>
				</div>

				<?php if(is_mw_qualified_teacher()) : ?>

					<div class="col-sm-12">
						<div class="step-block">
							<a role="button" data-toggle="collapse" href="#step2-collapse" class="step-number" title="See details">
								<?php printf(__('Step %s', 'iii-dictionary'), '<strong><em>2</em></strong>') ?>
							</a>
							<div id="step2-collapse"<?php echo $is_teaching_agreement_agreed ? ' class="collapse"' : '' ?>>
								<div class="step-inst"><?php _e('Now you are ready! Before you select the class to teach, let\'s review some of important rules.', 'iii-dictionary') ?></div>
								<h2 class="title-border">Teaching Agreement</h2>
								<div class="box box-red form-group">
									<div class="scroll-list" style="max-height: 200px; color: #fff">
										<?php echo mw_get_option('teaching-agreement') ?>
									</div>
								</div>
								<div class="row">
									<div class="col-sm-4 col-sm-offset-8">
										<button type="submit" name="i-agree" class="btn btn-default btn-block orange form-control"<?php echo $is_teaching_agreement_agreed ? ' disabled' : '' ?>><span class="icon-check"></span> <?php _e('I AGREE', 'iii-dictionary') ?></button>
									</div>
								</div>
							</div>
						</div>
					</div>

					<?php if($is_teaching_agreement_agreed) : ?>

						<div class="col-sm-12">
							<div class="step-block">
								<a role="button" data-toggle="collapse" href="#step3-collapse" class="step-number" title="See details">
									<?php printf(__('Step %s', 'iii-dictionary'), '<strong><em>3</em></strong>') ?>
								</a>
								<div class="step-inst"><?php _e('Select worksheet to grade from the list below', 'iii-dictionary') ?></div>
								<div class="box box-sapphire">
									<div class="row box-header">
										<div class="col-xs-12">
											<div class="row search-tools">
												<div class="col-xs-6 col-sm-3">
													<div class="form-group">
														<?php MWHtml::select_grades('ENGLISH', $filter['grade'], array('class' => 'select-sapphire form-control', 'name' => 'filter[grade]')) ?>
													</div>
												</div>
												<div class="col-xs-12 col-sm-3 col-sm-offset-6">
													<div class="form-group">
														<button type="submit" class="btn btn-default sky-blue form-control" name="filter[search]"><?php _e('Search', 'iii-dictionary') ?></button>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col-xs-12">
											<div class="scroll-list2" style="max-height: 600px">
												<table class="table table-striped table-condensed ik-table1 text-center" id="list-sheets">
													<thead>
														<tr>
															<th><?php _e('Grade', 'iii-dictionary') ?></th>
															<th><?php _e('Sheet Name', 'iii-dictionary') ?></th>
															<th class="hidden-xs"><?php _e('Requested by', 'iii-dictionary') ?></th>
															<th><?php _e('Price', 'iii-dictionary') ?></th>
															<th></th>
														</tr>
													</thead>
													<tfoot>
														<tr><td colspan="5"><?php echo $pagination ?></td></tr>
													</tfoot>
													<tbody><?php if(empty($grading_requests->items)) : ?>
															<tr><td colspan="5"><?php _e('No grading requests', 'iii-dictionary') ?></td></tr>
														<?php else :
															foreach($grading_requests->items as $item) : ?>
																<tr>
																	<td><?php echo $item->grade ?></td>
																	<td><?php echo $item->sheet_name ?></td>
																	<td class="hidden-xs"><?php echo $item->requester ?></td>
																	<td><?php printf(__('%s pts', 'iii-dictionary'), ik_calc_grading_earning($item->paid_amount)) ?></td>
																	<td>
																		<button type="submit" name="accept-request" class="btn btn-default btn-block btn-tiny grey accept-request" data-request-id="<?php echo $item->request_id ?>"><?php _e('Grade', 'iii-dictionary') ?></button>
																	</td>
																</tr>
														<?php endforeach; endif ?>
													</tbody>
												</table>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>

					<?php endif ?>

				<?php endif ?>

			<?php break;
				case 'request-payment': ?>

				<div class="col-sm-12">
					<div class="form-group">
						<label><?php _e('Your current points is', 'iii-dictionary') ?></label>
						<div class="box box-green">
							<h3 class="positive-amount"><?php echo number_format($current_user_points, 2) ?></h3>
						</div>
					</div>
				</div>
				<div class="col-sm-12">
					<h3><?php _e('Receiving Payments', 'iii-dictionary') ?></h3>
				</div>
				<div class="col-sm-12">
					<div class="box box-red box-arrow-down">
						<p><?php _e('How would you like your payment?', 'iii-dictionary') ?></p>
						<div class="row">
							<?php foreach($receiving_methods as $method) : ?>
								<div class="col-sm-6">
								<div class="radio radio-style1 radio-inline">
									<input id="receiving-method<?php echo $method->id ?>" type="radio" name="receiving-method" value="<?php echo $method->id ?>">
									<label for="receiving-method<?php echo $method->id ?>" class="subscription-type"><?php echo $method->name ?></label>
								</div>
								</div>
							<?php endforeach ?>
						</div>
					</div>
				</div>
				<div class="col-sm-12">
					<div class="box box-red box-arrow-down">
						<p>Amount requested <em class="text-info">(Exchange rate: <?php echo $point_ex_rate ?>pts = 1$)</em></p>
						<div class="box">
							<div class="amount-request">
								<input type="text" name="amount-request" value="0" autocomplete="off"> 00.00 pts
							</div>
						</div>
						<p class="text-alert"><strong><?php _e('Note:', 'iii-dictionary') ?></strong> <?php _e('You can redeem your balance in increments of 100.00', 'iii-dictionary') ?></p>
					</div>
				</div>
				<div class="col-sm-12">
					<div class="box box-red form-group">
						<p><?php _e('Email address for receiving a payment (paypal or gift card)', 'iii-dictionary') ?></p>
						<input type="text" name="receiving-email" class="form-control" size="25" style="width: auto">
					</div>
				</div>
				<div class="col-sm-5 col-sm-offset-7">
					<button type="submit" name="request-payment" class="btn btn-default btn-block orange form-control"><span class="icon-check"></span> <?php _e('Request Payment', 'iii-dictionary') ?></button>
				</div>

				<div class="col-sm-12">
					<h2 class="title-border"><?php _e('Details of your earnings less expenses', 'iii-dictionary') ?></h2>
				</div>
				<div class="col-sm-12">
					<div class="box box-green">
						<table class="table table-striped table-condensed ik-table1 text-center" id="list-sheets">
							<thead>
								<tr>
									<th><?php _e('Date', 'iii-dictionary') ?></th>
									<th><?php _e('Transaction', 'iii-dictionary') ?></th>
									<th class="hidden-xs"><?php _e('Amount', 'iii-dictionary') ?></th>
									<th><?php _e('Note', 'iii-dictionary') ?></th>
								</tr>
							</thead>
							<tfoot>
								<tr><td colspan="5"><?php echo $pagination ?></td></tr>
							</tfoot>
							<tbody><?php if(empty($user_transactions->items)) : ?>
									<tr><td colspan="4"><?php _e('No transactions', 'iii-dictionary') ?></td></tr>
								<?php else :
									foreach($user_transactions->items as $item) : ?>
										<tr>
											<td><?php echo ik_date_format($item->txn_date, 'm/d/Y H:i:s') ?></td>
											<td><?php echo $item->txn_type ?></td>
											<td class="<?php echo in_array($item->txn_type_id, array(POINT_TXN_GRADING_WORKSHEET, POINT_TXN_GIFT)) ? 'positive' : 'negative' ?>-amount"><?php echo $item->amount ?> pts</td>
											<td><?php echo $item->note ?></td>
										</tr>
								<?php endforeach; endif ?>
							</tbody>
						</table>
					</div>
				</div>

			<?php break;
				case 'tutor-math' : ?>
					<div class="col-sm-12">
						<div class="step-block">
							<a role="button" data-toggle="collapse" href="#step1-collapse" class="step-number" title="See details">
								<?php printf(__('Step %s', 'iii-dictionary'), '<strong><em>1</em></strong>') ?>
							</a>
							<div id="step1-collapse"<?php echo is_mw_qualified_teacher() ? ' class="collapse"' : '' ?>>
								<div class="step-inst"><?php _e('Let\'s get started with the following steps.', 'iii-dictionary') ?></div>
								<div class="box box-red">
									<ul>
										<li><strong>1)</strong> <?php _e('Take the following two tests. One test will be auto-graded, and you will see your result immediately. Another test will be to edit a sample student\'s essay. We will review your work.', 'iii-dictionary') ?></li>
										<li><strong>2)</strong> <?php _e('Once we review your editing, we will notify you on this site on how to proceed to the next step.', 'iii-dictionary') ?></li>
									</ul>
									<div class="form-group"></div>
									<div class="row">
										<div class="col-sm-5">
											<label><?php _e('Auto-graded Test qualification to teacher K-to-7', 'iii-dictionary') ?></label>
											<?php if(empty($math_test1_result)) : ?>
												<button type="submit" name="take-math-test1" class="btn btn-default btn-block grey form-control"><?php _e('Take this test', 'iii-dictionary') ?></button>
											<?php endif ?>
										</div>
										
										<?php if(!empty($math_test1_result)) : ?>
											<div class="col-sm-12">
												<?php if($math_test1_result[0]->graded) : ?>
													<p><?php _e('Your score:', 'iii-dictionary') ?> <strong class="text-<?php echo $math_test1_result[0]->score >= $teacher_math_test_score_threshold ? 'success' : 'danger' ?>"><?php echo $math_test1_result[0]->score ?></strong></p>
												<?php else : ?>
													<p><?php _e('Please check back to see if you are registered as a teacher to teach Math.', 'iii-dictionary') ?></p>
												<?php endif ?>
											</div>
										<?php endif ?>
										<?php if($math_test1_result[0]->graded) : ?>
											<div class="col-sm-12">
												<?php if($math_test1_result[0]->score < $teacher_math_test_score_threshold) : ?>
													<span class="text-danger"><?php _e('Sorry, you did not pass. Would you like to take the test again?', 'iii-dictionary') ?></span>
													<div class="row" style="margin-top: 10px">
														<div class="col-sm-5">
															<button type="submit" name="re-take-math-test1" class="btn btn-default btn-block grey form-control"><?php _e('Take this test again', 'iii-dictionary') ?></button>
														</div>
													</div>
												<?php else : ?>
													<span class="text-success"><?php _e('Accepted', 'iii-dictionary') ?></span>
												<?php endif ?>
											</div>
										<?php endif ?>

										<div class="clearfix"></div>
										<div class="col-sm-5" style="margin-top: 15px">
											<label><?php _e('Auto-graded Test qualification to teacher K-to-12', 'iii-dictionary') ?></label>
											<?php if(empty($math_test2_result)) : ?>
												<button type="submit" name="take-math-test2" class="btn btn-default btn-block grey form-control"<?php echo $math_test1_result[0]->score < $teacher_math_test_score_threshold ? ' disabled' : '' ?>><?php _e('Take this test', 'iii-dictionary') ?></button>
											<?php endif ?>
										</div>
										<?php if(!empty($math_test2_result)) : ?>
											<div class="col-sm-12">
												<?php if($math_test2_result[0]->graded) : ?>
													<p><?php _e('Your score:', 'iii-dictionary') ?> <strong class="text-<?php echo $math_test2_result[0]->score >=  $teacher_math_test_score_threshold ? 'success' : 'danger' ?>"><?php echo $math_test2_result[0]->score ?></strong></p>
												<?php else : ?>
													<p><?php _e('Please check back to see if you are registered as a teacher to teach Math.', 'iii-dictionary') ?></p>
												<?php endif ?>
											</div>
										<?php endif ?>
										<?php if($math_test2_result[0]->graded) : ?>
											<div class="col-sm-12">
												<?php if($math_test2_result[0]->score < $teacher_math_test_score_threshold) : ?>
													<span class="text-danger"><?php _e('Sorry, you did not pass. Would you like to take the test again?', 'iii-dictionary') ?></span>
													<div class="row" style="margin-top: 10px">
														<div class="col-sm-5">
															<button type="submit" name="re-take-math-test2" class="btn btn-default btn-block grey form-control"><?php _e('Take this test again', 'iii-dictionary') ?></button>
														</div>
													</div>
												<?php else : ?>
													<span class="text-success"><?php _e('Accepted', 'iii-dictionary') ?></span>
												<?php endif ?>
											</div>
										<?php endif ?>
									</div>
								</div>
							</div>
						</div>
					</div>
					<?php if(is_mw_qualified_teacher(null, 1)) : ?>
					<div class="col-sm-12">
						<div class="step-block">
							<a role="button" data-toggle="collapse" href="#step2-collapse" class="step-number" title="See details">
								<?php printf(__('Step %s', 'iii-dictionary'), '<strong><em>2</em></strong>') ?>
							</a>
							<div id="step2-collapse"<?php echo $is_teaching_agreement_agreed_math ? ' class="collapse"' : '' ?>>
								<div class="step-inst"><?php _e('Now you are ready! Before you select the class to teach, let\'s review some of important rules.', 'iii-dictionary') ?></div>
								<h2 class="title-border">Teaching Agreement</h2>
								<div class="box box-red form-group">
									<div class="scroll-list" style="max-height: 200px; color: #fff">
										<?php echo mw_get_option('math-teaching-agreement') ?>
									</div>
								</div>
								<div class="row">
									<div class="col-sm-4 col-sm-offset-8">
										<button type="submit" name="i-agree-math" class="btn btn-default btn-block orange form-control"<?php echo $is_teaching_agreement_agreed_math ? ' disabled' : '' ?>><span class="icon-check"></span> <?php _e('I AGREE', 'iii-dictionary') ?></button>
									</div>
								</div>
							</div>
						</div>
					</div>
					
					<?php if($is_teaching_agreement_agreed_math) : ?>

						<div class="col-sm-12">
							<div class="step-block">
								<a role="button" data-toggle="collapse" href="#step3-collapse" class="step-number" title="See details">
									<?php printf(__('Step %s', 'iii-dictionary'), '<strong><em>3</em></strong>') ?>
								</a>
								<div class="step-inst"><?php _e('Select worksheet to grade from the list below', 'iii-dictionary') ?></div>
								<div class="box box-sapphire">
									<div class="row box-header">
										<div class="col-xs-12">
											<div class="row search-tools">
												<div class="col-xs-6 col-sm-3">
													<div class="form-group">
														
													</div>
												</div>
												<div class="col-xs-12 col-sm-3 col-sm-offset-6">
													<div class="form-group">
														<button type="submit" class="btn btn-default sky-blue form-control" name="filter[search]"><?php _e('Search', 'iii-dictionary') ?></button>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col-xs-12">
											<div class="scroll-list2" style="max-height: 600px">
												<table class="table table-striped table-condensed ik-table1 text-center" id="list-request-chat">
													<thead>
														<tr>
															<th><?php _e('Math category Name', 'iii-dictionary') ?></th>
															<th>&nbsp;</th>
															<th><?php _e('Requested by', 'iii-dictionary') ?></th>
															<th><?php _e('Price /15 minutes', 'iii-dictionary') ?></th>
														</tr>
													</thead>
													<tfoot>
														<tr><td colspan="4"><?php echo $pagination ?></td></tr>
													</tfoot>
													<tbody><?php if(empty($chat_requests->items)) : ?>
														<tr><td colspan="4"><?php _e('No result', 'iii-dictionary') ?></td></tr>
													<?php else : foreach($chat_requests->items AS $data) : ?>
														<tr>
															<td><?php echo $data->category ?></td>
															<td>
																<?php 
																	switch($data->status) {
																		case 0 : 
																			echo '<a class="tt-btn-accept" href="#" 
																				data-session="' . $data->id . '" 
																				data-teacher="' . get_current_user_id() . '"
																				data-user="' . $data->user_id . '"
																				data-sheet="' . $data->sheet_id . '"
																				data-url="' . locale_home_url() . '/?' . $data->url . '"
																				
																				>' . __('Accept', 'iii-dictionary') . '</a>';
																		break;
																		case 1 : 
																			echo '<label class="tt-lbl-taken">' . __('Taken', 'iii-dictionary') . '</label>';
																		break;
																		case 2 : 
																			echo '<label class="tt-lbl-complete">' . __('Complete', 'iii-dictionary') . '</label>';
																		break;
																	}
																?>
															</td>
															<td><?php echo $data->user ?></td>
															<td><?php echo '$' . $data->price ?></td>
														</tr>
													<?php  endforeach; endif; ?>
													</tbody>
												</table>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					<?php endif ?>
					
					<?php endif ?>
			<?php break; 	?>
			<?php endswitch ?>

		</div>
	</form>

<?php if($active_tab == 'purchase-worksheet') : ?>
<div id="purchase-points-dialog" class="modal fade modal-red-brown">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
            <a href="#" data-dismiss="modal" aria-hidden="true" class="close"></a>
             <h3><?php _e('Purchase Points', 'iii-dictionary') ?></h3>
        </div>
		<form method="post" action="<?php echo home_url_ssl() ?>/?r=payments">
			<input type="hidden" name="sub-type" value="4">
			<div class="modal-body">
				<div class="row">
					<div class="col-sm-6">
						<div class="form-group">
							<label><?php _e('Number of Points', 'iii-dictionary') ?></label>
							<input type="number" class="form-control" name="no-of-points" id="no-of-points" min="1">
						</div>
					</div>
					<div class="col-sm-12">
						<div class="box" style="text-align: right">
							<?php _e('Total amount:', 'iii-dictionary') ?> <span class="currency">$ <span id="total-amount-points">0</span></span>
						</div>
					</div>
				</div>				
			</div>
			<div class="modal-footer">
				<div class="row">
					<div class="col-sm-6">
						<div class="form-group">
							<button type="submit" name="add-to-cart" class="btn btn-block orange confirm"><span class="icon-cart"></span><?php _e('Check out', 'iii-dictionary') ?></button>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group">
							<a href="#" data-dismiss="modal" aria-hidden="true" class="btn btn-block grey secondary"><span class="icon-cancel"></span><?php _e('Cancel', 'iii-dictionary') ?></a>
						</div>
					</div>
				</div>
			</div>
		</form>
      </div>
    </div>
</div>
<?php endif ?>

<table id="questions-table" style="display: none"></table>

<script>var ptsr = <?php echo mw_get_option('point-exchange-rate') ?>;</script>
<script>
	var __US = "<?php echo (is_user_logged_in()) ? get_current_user_id() : gethostname() ?>";
	var __NAME =  "<?php echo (!empty($current_user)) ? $current_user->display_name : '' ?>";
	var __EMAIL =  "<?php echo (!empty($current_user)) ? $current_user->user_email : '' ?>";
	var __PRICE = <?php echo mw_get_option('math-chat-price'); ?>;
</script>
<?php if(!$is_math_panel) : ?>
	<?php get_dict_footer() ?>
<?php else : ?>
	<?php get_math_footer() ?>
<?php endif ?>