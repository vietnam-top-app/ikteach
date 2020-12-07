<?php
	// for chat module
	wp_enqueue_script('nodejs-socket', 'http://166.62.122.90:8000/socket.io/socket.io.js', array(), '1.0.0', true );
	wp_enqueue_script('html5canvas', plugins_url('iii-dictionary/chat/pad/lib/js/html2canvas/dist/html2canvas.js'), array(), '1.0.0', true );
	wp_enqueue_script('splitter', plugins_url('iii-dictionary/chat/js/jquery.splitter-0.14.0.js'), array(), '1.0.0', true );
	wp_enqueue_script('notepad', plugins_url('iii-dictionary/chat/js/appChatmain.js'), array(), '1.0.0', true );
	wp_enqueue_style('chat-main-css', plugins_url('iii-dictionary/chat/css/chatMain.css'));
	wp_enqueue_script('chat-main-js', plugins_url('iii-dictionary/chat/js/chatMain.js'), array(), '1.0.0', true );

	//////////////////////////////////////////////////
	$sheet_id = empty($_GET['sid']) ? 0 : $_GET['sid'];
	$curr_mode = empty($_GET['mode']) ? 'practice' : $_GET['mode'];
	$nav_li_class = array();
	$is_next_homework = 0;
	/// techer take math test
	$teacher_taking_test = in_array($_GET['hid'], (array) $_SESSION['teacher_math_tests']);
	$current_user = wp_get_current_user();
	//get result of teacher if pass first test
	
	$math_filter['group_id'] 			= mw_get_option('teacher-math-test-group');
	$math_filter['check_result'] 		= true;
	$math_filter['user_id_result'] 		= $current_user->ID;
	$math_tests 						= MWDB::get_homework_assignments($math_filter);
	$math_test1_result 					= MWDB::get_homework_results($math_tests->items[0]->id, $current_user->ID);
	$score_threshold 					= mw_get_option('teacher-math-test-score-threshold');
	$pass = ($teacher_taking_test && ($score_threshold <= $math_test1_result[0]->score)) ? 1 : 0;
	$_is = ( is_mw_qualified_teacher() || is_mw_admin() || is_mw_super_admin()) ? true : false; 
	
	// check homework id
	if(!empty($_GET['hid'])) {
		$homework_assignment = MWDB::get_homework_assignment_by_id($_GET['hid']);
		$sheet_id = $homework_assignment->sheet_id;
	}
	

	$homework = MWDB::get_math_sheet_by_id($sheet_id);

	// if user try to go to worksheet page directly, check if the worksheet is subscribed one and user can view it
	if($homework->homework_type_id == HOMEWORK_SUBSCRIBED && !is_math_homework_tools_subscribed()) {
		wp_redirect(locale_home_url());
		exit();
	}

	if(!empty($homework_assignment)) {
		$homework_assignment->next_sheet = !empty($homework_assignment->name) ? $homework_assignment->name : $homework->sheet_name;
		if(!empty($homework_assignment->next_homework_id)) {
			$get_next_homework = MWDB::get_homework_assignment_by_id($homework_assignment->next_homework_id);
			$is_next_homework  = $get_next_homework->for_practice;
		}
	}
	
	$questions = json_decode($homework->questions, true);
	
	//store practice result
	if(!empty($_GET['hid']) && $curr_mode == 'practice' ) {
		$data['sheet_id'] 		= $sheet_id;
		$data['user_id'] 		= get_current_user_id();
		$data['practice_id'] 	= is_numeric($_GET['hid']) ? $_GET['hid'] : 0;
		MWDB::_store_math_practice($data);
	}
	/*
	if(empty($_GET['hid']) && $teacher_taking_test) {
		$homework_assignment 					= new stdClass;
		$filter['group_id'] 					= mw_get_option('teacher-math-test-group');
		$filter['sheet_id'] 					= $sheet_id;
		$data 									= MWDB::get_homework_assignments($filter);
		$homework_assignment->id 				= $data->items[0]->id;
		$homework_assignment->assignment_id 	= $data->items[0]->assignment_id;
		$homework_assignment->rid				= MWDB::get_homework_results($homework_assignment->id, $current_user->ID);
	}
	*/
	if(isset($_POST['submit-homework'])) {
		$submitted_on = date('Y-m-d', time());
		$correct_answers_count = 0;
	
		// checking answer and calculate score
		switch($homework_assignment->assignment_id) {
			case MATH_ASSIGNMENT_SINGLE_DIGIT:
			case MATH_ASSIGNMENT_TWO_DIGIT_MUL:
			case MATH_ASSIGNMENT_SINGLE_DIGIT_DIV:
			case MATH_ASSIGNMENT_TWO_DIGIT_DIV:
				$score = 100;
				foreach($questions['step'] as $key => $value) {
					if(implode('', $_POST['result'][$key]) != str_replace('@', '', $questions['step'][$key])) {
						$score = 0;
					}
				}

				if($score == 100){
					$correct_answers_count = 1;
                                }
				break;

			case MATH_ASSIGNMENT_FLASHCARD:
				$score = 0;
				$score_per_question = 100 / count($questions['q']);
				foreach($_POST['result'] as $key => $value) {
					if($value == $questions['q'][$key]['answer']) {
						$score += $score_per_question;
						$correct_answers_count++;
					}
				}
				break;

			case MATH_ASSIGNMENT_FRACTION:
				$score = 0;
				$score_per_question = 100 / count($questions['q']);
				foreach($_POST['result'] as $key => $value) {
					if(implode('/', $value) == str_replace(' ', '/', $questions['q'][$key]['answer'])) {
						$score += $score_per_question;
						$correct_answers_count++;
					}
				}
				break;

			case MATH_ASSIGNMENT_WORD_PROB:
				$score = $correct_answers_count = 0;
				
				if($_POST['result'] == $questions['answer']) {
					$score = 100;
					$correct_answers_count = 1;
				}  
				break;

			case MATH_ASSIGNMENT_QUESTION_BOX:
			case MATH_ASSIGNMENT_EQUATION:
				$score = 0;
				$score_per_question = 100 / count($_POST['result']);
				foreach($_POST['result'] as $key => $value) {
					if(compare_fraction($value, $questions['q'][$key]['answer'])) {
						$score += $score_per_question;
						$correct_answers_count++;
					}
				}
				break;

			/* case MATH_ASSIGNMENT_EQUATION:
				$score = 0;
				$score_per_question = 100 / count($_POST['result']);
				foreach($_POST['result'] as $key => $value) {
					if($value == $questions['q'][$key]['answer']) {
						$score += $score_per_question;
						$correct_answers_count++;
					}
				}
				break; */
		}
		
		$data = array(
			'userid' => get_current_user_id(), 
			'homework_id' => $homework_assignment->id,
			'answers' => json_encode($_POST['result']),
			'score' => $score,
			'correct_answers_count' => $correct_answers_count,
			'attempted_on' => $submitted_on,
			'submitted_on' => $submitted_on,
			'finished_question' => 1,
			'finished' => 1,
			'graded' => 1,
			'message' => $_REAL_POST['feedback']
		);
		
		//update role of user if pass test
		if($score >= $score_threshold && $_POST['pass'] == 1) {
			$user = get_user_by('id', $current_user->ID);
			$user->add_role('mw_qualified_math_teacher');
		}
		
		$result = (isset($rid) && !empty($rid->homework_result_id)) ? MWDB::manually_grade_homework($data, $rid->homework_result_id) : MWDB::auto_grade_homework($data) ;
		
		if($result) {
			ik_enqueue_messages('Homework Submitted.', 'success');
                        if($score >= $score_threshold ) {
                            ik_enqueue_messages('You correctly answer '.$score.'%', 'success');
                        }  else {
                            ik_enqueue_messages('Sorry, you correctly answered '.$score.'%', 'failed');
                        }
			wp_redirect($_POST['ref']);
			die;
		}

		ik_enqueue_messages('An error occurred, cannot submit homework.', 'error');
	}

	$is_word_prob_assignment = $homework->assignment_id == MATH_ASSIGNMENT_WORD_PROB;
	//var_dump($questions, $homework);die;
	
	//section for chat
	if(isset($_POST['btn-purchase-points'])) {
		$_SESSION['method_point']  	= 1;
		$_SESSION['return_math'] 	= locale_home_url() . '/?' . $_POST['return-math'];
		
		wp_redirect(locale_home_url() . "/?r=manage-subscription");
	}
	
?>
<?php get_math_header(__('Arithmetics', 'iii-dictionary')) ?>
<script>
    var CURRENTNAME = "<?php echo wp_get_current_user()->user_nicename!=null ? wp_get_current_user()->user_nicename:'null' ;?>";
</script>
<section>
	<header class="col-xs-9 col-sm-10 article-header math-homework-header">
		<h4 class="page-subtitle"><?php echo $homework->level_category_name ?></h4>
		<h2 class="page-title arithmetics" itemprop="headline"><?php echo $homework->level_name . ', ' . $homework->sublevel_name ?></h2>
		<p class="math-question"><?php echo $questions['question'] ?></p>
	</header>
	<div class="col-xs-3 col-sm-2 math-homework-sounds">
		<?php if($is_word_prob_assignment) : ?>
			<span class="math-sounds-player ">
				<span class="sounds-switch" id="speaker-button"></span>
			</span>
		<?php endif ?>
	</div>

	<div class="col-xs-12 math-homework-body">
		<div class="row">
			<form id="main-form" method="post" action="<?php echo locale_home_url() . '/?r=math-homework&amp;mode=' . $curr_mode; echo !empty($_GET['hid']) ? '&amp;hid=' . $_GET['hid'] : ''; echo !empty($_GET['ref']) ? '&amp;ref=' . $_GET['ref'] : '' ?>">
				<div class="col-sm-2 homework-nav">
					<?php

					switch($homework->assignment_id) :

						case MATH_ASSIGNMENT_SINGLE_DIGIT:
						case MATH_ASSIGNMENT_TWO_DIGIT_MUL:
						case MATH_ASSIGNMENT_SINGLE_DIGIT_DIV:
						case MATH_ASSIGNMENT_TWO_DIGIT_DIV:

							if($curr_mode == 'homework'){
								$nav_li_class[] = 'not-active visited';
							}

							$_prev = 'empty';
							foreach($questions['step'] as $k => $v) {
								if(isset($v) && $v != '') {
									if(in_array($homework->assignment_id, array(MATH_ASSIGNMENT_SINGLE_DIGIT_DIV, MATH_ASSIGNMENT_TWO_DIGIT_DIV))) {
										$no_steps[] = substr($k, 1);
									}
									else {
										// check the case both ops are single digit
										if(substr($k, 1) % 2 != 0) {
											$no_steps[] = substr($k, 1);
										}
										else if(strlen($_prev) > 1) {
											$no_steps[] = substr($k, 1);
										}
										$_prev = str_replace('@', '', $v);
									}
								}
							} ?>

							<h5 class="nav-title"><?php _e('Steps:', 'iii-dictionary') ?></h5>
							<div class="scroll-list-v" style="max-height: 380px">
								<ul class="nav-items" id="answer-steps">
									<?php
										$loop_step = 1;
										$loop_count = count($no_steps);
										if(in_array($homework->assignment_id, array(MATH_ASSIGNMENT_SINGLE_DIGIT_DIV, MATH_ASSIGNMENT_TWO_DIGIT_DIV))) {
											$loop_step = 2;
											$loop_count = $loop_count % 2 == 0 ? $loop_count : $loop_count - 1;
										}
										$li_count = 1;
										for($i = 0; $i < $loop_count; $i = $i + $loop_step) : 
											if($i == count($no_steps) - 1) {
												$nav_li_class[] = 'nlast';
											}
											?><li data-n="<?php echo $no_steps[$i] ?>"<?php echo !empty($nav_li_class) ? ' class="' . implode(' ', $nav_li_class) . '"' : '' ?>><?php echo $li_count ?></li><?php
										$li_count++; endfor ?>
								</ul>
							</div>

						<?php break; // end add, sub, mul, div assignment

						case MATH_ASSIGNMENT_FLASHCARD:
						case MATH_ASSIGNMENT_FRACTION:
						case MATH_ASSIGNMENT_EQUATION: ?>

							<h5 class="nav-title"><?php _e('Question:', 'iii-dictionary') ?></h5>
							<div class="scroll-list-v" style="max-height: 380px">
								<ul class="nav-items" id="question-nav">
									<?php for($i = 1; $i <= count($questions['q']); $i++) :
										?><li data-n="<?php echo $i ?>"<?php echo $homework->assignment_id == MATH_ASSIGNMENT_FLASHCARD && $homework->answer_time_limit ? ' class="not-active"' : '' ?>><?php echo $i ?></li><?php
									endfor ?>
								</ul>
							</div>

						<?php break; // end flash card, fraction assignment

						case MATH_ASSIGNMENT_WORD_PROB: 
							foreach($questions['q'] as $key => $item) {
								if(empty($item['image']) || trim($item['image']) == '') {
									unset($questions['q'][$key]);
								}
							} ?>

							<h5 class="nav-title"><?php _e('Steps:', 'iii-dictionary') ?></h5>
							<div class="scroll-list-v" style="max-height: 380px">
								<ul class="nav-items" id="step-nav">
									<?php for($i = 1; $i <= count($questions['q']); $i++) :
										?><li data-n="<?php echo $i ?>" data-ctrl="<?php echo $questions['q']['q' . $i]['param']?>"<?php echo !empty($nav_li_class) ? ' class="' . implode(' ', $nav_li_class) . '"' : '' ?>><?php echo $i ?></li><?php
									endfor ?>
								</ul>
							</div>
						<?php break; // end word problem assignment

						case MATH_ASSIGNMENT_QUESTION_BOX: 
							foreach($questions['q'] as $key => $item) {
								if(empty($item['answer']) || trim($item['answer']) == '') {
									unset($questions['q'][$key]);
								}
							} ?>

							<h5 class="nav-title"><?php _e('Steps:', 'iii-dictionary') ?></h5>
							<div class="scroll-list-v" style="max-height: 380px">
								<ul class="nav-items" id="qbox-step-nav">
									<?php for($i = 1; $i <= count($questions['q']); $i++) :
										?><li data-n="<?php echo $i ?>"><?php echo $i ?></li><?php
									endfor ?>
								</ul>
							</div>

						<?php break; // end question box
					endswitch ?>

				</div>
				<div class="col-sm-10 homework-content math-type-<?php echo $homework->assignment_id ?>" id="homework-content">

					<?php switch($homework->assignment_id) :

						case MATH_ASSIGNMENT_SINGLE_DIGIT: ?>

							<?php MWHtml::math_digit_box($questions['op1']) ?>
							<?php MWHtml::math_digit_box($questions['op2'], $questions['sign'], strlen($questions['op1']) - strlen($questions['op2'])) ?>
							<hr class="hr-formula hr-num-4">
							<?php MWHtml::math_answer_box($questions['step']['s1'], 1, 'result[s1]') ?>
							<?php MWHtml::math_answer_box($questions['step']['s2'], 2, 'result[s2]') ?>
							<hr class="hr-formula hr-num-4">
							<?php MWHtml::math_answer_box($questions['step']['s3'], 3, 'result[s3]') ?>

						<?php break; // end single digit add, sub and mul

						case MATH_ASSIGNMENT_TWO_DIGIT_MUL: ?>

							<?php MWHtml::math_digit_box($questions['op1']) ?>
							<?php MWHtml::math_digit_box($questions['op2'], 'x', strlen($questions['op1']) - strlen($questions['op2'])) ?>
							<hr class="hr-formula hr-num-4">
							<?php for($i = 1; $i <= 4; $i++) : ?>
								<?php MWHtml::math_answer_box($questions['step']['s' . $i], $i, 'result[s' . $i . ']') ?>
							<?php endfor ?>
							<hr class="hr-formula hr-num-5">
							<?php MWHtml::math_answer_box($questions['step']['s5'], 5, 'result[s5]') ?>
							<?php MWHtml::math_answer_box($questions['step']['s6'], 6, 'result[s6]') ?>
							<hr class="hr-formula hr-num-5">
							<?php MWHtml::math_answer_box($questions['step']['s7'], 7, 'result[s7]') ?>

						<?php break; // end two digit mul

						case MATH_ASSIGNMENT_SINGLE_DIGIT_DIV:
						case MATH_ASSIGNMENT_TWO_DIGIT_DIV: ?>

							<?php $last_step = count($no_steps);
								  MWHtml::math_answer_box($questions['step']['s' . $last_step], $last_step, 'result[s' . $last_step . ']') ?>
							<?php MWHtml::math_digit_box_division($questions['op1'], $questions['op2']) ?>
							<?php for($i = 1; $i <= $last_step - 2; $i++) : ?>
								<?php MWHtml::math_answer_box($questions['step']['s' . $i], $i, 'result[s' . $i . ']') ?>
							<?php endfor ?>
							<hr class="hr-formula hr-num-2">
							<?php $remainder_step = $last_step - 1;
								  MWHtml::math_answer_box($questions['step']['s' . $remainder_step], $remainder_step, 'result[s' . $remainder_step . ']') ?>
							<script>var answer_step_num = <?php echo $last_step ?>;</script>

						<?php break; // end single and two digit division

						case MATH_ASSIGNMENT_FLASHCARD: ?>

							<?php foreach($questions['q'] as $key => $item) : ?>
								<div class="flashcard-question hidden" id="flashcard-<?php echo $key ?>">
									<span class="math-number"><?php echo $item['op1'] ?></span>
									<span class="math-number"><?php echo str_replace('247','&divide;',$item['op']); ?></span>
									<span class="math-number"><?php echo $item['op2'] ?></span>
									<span class="math-number">=</span>
									<span class="math-number input-box"><input data-answer="<?php echo $item['answer'] ?>" name="result[<?php echo $key ?>]" type="text" class="answer-box" autocomplete="off"></span>
									<span class="math-number"><?php echo $item['note'] ?></span>
								</div>
							<?php endforeach ?>

						<?php break; // end flashcard assignment

						case MATH_ASSIGNMENT_FRACTION: ?>

							<?php foreach($questions['q'] as $key => $item) : ?>
								<div class="flashcard-question" id="flashcard-<?php echo $key ?>">
									<?php
										$_f = explode('/', $item['op1']);
										$_lf = explode(' ', $_f[0]); $_lf = count($_lf) == 2 ? $_lf : array('', $_lf[0]);
										$op1 = array($_lf[0], $_lf[1], $_f[1]);

										$_f = explode('/', $item['op2']);
										$_lf = explode(' ', $_f[0]); $_lf = count($_lf) == 2 ? $_lf : array('', $_lf[0]);
										$op2 = array($_lf[0], $_lf[1], $_f[1]);

										$_f = explode('/', $item['answer']);
										$_lf = explode(' ', $_f[0]); $_lf = count($_lf) == 2 ? $_lf : array('', $_lf[0]);
										$answer = array($_lf[0], $_lf[1], $_f[1]);
										?>
									<?php if(!empty($op1[0])) : ?>
										<div class="fraction left-number">
											<span class="math-number fraction-answer"><?php echo $op1[0] ?></span>
										</div>
									<?php endif;
									if(str_replace('247','&divide;',$item['op']) != ''): ?>
									<div class="fraction">
										<span class="math-number fraction-answer"><?php echo $op1[1] ?></span>
										<span class="icon-fraction fraction-answer"></span>
										<span class="math-number fraction-answer"><?php echo $op1[2] ?></span>
									</div>
									<div class="fraction">
										<span class="math-number">&nbsp;</span>
										<span class="sign"><?php echo str_replace('247','&divide;',$item['op']); ?></span>
										<span class="math-number">&nbsp;</span>
									</div>
									<?php 
									endif;
									if(!empty($op2[0])) : ?>
										<div class="fraction left-number">
											<span class="math-number fraction-answer"><?php echo $op2[0] ?></span>
										</div>
									<?php endif ?>
									<div class="fraction">
										<span class="math-number fraction-answer"><?php echo $op2[1] ?></span>
										<span class="icon-fraction fraction-answer"></span>
										<span class="math-number fraction-answer"><?php echo $op2[2] ?></span>
									</div>
									<div class="fraction">
										<span class="math-number">&nbsp;</span>
										<span class="sign">=</span>
										<span class="math-number">&nbsp;</span>
									</div>
									<?php if(!empty($answer[0])) : ?>
										<div class="fraction left-number">
											<span class="math-number input-box fraction-answer"><input data-answer="<?php echo $answer[0] ?>" autocomplete="off" name="result[<?php echo $key ?>][]" type="text" class="answer-box"></span>
										</div>
									<?php endif ?>
									<div class="fraction<?php echo empty($answer[2]) ? ' single' : '' ?>">
										<span class="math-number input-box fraction-answer"><input data-answer="<?php echo $answer[1] ?>" autocomplete="off" name="result[<?php echo $key ?>][]" type="text" class="answer-box"></span>
										<?php if(!empty($answer[2])) : ?>
											<span class="icon-fraction fraction-answer"></span>
											<span class="math-number input-box fraction-answer"><input data-answer="<?php echo $answer[2] ?>" autocomplete="off" name="result[<?php echo $key ?>][]" type="text" class="answer-box"></span>
										<?php endif ?>
									</div>
								</div>
							<?php endforeach ?>

						<?php break; // end fraction assignment

						case MATH_ASSIGNMENT_WORD_PROB: ?>

							<?php foreach($questions['q'] as $key => $item) : ?>
								<img src="<?php echo MWHtml::math_image_url($item['image']) ?>" alt="" id="word-prob-step-<?php echo $key ?>" class="word-prob-steps canvas-layer" data-img-src="<?php echo MWHtml::math_image_url($item['image']) ?>">
								<?php if(!empty($item['sound'])) : ?>
									<audio class="word-prob-sound" id="word-prob-sound-<?php echo $key ?>" preload="auto"><source src="<?php echo MWHtml::math_sound_url($item['sound']) ?>" type="audio/mpeg"></audio>
								<?php endif ?>
							<?php endforeach ?>

						<?php break; // end word problem assignment

						case MATH_ASSIGNMENT_QUESTION_BOX: ?>

							<?php foreach($questions['q'] as $key => $item) : ?>
								<div id="qbox-step-<?php echo $key ?>" class="question-box-block">
									<img src="<?php echo MWHtml::math_image_url($item['image']) ?>" alt="" class="word-prob-steps canvas-layer" data-img-src="<?php echo MWHtml::math_image_url($item['image']) ?>" >
									<span class="math-number input-box" style="z-index: <?php echo substr($key, 1) ?>;left: <?php echo $item['x-cord'] ?>%; top: <?php echo $item['y-cord'] ?>%; width: <?php echo $item['width'] ?>%; height: <?php echo $item['height'] ?>%"><input data-answer="<?php echo $item['answer'] ?>" autocomplete="off" name="result[<?php echo $key ?>]" type="text" class="answer-box"></span>
								</div>
							<?php endforeach ?>

						<?php break; // end question box assignment

						case MATH_ASSIGNMENT_EQUATION: ?>

							<?php foreach($questions['q'] as $key => $item) : ?>
								<div class="flashcard-question equation-question hidden" id="flashcard-<?php echo $key ?>">
									<span class="math-number"><?php echo strtr($item['equation'], array( '\n' 	=> '<br>', '-' 	=> '&#8211;' ) ) ?></span>
									<span class="math-number input-box"><input data-answer="<?php echo $item['answer'] ?>" name="result[<?php echo $key ?>]" type="text" class="answer-box" autocomplete="off"></span>
									<span class="math-number"><?php echo $item['note'] ?></span>
								</div>
							<?php endforeach ?>

						<?php break; // end equation assignment
					endswitch ?>

				</div>
				<div class="col-sm-10 homework-user-answer">
					<div class="row">
						<?php if($is_word_prob_assignment) : ?>
							<div class="col-xs-9">
								<input type="text" class="homework-input tooltip-top-left" name="result" id="input-answer" placeholder="<?php _e('Type Answers Here', 'iii-dictionary') ?>" data-answer="<?php echo $questions['answer'] ?>" data-correct="<?php _e('Correct!', 'iii-dictionary') ?>" data-incorrect="<?php _e('Incorrect!', 'iii-dictionary') ?>">
							</div>
						<?php endif ?>
						<div class="col-xs-3<?php echo !$is_word_prob_assignment ? ' col-xs-offset-9' : '' ?>">
							<?php 
								if(!$teacher_taking_test){
									$_ref_url = empty($_GET['ref']) ? site_home_url() . '/?r=homework-status' : esc_html(base64_decode(rawurldecode($_GET['ref'])));
								}else {
									$_ref_url = locale_home_url() . "/?r=my-account";
								}
								if(!empty($homework_assignment->next_homework_id)) {
									$_next_url = locale_home_url() . '/?r=math-homework';
									if($curr_mode == 'homework' || $is_next_homework != '1') {
										$_next_url .= '&amp;mode=homework';
									}
									$_next_url .= '&amp;hid=' . $homework_assignment->next_homework_id;
									$_next_url = empty($_GET['ref']) ? $_next_url : $_next_url . '&amp;ref=' . $_GET['ref'];
								} else {
									$_next_url = $_ref_url;
								}
								
								
							?>
							<?php if($curr_mode == 'homework') : ?>
								<button type="button" id="submit-homework" class="btn btn-default brown"><?php _e('Submit', 'iii-dictionary') ?></button>
							<?php else : ?>
								<a href="<?php echo $_next_url ?>" class="btn btn-default brown" id="next-worksheet"><?php _e('Next', 'iii-dictionary') ?></a>
							<?php endif ?>
						</div>
					</div>
				</div>
				<div class="col-sm-2 homework-controls">
					<button type="button" class="btn btn-default dark-green" id="open-notepad-btn"><i class="icon-notepad"></i><?php _e('Notepad', 'iii-dictionary') ?></button>
					<hr class="hr-green hidden-xs">
					<button type="button" class="btn btn-default dark-green" id="open-chat-btn"><i class="icon-chat"></i><?php _e('Tutoring', 'iii-dictionary') ?></button>
				</div>

<?php if($curr_mode == 'homework') : ?>
	<div id="submit-homework-modal" class="modal fade modal-green" data-keyboard="true" aria-hidden="true" <?php echo $teacher_taking_test ? ' data-backdrop="static"' : '' ?>>
		<div class="modal-dialog">
		  <div class="modal-content">
			<div class="modal-header">
				<h3><?php !$teacher_taking_test ? _e('The End of Homework', 'iii-dictionary') : _e('The End of Test', 'iii-dictionary') ?></h3>
			</div>
			<?php if(!$teacher_taking_test) : ?>
			<div class="modal-body">
				<?php _e('You have completed this homework.', 'iii-dictionary') ?><br>
				<?php _e('If you want to leave a message to your teacher, type it in the box below.', 'iii-dictionary') ?><br>
				<?php if(empty($homework_assignment->next_homework_id)) {
						_e('Click OK to submit.', 'iii-dictionary'); 
					} ?>
				<br>
				<div class="form-group">
					<textarea class="form-control" name="feedback" id="txt-feedback" placeholder="<?php _e('Leave feedback', 'iii-dictionary') ?>" style="resize: none"></textarea>
				</div>
				<?php if(!empty($homework_assignment->next_homework_id)) : ?>
					<div class="homework-notice">
						<?php printf(__('Starting the next worksheet, %s?', 'iii-dictionary'), $homework_assignment->next_sheet) ?><br>
					</div>
				<?php endif ?>
				<hr>
			</div>
			<div class="modal-footer">
				<div class="row">
					<?php if(empty($homework_assignment->next_homework_id)) : ?>
						<div class="col-sm-12 form-group">
							<button type="submit" name="submit-homework" class="btn btn-block orange submit-lesson-btn" data-loading-text="<?php _e('Submitting...', 'iii-dictionary') ?>" data-ref="<?php echo $_ref_url ?>"><span class="icon-accept"></span><?php _e('OK', 'iii-dictionary') ?></button>
						</div>
					<?php else : ?>
						<div class="col-sm-6 form-group">
							<button type="submit" name="submit-homework" class="btn btn-block orange submit-lesson-btn" data-loading-text="<?php _e('Submitting...', 'iii-dictionary') ?>" data-ref="<?php echo $_next_url ?>"><span class="icon-accept"></span><?php _e('Yes', 'iii-dictionary') ?></button>
						</div>
						<div class="col-sm-6 form-group">
							<button type="submit" name="submit-homework" class="btn btn-block grey submit-lesson-btn" data-loading-text="<?php _e('Submitting...', 'iii-dictionary') ?>" data-ref="<?php echo $_ref_url ?>"><span class="icon-cancel"></span><?php _e('Quit', 'iii-dictionary') ?></button>
						</div>
					<?php endif ?>
					<input type="hidden" name="ref" id="input-ref">
				</div>
			</div>
			<?php else : ?>
				<div class="modal-body">
					<?php _e('You have completed this test.', 'iii-dictionary') ?><br>
					<?php _e('If you want to leave a message to the admin, type it in the box below.', 'iii-dictionary') ?><br>
					<?php _e('Click OK to submit.', 'iii-dictionary') ?>
					<hr>
					<div class="form-group">
						<textarea class="form-control" id="txt-feedback" placeholder="<?php _e('Leave feedback', 'iii-dictionary') ?>" style="resize: none"></textarea>
					</div>
				</div>
				<div class="modal-footer">
					<div class="row">
						<div class="col-sm-12">
							<div class="form-group">
								<button type="submit" name="submit-homework" class="btn btn-block orange submit-lesson-btn" data-loading-text="<?php _e('Submitting...', 'iii-dictionary') ?>" data-ref="<?php echo $_ref_url ?>"><span class="icon-accept"></span><?php _e('OK', 'iii-dictionary') ?></button>
							</div>
						</div>
					</div>
					<input type="hidden" name="ref" id="input-ref">
					<?php if($teacher_taking_test) : ?>
					<input type="hidden" name="pass" value="<?php echo $pass ?>" />
					<?php endif ?>
				</div>
			<?php endif ?>
		  </div>
		</div>
	</div>
<?php endif ?>
			</form>
			
                    <div class="modal " id="modal-notice" role="dialog">
        <div class="modal-dialog modal-sm">
          <div class="modal-content" style="margin-top: 142px;    width: 50%;margin: auto;border-radius: 5px; border: 1px solid #627598;">
              <span class="close">×</span>
            <div class="modal-header" style=" padding-left: 0px;">
              <h4 class="modal-title" style="border-bottom: 1px solid #76797f;text-align: center;color: black; padding-left: 0px">Message</h4>
            </div>
            <div class="notice-content1">
            </div>
            <div class="notice-content" style="text-align: center;border-radius: 5px;padding: 8px;">
              <a class="button-notice"  id="auto-open-chat" target="_blank">Click here to open chat and drawing</a>
            </div>
          </div>
        </div>
      </div>

<div class="modal " id="modal-notice-close" role="dialog" style="right: 10%;">
        <div class="modal-dialog modal-sm">
          <div class="modal-content" style="margin-top: 105px;width: 50%;margin: auto;border-radius: 5px;border: 1px solid #627598;" >
            <div class="modal-header">
              <!--<button type="button" class="close" data-dismiss="modal">&times;</button>-->
              <h4 class="modal-title" style="text-align: center;border-bottom: 1px solid #76797f;color: black;">Message</h4>
            </div>
            <div class="notice-content" style="text-align: center;color: black;font-size: 18px;">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger btn-quit" style="width: 40%" data-dismiss="modal">Yes</button>
              <button type="button" class="btn btn-primary btn-close-md" style="width: 40%" data-dismiss="modal">No</button>
            </div>
          </div>
        </div>
      </div>
        <div class="modal" id="modal-notice-start" role="dialog" style="right: 10%;">
        <div class="modal-dialog modal-sm">
          <div class="modal-content" style="margin-top: 105px;width: 50%;margin: auto;border-radius: 5px;border: 1px solid #627598;" >
            <div class="modal-header">
              <!--<button type="button" class="close" data-dismiss="modal">&times;</button>-->
              <h4 class="modal-title" style="text-align: center;border-bottom: 1px solid #76797f;color: black;">Message</h4>
            </div>
            <div class="notice-content-start" style="text-align: center;color: black;font-size: 18px;">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="hidden-modal-start" style="width: 40%" data-dismiss="modal">Ok</button>
            </div>
          </div>
        </div>
      </div>
<!--SECTION CHAT-->
<?php if($curr_mode == 'practice' && !$teacher_taking_test) : ?>
<div class="body" id="block-chat">
	<div id="ignore-fixed">
		<div id="block-new-friend">
			<div class="nf-header col-md-12">
				<?php _e('Enter the account name', 'iii-dictionary') ?>
				<div id="nf-close"></div>
			</div>
			<div class="nf-content col-md-12">
				<div class="row">
					<div class="col-md-7">
						<input type="text" id="nf-txt-log" placeholder="Email & Username" />
					</div>
					<div class="col-md-5">
						<button id="nf-btn-send"><?php _e('Send', 'iii-dictionary') ?></button>
					</div>
					<div class="col-md-12 nd-notice">
						<label class="nf-lbl-notice" id="notice-null"><?php _e('Insert user name', 'iii-dictionary') ?></label>
						<label class="nf-lbl-notice" id="notice-yourself"><?php _e('Can\'t connect to your self', 'iii-dictionary') ?></label>
						<label class="nf-lbl-notice" id="notice-exists"><?php _e('User already exists', 'iii-dictionary') ?></label>
					</div>
				</div>
			</div>
		</div>
		<div class="container-fluid">
			<div class="row">
				<div class="col-md-12 chat-box-header block-header">
				  <label><?php _e('Request a Teacher for Math Tutorials', 'iii-dictionary') ?></label>
				  <input type="button" id="btnClose" class="btn-close" />
				</div>
			</div>
			<div class="block-content row">
				<?php if( is_user_logged_in() ) : ?>
					<div class="section-chat <?php echo ($_is) ? 'display-block' : '' ?>">
						<div class="col-md-12" id="block-chat-header">
							<div class="row">
								<div class="col-md-5">
									<label class="sl-label"><?php _e('Select a Reciplent', 'iii-dictionary') ?></label>
								</div>
								<div class="col-md-7">
<!--									<select id="sl-reciplent" class="sl-style">
										<option value="0"><?php _e('---- Click below ----', 'iii-dictionary') ?></option>
										<option value="new-friend"><?php _e('New Friend...', 'iii-dictionary') ?></option>
									</select>-->
                                                                    <input id="sl-reciplent" data-email="" class="" data-log="" value=""/>
								</div>
								<div class="col-md-12"><div class="block-break"></div></div> 
							</div>
						</div>
						
						<div class="col-md-12" id="block-chat-content">
							<div id="slitter">
								<div id="block-cc-draw">
									<div class="btn-canvas-edit" ></div>
									<canvas id="draw-canvas" class="block-canvas"></canvas>
								</div>

								<div class="bottom_panel mCustomScrollbar _mCS_1 mCS_no_scrollbar" id="block-cc-chat">
									<div class="block-apend-message">
										<div class="receive-message col-md-12">
											<div class="col-md-8 cm-text">
												<?php _e('Hello', 'iii-dictionary') ?> <b class="b-name"><?php echo $current_user->display_name; ?></b>
											</div>
											<!--<div class="col-md-4 cm-time"></div>-->
										</div>
										<!--
										<div class="current-message col-md-12" >
											<div class="col-md-4 rm-time"></div>
										</div>
										-->
									</div>
								</div>
								
							</div>
						</div>
						
						<div class="col-md-12" id="block-type-chat">
							<div class="row">
<!--								<div class="col-md-9">
									<textarea  class="txt-chat" id="txt-chat" placeholder="<?php _e('Type a message...', 'iii-dictionary') ?>" autocomplete="off"></textarea>
								</div>-->
								<div class="col-md-3">
									<button class="btn-send" id="btn-send" ><?php _e('Send', 'iii-dictionary') ?></button>
								</div>
							</div>
						</div>
					</div>
					<div class="section-register <?php echo (!$_is) ? 'display-block' : '' ?>">
						<div class="col-md-12 block-body">
							<div class="block-chat-notice"> <?php echo mw_get_option('math-chat-notice') ?> </div>
							<label class="block-chat-price" ><?php printf(__('Price $%d / 1 minutes', 'iii-dictionary'),  mw_get_option('math-chat-price')) ?></label>
						</div>
						<div class="col-md-12 block-footer">
							<div class="row">
								<div class="col-md-6">
									<button id="btn-chat-accept" class="btn-chat-default btn-chat-accept"><?php _e('Request', 'iii-dictionary') ?></button>
								</div>
								<div class="col-md-6">
									<button id="btn-chat-quit" class="btn-chat-default btn-chat-quit" ><?php _e('No Thanks', 'iii-dictionary') ?></button>
								</div>
							</div>
						</div>
						<div class="block-popup">
							<!--
							<div class="block-popup-header">
								<label id="btn-close-popup" class="btn-close-bp">x</label>
							</div>
							-->
							<div class="block-popup-content">
							</div>
						</div>
					</div>
				<?php else : ?>
					<form action="<?php echo locale_home_url() ?>/?r=login" name="loginform" method="post" >
						<div class="col-md-12">
							<div class="form-group">
								<label for='chat_username' class="lbl-chat"><?php _e('Username (e-mail address)', 'iii-dictionary') ?></label>
								<input type="text" id="chat_username" name="log" class="form-control" />
							</div>
						</div>
						<div class="col-md-12">
							<div class="form-group">
								<label for='chat_password' class="lbl-chat"><?php _e('Password', 'iii-dictionary') ?></label>
								<input type="password" id="chat_password" name="pwd" class="form-control" />
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<button type="submit" class="btn btn-default btn-block orange login" name="wp-submit">
									<span class="icon-user"></span><?php _e('Login', 'iii-dictionary') ?>
								</button>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<a href="<?php echo locale_home_url() ?>/?r=signup" class="btn btn-default btn-block grey signup"><span class="icon-pencil"></span><?php _e('Sign-up', 'iii-dictionary') ?></a>
							</div>
						</div>
						<div class="col-md-6">
						</div>
					</form>
				<?php endif ?>
				<div class="modal-custom">
					<div class="row popup-notice">
						<div class="col-md-12 notice-header">
							<?php _e('Message', 'iii-dictionary') ?>
						</div>
						<div class="col-md-12 notice-content">
							
						</div>
					</div>
				</div>
			</div>
		</div>
		
		
	</div>
</div>

<?php endif ?>


<script>
	var is_div_type = <?php echo in_array($homework->assignment_id, array(MATH_ASSIGNMENT_SINGLE_DIGIT_DIV, MATH_ASSIGNMENT_TWO_DIGIT_DIV)) ? 1 : 0 ?>;
	var _CMODE = "<?php echo $curr_mode ?>";
	var <?php echo $curr_mode == 'homework' ? '_ANSWER_TIME = ' . $homework->answer_time_limit : '_SHOW_TIME = ' . $homework->show_answer_after ?>;
	var _TYPE = <?php echo MATH_ASSIGNMENT_WORD_PROB ?>;
	var __HA = <?php echo $homework->assignment_id ?>;
	var __US = "<?php echo (is_user_logged_in()) ? get_current_user_id() : gethostname() ?>";
	var __URL = "<?php echo $_SERVER['QUERY_STRING'] ?>";
	var __SID = <?php echo $sheet_id ?>;
	var __IS = <?php echo (is_mw_qualified_teacher()) ? 1 : 0 ?>;
	var __NAME =  "<?php echo (!empty($current_user)) ? $current_user->display_name : '' ?>";
	var __EMAIL =  "<?php echo (!empty($current_user)) ? $current_user->user_email : '' ?>";
	var __PRICE = <?php echo mw_get_option('math-chat-price'); ?>;
       jQuery(document).ready(function(){ 
        setTimeout(function(){
            jQuery('.notice-content-start').html('').append('<p>Wait until your student click Start button</p>');
            jQuery("#modal-notice-start").show();
        }, 1000)});
</script>
<!--<style>
/* The Modal (background) */
.modal-notice {
    display: none; /* Hidden by default */
    position: fixed; /* Stay in place */
    z-index: 1; /* Sit on top */
    padding-top: 100px; /* Location of the box */
    left: 0;
    top: 0;
    width: 100%; /* Full width */
    height: 100%; /* Full height */
    overflow: auto; /* Enable scroll if needed */
    background-color: rgb(0,0,0); /* Fallback color */
    background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
}

/* Modal Content */
.modal-content {
    background-color: #fefefe;
    margin: auto;
    padding: 20px;
    border: 1px solid #888;
    width: 50%;
}

/* The Close Button */
.close {
    color: #aaaaaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
}

.close:hover,
.close:focus {
    color: #000;
    text-decoration: none;
    cursor: pointer;
}
.button-notice{
        background-color: #141e30;
    border: 1px solid #141e30;
    border-radius: 3px;
    box-sizing: border-box;
    color: #fafafa;
    cursor: pointer;
    display: inline-block;
    font-weight: 400;
    line-height: 1.5;
    padding: 0.3em 1em;
    white-space: normal;
    text-align: center;
    text-decoration: none;
}
</style>-->
<?php if(!is_math_panel()) : ?>
	<?php get_dict_footer() ?>
<?php else : ?>
	<?php get_math_footer() ?>
<?php endif ?>