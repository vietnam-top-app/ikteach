<?php
$route = get_route();
$main_categories = MWDB::get_grades(array('type' => 'MATH', 'level' => 0, 'admin_only' => 1, 'orderby' => 'ordering', 'order-dir' => 'asc'));
$levels = MWDB::get_grades(array('type' => 'MATH', 'level' => 1, 'orderby' => 'ordering', 'order-dir' => 'asc'));
$sublevels = MWDB::get_grades(array('type' => 'MATH', 'level' => 2, 'orderby' => 'ordering', 'order-dir' => 'asc'));
if (isset($route[1])) {
    switch ($route[1]) {
        case 'elearner': $active_menu = 68;
            break;
        case 'collegiate': $active_menu = 67;
            break;
        case 'medical': $active_menu = 66;
            break;
        case 'intermediate': $active_menu = 65;
            break;
        case 'elementary': $active_menu = 64;
            break;
    }
}

$current_user = wp_get_current_user();
$is_user_logged_in = is_user_logged_in();

$cart_items = get_cart_items();

$locale_code = explode('_', get_locale());

// enqueue math specific css and js
function ik_enqueue_math_css() {
    wp_enqueue_style('common-math', get_stylesheet_directory_uri() . '/library/css/common-math.css');
    wp_enqueue_script('common-math', get_stylesheet_directory_uri() . '/library/js/common-math.js', array('common-js'));
}

add_action('wp_enqueue_scripts', 'ik_enqueue_math_css', '999');

$url = wp_get_referer();
$str = explode('=', $url);
if (isset($str[1])) {
    $r = explode('/', $str[1]);
    $login = $r[0];
} else {
    $login = 'login';
}
if ($login == 'login') {
    $redirct = '';
} else {
    $redirct = $url;
}

//update user
?>
<!DOCTYPE html>

<!--[if lt IE 7]><html <?php language_attributes(); ?> class="no-js lt-ie9 lt-ie8 lt-ie7"><![endif]-->
<!--[if (IE 7)&!(IEMobile)]><html <?php language_attributes(); ?> class="no-js lt-ie9 lt-ie8"><![endif]-->
<!--[if (IE 8)&!(IEMobile)]><html <?php language_attributes(); ?> class="no-js lt-ie9"><![endif]-->
<!--[if gt IE 8]><!--> <html <?php language_attributes(); ?> class="no-js"><!--<![endif]-->

    <head>
        <meta charset="utf-8">

        <?php // force Internet Explorer to use the latest rendering engine available   ?>
        <meta http-equiv="X-UA-Compatible" content="IE=edge">

        <title><?php wp_title(''); ?></title>

        <?php // <meta name="HandheldFriendly" content="True">   ?>
        <meta name="MobileOptimized" content="320">
        <meta name="viewport" content="width=device-width, initial-scale=1"/>

        <?php // icons & favicons (for more: http://www.jonathantneal.com/blog/understand-the-favicon/)   ?>
        <link rel="apple-touch-icon" href="<?php echo get_template_directory_uri(); ?>/library/images/apple-touch-icon.png">
        <link rel="icon" href="<?php echo get_template_directory_uri(); ?>/favicon.png">
        <!--[if IE]>
                <link rel="shortcut icon" href="<?php echo get_template_directory_uri(); ?>/favicon.ico">
        <![endif]-->
        <?php // or, set /favicon.ico for IE10 win   ?>
        <meta name="msapplication-TileColor" content="#f01d4f">
        <meta name="msapplication-TileImage" content="<?php echo get_template_directory_uri(); ?>/library/images/win8-tile-icon.png">
        <meta name="theme-color" content="#121212">

        <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>">

        <?php // wordpress head functions  ?>
        <?php wp_head(); ?>
        <?php // end of wordpress head  ?>

        <?php if (!is_admin_panel()) : ?>
            <script src="<?php echo get_template_directory_uri(); ?>/library/js/iklearn.js"></script>
        <?php endif ?>
        <script>var home_url = "<?php echo locale_home_url() ?>", LANG_CODE = "<?php echo $locale_code[0] ?>", isuserloggedin = <?php echo $is_user_logged_in ? 1 : 0 ?>;</script>

        <?php // drop Google Analytics Here  ?>
        <?php // end analytics  ?>		

        <?php if (is_admin_panel()) : ?>
            <style type="text/css">
                a.sign-up-link {
                    pointer-events: none !important;
                    cursor: default !important;
                    color: #999 !important;
                }
            </style>
        <?php endif ?>

        <?php if (isset($active_menu)) : ?>
            <style type="text/css">
                #main-nav nav .main-menu li#menu-item-<?php echo $active_menu ?> a {
                    color: #FFF;
                }
            </style>
        <?php endif ?>

        <?php
        if (isset($_GET['boologin'])) {
            ?>
            <script>(function ($) {
                    $(function () {
                        if (localStorage.getItem('boologin') == null) {
                            console.log(localStorage.getItem('boologin'));
                            $("#myModal-login").modal("show");
                            localStorage.setItem('boologin', 1);
                        }
                    });
                })(jQuery);</script>
            <?php
        }
        ?>

    </head>

    <body <?php body_class('body-math'); ?> itemscope itemtype="http://schema.org/WebPage">
        <div class="mdl-create modal-yesno" id="modal-alert-yesno" >
            <div style="margin: 0 auto;width: 905px">  
                <h5 class="box-title-1" style="padding-left: 83px;padding-right: 228px"></h5>
                <button style="margin-top: 4px;" id="yes-delete" type="submit" class="btn-blue-yes btn-create-lesson nopadding-r style-btn-del" data-del="" name="">Yes</button>
                <button id="no-delete" type="submit" style="padding:0px !important; margin-left: 10px !important;" class="btn-cancel-grey btn-create-lesson nopadding-r style-btn-del" name="">No</button>
            </div>
        </div>
        <div class="mdl-create modal-yesno" id="modal-alert" >          
            <div style="margin: 0 auto;width: 900px">
                <h5 class="box-title-1" style="padding-left: 80px;padding-right: 150px"></h5>
                <button style="margin-top: 4px;" id="done-btn" type="submit" class="btn-blue-yes btn-create-lesson  nopadding-r" name=""></span>Done</button>
            </div> 
        </div>
        <div class="modal fade modal-signup" id="my-account-modal" role="dialog" >
            <div class="modal-dialog modal-lg modal-signup">
                <div class="modal-content modal-content-signup">

                    <div class="title-div">
                        <img class="icon-close-classes-created ic-close7" id="close-modal" data-dismiss="modal" src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Close.png">
                        <img id="menu_Taggle" src="<?php echo get_template_directory_uri(); ?>/library/images/Menu_Taggle.png">
                        <span class="modal-title text-uppercase">
                            <a href="#">
                                <img data-dismiss="modal" src="<?php echo get_template_directory_uri(); ?>/library/images/Logo_ikTeach.png">
                            </a>
                        </span>
                    </div>
                    <hr class="line-modal">
                    <div class="modal-body-signup">
                        <div class="section-right">
                            <div class="tab-content">

                                <div id="login-user" class="style-form tab-pane fade in active">
                                    <h3>Login</h3>

                                    <div class="col-md-12">


                                        <form action="<?php echo locale_home_url() ?>/?r=login" name="loginform" method="post">
                                            <div class="row">
                                                <div class="row md-login-r">
                                                    <div class="col-xs-12 col-sm-6 col-md-6">
                                                        <div class="form-group">
                                                            <label for="username"><?php _e('Username (e-mail address)', 'iii-dictionary') ?></label>
                                                            <input type="text" class="form-control" id="username" name="log" value="">
                                                        </div>     
                                                    </div>
                                                    <div class="col-xs-12 col-sm-6 col-md-6">
                                                        <div class="form-group">
                                                            <label for="password"><?php _e('Password', 'iii-dictionary') ?></label>
                                                            <input type="password" class="form-control" id="password" name="pwd" value="">
                                                        </div>     
                                                    </div>
                                                    <div class="clearfix" style="margin-bottom: 20px;"></div>
                                                    <div class="col-sm-6 col-md-6">
                                                        <div class="form-group">

                                                            <button type="submit" class="btn-orange form-control" name="wp-submit"></span><?php _e('Login', 'iii-dictionary') ?></button>
                                                        </div>     
                                                    </div>
                                                    <div class="col-sm-6 col-md-6">
                                                        <div class="form-group">
                                                            <button type="button" class="btn-cancel-grey sign-up"><?php _e('Create Account', 'iii-dictionary') ?></button>
                                                        </div>

                                                    </div>
                                                    <div class="forgot-pass">

                                                        <div class="form-group forgot-password-form">
                                                            <a class="forgot-password-a lblForgot"><?php _e('Forgot password?', 'iii-dictionary') ?></a>
                                                        </div>

                                                    </div>

                                                </div>
                                                <div class="clearfix"></div>


                                            </div>  
                                            <input name="redirect_to" value="<?php echo locale_home_url() ?>" type="hidden">
                                        </form>


                                    </div>



                                </div>

                                <div id="lost-password" class="hidden style-form tab-pane fade in">
                                    <h3>Lost Password</h3>
                                    <div class="">
                                        <form name="lostpasswordform" id="lostpasswordform" action="<?php echo esc_url(network_site_url('?r=login&action=forgotpassword')); ?>" method="post">
                                            <div class="row" style="margin-bottom: 20px;">
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label for="user_login" ><?php _e('Email Address for Receiving a New Password', 'iii-dictionary') ?></label>

                                                        <input type="text" name="user_login" id="user_login" class="form-control" value="">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-xs-12 col-sm-6 col-md-6">
                                                    <div class="form-group">
                                                        <button type="submit" name="wp-submit"  class="btn-orange form-control"><?php esc_attr_e('Receive New Password', 'iii-dictionary') ?>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-6 col-md-6">
                                                    <div class="form-group">
                                                        <button type="submit"  data-dismiss="modal" name="wp-submit"  name="cancel" class="btn-cancel-grey"><?php _e('Cancel', 'iii-dictionary') ?></button>
                                                    </div>

                                                </div>
                                            </div>
                                        </form>
                                    </div>




                                </div>

                                <div id="create-account" class="tab-pane fade in">
                                    <h3>Create Account</h3>
                                    <form method="post" id="createAccount" action="" name="registerform" enctype="multipart/form-data">
                                        <div class="row">
                                            <div class="col-sm-9 col-md-9">
                                                <div class="form-group">
                                                    <label for="user_login"><?php _e('Username (E-mail Address)', 'iii-dictionary') ?></label>
                                                    <input id="user_login" class="form-control" name="user_login" type="text" value="" required>
                                                </div>
                                            </div>
                                            <div class="col-sm-3 col-md-3">
                                                <div class="tooltip tooltip-manage-a-classroom col-xs-12 col-sm-12">

                                                    <a href="#"  id="check-availability" class="check-availability">
                                                        <img style="height: 15px;" src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Questions.png">
                                                        Find out availability
                                                        <span class="icon-loading"></span>
                                                        <span data-toggle="popover" data-placement="bottom" data-container="body"  data-html="true" data-max-width="420px" data-content="If username availability is “not available”, someone has already signed up with the email address you entered.<br>If username is “available”, please continue on with the sign up page."></span>
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <label for="user_password"><?php _e('Create Password', 'iii-dictionary') ?></label>
                                                    <input id="user_password" class="form-control" name="user_password" type="password" value="" required>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <label for="confirm_password"><?php _e('Confirm Password', 'iii-dictionary') ?></label>
                                                    <input id="confirm_password" class="form-control" name="confirm_password" type="password" value="" required>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <label for="first_name"><?php _e('First Name', 'iii-dictionary') ?></label>
                                                    <input id="first_name" class="form-control" name="first_name" type="text" value="" required>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <label for="last_name"><?php _e('Last Name', 'iii-dictionary') ?></label>
                                                    <input id="last_name" class="form-control" name="last_name" type="text" value="" required>
                                                </div>
                                            </div>
                                            <div class="col-sm-12 col-md-12" >
                                                <div class="form-group">
                                                    <label><?php _e('Date of Birth', 'iii-dictionary') ?> <small>(month/day/year)</small></label>
                                                    <div class="row tiny-gutter">
                                                        <div class="col-xs-12 col-sm-4 col-md-4" id="month">
                                                            <select id="birth_m" class="select-box-it form-control" name="birth-m">
                                                                <?php for ($i = 1; $i <= 12; $i++) : ?>
                                                                    <?php $pad_str = str_pad($i, 2, '0', STR_PAD_LEFT) ?>
                                                                    <option value="<?php echo $pad_str ?>"><?php echo $pad_str ?></option>
                                                                <?php endfor ?>
                                                            </select>
                                                        </div>
                                                        <div class="col-xs-12 col-sm-4 col-md-4" id="date">
                                                            <select id="birth_d" class="select-box-it form-control" name="birth-d">
                                                                <?php for ($i = 1; $i <= 31; $i++) : ?>
                                                                    <?php $pad_str = str_pad($i, 2, '0', STR_PAD_LEFT) ?>
                                                                    <option value="<?php echo $pad_str ?>"><?php echo $pad_str ?></option>
                                                                <?php endfor ?>
                                                            </select>
                                                        </div>
                                                        <div class="col-xs-12 col-sm-4 col-md-4">
                                                            <input id="birth_y" class="form-control" name="birth-y" type="text" value="" placeholder="Year" required>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="profile-pic" style="clear: both;">
                                                <label class="img-profile">Profile Picture (optional)</label>
                                                <div class="col-sm-1 col-md-1">
                                                    <div class="form-group">

                                                        <img src="<?php echo get_template_directory_uri(); ?>/library/images/Profile_Image.png" alt="Profile Picture">
                                                    </div>
                                                </div>
                                                <div class="col-sm-5 col-md-5">
                                                    <div class="form-group" style="margin-top: 15px;">

                                                        <input class="form-control input-file" type="file" id="input-avatar" value="" >
                                                        <div class="form-group">

                                                            <button class="btn-dark-blue" style="background: #cecece;" type="button" name="upload"  onclick="document.getElementById('input-avatar').click();"><?php _e('Browse and Upload', 'iii-dictionary') ?></button>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-sm-6 col-md-6" >
                                                    <div class="form-group" style="margin-top: 15px;">
                                                        <input class="form-control input-path" id="profile-avatar" type="text">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-sm-12 col-md-12">
                                                <label><?php _e('Language', 'iii-dictionary') ?></label>
                                                <div class="form__boolean" id="checkBoxSearch" style="margin-bottom: 10px;">
                                                    <div class="col-md-2 col-xs-4 cb-type2">
                                                        <label>
                                                            <input type="checkbox" class="radio_buttons class_cb_search option-input-2 radio" value="en" name="cb-lang"/>
                                                            English
                                                        </label>
                                                    </div>
                                                    <div class="col-md-2 col-xs-4 cb-type2">
                                                        <label>
                                                            <input type="checkbox" class="radio_buttons required class_cb_search option-input-2 radio" value="ja" name="cb-lang"/>
                                                            Japanese
                                                        </label>
                                                    </div>
                                                    <div class="col-md-2 col-xs-4 cb-type2">
                                                        <label>
                                                            <input type="checkbox"  class="radio_buttons required class_cb_search option-input-2 radio" value="ko" name="cb-lang"/>
                                                            Korean
                                                        </label>
                                                    </div>
                                                    <div class="col-md-2 col-xs-4 cb-type2">
                                                        <label>
                                                            <input type="checkbox" class="radio_buttons required class_cb_search option-input-2 radio" value="zh" name="cb-lang"/>
                                                            Chinese
                                                        </label>
                                                    </div>
                                                    <div class="col-md-2 col-xs-4 cb-type2">
                                                        <label>
                                                            <input type="checkbox"  class="radio_buttons required class_cb_search option-input-2 radio" value="zh-tw" name="cb-lang"/>
                                                            Traditional Chinese
                                                        </label>
                                                    </div>
                                                    <div class="col-md-2 col-xs-4 cb-type2">
                                                        <label>
                                                            <input type="checkbox"  class="radio_buttons required class_cb_search option-input-2 radio" value="vi" name="cb-lang"/>
                                                            Vietnamese
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-8 col-md-8">
                                                <div class="form-group">
                                                    <button class="btn-dark-blue" id="create-acc" style="background: #f7b555; margin-top: 25px;" type="button" name="wp-submit"><?php _e('Create Account', 'iii-dictionary') ?></button>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-4 col-md-4">
                                                <div class="form-group">
                                                    <button class="button-grey cancel-btn" style="background: #cecece; margin-top: 25px !important;" type="submit" name="cancel">
                                                        <?php _e('Cancel', 'iii-dictionary') ?>
                                                    </button>

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
                                                                <div class="box-dis" style="max-height: 200px;">
                                                                    <?php echo mw_get_option('registration-agreement') ?>
                                                                </div>
                                                                <hr style="color: #d6d6d6;">

                                                                <div class="col-sm-12 col-xs-6 col-md-12 agree">
                                                                    <div class="form-group">
                                                                        <input id="rdo-agreed" class="checkboxagree option-input-2"  type="checkbox" name="agree-english-teacher" value="1" >
                                                                        <label for="rdo-agreed" style="font-size: 15px !important; margin-bottom: 0px !important;">I agree to the terms and conditions</label>
                                                                    </div>
                                                                    <hr style="color: #d6d6d6;">
                                                                </div>
                                                                <div id="info">
                                                                    <h3>Personal Information</h3>
                                                                    <div class="col-sm-6 col-md-6 col-xs-12">
                                                                        <div class="form-group">
                                                                            <label>
                                                                                <?php _e('Mobile Phone Number', 'iii-dictionary') ?>
                                                                            </label>
                                                                            <input type="text" class="form-control" name="mobile-number" value="">
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-sm-6 col-md-6 col-xs-12">
                                                                        <div class="form-group">
                                                                            <label>
                                                                                <?php _e('Last School You Attended', 'iii-dictionary') ?>
                                                                            </label>
                                                                            <input type="text" class="form-control" name="last-school" value="">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-sm-6 col-md-6 col-xs-12">
                                                                        <div class="form-group">
                                                                            <label>
                                                                                <?php _e('School You Tought (if any)', 'iii-dictionary') ?>
                                                                            </label>
                                                                            <input type="text" class="form-control" name="previous-school" value="">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-sm-6 col-md-6 col-xs-12">
                                                                        <div class="form-group">
                                                                            <label>
                                                                                <?php _e('Skype ID (for interview)', 'iii-dictionary') ?>
                                                                            </label>
                                                                            <input type="text" class="form-control" name="skype" value="">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-sm-6 col-md-6 col-xs-12">
                                                                        <div class="form-group">
                                                                            <label>
                                                                                <?php _e('Current Profession', 'iii-dictionary') ?>
                                                                            </label>
                                                                            <input type="text" class="form-control" name="profession" value="">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div id="totutor" class="col-md-12">
                                                                <h3>What are you going to tutor?</h3>
                                                                <div class="col-md-12 ">
                                                                    <div class="form-group">
                                                                        <input id="eng-writing"  type="checkbox" class="radio_buttons required class_cb_search option-input-2 radio tutor-regis" name="eng-writing" />
                                                                        <label style="font-size: 15px !important; margin-bottom: 0px !important; margin-left: 5px;  color: #4b4b4b !important; font-weight: normal;">English (Writing Only)</label>
                                                                        <hr style="color: #d6d6d6;">
                                                                    </div>
                                                                    <div id="extend-tutor" class="extend-tutor">
                                                                        <div class="teach-ex col-md-12">
                                                                            <h4>Teaching experience in school</h4>
                                                                            <div class="col-sm-6 col-md-6 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('School Name', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="school-name" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-sm-6 col-md-6 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('School Website link', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="website-link" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-sm-6 col-md-6 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('School Phone Number', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="phone" value="" >
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-sm-6 col-md-6 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('Major to Teach (grade, subject)', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="major-teach" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-6">
                                                                                <label class="col-md-12">
                                                                                    <?php _e('Year of Teaching', 'iii-dictionary') ?>
                                                                                </label>
                                                                                <div class="form-group col-md-5">
                                                                                    <input type="text" class="form-control" name="from-year" value="">
                                                                                </div>
                                                                                <label class="col-md-2" style="padding: 10px 20px !important;">
                                                                                    <?php _e('to', 'iii-dictionary') ?>
                                                                                </label>
                                                                                <div class="form-group col-md-5">
                                                                                    <input type="text" class="form-control" name="to-year" value="">
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        <div class="teach-ex col-md-12">
                                                                            <h4>Teaching experience as a Student</h4>
                                                                            <div class="col-sm-6 col-md-6 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('School Currently Attending to', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="school-attend" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-sm-6 col-md-6 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('School Website link', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="website-link" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-6" style="padding: 0px !important;">
                                                                                <div class="form-group col-md-6">
                                                                                    <label>
                                                                                        <?php _e('Grade', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <select class="select-box-it form-control" name="birth-m">
                                                                                        <option value="1">Grade</option>
                                                                                        <option value="2">Junior</option>
                                                                                    </select>
                                                                                </div>
                                                                                <div class="form-group col-md-6">
                                                                                    <label>
                                                                                        <?php _e('GPA', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="gpa" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-sm-6 col-md-6 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('Major', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="major" value="">
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        <div class="teach-ex col-md-12">
                                                                            <h4>Other Qualification (other than teacher and student)</h4>
                                                                            <div class="col-sm-6 col-md-6 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('Your Academic Qualification', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="qualification" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-sm-6 col-md-6 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('Website link (if any)', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="website-link" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-sm-3 col-md-3 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('Years of Experience', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="experience" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-sm-9 col-md-9 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('Any Other Qualification Information', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="other-qualification" value="">
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="col-md-12 ">
                                                                    <div class="form-group">
                                                                        <input type="checkbox" id="eng-conver"  class="radio_buttons required class_cb_search option-input-2 radio tutor-regis" name="eng-conver" />
                                                                        <label style="font-size: 15px !important; margin-bottom: 0px !important; margin-left: 5px; color: #4b4b4b !important; font-weight: normal;">English (Conversation Only)</label>
                                                                        <hr style="color: #d6d6d6;">
                                                                    </div>
                                                                    <div id="extend-tutor2" class="extend-tutor">
                                                                        <div class="teach-ex col-md-12">
                                                                            <h4>Teaching experience in school</h4>
                                                                            <div class="col-sm-6 col-md-6 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('School Name', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="school-name" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-sm-6 col-md-6 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('School Website link', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="website-link" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-sm-6 col-md-6 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('School Phone Number', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="phone" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-sm-6 col-md-6 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('Major to Teach (grade, subject)', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="major-teach" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-6">
                                                                                <label class="col-md-12">
                                                                                    <?php _e('Year of Teaching', 'iii-dictionary') ?>
                                                                                </label>
                                                                                <div class="form-group col-md-5">
                                                                                    <input type="text" class="form-control" name="from-year" value="">
                                                                                </div>
                                                                                <label class="col-md-2" style="padding: 10px 20px !important;">
                                                                                    <?php _e('to', 'iii-dictionary') ?>
                                                                                </label>
                                                                                <div class="form-group col-md-5">
                                                                                    <input type="text" class="form-control" name="to-year" value="">
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        <div class="teach-ex col-md-12">
                                                                            <h4>Teaching experience as a Student</h4>
                                                                            <div class="col-sm-6 col-md-6 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('School Currently Attending to', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="school-attend" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-sm-6 col-md-6 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('School Website link', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="website-link" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-6" style="padding: 0px !important;">
                                                                                <div class="form-group col-md-6">
                                                                                    <label>
                                                                                        <?php _e('Grade', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <select class="select-box-it form-control" name="birth-m">
                                                                                        <option value="1">Grade</option>
                                                                                        <option value="2">Junior</option>
                                                                                    </select>
                                                                                </div>
                                                                                <div class="form-group col-md-6">
                                                                                    <label>
                                                                                        <?php _e('GPA', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="gpa" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-sm-6 col-md-6 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('Major', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="major" value="">
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        <div class="teach-ex col-md-12">
                                                                            <h4>Other Qualification (other than teacher and student)</h4>
                                                                            <div class="col-sm-6 col-md-6 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('Your Academic Qualification', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="qualification" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-sm-6 col-md-6 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('Website link (if any)', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="website-link" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-sm-3 col-md-3 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('Years of Experience', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="experience" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-sm-9 col-md-9 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('Any Other Qualification Information', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="other-qualification" value="">
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="col-md-12 ">
                                                                    <div class="form-group">
                                                                        <input type="checkbox" id="math-middle" class="radio_buttons required class_cb_search option-input-2 radio tutor-regis" name="eng-middle" />
                                                                        <label style="font-size: 15px !important; margin-bottom: 0px !important; margin-left: 5px; color: #4b4b4b !important; font-weight: normal;">Math (Up to Middle School)</label>
                                                                        <hr style="color: #d6d6d6;">
                                                                    </div>
                                                                    <div id="extend-tutor3" class="extend-tutor label-checkbox">
                                                                        <div class="teach-ex col-md-12">
                                                                            <h4>Teaching experience in school</h4>
                                                                            <div class="col-sm-6 col-md-6 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('School Name', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="school-name" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-sm-6 col-md-6 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('School Website link', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="website-link" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-sm-6 col-md-6 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('School Phone Number', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="phone" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-sm-6 col-md-6 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('Major to Teach (grade, subject)', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="major-teach" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-6">
                                                                                <label class="col-md-12">
                                                                                    <?php _e('Year of Teaching', 'iii-dictionary') ?>
                                                                                </label>
                                                                                <div class="form-group col-md-5">
                                                                                    <input type="text" class="form-control" name="from-year" value="">
                                                                                </div>
                                                                                <label class="col-md-2" style="padding: 10px 20px !important;">
                                                                                    <?php _e('to', 'iii-dictionary') ?>
                                                                                </label>
                                                                                <div class="form-group col-md-5">
                                                                                    <input type="text" class="form-control" name="to-year" value="">
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        <div class="teach-ex col-md-12">
                                                                            <h4>Teaching experience as a Student</h4>
                                                                            <div class="col-sm-6 col-md-6 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('School Currently Attending to', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="school-attend" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-sm-6 col-md-6 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('School Website link', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="website-link" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-6" style="padding: 0px !important;">
                                                                                <div class="form-group col-md-6">
                                                                                    <label>
                                                                                        <?php _e('Grade', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <select class="select-box-it form-control" name="birth-m">
                                                                                        <option value="1">Grade</option>
                                                                                        <option value="2">Junior</option>
                                                                                    </select>
                                                                                </div>
                                                                                <div class="form-group col-md-6">
                                                                                    <label>
                                                                                        <?php _e('GPA', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="gpa" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-sm-6 col-md-6 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('Major', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="major" value="">
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        <div class="teach-ex col-md-12">
                                                                            <h4>Other Qualification (other than teacher and student)</h4>
                                                                            <div class="col-sm-6 col-md-6 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('Your Academic Qualification', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="qualification" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-sm-6 col-md-6 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('Website link (if any)', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="website-link" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-sm-3 col-md-3 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('Years of Experience', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="experience" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-sm-9 col-md-9 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('Any Other Qualification Information', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="other-qualification" value="">
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="col-md-12 " style="margin-bottom: 30px;">
                                                                    <div class="form-group">
                                                                        <input type="checkbox" id="math-any" class="radio_buttons required class_cb_search option-input-2 radio tutor-regis" name="math-any" />
                                                                        <label style="font-size: 15px !important; margin-bottom: 0px !important; margin-left: 5px;  color: #4b4b4b !important; font-weight: normal;">Math (Any Level)</label>
                                                                    </div>
                                                                    <div id="extend-tutor4" class="extend-tutor">
                                                                        <div class="teach-ex col-md-12">
                                                                            <h4>Teaching experience in school</h4>
                                                                            <div class="col-sm-6 col-md-6 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('School Name', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="school-name" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-sm-6 col-md-6 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('School Website link', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="website-link" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-sm-6 col-md-6 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('School Phone Number', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="phone" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-sm-6 col-md-6 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('Major to Teach (grade, subject)', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="major-teach" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-6">
                                                                                <label class="col-md-12">
                                                                                    <?php _e('Year of Teaching', 'iii-dictionary') ?>
                                                                                </label>
                                                                                <div class="form-group col-md-5">
                                                                                    <input type="text" class="form-control" name="from-year" value="">
                                                                                </div>
                                                                                <label class="col-md-2" style="padding: 10px 20px !important;">
                                                                                    <?php _e('to', 'iii-dictionary') ?>
                                                                                </label>
                                                                                <div class="form-group col-md-5">
                                                                                    <input type="text" class="form-control" name="to-year" value="">
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        <div class="teach-ex col-md-12">
                                                                            <h4>Teaching experience as a Student</h4>
                                                                            <div class="col-sm-6 col-md-6 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('School Currently Attending to', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="school-attend" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-sm-6 col-md-6 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('School Website link', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="website-link" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-6" style="padding: 0px !important;">
                                                                                <div class="form-group col-md-6">
                                                                                    <label>
                                                                                        <?php _e('Grade', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <select class="select-box-it form-control" name="birth-m">
                                                                                        <option value="1">Grade</option>
                                                                                        <option value="2">Junior</option>
                                                                                    </select>
                                                                                </div>
                                                                                <div class="form-group col-md-6">
                                                                                    <label>
                                                                                        <?php _e('GPA', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="gpa" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-sm-6 col-md-6 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('Major', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="major" value="">
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        <div class="teach-ex col-md-12">
                                                                            <h4>Other Qualification (other than teacher and student)</h4>
                                                                            <div class="col-sm-6 col-md-6 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('Your Academic Qualification', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="qualification" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-sm-6 col-md-6 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('Website link (if any)', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="website-link" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-sm-3 col-md-3 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('Years of Experience', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="experience" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-sm-9 col-md-9 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('Any Other Qualification Information', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="other-qualification" value="">
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="col-xs-12 col-sm-8 col-md-8" style="margin-bottom: 30px;">
                                                                    <div class="form-group">
                                                                        <button class="btn-dark-blue" style="background: #f7b555;" type="submit" name="send-tutor">
                                                                            <?php _e('Update and Create Account', 'iii-dictionary') ?>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                                <div class="col-xs-12 col-sm-4 col-md-4" style="margin-bottom: 30px;">
                                                                    <div class="form-group">
                                                                        <button class="button-grey cancel-btn" style="background: #cecece; " type="submit" name="cancel">
                                                                            <?php _e('Cancel', 'iii-dictionary') ?>
                                                                        </button>

                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
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
                                                    <?php
                                                    $user_avatar = ik_get_user_avatar($current_user->ID);

                                                    if (!empty($user_avatar)) :
                                                        ?>
                                                        <img src="<?php echo $user_avatar ?>" alt="<?php echo $current_user->display_name ?>">
                                                        <?php
                                                    else :
                                                        ?>
                                                        <img src="<?php echo get_template_directory_uri(); ?>/library/images/Profile_Image.png" alt="Profile Picture">
                                                    <?php
                                                    endif
                                                    ?>                                           
                                                </div>
                                            </div>
                                            <div class="col-sm-10 col-md-10">
                                                <div class="form-group">
                                                    <label>My Name</label>
                                                    <span class="color-black"><?php
                                                        if ($is_user_logged_in) {
                                                            $display_name = get_user_meta($current_user->ID, 'display_name', true);
                                                            if (!empty($display_name) && $display_name != '')
                                                                echo $display_name;
                                                            else
                                                                _e('N/A', 'iii-dictionary');
                                                        } else
                                                            _e('N/A', 'iii-dictionary');
                                                        ?></span>
                                                </div>
                                            </div>
                                            <hr>
                                        </div>
                                        <div class="row line-profile">
                                            <div class="col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <label><?php _e('Points Balance', 'iii-dictionary') ?></label>
                                                    <span class="color-yellow"><?php
                                                        if ($is_user_logged_in)
                                                            _e(ik_get_user_points($current_user->ID));
                                                        else
                                                            _e('N/A', 'iii-dictionary');
                                                        ?> (USD)</span>
                                                </div>
                                                <hr>
                                            </div>
                                            <div class="col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <label><?php _e('Points Earned', 'iii-dictionary') ?></label>
                                                    <span class="color-yellow"><?php
                                                        if ($is_user_logged_in)
                                                            _e(ik_get_user_earned($current_user->ID));
                                                        else
                                                            _e('N/A', 'iii-dictionary');
                                                        ?> (USD)</span>
                                                </div>
                                                <hr>
                                            </div>
                                        </div>
                                        <div class="row line-profile">
                                            <div class="col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <label><?php _e('English (Writting) Tutor Qualification', 'iii-dictionary') ?></label>
                                                    <span class="color-yellow"><?php
                                                        if ($is_user_logged_in && is_mw_registered_teacher($current_user->ID, 0))
                                                            _e('Qualified', 'iii-dictionary');
                                                        else
                                                            _e('Not Qualified Yet', 'iii-dictionary');
                                                        ?></span>
                                                </div>
                                                <hr>
                                            </div>
                                            <div class="col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <label><?php _e('English (Conversation) Tutor Qualification', 'iii-dictionary') ?></label>
                                                    <span class="color-yellow"><?php
                                                        if ($is_user_logged_in && is_mw_qualified_teacher($current_user->ID, 0))
                                                            _e('Qualified', 'iii-dictionary');
                                                        else
                                                            _e('Not Qualified Yet', 'iii-dictionary');
                                                        ?></span>
                                                </div>
                                                <hr>
                                            </div>
                                        </div>
                                        <div class="row line-profile">
                                            <div class="col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <label><?php _e('Math (Up to Middle School) Tutor Qualification', 'iii-dictionary') ?></label>
                                                    <span class="color-yellow"><?php
                                                        if ($is_user_logged_in && is_mw_registered_teacher($current_user->ID, 1))
                                                            _e('Qualified', 'iii-dictionary');
                                                        else
                                                            _e('Not Qualified Yet', 'iii-dictionary');
                                                        ?></span>
                                                </div>
                                                <hr>
                                            </div>
                                            <div class="col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <label><?php _e('Math (Conversation) Tutor Qualification', 'iii-dictionary') ?></label>
                                                    <span class="color-yellow"><?php
                                                        if ($is_user_logged_in && is_mw_qualified_teacher($current_user->ID, 1))
                                                            _e('Qualified', 'iii-dictionary');
                                                        else
                                                            _e('Not Qualified Yet', 'iii-dictionary');
                                                        ?></span>
                                                </div>
                                                <hr>
                                            </div>
                                        </div>
                                        <div class="row line-profile">
                                            <div class="col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <label><?php _e('Email Address (for login)', 'iii-dictionary') ?></label>
                                                    <span class="color-black"><?php
                                                        if ($is_user_logged_in)
                                                            echo $current_user->user_email;
                                                        else
                                                            _e('N/A', 'iii-dictionary');
                                                        ?></span>
                                                </div>
                                                <hr>
                                            </div>
                                            <div class="col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <label><?php _e('Date of Birth (month/date/year)', 'iii-dictionary') ?></label>
                                                    <span class="color-black">
                                                        <?php
                                                        if ($is_user_logged_in) {
                                                            $date_of_birth = get_user_meta($current_user->ID, 'date_of_birth', true);
                                                            if (!empty($date_of_birth) && $date_of_birth != '')
                                                                echo $date_of_birth;
                                                            else
                                                                _e('N/A', 'iii-dictionary');
                                                        }else {
                                                            _e('N/A', 'iii-dictionary');
                                                        }
                                                        ?>
                                                    </span>
                                                </div>
                                                <hr>
                                            </div>

                                        </div>
                                        <div class="row line-profile">

                                            <div class="col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <label><?php _e('Language', 'iii-dictionary') ?></label>
                                                    <span class="color-black">
                                                        <?php
                                                        if ($is_user_logged_in) {
                                                            $language_type = get_user_meta($current_user->ID, 'language_type', true);
                                                            if (!empty($language_type) && $language_type != '') {

                                                                $langs = array(
                                                                    'en' => 'English',
                                                                    'ja' => '日本語',
                                                                    'ko' => '한국어',
                                                                    'vi' => 'Tiếng Việt',
                                                                    'zh' => '中文',
                                                                    'zh-tw' => '中國'
                                                                );
                                                                $languages_t = explode(',', $language_type);
                                                                if (count($languages_t) > 0) {
                                                                    $n = count($languages_t) - 1;
                                                                    for ($i = 0; $i < count($languages_t); $i++) {
                                                                        $key = $languages_t[$i];
                                                                        echo $langs[$key];
                                                                        if (count($languages_t) > 1 && $i < $n)
                                                                            echo ', ';
                                                                    }
                                                                }
                                                            } else
                                                                _e('N/A', 'iii-dictionary');
                                                        }else {
                                                            _e('N/A', 'iii-dictionary');
                                                        }
                                                        ?>                                               
                                                    </span>
                                                </div>
                                                <hr>
                                            </div>
                                            <div class="col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <label><?php _e('Mobile Phone Number', 'iii-dictionary') ?></label>
                                                    <span class="color-black">
                                                        <?php
                                                        if ($is_user_logged_in) {
                                                            $mobile_number = get_user_meta($current_user->ID, 'mobile_number', true);
                                                            if (!empty($mobile_number) && $mobile_number != '')
                                                                echo $mobile_number;
                                                            else
                                                                _e('N/A', 'iii-dictionary');
                                                        }else {
                                                            _e('N/A', 'iii-dictionary');
                                                        }
                                                        ?>
                                                    </span>
                                                </div>
                                                <hr>
                                            </div>
                                        </div>  
                                        <div class="row line-profile">

                                            <div class="col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <label><?php _e('Last School Attended', 'iii-dictionary') ?></label>
                                                    <span class="color-black">
                                                        <?php
                                                        if ($is_user_logged_in) {
                                                            $last_school = get_user_meta($current_user->ID, 'last_school', true);
                                                            if (!empty($last_school) && $last_school != '')
                                                                echo $last_school;
                                                            else
                                                                _e('N/A', 'iii-dictionary');
                                                        }else {
                                                            _e('N/A', 'iii-dictionary');
                                                        }
                                                        ?>
                                                    </span>
                                                </div>
                                                <hr>
                                            </div>
                                            <div class="col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <label><?php _e('Last School You Tought (if any)', 'iii-dictionary') ?></label>
                                                    <span class="color-black">
                                                        <?php
                                                        if ($is_user_logged_in) {
                                                            $previous_school = get_user_meta($current_user->ID, 'previous_school', true);
                                                            if (!empty($previous_school) && $previous_school != '')
                                                                echo $previous_school;
                                                            else
                                                                _e('N/A', 'iii-dictionary');
                                                        }else {
                                                            _e('N/A', 'iii-dictionary');
                                                        }
                                                        ?>
                                                    </span>
                                                </div>
                                                <hr>
                                            </div>
                                        </div> 
                                        <div class="row line-profile">

                                            <div class="col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <label><?php _e('Skype ID', 'iii-dictionary') ?></label>
                                                    <span class="color-black">
                                                        <?php
                                                        if ($is_user_logged_in) {
                                                            $skype_id = get_user_meta($current_user->ID, 'skype_id', true);
                                                            if (!empty($skype_id) && $skype_id != '')
                                                                echo $skype_id;
                                                            else
                                                                _e('N/A', 'iii-dictionary');
                                                        }else {
                                                            _e('N/A', 'iii-dictionary');
                                                        }
                                                        ?>
                                                    </span>
                                                </div>
                                                <hr>
                                            </div>
                                            <div class="col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <label><?php _e('Profession', 'iii-dictionary') ?></label>
                                                    <span class="color-black">
                                                        <?php
                                                        if ($is_user_logged_in) {
                                                            $user_profession = get_user_meta($current_user->ID, 'user_profession', true);
                                                            if (!empty($user_profession) && $user_profession != '')
                                                                echo $user_profession;
                                                            else
                                                                _e('N/A', 'iii-dictionary');
                                                        }else {
                                                            _e('N/A', 'iii-dictionary');
                                                        }
                                                        ?>
                                                    </span>
                                                </div>
                                                <hr>
                                            </div>
                                        </div>                               
                                    </form>
                                </div>

                                <div id="updateinfo" class="tab-pane fade">
                                    <h3>Update Info</h3>
                                    <form method="post" id="myUpdate" action="" name="registerform" enctype="multipart/form-data">
                                        <h4>Account Info</h4>
                                        <div class="row">
                                            <div class="col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <label for="current_email"><?php _e('Current Email Address (for login)', 'iii-dictionary') ?></label>
                                                    <input class="form-control input-current" readonly="" type="text" value="<?php
                                                    if ($is_user_logged_in)
                                                        echo $current_user->user_email;
                                                    else
                                                        _e('N/A', 'iii-dictionary');
                                                    ?>">
                                                </div>
                                            </div>
                                            <div class="col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <label for="new_email"><?php _e('New Email Address (for login)', 'iii-dictionary') ?></label>
                                                    <input id="new_email" class="form-control" name="new_email" type="text" value="">
                                                </div>
                                            </div>
                                            <div class="col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <label for="new_password"><?php _e('New Password', 'iii-dictionary') ?></label>
                                                    <input id="new_password" class="form-control" name="new_password" type="password" value="">
                                                </div>
                                            </div>
                                            <div class="col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <label for="retype_new_password"><?php _e('Retype New Password', 'iii-dictionary') ?></label>
                                                    <input id="retype_new_password" class="form-control" name="retype_new_password" type="password" value="">
                                                </div>
                                            </div>
                                            <div class="col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <label for="current_password"><?php _e('Current Password', 'iii-dictionary') ?></label>
                                                    <input id="current_password" class="form-control" name="current_password" type="password" value="">
                                                </div>
                                            </div>

                                        </div>
                                        <div class="row">
                                            <div class="profile-pic">
                                                <label class="img-profile">Profile Picture (optional)</label>
                                                <div class="col-sm-1 col-md-1">
                                                    <div class="form-group">
                                                        <?php
                                                        $user_avatar = ik_get_user_avatar($current_user->ID);

                                                        if (!empty($user_avatar)) :
                                                            ?>
                                                            <img src="<?php echo $user_avatar ?>" alt="<?php echo $current_user->display_name ?>">
                                                            <?php
                                                        else :
                                                            ?>
                                                            <img src="<?php echo get_template_directory_uri(); ?>/library/images/Profile_Image.png" alt="Profile Picture">
                                                        <?php
                                                        endif
                                                        ?>                                           
                                                    </div>
                                                </div>
                                                <div class="col-sm-5 col-md-5">
                                                    <div class="form-group" style="margin-top: 15px;">

                                                        <input class="form-control input-file" type="file" id="input-image" >
                                                        <div class="form-group">

                                                            <button class="btn-dark-blue" style="background: #cecece;" type="button" name="upload"  onclick="document.getElementById('input-image').click();"><?php _e('Browse and Upload', 'iii-dictionary') ?></button>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-sm-6 col-md-6" >
                                                    <div class="form-group" style="margin-top: 15px;">
                                                        <input class="form-control input-path" id="profile-value" type="text">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-12 col-md-12" style="margin-bottom: 10px; padding: 0px !important;">
                                            <label><?php _e('Language', 'iii-dictionary') ?></label>
                                            <div class="form__boolean" id="checkBoxSearch" style="margin-bottom: 10px;">
                                                <div class="col-md-2 col-xs-4 cb-type2">
                                                    <label>
                                                        <input type="checkbox" class="radio_buttons class_cb_search option-input-2 radio" value="en" name="cb-lang-up"/>
                                                        English
                                                    </label>
                                                </div>
                                                <div class="col-md-2 col-xs-4 cb-type2">
                                                    <label>
                                                        <input type="checkbox" class="radio_buttons class_cb_search option-input-2 radio" value="ja" name="cb-lang-up"/>
                                                        Japanese
                                                    </label>
                                                </div>
                                                <div class="col-md-2 col-xs-4 cb-type2">
                                                    <label>
                                                        <input type="checkbox"  class="radio_buttons class_cb_search option-input-2 radio" value="ko" name="cb-lang-up"/>
                                                        Korean
                                                    </label>
                                                </div>
                                                <div class="col-md-2 col-xs-4 cb-type2">
                                                    <label>
                                                        <input type="checkbox" class="radio_buttons class_cb_search option-input-2 radio" value="zh" name="cb-lang-up"/>
                                                        Chinese
                                                    </label>
                                                </div>
                                                <div class="col-md-2 col-xs-4 cb-type2">
                                                    <label>
                                                        <input type="checkbox"  class="radio_buttons class_cb_search option-input-2 radio" value="zh-tw" name="cb-lang-up"/>
                                                        Traditional Chinese
                                                    </label>
                                                </div>
                                                <div class="col-md-2 col-xs-4 cb-type2">
                                                    <label>
                                                        <input type="checkbox"  class="radio_buttons class_cb_search option-input-2 radio" value="vi" name="cb-lang-up"/>
                                                        Vietnamese
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <h4>Personal Info</h4>

                                        <div class="row">

                                            <div class="col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <?php
                                                    if ($is_user_logged_in) {
                                                        $mobile_number = get_user_meta($current_user->ID, 'mobile_number', true);
                                                        if (!empty($mobile_number) && $mobile_number != '')
                                                            $mobile_n = $mobile_number;
                                                        else
                                                            $mobile_n = __('N/A', 'iii-dictionary');
                                                    }else {
                                                        $mobile_n = __('N/A', 'iii-dictionary');
                                                    }
                                                    ?>
                                                    <label for="current_mobile"><?php _e('Current Mobile Phone Number', 'iii-dictionary') ?></label>
                                                    <input class="form-control input-current" readonly="" type="text" value="<?php echo $mobile_n ?>">
                                                </div>
                                            </div>
                                            <div class="col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <label for="new_mobile"><?php _e('New Mobile Phone Number', 'iii-dictionary') ?></label>
                                                    <input id="new_mobile" <?php if ($is_user_logged_in && ik_check_user_student($current_user->ID)) echo 'readonly=""'; ?> class="form-control <?php if ($is_user_logged_in && ik_check_user_student($current_user->ID)) echo 'input-current'; ?>" name="new_mobile" type="text" value="">
                                                </div>
                                            </div>
                                            <div class="col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <?php
                                                    if ($is_user_logged_in) {
                                                        $last_school = get_user_meta($current_user->ID, 'last_school', true);
                                                        if (!empty($last_school) && $last_school != '')
                                                            $last_s = $last_school;
                                                        else
                                                            $last_s = __('N/A', 'iii-dictionary');
                                                    }else {
                                                        $last_s = __('N/A', 'iii-dictionary');
                                                    }
                                                    ?>
                                                    <label for="last_attended"><?php _e('Last School You Attended', 'iii-dictionary') ?></label>
                                                    <input class="form-control input-current" readonly="" type="text" value="<?php echo $last_s ?>">
                                                </div>
                                            </div>
                                            <div class="col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <label for="new_last_attended"><?php _e('New Last School You Attended', 'iii-dictionary') ?></label>
                                                    <input id="new_last_attended" <?php if ($is_user_logged_in && ik_check_user_student($current_user->ID)) echo 'readonly=""'; ?> class="form-control <?php if ($is_user_logged_in && ik_check_user_student($current_user->ID)) echo 'input-current'; ?>" name="new_last_attended" type="text" value="" >
                                                </div>
                                            </div>
                                            <div class="col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <?php
                                                    if ($is_user_logged_in) {
                                                        $previous_school = get_user_meta($current_user->ID, 'previous_school', true);
                                                        if (!empty($previous_school) && $previous_school != '')
                                                            $previous_s = $previous_school;
                                                        else
                                                            $previous_s = __('N/A', 'iii-dictionary');
                                                    }else {
                                                        $previous_s = __('N/A', 'iii-dictionary');
                                                    }
                                                    ?>
                                                    <label for="last_tought"><?php _e('Last School You Tought (if any)', 'iii-dictionary') ?></label>
                                                    <input class="form-control input-current" readonly="" type="text" value="<?php echo $previous_s ?>">
                                                </div>
                                            </div>
                                            <div class="col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <label for="new_last_tought"><?php _e('New Last School You Tought (if any)', 'iii-dictionary') ?></label>
                                                    <input id="new_last_tought" <?php if ($is_user_logged_in && ik_check_user_student($current_user->ID)) echo 'readonly=""'; ?> class="form-control <?php if ($is_user_logged_in && ik_check_user_student($current_user->ID)) echo 'input-current'; ?>" name="new_last_tought" type="text" value="">
                                                </div>
                                            </div>
                                            <div class="col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <?php
                                                    if ($is_user_logged_in) {
                                                        $skype_id = get_user_meta($current_user->ID, 'skype_id', true);
                                                        if (!empty($skype_id) && $skype_id != '')
                                                            $skypeid = $skype_id;
                                                        else
                                                            $skypeid = __('N/A', 'iii-dictionary');
                                                    }else {
                                                        $skypeid = __('N/A', 'iii-dictionary');
                                                    }
                                                    ?>
                                                    <label for="skype_id"><?php _e('Skype ID', 'iii-dictionary') ?></label>
                                                    <input class="form-control input-current" readonly="" type="text" value="<?php echo $skypeid ?>">
                                                </div>
                                            </div>
                                            <div class="col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <label for="new_skype_id"><?php _e('New Skype ID', 'iii-dictionary') ?></label>
                                                    <input id="new_skype_id" <?php if ($is_user_logged_in && ik_check_user_student($current_user->ID)) echo 'readonly=""'; ?> class="form-control <?php if ($is_user_logged_in && ik_check_user_student($current_user->ID)) echo 'input-current'; ?>" name="new_skype_id" type="text" value="">
                                                </div>
                                            </div>
                                            <div class="col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <?php
                                                    if ($is_user_logged_in) {
                                                        $user_profession = get_user_meta($current_user->ID, 'user_profession', true);
                                                        if (!empty($user_profession) && $user_profession != '')
                                                            $user_prof = $user_profession;
                                                        else
                                                            $user_prof = __('N/A', 'iii-dictionary');
                                                    }else {
                                                        $user_prof = __('N/A', 'iii-dictionary');
                                                    }
                                                    ?>
                                                    <label for="profession"><?php _e('Profession', 'iii-dictionary') ?></label>
                                                    <input class="form-control input-current" readonly="" type="text" value="<?php echo $user_prof ?>">
                                                </div>
                                            </div>
                                            <div class="col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <label for="new_profession"><?php _e('New Profession', 'iii-dictionary') ?></label>
                                                    <input id="new_profession" <?php if ($is_user_logged_in && ik_check_user_student($current_user->ID)) echo 'readonly=""'; ?> class="form-control <?php if ($is_user_logged_in && ik_check_user_student($current_user->ID)) echo 'input-current'; ?>" name="new_profession" type="text" value="">
                                                </div>
                                            </div>
                                        </div> 
                                        <div class="row" style=" margin-bottom: 20px;">
                                            <div class="col-xs-12 col-sm-8 col-md-8">
                                                <div class="form-group">
                                                    <button id="update-teacher" class="btn-dark-blue" style="background: #f7b555; margin-top: 25px !important; " type="button"><?php _e('Update', 'iii-dictionary') ?></button>
                                                </div>
                                            </div>

                                            <div class="col-xs-12 col-sm-4 col-md-4">
                                                <div class="form-group">
                                                    <button class="button-grey cancel-btn" style="background: #cecece; margin-top: 25px !important;" type="submit" name="cancel">
                                                        <?php _e('Cancel', 'iii-dictionary') ?>
                                                    </button>

                                                </div>
                                            </div>
                                        </div>        
                                    </form>
                                </div>
                                <div id="tutor-regis-tab" class="tab-pane fade in">
                                    <h3>Tutor Registration</h3>
                                    <form method="post" id="tutorForm" action="" name="registerform" enctype="multipart/form-data">
                                        <div class="row">

                                            <div id="tutor-regis" class="col-md-12">

                                                <hr style="color: #d6d6d6;">
                                                <div id="step4-collapse">
                                                    <div class="row">
                                                        <?php
                                                        $is_teaching_agreement_uptodate_math = ik_is_teacher_agreement_uptodate('MATH');
                                                        $is_teaching_agreement_uptodate = ik_is_teacher_agreement_uptodate();
                                                        ?>
                                                        <div class="col-sm-12">
                                                            <div class="form-group box_english">
                                                                <div class="box-dis" style="max-height: 200px;">
                                                                    <?php echo mw_get_option('registration-agreement') ?>
                                                                </div>
                                                                <hr style="color: #d6d6d6;">

                                                                <div class="col-sm-12 col-xs-6 col-md-12 agree">
                                                                    <div class="form-group">
                                                                        <input id="rdo-agreed2" class="checkboxagree option-input-2"  type="checkbox" name="agree-english-teacher" value="1" >
                                                                        <label for="rdo-agreed2" style="font-size: 15px !important; margin-bottom: 0px !important;">I agree to the terms and conditions</label>
                                                                    </div>
                                                                    <hr style="color: #d6d6d6;">
                                                                </div>
                                                                <div id="info2">
                                                                    <h3>Personal Information</h3>
                                                                    <div class="col-sm-6 col-md-6 col-xs-12">
                                                                        <div class="form-group">
                                                                            <label>
                                                                                <?php _e('Mobile Phone Number', 'iii-dictionary') ?>
                                                                            </label>
                                                                            <input type="text" class="form-control" name="mobile-number" value="">
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-sm-6 col-md-6 col-xs-12">
                                                                        <div class="form-group">
                                                                            <label>
                                                                                <?php _e('Last School You Attended', 'iii-dictionary') ?>
                                                                            </label>
                                                                            <input type="text" class="form-control" name="last-school" value="">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-sm-6 col-md-6 col-xs-12">
                                                                        <div class="form-group">
                                                                            <label>
                                                                                <?php _e('School You Tought (if any)', 'iii-dictionary') ?>
                                                                            </label>
                                                                            <input type="text" class="form-control" name="previous-school" value="">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-sm-6 col-md-6 col-xs-12">
                                                                        <div class="form-group">
                                                                            <label>
                                                                                <?php _e('Skype ID (for interview)', 'iii-dictionary') ?>
                                                                            </label>
                                                                            <input type="text" class="form-control" name="skype" value="">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-sm-6 col-md-6 col-xs-12">
                                                                        <div class="form-group">
                                                                            <label>
                                                                                <?php _e('Current Profession', 'iii-dictionary') ?>
                                                                            </label>
                                                                            <input type="text" class="form-control" name="profession" value="">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div id="totutor" class="col-md-12">
                                                                <h3>What are you going to tutor?</h3>
                                                                <div class="col-md-12 ">
                                                                    <div class="form-group">
                                                                        <input id="eng-writing-tab" data-target="#extend-tutor-tab1" data-toggle="collapse" type="checkbox" class="radio_buttons required class_cb_search option-input-2 radio tutor-regis2" name="eng-writing" />
                                                                        <label style="font-size: 15px !important; margin-bottom: 0px !important; margin-left: 5px;  color: #4b4b4b !important; font-weight: normal;">English (Writing Only)</label>
                                                                        <hr style="color: #d6d6d6;">
                                                                    </div>
                                                                    <div id="extend-tutor-tab1" class="extend-tutor">
                                                                        <div class="teach-ex col-md-12">
                                                                            <h4>Teaching experience in school</h4>
                                                                            <div class="col-sm-6 col-md-6 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('School Name', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="school-name" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-sm-6 col-md-6 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('School Website link', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="website-link" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-sm-6 col-md-6 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('School Phone Number', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="phone" value="" >
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-sm-6 col-md-6 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('Major to Teach (grade, subject)', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="major-teach" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-6">
                                                                                <label class="col-md-12">
                                                                                    <?php _e('Year of Teaching', 'iii-dictionary') ?>
                                                                                </label>
                                                                                <div class="form-group col-md-5">
                                                                                    <input type="text" class="form-control" name="from-year" value="">
                                                                                </div>
                                                                                <label class="col-md-2" style="padding: 10px 20px !important;">
                                                                                    <?php _e('to', 'iii-dictionary') ?>
                                                                                </label>
                                                                                <div class="form-group col-md-5">
                                                                                    <input type="text" class="form-control" name="to-year" value="">
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        <div class="teach-ex col-md-12">
                                                                            <h4>Teaching experience as a Student</h4>
                                                                            <div class="col-sm-6 col-md-6 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('School Currently Attending to', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="school-attend" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-sm-6 col-md-6 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('School Website link', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="website-link" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-6" style="padding: 0px !important;">
                                                                                <div class="form-group col-md-6">
                                                                                    <label>
                                                                                        <?php _e('Grade', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <select class="select-box-it form-control" name="birth-m">
                                                                                        <option value="1">Grade</option>
                                                                                        <option value="2">Junior</option>
                                                                                    </select>
                                                                                </div>
                                                                                <div class="form-group col-md-6">
                                                                                    <label>
                                                                                        <?php _e('GPA', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="gpa" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-sm-6 col-md-6 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('Major', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="major" value="">
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        <div class="teach-ex col-md-12">
                                                                            <h4>Other Qualification (other than teacher and student)</h4>
                                                                            <div class="col-sm-6 col-md-6 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('Your Academic Qualification', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="qualification" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-sm-6 col-md-6 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('Website link (if any)', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="website-link" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-sm-3 col-md-3 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('Years of Experience', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="experience" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-sm-9 col-md-9 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('Any Other Qualification Information', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="other-qualification" value="">
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="col-md-12 ">
                                                                    <div class="form-group">
                                                                        <input type="checkbox" id="eng-conver-tab" data-target="#extend-tutor-tab2" data-toggle="collapse" class="radio_buttons required class_cb_search option-input-2 radio tutor-regis2" name="eng-conver" />
                                                                        <label style="font-size: 15px !important; margin-bottom: 0px !important; margin-left: 5px;  color: #4b4b4b !important; font-weight: normal;">English (Conversation Only)</label>
                                                                        <hr style="color: #d6d6d6;">
                                                                    </div>
                                                                    <div id="extend-tutor-tab2" class="extend-tutor">
                                                                        <div class="teach-ex col-md-12">
                                                                            <h4>Teaching experience in school</h4>
                                                                            <div class="col-sm-6 col-md-6 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('School Name', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="school-name" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-sm-6 col-md-6 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('School Website link', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="website-link" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-sm-6 col-md-6 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('School Phone Number', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="phone" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-sm-6 col-md-6 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('Major to Teach (grade, subject)', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="major-teach" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-6">
                                                                                <label class="col-md-12">
                                                                                    <?php _e('Year of Teaching', 'iii-dictionary') ?>
                                                                                </label>
                                                                                <div class="form-group col-md-5">
                                                                                    <input type="text" class="form-control" name="from-year" value="">
                                                                                </div>
                                                                                <label class="col-md-2" style="padding: 10px 20px !important;">
                                                                                    <?php _e('to', 'iii-dictionary') ?>
                                                                                </label>
                                                                                <div class="form-group col-md-5">
                                                                                    <input type="text" class="form-control" name="to-year" value="">
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        <div class="teach-ex col-md-12">
                                                                            <h4>Teaching experience as a Student</h4>
                                                                            <div class="col-sm-6 col-md-6 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('School Currently Attending to', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="school-attend" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-sm-6 col-md-6 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('School Website link', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="website-link" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-6" style="padding: 0px !important;">
                                                                                <div class="form-group col-md-6">
                                                                                    <label>
                                                                                        <?php _e('Grade', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <select class="select-box-it form-control" name="birth-m">
                                                                                        <option value="1">Grade</option>
                                                                                        <option value="2">Junior</option>
                                                                                    </select>
                                                                                </div>
                                                                                <div class="form-group col-md-6">
                                                                                    <label>
                                                                                        <?php _e('GPA', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="gpa" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-sm-6 col-md-6 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('Major', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="major" value="">
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        <div class="teach-ex col-md-12">
                                                                            <h4>Other Qualification (other than teacher and student)</h4>
                                                                            <div class="col-sm-6 col-md-6 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('Your Academic Qualification', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="qualification" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-sm-6 col-md-6 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('Website link (if any)', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="website-link" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-sm-3 col-md-3 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('Years of Experience', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="experience" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-sm-9 col-md-9 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('Any Other Qualification Information', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="other-qualification" value="">
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="col-md-12 ">
                                                                    <div class="form-group">
                                                                        <input type="checkbox" id="math-middle-tab" data-target="#extend-tutor-tab3" data-toggle="collapse" class="radio_buttons required class_cb_search option-input-2 radio tutor-regis2" name="eng-middle" />
                                                                        <label style="font-size: 15px !important; margin-bottom: 0px !important; margin-left: 5px;  color: #4b4b4b !important; font-weight: normal;">Math (Up to Middle School)</label>
                                                                        <hr style="color: #d6d6d6;">
                                                                    </div>
                                                                    <div id="extend-tutor-tab3" class="extend-tutor">
                                                                        <div class="teach-ex col-md-12">
                                                                            <h4>Teaching experience in school</h4>
                                                                            <div class="col-sm-6 col-md-6 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('School Name', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="school-name" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-sm-6 col-md-6 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('School Website link', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="website-link" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-sm-6 col-md-6 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('School Phone Number', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="phone" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-sm-6 col-md-6 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('Major to Teach (grade, subject)', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="major-teach" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-6">
                                                                                <label class="col-md-12">
                                                                                    <?php _e('Year of Teaching', 'iii-dictionary') ?>
                                                                                </label>
                                                                                <div class="form-group col-md-5">
                                                                                    <input type="text" class="form-control" name="from-year" value="">
                                                                                </div>
                                                                                <label class="col-md-2" style="padding: 10px 20px !important;">
                                                                                    <?php _e('to', 'iii-dictionary') ?>
                                                                                </label>
                                                                                <div class="form-group col-md-5">
                                                                                    <input type="text" class="form-control" name="to-year" value="">
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        <div class="teach-ex col-md-12">
                                                                            <h4>Teaching experience as a Student</h4>
                                                                            <div class="col-sm-6 col-md-6 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('School Currently Attending to', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="school-attend" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-sm-6 col-md-6 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('School Website link', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="website-link" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-6" style="padding: 0px !important;">
                                                                                <div class="form-group col-md-6">
                                                                                    <label>
                                                                                        <?php _e('Grade', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <select class="select-box-it form-control" name="birth-m">
                                                                                        <option value="1">Grade</option>
                                                                                        <option value="2">Junior</option>
                                                                                    </select>
                                                                                </div>
                                                                                <div class="form-group col-md-6">
                                                                                    <label>
                                                                                        <?php _e('GPA', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="gpa" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-sm-6 col-md-6 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('Major', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="major" value="">
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        <div class="teach-ex col-md-12">
                                                                            <h4>Other Qualification (other than teacher and student)</h4>
                                                                            <div class="col-sm-6 col-md-6 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('Your Academic Qualification', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="qualification" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-sm-6 col-md-6 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('Website link (if any)', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="website-link" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-sm-3 col-md-3 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('Years of Experience', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="experience" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-sm-9 col-md-9 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('Any Other Qualification Information', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="other-qualification" value="">
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="col-md-12 " style="margin-bottom: 30px;">
                                                                    <div class="form-group">
                                                                        <input type="checkbox" id="math-any-tab" data-target="#extend-tutor-tab4" data-toggle="collapse" class="radio_buttons required class_cb_search option-input-2 radio tutor-regis2" name="math-any" />
                                                                        <label style="font-size: 15px !important; margin-bottom: 0px !important; margin-left: 5px;  color: #4b4b4b !important; font-weight: normal;">Math (Any Level)</label>
                                                                    </div>
                                                                    <div id="extend-tutor-tab4" class="extend-tutor">
                                                                        <div class="teach-ex col-md-12">
                                                                            <h4>Teaching experience in school</h4>
                                                                            <div class="col-sm-6 col-md-6 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('School Name', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="school-name" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-sm-6 col-md-6 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('School Website link', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="website-link" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-sm-6 col-md-6 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('School Phone Number', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="phone" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-sm-6 col-md-6 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('Major to Teach (grade, subject)', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="major-teach" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-6">
                                                                                <label class="col-md-12">
                                                                                    <?php _e('Year of Teaching', 'iii-dictionary') ?>
                                                                                </label>
                                                                                <div class="form-group col-md-5">
                                                                                    <input type="text" class="form-control" name="from-year" value="">
                                                                                </div>
                                                                                <label class="col-md-2" style="padding: 10px 20px !important;">
                                                                                    <?php _e('to', 'iii-dictionary') ?>
                                                                                </label>
                                                                                <div class="form-group col-md-5">
                                                                                    <input type="text" class="form-control" name="to-year" value="">
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        <div class="teach-ex col-md-12">
                                                                            <h4>Teaching experience as a Student</h4>
                                                                            <div class="col-sm-6 col-md-6 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('School Currently Attending to', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="school-attend" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-sm-6 col-md-6 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('School Website link', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="website-link" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-6" style="padding: 0px !important;">
                                                                                <div class="form-group col-md-6">
                                                                                    <label>
                                                                                        <?php _e('Grade', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <select class="select-box-it form-control" name="birth-m">
                                                                                        <option value="1">Grade</option>
                                                                                        <option value="2">Junior</option>
                                                                                    </select>
                                                                                </div>
                                                                                <div class="form-group col-md-6">
                                                                                    <label>
                                                                                        <?php _e('GPA', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="gpa" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-sm-6 col-md-6 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('Major', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="major" value="">
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        <div class="teach-ex col-md-12">
                                                                            <h4>Other Qualification (other than teacher and student)</h4>
                                                                            <div class="col-sm-6 col-md-6 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('Your Academic Qualification', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="qualification" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-sm-6 col-md-6 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('Website link (if any)', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="website-link" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-sm-3 col-md-3 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('Years of Experience', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="experience" value="">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-sm-9 col-md-9 col-xs-12">
                                                                                <div class="form-group">
                                                                                    <label>
                                                                                        <?php _e('Any Other Qualification Information', 'iii-dictionary') ?>
                                                                                    </label>
                                                                                    <input type="text" class="form-control" name="other-qualification" value="">
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="col-xs-12 col-sm-8 col-md-8" style="margin-bottom: 30px;">
                                                                    <div class="form-group">
                                                                        <button class="btn-dark-blue" style="background: #f7b555;" type="submit" name="send-tutor">
                                                                            <?php _e('Update and Create Account', 'iii-dictionary') ?>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                                <div class="col-xs-12 col-sm-4 col-md-4" style="margin-bottom: 30px;">
                                                                    <div class="form-group">
                                                                        <button class="button-grey cancel-btn" style="background: #cecece;" type="submit" name="cancel">
                                                                            <?php _e('Cancel', 'iii-dictionary') ?>
                                                                        </button>

                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <?php
                                if (is_mw_qualified_teacher($current_user->ID, 1) && ik_is_teaching_agreement_agreed(1)) {
                                    $tab_title = __('My Earnings', 'iii-dictionary');
                                    $tab_info_url = get_info_tab_cloud_url('Popup_info_29.jpg');
                                    $current_user_points = ik_get_user_points($current_user->ID);
                                    $point_ex_rate = mw_get_option('point-exchange-rate');

                                    // user want to request payment
                                    // validate data
                                    $form_valid = true;
                                    if (empty($_POST['receiving-method'])) {
//                    ik_enqueue_messages(__('Please choose a Receiving method.', 'iii-dictionary'), 'error');
                                        $form_valid = false;
                                    }

                                    if (empty($_POST['amount-request']) || !is_numeric($_POST['amount-request']) || $_POST['amount-request'] < 1) {
//                    ik_enqueue_messages(__('Amount requested is invalid.', 'iii-dictionary'), 'error');
                                        $form_valid = false;
                                    } else {
                                        $amount = $_POST['amount-request'] * 100 / $point_ex_rate;
                                        if ($current_user_points < $amount) {
//                        ik_enqueue_messages(__('You don\'t have enough points for this request.', 'iii-dictionary'), 'notice');
                                            $form_valid = false;
                                        }
                                    }

                                    if (empty($_POST['receiving-email']) || !is_email($_POST['receiving-email'])) {
//                    ik_enqueue_messages(__('Email for receiving payment is invalid.', 'iii-dictionary'), 'error');
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
//                            wp_redirect(locale_home_url() . '/?r=teaching/request-payment');
                                                exit;
                                            } else {
                                                ik_enqueue_messages(__('An error occured, cannot request payment.', 'iii-dictionary'), 'error');
                                            }
                                        } else {
                                            ik_enqueue_messages(__('You don\'t have enough points for this request.', 'iii-dictionary'), 'error');
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
                                } else {
//                                    $title = __('Message', 'iii-dictionary');
//                                    $body = __('You need to pass the qualification test to access this panel.', 'iii-dictionary');
//                                    $return_url = locale_home_url() . '/?r=my-account#6';
                                    if ($is_registered_teacher == FALSE) {
//                $title = __('Registration Required', 'iii-dictionary');
//                $body = __('Please register as the teacher before Earn Money by Teaching Math in this panel.', 'iii-dictionary');
//                $return_url = locale_home_url() . '/?r=my-account#4';
                                    }
//                                    set_lockpage_dialog($title, $body, $return_url);
                                }
                                ?>
                                <div id="earn-pay" class="tab-pane fade in">
                                    <h3>Earning and Payment</h3>
                                    <form method="post" id="earningForm" action="" name="registerform" enctype="multipart/form-data">
                                        <div class="row">

                                            <div id="tutor-regis" class="col-md-12">


                                                <div id="step4-collapse">
                                                    <div class="row">

                                                        <div class="col-sm-12">
                                                            <div class="form-group box_english">

                                                                <div id="info" class="my-earning">
                                                                    <h3>My Earnings</h3>

                                                                    <div class="form-group">
                                                                        <label>
                                                                            <?php _e('Current Points Earned', 'iii-dictionary') ?>                            
                                                                        </label>
                                                                        <div class="color-yellow background-f4f7f7"><?php echo number_format($current_user_points, 2) ?></div>
                                                                    </div>


                                                                </div>
                                                            </div>

                                                            <div id="totutor" class="my-earning">
                                                                <h3>Details of Earning and Spending</h3>

                                                                <div class="col-sm-12 detail-earning" style="max-height: 500px; overflow: auto;">

                                                                    <table class="table table-condensed" id="list-sheets">
                                                                        <thead>
                                                                            <tr>
                                                                                <th><?php _e('Transaction', 'iii-dictionary') ?></th>
                                                                                <th class="hidden-xs"><?php _e('Points', 'iii-dictionary') ?></th>
                                                                                <th><?php _e('Note', 'iii-dictionary') ?></th>
                                                                                <th><?php _e('Date', 'iii-dictionary') ?></th>

                                                                            </tr>
                                                                        </thead>

                                                                        <tbody><?php if (empty($user_transactions->items)) : ?>
                                                                                <tr><td colspan="4"><?php _e('No transactions', 'iii-dictionary') ?></td></tr>
                                                                                <?php
                                                                            else :
                                                                                $total_point = 0;
                                                                                foreach ($user_transactions->items as $item) :
                                                                                    $total_point = $total_point + $item->amount;
                                                                                    ?>
                                                                                    <tr>
                                                                                        <td><?php echo $item->txn_type ?></td>
                                                                                        <?php
                                                                                        if ($item->txn_type == 'Payment') {
                                                                                            ?>
                                                                                            <td style="color: #a7a7a7 !important; font-size: 14px; font-weight: bold;" class="<?php echo in_array($item->txn_type_id, array(POINT_TXN_GRADING_WORKSHEET, POINT_TXN_GIFT)) ? 'positive' : 'negative' ?>-amount"><?php echo $item->amount ?></td>
                                                                                            <?php
                                                                                        } else {
                                                                                            ?>

                                                                                            <td style="color: #ce851f !important; font-size: 14px; font-weight: bold;" class="<?php echo in_array($item->txn_type_id, array(POINT_TXN_GRADING_WORKSHEET, POINT_TXN_GIFT)) ? 'positive' : 'negative' ?>-amount"><?php echo $item->amount ?></td>
                                                                                        <?php } ?>

                                                                                        <td class="note"><?php echo $item->note ?>
                                                                                            <div class="detail-note"><?php echo $item->note ?></div></td>

                                                                                        <td><?php echo ik_date_format($item->txn_date, 'm/d/Y H:i:s') ?></td>

                                                                                    </tr>
                                                                                <?php endforeach; ?>

                                                                            <?php endif ?>
                                                                        </tbody>
                                                                    </table>

                                                                </div>
                                                                <div class="paginate-f">
                                                                    <?php echo $pagination ?>
                                                                </div>      
                                                            </div>
                                                            <div class="clearfix"></div>
                                                            <div class="sub-col" class="my-earning">
                                                                <h3>Receiving Payments</h3>
                                                                <hr style="color: #d6d6d6;margin: 10px 0px;">
                                                                <div style=" font-size: 13px; color: #9c9c9c;">When teachers have a balance of at least $100, they can request to be paid.</div>
                                                                <hr style="color: #d6d6d6; margin: 10px 0px;">
                                                                <div class="col-md-6 receive-pay" style="padding-right: 20px !important;">
                                                                    <p class="tit-receive-pay">By <span>Paypal</span></p>
                                                                    <p>Send a Paypal invoice with the amount in multiples of $100 to <a href="#">payment@innovative-knowledge.com</a>. The payment will be sent to you within one week.</p>
                                                                </div>
                                                                <div class="col-md-6 receive-pay">
                                                                    <p class="tit-receive-pay">By <span>Amazon Gift Card</span></p>
                                                                    <p>Send the request to <a href="#">payment@innovative-knowledge.com</a> using paypal's email address. Indicate the amount (multiple of $100). The admin will send the amazon gift card to the email address.</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div id="subscription" class="tab-pane fade">  
                                    <h3>Subscription & Points</h3>
                                    <div class="subscription">
                                        <h3>Subscription Status & Purchase History</h3>
                                        <div id="tab-subs-purchase" class="tab-style">
                                            <ul class="nav nav-tabs">
                                                <li class="active tab-subs-purchase" id="subscription-status"><a data-toggle="tab" href="#tab-subs">Subscription Status</a></li>
                                                <li class="tab-subs-purchase" id="purchase-history"><a data-toggle="tab" href="#tab-purchase">Purchase History</a></li>

                                            </ul>
                                            <?php
                                            $current_user_id = get_current_user_id();

                                            $current_page2 = max(1, get_query_var('page'));

                                            $filter2 = get_page_filter_session();

                                            if (empty($filter2) && !isset($_POST['filter'])) {

                                                $filter2['orderby'] = 'ct.name';

                                                $filter2['order-dir'] = 'asc';

                                                $filter2['items_per_page'] = 9999;

                                                $filter2['offset'] = $filter2['items_per_page'] * ($current_page2 - 1);
                                            } else {

                                                $filter2['items_per_page'] = 99999999;

                                                if (isset($_REAL_POST['filter']['orderby'])) {

                                                    $filter2['orderby'] = $_REAL_POST['filter']['orderby'];



                                                    $filter2['order-dir'] = $_REAL_POST['filter']['order-dir'];
                                                }

                                                $filter2['offset'] = $filter2['items_per_page'] * ($current_page2 - 1);
                                            }

                                            set_page_filter_session($filter2);

                                            $user_subscriptions = MWDB::get_user_subscriptions($current_user_id, $filter2);

                                            $total_pages2 = ceil($user_subscriptions->total / $filter2['items_per_page']);

                                            $pagination2 = paginate_links(array(
                                                'format' => '?page=%#%',
                                                'current' => $current_page2,
                                                'total' => $total_pages2
                                            ));
                                            ?>
                                            <div class="tab-content">
                                                <div id="tab-subs" class="tab-pane fade in active">

                                                    <div style="max-height: 450px; overflow-y: auto; overflow-x:hidden;">

                                                        <table class="table table-condensed table-subscription" id="user-subscriptions">

                                                            <thead>

                                                                <tr>

                                                                    <th><?php _e('Type', 'iii-dictionary') ?><span class="sorting-indicator"></span></th>

                                                                    <th><?php _e('Students', 'iii-dictionary') ?></th>
                                                                    <th><?php _e('Dictionary', 'iii-dictionary') ?></th>
                                                                    <!--<th class="hidden-xs" style="width:11%"><?php _e('No. of Users', 'iii-dictionary') ?></th>-->

                                                                    <th><?php _e('Sub. End', 'iii-dictionary') ?> <span class="sorting-indicator"></span></th>



                                                                    <th><?php _e('Class (group)', 'iii-dictionary') ?></th>

                                                                    <th></th>

                                                                </tr>

                                                            </thead>

                                                            <tfoot>

                                                                <tr><td colspan="8"><?php echo $pagination2 ?></td></tr>

                                                            </tfoot>

                                                            <tbody>

                                                                <?php if (empty($user_subscriptions->items)) : ?>

                                                                    <tr><td colspan="8"><?php _e('You haven\'t subscribed yet.', 'iii-dictionary') ?></td></tr>

                                                                <?php else : ?>

                                                                    <?php
                                                                    foreach ($user_subscriptions->items as $code) :

                                                                        $date_a = date("Y-m-d");

                                                                        if (ik_date_format($code->expired_on) < $date_a) {
                                                                            ?>

                                                                            <tr <?php //echo $code->expired_on < date('Y-m-d', time()) ? ' class="text-muted"' : ''                                                                                         ?>>

                                                                                <td class="note" style="width: 30%;"><?php if (!$code->inherit) : ?>

                                                                                        <?php echo $code->type ?><?php echo in_array($code->typeid, array(SUB_SAT_PREPARATION, SUB_MATH_SAT_I_PREP, SUB_MATH_SAT_II_PREP)) ? ' - ' . $code->sat_class : '' ?>
                                                                                        <div class="detail-note"> <?php echo $code->type ?><?php echo in_array($code->typeid, array(SUB_SAT_PREPARATION, SUB_MATH_SAT_I_PREP, SUB_MATH_SAT_II_PREP)) ? ' - ' . $code->sat_class : '' ?></div>
                                                                                    <?php else : ?>

                                                                                        <?php echo $code->type ?>
                                                                                        <div class="detail-note"><?php echo $code->type ?></div>
                                                                                    <?php endif ?>


                                                                                </td>

                                                                                <td style="width: 10%;"><?php echo in_array($code->typeid, array(SUB_TEACHER_TOOL, SUB_TEACHER_TOOL_MATH, SUB_GROUP)) ? $code->number_of_students : 'N/A' ?></td>
                                                                                <td style="width: 10%;"><?php echo $code->dictionary ?></td>
                                                                                <td style="width: 10%;"><?php echo ik_date_format($code->expired_on) ?></td>
                                                                                <td style="width: 30%;" class="note"><?php echo is_null($code->group_name) ? 'N/A' : $code->group_name ?>
                                                                                    <div class="detail-note"><?php echo is_null($code->group_name) ? 'N/A' : $code->group_name ?></div>
                                                                                </td>
                                                                              <!--<td style="color:#7F7D7E" class="hidden-xs"><?php echo in_array($code->typeid, array(SUB_DICTIONARY, SUB_SELF_STUDY, SUB_SELF_STUDY_MATH, SUB_GROUP)) ? $code->number_of_students : 'N/A' ?></td>-->



                                                                                <?php
                                                                                $date1 = new DateTime();

                                                                                $date2 = new DateTime($code->expired_on);

                                                                                $interval = $date1->diff($date2);

                                                                                $months_left = $interval->d > 0 ? $interval->m + 1 : $interval->m;

                                                                                $checked_out_state = '';

                                                                                foreach ($cart_items as $item) {

                                                                                    if ($item->sub_id == $code->id) {

                                                                                        $checked_out_state = ' disabled';
                                                                                    }
                                                                                }
                                                                                ?><td style="width: 10%;"  data-subid="<?php echo $code->id ?>" data-type="<?php echo $code->typeid ?>" data-did="<?php echo $code->dictionary_id ?>" data-size="<?php echo $code->number_of_students ?>" data-months="<?php echo $months_left ?>"<?php echo!is_null($code->group_name) ? ' data-group="' . $code->group_name . '"' : '' ?> data-sat-class="<?php echo $code->sat_class ?>" data-sat-class-id="<?php echo $code->sat_class_id ?>"  data-gid="<?php echo $code->group_id ?>">

                                                                                    <?php if (!$code->inherit) : ?>

                                                                                        <?php if (in_array($code->typeid, array(SUB_TEACHER_TOOL_MATH, SUB_TEACHER_TOOL))) : ?>

                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <!--<button type="button" class="btn btn-default btn-block btn-tiny grey extend-sub-btn" data-task="add"<?php echo $checked_out_state ?>><?php _e('Add Members', 'iii-dictionary') ?></button>-->

                                                                                        <?php endif ?>

                                                                                        <?php if (!in_array($code->typeid, array(SUB_GROUP))) : ?>

                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <!--<button type="button" class="btn btn-default btn-block btn-tiny grey extend-sub-btn" <?php echo $checked_out_state ?>><?php _e('Renew Subscription', 'iii-dictionary') ?></button>-->

                                                                                        <?php endif ?>         
                                                                                        <a data-toggle="tab" href="#"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Detail_Grey_1.png" class="" alt="setting my account" style="width: 23px; margin-right: 5px;"></a>
                                                                                        <a data-toggle="tab" href="#"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Subscription.png" class="" alt="setting my account" style="width: 23px;"></a>

                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <!--                                                                                                        <a href="<?php echo locale_home_url() ?>/?r=view-subscription&amp;cid=<?php echo $code->id ?>" class="btn btn-default btn-block btn-tiny grey"><?php _e('Detail', 'iii-dictionary') ?></a>-->

                                                                                    <?php endif ?>

                                                                                </td>

                                                                            </tr>

                                                                        <?php }else { ?>

                                                                            <tr <?php //echo $code->expired_on < date('Y-m-d', time()) ? ' class="text-muted"' : ''                                                                                          ?>>

                                                                                <td><?php if (!$code->inherit) : ?>

                                                                                        <?php echo $code->type ?><?php echo in_array($code->typeid, array(SUB_SAT_PREPARATION, SUB_MATH_SAT_I_PREP, SUB_MATH_SAT_II_PREP)) ? ' - ' . $code->sat_class : '' ?>

                                                                                    <?php else : ?>

                                                                                        <?php echo $code->type ?>

                                                                                    <?php endif ?>

                                                                                </td>

                                                                                <td ><?php echo in_array($code->typeid, array(SUB_TEACHER_TOOL, SUB_TEACHER_TOOL_MATH, SUB_GROUP)) ? $code->number_of_students : 'N/A' ?></td>
                                                                                <td ><?php echo $code->dictionary ?></td>

                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <!--                                                                                <td ><?php echo in_array($code->typeid, array(SUB_DICTIONARY, SUB_SELF_STUDY, SUB_SELF_STUDY_MATH, SUB_GROUP)) ? $code->number_of_students : 'N/A' ?></td>-->

                                                                                <td ><?php echo ik_date_format($code->expired_on) ?></td>





                                                                                <td><?php echo is_null($code->group_name) ? 'N/A' : $code->group_name ?></td>

                                                                                <?php
                                                                                $date1 = new DateTime();

                                                                                $date2 = new DateTime($code->expired_on);

                                                                                $interval = $date1->diff($date2);

                                                                                $months_left = $interval->d > 0 ? $interval->m + 1 : $interval->m;

                                                                                $checked_out_state = '';

                                                                                foreach ($cart_items as $item) {

                                                                                    if ($item->sub_id == $code->id) {

                                                                                        $checked_out_state = ' disabled';
                                                                                    }
                                                                                }
                                                                                ?><td data-subid="<?php echo $code->id ?>" data-type="<?php echo $code->typeid ?>" data-did="<?php echo $code->dictionary_id ?>" data-size="<?php echo $code->number_of_students ?>" data-months="<?php echo $months_left ?>"<?php echo!is_null($code->group_name) ? ' data-group="' . $code->group_name . '"' : '' ?> data-sat-class="<?php echo $code->sat_class ?>" data-sat-class-id="<?php echo $code->sat_class_id ?>"  data-gid="<?php echo $code->group_id ?>">

                                                                                    <?php if (!$code->inherit) : ?>

                                                                                        <?php if (in_array($code->typeid, array(SUB_TEACHER_TOOL_MATH, SUB_TEACHER_TOOL))) : ?>

                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <!--<button type="button" class="btn btn-default btn-block btn-tiny grey extend-sub-btn" data-task="add"<?php echo $checked_out_state ?>><?php _e('Add Members', 'iii-dictionary') ?></button>-->

                                                                                        <?php endif ?>

                                                                                        <?php if (!in_array($code->typeid, array(SUB_GROUP))) : ?>
                                                                                            <a data-toggle="tab" href="#"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Detail_Grey_1.png" class="" alt="setting my account" style="width: 23px; margin-right: 5px;"></a>
                                                                                                                                                                   <!--<button type="button" class="btn btn-default btn-block btn-tiny grey extend-sub-btn" <?php echo $checked_out_state ?>><?php _e('Renew Subscription', 'iii-dictionary') ?></button>-->
                                                                                            <a data-toggle="tab" href="#"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Subscription.png" class="" alt="setting my account" style="width: 23px;"></a>
                                                                                        <?php endif ?>

                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <!--<a href="<?php echo locale_home_url() ?>/?r=view-subscription&amp;cid=<?php echo $code->id ?>" class="btn btn-default btn-block btn-tiny grey"><?php _e('Detail', 'iii-dictionary') ?></a>-->

                                                                                    <?php endif ?>

                                                                                </td>

                                                                            </tr>

                                                                            <?php
                                                                        }

                                                                    endforeach
                                                                    ?>

                                                                <?php endif ?>

                                                            </tbody>

                                                        </table>

                                                    </div>
                                                    <div style="clear: both;"></div>
                                                    <div class="detail-subs">

                                                        <a data-toggle="tab" href="#"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Detail_Grey_1.png" class="" alt="setting my account" style="width: 23px; margin-right: 5px;">Detail</a>
                                                        <a data-toggle="tab" href="#"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Subscription.png" class="" alt="setting my account" style="width: 23px;">Subscription</a>
                                                    </div>

                                                </div>
                                                <div id="tab-purchase" class="tab-pane fade">
                                                    <div  style="max-height: 450px; overflow: auto;">

                                                        <table class="table table-condensed table-subscription">

                                                            <thead>

                                                                <tr>

                                                                    <th><?php _e('Purchase Item', 'iii-dictionary') ?></th>

                                                                    <th><?php _e('Activation Code', 'iii-dictionary') ?></th>

                                                                    <th><?php _e('Payment Method', 'iii-dictionary') ?></th>

                                                                    <th><?php _e('Paid Amount', 'iii-dictionary') ?></th>

                                                                    <th><?php _e('Purchased On', 'iii-dictionary') ?></th>

                                                                </tr>

                                                            </thead>
                                                            <?php
                                                            $purchased_history = MWDB::get_user_purchase_history($current_user_id);
                                                            ?>
                                                            <tbody><?php if (empty($purchased_history)) : ?>

                                                                    <tr><td colspan="5"><?php _e('No history', 'iii-dictionary') ?></td></tr>

                                                                    <?php
                                                                else :

                                                                    foreach ($purchased_history as $item) :
                                                                        ?>

                                                                        <tr>

                                                                            <td><?php echo $item->purchased_item_name ?></td>

                                                                            <td><?php echo!empty($item->encoded_code) ? $item->encoded_code : 'NULL'; ?></td>

                                                                            <td><?php echo $item->payment_method ?></td>

                                                                            <td>$ <?php echo $item->amount ?></td>

                                                                            <td><?php echo ik_date_format($item->purchased_on, 'm/d/Y H:m:i') ?></td>

                                                                        </tr>

                                                                        <?php
                                                                    endforeach;

                                                                endif
                                                                ?>

                                                            </tbody>

                                                        </table>

                                                    </div>
                                                </div>
                                                <form  method="post" action="">
                                                    <input type="hidden" name="dictionary-id" id="dictionary-id" value="">
                                                    <input type="hidden" name="starting-date" id="starting-date-txt" value="">
                                                    <input type="hidden" name="assoc-group" id="assoc-group" value="">
                                                    <input type="hidden" name="group-name" id="group-name" value="">
                                                    <input type="hidden" name="group-pass" id="group-pass" value="">
                                                    <input type="hidden" id="activation-code" name="activation-code" value="">
                                                </form>
                                                <div class="activation">
                                                    <h3>Activation Code</h3>
                                                    <div class="form-group col-md-6" style="padding-left: 0px !important;">
                                                        <label for="credit-code">Enter a Credit Code <span style="font-style: italic;">(if you have any)</span></label>
                                                        <input class="form-control" id="credit-code" name="credit-code">
                                                    </div>
                                                    <div class="form-group col-md-6" style="padding-right: 0px !important;">
                                                        <button class="btn-dark-blue" id="val-credit-code" style="background: #f7b555; margin-top: 25px;margin-bottom: 50px;" type="button" data-loading-text="<?php _e('Checking...', 'iii-dictionary') ?>" data-error-text="<?php _e('Please enter a credit code', 'iii-dictionary') ?>"><?php _e('Apply', 'iii-dictionary') ?></button>
                                                        <span data-toggle="popover" data-placement="bottom" data-container="body"  data-html="true" data-max-width="420px"></span>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>


                                    </div>
                                </div>


                                <!--my subject-->
                                <div id="my-subject" class="style-form tab-pane fade">  
                                    <h3>My Subject</h3>
                                    <div class="subscription">
                                        <p class="text-intro-tab">My Subject are collections of Lessons you create for your classes. You can create a Subject by putting together a series of Lessons from the <span>My Lesson</span></p>
                                        <div class="my-lesson-body">
                                            <div class="border-selectall">
                                                <div class="col-md-9 col-sm-12 col-xs-12 cb-type-lesson">
                                                    <label>
                                                        <input type="checkbox" class="radio_buttons class_cb_search option-input-2 option-input-lesson radio" id="select-all-subject" value="" name="select-all-lesson"/>
                                                        <span style="padding-left: 18px">Select All</span>
                                                    </label>
                                                </div>
                                                <div class="col-md-3 col-sm-12 col-xs-12 search-assign">
                                                    <div class="form-group">
                                                        <button type="submit" class="btn-assign form-control" name="assign-lesson" id="btn-assign-subject"></span><?php _e('Search & Assign Lesson', 'iii-dictionary') ?></button>
                                                        <button type="submit" class="btn-down form-control hidden" name="assign-lesson-2" id="btn-down-subject"></span><?php _e('Search & Assign Lesson', 'iii-dictionary') ?></button>
                                                    </div>
                                                </div>

                                                <div class="clearfix"></div>
                                                <div class="select-style open-search" id="select-assign-subject">
                                                    <div class="search-box">
                                                        <div class="col-md-9 col-sm-9 col-xs-12 nopadding-l">
                                                            <div class="form-group">
                                                                <input  id="search-subject" name="search-subject" class="form-control search-tit" placeholder="Search Lesson from Here...">
                                                                <p class="link-clear">Clear</p>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3 col-sm-3 col-xs-12 nopadding-r">
                                                            <div class="form-group">
                                                                <button type="submit" class="btn-blue form-control" name="btn-search-lesson" id="search-btn-subject"</span><?php _e('Search', 'iii-dictionary') ?></button>
                                                            </div>
                                                        </div>
                                                        <div class="clearfix"></div>
                                                    </div>
                                                    <div class="col-md-9 col-sm-9 col-xs-12" id="select-sub" style="padding-left: 0px !important;">
                                                        <form method="get">
                                                            <select class="select-box-it form-control" name="sl-class" id="select-subject" >
                                                                <option selected value="" >Select a Class to Assign</option>
                                                                <option value="2" >Intermediate Algebra Math Class - Middle to High School</option>
                                                                <option value="3" >Intermediate Algebra Math Class - Middle to High School</option>
                                                                <option value="4" >Intermediate Algebra Math Class - Middle to High School</option>
                                                            </select>
                                                        </form>
                                                    </div>
                                                    <div class="col-md-3 col-sm-3 col-xs-12 nopadding-r">
                                                        <div class="form-group">
                                                            <button type="submit" class="btn-blue form-control" name="assign-now"></span><?php _e('Assign Now', 'iii-dictionary') ?></button>
                                                        </div>
                                                    </div>
                                                    <div class="clearfix"></div>
                                                </div>
                                                <div class="clearfix"></div>
                                            </div>


                                            <div class="table-my-lesson">
                                                <div style="max-height: 600px; overflow-y: auto; overflow-x:hidden;">

                                                    <table class="table table-condensed table-subscription" >

                                                        <thead>

                                                            <tr>
                                                                <th class="table-img-icon" id="icon-option-subject" data-sort="upward" style="width: 5%;"><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Newest.png" class="img-sort-subject" alt="option subject" ></th>

                                                                <th style="width: 5%;"><?php _e('Type', 'iii-dictionary') ?><span class="sorting-indicator"></span></th>

                                                                <th style="width: 70%;"><?php _e('Subject', 'iii-dictionary') ?></th>

                                                                <th style="width: 20%;"></th>

                                                            </tr>

                                                        </thead>
                                                        <tbody id="table-my-subject">


                                                        </tbody>

                                                    </table>


                                                </div>

                                            </div>
                                        </div>
                                        <div id="add-worksheet-subject" class="hidden add-worksheet">
                                            <div class="">
                                                <div class="head-addws col-md-12">
                                                    <p class="lesson-icon"><span>Add Lessons: </span> <img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Arrow_Right.png"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_MySubject.png"><span class="name-sub"></span></p>
                                                    <div class="close-icon"> <img  id="close-add-subject"   aria-hidden="true" src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Close.png"></div>   
                                                </div>
                                                <p>Where would you like to get Lessons from?</p>
                                                <div class="body-addws">
                                                    <div class="get-ws-choise col-md-12">
                                                        <div class="get-ws-choise-icon"><img id="icon-open-sub"  aria-hidden="true" src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Blue_Open.png"></div>
                                                        <div class="get-ws-choise-text" id="from-my-sub">From <span>My Lesson</span></div>
                                                        <div class="clearfix"></div>
                                                        <div class="lesson-detail-sub" style="display:none;">
                                                            <div class="border-selectall" style="border-top:none !important;">
                                                                <div class="col-md-8 col-sm-12 col-xs-12 cb-type-lesson">
                                                                    <label>
                                                                        <input type="checkbox" class="radio_buttons class_cb_search option-input-2 option-input-lesson radio" id="select-all-less4" value="" name="select-all-lesson"/>
                                                                        <span style="padding-left: 18px">Select All</span>
                                                                    </label>
                                                                </div>
                                                                <div class="col-md-4 col-sm-12 col-xs-12 search-assign">
                                                                    <div class="form-group">
                                                                        <button type="submit" class="btn-assign form-control" name="assign-lesson" id="search-my-less"></span><?php _e('Search & Assign Lesson', 'iii-dictionary') ?></button>
                                                                        <button type="submit" class="btn-down form-control hidden" name="assign-lesson-2" id="search-my-less-3"></span><?php _e('Search & Assign Lesson', 'iii-dictionary') ?></button>
                                                                    </div>
                                                                </div>
                                                                <div class="clearfix"></div>
                                                                <div class="select-style open-search" id="select-assign-sub">
                                                                    <div class="search-box">
                                                                        <div class="col-md-9 col-sm-9 col-xs-12 nopadding-l">
                                                                            <div class="form-group">
                                                                                <input  id="search-sub" name="search-lesson" class="form-control search-tit" placeholder="Search Lesson from Here...">
                                                                                <p class="link-clear">Clear</p>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-3 col-sm-3 col-xs-12 nopadding-r">
                                                                            <div class="form-group">
                                                                                <button type="submit" class="btn-blue form-control" name="btn-search-lesson" id="search-btn-sub"</span><?php _e('Search', 'iii-dictionary') ?></button>
                                                                            </div>
                                                                        </div>
                                                                        <div class="clearfix"></div>
                                                                    </div>
                                                                    <div class="col-md-9 col-sm-9 col-xs-12" style="padding-left: 0px !important;">
                                                                        <form method="get">
                                                                            <select class="select-box-it form-control" name="sl-class" id="select-assign-sub2" >
                                                                                <option selected disabled value="" >Select a Class to Assign</option>
                                                                                <option value="2" >Intermediate Algebra Math Class - Middle to High School</option>
                                                                                <option value="3" >Intermediate Algebra Math Class - Middle to High School</option>
                                                                                <option value="4" >Intermediate Algebra Math Class - Middle to High School</option>
                                                                            </select>
                                                                        </form>
                                                                    </div>
                                                                    <div class="col-md-3 col-sm-3 col-xs-12 nopadding-r">
                                                                        <div class="form-group">
                                                                            <button type="submit" class="btn-blue form-control" name="assign-now"></span><?php _e('Assign Now', 'iii-dictionary') ?></button>
                                                                        </div>
                                                                    </div>
                                                                    <div class="clearfix"></div>
                                                                </div>
                                                                <div class="clearfix"></div>
                                                            </div>

                                                            <div class="table-my-lesson active">
                                                                <div style="max-height: 600px; overflow-y: auto; overflow-x:hidden;">

                                                                    <table class="table table-condensed table-subscription" >

                                                                        <thead>

                                                                            <tr>
                                                                                <th class="table-img-icon" id="icon-option-less2" data-sort="upward" style="width: 5%;"><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Newest.png" class="img-sort-lesson2" alt="option lesson" ></th>
                                                                                <th style="width: 5%;"><?php _e('Type', 'iii-dictionary') ?><span class="sorting-indicator"></span></th>

                                                                                <th style="width: 70%;"><?php _e('Lesson', 'iii-dictionary') ?></th>

                                                                                <th style="width: 20%;"></th>

                                                                            </tr>

                                                                        </thead>
                                                                        <tbody id="table-less2">


                                                                        </tbody>

                                                                    </table>


                                                                </div>

                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="btn-add-ws col-md-3 nopadding-r" id="">
                                                        <button id="btn-add-less" type="submit" class="btn-orange btn-create-lesson form-control nopadding-r" ></span><?php _e('Add', 'iii-dictionary') ?></button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!--end of my subject-->

                                <!--my lesson-->
                                <div id="my-lesson" class="style-form tab-pane fade">  
                                    <h3>My Lesson</h3>
                                    <div class="subscription">
                                        <p class="text-intro-tab">My Lesson are collections of worksheets you create for your classes. You can create a lesson by putting together a series of worksheets from the <span>Public Library</span> and <span>My Library</span>.</p>
                                        <div class="my-lesson-body">
                                            <div class="border-selectall">
                                                <div class="col-md-6 col-sm-12 col-xs-12 cb-type-lesson">
                                                    <label>
                                                        <input type="checkbox" class="radio_buttons class_cb_search option-input-2 option-input-lesson radio" id="select-all-lesson" value="" name="select-all-lesson"/>
                                                        <span style="padding-left: 18px">Select All</span>
                                                    </label>
                                                </div>
                                                <div class="col-md-3 col-sm-12 col-xs-12 search-assign">
                                                    <div class="form-group">
                                                        <button type="submit" class="btn-assign form-control" name="assign-lesson" id="btn-assign"></span><?php _e('Search & Assign Lesson', 'iii-dictionary') ?></button>
                                                        <button type="submit" class="btn-down form-control hidden" name="assign-lesson-2" id="btn-assign-down"></span><?php _e('Search & Assign Lesson', 'iii-dictionary') ?></button>
                                                    </div>
                                                </div>
                                                <div class="col-md-3 col-sm-12 col-xs-12" style="padding-right: 0 !important">
                                                    <div class="form-group">
                                                        <button id="btn-create-subject" type="submit" class="btn-orange btn-create-lesson form-control nopadding-r" name="create-subject"></span><?php _e('Create Subject', 'iii-dictionary') ?></button>
                                                    </div>
                                                </div>
                                                <div class="clearfix"></div>
                                                <div class="select-style open-search" id="select-assign">
                                                    <div class="search-box">
                                                        <div class="col-md-9 col-sm-9 col-xs-12 nopadding-l">
                                                            <div class="form-group">
                                                                <input  id="search-lesson" name="search-lesson" class="form-control search-tit" placeholder="Search Lesson from Here...">
                                                                <p class="link-clear">Clear</p>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3 col-sm-3 col-xs-12 nopadding-r">
                                                            <div class="form-group">
                                                                <button type="submit" class="btn-blue form-control" name="btn-search-lesson" id="search-btn"</span><?php _e('Search', 'iii-dictionary') ?></button>
                                                            </div>
                                                        </div>
                                                        <div class="clearfix"></div>
                                                    </div>
                                                    <div class="col-md-9 col-sm-9 col-xs-12" style="padding-left: 0px !important;">
                                                        <form method="get">
                                                            <select class="select-box-it form-control" name="sl-class" id="select-assign-class" >
                                                                <option selected disabled value="" >Select a Class to Assign</option>
                                                                <option value="2" >Intermediate Algebra Math Class - Middle to High School</option>
                                                                <option value="3" >Intermediate Algebra Math Class - Middle to High School</option>
                                                                <option value="4" >Intermediate Algebra Math Class - Middle to High School</option>
                                                            </select>
                                                        </form>
                                                    </div>
                                                    <div class="col-md-3 col-sm-3 col-xs-12 nopadding-r">
                                                        <div class="form-group">
                                                            <button type="submit" class="btn-blue form-control" name="assign-now"></span><?php _e('Assign Now', 'iii-dictionary') ?></button>
                                                        </div>
                                                    </div>
                                                    <div class="clearfix"></div>
                                                </div>
                                                <div class="clearfix"></div>
                                            </div>

                                            <div class="create-subject-content hidden">
                                                <div class="header-create-lesson">
                                                    <h4 class="title-create">Creating a Subject<img id="close-icon-sub" src="http://ikteacher.com/wp-content/themes/ik-learn/library/images/Icon_Close.png" style="width: 15px;float: right"></h4>  
                                                </div>
                                                <div style="float: left;width: 70%">

                                                    <h5 class="lesson-name">Subject Name</h5>
                                                    <input class="form-control" value="" id="input-style-sub">
                                                </div>
                                                <div style="float: right;width: 28%">
                                                    <h5 class="lesson-name">Type of the Subject</h5>
                                                    <form method="get">
                                                        <select class="select-box-it form-control" name="sl-class" id="select-my-sub" data-selected="" >
                                                            <option id="option-mysub" value="1">English</option>

                                                        </select>
                                                    </form>
                                                </div>
                                                <div class=" col-nopadding" style="margin-top: 100px; width: 100%" >
                                                    <label for="about-class"><?php _e('Short description of the Subject', 'iii-dictionary') ?></label>
                                                    <div id="desc-mysubject">
                                                        <?php
                                                        $editor_settings = array(
                                                            'wpautop' => false,
                                                            'media_buttons' => false,
                                                            'quicktags' => false,
                                                            'editor_height' => 81,
                                                            'textarea_rows' => 15,
                                                            'tinymce' => array(
                                                                'toolbar1' => 'bold,italic,strikethrough,image,bullist,numlist,blockquote,hr,alignleft,aligncenter,alignright,spellchecker,fullscreen,wp_adv'
                                                            )
                                                        );
                                                        ?>
                                                        <?php wp_editor('', 'post_mysubject', $editor_settings); ?>
                                                        <div class="clear-both"></div>
                                                    </div>
                                                </div>

                                                <h5 class="lesson-name2">Confirm Lessons</h5>
                                                <table style="width: 100%;margin-bottom: 20px;" id="table-confirm-subject">


                                                </table>
                                                <button id="cancel-sub" type="submit" class="btn-gray btn-create-lesson form-control nopadding-r" name="create-lesson"></span><?php _e('Cancel', 'iii-dictionary') ?></button>
                                                <button id="create-sub" type="submit" class="btn-orange btn-create-lesson form-control nopadding-r" name="create-lesson"></span><?php _e('Create Subject', 'iii-dictionary') ?></button>
                                            </div>

                                            <div class="table-my-lesson active">
                                                <div style="max-height: 600px; overflow-y: auto; overflow-x:hidden;">

                                                    <table class="table table-condensed table-subscription" >

                                                        <thead>

                                                            <tr>
                                                                <th class="table-img-icon" id="icon-option-lesson" data-sort="upward" style="width: 5%;"><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Newest.png" class="img-sort-lesson" alt="option lesson" ></th>

                                                                <th style="width: 5%;"><?php _e('Type', 'iii-dictionary') ?><span class="sorting-indicator"></span></th>

                                                                <th style="width: 70%;"><?php _e('Lesson', 'iii-dictionary') ?></th>

                                                                <th style="width: 20%;"></th>

                                                            </tr>

                                                        </thead>
                                                        <tbody id="table-my-lesson">


                                                        </tbody>

                                                    </table>


                                                </div>

                                            </div>
                                        </div>
                                        <div id="add-worksheet" class="hidden add-worksheet">
                                            <div class="">
                                                <div class="head-addws col-md-12">
                                                    <p class="lesson-icon"><span>Add Worksheets: </span> <img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Arrow_Right.png"><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Lesson.png"><span class="name-lesson"></span></p>
                                                    <div class="close-icon"> <img  id="icon-close-addws"   aria-hidden="true" src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Close.png"></div>   
                                                </div>
                                                <p>Where would you like to get worksheets from?</p>
                                                <div class="body-addws">
                                                    <div class="get-ws-choise col-md-12">
                                                        <div class="get-ws-choise-icon"><img id="icon-open-lib"  aria-hidden="true" src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Blue_Open.png"></div>
                                                        <div class="get-ws-choise-text" id="from-my-lib">From <span>My Library</span></div>
                                                        <div class="clearfix"></div>
                                                        <div class="lesson-detail-lib" style="display:none;">
                                                            <div class="border-selectall border-selectall-1 active">
                                                                <div class="col-md-6 col-sm-12 col-xs-12 cb-type-lesson">
                                                                    <label>
                                                                        <input type="checkbox" class="radio_buttons class_cb_search option-input-2 option-input-lesson radio" id="select-all-mylibrary2" value="" name="select-all-mylibrary"/>
                                                                        <span style="padding-left: 18px">Select All</span>
                                                                    </label>

                                                                </div>
                                                                <div class="col-md-3 col-sm-6 col-xs-6" style="float:right;">
                                                                    <div class="form-group">
                                                                        <button type="submit" class="btn-assign form-control" name="my-library" id="search-my-lib2"></span><?php _e('Open Search', 'iii-dictionary') ?></button>
                                                                    </div>

                                                                </div>
                                                                <div class="clearfix"></div>
                                                                <div class="select-style open-search" id="open-search-my-lib2">
                                                                    <div class="search-box">
                                                                        <div class="col-md-9 col-sm-9 col-xs-12 nopadding-l">
                                                                            <div class="form-group">
                                                                                <input  id="search-tit-lib2" name="filter[sheet-name]" class="form-control search-tit" placeholder="Search Title from Here...">
                                                                                <p class="link-clear">Clear</p>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-3 col-sm-3 col-xs-12 nopadding-r">
                                                                            <div class="form-group">
                                                                                <button type="submit" class="btn-blue form-control" name="filter[search]" id="btn-search-my-lib2"</span><?php _e('Search', 'iii-dictionary') ?></button>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="clearfix"></div>
                                                                </div>


                                                                <div class="clearfix"></div>
                                                            </div>

                                                            <div class="table-my-lesson active">
                                                                <div style="max-height: 600px; overflow-y: auto; overflow-x:hidden;">

                                                                    <table class="table table-condensed table-subscription" >

                                                                        <thead>

                                                                            <tr>
                                                                                <th class="table-img-icon" id="icon-option-library2" data-sort="upward" style="width: 5%;"><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Newest.png" class="img-sort2" alt="option lesson" ></th>

                                                                                <th style="width: 20%;"><?php _e('Type', 'iii-dictionary') ?><span class="sorting-indicator"></span></th>

                                                                                <th style="width: 55%;"><?php _e('Worksheet', 'iii-dictionary') ?></th>

                                                                                <th style="width: 20%;"></th>

                                                                            </tr>

                                                                        </thead>
                                                                        <tbody id="table-library2">


                                                                        </tbody>

                                                                    </table>


                                                                </div>

                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12 get-ws-choise">
                                                        <div class="get-ws-choise-icon"><img  id="icon-open-pub"  aria-hidden="true" src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Blue_Open.png"></div>
                                                        <div class="get-ws-choise-text" id="from-pub">From <span>Public Library</span></div>
                                                        <div class="clearfix"></div>
                                                        <div id="tab-public-lib" class="tab-style from-public-lib" style="display:none; margin-top: 10px !important;">
                                                            <ul class="nav nav-tabs">
                                                                <li class="active tab-subs-purchase" id="tab-public-lib-eng2"><a data-toggle="tab"  href="#tab-eng-lib2" id="eng-tab">English</a></li>
                                                                <li class="tab-subs-purchase" id="tab-public-lib-math2"><a data-toggle="tab"  href="#tab-math-lib2" id="math-tab">Math</a></li>
                                                            </ul>

                                                            <div class="tab-content">
                                                                <div id="tab-eng-lib2" class="tab-pane fade in active">
                                                                    <div class="border-selectall border-selectall-1">
                                                                        <div class="col-md-9 col-sm-12 col-xs-12 cb-type-lesson">
                                                                            <label>
                                                                                <input type="checkbox" class="radio_buttons class_cb_search option-input-2 option-input-lesson radio" id="select-all-eng2" value="" name="select-all"/>
                                                                                <span style="padding-left: 18px">Select All</span>
                                                                            </label>
                                                                        </div>
                                                                        <div class="col-md-3 col-sm-6 col-xs-6">
                                                                            <div class="form-group">
                                                                                <button type="submit" class="btn-assign form-control" name="assign-lesson" id="btn-open-search-eng2"></span><?php _e('Open Search', 'iii-dictionary') ?></button>
                                                                            </div>
                                                                        </div>

                                                                        <div class="clearfix"></div>
                                                                        <div class="select-style open-search" id="open-search-eng2" style="display:none;">
                                                                            <div class="search-box">
                                                                                <div class="col-md-9 col-sm-9 col-xs-12 nopadding-l">
                                                                                    <div class="form-group">
                                                                                        <input  id="search-tit-eng2" name="filter[sheet-name]" class="form-control search-tit" placeholder="Search Title from Here...">
                                                                                        <p class="link-clear">Clear</p>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="col-md-3 col-sm-3 col-xs-12 nopadding-r">
                                                                                    <div class="form-group">
                                                                                        <button type="submit" class="btn-blue form-control" name="filter[search]" id="btn-search-eng2"</span><?php _e('Search', 'iii-dictionary') ?></button>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="select-box">


                                                                                <div class="col-md-6 col-sm-6 col-xs-12" style="padding-left: 0px !important;">

                                                                                    <form method="get">
                                                                                        <?php MWHtml::select_grades('ENGLISH', $filter['grade'], array('first_option' => '- Subject -', 'class' => 'form-control', 'name' => 'filter[grade]', 'id' => 'grade-en2')) ?>
                                                                                    </form>
                                                                                </div>
                                                                                <div class="col-md-6 col-sm-6 col-xs-12" style="padding-right: 0px !important;">

                                                                                    <form method="get">
                                                                                        <?php MWHtml::sel_assignments($filter['assignment-id'], false, array(), '- Lesson -', 'filter[assignment-id]', 'form-control', 'filter-assignment-en2') ?>
                                                                                    </form>
                                                                                </div>
                                                                            </div>
                                                                            <div class="clearfix"></div>
                                                                        </div>
                                                                        <div class="clearfix"></div>
                                                                    </div>

                                                                    <div class="table-my-lesson">
                                                                        <div style="max-height: 600px; overflow-y: auto; overflow-x:hidden;">

                                                                            <table class="table table-condensed table-subscription" >

                                                                                <thead>

                                                                                    <tr>
                                                                                        <th class="table-img-icon" id="icon-option-eng2" style="width: 5%;"><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_option.png" class="" alt="option lesson" ></th>
                                                                                <div class="hidden option-detail" id="option-detail-eng2">
                                                                                    <p>Sort by</p>
                                                                                    <ul>
                                                                                        <li id="sort-alphabet-eng">Alphabetical</li>
                                                                                        <li id="sort-type-eng">Type</li>
                                                                                        <li id="sort-new-eng">Newest</li>
                                                                                        <li id="sort-old-eng">Oldest</li>
                                                                                    </ul>
                                                                                </div>
                                                                                <th style="width:20%;"><?php _e('Lesson', 'iii-dictionary') ?><span class="sorting-indicator"></span></th>

                                                                                <th style="width: 45%;"><?php _e('Worksheet', 'iii-dictionary') ?></th>

                                                                                <th style="width: 15%;"><?php _e('Dictionary', 'iii-dictionary') ?></th>
                                                                                <th style="width: 15%;"></th>

                                                                                </tr>

                                                                                </thead>
                                                                                <tbody  class="table-eng-worksheet">



                                                                                </tbody>

                                                                            </table>


                                                                        </div>

                                                                    </div>
                                                                    <div id="pagination-result2" class="pagination-result">

                                                                    </div>
                                                                </div>
                                                                <div id="tab-math-lib2" class="tab-pane fade">
                                                                    <div class="border-selectall border-selectall-1">
                                                                        <div class="col-md-9 col-sm-12 col-xs-12 cb-type-lesson">
                                                                            <label>
                                                                                <input type="checkbox" class="radio_buttons class_cb_search option-input-2 option-input-lesson radio" id="select-all-math2" value="" name="select-all"/>
                                                                                <span style="padding-left: 18px">Select All</span>
                                                                            </label>
                                                                        </div>
                                                                        <div class="col-md-3 col-sm-6 col-xs-6">
                                                                            <div class="form-group">
                                                                                <button type="submit" class="btn-assign form-control" name="assign-lesson" id="btn-open-search2"></span><?php _e('Open Search', 'iii-dictionary') ?></button>
                                                                            </div>
                                                                        </div>

                                                                        <div class="clearfix"></div>
                                                                        <div class="select-style open-search" id="open-search-math2" style="display:none;">
                                                                            <div class="search-box">
                                                                                <div class="col-md-9 col-sm-9 col-xs-12 nopadding-l">
                                                                                    <div class="form-group">
                                                                                        <input  id="filter-sheet-name2" name="filter[sheet-name]" class="form-control search-tit" placeholder="Search Title from Here...">
                                                                                        <p class="link-clear">Clear</p>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="col-md-3 col-sm-3 col-xs-12 nopadding-r">
                                                                                    <div class="form-group">
                                                                                        <button type="submit" class="btn-blue form-control" name="filter[search]" id="btn-search-math2"</span><?php _e('Search', 'iii-dictionary') ?></button>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="select-box">
                                                                                <div class="col-md-6 col-sm-6 col-xs-12" style="padding-left: 0px !important;">

                                                                                    <form method="get">
                                                                                        <select class="select-box-it form-control" name="filter[cat-level]" id="filter-level-categories2">
                                                                                            <option selected value="">-Category-</option>
                                                                                            <?php foreach ($main_categories as $item) : ?>
                                                                                                <option value="<?php echo $item->id ?>"<?php echo $filter['cat-level'] == $item->id ? ' selected' : '' ?>><?php echo $item->name ?></option>
                                                                                            <?php endforeach ?>
                                                                                        </select>
                                                                                    </form>
                                                                                </div>
                                                                                <div class="col-md-6 col-sm-6 col-xs-12" style="padding-right: 0px !important;">
                                                                                    <form method="get">
                                                                                        <select class="select-box-it form-control" name="filter[level]" id="filter-levels2" data-selected="<?php echo $filter['level'] ?>" >
                                                                                            <option selected value="" >-Subject-</option>

                                                                                        </select>
                                                                                    </form>
                                                                                </div>
                                                                                <div class="col-md-6 col-sm-6 col-xs-12" style="padding-left: 0px !important;">
                                                                                    <form method="get">
                                                                                        <select class="select-box-it form-control" id="filter-sublevels2" name="filter[sublevel]" data-selected="<?php echo $filter['sublevel'] ?>">
                                                                                            <option selected value="" >-Lesson-</option>

                                                                                        </select>
                                                                                    </form>
                                                                                </div>
                                                                                <div class="col-md-6 col-sm-6 col-xs-12" style="padding-right: 0px !important;">

                                                                                    <form method="get">
                                                                                        <select class="select-box-it form-control" name="worksheet-format" id="worksheet-format2" >
                                                                                            <option selected value="">-Worksheet Format- </option>
                                                                                            <?php
                                                                                            $worksheet = MWHtml::sel_math_assignments($filter['assignment-id']);
                                                                                            foreach ($worksheet as $item):
                                                                                                ?>
                                                                                                <option value="<?php echo $item->id ?>"<?php echo $selected == $item->id ? ' selected' : '' ?>><?php echo $item->name ?></option>
                                                                                            <?php endforeach ?>
                                                                                        </select>
                                                                                    </form>
                                                                                </div>
                                                                            </div>
                                                                            <div class="clearfix"></div>
                                                                        </div>

                                                                        <div class="clearfix"></div>
                                                                    </div>

                                                                    <div class="table-my-lesson">
                                                                        <div style="max-height: 600px; overflow-y: auto; overflow-x:hidden;">

                                                                            <table class="table table-condensed table-subscription" >

                                                                                <thead>

                                                                                    <tr>
                                                                                        <th class="table-img-icon" id="icon-option-math2" style="width: 5%;"><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_option.png" class="" alt="option lesson" ></th>
                                                                                <div class="hidden option-detail" id="option-detail-math2">
                                                                                    <p>Sort by</p>
                                                                                    <ul>
                                                                                        <li id="sort-alphabet-math">Alphabetical</li>
                                                                                        <li id="sort-type-math">Type</li>
                                                                                        <li id="sort-new-math">Newest</li>
                                                                                        <li id="sort-old-math">Oldest</li>
                                                                                    </ul>
                                                                                </div>
                                                                                <th style="width:20%;"><?php _e('Category', 'iii-dictionary') ?><span class="sorting-indicator"></span></th>

                                                                                <th style="width: 55%;"><?php _e('Worksheet', 'iii-dictionary') ?></th>

                                                                                <th style="width: 15%;"></th>

                                                                                </tr>

                                                                                </thead>
                                                                                <tbody  class="table-math-worksheet">

                                                                                </tbody>

                                                                            </table>


                                                                        </div>

                                                                    </div>
                                                                    <div id="pagination-result-math2" class="pagination-result">

                                                                    </div>

                                                                </div>

                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="btn-add-ws col-md-3 nopadding-r" id="">
                                                        <button id="btn-add-ws" type="submit" class="btn-orange btn-create-lesson form-control nopadding-r" ></span><?php _e('Add', 'iii-dictionary') ?></button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>


                                    </div>
                                </div>
                                <!--public library-->
                                <div id="public-lib" class="style-form tab-pane fade">  
                                    <h3>Public Library</h3>
                                    <div class="subscription">
                                        <p class="text-intro-tab">Public Library has pre-made worksheets created for you to use. Save the worksheets into your <span>My Library</span> to use them. </p>
                                        <div id="tab-public-lib" class="tab-style">
                                            <ul class="nav nav-tabs">
                                                <li class="active tab-subs-purchase" id="tab-public-lib-eng"><a data-toggle="tab"  href="#tab-eng-lib" id="eng-tab">English</a></li>
                                                <li class="tab-subs-purchase" id="tab-public-lib-math"><a data-toggle="tab"  href="#tab-math-lib" id="math-tab">Math</a></li>

                                            </ul>

                                            <div class="tab-content">
                                                <div id="tab-eng-lib" class="tab-pane fade in active">
                                                    <div class="border-selectall border-selectall-1">
                                                        <div class="col-md-6 col-sm-12 col-xs-12 cb-type-lesson">
                                                            <label>
                                                                <input type="checkbox" class="radio_buttons class_cb_search option-input-2 option-input-lesson radio" id="select-all-eng" value="" name="select-all"/>
                                                                <span style="padding-left: 18px">Select All</span>
                                                            </label>
                                                        </div>
                                                        <div class="col-md-3 col-sm-6 col-xs-6">
                                                            <div class="form-group">
                                                                <button type="submit" class="btn-assign form-control" name="assign-lesson" id="btn-open-search-eng"></span><?php _e('Open Search', 'iii-dictionary') ?></button>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3 col-sm-6 col-xs-6 nopadding-r">
                                                            <div class="form-group">
                                                                <button type="submit" class="btn-orange btn-create-lesson form-control nopadding-r" name="save-to-lib" id="save-eng-lib"></span><?php _e('Save to My Library', 'iii-dictionary') ?></button>
                                                            </div>
                                                        </div>
                                                        <div class="clearfix"></div>
                                                        <div class="select-style open-search" id="open-search-eng">
                                                            <div class="search-box">
                                                                <div class="col-md-9 col-sm-9 col-xs-12 nopadding-l">
                                                                    <div class="form-group">
                                                                        <input  id="search-tit-eng" name="filter[sheet-name]" class="form-control search-tit" placeholder="Search Title from Here...">
                                                                        <p class="link-clear">Clear</p>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-3 col-sm-3 col-xs-12 nopadding-r">
                                                                    <div class="form-group">
                                                                        <button type="submit" class="btn-blue form-control" name="filter[search]" id="btn-search-eng"</span><?php _e('Search', 'iii-dictionary') ?></button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="select-box">


                                                                <div class="col-md-6 col-sm-6 col-xs-12" style="padding-left: 0px !important;">

                                                                    <form method="get">
                                                                        <?php MWHtml::select_grades('ENGLISH', $filter['grade'], array('first_option' => '- Subject -', 'class' => 'form-control', 'name' => 'filter[grade]', 'id' => 'grade-en')) ?>
                                                                    </form>
                                                                </div>
                                                                <div class="col-md-6 col-sm-6 col-xs-12" style="padding-right: 0px !important;">

                                                                    <form method="get">
                                                                        <?php MWHtml::sel_assignments($filter['assignment-id'], false, array(), '- Lesson -', 'filter[assignment-id]', 'form-control', 'filter-assignment-en') ?>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                            <div class="clearfix"></div>
                                                        </div>
                                                        <div class="clearfix"></div>
                                                    </div>

                                                    <div class="table-my-lesson">
                                                        <div style="max-height: 600px; overflow-y: auto; overflow-x:hidden;">

                                                            <table class="table table-condensed table-subscription" >

                                                                <thead>

                                                                    <tr>
                                                                        <th class="table-img-icon" id="icon-option-eng" style="width: 5%;"><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_option.png" class="" alt="option lesson" ></th>
                                                                <div class="hidden option-detail" id="option-detail-eng">
                                                                    <p>Sort by</p>
                                                                    <ul>
                                                                        <li id="sort-alphabet-eng">Alphabetical</li>
                                                                        <li id="sort-type-eng">Type</li>
                                                                        <li id="sort-new-eng">Newest</li>
                                                                        <li id="sort-old-eng">Oldest</li>
                                                                    </ul>
                                                                </div>
                                                                <th style="width:20%;"><?php _e('Lesson', 'iii-dictionary') ?><span class="sorting-indicator"></span></th>

                                                                <th style="width: 45%;"><?php _e('Worksheet', 'iii-dictionary') ?></th>

                                                                <th style="width: 15%;"><?php _e('Dictionary', 'iii-dictionary') ?></th>
                                                                <th style="width: 15%;"></th>

                                                                </tr>

                                                                </thead>
                                                                <tbody id="table-eng-worksheet">



                                                                </tbody>

                                                            </table>


                                                        </div>

                                                    </div>
                                                    <div id="pagination-result" class="pagination-result">

                                                    </div>

                                                </div>
                                                <div id="tab-math-lib" class="tab-pane fade">
                                                    <div class="border-selectall">
                                                        <div class="col-md-6 col-sm-12 col-xs-12 cb-type-lesson">
                                                            <label>
                                                                <input type="checkbox" class="radio_buttons class_cb_search option-input-2 option-input-lesson radio" id="select-all-math" value="" name="select-all"/>
                                                                <span style="padding-left: 18px">Select All</span>
                                                            </label>
                                                        </div>
                                                        <div class="col-md-3 col-sm-6 col-xs-6">
                                                            <div class="form-group">
                                                                <button type="submit" class="btn-assign form-control" name="assign-lesson" id="btn-open-search"></span><?php _e('Open Search', 'iii-dictionary') ?></button>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3 col-sm-6 col-xs-6 nopadding-r">
                                                            <div class="form-group">
                                                                <button type="submit" class="btn-orange btn-create-lesson form-control nopadding-r" name="save-to-lib" id="save-math-lib"></span><?php _e('Save to My Library', 'iii-dictionary') ?></button>
                                                            </div>
                                                        </div>
                                                        <div class="clearfix"></div>
                                                        <div class="select-style open-search" id="open-search-math">
                                                            <div class="search-box">
                                                                <div class="col-md-9 col-sm-9 col-xs-12 nopadding-l">
                                                                    <div class="form-group">
                                                                        <input  id="filter-sheet-name" name="filter[sheet-name]" class="form-control search-tit" placeholder="Search Title from Here...">
                                                                        <p class="link-clear">Clear</p>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-3 col-sm-3 col-xs-12 nopadding-r">
                                                                    <div class="form-group">
                                                                        <button type="submit" class="btn-blue form-control" name="filter[search]" id="btn-search-math"</span><?php _e('Search', 'iii-dictionary') ?></button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="select-box">
                                                                <div class="col-md-6 col-sm-6 col-xs-12" style="padding-left: 0px !important;">
                                                                    <form method="get">
                                                                        <select class="select-box-it form-control" name="filter[cat-level]" id="filter-level-categories">
                                                                            <option selected value="">-Category-</option>
                                                                            <?php foreach ($main_categories as $item) : ?>
                                                                                <option value="<?php echo $item->id ?>"<?php echo $filter['cat-level'] == $item->id ? ' selected' : '' ?>><?php echo $item->name ?></option>
                                                                            <?php endforeach ?>
                                                                        </select>
                                                                    </form>
                                                                </div>
                                                                <div class="col-md-6 col-sm-6 col-xs-12" style="padding-right: 0px !important;">
                                                                    <form method="get">
                                                                        <select class="select-box-it form-control" name="filter[level]" id="filter-levels" data-selected="<?php echo $filter['level'] ?>" >
                                                                            <option selected value="" >-Subject-</option>

                                                                        </select>
                                                                    </form>
                                                                </div>
                                                                <div class="col-md-6 col-sm-6 col-xs-12" style="padding-left: 0px !important;">
                                                                    <form method="get">
                                                                        <select class="select-box-it form-control" id="filter-sublevels" name="filter[sublevel]" data-selected="<?php echo $filter['sublevel'] ?>">
                                                                            <option selected value="" >-Lesson-</option>

                                                                        </select>
                                                                    </form>
                                                                </div>
                                                                <div class="col-md-6 col-sm-6 col-xs-12" style="padding-right: 0px !important;">
                                                                    <form method="get">
                                                                        <select class="select-box-it form-control" name="worksheet-format" id="worksheet-format" >
                                                                            <option selected value="">-Worksheet Format- </option>
                                                                            <?php
                                                                            $worksheet = MWHtml::sel_math_assignments($filter['assignment-id']);
                                                                            foreach ($worksheet as $item):
                                                                                ?>
                                                                                <option value="<?php echo $item->id ?>"<?php echo $selected == $item->id ? ' selected' : '' ?>><?php echo $item->name ?></option>
                                                                            <?php endforeach ?>
                                                                        </select>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                            <div class="clearfix"></div>
                                                        </div>

                                                        <div class="clearfix"></div>
                                                    </div>

                                                    <div class="table-my-lesson">
                                                        <div style="max-height: 600px; overflow-y: auto; overflow-x:hidden;">

                                                            <table class="table table-condensed table-subscription" >

                                                                <thead>

                                                                    <tr>
                                                                        <th class="table-img-icon" id="icon-option-math" style="width: 5%;"><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_option.png" class="" alt="option lesson" ></th>
                                                                <div class="hidden option-detail" id="option-detail-math">
                                                                    <p>Sort by</p>
                                                                    <ul>
                                                                        <li id="sort-alphabet-math">Alphabetical</li>
                                                                        <li id="sort-type-math">Type</li>
                                                                        <li id="sort-new-math">Newest</li>
                                                                        <li id="sort-old-math">Oldest</li>
                                                                    </ul>
                                                                </div>
                                                                <th style="width:20%;"><?php _e('Category', 'iii-dictionary') ?><span class="sorting-indicator"></span></th>

                                                                <th style="width: 55%;"><?php _e('Worksheet', 'iii-dictionary') ?></th>

                                                                <th style="width: 15%;"></th>

                                                                </tr>

                                                                </thead>
                                                                <tbody id="table-math-worksheet">

                                                                </tbody>

                                                            </table>


                                                        </div>

                                                    </div>
                                                    <div id="pagination-result-math" class="pagination-result">

                                                    </div>


                                                </div>

                                            </div>
                                        </div>

                                    </div>
                                </div>
                                <!--end public library-->
                                <!--My Library-->
                                <div id="my-library" class="style-form tab-pane fade">  
                                    <h3>My library</h3>
                                    <div class="subscription">
                                        <p class="text-intro-tab">My Library are collections of worksheets, both yours and from the <span>Public Library</span>. Use these worksheets in your library to create a lesson. You can create your own worksheet using the worksheet tool.</p>
                                        <div class="library-lib">
                                            <hr style="border-top: 1px solid #8e8e8e;margin-bottom: 10px">
                                            <div class="lesson-detail2">
                                                <div class="border-selectall active" style="padding-bottom: 0;">
                                                    <div class="col-md-3 col-sm-6 col-xs-6 cb-type-lesson">
                                                        <label>
                                                            <input type="checkbox" class="radio_buttons class_cb_search option-input-2 option-input-lesson radio" id="select-all-mylibrary" value="" name="select-all-mylibrary"/>
                                                            <span style="padding-left: 18px">Select All</span>
                                                        </label>
                                                    </div>
                                                    <div class="col-md-3 col-sm-6 col-xs-6">
                                                        <div class="form-group">
                                                            <button type="submit" class="btn-assign form-control" name="my-library" id="search-my-lib"></span><?php _e('Open Search', 'iii-dictionary') ?></button>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 col-sm-6 col-xs-6">
                                                        <div class="form-group">
                                                            <button id="btn-create-library" type="submit" class="btn-orange btn-create-lesson form-control nopadding-r" name="create-lesson"></span><?php _e('Create Lesson', 'iii-dictionary') ?></button>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 col-sm-6 col-xs-6 nopadding-r">
                                                        <div class="form-group">
                                                            <button id="add-more-btn" type="submit" class="btn-gray btn-create-lesson form-control nopadding-r" name="create-lesson"></span><?php _e('Add more', 'iii-dictionary') ?></button>
                                                        </div>
                                                    </div>
                                                    <div class="clearfix"></div>
                                                    <div class="select-style open-search" id="open-search-my-lib">
                                                        <div class="search-box">
                                                            <div class="col-md-9 col-sm-9 col-xs-12 nopadding-l">
                                                                <div class="form-group">
                                                                    <input  id="search-tit-lib" name="filter[sheet-name]" class="form-control search-tit" placeholder="Search Title from Here...">
                                                                    <p class="link-clear">Clear</p>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-3 col-sm-3 col-xs-12 nopadding-r">
                                                                <div class="form-group">
                                                                    <button type="submit" class="btn-blue form-control" name="filter[search]" id="btn-search-my-lib"</span><?php _e('Search', 'iii-dictionary') ?></button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="clearfix"></div>
                                                    </div>
                                                    <div class="clearfix"></div>
                                                </div>
                                                <div class="create-lesson-content hidden">
                                                    <div class="header-create-lesson">
                                                        <h4 class="title-create">Creating a Lesson<img id="close-icon" src="http://ikteacher.com/wp-content/themes/ik-learn/library/images/Icon_Close.png" style="width: 15px;float: right"></h4>  
                                                    </div>
                                                    <div style="float: left;width: 70%">

                                                        <h5 class="lesson-name">Lesson Name</h5>
                                                        <input class="form-control" value="" id="input-style">
                                                    </div>
                                                    <div style="float: right;width: 28%">
                                                        <h5 class="lesson-name">Type of the Lesson</h5>
                                                        <form method="get">
                                                            <select class="select-box-it form-control" name="sl-class" id="select-my-lib" data-selected="" >
                                                                <option id="option-mylib" value="1">English</option>

                                                            </select>
                                                        </form>
                                                    </div>
                                                    <div class=" col-nopadding" style="margin-top: 100px; width: 100%" >
                                                        <label for="about-class"><?php _e('Short description of the class', 'iii-dictionary') ?></label>
                                                        <div id="desc-mylibrary">
                                                            <?php
                                                            $editor_settings = array(
                                                                'wpautop' => false,
                                                                'media_buttons' => false,
                                                                'quicktags' => false,
                                                                'editor_height' => 81,
                                                                'textarea_rows' => 15,
                                                                'tinymce' => array(
                                                                    'toolbar1' => 'bold,italic,strikethrough,image,bullist,numlist,blockquote,hr,alignleft,aligncenter,alignright,spellchecker,fullscreen,wp_adv'
                                                                )
                                                            );
                                                            ?>
                                                            <?php wp_editor('', 'post_mylibrary', $editor_settings); ?>
                                                            <div class="clear-both"></div>
                                                        </div>
                                                    </div>

                                                    <h5 class="lesson-name2">Confirm Worksheets</h5>
                                                    <table style="width: 100%;margin-bottom: 20px;" id="table-confirm-worksheet">


                                                    </table>
                                                    <button id="cancel-btn1" type="submit" class="btn-gray btn-create-lesson form-control nopadding-r" name="create-lesson"></span><?php _e('Cancel', 'iii-dictionary') ?></button>
                                                    <button id="create-btn1" type="submit" class="btn-orange btn-create-lesson form-control nopadding-r" name="create-lesson"></span><?php _e('Create Lesson', 'iii-dictionary') ?></button>
                                                </div>
                                                <div class="table-my-lesson active">
                                                    <div style="max-height: 600px; overflow-y: auto; overflow-x:hidden;">

                                                        <table class="table table-condensed table-subscription" >

                                                            <thead>

                                                                <tr>
                                                                    <th class="table-img-icon" id="icon-option-library" data-sort="upward" style="width: 5%;"><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Newest.png" class="img-sort" alt="option lesson" ></th>

                                                                    <th style="width: 20%;"><?php _e('Type', 'iii-dictionary') ?><span class="sorting-indicator"></span></th>

                                                                    <th style="width: 55%;"><?php _e('Worksheet', 'iii-dictionary') ?></th>

                                                                    <th style="width: 20%;"></th>

                                                                </tr>

                                                            </thead>
                                                            <tbody id="table-library">


                                                            </tbody>

                                                        </table>


                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                        <div id="add-worksheet-mylib" class="hidden add-worksheet">
                                            <div class="">
                                                <div class="head-addws col-md-12">
                                                    <p class="lesson-icon"><span>Add Worksheets: </span> Where would you like to get worksheets from?</p>
                                                    <div class="close-icon"> <img  id="icon-close-addws-lib"   aria-hidden="true" src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Close.png"></div>
                                                </div>

                                                <div class="body-addws">

                                                    <div class="col-md-12 get-ws-choise">
                                                        <div class="get-ws-choise-icon"><img  id="icon-open-pub2"  aria-hidden="true" src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Blue_Open.png"></div>
                                                        <div class="get-ws-choise-text" id="from-pub2">From <span>Public Library</span></div>
                                                        <div class="clearfix"></div>
                                                        <div id="tab-public-lib2" class="tab-style from-public-lib2" style="display:none; margin-top: 10px !important;">
                                                            <ul class="nav nav-tabs">
                                                                <li class="active tab-subs-purchase" id="tab-public-lib-eng3"><a data-toggle="tab"  href="#tab-eng-lib3" id="eng-tab">English</a></li>
                                                                <li class="tab-subs-purchase" id="tab-public-lib-math3"><a data-toggle="tab"  href="#tab-math-lib3" id="math-tab">Math</a></li>
                                                            </ul>

                                                            <div class="tab-content">
                                                                <div id="tab-eng-lib3" class="tab-pane fade in active">
                                                                    <div class="border-selectall" style="border-top: none !important;">
                                                                        <div class="col-md-9 col-sm-12 col-xs-12 cb-type-lesson">
                                                                            <label>
                                                                                <input type="checkbox" class="radio_buttons class_cb_search option-input-2 option-input-lesson radio" id="select-all-eng3" value="" name="select-all"/>
                                                                                <span style="padding-left: 18px">Select All</span>
                                                                            </label>
                                                                        </div>
                                                                        <div class="col-md-3 col-sm-6 col-xs-6">
                                                                            <div class="form-group">
                                                                                <button type="submit" class="btn-assign form-control" name="assign-lesson" id="btn-open-search-eng3"></span><?php _e('Open Search', 'iii-dictionary') ?></button>
                                                                            </div>
                                                                        </div>

                                                                        <div class="clearfix"></div>
                                                                        <div class="select-style open-search" id="open-search-eng3" style="display:none;">
                                                                            <div class="search-box">
                                                                                <div class="col-md-9 col-sm-9 col-xs-12 nopadding-l">
                                                                                    <div class="form-group">
                                                                                        <input  id="search-tit-eng3" name="filter[sheet-name]" class="form-control search-tit" placeholder="Search Title from Here...">
                                                                                        <p class="link-clear">Clear</p>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="col-md-3 col-sm-3 col-xs-12 nopadding-r">
                                                                                    <div class="form-group">
                                                                                        <button type="submit" class="btn-blue form-control" name="filter[search]" id="btn-search-eng3"</span><?php _e('Search', 'iii-dictionary') ?></button>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="select-box">


                                                                                <div class="col-md-6 col-sm-6 col-xs-12" style="padding-left: 0px !important;">

                                                                                    <form method="get">
                                                                                        <?php MWHtml::select_grades('ENGLISH', $filter['grade'], array('first_option' => '- Subject -', 'class' => 'form-control', 'name' => 'filter[grade]', 'id' => 'grade-en3')) ?>

                                                                                    </form>
                                                                                </div>
                                                                                <div class="col-md-6 col-sm-6 col-xs-12" style="padding-right: 0px !important;">

                                                                                    <form method="get">
                                                                                        <?php MWHtml::sel_assignments($filter['assignment-id'], false, array(), '- Lesson -', 'filter[assignment-id]', 'form-control', 'filter-assignment-en3') ?>
                                                                                    </form>
                                                                                </div>
                                                                            </div>
                                                                            <div class="clearfix"></div>
                                                                        </div>
                                                                        <div class="clearfix"></div>
                                                                    </div>

                                                                    <div class="table-my-lesson">
                                                                        <div style="max-height: 600px; overflow-y: auto; overflow-x:hidden;">

                                                                            <table class="table table-condensed table-subscription" >

                                                                                <thead>

                                                                                    <tr>
                                                                                        <th class="table-img-icon" id="icon-option-eng3" style="width: 5%;"><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_option.png" class="" alt="option lesson" ></th>
                                                                                <div class="hidden option-detail" id="option-detail-eng3">
                                                                                    <p>Sort by</p>
                                                                                    <ul>
                                                                                        <li id="sort-alphabet-eng">Alphabetical</li>
                                                                                        <li id="sort-type-eng">Type</li>
                                                                                        <li id="sort-new-eng">Newest</li>
                                                                                        <li id="sort-old-eng">Oldest</li>
                                                                                    </ul>
                                                                                </div>
                                                                                <th style="width:20%;"><?php _e('Level Category', 'iii-dictionary') ?><span class="sorting-indicator"></span></th>

                                                                                <th style="width: 45%;"><?php _e('Worksheet Name', 'iii-dictionary') ?></th>

                                                                                <th style="width: 15%;"><?php _e('Dictionary', 'iii-dictionary') ?></th>
                                                                                <th style="width: 15%;"></th>

                                                                                </tr>

                                                                                </thead>
                                                                                <tbody  class="table-eng-worksheet">



                                                                                </tbody>

                                                                            </table>


                                                                        </div>

                                                                    </div>
                                                                    <div id="pagination-result3" class="pagination-result">

                                                                    </div>

                                                                </div>
                                                                <div id="tab-math-lib3" class="tab-pane fade">
                                                                    <div class="border-selectall" style="border-top: none !important;">
                                                                        <div class="col-md-9 col-sm-12 col-xs-12 cb-type-lesson">
                                                                            <label>
                                                                                <input type="checkbox" class="radio_buttons class_cb_search option-input-2 option-input-lesson radio" id="select-all-math3" value="" name="select-all"/>
                                                                                <span style="padding-left: 18px">Select All</span>
                                                                            </label>
                                                                        </div>
                                                                        <div class="col-md-3 col-sm-6 col-xs-6">
                                                                            <div class="form-group">
                                                                                <button type="submit" class="btn-assign form-control" name="assign-lesson" id="btn-open-search3"></span><?php _e('Open Search', 'iii-dictionary') ?></button>
                                                                            </div>
                                                                        </div>

                                                                        <div class="clearfix"></div>
                                                                        <div class="select-style open-search" id="open-search-math3" style="display:none;">
                                                                            <div class="search-box">
                                                                                <div class="col-md-9 col-sm-9 col-xs-12 nopadding-l">
                                                                                    <div class="form-group">
                                                                                        <input  id="filter-sheet-name3" name="filter[sheet-name]" class="form-control search-tit" placeholder="Search Title from Here...">
                                                                                        <p class="link-clear">Clear</p>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="col-md-3 col-sm-3 col-xs-12 nopadding-r">
                                                                                    <div class="form-group">
                                                                                        <button type="submit" class="btn-blue form-control" name="filter[search]" id="btn-search-math3"</span><?php _e('Search', 'iii-dictionary') ?></button>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="select-box">
                                                                                <div class="col-md-6 col-sm-6 col-xs-12" style="padding-left: 0px !important;">
                                                                                    <form method="get">
                                                                                        <select class="select-box-it form-control" name="filter[cat-level]" id="filter-level-categories3">
                                                                                            <option selected value="">-Category-</option>
                                                                                            <?php foreach ($main_categories as $item) : ?>
                                                                                                <option value="<?php echo $item->id ?>"<?php echo $filter['cat-level'] == $item->id ? ' selected' : '' ?>><?php echo $item->name ?></option>
                                                                                            <?php endforeach ?>
                                                                                        </select>
                                                                                    </form>
                                                                                </div>
                                                                                <div class="col-md-6 col-sm-6 col-xs-12" style="padding-right: 0px !important;">
                                                                                    <form method="get">
                                                                                        <select class="select-box-it form-control" name="filter[level]" id="filter-levels3" data-selected="<?php echo $filter['level'] ?>" >
                                                                                            <option selected value="" >-Subject-</option>

                                                                                        </select>
                                                                                    </form>
                                                                                </div>
                                                                                <div class="col-md-6 col-sm-6 col-xs-12" style="padding-left: 0px !important;">
                                                                                    <form method="get">
                                                                                        <select class="select-box-it form-control" id="filter-sublevels3" name="filter[sublevel]" data-selected="<?php echo $filter['sublevel'] ?>">
                                                                                            <option selected value="" >-Lesson-</option>

                                                                                        </select>
                                                                                    </form>
                                                                                </div>
                                                                                <div class="col-md-6 col-sm-6 col-xs-12" style="padding-right: 0px !important;">
                                                                                    <form method="get">
                                                                                        <select class="select-box-it form-control" name="worksheet-format" id="worksheet-format3" >
                                                                                            <option selected value="">-Worksheet Format- </option>
                                                                                            <?php
                                                                                            $worksheet = MWHtml::sel_math_assignments($filter['assignment-id']);
                                                                                            foreach ($worksheet as $item):
                                                                                                ?>
                                                                                                <option value="<?php echo $item->id ?>"<?php echo $selected == $item->id ? ' selected' : '' ?>><?php echo $item->name ?></option>
                                                                                            <?php endforeach ?>
                                                                                        </select>
                                                                                    </form>
                                                                                </div>
                                                                            </div>
                                                                            <div class="clearfix"></div>
                                                                        </div>

                                                                        <div class="clearfix"></div>
                                                                    </div>

                                                                    <div class="table-my-lesson">
                                                                        <div style="max-height: 600px; overflow-y: auto; overflow-x:hidden;">

                                                                            <table class="table table-condensed table-subscription" >

                                                                                <thead>

                                                                                    <tr>
                                                                                        <th class="table-img-icon" id="icon-option-math3" style="width: 5%;"><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_option.png" class="" alt="option lesson" ></th>
                                                                                <div class="hidden option-detail" id="option-detail-math3">
                                                                                    <p>Sort by</p>
                                                                                    <ul>
                                                                                        <li id="sort-alphabet-math">Alphabetical</li>
                                                                                        <li id="sort-type-math">Type</li>
                                                                                        <li id="sort-new-math">Newest</li>
                                                                                        <li id="sort-old-math">Oldest</li>
                                                                                    </ul>
                                                                                </div>
                                                                                <th style="width:20%;"><?php _e('Level Category', 'iii-dictionary') ?><span class="sorting-indicator"></span></th>

                                                                                <th style="width: 55%;"><?php _e('Worksheet Name', 'iii-dictionary') ?></th>

                                                                                <th style="width: 15%;"></th>

                                                                                </tr>

                                                                                </thead>
                                                                                <tbody  class="table-math-worksheet">

                                                                                </tbody>

                                                                            </table>


                                                                        </div>

                                                                    </div>
                                                                    <div id="pagination-result-math3" class="pagination-result">

                                                                    </div>


                                                                </div>

                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="btn-add-ws col-md-3 nopadding-r" id="">
                                                        <button id="btn-add-ws-lib" type="submit" class="btn-orange btn-create-lesson form-control nopadding-r" ></span><?php _e('Add', 'iii-dictionary') ?></button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- end of My Library-->

                                <!--Public lesson-->
                                <div id="ready-lesson" class="style-form tab-pane fade">  
                                    <h3>Public Lessons</h3>
                                    <div class="subscription">
                                        <p class="text-intro-tab">Public lessons in the list below are available for offering to your class. They have a subscription price that the student needs to pay when a student joins your class. If you wish to make your class free to your students, do not include it in your lesson content.</p>
                                        <div id="tab-public-les" class="tab-style1">
                                            <ul class="nav nav-tabs">
                                                <li class="active tab-pub-les refresh-public-lesson" id="tab-public-les-cri"><a data-toggle="tab"  href="#tab-cri-les" id="cri-tab">Critical Lesson</a></li>
                                                <li class="tab-pub-les" id="tab-public-les-ess"><a data-toggle="tab"  href="#tab-ess-les" id="ess-tab">Essentials</a></li>
                                                <li class="tab-pub-les" id="tab-public-les-sat"><a data-toggle="tab"  href="#tab-sat-les" id="sat-tab">SAT</a></li>
                                                <li class="tab-pub-les" id="tab-public-les-cou"><a data-toggle="tab"  href="#tab-cou-les" id="cou-tab">Courses</a></li>
                                            </ul>
                                        </div>
                                        <div class="eng-math-les active" id="eng-math">
                                            <div class="col-md-1" style="padding-left: 0px !important;">
                                                <img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_curve_arrow.png" class="" alt="" id="sub-menu-pub-les">
                                            </div>
                                            <ul class="sub-menu-public">
                                                <li class="col-md-1 cri-eng-math active" id="menu-eng">
                                                    <a data-toggle="tab" class="sub-menu-title" id="eng-menu">English</a>
                                                </li>
                                                <li class="col-md-1 cri-eng-math" id="menu-math">
                                                    <a data-toggle="tab" class="sub-menu-title" id="math-menu">Math</a>
                                                </li>

                                                <div class="clearfix"></div>
                                            </ul>
                                        </div>
                                        <div class="eng-math-les hidden" id="dic-sel-stu">
                                            <div class="col-md-1" style="padding-left: 0px !important;">
                                                <img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_curve_arrow.png" class="" alt="" id="sub-menu-dic-sel">
                                            </div>
                                            <ul class="sub-menu-public">
                                                <li class="col-md-2 cri-eng-math active" id="dict">
                                                    <a data-toggle="tab" class="sub-menu-title" id="dictionary-menu">Dictionary</a>
                                                </li>
                                                <li class="col-md-2 cri-eng-math" id="self-study">
                                                    <a data-toggle="tab" class="sub-menu-title" id="self-study-menu">Self-study</a>
                                                </li>
                                                <div class="clearfix"></div>
                                            </ul>
                                        </div>
                                        <div class="eng-math-les hidden" id="sat">
                                            <div class="col-md-1" style="padding-left: 0px !important;">
                                                <img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_curve_arrow.png" class="" alt="" id="sub-menu-sat">
                                            </div>
                                            <ul class="sub-menu-public">
                                                <li class="col-md-1 cri-eng-math active" id="sat-eng">
                                                    <a data-toggle="tab" class="sub-menu-title" id="eng-sat">English</a>
                                                </li>
                                                <li class="col-md-1 cri-eng-math" id="sat-math">
                                                    <a data-toggle="tab" class="sub-menu-title" id="math-sat">Math</a>
                                                </li>
                                                <div class="clearfix"></div>
                                            </ul>
                                        </div>
                                        <div class="eng-math-les hidden" id="courses">
                                            <div class="col-md-1" style="padding-left: 0px !important;">
                                                <img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_curve_arrow.png" class="" alt="" id="sub-menu-sat">
                                            </div>
                                            <ul class="sub-menu-public">
                                                <li class="cri-eng-math active">
                                                    <a data-toggle="tab" class="sub-menu-title">ikMath Course</a>
                                                </li>

                                                <div class="clearfix"></div>
                                            </ul>
                                        </div>
                                        <div id="eng-public" class="active set-hide-tab">
                                            <div class="border-selectall">
                                                <div class="col-md-5 col-sm-12 col-xs-12 cb-type-lesson">
                                                    <label>
                                                        <input type="checkbox" class="radio_buttons class_cb_search option-input-2 option-input-lesson radio" id="select-all-ready-eng" value="" name="select-all-eng"/>
                                                        <span style="padding-left: 18px">Select All</span>
                                                    </label>
                                                </div>
                                                <div class="col-md-4 col-sm-3 col-xs-3" style="padding-right: 0px !important">
                                                    <div class="form-group">
                                                        <button type="submit" class="btn-ready-lesson-down form-control" name="ready-lesson-down" id="btn-ready-eng-down"></span><?php _e('Search & Assign Lesson', 'iii-dictionary') ?></button>
                                                        <button type="submit" class="btn-ready-lesson-up form-control hidden" name="ready-lesson-up" id="btn-ready-eng-up"></span><?php _e('Search & Assign Lesson', 'iii-dictionary') ?></button>
                                                    </div>           
                                                </div>
                                                <div class="col-md-3 col-sm-6 col-xs-6 nopadding-r">
                                                    <div class="form-group">
                                                        <button type="submit" class="btn-orange btn-create-lesson form-control nopadding-r save-to-lesson" data-id="critical lesson" name="create-lesson"></span><?php _e('Save to My Lesson', 'iii-dictionary') ?></button>
                                                    </div>
                                                </div>
                                                <div class="clearfix"></div>

                                                <div class="select-style" id="open-search-ready-eng">
                                                    <div class="search-box">
                                                        <div class="col-md-9 col-sm-9 col-xs-12 nopadding-l">
                                                            <div class="form-group">
                                                                <input  id="search-ready-les-eng" name="search-ready-les" class="form-control search-tit" placeholder="Search Lesson from Here...">
                                                                <p class="link-clear">Clear</p>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3 col-sm-3 col-xs-12 nopadding-r">
                                                            <div class="form-group">
                                                                <button type="submit" class="btn-blue form-control" name="btn-search-ready" id="search-ready-btn-eng"</span><?php _e('Search', 'iii-dictionary') ?></button>
                                                            </div>
                                                        </div>
                                                        <div class="clearfix"></div>
                                                    </div>
                                                    <div class="col-md-9 col-sm-9 col-xs-12" style="padding-left: 0px !important;">
                                                        <form method="get">
                                                            <select class="select-box-it form-control" name="sl-class" id="select-lesson-eng" >
                                                                <option selected value="1">- Select a Class to Assign -</option>
                                                                <option value="2" >Intermediate Algebra Math Class - Middle to High School</option>
                                                                <option value="3" >Intermediate Algebra Math Class - Middle to High School</option>
                                                                <option value="4" >Intermediate Algebra Math Class - Middle to High School</option>
                                                            </select>
                                                        </form>
                                                    </div>
                                                    <div class="col-md-3 col-sm-3 col-xs-12" style="padding-right: 0px !important;">
                                                        <div class="form-group">
                                                            <button type="submit" id="assign-btn-eng" class="btn-blue form-control nopadding-r" name="assign-now"></span><?php _e('Assign Now', 'iii-dictionary') ?></button>
                                                        </div>
                                                    </div>

                                                    <div class="clearfix"></div>      
                                                </div>
                                            </div>
                                            <div class="table-my-lesson">
                                                <div style="max-height: 600px; overflow-y: auto; overflow-x:hidden;">

                                                    <table class="table table-condensed table-subscription" >
                                                        <thead>
                                                            <tr>
                                                                <th class="table-img-icon" id="icon-option-ready-eng" data-sort="upward" style="width: 5%;"><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Newest.png" class="img-sort-eng" alt="option lesson" ></th>
                                                                <th style="width: 5%;"><?php _e('Type', 'iii-dictionary') ?><span class="sorting-indicator"></span></th>
                                                                <th style="width: 70%;"><?php _e('Worksheet', 'iii-dictionary') ?></th>
                                                                <th style="width: 20%;"><?php _e('Price', 'iii-dictionary') ?></th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="table-ready-lesson-eng">

                                                        </tbody>

                                                    </table>


                                                </div>

                                            </div>
                                        </div>

                                        <div id="dict-public" class="hidden set-hide-tab">
                                            <div class="border-selectall">
                                                <div class="col-md-5 col-sm-12 col-xs-12 cb-type-lesson">
                                                    <label>
                                                        <input type="checkbox" class="radio_buttons class_cb_search option-input-2 option-input-lesson radio" id="select-all-dict" value="" name="select-all-dictionary"/>
                                                        <span style="padding-left: 18px">Select All</span>
                                                    </label>
                                                </div>
                                                <div class="col-md-4 col-sm-3 col-xs-3" style="padding-right: 0px !important">
                                                    <div class="form-group">
                                                        <button type="submit" class="btn-ready-lesson-down form-control" name="dict-down" id="btn-dict-down"></span><?php _e('Search & Assign Lesson', 'iii-dictionary') ?></button>
                                                        <button type="submit" class="btn-ready-lesson-up form-control hidden" name="dict-up" id="btn-dict-up"></span><?php _e('Search & Assign Lesson', 'iii-dictionary') ?></button>
                                                    </div>           
                                                </div>
                                                <div class="col-md-3 col-sm-6 col-xs-6 nopadding-r">
                                                    <div class="form-group">
                                                        <button type="submit" class="btn-orange btn-create-lesson form-control nopadding-r save-to-lesson" data-id="essential" name="create-lesson"></span><?php _e('Save to My Lesson', 'iii-dictionary') ?></button>
                                                    </div>
                                                </div>
                                                <div class="clearfix"></div>
                                                <div class="select-style" id="open-search-dictionary">
                                                    <div class="search-box">
                                                        <div class="col-md-9 col-sm-9 col-xs-12 nopadding-l">
                                                            <div class="form-group">
                                                                <input  id="text-search-essential" name="search-ready-les" class="form-control search-tit" placeholder="Search Lesson from Here...">
                                                                <p class="link-clear">Clear</p>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3 col-sm-3 col-xs-12 nopadding-r">
                                                            <div class="form-group">
                                                                <button type="submit" class="btn-blue form-control" name="btn-search-ready" id="search-essential-btn"</span><?php _e('Search', 'iii-dictionary') ?></button>
                                                            </div>
                                                        </div>
                                                        <div class="clearfix"></div>
                                                    </div>
                                                    <div class="col-md-9 col-sm-9 col-xs-12" style="padding-left: 0px !important;">
                                                        <form method="get">
                                                            <select class="select-box-it form-control" name="sl-class" id="select-dict" >
                                                                <option selected value="1">- Select a Class to Assign -</option>
                                                                <option value="2" >Intermediate Algebra Math Class - Middle to High School</option>
                                                                <option value="3" >Intermediate Algebra Math Class - Middle to High School</option>
                                                                <option value="4" >Intermediate Algebra Math Class - Middle to High School</option>
                                                            </select>
                                                        </form>
                                                    </div>
                                                    <div class="col-md-3 col-sm-3 col-xs-12" style="padding-right: 0px !important;">
                                                        <div class="form-group">
                                                            <button type="submit" id="assign-dict-btn" class="btn-blue form-control nopadding-r" name="assign-now"></span><?php _e('Assign Now', 'iii-dictionary') ?></button>
                                                        </div>
                                                    </div>

                                                    <div class="clearfix"></div>      
                                                </div>
                                            </div>
                                            <div class="table-my-lesson">
                                                <div style="max-height: 600px; overflow-y: auto; overflow-x:hidden;">

                                                    <table class="table table-condensed table-subscription" >
                                                        <thead>
                                                            <tr>
                                                                <th class="table-img-icon" id="icon-option-essential" data-sort="upward" style="width: 5%;"><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Newest.png" class="img-sort-dict" alt="option lesson" ></th>
                                                                <th style="width: 5%;"><?php _e('Type', 'iii-dictionary') ?><span class="sorting-indicator"></span></th>
                                                                <th style="width: 70%;"><?php _e('Worksheet', 'iii-dictionary') ?></th>
                                                                <th style="width: 20%;"><?php _e('Price', 'iii-dictionary') ?></th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="table-dict">

                                                        </tbody>

                                                    </table>


                                                </div>

                                            </div>
                                        </div>
                                        <div id="sat-public" class="hidden set-hide-tab">
                                            <div class="border-selectall">
                                                <div class="col-md-5 col-sm-12 col-xs-12 cb-type-lesson">
                                                    <label>
                                                        <input type="checkbox" class="radio_buttons class_cb_search option-input-2 option-input-lesson radio" id="select-all-sat" value="" name="select-all-sat"/>
                                                        <span style="padding-left: 18px">Select All</span>
                                                    </label>
                                                </div>
                                                <div class="col-md-4 col-sm-3 col-xs-3" style="padding-right: 0px !important">
                                                    <div class="form-group">
                                                        <button type="submit" class="btn-ready-lesson-down form-control" name="ready-lesson-down" id="btn-sat-down"></span><?php _e('Search & Assign Lesson', 'iii-dictionary') ?></button>
                                                    </div>           
                                                </div>
                                                <div class="col-md-3 col-sm-6 col-xs-6 nopadding-r">
                                                    <div class="form-group">
                                                        <button type="submit" class="btn-orange btn-create-lesson form-control nopadding-r save-to-lesson" data-id="sat" name="create-lesson"></span><?php _e('Save to My Lesson', 'iii-dictionary') ?></button>
                                                    </div>
                                                </div>
                                                <div class="clearfix"></div>

                                                <div class="select-style" id="open-search-sat">
                                                    <div class="search-box">
                                                        <div class="col-md-9 col-sm-9 col-xs-12 nopadding-l">
                                                            <div class="form-group">
                                                                <input  id="search-sat" name="search-sat" class="form-control search-tit" placeholder="Search Lesson from Here...">
                                                                <p class="link-clear">Clear</p>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3 col-sm-3 col-xs-12 nopadding-r">
                                                            <div class="form-group">
                                                                <button type="submit" class="btn-blue form-control" name="btn-search-ready" id="search-sat-btn"</span><?php _e('Search', 'iii-dictionary') ?></button>
                                                            </div>
                                                        </div>
                                                        <div class="clearfix"></div>
                                                    </div>
                                                    <div class="col-md-9 col-sm-9 col-xs-12" style="padding-left: 0px !important;">
                                                        <form method="get">
                                                            <select class="select-box-it form-control" name="sl-class" id="select-sat" >
                                                                <option selected value="1">- Select a Class to Assign -</option>
                                                                <option value="2" >Intermediate Algebra Math Class - Middle to High School</option>
                                                                <option value="3" >Intermediate Algebra Math Class - Middle to High School</option>
                                                                <option value="4" >Intermediate Algebra Math Class - Middle to High School</option>
                                                            </select>
                                                        </form>
                                                    </div>
                                                    <div class="col-md-3 col-sm-3 col-xs-12" style="padding-right: 0px !important;">
                                                        <div class="form-group">
                                                            <button type="submit" id="assign-btn-sat" class="btn-blue form-control nopadding-r" name="assign-now"></span><?php _e('Assign Now', 'iii-dictionary') ?></button>
                                                        </div>
                                                    </div>

                                                    <div class="clearfix"></div>      
                                                </div>
                                            </div>
                                            <div class="table-my-lesson">
                                                <div style="max-height: 600px; overflow-y: auto; overflow-x:hidden;">

                                                    <table class="table table-condensed table-subscription" >
                                                        <thead>
                                                            <tr>
                                                                <th class="table-img-icon" id="icon-option-sat" data-sort="upward" style="width: 5%;"><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Newest.png" class="img-sort-sat" alt="option lesson" ></th>
                                                                <th style="width: 5%;"><?php _e('Type', 'iii-dictionary') ?><span class="sorting-indicator"></span></th>
                                                                <th style="width: 70%;"><?php _e('Worksheet', 'iii-dictionary') ?></th>
                                                                <th style="width: 20%;"><?php _e('Price', 'iii-dictionary') ?></th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="table-sat">

                                                        </tbody>

                                                    </table>


                                                </div>

                                            </div>
                                        </div>
                                        <div id="course-public" class="hidden set-hide-tab">
                                            <div class="border-selectall">
                                                <div class="col-md-5 col-sm-12 col-xs-12 cb-type-lesson">
                                                    <label>
                                                        <input type="checkbox" class="radio_buttons class_cb_search option-input-2 option-input-lesson radio" id="select-all-course" value="" name="select-all-course"/>
                                                        <span style="padding-left: 18px">Select All</span>
                                                    </label>
                                                </div>
                                                <div class="col-md-4 col-sm-3 col-xs-3" style="padding-right: 0px !important">
                                                    <div class="form-group">
                                                        <button type="submit" class="btn-ready-lesson-down form-control" name="ready-lesson-down" id="btn-course-down"></span><?php _e('Search & Assign Lesson', 'iii-dictionary') ?></button>
                                                    </div>           
                                                </div>
                                                <div class="col-md-3 col-sm-6 col-xs-6 nopadding-r">
                                                    <div class="form-group">
                                                        <button type="submit" class="btn-orange btn-create-lesson form-control nopadding-r save-to-lesson" data-id="courses" name="create-lesson"></span><?php _e('Save to My Lesson', 'iii-dictionary') ?></button>
                                                    </div>
                                                </div>
                                                <div class="clearfix"></div>

                                                <div class="select-style" id="open-search-course">
                                                    <div class="search-box">
                                                        <div class="col-md-9 col-sm-9 col-xs-12 nopadding-l">
                                                            <div class="form-group">
                                                                <input  id="search-course" name="search-sat" class="form-control search-tit" placeholder="Search Lesson from Here...">
                                                                <p class="link-clear">Clear</p>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3 col-sm-3 col-xs-12 nopadding-r">
                                                            <div class="form-group">
                                                                <button type="submit" class="btn-blue form-control" name="btn-search-ready" id="search-course-btn"</span><?php _e('Search', 'iii-dictionary') ?></button>
                                                            </div>
                                                        </div>

                                                        <div class="clearfix"></div>
                                                    </div>
                                                    <div class="col-md-9 col-sm-9 col-xs-12" style="padding-left: 0px !important;">
                                                        <form method="get">
                                                            <select class="select-box-it form-control" name="sl-class" id="select-class-course" >
                                                                <option selected value="1">- Select a Class to Assign -</option>
                                                                <option value="2" >Intermediate Algebra Math Class - Middle to High School</option>
                                                                <option value="3" >Intermediate Algebra Math Class - Middle to High School</option>
                                                                <option value="4" >Intermediate Algebra Math Class - Middle to High School</option>
                                                            </select>
                                                        </form>
                                                    </div>
                                                    <div class="col-md-3 col-sm-3 col-xs-12" style="padding-right: 0px !important;">
                                                        <div class="form-group">
                                                            <button type="submit" id="assign-btn-course" class="btn-blue form-control nopadding-r" name="assign-now"></span><?php _e('Assign Now', 'iii-dictionary') ?></button>
                                                        </div>
                                                    </div>

                                                    <div class="clearfix"></div>      
                                                </div>
                                            </div>
                                            <div class="table-my-lesson">
                                                <div style="max-height: 600px; overflow-y: auto; overflow-x:hidden;">

                                                    <table class="table table-condensed table-subscription" >
                                                        <thead>
                                                            <tr>
                                                                <th class="table-img-icon" id="icon-option-course" data-sort="upward" style="width: 5%;"><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Newest.png" class="img-sort-sat" alt="option lesson" ></th>
                                                                <th style="width: 5%;"><?php _e('Type', 'iii-dictionary') ?><span class="sorting-indicator"></span></th>
                                                                <th style="width: 70%;"><?php _e('Worksheet', 'iii-dictionary') ?></th>
                                                                <th style="width: 20%;"><?php _e('Price', 'iii-dictionary') ?></th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="table-course">

                                                        </tbody>

                                                    </table>


                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="section-left">
                            <ul id="menu-left-myaccount" class="nav nav-tabs">
                                <li class="active" id="account"><a data-toggle="tab" href="#hom"><img src="<?php echo get_template_directory_uri(); ?>/library/images/1_Menu_Icon_My_Account.png" class="" alt="setting my account" style="width: 23px;margin:30px 0px 20px"></a>
                                </li>

                                <li><a data-toggle="tab" href="#"><img src="<?php echo get_template_directory_uri(); ?>/library/images/3_Menu_Icon_Tutoring.png" class="" alt="setting my account" style="width: 23px;margin:15px 0px"></a></li>
                                <li id="lesson"><a data-toggle="tab"  href="#"><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Create_Lesson.png" class="" alt="setting my account" style="width: 23px;margin:15px 0px"></a></li>
                                <li><a data-toggle="tab" href="#"><img src="<?php echo get_template_directory_uri(); ?>/library/images/6_Menu_Icon_Private_Message.png" class="" alt="setting my account" style="width: 23px;margin:15px 0px"></a></li>
                                <li><a data-toggle="tab" href="#"><img src="<?php echo get_template_directory_uri(); ?>/library/images/7_Menu_Icon_Support_Feedback.png" class="" alt="setting my account" style="width: 23px;margin:15px 0px"></a></li>
                                <li><a data-toggle="tab" href="#"><img src="<?php echo get_template_directory_uri(); ?>/library/images/5_Menu_Icon_Download.png" class="" alt="setting my account" style="width: 23px;margin:15px 0px"></a></li>
                            </ul>
                        </div>

                        <div id="mySidenav" class="sidenav">
                            <ul class="nav nav-tabs none-block">
                                <li><a class="header-menu-left" data-toggle="tab" id="myacc">My Account</a>
                                    <ul class="sub-menu-left" id="sub-myacc">
                                        <li id="sub-createacc" class="active"><a data-toggle="tab" href="#create-account">Create Account</a></li>
                                        <li id="sub-profile"><a data-toggle="tab" href="#profile">Profile</a></li>
                                        <li><a  data-toggle="tab" href="#updateinfo">Update Info</a></li>
                                        <li><a  data-toggle="tab" href="#tutor-regis-tab">Tutor Registration</a></li>
                                        <li><a data-toggle="tab" href="#subscription" id="status-history">Subscription & Points</a></li>
                                        <li><a  data-toggle="tab" href="#earn-pay">Earning and Payment</a></li>

                                    </ul>
                                </li>

                                <li><a class="header-menu-left padd-adjus" href="#">Tutoring</a></li>
                                <li><a class="header-menu-left padd-adjus" data-toggle="tab"  id="lesson-manager">Lesson Manager</a>
                                    <ul class="sub-menu-left" id="sub-lesson-manager">
                                        <li><a data-toggle="tab" href="#my-subject" id="my-subject-li">My Subject</a></li>
                                        <li><a data-toggle="tab" href="#my-lesson" id="my-lesson-li">My Lesson</a></li>
                                        <li class="my-lib-li"><a  data-toggle="tab" href="#my-library" id="my-library-li">My Library</a></li>
                                        <li class="pub-lib-li"><a  data-toggle="tab" href="#public-lib" id="public-library">Public Library</a></li>
                                        <li><a  data-toggle="tab" href="#ready-lesson" id="ready-lesson-li" class="refresh-public-lesson">Public Lesson</a></li>
                                    </ul>
                                </li>
                                <li><a class="header-menu-left padd-adjus" href="#">Private Message</a></li>
                                <li><a class="header-menu-left padd-adjus" href="#">Feedback to Support</a></li>
                                <li><a class="header-menu-left padd-adjus" href="#">Downloads</a></li>
                            </ul>
                        </div>
                    </div>


                </div>

            </div>
        </div>
        <div class="modal modal-red-brown" id="error-messages-modal" tabindex="-1" role="dialog" aria-hidden="true" style="padding-right: 17px; display: none;z-index: 3000;">
            <div class="modal-dialog">
                <div class="modal-content" style="margin-top: 42px;">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-xs-11" id="error-edit-class">

                            </div>
                            <img class="icon-close-classes-created" id="icon-close"  aria-hidden="true" style="top: 25%" src="<?php echo get_template_directory_uri(); ?>/library/images/close_white.png">
                        </div>
                    </div>
                </div>
            </div>
        </div>


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
                border-radius: 0;
                position: relative;
            }

            .sidenav {
                /*box-shadow: 5px 0px 0px 0px #F0F0F0;*/
                width: 171px;
                border-right: 1px solid #d6d6d6 !important;
                width: 0;
                position: absolute;
                z-index: 4000;
                margin-left: 43px;
                background-color: #fff !important;
                overflow-x: hidden;
                transition: 0.5s;
                height: calc(100% - 63px);
                padding-top: 0px !important;
                overflow: hidden;
            }

            .sidenav a {
                padding: 0px 8px 8px 1px;
                text-decoration: none;
                font-size: 25px;
                color: #818181 !important;
                display: block;
                transition: 0.3s;
                line-height: normal;
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
            .sidenav.open {
                width: 200px;
                box-shadow: 5px 0px 0px 0px rgba(0,0,0,0.1); 
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
            .cb-type2{
                text-align: left;

            }
            .cb-type2 label{
                color: #4b4b4b;
                font-size: 15px;

            }
            #checkBoxSearch .cb-type2:first-child{
                padding-left: 0px !important;
            }
            #checkBoxSearch .cb-type2:last-child{
                padding-left: 30px !important;
                padding-right: 0px !important;
            }

            .option-input-2 {
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

                /*                border: 2px solid #979797;*/
                color: #fff;
                cursor: pointer;
                display: inline-block;
                margin-right: 0.5rem;
                outline: none;
                position: relative;
                z-index: 1;

                text-decoration: none;
            }
            .option-input-2:hover {

            }
            .option-input-2:checked {

            }
            .option-input-2:checked {
                /*                height: 100%;
                                width: 100%;
                                position: absolute;*/
                content: "";
                background: url('http://ikteacher.com/wp-content/themes/ik-learn/library/images/Icon_Check-Box-ALL.png') no-repeat center;
                display: inline-block;
                background-position-x: 0px !important;
                /*                display: block;*/
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
            .option-input-2:checked::after {

                display: block;
                position: relative;
                z-index: 100;
            }
        </style>

        <div id="container" style="
             background: #ffba5a; /* Old browsers */
             background: -moz-linear-gradient(left, #ffba5a 0%, #ffba5a 50%, #04c2ce 50%, #04c2ce 100%); /* FF3.6-15 */
             background: -webkit-linear-gradient(left, #ffba5a 0%,#ffba5a 50%,#04c2ce 50%,#04c2ce 100%); /* Chrome10-25,Safari5.1-6 */
             background: linear-gradient(to right, #ffba5a 0%,#ffba5a 50%,#04c2ce 50%,#04c2ce 100%); /* W3C, IE10+, FF16+, Chrome26+, Opera12+, Safari7+ */
             filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#ffba5a', endColorstr='#04c2ce',GradientType=1 ); /* IE6-9 */">

            <header class="header header-math-new" itemscope itemtype="http://schema.org/WPHeader" >

                <div class="top-nav"></div>

                <!-- <div class="main-nav-block"></div> -->

                <div class="container" style="position: relative" class="col-md-12">		

                    <div id="logo">
                        <a href="<?php echo locale_home_url() ?>" rel="nofollow" title="<?php bloginfo('name'); ?>">
                            <img style="height: 25px; width: 100px;" src="<?php echo get_template_directory_uri(); ?>/library/images/ikTeach_Logo.png" alt="">
                        </a>
                    </div>

                    <div id="sub-logo">
                        <a href="<?php echo locale_home_url(); ?>" rel="nofollow" title="Innovative Knowledge">
                            <img style="height: 25px; width: 100px;" src="<?php echo get_template_directory_uri(); ?>/library/images/ikTeach_Logo.png" alt="">
                        </a>
                    </div>

                    <div class="dropdown" id="dropdown-account-custom">
                        <button class="dropdown-toggle text-uppercase view-my-account" id="btn-account-custom" type="button" data-toggle="dropdown" style="width: auto;">Account
                        <!-- <span class="caret"></span> --></button>
                        <!--                        <ul class="dropdown-menu" id="dropdown-menu-account-custom">
                                                    <li><a class="view-my-account">My Account</a></li>
                                                    <li><a href="<?php echo locale_home_url() ?>/?r=manage-subscription">Manage Subscription</a></li>
                                                    <li><a href="<?php echo locale_home_url() ?>/?r=private-messages">Private Message</a></li>
                                                    <li><a href="<?php echo locale_home_url() ?>/?r=private-messages&view=newpm&type=feedback">Feedback to Support</a></li>
                                                </ul>-->
                    </div>

                    <?php if (defined('IK_TEST_SERVER')) : ?>
                        <div style="position: absolute;left: 240px;top: 5px">
                            <h2 style="margin: 0px;color: #fff;font-style: italic;text-shadow: 1px 1px #000">Test Site</h2>
                        </div>
                    <?php endif ?>

                    <?php MWHtml::sel_lang_switcher() ?>
                    <ul id="user-nav">
                            <!-- <li><a href="#" class="user-name-custom">Peter Chung<span class=""></span></a></li>	 -->					
                        <?php if ($is_user_logged_in) : ?>
                            <li><a class="display-name view-my-account">[<?php echo get_user_meta($current_user->ID, 'first_name', true) . ' ' . get_user_meta($current_user->ID, 'last_name', true); ?>]</a></li>
                        <?php endif ?>
                        <li><a class="shopping-cart" href="<?php echo home_url_ssl() ?>/?r=payments" title="<?php _e('Shopping Cart', 'iii-dictionary') ?>"><span><img style="height: 18px;" src="<?php echo get_template_directory_uri(); ?>/library/images/icon_shopping.png"></span>(<?php echo count($cart_items) ?>)</a></li>
                        <!-- <li><a href="http://www.ikteach.com/<?php echo $lang; ?>" title="<?php _e('Home', 'iii-dictionary') ?>"><?php _e('Home', 'iii-dictionary') ?><span class="home-icon2"></span></a></li> -->
                        <?php if (!$is_user_logged_in) : ?>
                            <li><a title="<?php _e('Login', 'iii-dictionary') ?>" data-toggle="modal" data-target="#my-account-modal"><?php _e('Login', 'iii-dictionary') ?><span class="login-icon"></span></a></li>
                            <li class="sign-up"><a class="sign-up-link" data-toggle="modal" data-target="#my-account-modal" title="<?php _e('Create account', 'iii-dictionary') ?>"><?php _e('Create account', 'iii-dictionary') ?><span class="signup-icon"></span></a></li>

                            <li><a style="display: none;" class="home-icon2-custom" href="<?php echo locale_home_url() ?>" title="<?php _e('Home', 'iii-dictionary') ?>"><?php _e('Home', 'iii-dictionary') ?><span class="home-icon2"></span></a></li>

                        <?php else : ?>
                            <li><a class="logout-link" href="<?php echo wp_logout_url(locale_home_url()) ?>" title="<?php _e('Logout', 'iii-dictionary') ?>"><?php _e('Logout', 'iii-dictionary') ?><span class="login-icon"></span></a></li>
                        <?php endif ?>
                    </ul>

                    <!-- <div id="btn-main-menu" class="btn-menu-collapse"></div> -->
                    <div class="btn-menu-collapse" data-toggle="collapse" data-target="#navbarCollapse"></div>

                    <!-- <div id="main-nav" class="row menu_main_math_teacher">
                            
                        <nav class="navbar navbar-default" style="padding-top: 16px">
                    <?php
                    wp_nav_menu(array(// remove nav container
                        'container_class' => 'menu_main_math_teacher_home', // class of container (should you choose to use it)
                        'menu' => 'Main Menu Math', // nav name
                        'menu_class' => 'main-menu nav navbar-nav', // adding custom nav class
                        'theme_location' => 'main-nav-math-teach-home', // where it's located in the theme
                        'before' => '', // before the menu
                        'after' => '', // after the menu
                        'link_before' => '', // before each link
                        'link_after' => '', // after each link
                        'depth' => 0, // limit the depth of the nav
                        'fallback_cb' => ''                             // fallback function (if there is one)
                    ));
                    ?>
                            </nav>

                            <div id="btn-sub-menu" class="btn-menu-collapse"></div>

                            <nav class="navbar navbar-default" id="sub-user-nav">
                    <?php
                    wp_nav_menu(array(
                        'container' => false, // remove nav container
                        'container_class' => '', // class of container (should you choose to use it)
                        'menu' => 'Function Menu', // nav name
                        'menu_class' => 'user-nav nav navbar-nav math_teacher_nav', // adding custom nav class
                        'theme_location' => 'math-user-nav-teach', // where it's located in the theme
                        'before' => '', // before the menu
                        'after' => '', // after the menu
                        'link_before' => '', // before each link
                        'link_after' => '', // after each link
                        'depth' => 0, // limit the depth of the nav
                        'fallback_cb' => ''                             // fallback function (if there is one)
                    ));
                    ?>
                            </nav>

                            <nav class="navbar navbar-default visible-xs" id="lang-switcher-nav">
                    <?php
                    wp_nav_menu(array(
                        'container' => false, // remove nav container
                        'container_class' => '', // class of container (should you choose to use it)
                        'menu_class' => 'menu-lang-switcher nav navbar-nav', // adding custom nav class
                        'theme_location' => 'lang-switcher-nav', // where it's located in the theme
                        'before' => '', // before the menu
                        'after' => '', // after the menu
                        'link_before' => '', // before each link
                        'link_after' => '', // after each link
                        'depth' => 0, // limit the depth of the nav
                        'fallback_cb' => ''                             // fallback function (if there is one)
                    ));
                    ?>
                            </nav>
                    </div> -->

                </div>
                <!--============ Manage and Tutoring header=================-->

                <div id="main-nav" class="row menu_main_math_teacher">
                    <div class="navbar navbar-default">
                        <div class="dropdown manage-tutoring-dropdown col-sm-6 col-md-6 ">
                            <button style="height: 40px; border: none; font-size: 18px; padding-right: 12px;" class="dropdown-toggle manage-class-button manage-tutoring-button" type="button" data-toggle="dropdown">
                                <span style="float: right;" class="manage-tutoring-caret"><img src="<?php echo get_template_directory_uri(); ?>/library/images/00_Down_arrow.png"></span>
                                <span style="float: right;" class="manage-name-span">Manage Classes</span>
                            </button>
                            <ul class="dropdown-menu manage-class-dropdown-menu manage-tutoring-dropdown-menu">
                                <li><a href="<?php echo locale_home_url() ?>/?r=create-group&amp;layout=create"><span class="glyphicon glyphicon-arrow-right"></span>Create a Class</a></li>
                                <li><a href="<?php echo locale_home_url() ?>/?r=teachers-box"><span class="glyphicon glyphicon-arrow-right"></span>Manage a Class</a></li>
                                <li><a href="<?php echo locale_home_url() ?>/?r=homework-assignment"><span class="glyphicon glyphicon-arrow-right"></span>Assign Worksheets to a class</a></li>
                                <li><a href="<?php echo locale_home_url() ?>/?r=teaching/teach-class/sell-worksheet"><span class="glyphicon glyphicon-arrow-right"></span>Buy/Sell Worksheets</a></li>
                            </ul>
                        </div>
                        <div class="dropdown manage-tutoring-dropdown col-sm-6 col-md-6">
                            <button style="height: 40px; border: none; font-size: 18px;" class="dropdown-toggle tutoring-button manage-tutoring-button" type="button" data-toggle="dropdown"><span style="float: left; color: #004d52;" class="tutoring-name-span">Tutoring</span>
                                <span class="manage-tutoring-caret"><img src="<?php echo get_template_directory_uri(); ?>/library/images/turoring_dropdown_arrow.png"></span></button>
                            <ul class="dropdown-menu tutoring-dropdown-menu manage-tutoring-dropdown-menu">
                                <li><a href="<?php echo locale_home_url() ?>/?r=my-account#step4-collapse"><span class="glyphicon glyphicon-arrow-right"></span>Tutor Registration</a></li>
                                <li><a href="<?php echo locale_home_url() ?>/?r=teaching/teach-class"><span class="glyphicon glyphicon-arrow-right"></span>Tutor English Writing</a></li>
                                <li><a href="<?php echo locale_home_url() ?>/?r=teaching-math"><span class="glyphicon glyphicon-arrow-right"></span>Tutor Math</a></li>
                                <li><a href="<?php echo locale_home_url() ?>/?r=teaching-math/request-payment"><span class="glyphicon glyphicon-arrow-right"></span>Request Rayment</a></li>
                            </ul>
                        </div>

                    </div>
                </div>

                <nav id="navbarCollapse" class=" collapse navbar-mobile">
                    <div class="navbar navbar-default " id="navbarNavDropdown">
                        <div class="dropdown dropdown-mobile col-sm-6 col-md-6">
                            <div class="div-1">
                                <div class="div-2"> 
                                    <button class="btn dropdown-toggle mobile-menu-button" type="button" data-toggle="collapse" data-target="#dropdown-menu-manage"><div class="menu-div-mobile"><span class="menu-span-mobile">Manage Classes</span>
                                            <span class="manage-tutoring-caret"><img class="arrow-img" src="<?php echo get_template_directory_uri(); ?>/library/images/00_Down_arrow.png"></span></div></button>
                                    <ul class="collapse dropdown-menu-mobile" id="dropdown-menu-manage" style="background-color: ">
                                        <div>
                                            <li><a href="<?php echo locale_home_url() ?>/?r=create-group&layout=create">Create a Class</a></li>
                                            <li><a href="<?php echo locale_home_url() ?>/?r=teachers-box">Edit a Class</a></li>
                                            <li><a href="<?php echo locale_home_url() ?>/?r=homework-assignment">Assign Worksheets to a class</a></li>
                                            <li><a href="<?php echo locale_home_url() ?>/?r=teaching/teach-class/sell-worksheet">Buy/Sell Worksheets</a></li>
                                        </div>

                                    </ul>
                                </div>
                            </div>

                        </div>
                        <div class="dropdown dropdown-mobile col-sm-12 col-md-12">
                            <div class="div-1">
                                <div class="div-2"> 
                                    <button class="btn  dropdown-toggle mobile-menu-button" type="button" data-toggle="collapse" data-target="#dropdown-menu-tutoring"><div class="menu-div-mobile"><span class="menu-span-mobile">Turoring</span>
                                            <span class="manage-tutoring-caret"><img class="arrow-img" src="<?php echo get_template_directory_uri(); ?>/library/images/00_Down_arrow.png"></span></div></button>
                                    <ul class="collapse dropdown-menu-mobile" id="dropdown-menu-tutoring">
                                        <div>
                                            <li><a href="<?php echo locale_home_url() ?>/?r=my-account#step4-collapse">Tutor Registration</a></li>
                                            <li><a href="<?php echo locale_home_url() ?>/?r=teaching/teach-class">Tutor English Writing</a></li>
                                            <li><a href="<?php echo locale_home_url() ?>/?r=teaching-math">Tutor Math</a></li>
                                            <li><a href="<?php echo locale_home_url() ?>/?r=teaching-math/request-payment">Request Rayment</a></li>
                                        </div>

                                    </ul>
                                </div>
                            </div>
                        </div>
                        <?php
                        $langs = array(
                            'en' => 'English',
                            'ja' => 'Japaneses',
                            'ko' => 'Korean',
                            'vi' => 'Vietnamese'
                        );
                        ?>
                        <div class="dropdown dropdown-mobile language-dropdown">
                            <div class="div-1">
                                <div class="div-2"> 
                                    <button class="btn dropdown-toggle  mobile-menu-button" type="button" data-toggle="collapse" data-target="#dropdown-menu-language"><div class="menu-div-mobile"><span class="menu-span-mobile">Language</span>
                                            <span class="manage-tutoring-caret"><img class="arrow-img" src="<?php echo get_template_directory_uri(); ?>/library/images/00_Down_arrow.png"></span></div></button>
                                    <ul class="collapse dropdown-menu-mobile" id="dropdown-menu-language">
                                        <div>
                                            <!-- <li><a href="#">English</a></li>
                                            <li><a href="<?php echo locale_home_url() ?>/ja/">Japaneses</a></li>
                                            <li><a href="#">Korean</a></li>
                                            <li><a href="#">Vietnamese</a></li> -->
                                            <?php foreach ($langs as $code => $lang) : ?>
                                                <li><a href="<?php
                                                    echo is_math_panel() ? str_replace('://', '://math.', site_url()) : site_url();
                                                    echo '/' . $code . '/'
                                                    ?>"><?php echo $lang ?></a></li>
                                                <?php endforeach ?>
                                        </div>

                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="dropdown dropdown-mobile account-dropdown">
                            <div class="div-1">
                                <div class="div-2 dropdown-bottom"> 
                                    <button class="btn dropdown-toggle mobile-menu-button" type="button" data-toggle="collapse" data-target="#dropdown-menu-account"><div class="menu-div-mobile"><span class="menu-span-mobile">Account</span>
                                            <span class="manage-tutoring-caret"><img class="arrow-img" src="<?php echo get_template_directory_uri(); ?>/library/images/00_Down_arrow.png"></span></div></button>
                                    <ul class="collapse dropdown-menu-mobile" id="dropdown-menu-account">
                                        <div>
                                            <li><a class="view-my-account">My Account</a></li>
                                            <li><a href="<?php echo locale_home_url() ?>/?r=manage-subscription">Manage Subscription</a></li>
                                            <li><a href="<?php echo locale_home_url() ?>/?r=private-messages">Private Message</a></li>
                                            <li><a href="<?php echo locale_home_url() ?>/?r=private-messages&view=newpm&type=feedback">Feedback to Support</a></li>
                                        </div>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </nav>
                <!--============ End Manage and Tutoring header=================-->
                <script type="text/javascript">
                    jQuery(function ($) {
                        $(".mobile-menu-button").click(function () {
                            $('.dropdown-menu-mobile').each(function (index) {
                                if ($('.dropdown-menu-mobile').hasClass('in')) {
                                    $('.dropdown-menu-mobile').removeClass('in');
                                    $('.mobile-menu-button').find('.arrow-img').attr('src', '<?php echo get_template_directory_uri(); ?>/library/images/00_Down_arrow.png')
                                }
                            });
                        });
                        $(".mobile-menu-button").each(function (index) {
                            $(this).on("click", function () {
                                var clicks = $(this).data('clicks');
                                if (clicks) {
                                    var elements = document.getElementsByClassName('arrow-img'), i, len;
                                    elements[index].src = "<?php echo get_template_directory_uri(); ?>/library/images/00_Down_arrow.png";
                                } else {
                                    var elements = document.getElementsByClassName('arrow-img'), i, len;
                                    elements[index].src = "<?php echo get_template_directory_uri(); ?>/library/images/01_Up_arrow.png";
                                }
                                $(this).data("clicks", !clicks);
                            });
                        });
                    });
                </script>

            </header>
            <?php
//link download of apps with system of user.
            $link_url = ik_link_apps();
            $is_math_panel = is_math_panel();

            if (isset($_GET['action'])) {
                $action = $_GET['action'];
            } else {
                $action = 'login';
                $page_header_title = __('Login', 'iii-dictionary');
            }

            switch ($action) {
                case 'login':

                    $page_title_tag = __('Login', 'iii-dictionary');

                    if (isset($_POST['wp-submit'])) {
                        $creds['user_login'] = $_POST['log'];
                        $creds['user_password'] = $_POST['pwd'];
                        //$creds['remember'] = true;
                        $user = wp_signon($creds, false);

                        if (is_wp_error($user)) {
                            ik_enqueue_messages(__('Please check your Login Email address or Password and try it again.', 'iii-dictionary'), 'error');

                            if (!isset($_SESSION['login_tries'])) {
                                $_SESSION['login_tries'] = 1;
                            } else {
                                $_SESSION['login_tries'] += 1;

                                if ($_SESSION['login_tries'] >= 3) {
                                    ik_enqueue_messages(__('Did you forget your password? Please try "Forgot Password"', 'iii-dictionary'), 'message');
                                }
                            }
                        } else {
                            $user_id = wp_get_current_user();
                            if (!$user_id->language_type) {
                                update_user_meta($user_id->ID, 'language_type', 'en');
                            }
                            $_SESSION['notice-dialog'] = 1;
                            if (isset($_SESSION['mw_referer'])) {
                                $segment = explode('/', $_SESSION['mw_referer']);
                                if (isset($segment[3]) && $segment[3] == 'wp-content') {
                                    $_SESSION['mw_referer'] = locale_home_url();
                                }
                            }
                            $_SESSION['mw_referer'] = isset($_SESSION['mw_referer']) ? $_SESSION['mw_referer'] : locale_home_url();

                            wp_redirect($_SESSION['mw_referer']);
                            exit;
                        }
                    }

                    break;

                case 'forgotpassword' :

                    $page_header_title = __('Lost Password', 'iii-dictionary');
                    $page_title_tag = __('Lost Password', 'iii-dictionary');

                    if (isset($_POST['wp-submit'])) {
                        $has_err = false;
                        if (empty($_POST['user_login'])) {
                            ik_enqueue_messages(__('Please enter a username or e-mail address.', 'iii-dictionary'), 'error');
                            $has_err = true;
                        } else if (is_email($_POST['user_login'])) {
                            $user_data = get_user_by('email', trim($_POST['user_login']));
                            if (empty($user_data)) {
                                ik_enqueue_messages(__('There is no user registered with that email address.', 'iii-dictionary'), 'error');
                                $has_err = true;
                            }
                        } else {
                            $login = trim($_POST['user_login']);
                            $user_data = get_user_by('login', $login);
                        }

                        if (!$user_data) {
                            ik_enqueue_messages(__('Invalid username or e-mail.', 'iii-dictionary'), 'error');
                            $has_err = true;
                        }

                        if (!$has_err) {
                            // Redefining user_login ensures we return the right case in the email.
                            $user_login = $user_data->user_login;
                            $user_email = $user_data->user_email;

                            // Generate something random for a password reset key.
                            $key = wp_generate_password(20, false);

                            // Now insert the key, hashed, into the DB.
                            if (empty($wp_hasher)) {
                                require_once ABSPATH . WPINC . '/class-phpass.php';
                                $wp_hasher = new PasswordHash(8, true);
                            }
                            //$hashed = $wp_hasher->HashPassword( $key );
                            $hashed = time() . ':' . $wp_hasher->HashPassword($key);
                            $wpdb->update($wpdb->users, array('user_activation_key' => $hashed), array('user_login' => $user_login));

                            $message = '<p>';
                            $message .= __('Someone requested that the password be reset for the following account:', 'iii-dictionary') . " ";
                            $message .= network_home_url() . " ";
                            $message .= sprintf(__('Username: %s', 'iii-dictionary'), $user_login) . " </p><p></p><p>";
                            $message .= __('If this was a mistake, just ignore this email and nothing will happen.', 'iii-dictionary') . " </p><p></p><p>";
                            $message .= __('To reset your password, visit the following address:', 'iii-dictionary') . " </p><p></p><p>";
                            $message .= '' . network_site_url('?r=login&action=resetpass&key=' . $key . '&login=' . rawurlencode($user_login)) . " </p>";

                            $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

                            $title = sprintf(__('[%s] Password Reset', 'iii-dictionary'), $blogname);

                            $title = apply_filters('retrieve_password_title', $title);

                            $message = apply_filters('retrieve_password_message', $message, $key, $user_login, $user_data);

                            if ($message && !wp_mail($user_email, wp_specialchars_decode($title), $message)) {
                                ik_enqueue_messages(__('The e-mail could not be sent.', 'iii-dictionary') . "<br>\n" . __('Possible reason: your host may have disabled the mail() function.', 'iii-dictionary'), 'error');
                            } else {
                                ik_enqueue_messages(__('Please check your e-mail for the confirmation link.', 'iii-dictionary'), 'message');
                            }


                            exit;
                        }
                    } else {
                        if (isset($_GET['error'])) {
                            if ('invalidkey' == $_GET['error']) {
                                ik_enqueue_messages(__('Sorry, that key does not appear to be valid.', 'iii-dictionary'), 'error');
                            } else if ('expiredkey' == $_GET['error']) {
                                ik_enqueue_messages(__('Sorry, that key has expired. Please try again.', 'iii-dictionary'), 'error');
                            }
                        }
                    }

                    break;

                case 'resetpass' :

                    $page_header_title = __('Reset Password', 'iii-dictionary');
                    $page_title_tag = __('Reset Password', 'iii-dictionary');

                    if (isset($_GET['key']) && isset($_GET['login'])) {
                        $rp_login = esc_html(stripslashes($_GET['login']));
                        $rp_key = esc_html($_GET['key']);
                        $user = check_password_reset_key($rp_key, $rp_login);
                    } else if (isset($_POST['rp_key']) && isset($_POST['rp_login'])) {
                        $rp_login = esc_html(stripslashes($_POST['rp_login']));
                        $rp_key = esc_html($_POST['rp_key']);
                        $user = check_password_reset_key($rp_key, $rp_login);
                    } else {
                        $user = false;
                    }

                    if (!$user || is_wp_error($user)) {
                        if ($user && $user->get_error_code() === 'expired_key')
                            wp_redirect(site_url('?r=login&action=forgotpassword&error=expiredkey'));
                        else
                            wp_redirect(site_url('?r=login&action=forgotpassword&error=invalidkey'));
                        exit;
                    }

                    if (isset($_POST['wp-submit'])) {
                        $has_err = false;
                        if (isset($_POST['pass1']) && $_POST['pass1'] != $_POST['pass2']) {
                            ik_enqueue_messages(__('The passwords do not match.', 'iii-dictionary'), 'error');
                            $has_err = true;
                        }

                        if (!$has_err && isset($_POST['pass1']) && !empty($_POST['pass1'])) {
                            reset_password($user, $_POST['pass1']);
                            ik_enqueue_messages(__('Your password has been reset.', 'iii-dictionary'), 'success');

                            wp_redirect(locale_home_url() . '/?r=login');
                            exit;
                        }
                    }

                    break;
            }
            ?>
            <!--            <div class="modal fade modal-login" id="myModal-login" role="dialog">
                            <div class="modal-dialog modal-lg modal-login">
                                <div class="modal-content modal-content-login">
                                    <div class="title-div" style="">
                                        <img class="icon-close-classes-created" data-dismiss="modal" src="<?php echo get_template_directory_uri(); ?>/library/images/close_blue.png">
                                        <h4 class="modal-title text-uppercase">Login</h4>
                                    </div>
            
                                    <div class="modal-body-login">
            
                                        <div class="">
                                            <div class="">
                                                <div class="col-md-12">
            
            <?php
            switch ($action) :
                case 'login' :
                    ?>
                                                                                                                                                                                                                
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <form action="<?php echo locale_home_url() ?>/?r=login" name="loginform" method="post">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <div class="row">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <div class="row md-login-r">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <div class="col-xs-12 col-sm-6 col-md-6">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <div class="form-group">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <label for="username"><?php _e('Username (e-mail address)', 'iii-dictionary') ?></label>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <input type="text" class="form-control" id="username" name="log" value="">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            </div>     
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <div class="col-xs-12 col-sm-6 col-md-6">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <div class="form-group">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <label for="password"><?php _e('Password', 'iii-dictionary') ?></label>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <input type="password" class="form-control" id="password" name="pwd" value="">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            </div>     
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <div class="clearfix"></div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <div class="col-sm-6 col-md-6">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <div class="form-group" style="height: 50px; margin-top: -20px;">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <label>&nbsp;</label>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <button type="submit" class="btn-dark-blue" name="wp-submit"></span><?php _e('Login', 'iii-dictionary') ?></button>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            </div>     
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <div class="col-sm-6 col-md-6">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <div class="form-group">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <label>&nbsp;</label>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <a  data-toggle="modal" data-target="#my-account-modal" class="btn button-grey"> <span class="icon-pencil"> </span><?php _e('Sign-Up', 'iii-dictionary') ?></a>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                         <div class="col-sm-5 col-md-4 col-sm-offset-5 col-md-offset-4 text-right">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <div class="form-group">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <a href="<?php echo locale_home_url() ?>/?r=login&amp;action=forgotpassword" class="lblForgot"><?php _e('Forgot password?', 'iii-dictionary') ?> &gt;</a>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        </div> 
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <div class="clearfix"></div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <div style="padding-top: 20px;">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <div class="footer-modal-login">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <div class="pull-left" style="margin-right: 15px">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <img alt="" src="<?php echo get_template_directory_uri() ?>/library/images/desktop-shortcut.png">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <div class="pull-left right-pull-left">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <p class="instructions-text"><?php _e('Get to the site faster! Download desktop startup icon. ', 'iii-dictionary') ?></p>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <span class="downloads-block"> <span class="icon-download"> </span>  <?php _e('DOWNLOAD:', 'iii-dictionary') ?> 
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <span class="downloads-links"><a href="<?php echo $link_url['mac']; ?>"><?php _e('MAC', 'iii-dictionary') ?></a> / <a href="<?php echo $link_url['win']; ?>"><?php _e('WINDOWS', 'iii-dictionary') ?></a></span>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                </span>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <p class="instructions-text instr-text"><?php _e('( For mobile, visit Iklearn.com)') ?></p>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <div class="">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <div style="padding-top: 10px;">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <div class="form-group forgot-password-form">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <a class="forgot-password-a" data-toggle="modal" data-target="#myModal-forgot-password" class="lblForgot"><img style="height: 22px;" src="<?php echo get_template_directory_uri() ?>/library/images/forgot_icon.png"><?php _e('Forgot password?', 'iii-dictionary') ?></a>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    </div>
                                                                                                                                                                                                                
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                </div>  
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <input name="redirect_to" value="<?php echo locale_home_url() ?>" type="hidden">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            </form>
                                                                                                                                                                                                                
                    <?php
                    break;
                case 'forgotpassword' :
                    ?>
                                                                                                                                                                                                                
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <div class="row">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <div class="col-md-12">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <div class="form-group has-error">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <label class="control-label"><?php _e('Please enter your username or email address. You will receive a link to create a new password via email.', 'iii-dictionary') ?></label>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <form name="lostpasswordform" id="lostpasswordform" action="<?php echo esc_url(network_site_url('?r=login&action=forgotpassword')); ?>" method="post">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <div class="row">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <div class="col-xs-12 col-sm-6 col-md-6">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <div class="form-group">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <label for="user_login" ><?php _e('Username or E-mail', 'iii-dictionary') ?></label>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <input type="text" name="user_login" id="user_login" class="form-control" value="">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <div class="row">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <div class="col-xs-12 col-sm-6 col-md-6">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <div class="form-group">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <button type="submit" name="wp-submit" id="wp-submit" class="btn btn-default btn-block sky-blue">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <span class="icon-switch"></span><?php esc_attr_e('Get New Password', 'iii-dictionary') ?>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            </button>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            </form>
                                                                                                                                                                                                                
                    <?php
                    break;
                case 'resetpass' :
                    ?>
                                                                                                                                                                                                                
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <div class="row">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <div class="col-md-12">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <div class="form-group has-error">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <label class="control-label"><?php _e('Enter your new password below.', 'iii-dictionary') ?></label>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <form name="resetpassform" id="resetpassform" action="<?php echo esc_url(network_site_url('?r=login&action="resetpass')); ?>" method="post">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <div class="row">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <div class="col-md-6">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <div class="form-group">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <label for="pass1"><?php _e('New password', 'iii-dictionary') ?></label>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <input type="password" name="pass1" id="pass1" class="form-control" size="20" value="">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <div class="row">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <div class="col-md-6">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <div class="form-group">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <label for="pass2"><?php _e('Confirm new password', 'iii-dictionary') ?></label>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <input type="password" name="pass2" id="pass2" class="form-control" size="20" value="">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <div class="row">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <div class="col-md-6">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <div class="form-group">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <button type="submit" name="wp-submit" id="wp-submit" class="btn btn-default btn-block sky-blue">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <span class="icon-switch"></span><?php esc_attr_e('Reset Password', 'iii-dictionary') ?>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            </button>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <input type="hidden" name="rp_key" value="<?php echo $rp_key ?>">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <input type="hidden" name="rp_login" value="<?php echo $rp_login ?>">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            </form>
                                                                                                                                                                                                                
                    <?php
                    break;
            endswitch
            ?>
            
                                                </div>
                                            </div>
                                        </div>
            
                                    </div>
                                     <div class="modal-footer">
                                      <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                    </div> 
                                </div>
                            </div>
                        </div>-->
            <style type="text/css">
                .selectboxit-default-arrow{
                    border-top-color: #0B90BB;
                }

                .modal-forgot-password,
                .modal-signup,
                .modal-login{
                    margin: 0;
                    padding: 0;
                    width: 100% !important;
                    height: 100% !important; 
                }
                .modal-content-forgot-password,
                .modal-content-signup,
                .modal-content-login{
                    height: auto;
                    min-height: 100%;
                    color: #000 !important;
                } 
                #content{
                    background: #fff;
                }
                .btn-dark-blue{
                    background: #004258;
                    color: #fff;
                    border: none;
                    height: 38px;
                    left: 0;
                    right: 0;
                    color: #fff;
                    border: none;
                    height: 38px;
                    box-shadow: none ! important;
                    padding: 6px 0 !important;
                    text-decoration: none;
                    font-size: 15px;
                    width: 100%;
                    text-align: center;
                }
                .button-grey{
                    left: 0;
                    right: 0;
                    background: grey;
                    color: #fff;
                    border: none;
                    height: 38px;
                    box-shadow: none ! important;
                    padding: 6px 0 !important;
                    text-decoration: none;
                    font-size: 15px;
                    position: absolute;
                    margin: 10px 8px;
                    text-align: center;
                    cursor: pointer;

                }
                .btn-dark-blue:hover,
                .btn-dark-blue:focus,
                .btn-dark-blue:visited,
                .btn-dark-blue:active,
                .button-grey:hover,
                .button-grey:focus,
                .button-grey:visited,
                .button-grey:active{
                    color: #fff;
                    text-decoration: none;
                }
                .forgot-password-a{
                    color: #9c9c9c;

                    white-space: nowrap;
                    cursor: pointer;
                    font-weight: 500;
                }
                .forgot-password-a:hover,
                .forgot-password-a:active,
                .forgot-password-a:visited,
                .forgot-password-a:focus{
                    color: #9c9c9c;

                }
                .forgot-password-a img{
                    padding-right: 5px;
                }
                /* Đã có ở page create group */
                .page-title-responsive{
                    width: 910px; margin: auto;
                }
                .content-page-responsive{
                    width: 900px; margin: auto;
                }


                @media(max-width: 1200px){
                    .content-page-responsive{
                        width: 820px; margin: auto;
                    }
                    .page-title-responsive{
                        width: 825px; margin: auto;
                    }
                    .modal-body-login,
                    .modal-body-signup,
                    .modal-body-forgot-password{
                        padding:10px 80px;
                    }
                }
                @media(max-width: 992px){
                    .page-title-responsive{
                        width: 750px; margin: auto;
                    }
                    .content-page-responsive{
                        width: 750px; margin: auto;
                    }
                    .modal-body-login,
                    .modal-body-signup,
                    .modal-body-forgot-password{
                        padding:25px 40px;
                    }
                    .title-div{
                        padding:10px 40px;
                    }

                }
                @media(min-width: 992px){
                    .modal-body-login,
                    .modal-body-signup,
                    .modal-body-forgot-password{
                        padding:25px 160px;
                    }
                    .title-div{
                        padding:10px 160px;
                    }
                }
                @media(max-width: 767px){

                    .page-title-responsive{
                        width: 90%; margin: auto;
                    } 
                    .content-page-responsive{
                        width: 90%; margin: auto;
                    }
                    .right-pull-left{
                        width: 65%;
                    }

                    .row.tiny-gutter div{
                        padding-bottom: 25px;
                    }
                    #myModal-login .btn-dark-blue{
                        margin: -6px 0 0 0;
                    }
                    #myModal-login .button-grey{
                        margin:41px 8px;
                    }
                    #myModal-signup .btn-dark-blue{
                        margin: -6px 0 0 0;
                    }
                    #myModal-signup .button-grey{
                        margin: 11px 8px;
                    }
                    #myModal-forgot-password .button-grey{
                        margin: 58px 8px;
                    }
                    #myModal-forgot-password .button-grey{
                        margin: 10px 8px;
                    }

                }
                #myModal-forgot-password .btn-dark-blue{
                    margin: 10px 0 0 0;
                }	/* Đã có ở page create group */
                .forgot-password-form{
                    text-align: right;
                }
                .instr-text{
                    display: inline;
                    font-style: italic;
                }
                .instructions-text{
                    margin-bottom: 6px !important;
                    color: #000000;
                    font-size: 16px;
                }
                .downloads-links a{
                    color: #000;
                    font-weight: 700;
                    text-decoration: none;
                }
                .downloads-links a:hover
                .downloads-links a:focus,
                .downloads-links a:visited,
                .downloads-links a:active{
                    text-decoration: none;
                }
                @media(max-width: 420px){

                    .right-pull-left{
                        width: 70%;
                    }


                }
                @media(min-width: 640px){
                    .md-login-r{
                        padding-bottom: 55px;
                    }
                    .footer-modal-login{
                        width: 80%;
                    }
                }
                @media(max-width: 640px){
                    .forgot-password-a{
                        position: absolute;
                        top: 270px;
                        left: 10px;
                        font-size: 13px;
                    }
                    .footer-modal-login{
                        width: 100%;
                    }
                    .md-login-r{
                        padding-bottom: 90px;
                    }
                    .pull-left p{
                        font-size: 13px;
                    }
                    .tooltip-div:before{
                        left: 23%;
                    }
                    .tooltip-div{
                        left: -4px;
                        right: 17px;
                    }
                }

                .title-div{
                    /* padding: 10px 40px; */
                    border-bottom: 1px solid #BBBBBB;
                }
                #myModal-login .modal-title{
                    font-size: 24px;
                    color: #0C97D2;
                }
                .icon-close-classes-created {
                    float: right;
                    cursor: pointer;
                }
                #myModal-login .modal-title,
                #myModal-forgot-password .modal-title,
                #myModal-signup .modal-title{
                    font-size: 24px;
                    color: #0C97D2;
                }
                .modal-content-forgot-password,
                .modal-content-signup{
                    color: #000 !important;
                }

                #lostpasswordform img{
                    position: absolute;
                    left:150px;
                    top: -29px;
                } 


                .tooltip-col {
                    position: relative;
                    display: inline-block;
                    cursor:pointer;
                    text-align:center;
                }

                .tooltiptext {
                    visibility: hidden;
                    /* width: 150px;
                        height: 50px;
                    */    background-color: black;
                    color: #fff;
                    text-align: center;
                    padding: 5px 10px;
                    box-sizing: border-box;
                    position: absolute;
                    z-index: 1;
                    top: 9px;
                    left:0;
                    color: #000000;
                    background:#FFCD86; 
                    padding:30px !important;
                }

                .tooltiptext::after {
                    content: "";
                    position: absolute;
                    top: -20px;
                    left: 150px;   
                    border-width: 10px;
                    border-top: 10px solid transparent;
                    border-right: 8px solid transparent;
                    border-bottom: 10px solid #FFCD86;
                    border-left: 8px solid transparent;
                }
                .tooltip-col:hover .tooltiptext {
                    visibility: visible;
                }


            </style>

            <div class="modal fade modal-signup" id="myModal-signup" role="dialog">
                <div class="modal-dialog modal-lg modal-signup">
                    <div class="modal-content modal-content-signup">
                        <!-- <div class="modal-header">
                          <button type="button" class="close" data-dismiss="modal">&times;</button>
                          <h4 class="modal-title">Sign up</h4>
                          <img class="icon-close-classes-created" src="<?php echo get_template_directory_uri(); ?>/library/images/close_blue.png">
                        </div> -->
                        <div class="title-div" style="">
                            <img class="icon-close-classes-created" data-dismiss="modal" src="<?php echo get_template_directory_uri(); ?>/library/images/close_blue.png">
                            <h4 class="modal-title text-uppercase">Sign-up</h4>
                        </div>
                        <div class="modal-body-signup">
                            <div class="">
                                <div class="">
                                    <form method="post" id="mySignup" action="<?php echo locale_home_url() ?>/?r=signup" name="registerform" enctype="multipart/form-data">
                                        <div class="row">
                                            <div class="col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <label for="user_login"><?php _e('Username (e-mail address)', 'iii-dictionary') ?></label>
                                                    <input id="user_login" class="form-control" name="user_login" type="text" value="" required>
                                                </div>
                                            </div>
                                            <!-- <div class="col-sm-3">
                                                    <div class="form-group">
                                                            <label>&nbsp;</label>															
                                                            <a href="#" id="check-availability" class="check-availability"><?php _e('Find out availability', 'iii-dictionary') ?>
                                                                    <span class="icon-loading"></span>
                                                                    <span class="icon-availability" data-toggle="popover" data-placement="bottom" data-container="body" data-trigger="hover" data-html="true" data-max-width="420px" data-content="If username availability is “not available”, someone has already signed up with the email address you entered.<br>If username is “available”, please continue on with the sign up page."></span>
                                                            </a>
                                                    </div>
                                            </div> -->

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
                                                    <button class="btn-dark-blue" type="submit" name="wp-submit"></span><?php _e('Create Account', 'iii-dictionary') ?></button>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <label>&nbsp;</label>
                                                    <button class="button-grey cancel-btn" style="background: #cecece; margin-top: 25px !important;" type="submit" name="cancel">
                                                        <?php _e('Cancel', 'iii-dictionary') ?>
                                                    </button>

                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>


                        </div>

                    </div>
                </div>
            </div>

            <?php
            $is_math_panel = is_math_panel();
            $_page_title = __('Sign-up', 'iii-dictionary');
            ?>

            <script type="text/javascript">
                function show_detail(id) {

                    var element = document.getElementById(id);
                    var check = element.classList.contains("hidden");
                    if (check == true) {

                        element.classList.remove("hidden");
                    } else {
                        element.classList.add("hidden");
                    }
                }
                ;
                function edit_desc(id) {
                    document.getElementById("detaildesc" + id).classList.add("hidden");
                    document.getElementById("editdesc" + id).classList.add("hidden");
                    document.getElementById("detailinput" + id).classList.remove("hidden");
                    document.getElementById("editsuccess" + id).classList.remove("hidden");
                }
                ;
            </script>
            <script type="text/javascript">
                (function ($) {
                    $(function () {

                        function show_detail_worksheet(id, class_open) {
                            var check = $(class_open).hasClass("hidden");
                            if (check == false) {
                                $(class_open).addClass(" hidden");
                                $(id + " .open-arrow img").attr("src", "<?php echo get_template_directory_uri(); ?>/library/images/icon_Arrow_Open.png"); //open-arrow là class của ảnh mũi  tên khi click chuột
                            } else {
                                $(id + " .open-arrow").click(function () {
                                    if ($(class_open).hasClass("hidden")) {
                                        $(".detail-tr-ready").not(id + " .detail-tr-ready").addClass(" hidden");
                                        $(".open-arrow img").not(id + " .open-arrow img").attr("src", "<?php echo get_template_directory_uri(); ?>/library/images/icon_Arrow_Open.png");
                                        $(class_open).removeClass("hidden");
                                        $(id + " .open-arrow img").attr("src", "<?php echo get_template_directory_uri(); ?>/library/images/icon_Arrow_Close.png");
                                    } else {
                                        $(".detail-tr-ready").not(id + " .detail-tr-ready").addClass(" hidden");
                                        $(".open-arrow img").not(id + " .open-arrow img").attr("src", "<?php echo get_template_directory_uri(); ?>/library/images/icon_Arrow_Open.png");
                                        $(class_open).addClass(" hidden");
                                        $(id + " .open-arrow img").attr("src", "<?php echo get_template_directory_uri(); ?>/library/images/icon_Arrow_Open.png");
                                    }
                                });
                            }
                        }
                        ;
                        function show_detail_worksheet_lesson(id, class_open) {

                            var check = $(class_open).hasClass("hidden");
                            if (check == false) {
                                $(class_open).addClass(" hidden");
                                $(id + " .open-arrow img").attr("src", "<?php echo get_template_directory_uri(); ?>/library/images/icon_Arrow_Open.png");
                            } else {
                                $(id + " .open-arrow").click(function () {
                                    if ($(class_open).hasClass("hidden")) {
                                        $(".detail-tr-lesson").not(id + " .detail-tr-lesson").addClass(" hidden");
                                        $(".open-arrow img").not(id + " .open-arrow img").attr("src", "<?php echo get_template_directory_uri(); ?>/library/images/icon_Arrow_Open.png");
                                        $(class_open).removeClass("hidden");
                                        $(id + " .open-arrow img").attr("src", "<?php echo get_template_directory_uri(); ?>/library/images/icon_Arrow_Close.png");
                                    } else {
                                        $(".detail-tr-lesson").not(id + " .detail-tr-lesson").addClass(" hidden");
                                        $(".open-arrow img").not(id + " .open-arrow img").attr("src", "<?php echo get_template_directory_uri(); ?>/library/images/icon_Arrow_Open.png");
                                        $(class_open).addClass(" hidden");
                                        $(id + " .open-arrow img").attr("src", "<?php echo get_template_directory_uri(); ?>/library/images/icon_Arrow_Open.png");
                                    }
                                });
                            }
                        }
                        ;
                        $(document).click(function (e)
                        {
                            var option_less2 = $("#icon-option-less2");
                            var option_detail_less2 = $("#option-detail-less2");
                            var option_subject = $("#icon-option-subject");
                            var option_detail_subject = $("#option-detail-subject");
                            var option_lesson = $("#icon-option-lesson");
                            var option_detail_lesson = $("#option-detail-lesson");
                            var option_library = $("#icon-option-library");
                            var option_detail_library = $("#option-detail-library");
                            var option_eng = $("#icon-option-eng");
                            var option_detail_eng = $("#option-detail-eng");
                            var option_math = $("#icon-option-math");
                            var option_detail_math = $("#option-detail-math");
                            var option_ready = $("#icon-option-ready");
                            var option_detail_ready = $("#option-detail-ready");
                            if (!option_less2.is(e.target) && option_less2.has(e.target).length === 0 && !option_detail_less2.is(e.target) && option_detail_less2.has(e.target).length === 0)
                            {
                                $("#option-detail-less2").addClass(" hidden");
                            }
                            if (!option_subject.is(e.target) && option_subject.has(e.target).length === 0 && !option_detail_subject.is(e.target) && option_detail_subject.has(e.target).length === 0)
                            {
                                $("#option-detail-subject").addClass(" hidden");
                            }
                            if (!option_lesson.is(e.target) && option_lesson.has(e.target).length === 0 && !option_detail_lesson.is(e.target) && option_detail_lesson.has(e.target).length === 0)
                            {
                                $("#option-detail-lesson").addClass(" hidden");
                            }
                            if (!option_library.is(e.target) && option_library.has(e.target).length === 0 && !option_detail_library.is(e.target) && option_detail_library.has(e.target).length === 0)
                            {

                                $("#option-detail-library").addClass(" hidden");
                            }
                            if (!option_eng.is(e.target) && option_eng.has(e.target).length === 0 && !option_detail_eng.is(e.target) && option_detail_eng.has(e.target).length === 0)
                            {
                                $("#option-detail-eng").addClass(" hidden");
                            }
                            if (!option_math.is(e.target) && option_math.has(e.target).length === 0 && !option_detail_math.is(e.target) && option_detail_math.has(e.target).length === 0)
                            {

                                $("#option-detail-math").addClass(" hidden");
                            }
                            if (!option_ready.is(e.target) && option_ready.has(e.target).length === 0 && !option_detail_ready.is(e.target) && option_detail_ready.has(e.target).length === 0)
                            {
                                $("#option-detail-ready").addClass(" hidden");
                            }
                        });
                        function isValidEmail(emailText) {
                            var pattern = new RegExp(/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i);
                            return pattern.test(emailText);
                        }
                        ;
                        var availability_checking = false;
                        var redirect = '<?php echo $redirct ?>';
                        $("#check-availability").click(function (e) {
                            e.preventDefault();
                            if (availability_checking) {
                                return;
                            }
                            var tthis = $(this);
                            var user_login = $("#user_login").val().trim();
                            if (user_login != "") {
                                tthis.popover("destroy");
                                availability_checking = true;
                                tthis.find(".icon-loading").fadeIn();
                                $.getJSON(home_url + "/?r=ajax/availability/user", {user_login: user_login}, function (data) {
                                    if (isValidEmail(user_login)) {

                                        if (data [0] == 0) {
                                            var p_c = '<span class="popover-alert"><?php _e('Not Available', 'iii-dictionary') ?></span>';
                                        } else {
                                            var p_c = '<span class="popover-alert"><?php _e('Available', 'iii-dictionary') ?></span>';
                                        }
                                    } else {
                                        var p_c = '<span class="popover-alert"><?php _e('Invalid', 'iii-dictionary') ?></span>';
                                    }
                                    tthis.find(".icon-loading").fadeOut();
                                    tthis.popover({
                                        placement: "bottom",
                                        content: p_c,
                                        trigger: "click",
                                        html: true
                                    }).popover("show");
                                    setTimeout(function () {
                                        tthis.popover("hide")
                                    }, 3000);
                                    availability_checking = false;
                                });
                            }
                        });
                        $('.r_agree_english').click(function (e) {
                            $('#r_agree_english').find('input').attr('checked', true);
                            $('#r_agree_english label').addClass('checked_lb');
                        });
                        $('.r_agree_math').click(function (e) {
                            $('#r_agree_math').find('input').attr('checked', true);
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
                        $('.check_lb').click(function (e) {
                            var checked = $(this).find('input').attr('checked');
                            if (checked == 'checked') {
                                $(this).addClass('checked_lb');
                            } else {
                                $(this).removeClass('checked_lb');
                            }
                        });
                        $('.logout-link').click(function (e) {
                            localStorage.clear();
                        });
                        $(".view-my-account").click(function () {
                            $("#my-account-modal").modal('show');
                            var name = $(".display-name").text();
                            if (name !== '') {
                                $("#sub-createacc").removeClass("active");
                                $("#sub-profile").addClass("active");
                                $("#create-account").removeClass("active");
                                $("#create-account").removeClass("in");
                                $("#login-user").removeClass("active");
                                $("#profile").addClass("active");
                                $("#profile").addClass("in");
                            }

                        });
                        $(".sign-up").click(function () {
                            $("#login-user").removeClass("active");
                            $("#create-account").addClass(" active");
                        });
                        $('#my-account-modal').on('hidden.bs.modal', function (e)
                        {

                            $("#my-account-modal input:not(:checkbox, .input-current)").val("");
                            $(".option-input-2").
                                    attr("checked", false);
                            location.reload();
                        });
                        $("#account").click(function () {
                            var name = $(".display-name").text();
                            if (name !== '') {
                                if ($("#mySidenav").hasClass("open")) {
                                    closeNav();
                                } else {
                                    $("#sub-myacc").css("display", "block");
                                    $("#sub-myacc").addClass("opensub");
                                    $("#sub-subscription").css("display", "none");
                                    $("#sub-subscription").removeClass("opensub");
                                    $("#sub-lesson-manager").css("display", "none");
                                    $("#sub-lesson-manager").removeClass("opensub");
                                    $('#menu-left-myaccount li:nth-child(3)').css("margin-top", "0px");
                                    openNav();
                                    $('#menu-left-myaccount li:nth-child(2)').css("margin-top", "185px");
                                    $('#menu-left-myaccount li:nth-child(6)').css("margin-top", "3px");
                                }
                            }

                        });

                        $("#lesson").click(function () {
                            var name = $(".display-name").text();
                            if (name !== '') {
                                if ($("#mySidenav").hasClass("open")) {
                                    closeNav();
                                } else {
                                    $("#sub-lesson-manager").css("display", "block");
                                    $("#sub-lesson-manager").addClass("opensub");
                                    $("#sub-myacc").css("display", "none");
                                    $("#sub-myacc").removeClass("opensub");
                                    $("#sub-subscription").css("display", "none");
                                    $("#sub-subscription").removeClass("opensub");
                                    $('#menu-left-myaccount li:nth-child(3)').css("margin-top", "0px");
                                    openNav();
                                    $('#menu-left-myaccount li:nth-child(4)').css("margin-top", "149px");
                                    $('#menu-left-myaccount li:nth-child(6)').css("margin-top", "7px");
                                }
                            }

                        });
                        $("#menu_Taggle").click(function () {

                            var name = $(".display-name").text();
                            if (name !== '') {
                                if ($("#mySidenav").hasClass("open")) {
                                    closeNav();
                                } else {

                                    openNav();
                                    if ($("#sub-subscription").hasClass("opensub")) {
                                        $('#menu-left-myaccount li:nth-child(3)').css("margin-top", "89px");
                                        $('#menu-left-myaccount li:nth-child(6)').css("margin-top", "7px");
                                    } else if ($("#sub-lesson-manager").hasClass("opensub")) {
                                        $('#menu-left-myaccount li:nth-child(5)').css("margin-top", "149px");
                                        $('#menu-left-myaccount li:nth-child(6)').css("margin-top", "7px");
                                    } else {
                                        $('#menu-left-myaccount li:nth-child(2)').css("margin-top", "185px");
                                        $('#menu-left-myaccount li:nth-child(6)').css("margin-top", "3px");
                                    }

                                }

                            }

                        });
                        $("#mySidenav a").click(function () {
                            closeNav();
                            $('#menu-left-myaccount li:nth-child(2)').css("margin-top", "0px");
                        });
                        function openNav() {
                            $("#mySidenav").removeClass("open");
                            $("#mySidenav").removeClass("close");
                            $("#mySidenav").addClass("open");
                        }

                        function closeNav() {
                            $("#mySidenav").removeClass("open");
                            $("#mySidenav").removeClass("close");
                            $("#mySidenav").addClass("close");
                            $('#menu-left-myaccount li').css("margin-top", "0px");
                        }

                        $("#sub-subscription li").click(function () {
                            $("#sub-myacc li").removeClass("active");
                            $("#sub-lesson-manager li").removeClass("active");
                        });
                        $("#sub-lesson-manager li").click(function () {
                            $("#sub-myacc li").removeClass("active");
                            $("#sub-subscription li").removeClass("active");
                        });
                        $("#sub-myacc li").click(function () {
                            $("#sub-subscription li").removeClass("active");
                            $("#sub-lesson-manager li").removeClass("active");
                        });

                        $("#myacc").click(function () {
                            $("#sub-myacc").css("display", "block");
                            $("#sub-myacc").addClass("opensub");
                            $("#sub-subscription").css("display", "none");
                            $("#sub-subscription").removeClass("opensub");
                            $("#sub-lesson-manager").css("display", "none");
                            $("#sub-lesson-manager").removeClass("opensub");
                            if ($("#mySidenav").hasClass("open")) {
                                closeNav();
                                $('#menu-left-myaccount li:nth-child(2)').css("margin-top", "0px");
                            } else {
                                openNav();
                                $('#menu-left-myaccount li:nth-child(2)').css("margin-top", "185px");
                                $('#menu-left-myaccount li:nth-child(6)').css("margin-top", "3px");
                            }
                        });
                        $("#lesson-manager").click(function () {
                            $("#sub-lesson-manager").css("display", "block");
                            $("#sub-lesson-manager").addClass("opensub");
                            $("#sub-myacc").css("display", "none");
                            $("#sub-myacc").removeClass("opensub");
                            $("#sub-subscription").css("display", "none");
                            $("#sub-subscription").removeClass("opensub");
                            if ($("#mySidenav").hasClass("open")) {
                                closeNav();
                                $('#menu-left-myaccount li:nth-child(3)').css("margin-top", "0px");
                            } else {
                                openNav();
                                $('#menu-left-myaccount li:nth-child(4)').css("margin-top", "149px");
                                $('#menu-left-myaccount li:nth-child(6)').css("margin-top", "7px");
                            }

                        });
                        $('#create-acc').click(function () {
                            var user_name = $('#user_login').val();
                            var user_password = $('#user_password').val();
                            var confirm_password = $('#confirm_password').val();
                            var first_name = $('#first_name').val();
                            var last_name = $('#last_name').val();
                            var birth_m = $('#birth_m').val();
                            var birth_d = $('#birth_d').val();
                            var birth_y = $('#birth_y').val();
                            var profile_avatar = $('#profile-avatar').val();
                            var cb_lang = [];
                            $('input[name="cb-lang"]:checked').each(function () {
                                cb_lang.push(this.value);
                            });
                            $.post(home_url + "/?r=ajax/create_account", {
                                user_name: user_name,
                                user_password: user_password,
                                confirm_password: confirm_password,
                                first_name: first_name,
                                last_name: last_name,
                                birth_m: birth_m,
                                birth_d: birth_d,
                                birth_y: birth_y,
                                cb_lang: cb_lang,
                                profile_avatar: profile_avatar
                            }, function (data) {
                                if ($.trim(data) == '1') {
                                    if (redirect == '')
                                        window.location.reload();
                                    else
                                        document.location.href = redirect;
                                } else {
                                    $('#error-edit-class').html(data);
                                    $('#error-messages-modal').css("display", "block");
                                }

                            });
                        });
                        $('#update-teacher').click(function () {
                            var user_email = $('#new_email').val();
                            var new_password = $('#new_password').val();
                            var retype_new_password = $('#retype_new_password').val();
                            var current_password = $('#current_password').val();
                            var mobile_number = $('#new_mobile').val();
                            var last_school = $('#new_last_attended').val();
                            var previous_school = $('#new_last_tought').val();
                            var skype_id = $('#new_skype_id').val();
                            var user_profession = $('#new_profession').val();
                            var cb_lang = [];
                            var profile_avatar = $('#profile-value').val();
                            $('input[name="cb-lang-up"]:checked').each(function () {
                                cb_lang.push(this.value);
                            });
                            $.post(home_url + "/?r=ajax/update_info", {
                                user_email: user_email,
                                new_password: new_password,
                                retype_new_password: retype_new_password,
                                current_password: current_password,
                                mobile_number: mobile_number,
                                last_school: last_school,
                                previous_school: previous_school,
                                skype_id: skype_id,
                                user_profession: user_profession,
                                cb_lang: cb_lang,
                                profile_avatar: profile_avatar
                            }, function (data) {
                                if ($.trim(data) == '1') {
                                    var dt = '<span style="font-weight:bold;">Account Info Updated!</span><br/>Your account has been updated successfully.';
                                    $('#error-edit-class').html(dt);
                                    $('#error-messages-modal').css("display", "block");
                                } else {
                                    $('#error-edit-class').html(data);
                                    $('#error-messages-modal').css("display", "block");
                                }
                            });
                        });
                        $("#input-avatar").change(function () {
                            var file_data = $('#input-avatar').prop('files')[0];
                            var type = file_data.type;
                            var match = ["image/gif", "image/png", "image/jpg", ];
                            var form_data = new FormData();
                            form_data.append('file', file_data);
                            $.ajax({
                                url: home_url + "/?r=ajax/upload_avatar",
                                dataType: 'text',
                                cache: false,
                                contentType: false,
                                processData: false,
                                data: form_data,
                                type: 'post',
                                success: function (res) {
                                    if ($.trim(res) != '0') {
                                        $("#profile-avatar").val($.trim(res));
                                    } else {
                                        $('#error-edit-class').html('Error: There was an error uploading your file');
                                        $('#error-messages-modal').css("display", "block");
                                    }
                                }
                            });
                        });
                        $("#input-image").change(function () {
                            var file_data = $('#input-image').prop('files')[0];
                            var type = file_data.type;
                            var form_data = new FormData();
                            form_data.append('file', file_data);
                            $.ajax({
                                url: home_url + "/?r=ajax/upload_avatar",
                                dataType: 'text',
                                cache: false,
                                contentType: false,
                                processData: false,
                                data: form_data,
                                type: 'post',
                                success: function (res) {
                                    if ($.trim(res) != '0') {
                                        $("#profile-value").val($.trim(res));
                                    } else {
                                        $('#error-edit-class').html('Error: There was an error uploading your file');
                                        $('#error-messages-modal').css("display", "block");
                                    }
                                }
                            });
                        });
                        $("#info").keypress(function () {
                            var stt = $("#rdo-agreed").is(":checked");
                            var data = 'Please read and check the box of Terms and Conditions';
                            if (stt == false) {
                                $('#error-edit-class').html(data);
                                $('#error-messages-modal').css("display", "block");
                                $("#info").find('input').attr('readonly', true);
                            } else {
                                $("#info").find('input').attr('readonly', false);
                            }
                        });
                        $("#info2").keypress(function () {
                            var stt = $("#rdo-agreed2").is(":checked");
                            var data = 'Please read and check the box of Terms and Conditions';
                            if (stt == false) {
                                $('#error-edit-class').html(data);
                                $('#error-messages-modal').css("display", "block");
                                $("#info2").find('input').attr('readonly', true);
                            } else {
                                $("#info2").find('input').attr('readonly', false);
                            }
                        });
                        $(".tutor-regis").click(function () {
                            var stt = $("#rdo-agreed").is(":checked");
                            var data = 'Please read and check the box of Terms and Conditions';
                            if (stt == false) {
                                $('#error-edit-class').html(data);
                                $('#error-messages-modal').css("display", "block");
                                $(".extend-tutor").css("display", "none");
                                $(".tutor-regis").attr("checked", false);
                            } else {
                                var tutor = $("#eng-writing").is(":checked");
                                var tutor2 = $("#eng-conver").is(":checked");
                                var tutor3 = $("#math-middle").is(":checked");
                                var tutor4 = $("#math-any").is(":checked");
                                if (tutor == true) {
                                    $("#extend-tutor").css("display", "block");
                                } else {
                                    $("#extend-tutor").css("display", "none");
                                }
                                if (tutor2 == true) {
                                    $("#extend-tutor2").css("display", "block");
                                } else {
                                    $("#extend-tutor2").css("display", "none");
                                }
                                if (tutor3 == true) {
                                    $("#extend-tutor3").css("display", "block");
                                } else {
                                    $("#extend-tutor3").css("display", "none");
                                }

                                if (tutor4 == true) {
                                    $("#extend-tutor4").css("display", "block");
                                } else {
                                    $("#extend-tutor4").css("display", "none");
                                }
                            }
                        });
                        $("#rdo-agreed").click(function () {
                            var stt = $("#rdo-agreed").is(":checked");
                            var data = 'Please read and check the box of Terms and Conditions';
                            if (stt == false) {
                                $('#error-edit-class').html(data);
                                $('#error-messages-modal').css("display", "block");
                                $(".extend-tutor").css("display", "none");
                                $(".tutor-regis").attr("checked", false);
                                $("#info").find('input').attr('readonly', true);
                            }
                        });
                        $(".tutor-regis2").click(function () {
                            var stt = $("#rdo-agreed2").is(":checked");
                            var data = 'Please read and check the box of Terms and Conditions';
                            if (stt == false) {
                                $('#error-edit-class').html(data);
                                $('#error-messages-modal').css("display", "block");
                                $(".extend-tutor").css("display", "none");
                                $(".tutor-regis2").attr("checked", false);
                            } else {
                                var tutor = $("#eng-writing-tab").is(":checked");
                                var tutor2 = $("#eng-conver-tab").is(":checked");
                                var tutor3 = $("#math-middle-tab").is(":checked");
                                var tutor4 = $("#math-any-tab").is(":checked");
                                if (tutor == true) {
                                    $("#extend-tutor-tab1").css("display", "block");
                                } else {
                                    $("#extend-tutor-tab1").css("display", "none");
                                }
                                if (tutor2 == true) {
                                    $("#extend-tutor-tab2").css("display", "block");
                                } else {
                                    $("#extend-tutor-tab2").css("display", "none");
                                }
                                if (tutor3 == true) {
                                    $("#extend-tutor-tab3").css("display", "block");
                                } else {
                                    $("#extend-tutor-tab3").css("display", "none");
                                }
                                if (tutor4 == true) {
                                    $("#extend-tutor-tab4").css("display", "block");
                                } else {
                                    $("#extend-tutor-tab4").css("display", "none");
                                }
                            }
                        });
                        $("#rdo-agreed2").click(function () {
                            var stt = $("#rdo-agreed2").is(":checked");
                            var data = 'Please read and check the box of Terms and Conditions';
                            if (stt == false) {
                                $('#error-edit-class').html(data);
                                $('#error-messages-modal').css("display", "block");
                                $(".extend-tutor").css("display", "none");
                                $(".tutor-regis").attr("checked", false);
                                $("#info").find('input').attr('readonly', true);
                            }
                        });
                        $("#icon-close").click(function () {
                            $("#error-messages-modal").css("display", "none");
                        });
                        $("#val-credit-code").click(function () {
                            var credit_code = $("#credit-code").val();
                            var dictionary_id = $("#dictionary-id").val();
                            var starting_date_txt = $("#starting-date-txt").val();
                            var assoc_group = $("#assoc-group").val();
                            var group_name = $("#group-name").val();
                            var group_pass = $("#group-pass").val();
                            var activation_code = $("#activation-code").val();
                            var data = {
                                credit_code: credit_code,
                                dictionary_id: dictionary_id,
                                starting_date_txt: starting_date_txt,
                                assoc_group: assoc_group,
                                group_name: group_name,
                                group_pass: group_pass,
                                activation_code: activation_code
                            };
                            $.post(home_url + "/?r=ajax/credit_code", {
                                data: data
                            }, function (result) {
                                if ($.trim(result) != '1') {
                                    $('#error-edit-class').html(result);
                                    $('#error-messages-modal').css("display", "block");
                                }
                            });
                        });
                        $(".forgot-pass").click(function () {
                            $("#login-user").addClass("hidden");
                            $("#login-user").removeClass("active");
                            $("#lost-password").removeClass("hidden");
                            $("#lost-password").addClass("active");
                        });
                        $("#icon-option-lesson").click(function () {
                            var sort = $(this).attr("data-sort");
                            $.post(home_url + "/?r=ajax/get_my_lesson", {sort: sort}, function (data) {
                                $("#table-my-lesson").html('');
                                if (data !== "0") {
                                    if (sort == "upward") {
                                        $("#icon-option-lesson").attr("data-sort", "downward");
                                        $(".img-sort-lesson").attr("src", "<?php echo get_template_directory_uri(); ?>/library/images/icon_Oldest.png");
                                    } else if (sort == "downward") {
                                        $("#icon-option-lesson").attr("data-sort", "upward");
                                        $(".img-sort-lesson").attr("src", "<?php echo get_template_directory_uri(); ?>/library/images/icon_Newest.png");
                                    }
                                    var html = '';
                                    data = JSON.parse(data);
                                    if (data.publesson != null) {
                                        if (data.publesson.length > 0) {
                                            $.each(data.publesson, function (i, v) {
                                                html += '<tr id="lesson-tr' + v.id + '" class="lesson-tr">';
                                                html += '<td>';
                                                html += ' <div class="cb-type2">';
                                                html += ' <label>';
                                                html += '<input type="checkbox" class="radio_buttons class_cb_search option-input-2 option-input-lesson option-input-lesson-check radio" value="' + v.id + '" data-name="' + v.name + '" name="select-tr"/>';
                                                html += ' </label>';
                                                html += '</div>';
                                                html += ' </td>';
                                                html += '<td class="table-img-icon">';
                                                if (v.cate == 1) {
                                                    html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_ENGLISH.png" alt="lesson" >';
                                                } else if (v.cate == 5) {
                                                    html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_MATH.png" alt="lesson" >';
                                                } else if (v.cate == 8) {
                                                    html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Dictionary.png" alt="lesson" >';
                                                } else if (v.cate == 9) {
                                                    html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Self-study.png" alt="lesson" >';
                                                } else if (v.cate == 10) {
                                                    html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_SAT.png" alt="lesson" >';
                                                }
                                                html += '</td>';
                                                html += '<td class="table-img-icon">';
                                                html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Ready-made_lesson.png" class="" alt="lesson" >';
                                                html += '<span class="name-ready-lesson my-public-lesson" data-lid="' + v.id + '">' + v.name + '</span>';
                                                html += ' </td>';
                                                html += '<td class="table-img-icon">';
                                                html += '<div class="delete-worksheet" data-lid="' + v.id + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Delete_worksheet.png" class="" alt="lesson" ></div>';

                                                html += '<div class="desc-my-lesson" data-lid="' + v.id + '" data-desc="' + v.description + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Detail_1.png" class="" alt="lesson" ></div>';
                                                if (v.price != 0) {
                                                    html += '<div class="price-lesson"><span class="">$</span>' + v.price + ' </div>';
                                                } else {
                                                    html += '<div class="price-lesson"><span class="">FREE</div>';
                                                }
                                                html += '</td>';
                                                html += '</tr>';
                                            });
                                        }
                                    }
                                    if (data.lesson != null) {
                                        if (data.lesson.length > 0) {
                                            $.each(data.lesson, function (i, v) {

                                                html += '<tr id="lesson-tr' + v.id + '" class="lesson-tr">';
                                                html += '<td>';
                                                html += ' <div class="cb-type2">';
                                                html += ' <label>';
                                                html += '<input type="checkbox" class="radio_buttons class_cb_search option-input-2 option-input-lesson option-input-lesson-check radio" value="' + v.id + '" data-name="' + v.name + '" name="select-tr"/>';
                                                html += ' </label>';
                                                html += '</div>';
                                                html += ' </td>';
                                                html += '<td class="table-img-icon">';
                                                if (v.cate == 1) {
                                                    html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_ENGLISH.png" alt="lesson" >';
                                                } else if (v.cate == 5) {
                                                    html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_MATH.png" alt="lesson" >';
                                                } else if (v.cate == 2) {
                                                    html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/Type_Science.png" alt="lesson" >';
                                                } else if (v.cate == 3) {
                                                    html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/Type_History.png" alt="lesson" >';
                                                } else if (v.cate == 4) {
                                                    html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/Type_Art_n_Design.png" alt="lesson" >';
                                                } else if (v.cate == 6) {
                                                    html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/Type_Music.png" alt="lesson" >';
                                                } else if (v.cate == 7) {
                                                    html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/Type_Others.png" alt="lesson" >';
                                                }
                                                html += '</td>';
                                                html += '<td class="table-img-icon">';
                                                html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Lesson.png" class="" alt="lesson" >';
                                                html += '<span class="name-lesson my-lesson-ws" data-lid="' + v.id + '">' + v.name + '</span>';
                                                html += ' </td>';
                                                html += '<td class="table-img-icon">';
                                                html += '<div class="delete-worksheet" data-lid="' + v.id + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Delete_worksheet.png" class="" alt="lesson" ></div>';
                                                html += '<div class="add-worksheet-ic" data-lid="' + v.id + '" data-name="' + v.name + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Add_worksheet.png" class="" alt="lesson" ></div>';
                                                html += '<div class="desc-my-lesson" data-lid="' + v.id + '" data-desc="' + v.description + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Detail_1.png" class="" alt="lesson" ></div>';
                                                html += '</td>';
                                                html += '</tr>';

                                            });

                                        }
                                    }
                                    $("#table-my-lesson").html(html);

                                } else {
                                    html += '<tr id="lesson-tr" class="lesson-tr">';
                                    html += '<td></td><td colspan="3">No results.</td></tr>';
                                    $("#table-my-lesson").html(html);
                                }

                            });
                        });
                        $("#icon-option-eng").click(function () {
                            if ($("#option-detail-eng").hasClass("hidden")) {
                                $("#option-detail-eng").removeClass("hidden");
                            } else {
                                $("#option-detail-eng").addClass(" hidden");
                            }
                        });
                        $("#icon-option-eng2").live("click", function () {
                            if ($("#option-detail-eng2").hasClass("hidden")) {
                                $("#option-detail-eng2").removeClass("hidden");
                            } else {
                                $("#option-detail-eng2").addClass(" hidden");
                            }
                        });
                        $("#icon-option-math3").live("click", function () {
                            if ($("#option-detail-math3").hasClass("hidden")) {
                                $("#option-detail-math3").removeClass("hidden");
                            } else {
                                $("#option-detail-math3").addClass(" hidden");
                            }
                        });
                        $("#icon-option-eng3").live("click", function () {
                            if ($("#option-detail-eng3").hasClass("hidden")) {
                                $("#option-detail-eng3").removeClass("hidden");
                            } else {
                                $("#option-detail-eng3").addClass(" hidden");
                            }
                        });
                        $("#icon-option-math").click(function () {
                            if ($("#option-detail-math").hasClass("hidden")) {
                                $("#option-detail-math").removeClass("hidden");
                            } else {
                                $("#option-detail-math").addClass(" hidden");
                            }
                        });
                        $("#icon-option-math2").live("click", function () {
                            if ($("#option-detail-math2").hasClass("hidden")) {
                                $("#option-detail-math2").removeClass("hidden");
                            } else {
                                $("#option-detail-math2").addClass(" hidden");
                            }
                        });
                        $("#icon-option-library").click(function () {
                            var sort = $(this).attr("data-sort");
                            $.post(home_url + "/?r=ajax/get_my_library", {sort: sort}, function (data) {
                                $("#table-library").html('');
                                if (data !== "0") {
                                    if (sort == "upward") {
                                        $("#icon-option-library").attr("data-sort", "downward");
                                        $(".img-sort").attr("src", "<?php echo get_template_directory_uri(); ?>/library/images/icon_Oldest.png");
                                    } else if (sort == "downward") {
                                        $("#icon-option-library").attr("data-sort", "upward");
                                        $(".img-sort").attr("src", "<?php echo get_template_directory_uri(); ?>/library/images/icon_Newest.png");
                                    }
                                    data = JSON.parse(data);
                                    if (data.library.length > 0) {
                                        $.each(data.library, function (i, v) {
                                            var html = '';
                                            html += '<tr id="tr-lesson-' + v.sheet_id + '" class="tr-lesson">';
                                            html += ' <td >';
                                            html += '<div class="cb-type2">';
                                            html += '<label>';
                                            html += ' <input type="checkbox" class="radio_buttons class_cb_search option-input-2 option-input-lesson option-input-mylibrary radio" value="' + v.name + '" data-id="' + v.sheet_id + '" name="select-tr"/>';
                                            html += '</label>';
                                            html += '</div>';
                                            html += '</td>';
                                            html += '<td class="table-img-icon">';
                                            if (v.cate == 1) {
                                                html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_ENGLISH.png" alt="lesson" >';
                                            } else if (v.cate == 5) {
                                                html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_MATH.png" alt="lesson" >';
                                            } else if (v.cate == 2) {
                                                html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/Type_Science.png" alt="lesson" >';
                                            } else if (v.cate == 3) {
                                                html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/Type_History.png" alt="lesson" >';
                                            } else if (v.cate == 4) {
                                                html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/Type_Art_n_Design.png" alt="lesson" >';
                                            } else if (v.cate == 6) {
                                                html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/Type_Music.png" alt="lesson" >';
                                            } else if (v.cate == 7) {
                                                html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/Type_Others.png" alt="lesson" >';
                                            }
                                            html += v.assignment + '</td>';
                                            html += '<td class="table-img-icon">';
                                            html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Worksheet.png" class="" alt="lesson" >';
                                            html += '<span class="name-lesson-2">' + v.name + '</span>';
                                            html += '</td>';
                                            html += '<td class="table-img-icon">';
                                            html += '<div class="delete-worksheet" data-id="' + v.id + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Delete_worksheet.png" alt="lesson" ></div>';
                                            html += '<div class="magnifiy-worksheet-ic"><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Magnifiy.png" class="" alt="lesson" ></div>';
                                            html += '<div class="detail-worksheet-ic" data-sid="' + v.sheet_id + '" ><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Detail_1.png" class="" alt="lesson" ></div>';
                                            html += '</td>';
                                            html += '</tr>';
                                            $("#table-library").append(html);
                                        });
                                    }
                                } else {
                                    var html = '';
                                    html += '<tr><td colspan="4">No results.</td>';
                                    html += '</tr>';
                                    $("#table-library").append(html);
                                }
                            });
                        });
                        $("#icon-option-library2").click(function () {
                            var sort = $(this).attr("data-sort");
                            $.post(home_url + "/?r=ajax/get_my_library", {sort: sort}, function (data) {
                                $("#table-library2").html('');
                                if (data !== "0") {
                                    if (sort == "upward") {
                                        $("#icon-option-library2").attr("data-sort", "downward");
                                        $(".img-sort2").attr("src", "<?php echo get_template_directory_uri(); ?>/library/images/icon_Oldest.png");
                                    } else if (sort == "downward") {
                                        $("#icon-option-library2").attr("data-sort", "upward");
                                        $(".img-sort2").attr("src", "<?php echo get_template_directory_uri(); ?>/library/images/icon_Newest.png");
                                    }
                                    data = JSON.parse(data);
                                    if (data.library.length > 0) {
                                        $.each(data.library, function (i, v) {
                                            var html = '';
                                            html += '<tr id="tr-lesson-' + v.sheet_id + '" class="tr-lesson">';
                                            html += ' <td >';
                                            html += '<div class="cb-type2">';
                                            html += '<label>';
                                            html += ' <input type="checkbox" class="radio_buttons class_cb_search option-input-2 option-input-lesson option-input-mylibrary radio" value="' + v.name + '" data-id="' + v.sheet_id + '" name="select-tr"/>';
                                            html += '</label>';
                                            html += '</div>';
                                            html += '</td>';
                                            html += '<td class="table-img-icon">';
                                            if (v.cate == 1) {
                                                html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_ENGLISH.png" alt="lesson" >';
                                            } else if (v.cate == 5) {
                                                html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_MATH.png" alt="lesson" >';
                                            } else if (v.cate == 2) {
                                                html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/Type_Science.png" alt="lesson" >';
                                            } else if (v.cate == 3) {
                                                html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/Type_History.png" alt="lesson" >';
                                            } else if (v.cate == 4) {
                                                html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/Type_Art_n_Design.png" alt="lesson" >';
                                            } else if (v.cate == 6) {
                                                html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/Type_Music.png" alt="lesson" >';
                                            } else if (v.cate == 7) {
                                                html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/Type_Others.png" alt="lesson" >';
                                            }
                                            html += v.assignment + '</td>';
                                            html += '<td class="table-img-icon">';
                                            html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Worksheet.png" class="" alt="lesson" >';
                                            html += '<span class="name-lesson-2">' + v.name + '</span>';
                                            html += '</td>';
                                            html += '<td class="table-img-icon">';
                                            html += '<div class="delete-worksheet" data-id="' + v.id + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Delete_worksheet.png" alt="lesson" ></div>';
                                            html += '<div class="magnifiy-worksheet-ic"><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Magnifiy.png" class="" alt="lesson" ></div>';
                                            html += '<div class="detail-worksheet-ic" data-sid="' + v.sheet_id + '" ><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Detail_1.png" class="" alt="lesson" ></div>';
                                            html += '</td>';
                                            html += '</tr>';
                                            $("#table-library2").append(html);
                                        });
                                    }
                                } else {
                                    var html = '';
                                    html += '<tr><td colspan="4">No results.</td>';
                                    html += '</tr>';
                                    $("#table-library2").append(html);
                                }
                            });
                        });
                        $("#icon-option-ready").click(function () {
                            if ($("#option-detail-ready").hasClass("hidden")) {
                                $("#option-detail-ready").removeClass("hidden");
                            } else {
                                $("#option-detail-ready").addClass(" hidden");
                            }
                        });
                        $("#select-all-lesson").click(function () {
                            if ($("#select-all-lesson").is(":checked")) {
                                $(".option-input-lesson-check").attr("checked", true);
                            } else
                                $(".option-input-lesson-check").attr("checked", false);
                        });
                        $("#select-all-math").click(function () {
                            if ($("#select-all-math").is(":checked")) {
                                $(".option-input-worksheet-math").attr("checked", true);
                            } else
                                $(".option-input-worksheet-math").attr("checked", false);
                        });
                        $("#select-all-math2").live("click", function () {
                            if ($("#select-all-math2").is(":checked")) {
                                $(".option-input-worksheet-math").attr("checked", true);
                            } else
                                $(".option-input-worksheet-math").attr("checked", false);
                        });
                        $("#select-all-math3").live("click", function () {
                            if ($("#select-all-math3").is(":checked")) {
                                $(".option-input-worksheet-math").attr("checked", true);
                            } else
                                $(".option-input-worksheet-math").attr("checked", false);
                        });
                        $("#select-all-eng").click(function () {
                            if ($("#select-all-eng").is(":checked")) {
                                $(".option-input-worksheet-eng").attr("checked", true);
                            } else
                                $(".option-input-worksheet-eng").attr("checked", false);
                        });
                        $("#select-all-eng2").live("click", function () {
                            if ($("#select-all-eng2").is(":checked")) {
                                $(".option-input-worksheet-eng").attr("checked", true);
                            } else
                                $(".option-input-worksheet-eng").attr("checked", false);
                        });
                        $("#select-all-eng3").live("click", function () {
                            if ($("#select-all-eng3").is(":checked")) {
                                $(".option-input-worksheet-eng").attr("checked", true);
                            } else
                                $(".option-input-worksheet-eng").attr("checked", false);
                        });
                        $("#select-all-mylibrary").live("click", function () {
                            if ($("#select-all-mylibrary").is(":checked")) {
                                $(".option-input-mylibrary").attr("checked", true);
                            } else
                                $(".option-input-mylibrary").attr("checked", false);
                        });
                        $("#select-all-mylibrary2").live("click", function () {
                            if ($("#select-all-mylibrary2").is(":checked")) {
                                $(".option-input-mylibrary").attr("checked", true);
                            } else
                                $(".option-input-mylibrary").attr("checked", false);
                        });
                        $("#select-all-ready").click(function () {
                            if ($("#select-all-ready").is(":checked")) {
                                $(".option-input-ready").attr("checked", true);
                            } else
                                $(".option-input-ready").attr("checked", false);
                        });
                        $("#btn-assign").click(function () {
                            if ($("#btn-assign").hasClass("btn-assign")) {
                                $("#btn-assign").removeClass("btn-assign");
                                $("#btn-assign").addClass("btn-down");
                                $("#select-assign").slideDown("fast");
                            } else {
                                $("#btn-assign").removeClass("btn-down");
                                $("#btn-assign").addClass("btn-assign");
                                $("#select-assign").slideUp("fast");
                            }
                        });
                        $("#btn-assign-subject").click(function () {
                            if ($("#btn-assign-subject").hasClass("btn-assign")) {
                                $("#btn-assign-subject").removeClass("btn-assign");
                                $("#btn-assign-subject").addClass("btn-down");
                                $("#select-assign-subject").slideDown("fast");
                            } else {
                                $("#btn-assign-subject").removeClass("btn-down");
                                $("#btn-assign-subject").addClass("btn-assign");
                                $("#select-assign-subject").slideUp("fast");
                            }
                        });
                        $("#btn-ready-lesson-down").click(function () {
                            $("#btn-ready-lesson-up").removeClass("hidden");
                            $("#btn-ready-lesson-down").addClass("hidden");
                            $("#btn-ready-lesson-up").addClass("active");
                            $("#open-search-ready").slideDown("fast");
                        });
                        $("#btn-ready-lesson-up").click(function () {
                            $("#btn-ready-lesson-down").removeClass("hidden");
                            $("#btn-ready-lesson-up").addClass("hidden");
                            $("#btn-ready-lesson-down").addClass("active");
                            $("#open-search-ready").slideUp("fast");
                        });
                        $("#search-my-lib").click(function () {
                            if ($("#search-my-lib").hasClass("btn-assign")) {
                                $("#search-my-lib").removeClass("btn-assign");
                                $("#search-my-lib").addClass("btn-down");
                                $("#open-search-my-lib").slideDown("fast");
                                $("#search-my-lib").css("background", "url(<?php echo get_template_directory_uri(); ?>/library/images/icon_Orange_up.png) no-repeat");
                                $("#search-my-lib").text("Close Search");
                            } else
                            {
                                $("#search-my-lib").removeClass("btn-down");
                                $("#search-my-lib").addClass("btn-assign");
                                $("#open-search-my-lib").slideUp("fast");
                                $("#search-my-lib").text("Open Search");
                                $("#search-my-lib").css("background", "url(<?php echo get_template_directory_uri(); ?>/library/images/icon_Orange_down.png) no-repeat");
                            }
                        });
                        $("#search-my-lib2").click(function () {
                            if ($("#search-my-lib2").hasClass("btn-assign")) {
                                $("#search-my-lib2").removeClass("btn-assign");
                                $("#search-my-lib2").addClass("btn-down");
                                $("#open-search-my-lib2").slideDown("fast");
                                $("#search-my-lib2").css("background", "url(<?php echo get_template_directory_uri(); ?>/library/images/icon_Orange_up.png) no-repeat");
                                $("#search-my-lib2").text("Close Search");
                            } else
                            {
                                $("#search-my-lib2").removeClass("btn-down");
                                $("#search-my-lib2").addClass("btn-assign");
                                $("#open-search-my-lib2").slideUp("fast");
                                $("#search-my-lib2").text("Open Search");
                                $("#search-my-lib2").css("background", "url(<?php echo get_template_directory_uri(); ?>/library/images/icon_Orange_down.png) no-repeat");
                            }
                        });
                        $("#btn-open-search").click(function () {
                            if ($("#btn-open-search").hasClass("btn-assign")) {
                                $("#btn-open-search").removeClass("btn-assign");
                                $("#btn-open-search").addClass("btn-down");
                                $("#open-search-math").slideDown("fast");
                                $("#btn-open-search").css("background", "url(<?php echo get_template_directory_uri(); ?>/library/images/icon_Orange_up.png) no-repeat");
                                $("#btn-open-search").text("Close Search");
                            } else
                            {
                                $("#btn-open-search").removeClass("btn-down");
                                $("#btn-open-search").addClass("btn-assign");
                                $("#open-search-math").slideUp("fast");
                                $("#btn-open-search").text("Open Search");
                                $("#btn-open-search").css("background", "url(<?php echo get_template_directory_uri(); ?>/library/images/icon_Orange_down.png) no-repeat");
                            }
                        });
                        $("#filter-level-categories").change(function () {
                            var cate = $("#filter-level-categoriesSelectBoxItText").attr('data-val');
                            $.post(home_url + "/?r=ajax/get_level", {cate: cate}, function (data) {
                                var dt = JSON.parse(data);
                                $('#filter-levels').html(dt.level).data("selectBox-selectBoxIt").refresh();
                            });

                            $.post(home_url + "/?r=ajax/get_level", {level: 0}, function (data) {
                                var dt = JSON.parse(data);
                                $('#filter-sublevels').html(dt.sublevel).data("selectBox-selectBoxIt").refresh();
                            });
                        });
                        $("#filter-level-categories2").change(function () {
                            var cate = $("#filter-level-categories2SelectBoxItText").attr('data-val');
                            $.post(home_url + "/?r=ajax/get_level", {cate: cate}, function (data) {
                                var dt = JSON.parse(data);
                                $('#filter-levels2').html(dt.level).data("selectBox-selectBoxIt").refresh();
                            });
                            $.post(home_url + "/?r=ajax/get_level", {level: 0}, function (data) {
                                var dt = JSON.parse(data);
                                $('#filter-sublevels2').html(dt.sublevel).data("selectBox-selectBoxIt").refresh();
                            });
                        });
                        $("#filter-level-categories3").change(function () {
                            var cate = $("#filter-level-categories3SelectBoxItText").attr('data-val');
                            $.post(home_url + "/?r=ajax/get_level", {cate: cate}, function (data) {
                                var dt = JSON.parse(data);
                                $('#filter-levels3').html(dt.sublevel).data("selectBox-selectBoxIt").refresh();
                            });
                            $.post(home_url + "/?r=ajax/get_level", {level: 0}, function (data) {
                                var dt = JSON.parse(data);
                                $('#filter-sublevels3').html(dt.sublevel).data("selectBox-selectBoxIt").refresh();
                            });
                        });
                        $("#filter-levels").change(function () {
                            var level = $("#filter-levelsSelectBoxItText").attr('data-val');
                            $.post(home_url + "/?r=ajax/get_level", {level: level}, function (data) {
                                var dt = JSON.parse(data);
                                $('#filter-sublevels').html(dt.sublevel).data("selectBox-selectBoxIt").refresh();
                            });
                        });
                        $("#filter-levels2").change(function () {
                            var level = $("#filter-levels2SelectBoxItText").attr('data-val');
                            $.post(home_url + "/?r=ajax/get_level", {level: level}, function (data) {
                                var dt = JSON.parse(data);
                                $('#filter-sublevels2').html(dt.sublevel).data("selectBox-selectBoxIt").refresh();
                            });
                        });
                        $("#filter-levels3").change(function () {
                            var level = $("#filter-levels3SelectBoxItText").attr('data-val');
                            $.post(home_url + "/?r=ajax/get_level", {level: level}, function (data) {
                                var dt = JSON.parse(data);
                                $('#filter-sublevels3').html(dt.sublevel).data("selectBox-selectBoxIt").refresh();
                            });
                        });
                        $("#btn-open-search2").live("click", function () {
                            if ($("#btn-open-search2").hasClass("btn-assign")) {
                                $("#btn-open-search2").removeClass("btn-assign");
                                $("#btn-open-search2").addClass("btn-down");
                                $("#open-search-math2").slideDown("fast");
                                $("#btn-open-search2").css("background", "url(<?php echo get_template_directory_uri(); ?>/library/images/icon_Orange_up.png) no-repeat");
                                $("#btn-open-search2").text("Close Search");
                            } else
                            {
                                $("#btn-open-search2").removeClass("btn-down");
                                $("#btn-open-search2").addClass("btn-assign");
                                $("#open-search-math2").slideUp("fast");
                                $("#btn-open-search2").text("Open Search");
                                $("#btn-open-search2").css("background", "url(<?php echo get_template_directory_uri(); ?>/library/images/icon_Orange_down.png) no-repeat");
                            }
                        });
                        $("#btn-open-search-eng").click(function () {
                            if ($("#btn-open-search-eng").hasClass("btn-assign")) {
                                $("#btn-open-search-eng").removeClass("btn-assign");
                                $("#btn-open-search-eng").addClass("btn-down");
                                $("#open-search-eng").slideDown("fast");
                                $("#btn-open-search-eng").css("background", "url(<?php echo get_template_directory_uri(); ?>/library/images/icon_Orange_up.png) no-repeat");
                                $("#btn-open-search-eng").text("Close Search");
                            } else
                            {
                                $("#btn-open-search-eng").removeClass("btn-down");
                                $("#btn-open-search-eng").addClass("btn-assign");
                                $("#open-search-eng").slideUp("fast");
                                $("#btn-open-search-eng").text("Open Search");
                                $("#btn-open-search-eng").css("background", "url(<?php echo get_template_directory_uri(); ?>/library/images/icon_Orange_down.png) no-repeat");
                            }
                        });
                        $("#btn-open-search-eng2").live("click", function () {
                            if ($("#btn-open-search-eng2").hasClass("btn-assign")) {
                                $("#btn-open-search-eng2").removeClass("btn-assign");
                                $("#btn-open-search-eng2").addClass("btn-down");
                                $("#open-search-eng2").slideDown("fast");
                                $("#btn-open-search-eng2").css("background", "url(<?php echo get_template_directory_uri(); ?>/library/images/icon_Orange_up.png) no-repeat");
                                $("#btn-open-search-eng2").text("Close Search");
                            } else
                            {
                                $("#btn-open-search-eng2").removeClass("btn-down");
                                $("#btn-open-search-eng2").addClass("btn-assign");
                                $("#open-search-eng2").slideUp("fast");
                                $("#btn-open-search-eng2").text("Open Search");
                                $("#btn-open-search-eng2").css("background", "url(<?php echo get_template_directory_uri(); ?>/library/images/icon_Orange_down.png) no-repeat");
                            }
                        });
                        $("#btn-open-search-eng3").live("click", function () {
                            if ($("#btn-open-search-eng3").hasClass("btn-assign")) {
                                $("#btn-open-search-eng3").removeClass("btn-assign");
                                $("#btn-open-search-eng3").addClass("btn-down");
                                $("#open-search-eng3").slideDown("fast");
                                $("#btn-open-search-eng3").css("background", "url(<?php echo get_template_directory_uri(); ?>/library/images/icon_Orange_up.png) no-repeat");
                                $("#btn-open-search-eng3").text("Close Search");
                            } else
                            {
                                $("#btn-open-search-eng3").removeClass("btn-down");
                                $("#btn-open-search-eng3").addClass("btn-assign");
                                $("#open-search-eng3").slideUp("fast");
                                $("#btn-open-search-eng3").text("Open Search");
                                $("#btn-open-search-eng3").css("background", "url(<?php echo get_template_directory_uri(); ?>/library/images/icon_Orange_down.png) no-repeat");
                            }
                        });
                        $("#btn-open-search3").live("click", function () {
                            if ($("#btn-open-search3").hasClass("btn-assign")) {
                                $("#btn-open-search3").removeClass("btn-assign");
                                $("#btn-open-search3").addClass("btn-down");
                                $("#open-search-math3").slideDown("fast");
                                $("#btn-open-search3").css("background", "url(<?php echo get_template_directory_uri(); ?>/library/images/icon_Orange_up.png) no-repeat");
                                $("#btn-open-search3").text("Close Search");
                            } else
                            {
                                $("#btn-open-search3").removeClass("btn-down");
                                $("#btn-open-search3").addClass("btn-assign");
                                $("#open-search-math3").slideUp("fast");
                                $("#btn-open-search3").text("Open Search");
                                $("#btn-open-search3").css("background", "url(<?php echo get_template_directory_uri(); ?>/library/images/icon_Orange_down.png) no-repeat");
                            }
                        });
                        $(".up-worksheet,.down-worksheet").live("click", function () {
                            var row = $(this).parents("tr:first");
                            var cls_prev = row.prev().hasClass("detail-worksheet");
                            var cls_next = row.next().hasClass("detail-worksheet");
                            var slid = $(this).attr("data-slid");
                            var lid = $(this).attr("data-lid");
                            if ($(this).is(".up-worksheet")) {
                                if (cls_prev == true) {
                                    $.post(home_url + "/?r=ajax/update_order_sheet", {order: "up", id: slid, lid: lid}, function (data) {
                                    });
                                    row.insertBefore(row.prev());
                                }
                            } else {
                                if (cls_next == true) {
                                    $.post(home_url + "/?r=ajax/update_order_sheet", {order: "down", id: slid, lid: lid}, function (data) {
                                    });
                                    row.insertAfter(row.next());
                                }
                            }
                        });
                        $("#assign-btn").click(function () {
                            var selected = new Array();
                            $(".option-input-ready:checked").each(function () {
                                var data_id = $(this).attr("data-id");
                                var data_sheet = $(this).val();
                                selected.push({"id": data_id, "sheet": data_sheet});
                            });
                            var html = '';
                            if (selected.length !== 0) {
                                $("#select-all-ready").attr("checked", false);
                            } else {
                                html += 'Please select a class to assign the lessons.';
                                $(".box-title-1").html(html);
                                $("#modal-alert").css('display', 'block');
                            }
                        });
                        $("#btn-create-library").click(function () {
                            var check = $(".create-lesson-content").hasClass("active");
                            $("#input-style").attr('value', '');
                            $('#post_mylibrary_ifr').contents().find('#tinymce').text('');
                            if (check == false) {
                                var selected = new Array();
                                $(".option-input-mylibrary:checked").each(function () {
                                    var data_id = $(this).attr("data-id");
                                    var data_sheet = $(this).val();
                                    selected.push({"id": data_id, "sheet": data_sheet});
                                });
                                var html = '';
                                if (selected.length !== 0) {
                                    var table_confirm_worksheet = $("#table-confirm-worksheet");
                                    for (var i = 0; i < selected.length; i++) {
                                        html += '<tr data-sid="' + selected[i].id + '" id="' + selected[i].id + '" class="row-worksheet">';
                                        html += ' <td class="table-img-icon" style="padding-bottom: 10px;padding-top: 5px; width:95%">';
                                        html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Worksheet.png" class="" alt="lesson" >';
                                        html += '<span class="name-lesson-2">' + selected[i].sheet + '</span>';
                                        html += '</td>';
                                        html += '<td class="table-img-icon text-right delete-sheet"  ><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Delete_worksheet.png" class="" alt="lesson"></td>';
                                        html += '</tr>';
                                    }
                                    table_confirm_worksheet.append(html);
                                    $(".table-my-lesson").removeClass("active");
                                    $(".table-my-lesson").addClass("hidden");
                                    $(".create-lesson-content").removeClass("hidden");
                                    $(".create-lesson-content").addClass("active");
                                    $("#select-all-mylibrary").attr("checked", false);
                                    $.post(home_url + "/?r=ajax/get_cate", {type: "GET"}, function (data) {
                                        var data = JSON.parse(data);
                                        if (data.cate.length > 0) {
                                            var html_cate = '';
                                            $.each(data.cate, function (i, v) {
                                                html_cate += '<option  value="' + v.id + '">' + v.name + '</option>';
                                            });
                                            $('#select-my-lib').html(html_cate).data("selectBox-selectBoxIt").refresh();
                                        }
                                    });
                                } else {
                                    html += 'Please select worksheets first.';
                                    $(".box-title-1").html(html);
                                    $("#modal-alert").css('display', 'block');
                                }
                            }
                        });
                        $("#close-icon-sub").click(function () {
                            $("#input-style-sub").attr('value', '');
                            $(".create-subject-content").removeClass("active");
                            $(".create-subject-content").addClass("hidden");
                            $(".table-my-lesson").removeClass("hidden");
                            $(".table-my-lesson").addClass("active");
                            $(".option-input-lesson").attr("checked", false);
                            $("#select-all-subject").attr("checked", false);
                            $("#table-confirm-subject").html("");
                        });
                        $("#close-icon").click(function () {
                            $("#input-style").attr('value', '');
                            $(".create-lesson-content").removeClass("active");
                            $(".create-lesson-content").addClass("hidden");
                            $(".table-my-lesson").removeClass("hidden");
                            $(".table-my-lesson").addClass("active");
                            $(".option-input-mylibrary").attr("checked", false);
                            $("#select-all-mylibrary").attr("checked", false);
                            $("#table-confirm-worksheet").html("");
                        });
                        $("#cancel-sub").click(function () {
                            $(".create-subject-content").removeClass("active");
                            $(".create-subject-content").addClass("hidden");
                            $(".table-my-lesson").removeClass("hidden");
                            $(".table-my-lesson").addClass("active");
                            $("#select-all-subject").attr("checked", false);
                            $(".option-input-lesson-check").attr("checked", false);
                            $("#table-confirm-subject").html("");
                        });
                        $("#cancel-btn1").click(function () {
                            $(".create-lesson-content").removeClass("active");
                            $(".create-lesson-content").addClass("hidden");
                            $(".table-my-lesson").removeClass("hidden");
                            $(".table-my-lesson").addClass("active");
                            $("#select-all-mylibrary").attr("checked", false);
                            $(".option-input-mylibrary").attr("checked", false);
                            $("#table-confirm-worksheet").html("");
                        });
                        $("#tab-public-lib-eng").click(function () {
                            if ($("#btn-open-search-eng").hasClass("btn-down"))
                            {
                                $("#btn-open-search-eng").removeClass("btn-down");
                                $("#btn-open-search-eng").addClass("btn-assign");
                                $("#open-search-eng").slideUp("fast");
                            }
                            var kt = $("#tab-eng-lib").hasClass("active");
                            if (kt == false)
                                $("#open-search-eng").css("display", "none");
                            $("#btn-open-search-eng").text("Open Search");
                            $("#btn-open-search-eng").css("background", "url(<?php echo get_template_directory_uri(); ?>/library/images/icon_Orange_down.png) no-repeat");
                            $("#select-all-eng").attr("checked", false);
                            $("#option-detail-eng").addClass(" hidden");
                            var cat_level = '';
                            var level = '';
                            var sublevel = '';
                            var lang = '';
                            var sheet_name = '';
                            var group_name = '';
                            var assignment_id = '';
                            var homework_types = '';
                            var grade = '';
                            var trivia_exclusive = '';
                            var active = '';
                            var type = '';
                            getWorksheets(lang, sheet_name, group_name, assignment_id, homework_types, grade, trivia_exclusive, active, cat_level, level, sublevel, type);
                        });
                        $("#tab-public-lib-math").click(function () {
                            if ($("#btn-open-search").hasClass("btn-down"))
                            {
                                $("#btn-open-search").removeClass("btn-down");
                                $("#btn-open-search").addClass("btn-assign");
                                $("#open-search-math").slideUp("fast");
                            }
                            var kt = $("#tab-math-lib").hasClass("active");
                            if (kt == false)
                                $("#open-search-math").css("display", "none");
                            $("#btn-open-search").text("Open Search");
                            $("#btn-open-search").css("background", "url(<?php echo get_template_directory_uri(); ?>/library/images/icon_Orange_down.png) no-repeat");
                            $("#select-all-math").attr("checked", false);
                            $("#option-detail-math").addClass(" hidden");
                            var cat_level = '';
                            var level = '';
                            var sublevel = '';
                            var lang = '';
                            var sheet_name = '';
                            var group_name = '';
                            var assignment_id = '';
                            var homework_types = '';
                            var grade = '';
                            var trivia_exclusive = '';
                            var active = '';
                            var type = '';
                            getWorksheets(lang, sheet_name, group_name, assignment_id, homework_types, grade, trivia_exclusive, active, cat_level, level, sublevel, type);
                        });
                        $("#tab-public-lib-math3").click(function () {
                            if ($("#btn-open-search3").hasClass("btn-down"))
                            {
                                $("#btn-open-search3").removeClass("btn-down");
                                $("#btn-open-search3").addClass("btn-assign");
                                $("#open-search-math3").slideUp("fast");
                            }
                            var kt = $("#tab-math-lib3").hasClass("active");
                            if (kt == false)
                                $("#open-search-math3").css("display", "none");
                            $("#btn-open-search3").text("Open Search");
                            $("#btn-open-search3").css("background", "url(<?php echo get_template_directory_uri(); ?>/library/images/icon_Orange_down.png) no-repeat");
                            $("#select-all-math3").attr("checked", false);
                            $("#option-detail-math3").addClass(" hidden");
                            var cat_level = '';
                            var level = '';
                            var sublevel = '';
                            var lang = '';
                            var sheet_name = '';
                            var group_name = '';
                            var assignment_id = '';
                            var homework_types = '';
                            var grade = '';
                            var trivia_exclusive = '';
                            var active = '';
                            var type = '';
                            getWorksheets2(lang, sheet_name, group_name, assignment_id, homework_types, grade, trivia_exclusive, active, cat_level, level, sublevel, type);
                        });
                        $("#tab-public-lib-eng3").click(function () {
                            if ($("#btn-open-search-eng3").hasClass("btn-down"))
                            {
                                $("#btn-open-search-eng3").removeClass("btn-down");
                                $("#btn-open-search-eng3").addClass("btn-assign");
                                $("#open-search-eng3").slideUp("fast");
                            }
                            var kt = $("#tab-eng-lib3").hasClass("active");
                            if (kt == false)
                                $("#open-search-eng3").css("display", "none");
                            $("#btn-open-search-eng3").text("Open Search");
                            $("#btn-open-search-eng3").css("background", "url(<?php echo get_template_directory_uri(); ?>/library/images/icon_Orange_down.png) no-repeat");
                            $("#select-all-eng3").attr("checked", false);
                            $("#option-detail-eng3").addClass(" hidden");
                            var cat_level = '';
                            var level = '';
                            var sublevel = '';
                            var lang = '';
                            var sheet_name = '';
                            var group_name = '';
                            var assignment_id = '';
                            var homework_types = '';
                            var grade = '';
                            var trivia_exclusive = '';
                            var active = '';
                            var type = '';
                            getWorksheets2(lang, sheet_name, group_name, assignment_id, homework_types, grade, trivia_exclusive, active, cat_level, level, sublevel, type);
                        });
                        $("#tab-public-lib-eng2").click(function () {
                            if ($("#btn-open-search-eng2").hasClass("btn-down"))
                            {
                                $("#btn-open-search-eng2").removeClass("btn-down");
                                $("#btn-open-search-eng2").addClass("btn-assign");
                                $("#open-search-eng2").slideUp("fast");
                            }
                            var kt = $("#tab-eng-lib2").hasClass("active");
                            if (kt == false)
                                $("#open-search-eng2").css("display", "none");
                            $("#btn-open-search-eng2").text("Open Search");
                            $("#btn-open-search-eng2").css("background", "url(<?php echo get_template_directory_uri(); ?>/library/images/icon_Orange_down.png) no-repeat");
                            $("#select-all-eng2").attr("checked", false);
                            $("#option-detail-eng2").addClass(" hidden");
                            var cat_level = '';
                            var level = '';
                            var sublevel = '';
                            var lang = '';
                            var sheet_name = '';
                            var group_name = '';
                            var assignment_id = '';
                            var homework_types = '';
                            var grade = '';
                            var trivia_exclusive = '';
                            var active = '';
                            var type = '';
                            getWorksheets2(lang, sheet_name, group_name, assignment_id, homework_types, grade, trivia_exclusive, active, cat_level, level, sublevel, type);
                        });
                        $("#tab-public-lib-math2").click(function () {
                            if ($("#btn-open-search2").hasClass("btn-down"))
                            {
                                $("#btn-open-search2").removeClass("btn-down");
                                $("#btn-open-search2").addClass("btn-assign");
                                $("#open-search-math2").slideUp("fast");
                            }
                            var kt = $("#tab-math-lib2").hasClass("active");
                            if (kt == false)
                                $("#open-search-math2").css("display", "none");
                            $("#btn-open-search2").text("Open Search");
                            $("#btn-open-search2").css("background", "url(<?php echo get_template_directory_uri(); ?>/library/images/icon_Orange_down.png) no-repeat");
                            $("#select-all-math2").attr("checked", false);
                            $("#option-detail-math2").addClass(" hidden");
                            var cat_level = '';
                            var level = '';
                            var sublevel = '';
                            var lang = '';
                            var sheet_name = '';
                            var group_name = '';
                            var assignment_id = '';
                            var homework_types = '';
                            var grade = '';
                            var trivia_exclusive = '';
                            var active = '';
                            var type = '';
                            getWorksheets2(lang, sheet_name, group_name, assignment_id, homework_types, grade, trivia_exclusive, active, cat_level, level, sublevel, type);
                        });
                        $("#public-library").click(function () {
                            if ($("#btn-open-search-eng").hasClass("btn-down"))
                            {
                                $("#btn-open-search-eng").removeClass("btn-down");
                                $("#btn-open-search-eng").addClass("btn-assign");
                            }
                            $("#select-all-eng").attr("checked", false);
                            $("#btn-open-search-eng").text("Open Search");
                            $("#btn-open-search-eng").css("background", "url(<?php echo get_template_directory_uri(); ?>/library/images/icon_Orange_down.png) no-repeat");
                            $("#option-detail-eng").addClass(" hidden");
                            $("#select-all-eng").attr("checked", false);
                            $("#open-search-eng").css("display", "none");
                            $("#tab-public-lib-eng").addClass(" active");
                            $("#tab-public-lib-math").removeClass(" active");
                            $("#tab-math-lib").removeClass(" active in");
                            $("#tab-eng-lib").addClass(" active in");
                            $.post(home_url + "/?r=ajax/ik_check_user_student", {type: "GET"}, function (data) {
                                if (data !== 'T-Qual' && data !== 'T-Reg' && data !== 'T-M-Reg' && data !== 'T-M-Qual' && data !== 'Teacher') {

                                    $("#save-eng-lib").addClass("hidden");
                                }
                            });
                            var cat_level = '';
                            var level = '';
                            var sublevel = '';
                            var lang = '';
                            var sheet_name = '';
                            var group_name = '';
                            var assignment_id = '';
                            var homework_types = '';
                            var grade = '';
                            var trivia_exclusive = '';
                            var active = '';
                            var type = '';
                            getWorksheets(lang, sheet_name, group_name, assignment_id, homework_types, grade, trivia_exclusive, active, cat_level, level, sublevel, type);
                        });
                        $("#my-subject-li").click(function () {
                            if ($("#btn-assign-subject").hasClass("btn-down"))
                            {
                                $("#btn-assign-subject").removeClass("btn-down");
                                $("#btn-assign-subject").addClass("btn-assign");
                            }
                            $("#select-all-subject").attr("checked", false);
                            $("#select-all-less4").attr("checked", false);
                            $(".option-sub-check").attr("checked", false);
                            $("#select-assign-subject").css("display", "none");
                            $("#option-detail-subject").addClass(" hidden");
                            $(".my-lesson-body").removeClass("hidden");
                            $("#add-worksheet-subject").addClass(" hidden");
                            $("#search-my-less-3").addClass("hidden");
                            $("#search-my-less").removeClass("hidden");
                            var search = '';
                            get_my_subject(search);
                        });
                        $("#my-lesson-li").click(function () {
                            if ($("#btn-assign").hasClass("btn-down"))
                            {
                                $("#btn-assign").removeClass("btn-down");
                                $("#btn-assign").addClass("btn-assign");
                            }
                            $("#select-all-lesson").attr("checked", false);
                            $(".option-input-lesson-check").attr("checked", false);
                            $("#select-assign").css("display", "none");
                            $("#n").text("Search & Assign Lesson");
                            $("#option-detail-lesson").addClass(" hidden");
                            $(".my-lesson-body").removeClass("hidden");
                            $("#add-worksheet").addClass(" hidden");
                            $(".create-subject-content").removeClass("active");
                            $(".create-subject-content").addClass("hidden");
                            $(".table-my-lesson").removeClass("hidden");
                            $(".table-my-lesson").addClass("active");
                            var text = '';
                            $("#table-my-lesson").html('');
                            var table = "table-my-lesson";
                            get_my_lesson(text, table);
                            $.post(home_url + "/?r=ajax/ik_check_user_student", {type: "GET"}, function (data) {
                                if (data !== 'T-Qual' && data !== 'T-Reg' && data !== 'T-M-Reg' && data !== 'T-M-Qual' && data !== 'Teacher') {

                                    $(".my-lesson-body").addClass("hidden");
                                }
                            });
                        });
                        $("#my-library-li").click(function () {
                            if ($("#search-my-lib").hasClass("btn-down"))
                            {
                                $("#search-my-lib").removeClass("btn-down");
                                $("#search-my-lib").addClass("btn-assign");
                                $("#search-my-lib").text("Open Search");
                                $("#search-my-lib").css("background", "url(<?php echo get_template_directory_uri(); ?>/library/images/icon_Orange_down.png) no-repeat");
                            }
                            $("#open-search-my-lib").css("display", "none");
                            $("#select-all-mylibrary").attr("checked", false);
                            $(".create-lesson-content").removeClass("active");
                            $(".create-lesson-content").addClass("hidden");
                            $(".table-my-lesson").removeClass("hidden");
                            $(".table-my-lesson").addClass("active");
                            $(".option-input-mylibrary").attr("checked", false);
                            $("#option-detail-library").addClass(" hidden");
                            $("#my-library").addClass("active in");
                            $("#input-style").attr('value', "");
                            $("#add-worksheet-mylib").addClass(' hidden');
                            $(".library-lib ").removeClass(' hidden');
                            $.post(home_url + "/?r=ajax/ik_check_user_student", {type: "GET"}, function (data) {
                                if (data !== 'T-Qual' && data !== 'T-Reg' && data !== 'T-M-Reg' && data !== 'T-M-Qual' && data !== 'Teacher') {

                                    $(".library-lib").addClass("hidden");
                                }
                            });
                            var text = '';
                            var table = "table-library";
                            get_my_library(text, table);
                        });
                        $(".refresh-public-lesson").click(function () {
                            if ($("#btn-ready-eng-down").hasClass("btn-ready-lesson-up"))
                            {
                                $("#btn-ready-eng-down").removeClass("btn-ready-lesson-up");
                                $("#btn-ready-eng-down").addClass("btn-ready-lesson-down");
                            }

                            if ($("#btn-dict-down").hasClass("btn-ready-lesson-up"))
                            {
                                $("#btn-dict-down").removeClass("btn-ready-lesson-up");
                                $("#btn-dict-down").addClass("btn-ready-lesson-down");
                            }
                            if ($("#eng-public").hasClass("hidden")) {
                                $("#eng-public").removeClass("hidden");
                                $("#eng-public").addClass("active");
                            }
                            if ($("#sat").hasClass("active")) {
                                $("#sat").removeClass("active");
                                $("#sat").addClass("hidden");
                            }
                            if ($("#eng-math").hasClass("hidden")) {
                                $("#eng-math").removeClass("hidden");
                                $("#eng-math").addClass("active");
                            }
                            if ($("#menu-math").hasClass("active")) {
                                $("#menu-math").removeClass("active");
                                $("#menu-eng").addClass("active");
                            }
                            if ($("#courses").hasClass("active")) {
                                $("#courses").removeClass("active");
                                $("#courses").addClass("hidden");
                            }
                            $("#dic-sel-stu").removeClass("active");
                            $("#dic-sel-stu").addClass("hidden");
                            $(".tab-pub-les").not("#tab-public-les-cri").removeClass("active");
                            $("#tab-public-les-cri").addClass("active");
                            $("#select-all-ready-eng").attr("checked", false);

                            $("#open-search-ready-eng").css('display', 'none');
                            $("#open-search-ready-math").css('display', 'none');
                            $("#open-search-dictionary").css('display', 'none');
                            $("#open-search-sat").css("display", "none");
                            $("#btn-ready-lesson-down").removeClass("hidden");
                            $("#btn-ready-lesson-up").addClass(" hidden");
                            $(".option-input-ready-eng").attr("checked", false);
                            $("#option-detail-ready").addClass(" hidden");
                            $("#search-ready-les-eng").val('');
                            $("#search-ready-les-math").val('');
                            if ($("#math-public").hasClass("active")) {
                                $("#math-public").removeClass("active");
                                $("#math-public").addClass("hidden");
                                $("#eng-public").removeClass("hidden");
                                $("#eng-public").addClass("active");
                            }
                            var text = '';
                            var table = "table-ready-lesson-eng";
                            get_groups_home_eng(text, table);
                        });
                        $("#done-btn").click(function () {
                            $("#modal-alert").css('display', 'none');
                            $(".modal-body").removeClass("class-more");
                        });
                        $("#add-more-btn").click(function () {
                            if ($("#btn-open-search-eng").hasClass("btn-down"))
                            {
                                $("#btn-open-search-eng").removeClass("btn-down");
                                $("#btn-open-search-eng").addClass("btn-assign");
                                $("#btn-open-search-eng").text("Open Search");
                                $("#btn-open-search-eng").css("background", "url(<?php echo get_template_directory_uri(); ?>/library/images/icon_Orange_down.png) no-repeat");
                            }
                            $("#open-search-eng").css('display', 'none');
                            $("#add-worksheet-mylib").removeClass("hidden");
                            $(".library-lib").addClass(" hidden");
                            $(".from-public-lib2").removeClass("active");
                            $(".from-public-lib2").slideUp("slow");
                            $("#icon-open-pub2").attr("src", "<?php echo get_template_directory_uri(); ?>/library/images/Icon_Blue_Open.png");
                            $(".table-eng-worksheet").html('');
                        });
                        $("#create-sub").click(function () {
                            var desc = $('#post_mysubject_ifr').contents().find('#tinymce').text();
                            var name = $('#input-style-sub').val();
                            var cate = $('#select-my-subSelectBoxItText').attr("data-val");
                            var selected = new Array();
                            $(".row-worksheet").each(function () {
                                var sid = $(this).attr("data-sid");
                                selected.push(sid);
                            });
                            $.post(home_url + "/?r=ajax/add_my_lesson", {name: name, desc: desc, cate: cate, sheet: selected}, function (data) {
                                if (data !== 0) {
                                    var html = '';
                                    html += 'New Subject has been created! You can find it from My Subject.';
                                    $(".box-title-1").html(html);
                                    $("#modal-alert").css('display', 'block');
                                    $(".create-subject-content").removeClass("active");
                                    $(".create-subject-content").addClass("hidden");
                                    $(".table-my-lesson").removeClass("hidden");
                                    $(".table-my-lesson").addClass("active");
                                }
                            });
                        });
                        $("#create-btn1").click(function () {
//                          var desc=$.trim(tinymce.get('post_mylibrary').getContent();
                            var desc = $('#post_mylibrary_ifr').contents().find('#tinymce').text();
                            var name = $('#input-style').val();
                            var cate = $('#select-my-libSelectBoxItText').attr("data-val");
                            var selected = new Array();
                            $(".row-worksheet").each(function () {

                                var sid = $(this).attr("data-sid");
                                selected.push(sid);
                            });
                            $.post(home_url + "/?r=ajax/add_my_lesson", {name: name, desc: desc, cate: cate, sheet: selected}, function (data) {
                                if (data !== 0) {
                                    var html = '';
                                    html += 'New Lesson has been created! You can find it from My Lesson.';
                                    $(".box-title-1").html(html);
                                    $("#modal-alert").css('display', 'block');
                                    $(".create-lesson-content").removeClass("active");
                                    $(".create-lesson-content").addClass("hidden");
                                    $(".table-my-lesson").removeClass("hidden");
                                    $(".table-my-lesson").addClass("active");
                                    $(".option-input-mylibrary").attr("checked", false);
                                    $("#select-all-mylibrary").attr("checked", false);
                                }
                            });
                        });
                        $("#save-math-lib").click(function () {
                            var selected = new Array();
                            $(".option-input-worksheet-math:checked").each(function () {
                                var id = $(this).val();
                                var cate = $(this).attr("data-cate");
                                var sheet = $(this).attr("data-sheet");
                                selected.push({id: id, cate: cate, sheet: sheet});
                            });
                            if (selected.length == 0) {
                                var html = '';
                                html += 'Please select worksheets first.';
                                $(".box-title-1").html(html);
                                $("#modal-alert").css('display', 'block');
                            } else {
                                $.post(home_url + "/?r=ajax/add_my_library", {
                                    data: selected}, function (data) {
                                    if (data !== "0") {
                                        var html = '';
                                        html += 'Worksheets sucessfully saved to <span style="font-style:italic;">My Library</span>.';
                                        $(".box-title-1").html(html);
                                        $("#modal-alert").css('display', 'block');
                                        $("#public-lib").removeClass("active in");
                                        $("#my-library").addClass("active in");
                                        $(".pub-lib-li").removeClass("active");
                                        $(".my-lib-li").addClass("active");
                                        var text = '';
                                        var table = "table-library";
                                        get_my_library(text, table);
                                    } else {
                                        var html = '';
                                        html += 'Fail!';
                                        $(".box-title-1").html(html);
                                        $("#modal-alert").css('display', 'block');
                                    }
                                });
                            }
                        });
                        $("#save-eng-lib").click(function () {
                            var selected = new Array();
                            $(".option-input-worksheet-eng:checked").each(function () {
                                var id = $(this).val();
                                var cate = $(this).attr("data-cate");
                                var sheet = $(this).attr("data-sheet");
                                selected.push({id: id, cate: cate, sheet: sheet});
                            });
                            if (selected.length == 0) {
                                var html = '';
                                html += 'Please select worksheets first.';
                                $(".box-title-1").html(html);
                                $("#modal-alert").css('display', 'block');
                            } else {
                                $.post(home_url + "/?r=ajax/add_my_library", {
                                    data: selected}, function (data) {
                                    if (data !== "0") {
                                        var html = '';
                                        html += 'Worksheets successfully saved to <span style="font-style:italic;">My Library</span>.';
                                        $(".box-title-1").html(html);
                                        $("#modal-alert").css('display', 'block');
                                        $("#public-lib").removeClass("active in");
                                        $("#my-library").addClass("active in");
                                        $(".pub-lib-li").removeClass("active");
                                        $(".my-lib-li").addClass("active");
                                        var text = '';
                                        var table = "table-library";
                                        get_my_library(text, table);
                                    } else {
                                        var html = '';
                                        html += 'Fail!';
                                        $(".box-title-1").html(html);
                                        $("#modal-alert").css('display', 'block');
                                    }
                                });
                            }
                        });
                        $("#no-delete").live("click", function () {
                            $("#modal-alert-yesno").css('display', 'none');
                            $("#yes-delete").attr("data-del", "");
                            $("#yes-delete").attr("data-ws", "");
                            $("#yes-delete").attr("data-lesson", "");
                            $("#yes-delete").attr("data-lsheet", "");
                            $("#yes-delete").attr("data-sublid", "");
                            $("#yes-delete").attr("data-subid", "");
                            $("#yes-delete").attr("data-lessonid", "");
                            $("#yes-delete").attr("data-rowlesson", "");
                        });
                        $("#yes-delete").live("click", function () {
                            $("#modal-alert-yesno").css('display', 'none');
                            var id = $("#yes-delete").attr("data-del");
                            var sid = $("#yes-delete").attr("data-ws");
                            var lid = $("#yes-delete").attr("data-lesson");
                            var slid = $("#yes-delete").attr("data-lsheet");
                            var lesson_id = $("#yes-delete").attr("data-lessonid");
                            var rowlesson = $("#yes-delete").attr("data-rowlesson");
                            var item = $("#yes-delete").attr("data-item");
                            var sublid = $("#yes-delete").attr("data-sublid");
                            var subid = $("#yes-delete").attr("data-subid");
                            var subject_id = $("#yes-delete").attr("data-subject");
                            if (id !== null) {
                                $.post(home_url + "/?r=ajax/delete_my_library", {data: id}, function (data) {
                                    if (data == "1") {
                                        var text = '';
                                        var table = "table-library";
                                        get_my_library(text, table);
                                    }
                                });
                            }
                            if (sid !== null) {
                                $("#" + sid + "").remove();
                            }
                            if (rowlesson !== null) {
                                $("#" + rowlesson + "").remove();
                            }
                            if (lid !== null) {
                                $.post(home_url + "/?r=ajax/delete_my_lesson", {data: lid}, function (data) {
                                    if (data == "1") {
                                        var text = '';
                                        $("#table-my-lesson").html('');
                                        var table = "table-my-lesson";
                                        get_my_lesson(text, table);
                                    }
                                });
                            }
                            if (slid !== null && item == null) {
                                $.post(home_url + "/?r=ajax/delete_my_lesson", {slid: slid}, function (data) {
                                    if (data == "1") {
                                        $.post(home_url + "/?r=ajax/get_my_lesson_sheet", {data: lesson_id}, function (data1) {
                                            $('#lesson-tr' + lesson_id + '').nextAll(".detail-tr-lesson").not(".desc-worksheet").remove();
                                            if (data1 !== "0") {
                                                var dt = JSON.parse(data1);
                                                if (dt.sheet.length > 0) {
                                                    var check = $('#lesson-tr' + lesson_id + '').next().hasClass("detail-tr-lesson");
                                                    var html = '';
                                                    $.each(dt.sheet, function (i, v) {
                                                        html += '<tr class="detail-worksheet detail-tr-1 detail-tr-lesson" id="detail-sheet-' + v.sheet_id + '"><td></td>';
                                                        html += '<td></td>';
                                                        html += '<td class="table-img-icon">';
                                                        html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Worksheet.png" class="" alt="lesson" style="margin-bottom: 2px;">' + v.sheet_name + '</td>';
                                                        html += ' <td class="table-img-icon">';
                                                        html += '<div class="delete-worksheet" data-slid="' + v.id + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Delete_worksheet.png" alt="lesson" ></div>';
                                                        html += ' <div class="detail-worksheet-ls" data-id="' + v.sheet_id + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Detail_1.png" class="" alt="lesson" ></div>';
                                                        html += ' <div class="down-worksheet" data-slid="' + v.id + '" data-lid="' + v.lesson_id + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Move_down.png" class="" alt="lesson" ></div>';
                                                        html += ' <div class="up-worksheet" data-slid="' + v.id + '" data-lid="' + v.lesson_id + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Move_up.png" class="" alt="lesson" ></div>';
                                                        html += ' </td>';
                                                        html += '</tr>';
                                                    });
                                                    $('#lesson-tr' + lesson_id + '').find(".open-arrow img").attr("src", "<?php echo get_template_directory_uri(); ?>/library/images/icon_Arrow_Close.png");
                                                    $('.desc-worksheet').after(html);
                                                    $(".detail-tr-lesson td").hide().slideDown("slow");
                                                }
                                            }
                                        });
                                    }
                                });
                            }
                            if (slid !== null && item == "subject") {
                                $.post(home_url + "/?r=ajax/delete_my_lesson", {slid: slid}, function (data) {
                                    var html = '';
                                    if (data == "1") {
                                        $('#tr-lesson-' + lesson_id + '').nextAll(".detail-worksheet").remove();
                                        $.post(home_url + "/?r=ajax/get_my_lesson_sheet", {data: slid}, function (data) {
                                            if (data !== "0") {
                                                var data = JSON.parse(data);
                                                if (data.sheet.length > 0) {
                                                    $.each(data.sheet, function (i, v) {
                                                        html += '<tr class="detail-worksheet detail-tr-1 detail-tr-lesson tr-lesson" id="tr-lesson-' + v.sheet_id + '"><td></td>';
                                                        html += '<td></td>';
                                                        html += '<td class="table-img-icon" style="padding-left: 65px !important;">';
                                                        html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Worksheet.png" class="" alt="lesson" style="margin-bottom: 2px;">' + v.sheet_name + '</td>';
                                                        html += ' <td class="table-img-icon">';
                                                        html += '<div class="delete-worksheet" data-slid="' + v.id + '" data-lid2="' + v.lesson_id + '" data-item="subject"><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Delete_worksheet.png" alt="lesson" ></div>';
                                                        html += ' <div class="detail-worksheet-ls" data-id="' + v.sheet_id + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Detail_1.png" class="" alt="lesson" ></div>';
                                                        html += ' <div class="down-worksheet" data-slid="' + v.id + '" data-lid="' + v.lesson_id + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Move_down.png" class="" alt="lesson" ></div>';
                                                        html += ' <div class="up-worksheet" data-slid="' + v.id + '" data-lid="' + v.lesson_id + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Move_up.png" class="" alt="lesson" ></div>';
                                                        html += ' </td>';
                                                        html += '</tr>';
                                                    });
                                                    $('#tr-lesson-' + lesson_id + '').after(html);
                                                    $(".detail-worksheet td").hide().slideDown("slow");
                                                }
                                            }
                                        });
                                    }
                                });
                            }
                            if (subid !== null) {
                                $.post(home_url + "/?r=ajax/delete_lesson_subject", {subid: subid}, function (data) {
                                    if (data == "1") {
                                        var search = '';
                                        get_my_subject(search);
                                    }
                                });
                            }
                            if (sublid !== null) {
                                $.post(home_url + "/?r=ajax/delete_lesson_subject", {sublid: sublid}, function (data) {
                                    if (data == "1") {
                                        $('#subj-tr' + subject_id + '').nextAll(".detail-lesson").remove();
                                        $.post(home_url + "/?r=ajax/get_subject_lesson", {subid: subject_id}, function (data2) {
                                            var data3 = JSON.parse(data2);
                                            var html = '';
                                            if (data3 !== null) {
                                                if (data3.length > 0) {
                                                    $.each(data3, function (i, v) {
                                                        html += '<tr class="detail-lesson detail-tr-1 detail-tr-lesson tr-lesson" id="tr-lesson-' + v.lesson_id + '" ><td></td>';
                                                        html += '<td></td>';
                                                        html += '<td class="table-img-icon lst-worksheet-lesson" style=" color:#ce851f !important; cursor:pointer;  " data-lid="' + v.lesson_id + '">';
                                                        html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Lesson.png" alt="lesson" style="margin-bottom: 2px;" >' + v.lesson + '</td>';
                                                        html += ' <td class="table-img-icon">';
                                                        html += '<div class="delete-subject" data-sublid="' + v.id + '" data-subject="' + v.subject_id + '" ><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Delete_worksheet.png" alt="lesson" ></div>';
                                                        html += ' <div class="detail-desc-lesson" data-id="' + v.lesson_id + '" data-desc="' + v.lesson_desc + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Detail_1.png" class="" alt="lesson" ></div>';
                                                        html += ' <div class="down-worksheet" data-slid="' + v.id + '" data-lid="' + v.lesson_id + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Move_down.png" class="" alt="lesson" ></div>';
                                                        html += ' <div class="up-worksheet" data-slid="' + v.id + '" data-lid="' + v.lesson_id + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Move_up.png" class="" alt="lesson" ></div>';
                                                        html += ' </td>';
                                                        html += '</tr>';
                                                    });
                                                    $('#subj-tr' + subject_id + '').after(html);
                                                    $(".detail-lesson td").hide().slideDown("slow");
                                                }
                                            }
                                        });
                                    }
                                });
                            }
                        });
                        $(".delete-worksheet").live("click", function () {
                            var tthis = $(this);
                            var id = tthis.attr("data-id");
                            var lid = tthis.attr("data-lid");
                            var slid = tthis.attr("data-slid");
                            var lid2 = tthis.attr("data-lid2");
                            var item = tthis.attr("data-item");
                            var html = '';
                            $("#yes-delete").attr("data-del", id);
                            $("#yes-delete").attr("data-lesson", lid);
                            $("#yes-delete").attr("data-lsheet", slid);
                            $("#yes-delete").attr("data-lessonid", lid2);
                            $("#yes-delete").attr("data-item", item);
                            html += 'Are you sure you want to delete it?';
                            $("#modal-alert-yesno .box-title-1").html(html);
                            $("#modal-alert-yesno").css('display', 'block');
                        });
                        // get all worksheets function,used to load data for Public Libary
                        function getWorksheets(lang, sheet_name, group_name, assignment_id, homework_types, grade, trivia_exclusive, active, cat_level, level, sublevel, type, page) {
                            var tbody_math = $("#table-math-worksheet");
                            var tbody_english = $("#table-eng-worksheet");
                            tbody_math.html("");
                            tbody_english.html("");
                            $.get(home_url + "/?r=ajax/get_worksheets", {lang: lang, sheet_name: sheet_name, group_name: group_name, assignment_id: assignment_id, homework_types: homework_types, grade: grade, trivia_exclusive: trivia_exclusive, active: active, cat_level: cat_level, level: level, sublevel: sublevel, type: type, page: page}, function (data) {
                                data = JSON.parse(data);
                                if (type == '' || type == 'english') {
                                    $("#pagination-result").html(data.paginate);
                                    if (data.english.length > 0) {
                                        $.each(data.english, function (i, v) {
                                            var html = '';
                                            html += '<tr id="tr-lesson-' + v.id + '" class="tr-lesson">';
                                            html += ' <td >';
                                            html += ' <div class="cb-type2">';
                                            html += '<label>';
                                            html += '<input type="checkbox" class="radio_buttons class_cb_search option-input-2 option-input-worksheet option-input-worksheet-eng radio" name="cid[]" value="' + v.id + '" data-cate="' + v.cate_id + '" data-sheet="' + v.sheet_name + '" data-desc="' + v.description + '">';
                                            html += ' </label>';
                                            html += '  </div>';
                                            html += ' </td>';
                                            html += '<td>' + v.assignment + '</td>';
                                            html += '<td class="table-img-icon">';
                                            html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Worksheet.png" class="" alt="lesson" > ' + v.sheet_name + '</td>';
                                            html += '<td>' + v.name + '</td>';
                                            html += '<td class="table-img-icon">';
                                            html += '<div class="search-worksheet-tr"><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Magnifiy.png" class="" alt="lesson" ></div>';
                                            html += '<div class="detail-worksheet-pub" data-sid="' + v.id + '" ><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Detail_1.png" class="" alt="lesson" ></div>';
                                            html += '</td>';
                                            html += '</tr>';
                                            tbody_english.append(html);
                                        });
                                        $(".view-assign-homework").click(function () {
                                            var tthis = $(this);
                                            var sheet_name = tthis.attr("data-sheet-name");
                                            var sheet_id = tthis.attr("data-sheet-id");
                                            $('#assign-homework-worksheet-name').html(sheet_name);
                                            $('#input-assign-homework-worksheet-id').val(sheet_id);
                                        });
                                        for (var i = data.english.length; i < 11; i++) {
                                            var add_tr_e = '<tr><td>&nbsp</td><td></td><td></td><td></td><td></td><td></td></tr>';
                                            tbody_english.append(add_tr_e);
                                        }
                                    } else {
                                        var tr_e = '<tr><td colspan="6">No results.</td></tr>';
                                        tbody_english.append(tr_e);
                                    }
                                }
                                if (type == '' || type == 'math') {
                                    $("#pagination-result-math").html(data.paginate_math);
                                    if (data.math.length > 0) {

                                        $.each(data.math, function (i, v) {
                                            var html = '';
                                            html += '<tr id="tr-lesson-' + v.id + '" class="tr-lesson">';
                                            html += ' <td >';
                                            html += ' <div class="cb-type2">';
                                            html += '<label>';
                                            html += '<input type="checkbox" class="radio_buttons class_cb_search option-input-2 option-input-worksheet option-input-worksheet-math radio" name="select-tr" value="' + v.id + '" data-cate="' + v.cate_id + '"  data-sheet="' + v.sheet_name + '" data-desc="' + v.description + '" />';
                                            html += '</label>';
                                            html += ' </div>';
                                            html += ' </td>';
                                            html += '<td>' + v.level_category_name + '</td>';
                                            html += '<td class="table-img-icon">';
                                            html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Worksheet.png" class="" alt="lesson" > ' + v.sheet_name + '</td>';
                                            html += '<td class="table-img-icon">';
                                            html += '<div class="search-worksheet-tr"><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Magnifiy.png" class="" alt="lesson" ></div>';
                                            html += '<div class="detail-worksheet-pub" data-sid="' + v.id + '" ><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Detail_1.png" class="" alt="lesson" ></div>';
                                            html += '</td>';
                                            html += '</tr>';
                                            tbody_math.append(html);
                                        });
                                        $(".view-assign-homework").click(function () {
                                            var tthis = $(this);
                                            var sheet_name = tthis.attr("data-sheet-name");
                                            var sheet_id = tthis.attr("data-sheet-id");
                                            $('#assign-homework-worksheet-name').html(sheet_name);
                                            $('#input-assign-homework-worksheet-id').val(sheet_id);
                                        });
                                        for (var i = data.math.length; i < 11; i++) {
                                            var add_tr_m = '<tr><td>&nbsp</td><td></td><td></td><td></td><td></td><td></td></tr>';
                                            tbody_math.append(add_tr_m);
                                        }
                                    } else {
                                        var tr_m = '<tr><td colspan="6">No results</td></tr>';
                                        tbody_math.append(tr_m);
                                    }
                                }
                            });
                        }
                        function getWorksheets2(lang, sheet_name, group_name, assignment_id, homework_types, grade, trivia_exclusive, active, cat_level, level, sublevel, type, page) {
                            var tbody_math = $(".table-math-worksheet");
                            var tbody_english = $(".table-eng-worksheet");
                            tbody_math.html("");
                            tbody_english.html("");
                            $.get(home_url + "/?r=ajax/get_worksheets", {lang: lang, sheet_name: sheet_name, group_name: group_name, assignment_id: assignment_id, homework_types: homework_types, grade: grade, trivia_exclusive: trivia_exclusive, active: active, cat_level: cat_level, level: level, sublevel: sublevel, type: type, page: page, number: "2"}, function (data) {
                                data = JSON.parse(data);
                                if (type == '' || type == 'english') {
                                    if (data.english.length > 0) {
                                        $("#pagination-result2").html(data.paginate);
                                        $("#pagination-result3").html(data.paginate);
                                        $.each(data.english, function (i, v) {
                                            var html = '';
                                            html += '<tr id="tr-lesson-' + v.id + '" class="tr-lesson">';
                                            html += ' <td >';
                                            html += ' <div class="cb-type2">';
                                            html += '<label>';
                                            html += '<input type="checkbox" class="radio_buttons class_cb_search option-input-2 option-input-worksheet option-input-worksheet-eng radio" name="cid[]" value="' + v.id + '" data-cate="' + v.cate_id + '" data-sheet="' + v.sheet_name + '" data-desc="' + v.description + '">';
                                            html += ' </label>';
                                            html += '  </div>';
                                            html += ' </td>';
                                            html += '<td>' + v.assignment + '</td>';
                                            html += '<td class="table-img-icon">';
                                            html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Worksheet.png" class="" alt="lesson" > ' + v.sheet_name + '</td>';
                                            html += '<td>' + v.name + '</td>';
                                            html += '<td class="table-img-icon">';
                                            html += '<div class="search-worksheet-tr"><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Magnifiy.png" class="" alt="lesson" ></div>';
                                            html += '<div class="detail-worksheet-pub" data-sid="' + v.id + '" ><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Detail_1.png" class="" alt="lesson" ></div>';
                                            html += '</td>';
                                            html += '</tr>';
                                            tbody_english.append(html);
                                        });
                                        $(".view-assign-homework").click(function () {
                                            var tthis = $(this);
                                            var sheet_name = tthis.attr("data-sheet-name");
                                            var sheet_id = tthis.attr("data-sheet-id");
                                            $('#assign-homework-worksheet-name').html(sheet_name);
                                            $('#input-assign-homework-worksheet-id').val(sheet_id);
                                        });
                                        for (var i = data.english.length; i < 11; i++) {
                                            var add_tr_e = '<tr><td>&nbsp</td><td></td><td></td><td></td><td></td><td></td></tr>';
                                            tbody_english.append(add_tr_e);
                                        }
                                    } else {
                                        var tr_e = '<tr><td colspan="6">No results.</td></tr>';
                                        tbody_english.append(tr_e);
                                    }
                                }
                                if (type == '' || type == 'math') {
                                    if (data.math.length > 0) {
                                        $("#pagination-result-math2").html(data.paginate_math);
                                        $("#pagination-result-math3").html(data.paginate_math);
                                        $.each(data.math, function (i, v) {
                                            var html = '';
                                            html += '<tr id="tr-lesson-' + v.id + '" class="tr-lesson">';
                                            html += ' <td >';
                                            html += ' <div class="cb-type2">';
                                            html += '<label>';
                                            html += '<input type="checkbox" class="radio_buttons class_cb_search option-input-2 option-input-worksheet option-input-worksheet-math radio" name="select-tr" value="' + v.id + '" data-cate="' + v.cate_id + '"  data-sheet="' + v.sheet_name + '" data-desc="' + v.description + '" />';
                                            html += '</label>';
                                            html += ' </div>';
                                            html += ' </td>';
                                            html += '<td>' + v.level_category_name + '</td>';
                                            html += '<td class="table-img-icon">';
                                            html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Worksheet.png" class="" alt="lesson" > ' + v.sheet_name + '</td>';
                                            html += '<td class="table-img-icon">';
                                            html += '<div class="search-worksheet-tr"><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Magnifiy.png" class="" alt="lesson" ></div>';
                                            html += '<div class="detail-worksheet-pub" data-sid="' + v.id + '" ><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Detail_1.png" class="" alt="lesson" ></div>';
                                            html += '</td>';
                                            html += '</tr>';
                                            tbody_math.append(html);
                                        });
                                        $(".view-assign-homework").click(function () {
                                            var tthis = $(this);
                                            var sheet_name = tthis.attr("data-sheet-name");
                                            var sheet_id = tthis.attr("data-sheet-id");
                                            $('#assign-homework-worksheet-name').html(sheet_name);
                                            $('#input-assign-homework-worksheet-id').val(sheet_id);
                                        });
                                        for (var i = data.math.length; i < 11; i++) {
                                            var add_tr_m = '<tr><td>&nbsp</td><td></td><td></td><td></td><td></td><td></td></tr>';
                                            tbody_math.append(add_tr_m);
                                        }
                                    } else {
                                        var tr_m = '<tr><td colspan="6">No results</td></tr>';
                                        tbody_math.append(tr_m);
                                    }
                                }
                            });
                        }
                        //show description of worksheet
                        $(".detail-worksheet-ic").live("click", function () {
                            var id = $(this).attr("data-sid");
                            var open = $("#tr-lesson-" + id + "").next().hasClass("desc-sheet");
                            $(".tr-lesson").not("#tr-lesson-" + id + "").nextAll(".desc-sheet").remove();
                            if (open == false) {
                                $.get(home_url + "/?r=ajax/get_desc_worksheet", {data: id}, function (data1) {
                                    if (data1 !== "0") {
                                        var data = JSON.parse(data1);
                                        if (data.sheet.length > 0) {
                                            var html = '';
                                            $.each(data.sheet, function (i, v) {
                                                html += '<tr class="desc-sheet detail-tr-lesson desc-worksheet-' + v.id + '">';
                                                html += '<td></td>';
                                                html += '<td></td>';
                                                html += '<td colspan="2" class="input-detail-lib">';
                                                if (v.description == "")
                                                {
                                                    html += '<div  class="desc-detail-sheet" id="detaildesc' + v.id + '">&nbsp;</div>';
                                                } else
                                                {
                                                    html += '<div  class="desc-detail-sheet" id="detaildesc' + v.id + '">' + v.description + '</div>';
                                                }
                                                html += '<textarea rows="4" id="detailinput' + v.id + '" class="hidden" name="input-detail" >' + v.description + '</textarea>';
                                                html += '<div class="table-img-icon edit-desc-worksheet" onclick="edit_desc(' + v.id + ')" id="editdesc' + v.id + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Edit.png" class="" alt="lesson" ></div>';
                                                html += '<div class="table-img-icon undo-desc" data-lid="' + v.id + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Undo.png" class="" alt="lesson" ></div>';
                                                html += '<div class="hidden table-img-icon edit-desc-worksheet edit-success-ws"  id="editsuccess' + v.id + '" data-sid="' + v.id + '" ><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Edit_Done.png" class="" alt="lesson" ></div>';
                                                html += ' </td>';
                                                html += '</tr>';
                                            });
                                            $("#tr-lesson-" + id + "").after(html);
                                            $(".desc-sheet td").hide().slideDown("slow");
                                        }
                                    }
                                });
                            } else {
                                $(".desc-sheet td").slideUp("slow", function () {
                                    $("#tr-lesson-" + id + "").nextAll(".desc-sheet").remove();
                                });
                            }
                        });
                        //show description of worksheet in Public Library
                        $(".detail-worksheet-pub").live("click", function () {
                            var id = $(this).attr("data-sid");
                            var open = $("#tr-lesson-" + id + "").next().hasClass("desc-sheet");
                            if (open == false) {
                                $(".desc-sheet").not("#tr-lesson-" + id + "").slideUp("slow");
                                $(".tr-lesson").not("#tr-lesson-" + id + "").next(".desc-sheet-ls").remove();
                                $.get(home_url + "/?r=ajax/get_desc_worksheet", {data: id}, function (data) {
                                    data = JSON.parse(data);
                                    if (data.sheet.length > 0) {
                                        var html = '';
                                        $.each(data.sheet, function (i, v) {
                                            html += '<tr class="desc-sheet detail-tr-lesson desc-worksheet-' + v.id + '">';
                                            html += '<td></td>';
                                            html += '<td></td>';
                                            html += '<td colspan="3" class="input-detail-pub">';
                                            html += '<div class="desc-detail-sheet " id="detaildesc' + v.id + '">' + v.description + '</div>';

                                            html += ' </td>';
                                            html += '</tr>';
                                        });
                                        $("#tr-lesson-" + id + "").after(html);
                                        $(".desc-sheet td").hide().slideDown("slow");
                                    }
                                });
                            } else {
                                $(".desc-sheet td").slideUp("slow", function () {
                                    $("#tr-lesson-" + id + "").next(".desc-sheet").remove();
                                });
                            }
                        });
                        //show description of worksheet in My Lesson
                        $(".detail-worksheet-ls").live("click", function () {
                            var id = $(this).attr("data-id");
                            var open = $("#tr-lesson-" + id + "").next().hasClass("desc-sheet-ls");
                            if (open == false) {
                                $(".detail-worksheet").not("#tr-lesson-" + id + "").nextAll(".desc-sheet-ls").remove();
                                $.get(home_url + "/?r=ajax/get_desc_worksheet", {data: id}, function (data1) {
                                    var data = JSON.parse(data1);
                                    if (data.sheet.length > 0) {
                                        var html = '';
                                        $.each(data.sheet, function (i, v) {
                                            html += '<tr class="desc-sheet-ls detail-tr-lesson desc-worksheet-' + v.id + '">';
                                            html += '<td></td>';
                                            html += '<td></td>';
                                            html += '<td colspan="2" class="input-detail-ls lesson-detail-1 " >';
                                            if (v.description == "")
                                            {
                                                html += '<div class="desc-detail detail-ws-style" id="detaildesc' + v.id + '">&nbsp;</div>';
                                            } else
                                            {
                                                html += '<div class="desc-detail detail-ws-style" id="detaildesc' + v.id + '">' + v.description + '</div>';
                                            }
                                            html += '<textarea rows="4" id="detailinput' + v.id + '" class="hidden" name="input-detail" >' + v.description + '</textarea>';
                                            html += '<div class="table-img-icon edit-desc-worksheet" onclick="edit_desc(' + v.id + ')" id="editdesc' + v.id + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Edit.png" class="" alt="lesson" ></div>';
                                            html += '<div class="table-img-icon undo-desc" data-lid="' + v.id + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Undo.png" class="" alt="lesson" ></div>';
                                            html += '<div class="hidden table-img-icon edit-desc-worksheet edit-success"  id="editsuccess' + v.id + '" data-sid="' + v.id + '" ><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Edit_Done.png" class="" alt="lesson" ></div>';
                                            html += ' </td>';
                                            html += '</tr>';
                                        });
                                        $("#tr-lesson-" + id + "").after(html);
                                        $(".desc-sheet-ls td").hide().slideDown("slow");
                                    }
                                });
                            } else {
                                $(".desc-sheet-ls td").slideUp("fast", function () {
                                    $("#tr-lesson-" + id + "").nextAll(".desc-sheet-ls").remove();
                                });
                            }
                        });
                        //show description of worksheet in My Subject
                        $(".detail-worksheet-ls-sub").live("click", function () {
                            var id = $(this).attr("data-id");
                            var open = $("#tr-lesson-" + id + "").next().hasClass("desc-sheet-ls");
                            if (open == false) {
                                $(".detail-worksheet").not("#tr-lesson-" + id + "").nextAll(".desc-sheet-ls").remove();
                                $.get(home_url + "/?r=ajax/get_desc_worksheet", {data: id}, function (data1) {
                                    var data = JSON.parse(data1);
                                    if (data.sheet.length > 0) {
                                        var html = '';
                                        $.each(data.sheet, function (i, v) {
                                            html += '<tr class="desc-sheet-ls detail-tr-lesson set-hide desc-worksheet-' + v.id + '">';
                                            html += '<td></td>';
                                            html += '<td></td>';
                                            html += '<td colspan="2" class="input-detail-ls lesson-detail-1 detail-input1" >';
                                            if (v.description == "")
                                            {
                                                html += '<div class="desc-detail lesson-ws-detail detail-ws" id="detaildesc' + v.id + '">&nbsp;</div>';
                                            } else
                                            {
                                                html += '<div class="desc-detail lesson-ws-detail detail-ws" id="detaildesc' + v.id + '">' + v.description + '</div>';
                                            }
                                            html += '<textarea rows="4" id="detailinput' + v.id + '" class="hidden" name="input-detail" >' + v.description + '</textarea>';
                                            html += '<div class="table-img-icon edit-desc-worksheet" onclick="edit_desc(' + v.id + ')" id="editdesc' + v.id + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Edit.png" class="" alt="lesson" ></div>';
                                            html += '<div class="table-img-icon undo-desc" data-lid="' + v.id + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Undo.png" class="" alt="lesson" ></div>';
                                            html += '<div class="hidden table-img-icon edit-desc-worksheet edit-success-sheet-sub"  id="editsuccess' + v.id + '" data-sid="' + v.id + '" ><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Edit_Done.png" class="" alt="lesson" ></div>';
                                            html += ' </td>';
                                            html += '</tr>';
                                        });
                                        $("#tr-lesson-" + id + "").after(html);
                                        $(".desc-sheet-ls td").hide().slideDown("slow");
                                    }
                                });
                            } else {
                                $(".desc-sheet-ls td").slideUp("fast", function () {
                                    $("#tr-lesson-" + id + "").nextAll(".desc-sheet-ls").remove();
                                });
                            }
                        });
                        $(".detail-worksheet-ready").live("click", function () {
                            var id = $(this).attr("data-id");
                            var open = $("#tr-lesson-" + id + "").next().hasClass("desc-sheet-ls");
                            if (open == false) {
                                $(".desc-sheet-ls").not("#tr-lesson-" + id + "").slideUp("slow", function () {
                                    $(".tr-lesson").not("#tr-lesson-" + id + "").nextAll(".desc-sheet-ls").remove();
                                });
                                $.get(home_url + "/?r=ajax/get_desc_worksheet", {data: id}, function (data1) {
                                    var data = JSON.parse(data1);
                                    if (data.sheet.length > 0) {
                                        var html = '';
                                        $.each(data.sheet, function (i, v) {
                                            html += '<tr class="desc-sheet-ls detail-tr-lesson desc-worksheet-' + v.id + '">';
                                            html += '<td></td>';
                                            html += '<td></td>';
                                            html += '<td colspan="2" class="input-detail lesson-detail-1">';
                                            html += '<div class="desc-detail lesson-detail" id="detaildesc' + v.id + '">' + v.description + '</div><textarea rows="4" id="detailinput' + v.id + '" class="hidden" name="input-detail" >' + v.description + '</textarea>';
                                            html += '<div class="table-img-icon edit-desc-worksheet" onclick="edit_desc(' + v.id + ')" id="editdesc' + v.id + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Edit.png" class="" alt="lesson" ></div>';
                                            html += '<div class="table-img-icon undo-desc" data-lid="' + v.id + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Undo.png" class="" alt="lesson" ></div>';
                                            html += '<div class="hidden table-img-icon edit-desc-worksheet edit-success"  id="editsuccess' + v.id + '" data-sid="' + v.id + '" ><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Edit_Done.png" class="" alt="lesson" ></div>';
                                            html += ' </td>';
                                            html += '</tr>';
                                        });
                                        $("#tr-lesson-" + id + "").after(html);
                                        $(".desc-sheet-ls td").hide().slideDown("slow");
                                    }
                                });
                            } else {
                                $(".desc-sheet-ls td").slideUp("slow", function () {
                                    $("#tr-lesson-" + id + "").nextAll(".desc-sheet-ls").remove();
                                });
                            }
                        });
                        //search English Worksheet in Public Libary
                        $("#btn-search-eng").click(function () {
                            var assignment_id = $('#filter-assignment-enSelectBoxItText').attr('data-val');
                            var cat_level = '';
                            var homework_types = '';
                            var level = '';
                            var sublevel = '';
                            var lang = '';
                            var sheet_name = $('#search-tit-eng').val();
                            var group_name = '';
                            var grade = $('#grade-enSelectBoxItText').attr('data-val');
                            var trivia_exclusive = '';
                            var active = '';
                            var type = '';
                            getWorksheets(lang, sheet_name, group_name, assignment_id, homework_types, grade, trivia_exclusive, active, cat_level, level, sublevel, type);
                        });
                        $("#btn-search-eng2").click(function () {
                            var assignment_id = $('#filter-assignment-en2SelectBoxItText').attr('data-val');
                            var cat_level = '';
                            var homework_types = '';
                            var level = '';
                            var sublevel = '';
                            var lang = '';
                            var sheet_name = $('#search-tit-eng2').val();
                            var group_name = '';
                            var grade = $('#grade-en2SelectBoxItText').attr('data-val');
                            var trivia_exclusive = '';
                            var active = '';
                            var type = '';
                            getWorksheets2(lang, sheet_name, group_name, assignment_id, homework_types, grade, trivia_exclusive, active, cat_level, level, sublevel, type);
                        });
                        $("#btn-search-math").click(function () {
                            var assignment_id = $('#worksheet-formatSelectBoxItText').attr('data-val');
                            var cat_level = $('#filter-level-categoriesSelectBoxItText').attr('data-val');
                            var homework_types = '';
                            var level = $('#filter-levelsSelectBoxItText').attr('data-val');
                            var sublevel = $('#filter-sublevelsSelectBoxItText').attr('data-val');
                            var lang = '';
                            var sheet_name = $('#filter-sheet-name').val();
                            var group_name = '';
                            var grade = '';
                            var trivia_exclusive = '';
                            var active = '';
                            var type = '';
                            getWorksheets(lang, sheet_name, group_name, assignment_id, homework_types, grade, trivia_exclusive, active, cat_level, level, sublevel, type);
                        });
                        $("#btn-search-math2").click(function () {
                            var assignment_id = $('#worksheet-format2SelectBoxItText').attr('data-val');
                            var cat_level = $('#filter-level-categories2SelectBoxItText').attr('data-val');
                            var homework_types = '';
                            var level = $('#filter-levels2SelectBoxItText').attr('data-val');
                            var sublevel = $('#filter-sublevels2SelectBoxItText').attr('data-val');
                            var lang = '';
                            var sheet_name = $('#filter-sheet-name2').val();
                            var group_name = '';
                            var grade = '';
                            var trivia_exclusive = '';
                            var active = '';
                            var type = '';
                            getWorksheets2(lang, sheet_name, group_name, assignment_id, homework_types, grade, trivia_exclusive, active, cat_level, level, sublevel, type);
                        });
                        $("#btn-search-math3").click(function () {
                            var assignment_id = $('#worksheet-format3SelectBoxItText').attr('data-val');
                            var cat_level = $('#filter-level-categories3SelectBoxItText').attr('data-val');
                            var homework_types = '';
                            var level = $('#filter-levels3SelectBoxItText').attr('data-val');
                            var sublevel = $('#filter-sublevels3SelectBoxItText').attr('data-val');
                            var lang = '';
                            var sheet_name = $('#filter-sheet-name3').val();
                            var group_name = '';
                            var grade = '';
                            var trivia_exclusive = '';
                            var active = '';
                            var type = '';
                            getWorksheets2(lang, sheet_name, group_name, assignment_id, homework_types, grade, trivia_exclusive, active, cat_level, level, sublevel, type);
                        });
                        $("#btn-search-eng3").click(function () {
                            var assignment_id = $('#filter-assignment-en3SelectBoxItText').attr('data-val');
                            var cat_level = '';
                            var homework_types = '';
                            var level = '';
                            var sublevel = '';
                            var lang = '';
                            var sheet_name = $('#search-tit-eng3').val();
                            var group_name = '';
                            var grade = $('#grade-en3SelectBoxItText').attr('data-val');
                            var trivia_exclusive = '';
                            var active = '';
                            var type = '';
                            getWorksheets2(lang, sheet_name, group_name, assignment_id, homework_types, grade, trivia_exclusive, active, cat_level, level, sublevel, type);
                        });
                        $("#search-btn").click(function () {
                            var search = $("#search-lesson").val();
                            var tbody_my_lesson = "table-my-lesson";
                            get_my_lesson(search, tbody_my_lesson);
                        });
                        //get my library function
                        function get_my_library(text, table) {
                            var tbody_my_library = $("#" + table + "");
                            tbody_my_library.html("");
                            $.post(home_url + "/?r=ajax/get_my_library", {data: text}, function (data) {
                                if (data !== "0") {
                                    data = JSON.parse(data);
                                    if (data.library.length > 0) {
                                        $.each(data.library, function (i, v) {
                                            var html = '';
                                            html += '<tr id="tr-lesson-' + v.sheet_id + '" class="tr-lesson">';
                                            html += ' <td >';
                                            html += '<div class="cb-type2">';
                                            html += '<label>';
                                            html += ' <input type="checkbox" class="radio_buttons class_cb_search option-input-2 option-input-lesson option-input-mylibrary radio" value="' + v.name + '" data-id="' + v.sheet_id + '" name="select-tr"/>';
                                            html += '</label>';
                                            html += '</div>';
                                            html += '</td>';
                                            html += '<td class="table-img-icon">';
                                            if (v.cate == 1) {
                                                html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_ENGLISH.png" alt="lesson" >';
                                            } else if (v.cate == 5) {
                                                html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_MATH.png" alt="lesson" >';
                                            } else if (v.cate == 2) {
                                                html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/Type_Science.png" alt="lesson" >';
                                            } else if (v.cate == 3) {
                                                html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/Type_History.png" alt="lesson" >';
                                            } else if (v.cate == 4) {
                                                html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/Type_Art_n_Design.png" alt="lesson" >';
                                            } else if (v.cate == 6) {
                                                html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/Type_Music.png" alt="lesson" >';
                                            } else if (v.cate == 7) {
                                                html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/Type_Others.png" alt="lesson" >';
                                            }
                                            html += v.assignment + '</td>';
                                            html += '<td class="table-img-icon">';
                                            html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Worksheet.png" class="" alt="lesson" >';
                                            html += '<span class="name-lesson-2">' + v.name + '</span>';
                                            html += '</td>';
                                            html += '<td class="table-img-icon">';
                                            html += '<div class="delete-worksheet" data-id="' + v.id + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Delete_worksheet.png" alt="lesson" ></div>';
                                            html += '<div class="magnifiy-worksheet-ic"><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Magnifiy.png" class="" alt="lesson" ></div>';
                                            html += '<div class="detail-worksheet-ic" data-sid="' + v.sheet_id + '" ><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Detail_1.png" class="" alt="lesson" ></div>';
                                            html += '</td>';
                                            html += '</tr>';
                                            tbody_my_library.append(html);
                                        });
                                    }
                                } else {
                                    var html = '';
                                    html += '<tr><td colspan="4">No results.</td>';
                                    html += '</tr>';
                                    tbody_my_library.append(html);
                                }
                            });
                        }
                        //update description of worksheet
                        $(".edit-success").live("click", function () {
                            var sid = $(this).attr("data-sid");
                            var text = $("#detailinput" + sid).val();
                            $.post(home_url + "/?r=ajax/update_desc_worksheet", {desc: text, sid: sid}, function (data) {
                                $("#tr-lesson-" + sid + "").nextAll(".desc-sheet-ls").remove();
                                $.get(home_url + "/?r=ajax/get_desc_worksheet", {data: sid}, function (data1) {
                                    var data = JSON.parse(data1);
                                    if (data.sheet.length > 0) {
                                        var html = '';
                                        $.each(data.sheet, function (i, v) {
                                            html += '<tr class="desc-sheet-ls detail-tr-lesson desc-worksheet-' + v.id + '">';
                                            html += '<td></td>';
                                            html += '<td></td>';
                                            html += '<td colspan="2" class="input-detail-ls lesson-detail-1">';
                                            html += '<div class="desc-detail detail-ws-style" id="detaildesc' + v.id + '">' + v.description + '</div><textarea rows="4" id="detailinput' + v.id + '" class="hidden" name="input-detail" >' + v.description + '</textarea>';
                                            html += '<div class="table-img-icon edit-desc-worksheet" onclick="edit_desc(' + v.id + ')" id="editdesc' + v.id + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Edit.png" class="" alt="lesson" ></div>';
                                            html += '<div class="table-img-icon undo-desc" data-lid="' + v.id + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Undo.png" class="" alt="lesson" ></div>';
                                            html += '<div class="hidden table-img-icon edit-desc-worksheet edit-success"  id="editsuccess' + v.id + '" data-sid="' + v.id + '" ><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Edit_Done.png" class="" alt="lesson" ></div>';
                                            html += ' </td>';
                                            html += '</tr>';
                                        });
                                        $("#tr-lesson-" + sid + "").after(html);
                                        $(".desc-sheet-ls td").hide().slideDown("slow");
                                    }
                                });
                                if (data == '1') {
                                }
                            });
                        });
                        //update description of worksheet
                        $(".edit-success-sheet-sub").live("click", function () {
                            var sid = $(this).attr("data-sid");
                            var text = $("#detailinput" + sid).val();
                            $.post(home_url + "/?r=ajax/update_desc_worksheet", {desc: text, sid: sid}, function (data) {
                                $("#tr-lesson-" + sid + "").nextAll(".desc-sheet-ls").remove();
                                $.get(home_url + "/?r=ajax/get_desc_worksheet", {data: sid}, function (data1) {
                                    var data = JSON.parse(data1);
                                    if (data.sheet.length > 0) {
                                        var html = '';
                                        $.each(data.sheet, function (i, v) {
                                            html += '<tr class="desc-sheet-ls detail-tr-lesson desc-worksheet-' + v.id + '">';
                                            html += '<td></td>';
                                            html += '<td></td>';
                                            html += '<td colspan="2" class="input-detail-ls lesson-detail-1 detail-input1">';
                                            html += '<div class="desc-detail lesson-ws-detail detail-ws sub-detail"  id="detaildesc' + v.id + '">' + v.description + '</div><textarea rows="4" id="detailinput' + v.id + '" class="hidden" name="input-detail" >' + v.description + '</textarea>';
                                            html += '<div class="table-img-icon edit-desc-worksheet" onclick="edit_desc(' + v.id + ')" id="editdesc' + v.id + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Edit.png" class="" alt="lesson" ></div>';
                                            html += '<div class="table-img-icon undo-desc" data-lid="' + v.id + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Undo.png" class="" alt="lesson" ></div>';
                                            html += '<div class="hidden table-img-icon edit-desc-worksheet edit-success-sheet-sub"  id="editsuccess' + v.id + '" data-sid="' + v.id + '" ><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Edit_Done.png" class="" alt="lesson" ></div>';
                                            html += ' </td>';
                                            html += '</tr>';
                                        });
                                        $("#tr-lesson-" + sid + "").after(html);
                                        $(".desc-sheet-ls td").hide().slideDown("slow");
                                    }
                                });
                                if (data == '1') {
                                }
                            });
                        });
                        //update description of worksheet
                        $(".edit-success-ws").live("click", function () {
                            var sid = $(this).attr("data-sid");
                            var text = $("#detailinput" + sid).val();
                            $.post(home_url + "/?r=ajax/update_desc_worksheet", {desc: text, sid: sid}, function (data) {
                                $("#tr-lesson-" + sid + "").nextAll(".desc-sheet").remove();
                                $.get(home_url + "/?r=ajax/get_desc_worksheet", {data: sid}, function (data1) {
                                    var data = JSON.parse(data1);
                                    if (data.sheet.length > 0) {
                                        var html = '';
                                        $.each(data.sheet, function (i, v) {
                                            html += '<tr class="desc-sheet detail-tr-lesson desc-worksheet-' + v.id + '">';
                                            html += '<td></td>';
                                            html += '<td></td>';
                                            html += '<td colspan="2" class="input-detail-lib">';
                                            html += '<div class="desc-detail-sheet" id="detaildesc' + v.id + '">' + v.description + '</div><textarea rows="4" id="detailinput' + v.id + '" class="hidden" name="input-detail" >' + v.description + '</textarea>';
                                            html += '<div class="table-img-icon edit-desc-worksheet" onclick="edit_desc(' + v.id + ')" id="editdesc' + v.id + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Edit.png" class="" alt="lesson" ></div>';
                                            html += '<div class="table-img-icon undo-desc" data-lid="' + v.id + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Undo.png" class="" alt="lesson" ></div>';
                                            html += '<div class="hidden table-img-icon edit-desc-worksheet edit-success-ws"  id="editsuccess' + v.id + '" data-sid="' + v.id + '" ><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Edit_Done.png" class="" alt="lesson" ></div>';
                                            html += ' </td>';
                                            html += '</tr>';
                                        });
                                        $("#tr-lesson-" + sid + "").after(html);
                                        $(".desc-sheet td").hide().slideDown("slow");
                                    }
                                });
                                if (data == '1') {
                                }
                            });
                        });
                        $(".edit-success-ready").live("click", function () {
                            var sid = $(this).attr("data-sid");
                            var text = $("#detailinput" + sid).val();
                            $.post(home_url + "/?r=ajax/update_desc_worksheet", {desc: text, sid: sid}, function (data) {
                                $("#tr-lesson-" + sid + "").nextAll(".desc-sheet-ls").remove();
                                $.get(home_url + "/?r=ajax/get_desc_worksheet", {data: sid}, function (data1) {
                                    var data = JSON.parse(data1);
                                    if (data.sheet.length > 0) {
                                        var html = '';
                                        $.each(data.sheet, function (i, v) {
                                            html += '<tr class="desc-sheet detail-tr-lesson desc-worksheet-' + v.id + '">';
                                            html += '<td></td>';
                                            html += '<td></td>';
                                            html += '<td colspan="3" class="input-detail">';
                                            html += '<div class="desc-detail-sheet " id="detaildesc' + v.id + '">' + v.description + '</div><textarea rows="4" id="detailinput' + v.id + '" class="hidden" name="input-detail" >' + v.description + '</textarea>';
                                            html += '<div class="table-img-icon edit-desc-worksheet" onclick="edit_desc(' + v.id + ')" id="editdesc' + v.id + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Edit.png" class="" alt="lesson" ></div>';
                                            html += '<div class="table-img-icon undo-desc" data-lid="' + v.id + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Undo.png" class="" alt="lesson" ></div>';
                                            html += '<div class="hidden table-img-icon edit-desc-worksheet edit-success-pub" id="editsuccess' + v.id + '" data-sid="' + v.id + '" ><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Edit_Done.png" class="" alt="lesson" ></div>';
                                            html += ' </td>';
                                            html += '</tr>';
                                        });
                                        $("#tr-lesson-" + sid + "").after(html);
                                        $(".desc-sheet-ls td").hide().slideDown("slow");
                                    }
                                });
                                if (data == '1') {
                                }
                            });
                        });
                        $(".edit-success-ls").live("click", function () {
                            var lid = $(this).attr("data-lid");
                            var text = $("#detailinput" + lid).val();
                            $('#lesson-tr' + lid + '').nextAll(".detail-desc").remove();
                            $.post(home_url + "/?r=ajax/update_desc_lesson", {desc: text, lid: lid}, function (data) {
                                data = JSON.parse(data);
                                var html = '';
                                $.each(data, function (i, v) {
                                    html += '<tr class="detail-desc detail-tr-lesson desc-worksheet desc-worksheet-' + v.id + '" >';
                                    html += '<td></td>';
                                    html += '<td></td>';
                                    html += '<td colspan="2" class="input-detail-lesson">';
                                    html += '<div class="desc-detail" id="detaildesc' + v.id + '">' + v.desc + '</div><textarea rows="4" id="detailinput' + v.id + '" class="hidden" name="input-detail" >' + v.desc + '</textarea>';
                                    html += '<div class="table-img-icon edit-desc-worksheet"  onclick="edit_desc(' + v.id + ')" id="editdesc' + v.id + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Edit.png" class="" alt="lesson" ></div>';
                                    html += '<div class="table-img-icon undo-desc" data-lid="' + v.id + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Undo.png" class="" alt="lesson" ></div>';
                                    html += '<div class="hidden table-img-icon edit-desc-worksheet edit-success-ls"  id="editsuccess' + v.id + '" data-lid="' + v.id + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Edit_Done.png" class="" alt="lesson" ></div>';
                                    html += ' </td>';
                                    html += '</tr>';
                                    $('#lesson-tr' + lid + '').after(html);
                                    $(".detail-tr-lesson td").hide().slideDown("slow");
                                });
                            });
                        });
                        function get_my_lesson(text, table) {
                            $.post(home_url + "/?r=ajax/get_my_lesson", {search: text}, function (data) {

                                var tbody_lesson = $("#" + table + "");
                                tbody_lesson.html('');
                                data = JSON.parse(data);
                                var html = '';
                                if (data == 0) {
                                    html += '<tr id="lesson-tr" class="lesson-tr">';
                                    html += '<td></td><td colspan="3">No results.</td></tr>';
                                } else {
                                    if (data.publesson != null) {
                                        if (data.publesson.length > 0) {
                                            $.each(data.publesson, function (i, v) {
                                                html += '<tr id="lesson-tr' + v.id + '" class="lesson-tr">';
                                                html += '<td>';
                                                html += ' <div class="cb-type2">';
                                                html += ' <label>';
                                                html += '<input type="checkbox" class="radio_buttons class_cb_search option-input-2 option-input-lesson option-input-lesson-check radio" value="' + v.id + '" data-name="' + v.name + '" name="select-tr"/>';
                                                html += ' </label>';
                                                html += '</div>';
                                                html += ' </td>';
                                                html += '<td class="table-img-icon">';
                                                if (v.cate == 1) {
                                                    html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_ENGLISH.png" alt="lesson" >';
                                                } else if (v.cate == 5) {
                                                    html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_MATH.png" alt="lesson" >';
                                                } else if (v.cate == 8) {
                                                    html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Dictionary.png" alt="lesson" >';
                                                } else if (v.cate == 9) {
                                                    html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Self-study.png" alt="lesson" >';
                                                } else if (v.cate == 10) {
                                                    html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_SAT.png" alt="lesson" >';
                                                }
                                                html += '</td>';
                                                html += '<td class="table-img-icon">';
                                                html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Ready-made_lesson.png" class="" alt="lesson" >';
                                                html += '<span class="name-ready-lesson my-public-lesson" data-lid="' + v.id + '">' + v.name + '</span>';
                                                html += ' </td>';
                                                html += '<td class="table-img-icon">';
                                                html += '<div class="delete-worksheet" data-lid="' + v.id + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Delete_worksheet.png" class="" alt="lesson" ></div>';

                                                html += '<div class="desc-my-lesson" data-lid="' + v.id + '" data-desc="' + v.description + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Detail_1.png" class="" alt="lesson" ></div>';
                                                if (v.price != 0) {
                                                    html += '<div class="price-lesson"><span class="">$</span>' + v.price + ' </div>';
                                                } else {
                                                    html += '<div class="price-lesson"><span class="">FREE</div>';
                                                }
                                                html += '</td>';
                                                html += '</tr>';
                                            });
                                        }
                                    }
                                    if (data.lesson != null) {
                                        if (data.lesson.length > 0) {
                                            $.each(data.lesson, function (i, v) {

                                                html += '<tr id="lesson-tr' + v.id + '" class="lesson-tr">';
                                                html += '<td>';
                                                html += ' <div class="cb-type2">';
                                                html += ' <label>';
                                                html += '<input type="checkbox" class="radio_buttons class_cb_search option-input-2 option-input-lesson option-input-lesson-check radio" value="' + v.id + '" data-name="' + v.name + '" name="select-tr"/>';
                                                html += ' </label>';
                                                html += '</div>';
                                                html += ' </td>';
                                                html += '<td class="table-img-icon">';
                                                if (v.cate == 1) {
                                                    html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_ENGLISH.png" alt="lesson" >';
                                                } else if (v.cate == 5) {
                                                    html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_MATH.png" alt="lesson" >';
                                                } else if (v.cate == 2) {
                                                    html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/Type_Science.png" alt="lesson" >';
                                                } else if (v.cate == 3) {
                                                    html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/Type_History.png" alt="lesson" >';
                                                } else if (v.cate == 4) {
                                                    html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/Type_Art_n_Design.png" alt="lesson" >';
                                                } else if (v.cate == 6) {
                                                    html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/Type_Music.png" alt="lesson" >';
                                                } else if (v.cate == 7) {
                                                    html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/Type_Others.png" alt="lesson" >';
                                                }
                                                html += '</td>';
                                                html += '<td class="table-img-icon">';
                                                html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Lesson.png" class="" alt="lesson" >';
                                                html += '<span class="name-lesson my-lesson-ws" data-lid="' + v.id + '">' + v.name + '</span>';
                                                html += ' </td>';
                                                html += '<td class="table-img-icon">';
                                                html += '<div class="delete-worksheet" data-lid="' + v.id + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Delete_worksheet.png" class="" alt="lesson" ></div>';
                                                html += '<div class="add-worksheet-ic" data-lid="' + v.id + '" data-name="' + v.name + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Add_worksheet.png" class="" alt="lesson" ></div>';
                                                html += '<div class="desc-my-lesson" data-lid="' + v.id + '" data-desc="' + v.description + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Detail_1.png" class="" alt="lesson" ></div>';
                                                html += '</td>';
                                                html += '</tr>';

                                            });

                                        }
                                    }
                                }
                                tbody_lesson.html(html);
                            });
                        }
                        function get_my_lesson2(text, table) {
                            $.post(home_url + "/?r=ajax/get_my_lesson", {search: text}, function (data) {
                                var tbody_lesson = $("#" + table + "");
                                data = JSON.parse(data);
                                var html = '';
                                if (data == 0) {
                                    html += '<tr id="lesson-tr" class="lesson-tr">';
                                    html += '<td></td><td colspan="3">No results.</td></tr>';
                                } else {
                                    if (data.publesson != null) {
                                        if (data.publesson.length > 0) {
                                            $.each(data.publesson, function (i, v) {
                                                html += '<tr id="lesson-tr' + v.id + '" class="lesson-tr">';
                                                html += '<td>';
                                                html += ' <div class="cb-type2">';
                                                html += ' <label>';
                                                html += '<input type="checkbox" class="radio_buttons class_cb_search option-input-2 option-input-lesson option-input-lesson-check radio" value="' + v.id + '" data-name="' + v.name + '" name="select-tr"/>';
                                                html += ' </label>';
                                                html += '</div>';
                                                html += ' </td>';
                                                html += '<td class="table-img-icon">';
                                                if (v.cate == 1) {
                                                    html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_ENGLISH.png" alt="lesson" >';
                                                } else if (v.cate == 5) {
                                                    html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_MATH.png" alt="lesson" >';
                                                } else if (v.cate == 8) {
                                                    html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Dictionary.png" alt="lesson" >';
                                                } else if (v.cate == 9) {
                                                    html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Self-study.png" alt="lesson" >';
                                                } else if (v.cate == 10) {
                                                    html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_SAT.png" alt="lesson" >';
                                                }
                                                html += '</td>';
                                                html += '<td class="table-img-icon">';
                                                html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Ready-made_lesson.png" class="" alt="lesson" >';
                                                html += '<span class="name-ready-lesson my-public-lesson" data-lid="' + v.id + '">' + v.name + '</span>';
                                                html += ' </td>';
                                                html += '<td class="table-img-icon">';
                                                html += '<div class="delete-worksheet" data-lid="' + v.id + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Delete_worksheet.png" class="" alt="lesson" ></div>';

                                                html += '<div class="desc-my-lesson" data-lid="' + v.id + '" data-desc="' + v.description + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Detail_1.png" class="" alt="lesson" ></div>';
                                                if (v.price != 0) {
                                                    html += '<div class="price-lesson"><span class="">$</span>' + v.price + ' </div>';
                                                } else {
                                                    html += '<div class="price-lesson"><span class="">FREE</div>';
                                                }
                                                html += '</td>';
                                                html += '</tr>';
                                            });
                                        }
                                    }
                                    if (data.lesson != null) {
                                        if (data.lesson.length > 0) {
                                            $.each(data.lesson, function (i, v) {
                                                html += '<tr id="lesson-tr' + v.id + '" class="lesson-tr">';
                                                html += '<td>';
                                                html += ' <div class="cb-type2">';
                                                html += ' <label>';
                                                html += '<input type="checkbox" class="radio_buttons class_cb_search option-input-2 option-input-lesson option-input-lesson-check radio" value="' + v.id + '" data-name="' + v.name + '" name="select-tr"/>';
                                                html += ' </label>';
                                                html += '</div>';
                                                html += ' </td>';
                                                html += '<td class="table-img-icon">';
                                                if (v.cate == 1) {
                                                    html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_ENGLISH.png" alt="lesson" >';
                                                } else if (v.cate == 5) {
                                                    html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_MATH.png" alt="lesson" >';
                                                } else if (v.cate == 2) {
                                                    html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/Type_Science.png" alt="lesson" >';
                                                } else if (v.cate == 3) {
                                                    html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/Type_History.png" alt="lesson" >';
                                                } else if (v.cate == 4) {
                                                    html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/Type_Art_n_Design.png" alt="lesson" >';
                                                } else if (v.cate == 6) {
                                                    html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/Type_Music.png" alt="lesson" >';
                                                } else if (v.cate == 7) {
                                                    html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/Type_Others.png" alt="lesson" >';
                                                }
                                                html += '</td>';
                                                html += '<td class="table-img-icon">';
                                                html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Lesson.png" class="" alt="lesson" >';
                                                html += '<span class="name-lesson my-lesson-ws" data-lid="' + v.id + '">' + v.name + '</span>';
                                                html += ' </td>';
                                                html += '<td class="table-img-icon">';
                                                html += '<div class="delete-worksheet" data-lid="' + v.id + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Delete_worksheet.png" class="" alt="lesson" ></div>';
                                                html += '<div class="desc-my-lesson" data-lid="' + v.id + '" data-desc="' + v.description + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Detail_1.png" class="" alt="lesson" ></div>';
                                                html += '</td>';
                                                html += '</tr>';

                                            });

                                        }
                                    }
                                }
                                tbody_lesson.html(html);
                            });
                        }
                        //add worksheet
                        $(".add-worksheet-ic").live("click", function () {
                            var lid = $(this).attr("data-lid");
                            var name = $(this).attr("data-name");
                            $(".my-lesson-body").addClass(" hidden");
                            $("#add-worksheet").removeClass("hidden");
                            $("#btn-add-ws").attr("data-lid", lid);
                            $(".name-lesson").text(name);
                            $(".lesson-detail-lib").removeClass("active");
                            $(".lesson-detail-lib").slideUp("slow");
                            $("#icon-open-lib").attr("src", "<?php echo get_template_directory_uri(); ?>/library/images/Icon_Blue_Open.png");
                            $(".from-public-lib").removeClass("active");
                            $(".from-public-lib").slideUp("slow");
                            $("#icon-open-pub").attr("src", "<?php echo get_template_directory_uri(); ?>/library/images/Icon_Blue_Open.png");
                        });
                        $(".link-clear").click(function () {
                            $(".search-tit").val('');
                        });
                        $("#icon-open-pub").live("click", function () {
                            var check = $(".from-public-lib").hasClass("active");
                            if (check == false) {
                                var cat_level = '';
                                var level = '';
                                var sublevel = '';
                                var lang = '';
                                var sheet_name = '';
                                var group_name = '';
                                var assignment_id = '';
                                var homework_types = '';
                                var grade = '';
                                var trivia_exclusive = '';
                                var active = '';
                                var type = '';
                                getWorksheets2(lang, sheet_name, group_name, assignment_id, homework_types, grade, trivia_exclusive, active, cat_level, level, sublevel, type);
                                $(".from-public-lib").addClass(" active");
                                $(".from-public-lib").slideDown("slow");
                                $(".lesson-detail-lib").removeClass("active");
                                $(".lesson-detail-lib").slideUp("slow");
                                $("#icon-open-lib").attr("src", "<?php echo get_template_directory_uri(); ?>/library/images/Icon_Blue_Open.png");
                                $("#icon-open-pub").attr("src", "<?php echo get_template_directory_uri(); ?>/library/images/Icon_Blue_Close.png");
                            } else {
                                $(".from-public-lib").removeClass("active");
                                $(".from-public-lib").slideUp("slow");
                                $("#icon-open-pub").attr("src", "<?php echo get_template_directory_uri(); ?>/library/images/Icon_Blue_Open.png");
                                $(".table-eng-worksheet").html('');
                                $(".table-math-worksheet").html('');
                            }
                        });
                        $("#icon-open-pub2").live("click", function () {
                            var check = $(".from-public-lib2").hasClass("active");
                            if (check == false) {
                                var cat_level = '';
                                var level = '';
                                var sublevel = '';
                                var lang = '';
                                var sheet_name = '';
                                var group_name = '';
                                var assignment_id = '';
                                var homework_types = '';
                                var grade = '';
                                var trivia_exclusive = '';
                                var active = '';
                                var type = '';
                                getWorksheets2(lang, sheet_name, group_name, assignment_id, homework_types, grade, trivia_exclusive, active, cat_level, level, sublevel, type);
                                $(".from-public-lib2").addClass(" active");
                                $(".from-public-lib2").slideDown("slow");
                                $(".lesson-detail-lib2").removeClass("active");
                                $(".lesson-detail-lib2").slideUp("slow");
                                $("#icon-open-lib2").attr("src", "<?php echo get_template_directory_uri(); ?>/library/images/Icon_Blue_Open.png");
                                $("#icon-open-pub2").attr("src", "<?php echo get_template_directory_uri(); ?>/library/images/Icon_Blue_Close.png");
                                $("#btn-open-search-eng3").removeClass("btn-down");
                                $("#btn-open-search-eng3").addClass("btn-assign");
                                $("#open-search-eng3").slideUp("fast");
                                $("#select-all-eng3").attr("checked", false);
                                $("#option-detail-eng3").addClass(" hidden");
                                $("#tab-public-lib-eng3").addClass(" active");
                                $("#tab-public-lib-math3").removeClass(" active");
                                $("#tab-math-lib3").removeClass(" active in");
                                $("#tab-eng-lib3").addClass(" active in");
                            } else {
                                $(".from-public-lib2").removeClass("active");
                                $(".from-public-lib2").slideUp("slow");
                                $("#icon-open-pub2").attr("src", "<?php echo get_template_directory_uri(); ?>/library/images/Icon_Blue_Open.png");
                                $(".table-eng-worksheet").html('');
                                $(".table-math-worksheet").html('');
                            }
                        });
                        $("#icon-open-lib").live("click", function () {
                            var check = $(".lesson-detail-lib").hasClass("active");
                            if (check == false) {
                                $("#open-search-my-lib2").css("display", "none");
                                $(".lesson-detail-lib").addClass(" active");
                                $(".lesson-detail-lib").slideDown("slow");
                                $(".from-public-lib").removeClass("active");
                                $(".from-public-lib").slideUp("slow");
                                $("#icon-open-pub").attr("src", "<?php echo get_template_directory_uri(); ?>/library/images/Icon_Blue_Open.png");
                                $("#icon-open-lib").attr("src", "<?php echo get_template_directory_uri(); ?>/library/images/Icon_Blue_Close.png");
                                var text = '';
                                $("#table-library").html('');
                                var table = "table-library2";
                                get_my_library(text, table);
                            } else {
                                $(".lesson-detail-lib").removeClass("active");
                                $(".lesson-detail-lib").slideUp("slow");
                                $("#icon-open-lib").attr("src", "<?php echo get_template_directory_uri(); ?>/library/images/Icon_Blue_Open.png");
                                $("#table-library2").html("");
                            }
                        });
                        $("#icon-close-addws").live("click", function () {
                            $("#add-worksheet").addClass(' hidden');
                            $(".my-lesson-body").removeClass(' hidden');
                            var text = '';
                            $("#table-my-lesson").html('');
                            var table = "table-my-lesson";
                            get_my_lesson(text, table);
                        });
                        $("#icon-close-addws-lib").live("click", function () {
                            $("#add-worksheet-mylib").addClass(' hidden');
                            $(".library-lib ").removeClass(' hidden');
                            var text = '';
                            var table = "table-library";
                            get_my_library(text, table);
                        });
                        //delete sheet create lesson
                        $(".delete-sheet").live("click", function () {
                            var id = $(this).parent(".row-worksheet").attr("data-sid");
                            var html = '';
                            $("#yes-delete").attr("data-ws", id);
                            html += 'Are you sure you want to delete it?';
                            $("#modal-alert-yesno .box-title-1").html(html);
                            $("#modal-alert-yesno").css('display', 'block');
                        });
                        $(".delete-lesson").live("click", function () {
                            var id = $(this).parent(".row-worksheet").attr("data-lid");
                            var html = '';
                            $("#yes-delete").attr("data-rowlesson", id);
                            html += 'Are you sure you want to delete it?';
                            $("#modal-alert-yesno .box-title-1").html(html);
                            $("#modal-alert-yesno").css('display', 'block');
                        });
                        //search worksheet in my library
                        $("#btn-search-my-lib").click(function () {
                            var text = $("#search-tit-lib").val();
                            var table = "table-library";
                            get_my_library(text, table);
                        });
                        $("#btn-search-my-lib2").click(function () {
                            var text = $("#search-tit-lib2").val();
                            var table = "table-library2";
                            get_my_library(text, table);
                        });
                        //add worksheet in my lesson
                        $("#btn-add-ws").live("click", function () {
                            var lid = $(this).attr("data-lid");
                            var selected = new Array();
                            $(".option-input-mylibrary:checked").each(function () {
                                var id = $(this).attr("data-id");
                                selected.push(id);
                            });
                            $(".option-input-worksheet-eng:checked").each(function () {
                                var id = $(this).val();
                                selected.push(id);
                            });
                            $(".option-input-worksheet-math:checked").each(function () {
                                var id = $(this).val();
                                selected.push(id);
                            });
                            $.post(home_url + "/?r=ajax/add_my_lesson", {lid: lid, sheet: selected}, function (data) {
                                if (data !== "0") {
                                    $(".my-lesson-body").removeClass("hidden");
                                    $("#add-worksheet").addClass(" hidden");
                                    var html = '';
                                    html += 'Worksheets successfully added to My Lesson.';
                                    $(".box-title-1").html(html);
                                    $("#modal-alert").css('display', 'block');
                                    var text = '';
                                    $("#table-my-lesson").html('');
                                    var table = "table-my-lesson";
                                    get_my_lesson(text, table);
                                }
                            });
                        });
                        //addd worksheet in my library from public library
                        $("#btn-add-ws-lib").live("click", function () {
                            var selected = new Array();
                            $(".option-input-worksheet-eng:checked").each(function () {
                                var id = $(this).val();
                                var cate = $(this).attr("data-cate");
                                var sheet = $(this).attr("data-sheet");
                                selected.push({id: id, cate: cate, sheet: sheet});
                            });
                            $(".option-input-worksheet-math:checked").each(function () {
                                var id = $(this).val();
                                var cate = $(this).attr("data-cate");
                                var sheet = $(this).attr("data-sheet");
                                selected.push({id: id, cate: cate, sheet: sheet});
                            });
                            if (selected.length == 0) {
                                var html = '';
                                html += 'Please select worksheets first.';
                                $(".box-title-1").html(html);
                                $("#modal-alert").css('display', 'block');
                            } else {
                                $.post(home_url + "/?r=ajax/add_my_library", {
                                    data: selected}, function (data) {
                                    if (data !== "0") {
                                        var html = '';
                                        html += 'Worksheets successfully saved to <span style="font-style:italic;">My Library</span>.';
                                        $(".box-title-1").html(html);
                                        $("#modal-alert").css('display', 'block');
                                        $("#add-worksheet-mylib").addClass(' hidden');
                                        $(".library-lib ").removeClass(' hidden');
                                        var text = '';
                                        var table = "table-library";
                                        get_my_library(text, table);
                                    } else {
                                        var html = '';
                                        html += 'Fail!';
                                        $(".box-title-1").html(html);
                                        $("#modal-alert").css('display', 'block');
                                    }
                                });
                            }
                        });
                        //undo description
                        $(".undo-desc").live("click", function () {
                            var id = $(this).attr("data-lid");
                            var text = $("#detaildesc" + id).text();
                            $("#detailinput" + id).val(text);
                        });
                        $(".edit-desc-worksheet").live("click", function () {
                            $(".input-detail textarea").focus();
                        });
                        //get worksheet of ready-made Lesson
                        $(".open-arrow-ready-eng").live("click", function () {
                            var id = $(this).attr("data-gid");
                            var desc = $(this).attr("data-content");
                            var open = $(".eng-tr").not('#eng-tr' + id + '').next().hasClass("detail-tr-lesson");
                            if (open == true) {
                                $(".eng-tr").not('#eng-tr' + id + '').nextAll(".detail-tr-lesson").remove();
                            }
                            var check1 = $('#eng-tr' + id + '').next().hasClass("detail-desc");
                            if (check1 == false) {
                                var html = '';
                                html += '<tr class="detail-desc detail-tr-lesson desc-worksheet desc-worksheet-' + id + '" >';
                                html += '<td></td>';
                                html += '<td></td>';
                                html += '<td colspan="3" class="input-detail">';
                                var check2 = $('#eng-tr' + id + '').next().hasClass("detail-worksheet");
                                if (check2 == true) {
                                    html += '<div class="desc-ready" id="detaildesc' + id + '">' + desc + '</div>';
                                } else {
                                    html += '<div class="desc-ready" id="detaildesc' + id + '">' + desc + '</div>';
                                }
                                html += ' </td>';
                                html += '</tr>';
                                $('#eng-tr' + id + '').after(html);
                                $(".detail-tr-lesson td").hide().slideDown("slow");
                            } else {
                                $('.detail-desc td').slideUp("slow", function () {
                                    $('#eng-tr' + id + '').nextAll(".detail-desc").remove();
                                });
                            }
                        });
                        $(".open-arrow-ready-math").live("click", function () {
                            var id = $(this).attr("data-gid");
                            var desc = $(this).attr("data-content");
                            var open = $(".math-tr").not('#math-tr' + id + '').next().hasClass("detail-tr-lesson");
                            if (open == true) {
                                $(".math-tr").not('#math-tr' + id + '').nextAll(".detail-tr-lesson").remove();
                            }
                            var check1 = $('#math-tr' + id + '').next().hasClass("detail-desc");
                            if (check1 == false) {
                                var html = '';
                                html += '<tr class="detail-desc detail-tr-lesson desc-worksheet desc-worksheet-' + id + '" >';
                                html += '<td></td>';
                                html += '<td></td>';
                                html += '<td colspan="3" class="input-detail">';
                                var check2 = $('#math-tr' + id + '').next().hasClass("detail-worksheet");
                                if (check2 == true) {
                                    html += '<div class="desc-ready" id="detaildesc' + id + '">' + desc + '</div>';
                                } else {
                                    html += '<div class="desc-ready" id="detaildesc' + id + '">' + desc + '</div>';
                                }
                                html += ' </td>';
                                html += '</tr>';
                                $('#math-tr' + id + '').after(html);
                                $(".detail-tr-lesson td").hide().slideDown("slow");
                            } else {
                                $('.detail-desc td').slideUp("slow", function () {
                                    $('#math-tr' + id + '').nextAll(".detail-desc").remove();
                                });
                            }
                        });
                        $(".paginate-pub").live("click", function () {
                            var page = $(this).attr("page");
                            var cat_level = '';
                            var level = '';
                            var sublevel = '';
                            var lang = '';
                            var sheet_name = '';
                            var group_name = '';
                            var assignment_id = '';
                            var homework_types = '';
                            var grade = '';
                            var trivia_exclusive = '';
                            var active = '';
                            var type = '';
                            getWorksheets(lang, sheet_name, group_name, assignment_id, homework_types, grade, trivia_exclusive, active, cat_level, level, sublevel, type, page);
                        });
                        $(".paginate-pub2").live("click", function () {
                            var page = $(this).attr("page");
                            var cat_level = '';
                            var level = '';
                            var sublevel = '';
                            var lang = '';
                            var sheet_name = '';
                            var group_name = '';
                            var assignment_id = '';
                            var homework_types = '';
                            var grade = '';
                            var trivia_exclusive = '';
                            var active = '';
                            var type = '';
                            getWorksheets2(lang, sheet_name, group_name, assignment_id, homework_types, grade, trivia_exclusive, active, cat_level, level, sublevel, type, page);
                        });
                        //event for create subject button
                        $("#btn-create-subject").live("click", function () {
                            $("#input-style-sub").attr('value', '');
                            $('#post_mysubject_ifr').contents().find('#tinymce').text('');
                            var check = $("#create-subject-content").hasClass("active");
                            if (check == false) {
                                $("#table-confirm-subject").html("");
                                var selected = new Array();
                                $(".option-input-lesson:checked").each(function () {
                                    var lessonid = $(this).val();
                                    var lesson = $(this).attr('data-name');
                                    selected.push({"lid": lessonid, 'lesson': lesson});
                                });
                                var html = '';
                                if (selected.length !== 0) {
                                    var table_confirm_worksheet = $("#table-confirm-subject");
                                    for (var i = 0; i < selected.length; i++) {
                                        html += '<tr data-lid="' + selected[i].lid + '" id="' + selected[i].lid + '" class="row-worksheet">';
                                        html += ' <td class="table-img-icon" style="padding-bottom: 10px;padding-top: 5px; width:95%">';
                                        html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Lesson.png" class="" alt="lesson" >';
                                        html += '<span class="name-lesson-2">' + selected[i].lesson + '</span>';
                                        html += '</td>';
                                        html += '<td class="table-img-icon text-right delete-lesson"  ><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Delete_worksheet.png" class="" alt="lesson"></td>';
                                        html += '</tr>';
                                    }
                                    table_confirm_worksheet.append(html);
                                    $(".table-my-lesson").removeClass("active");
                                    $(".table-my-lesson").addClass("hidden");
                                    $(".create-subject-content").removeClass("hidden");
                                    $(".create-subject-content").addClass("active");
                                    $("#select-all-mylibrary").attr("checked", false);
                                    $.post(home_url + "/?r=ajax/get_cate", {type: "GET"}, function (data) {
                                        var data = JSON.parse(data);
                                        if (data.cate.length > 0) {
                                            var html_cate = '';
                                            $.each(data.cate, function (i, v) {
                                                html_cate += '<option  value="' + v.id + '">' + v.name + '</option>';
                                            });
                                            $('#select-my-sub').html(html_cate).data("selectBox-selectBoxIt").refresh();
                                        }
                                    });
                                } else {
                                    html += 'Please select lessons first.';
                                    $(".box-title-1").html(html);
                                    $("#modal-alert").css('display', 'block');
                                }
                            }
                        });
                        //save my subject
                        $("#create-sub").click(function () {
                            var desc = $('#post_mysubject_ifr').contents().find('#tinymce').text();
                            var name = $('#input-style-sub').val();
                            var cate = $('#select-my-subSelectBoxItText').attr("data-val");
                            var selected = new Array();
                            $(".row-worksheet").each(function () {
                                var sid = $(this).attr("data-lid");
                                selected.push(sid);
                            });
                            $.post(home_url + "/?r=ajax/add_my_subject", {name: name, desc: desc, cate: cate, lesson: selected}, function (data) {
                                if (data !== 0) {
                                    var html = '';
                                    html += 'New Subject has been created! You can find it from My Subject.';
                                    $(".box-title-1").html(html);
                                    $("#modal-alert").css('display', 'block');
                                    $(".create-subject-content").removeClass("active");
                                    $(".create-subject-content").addClass("hidden");
                                    $(".table-my-lesson").removeClass("hidden");
                                    $(".table-my-lesson").addClass("active");
                                    $(".option-input-lesson").attr("checked", false);
                                    $("#select-all-subject").attr("checked", false);
                                }
                            });
                        });
                        function get_my_subject(search) {
                            $.post(home_url + "/?r=ajax/get_my_subject", {search: search}, function (data) {
                                $("#table-my-subject").html('');
                                data = JSON.parse(data);
                                if (data.subject.length > 0) {
                                    $.each(data.subject, function (i, v) {
                                        var html = '';
                                        html += '<tr id="subj-tr' + v.id + '" class="subj-tr">';
                                        html += '<td>';
                                        html += ' <div class="cb-type2">';
                                        html += ' <label>';
                                        html += '<input type="checkbox" class="radio_buttons class_cb_search option-input-2 option-input-lesson option-sub-check radio" value="' + v.id + '" data-name="' + v.name + '" name="select-tr"/>';
                                        html += ' </label>';
                                        html += '</div>';
                                        html += ' </td>';
                                        html += '<td class="table-img-icon">';
                                        if (v.cate == 1) {
                                            html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_ENGLISH.png" alt="lesson" >';
                                        } else if (v.cate == 5) {
                                            html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_MATH.png" alt="lesson" >';
                                        } else if (v.cate == 2) {
                                            html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/Type_Science.png" alt="lesson" >';
                                        } else if (v.cate == 3) {
                                            html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/Type_History.png" alt="lesson" >';
                                        } else if (v.cate == 4) {
                                            html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/Type_Art_n_Design.png" alt="lesson" >';
                                        } else if (v.cate == 6) {
                                            html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/Type_Music.png" alt="lesson" >';
                                        } else if (v.cate == 7) {
                                            html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/Type_Others.png" alt="lesson" >';
                                        }
                                        html += '</td>';
                                        html += '<td class="table-img-icon">';
                                        html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_MySubject.png" class="" alt="lesson" >';
                                        html += '<span class="name-subject lst-sub-lesson" data-subid="' + v.id + '">' + v.name + '</span>';
                                        html += ' </td>';
                                        html += '<td class="table-img-icon">';
                                        html += '<div class="delete-subject" data-subid="' + v.id + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Delete_worksheet.png" class="" alt="lesson" ></div>';
                                        html += '<div class="add-lesson-icon" data-lid="' + v.id + '" data-name="' + v.name + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Add_worksheet.png" class="" alt="lesson" ></div>';
                                        html += '<div class="detail-subject" data-subid="' + v.id + '" data-desc="' + v.description + '" ><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Detail_1.png" class="" alt="lesson" ></div>';
                                        html += '</td>';
                                        html += '</tr>';
                                        $("#table-my-subject").append(html);
                                    });
                                }
                            });
                        }
                        $(".detail-subject").live("click", function () {
                            var id = $(this).attr("data-subid");
                            var desc = $(this).attr("data-desc");
                            var open = $(".subj-tr").not('#subj-tr' + id + '').next().hasClass("detail-desc");
                            if (open == true) {
                                $(".subj-tr").not('#subj-tr' + id + '').nextAll(".detail-desc").remove();
                                $(".subj-tr").not('#subj-tr' + id + '').nextAll(".detail-tr-lesson").remove();
                            }
                            var check1 = $('#subj-tr' + id + '').next().hasClass("detail-desc");
                            if (check1 == false) {
                                var html = '';
                                html += '<tr class="detail-desc desc-worksheet desc-worksheet-' + id + '" >';
                                html += '<td></td>';
                                html += '<td></td>';
                                html += '<td colspan="2" class="input-detail detail-input">';
                                var check2 = $('#subj-tr' + id + '').next().hasClass("detail-lesson");
                                if (check2 == true) {
                                    html += '<div class="desc-detail style-border-desc" id="detaildesc' + id + '">' + desc + '</div>';
                                } else {
                                    html += '<div class="desc-detail" id="detaildesc' + id + '">' + desc + '</div>';
                                }
                                html += '<textarea rows="4" id="detailinput' + id + '" class="hidden" name="input-detail" >' + desc + '</textarea>';
                                html += '<div class="table-img-icon edit-desc-worksheet"  onclick="edit_desc(' + id + ')" id="editdesc' + id + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Edit.png" class="" alt="lesson" ></div>';
                                html += '<div class="table-img-icon undo-desc-subject" data-lid="' + id + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Undo.png" class="" alt="lesson" ></div>';
                                html += '<div class="hidden table-img-icon edit-desc-worksheet edit-success-subject"  id="editsuccess' + id + '" data-subid="' + id + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Edit_Done.png" class="" alt="lesson" ></div>';
                                html += ' </td>';
                                html += '</tr>';
                                $('#subj-tr' + id + '').after(html);
                                $(".detail-desc td").hide().slideDown("slow");
                            } else {
                                $('.detail-desc td').slideUp("fast", function () {
                                    $('#subj-tr' + id + '').nextAll(".detail-desc").remove();
                                });
                            }
                        });
                        //update description subject
                        $(".edit-success-subject").live("click", function () {
                            var sid = $(this).attr("data-subid");
                            var text = $("#detailinput" + sid).val();
                            $.post(home_url + "/?r=ajax/update_desc_subject", {desc: text, sid: sid}, function (data) {
                                $("#subj-tr" + sid + "").nextAll(".detail-desc").remove();
                                $.get(home_url + "/?r=ajax/get_desc_subject", {data: sid}, function (data1) {
                                    var data = JSON.parse(data1);
                                    if (data.sheet.length > 0) {
                                        var html = '';
                                        $.each(data.sheet, function (i, v) {
                                            html += '<tr class="detail-desc detail-tr-lesson desc-worksheet desc-worksheet-' + v.id + '">';
                                            html += '<td></td>';
                                            html += '<td></td>';
                                            html += '<td colspan="2" class="input-detail detail-input">';
                                            html += '<div class="desc-detail" id="detaildesc' + v.id + '">' + v.description + '</div><textarea rows="4" id="detailinput' + v.id + '" class="hidden" name="input-detail" >' + v.description + '</textarea>';
                                            html += '<div class="table-img-icon edit-desc-worksheet" onclick="edit_desc(' + v.id + ')" id="editdesc' + v.id + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Edit.png" class="" alt="lesson" ></div>';
                                            html += '<div class="table-img-icon undo-desc-subject" data-lid="' + v.id + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Undo.png" class="" alt="lesson" ></div>';
                                            html += '<div class="hidden table-img-icon edit-desc-worksheet edit-success-subject"  id="editsuccess' + v.id + '" data-subid="' + v.id + '" ><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Edit_Done.png" class="" alt="lesson" ></div>';
                                            html += ' </td>';
                                            html += '</tr>';
                                        });
                                        $("#subj-tr" + sid + "").after(html);
                                        $(".detail-desc td").hide().slideDown("slow");
                                    }
                                });
                                if (data == '1') {
                                }
                            });
                        });
                        //undo description
                        $(".undo-desc-subject").live("click", function () {
                            var id = $(this).attr("data-lid");
                            var text = $("#detaildesc" + id).text();
                            $("#detailinput" + id).val(text);
                        });
                        //list lesson's subject
                        $(".lst-sub-lesson").live("click", function () {
                            var id = $(this).attr("data-subid");
                            $(".subj-tr").not('#subj-tr' + id + '').next(".detail-desc").remove();
                            $(".subj-tr").not('#subj-tr' + id + '').nextAll(".desc-lesson-sub").remove();
                            var open = $(".subj-tr").not('#subj-tr' + id + '').next().hasClass("detail-tr-lesson");
                            if (open == true) {
                                $(".subj-tr").not('#subj-tr' + id + '').nextAll(".detail-tr-lesson").remove();
                            }
                            var check1 = $('#subj-tr' + id + '').nextAll().hasClass("detail-lesson");
                            if (check1 == false) {
                                $.post(home_url + "/?r=ajax/get_subject_lesson", {subid: id}, function (data) {
                                    data = JSON.parse(data);
                                    var html = '';
                                    if (data !== null) {
                                        if (data.length > 0) {
                                            $.each(data, function (i, v) {
                                                html += '<tr class="detail-lesson detail-tr-1 detail-tr-lesson tr-lesson set-hide-lesson" id="tr-lesson-' + v.lesson_id + '" ><td></td>';
                                                html += '<td></td>';
                                                html += '<td class="table-img-icon lst-worksheet-lesson" style=" color:#ce851f !important; cursor:pointer;  " data-lid="' + v.lesson_id + '">';
                                                html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Lesson.png" alt="lesson" style="margin-bottom: 2px;" >' + v.lesson + '</td>';
                                                html += ' <td class="table-img-icon">';
                                                html += '<div class="delete-subject" data-sublid="' + v.id + '" data-subject="' + v.subject_id + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Delete_worksheet.png" alt="lesson" ></div>';
                                                html += ' <div class="detail-desc-lesson" data-id="' + v.lesson_id + '" data-desc="' + v.lesson_desc + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Detail_1.png" class="" alt="lesson" ></div>';
                                                html += ' <div class="down-lesson" data-sublid="' + v.id + '" data-subid="' + v.subject_id + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Move_down.png" class="" alt="lesson" ></div>';
                                                html += ' <div class="up-lesson" data-sublid="' + v.id + '" data-subid="' + v.subject_id + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Move_up.png" class="" alt="lesson" ></div>';
                                                html += ' </td>';
                                                html += '</tr>';
                                            });
                                        }
                                    } else {
                                        html += '<tr class="detail-lesson detail-tr-1 detail-tr-lesson tr-lesson set-hide"><td></td><td></td><td colspan="2" style="padding-left:35px;">This folder is empty.</td></tr>';
                                    }
                                    if ($('#subj-tr' + id + '').next().hasClass("detail-desc")) {
                                        $('.desc-detail').addClass("style-border-desc");
                                        $(".detail-desc").after(html);
                                        $(".detail-lesson td").hide().slideDown("slow");
                                    } else {
                                        $('#subj-tr' + id + '').after(html);
                                        $(".detail-lesson td").hide().slideDown("slow");
                                    }
                                });
                            } else {
                                $('.desc-detail').removeClass("style-border-desc");
                                $('.detail-lesson td').slideUp("fast", function () {
                                    $('#subj-tr' + id + '').nextAll(".detail-tr-lesson").remove();
                                    $('#subj-tr' + id + '').nextAll(".detail-desc").remove();
                                });
                            }
                        });
                        //list worksheet's lesson in subject
                        $(".lst-worksheet-lesson").live("click", function () {
                            var id = $(this).attr("data-lid");
                            var check1 = $('#tr-lesson-' + id + '').next().hasClass("detail-worksheet");
                            var open = $(".tr-lesson").not('#tr-lesson-' + id + '').nextAll().hasClass("detail-worksheet");
                            var open2 = $(".tr-lesson").not('#tr-lesson-' + id + '').next().hasClass("desc-lesson-sub");
                            if (open == true || open2 == true) {
                                var open3 = $('#tr-lesson-' + id + '').nextAll().hasClass('worksheet-lesson-' + id);

                                $(".tr-lesson").not('#tr-lesson-' + id + '').next(".desc-lesson-sub").remove();
                                if (open3 == false) {
                                    $(".tr-lesson").not('#tr-lesson-' + id + '').nextAll(".detail-worksheet").remove();
                                }
                                $(".tr-lesson").not('#tr-lesson-' + id + '').removeClass("style-tr-sub");
                            }
                            var html = '';
                            if (check1 == false) {
                                $.post(home_url + "/?r=ajax/get_my_lesson_sheet", {data: id}, function (data) {
                                    if (data !== "0") {
                                        data = JSON.parse(data);
                                        if (data.sheet.length > 0) {
                                            $.each(data.sheet, function (i, v) {
                                                html += '<tr class="detail-worksheet detail-tr-1 detail-tr-lesson tr-lesson set-hide worksheet-lesson-' + id + '" id="tr-lesson-' + v.sheet_id + '"><td></td>';
                                                html += '<td></td>';
                                                html += '<td class="table-img-icon" style="padding-left: 62px !important;">';
                                                html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Worksheet.png" class="" alt="lesson" style="margin-bottom: 2px;">' + v.sheet_name + '</td>';
                                                html += ' <td class="table-img-icon">';
                                                html += '<div class="delete-worksheet" data-slid="' + v.id + '" data-lid2="' + v.lesson_id + '" data-item="subject"><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Delete_worksheet.png" alt="lesson" ></div>';
                                                html += ' <div class="detail-worksheet-ls-sub" data-id="' + v.sheet_id + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Detail_1.png" class="" alt="lesson" ></div>';
                                                html += ' <div class="down-worksheet" data-slid="' + v.id + '" data-lid="' + v.lesson_id + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Move_down.png" class="" alt="lesson" ></div>';
                                                html += ' <div class="up-worksheet" data-slid="' + v.id + '" data-lid="' + v.lesson_id + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Move_up.png" class="" alt="lesson" ></div>';
                                                html += ' </td>';
                                                html += '</tr>';
                                            });
                                        }
                                    } else {
                                        html += '<tr class="detail-worksheet detail-tr-1 detail-tr-lesson tr-lesson set-hide"><td></td><td></td><td colspan="2" style="padding-left:65px;">This folder is empty.</td></tr>';
                                    }
                                    $('#tr-lesson-' + id + '').addClass("style-tr-sub");
                                    var check2 = $('#tr-lesson-' + id + '').next().hasClass("desc-lesson-sub");
                                    if (check2 == true) {
                                        var check3 = $('.desc-lesson-sub').next().hasClass("detail-worksheet");
                                        if (check3 == false) {
                                            $(".desc-lesson-sub").after(html);
                                            $(".detail-worksheet td").hide().slideDown("slow");
                                        } else if (check3 == true) {
                                            $('#tr-lesson-' + id + '').removeClass("style-tr-sub");
                                            $('.set-hide td').slideUp("slow", function () {
                                                $('#tr-lesson-' + id + '').nextAll(".set-hide").remove();
                                            });
                                        }
                                    } else {
                                        $('#tr-lesson-' + id + '').after(html);
                                        $(".detail-tr-lesson td").hide().slideDown("slow");
                                    }
                                });
                            } else {
                                $('#tr-lesson-' + id + '').removeClass("style-tr-sub");
                                $('.detail-worksheet td').slideUp("slow", function () {
                                    $('#tr-lesson-' + id + '').nextAll(".set-hide").remove();
                                });
                            }
                        });
                        //show description of lesson in  My subject
                        $(".detail-desc-lesson").live("click", function () {
                            var id = $(this).attr("data-id");
                            var desc = $(this).attr("data-desc");
                            var open = $("#tr-lesson-" + id + "").next().hasClass("desc-lesson-sub");
                            if (open == false) {
                                var check2 = $('#tr-lesson-' + id + '').next().hasClass('worksheet-lesson-' + id);
                                $('#tr-lesson-' + id + '').addClass("style-tr-sub");
                                if (check2 == false) {
                                    $(".tr-lesson").not('#tr-lesson-' + id + '').nextAll(".detail-worksheet").remove();
                                }
                                $(".tr-lesson").not('#tr-lesson-' + id + '').removeClass("style-tr-sub");
                                $(".tr-lesson").not("#tr-lesson-" + id + "").next(".desc-lesson-sub").remove();
                                var html = '';
                                html += '<tr class="desc-lesson-sub detail-tr-lesson set-hide desc-worksheet-' + id + '">';
                                html += '<td></td>';
                                html += '<td></td>';
                                html += '<td colspan="2" class="input-detail lesson-detail-1 detail-input">';
                                if (desc == "")
                                {
                                    html += '<div class="desc-detail lesson-detail sub-detail" id="detaildesc' + id + '">&nbsp;</div>';
                                } else
                                {
                                    html += '<div class="desc-detail lesson-detail sub-detail" id="detaildesc' + id + '">' + desc + '</div>';
                                }
                                html += '<textarea rows="4" id="detailinput' + id + '" class="hidden" name="input-detail" >' + desc + '</textarea>';
                                html += '<div class="table-img-icon edit-desc-worksheet" onclick="edit_desc(' + id + ')" id="editdesc' + id + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Edit.png" class="" alt="lesson" ></div>';
                                html += '<div class="table-img-icon undo-desc" data-lid="' + id + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Undo.png" class="" alt="lesson" ></div>';
                                html += '<div class="hidden table-img-icon edit-desc-worksheet edit-success-lessonsub"  id="editsuccess' + id + '" data-lid="' + id + '" ><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Edit_Done.png" class="" alt="lesson" ></div>';
                                html += ' </td>';
                                html += '</tr>';
                                $("#tr-lesson-" + id + "").after(html);
                                $(".desc-lesson-sub td").hide().slideDown("slow");
                            } else {
                                var check = $(".desc-lesson-sub").next().hasClass("detail-worksheet");
                                if (check == false) {
                                    $('#tr-lesson-' + id + '').removeClass("style-tr-sub");
                                }
                                $(".desc-lesson-sub td").slideUp("slow", function () {
                                    $("#tr-lesson-" + id + "").nextAll(".desc-lesson-sub").remove();
                                });
                            }
                        });
                        $("#select-all-subject").click(function () {
                            if ($("#select-all-subject").is(":checked")) {

                                $(".option-sub-check").attr("checked", true);
                            } else
                                $(".option-sub-check").attr("checked", false);
                        });
                        $("#icon-option-subject").click(function () {
                            var sort = $(this).attr("data-sort");
                            $.post(home_url + "/?r=ajax/get_my_subject", {sort: sort}, function (data) {
                                $("#table-my-subject").html('');
                                if (data !== "0") {
                                    if (sort == "upward") {
                                        $("#icon-option-subject").attr("data-sort", "downward");
                                        $(".img-sort-subject").attr("src", "<?php echo get_template_directory_uri(); ?>/library/images/icon_Oldest.png");
                                    } else if (sort == "downward") {
                                        $("#icon-option-subject").attr("data-sort", "upward");
                                        $(".img-sort-subject").attr("src", "<?php echo get_template_directory_uri(); ?>/library/images/icon_Newest.png");
                                    }
                                    data = JSON.parse(data);
                                    if (data.subject.length > 0) {
                                        $.each(data.subject, function (i, v) {
                                            var html = '';
                                            html += '<tr id="subj-tr' + v.id + '" class="subj-tr">';
                                            html += '<td>';
                                            html += ' <div class="cb-type2">';
                                            html += ' <label>';
                                            html += '<input type="checkbox" class="radio_buttons class_cb_search option-input-2 option-input-lesson option-sub-check radio" value="' + v.id + '" data-name="' + v.name + '" name="select-tr"/>';
                                            html += ' </label>';
                                            html += '</div>';
                                            html += ' </td>';
                                            html += '<td class="table-img-icon">';
                                            if (v.cate == 1) {
                                                html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_ENGLISH.png" alt="lesson" >';
                                            } else if (v.cate == 5) {
                                                html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_MATH.png" alt="lesson" >';
                                            } else if (v.cate == 2) {
                                                html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/Type_Science.png" alt="lesson" >';
                                            } else if (v.cate == 3) {
                                                html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/Type_History.png" alt="lesson" >';
                                            } else if (v.cate == 4) {
                                                html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/Type_Art_n_Design.png" alt="lesson" >';
                                            } else if (v.cate == 6) {
                                                html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/Type_Music.png" alt="lesson" >';
                                            } else if (v.cate == 7) {
                                                html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/Type_Others.png" alt="lesson" >';
                                            }
                                            html += '</td>';
                                            html += '<td class="table-img-icon">';
                                            html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_MySubject.png" class="" alt="lesson" >';
                                            html += '<span class="name-subject lst-sub-lesson" data-subid="' + v.id + '">' + v.name + '</span>';
                                            html += ' </td>';
                                            html += '<td class="table-img-icon">';
                                            html += '<div class="delete-subject" data-subid="' + v.id + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Delete_worksheet.png" class="" alt="lesson" ></div>';
                                            html += '<div class="add-lesson-icon" data-lid="' + v.id + '" data-name="' + v.name + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Add_worksheet.png" class="" alt="lesson" ></div>';
                                            html += '<div class="detail-subject" data-subid="' + v.id + '" data-desc="' + v.description + '" ><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Detail_1.png" class="" alt="lesson" ></div>';
                                            html += '</td>';
                                            html += '</tr>';
                                            $("#table-my-subject").append(html);
                                        });
                                    }
                                } else {
                                    var html = '';
                                    html += '<tr><td colspan="4">No results.</td>';
                                    html += '</tr>';
                                    $("#table-my-subject").append(html);
                                }
                            });
                        });
                        $(".add-lesson-icon").live("click", function () {
                            var lid = $(this).attr("data-lid");
                            var name = $(this).attr("data-name");
                            $(".my-lesson-body").addClass(" hidden");
                            $("#add-worksheet-subject").removeClass("hidden");
                            $("#btn-add-less").attr("data-lid", lid);
                            $(".name-sub").text(name);
                            $(".lesson-detail-sub").removeClass("active");
                            $(".lesson-detail-sub").slideUp("fast");
                            $("#select-all-less4").attr("checked", false);
                            $("#icon-open-sub").attr("src", "<?php echo get_template_directory_uri(); ?>/library/images/Icon_Blue_Open.png");
                            $("#icon-open-sub").attr("src", "<?php echo get_template_directory_uri(); ?>/library/images/Icon_Blue_Open.png");
                        });
                        $("#icon-open-sub").live("click", function () {
                            var check = $(".lesson-detail-sub").hasClass("active");
                            if (check == false) {
                                $(".lesson-detail-sub").addClass(" active");
                                $(".lesson-detail-sub").slideDown("slow");
                                $("#icon-open-sub").attr("src", "<?php echo get_template_directory_uri(); ?>/library/images/Icon_Blue_Close.png");
                                $("#select-assign-sub").slideUp("fast");
                                $("#select-all-less2").attr("checked", false);
                                $("#option-detail-less2").addClass(" hidden");
                                var text = '';
                                $("#table-less2").html('');
                                var table = "table-less2";
                                get_my_lesson2(text, table);
                            } else {
                                $("#select-all-less4").attr("checked", false);
                                $(".lesson-detail-sub").removeClass("active");
                                $(".lesson-detail-sub").slideUp("slow");
                                $("#icon-open-sub").attr("src", "<?php echo get_template_directory_uri(); ?>/library/images/Icon_Blue_Open.png");
                                $("#table-less2").html('');
                            }
                        });
                        $("#close-add-subject").live("click", function () {
                            $("#add-worksheet-subject").addClass(' hidden');
                            $(".my-lesson-body").removeClass(' hidden');
                            $("#search-my-less-3").addClass("hidden");
                            $("#search-my-less").removeClass("hidden");
                            var search = '';
                            get_my_subject(search);
                        });
                        $("#search-my-less").click(function () {
                            $("#search-my-less").addClass("hidden");
                            $("#search-my-less-3").removeClass("hidden");
                            $("#select-assign-sub").slideDown("fast");
                        });
                        $("#search-my-less-3").click(function () {
                            $("#search-my-less-3").addClass("hidden");
                            $("#search-my-less").removeClass("hidden");
                            $("#select-assign-sub").slideUp("fast");
                        });
                        $("#icon-option-less2").click(function () {
                            var sort = $(this).attr("data-sort");
                            $.post(home_url + "/?r=ajax/get_my_lesson", {sort: sort}, function (data) {
                                $("#table-less2").html('');
                                if (data !== "0") {
                                    if (sort == "upward") {
                                        $("#icon-option-less2").attr("data-sort", "downward");
                                        $(".img-sort-lesson2").attr("src", "<?php echo get_template_directory_uri(); ?>/library/images/icon_Oldest.png");
                                    } else if (sort == "downward") {
                                        $("#icon-option-less2").attr("data-sort", "upward");
                                        $(".img-sort-lesson2").attr("src", "<?php echo get_template_directory_uri(); ?>/library/images/icon_Newest.png");
                                    }
                                    data = JSON.parse(data);
                                    if (data.lesson.length > 0) {
                                        $.each(data.lesson, function (i, v) {
                                            var html = '';
                                            html += '<tr id="lesson-tr' + v.id + '" class="lesson-tr">';
                                            html += '<td>';
                                            html += ' <div class="cb-type2">';
                                            html += ' <label>';
                                            html += '<input type="checkbox" class="radio_buttons class_cb_search option-input-2 option-input-lesson option-input-lesson-check radio" value="' + v.id + '" data-name="' + v.name + '" name="select-tr"/>';
                                            html += ' </label>';
                                            html += '</div>';
                                            html += ' </td>';
                                            html += '<td class="table-img-icon">';
                                            if (v.cate == 1) {
                                                html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_ENGLISH.png" alt="lesson" >';
                                            } else if (v.cate == 5) {
                                                html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_MATH.png" alt="lesson" >';
                                            } else if (v.cate == 2) {
                                                html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/Type_Science.png" alt="lesson" >';
                                            } else if (v.cate == 3) {
                                                html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/Type_History.png" alt="lesson" >';
                                            } else if (v.cate == 4) {
                                                html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/Type_Art_n_Design.png" alt="lesson" >';
                                            } else if (v.cate == 6) {
                                                html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/Type_Music.png" alt="lesson" >';
                                            } else if (v.cate == 7) {
                                                html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/Type_Others.png" alt="lesson" >';
                                            }
                                            html += '</td>';
                                            html += '<td class="table-img-icon">';
                                            html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Lesson.png" class="" alt="lesson" >';
                                            html += '<span class="name-lesson my-lesson-ws" data-lid="' + v.id + '">' + v.name + '</span>';
                                            html += ' </td>';
                                            html += '<td class="table-img-icon">';
                                            html += '<div class="delete-worksheet" data-lid="' + v.id + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Delete_worksheet.png" class="" alt="lesson" ></div>';
                                            html += '<div class="add-worksheet-ic" data-lid="' + v.id + '" data-name="' + v.name + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Add_worksheet.png" class="" alt="lesson" ></div>';
                                            html += '<div class="desc-my-lesson" data-lid="' + v.id + '" data-desc="' + v.description + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Detail_1.png" class="" alt="lesson" ></div>';
                                            html += '</td>';
                                            html += '</tr>';
                                            $("#table-less2").append(html);
                                        });
                                    }
                                } else {
                                    var html = '';
                                    html += '<tr><td colspan="4">No results.</td>';
                                    html += '</tr>';
                                    $("#table-less2").append(html);
                                }
                            });
                        });
                        $("#select-all-less4").click(function () {
                            if ($("#select-all-less4").is(":checked")) {
                                $(".option-input-lesson-check").attr("checked", true);
                            } else
                                $(".option-input-lesson-check").attr("checked", false);
                        });
                        $("#search-btn-sub").click(function () {
                            var search = $("#search-sub").val();
                            var tbody_my_lesson = "table-less2";

                            get_my_lesson2(search, tbody_my_lesson);
                        });
                        $(".delete-subject").live("click", function () {
                            var subid = $(this).attr("data-subid");
                            var sublid = $(this).attr("data-sublid");
                            var subject_id = $(this).attr("data-subject");
                            var html = '';
                            $("#yes-delete").attr("data-subid", subid);
                            $("#yes-delete").attr("data-sublid", sublid);
                            $("#yes-delete").attr("data-subject", subject_id);
                            html += 'Are you sure you want to delete it?';
                            $("#modal-alert-yesno .box-title-1").html(html);
                            $("#modal-alert-yesno").css('display', 'block');
                        });
                        //order lesson in my subject
                        $(".up-lesson,.down-lesson").live("click", function () {
                            var row = $(this).parents("tr:first");
                            var cls_prev = row.prev().hasClass("detail-lesson");
                            var cls_next = row.next().hasClass("detail-lesson");
                            var sublid = $(this).attr("data-sublid");
                            var subid = $(this).attr("data-subid");
                            if ($(this).is(".up-lesson")) {
                                if (cls_prev == true) {
                                    $.post(home_url + "/?r=ajax/update_order_lesson", {ordering: "up", sublid: sublid, subid: subid}, function (data) {
                                    });
                                    row.insertBefore(row.prev());
                                }
                            } else {
                                if (cls_next == true) {
                                    $.post(home_url + "/?r=ajax/update_order_lesson", {ordering: "down", sublid: sublid, subid: subid}, function (data) {
                                    });
                                    row.insertAfter(row.next());
                                }
                            }
                        });
                        //event for search button subject
                        $("#search-btn-subject").live("click", function () {
                            var search = $("#search-subject").val();
                            get_my_subject(search);
                        });
                        //add lesson in my subject
                        $("#btn-add-less").live("click", function () {
                            var lid = $(this).attr("data-lid");
                            var selected = new Array();
                            $(".option-input-lesson:checked").each(function () {
                                var id = $(this).val();
                                selected.push(id);
                            });
                            $.post(home_url + "/?r=ajax/add_my_subject", {subid: lid, lesson: selected}, function (data) {
                                if (data !== "0") {
                                    $(".my-lesson-body").removeClass("hidden");
                                    $("#add-worksheet-subject").addClass(" hidden");
                                    var html = '';
                                    html += 'Lessons successfully added to My subject.';
                                    $(".box-title-1").html(html);
                                    $("#modal-alert").css('display', 'block');
                                    $.post(home_url + "/?r=ajax/get_subject_lesson", {subid: lid}, function (data) {
                                        $('#subj-tr' + lid + '').nextAll(".detail-lesson").remove();
                                        data = JSON.parse(data);
                                        var html = '';
                                        if (data !== '') {
                                            if (data.length > 0) {
                                                $.each(data, function (i, v) {
                                                    html += '<tr class="detail-lesson detail-tr-1 detail-tr-lesson tr-lesson" id="tr-lesson-' + v.lesson_id + '" ><td></td>';
                                                    html += '<td></td>';
                                                    html += '<td class="table-img-icon lst-worksheet-lesson" style=" color:#ce851f !important; cursor:pointer;  " data-lid="' + v.lesson_id + '">';
                                                    html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Lesson.png" alt="lesson" style="margin-bottom: 2px;" >' + v.lesson + '</td>';
                                                    html += ' <td class="table-img-icon">';
                                                    html += '<div class="delete-subject" data-sublid="' + v.id + '" data-subject="' + v.subject_id + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Delete_worksheet.png" alt="lesson" ></div>';
                                                    html += ' <div class="detail-desc-lesson" data-id="' + v.lesson_id + '" data-desc="' + v.lesson_desc + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Detail_1.png" class="" alt="lesson" ></div>';
                                                    html += ' <div class="down-lesson" data-sublid="' + v.id + '" data-subid="' + v.subject_id + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Move_down.png" class="" alt="lesson" ></div>';
                                                    html += ' <div class="up-lesson" data-sublid="' + v.id + '" data-subid="' + v.subject_id + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Move_up.png" class="" alt="lesson" ></div>';
                                                    html += ' </td>';
                                                    html += '</tr>';
                                                });
                                                $('#subj-tr' + lid + '').after(html);
                                                $(".detail-lesson td").hide().slideDown("slow");
                                            }
                                        }
                                    });
                                }
                            });
                        });
                        $(".edit-success-lessonsub").live("click", function () {
                            var lid = $(this).attr("data-lid");
                            var text = $("#detailinput" + lid).val();
                            $('#tr-lesson-' + lid + '').nextAll(".desc-lesson-sub").remove();
                            $.post(home_url + "/?r=ajax/update_desc_lesson", {desc: text, lid: lid}, function (data) {
                                if (data !== '') {
                                    data = JSON.parse(data);
                                    var html = '';
                                    $.each(data, function (i, v) {
                                        html += '<tr class="desc-lesson-sub detail-tr-lesson set-hide desc-worksheet-' + v.id + '">';
                                        html += '<td></td>';
                                        html += '<td></td>';
                                        html += '<td colspan="2" class="input-detail lesson-detail-1 detail-input">';
                                        if (v.desc == "")
                                        {
                                            html += '<div class="desc-detail lesson-detail sub-detail" id="detaildesc' + v.id + '">&nbsp;</div>';
                                        } else
                                        {
                                            html += '<div class="desc-detail lesson-detail sub-detail" id="detaildesc' + v.id + '">' + v.desc + '</div>';
                                        }
                                        html += '<textarea rows="4" id="detailinput' + v.id + '" class="hidden" name="input-detail" >' + v.desc + '</textarea>';
                                        html += '<div class="table-img-icon edit-desc-worksheet" onclick="edit_desc(' + v.id + ')" id="editdesc' + v.id + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Edit.png" class="" alt="lesson" ></div>';
                                        html += '<div class="table-img-icon undo-desc" data-lid="' + v.id + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Undo.png" class="" alt="lesson" ></div>';
                                        html += '<div class="hidden table-img-icon edit-desc-worksheet edit-success-lessonsub"  id="editsuccess' + v.id + '" data-lid="' + v.id + '" ><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Edit_Done.png" class="" alt="lesson" ></div>';
                                        html += ' </td>';
                                        html += '</tr>';
                                        $('#tr-lesson-' + lid + '').after(html);
                                        $(".detail-tr-lesson td").hide().slideDown("slow");
                                    });
                                }
                            });
                        });
                        //list worksheet in my lesson
                        $(".my-lesson-ws").live("click", function () {
                            $(".lesson-tr").css("border-bottom", "1px solid #ddd");
                            var id = $(this).attr("data-lid");
                            var open = $(".lesson-tr").not('#lesson-tr' + id + '').next().hasClass("detail-tr-lesson");
                            if (open == true) {
                                $(".lesson-tr").not('#lesson-tr' + id + '').nextAll(".detail-tr-lesson").remove();
                            }
                            var html = '';
                            var check1 = $('#lesson-tr' + id + '').next().hasClass("detail-worksheet");
                            if (check1 == false) {
                                $.post(home_url + "/?r=ajax/get_my_lesson_sheet", {data: id}, function (data) {
                                    if (data !== "0") {
                                        data = JSON.parse(data);
                                        if (data.sheet.length > 0) {
                                            $.each(data.sheet, function (i, v) {
                                                html += '<tr class="detail-worksheet detail-tr-1 detail-tr-lesson tr-lesson set-hide" id="tr-lesson-' + v.sheet_id + '"><td></td>';
                                                html += '<td></td>';
                                                html += '<td class="table-img-icon">';
                                                html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Worksheet.png" class="" alt="lesson" style="margin-bottom: 2px;">' + v.sheet_name + '</td>';
                                                html += ' <td class="table-img-icon">';
                                                html += '<div class="delete-worksheet" data-slid="' + v.id + '" data-lid2="' + v.lesson_id + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Delete_worksheet.png" alt="lesson" ></div>';
                                                html += ' <div class="detail-worksheet-ls" data-id="' + v.sheet_id + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Detail_1.png" class="" alt="lesson" ></div>';
                                                html += ' <div class="down-worksheet" data-slid="' + v.id + '" data-lid="' + v.lesson_id + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Move_down.png" class="" alt="lesson" ></div>';
                                                html += ' <div class="up-worksheet" data-slid="' + v.id + '" data-lid="' + v.lesson_id + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Move_up.png" class="" alt="lesson" ></div>';
                                                html += ' </td>';
                                                html += '</tr>';
                                            });
                                        }
                                    } else {
                                        html += '<tr class="detail-worksheet detail-tr-1 detail-tr-lesson tr-lesson"><td></td><td></td><td>This folder is empty.</td></tr>';
                                    }
                                    var check2 = $('#lesson-tr' + id + '').next().hasClass("detail-desc");
                                    if (check2 == true) {
                                        var check3 = $('.detail-desc').next().hasClass("detail-worksheet");
                                        if (check3 == false) {
                                            $('.desc-detail').addClass("style-border-desc");
                                            $(".detail-desc").after(html);
                                            $(".detail-worksheet td").hide().slideDown("slow");
                                        } else if (check3 == true) {
                                            $('.desc-detail').removeClass("style-border-desc");
                                            $('.detail-worksheet td').slideUp("slow", function () {
                                                $('#lesson-tr' + id + '').nextAll(".set-hide").remove();
                                            });
                                        }
                                    } else {
                                        $('#lesson-tr' + id + '').after(html);
                                        $(".detail-tr-lesson td").hide().slideDown("slow");
                                    }
                                });
                            } else {
                                $('.desc-detail').removeClass("style-border-desc");
                                $('.detail-tr-lesson td').slideUp("slow", function () {
                                    $('#lesson-tr' + id + '').nextAll(".set-hide").remove();
                                });
                            }
                        });
                        // show description lesson in my Lesson
                        $(".desc-my-lesson").live("click", function () {
                            var id = $(this).attr("data-lid");
                            var desc = $(this).attr("data-desc");
                            var open = $(".lesson-tr").not('#lesson-tr' + id + '').next().hasClass("detail-tr-lesson");
                            if (open == true) {
                                $(".lesson-tr").not('#lesson-tr' + id + '').nextAll(".detail-tr-lesson").remove();
                            }
                            var check1 = $('#lesson-tr' + id + '').next().hasClass("detail-desc");
                            if (check1 == false) {
                                var html = '';
                                html += '<tr class="detail-desc detail-tr-lesson desc-worksheet set-hide desc-worksheet-' + id + '" >';
                                html += '<td></td>';
                                html += '<td></td>';
                                html += '<td colspan="2" class="input-detail-lesson">';
                                var check2 = $('#lesson-tr' + id + '').next().hasClass("detail-worksheet");
                                if (check2 == true) {
                                    html += '<div class="desc-detail  style-border-desc" id="detaildesc' + id + '">' + desc + '</div>';
                                } else {
                                    html += '<div class="desc-detail" id="detaildesc' + id + '">' + desc + '</div>';
                                }
                                html += '<textarea rows="4" id="detailinput' + id + '" class="hidden" name="input-detail" >' + desc + '</textarea>';
                                html += '<div class="table-img-icon edit-desc-worksheet"  onclick="edit_desc(' + id + ')" id="editdesc' + id + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Edit.png" class="" alt="lesson" ></div>';
                                html += '<div class="table-img-icon undo-desc" data-lid="' + id + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Undo.png" class="" alt="lesson" ></div>';
                                html += '<div class="hidden table-img-icon edit-desc-worksheet edit-success-ls"  id="editsuccess' + id + '" data-lid="' + id + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Edit_Done.png" class="" alt="lesson" ></div>';
                                html += ' </td>';
                                html += '</tr>';
                                $('#lesson-tr' + id + '').after(html);
                                $(".detail-desc td").hide().slideDown("slow");
                            } else {
                                $('.detail-desc td').slideUp("slow", function () {
                                    $('#lesson-tr' + id + '').nextAll(".detail-desc").remove();
                                });
                            }
                        });
                        function get_groups_home_eng(text, table) {
                            var tbody_public_eng = $("#" + table + "");
                            tbody_public_eng.html("");
                            $.post(home_url + "/?r=ajax/get_groups_home_eng", {type: "GET"}, function (data) {
                                var dt = JSON.parse(data);
                                $.each(dt.arr, function (i, v) {
                                    var html = '';
                                    html += '<tr id="eng-tr' + v.id + '" class="eng-tr">\n\
                                                            <td >\n\
                                                                <div class="cb-type2">\n\
                                                                    <label>\n\
                                                                        <input type="checkbox" class="radio_buttons class_cb_search option-input-2 option-input-lesson option-input-ready-eng radio" value="' + v.id + '" data-cate="1" data-name="' + v.name + '" data-desc="' + v.desc + '" data-price="' + v.price + '" name="select-tr"/>\n\
                                                                    </label>\n\
                                                                </div>\n\
                                                            </td>';
                                    html += '<td class="table-img-icon">';
                                    if (v.cate == 'English') {
                                        html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_ENGLISH.png" alt="lesson" >';
                                    }
                                    html += '</td>';
                                    html += ' <td class="table-img-icon">';
                                    html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Ready-made_lesson.png" class="" alt="lesson" >';
                                    html += '<span class="name-ready-lesson eng-name" data-gid="' + v.id + '">' + v.name + '</span>';
                                    html += '</td>';
                                    html += '<td class="table-img-icon">';
                                    if (v.price == 0) {
                                        html += '<p class="free-lesson">FREE</p>';
                                    } else {
                                        html += '<p class="pay-lesson"><span>$</span>' + v.price + '</p>';
                                    }
                                    html += '</td>';
                                    html += '<td class="table-img-icon">';
                                    html += ' <div class="open-arrow-ready-eng" data-gid="' + v.id + '" data-content="' + v.desc + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Detail_1.png" class="" alt="lesson" ></div>';
                                    html += ' </td>';
                                    html += '</tr>';
                                    tbody_public_eng.append(html);
                                });
                            });
                        }
                        function get_groups_home_math(text, table) {
                            var tbody_public_math = $("#" + table + "");
                            tbody_public_math.html("");
                            $.post(home_url + "/?r=ajax/get_groups_home_math", {type: "GET"}, function (data) {
                                var dt = JSON.parse(data);
                                $.each(dt.arr, function (i, v) {
                                    var html = '';
                                    html += '<tr id="math-tr' + v.id + '" class="math-tr">\n\
                                                            <td >\n\
                                                                <div class="cb-type2">\n\
                                                                    <label>\n\
                                                                        <input type="checkbox" class="radio_buttons class_cb_search option-input-2 option-input-lesson option-input-ready-eng radio" value="' + v.id + '" data-cate="5" data-name="' + v.name + '" data-desc="' + v.desc + '" data-price="' + v.price + '" name="select-tr"/>\n\
                                                                    </label>\n\
                                                                </div>\n\
                                                            </td>';
                                    html += '<td class="table-img-icon">';
                                    if (v.cate == 'Math') {
                                        html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_MATH.png" alt="lesson" >';
                                    }
                                    html += '</td>';
                                    html += ' <td class="table-img-icon">';
                                    html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Ready-made_lesson.png" class="" alt="lesson" >';
                                    html += '<span class="name-ready-lesson math-name" data-gid="' + v.id + '">' + v.name + '</span>';
                                    html += '</td>';
                                    html += '<td class="table-img-icon">';
                                    if (v.price == 0) {
                                        html += '<p class="free-lesson">FREE</p>';
                                    } else {
                                        html += '<p class="pay-lesson"><span>$</span>' + v.price + '</p>';
                                    }
                                    html += '</td>';
                                    html += '<td class="table-img-icon">';
                                    html += ' <div class="open-arrow-ready-math" data-gid="' + v.id + '" data-content="' + v.desc + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Detail_1.png" class="" alt="lesson" ></div>';
                                    html += ' </td>';
                                    html += '</tr>';
                                    tbody_public_math.append(html);
                                });
                            });
                        }
                        $("#eng-menu").click(function () {
                            if ($("#eng-public").hasClass("hidden"))
                            {
                                $("#eng-public").removeClass("hidden");
                                $("#eng-public").addClass("active");
                                $("#math-public").removeClass("active");
                                $("#math-public").addClass("hidden");
                            }
                            $("#select-all-ready-eng").attr("checked", false);
                            $("#open-search-ready-eng").css('display', 'none');
                            if ($("#btn-ready-eng-down").hasClass("btn-ready-lesson-up"))
                            {
                                $("#btn-ready-eng-down").removeClass("btn-ready-lesson-up");
                                $("#btn-ready-eng-down").addClass("btn-ready-lesson-down");
                            }
                            $("#search-ready-les-eng").val('');
                            var text = '';
                            var table = "table-ready-lesson-eng";
                            get_groups_home_eng(text, table);
                        });
                        $("#math-menu").click(function () {
                            $("#select-all-ready-eng").attr("checked", false);

                            var text = '';
                            var table = "table-ready-lesson-eng";
                            get_groups_home_math(text, table);
                        });
                        $("#btn-ready-eng-down").click(function () {
                            if ($("#btn-ready-eng-down").hasClass("btn-ready-lesson-down")) {
                                $("#btn-ready-eng-down").removeClass("btn-ready-lesson-down");
                                $("#btn-ready-eng-down").addClass("btn-ready-lesson-up");
                                $("#open-search-ready-eng").slideDown("fast");
                            } else {
                                $("#btn-ready-eng-down").removeClass("btn-ready-lesson-up");
                                $("#btn-ready-eng-down").addClass("btn-ready-lesson-down");
                                $("#open-search-ready-eng").slideUp("fast");
                            }
                        });

                        $("#select-all-ready-eng").click(function () {
                            if ($("#select-all-ready-eng").is(":checked")) {

                                $(".option-input-ready-eng").attr("checked", true);
                            } else
                                $(".option-input-ready-eng").attr("checked", false);
                        });

                        $(".eng-name").live("click", function () {
                            $(".eng-tr").css("border-bottom", "1px solid #ddd");
                            var id = $(this).attr("data-gid");
                            var open = $(".eng-tr").not('#eng-tr' + id + '').next().hasClass("detail-tr-lesson");
                            if (open == true) {
                                $(".eng-tr").not('#eng-tr' + id + '').nextAll(".detail-tr-lesson").remove();
                            }
                            var html = '';
                            var check1 = $('#eng-tr' + id + '').next().hasClass("detail-worksheet");
                            if (check1 == false) {
                                $.post(home_url + "/?r=ajax/get_ready_lesson_sheet", {data: id}, function (data) {
                                    if (data !== "0") {
                                        var data = JSON.parse(data);
                                        if (data.sheet.length > 0) {
                                            $.each(data.sheet, function (i, v) {
                                                html += '<tr class="detail-worksheet detail-tr-1 detail-tr-lesson tr-lesson set-hide" id="eng-lesson-' + v.id + '" ><td></td>';
                                                html += '<td></td>';
                                                html += '<td class="table-img-icon">';
                                                html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Worksheet.png" class="" alt="lesson" style="margin-bottom: 2px;">' + v.sheet_name + '</td>';
                                                html += ' <td class="table-img-icon" colspan="2">';
                                                html += ' <div class="detail-worksheet-ready" data-id="' + v.id + '" data-gid="' + id + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Detail_1.png" class="" alt="lesson" ></div>';
                                                html += ' <div class="down-worksheet" data-slid="' + v.id + '" ><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Move_down.png" class="" alt="lesson" ></div>';
                                                html += ' <div class="up-worksheet" data-slid="' + v.id + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Move_up.png" class="" alt="lesson" ></div>';
                                                html += ' </td>';
                                                html += '</tr>';
                                            });
                                        }
                                    } else {
                                        html += '<tr class="detail-worksheet detail-tr-1 detail-tr-lesson tr-lesson"><td></td><td></td><td>This folder is empty.</td></tr>';
                                    }
                                    var check2 = $('#eng-tr' + id + '').next().hasClass("detail-desc");
                                    if (check2 == true) {
                                        var check3 = $('.detail-desc').next().hasClass("detail-worksheet");
                                        if (check3 == false) {
                                            $('.desc-detail').addClass("style-border-desc");
                                            $(".detail-desc").after(html);
                                            $(".detail-worksheet td").hide().slideDown("slow");
                                        } else if (check3 == true) {
                                            $('.desc-detail').removeClass("style-border-desc");
                                            $('.desc-worksheet td').slideUp("slow", function () {
                                                $('#eng-tr' + id + '').nextAll(".set-hide").remove();
                                            });
                                        }
                                    } else {
                                        $('#eng-tr' + id + '').after(html);
                                        $(".detail-tr-lesson td").hide().slideDown("slow");
                                    }
                                });
                            } else {
                                $('.desc-detail').removeClass("style-border-desc");
                                $('.detail-tr-lesson td').slideUp("slow", function () {
                                    $('#eng-tr' + id + '').nextAll(".set-hide").remove();
                                });
                            }
                        });
                        $(".math-name").live("click", function () {
                            $(".math-tr").css("border-bottom", "1px solid #ddd");
                            var id = $(this).attr("data-gid");
                            var open = $(".math-tr").not('#math-tr' + id + '').next().hasClass("detail-tr-lesson");
                            if (open == true) {
                                $(".math-tr").not('#math-tr' + id + '').nextAll(".detail-tr-lesson").remove();
                            }
                            var html = '';
                            var check1 = $('#math-tr' + id + '').next().hasClass("detail-worksheet");
                            if (check1 == false) {
                                $.post(home_url + "/?r=ajax/get_ready_lesson_sheet", {data: id}, function (data) {
                                    if (data !== "0") {
                                        var data = JSON.parse(data);
                                        if (data.sheet.length > 0) {
                                            $.each(data.sheet, function (i, v) {
                                                html += '<tr class="detail-worksheet detail-tr-1 detail-tr-lesson tr-lesson set-hide" id="math-lesson-' + v.id + '" ><td></td>';
                                                html += '<td></td>';
                                                html += '<td class="table-img-icon">';
                                                html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Worksheet.png" class="" alt="lesson" style="margin-bottom: 2px;">' + v.sheet_name + '</td>';
                                                html += ' <td class="table-img-icon" colspan="2">';
                                                html += ' <div class="detail-worksheet-ready" data-id="' + v.id + '" data-gid="' + id + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Detail_1.png" class="" alt="lesson" ></div>';
                                                html += ' <div class="down-worksheet" data-slid="' + v.id + '" ><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Move_down.png" class="" alt="lesson" ></div>';
                                                html += ' <div class="up-worksheet" data-slid="' + v.id + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Move_up.png" class="" alt="lesson" ></div>';
                                                html += ' </td>';
                                                html += '</tr>';
                                            });
                                        }
                                    } else {
                                        html += '<tr class="detail-worksheet detail-tr-1 detail-tr-lesson tr-lesson"><td></td><td></td><td>This folder is empty.</td></tr>';
                                    }
                                    var check2 = $('#math-tr' + id + '').next().hasClass("detail-desc");
                                    if (check2 == true) {
                                        var check3 = $('.detail-desc').next().hasClass("detail-worksheet");
                                        if (check3 == false) {
                                            $('.desc-detail').addClass("style-border-desc");
                                            $(".detail-desc").after(html);
                                            $(".detail-worksheet td").hide().slideDown("slow");
                                        } else if (check3 == true) {
                                            $('.desc-detail').removeClass("style-border-desc");
                                            $('.desc-worksheet td').slideUp("slow", function () {
                                                $('#math-tr' + id + '').nextAll(".set-hide").remove();
                                            });
                                        }
                                    } else {
                                        $('#math-tr' + id + '').after(html);
                                        $(".detail-tr-lesson td").hide().slideDown("slow");
                                    }
                                });
                            } else {
                                $('.desc-detail').removeClass("style-border-desc");
                                $('.detail-tr-lesson td').slideUp("slow", function () {
                                    $('#math-tr' + id + '').nextAll(".set-hide").remove();
                                });
                            }
                        });
                        $("#search-ready-btn-eng").click(function () {
                            var search = $("#search-ready-les-eng").val();
                            if ($("#menu-eng").hasClass("active")) {

                                var tbody_ready_eng = $("#table-ready-lesson-eng");
                                tbody_ready_eng.html("");
                                $.post(home_url + "/?r=ajax/get_groups_home_eng", {search: search}, function (data) {
                                    if (data == 0) {
                                        var tr_m = '<tr><td colspan="6">No results</td></tr>';
                                        tbody_ready_eng.append(tr_m);
                                    } else {
                                        var dt = JSON.parse(data);
                                        $.each(dt.arr, function (i, v) {
                                            var html = '';
                                            html += '<tr id="eng-tr' + v.id + '" class="eng-tr">\n\
                                                                    <td >\n\
                                                                        <div class="cb-type2">\n\
                                                                            <label>\n\
                                                                                <input type="checkbox" class="radio_buttons class_cb_search option-input-2 option-input-lesson option-input-ready-eng radio" value="' + v.id + '" data-cate="1" data-name="' + v.name + '" data-desc="' + v.content + '" name="select-tr"/>\n\
                                                                            </label>\n\
                                                                        </div>\n\
                                                                    </td>';
                                            html += '<td class="table-img-icon">';
                                            if (v.cate == 'English') {
                                                html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_ENGLISH.png" alt="lesson" >';
                                            }
                                            html += '</td>';
                                            html += ' <td class="table-img-icon">';
                                            html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Ready-made_lesson.png" class="" alt="lesson" >';
                                            html += '<span class="name-ready-lesson eng-name" data-gid="' + v.id + '">' + v.name + '</span>';
                                            html += '</td>';
                                            html += '<td class="table-img-icon">';
                                            if (v.price == 0) {
                                                html += '<p class="free-lesson">FREE</p>';
                                            } else {
                                                html += '<p class="pay-lesson"><span>$</span>' + v.price + '</p>';
                                            }
                                            html += '</td>';
                                            html += '<td class="table-img-icon">';
                                            html += ' <div class="open-arrow-ready-eng" data-gid="' + v.id + '" data-content="' + v.content + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Detail_1.png" class="" alt="lesson" ></div>';
                                            html += ' </td>';
                                            html += '</tr>';
                                            tbody_ready_eng.append(html);
                                        });
                                    }
                                });
                            }
                            if ($("#menu-math").hasClass("active")) {
                                var tbody_ready_math = $("#table-ready-lesson-eng");
                                tbody_ready_math.html("");
                                $.post(home_url + "/?r=ajax/get_groups_home_math", {search: search}, function (data) {
                                    if (data == 0) {
                                        var tr_m = '<tr><td colspan="6">No results</td></tr>';
                                        tbody_ready_math.append(tr_m);
                                    } else {
                                        var dt = JSON.parse(data);
                                        $.each(dt.arr, function (i, v) {
                                            var html = '';
                                            html += '<tr id="math-tr' + v.id + '" class="math-tr">\n\
                                                                    <td >\n\
                                                                        <div class="cb-type2">\n\
                                                                            <label>\n\
                                                                                <input type="checkbox" class="radio_buttons class_cb_search option-input-2 option-input-lesson option-input-ready-eng radio" value="' + v.id + '" name="select-tr"/>\n\
                                                                            </label>\n\
                                                                        </div>\n\
                                                                    </td>';
                                            html += '<td class="table-img-icon">';
                                            if (v.cate == 'Math') {
                                                html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_MATH.png" alt="lesson" >';
                                            }
                                            html += '</td>';
                                            html += ' <td class="table-img-icon">';
                                            html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Ready-made_lesson.png" class="" alt="lesson" >';
                                            html += '<span class="name-ready-lesson math-name" data-gid="' + v.id + '">' + v.name + '</span>';
                                            html += '</td>';
                                            html += '<td class="table-img-icon">';
                                            if (v.price == 0) {
                                                html += '<p class="free-lesson">FREE</p>';
                                            } else {
                                                html += '<p class="pay-lesson"><span>$</span>' + v.price + '</p>';
                                            }
                                            html += '</td>';
                                            html += '<td class="table-img-icon">';
                                            html += ' <div class="open-arrow-ready-math" data-gid="' + v.id + '" data-content="' + v.content + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Detail_1.png" class="" alt="lesson" ></div>';
                                            html += ' </td>';
                                            html += '</tr>';
                                            tbody_ready_math.append(html);
                                        });
                                    }
                                });
                            }

                        });

                        $("#icon-option-ready-eng").click(function () {
                            var sort = $(this).attr("data-sort");
                            if ($("#menu-eng").hasClass("active")) {
                                $.post(home_url + "/?r=ajax/get_groups_home_eng", {sort: sort}, function (data) {
                                    $("#table-ready-lesson-eng").html('');
                                    if (data !== "0") {
                                        if (sort == "upward") {
                                            $("#icon-option-ready-eng").attr("data-sort", "downward");
                                            $(".img-sort-eng").attr("src", "<?php echo get_template_directory_uri(); ?>/library/images/icon_Oldest.png");
                                        } else if (sort == "downward") {
                                            $("#icon-option-ready-eng").attr("data-sort", "upward");
                                            $(".img-sort-eng").attr("src", "<?php echo get_template_directory_uri(); ?>/library/images/icon_Newest.png");
                                        }
                                        var dt = JSON.parse(data);
                                        if (dt.arr.length > 0) {
                                            $.each(dt.arr, function (i, v) {
                                                var html = '';
                                                html += '<tr id="eng-tr' + v.id + '" class="eng-tr">\n\
                                                                <td >\n\
                                                                    <div class="cb-type2">\n\
                                                                        <label>\n\
                                                                            <input type="checkbox" class="radio_buttons class_cb_search option-input-2 option-input-lesson option-input-ready-eng radio" value="' + v.id + '" data-cate="1" data-name="' + v.name + '" data-desc="' + v.content + '" name="select-tr"/>\n\
                                                                        </label>\n\
                                                                    </div>\n\
                                                                </td>';
                                                html += '<td class="table-img-icon">';
                                                if (v.cate == 'English') {
                                                    html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_ENGLISH.png" alt="lesson" >';
                                                }
                                                html += '</td>';
                                                html += ' <td class="table-img-icon">';
                                                html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Ready-made_lesson.png" class="" alt="lesson" >';
                                                html += '<span class="name-ready-lesson eng-name" data-gid="' + v.id + '">' + v.name + '</span>';
                                                html += '</td>';
                                                html += '<td class="table-img-icon">';
                                                if (v.price == 0) {
                                                    html += '<p class="free-lesson">FREE</p>';
                                                } else {
                                                    html += '<p class="pay-lesson"><span>$</span>' + v.price + '</p>';
                                                }
                                                html += '</td>';
                                                html += '<td class="table-img-icon">';
                                                html += ' <div class="open-arrow-ready-eng" data-gid="' + v.id + '" data-content="' + v.content + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Detail_1.png" class="" alt="lesson" ></div>';
                                                html += ' </td>';
                                                html += '</tr>';
                                                $("#table-ready-lesson-eng").append(html);
                                            });
                                        }
                                    } else {
                                        var html = '';
                                        html += '<tr><td colspan="4">No results.</td>';
                                        html += '</tr>';
                                        $("#table-ready-lesson-eng").append(html);
                                    }
                                });
                            }
                            if ($("#menu-math").hasClass("active")) {
                                $.post(home_url + "/?r=ajax/get_groups_home_math", {sort: sort}, function (data) {
                                    $("#table-ready-lesson-eng").html('');
                                    if (data !== "0") {
                                        if (sort == "upward") {
                                            $("#icon-option-ready-eng").attr("data-sort", "downward");
                                            $(".img-sort-eng").attr("src", "<?php echo get_template_directory_uri(); ?>/library/images/icon_Oldest.png");
                                        } else if (sort == "downward") {
                                            $("#icon-option-ready-eng").attr("data-sort", "upward");
                                            $(".img-sort-eng").attr("src", "<?php echo get_template_directory_uri(); ?>/library/images/icon_Newest.png");
                                        }
                                        var dt = JSON.parse(data);
                                        $.each(dt.arr, function (i, v) {
                                            var html = '';
                                            html += '<tr id="math-tr' + v.id + '" class="math-tr">\n\
                                                                <td >\n\
                                                                    <div class="cb-type2">\n\
                                                                        <label>\n\
                                                                            <input type="checkbox" class="radio_buttons class_cb_search option-input-2 option-input-lesson option-input-ready-eng radio" value="' + v.id + '" name="select-tr"/>\n\
                                                                        </label>\n\
                                                                    </div>\n\
                                                                </td>';
                                            html += '<td class="table-img-icon">';
                                            if (v.cate == 'Math') {
                                                html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_MATH.png" alt="lesson" >';
                                            }
                                            html += '</td>';
                                            html += ' <td class="table-img-icon">';
                                            html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Ready-made_lesson.png" class="" alt="lesson" >';
                                            html += '<span class="name-ready-lesson math-name" data-gid="' + v.id + '">' + v.name + '</span>';
                                            html += '</td>';
                                            html += '<td class="table-img-icon">';
                                            if (v.price == 0) {
                                                html += '<p class="free-lesson">FREE</p>';
                                            } else {
                                                html += '<p class="pay-lesson"><span>$</span>' + v.price + '</p>';
                                            }
                                            html += '</td>';
                                            html += '<td class="table-img-icon">';
                                            html += ' <div class="open-arrow-ready-math" data-gid="' + v.id + '" data-content="' + v.content + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Detail_1.png" class="" alt="lesson" ></div>';
                                            html += ' </td>';
                                            html += '</tr>';
                                            $("#table-ready-lesson-eng").append(html);
                                        });
                                    } else {
                                        var html = '';
                                        html += '<tr><td colspan="4">No results.</td>';
                                        html += '</tr>';
                                        $("#table-ready-lesson-math").append(html);
                                    }
                                });
                            }
                        });

                        $("#tab-public-les-ess").click(function () {
                            $("#eng-math").removeClass("active");
                            $("#eng-math").addClass("hidden");
                            $("#dic-sel-stu").removeClass("hidden");
                            $("#dic-sel-stu").addClass("active");
                            $("#dict-public").removeClass("hidden");
                            $("#dict-public").addClass("active");
                            $("#eng-public").removeClass("active");
                            $("#eng-public").addClass("hidden");
                            $("#math-public").removeClass("active");
                            $("#math-public").addClass("hidden");
                            $("#sat").removeClass("active");
                            $("#sat").addClass("hidden");
                            $(".eng-math-les").not('#dic-sel-stu').addClass("hidden");
                            $(".eng-math-les").not('#dic-sel-stu').removeClass("active");
                            var check = $("#btn-dict-down").hasClass("btn-ready-lesson-up");
                            if (check == true)
                            {
                                $("#btn-dict-down").removeClass("btn-ready-lesson-up");
                                $("#btn-dict-down").addClass("btn-ready-lesson-down");
                            }
                            if ($("#self-study").hasClass("active")) {
                                $("#self-study").removeClass("active");
                                $("#dict").addClass("active");
                            }
                        });
                        $("#dict-public").click(function () {
                            if ($("#btn-dict-down").hasClass("btn-ready-lesson-up"))
                            {
                                $("#btn-dict-down").removeClass("btn-ready-lesson-up");
                                $("#btn-dict-down").addClass("btn-ready-lesson-down");
                            }
                        });
                        $("#btn-dict-down").click(function () {
                            var check = $("#btn-dict-down").hasClass("btn-ready-lesson-down");
                            if (check == true) {
                                $("#btn-dict-down").removeClass("btn-ready-lesson-down");
                                $("#btn-dict-down").addClass("btn-ready-lesson");
                                $("#open-search-dictionary").slideDown("fast");
                            } else {
                                $("#btn-dict-down").removeClass("btn-ready-lesson");
                                $("#btn-dict-down").addClass("btn-ready-lesson-down");
                                $("#open-search-dictionary").slideUp("fast");
                            }
                        });
                        //dictonary's event
                        $("#dictionary-menu").click(function () {
                            $("#select-all-dict").attr("checked", false);
                            $("#btn-dict-down").removeClass("btn-ready-lesson");
                            $("#btn-dict-down").addClass("btn-ready-lesson-down");
                            $("#open-search-dictionary").slideUp("fast");
                            $("#text-search-essential").val('');
                            var text = '';
                            get_dictionaries(text);
                            $("#select-all-dict").attr("checked", false);
                        });
                        //fucntion get dictionaries
                        function get_dictionaries(text) {
                            $("#table-dict").html("");
                            $.post(home_url + "/?r=ajax/get_dictionaries", {search: text}, function (data) {
                                if (data != 0) {
                                    data = JSON.parse(data);

                                    $.each(data, function (i, v) {
                                        var html = '';
                                        html += '<tr id="math-tr' + v.id + '" class="math-tr">\n\
                                                                <td >\n\
                                                                    <div class="cb-type2">\n\
                                                                        <label>\n\
                                                                            <input type="checkbox" class="radio_buttons class_cb_search option-input-2 option-input-lesson option-input-dict radio" value="' + v.id + '" data-cate="8" data-name="' + v.name + '" data-desc="" data-price="3" name="select-tr"/>\n\
                                                                        </label>\n\
                                                                    </div>\n\
                                                                </td>';
                                        html += '<td class="table-img-icon">';
                                        html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Dictionary.png" alt="lesson" >';
                                        html += '</td>';
                                        html += ' <td class="table-img-icon">';
                                        html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Ready-made_lesson.png" class="" alt="lesson" >';
                                        html += '<span class="name-ready-lesson" data-gid="' + v.id + '">' + v.name + '</span>';
                                        html += '</td>';
                                        html += '<td class="table-img-icon">';
                                        html += '<p class="pay-lesson"><span>$</span>3</p>';
                                        html += '</td>';
                                        html += '<td class="table-img-icon">';
                                        html += '<div class="open-arrow-dict" data-gid="' + v.id + '" data-content="' + v.content + '"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Detail_1.png" class="" alt="lesson" ></div>';
                                        html += '</td>';

                                        html += '</tr>';
                                        $("#table-dict").append(html);
                                    });
                                } else {
                                    var html = '';
                                    html += '<tr id="math-tr" class="math-tr"><td></td><td colspan="3">No result.</td></tr>';
                                    $("#table-dict").append(html);
                                }

                            });
                        }

                        $("#ess-tab").click(function () {
                            var text = '';
                            get_dictionaries(text);
                            $("#select-all-dict").attr("checked", false);
                            $(".set-hide-tab").not("#dict-public").addClass("hidden");
                            $("#btn-dict-down").removeClass("btn-ready-lesson");
                            $("#btn-dict-down").addClass("btn-ready-lesson-down");
                            $("#open-search-dictionary").slideUp("fast");
                        });
                        $("#sat-tab").click(function () {

                            $("#select-all-dict").attr("checked", false);
                            $(".set-hide-tab").not("#sat-public").addClass("hidden");
                        });
                        //self-study-menu's event
                        $("#self-study-menu").click(function () {
                            $("#select-all-dict").attr("checked", false);
                            $("#btn-dict-down").removeClass("btn-ready-lesson");
                            $("#btn-dict-down").addClass("btn-ready-lesson-down");
                            $("#open-search-dictionary").slideUp("fast");
                            $("#text-search-essential").val('');
                            $("#table-dict").html("");

                            var html = '';
                            html += '<tr id="math-tr" class="math-tr">\n\
                                                                <td >\n\
                                                                    <div class="cb-type2">\n\
                                                                        <label>\n\
                                                                            <input type="checkbox" class="radio_buttons class_cb_search option-input-2 option-input-lesson option-input-dict radio" value="" data-cate="9" data-name="English Self-study (includes 1 dictionary of choice)" data-desc="" data-price="10" name="select-tr"/>\n\
                                                                        </label>\n\
                                                                    </div>\n\
                                                                </td>';
                            html += '<td class="table-img-icon">';
                            html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Self-study.png" alt="lesson" >';
                            html += '</td>';
                            html += ' <td class="table-img-icon">';
                            html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Ready-made_lesson.png" class="" alt="lesson" >';
                            html += '<span class="name-ready-lesson" > English Self-study (includes 1 dictionary of choice)</span>';
                            html += '</td>';
                            html += '<td class="table-img-icon">';
                            html += '<p class="pay-lesson"><span>$</span>10</p>';
                            html += '</td>';
                            html += '<td class="table-img-icon">';
                            html += '<div class="open-arrow-sel" data-gid="" data-content=""><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Detail_1.png" class="" alt="lesson" ></div>';
                            html += '</td>';

                            html += '</tr>';
                            html += '<tr id="math-tr" class="math-tr">\n\
                                                                <td >\n\
                                                                    <div class="cb-type2">\n\
                                                                        <label>\n\
                                                                            <input type="checkbox" class="radio_buttons class_cb_search option-input-2 option-input-lesson option-input-dict radio" value="" data-cate="9" data-name="Math Self-study" data-desc="" data-price="60" name="select-tr"/>\n\
                                                                        </label>\n\
                                                                    </div>\n\
                                                                </td>';
                            html += '<td class="table-img-icon">';
                            html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Self-study.png" alt="lesson" >';
                            html += '</td>';
                            html += ' <td class="table-img-icon">';
                            html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Ready-made_lesson.png" class="" alt="lesson" >';
                            html += '<span class="name-ready-lesson" >Math Self-study  </span>';
                            html += '</td>';
                            html += '<td class="table-img-icon">';
                            html += '<p class="pay-lesson"><span>$</span>60</p>';
                            html += '</td>';
                            html += '<td class="table-img-icon">';
                            html += '<div class="open-arrow-sel" data-gid="" data-content=""><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Detail_1.png" class="" alt="lesson" ></div>';
                            html += '</td>';

                            html += '</tr>';
                            $("#table-dict").append(html);


                        });
                        $("#cri-tab").click(function () {
                            $(".set-hide-tab").not("#eng-public").addClass("hidden");
                            $(".eng-math-les").not('#eng-math').addClass("hidden");
                            $(".eng-math-les").not('#eng-math').removeClass("active");
                        });
                        $("#ready-lesson-li").click(function () {
                            $(".set-hide-tab").not("#eng-public").addClass("hidden");
                            $.post(home_url + "/?r=ajax/ik_check_user_student", {type: "GET"}, function (data) {
                                if (data !== 'T-Qual' && data !== 'T-Reg' && data !== 'T-M-Reg' && data !== 'T-M-Qual' && data !== 'Teacher') {

                                    $(".save-to-lesson").addClass("hidden");
                                }
                            });
                        });
                        $("#select-all-dict").click(function () {
                            if ($("#select-all-dict").is(":checked")) {

                                $(".option-input-dict").attr("checked", true);
                            } else
                                $(".option-input-dict").attr("checked", false);
                        });
                        $("#tab-public-les-sat").click(function () {
                            $("#eng-public").removeClass("active");
                            $("#eng-public").addClass("hidden");
                            $("#math-public").removeClass("active");
                            $("#math-public").addClass("hidden");
                            $("#dict-public").removeClass("active");
                            $("#dict-public").addClass("hidden");
                            $("#sat-public").removeClass("hidden");
                            $("#sat-public").addClass("active");
                            $("#eng-math").removeClass("active");
                            $("#eng-math").addClass("hidden");
                            $("#dic-sel-stu").removeClass("active");
                            $("#dic-sel-stu").addClass("hidden");
                            $("#sat").removeClass("hidden");
                            $("#sat").addClass("active");
                            $(".eng-math-les").not('#sat').addClass("hidden");
                            $(".eng-math-les").not('#sat').removeClass("active");
                            if ($("#sat-math").hasClass("active")) {
                                $("#sat-math").removeClass("active");
                                $("#sat-eng").addClass("active");
                            }
                            if ($("#btn-sat-down").hasClass("btn-ready-lesson-up"))
                            {
                                $("#btn-sat-down").removeClass("btn-ready-lesson-up");
                                $("#btn-sat-down").addClass("btn-ready-lesson-down");
                            }
                            var check = $("#btn-sat-down").hasClass("btn-ready-lesson-up");
                            if (check == true)
                            {
                                $("#btn-sat-down").removeClass("btn-ready-lesson-up");
                                $("#btn-sat-down").addClass("btn-ready-lesson-down");
                            }
                        });
                        $("#btn-sat-down").click(function () {
                            var check = $("#btn-sat-down").hasClass("btn-ready-lesson-down");
                            if (check == true) {
                                $("#btn-sat-down").removeClass("btn-ready-lesson-down");
                                $("#btn-sat-down").addClass("btn-ready-lesson");
                                $("#open-search-sat").slideDown("fast");
                            } else {
                                $("#btn-sat-down").removeClass("btn-ready-lesson");
                                $("#btn-sat-down").addClass("btn-ready-lesson-down");
                                $("#open-search-sat").slideUp("fast");
                            }
                        });
                        //button search for Essential
                        $("#search-essential-btn").click(function () {
                            var text = $("#text-search-essential").val();
                            
                            if ($("#dict").hasClass("active")) {
                                get_dictionaries(text);
                            
                        }else if($("#self-study").hasClass('active')){
                            var arr=new Array( {
                                            'name': 'English Self-study (includes 1 dictionary of choice)',
                                            'price': '10'
                                        },
                                        {
                                            'name': 'Math Self-study',
                                            'price': '60'
                                        }
                                );
                                var arr_search = search_public_lesson(arr, text);
                                var html = '';
                                $("#table-dict").html(html);
                                if (arr_search.length != 0) {
                                    $.each(arr_search, function (i, v) {
 html += '<tr id="math-tr" class="math-tr">\n\
                                                                <td >\n\
                                                                    <div class="cb-type2">\n\
                                                                        <label>\n\
                                                                            <input type="checkbox" class="radio_buttons class_cb_search option-input-2 option-input-lesson option-input-dict radio" value="" data-cate="9" data-name="'+v.name+'" data-desc="" data-price="'+v.price+'" name="select-tr"/>\n\
                                                                        </label>\n\
                                                                    </div>\n\
                                                                </td>';
                            html += '<td class="table-img-icon">';
                            html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Self-study.png" alt="lesson" >';
                            html += '</td>';
                            html += ' <td class="table-img-icon">';
                            html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Ready-made_lesson.png" class="" alt="lesson" >';
                            html += '<span class="name-ready-lesson" > '+v.name+'</span>';
                            html += '</td>';
                            html += '<td class="table-img-icon">';
                            html += '<p class="pay-lesson"><span>$</span>'+v.price+'</p>';
                            html += '</td>';
                            html += '<td class="table-img-icon">';
                            html += '<div class="open-arrow-sel" data-gid="" data-content=""><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Detail_1.png" class="" alt="lesson" ></div>';
                            html += '</td>';

                            html += '</tr>';
                                    });
                                } else {
                                    html += '<tr><td colspan="6">No results.</td>';
                                    html += '</tr>';
                                }
                                $("#table-dict").html(html);
                            
                            
                        }

                        });
                        //order dictionary 
                        $("#icon-option-essential").click(function () {
                            var sort = $(this).attr("data-sort");
                            if ($("#dict").hasClass("active")) {
                                $.post(home_url + "/?r=ajax/get_dictionaries", {sort: sort}, function (data) {
                                    if (data != 0) {

                                        $("#table-dict").html("");
                                        if (sort == "upward") {
                                            $("#icon-option-essential").attr("data-sort", "downward");
                                            $(".img-sort-dict").attr("src", "<?php echo get_template_directory_uri(); ?>/library/images/icon_Oldest.png");
                                        } else if (sort == "downward") {
                                            $("#icon-option-essential").attr("data-sort", "upward");
                                            $(".img-sort-dict").attr("src", "<?php echo get_template_directory_uri(); ?>/library/images/icon_Newest.png");
                                        }
                                        data = JSON.parse(data);

                                        $.each(data, function (i, v) {
                                            var html = '';
                                            html += '<tr id="math-tr' + v.id + '" class="math-tr">\n\
                                                                <td >\n\
                                                                    <div class="cb-type2">\n\
                                                                        <label>\n\
                                                                            <input type="checkbox" class="radio_buttons class_cb_search option-input-2 option-input-lesson option-input-dict radio" value="' + v.id + '" data-cate="8" data-name="' + v.name + '" data-desc="" data-price="3" name="select-tr"/>\n\
                                                                        </label>\n\
                                                                    </div>\n\
                                                                </td>';
                                            html += '<td class="table-img-icon">';
                                            html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Dictionary.png" alt="lesson" >';
                                            html += '</td>';
                                            html += ' <td class="table-img-icon">';
                                            html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Ready-made_lesson.png" class="" alt="lesson" >';
                                            html += '<span class="name-ready-lesson" data-gid="' + v.id + '">' + v.name + '</span>';
                                            html += '</td>';
                                            html += '<td class="table-img-icon">';
                                            html += '<p class="pay-lesson"><span>$</span>3</p>';
                                            html += '</td>';

                                            html += '</tr>';
                                            $("#table-dict").append(html);
                                        });
                                    } else {
                                        var html = '';
                                        html += '<tr id="math-tr" class="math-tr"><td></td><td colspan="3">No result.</td></tr>';
                                        $("#table-dict").append(html);
                                    }

                                });
                            }
                        });
                        $("#eng-sat").click(function () {
                            get_sat_english();
                            $("#btn-sat-down").removeClass("btn-ready-lesson");
                            $("#btn-sat-down").addClass("btn-ready-lesson-down");
                            $("#open-search-sat").slideUp("fast");
                            $("#select-all-sat").attr("checked", false);
                        });
                        function get_sat_english() {
                            $("#select-all-sat").attr("checked",false);
                            $("#table-sat").html("");
                            var english = new Array(
                                {
                                    id : "1",
                                    name : "SAT Preparation - Grammar Review",
                                    price : "50"
                                },
                                {
                                    id : "2",
                                    name : "English SAT Preparation - Writing Practice",
                                    price : "20"
                                },
                                {
                                    id : "3",
                                    name : "English SAT Simulation Test 1",
                                    price : "50"
                                },
                                {
                                    id : "4",
                                    name : "English SAT Simulation Test 2",
                                    price : "50"
                                },
                                {
                                    id : "5",
                                    name : "English SAT Simulation Test 3",
                                    price : "50"
                                },
                                {
                                    id : "6",
                                    name : "English SAT Simulation Test 4",
                                    price : "50"
                                },
                                {
                                    id : "7",
                                    name : "English SAT Simulation Test 5",
                                    price : "50"
                                }
                            );
                            $.each(english, function(i,v){
                                $("#select-all-sat").attr("checked", false);
                                var html = '';
                                html += '<tr id="sat-tr'+ v.id +'" class="sat-tr"><td><div class="cb-type2"><label><input type="checkbox" class="radio_buttons class_cb_search option-input-2 option-input-lesson option-input-sat radio" value="" name="select-tr"/></label> </div> </td>';
                                html += '<td class="table-img-icon">';
                                html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_SAT.png" alt="lesson" >';
                                html += '</td>';
                                html += ' <td class="table-img-icon">';
                                html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Ready-made_lesson.png" class="" alt="lesson" >';
                                html += '<span class="name-sat">'+ v.name +'</span>';
                                html += '</td>';
                                html += '<td class="table-img-icon">';
                                html += '<p class="pay-lesson"><span>$</span>'+ v.price +'</p>';
                                html += '</td>';
                                html += '<td class="table-img-icon">';
                                html += '<div class="open-arrow-sel" data-gid="" data-content=""><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Detail_1.png" class="" alt="lesson" ></div>';
                                html += '</td>';
                                html += '</tr>';
                                $("#table-sat").append(html);
                            });
                        }
                        $("#math-sat").click(function () {
                            $("#table-sat").html("");
                            $("#select-all-sat").attr("checked", false);
                            $("#btn-sat-down").removeClass("btn-ready-lesson");
                            $("#btn-sat-down").addClass("btn-ready-lesson-down");
                            $("#open-search-sat").slideUp("fast");
                            get_sat_math();
                        });
                        $("#select-all-sat").click(function () {
                            if ($("#select-all-sat").is(":checked")) {

                                $(".option-input-sat").attr("checked", true);
                            } else
                                $(".option-input-sat").attr("checked", false);
                        });
                        $("#sat-tab").click(function () {
                            get_sat_english();
                            $("#select-all-sat").attr("checked", false);
                            $(".set-hide-tab").not("#sat-public").addClass("hidden");
                            $("#btn-sat-down").removeClass("btn-ready-lesson");
                            $("#btn-sat-down").addClass("btn-ready-lesson-down");
                            $("#open-search-sat").slideUp("fast");
                        });
                        //event Course's Tab
                        $("#tab-public-les-cou").click(function () {
                            $("#select-all-course").attr("checked", false);
                            $(".eng-math-les").not('#courses').addClass("hidden");
                            $(".eng-math-les").not('#courses').removeClass("active");
                            $(".set-hide-tab").not('#course-public').addClass("hidden");
                            $(".set-hide-tab").not('#course-public').removeClass("active");
                            $("#courses").addClass("active");
                            $("#courses").removeClass("hidden");
                            $("#course-public").addClass("active");
                            $("#course-public").removeClass("hidden");
                            $("#open-search-course").css('display', 'none');
                            var check = $("#btn-course-down").hasClass("btn-ready-lesson");
                            if (check == true)
                            {
                                $("#btn-course-down").removeClass("btn-ready-lesson");
                                $("#btn-course-down").addClass("btn-ready-lesson-down");

                            }
                            get_course();


                        });
                        function get_course() {
                            var html = '';
                            var table = $("#table-course");
                            table.html(html);
                            var arr = new Array(
                                    {
                                        type_id: 38,
                                        name: "ikMath Course - Math Kindergarten",
                                        price: "20"
                                    },
                                    {
                                        type_id: 39,
                                        name: "ikMath Course - Math Grade 1",
                                        price: "20"
                                    },
                                    {
                                        type_id: 40,
                                        name: "ikMath Course - Math Grade 2",
                                        price: "20"
                                    },
                                    {
                                        type_id: 41,
                                        name: "ikMath Course - Math Grade 3",
                                        price: "30"
                                    },
                                    {
                                        type_id: 42,
                                        name: "ikMath Course - Math Grade 4",
                                        price: "30"
                                    },
                                    {
                                        type_id: 43,
                                        name: "ikMath Course - Math Grade 5",
                                        price: "30"
                                    },
                                    {
                                        type_id: 44,
                                        name: "ikMath Course - Math Grade 6",
                                        price: "30"
                                    },
                                    {
                                        type_id: 45,
                                        name: "ikMath Course - Math Grade 7",
                                        price: "40"
                                    },
                                    {
                                        type_id: 46,
                                        name: "ikMath Course - Math Grade 8",
                                        price: "40"
                                    },
                                    {
                                        type_id: 47,
                                        name: "ikMath Course - Math Grade 9",
                                        price: "50"
                                    },
                                    {
                                        type_id: 48,
                                        name: "ikMath Course - Math Grade 10",
                                        price: "50"
                                    },
                                    {
                                        type_id: 49,
                                        name: "ikMath Course - Math Grade 11",
                                        price: "50"
                                    },
                                    {
                                        type_id: 50,
                                        name: "ikMath Course - Math Grade 12",
                                        price: "50"
                                    }
                            );
                            $.each(arr, function (i, v) {
                                html += '<tr id="course-tr' + v.type_id + '" class="course-tr"><td><div class="cb-type2"><label><input type="checkbox" class="radio_buttons class_cb_search option-input-2 option-input-lesson option-input-course radio" value="' + v.type_id + '" data-cate="5" data-name="' + v.name + '" data-desc="" data-price="' + v.price + '" name="select-tr"/></label> </div> </td>';
                                html += '<td class="table-img-icon">';
                                html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_MATH.png" alt="lesson" >';
                                html += '</td>';
                                html += ' <td class="table-img-icon">';
                                html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Ready-made_lesson.png" class="" alt="lesson" >';
                                html += '<span class="name-course" data-type="' + v.type_id + '">' + v.name + '</span>';
                                html += '</td>';
                                html += '<td class="table-img-icon">';
                                html += '<p class="pay-lesson"><span>$</span>' + v.price + '</p>';
                                html += '</td>';
                                html += '<td class="table-img-icon">';
                                html += '<div class="open-arrow-course" data-gid="" data-content=""><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Detail_1.png" class="" alt="lesson" ></div>';
                                html += '</td>';
                                html += '</tr>';
                            });
                            table.html(html);
                        }
                        $("#btn-course-down").click(function () {
                            var check = $("#btn-course-down").hasClass("btn-ready-lesson-down");
                            if (check == true) {
                                $("#btn-course-down").removeClass("btn-ready-lesson-down");
                                $("#btn-course-down").addClass("btn-ready-lesson");
                                $("#open-search-course").slideDown("fast");
                            } else {
                                $("#btn-course-down").removeClass("btn-ready-lesson");
                                $("#btn-course-down").addClass("btn-ready-lesson-down");
                                $("#open-search-course").slideUp("fast");
                            }
                        });
                        $("#select-all-course").click(function () {
                            if ($("#select-all-course").is(":checked")) {

                                $(".option-input-course").attr("checked", true);
                            } else
                                $(".option-input-course").attr("checked", false);
                        });
                        //save Public Lesson to My Lesson
                        $(".save-to-lesson").click(function () {
                            var id = $(this).attr("data-id");
                            var selected = new Array();


                            switch (id) {
                                case 'critical lesson':

                                    $(".option-input-ready-eng:checked").each(function () {
                                        var id = $(this).val();
                                        var name = $(this).attr("data-name");
                                        var desc = $(this).attr("data-desc");
                                        var cate = $(this).attr("data-cate");
                                        var price = $(this).attr("data-price");
                                        selected.push({id: id, name: name, desc: desc, cate: cate, price: price});
                                    });
                                    if (selected.length == 0) {
                                        var html = '';
                                        html += 'Please select lesson first.';
                                        $(".box-title-1").html(html);
                                        $("#modal-alert").css('display', 'block');
                                    } else {
                                        $.post(home_url + "/?r=ajax/add_public_lesson", {
                                            data: selected}, function (data) {
                                            if (data != 0) {
                                                var html = '';
                                                html += 'Lessons successfully saved to <span style="font-style:italic;">My Lesson</span>.';
                                                $(".box-title-1").html(html);
                                                $("#modal-alert").css('display', 'block');
                                                $(".option-input-ready-eng").attr("checked", false);
                                            } else {
                                                var html = '';
                                                html += 'Fail!';
                                                $(".box-title-1").html(html);
                                                $("#modal-alert").css('display', 'block');
                                            }
                                        });
                                    }
                                    break;
                                case 'essential':
                                    $(".option-input-dict:checked").each(function () {
                                        var id = $(this).val();
                                        var name = $(this).attr("data-name");
                                        var desc = $(this).attr("data-desc");
                                        var cate = $(this).attr("data-cate");
                                        var price = $(this).attr("data-price");
                                        selected.push({id: id, name: name, desc: desc, cate: cate, price: price});
                                    });
                                    if (selected.length == 0) {
                                        var html = '';
                                        html += 'Please select lesson first.';
                                        $(".box-title-1").html(html);
                                        $("#modal-alert").css('display', 'block');
                                    } else {
                                        $.post(home_url + "/?r=ajax/add_public_lesson", {
                                            data: selected}, function (data) {
                                            console.log(data);
                                            if (data != 0) {
                                                var html = '';
                                                html += 'Lessons successfully saved to <span style="font-style:italic;">My Lesson</span>.';
                                                $(".box-title-1").html(html);
                                                $("#modal-alert").css('display', 'block');
                                                $(".option-input-dict").attr("checked", false);
                                            } else {
                                                var html = '';
                                                html += 'Fail!';
                                                $(".box-title-1").html(html);
                                                $("#modal-alert").css('display', 'block');
                                            }
                                        });
                                    }
                                    break;
                                case 'sat':
                                    $(".option-input-sat:checked").each(function () {
                                        var id = $(this).val();
                                        var name = $(this).attr("data-name");
                                        var desc = $(this).attr("data-desc");
                                        var cate = $(this).attr("data-cate");
                                        var price = $(this).attr("data-price");
                                        selected.push({id: id, name: name, desc: desc, cate: cate, price: price});
                                    });
                                    if (selected.length == 0) {
                                        var html = '';
                                        html += 'Please select lesson first.';
                                        $(".box-title-1").html(html);
                                        $("#modal-alert").css('display', 'block');
                                    } else {
                                        $.post(home_url + "/?r=ajax/add_public_lesson", {
                                            data: selected}, function (data) {

                                            if (data != 0) {
                                                var html = '';
                                                html += 'Lessons successfully saved to <span style="font-style:italic;">My Lesson</span>.';
                                                $(".box-title-1").html(html);
                                                $("#modal-alert").css('display', 'block');
                                                $(".option-input-dict").attr("checked", false);
                                            } else {
                                                var html = '';
                                                html += 'Fail!';
                                                $(".box-title-1").html(html);
                                                $("#modal-alert").css('display', 'block');
                                            }
                                        });
                                    }
                                    break;
                                case 'courses':
                                    $(".option-input-course:checked").each(function () {
                                        var id = $(this).val();
                                        var name = $(this).attr("data-name");
                                        var desc = $(this).attr("data-desc");
                                        var cate = $(this).attr("data-cate");
                                        var price = $(this).attr("data-price");
                                        selected.push({id: id, name: name, desc: desc, cate: cate, price: price});
                                    });
                                    if (selected.length == 0) {
                                        var html = '';
                                        html += 'Please select lesson first.';
                                        $(".box-title-1").html(html);
                                        $("#modal-alert").css('display', 'block');
                                    } else {
                                        $.post(home_url + "/?r=ajax/add_public_lesson", {
                                            data: selected}, function (data) {

                                            if (data != 0) {
                                                var html = '';
                                                html += 'Lessons successfully saved to <span style="font-style:italic;">My Lesson</span>.';
                                                $(".box-title-1").html(html);
                                                $("#modal-alert").css('display', 'block');
                                                $(".option-input-dict").attr("checked", false);
                                            } else {
                                                var html = '';
                                                html += 'Fail!';
                                                $(".box-title-1").html(html);
                                                $("#modal-alert").css('display', 'block');
                                            }
                                        });
                                    }
                                    break;
                            }
                        });
                        $("#search-course-btn").click(function () {
                            var course = new Array(
                                    {
                                        name: "ikMath Course - Math Kindergarten",
                                        price: "$20"
                                    },
                                    {
                                        name: "ikMath Course - Math Grade 1",
                                        price: "$20"
                                    },
                                    {
                                        name: "ikMath Course - Math Grade 2",
                                        price: "$20"
                                    },
                                    {
                                        name: "ikMath Course - Math Grade 3",
                                        price: "$30"
                                    },
                                    {
                                        name: "ikMath Course - Math Grade 4",
                                        price: "$30"
                                    },
                                    {
                                        name: "ikMath Course - Math Grade 5",
                                        price: "$30"
                                    },
                                    {
                                        name: "ikMath Course - Math Grade 6",
                                        price: "$30"
                                    },
                                    {
                                        name: "ikMath Course - Math Grade 7",
                                        price: "$40"
                                    },
                                    {
                                        name: "ikMath Course - Math Grade 8",
                                        price: "$40"
                                    },
                                    {
                                        name: "ikMath Course - Math Grade 9",
                                        price: "$50"
                                    },
                                    {
                                        name: "ikMath Course - Math Grade 10",
                                        price: "$50"
                                    },
                                    {
                                        name: "ikMath Course - Math Grade 11",
                                        price: "$50"
                                    },
                                    {
                                        name: "ikMath Course - Math Grade 12",
                                        price: "$50"
                                    }
                            );
                            var text = $("#search-course").val();
                            var cou = search_public_lesson(course, text);

                            $("#table-course").html("");
                            $.each(cou, function (i, v) {

                                var html = '';
                                html += '<tr id="course-tr" class="course-tr"><td><div class="cb-type2"><label><input type="checkbox" class="radio_buttons class_cb_search option-input-2 option-input-lesson option-input-course radio" value="" name="select-tr"/></label> </div> </td>';
                                html += '<td class="table-img-icon">';
                                html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_MATH.png" alt="lesson" >';
                                html += '</td>';
                                html += ' <td class="table-img-icon">';
                                html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Ready-made_lesson.png" class="" alt="lesson" >';
                                html += '<span class="name-sat">' + v.name + '</span>';
                                html += '</td>';
                                html += '<td class="table-img-icon">';
                                html += '<p class="pay-lesson">' + v.price + '</p>';
                                html += '</td>';
                                html += '<td class="table-img-icon">';
                                html += '<div class="open-arrow-course" data-gid="" data-content=""><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Detail_1.png" class="" alt="lesson" ></div>';
                                html += '</td>';
                                $("#table-course").append(html);
                            });
                            if (cou.length == 0) {

                                var html = '';
                                html += '<tr><td colspan="6">No results.</td>';
                                html += '</tr>';
                                $("#table-course").append(html);
                            }
                        });
                        function search_public_lesson(arr, text) {
                            var arr_search = new Array();
                            $.each(arr, function (i, value) {
                                var str = value.name.toLowerCase();
                                var index = str.indexOf(text.toLowerCase());
                                if (index >= 0) {
                                    arr_search.push(value);
                                }
                            });
                            return arr_search;
                        }
                        //button search sat
                        $("#search-sat-btn").click(function () {
                            var text = $("#search-sat").val();
                            if ($("#sat-math").hasClass("active")) {
                                var arr = new Array(
                                        {
                                            'name': 'Math SAT Preparation',
                                            'price': '50'
                                        },
                                        {
                                            'name': 'Math SAT Simulation Test 1(New SAT Test)',
                                            'price': '50'
                                        },
                                        {
                                            'name': 'Math SAT Simulation Test 2(New SAT Test)',
                                            'price': '50'
                                        },
                                        {
                                            'name': 'Math SAT Simulation Test 3(New SAT Test)',
                                            'price': '50'
                                        },
                                        {
                                            'name': 'Math SAT Simulation Test 4(New SAT Test)',
                                            'price': '50'
                                        },
                                        {
                                            'name': 'Math SAT Simulation Test 5(New SAT Test)',
                                            'price': '50'
                                        },
                                        {
                                            'name': 'Math SAT 2 Preparation',
                                            'price': '80'
                                        },
                                        {
                                            'name': 'Math SAT 2 Simulation Test 1',
                                            'price': '50'
                                        },
                                        {
                                            'name': 'Math SAT 2 Simulation Test 2',
                                            'price': '50'
                                        },
                                        {
                                            'name': 'Math SAT 2 Simulation Test 3',
                                            'price': '50'
                                        },
                                        {
                                            'name': 'Math SAT 2 Simulation Test 4',
                                            'price': '50'
                                        },
                                        {
                                            'name': 'Math SAT 2 Simulation Test 5',
                                            'price': '50'
                                        }
                                );
                                var arr_search = search_public_lesson(arr, text);
                                var html = '';
                                $("#table-sat").html(html);
                                if (arr_search.length != 0) {
                                    $.each(arr_search, function (i, v) {

                                        html += '<tr id="sat-tr" class="sat-tr"><td><div class="cb-type2"><label><input type="checkbox" class="radio_buttons class_cb_search option-input-2 option-input-lesson option-input-sat radio" value="" data-cate="10" data-name="' + v.name + '" data-desc="" data-price="' + v.price + '" name="select-tr"/></label> </div> </td>';
                                        html += '<td class="table-img-icon">';
                                        html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_SAT.png" alt="lesson" >';
                                        html += '</td>';
                                        html += ' <td class="table-img-icon">';
                                        html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Ready-made_lesson.png" class="" alt="lesson" >';
                                        html += '<span class="name-sat">' + v.name + '</span>';
                                        html += '</td>';
                                        html += '<td class="table-img-icon">';
                                        html += '<p class="pay-lesson"><span>$</span>' + v.price + '</p>';
                                        html += '</td>';
                                        html += '<td class="table-img-icon">';
                                        html += '<div class="open-arrow-sat-math" data-gid="" data-content=""><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Detail_1.png" class="" alt="lesson" ></div>';
                                        html += '</td>';
                                        html += '</tr>';
                                    });
                                } else {
                                    html += '<tr><td colspan="6">No results.</td>';
                                    html += '</tr>';
                                }
                                $("#table-sat").html(html);
                            } else if ($("#sat-eng").hasClass("active")) {
                                  var arr = new Array(
                                        {
                                            'name': 'SAT Preparation - Grammar Review',
                                            'price': '50'
                                        },
                                        {
                                            'name': 'English SAT Preparation - Writing Practice',
                                            'price': '20'
                                        },
                                        {
                                            'name': 'English SAT Simulation Test 1',
                                            'price': '50'
                                        },
                                        {
                                            'name': 'English SAT Simulation Test 2',
                                            'price': '50'
                                        },
                                        {
                                            'name': 'English SAT Simulation Test 3',
                                            'price': '50'
                                        },
                                        {
                                            'name': 'English SAT Simulation Test 4',
                                            'price': '50'
                                        },
                                        {
                                            'name': 'English SAT Simulation Test 5',
                                            'price': '50'
                                        }
                                );
                                var arr_search = search_public_lesson(arr, text);
                                var html = '';
                                $("#table-sat").html(html);
                                if (arr_search.length != 0) {
                                    $.each(arr_search, function (i, v) {

                                       html += '<tr id="sat-tr" class="sat-tr"><td><div class="cb-type2"><label><input type="checkbox" class="radio_buttons class_cb_search option-input-2 option-input-lesson option-input-sat radio" value="" data-cate="10" data-name="'+ v.name +'" data-desc="" data-price="'+v.price+'" name="select-tr"/></label> </div> </td>';
                            html += '<td class="table-img-icon">';
                            html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_SAT.png" alt="lesson" >';
                            html += '</td>';
                            html += ' <td class="table-img-icon">';
                            html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Ready-made_lesson.png" class="" alt="lesson" >';
                            html += '<span class="name-sat">'+v.name+'</span>';
                            html += '</td>';
                            html += '<td class="table-img-icon">';
                            html += '<p class="pay-lesson"><span>$</span>'+v.price+'</p>';
                            html += '</td>';
                            html += '<td class="table-img-icon">';
                            html += '<div class="open-arrow-sel" data-gid="" data-content=""><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Detail_1.png" class="" alt="lesson" ></div>';
                            html += '</td>';
                            html += '</tr>';
                                    });
                                } else {
                                    html += '<tr><td colspan="6">No results.</td>';
                                    html += '</tr>';
                                }
                                $("#table-sat").html(html);
                            }
                        });
                        function get_sat_math() {
                            $("#table-sat").html("");
                            var math = new Array(
                                {
                                    id : "1",
                                    name : "Math SAT Preparation",
                                    price : "50"
                                },
                                {
                                    id : "2",
                                    name : "Math SAT Simulation Test 1(New SAT Test)",
                                    price : "50"
                                },
                                {
                                    id : "3",
                                    name : "Math SAT Simulation Test 2(New SAT Test)",
                                    price : "50"
                                },
                                {
                                    id : "4",
                                    name : "Math SAT Simulation Test 3(New SAT Test)",
                                    price : "50"
                                },
                                {
                                    id : "5",
                                    name : "Math SAT Simulation Test 4(New SAT Test)",
                                    price : "50"
                                },
                                {
                                    id : "6",
                                    name : "Math SAT Simulation Test 5(New SAT Test)",
                                    price : "50"
                                },
                                {
                                    id : "7",
                                    name : "Math SAT 2 Preparation",
                                    price : "80"
                                },
                                {
                                    id : "8",
                                    name : "Math SAT 2 Simulation Test 1",
                                    price : "50"
                                },
                                {
                                    id : "9",
                                    name : "Math SAT 2 Simulation Test 2",
                                    price : "50"
                                },
                                {
                                    id : "10",
                                    name : "Math SAT 2 Simulation Test 3",
                                    price : "50"
                                },
                                {
                                    id : "11",
                                    name : "Math SAT 2 Simulation Test 4",
                                    price : "50"
                                },
                                {
                                    id : "12",
                                    name : "Math SAT 2 Simulation Test 5",
                                    price : "50"
                                },
                            );
                            $.each(math, function(i,v){
                                $("#select-all-sat").attr("checked", false);
                                var html = '';
                                html += '<tr id="sat-tr'+ v.id + '" class="sat-tr"><td><div class="cb-type2"><label><input type="checkbox" class="radio_buttons class_cb_search option-input-2 option-input-lesson option-input-sat radio" value="" data-cate="10" data-name="Math SAT Preparation" data-desc="" data-price="50" name="select-tr"/></label> </div> </td>';
                                html += '<td class="table-img-icon">';
                                html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_SAT.png" alt="lesson" >';
                                html += '</td>';
                                html += ' <td class="table-img-icon">';
                                html += '<img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Ready-made_lesson.png" class="" alt="lesson" >';
                                html += '<span class="name-sat">'+ v.name +'</span>';
                                html += '</td>';
                                html += '<td class="table-img-icon">';
                                html += '<p class="pay-lesson"><span>$</span>'+ v.price +'</p>';
                                html += '</td>';
                                html += '<td class="table-img-icon">';
                                html += '<div class="open-arrow-sat-math" data-gid="" data-content=""><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Detail_1.png" class="" alt="lesson" ></div>';
                                html += '</td>';
                                html += '</tr>';
                                $("#table-sat").append(html);
                            });
                        }
                         //list of Course
                        $(".name-course").live("click", function () {
                            var type_id = $(this).attr("data-type");
                            $.get(home_url + "/?r=ajax/get_list_course", {data: type_id}, function (data) {
                                var html = '';
                                if (data != 0) {
                                    var check2 = $(".course-tr").not("#course-tr" + type_id).next().hasClass("content-tr");
                                    if (check2 == true) {
                                        $(".course-tr").not("#course-tr" + type_id).nextAll(".content-tr").remove();
                                    }
                                    data = JSON.parse(data);
                                    var check = $("#course-tr" + type_id).next().hasClass("content-tr");
                                    if (check == false) {
                                        $.each(data, function (i, v) {
                                            html += '<tr id="content-tr' + v.group_id + '" class="content-tr content-stl set-hide">';
                                            html += '<td></td>';
                                            html += '<td></td>';
                                            html += '<td class="name-content" colspan="2" data-gid="' + v.group_id + '" data-type="' + type_id + '">' + v.content + '</td>';
                                            html += '<td class="table-img-icon"><div class="open-arrow-sel"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Detail_1.png" class="" alt="lesson" ></div></td>';
                                            html += '</tr>';
                                        });
                                        $("#course-tr" + type_id).after(html);
                                        $(".content-tr td").slideDown("slow");
                                    } else {
                                        $(".set-hide td").slideUp("slow", function () {
                                            $("#course-tr" + type_id).nextAll(".set-hide").remove();
                                        });

                                    }
                                }
                            });
                        });
                        //list worksheet content
                        $(".name-content").live("click", function () {
                            var type_id = $(this).attr("data-type");
                            var gid = $(this).attr("data-gid");
                            $.get(home_url + "/?r=ajax/get_worksheet_course", {type: type_id, gid: gid}, function (data) {
                                var html = '';
                                if (data != 0) {
                                    var check2 = $(".content-tr").not("#content-tr" + gid).next().hasClass("sheet-tr");
                                    if (check2 == true) {
                                        $(".content-tr").not("#content-tr" + gid).nextAll(".sheet-tr").remove();
                                    }
                                    data = JSON.parse(data);
                                    var check = $("#content-tr" + gid).next().hasClass("sheet-tr");
                                    if (check == false) {
                                        $.each(data, function (i, v) {
                                            html += '<tr id="sheet-tr' + v.sheet_id + '" class="sheet-tr sheet-stl set-hide">';
                                            html += '<td></td>';
                                            html += '<td></td>';
                                            html += '<td class="name-sheet table-img-icon" colspan="2" ><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Worksheet.png" class="" alt="lesson" >' + v.sheet_name + '</td>';
                                            html += '<td class="table-img-icon"><div class="open-arrow-sel"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Detail_1.png" class="" alt="lesson" ></div></td>';
                                            html += '</tr>';
                                        });
                                        $("#content-tr" + gid).after(html);
                                        $(".sheet-tr td").slideDown("slow");
                                    } else {
                                        $(".sheet-tr td").slideUp("slow", function () {
                                            $("#content-tr" + gid).nextAll(".sheet-tr").remove();
                                        });

                                    }
                                }
                            })
                        });


                    }); //end function
                })(jQuery);

            </script>