<?php

//	$user = new stdClass;

	if(isset($_GET['cid']))
	{
		$user = get_user_by('id', $_GET['cid']);
	}

	$is_registered_teacher = is_mw_registered_teacher($_GET['cid']);
	$is_qualified_teacher = is_mw_qualified_teacher($_GET['cid']);

	if(isset($_POST['give-points']))
	{
		ik_gift_user_points($_POST['no-of-points'], $user->ID);

		ik_enqueue_messages($_POST['no-of-points'] . ' points was given to this user.', 'success');
		wp_redirect(home_url() . '/?r=view-user&cid=' . $user->ID);
		exit;
	}

	if(isset($_POST['change-role'])) {
		$role = $_POST['new-role'];

		if($role == 'mw_qualified_teacher') {
			$user->add_role($role);
		}
		else {
			$user->remove_role('mw_qualified_teacher');

			// delete test results of the user so that the user can take test again
			$group_id = mw_get_option('teacher-test-group');

			$homework_ids = $wpdb->get_col(
				'SELECT id FROM ' . $wpdb->prefix . 'dict_homeworks WHERE group_id = ' . $group_id
			);

			$wpdb->query(
				'DELETE FROM ' . $wpdb->prefix . 'dict_homework_results 
				WHERE userid = ' . $user->ID . ' AND homework_id IN (' . implode(',', $homework_ids) . ')'
			);
		}

		ik_enqueue_messages('Successfully change Teacher\'s role', 'success');
		wp_redirect(home_url() . '/?r=view-user&cid=' . $_GET['cid']);
		exit;
	}
?>
<?php get_dict_header('User Detail') ?>
<?php get_dict_page_title('User Detail', 'admin-page') ?>

	<form id="main-form" method="post" action="<?php echo home_url() . '/?r=view-user&amp;cid=' . $_GET['cid'] ?>">
		<div class="row">
			<div class="col-xs-12">
				<h2 class="title-border">User: <?php echo $user->display_name ?></h2>
			</div>
		</div>
		<div class="row" style="font-size: 17px; color: #fff">										
			<div class="col-xs-12">
				<table class="table table-striped table-style3">
					<tr>
						<td style="width: 200px">Username:</td>
						<td colspan="2"><?php echo $user->user_login ?></td>
					</tr>
					<tr>
						<td>First Name:</td>
						<td colspan="2"><?php echo $user->first_name ?></td>
					</tr>
					<tr>
						<td>Last Name:</td>
						<td colspan="2"><?php echo $user->last_name ?></td>
					</tr>
					<tr>
						<td>Date of Birth:</td>
						<td colspan="2"><?php echo get_user_meta($user->ID, 'date_of_birth', true) ?></td>
					</tr>
					<tr>
						<td>Email Address:</td>
						<td colspan="2"><?php echo $user->user_email ?></td>
					</tr>
					<tr>
						<td>Registration Date:</td>
						<td colspan="2"><?php echo ik_date_format($user->user_registered) ?></td>
					</tr>
					<tr>
						<td>User Type:</td>
						<td>
							<?php if($is_qualified_teacher) : ?>
								Qualified Teacher
							<?php else : if($is_registered_teacher) : ?>
								Registered Teacher
							<?php else : ?>
								Normal User
							<?php endif; endif ?>
						</td>
					</tr>
					<tr>
						<td>Points balance:</td>
						<td><?php echo ik_get_user_points($user->ID) ?>
							<a href="#give-points-modal" data-toggle="modal" class="btn btn-default grey btn-tiny">Give Points</a>
						</td>
					</tr>
				</table>
			</div>
		</div>

		<?php // teacher qualification test result
			if($is_registered_teacher || $is_qualified_teacher) :

				$filter['homework_result'] = true;
				$filter['user_id'] = $user->ID;
				$test_results = MWDB::get_group_homeworks(mw_get_option('teacher-test-group'), $filter) ?>
			
			<div class="row">
				<div class="col-sm-12">
					<h2 class="title-border">Teacher Qualification Test Results</h2>
				</div>
				<div class="col-sm-12">
					<div class="form-group">
						<div class="box">
							<table class="table table-striped table-condensed ik-table1 text-center">
								<thead>
									<tr>
										<th>Sheet Name</th>
										<th class="hidden-xs">Last Attempt</th>
										<th>Comp. Date</th>
										<th class="hidden-xs">Score</th>
										<th>%</th>
										<th>Feedback</th>
									</tr>
								</thead>
								<tbody>
									<?php if(empty($test_results->items)) : ?>
										<tr><td colspan="6">No results.</td></tr>
									<?php else : ?>
										<?php foreach($test_results->items AS $result) : ?>														
											<tr>
												<td><?php echo $result->sheet_name ?></td>
												<td class="hidden-xs"><?php echo $result->attempted_on == 'N/A' ? 'N/A': ik_date_format($result->attempted_on) ?></td>
												<td><?php echo in_array($result->submitted_on, array('0000-00-00', 'N/A')) === true ? 'Incomplete' : ik_date_format($result->submitted_on) ?></td>
												<td><?php 
													if($result->assignment_id == ASSIGNMENT_WRITING) :
														if(!is_null($result->graded)) :
															if(!$result->graded) : ?>
															<a href="<?php echo site_home_url() . '/?r=grade-homework&amp;hid=' . $result->homework_id . '&amp;sid=' . $result->userid . '&amp;admin=1' ?>" class="btn btn-default btn-block btn-tiny grey">Grade</a>
														<?php else : ?>
															Graded
														<?php endif;
														endif;
													else :
														echo $result->correct_answers_count;
													endif ?></td>
												<td class="hidden-xs"><?php echo $result->score ?></td>
												<td>
													<button type="button" tabindex="0" class="btn btn-default btn-block btn-tiny grey" title="Teacher Feedback" data-toggle="popover" data-content="<?php echo nl2br($result->message) ?>" data-html="true" data-placement="bottom" data-container="body" data-trigger="focus">View</button>
													<div style="display: none"><?php echo $result->message ?></div>
												</td>
											</tr>
										<?php endforeach ?>
									<?php endif ?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
				<div class="col-sm-4 col-sm-offset-1">
					<div class="form-group" style="text-align: right">
						<label>Change Teacher Status to</label>
					</div>
				</div>
				<div class="col-sm-4">
					<div class="form-group">
						<select class="select-box-it form-control" name="new-role">
							<option value="mw_registered_teacher"<?php echo $is_registered_teacher ? ' selected' : '' ?>>Registered Teacher</option>
							<option value="mw_qualified_teacher"<?php echo $is_qualified_teacher ? ' selected' : '' ?>>Qualified Teacher</option>
						</select>
					</div>
				</div>
				<div class="col-sm-3">
					<div class="form-group">
						<button type="submit" name="change-role" class="btn btn-default btn-block orange form-control">Change</button>
					</div>
				</div>
			</div>

		<?php endif ?>

		<?php // User's Subscription
			$subscriptions = MWDB::get_user_subscription($user->ID) ?>

			<div class="row">
				<div class="col-sm-12">
					<h2 class="title-border">User's Subscription</h2>
				</div>
				<div class="col-sm-12">
					<div class="box box-sapphire">
						<div class="scroll-list" style="max-height: 200px">
							<table class="table table-striped table-condensed ik-table1 text-center">
								<thead>
									<tr>
										<th>Type</th>
										<th>No. of Months</th>
										<th>Dictionary</th>
										<th>Group</th>
										<th>Activated On</th>
										<th>Expired On</th>
									</tr>
								</thead>
								<tbody>
									<?php if(empty($subscriptions)) : ?>
										<tr><td colspan="6">This user hasn't subscribed yet.</td></tr>
									<?php else : ?>
										<?php foreach($subscriptions as $sub) : ?>
											<tr>
												<td><?php echo $sub->subscription_name ?></td>
												<td><?php echo $sub->number_of_months ?></td>
												<td><?php echo $sub->dictionary_name ?></td>
												<td><?php echo $sub->group_name ?></td>
												<td><?php echo ik_date_format($sub->activated_on) ?></td>
												<td><?php echo ik_date_format($sub->expired_on) ?></td>
											</tr>
										<?php endforeach ?>
									<?php endif ?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>

		<?php // homeworks list
			$homeworks = MWDB::get_homeworks_by('user_id', $user->ID) ?>

		<div class="row">
			<div class="col-sm-12">
				<h2 class="title-border">Issued Homeworks</h2>
			</div>
			<div class="col-sm-12">
				<div class="form-group">
					<div class="box">
						<div class="scroll-list" style="max-height: 200px">
							<table class="table table-striped table-condensed ik-table1 text-center">
								<thead>
									<tr>
										<th>Assignment</th>
										<th>Group</th>
										<th>Sheet Name</th>
										<th>Deadline</th>
										<th>Created On</th>
										<th></th>
									</tr>
								</thead>
								<tbody>
									<?php if(empty($homeworks)) : ?>
										<tr><td colspan="6">This user hasn't issued any homeworks yet.</td></tr>
									<?php else : ?>
										<?php foreach($homeworks as $homework) : ?>
											<tr class="<?php echo !$homework->active ? 'text-muted' : '' ?>">
												<td><?php echo $homework->assignment ?></td>
												<td><a href="<?php echo home_url() . '?r=check-groups&amp;g=' . $homework->group_name . '#hlist' ?>"><?php echo $homework->group_name ?></a></td>
												<td><?php echo $homework->sheet_name ?></td>
												<td><?php echo $homework->deadline == '0000-00-00' ? 'None' : ik_date_format($homework->deadline) ?></td>
												<td><?php echo ik_date_format($homework->created_on) ?></td>
												<td>
													<?php // the the list of students signed up this homeworks
														$students = MWDB::get_homework_results($homework->id);
														$json = array();
														foreach($students as $student) {
															$json[] = $student->display_name;
														}
													?>
													<button type="button" class="btn btn-default btn-block btn-tiny grey students-list" data-list="<?php echo esc_html(json_encode((array) $json)) ?>">Students</button>
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

		<div class="row">
			<div class="col-sm-3 col-sm-offset-9">
				<a href="<?php echo home_url() ?>/?r=check-users" class="btn btn-default btn-block grey form-control"><span class="icon-goback"></span>Back</a>
			</div>
		</div>

<div class="modal fade modal-red-brown" id="give-points-modal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog">
	<div class="modal-content">
	  <div class="modal-header">
		<a href="#" data-dismiss="modal" aria-hidden="true" class="close"></a>
		<h3 class="modal-title">Give Free Points</h3>
	  </div>
	  <div class="modal-body">
		<div class="form-group">
			<label>Number of Points</label>
			<input type="number" class="form-control" name="no-of-points" value="0">
		</div>
	  </div>
	  <div class="modal-footer">
		<div class="row">
			<div class="col-sm-6">
				<button type="submit" name="give-points" class="btn btn-block orange"><span class="icon-accept"></span>OK</button>
			</div>
			<div class="col-sm-6">
				<a href="#" data-dismiss="modal" class="btn btn-block grey"><span class="icon-cancel"></span>Cancel</a>
			</div>
		</div>
	  </div>
	</div>
  </div>
</div>

	</form>

<div class="modal fade modal-red-brown" id="students-list-modal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog">
	<div class="modal-content">
	  <div class="modal-header">
		<a href="#" data-dismiss="modal" aria-hidden="true" class="close"></a>
		<h3 class="modal-title" id="myModalLabel">Students List</h3>
	  </div>
	  <div class="modal-body">		
	  </div>
	</div>
  </div>
</div>

<script>
	(function($){
		$(function(){
			$(".students-list").click(function(){
				var $list = JSON.parse($(this).attr("data-list"));
				var html = "";
				$.each($list, function(i, v){
					html += "<span style='width: 150px; display: inline-block'>" + (i + 1) + ". " + v + "</span>";
				});
				$("#students-list-modal .modal-body").html(html);
				$("#students-list-modal").modal();
			});
		});
	})(jQuery);
</script>
<?php get_dict_footer() ?>