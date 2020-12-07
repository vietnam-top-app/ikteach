<?php
	// change request status
	if(isset($_POST['process'])) {
		$data = array(
			'status_id' => TEACHER_REQ_PAIDOUT,
			'processed_by' => get_current_user_id(),
			'processed_on' => date('Y-m-d H:i:s', time())
		);

		if(MWDB::update_payment_request($_POST['cid'], $data)) {
			ik_enqueue_messages('Successfully change request status.', 'success');
			wp_redirect(home_url() . '/?r=payment-requests');
			exit;
		}
		else {
			ik_enqueue_messages('An error occured, cannot change request status.', 'error');
		}
	}

	// page content
	$receiving_methods = MWDB::get_payment_receiving_methods();

	$current_page = max( 1, get_query_var('page'));
	$filter = get_page_filter_session();
	if(empty($filter))
	{
		$filter['items_per_page'] = 25;
		$filter['offset'] = $filter['items_per_page'] * ($current_page - 1);
	}
	else {
		if(isset($_POST['filter']['search']))
		{
			$filter['email'] = $_POST['filter']['email'];
			$filter['method'] = $_POST['filter']['method'];
			$filter['status'] = $_POST['filter']['status'];
		}
		$filter['offset'] = $filter['items_per_page'] * ($current_page - 1);
	}

	set_page_filter_session($filter);
	$requests = MWDB::get_payment_requests($filter, $filter['offset'], $filter['items_per_page']);
	$total_pages = ceil($requests->total / $filter['items_per_page']);

	$pagination = paginate_links( array(
		'format' => '?page=%#%',
		'current' =>  $current_page,
		'total' => $total_pages
	) );

?>
<?php get_dict_header('Payment Requests') ?>
<?php get_dict_page_title('Teacher\' Payment Requests', 'admin-page') ?>

	<form action="<?php echo home_url() ?>/?r=payment-requests" method="post" id="main-form">
		<div class="row">
			<div class="col-xs-12">
				<h2 class="title-border">Requests List</h2>
			</div>
			<div class="col-xs-12">
				<div class="box box-sapphire">
					<div class="row box-header">
						<div class="col-xs-6 col-sm-4">
							<input type="text" name="filter[email]" class="form-control" placeholder="Receiving Email" value="<?php echo $filter['email'] ?>">
						</div>
						<div class="col-xs-6 col-sm-3">
							<select name="filter[method]" class="select-box-it select-sapphire form-control">
								<option value="">-Receiving Method-</option>
								<?php foreach($receiving_methods AS $method) : ?>
									<option value="<?php echo $method->id ?>"<?php echo $filter['method'] == $method->id ? ' selected' : ''?>><?php echo $method->name ?></option>
								<?php endforeach ?>
							</select>
						</div>
						<div class="col-xs-6 col-sm-3">
							<select name="filter[status]" class="select-box-it select-sapphire form-control">
								<option value="">-Request Status-</option>
								<option value="1"<?php echo $filter['status'] == '1' ? ' selected' : ''?>>Pending</option>
								<option value="2"<?php echo $filter['status'] == '2' ? ' selected' : ''?>>Processed</option>
							</select>
						</div>
						<div class="col-xs-6 col-sm-2">
							<button type="submit" class="btn btn-default sky-blue form-control" name="filter[search]">Search</button>
						</div>
					</div>
					<div class="row">
						<div class="col-xs-12">
							<table class="table table-striped table-condensed ik-table1 ik-table-break-all text-center" id="list-sheets">
								<thead>
									<tr>
										<th>Requester</th>
										<th>Receiving Method</th>
										<th>Receiving Email</th>
										<th>Amount</th>
										<th>Status</th>
										<th>Requested On</th>
										<th>Processed On</th>
										<th style="min-width: 70px"></th>
									</tr>
								</thead>
								<tfoot>
									<tr><td colspan="8"><?php echo $pagination ?></td></tr>
								</tfoot>
								<tbody><?php if(empty($requests->items)) : ?>
									<tr><td colspan="8">No requests</td></tr>
								<?php else : foreach($requests->items as $request) : ?>
									<tr<?php echo $request->status_id == TEACHER_REQ_PAIDOUT ? ' class="text-muted"' : '' ?>>
										<td><?php echo $request->requester ?></td>
										<td><?php echo $request->method ?></td>
										<td><?php echo $request->receiving_email ?></td>
										<td>$ <?php echo $request->amount ?></td>
										<td><?php echo $request->status ?></td>
										<td><?php echo ik_date_format($request->requested_on, 'm/d/Y H:i:s') ?></td>
										<td><?php echo ik_date_format($request->processed_on, 'm/d/Y H:i:s') ?></td>
										<td><button type="button" class="btn btn-default btn-block btn-tiny grey process-req" data-req-id="<?php echo $request->id ?>">Process</button></td>
									</tr>
								<?php endforeach; endif ?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>

<div id="process-request-dialog" class="modal fade modal-red-brown">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
            <a href="#" data-dismiss="modal" aria-hidden="true" class="close"></a>
             <h3>Process Request</h3>
        </div>
		<div class="modal-body">
			<div class="row">
				<div class="col-sm-12">
					<div class="form-group">
						<img src="<?php echo get_template_directory_uri() ?>/library/images/icon-warning4.png" alt="Warning">
						Are you sure you want to change this request status to <strong>Processed</strong>?
					</div>
				</div>
				<div class="col-sm-6">
					<div class="form-group">
						<button type="submit" name="process" class="btn btn-block orange"><span class="icon-check"></span>Yes</button>
					</div>
				</div>
				<div class="col-sm-6">
					<div class="form-group">
						<a href="#" data-dismiss="modal" aria-hidden="true" class="btn btn-block grey"><span class="icon-cancel"></span>No</a>
					</div>
				</div>
			</div>
			<input type="hidden" name="cid" id="cid">
		</div>
      </div>
    </div>
</div>

	</form>

<script>
	(function($){
		$(function(){
			$(".process-req").click(function(){
				$("#cid").val($(this).attr("data-req-id"));
				$("#process-request-dialog").modal();
			});
		});
	})(jQuery);
</script>
<?php get_dict_footer() ?>