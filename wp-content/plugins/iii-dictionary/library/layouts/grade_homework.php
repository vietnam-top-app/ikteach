<?php

	$hid = $_GET['hid'];
	$sid = $_GET['sid'];
	$hrid = isset($_GET['hrid']) ? $_GET['hrid'] : 0;

	if(!$hrid) {
		$page_url = locale_home_url() . '/?r=grade-homework&hid=' . $hid . '&sid=' . $sid;
	}
	else {
		$page_url = locale_home_url() . '/?r=grade-homework&hrid=' . $hrid;
	}

	if(isset($_GET['admin'])) {
		$page_url .= '&admin=1';
	}

	if(isset($_POST['submit'])) 
	{
		$_REAL_POST['graded_by'] = get_current_user_id();

		if(MWDB::grade_homework($_REAL_POST))
		{
			if($_GET['admin']) {
				// set teacher's role to qualified if he passed the test
				if($_POST['score'] >= mw_get_option('teacher-test-score-threshold')) {
					$user = get_user_by('id', $sid);
					$user->add_role('mw_qualified_teacher');
				}
			}

			if($_SESSION['grading_homework']['hrid'] === $hrid) {
				// finish a grading request
                                if($_SESSION['status']==1){
                                    if(ik_finish_grading_request($_SESSION['grading_homework']['req_id'],1,$_SESSION['grading'])) {
                                            wp_redirect(locale_home_url() . '/?r=teaching/teach-class');
                                            exit;
                                    }
                                }else{
                                    if(ik_finish_grading_request($_SESSION['grading_homework']['req_id'])) {
                                            wp_redirect(locale_home_url() . '/?r=teaching/teach-class');
                                            exit;
                                    }
                                }
			}

			wp_redirect($page_url);
			exit;
		}
	}

	if(!empty($_SESSION['grading_homework']) && $_SESSION['grading_homework']['hrid'] === $hrid) {
                if($_SESSION['status']==1){
                    $result = MWDB::get_homework_result_status($hrid);
                }else{
                    $result = MWDB::get_homework_result($hrid);
                }
		$result->homework_result_id = $hrid;
		$homework_result = array($result);
	}
	else {
		$homework_result = MWDB::get_homework_results($hid, $sid);
	}
        
	$questions = json_decode($homework_result[0]->questions, true);
	$answers = json_decode($homework_result[0]->answers, true);
	$teacher_comments = json_decode($homework_result[0]->teacher_comments, true);

	// force using red as default text color
	add_filter('tiny_mce_before_init', 'wpse24113_tiny_mce_before_init');
	function wpse24113_tiny_mce_before_init($init) {
			$init['setup'] = '[function(ed) {' .
				'ed.onKeyDown.add(function(ed, e) {'.
					'if(tinyMCE.activeEditor.selection.getNode().style.color != "rgb(255, 0, 0)"){tinyMCE.activeEditor.execCommand("ForeColor", false, "#ff0000")}' .
				'});'.
			'}][0]';

		return $init;
	}

?>
<?php get_dict_header(__('Teacher\'s Box - Grade Homework', 'iii-dictionary')) ?>
<?php get_dict_page_title(__('Teacher\'s box - Grade Homework', 'iii-dictionary')) ?>

	<form method="post" action="<?php echo htmlspecialchars($page_url) ?>">
		<div class="row homework-result-header">
			<div class="col-sm-4">
				<label><?php _e('Student:', 'iii-dictionary') ?> <span><?php echo $homework_result[0]->display_name ?></span></label>
			</div>
			<?php if(!empty($homework_result[0]->group_name)) : ?>
				<div class="col-sm-8">
					<label><?php _e('Group:', 'iii-dictionary') ?> <span><?php echo $homework_result[0]->group_name ?></span></label>
				</div>
			<?php endif ?>
			<div class="col-sm-12">
				<label><?php _e('Homework:', 'iii-dictionary') ?> <span><?php echo $homework_result[0]->sheet_name ?></span></label>
			</div>
			<div class="col-sm-12">
				<h2 class="title-border"><?php _e('Answers', 'iii-dictionary') ?></h2>
			</div>

			<?php foreach($questions['question'] as $key => $question) : ?>
				<div class="col-sm-12">
					<strong class="heading3 text-primary"><?php printf(__('Subject %s', 'iii-dictionary'), $key + 1) ?></strong>
				</div>
				<div class="col-sm-12">
					<div class="form-group box">
						<div class="scroll-list" style="max-height: 150px; color: #fff">
							<?php echo nl2br($question) ?>
						</div>
					</div>
				</div>
				<div class="col-sm-12">
					<strong class="heading3 text-success"><?php _e('Essay', 'iii-dictionary') ?></strong>
				</div>
				<div class="col-sm-12">
					<div class="form-group">
						<?php 
							$editor_settings = array(
								'textarea_name' => 'graded_answers[q' . $key . ']',
								'wpautop' => false,
								'media_buttons' => false,
								'quicktags' => false,
								'textarea_rows' => 10,
								'tinymce' => true
							);
							wp_editor($answers['q' . $key] . '<p></p>', 'writing_essay' . $key, $editor_settings); ?>
					</div>
				</div>
				<div class="col-sm-12">
					<strong class="heading3 text-danger"><?php _e('Comments', 'iii-dictionary') ?></strong>
				</div>
				<div class="col-sm-12">
					<div class="form-group">
						<textarea class="form-control" name="comments[q<?php echo $key ?>]" style="resize: vertical"><?php echo $teacher_comments['q' . $key] ?></textarea>
					</div>
				</div>
			<?php endforeach ?>
			<div class="col-sm-3 col-sm-offset-3 form-inline">
				<div class="form-group">
					<strong><?php _e('Score:', 'iii-dictionary') ?></strong>
					<input type="number" min="0" max="100" class="form-control" name="score" value="<?php echo $homework_result[0]->score ?>" style="width: 100px;"> %
				</div>
			</div>
			<div class="col-sm-3">
				<button type="submit" name="submit" class="btn btn-default btn-block orange form-control"><span class="icon-save"></span><?php _e('Grade', 'iii-dictionary') ?></button>
			</div>
			<div class="col-sm-3">
				<?php 
					if(!empty($_SESSION['grading_homework']) && $_SESSION['grading_homework']['hrid'] === $hrid) {
						$goback_url = locale_home_url() . '/?r=teaching/teach-class';
					}
					else if(isset($_GET['admin'])) {
						$goback_url = site_admin_url() . '/?r=view-user&cid=' . $sid;
					}
					else {
						$goback_url = locale_home_url() . '/?r=teachers-box';
					}
				?>
				<a href="<?php echo $goback_url ?>" class="btn btn-default btn-block grey form-control"><span class="icon-goback"></span><?php _e('Go back', 'iii-dictionary') ?></a>
			</div>
		</div>
		<input type="hidden" name="hrid" value="<?php echo $homework_result[0]->homework_result_id ?>">
	</form>

<?php get_dict_footer() ?>