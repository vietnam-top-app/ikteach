<?php

	$route = get_route();
	$client = $_GET['client'];

	$active_tab = empty($route[1]) ? 'vocab' : $route[1];

	$class_type_obj = MWDB::get_group_class_type_by('slug', $active_tab);
	$class_type = $class_type_obj->id;
	$subscription_fee = ik_get_sat_subscription_fee($class_type);
	
	/*
	* add subtitle 
	* now just add for math, so variable will be define at here. 
	* it will remove when all done.
	* remove done
	*/
	$subtitle = '';
	$is_link = array();
	

	// English SAT Preparation page
	if(empty($client)) {
		$header_title = __('SAT Preparation', 'iii-dictionary');
		$form_action = locale_home_url() . '/?r=sat-preparation/' . $active_tab;
		$subtitle  = '2016 Version';
		$is_change = true;
		$is_link   = array(
			'goto_url' 	=> site_math_url() . '?r=sat-preparation/sat1prep&client=math-sat1',
			'name' 		=> 'Math SAT',
			'prefix' 	=> 'Go to &#8594',
			'class' 	=> 'omg_sat-link'
		);
		
		$tabs = array(
			'vocab' => array(
				'url' => locale_home_url() . '/?r=sat-preparation/vocab',
				'text' => __('Vocabulary / Grammar', 'iii-dictionary')
			),
			'writing' => array(
				'url' => locale_home_url() . '/?r=sat-preparation/writing',
				'text' => __('Writing Skills', 'iii-dictionary')
			),
			'sat1' => array(
				'url' => locale_home_url() . '/?r=sat-preparation/sat1',
				'text' => __('SAT Test 1', 'iii-dictionary')
			),
			'sat2' => array(
				'url' => locale_home_url() . '/?r=sat-preparation/sat2',
				'text' => __('SAT Test 2', 'iii-dictionary')
			),
			'sat3' => array(
				'url' => locale_home_url() . '/?r=sat-preparation/sat3',
				'text' => __('SAT Test 3', 'iii-dictionary')
			),
			'sat4' => array(
				'url' => locale_home_url() . '/?r=sat-preparation/sat4',
				'text' => __('SAT Test 4', 'iii-dictionary')
			),
			'sat5' => array(
				'url' => locale_home_url() . '/?r=sat-preparation/sat5',
				'text' => __('SAT Test 5', 'iii-dictionary')
			)
		);

		switch($active_tab) {
			case 'vocab':
				$tab_info_url = get_info_tab_cloud_url('Popup_info_30.jpg');
				$sub_msg = __('Please subscribe SAT Preparation - Vocabulary / Grammar to start', 'iii-dictionary');
				break;
			case 'writing':
				$tab_info_url = get_info_tab_cloud_url('Popup_info_31.jpg');
				$sub_msg = __('Please subscribe SAT Preparation - Writing Skills to start', 'iii-dictionary');
				break;
			case 'sat1':
				$sub_msg = __('Please subscribe SAT Preparation - SAT Test 1 to start', 'iii-dictionary');
				$tab_info_url = get_info_tab_cloud_url('Popup_info_32.jpg');
				break;
			case 'sat2':
				$sub_msg = __('Please subscribe SAT Preparation - SAT Test 2 to start', 'iii-dictionary');
				$tab_info_url = get_info_tab_cloud_url('Popup_info_32.jpg');
				break;
			case 'sat3':
				$sub_msg = __('Please subscribe SAT Preparation - SAT Test 3 to start', 'iii-dictionary');
				$tab_info_url = get_info_tab_cloud_url('Popup_info_32.jpg');
				break;
			case 'sat4':
				$sub_msg = __('Please subscribe SAT Preparation - SAT Test 4 to start', 'iii-dictionary');
				$tab_info_url = get_info_tab_cloud_url('Popup_info_32.jpg');
				break;
			case 'sat5':
				$tab_info_url = get_info_tab_cloud_url('Popup_info_32.jpg');
				$sub_msg = __('Please subscribe SAT Preparation - SAT Test 5 to start', 'iii-dictionary');
				break;
		}
	}
	// Math SAT 1 Preparation page
	else if($client === 'math-sat1') {
		$header_title = __('SAT I Preparation', 'iii-dictionary');
		$form_action = locale_home_url() . '/?r=sat-preparation/' . $active_tab . '&client=math-sat1';
		$subtitle  = '2016 version';
		$is_change = false;
		$is_link   = array(
			'goto_url' 	=> site_home_url() . '?r=sat-preparation',
			'name' 		=> 'English SAT',
			'prefix' 	=> 'Go to &#8594',
			'class' 	=> 'omg_sat-link'
		);
		$tabs = array(
			'sat1prep' => array(
				'url' => locale_home_url() . '/?r=sat-preparation/sat1prep&client=math-sat1',
				'text' => __('Preparation', 'iii-dictionary')
			),
			'sat1a' => array(
				'url' => locale_home_url() . '/?r=sat-preparation/sat1a&client=math-sat1',
				'text' => __('SAT 1A', 'iii-dictionary')
			),
			'sat1b' => array(
				'url' => locale_home_url() . '/?r=sat-preparation/sat1b&client=math-sat1',
				'text' => __('SAT 1B', 'iii-dictionary')
			),
			'sat1c' => array(
				'url' => locale_home_url() . '/?r=sat-preparation/sat1c&client=math-sat1',
				'text' => __('SAT 1C', 'iii-dictionary')
			),
			'sat1d' => array(
				'url' => locale_home_url() . '/?r=sat-preparation/sat1d&client=math-sat1',
				'text' => __('SAT 1D', 'iii-dictionary')
			),
			'sat1e' => array(
				'url' => locale_home_url() . '/?r=sat-preparation/sat1e&client=math-sat1',
				'text' => __('SAT 1E', 'iii-dictionary')
			)
		);

		switch($active_tab) {
			case 'sat1prep':
				//$tab_info_url = get_info_tab_cloud_url('Popup_info_30.jpg');
				$sub_msg = __('Please subscribe SAT Preparation - Preparation to start', 'iii-dictionary');
				break;
			case 'sat1a':
				//$tab_info_url = get_info_tab_cloud_url('Popup_info_31.jpg');
				$sub_msg = __('Please subscribe SAT Preparation - SAT 1A to start', 'iii-dictionary');
				break;
			case 'sat1b':
				$sub_msg = __('Please subscribe SAT Preparation - SAT 1B to start', 'iii-dictionary');
				//$tab_info_url = get_info_tab_cloud_url('Popup_info_32.jpg');
				break;
			case 'sat1c':
				$sub_msg = __('Please subscribe SAT Preparation - SAT 1C to start', 'iii-dictionary');
				//$tab_info_url = get_info_tab_cloud_url('Popup_info_32.jpg');
				break;
			case 'sat1d':
				$sub_msg = __('Please subscribe SAT Preparation - SAT 1D to start', 'iii-dictionary');
				//$tab_info_url = get_info_tab_cloud_url('Popup_info_32.jpg');
				break;
			case 'sat1e':
				$sub_msg = __('Please subscribe SAT Preparation - SAT 1E to start', 'iii-dictionary');
				//$tab_info_url = get_info_tab_cloud_url('Popup_info_32.jpg');
				break;
		}
	}
	// Math SAT 2 Preparation page
	else if($client === 'math-sat2') {
		$header_title = __('SAT II Preparation', 'iii-dictionary');
		$form_action = locale_home_url() . '/?r=sat-preparation/' . $active_tab . '&client=math-sat2';
		$is_change = false;

		$tabs = array(
			'sat2prep' => array(
				'url' => locale_home_url() . '/?r=sat-preparation/sat2prep&client=math-sat2',
				'text' => __('Preparation', 'iii-dictionary')
			),
			'sat2a' => array(
				'url' => locale_home_url() . '/?r=sat-preparation/sat2a&client=math-sat2',
				'text' => __('SAT 2A', 'iii-dictionary')
			),
			'sat2b' => array(
				'url' => locale_home_url() . '/?r=sat-preparation/sat2b&client=math-sat2',
				'text' => __('SAT 2B', 'iii-dictionary')
			),
			'sat2c' => array(
				'url' => locale_home_url() . '/?r=sat-preparation/sat2c&client=math-sat2',
				'text' => __('SAT 2C', 'iii-dictionary')
			),
			'sat2d' => array(
				'url' => locale_home_url() . '/?r=sat-preparation/sat2d&client=math-sat2',
				'text' => __('SAT 2D', 'iii-dictionary')
			),
			'sat2e' => array(
				'url' => locale_home_url() . '/?r=sat-preparation/sat2e&client=math-sat2',
				'text' => __('SAT 2E', 'iii-dictionary')
			)
		);

		switch($active_tab) {
			case 'sat2prep':
				//$tab_info_url = get_info_tab_cloud_url('Popup_info_30.jpg');
				$sub_msg = __('Please subscribe SAT Preparation - Preparation to start', 'iii-dictionary');
				break;
			case 'sat2a':
				//$tab_info_url = get_info_tab_cloud_url('Popup_info_31.jpg');
				$sub_msg = __('Please subscribe SAT Preparation - SAT 2A to start', 'iii-dictionary');
				break;
			case 'sat2b':
				$sub_msg = __('Please subscribe SAT Preparation - SAT 2B to start', 'iii-dictionary');
				//$tab_info_url = get_info_tab_cloud_url('Popup_info_32.jpg');
				break;
			case 'sat2c':
				$sub_msg = __('Please subscribe SAT Preparation - SAT 2C to start', 'iii-dictionary');
				//$tab_info_url = get_info_tab_cloud_url('Popup_info_32.jpg');
				break;
			case 'sat2d':
				$sub_msg = __('Please subscribe SAT Preparation - SAT 2D to start', 'iii-dictionary');
				//$tab_info_url = get_info_tab_cloud_url('Popup_info_32.jpg');
				break;
			case 'sat2e':
				$sub_msg = __('Please subscribe SAT Preparation - SAT 2E to start', 'iii-dictionary');
				//$tab_info_url = get_info_tab_cloud_url('Popup_info_32.jpg');
				break;
		}
	}

	$tab_options = array(
		'items' => $tabs,
		'active' => $active_tab
	);

	$is_sat_class_subscribed = is_sat_class_subscribed($class_type);

	if($is_sat_class_subscribed) {
		// user click Start button, join user to the group.
		if(!empty($_POST['jid'])) {
			$g = MWDB::get_group($_POST['jid'], 'id');

			if(MWDB::join_group($_POST['jid'])) {
				wp_redirect(locale_home_url() . '/?r=homework-status');
				exit;
			}
		}
	}

	$current_page = max( 1, get_query_var('page'));
	$filter = get_page_filter_session();
	if(empty($filter)) {
		$filter['orderby'] = 'ordering';
		$filter['items_per_page'] = 25;
		$filter['offset'] = $filter['items_per_page'] * ($current_page - 1);
		$filter['group_type'] = GROUP_CLASS;
		$filter['class_type'] = $class_type;
	}
	else {
		$filter['class_type'] = $class_type;
		$filter['offset'] = $filter['items_per_page'] * ($current_page - 1);
	}

	set_page_filter_session($filter);
	$filter['offset'] = 0;
	$filter['items_per_page'] = 99999999;
	$groups = MWDB::get_groups($filter, $filter['offset'], $filter['items_per_page']);
	$total_pages = ceil($groups->total / $filter['items_per_page']);

	$pagination = paginate_links(array(
		'format' => '?page=%#%',
		'current' =>  $current_page,
		'total' => $total_pages
	));

	ik_enqueue_js_messages('login_req_h', __('Login Required', 'iii-dictionary'));
	ik_enqueue_js_messages('login_req_err', __('Please login in order to continue to use this function.', 'iii-dictionary'));
	ik_enqueue_js_messages('login_req_lbl', __('Login', 'iii-dictionary'));
	
	ik_enqueue_js_messages('sub_req_h', __('Subscription Required', 'iii-dictionary'));
	ik_enqueue_js_messages('sub_req_err', $sub_msg);
	ik_enqueue_js_messages('sub_req_lbl', __('Subscribe', 'iii-dictionary'));
?>
<?php if(empty($client)) : ?>
	<?php get_dict_header($header_title, 'purple') ?>
<?php else : ?>
	<?php get_math_header($header_title, 'purple') ?>
<?php endif ?>
<?php get_dict_page_title($header_title, '', $subtitle, $tab_options, $tab_info_url, $is_change, $is_link) ?>

	<form action="<?php echo $form_action ?>" method="post" id="main-form">
		<div class="row">
			<div class="col-xs-12">
				<div class="box2">
					<div id="subscription-fee">
						<span class="icon-gear-purple"></span><span><a href="<?php echo home_url() ?>/?r=manage-subscription#3"><?php _e('Subscription fee:', 'iii-dictionary') ?></a></span><span>$ <?php echo $subscription_fee ?> / <?php _e('month', 'iii-dictionary') ?></span>
					</div>
				</div>
			</div>
			<div class="col-xs-12" style="margin-top: 25px;">
				<div class="box box-purple">
					<table class="table table-striped table-condensed ik-table1 ik-table-break-all text-center">
						<thead>
							<tr>
								<th><?php _e('Content', 'iii-dictionary') ?></th>
								<th class="hidden-xs"><?php _e('No. of Worksheets', 'iii-dictionary') ?></th>
								<th><?php _e('Detail', 'iii-dictionary') ?></th>
								<th><?php _e('Start', 'iii-dictionary') ?></th>
							</tr>
						</thead>
						<tfoot>
							<tr><td colspan="4"><?php echo $pagination ?></td></tr>
						</tfoot>
						<tbody>
							<?php if(!empty($groups->items)) :
								foreach($groups->items as $group) : ?>
									<tr>
										<td><?php echo $group->content ?></td>
										<td class="hidden-xs"><?php echo is_null($group->no_homeworks) ? 0 : $group->no_homeworks ?></td>
										<td><a href="#" class="class-detail-btn">Click</a><div><?php echo $group->detail ?></div></td>
										<td><?php
											if(is_student_in_group(get_current_user_id(), $group->id)) :
												$sat_results = get_sat_class_score($group->id);
												if(is_sat_class_completed($sat_results)) : ?>
													<a href="#" role="button" class="view-score" data-jid="<?php echo $group->id ?>"><?php _e('Completed', 'iii-dictionary') ?></a>
												<?php else : ?>
													<a href="<?php echo home_url() . '/?r=homework-status&amp;gid=' . $group->id ?>" class="links-purprle"><?php _e('Working', 'iii-dictionary') ?></a>
												<?php endif ?>
												<table class="hidden"><tbody>
												<?php foreach($sat_results as $result) : ?>
													<tr>
														<td><?php echo $result->sheet_name ?></td>
														<td><?php echo $result->score ?></td>
														<td><?php echo $result->submitted_on ?></td>
													</tr>
												<?php endforeach ?>
												</tbody></table>
											<?php else : ?>
												<a href="#" role="button" class="start-class-btn" data-jid="<?php echo $group->id ?>"><?php _e('Start', 'iii-dictionary') ?></a>
											<?php endif ?>
										</td>
									</tr>
								<?php endforeach;
								else : ?>
									<tr><td colspan="4"><?php _e('No classes', 'iii-dictionary') ?></td></tr>
							<?php endif ?>
						</tbody>
					</table>
				</div>
			</div>												
		</div>
		<input type="hidden" name="jid" id="jid">
	</form>

<div class="modal fade modal-purple modal-large" id="class-detail-modal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog">
	<div class="modal-content">
	  <div class="modal-header">
		<a href="#" data-dismiss="modal" aria-hidden="true" class="close"></a>
		<h3 class="modal-title" id="myModalLabel"><?php _e('Class Detail', 'iii-dictionary') ?></h3>
	  </div>
	  <div class="modal-body"></div>
	</div>
  </div>
</div>

<div class="modal fade modal-purple modal-large" id="view-score-modal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog">
	<div class="modal-content">
	  <div class="modal-header">
		<a href="#" data-dismiss="modal" aria-hidden="true" class="close"></a>
		<h3 class="modal-title" id="myModalLabel"><?php _e('View Score', 'iii-dictionary') ?></h3>
	  </div>
	  <div class="modal-body">
		<table class="table table-striped table-condensed ik-table1 ik-table-break-all text-center" id="table-score">
			<thead><tr>
					<th><?php _e('Worksheet Name', 'iii-dictionary') ?></th>
					<th><?php _e('Score', 'iii-dictionary') ?></th>
					<th><?php _e('Completed Date', 'iii-dictionary') ?></th>
			</tr></thead>
			<tbody></tbody>
		</table>
	  </div>
	</div>
  </div>
</div>

<div id="require-modal" class="modal fade modal-purple" aria-hidden="true">
	<div class="modal-dialog">
	  <div class="modal-content">
		<div class="modal-header">
			<a href="#" data-dismiss="modal" aria-hidden="true" class="close"></a>
			<h3><?php _e('Subscription Required', 'iii-dictionary') ?></h3>
		</div>
		<div class="modal-body"></div>
		<div class="modal-footer">
			<a href="<?php echo locale_home_url() ?>/?r=manage-subscription#3" class="btn btn-block orange"></a>
		</div>
	  </div>
	</div>
</div>

<script>var annoying = <?php echo $is_sat_class_subscribed ? 'false' : 'true' ?>;</script>

<?php if(empty($client)) : ?>
	<?php get_dict_footer() ?>
<?php else : ?>
	<?php get_math_footer() ?>
<?php endif ?>