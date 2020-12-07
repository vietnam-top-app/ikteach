<?php

	wp_register_script( 'csv-js', get_stylesheet_directory_uri() . '/library/js/jquery.csv-0.71.min.js', array( 'jquery' ) );
	wp_enqueue_script( 'csv-js' );

	$layout = isset($_GET['layout']) ? $_GET['layout'] : '';
	$cid = isset($_GET['cid']) && is_numeric($_GET['cid']) ? $_GET['cid'] : 0;
	$task = isset($_POST['task']) ? $_POST['task'] : '';

	$route = get_route();
	if(empty($route[1])) {
		$active_tab = 'english';
	}
	else {
		$active_tab = $route[1];
	}

	$tab_options = array(
		'items' => array(
			'english' => array('url' => home_url() . '/?r=admin-homework-creator/english', 'text' => 'English'),
			'mathematics' => array('url' => home_url() . '/?r=admin-homework-creator/mathematics', 'text' => 'Mathematics')
		),
		'active' => $active_tab
	);

	switch($active_tab)
	{
		// english homework
		case 'english':

			// process task
			$data = array();
			$data['assignment-id'] = ASSIGNMENT_SPELLING;

			// update or create english sheet
			if(isset($task['create']) || isset($task['update']))
			{
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
				$data['active'] = 0; // disable sheet by default
				$data['next-worksheet-id'] = $_REAL_POST['next-worksheet-id'];
				$data['lang'] = !empty($_POST['lang']) ? $_POST['lang'] : 'en';

				if(MWDB::store_sheet($data))
				{
					wp_redirect(home_url() . '/?r=admin-homework-creator/english');
					exit;
				}
				else
				{
					/* if($_REAL_POST['sid']) {
						wp_redirect(home_url() . '/?r=admin-homework-creator/english&layout=create&cid=' . $_REAL_POST['sid']);
						exit;
					} */
				}
			}

			// toggle active
			if(isset($task['active']))
			{
				$cid = $_REAL_POST['cid'];

				if(!empty($cid)) {
					if(MWDB::toggle_active_sheets($cid))
					{
						ik_enqueue_messages('Successfully active/deactive ' . count($cid) . ' sheets', 'success');

						wp_redirect(home_url() . '/?r=admin-homework-creator/english');
						exit;
					}
				}
				else {
					ik_enqueue_messages('Please select a sheet.', 'error');
				}
			}

			// remove sheet
			if(isset($task['remove']))
			{
				$cid = $_REAL_POST['cid'];

				if( MWDB::delete_sheets($cid) )
				{
					wp_redirect(home_url() . '/?r=admin-homework-creator/english');
					exit;
				}
			}

			// export all sheets to CSV file
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
					if($item->assignment_id == ASSIGNMENT_SPELLING) {
						foreach($content as $item) {
							fputcsv($fp, array(html_entity_decode($item, ENT_QUOTES)));
						}
					}
					else {
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
			{ // view a sheet
				$current_sheet = $wpdb->get_row($wpdb->prepare(
					'SELECT s.*, gr.name AS grade
					FROM ' . $wpdb->prefix . 'dict_sheets AS s
					JOIN ' . $wpdb->prefix . 'dict_grades AS gr ON gr.id = s.grade_id
					WHERE s.id = %s',
					$cid
				));

				$data['assignment-id'] = $current_sheet->assignment_id;
				$data['homework-types'] = $current_sheet->homework_type_id;
				$data['sheet-categories'] = $current_sheet->category_id;
				$data['trivia-exclusive'] = $current_sheet->trivia_exclusive;
				$data['grade'] = $current_sheet->grade_id;
				$data['sheet-name'] = $current_sheet->sheet_name;
				$data['dictionary'] = $current_sheet->dictionary_id;
				$data['questions'] = json_decode($current_sheet->questions, true);
				$data['reading_passage'] = $current_sheet->passages;
				$data['description'] = $current_sheet->description;
				$data['lang'] = $current_sheet->lang;
			}
			else 
			{ // sheet list
				$current_page = max( 1, get_query_var('page'));
				$filter = get_page_filter_session();
				if(empty($filter) && !isset($_REAL_POST['filter']))
				{
					$filter['orderby'] = 'grade';
					$filter['order-dir'] = 'asc';
					$filter['items_per_page'] = 20;
					$filter['offset'] = $filter['items_per_page'] * ($current_page - 1);
				}
				else {
					if(isset($_REAL_POST['filter']['search']))
					{
						$filter['lang'] = $_POST['filter']['lang'];
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
				
				$avail_sheets = $sheets_obj->items;
				$total_rows = $sheets_obj->total;
				$total_pages = ceil($total_rows / $filter['items_per_page']);
				$pagination = paginate_links( array(
					'format' => '?page=%#%',
					'current' =>  $current_page,
					'total' => $total_pages
				) );
			}

			break; // end case english

		// Math homework
		case 'mathematics':

			// create or update a worksheet
			if(isset($task['create']) || isset($task['update']))
			{
				$data['assignment_id'] = $_POST['math-assignments'];
				$data['homework_type_id'] = $_POST['homework-types'];
				$data['grade_id'] = $_POST['sublevel'];
				$data['sheet_name'] = $_REAL_POST['sheet-name'];
				$data['questions'] = $_REAL_POST['questions'];
				$data['description'] = $_REAL_POST['description'];
				$data['answer_time_limit'] = $_POST['answer-time-limit'];
				$data['show_answer_after'] = $_POST['show-answer-after'];
				$data['category_id'] = 5; // Set to Math category
				$data['active'] = 1;
				$data['created_on'] = date('Y-m-d', time());
				$data['lang'] = $_POST['lang'];
				switch($data['assignment_id']) {
					case MATH_ASSIGNMENT_SINGLE_DIGIT_DIV:
					case MATH_ASSIGNMENT_TWO_DIGIT_DIV:
						$data['questions']['sign'] = '&divide;';
						$steps = explode("\r\n", $data['questions']['steps']);
						foreach($steps as $key => $v) {
							$data['questions']['step']['s' . ($key + 1)] = $v;
						}
						$total_step = count($steps);
						$data['questions']['step']['s' . ($total_step + 1)] = $data['questions']['remainder'];
						$data['questions']['step']['s' . ($total_step + 2)] = $data['questions']['answer'];
						break;

					case MATH_ASSIGNMENT_FLASHCARD:
					case MATH_ASSIGNMENT_FRACTION:
					case MATH_ASSIGNMENT_EQUATION:
						foreach($data['questions']['q'] as $key => $item) {
							$data['questions']['q'][$key]['op'] = htmlentities($item['op']);
							if(trim($item['answer']) == '') {
								unset($data['questions']['q'][$key]);
							}
						}
						break;

					case MATH_ASSIGNMENT_WORD_PROB:
						foreach($data['questions']['q'] as $key => $item) {
							if(empty($item['image']) || trim($item['image']) == '') {
								unset($data['questions']['q'][$key]);
							}
						}
						break;
				}

				if(!empty($_POST['cid'])) {
					$data['id'] = $_POST['cid'];
				}
				else {
					$data['created_by'] = get_current_user_id();

					$hightest_order = $wpdb->get_col(
						$wpdb->prepare('SELECT MAX(ordering) FROM ' . $wpdb->prefix . 'dict_sheets WHERE grade_id = %d', $data['grade_id'])
					);
					$data['ordering'] = $hightest_order[0] + 1;
				}

				$sel_level_category = $_POST['level-category'];
				$sel_level = $_POST['level'];
				
				if(MWDB::store_math_sheet($data)) {
					wp_redirect(home_url() . '/?r=admin-homework-creator/mathematics');
					exit;
				}
			}

			// change sheet order up
			if(isset($_POST['order-up'])) {
				MWDB::set_math_sheet_order_up($_POST['oid']);
				wp_redirect(locale_home_url() . '/?r=admin-homework-creator/mathematics');
				exit;
			}

			// change sheet order down
			if(isset($_POST['order-down'])) {
				MWDB::set_math_sheet_order_down($_POST['oid']);
				wp_redirect(locale_home_url() . '/?r=admin-homework-creator/mathematics');
				exit;
			}

			// toggle active a sheet
			if(isset($task['active']))
			{
				$cid = $_REAL_POST['cid'];

				if(!empty($cid)) {
					if(MWDB::toggle_active_math_sheets($cid))
					{
						ik_enqueue_messages('Successfully active/deactive ' . count($cid) . ' sheets', 'success');

						wp_redirect(home_url() . '/?r=admin-homework-creator/mathematics');
						exit;
					}
				}
				else {
					ik_enqueue_messages('Please select a sheet.', 'error');
				}
			}

			// delete math sheet
			if(isset($task['remove']))
			{
				$cid = $_REAL_POST['cid'];

				if(MWDB::delete_math_sheets($cid))
				{
					wp_redirect(home_url() . '/?r=admin-homework-creator/mathematics');
					exit;
				}
			}

			$main_categories = MWDB::get_grades(array('type' => 'MATH', 'level' => 0, 'admin_only' => 1, 'orderby' => 'ordering', 'order-dir' => 'asc'));
			$levels = MWDB::get_grades(array('type' => 'MATH', 'level' => 1, 'orderby' => 'ordering', 'order-dir' => 'asc'));
			$sublevels = MWDB::get_grades(array('type' => 'MATH', 'level' => 2, 'orderby' => 'ordering', 'order-dir' => 'asc'));

			$sel_levels_html = '';
			foreach($main_categories as $item) {
				$sel_levels_html .= '<select class="hidden" id="_l' . $item->id . '">';
				foreach($levels as $level) {
					if($level->parent_id == $item->id) {
						$sel_levels_html .= '<option value="' . $level->id . '">' . $level->name . '</option>';
					}
				}
				$sel_levels_html .= '</select>';
			}

			$sel_sublevels_html = '';
			foreach($levels as $level) {
				$sel_sublevels_html .= '<select class="hidden" id="_sl' . $level->id . '">';
				foreach($sublevels as $sublevel) {
					if($sublevel->parent_id == $level->id) {
						$sel_sublevels_html .= '<option value="' . $sublevel->id . '">' . $sublevel->name . '</option>';
					}
				}
				$sel_sublevels_html .= '</select>';
			}

			// page content
			if($cid)
			{ // view a sheet
				$current_sheet = MWDB::get_math_sheet_by_id($cid);

				$data['assignment_id'] = $current_sheet->assignment_id;
				$data['homework_type_id'] = $current_sheet->homework_type_id;
				$data['sublevel_id'] = $current_sheet->grade_id;
				$data['sheet_name'] = $current_sheet->sheet_name;
				$data['questions'] = json_decode($current_sheet->questions, true);
				$data['description'] = $current_sheet->description;
				$data['answer_time_limit'] = $current_sheet->answer_time_limit;
				$data['show_answer_after'] = $current_sheet->show_answer_after;
				$sel_level_category = $current_sheet->category_level_id;
				$sel_level = $current_sheet->level_id;
				$data['lang'] = $current_sheet->lang;
			}
			else { // sheet list
				$current_page = max( 1, get_query_var('page'));
				$filter = get_page_filter_session();
				if(empty($filter) && !isset($_REAL_POST['filter']))
				{
					$filter['orderby'] = 'ordering';
					$filter['order-dir'] = 'asc';
					$filter['items_per_page'] = 20;
					$filter['offset'] = $filter['items_per_page'] * ($current_page - 1);
				}
				else {
					if(isset($_REAL_POST['filter']['search']))
					{
						$filter['lang'] = $_POST['filter']['lang'];
						$filter['sheet-name'] = $_REAL_POST['filter']['sheet-name'];
						$filter['assignment-id'] = $_REAL_POST['filter']['math-assignments'];
						$filter['homework-types'] = $_REAL_POST['filter']['homework-types'];
						$filter['active'] = $_REAL_POST['filter']['active'];
						$filter['cat-level'] = $_REAL_POST['filter']['cat-level'];
						$filter['level'] = $_REAL_POST['filter']['level'];
						$filter['sublevel'] = $_REAL_POST['filter']['sublevel'];
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
				$sheets_obj = MWDB::get_math_sheets($filter, $filter['offset'], $filter['items_per_page']);
				$avail_sheets = $sheets_obj->items;
				$total_rows = $sheets_obj->total;

				$total_pages = ceil($total_rows / $filter['items_per_page']);
				$pagination = paginate_links( array(
					'format' => '?page=%#%',
					'current' =>  $current_page,
					'total' => $total_pages
				) );
			}

			break; // end case mathematics
	}
?>
<?php get_dict_header('Admin Homework Creator') ?>
<?php get_dict_page_title('Admin Homework Creator', 'admin-page', '', $tab_options) ?>

	<form method="post" action="<?php echo home_url() . '/?r=admin-homework-creator/' . $active_tab; echo $layout == 'create' ? '&amp;layout=create' : '' ?><?php echo $cid ? '&amp;cid=' . $cid : '' ?>" id="main-form" enctype="multipart/form-data">

		<?php switch($active_tab) : 

			case 'english': ?>

				<?php if($layout != 'create') : ?>

					<div class="row">
						<div class="col-sm-12">
							<h2 class="title-border">Available Worksheets</h2>
						</div>
						<div class="col-sm-5 col-md-4 col-sm-offset-7 col-md-offset-8">
							<div class="form-group">
								<a href="<?php echo home_url() ?>/?r=admin-homework-creator&amp;layout=create" class="btn btn-default orange form-control"><span class="icon-plus"></span>Create Worksheet</a>
							</div>
						</div>
						<div class="col-sm-12">
							<div class="box box-sapphire">
								<div class="row box-header">
									<div class="col-sm-3 col-sm-offset-4">
										<?php MWHtml::select_languages($filter['lang'], array('first_option' => '-Language-', 'name' => 'filter[lang]', 'class' => 'select-sapphire form-control')) ?>
									</div>
									<div class="col-sm-3">
										<button name="task[active]" type="submit" class="btn btn-default btn-block grey form-control">Active/Deactive</button>
									</div>
									<div class="col-sm-2">
										<button type="button" id="conf-del-btn" class="btn btn-default btn-block grey form-control">Remove</button>
									</div>
									<div class="col-sm-12">
										<div class="row search-tools">
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
													<?php MWHtml::sel_homework_types($filter['homework-types'],
															array('first_option' => __('-Homework Type-', 'iii-dictionary'),
																'name' => 'filter[homework-types]', 'class' => 'select-sapphire form-control',
																'id' => 'filter-homework-types', 'subscribed_option' => true,
																'admin_panel' => true)
													) ?>
												</div>
											</div>
											<div class="col-sm-3">
												<div class="form-group">
													<?php MWHtml::select_grades('ENGLISH', $filter['grade'], array('class' => 'select-sapphire form-control', 'name' => 'filter[grade]')) ?>
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
														<th class="hidden-xs" style="min-width: 80px">
															<a href="#" class="sortable<?php echo $filter['orderby'] == 'grade' ? ' ' . $filter['order-dir'] : '' ?>" data-sort-by="grade">Grade <span class="sorting-indicator"></span></a>
														</th>
														<th>
															<a href="#" class="sortable<?php echo $filter['orderby'] == 'sheet_name' ? ' ' . $filter['order-dir'] : '' ?>" data-sort-by="sheet_name">Sheet Name <span class="sorting-indicator"></span></a>
														</th>
														<th class="hidden-xs">Dictionary</th>
														<th class="hidden-xs">Type</th>
														<th></th>
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
																<a href="<?php echo home_url() ?>/?r=admin-homework-creator&amp;layout=create&amp;cid=<?php echo $sheet->id ?>" title="Edit this sheet" class="btn btn-default btn-block btn-tiny grey">Edit</a>
																<?php if($sheet->assignment_id != ASSIGNMENT_SPELLING && $sheet->assignment_id != ASSIGNMENT_VOCAB_BUILDER && $sheet->assignment_id != ASSIGNMENT_REPORT) : ?>
																	<button type="button" class="btn btn-default btn-block btn-tiny grey preview-btn">Preview</button>
																<?php endif ?>
																<button type="button" class="btn btn-default btn-block btn-tiny grey worksheet-details-btn"><?php _e('Details', 'iii-dictionary') ?></button>
																<div class="hidden"><?php echo $sheet->description ?></div>
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
							<button type="submit" name="export" class="btn btn-default btn-block grey form-control">Export</button>
						</div>
						<div class="col-sm-5 col-md-4">
							<div class="form-group"></div>
							<a href="<?php echo home_url() ?>/?r=admin-homework-creator&amp;layout=create" class="btn btn-default btn-block orange form-control"><span class="icon-plus"></span>Create Worksheet</a>
						</div>
					</div>

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
			<div class="col-sm-12" style="display: none" id="passage-block">
				<div class="form-group">
					<label>Passage</label>
					<div id="reading-passage-box" class="scroll-list" style="max-height: 200px">
						<div id="reading-passage"></div>
					</div>
				</div>
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
		</div>
		<input type="hidden" id="current-row" value="1">
		<input type="hidden" id="current-assignment" value="">
	  </div>
	</div>
  </div>
</div>

<div class="modal fade modal-red-brown" id="worksheet-details-modal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <a href="#" data-dismiss="modal" aria-hidden="true" class="close"></a>
        <h3 class="modal-title">Worksheet details</h3>
      </div>
      <div class="modal-body">
		<label>Worksheet Description</label>
		<div class="box">
			<div class="scroll-list" style="max-height: 350px">
				<div id="hw-desc"></div>
			</div>
		</div>
	  </div>
    </div>
  </div>
</div>
				<?php else : ?>

					<div class="row">
						<div class="col-md-12">
							<h2 class="title-border"><?php echo $cid ? 'Update' : 'Create new' ?> Worksheet</h2>
						</div>
						<div class="col-md-5">
							<div class="row">
								<div class="col-xs-6">
									<div class="form-group">
										<label>Assignments</label>
										<?php $assignment_html = MWHtml::sel_assignments($data['assignment-id'], true, $data['questions']) ?>
									</div>
								</div>
								<div class="col-xs-6">
									<div class="form-group">
										<label>Homework Types</label>
										<?php MWHtml::sel_homework_types($data['homework-types'],
												array('first_option' => 'Select one', 'subscribed_option' => true, 'admin_panel' => true)
										) ?>
									</div>
								</div>
								<div class="col-xs-6">
									<div class="form-group">
										<label>Sheet Categories</label>
										<?php MWHtml::sel_sheet_categories($data['sheet-categories']) ?>
									</div>
								</div>
								<div class="col-xs-6">
									<div class="form-group">
										<label>&nbsp;</label>
										<div class="checkbox">
											<label>
												<input type="checkbox" value="1" name="trivia-exclusive"<?php echo $data['trivia-exclusive'] ? ' checked' : '' ?>> Trivia exclusive
											</label>
										</div>
									</div>
								</div>
								<div class="col-xs-12">
									<div class="form-group">
										<label for="grade">Grade</label>
										<?php MWHtml::select_grades('ENGLISH', $data['grade'], array('id' => 'grade', 'name' => 'grade', 'first_option' => 'Select Grade')) ?>
									</div>					
								</div>
								<div class="col-xs-12">
									<div class="form-group">
										<label for="sheet-name">Sheet Name</label>
										<input type="text" class="form-control" id="sheet-name" name="sheet-name" value="<?php echo $data['sheet-name'] ?>">
									</div>
								</div>
								<div class="col-xs-6 form-group">
									<label>Language</label>
									<?php MWHtml::select_languages($data['lang'], array('name' => 'lang')) ?>
								</div>
								<div class="col-xs-6" id="grading-price-block"<?php echo $data['assignment-id'] == ASSIGNMENT_WRITING ? '' : ' style="display: none"' ?>>
									<div class="form-group">
										<label for="grading-price">Price</label>
										<input type="number" class="form-control" id="grading-price" name="grading-price" value="<?php echo $data['grading-price'] ?>">
									</div>
								</div>
								<div class="clearfix"></div>
								<div class="col-xs-12 col-md-6">
									<div class="form-group">
										<label for="imported-file">Import from a file</label>
										<input type="text" class="form-control" id="imported-file" name="imported-file" value="" readonly>
									</div>					
								</div>
								<div class="col-xs-12 col-md-6">
									<div class="form-group">
										<label>&nbsp;</label>
										<span class="btn btn-default btn-block grey btn-file">
											<span class="icon-browse"></span>Browse
											<input name="input-file" id="input-file" type="file">
										</span>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-7">
							<div class="box">
								<section id="sheets-list">
									<div class="row box-header">
										<div class="col-xs-12">
											<h3>Max. 20 lines per sheet</h3>
										</div>
									</div>
									<div class="row">
										<div class=" col-xs-12 scroll-list">
											<div class="loading-overlay"></div>
											<table class="table table-striped no-padding sheet-editor" id="sheet"><?php echo $assignment_html['html'] ?></table>
										</div>
									</div>
									<div class="row box-footer">
										<div class="col-xs-6 col-sm-4 col-md-6">														
												<label class="sr-only">Select a dictionary to use</label>
												<?php MWHtml::select_dictionaries($data['dictionary'], false, 'dictionary', 'sel-dictionary', 'form-control') ?>														
										</div>
										<div class="col-xs-5 col-sm-4 col-md-5 col-sm-offset-3 col-md-offset-0">
											<button type="button" id="check-word" class="btn btn-default btn-block btn-tiny sky-blue form-control" data-loading-text="Checking...">Check words</button>
										</div>
									</div>
								</section>
							</div>
						</div>										
					</div>

					<div class="row">
						<?php
							$editor_settings = array(
								'wpautop' => false,
								'media_buttons' => false,
								'quicktags' => false,
								'textarea_rows' => 7,
								'tinymce' => array(
									'toolbar1' => 'bold,italic,strikethrough,image,bullist,numlist,blockquote,hr,alignleft,aligncenter,alignright,spellchecker,fullscreen,wp_adv'
								)
							);
						?>

						<div class="col-sm-12" id="reading-passage-block" style="display: none">
							<div class="form-group">
								<label>Passage</label>
								<?php wp_editor($data['reading_passage'], 'reading_passage', $editor_settings) ?>
							</div>
						</div>

						<div class="col-sm-12">
							<div class="form-group">
								<label>Description of Homework</label>
								<?php wp_editor($data['description'], 'description', $editor_settings) ?>
							</div>
						</div>

						<div class="col-sm-6">
							<div class="form-group">
								<?php if($cid) : ?>
									<button type="submit" name="task[update]" class="btn btn-default btn-block orange"><span class="icon-save"></span>Update worksheet</button>
								<?php else : ?>
									<button type="submit" name="task[create]" class="btn btn-default btn-block orange"><span class="icon-plus"></span>Create a new worksheet</button>
								<?php endif ?>
							</div>
						</div>
						<div class="col-sm-6">
							<div class="form-group">
								<a href="<?php echo home_url() ?>/?r=admin-homework-creator/english" class="btn btn-default btn-block grey"><span class="icon-goback"></span>Go back</a>
							</div>
						</div>
					</div>

					<input type="hidden" id="wordchecked" name="wordchecked" value="0">
					<input type="hidden" id="recheck" name="recheck" value="0">
					<input type="hidden" id="cid" name="sid" value="<?php echo $cid ?>">

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

			<?php break; // end english case

			case 'mathematics': ?>

				<?php if($layout != 'create') : ?>

					<div class="row">
						<div class="col-sm-12">
							<h2 class="title-border">Available Math Worksheets</h2>
						</div>
						<div class="col-sm-5 col-md-4 col-sm-offset-7 col-md-offset-8">
							<div class="form-group">
								<a href="<?php echo home_url() ?>/?r=admin-homework-creator/mathematics&amp;layout=create" class="btn btn-default orange form-control"><span class="icon-plus"></span>Create Worksheet</a>
							</div>
						</div>
						<div class="col-sm-12">
							<div class="box box-sapphire">
								<div class="row box-header">
									<div class="col-sm-3 col-sm-offset-4">
										<?php MWHtml::select_languages($filter['lang'], array('first_option' => '-Language-', 'name' => 'filter[lang]', 'class' => 'select-sapphire form-control')) ?>
									</div>
									<div class="col-sm-3">
										<button name="task[active]" type="submit" class="btn btn-default btn-block grey form-control">Active/Deactive</button>
									</div>
									<div class="col-sm-2">
										<button type="button" id="conf-del-btn" class="btn btn-default btn-block grey form-control">Remove</button>
									</div>
									<div class="col-sm-12">
										<div class="row search-tools">
											<div class="col-sm-6">
												<div class="form-group">
													<input type="text" id="filter-sheet-name" name="filter[sheet-name]" class="form-control" placeholder="Sheet Name" value="<?php echo $filter['sheet-name'] ?>">
												</div>
											</div>
											<div class="col-sm-3">
												<div class="form-group">
													<?php MWHtml::sel_homework_types($filter['homework-types'],
															array('first_option' => __('-Homework Type-', 'iii-dictionary'),
																'name' => 'filter[homework-types]', 'class' => 'select-sapphire form-control',
																'id' => 'filter-homework-types', 'subscribed_option' => true,
																'admin_panel' => true)
													) ?>
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
											<div class="col-sm-4 form-group">
												<select class="select-box-it select-sapphire form-control" name="filter[cat-level]" id="filter-level-categories">
													<option value="">-Level Category-</option>
													<?php foreach($main_categories as $item) : ?>
														<option value="<?php echo $item->id ?>"<?php echo $filter['cat-level'] == $item->id ? ' selected' : '' ?>><?php echo $item->name ?></option>
													<?php endforeach ?>
												</select>
											</div>
											<div class="col-sm-5 form-group">
												<?php MWHtml::sel_math_assignments($filter['assignment-id'], array('first-option' => '-Worksheet Format-', 'name' => 'filter[math-assignments]', 'id' => 'math-assignments', 'class' => 'select-sapphire')) ?>
											</div>
											<div class="col-sm-3">
												<div class="form-group">
													<button type="submit" class="btn btn-default sky-blue form-control" name="filter[search]" id="search-btn">Search</button>
												</div>
											</div>
											<div class="col-sm-6 form-group">
												<select class="select-box-it select-sapphire form-control" name="filter[level]" id="filter-levels" data-selected="<?php echo $filter['level'] ?>">
													<option value="">-Level-</option>
												</select>
												<?php echo $sel_levels_html ?>
											</div>
											<div class="col-sm-6 form-group">
												<select class="select-box-it select-sapphire form-control" id="filter-sublevels" name="filter[sublevel]" data-selected="<?php echo $filter['sublevel'] ?>">
													<option value="">-Sublevel-</option>
												</select>
												<?php echo $sel_sublevels_html ?>
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
														<th class="hidden-xs">Level Category</th>
														<th class="hidden-xs">
															<a href="#" class="sortable<?php echo $filter['orderby'] == 'level_name' ? ' ' . $filter['order-dir'] : '' ?>" data-sort-by="level_name">Level <span class="sorting-indicator"></span></a>
														</th>
														<th class="hidden-xs">
															<a href="#" class="sortable<?php echo $filter['orderby'] == 'sublevel_name' ? ' ' . $filter['order-dir'] : '' ?>" data-sort-by="sublevel_name">Sublevel <span class="sorting-indicator"></span></a>
														</th>
														<th>
															<a href="#" class="sortable<?php echo $filter['orderby'] == 'sheet_name' ? ' ' . $filter['order-dir'] : '' ?>" data-sort-by="sheet_name">Sheet Name <span class="sorting-indicator"></span></a>
														</th>
														<th style="width: 100px">
															<a href="#" class="sortable<?php echo $filter['orderby'] == 'ordering' ? ' ' . $filter['order-dir'] : '' ?>" data-sort-by="ordering">Ordering <span class="sorting-indicator"></span></a>
														</th>
														<th></th>
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
															<td class="hidden-xs"><?php echo $sheet->level_category_name ?></td>
															<td><?php echo $sheet->level_name ?></td>
															<td class="hidden-xs"><?php echo $sheet->sublevel_name ?></td>
															<td class="hidden-xs"><?php echo $sheet->sheet_name ?></td>
															<td>
																<button type="submit" name="order-up" class="btn btn-micro grey change-order" data-id="<?php echo $sheet->id ?>"><span class="icon-uparrow"></span></button>
																<button type="submit" name="order-down" class="btn btn-micro grey change-order" data-id="<?php echo $sheet->id ?>"><span class="icon-downarrow"></span></button>
																<span class="ordering"><?php echo $sheet->ordering ?></span>
															</td>
															<td>
																<a href="<?php echo home_url() ?>/?r=admin-homework-creator/mathematics&amp;layout=create&amp;cid=<?php echo $sheet->id ?>" class="btn btn-default btn-block btn-tiny grey">Edit</a>
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
							<a href="<?php echo home_url() ?>/?r=admin-homework-creator/mathematics&amp;layout=create" class="btn btn-default btn-block orange form-control"><span class="icon-plus"></span>Create Worksheet</a>
						</div>
					</div>

				<?php else : ?>

					<div class="row">
						<div class="col-sm-12">
							<h2 class="title-border"><?php echo $cid ? 'Update' : 'Create new' ?> Worksheet</h2>
						</div>
						<div class="col-sm-6 form-group">
							<label>Assignments</label>
							<?php MWHtml::sel_math_assignments($data['assignment_id']) ?>
						</div>
						<div class="col-sm-6 form-group">
							<label>Level Category</label>
							<select class="select-box-it form-control" name="level-category" id="sel-level-categories">
								<option value="">Select one</option>
								<?php foreach($main_categories as $item) : ?>
									<option value="<?php echo $item->id ?>"<?php echo $sel_level_category == $item->id ? ' selected' : '' ?>><?php echo $item->name ?></option>
								<?php endforeach ?>
							</select>
						</div>
						<div class="col-sm-6 form-group">
							<label>Homework Types</label>
							<?php MWHtml::sel_homework_types($data['homework_type_id'],
									array('first_option' => 'Select one', 'subscribed_option' => true, 'admin_panel' => true)
							) ?>
						</div>
						<div class="col-sm-6 form-group">
							<label>Level</label>
							<select class="select-box-it form-control" name="level" id="sel-levels" data-selected="<?php echo $sel_level ?>"></select>
							<?php echo $sel_levels_html ?>
						</div>
						<div class="col-sm-6 form-group">
							<label for="sheet-name">Sheet Name</label>
							<input type="text" class="form-control" id="sheet-name" name="sheet-name" value="<?php echo $data['sheet_name'] ?>">
						</div>
						<div class="col-sm-6 form-group">
							<label>Sublevel</label>
							<select class="select-box-it form-control" id="sel-sublevels" name="sublevel" data-selected="<?php echo $data['sublevel_id'] ?>"></select>
							<?php echo $sel_sublevels_html ?>
						</div>
						<div class="col-sm-12 hidden" id="time-limit-block">
							<div class="row form-group">
								<div class="col-sm-6">
									<label>Time limit (Homework mode) <small>(Seconds)</small></label>
									<input type="number" class="form-control" name="answer-time-limit" value="<?php echo $data['answer_time_limit'] ?>">
								</div>
								<div class="col-sm-6">
									<label>Time limit (Practice mode) <small>(Seconds)</small></label>
									<input type="number" class="form-control" name="show-answer-after" value="<?php echo $data['show_answer_after'] ?>">
								</div>
							</div>
						</div>
						<div class="col-sm-6">
							<div class="row">
								<div class="col-xs-6 form-group">
									<label for="imported-file-math">Import from a file</label>
									<input type="text" class="form-control" id="imported-file-math" name="imported-file" value="" readonly>
								</div>
								<div class="col-xs-6 form-group">
									<label>&nbsp;</label>
									<span class="btn btn-default btn-block grey btn-file">
										<span class="icon-browse"></span>Browse
										<input name="input-file" id="input-file-math" type="file">
									</span>
								</div>
							</div>
						</div>
						<div class="col-xs-6 form-group">
							<label>Language</label>
							<?php MWHtml::select_languages($data['lang'], array('name' => 'lang')) ?>
						</div>

						<div class="col-sm-12 form-group">
							<div class="box">
								<?php MWHtml::math_worksheet_form($data['questions']) ?>
							</div>
						</div>

						<div class="col-sm-12 form-group">
							<?php
								$editor_settings = array(
									'wpautop' => false,
									'media_buttons' => false,
									'quicktags' => false,
									'textarea_rows' => 7,
									'tinymce' => array(
										'toolbar1' => 'bold,italic,strikethrough,image,bullist,numlist,blockquote,hr,alignleft,aligncenter,alignright,spellchecker,fullscreen,wp_adv'
									)
								);
							?>
							<label>Description of Homework</label>
							<?php wp_editor($data['description'], 'description', $editor_settings) ?>
						</div>

						<div class="col-sm-6 form-group">
							<?php if($cid) : ?>
								<button type="submit" name="task[update]" class="btn btn-default btn-block orange cache-form"><span class="icon-save"></span>Update worksheet</button>
							<?php else : ?>
								<button type="submit" name="task[create]" class="btn btn-default btn-block orange cache-form"><span class="icon-plus"></span>Create a new worksheet</button>
							<?php endif ?>
						</div>
						<div class="col-sm-6 form-group">
							<a href="<?php echo home_url() ?>/?r=admin-homework-creator/mathematics" class="btn btn-default btn-block grey"><span class="icon-goback"></span>Go back</a>
						</div>
						<input type="hidden" name="cid" id="cid" value="<?php echo $cid ?>">
					</div>

				<?php endif ?>

			<?php break; // end mathematics case

		endswitch ?>

<div class="modal fade modal-red-brown" id="confirm-deletion-modal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog">
	<div class="modal-content">
	  <div class="modal-header">
		<a href="#" data-dismiss="modal" aria-hidden="true" class="close"></a>
		<h3 class="modal-title" id="myModalLabel">Worksheet Deletion</h3>
	  </div>
	  <div class="modal-body"></div>
	  <div class="modal-footer">
		<div class="row">
			<div class="col-sm-6">
				<button type="submit" name="task[remove]" class="btn btn-block orange"><span class="icon-accept"></span>Yes</button>
			</div>
			<div class="col-sm-6">
				<a href="#" data-dismiss="modal" aria-hidden="true" class="btn btn-block grey"><span class="icon-cancel"></span>No</a>
			</div>
		</div>
	  </div>
	</div>
  </div>
</div>

		<input type="hidden" name="oid" id="oid">
		<input type="hidden" name="filter[orderby]" id="filter-order" value="<?php echo $filter['orderby'] ?>">
		<input type="hidden" name="filter[order-dir]" id="filter-order-dir" value="<?php echo $filter['order-dir'] ?>">
	</form>

<?php echo $assignment_html['js'] ?>

<table id="questions-table" style="display: none"></table>

<script>var _PRELOAD<?php if($layout == 'create') : ?> = <?php echo $cid ? 0 : 1 ?><?php endif ?>;</script>

<?php get_dict_footer() ?>