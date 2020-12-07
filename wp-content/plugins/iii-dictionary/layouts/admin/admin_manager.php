<?php

	$cid = isset($_GET['id']) ? $_GET['id'] : 0;
	$view = empty($_GET['view']) ? 'list' : $_GET['view'];

	$is_mw_super_admin = ik_check_user_role('mw_super_admin');

	global $wp_roles;
	
	// create new admin
	if(isset($_POST['task']['create']))
	{
		$user_name = $_REAL_POST['user_name'];
		$password = $_REAL_POST['password'];
		$user_email = $_REAL_POST['user_email'];
		$display_name  = $_REAL_POST['display_name'];

		$accessible_pages = empty($_POST['accessible']) ? array() : $_POST['accessible'];
		$accessible_pages = array_flip($accessible_pages);
		foreach($accessible_pages as $key => $value) {
			$accessible_pages[$key] = true;
		}

		$user_id = username_exists( $user_name );		
		$has_error = false;

		if(strlen( $user_name ) === 0)
		{
			ik_enqueue_messages('Please enter a username.', 'error');
			$has_error = true;
		}
		if(strpos($user_name, ' ') !== false) 
		{
			ik_enqueue_messages('Username cannot contain special characters.', 'error');
			$has_error = true;
		}
		if(strlen( $password ) === 0) 
		{
			ik_enqueue_messages('Passwords must not be empty.', 'error');
			$has_error = true;
		}
		if(strlen( $password ) < 6) 
		{
			ik_enqueue_messages('Passwords must be at least six characters long.', 'error');
			$has_error = true;
		}
		if($user_id) 
		{
			ik_enqueue_messages('This username is already registered. Please choose another one.', 'error');
			$has_error = true;
		}
		if($user_email != '' && !is_email($user_email)) {
			ik_enqueue_messages('Invalid email address.', 'error');
			$has_error = true;
		}

		if(!$has_error)
		{
			$_SESSION['admin_register'] = 1;

			$user_id = wp_create_user($user_name, $password, $user_email);
			if(!is_wp_error($user_id)) {
				$display_name = !empty($display_name) ? $display_name : $user_name; 
				wp_update_user(array('ID' => $user_id, 'display_name' => $display_name));

				update_user_meta($user_id, 'ik_admin_capabilities', $accessible_pages);

				// set role to admin
				$user_obj = new WP_User($user_id);
				$user_obj->set_role('mw_admin');

				ik_enqueue_messages('Successfully create admin: ' . $user_name, 'success');
				
				wp_redirect( home_url() . '/?r=admin-manager' );
				exit;
			}
			else {
				$errors = $user_id->get_error_messages();
				foreach($errors as $error) {
					ik_enqueue_messages($error, 'error');
				}
			}
		}
	}
	
	// update admin
	if(isset($_POST['task']['update']))
	{
		$password = $_REAL_POST['password'];	
		$user_name = $_REAL_POST['user_name'];
		$user_email = $_REAL_POST['user_email'];
		$display_name  = $_REAL_POST['display_name'];

		$accessible_pages = empty($_POST['accessible']) ? array() : $_POST['accessible'];
		$accessible_pages = array_flip($accessible_pages);
		foreach($accessible_pages as $key => $value) {
			$accessible_pages[$key] = true;
		}

		$userdata = array(
			'ID' => $_POST['uID'],
			'user_email' => $user_email
		);

		if(trim($password) != '') {
			$userdata['user_pass'] = $password;
		}

		$user_id = wp_update_user($userdata);

		if(!is_wp_error($user_id)) {
			$display_name = !empty($display_name) ? $display_name : $user_name; 
			wp_update_user(array('ID' => $user_id, 'display_name' => $display_name));
				
			update_user_meta($user_id, 'ik_admin_capabilities', $accessible_pages);

			ik_enqueue_messages('Successfully update user: ' . $user_name, 'success');
			wp_redirect( home_url() . '/?r=admin-manager' );
			exit;
		}
		else {
			$errors = $user_id->get_error_messages();
			foreach($errors as $error) {
				ik_enqueue_messages($error, 'error');
				wp_redirect( home_url() . '/?r=admin-manager&id=' . $_POST['uID']);
				exit;
			}
		}
	}

	// remove admins
	if( isset($_POST['task']['remove']) )
	{
		if($is_mw_super_admin) {
			$current_user = wp_get_current_user();
			$ids = $_POST['cid'];

			if(!empty($ids)) {
				if( array_search($current_user->ID, $ids) !== false ) {
					ik_enqueue_messages('You cannot remove yourself.', 'error');
				}
				else {
					$super_admin = MWDB::get_users(array('roles' => array('mw_super_admin')));

					foreach($ids as $id)
					{
						if(!is_mw_super_admin($id)) {
							$del_status = wp_delete_user($id);
						}
						else {
							ik_enqueue_messages('You cannot remove Super Amin.', 'error');
						}
					}
				}

				if($del_status) {
					ik_enqueue_messages('Successfully delete ' . count($ids) . ' admin.', 'success');
					wp_redirect(home_url() . '/?r=admin-manager');
					exit;
				}
			}
			else {
				ik_enqueue_messages('Please select a user.', 'notice');
			}
		}
		else {
			ik_enqueue_messages('You don\'t have permission to do this action!', 'error');
		}
	}

	if($view != 'create') {
		$current_page = max( 1, get_query_var('page'));
		$items_per_page = 15;
		$offset = $items_per_page * ($current_page - 1);

		$filter['roles'] = array('mw_super_admin', 'mw_admin');

		if(isset($_REAL_POST['filter']['orderby'])) {
			$filter['orderby'] = $_REAL_POST['filter']['orderby'];
			$filter['order-dir'] = $_REAL_POST['filter']['order-dir'];
		}
		else {
			$filter['orderby'] = 'user_login';
			$filter['order-dir'] = 'asc';
		}

		$users = MWDB::get_users($filter);							//get_users_with_role // ik_check_user_role
		$total_pages = ceil($users->total / $items_per_page);

		$pagination = paginate_links(array(
			'format' => '?page=%#%',
			'current' =>  $current_page,
			'total' => $total_pages
		));
	}
	else {
		$admin_pages = ik_get_admin_pages();

		if($cid) {
			$user = get_user_by('id', $cid);
			$user_admin_cap = (array) get_user_meta($cid, 'ik_admin_capabilities', true);
		}
	}
?>

<?php get_dict_header('Admin Manager') ?>
<?php get_dict_page_title('Admin Manager', 'admin-page') ?>

	<form method="post" action="<?php echo home_url() ?>/?r=admin-manager&amp;view=<?php echo $view ?>" id="main-form">

		<?php if($view != 'create') : ?>

			<div class="row">
				<div class="col-sm-12">
					<h2 class="title-border">Admin's List</h2>
				</div>
				<div class="col-sm-5 col-md-4 col-sm-offset-7 col-md-offset-8">
					<div class="form-group">
						<a href="<?php echo home_url() ?>/?r=admin-manager&amp;view=create" class="btn btn-default orange form-control"><span class="icon-plus"></span>Add New Admin</a>
					</div>
				</div>
				<div class="col-sm-12">
					<div class="box">
						<div class="row box-header">
							<?php if($is_mw_super_admin) : ?>
								<div class="col-sm-3 col-sm-offset-9">
									<div class="form-group">
										<button name="task[remove]" type="submit" class="btn btn-default btn-block grey">Remove</button>
									</div>
								</div>
							<?php endif ?>
						</div>
						<div class="row">
							<div class="col-sm-12">
								<table class="table table-striped table-condensed ik-table1 text-center">
									<thead>
										<tr>
											<th><input type="checkbox" class="check-all" data-name="cid[]"></th>
											<th>
												<a href="#" class="sortable<?php echo $filter['orderby'] == 'user_login' ? ' ' . $filter['order-dir'] : '' ?>" data-sort-by="user_login">Username <span class="sorting-indicator"></span></a>
											</th>
											<th>
												<a href="#" class="sortable<?php echo $filter['orderby'] == 'user_email' ? ' ' . $filter['order-dir'] : '' ?>" data-sort-by="user_email">Email <span class="sorting-indicator"></span></a>
											</th>
											<th>Roles</th>
											<th>Registration Date</th>
											<th></th>
										</tr>
									</thead>
									<tfoot>
										<tr><td colspan="6"><?php echo $pagination ?></td></tr>
									</tfoot>
									<tbody>
										<?php if(empty($users->items)) : ?>
											<tr><td colspan="6">No results.</td></tr>
										<?php else :
											foreach($users->items as $u) : ?>
												<tr>
													<td><input class="chkuserid" type="checkbox" name="cid[]" value="<?php echo $u->ID ?>"></td>
													<td><a href="<?php echo home_url() ?>/?r=view-user&amp;cid=<?php echo $u->ID ?>" title="View user detail"><?php echo $u->user_login ?></a></td>
													<td><?php echo $u->user_email ?></td>
													<td><?php
														preg_match_all('/"([^"]*+)"/', $u->capabilities, $roles);
														foreach($roles[1] as $role) {
															echo $wp_roles->roles[$role]['name'] . '<br>';
														}?>
													</td>
													<td><?php echo ik_date_format($u->user_registered) ?></td>
													<td><a href="<?php echo home_url() ?>/?r=admin-manager&amp;view=create&amp;id=<?php echo $u->ID ?>" class="btn btn-default btn-block btn-tiny grey">Edit</a></td>
												</tr>
											<?php endforeach;
											endif ?>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
			<input type="hidden" name="filter[orderby]" id="filter-order" value="<?php echo $filter['orderby'] ?>">
			<input type="hidden" name="filter[order-dir]" id="filter-order-dir" value="<?php echo $filter['order-dir'] ?>">

		<?php else : ?>

			<div class="row">
				<div class="col-sm-12">
					<h2 class="title-border"><?php echo $cid ? 'Update' : 'Create New' ?> Admin</h2>
				</div>
				<div class="col-sm-6">
					<div class="form-group">
						<label for="user_name">Username</label>
						<input type="text" class="form-control" id="user_name" name="user_name" value="<?php echo $user->user_login ?>">
						<input type="hidden" name="uID" value="<?php echo $user->ID?>">
					</div>					
				</div>
				<div class="col-sm-6">
					<div class="form-group">
						<label for="password">Password</label>
						<input type="password" class="form-control" id="password" name="password" value="">
					</div>					
				</div>
				<div class="col-sm-6">
					<div class="form-group">
						<label for="user_email">Email</label>
						<input type="text" class="form-control" id="user_email" name="user_email" value="<?php echo $user->user_email ?>">
					</div>					
				</div>
				<div class="col-sm-6">
					<div class="form-group">
						<label for="user_email">Name to be displyed</label>
						<input type="text" class="form-control" id="display_name" name="display_name" value="<?php echo $user->display_name ?>">
					</div>					
				</div>
				<?php if($is_mw_super_admin) : ?>
					<div class="col-sm-12">
						<h3 class="title-border">Access Control <small>Select pages that allowed access from this admin</small></h3>
					</div>
					<div class="col-sm-12">
						<input type="checkbox" id="check-all" class="check-all" data-name="accessible[]">
						<label for="check-all">Check all</label>
					</div>
					<?php foreach($admin_pages as $slug => $page) : ?>
						<div class="col-sm-4">
							<input type="checkbox" name="accessible[]" id="p-<?php echo $slug ?>" value="<?php echo $slug ?>"<?php echo $user_admin_cap[$slug] ? ' checked' : '' ?>>
							<label for="p-<?php echo $slug ?>"><?php echo $page ?></label>
						</div>
					<?php endforeach ?>
				<?php endif ?>

				<div class="clearfix"></div>
				<div class="col-sm-6">
					<div class="form-group">
						<label>&nbsp;</label>
						<?php if(!$cid) : ?>
							<button name="task[create]" type="submit" class="btn btn-default btn-block orange form-control"><span class="icon-plus"></span>Create a new admin account</button>
						<?php else : ?>
							<button name="task[update]" type="submit" class="btn btn-default btn-block orange form-control"><span class="icon-check"></span>Update admin account</button>
						<?php endif ?>
					</div>
				</div>
				<div class="clearfix"></div>
				<div class="col-sm-6">
					<div class="form-group">
						<a href="<?php echo home_url() ?>/?r=admin-manager" class="btn btn-default grey form-control"><span class="icon-goback"></span>Back</a>
					</div>
				</div>
			</div>

		<?php endif ?>

	</form>

<?php get_dict_footer() ?>