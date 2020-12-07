<?php
	
	$is_math_panel 	= is_math_panel();
	$_page_title 	= __('View Subscription', 'iii-dictionary');
	
	$user = wp_get_current_user();
	$code = new stdClass;

	if(isset($_GET['cid']))
	{
		$code = MWDB::get_user_subscription_details($_GET['cid']);
	}
?>

<?php 
	if(!$is_math_panel) : 
		get_dict_header($_page_title);
	else :
		get_math_header($_page_title, 'red-brown');
	endif
?>
<?php get_dict_page_title($_page_title) ?>

	<form id="main-form" method="post" action="<?php echo locale_home_url() ?>/?r=view-subscription">
		<div class="row">
			<div class="col-xs-12">
				<h2 class="title-border"><?php printf(__('%s Subscription', 'iii-dictionary'),$code->code_type) ?></h2>
			</div>
		</div>
		<div class="row" style="font-size: 17px; color: #fff">										
			<div class="col-xs-12">
				<table class="table table-striped table-style3">
					<?php if(!is_null($code->encoded_code)) : ?>
						<tr>
							<td><?php _e('Subscription Code:', 'iii-dictionary') ?></td>
							<td colspan="2"><?php echo $code->encoded_code ?></td>
						</tr>
					<?php endif ?>
					<tr>
						<td style="width: 200px"><?php _e('Subscription Type:', 'iii-dictionary') ?></td>
						<td colspan="2"><?php echo $code->code_type ?><?php echo $code->typeid == SUB_SAT_PREPARATION ? ' - ' . $code->sat_class : '' ?></td>
					</tr>
					<tr>
						<td><?php _e('Subscription Start:', 'iii-dictionary') ?></td>
						<td colspan="2"><?php echo date('m/d/Y', strtotime($code->activated_on)) ?></td>
					</tr>
					<tr>
						<td><?php _e('Subscription End:', 'iii-dictionary') ?></td>
						<td colspan="2"><?php echo date('m/d/Y', strtotime($code->expired_on)) ?> 
							&mdash; <?php $days_left = floor((strtotime($code->expired_on) - time()) / (60 * 60 * 24));
											$days_left = $days_left < 0 ? 0 : $days_left;
											printf(__('%s days left', 'iii-dictionary'), $days_left) 
						?></td>
					</tr>
					<?php if(!empty($code->dictionary)) : ?>
						<tr>
							<td><?php _e('Dictionary:', 'iii-dictionary') ?></td>
							<td colspan="2"><?php echo $code->dictionary ?></td>
						</tr>
					<?php endif ?>
					<?php if(!empty($code->group_name)) : ?>
						<tr>
							<td><?php _e('Group Name:', 'iii-dictionary') ?></td>
							<td colspan="2"><?php echo $code->group_name ?></td>
						</tr>
					<?php endif ?>
					<?php if($code->typeid != SUB_SAT_PREPARATION) : ?>
						<tr>
							<td><?php $code->typeid == SUB_TEACHER_TOOL ? _e('Number of Students', 'iii-dictionary') : _e('Number of Users', 'iii-dictionary') ?>:</td>
							<td colspan="2"><?php echo $code->number_of_students ?></td>
						</tr>
					<?php endif ?>
					<?php if($code->typeid == SUB_DICTIONARY) : ?>
						<tr>
							<td><?php _e('License left:', 'iii-dictionary') ?></td>
							<td><?php echo $code->number_of_students - $code->no_activation ?>
							</td>
						</tr>
						<tr>
							<td></td>
							<td>- <?php _e('You can activate other user accounts by entering this activation code.', 'iii-dictionary') ?><br>
								- <?php _e('For public computers, please see the guideline at Manage Subscription panel.', 'iii-dictionary') ?>
							</td>
						</tr>
					<?php endif ?>
				</table>
			</div>
			<div class="col-xs-4 col-xs-offset-8" style="margin-top: 20px">
				<div class="form-group">
					<a href="<?php echo locale_home_url() ?>/?r=manage-subscription" class="btn btn-default grey form-control"><span class="icon-goback"></span><?php _e('Go back', 'iii-dictionary') ?></a>
				</div>
			</div>
		</div>
	</form>

<?php if(!$is_math_panel) : ?>
	<?php get_dict_footer() ?>
<?php else : ?>
	<?php get_math_footer() ?>
<?php endif ?>