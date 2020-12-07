<?php
$current_user_id = get_current_user_id();
$is_registered_teacher_math = is_mw_registered_teacher(get_current_user_id(), 1);
$is_registered_teacher = is_mw_registered_teacher();
if ($is_registered_teacher_math == true || $is_registered_teacher == true) {
    $gname = $gpass = '';
    $is_math_panel = is_math_panel();
    $_page_title = __('Manage Classes', 'iii-dictionary');
    $layout = isset($_GET['layout']) ? $_GET['layout'] : 'list';

    $is_mw_admin = false;
    if (is_mw_super_admin() || is_mw_admin()) {
        $is_mw_admin = true;
        $group = new stdClass;
        $group->group_type_id = GROUP_CLASS;
    }

    $current_user_id = get_current_user_id();
    $a = '';

    if (isset($_POST['submit']) || isset($_POST['update'])) {
        $data['id'] = $_REAL_POST['cid'];
        $data['gname'] = esc_html($_REAL_POST['group-name']);
        $data['gpass'] = esc_html($_REAL_POST['password']);
        $data['change_students_no'] = esc_html($_REAL_POST['charge-students-no']);
        $data['change_students_yes'] = esc_html($_REAL_POST['charge-students-yes']);
        $data['price_per_student'] = esc_html($_REAL_POST['price-per-student']);

        $data['number_of_students'] = esc_html($_REAL_POST['num-students']);
        $data['number_of_months'] = esc_html($_REAL_POST['num-month']);
        $starting_date = date('Y-m-d', time());
        $expired_date = date('Y-m-d', strtotime('+' . $data['number_of_months'] . ' months', strtotime($starting_date)));

        $data['activation_code_id'] = 0;
        $data['user_id'] = $current_user_id;
        $data['starting_date'] = $starting_date;
        $data['expired_date'] = $expired_date;
        $data['code_typeid'] = 1; // Need Edit
        $data['sat_class_id'] = 0;
        $data['dictionary_id'] = 1; // Need Edit

        if ($data['change_students_yes'] == 1) {
            $data['group_type_id'] = GROUP_CLASS;
            $data['price'] = esc_html($_REAL_POST['price-per-student']);
        }

        $gname = $data['gname'];
        $gpass = $data['gpass'];
        $change_students_no = $data['change_students_no'];
        $change_students_yes = $data['change_students_yes'];

        if (empty($data['id'])) {
            $data['created_by'] = $current_user_id;
            $data['created_on'] = date('Y-m-d', time());
            $data['active'] = 1;
        }
        if ($is_mw_admin) {
            $data['group_type_id'] = $_REAL_POST['group-types'];
            $data['class_type_id'] = $_REAL_POST['class-types'];
            $data['content'] = $_REAL_POST['group-content'];
            $data['detail'] = $_REAL_POST['group_detail'];
            $data['ordering'] = $_REAL_POST['ordering'];
            $data['price'] = !empty($_REAL_POST['price']) ? $_REAL_POST['price'] : 0;
            $data['special_group'] = isset($_REAL_POST['sat_special_group']) ? 1 : 0;
        }

        if (MWDB::store_group($data)) {
            $redirect_to = locale_home_url() . '/?r=create-group';

            if (!empty($data['id'])) {
                $redirect_to .= '&layout=create&cid=' . $data['id'];
            }

            wp_redirect($redirect_to);
            exit;
        }
    }

    if (isset($_POST['order-up'])) {
        MWDB::set_group_order_up($_POST['cid']);
        wp_redirect(locale_home_url() . '/?r=create-group');
        exit;
    }

    if (isset($_POST['order-down'])) {
        MWDB::set_group_order_down($_POST['cid']);
        wp_redirect(locale_home_url() . '/?r=create-group');
        exit;
    }

    if ($is_mw_admin) {
        $current_page = max(1, get_query_var('page'));
        $filter = get_page_filter_session();
        if (empty($filter) && !isset($_POST['filter'])) {
            $filter['orderby'] = 'ordering';
            $filter['order-dir'] = 'asc';
            $filter['items_per_page'] = 30;
            $filter['offset'] = $filter['items_per_page'] * ($current_page - 1);
            $filter['group_type'] = GROUP_CLASS;
            $filter['class_type'] = $_REAL_POST['filter']['class-types'];
        } else {
            if (isset($_POST['filter']['search'])) {
                $filter['group-name'] = $_REAL_POST['filter']['group-name'];
                $filter['class_type'] = $_REAL_POST['filter']['class-types'];
            }

            if (isset($_REAL_POST['filter']['orderby'])) {
                $filter['orderby'] = $_REAL_POST['filter']['orderby'];
                $filter['order-dir'] = $_REAL_POST['filter']['order-dir'];
            }

            $filter['offset'] = $filter['items_per_page'] * ($current_page - 1);
        }

        set_page_filter_session($filter);
        $group_types = MWDB::get_group_types();
        $class_types = MWDB::get_group_class_types();
        $filter['offset'] = 0;
        $filter['items_per_page'] = 99999999;
        $filter['is_admin_create_group'] = 1;
        $groups = MWDB::get_groups($filter, $filter['offset'], $filter['items_per_page']);
        $total_pages = ceil($groups->total / $filter['items_per_page']);

        $pagination = paginate_links(array(
            'format' => '?page=%#%',
            'current' => $current_page,
            'total' => $total_pages
        ));
    }

    if (isset($_GET['cid'])) {
        $group = MWDB::get_group($_GET['cid'], 'id');
        $gname = $group->name;
        $gpass = $group->password;
        $gspecial = $group->special_group;
    }
} else {
    if ($is_registered_teacher == FALSE && $is_registered_teacher_math == FALSE) {
        $title = __('Registration Required', 'iii-dictionary');
        $body = __('Please register as the teacher before create class in this panel.', 'iii-dictionary');
        $return_url = locale_home_url() . '/?r=my-account#4';
    }

    set_lockpage_dialog($title, $body, $return_url);
}


//tab math NEW ASSIGNMENT
if ($cid) { // view a sheet
    $current_sheet = MWDB::get_math_sheet_by_id($cid);

    $data['assignment_id'] = $current_sheet->assignment_id;
    $data['homework_type_id'] = $current_sheet->homework_type_id;
    $data['sublevel_id'] = $current_sheet->grade_id;
    $data['group-name'] = $current_sheet->group_name;
    $data['sheet_name'] = $current_sheet->sheet_name;
    $data['questions'] = json_decode($current_sheet->questions, true);
    $data['description'] = $current_sheet->description;
    $data['answer_time_limit'] = $current_sheet->answer_time_limit;
    $data['show_answer_after'] = $current_sheet->show_answer_after;
    $sel_level_category = $current_sheet->category_level_id;
    $sel_level = $current_sheet->level_id;
    $data['lang'] = $current_sheet->lang;
} else { // sheet list
    $current_page = max(1, get_query_var('page'));
    $filter = get_page_filter_session();
    if (empty($filter) && !isset($_REAL_POST['filter'])) {
        $filter['orderby'] = 'active';
        $filter['order-dir'] = 'asc';
        $filter['items_per_page'] = 20;
        $filter['offset'] = $filter['items_per_page'] * ($current_page - 1);
    } else {
        if (isset($_REAL_POST['filter']['search'])) {
            $filter['lang'] = $_POST['filter']['lang'];
            $filter['group-name'] = $_REAL_POST['filter']['group-name'];
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

//tab english NEW ASSIGNMENT
if ($cid) { // view a sheet
    $current_sheet = $wpdb->get_row($wpdb->prepare(
                    'SELECT s.*, gr.name AS grade
					FROM ' . $wpdb->prefix . 'dict_sheets AS s
					JOIN ' . $wpdb->prefix . 'dict_grades AS gr ON gr.id = s.grade_id
					WHERE s.id = %s', $cid
    ));

    $data['assignment-id'] = $current_sheet->assignment_id;
    $data['homework-types'] = $current_sheet->homework_type_id;
    $data['sheet-categories'] = $current_sheet->category_id;
    $data['trivia-exclusive'] = $current_sheet->trivia_exclusive;
    $data['grade'] = $current_sheet->grade_id;
    $data['group-name'] = $current_sheet->group_name;
    $data['sheet-name'] = $current_sheet->sheet_name;
    $data['grading-price'] = $current_sheet->grading_price;
    $data['dictionary'] = $current_sheet->dictionary_id;
    $data['questions'] = json_decode($current_sheet->questions, true);
    $data['reading_passage'] = $current_sheet->passages;
    $data['description'] = $current_sheet->description;
    $data['lang'] = $current_sheet->lang;
} else { // sheet list
    $current_page = max(1, get_query_var('page'));
    $filter = get_page_filter_session();
    if (empty($filter) && !isset($_REAL_POST['filter'])) {
        $filter['orderby'] = 'grade';
        $filter['order-dir'] = 'asc';
        $filter['items_per_page'] = 20;
        $filter['offset'] = $filter['items_per_page'] * ($current_page - 1);
    } else {
        if (isset($_REAL_POST['filter']['search'])) {
            $filter['lang'] = $_POST['filter']['lang'];
            $filter['sheet-name'] = $_REAL_POST['filter']['sheet-name'];
            $filter['group-name'] = $_REAL_POST['filter']['group-name'];
            $filter['grade'] = $_REAL_POST['filter']['grade'];
            $filter['assignment-id'] = $_REAL_POST['filter']['assignment-id'];
            $filter['homework-types'] = $_REAL_POST['filter']['homework-types'];
            $filter['trivia-exclusive'] = $_REAL_POST['filter']['trivia-exclusive'];
            $filter['active'] = $_REAL_POST['filter']['active'];
            $check_global = 1;
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
    $sheets_obj = MWDB::get_sheets($filter, false, true);
    $avail_eng_sheets = $sheets_obj->items;
    $total_rows = $sheets_obj->total;
    $total_pages = ceil($total_rows / $filter['items_per_page']);
    $pagination = paginate_links(array(
        'format' => '?page=%#%',
        'current' => $current_page,
        'total' => $total_pages
    ));
}

// btn-assign-submit
if (isset($_POST['assign'])) {
    if (!empty($_POST['homework-name'])) {
        $data['name'] = $_POST['homework-name'];
    }

    $data['deadline'] = !empty($_POST['deadline']) ? date('Y-m-d', strtotime($_POST['deadline'])) : '0000-00-00';
    $data['for_practice'] = $_POST['for-practice'];
    $data['is_retryable'] = $_POST['is-retryable'];
    $data['adminlastpage'] = $_POST['checkboxpageadminmodal'];
    $data['teacherlastpage'] = $_POST['checkboxpagemodal'];
    $data['sheet_id'] = $_POST['sheet_id'];
    $data['group'] = $_POST['group_id'];

    if (MWDB::assign_homework_new($data)) {
        ik_enqueue_messages(__('Homework assign.', 'iii-dictionary'), 'success');
        wp_redirect(locale_home_url() . '/?r=teachers-box');
        exit;
    } else {
        ik_enqueue_messages(__('An error occurred, cannot assign homework.', 'iii-dictionary'), 'error');
    }
}

// btn-update-homework-submit
if (isset($_POST['update-homework'])) {
    if (!empty($_POST['homework-name'])) {
        $data['name'] = $_POST['homework-name'];
    }

    $data['deadline'] = !empty($_POST['deadline']) ? date('Y-m-d', strtotime($_POST['deadline'])) : '0000-00-00';
    $data['for_practice'] = $_POST['for-practice'];
    $data['is_retryable'] = $_POST['is-retryable'];
    $data['adminlastpage'] = $_POST['checkboxpageadminmodal'];
    $data['teacherlastpage'] = $_POST['checkboxpagemodal'];

    //$data['next_homework_id'] = $_POST['link-id'];
    $data['id'] = $_POST['_cid'];
    $data['active'] = 1;
    if (MWDB::update_homework_assignment($data)) {
        ik_enqueue_messages(__('Homework update.', 'iii-dictionary'), 'success');
        wp_redirect(locale_home_url() . '/?r=teachers-box');
        exit;
    } else {
        ik_enqueue_messages(__('An error occurred, cannot update homework.', 'iii-dictionary'), 'error');
    }
}

// <!-- Ordering -->
$gid = empty($_GET['gid']) ? 0 : $_GET['gid'];
$hid = empty($_GET['hid']) ? 0 : $_GET['hid'];
$task = isset($_POST['task']) ? $_POST['task'] : '';

$is_admin = is_mw_super_admin() || is_mw_admin() ? true : false;
$current_user_id = get_current_user_id();

$is_math_panel = is_math_panel();
$_page_title = __('Manage Classes', 'iii-dictionary');

if ($task == 'toggle-active') {
    $tid = $_POST['tid'];
    if (!empty($tid)) {
        foreach ($tid as $id) {
            $result = $wpdb->query(
                    $wpdb->prepare('UPDATE ' . $wpdb->prefix . 'dict_homeworks SET active = ABS(active - 1) WHERE id = %d', $id)
            );

            if (!$result) {
                break;
            }
        }

        if ($result) {
            ik_enqueue_messages('Successfully active/deactive ' . count($tid) . ' Homework.', 'success');
            wp_redirect(home_url() . '/?r=teachers-box');
            exit;
        } else {
            ik_enqueue_messages('There\'s error occurs during the operation.', 'error');
            wp_redirect(home_url() . '/?r=teachers-box');
            exit;
        }
    }
}
// export student's results to .CSV file
if (isset($_POST['export'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename=worksheets_result_' . date('mdY_Hms', time()));
    $fp = fopen('php://output', 'w');

    $worksheets = MWDB::get_homework_assignments(array('group_id' => $_POST['group-id']));

    foreach ($worksheets->items as $worksheet) {
        $worksheets_result = MWDB::get_homework_results($worksheets->items[0]->id);

        // outputting
        $row_header = array('<<<<<', 'Worksheet: ' . $worksheet->sheet_name, 'Grade: ' . $worksheet->grade, '>>>>>');
        fputcsv($fp, $row_header);
        fputcsv($fp, array());
        fputcsv($fp, array('', 'Student', 'Score', 'Completed Date'));

        foreach ($worksheets_result as $key => $result) {
            $row = array($key + 1, $result->display_name, $result->score, $result->submitted_on);
            fputcsv($fp, $row);
        }
        fputcsv($fp, array());
        fputcsv($fp, array());
    }
    fclose($fp);
    exit;
}

// Send email to all member of group
if (isset($_POST['send-to-all'])) {
    $subject = $_POST['subject'];
    $message = $_POST['message-to-all'];
    $group_id = $_POST['message_group_id'];

    $students = MWDB::get_group_students($group_id);
    if ($students) {
        $total_success = 0;
        foreach ($students as $key => $student) {
            if (wp_mail($student->user_email, $subject, $message)) {
                $total_success ++;
            }
        }
        if ($total_success != 0) {
            ik_enqueue_messages('Successfully send email to everybody', 'iii-dictionary', 'message');
            wp_redirect(locale_home_url() . '?r=teachers-box');
        } else {
            ik_enqueue_messages('Failure send email to everybody', 'error');
            wp_redirect(locale_home_url() . '?r=teachers-box');
        }
    } else {
        ik_enqueue_messages(__('No student has joined this group yet.', 'iii-dictionary'), 'message');
        wp_redirect(locale_home_url() . '?r=teachers-box');
    }
}

// update a homework assignment
// 
// if(isset($_POST['update-homework'])) {
// 	if(!empty($_POST['homework-name'])) {
// 		$data['name'] = $_POST['homework-name'];
// 	}
// 	$data['deadline'] = !empty($_POST['deadline']) ? date('Y-m-d', strtotime($_POST['deadline'])) : '0000-00-00';
// 	$data['next_homework_id'] = $_POST['link-id'];
// 	$data['id'] = $_POST['_cid'];
// 	$data['for_practice'] = $_POST['for-practice'];
// 	$data['is_retryable'] = $_POST['is-retryable'];
// 	$data['adminlastpage'] = $_POST['checkboxpageadminmodal'];
// 	$data['teacherlastpage'] = $_POST['checkboxpagemodal'];
// 	if(MWDB::update_homework_assignment($data)) {
// 		ik_enqueue_messages(__('Homework updated.', 'iii-dictionary'), 'success');
// 		wp_redirect(locale_home_url() . '/?r=teachers-box&gid=' . $gid);
// 		exit;
// 	}
// 	else {
// 		ik_enqueue_messages(__('An error occurred, cannot update homework.', 'iii-dictionary'), 'error');
// 	}
// }

$current_page = max(1, get_query_var('page'));
$filter = get_page_filter_session();
if (empty($filter) && !isset($_POST['filter'])) {
    $filter['orderby'] = 'g.name';
    $filter['order-dir'] = 'asc';
    $filter['items_per_page'] = 25;
    $filter['offset'] = $filter['items_per_page'] * ($current_page - 1);
    $filter['created_by'] = get_current_user_id();
    $filter['subscription_status'] = true;
    if ($is_admin) {
        $filter['fetch_classes'] = true;
    }
} else {
    $filter['created_by'] = $current_user_id;
    if (isset($_POST['filter']['search'])) {
        $filter['group-name'] = $_REAL_POST['filter']['group-name'];
        $filter['class_type'] = $_POST['filter']['class-types'];
    }

    if (isset($_REAL_POST['filter']['orderby'])) {
        $filter['orderby'] = $_REAL_POST['filter']['orderby'];
        $filter['order-dir'] = $_REAL_POST['filter']['order-dir'];
    }

    $filter['offset'] = $filter['items_per_page'] * ($current_page - 1);
}

if (!$gid) {
    $waiting_homeworks = MWDB::get_waiting_grading_homeworks($current_user_id);

    $filter['offset'] = 0;
    $filter['items_per_page'] = 99999999;
    $filter['orderby'] = 'g.id';
    $filter['order-dir'] = 'desc';
    $groups = MWDB::get_groups($filter, $filter['offset'], $filter['items_per_page']);
    // echo "<pre>";
    // var_dump($wpdb);
    // echo "</pre>";
    $total_pages = ceil($groups->total / $filter['items_per_page']);
} else {
    $filter['group_id'] = $gid;
    $filter['check_result'] = true;
    $filter['created_by'] = '';
    $filter['offset'] = 0;
    $filter['items_per_page'] = 99999999;
    $assignments = MWDB::get_homework_assignments($filter, $filter['offset'], $filter['items_per_page']);
    $total_pages = ceil($assignments->total / $filter['items_per_page']);

    if (isset($_POST['remove-assignment'])) {
        if (MWDB::remove_homework($_POST['cid']) !== false) {
            wp_redirect(locale_home_url() . '/?r=teachers-box&gid=' . $gid);
            exit;
        }
    }
}

set_page_filter_session($filter);

$class_types = MWDB::get_group_class_types();

$pagination = paginate_links(array(
    'format' => '?page=%#%',
    'current' => $current_page,
    'total' => $total_pages
        ));
$main_categories = MWDB::get_grades(array('type' => 'MATH', 'level' => 0, 'admin_only' => 1, 'orderby' => 'ordering', 'order-dir' => 'asc'));
$levels = MWDB::get_grades(array('type' => 'MATH', 'level' => 1, 'orderby' => 'ordering', 'order-dir' => 'asc'));
$sublevels = MWDB::get_grades(array('type' => 'MATH', 'level' => 2, 'orderby' => 'ordering', 'order-dir' => 'asc'));

$sel_levels_html = '';
foreach($main_categories as $item) {
    $sel_levels_html .= '<select class="hidden" id="_l' . $item->id . '">';
    $sel_levels_html .= '<option value="">Level</option>';
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
    $sel_sublevels_html .= '<option value="">Sublevel</option>';
    foreach($sublevels as $sublevel) {
        if($sublevel->parent_id == $level->id) {
            $sel_sublevels_html .= '<option value="' . $sublevel->id . '">' . $sublevel->name . '</option>';
        }
    }
    $sel_sublevels_html .= '</select>';
}
?>
<?php get_math_header($_page_title, 'main1', 'red-brown') ?>

<?php get_dict_page_title($_page_title, '', '', array(), get_info_tab_cloud_url('Popup_info_17.jpg')) ?>

<style type="text/css" xmlns="http://www.w3.org/1999/html">
    /*=============Create a class Page style=================*/
    #content{
        background: #fff;
    }
    header.article-header{
        border-bottom: 1px solid #bbb;
    }
    .form__group {
        position: relative;
        margin-bottom: 25px;
    }
    .form__boolean {
        margin-top: 5px;
    }
    .form__boolean .radio {
        margin-right: 5px;
    }
    .pretty_radio[type="radio"]:not(:checked), .pretty_radio[type="radio"]:checked {
        position: absolute;
        left: -9999px;
        -webkit-transition: all .2s ease-in-out;
        transition: all .2s ease-in-out;
    }
    input[type="checkbox"], input[type="radio"] {
        -webkit-box-sizing: border-box;
        box-sizing: border-box;
        padding: 0;
    }
    .pretty_radio:after {
        clear: both;
    }
    .pretty_radio:before, .pretty_radio:after {
        content: " ";
        display: table;
    }
    .pretty_radio[type="radio"]:not(:checked)+label, .pretty_radio[type="radio"]:checked+label {
        position: relative;
        display: block;
        padding-left: 30px;
        line-height: 32px;
        font-size: 18px;
        cursor: pointer;
        -webkit-transition: all .2s ease-in-out;
        transition: all .2s ease-in-out;
        font-weight: normal;
    }
    .pretty_radio[type="radio"]+label {
        color: #000;
    }
    .pretty_radio[type="radio"]:not(:checked)+label:before {
        content: "";
        position: absolute;
        left: 2px;
        top: 6px;
        width: 20px;
        height: 20px;
        border: 2px solid #bdc3c7;
        background: #fff;
        border-radius: 30px;
        -webkit-box-shadow: none;
        box-shadow: none;
        background-repeat: no-repeat;
        background-position: 0 12px;
        -webkit-animation-duration: .2s;
        animation-duration: .2s;
    }

    .pretty_radio[type="radio"]:not(:checked)+label:after {
        opacity: 0;
        -webkit-transform: scale(0);
        transform: scale(0);
    }
    .pretty_radio[type="radio"]:not(:checked)+label:after, .pretty_radio[type="radio"]:checked+label:after {
        content: "";
        position: absolute;
        display: block;
        top: 10px;
        left: 6px;
        font-size: 18px;
        width: 12px;
        height: 12px;
        border: 0;
        background: #01a0cc;
        border-radius: 30px;
        -webkit-transition: all .2s;
        transition: all .2s;
    }
    .pretty_radio[type="radio"]:checked+label:before {
        content: "";
        position: absolute;
        left: 2px;
        top: 6px;
        width: 20px;
        height: 20px;
        border: 2px solid #bdc3c7;
        -webkit-transition: border cubic-bezier(0.25,0.46,0.45,0.94) .2s;
        transition: border cubic-bezier(0.25,0.46,0.45,0.94) .2s;
        color: #01a0cc;
        background: #fff;
        border-radius: 30px;
        -webkit-box-shadow: none;
        box-shadow: none;
        background-repeat: no-repeat;
        background-position: 0 12px;
        -webkit-animation-duration: .2s;
        animation-duration: .2s;
    }
    .pretty_radio[type="radio"]:checked+label:after {
        opacity: 1;
        color: #111;
        -webkit-transform: scale(1);
        transform: scale(1);
        -webkit-animation-duration: .2s;
        animation-duration: .2s;
    }
    .pretty_radio[type="radio"]:not(:checked)+label:after, .pretty_radio[type="radio"]:checked+label:after {
        content: "";
        position: absolute;
        display: block;
        top: 10px;
        left: 6px;
        font-size: 18px;
        width: 12px;
        height: 12px;
        border: 0;
        background: #01a0cc;
        border-radius: 30px;
        -webkit-transition: all .2s;
        transition: all .2s;
    }
    .label-title-create-class{
        position: relative;
        display: block;
        -webkit-box-sizing: border-box;
        box-sizing: border-box;
        padding: 0;
        margin: 0 50px 5px 0;
        font-weight: 600;
        font-size: 19px;
        color: #01a0cc;
    }
    .Name-pass-p{
        color: #0098D1;
        font-size: 18px;
        font-weight: 700;
    }
    .create-a-class-btn{
        width: 100%;
        background: #004258;
        color: #fff;
        border: none;
        height: 35px;
    }
    .create-a-class-update-homework-btn {
        width: 100%;
        margin-top: 28px;
        background: #8C8C8C;
        color: #fff;
        border: none;
        height: 35px;
    }
    .moneychose-option{
        position: absolute;
        width: 95%;
        cursor: default !important;
        padding-left: 5px !important;

    }
    /* TOOLTIP OF CREATE CLASS PAGE */
    .tooltip-charge-student .tooltip-div{
        visibility: hidden;
        background: #FFCD86;
        color: #fff;
        font-weight: 500;
        padding: 5px 0;
        color: #000;
        padding: 30px;
        position: absolute;
        z-index: 1;
        top: 11px;
        left: -72px;
        margin-top: 15px;
        font-size: 14px;
    }	
    .tooltip-charge-student img{
        padding-right: 5px;
        margin-top: -9px;
        position: absolute;
        left: -22px;
    }
    .tooltip-charge-student .tooltip-div:before{
        content: '';
        display: block;
        position: absolute;
        top: -20px;
        left: 15%;
        border-top: 10px solid transparent;
        border-right: 8px solid transparent;
        border-bottom: 10px solid #FFCD86;
        border-left: 8px solid transparent;
    }
    .tooltip-charge-student:hover .tooltip-div{
        visibility: visible;
    }
    .tooltip-charge-student,
    .tooltip-manage-a-classroom {
        position: relative;
        display: inline-block;
        opacity: 1;
        color: #FF8E07;
        font-size: 15px;
        font-weight: 700;
        margin-top: 11px;
        cursor: pointer;
        z-index: auto;
    }

    .tooltip-manage-a-classroom .tooltip-div {
        visibility: hidden;
        background:#FFCD86;
        color: #fff;
        font-weight: 500;
        padding: 5px 0;
        color: #000;
        padding:20px;
        /*Position the tooltip*/
        position: absolute;
        z-index: 1;
        margin-top:15px; 
        font-size: 14px;
    }
    .tooltip-manage-a-classroom img{
        padding-right: 5px;
        margin-top: -7px;
    }

    .tooltip-manage-a-classroom:hover .tooltip-div {
        visibility: visible;
    }
    .tooltip-charge-student .tooltip-div:before,
    .tooltip-manage-a-classroom .tooltip-div:before{
        content: '';
        display: block;  
        position: absolute;
        top:-20px;
        left:20%;
        border-top: 10px solid transparent;
        border-right: 8px solid transparent;
        border-bottom: 10px solid #FFCD86;
        border-left: 8px solid transparent;
    }
    .tooltip-uh-1 .tooltip-div:before{
        left:7.5%;
    }
    .tooltip-uh-2 .tooltip-div:before{
        left:34.5%;
    }
    @media(max-width: 720px){
        .tooltip-manage-a-classroom .tooltip-div {
            font-size: 12px;
        }
    }
    /* END TOOLTIP OF CREATE CLASS PAGE */


    /* TABLE OF CREATE CLASS PAGE*/
    #table-available-worksheets-math-tab a,
    #table-available-worksheets-eng-tab a,
    #table-manage-your-classes a{
        cursor: pointer;
    }
    #table-available-worksheets-math-tab,
    #table-manage-your-classes{
        border: none;
        width: 100%;
    }
    #table-available-worksheets-math-tab th, #table-available-worksheets-math-tab td,
    #table-available-worksheets-eng-tab th, #table-available-worksheets-eng-tab td,
    #table-manage-your-classes th, #table-manage-your-classes td{
        border-top: none;
    }
    .all-table-create-class .table thead th{
        font-weight: normal;
        color: #838383;
        font-size: 13px;
    }
    .all-table-create-class{
        border: none;
    }

    /* END TABLE OF CREATE CLASS PAGE*/

    /* MODAL OF CREATE CLASS PAGE */
    .modal-classes-created .modal-content{
        color: #000 !important;
    }
    .modal-classes-created .modal-dialog{
        width: 80%;
    }
    .modal-classes-created a{
        text-decoration: none;
        cursor: pointer;
    }
    .modal-dialog-classes-created{
        margin: 0;
        padding: 0;
        width: 100% !important;
        height: 100% !important;
    }
    .modal-dialog-classes-created .modal-content{
        height: auto;
        min-height: 100%;
        border-radius: 0;
        margin: 0 !important;
        border: none;
        box-shadow: none;
    }
    /*ICON CLOSE */
    .icon-close-classes-created {
        position: absolute;
        top: 1%;
        right: 5%;
        cursor: pointer;
    }
    .icon-close-assignment-classes-created {
        float: right;
        cursor: pointer;
    }
    .icon-close-detail-classes-created {
        float: right;
        cursor: pointer;
    }
    .icon-close-update-homework {
        float: right;
        cursor: pointer;
    }
    /*ICON CLOSE */

    /* MODAL OF CREATE A CLASS PAGE */

    .pad-left-0{
        padding-left: 0!important;
    }
    .pad-right-0{
        padding-right: 0!important;
    }

    .img-height-22{
        height: 22px;
    }
    .img-height-24{
        height: 21px;
    }
    .img-height-20{
        height: 20px;
    }
    .img-height-17{
        height: 17px;
    }
    .collection_radio_buttons{
        position: absolute;
        width: 95%;
    }
    .underline{
        text-decoration: underline;
    }
    .number-tp{
        font-weight: 700;
        padding-right: 5px;
    }

    .content-math{
        background: #fff !important;
    }
    .form-div-border{
        padding: 20px;
        margin-bottom: 30px !important;
        margin-top: 10px;
        border: 1px solid #bdc3c7;
    }
    #create-group .entry-content{
        min-height: 430px;
    }
    .page-title{
        font-size: 24px !important;
    }
    input[type=number]::-webkit-inner-spin-button, input[type=number]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    .class-name-grey{
        color: #8B8B8B;
        font-size: 14px;
        padding-right: 5px;
    }

    .op-style-space {
        border-left: 3px solid #fff;
        padding-left: 10px !important;
        right: 0;
        text-align: left;
        height: 50px;
        width: 120px;

    }
    #main1 .entry-content{
        padding-bottom: 0px;
        min-height: 0px;
    }
    .op-col-assign {
        text-align: center;
        width: 60px;
        border-left: 3px solid #fff;
        right: 0;
        /* height: 38px !important; */
        padding-top: 5px !important;
        height: 38px;
    }

    .assignment-table-content-center{


    }
    /* .th-assign-ordering{
            position: absolute;
            right: 90px;
            top: 129px;
            color: #838383;
            border: none !important;
    } */
    .page-title-responsive{
        width: 910px; margin: auto;
    }
    .content-page-responsive{
        width: 955px; margin: auto;
    }
    @media(max-width: 1200px){

        .page-title-responsive{
            width: 825px; margin: auto;
        }
        .content-page-responsive{
            width: 820px; margin: auto;
        }
        .assignment-table-content-center{
            width: 709px;
        }

    }
    @media(max-width: 992px){
        .page-title-responsive{
            width: 750px; margin: auto;
        }
        .content-page-responsive{
            width: 750px; margin: auto;
        }
        .assignment-table-content-center{
            width: 639px;
        }
        /* .th-assign-ordering{
                right: 15px;
        } */
    }
    @media(max-width: 768px){
        .page-title-responsive{
            width: 90%; margin: auto;
        }
        .content-page-responsive{
            width: 90%; margin: auto;
        }
        .assignment-table-content-center{
            width: auto;

        }
        /* .th-assign-ordering{
                right: 6%;
        } */
    }
    /* ===========Mobile responsive========= */
    @media(min-width: 320px) and (max-width: 420px){
        .button-mobile{
            width: 100% !important;
        }
        .label-create{
            padding-left: 16px !important;
        }
        .label-create span{
            padding-left: 30px;
        }
    }
    .hr-create-class{
        margin-top: 40px;
        margin-bottom: 0px;
    }
    @media(max-width: 768px){
        .hr-create-class{
            margin-top: 80px;
            margin-bottom: 0px;
        }
    }
    @media(max-width:420px ){
        .mobile-update-ordering-button,
        .mobile-new-assighment-button{
            width: 100%;
            padding: 5px 0 !important;
        }
        .ac-class{
            width: 100%;
            padding-left: 0 !important;
        }
    }

    /* =========== End Mobile responsive========= */

    /*=============End Create a class Page style=================*/

</style>

<style type="text/css">
    /* MODAL CLASS ASSIGNMENT */
    .table-assignment-created tr>td{
        border-top: none !important;
        border-bottom: none !important;
    }
    .table-assignment-created tr>td,.table-assignment-created tr>th{
        white-space: nowrap;
    }
    @media (min-width: 767px){
        .div-action-assign{
            padding-left: 7px;
        }
    }
    @media (max-width: 420px){
        .table-assignment-created tr>td{
            white-space: pre-wrap !important;
            height: 130px;
        }
        .td-check-active-icon{

            height: 130px;
        }
        .op-col-assign{
            height: 131px;
            padding-top: 50px !important;
        }
        .tooltip-classes-created {
            margin-top: 50px;
        }
    }
    .td-check-active-icon {
        border-right: 3px solid #fff;
        left: 0;
        height: 38px;
        width: 45px;
        text-align: center;
    }
    .td-check-active-icon img{
        padding: 0;
    }
    .input-assigned-classes-created{
        width: 50px;
        color: #01a0cc !important;
        text-align: center;
        margin-right: 10px;
    }
    /*===============Active and Deactivated section=================*/
    .active-class span,
    .deactivated-class span{
        padding-left: 5px;
    }
    .active-class{
        color: #66CC00;
    }
    .deactivated-class{
        color: #7f8c8d;
    }
    .new-assignment{
        border: none;
        width: 100%;
        height: 33px;
        color: #fff;
    }
    .new-assignment{
        background: #8C8C8C;
    }
    .ac-class-assign,
    .ac-class-assign div{
        padding-right: 0 !important;
        padding-left: 5px !important;
    }
    .ac-class-assign{
        padding-top: 20px !important;
    }

    @media(max-width: 420px){
        .ac-class-assign .mobile-update-ordering-button{
            padding-left: 0px !important;
        }
    }

    @media(max-width: 767px){
        /*.table-assignment-created .ac-class-assign:nth-child(1){
                margin-left: 12px;
        }*/
        .ac-class-assign,
        .ac-class-assign .mobile-new-assighment-button{
            padding-left: 0px !important;
        }
        .ac-class-assign .active-class{
            padding-left: 18px !important;
        }
        .ac-class-assign{
            padding-top: 20px !important;
        }
        .ac-class-assign div:nth-child(3),.ac-class-assign div:nth-child(4){
            margin-top: 5px;
            padding-left: 18px !important;
        }
    }

    @media(max-width: 991px){
        .ac-class-assign div:nth-child(3){
            margin-top: 20px;
        }
    }

    .red-brown .table-striped > tbody > tr:nth-of-type(2n+1) > td:nth-of-type(5) {
        background-color: #dfdfdf;
    }
    .red-brown .table-striped > tbody > tr:nth-of-type(2n+1) > td:nth-of-type(6) {
        background-color: #dfdfdf;
    }
    .red-brown .table-striped > tbody > tr:nth-of-type(2n) > td:nth-of-type(4) {
        background-color: #fff;
    }
    .table-assignment-created .table-striped > tbody > tr:nth-of-type(2n+1) > td:nth-of-type(1){
        background-color: #dfdfdf;
    }
    .table-assignment-created .table-striped > tbody > tr:nth-of-type(2n+1) > td:nth-of-type(6){
        background-color: #dfdfdf;
    }
    .table-assignment-created .table-striped > tbody > tr:nth-of-type(2n+1) > td:nth-of-type(7){
        background-color: #dfdfdf;
    }

    /*===============End Active and Deactivated section=================*/

    /* =========tooltip========= */
    .tooltip-classes-created {
        position: relative;
	    display: inline-block;
	    opacity: 1;
	    margin-top: 1px;
	    margin-left: 3px;
	    z-index: auto;
	    text-align: center;
    }
    /*.tooltip-classes-created:hover .tooltip-div {
        visibility: visible;
    }*/
    .tooltip-classes-created .tooltip-div {
        left: 33px;
        top: -60px;
        visibility: hidden;
        background:#FFCD86;
        font-weight: 500;
        padding: 5px 0;
        color: #000;
        padding:20px;
        width: 300px;
        /*Position the tooltip*/
        position: absolute;
        z-index: 1;
        margin-top:15px; 
        font-size: 14px;
    }
    .tooltip-classes-created .tooltip-div:before{
        content: '';
        display: block;  
        position: absolute;
        top:45px;
        left:-5%;
        border-top: 10px solid transparent;
        border-right: 8px solid #FFCD86;
        border-bottom: 10px solid transparent;
        border-left: 8px solid transparent;
    }
    .tooltip-classes-created .tooltip-div a{
        color: #dba144;
    }
    /* =========tooltip========= */
    /* END MODAL CLASS ASSIGNMENT */

    /* ============================================================================================================== */

    /* MODAL UPDATE HOMEWORK */
    .hl-checkbox{
        width: 44px;
        padding-left: 15px !important;
    }
    /* END MODAL UPDATE HOMEWORK */

    /* MODAL NEW ASSIGNMENT */
    #available-worksheet-tabs .nav-tabs li a{
        padding: 8px 60px;
        border: 1px solid #ddd;
        color: #ddd;
        margin-right: 0;
        border-radius: 0;  
    }
    #available-worksheet-tabs .nav-tabs li.active a{
        color: #000;
        border-bottom-color: transparent;
    }
    #available-worksheet-tabs .tab-heading{
        padding:10px;
        margin-bottom: 25px; 
    }

    /* END MODAL NEW ASSIGNMENT */

    /* ============================================================================================================== */

    /* MODAL UPDATE SUBSCRIPTION */

    .bd-update-subscription-modal-lg table th{
        color:#BBBBBB;

    }
    .bd-update-subscription-modal-lg table td{
        font-weight: bold;
        text-align: center;
    }
    .bd-update-subscription-modal-lg table thead tr th:nth-child(1){
        width: 15%;
        text-align: center;
    }
    .bd-update-subscription-modal-lg table thead tr th:nth-child(2){
        width: 25%;
        text-align: center;
    }
    .bd-update-subscription-modal-lg table thead tr th:nth-child(3){
        padding-left: 30px;
    }
    .bd-update-subscription-modal-lg table tr td:nth-child(3){
        padding-left: 30px;
        text-align: left;
    }
    .bd-update-subscription-modal-lg table thead tr th:nth-child(1),
    .bd-update-subscription-modal-lg table thead tr th:nth-child(2),
    .bd-update-subscription-modal-lg table td:nth-child(1),
    .bd-update-subscription-modal-lg table td:nth-child(2){
        border-right: 1px solid #CCCCCC;
    }
    .total-amount-ud-sub{
        background: #DFDFDF;
        padding:20px;
        margin: 20px 0;
        text-align: right;
    }
    .op-new-assign{
        border-left: 3px solid #fff;
        padding-left: 10px !important;
        right: 7px;
        text-align: left;
        height: 36px;
        width: 80px;

    }

    /* END MODAL UPDATE SUBSCRIPTION */
    /* =============================================================================== */

    /*PREVIEW-SAMPLE-PAGE*/

    /* Set gray background color and 100% height */
    .sidenav {
        padding-top: 20px;
        background-color: #052206;
        height: 100%;
        padding-bottom: 5000px;
        margin-bottom: -5000px;
    }

    /* On small screens, set height to 'auto' for sidenav and grid */
    @media screen and (max-width: 767px) {
        .sidenav {
            /*height: auto;
            padding: 15px;*/
        }
    }

    .preview-step-btn {
        width: 100%;
        background: #F6F2C3;
        color: #29433A;
        font-size: 25px;
        border: none;
        height: 35px;
        border-right: 10px solid #29433A;
    }
    .preview-sup-btn{
        width: 90%;
        margin: auto;
        height: 50px;
        background: #F0FFFA;
    }
    .preview-answer-input::placeholder{
        color: #fff;
        font-weight: bold;
        text-transform: uppercase;
    }
    .preview-answer-input{
        color: #fff !important;
        height: 100%;
        background: #2F2D1E !important;
        border: none;
    }

    /* Switch icon css */
    .onoffswitch {
        position: relative; width: 90px;
        -webkit-user-select:none; -moz-user-select:none; -ms-user-select: none;
    }
    .onoffswitch-checkbox {
        display: none;
    }
    .onoffswitch-label {
        display: block; overflow: hidden; cursor: pointer;
        border: 1px solid #000; border-radius: 20px;
    }
    .onoffswitch-inner {
        display: block; width: 200%; margin-left: -100%;
        -moz-transition: margin 0.3s ease-in 0s; -webkit-transition: margin 0.3s ease-in 0s;
        -o-transition: margin 0.3s ease-in 0s; transition: margin 0.3s ease-in 0s;
    }
    .onoffswitch-inner:before, .onoffswitch-inner:after {
        display: block; float: left; width: 50%; height: 30px; padding: 0; line-height: 30px;
        font-size: 14px; color: white; font-family: Trebuchet, Arial, sans-serif; font-weight: bold;
        -moz-box-sizing: border-box; -webkit-box-sizing: border-box; box-sizing: border-box;
    }
    .onoffswitch-inner:before {
        content: "ON";
        padding-left: 10px;
        background-color: #40665B;
        color: #569584;
    }
    .onoffswitch-inner:after {
        content: "OFF";
        padding-right: 10px;
        background-color: #40665B; color: #569584;
        text-align: right;
    }
    .onoffswitch-switch {
        display: block;
        width: 40px;
        margin: -4px;
        background: #40665B;
        border: 1px solid #000;
        border-radius: 20px;
        position: absolute;
        top: 0;
        bottom: 0;
        right: 57px;
        -moz-transition: all 0.3s ease-in 0s;
        -webkit-transition: all 0.3s ease-in 0s;
        -o-transition: all 0.3s ease-in 0s;
        transition: all 0.3s ease-in 0s;
    }
    .onoffswitch-checkbox:checked + .onoffswitch-label .onoffswitch-inner {
        margin-left: -20px;
    }
    .onoffswitch-checkbox:checked + .onoffswitch-label .onoffswitch-switch {
        right: 0px; 
    }
    /* End Switch icon css */

    /*Sound-icon-css*/
    .sound {
        width: 50px;
        height: 50px;
        position: relative;
        cursor: pointer;
        display: inline-block;
    }
    .sound--icon {
        color: #40665B;
        width: 75%;
        height: 100%;
        line-height: 100%;
        font-size: 100px;
        display: block;
        margin: auto;
    }
    .sound--wave {
        position: absolute;
        border: 4px solid transparent;
        border-right: 4px solid #40665B;
        border-radius: 50%;
        -webkit-transition: all 200ms;
        transition: all 200ms;
        margin: auto;
        top: 0;
        bottom: 0;
        left: 0;
        right: 0;
    }
    .sound--wave_one {
        width: 50%;
        height: 50%;
        top: -12px;
    }
    .sound--wave_two {
        width: 75%;
        height: 75%;
        top: -12px;
    }
    .sound-mute .sound--wave {
        border-radius: 0;
        width: 50%;
        height: 50%;
        border-width: 0 4px 0 0;
    }
    .sound-mute .sound--wave_one {
        -webkit-transform: rotate(45deg) translate3d(0, -50%, 0);
        transform: rotate(45deg) translate3d(0, -50%, 0);
    }
    .sound-mute .sound--wave_two {
        -webkit-transform: rotate(-45deg) translate3d(0, 50%, 0);
        transform: rotate(-45deg) translate3d(0, 50%, 0);
    }
    /*End Sound-icon-css*/

    /*PREVIEW-SAMPLE-PAGE*/
	.cb-type{
		text-align: left;
	}
	.cb-type label{
		color: #909090;
		margin-left: 25px;
	}
	.option-input {
	  	-webkit-appearance: none;
		-moz-appearance: none;
		-ms-appearance: none;
		-o-appearance: none;
		appearance: none;
		position: relative;
		top: 4.333px;
		right: 0;
		bottom: 0;
		left: 0;
		height: 22px;
		width: 22px;
		background: #fff;
		border: 2px solid #979797;
		color: #fff;
		cursor: pointer;
		display: inline-block;
		margin-right: 0.5rem;
		outline: none !important;
		position: relative;
		z-index: 1000;
		border-radius: 6px;
		text-decoration: none;
	}
	.option-input:hover {
	  	background: #fff;
	}
	.option-input:checked {
	  	background: #979797;
	}
	.option-input:checked {
	  	 content: "";
                background: url('http://ikteacher.com/wp-content/themes/ik-learn/library/images/check_box_checked.png') no-repeat center;
	  	background-size: contain;
	  	display: inline-block;
                    border: none;
                    
	  	
	}
	.option-input:checked::after {
	  	
	  	display: block;
	  	position: relative;
	  	z-index: 100;
	}

</style>
<div class="row">
    <div class="col-sm-9 col-md-9">
        <div class="form-group">
            <input type="text" id="ex" class="form-control" value="" placeholder="Type Here!">
        </div>
    </div>
    <div class="col-sm-3 col-md-3 button-mobile">
        <div class="form-group">
            <button type="submit" name="submit" class="create-a-class-btn">Search</button>
        </div>
    </div>
    <div class="form__boolean" id="checkBoxSearch" style="margin-bottom: 10px;">
		<div class="col-md-4 col-xs-12 cb-type">
			<label>
			    <input type="radio" class="radio_buttons required class_cb_search option-input radio" name="cb-type"/>
			    Class Name
			</label>
		</div>
		<div class="col-md-4 col-xs-12 cb-type">
			<label>
			    <input type="radio" checked class="radio_buttons required class_cb_search option-input radio" name="cb-type"/>
			    Worksheet Name
			</label>
		</div>
		<div class="col-md-4 col-xs-12 cb-type">
			<label>
			    <input type="radio" class="radio_buttons required class_cb_search option-input radio" name="cb-type"/>
			    Student Name
			</label>
		</div>
  	</div>
</div>
<script type="text/javascript">
    jQuery(function ($) {
        $('#checkBoxSearch').delegate('img', 'click', function () {

            $('#cb-class-name').removeAttr("checked");
            $('#cb-worksheet-name').removeAttr("checked");
            $('#cb-student-name').removeAttr("checked");
            $('#cb-class-name').next().attr('src', "<?php echo get_template_directory_uri(); ?>/library/images/check_box_empty.png");
            $('#cb-worksheet-name').next().attr('src', "<?php echo get_template_directory_uri(); ?>/library/images/check_box_empty.png");
            $('#cb-student-name').next().attr('src', "<?php echo get_template_directory_uri(); ?>/library/images/check_box_empty.png");

            $(this).attr('src', "<?php echo get_template_directory_uri(); ?>/library/images/check_box_checked.png");
            $(this).prev().attr('checked', true);
        });
    });
</script>
<div class="col-md-12">
    <div class="title-border row" style="padding-top: 25px;">
        <div class="col-md-6">
            <p class="Name-pass-p label-title-create-class">List of the Classes</p>
        </div>

    </div>
</div>

<div class="col-md-12" style="padding: 0!important; overflow-y: auto; height: 500px;">
    <div class="" style="overflow-x:scroll;overflow-y:visible;height: auto;">
        <div id="table-manage-your-classes" class="table-responsive all-table-create-class">
            <!-- <label class="label-title-create-class" style="position: absolute; top: 0;">Manage your Classes</label> -->

            <table class="table table-striped table-sm">
                <thead>
                    <tr>
                        <th>Classes</th>
                        <th>Price</th>
                        <th># of Students</th>
                        <th>Expiration Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($groups->items)) : ?>
                        <tr><td colspan="5" class="text-center"><?php _e('No results', 'iii-dictionary') ?></td></tr>
                        <tr><td colspan="5">&nbsp&nbsp</td></tr>
                        <tr><td colspan="5">&nbsp&nbsp</td></tr>
                        <tr><td colspan="5">&nbsp&nbsp</td></tr>
                        <tr><td colspan="5">&nbsp&nbsp</td></tr>
                        <tr><td colspan="5">&nbsp&nbsp</td></tr>
                        <tr><td colspan="5">&nbsp&nbsp</td></tr>
                        <tr><td colspan="5">&nbsp&nbsp</td></tr>
                        <tr><td colspan="5">&nbsp&nbsp</td></tr>
                    <?php else : ?>
                        <?php foreach ($groups->items AS $group) : ?>
                            <?php
                            $group_detail = MWDB::get_group_detail($group->id);
                            $get_user_subscription_by_group_id = MWDB::get_user_subscription_by_group_id($group->id);
//                            var_dump($get_user_subscription_by_group_id);
                            ?>


                            <tr><form method="post">
                                <td><p class="overflow-td"><input type="hidden" name="abc" value="<?php echo $group->id ?>"><?php echo $group->name ?></p>
                                </td></form>

                                <td class="price-td">
                                    <?php
                                    $price = number_format($group_detail[0]->price,2,'.',',');
                                    if ($group_detail[0]->price != 0) {
                                        echo "$ " . $price;
                                    } else {
                                        echo $group->group_type;
                                    }
                                    ?></td>
                                <td><?php echo is_null($get_user_subscription_by_group_id[0]->count) ? 0 : $get_user_subscription_by_group_id[0]->count ?></td>
                                <td><?php echo $get_user_subscription_by_group_id[0]->expired_on ?></td>
                                <td class="op-style-space"><a class="view-student" data-gname="<?php echo $group->name ?>" data-gid="<?php echo $group->id ?>"><img class="img-height-24" src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Students.png"></a><a class="view-assignment" data-gname="<?php echo $group->name ?>" data-gid="<?php echo $group->id ?>" style="margin-left: 10px;"><img class="img-height-24" src="<?php echo get_template_directory_uri(); ?>/library/images/icon_assignment.png"></a><a class="view-ud-subscription" data-gname="<?php echo $group->name ?>" data-gid="<?php echo $group->id ?>" data-stu="<?php echo is_null($get_user_subscription_by_group_id[0]->number_of_students) ? 0 : $get_user_subscription_by_group_id[0]->number_of_students ?>" data-price="<?php
                                    if ($group_detail[0]->price != 0) {
                                        echo $price;
                                    } else {
                                        echo $group->group_type;
                                    }
                                    ?>" data-expired="<?php echo $get_user_subscription_by_group_id[0]->expired_on ?>" style="margin-left: 10px;"><img style="height:21px" src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Message.png"></a></td>
                            </tr>
                        <?php 
                        endforeach; 
                        $cnt = count($groups->items);
                        for($i = $cnt; $i < 10; $i++){
                            echo '<tr><td colspan="5">&nbsp&nbsp</td></tr>';
                        }
                        ?> <!-- data-toggle="modal" data-target=".bd-detail-classes-created-modal-lg" -->
                    <?php endif ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div>
    <div class="col-xs-2 col-sm-5 col-md-5 ac-class">

    </div>
    <div class="col-xs-10 col-sm-7 col-md-7 ac-class" style="margin: 30px 0 45px 0;">
        <div class="deactivated-class col-sm-offset-2 col-xs-4 col-sm-3" style="right: -3%; text-align: right;">

            <img class="img-height-20" src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Students_Gray.png"><span>Students</span>
        </div>
        <div class="deactivated-class col-xs-4 col-sm-4" style="right: 0; text-align: right; padding-right: 0 !important">
            <img class="img-height-20" src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Assignment_Grey.png" style="width: 20px;"><span>Assignment</span>
        </div>
        <div class="deactivated-class col-xs-4 col-sm-3" style="right: 0; text-align: right; padding-right: 0 !important">
            <img class="img-height-20" src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Message_Gray.png" style="width: 20px;"><span>Message</span>
        </div>
    </div>
</div>
<?php
ik_enqueue_js_messages('pw_inc', __('Password incorrect.', 'iii-dictionary'));
ik_enqueue_js_messages('pw_changed', __('Saved!', 'iii-dictionary'));
ik_enqueue_js_messages('pw_change_err', __('Cannot change group password.', 'iii-dictionary'));
ik_enqueue_js_messages('empty_group', __('No student has joined this group yet.', 'iii-dictionary'));
ik_enqueue_js_messages('empty_op', __('(No Link)', 'iii-dictionary'));

ik_enqueue_js_messages('empty_assignment', __('No Homeworks assigned to this group yet.', 'iii-dictionary'));
?>
<script type="text/javascript">
    jQuery(function ($) {
        $(".view-student").click(function () {
            var tthis = $(this);
            $("#group-id").val(tthis.attr("data-gid"));
            var tbody = $("#classes-detail-list-students tbody");
            tbody.html("");
            $.get(home_url + "/?r=ajax/group/students", {gid: tthis.attr("data-gid")}, function (data) {
                data = JSON.parse(data);
                if (data.length > 0) {
                    $.each(data, function (i, v) {
                        var tr = "<tr><td>" + v.name + "</td><td>" + v.email + "</td><td>" + v.joined_date + "</td><td>" + v.done_hw + "</td></tr>";
                        tbody.append(tr);
                    });
                } else {
                    var tr = "<tr><td colspan='4'>" + JS_MESSAGES.empty_group + "</td></tr>";
                    tbody.append(tr);
                }
                var gname = tthis.attr("data-gname");
                $('#class-detail-group-name').html(gname);
                //document.getElementById("class-detail-group-name").innerHTML = tthis.attr("data-gname");
                $(".bd-detail-classes-created-modal-lg").modal();
            });
        });
        $(".view-assignment").click(function () {
            var tthis = $(this);
            var check_active = 1;
            var index_check_active = 0;
            var gid = tthis.attr("data-gid");
            var gname = tthis.attr("data-gname");
            $('#input-assign-homework-group-id').val(gid);
            $('#input-ud-homework-group-id').val(gid);
            $('.view-new-assignment').attr('data-gid',gid);
            $('.view-new-assignment').attr('data-gname',gname);
            //document.getElementById("input-assign-homework-group-id").value = tthis.attr("data-gid");
            //document.getElementById("input-ud-homework-group-id").value = tthis.attr("data-gid");
            $("#group-id-assignment").val(tthis.attr("data-gid"));
            var tbody = $("#table-classes-assign tbody");
            tbody.html("");
            $.get(home_url + "/?r=ajax/group/assignment", {gid: tthis.attr("data-gid")}, function (data) {
                data = JSON.parse(data);
                
                if (data.length > 0) {
                    $.each(data, function (i, v) {
                        
                        var src_check_active = "";
                        if (v.active == 2) {
                            src_check_active = "Icon_New.png";
                        } else if (v.active == 1) {
                            src_check_active = "icon_active.png";
                        } else {
                            src_check_active = "icon_inactive_gray.png";
                        }
                      
                        var tr = "<tr><td class='td-check-active-icon' style='padding-left:0px !important;'><div class='tooltip tooltip-classes-created col-xs-12 col-sm-12'><img class='img-assign-classes-created' style='height: 18px;' src='<?php echo get_template_directory_uri(); ?>/library/images/" + src_check_active + "'><div class='tooltip-div tooltip-div-assign'><div class='active-popup'><p>This is newly added worksheet. After updating the detail, you can either Activate or Deactivate the worksheet.</p></div></div></td><td>" + v.grade + "</td><td>" + v.name + "</td><td>" + v.assigned_date + "</td><td>" + v.deadline + "</td><td class='op-col-assign'><a class='view-ud-homework' data-toggle='modal' data-target='.bd-update-homework-modal-lg' data-sheet-id=" + v.sheet_id + "  data-assignment-id=" + v.id + "><img src='<?php echo get_template_directory_uri(); ?>/library/images/icon_n_update.png' style='height:23px;'></a></td></tr>";
                        // <input class='input-assigned-classes-created txt-ordering' type='number' data-id='"+v.sheet_id+"' value='"+v.ordering+"' min='1'>

                        // check_active = v.active;
                        // index_check_active = i;

                        tbody.append(tr);                        
                    });
                    for(var i = data.length;i < 10; i++){
                        var add_tr = '<tr><td colspan="6">&nbsp&nbsp</td></tr>';
                        tbody.append(add_tr);
                    }
                } else {
                    var tr = "<tr><td class='td-check-active-icon' style='padding-left:0px !important;'><div class='tooltip tooltip-classes-created col-xs-12 col-sm-12'><img class='img-assign-classes-created' style='height: 18px;' src='<?php echo get_template_directory_uri(); ?>/library/images/icon_inactive_gray.png'></div></td><td colspan='4'>" + JS_MESSAGES.empty_assignment + "</td><td style='background:#dfdfdf;' class='op-col-assign'><a><img src='<?php echo get_template_directory_uri(); ?>/library/images/icon_n_update_gray.png' style='height:23px;'></a></td></tr>";
                    tbody.append(tr);
                    for(var i = 0;i < 10; i++){
                        var add_tr = '<tr><td colspan="6">&nbsp&nbsp</td></tr>';
                        tbody.append(add_tr);
                    }
                }
                $('#class-assignment-group-name').html(gname);
                //document.getElementById("class-assignment-group-name").innerHTML = tthis.attr("data-gname");
                $(".bd-assignment-classes-created-modal-lg").modal();
                $(".view-ud-homework").click(function () {
                    var tthis = $(this);
                    document.getElementById("input-ud-homework-worksheet-id").value = tthis.attr("data-sheet-id");
                    document.getElementById("input-assignment-id").value = tthis.attr("data-assignment-id");
                });
                $(".img-assign-classes-created").each(function (index) {
                    $(this).on("click", function () {
                        var clicks = $(this).data('clicks');
                        var elements = document.getElementsByClassName('img-assign-classes-created'), i, len;
                        if (elements[index].src == "<?php echo get_template_directory_uri(); ?>/library/images/icon_inactive_gray.png") {
                            elements[index].src = "<?php echo get_template_directory_uri(); ?>/library/images/icon_active.png";
                            var elements_assignment = document.getElementsByClassName('view-ud-homework'), i, len;
                            $.get(home_url + "/?r=ajax/group/changeactive", {assignmentid: $(elements_assignment[index]).attr("data-assignment-id")}, function (data) {

                            });
                        } else if (elements[index].src == "<?php echo get_template_directory_uri(); ?>/library/images/icon_active.png") {
                            elements[index].src = "<?php echo get_template_directory_uri(); ?>/library/images/icon_inactive_gray.png";
                            var elements_assignment = document.getElementsByClassName('view-ud-homework'), i, len;
                            $.get(home_url + "/?r=ajax/group/changdeactive", {assignmentid: $(elements_assignment[index]).attr("data-assignment-id")}, function (data) {

                            });
                        } else {
                            if (clicks) {
                                var elements = document.getElementsByClassName('tooltip-div-assign'), i, len;
                                elements[index].style["visibility"] = "hidden";
                            } else {
                                var elements = document.getElementsByClassName('tooltip-div-assign'), i, len;
                                elements[index].style["visibility"] = "visible";
                            }
                            $(this).data("clicks", !clicks);
                        }
                    });
                });

                // $( ".img-assign-classes-created" ).each(function(index) {
                // 	var elems = document.getElementsByClassName("img-assign-classes-created");
                // 	if(check_active == 2){
                // 		elems[index_check_active].src = "<?php echo get_template_directory_uri(); ?>/library/images/Icon_New.png";
                // 	}
                // });
            });
        });
        $(".view-ud-subscription").click(function () {
            var tthis = $(this);
            var id;
            $('#get_id').val(tthis.attr('data-gid'));
            id=$(this).attr('data-gid');

            $("#select-class").selectBoxIt('selectOption',id.toString()).data("selectBox-selectBoxIt");
            $("#select-class").data("selectBox-selectBoxIt").refresh();
            //document.getElementById("select-class").innerHTML = tthis.attr("data-gname");
            //hdocument.getElementById("class-ud-subscription-group-name").innerHTML = tthis.attr("data-gname");
            //if ($.isNumeric(tthis.attr("data-price"))) {
                //document.getElementById("price-ud-sub").innerHTML = "$ "+ insertDecimal(parseFloat(tthis.attr("data-price")));
            //} else {
                //document.getElementById("price-ud-sub").innerHTML = tthis.attr("data-price");
            //}
//			document.getElementById("stu-ud-sub").innerHTML = tthis.attr("data-stu");
//			document.getElementById("expired-date-ud-sub").innerHTML = tthis.attr("data-expired");
            $(".bd-update-subscription-modal-lg").modal();
        });
        $(".view-new-assignment").click(function () {
            var tthis = $(this);
            var gid = tthis.attr("data-gid");
            var gname = tthis.attr("data-gname");
            $('#class-new-assignment-group-name').html(gname);
            //document.getElementById("class-new-assignment-group-name").innerHTML = document.getElementById("class-assignment-group-name").innerHTML;
        });
        $(".view-assign-homework").click(function () {
            var tthis = $(this);
            var sheet_name = tthis.attr("data-sheet-name");
            var sheet_id = tthis.attr("data-sheet-id");
            $('#assign-homework-worksheet-name').html(sheet_name);
            $('#input-assign-homework-worksheet-id').val(sheet_id);
            //document.getElementById("assign-homework-worksheet-name").innerHTML = tthis.attr("data-sheet-name");
            //document.getElementById("input-assign-homework-worksheet-id").value = tthis.attr("data-sheet-id");
        });
        function insertDecimal(num) {
            return num.toFixed(2);
        }

    });

</script>
<script type="text/javascript">
                                               
                                                function openCity(evt, cityName) {
                                                    var i, tabcontent, tablinks;
                                                    tabcontent = document.getElementsByClassName("tabcontent");
                                                    for (i = 0; i < tabcontent.length; i++) {
                                                        tabcontent[i].style.display = "none";
                                                    }
                                                    tablinks = document.getElementsByClassName("tablinks");
                                                    for (i = 0; i < tablinks.length; i++) {
                                                        tablinks[i].className = tablinks[i].className.replace(" active", "");
                                                    }
                                                    document.getElementById(cityName).style.display = "block";
                                                    evt.currentTarget.className += " active";

                                                    var received = document.getElementById("received");
                                                    var private_mess = document.getElementById("private-mess");
                                                    var postings = document.getElementById("postings");
                                                    var class_postings = document.getElementById("class-postings");
                                                    var header_tab = document.getElementById("header-tab");
                                                    var header_email = document.getElementById("header-email");
                                                    if(cityName == 'London'){
                                                        received.className += " active";
                                                        document.getElementById('tab-prv1').style.display = "block";
                                                    }

                                                    if(cityName == 'tab-prv1' || cityName == 'tab-prv2' || cityName == 'tab-prv3')
                                                        private_mess.className += " active";
                                                    else
                                                        private_mess.className.replace(" active", "");

                                                    if(cityName == 'Paris'){
                                                        postings.className += " active";
                                                        document.getElementById('tab-post1').style.display = "block";
                                                    }

                                                    if(cityName == 'tab-post1'){
                                                        header_tab.style.display = "block";
                                                        header_email.style.display = "none";
                                                    }

                                                    if(cityName == 'tab-post2' || cityName == 'tab-post3'){
                                                        header_tab.style.display = "none";
                                                        header_email.style.display = "block";
                                                    }

                                                    if(cityName == 'tab-post1' || cityName == 'tab-post2' || cityName == 'tab-post3')
                                                        class_postings.className += " active";
                                                    else
                                                        class_postings.className.replace(" active", "");
                                                    
                                                }

                                            </script>
<div id="modal-question" class="modal fade modal-red-brown">
    <div class="modal-dialog modal-custom-first css-no-padding">
        <div class="modal-content boder-black" style="background: #86C1D1;">
            <div id="view-result-writing-body" style="color: #000;">
            </div>
            <div class="modal-footer footer-custom" style="padding-left: 4%;padding-right: 4%;padding-bottom: 3% !important;">
                <div><img class="icon-close-update-subscription pull-right" data-dismiss="modal" style="cursor: pointer;" src="<?php echo get_template_directory_uri(); ?>/library/images/close_white.png"></div>
                <div class="cancel-schedule css-cancel1 css-font-weight" style="text-align: left;color: #fff;padding-top: 10px;">Are you sure you want to delete the message?</div>
                <div style="margin-top: 40px;">
                    <input type="button" id="btn-ok-cancel" class="css-btn-delete" value="Delete">
                    <input type="button" id="btn-no" class="css-btn-cancel" value="Cancel">
                </div>
            </div>
        </div>
    </div>
</div>
<div id="modal-question-posting" class="modal fade modal-red-brown">
    <div class="modal-dialog modal-custom-first css-no-padding">
        <div class="modal-content boder-black" style="background: #86C1D1;">
            <div id="view-result-writing-body" style="color: #000;">
            </div>
            <div class="modal-footer footer-custom" style="padding-left: 4%;padding-right: 4%;padding-bottom: 3% !important;">
                <div><img class="icon-close-update-subscription pull-right" data-dismiss="modal" style="cursor: pointer;" src="<?php echo get_template_directory_uri(); ?>/library/images/close_white.png"></div>
                <div class="cancel-schedule css-cancel1 css-font-weight" style="text-align: left;color: #fff;padding-top: 10px;">Are you sure you want to delete the post?</div>
                <div style="margin-top: 40px;">
                    <input type="button" id="btn-ok-cancel-posting" class="css-btn-delete" value="Delete">
                    <input type="button" id="btn-no-posting" class="css-btn-cancel" value="Cancel">
                </div>
            </div>
        </div>
    </div>
</div>
<div id="modal-send-message" class="modal modal-red-brown"  style="z-index: 9999;">
    <div class="modal-dialog modal-custom-first css-no-padding">
        <div class="modal-content boder-black" style="background: #86C1D1;">
            <div id="view-result-writing-body" style="color: #000;">
            </div>
            <div class="modal-footer footer-custom" style="padding-left: 4%;padding-right: 4%;padding-bottom: 3% !important;">
                <div><img class="icon-close-update-subscription pull-right" data-dismiss="modal" style="cursor: pointer;" src="<?php echo get_template_directory_uri(); ?>/library/images/close_white.png"></div>
                <div class="cancel-schedule css-cancel1 css-font-weight" style="text-align: left;color: #fff;padding-top: 10px;">You message has been sent</div>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="modal-send-message-posting" class="modal modal-red-brown"  style="z-index: 9999;">
    <div class="modal-dialog modal-custom-first css-no-padding">
        <div class="modal-content boder-black" style="background: #86C1D1;">
            <div id="view-result-writing-body" style="color: #000;">
            </div>
            <div class="modal-footer footer-custom" style="padding-left: 4%;padding-right: 4%;padding-bottom: 3% !important;">
                <div><img class="icon-close-update-subscription pull-right" data-dismiss="modal" style="cursor: pointer;" src="<?php echo get_template_directory_uri(); ?>/library/images/close_white.png"></div>
                <div class="cancel-schedule css-cancel1 css-font-weight" style="text-align: left;color: #fff;padding-top: 10px;">You message has been posted!</div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade bd-update-subscription-modal-lg modal-classes-created" tabindex="-1" role="dialog" id="modal-message-center" aria-labelledby="myLargeModalLabel" aria-hidden="true" style="background:#fff;">
    <div class="modal-dialog modal-lg modal-dialog-classes-created modal-dialog-assignment-created">
        <div class="modal-content">
            <header class="col-xs-12 article-header">
                <div class="row">
                    <div class="container">
                        <div class="page-title-responsive">
                            <h1 class="page-title text-uppercase pull-left" itemprop="headline" style="padding-top: 8px;">Message center</h1>
                            <img class="icon-close-update-subscription pull-right" data-dismiss="modal" style="cursor: pointer;" src="<?php echo get_template_directory_uri(); ?>/library/images/close_blue.png">
                        </div>
                    </div>

                </div>
            </header>
            <div class="container"> <!-- add div container only content -->
                <section class="col-xs-12 entry-content">
                    <div class="row">
                        <div class="content-page-responsive">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="row">
                                                <div class="choose col-md-6" >
                                                    <div class="form-group">
                                                        <input type="hidden" name="get_id" id="get_id" value="0">

                                                    <form method="get">
                                                        <select class="select-box-it form-control" name="sl-class" id="select-class" >
                                                            <?php
                                                            foreach ($groups->items AS $group) : ?>
                                                            <option value="<?php echo $group->id ?>" ><?php echo $group->name ?></option>
                                                            <?php
                                                            endforeach;?>

                                                        </select>
                                                    </form>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div id="tab-select-class" class="tab" style="height: 40px;" >
                                                        <button class="tablinks ic-class-porting css-class-porting" onclick="openCity(event, 'Paris')" id="class-postings">Class Posting</button>
                                                        <button class="tablinks ic-private-message css-private-message" id="private-mess" onclick="openCity(event, 'London')">Private Message</button>

                                                    </div>
                                                </div>

                                                <div class="col-md-12">
                                                    <div id="Paris" style="padding: 0px;" class="tabcontent" onclick="document.getElementById('Paris').style.display = 'block'">
                                                        <div style="border-bottom: 1px solid #cccc;">
                                                            <div class="col-md-6" style="padding: 14px 0px !important;">
                                                                <div id="header-tab">
                                                                    <a class="prev" style="padding: 2px 8px; background: #f4f4f4;"><</a>
                                                                    <a class="next" style="padding: 2px 8px; background: #f4f4f4; margin-right: 15px; margin-left: 5px;">></a><span>01/08</span>
                                                                </div>
                                                                <div id="header-email">
                                                                    <span style="color: #a9a9a9;">
                                                                        <?php 
                                                                        $current_user = wp_get_current_user();
                                                                        echo 'My Email: '.$current_user->user_email;
                                                                        ?>                
                                                                    </span>
                                                                </div>
                                                            </div>

                                                            <div class="tab tab-prv col-md-6 css-tab-sub-message">
                                                                <button class="tablinks" id="student_plist" onclick="openCity(event, 'tab-post3')">Student List</button>
                                                                <button class="tablinks" id="write_post" onclick="openCity(event, 'tab-post2')">Write a Post</button>

                                                                <button class="tablinks" id="postings"  onclick="openCity(event, 'tab-post1')">Postings</button>
                                                            </div>
                                                            <div style="clear:both;"></div>
                                                        </div>
                                                        <div id="tab-post1" style="display: block; padding: 0px; height: 500px;overflow-y: auto;" class="tabcontent css-table-class">
                                                            <table class="table table-striped table-sm" style="padding: 0px; margin-bottom: 2px;">
                                                                <thead>
                                                                    <tr>
                                                                        <th style="width: 50%;text-align: left;padding-left: 15px;  border: none !important;">Postings</th>
                                                                        <th style="width: 20%;text-align: left; border: none !important;">Student Name</th>
                                                                        <th>Date</th>
                                                                        <th></th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody id="table-postings-class">

                                                                </tbody>
                                                            </table>
                                                        </div>
                                                        <div id="detail-postings">
                                                            <h4 ><span id="dpt-subject"></span><span id="dpt-id" class="del-subject-posting" style="float: right; width: 20px; cursor: pointer;">&nbsp;</span></h4>
                                                            <div style="font-size: 13px; font-style: italic; color: #0093C2; margin-top: -5px;">by: <span id="send-by">by: </span><span style="float: right;" id="dptDate"></span></div>
                                                            <p id="dptmessage" style="margin-top: 30px;"></p>           
                                                        </div>

                                                        <div id="tab-post2" class="tabcontent">
                                                            <div class="write_post row">
                                                                
                                                                <div class="col-md-12">
                                                                    <p>Subject</p>
                                                                    <input type="text" value="" name="post_subject" style="height: 34px;" id="post_subject">
                                                                </div>
                                                                <div class="col-md-12">
                                                                    <?php 
                                                                $editor_settings = array(
                                                                    'wpautop' => false,
                                                                    'media_buttons' => false,
                                                                    'quicktags' => false,
                                                                    'editor_height' => 270,
                                                                    'textarea_rows' => 15,
                                                                    'tinymce' => array(
                                                                        'toolbar1' => 'bold,italic,strikethrough,image,bullist,numlist,blockquote,hr,alignleft,aligncenter,alignright,spellchecker,fullscreen,wp_adv'
                                                                    )
                                                                );
                                                                ?>
                                                                 <div class="form-group" style="padding: 0px; border: 1px solid #ccc">
                                                                <?php wp_editor('', 'post_message', $editor_settings); ?>
                                                                </div>
                                                                </div>
                                                                <div class="col-md-3 text-center" style="float: right">
                                                                    <input type="submit" name="btn-send" value="Post" class="css-send-ms btn-post ">
                                                                </div>
                                                            </div>
                                                        </div>    

                                                        <div id="tab-post3" class="tabcontent" style="padding: 0px; height: 500px;overflow-y: auto;">
                                                            <table class="table table-striped table-sm" style="padding: 0px; margin-bottom: 2px;">
                                                                <thead>
                                                                <tr>
                                                                    <th style="width: 30%;text-align: left;padding-left: 15px;  border: none !important;">Student Name</th>
                                                                    <th style="width: 20%;text-align: left; border: none !important;">Email Address</th>
                                                                    <th style="width: 20%;text-align: left; border: none !important;">JoinedDate</th>
                                                                    <th>Date Left</th>
                                                                    <th></th>
                                                                </tr>
                                                                </thead>
                                                                <tbody id="table-student-list">


                                                                </tbody>
                                                            </table>
                                                        </div>
                                                        <div id="write-student-message" class="write_post row">     
                                                            <div class="col-md-6">
                                                                <?php
                                                                  $student_list = MWDB::get_users(array('user-sub' => 'no', 'user-type' => 'user'), 0, 20);
                                                                ?>
                                                                <p>To</p>
                                                                <div class="form-group">
                                                                    <select class="select-box-it form-control" name="sl-teacher" id="select-student-list" >
                                                                        
                                                                    </select>   
                                                                </div>
                                                            </div>   
                                                            <div class="col-md-6">
                                                                <p>Subject</p>
                                                                <input type="text" value="" name="post_subject_student" style="height: 34px;" id="post_subject">
                                                            </div>
                                                            <div class="col-md-12">
                                                                <?php 
                                                            $editor_settings = array(
                                                                'wpautop' => false,
                                                                'media_buttons' => false,
                                                                'quicktags' => false,
                                                                'editor_height' => 270,
                                                                'textarea_rows' => 15,
                                                                'tinymce' => array(
                                                                    'toolbar1' => 'bold,italic,strikethrough,image,bullist,numlist,blockquote,hr,alignleft,aligncenter,alignright,spellchecker,fullscreen,wp_adv'
                                                                )
                                                            );
                                                            ?>
                                                             <div class="form-group" style="padding: 0px; border: 1px solid #ccc">
                                                            <?php wp_editor('', 'post_message_student', $editor_settings); ?>
                                                            </div>
                                                            </div>
                                                            <div class="col-md-3 text-center" style="float: right">
                                                                <input type="submit" name="btn-send" value="Send" class="css-send-ms btn-post-student">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div id="London" style="padding: 0px;" class="tabcontent" onclick="document.getElementById('London').style.display = 'block'">
                                                        <div style="border-bottom: 1px solid #cccc;">
                                                            <div class="col-md-6" style="padding: 14px 0px !important;">
                                                                <a class="prev" style="padding: 2px 8px; background: #f4f4f4;"><</a><a class="next" style="padding: 2px 8px; background: #f4f4f4; margin-right: 15px; margin-left: 5px;">></a><span>01/08</span>
                                                            </div>

                                                            <div class="tab tab-prv col-md-6 css-tab-sub-message">
                                                                <button class="tablinks" id="new_message" onclick="openCity(event, 'tab-prv3')">New Message</button>
                                                                <button class="tablinks" id="sent" onclick="openCity(event, 'tab-prv2')">Sent</button>

                                                                <button class="tablinks" id="received"  onclick="openCity(event, 'tab-prv1')">Received</button>
                                                            </div>
                                                            <div style="clear:both;"></div>
                                                        </div>

                                                        <?php

                                                        ?>
                                                        <div id="tab-prv1" style="display: block; padding: 0px; height: 500px;

overflow-y: auto;" class="tabcontent css-table-class">
                                                            <table class="table table-striped table-sm" style="padding: 0px; margin-bottom: 2px;">
                                                                <thead>
                                                                    <tr>
                                                                        <th style="width: 50%;text-align: left;padding-left: 15px;  border: none !important;">Subject</th>
                                                                        <th style="width: 20%;text-align: left; border: none !important;">From</th>
                                                                        <th>ReceivedDate</th>
                                                                        <th></th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody id="table-message-receive">


                                                                </tbody>

                                                            </table>

                                                        </div>
                                                        <div id="detail-subject">

                                                            <h4 ><span id="tit-subject"></span><span class="del-subject" style="float: right; width: 20px; cursor: pointer;">&nbsp;</span></h4>
                                                            <div style="font-size: 13px; font-style: italic; color: #0093C2; margin-top: -5px;">by: <span id="send-by">by: </span><span style="float: right;" id="receivedDate"></span></div>
                                                            <p id="message" style="margin-top: 30px;"></p>
                                                            <div class="text-center col-md-3" style="float: right;margin-top: 20px;">
                                                                <input type="submit" name="btn-reply" value="Reply" id="btn-reply" class="css-send-ms" style="width: 100%;">
                                                            </div>
                                                        </div>


                                                    </div>
                                                    <div id="tab-prv2" class="tabcontent" style="padding: 0px; height: 500px;

overflow-y: auto;">
                                                        <table class="table table-striped table-sm" style="padding: 0px; margin-bottom: 2px;">
                                                            <thead>
                                                            <tr>
                                                                <th style="width: 50%;text-align: left;padding-left: 15px;  border: none !important;">Subject</th>
                                                                <th style="width: 20%;text-align: left; border: none !important;">Recipient</th>
                                                                <th>Sent Date</th>
                                                                <th></th>
                                                            </tr>
                                                            </thead>
                                                            <tbody id="table-message-sent">


                                                            </tbody>

                                                        </table>

                                                    </div>
                                                    <div id="detail-subject-sent">

                                                        <h4 ><span id="tit-subject-sent"></span><span class="del-subject" style="float: right; width: 20px; cursor: pointer;">&nbsp;</span></h4>
                                                        <div style="font-size: 13px; font-style: italic; color: #009dcb; margin-top: -5px;">by: <b><span id="sender">by: </span></b><span style="float: right;" id="sentDate"></span></div>
                                                        <p id="message-sent" style="margin-top: 30px;"></p>
                                                    </div>
                                                    <?php
                                                      $student_list = MWDB::get_users(array('user-sub' => 'no', 'user-type' => 'user'), 0, 100);
                                                    ?>
                                                    <div id="tab-prv3" class="tabcontent">
                                                        <div class="new_message row">
                                                            <div class="col-md-6">
                                                                <p>To</p>
                                                                <div class="form-group">
                                                                    <select class="select-box-it form-control" name="sl-teacher" id="select-teacher" >
                                                                        <?php 
                                                                        if(count($student_list->items) > 0){ 
                                                                            foreach ($student_list->items as $key => $value) {
                                                                        ?>
                                                                        <option value="<?php echo $value->ID ?>"><?php echo $value->display_name ?></option>
                                                                        <?php
                                                                            }
                                                                        }
                                                                        ?>
                                                                    </select>   
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <p>Subject</p>
                                                                <input type="text" value="" name="text_subject" style="height: 34px;" id="text_subject">
                                                            </div>
                                                            <div class="col-md-12">
                                                                <?php 
                                                            $editor_settings = array(
                                                                'wpautop' => false,
                                                                'media_buttons' => false,
                                                                'quicktags' => false,
                                                                'editor_height' => 270,
                                                                'textarea_rows' => 15,
                                                                'tinymce' => array(
                                                                    'toolbar1' => 'bold,italic,strikethrough,image,bullist,numlist,blockquote,hr,alignleft,aligncenter,alignright,spellchecker,fullscreen,wp_adv'
                                                                )
                                                            );
                                                            ?>
                                                             <div class="form-group" style="padding: 0px; border: 1px solid #ccc">
                                                            <?php wp_editor('', 'content_message', $editor_settings); ?>
                                                            </div>
                                                            </div>
                                                            <div class="col-md-3 text-center" style="float: right">
                                                                <input type="submit" name="btn-send" value="Send" class="css-send-ms btn-send">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            
                                        </div>
                                    </div>
                                   
                                </div>
                            </div>
                        </div>
                </section>
            </div>
        </div>
    </div>
</div>

<div class="modal fade bd-detail-classes-created-modal-lg modal-classes-created" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" style="background:#fff;">
    <div class="modal-dialog modal-lg modal-dialog-classes-created modal-dialog-assignment-created">
        <div class="modal-content">
            <header class="col-xs-12 article-header">
                <div class="row">
                    <div class="container">
                        <div class="page-title-responsive">
                            <h1 class="page-title text-uppercase pull-left" itemprop="headline" style="padding-top: 8px;">List of Students</h1>
                            <img class="icon-close-detail-classes-created" data-dismiss="modal" src="<?php echo get_template_directory_uri(); ?>/library/images/close_blue.png">
                        </div>
                    </div>
                </div>
            </header>
            <div class="container"> <!-- add div container only content -->
                <section class="col-xs-12 entry-content">
                    <div class="row">
                        <div class="content-page-responsive">
                            <div class="col-md-12">
                                <div class="form-group form-div-border">
                                    <span>
                                        <span class="class-name-grey">Class Name: </span>
                                        <strong id="class-detail-group-name"></strong>
                                    </span>
                                </div>
                            </div>
                            <div class="table-responsive all-table-create-class col-md-12">
                                <table class="table table-striped table-sm" id="classes-detail-list-students">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Email Address</th>
                                            <th>Date Joined</th>
                                            <th>Date Left</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                       <!-- <tr>
                                          <td>Peter Chung</td>
                                          <td>oniashc@gmail.com</td>
                                          <td>2018-01-17</td>
                                          <td>2018-02-17</td>
                                          </tr> -->
                                    </tbody>
                                </table>
                            </div>
                            <input type="hidden" name="group-id" id="group-id">
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>

<form method="post" action="<?php echo locale_home_url() . '/?r=teachers-box' ?><?php echo $gid ? '&amp;gid=' . $gid : '' ?>" id="main-form">
    <div class="modal fade bd-assignment-classes-created-modal-lg modal-classes-created" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" style="background:#fff;">
        <div class="modal-dialog modal-lg modal-dialog-classes-created modal-dialog-assignment-created">
            <div class="modal-content">
                <header class="col-xs-12 article-header">
                    <div class="row">
                        <div class="container">
                            <div class="page-title-responsive">
                                <h1 class="page-title text-uppercase pull-left" itemprop="headline" style="padding-top: 8px;">Class assignment</h1>
                                <img class="icon-close-assignment-classes-created" data-dismiss="modal" src="<?php echo get_template_directory_uri(); ?>/library/images/close_blue.png">
                            </div>
                        </div>
                    </div>
                </header>
                <div class="container"> <!-- add div container only content -->
                    <section class="col-xs-12 entry-content">
                        <div class="row">
                            <div class="content-page-responsive">
                                <div class="col-md-12">
                                    <div class="form-group form-div-border row">
                                        <span>
                                            <span class="class-name-grey">Class Name: </span>
                                            <strong id="class-assignment-group-name"></strong>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="content-page-responsive">
                                <div class="col-md-12" style="padding: 0!important;overflow-y: auto; height: 500px; position: relative;">
                                    <div class="assignment-table-content-center">
                                        <div class="table-responsive table-assignment-created all-table-create-class">

                                                <div class=" col-md-12">
                                                    <table class="table table-striped table-sm" id="table-classes-assign">
                                                        <thead>
                                                            <tr>
                                                                <th style="padding-left: 0px !important;"></th>
                                                                <th><?php _e('Name', 'iii-dictionary') ?></th>
                                                                <th><?php _e('Grade', 'iii-dictionary') ?></th>
                                                                <th><?php _e('Assigned Date', 'iii-dictionary') ?></th>
                                                                <th><?php _e('Deadline', 'iii-dictionary') ?></th>
                                                              <!-- <th class="th-assign-ordering"><?php _e('Ordering', 'iii-dictionary') ?></th> -->
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                        </tbody>
                                                    </table>
                                                </div>

                                            <input type="hidden" name="group-id-assignment" id="group-id-assignment">
                                        </div>
                                    </div>
                                </div>
                                <div style="width: 100%;">
                                    <div class="div-action-assign">
                                        <div class="col-xs-12 col-sm-5 col-md-6 ac-class-assign">
                                            <div class="active-class col-xs-6 col-sm-6 col-md-3">
                                                <img class="img-height-20" src="<?php echo get_template_directory_uri(); ?>/library/images/icon_active.png"><span>Acitivated</span>
                                            </div>
                                            <div class="deactivated-class col-xs-6 col-sm-6 col-md-4">
                                                <img class="img-height-20" src="<?php echo get_template_directory_uri(); ?>/library/images/icon_inactive_gray.png"><span>Deactivated</span>
                                            </div>
                                            <div class="deactivated-class col-xs-6 col-sm-6 col-md-2">
                                                <img class="img-height-20" src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_New.png" style="width: 20px;"><span style="color: #009DCA;">New</span>
                                            </div>
                                            <div class="deactivated-class col-xs-6 col-sm-6 col-md-3">
                                                <img class="img-height-20" src="<?php echo get_template_directory_uri(); ?>/library/images/icon_n_update_gray.png" style="width: 20px;"><span>Update</span>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-7 col-md-6 ac-class-assign">
                                            <div class="col-xs-6 col-sm-6 col-md-6 mobile-new-assighment-button">
                                                <!-- <button type="button" class="new-assignment" data-toggle="modal" data-target=".bd-new-assignment-modal-lg">New Assignment</button> -->
                                            </div>
                                            <div class="col-xs-6 col-sm-6 col-md-6 mobile-update-ordering-button">
                                                <button type="button" class="new-assignment view-new-assignment" data-toggle="modal" data-target=".bd-new-assignment-modal-lg">New Assignment</button>
                                                <!-- <button type="button" id="btn-set-ordering" class="update-ordering">Update Ordering</button> -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
</form>

<script type="text/javascript">
    jQuery(function ($) {
        $('.sound').click(function () {
            $(this).toggleClass('sound-mute');
        });

        /*$( ".img-assign-classes-created" ).each(function(index) {
         $(this).on("click", function(){
         
         var clicks = $(this).data('clicks');
         if (clicks) {
         var elements = document.getElementsByClassName('tooltip-div-assign'), i, len;
         elements[index].style["visibility"]="hidden";
         } else {
         var elements = document.getElementsByClassName('tooltip-div-assign'), i, len;
         elements[index].style["visibility"]="visible";
         }
         $(this).data("clicks", !clicks);
         
         
         });
         });
         $( ".deactive-assign-choice" ).each(function(index) {
         $(this).on("click", function(){
         var elements = document.getElementsByClassName('tooltip-div-assign'), i, len;
         elements[index].style["visibility"]="hidden";
         var elements = document.getElementsByClassName('img-assign-classes-created'), i, len;
         elements[index].src="<?php echo get_template_directory_uri(); ?>/library/images/icon_inactive_gray.png";
         var elements = document.getElementsByClassName('deactive-popup'), i, len;
         elements[index].className += " hidden";
         var elements = document.getElementsByClassName('active-popup'), i, len;
         elements[index].classList.remove("hidden");
         });
         });
         $( ".active-assign-choice" ).each(function(index) {
         $(this).on("click", function(){
         var elements = document.getElementsByClassName('tooltip-div-assign'), i, len;
         elements[index].style["visibility"]="hidden";
         var elements = document.getElementsByClassName('img-assign-classes-created'), i, len;
         elements[index].src="<?php echo get_template_directory_uri(); ?>/library/images/icon_active.png";
         var elements = document.getElementsByClassName('active-popup'), i, len;
         elements[index].className += " hidden";
         var elements = document.getElementsByClassName('deactive-popup'), i, len;
         elements[index].classList.remove("hidden");
         });
         });
         $( ".deactive-cancel-assign-choice" ).each(function(index) {
         $(this).on("click", function(){
         var elements = document.getElementsByClassName('tooltip-div-assign'), i, len;
         elements[index].style["visibility"]="hidden";
         });
         });
         $( ".active-cancel-assign-choice" ).each(function(index) {
         $(this).on("click", function(){
         var elements = document.getElementsByClassName('tooltip-div-assign'), i, len;
         elements[index].style["visibility"]="hidden";
         });
         });*/

        $('body').on('hidden.bs.modal', function (e) {
            if ($('.modal').hasClass('in')) {
                $('body').addClass('modal-open');
            }
        });

        $(".icon-close-classes-created").each(function (index) {
            $(this).on("click", function () {
                $(".modal-red-brown").modal('hide');
            });
            //  setTimeout(function() {
            //     $(".icon-close-classes-created").click();
            // }, 3000);
        });
        $('#btn-set-ordering').click(function () {
            $('.txt-ordering').each(function () {
                var id = $(this).attr("data-id");
                var number = $(this).val();
                $.get(home_url + "/?r=ajax/set-ordering", {id: id, number: number}, function (data) {
                });
            });
            location.reload();
        });
        $(".cb_uh_display_last_page").each(function (index) {
            $(this).on("click", function () {

                var clicks = $(this).data('clicks');
                if (clicks) {
                    var elements = document.getElementsByClassName('cb_uh_display_last_page'), i, len;
                    elements[index].src = "<?php echo get_template_directory_uri(); ?>/library/images/check_box_checked.png";
                } else {
                    var elements = document.getElementsByClassName('cb_uh_display_last_page'), i, len;
                    elements[index].src = "<?php echo get_template_directory_uri(); ?>/library/images/check_box_empty.png";
                }
                $(this).data("clicks", !clicks);
            });
        });
        $('#num-month-ud-sub').on('change', function () {
            document.getElementById("total-amount-ud-sub").innerHTML = "$" + insertDecimal(this.value * document.getElementById("num-students-ud-sub").value * 5);
        });
        $('#num-students-ud-sub').on('change', function () {
            document.getElementById("total-amount-ud-sub").innerHTML = "$" + insertDecimal(this.value * document.getElementById("num-month-ud-sub").value * 5);
        });

        function insertDecimal(num) {
            return num.toFixed(2);
        }

    });
</script>

<form method="post" action="<?php echo locale_home_url() . '/?r=teachers-box' ?><?php echo $gid ? '&amp;gid=' . $gid : '' ?>" id="main-form">
    <div class="modal fade bd-update-homework-modal-lg modal-classes-created" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" style="background:#fff;">
        <div class="modal-dialog modal-lg modal-dialog-classes-created modal-dialog-assignment-created">
            <div class="modal-content">
                <header class="col-xs-12 article-header">
                    <div class="row">
                        <div class="container">
                            <div class="page-title-responsive">
                                <h1 class="page-title text-uppercase pull-left" itemprop="headline" style="padding-top: 8px;">Update homework</h1>
                                <img class="icon-close-update-homework" data-dismiss="modal" src="<?php echo get_template_directory_uri(); ?>/library/images/close_blue.png">
                            </div>
                        </div>

                    </div>
                </header>
                <div class="container"> <!-- add div container only content -->
                    <section class="col-xs-12 entry-content">
                        <div class="row">
                            <div class="content-page-responsive">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <p class="Name-pass-p label-title-create-class">Homework Name</p>
                                                        <input type="hidden" name="sheet_id" value="" id="input-ud-homework-worksheet-id">
                                                        <input type="hidden" name="group_id" value="" id="input-ud-homework-group-id">
                                                        <input type="hidden" name="_cid" value="" id="input-assignment-id">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-sm-5 col-md-5">
                                                <div class="form-group">
                                                    <label for="">Name</label>
                                                    <input type="text" class="form-control" name="homework-name" value="" required="">
                                                </div>     
                                            </div>
                                            <div class="col-sm-5 col-md-5">
                                                <div class="form-group">
                                                    <label for="">Deadline</label>
                                                    <input type="text" class="form-control" id="deadline" name="deadline" value="" placeholder="No deadline">
                                                </div>     
                                            </div>

                                            <div class="col-xs-2 col-sm-2 col-md-2 button-mobile">
                                                <div class="form-group">
                                                    <button type="submit" name="submit" class="create-a-class-update-homework-btn">Reset</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form__group radio_buttons required application_disbursment_type_code title-border" aria-required="true">
                                            <label class="label-title-create-class" aria-required="true">Homework Mode</label>
                                            <div class="form__boolean">
                                                <span class="radio pad-left-0 col-md-3 col-xs-6">
                                                    <input role="moneychose" class="radio_buttons required pretty_radio" type="radio" value="1" name="for-practice" id="application_homework_mode_1" aria-required="true" required="">
                                                    <label class="collection_radio_buttons" for="application_homework_mode_1">Practice</label>
                                                </span>
                                                <span class="radio col-md-1 col-xs-6" style="margin-top: 10px;">
                                                    <input role="moneychose" class="radio_buttons required pretty_radio" type="radio" value="0" name="for-practice" id="application_homework_mode_2" aria-required="true">
                                                    <label class="collection_radio_buttons label-create" for="application_homework_mode_2">Test</label>
                                                </span>
                                                <div class="col-md-3" style="margin-top: 15px;margin-left: 20px;">
                                                    <img class="cb_uh_display_last_page pull-left img-height-22" src="<?php echo get_template_directory_uri(); ?>/library/images/check_box_checked.png">
                                                    <p class="pull-left" style="margin-left: 10px;color: #909090;">Display last page?</p>
                                                    <input type="hidden" name="checkboxpageadminmodal" value="0">
                                                    <input type="hidden" name="checkboxpagemodal" value="1">
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="tooltip tooltip-manage-a-classroom tooltip-uh-1 col-xs-12 col-sm-12">
                                                        <img style="margin-top: 2px; margin-left: -50px; height: 22px;" src="<?php echo get_template_directory_uri(); ?>/library/images/Question_Icon.png">
                                                        <div class="tooltip-div" style="left: -23%;">
                                                            <div class="tp">
                                                                <p>Some worksheet has multiple pages with answer showing at the end of the page. Mark on this will make the worksheet to show the last page(answer).</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form__group radio_buttons required application_disbursment_type_code title-border" aria-required="true">
                                            <label class="label-title-create-class" aria-required="true">Homework Retryable?</label>
                                            <div class="form__boolean">
                                                <span class="radio pad-left-0 col-md-3">
                                                    <input role="moneychose" class="radio_buttons required pretty_radio" type="radio" value="1" name="is-retryable" id="application_homework_retry_1" aria-required="true" required="">
                                                    <label class="collection_radio_buttons" for="application_homework_retry_1">Yes</label>
                                                </span>
                                                <span class="radio col-md-3" style="margin-top: 10px;">
                                                    <input role="moneychose" class="radio_buttons required pretty_radio" type="radio" value="0" name="is-retryable" id="application_homework_retry_2" aria-required="true">
                                                    <label class="collection_radio_buttons label-create" for="application_homework_retry_2">No</label>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form__group radio_buttons required application_disbursment_type_code title-border" aria-required="true">
                                            <label style="margin-bottom: 20px;" class="label-title-create-class" aria-required="true">Homework Links
                                                <div class="tooltip tooltip-manage-a-classroom tooltip-uh-2">
                                                    <img style="margin-top: -5px; margin-left: 5px; height: 22px;" src="<?php echo get_template_directory_uri(); ?>/library/images/Question_Icon.png">
                                                    <div class="tooltip-div" style="left: -300%;width: 300px;">
                                                        <div class="tp">
                                                            <p>Mark on this will link the worksheet of your choice upon the finish current worksheet</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </label>


                                            <div class="table-responsive all-table-create-class">
                                                <table class="table table-striped table-sm">
                                                    <thead>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td class="hl-checkbox"><img class="cb_uh_display_last_page pull-left img-height-22" src="<?php echo get_template_directory_uri(); ?>/library/images/check_box_checked.png"></td>
                                                            <td>No Link</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="hl-checkbox"><img class="cb_uh_display_last_page pull-left img-height-22" src="<?php echo get_template_directory_uri(); ?>/library/images/check_box_checked.png"></td>
                                                            <td>No Link</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="hl-checkbox"><img class="cb_uh_display_last_page pull-left img-height-22" src="<?php echo get_template_directory_uri(); ?>/library/images/check_box_checked.png"></td>
                                                            <td>No Link</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="hl-checkbox"><img class="cb_uh_display_last_page pull-left img-height-22" src="<?php echo get_template_directory_uri(); ?>/library/images/check_box_checked.png"></td>
                                                            <td>No Link</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-12">
                                        <div class="col-xs-6 col-sm-6 col-md-6 pad-left-0"><!-- 
                                         <button class="create-a-class-btn">Update</button> -->
                                            <button class="create-a-class-btn" type="submit" name="update-homework"><?php _e('Update', 'iii-dictionary') ?></button>
                                        </div>
                                        <div class="col-xs-6 col-sm-6 col-md-6 pad-right-0">
                                            <a type="submit" data-dismiss="modal" class="button-grey" style="margin: 0;"><?php esc_attr_e('Cancel', 'iii-dictionary') ?>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
</form>

<div class="modal fade bd-new-assignment-modal-lg modal-classes-created" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" style="background:#fff;">
    <div class="modal-dialog modal-lg modal-dialog-classes-created modal-dialog-assignment-created">
        <div class="modal-content">
            <header class="col-xs-12 article-header">
                <div class="row">
                    <div class="container">
                        <div class="page-title-responsive">
                            <h1 class="page-title text-uppercase pull-left" itemprop="headline" style="padding-top: 8px;">New Assignment</h1>
                            <img class="icon-close-new-assignment pull-right" data-dismiss="modal" style="cursor: pointer;" src="<?php echo get_template_directory_uri(); ?>/library/images/close_blue.png">
                        </div>
                    </div>
                </div>
            </header>
            <div class="container">
                <!-- add div container only content -->
                <section class="col-xs-12 entry-content">
                    <div class="row">
                        <div class="content-page-responsive">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="row">

                                        <div class="form-group form-div-border">
                                            <span>
                                                <span class="class-name-grey">Class Name: </span>
                                                <strong id="class-new-assignment-group-name"></strong>
                                            </span>
                                        </div>
                                        <div id="available-worksheet-tabs">	
                                            <div class="tab-heading row">
                                                <p class="col-md-6 Name-pass-p label-title-create-class">Available Worksheets</p>
                                                <ul class="nav nav-tabs">
                                                    <li class="pull-right"><a href="#math-tab" data-toggle="tab">Math</a>
                                                    </li>
                                                    <li class="active pull-right">
                                                        <a href="#english-tab" data-toggle="tab" style="border-right: 0;">English</a>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="tab-content row">
                                                <div class="tab-pane active" id="english-tab">
                                                    <div class="col-sm-9 col-md-9">
                                                        <div class="form-group">
                                                            <input type="text" class="form-control" value="" placeholder="Worksheet Name">
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-3 col-md-3 button-mobile">
                                                        <div class="form-group">
                                                            <button type="submit" name="submit" class="create-a-class-btn">Search</button>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-12 col-md-12">
                                                        <div class="form-group">
                                                            <div class="row tiny-gutter" style="margin-top: 15px;">
                                                                <div class="col-xs-12 col-sm-4 col-md-4">
                                                                    <?php MWHtml::sel_assignments($filter['assignment-id'], false, array(), 'Assignment', 'filter[assignment-id]', 'form-control', 'filter-assignment') ?>
                                                                </div>
                                                                <div class="col-xs-12 col-sm-4 col-md-4">
                                                                    <?php
                                                                    MWHtml::sel_homework_types($filter['homework-types'], array('first_option' => __('Homework Type', 'iii-dictionary'),
                                                                        'name' => 'filter[homework-types]', 'class' => 'form-control',
                                                                        'id' => 'filter-homework-types', 'subscribed_option' => true,
                                                                        'admin_panel' => true)
                                                                    )
                                                                    ?>
                                                                </div>
                                                                <div class="col-xs-12 col-sm-4 col-md-4">
<?php MWHtml::select_grades('ENGLISH', $filter['grade'], array('first_option' => 'Grade', 'class' => 'form-control', 'name' => 'filter[grade]')) ?>
                                                                </div>

                                                            </div>
                                                            <div class="row tiny-gutter" style="margin-top: 20px">
                                                                <div class="col-xs-12 col-sm-4 col-md-4">
                                                                    <select class="select-box-it form-control" name="filter[trivia-exclusive]">
                                                                        <option value="">Trivia Exclusive</option>
                                                                        <option value="1"<?php echo $filter['trivia-exclusive'] == '1' ? ' selected' : '' ?>>Yes</option>
                                                                        <option value="0"<?php echo $filter['trivia-exclusive'] == '0' ? ' selected' : '' ?>>No</option>
                                                                    </select>
                                                                </div>
                                                                <div class="col-xs-12 col-sm-4 col-md-4">
                                                                    <select class="select-box-it form-control" name="filter[active]">
                                                                        <option value="">Status</option>
                                                                        <option value="1"<?php echo $filter['active'] == '1' ? ' selected' : '' ?>>Active</option>
                                                                        <option value="0"<?php echo $filter['active'] == '0' ? ' selected' : '' ?>>Inactive</option>
                                                                    </select>
                                                                </div>
                                                                <div class="col-xs-12 col-sm-4 col-md-4">
<?php MWHtml::select_languages($filter['lang'], array('first_option' => 'Language', 'class' => 'form-control', 'name' => 'filter[lang]')) ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="title-border"></div>
                                                        <div class="row">
                                                            <div class="col-md-12" style="overflow-y: auto;height: 500px;">


                                                                    <div id="table-manage-your-classes" class="table-responsive all-table-create-class">
                                                                        <table class="table table-striped table-sm">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th>Assignment</th>
                                                                                    <th>Grade</th>
                                                                                    <th>Dictionary</th>
                                                                                    <th>Type</th>
                                                                                    <th>Sheet Name</th>
                                                                                    <th></th>
                                                                                </tr>
                                                                            </thead>
                                                                    <!-- <tbody>
                                                                    <tr>
                                                                    <td>Arithmetic</td>
                                                                    <td>(Notepad)<br>Add, Sub, Mult, Div</td>
                                                                    <td>Subscribed</td>
                                                                    <td>Adding</td>
                                                                    <td class="op-new-assign"><a data-toggle="modal" data-target=".bd-assign-modal-lg"><img class="img-height-20" src="<?php echo get_template_directory_uri(); ?>/library/images/icon_assign.png"></a><a data-toggle="modal" data-target=".bd-preview-modal-lg" style="margin-left: 10px;"><img class="img-height-24"  src="<?php echo get_template_directory_uri(); ?>/library/images/icon_preview2.png"></a></td>
                                                                    </tr>
                                                                    </tbody> -->
                                                                            <tbody><?php if (empty($avail_eng_sheets)) : ?>
                                                                                    <tr>
                                                                                        <td colspan="5">No results.</td>
                                                                                        <td class="op-new-assign" style="background: #dfdfdf; height: auto;"><a><img class="img-height-20" src="<?php echo get_template_directory_uri(); ?>/library/images/icon_assign_gray.png"></a><a style="margin-left: 10px;"><img class="img-height-24"  src="<?php echo get_template_directory_uri(); ?>/library/images/icon_preview2_gray.png"></a></td>
                                                                                    </tr>
                                                                                    <tr><td colspan="6">&nbsp&nbsp</td></tr>
                                                                                    <tr><td colspan="6">&nbsp&nbsp</td></tr>
                                                                                    <tr><td colspan="6">&nbsp&nbsp</td></tr>
                                                                                    <tr><td colspan="6">&nbsp&nbsp</td></tr>
                                                                                    <tr><td colspan="6">&nbsp&nbsp</td></tr>
                                                                                    <tr><td colspan="6">&nbsp&nbsp</td></tr>
                                                                                    <tr><td colspan="6">&nbsp&nbsp</td></tr>
                                                                                    <tr><td colspan="6">&nbsp&nbsp</td></tr>
                                                                                    <tr><td colspan="6">&nbsp&nbsp</td></tr>
                                                                                    <tr><td colspan="6">&nbsp&nbsp</td></tr>
                                                                                    <tr><td colspan="6">&nbsp&nbsp</td></tr>
                                                                                    <?php else : foreach ($avail_eng_sheets as $sheet) : ?>
                                                                                        <tr<?php echo $sheet->active ? '' : ' class="text-muted"' ?> data-id="<?php echo $sheet->id ?>" data-assignment="<?php echo $sheet->assignment_id ?>">
                                                                                            <td style="width: 150px !important;"><?php echo $sheet->assignment ?></td>
                                                                                            <td style="width: 115px !important;"><?php echo $sheet->grade ?></td>
                                                                                            <?php if (empty($sheet->name)) { ?>
                                                                                                <td class="hidden-xs" style="width: 12% !important"><?php echo $sheet->name ?></td>
        <?php } else { ?> 
                                                                                                <td class="hidden-xs" style="width: 12% !important"><?php echo $sheet->name ?></td>
        <?php } ?>
                                                                                            <td class="hidden-xs"><?php echo $sheet->homework_type ?></td>

                                                                                            <td><?php echo $sheet->sheet_name ?></td>



                                                                                            <td class="op-new-assign"><a class="view-assign-homework" data-toggle="modal" data-target=".bd-assign-modal-lg" data-sheet-name="<?php echo $sheet->sheet_name ?>" data-sheet-id="<?php echo $sheet->id ?>"><img class="img-height-17" src="<?php echo get_template_directory_uri(); ?>/library/images/icon_assign.png"></a><a data-toggle="modal" data-target=".bd-preview-modal-lg" style="margin-left: 10px;"><img class="img-height-17"  src="<?php echo get_template_directory_uri(); ?>/library/images/icon_preview2.png"></a></td>
                                                                                        </tr>
        <?php
    endforeach;
endif
?>
                                                                            </tbody>
                                                                        </table>
                                                                    </div>                                                                
                                                            </div>
                                                            <div class="clearfix" style="margin: 0 0 20px;">
                                                                <div class="col-xs-3 col-sm-5 col-md-5 ac-class">
                                                                </div>
                                                                <div class="col-xs-9 col-sm-7 col-md-7 ac-class">
                                                                    <div class="deactivated-class col-xs-5 col-sm-4 col-md-4 col-xs-offset-3 col-sm-offset-5 col-md-offset-5" style="right: -3%; text-align: right;">
                                                                        <img class="img-height-20" src="<?php echo get_template_directory_uri(); ?>/library/images/icon_assign_gray.png"><span>Assign</span>
                                                                    </div>
                                                                    <div class="deactivated-class col-xs-4 col-sm-3 col-md-3" style="right: 0; text-align: right; padding-right: 0 !important">
                                                                        <img class="img-height-20" src="<?php echo get_template_directory_uri(); ?>/library/images/icon_preview2_gray.png" style="width: 20px;"><span>Preview</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="tab-pane" id="math-tab">
                                                    <div class="col-sm-9 col-md-9">
                                                        <div class="form-group">
                                                            <input type="text" id="filte-sheet-name" name="worksheet-name" class="form-control" value="" placeholder="Worksheet Name">
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-3 col-md-3 button-mobile">
                                                        <div class="form-group">
                                                            <button type="submit" id="btn-search-math" name="submit" class="create-a-class-btn">Search</button>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-12 col-md-12">
                                                        <div class="form-group">
                                                            <div class="row tiny-gutter" style="margin-top: 15px;">
                                                                <div class="col-xs-12 col-sm-4 col-md-4">
                                                                    <?php
                                                                    MWHtml::sel_homework_types($filter['homework-types'], array('first_option' => __('Homework Type', 'iii-dictionary'),
                                                                        'name' => 'filter[homework-types]', 'class' => 'form-control',
                                                                        'id' => 'filter-homework-types-math', 'subscribed_option' => true,
                                                                        'admin_panel' => true)
                                                                    )
                                                                    ?>
                                                                </div>                                                    
                                                                <div class="col-xs-12 col-sm-4 col-md-4">
                                                                   <select class="select-box-it form-control" name="level-category" id="sel-level-categories">
                                                                        <option value="">Level Category</option>
<?php foreach ($main_categories as $item) : ?>
                                                                            <option value="<?php echo $item->id ?>"<?php echo $filter['cat-level'] == $item->id ? ' selected' : '' ?>><?php echo $item->name ?></option>
                                                                    <?php endforeach ?>
                                                                    </select>
                                                                </div>
                                                                <div class="col-xs-12 col-sm-4 col-md-4">
<?php MWHtml::sel_math_assignments($filter['assignment-id'], array('first-option' => 'Worksheet Format', 'name' => 'filter[math-assignments]', 'id' => 'math-assignments')) ?>
                                                                </div>

                                                            </div>
                                                            <div class="row tiny-gutter" style="margin-top: 20px">
                                                                <div class="col-xs-12 col-sm-4 col-md-4">
                                                                    <select class="select-box-it form-control" name="level" id="sel-levels">
                                                                        <option value="">Level</option>
                                                                    </select>
                                                                    <?php echo $sel_levels_html ?>
                                                                </div>
                                                                <div class="col-xs-12 col-sm-4 col-md-4">
                                                                    <select class="select-box-it form-control" id="sel-sublevels" name="sublevel">
                                                                        <option value="">Sublevel</option>
                                                                    </select>
                                                                    <?php echo $sel_sublevels_html ?>
                                                                </div>
                                                                <div class="col-xs-12 col-sm-4 col-md-4">
<?php MWHtml::select_languages($filter['lang'], array('first_option' => 'Language', 'name' => 'filter[lang]', 'class' => 'form-control','id' => 'filter-lang-math')) ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="title-border"></div>
                                                        <div class="row">
                                                            <div class="col-md-12" style="overflow-y: auto;height: 500px;">


                                                                    <div id="table-manage-your-classes" class="table-responsive all-table-create-class">
                                                                        <table class="table table-striped table-sm">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th>Level Category</th>
                                                                                    <th>Level</th>
                                                                                    <th width="25%">Level Category</th>
                                                                                    <th>Type</th>
                                                                                    <th>Sheet Name</th>
                                                                                    <th></th>
                                                                                </tr>
                                                                            </thead>
                                                                    
                                                                            <tbody id="result-math-search"><?php if (empty($avail_sheets)) : ?>
                                                                                    <tr>
                                                                                        <td colspan="5">No results</td>
                                                                                        <td class="op-new-assign" style="background: #dfdfdf; height: auto;"><a><img class="img-height-20" src="<?php echo get_template_directory_uri(); ?>/library/images/icon_assign_gray.png"></a><a style="margin-left: 10px;"><img class="img-height-24"  src="<?php echo get_template_directory_uri(); ?>/library/images/icon_preview2_gray.png"></a></td>
                                                                                    </tr>
                                                                                    <tr><td colspan="6">&nbsp&nbsp</td></tr>
                                                                                    <tr><td colspan="6">&nbsp&nbsp</td></tr>
                                                                                    <tr><td colspan="6">&nbsp&nbsp</td></tr>
                                                                                    <tr><td colspan="6">&nbsp&nbsp</td></tr>
                                                                                    <tr><td colspan="6">&nbsp&nbsp</td></tr>
                                                                                    <tr><td colspan="6">&nbsp&nbsp</td></tr>
                                                                                    <tr><td colspan="6">&nbsp&nbsp</td></tr>
                                                                                    <tr><td colspan="6">&nbsp&nbsp</td></tr>
                                                                                    <tr><td colspan="6">&nbsp&nbsp</td></tr>
                                                                                    <tr><td colspan="6">&nbsp&nbsp</td></tr>
                                                                                    <tr><td colspan="6">&nbsp&nbsp</td></tr>
<?php else : foreach ($avail_sheets as $sheet) : ?>
                                                                                        <tr<?php echo $sheet->active ? '' : ' class="text-muted"' ?> data-id="<?php echo $sheet->id ?>" data-assignment="<?php echo $sheet->assignment_id ?>">
                                                                                            <td><?php echo $sheet->level_category_name ?></td>
                                                                                            <td><?php echo $sheet->level_name ?></td>
                                                                                            <td><?php echo $sheet->sublevel_name ?></td>
                                                                                            <td><?php echo $sheet->type ?></td>
                                                                                            <td><?php echo $sheet->sheet_name ?></td>
                                                                                            <td class="op-new-assign"><a class="view-assign-homework" data-toggle="modal" data-target=".bd-assign-modal-lg" data-sheet-name="<?php echo $sheet->sheet_name ?>" data-sheet-id="<?php echo $sheet->id ?>"><img class="img-height-20" src="<?php echo get_template_directory_uri(); ?>/library/images/icon_assign.png"></a><a data-toggle="modal" data-target=".bd-preview-modal-lg" style="margin-left: 10px;"><img class="img-height-24"  src="<?php echo get_template_directory_uri(); ?>/library/images/icon_preview2.png"></a></td>
                                                                                        </tr>
        <?php
    endforeach;
endif
?>
                                                                            </tbody>
                                                                        </table>
                                                                    </div>                                                                
                                                            </div>
                                                            <div class="clearfix" style="margin: 0 0 20px;">
                                                                <div class="col-xs-3 col-sm-5 col-md-5 ac-class">
                                                                </div>
                                                                <div class="col-xs-9 col-sm-7 col-md-7 ac-class">
                                                                    <div class="deactivated-class col-xs-5 col-sm-4 col-md-4 col-xs-offset-3 col-sm-offset-5 col-md-offset-5" style="right: -3%; text-align: right;">
                                                                        <img class="img-height-20" src="<?php echo get_template_directory_uri(); ?>/library/images/icon_assign_gray.png"><span>Assign</span>
                                                                    </div>
                                                                    <div class="deactivated-class col-xs-4 col-sm-3 col-md-3" style="right: 0; text-align: right; padding-right: 0 !important">
                                                                        <img class="img-height-20" src="<?php echo get_template_directory_uri(); ?>/library/images/icon_preview2_gray.png" style="width: 20px;"><span>Preview</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>

<form method="post" action="<?php echo locale_home_url() . '/?r=teachers-box' ?><?php echo $gid ? '&amp;gid=' . $gid : '' ?>" id="main-form">
    <div class="modal fade bd-assign-modal-lg modal-classes-created" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" style="background:#fff;">
        <div class="modal-dialog modal-lg modal-dialog-classes-created modal-dialog-assignment-created">
            <div class="modal-content">
                <header class="col-xs-12 article-header">
                    <div class="row">
                        <div class="container">
                            <div class="page-title-responsive">
                                <h1 class="page-title text-uppercase pull-left" itemprop="headline" style="padding-top: 8px;">Assign homework</h1>
                                <img class="icon-close-assign pull-right" data-dismiss="modal" style="cursor: pointer;" src="<?php echo get_template_directory_uri(); ?>/library/images/close_blue.png">
                            </div>
                        </div>
                    </div>
                </header>
                <div class="container">
                    <!-- add div container only content -->
                    <section class="col-xs-12 entry-content">
                        <div class="row">
                            <div class="content-page-responsive">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group form-div-border">
                                            <span>
                                                <span class="class-name-grey">Worksheet Name: </span>
                                                <strong id="assign-homework-worksheet-name"></strong>
                                                <input type="hidden" name="sheet_id" value="" id="input-assign-homework-worksheet-id">
                                                <input type="hidden" name="group_id" value="" id="input-assign-homework-group-id">
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <p class="Name-pass-p label-title-create-class">Homework Name</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-5 col-md-5">
                                                <div class="form-group">
                                                    <label for="">Enter the Homework Name</label>
                                                    <input type="text" class="form-control" value="" name="homework-name" required="">
                                                </div>
                                            </div>
                                            <div class="col-sm-5 col-md-5">
                                                <div class="form-group">
                                                    <label for="">Deadline</label>
                                                    <input type="text" class="form-control" value="" name="deadline" placeholder="No deadline">
                                                </div>
                                            </div>
                                            <div class="col-xs-2 col-sm-2 col-md-2 button-mobile">
                                                <div class="form-group">
                                                    <button type="submit" name="submit" class="create-a-class-update-homework-btn">Reset</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form__group radio_buttons required application_disbursment_type_code title-border" aria-required="true">
                                            <label class="label-title-create-class" aria-required="true">Homework Mode</label>
                                            <div class="form__boolean">
                                                <span class="radio pad-left-0 col-md-3 col-xs-6">
                                                    <input role="moneychose" class="radio_buttons required pretty_radio" type="radio" value="1" name="for-practice" id="application_homework_mode_2_1" aria-required="true" required="">
                                                    <label class="collection_radio_buttons" for="application_homework_mode_2_1">Practice</label>
                                                </span>
                                                <span class="radio col-md-1 col-xs-6" style="margin-top: 10px;">
                                                    <input role="moneychose" class="radio_buttons required pretty_radio" type="radio" value="0" name="for-practice" id="application_homework_mode_2_2" aria-required="true">
                                                    <label class="collection_radio_buttons label-create" for="application_homework_mode_2_2">Test</label>
                                                </span>
                                                <div class="col-md-3" style="margin-top: 15px;margin-left: 20px;">
                                                    <img class="cb_uh_display_last_page pull-left img-height-22" src="<?php echo get_template_directory_uri(); ?>/library/images/check_box_checked.png">
                                                    <p class="pull-left" style="margin-left: 10px;color: #909090;">Display last page?</p>
                                                    <input type="hidden" name="checkboxpageadminmodal" value="0">
                                                    <input type="hidden" name="checkboxpagemodal" value="1">
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="tooltip tooltip-manage-a-classroom tooltip-uh-1 col-xs-12 col-sm-12">
                                                        <img style="margin-top: 2px; margin-left: -50px; height: 22px;" src="<?php echo get_template_directory_uri(); ?>/library/images/Question_Icon.png">
                                                        <div class="tooltip-div" style="left: -23%;">
                                                            <div class="tp">
                                                                <p>Some worksheet has multiple pages with answer showing at the end of the page. Mark on this will make the worksheet to show the last page(answer).</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form__group radio_buttons required application_disbursment_type_code title-border" aria-required="true">
                                            <label class="label-title-create-class" aria-required="true">Homework Retryable?</label>
                                            <div class="form__boolean">
                                                <span class="radio pad-left-0 col-md-3">
                                                    <input role="moneychose" class="radio_buttons required pretty_radio" type="radio" value="1" name="is-retryable" id="application_homework_retry_2_1" aria-required="true" required="">
                                                    <label class="collection_radio_buttons" for="application_homework_retry_2_1">Yes</label>
                                                </span>
                                                <span class="radio col-md-3" style="margin-top: 10px;">
                                                    <input role="moneychose" class="radio_buttons required pretty_radio" type="radio" value="0" name="is-retryable" id="application_homework_retry_2_2" aria-required="true">
                                                    <label class="collection_radio_buttons label-create" for="application_homework_retry_2_2">No</label>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-12" style="padding-top: 20px;">
                                        <div class="col-xs-6 col-sm-6 col-md-6 pad-left-0"><!-- 
                                         <button class="create-a-class-btn">Update</button> -->
                                            <button class="btn-dark-blue" type="submit" name="assign"><?php _e('Assign', 'iii-dictionary') ?></button>
                                        </div>
                                        <div class="col-xs-6 col-sm-6 col-md-6 pad-right-0">
                                            <a type="submit" data-dismiss="modal" class="button-grey" style="margin: 0;"><?php esc_attr_e('Cancel', 'iii-dictionary') ?>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
</form>

<div class="modal fade bd-preview-modal-lg modal-classes-created" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" style="background:#052206;">
    <div class="modal-dialog modal-lg modal-dialog-classes-created modal-dialog-assignment-created">
        <div class="modal-content" style="background: #052206;">
            <div class="container-fluid text-center" style="overflow: hidden; margin: 0 2% 2% 2%;">    
                <div class="row content">
                    <div class="col-sm-10 text-left" style="padding-right: 0px !important; border-right: 3px solid #000;"> 
                        <h4 style="color: #95a5a6;margin-top: 40px;"><i>Algebra I</i></h4>
                        <header class="col-xs-12 article-header" style="background: #052206; padding-top: 0;">
                            <div class="row">
                                <div class="page-title-responsive" style="width: 100%;">
                                    <h1 class="page-title text-uppercase pull-left" itemprop="headline" style="padding-top: 8px; color: #4EE5FF;"><strong>Functions and Variables, Even Function</strong></h1>
                                    <img class="icon-close-preview pull-right" data-dismiss="modal" style="cursor: pointer;" src="<?php echo get_template_directory_uri(); ?>/library/images/close_blue.png">
                                </div>
                            </div>
                        </header>

                        <p style="color: #95a5a6;"><i>Function Type, Even Function</i></p>
                        <img src="<?php echo get_template_directory_uri(); ?>/library/images/03_Preview_Sample.png" style="width: 100%; height: auto;">
                        <div class="col-sm-12" style="background: #2F2D1E;height: 65px;padding:0!important;">
                          <!-- <div class="col-sm-2"><h4 class="text-uppercase" style="color: #fff; margin-top: 25px;"><strong>Type Answer Here</strong></h4></div>
                          <div class="col-sm-8"></div>
                          <div class="col-sm-2"><button type="button" class="create-a-class-btn" style="margin-top: 15px;"><strong>Next</strong></button></div> -->
                            <input type="text" class="form-control preview-answer-input" id="" name="" value='' placeholder="Type Answer Here"/>
                            <button type="button" class="create-a-class-btn" style="top: 12px;height: 40px;width: 200px;right: 10px;position: absolute;"><strong>Next</strong></button>
                        </div>
                    </div>
                    <div class="col-sm-2 sidenav">
                        <div class="row" style="margin-top: 82px; height: 32px;">
                            <div class="col-sm-6">
                                <div class="sound">
                                    <div class="sound--icon"><a href="javascript:void(0)"><img src="<?php echo get_template_directory_uri(); ?>/library/images/sound-volume.png" style="width: 35px;height: auto;margin-bottom: 100px;margin-left: -15px;"></a></div>
                                    <div class="sound--wave sound--wave_one"></div>
                                    <div class="sound--wave sound--wave_two"></div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="onoffswitch">
                                    <input type="checkbox" name="onoffswitch" class="onoffswitch-checkbox" id="myonoffswitch" checked>
                                    <label class="onoffswitch-label" for="myonoffswitch">
                                        <span class="onoffswitch-inner"></span>
                                        <span class="onoffswitch-switch"></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div style="padding-bottom: 5000px; margin-bottom: -5000px; background: #29433A;">
                                <h3 style="margin: 20px 100px -40px 0; height: 80px; color: #569584;"><strong>Steps: </strong></h3>
                                <br>
                                <hr style="width: 95%;border-color: #7f8c8d;">
                                <button type="button" class="preview-step-btn"><strong>1</strong></button>
                                <button type="button" class="preview-step-btn"><strong>2</strong></button>

                                <button type="button" class="preview-sup-btn" style="margin-top: 400px"><img src="<?php echo get_template_directory_uri(); ?>/library/images/notepad-512.png"><strong>Notepad</strong></button>
                                <hr style="width: 95%;border-color: #7f8c8d;">
                                <button type="button" class="preview-sup-btn"><img src="<?php echo get_template_directory_uri(); ?>/library/images/chat-icon.png"><strong>Tutoring</strong></button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php get_math_footer() ?>
<link href="/wp-content/themes/ik-learn/library/bootstrap-datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet" >
<script src="/wp-content/themes/ik-learn/library/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
<script>
    (function ($) {
        $(function () {

            var group_price = $('#group_price').parents('div.class-group-block');
            var select_box = $('#class-types');

            if (select_box.val() <= 20) {
                group_price.slideUp();
            }

            if ($("#group-types").val() == "1") {
                $(".class-group-block").hide();
            } else {
                $(".class-free-block").hide();
            }

            $("#group-types").change(function () {
                if ($(this).val() == "1") {
                    $(".class-free-block").slideDown();
                    $(".class-group-block").slideUp();
                } else {
                    $(".class-free-block").hide();
                    $(".class-group-block").slideDown();
                    if (select_box.find('option:selected').val() <= 20) {
                        group_price.hide();
                    }
                }
            });

            select_box.change(function () {
                if ($(this).val() > 20) {
                    group_price.slideDown();
                } else {
                    group_price.slideUp();
                    $('#group_price').val('');
                }                
            });

            $("#sel-level-categories").on("option-click", function(){
                var _c = $(this).val();
                var $_l = $("#sel-levels");
                var $_sl = $("#sel-sublevels");
                $_l.find("option:not(:first-child)").remove();
                $_l.html("").data("selectBox-selectBoxIt").refresh();
                $_sl.find("option:not(:first-child)").remove();
                $_sl.html("").data("selectBox-selectBoxIt").refresh();
                if(_c != ""){
                    $_l.append($("#_l" + _c).html()).data("selectBox-selectBoxIt").refresh().selectOption($_l.attr("data-selected"));
                    $_sl.append($("#_sl" + $_l.val()).html()).data("selectBox-selectBoxIt").refresh().selectOption($_sl.attr("data-selected"));
                }
            });

            $("#sel-levels").on("option-click", function(){
                var _l = $(this).val();
                var $_sl = $("#sel-sublevels");
                $_sl.find("option:not(:first-child)").remove();
                $_sl.append($("#_sl" + _l).html()).data("selectBox-selectBoxIt").refresh();
            });

            $("#sel-level-categories").trigger("option-click");

            $('#btn-search-math').click(function (e) {
                var math_assignments = $('#math-assignmentsSelectBoxItText').attr('data-val');
                var level_categories = $('#sel-level-categoriesSelectBoxItText').attr('data-val');
                var homework_types = $('#filter-homework-types-mathSelectBoxItText').attr('data-val');
                var levels = $('#sel-levelsSelectBoxItText').attr('data-val');
                var sublevels = $('#sel-sublevelsSelectBoxItText').attr('data-val');
                var lang = $('#filter-lang-mathSelectBoxItText').attr('data-val');
                var sheet_name = $('#filte-sheet-name').val();

                $.get(home_url + "/?r=ajax/get_math_search", {math_assignments: math_assignments, level_categories: level_categories, homework_types: homework_types, levels: levels, sublevels: sublevels, lang: lang, sheet_name: sheet_name}, function (data) {
                    $('#result-math-search').html(data);
                    $(".view-assign-homework").click(function () {
                        var tthis = $(this);
                        var sheet_name = tthis.attr("data-sheet-name");
                        var sheet_id = tthis.attr("data-sheet-id");
                        $('#assign-homework-worksheet-name').html(sheet_name);
                        $('#input-assign-homework-worksheet-id').val(sheet_id);
                    });
                });
                //alert(math_assignments);
            });

            $('#manage_classroom').click(function (e) {
                $('#manage-classroom-modal').addClass('in');
                $('#manage-classroom-modal').modal("show");
            });
            $('.subject').click(function (e) {
                $('#tab-prv1').hide();
                $('#detail-subject').show();
            });            
            $('.del-subject').click(function (e) {
                $('#modal-question').modal('show');
            });
            $('.subject-posting').click(function (e) {
                $('#tab-post1').hide();
                $('#detail-postings').show();
            });
            $('.del-subject-posting').click(function (e) {
                var mid = $(this).attr('data-id');
                $('#btn-ok-cancel-posting').attr('data-id',mid);
                $('#modal-question-posting').modal('show');
            });
            $('.btn-send').click(function () {
                var student = $('#select-teacher').val();
                var text_subject = $('#text_subject').val();                
                var id = $('#get_id').val();
                var content =  $.trim(tinymce.get('content_message').getContent());
                if(text_subject != ''){
                    if( $('#select-class').change()){
                        var sl= $('#select-class').val();
                        $.post(home_url + "/?r=ajax/save_message", {
                            id: sl,
                            recipient_id: student,
                            subject: text_subject,
                            message: content
                        }, function (data) {
//                            if ($.trim(data) == 'ok') $('#modal-send-message').modal('show');
                            if ($.trim(data) == '1') {
                                $('#modal-send-message').modal('show');
                                $('#new_message').removeClass('active');
                                $('#tab-prv3').hide();
                                document.getElementById('sent').className += " active";
                                document.getElementById('tab-prv2').style.display = "block";
                                $('#text_subject').val('');
                                tinyMCE.activeEditor.setContent('');
                            }
                        });
                    }
                    else {
                        $.post(home_url + "/?r=ajax/save_message", {
                            id: id,
                            recipient_id: student,
                            subject: text_subject,
                            message: content
                        }, function (data) {
                            if ($.trim(data) == '1'){
                                $('#modal-send-message').modal('show');
                                $('#text_subject').val('');
                                tinyMCE.activeEditor.setContent('');
                            }
                        });
                    }
                }
            });

            $('.btn-post').click(function () {
                var text_subject = $('#post_subject').val();                
                var id = $('#get_id').val();
                var content =  $.trim(tinymce.get('post_message').getContent());
                if(text_subject != ''){
                    $.post(home_url + "/?r=ajax/write_post_message", {
                        id: id,                        
                        subject: text_subject,
                        message: content
                    }, function (data) {
                        if ($.trim(data) == '1'){
                            $('#modal-send-message-posting').modal('show');
                            $('#post_subject').val('');
                            tinyMCE.activeEditor.setContent('');

                            $.get(home_url + "/?r=ajax/get_class_postings", {gid: id}, function (data) {
                                $('#table-postings-class').html(data);
                                $('.del-subject-posting').click(function (e) {
                                    var mid = $(this).attr('data-id');
                                    $('#btn-ok-cancel-posting').attr('data-id',mid);
                                    $('#modal-question-posting').modal('show');
                                });
                               $('.subject-posting').click(function (e) {
                                    var tthis = $(this);                            
                                    document.getElementById('dpt-subject').innerHTML=tthis.attr('data-subject');
                                    document.getElementById('send-by').innerHTML=tthis.attr('data-student');
                                    document.getElementById('dptDate').innerHTML=tthis.attr('data-date');
                                    document.getElementById('dptmessage').innerHTML=tthis.attr('data-message');

                                    $('#tab-post1').hide();
                                    $('#detail-postings').show();
                                });
                            });
                            var postings = document.getElementById("postings");
                            $('#write_post').removeClass('active');
                            $('#tab-post2').hide();                            
                            $('#tab-post1').show();
                            postings.className += " active";
                        }
                    });
                }
            });

            $('.btn-post-student').click(function () {
                var text_subject = $('#post_subject-student').val();                
                var id = $('#get_id').val();
                var content =  $.trim(tinymce.get('post_message-student').getContent());
                if(text_subject != ''){
                    $.post(home_url + "/?r=ajax/write_post_message", {
                        id: id,                        
                        subject: text_subject,
                        message: content
                    }, function (data) {
                        if ($.trim(data) == '1'){
                            $('#modal-send-message-posting').modal('show');
                            $('#post_subject-student').val('');
                            tinyMCE.activeEditor.setContent('');

                            $.get(home_url + "/?r=ajax/get_class_postings", {gid: id}, function (data) {
                                $('#table-postings-class').html(data);
                                $('.del-subject-posting').click(function (e) {
                                    var mid = $(this).attr('data-id');
                                    $('#btn-ok-cancel-posting').attr('data-id',mid);
                                    $('#modal-question-posting').modal('show');
                                });
                               $('.subject-posting').click(function (e) {
                                    var tthis = $(this);                            
                                    document.getElementById('dpt-subject').innerHTML=tthis.attr('data-subject');
                                    document.getElementById('send-by').innerHTML=tthis.attr('data-student');
                                    document.getElementById('dptDate').innerHTML=tthis.attr('data-date');
                                    document.getElementById('dptmessage').innerHTML=tthis.attr('data-message');

                                    $('#tab-post1').hide();
                                    $('#detail-postings').show();
                                });
                            });
                            var postings = document.getElementById("postings");
                            $('#write_post').removeClass('active');
                            $('#tab-post2').hide();                            
                            $('#tab-post1').show();
                            postings.className += " active";
                        }
                    });
                }
            });

            $('#btn-no').click(function () {
                $('#modal-question').modal('hide');
            });
            $('#btn-no-posting').click(function () {
                $('#modal-question-posting').modal('hide');
            });
            $('#btn-ok-cancel').click(function () {
                var mid = $(this).attr('data-id');
                var mtype = $(this).attr('data-type');
                var id = $('#get_id').val();
                $.get(home_url + "/?r=ajax/delete_message", {id: mid,type:mtype}, function () {
                    if(mtype == 'sent'){
                        $.get(home_url + "/?r=ajax/get_message_sent", {gid: id}, function (data) {
                            $('#table-message-sent').html(data);
                            $('.del-subject').click(function (e) {
                                var mid = $(this).attr('data-id');
                                var type = $(this).attr('data-type');
                                $('#btn-ok-cancel').attr('data-id',mid);
                                $('#btn-ok-cancel').attr('data-type',type);
                                $('#modal-question').modal('show');
                            });

                            $('.subject').click(function (e) {
                                $('#tab-prv2').hide();
                                $('#detail-subject').show();
                            });
                        });
                    }else{
                         $.get(home_url + "/?r=ajax/get_message_receive", {gid: id}, function (data) {
                            $('#table-message-receive').html(data);
                            $('.del-subject').click(function (e) {
                                var mid = $(this).attr('data-id');
                                var type = $(this).attr('data-type');
                                $('#btn-ok-cancel').attr('data-id',mid);
                                $('#btn-ok-cancel').attr('data-type',type);
                                $('#modal-question').modal('show');
                            });

                            $('.subject').click(function (e) {
                                $('#tab-prv1').hide();
                                $('#detail-subject').show();
                            });
                        });
                    }
                    $('#modal-question').modal('hide');
                });
            });
            $('#received').click(function () {
                $('#detail-subject').hide();
                $('#detail-subject-sent').hide();
                var id = $('#get_id').val();
                $.get(home_url + "/?r=ajax/get_message_receive", {gid: id}, function (data) {
                    $('#table-message-receive').html(data);
                    $('.del-subject').click(function (e) {
                        var mid = $(this).attr('data-id');
                        var type = $(this).attr('data-type');
                        $('#btn-ok-cancel').attr('data-id',mid);
                        $('#btn-ok-cancel').attr('data-type',type);
                        $('#modal-question').modal('show');
                    });

                    $('.subject').click(function (e) {

                        $('#tab-prv1').hide();
                        $('#detail-subject').show();
                    });
                });
            });
            $('#btn-ok-cancel-posting').click(function () {
                var mid = $(this).attr('data-id');
                var id = $('#get_id').val();
                $.post(home_url + "/?r=ajax/delete_post_message", {id: mid}, function (result) {
                    if ($.trim(result) == 'ok'){
                        $('#table-postings-class').html('');
                        $.get(home_url + "/?r=ajax/get_class_postings", {gid: id}, function (data) {
                            $('#table-postings-class').html(data);
                            $('.del-subject-posting').click(function (e) {
                                var mid = $(this).attr('data-id');
                                $('#btn-ok-cancel-posting').attr('data-id',mid);
                                $('#modal-question-posting').modal('show');
                            });

                            $('.subject-posting').click(function (e) {
                                $('#tab-post1').hide();
                                $('#detail-postings').show();
                            });
                        });
                        $('#modal-question-posting').modal('hide');
                    }
                });
            });
            $('#postings').click(function () {
                $('#detail-postings').hide();
                $('#write-student-message').hide();
                var id = $('#get_id').val();
                $.get(home_url + "/?r=ajax/get_class_postings", {gid: id}, function (data) {
                    $('#table-postings-class').html(data);
                    $('.del-subject-posting').click(function (e) {
                        var mid = $(this).attr('data-id');
                        $('#btn-ok-cancel-posting').attr('data-id',mid);
                        $('#modal-question-posting').modal('show');
                    });

                    $('.subject-posting').click(function (e) {
                        var tthis = $(this);                            
                        document.getElementById('dpt-subject').innerHTML=tthis.attr('data-subject');
                        document.getElementById('send-by').innerHTML=tthis.attr('data-student');
                        document.getElementById('dptDate').innerHTML=tthis.attr('data-date');
                        document.getElementById('dptmessage').innerHTML=tthis.attr('data-message');

                        $('#tab-post1').hide();
                        $('#detail-postings').show();
                    });
                });
            });
            $('#write_post').click(function () {
                $('#detail-postings').hide();
                $('#write-student-message').hide();
            });
            $('#student_plist').click(function () {
                $('#detail-postings').hide();
                $('#write-student-message').hide();
                var id = $('#get_id').val();
                $.get(home_url + "/?r=ajax/get_group_students_list", {gid: id}, function (data) {
                    $('#table-student-list').html(data);
                    
                    $('.student-post').click(function (e) {
                        $('#tab-post3').hide();
                        $('#write-student-message').show();
                    });
                });
            });
            $("#modal-message-center").on("show.bs.modal",function(){
                var id = $('#get_id').val();
                $('#text_subject').val('');
                $('#post_subject').val('');

                document.getElementById('class-postings').className += " active";
                document.getElementById('postings').className += " active";
                document.getElementById('Paris').style.display = "block";
                document.getElementById('tab-post1').style.display = "block";
                document.getElementById('header-tab').style.display = "block";
                document.getElementById('header-email').style.display = "none";

                $.get(home_url + "/?r=ajax/get_class_postings", {gid: id}, function (data) {
                    $('#table-postings-class').html(data);
                    $('.del-subject-posting').click(function (e) {
                        var mid = $(this).attr('data-id');
                        $('#btn-ok-cancel-posting').attr('data-id',mid);
                        $('#modal-question-posting').modal('show');
                    });
                   $('.subject-posting').click(function (e) {
                        var tthis = $(this);                            
                        document.getElementById('dpt-subject').innerHTML=tthis.attr('data-subject');
                        document.getElementById('send-by').innerHTML=tthis.attr('data-student');
                        document.getElementById('dptDate').innerHTML=tthis.attr('data-date');
                        document.getElementById('dptmessage').innerHTML=tthis.attr('data-message');

                        $('#tab-post1').hide();
                        $('#detail-postings').show();
                    });
                });

                $.get(home_url + "/?r=ajax/get_group_students_list", {gid: id}, function (data) {
                    $('#table-student-list').html(data);
                    
                    $('.student-post').click(function (e) {
                        $('#tab-post3').hide();
                        $('#write-student-message').show();
                    });
                });

                $.get(home_url + "/?r=ajax/get_message_receive", {gid: id}, function (data) {
                    $('#table-message-receive').html(data);
                    $('.del-subject').click(function (e) {
                        var mid = $(this).attr('data-id');
                        var type = $(this).attr('data-type');
                        $('#btn-ok-cancel').attr('data-id',mid);
                        $('#btn-ok-cancel').attr('data-type',type);
                        $('#modal-question').modal('show');
                    });

                    $('.subject').click(function (e) {
                        var tthis = $(this);
                        var new_message = document.getElementById("new_message");
                        document.getElementById('tit-subject').innerHTML=tthis.attr('data-subject');
                        document.getElementById('send-by').innerHTML=tthis.attr('data-sender');
                        document.getElementById('receivedDate').innerHTML=tthis.attr('data-receiveddate');
                        document.getElementById('message').innerHTML=tthis.attr('data-message');

                        $('#tab-prv1').hide();
                        $('#detail-subject').show();

                        $('#btn-reply').click(function (e) {
                            $('#received').removeClass('active');
                            $('#detail-subject').hide();
                            new_message.className += " active";
                            document.getElementById('tab-prv3').style.display = "block";
                        });
                    });
                });

                $.get(home_url + "/?r=ajax/get_message_sent", {gid: id}, function (data) {
                    $('#table-message-sent').html(data);
                    $('.del-subject').click(function (e) {
                        var mid = $(this).attr('data-id');
                        var type = $(this).attr('data-type');
                        $('#btn-ok-cancel').attr('data-id', mid);
                        $('#btn-ok-cancel').attr('data-type', type);
                        $('#modal-question').modal('show');
                    });
                });

                $.get(home_url + "/?r=ajax/get_option_student_list", {gid: id}, function (data) {
                    //$('#select-student-list').html(data);
                    $('#select-student-list').html(data).data("selectBox-selectBoxIt").refresh();
                });

                $("#select-class").selectBoxIt().change(function() {
                    var sl = this.value;
                    $('#get_id').val(sl);
                    $('#table-postings-class').html('');
                    $('#table-student-list').html('');
                    $('#table-message-sent').html('');
                    $('#table-message-receive').html('');

                    $('#detail-postings').hide();
                    $('#write-student-message').hide();

                    $.get(home_url + "/?r=ajax/get_class_postings", {gid: sl}, function (data) {
                        $('#table-postings-class').html(data);
                        $('.del-subject-posting').click(function (e) {
                            var mid = $(this).attr('data-id');
                            $('#btn-ok-cancel-posting').attr('data-id',mid);
                            $('#modal-question-posting').modal('show');
                        });
                        $('.subject-posting').click(function (e) {
                            var tthis = $(this);                            
                            document.getElementById('dpt-subject').innerHTML=tthis.attr('data-subject');
                            document.getElementById('send-by').innerHTML=tthis.attr('data-student');
                            document.getElementById('dptDate').innerHTML=tthis.attr('data-date');
                            document.getElementById('dptmessage').innerHTML=tthis.attr('data-message');

                            $('#tab-post1').hide();
                            $('#detail-postings').show();
                        });
                    });

                    $.get(home_url + "/?r=ajax/get_group_students_list", {gid: sl}, function (data) {
                        $('#table-student-list').html(data);
                        
                        $('.student-post').click(function (e) {
                            $('#tab-post3').hide();
                            $('#write-student-message').show();
                        });
                    });

                    $.get(home_url + "/?r=ajax/get_message_receive", {gid: sl}, function (data) {
                        $('#table-message-receive').html(data);
                        $('.del-subject').click(function (e) {
                            var mid = $(this).attr('data-id');
                            var type = $(this).attr('data-type');
                            $('#btn-ok-cancel').attr('data-id',mid);
                            $('#btn-ok-cancel').attr('data-type',type);
                            $('#modal-question').modal('show');
                        });
                        
                        $('.subject').click(function (e) {
                            var tthis = $(this);
                            var new_message = document.getElementById("new_message");
                            document.getElementById('tit-subject').innerHTML=tthis.attr('data-subject');
                            document.getElementById('send-by').innerHTML=tthis.attr('data-sender');
                            document.getElementById('receivedDate').innerHTML=tthis.attr('data-receiveddate');
                            document.getElementById('message').innerHTML=tthis.attr('data-message');

                            $('#tab-prv1').hide();
                            $('#detail-subject').show();

                            $('#btn-reply').click(function (e) {
                                $('#received').removeClass('active');
                                $('#detail-subject').hide();
                                new_message.className += " active";
                                document.getElementById('tab-prv3').style.display = "block";
                            });
                        });
                    });

                    $.get(home_url + "/?r=ajax/get_message_sent", {gid: sl}, function (data) {
                        $('#table-message-sent').html(data);
                        $('.del-subject').click(function (e) {
                            var mid = $(this).attr('data-id');
                            var type = $(this).attr('data-type');
                            $('#btn-ok-cancel').attr('data-id', mid);
                            $('#btn-ok-cancel').attr('data-type', type);
                            $('#modal-question').modal('show');
                        });
                    });

                    $.get(home_url + "/?r=ajax/get_option_student_list", {gid: sl}, function (data) {
                        //$('#select-student-list').html(data);
                        $('#select-student-list').html(data).data("selectBox-selectBoxIt").refresh();
                    });
                });

                /*                
                $.get(home_url + "/?r=ajax/get_message_receive", {gid: id}, function (data) {
                    $('#table-message-receive').html(data);
                    $('.del-subject').click(function (e) {
                        var mid = $(this).attr('data-id');
                        var type = $(this).attr('data-type');
                        $('#btn-ok-cancel').attr('data-id',mid);
                        $('#btn-ok-cancel').attr('data-type',type);
                        $('#modal-question').modal('show');
                    });

                    $('.subject').click(function (e) {
                        var tthis = $(this);
                        var new_message = document.getElementById("new_message");
                        document.getElementById('tit-subject').innerHTML=tthis.attr('data-subject');
                        document.getElementById('send-by').innerHTML=tthis.attr('data-sender');
                        document.getElementById('receivedDate').innerHTML=tthis.attr('data-receiveddate');
                        document.getElementById('message').innerHTML=tthis.attr('data-message');

                        $('#tab-prv1').hide();
                        $('#detail-subject').show();

                        $('#btn-reply').click(function (e) {
                            $('#received').removeClass('active');
                            $('#detail-subject').hide();
                            new_message.className += " active";
                            document.getElementById('tab-prv3').style.display = "block";


                        });

                    });

                    $('#select-class').change(function () {
                       var sl= $('#select-class').val();
                        $('#table-message-receive').html('');
                        $.get(home_url + "/?r=ajax/get_message_receive", {gid: sl}, function (data) {
                            $('#table-message-receive').html(data);
                            $('.del-subject').click(function (e) {
                                var mid = $(this).attr('data-id');
                                var type = $(this).attr('data-type');
                                $('#btn-ok-cancel').attr('data-id', mid);
                                $('#btn-ok-cancel').attr('data-type', type);
                                $('#modal-question').modal('show');
                            });
                        });
//                        document.getElementById('received').className+=" active";
//                        $('#new_message').removeClass('active');
//                        $('#sent').removeClass('active');
                        $('#table-message-sent').html('');
                        $.get(home_url + "/?r=ajax/get_message_sent", {gid: sl}, function (data) {

                            $('#table-message-sent').html(data);
                            $('.del-subject').click(function (e) {
                                var mid = $(this).attr('data-id');
                                var type = $(this).attr('data-type');
                                $('#btn-ok-cancel').attr('data-id', mid);
                                $('#btn-ok-cancel').attr('data-type', type);
                                $('#modal-question').modal('show');
                            });
                        });


                    });

                });
                */
            });

            $('#sent').click(function () {
                $('#detail-subject').hide();
                $('#detail-subject-sent').hide();
                $('#received').removeClass('active');
                $('#tab-prv2').show();
                var id = $('#get_id').val();
                $.get(home_url + "/?r=ajax/get_message_sent", {gid: id}, function (data) {

                    $('#table-message-sent').html(data);
                    $('.del-subject').click(function (e) {
                        var mid = $(this).attr('data-id');
                        var type = $(this).attr('data-type');
                        $('#btn-ok-cancel').attr('data-id',mid);
                        $('#btn-ok-cancel').attr('data-type',type);
                        $('#modal-question').modal('show');
                    });

                    $('.subject-sent').click(function (e) {
                        var tthis = $(this);
                        document.getElementById('tit-subject-sent').innerHTML=tthis.attr('data-subject');
                        document.getElementById('sender').innerHTML=tthis.attr('data-sender');
                        document.getElementById('sentDate').innerHTML=tthis.attr('data-sentdate');
                        document.getElementById('message-sent').innerHTML=tthis.attr('data-message');
                        $('#tab-prv2').hide();
                        $('#detail-subject-sent').show();

                    });
                });
            });

            $('#new_message').click(function () {
                $('#detail-subject').hide();
                $('#detail-subject-sent').hide();
            });
            
            $("#dealine").datepicker({
            });
            
            
        });
    })(jQuery);
</script>