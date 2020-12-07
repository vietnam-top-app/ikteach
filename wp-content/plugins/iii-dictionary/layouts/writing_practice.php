<?php
	$page_title_tag = __('Writing Homework', 'iii-dictionary');
	include IK_PLUGIN_DIR . '/library/formatter.php';
	$mode = get_query_var('mode', 'practice');
	$select_grade_sheets = $insql = $tmp = $js_homework_list = array();
	$cur_sheet_index = $count = 0;
	$sid = isset($_GET['sid']) ? $_GET['sid'] : 0;
	$_ws_default = 0;
	$_return_url = base64_decode(rawurldecode($_GET['ref']));
	// is teacher taking a test?
	$teacher_taking_test = in_array($sid, (array) $_SESSION['teacher_tests']);
	
	$homework_sheets = MWDB::get_homework_sheets(ASSIGNMENT_WRITING);
	$sheet_list = $homework_sheets;

	if($mode == 'practice')
	{
		$page_title_tag = __('Writing Practice', 'iii-dictionary');
		$practice_sheets = MWDB::get_practice_sheets(ASSIGNMENT_WRITING);

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
	
	if(empty($_GET['hid']) && $teacher_taking_test) {
		$homework_assignment 		= new stdClass;
		$filter['group_id'] 		= mw_get_option('teacher-test-group');
		$filter['sheet_id'] 		= $sid;
		$data 						= MWDB::get_homework_assignments($filter);
		$homework_assignment->id 	= $data->items[0]->id;
	}

	// user didn't select a homework, redirect back to practice page
	if($mode == 'homework' && !$sid) {
		wp_redirect(locale_home_url() . '/?r=writing-practice');
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
			$_disabled = (($item->homework_type_id == HOMEWORK_SUBSCRIBED && !get_ws_subscribed()) || (in_array($item->homework_type_id, array(HOMEWORK_MY_OWN, HOMEWORK_LICENSED, HOMEWORK_CLASS)) && !is_user_logged_in()) ) ? ' disabled' : '';
			if(is_mw_super_admin() || is_mw_admin()) { $_disabled = ''; }
			
			if(!$sid && $item->ws_default == 1 && !is_user_logged_in() && $_ws_default == 0){
				$_ws_default = $item->sheet_id;
				$sheet = $item;
				$select_grade_sheets[$item->grade] = '<option data-sheet-id="' . $item->sheet_id . '" value="' . $item->sheet_name . '"' . $_disabled . '>' . $item->sheet_name . '</option>' . $select_grade_sheets[$item->grade];
				GOTO IGNORE;
			}
			$select_grade_sheets[$item->grade] .= '<option data-sheet-id="' . $item->sheet_id . '" value="' . $item->sheet_name . '"' . $_disabled . '>' . $item->sheet_name . '</option>';
			IGNORE:
			
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

	$words = json_decode($sheet->questions, true);
	$dict_table = get_dictionary_table($sheet->dictionary_id);

	// load user answers
	if(!empty($sheet->answers)) {
		$answers = json_decode($sheet->answers, true);
	}
	else {
		$answers = json_decode($sheet->practice_answers, true);
	}

	$word_total = count($words['question']);
	$_cur_word_index = 0;
	if(isset($sheet->finished_question)) {
		$_cur_word_index = $sheet->finished_question + 1;
		if($_cur_word_index == $word_total) {
			$_cur_word_index = 0;
		}
	}

	// generate javascript for default worksheet
	$jsvar = 'var words = [];';
	for($i = 0; $i < $word_total; $i++)
	{
		$ans = '';
		if(isset($answers['q' . $i])) {
			$ans = $answers['q' . $i];
		}

		$jsvar .= 'words[' . $i . '] = {sentence: ' . json_encode($words['question'][$i]) .
									 ', quiz: ' . json_encode($words['quiz'][$i]) . 
									 ', selected: ' . json_encode($ans) . '};';
	}
?>
<?php get_dict_header($page_title_tag, 'green') ?>
<?php
	$info_tab_url = get_info_tab_cloud_url('Popup_info_15.jpg');
	if($mode == 'homework') {
		$box_bg = ' box-test-mode';
		$disable_select = ' disabled';		
		get_dict_page_title($page_title_tag, 'test-mode', '', array(), $info_tab_url);
	}
	else {
		$box_bg = '';
		$disable_select = ($sid && is_user_logged_in() ) ? ' disabled' : '';
		get_dict_page_title($page_title_tag, '', '', array(), $info_tab_url);
	}
?>

	<div class="row">
		<div class="col-sm-12">
			<h3 class="med-font-size"><?php _e('Write an essay', 'iii-dictionary') ?></h3>
		</div>										
	</div>
	<div class="row"<?php echo $teacher_taking_test ? ' style="display: none"' : '' ?>>
		<div class="col-sm-3">
			<div class="form-group box small<?php echo $box_bg ?>">
				<label><?php _e('LEVEL:', 'iii-dictionary') ?></label>
				<select class="select-box-it select-green" id="grade"<?php echo $disable_select ?>>
					<?php foreach($select_grade_sheets as $g => $s) : ?>
						<option value="<?php echo $g ?>"<?php echo $sid && $g == $sheet->grade ? ' selected' : '' ?>><?php echo $g ?></option>
					<?php endforeach ?>
				</select>
			</div>
		</div>
		<div class="col-sm-6">
			<div class="form-group box small<?php echo $box_bg ?>">
				<label><?php _e('LESSON:', 'iii-dictionary') ?></label>
				<select class="select-box-it select-green" id="sheet-num"<?php echo $disable_select ?>>
				<?php foreach($select_grade_sheets as $g => $s) : ?>
						<?php echo $s ?>
				<?php endforeach ?>
				</select>
			</div>
		</div>
		<div class="col-sm-3 form-group">
			<div class="loading">
				<span class="icon-loading"></span>&nbsp;<?php _e('Loading...', 'iii-dictionary') ?>
			</div>
			<div class="words-pagin">
				<?php printf(__('Question %s of %s', 'iii-dictionary'),
						'<span id="word-num">1</span>',
						'<span id="word-total">' . $word_total . '</span>') ?>
			</div>
			<button class="btn btn-default btn-block sky-blue" type="button" id="reset-counter"><?php _e('Go back to 1', 'iii-dictionary') ?></button>
			<div class="words-pagin" id="htype-id">
				<?php if($sheet->homework_type_id == HOMEWORK_PUBLIC) _e('Worksheet - Free', 'iii-dictionary') ?>
				<?php if($sheet->homework_type_id == HOMEWORK_SUBSCRIBED) _e('Worksheet - Subscribed', 'iii-dictionary') ?>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col-xs-12" id="quiz-box">
			<span id="quiz"></span>
		</div>
		<div class="col-xs-12">
			<div class="form-group select-box" id="writing-box">
				<label id="vocab-question"></label>
				<?php 
					$settings = array(
						'wpautop' => false,
						'media_buttons' => false,
						'quicktags' => false,
						'textarea_rows' => 10,
						'tinymce' => array(
							'toolbar1' => 'formatselect,bold,italic,underline,blockquote,alignleft,aligncenter,alignright,alignjustify,removeformat,charmap,outdent,indent,undo,redo,wp_help,fullscreen',
							'toolbar2' => ''
						)
					);
					wp_editor($answers['q0'], 'writing_essay', $settings); ?>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-4">
			<div class="form-group">
				<button type="button" id="next-btn" class="btn btn-default btn-block sky-blue"><span class="icon-next"></span><?php _e('Next', 'iii-dictionary') ?></button>
			</div>
		</div>
		<?php if(is_user_logged_in() && ($mode == 'homework' || !empty($_return_url))) : ?>
			<div class="col-sm-4">
				<div class="form-group">
					<?php if($mode == 'homework') : ?>
						<button type="button" id="submit-btn" class="btn btn-default btn-block orange" disabled><span class="icon-submit"></span><?php _e('Submit Homework', 'iii-dictionary') ?></button>
					<?php else : ?>
						<a href="#quit-practice-modal" data-toggle="modal" class="btn btn-default btn-block orange"><span class="icon-submit"></span><?php _e('Quit Practice', 'iii-dictionary') ?></a>
					<?php endif ?>
				</div>
			</div>
		<?php endif ?>
	</div>
	<input type="hidden" id="current-word" value="0">									
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
					<div class="row">				
						<div class="col-md-12">
							<a href="<?php echo locale_home_url() ?>/?r=writing-practice" class="btn btn-block grey secondary"><span class="icon-switch"></span><?php _e('Go back', 'iii-dictionary') ?></a>
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
				<a href="#" data-dismiss="modal" aria-hidden="true" class="close"></a>
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
						<a href="<?php echo $_return_url ?>" class="btn btn-block orange"><span class="icon-accept"></span><?php _e('OK', 'iii-dictionary') ?></a>
					</div>
				<?php else : ?>
					<div class="col-sm-6 form-group">
						<a href="<?php echo locale_home_url() . '/?r=writing-practice&amp;hid=' . $sheet->next_homework_id . '&amp;ref=' . $_GET['ref'] ?>" class="btn btn-block orange"><span class="icon-accept"></span><?php _e('Yes', 'iii-dictionary') ?></a>
					</div>
					<div class="col-sm-6 form-group">
						<a href="<?php echo $_return_url ?>" class="btn btn-block grey"><span class="icon-cancel"></span><?php _e('Quit', 'iii-dictionary') ?></a>
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
	<?php echo $jsvar ?>
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
			if(CMODE == "homework") {
				is_all_questions_answered();
			}
			function is_all_questions_answered(){
				var $answered = true;
				$.each(words, function(i,v){
					if(v.selected == ""){
						$answered = false;
					}
				});
				if($answered){
					$("#submit-test").parent().parent().removeClass("hidden");
				}
			}

			setup_question();
			$("#sheet-num").html($("#sheet-num-" + $("#grade").val()).html());
			$("#sheet-num").data("selectBox-selectBoxIt").refresh();
			$("#sheet-num").data("selectBox-selectBoxIt").selectOption(<?php echo $sid ? '"' . $sheet->sheet_name . '"' : 0 ?>);

			$("#reset-counter").click(function(){
				$("#word-num").html(1);
				$("#current-word").val(0);
				setup_question();
			});

			$("#next-btn").click(function(){
				save_answer($(this));
				var current_word_index = parseInt($("#current-word").val());
				if(current_word_index == (words.length - 1)) {
					current_word_index = -1;
				}
				if(current_word_index == (words.length - 2)) {
					$("#submit-btn").prop("disabled", false);
				}
				$("#submit-btn").button("reset");
				$("#current-word").val(current_word_index + 1);
				$("#word-num").html(current_word_index + 2);
				setup_question();
				if(CMODE == "homework") {
					is_all_questions_answered();
				}
			});

			function save_answer(button){
				var tthis = button;
				var $popover = $("#wp-writing_essay-wrap");
				var content = tinyMCE.activeEditor.getContent();
				$popover.popover("destroy");
				if(content.trim() == ""){
					$popover.popover({content: '<span class="popover-alert"><?php _e('Please write your essay!', 'iii-dictionary') ?></span>', html: true, placement: "top"}); 
					$popover.popover("show");
					setTimeout(function(){$popover.popover("destroy")}, 1500);
				}else{
					tthis.button("loading");
					words[$("#current-word").val()].selected = content;
					if(CMODE == "homework"){
						$.post(home_url + "/?r=ajax/homework/answer",
							{rid: $("#rid").val(), homework_id: current_homework_id, qc: words.length, q: $("#current-word").val(), answer: content, graded: 0, writing: 1},
							function(data){
								tthis.button("reset");
								var data = JSON.parse(data);
								$("#rid").val(data);
								is_all_questions_answered();
							}
						);
					}else{
						$.post(home_url + "/?r=ajax/practice/save",
							{pid: pid, ptid: ptid, sid: csid, q: $("#current-word").val(), answer: content},
							function(data){
								tthis.button("reset");
								data = JSON.parse(data);
								pid = data[0];
							}
						);
					}
				}
			}

			$("#submit-btn").click(function(){
				save_answer($(this));
				$("#submit-lesson-modal-dialog").modal();
				$("#txt-feedback").focus();
			});

			$(".submit-lesson-btn").click(function(){
				var tthis = $(this);
				tthis.button("loading");
				$.post(home_url + "/?r=ajax/homework/submit",
					{homework_id: current_homework_id,teacher_taking_test: tc_taking_test , rid: $("#rid").val(), feedback: $("#txt-feedback").val()},
					function(data){
						<?php if($teacher_taking_test) : ?>
							window.location.href = home_url + "/?r=my-account";
						<?php else : ?>
							window.location.href = tthis.attr("data-ref");
						<?php endif ?>
					}
				);
			});

			$("#grade").on("option-click", function(){
				$("#sheet-num").html($("#sheet-num-" + $(this).val()).html());
				$("#sheet-num").data("selectBox-selectBoxIt").refresh();
				if(!$('#sheet-num').find('option').eq(0).is(':disabled')) {
					$("#sheet-num").trigger("option-click");
				}
			});

			$("#sheet-num").on("option-click", function(){
				$(".words-pagin").hide();
				$(".loading").fadeIn();
				$("#submit-btn").prop("disabled", true);
				$("#next-btn").prop("disabled", true);
				var sid = $("#sheet-num :selected").attr("data-sheet-id");
				$.getJSON(home_url + "/?r=ajax/question",
					{sid: sid},
					function(data){
						
						csid = sid;
						pid = data.pid;
						$("#word-total").html(0);
						words = [];
						if(data.sheet[0] != null){
							words = data.sheet[0];
							$("#word-total").html(data.sheet[0].length);
						}
						$("#reading-passage").html(data.sheet.passage);
						$("#word-num").html(1);
						$("#htype-id").text(data.htype);
						$("#submit-btn").prop("disabled", false);
						$("#next-btn").prop("disabled", false);
						$("#current-word").val(0);
						$(".loading").hide();
						$(".words-pagin").fadeIn();
						setup_question();
					}
				);
			});
		});

		function setup_question(){
			if(typeof words[$("#current-word").val()] != "undefined"){
				$("#vocab-question").html(words[$("#current-word").val()].sentence.replace(/(?:\r\n|\r|\n)/g, "<br>"));
				$("#quiz").html(words[$("#current-word").val()].quiz);
				if(tinyMCE.activeEditor){
					tinyMCE.activeEditor.setContent(words[$("#current-word").val()].selected);
				}
			}else{
				$("#vocab-question").html("");
				$("#quiz").html("");
				if(tinyMCE.activeEditor){
					tinyMCE.activeEditor.setContent("");
				}
			}
		}
	})(jQuery);
</script>
<?php get_dict_footer() ?>