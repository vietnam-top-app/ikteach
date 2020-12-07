<?php

	wp_register_script( 'csv-js', get_stylesheet_directory_uri() . '/library/js/jquery.csv-0.71.min.js', array( 'jquery' ) );
	wp_enqueue_script( 'csv-js' );

	$layout = isset($_GET['layout']) ? $_GET['layout'] : '';
	$cid = isset($_GET['cid']) && is_numeric($_GET['cid']) ? $_GET['cid'] : 0;

	// process task
	$task = isset($_POST['task']) ? $_POST['task'] : '';

	if(isset($task['create']) || isset($task['update']))
	{
		$sel_custom = $_REAL_POST['sel-custom'];

		$data['id'] = $_REAL_POST['sid'];
		$data['assignment-id'] = $_REAL_POST['assignments'];
		$data['homework-types'] = $_REAL_POST['homework-types'];
		$data['sheet-categories'] = $_REAL_POST['sheet-categories'];
		$data['trivia-exclusive'] = isset($_REAL_POST['trivia-exclusive']) ? $_REAL_POST['trivia-exclusive'] : 0;
		$data['grade'] = $_REAL_POST['grade'];
		$data['sheet-name'] = $_REAL_POST['sheet-name'];
		$data['grading-price'] = $_REAL_POST['grading-price'];
		$data['dictionary'] = $_REAL_POST['dictionary'];
		$data['questions'] = $_REAL_POST['words'];
		$data['reading_passage'] = $_REAL_POST['reading_passage'];
		$data['description'] = $_REAL_POST['description'];
		$data['wordchecked'] = $_REAL_POST['wordchecked'];
		$data['active'] = 0;

		if(MWDB::store_sheet($data))
		{
			wp_redirect( home_url() . '/?r=math-homework-creator' );
			exit;
		}
		else
		{
			/* if($_REAL_POST['sid']) {
				wp_redirect( home_url() . '/?r=math-homework-creator&layout=create&cid=' . $_REAL_POST['sid'] );
				exit;
			} */
		}
	}

	if(isset($task['active']))
	{
		$cid = $_REAL_POST['cid'];

		if(!empty($cid)) {
			if(MWDB::active_sheets($cid))
			{
				ik_enqueue_messages('Successfully active/deactive ' . count($cid) . ' sheets', 'success');

				wp_redirect( home_url() . '/?r=math-homework-creator' );
				exit;
			}
		}
		else {
			ik_enqueue_messages('Please select a sheet.', 'error');
		}
	}

	if(isset($task['remove']))
	{
		$cid = $_REAL_POST['cid'];

		if( MWDB::delete_sheets($cid) )
		{
			wp_redirect( home_url() . '/?r=math-homework-creator' );
			exit;
		}
	}

	if(isset($_POST['export']))
	{
		$slist = MWDB::get_all_sheets();

		header( 'Content-Type: text/csv' );
		header( 'Content-Disposition: attachment;filename=homework_export_' . date('mdY_Hms', time()));
		$fp = fopen('php://output', 'w');
		foreach($slist as $item) {
			$row_header = array('Sheet Name: ' . $item->sheet_name . ' --- Grade: ' . $item->grade);
			fputcsv($fp, $row_header);
			$content = json_decode($item->questions);
			if($item->assignment_id == ASSIGNMENT_SPELLING)
			{
				foreach($content as $item) {
					fputcsv($fp, array(html_entity_decode($item, ENT_QUOTES)));
				}
			}
			else
			{
				foreach($content->question as $key => $value) {
					$col1 = html_entity_decode($content->quiz[$key], ENT_QUOTES);
					$col2 = html_entity_decode($content->question[$key], ENT_QUOTES);
					$col3 = html_entity_decode($content->c_answer[$key], ENT_QUOTES);
					$col4 = html_entity_decode($content->w_answer1[$key], ENT_QUOTES);
					$col5 = html_entity_decode($content->w_answer2[$key], ENT_QUOTES);

					$row = array($col1, $col2, $col3, $col4, $col5);
					if(!empty($content->w_answer3[$key])) {
						$row[] = html_entity_decode($content->w_answer3[$key], ENT_QUOTES);
					}
					if(!empty($content->w_answer4[$key])) {
						$row[] = html_entity_decode($content->w_answer4[$key], ENT_QUOTES);
					}

					fputcsv($fp, $row);

					if($item->assignment_id == ASSIGNMENT_READING) {
						fputcsv($fp, array(strip_tags($item->passages)));
					}
				}
			}
			fputcsv($fp, array());
			fputcsv($fp, array());
		}
		fclose($fp);
		exit;
	}

	// page content	
	if($cid)
	{
		$current_sheet = $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . $wpdb->prefix . 'dict_sheets WHERE id = %s', $cid));

		$data['assignment-id'] = $current_sheet->assignment_id;
		$data['homework-types'] = $current_sheet->homework_type_id;
		$data['sheet-categories'] = $current_sheet->category_id;
		$data['trivia-exclusive'] = $current_sheet->trivia_exclusive;
		$data['grade'] = $current_sheet->grade;
		$data['sheet-name'] = $current_sheet->sheet_name;
		$data['dictionary'] = $current_sheet->dictionary_id;
		$data['questions'] = json_decode($current_sheet->questions, true);
		$data['reading_passage'] = $current_sheet->passages;
		$data['description'] = $current_sheet->description;
	}

	$main_categories = MWDB::get_grades(array('type' => 'MATH', 'level' => 0));
	$levels = MWDB::get_grades(array('type' => 'MATH', 'level' => 1));
	$sublevels = MWDB::get_grades(array('type' => 'MATH', 'level' => 2));

	$sel_levels_html = $sel_sublevels_html = '';
	foreach($main_categories as $item) {
		$sel_levels_html .= '<select class="hidden" id="_l' . $item->id . '">';
		foreach($levels as $level) {
			if($level->parent_id == $item->id) {
				$sel_levels_html .= '<option value="' . $level->id . '">' . $level->name . '</option>';
			}

			$sel_sublevels_html .= '<select class="hidden" id="_sl' . $level->id . '">';
			foreach($sublevels as $sublevel) {
				if($sublevel->parent_id == $level->id) {
					$sel_sublevels_html .= '<option value="' . $sublevel->id . '">' . $sublevel->name . '</option>';
				}
			}
			$sel_sublevels_html .= '</select>';
		}
		$sel_levels_html .= '</select>';
	}
	
	$current_page = max( 1, get_query_var('page'));
	$filter = get_page_filter_session();
	if(empty($filter))
	{
		$filter['orderby'] = 'grade';
		$filter['order-dir'] = 'asc';
		$filter['items_per_page'] = 20;
		$filter['offset'] = $filter['items_per_page'] * ($current_page - 1);
	}
	else {
		if(isset($_REAL_POST['filter']['search']))
		{
			$filter['sheet-name'] = $_REAL_POST['filter']['sheet-name'];
			$filter['grade'] = $_REAL_POST['filter']['grade'];
			$filter['assignment-id'] = $_REAL_POST['filter']['assignment-id'];
			$filter['homework-types'] = $_REAL_POST['filter']['homework-types'];
			$filter['trivia-exclusive'] = $_REAL_POST['filter']['trivia-exclusive'];
			$filter['active'] = $_REAL_POST['filter']['active'];
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
	$sheets_obj = MWDB::get_sheets($filter, false, true);
	// $avail_sheets = $sheets_obj->items;
	$total_rows = $sheets_obj->total;
	$total_pages = ceil($total_rows / $filter['items_per_page']);
	$pagination = paginate_links( array(
		'format' => '?page=%#%',
		'current' =>  $current_page,
		'total' => $total_pages
	) );
?>
<?php get_dict_header('Admin Math Homework Creator') ?>
<?php get_dict_page_title('Admin Math Homework Creator', 'admin-page') ?>

	<form method="post" action="<?php echo home_url() ?>/?r=math-homework-creator<?php echo $layout == 'create' ? '&amp;layout=create' : '' ?><?php echo $cid ? '&amp;cid=' . $cid : '' ?>" id="main-form" enctype="multipart/form-data">

		<?php if($layout != 'create') : ?>
			<div class="row">
				<div class="col-sm-12">
					<h2 class="title-border">Available Math Worksheets</h2>
				</div>
				<div class="col-sm-5 col-md-4 col-sm-offset-7 col-md-offset-8">
					<div class="form-group">
						<a href="<?php echo home_url() ?>/?r=math-homework-creator&amp;layout=create" class="btn btn-default orange form-control"><span class="icon-plus"></span>Create Worksheet</a>
					</div>
				</div>
				<div class="col-sm-12">
					<div class="box box-sapphire">
						<div class="row box-header">
							<div class="col-sm-3 col-sm-offset-7">
								<button name="task[active]" type="submit" class="hidden btn btn-default btn-block grey form-control">Active/Deactive</button>
							</div>
							<div class="col-sm-2">
								<button name="task[remove]" type="submit" class="hidden btn btn-default btn-block grey form-control">Remove</button>
							</div>
							<div class="col-sm-12">
								<div class="row search-tools hidden">
									<div class="col-sm-6">
										<div class="form-group">
											<input type="text" id="filter-sheet-name" name="filter[sheet-name]" class="form-control" placeholder="Sheet Name" value="<?php echo $filter['sheet-name'] ?>">
										</div>
									</div>
									<div class="col-sm-3">
										<div class="form-group">
											<?php MWHtml::sel_assignments($filter['assignment-id'], false, array(), '-Assignment-', 'filter[assignment-id]', 'select-sapphire form-control', 'filter-assignment') ?>
										</div>
									</div>
									<div class="col-sm-3">
										<div class="form-group">
											<?php MWHtml::sel_homework_types($filter['homework-types'], true, '-Homework Type-', 'filter[homework-types]', 'select-sapphire form-control', 'filter-homework-types') ?>
										</div>
									</div>
									<div class="col-sm-3">
										<div class="form-group">
											<?php MWHtml::select_grade('', 'select-sapphire form-control', 'filter[grade]', $filter['grade']) ?>
										</div>
									</div>
									<div class="col-sm-3">
										<div class="form-group">
											<select class="select-box-it select-sapphire form-control" name="filter[trivia-exclusive]">
												<option value="">-Trivia Exclusive-</option>
												<option value="1"<?php echo $filter['trivia-exclusive'] == '1' ? ' selected' : '' ?>>Yes</option>
												<option value="0"<?php echo $filter['trivia-exclusive'] == '0' ? ' selected' : '' ?>>No</option>
											</select>
										</div>
									</div>
									<div class="col-sm-3">
										<div class="form-group">
											<select class="select-box-it select-sapphire form-control" name="filter[active]">
												<option value="">-Status-</option>
												<option value="1"<?php echo $filter['active'] == '1' ? ' selected' : '' ?>>Active</option>
												<option value="0"<?php echo $filter['active'] == '0' ? ' selected' : '' ?>>Inactive</option>
											</select>
										</div>
									</div>
									<div class="col-sm-3">
										<div class="form-group">
											<button type="submit" class="btn btn-default sky-blue form-control" name="filter[search]" id="search-btn">Search</button>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-12">
								<div class="scroll-list2" style="max-height: 600px">
									<table class="table table-striped table-condensed ik-table1 vertical-middle text-center">
										<thead>
											<tr>
												<th><input type="checkbox" class="check-all" data-name="cid[]"></th>
												<th class="hidden-xs">Assignment</th>
												<th class="hidden-xs">Level Category</th>
												<th class="hidden-xs">
													<a href="#" class="sortable<?php echo $filter['orderby'] == 'level' ? ' ' . $filter['order-dir'] : '' ?>" data-sort-by="grade">Level <span class="sorting-indicator"></span></a>
												</th>
												<th class="hidden-xs">
													<a href="#" class="sortable<?php echo $filter['orderby'] == 'sublevel' ? ' ' . $filter['order-dir'] : '' ?>" data-sort-by="grade">Sublevel <span class="sorting-indicator"></span></a>
												</th>
												<th>
													<a href="#" class="sortable<?php echo $filter['orderby'] == 'sheet_name' ? ' ' . $filter['order-dir'] : '' ?>" data-sort-by="sheet_name">Sheet Name <span class="sorting-indicator"></span></a>
												</th>
											</tr>
										</thead>
										<tfoot>
											<tr><td colspan="7"><?php echo $pagination ?></td></tr>
										</tfoot>
										<tbody><?php if(empty($avail_sheets)) : ?>
											<tr><td colspan="7">No results</td></tr>
											<?php else : foreach($avail_sheets as $sheet) : ?>
												<tr<?php echo $sheet->active ? '' : ' class="text-muted"' ?> data-id="<?php echo $sheet->id ?>" data-assignment="<?php echo $sheet->assignment_id ?>">
													<td><input type="checkbox" name="cid[]" value="<?php echo $sheet->id ?>"></td>
													<td class="hidden-xs"><?php echo $sheet->assignment ?></td>
													<td class="hidden-xs"><?php echo $sheet->grade ?></td>
													<td><?php echo $sheet->sheet_name ?></td>
													<td class="hidden-xs"><?php echo $sheet->name ?></td>
													<td class="hidden-xs"><?php echo $sheet->homework_type ?></td>
													<td>
														<a href="<?php echo home_url() ?>/?r=math-homework-creator&amp;layout=create&amp;cid=<?php echo $sheet->id ?>" title="Edit this sheet" class="btn btn-default btn-block btn-tiny grey">Edit</a>
														<?php if($sheet->assignment_id != ASSIGNMENT_SPELLING && $sheet->assignment_id != ASSIGNMENT_VOCAB_BUILDER) : ?>
															<button type="button" class="btn btn-default btn-block btn-tiny grey preview-btn">Preview</button>
														<?php endif ?>
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
				<div class="col-sm-4 col-sm-offset-3 col-md-offset-4">
					<div class="form-group"></div>
					<button type="submit" name="export" class="hidden btn btn-default btn-block grey form-control">Export</button>
				</div>
				<div class="col-sm-5 col-md-4">
					<div class="form-group"></div>
					<a href="<?php echo home_url() ?>/?r=math-homework-creator&amp;layout=create" class="btn btn-default btn-block orange form-control"><span class="icon-plus"></span>Create Worksheet</a>
				</div>
			</div>
			<input type="hidden" name="filter[orderby]" id="filter-order" value="<?php echo $filter['orderby'] ?>">
			<input type="hidden" name="filter[order-dir]" id="filter-order-dir" value="<?php echo $filter['order-dir'] ?>">

<div class="modal fade modal-green" id="homework-viewer-modal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <a href="#" data-dismiss="modal" aria-hidden="true" class="close"></a>
        <h3 class="modal-title">Homework Viewer <span><span id="homework-detail"></span> <span id="question-i">1</span></span></h3>
      </div>
      <div class="modal-body green">
		<div class="row">
			<div class="col-sm-12" id="quiz-box">
				<span id="quiz"></span>
			</div>
			<div class="col-sm-12">
				<ul class="select-box multi-choice" id="question-box" data-placement="top" data-trigger="focus">
					<li class="vocab-keyword" id="vocab-question"></li>
					<li><a class="answer"><span class="box-letter">A</span> <span id="answer-a" class="ac"></span></a></li>
					<li><a class="answer"><span class="box-letter">B</span> <span id="answer-b" class="ac"></span></a></li>
					<li><a class="answer"><span class="box-letter">C</span> <span id="answer-c" class="ac"></span></a></li>
					<li class="hidden"><a class="answer"><span class="box-letter">D</span> <span id="answer-d" class="ac"></span></a></li>
					<li class="hidden"><a class="answer"><span class="box-letter">E</span> <span id="answer-e" class="ac"></span></a></li>
				</ul>
				<div class="box box-green" id="writing-subject-block" style="display: none; margin: 20px 0"><div class="scroll-list" style="max-height: 250px"><div id="writing-subject"></div></div></div>
			</div>
			<div class="col-sm-12">
				<div class="form-group">
					<button type="button" id="next-btn" class="btn btn-default btn-block sky-blue"><span class="icon-next"></span>Next</button>
				</div>
			</div>
			<div class="col-sm-12" style="display: none" id="passage-block">
				<div class="form-group">
					<label>Passage</label>
					<div id="reading-passage-box" class="scroll-list" style="max-height: 200px">
						<div id="reading-passage"></div>
					</div>
				</div>
			</div>
		</div>
		<input type="hidden" id="current-row" value="1">
		<input type="hidden" id="current-assignment" value="">
	  </div>
    </div>
  </div>
</div>

		<?php else : ?>

			<div class="row">
				<div class="col-sm-12">
					<h2 class="title-border"><?php echo $cid ? 'Update' : 'Create new' ?> Homework</h2>
				</div>
				<div class="col-sm-6 form-group">
					<label>Assignments</label>
					<?php MWHtml::sel_math_assignments() ?>
				</div>
				<div class="col-sm-6 form-group">
					<label>Level Category</label>
					<select class="select-box-it form-control" name="level-category" id="sel-level-categories">
						<option value="">Select one</option>
						<?php foreach($main_categories as $item) : ?>
							<option value="<?php echo $item->id ?>"><?php echo $item->name ?></option>
						<?php endforeach ?>
					</select>
				</div>
				<div class="col-sm-6 form-group">
					<label>Homework Types</label>
					<?php MWHtml::sel_homework_types($data['homework-types'], true, 'Select one') ?>
				</div>
				<div class="col-sm-6 form-group">
					<label>Level</label>
					<select class="select-box-it form-control" name="level" id="sel-levels"></select>
					<?php echo $sel_levels_html ?>
				</div>
				<div class="col-sm-6 form-group">
					<label for="sheet-name">Sheet Name</label>
					<input type="text" class="form-control" id="sheet-name" name="sheet-name" value="<?php echo $data['sheet-name'] ?>">
				</div>
				<div class="col-sm-6 form-group">
					<label>Sublevel</label>
					<select class="select-box-it form-control" id="sel-sublevels">
					</select>
					<?php echo $sel_sublevels_html ?>
				</div>
				<div class="col-sm-6">
					<div class="row">
						<div class="col-xs-6 form-group">
							<label for="imported-file">Import from a file</label>
							<input type="text" class="form-control" id="imported-file" name="imported-file" value="" readonly>
						</div>
						<div class="col-xs-6 form-group">
							<label>&nbsp;</label>
							<span class="btn btn-default btn-block grey btn-file">
								<span class="icon-browse"></span>Browse
								<input name="input-file" id="input-file" type="file">
							</span>
						</div>
					</div>
				</div>
				
				<div class="col-sm-12 form-group">
					<div class="box">
						<div class="form-group">
							<input type="text" class="form-control" placeholder="Question">
						</div>
						<table class="table table-striped table-condensed ik-table1 vertical-middle text-center">
							<thead><tr>
								<th>No.</th>
								<th>Step</th>
								<th>Comment</th>
								<th>Number</th>
								<th>Note</th>
							</tr></thead>
							<tbody>
								<tr>
									<td>[1]</td>
									<td></td>
									<td>Multicant</td>
									<td><input type="text" class="input-box-style2"></td>
									<td></td>
								</tr>
								<tr>
									<td></td>
									<td></td>
									<td>Multicant</td>
									<td><input type="text" class="input-box-style2"></td>
									<td></td>
								</tr>
								<tr>
									<td></td>
									<td>Step 1</td>
									<td>Partial Sum</td>
									<td><input type="text" class="input-box-style2"></td>
									<td></td>
								</tr>
								<tr>
									<td></td>
									<td>Step 2</td>
									<td>Carry</td>
									<td><input type="text" class="input-box-style2"></td>
									<td></td>
								</tr>
								<tr>
									<td></td>
									<td>Step 3</td>
									<td>Partial Sum</td>
									<td><input type="text" class="input-box-style2"></td>
									<td></td>
								</tr>
								<tr>
									<td></td>
									<td>Step 4</td>
									<td>Carry</td>
									<td><input type="text" class="input-box-style2"></td>
									<td></td>
								</tr>
								<tr>
									<td></td>
									<td>Step 5</td>
									<td>Partial Sum</td>
									<td><input type="text" class="input-box-style2"></td>
									<td></td>
								</tr>
								<tr>
									<td></td>
									<td>Step 6</td>
									<td>Carry</td>
									<td><input type="text" class="input-box-style2"></td>
									<td></td>
								</tr>
								<tr>
									<td></td>
									<td>Step 7</td>
									<td></td>
									<td><input type="text" class="input-box-style2"></td>
									<td>Answer</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col-sm-6">
					<div class="form-group">
						<?php if($cid) : ?>
							<button type="button" name="task[update]" class="btn btn-default btn-block orange"><span class="icon-save"></span>Update homework</button>
						<?php else : ?>
							<button type="button" name="task[create]" class="btn btn-default btn-block orange"><span class="icon-plus"></span>Create a new homework</button>
						<?php endif ?>
					</div>
				</div>
				<div class="col-sm-6">
					<div class="form-group">
						<a href="<?php echo home_url() ?>/?r=math-homework-creator" class="btn btn-default btn-block grey"><span class="icon-goback"></span>Go back</a>
					</div>
				</div>
			</div>

<div class="modal fade modal-red-brown modal-large" id="sheet-editor-modal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <a href="#" data-dismiss="modal" aria-hidden="true" class="close"></a>
        <h3 class="modal-title">Editor: Question <span></span></h3>
      </div>
      <div class="modal-body">
		<input type="hidden" id="current-row-index" value="">
		<div class="form-group">
			<label>Subject</label>
			<input type="text" id="editor-input-5" class="form-control" value="" autocomplete="off">
		</div>
		<div class="form-group">
			<label>Question</label>
			<input type="text" id="editor-input-1i" class="form-control" value="" autocomplete="off">
			<textarea class="form-control" id="editor-input-1a" style="resize: vertical; height: 300px; display: none"></textarea>
		</div>
		<div class="form-group">
			<label>Correct Answer</label>
			<input type="text" id="editor-input-2" class="form-control" value="" autocomplete="off">
		</div>
		<div class="form-group">
			<label>Incorrect Answer 1</label>
			<input type="text" id="editor-input-3" class="form-control" value="" autocomplete="off">
		</div>
		<div class="form-group">
			<label>Incorrect Answer 2</label>
			<input type="text" id="editor-input-4" class="form-control" value="" autocomplete="off">
		</div>
		<div class="form-group">
			<label>Incorrect Answer 3</label>
			<input type="text" id="editor-input-6" class="form-control" value="" autocomplete="off">
		</div>
		<div class="form-group">
			<label>Incorrect Answer 4</label>
			<input type="text" id="editor-input-7" class="form-control" value="" autocomplete="off">
		</div>
	  </div>
      <div class="modal-footer">
		<div class="row">
			<div class="col-sm-6">
				<button type="button" class="btn btn-block orange" id="editor-save-btn"><span class="icon-check"></span>Save</button>
			</div>
			<div class="col-sm-6">
				<button type="button" class="btn btn-block grey" data-dismiss="modal"><span class="icon-cancel"></span>Cancel</button>
			</div>
		</div>
      </div>
    </div>
  </div>
</div>

		<?php endif ?>

	</form>

<script>
	(function($){
		$(function(){
			$("#sel-level-categories").on("option-click", function(){
				var _c = $(this).val();
				if(_c != ""){
					var $_l = $("#sel-levels");
					$_l.html($("#_l" + _c).html()).data("selectBox-selectBoxIt").refresh();
					$("#sel-sublevels").html($("#_sl" + $_l.val()).html()).data("selectBox-selectBoxIt").refresh();
				}else{
					$("#sel-levels").html("").data("selectBox-selectBoxIt").refresh();
					$("#sel-sublevels").html("").data("selectBox-selectBoxIt").refresh();
				}
			});

			$("#sel-levels").on("option-click", function(){
				var _l = $(this).val();
				$("#sel-sublevels").html($("#_sl" + _l).html()).data("selectBox-selectBoxIt").refresh();
			});
		});
	})(jQuery);
</script>

<?php get_dict_footer() ?>