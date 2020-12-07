<?php
$current_user_id = get_current_user_id();
$is_registered_teacher_math = is_mw_registered_teacher(get_current_user_id(), 1);
$is_registered_teacher = is_mw_registered_teacher();
if($is_registered_teacher_math ==true || $is_registered_teacher ==true){
	$gname = $gpass = '';
	$is_math_panel = is_math_panel();
	$_page_title = __('Create a Group or Class', 'iii-dictionary');
	$layout = isset($_GET['layout']) ? $_GET['layout'] : 'list';

	$is_mw_admin = false;
	if(is_mw_super_admin() || is_mw_admin())
	{
		$is_mw_admin = true;
		$group = new stdClass;
		$group->group_type_id = GROUP_CLASS;
	}

	$current_user_id = get_current_user_id();
	$a = '';

	if(isset($_POST['submit']) || isset($_POST['update']))
	{
		$data['id'] = $_REAL_POST['cid'];
		$data['gname'] = esc_html($_REAL_POST['group-name']);
		$data['gpass'] = esc_html($_REAL_POST['password']);
		
		$gname = $data['gname'];
		$gpass = $data['gpass'];

		if(empty($data['id'])) {
			$data['created_by'] = $current_user_id;
			$data['created_on'] = date('Y-m-d', time());
			$data['active'] = 1;
		}

		if($is_mw_admin) {
			$data['group_type_id'] 	= $_REAL_POST['group-types'];
			$data['class_type_id'] 	= $_REAL_POST['class-types'];
			$data['content'] 		= $_REAL_POST['group-content'];
			$data['detail'] 		= $_REAL_POST['group_detail'];
			$data['ordering'] 		= $_REAL_POST['ordering'];
			$data['price'] 			= !empty($_REAL_POST['price'])? $_REAL_POST['price'] : 0;
			$data['special_group'] 	= isset($_REAL_POST['sat_special_group']) ? 1 : 0;
		}

		if(MWDB::store_group($data)) {
			$redirect_to = locale_home_url() . '/?r=create-group';

			if(!empty($data['id'])) {
				$redirect_to .= '&layout=create&cid=' . $data['id'];
			}

			wp_redirect($redirect_to);
			exit;
		}
	}

	if(isset($_POST['order-up'])) {
		MWDB::set_group_order_up($_POST['cid']);
		wp_redirect(locale_home_url() . '/?r=create-group');
		exit;
	}

	if(isset($_POST['order-down'])) {
		MWDB::set_group_order_down($_POST['cid']);
		wp_redirect(locale_home_url() . '/?r=create-group');
		exit;
	}

	if($is_mw_admin) {
		$current_page = max( 1, get_query_var('page'));
		$filter = get_page_filter_session();
		if(empty($filter) && !isset($_POST['filter']))
		{
			$filter['orderby'] = 'ordering';
			$filter['order-dir'] = 'asc';
			$filter['items_per_page'] = 30;
			$filter['offset'] = $filter['items_per_page'] * ($current_page - 1);
			$filter['group_type'] = GROUP_CLASS;
			$filter['class_type'] = $_REAL_POST['filter']['class-types'];
		}
		else {
			if(isset($_POST['filter']['search'])) {
				$filter['group-name'] = $_REAL_POST['filter']['group-name'];
				$filter['class_type'] = $_REAL_POST['filter']['class-types'];
			}

			if(isset($_REAL_POST['filter']['orderby'])) {
				$filter['orderby'] = $_REAL_POST['filter']['orderby'];
				$filter['order-dir'] = $_REAL_POST['filter']['order-dir'];
			}

			$filter['offset'] = $filter['items_per_page'] * ($current_page - 1);
		}
		
		set_page_filter_session($filter);
		$group_types = MWDB::get_group_types();
		$class_types = MWDB::get_group_class_types();
		$filter['offset'] = 0;
		$filter['items_per_page'] = 99999999;
		$filter['is_admin_create_group'] = 1;
		$groups = MWDB::get_groups($filter, $filter['offset'], $filter['items_per_page']);
		$total_pages = ceil($groups->total / $filter['items_per_page']);
		
		$pagination = paginate_links(array(
			'format' => '?page=%#%',
			'current' =>  $current_page,
			'total' => $total_pages
		));
	}

	if(isset($_GET['cid']))
	{
		$group 	= MWDB::get_group($_GET['cid'], 'id');
		$gname 		= $group->name;
		$gpass 		= $group->password;
		$gspecial 	= $group->special_group;
	}
        }  else {
            if ($is_registered_teacher==FALSE && $is_registered_teacher_math==FALSE) {
                $title = __('Registration Required', 'iii-dictionary');
                $body = __('Please register as the teacher before create class in this panel.', 'iii-dictionary');
                                        $return_url = locale_home_url() . '/?r=my-account#4';
            }

                                        set_lockpage_dialog($title, $body, $return_url);
                        }

?>
	<?php get_math_header($_page_title, 'red-brown') ?>

<?php get_dict_page_title($_page_title, '', '', array(), get_info_tab_cloud_url('Popup_info_17.jpg')) ?>
	<div class="row">
		<div class="col-md-12">
			<form method="post" action="<?php echo locale_home_url() ?>/?r=create-group<?php echo $layout == 'create' ? '&amp;layout=create' : '' ?><?php echo isset($_GET['cid']) ? '&amp;cid=' . $_GET['cid'] : '' ?>" id="main-form">
				<?php if($is_mw_admin && $layout == 'list') : ?>
					<div class="row">
						<div class="col-sm-12">
							<h2 class="title-border"><?php _e('List of "Class" Groups', 'iii-dictionary') ?></h2>
						</div>
						<div class="col-sm-4 col-sm-offset-8">
							<div class="form-group">
								<a href="<?php echo locale_home_url() ?>/?r=create-group&amp;layout=create" class="btn btn-default orange form-control">
									<span class="icon-plus"></span><?php _e('Create Group', 'iii-dictionary') ?>
								</a>
							</div>
						</div>
						<div class="col-sm-12">
							<div class="box">
								<div class="row box-header">
									<div class="col-sm-12">
										<div class="row search-tools">
											<div class="col-sm-4">
												<div class="form-group">
													<input type="text" name="filter[group-name]" class="form-control" placeholder="<?php _e('Group name', 'iii-dictionary') ?>" value="<?php echo $filter['group-name'] ?>">
												</div>
											</div>
											<div class="col-sm-4">
												<div class="form-group">
													<select name="filter[class-types]" class="select-box-it form-control">
														<option value="">--Class--</option>
														<option value="0" <?php echo $filter['class_type'] == '0' && !is_null($filter['class_type']) ? ' selected' : ''?>><?php _e('Free', 'iii-dictionary') ?></option>
														<?php foreach($class_types as $class_type) : ?>
															<option value="<?php echo $class_type->id ?>"<?php echo $filter['class_type'] == $class_type->id ? ' selected' : ''?>><?php echo $class_type->name ?></option>
														<?php endforeach ?>
													</select>
												</div>
											</div>
											<div class="col-sm-4">
												<div class="form-group">
													<button type="submit" class="btn btn-default orange form-control" name="filter[search]"><?php _e('Search', 'iii-dictionary') ?></button>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-sm-12">
										<div class="scroll-list2" style="max-height: 600px">
											<table class="table table-striped table-condensed ik-table1 text-center">
												<thead>
													<tr>
														<th>
															<a href="#" class="sortable<?php echo $filter['orderby'] == 'g.name' ? ' ' . $filter['order-dir'] : '' ?>" data-sort-by="g.name"><?php _e('Group Name', 'iii-dictionary') ?> <span class="sorting-indicator"></span></a>
														</th>
														<th><?php _e('Class', 'iii-dictionary') ?></th>
														<th>
															<a href="#" class="sortable<?php echo $filter['orderby'] == 'ordering' ? ' ' . $filter['order-dir'] : '' ?>" data-sort-by="ordering"><?php _e('Ordering', 'iii-dictionary') ?> <span class="sorting-indicator"></span></a>
														</th>
														<th class="hidden-xs">
															<a href="#" class="sortable<?php echo $filter['orderby'] == 'created_on' ? ' ' . $filter['order-dir'] : '' ?>" data-sort-by="created_on"><?php _e('Created on', 'iii-dictionary') ?> <span class="sorting-indicator"></span></a>
														</th>
														<th></th>
													</tr>
												</thead>
												<tfoot>
													<tr><td colspan="5"><?php echo $pagination ?></td></tr>
												</tfoot>
												<tbody>
													<?php if(empty($groups->items)) : ?>
														<tr><td colspan="5"><?php _e('You haven\'t created any group yet.', 'iii-dictionary') ?></td></tr>
													<?php else :
														foreach($groups->items as $item) : ?>
															<tr>
																<td><?php echo $item->name ?></td>
																<td><?php echo !empty($item->class_name) ? $item->class_name : 'Free'; ?></td>
																<td>
																	<?php if(!empty($item->ordering)) : ?>
																	<button type="submit" name="order-up" class="btn btn-micro grey order-btn" data-id="<?php echo $item->id ?>"><span class="icon-uparrow"></span></button>
																	<button type="submit" name="order-down" class="btn btn-micro grey order-btn" data-id="<?php echo $item->id ?>"><span class="icon-downarrow"></span></button>
																	<span class="ordering"><?php echo $item->ordering ?></span>
																	<?php endif ?>
																</td>
																<td class="hidden-xs"><?php echo ik_date_format($item->created_on) ?></td>
																<td><a href="<?php echo locale_home_url() . '/?r=create-group&amp;layout=create&amp;cid=' . $item->id ?>" class="btn btn-default btn-block btn-tiny grey">Edit</a></td>
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
					</div>
				<?php endif ?>
				<input type="hidden" name="cid" value="<?php echo $_GET['cid'] ?>">
				<input type="hidden" name="filter[orderby]" id="filter-order" value="<?php echo $filter['orderby'] ?>">
				<input type="hidden" name="filter[order-dir]" id="filter-order-dir" value="<?php echo $filter['order-dir'] ?>">

				<?php if(!$is_mw_admin || $layout == 'create') : ?>
					 <div class="row">
						<div class="col-md-12">
							<div class="title-border row">
								<div class="col-md-6">
									<h2><?php _e('Create New Group', 'iii-dictionary') ?></h2>
								</div>
								<div class="col-md-6">
									<a href="javascript:void(0)" class="omg_link-format" id="manage_classroom"><?php _e('See how to manage a classroom?', 'iii-dictionary') ?></a>
								</div>
							</div>
						</div>

						<div class="col-md-9">
							<div class="form-group">
								<label for="group-name"><?php _e('Group name', 'iii-dictionary') ?></label>
								<input type="text" class="form-control" id="group-name" name="group-name" value="<?php echo $gname ?>">
							</div>     
						</div>
						<div class="col-md-9">
							<div class="form-group">
								<label for="password"><?php _e('Group password', 'iii-dictionary') ?></label>
								<input type="text" class="form-control" id="password" name="password" value="<?php echo $gpass ?>">
							</div>     
						</div>

						<div class="col-sm-9">
							<?php if(empty($_GET['cid'])) : ?>
								<div class="form-group">
									<button type="submit" name="submit" class="btn btn-default btn-block orange"><span class="icon-user-plus"></span><?php _e('Create a new group', 'iii-dictionary') ?></button>
								</div>
							<?php else : ?>
								<div class="form-group">
									<button type="submit" name="update" class="btn btn-default btn-block orange"><span class="icon-user-plus"></span><?php _e('Update group', 'iii-dictionary') ?></button>
								</div>
							<?php endif ?>
						</div>
					</div>
				<?php endif ?>

				<div class="row">
					<div class="col-md-9 group-info">
						<div class="box">
							1. <?php _e('Create a name for your group (class).', 'iii-dictionary') ?><br>
							2. <?php _e('Give the group name and password to your students to join the class.', 'iii-dictionary') ?><br>
							<?php //_e('Send homework online to your class.', 'iii-dictionary') ?>
							3. <?php _e('Go to Homework Assignment under "Teacher" and select a worksheet and send it to the group as the homework assignment.', 'iii-dictionary') ?><br>
							4. <?php _e('Homework completed by students is auto-graded.', 'iii-dictionary') ?><br>
							5. <?php _e('View the homework results at the', 'iii-dictionary') ?> <a href="<?php echo locale_home_url() ?>/?r=teachers_box"><?php _e('Manage Your Class', 'iii-dictionary') ?></a>.
						</div>
					</div>
				</div>
		  </form>
		</div>
	</div>
<div class="modal fade modal-red-brown" id="manage-classroom-modal" tabindex="-1" role="dialog" aria-hidden="true">
	  <div class="container" style="margin-top:40px; margin-bottom:20px">
		<div class="modal-content">
			<?php MWHtml::manage_your_class(); ?>	
		</div>
	  </div>
	</div>
<script>
	(function($){
		$(function(){
			
			var group_price = $('#group_price').parents('div.class-group-block');
			var select_box = $('#class-types');
			
			if(select_box.val() <= 20) {
				group_price.slideUp();
			}
			
			if($("#group-types").val() == "1"){
				$(".class-group-block").hide();
			}else {
				$(".class-free-block").hide();
			}

			$("#group-types").change(function(){
				if($(this).val() == "1"){
					$(".class-free-block").slideDown();
					$(".class-group-block").slideUp();
				}else{
					$(".class-free-block").hide();
					$(".class-group-block").slideDown();
					if(select_box.find('option:selected').val() <= 20) {
						group_price.hide();
					}
				}
			});
			
			select_box.change(function() {
				if($(this).val() > 20) {
					group_price.slideDown();
				}else {
					group_price.slideUp();
					$('#group_price').val('');
				}
			});
			$('#manage_classroom').click(function(e){
				$('#manage-classroom-modal').addClass('in');
				$('#manage-classroom-modal').modal("show");
			});
		});
	})(jQuery);
</script>
<?php if(!$is_math_panel) : ?>
	<?php get_dict_footer() ?>
<?php else : ?>
	<?php get_math_footer() ?>
<?php endif ?>