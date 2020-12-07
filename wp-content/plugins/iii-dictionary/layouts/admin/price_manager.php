<?php
	if(isset($_POST['submit-price']))
	{
		$tt_price = $_POST['teacher-tool-price'] != '' ? $_POST['teacher-tool-price'] : 0;
		$self_study_price = $_POST['self-study-price'] != '' ? $_POST['self-study-price'] : 0;
		$d_price = $_POST['dictionary-price'] != '' ? $_POST['dictionary-price'] : 0;
		$all_d_price = $_POST['all-dictionary-price'] != '' ? $_POST['all-dictionary-price'] : 0;
		$pop_in = $_POST['sub-popup-interval'] != '' ? $_POST['sub-popup-interval'] : 0;
		$pop_times = $_POST['sub-popup-times'] != '' ? $_POST['sub-popup-times'] : 0;
		$min_student = $_POST['min-students-subscription'] != '' ? $_POST['min-students-subscription'] : 1;

		$sat_grammar_p = $_POST['sat-grammar-price'] != '' ? $_POST['sat-grammar-price'] : 0;
		$sat_writing_p = $_POST['sat-writing-price'] != '' ? $_POST['sat-writing-price'] : 0;
		$sat_test_p = $_POST['sat-test-price'] != '' ? $_POST['sat-test-price'] : 0;
		$pts_exchange_rate = $_POST['point-exchange-rate'] != '' ? $_POST['point-exchange-rate'] : 1;

		$teacher_sheet_margin = $_POST['teacher-sheet-price-margin'] != '' ? $_POST['teacher-sheet-price-margin'] : 0;
		$teacher_max_point = $_POST['teacher-max-point'] != '' ? $_POST['teacher-max-point'] : 0;
		$teacher_grading_margin = $_POST['teacher-grading-price-margin'] != '' ? $_POST['teacher-grading-price-margin'] : 0;
		
		
		$teacher_test_score_threshold = $_POST['teacher-test-score-threshold'] != '' ? $_POST['teacher-test-score-threshold'] : 0 ;
		$teacher_test_math_score_threshold = $_POST['teacher-math-test-score-threshold'] != '' ? $_POST['teacher-math-test-score-threshold'] : 0 ;
		
		$math_min_students_subscription 	= $_POST['math-min-students-subscription'] != '' ? $_POST['math-min-students-subscription'] : 0;
		$math_teacher_tool_price 			= $_POST['math-teacher-tool-price'] != '' ? $_POST['math-teacher-tool-price'] : 0;
		$math_self_study_price 				= $_POST['math-self-study-price'] != '' ? $_POST['math-self-study-price'] : 0;
		$math_sat1_price 					= $_POST['math-sat1-price'] != '' ? $_POST['math-sat1-price'] : 0;
		$math_sat2_price 					= $_POST['math-sat2-price'] != '' ? $_POST['math-sat2-price'] : 0;

		mw_set_option('teacher-tool-price', $tt_price);
		mw_set_option('self-study-price', $self_study_price);
		mw_set_option('dictionary-price', $d_price);
		mw_set_option('all-dictionary-price', $all_d_price);
		mw_set_option('sub-popup-interval', $pop_in);
		mw_set_option('sub-popup-times', $pop_times);
		mw_set_option('min-students-subscription', $min_student);

		mw_set_option('sat-grammar-price', $sat_grammar_p);
		mw_set_option('sat-writing-price', $sat_writing_p);
		mw_set_option('sat-test-price', $sat_test_p);

		mw_set_option('teacher-sheet-price-margin', $teacher_sheet_margin);
		mw_set_option('teacher-grading-price-margin', $teacher_grading_margin);
		mw_set_option('teacher-max-point', $teacher_max_point);
		mw_set_option('point-exchange-rate', $pts_exchange_rate);
	
		mw_set_option('math-min-students-subscription', $math_min_students_subscription);
		mw_set_option('math-teacher-tool-price', $math_teacher_tool_price);
		mw_set_option('math-self-study-price', $math_self_study_price);
		mw_set_option('math-sat1-price', $math_sat1_price);
		mw_set_option('math-sat2-price', $math_sat2_price);

		mw_set_option('teacher-test-group', $_POST['teacher-test-group']);
		mw_set_option('teacher-math-test-group', $_POST['teacher-math-test-group']);
		mw_set_option('teacher-test-score-threshold', $teacher_test_score_threshold);
		mw_set_option('teacher-math-test-score-threshold', $teacher_test_math_score_threshold);
		
		wp_redirect( home_url() . '/?r=price-manager' );
		exit;
	}

	if(isset($_POST['submit-agreement']))
	{
		mw_set_option('registration-agreement', $_POST['registration_agreement']);
		mw_set_option('math-registration-agreement', $_POST['math_registration_agreement']);
		mw_set_option('teaching-agreement', $_POST['teaching_agreement']);
		mw_set_option('math-registration-agreement', $_POST['math_registration_agreement']);
		mw_set_option('math-teaching-agreement', $_POST['math_teaching_agreement']);
		mw_set_option('math-chat-notice', $_POST['math_chat_notice']);
		mw_set_option('math-chat-price', $_POST['math_chat_price']);
		mw_set_option('agreement-update-date', date('Y-m-d H:i:s', time()));
		
		wp_redirect( home_url() . '/?r=price-manager' );
		exit;
	}
	
	if(isset($_POST['submit-link']))
	{
		mw_set_option('english-link-en', $_POST['english-link-en']);
		mw_set_option('english-link-ja', $_POST['english-link-ja']);
		mw_set_option('english-link-ko', $_POST['english-link-ko']);
		mw_set_option('english-link-zh', $_POST['english-link-zh']);
		mw_set_option('english-link-vn', $_POST['english-link-vn']);
		
		mw_set_option('math-link-en', $_POST['math-link-en']);
		mw_set_option('math-link-ja', $_POST['math-link-ja']);
		mw_set_option('math-link-ko', $_POST['math-link-ko']);
		mw_set_option('math-link-zh', $_POST['math-link-zh']);
		mw_set_option('math-link-vn', $_POST['math-link-vn']);
		
		
		wp_redirect( home_url() . '/?r=price-manager' );
		exit;
	}
	
	if(isset($_POST['submit-mange-sub'])) {
		global $wpdb;
		$param = $_POST['upt_sub'];
		
		foreach($param as $n => $data) {
			if(!empty($data)) {
				$wpdb->update(
					$wpdb->prefix . 'dict_subscription_type',
					array( 'name' => trim($data) ),
					array( 'id' => $n )
				);
			}
		}
		wp_redirect( home_url() . '/?r=price-manager' );
		exit;
	}

	$teacher_test_groups 	= MWDB::get_groups(array('group_type' => GROUP_CLASS, 'class_type' => CLASS_OTHERS));
	$sub 					= MWDB::_get_name_subscription_type();
?>
<?php get_dict_header('Admin Price Manager') ?>
<?php get_dict_page_title('Admin Price Manager', 'admin-page') ?>

	<form action="<?php echo home_url() ?>/?r=price-manager" method="post">	
		<div class="row">
			<div class="col-xs-12">
				<div class="row">
					<div class="col-sm-12">
						<h2 class="title-border">Homework Tools</h2>
					</div>
					<div class="col-sm-6">
						<div class="form-group">
							<label for="teacher-tool-price">Teacher's tool price <small>(cents)</small></label>
							<input type="number" class="form-control" name="teacher-tool-price" min="0" id="teacher-tool-price" value="<?php echo mw_get_option('teacher-tool-price') ?>">
							<div style="margin-top: 8px; text-align: right"><span style="color: #fff"><strong>X</strong></span> Number of Students <span style="color: #fff"><strong>X</strong></span> Number of months</div>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group">
							<label for="min-students-subscription">Min number of students/licenses</label>
							<input type="number" class="form-control" name="min-students-subscription" min="1" id="min-students-subscription" value="<?php echo mw_get_option('min-students-subscription') ?>">
						</div>
					</div>
					<div class="clearfix"></div>
					<div class="col-sm-6">
						<div class="form-group">
							<label for="self-study-price">Student Self-study <small>(Per month, dollars)</small></label>
							<input type="number" class="form-control" name="self-study-price" min="0" id="self-study-price" value="<?php echo mw_get_option('self-study-price') ?>">
						</div>
					</div>

					<div class="col-sm-12">
						<h2 class="title-border">Dictionary</h2>
					</div>
					<div class="col-sm-6">
						<div class="form-group">
							<label for="dictionary-price">Dictionary subscription price <small>(cents)</small></label>
							<input type="number" class="form-control" name="dictionary-price" min="0" id="dictionary-price" value="<?php echo mw_get_option('dictionary-price') ?>">
							<div style="margin-top: 8px; text-align: right"><span style="color: #fff"><strong>X</strong></span> Number of Licenses <span style="color: #fff"><strong>X</strong></span> Number of months</div>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group">
							<label for="all-dictionary-price">All dictionary subscription price <small>(cents)</small></label>
							<input type="number" class="form-control" name="all-dictionary-price" min="0" id="all-dictionary-price" value="<?php echo mw_get_option('all-dictionary-price') ?>">
							<div style="margin-top: 8px; text-align: right"><span style="color: #fff"><strong>X</strong></span> Number of Licenses <span style="color: #fff"><strong>X</strong></span> Number of months</div>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group">
							<label for="sub-popup-interval">Dictionary subscription popup time interval <small>(seconds)</small></label>
							<input type="number" class="form-control" name="sub-popup-interval" min="0" id="sub-popup-interval" value="<?php echo mw_get_option('sub-popup-interval') ?>">
						</div>
					</div>

					<div class="col-sm-6">
						<div class="form-group">
							<label for="sub-popup-times">Number of searched words without popup <small>(times)</small></label>
							<input type="number" class="form-control" name="sub-popup-times" min="0" id="sub-popup-times" value="<?php echo mw_get_option('sub-popup-times') ?>">
						</div>
					</div>

					<div class="col-sm-12">
						<h2 class="title-border">SAT Preparation</h2>
					</div>
					<div class="col-sm-6">
						<div class="form-group">
							<label for="sat-grammar-price">Grammar Class per month <small>($)</small></label>
							<input type="number" class="form-control" name="sat-grammar-price" min="0" step="any" id="sat-grammar-price" value="<?php echo mw_get_option('sat-grammar-price') ?>">
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group">
							<label for="sat-writing-price">Writing Class per month <small>($)</small></label>
							<input type="number" class="form-control" name="sat-writing-price" min="0" step="any" id="sat-writing-price" value="<?php echo mw_get_option('sat-writing-price') ?>">
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group">
							<label for="sat-test-price">SAT Practice test per month <small>($)</small></label>
							<input type="number" class="form-control" name="sat-test-price" min="0" step="any" id="sat-test-price" value="<?php echo mw_get_option('sat-test-price') ?>">
						</div>
					</div>

					<div class="col-sm-12">
						<h2 class="title-border">Teacher</h2>
					</div>
					<div class="col-sm-6">
						<div class="form-group">
							<label for="teacher-sheet-price-margin">Margin to be added to the teacher's worksheet price <small>(%)</small></label>
							<input type="number" class="form-control" name="teacher-sheet-price-margin" min="0" max="100" id="teacher-sheet-price-margin" value="<?php echo mw_get_option('teacher-sheet-price-margin') ?>">
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group">
							<label for="teacher-grading-price-margin">Margin to be taken from the writing grading price <small>(%)</small></label>
							<input type="number" class="form-control" name="teacher-grading-price-margin" min="0" max="100" id="teacher-grading-price-margin" value="<?php echo mw_get_option('teacher-grading-price-margin') ?>">
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group">
							<label for="teacher-max-point">The MAX point for teachers</label>
							<input type="number" class="form-control" name="teacher-max-point" min="0" id="teacher-max-point" value="<?php echo mw_get_option('teacher-max-point') ?>">
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group">
							<label for="point-exchange-rate">Points conversion rate <small>(Point per 1$)</small></label>
							<input type="number" class="form-control" name="point-exchange-rate" min="1" id="point-exchange-rate" value="<?php echo mw_get_option('point-exchange-rate') ?>">
						</div>
					</div>

					<div class="clearfix"></div>

					<div class="col-sm-6">
						<div class="form-group">
							<label>Select a Group containing Teacher's Test</label>
							<select class="select-box-it form-control" name="teacher-test-group">
								<option value="">Select one</option>
								<?php foreach($teacher_test_groups->items as $item) : ?>
									<option value="<?php echo $item->id ?>"<?php echo $item->id == mw_get_option('teacher-test-group') ? ' selected' : '' ?>><?php echo $item->name ?></option>
								<?php endforeach ?>
							</select>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group">
							<label>Teacher Test Passing Threshold</label>
							<input type="number" name="teacher-test-score-threshold" class="form-control" min="0" value="<?php echo mw_get_option('teacher-test-score-threshold') ?>">
						</div>
					</div>
					
					<div class="col-sm-6">
						<div class="form-group">
							<label>Select a Group containing Teacher's Test Math</label>
							<select class="select-box-it form-control" name="teacher-math-test-group">
								<option value="">Select one</option>
								<?php foreach($teacher_test_groups->items as $item) : ?>
									<option value="<?php echo $item->id ?>"<?php echo $item->id == mw_get_option('teacher-math-test-group') ? ' selected' : '' ?>><?php echo $item->name ?></option>
								<?php endforeach ?>
							</select>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group">
							<label>Teacher Test Math Passing Threshold</label>
							<input type="number" name="teacher-math-test-score-threshold" class="form-control" min="0" value="<?php echo mw_get_option('teacher-math-test-score-threshold') ?>">
						</div>
					</div>

					<div class="clearfix"></div>

					<div class="col-sm-12">
						<h2 class="title-border">Homework Tools For Math</h2>
					</div>
					<div class="col-sm-6">
						<div class="form-group">
							<label for="math-teacher-tool-price">Teacher's tool price <small>(cents)</small></label>
							<input type="number" class="form-control" name="math-teacher-tool-price" min="0" id="math-teacher-tool-price" value="<?php echo mw_get_option('math-teacher-tool-price') ?>">
							<div style="margin-top: 8px; text-align: right"><span style="color: #fff"><strong>X</strong></span> Number of Students <span style="color: #fff"><strong>X</strong></span> Number of months</div>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group">
							<label for="math-min-students-subscription">Min number of students/licenses</label>
							<input type="number" class="form-control" name="math-min-students-subscription" min="1" id="math-min-students-subscription" value="<?php echo mw_get_option('math-min-students-subscription') ?>">
						</div>
					</div>
					<div class="clearfix"></div>
					<div class="col-sm-6">
						<div class="form-group">
							<label for="self-study-price">Student Self-study <small>(Per month, dollars)</small></label>
							<input type="number" class="form-control" name="math-self-study-price" min="0" id="math-self-study-price" value="<?php echo mw_get_option('math-self-study-price') ?>">
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group">
							<label>SAT1 Preparation </label>
							<input type="number" class="form-control" name="math-sat1-price" min="0" value="<?php echo mw_get_option('math-sat1-price') ?>">
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group">
							<label>SAT2 Preparation </label>
							<input type="number" class="form-control" name="math-sat2-price" min="0" value="<?php echo mw_get_option('math-sat2-price') ?>">
						</div>
					</div>

					<div class="clearfix"></div>

					<div class="col-sm-6 form-group">
						<label>&nbsp;</label>
						<button name="submit-price" type="submit" class="btn btn-default btn-block orange"><span class="icon-hand"></span>Apply new pricing</button>
					</div>

					<div class="col-sm-12">
						<h2 class="title-border">Agreement</h2>
					</div>

					<?php
						$settings = array(
							'wpautop' => false,
							'media_buttons' => false,
							'quicktags' => false,
							'textarea_rows' => 12,
							'tinymce' => array(
								'toolbar1' => 'bold,italic,strikethrough,image,bullist,numlist,blockquote,hr,alignleft,aligncenter,alignright,spellchecker,fullscreen,wp_adv'
							)
						);
					?>
					<div class="col-sm-12">
						<div class="form-group">
							<label>Teacher Registration Agreement</label>
							<?php wp_editor(mw_get_option('registration-agreement'), 'registration_agreement', $settings); ?> 
						</div>
					</div>
					<div class="col-sm-12">
						<div class="form-group">
							<label>Teacher Teaching Agreement</label>
							<?php wp_editor(mw_get_option('teaching-agreement'), 'teaching_agreement', $settings); ?> 
						</div>
					</div>
					<div class="col-sm-12">
						<h2 class="title-border">Agreement For Math</h2>
					</div>
					<div class="col-sm-12">
						<div class="form-group">
							<label>Teacher Registration Agreement</label>
							<?php wp_editor(mw_get_option('math-registration-agreement'), 'math_registration_agreement', $settings); ?> 
						</div>
					</div>
					<div class="col-sm-12">
						<div class="form-group">
							<label>Teacher Teaching Agreement</label>
							<?php wp_editor(mw_get_option('math-teaching-agreement'), 'math_teaching_agreement', $settings); ?> 
						</div>
					</div>
					<div class="col-sm-12">
						<div class="form-group">
							<label>Math chat requested</label>
							<?php wp_editor(mw_get_option('math-chat-notice'), 'math_chat_notice', $settings); ?> 
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group">
							<label>Price per 1 minutes</label>
							<input type="number" class="form-control" name="math_chat_price" min="0" value="<?php echo mw_get_option('math-chat-price') ?>" />
						</div>
					</div>
					<div class="clearfix" ></div>
					
					<div class="col-sm-6">
						<div class="form-group">
							<button name="submit-agreement" type="submit" class="btn btn-default btn-block orange"><span class="icon-hand"></span>Apply new agreement</button>
						</div>     
					</div>
					<div class="clearfix"></div>
					<div class="col-sm-6">
						<h2><?php _e('English side','iii-dictionary') ?></h2>
					</div>
					<div class="col-sm-6">
						<h2><?php _e('Math side (main menu)','iii-dictionary') ?></h2>
					</div>
					<div class="col-sm-6">
						<div class="row">
							<div class="col-sm-12 form-group">
								<label class="col-sm-5 control-label"><?php _e('English','iii-dictionary') ?></label>
								<div class="col-sm-7">
									<input type="input" class="form-control" name="english-link-en" value="<?php echo mw_get_option('english-link-en') ?>">
								</div>
							</div>
							<div class="col-sm-12 form-group">
								<label class="col-sm-5 control-label"><?php _e('Japanese','iii-dictionary') ?></label>
								<div class="col-sm-7">
									<input type="input" class="form-control" name="english-link-ja"  value="<?php echo mw_get_option('english-link-ja') ?>">
								</div>
							</div>
							<div class="col-sm-12 form-group">
								<label class="col-sm-5 control-label"><?php _e('Korean','iii-dictionary') ?></label>
								<div class="col-sm-7">
									<input type="input" class="form-control" name="english-link-ko"  value="<?php echo mw_get_option('english-link-ko') ?>">
								</div>
							</div>
							<div class="col-sm-12 form-group">
								<label class="col-sm-5 control-label"><?php _e('Chinese','iii-dictionary') ?></label>
								<div class="col-sm-7">
									<input type="input" class="form-control" name="english-link-zh" value="<?php echo mw_get_option('english-link-zh') ?>">
								</div>
							</div>
							<div class="col-sm-12 form-group">
								<label class="col-sm-5 control-label"><?php _e('Vietnamese','iii-dictionary') ?></label>
								<div class="col-sm-7">
									<input type="input" class="form-control" name="english-link-vn" value="<?php echo mw_get_option('english-link-vn') ?>">
								</div>
							</div>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="row">
							<div class="col-sm-12 form-group">
								<label class="col-sm-5 control-label"><?php _e('English','iii-dictionary') ?></label>
								<div class="col-sm-7">
									<input type="input" class="form-control" name="math-link-en" value="<?php echo mw_get_option('math-link-en') ?>">
								</div>
							</div>
							<div class="col-sm-12 form-group">
								<label class="col-sm-5 control-label"><?php _e('Japanese','iii-dictionary') ?></label>
								<div class="col-sm-7">
									<input type="input" class="form-control" name="math-link-ja"  value="<?php echo mw_get_option('math-link-ja') ?>">
								</div>
							</div>
							<div class="col-sm-12 form-group">
								<label class="col-sm-5 control-label"><?php _e('Korean','iii-dictionary') ?></label>
								<div class="col-sm-7">
									<input type="input" class="form-control" name="math-link-ko"  value="<?php echo mw_get_option('math-link-ko') ?>">
								</div>
							</div>
							<div class="col-sm-12 form-group">
								<label class="col-sm-5 control-label"><?php _e('Chinese','iii-dictionary') ?></label>
								<div class="col-sm-7">
									<input type="input" class="form-control" name="math-link-zh" value="<?php echo mw_get_option('math-link-zh') ?>">
								</div>
							</div>
							<div class="col-sm-12 form-group">
								<label class="col-sm-5 control-label"><?php _e('Vietnamese','iii-dictionary') ?></label>
								<div class="col-sm-7">
									<input type="input" class="form-control" name="math-link-vn" value="<?php echo mw_get_option('math-link-vn') ?>">
								</div>
							</div>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group">
							<button name="submit-link" type="submit" class="btn btn-default btn-block orange"><span class="icon-hand"></span><?php _e('Apply the new Links','iii-dictionary') ?></button>
						</div>     
					</div>
					<div class="clearfix"></div>
				
					<div class="col-sm-12">
						<h2 class="title-border">Manage Subscription</h2>
					</div>
					<div class="col-sm-6">
						<h2><?php _e('English','iii-dictionary') ?></h2>
					</div>
					<div class="col-sm-6">
						<h2><?php _e('Math','iii-dictionary') ?></h2>
					</div>
					<div class="col-sm-6">
						<div class="form-group">
							<label for="sub-en-hwt">Homework Tool</label>
							<input type="text" class="form-control" name="upt_sub[1]" id="sub-en-hwt" value="<?php echo $sub[0] ?>">
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group">
							<label for="sub-math-hwt">Homework Tool</label>
							<input type="text" class="form-control" name="upt_sub[6]" id="sub-math-hwt" value="<?php echo $sub[5] ?>">
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group">
							<label for="sub-en-sss">Student Self-study</label>
							<input type="text" class="form-control" name="upt_sub[5]" id="sub-en-sss" value="<?php echo $sub[4] ?>">
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group">
							<label for="sub-math-sss">Student Self-study</label>
							<input type="text" class="form-control" name="upt_sub[9]" id="sub-math-sss" value="<?php echo $sub[8] ?>">
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group">
							<label for="sub-en-sat">SAT</label>
							<input type="text" class="form-control" name="upt_sub[3]" id="sub-en-sat" value="<?php echo $sub[2] ?>">
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group">
							<label for="sub-math-sat1">SAT I</label>
							<input type="text" class="form-control" name="upt_sub[7]" id="sub-math-sat1" value="<?php echo $sub[6] ?>">
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group">
							<label for="sub-en-dic">Dictionary</label>
							<input type="text" class="form-control" name="upt_sub[2]" id="sub-en-dic" value="<?php echo $sub[1] ?>">
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group">
							<label for="sub-math-sat2">SAT II</label>
							<input type="text" class="form-control" name="upt_sub[8]" id="sub-math-sat2" value="<?php echo $sub[7] ?>">
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group">
							<label for="sub-points">Points <small>(english/math)</small></label>
							<input type="text" class="form-control" name="upt_sub[4]" id="sub-points" value="<?php echo $sub[3] ?>">
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group">
							<label for="sub-group">Math study Group</label>
							<input type="text" class="form-control" name="upt_sub[10]" id="sub-group" value="<?php echo $sub[9] ?>">
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group">
							<label for="sub-group">English study Group</label>
							<input type="text" class="form-control" name="upt_sub[11]" id="sub-group" value="<?php echo $sub[10] ?>">
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group">
							<button name="submit-mange-sub" type="submit" class="btn btn-default btn-block orange"><span class="icon-hand"></span><?php _e('Apply the new subscription','iii-dictionary') ?></button>
						</div>     
					</div>
					<div class="clearfix"></div>
					
				</div>
			</div>
		</div>
	</form>

<?php get_dict_footer() ?>