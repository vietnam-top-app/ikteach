<?php

	$teacher_m = $_GET['t_m']; // teacher months sub
	$num_students = $_GET['t_s']; // no of students
	$dictionary_id = $_GET['d']; // dictionary id
	$dictionary_m = $_GET['d_m']; // dictionary months
	$sat_months = $_GET['s_m']; // SAT months
	$sub_type = $_GET['s_t'];

	$sat_class_types = MWDB::get_group_class_types(false, 1);

	if(isset($_POST['generate']))
	{
		$teacher_m = $_POST['no_of_months_teacher_tool'];
		$num_students = $_POST['subscription-type'] != SUB_SELF_STUDY ? $_POST['no_of_students'] : 1;
		$dictionary_id = $_POST['dictionary_id'];
		$dictionary_m = $_POST['no_of_months_dictionary'];
		$selftudy_m = $_POST['no_of_months_self_study'];
		$num_codes = $_POST['num-of-codes'];
		$sub_type = $_POST['subscription-type'];
		$sat_m = $_POST['no_of_months_sat'];
		$sat_class = $_POST['sat-class'];
		$has_err = false;

		if($num_students == '') {
			ik_enqueue_messages('Please select Number of students/licenses.', 'error');
			$has_err = true;
		}

		if($sub_type == SUB_TEACHER_TOOL) {
			$dictionary_m = 0;
			if($teacher_m == '') {
				ik_enqueue_messages('Please select Number of months for teacher\'s subscription.', 'error');
				$has_err = true;
			}
		}
		else if($sub_type == SUB_DICTIONARY) {
			$teacher_m = 0;
			if($dictionary_m == '') {
				ik_enqueue_messages('Please select Number of months for Dictionary subscription.', 'error');
				$has_err = true;
			}
		}else if($sub_type == SUB_SELF_STUDY) {
			$teacher_m = 0;
			if($selftudy_m == '') {
				ik_enqueue_messages('Please select Number of months for Self-Study subscription.', 'error');
				$has_err = true;
			}else {
				$dictionary_m = $selftudy_m;
			}
		}

		if($sub_type == 3) {
			if($sat_m == '') {
				ik_enqueue_messages('Please select Number of months for SAT subscription.', 'error');
				$has_err = true;
			}

			if($sat_class == '') {
				ik_enqueue_messages('Please select SAT class.', 'error');
				$has_err = true;
			}
		}
		else {
			if($dictionary_id == '') {
				ik_enqueue_messages('Please select Dictionary.', 'error');
				$has_err = true;
			}
		}

		if(!is_numeric($num_codes)) {
			$num_codes = 1;
		}

		if(!$has_err) {
			for($i = 1; $i <= $num_codes; $i++)
			{
				$codes[] = generate_credit_code($sub_type, $teacher_m, $num_students, $dictionary_id, $dictionary_m, false, $sat_m, $sat_class);
			}

			if(!empty($codes)) {
				if($_POST['output-type'] == 2) {
					// view codes as text
					$_SESSION['generated_codes'] = $codes;
				}
				else {
					// send codes as csv file
					if($teacher_m != 0) {
						//$filename = 'Teachers_Tool_Credit_Codes_' . date('mdY_Hms', time()) . '.csv';
						$filename = 'Teachers_Tool_Credit_Codes_' . date('mdY_Hms', time()) . '.txt';
					}
					else if ($sub_type == SUB_SELF_STUDY) {
						$filename = 'Self-study_Credit_Codes_' . date('mdY_Hms', time()) . '.txt';
					}else {
						$filename = 'Dictionary_Credit_Codes_' . date('mdY_Hms', time()) . '.txt';
					}

					header( 'Content-Type: text/plain' );
					header( 'Content-Disposition: attachment;filename=' . $filename);
					$fp = fopen('php://output', 'w');
					foreach($codes as $code) {
						//fputcsv($fp, array($code));
						fputs($fp, (string)$code. "\r\n");
					}
					fclose($fp);
					exit;
				}

				ik_enqueue_messages(count($codes) . ' codes generated.', 'success');
			}

			wp_redirect( home_url() . '/?r=create-credit-codes' );
			exit;
		}
	}

	$sn = $wpdb->get_row('SELECT sn FROM ' . $wpdb->prefix . 'dict_credit_code_serial');
	$starting_num = $sn->sn + 1;
?>
<?php get_dict_header('Admin Create Credit Codes') ?>
<?php get_dict_page_title('Admin Create Credit Codes', 'admin-page') ?>

	<form method="post" action="<?php echo home_url() ?>/?r=create-credit-codes">
		<div class="row">
			<div class="col-sm-6">
				<div class="form-group">
					<div class="radio radio-style1">
						<input id="teacher-sub-type" type="radio" name="subscription-type" value="1"<?php echo $sub_type == '1' || empty($sub_type) ? ' checked' : '' ?>>
						<label for="teacher-sub-type" style="font-weight: bold">Teacher's Tool</label>
					</div>
				</div>
			</div>
			<div class="col-sm-6">
				<div class="form-group">
					<div class="radio radio-style1">
						<input id="dictioanry-sub-type" type="radio" name="subscription-type" value="2"<?php echo $sub_type == '2' ? ' checked' : '' ?>>
						<label for="dictioanry-sub-type" style="font-weight: bold">Dictionary</label>
					</div>
				</div>
			</div>
			<div class="col-sm-6">											
				<div class="row">
					<div class="col-xs-12">
						<div class="form-group">
							<label>Number of months, teacher's subscription</label>
							<?php MWHtml::select_num_of_months_teacher_subscription($teacher_m, 'no_of_months_teacher_tool') ?>
						</div>
					</div>
					<div class="col-xs-12">
						<div class="form-group">
							<label>Number of students/licenses allowed in this subscription</label>
							<select class="select-box-it form-control" name="no_of_students" id="no-of-students">
								<?php for($i = 0; $i <= 9; $i++) : ?>
									<option value="<?php echo trans_num_student_digit($i) ?>"><?php echo trans_num_student_digit($i) ?> students/licenses</option>
								<?php endfor ?>
							</select>
							<?php // MWHtml::select_num_of_students_subscription($num_students, 'no_of_students') ?>
						</div>					
					</div>
				</div>
			</div>
			<div class="col-sm-6">											
				<div class="row">
					<div class="col-xs-12">
						<div class="form-group">
							<label>Select the type of dictionary</label>
							<?php MWHtml::select_dictionaries($dictionary_id, true, 'dictionary_id', 'sel-dictionary', '', true) ?>
						</div>
					</div>
					<div class="col-xs-12">
						<div class="form-group">
							<label>Number of months dictionary subscription</label>
							<?php MWHtml::select_num_of_months_dict_subscription($dictionary_m, 'no_of_months_dictionary') ?>
						</div>					
					</div>
				</div>											
			</div>
		</div>

		<div class="row">
			<div class="col-sm-6">
				<div class="row">
					<div class="col-sm-12">
						<div class="form-group">
							<div class="radio radio-style1">
								<input id="sat-sub-type" type="radio" name="subscription-type" value="3"<?php echo $sub_type == '3' ? ' checked' : '' ?>>
								<label for="sat-sub-type" style="font-weight: bold">SAT Preparation</label>
							</div>
						</div>
					</div>
					<div class="col-sm-12">
						<div class="form-group">
							<label>Number of months</label>
							<?php MWHtml::select_num_of_months_teacher_subscription($sat_months, 'no_of_months_sat', 'no_of_months_sat') ?>
						</div>
					</div>
					<div class="col-sm-12">
						<div class="form-group">
							<label>Select SAT class</label>
							<select class="select-box-it" name="sat-class" id="sat-class">
								<option value="">Select one</option>
								<?php foreach($sat_class_types as $item) : ?>
									<option value="<?php echo $item->id ?>"><?php echo $item->name ?></option>
								<?php endforeach ?>
							</select>
						</div>
					</div>
				</div>
			</div>
			<div class="col-sm-6">
				<div class="row">
					<div class="col-sm-12">
						<div class="form-group">
							<div class="radio radio-style1">
								<input id="self-study-sub-type" type="radio" name="subscription-type" value="5"<?php echo $sub_type == '5' ? ' checked' : '' ?>>
								<label for="self-study-sub-type" style="font-weight: bold">Self-Study</label>
							</div>
						</div>
					</div>
					<div class="col-xs-12">
						<div class="form-group">
							<?php MWHtml::select_num_of_months_dict_subscription($dictionary_m, 'no_of_months_self_study', 'no-of-months-self-study') ?>
						</div>					
					</div>
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-sm-6">
				<div class="form-group">
					<label for="starting-num">Starting number</label>
					<input type="text" class="form-control" id="starting-num" name="starting-num" value="<?php echo $starting_num ?>" readonly>
				</div>
			</div>
			<div class="col-sm-6">
				<div class="form-group">
					<label for="num-of-codes">How many codes?</label>
					<input type="number" class="form-control" id="num-of-codes" name="num-of-codes" min="1" max="2000" value="1">
				</div>					
			</div>
			<div class="col-sm-3">
				<div class="form-group">
					<label>Output</label>
					<div class="radio radio-style1">
						<input id="file-output" type="radio" name="output-type" value="1" checked>
						<label for="file-output">Save to a file</label>
					</div>
				</div>
			</div>
			<div class="col-sm-3">
				<div class="form-group">
					<label>&nbsp;</label>
					<div class="radio radio-style1">
						<input id="text-output" type="radio" name="output-type" value="2">
						<label for="text-output">View as text</label>
					</div>
				</div>
			</div>
			<div class="col-sm-6">
				<div class="form-group">
					<label>&nbsp;</label>
					<button type="submit" class="btn btn-default btn-block orange medium form-control" name="generate"><span class="icon-plus"></span>Generate</button>
				</div>
			</div>
		</div>
		<?php if(isset($_SESSION['generated_codes'])) :  ?>
			<div class="row">
				<div class="col-xs-12 box">
					<h2>Generated Credit Codes</h2>
					<?php foreach($_SESSION['generated_codes'] as $code) : ?>
						<div class="text-muted"><?php echo $code ?></div>
					<?php endforeach ?>
				</div>
			</div>
		<?php $_SESSION['generated_codes'] = null; endif ?>
	</form>

<script>
	(function($){
		$(function(){
			function change_sub_sel_state(){
				switch($("[name='subscription-type']:checked").val()){
					case "1": $("#sel-teacher-tool").data("selectBox-selectBoxIt").enable();
						$("#sel-dict-months").data("selectBox-selectBoxIt").disable();
						$("#no_of_months_sat").data("selectBox-selectBoxIt").disable();
						$("#sat-class").data("selectBox-selectBoxIt").disable();
						$("#no-of-months-self-study").data("selectBox-selectBoxIt").disable();
						$("#no-of-students").data("selectBox-selectBoxIt").enable();
						break;
					case "2": $("#sel-dict-months").data("selectBox-selectBoxIt").enable();
						$("#sel-teacher-tool").data("selectBox-selectBoxIt").disable();
						$("#no_of_months_sat").data("selectBox-selectBoxIt").disable();
						$("#sat-class").data("selectBox-selectBoxIt").disable();
						$("#no-of-months-self-study").data("selectBox-selectBoxIt").disable();
						$("#no-of-students").data("selectBox-selectBoxIt").enable();
						break;
					case "3": $("#no_of_months_sat").data("selectBox-selectBoxIt").enable();
						$("#sat-class").data("selectBox-selectBoxIt").enable();
						$("#sel-dict-months").data("selectBox-selectBoxIt").disable();
						$("#sel-teacher-tool").data("selectBox-selectBoxIt").disable();
						$("#no-of-months-self-study").data("selectBox-selectBoxIt").disable();
						$("#no-of-students").data("selectBox-selectBoxIt").enable();
						break;
					case "5" :  $("#no-of-months-self-study").data("selectBox-selectBoxIt").enable();
						$("#sel-dict-months").data("selectBox-selectBoxIt").disable();
						$("#sel-teacher-tool").data("selectBox-selectBoxIt").disable();
						$("#no_of_months_sat").data("selectBox-selectBoxIt").disable();
						$("#sat-class").data("selectBox-selectBoxIt").disable();
						$("#no-of-students").data("selectBox-selectBoxIt").disable();
						break;
				}
			}

			change_sub_sel_state();
			$("[name='subscription-type']").change(function(){
				change_sub_sel_state();
			});
		});
	})(jQuery);
</script>
<?php get_dict_footer() ?>