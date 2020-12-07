<?php
if (!is_user_logged_in()) {
    ik_enqueue_messages(__('Please log in and get more function and content.', 'iii-dictionary'), 'error');
    wp_redirect(locale_home_url() . '/?r=login');
    exit;
}
    $is_teaching_agreement_agreed_math = ik_is_teaching_agreement_agreed(math);
    $is_registered_teacher = is_mw_registered_teacher(get_current_user_id(), 1);

wp_enqueue_script('nodejs-socket-teaching', 'http://107.180.78.211:8000/socket.io/socket.io.js', array(), '1.0.0', true);
wp_enqueue_script('splitter', plugins_url('iii-dictionary/chat/js/jquery.splitter-0.14.0.js'), array(), '1.0.0', true );
wp_enqueue_script('chat-main-js-teaching', plugins_url('iii-dictionary/chat/js/chatMain.js'), array(), '1.0.0', true);

$is_math_panel = true;
$_page_title = __('Earn Money by Teaching Math', 'iii-dictionary');

$route = get_route();
if (empty($route[1])) {
    $active_tab = 'tutor-math';
} else {
    $active_tab = $route[1];
}
//TAB FOR MATH IKLEARN
$math_tab_options = array(
    'items' => array(
        //'register' => array('url' => locale_home_url() . '/?r=teaching-math/register', 'text' => __('Register', 'iii-dictionary')),
        'tutor-math' => array('url' => locale_home_url() . '/?r=teaching-math/tutor-math', 'text' => __('Tutor Math', 'iii-dictionary')),
        'request-payment' => array('url' => locale_home_url() . '/?r=teaching-math/request-payment', 'text' => __('Request Payment', 'iii-dictionary'))
    ),
    'active' => $active_tab
);
$current_user = wp_get_current_user();

switch ($active_tab) {
    // Register Tab
    case 'register':
        $tab_title = __('Registration Agreement', 'iii-dictionary');
        $tab_info_url = get_info_tab_cloud_url('Popup_info_25.jpg');

        // teacher register
        if (isset($_POST['i-agree']) || isset($_POST['update-teacher'])) {
            if (MWDB::update_user($current_user, $is_math_panel)) {
                wp_redirect(locale_home_url() . '/?r=teaching');
                exit;
            }
        }

        // teacher agreed to the new agreement
        if (isset($_POST['agree-new-agreement'])) {
            $agreement_update_date = mw_get_option('agreement-update-date');
            update_user_meta($current_user->ID, 'teacher_agreement_ver', $agreement_update_date);
            wp_redirect(locale_home_url() . '/?r=teaching');
            exit;
        }
        // math teacher agreed to the new agreement
        if (isset($_POST['agree-new-agreement-math'])) {
            $agreement_update_date = mw_get_option('agreement-update-date');
            update_user_meta($current_user->ID, 'math_teacher_agreement_ver', $agreement_update_date);
            wp_redirect(locale_home_url() . '/?r=teaching');
            exit;
        }

        break;
    // Request Payment Tab
    case 'request-payment':
        if (is_mw_qualified_teacher($current_user->ID, 1) && ik_is_teaching_agreement_agreed(1)) {
            $tab_title = __('My Earnings', 'iii-dictionary');
            $tab_info_url = get_info_tab_cloud_url('Popup_info_29.jpg');
            $current_user_points = ik_get_user_points($current_user->ID);
            $point_ex_rate = mw_get_option('point-exchange-rate');

            // user want to request payment
            if (isset($_POST['request-payment'])) {
                // validate data
                $form_valid = true;
                if (empty($_POST['receiving-method'])) {
                    ik_enqueue_messages(__('Please choose a Receiving method.', 'iii-dictionary'), 'error');
                    $form_valid = false;
                }

                if (empty($_POST['amount-request']) || !is_numeric($_POST['amount-request']) || $_POST['amount-request'] < 1) {
                    ik_enqueue_messages(__('Amount requested is invalid.', 'iii-dictionary'), 'error');
                    $form_valid = false;
                } else {
                    $amount = $_POST['amount-request'] * 100 / $point_ex_rate;
                    if ($current_user_points < $amount) {
                        ik_enqueue_messages(__('You don\'t have enough points for this request.', 'iii-dictionary'), 'notice');
                        $form_valid = false;
                    }
                }

                if (empty($_POST['receiving-email']) || !is_email($_POST['receiving-email'])) {
                    ik_enqueue_messages(__('Email for receiving payment is invalid.', 'iii-dictionary'), 'error');
                    $form_valid = false;
                }

                if ($form_valid) {
                    $data = array(
                        'requested_by' => $current_user->ID,
                        'receiving_method_id' => $_POST['receiving-method'],
                        'status_id' => TEACHER_REQ_PENDING,
                        'amount' => $amount,
                        'receiving_email' => $_POST['receiving-email'],
                        'requested_on' => date('Y-m-d H:i:s', time())
                    );

                    if (ik_deduct_user_points($amount, $current_user->ID)) {
                        if (MWDB::store_payment_request($data)) {
                            ik_enqueue_messages(__('Your payment request has been sent.', 'iii-dictionary'), 'success');
                            wp_redirect(locale_home_url() . '/?r=teaching/request-payment');
                            exit;
                        } else {
                            ik_enqueue_messages(__('An error occured, cannot request payment.', 'iii-dictionary'), 'error');
                        }
                    } else {
                        ik_enqueue_messages(__('You don\'t have enough points for this request.', 'iii-dictionary'), 'error');
                    }
                }
            }

            $receiving_methods = MWDB::get_payment_receiving_methods();

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
            $user_transactions = MWDB::get_user_point_transactions($filter, $filter['offset'], $filter['items_per_page']);
            $total_pages = ceil($user_transactions->total / $filter['items_per_page']);

            $pagination = paginate_links(array(
                'format' => '?page=%#%',
                'current' => $current_page,
                'total' => $total_pages
            ));

            break;
        } else {
            $title = __('Message', 'iii-dictionary');
            $body = __('You need to pass the qualification test to access this panel.', 'iii-dictionary');
            $return_url = locale_home_url() . '/?r=my-account#6';
            if ($is_registered_teacher == FALSE) {
                $title = __('Registration Required', 'iii-dictionary');
                $body = __('Please register as the teacher before Earn Money by Teaching Math in this panel.', 'iii-dictionary');
                $return_url = locale_home_url() . '/?r=my-account#4';
            }
            set_lockpage_dialog($title, $body, $return_url);
        }
    case 'tutor-math' :
        if (is_mw_qualified_teacher($current_user->ID, 1) && ik_is_teaching_agreement_agreed(1)) {
            $tab_title = __('Follow the steps to teach classes', 'iii-dictionary');
            $tab_info_url = get_info_tab_cloud_url('//');

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
            if (isset($_POST['i-agree-math'])) {
                ik_agree_teaching_agreement(math);

                wp_redirect(locale_home_url() . '/?r=teaching/tutor-math');
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
            foreach ($chat_requests->items AS $data){
                
                $date = date($data->datetime);
                $day_1 = date('Y-m-d', $date) ;
                $day_2 = date('Y-m-d') ; //current date

                $days = (strtotime($day_2) - strtotime($date)) / (60 * 60 * 24);
                if($days>=1){
                MWDB::update_chat_date($data->id);
                }
            }
            $chat_requests = MWDB::get_chat_session_requests($filter, $filter['offset'], $filter['items_per_page']);
            $total_pages = ceil($chat_requests->total / $filter['items_per_page']);

            $pagination = paginate_links(array(
                'format' => '?page=%#%',
                'current' => $current_page,
                'total' => $total_pages
            ));


            break;
        } else {
            $title = __('Message', 'iii-dictionary');
            $body = __('Please Click on Teaching Agreement to Continue.', 'iii-dictionary');
            $return_url = locale_home_url() . '/?r=my-account#6';
            if ($is_registered_teacher == FALSE) {
                $title = __('Registration Required', 'iii-dictionary');
                $body = __('Please register as the teacher before Earn Money by Teaching Math in this panel.', 'iii-dictionary');
                $return_url = locale_home_url() . '/?r=my-account#4';
            }

            set_lockpage_dialog($title, $body, $return_url);
        }
}

//$is_mw_registered_teacher = (!$is_math_panel) ? is_mw_registered_teacher() : is_mw_registered_teacher(null, 1);
$is_mw_registered_teacher = is_mw_registered_teacher();
$is_teaching_agreement_uptodate_math = ik_is_teacher_agreement_uptodate('MATH');

if (!is_mw_qualified_teacher($current_user->ID, 1)) {
    $title = __('Message', 'iii-dictionary');
    $body = __('You need to pass the qualification test to access this panel.', 'iii-dictionary');
    $return_url = locale_home_url() . '/?r=my-account#6';
    if ($is_registered_teacher == FALSE) {
        $title = __('Registration Required', 'iii-dictionary');
        $body = __('Please register as the teacher before Earn Money by Teaching Math in this panel.', 'iii-dictionary');
        $return_url = locale_home_url() . '/?r=my-account#4';
    }
    set_lockpage_dialog($title, $body, $return_url);
}
?>
<?php
get_math_header($_page_title, 'red-brown');
get_dict_page_title($_page_title, '', '', $math_tab_options, $tab_info_url, false, array(), $tab_extras_options);
?>

<form action="<?php echo locale_home_url() . '/?r=teaching-math/' . $active_tab ?>" method="post" id="main-form" enctype="multipart/form-data">
    <div class="row">
        <div class="col-sm-12">
            <h2 class="title-border"><?php echo $tab_title ?></h2>
        </div>

<?php switch ($active_tab) :
    case 'request-payment':
        ?>

                <div class="col-sm-12">
                    <div class="form-group">
                        <label><?php _e('Your current points is', 'iii-dictionary') ?></label>
                        <div class="box box-green">
                            <h3 class="positive-amount"><?php echo number_format($current_user_points, 2) ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12">
                    <h3><?php _e('Receiving Payments', 'iii-dictionary') ?></h3>
                </div>
                <div class="col-sm-12">
                    <div class="box box-red box-arrow-down">
                        <p><?php _e('How would you like your payment?', 'iii-dictionary') ?></p>
                        <div class="row">
        <?php
        if (is_mw_qualified_teacher($current_user->ID, 1)) {
            foreach ($receiving_methods as $method) :
                ?>
                                    <div class="col-sm-6">
                                        <div class="radio radio-style1 radio-inline">
                                            <input id="receiving-method<?php echo $method->id ?>" type="radio" name="receiving-method" value="<?php echo $method->id ?>">
                                            <label for="receiving-method<?php echo $method->id ?>" class="subscription-type"><?php echo $method->name ?></label>
                                        </div>
                                    </div>
            <?php
            endforeach;
        }
        ?>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12">
                    <div class="box box-red box-arrow-down">
                        <p>Amount requested <em class="text-info">(Exchange rate: <?php echo $point_ex_rate ?>pts = 1$)</em></p>
                        <div class="box">
                            <div class="amount-request">
                                <input type="text" name="amount-request" value="0" autocomplete="off"> 00.00 pts
                            </div>
                        </div>
                        <p class="text-alert"><strong><?php _e('Note:', 'iii-dictionary') ?></strong> <?php _e('You can redeem your balance in increments of 100.00', 'iii-dictionary') ?></p>
                    </div>
                </div>
                <div class="col-sm-12">
                    <div class="box box-red form-group">
                        <p><?php _e('Email address for receiving a payment (paypal or gift card)', 'iii-dictionary') ?></p>
                        <input type="text" name="receiving-email" class="form-control" size="25" style="width: auto" value="<?php echo get_user_meta($current_user->ID, 'email_paypal', true); ?>">
                    </div>
                </div>
                <div class="col-sm-5 col-sm-offset-7">
                    <button type="submit" name="request-payment" class="btn btn-default btn-block orange form-control"><span class="icon-check"></span> <?php _e('Request Payment', 'iii-dictionary') ?></button>
                </div>

                <div class="col-sm-12">
                    <h2 class="title-border"><?php _e('Details of your earnings less expenses', 'iii-dictionary') ?></h2>
                </div>
                <div class="col-sm-12">
                    <div class="box box-green">
                        <table class="table table-striped table-condensed ik-table1 text-center" id="list-sheets">
                            <thead>
                                <tr>
                                    <th><?php _e('Date', 'iii-dictionary') ?></th>
                                    <th><?php _e('Transaction', 'iii-dictionary') ?></th>
                                    <th class="hidden-xs"><?php _e('Amount', 'iii-dictionary') ?></th>
                                    <th><?php _e('Note', 'iii-dictionary') ?></th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr><td colspan="5"><?php echo $pagination ?></td></tr>
                            </tfoot>
                            <tbody><?php if (empty($user_transactions->items)) : ?>
                                    <tr><td colspan="4"><?php _e('No transactions', 'iii-dictionary') ?></td></tr>
        <?php
        else :
            $total_point = 0;
            foreach ($user_transactions->items as $item) :
                $total_point = $total_point + $item->amount;
                ?>
                                        <tr>
                                            <td><?php echo ik_date_format($item->txn_date, 'm/d/Y H:i:s') ?></td>
                                            <td><?php echo $item->txn_type ?></td>
                                            <td class="<?php echo in_array($item->txn_type_id, array(POINT_TXN_GRADING_WORKSHEET, POINT_TXN_GIFT)) ? 'positive' : 'negative' ?>-amount"><?php echo $item->amount ?> pts</td>
                                            <td><?php echo $item->note ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <tr>
                                        <td></td>
                                        <td><?php _e('Balance ', 'iii-dictionary') ?></td>
                                        <td><?php echo $total_point; ?> <?php if ($total_point > 1) _e('points', 'iii-dictionary');
                        else _e('point', 'iii-dictionary'); ?></td>
                                        <td></td>
                                    </tr>
                                <?php endif ?>
                            </tbody>
                        </table>
                    </div>
                </div>

        <?php break;
    case 'tutor-math' :
        ?>
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
                                            <th><?php _e('Earned / 15 minutes', 'iii-dictionary') ?></th>
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
                        if(gearside_is_user_online($data->user_id)){
                            $online=1;
                            echo '<a class="tt-btn-accept" href="#" 
										data-session="' . $data->id . '" 
										data-teacher="' . get_current_user_id() . '"
										data-user="' . $data->user_id . '"
										data-online="' .$online . '"
										data-sheet="' . $data->sheet_id . '"
										data-user-name="' . $data->user . '"
										data-url="' . locale_home_url() . '/?' . $data->url . '"
										>' . __('Requesting', 'iii-dictionary') . '</a>';
                        }else{
                            $online=0;
                            echo '<a class="tt-btn-accept"  href="#" 
										data-session="' . $data->id . '" 
										data-teacher="' . get_current_user_id() . '"
										data-user="' . $data->user_id . '"
										data-online="' .$online . '"
										data-sheet="' . $data->sheet_id . '"
										data-user-name="' . $data->user . '"
										data-url="' . locale_home_url() . '/?' . $data->url . '"
										>' . __('Requesting', 'iii-dictionary') . '</a>';
                        }
                        break;
                    case 1 :
                        echo '<label class="tt-lbl-taken">' . __('Taken', 'iii-dictionary') . '</label>';
                        break;
                    case 2 :
                                switch (intval($data->quit_status)) {
                                        case 0 :
                                            echo '<label class="tt-lbl-dropped">' . __('Dropped', 'iii-dictionary') . '</label>';
                                            break;
                                        case 1 :
                                            echo '<label class="tt-lbl-completing">' . __('Completing', 'iii-dictionary') . '</label>';
                                            break;
                                        case 2 :
                                            echo '<label class="tt-lbl-dropping">' . __('Dropping', 'iii-dictionary') . '</label>';
                                            break;
                                }
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
                                        <?php break; ?>
<?php endswitch ?>

    </div>
</form>

        <?php if ($active_tab == 'purchase-worksheet') : ?>
    <div id="purchase-points-dialog" class="modal fade modal-red-brown">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <a href="#" data-dismiss="modal" aria-hidden="true" class="close"></a>
                    <h3><?php _e('Purchase Points', 'iii-dictionary') ?></h3>
                </div>
                <form method="post" action="<?php echo home_url_ssl() ?>/?r=payments">
                    <input type="hidden" name="sub-type" value="4">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label><?php _e('Number of Points', 'iii-dictionary') ?></label>
                                    <input type="number" class="form-control" name="no-of-points" id="no-of-points" min="1">
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <div class="box" style="text-align: right">
    <?php _e('Total amount:', 'iii-dictionary') ?> <span class="currency">$ <span id="total-amount-points">0</span></span>
                                </div>
                            </div>
                        </div>				
                    </div>
                    <div class="modal-footer">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <button type="submit" name="add-to-cart" class="btn btn-block orange confirm"><span class="icon-cart"></span><?php _e('Check out', 'iii-dictionary') ?></button>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <a href="#" data-dismiss="modal" aria-hidden="true" class="btn btn-block grey secondary"><span class="icon-cancel"></span><?php _e('Cancel', 'iii-dictionary') ?></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif ?>

<table id="questions-table" style="display: none"></table>

<script>var ptsr = <?php echo mw_get_option('point-exchange-rate') ?>;</script>
<script>
	var __SID = '';
	var __IS = <?php echo (is_mw_qualified_teacher()) ? 1 : 0 ?>;
	var __US = "<?php echo (is_user_logged_in()) ? get_current_user_id() : gethostname() ?>";
	var __NAME =  "<?php echo (!empty($current_user)) ? $current_user->display_name : '' ?>";
	var __EMAIL =  "<?php echo (!empty($current_user)) ? $current_user->user_email : '' ?>";
	var __PRICE = <?php echo mw_get_option('math-chat-price'); ?>;
</script>
<?php if (!$is_math_panel) : ?>
    <?php get_dict_footer() ?>
<?php else : ?>
    <?php get_math_footer() ?>
<?php endif ?>