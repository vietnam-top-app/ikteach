<?php
	$editor_settings = array(
		'wpautop' => false,
		'media_buttons' => false,
		'quicktags' => false,
		'textarea_rows' => 300,
		'tinymce' => array(
			'toolbar1' => 'bold,italic,strikethrough,image,bullist,numlist,blockquote,hr,alignleft,aligncenter,alignright,spellchecker,fullscreen,wp_adv'
		)
	);
	// reply a feedback
	if(isset($_POST['reply-feedback']))
	{
		$reply_prefix = strpos($_REAL_POST['reply-subject'], 'RE: ') === false ? 'RE: ' : '';
		
		$subject = $reply_prefix . $_REAL_POST['reply-subject'];

		$recipient = $_POST['recipient-id'];
		$message = $_REAL_POST['message'];
		if(ik_send_private_message($recipient, $subject, $message, true)) {
			// update moderation status to replied
			ik_update_message_mod_status($_POST['reply-id'], MESSAGE_MOD_STATUS_REPLIED);

			wp_redirect(home_url() . '/?r=feedback-from-users');
			exit;
		}
	}
	if(isset($_POST['send-to-all']))
	{
		$recipient_id = RECIPIENT_MESSAGE_FOR_ALL;
		$subject = SUBJECT_MESSAGE_FOR_ALL;
		$message = $_REAL_POST['message-to-all'];
		$display = !is_null($_POST['display-at-login']) ? 2 : 1;
		if(ik_send_private_message($recipient_id, $subject, $message, true ,$display)) {
			wp_redirect(home_url() . '/?r=feedback-from-users');
			exit;
		}
		
	}

	// admin want to mark messages as replied
	if(isset($_POST['mark-done'])) {
		ik_update_message_mod_status($_POST['mid'], MESSAGE_MOD_STATUS_REPLIED);

		ik_enqueue_messages(count($_POST['mid']) . ' messages have been marked as Replied.', 'success');

		wp_redirect(home_url() . '/?r=feedback-from-users');
		exit;
	}

	// page content
	$mod_status = MWDB::get_message_mod_status();
	$current_page = max( 1, get_query_var('page'));
	$filter = get_page_filter_session();
	if(empty($filter))
	{
		$filter['user_id'] = 0;
		$filter['type'] = 'received';
		$filter['sender_id'] = get_current_user_id();
		$filter['items_per_page'] = 25;
		$filter['offset'] = $filter['items_per_page'] * ($current_page - 1);
	}
	else {
		if(isset($_POST['filter']['search']))
		{
			$filter['sender-email'] = $_REAL_POST['filter']['sender-email'];
			$filter['mod-status'] = $_POST['filter']['mod-status'];
			$filter['type'] = $_POST['filter']['type'];
			if($_POST['filter']['type'] == 'sent') {
				$filter['mod-status'] = '';
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
?>
<?php get_dict_header('Feedback From Users') ?>
<?php get_dict_page_title('Feedback From Users', 'admin-page') ?>

	<form action="<?php echo home_url() ?>/?r=feedback-from-users" method="post" id="main-form">
		<div class="row">
			<div class="col-sm-12">
				<div class="title-border row">
					<div class="col-sm-6">
						<h2>List Feedback</h2>
					</div>
					<div class="col-sm-6">
							<a href="#" class="omg_link-format" id="send-message-to-all-a">Send a Message to Everybody</a>
					</div>
				</div>
			</div>
			<div class="col-sm-12">
				<div class="box box-sapphire">
					<div class="row box-header">
						<div class="col-sm-3 col-sm-offset-9">
							<div class="form-group">
								<button name="mark-done" type="submit" class="btn btn-default grey form-control">Mark as Done</button>
							</div>
						</div>
						<div class="col-sm-4">
							<input type="text" id="filter-email" name="filter[sender-email]" class="form-control" placeholder="Email" value="<?php echo $filter['sender-email'] ?>">
						</div>
						<div class="col-sm-2">
							<?php if($filter['type'] == 'received') : ?>
								<select name="filter[mod-status]" class="select-box-it select-sapphire form-control">
									<option value="">--Status--</option>
									<?php foreach($mod_status as $item) : ?>
										<option value="<?php echo $item->id ?>"<?php echo $filter['mod-status'] == $item->id ? ' selected' : ''?>><?php echo $item->name ?></option>
									<?php endforeach ?>
								</select>
							<?php endif ?>
						</div>
						<div class="col-sm-3">
							<select name="filter[type]" class="select-box-it select-sapphire form-control">
								<option value="received"<?php echo $filter['type'] == 'received' ? ' selected' : ''?>>Received Message</option>
								<option value="sent"<?php echo $filter['type'] == 'sent' ? ' selected' : ''?>>Sent Messages</option>
							</select>
						</div>
						<div class="col-sm-3">
							<button type="submit" id="search-btn" class="btn btn-default btn-block sky-blue form-control" name="filter[search]">Search</button>
						</div>
					</div>
					<div class="row">
						<div class="col-xs-12">
							<div class="scroll-list2" style="max-height: 600px">
								<table class="table table-striped table-condensed ik-table1 text-center" id="list-sheets">
									<thead>
										<tr>
											<th><input type="checkbox" class="check-all" data-name="mid[]"></th>
											<th>Status</th>
											<th><?php echo $filter['type'] == 'received' ? 'Sender' : 'Recipient' ?></th>
											<th>Subject</th>
											<th><?php echo $filter['type'] == 'received' ? 'Received' : 'Sent' ?> Date</th>
											<th></th>
										</tr>
									</thead>
									<tfoot>
										<tr><td colspan="6"><?php echo $pagination ?></td></tr>
									</tfoot>
									<tbody><?php if(empty($messages->items)) : ?>
										<tr><td colspan="6">No feedback</td></tr>
										<?php else: foreach($messages->items as $message) : ?>
											<tr<?php echo $message->moderation_status == MESSAGE_MOD_STATUS_REPLIED ? ' class="text-muted"' : '' ?>>
												<td><input type="checkbox" name="mid[]" value="<?php echo $message->id ?>"></td>
												<td><?php echo $message->mod_status ?></td>
												<td><?php echo $filter['type'] == 'received' ? $message->sender_login : $message->recipient_login ?></td>
												<td><?php echo ik_cut_str($message->subject, 50) ?></td>
												<td><?php $date = $filter['type'] == 'received' ? $message->received_on : $message->sent_on;
													echo ik_date_format($date, 'H:i M d, Y') ?></td>
												<td>
													<button type="button" data-subject="<?php echo $message->subject ?>" data-email="<?php echo $filter['type'] == 'sent' ? 'Support' : $message->sender_login ?>" data-reply-id="<?php echo $message->id ?>" data-recipient-id="<?php echo $message->sender_id ?>" class="btn btn-default btn-block btn-tiny grey view-msg">Detail</button>
													<div class="hidden"><?php 
														$msg = $message->message;
														if($filter['type'] == 'received') {
															$msg = '<blockquote><p class="quoted-from">' . $message->sender_login . ' wrote:</p>' . $message->message . '</blockquote>';
														}
														echo $msg ?></div>
												</td>
											</tr>
										<?php endforeach; endif ?>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

<div class="modal fade modal-red-brown modal-large "  id="reply-feedback-modal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog">
	<div class="modal-content">
	  <div class="modal-header">
		<a href="#" data-dismiss="modal" aria-hidden="true" class="close"></a>
		<h3 class="modal-title">Reply Feedback</h3>
	  </div>
	  <div class="modal-body">
		<?php if($filter['type'] == 'received') : ?>
			<div class="form-group">
				Message from <strong id="message-from"></strong>. Add your message and send it back.
			</div>
		<?php endif ?>
		<div class="form-group">
			<label>Subject</label>
			<div id="message-subject"></div>
		</div>
		<div class="form-group">
			<label>Message</label>
			<?php wp_editor('', 'message', $editor_settings); ?>
		</div>
		<?php if($filter['type'] == 'received') : ?>
			<div class="row">
				<div class="col-sm-6">
					<button type="submit" name="reply-feedback" class="btn btn-block orange"><span class="icon-check"></span> Reply</button>
				</div>
				<div class="col-sm-6">
					<a href="#" data-dismiss="modal" aria-hidden="true" class="btn btn-block grey"><span class="icon-cancel"></span> Cancel</a>
				</div>
			</div>
		<?php endif ?>
		<input type="hidden" name="reply-id" id="reply-id">
		<input type="hidden" name="reply-subject" id="reply-subject">
		<input type="hidden" name="recipient-id" id="recipient-id">
	  </div>
	</div>
  </div>
</div>

<div class="modal fade modal-large modal-red-brown" id="send-message-to-all-modal" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header omg_modal-add-div-right">
				<div class="row">
					<div class="col-sm-8">
						<h3 class="modal-title">A Message to Everybody</h3>
					</div>
					<div class="col-sm-4">
						<a href="#" data-dismiss="modal" aria-hidden="true" class="close"></a>
						<input type="checkbox" id="display-at-login" name="display-at-login" />
						<label for="display-at-login">Display at Login</label>
					</div>
				</div>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<?php 
					$editor_settings_new = array(
						'wpautop' => false,
						'media_buttons' => false,
						'quicktags' => false,
						'textarea_rows' => 100,
						'tinymce' => array(
							'toolbar1' => 'bold,italic,strikethrough,image,bullist,numlist,blockquote,hr,alignleft,aligncenter,alignright,spellchecker,fullscreen,wp_adv'
						)
					);
					wp_editor('', 'message-to-all', $editor_settings_new); ?>
				</div>
				<div class="row">
					<div class="col-sm-6">
						<button type="submit" name="send-to-all" class="btn btn-block orange" id="send-to-all-send"><span class="icon-check"></span> Send</button>
					</div>
					<div class="col-sm-6">
						<a href="#" data-dismiss="modal" aria-hidden="true" class="btn btn-block grey" id="send-to-all-cancel"><span class="icon-cancel"></span> Cancel</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
	</form>
<div id="process-message-modal" class="modal modal-large fade modal-red-brown" aria-hidden="true" data-backdrop="static">
	<div class="modal-dialog">
	  <div class="modal-content">
		<div class="modal-header">
			<div class="three-quarters-loader "><?php _e('Loading...', 'iii-dictionary') ?></div>
		</div>
		<div class="modal-body">
			<p><?php _e('Please don\'t close the window until it is completed.', 'iii-dictionary') ?></p>
		</div>ack
	  </div>
	</div>
</div>
<style type="text/css">
@media (min-width: 320px){
	iframe#message_ifr, iframe#message-to-all_ifr{height: 200px !important}
}	
@media (min-width: 768px){
	iframe#message_ifr, iframe#message-to-all_ifr{height: 230px !important}
}	
@media (min-width: 980px){
	iframe#message_ifr, iframe#message-to-all_ifr{height: 250px !important}
}

</style>
<script>
	  (function($) {
		$rp_modal = $("#reply-feedback-modal");
		$rp_modal.draggable({
			handle: '.modal-header'
		});
		
	  })(jQuery);
</script>
<?php get_dict_footer() ?>