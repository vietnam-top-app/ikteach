<?php
	$route = get_route();

	// make sure any ajax call to this script receive status 200
	header('HTTP/1.1 200 OK');

	if( !isset($route[1]) ) : ?>
<!DOCTYPE html>
<html><head></head></html>
<?php endif ?>
<?php
	global $wpdb;
	$task = $route[1];
	if(isset($route[2])) {
		$do = $route[2];
	}

	/*
	 * ajax search for dictionary
	 */
	if($task == 'dictionary')
	{
		$d = $_GET['d'];
		$dict_table = get_dictionary_table($d);		
		
		$words = $wpdb->get_results( $wpdb->prepare(
					'SELECT DISTINCT entry FROM ' . $wpdb->prefix . $dict_table . ' WHERE entry LIKE %s LIMIT 0, 8',
					array($_GET['w'] . '%')
				) );

		// user might input inflected form. Try to get original form
		if(empty($words)) {
			$search = $wpdb->get_row($wpdb->prepare('SELECT DISTINCT entry FROM ' . $wpdb->prefix . $dict_table . ' WHERE REPLACE(inflection, \'*\', \'\') LIKE %s', array('%<if>' . $_GET['w'] . '</if>%')));
		}

		// research
		if(!is_null($search))
		{
			$words = $wpdb->get_results($wpdb->prepare(
					'SELECT DISTINCT entry FROM ' . $wpdb->prefix . $dict_table . ' WHERE entry LIKE %s LIMIT 0, 8',
					array($search->entry . '%')
				));
		}

		if( !empty($words) )
		{
			foreach($words as $word)
			{
				?><a href="<?php echo locale_home_url() . '/?r=dictionary/' . $d . '/' . $word->entry ?>"><?php echo $word->entry ?></a><?php
			}
		}
		else
		{
			$words = $wpdb->get_results( $wpdb->prepare(
					'SELECT DISTINCT entry, levenshtein(entry, %s) AS lev FROM `wp_dict_elementary` WHERE entry LIKE %s ORDER BY lev LIMIT 8',
					array( $_GET['w'], substr($_GET['w'], 0, 2) . '%' )
				) );

			foreach($words as $word)
			{
				?><a href="<?php echo locale_home_url() . '/?r=dictionary/' .  $d . '/' . $word->entry ?>"><?php echo $word->entry ?></a><?php
			}
		}

		exit;
	}

	/*
	 * return a random quiz
	 */
	if($task == 'randomquiz')
	{
		$dictionary = $_GET['d'];
		$sheet_category = $_GET['c'];

		echo json_encode(MWDB::random_quiz($dictionary, $sheet_category));

		die;
	}

	/*
	 * remove an item from search history
	 */
	if($task == 'history')
	{
		if($do == 'remove')
		{
			remove_search_history_item($_REAL_POST['id'], $_REAL_POST['d']);
		}
	}

	/*
	 * return sheet content
	 */
	if( $task == 'sheets')
	{
		$sid = $_GET['sid'];

		if(isset($_GET['readonly']))
		{
			if($_GET['readonly'])
				$readonly = ' readonly="readonly"';
			else
				$readonly = '';
		}
		else
			$readonly = '';

		$result = $wpdb->get_row( $wpdb->prepare(
					'SELECT * FROM ' . $wpdb->prefix . 'dict_sheets WHERE id = %d',
					array( $sid )
				) );

		if(is_null($result)) {
			die('0');
		}
		$questions = json_decode($result->questions, true);

		$html = '<tbody>';
		switch($result->assignment_id)
		{
			case ASSIGNMENT_SPELLING:
				for($i = 1; $i <= 20; $i++)
				{
					$html .= '<tr>';
					$html .=     '<td><input type="text" value="' . esc_html($questions[$i - 1]) . '"' . $readonly. '></td>';
					$html .= '</tr>';
				}
				break;
			case ASSIGNMENT_VOCAB_GRAMMAR:
				for($i = 1; $i <= 20; $i++)
				{
					$html .= '<tr>';
					$html .= 	  '<td><input type="text" value="' . esc_html($questions['question'][$i - 1]) . '"></td>';
					$html .=      '<td><input type="text" value="' . esc_html($questions['c_answer'][$i - 1]) . '"' . $readonly . '></td>';
					$html .= 	  '<td><input type="text" value="' . esc_html($questions['w_answer1'][$i - 1]) . '"' . $readonly . '></td>';
					$html .= 	  '<td><input type="text" value="' . esc_html($questions['w_answer2'][$i - 1]) . '"' . $readonly . '></td>';
					$html .= 	  '<td><input type="text" value="' . esc_html($questions['w_answer3'][$i - 1]) . '"' . $readonly . '></td>';
					$html .= 	  '<td><input type="text" value="' . esc_html($questions['w_answer4'][$i - 1]) . '"' . $readonly . '></td>';
					$html .= 	  '<td><input type="text" value="' . esc_html($questions['quiz'][$i - 1]) . '"></td>';
					$html .= '</tr>';
				}
				break;
			case ASSIGNMENT_READING:
				for($i = 1; $i <= 20; $i++) {
					$html .= '<tr>';
					$html .= 	'<td><input type="text" value="' . $questions['question'][$i - 1] . '"></td>';
					$html .= 	'<td><input type="text" value="' . $questions['c_answer'][$i - 1] . '"></td>';
					$html .= 	'<td><input type="text" value="' . $questions['w_answer1'][$i - 1] . '"></td>';
					$html .= 	'<td><input type="text" value="' . $questions['w_answer2'][$i - 1] . '"></td>';
					$html .= 	'<td><input type="text" value="' . $questions['w_answer3'][$i - 1] . '"></td>';
					$html .= 	'<td><input type="text" value="' . $questions['w_answer4'][$i - 1] . '"></td>';
					$html .= 	'<td><input type="text" value="' . $questions['quiz'][$i - 1] . '"></td>';
					$html .= '</tr>';
				}
				$json['passage'] = $result->passages;
				break;
			case ASSIGNMENT_WRITING:
				for($i = 1; $i <= 20; $i++) {
					$html .= '<tr>';
					$html .= 	'<td><textarea>' . $questions['question'][$i - 1] . '</textarea></td>';
					$html .= 	'<td><input type="text" value="' . $questions['quiz'][$i - 1] . '"></td>';
					$html .= '</tr>';
				}
				break;
		}
		$html .= '</tbody>';

		$json['html'] = $html;
		// $json['desc'] = $result->description;

		echo json_encode($json);
		die;
	}
	
	/*
	 * return a question
	 */
	if($task == 'question')
	{
		$current_user_id = get_current_user_id();
		if( isset($_GET['hid']) && is_numeric($_GET['hid']) )
		{
			$sheet = $wpdb->get_row($wpdb->prepare(
				'SELECT s.*, hs.id AS result_id, finished_question
				FROM ' . $wpdb->prefix . 'dict_homeworks AS h
				JOIN ' . $wpdb->prefix . 'dict_sheets AS s ON s.id = h.sheet_id
				LEFT JOIN ' . $wpdb->prefix . 'dict_homework_results AS hs ON hs.homework_id = h.id
				WHERE h.id = %d AND (userid = %d OR userid IS NULL)', $_GET['hid'], $current_user_id
			));

			if(is_null($sheet->result_id)) {
				$json['rid'] = $json['lq']  = 0;
			}
			else {
				$json['rid'] = $sheet->result_id;
				$json['lq']  = $sheet->finished_question;
			}
		}

		if( isset($_GET['sid']) && is_numeric($_GET['sid']) ) 
		{
			$sheet = $wpdb->get_row($wpdb->prepare(
				'SELECT s.*, p.id AS pid, p.answers AS practice_answers
				FROM ' . $wpdb->prefix . 'dict_sheets AS s
				LEFT JOIN ' . $wpdb->prefix . 'dict_practice_results AS p ON p.sheet_id = s.id AND p.user_id = ' . $current_user_id . '
				WHERE s.id = %s', $_GET['sid']
			));
		}

		$words = json_decode($sheet->questions);
		$practice_answers = json_decode($sheet->practice_answers, true);
		$dict_table = get_dictionary_table($sheet->dictionary_id);
		include IK_PLUGIN_DIR . '/library/formatter.php';
		
		if($sheet->assignment_id == ASSIGNMENT_SPELLING)
		{
			$insql = '';
			$count = 0;
			foreach($words as $key => $v) {
				$insql[] = "'" . esc_sql($v) . "'";
			}

			$results = $wpdb->get_results(
				'SELECT id, entry, sound, sound_url, definition 
				FROM ' . $wpdb->prefix . $dict_table . ' 
				WHERE entry IN (' . implode(',', $insql) . ')'
			);

			foreach($results as $item) {
				$tmp[strtolower($item->entry)][] = array(
					'id' => $item->id, 
					'entry' => $item->entry, 
					'sound' => $item->sound, 
					'sound_url' => $item->sound_url, 
					'definition' => $item->definition
				);
			}

			foreach($tmp as $items)
			{
				$a = array();

				foreach($items as $item)
				{
					$a['entry'] = $item['entry'];
					$a['def'] .= WFormatter::_def($item['definition'], $sheet->dictionary_id);
					if(!isset($a['sound']))
					{
						if(!is_null($item['sound_url'])) {
							$a['sound'] = $item['sound_url'];
						}
						else {
							$sound_url = WFormatter::_sound($item['sound'], $sheet->dictionary_id, true);
							$a['sound'] = $sound_url;
							if($sound_url != '') {
								$wpdb->update( 
									$wpdb->prefix . $dict_table, 
									array( 
										'sound_url' => $sound_url
									),
									array( 'id' => $item['id'] )
								);
							}
						}
					}
					$ans = '';
					if(isset($practice_answers['q' . $count])) {
						$ans = $practice_answers['q' . $count];
					}
					$a['selected'] = $ans;
					
				}
				$json['sheet'][] = $a;
				$count++;
			}
		}
		else
		{
			for($i = 0; $i < count($words->question); $i++)
			{
				$ans = '';
				if(isset($practice_answers['q' . $i])) {
					$ans = $practice_answers['q' . $i];
				}

				$answers = array(
					array($words->c_answer[$i], 1),
					array($words->w_answer1[$i], 0),
					array($words->w_answer2[$i], 0)
				);
				if(!empty($words->w_answer3[$i])) {
					$answers[] = array($words->w_answer3[$i], 0);
				}
				if(!empty($words->w_answer4[$i])) {
					$answers[] = array($words->w_answer4[$i], 0);
				}

				$q[$i] = array(
					'sentence' => $words->question[$i],
					'answers'  => $answers,
					'c_a'      => $words->c_answer[$i],
					'quiz'     => $words->quiz[$i],
					'selected'     => $ans
				);
			}

			if(in_array($sheet->assignment_id, array(ASSIGNMENT_VOCAB_GRAMMAR, ASSIGNMENT_READING)) !== false)
			{
				$def_js = array();
				$json['sheet'] = array($q, $def_js);
			}
			else
			{
				$json['sheet'][] = $q;
			}
		}

		$json['pid'] = is_null($sheet->pid) ? 0 : (int) $sheet->pid;

		if($sheet->assignment_id == ASSIGNMENT_READING) {
			$json['sheet']['passage'] = $sheet->passages;
		}

		$json['htype'] = '';
		if($sheet->homework_type_id == HOMEWORK_PUBLIC) {
			$json['htype'] = __('Worksheet - Free', 'iii-dictionary');
		}
		else if($sheet->homework_type_id == HOMEWORK_SUBSCRIBED) {
			$json['htype'] = __('Worksheet - Subscribed', 'iii-dictionary');
		}

		echo json_encode($json);
		die;
	}

	/*
	 * saving practice answers
	 */
	if($task == 'practice')
	{
		$userid = get_current_user_id();

		if(!$userid) {
			die;
		}

		if($do == 'save')
		{
			$pid = $_REAL_POST['pid'];
			$q = $_REAL_POST['q'];
			$sid = $_REAL_POST['sid'];
			$answers = array('q' . $q => $_REAL_POST['answer']);
			$ptid = $_REAL_POST['ptid'];
			if(!$pid)
			{
				$result = $wpdb->insert(
					$wpdb->prefix . 'dict_practice_results', 
					array(
						'user_id' => $userid, 
						'sheet_id' => $sid,
						'answers' => json_encode($answers),
						'practice_id' => $ptid
					)
				);

				$pid = $wpdb->insert_id;
			}
			else
			{
				$row = $wpdb->get_row('SELECT answers, practice_id  FROM ' . $wpdb->prefix . 'dict_practice_results WHERE id = ' . esc_sql($pid));
				if($row) {
					$updated_answers = array_merge(json_decode($row->answers, true), $answers);
					$ptid = $ptid  != 0 ? $ptid : $row->practice_id;
					$result = $wpdb->update(
						$wpdb->prefix . 'dict_practice_results',
						array(
							'answers' => json_encode($updated_answers),
							'practice_id' => $ptid
						),
						array('id' => $pid)
					);
				}
			}

			if($result !== false) {
				echo json_encode(array($pid));
			} else {
				echo json_encode(array(0));
			}
			exit;
		}
	}

	/*
	 * saving homework answers
	 */
	if($task == 'homework')
	{
		$userid = get_current_user_id();

		if(!$userid || !isset($_POST['homework_id'])) {
			die;
		}
                $valuepoint=0;
		// saving answers as student progress
		if($do == 'answer')
		{
			$q = $_REAL_POST['q'];
			$question_count = $_REAL_POST['qc'];
			$rid = $_REAL_POST['rid'];
			$answer = !empty($_POST['writing']) ? $_REAL_POST['answer'] : json_decode($_REAL_POST['answer'], true);
			$graded = isset($_REAL_POST['graded']) ? $_REAL_POST['graded'] : 1;

			$score = 0;
			$score_per_question = 100 / $question_count;

			if(!$rid)
			{
				$ca = 0;
				if($answer->score) {
					$score = $score_per_question;
					$ca = 1;
				}

				$result = $wpdb->insert(
					$wpdb->prefix . 'dict_homework_results', 
					array(
						'userid' => $userid, 
						'homework_id' => $_POST['homework_id'],
						'answers' => json_encode(array('q' . $q => $answer)),
						'score' => $score,
						'correct_answers_count' => $ca,
						'attempted_on' => date('Y-m-d', time()),
						'finished_question' => $q,
						'finished' => 0,
						'graded' => $graded
					)
				);

				if($result) {
					echo json_encode(array($wpdb->insert_id));
				} else {
					echo json_encode(array(0));
				}
				exit;
			}
			else
			{
				$result_sheet = $wpdb->get_row($wpdb->prepare(
					'SELECT answers, correct_answers_count, score 
					FROM ' . $wpdb->prefix . 'dict_homework_results 
					WHERE id = %d', $rid
				));

				$answers = json_decode($result_sheet->answers, true);
				$answers['q' . $q] = $answer;

				$correct_count = 0;
				// check for number of correct answers if this is not writing homework
				if(empty($_POST['writing'])) {
					foreach($answers as $item) {
						if($item['score']) {
							$correct_count++;
						}
					}
				}
				// calculate total score
				$score = $correct_count * $score_per_question;

				$result_data = array(
						'answers' => json_encode($answers),
						'correct_answers_count' => $correct_count,
						'score' => $score,
						'finished_question' => $q,
						'attempted_on' => date('Y-m-d', time()),
						'graded' => $graded
				);

				$result = $wpdb->update(
					$wpdb->prefix . 'dict_homework_results',
					$result_data, 
					array('id' => $rid)
				);

				if($result !== false) {
					echo json_encode(array($rid));
				} else {
					echo json_encode(array(0));
				}
                                $valuepoint=$score;
				exit;
			}
		}

		// set the homework to finished
		if($do == 'submit')
		{

			$rid = esc_sql($_POST['rid']);
			$feedback = esc_sql(stripslashes($_POST['feedback']));
			// If teacher take the test
			if(isset($_POST['teacher_taking_test']) && $_POST['teacher_taking_test'] == 1){ 
				$current_user_id = get_current_user_id();
				$user = $wpdb->get_row("SELECT * FROM {$wpdb->users} WHERE ID = {$current_user_id}");
				$subject = __('About teacher take the test','iii-dictionary');
				$message .= __('An teacher account has completed test. Login with administrator account to check the test result.
                                This is infomation of teacher:','iii-dictionary').'<br>';
				$message .= __('Fullname:').' '.$user->display_name.'<br>';
				$message .= __('Username:').' '.$user->user_login.'<br>';
				$message .= __('Email:').' '.$user->user_email.'<br>';
				$message .= '<a href="'.home_url().'/wp-admin" >'.__('Click here').'</a> '.__('to login','iii-dictionary');
				$admins = get_users(array('role' => 'mw_super_admin'));
				foreach ($admins as $key => $admin) {
					if($admin->user_email != ''){
						wp_mail($admin->user_email,$subject,$message);		
					}
				}
			}
			$result = $wpdb->update(
				$wpdb->prefix . 'dict_homework_results',
				array(
					'finished' => 1,
					'submitted_on' => date('Y-m-d', time()),
					'message' => $feedback
				), 
				array( 'id' => $rid )
			);
                        $current_user_id = get_current_user_id();
//                        $resultpoint=$wpdb->get_row( "SELECT score FROM wp_dict_homework_results 
//                        WHERE homework_id =  {$_POST['homework_id']} AND userid = {$current_user_id}")->score;
                        $resultpoint=$wpdb->get_row( "SELECT hdr.score as score,ds.assignment_id as assignment_id FROM wp_dict_homework_results as hdr
                            JOIN wp_dict_homeworks AS dh ON hdr.homework_id = dh.id
                            JOIN wp_dict_sheets AS ds ON ds.id = dh.sheet_id
                            WHERE homework_id =  {$_POST['homework_id']} AND userid = {$current_user_id}");
			if($result) {
				echo json_encode(array($rid));
			} else {
				echo json_encode(array(0));
			} 
                        $teacher_test_score_threshold = mw_get_option('teacher-test-score-threshold');
                        ik_enqueue_messages('Homework Submitted.', 'success');
                        if($resultpoint->assignment_id!=4){
                             if($resultpoint->score<$teacher_test_score_threshold){
                                 ik_enqueue_messages('Sorry, you correctly answered '.$resultpoint->score.'%', 'failed');
                             }  else {
                                 ik_enqueue_messages('You correctly answer '.$resultpoint->score.'%', 'success');
                             }
                            
                        }
			exit;
		}
	}

	/*
	 * update homework score
	 */
	if($task == 'grade_homework')
	{
		$score = $_POST['score'];
		$hrid = $_POST['hrid'];

		if($score >= 0 && $score <= 100) {
			$result = $wpdb->update(
				$wpdb->prefix . 'dict_homework_results',
				array('score' => $score, 'correct_answers_count' => $score, 'graded' => 1),
				array('id' => $hrid)
			);
		}
	}

	/*
	 * Check to see if words exist in given dictionary
	 */
	if($task == 'checkword')
	{
		if( !isset($_GET['dict']) || !is_numeric($_GET['dict']) ) {
			die;
		}
		
		include IK_PLUGIN_DIR . '/library/formatter.php';

		$w = stripslashes($_GET['w']);
		$dict_table = get_dictionary_table($_GET['dict']);

		$words = json_decode($w);
		$words = array_merge($words[0], $words[1]);
		$words_sound = $words;
		$output[0] = $output[1] = $insql = array();

		foreach($words as $key => $v)
		{
			if($v != '') {
				$insql[$key] = "'" . esc_sql($v) . "'";
			}
			else {
				unset($words[$key]);
				unset($words_sound[$key]);
			}
		}

		$results = $wpdb->get_results('SELECT id, entry, sound, sound_url FROM ' . $wpdb->prefix . $dict_table . ' WHERE entry IN (' . implode(',', $insql) . ')');

		foreach($results as $item)
		{
			if(($key = array_search($item->entry, $words)) !== false)
			{
				unset($words[$key]);
			}

			if(is_null($item->sound_url))
			{
				$sound_url = WFormatter::_sound($item->sound, $_GET['dict'], true);
				if($sound_url != '')
				{
					$result = $wpdb->update(
						$wpdb->prefix . $dict_table, 
						array( 
							'sound_url' => $sound_url
						),
						array( 'id' => $item->id )
					);

					if(($key = array_search($item->entry, $words_sound)) !== false)
					{
						unset($words_sound[$key]);
					}
				}
			}
			else
			{
				if(($key = array_search($item->entry, $words_sound)) !== false)
				{
					unset($words_sound[$key]);
				}
			}
		}

		$output[0] = $words;
		$output[1] = $words_sound;

		echo json_encode($output);
		die;
	}

	/*
	 * toggle Sheet state
	 */
	if($task == 'shtstate')
	{		
		if( !isset($_POST['id']) || !is_numeric($_POST['id']) ) {
			die;
		}

		$result = $wpdb->query($wpdb->prepare( 
					'UPDATE ' . $wpdb->prefix . 'dict_sheets SET active = ABS(active - 1) WHERE id = %d', $_POST['id']
				));
		echo $result ? '1' : '0';
		die;
	}
	
	/*
	 * Group Homework
	 */
	if($task == 'group')
	{
		if(!isset($route[2])){
			die;
		}

		$do = $route[2];

		if($do == 'create')
		{
			$gname = esc_html($_POST['gname']);
			$gpass = esc_html($_POST['gpasswrd']);
			if( trim($gname) != '' && trim($gpass) != '')
			{
				if(strpos($gname, ' ') !== false) {
					echo json_encode(array('status' => 0, 'msg' => 'Group name cannot contain spacing!'));
					die;
				}
				$result = $wpdb->query( $wpdb->prepare(
							'SELECT * FROM ' . $wpdb->prefix . 'dict_groups WHERE name = %s',
							array( $gname )
						) );

				if( !$result )
				{
					$res = $wpdb->insert(
						$wpdb->prefix . 'dict_groups', 
						array( 
							'name' => $gname, 
							'password' => $gpass,
							'created_by' => get_current_user_id(),
							'created_on' => date('Y-m-d', time()),
							'active' => 1
						)
					);

					if($res) {						
						echo json_encode(array('status' => 1, 'msg' => 'Successfully create Group: <em>' . $gname . '</em>', 'id' => $wpdb->insert_id));
						die;
					}
					else {
						echo json_encode(array('status' => 0, 'msg' => 'Can not create Group!'));
						die;
					}
				}
				else
				{
					echo json_encode(array('status' => 0, 'msg' => 'The name, <em>' . $gname . '</em>, is already used. Please try it again with a different name.'));
					die;
				}
			}
			else
			{
				echo json_encode(array('status' => 0, 'msg' => 'Group name and Passwords must not be empty!'));
				die;
			}
		}
		
		if($do == 'list')
		{
			$groups = $wpdb->get_results('SELECT id, name FROM ' . $wpdb->prefix . 'dict_groups WHERE created_by = ' . get_current_user_id());
			echo json_encode($groups);
			die;
		}
		
		if($do == 'changepass')
		{
			$apw = stripslashes($_POST['apw']);
			$npw = stripslashes($_POST['npw']);
			$gid = stripslashes($_POST['gid']);
			
			$user = get_userdata( get_current_user_id() );
			if(wp_check_password( $apw, $user->user_pass, $user->ID) ) {
				$wpdb->update($wpdb->prefix . 'dict_groups', array('password' => $npw), array('id' => $gid));
				echo json_encode(array(1));
			}else{
				echo json_encode(array(0));
			}
		}

		if($do == 'availability')
		{
			$gname = $_REAL_POST['gn'];
			$result = $wpdb->get_row($wpdb->prepare('SELECT id FROM ' . $wpdb->prefix . 'dict_groups WHERE name = %s', $gname));

			if(empty($result)) {
				die('1');
			}
			else {
				die('0');
			}
		}

		if($do == 'students')
		{
			$gid = $_GET['gid'];

			$students = MWDB::get_group_students($gid);

			$output = array();
			foreach($students as $student) {
				$output[] = array(
					'name' => $student->display_name,
					'email' => $student->user_email,
					'joined_date' => $student->joined_date,
					'done_hw' => $student->homeworks_done
				);
			}

			echo json_encode($output);
			die;
		}
	}
	
	/*
	 * User availability
	 */
	if($task == 'availability')
	{		
		if($do == 'user')
		{
			$user_login = $_GET['user_login'];

			$user = $wpdb->get_row($wpdb->prepare('SELECT user_login FROM ' . $wpdb->users . ' WHERE user_login = %s', $user_login));

			if($user) {
				echo json_encode(array(0)); die;
			}

			$user = $wpdb->get_row($wpdb->prepare('SELECT user_login FROM ' . $wpdb->users . ' WHERE user_email = %s', $user_login));
			
			if($user) {
				echo json_encode(array(0)); die;
			}

			echo json_encode(array(1)); die;
		}
	}

	/*
	 * User info
	 */
	if($task == 'user')
	{
		if($do == 'passcheck')
		{
			$user = get_userdata( get_current_user_id() );
			if(wp_check_password( $_POST['pw'], $user->user_pass, $user->ID) ) {
				echo json_encode(array(1));
			}
			else {
				echo json_encode(array(0));
			}
			exit;
		}
	}

	/*
	 * validate creadit code
	 */
	if($task == 'validatecredit')
	{
		$credit_code = $_POST['c'];

		$code = $wpdb->get_row(
			$wpdb->prepare('SELECT c.*, us.activated_by, COUNT(activated_by) AS activated_times
							FROM ' . $wpdb->prefix . 'dict_credit_codes AS c
							LEFT JOIN ' . $wpdb->prefix . 'dict_user_subscription AS us ON us.activation_code_id = c.id
							WHERE encoded_code = %s', $_POST['c'])
		);

		if(is_null($code))
		{
			$json['status'] = 0;
			$json['title']  = __('Invalid credit code number.', 'iii-dictionary');
			$json['msg']    = __('The credit code you entered is invalid. Please enter a different one.', 'iii-dictionary');
		}
		else if($code->activated_by && ($code->typeid == 1 || $code->typeid == 3 || $code->typeid == 4))
		{
			$json['status'] = 0;
			$json['title']  = __('This credit code has been used already.', 'iii-dictionary');
			$json['msg'] 	= __('Please enter a different credit code.', 'iii-dictionary');
		}
		else if(!$code->active)
		{
			$json['status'] = 0;
			$json['title']  = __('This credit code has expired.', 'iii-dictionary');
			$json['msg'] 	= __('This credit code has already expired. Please enter a different one.', 'iii-dictionary');
		}
		else if($code->activated_times == $code->no_of_students && $code->typeid == 2)
		{
			$json['status'] = 0;
			if(is_numeric($code->activated_by)) {
				$json['title']  = __('Activation error', 'iii-dictionary');
				$json['msg'] 	= __('Number of license is used up for this activation code. Please enter a different code.', 'iii-dictionary');
			}
			else {
				$json['title']  = __('Activation notice', 'iii-dictionary');
				$json['msg'] 	= __('This activation code is already actived from Desktop app. Please use the Desktop icon to start iklearn.com.', 'iii-dictionary');
			}
		}
		else
		{
			$json['status'] = 1;
			$json['ltype']  = (int) $code->typeid;
			$json['did']  = (int) $code->dictionary_id;
			$json['size'] = $code->no_of_students;
		}

		echo json_encode($json);
		die;
	}

	/*
	 * flash cards
	 */
	if($task == 'flashcard')
	{
		$dictionary_id = get_dictionary_id_by_slug($_REAL_POST['did']);

		$is_dictionary_subscribed = is_dictionary_subscribed($dictionary_id);

		if($do == 'addfolder')
		{
			if(!$is_dictionary_subscribed) {
				die(json_encode(array(0)));
			}

			$name = $_REAL_POST['n'];

			$result = $wpdb->insert(
				$wpdb->prefix . 'dict_flashcard_folders',
				array(
					'user_id' => get_current_user_id(),
					'dictionary_id' => $dictionary_id,
					'name' => $name
				)
			);

			if($result) {
				die(json_encode(array($wpdb->insert_id)));
			}
			else {
				die(json_encode(array(0)));
			}
		}

		if($do == 'addcard')
		{
			$current_user_id = get_current_user_id();

			if(!$is_dictionary_subscribed) {
				$cards = $wpdb->get_col('SELECT COUNT(*) FROM ' . $wpdb->prefix . 'dict_flashcards WHERE created_by = ' . $current_user_id . ' AND dictionary_id = ' . $dictionary_id);

				// free user can add up to 5 flash cards
				if($cards[0] >= 5) {
					echo json_encode(array('status' => 2));
					die;
				}
			}

			$entry = $_REAL_POST['e'];
			$folder_id = $_REAL_POST['fid'];

			$result = $wpdb->insert(
				$wpdb->prefix . 'dict_flashcards',
				array(
					'created_by' => $current_user_id,
					'folder_id' => $folder_id,
					'group_id' => 0,
					'dictionary_id' => $dictionary_id,
					'word' => $entry
				)
			);

			$result2 = $wpdb->insert(
				$wpdb->prefix . 'dict_flashcard_userdata',
				array(
					'flashcard_id' => $wpdb->insert_id,
					'user_id' => $current_user_id
				)
			);

			if($result) {
				echo json_encode(array('status' => 1));
			}
			else {
				echo json_encode(array('status' => 0));
			}

			die;
		}

		if($do == 'savenotes')
		{
			$current_user_id = get_current_user_id();
			$existing = $wpdb->get_row('SELECT id FROM ' . $wpdb->prefix . 'dict_flashcard_userdata WHERE flashcard_id = ' . esc_sql($_POST['id']) . ' AND user_id = ' . $current_user_id);

			if(empty($existing)) {
				$result = $wpdb->insert(
					$wpdb->prefix . 'dict_flashcard_userdata',
					array(
						'flashcard_id' => $_POST['id'],
						'user_id' => $current_user_id,
						'notes' => $_REAL_POST['notes']
					)
				);
			}
			else {
				$result = $wpdb->update(
					$wpdb->prefix . 'dict_flashcard_userdata',
					array(
						'notes' => $_REAL_POST['notes']
					),
					array(
						'flashcard_id' => $_POST['id'],
						'user_id' => $current_user_id
					)
				);
			}

			if($result !== false) {
				die(json_encode(array(1)));
			}
			else {
				die(json_encode(array(0)));
			}
		}

		if($do == 'memorized')
		{
			$current_user_id = get_current_user_id();
			$flashcard_id = esc_sql($_POST['id']);
			$existing = $wpdb->get_row('SELECT id FROM ' . $wpdb->prefix . 'dict_flashcard_userdata WHERE flashcard_id = ' . $flashcard_id . ' AND user_id = ' . $current_user_id);

			if(isset($_POST['memorized'])) {
				$value = 1;
			}
			else {
				$value = 'ABS(memorized - 1)';
			}

			if(empty($existing)) {
				$wpdb->insert(
					$wpdb->prefix . 'dict_flashcard_userdata',
					array(
						'flashcard_id' => $flashcard_id,
						'user_id' => $current_user_id,
						'memorized' => 1
					)
				);
			}
			else {
				$wpdb->query('UPDATE ' . $wpdb->prefix . 'dict_flashcard_userdata 
							  SET memorized = ' . $value . '
							  WHERE flashcard_id = ' . $flashcard_id . ' AND user_id = ' . $current_user_id);
			}

			die;
		}

		if($do == 'delete')
		{
			$current_user_id = get_current_user_id();

			$result = $wpdb->delete(
				$wpdb->prefix . 'dict_flashcards',
				array(
					'id' => $_POST['id'],
					'created_by' => $current_user_id
				)
			);

			$wpdb->delete(
				$wpdb->prefix . 'dict_flashcard_userdata',
				array(
					'flashcard_id' => $_POST['id'],
					'user_id' => $current_user_id
				)
			);

			if($result !== false) {
				die(json_encode(array(1)));
			}
			else {
				die(json_encode(array(0)));
			}
		}

		if($do == 'lookup')
		{
			include IK_PLUGIN_DIR . '/library/formatter.php';

			$flashcard = $wpdb->get_row($wpdb->prepare('SELECT word, dictionary_id FROM ' . $wpdb->prefix . 'dict_flashcards WHERE id = %d', $_GET['id']));

			$dictionary_table = get_dictionary_table($flashcard->dictionary_id);
			
			$word = $wpdb->get_row('SELECT * FROM ' . $wpdb->prefix . $dictionary_table . ' WHERE entry = \'' . $flashcard->word . '\'');

			$html = '<div id="headword">' . WFormatter::_hw($word->headword) . '</div>' .
					'<div id="pronunciation">' .
						WFormatter::_sound($word->sound, $flashcard->dictionary_id) . 
						WFormatter::_pr($word->pronunciation) . 
						'<span class="functional-label">' . WFormatter::_fl($word->functional_label) . '</span>' .
					'</div>' . 
					'<div id="definition">' . 
						WFormatter::_def($word->definition, $flashcard->dictionary_id) .
					'</div>';

			echo $html;
			
			die;
		}
	}

	// grade api
	if($task === 'grade')
	{
		if($do === 'add')
		{
			$data['parent_id'] = $_POST['parent_id'];
			$data['name'] = $_POST['name'];
			$data['type'] = $_POST['type'];
			$data['level'] = $_POST['level'];

			if($last_id = MWDB::store_grade($data)) {
				echo $last_id;
			}
			else {
				echo '0';
			}
			exit;
		}

		if($do == 'rename')
		{
			$data['id'] = $_POST['id'];
			$data['name'] = $_REAL_POST['n'];

			if($last_id = MWDB::store_grade($data)) {
				echo $last_id;
			}
			else {
				echo '0';
			}
			exit;
		}
                
                if($do == 'changelastpage')
		{
			$data['id'] = $_POST['id'];
			$data['lastpage'] = $_REAL_POST['check'];

			if(MWDB::store_sheet_page($data)) {
				echo 'update success';
			}
			else {
				echo 'update error';
			}
			exit;
		}
                
		if($do == 'change_order')
		{
			$dir = $_POST['dir'];

			if($dir == 'up') {
				MWDB::set_grade_order_up($_POST['id']);
			}
			else if($dir == 'down') {
				MWDB::set_grade_order_down($_POST['id']);
			}
		}
	}

	if($task === 'math_worksheet')
	{
		if($do === 'get')
		{
			//check user subscription
			$flag = '';
			if(isset($_GET['lid'])) {
				//if(!is_homework_tools_subscribed() || !is_mw_super_admin() || !is_mw_admin() || !(!is_user_logged_in() && isset($_GET['ncl']) && $_GET['ncl'] < 2)) {
				if(!is_math_homework_tools_subscribed() || !is_user_logged_in()) { $flag = 'text-muted'; }
				if(is_mw_super_admin() || is_mw_admin())  { $flag = ''; }
			}
			$query = 'SELECT ms.id, sheet_name , homework_type_id
						FROM ' . $wpdb->prefix . 'dict_sheets AS ms
						JOIN ' . $wpdb->prefix . 'dict_grades AS gr ON gr.id = ms.grade_id
						JOIN (
							SELECT id, name AS level_name, parent_id AS level_parent_id 
							FROM ' . $wpdb->prefix . 'dict_grades WHERE level = 1
						) AS lgr ON lgr.id = gr.parent_id
						JOIN (
							SELECT id, name AS level_category_name 
							FROM ' . $wpdb->prefix . 'dict_grades WHERE level = 0
						) AS cgr ON cgr.id = lgr.level_parent_id';

			if(!empty($_GET['cid'])) {
				$cat_id = $_GET['cid'];
				$where[] = 'cgr.id = %d';
				$params[] = $cat_id;
			}

			if(!empty($_GET['plid'])) {
				$level_id = $_GET['plid'];
				$where[] = 'lgr.id = %d';
				$params[] = $level_id;
			}

			if(!empty($_GET['lid'])) {
				$sublevel_id = $_GET['lid'];
				$where[] = 'grade_id = %d';
				$params[] = $sublevel_id;
			}

			if(!empty($_GET['name'])) {
				$sheet_name = $_GET['name'];
				$where[] = 'sheet_name LIKE %s';
				$params[] = '%' . $sheet_name . '%';
			}

			if(!empty($_GET['exclude'])) {
				$where[] = 'ms.id <> %s';
				$params[] = $_GET['exclude'];
			}
			/*
			if(!is_math_homework_tools_subscribed()) {
				$where[] = 'homework_type_id <> ' . HOMEWORK_SUBSCRIBED;
			}
			*/

			if(!empty($where)) {
				$query .= ' WHERE ' . implode(' AND ', $where);
			}

			$query .= ' ORDER BY ms.ordering';

			$worksheets = $wpdb->get_results(
				$wpdb->prepare($query, $params)
			);
			$is_sub = get_ws_subscribed();
			$json = array();
			foreach($worksheets as $worksheet) {
				$json[] = array('sid' => $worksheet->id, 'name' => $worksheet->sheet_name, 'sub' => $flag, 'type' => $worksheet->homework_type_id, 'is' => $is_sub );
			}

			echo json_encode($json);
			exit;
		}
	}

	if($task === 'worksheet')
	{
		if($do === 'get')
		{
			$query = 'SELECT [columns]
					  FROM ' . $wpdb->prefix . 'dict_sheets AS s
					  JOIN ' . $wpdb->prefix . 'dict_grades AS gr ON gr.id = s.grade_id';

			$columns[] = 's.*, gr.name AS grade';

			$where[] = 'category_id <> 5';

			if($_GET['assignment_name']) {
				$columns[] = 'hal.name as aname';
				$query .= ' JOIN ' . $wpdb->prefix . 'dict_homework_assignment_langs AS hal ON hal.assignment_id = s.assignment_id AND hal.lang = \'' . get_short_lang_code() . '\'';
			}

			if(!empty($_GET['name'])) {
				$sheet_name = $_GET['name'];
				$where[] = 'sheet_name LIKE %s';
				$params[] = '%' . $sheet_name . '%';
			}

			if(!empty($_GET['assignment'])) {
				$where[] = 'assignment_id = %d';
				$params[] = $_GET['assignment'];
			}

			if(!empty($_GET['type'])) {
				$where[] = 'homework_type_id = %d';
				$params[] = $_GET['type'];
			}

			if(!empty($_GET['grade'])) {
				$where[] = 'gr.name = %d';
				$params[] = $_GET['grade'];
			}

			if(!empty($_GET['exclude'])) {
				$where[] = 's.id <> %s';
				$params[] = $_GET['exclude'];
			}

			if(!empty($where)) {
				$query .= ' WHERE ' . implode(' AND ', $where);
			}

			$query = str_replace('[columns]', implode(',', $columns), $query);

			$worksheets = $wpdb->get_results(
				$wpdb->prepare($query, $params)
			);

			$json = array();
			foreach($worksheets as $worksheet) {
				$item = array('sid' => $worksheet->id, 'name' => $worksheet->sheet_name);
				if($_GET['assignment_name']) {
					$item['aname'] = $worksheet->aname;
				}
				if($_GET['grade_name']) {
					$item['grade'] = $worksheet->grade;
				}
				$json[] = $item;
			}
			
			echo json_encode($json);
			exit;
		}
	}
	if($task === 'worksheetmath')
	{
		if($do === 'get')
		{
			$query = 'SELECT [columns]
					  FROM ' . $wpdb->prefix . 'dict_sheets AS s
					  JOIN ' . $wpdb->prefix . 'dict_grades AS gr ON gr.id = s.grade_id';

			$columns[] = 's.*, gr.name AS grade';

			$where[] = 'category_id = 5';

			if($_GET['assignment_name']) {
				$columns[] = 'hal.name as aname';
				$query .= ' JOIN ' . $wpdb->prefix . 'dict_homework_assignment_langs AS hal ON hal.assignment_id = s.assignment_id AND hal.lang = \'' . get_short_lang_code() . '\'';
			}

			if(!empty($_GET['name'])) {
				$sheet_name = $_GET['name'];
				$where[] = 'sheet_name LIKE %s';
				$params[] = '%' . $sheet_name . '%';
			}

			if(!empty($_GET['assignment'])) {
				$where[] = 'assignment_id = %d';
				$params[] = $_GET['assignment'];
			}

			if(!empty($_GET['type'])) {
				$where[] = 'homework_type_id = %d';
				$params[] = $_GET['type'];
			}

			if(!empty($_GET['grade'])) {
				$where[] = 'gr.name = %d';
				$params[] = $_GET['grade'];
			}

			if(!empty($_GET['exclude'])) {
				$where[] = 's.id <> %s';
				$params[] = $_GET['exclude'];
			}

			if(!empty($where)) {
				$query .= ' WHERE ' . implode(' AND ', $where);
			}

			$query = str_replace('[columns]', implode(',', $columns), $query);

			$worksheets = $wpdb->get_results(
				$wpdb->prepare($query, $params)
			);

			$json = array();
			foreach($worksheets as $worksheet) {
				$item = array('sid' => $worksheet->id, 'name' => $worksheet->sheet_name);
				if($_GET['assignment_name']) {
					$item['aname'] = $worksheet->aname;
				}
				if($_GET['grade_name']) {
					$item['grade'] = $worksheet->grade;
				}
				$json[] = $item;
			}
			
			echo json_encode($json);
			exit;
		}
	}
	if($task == 'mw_download') {
		$is_login = $_GET['is_login'];
		if($is_login == 0) {
			$json['status'] = 0;
		}else {
			$json['status'] = 1;
		}
		echo json_encode($json);
		exit;
	}
	
	if($task == 'status_msg') {
		global $wpdb;
		$id = $_POST['id'];
		if($id != 0) {
			$result = $wpdb->query(
				'UPDATE ' . $wpdb->prefix . 'dict_private_message_inbox 
				SET status = 1 WHERE id = ' . $id
			);
		}
		exit;
	}
	
	if($task == 'get_sub_dic') {
		$sub_folder = $_POST['sub'];
		$html = '<option value="" >'  . __('Select a directory', 'iii-dictionary') . '</option>';
		if(!empty($sub_folder)) {
			foreach(glob($sub_folder .'/*', GLOB_ONLYDIR) as $data)  {
				$selected = ($_SESSION['media']['sub-dic'] == basename($data)) ? 'selected' : '';
				$html .= '<option value="'. basename($data) .'"' . $selected .'>'  . basename($data) . '</option>';
			}
		}
		
		echo $html;
		exit;
	}
	
	if($task == 'chat') {
		if($do === 'request') {
			global $wpdb;
			$_sheet_id 		= $_POST['sid'];
			$_user_id 		= $_POST['id'];
			
			$_points 		= ik_get_user_points($_user_id);
			$_price_chat 	= mw_get_option('math-chat-price');
			$_return 		= (isset($_POST['return'])) ? trim($_POST['return']) : '';
			
			if($_points < $_price_chat) {
				$html  = '<div class="col-md-12 block-respone-content">'. __('Sorry, you do not have enough points for this session', 'iii-dictionary') .'</div>';
				$html .= '<div class="col-md-12 block-respone-question">' . __('Would you like to purchase points now?', 'iii-dictionary') . '</div>';
				$html .= '<div class="col-md-12 block-popup-btn"><div class="row">';
				$html .= '<div class="col-md-6"><button name="btn-purchase-points" type="submit" form="main-form" class="btn-popup-style">'. __('Yes', 'iii-dictionary') .'</button></div>';
				$html .= '<div class="col-md-6"><button class="btn-popup-style btn-close-bp '. $_check .'">'. __('No', 'iii-dictionary') .'</button></div>';
				$html .= '<input form="main-form" type="hidden" name="return-math" value="' . $_return . '" />';
				$html .= '</div></div>';
			}else {
				$data  = array( 
					'sheet_id' 		=> $_sheet_id,
					'user_id'  		=> $_user_id,
					'teacher_id' 	=> 0,
					'price' 		=> $_price_chat,
					'datetime' 		=> date('Y-m-d', time()),
					'url' 			=> $_return,
					'status' 		=> 0
				);
				$check_exists =  $wpdb->get_results('SELECT * FROM '. $wpdb->prefix .'dict_chat_session AS dcs 
												WHERE dcs.sheet_id = ' . esc_sql($_sheet_id) . ' AND dcs.user_id = ' . esc_sql($_user_id) .' AND dcs.status != 2');
				
				if(count($check_exists) == 0) {
					$result = $wpdb->insert($wpdb->prefix . 'dict_chat_session', $data);
				}
		
				switch($check_exists[0]->status) {
					case 1  :
						$teacher = get_userdata($check_exists[0]->teacher_id);
						$html  .= '<div id="block-start">';
						$html  .= '<div class="col-md-12">'. __('A teacher, ', 'iii-dictionary') . '' . $teacher->user_email . '' . __(' has responded.', 'iii-dictionary');
						$html  .= '<div class="col-md-12">'. __('Would you like to start the tutoring now ?', 'iii-dictionary') .'</div>';
						$html  .= '<div class="col-md-6 col-md-offset-6"><button data-teacher="'. $teacher->user_email .'" id="start-session" class="btn-popup-style">'. __('Start', 'iii-dictionary') .'</button></div>';
						$html  .= '</div>';
					break;
					case 2  :
					break;
					default :
						$html  	= '<div class="col-md-12 block-respone-content">'. __('Your request has been sent to the teacher\'s panel.', 'iii-dictionary') .'</div>';
						$html  .= '<div class="col-md-12 block-respone-wait">' . __('Please wait until a teacher responses', 'iii-dictionary') . '</div>';
						$html  .= '<div class="col-md-6 col-md-offset-6"><button class="btn-popup-style btn-cancel-session">'. __('No', 'iii-dictionary') .'</button></div>';
					break;
				}
				//store request chat to database
			}
			echo $html;
			exit;
		}
		
		if($do === 'notice') {
			$id = $_POST['id'];
			switch($id) {
                                case 7 :
					$_id = ( $_POST['_id'] ) ? $_POST['_id'] : ''; 
                                        $wpdb->update( $wpdb->prefix . 'dict_chat_session', array('room' => 2), array('id' => $_id) );
					break;
				case 2 : 
					$html  = '<div class="col-md-12 block-respone-content">'. __('Sorry, you do not have enough points for this session', 'iii-dictionary') .'</div>';
					$html .= '<div class="col-md-12 block-respone-question">' . __('Would you like to purchase points now?', 'iii-dictionary') . '</div>';
					$html .= '<div class="col-md-12 block-popup-btn"><div class="row">';
					$html .= '<div class="col-md-6"><button name="btn-purchase-points" type="submit" form="main-form" class="btn-popup-style">'. __('Yes', 'iii-dictionary') .'</button></div>';
					$html .= '<div class="col-md-6"><button class="btn-popup-style btn-quit">'. __('No', 'iii-dictionary') .'</button></div>';
					$html .= '<input form="main-form" type="hidden" name="return-math" value="' . $_POST['return'] . '" />';
					$html .= '</div></div>';
					break;
				case 1 :
					$html  = '<p style="padding-left: 20%; ">Do you want to quit tutoring?</p>';
                                        break;
				case 0 :
					$is_con = ( $_POST['is_con'] ) ? 'not_enough' : 'continue_session'; 
					$html  = '<div class="col-md-12 block-continue-content">'. __('Do you want continue this session ?', 'iii-dictionary') .'</div>';
					$html .= '<div class="col-md-6"><button class="btn-popup-style '. $is_con .'">'. __('Yes', 'iii-dictionary') .'</button></div>';
					$html .= '<div class="col-md-6"><button class="btn-popup-style btn-quit">'. __('No', 'iii-dictionary') .'</button></div>';
					break;
			}
			
			echo $html;
		}
		if($do === 'update_quit_status') {
			global $wpdb;
			$_id = ( $_POST['_id'] ) ? $_POST['_id'] : ''; 
			$id = ( $_POST['id'] ) ? $_POST['id'] : ''; 
                        $wpdb->update( $wpdb->prefix . 'dict_chat_session', array('quit_status' => $id), array('id' => $_id) );
		}
		if($do === 'update_session') {
			global $wpdb;
			$data =	array(
				'teacher_id' 	=> $_POST['teacher_id'],
				'status' 		=> 1
			);
			$result = $wpdb->update($wpdb->prefix . 'dict_chat_session', $data, array('id' => $_POST['id']));
			echo $result;
			exit;
		}
		
		if($do === 'insert_history') {
			global $wpdb;
			$data = array( 
				'from_id' 	=> $_POST['from_id'],
				'to_id' 	=> $_POST['to_id'],
				'from_time' => $_POST['from_time'],
				'content' 	=> $_POST['content'],
				'room' 		=> $_POST['room'],
			);
			$chat_session =  $wpdb->get_row('SELECT room FROM '. $wpdb->prefix .'dict_chat_session WHERE id = ' . esc_sql($_POST['idroom']));
			if($chat_session->room==0){
                            $wpdb->insert($wpdb->prefix . 'dict_chat_history', $data);
                            echo $wpdb->insert_id;
                            exit;
                        }else{
                            echo 1;
                            exit;
                        }
		}
		
		if($do === 'update_history') {
			global $wpdb;
			$id = (filter_var($_POST['id'], FILTER_VALIDATE_INT)) ? $_POST['id'] : 0;
			$wpdb->update( $wpdb->prefix . 'dict_chat_history', array('to_time' => $_POST['to_time']), array('id' => $id) );
			
			echo $id;
			exit;
		}
		
		if($do === 'get_history') {
			global $wpdb;
			$html 	= '';
			$id 	= (filter_var($_POST['id'], FILTER_VALIDATE_INT)) ? $_POST['id'] : 0;
			$room 	= $_POST['room'];
			$student = $_POST['idstudent'];
			$teacher= $_POST['idteacher'];
			
			$results = $wpdb->get_results('SELECT * FROM '. $wpdb->prefix .'dict_chat_history AS dch WHERE dch.room = ' . esc_sql($room). ' ORDER  BY id DESC');
//                        if($results[0]->from_id == $student){
//                                $html .= '<span class="wplc-user-message" style="text-decoration: none;"> Student : '.  get_user_by('id', $results[0]->from_id)->user_nicename .'</span><div class="wplc-clear-float-message"></div>';
//                                $html .= '<span class="wplc-admin-message " style="text-decoration: none;"> Tutor : '.  get_user_by('id', $results[0]->to_id)->user_nicename .'</span><br /><div class="wplc-clear-float-message"></div>';
//                        }else{
//                                $html .= '<span class="wplc-user-message " style="text-decoration: none;"> Tutor : '.  get_user_by('id', $results[0]->to_id)->user_nicename .'</span><div class="wplc-clear-float-message"></div>';
//                                $html .= '<span class="wplc-admin-message "  style="text-decoration: none;"> Student : '.  get_user_by('id', $results[0]->from_id)->user_nicename .'</span><br /><div class="wplc-clear-float-message"></div>';
//                            
//                        }
                        foreach($results AS $data) {
				if($data->from_id == $id){
                                    $html .= '<span class="wplc-user-message ">Tutor : '. wp_unslash($data->content) . '</span><div class="wplc-clear-float-message"></div>';
				}else {
                                    $html .= '<span class="wplc-admin-message  "><strong></strong>Student : '. wp_unslash($data->content) . '</span><br /><div class="wplc-clear-float-message"></div>';
				}
			}
			echo $html;
			exit;
		}
		
		if($do === 'start_session') {
			global $wpdb;
			$sid 	= (filter_var($_POST['sid'], FILTER_VALIDATE_INT)) ? $_POST['sid'] : 0;
			$uid 	= (filter_var($_POST['uid'], FILTER_VALIDATE_INT)) ? $_POST['uid'] : 0;
			
			$result = $wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'dict_chat_session WHERE sheet_id = '. $sid .' AND user_id= ' . $uid);
			
			echo ($result > 0) ? true : false;
			exit;
		}
		
		if($do === 'update_points') {
			global $wpdb;
			$uid 			= (filter_var($_POST['uid'], FILTER_VALIDATE_INT)) ? $_POST['uid'] : 0;
			$tid 			= (filter_var($_POST['tid'], FILTER_VALIDATE_INT)) ? $_POST['tid'] : 0;
			$points 		= ik_get_user_points($uid);
			$t_points 		= ik_get_user_points($tid);
			$price 			= mw_get_option('math-chat-price');
			if($points >=  $price) {
				$update_points 		= $points - $price;
				$t_update_points 	= $_points + $price;
				update_user_meta($uid, 'user_points', $update_points);
				update_user_meta($tid, 'user_points', $t_update_points);
				echo  $update_points;
				exit;
			}
			echo 0;
			exit;
		}
		
		if($do === 'clear_session') {
			global $wpdb;
			$sid 	= (filter_var($_POST['sid'], FILTER_VALIDATE_INT)) ? $_POST['sid'] : 0;
			$uid 	= (filter_var($_POST['uid'], FILTER_VALIDATE_INT)) ? $_POST['uid'] : 0;
			
			$wpdb->update( $wpdb->prefix . 'dict_chat_session', array('status' => 2) , array('sheet_id' => $sid, 'user_id' => $uid) );
			
			echo 1;
			exit;
		}
		
		if($do === 'cancel_session') {
			global $wpdb;
			$sid 	= (filter_var($_POST['sid'], FILTER_VALIDATE_INT)) ? $_POST['sid'] : 0;
			$uid 	= (filter_var($_POST['uid'], FILTER_VALIDATE_INT)) ? $_POST['uid'] : 0;
			
			$wpdb->delete( $wpdb->prefix . 'dict_chat_session', array('sheet_id' => $sid, 'user_id' => $uid) );
			
			echo 1;
			exit;
		}
	}
        if($task=="set-ordering") {
            global $wpdb;
            $id = $_REQUEST["id"];
            $number = $_REQUEST["number"];
            $order = MWDB::get_order_sheet($id);
//            echo($order->ordering) ;die; 
            $result = $wpdb->query(
                    'UPDATE ' .$wpdb->prefix.'dict_sheets SET ordering = '.$number.' WHERE id='.$id
            );
            exit;
        }