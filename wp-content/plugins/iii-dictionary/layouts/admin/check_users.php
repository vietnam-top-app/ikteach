<?php
	if(isset($_POST['opt_email_list'])){ // export email list with csv format
		global $wpdb;
		$list_user = $wpdb->get_results("SELECT ID, user_email FROM {$wpdb->users}");
		if($list_user){
			$f = fopen('php://memory', 'w');
			fputcsv($f, array('ID','Email','Language'));
			foreach ($list_user as $key => $user_i) {
				if(get_user_meta($user_i->ID,'language_type',true)) $language_type = get_user_meta($user_i->ID,'language_type',true);
				else $language_type = 'en';
				fputcsv($f, array($key+1,$user_i->user_email,$language_type));
			}
			 fseek($f, 0);
		    /** modify header to be downloadable csv file **/
		    header('Content-Type: application/csv');
		    header('Content-Disposition: attachement; filename="email_list.csv";');
		    /** Send file to browser for download */
		    fpassthru($f);
		    exit();
		}
	}

	$task = isset($_POST['task']) ? $_POST['task'] : '';
	
	if($task =='toggle-active')
	{
		$cid = $_POST['cid'];
		if(!empty($cid))
		{
			foreach($cid as $id)
			{
				ik_toggle_block_user($id);
			}

			ik_enqueue_messages('Successfully active/deactive ' . count($cid) . ' users.', 'success');
			wp_redirect(home_url() . '/?r=check-users');
			exit();
		}
	}

	if($task == 'opt_email_list') {
		$cid = $_POST['cid'];
		if(!empty($cid)) {
			$file_name = date('mdY_Hms', time()) . '.txt';
			header('Content-Type: text/plain');
			header('Content-Disposition: attachment;filename=' . $file_name);
			$fp = fopen('php://output', 'w');
			foreach($cid as $id) {
				$email = get_user_by('id', $id);
				fputs($fp, $email->data->user_email . "\r\n");
			}
			fclose($fp);
			exit;
		}
		ik_enqueue_messages('Successfully Output ' . count($cid) . ' Emails.', 'success');
		wp_redirect( home_url() . '/?r=check-users' );
		exit;
	}

	// page content
	$current_page = max( 1, get_query_var('page'));
	$filter = get_page_filter_session();
	if(empty($filter))
	{
		$filter['orderby'] = 'user_registered';
		$filter['order-dir'] = 'desc';
		$filter['items_per_page'] = 25;
		$filter['offset'] = $filter['items_per_page'] * ($current_page - 1);
	}
	else {		
		if(isset($_POST['filter']['search']))
		{
			$filter['user-name'] = $_POST['filter']['user-name'];
			$filter['user-email'] = $_POST['filter']['user-email'];
			$filter['state'] = $_POST['filter']['state'];
			$filter['user-sub'] = $_POST['filter']['user-sub'];
			$filter['user-type'] = $_POST['filter']['user-type'];
			$filter['took-test'] = $_POST['filter']['took-test'];
		}

		if(isset($_REAL_POST['filter']['orderby'])) {
			$filter['orderby'] = $_REAL_POST['filter']['orderby'];
			$filter['order-dir'] = $_REAL_POST['filter']['order-dir'];
		}

		$filter['offset'] = $filter['items_per_page'] * ($current_page - 1);
	}

	set_page_filter_session($filter);
	$filter['offset'] = 0;
	$filter['items_per_page'] = 99999999;
	$users = MWDB::get_users($filter);
	$total_pages = ceil($users->total / $filter['items_per_page']);

	$pagination = paginate_links(array(
		'format' => '?page=%#%',
		'current' =>  $current_page,
		'total' => $total_pages
	));

	/* if($task == 'remove')
	{
		$cid = $_POST['cid'];

		foreach($cid as $id)
		{
			$wpdb->update( $wpdb->prefix . 'dict_groups', array( 'active' => 0 ), array('created_by' => $id) );
			$wpdb->update( $wpdb->prefix . 'dict_homeworks', array( 'active' => 0 ), array('created_by' => $id) );
			$wpdb->delete( $wpdb->prefix . 'dict_group_students', array( 'student_id' => $id ) );
			$wpdb->delete( $wpdb->prefix . 'dict_homework_results', array( 'userid' => $id ) );
			wp_delete_user($id);
		}

		ik_enqueue_messages('Successfully remove ' . count($cid) . ' users.', 'success');
		wp_redirect( home_url() . '/?r=check-users' );
		exit;
	} */

?>
<?php get_dict_header('Check Users') ?>
<?php get_dict_page_title('Check Users\'s List', 'admin-page') ?>

	<form action="<?php echo home_url() ?>/?r=check-users" method="post" id="main-form">
		<div class="row">
			<div class="col-sm-12">
				<h2 class="title-border">User's List</h2>
			</div>
			<div class="col-sm-12">
				<div class="box box-sapphire">
					<div class="row box-header">
						<div class="col-sm-3">
							<div class="form-group">
								<button name="opt_email_list" id="opt_email_list" type="submit" class="btn btn-default grey btn-tiny form-control">Output Email List</button>
							</div>
						</div>
						<div class="col-sm-3 col-sm-offset-6">
							<div class="form-group">
								<button name="toggle-active" id="toggle-active" type="button" class="btn btn-default grey btn-tiny form-control">Active/Deactive</button>
							</div>
						</div>
						<div class="col-sm-5">
							<div class="form-group">
								<input type="text" name="filter[user-name]" class="form-control" placeholder="User name" value="<?php echo $filter['user-name'] ?>">
							</div>
						</div>
						<div class="col-sm-5">
							<div class="form-group">
								<input type="text" name="filter[user-email]" class="form-control" placeholder="User Email" value="<?php echo $filter['user-email'] ?>">
							</div>
						</div>
						<div class="col-sm-2">
							<div class="form-group">
								<select name="filter[state]" class="select-box-it select-sapphire form-control">
									<option value="">--State--</option>
									<option value="1"<?php echo $filter['state'] == '1' ? ' selected' : ''?>>Active</option>
									<option value="0"<?php echo $filter['state'] == '0' ? ' selected' : ''?>>Inactive</option>
								</select>
							</div>
						</div>
						<div class="col-sm-4">
							<div class="form-group">
								<select name="filter[user-sub]" class="select-box-it select-sapphire form-control">
									<option value="">--User Subscription--</option>
									<option value="no"<?php echo $filter['user-sub'] == 'no' ? ' selected' : ''?>>No Subscription</option>
									<option value="teacher"<?php echo $filter['user-sub'] == 'teacher' ? ' selected' : ''?>>Teacher's Homework Tool</option>
									<option value="dictionary"<?php echo $filter['user-sub'] == 'dictionary' ? ' selected' : ''?>>Dictionary</option>
									<option value="sat"<?php echo $filter['user-sub'] == 'sat' ? ' selected' : ''?>>SAT Preparation</option>
								</select>
							</div>
						</div>
						<div class="col-sm-3">
							<div class="form-group">
								<select name="filter[user-type]" class="select-box-it select-sapphire form-control">
									<option value="">--User Type--</option>
									<option value="user"<?php echo $filter['user-type'] == 'user' ? ' selected' : ''?>>Normal User</option>
									<option value="r-teacher"<?php echo $filter['user-type'] == 'r-teacher' ? ' selected' : ''?>>Registered Teacher</option>
									<option value="q-teacher"<?php echo $filter['user-type'] == 'q-teacher' ? ' selected' : ''?>>Qualified Teacher</option>
									<option value="mr-teacher"<?php echo $filter['user-type'] == 'mr-teacher' ? ' selected' : ''?>>M-Registered Teacher</option>
									<option value="mq-teacher"<?php echo $filter['user-type'] == 'mq-teacher' ? ' selected' : ''?>>M-Qualified Teacher</option>
								</select>
							</div>
						</div>
						<div class="col-sm-3">
							<div class="form-group">
								<select class="select-box-it select-sapphire form-control" name="filter[took-test]">
									<option value="">Teachers need grading</option>
									<option value="1"<?php echo $filter['took-test'] == '1' ? ' selected' : ''?>>Yes</option>
									<option value="0"<?php echo $filter['took-test'] == '0' ? ' selected' : ''?>>No</option>
								</select>
							</div>
						</div>
						<div class="col-sm-2">
							<div class="form-group">
								<button type="submit" id="search" class="btn btn-default btn-block sky-blue form-control" name="filter[search]">Search</button>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-12">
							<div class="scroll-list2" style="max-height: 600px">
								<table class="table table-striped table-condensed ik-table1 text-center" id="list-sheets">
									<thead>
										<tr>
											<th><input type="checkbox" class="check-all" data-name="cid[]"></th>
											<th>
												<a href="#" class="sortable<?php echo $filter['orderby'] == 'display_name' ? ' ' . $filter['order-dir'] : '' ?>" data-sort-by="display_name">Account name <span class="sorting-indicator"></span></a>
											</th>
											<th>
												<a href="#" class="sortable<?php echo $filter['orderby'] == 'user_email' ? ' ' . $filter['order-dir'] : '' ?>" data-sort-by="user_email">Email <span class="sorting-indicator"></span></a>
											</th>
											<th>User Type</th>
											<th>
												<a href="#" class="sortable<?php echo $filter['orderby'] == 'user_registered' ? ' ' . $filter['order-dir'] : '' ?>" data-sort-by="user_registered">Registration Date <span class="sorting-indicator"></span></a>
											</th>
											<th>
												<a href="#" class="sortable<?php echo $filter['orderby'] == 'date_of_birth' ? ' ' . $filter['order-dir'] : '' ?>" data-sort-by="date_of_birth">Age <span class="sorting-indicator"></span></a>
											</th>
											<th>
												<a href="#" class="sortable" data-sort-by="language">Language <span class="sorting-indicator"></span></a>
											</th>
										</tr>
									</thead>
									<tfoot>
										<tr><td colspan="6"><?php echo $pagination ?></td></tr>
									</tfoot>
									<tbody><?php if(empty($users->items)) : ?>
										<tr><td colspan="6">No results</td></tr>
									<?php else : foreach($users->items as $user) :
										?><tr<?php echo is_user_enable($user->ID) ? '' : ' class="text-muted"' ?>><?php
											?><td><input type="checkbox" name="cid[]" value="<?php echo $user->ID ?>"></td><?php
											?><td><a href="<?php echo home_url() ?>/?r=view-user&amp;cid=<?php echo $user->ID ?>"><?php echo $user->display_name ?></a></td><?php
											?><td><?php echo $user->user_email ?></td><?php
											?><td><?php echo ik_get_user_type($user->ID) ?></td><?php
											?><td><?php echo ik_date_format($user->user_registered) ?></td><?php
												if(empty($user->date_of_birth) || !strtotime($user->date_of_birth)) {
													$date_of_birth = 'N/A';
													$age = 'N/A';
												}
												else {
													$date_of_birth = ik_date_format($user->date_of_birth);
													$date1 = new DateTime();
													$date2 = new DateTime($user->date_of_birth);
													$interval = $date1->diff($date2);
													$age = $interval->y; 
												}
											?>
											<td><?php echo $age ?></td>
											<td><?php echo get_language_type_of_user($user->ID); ?></td>
											</tr><?php
										endforeach; endif
									?></tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<input type="hidden" name="filter[orderby]" id="filter-order" value="<?php echo $filter['orderby'] ?>">
		<input type="hidden" name="filter[order-dir]" id="filter-order-dir" value="<?php echo $filter['order-dir'] ?>">
		<input type="hidden" name="task" id="task" value="">
	</form>

<div class="modal fade modal-red-brown" id="confirm-modal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog">
	<div class="modal-content">
	  <div class="modal-header">
		<a href="#" data-dismiss="modal" aria-hidden="true" class="close"></a>
		<h3 class="modal-title" id="myModalLabel">Confirmation</h3>
	  </div>
	  <div class="modal-body">		
	  </div>
	  <div class="modal-footer">
		<div class="row">
			<div class="col-sm-6">
				<a href="#" data-dismiss="modal" id="btnConfirm" class="btn btn-block orange confirm"><span class="icon-accept"></span>Yes</a>
			</div>
			<div class="col-sm-6">
				<a href="#" data-dismiss="modal" aria-hidden="true" class="btn btn-block grey secondary"><span class="icon-cancel"></span>No</a>
			</div>
		</div>
	  </div>
	</div>
  </div>
</div>

<?php get_dict_footer() ?>