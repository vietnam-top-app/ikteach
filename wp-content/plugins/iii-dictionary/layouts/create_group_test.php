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
        $data['about_class'] = esc_html($_REAL_POST['about_class']);
        $data['gpass'] = esc_html($_REAL_POST['password']);
        $data['change_students_no'] = esc_html($_REAL_POST['charge-students-no']);
        $data['change_students_yes'] = esc_html($_REAL_POST['charge-students-yes']);
        $data['price_per_student'] = esc_html($_REAL_POST['price-per-student']);

        //$data['number_of_students'] = esc_html($_REAL_POST['num-students']);
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
        if ($data['price'] > 5)
            $data['number_of_students'] = FLOOR($data['price'] / 5);
        else
            $data['number_of_students'] = 1;

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



// <!-- Ordering -->
$gid = empty($_GET['gid']) ? 0 : $_GET['gid'];
$hid = empty($_GET['hid']) ? 0 : $_GET['hid'];
$task = isset($_POST['task']) ? $_POST['task'] : '';

$is_admin = is_mw_super_admin() || is_mw_admin() ? true : false;
$current_user_id = get_current_user_id();

$is_math_panel = is_math_panel();
$_page_title = __('Creating a Class', 'iii-dictionary');

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
    $filter['orderby'] = 'g.id';
    $filter['order-dir'] = 'desc';
    $filter['offset'] = 0;
    $filter['items_per_page'] = 99999999;
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
?>
<?php get_math_header($_page_title, 'main1', 'red-brown') ?>

<?php get_dict_page_title($_page_title, '', '', array(), get_info_tab_cloud_url('Popup_info_17.jpg')) ?>

<style type="text/css">
    /*=============Create a class Page style=================*/
    #content{
        background: #fff;
    }
    header.article-header{
        border-bottom: 1px solid #e0e0e0;
    }
    .form__group {
        position: relative;
        margin-bottom: 25px;
    }
    .form__boolean {
        margin-top: 5px;
    }
    .form__boolean .radio {
        margin-right: 20px;
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
    .price-per-student {
        width: 120px;
        padding: 0px 20px;
        margin: 0px 10px;
        margin-left: -30px;
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
    .moneychose-option .tooltip-col .image-tooltip{
        top: -25px;
        left: 230px;
        position: absolute;

    }
    .title-border .form__boolean .tooltiptext::after {
        content: "";
        position: absolute;
        top: -20px;
        left: 248px;
        border-width: 10px;
        border-top: 10px solid transparent;
        border-right: 8px solid transparent;
        border-bottom: 10px solid #FFCD86;
        border-left: 8px solid transparent;
    }
    @media(min-width: 992px){
        .title-border .form__boolean .tooltiptext::after{
            left: 198px !important;
        }
    }
    @media(min-width: 768px){
        .title-border .form__boolean .tooltiptext::after{
            left: 198px !important;
        }
    }
    @media(min-width: 320px) and (max-width: 767px){
        .title-border .form__boolean .tooltiptext::after{
            left: 210px !important;
        }
    }
    .moneychose-option{
        position: absolute;
        width: 95%;
        cursor: default !important;
        padding-left: 5px !important;

    }
    /* TOOLTIP OF CREATE CLASS PAGE */
    .title-border .form__boolean .tooltiptext {
        visibility: hidden;
        background-color: black;
        color: #fff;
        text-align: center;
        padding: 5px 10px;
        box-sizing: border-box;
        position: absolute;
        z-index: 1;
        top: 9px;
        left:24px;
        color: #000000;
        background:#FFCD86;
        padding:20px !important;
        text-align: left;
        font-size: 14px;
    }
    .title-border .form__boolean .tooltip-col:hover .tooltiptext {
        visibility: visible;
    }
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
        width: 80px;

    }
    #main1 .entry-content{
        padding-bottom: 0px;
        min-height: 0px;
    }
    .op-col-assign {
        text-align: center;
        width: 60px;
        position: absolute !important;
        right: 0;
        /* height: 38px !important; */
        padding-top: 5px !important;
        height: 38px;
    }

    .assignment-table-content-center{
        margin-left: 48px;
        width: 789px;
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
        width: 900px; margin: auto;
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
            margin-left: 48px;
            margin-right: 63px;
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
        .charge-to-student{
            margin-left: 7px;
        }
        .price-per-student{
            width: 50% !important;
            width: 120px;
            padding: 0px 20px;
            margin: 0px -24px;
        }
    }
    @media(max-width: 359px){
        .price-per-student{
            width: 50% !important;
            width: 120px;
            padding: 0px 20px;
            margin: 0px -21px;
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
    .price-per-student{
        border: 1px solid #c2c2c2;
    }
    @media(min-width: 421px){
        .charge-to-student{
            padding-left: 0px !important;
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
        position: absolute;
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
        margin-top: 5px;
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
        width: 20%;
        text-align: left;
        padding-left: 50px !important;
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
        height: 62px;
        width: 80px;
        position: absolute;
    }
    #create-group .entry-content{
        padding-bottom: 0;
    }

    /* END MODAL UPDATE SUBSCRIPTION */
    /* =============================================================================== */

</style>
<script type="text/javascript">
    (function ($) {
        $(function () {  
            $('.detail_price').click (function (){
                var id = $(this).attr('data-id');
                if(id == 'eng-self-study-price'){
                    if($('#'+id).hasClass('hidden')){
                        $('#'+id).removeClass('hidden');
                    }else{
                        $('#'+id).addClass('hidden');
                    }
                }else if(id == 'mw-dictionary-price'){
                    if($('#'+id).hasClass('hidden')){
                        $('#'+id).removeClass('hidden');
                    }else{
                        $('#'+id).addClass('hidden');
                    }
                }else if(id == 'sat-grammar-price'){
                    if($('#'+id).hasClass('hidden')){
                        $('#'+id).removeClass('hidden');
                    }else{
                        $('#'+id).addClass('hidden');
                    }
                }else if(id == 'sat-writing-price'){
                    if($('#'+id).hasClass('hidden')){
                        $('#'+id).removeClass('hidden');
                    }else{
                        $('#'+id).addClass('hidden');
                    }
                }else if(id == 'sat-test-price'){
                    if($('#'+id).hasClass('hidden')){
                        $('#'+id).removeClass('hidden');
                    }else{
                        $('#'+id).addClass('hidden');
                    }
                }else if(id == 'math-study-price'){
                    if($('#'+id).hasClass('hidden')){
                        $('#'+id).removeClass('hidden');
                    }else{
                        $('#'+id).addClass('hidden');
                    }
                }else if(id == 'ikmath-course-price'){
                    if($('#'+id).hasClass('hidden')){
                        $('#'+id).removeClass('hidden');
                    }else{
                        $('#'+id).addClass('hidden');
                    }
                }else if(id == 'sat-prepare-price'){
                    if($('#'+id).hasClass('hidden')){
                        $('#'+id).removeClass('hidden');
                    }else{
                        $('#'+id).addClass('hidden');
                    }
                }else if(id == 'new-sat-simulate-price'){
                    if($('#'+id).hasClass('hidden')){
                        $('#'+id).removeClass('hidden');
                    }else{
                        $('#'+id).addClass('hidden');
                    }
                }else if(id == 'sat-2-prepare-price'){
                    if($('#'+id).hasClass('hidden')){
                        $('#'+id).removeClass('hidden');
                    }else{
                        $('#'+id).addClass('hidden');
                    }
                }else if(id == 'sat-simulate-price'){
                    if($('#'+id).hasClass('hidden')){
                        $('#'+id).removeClass('hidden');
                    }else{
                        $('#'+id).addClass('hidden');
                    }
                }
            });
        });
    })(jQuery);

    function close_detail_price(id) {
        switch (id) {
            case 'eng-self-study-price':
            
                document.getElementById("eng-self-study-price").className += " hidden";
                break;
            
            case 'mw-dictionary-price':
            
                document.getElementById("mw-dictionary-price").className += " hidden";
                break;
            
            case 'sat-grammar-price':
            
                document.getElementById("sat-grammar-price").className += " hidden";
                break;
            
            case 'sat-writing-price':
            
                document.getElementById("sat-writing-price").className += " hidden";
                break;
            
            case 'sat-test-price':
            
                document.getElementById("sat-test-price").className += " hidden";
                break;
            
            case 'sat-2-prepare-price':
            
                document.getElementById("sat-2-prepare-price").className += " hidden";
                break;
            
            case 'new-sat-simulate-price':
            
                document.getElementById("new-sat-simulate-price").className += " hidden";
                break;
            
            case 'math-study-price':
            
                document.getElementById("math-study-price").className += " hidden";
                break;
            
            case 'ikmath-course-price':
            
                document.getElementById("ikmath-course-price").className += " hidden";
                break;
            
            case 'sat-prepare-price':
            
                document.getElementById("sat-prepare-price").className += " hidden";
                break;
            
            case 'sat-simulate-price':
            
                document.getElementById("sat-simulate-price").className += " hidden";
                break;
            

        }


    }
</script>
<form method="post" action="<?php echo locale_home_url() ?>/?r=create-group<?php echo $layout == 'create' ? '&amp;layout=create' : '' ?><?php echo isset($_GET['cid']) ? '&amp;cid=' . $_GET['cid'] : '' ?>" id="main-form" novalidate>
    <div class="row">
        <div class="creating-class">
            <div class="sub-tit">
                <h3>Earn money from your classroom (group) </h3>
                <p class="text-used">
                    Create a class and have students join. With a class name and password, a student can join your class from the list of classes displayed on the student site. You need to select what you will tutor. You may also offer a ready-made lesson in your classroom. You will earn the portion of sales (See the percentage below).
                </p>
                <ul class="text-percent">
                    <li>There is no cost of setting up a class</li>
                    <li>Earn money by offering tutoring, your lessons, and ready-made lessons</li>
                    <li>Flexible scheduling for your tutoring</li>
                    <li>Hundreds of ready-made worksheets you can use or create your own</li>

                </ul>
            </div>
        </div>

        <div class="col-md-12">

            <input type="hidden" name="cid" value="<?php echo $_GET['cid'] ?>">
            <input type="hidden" name="filter[orderby]" id="filter-order" value="<?php echo $filter['orderby'] ?>">
            <input type="hidden" name="filter[order-dir]" id="filter-order-dir" value="<?php echo $filter['order-dir'] ?>">
        </div>
<?php if (!$is_mw_admin || $layout == 'create') : ?>
            <div class="creating-class">
                <div class="sub-tit">
                    <h3>Explain your class to your students</h3>
                </div>
                <div class="col-sm-10 col-md-10 col-nopadding">
                    <div class="form-group">
                        <label for="group-name"><?php _e('Class Name', 'iii-dictionary') ?></label>
                        <input type="text" class="form-control" id="group-name" name="group-name" value="<?php echo $gname ?>">
                    </div>
                </div>
                <div class="col-sm-2 col-md-2 col-nopadding">
                    <div class="tooltip tooltip-manage-a-classroom col-xs-12 col-sm-12">

                        <a href="#"  id="check-availability" class="check-availability">
                            Find out availability
                            <span class="icon-loading"></span>
                            <span data-toggle="popover" data-placement="bottom" data-container="body"  data-html="true" data-max-width="420px" data-content="If username availability is “not available”, someone has already signed up with the email address you entered.<br>If username is “available”, please continue on with the sign up page."></span>
                        </a>
                    </div>
                </div>

                <div class="col-md-12 col-nopadding" style="margin-bottom: 20px;" >
                    <label for="about-class"><?php _e('Short description of the class', 'iii-dictionary') ?></label>
                    <div id="desc-class">
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
    <?php wp_editor('', 'post_message', $editor_settings); ?>
                        <div class="clear-both"></div>
                    </div>
                </div>
                <div class="">
                    <div class="form-group">
                        <label for="password"><?php _e('Do you want a password for your students to join your class', 'iii-dictionary') ?></label>
                        <div class="col-md-12 col-nopadding label-checkbox">
                            <input type="checkbox" class="checkboxagree option-input-2" id="pass-no"><label for="pass-no">No</label>
                        </div>
                        <div class="col-md-1 col-nopadding label-checkbox">
                            <input type="checkbox" class="checkboxagree option-input-2" id="pass-yes"><label for="pass-yes">Yes</label>
                        </div>

                        <div class="col-md-5 col-nopadding label-checkbox custom-col5">
                            <input type="text" class="form-control" id="type-password" name="type-password" value="<?php echo $gpass ?>" placeholder="Type Password...">
                        </div>
                        <div class="col-md-5 col-nopadding label-checkbox custom-col5">
                            <input type="text" class="form-control" id="retype-password" name="retypepassword" placeholder="Retype Password..." value="<?php echo $gpass ?>">
                        </div>
                    </div>
                </div>
                <div style="clear: both;"></div>
            </div>
            <div class="creating-class">

                <div class="sub-tit">
                    <h3>Adding features to your class <span>(This section can be edited later)</span></h3>
                </div>
                <div class="label-checkbox">
                    <div class="col-md-12 col-nopadding border-check">
                        <input type="checkbox" class="checkboxagree option-input-2" id="cb-tutoring" name="cb-tutoring">
                        <label for="cb-tutoring">Tutoring</label>

                    </div>   
                    <!--detail-->
                    <div id="detail-tutoring" class="hidden detail-feature">
                        <p class="text-used">If you are a qualified teacher, you can add this feature to your class. If you want to do tutoring, go to <span id="gotoTutor">Tutor Registration</span> page.</p>
                        <div class="option-tutoring">
                            <h4>English Writing Skill</h4>
                            <p class="text-desc-detail">You teach how to write English online, but not interactively</p>
                            <div class="col-md-12 col-nopadding">
                                <input type="checkbox" class="checkboxagree option-input-2" ><label>No</label>
                            </div>
                            <div class="col-md-12 col-nopadding unit-position">
                                <input type="checkbox" class="checkboxagree option-input-2" ><label>Yes</label>
                                <input type="text" name="price-eng-writing" id="price-eng-writing"><span class="text-desc-detail">Per Minute</span>
                                <span class="unit">$</span>
                            </div>
                            <div class="clear-both"></div>

                        </div>
                        <div class="option-tutoring">
                            <h4>English Conversation Practice</h4>
                            <p class="text-desc-detail">You converse with a foreign student over video chat</p>
                            <div class="col-md-12 col-nopadding">
                                <input type="checkbox" class="checkboxagree option-input-2" ><label>No</label>
                            </div>
                            <div class="col-md-12 col-nopadding">
                                <input type="checkbox" class="checkboxagree option-input-2" ><label>Yes</label>
                                <input type="text"  name="price-eng-conver" id="price-eng-conver"><span class="text-desc-detail">Per Minute</span>
                                <span class="unit">$</span>
                            </div>
                            <div class="clear-both"></div>

                        </div>
                        <div class="option-tutoring">
                            <h4>Math <span>(Up to Elementary School)</span></h4>
                            <p class="text-desc-detail">Tutor Math online interactively over voice chat and handwriting notepad chat</p>
                            <div class="col-md-12 col-nopadding">
                                <input type="checkbox" class="checkboxagree option-input-2" ><label>No</label>
                            </div>
                            <div class="col-md-12 col-nopadding">
                                <input type="checkbox" class="checkboxagree option-input-2" ><label>Yes</label>
                                <input type="text" name="price-math" id="price-math"><span class="text-desc-detail">Per Minute</span>
                                <span class="unit">$</span>
                            </div>
                            <div class="clear-both"></div>
                        </div>
                        <div class="option-tutoring">
                            <h4>Math <span>(Any Level)</span></h4>
                            <p class="text-desc-detail">Tutor Math online interactively over voice chat and handwriting notepad chat</p>
                            <div class="col-md-12 col-nopadding">
                                <input type="checkbox" class="checkboxagree option-input-2" ><label>No</label>
                            </div>
                            <div class="col-md-12 col-nopadding">
                                <input type="checkbox" class="checkboxagree option-input-2" ><label>Yes</label>
                                <input type="text" name="price-math-level" id="price-math-level"><span class="text-desc-detail">Per Minute</span>
                                <span class="unit">$</span>
                            </div>
                            <div class="clear-both"></div>
                        </div>

                    </div>
                    <div class="col-md-12 col-nopadding border-check">
                        <input type="checkbox" class="checkboxagree option-input-2" id="cb-lesson" name="cb-lesson">
                        <label for="cb-lesson">Lesson</label>
                    </div>  
                    <div id="detail-lesson" class="hidden detail-feature">
                        <p class="text-used">Create lesson for your classes. Lessons are set collections of worksheets created by you or from our library. </p>
                        <ul >
                            <li class="text-used"><span>Step 1: </span>Collect the Worksheets for the library for your class.</li>
                            <li class="text-used"><span>Step 2: </span>Create a lesson by defining the sequence of worksheets</li>
                            <li class="text-used"><span>Step 3: </span>Offer the lesson to your class with description and pricing </li>
                        </ul>
                        <div class="col-xs-6 col-sm-6 col-md-6 col-nopadding">
                            <div class="form-group">
                                <button type="submit" name="submit" class="btn-orange"><?php _e('Create a Lesson', 'iii-dictionary') ?></button>
                            </div>
                        </div>

                    </div>
                    <div class="col-md-12 col-nopadding" style="padding-bottom: 20px;padding-top: 10px;">
                        <input type="checkbox" class="checkboxagree option-input-2" id="cb-madelesson" name="cb-madelesson">
                        <label for="cb-madelesson">Ready-made Lesson</label>
                    </div>  
                    <div id="detail-madelesson" class="hidden detail-feature">
                        <p class="text-used">Offer a ready-made lesson on this site to your class. When a student purchase it from you, you earn the part of the payment. The ready-made lessons are self-grading worksheets and you help students to work on it.</p>
                        <div class="detail-madelesson">
                            <div class="col-md-12 col-nopadding border-check">
                                <input type="checkbox" class="checkboxagree option-input-2" id="cb-eng-lesson"><label>English</label>
                            </div>
                            <ul class="made-lesson hidden" id="eng-lesson">
                                <li class="item-lesson">
                                    <input type="checkbox" class="checkboxagree option-input-2 eng-option-detail" name="eng-self-study" id="eng-self-study"><label>English Self-Study <a class="detail_price" data-id="eng-self-study-price">(detail/price)</a></label>

                                </li>
                                <div id="eng-self-study-price" class="hidden detail-item-lesson">
                                    <p class="text-used">Our self study program is a good way for self improvement on different subject. We have over 200 worksheets of:
                                        <span class="detail-close" onclick="close_detail_price('eng-self-study-price')"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Close.png"></span>
                                    </p>

                                    <ul class="text-price">
                                        <li>Spelling</li>
                                        <li>Vocabulary & Grammar</li>
                                        <li>Reading Comprehension</li>
                                        <li>Writing Practice</li>
                                        <li>Vocabulary Builder Tools</li>
                                        <li>Your Choice of Dictionary (E Learner, Collegiate, Medical, Intermediate, and Elementary)</li>

                                    </ul>
                                    <p class="detail-price">Price for 1 month with your choice of 1 dictionary:
                                        <span>
                                        <?php 
                                            $self_study_price = mw_get_option('self-study-price');
                                            echo '$'.(int)$self_study_price;
                                        ?>
                                        </span>
                                    </p>
                                </div>
                                <li class="item-lesson">
                                    <input type="checkbox" class="checkboxagree option-input-2 eng-option-detail" name="mw-dictionary" id="mw-dictionary"><label>Merriam-Webster Dictionary <a class="detail_price" data-id="mw-dictionary-price">(detail/price)</a></label>
                                </li>
                                <div id="mw-dictionary-price" class="hidden detail-item-lesson">
                                    <p class="text-used">A powerful electronic version of America’s best-selling language reference that delivers accurate, up-to-date information while word processing, composing email, designing spreadsheets, preparing presentation, or surfing the Web.
                                        <span class="detail-close" onclick="close_detail_price('mw-dictionary-price')"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Close.png"></span>
                                    </p>

                                    <ul class="text-price">
                                        <li>If you purchase a dictionary subscription for multiple users, give each user the activation code that is generated after purchase. Users can activate their subscriptions and view account information under My Account</li>
                                        <li>For installation on public computers, such as school computer labs, click on the instructions (shown above on this page) after you have made the purchase.</li>


                                    </ul>
                                    <p class="detail-price">Price for starting is 
                                        <span>
                                            <?php 
                                                $dictionary_price = mw_get_option('dictionary-price');
                                                echo '$'.SUB_SAT_PREPARATION;
                                            ?>
                                        </span>
                                     for 3 month. (after that every month cost <span>$1</span>)</p>
                                </div>

                                <li class="item-lesson">
                                    <input type="checkbox" class="checkboxagree option-input-2 eng-option-detail" name="sat-grammar" id="sat-grammar"><label>SAT Preparation - Grammar Review  <a class="detail_price" data-id="sat-grammar-price">(detail/price)</a></label>
                                </li>
                                <div id="sat-grammar-price" class="hidden detail-item-lesson">
                                    <p class="text-used">SAT Preparation: You may join SAT preparation class at this site. Once you join, then you can start your preparation study.
                                        <span class="detail-close" onclick="close_detail_price('sat-grammar-price')"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Close.png"></span>
                                    </p>

                                    <ul class="text-price">
                                        <li>This class reviews grammar efficiently from beginning to end which is necessary for building basic skills for getting a high SAT score.</li>
                                        <li>You will also substantially build your vocabulary in this class.</li>

                                    </ul>
                                    <p class="detail-price">Price for 1 month: 
                                        <span>
                                            <?php 
                                                $sat_grammar = mw_get_option('sat-grammar-price');
                                                echo '$'.(int)$sat_grammar;
                                            ?>
                                        </span>
                                    </p>
                                </div>

                                <li class="item-lesson">
                                    <input type="checkbox" class="checkboxagree option-input-2 eng-option-detail" name="sat-writing" id="sat-writing"><label>SAT Preparation - Writing Practice  <a class="detail_price" data-id="sat-writing-price">(detail/price)</a></label>
                                </li>
                                <div id="sat-writing-price" class="hidden detail-item-lesson">
                                    <p class="text-used">SAT Preparation: You may join SAT preparation class at this site. Once you join, then you can start your preparation study.
                                        <span class="detail-close" onclick="close_detail_price('sat-writing-price')"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Close.png"></span>
                                    </p>

                                    <ul class="text-price">
                                        <li>This class lets you prepare writing section of the SAT test which covers: tips, writing style, methods, confusing words and phrases, and many more.</li>
                                        <li>Get real teacher’s help by requesting it from the writing group that you are currently in (Each request will cost the student. The price range will be vary to different task).</li>

                                    </ul>
                                    <p class="detail-price">Price for 1 month: 
                                        <span>
                                            <?php 
                                                $sat_writing = mw_get_option('sat-writing-price');
                                                echo '$'.(int)$sat_writing;
                                            ?>
                                        </span>
                                    </p>
                                </div>

                                <li class="item-lesson">
                                    <input type="checkbox" class="checkboxagree option-input-2 eng-option-detail" name="sat-test" id="sat-test"><label>SAT Simulation Test  <a class="detail_price" data-id="sat-test-price">(detail/price)</a></label>
                                </li>
                                <div id="sat-test-price" class="hidden detail-item-lesson">
                                    <p class="text-used">SAT Preparation: You may join SAT preparation class at this site. Once you join, then you can start your preparation study.
                                        <span class="detail-close" onclick="close_detail_price('sat-test-price')"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Close.png"></span>
                                    </p>

                                    <ul class="text-price">
                                        <li>You may subscribe to one of five tests available for SAT. Once subscribed, you may take the same test as many times as you want.</li>
                                        <li>If you want different set for practice test, you need to subscribe for the second practice test. You can subscribe up to five practice tests. You may retake subscribed tests as many times as you want during your valid subscription period.</li>

                                    </ul>
                                    <p class="detail-price">Price for 1 month: 
                                        <span>
                                            <?php 
                                                $sat_test = mw_get_option('sat-test-price');
                                                echo '$'.(int)$sat_test;
                                            ?>
                                        </span>
                                    </p>   
                                </div>
                            </ul>
                            <div class="col-md-12 col-nopadding" style="padding-bottom: 20px;padding-top: 10px;">
                                <input type="checkbox" class="checkboxagree option-input-2" id="cb-math-lesson"><label>Math</label>
                            </div>
                            <div class="clear-both"></div>
                            <ul class="made-lesson hidden" id="math-lesson">
                                <li class="item-lesson">
                                    <input type="checkbox" class="checkboxagree option-input-2 math-option-detail" name="math-study" id="math-study"><label>Math Self-Study <a class="detail_price" data-id="math-study-price">(detail/price)</a></label>
                                </li>
                                <div id="math-study-price" class="hidden detail-item-lesson">
                                    <p class="text-used">You can use all worksheets listed in this site once you subscribe "Self-study" mode. Self-study mode is monthly subscription. You can see the complete list of worksheets by clicking below.
                                        <span class="detail-close" onclick="close_detail_price('math-study-price')"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Close.png"></span>
                                    </p>

                                    <ul class="text-price">
                                        <li>Word Problem</li>
                                        <li>Add and Sub & Single Digit Multiplication</li>
                                        <li>Two Digit Multiplication</li>
                                        <li>Long Division by Single Digit</li>
                                        <li>Equation</li>
                                        <li>Fraction</li>
                                        <li>Question Box</li>
                                    </ul>
                                    <p class="detail-price">Price for 1 month: 
                                        <span>
                                            <?php 
                                                $self_study_price_math = mw_get_option('math-self-study-price');
                                                echo '$'.(int)$self_study_price_math;
                                            ?>
                                        </span>
                                    </p>
                                </div>


                                <li class="item-lesson">
                                    <input type="checkbox" class="checkboxagree option-input-2 math-option-detail" name="ikmath-course" id="ikmath-course"><label>ikMath Courses <a class="detail_price" data-id="ikmath-course-price">(detail/price)</a></label>
                                </li>
                                <div id="ikmath-course-price" class="hidden detail-item-lesson">
                                    <p class="text-used">Please select the grade you subscribe and pay for one month subscription below.
                                        <span class="detail-close" onclick="close_detail_price('ikmath-course-price')"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Close.png"></span>
                                    </p>

                                    <ul class="text-price">
                                        <li>Select classes from Math Kindergarten to Math Grade 12</li>


                                    </ul>
                                    <p class="detail-price" style="height: auto;">
                                        Math Kindergarten - Price for 1 month:<span> $20</span><br/>
                                        Math Grade 1 - Price for 1 month: <span>$20</span><br/>
                                        Math Grade 2 - Price for 1 month: <span>$20</span><br/>
                                        Math Grade 3 - Price for 1 month: <span>$30</span><br/>
                                        Math Grade 4 - Price for 1 month: <span>$30</span><br/>
                                        Math Grade 5 - Price for 1 month: <span>$30</span><br/>
                                        Math Grade 6 - Price for 1 month: <span>$30</span><br/>
                                        Math Grade 7 - Price for 1 month: <span>$40</span><br/>
                                        Math Grade 8 - Price for 1 month: <span>$40</span><br/>
                                        Math Grade 9 - Price for 1 month: <span>$50</span><br/>
                                        Math Grade 10 - Price for 1 month: <span>$50</span><br/>
                                        Math Grade 11 - Price for 1 month: <span>$50</span><br/>
                                        Math Grade 12 - Price for 1 month: <span>$50</span><br/>
                                    </p>
                                </div>


                                <li class="item-lesson">
                                    <input type="checkbox" class="checkboxagree option-input-2 math-option-detail" name="sat-prepare" id="sat-prepare"><label>SAT Preparation <a class="detail_price" data-id="sat-prepare-price">(detail/price)</a></label>
                                </li>
                                <div id="sat-prepare-price" class="hidden detail-item-lesson">
                                    <p class="text-used">This practice is essential to prepare for SAT I preparation. You will quickly review entire Algebra in this class.
                                        <span class="detail-close" onclick="close_detail_price('sat-prepare-price')"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Close.png"></span>
                                    </p>

                                    <ul class="text-price">
                                        <li>The review of Algebra cover subjects like: Function, Function Word Problems, Linear Word Problems, Quadratic Word Problems, Word Problems and so forth.</li>
                                        <li>The review of Geometry cover subjects like: Geometry Review, Volume Area, Word Problems, Analytical and so forth.</li>

                                    </ul>
                                    <p class="detail-price">Price for 1 month: <span>$50</span></p>
                                </div>


                                <li class="item-lesson">
                                    <input type="checkbox" class="checkboxagree option-input-2 math-option-detail" name="new-sat-simulate" id="new-sat-simulate"><label>SAT Simulation Test (New SAT Test) <a class="detail_price" data-id="new-sat-simulate-price">(detail/price)</a></label>
                                </li>
                                <div id="new-sat-simulate-price" class="hidden detail-item-lesson">
                                    <p class="text-used">Five simulated tests which consists of multiple choices, numeric answers, with or without calculator questions and so forth.
                                        <span class="detail-close" onclick="close_detail_price('new-sat-simulate-price')"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Close.png"></span>
                                    </p>

                                    <ul class="text-price">
                                        <li>New updated SAT simulated test.</li>
                                        <li>Five simulated tests which consists of multiple choices, numeric answers, with or without calculator questions and so forth.</li>

                                    </ul>
                                    <p class="detail-price">Price for 1 month: <span>$50</span></p>
                                </div>


                                <li class="item-lesson">
                                    <input type="checkbox" class="checkboxagree option-input-2 math-option-detail" name="sat-2-prepare" id="sat-2-prepare"><label>SAT 2 Preparation <a class="detail_price" data-id="sat-2-prepare-price">(detail/price)</a></label>
                                </li>
                                <div id="sat-2-prepare-price" class="hidden detail-item-lesson">
                                    <p class="text-used">This practice is essential to prepare for SAT 2 preparation. You will quickly review entire Algebra in this class.
                                        <span class="detail-close" onclick="close_detail_price('sat-2-prepare-price')"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Close.png"></span>
                                    </p>

                                    <ul class="text-price">
                                        <li>The review of Algebra II cover subjects like: Polynomial, Rational, Exponent, Logarithm, Trigonometry, Conic and so forth.</li>
                                        <li>The review of Precalculus cover subjects like: Precalculus and so forth.</li>

                                    </ul>
                                    <p class="detail-price">Price for 1 month: <span>$80</span></p>
                                </div>


                                <li class="item-lesson">
                                    <input type="checkbox" class="checkboxagree option-input-2 math-option-detail" name="sat-simulate" id="sat-simulate"><label>SAT Simulation Test <a class="detail_price" data-id="sat-simulate-price">(detail/price)</a></label>
                                </li>
                                <div id="sat-simulate-price" class="hidden detail-item-lesson">
                                    <p class="text-used">There are five simulated tests available for the subscription. Once subscribed, you may take the test immediately.
                                        <span class="detail-close" onclick="close_detail_price('sat-simulate-price')"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Close.png"></span>
                                    </p>

                                    <ul class="text-price">
                                        <li>Five simulated tests which consists of multiple choices, numeric answers, with or without calculator questions and so forth.</li>

                                    </ul>
                                    <p class="detail-price">Price for 1 month: <span>$50</span></p>
                                </div>


                            </ul>

                            <div class="clear-both"></div>

                        </div>



                    </div>
                </div>

            </div>
            <div class="col-xs-8 col-sm-8 col-md-8 col-nopadding">
                <div class="form-group">
                    <button type="submit" name="submit" class="btn-orange"><?php _e('Create a new class now', 'iii-dictionary') ?></button>
                </div>
            </div>
            <div class="col-xs-4 col-sm-4 col-md-4 col-nopadding">
                <div class="form-group">
                    <button type="submit" name="cancel" class="btn-cancel-grey"><?php _e('Cancel', 'iii-dictionary') ?></button>
                </div>
            </div>
        </div>
<?php endif ?>

</div>
</div>
</form>

<div class="modal fade modal-red-brown" id="manage-classroom-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="container" style="margin-top:40px; margin-bottom:20px">
        <div class="modal-content">
<?php MWHtml::manage_your_class(); ?>
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
        $(".view-students").click(function () {

            var tthis = $(this);
            $("#group-id").val(tthis.attr("data-gid"));
            $('.edit-class').hide();
            $('#btn-class').show();
            //var tbody = $("#classes-detail-list-students tbody");
            //tbody.html("");
            $.get(home_url + "/?r=ajax/group/students", {gid: tthis.attr("data-gid")}, function (data) {
                //data = JSON.parse(data);
                // if(data.length > 0){
                // 	$.each(data, function(i,v){
                // 		var tr = "<tr><td>" + v.name + "</td><td>" + v.email + "</td><td>" + v.joined_date + "</td><td>" + v.done_hw + "</td></tr>";
                // 		tbody.append(tr);
                // 	});
                // }else{
                // 	var tr = "<tr><td colspan='4'>" + JS_MESSAGES.empty_group + "</td></tr>";
                // 	tbody.append(tr);
                // }

                if (tthis.attr("data-price") == 'Free') {
                    var price_class = 'Free';
                } else {
                    var price_class = tthis.attr("data-price");
                    var checked = "<?php echo get_template_directory_uri(); ?>/library/images/check_box_checked.png";
                    var empty = "<?php echo get_template_directory_uri(); ?>/library/images/check_box_empty.png";
                    $('#fee-class').val(tthis.attr("data-price"));
                    $('#cb-free').val('0');
                    $('#cb-free').next('input + img').attr('alt', '0');
                    $('#cb-free').next('input + img').attr('src', empty);

                    $('#cb-change-price').val('1');
                    $('#cb-change-price').next('input + img').attr('alt', '0');
                    $('#cb-change-price').next('input + img').attr('src', checked);
                    $('.css-show-press').css("visibility", "visible");
                }
                document.getElementById("class-detail-group-name").innerHTML = tthis.attr("data-gname");
                document.getElementById("price-class").innerHTML = price_class;
                document.getElementById("ofStudent-class").innerHTML = tthis.attr("data-stu");
                document.getElementById("expiration-class").innerHTML = tthis.attr("data-expired");
                document.getElementById("group-about-class").innerHTML = tthis.attr("data-about-class");
                //document.getElementById("group-about-class").innerHTML = tthis.attr('data-id');
                $('#id-class').val(tthis.attr('data-id'));
                $(".bd-detail-classes-created-modal-lg").modal();
            });
        });
        $(".view-ud-subscription").click(function () {
            var tthis = $(this);
            document.getElementById("class-ud-subscription-group-name").innerHTML = tthis.attr("data-gname");
            // if($.isNumeric(tthis.attr("data-price"))){
            // 	document.getElementById("price-ud-sub").innerHTML = "$ "+ insertDecimal(parseFloat(tthis.attr("data-price")));
            // }else{
            // 	document.getElementById("price-ud-sub").innerHTML = tthis.attr("data-price");
            // }
            if (tthis.attr("data-price") == 'Free')
                var price_class = 'Free';
            else
                var price_class = tthis.attr("data-price");
            document.getElementById("price-ud-sub").innerHTML = price_class;
            document.getElementById("stu-ud-sub").innerHTML = tthis.attr("data-stu");
            document.getElementById("expired-date-ud-sub").innerHTML = tthis.attr("data-expired");
            $(".bd-update-subscription-modal-lg").modal();
        });
        function insertDecimal(num) {
            return num.toFixed(2);
        }
    });

</script>
<div class="modal fade bd-update-subscription-modal-lg modal-classes-created" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" style="background:#fff;">
    <div class="modal-dialog modal-lg modal-dialog-classes-created modal-dialog-assignment-created">
        <div class="modal-content">
            <header class="col-xs-12 article-header">
                <div class="row">
                    <div class="container">
                        <div class="page-title-responsive">
                            <h1 class="page-title text-uppercase pull-left" itemprop="headline" style="padding-top: 8px;padding-left: 4px;">Update Subscription</h1>
                            <img class="icon-close-update-subscription pull-right" data-dismiss="modal" aria-hidden="true" style="cursor: pointer; margin-right: -3px;" src="<?php echo get_template_directory_uri(); ?>/library/images/close_blue.png">
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
                                    <div class="form-div-border" style="padding-bottom: 0;">
                                        <span>
                                            <span class="class-name-grey">Class name: </span><strong id="class-ud-subscription-group-name"></strong>
                                        </span>
                                        <div class="table-responsive" style="padding-top: 20px;">
                                            <table class="table ">
                                                <thead>
                                                    <tr>
                                                        <th>Price</th>
                                                        <th># of Students</th>
                                                        <th>Expiration Date</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td id="price-ud-sub">$2.00</td>
                                                        <td id="stu-ud-sub"></td>
                                                        <td id="expired-date-ud-sub">2/14/2018<span>(expired)</span></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="" style="padding: 30px 0;">

                                        <div class="row">
                                            <label class="col-md-5">Would you like to extends the subscription?</label>
                                            <div class="col-xs-3 col-sm-3 col-md-2">
                                                <select class="select-box-it form-control" id="num-month-ud-sub" name="birth-m">
<?php for ($i = 0; $i <= 12; $i++) : ?>
    <?php $pad_str = str_pad($i, 2, '0', STR_PAD_LEFT) ?>
                                                        <option value="<?php echo $pad_str ?>"><?php echo $pad_str ?></option>
<?php endfor ?>
                                                </select>
                                            </div>
                                            <span> Month(s)</span>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <div class="col-md-12">

                                    <div class="title-border" style="padding: 30px 0;">
                                        <div class="row">
                                            <label class="col-md-4">Would you like to add more students?</label>
                                            <div class="col-xs-3 col-sm-3 col-md-2">
                                                <select class="select-box-it form-control" id="num-students-ud-sub" name="student">
<?php for ($i = 0; $i <= 31; $i++) : ?>
    <?php $pad_str = str_pad($i, 2, '0', STR_PAD_LEFT) ?>
                                                        <option value="<?php echo $pad_str ?>"><?php echo $pad_str ?></option>
<?php endfor ?>
                                                </select>
                                            </div>
                                            <span> Student(s)</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="total-amount-ud-sub">Total Amount: <strong  id="total-amount-ud-sub" style="padding-left: 10px;">$0.00</strong>
                                    </div>
                                </div>
                                <div class="col-xs-12 col-sm-12 col-md-12">
                                    <div class="col-xs-6 col-sm-6 col-md-6 pad-left-0"><!--
                                     <button class="create-a-class-btn">Update</button> -->
                                        <button class="btn-dark-blue" type="submit"><?php _e('Check out', 'iii-dictionary') ?></button>
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

<div class="modal fade bd-detail-classes-created-modal-lg modal-classes-created" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" style="background:#fff;">
    <div class="modal-dialog modal-lg modal-dialog-classes-created modal-dialog-assignment-created">
        <div class="modal-content">
            <header class="col-xs-12 article-header">
                <div class="row">
                    <div class="container">
                        <div class="page-title-responsive">
                            <h1 class="page-title text-uppercase pull-left" itemprop="headline" style="padding-top: 8px;">Edit class</h1>
                            <img class="icon-close-detail-classes-created" data-dismiss="modal" aria-hidden="true" src="<?php echo get_template_directory_uri(); ?>/library/images/close_blue.png">
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
                                    <input type="hidden" name="id-class" id="id-class" value="0" />
                                </div>
                            </div>
                            <div class="about-class col-md-12">
                                <p><span class="class-name-grey">About the Class:</span></p>
                                <p id="group-about-class"></p>
                                <hr/>
                                <table class="">
                                    <tr>
                                        <th width="10%">Price</th>
                                        <th width="10%"># of Students:</th>
                                        <th width="40%">Expiration Date:</th>
                                    </tr>
                                    <tr>

                                        <td id="price-class"></td>
                                        <td id="ofStudent-class"></td>
                                        <td id="expiration-class"></td>
                                    </tr>
                                </table>
                                <hr id="hr"/>
                                <div id="btn-class" class="row">
                                    <div class="col-md-6">
                                        <input type="submit" name="btn-edit-class" id="btn-edit-class" class="btn-edit-class" value="Edit"  >
                                    </div>
                                    <div class="col-md-6">
                                        <input type="submit" name="btn-cancel" data-dismiss="modal" class="btn-cancel" value="Cancel">
                                    </div>
                                </div>
                            </div>
                            <!--                            --><?php
//                            if(isset($_POST['btn-save-class'])){
//                                $redirect_to = locale_home_url() . '/?r=create-group&amp;layout=create';
//
//                                wp_redirect($redirect_to);
//                            }
//                            
?>
                            <div class="edit-class col-md-12">
                                <form method="post" novalidate>
                                    <div class="form__boolean">
                                        <h4><span class="page-title text-uppercase" >Editing the Class</span></h4>
                                        <p>Rename the Class</p>
                                        <input class="form__group" type="text" name="txtrename" id="txtrename" >
                                        <p>Rewrite About the Class</p>
                                        <textarea name="rewrite" id="rewrite" cols="12" rows="5"></textarea>
                                        <div class="row" style="margin-top: 20px;">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="current_password">Type Current Password</label>
                                                    <input class="form-control" id="current_password" name="current_password" value="" type="text">
                                                </div>                                    		
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="new_password">Type New Password</label>
                                                    <input class="form-control" id="new_password" name="new_password" value="" type="text">
                                                </div>                                        	
                                            </div>
                                        </div>
                                        <p style="margin-top: 20px;">Do you want to change the amount of fee for joining this class?</p>

                                        <span class="radio">
                                            <input type="hidden" name="radio-fee" id="cb-free" value="1">
                                            <img class="check_box_fee_class img-height-22" src="<?php echo get_template_directory_uri(); ?>/library/images/check_box_checked.png" alt="1">
                                            <label class="collection_radio_buttons moneychose-option" for="application_charge_students_1"><span>Free</span></label>
                                        </span>

                                        <span class="radio">
                                            <input type="hidden" name="radio-fee" id="cb-change-price" value="0">
                                            <img style="height: 22px;" class="check_box_fee_class img-height-22" src="<?php echo get_template_directory_uri(); ?>/library/images/check_box_empty.png" alt="0">
                                            <label class="collection_radio_buttons moneychose-option" for="application_charge_students_2">
                                                <span >Change Price</span>

                                                <input type="number" name="feecharge-students-yesclass" id="fee-class" >
                                                <span class="unit css-show-press" style="left: 110px; top:3px;">$</span>

                                                <span style="color: #909090; padding-left: 10px; font-size: 14px; ">New Student Only</span>

                                                <div class="tooltip-col text-center col-md-12 col-md-offset-2">
                                                    <span class="image-tooltip" style="left: 265px;"> <!--style=" top:-15px !important;"-->
                                                        <img style="margin-left: 10px;" class="img-height-22" src="<?php echo get_template_directory_uri() ?>/library/images/Question_Icon.png">
                                                    </span>
                                                    <div class="tooltiptext col-xs-12 col-sm-6 col-md-4">
                                                        <ul>
                                                            <li>
                                                                <span class="number-tp">1.</span>Student will be required  to pay when joining class
                                                            </li>
                                                            <li>
                                                                <span class="number-tp">2.</span><?php
                                                                $per_be_paid = MWDB::get_percentage_paid_to_teacher();
                                                                echo $per_be_paid[0]->option_value;
?>% to be paid to teacher
                                                            </li>
                                                        </ul>
                                                    </div>
                                                    </span>
                                                </div>			                                
                                                </form>
                                            </label>

                                        </span>
                                    </div>

                                    <div class="row" style="margin-top: 50px;">
                                        <form method="post" action="<?php echo locale_home_url() ?>/?r=create-group&amp;layout=create">
                                            <div class="col-md-6">

                                                <input type="submit" name="btn-save-class" id="btn-save-class"  class="btn-edit-class" value="Save and Finish" >
                                            </div>
                                            <div class="col-md-6">
                                                <input type="button" name="btn-cancel" data-dismiss="modal" class="btn-cancel" value="Cancel">
                                            </div>
                                        </form>
                                    </div>
                            </div>
                        </div>
                    </div>
            </div>
            <input type="hidden" name="group-id" id="group-id">
        </div>
    </div>
</section>
</div>
</div>
</div>
</div>
<div class="modal fade modal-red-brown" id="error-messages-modal" tabindex="-1" role="dialog" aria-hidden="true" style="padding-right: 17px;">
    <div class="modal-dialog">
        <div class="modal-content" style="margin-top: 42px;">
            <div class="modal-body">
                <div class="row">
                    <div class="col-xs-11" id="error-edit-class">

                    </div>
                    <img class="icon-close-classes-created" data-dismiss="modal" aria-hidden="true" style="top: 25%" src="<?php echo get_template_directory_uri(); ?>/library/images/close_white.png">
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    jQuery(function ($) {

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
        $(".check_box_fee_class").each(function (index) {
            $(this).on("click", function () {
                var elements = document.getElementsByClassName('check_box_fee_class'), i, len;
                var cb_1 = elements[0];
                var cb_2 = elements[1];

                if (index == 0) {
                    if (cb_1.alt === "0" && cb_2.alt === "0") {
                        cb_1.alt = "1";
                        cb_1.src = "<?php echo get_template_directory_uri(); ?>/library/images/check_box_checked.png";
                        cb_2.src = "<?php echo get_template_directory_uri(); ?>/library/images/check_box_empty.png";
                        document.getElementById("cb-free").value = "0";
                    } else if (cb_1.alt === "0" && cb_2.alt === "1") {
                        cb_1.alt = "1";
                        cb_2.alt = "0";
                        cb_1.src = "<?php echo get_template_directory_uri(); ?>/library/images/check_box_checked.png";
                        cb_2.src = "<?php echo get_template_directory_uri(); ?>/library/images/check_box_empty.png";
                        document.getElementById("cb-free").value = "0";
                        document.getElementById("cb-change-price").value = "1";
                    }
                } else {
                    if (cb_1.alt === "0" && cb_2.alt === "0") {
                        cb_2.alt = "1";
                        cb_2.src = "<?php echo get_template_directory_uri(); ?>/library/images/check_box_checked.png";
                        cb_1.src = "<?php echo get_template_directory_uri(); ?>/library/images/check_box_empty.png";
                        document.getElementById("cb-free").value = "0";
                    } else if (cb_1.alt === "1" && cb_2.alt === "0") {
                        cb_2.alt = "1";
                        cb_1.alt = "0";
                        cb_2.src = "<?php echo get_template_directory_uri(); ?>/library/images/check_box_checked.png";
                        cb_1.src = "<?php echo get_template_directory_uri(); ?>/library/images/check_box_empty.png";
                        document.getElementById("cb-free").value = "0";
                        document.getElementById("cb-change-price").value = "1";
                    }
                }

            });
        });
        $(".check_box_charge_students").each(function (index) {
            $(this).on("click", function () {
                var elements = document.getElementsByClassName('check_box_charge_students'), i, len;
                var cb_1 = elements[0];
                var cb_2 = elements[1];

                if (index == 0) {
                    if (cb_1.alt === "0" && cb_2.alt === "0") {
                        cb_1.alt = "1";
                        cb_1.src = "<?php echo get_template_directory_uri(); ?>/library/images/check_box_checked.png";
                        document.getElementById("charge-students-no").value = "1";
                    } else if (cb_1.alt === "0" && cb_2.alt === "1") {
                        cb_1.alt = "1";
                        cb_2.alt = "0";
                        cb_1.src = "<?php echo get_template_directory_uri(); ?>/library/images/check_box_checked.png";
                        cb_2.src = "<?php echo get_template_directory_uri(); ?>/library/images/check_box_empty.png";
                        document.getElementById("charge-students-no").value = "1";
                        document.getElementById("charge-students-yes").value = "0";
                    }
                } else {
                    if (cb_1.alt === "0" && cb_2.alt === "0") {
                        cb_2.alt = "1";
                        cb_2.src = "<?php echo get_template_directory_uri(); ?>/library/images/check_box_checked.png";
                        document.getElementById("charge-students-yes").value = "1";
                    } else if (cb_1.alt === "1" && cb_2.alt === "0") {
                        cb_2.alt = "1";
                        cb_1.alt = "0";
                        cb_2.src = "<?php echo get_template_directory_uri(); ?>/library/images/check_box_checked.png";
                        cb_1.src = "<?php echo get_template_directory_uri(); ?>/library/images/check_box_empty.png";
                        document.getElementById("charge-students-yes").value = "1";
                        document.getElementById("charge-students-no").value = "0";
                    }
                }

            });
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

        // $('#num-month').on('change', function() {
        //   document.getElementById("total-price").innerHTML = "$"+ this.value*document.getElementById("num-students").value*5;
        // });
        //    $('#num-students').on('change', function() {
        //   document.getElementById("total-price").innerHTML = "$"+ this.value*document.getElementById("num-month").value*5;
        // });
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

<?php if (!$is_math_panel) : ?>
    <?php get_dict_footer() ?>
<?php else : ?>
    <?php get_math_footer() ?>
<?php endif ?>

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
            $('#manage_classroom').click(function (e) {
                $('#manage-classroom-modal').addClass('in');
                $('#manage-classroom-modal').modal("show");
            });
            $('.price-per-student').keypress(function () {
                $('.css-show-press').css("visibility", "visible");
            });
            $('#fee-class').keypress(function () {
                $('.css-show-press').css("visibility", "visible");
            });
            $('#btn-edit-class').click(function () {
                $('#btn-class').hide();
                $('#hr').show();
            });
            $('#btn-edit-class').click(function () {
                $('#btn-class').hide();
                $('#hr').show();
                $('#current_password').val('');
                $('#new_password').val('');
                $('.edit-class').show();
            });

            $('#btn-save-class').click(function (e) {
                e.preventDefault();
                var group_id = $('#id-class').val();
                var aboutcl = $('#rewrite').val();
                var radio_alt = $('.check_box_fee_class').attr('alt');
                var name = $('#txtrename').val();
                var current_password = $('#current_password').val();
                var new_password = $('#new_password').val();
                var price = 0;
                if (radio_alt == "1") {
                    price = 0;
                } else {
                    price = $('#fee-class').val();
                }
                $.get(home_url + "/?r=ajax/update_edit_class", {group_id: group_id, name: name, price: price, about_class: aboutcl, check_box_fee: radio_alt, current_password: current_password, new_password: new_password}, function (data) {
                    if ($.trim(data) == 'ok') {
                        window.location.reload();
                    } else {
                        $('#error-edit-class').html(data);
                        $('#error-messages-modal').modal('show');
                    }
                });
            });
            $("#cb-tutoring").click(function () {
                var check = $("#cb-tutoring").is(":checked");
                if (check == true) {
                    $("#detail-tutoring").removeClass("hidden");
                } else {
                    $("#detail-tutoring").addClass("hidden");

                }
            });

            $("#cb-lesson").click(function () {
                var check = $("#cb-lesson").is(":checked");
                if (check == true) {
                    $("#detail-lesson").removeClass("hidden");
                } else {
                    $("#detail-lesson").addClass("hidden");

                }
            });
            $("#cb-madelesson").click(function () {
                var check = $("#cb-madelesson").is(":checked");
                if (check == true) {
                    $("#detail-madelesson").removeClass("hidden");
                } else {
                    $("#detail-madelesson").addClass("hidden");
                    $(".made-lesson").addClass("hidden");
                    $("#cb-eng-lesson").attr("checked", false);
                    $("#cb-math-lesson").attr("checked", false);
                }
            });
            $("#cb-eng-lesson").click(function () {
                var check = $("#cb-eng-lesson").is(":checked");
                if (check == true) {
                    $("#eng-lesson").removeClass("hidden");
                } else {
                    $("#eng-lesson").addClass("hidden");
                    $(".detail-item-lesson").addClass("hidden");
                    $(".eng-option-detail").attr("checked", false);
                }
            });
            $("#cb-math-lesson").click(function () {
                var check = $("#cb-math-lesson").is(":checked");
                if (check == true) {
                    $("#math-lesson").removeClass("hidden");
                } else {
                    $("#math-lesson").addClass("hidden");
                     $(".detail-item-lesson").addClass("hidden");
                     $(".math-option-detail").attr("checked", false);
                }
            });

            $("#gotoTutor").click(function () {
                $("#my-account-modal").modal('show');
                $("#sub-createacc").removeClass("active");
                $("#tutor-regist").addClass("active");
                $("#create-account").removeClass("active");
                $("#create-account").removeClass("in");
                $("#tutor-regis-tab").addClass("active");
                $("#tutor-regis-tab").addClass("in");
            });



        });//end function
    })(jQuery);
</script>
