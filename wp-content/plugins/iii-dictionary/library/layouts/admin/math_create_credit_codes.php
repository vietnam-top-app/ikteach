<?php 
	$sat_class_types = $sat_class_types = MWDB::get_group_class_types(false, 2);
	if(isset($_POST['generate'])) {
		
		$teacher_m = $_POST['math_no_of_months_teacher_tool'];
		$num_students = $_POST['math-subscription-type'] != (SUB_SELF_STUDY_MATH || SUB_POINTS_PURCHASE) ? $_POST['math_no_of_students'] : 1;
		$dictionary_m = $_POST['math_no_of_months_dictionary'];
		$num_codes = $_POST['math-num-of-codes'];
		$sub_type = $_POST['math-subscription-type'];
		$sat_m = $_POST['math_no_of_months_sat'];
		$sat_class = $_POST['math-sat-class'];
		$dictionary_id = NO_DICTIONARY_ID;
		$num_points 		= is_null($_POST['num_points']) ? 0 : $_POST['num_points'];
		$has_err = false;
		if($num_students == '') {
			ik_enqueue_messages('Please select Number of students/licenses.', 'error');
			$has_err = true;
		}

		if($sub_type == SUB_TEACHER_TOOL_MATH) {
			$dictionary_m = 0;
			if($teacher_m == '') {
				ik_enqueue_messages('Please select Number of months for teacher\'s subscription.', 'error');
				$has_err = true;
			}
		}
		else if($sub_type == SUB_SELF_STUDY_MATH) {
			$teacher_m = 0;
			if($dictionary_m == '') {
				ik_enqueue_messages('Please select Number of months for Self-study subscription.', 'error');
				$has_err = true;
			}
		}
		
		if($sub_type == SUB_MATH_SAT_I_PREP) {
			if($sat_m == '') {
				ik_enqueue_messages('Please select Number of months for SAT subscription.', 'error');
				$has_err = true;
			}

			if($sat_class == '') {
				ik_enqueue_messages('Please select SAT class.', 'error');
				$has_err = true;
			}
		}
		
		if(!is_numeric($num_codes)) {
			$num_codes = 1;
		}
		
		if(!$has_err) {
			for($i = 1; $i <= $num_codes; $i++)
			{
				$codes[] = generate_credit_code($sub_type, $teacher_m, $num_students, $dictionary_id, $dictionary_m, false, $sat_m, $sat_class, $num_points,  true);
			}

			if(!empty($codes)) {
				if($_POST['output-type'] == 2) {
					// view codes as text
					$_SESSION['math_generated_codes'] = $codes;
				}
				else {
					// send codes as txt file
					if($teacher_m != 0) {
						$filename = 'Teachers_Tool_Credit_Codes_' . date('mdY_Hms', time()) . '.txt';
					}
					else {
						$filename = 'Self-study_Credit_Codes_' . date('mdY_Hms', time()) . '.txt';
					}

					header( 'Content-Type: text/plain' );
					header( 'Content-Disposition: attachment;filename=' . $filename);
					$fp = fopen('php://output', 'w');
					foreach($codes as $code) {
						fputs($fp, (string)$code. "\r\n");
					}
					fclose($fp);
					exit;
				}

				ik_enqueue_messages(count($codes) . ' codes generated.', 'success');
			}

			wp_redirect( home_url() . '/?r=math-create-credit-codes' );
			exit;
		}
	}
	$sn = $wpdb->get_row('SELECT snm FROM ' . $wpdb->prefix . 'dict_credit_code_serial');
	$starting_num = $sn->snm + 1;
?>
<?php get_dict_header('Admin Create Credit Codes') ?>
<?php get_dict_page_title('Admin Create Credit Codes', 'admin-page', 'MATH') ?>
<form method="post" action="<?php echo home_url() ?>/?r=math-create-credit-codes">
	<div class="row">
		<div class="col-sm-6">
			<div class="form-group">
				<div class="radio radio-style1">
					<input id="math-teacher-sub-type" type="radio" name="math-subscription-type" value="6" <?php echo $sub_type == '6' || empty($sub_type) ? ' checked' : '' ?>>
					<label for="math-teacher-sub-type" style="font-weight: bold">Teacher's Tool</label>
				</div>
			</div>
		</div>
		<div class="col-sm-6">
			<div class="form-group">
				<div class="radio radio-style1">
					<input id="math-self-study" type="radio" name="math-subscription-type" value="9" <?php echo $sub_type == '9' ? ' checked' : '' ?>>
					<label for="math-self-study" style="font-weight: bold">Self-study</label>
				</div>
			</div>
		</div>
		<div class="col-sm-6">											
			<div class="row">
				<div class="col-xs-12">
					<div class="form-group">
						<label>Number of months, teacher's subscription</label>
						<?php MWHtml::select_num_of_months_teacher_subscription($teacher_m, 'math_no_of_months_teacher_tool', 'math-sel-teacher-tool') ?>
					</div>
				</div>
				<div class="col-xs-12">
					<div class="form-group">
						<label>Number of students/licenses allowed in this subscription</label>
						<select id="math-no-of-students" class="select-box-it form-control" name="math_no_of_students">
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
						<label>Number of months dictionary subscription</label>
						<?php MWHtml::select_num_of_months_dict_subscription($dictionary_m, 'math_no_of_months_dictionary', 'math-sel-dict-months') ?>
					</div>					
				</div>
			</div>											
		</div>
	</div>
	
	<div class="row">
		<div class="col-sm-6">
			<div class="form-group">
				<div class="radio radio-style1">
					<input id="math-sat-sub-type" type="radio" name="math-subscription-type" value="7" <?php echo $sub_type == '7' ? ' checked' : '' ?>>
					<label for="math-sat-sub-type" style="font-weight: bold">SAT Preparation</label>
				</div>
			</div>
		</div>
		<!--point type-->
		<div class="col-sm-6">
			<div class="form-group">
				<div class="radio radio-style1">
					<input id="point-type" name="math-subscription-type" type="radio" name="point-type" value="4" <?php echo $sub_type == '4' ? ' checked' : '' ?>>
					<label for="point-type" style="font-weight: bold">Points credit amount ($)</label>
				</div>
			</div>
		</div>
		<div class="col-sm-6">
			<div class="form-group">
				<label>Number of months</label>
				<?php MWHtml::select_num_of_months_teacher_subscription($sat_months, 'math_no_of_months_sat', 'math_no_of_months_sat') ?>
			</div>
		</div>
		<!--point type-->
		<div class="col-sm-6">
			<div class="form-group">
				<label for="num_points">&nbsp;</label>
				<input type="number" class="form-control" id="num_points" name="num_points" value="1">
			</div>					
		</div>
		<div class="col-sm-6">
			<div class="form-group">
				<label>Select SAT class</label>
				<select class="select-box-it" name="math-sat-class" id="math-sat-class">
					<option value="">Select one</option>
					<?php foreach($sat_class_types as $item) : ?>
						<option value="<?php echo $item->id ?>"><?php echo $item->name ?></option>
					<?php endforeach ?>
				</select>
			</div>
		</div>
		<div class="clearfix"></div>
	</div>
	
	<div class="row">
		<div class="col-sm-6">
			<div class="form-group">
				<label for="math-starting-num">Starting number</label>
				<input type="text" class="form-control" id="math-starting-num" name="math-starting-num" value="<?php echo $starting_num ?>" readonly>
			</div>
		</div>
		<div class="col-sm-6">
			<div class="form-group">
				<label for="math-num-of-codes">How many codes?</label>
				<input type="number" class="form-control" id="math-num-of-codes" name="math-num-of-codes" min="1" max="2000" value="1">
			</div>					
		</div>
		<div class="col-sm-3">
			<div class="form-group">
				<label>Output</label>
				<div class="radio radio-style1">
					<input id="math-file-output" type="radio" name="output-type" value="1" checked>
					<label for="math-file-output">Save to a file</label>
				</div>
			</div>
		</div>
		<div class="col-sm-3">
			<div class="form-group">
				<label>&nbsp;</label>
				<div class="radio radio-style1">
					<input id="math-text-output" type="radio" name="output-type" value="2">
					<label for="math-text-output">View as text</label>
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
	
	<?php if(isset($_SESSION['math_generated_codes'])) :  ?>
		<div class="row">
			<div class="col-xs-12 box">
				<h2>Generated Credit Codes</h2>
				<?php foreach($_SESSION['math_generated_codes'] as $code) : ?>
					<div class="text-muted"><?php echo $code ?></div>
				<?php endforeach ?>
			</div>
		</div>
	<?php $_SESSION['math_generated_codes'] = null; endif ?>
</form>

<script>
	(function($){
		$(function(){
			function change_sub_sel_state(){
				switch($("[name='math-subscription-type']:checked").val()){
					case "6": $("#math-sel-teacher-tool").data("selectBox-selectBoxIt").enable();
						$("#math-sel-dict-months").data("selectBox-selectBoxIt").disable();
						$("#math_no_of_months_sat").data("selectBox-selectBoxIt").disable();
						$("#math-sat-class").data("selectBox-selectBoxIt").disable();
						$("#math-no-of-students").data("selectBox-selectBoxIt").enable();
						$("#num_points").prop('disabled', true);
						break;
					case "9": $("#math-sel-dict-months").data("selectBox-selectBoxIt").enable();
						$("#math-sel-teacher-tool").data("selectBox-selectBoxIt").disable();
						$("#math_no_of_months_sat").data("selectBox-selectBoxIt").disable();
						$("#math-sat-class").data("selectBox-selectBoxIt").disable();
						$("#math-no-of-students").data("selectBox-selectBoxIt").disable();
						$("#num_points").prop('disabled', true);
						break;
					case "7" : 
						$("#math-sel-dict-months").data("selectBox-selectBoxIt").disable();
						$("#math-sel-teacher-tool").data("selectBox-selectBoxIt").disable();
						$("#math_no_of_months_sat").data("selectBox-selectBoxIt").enable();
						$("#math-sat-class").data("selectBox-selectBoxIt").enable();
						$("#math-no-of-students").data("selectBox-selectBoxIt").enable();
						$("#num_points").prop('disabled', true);
						break;
					case "4" :
						$("#math-sel-dict-months").data("selectBox-selectBoxIt").disable();
						$("#math-sel-teacher-tool").data("selectBox-selectBoxIt").disable();
						$("#math_no_of_months_sat").data("selectBox-selectBoxIt").disable();
						$("#math-sat-class").data("selectBox-selectBoxIt").disable();
						$("#math-no-of-students").data("selectBox-selectBoxIt").disable();
						$("#num_points").prop('disabled', false);
						break;
				}
			}
			change_sub_sel_state();
			$("[name='math-subscription-type']").change(function(){
				change_sub_sel_state();
			});
		});
	})(jQuery);
</script>

<?php get_dict_footer() ?>