<?php

	$data['parent_id'] = $_SESSION['data_parent_id'];

	if(isset($_POST['create']))
	{
		if($_POST['cid']) {
			$data['id'] = $_POST['cid'];
		}
		$data['parent_id'] = $_POST['parent-level'];
		$data['name'] = $_REAL_POST['level-name'];
		$data['ordering'] = $_POST['ordering'];
		$data['type'] = 'MATH';
		$data['level'] = 1;

		$_SESSION['data_parent_id'] = $data['parent_id'];

		if(MWDB::store_grade($data)) {
			ik_enqueue_messages('Successfully store math level', 'success');
			wp_redirect(home_url() . '/?r=create-math-level');
			exit;
		}
		else {
			ik_enqueue_messages('An error occured', 'error');
		}
	}

	if(isset($_POST['order-up'])) {
		MWDB::set_grade_order_up($_POST['cid']);
		wp_redirect(home_url() . '/?r=create-math-level');
		exit;
	}

	if(isset($_POST['order-down'])) {
		MWDB::set_grade_order_down($_POST['cid']);
		wp_redirect(home_url() . '/?r=create-math-level');
		exit;
	}

	$lid = empty($_GET['lid']) ? 0 : $_GET['lid'];

	if($lid) {
		$grade = MWDB::get_grade_by_id($lid);
		$data['parent_id'] = $grade->parent_id;
		$data['name'] = $grade->name;
		$data['ordering'] = $grade->ordering;
	}

	$filter = get_page_filter_session();
	if(empty($filter))
	{
		$filter['type'] = 'MATH';
		$filter['level'] = 1;
		$filter['orderby'] = 'ordering';
		$filter['order-dir'] = 'asc';
		// $filter['items_per_page'] = 99999999;
		// $filter['offset'] = $filter['items_per_page'] * ($current_page - 1);
	}
	else {
		if(isset($_REAL_POST['filter']['search'])) {
			$filter['parent_id'] = $_POST['filter']['category'];
		}

		if(isset($_REAL_POST['filter']['orderby'])) {
			$filter['orderby'] = $_REAL_POST['filter']['orderby'];
			$filter['order-dir'] = $_REAL_POST['filter']['order-dir'];
		}

		$filter['offset'] = $filter['items_per_page'] * ($current_page - 1);
	}

	set_page_filter_session($filter);

	$main_categories = MWDB::get_grades(array('type' => 'MATH', 'level' => 0, 'admin_only' => 1));
	$levels = MWDB::get_grades($filter);
?>
<?php get_dict_header('Math Level') ?>
<?php get_dict_page_title('Math Level', 'admin-page') ?>

	<form action="<?php echo home_url() ?>/?r=create-math-level" method="post" id="main-form">
		<div class="row">
			<div class="col-sm-12">
				<h2 class="title-border">New Math Level</h2>
			</div>
			<div class="col-sm-3 form-group">
				<label>Select Category</label>
				<select class="select-box-it form-control" name="parent-level">
					<?php foreach($main_categories as $item) : ?>
						<option value="<?php echo $item->id ?>"<?php echo $item->id == $data['parent_id'] ? ' selected' : '' ?>><?php echo $item->name ?></option>
					<?php endforeach ?>
				</select>
			</div>
			<div class="col-sm-4 form-group">
				<label>Level Name</label>
				<input type="text" class="form-control" name="level-name" value="<?php echo $data['name'] ?>">
			</div>
			<div class="col-sm-2 form-group">
				<label>Ordering</label>
				<input type="number" class="form-control" name="ordering" value="<?php echo $data['ordering'] ?>"<?php echo $lid ? ' readonly' : '' ?>>
			</div>
			<div class="col-sm-3 form-group">
				<label>&nbsp;</label>
				<input type="submit" name="create" class="btn btn-default btn-block orange form-control" value="<?php echo $lid ? 'Update' : 'Create' ?> math level">
			</div>

			<div class="col-sm-12">
				<h2 class="title-border">Math Level</h2>
			</div>
			<div class="col-sm-12">
				<div class="box">
					<div class="row box-header">
						<div class="col-sm-3 col-sm-offset-6">
							<select class="select-box-it form-control" name="filter[category]">
								<option value="">--Category--</option>
								<?php foreach($main_categories as $item) : ?>
									<option value="<?php echo $item->id ?>"<?php echo $filter['parent_id'] == $item->id ? ' selected' : '' ?>><?php echo $item->name ?></option>
								<?php endforeach ?>
							</select>
						</div>
						<div class="col-sm-3">
							<button type="submit" name="filter[search]" class="btn btn-default btn-block grey form-control">Search</button>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-12">
							<div class="scroll-list2" style="max-height: 600px">
								<table class="table table-striped table-condensed ik-table1 text-center">
									<thead>
										<tr>
											<th>Category</th>
											<th>Level</th>
											<th>
												<a href="#" class="sortable<?php echo $filter['orderby'] == 'ordering' ? ' ' . $filter['order-dir'] : '' ?>" data-sort-by="ordering">Ordering <span class="sorting-indicator"></span></a>
											</th>
											<th style="width: 120px">Sub Level</th>
											<th></th>
										</tr>
									</thead>
									<tbody>
										<?php if(empty($levels)) : ?>
											<tr><td colspan="3">No results</td></tr>
										<?php else :
											foreach($levels as $item) :
												$sublevels = MWDB::get_grades(array('type' => 'MATH', 'level' => 2, 'parent_id' => $item->id, 'orderby' => 'ordering', 'order-dir' => 'asc')) ?>
												<tr>
													<td><?php echo $item->parent_name ?></td>
													<td><a href="<?php echo home_url() . '/?r=create-math-level&amp;lid=' . $item->id ?>"><?php echo $item->name ?></a></td>
													<td>
														<button type="submit" name="order-up" class="btn btn-micro grey order-btn" data-id="<?php echo $item->id ?>"><span class="icon-uparrow"></span></button>
														<button type="submit" name="order-down" class="btn btn-micro grey order-btn" data-id="<?php echo $item->id ?>"><span class="icon-downarrow"></span></button>
														<span class="ordering"><?php echo $item->ordering ?></span>
													</td>
													<td>
														<button type="button" class="btn btn-default btn-block btn-tiny grey view-sublevel" data-id="<?php echo $item->id ?>" data-name="<?php echo $item->name ?>">View or Add</button>
														<table class="hidden" id="_s<?php echo $item->id ?>"><tbody><?php 
															foreach($sublevels as $subitem) : ?>
																<tr>
																	<td><?php echo $subitem->name ?></td>
																	<td><input type="text" class="form-control txt-name" placeholder="New name"></td>
																	<td><button type="button" class="btn btn-default btn-block grey form-control btn-rename" data-loading-text="Saving..." data-id="<?php echo $subitem->id ?>">Rename</button></td>
																	<td style="width: 100px">
																		<button type="button" name="order-up" class="btn btn-micro grey order-btn sub-order-up" data-id="<?php echo $subitem->id ?>"><span class="icon-uparrow"></span></button>
																		<button type="button" name="order-down" class="btn btn-micro grey order-btn sub-order-down" data-id="<?php echo $subitem->id ?>"><span class="icon-downarrow"></span></button>
																		<span class="ordering"><?php echo $subitem->ordering ?></span>
																	</td>
																</tr>
															<?php endforeach ?>
														</tbody></table>
													</td>
													<td>
														<a href="<?php echo home_url() . '/?r=create-math-level&amp;lid=' . $item->id ?>" class="btn btn-default btn-block btn-tiny grey">Edit</a>
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
		</div>
		<input type="hidden" name="cid" value="<?php echo $lid ?>">
		<input type="hidden" name="filter[orderby]" id="filter-order" value="<?php echo $filter['orderby'] ?>">
		<input type="hidden" name="filter[order-dir]" id="filter-order-dir" value="<?php echo $filter['order-dir'] ?>">
	</form>

<div id="sublevel-dialog" class="modal fade modal-large modal-red-brown" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
            <a href="#" data-dismiss="modal" aria-hidden="true" class="close"></a>
            <h3>Sub Level of <span id="parent-name"></span></h3>
        </div>
        <div class="modal-body">
			<div class="row">
				<div class="col-sm-6 form-group">
					<label>Sub Level Name</label>
					<input type="text" class="form-control" id="sub-level-name">
				</div>
				<div class="col-sm-6 form-group">
					<label>&nbsp;</label>
					<button type="button" class="btn btn-default btn-block orange form-control" id="create-sublevel" data-loading-text="Creating...">Create</button>
				</div>
				<div class="col-sm-12">
					<h2 class="title-border">Sub Level List</h2>
				</div>
				<div class="col-sm-12">
					<table class="table table-striped table-condensed ik-table1" id="sublevel-tbl">
						<thead><tr><th>Sub Level Name</th></tr></thead>
						<tbody></tbody>
					</table>
				</div>
				<input type="hidden" id="parent-id">
			</div>
		</div>
      </div>
    </div>
</div>

<script>
	(function($){
		$(function(){
			$("button.view-sublevel").click(function(){
				$("#parent-id").val($(this).attr("data-id"));
				$("#parent-name").text($(this).attr("data-name"));
				$("#sublevel-tbl").find("tbody").html($(this).next().find("tbody").html());
				$("#sublevel-dialog").modal();
			});

			$("#create-sublevel").click(function(){
				var tthis = $(this);
				var _n = $("#sub-level-name");
				if(_n.val().trim() == ""){
					_n.popover({content: '<span class="text-danger">Level name cannot be empty</span>', html: true, trigger: "hover", placement: "bottom"})
					.popover("show");
					setTimeout(function(){_n.popover("destroy")}, 1500);
				}else{
					tthis.button("loading");
					var _name = _n.val();
					var _pid = $("#parent-id").val();
					$.post(home_url + "/?r=ajax/grade/add",{name: _name, parent_id: _pid, level: 2, type: "MATH"},function(data){
						tthis.button("reset");
						if(data != 0){
							var tr = "<tr><td>" + _name + "</td></tr>";
							$("#sublevel-tbl").find("tbody").append(tr);
							$("#_s" + _pid).find("tbody").append(tr);
							_n.val("").focus();
						}
					});
				}
			});

			$("#sublevel-tbl").on("click", "button.btn-rename", function(){
				var tthis = $(this);
				var _id = $(this).attr("data-id");
				var _t = tthis.parents("tr").find("input.txt-name");
				if(_t.val().trim() != ""){
					tthis.button("loading");
					$.post(home_url + "/?r=ajax/grade/rename", {id: _id, n: _t.val()}, function(data){
						//tthis.button("reset");
						location.reload();
					});
				}
			});

			$("#sublevel-tbl").on("click", ".sub-order-up", function(){
				var tr = $(this).parents("tr");
				var corder = tr.find("span.ordering"), porder = tr.prev().find("span.ordering");
				$.post(home_url + "/?r=ajax/grade/change_order", {id: $(this).attr("data-id"), dir: "up"});
				tr.fadeOut(400).insertBefore(tr.prev()).fadeIn(400);
				corder.text(parseInt(corder.text()) - 1);
				porder.text(parseInt(porder.text()) + 1);
			});

			$("#sublevel-tbl").on("click", ".sub-order-down", function(){
				var tr = $(this).parents("tr");
				var corder = tr.find("span.ordering"), porder = tr.next().find("span.ordering");
				$.post(home_url + "/?r=ajax/grade/change_order", {id: $(this).attr("data-id"), dir: "down"});
				tr.fadeOut(400).insertAfter(tr.next()).fadeIn(400);
				corder.text(parseInt(corder.text()) + 1);
				porder.text(parseInt(porder.text()) - 1);
			});
		});
	})(jQuery);
</script>
<?php get_dict_footer() ?>