<?php

	$task = isset($_POST['task']) ? $_POST['task'] : '';

	if($task == 'toggle-active')
	{
		$cid = $_POST['cid'];
		if(!empty($cid))
		{
			foreach($cid as $id)
			{
				$result = $wpdb->query(
					$wpdb->prepare('UPDATE ' . $wpdb->prefix . 'dict_groups SET active = ABS(active - 1) WHERE id = %d', $id)
				);
				
				if(!$result) {
					break;
				}
			}

			if($result) {
				ik_enqueue_messages('Successfully active/deactive ' . count($cid) . ' Groups.', 'success');
				wp_redirect( home_url() . '/?r=check-groups' );
				exit;
			}
			else {
				ik_enqueue_messages('There\'s error occurs during the operation.', 'error');
				wp_redirect( home_url() . '/?r=check-groups' );
				exit;
			}
		}
	}
	
	// page content
	$current_page = max( 1, get_query_var('page'));
	$filter = get_page_filter_session();
	if(empty($filter))
	{
		$filter['orderby'] = 'created_on';
		$filter['order-dir'] = 'desc';
		$filter['items_per_page'] = 15;
		$filter['offset'] = $filter['items_per_page'] * ($current_page - 1);
		$filter['subscription_status'] = true;
	}
	else {
		if(isset($_POST['filter']['search'])) {
			$filter['role'] = $_POST['filter']['role'];
			$filter['group-name'] = $_REAL_POST['filter']['group-name'];
			$filter['owner-name'] = $_REAL_POST['filter']['owner-name'];
			$filter['state'] = $_POST['filter']['state'];
			$filter['group_type'] = $_POST['filter']['group_type'];
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
	$groups = MWDB::get_groups($filter, $filter['offset'], $filter['items_per_page']);
	$total_pages = ceil($groups->total / $filter['items_per_page']);

	$pagination = paginate_links( array(
		'format' => '?page=%#%',
		'current' =>  $current_page,
		'total' => $total_pages
	) );

	if(isset($_GET['g']))
	{
		$f_g = esc_html($_GET['g']);
		$current_group = MWDB::get_group($f_g);
		$homeworks = MWDB::get_homeworks_by('group_id', $current_group->id);
		$students = MWDB::get_group_students($current_group->id);

		$js_hw = 'var hw_list = {a1: [], a2: [], a3: [], a4: [], a6: [], a7: [], a8: [], a9: [], a10: [], a11: [], a12: [], a13: [], a14: [], a15: []};';
		foreach($homeworks as $hw)
		{
			$js_hw .= 'hw_list.a' . $hw->assignment_id . '.push({type: "' . $hw->assignment . '", grade: "' . $hw->grade . '", sheet: "' . $hw->sheet_name . '", dl: "' . $hw->deadline . '", cd: "' . $hw->created_on . '"});';
		}
	}
	
?>
<?php get_dict_header('Check Groups\'s List') ?>
<?php get_dict_page_title('Check Groups\'s List', 'admin-page') ?>

	<form action="<?php echo home_url() ?>/?r=check-groups" method="post" id="main-form">
		<div class="row">
			<div class="col-xs-12 box box-sapphire">
				<div class="row box-header">
					<div class="col-xs-9">
						<h3>Groups's List</h3>
					</div>
					<div class="col-xs-3">
						<button name="toggle-active" id="toggle-active" type="button" class="btn btn-default grey btn-tiny form-control">Active/Deactive</button>
					</div>
					<div class="row">
						<div class="col-xs-12">
							<div class="row search-tools">
								<div class="col-sm-3">
									<input type="text" name="filter[group-name]" class="form-control" placeholder="Group name" value="<?php echo $filter['group-name'] ?>">
								</div>
								<div class="col-sm-3">
									<input type="text" name="filter[owner-name]" class="form-control" placeholder="Owner name" value="<?php echo $filter['owner-name'] ?>">
								</div>
								<div class="col-sm-2">
									<select name="filter[group_type]" class="select-box-it select-sapphire form-control">
										<option value="">--Type--</option>
										<option value="<?php echo GROUP_FREE ?>"<?php echo $filter['group_type'] == GROUP_FREE ? ' selected' : ''?>>Free</option>
										<option value="<?php echo GROUP_SUBSCRIBED ?>"<?php echo $filter['group_type'] == GROUP_SUBSCRIBED ? ' selected' : ''?>>Subscribed</option>
										<option value="<?php echo GROUP_CLASS ?>"<?php echo $filter['group_type'] == GROUP_CLASS ? ' selected' : ''?>>Class</option>
									</select>
								</div>
								<div class="col-sm-2">
									<select name="filter[state]" class="select-box-it select-sapphire form-control">
										<option value="">--State--</option>
										<option value="1"<?php echo $filter['state'] == '1' ? ' selected' : ''?>>Active</option>
										<option value="0"<?php echo $filter['state'] == '0' ? ' selected' : ''?>>Inactive</option>
									</select>
								</div>
								<div class="col-sm-2">
									<div class="form-group">
										<button type="submit" class="btn btn-default sky-blue form-control" name="filter[search]">Search</button>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<div class="scroll-list2" style="max-height: 600px">
							<div class=" grid-table grid-table-striped">
								<div class="row grid-table-head">
									<div class="col-xs-1 text-center"><input type="checkbox" class="check-all" data-name="cid[]"></div>
									<div class="col-xs-1 text-center">Active</div>
									<div class="col-xs-2 text-center"><a href="#" class="sortable<?php echo $filter['orderby'] == 'name' ? ' ' . $filter['order-dir'] : '' ?>" data-sort-by="name">Group Name<span class="sorting-indicator"></span></a></div>
									<div class="col-xs-2 text-center"><a href="#" class="sortable<?php echo $filter['orderby'] == 'display_name' ? ' ' . $filter['order-dir'] : '' ?>" data-sort-by="display_name">Owner<span class="sorting-indicator"></span></a></div>
									<div class="col-xs-2 text-center">No. of Students</div>
									<div class="col-xs-2 text-center"><a href="#" class="sortable<?php echo $filter['orderby'] == 'expired_on' ? ' ' . $filter['order-dir'] : '' ?>" data-sort-by="expired_on">Sub. Exp<span class="sorting-indicator"></span></a></div>
									<div class="col-xs-2 text-center"><a href="#" class="sortable<?php echo $filter['orderby'] == 'created_on' ? ' ' . $filter['order-dir'] : '' ?>" data-sort-by="created_on">Created On<span class="sorting-indicator"></span></a></div>
								</div>
								<?php if(!empty($groups->items)) :
										foreach($groups->items as $group) : ?>
											<div class="row grid-table-row<?php echo !$group->active ? ' grid-row-gray' : '' ?>">
												<div class="col-xs-1 text-center"><input type="checkbox" name="cid[]" value="<?php echo $group->id ?>"></div>
												<div class="col-xs-1 text-center"><span class="icon-<?php echo $group->active ? 'check' : 'cancel' ?> icon-nomargin"></span></div>
												<div class="col-xs-2 text-center">
													<a href="<?php echo home_url() . '/?r=check-groups&g=' . $group->name . '#hlist' ?>" title="View homeworks"><?php echo $group->name ?></a>
												</div>
												<div class="col-xs-2 text-center">
													<?php if(!is_null($group->display_name)) : ?>
														<a href="<?php echo home_url() . '/?r=view-user&cid=' . $group->uid ?>"><?php echo $group->display_name ?></a>
													<?php else : ?>
														<span style="color: #f00">Owner deleted</span>
													<?php endif ?>
												</div>
												<div class="col-xs-2 text-center"><?php echo $group->no_of_student ?></div>
												<div class="col-xs-2 text-center"><?php echo is_null($group->expired_on) ? 'No Sub.' : ik_date_format($group->expired_on) ?></div>
												<div class="col-xs-2 text-center"><?php echo ik_date_format($group->created_on) ?></div>
											</div>
										<?php endforeach;
									else : ?>
										<div class="row grid-table-row">
											<div class="col-xs-12 text-center">No results found</div>
										</div>
									<?php endif ?>
							</div>
						</div>
					</div>
				</div>
				
				<div class="row">
					<div class="col-xs-12 text-center">
						<?php echo $pagination ?>
					</div>
				</div>
			</div>												
		</div>

		<div class="row" style="margin-top: 20px" id="hlist">
			<div class="col-md-12 box">
				<div class="row box-header">
					<div class="col-xs-12">
						<h3>Group Homeworks</h3>
					</div>
					<?php if(isset($f_g)) : ?>
						<div class="col-xs-6 col-md-4">
							<h4>Group Name: <span id="g-name" style="color: #fff"><?php echo $current_group->name ?></span></h4>
						</div>
						<div class="col-xs-6 col-md-4">
							<h4>Owner: <span id="g-grade" style="color: #fff"><?php echo $current_group->display_name ?></span></h4>
						</div>
						<div class="col-xs-3 col-sm-3">
							<?php MWHtml::get_sel_assignments('', false, array(), '-Assignment-', 'filter[assignment-id]', 'form-control', 'filter-assignment') ?>
						</div>
					<?php endif ?>
				</div>
				<div class="row">
					<div class="col-md-12 grid-table grid-table-striped">
						<div class="row grid-table-head">
							<div class="col-xs-3 text-center">Type</div>
							<div class="col-xs-1 text-center">Grade</div>
							<div class="col-xs-2 text-center">Sheet Name</div>
							<div class="col-xs-3 text-center">Deadline</div>
							<div class="col-xs-3 text-center">Created On</div>
						</div>
						<div class="scroll-list" style="max-height: 400px">
							<div class="grid-table-body" id="hw-list">
								<?php if(!isset($f_g)) : ?>
									<div class="row grid-table-row">
										<div class="col-xs-12 text-center">Select a Group to view details</div>
									</div>
								<?php endif ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="row" style="margin-top: 20px">
			<div class="col-xs-12 box box-sapphire">
				<div class="row box-header">
					<div class="col-xs-12">
						<h3>Joined Users's List</h3>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12 grid-table grid-table-striped">
						<div class="row grid-table-head">
							<div class="col-xs-4 text-center">Username</div>
							<div class="col-xs-4 text-center">Email</div>
							<div class="col-xs-4 text-center">Homeworks Done/in Progress</div>
						</div>
						<div class="scroll-list" style="max-height: 200px">
							<div class="row grid-table-body">
								<?php if(empty($students)) : ?>
									<div class="row grid-table-row">
										<div class="col-xs-12 text-center">N/A</div>
									</div>
								<?php else : ?>
									<?php foreach($students as $student) : ?>
										<div class="row grid-table-row">
											<div class="col-xs-4 text-center"><?php echo $student->display_name ?></div>
											<div class="col-xs-4 text-center"><?php echo $student->user_email ?></div>
											<div class="col-xs-4 text-center"><?php echo $student->homeworks_done ?></div>
										</div>
									<?php endforeach ?>
								<?php endif ?>
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
  <div class="modal-dialog" style="width: 680px;">
	<div class="modal-content">
	  <div class="modal-header">
		<a href="#" data-dismiss="modal" aria-hidden="true" class="close"></a>
		<h3 class="modal-title" id="myModalLabel">Confirmation</h3>
	  </div>
	  <div class="modal-body">		
	  </div>
	  <div class="modal-footer">
		<div class="row" style="padding-left: 30px; padding-right: 30px;">
			<div class="col-md-6">
				<a href="#" data-dismiss="modal" id="btnConfirm" class="btn btn-block orange confirm"><span class="icon-accept"></span>Yes</a>
			</div>
			<div class="col-md-6">
				<a href="#" data-dismiss="modal" aria-hidden="true" class="btn btn-block grey secondary"><span class="icon-cancel"></span>No</a>
			</div>
		</div>
	  </div>
	</div>
  </div>
</div>

<script>
	(function($){
		$(function(){
			<?php if(isset($js_hw)) : echo $js_hw; ?>
				display_hw_list(hw_list.a1.concat(hw_list.a2, hw_list.a3, hw_list.a4, hw_list.a6, hw_list.a7, hw_list.a8, hw_list.a9, hw_list.a10, hw_list.a11, hw_list.a12, hw_list.a13, hw_list.a14, hw_list.a15));				
				$("#filter-assignment").change(function(){
					var type = $(this).val();
					$("#hw-list").html("");
					if(type == ""){
						display_hw_list(hw_list.a1.concat(hw_list.a2, hw_list.a3, hw_list.a4, hw_list.a6, hw_list.a7, hw_list.a8, hw_list.a9, hw_list.a10, hw_list.a11, hw_list.a12, hw_list.a13, hw_list.a14, hw_list.a15));
					}else{
						display_hw_list(hw_list["a" + type]);
					}
				});

				function display_hw_list(list){
					if(list.length > 0){
						$.each(list, function(i,e){
							var row = $('<div class="row grid-table-row" style="display: none">' +
											'<div class="col-xs-3 text-center">' + e.type + '</div>' +
											'<div class="col-xs-1 text-center">' + e.grade + '</div>' +
											'<div class="col-xs-2 text-center">' + e.sheet + '</div>' +
											'<div class="col-xs-3 text-center">' + e.dl + '</div>' +
											'<div class="col-xs-3 text-center">' + e.cd + '</div>' +
										'</div>');
							row.appendTo("#hw-list").fadeIn();
						});
					}else{
						var row = $('<div class="row grid-table-row" style="display: none">' +
										'<div class="col-xs-12 text-center">No homework assigned to this group.</div>' +
									'</div>');
						row.appendTo("#hw-list").fadeIn();
					}
				}
			<?php endif ?>

			$("#toggle-active").click(function(){
				var check_count = $('[name="cid[]"]:checked').length;
				if(check_count == 0) {
					$("#confirm-modal .modal-body").html("You must select a Group first.");
					$("#confirm-modal .modal-footer > .row").html('<div class="col-md-12"><a href="#" data-dismiss="modal" class="btn btn-block grey"><span class="icon-cancel"></span>Back</a></div>');
				}else{
					$("#task").val("toggle-active");
					$("#confirm-modal .modal-body").html("You are about to Active/Deactive " + check_count + " Groups.<br>Do you want to process?");
					$("#confirm-modal .modal-footer > .row").html('<div class="col-md-6"><a href="#" data-dismiss="modal" id="btnConfirm" class="btn btn-block orange confirm"><span class="icon-accept"></span>Yes</a></div><div class="col-md-6"><a href="#" data-dismiss="modal" aria-hidden="true" class="btn btn-block grey secondary"><span class="icon-cancel"></span>No</a></div>');
				}
				$("#confirm-modal").modal();
			});

			$("body").on("click", "#btnConfirm", function(){
				$("#main-form").submit();
			});
		});
	})(jQuery);
</script>
<?php get_dict_footer() ?>