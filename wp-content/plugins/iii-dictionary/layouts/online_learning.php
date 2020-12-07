<?php
	global $wpdb;

	$task = isset($_POST['task']) ? $_POST['task'] : '';
	$gid = empty($_GET['gid']) ? 0 : $_GET['gid'];
	$gname = '';
	$current_user_id = get_current_user_id();
	$uref = rawurlencode(base64_encode(home_url() . $_SERVER['REQUEST_URI']));
	$is_math_panel = is_math_panel();
	$_page_title = __('Online Learning', 'iii-dictionary');
	$_averge = 0;

	// user want to join group
	if(isset($_POST['join']))
	{
		$gname = esc_html($_POST['gname']);
		$gpass = esc_html($_POST['gpass']);

		if(MWDB::join_group($gname, $gpass)) {
			wp_redirect(locale_home_url() . '/?r=online-learning');
			exit;
		}
	}

	// user want to leave group
	if(isset($_POST['leave']))
	{
		$result = MWDB::leave_group($_POST['gid']);

		if($result) {
			ik_enqueue_messages(__('Successfully left group.', 'iii-dictionary'), 'success');

			// updating subscription status
			update_user_subscription();

			wp_redirect(locale_home_url() . '/?r=homework-status');
			exit;
		}
		else {
			ik_enqueue_messages(__('Cannot leave Group.', 'iii-dictionary'), 'error');
		}
	}

	// page content
	$current_page = max( 1, get_query_var('page'));
	$filter['items_per_page'] = 20;
	$filter['offset'] = $filter['items_per_page'] * ($current_page - 1);

	if(!$gid)
	{
		$filter['offset'] = 0;
		$filter['items_per_page'] = 99999999;
		$user_groups = MWDB::get_user_joined_groups($current_user_id, $filter['offset'], $filter['items_per_page'], true);
		$total_pages = ceil($user_groups->total / $filter['items_per_page']);
	}
	else
	{
		// user want to re do the homework
		if(isset($_POST['retry'])) {
			if(MWDB::delete_homework_result($_POST['rid'])) {
				$url = MWHtml::get_practice_page_url($_POST['aid']) . '&mode=homework&sid=' . $_POST['sid'] . '&ref=' . $uref;
				wp_redirect($url);
				exit;
			}
		}

		// user want to request grading from teacher
		if(isset($_POST['request-grading'])) {
			$hrid = $_POST['hrid']; // homework result id
			$hid = $_POST['hid']; // homework id

			// request grading
			if(ik_request_worksheet_grading($hrid, $hid, $current_user_id)) {
				wp_redirect(locale_home_url() . '/?r=homework-status&gid=' . $gid);
				exit;
			}
		}

		$filter['homework_result'] = true;
		$filter['user_id'] = get_current_user_id();
		$filter['is_active'] = 1;
		$group = MWDB::get_group($gid, 'id');
		$homeworks = MWDB::get_group_homeworks($gid, $filter, $filter['offset'], $filter['items_per_page']);
		$total_pages = ceil($homeworks->total / $filter['items_per_page']);
		//calculate the average score
		if(!empty($homeworks->items)) {
			$_averge =  average_test_homework($homeworks->items);
		}
	}
	
	$pagination = paginate_links(array(
		'format' => '?page=%#%',
		'current' =>  $current_page,
		'total' => $total_pages
	));
	

?>
<?php if(!$is_math_panel) : ?>
	<?php get_dict_header($_page_title) ?>
<?php else : ?>
	<?php get_math_header($_page_title, 'red-brown') ?>
<?php endif ?>
<?php get_dict_page_title($_page_title, '', '', array(), get_info_tab_cloud_url('Popup_info_11.jpg')) ?>

	<form method="post" action="<?php echo locale_home_url()?>/?r=homework-status<?php echo $gid ? '&amp;gid=' . $gid : '' ?>" id="main-form" enctype="multipart/form-data">
		<div class="row">

		<?php if(!$gid) : ?>

			<div class="col-sm-12">
				<h2 class="title-border"><?php _e('Finish homework first?', 'iii-dictionary') ?></h2>
			</div>
			<div class="col-sm-4">
				<div class="form-group">
					<label for="gname"><?php _e('Group name', 'iii-dictionary') ?></label>
					<input type="text" class="form-control" id="gname" name="gname" value="<?php echo $gname ?>">
				</div>
			</div>
			<div class="col-sm-4">
				<div class="form-group">
					<label for="gpass"><?php _e('Group password', 'iii-dictionary') ?></label>
					<input type="password" class="form-control" id="gpass" name="gpass" value="">
				</div>					
			</div>
			<div class="col-sm-4">
				<div class="form-group">
					<label>&nbsp;</label>
					<button type="button" class="btn btn-default btn-block orange form-control" id="join-group"><span class="icon-check"></span><?php _e('Join', 'iii-dictionary') ?></button>
				</div>
			</div>
			<div class="col-sm-12">
				<label><?php _e('Follow the steps to Study online', 'iii-dictionary') ?></label>
			</div>
			<div class="col-sm-12">
				<div class="step-block">
					<p><a role="button" style="color:#fff" data-toggle="collapse" href="#step1-collapse" class="step-number" title="" ><?php _e('Check Homework from your teachers','iii-dictionary'); ?></a></p>
					<div id="step1-collapse" class="collapse">
						<div class="box box-sapphire">
						<div class="row">
							<div class="col-sm-12">
								<div class="scroll-list2" style="max-height: 600px">
									<table class="table table-striped table-condensed ik-table1 ik-table-break-all text-center">
										<thead>
											<tr>
												<th><?php _e('Group name', 'iii-dictionary') ?></th>
												<th><?php _e('Teacher', 'iii-dictionary') ?></th>
												<th class="hidden-xs"><?php _e('No. of Homework', 'iii-dictionary') ?></th>
												<th class="hidden-xs"><?php _e('No. of Completed', 'iii-dictionary') ?></th>
												<th></th>
												<th></th>
											</tr>
										</thead>
										<tfoot>
											<tr><td colspan="6"><?php echo $pagination ?></td></tr>
										</tfoot>
										<tbody>
											<?php if(empty($user_groups->items)) : ?>
												<tr>
													<td colspan="6"><?php _e('You haven\'t joined any groups yet.', 'iii-dictionary') ?></td>
												</tr>
											<?php else : ?>
												<?php foreach($user_groups->items as $item) : ?>
													<tr>
														<td><?php echo $item->group_name ?></td>
														<td><?php echo $item->group_type_id == GROUP_CLASS ? 'SAT Prep.' : $item->teacher ?></td>
														<td class="hidden-xs"><?php echo $item->no_of_homework ?></td>
														<td><?php echo $item->completed_homework ?></td>
														<td>
															<a href="<?php echo locale_home_url() . '/?r=online-learning&amp;gid=' . $item->group_id ?>" class="btn btn-default btn-block btn-tiny grey"><?php _e('Homeworks', 'iii-dictionary') ?></a>
														</td>
														<td><?php
															if(!$item->is_default) : ?>
																<button type="button" class="btn btn-default btn-block btn-tiny grey leave-grp-btn" data-gid="<?php echo $item->group_id ?>" data-gname="<?php echo $item->group_name ?>"><?php _e('Leave Group', 'iii-dictionary') ?></button>
															<?php endif ?>
														</td>
													</tr>
													<?php endforeach ?>
											<?php endif ?>
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
					</div>
					<p><a role="button" style="color:#fff" data-toggle="collapse" href="#step2-collapse" class="step-number" title="" ><?php _e('Start Essay Writing Practice','iii-dictionary'); ?></a></p>
					<div id="step2-collapse" class="collapse" >
						<h3 style="margin: 0 0 10px 0;"><?php _e('Writing Practice','iii-dictionary'); ?></h3>
						<div class="row" style="margin-bottom:10px">
							<div class="col-md-4 col-sm-4 col-xs-12">
								<button type="button" class="btn btn-default btn-block orange form-control"><span class="icon-check"> </span><?php _e('Request Tutoring','iii-dictionary'); ?></button>
							</div>
						</div>
						<p style="margin: 0 0 10px 0;"><label><?php _e('Request a teacher to edit and improve your writing.','iii-dictionary'); ?>..</label></p>
					</div>
					<p><a role="button" style="color:#fff" data-toggle="collapse" href="#step3-collapse" class="step-number" title="" ><?php _e('Start Math Tutoring','iii-dictionary'); ?></a></p>
					<div id="step3-collapse" class="collapse">
						<h3 style="margin: 0 0 10px 0;"><?php _e('Math Tutoring','iii-dictionary'); ?></h3>
						<div class="row" style="margin-bottom:10px">
							<div class="col-md-4 col-sm-4 col-xs-12">
								<button type="button" class="btn btn-default btn-block orange form-control"><span class="icon-check"> </span><?php _e('Request Tutoring','iii-dictionary'); ?></button>
							</div>
						</div>
						<p style="margin: 0 0 10px 0;"><label><?php _e('Request a tutor to help you with math problems, including your math homework.','iii-dictionary'); ?>..</label></p>
					</div>
				</div>
			</div>

<div id="join-group-dialog" class="modal fade modal-red-brown" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
            <a href="#" data-dismiss="modal" aria-hidden="true" class="close"></a>
             <h3><?php _e('Are you sure you are joining to this group?', 'iii-dictionary') ?></h3>
        </div>
        <div class="modal-body">
			<p><?php _e('Are you sure you are joining to this group?', 'iii-dictionary') ?></p>
			<p><?php _e('If this group name is provided by your teacher, your name will show up in his/her class member list and it will cost the teacher for class membership. If this is a private group, it is free.', 'iii-dictionary') ?></p>
			<hr>
			<h4 class="text-warning2"><?php _e('Do you want to join group chat (chat board for this group)?', 'iii-dictionary') ?></h4>
			<div class="row">
				<div class="col-xs-3 col-xs-offset-3">
					<div class="radio radio-style1">															
						<input id="rdo-yes" type="radio" name="joinchat" value="1" checked>
						<label for="rdo-yes"><?php _e('Yes', 'iii-dictionary') ?></label>
					</div>
				</div>
				<div class="col-xs-3">
					<div class="radio radio-style1">
						<input id="rdo-no" type="radio" name="joinchat" value="0">  
						<label for="rdo-no"><?php _e('No', 'iii-dictionary') ?></label>
					</div>
				</div>
			</div>
        </div>
        <div class="modal-footer">			
			<div class="row">
				<div class="col-sm-6">
					<button type="submit" name="join" class="btn btn-block orange confirm"><span class="icon-accept"></span><?php _e('Yes, Join', 'iii-dictionary') ?></button>
				</div>
				<div class="col-sm-6">
					<a href="#" data-dismiss="modal" aria-hidden="true" class="btn btn-block grey secondary"><span class="icon-cancel"></span><?php _e('Cancel', 'iii-dictionary') ?></a>
				</div>
			</div>
        </div>
      </div>
    </div>
</div>

<div id="leave-group-dialog" class="modal fade modal-red-brown" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
            <a href="#" data-dismiss="modal" aria-hidden="true" class="close"></a>
             <h3><?php _e('Leave Group', 'iii-dictionary') ?></h3>
        </div>
        <div class="modal-body">
			<p><?php printf(__('Do you want to leave Group: %s', 'iii-dictionary'), '<strong id="lev-group-name"></strong>') ?></p>
        </div>
        <div class="modal-footer">
			<div class="row">
				<div class="col-sm-6">
					<button name="leave" class="btn btn-block orange"><span class="icon-check"></span><?php _e('OK', 'iii-dictionary') ?></button>
				</div>
				<div class="col-sm-6">
					<a href="#" data-dismiss="modal" class="btn btn-block grey"><span class="icon-cancel"></span><?php _e('Cancel', 'iii-dictionary') ?></a>
				</div>
			</div>
        </div>
      </div>
    </div>
</div>

		<?php else : ?>

			<div class="col-sm-12">
				<h2 class="title-border"><?php _e('List of Homeworks', 'iii-dictionary') ?></h2>
			</div>
			<div class="col-sm-12">
				<div class="box">
					<div class="row box-header">
						<div class="col-sm-4">
							<h4><?php _e('Teacher:', 'iii-dictionary') ?> <span id="t-name" style="color: #fff"><?php echo $group->display_name ?></span></h4>
						</div>
						<div class="col-sm-8">
							<h4><?php _e('Group Name:', 'iii-dictionary') ?> <span id="g-name" style="color: #fff"><?php echo $group->name ?></span></h4>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-12">
							<table class="table table-striped table-condensed ik-table1 text-center vertical-middle">
								<thead>
									<tr>
										<th><?php _e('Homework Name', 'iii-dictionary') ?></th>
										<th><?php _e('Due date', 'iii-dictionary') ?></th>
										<th class="hidden-xs"><?php _e('Status', 'iii-dictionary') ?></th>
										<th><?php _e('Result', 'iii-dictionary') ?></th>
										<th><?php _e('Request Grading', 'iii-dictionary') ?></th>
										<th></th>
									</tr>
								</thead>
								<tfoot>
									<tr><td colspan="6"><?php echo $pagination ?></td></tr>
								</tfoot>
								<tbody>
									<?php if(!empty($homeworks->items)) : foreach($homeworks->items as $hw) : ?>
										<tr>
											<td><?php echo empty($hw->homework_name) ? $hw->sheet_name : $hw->homework_name ?></td>
											<td><?php echo $hw->deadline == '0000-00-00' ? 'No deadline' : ik_date_format($hw->deadline) ?></td>
											<?php if(is_null($hw->finished)) {
													$txt = __('New', 'iii-dictionary');
													$td_class = ' text-primary';
												} 
												else if(!$hw->finished) {
													$txt = __('Unfinished', 'iii-dictionary');
													$td_class = ' text-warning2';
												}
												else {
													if($hw->deadline != '0000-00-00' && $hw->submitted_on > $hw->deadline) {
														$txt = __('Over Due', 'iii-dictionary');
														$td_class = ' text-danger';
													}
													else {
														$txt = __('Finished', 'iii-dictionary');
														$td_class = ' text-success';
													}
												} ?>
											<td class="hidden-xs"><strong class="<?php echo $td_class ?>"><?php echo $txt ?></strong></td>
											<td>
												<?php if(!is_null($hw->attempted_on) && $hw->assignment_id != ASSIGNMENT_REPORT) : ?>
													<a href="<?php echo locale_home_url() . '/?r=homework-result&amp;hid=' . $hw->hid . '&amp;sid=' . $current_user_id ?>" class="btn btn-default btn-tiny grey"><?php _e('View', 'iii-dictionary') ?></a>
												<?php else : ?>
													<?php echo $hw->score ?> %
												<?php endif ?>
											</td>
											<td><?php
													if($hw->homework_type_id == HOMEWORK_CLASS && $hw->finished) :
														$can_retry = $hw->is_retryable;
														if(ik_validate_date($hw->finished_on)) {
															// homework is graded
															$btn_text = __('Graded', 'iii-dictionary');
															$btn_disabled = ' disabled';
														} else if (ik_validate_date($hw->accepted_on)) {
															// grading request is accepted by a teacher
															$btn_text = __('Accepted', 'iii-dictionary');
															$btn_disabled = ' disabled';
															$can_retry = false;
														} else if (ik_validate_date($hw->requested_on)) {
															// grading request is requested
															$btn_text = __('Requested', 'iii-dictionary');
															$btn_disabled = ' disabled';
															$can_retry = false;
														} else if($hw->assignment_id == ASSIGNMENT_WRITING) {
															// request a grading if homework is writing
															$btn_text = __('Request', 'iii-dictionary');
															$btn_disabled = '';
														} else {
															// auto-graded homework
															$btn_text = __('Graded', 'iii-dictionary');
															$btn_disabled = ' disabled';
														} ?>
														<button type="button" data-hid="<?php echo $hw->hid ?>" data-hrid="<?php echo $hw->homework_result_id ?>" data-cost="<?php echo $hw->grading_price ?>" class="btn btn-default btn-block btn-tiny grey request-grading"<?php echo $btn_disabled ?>><?php echo $btn_text ?></button>
												<?php else : ?>
													<?php if(!is_null($hw->finished) && in_array($hw->assignment_id, array(ASSIGNMENT_WRITING,ASSIGNMENT_REPORT)) === true) :
															if(!$hw->graded) :
																_e('Waiting for grading', 'iii-dictionary');
															else :
																_e('Graded', 'iii-dictionary');
															endif;
														endif ?>
												<?php endif
											?></td>
											<td>
												<?php if(!$hw->finished || is_null($hw->finished)) :
													$practice_url = MWHtml::get_practice_page_url($hw->assignment_id) . '&amp;hid=' . $hw->hid;
													$homework_url = MWHtml::get_practice_page_url($hw->assignment_id) . '&amp;mode=homework&amp;hid=' . $hw->hid;
														if($hw->assignment_id != ASSIGNMENT_REPORT) : ?>
															<a href="#" data-practice-url="<?php echo $practice_url ?>" data-homework-url="<?php echo $homework_url ?>" data-for-practice="<?php echo $hw->for_practice ?>" data-startnew="<?php echo is_null($hw->finished) ? 1 : 0 ?>" class="btn btn-default btn-block btn-tiny orange goto-homework"><?php _e('Do homework', 'iii-dictionary') ?></a>
														<?php else :
															$rp_url = $hw->for_practice ? $practice_url : $homework_url ?>
															<a href="<?php echo $rp_url ?>" class="btn btn-default btn-block btn-tiny orange"><?php _e('Do homework', 'iii-dictionary') ?></a>
														<?php endif ?>
												<?php else : if($hw->homework_type_id == HOMEWORK_CLASS && $can_retry) : ?>
													<button type="submit" name="retry" class="btn btn-default btn-block btn-tiny orange retry-homework"><?php _e('Retry homework', 'iii-dictionary') ?></button>
													<input type="hidden" name="rid" value="<?php echo $hw->id ?>">
													<input type="hidden" name="sid" value="<?php echo $hw->sheet_id ?>">
													<input type="hidden" name="aid" value="<?php echo $hw->assignment_id ?>">
												<?php endif; endif ?>
											</td>
										</tr>
									<?php endforeach; else : ?>
										<tr>
											<td colspan="7"><?php _e('No homework assigned to this Group yet', 'iii-dictionary') ?></td>
										</tr>
									<?php endif ?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
			<div class="col-sm-4 col-sm-offset-4 omg_average" >
				<?php printf(__('Average : %d %s', 'iii-dictionary'), $_averge, '%') ?>
			</div>
			<div class="col-sm-4 " style="margin-top: 20px">
				<div class="form-group">
					<a href="<?php echo locale_home_url() ?>/?r=online-learning" class="btn btn-default grey form-control"><span class="icon-goback"></span><?php _e('Go back', 'iii-dictionary') ?></a>
				</div>
			</div>
			<input type="hidden" id="uref" value="<?php echo $uref ?>">

		<?php
			ik_enqueue_js_messages('test_inst', __('This is the Test assigned by your teacher. The score will be displayed at Homework Status panel.', 'iii-dictionary'));
			ik_enqueue_js_messages('practice_inst', __('This is Practice Worksheet sent by your teacher', 'iii-dictionary'));
			ik_enqueue_js_messages('unfinished_homework', __('You have more than 2 unfinished homeworks. Please complete it before starting another one.', 'iii-dictionary'));
			ik_enqueue_js_messages('point_err', sprintf(__('Your current points is <strong>%d</strong> pts. You don\'t have enough points to request grading for this homework', 'iii-dictionary'), ik_get_user_points($current_user_id)));
		?>

<div id="request-grading-dialog" class="modal fade modal-red-brown" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
            <a href="#" data-dismiss="modal" aria-hidden="true" class="close"></a>
             <h3><?php _e('Request Grading', 'iii-dictionary') ?></h3>
        </div>
        <div class="modal-body">
			<?php printf(__('This grading and editing your writing costs %s points.', 'iii-dictionary'), '<strong id="grading-cost"></strong>') ?>
			<a href="<?php echo locale_home_url() ?>/?r=manage-subscription"><?php _e('Need Points?', 'iii-dictionary') ?></a>
			<p class="text-danger" id="request-grading-err"></p>
        </div>
        <div class="modal-footer">
			<div class="row">
				<div class="col-sm-6">
					<button name="request-grading" class="btn btn-block orange"><span class="icon-check"></span><?php _e('OK', 'iii-dictionary') ?></button>
				</div>
				<div class="col-sm-6">
					<a href="#" data-dismiss="modal" class="btn btn-block grey"><span class="icon-cancel"></span><?php _e('Cancel', 'iii-dictionary') ?></a>
				</div>
			</div>
        </div>
		<input type="hidden" name="hrid" id="hrid">
		<input type="hidden" name="hid" id="hid">
      </div>
    </div>
</div>

		<?php endif ?>

		</div>
		<input type="hidden" name="task" id="task" value="">
		<input type="hidden" name="gid" id="gid" value="">
		<input type="hidden" id="unfinished_homework" value="<?php echo is_homework_unfinished() ? 1 : 0 ?>">
	</form>

<div id="switch-mode-dialog" class="modal fade modal-red-brown" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
            <a href="#" data-dismiss="modal" aria-hidden="true" class="close"></a>
             <h3><?php _e('Starting Homework', 'iii-dictionary') ?></h3>
        </div>
        <div class="modal-body"></div>
        <div class="modal-footer">			
			<div class="row">
				<div class="col-sm-6 col-sm-offset-6">
					<a href="#" id="btn-practice" class="btn btn-block orange"><span class="icon-accept"></span><?php _e('OK', 'iii-dictionary') ?></a>
				</div>
			</div>
        </div>
      </div>
    </div>
</div>

<script>
	var ypoints = <?php echo ik_get_user_points($current_user_id) ?>;
</script>
<?php if(!$is_math_panel) : ?>
	<?php get_dict_footer() ?>
<?php else : ?>
	<?php get_math_footer() ?>
<?php endif ?>