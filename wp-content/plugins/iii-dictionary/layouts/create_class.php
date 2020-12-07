<?php
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

	if(isset($_POST['submit']))
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
			$redirect_to = locale_home_url() . '/?r=create-class';

			if(!empty($data['id'])) {
				$redirect_to .= '&layout=create&cid=' . $data['id'];
			}

			wp_redirect($redirect_to);
			exit;
		}
	}
?>
<?php get_math_header($_page_title, 'red-brown') ?>
<?php get_dict_page_title($_page_title, '', '', array(), get_info_tab_cloud_url('Popup_info_17.jpg')) ?>

	<div class="row">
		<div class="col-md-12">
			<form method="post" action="<?php echo locale_home_url() ?>/?r=create-class<?php echo $layout == 'create' ? '&amp;layout=create' : '' ?><?php echo isset($_GET['cid']) ? '&amp;cid=' . $_GET['cid'] : '' ?>" id="main-form">
				<input type="hidden" name="cid" value="<?php echo $_GET['cid'] ?>">
				<input type="hidden" name="filter[orderby]" id="filter-order" value="<?php echo $filter['orderby'] ?>">
				<input type="hidden" name="filter[order-dir]" id="filter-order-dir" value="<?php echo $filter['order-dir'] ?>">
				<div class="row">
					<div class="col-md-12">
						<div class="title-border row">
							<div class="col-md-6">
								<h2><?php _e('Create New Group', 'iii-dictionary') ?></h2>
							</div>
							<div class="col-md-6">
									<a href="<?php echo locale_home_url() ?>/?r=manage-your-class" class="omg_link-format"><?php _e('See how to manage a classroom?', 'iii-dictionary') ?></a>
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
							<label >Select</label>
							<select class="select-box-it form-control" name="">
								<option value="english">English</option>
								<option value="math">Math</option>
							</select>
						</div>
					</div>
					<div class="col-md-9">
						<div class="form-group">
							<label for="password"><?php _e('Group password', 'iii-dictionary') ?></label>
							<input type="text" class="form-control" id="password" name="password" value="<?php echo $gpass ?>">
						</div>     
					</div>

					<div class="col-sm-9">
						<div class="form-group">
							<button type="submit" name="submit" class="btn btn-default btn-block orange"><span class="icon-user-plus"></span><?php _e('Create a new group', 'iii-dictionary') ?></button>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-9 group-info">
						<div class="box">
							1. <?php _e('Create a name for your group (class).', 'iii-dictionary') ?><br>
							2. <?php _e('Give the group name and password to your students to join the class.', 'iii-dictionary') ?><br>
							<?php //_e('Send homework online to your class.', 'iii-dictionary') ?>
							3. <?php _e('Go to Homework Assignment under "Teacher" and select a worksheet and send it to the group as the homework assignment.', 'iii-dictionary') ?><br>
							4. <?php _e('Homework completed by students is auto-graded.', 'iii-dictionary') ?><br>
							5. <?php _e('View the homework results at the', 'iii-dictionary') ?> <a href="<?php echo locale_home_url() ?>/?r=teachers_box"><?php _e('Teacher\'s Box', 'iii-dictionary') ?></a>.
						</div>
					</div>
				</div>
		  </form>
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
		});
	})(jQuery);
</script>
<?php if(!$is_math_panel) : ?>
	<?php get_dict_footer() ?>
<?php else : ?>
	<?php get_math_footer() ?>
<?php endif ?>