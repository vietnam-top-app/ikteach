<?php
	$current_user_id = get_current_user_id();

	$gid = isset($_GET['g']) && is_numeric($_GET['g']) ? $_GET['g'] : 0;
	
	$is_math_panel = is_math_panel();
	$_page_title = __('Group Messages', 'iii-dictionary');

	if(!$gid)
	{
		$filter = get_page_filter_session();
		if(empty($filter))
		{
			$filter['orderby'] = 'ordering';
			$filter['order-dir'] = 'asc';
		}
		else {
			if(isset($_REAL_POST['filter']['orderby'])) {
				$filter['orderby'] = $_REAL_POST['filter']['orderby'];
				$filter['order-dir'] = $_REAL_POST['filter']['order-dir'];
			}

			$filter['offset'] = $filter['items_per_page'] * ($current_page - 1);
		}

		set_page_filter_session($filter);
		$groups = MWDB::get_user_group_messageboard($current_user_id, $filter);
	}
	else
	{
		if(isset($_POST['reply'])) {
			$data = array(
				'group_id' => $gid,
				'posted_by' => $current_user_id,
				'message' => $_REAL_POST['message'],
				'posted_on' => date('Y-m-d H:i:s', time())
			);

			if(MWDB::insert_group_message($data)) {
				wp_redirect(locale_home_url() . '/?r=group-messages&g=' . $gid);
				exit;
			}
		}

		$group = MWDB::get_group($gid, 'id');
		$messages = MWDB::get_group_messages($gid);
	}
?>
<?php if(!$is_math_panel) : ?>
	<?php get_dict_header($_page_title) ?>
<?php else : ?>
	<?php get_math_header($_page_title, 'red-brown') ?>
<?php endif ?>
<?php get_dict_page_title($_page_title, '', '', array(), get_info_tab_cloud_url('Popup_info_33.jpg')) ?>

	<div class="row">
		<div class="col-md-12">
			<form method="post" action="<?php echo locale_home_url() ?>/?r=group-messages<?php echo $gid ? '&amp;g=' . $gid : '' ?>" id="main-form">
				<div class="row">
					<?php if(!$gid) : ?>
						<div class="col-sm-12">
							<h2 class="title-border"><?php _e('Group Messages', 'iii-dictionary') ?></h2>
						</div>
						<div class="col-sm-12">
							<div class="box">
								<div class="row">
									<div class="col-sm-12">
										<div class="scroll-list2" style="max-height: 600px">
											<table class="table table-striped table-condensed ik-table1 text-center">
												<thead>
													<tr>
														<th>
															<a href="#" class="sortable<?php echo $filter['orderby'] == 'g.name' ? ' ' . $filter['order-dir'] : '' ?>" data-sort-by="g.name"><?php _e('Group', 'iii-dictionary') ?> <span class="sorting-indicator"></span></a>
														</th>
														<th class="hidden-xs">
															<a href="#" class="sortable<?php echo $filter['orderby'] == 'posted_on' ? ' ' . $filter['order-dir'] : '' ?>" data-sort-by="posted_on"><?php _e('Last Post', 'iii-dictionary') ?> <span class="sorting-indicator"></span></a>
														</th>
														<th class="hidden-xs" style="width: 80px">
															<a href="#" class="sortable<?php echo $filter['orderby'] == 'replies' ? ' ' . $filter['order-dir'] : '' ?>" data-sort-by="replies"><?php _e('Replies', 'iii-dictionary') ?> <span class="sorting-indicator"></span></a>
														</th>
														<th style="width: 120px"></th>
													</tr>
												</thead>
												<tfoot>
													<tr><td colspan="5"><?php echo $pagination ?></td></tr>
												</tfoot>
												<tbody><?php if(empty($groups)) : ?>
														<tr><td colspan="5"><?php _e('You haven\'t joined any group yet.', 'iii-dictionary') ?></td></tr>
													<?php else :
														foreach($groups as $item) : ?>
															<tr>
																<td><a href="<?php echo locale_home_url() . '/?r=group-messages&amp;g=' . $item->id ?>"><?php echo $item->group_name ?></a></td>
																<td class="hidden-xs"><?php
																	if(empty($item->posted_on)) :
																		_e('No post', 'iii-dictionary');
																	else :
																		echo ik_date_format($item->posted_on, 'M d, Y H:i') ?> <br> <?php _e('by', 'iii-dictionary') ?> <a href="#"><?php echo $item->poster ?></a><?php
																	endif
																?></td>
																<td class="hidden-xs"><?php echo $item->replies ?></td>
																<td>
																	<button type="button" class="btn btn-default btn-block btn-tiny grey members-list"><?php _e('Members List', 'iii-dictionary') ?></button>
																	<table class="hidden"><tbody><?php $members = MWDB::get_group_members($item->id);
																		foreach($members as $member) :
																			?><tr><?php
																				?><td><?php echo $member->display_name ?></td><?php
																				?><td><?php echo ik_date_format($member->joined_date, 'M d, Y') ?></td><?php
																				?><td><?php 
																					if($current_user_id != $member->ID) :
																						?><a href="<?php echo locale_home_url() . '/?r=private-messages&view=newpm&amp;recipient=' . $member->ID ?>" title="Send private message to <?php echo $member->display_name ?>"><?php _e('Send PM', 'iii-dictionary') ?></a><?php
																					endif
																				?></td><?php
																			?></tr><?php
																		endforeach ?>
																	</tbody></table>
																</td>
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

						<div class="col-sm-12">
							<h2 class="title-border"><?php _e('Group', 'iii-dictionary') ?> <?php echo $group->name ?></h2>
						</div>
						<div class="col-sm-3 col-sm-offset-5">
							<div class="form-group">
								<button type="button" class="btn btn-default btn-block grey form-control members-list"><?php _e('Members List', 'iii-dictionary') ?></button>
								<table class="hidden"><tbody><?php $members = MWDB::get_group_members($group->id);
									foreach($members as $member) : ?>
										<tr>
											<td><?php echo $member->display_name ?></td>
											<td><?php echo $member->joined_date ?></td>
											<td><a href="<?php echo locale_home_url() . '/?r=private-messages&view=newpm&amp;recipient=' . $member->ID ?>"><?php _e('Send PM', 'iii-dictionary') ?></a></td>
										</tr>
								<?php endforeach ?>
								</tbody></table>
							</div>
						</div>
						<div class="col-sm-4">
							<div class="form-group">
								<a href="<?php echo locale_home_url() ?>/?r=group-messages" class="btn btn-default grey form-control"><span class="icon-goback"></span><?php _e('Back', 'iii-dictionary') ?></a>
							</div>
						</div>
						<div class="col-sm-12">
							<div class="posts-list">
								<?php if(empty($messages)) : ?>
									<div class="box box-red post-block">
										<p class="text-center"><?php _e('No post', 'iii-dictionary') ?></p>
									</div>
								<?php else :
									foreach($messages as $key => $message) : ?>
										<div class="box box-red post-block">
											<div class="post-header">
												<span class="post-num"><?php echo $key + 1 ?></span>
												<span><?php _e('Name:', 'iii-dictionary') ?> <span class="post-author"><?php echo $message->poster ?></span></span>
												<span class="post-date"><?php echo ik_date_format($message->posted_on, 'M d, Y H:i') ?></span>
												<?php if($current_user_id != $message->posted_by) : ?>
													<span><a href="<?php echo locale_home_url() . '/?r=private-messages&view=newpm&amp;recipient=' . $message->posted_by ?>" title="<?php printf(__('Send private message to %s', 'iii-dictionary'), $message->poster) ?>"><?php _e('Send PM', 'iii-dictionary') ?></a></span>
												<?php endif ?>
											</div>
											<div class="post-content"><?php echo $message->message ?></div>
										</div>
								<?php endforeach;
								endif ?>
							</div>
							<div class="post-form">
								<div class="form-group">
									<label for="group-details"><?php _e('Write message', 'iii-dictionary') ?></label>
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
									
									wp_editor('', 'message', $editor_settings); ?>
								</div>
								<div class="form-group">
									<button type="submit" name="reply" class="btn btn-default orange post-reply"><span class="icon-save"></span><?php _e('Reply', 'iii-dictionary') ?></button>
								</div>
							</div>
						</div>

					<?php endif ?>

				</div>
		  </form>
		</div>
	</div>

<div id="members-list-dialog" class="modal fade modal-red-brown" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
            <a href="#" data-dismiss="modal" aria-hidden="true" class="close"></a>
             <h3><?php _e('Members List', 'iii-dictionary') ?></h3>
        </div>
        <div class="modal-body">
			<table id="members-list" class="table table-striped table-condensed ik-table1 text-center">
				<thead><tr>
					<th><?php _e('Account Name', 'iii-dictionary') ?></th>
					<th><?php _e('Joined Date', 'iii-dictionary') ?></th>
					<th></th>
				</tr></thead>
				<tbody></tbody>
			</table>
        </div>
      </div>
    </div>
</div>
<script>
	(function($){
		$(function(){
			$("button.members-list").click(function(){
				$("#members-list").find("tbody").html($(this).next().find("tbody").html());
				$("#members-list-dialog").modal();
			});
		});
	})(jQuery);
</script>
</script>
<?php if(!$is_math_panel) : ?>
	<?php get_dict_footer() ?>
<?php else : ?>
	<?php get_math_footer() ?>
<?php endif ?>