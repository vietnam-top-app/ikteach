<?php
wp_register_script('csv-js', get_stylesheet_directory_uri() . '/library/js/jquery.csv-0.71.min.js', array('jquery'));
wp_enqueue_script('csv-js');

$layout = isset($_GET['layout']) ? $_GET['layout'] : '';
$cid = isset($_GET['cid']) && is_numeric($_GET['cid']) ? $_GET['cid'] : 0;
$current_user_id = get_current_user_id();
$actual_link_current = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$actual_link = strstr($actual_link_current, 'mathematics');
$is_admin = is_mw_super_admin() || is_mw_admin() ? true : false;
$is_math_panel = is_math_panel();
$_page_title = __('Assign Homework', 'iii-dictionary');
$is_teaching_agreement_uptodate_math = ik_is_teacher_agreement_uptodate('MATH');
$is_teaching_agreement_uptodate_english = ik_is_teacher_agreement_uptodate();
$is_mw_registered_teacher = is_mw_registered_teacher();
$is_mw_registered_teacher_math = is_mw_registered_teacher($current_user_id, 1);
$is_qualified_teacher = is_mw_qualified_teacher();
$is_qualified_teacher_math = is_mw_qualified_teacher($current_user_id, 1);
$route = get_route();
if (empty($route[1])) {
    $active_tab = 'english';
    $_page_title = __('Assign English Homework', 'iii-dictionary');
} else {
    $active_tab = $route[1];
    $_page_title = __('Assign Math Homework', 'iii-dictionary');
}

$tab_options = array(
    'items' => array(
        'english' => array('url' => home_url() . '/?r=homework-assignment/english', 'text' => 'English'),
        'mathematics' => array('url' => home_url() . '/?r=homework-assignment/mathematics', 'text' => 'Mathematics')
    ),
    'active' => $active_tab
);

$tab_options['items'] = array(
    'english' => array('url' => home_url() . '/?r=homework-assignment/english', 'text' => 'English'),
    'mathematics' => array('url' => home_url() . '/?r=homework-assignment/mathematics', 'text' => 'Math')
);

// process task
$task = isset($_POST['task']) ? $_POST['task'] : '';
// override $task if set in query string
$task = isset($_GET['task']) ? $_GET['task'] : $task;

// assign homework
if (isset($task['register'])) {
    if (isset($_REQUEST['checkboxpage'])) {
        $data['teacherlastpage'] = $_REQUEST['checkboxpage'];
    } else {
        $data['teacherlastpage'] = 0;
    }
    $data['name'] = $_REAL_POST['homework-name'];
    $data['deadline'] = $_REAL_POST['deadline'];
    $data['for_practice'] = $_REAL_POST['hw-for-practice'];
    $data['sheet_id'] = $_REAL_POST['sheet-id'];
    $data['group'] = $_REAL_POST['group'];
    $data['is_retryable'] = $_POST['is-retryable'];
session_start();
    $_SESSION['grouptype']=$_REAL_POST['grouptype'];
    $_SESSION['groupclass']=$_REAL_POST['groupclass'];
    $_SESSION['group']=$_REAL_POST['group'];
    if ($actual_link == 'mathematics') {
        $checktool = user_can_assign_homework($data['group'], 6);
    } else {
        $checktool = user_can_assign_homework($data['group'], 1);
    }
    // checking subscription
    if (!$checktool) {
        ik_enqueue_messages(
                __('You need to subscribe Homework Tool for this group before sending homework.', 'iii-dictionary')
                . '<br><br>
				<div class="row">
					<div class="col-sm-6 form-group">
						<a href="' . locale_home_url() . '/?r=manage-subscription#1" class="btn btn-default btn-block orange"><span class="icon-check"></span>' . __('Subscribe', 'iii-dictionary') . '</a>
					</div>
					<div class="col-sm-6 form-group">
						<a href="#" data-dismiss="modal" aria-hidden="true" class="btn btn-default btn-block grey"><span class="icon-cancel"></span>' . __('No', 'iii-dictionary') . '</a>
					</div>
				</div>'
                , 'error');
    } else {
        if (MWDB::assign_homework($data)) {
            ik_enqueue_messages(__('Successfully assigned homework.', 'iii-dictionary'), 'success');
        } else {
            ik_enqueue_messages(__('Can not assign homework.', 'iii-dictionary'), 'error');
        }

        wp_redirect(locale_home_url() . '/?r=homework-assignment/' . $active_tab);
        exit;
    }
}

switch ($active_tab) {
    // english homework
    case 'english':
        if (is_mw_registered_teacher) {
            $data = array();
            $data['assignment-id'] = ASSIGNMENT_SPELLING;
            $sel_custom = '';

            // create or update homework
            if (isset($task['create']) || isset($task['update'])) {
                $data['id'] = $_REAL_POST['sheet-id'];
                $data['assignment-id'] = $_REAL_POST['assignments'];
                $data['sheet-categories'] = 1; // Default to English // $_REAL_POST['sheet-categories'];
                $data['grade'] = $_REAL_POST['grade'];
                $data['sheet-name'] = $_REAL_POST['sheet-name'];
                $data['dictionary'] = $_REAL_POST['dictionary'];
                $data['questions'] = $_REAL_POST['words'];
                $data['reading_passage'] = $_REAL_POST['reading_passage'];
                $data['description'] = $_REAL_POST['description'];
                $data['wordchecked'] = $_REAL_POST['wordchecked'];
                $data['lang'] = !empty($_POST['lang']) ? $_POST['lang'] : 'en';
                if (isset($task['create'])) {
                    $data['homework-types'] = HOMEWORK_MY_OWN;
                }
                if ($data['assignment-id'] == ASSIGNMENT_REPORT) {
                    $data['questions'] = $_REAL_POST['report_assignment'];
                }

                $sel_custom = $_REAL_POST['sel-custom'];

                if (MWDB::store_sheet($data)) {
                    wp_redirect(locale_home_url() . '/?r=homework-assignment/english');
                    exit;
                } else {
                    if ($_REAL_POST['sheet-id']) {
                        wp_redirect(locale_home_url() . '/?r=homework-assignment/english&layout=create&cid=' . $_REAL_POST['sheet-id']);
                        exit;
                    }
                }
            }

            if (isset($task['register-flashcards'])) {
                $data['id'] = $_REAL_POST['sheet-id'];
                $data['assignment-id'] = $_REAL_POST['assignments'];
                $data['sheet-categories'] = 1; // Default to English // $_REAL_POST['sheet-categories'];
                $data['dictionary'] = $_REAL_POST['dictionary'];
                $data['questions'] = $_REAL_POST['words'];
                $data['wordchecked'] = $_REAL_POST['wordchecked'];
                $data['group'] = $_REAL_POST['group'];
                $data['header-name'] = esc_html($_REAL_POST['header-name']);
                $data['comments'] = esc_html($_REAL_POST['comments']);

                if ($data['group'] == '') {
                    ik_enqueue_messages(__('Please select a Group', 'iii-dictionary'), 'error');
                } else {
                    // checking subscription
                    if (!user_can_assign_homework($data['group'])) {
                        ik_enqueue_messages(
                                __('You need to subscribe Homework Tool for this group before sending flashcards.', 'iii-dictionary')
                                . '<br><br>
							<div class="row">
								<div class="col-sm-6 form-group">
									<a href="' . locale_home_url() . '/?r=manage-subscription#1" class="btn btn-default btn-block orange"><span class="icon-check"></span>Subscribe</a>
								</div>
								<div class="col-sm-6 form-group">
									<a href="#" data-dismiss="modal" aria-hidden="true" class="btn btn-default btn-block grey"><span class="icon-cancel"></span>No</a>
								</div>
							</div>'
                                , 'error');
                    } else {
                        if (MWDB::assign_teacher_flashcards($data)) {
                            wp_redirect(locale_home_url() . '/?r=homework-assignment/english');
                            exit;
                        }
                    }
                }
            }

            // delete sheet. Currently unused
            if ($task == 'remove') {
                $cid = $_GET['cid'];

                if (!isset($_GET['fset'])) {
                    MWDB::delete_sheets($cid, true);
                } else {
                    MWDB::delete_teacher_flashcard_set($cid);
                }

                wp_redirect(locale_home_url() . '/?r=homework-assignment/english');
                exit;
            }

            // page content
            if ($cid) {
                if (!isset($_GET['fset'])) {
                    $current_sheet = $wpdb->get_row($wpdb->prepare(
                                    'SELECT s.*, gr.name AS grade
						 FROM ' . $wpdb->prefix . 'dict_sheets AS s
						 JOIN ' . $wpdb->prefix . 'dict_grades AS gr ON gr.id = s.grade_id
						 WHERE s.id = %s', $cid
                    ));

                    $data['assignment-id'] = $current_sheet->assignment_id;
                    $data['sheet-categories'] = $current_sheet->category_id;
                    $data['grade'] = $current_sheet->grade;
                    $data['sheet-name'] = $current_sheet->sheet_name;
                    $data['dictionary'] = $current_sheet->dictionary_id;
                    $data['questions'] = $data['assignment-id'] == ASSIGNMENT_REPORT ? $current_sheet->questions : json_decode($current_sheet->questions, true);
                    $data['reading_passage'] = $current_sheet->passages;
                    $data['description'] = $current_sheet->description;
                } else {
                    $current_set = $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . $wpdb->prefix . 'dict_flashcard_teacher_sets WHERE id = %s', $cid));
                    $flashcards = $wpdb->get_results('SELECT dictionary_id, word, teacher_sentence FROM ' . $wpdb->prefix . 'dict_flashcards WHERE teacher_set_id = ' . $current_set->id);

                    $data['assignment-id'] = ASSIGNMENT_VOCAB_BUILDER;
                    $data['dictionary'] = $flashcards[0]->dictionary_id;
                    $data['group'] = $current_set->group_id;
                    $w = array();
                    foreach ($flashcards as $card) {
                        $w[] = $card->word;
                        $s[] = $card->teacher_sentence;
                    }
                    $data['questions'] = array('word' => $w, 'sentence' => $s);
                    $data['header-name'] = $current_set->header_name;
                    $data['comments'] = $current_set->comments;
                }

                if ((!is_null($current_sheet) && $current_user_id != $current_sheet->created_by) || (!is_null($current_set) && $current_user_id != $current_set->teacher_id)) {
                    ik_enqueue_messages(__('You don\'t have permission to edit this Homework.', 'iii-dictionary'), 'error');
                    wp_redirect(locale_home_url() . '/?r=homework-assignment/english');
                    exit;
                }
            }

            $current_page = max(1, get_query_var('page'));
            $filter = get_page_filter_session();

            if (empty($filter) && !isset($_POST['filter'])) {
                $filter['orderby'] = 'grade';
                $filter['order-dir'] = 'asc';
                $filter['grade'] = $filter['assignment-id'] = $filter['homework-types'] = '';
                $filter['items_per_page'] = 20;
                $filter['offset'] = $filter['items_per_page'] * ($current_page - 1);
            } else {
                if (isset($_POST['filter']['search'])) {
                    $filter['sheet-name'] = $_REAL_POST['filter']['sheet-name'];
                    $filter['grade'] = $_POST['filter']['grade'];
                    $filter['assignment-id'] = $_POST['filter']['assignment-id'];
                    $filter['homework-types'] = $_POST['filter']['homework-types'];
                }

                if (isset($_REAL_POST['filter']['orderby'])) {

                    $filter['orderby'] = $_REAL_POST['filter']['orderby'];
                    $filter['order-dir'] = $_REAL_POST['filter']['order-dir'];
                }

                $filter['offset'] = $filter['items_per_page'] * ($current_page - 1);
            }

            set_page_filter_session($filter);

            $filter['offset'] = 0;
            $filter['items_per_page'] = 99999999;
            $sheets_obj = MWDB::get_sheets($filter, true);
            if (empty($filter['assignment-id']) || $filter['assignment-id'] == ASSIGNMENT_VOCAB_BUILDER) {
                $flashcard_obj = MWDB::get_flashcard_sets($filter);
                $sheets_obj->items = array_merge($sheets_obj->items, $flashcard_obj->items);
            }

            $avail_sheets = $sheets_obj->items;
            $total_rows = $sheets_obj->total;

            $total_pages = ceil($total_rows / $filter['items_per_page']);
            $pagination = paginate_links(array(
                'format' => '?page=%#%',
                'current' => $current_page,
                'total' => $total_pages
            ));

            if ($layout != 'create') {
                $info_tab_url = array(
                    get_info_tab_cloud_url('Popup_info_18.jpg')
                );
            } else {
                $info_tab_url = array(
                    ASSIGNMENT_SPELLING => get_info_tab_cloud_url('Popup_info_20.jpg'),
                    ASSIGNMENT_VOCAB_GRAMMAR => get_info_tab_cloud_url('Popup_info_21.jpg'),
                    ASSIGNMENT_READING => get_info_tab_cloud_url('Popup_info_22.jpg'),
                    ASSIGNMENT_WRITING => get_info_tab_cloud_url('Popup_info_23.jpg'),
                    ASSIGNMENT_VOCAB_BUILDER => get_info_tab_cloud_url('Popup_info_24.jpg'),
                    ASSIGNMENT_REPORT => get_info_tab_cloud_url('Popup_info_19.jpg')
                );
            }

            break; // end case english
        } else {
            $title = __('Registration Required', 'iii-dictionary');
            $body = __('Please register as the teacher before assign homework in this panel.', 'iii-dictionary');
            $return_url = locale_home_url() . '/?r=my-account#4';


            set_lockpage_dialog($title, $body, $return_url);
        }
    // Math homework
    case 'mathematics':
        if (is_mw_registered_teacher($current_user_id, 1)) {
            $main_categories = MWDB::get_grades(array('type' => 'MATH', 'level' => 0, 'admin_only' => 1, 'orderby' => 'ordering', 'order-dir' => 'asc'));
            $levels = MWDB::get_grades(array('type' => 'MATH', 'level' => 1, 'orderby' => 'ordering', 'order-dir' => 'asc'));
            $sublevels = MWDB::get_grades(array('type' => 'MATH', 'level' => 2, 'orderby' => 'ordering', 'order-dir' => 'asc'));

            $sel_levels_html = '';
            foreach ($main_categories as $item) {
                $sel_levels_html .= '<select class="hidden" id="_l' . $item->id . '">';
                foreach ($levels as $level) {
                    if ($level->parent_id == $item->id) {
                        $sel_levels_html .= '<option value="' . $level->id . '">' . $level->name . '</option>';
                    }
                }
                $sel_levels_html .= '</select>';
            }

            $sel_sublevels_html = '';
            foreach ($levels as $level) {
                $sel_sublevels_html .= '<select class="hidden" id="_sl' . $level->id . '">';
                foreach ($sublevels as $sublevel) {
                    if ($sublevel->parent_id == $level->id) {
                        $sel_sublevels_html .= '<option value="' . $sublevel->id . '">' . $sublevel->name . '</option>';
                    }
                }
                $sel_sublevels_html .= '</select>';
            }

            // page content
            if ($cid) { // view a sheet
                $current_sheet = MWDB::get_math_sheet_by_id($cid);

                $data['assignment_id'] = $current_sheet->assignment_id;
                $data['homework_type_id'] = $current_sheet->homework_type_id;
                $data['sublevel_id'] = $current_sheet->grade_id;
                $data['sheet_name'] = $current_sheet->sheet_name;
                $data['questions'] = json_decode($current_sheet->questions, true);
                $data['description'] = $current_sheet->description;
                $sel_level_category = $current_sheet->category_level_id;
                $sel_level = $current_sheet->level_id;
            } else { // sheet list
                $current_page = max(1, get_query_var('page'));
                $filter = get_page_filter_session();
                if (empty($filter) && !isset($_POST['filter'])) {
                    $filter['orderby'] = 'level_name';
                    $filter['order-dir'] = 'asc';
                    $filter['items_per_page'] = 20;
                    $filter['offset'] = $filter['items_per_page'] * ($current_page - 1);
                } else {
                    if (isset($_REAL_POST['filter']['search'])) {
                        $filter['sheet-name'] = $_REAL_POST['filter']['sheet-name'];
                        $filter['assignment-id'] = $_REAL_POST['filter']['math-assignments'];
                        $filter['homework-types'] = $_REAL_POST['filter']['homework-types'];
                        $filter['active'] = $_REAL_POST['filter']['active'];
                        $filter['cat-level'] = $_REAL_POST['filter']['cat-level'];
                        $filter['level'] = $_REAL_POST['filter']['level'];
                        $filter['sublevel'] = $_REAL_POST['filter']['sublevel'];
                    }

                    if (isset($_REAL_POST['filter']['orderby'])) {
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
                $pagination = paginate_links(array(
                    'format' => '?page=%#%',
                    'current' => $current_page,
                    'total' => $total_pages
                ));
            }

            break; // end case mathematics
        } else {
            $title = __('Registration Required', 'iii-dictionary');
            $body = __('Please register as the teacher before assign homework in this panel.', 'iii-dictionary');
            $return_url = locale_home_url() . '/?r=my-account#4';

            set_lockpage_dialog($title, $body, $return_url);
        }
}
$class_types = MWDB::get_group_class_types();
$group_list = MWDB::get_groups(array('created_by' => $current_user_id, 'state' => 1, 'group_type' => GROUP_FREE));
$group_list1 = MWDB::get_groups(array( 'state' => 1,'class_type'=> count($class_types)+1 ));
//var_dump($group_list1);die;
?>
<?php get_math_header($_page_title, 'red-brown') ?>


<?php get_dict_page_title($_page_title, '', '', $tab_options, array(), get_info_tab_cloud_url('Popup_info_18.jpg')) ?>

<form method="post" id="main-form" enctype="multipart/form-data" action="<?php
echo locale_home_url() . '/?r=homework-assignment/' . $active_tab;
echo $layout == 'create' ? '&amp;layout=create' : ''
?><?php echo $cid ? '&amp;cid=' . $cid : '' ?><?php echo isset($_GET['fset']) ? '&amp;fset=1' : '' ?>">

    <?php
    switch ($active_tab) :

        case 'english':
            ?>

            <?php if ($layout != 'create') : ?>

                <div class="row">
                    <div class="col-sm-12">
                        <h2 class="title-border"><?php _e('Available Worksheets', 'iii-dictionary') ?> <small><?php _e('(Select one to assign)', 'iii-dictionary') ?></small></h2>
                    </div>
                    <div class="col-sm-5 col-md-4 col-sm-offset-7 col-md-offset-8">
                        <div class="form-group">
                            <a href="<?php echo locale_home_url() ?>/?r=homework-assignment/english&amp;layout=create" class="btn btn-default orange form-control"><span class="icon-plus"></span><?php _e('Create my own', 'iii-dictionary') ?></a>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="box box-sapphire">
                            <div class="row box-header">
                                <div class="col-xs-12">
                                    <div class="row search-tools">
                                        <div class="col-xs-6">
                                            <div class="form-group">
                                                <input type="text" name="filter[sheet-name]" class="form-control" placeholder="<?php _e('Sheet Name', 'iii-dictionary') ?>" value="<?php echo $filter['sheet-name'] ?>">
                                            </div>
                                        </div>
                                        <div class="clearfix"></div>
                                        <div class="col-xs-6 col-sm-3">
                                            <div class="form-group">
                                                <?php MWHtml::select_grades('ENGLISH', $filter['grade'], array('class' => 'select-sapphire form-control', 'name' => 'filter[grade]')) ?>
                                            </div>
                                        </div>
                                        <div class="col-xs-6 col-sm-3">
                                            <div class="form-group">
                                                <?php MWHtml::sel_assignments($filter['assignment-id'], false, array(), __('-Lesson-', 'iii-dictionary'), 'filter[assignment-id]', 'select-sapphire form-control', 'filter-assignment') ?>
                                            </div>
                                        </div>
                                        <div class="col-xs-6 col-sm-3">
                                            <div class="form-group">
                                                <?php
                                                MWHtml::sel_homework_types($filter['homework-types'], array('first_option' => __('-Homework Type-', 'iii-dictionary'),
                                                    'name' => 'filter[homework-types]', 'class' => 'select-sapphire form-control',
                                                    'id' => 'filter-homework-types', 'subscribed_option' => true)
                                                )
                                                ?>
                                            </div>
                                        </div>
                                        <div class="col-xs-6 col-sm-3">
                                            <div class="form-group">
                                                <button type="submit" class="btn btn-default sky-blue form-control" name="filter[search]"><?php _e('Search', 'iii-dictionary') ?></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-12">
                                    <div class="scroll-list2" style="max-height: 600px">
                                        <table class="table table-striped table-condensed ik-table1 vertical-middle text-center" id="list-sheets">
                                            <thead>
                                                <tr>
                                                    <th class="hidden-xs"><?php _e('Lesson', 'iii-dictionary') ?></th>
                                                    <th class="hidden-xs" style="min-width: 80px">
                                                        <a href="#" class="sortable<?php echo $filter['orderby'] == 'grade' ? ' ' . $filter['order-dir'] : '' ?>" data-sort-by="grade"><?php _e('Subject', 'iii-dictionary') ?> <span class="sorting-indicator"></span></a>
                                                    </th>
                                                   
                                                    <th>
                                                        <a href="#" class="sortable<?php echo $filter['orderby'] == 'sheet_name' ? ' ' . $filter['order-dir'] : '' ?>" data-sort-by="sheet_name"><?php _e('Worksheet', 'iii-dictionary') ?> <span class="sorting-indicator"></span></a>
                                                    </th>
                                                     <th class="hidden-sm"><?php _e('Dictionary', 'iii-dictionary') ?></th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tfoot>
                                                <tr><td colspan="5"><?php echo $pagination ?></td></tr>
                                            </tfoot>
                                            <tbody><?php
                                                if (!empty($avail_sheets)) :
                                                    foreach ($avail_sheets as $sheet) :
                                                        switch ($sheet->homework_type_id) {
                                                            case HOMEWORK_CLASS :
                                                                $cannot_use_subscribed_worksheet = !is_sat_special_group();
                                                                break;
                                                            case HOMEWORK_SUBSCRIBED :
                                                                $cannot_use_subscribed_worksheet = !is_homework_tools_subscribed();
                                                                break;
                                                        }
                                                        ?><tr <?php echo $sheet->homework_type_id; ?> data-id="<?php echo $sheet->id ?>" data-assignment="<?php echo $sheet->assignment_id ?>"<?php echo $cannot_use_subscribed_worksheet ? ' class="text-muted" title="' . __('Require Homework Tool Subscrption', 'iii-dictionary') . '"' : '' ?>><?php ?><td class="hidden-xs"><?php echo $sheet->assignment ?></td><?php ?><td class="hidden-xs"><?php echo $sheet->grade ?></td><?php ?><td><?php echo $sheet->sheet_name ?></td><?php ?><td class="hidden-sm"><?php echo $sheet->name ?></td><?php ?><td><?php
                                                        // Edit button
                                                        if ($sheet->created_by == $current_user_id) {
                                                            if ($sheet->assignment_id == ASSIGNMENT_VOCAB_BUILDER) {
                                                                echo '<a href="' . locale_home_url() . '/?r=homework-assignment/english&amp;layout=create&amp;fset=1&amp;cid=' . $sheet->id . '" class="btn btn-default btn-block btn-tiny grey">' . __('Edit', 'iii-dictionary') . '</a>';
                                                                // 	<a href="' . locale_home_url() . '/?r=homework-assignment/english&amp;task=remove&amp;fset=1&amp;cid=' . $sheet->id . '" title="Delete this set">Delete</a>
                                                            } else {
                                                                echo '<a href="' . locale_home_url() . '/?r=homework-assignment/english&amp;layout=create&amp;cid=' . $sheet->id . '" class="btn btn-default btn-block btn-tiny grey">' . __('Edit', 'iii-dictionary') . '</a>';
                                                                // <a href="' . locale_home_url() . '/?r=homework-assignment/english&amp;task=remove&amp;cid=' . $sheet->id . '" title="Delete this sheet">Delete</a>
                                                            }
                                                        }
                                                        // Assign button
                                                        if (!$cannot_use_subscribed_worksheet && $sheet->assignment_id != ASSIGNMENT_VOCAB_BUILDER) :
                                                            ?><button type="button" class="btn btn-default btn-block btn-tiny grey assign-btn"><?php _e('Assign', 'iii-dictionary') ?></button><?php
                                                                            endif;
                                                                            // Preview button
                                                                            if (!$cannot_use_subscribed_worksheet && in_array($sheet->assignment_id, array(ASSIGNMENT_VOCAB_BUILDER, ASSIGNMENT_REPORT)) === false) :
                                                                                ?><button type="button" class="btn btn-default btn-block btn-tiny grey preview-btn"><?php _e('Preview', 'iii-dictionary') ?></button><?php
                                                                endif
                                                                ?><button type="button" class="btn btn-default btn-block btn-tiny grey worksheet-details-btn"><?php _e('Details', 'iii-dictionary') ?></button><?php
                                                                ?><div class="hidden"><?php echo $sheet->description ?></div><?php
                                                                ?></td><?php
                                                                ?></tr><?php
                                                    endforeach;
                                                else :
                                                    ?>
                                                    <tr>
                                                        <td colspan="5"><?php _e('No results', 'iii-dictionary') ?></td>
                                                    </tr>
                                                <?php endif ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-5 col-md-4 col-sm-offset-7 col-md-offset-8">
                        <div class="form-group"></div>
                        <a href="<?php echo locale_home_url() ?>/?r=homework-assignment/english&amp;layout=create" class="btn btn-default orange form-control"><span class="icon-plus"></span><?php _e('Create my own', 'iii-dictionary') ?></a>
                    </div>
                </div>

                <div class="modal fade modal-green" id="homework-viewer-modal" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <a href="#" data-dismiss="modal" aria-hidden="true" class="close"></a>
                                <h3 class="modal-title"><?php _e('Homework Viewer', 'iii-dictionary') ?> <span><span id="homework-detail"></span> <span id="question-i">1</span></span></h3>
                            </div>
                            <div class="modal-body green">
                                <div class="row">
                                    <div class="col-sm-12" id="quiz-box">
                                        <span id="quiz"></span>
                                    </div>
                                    <div class="col-sm-12" style="display: none" id="passage-block">
                                        <div class="form-group">
                                            <label><?php _e('Passage', 'iii-dictionary') ?></label>
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

                                        <div class="box box-green" id="spelling-subject-block" style="display: none; margin: 20px 0"> </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <button type="button" id="next-btn" class="btn btn-default btn-block sky-blue"><span class="icon-next"></span><?php _e('Next', 'iii-dictionary') ?></button>
                                            <button type="button" data-dismiss="modal" id="exit-btn" class="btn btn-block grey"><span class="icon-cancel"></span><?php _e('Exit', 'iii-dictionary') ?></button>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" id="current-row" value="1">
                                <input type="hidden" id="current-assignment" value="">
                            </div>
                        </div>
                    </div>
                </div>

                <table id="questions-table" style="display: none"></table>

            <?php else : ?>

                <div class="row homework">
                    <div class="col-sm-12">
                        <h2 class="title-border"><?php echo $cid ? __('Edit Worksheet', 'iii-dictionary') : __('Create New Worksheet', 'iii-dictionary') ?></h2>
                    </div>
                    <div class="col-sm-12 col-md-5" id="sheet-header-block">
                        <div class="row" id="assignment-radio">
                            <div class="col-xs-12">
                                <div class="form-group">
                                    <label><?php _e('Assignment', 'iii-dictionary') ?></label>
                                    <?php $assignment_html = MWHtml::sel_assignments($data['assignment-id'], true, $data['questions']) ?>
                                </div>
                            </div>
                        </div>
                        <div class="row" id="vocab-block-1">
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="grade"><?php _e('Grade', 'iii-dictionary') ?></label>
                                    <?php MWHtml::select_grades('ENGLISH', $data['grade'], array('id' => 'grade', 'name' => 'grade', 'first_option' => __('Select Grade', 'iii-dictionary'))) ?>
                                </div>					
                            </div>
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="sheet-name"><?php _e('Sheet Name', 'iii-dictionary') ?></label>
                                    <input type="text" class="form-control" id="sheet-name" name="sheet-name" value="<?php echo $data['sheet-name'] ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row" id="vocab-block-2" style="display: none">
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="header-name"><?php _e('Header Name', 'iii-dictionary') ?></label>
                                    <input type="text" class="form-control" id="header-name" name="header-name" value="<?php echo $data['header-name'] ?>">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="group-name"><?php _e('Group name', 'iii-dictionary') ?></label>
                                    <select name="group" class="select-box-it form-control" id="sel-group"></select>
                                    <select class="hidden" id="sel-group-types"><option value="0" selected><?php _e('My Groups', 'iii-dictionary') ?></option></select>
                                    <select id="class-group0" class="hidden">
                                        <?php foreach ($group_list->items as $grp) :
                                            ?><option value="<?php echo $grp->id ?>"<?php echo $grp->id == $data['group'] ? ' selected' : '' ?>><?php echo $grp->name ?></option><?php endforeach ?>
                                    </select>
                                </div>					
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label><?php _e('Create a new group', 'iii-dictionary') ?></label>
                                    <a href="#create-group-modal" data-toggle="modal" class="btn btn-default btn-block orange form-control"><span class="icon-plus"></span><?php _e('Create', 'iii-dictionary') ?></a>
                                </div>
                            </div>
                        </div>

                        <div class="row" id="import-block">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="imported-file"><?php _e('Import CSV', 'iii-dictionary') ?></label>
                                    <span class="icon-availability"></span>
                                    <input type="text" class="form-control" id="imported-file" name="imported-file" value="" readonly>
                                </div>					
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <span class="btn btn-default btn-block grey btn-file">
                                        <span class="icon-browse"></span><?php _e('Browse', 'iii-dictionary') ?> <input name="input-file" id="input-file" type="file">
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-12 col-md-7" id="questions-sheet" style="margin-bottom: 10px">
                        <div class="box">
                            <section id="sheets-list">
                                <div class="row box-header">
                                    <div class="col-xs-12">
                                        <h3><?php _e('Max. 20 lines per sheet', 'iii-dictionary') ?></h3>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xs-12 scroll-list">
                                        <div class="loading-overlay"></div>
                                        <table class="table table-striped no-padding sheet-editor" id="sheet"><?php echo $assignment_html['html'] ?></table>
                                    </div>
                                </div>
                                <div class="row box-footer">
                                    <div class="col-xs-6 col-sm-4 col-md-6">														
                                        <label class="sr-only"><?php _e('Select a dictionary to use', 'iii-dictionary') ?></label>
                                        <?php MWHtml::select_dictionaries($data['dictionary']) ?>
                                    </div>
                                    <?php if ($layout == 'create') : ?>
                                        <div class="col-xs-5 col-sm-4 col-md-5 col-sm-offset-3 col-md-offset-0" id="chk-words-btn">
                                            <button type="button" id="check-word" class="btn btn-default btn-block sky-blue form-control" data-loading-text="<?php _e('Checking...', 'iii-dictionary') ?>"><?php _e('Check words', 'iii-dictionary') ?></button>
                                        </div>
                                    <?php endif ?>
                                </div>
                            </section>
                        </div>
                    </div>

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
                            <label><?php _e('Passage', 'iii-dictionary') ?></label>
                            <?php wp_editor($data['reading_passage'], 'reading_passage', $editor_settings); ?> 
                        </div>
                    </div>

                    <div class="col-sm-12 js-hidden" id="report-assignment-block">
                        <div class="form-group">
                            <label><?php _e('Assignment', 'iii-dictionary') ?></label>
                            <?php wp_editor($data['questions'], 'report_assignment', $editor_settings); ?>
                        </div>
                    </div>

                    <div class="col-sm-12" id="description-block">
                        <div class="form-group">
                            <label><?php _e('Description of Homework', 'iii-dictionary') ?></label>
                            <?php wp_editor($data['description'], 'description', $editor_settings); ?>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="form-group">
                            <?php if ($cid) : ?>
                                <button type="submit" name="task[update]"<?php echo isset($_GET['fset']) ? ' id="create-homework"' : '' ?> class="btn btn-default btn-block orange"><span class="icon-save"></span><?php _e('Update worksheet', 'iii-dictionary') ?></button>
                            <?php else : ?>
                                <button type="submit" name="task[create]" id="create-homework" class="btn btn-default btn-block orange"><span class="icon-plus"></span><?php _e('Create a new worksheet', 'iii-dictionary') ?></button>
                            <?php endif ?>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <a href="<?php echo locale_home_url() ?>/?r=homework-assignment/english" class="btn btn-default btn-block grey"><span class="icon-goback"></span><?php _e('Go back', 'iii-dictionary') ?></a>
                        </div>
                    </div>
                </div>

                <div class="modal fade modal-red-brown" id="comments-box-modal" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <a href="#" data-dismiss="modal" aria-hidden="true" class="close"></a>
                                <h3 class="modal-title"><?php _e('Teacher\'s comments', 'iii-dictionary') ?></h3>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <label><?php _e('Write your comments here', 'iii-dictionary') ?></label>
                                    <textarea class="form-control" name="comments" style="resize: none;height: 150px"><?php echo $data['comments'] ?></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <button type="submit" class="btn btn-block orange confirm" name="task[register-flashcards]"><span class="icon-check"></span><?php _e('Save', 'iii-dictionary') ?></button>
                                    </div>
                                    <div class="col-sm-6">
                                        <button type="button" class="btn btn-block grey" data-dismiss="modal"><span class="icon-cancel"></span><?php _e('Cancel', 'iii-dictionary') ?></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade modal-red-brown modal-large" id="sheet-editor-modal" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <a href="#" data-dismiss="modal" aria-hidden="true" class="close"></a>
                                <h3 class="modal-title"><?php printf(__('Edit Question %s', 'iii-dictionary'), '<span></span>') ?></h3>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" id="current-row-index" value="">
                                <div class="form-group">
                                    <label><?php _e('Subject', 'iii-dictionary') ?></label>
                                    <input type="text" id="editor-input-5" class="form-control" value="" autocomplete="off">
                                </div>
                                <div class="form-group">
                                    <label><?php _e('Question', 'iii-dictionary') ?></label>
                                    <input type="text" id="editor-input-1i" class="form-control" value="" autocomplete="off">
                                    <textarea class="form-control" id="editor-input-1a" style="resize: vertical; height: 300px; display: none"></textarea>
                                </div>
                                <div class="form-group">
                                    <label><?php _e('Correct Answer', 'iii-dictionary') ?></label>
                                    <input type="text" id="editor-input-2" class="form-control" value="" autocomplete="off">
                                </div>
                                <div class="form-group">
                                    <label><?php _e('Incorrect Answer 1', 'iii-dictionary') ?></label>
                                    <input type="text" id="editor-input-3" class="form-control" value="" autocomplete="off">
                                </div>
                                <div class="form-group">
                                    <label><?php _e('Incorrect Answer 2', 'iii-dictionary') ?></label>
                                    <input type="text" id="editor-input-4" class="form-control" value="" autocomplete="off">
                                </div>
                                <div class="form-group">
                                    <label><?php _e('Incorrect Answer 3', 'iii-dictionary') ?></label>
                                    <input type="text" id="editor-input-6" class="form-control" value="" autocomplete="off">
                                </div>
                                <div class="form-group">
                                    <label><?php _e('Incorrect Answer 4', 'iii-dictionary') ?></label>
                                    <input type="text" id="editor-input-7" class="form-control" value="" autocomplete="off">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <button type="button" class="btn btn-block orange" id="editor-save-btn"><span class="icon-check"></span><?php _e('Save', 'iii-dictionary') ?></button>
                                    </div>
                                    <div class="col-sm-6">
                                        <button type="button" class="btn btn-block grey" data-dismiss="modal"><span class="icon-cancel"></span><?php _e('Cancel', 'iii-dictionary') ?></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php echo $assignment_html['js'] ?>
            <?php endif ?>

            <input type="hidden" id="wordchecked" name="wordchecked" value="0">

            <?php
            break; // end english case

        case 'mathematics':
            ?>

            <div class="row">
                <div class="col-sm-12">
                    <h2 class="title-border"><?php _e('Available Worksheets', 'iii-dictionary') ?> <small><?php _e('(Select one to assign)', 'iii-dictionary') ?></small></h2>
                </div>
                <div class="col-sm-5 col-md-4 col-sm-offset-7 col-md-offset-8">
                    <div class="form-group hidden">
                        <a href="<?php echo home_url() ?>/?r=homework-assignment/mathematics&amp;layout=create" class="btn btn-default orange form-control"><span class="icon-plus"></span><?php _e('Create my own', 'iii-dictionary') ?></a>
                    </div>
                </div>
                <div class="col-sm-12">
                    <div class="box box-sapphire">
                        <div class="row box-header">
                            <div class="col-sm-12">
                                <div class="row search-tools">
                                    <div class="col-sm-6 form-group">
                                        <input type="text" id="filter-sheet-name" name="filter[sheet-name]" class="form-control" placeholder="<?php _e('Sheet Name', 'iii-dictionary') ?>" value="<?php echo $filter['sheet-name'] ?>">
                                    </div>
                                    <div class="col-sm-6 form-group">
                                        <?php
                                        MWHtml::sel_homework_types($filter['homework-types'], array('first_option' => __('-Homework Type-', 'iii-dictionary'),
                                            'name' => 'filter[homework-types]', 'class' => 'select-sapphire form-control',
                                            'id' => 'filter-homework-types', 'subscribed_option' => true)
                                        )
                                        ?>
                                    </div>
                                    <div class="col-sm-4 form-group">
                                        <select class="select-box-it select-sapphire form-control" name="filter[cat-level]" id="filter-level-categories">
                                            <option value=""><?php _e('-Category-', 'iii-dictionary') ?></option>
                                            <?php foreach ($main_categories as $item) : ?>
                                                <option value="<?php echo $item->id ?>"<?php echo $filter['cat-level'] == $item->id ? ' selected' : '' ?>><?php echo $item->name ?></option>
                                            <?php endforeach ?>
                                        </select>
                                    </div>
                                    <div class="col-sm-5 form-group">
                                        <?php MWHtml::sel_math_assignments($filter['assignment-id'], array('first-option' => __('-Worksheet Format-', 'iii-dictionary'), 'name' => 'filter[math-assignments]', 'id' => 'math-assignments', 'class' => 'select-sapphire')) ?>
                                    </div>
                                    <div class="col-sm-3 form-group">
                                        <button type="submit" class="btn btn-default sky-blue form-control" name="filter[search]" id="search-btn"><?php _e('Search', 'iii-dictionary') ?></button>
                                    </div>
                                    <div class="col-sm-6 form-group">
                                        <select class="select-box-it select-sapphire form-control" name="filter[level]" id="filter-levels" data-selected="<?php echo $filter['level'] ?>">
                                            <option value=""><?php _e('-Subject-', 'iii-dictionary') ?></option>
                                        </select>
                                        <?php echo $sel_levels_html ?>
                                    </div>
                                    <div class="col-sm-6 form-group">
                                        <select class="select-box-it select-sapphire form-control" id="filter-sublevels" name="filter[sublevel]" data-selected="<?php echo $filter['sublevel'] ?>">
                                            <option value=""><?php _e('-Lesson-', 'iii-dictionary') ?></option>
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
                                                <th class="hidden-xs"><?php _e('Category', 'iii-dictionary') ?></th>
                                                <th class="hidden-xs">
                                                    <a href="#" class="sortable<?php echo $filter['orderby'] == 'level_name' ? ' ' . $filter['order-dir'] : '' ?>" data-sort-by="level_name"><?php _e('Subject', 'iii-dictionary') ?> <span class="sorting-indicator"></span></a>
                                                </th>
                                                <th class="hidden-xs">
                                                    <a href="#" class="sortable<?php echo $filter['orderby'] == 'sublevel_name' ? ' ' . $filter['order-dir'] : '' ?>" data-sort-by="sublevel_name"><?php _e('Lesson', 'iii-dictionary') ?> <span class="sorting-indicator"></span></a>
                                                </th>
                                                
                                                <th>
                                                    <a href="#" class="sortable<?php echo $filter['orderby'] == 'sheet_name' ? ' ' . $filter['order-dir'] : '' ?>" data-sort-by="sheet_name"><?php _e('Worksheet', 'iii-dictionary') ?> <span class="sorting-indicator"></span></a>
                                                </th>
                                                <th class="hidden-xs">
                                                    <?php _e('Type', 'iii-dictionary') ?>
                                                </th>
                                                <th style="min-width: 70px"></th>
                                            </tr>
                                        </thead>
                                        <tfoot>
                                            <tr><td colspan="5"><?php echo $pagination ?></td></tr>
                                        </tfoot>
                                        <tbody><?php if (empty($avail_sheets)) : ?>
                                                <tr><td colspan="6">No results</td></tr>
                                                <?php
                                            else : foreach ($avail_sheets as $sheet) :
                                                    $cannot_use_subscribed_worksheet = !is_math_homework_tools_subscribed() && $sheet->homework_type_id == HOMEWORK_SUBSCRIBED;
                                                    ?><tr<?php echo $sheet->active ? '' : ' class="hidden"' ?> data-id="<?php echo $sheet->id ?>" 
                                                                                                             data-assignment="<?php echo $sheet->assignment_id ?>"
                                                                                                                 <?php if(!$is_admin):
                                                                                                                 echo $cannot_use_subscribed_worksheet ? ' class="text-muted" title="' . __('Require Homework Tool Subscrption', 'iii-dictionary') . '"' : '' ;
                                                                                                             endif;  ?>><?php ?><td class="hidden-xs"><?php echo $sheet->level_category_name ?></td><?php ?><td><?php echo $sheet->level_name ?></td><?php ?><td class="hidden-xs"><?php echo $sheet->sublevel_name ?></td><?php ?><td class="hidden-xs"><?php echo $sheet->sheet_name ?></td><?php ?><td class="hidden-xs"><?php echo $sheet->type ?></td><?php ?><td><?php
                                                    if (!$cannot_use_subscribed_worksheet || $is_admin) : ?>
                                                            <button type="button" class="btn btn-default btn-block btn-tiny grey assign-btn"><?php _e('Assign', 'iii-dictionary') ?></button>
                                                            <?php endif; ?>
                                                            <button data-id="<?php echo $sheet->id ?>" type="button" class="btn btn-default btn-block btn-tiny grey worksheet-details-btn"><?php _e('Details', 'iii-dictionary') ?></button><?php ?><div class="hidden"><?php echo $sheet->description; ?></div><div class="hidden"><input class="checkboxpage"  type="checkbox" id="checkboxpage" value="<?php echo $sheet->lastpage == 1 ? 1 : 0 ?>"  <?php echo $sheet->lastpage == 1 ? 'checked' : '' ?>><?php ?></td><?php ?></tr><?php
                                                        endforeach;
                                                    endif
                                                    ?>
                                                        </tbody>
                                                        </table>
                                                    </div>
                                                    </div>
                                                    </div>
                                                    </div>
                                                    </div>
                                                    <div class="col-sm-5 col-md-4 col-sm-offset-7 col-md-offset-8 hidden">
                                                        <div class="form-group"></div>
                                                        <a href="<?php echo home_url() ?>/?r=homework-assignment/mathematics&amp;layout=create" class="btn btn-default btn-block orange form-control"><span class="icon-plus"></span><?php _e('Create my own', 'iii-dictionary') ?></a>
                                                    </div>
                                                    </div>

                                                    <?php
                                                    break; // end mathematics case

                                            endswitch
                                            ?>

                                            <?php if ($layout != 'create') : ?>

                                                <div class="modal fade modal-red-brown" id="assign-homework-modal" tabindex="-1" role="dialog" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <a href="#" data-dismiss="modal" aria-hidden="true" class="close"></a>
                                                                <h3 class="modal-title"><?php _e('Assign Homework', 'iii-dictionary') ?></h3>

                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="row">
                                                                    <div class="col-sm-12 form-group">
                                                                        <label><?php _e('Selected Worksheet', 'iii-dictionary') ?></label>
                                                                        <p class="box" id="homework-name" style="padding: 5px 15px"></p>
                                                                    </div>
                                                                    <div class="col-sm-6 form-group">
                                                                        <label><?php _e('Homework Name', 'iii-dictionary') ?></label>
                                                                        <input type="text" class="form-control" name="homework-name" value="<?php echo $homework_name ?>">
                                                                    </div>
                                                                    <div class="col-sm-6 form-group">
                                                                        <label for="deadline"><?php _e('Deadline', 'iii-dictionary') ?></label>
                                                                        <input type="text" class="form-control" id="deadline" name="deadline" value="<?php echo $deadline ?>" placeholder="<?php _e('No deadline', 'iii-dictionary') ?>">
                                                                    </div>
                                                                    <div class="col-sm-6 form-group">
                                                                        <label><?php _e('Homework mode', 'iii-dictionary') ?></label>
                                                                        <div class="row">
                                                                            <div class="col-xs-6">
                                                                                <div class="radio radio-style1">
                                                                                    <input id="hw-for-test" type="radio" name="hw-for-practice" value="0" checked>
                                                                                    <label for="hw-for-test"><?php _e('Test', 'iii-dictionary') ?></label>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-xs-6">
                                                                                <div class="radio radio-style1">
                                                                                    <input id="hw-for-practice" type="radio" name="hw-for-practice" value="1">
                                                                                    <label for="hw-for-practice"><?php _e('Practice', 'iii-dictionary') ?></label>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <?php if (is_mw_admin() || is_mw_super_admin()) : ?>

                                                                        <div class="col-sm-6 form-group">
                                                                            <label>Homework is retryable?</label>
                                                                            <div class="row">
                                                                                <div class="col-xs-6">
                                                                                    <div class="radio radio-style1">
                                                                                        <input id="is-retryable-no" type="radio" name="is-retryable" value="0" checked>
                                                                                        <label for="is-retryable-no">No</label>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="col-xs-6">
                                                                                    <div class="radio radio-style1">
                                                                                        <input id="is-retryable-yes" type="radio" name="is-retryable" value="1">
                                                                                        <label for="is-retryable-yes">Yes</label>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <?php if ($actual_link == 'mathematics') { ?>
                                                                            <div class="row col-sm-12">
                                                                                <div class="col-xs-6">
                                                                                    <div style="float:left">
                                                                                        <input id="rdo-no" class="checkboxpage"  type="checkbox" name="checkboxpage" value="1" >
                                                                                    </div>
                                                                                    <div >
                                                                                        <label style="padding-left: 8%">Display last page</label>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        <?php } ?>
                                                                        <div class="col-sm-12" >
                                                                            <?php
                                                                            $class_group_types = MWDB::get_group_class_types();
                                                                            $group_classes = MWDB::get_groups(array('group_type' => GROUP_CLASS))
                                                                            ?>
                                                                            <div class="col-md-4 form-group" style="padding-left: 0px !important;">
                                                                                <label><?php _e('Group types', 'iii-dictionary') ?></label>
                                                                                <select class="select-box-it" id="sel-group-types" name="grouptype">
                                                                                    <?php foreach ($class_group_types as $item) : ?>
                                                                                        <option value="<?php echo $item->id ?>" <?php echo $item->id==$_SESSION['grouptype']? 'selected': ''?>><?php echo $item->name ?> </option>
                                                                                    <?php endforeach ?>
                                                                                    <option value="0" <?php echo 0==$_SESSION['grouptype']? 'selected': ''?>><?php _e('Orther Groups', 'iii-dictionary') ?></option>
                                                                                </select>
                                                                                <?php foreach ($class_group_types as $item) :
                                                                                    ?><select id="class-group<?php echo $item->id ?>" name="groupclass" class="hidden"><?php
                                                                                    foreach ($group_classes->items as $class) :
                                                                                        if ($class->class_type_id == $item->id) :
                                                                                            ?><option value="<?php echo $class->id ?>" <?php echo $class->id==$_SESSION['group']? 'selected': ''?>><?php echo $class->name ?></option><?php
                                                                                            endif;
                                                                                        endforeach
                                                                                        ?></select><?php endforeach ?>
                                                                            </div>
                                                                            <?php
                                                                        else :
                                                                            // make sure normal user always has his own group selected 
                                                                            ?>
                                                                            <select class="hidden" id="sel-group-types"><option value="0" selected><?php _e('My Groups', 'iii-dictionary') ?></option></select>
                                                                        <?php endif ?>
                                                                            
                                                                        <?php if (is_mw_admin() || is_mw_super_admin()) : ?>    
                                                                        <select id="class-group0" class="hidden">
                                                                            <?php foreach ($group_list1->items as $grp) :
                                                                                ?><option value="<?php echo $grp->id ?>" <?php echo $grp->id==$_SESSION['group']? 'selected': ''?>><?php echo $grp->name ?></option><?php endforeach ?>
                                                                        </select>
                                                                        <?php else : ?>
                                                                            <select id="class-group0" class="hidden">
                                                                            <?php foreach ($group_list->items as $grp) :
                                                                                ?><option value="<?php echo $grp->id ?>"><?php echo $grp->name ?></option><?php endforeach ?>
                                                                        </select>
                                                                            <?php endif?>
                                                                        <div class="col-sm-8 form-group">
                                                                            <label for="group-name"><?php _e('Group name', 'iii-dictionary') ?></label>
                                                                            <select name="group" class="select-box-it form-control" id="sel-group"></select>
                                                                        </div>

                                                                    <div >
                                                                        <div class="col-sm-4 form-group" style="padding-left: 0px !important;">
                                                                            <label><?php _e('Create a new group', 'iii-dictionary') ?></label>
                                                                            <a id="new-group-btn" data-toggle="modal" class="btn btn-default btn-block orange form-control"><span class="icon-plus"></span><?php _e('Create', 'iii-dictionary') ?></a>
                                                                        </div>
                                                                        <div class="col-sm-8 form-group">
                                                                            <label>&nbsp;</label>
                                                                            <button type="submit" id="send-btn" name="task[register]" class="btn btn-default btn-block orange form-control"><span class="icon-send"></span><?php _e('Send a homework assignment', 'iii-dictionary') ?></button>
                                                                        </div>
                                                                    </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="modal fade modal-red-brown" id="worksheet-details-modal" tabindex="-1" role="dialog" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <a href="#" data-dismiss="modal" aria-hidden="true" class="close"></a>
                                                                <h3 class="modal-title"><?php _e('Worksheet details', 'iii-dictionary') ?></h3>
                                                            </div>
                                                            <div class="modal-body">

                                                                <div >
                                                                    <label style="display: inline-block;color: #fff779;"><?php _e('Worksheet Name : ', 'iii-dictionary') ?></label><label style="display: inline-block;padding-left: 1%" id="sheet-name"></label>
                                                                </div>
                                                                <label style="color: #fff779;"><?php _e('Worksheet Description', 'iii-dictionary') ?></label>
                                                                <div class="box">
                                                                    <div class="scroll-list" style="max-height: 350px">
                                                                        <div id="hw-desc"></div>
                                                                    </div>

                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                            <?php endif ?>

                                            <input type="hidden" id="sheet-id" name="sheet-id" value="<?php echo $cid ?>">
                                            <input type="hidden" name="filter[orderby]" id="filter-order" value="<?php echo $filter['orderby'] ?>">
                                            <input type="hidden" name="filter[order-dir]" id="filter-order-dir" value="<?php echo $filter['order-dir'] ?>">
                                            </form>

                                            <div class="modal fade modal-red-brown iframe" id="create-group-modal" tabindex="-1" role="dialog" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <a href="#" data-dismiss="modal" aria-hidden="true" class="close"></a>
                                                            <h3 class="modal-title"><?php _e('Greate a Group', 'iii-dictionary') ?></h3>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="row">
                                                                <div class="col-sm-12">
                                                                    <div class="row">
                                                                        <div class="col-sm-12" id="create-group-error"></div>
                                                                        <div class="col-sm-6">
                                                                            <div class="form-group">
                                                                                <label for="group-name"><?php _e('Group name', 'iii-dictionary') ?></label>
                                                                                <input type="text" class="form-control" id="group-name" name="group-name" value="">
                                                                            </div>     
                                                                        </div>
                                                                        <div class="col-sm-6">
                                                                            <div class="form-group">
                                                                                <label for="group-password"><?php _e('Group password', 'iii-dictionary') ?></label>
                                                                                <input type="text" class="form-control" id="group-password" name="password" value="">
                                                                            </div>     
                                                                        </div>
                                                                        <div class="col-sm-12">
                                                                            <div class="form-group">
                                                                                <button type="button" id="create-group-btn" class="btn btn-default btn-block orange login"><span class="icon-user-plus"></span><?php _e('Create a new group', 'iii-dictionary') ?></button>
                                                                            </div>     
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <script>
                                                (function ($) {
                                                    $(function () {
<?php if ($layout == 'create') : ?>
                                                            $("#page-info-tab-dialog").find(".info-tab-img").hide();
                                                            $("#page-info-tab-dialog").find("#t-" + $("#assignments").val()).show();
                                                            $("#assignments").change(function () {
                                                                $("#page-info-tab-dialog").find(".info-tab-img").hide();
                                                                $("#page-info-tab-dialog").find("#t-" + $(this).val()).show();
                                                            });
<?php endif ?>
                                                    });
                                                })(jQuery);
                                            </script>
                                            <style>
                                                .checkboxpage, .checkboxlastpage{
                                                    width: 28px;
                                                    height: 28px;
                                                    border-radius: 50px;
                                                    box-shadow: inset 0px 1px 1px white, 0px 1px 3px rgba(0,0,0,0.5);
                                                    position: relative;
                                                }
                                            </style>
                                            <?php if (!$is_math_panel) : ?>
                                                <?php get_dict_footer() ?>
                                            <?php else : ?>
                                                <?php get_math_footer() ?>
<?php endif ?>