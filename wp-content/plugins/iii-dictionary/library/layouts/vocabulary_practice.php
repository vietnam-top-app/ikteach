<?php
	$page_title_tag = __('Vocabulary and Grammar Homework', 'iii-dictionary');
	include IK_PLUGIN_DIR . '/library/formatter.php';
	$mode = get_query_var('mode', 'practice');
	$select_grade_sheets = $insql = $tmp = $def = $js_homework_list = array();
	$cur_sheet_index = $count = 0;
	$sid = isset($_GET['sid']) ? $_GET['sid'] : 0;
	$_return_url = base64_decode(rawurldecode($_GET['ref']));
	// is teacher taking a test?
	$teacher_taking_test = in_array($sid, (array) $_SESSION['teacher_tests']);
	$homework_sheets = MWDB::get_homework_sheets(ASSIGNMENT_VOCAB_GRAMMAR);
	$sheet_list = $homework_sheets;

	if($mode == 'practice')
	{
		$page_title_tag = __('Vocabulary and Grammar Practice', 'iii-dictionary');
		$practice_sheets = MWDB::get_practice_sheets(ASSIGNMENT_VOCAB_GRAMMAR);

		foreach($homework_sheets as $key => $item) {
			if($item->private) {
				$teacher_sheet_suggestion[$item->grade] = $item->grade;
			}
			else {
				$public_sheet_grade_suggestion[$item->grade] = $item->grade; 
				$public_sheet_sheet_suggestion[$item->sheet_name] = $item->sheet_name;
			}
		}
		
		$sheet_list = $practice_sheets;
	}

	// check homework id
	if(!empty($_GET['hid'])) {
		$homework_assignment = MWDB::get_homework_assignment_by_id($_GET['hid']);
		$sid = $homework_assignment->sheet_id;
	}

	// user didn't select a homework, redirect back to practice page
	if($mode == 'homework' && !$sid) {
		wp_redirect(locale_home_url() . '/?r=vocabulary-practice');
		exit;
	}

	// no sheet id provided, get first sheet in the list as init sheet
	if(!$sid) {
		$sheet = $sheet_list[0];
	}

	// if sheet id is provided, check if teacher taking a test
	if($sid && $teacher_taking_test) {
		if($teacher_taking_test) {
			$sheet = MWDB::get_sheet($sid, mw_get_option('teacher-test-group'));
			$group_test = mw_get_option('teacher-test-group');
			$test_hw = $wpdb->get_row("SELECT id FROM wp_dict_homeworks WHERE sheet_id = {$sid} AND group_id = {$group_test}");
			$test_hw_id = $test_hw->id;
		}
		else {
			$sheet = MWDB::get_sheet($sid);
		}
		$sheet_list = array($sheet);
	}
	else {
		foreach($sheet_list as $item) {
			$_disabled = ($item->homework_type_id == HOMEWORK_SUBSCRIBED && !get_ws_subscribed()) ? ' disabled' : '';
			if(is_mw_super_admin() || is_mw_admin()) { $_disabled = ''; }
			
			$select_grade_sheets[$item->grade] .= '<option data-sheet-id="' . $item->sheet_id . '" value="' . $item->sheet_name . '"' . $_disabled . '>' . $item->sheet_name . '</option>';
			if($sid && $sid == $item->sheet_id) {
				$sheet = $item;

				// we only need 1 sheet in homework mode if sheet id is provided
				if($mode == 'homework') {
					$js_homework_list = array();
					$sheet_list = array($item);
					break;
				}
			}

			if($mode == 'homework' && $sheet->homework_id != $item->homework_id) {
				$js_homework_list[] = '{hid: ' . $item->homework_id . ', sid: ' . $item->sheet_id . ', grade: "' . $item->grade . '", sheet_num: "' . $item->sheet_name . '"}';
			}
		}
	}

	// get next homework
	if(!empty($homework_assignment->next_homework_id)) {
		$next_homework = MWDB::get_homework_assignment_by_id($homework_assignment->next_homework_id);
		$sheet->next_homework_id = $homework_assignment->next_homework_id;
		$sheet->next_sheet = empty($next_homework->name) ? $next_homework->sheet_name : $next_homework->name;
		$sheet->next_assignment_id = $next_homework->assignment_id;
	}

	$sheet_total = count($sheet_list);
	// load user answers
	if(!empty($sheet->answers)) {
		$user_answers = json_decode($sheet->answers, true);
	}
	else {
		$user_answers = json_decode($sheet->practice_answers, true);
	}

	$words = json_decode($sheet->questions, true);
	$dict_table = get_dictionary_table($sheet->dictionary_id);

	$word_total = count($words['question']);
	$_cur_word_index = 0;
	if(isset($sheet->finished_question)) {
		$_cur_word_index = $sheet->finished_question + 1;
		if($_cur_word_index == $word_total) {
			$_cur_word_index = 0;
		}
	}

	// generate javascript for default worksheet
	$questions_js = 'var words = [];';
	for($i = 0; $i < $word_total; $i++) {
		$questions_js .= 'words[' . $i . '] = ' .
			'{sentence: "' . esc_html($words['question'][$i]) . '",' .
			'c_a: "' . esc_html($words['c_answer'][$i]) . '",' .
			'quiz: "' . esc_html($words['quiz'][$i]) . '",' .
			'answers: [' .
				'["' . esc_html($words['c_answer'][$i]) . '", 1],' .
				'["' . esc_html($words['w_answer1'][$i]) . '", 0],' .
				'["' . esc_html($words['w_answer2'][$i]) . '", 0],';

		if(!empty($words['w_answer3'][$i])) {
			$questions_js .= '["' . esc_html($words['w_answer3'][$i]) . '", 0],';
		}

		if(!empty($words['w_answer4'][$i])) {
			$questions_js .= '["' . esc_html($words['w_answer4'][$i]) . '", 0]';
		}
		$ans = !isset($user_answers['q' . $i]['selected'])  ? $user_answers['q' . $i] : $user_answers['q' . $i]['selected'];
		$questions_js .= '],' .
			'selected: "' . $ans . '"' . 
			'};';
	}
	
?>
<?php get_dict_header($page_title_tag, 'green') ?>
<?php
	$info_tab_url = get_info_tab_cloud_url('Popup_info_13.jpg');
	if($mode == 'homework') {
		$box_bg = ' box-test-mode';
		$disable_select = ' disabled';		
		get_dict_page_title($page_title_tag, 'test-mode', '', array(), $info_tab_url);
	}
	else {
		$box_bg = '';
		get_dict_page_title($page_title_tag, '', '', array(), $info_tab_url);
	}
?>

	<div class="row">
		<div class="col-sm-12">
			<h3 class="med-font-size"><?php _e('Choose correct answer', 'iii-dictionary') ?></h3>
		</div>										
	</div>

	<div class="row">
		<div class="col-sm-3">
			<div class="form-group box small<?php echo $box_bg ?>"  <?php echo $teacher_taking_test ? ' style="display: none"' : '' ?>>
				<label><?php _e('LEVEL:', 'iii-dictionary') ?></label>
				<select class="select-box-it select-green" id="grade"<?php echo $disable_select ?>>
					<?php foreach($select_grade_sheets as $g => $s) : ?>
						<option value="<?php echo $g ?>"<?php echo $sid && $g == $sheet->grade ? ' selected' : '' ?>><?php echo $g ?></option>
					<?php endforeach ?>
				</select>
			</div>
		</div>
		<div class="col-sm-6">
			<div class="form-group box small<?php echo $box_bg ?>" <?php echo $teacher_taking_test ? ' style="display: none"' : '' ?>>
				<label><?php _e('LESSON:', 'iii-dictionary') ?></label>
				<select class="select-box-it select-green" id="sheet-num"<?php echo $disable_select ?>></select>
				<?php foreach($select_grade_sheets as $g => $s) : ?>
					<select id="sheet-num-<?php echo $g ?>" style="display: none">
						<?php echo $s ?>
					</select>
				<?php endforeach ?>
			</div>
		</div>
		<div class="col-sm-3 form-group">
			<div class="loading" <?php echo $teacher_taking_test ? ' style="display: none"' : '' ?>>
				<span class="icon-loading"></span>&nbsp;<?php _e('Loading...', 'iii-dictionary') ?>
			</div>
			<div class="words-pagin"><?php 
				printf(
					__('Question %s of %s', 'iii-dictionary'),
					'<span id="word-num">' . ($_cur_word_index + 1) . '</span>',
					'<span id="word-total">' . $word_total . '</span>'
				) ?>
			</div>
			<?php if($mode != 'homework') : ?>
				<button class="btn btn-default btn-block sky-blue" type="button" id="reset-counter"<?php echo $mode == 'homework' ? ' disabled' : '' ?> <?php echo $teacher_taking_test ? ' style="display: none"' : '' ?>><?php _e('Go back to 1', 'iii-dictionary') ?></button>
			<?php endif ?>
			<div class="words-pagin" id="htype-id" <?php echo $teacher_taking_test ? ' style="display: none"' : '' ?>>
				<?php if($sheet->homework_type_id == HOMEWORK_PUBLIC) _e('Worksheet - Free', 'iii-dictionary') ?>
				<?php if($sheet->homework_type_id == HOMEWORK_SUBSCRIBED) _e('Worksheet - Subscribed', 'iii-dictionary') ?>
			</div>
		</div>
	</div>
                
	<div class="row">
		<div class="col-xs-12" id="quiz-box">
			<span id="quiz"></span>
		</div>
	</div>
	<div class="row">										
		<div class="col-xs-12">
			<ul class="select-box multi-choice" id="question-box" data-placement="top" data-trigger="focus">
				<li class="vocab-keyword" id="vocab-question"></li>
			</ul>
		</div>
	</div>
	<div class="row">
		<?php 
		if($mode == 'practice') : ?>
			<div class="col-sm-<?php echo !empty($_return_url) ? 4 : 6 ?>">
				<div class="form-group">
					<button type="button" id="submit-btn" class="btn btn-default btn-block orange" data-loading-text="<?php _e('Saving...', 'iii-dictionary') ?>"><span class="icon-submit"></span><?php _e('Submit', 'iii-dictionary') ?></button>
				</div>
			</div>
		<?php endif ?>
		<div class="col-sm-<?php echo $mode == 'practice' && !empty($_return_url) ? 4 : 6 ?>">
			<div class="form-group">
				<button type="button" id="next-btn" class="btn btn-default btn-block sky-blue"><span class="icon-next"></span><?php _e('Next', 'iii-dictionary') ?></button>
			</div>
		</div>
		<?php if(is_user_logged_in() && ($mode == 'homework' || !empty($_return_url))) : ?>
			<div class="col-sm-<?php echo $mode == 'practice' && !empty($_return_url) ? 4 : 6 ?>">
				<div class="form-group">
					<?php if($mode == 'homework') : ?>
						<button type="button" id="submit-homework" class="btn btn-default btn-block orange" disabled><span class="icon-submit"></span><?php _e('Submit Homework', 'iii-dictionary') ?></button>
					<?php else : ?>
						<a href="#quit-practice-modal" data-toggle="modal" class="btn btn-default btn-block orange"><span class="icon-submit"></span><?php _e('Quit Practice', 'iii-dictionary') ?></a>
					<?php endif ?>
				</div>
			</div>
		<?php endif ?>
	</div>
	<input type="hidden" id="selected-answer" value="">
	<input type="hidden" id="current-word" value="<?php echo $_cur_word_index ?>">									
	<input type="hidden" id="rid" value="<?php echo isset($sheet->homework_result_id) ? $sheet->homework_result_id : 0 ?>">

<?php if($mode == 'homework') : ?>

	<?php if(empty($homework_sheets) && !$teacher_taking_test) : ?>

		<div id="nogroup-mode-dialog" class="modal fade modal-green" data-keyboard="false" data-backdrop="static" aria-hidden="true">
			<div class="modal-dialog">
			  <div class="modal-content">
			  <?php if(!is_student_in_group()) : ?>
				<div class="modal-header">
					<h3><?php _e('Join a Group', 'iii-dictionary') ?></h3>
				</div>
				<div class="modal-body">
					<p><?php _e('You need to join a Group to use Homework mode.', 'iii-dictionary') ?><br>
						<?php _e('To join a Group, go to Homework Status page', 'iii-dictionary') ?><br>
						<?php _e('You can get Group name and Group password from a teacher.', 'iii-dictionary') ?></p>
				</div>
			  <?php else : ?>
				<div class="modal-header">
					<h3><?php _e('No Homework', 'iii-dictionary') ?></h3>
				</div>
				<div class="modal-body">
					<p><?php _e('You do not have any homework assignment now.', 'iii-dictionary') ?></p>
				</div>
			  <?php endif ?>
				<div class="modal-footer">
					<div class="row" style="padding-left: 30px; padding-right: 30px;">				
						<div class="col-md-12">
							<a href="<?php echo locale_home_url() ?>/?r=vocabulary-practice" class="btn btn-block grey secondary"><span class="icon-switch"></span><?php _e('Go back', 'iii-dictionary') ?></a>
						</div>
					</div>
				</div>
			  </div>
			</div>
		</div>
		<script>jQuery("#nogroup-mode-dialog").modal();centerModals();</script>

	<?php endif ?>

	<div id="submit-lesson-modal-dialog" class="modal fade modal-green" data-keyboard="true" aria-hidden="true"<?php echo $teacher_taking_test ? ' data-backdrop="static"' : '' ?>>
		<div class="modal-dialog">
		  <div class="modal-content">
			<div class="modal-header">
				<h3><?php !$teacher_taking_test ? _e('The End of Homework', 'iii-dictionary') : _e('The End of Test', 'iii-dictionary') ?></h3>
			</div>
			
			<?php if(!$teacher_taking_test) : ?>
				<div class="modal-body">
					<?php _e('You have completed this homework.', 'iii-dictionary') ?><br>
					<?php _e('If you want to leave a message to your teacher, type it in the box below.', 'iii-dictionary') ?><br>
					<?php 
						if(empty($sheet->next_homework_id)) {
							_e('Click OK to submit.', 'iii-dictionary'); 
						} ?>
					<br>
					<div class="form-group">
						<textarea class="form-control" id="txt-feedback" placeholder="<?php _e('Leave feedback', 'iii-dictionary') ?>" style="resize: none"></textarea>
					</div>
					<?php if(!empty($sheet->next_homework_id)) : ?>
						<div class="homework-notice">
							<?php printf(__('Starting the next worksheet, %s?', 'iii-dictionary'), $sheet->next_sheet) ?><br>
						</div>
					<?php endif ?>
					<hr>
				</div>
				<div class="modal-footer">
					<div class="row">
						<?php if(empty($sheet->next_homework_id)) : ?>
							<div class="col-sm-12 form-group">
								<button type="button" class="btn btn-block orange confirm submit-lesson-btn" data-loading-text="<?php _e('Submitting...', 'iii-dictionary') ?>" data-ref="<?php echo $_return_url ?>"><span class="icon-accept"></span><?php _e('OK', 'iii-dictionary') ?></button>
							</div>
						<?php else : ?>
							<div class="col-sm-6 form-group">
								<button type="button" class="btn btn-block orange confirm submit-lesson-btn" data-loading-text="<?php _e('Submitting...', 'iii-dictionary') ?>" data-ref="<?php echo MWHtml::get_practice_page_url($sheet->next_assignment_id) . '&amp;mode=homework&amp;hid=' . $sheet->next_homework_id . '&amp;ref=' . $_GET['ref'] ?>"><span class="icon-accept"></span><?php _e('Yes', 'iii-dictionary') ?></button>
							</div>
							<div class="col-sm-6 form-group">
								<button type="button" class="btn btn-block grey confirm submit-lesson-btn" data-loading-text="<?php _e('Submitting...', 'iii-dictionary') ?>" data-ref="<?php echo $_return_url ?>"><span class="icon-cancel"></span><?php _e('Quit', 'iii-dictionary') ?></button>
							</div>
						<?php endif ?>
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
								<button type="button" class="btn btn-block orange confirm submit-lesson-btn" data-loading-text="<?php _e('Submitting...', 'iii-dictionary') ?>"><span class="icon-accept"></span><?php _e('OK', 'iii-dictionary') ?></button>
							</div>
						</div>
					</div>
				</div>
			<?php endif ?>
		  </div>
		</div>
	</div>

<?php else : ?>

<div id="quit-practice-modal" class="modal fade modal-green" data-keyboard="true" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
            <h3><?php _e('The end of practice session', 'iii-dictionary') ?></h3>
        </div>
		<div class="modal-body">
			<div class="homework-notice">
				<?php if(!empty($sheet->next_homework_id)) : ?>
					<?php printf(__('Starting the next worksheet, %s?', 'iii-dictionary'), $sheet->next_sheet) ?>
				<?php else : ?>
					<?php _e('The end of practice session', 'iii-dictionary') ?>
				<?php endif ?>
			</div>
		</div>
		<div class="modal-footer">
			<div class="row">
				<?php if(empty($sheet->next_homework_id)) : ?>
					<div class="col-sm-12 form-group">
						<a href="<?php echo $_return_url ?>" class="btn btn-block orange confirm"><span class="icon-accept"></span><?php _e('OK', 'iii-dictionary') ?></a>
					</div>
				<?php else : ?>
					<div class="col-sm-6 form-group">
						<a href="<?php echo locale_home_url() . '/?r=vocabulary-practice&amp;hid=' . $sheet->next_homework_id . '&amp;ref=' . $_GET['ref'] ?>" class="btn btn-block orange confirm"><span class="icon-accept"></span><?php _e('Yes', 'iii-dictionary') ?></a>
					</div>
					<div class="col-sm-6 form-group">
						<a href="<?php echo $_return_url ?>" class="btn btn-block grey confirm"><span class="icon-cancel"></span><?php _e('Quit', 'iii-dictionary') ?></a>
					</div>
				<?php endif ?>
			</div>
		</div>
      </div>
    </div>
</div>

<?php endif ?>

<script>
	var CMODE = "<?php echo $mode ?>";
	var csid = <?php echo $sheet->sheet_id // current sheet id ?>;
	var pid = <?php echo is_null($sheet->pid) ? 0 : $sheet->pid // practice result id ?>;
	var ptid = <?php echo is_numeric($_GET['hid']) ? $_GET['hid'] : 0 ?>;
	<?php echo $questions_js ?>
	<?php if($mode == 'homework') : ?>
		var homework_list = [<?php echo implode(',', $js_homework_list) ?>];
		<?php if(isset($test_hw_id)): ?>		
		var current_homework_id = <?php echo !empty($test_hw_id) ? $test_hw_id : 0 ?>;
		<?php else: ?>
		var current_homework_id = <?php echo !empty($homework_assignment->id) ? $homework_assignment->id : 0 ?>;
		<?php endif; ?>
	<?php endif ?>
	var tc_taking_test = <?php if($teacher_taking_test) echo '1'; else echo '0' ?>;
	(function($){
		$(function(){
			setup_question();
			$("#sheet-num").html($("#sheet-num-" + $("#grade").val()).html());
			$("#sheet-num").data("selectBox-selectBoxIt").refresh();
			$("#sheet-num").data("selectBox-selectBoxIt").selectOption(<?php echo $sid ? '"' . $sheet->sheet_name . '"' : 0 ?>);

			$("#reset-counter").click(function(){
				$("#word-num").html(1);
				$("#current-word").val(0);
				setup_question();
				$("#selected-answer").val("");
				$(".answer").removeClass("selected");
			});

			$("#question-box").on("click", ".answer", function(e){
				e.preventDefault();
				$(".answer").removeClass("selected");
				$(".box-letter").removeClass("omg_highlight");
				$(this).addClass("selected");
				$(this).find('span.box-letter').addClass("omg_highlight");
				$("#selected-answer").val($(this).find(".ac").text());
			});

			$("#next-btn").click(function(){
				if(CMODE == "homework"){
					var tthis = $(this);
					var _cur_word_i = $("#current-word").val();
					var _selected = $(".answer.selected").find(".ac").text();
					words[_cur_word_i].selected = _selected;
					var _a = {selected: _selected, score: $(".answer.selected").find(".ac").attr("data-score")};
					tthis.button("loading");
					save_answer(JSON.stringify(_a));
					setTimeout(function(){tthis.button("reset")}, 550);
				}
				var current_word_index = parseInt($("#current-word").val());
				if(current_word_index == (words.length - 1)) {
					current_word_index = -1;
				}
				if(current_word_index == (words.length - 2)) {
					$("#submit-homework").prop("disabled", false);
				}
				$("#submit-btn").button("reset");
				$("#current-word").val(current_word_index + 1);
				$("#word-num").html(current_word_index + 2);
				$("#selected-answer").val("");
				$(".answer").removeClass("selected");
				setup_question();
			});

			$("#submit-btn").click(function(){
				$("#question-box").popover("destroy");
				if($("#selected-answer").val().trim() == ""){
					$("#question-box").popover({content: '<span class="popover-alert"><?php _e('Please select an answer!', 'iii-dictionary') ?></span>', html: true}); 
					$("#question-box").popover("show");
					setTimeout(function(){$("#question-box").popover("destroy")}, 1000);
				}else{
					var _cur_word_i = $("#current-word").val();
					var _selected = $(".answer.selected").find(".ac").text();
					words[_cur_word_i].selected = _selected;
					var _a = {selected: _selected, score: $(".answer.selected").find(".ac").attr("data-score")};
					if(CMODE == 'practice'){
						if(_a.score == "1"){
							$("#question-box").popover({content: '<span class="popover-alert"><?php _e('Correct!', 'iii-dictionary') ?></span>', html: true});
							$("#question-box").popover("show");
						}else{
							$("#question-box").popover({content: '<span class="popover-alert"><?php _e('Wrong!', 'iii-dictionary') ?></span>', html: true});
							$("#question-box").popover("show");
						}
						setTimeout(function(){$("#question-box").popover("destroy")}, 1000);
						save_answer();
					}else{
						save_answer(JSON.stringify(_a));
					}
				}
			});

			function save_answer(answer){
				if(CMODE == "homework"){
					$("#submit-btn").button("loading");
					$.post(home_url + "/?r=ajax/homework/answer",
						{rid: $("#rid").val(), homework_id: current_homework_id, qc: words.length, q: $("#current-word").val(), answer: answer},
						function(data){
							var data = JSON.parse(data);
							$("#rid").val(data);
							$("#submit-btn").button("reset");
						}
					);
				}else {
					var _selected = $(".answer.selected").find(".ac").text();
					$.post(home_url + "/?r=ajax/practice/save",
						{pid: pid, ptid: ptid, sid: csid, q: $("#current-word").val(), answer: _selected},
						function(data){
							data = JSON.parse(data);
							pid = data[0];
						}
					);
				}
			}

			$("#submit-homework").click(function(){
				$("#submit-lesson-modal-dialog").modal();
				$("#txt-feedback").focus();
			});

			$(".submit-lesson-btn").click(function(){
				var _cur_word_i = $("#current-word").val();
				var _selected = $(".answer.selected").find(".ac").text();
				words[_cur_word_i].selected = _selected;
				var _a = {selected: _selected, score: $(".answer.selected").find(".ac").attr("data-score")};
				save_answer(JSON.stringify(_a));
				var tthis = $(this);
				tthis.button("loading");
				$.post(home_url + "/?r=ajax/homework/submit",
					{homework_id: current_homework_id, teacher_taking_test: tc_taking_test , rid: $("#rid").val(), feedback: $("#txt-feedback").val()},
					function(data){
						<?php if($teacher_taking_test) : ?>
							window.location.href = home_url + "/?r=my-account";
						<?php else : ?>
							window.location.href = tthis.attr("data-ref");
						<?php endif ?>
					}
				);
			});

			$("#switch-mode").click(function(){
				var mode = $(this).attr("data-mode");
				if(mode == "homework"){
					window.location.href = home_url + "/?r=vocabulary-practice";
				}
			});

			$("#grade").on("option-click", function(){
				$("#sheet-num").html($("#sheet-num-" + $(this).val()).html());
				$("#sheet-num").data("selectBox-selectBoxIt").refresh();
				$("#sheet-num").trigger("option-click");
			});

			$("#sheet-num").on("option-click", function(){
				$(".words-pagin").hide();
				$(".loading").fadeIn();
				$("#submit-btn").prop("disabled", true);
				$("#next-btn").prop("disabled", true);
				var sid = $("#sheet-num :selected").attr("data-sheet-id");
				$.getJSON(home_url + "/?r=ajax/question",
					{sid: sid },
					function(data){
						csid = sid;
						pid = data.pid;
						words = data.sheet[0];
						$("#word-total").html(data.sheet[0].length);
						$("#word-num").html(1);
						$("#htype-id").text(data.htype);
						$("#submit-btn").prop("disabled", false);
						$("#next-btn").prop("disabled", false);
						$("#current-word").val(0);
						$(".loading").hide();
						$(".words-pagin").fadeIn();
						$("#selected-answer").val("");
						$(".answer").removeClass("selected");
						setup_question();
					}
				);
			});
		});

		function setup_question(){
			var _ci = $("#current-word").val();
			$("#vocab-question").html(words[_ci].sentence);
			$("#quiz").html(words[_ci].quiz);
			var choices;
			choices = shuffle(words[_ci].answers);
			$("#question-box").find("li").not(":first-child").remove();
			$.each(choices, function(i,v){
				if(v[0] == words[_ci].selected){
					selected = " selected";
					hight_light = " omg_highlight";
					$("#selected-answer").val(v[0]);
				}else{
					selected = "";
					hight_light = "";
				}

				var li = '<li><a href="#" class="answer' + selected + '"><span class="box-letter '+ hight_light + '">' + String.fromCharCode("A".charCodeAt(0) + i) + '</span> <span class="ac" data-score="' + v[1] + '">' + v[0] + '</span></a></li>';
				$("#question-box").append(li);
			});
		}
	})(jQuery);
</script>
<?php get_dict_footer() ?>