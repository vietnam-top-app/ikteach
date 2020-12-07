<?php
	$view = isset($_GET['view']) ? $_GET['view'] : 'list';
	$mid = empty($_GET['mid']) ? 0 : $_GET['mid'];
	$sid = empty($_GET['sid']) ? 0 : $_GET['sid'];
	$message_type = !empty($_GET['type']) ? $_GET['type'] : 'private';
	$is_math_panel = is_math_panel();
	$page_title = __('Private Messages', 'iii-dictionary');
	$current_user = wp_get_current_user();
	$action_url = locale_home_url() . '/?r=private-messages';
	$action_url .= $view == 'newpm' ? '&view=newpm' : '';
	$action_url .= $mid ? '&mid=' . $mid : '';

	if($view == 'list') {
		$message_status = MWDB::get_message_status();
		$current_page = max( 1, get_query_var('page'));
		$filter = get_page_filter_session();
		if(empty($filter))
		{
			$filter['type'] = 'received';
			$filter['user_id'] = $current_user->ID;
			$filter['items_per_page'] = 30;
			$filter['offset'] = $filter['items_per_page'] * ($current_page - 1);
		}
		else {
			if(isset($_POST['filter']['search']))
			{
				$filter['status'] = $_POST['filter']['status'];
				$filter['type'] = $_POST['filter']['type'];
				if($_POST['filter']['type'] == 'sent') {
					$filter['status'] = '';
				}
			}

			$filter['offset'] = $filter['items_per_page'] * ($current_page - 1);
		}

		set_page_filter_session($filter);
		$messages = MWDB::get_private_messages($filter['type'], $filter, $filter['offset'], $filter['items_per_page']);
		$total_pages = ceil($messages->total / $filter['items_per_page']);

		$pagination = paginate_links(array(
			'format' => '?page=%#%',
			'current' =>  $current_page,
			'total' => $total_pages
		));
	}
	else
	{
		if($message_type == 'feedback') {
			$recipient = 'Support';
			$page_title = __('Feedback to Support', 'iii-dictionary');
			$recipient_id = 0;
		}
		else {
			if(!empty($_GET['recipient'])) {
				$recipient_id = $_GET['recipient'];

				$recipient_obj = get_user_by('id', $recipient_id);
				if($recipient_obj) {
					$recipient = $recipient_obj->user_login;
				}
			}
		}

		// open received message
		if($mid) {
			$cur_message = MWDB::get_received_private_message($mid);

			if($cur_message->status == MESSAGE_STATUS_UNREAD) {
				// if this is an unread message, set it to read
				ik_update_message_status($mid, MESSAGE_STATUS_READ);
			}

			if(!$cur_message->sender_id) {
				$recipient = 'Support';
				$recipient_id = 0;
			}
			else {
				$recipient = $cur_message->sender_login;
				$recipient_id = $cur_message->sender_id;
			}

			$subject = $cur_message->subject;
			$message = '<blockquote><p class="quoted-from">' . $recipient . ' wrote:</p>' . $cur_message->message . '</blockquote><p></p>';
		}

		// open sent message
		if($sid) {
			$cur_message = MWDB::get_sent_private_message($sid);

			if($cur_message->status == MESSAGE_STATUS_UNREAD) {
				// if this is an unread message, set it to read
				ik_update_message_status($mid, MESSAGE_STATUS_READ);
			}

			if(!$cur_message->recipient_id) {
				$recipient = 'Support';
				$recipient_id = 0;
			}
			else {
				$recipient = $cur_message->recipient_login;
				$recipient_id = $cur_message->recipient_id;
			}

			$subject = $cur_message->subject;
			$message = $cur_message->message;
		}

		if(isset($_POST['submit-message']))
		{
			$form_valid = true;
			$recipient_id = $_REAL_POST['recipient-id'];
			$reply_prefix = $mid && strpos($_REAL_POST['subject'], 'RE: ') === false ? 'RE: ' : '';
			$subject = $_REAL_POST['subject'];
			$message = $_REAL_POST['message'];

			if(!wp_check_password($_POST['sender-password'], $current_user->user_pass, $current_user->ID)) {
				ik_enqueue_messages(__('Password not match.', 'iii-dictionary'), 'error');
				$form_valid = false;
			}

			if($recipient_id == '') {
				$recipient = $_POST['recipient'];
				$recipient_obj = get_user_by('login', $recipient);
				if($recipient_obj) {
					$recipient_id = $recipient_obj->ID;
				}
				else {
					ik_enqueue_messages(__('Invalid recipient.', 'iii-dictionary'), 'error');
					$form_valid = false;
				}
			}

			if(empty($subject)) {
				ik_enqueue_messages(__('You must enter a subject.', 'iii-dictionary'), 'error');
				$form_valid = false;
			}

			if(empty($message)) {
				ik_enqueue_messages(__('Please complete message fields.', 'iii-dictionary'), 'error');
				$form_valid = false;
			}

			if($form_valid && ik_send_private_message($recipient_id, $reply_prefix . $subject, $message)) {
				wp_redirect(locale_home_url() . '/?r=private-messages');
				exit;
			}
		}

		// user view received message
		if($mid) {
			$header_title = __('Received Message', 'iii-dictionary');
			$send_to_lbl = __('Sender Username', 'iii-dictionary');
		}
		// user view sent message
		else if($sid) {
			$header_title = __('Sent Message', 'iii-dictionary');
			$send_to_lbl = __('Recipient Username', 'iii-dictionary');
		}
		// user send new message
		else {
			$header_title = __('New Message', 'iii-dictionary');
			$send_to_lbl = __('Recipient Username', 'iii-dictionary');
		}
	}

	$page_tab_info = get_info_tab_cloud_url('Popup_info_34.jpg');
	if($message_type == 'feedback') {
		$page_tab_info = get_info_tab_cloud_url('Popup_info_35.jpg');
	}
?>
<?php if(!$is_math_panel) : ?>
	<?php get_dict_header($page_title) ?>
<?php else : ?>
	<?php get_math_header($page_title, 'red-brown') ?>
<?php endif ?>
<?php get_dict_page_title($page_title, '', '', array(), $page_tab_info) ?>

	<form method="post" action="<?php echo htmlspecialchars($action_url) ?>" id="main-form">
		<?php if($view == 'list') : ?>
			<div class="row">
				<div class="col-sm-12">
					<h2 class="title-border"><?php _e('List Messages', 'iii-dictionary') ?></h2>
				</div>
				<?php if($view == 'list') : ?>
					<div class="col-sm-4">
						<div class="form-group">
							<a href="<?php echo locale_home_url() ?>/?r=private-messages&amp;view=newpm" class="btn btn-default grey form-control"><?php _e('New Message', 'iii-dictionary') ?></a>
						</div>
					</div>
				<?php endif ?>
				<div class="col-sm-12">
					<div class="box">
						<div class="row box-header">
							<div class="col-sm-3">
								<select name="filter[type]" class="select-box-it form-control">
									<option value="received"<?php echo $filter['type'] == 'received' ? ' selected' : ''?>><?php _e('Received Message', 'iii-dictionary') ?></option>
									<option value="sent"<?php echo $filter['type'] == 'sent' ? ' selected' : ''?>><?php _e('Sent Messages', 'iii-dictionary') ?></option>
								</select>
							</div>
							<div class="col-sm-3">
								<?php if($filter['type'] == 'received') : ?>
									<div class="form-group">
										<select name="filter[status]" id="filter-status" class="select-box-it form-control">
											<option value="">--<?php _e('Status', 'iii-dictionary') ?>--</option>
											<option value="1"<?php echo '1' == $filter['status'] ? ' selected' : '' ?>><?php _e('Read', 'iii-dictionary') ?></option>
											<option value="2"<?php echo '2' == $filter['status'] ? ' selected' : '' ?>><?php _e('Unread', 'iii-dictionary') ?></option>
										</select>
									</div>
								<?php endif ?>
							</div>
							<div class="col-sm-3 col-sm-offset-3">
								<button type="submit" class="btn btn-default btn-block grey form-control" name="filter[search]"><?php _e('Search', 'iii-dictionary') ?></button>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-12">
								<div class="scroll-list2" style="max-height: 600px">
								<?php 
									if($filter['type'] == 'received') {
										$first_col = __('Sender', 'iii-dictionary');
										$third_col = __('Received Date', 'iii-dictionary');
										$date_type = 'received_on';
										$view_type = 'mid';
									}
									else {
										$first_col = __('Recipient', 'iii-dictionary');
										$third_col = __('Sent Date', 'iii-dictionary');
										$date_type = 'sent_on';
										$view_type = 'sid';
									} ?>
									<table class="table table-striped table-condensed ik-table1 text-center">
										<thead>
											<tr>
												<th><?php echo $first_col ?></th>
												<th><?php _e('Subject', 'iii-dictionary') ?></th>
												<th><?php echo $third_col ?></th>
												<th></th>
											</tr>
										</thead>
										<tfoot>
											<tr><td colspan="4"><?php echo $pagination ?></td></tr>
										</tfoot>
										<tbody>
											<?php if(empty($messages->items)) : ?>
												<tr><td colspan="4">
													<?php if($filter['type'] == 'received') : ?>
														<?php _e('You haven\'t received any message.', 'iii-dictionary') ?>
													<?php else : ?>
														<?php _e('You haven\'t sent any message.', 'iii-dictionary') ?>
													<?php endif ?>
												</td></tr>
											<?php else :
												foreach($messages->items as $item) : ?>
													<tr<?php echo $filter['type'] == 'received' && $item->status == MESSAGE_STATUS_UNREAD ? ' class="message-unread"' : '' ?>>
														<td><?php 
															if($item->system_message || ($filter['type'] == 'sent' && !$item->recipient_id)) :
																?>Support<?php
															else :
																echo $filter['type'] == 'received' ? $item->sender_login : $item->recipient_login;
															endif ?>
														</td>
														<td><?php echo ik_cut_str($item->subject, 55) ?></td>
														<td><?php echo ik_date_format($item->$date_type, 'M d, Y H:i') ?></td>
														<td><a href="<?php echo home_url() . '/?r=private-messages&amp;view=newpm&amp;' . $view_type . '=' . $item->id ?>" class="btn btn-default btn-block btn-tiny grey"><?php _e('Open', 'iii-dictionary') ?></a></td>
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

		<?php if($view == 'newpm') : ?>
			<div class="row">
				<?php if($message_type == 'feedback') : ?>
					<div class="col-sm-12">
						<p style="font-size: 20px; color: #fff"><?php _e('If you have any question, or found any malfunction at this site, please let us know.', 'iii-dictionary') ?></p>
					</div>
				<?php endif ?>
				<div class="col-sm-12">
					<h2 class="title-border"><?php echo $header_title ?></h2>
				</div>

				<div class="col-sm-4">
					<div class="form-group">
						<a href="<?php echo locale_home_url() ?>/?r=private-messages" class="btn btn-default btn-block grey"><span class="icon-goback"></span><?php _e('Go back', 'iii-dictionary') ?></a>
					</div>
				</div>

				<div class="clearfix"></div>

				<div class="col-sm-6">
					<div class="form-group">
						<label for="sender-email"><?php _e('Your E-mail address', 'iii-dictionary') ?></label>
						<input type="text" class="form-control" id="sender-email" name="sender-email" value="<?php echo $current_user->user_email ?>" disabled>
					</div>     
				</div>
				<div class="col-sm-6">
					<div class="form-group">
						<label for="sender-password"><?php _e('Your Password', 'iii-dictionary') ?></label>
						<input type="password" class="form-control" id="sender-password" name="sender-password" value="">
					</div>     
				</div>

				<div class="clearfix"></div>

				<div class="col-sm-6">
					<div class="form-group">
						<label for="recipient"><?php echo $send_to_lbl ?></label>
						<input type="text" class="form-control" id="recipient" name="recipient" value="<?php echo $recipient ?>"<?php echo $mid || $message_type == 'feedback' ? ' disabled' : '' ?>>
					</div>     
				</div>

				<div class="col-sm-12">
					<div class="form-group">
						<label for="subject"><?php _e('Subject', 'iii-dictionary') ?></label>
						<input type="text" class="form-control" id="subject" name="subject" maxlength="100" value="<?php echo $subject ?>">
					</div>     
				</div>
				<div class="col-sm-12">
					<div class="form-group">
						<label><?php _e('Message', 'iii-dictionary') ?></label>
						<?php if(!empty($cur_message->message)) : ?>
							<div class="box box-red message-body"><?php echo $cur_message->message ?></div>
						<?php endif ?>
					</div>
				</div>
				<div class="col-sm-12">
					<?php 
						$editor_settings = array(
							'wpautop' => false,
							'media_buttons' => false,
							'quicktags' => false,
							'textarea_rows' => 10,
							'tinymce' => array(
								'toolbar1' => 'bold,italic,strikethrough,image,bullist,numlist,blockquote,hr,alignleft,aligncenter,alignright,spellchecker,fullscreen,wp_adv'
							)
						); 
					?>
					<div class="form-group">
						<?php wp_editor($message, 'message', $editor_settings); ?>
					</div>
				</div>

				<?php if($view == 'newpm' && !$sid) : ?>
					<div class="col-sm-4">
						<div class="form-group">
							<button type="submit" name="submit-message" class="btn btn-default btn-block orange"><span class="icon-save"></span><?php echo $mid ? __('Reply', 'iii-dictionary') : __('Send', 'iii-dictionary') ?></button>
						</div>
					</div>
				<?php endif ?>
			</div>
			<input type="hidden" name="recipient-id" value="<?php echo $recipient_id ?>">
		<?php endif ?>

  </form>
</script>
<?php if(!$is_math_panel) : ?>
	<?php get_dict_footer() ?>
<?php else : ?>
	<?php get_math_footer() ?>
<?php endif ?>