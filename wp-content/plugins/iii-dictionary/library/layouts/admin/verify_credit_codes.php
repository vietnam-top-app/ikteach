<?php
	$c = isset($_GET['c']) ? $_GET['c'] : '';

	if($c != '')
	{
		$code = MWDB::get_credit_code($c);
	}

	if(isset($_POST['void']))
	{
		MWDB::disable_credit_code($_POST['credit-code']);

		wp_redirect( home_url() . '/?r=verify-credit-codes' );
		exit;
	}
	
	if(isset($_POST['export'])) {
		$codes = MWDB::get_credit_codes(array('items_per_page' => 99999999, 'offset' => 0));
		$file_name = date('mdY_Hms', time()) . '.csv';
		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename=' . $file_name);
		$fp = fopen('php://output', 'w');
		fputcsv($fp, array('Encoded code', 'Type', 'Created by', 'Created on', 'Status'));
		foreach($codes->list as $code) {
			if($code->active) {
				if(!empty($code->activated_by)) {
					if($code->expired_on < date('Y-m-d', time())) { 
						$status = 'Expired';
					}else{ 
						$status = 'Inused';
					}
				}else {
					$status = 'Not used';
				}
			}else {
				$status = 'Inactive';
			}
			fputcsv($fp, array('"'. $code->encoded_code . '"', $code->type, $code->display_name, $code->created_on, $status));
		} 
		fclose($fp);
		exit;
	}
	// page content	
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
			$filter['search-value'] = $_POST['filter']['search-value'];
			$filter['type'] = $_POST['filter']['type'];
			$filter['status'] = $_POST['filter']['status'];
		}
		$filter['offset'] = $filter['items_per_page'] * ($current_page - 1);
	}

	set_page_filter_session($filter);
	$codes = MWDB::get_credit_codes($filter);
	$total_pages = ceil($codes->total / $filter['items_per_page']);
	
	$pagination = paginate_links( array(
		'format' => '?page=%#%',
		'current' =>  $current_page,
		'total' => $total_pages
	) );
	
	
?>
<?php get_dict_header('Admin Code Verifier') ?>
<?php get_dict_page_title('Admin Code Verifier', 'admin-page') ?>

	<form method="post" action="<?php echo home_url() ?>/?r=verify-credit-codes<?php echo $current_page > 1 ? '&amp;page=' . $current_page : '' ?>">
		<div class="row">
			<div class="col-sm-6">
				<div class="form-group">
					<label for="credit-code">Credit code</label>
					<div class="input-text-btn-wrapper">
						<input type="text" class="form-control" id="credit-code" name="credit-code" value="<?php echo $c ?>">
						<span class="icon-goto btn-inside" id="verify" title="Decode"></span>
					</div>
				</div>					
			</div>
			<div class="col-sm-6">
				<div class="form-group">
					<label>Decoded number</label>
					<p class="box"><?php echo is_null($code->original_code) ? 'N/A' : $code->original_code ?></p>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-6">
				<div class="row">
					<div class="col-xs-12">
						<div class="form-group">
							<label for="teacher-sub">Number of months for teacher's subscription</label>
							<input type="text" class="form-control" id="teacher-sub" name="teacher-sub" value="<?php echo $code->no_of_months_teacher_tool == 9 ? 12 : $code->no_of_months_teacher_tool ?>" readonly>
						</div>					
					</div>
					<div class="col-xs-12">
						<div class="form-group">
							<label for="num-of-students">Number of students allowed in this subscription</label>
							<input type="text" class="form-control" id="num-of-students" name="num-of-students" value="<?php echo $code->no_of_students ?>" readonly>
						</div>
					</div>
				</div>
				
			</div>
			<div class="col-md-6">												
				<div class="row">
					<div class="col-sm-12">
						<div class="form-group">
							<label for="sel-dictionary">Type of dictionary</label>																
							<?php MWHtml::select_dictionaries($code->dictionary_id, true, 'dictionary_id') ?>
						</div>
					</div>
					<div class="col-sm-12">
						<div class="form-group">
							<label for="dictionary-sub">Number of months dictionary subscription</label>
							<input type="text" class="form-control" id="dictionary-sub" name="dictionary-sub" value="<?php echo $code->no_of_months_dictionary ?>" readonly>
						</div>					
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-6">
				<div class="form-group">
					<label>Code used by</label>
					<p class="box"><?php echo is_null($code->display_name) ? 'N/A' : $code->display_name ?></p>
				</div>
			</div>
			<div class="col-sm-6">
				<div class="form-group">
					<label>Code used on</label>
					<p class="box"><?php echo is_null($code->activated_on) ? 'N/A' : $code->activated_on ?></p>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-6">
				<div class="form-group">
				<label>&nbsp;</label>
					<button type="submit" class="btn btn-default btn-block orange medium" name="void"><span class="icon-modify"></span>Void</button>
				</div>
			</div>
			<div class="col-sm-6">
				<div class="form-group">
					<label>&nbsp;</label>
					<a href="<?php echo home_url() ?>/?r=create-credit-codes&amp;s_t=<?php echo $code->typeid ?>&amp;t_m=<?php echo $code->no_of_months_teacher_tool ?>&amp;t_s=<?php echo $code->no_of_students ?>&amp;d=<?php echo $code->dictionary_id ?>&amp;d_m=<?php echo $code->no_of_months_dictionary ?>" class="btn btn-default btn-block orange medium"><span class="icon-issue"></span>Issue the replacement code</a>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-xs-12">
				<div class="box box-sapphire">
					<div class="row box-header">
						<div class="col-xs-6">
							<h3>Credit codes List</h3>
						</div>
						<div class="col-xs-6 add-col"><input type="submit" name="export" class="add-permalink" value="Export the list" /></div>
						<div class="col-xs-12">
							<div class="row search-tools">
								<div class="col-sm-5">
									<input type="text" name="filter[search-value]" class="form-control" placeholder="Credit code" value="<?php echo $filter['search-value'] ?>">
								</div>
								<div class="col-sm-3">
									<?php MWHtml::select_credit_code_type($filter['type']) ?>
								</div>
								<div class="col-sm-2">
									<select name="filter[status]" class="select-box-it select-sapphire form-control">
										<option value="">--Status--</option>
										<option value="0"<?php echo $filter['status'] == '0' ? ' selected' : '' ?>>Not used</option>
										<option value="1"<?php echo $filter['status'] == '1' ? ' selected' : '' ?>>Inused</option>
										<option value="2"<?php echo $filter['status'] == '2' ? ' selected' : '' ?>>Expired</option>
										<option value="3"<?php echo $filter['status'] == '3' ? ' selected' : '' ?>>Disabled</option>
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
					<div class="row">
						<div class="col-md-12 grid-table grid-table-striped">
							<div class="row grid-table-head">
								<!-- <div class="col-xs-1 centered"><input type="checkbox" class="check-all" data-name="cid[]"></div> -->
								<div class="col-xs-3 centered">Encoded code</div>
								<div class="col-xs-2 centered">Type</div>
								<div class="col-xs-3 centered">Created by</div>
								<div class="col-xs-2 centered">Created on</div>
								<div class="col-xs-2 centered">Status</div>
							</div>
							<?php foreach($codes->list as $code) : ?>
								<div class="row grid-table-row<?php echo !$code->active ? ' text-muted' : '' ?>">
									<!-- <div class="col-xs-1 centered"><input type="checkbox" name="cid[]" value="<?php echo $group->id ?>"></div> -->
									<div class="col-xs-3 centered"><a href="<?php echo home_url() ?>/?r=verify-credit-codes&amp;c=<?php echo $code->encoded_code ?>" title="View detail"><?php echo $code->encoded_code ?></a></div>
									<div class="col-xs-2 centered"><?php echo $code->type ?></div>
									<div class="col-xs-3 centered"><?php echo $code->display_name ?></div>
									<div class="col-xs-2 centered"><?php echo $code->created_on ?></div>														
									<div class="col-xs-2 centered">
										<?php if($code->active) : ?>
											<?php if(!empty($code->activated_by)) : ?>
												<?php if($code->expired_on < date('Y-m-d', time())) : ?>
													Expired
												<?php else : ?>
													Inused
												<?php endif ?>
											<?php else : ?>
												Not used
											<?php endif ?>
										<?php else : ?>
											Inactive
										<?php endif ?>
									</div>
								</div>
							<?php endforeach ?>
						</div>
					</div>
					
					<div class="row">
						<div class="col-xs-12 centered">
							<?php echo $pagination ?>
						</div>
					</div>
				</div>
			</div>												
		</div>
	</form>

<script>
	(function($){
		$(function(){
			$('#import_list').click(function() {
				$.ajax(home_url + '/?r=ajax/admin/export', {
					method : 'post',
					data   : { id : 1 }
				});
				return false;
			});
			$("#sel-dictionary").data("selectBox-selectBoxIt").disable();
			$("#verify").click(function(){
				var c = $("#credit-code").val().trim();
				if(c != ""){
					window.location.href = "<?php echo home_url() ?>/?r=verify-credit-codes&c=" + c;
				}
			});
			
			
		});
	})(jQuery);
</script>

<?php get_dict_footer() ?>