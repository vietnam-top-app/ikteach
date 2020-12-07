
<?php
$user = wp_get_current_user();

$user_groups = MWDB::get_current_user_groups_create();

$_page_title = __('My Account', 'iii-dictionary');

$user_group_join = MWDB::get_join_user_groups();

$createdgroup = array();

for ($i = 0; $i < count($user_groups); $i++) {

    $createdgroup[$i] = $user_groups[$i]->name;
}

$status_teacher = '';

$is_registered_math_teacher = is_mw_registered_teacher($user->ID, 1);

$is_registered_english_teacher = is_mw_registered_teacher($user->ID);

$is_qualified_math_teacher = is_mw_qualified_teacher($user->ID, 1);

$is_qualified_english_teacher = is_mw_qualified_teacher($user->ID);

$is_mw_registered_teacher = is_mw_registered_teacher();

$is_mw_registered_math_teacher = is_mw_registered_teacher($user->ID, 1);

$is_teaching_agreement_uptodate = ik_is_teacher_agreement_uptodate();

$is_teaching_math_agreement_uptodate = ik_is_teacher_agreement_uptodate(1);

if ($is_registered_math_teacher) :

    $status_teacher_qualified_math = 'I am registered teacher.';

endif;

if ($is_qualified_math_teacher) :

    $status_teacher_qualified_math = 'I am a qualified teacher.';

endif;

if ($status_teacher_qualified_math != 'I am registered teacher.' && $status_teacher_qualified_math != 'I am a qualified teacher.') {

    $status_teacher_qualified_math = 'I am unregistered teacher.';
}

if ($is_registered_english_teacher) :

    $status_teacher_qualified_english = 'I am registered teacher.';

endif;

if ($is_qualified_english_teacher) :

    $status_teacher_qualified_english = 'I am a qualified teacher.';

endif;

if ($status_teacher_qualified_english != 'I am registered teacher.' && $status_teacher_qualified_english != 'I am a qualified teacher.') {

    $status_teacher_qualified_english = 'I am unregistered teacher.';
}



if (isset($_POST['save'])) {

    if (MWDB::update_user($user)) {

        wp_redirect(locale_home_url() . '/?r=my-account');

        exit;
    }
}

if (isset($_POST['update-teacher'])) {

    if (MWDB::update_user($user)) {

        wp_redirect(locale_home_url() . '/?r=my-account');

        exit;
    }
}

if (isset($_POST['i-agree-update'])) {

    if (MWDB::update_user($user)) {

        wp_redirect(locale_home_url() . '/?r=my-account');

        exit;
    }
}

$tab_title = __('Follow the steps to teach classes', 'iii-dictionary');

$tab_info_url = get_info_tab_cloud_url('Popup_info_28.jpg');



$is_teaching_agreement_agreed = ik_is_teaching_agreement_agreed();

$teacher_test_score_threshold = mw_get_option('teacher-test-score-threshold');

$filter['group_id'] = mw_get_option('teacher-test-group');



$filter['check_result'] = true;

$filter['user_id_result'] = $current_user->ID;



$tests = MWDB::get_homework_assignments($filter);



// make sure second test is writing test

if ($tests->items[0]->assignment_id == ASSIGNMENT_WRITING) {

    $tmp = $tests->items[0];

    $tests->items[0] = $tests->items[1];

    $tests->items[1] = $tmp;
}



if (!empty($tests->items)) {

    $test1_result = MWDB::get_homework_results($tests->items[0]->id, $current_user->ID);

    $test2_result = MWDB::get_homework_results($tests->items[1]->id, $current_user->ID);
}

if (isset($_POST['take-test1']) || isset($_POST['take-test2'])) {

    MWDB::join_group($filter['group_id']);

    ik_clear_message_queue();



    if (!empty($tests->items)) {

        // store teacher tests in the session so we can check later

        $_SESSION['teacher_tests'] = array($tests->items[0]->sheet_id, $tests->items[1]->sheet_id);



        if (isset($_POST['take-test1'])) {

            if (!$is_mw_registered_teacher) {

                if (!$is_teaching_agreement_uptodate) {

                    $title = __('Agreement Updated', 'iii-dictionary');

                    $body = __('Our Agreement is updated, please agree to the new Agreement in order to continue.', 'iii-dictionary');
                }



                if (!$is_mw_registered_teacher) {

                    $title = __('Registration Required', 'iii-dictionary');

                    $body = __('Please register as english Teacher in order to access this page.', 'iii-dictionary');
                }

                set_lockpage_dialog($title, $body, $return_url);
            } else {

                $test_url = MWHtml::get_practice_page_url($tests->items[0]->assignment_id) . '&mode=homework&sid=' . $tests->items[0]->sheet_id;
            }
        }

        if (isset($_POST['take-test2'])) {

            if (!$is_mw_registered_teacher) {

                if (!$is_teaching_agreement_uptodate) {

                    $title = __('Agreement Updated', 'iii-dictionary');

                    $body = __('Our Agreement is updated, please agree to the new Agreement in order to continue.', 'iii-dictionary');
                }



                if (!$is_mw_registered_teacher) {

                    $title = __('Registration Required', 'iii-dictionary');

                    $body = __('Please register as Teacher in order to access this page.', 'iii-dictionary');
                }

                set_lockpage_dialog($title, $body, $return_url);
            } else {

                $test_url = MWHtml::get_practice_page_url($tests->items[1]->assignment_id) . '&mode=homework&sid=' . $tests->items[1]->sheet_id;
            }
        }

        if (isset($test_url)) {

            wp_redirect($test_url);

            exit;
        }
    }
}



// teacher want to re take test 1

if (isset($_POST['re-take-test1'])) {

    MWDB::delete_homework_result($test1_result[0]->homework_result_id);



    $test_url = MWHtml::get_practice_page_url($tests->items[0]->assignment_id) . '&mode=homework&sid=' . $tests->items[0]->sheet_id;

    wp_redirect($test_url);

    exit;
}



// teacher want to re take test 2

if (isset($_POST['re-take-test2'])) {

    MWDB::delete_homework_result($test2_result[0]->homework_result_id);



    $test_url = MWHtml::get_practice_page_url($tests->items[1]->assignment_id) . '&mode=homework&sid=' . $tests->items[1]->sheet_id;

    wp_redirect($test_url);

    exit;
}



// agree teaching agreement

if (isset($_POST['teaching-english-agree'])) {

    ik_agree_teaching_agreement();



    wp_redirect(locale_home_url() . '/?r=my-account');

    exit;
}

// accept grading request

if (isset($_POST['accept-request'])) {

    $request = MWDB::get_worksheet_grading_request($_POST['request-id']);



    $_SESSION['grading_homework'] = array('hrid' => $request->homework_result_id, 'req_id' => $_POST['request-id']);



    wp_redirect(locale_home_url() . '/?r=grade-homework&hrid=' . $request->homework_result_id);

    exit;
}



//math





$tab_title = __('Follow the steps to teach classes', 'iii-dictionary');

/* @var $tab_info_url type */
$tab_info_url = get_info_tab_cloud_url('//');

$is_teaching_agreement_agreed_math = ik_is_teaching_agreement_agreed("math");

$teacher_math_test_score_threshold = mw_get_option('teacher-math-test-score-threshold');



$math_filter['group_id'] = mw_get_option('teacher-math-test-group');

$math_filter['check_result'] = true;

$math_filter['user_id_result'] = $current_user->ID;



$math_tests = MWDB::get_homework_assignments($math_filter);





if (!empty($math_tests->items)) {

    $math_test1_result = MWDB::get_homework_results($math_tests->items[0]->id, $current_user->ID);

    $math_test2_result = MWDB::get_homework_results($math_tests->items[1]->id, $current_user->ID);
}

if (isset($_POST['take-math-test1']) || isset($_POST['take-math-test2'])) {

    if (!$is_mw_registered_math_teacher) {

        if (!$is_teaching_math_agreement_uptodate) {

            $title = __('Agreement Updated', 'iii-dictionary');

            $body = __('Our Agreement is updated, please agree to the new Agreement in order to continue.', 'iii-dictionary');
        }



        if (!$is_mw_registered_math_teacher) {

            $title = __('Registration Required', 'iii-dictionary');

            $body = __('Please register as math Teacher in order to access this page.', 'iii-dictionary');
        }

        set_lockpage_dialog($title, $body, $return_url);
    } else {

        MWDB::join_group($math_filter['group_id']);

        ik_clear_message_queue();



        if (!empty($math_tests->items)) {

            // store teacher tests in the session so we can check later

            $_SESSION['teacher_math_tests'] = array($math_tests->items[0]->id, $math_tests->items[1]->id);



            if (isset($_POST['take-math-test1'])) {



                $math_test_url = MWHtml::get_practice_page_url($math_tests->items[0]->assignment_id) . '&mode=homework&hid=' . $math_tests->items[0]->id;
            }

            if (isset($_POST['take-math-test2'])) {

                $math_test_url = MWHtml::get_practice_page_url($math_tests->items[1]->assignment_id) . '&mode=homework&hid=' . $math_tests->items[1]->id;
            }



            if (isset($math_test_url)) {

                wp_redirect($math_test_url);

                exit;
            }
        }
    }
}



// teacher want to re take test 1

if (isset($_POST['re-take-math-test1'])) {

    MWDB::delete_homework_result($math_test1_result[0]->homework_result_id);



    $math_test_url = MWHtml::get_practice_page_url($math_tests->items[0]->assignment_id) . '&mode=homework&hid=' . $math_tests->items[0]->id;

    wp_redirect($math_test_url);

    exit;
}



// teacher want to re take test 2

if (isset($_POST['re-take-math-test2'])) {

    MWDB::delete_homework_result($math_test2_result[0]->homework_result_id);



    $math_test_url = MWHtml::get_practice_page_url($math_tests->items[1]->assignment_id) . '&mode=homework&hid=' . $math_tests->items[1]->id;

    wp_redirect($math_test_url);

    exit;
}



// agree teaching agreement

if (isset($_POST['teaching-math-agree'])) {

    ik_agree_teaching_agreement(math);



    wp_redirect(locale_home_url() . '/?r=my-account');

    exit;
}

$current_page = max(1, get_query_var('page'));

$filter = get_page_filter_session();

if (empty($filter) && !isset($_POST['filter'])) {

    $filter['items_per_page'] = 20;

    $filter['offset'] = $filter['items_per_page'] * ($current_page - 1);
} else {

    if (isset($_POST['filter']['search'])) {

        $filter['grade'] = $_POST['filter']['grade'];
    }

    $filter['offset'] = $filter['items_per_page'] * ($current_page - 1);
}



set_page_filter_session($filter);





$filter['offset'] = 0;

$filter['items_per_page'] = 99999999;

$chat_requests = MWDB::get_chat_session_requests($filter, $filter['offset'], $filter['items_per_page']);

$total_pages = ceil($chat_requests->total / $filter['items_per_page']);



$pagination = paginate_links(array(
    'format' => '?page=%#%',
    'current' => $current_page,
    'total' => $total_pages
        ));
?>

<?php get_math_header($_page_title, 'red-brown') ?>


<div class="modal fade modal-signup" id="my-account-modal" role="dialog">

    <div class="modal-dialog modal-lg modal-signup">
        <div class="modal-content modal-content-signup">

            <div class="title-div">
                <img class="icon-close-classes-created ic-close7" data-dismiss="modal" src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Close.png">
                <img id="menu_Taggle" src="<?php echo get_template_directory_uri(); ?>/library/images/Menu_Taggle.png">
                <span class="modal-title text-uppercase">
                    <a href="#">
                        <img data-dismiss="modal" src="<?php echo get_template_directory_uri(); ?>/library/images/Logo_ikTeach.png">
                    </a>
                </span>
            </div>
            <hr style="margin-bottom: 0px;">
            <div class="modal-body-signup">
                <div class="section-right">
                    <!--                <h3>Update Info</h3>
                                    <h4>Login Info</h4>-->
                    <!--                    <form method="post" id="myForm" action="" name="registerform" enctype="multipart/form-data">
                                            <div class="row">
                                                <div class="col-sm-6 col-md-6">
                                                    <div class="form-group">
                                                        <label for="user_login"><?php _e('Username (e-mail address)', 'iii-dictionary') ?></label>
                                                        <input id="user_login" class="form-control" name="user_login" type="text" value="" required>
                                                    </div>
                                                </div>
                
                                                <div class="col-sm-6 col-md-6">
                                                    <div class="form-group">
                                                        <label for="password"><?php _e('Create Password', 'iii-dictionary') ?></label>
                                                        <input id="password" class="form-control" name="password" type="password" value="" required>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6 col-md-6">
                                                    <div class="form-group">
                                                        <label for="confirmpassword"><?php _e('Confirm Password', 'iii-dictionary') ?></label>
                                                        <input id="confirmpassword" class="form-control" name="confirm_password" type="password" value="" required>
                                                    </div>
                                                </div>
                
                                                <div class="col-sm-6 col-md-6">
                                                    <div class="form-group">
                                                        <label for="lastname"><?php _e('Last Name', 'iii-dictionary') ?></label>
                                                        <input id="lastname" class="form-control" name="last_name" type="text" value="" required>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6 col-md-6">
                                                    <div class="form-group">
                                                        <label for="firstname"><?php _e('First Name', 'iii-dictionary') ?></label>
                                                        <input id="firstname" class="form-control" name="first_name" type="text" value="" required>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6 col-md-6">
                                                    <div class="form-group">
                                                        <label><?php _e('Language', 'iii-dictionary') ?></label>
<?php MWHtml::language_type('en') ?>
                                                    </div>
                                                </div>
                                                <div class="col-sm-12 col-md-12">
                                                    <div class="form-group">
                                                        <label><?php _e('Date of Birth', 'iii-dictionary') ?> <small>(mm/dd/yyyy)</small></label>
                                                        <div class="row tiny-gutter">
                                                            <div class="col-xs-12 col-sm-4 col-md-4">
                                                                <select class="select-box-it form-control" name="birth-m">
                                                                    <option value="00">Month</option>
<?php for ($i = 1; $i <= 12; $i++) : ?>
    <?php $pad_str = str_pad($i, 2, '0', STR_PAD_LEFT) ?>
                                                                            <option value="<?php echo $pad_str ?>"><?php echo $pad_str ?></option>
<?php endfor ?>
                                                                </select>
                                                            </div>
                                                            <div class="col-xs-12 col-sm-4 col-md-4">
                                                                <select class="select-box-it form-control" name="birth-d">
                                                                    <option value="00">Day</option>
<?php for ($i = 1; $i <= 31; $i++) : ?>
    <?php $pad_str = str_pad($i, 2, '0', STR_PAD_LEFT) ?>
                                                                            <option value="<?php echo $pad_str ?>"><?php echo $pad_str ?></option>
<?php endfor ?>
                                                                </select>
                                                            </div>
                                                            <div class="col-xs-12 col-sm-4 col-md-4">
                                                                <input class="form-control" name="birth-y" type="text" value="" placeholder="Year" required>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                
                                                <div class="col-xs-12 col-sm-6 col-md-6">
                                                    <div class="form-group" style="height: 50px; margin-top: -20px;">
                                                        <label>&nbsp;</label>
                                                        <button class="btn-dark-blue" type="submit" name="wp-submit"><?php _e('Create Account', 'iii-dictionary') ?></button>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-6 col-md-6">
                                                    <div class="form-group">
                                                        <label>&nbsp;</label>
                                                        <a class="button-grey" data-dismiss="modal" type="submit" name="wp-submit"><?php _e('Cancel', 'iii-dictionary') ?></a>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>-->
                    <div class="tab-content">

                        <!--Content Create Account-->    
                        <div id="create-account" class="tab-pane fade in active">
                            <h3>Create Account</h3>

                            <form method="post" id="myForm" action="" name="registerform" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-sm-9 col-md-9">
                                        <div class="form-group">
                                            <label for="user_login"><?php _e('Username (E-mail Address)', 'iii-dictionary') ?></label>
                                            <input id="user_login" class="form-control" name="user_name" type="text" value="" required>
                                        </div>
                                    </div>
                                    <div class="col-sm-3 col-md-3">
                                        <div class="tooltip tooltip-manage-a-classroom col-xs-12 col-sm-12">
                                            <img style="height: 15px;" src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Questions.png"><a href="#">Find out availability</a>
                                            <!--								<div class="tooltip-div ">
                                                                                                                <div class="tp">
                                                                                                                        <ul>
                                                                                                                                <li>
                                                                                                                                        <span class="number-tp">1.</span>Create a Name for your Class.
                                                                                                                                </li>
                                                                                                                                <li>
                                                                                                                                        <span class="number-tp">2.</span>Give the class a name and password to your students to join the class.
                                                                                                                                </li>
                                                                                                                                <li><span class="number-tp">3.</span>Go to Homework Assignment under "Teacher" and select a worksheet and send it to the group as the homework assignment.</li>
                                                                                                                                <li><span class="number-tp">4.</span>Homework completed by students is auto-graded.</li>
                                                                                                                                <li><span class="number-tp">5.</span>View the homework results at the <span class="underline">Manage your class.</span></li>
                                                                                                                        </ul>
                                                                                                                </div>
                                                                                                        </div>-->
                                        </div>
                                    </div>

                                    <div class="col-sm-6 col-md-6">
                                        <div class="form-group">
                                            <label for="password"><?php _e('Create Password', 'iii-dictionary') ?></label>
                                            <input id="password" class="form-control" name="password" type="password" value="" required>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6">
                                        <div class="form-group">
                                            <label for="confirmpassword"><?php _e('Confirm Password', 'iii-dictionary') ?></label>
                                            <input id="confirmpassword" class="form-control" name="confirm_password" type="password" value="" required>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6">
                                        <div class="form-group">
                                            <label for="firstname"><?php _e('First Name', 'iii-dictionary') ?></label>
                                            <input id="firstname" class="form-control" name="first_name" type="text" value="" required>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6">
                                        <div class="form-group">
                                            <label for="lastname"><?php _e('Last Name', 'iii-dictionary') ?></label>
                                            <input id="lastname" class="form-control" name="last_name" type="text" value="" required>
                                        </div>
                                    </div>

                                    <div class="col-sm-12 col-md-12" >
                                        <div class="form-group">
                                            <label><?php _e('Date of Birth', 'iii-dictionary') ?> <small>(month/day/year)</small></label>
                                            <div class="row tiny-gutter">
                                                <div class="col-xs-12 col-sm-4 col-md-4" id="month">
                                                    <select class="select-box-it form-control" name="birth-m">

<?php for ($i = 1; $i <= 12; $i++) : ?>
    <?php $pad_str = str_pad($i, 2, '0', STR_PAD_LEFT) ?>
                                                            <option value="<?php echo $pad_str ?>"><?php echo $pad_str ?></option>
<?php endfor ?>
                                                    </select>
                                                </div>
                                                <div class="col-xs-12 col-sm-4 col-md-4" id="date">
                                                    <select class="select-box-it form-control" name="birth-d">

<?php for ($i = 1; $i <= 31; $i++) : ?>
    <?php $pad_str = str_pad($i, 2, '0', STR_PAD_LEFT) ?>
                                                            <option value="<?php echo $pad_str ?>"><?php echo $pad_str ?></option>
<?php endfor ?>
                                                    </select>
                                                </div>
                                                <div class="col-xs-12 col-sm-4 col-md-4">
                                                    <input class="form-control" name="birth-y" type="text" value="" placeholder="Year" required>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-12 col-md-12">
                                        <label><?php _e('Language', 'iii-dictionary') ?></label>
                                        <div class="form__boolean" id="checkBoxSearch" style="margin-bottom: 10px;">
                                            <div class="col-md-2 col-xs-4 cb-type">
                                                <label>
                                                    <input type="checkbox" class="radio_buttons required class_cb_search option-input radio" name="cb-eng"/>
                                                    English
                                                </label>
                                            </div>
                                            <div class="col-md-2 col-xs-4 cb-type">
                                                <label>
                                                    <input type="checkbox"  checked class="radio_buttons required class_cb_search option-input radio" name="cb-japan"/>
                                                    Japanese
                                                </label>
                                            </div>
                                            <div class="col-md-2 col-xs-4 cb-type">
                                                <label>
                                                    <input type="checkbox"  class="radio_buttons required class_cb_search option-input radio" name="cb-korea"/>
                                                    Korean
                                                </label>
                                            </div>
                                            <div class="col-md-2 col-xs-4 cb-type">
                                                <label>
                                                    <input type="checkbox" class="radio_buttons required class_cb_search option-input radio" name="cb-chinese"/>
                                                    Chinese
                                                </label>
                                            </div>
                                            <div class="col-md-2 col-xs-4 cb-type">
                                                <label>
                                                    <input type="checkbox"  class="radio_buttons required class_cb_search option-input radio" name="cb-trachinese"/>
                                                    Traditional Chinese
                                                </label>
                                            </div>
                                            <div class="col-md-2 col-xs-4 cb-type" style="padding-left: 30px !important;">
                                                <label>
                                                    <input type="checkbox"  class="radio_buttons required class_cb_search option-input radio" name="cb-vietnam"/>
                                                    Vietnamese
                                                </label>
                                            </div>
                                        </div>
                                        <!--                                        <div class="form-group">
                                                                                    <label><?php _e('Language', 'iii-dictionary') ?></label>
<?php MWHtml::language_type('en') ?>
                                                                                </div>-->
                                    </div>
                                    <div class="col-xs-12 col-sm-8 col-md-8">
                                        <div class="form-group" style="height: 50px; margin-top: -20px;">
                                            <label>&nbsp;</label>
                                            <button class="btn-dark-blue" style="background: #f7b555;" type="submit" name="wp-submit"><?php _e('Create Account', 'iii-dictionary') ?></button>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-4 col-md-4">
                                        <div class="form-group">
                                            <label>&nbsp;</label>
                                            <a class="button-grey" style="background: #cecece; margin-top: 5px;" data-dismiss="modal" type="submit" name="wp-submit"><?php _e('Cancel', 'iii-dictionary') ?></a>
                                        </div>
                                    </div>
                                    <div id="tutor-regis" class="col-md-12">
                                        <h3>Tutor Registration</h3>
                                        <hr style="color: #d6d6d6;">
                                        <div id="step4-collapse">

                                            <div class="row">

                                              
<?php
$is_teaching_agreement_uptodate_math = ik_is_teacher_agreement_uptodate('MATH');

$is_teaching_agreement_uptodate = ik_is_teacher_agreement_uptodate();
?>

                                                <div class="col-sm-12">

                                                    <div class="form-group box_english">

                                                        <div class="box-dis"  style="max-height: 200px;">

<?php echo mw_get_option('registration-agreement') ?>


                                                        </div>
                                                        <hr style="color: #d6d6d6;">
                                                        
                                                        <div class="col-sm-12 col-xs-6 col-md-12 agree">
                                                            <div class="form-group">

                                                                <input id="rdo-agreed" class="checkboxagree" <?php if ($is_teaching_agreement_uptodate) echo 'checked'; ?> type="checkbox" name="agree-english-teacher" value="1" >
                                                                <label for="rdo-agreed" style="font-size: 15px !important; margin-bottom: 0px !important;">I agree to the terms and conditions</label>
                                                            </div>
                                                            <hr style="color: #d6d6d6;">
                                                        </div>
                                                        <div id="info">
                                                            <h3>Personal Information</h3>
                                                            <div class="col-sm-6 col-md-6 col-xs-12">

                                                                <div class="form-group">

                                                                    <label><?php _e('Mobile Phone Number', 'iii-dictionary') ?></label>

                                                                    <input type="text" class="form-control" name="mobile-number" value="<?php
                                                                    echo get_user_meta($current_user->ID, 'mobile_number', true);

                                                                    ;
                                                                    ?>">

                                                                </div>

                                                            </div>
                                                            <div class="col-sm-6 col-md-6 col-xs-12">

                                                                <div class="form-group">

                                                                    <label><?php _e('Payment Email (for Paypal)', 'iii-dictionary') ?></label>

                                                                    <input type="text" class="form-control" name="email-paypal" value="<?php echo get_user_meta($current_user->ID, 'email_paypal', true); ?>" required>

                                                                </div>

                                                            </div>

                                                            <div class="col-sm-6 col-md-6 col-xs-12">

                                                                <div class="form-group">

                                                                    <label><?php _e('Last School You Attended', 'iii-dictionary') ?></label>

                                                                    <input type="text" class="form-control" name="last-school" value="<?php echo get_user_meta($current_user->ID, 'last_school', true); ?>" required>

                                                                </div>

                                                            </div>

                                                            <div class="col-sm-6 col-md-6 col-xs-12">

                                                                <div class="form-group">

                                                                    <label><?php _e('School You Tought (if any)', 'iii-dictionary') ?></label>

                                                                    <input type="text" class="form-control" name="previous-school" value="<?php echo get_user_meta($current_user->ID, 'previous_school', true); ?>" required>

                                                                </div>

                                                            </div>
                                                            <div class="col-sm-6 col-md-6 col-xs-12">

                                                                <div class="form-group">

                                                                    <label><?php _e('Skype ID (for interview)', 'iii-dictionary') ?></label>

                                                                    <input type="text" class="form-control" name="skype" value="<?php echo get_user_meta($current_user->ID, 'previous_school', true); ?>" required>

                                                                </div>

                                                            </div>
                                                            <div class="col-sm-6 col-md-6 col-xs-12">

                                                                <div class="form-group">

                                                                    <label><?php _e('Current Profession', 'iii-dictionary') ?></label>

                                                                    <input type="text" class="form-control" name="profession" value="<?php echo get_user_meta($current_user->ID, 'previous_school', true); ?>" required>

                                                                </div>

                                                            </div>

                                                        </div>
<!--
                                                        <div class="col-xs-8 col-sm-5">

                                                            <div class="form-group">

                                                                <label>&nbsp;</label>

                                                                <button type="button" class="btn btn-default btn-block orange form-control r_agree_english" name="ie-agree" ><span <?php
if ($is_teaching_agreement_uptodate) {

    echo 'class="icon-check"';
} else {

    echo 'class="icon-cancel"';
}
?>></span> <?php ($is_teaching_agreement_uptodate) ? _e('AGREED', 'iii-dictionary') : _e('CLICK TO AGREE', 'iii-dictionary') ?></button>

                                                            </div>

                                                        </div>-->

                                                    </div>
                                                    <div id="totutor" class="col-md-12">
                                                        <h3>What are you going to tutor?</h3>
                                                        <div class="col-md-12 ">
                                                            <div class="form-group">
                                                                <input id="eng-writing" data-target="#extend-tutor" data-toggle="collapse" type="checkbox" class="radio_buttons required class_cb_search option-input radio" name="eng-writing"/>
                                                                <label style="font-size: 15px !important; margin-bottom: 0px !important; margin-left: 5px;">English (Writing Only)</label>
                                                                <hr style="color: #d6d6d6;">
                                                            </div>
                                                            <div id="extend-tutor" class="collapse">
                                                        <div class="teach-ex col-md-12">
                                                            <h4>Teaching experience in school</h4>
                                                              <div class="col-sm-6 col-md-6 col-xs-12">

                                                                <div class="form-group">

                                                                    <label><?php _e('School Name', 'iii-dictionary') ?></label>

                                                                    <input type="text" class="form-control" name="school-name" value="" required>

                                                                </div>

                                                            </div>
                                                            <div class="col-sm-6 col-md-6 col-xs-12">

                                                                <div class="form-group">

                                                                    <label><?php _e('School Website link', 'iii-dictionary') ?></label>

                                                                    <input type="text" class="form-control" name="website-link" value="" required>

                                                                </div>

                                                            </div>
                                                            <div class="col-sm-6 col-md-6 col-xs-12">

                                                                <div class="form-group">

                                                                    <label><?php _e('School Phone Number', 'iii-dictionary') ?></label>

                                                                    <input type="text" class="form-control" name="phone" value="" required>

                                                                </div>

                                                            </div>
                                                            <div class="col-sm-6 col-md-6 col-xs-12">

                                                                <div class="form-group">

                                                                    <label><?php _e('Major to Teach (grade, subject)', 'iii-dictionary') ?></label>

                                                                    <input type="text" class="form-control" name="major-teach" value="" required>

                                                                </div>

                                                            </div>
                                                           
                                                            <div class="col-md-6">
                                                                <label class="col-md-12"><?php _e('Year of Teaching', 'iii-dictionary') ?></label>
                                                                <div class="form-group col-md-5">
                                                                   
                                                                    <input type="text" class="form-control" name="from-year" value="" required>

                                                                </div>
                                                                <label class="col-md-2" style="padding: 10px 20px !important;"><?php _e('to', 'iii-dictionary') ?></label>
                                                               <div class="form-group col-md-5">
                                                                   
                                                                    <input type="text" class="form-control" name="to-year" value="" required>

                                                               </div>

                                                            </div>
                                                            
                                                        </div>
                                                        <div class="teach-ex col-md-12">
                                                            <h4>Teaching experience as a Student</h4>
                                                              <div class="col-sm-6 col-md-6 col-xs-12">

                                                                <div class="form-group">

                                                                    <label><?php _e('School Currently Attending to', 'iii-dictionary') ?></label>

                                                                    <input type="text" class="form-control" name="school-attend" value="" required>

                                                                </div>

                                                            </div>
                                                            <div class="col-sm-6 col-md-6 col-xs-12">

                                                                <div class="form-group">

                                                                    <label><?php _e('School Website link', 'iii-dictionary') ?></label>

                                                                    <input type="text" class="form-control" name="website-link" value="" required>

                                                                </div>

                                                            </div>
                                                            
                                                            
                                                           
                                                            <div class="col-md-6" style="padding: 0px !important;">
                                                                
                                                                <div class="form-group col-md-6">
                                                                   <label><?php _e('Grade', 'iii-dictionary') ?></label>
                                                                   <select class="select-box-it form-control" name="birth-m">
                                                                       <option value="1">Grade</option>
                                                                       <option value="2">Junior</option>

                                                                   </select>

                                                                </div>
                                                                
                                                               <div class="form-group col-md-6">
                                                                   <label><?php _e('GPA', 'iii-dictionary') ?></label>
                                                                    <input type="text" class="form-control" name="gpa" value="" required>

                                                               </div>

                                                            </div>
                                                            <div class="col-sm-6 col-md-6 col-xs-12">
                                                                <div class="form-group">
                                                                    <label><?php _e('Major', 'iii-dictionary') ?></label>
                                                                    <input type="text" class="form-control" name="major" value="" required>
                                                                </div>

                                                            </div>
                                                            
                                                        </div>
                                                        <div class="teach-ex col-md-12">
                                                            <h4>Other Qualification (other than teacher and student)</h4>
                                                              <div class="col-sm-6 col-md-6 col-xs-12">

                                                                <div class="form-group">

                                                                    <label><?php _e('Your Academic Qualification', 'iii-dictionary') ?></label>

                                                                    <input type="text" class="form-control" name="qualification" value="" required>

                                                                </div>

                                                            </div>
                                                            <div class="col-sm-6 col-md-6 col-xs-12">

                                                                <div class="form-group">

                                                                    <label><?php _e('Website link (if any)', 'iii-dictionary') ?></label>

                                                                    <input type="text" class="form-control" name="website-link" value="" required>

                                                                </div>

                                                            </div>
                                                            <div class="col-sm-3 col-md-3 col-xs-12">

                                                                <div class="form-group">

                                                                    <label><?php _e('Years of Experience', 'iii-dictionary') ?></label>

                                                                    <input type="text" class="form-control" name="experience" value="" required>

                                                                </div>

                                                            </div>
                                                            <div class="col-sm-9 col-md-9 col-xs-12">

                                                                <div class="form-group">

                                                                    <label><?php _e('Any Other Qualification Information', 'iii-dictionary') ?></label>

                                                                    <input type="text" class="form-control" name="other-qualification" value="" required>

                                                                </div>

                                                            </div>
                                                           
                                                            
                                                            
                                                        </div>
                                                    </div>
                                                          
                                                        </div>
                                                        <div class="col-md-12 ">
                                                            <div class="form-group">
                                                                <input type="checkbox" data-target="#extend-tutor2" data-toggle="collapse" class="radio_buttons required class_cb_search option-input radio" name="eng-conver"/>
                                                                <label style="font-size: 15px !important; margin-bottom: 0px !important; margin-left: 5px;">English (Conversation Only)</label>
                                                                <hr style="color: #d6d6d6;">
                                                            </div>
                                                            <div id="extend-tutor2" class="collapse">
                                                        <div class="teach-ex col-md-12">
                                                            <h4>Teaching experience in school</h4>
                                                              <div class="col-sm-6 col-md-6 col-xs-12">

                                                                <div class="form-group">

                                                                    <label><?php _e('School Name', 'iii-dictionary') ?></label>

                                                                    <input type="text" class="form-control" name="school-name" value="" required>

                                                                </div>

                                                            </div>
                                                            <div class="col-sm-6 col-md-6 col-xs-12">

                                                                <div class="form-group">

                                                                    <label><?php _e('School Website link', 'iii-dictionary') ?></label>

                                                                    <input type="text" class="form-control" name="website-link" value="" required>

                                                                </div>

                                                            </div>
                                                            <div class="col-sm-6 col-md-6 col-xs-12">

                                                                <div class="form-group">

                                                                    <label><?php _e('School Phone Number', 'iii-dictionary') ?></label>

                                                                    <input type="text" class="form-control" name="phone" value="" required>

                                                                </div>

                                                            </div>
                                                            <div class="col-sm-6 col-md-6 col-xs-12">

                                                                <div class="form-group">

                                                                    <label><?php _e('Major to Teach (grade, subject)', 'iii-dictionary') ?></label>

                                                                    <input type="text" class="form-control" name="major-teach" value="" required>

                                                                </div>

                                                            </div>
                                                           
                                                            <div class="col-md-6">
                                                                <label class="col-md-12"><?php _e('Year of Teaching', 'iii-dictionary') ?></label>
                                                                <div class="form-group col-md-5">
                                                                   
                                                                    <input type="text" class="form-control" name="from-year" value="" required>

                                                                </div>
                                                                <label class="col-md-2" style="padding: 10px 20px !important;"><?php _e('to', 'iii-dictionary') ?></label>
                                                               <div class="form-group col-md-5">
                                                                   
                                                                    <input type="text" class="form-control" name="to-year" value="" required>

                                                               </div>

                                                            </div>
                                                            
                                                        </div>
                                                        <div class="teach-ex col-md-12">
                                                            <h4>Teaching experience as a Student</h4>
                                                              <div class="col-sm-6 col-md-6 col-xs-12">

                                                                <div class="form-group">

                                                                    <label><?php _e('School Currently Attending to', 'iii-dictionary') ?></label>

                                                                    <input type="text" class="form-control" name="school-attend" value="" required>

                                                                </div>

                                                            </div>
                                                            <div class="col-sm-6 col-md-6 col-xs-12">

                                                                <div class="form-group">

                                                                    <label><?php _e('School Website link', 'iii-dictionary') ?></label>

                                                                    <input type="text" class="form-control" name="website-link" value="" required>

                                                                </div>

                                                            </div>
                                                            
                                                            
                                                           
                                                            <div class="col-md-6" style="padding: 0px !important;">
                                                                
                                                                <div class="form-group col-md-6">
                                                                   <label><?php _e('Grade', 'iii-dictionary') ?></label>
                                                                   <select class="select-box-it form-control" name="birth-m">
                                                                       <option value="1">Grade</option>
                                                                       <option value="2">Junior</option>

                                                                   </select>

                                                                </div>
                                                                
                                                               <div class="form-group col-md-6">
                                                                   <label><?php _e('GPA', 'iii-dictionary') ?></label>
                                                                    <input type="text" class="form-control" name="gpa" value="" required>

                                                               </div>

                                                            </div>
                                                            <div class="col-sm-6 col-md-6 col-xs-12">
                                                                <div class="form-group">
                                                                    <label><?php _e('Major', 'iii-dictionary') ?></label>
                                                                    <input type="text" class="form-control" name="major" value="" required>
                                                                </div>

                                                            </div>
                                                            
                                                        </div>
                                                        <div class="teach-ex col-md-12">
                                                            <h4>Other Qualification (other than teacher and student)</h4>
                                                              <div class="col-sm-6 col-md-6 col-xs-12">

                                                                <div class="form-group">

                                                                    <label><?php _e('Your Academic Qualification', 'iii-dictionary') ?></label>

                                                                    <input type="text" class="form-control" name="qualification" value="" required>

                                                                </div>

                                                            </div>
                                                            <div class="col-sm-6 col-md-6 col-xs-12">

                                                                <div class="form-group">

                                                                    <label><?php _e('Website link (if any)', 'iii-dictionary') ?></label>

                                                                    <input type="text" class="form-control" name="website-link" value="" required>

                                                                </div>

                                                            </div>
                                                            <div class="col-sm-3 col-md-3 col-xs-12">

                                                                <div class="form-group">

                                                                    <label><?php _e('Years of Experience', 'iii-dictionary') ?></label>

                                                                    <input type="text" class="form-control" name="experience" value="" required>

                                                                </div>

                                                            </div>
                                                            <div class="col-sm-9 col-md-9 col-xs-12">

                                                                <div class="form-group">

                                                                    <label><?php _e('Any Other Qualification Information', 'iii-dictionary') ?></label>

                                                                    <input type="text" class="form-control" name="other-qualification" value="" required>

                                                                </div>

                                                            </div>
                                                           
                                                            
                                                            
                                                        </div>
                                                    </div>
                                                          
                                                        </div>
                                                        <div class="col-md-12 ">
                                                            <div class="form-group">
                                                                <input type="checkbox" data-target="#extend-tutor3" data-toggle="collapse" class="radio_buttons required class_cb_search option-input radio" name="eng-middle"/>
                                                                <label style="font-size: 15px !important; margin-bottom: 0px !important; margin-left: 5px;">Math (Up to Middle School)</label>
                                                                <hr style="color: #d6d6d6;">
                                                            </div>
                                                            <div id="extend-tutor3" class="collapse">
                                                        <div class="teach-ex col-md-12">
                                                            <h4>Teaching experience in school</h4>
                                                              <div class="col-sm-6 col-md-6 col-xs-12">

                                                                <div class="form-group">

                                                                    <label><?php _e('School Name', 'iii-dictionary') ?></label>

                                                                    <input type="text" class="form-control" name="school-name" value="" required>

                                                                </div>

                                                            </div>
                                                            <div class="col-sm-6 col-md-6 col-xs-12">

                                                                <div class="form-group">

                                                                    <label><?php _e('School Website link', 'iii-dictionary') ?></label>

                                                                    <input type="text" class="form-control" name="website-link" value="" required>

                                                                </div>

                                                            </div>
                                                            <div class="col-sm-6 col-md-6 col-xs-12">

                                                                <div class="form-group">

                                                                    <label><?php _e('School Phone Number', 'iii-dictionary') ?></label>

                                                                    <input type="text" class="form-control" name="phone" value="" required>

                                                                </div>

                                                            </div>
                                                            <div class="col-sm-6 col-md-6 col-xs-12">

                                                                <div class="form-group">

                                                                    <label><?php _e('Major to Teach (grade, subject)', 'iii-dictionary') ?></label>

                                                                    <input type="text" class="form-control" name="major-teach" value="" required>

                                                                </div>

                                                            </div>
                                                           
                                                            <div class="col-md-6">
                                                                <label class="col-md-12"><?php _e('Year of Teaching', 'iii-dictionary') ?></label>
                                                                <div class="form-group col-md-5">
                                                                   
                                                                    <input type="text" class="form-control" name="from-year" value="" required>

                                                                </div>
                                                                <label class="col-md-2" style="padding: 10px 20px !important;"><?php _e('to', 'iii-dictionary') ?></label>
                                                               <div class="form-group col-md-5">
                                                                   
                                                                    <input type="text" class="form-control" name="to-year" value="" required>

                                                               </div>

                                                            </div>
                                                            
                                                        </div>
                                                        <div class="teach-ex col-md-12">
                                                            <h4>Teaching experience as a Student</h4>
                                                              <div class="col-sm-6 col-md-6 col-xs-12">

                                                                <div class="form-group">

                                                                    <label><?php _e('School Currently Attending to', 'iii-dictionary') ?></label>

                                                                    <input type="text" class="form-control" name="school-attend" value="" required>

                                                                </div>

                                                            </div>
                                                            <div class="col-sm-6 col-md-6 col-xs-12">

                                                                <div class="form-group">

                                                                    <label><?php _e('School Website link', 'iii-dictionary') ?></label>

                                                                    <input type="text" class="form-control" name="website-link" value="" required>

                                                                </div>

                                                            </div>
                                                            
                                                            
                                                           
                                                            <div class="col-md-6" style="padding: 0px !important;">
                                                                
                                                                <div class="form-group col-md-6">
                                                                   <label><?php _e('Grade', 'iii-dictionary') ?></label>
                                                                   <select class="select-box-it form-control" name="birth-m">
                                                                       <option value="1">Grade</option>
                                                                       <option value="2">Junior</option>

                                                                   </select>

                                                                </div>
                                                                
                                                               <div class="form-group col-md-6">
                                                                   <label><?php _e('GPA', 'iii-dictionary') ?></label>
                                                                    <input type="text" class="form-control" name="gpa" value="" required>

                                                               </div>

                                                            </div>
                                                            <div class="col-sm-6 col-md-6 col-xs-12">
                                                                <div class="form-group">
                                                                    <label><?php _e('Major', 'iii-dictionary') ?></label>
                                                                    <input type="text" class="form-control" name="major" value="" required>
                                                                </div>

                                                            </div>
                                                            
                                                        </div>
                                                        <div class="teach-ex col-md-12">
                                                            <h4>Other Qualification (other than teacher and student)</h4>
                                                              <div class="col-sm-6 col-md-6 col-xs-12">

                                                                <div class="form-group">

                                                                    <label><?php _e('Your Academic Qualification', 'iii-dictionary') ?></label>

                                                                    <input type="text" class="form-control" name="qualification" value="" required>

                                                                </div>

                                                            </div>
                                                            <div class="col-sm-6 col-md-6 col-xs-12">

                                                                <div class="form-group">

                                                                    <label><?php _e('Website link (if any)', 'iii-dictionary') ?></label>

                                                                    <input type="text" class="form-control" name="website-link" value="" required>

                                                                </div>

                                                            </div>
                                                            <div class="col-sm-3 col-md-3 col-xs-12">

                                                                <div class="form-group">

                                                                    <label><?php _e('Years of Experience', 'iii-dictionary') ?></label>

                                                                    <input type="text" class="form-control" name="experience" value="" required>

                                                                </div>

                                                            </div>
                                                            <div class="col-sm-9 col-md-9 col-xs-12">

                                                                <div class="form-group">

                                                                    <label><?php _e('Any Other Qualification Information', 'iii-dictionary') ?></label>

                                                                    <input type="text" class="form-control" name="other-qualification" value="" required>

                                                                </div>

                                                            </div>
                                                           
                                                            
                                                            
                                                        </div>
                                                    </div>
                                                          
                                                        </div>
                                                        <div class="col-md-12 " style="margin-bottom: 30px;">
                                                            <div class="form-group">
                                                                <input type="checkbox" data-target="#extend-tutor4" data-toggle="collapse" class="radio_buttons required class_cb_search option-input radio" name="math-any"/>
                                                                <label style="font-size: 15px !important; margin-bottom: 0px !important; margin-left: 5px;">Math (Any Level)</label>
                                                               
                                                            </div>
                                                            <div id="extend-tutor4" class="collapse">
                                                        <div class="teach-ex col-md-12">
                                                            <h4>Teaching experience in school</h4>
                                                              <div class="col-sm-6 col-md-6 col-xs-12">

                                                                <div class="form-group">

                                                                    <label><?php _e('School Name', 'iii-dictionary') ?></label>

                                                                    <input type="text" class="form-control" name="school-name" value="" required>

                                                                </div>

                                                            </div>
                                                            <div class="col-sm-6 col-md-6 col-xs-12">

                                                                <div class="form-group">

                                                                    <label><?php _e('School Website link', 'iii-dictionary') ?></label>

                                                                    <input type="text" class="form-control" name="website-link" value="" required>

                                                                </div>

                                                            </div>
                                                            <div class="col-sm-6 col-md-6 col-xs-12">

                                                                <div class="form-group">

                                                                    <label><?php _e('School Phone Number', 'iii-dictionary') ?></label>

                                                                    <input type="text" class="form-control" name="phone" value="" required>

                                                                </div>

                                                            </div>
                                                            <div class="col-sm-6 col-md-6 col-xs-12">

                                                                <div class="form-group">

                                                                    <label><?php _e('Major to Teach (grade, subject)', 'iii-dictionary') ?></label>

                                                                    <input type="text" class="form-control" name="major-teach" value="" required>

                                                                </div>

                                                            </div>
                                                           
                                                            <div class="col-md-6">
                                                                <label class="col-md-12"><?php _e('Year of Teaching', 'iii-dictionary') ?></label>
                                                                <div class="form-group col-md-5">
                                                                   
                                                                    <input type="text" class="form-control" name="from-year" value="" required>

                                                                </div>
                                                                <label class="col-md-2" style="padding: 10px 20px !important;"><?php _e('to', 'iii-dictionary') ?></label>
                                                               <div class="form-group col-md-5">
                                                                   
                                                                    <input type="text" class="form-control" name="to-year" value="" required>

                                                               </div>

                                                            </div>
                                                            
                                                        </div>
                                                        <div class="teach-ex col-md-12">
                                                            <h4>Teaching experience as a Student</h4>
                                                              <div class="col-sm-6 col-md-6 col-xs-12">

                                                                <div class="form-group">

                                                                    <label><?php _e('School Currently Attending to', 'iii-dictionary') ?></label>

                                                                    <input type="text" class="form-control" name="school-attend" value="" required>

                                                                </div>

                                                            </div>
                                                            <div class="col-sm-6 col-md-6 col-xs-12">

                                                                <div class="form-group">

                                                                    <label><?php _e('School Website link', 'iii-dictionary') ?></label>

                                                                    <input type="text" class="form-control" name="website-link" value="" required>

                                                                </div>

                                                            </div>
                                                            
                                                            
                                                           
                                                            <div class="col-md-6" style="padding: 0px !important;">
                                                                
                                                                <div class="form-group col-md-6">
                                                                   <label><?php _e('Grade', 'iii-dictionary') ?></label>
                                                                   <select class="select-box-it form-control" name="birth-m">
                                                                       <option value="1">Grade</option>
                                                                       <option value="2">Junior</option>

                                                                   </select>

                                                                </div>
                                                                
                                                               <div class="form-group col-md-6">
                                                                   <label><?php _e('GPA', 'iii-dictionary') ?></label>
                                                                    <input type="text" class="form-control" name="gpa" value="" required>

                                                               </div>

                                                            </div>
                                                            <div class="col-sm-6 col-md-6 col-xs-12">
                                                                <div class="form-group">
                                                                    <label><?php _e('Major', 'iii-dictionary') ?></label>
                                                                    <input type="text" class="form-control" name="major" value="" required>
                                                                </div>

                                                            </div>
                                                            
                                                        </div>
                                                        <div class="teach-ex col-md-12">
                                                            <h4>Other Qualification (other than teacher and student)</h4>
                                                              <div class="col-sm-6 col-md-6 col-xs-12">

                                                                <div class="form-group">

                                                                    <label><?php _e('Your Academic Qualification', 'iii-dictionary') ?></label>

                                                                    <input type="text" class="form-control" name="qualification" value="" required>

                                                                </div>

                                                            </div>
                                                            <div class="col-sm-6 col-md-6 col-xs-12">

                                                                <div class="form-group">

                                                                    <label><?php _e('Website link (if any)', 'iii-dictionary') ?></label>

                                                                    <input type="text" class="form-control" name="website-link" value="" required>

                                                                </div>

                                                            </div>
                                                            <div class="col-sm-3 col-md-3 col-xs-12">

                                                                <div class="form-group">

                                                                    <label><?php _e('Years of Experience', 'iii-dictionary') ?></label>

                                                                    <input type="text" class="form-control" name="experience" value="" required>

                                                                </div>

                                                            </div>
                                                            <div class="col-sm-9 col-md-9 col-xs-12">

                                                                <div class="form-group">

                                                                    <label><?php _e('Any Other Qualification Information', 'iii-dictionary') ?></label>

                                                                    <input type="text" class="form-control" name="other-qualification" value="" required>

                                                                </div>

                                                            </div>
                                                           
                                                            
                                                            
                                                        </div>
                                                    </div>
                                                          
                                                        </div>
                                                        <div class="col-xs-12 col-sm-8 col-md-8" style="margin-bottom: 30px;">
                                                            <div class="form-group" style="height: 50px; margin-top: -20px;">
                                                                <label>&nbsp;</label>
                                                                <button class="btn-dark-blue" style="background: #f7b555;" type="submit" name="send-tutor"><?php _e('Send Tutoring Request', 'iii-dictionary') ?></button>
                                                            </div>
                                                        </div>
                                                        <div class="col-xs-12 col-sm-4 col-md-4" style="margin-bottom: 30px;">
                                                            <div class="form-group">
                                                                <label>&nbsp;</label>
                                                                <a class="button-grey" style="background: #cecece; margin-top: 5px;" data-dismiss="modal" type="submit" name="cancel"><?php _e('Cancel', 'iii-dictionary') ?></a>
                                                            </div>
                                                        </div>
                                                        
                                                        
                                                    </div>
                                                    
<!--
                                                    <div class="form-group box_math" style="display:none">

                                                        <div class="box box-red " >

                                                            <div class="scroll-list" style="max-height: 200px; color: #fff">

<?php echo mw_get_option('math-registration-agreement') ?>

                                                            </div>

                                                        </div>

                                                        <div class="col-sm-6 col-xs-2"></div>

                                                        <div class="col-xs-2 col-sm-1">

                                                            <div class="form-group">

                                                                <label>&nbsp;</label>

                                                                <div>

                                                                    <input id="rdo-agreed-math" class="checkboxagree" <?php if ($is_teaching_agreement_uptodate_math) echo 'checked'; ?> type="checkbox" name="agree-math-teacher" value="2">



                                                                </div>

                                                            </div>

                                                        </div>

                                                        <div class="col-xs-8 col-sm-5">

                                                            <div class="form-group">

                                                                <label>&nbsp;</label>

                                                                <button type="button" class="btn btn-default btn-block orange form-control r_agree_math" name="im-agree" id="i-agree"><span class="<?php
                                                                    if ($is_teaching_agreement_uptodate_math) {

                                                                        echo 'icon-check';
                                                                    } else {

                                                                        echo 'icon-cancel';
                                                                    }
                                                                    ?>"></span> <?php ($is_teaching_agreement_uptodate_math) ? _e('AGREED', 'iii-dictionary') : _e('CLICK TO AGREE', 'iii-dictionary') ?></button>

                                                            </div>

                                                        </div>

                                                    </div>-->

                                                </div>

                                            </div>

<!--                                            <div class="row">

                                                <div class="col-sm-4">

                                                    <div class="form-group">

                                                        <table class="table profile-picture-form">

                                                            <tr>

                                                                <td class="profile-picture">

                                                                    <div id="profile-picture">

<?php
$user_avatar = ik_get_user_avatar($current_user->ID);

if (!empty($user_avatar)) :
    ?>

                                                                            <img src="<?php echo $user_avatar ?>" width="100" height="100" alt="<?php echo $current_user->display_name ?>">

    <?php
else :

    echo get_avatar($current_user->ID, 120);

endif
?>

                                                                    </div>

                                                                </td>

                                                                <td class="upload-block">

                                                                    <label><?php _e('Profile Picture', 'iii-dictionary') ?></label><br>

                                                                    <span class="btn btn-default grey btn-file">

                                                                        <span class="icon-browse"></span><?php _e('Browse', 'iii-dictionary') ?> <input name="input-image" id="input-image" type="file">

                                                                    </span>

                                                                </td>

                                                            </tr>

                                                        </table>

                                                    </div>

                                                </div>

                                                <div class="col-md-8">

                                                    <div class="form-group">

                                                        <label><?php _e('Which language you use to teach', 'iii-dictionary') ?></label>

                                                        <div class="row">

<?php
$language_teach = get_user_meta($current_user->ID, 'language_teach', true);

$langs = explode(',', $language_teach);
?>

                                                            <div class="col-md-6">

                                                                <label class="check_lb <?php if (in_array('en', $langs)) echo 'checked_lb'; ?>"><input type="checkbox" <?php if (in_array('en', $langs)) echo 'checked'; ?> name="language_teach[]" value="en"> <?php _e('English', 'iii-dictionary'); ?></label><br>

                                                                <label class="check_lb <?php if (in_array('ja', $langs)) echo 'checked_lb'; ?>"><input type="checkbox" <?php if (in_array('ja', $langs)) echo 'checked'; ?> name="language_teach[]" value="ja"> <?php _e('Japanese', 'iii-dictionary'); ?></label><br>

                                                                <label class="check_lb <?php if (in_array('ko', $langs)) echo 'checked_lb'; ?>"><input type="checkbox" <?php if (in_array('ko', $langs)) echo 'checked'; ?> name="language_teach[]" value="ko"> <?php _e('Korean', 'iii-dictionary'); ?></label><br>

                                                            </div>

                                                            <div class="col-md-6">

                                                                <label class="check_lb <?php if (in_array('zh', $langs)) echo 'checked_lb'; ?>"><input type="checkbox" <?php if (in_array('zh', $langs)) echo 'checked'; ?> name="language_teach[]" value="zh"> <?php _e('Chinese', 'iii-dictionary'); ?></label><br>

                                                                <label class="check_lb <?php if (in_array('zh-tw', $langs)) echo 'checked_lb'; ?>"><input type="checkbox" <?php if (in_array('zh-tw', $langs)) echo 'checked'; ?> name="language_teach[]" value="zh-tw"> <?php _e('Traditional Chinese', 'iii-dictionary'); ?></label><br>

                                                                <label class="check_lb <?php if (in_array('vi', $langs)) echo 'checked_lb'; ?>"><input type="checkbox" <?php if (in_array('vi', $langs)) echo 'checked'; ?> name="language_teach[]" value="vi"> <?php _e('Vietnamese', 'iii-dictionary'); ?></label><br>

                                                            </div>

                                                        </div>

                                                    </div>

                                                </div>

                                            </div>

                                           

                                           
                                            <div class="col-xs-10 col-sm-12">

                                                <div class="form-group">

<?php if ($is_mw_registered_teacher) : ?>

                                                        <button type="submit" class="btn btn-default btn-block orange form-control" name="update-teacher"><span class="icon-check"></span> <?php _e('UPDATE', 'iii-dictionary') ?></button>

<?php else : ?>

                                                        <button type="submit" class="btn btn-default btn-block orange form-control" name="i-agree-update" id="i-agree"><span class="icon-check"></span> <?php _e('I AGREE', 'iii-dictionary') ?></button>

<?php endif ?>

                                                </div>

                                            </div>-->

                                        </div>

                                    </div>
                                </div>
                            </form>
                        </div>
                        <div id="profile" class="tab-pane fade">
                            <h3>Profile</h3>
                            <form method="post" id="myProfile" action="" name="registerform" enctype="multipart/form-data">
                                <div class="row profile-pic">
                                    <div class="col-sm-1 col-md-1">
                                        <div class="form-group">                                            
                                            <img src="<?php echo get_template_directory_uri(); ?>/library/images/profile-picture.png" alt="Profile Picture">
                                        </div>
                                    </div>
                                    <div class="col-sm-10 col-md-10">
                                        <div class="form-group">
                                            <label>My Name</label>
                                            <span class="color-black">Peter</span>
                                        </div>
                                    </div>
                                    <hr>
                                </div>
                                <div class="row line-profile">
                                    <div class="col-sm-6 col-md-6">
                                        <div class="form-group">
                                            <label>Points Balance</label>
                                            <span class="color-yellow">672 (USD)</span>
                                        </div>
                                        <hr>
                                    </div>
                                    <div class="col-sm-6 col-md-6">
                                        <div class="form-group">
                                            <label>Points Earned</label>
                                            <span class="color-yellow">120 (USD)</span>
                                        </div>
                                        <hr>
                                    </div>
                                </div>
                                <div class="row line-profile">
                                    <div class="col-sm-6 col-md-6">
                                        <div class="form-group">
                                            <label>English (Writting) Tutor Qualification</label>
                                            <span class="color-yellow">Qualified</span>
                                        </div>
                                        <hr>
                                    </div>
                                    <div class="col-sm-6 col-md-6">
                                        <div class="form-group">
                                            <label>English (Conversation) Tutor Qualification</label>
                                            <span class="color-yellow">Not Qualified Yet</span>
                                        </div>
                                        <hr>
                                    </div>
                                </div>
                                <div class="row line-profile">
                                    <div class="col-sm-6 col-md-6">
                                        <div class="form-group">
                                            <label>Math (Up to Middle School) Tutor Qualification</label>
                                            <span class="color-yellow">Not Qualified Yet</span>
                                        </div>
                                        <hr>
                                    </div>
                                    <div class="col-sm-6 col-md-6">
                                        <div class="form-group">
                                            <label>Math (Conversation) Tutor Qualification</label>
                                            <span class="color-yellow">Qualified</span>
                                        </div>
                                        <hr>
                                    </div>
                                </div>
                                <div class="row line-profile">
                                    <div class="col-sm-6 col-md-6">
                                        <div class="form-group">
                                            <label>Email Address (for login)</label>
                                            <span class="color-black">abc@gmail.com</span>
                                        </div>
                                        <hr>
                                    </div>
                                    <div class="col-sm-6 col-md-6">
                                        <div class="form-group">
                                            <label>Payment Email (for Paypal)</label>
                                            <span class="color-black">paypal123@gmail.com</span>
                                        </div>
                                        <hr>
                                    </div>
                                </div>
                                <div class="row line-profile">
                                    <div class="col-sm-6 col-md-6">
                                        <div class="form-group">
                                            <label>Date of Birth (month/date/year)</label>
                                            <span class="color-black">01/ 10/ 1986</span>
                                        </div>
                                        <hr>
                                    </div>
                                    <div class="col-sm-6 col-md-6">
                                        <div class="form-group">
                                            <label>Language</label>
                                            <span class="color-black">English, Korean</span>
                                        </div>
                                        <hr>
                                    </div>
                                </div>  
                                <div class="row line-profile">
                                    <div class="col-sm-6 col-md-6">
                                        <div class="form-group">
                                            <label>Mobile Phone Number</label>
                                            <span class="color-black">(408)712-8888</span>
                                        </div>
                                        <hr>
                                    </div>
                                    <div class="col-sm-6 col-md-6">
                                        <div class="form-group">
                                            <label>Last School Attended</label>
                                            <span class="color-black">Silicon Valley College</span>
                                        </div>
                                        <hr>
                                    </div>
                                </div> 
                                <div class="row line-profile">
                                    <div class="col-sm-6 col-md-6">
                                        <div class="form-group">
                                            <label>Last School You Tought (if any)</label>
                                            <span class="color-black">San Jose Middle School</span>
                                        </div>
                                        <hr>
                                    </div>
                                    <div class="col-sm-6 col-md-6">
                                        <div class="form-group">
                                            <label>Skype ID</label>
                                            <span class="color-black">adc_skype</span>
                                        </div>
                                        <hr>
                                    </div>
                                    <div class="col-sm-6 col-md-6">
                                        <div class="form-group">
                                            <label>Profession</label>
                                            <span class="color-black">Content Creator</span>
                                        </div>
                                        <hr>
                                    </div>
                                </div>                               
                            </form>
                        </div>
                        <div id="updateinfo" class="tab-pane fade">
                            <h3>Update Info</h3>
                            <form method="post" id="myUpdate" action="" name="registerform" enctype="multipart/form-data">
                                <h4>Login Info</h4>
                                <div class="row">
                                    <div class="col-sm-6 col-md-6">
                                        <div class="form-group">
                                            <label for="current_email"><?php _e('Current Email Address (for login)', 'iii-dictionary') ?></label>
                                            <input id="current_email" class="form-control input-current" name="current_email" type="text" value="" required>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6">
                                        <div class="form-group">
                                            <label for="new_email"><?php _e('New Email Address (for login)', 'iii-dictionary') ?></label>
                                            <input id="new_email" class="form-control" name="new_email" type="text" value="" required>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6">
                                        <div class="form-group">
                                            <label for="current_email_paypal"><?php _e('Current Payment Email (for Paypal)', 'iii-dictionary') ?></label>
                                            <input id="current_email_paypal" class="form-control input-current"  name="current_email_paypal" type="text" value="" required>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6">
                                        <div class="form-group">
                                            <label for="new_email_paypal"><?php _e('New Payment Email (for Paypal)', 'iii-dictionary') ?></label>
                                            <img style="height: 15px;" src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Questions.png">
                                            <input id="new_email_paypal" class="form-control" name="new_email_paypal" type="text" value="" required>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6">
                                        <div class="form-group">
                                            <label for="new_password"><?php _e('New Password', 'iii-dictionary') ?></label>
                                            <input id="new_password" class="form-control" name="new_password" type="text" value="" required>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6">
                                        <div class="form-group">
                                            <label for="retype_new_password"><?php _e('Retype New Password', 'iii-dictionary') ?></label>
                                            <input id="retype_new_password" class="form-control" name="retype_new_password" type="text" value="" required>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6">
                                        <div class="form-group">
                                            <label for="current_password"><?php _e('Current Password', 'iii-dictionary') ?></label>
                                            <input id="current_password" class="form-control" name="current_password" type="text" value="" required>
                                        </div>
                                    </div>
                                </div>
                                <h4>Personal Info</h4>
                                <div class="row profile-pic">
                                    <label class="img-profile">Profile Picture</label>
                                    <div class="col-sm-1 col-md-1">
                                        <div class="form-group">
                                          
                                            <img src="<?php echo get_template_directory_uri(); ?>/library/images/profile-picture.png" alt="Profile Picture">
                                        </div>
                                    </div>
                                    <div class="col-sm-5 col-md-5">
                                        <div class="form-group" style="margin-top: 15px;">
                                            
                                            <input class="form-control input-file" type="file" id="input-profile" value="">
                                            <div class="form-group">
                                            
                                            <button class="btn-dark-blue" style="background: #cecece;" type="button" name="upload"  onclick="document.getElementById('input-profile').click();"><?php _e('Browse and Upload', 'iii-dictionary') ?></button>
                                        </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6" >
                                        <div class="form-group" style="margin-top: 15px;">
                                            <input class="form-control input-path" type="text" value="">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12 col-md-12">
                                        <label><?php _e('Language', 'iii-dictionary') ?></label>
                                        <div class="form__boolean" id="checkBoxSearch" style="margin-bottom: 10px;">
                                            <div class="col-md-2 col-xs-4 cb-type">
                                                <label>
                                                    <input type="checkbox" class="radio_buttons required class_cb_search option-input radio" name="cb-eng"/>
                                                    English
                                                </label>
                                            </div>
                                            <div class="col-md-2 col-xs-4 cb-type">
                                                <label>
                                                    <input type="checkbox"  checked class="radio_buttons required class_cb_search option-input radio" name="cb-japan"/>
                                                    Japanese
                                                </label>
                                            </div>
                                            <div class="col-md-2 col-xs-4 cb-type">
                                                <label>
                                                    <input type="checkbox"  class="radio_buttons required class_cb_search option-input radio" name="cb-korea"/>
                                                    Korean
                                                </label>
                                            </div>
                                            <div class="col-md-2 col-xs-4 cb-type">
                                                <label>
                                                    <input type="checkbox" class="radio_buttons required class_cb_search option-input radio" name="cb-chinese"/>
                                                    Chinese
                                                </label>
                                            </div>
                                            <div class="col-md-2 col-xs-4 cb-type">
                                                <label>
                                                    <input type="checkbox"  class="radio_buttons required class_cb_search option-input radio" name="cb-trachinese"/>
                                                    Traditional Chinese
                                                </label>
                                            </div>
                                            <div class="col-md-2 col-xs-4 cb-type" style="padding-left: 30px !important;">
                                                <label>
                                                    <input type="checkbox"  class="radio_buttons required class_cb_search option-input radio" name="cb-vietnam"/>
                                                    Vietnamese
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6">
                                        <div class="form-group">
                                            <label for="current_mobile"><?php _e('Current Mobile Phone Number', 'iii-dictionary') ?></label>
                                            <input id="current_mobile" class="form-control input-current" readonly="" name="current_mobile" type="text" value="" required>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6">
                                        <div class="form-group">
                                            <label for="new_mobile"><?php _e('New Mobile Phone Number', 'iii-dictionary') ?></label>
                                            <input id="new_mobile" class="form-control" name="new_mobile" type="text" value="" required>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6">
                                        <div class="form-group">
                                            <label for="last_attended"><?php _e('Last School You Attended', 'iii-dictionary') ?></label>
                                            <input id="last_attended" class="form-control input-current" readonly="" name="last_attended" type="text" value="" required>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6">
                                        <div class="form-group">
                                            <label for="new_last_attended"><?php _e('New Last School You Attended', 'iii-dictionary') ?></label>
                                            <input id="new_last_attended" class="form-control" name="new_last_attended" type="text" value="" required>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6">
                                        <div class="form-group">
                                            <label for="last_tought"><?php _e('Last School You Tought (if any)', 'iii-dictionary') ?></label>
                                            <input id="last_tought" class="form-control input-current" readonly="" name="last_tought" type="text" value="" required>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6">
                                        <div class="form-group">
                                            <label for="new_last_tought"><?php _e('New Last School You Tought (if any)', 'iii-dictionary') ?></label>
                                            <input id="new_last_tought" class="form-control" name="new_last_tought" type="text" value="" required>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6">
                                        <div class="form-group">
                                            <label for="skype_id"><?php _e('Skype ID', 'iii-dictionary') ?></label>
                                            <input id="skype_id" class="form-control input-current" readonly="" name="skype_id" type="text" value="" required>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6">
                                        <div class="form-group">
                                            <label for="new_skype_id"><?php _e('New Skype ID', 'iii-dictionary') ?></label>
                                            <input id="new_skype_id" class="form-control" name="new_skype_id" type="text" value="" required>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6">
                                        <div class="form-group">
                                            <label for="profession"><?php _e('Profession', 'iii-dictionary') ?></label>
                                            <input id="profession" class="form-control input-current" readonly="" name="profession" type="text" value="">
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6">
                                        <div class="form-group">
                                            <label for="new_profession"><?php _e('New Profession', 'iii-dictionary') ?></label>
                                            <input id="new_profession" class="form-control" name="new_profession" type="text" value="" required>
                                        </div>
                                    </div>
                                </div> 
                                <div class="row">
                                    <div class="col-xs-12 col-sm-8 col-md-8">
                                        <div class="form-group" style="height: 50px; margin-top: -20px; margin-bottom: 30px;">
                                            <label>&nbsp;</label>
                                            <button class="btn-dark-blue" style="background: #f7b555;" type="submit" name="wp-submit"><?php _e('Update', 'iii-dictionary') ?></button>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-4 col-md-4">
                                        <div class="form-group">
                                            <label>&nbsp;</label>
                                            <a class="button-grey" style="background: #cecece; margin-top: 5px;" data-dismiss="modal" type="submit" name="wp-submit"><?php _e('Cancel', 'iii-dictionary') ?></a>
                                        </div>
                                    </div>
                                </div>        
                            </form>
                        </div>
                        <div id="menu4" class="tab-pane fade">bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb</div>
                    </div>
                </div>
                <div class="section-left">
                    <ul id="menu-left-myaccount" class="nav nav-tabs">
                        <li class="active" id="account"><a data-toggle="tab" href="#hom"><img src="<?php echo get_template_directory_uri(); ?>/library/images/1_Menu_Icon_My_Account.png" class="" alt="setting my account" style="width: 23px;margin:30px 0px 20px"></a>
                        </li>
                        <li><a data-toggle="tab" href="#menu3"><img src="<?php echo get_template_directory_uri(); ?>/library/images/2_Menu_Icon_My_Subscription.png" class="" alt="setting my account" style="width: 23px;margin:15px 0px"></a></li>
                        <li><a data-toggle="tab" href="#menu4"><img src="<?php echo get_template_directory_uri(); ?>/library/images/3_Menu_Icon_Tutoring.png" class="" alt="setting my account" style="width: 23px;margin:15px 0px"></a></li>
                        <li><a data-toggle="tab" href="#menu4"><img src="<?php echo get_template_directory_uri(); ?>/library/images/4_Menu_Icon_Worksheet.png" class="" alt="setting my account" style="width: 23px;margin:15px 0px"></a></li>
                        <li><a data-toggle="tab" href="#menu4"><img src="<?php echo get_template_directory_uri(); ?>/library/images/5_Menu_Icon_Download.png" class="" alt="setting my account" style="width: 23px;margin:15px 0px"></a></li>
                    </ul>
                </div>
                <div id="mySidenav" class="sidenav">
                    <ul class="nav nav-tabs none-block">
                        <li><a class="header-menu-left active" data-toggle="tab" href="#create-account">My Account</a></li>
                        <li><a class="sub-menu-left" data-toggle="tab" href="#create-account">Create Account</a></li>
                        <li><a class="sub-menu-left" data-toggle="tab" href="#profile">Profile</a></li>
                        <li><a class="sub-menu-left" data-toggle="tab" href="#updateinfo">Update Info</a></li>
                        <li><a class="sub-menu-left" data-toggle="tab" href="#">Qualification</a></li>
                        <li><a class="header-menu-left padd-adjus" href="#">My Subscription</a></li>
                        <li><a class="header-menu-left padd-adjus" href="#">Tutoring</a></li>
                        <li><a class="header-menu-left padd-adjus" href="#">Worksheets</a></li>
                        <li><a class="header-menu-left padd-adjus" href="#">Downloads</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

</div> 



<?php get_dict_page_title($_page_title, '', '', array(), get_info_tab_cloud_url('Popup_info_9.jpg')) ?>

<p style="font-size: 28px;">Modify My Account and Register as a Teacher</p>

<div>

    <div class="row">

        <p><a role="button" style="color:#fff779" data-toggle="collapse" href="#step1-collapse" class="step-number" title="" ><?php _e('Profile', 'iii-dictionary'); ?></a></p>

        <div id="step1-collapse" class="collapse">

            <div class="row " style="padding-left: 10%">

<?php $point_ex_rate = mw_get_option('point-exchange-rate'); ?>

                <p class="instructions-text ">Math</p>

                <p class="instructions-text " style="padding-left: 5%"><?php printf(__('%s', 'iii-dictionary'), '<strong>' . $status_teacher_qualified_math . '</strong>') ?></p>

                <p class="instructions-text ">English</p>

                <p class="instructions-text " style="padding-left: 5%"><?php printf(__('%s', 'iii-dictionary'), '<strong>' . $status_teacher_qualified_english . '</strong>') ?></p>

                <p class="instructions-text "><?php printf(__('My remaining points are: $%s', 'iii-dictionary'), '<strong>' . ik_get_user_points($user->ID) . '</strong>') ?></p>

                <div >

                    <div style="float: left;" >

                        <p class="instructions-text "><?php _e('Point I have earned:', 'iii-dictionary'); ?> <?php echo '$' . (ik_get_user_points($user->ID) / $point_ex_rate); ?></p>

                    </div>

                    <div >

                        <p class="instructions-text "><a style="padding-left: 10%" href="<?php echo locale_home_url(); ?>?r=teaching/request-payment" ><?php _e('Request for the payment(Click)?', 'iii-dictionary'); ?></a></p>

                    </div>

                </div>

                <p class="instructions-text "><?php printf(__('I requested the payment: Yes ', 'iii-dictionary')) ?></p>

                <div class="row">

                    <p><a role="button" style="color:#b68342" data-toggle="collapse" href="#step7-collapse" class="step-number" title="" ><?php _e('Created Groups and Joined Groups', 'iii-dictionary'); ?></a></p>

                    <div id="step7-collapse" class="collapse">

                        <div class="row " style="padding-left: 10%">

<?php for ($j = 0; $j < count($createdgroup); $j++) {
    ?>

                                <p class="instructions-text " ><?php printf(__('I create a group: %s ', 'iii-dictionary'), '<strong>' . $createdgroup[$j] . '</strong>') ?></p>

    <?php
}

for ($j = 0; $j < count($user_group_join); $j++) {
    ?>

                                <p class="instructions-text " ><?php printf(__('I belong to: %s ', 'iii-dictionary'), '<strong>' . $user_group_join[$j]->name . '</strong>') ?></p>

    <?php
}
?>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <div class="row">

        <form method="post" action="<?php echo locale_home_url() ?>/?r=my-account" enctype="multipart/form-data">

            <p><a role="button" style="color:#fff779" data-toggle="collapse" href="#step2-collapse" class="step-number" title="" ><?php _e('Modify login info', 'iii-dictionary'); ?></a></p>

            <div id="step2-collapse" class="collapse">

                <div class="col-sm-6">

                    <div class="form-group">

                        <label for="email"><?php _e('Current e-mail address', 'iii-dictionary') ?></label>

                        <input type="text" class="form-control" id="email" name="email" value="<?php echo $user->user_email ?>">

                    </div>

                </div>

                <div class="col-sm-6">

                    <div class="form-group">

                        <label for="new-email"><?php _e('New e-mail address', 'iii-dictionary') ?></label>

                        <input type="text" class="form-control" id="new-email" name="new-email" value="" placeholder="<?php _e('unchanged', 'iii-dictionary') ?>" required>

                    </div>

                </div>



                <div class="col-sm-6">

                    <div class="form-group">

                        <label for="new-password"><?php _e('New password', 'iii-dictionary') ?></label>

                        <input type="password" class="form-control" id="new-password" name="new-password" value="" placeholder="<?php _e('unchanged', 'iii-dictionary') ?>" required>

                    </div>

                </div>

                <div class="col-sm-6">

                    <div class="form-group">

                        <label for="confirm-passwrd"><?php _e('Retype new password', 'iii-dictionary') ?></label>

                        <input type="password" class="form-control" id="confirm-passwrd" name="confirm-password" value="" required>

                    </div>

                </div>



                <div class="col-sm-6">

                    <div class="form-group">

                        <label for="passwrd"><?php _e('Current password', 'iii-dictionary') ?></label>

                        <input type="password" class="form-control" id="passwrd" name="old-password" value="" placeholder="**********" required>

                    </div>

                </div>

                <div class="col-sm-12">

                    <div class="form-group">

                        <label>&nbsp;</label>

                        <button type="submit" class="btn btn-default btn-block orange form-control" name="save"  disabled><span class="icon-save"></span><?php _e('Save', 'iii-dictionary') ?></button>

                    </div>

                </div>

            </div>

        </form>

    </div>

    <div class="row">

        <form method="post" action="<?php echo locale_home_url() ?>/?r=my-account" enctype="multipart/form-data">

            <p><a role="button" style="color:#fff779" data-toggle="collapse" href="#step3-collapse" class="step-number" title="" ><?php _e('Modify personal info', 'iii-dictionary'); ?></a></p>

            <div id="step3-collapse" class="collapse">

                <div class="col-sm-6">

                    <div class="form-group">

                        <label for="current-first-name"><?php _e('First Name', 'iii-dictionary') ?></label>

                        <input type="text" class="form-control" id="current-first-name" value="<?php echo $user->first_name ?>" required>

                    </div>

                </div>

                <div class="col-sm-6">

                    <div class="form-group">

                        <label for="current-last-name"><?php _e('Last Name', 'iii-dictionary') ?></label>

                        <input type="text" class="form-control" id="current-last-name" value="<?php echo $user->last_name ?>" required>

                    </div>

                </div>

                <div class="col-sm-6">

                    <div class="form-group">

                        <label for="first-name"><?php _e('New first Name', 'iii-dictionary') ?></label>

                        <input type="text" class="form-control" id="first-name" name="first-name" value="" placeholder="<?php _e('unchanged', 'iii-dictionary') ?>" required> 

                    </div>

                </div>





                <div class="col-sm-6">

                    <div class="form-group">

                        <label for="last-name"><?php _e('New last Name', 'iii-dictionary') ?></label>

                        <input type="text" class="form-control" id="last-name" name="last-name" value="" placeholder="<?php _e('unchanged', 'iii-dictionary') ?>" required>

                    </div>

                </div>



                <div class="col-sm-6">

                    <div class="form-group">

                        <label><?php _e('Date of Birth', 'iii-dictionary') ?> <small>(mm/dd/yyyy)</small></label>

                        <div class="row">

<?php
$date_of_birth = array();

if (strtotime($user->date_of_birth)) {

    $date_of_birth = explode('/', $user->date_of_birth);
}
?>

                            <div class="col-xs-4">

                                <select class="select-box-it form-control" name="birth-m">

                                    <option value="00">mm</option>

<?php for ($i = 1; $i <= 12; $i++) : ?>

    <?php $pad_str = str_pad($i, 2, '0', STR_PAD_LEFT) ?>

                                        <option value="<?php echo $pad_str ?>"<?php echo $date_of_birth[0] == $pad_str ? ' selected' : '' ?>><?php echo $pad_str ?></option>

<?php endfor ?>

                                </select>

                            </div>

                            <div class="col-xs-4">

                                <select class="select-box-it form-control" name="birth-d">

                                    <option value="00">dd</option>

<?php for ($i = 1; $i <= 31; $i++) : ?>

    <?php $pad_str = str_pad($i, 2, '0', STR_PAD_LEFT) ?>

                                        <option value="<?php echo $pad_str ?>"<?php echo $date_of_birth[1] == $pad_str ? ' selected' : '' ?>><?php echo $pad_str ?></option>

<?php endfor ?>

                                </select>

                            </div>

                            <div class="col-xs-4">

                                <input class="form-control" name="birth-y" type="text" value="<?php echo $date_of_birth[2] ?>" placeholder="yyyy" required>

                            </div>

                        </div>

                    </div>

                </div>

                <div class="col-sm-6">

                    <div class="form-group">

                        <label for="last-name"><?php _e('Language', 'iii-dictionary') ?></label>

<?php
$langs = array(
    'en' => 'English',
    'ja' => '日本語',
    'ko' => '한국어',
    'vi' => 'Tiếng Việt',
    'zh' => '中文',
    'zh-tw' => '中國'
);



//$cur_lang = get_short_lang_code();
?>

                        <select name="language_type" class="form-control language_type">

                            <?php foreach ($langs as $code => $lang) : ?>

                                <option value="<?php echo $code; ?>"<?php echo $user->language_type == $code ? ' selected' : '' ?>><?php echo $lang ?></option>

<?php endforeach ?>

                        </select>

                    </div>

                </div>

                <div class="col-sm-12">

                    <div class="form-group">

                        <label>&nbsp;</label>

                        <button type="submit" class="btn btn-default btn-block orange form-control" name="save" id="save-btn" disabled><span class="icon-save"></span><?php _e('Save', 'iii-dictionary') ?></button>

                    </div>

                </div>

            </div>

        </form>

    </div>

    <div class="row">

        <form method="post" action="<?php echo locale_home_url() ?>/?r=my-account" enctype="multipart/form-data">

            <p><a role="button" style="color:#fff779" data-toggle="collapse" href="#step4-collapse" class="step-number" title="" ><?php _e('Teacher\'s registration agreement', 'iii-dictionary'); ?></a></p>

            <div id="step4-collapse" class="collapse">

                <div class="row red-brown">

                    <div class="col-sm-12" id="page-tabs-container" style="background:transparent">

                        <ul id="page-tabs">

                            <li class="page-tab active" data-tab="english_class"><a href="javascript:void(0);" ><?php _e('English Classes', 'iii-dictionary'); ?></a></li>

                            <li class="page-tab" data-tab="math_class"><a href="javascript:void(0);" ><?php _e('Math Classes', 'iii-dictionary'); ?></a></li>

                        </ul>

                    </div>

<?php
$is_teaching_agreement_uptodate_math = ik_is_teacher_agreement_uptodate('MATH');

$is_teaching_agreement_uptodate = ik_is_teacher_agreement_uptodate();
?>

                    <div class="col-sm-12">

                        <div class="form-group box_english">

                            <div class="box box-red " >

                                <div class="scroll-list" style="max-height: 200px; color: #fff">

                        <?php echo mw_get_option('registration-agreement') ?>

                                </div>

                            </div>

                            <div class="col-sm-6 col-xs-2"></div>

                            <div class="col-sm-1 col-xs-2">

                                <label>&nbsp;</label>

                                <div >

                                    <input id="rdo-agreed" class="checkboxagree" <?php if ($is_teaching_agreement_uptodate) echo 'checked'; ?> type="checkbox" name="agree-english-teacher" value="1" >

                                </div>

                            </div>

                            <div class="col-xs-8 col-sm-5">

                                <div class="form-group">

                                    <label>&nbsp;</label>

                                    <button type="button" class="btn btn-default btn-block orange form-control r_agree_english" name="ie-agree" id="i-agree"><span <?php
                        if ($is_teaching_agreement_uptodate) {

                            echo 'class="icon-check"';
                        } else {

                            echo 'class="icon-cancel"';
                        }
                        ?>></span> <?php ($is_teaching_agreement_uptodate) ? _e('AGREED', 'iii-dictionary') : _e('CLICK TO AGREE', 'iii-dictionary') ?></button>

                                </div>

                            </div>

                        </div>

                        <div class="form-group box_math" style="display:none">

                            <div class="box box-red " >

                                <div class="scroll-list" style="max-height: 200px; color: #fff">

<?php echo mw_get_option('math-registration-agreement') ?>

                                </div>

                            </div>

                            <div class="col-sm-6 col-xs-2"></div>

                            <div class="col-xs-2 col-sm-1">

                                <div class="form-group">

                                    <label>&nbsp;</label>

                                    <div>

                                        <input id="rdo-agreed-math" class="checkboxagree" <?php if ($is_teaching_agreement_uptodate_math) echo 'checked'; ?> type="checkbox" name="agree-math-teacher" value="2">



                                    </div>

                                </div>

                            </div>

                            <div class="col-xs-8 col-sm-5">

                                <div class="form-group">

                                    <label>&nbsp;</label>

                                    <button type="button" class="btn btn-default btn-block orange form-control r_agree_math" name="im-agree" id="i-agree"><span class="<?php
if ($is_teaching_agreement_uptodate_math) {

    echo 'icon-check';
} else {

    echo 'icon-cancel';
}
?>"></span> <?php ($is_teaching_agreement_uptodate_math) ? _e('AGREED', 'iii-dictionary') : _e('CLICK TO AGREE', 'iii-dictionary') ?></button>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

                <div class="row">

                    <div class="col-sm-4">

                        <div class="form-group">

                            <table class="table profile-picture-form">

                                <tr>

                                    <td class="profile-picture">

                                        <div id="profile-picture">

                                        <?php
                                        $user_avatar = ik_get_user_avatar($current_user->ID);

                                        if (!empty($user_avatar)) :
                                            ?>

                                                <img src="<?php echo $user_avatar ?>" width="100" height="100" alt="<?php echo $current_user->display_name ?>">

                                            <?php
                                        else :

                                            echo get_avatar($current_user->ID, 120);

                                        endif
                                        ?>

                                        </div>

                                    </td>

                                    <td class="upload-block">

                                        <label><?php _e('Profile Picture', 'iii-dictionary') ?></label><br>

                                        <span class="btn btn-default grey btn-file">

                                            <span class="icon-browse"></span><?php _e('Browse', 'iii-dictionary') ?> <input name="input-image" id="input-image" type="file">

                                        </span>

                                    </td>

                                </tr>

                            </table>

                        </div>

                    </div>

                    <div class="col-md-8">

                        <div class="form-group">

                            <label><?php _e('Which language you use to teach', 'iii-dictionary') ?></label>

                            <div class="row">

<?php
$language_teach = get_user_meta($current_user->ID, 'language_teach', true);

$langs = explode(',', $language_teach);
?>

                                <div class="col-md-6">

                                    <label class="check_lb <?php if (in_array('en', $langs)) echo 'checked_lb'; ?>"><input type="checkbox" <?php if (in_array('en', $langs)) echo 'checked'; ?> name="language_teach[]" value="en"> <?php _e('English', 'iii-dictionary'); ?></label><br>

                                    <label class="check_lb <?php if (in_array('ja', $langs)) echo 'checked_lb'; ?>"><input type="checkbox" <?php if (in_array('ja', $langs)) echo 'checked'; ?> name="language_teach[]" value="ja"> <?php _e('Japanese', 'iii-dictionary'); ?></label><br>

                                    <label class="check_lb <?php if (in_array('ko', $langs)) echo 'checked_lb'; ?>"><input type="checkbox" <?php if (in_array('ko', $langs)) echo 'checked'; ?> name="language_teach[]" value="ko"> <?php _e('Korean', 'iii-dictionary'); ?></label><br>

                                </div>

                                <div class="col-md-6">

                                    <label class="check_lb <?php if (in_array('zh', $langs)) echo 'checked_lb'; ?>"><input type="checkbox" <?php if (in_array('zh', $langs)) echo 'checked'; ?> name="language_teach[]" value="zh"> <?php _e('Chinese', 'iii-dictionary'); ?></label><br>

                                    <label class="check_lb <?php if (in_array('zh-tw', $langs)) echo 'checked_lb'; ?>"><input type="checkbox" <?php if (in_array('zh-tw', $langs)) echo 'checked'; ?> name="language_teach[]" value="zh-tw"> <?php _e('Traditional Chinese', 'iii-dictionary'); ?></label><br>

                                    <label class="check_lb <?php if (in_array('vi', $langs)) echo 'checked_lb'; ?>"><input type="checkbox" <?php if (in_array('vi', $langs)) echo 'checked'; ?> name="language_teach[]" value="vi"> <?php _e('Vietnamese', 'iii-dictionary'); ?></label><br>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

                <div class="col-sm-6">

                    <div class="form-group">

                        <label><?php _e('Mobile phone number', 'iii-dictionary') ?></label>

                        <input type="text" class="form-control" name="mobile-number" value="<?php
                                        echo get_user_meta($current_user->ID, 'mobile_number', true);

                                        ;
?>">

                    </div>

                </div>

                <div class="col-sm-6">

                    <div class="form-group">

                        <label><?php _e('Email for Paypal', 'iii-dictionary') ?></label>

                        <input type="text" class="form-control" name="email-paypal" value="<?php echo get_user_meta($current_user->ID, 'email_paypal', true); ?>" required>

                    </div>

                </div>

                <div class="col-sm-6">

                    <div class="form-group">

                        <label><?php _e('Last school  you attended', 'iii-dictionary') ?></label>

                        <input type="text" class="form-control" name="last-school" value="<?php echo get_user_meta($current_user->ID, 'last_school', true); ?>" required>

                    </div>

                </div>

                <div class="col-sm-6">

                    <div class="form-group">

                        <label><?php _e('Latest school you tought if any', 'iii-dictionary') ?></label>

                        <input type="text" class="form-control" name="previous-school" value="<?php echo get_user_meta($current_user->ID, 'previous_school', true); ?>" required>

                    </div>

                </div>

                <div class="col-xs-10 col-sm-12">

                    <div class="form-group">

<?php if ($is_mw_registered_teacher) : ?>

                            <button type="submit" class="btn btn-default btn-block orange form-control" name="update-teacher"><span class="icon-check"></span> <?php _e('UPDATE', 'iii-dictionary') ?></button>

<?php else : ?>

                            <button type="submit" class="btn btn-default btn-block orange form-control" name="i-agree-update" id="i-agree"><span class="icon-check"></span> <?php _e('I AGREE', 'iii-dictionary') ?></button>

                                <?php endif ?>

                    </div>

                </div>

            </div>

        </form>

    </div>

</div>

<!-- step english (take the math teacher test)-->

<div class="row">

    <form method="post" action="<?php echo locale_home_url() ?>/?r=my-account" enctype="multipart/form-data">

        <p><a role="button" style="color:#fff779" data-toggle="collapse" href="#step5-collapse" class="step-number" title="" ><?php _e('Take the English teacher test', 'iii-dictionary'); ?></a></p>

        <div id="step5-collapse" class="collapse">

            <div class="row " style="padding-left: 10%">

                <div class="col-sm-12">

                    <div class="step-block">



                        <div class="step-inst"><?php _e('Let\'s get started with the following steps.', 'iii-dictionary') ?></div>

                        <div class="box box-red">





                            <div class="form-group"></div>

                            <div class="row">

                                <ul>

                                    <li><strong>1)</strong> <?php _e('Take the following two tests. One test will be auto-graded, and you will see your result immediately. Another test will be to edit a sample student\'s essay. We will review your work.', 'iii-dictionary') ?></li>

                                </ul>

                                <div class="col-sm-5">

                                    <label><?php _e('Auto-graded Test', 'iii-dictionary') ?></label>

<?php
if (empty($test1_result)) {

    if (is_mw_qualified_teacher()) {
        ?>

                                            <div class="col-sm-12" >

                                                <p ><?php _e('Your score:', 'iii-dictionary') ?> <strong class="text-success">

        <?php echo 'Accepted by admin';
        ?></strong></p>

                                                <span class="text-success"><?php _e('Accepted', 'iii-dictionary') ?></span>

                                            </div>

    <?php } else { ?>

                                            <button type="submit" name="take-test1" class="btn btn-default btn-block grey form-control"<?php echo is_mw_qualified_teacher() ? 'disabled' : '' ?>><?php _e('Take this test', 'iii-dictionary') ?></button>

    <?php }
} ?>

                                </div>

<?php if (!empty($test1_result)) : ?>

                                    <div class="col-sm-12">

    <?php if ($test1_result[0]->graded) : ?>

                                            <p><?php _e('Your score:', 'iii-dictionary') ?> <strong 

        <?php
        if (($test1_result[0]->score < $teacher_test_score_threshold && is_mw_qualified_teacher()) || $test1_result[0]->score >= $teacher_test_score_threshold) {

            echo 'class="text-success"';
        } else {
            echo 'class="text-danger"';
        }
        ?>>

        <?php
        if ($test1_result[0]->score < $teacher_test_score_threshold && is_mw_qualified_teacher()) {

            echo 'Accepted by admin';
        } else {

            echo $test1_result[0]->score . '%';
        }
        ?></strong></p>



    <?php else : ?>

                                            <p><?php _e('Please check back to see if you are registered as a teacher to teach English writing.', 'iii-dictionary') ?></p>

    <?php endif ?>

                                    </div>

<?php endif ?>

<?php if ($test1_result[0]->graded) : ?>

                                    <div class="col-sm-12">

    <?php if (($test1_result[0]->score > $teacher_test_score_threshold) || $test1_result[0]->score < $teacher_test_score_threshold && is_mw_qualified_teacher()): ?>

                                            <span class="text-success"><?php _e('Accepted', 'iii-dictionary') ?></span>

    <?php else : ?>

                                            <span class="text-danger"><?php _e('Sorry, you did not pass. Would you like to take the test again?', 'iii-dictionary') ?></span>

                                            <div class="row" style="margin-top: 10px">

                                                <div class="col-sm-5">

                                                    <button type="submit" name="re-take-test1" class="btn btn-default btn-block grey form-control"><?php _e('Take this test again', 'iii-dictionary') ?></button>

                                                </div>

                                            </div>

    <?php endif ?>

                                    </div>

<?php endif ?>



                                <div class="clearfix"></div>

                                <ul>

                                    <li><strong>2)</strong> <?php _e('Once we review your editing, we will notify you on this site on how to proceed to the next step.', 'iii-dictionary') ?></li>

                                </ul>

                                <div class="col-sm-5" style="margin-top: 15px">

                                    <label><?php _e('Edit a sample student\'s essay Test', 'iii-dictionary') ?></label>

                                    <?php
                                    if (empty($test2_result)) :

                                        if (is_mw_qualified_teacher()) {
                                            ?>

                                            <div class="col-sm-12" >

                                                <p ><?php _e('Your score:', 'iii-dictionary') ?> <strong class="text-success">

        <?php echo 'Accepted by admin';
        ?></strong></p>

                                                <span class="text-success"><?php _e('Accepted', 'iii-dictionary') ?></span>

                                            </div>

                                        <?php } else { ?>

                                            <button type="submit" name="take-test2" class="btn btn-default btn-block grey form-control"

                                        <?php
                                        echo

                                        ($test1_result[0]->score < $teacher_test_score_threshold || is_mw_qualified_teacher() == true) ? 'disabled' : ''
                                        ?>><?php \_e('Take this test', 'iii-dictionary') ?></button>

    <?php }endif ?>

                                </div>

                                            <?php if (!empty($test2_result)) : ?>

                                    <div class="col-sm-12">

                                                <?php if ($test2_result[0]->graded) : ?>

                                            <p><?php _e('Your score:', 'iii-dictionary') ?> <strong 

                                                    <?php if (( $test2_result[0]->score < $teacher_test_score_threshold && is_mw_qualified_teacher()) || $test2_result[0]->score >= $teacher_math_test_score_threshold) { ?> 

                                                        <?php
                                                        echo 'class="text-success"';
                                                    } else {

                                                        echo 'class="text-danger"';
                                                    }
                                                    ?>>

                                            <?php
                                            if ($test2_result[0]->score < $teacher_test_score_threshold && is_mw_qualified_teacher()) {

                                                echo 'Accepted by admin';
                                            } else {

                                                echo $test2_result[0]->score . '%';
                                            }
                                            ?></strong></p>

                                    <?php else : ?>

                                            <p><?php _e('Please check back to see if you are registered as a teacher to teach English writing.', 'iii-dictionary') ?></p>

                                        <?php endif ?>

                                    </div>

                                    <?php endif ?>

<?php if ($test2_result[0]->graded) : ?>

                                    <div class="col-sm-12">

    <?php if ($test2_result[0]->score >= $teacher_test_score_threshold || is_mw_qualified_teacher()) : ?>

                                            <span class="text-success"><?php _e('Accepted', 'iii-dictionary') ?></span>

    <?php else : ?>

                                            <span class="text-danger"><?php _e('Sorry, you did not pass. Would you like to take the test again?', 'iii-dictionary') ?></span>

                                            <div class="row" style="margin-top: 10px">

                                                <div class="col-sm-5">

                                                    <button type="submit" name="re-take-test2" class="btn btn-default btn-block grey form-control"><?php _e('Take this test again', 'iii-dictionary') ?></button>

                                                </div>

                                            </div>

    <?php endif ?>

                                    </div>

<?php endif ?>

                            </div>

                        </div>

                    </div>

                </div>



<?php if (is_mw_qualified_teacher()) : ?>



                    <div class="col-sm-12">

                        <div class="step-block">

                            <div class="step-inst"><?php _e('Now you are ready! Before you select the class to teach, let\'s review some of important rules.', 'iii-dictionary') ?></div>

                            <h2 class="title-border">Teaching Agreement</h2>

                            <div class="box box-red form-group">

                                <div class="scroll-list" style="max-height: 200px; color: #fff">

                                                <?php echo mw_get_option('teaching-agreement') ?>

                                </div>

                            </div>

                            <div class="row">

                                <div class="col-sm-6 col-xs-2"></div>

                                <div class="col-sm-1 col-xs-2">

                                    <label>&nbsp;</label>

                                    <div class="radio radio-style1" id="r_agree_english">

                                        <input  <?php echo $is_teaching_agreement_agreed ? 'checked' : ''; ?> type="radio" name="agree-english-teacher" value="1" >

                                        <label for="rdo-agreed"> </label>

                                    </div>

                                </div>

                                <div class="col-xs-8 col-sm-5">

                                    <div class="form-group">

                                        <label>&nbsp;</label>

                                        <button type="submit" class="btn btn-default btn-block orange form-control" name="teaching-english-agree" ><span class="<?php
                                                if ($is_teaching_agreement_agreed) {

                                                    echo 'icon-check';
                                                } else {

                                                    echo 'icon-cancel';
                                                }
                                                ?>"></span> <?php ($is_teaching_agreement_agreed) ? _e('AGREED', 'iii-dictionary') : _e('CLICK TO AGREE', 'iii-dictionary') ?></button>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>



    <?php if ($is_teaching_agreement_agreed) : ?>



                        <div class="col-sm-12">

                            <div class="step-block">

                                <div class="step-inst"><?php _e('Select worksheet to grade from the list below', 'iii-dictionary') ?></div>

                                <div class="box box-sapphire">

                                    <div class="row box-header">

                                        <div class="col-xs-12">

                                            <div class="row search-tools">

                                                <div class="col-xs-6 col-sm-3">

                                                    <div class="form-group">

        <?php MWHtml::select_grades('ENGLISH', $filter['grade'], array('class' => 'select-sapphire form-control', 'name' => 'filter[grade]')) ?>

                                                    </div>

                                                </div>

                                                <div class="col-xs-12 col-sm-3 col-sm-offset-6">

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

                                                <table class="table table-striped table-condensed ik-table1 text-center" id="list-sheets">

                                                    <thead>

                                                        <tr>

                                                            <th><?php _e('Grade', 'iii-dictionary') ?></th>

                                                            <th><?php _e('Sheet Name', 'iii-dictionary') ?></th>

                                                            <th class="hidden-xs"><?php _e('Requested by', 'iii-dictionary') ?></th>

                                                            <th><?php _e('Price', 'iii-dictionary') ?></th>

                                                            <th></th>

                                                        </tr>

                                                    </thead>

                                                    <tfoot>

                                                        <tr><td colspan="5"><?php echo $pagination ?></td></tr>

                                                    </tfoot>

                                                    <tbody><?php if (empty($grading_requests->items)) : ?>

                                                            <tr><td colspan="5"><?php _e('No grading requests', 'iii-dictionary') ?></td></tr>

            <?php
        else :

            foreach ($grading_requests->items as $item) :
                ?>

                                                        <?php
                                                        $now = date('Y-m-d');

                                                        $days = (strtotime($now) - strtotime($item->requested_on)) / (60 * 60 * 24);

                                                        if ($days <= 3) {
                                                            ?>

                                                                    <tr>

                                                                        <td><?php echo $item->grade ?></td>

                                                                        <td><?php echo $item->sheet_name ?></td>

                                                                        <td class="hidden-xs"><?php echo $item->requester ?></td>

                                                                        <td><?php printf(__('%s pts', 'iii-dictionary'), ik_calc_grading_earning($item->paid_amount)) ?></td>

                                                                        <td>

                                                                            <button type="submit" name="accept-request" class="btn btn-default btn-block btn-tiny grey accept-request" data-request-id="<?php echo $item->request_id ?>"><?php _e('Grade', 'iii-dictionary') ?></button>

                                                                        </td>

                                                                    </tr>

                    <?php
                } else {

                    ik_add_user_points($item->paid_amount, $item->requested_by);

                    MWDB::delete_worksheet_grading_requests($item->request_id);
                }
                ?>

                <?php
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

                        </div>



    <?php endif ?>



<?php endif ?>

            </div>

        </div>

    </form>

</div>



<!-- step math (take the math teacher test)-->

<form action="<?php echo locale_home_url() . '/?r=my-account' ?>" method="post" id="main-form" enctype="multipart/form-data">

    <div class="row">

        <p><a role="button" style="color:#fff779" data-toggle="collapse" href="#step6-collapse" class="step-number" title="" ><?php _e('Take the Math teacher test', 'iii-dictionary'); ?></a></p>

        <div id="step6-collapse" class="collapse">

            <div class="row " style="padding-left: 10%">

                <div class="col-sm-12">

                    <div class="step-block">



                        <div class="step-inst"><?php _e('Let\'s get started with the following steps.', 'iii-dictionary') ?></div>

                        <div class="box box-red">





                            <div class="form-group"></div>

                            <div class="row">

                                <ul>

                                    <li><strong>1)</strong> <?php _e('Take the following two tests. One test will be auto-graded, and you will see your result immediately. Another test will be to edit a sample student\'s essay. We will review your work.', 'iii-dictionary') ?></li>

                                </ul>

                                <div class="col-sm-5">

                                    <label><?php _e('Auto-graded Test qualification to teacher K-to-7', 'iii-dictionary') ?></label>

                                                <?php if (empty($math_test1_result)) :

                                                    if (is_mw_qualified_teacher($user->ID, 1)) {
                                                        ?>

                                            <div class="col-sm-12" >

                                                <p ><?php _e('Your score:', 'iii-dictionary') ?> <strong class="text-success">

        <?php echo 'Accepted by admin';
        ?></strong></p>

                                                <span class="text-success"><?php _e('Accepted', 'iii-dictionary') ?></span>

                                            </div>

    <?php } else { ?>

                                            <button type="submit" name="take-math-test1" class="btn btn-default btn-block grey form-control" <?php echo is_mw_qualified_teacher($user->ID, 1) ? 'disabled' : '' ?>><?php _e('Take this test', 'iii-dictionary') ?></button>

    <?php }endif ?>

                                </div>



                                                <?php if (!empty($math_test1_result)) : ?>

                                    <div class="col-sm-12">

                                                    <?php if ($math_test1_result[0]->graded) : ?>

                                            <p><?php _e('Your score:', 'iii-dictionary') ?> <strong

                                                        <?php
                                                        if ($math_test1_result[0]->score >= $teacher_math_test_score_threshold || ($math_test1_result[0]->score < $teacher_math_test_score_threshold && is_mw_qualified_teacher($user->ID, 1))) {

                                                            echo 'class="text-success"';
                                                        } else {

                                                            echo 'class="text-danger"';
                                                        }
                                                        ?>>

        <?php
        if ($math_test1_result[0]->score < $teacher_math_test_score_threshold && is_mw_qualified_teacher($user->ID, 1)) {

            echo 'Accepted by admin';
        } else {

            echo $math_test1_result[0]->score . '%';
        }
        ?></strong></p>

    <?php else : ?>

                                            <p><?php _e('Please check back to see if you are registered as a teacher to teach Math.', 'iii-dictionary') ?></p>

    <?php endif ?>

                                    </div>

                <?php endif ?>

<?php if ($math_test1_result[0]->graded) : ?>

                                    <div class="col-sm-12">

    <?php if ($math_test1_result[0]->score >= $teacher_math_test_score_threshold || ($math_test1_result[0]->score < $teacher_math_test_score_threshold && is_mw_qualified_teacher($user->ID, 1) )) : ?>

                                            <span class="text-success"><?php _e('Accepted', 'iii-dictionary') ?></span>

    <?php else : ?>

                                            <span class="text-danger"><?php _e('Sorry, you did not pass. Would you like to take the test again?', 'iii-dictionary') ?></span>

                                            <div class="row" style="margin-top: 10px">

                                                <div class="col-sm-5">

                                                    <button type="submit" name="re-take-math-test1" class="btn btn-default btn-block grey form-control"><?php _e('Take this test again', 'iii-dictionary') ?></button>

                                                </div>

                                            </div>

    <?php endif ?>

                                    </div>

<?php endif ?>



                                <div class="clearfix"></div>

                                <ul> 

                                    <li><strong>2)</strong> <?php _e('Once we review your editing, we will notify you on this site on how to proceed to the next step.', 'iii-dictionary') ?></li>

                                </ul>

                                <div class="col-sm-5" style="margin-top: 15px">

                                    <label><?php _e('Auto-graded Test qualification to teacher K-to-12', 'iii-dictionary') ?></label>

<?php
if (empty($math_test2_result)) :

    if (is_mw_qualified_teacher($user->ID, 1)) {
        ?>

                                            <div class="col-sm-12" >

                                                <p ><?php _e('Your score:', 'iii-dictionary') ?> <strong class="text-success">

        <?php echo 'Accepted by admin';
        ?></strong></p>

                                                <span class="text-success"><?php _e('Accepted', 'iii-dictionary') ?></span>

                                            </div>

    <?php } else { ?>



                                            <button type="submit" name="take-math-test2" class="btn btn-default btn-block grey form-control"<?php echo ($math_test1_result[0]->score < $teacher_math_test_score_threshold || is_mw_qualified_teacher(1) == true) ? ' disabled' : '' ?>><?php _e('Take this test', 'iii-dictionary') ?></button>

    <?php }endif ?>

                                </div>

                                    <?php if (!empty($math_test2_result)) : ?>

                                    <div class="col-sm-12">

                                        <?php if ($math_test2_result[0]->graded) : ?>

                                            <p><?php _e('Your score:', 'iii-dictionary') ?> <strong 

        <?php
        if (($math_test2_result[0]->score < $teacher_math_test_score_threshold && is_mw_qualified_teacher($user->ID, 1)) || $math_test2_result[0]->score >= $teacher_math_test_score_threshold) {

            echo 'class="text-success"';
        } else {

            echo 'class="text-danger"';
        }
        ?>>

                                                    <?php
                                                    if ($math_test2_result[0]->score < $teacher_math_test_score_threshold && is_mw_qualified_teacher($user->ID, 1)) {

                                                        echo 'Accepted by admin';
                                                    } else {

                                                        echo $math_test2_result[0]->score . '%';
                                                    }
                                                    ?></strong></p>

                                                <?php else : ?>

                                            <p><?php _e('Please check back to see if you are registered as a teacher to teach Math.', 'iii-dictionary') ?></p>

                                                <?php endif ?>

                                    </div>

                                            <?php endif ?>

                                    <?php if ($math_test2_result[0]->graded) : ?>

                                    <div class="col-sm-12">

                                        <?php if ($math_test2_result[0]->score >= $teacher_math_test_score_threshold || ($math_test2_result[0]->score < $teacher_math_test_score_threshold && is_mw_qualified_teacher($user->ID, 1))) : ?>

                                            <span class="text-success"><?php _e('Accepted', 'iii-dictionary') ?></span>

                                    <?php else : ?>

                                            <span class="text-danger"><?php _e('Sorry, you did not pass. Would you like to take the test again?', 'iii-dictionary') ?></span>

                                            <div class="row" style="margin-top: 10px">

                                                <div class="col-sm-5">

                                                    <button type="submit" name="re-take-math-test2" class="btn btn-default btn-block grey form-control"><?php _e('Take this test again', 'iii-dictionary') ?></button>

                                                </div>

                                            </div>

    <?php endif ?>

                                    </div>

<?php endif ?>

                            </div>

                        </div>

                    </div>

                </div>

                                <?php if (is_mw_qualified_teacher(null, 1)) : ?>

                    <div class="col-sm-12">

                        <div class="step-block">



                            <div class="step-inst"><?php _e('Now you are ready! Before you select the class to teach, let\'s review some of important rules.', 'iii-dictionary') ?></div>

                            <h2 class="title-border">Teaching Agreement</h2>

                            <div class="box box-red form-group">

                                <div class="scroll-list" style="max-height: 200px; color: #fff">

                                        <?php echo mw_get_option('math-teaching-agreement') ?>

                                </div>

                            </div>

                            <div class="row">

                                <div class="col-sm-6 col-xs-2"></div>

                                <div class="col-sm-1 col-xs-2">

                                    <label>&nbsp;</label>

                                    <div class="radio radio-style1" id="r_agree_english">

                                        <input <?php echo $is_teaching_agreement_agreed_math ? 'checked' : ''; ?> type="radio" >

                                        <label for="rdo-agreed"> </label>

                                    </div>

                                </div>

                                <div class="col-xs-8 col-sm-5">

                                    <div class="form-group">

                                        <label>&nbsp;</label>

                                        <button type="submit" class="btn btn-default btn-block orange form-control" name="teaching-math-agree" ><span class="<?php
                                        if ($is_teaching_agreement_agreed_math) {

                                            echo 'icon-check';
                                        } else {

                                            echo 'icon-cancel';
                                        }
                                        ?>"></span> <?php ($is_teaching_agreement_agreed_math) ? _e('AGREED', 'iii-dictionary') : _e('CLICK TO AGREE', 'iii-dictionary') ?></button>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>



                                                <?php if ($is_teaching_agreement_agreed_math) : ?>



                        <div class="col-sm-12">

                            <div class="step-block">

                                <div class="step-inst"><?php _e('Select worksheet to grade from the list below', 'iii-dictionary') ?></div>

                                <div class="box box-sapphire">



                                    <div class="row">

                                        <div class="col-xs-12">

                                            <div class="scroll-list2" style="max-height: 600px">

                                                <table class="table table-striped table-condensed ik-table1 text-center" id="list-request-chat">

                                                    <thead>

                                                        <tr>

                                                            <th><?php _e('Math category Name', 'iii-dictionary') ?></th>

                                                            <th>&nbsp;</th>

                                                            <th><?php _e('Requested by', 'iii-dictionary') ?></th>

                                                            <th><?php _e('Price /15 minutes', 'iii-dictionary') ?></th>

                                                        </tr>

                                                    </thead>

                                                    <tfoot>

                                                        <tr><td colspan="4"><?php echo $pagination ?></td></tr>

                                                    </tfoot>

                                                    <tbody><?php if (empty($chat_requests->items)) : ?>

                                                            <tr><td colspan="4"><?php _e('No result', 'iii-dictionary') ?></td></tr>

        <?php else : foreach ($chat_requests->items AS $data) : ?>

                                                                <tr>

                                                                    <td><?php echo $data->category ?></td>

                                                                    <td>

                <?php
                switch ($data->status) {

                    case 0 :

                        echo '<a class="tt-btn-accept" href="#" 

										data-session="' . $data->id . '" 

										data-teacher="' . get_current_user_id() . '"

										data-user="' . $data->user_id . '"

										data-sheet="' . $data->sheet_id . '"

										data-url="' . locale_home_url() . '/?' . $data->url . '"

										>' . __('Accept', 'iii-dictionary') . '</a>';

                        break;

                    case 1 :

                        echo '<label class="tt-lbl-taken">' . __('Taken', 'iii-dictionary') . '</label>';

                        break;

                    case 2 :

                        echo '<label class="tt-lbl-complete">' . __('Complete', 'iii-dictionary') . '</label>';

                        break;
                }
                ?>

                                                                    </td>

                                                                    <td><?php echo $data->user ?></td>

                                                                    <td><?php echo '$' . $data->price ?></td>

                                                                </tr>

                                                        <?php
                                                    endforeach;

                                                endif;
                                                ?>

                                                    </tbody>

                                                </table>

                                            </div>

                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>

    <?php endif ?>



<?php endif ?>

            </div>

        </div>

    </div>

</form>

</div>

<script>

    (function ($) {
        $(function () {
            function openNav() {
                $("#mySidenav").removeClass("open");
                $("#mySidenav").removeClass("close");
                $("#mySidenav").addClass("open");
            }

            function closeNav() {
                $("#mySidenav").removeClass("open");
                $("#mySidenav").removeClass("close");
                $("#mySidenav").addClass("close");
            }
            if (window.location.hash != "") {

                var _v = window.location.hash.replace("#", "");

                if (_v == 4) {

//                    $("#step4-collapse").collapse();

                }

                if (_v == 5) {

                    $("#step5-collapse").collapse();

                }

                if (_v == 6) {

                    $("#step6-collapse").collapse();

                }

                if (_v == 56) {

                    $("#step6-collapse").collapse();

                    $("#step5-collapse").collapse();

                }



            }



            $("input[type='text'], input[type='password']").keyup(function () {

                $("#save-btn").prop("disabled", false);

            });

            $("[name='birth-m'], [name='birth-d']").change(function () {

                $("#save-btn").prop("disabled", false);

            });

            $(".language_type").change(function () {

                $("#save-btn").prop("disabled", false);

            });

            $('.check_lb').click(function (e) {

                var checked = $(this).find('input').attr('checked');

                if (checked == 'checked') {

                    $(this).addClass('checked_lb');

                } else {

                    $(this).removeClass('checked_lb');

                }

            });

            $('.r_agree_english').click(function (e) {

                $html = '<span class="icon-check"></span>AGREED';

                $('#rdo-agreed').attr('checked', true);

                $('.r_agree_english').html($html);

                $('#r_agree_english label').addClass('checked_lb');

            });

            $('.r_agree_math').click(function (e) {

                $html = '<span class="icon-check"></span>AGREED';

                $('#rdo-agreed-math').attr('checked', true);

                $('.r_agree_math').html($html);

                $('#r_agree_math label').addClass('checked_lb');

            });



            $(".page-tab").click(function (e) {

                var tab = $(this).attr('data-tab');

                $(".page-tab").removeClass('active');

                $(this).addClass('active');

                if (tab == 'english_class') {

                    $('.box_english').show();

                    $('.box_math').hide();

                } else {

                    $('.box_english').hide();

                    $('.box_math').show();

                }

            });

            $("#my-account-modal").modal('show');
            $("#account").click(function () {
                if ($("#mySidenav").hasClass("open")) {
                    closeNav();
                    $('#menu-left-myaccount li:nth-child(2)').css("margin-top", "0px");
                } else {
                    openNav();
                    $('#menu-left-myaccount li:nth-child(2)').css("margin-top", "150px")
                }
            });
            $("#menu_Taggle").click(function () {
                if ($("#mySidenav").hasClass("open")) {
                    closeNav();
                    $('#menu-left-myaccount li:nth-child(2)').css("margin-top", "0px");
                } else {
                    openNav();
                    $('#menu-left-myaccount li:nth-child(2)').css("margin-top", "150px")
                }

            });
            $("#mySidenav a").click(function () {
                closeNav();
                $('#menu-left-myaccount li:nth-child(2)').css("margin-top", "0px");
            });
            
            
        });

//        $('#checkBoxSearch').delegate('img', 'click', function () {
//
//            $('#cb-korea').removeAttr("checked");
//            $('#cb-chinese').removeAttr("checked");
//            $('#cb-trachinese').removeAttr("checked");
//            $('#cb-japan').removeAttr("checked");
//            $('#cb-vietnam').removeAttr("checked");
//            $('#cb-eng').next().attr('src', "<?php echo get_template_directory_uri(); ?>/library/images/Icon_Un-Check.png");
//            $('#cb-japan').next().attr('src', "<?php echo get_template_directory_uri(); ?>/library/images/Icon_Un-Check.png");
//            $('#cb-vietnam').next().attr('src', "<?php echo get_template_directory_uri(); ?>/library/images/Icon_Un-Check.png");
//            $('#cb-chinese').next().attr('src', "<?php echo get_template_directory_uri(); ?>/library/images/Icon_Un-Check.png");
//            $('#cb-trachinese').next().attr('src', "<?php echo get_template_directory_uri(); ?>/library/images/Icon_Un-Check.png");
//            $(this).attr('src', "<?php echo get_template_directory_uri(); ?>/library/images/Icon_Check-Box-ALL.png");
//            $(this).prev().attr('checked', true);
//        });

    })(jQuery);

</script>

<style>

    .tooltip-charge-student, .tooltip-manage-a-classroom{
        margin-top: 35px !important;
        font-size: 13px !important;
        font-weight: normal !important;
    }
    .tooltip-charge-student, .tooltip-manage-a-classroom a{
        color: #ff9600;

    }

    .checkboxagree{

        width: 28px;

        height: 20px;

        border-radius: 50px;

        

        position: relative;
    }

    .sidenav {
        /*box-shadow: 5px 0px 0px 0px #F0F0F0;*/
        width: 171px;
        border-right: 1px solid #BBBBBB;
        width: 0;
        position: absolute;
        z-index: 1;
        margin-left: 43px;
        background-color: #fff;
        overflow-x: hidden;
        transition: 0.5s;
    }

    .sidenav a {
        padding: 0px 8px 8px 1px;
        text-decoration: none;
        font-size: 25px;
        color: #818181;
        display: block;
        transition: 0.3s;
    }

    .sidenav a:hover {
        color: #f1f1f1;
    }

    .sidenav .closebtn {
        position: absolute;
        top: 0;
        right: 25px;
        font-size: 36px;
        margin-left: 50px;
    }
    .open {
        width: 200px;
        box-shadow: 5px 0px 0px 0px #F0F0F0; 
    }

    .selectboxit,.selectboxit-options{
        background: #ffffff !important;
        color: #4b4b4b !important;

    }
    .selectboxit-text{
        font-weight: normal !important;
    }
    .selectboxit-option-anchor{
        font-weight: normal !important;
    }
    .selectboxit-default-arrow{
        border-top-color:#4b4b4b !important;
    }


    /*  #time+ .selectboxit-container .selectboxit-options,
    #time + .selectboxit-container .selectboxit,
    #time + .selectboxit-btn.selectboxit-enabled:hover, 
    #time + .selectboxit-btn.selectboxit-enabled:focus, 
    #time + .selectboxit-btn.selectboxit-enabled:active {
        background-color: #e1f4fd  !important;
    }*/

    #month .selectboxit-btn.selectboxit-enabled:active,

    #month .selectboxit-container .selectboxit-options :hover,
    #month .selectboxit-container .selectboxit-options :focus,
    #date .selectboxit-btn.selectboxit-enabled:active,
    #date .selectboxit-btn.selectboxit-enabled:hover,

    #date .selectboxit-container .selectboxit-options :hover,
    #date .selectboxit-container .selectboxit-options :focus
    {
        background-color: #e1f4fd  !important;
    }
    #month .selectboxit-container .selectboxit-options :hover,
    #month .selectboxit-container .selectboxit-options :focus,
    #date .selectboxit-container .selectboxit-options :hover,
    #date .selectboxit-container .selectboxit-options :focus
    {
        color: #4d4d4d !important;
    }
    .cb-type{
        text-align: left;
        padding: 0!important;
    }
    .cb-type label{
        color: #4b4b4b;
        font-size: 15px;

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
        outline: none;
        position: relative;
        z-index: 1;

        text-decoration: none;
    }
    .option-input:hover {
        background: #fff;
    }
    .option-input:checked {

    }
    .option-input:checked::before {
        height: 100%;
        width: 100%;
        position: absolute;
        content: "";
        background: url('http://ikteacher.com/wp-content/themes/ik-learn/library/images/Icon_Check-Box-ALL.png') no-repeat center;
        display: inline-block;

        display: block;
        background-size: contain;

    }
    .box.box-arrow-down:after {
        content: "";
        background: url(../images/Icon_Un-Check.png) no-repeat center;
        width: 100%;
        height: 36px;
        display: block;
        position: absolute;
        background-size: contain;
        left: 0;
    }
    .option-input:checked::after {
        content: '';
        display: block;
        position: relative;
        z-index: 100;
    }
</style>

<?php
get_math_footer();

