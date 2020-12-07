<?php

	$hid = $_GET['hid'];
	$sid = $_GET['sid'];
	
	$is_math_panel = is_math_panel();
	$_page_title = __('Homework Result', 'iii-dictionary');

	$homework_result = MWDB::get_homework_results($hid, $sid);
	$questions = json_decode($homework_result[0]->questions, true);
	$answers = json_decode($homework_result[0]->answers, true);
	$teacher_comments = json_decode($homework_result[0]->teacher_comments, true);
?>
<?php if(!$is_math_panel) : ?>
	<?php get_dict_header($_page_title) ?>
<?php else : ?>
	<?php get_math_header($_page_title, 'red-brown') ?>
<?php endif ?>
<?php get_dict_page_title($_page_title) ?>
	<div class="row homework-result-header">
		<?php if($homework_result[0]->assignment_id == ASSIGNMENT_WRITING) : ?>
			<div class="col-sm-12">
				<label><?php _e('Teacher', 'iii-dictionary') ?></label>
				<div id="profile-picture">
					<?php 
						$user_avatar = ik_get_user_avatar($homework_result[0]->graded_by);
						  if(!empty($user_avatar)) : ?>
							<img src="<?php echo $user_avatar ?>" width="100" height="100" alt="">
					<?php else :
							echo get_avatar($homework_result[0]->graded_by, 120);
						  endif ?>
				</div>
			</div>
		<?php endif ?>
		<?php if(!empty($homework_result[0]->dictionary)) : ?>
			<div class="col-sm-4 col-md-3">
				<label><?php _e('Dictionary:', 'iii-dictionary') ?> <span><?php echo $homework_result[0]->dictionary ?></span></label>
			</div>
		<?php endif ?>
		<div class="col-sm-4 col-md-9">
			<label><?php _e('Level:', 'iii-dictionary') ?> <span><?php echo $homework_result[0]->grade ?></span></label>
		</div>
		<div class="clearfix"></div>
		<div class="col-sm-4 col-md-3">
			<label><?php _e('Score:', 'iii-dictionary') ?> <span>
				<?php if($homework_result[0]->assignment_id == ASSIGNMENT_WRITING) : ?>
					<?php printf(__('%d %%', 'iii-dictionary'), $homework_result[0]->score) ?>
				<?php else : ?>
					<?php printf(__('%d correct, %d %%', 'iii-dictionary'), $homework_result[0]->correct_answers_count, $homework_result[0]->score) ?>
				<?php endif ?>
			</span></label>
		</div>
		<div class="col-sm-5 col-md-4">
			<label><?php _e('Last Attempt:', 'iii-dictionary') ?> <span><?php echo ik_date_format($homework_result[0]->attempted_on) ?></span></label>
		</div>
		<div class="col-sm-5">
			<label><?php _e('Completed Date:', 'iii-dictionary') ?> <span><?php echo ik_date_format($homework_result[0]->submitted_on) ?></span></label>
		</div>
		<div class="col-sm-12">
			<label><?php _e('Lesson:', 'iii-dictionary') ?> <span><?php echo $homework_result[0]->sheet_name ?></span></label>
		</div>
	</div>

	<div class="row">

		<?php switch($homework_result[0]->assignment_id) {

				case ASSIGNMENT_SPELLING: ?>

						<div class="col-sm-12">
							<h2 class="title-border"><?php _e('Missed Word', 'iii-dictionary') ?></h2>
						</div>
						<?php foreach($answers as $key => $item) : $n = substr($key, 1) ?>

							<div class="col-sm-12">
								<div class="box box-red form-group">
									<strong class="text-primary"><?php echo __('Question', 'iii-dictionary') . ' ' . ($n + 1) ?></strong>
									<div>
										<audio controls>
											<source src="<?php echo $item['question'] ?>" type="audio/mpeg">
										</audio>
									</div>
									<strong class="heading3 text-success"><?php _e('Correct Answer', 'iii-dictionary') ?></strong>
									<div style="color: #fff">
										<?php echo $questions[$n] ?>
									</div>
									<strong class="heading3 text-success"><?php _e('Your Answer', 'iii-dictionary') ?></strong>
									<div style="color: #<?php echo $answers['q' . $key]['score'] ? 'fff' : 'FF5959' ?>">
										<?php echo $item['selected'] ?>
									</div>
								</div>
							</div>

				<?php endforeach;
					break; // end spelling case

				case ASSIGNMENT_VOCAB_GRAMMAR:
				case ASSIGNMENT_READING:

					if($homework_result[0]->assignment_id == ASSIGNMENT_READING) : ?>
						<div class="col-sm-12">
							<strong class="heading3 text-muted"><?php _e('Article', 'iii-dictionary') ?></strong>
						</div>
						<div class="col-sm-12">
							<div class="form-group box">
								<div class="scroll-list" style="max-height: 300px; color: #fff">
									<?php echo $homework_result[0]->passages ?>
								</div>
							</div>
						</div>
					<?php endif ?>
						<div class="col-sm-12">
							<h2 class="title-border"><?php _e('Answers', 'iii-dictionary') ?></h2>
						</div>
						<?php foreach($questions['question'] as $key => $item) : ?>

							<div class="col-sm-12">
								<div class="box box-red form-group">
									<strong class="heading3 text-primary"><?php echo __('Question', 'iii-dictionary') . ' ' . ($key + 1) ?></strong>
									<div class="scroll-list" style="max-height: 150px; color: #fff">
										<?php echo $item ?>
									</div>
									<strong class="heading3 text-success"><?php _e('Correct Answer', 'iii-dictionary') ?></strong>
									<div style="color: #fff">
										<?php echo $questions['c_answer'][$key] ?>
									</div>
									<strong class="heading3 text-success"><?php _e('Your Answer', 'iii-dictionary') ?></strong>
									<div style="color: #<?php echo $answers['q' . $key]['score'] ? 'fff' : 'FF5959' ?>">
										<?php echo $answers['q' . $key]['selected'] ?>
									</div>
								</div>
							</div>

					<?php endforeach;
					break; // end vocabulary and reading case

				case ASSIGNMENT_WRITING:

					if(!empty($questions['question'])) : ?>
						<div class="col-sm-12">
							<h2 class="title-border"><?php _e('Grading Results', 'iii-dictionary') ?></h2>
						</div>
						<?php foreach($questions['question'] as $key => $question) : ?>

							<div class="col-sm-12">
								<strong class="heading3 text-primary"><?php _e('Subject', 'iii-dictionary') ?></strong>
							</div>
							<div class="col-sm-12">
								<div class="form-group box">
									<div class="scroll-list" style="max-height: 150px; color: #fff">
										<?php echo nl2br($question) ?>
									</div>
								</div>
							</div>
							<div class="col-sm-12">
								<strong class="heading3 text-success"><?php _e('Your Essay', 'iii-dictionary') ?></strong>
							</div>
							<div class="col-sm-12">
								<div class="form-group box box-sapphire" style="word-wrap: break-word">
									<?php echo $answers['q' . $key] ?>
								</div>
							</div>
							<div class="col-sm-12">
								<strong class="heading3 text-danger"><?php _e('Teacher\'s Comments', 'iii-dictionary') ?></strong>
							</div>
							<div class="col-sm-12">
								<div class="form-group" style="color: #fff">
									<?php echo empty($teacher_comments['q' . $key]) ? 'No comments' : $teacher_comments['q' . $key] ?>
								</div>
							</div>
				<?php 	endforeach;
					else : ?>

						<div class="col-sm-12 form-group">
							<div class="box box-sapphire">
								<strong class="heading3 text-success"><?php _e('All of your answers is correct', 'iii-dictionary') ?></strong>
							</div>
						</div>

				<?php endif;
					break; // end writing case

				case MATH_ASSIGNMENT_SINGLE_DIGIT:
				case MATH_ASSIGNMENT_TWO_DIGIT_MUL:
				case MATH_ASSIGNMENT_SINGLE_DIGIT_DIV:
				case MATH_ASSIGNMENT_TWO_DIGIT_DIV: ?>

					<div class="col-sm-12">
						<div class="box box-red form-group">
							<strong class="heading3 text-primary"><?php echo __('Question', 'iii-dictionary') ?></strong>
							<div style="max-height: 150px; color: #fff">
								<?php echo $questions['op1'] . ' ' . $questions['sign'] . ' ' . $questions['op2'] ?> =
							</div>
							<strong class="heading3 text-success"><?php _e('Correct Answer', 'iii-dictionary') ?></strong>
							<div style="color: #fff">
								<?php echo $questions['step']['s' . count($questions['step'])] ?>
							</div>
							<strong class="heading3 text-success"><?php _e('Your Answer', 'iii-dictionary') ?></strong>
							<div style="color: #<?php echo $homework_result[0]->score ? 'fff' : 'FF5959' ?>">
								<?php echo implode('', $answers['s' . count($questions['step'])]) ?>
							</div>
						</div>
					</div>

					<?php break; // end math addition, substraction, multiplication and division case

				case MATH_ASSIGNMENT_FLASHCARD:
				case MATH_ASSIGNMENT_FRACTION:

					foreach($questions['q'] as $key => $item) : ?>

						<div class="col-sm-12">
							<div class="box box-red form-group">
								<strong class="heading3 text-primary"><?php echo __('Question', 'iii-dictionary') . ' ' . substr($key, 1) ?></strong>
								<div style="max-height: 150px; color: #fff">
									<?php echo $item['op1'] . ' ' . $item['op'] . ' ' . $item['op2'] ?> =
								</div>
								<strong class="heading3 text-success"><?php _e('Correct Answer', 'iii-dictionary') ?></strong>
								<div style="color: #fff">
									<?php echo $item['answer'] . ' ' . $item['note'] ?>
								</div>
								<strong class="heading3 text-success"><?php _e('Your Answer', 'iii-dictionary') ?></strong>
								<div style="color: #fff">
									<?php if($homework_result[0]->assignment_id == MATH_ASSIGNMENT_FLASHCARD) : ?>
										<?php echo implode('/', (array) $answers[$key]) . ' ' . $item['note'] ?>
									<?php else : ?>
										<?php echo count($answers[$key]) == 3 ? $answers[$key][0] . ' ' . $answers[$key][1] . '/' . $answers[$key][2] : implode('/', $answers[$key]) ?>
									<?php endif ?>
								</div>
							</div>
						</div>

					<?php endforeach;
						break; // end math flashcard and fraction case

				case MATH_ASSIGNMENT_WORD_PROB: ?>

					<div class="col-sm-12">
						<div class="box box-red form-group">
							<strong class="heading3 text-success"><?php _e('Correct Answer', 'iii-dictionary') ?></strong>
							<div style="color: #fff">
								<?php echo $questions['answer'] ?>
							</div>
							<strong class="heading3 text-success"><?php _e('Your Answer', 'iii-dictionary') ?></strong>
							<div style="color: #<?php echo $homework_result[0]->score ? 'fff' : 'FF5959' ?>">
								<?php echo $answers ?>
							</div>
						</div>
					</div>

					<?php break; // end math word problem case

				case MATH_ASSIGNMENT_QUESTION_BOX:
				case MATH_ASSIGNMENT_EQUATION:
					foreach($questions['q'] as $key => $item) : if($item['answer'] != '') : ?>

					<div class="col-sm-12">
						<div class="box box-red form-group">
							<strong class="heading3 text-primary">
								<?php echo $homework_result[0]->assignment_id == MATH_ASSIGNMENT_EQUATION ?
									__('Question', 'iii-dictionary') :
									__('Step', 'iii-dictionary');
									echo ' ' . substr($key, 1) ?></strong>
							<strong class="heading3 text-success"><?php _e('Correct Answer', 'iii-dictionary') ?></strong>
							<div style="color: #fff"><?php echo $item['answer'] ?></div>
							<strong class="heading3 text-success"><?php _e('Your Answer', 'iii-dictionary') ?></strong>
							<div style="color: #fff"><?php echo $answers[$key] ?></div>
						</div>
					</div>

					<?php endif; endforeach;
					break; // end math question box case

				case MATH_ASSIGNMENT_EQUATION: ?>

					<?php break; // end euqation case
			} ?>

		<div class="col-sm-3">
			<a href="<?php echo locale_home_url() . '/?r=homework-status&amp;gid=' . $homework_result[0]->group_id ?>" class="btn btn-default btn-block grey form-control"><span class="icon-goback"></span><?php _e('Back', 'iii-dictionary') ?></a>
		</div>
	</div>

<?php if(!$is_math_panel) : ?>
	<?php get_dict_footer() ?>
<?php else : ?>
	<?php get_math_footer() ?>
<?php endif ?>