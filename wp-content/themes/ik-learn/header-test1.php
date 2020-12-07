<div class="modal-dialog modal-lg modal-signup">
            <div class="modal-content modal-content-signup">
                <div class="title-div">
                    <div class="icon-close-classes-created">
                        <?php if ($is_user_logged_in) { ?>
                        <button type="button" id="btn-my-timezone" class="btn-my-schedule">
                            <span id="mycity-name"><?php echo $time_zone_user1 ?></span>
                            <span id="mytime-clock" data-hour="24" data-minute="0">2:35 PM</span>
                            <img class="ic-my-schedule" src="<?php echo get_template_directory_uri(); ?>/library/images/icon_TimeZone_Selector.png">
                        </button>
                        <?php } ?>

                        <button type="button" id="menu-quick-notification">
                            <?php if($count_notification == 0){ ?>
                            <img class="ic-close7" src="<?php echo get_template_directory_uri(); ?>/library/images/07_Top_Trigger.png">
                            <?php }else{ ?>
                            <img class="ic-close7 active" src="<?php echo get_template_directory_uri(); ?>/library/images/08_Top_Trigger_NOTIFICATION.png">    
                            <?php } ?>
                        </button>

                        <ul id="open-menu-quicknotifi" style="display: none;">
                            <li>
                                <button type="button" id="quick-notification-btn">
                                    <img src="<?php echo get_template_directory_uri(); ?>/library/images/10_Menu_Notification.png">
                                    Quick Notification
                                </button>
                            </li>
                            <li>
                                <button type="button" id="btn-my-schedule">
                                    <img src="<?php echo get_template_directory_uri(); ?>/library/images/01_icon_Schedule_Starter.png">
                                    Schedule Starter
                                </button>
                            </li>
                            
                        </ul>
                        <button  type="button" id="close-modal" style="background: none;">
                                    <img src="<?php echo get_template_directory_uri(); ?>/library/images/09_Menu_Closed.png" style="height: 14px; margin-top: -5px;">
                                    
                                </button>

                        <ul id="my-timezone" style="display: none;">
                            <li data-value="" data-city="London" <?php if($timezone_index == '0' ) echo 'class="active"'; ?>>Select Time Zone</li>
                                <li class="my-timezone<?php if($timezone_name == 'America/New_York' ) echo ' active'; ?>" data-index="1" data-value="-5" data-name="America/New_York" data-city="New York">
                                    <span class="name-city" id="name-city1">New York</span>
                                    <span class="name-clock" id="name-clock1"></span>
                                </li>
                                <li class="my-timezone<?php if($timezone_name == 'America/Chicago' ) echo ' active'; ?>" data-index="2" data-value="-6" data-name="America/Chicago" data-city="Minneapolis">
                                    <span class="name-city" id="name-city2">Minneapolis</span>
                                    <span class="name-clock" id="name-clock2"></span>
                                </li>
                                <li class="my-timezone<?php if( $timezone_name == 'America/Denver' ) echo ' active'; ?>" data-index="3" data-value="-5" data-name="America/Denver" data-city="Colorado">
                                    <span class="name-city" id="name-city3">Colorado</span>
                                    <span class="name-clock" id="name-clock3"></span>
                                </li>
                                <li class="my-timezone <?php if($timezone_name == 'America/Los_Angeles' ) echo ' active'; ?>" data-index="4" data-value="-7" data-name="America/Los_Angeles" data-city="SF/LA">
                                    <span class="name-city" id="name-city4">SF/LA</span>
                                    <span class="name-clock" id="name-clock4"></span>
                                </li>
                                <li class="my-timezone <?php if($timezone_name == 'Pacific/Honolulu' ) echo ' active'; ?>" data-index="5" data-value="-10" data-name="Pacific/Honolulu" data-city="Hawaii">
                                    <span class="name-city" id="name-city5">Hawaii</span>
                                    <span class="name-clock" id="name-clock5"></span>
                                </li>
                                <li class="my-timezone<?php if($timezone_name == 'Pacific/Guam' ) echo ' active'; ?>" data-index="6" data-value="+10" data-name="Pacific/Guam" data-city="Guam">
                                    <span class="name-city" id="name-city6">Guam</span>
                                    <span class="name-clock" id="name-clock6"></span>
                                </li>
                                <li class="my-timezone<?php if($timezone_name == 'Asia/Tokyo' ) echo ' active'; ?>" data-index="7" data-value="+9" data-name="Asia/Tokyo" data-city="Tokyo">
                                    <span class="name-city" id="name-city7">Tokyo</span>
                                    <span class="name-clock" id="name-clock7"></span>
                                </li>
                                <li class="my-timezone<?php if($timezone_name == 'Asia/Seoul' ) echo ' active'; ?>" data-index="8" data-value="+9" data-name="Asia/Seoul" data-city="Seoul">
                                    <span class="name-city" id="name-city8">Seoul</span>
                                    <span class="name-clock" id="name-clock8"></span>
                                </li>
                                <li class="my-timezone<?php if($timezone_name == 'Asia/Shanghai' ) echo ' active'; ?>" data-index="9" data-value="+8" data-name="Asia/Shanghai" data-city="Beijing">
                                    <span class="name-city" id="name-city9">Beijing</span>
                                    <span class="name-clock" id="name-clock9"></span>
                                </li>
                                <li class="my-timezone<?php if($timezone_name == 'Asia/Shanghai' ) echo ' active'; ?>" data-index="10" data-value="+8" data-name="Asia/Shanghai" data-city="Xianyang">
                                    <span class="name-city" id="name-city10">Xianyang</span>
                                    <span class="name-clock" id="name-clock10"></span>
                                </li>
                                <li class="my-timezone<?php if($timezone_name == 'Asia/Ho_Chi_Minh' ) echo ' active'; ?>" data-index="11" data-value="+7" data-name="Asia/Ho_Chi_Minh" data-city="Hanoi">
                                    <span class="name-city" id="name-city11">Hanoi</span>
                                    <span class="name-clock" id="name-clock11"></span>
                                </li>
                                <li class="my-timezone<?php if($timezone_name == 'Asia/Bangkok' ) echo ' active'; ?>" data-index="12" data-value="+7" data-name="Asia/Bangkok" data-city="Bangkok">
                                    <span class="name-city" id="name-city12">Bangkok</span>
                                    <span class="name-clock" id="name-clock12"></span>
                                </li>
                                <li class="my-timezone<?php if($timezone_name == 'Asia/Rangoon' ) echo ' active'; ?>" data-index="13" data-value="+7" data-name="Asia/Rangoon" data-city="Myanmar">
                                    <span class="name-city" id="name-city13">Myanmar</span>
                                    <span class="name-clock" id="name-clock13"></span>
                                </li>
                                <li class="my-timezone <?php if($timezone_name == 'Asia/Dhaka' ) echo ' active'; ?>" data-index="14" data-value="+6" data-name="Asia/Dhaka" data-city="Bangladesh">
                                    <span class="name-city" id="name-city14">Bangladesh</span>
                                    <span class="name-clock" id="name-clock14"></span>
                                </li>
                                <li class="my-timezone<?php if($timezone_name == 'Asia/Colombo' ) echo ' active'; ?>" data-index="15" data-value="+5" data-name="Asia/Colombo" data-city="Sri Lanka">
                                    <span class="name-city" id="name-city15">Sri Lanka</span>
                                    <span class="name-clock" id="name-clock15"></span>
                                </li>
                                <li class="my-timezone<?php if($timezone_name == '16' ) echo ' active'; ?>" data-index="16" data-value="+5" data-name="Asia/Kolkata" data-city="New Delhi">
                                    <span class="name-city" id="name-city16">New Delhi</span>
                                    <span class="name-clock" id="name-clock16"></span>
                                </li>
                                <li class="my-timezone<?php if( $timezone_name== '17' ) echo ' active'; ?>" data-index="17" data-value="+5" data-name="Asia/Kolkata" data-city="Mumbai">
                                    <span class="name-city" id="name-city17">Mumbai</span>
                                    <span class="name-clock" id="name-clock17"></span>
                                </li>
                                <li class="my-timezone<?php if($timezone_name == 'Europe/London' ) echo ' active'; ?>" data-index="18" data-value="0" data-name="Europe/London" data-city="London">
                                    <span class="name-city" id="name-city18">London</span>
                                    <span class="name-clock" id="name-clock18"></span>
                                </li>
                                <li class="my-timezone<?php if($timezone_name == 'Australia/Sydney' ) echo ' active'; ?>" data-index="19" data-value="+5" data-name="Australia/Sydney" data-city="Sydney">
                                    <span class="name-city" id="name-city19">Sydney</span>
                                    <span class="name-clock" id="name-clock19"></span>
                                </li>
                        </ul>
                    </div>
                    <img id="menu_Taggle" src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Menu_Trigger.png">
                    <span class="modal-title text-uppercase">
                        <a id="iklearn-home"  href="https://iktutor.com/en/<?php echo $link_ss?>">
                            <img src="<?php echo get_template_directory_uri(); ?>/library/images/ikTeach_Logo.png">
                        </a>
                    </span>
                </div>
                <hr class="line-modal">
                <div class="bg-overload"></div>
                <div id="open-list-quicknotifi" style="display: none;">
                    <div class="add-list-quicknotifi">
                                
                    </div>
                </div>
                <div class="modal-body-signup">
                    <div class="section-right">
                        <div class="tab-content">
                            <!-- Login -->
                            <div id="login-user" class="style-form tab-pane fade in active">
                                <h3>Login</h3>
                                <div class="col-md-12">
                                    <form action="<?php echo locale_home_url() ?>/?r=login" name="loginform" method="post">
                                        <div class="row">
                                            <div class="row md-login-r">
                                                <div class="col-xs-12 col-sm-6 col-md-6">
                                                    <div class="form-group">
                                                        <label for="username">
                                                            <?php _e('Username (e-mail address)', 'iii-dictionary') ?>
                                                        </label>
                                                        <input type="text" class="form-control border-ras" id="username-login" name="log" value="" style="border-radius:0px; margin-left: 0; padding-bottom: 6px">
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-6 col-md-6">
                                                    <div class="form-group">
                                                        <label for="password">
                                                            <?php _e('Password', 'iii-dictionary') ?>
                                                        </label>
                                                        <input type="password" class="form-control border-ras" id="password-login" name="pwd" value="">
                                                    </div>
                                                </div>
                                                <div class="clearfix" style="margin-bottom: 20px;"></div>
                                                <div class="col-sm-6 col-md-6">
                                                    <div class="form-group">
                                                        <button type="button" id="btn-login" class="btn-orange border-btn" name="wp-submit">
                                                            <?php _e('Login', 'iii-dictionary') ?>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6 col-md-6">
                                                    <div class="form-group">
                                                        <button type="button" class="btn-cancel-grey sign-up border-btn">
                                                            <?php _e('Create Account', 'iii-dictionary') ?>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="clearfix"></div>
                                                <div class="forgot-pass">
                                                    <div class="form-group forgot-password-form">
                                                        <a class="forgot-password-a lblForgot">
                                                            <?php _e('Forgot password?', 'iii-dictionary') ?>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="clearfix"></div>
                                        </div>
                                        <input name="redirect_to" value="<?php echo locale_home_url() ?>" type="hidden">
                                    </form>
                                </div>
                            </div>
                            <!-- Login -->

                            <!-- Lost Password -->
                            <div id="lost-password" class="hidden style-form tab-pane fade in">
                                <h3>Lost Password</h3>
                                <div class="">
                                    <form name="lostpasswordform" id="lostpassword-form" action="<?php echo esc_url(network_site_url('?r=login&action=forgotpassword')); ?>" method="post">
                                        <div class="row" style="margin-bottom: 20px;">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="user_login">
                                                        <?php _e('Email Address for Receiving a New Password', 'iii-dictionary') ?>
                                                    </label>

                                                    <input type="text" name="user_login" id="user_login_password" class="form-control border-ras" value="">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-12 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <button type="submit" name="wp-submit" class="btn-orange border-btn">
                                                        <?php esc_attr_e('Receive New Password', 'iii-dictionary') ?>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <button type="button" name="wp-submit" name="cancel" class="btn-cancel-grey border-btn close-modal-account">
                                                        <?php _e('Cancel', 'iii-dictionary') ?>
                                                    </button>
                                                </div>

                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <!-- Lost Password -->
                            <!-- Create Basic Account -->
                            <div id="create-account" class="tab-pane fade">
                                <div class="student-center">
                                    <p class="my-account">My Account</p>
                                    <p class="tutor-acc">CREATE A STUDENT ACCOUNT</p>
                                </div>
                                <p class="mt-top-14 heading-acc" style="color: #36a93f; font-size: 20px; font-family: Myriad_regular;">Basic Account <img class="icon-about" src="<?php echo get_template_directory_uri(); ?>/library/images/icon_About.png" alt="info"><span id="create-overview">overview</span></p>
                                <p style="color: red; font-size: 14px; "> (<span style=" position: absolute;  font-weight: bold;   padding-left: 1px; font-size: 14pt">*</span>&nbsp &nbsp) Required</p>
                                <form method="post" id="createAccount" action="" name="registerform" enctype="multipart/form-data" autocomplete="off">
                                    <div class="row">
                                        <div class="col-sm-9 col-md-9 refreshclass">
                                            <div class="find-general-border">
                                            <div class="form-group">
                                                <span class="find-label"><?php _e('Email Address', 'iii-dictionary') ?><span class="required-star"> *</span></span>
                                                <input id="user_login_signup" class="form-control" name="user_login" type="text" value=" "  required>
                                                
                                                <span id="checked-availability" class="not-check-available" style="    margin-right: 20px;     margin-top: 11px;"><span></span></span>
                                                <button class="btn-dark-blue border-btn check-availability" id="check-availability" style="background: #FFA523; width: 50px; margin-top: -41px; float: right; border-radius: 8px !important;" type="button" name="wp-submit">Check</button>
                                            </div>
                                        </div>
                                        </div>
                                        <div class="col-sm-3 col-md-3">
                                            <div class="find-general-border">
                                                <span class="find-label"><?php _e('Gender', 'iii-dictionary') ?><span class="required-star"> *</span></span>   
                                                <div class="form-group">
                                                    <div class="border-ras select-style" id="gender">
                                                        <select id="birth_g_pc" class="select-box-it form-control" name="birth_g_pc">
                                                            
                                                            <option value="Male">Male</option>
                                                            <option value="Female">Female</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="clearfix"></div>
                                        <div class="col-sm-6 col-md-6 mt-top-3 refreshclass">
                                            <div class="find-general-border">
                                                <span class="find-label"><?php _e('Password', 'iii-dictionary') ?><span class="required-star"> *</span></span>
                                            <div class="form-group">
                                                
                                                <input id="user_password_signup" class="form-control border-ras" name="user_password" type="text" value=""required>
                                                
                                                <div class="clear-input" onclick="document.getElementById('user_password_signup').value=null;"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 col-md-6 mt-top-3 refreshclass">
                                            <div class="find-general-border">
                                                <span class="find-label"><?php _e('Confirm Password', 'iii-dictionary') ?><span class="required-star"> *</span></span>
                                            <div class="form-group">
                                                <input id="confirm_password" class="form-control border-ras" name="confirm_password" type="text" value="" required>
                                                <div class="clear-input" onclick="document.getElementById('confirm_password').value=null;"></div>
                                                </div>
                                            </div>
                                        </div>

                                        
                                        <div class="clearfix"></div>
                                        <div class="col-sm-6 col-md-6 mt-top-3 refreshclass">
                                            <div class="find-general-border">
                                                <span class="find-label"><?php _e('First Name', 'iii-dictionary') ?><span class="required-star"> *</span></span>
                                            <div class="form-group">
                                                <input id="first_name_signup" class="form-control" name="first_name" type="text" value="" required>
                                                <div class="clear-input" onclick="document.getElementById('first_name_signup').value=null;"></div>
                                            </div>
                                        </div>
                                        </div>
                                        <div class="col-sm-6 col-md-6 mt-top-3 refreshclass">
                                            <div class="find-general-border">
                                                <span class="find-label"><?php _e('Last Name', 'iii-dictionary') ?><span class="required-star"> *</span></span>
                                            <div class="form-group">
                                                <input id="last_name_signup" class="form-control" name="last_name" type="text" value="" required>
                                                <div class="clear-input" onclick="document.getElementById('last_name_signup').value=null;"></div>
                                            </div>
                                        </div>
                                        </div>
                                        <div class="clearfix"></div>
                                        <div class="col-sm-12 col-md-12 mt-top-4">
                                            
                                                <p class="create-label mt-bottom-11 heading-acc">
                                                    <?php _e('Date of Birth', 'iii-dictionary') ?><img class="icon-about" src="<?php echo get_template_directory_uri(); ?>/library/images/icon_About.png" alt="info">
                                                </p>
                                                <div class="row tiny-gutter">
                                                    <div class="col-xs-12 col-sm-4 col-md-4 border-ras select-style" id="month">
                                                    <div class="find-general-border">
                                                        <span class="find-label"><?php _e('Month', 'iii-dictionary') ?><span class="required-star"> *</span></span>
                                                        <div class="form-group">
                                                        <select id="birth_m" class="select-box-it form-control" name="birth-m">
                                                            
                                                            <?php for ($i = 1; $i <= 12; $i++) : ?>
                                                                <?php $pad_str = str_pad($i, 2, '0', STR_PAD_LEFT) ?>
                                                                    <option value="<?php echo $pad_str ?>">
                                                                        <?php echo $pad_str ?>
                                                                    </option>
                                                                    <?php endfor ?>
                                                        </select>
                                                    </div>
                                                    </div>
                                                    </div>
                                                    <div class="col-xs-12 col-sm-4 col-md-4 border-ras select-style" id="date">
                                                        <div class="find-general-border">
                                                        <span class="find-label"><?php _e('Day', 'iii-dictionary') ?><span class="required-star"> *</span></span>
                                                        <div class="form-group">
                                                        <select id="birth_d" class="select-box-it form-control" name="birth-d">
                                                            
                                                            <?php for ($i = 1; $i <= 31; $i++) : ?>
                                                                <?php $pad_str = str_pad($i, 2, '0', STR_PAD_LEFT) ?>
                                                                    <option value="<?php echo $pad_str ?>">
                                                                        <?php echo $pad_str ?>
                                                                    </option>
                                                                    <?php endfor ?>
                                                        </select>
                                                    </div>
                                                    </div>
                                                    </div>
                                                    <div class="col-xs-12 col-sm-4 col-md-4 refreshclass year-mb">
                                                        <div class="find-general-border">
                                                            <span class="find-label"><?php _e('Year', 'iii-dictionary') ?><span class="required-star"> *</span></span>
                                                            <div class="form-group">
                                                                <input id="birth_y" class="form-control" name="birth-y" type="text" value="" required>
                                                                <div class="clear-input" onclick="document.getElementById('birth_y').value=null;"></div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                   <!--  <div class="col-xs-12 col-sm-4 col-md-4 gender-mb">
                                                        <div id="gender-mb">
                                                            <div class="form-group">
                                                                <div class="border-ras select-style" id="gender">
                                                                    <select id="birth-g_mb" class="select-box-it form-control" name="birth-g_mb">
                                                                        <option value="">Gender</option>
                                                                        <option value="Male">Male</option>
                                                                        <option value="Female">Female</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div> -->
                                                </div>
                                            </div>
                                        
                                        
                                        <p class="col-sm-12 col-md-12 col-xs-12 mt-top-4 heading-acc"><?php _e('Language', 'iii-dictionary') ?> & <?php _e('Time Zone', 'iii-dictionary') ?>  <img class="icon-about" src="<?php echo get_template_directory_uri(); ?>/library/images/icon_About.png" alt="info"> </p>
                                        <div id="language-timezone" class="col-sm-6 col-md-6 col-xs-6">
                                                
                                                <div  class="find-general-border language-input">
                                                <span class="find-label"><?php _e('Language', 'iii-dictionary') ?><span class="required-star"> *</span></span>
                                                <div id="show-language" class="show-language">
                                                  
                                                <span class="show-language-drop" style="margin-top: -2.5px; padding-right: 5px;"><i style="opacity:0;">0</i></span>
                                                </div>
                                                </div>
                                            
                                                <div class="form__boolean mt-bottom-10 clearfix language_drop" id="checkBoxSearch" style="margin-top: -14px">
                                                    <span class="Available-lg">Available language</span>
                                                    <ul id="list-language" style="font-size: 12px; color: #9c9c9c;">
                                                        <li>
                                                            <input type="checkbox" class="radio_buttons class_cb_search option-input-3 radio" value="en" data-lang="en" name="cb-lang"/>
                                                            <span>&ensp; English</span>
                                                        </li>
                                                        <li>
                                                            <input type="checkbox" class="radio_buttons class_cb_search option-input-3 radio" value="ja" data-lang="ja" name="cb-lang"/>
                                                            <span>&ensp; Japanese</span>
                                                        </li>
                                                        <li>
                                                            <input type="checkbox" class="radio_buttons class_cb_search option-input-3 radio" value="ko" data-lang="ko" name="cb-lang"/>
                                                            <span>&ensp; Korean</span>
                                                        </li>
                                                        <li>
                                                            <input type="checkbox" class="radio_buttons class_cb_search option-input-3 radio" value="zh" data-lang="zh" name="cb-lang"/>
                                                            <span>&ensp; Chinese</span>
                                                        </li>
                                                        <li>
                                                            <input type="checkbox" class="radio_buttons class_cb_search option-input-3 radio" value="zh-tw" data-lang="zh-tw" name="cb-lang"/>
                                                            <span>&ensp; Traditional Chinese</span>
                                                        </li>
                                                        <li>
                                                            <input type="checkbox" class="radio_buttons class_cb_search option-input-3 radio" value="vi" data-lang="vi" name="cb-lang"/>
                                                            <span>&ensp; Vietnamese</span>
                                                        </li>
                                                        <li>
                                                            <input type="checkbox" class="radio_buttons class_cb_search option-input-3 radio" value="ot" data-lang="ot" name="cb-lang"/>
                                                            <span>&ensp; Others</span>
                                                        </li>
                                                        
                                                    </ul>
                                                    <div style="padding:12px 0;">
                                                    <div class="ol-sm-6 col-md-6">
                                                        <button id="save-lg" class="btn-dark-blue border-btn" style="background: #009dcb;margin-left: -13px;" type="button" name="save_timelot">
                                                                                SAVE   
                                                                            </button>
                                                    </div>
                                                    <div class="ol-sm-6 col-md-6">
                                                        <button id="cancel-lg" class="btn-dark-blue border-btn" style="background: #CECECE;     margin-left: -5px;" type="button" name="cancel_timelot" >
                                                                                CANCEL
                                                                            </button>
                                                </div>
                                                </div>
                                                </div>
                                                </div>
                                        <div class="col-sm-6 col-md-6 col-xs-6 mt-top-mb-12">
                                            <div class="find-general-border">
                                                <span class="find-label"><?php _e('My Time Zone', 'iii-dictionary') ?><span class="required-star"> *</span></span>
                                            
                                            <div class="form-group border-ras select-style user-timezone mt-top-8">
                                                <select class="select-box-it form-control" name="time_zone" id="user-time-zone">
                                                    <option value="0" data-value="0" data-name="Europe/London" data-city="London">Select Time Zone</option>
                                                    <option value="1" data-value="-5" data-city="New York" data-name="America/New_York">New York</option>
                                                    <option value="2" data-value="-6" data-city="Minneapolis" data-name="America/Chicago">Minneapolis</option>
                                                    <option value="3" data-value="-5" data-city="Colorado" data-name="America/Denver">Colorado</option>
                                                    <option value="4" data-value="-7" data-city="SF/LA" data-name="America/Los_Angeles">SF/LA</option>
                                                    <option value="5" data-value="-10" data-city="Hawaii" data-name="Pacific/Honolulu">Hawaii</option>
                                                    <option value="6" data-value="+10" data-city="Guam" data-name="Pacific/Guam">Guam</option>
                                                    <option value="7" data-value="+9" data-city="Tokyo" data-name="Asia/Tokyo">Tokyo</option>
                                                    <option value="8" data-value="+9" data-city="Seoul" data-name="Asia/Seoul">Seoul</option>
                                                    <option value="9" data-value="+8" data-city="Beijing" data-name="Asia/Shanghai">Beijing</option>
                                                    <option value="10" data-value="+8" data-city="Xianyang" data-name="Asia/Shanghai">Xianyang</option>
                                                    <option value="11" data-value="+7" data-city="Hanoi" data-name="Asia/Ho_Chi_Minh">Hanoi</option>
                                                    <option value="12" data-value="+7" data-city="Bangkok" data-name="Asia/Bangkok">Bangkok</option>
                                                    <option value="13" data-value="+7" data-city="Myanmar" data-name="Asia/Rangoon">Myanmar</option>
                                                    <option value="14" data-value="+6" data-city="Bangladesh" data-name="Asia/Dhaka">Bangladesh</option>
                                                    <option value="15" data-value="+5" data-city="Sri Lanka" data-name="Asia/Colombo">Sri Lanka</option>
                                                    <option value="16" data-value="+5" data-city="New Delhi" data-name="Asia/Kolkata">New Delhi</option>
                                                    <option value="17" data-value="+5" data-city="Mumbai" data-name="Asia/Kolkata">Mumbai</option>
                                                    <option value="18" data-value="0" data-city="London" data-name="Europe/London">London</option>
                                                    <option value="19" data-value="+5" data-city="Sydney" data-name="Australia/Sydney">Sydney</option>
                                                </select>
                                            </div>
                                        </div>
                                        </div>
                                        <div class="clearfix"></div>    
                                        <div class="col-sm-12 col-md-12 profile-pic refreshclass mt-top-4" style="clear: both;">                                                
                                                    
                                                <div class="row">
                                                    <div class="col-sm-12 col-md-12"><p class="heading-acc">Profile Picture (optional)<img class="icon-about" src="<?php echo get_template_directory_uri(); ?>/library/images/icon_About.png" alt="info"></p></div>
                                                    <div class="col-sm-3 col-md-3 mt-top-9">
                                                        <div class="row">
                                                        <div class="form-group">
                                                            <div class="col-sm-4 col-md-4">
                                                            <img id="user-upload-avatar" src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Image_Person.png" alt="Profile Picture" style="display: inline-block; margin-right: 14px;">
                                                            </div>
                                                            <div class="col-sm-8 col-md-8">
                                                            <input class="form-control input-file" type="file" id="input-avatar" value="" >
                                                            <button class="btn-dark-blue border-btn" style="background: #cecece; display: inline-block; width: 100%; height: 50px; border-radius: 10px !important;" type="button" name="upload"  onclick="document.getElementById('input-avatar').click();"><?php _e('Browse', 'iii-dictionary') ?></button>
                                                        </div>
                                                        </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-sm-9 col-md-9 mt-top-9" >
                                                        <div class="find-general-border">
                                                            <span class="find-label">Image Location</span>
                                                            <div class="form-group">                                    
                                                            <input class="form-control input-path" id="profile-avatar" type="text">
                                                            <div class="clear-input" onclick="document.getElementById('profile_avatar').value=null;"></div>
                                                        </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        
                                        
                                        <div class="clearfix"></div>
                                        <div class="col-xs-12 col-sm-6 col-md-6 mt-bottom-24">
                                            <div class="form-group">
                                                <button class="btn-dark-blue border-btn" id="create-acc" style="background: #199eca; margin-top: 20px;" type="button" name="wp-submit">
                                                    <?php _e('Create Account', 'iii-dictionary') ?>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-6 col-md-6 mt-bottom-24">
                                            <div class="form-group">
                                                <button class="btn-dark-blue cancel-btn border-btn close-modal-account" style="background: #CECECE; margin-top: 20px !important;" type="button" name="cancel">
                                                    <?php _e('Cancel', 'iii-dictionary') ?>
                                                </button>

                                            </div>
                                        </div>
                                        <div class="clearfix"></div>
                                    </div>
                                </form>
                            </div>
                            <!-- Create Basic Account -->  

                            <!-- Profile -->
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
                                                <img id="profile-user-avatar" src="<?php echo $user_avatar ?>" alt="<?php echo $current_user->display_name ?>">
                                                <?php
                                                else :
                                                    ?>
                                                    <img id="profile-user-avatar" src="<?php echo get_template_directory_uri(); ?>/library/images/Profile_Image.png" alt="Profile Picture">
                                                <?php
                                                endif
                                                ?>
                                            </div>
                                        </div>
                                        <div class="col-sm-10 col-md-10">
                                            <div class="form-group" id="info-name">
                                                <label>My Name</label>
                                                <span class="color-black" id="profile-my-name"><?php
                                                    if ($is_user_logged_in) {
                                                        $display_name = get_user_meta($current_user->ID, 'display_name', true);
                                                        if (!empty($display_name) && $display_name != '')
                                                            echo $display_name;
                                                        else{
                                                            $ru_first_name = get_user_meta($current_user->ID, 'first_name', true);
                                                            $ru_last_name = get_user_meta($current_user->ID, 'last_name', true);
                                                            echo $ru_first_name.' '.$ru_last_name;
                                                    };} else
                                                        _e('N/A', 'iii-dictionary');
                                                    ?>
                                                </span>
                                            </div>
                                            <div class="btn-dark-blue border-btn check-availability" id="edit-profile">Update Profile</div>
                                        </div>
                                        <hr>
                                    </div>
                                    <div class="row line-profile">
                                        <div class="col-sm-6 col-md-6">
                                            <div class="form-group">
                                                <label>
                                                    <?php _e('Points Balance', 'iii-dictionary') ?>
                                                </label>
                                                <span class="color-yellow" id="profile-point-balance"><?php
                                                    if ($is_user_logged_in)
                                                        _e(ik_get_user_points($current_user->ID));
                                                    else
                                                        _e('N/A', 'iii-dictionary');
                                                    ?> (USD)
                                                </span>
                                                <button type="button" id="chase-point" class="border-btn">Purchase Points</button>
                                            </div>
                                            <hr>
                                        </div>
                                        <div class="col-sm-6 col-md-6">
                                            <div class="form-group">
                                                <label>
                                                    <?php _e('Points Earned', 'iii-dictionary') ?>
                                                </label>
                                                <span class="color-yellow" id="profile-point-earned"><?php
                                                    if ($is_user_logged_in)
                                                        _e(ik_get_user_earned($current_user->ID));
                                                    else
                                                        _e('N/A', 'iii-dictionary');
                                                    ?> (USD)
                                                </span>
                                            </div>
                                            <hr>
                                        </div>
                                    </div>
                                    <div class="row line-profile">
                                        <div class="col-sm-6 col-md-6">
                                            <div class="form-group">
                                                <label>
                                                    <?php _e('Email Address (for login)', 'iii-dictionary') ?>
                                                </label>
                                                <span class="color-black" id="profile-user-email"><?php
                                                    if ($is_user_logged_in)
                                                        echo $current_user->user_email;
                                                    else
                                                        _e('N/A', 'iii-dictionary');
                                                    ?>                        
                                                </span>
                                            </div>
                                            <hr>
                                        </div>
                                        <div class="col-sm-6 col-md-6">
                                            <div class="form-group">
                                                <label>
                                                    <?php _e('Date of Birth (month/date/year)', 'iii-dictionary') ?>
                                                </label>
                                                <span class="color-black" id="profile-date-birth">
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
                                                <label>
                                                    <?php _e('Language', 'iii-dictionary') ?>
                                                </label>
                                                <span class="color-black" id="profile-language">
                                                    <?php
                                                    if ($is_user_logged_in) {
                                                        $language_type = get_user_meta($current_user->ID, 'language_type', true);
                                                        if (!empty($language_type) && $language_type != '') {

                                                            $langs = array(
                                                                'en' => 'English',
                                                                'ja' => '日本語',
                                                                'ko' => '한국어',                                    
                                                                'zh' => '中文',
                                                                'zh-tw' => '中國',
                                                                'vi' => 'Tiếng Việt',
                                                                'ot' => 'Others'
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
                                                <label><?php _e('Gender', 'iii-dictionary') ?></label>
                                                <span class="color-black" id="gender-show">
                                                        <?php
                                                        if ($is_user_logged_in) {
                                                            $gender_show = get_user_meta($current_user->ID, 'gender', true);
                                                            if (!empty($gender_show) && $gender_show != '')
                                                                echo $gender_show;
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
                                        <!-- <div class="col-sm-6 col-md-6">
                                            <div class="form-group">
                                                <label>
                                                    <?php _e('Mobile Phone Number', 'iii-dictionary') ?>
                                                </label>
                                                <span class="color-black" id="profile-mobile-phone">
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
                                        </div> -->
                                    </div>
                                    <!-- <div class="row line-profile">
                                        <div class="col-sm-6 col-md-6">
                                            <div class="form-group">
                                                <label>
                                                    <?php _e('Last School Attended', 'iii-dictionary') ?>
                                                </label>
                                                <span class="color-black" id="profile-last-attended">
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
                                                <label>
                                                    <?php _e('Skype ID', 'iii-dictionary') ?>
                                                </label>
                                                <span class="color-black" id="profile-skype-id">
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
                                    </div> -->
                                    <div class="row line-profile">
                                            <div class="col-sm-6 col-md-6">
                                                <label><?php _e('Time Zone', 'iii-dictionary') ?></label>
                                                 <span class="color-black" id="profile-skype-id">
                                                        <?php
                                                        if ($is_user_logged_in) {
                                                            
                                                            if (!empty($my_timezone_index) && $my_timezone_index != ''){
                                                                if($my_timezone_index == '1' ) echo 'New York';
                                                                if($my_timezone_index == '2' ) echo 'Minneapolis';
                                                                if($my_timezone_index == '3' ) echo 'Colorado';
                                                                if($my_timezone_index == '4' ) echo 'SF/LA';
                                                                if($my_timezone_index == '5' ) echo 'Hawaii';
                                                                if($my_timezone_index == '6' ) echo 'Guam';
                                                                if($my_timezone_index == '7') echo 'Tokyo';                     
                                                                if($my_timezone_index == '8' ) echo 'Seoul';
                                                                if($my_timezone_index == '9' ) echo 'Beijing';
                                                                if($my_timezone_index == '10' ) echo 'Xianyang';
                                                                if($my_timezone_index == '11' ) echo 'Hanoi';
                                                                if($my_timezone_index == '12' ) echo 'Bangkok';
                                                                if($my_timezone_index == '13' ) echo 'Myanmar';
                                                                if($my_timezone_index == '14' ) echo 'Bangladesh';
                                                                if($my_timezone_index == '15' ) echo 'Sri Lanka';
                                                                if($my_timezone_index == '16' ) echo 'New Delhi';
                                                                if($my_timezone_index == '17' ) echo 'Mumbai';
                                                                if($my_timezone_index == '18' ) echo 'London';
                                                                if($my_timezone_index == '19' ) echo 'Sydney';

                                                            }
                                                            else
                                                                _e('N/A', 'iii-dictionary');
                                                        }else {
                                                            _e('N/A', 'iii-dictionary');
                                                        }
                                                        ?>
                                                    </span>
                                            </div>
                                            
                                        </div>
                                </form>
                            </div>
                            <!-- Profile --> 

                            <!-- Update My Account -->
                            <div id="updateinfo" class="tab-pane fade">
                                <?php
                                    $update_username = '';
                                    $update_user_password = '';
                                    $update_birth_g = '';
                                    $update_first_name = '';
                                    $update_last_name = '';
                                    $update_birth_m = '';
                                    $update_birth_d = '';
                                    $update_birth_y = '';
                                    $update_language = array();
                                    $profile_value = '';
                                    $update_mobile_number = '';
                                    $update_profession = '';
                                    $update_last_school = '';
                                    $update_previous_school = '';
                                    $update_skype = '';
                                    $desc_tell_update = '';
                                    $subject_type_update = array();
                                    $update_school_name = '';
                                    $update_teaching_link = '';
                                    $update_teaching_subject = '';
                                    $update_years = '';
                                    $update_school_attend = '';
                                    $update_gpa = '';
                                    $update_grade = '';
                                    $update_major = '';
                                    $update_school_name1 = '';
                                    $update_school_name2 = '';
                                    $update_school_link1 = '';
                                    $update_school_link2 = '';
                                    $update_any_other = '';
                                    $update_description = '';
                                    $update_student_link = '';
                                    $time_zone = '';
                                    $time_zone_index = '';
                                    if ($is_user_logged_in) {
                                        $update_username = $current_user->user_email;
                                        $update_first_name = get_user_meta($current_user->ID, 'first_name', true);
                                        $update_last_name = get_user_meta($current_user->ID, 'last_name', true);
                                        $update_user_password = get_user_meta($current_user->ID, 'user_password', true);
                                        $update_birth_g = get_user_meta($current_user->ID, 'gender', true);
                                        $date_of_birth = get_user_meta($current_user->ID, 'date_of_birth', true);
                                        $time_zone = get_user_meta($current_user->ID, 'user_timezone', true);
                                        $time_zone_index = get_user_meta($current_user->ID, 'time_zone_index', true);
                                        if($date_of_birth != ''){
                                        $arr_birth = explode('/', $date_of_birth);
                                        $update_birth_m = isset($arr_birth[0])?$arr_birth[0]:'';
                                        $update_birth_d = isset($arr_birth[1])?$arr_birth[1]:'';
                                        $update_birth_y = isset($arr_birth[2])?$arr_birth[2]:'';
                                        }
                                        $language_type = get_user_meta($current_user->ID, 'language_type', true);
                                        if($language_type != '') $update_language = explode(',', $language_type);

                                        $profile_value = get_user_meta($current_user->ID, 'ik_user_avatar', true);

                                        $update_mobile_number = get_user_meta($current_user->ID, 'mobile_number', true);
                                        $update_profession = get_user_meta($current_user->ID, 'user_profession', true);
                                        $update_last_school = get_user_meta($current_user->ID, 'last_school', true);
                                        $update_previous_school = get_user_meta($current_user->ID, 'previous_school', true);
                                        $update_skype = get_user_meta($current_user->ID, 'skype_id', true);
                                        $desc_tell_update = get_user_meta($current_user->ID, 'desc_tell_me', true);
                                        $subject_type = get_user_meta($current_user->ID, 'subject_type', true);
                                        if($subject_type != '') $subject_type_update = explode(',', $subject_type);
                                        $update_school_name = get_user_meta($current_user->ID, 'school_name', true);
                                        $update_teaching_link = get_user_meta($current_user->ID, 'teaching_link', true);
                                        $update_teaching_subject = get_user_meta($current_user->ID, 'teaching_subject', true);
                                        $update_student_link = get_user_meta($current_user->ID, 'student_link', true);
                                        $update_years = get_user_meta($current_user->ID, 'user_years', true);
                                        $update_school_attend = get_user_meta($current_user->ID, 'school_attend', true);
                                        $update_gpa = get_user_meta($current_user->ID, 'user_gpa', true);
                                        $update_grade = get_user_meta($current_user->ID, 'user_grade', true);
                                        $update_major = get_user_meta($current_user->ID, 'user_major', true);
                                        $update_school_name1 = get_user_meta($current_user->ID, 'school_name1', true);
                                        $update_school_name2 = get_user_meta($current_user->ID, 'school_name2', true);
                                        $update_school_link1 = get_user_meta($current_user->ID, 'school_link1', true);
                                        $update_school_link2 = get_user_meta($current_user->ID, 'school_link2', true);
                                        $update_any_other = get_user_meta($current_user->ID, 'any_other', true);
                                        $update_description = get_user_meta($current_user->ID, 'subject_description', true);
                                    }
                                ?>
                                <div class="student-center">
                                    <p class="my-account">My Account</p>
                                    <p class="tutor-acc">UPDATE MY ACCOUNT</p>
                                </div>
                                <form method="post" id="myUpdate" action="" name="updateAccount" enctype="multipart/form-data" autocomplete="off">
                                    
                                    <p class="heading-acc" style="color: #36a93f; margin-top: 16px; font-size: 20px; font-family: Myriad_regular;">Basic Account <img class="icon-about" src="<?php echo get_template_directory_uri(); ?>/library/images/icon_About.png" alt="info"></p>
                                    <span style="color: red; font-size: 14px; float: right; margin-top: -30px;"> (<span style=" position: absolute;  font-weight: bold;   padding-left: 1px; font-size: 14pt">*</span>&nbsp &nbsp) Required</span>
                                    <div class="row">
                                        <div class="col-sm-9 col-md-9">
                                        <div class="find-general-border">
                                            <span class="find-label"><?php _e('Email Address', 'iii-dictionary') ?><span class="required-star"> *</span></span>
                                            <div class="form-group">
                                                <input id="update_username" class="form-control" name="update_username" type="text" value="<?php echo $update_username ?>" readonly="">
                                                
                                            </div>
                                        </div>
                                        </div>
                                        <div class="col-sm-3 col-md-3">
                                            <div id="update-gender-pc">
                                                <div class="find-general-border">
                                                <span class="find-label"><?php _e('Gender', 'iii-dictionary') ?><span class="required-star"> *</span></span>                                            
                                                <div class="form-group">
                                                    <div class="border-ras select-style" id="gender_up">
                                                        
                                                        <select id="update_birth_g_pc" class="select-box-it form-control" name="update_birth_g_pc">
                                                            <option value="" <?php if($update_birth_g == '') echo 'selected="selected"' ?>>Gender</option>
                                                            <option value="Male" <?php if($update_birth_g == "Male")echo 'selected="selected"'?>>Male</option>
                                                            <option value="Female" <?php if($update_birth_g == "Female")echo 'selected="selected"'?>>Female</option>
                                                        </select>
                                                        
                                                    </div>
                                                </div>
                                            </div>
                                            </div>
                                        </div>
                                        <div class="clearfix"></div>
                                        <div class="col-sm-6 col-md-6">
                                            <div class="find-general-border">
                                                <span class="find-label"><?php _e('Password', 'iii-dictionary') ?><span class="required-star"> *</span></span>
                                            <div class="form-group">
                                                <input id="update_password" class="form-control border-ras" name="update_password" type="text" value="" required>
                                                <div class="clear-input" onclick="document.getElementById('update_password').value=null;"></div>
                                            </div>
                                        </div>
                                        </div>
                                        <div class="col-sm-6 col-md-6">
                                            <div class="find-general-border">
                                                <span class="find-label"><?php _e('Confirm Password', 'iii-dictionary') ?><span class="required-star"> *</span></span>
                                            <div class="form-group">
                                                <input id="update_confirmpass" class="form-control border-ras" name="update_confirmpass" type="text" value="" required>
                                                <div class="clear-input" onclick="document.getElementById('update_confirmpass').value=null;"></div>
                                            </div>
                                        </div>
                                        </div>

                                        
                                        <div class="clearfix"></div>
                                        
                                        <div class="col-sm-6 col-md-6">
                                            <div class="find-general-border">
                                                <span class="find-label"><?php _e('First Name', 'iii-dictionary') ?><span class="required-star"> *</span></span>
                                            <div class="form-group">
                                                <input id="update_first_name" class="form-control" name="update_first_name" type="text" value="<?php echo $update_first_name ?>" required>
                                                <div class="clear-input" onclick="document.getElementById('update_first_name').value=null;"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 col-md-6">
                                            <div class="find-general-border">
                                                <span class="find-label"><?php _e('Last Name', 'iii-dictionary') ?><span class="required-star"> *</span></span>
                                            <div class="form-group">
                                                <input id="update_last_name" class="form-control" name="update_last_name" type="text" value="<?php echo $update_last_name ?>" required>
                                                <div class="clear-input" onclick="document.getElementById('update_last_name').value=null;"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="clearfix"></div>
                                        <p class=" heading-acc create-label col-sm-12 col-md-12 mt-top-4">Date of Birth<img class="icon-about" src="<?php echo get_template_directory_uri(); ?>/library/images/icon_About.png" alt="info"></p>
                                        <div class="col-sm-12 col-md-12">

                                           
                                                
                                                <div class="row tiny-gutter">
                                                    <div class="col-xs-12 col-sm-4 col-md-4 border-ras select-style" id="update_month">
                                                        <div class="find-general-border">
                                                            <span class="find-label"><?php _e('Month', 'iii-dictionary') ?><span class="required-star"> *</span></span>
                                                        <div class="form-group">
                                                        <select id="update_birth_m" class="select-box-it form-control" name="update-birth-m">
                                                            <option value="">(Month)</option>
                                                            <?php 
                                                            for ($i = 1; $i <= 12; $i++) : 
                                                                $pad_str = str_pad($i, 2, '0', STR_PAD_LEFT);
                                                                if($pad_str == $update_birth_m)
                                                                    $sel_um = 'selected="selected"';
                                                                else
                                                                    $sel_um = ''; 
                                                            ?>
                                                            <option value="<?php echo $pad_str ?>" <?php echo $sel_um ?>>
                                                                <?php echo $pad_str ?>
                                                            </option>
                                                            <?php endfor ?>
                                                        </select>
                                                    </div>
                                                    </div>
                                                    </div>
                                                    <div class="col-xs-12 col-sm-4 col-md-4 border-ras select-style" id="update_date">
                                                        <div class="find-general-border">
                                                            <span class="find-label"><?php _e('Day', 'iii-dictionary') ?><span class="required-star"> *</span></span>
                                                        <div class="form-group">
                                                        <select id="update_birth_d" class="select-box-it form-control" name="update-birth-d">
                                                            <option value="">(Day)</option>
                                                            <?php 
                                                            for ($i = 1; $i <= 31; $i++): 
                                                                $pad_str = str_pad($i, 2, '0', STR_PAD_LEFT);
                                                                if($pad_str == $update_birth_d)
                                                                    $sel_ud = 'selected="selected"';
                                                                else
                                                                    $sel_ud = ''; 
                                                            ?>
                                                            <option value="<?php echo $pad_str ?>" <?php echo $sel_ud ?>>
                                                                <?php echo $pad_str ?>
                                                            </option>
                                                            <?php endfor ?>
                                                        </select>
                                                    </div>
                                                    </div>
                                                    </div>
                                                    <div class="col-xs-12 col-sm-4 col-md-4 year-mb">
                                                        <div class="find-general-border">
                                                            
                                                            <span class="find-label"><?php _e('Year', 'iii-dictionary') ?><span class="required-star"> *</span></span>
                                                            <div class="form-group">
                                                        <input id="update_birth_y" class="form-control" name="update-birth-y" type="text" value="<?php echo $update_birth_y ?>" required>
                                                        <div class="clear-input" onclick="document.getElementById('update_birth_y').value=null;"></div>
                                                        </div>
                                                    </div>
                                                    </div>

                                                    
                                                </div>
                                            
                                        </div>
                                        <div class="clearfix"></div>
                                        <p class=" heading-acc create-label col-sm-12 col-md-12 mt-bottom-11">Language & Time Zone<img class="icon-about" src="<?php echo get_template_directory_uri(); ?>/library/images/icon_About.png" alt="info"></p>
                                        <div id="language-timezone-update" class="col-sm-6 col-md-6 col-xs-6 mt-top-mb-12">
                                                
                                                <div  class="find-general-border language-input">
                                                <span class="find-label"><?php _e('Language', 'iii-dictionary') ?><span class="required-star"> *</span></span>
                                                <div id="show-language-up" class="show-language">

                                                    <?php 
                                                         $languagelist = "";
                                                        if(count($update_language) > 0){
                                                       
                                                        if(in_array("en", $update_language)){$languagelist = $languagelist."English, ";}
                                                        if(in_array("ja", $update_language)){$languagelist = $languagelist."Japanese, ";}
                                                        if(in_array("ko", $update_language)){$languagelist = $languagelist."Korean, ";}
                                                        if(in_array("zh", $update_language)){$languagelist = $languagelist."Chinese, ";}
                                                        if(in_array("zh-tw", $update_language)){$languagelist = $languagelist."Traditional Chinese, ";}
                                                        if(in_array("vi", $update_language)){$languagelist = $languagelist."Vietnamese, ";}
                                                        if(in_array("ot", $update_language)){$languagelist = $languagelist."Others, ";}

                                                        };
                                                        echo rtrim($languagelist,", ");
                                                        
                                                        ?>
                                                        <span class="selectboxit-arrow-container" style="margin-top: -2.5px; margin-right: 0"><i style="opacity:0;">0</i></span>
                                                </div>
                                                </div>
                                                <div class="form__boolean mt-bottom-10 clearfix language_drop" id="checkBoxSearch" style="margin-top: -14px">
                                                    <span class="Available-lg">Available language</span>
                                                    <ul id="list-language" style="font-size: 12px; color: #9c9c9c;">
                                                        <li>
                                                            <input type="checkbox" class="radio_buttons option-input-3 radio" value="en" <?php if(count($update_language) > 0 && in_array("en", $update_language)) echo 'checked="checked"'; ?> name="update-cb-lang"/>
                                                            <span>&ensp; English</span>
                                                        </li>
                                                        
                                                        <li>
                                                            <input type="checkbox" class="radio_buttons option-input-3 radio" value="ja" <?php if(count($update_language) > 0 && in_array("ja", $update_language)) echo 'checked="checked"'; ?> name="update-cb-lang"/>
                                                            <span>&ensp; Japanese</span>
                                                        </li>
                                                    
                                                        <li>
                                                            <input type="checkbox"  class="radio_buttons option-input-3 radio" value="ko" <?php if(count($update_language) > 0 && in_array("ko", $update_language)) echo 'checked="checked"'; ?> name="update-cb-lang"/>
                                                            <span>&ensp; Korean</span>
                                                        </li>
                                                    
                                                        <li>
                                                            <input type="checkbox" class="radio_buttons option-input-3 radio" value="zh" <?php if(count($update_language) > 0 && in_array("zh", $update_language)) echo 'checked="checked"'; ?> name="update-cb-lang"/>
                                                            <span>&ensp; Chinese</span>
                                                        </li>
                                                    
                                                        <li>
                                                            <input type="checkbox" class="radio_buttons option-input-3 radio" value="zh-tw" <?php if(count($update_language) > 0 && in_array("zh-tw", $update_language)) echo 'checked="checked"'; ?> name="update-cb-lang"/>
                                                            <span>&ensp; Traditional Chinese</span>
                                                        </li>
                                                    
                                                        <li>
                                                            <input type="checkbox" class="radio_buttons option-input-3 radio" value="vi" <?php if(count($update_language) > 0 && in_array("vi", $update_language)) echo 'checked="checked"'; ?> name="update-cb-lang"/>
                                                            <span>&ensp; Vietnamese</span>
                                                        </li>
                                                    
                                                        <li>
                                                            <input type="checkbox" class="radio_buttons option-input-3 radio" value="ot" <?php if(count($update_language) > 0 && in_array("ot", $update_language)) echo 'checked="checked"'; ?> name="update-cb-lang"/>
                                                            <span>&ensp; Others</span>
                                                        </li>
                                                    </ul>
                                                    <div style="padding:12px 0;">
                                                <div class="ol-sm-6 col-md-6">
                                                   <button id="save-lg-up" class="btn-dark-blue border-btn" style="background: #009dcb;margin-left: -13px;" type="button" name="save_timelot">
                                                                                SAVE   
                                                                            </button>
                                                </div>
                                                <div class="ol-sm-6 col-md-6">
                                                     <button id="cancel-lg-up" class="btn-dark-blue border-btn" style="background: #CECECE;margin-left: -5px;" type="reset" name="cancel_timelot" >
                                                                                CANCEL
                                                                            </button>
                                                </div>
                                                </div>
                                                </div>
                                                </div>
                                        <div class="col-sm-6 col-md-6 col-xs-6 mt-top-mb-12">
                                            <div class="find-general-border">
                                            <span class="find-label">
                                                <?php _e('My Time Zone', 'iii-dictionary') ?><span class="required-star"> *</span>
                                            </span>
                                            <div class="form-group border-ras select-style user-timezone mt-top-8">
                                                <select class="select-box-it form-control" name="time_zone" id="update-time-zone">
                                                    <option value="0" data-value="0" data-name="Europe/London" data-city="London" <?php if($time_zone_index == '0' ) echo 'selected="selected"'; ?>>Select Time Zone</option>
                                                    <option value="1" data-value="-5" data-city="New York" data-name="America/New_York" <?php if($time_zone_index == '1' ) echo 'selected="selected"'; ?>>New York</option>
                                                    <option value="2" data-value="-6" data-city="Minneapolis" data-name="America/Chicago" <?php if($time_zone_index == '2' ) echo 'selected="selected"'; ?>>Minneapolis</option>
                                                    <option value="3" data-value="-5" data-city="Colorado" data-name="America/Denver" <?php if($time_zone_index == '3' ) echo 'selected="selected"'; ?>>Colorado</option>
                                                    <option value="4" data-value="-7" data-city="SF/LA" data-name="America/Los_Angeles" <?php if($time_zone_index == '4' ) echo 'selected="selected"'; ?>>SF/LA</option>
                                                    <option value="5" data-value="-10" data-city="Hawaii" data-name="Pacific/Honolulu" <?php if($time_zone_index == '5' ) echo 'selected="selected"'; ?>>Hawaii</option>
                                                    <option value="6" data-value="+10" data-city="Guam" data-name="Pacific/Guam" <?php if($time_zone_index == '6' ) echo 'selected="selected"'; ?>>Guam</option>
                                                    <option value="7" data-value="+9" data-city="Tokyo" data-name="Asia/Tokyo" <?php if($time_zone_index == '7' ) echo 'selected="selected"'; ?>>Tokyo</option>
                                                    <option value="8" data-value="+9" data-city="Seoul" data-name="Asia/Seoul" <?php if($time_zone_index == '8' ) echo 'selected="selected"'; ?>>Seoul</option>
                                                    <option value="9" data-value="+8" data-city="Beijing" data-name="Asia/Shanghai" <?php if($time_zone_index == '9' ) echo 'selected="selected"'; ?>>Beijing</option>
                                                    <option value="10" data-value="+8" data-city="Xianyang" data-name="Asia/Shanghai" <?php if($time_zone_index == '10' ) echo 'selected="selected"'; ?>>Xianyang</option>
                                                    <option value="11" data-value="+7" data-city="Hanoi" data-name="Asia/Ho_Chi_Minh" <?php if($time_zone_index == '11' ) echo 'selected="selected"'; ?>>Hanoi</option>
                                                    <option value="12" data-value="+7" data-city="Bangkok" data-name="Asia/Bangkok" <?php if($time_zone_index == '12' ) echo 'selected="selected"'; ?>>Bangkok</option>
                                                    <option value="13" data-value="+7" data-city="Myanmar" data-name="Asia/Rangoon" <?php if($time_zone_index == '13' ) echo 'selected="selected"'; ?>>Myanmar</option>
                                                    <option value="14" data-value="+6" data-city="Bangladesh" data-name="Asia/Dhaka" <?php if($time_zone_index == '14' ) echo 'selected="selected"'; ?>>Bangladesh</option>
                                                    <option value="15" data-value="+5" data-city="Sri Lanka" data-name="Asia/Colombo" <?php if($time_zone_index == '15' ) echo 'selected="selected"'; ?>>Sri Lanka</option>
                                                    <option value="16" data-value="+5" data-city="New Delhi" data-name="Asia/Kolkata" <?php if($time_zone_index == '16' ) echo 'selected="selected"'; ?>>New Delhi</option>
                                                    <option value="17" data-value="+5" data-city="Mumbai" data-name="Asia/Kolkata" <?php if($time_zone_index == '17' ) echo 'selected="selected"'; ?>>Mumbai</option>
                                                    <option value="18" data-value="0" data-city="London" data-name="Europe/London" <?php if($time_zone_index == '18' ) echo 'selected="selected"'; ?>>London</option>
                                                    <option value="19" data-value="+5" data-city="Sydney" data-name="Australia/Sydney" <?php if($time_zone_index == '19' ) echo 'selected="selected"'; ?>>Sydney</option>
                                                </select>
                                            </div>
                                        </div>
                                        </div>
                                        <div class="clearfix"></div>

                                        
                                            <p class="heading-acc create-label col-sm-12 col-md-12 mt-bottom-4 mt-top-4">Profile Picture (optional)<img class="icon-about" src="<?php echo get_template_directory_uri(); ?>/library/images/icon_About.png" alt="info"></p>
                                            
                                            
                                                <div class="col-sm-3 col-md-3 mt-top-9">
                                                    <div class="row">
                                                        <div class="col-sm-4 col-md-4">
                                                    

                                                        <img id="user-upload-img" src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Image_Person.png" alt="Profile Picture" style="display: inline-block; margin-right: 14px;">
                                                        </div>
                                                        <div class="col-sm-8 col-md-8">
                                                        <input class="form-control input-file" type="file" id="input-image" value="">
                                                        <button class="btn-dark-blue border-btn" style="background: #cecece; display: inline-block; height: 50px;" type="button" name="upload" onclick="document.getElementById('input-image').click();">
                                                            <?php _e('Browse', 'iii-dictionary') ?>
                                                        </button>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-sm-9 col-md-9 mt-top-9">
                                                    <div class="find-general-border">
                                                        <span class="find-label"><?php _e('Image Location', 'iii-dictionary') ?></span>
                                                    <div class="form-group">
                                                        <input class="form-control input-path" id="profile-value" type="text" value="<?php echo $profile_value ?>">
                                                    </div>
                                                </div>
                                                </div>
                                            
                                        
                                        <div class="clearfix"></div>
                                        
                                        <?php 
                                            if ($is_user_logged_in && (is_mw_qualified_teacher($current_user->ID) || is_mw_registered_teacher($current_user->ID)))
                                                $style = 'style="display: none;"';
                                            else
                                                $style = 'style="display: none;"';
                                        ?>
                                        <div id="tutor-regis-update" class="col-md-12" <?php echo $style ?>>
                                            <h4>Teacher and Tutor Account Info:</h4>
                                            <div id="info-update">
                                                <div class="row">
                                                    <div class="col-sm-6 col-md-6 col-xs-12">
                                                        <div class="form-group">
                                                            <input type="text" class="form-control" name="mobile_number" value="<?php echo $update_mobile_number ?>" id="mobile-number-update">
                                                            <span class="placeholder"><?php _e('Mobile Number', 'iii-dictionary') ?>:</span>
                                                        </div>
                                                    </div>

                                                    <div class="col-sm-6 col-md-6 col-xs-12 mt-top-mb">
                                                        <div class="form-group">
                                                            <input type="text" class="form-control" name="profession" value="<?php echo $update_profession ?>" id="profession-update">
                                                            <span class="placeholder"><?php _e('Profession', 'iii-dictionary') ?>:</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6 col-md-6 col-xs-12 mt-top-14">
                                                        <div class="form-group">
                                                            <input type="text" class="form-control" name="last_school" value="<?php echo $update_last_school ?>" id="last-school-update">
                                                            <span class="placeholder"><?php _e('Last School Attended', 'iii-dictionary') ?>:</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6 col-md-6 col-xs-12 mt-top-14">
                                                        <div class="form-group">
                                                            <input type="text" class="form-control" name="previous_school" value="<?php echo $update_previous_school ?>" id="previous-school-update">
                                                            <span class="placeholder"><?php _e('School Taught (if any)', 'iii-dictionary') ?>:</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6 col-md-6 col-xs-12 mt-top-14">
                                                        <div class="form-group">
                                                            <input type="text" class="form-control" name="skype" value="<?php echo $update_skype ?>" id="skype-update">
                                                            <span class="placeholder"><?php _e('Skype ID (if any)', 'iii-dictionary') ?>:</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <label class="mt-top-10 mt-bottom-12">Tell me why you like Tutoring and Teaching</label>
                                            

                                            <label class="mt-top-12">Subjects you Interested in Tutor (check to all applied)</label>
                                            <div class="row">
                                                <div class="form__boolean chk-subject-type mt-bottom-10 clearfix" id="checkBoxSearch" style="margin-top: 0">
                                                    <div class="col-sm-4 col-md-3 col-xs-4 cb-type3">
                                                        <label>
                                                            <input type="checkbox" class="radio_buttons option-input-2 radio" value="english_writting" <?php if(count($subject_type_update)> 0 && in_array("english_writting", $update_language)) echo 'checked="checked"'; ?> name="subject_type_update"/> English Writing
                                                        </label>
                                                    </div>
                                                    <div class="col-sm-4 col-md-3 col-xs-4 cb-type3">
                                                        <label>
                                                            <input type="checkbox" class="radio_buttons option-input-2 radio" value="english_conversation" <?php if(count($subject_type_update)> 0 && in_array("english_conversation", $update_language)) echo 'checked="checked"'; ?> name="subject_type_update"/> English Conversation
                                                        </label>
                                                    </div>
                                                    <div class="col-sm-4 col-md-3 col-xs-4 cb-type3">
                                                        <label>
                                                            <input type="checkbox" class="radio_buttons option-input-2 radio" value="math_elementary" <?php if(count($subject_type_update)> 0 && in_array("math_elementary", $update_language)) echo 'checked="checked"'; ?> name="subject_type_update"/> Math (upto elementary)
                                                        </label>
                                                    </div>
                                                    <div class="col-sm-3 col-md-3 col-xs-4 cb-type3">
                                                        <label>
                                                            <input type="checkbox" class="radio_buttons option-input-2 radio" value="math_any_level" <?php if(count($subject_type_update)> 0 && in_array("math_any_level", $update_language)) echo 'checked="checked"'; ?> name="subject_type_update"/> Math (any level)
                                                        </label>
                                                    </div>
                                                    <div class="col-sm-2 col-md-2 col-xs-4 cb-type3">
                                                        <label>
                                                            <input type="checkbox" class="radio_buttons option-input-2 radio" value="other" <?php if(count($subject_type_update)> 0 && in_array("other", $update_language)) echo 'checked="checked"'; ?> name="subject_type_update"/> Others
                                                        </label>
                                                    </div>
                                                    <div class="col-sm-7 col-md-10 col-xs-12 mt-top-14">
                                                        <div class="form-group">
                                                            <input type="text" class="form-control" name="description" value="<?php echo $update_description ?>" id="description-update">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <label class="mt-top-9 mt-bottom-7">Teaching Experience at School</label>
                                            <div class="row mt-top-9">
                                                <div class="col-sm-6 col-md-6 col-xs-12">
                                                    <div class="form-group">
                                                        <input type="text" class="form-control" name="school_name" value="<?php echo $update_school_name ?>" id="school-name-update">
                                                        <span class="placeholder"><?php _e('School Name', 'iii-dictionary') ?>:</span>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6 col-md-6 col-xs-12 mt-top-mb">
                                                    <div class="form-group">
                                                        <input type="text" class="form-control" name="teaching_link" value="<?php echo $update_teaching_link ?>" id="teaching-link-update">
                                                        <span class="placeholder"><?php _e('Link (if any)', 'iii-dictionary') ?>:</span>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6 col-md-6 col-xs-12 mt-top-14">
                                                    <div class="form-group">
                                                        <input type="text" class="form-control" name="teaching_subject" value="<?php echo $update_teaching_subject ?>" id="subject-update">
                                                        <span class="placeholder"><?php _e('Subject', 'iii-dictionary') ?>:</span>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6 col-md-6 col-xs-12 mt-top-14">
                                                    <div class="form-group">
                                                        <input type="text" class="form-control" name="years" value="<?php echo $update_years ?>" id="years-update">
                                                        <span class="placeholder"><?php _e('Years', 'iii-dictionary') ?>:</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <label class="mt-top-9 mt-bottom-7">Teaching Experience at Student</label>
                                            <div class="row mt-top-9">
                                                <div class="col-sm-6 col-md-6 col-xs-12">
                                                    <div class="form-group">
                                                        <input type="text" class="form-control" name="school_attend" value="<?php echo $update_school_attend ?>" id="school-attend-update">
                                                        <span class="placeholder"><?php _e('Attending', 'iii-dictionary') ?>:</span>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6 col-md-6 col-xs-12 mt-top-mb">
                                                    <div class="form-group">
                                                        <input type="text" class="form-control" name="student_link" value="<?php echo $update_student_link ?>" id="student-link-update">
                                                        <span class="placeholder"><?php _e('Link (if any)', 'iii-dictionary') ?>:</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mt-top-14">
                                                <div class="col-sm-4 col-md-4 cb-type4">
                                                    <div class="form-group border-ras select-style">
                                                        <select class="select-box-it form-control" name="birth-m" id="grade-update">
                                                            <option value="1" <?php if($update_grade=='1' ) echo 'selected="selected"'; ?>>Freshman</option>
                                                            <option value="2" <?php if($update_grade=='2' ) echo 'selected="selected"'; ?>>Sophomore</option>
                                                            <option value="3" <?php if($update_grade=='3' ) echo 'selected="selected"'; ?>>Junior</option>
                                                            <option value="4" <?php if($update_grade=='4' ) echo 'selected="selected"'; ?>>Senior</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-sm-4 col-md-4 cb-type4">
                                                    <div class="form-group">
                                                        <input type="text" class="form-control" name="gpa" value="<?php echo $update_gpa ?>" id="gpa-update">
                                                        <span class="placeholder"><?php _e('GPA', 'iii-dictionary') ?>:</span>
                                                    </div>
                                                </div>
                                                <div class="col-sm-4 col-md-4 cb-type4 mt-top-mb">
                                                    <div class="form-group">
                                                        <input type="text" class="form-control" name="major" value="<?php echo $update_major ?>" id="major-update">
                                                        <span class="placeholder"><?php _e('Major', 'iii-dictionary') ?>:</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <label class="mt-top-9 mt-bottom-7">Educational Background</label>
                                            <div class="row mt-top-9">
                                                <div class="col-sm-6 col-md-6 col-xs-12">
                                                    <div class="form-group">
                                                        <input type="text" class="form-control" name="school_name1" value="<?php echo $update_school_name1 ?>" id="school-name1-update">
                                                        <span class="placeholder"><?php _e('School Name 1', 'iii-dictionary') ?>:</span>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6 col-md-6 col-xs-12 mt-top-mb">
                                                    <div class="form-group">
                                                        <input type="text" class="form-control" name="school_link1" value="<?php echo $update_school_link1 ?>" id="school-link1-update">
                                                        <span class="placeholder"><?php _e('Link (if any)', 'iii-dictionary') ?>:</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mt-top-14">
                                                <div class="col-sm-6 col-md-6 col-xs-12">
                                                    <div class="form-group">
                                                        <input type="text" class="form-control" name="school_name2" value="<?php echo $update_school_name2 ?>" id="school-name2-update">
                                                        <span class="placeholder"><?php _e('School Name 2', 'iii-dictionary') ?>:</span>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6 col-md-6 col-xs-12 mt-top-mb">
                                                    <div class="form-group">
                                                        <input type="text" class="form-control" name="school_link2" value="<?php echo $update_school_link2 ?>" id="school-link2-update">
                                                        <span class="placeholder"><?php _e('Link (if any)', 'iii-dictionary') ?>:</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mt-top-14">
                                                <div class="col-sm-12 col-md-12 col-xs-12">
                                                    <div class="form-group">
                                                        <input type="text" class="form-control" name="any_other" value="<?php echo $update_any_other ?>" id="any-other-update">
                                                        <span class="placeholder"><?php _e('Others', 'iii-dictionary') ?>:</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-top-14">
                                        <div class="col-sm-6 col-md-6 col-xs-12 mt-top-4">
                                            <div class="form-group">
                                                <button id="update-teacher" class="btn-dark-blue border-btn" style="background: #009dcb;" type="button" name="send-tutor">
                                                    <?php _e('Update', 'iii-dictionary') ?>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 col-md-6 col-xs-12 mt-top-4">
                                            <div class="form-group">
                                                <button id="cancel-update-teacher" class="btn-dark-blue border-btn cancel-update-teacher" data-id="sub-update-info" data-tab="updateinfo" style="background: #CECECE;" type="button" name="cancel">
                                                    <?php _e('Cancel', 'iii-dictionary') ?>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" value="" id="chk-tutor-teacher">
                                    <input type="hidden" value="" id="chk-user-gender">
                                </form>
                            </div>
                            <!-- Update My Account -->

                            <!-- Subscription & Points -->
                            <div id="subscription" class="tab-pane fade">
                                <div class="student-center">
                                    <p class="my-account">My Account</p>
                                    <p class="tutor-acc">Subscription & Points</p>
                                </div>
                                <div class="purchase-history">
                                    <h3>Purchase Points History</h3>
                                    <div>
                                        <ul class="nav nav-tabs point-history">
                                            <li>Type</li>
                                            <li>Points</li>
                                            <li>Price</li>
                                            <li>Payment Method</li>
                                            <li>Purchased On</li>
                                        </ul>
                                        <ul class="history-points">
                                            <?php 
                                            $history_point = get_user_meta($current_user->ID, 'history_point', true);
                                                if($history_point == ''){
                                                    echo '<span style="font-size:15px; color: #8a8a8a;"><i>No purchase history</i></span>';
                                                }else{
                                                    foreach($history_point as $value){ ?>
                                                        <ul class="nav nav-tabs list-history">
                                                            <li style="width: 125px;">Points</li>
                                                            <li style="width: 135px;"><?php echo $value[0]; ?></li>
                                                            <li style="width: 127px;">$<?php echo $value[1]; ?></li>
                                                            <li style="width: 207px;"><?php echo $value[2]; ?></li>
                                                            <li><?php echo $value[3]; ?></li>
                                                        </ul>

                                                   <?php };
                                                };
                                            ?>
                                        </ul>
                                    </div>
                                </div>
                                <div class="subscription">
                                    <h3>Subscription Status & Purchase History</h3>
                                    <div id="tab-subs-purchase" class="tab-style">
                                        <ul class="nav nav-tabs">
                                            <li  class="active tab-subs-purchase" id="subscription-purchase"><a data-toggle="tab" href="#tab-cart">Subscription to purchase</a></li>
                                            <li class="tab-subs-purchase" id="subscription-status"><a data-toggle="tab" href="#tab-subs">Subscription Status</a></li>
                                            <li class="tab-subs-purchase" id="purchase-history"><a data-toggle="tab" href="#tab-purchase">Purchase History</a></li>
                                        </ul>
                                        <?php
                                            $current_user_id = get_current_user_id();
                                            $current_page2 = max(1, get_query_var('page'));
                                            $filter2 = get_page_filter_session();
                                            $cart_items = get_cart_items();
                                            $cart_amount = is_null(get_cart_amount()) ? 0 : get_cart_amount();
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
                                            <div id="tab-cart" class="tab-pane fade in active">
                                                <div style="max-height: 450px; overflow-y: auto; overflow-x:hidden;">
                                                    <table class="table table-condensed table-subscription" id="user-subscriptions">
                                                        <thead>
                                                            <tr>
                                                                <th><?php _e('Type', 'iii-dictionary') ?></th>
                                                                <th><?php _e('Months', 'iii-dictionary') ?></th>
                                                                <th><?php _e('Size of Class', 'iii-dictionary') ?></th>
                                                                <th><?php _e('No. of License', 'iii-dictionary') ?></th>
                                                                <th class="hidden-xs"><?php _e('Dictionary', 'iii-dictionary') ?></th>
                                                                <th><?php _e('No. of Points', 'iii-dictionary') ?></th>
                                                                <th><?php _e('Price', 'iii-dictionary') ?></th>
                                                                <th></th>
                                                            </tr>
                                                        </thead>

                                                        <tfoot>
                                                            
                                                        </tfoot>

                                                        <tbody>
                                                           <?php
//                                    var_dump($cart_items);
                                                                $arr_id = [];
                                                                if (!empty($cart_items)) :
                                                                    
                                                                    foreach ($cart_items as $key => $item) :
                            //                                            var_dump($item);die;
                                                                        ?>
                                                                        <tr id="item-subs<?php echo $item->id ?>">  
                                                                            <td><?php
                                                                                echo $item->type;
                                                                                echo $item->extending ? ' ' . __('(Additional)', 'iii-dictionary') : ''
                                                                                ?>
                                                                            </td>
                                                                            <td><?php echo $item->no_months;
                                                                            ?></td>
                                                                            <?php
                                                                                array_push($arr_id,$item->typeid);
                                                                            ?>
                                                                            <td><?php echo in_array($item->typeid, array(SUB_TEACHER_TOOL, SUB_TEACHER_TOOL_MATH, SUB_GROUP)) ? $item->no_students : 'N/A' ?></td>
                                                                            <td><?php echo in_array($item->typeid, array(SUB_DICTIONARY, SUB_SELF_STUDY, SUB_SELF_STUDY_MATH, SUB_GROUP)) ? $item->no_students : '1' ?></td>
                                                                            <td class="hidden-xs"><?php echo empty($item->dictionary) ? 'N/A' : $item->dictionary ?></td>
                                                                            <td><?php echo empty($item->no_of_points) ? 'N/A' : $item->no_of_points ?></td>
                                                                            <td> <?php echo $item->price ?> Points</td>
                                                                            <td><button type="submit" name="delete-cart-item" value="<?php echo $item->id ?>" class="btn-custom-2 delete-item" style="margin: 0"><?php _e('Delete', 'iii-dictionary') ?></button></td>
                                                                        </tr>
                                                                        <?php
                                                                    endforeach;
                                                                      
                                                                        
                                                                 else :
                                                                    ?>
                                                            <tr><td colspan="9"><?php _e('Your cart is empty', 'iii-dictionary') ?></td></tr>
                                                            <?php endif ?>
                                                            <tr>
                                                            
                                                        </tbody>
                                                    </table>
                                                    <div style="float: right;height: 0px; padding-right: 10px;" >
                                                        <span>Total Amount: </span>
                                                        <span class="sum-prices" id="sum-pri" value="<?php echo $cart_amount ?>"><?php echo $cart_amount ?> Points</span>
                                                    </div>
                                                    <div class="sum-prices" style="padding:40px 0 10px 0;">
                                                        <span>Total Points: </span>
                                                        <span><?php echo ik_get_user_points(); ?></span>
                                                    </div>
                                                    <div id="accept-point" style="color: red; display: none">
                                                        <i> ● You don't have enough points to pay for the courses, <span class="click-add">click here to add more points to your account</span></i>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-xs-12 col-sm-6">
                                                            <button id="check-cart" class="btn-dark-blue border-btn" type="button" style="background: #009dcb;">Check out</button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div style="clear: both;"></div>
                                            </div>
                                            <div id="tab-subs" class="tab-pane fade">
                                                <div style="max-height: 450px; overflow-y: auto; overflow-x:hidden;">
                                                    <table class="table table-condensed table-subscription" id="user-subscriptions">
                                                        <thead>
                                                            <tr>
                                                                <th>
                                                                    <?php _e('Type', 'iii-dictionary') ?><span class="sorting-indicator"></span>
                                                                </th>
                                                                <th>
                                                                    <?php _e('Students', 'iii-dictionary') ?>
                                                                </th>
                                                                <th>
                                                                    <?php _e('Dictionary', 'iii-dictionary') ?>
                                                                </th>     
                                                                <th>
                                                                    <?php _e('Sub. End', 'iii-dictionary') ?> <span class="sorting-indicator"></span>
                                                                </th>
                                                                <th>
                                                                    <?php _e('Class (group)', 'iii-dictionary') ?>
                                                                </th>
                                                                <th></th>
                                                            </tr>
                                                        </thead>

                                                        <tfoot>
                                                            <tr>
                                                                <td colspan="8">
                                                                    <?php echo $pagination2 ?>
                                                                </td>
                                                            </tr>
                                                        </tfoot>

                                                        <tbody>
                                                            <?php 
                                                            if (empty($user_subscriptions->items)) : 
                                                            ?>
                                                            <tr>
                                                                <td colspan="6">
                                                                    <?php _e('You haven\'t subscribed yet.', 'iii-dictionary') ?>
                                                                </td>
                                                            </tr>
                                                            <?php 
                                                            else : 
                                                                foreach ($user_subscriptions->items as $code) :
                                                                $date_a = date("Y-m-d");
                                                                if (ik_date_format($code->expired_on) < $date_a) {
                                                            ?>
                                                            <tr> 
                                                                <td class="note" style="width: 30%;">
                                                                    <?php 
                                                                    if (!$code->inherit){
                                                                        echo $code->type;
                                                                        echo in_array($code->typeid, array(SUB_SAT_PREPARATION, SUB_MATH_SAT_I_PREP, SUB_MATH_SAT_II_PREP)) ? ' - ' . $code->sat_class : ''; 
                                                                        ?>
                                                                       <!--  <div class="detail-note"> -->
                                                                            <!-- <?php 
                                                                            echo $code->type;
                                                                            echo in_array($code->typeid, array(SUB_SAT_PREPARATION, SUB_MATH_SAT_I_PREP, SUB_MATH_SAT_II_PREP)) ? ' - ' . $code->sat_class : ''; 
                                                                            ?> -->
                                                                        <!-- </div> -->
                                                                    <?php 
                                                                    }else{
                                                                        echo $code->type 
                                                                    ?>
                                                                        <div class="detail-note">
                                                                            <?php echo $code->type ?>
                                                                        </div>
                                                                    <?php
                                                                    }
                                                                    ?>
                                                                </td>
                                                                <td style="width: 10%;">
                                                                    <?php 
                                                                    echo in_array($code->typeid, array(SUB_TEACHER_TOOL, SUB_TEACHER_TOOL_MATH, SUB_GROUP)) ? $code->number_of_students : 'N/A'; 
                                                                    ?>
                                                                </td>
                                                                <td style="width: 10%;">
                                                                    <?php echo $code->dictionary ?>
                                                                </td>
                                                                <?php if(strtotime($date_a) > strtotime($code->expired_on)){
                                                                    echo '<td style="width: 10%; color: #f00000 !important;">';
                                                                }else{
                                                                echo '<td style="width: 10%; color:#08a500 !important;">';
                                                                }?>
                                                                    <?php echo ik_date_format($code->expired_on) ?>
                                                                </td>
                                                                <td style="width: 30%;" class="note">
                                                                    <?php 
                                                                    echo is_null($code->group_name) ? 'N/A' : $code->group_name; 
                                                                    ?>
                                                                    <!-- <div class="detail-note">
                                                                        <?php 
                                                                        echo is_null($code->group_name) ? 'N/A' : $code->group_name; 
                                                                        ?>
                                                                    </div> -->
                                                                </td>
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
                                                                ?>
                                                                <td style="width: 10%;" data-subid="<?php echo $code->id ?>" data-type="<?php echo $code->typeid ?>" data-did="<?php echo $code->dictionary_id ?>" data-size="<?php echo $code->number_of_students ?>" data-months="<?php echo $months_left ?>" <?php echo!is_null($code->group_name) ? ' data-group="' . $code->group_name . '"' : '' ?> data-sat-class=" <?php echo $code->sat_class ?>" data-sat-class-id="<?php echo $code->sat_class_id ?>" data-gid="<?php echo $code->group_id ?>">

                                                                    <?php if (!$code->inherit){ ?>
                                                                    <a data-toggle="tab" href="#"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Detail_Grey_1.png" class="" alt="setting my account" style="width: 23px; margin-right: 5px;"></a>
                                                                    <a data-toggle="tab" href="#"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Subscription.png" class="" alt="setting my account" style="width: 23px;"></a>
                                                                    <?php } ?>
                                                                </td>
                                                            </tr>
                                                            <?php }else{ ?>
                                                            <tr>
                                                                <td>
                                                                    <?php 
                                                                    if (!$code->inherit){
                                                                        echo $code->type;
                                                                        echo in_array($code->typeid, array(SUB_SAT_PREPARATION, SUB_MATH_SAT_I_PREP, SUB_MATH_SAT_II_PREP)) ? ' - ' . $code->sat_class : '';
                                                                    }else{ 
                                                                        echo $code->type; 
                                                                    }
                                                                    ?>
                                                                </td>
                                                                <td>
                                                                    <?php 
                                                                    echo in_array($code->typeid, array(SUB_TEACHER_TOOL, SUB_TEACHER_TOOL_MATH, SUB_GROUP)) ? $code->number_of_students : 'N/A';
                                                                    ?>
                                                                </td>
                                                                <td>
                                                                    <?php echo $code->dictionary ?>
                                                                </td>
                                                                <td>
                                                                    <?php echo ik_date_format($code->expired_on) ?>
                                                                </td>
                                                                <td>
                                                                    <?php 
                                                                    echo is_null($code->group_name) ? 'N/A' : $code->group_name; 
                                                                    ?>
                                                                </td>

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
                                                                ?>
                                                                <td data-subid="<?php echo $code->id ?>" data-type="<?php echo $code->typeid ?>" data-did="<?php echo $code->dictionary_id ?>" data-size="<?php echo $code->number_of_students ?>" data-months="<?php echo $months_left ?>" <?php echo!is_null($code->group_name) ? ' data-group="' . $code->group_name . '"' : '' ?> data-sat-class="<?php echo $code->sat_class ?>" data-sat-class-id="<?php echo $code->sat_class_id ?>" data-gid="<?php echo $code->group_id ?>">

                                                                    <?php 
                                                                    if (!$code->inherit){
                                                                        if (!in_array($code->typeid, array(SUB_GROUP))){  
                                                                    ?>
                                                                    <a data-toggle="tab" href="#"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Detail_Grey_1.png" class="" alt="setting my account" style="width: 23px; margin-right: 5px;"></a>
                                                                                                                
                                                                    <a data-toggle="tab" href="#"><img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Subscription.png" class="" alt="setting my account" style="width: 23px;"></a>
                                                                    <?php 
                                                                       }
                                                                    }
                                                                    ?>
                                                                </td>
                                                            </tr>
                                                            <?php
                                                                    }
                                                                endforeach;
                                                            endif; 
                                                            ?>
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
                                                <div style="max-height: 450px; overflow: auto;">
                                                    <table class="table table-condensed table-subscription">
                                                        <thead>
                                                            <tr>
                                                                <th>
                                                                    <?php _e('Purchase Item', 'iii-dictionary') ?>
                                                                </th>
                                                                <th>
                                                                    <?php _e('Activation Code', 'iii-dictionary') ?>
                                                                </th>
                                                                <th>
                                                                    <?php _e('Payment Method', 'iii-dictionary') ?>
                                                                </th>
                                                                <th>
                                                                    <?php _e('Paid Amount', 'iii-dictionary') ?>
                                                                </th>
                                                                <th>
                                                                    <?php _e('Purchased On', 'iii-dictionary') ?>
                                                                </th>
                                                            </tr>
                                                        </thead>
                                                        <?php
                                                        $purchased_history = MWDB::get_user_purchase_history($current_user_id);
                                                        ?>
                                                        <tbody>
                                                        <?php if (empty($purchased_history)) : ?>
                                                            <tr>
                                                                <td colspan="5">
                                                                    <?php _e('No history', 'iii-dictionary') ?>
                                                                </td>
                                                            </tr>
                                                        <?php
                                                        else :
                                                            foreach ($purchased_history as $item) :
                                                        ?>
                                                            <tr>
                                                                <td>
                                                                    <?php echo $item->purchased_item_name ?>
                                                                </td>
                                                                <td>
                                                                    <?php echo!empty($item->encoded_code) ? $item->encoded_code : 'NULL'; ?>
                                                                </td>
                                                                <td>
                                                                    <?php echo $item->payment_method ?>
                                                                </td>
                                                                <td>$
                                                                    <?php echo $item->amount ?>
                                                                </td>
                                                                <td>
                                                                    <?php echo ik_date_format($item->purchased_on, 'm/d/Y H:m:i') ?>
                                                                </td>
                                                            </tr>
                                                        <?php
                                                            endforeach;
                                                        endif
                                                        ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                            <form method="post" action="">
                                                <input type="hidden" name="dictionary-id" id="dictionary-id" value="">
                                                <input type="hidden" name="starting-date" id="starting-date-txt" value="">
                                                <input type="hidden" name="assoc-group" id="assoc-group" value="">
                                                <input type="hidden" name="group-name" id="group-name" value="">
                                                <input type="hidden" name="group-pass" id="group-pass" value="">
                                                <input type="hidden" id="activation-code" name="activation-code" value="">
                                            </form>
                                            <div class="activation" style="padding-top: 10px;">
                                                <h3>Activation Code</h3>
                                                <div class="form-group col-md-6" style="padding-left: 0px !important;">
                                                    <label for="credit-code">Enter a Credit Code <span style="font-style: italic;">(if you have any)</span></label>
                                                    <input class="form-control border-ras" id="credit-code" name="credit-code">
                                                </div>
                                                <div class="form-group col-md-6" style="padding-right: 0px !important;">
                                                    <button class="btn-dark-blue border-btn" id="val-credit-code" style="background: #f7b555; margin-top: 28px;margin-bottom: 50px;" type="button" data-loading-text="<?php _e('Checking...', 'iii-dictionary') ?>" data-error-text="<?php _e('Please enter a credit code', 'iii-dictionary') ?>">
                                                        <?php _e('Apply', 'iii-dictionary') ?>
                                                    </button>
                                                    <span data-toggle="popover" data-placement="bottom" data-container="body" data-html="true" data-max-width="420px"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Subscription & Points -->

                            <div id="purchase-points" class="tab-pane fade">
                                <div class="student-center" style="margin-left: 0">
                                    <div class="row">
                                        <div class="col-sm-6 col-md-6 col-xs-6">
                                            <p class="mt-bottom-12 student-center-title">Student Information Center</p>
                                            <div class="new-request-list">PURCHASE POINTS</div>
                                        </div>
                                        
                                    </div>
                                </div>
                                <div class="purchase-points">
                                    <div class="number-point">
                                        <p class="mt-top-14 title-payment">Number of Points</p>
                                        <div class="row">
                                            <div class="col-sm-6 col-md-6- col-xs-6">
                                                <div class="find-general-border">
                                                    <span class="find-label">Points</span>
                                                    <div class="form-group">
                                                        <input id="point-input" class="form-control" type="text" name="point-input" value="" autocomplete="off">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 col-md-6- col-xs-6 ">
                                                <div class="find-general-border" style="background: #f4f7f7;">
                                                    <span class="find-label">Total Amount</span>
                                                    <div class="form-group">
                                                        <input id="total-amount" class="form-control" type="text" name="point-input" value="" readonly>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 col-md-6- col-xs-6  mt-bottom-14">
                                                <button id="confirm-point" style="background: #009dcb;" class="border-btn btn-dark-blue" type="button">CONFIRM</button>
                                            </div>
                                            <div class="col-sm-6 col-md-6- col-xs-6  mt-bottom-14">
                                                <button id="confirm-point" class="border-btn btn-dark-blue" style="background: #CECECE;" onclick="document.getElementById('total-amount').value=null;document.getElementById('point-input').value=null;">RESET</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="checkout-point" style="padding-left: 33px;">
                                        <p class="mt-top-14 mt-bottom-5 title-payment">Checkout</p>
                                        <div class="row" style="padding-left:8px;">
                                            <div class="col-sm-12 mt-bottom-5 table-payment">
                                                <div class="col-sm-3">
                                                    <span>Type</span>
                                                </div>
                                                <div class="col-sm-9">
                                                    <span>Price</span>
                                                </div>
                                            </div>
                                            <div id="list-payment" class="col-sm-12  table-payment-list">
                                                <div class="col-sm-3">
                                                    <span id="type-payment">No Item Seclect</span>
                                                </div>
                                                <div class="col-sm-9">
                                                    <div class="row">
                                                        <div class="col-sm-10" id="price-payment">$0.00</div>
                                                        <div class="col-sm-2"><span id="remove-payment">Remove<span><img src="<?php echo get_template_directory_uri(); ?>/library/images/01_icon_trash.png" style="height: 22px;margin-top: -3px; float: right;"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-12 sum-price">
                                                <span id="sum-price" class="sum-prices">0.00</span><span class="sum-prices">&nbsp;$</span><span>Total Amount:&nbsp;&nbsp;</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="payment-method mt-bottom-14" style="padding-left: 33px;">
                                        <p class="mt-top-14  title-payment">Payment Method</p>
                                        <div class="mt-top-10 mt-bottom-8" >
                                            <input type="checkbox" name="payment_paypal" id="payment_paypal" class="radio_buttons option-input-3 radio">
                                            <span>&nbsp;&nbsp;Pay with PayPal</span>
                                            
                                        </div>
                                        <div id="paypal-box" class="row mt-top-10" style="display: none;">
                                                <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
                                                    <button type="image" id="paypal-submit" border="0">Pay with Paypal</button>
                                                </div>
                                                <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
                                                    <p style="font-size: 13px; color: #d29500;">
                                                        <strong>Note:</strong> Paypal might take sometimes to process your payment. If you don't see the item you paid in Subscription history, please log out and log in again after a few minutes</p>
                                                </div>
                                                <div id="paypal-button-container"></div>
                                                <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top" class="hidden">
                                                    <input type="hidden" name="cmd" value="_xclick">
                                                    <input type="hidden" name="business" value="payment@innovative-knowledge.com">
                                                    <input type="hidden" name="item_name" value="Subscription">
                                                    <input id="paypal-price" type="hidden" name="amount" value="">
                                                    <input id="item_point" type="hidden" name="item_point" value="">
                                                    <input id="item-code" type="hidden password" name="item_code" value="">
                                                    <input type="hidden" name="custom" value="<?php echo get_current_user_id() ?>">
                                                    <input id="return-paypal" type="hidden" name="return" value="">
                                                    <input type="image" id="paypal-btn" src="https://www.paypalobjects.com/en_US/i/btn/btn_buynowCC_LG.gif" border="0" name="submit">
                                                    <img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
                                                </form>
                                            </div>
                                        
                                        <div style="height: 50px; border-bottom:  1px solid #d9d9d9; border-top:  1px solid #d9d9d9; padding-top: 7px">
                                            <input type="checkbox" name="payment_paypal" class="radio_buttons option-input-3 radio" disabled>
                                            <span>&nbsp;&nbsp;Use existing credit cards?</span>
                                        </div>
                                        <div class="mt-bottom-14">
                                            <input type="checkbox" name="payment_paypal" class="radio_buttons option-input-3 radio" disabled>
                                            <span>&nbsp;&nbsp;Use new credit card?</span>
                                        </div>
                                        <button id="purchase-now" style="background: #cecece; width: 100%; margin-top: 10px;" class="border-btn btn-dark-blue" type="button">PURCHASE NOW</button>
                                    </div>

                                </div>
                            </div>

                            <div id="tutoring-main" class="tab-pane fade">
                                <div class="student-center">
                                    <div class="row">
                                        <div class="col-sm-6 col-md-6 col-xs-6">
                                            <p class="mt-bottom-12 student-center-title">Student Information Center</p>
                                            <div class="new-request-list">SCHEDULE</div>
                                        </div>
                                        <div class="col-sm-6 col-md-6 col-xs-6 text-right">
                                            <button type="button" id="btn-getting">
                                                Getting Tutoring
                                            </button>

                                            <button type="button" id="btn-tutor">
                                                Find Tutor
                                            </button>
                                            <button type="button" id="btn-schedule">
                                                Schedule
                                            </button>
                                        </div>
                                    </div>
                                </div>



                                <div class="tutoring-main">
                                    <div id="tab-sub-tutoring" class="tab-style2">
                                        <div class="tab-content">
                                            <div id="tab-tutor-content" class="tab-pane fade in active">
                                                <input value="" type="hidden" id="active-day-tutor">
                                                <input value="" type="hidden" id="today-tutor">
                                                <div class="form-group border-ras select-style" id="custom-timezone" data-type="" data-day="">
                                                    <?php
                                                    $timezone_index = '';
                                                    if ($is_user_logged_in) {
                                                    $timezone_index = get_user_meta($current_user->ID, 'time_zone_index', true);
                                                    }
                                                    ?>
                                                    <span class="placeholder-timezone">                 
                                                        Time Zone:
                                                    </span>
                                                    <span id="time-clock" data-hour="24" data-minute="0">2:35 PM</span>
                                                    <select class="select-box-it form-control" name="time_zone" id="select-timezone">
                                                        <option disabled="" value="0" data-value="" data-city="London" <?php if($timezone_index == '0' ) echo 'selected="selected"'; ?>>Select Time Zone</option>
                                                        <option value="1" data-value="-5" data-name="America/New_York" data-city="New York" <?php if($timezone_index == '1' ) echo 'selected="selected"'; ?>>New York</option>
                                                        <option value="2" data-value="-6" data-name="America/Chicago" data-city="Minneapolis" <?php if($timezone_index == '2' ) echo 'selected="selected"'; ?>>Minneapolis</option>
                                                        <option value="3" data-value="-5" data-name="America/Denver" data-city="Colorado" <?php if($timezone_index == '3' ) echo 'selected="selected"'; ?>>Colorado</option>
                                                        <option value="4" data-value="-7" data-name="America/Los_Angeles" data-city="SF/LA" <?php if($timezone_index == '4' ) echo 'selected="selected"'; ?>>SF/LA</option>
                                                        <option value="5" data-value="-10" data-name="Pacific/Honolulu" data-city="Hawaii" <?php if($timezone_index == '5' ) echo 'selected="selected"'; ?>>Hawaii</option>
                                                        <option value="6" data-value="+10" data-name="Pacific/Guam" data-city="Guam" <?php if($timezone_index == '6' ) echo 'selected="selected"'; ?>>Guam</option>
                                                        <option value="7" data-value="+9" data-name="Asia/Tokyo" data-city="Tokyo" <?php if($timezone_index == '7' ) echo 'selected="selected"'; ?>>Tokyo</option>
                                                        <option value="8" data-value="+9" data-name="Asia/Seoul" data-city="Seoul" <?php if($timezone_index == '8' ) echo 'selected="selected"'; ?>>Seoul</option>
                                                        <option value="9" data-value="+8" data-name="Asia/Shanghai" data-city="Beijing" <?php if($timezone_index == '9' ) echo 'selected="selected"'; ?>>Beijing</option>
                                                        <option value="10" data-value="+8" data-name="Asia/Shanghai" data-city="Xianyang" <?php if($timezone_index == '10' ) echo 'selected="selected"'; ?>>Xianyang</option>
                                                        <option value="11" data-value="+7" data-name="Asia/Ho_Chi_Minh" data-city="Hanoi" <?php if($timezone_index == '11' ) echo 'selected="selected"'; ?>>Hanoi</option>
                                                        <option value="12" data-value="+7" data-name="Asia/Bangkok" data-city="Bangkok" <?php if($timezone_index == '12' ) echo 'selected="selected"'; ?>>Bangkok</option>
                                                        <option value="13" data-value="+7" data-name="Asia/Rangoon" data-city="Myanmar" <?php if($timezone_index == '13' ) echo 'selected="selected"'; ?>>Myanmar</option>
                                                        <option value="14" data-value="+6" data-name="Asia/Dhaka" data-city="Bangladesh" <?php if($timezone_index == '14' ) echo 'selected="selected"'; ?>>Bangladesh</option>
                                                        <option value="15" data-value="+5" data-name="Asia/Colombo" data-city="Sri Lanka" <?php if($timezone_index == '15' ) echo 'selected="selected"'; ?>>Sri Lanka</option>
                                                        <option value="16" data-value="+5" data-name="Asia/Kolkata" data-city="New Delhi" <?php if($timezone_index == '16' ) echo 'selected="selected"'; ?>>New Delhi</option>
                                                        <option value="17" data-value="+5" data-name="Asia/Kolkata" data-city="Mumbai" <?php if($timezone_index == '17' ) echo 'selected="selected"'; ?>>Mumbai</option>
                                                        <option value="18" data-value="0" data-name="Europe/London" data-city="London" <?php if($timezone_index == '18' ) echo 'selected="selected"'; ?>>London</option>
                                                        <option value="19" data-value="+5" data-name="Australia/Sydney" data-city="Sydney" <?php if($timezone_index == '19' ) echo 'selected="selected"'; ?>>Sydney</option>
                                                    </select>
                                                </div>
                                                <div class="getting-tutor-main" style="display: none; margin-left: -34px;">
                                                    <div class="step-box">
                                                        
                                                        <img style="width: 371px" src="<?php echo get_template_directory_uri(); ?>/library/images/05_Title_Image.jpg">
                                                        <div style=" float: right; width: 400px;">
                                                            <br>
                                                            <span style="font-family: Myriad_light; color: #343434;  font-size: 40px; line-height: 1.3;">Welcome to iktutor.com! Let’s get you started with the tutoring.</span>
                                                            <br><br>
                                                            <span style="font-family: Myriad_light; color: #8a8a8a;font-size: 17px; ">These 4 Steps of Quick Start Guide will help you through how to get into Online Tutoring with the basics that you need to know.</span>
                                                        </div>
                                                    </div>
                                                    <div class="step-box" >
                                                        <img class="step-below" src="<?php echo get_template_directory_uri(); ?>/library/images/13_Step_BelowArrow.png">
                                                        <div class="step-btn" style=" background: #58aec8;">STEP 1</div>
                                                        <br>
                                                        <img class="getting-img" style="width: 305px" src="<?php echo get_template_directory_uri(); ?>/library/images/06_Step1.jpg">
                                                        <div>
                                                            <p class="tit-getting">Do you have enough points for <br>tutoring? If not, here’s what <br>you can do!</p>
                                                            <ul class="list-getting">
                                                                <img src="<?php echo get_template_directory_uri(); ?>/library/images/03_Icon_Sub-menu.png"><li>You will spend points to schedule a Tutoring.</li>
                                                                <img src="<?php echo get_template_directory_uri(); ?>/library/images/03_Icon_Sub-menu.png"><li>1 point = $1 dollar. Simple as that!</li>
                                                                <img src="<?php echo get_template_directory_uri(); ?>/library/images/03_Icon_Sub-menu.png"><li>Anytime you are short on points, simply “Recharge” them.</li>
                                                                <img src="<?php echo get_template_directory_uri(); ?>/library/images/03_Icon_Sub-menu.png"><li>That’s all, now you are ready to begin!</li>
                                                            </ul>
                                                        </div>
                                                        <a id="go-to-point" href="<?php echo locale_home_url() ?>/?r=my-account#purchase-points"><div class="link-getting" style="color: #49a1bc;">Go to Purchase Points &nbsp;<img src="<?php echo get_template_directory_uri(); ?>/library/images/01_Icon_Step1.png"></div></a>
                                                    </div>
                                                    <div class="step-box">
                                                        <img class="step-below" src="<?php echo get_template_directory_uri(); ?>/library/images/13_Step_BelowArrow.png">
                                                         <div class="step-btn"  style=" background: #ffad42;">STEP 2</div>
                                                        <br>
                                                        <img class="getting-img" style="width: 260px" src="<?php echo get_template_directory_uri(); ?>/library/images/07_Step2.jpg">
                                                        <div>
                                                            <p class="tit-getting">What and when do you want your schedule to be?</p>
                                                            <ul class="list-getting">
                                                                <img src="<?php echo get_template_directory_uri(); ?>/library/images/03_Icon_Sub-menu.png"><li>There are two ways to make a schedule. Through “<u>Find Tutor Page</u>” or “<u>Schedule Page</u>”</li>
                                                                <img src="<?php echo get_template_directory_uri(); ?>/library/images/03_Icon_Sub-menu.png"><li>Find Tutor Page: To set a schedule, a student defines “<u>Subject</u>”, “<u>Date</u>”, and “<u>Other Parameters</u>” from the search area. </li>
                                                                <img src="<?php echo get_template_directory_uri(); ?>/library/images/03_Icon_Sub-menu.png"><li>Schedule Page: From the calendar, select the “<u>Date</u>”, then it will take you to the “Fnd Tutor Page”. From there, finish the remaining parameters</li>
                                                                <img src="<?php echo get_template_directory_uri(); ?>/library/images/03_Icon_Sub-menu.png"><li>Now you are almost ready to meet your tutor! </li>
                                                            </ul>

                                                            <div class="go-to-find link-getting" style="color: #ffad42;">Go to Find Tutor Page &nbsp;<img src="<?php echo get_template_directory_uri(); ?>/library/images/02_Icon_Step2.png"></div>
                                                            <div id="go-to-schedule" class="link-getting" style="color: #ffad42;">Go to Schedule Page &nbsp;<img src="<?php echo get_template_directory_uri(); ?>/library/images/02_Icon_Step2.png"></div>
                                                        </div>
                                                    </div>
                                                    <div class="step-box">
                                                        <img class="step-below" src="<?php echo get_template_directory_uri(); ?>/library/images/13_Step_BelowArrow.png">
                                                        <div class="step-btn"  style=" background: #ff6d6d;">STEP 3</div>
                                                        <br>
                                                        <img class="getting-img" style="width: 250px" src="<?php echo get_template_directory_uri(); ?>/library/images/08_Step3.jpg">
                                                        <p class="tit-getting">It’s time to select the right tutor!</p>
                                                        <ul class="list-getting">
                                                            <img src="<?php echo get_template_directory_uri(); ?>/library/images/03_Icon_Sub-menu.png"><li>Once Preference is set, click on “<u>Search</u>”. Now you will be presented with “<u>Lists of Tutors</u>”.</li>
                                                            <img src="<?php echo get_template_directory_uri(); ?>/library/images/03_Icon_Sub-menu.png"><li>Alternatively, go to “<u>Available Tutor List</u>”, if you don’t have any preference.</li>
                                                            <img src="<?php echo get_template_directory_uri(); ?>/library/images/03_Icon_Sub-menu.png"><li>Before finalizing a tutor, you can check their details like, “<u>Marketing Words</u>”, “<u>Background</u>”, “<u>Reviews</u>”, and “<u>Scheduling Status</u>”. These will help you to decide the right tutor for your schedule.</li>
                                                            <img src="<?php echo get_template_directory_uri(); ?>/library/images/03_Icon_Sub-menu.png"><li>When everything looks good, make a schedule by clicking on the “<u>Schedule Button</u>”.</li>

                                                        </ul>
                                                        <div class="link-getting go-to-find-tutor" style="color: #ff6d6d;">Go to Available Tutor List &nbsp;<img src="<?php echo get_template_directory_uri(); ?>/library/images/03_Icon_Step3.png"></div>
                                                        <div class="go-to-find link-getting" style="color: #ff6d6d;">Go to Find Tutor Page &nbsp;<img src="<?php echo get_template_directory_uri(); ?>/library/images/03_Icon_Step3.png"></div>
                                                    </div>
                                                    <div class="step-box" style="border: 0">
                                                        <img class="step-below" src="<?php echo get_template_directory_uri(); ?>/library/images/13_Step_BelowArrow.png">
                                                        <div class="step-btn"  style=" background: #65d02a;">STEP 4</div>
                                                        <br>
                                                        <img class="getting-img" style="width: 309px" src="<?php echo get_template_directory_uri(); ?>/library/images/09_Step4.jpg">
                                                        <p class="tit-getting">What to expect after everything is set.</p>
                                                        <ul class="list-getting">
                                                            <img src="<?php echo get_template_directory_uri(); ?>/library/images/03_Icon_Sub-menu.png"><li>At this point, students need to wait for the tutor to “<u>Confirm</u>” the schedule.</li>
                                                            <img src="<?php echo get_template_directory_uri(); ?>/library/images/03_Icon_Sub-menu.png"><li>When confirmed, both tutor and student meet up at the “<u>Online Tutoring Notepad</u>” from the appointed time. </li>
                                                            <img src="<?php echo get_template_directory_uri(); ?>/library/images/03_Icon_Sub-menu.png"><li>For whatever reason, if the tutor hasn’t confirmed yet, students have the option to “<u>Cancel</u>” the schedule. </li>
                                                            <img src="<?php echo get_template_directory_uri(); ?>/library/images/03_Icon_Sub-menu.png"><li>All the schedule status can be checked from the student's “<u>Schedule Detailed Page</u>”. </li>
                                                            <img src="<?php echo get_template_directory_uri(); ?>/library/images/03_Icon_Sub-menu.png"><li>That’s it! Enjoy your online tutoring! </li>

                                                        </ul>
                                                        <div id=go-to-all class="link-getting" style="color: #65d02a;">Go to Schedule Detail Page &nbsp;<img src="<?php echo get_template_directory_uri(); ?>/library/images/04_Icon_Step4.png"></div>
                                                        <a href="https://notepad.iktutor.com/" target="_bank"><div class="link-getting" style="color: #65d02a;">Go to Online Tutoring Notepad &nbsp;<img src="<?php echo get_template_directory_uri(); ?>/library/images/04_Icon_Step4.png"></div></a>
                                                    </div>

                                                </div>
                                                <div class="section-tutor-main">
                                                    <div class="border-selectall color-border">
                                                        <button type="button" class="btn-sub-tab active" name="available_now" id="btn-available-now">
                                                            <img src="<?php echo get_template_directory_uri(); ?>/library/images/04_Available_Now_Selected.png" alt="">Available Now
                                                        </button>
                                                        <button type="button" class="btn-sub-tab" name="find_tutoring" id="btn-find-tutoring">
                                                            <img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Find_off.png" alt="">Find
                                                        </button>
                                                        <button type="button" class="btn-sub-tab" name="list_favorite" id="btn-list-favorite">
                                                            <img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_L_Favorite.png" alt="">Favorites
                                                        </button>
                                                        <button type="button" class="btn-sub-tab" name="list_review" id="btn-list-review">
                                                            <img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_L_Review.png" alt="">Review
                                                        </button>
                                                        <button type="button" class="btn-sub-tab" name="list_tutoring" id="btn-list-tutoring">
                                                            <img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_L_list.png" alt="">All
                                                        </button>
                                                    </div>

                                                    <div class="color-border toggle-btn" style="display: none;">
                                                        <input type="checkbox" name="cb_show_available" id="cb-show-available" class="hidden check-toggle">
                                                        <img  style="height: 18px; display: inline-block;" src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Toggle_Switch_OFF.png" alt="Toggle_on" class="inactive-img" onclick="document.getElementById('cb-show-available').click();">
                                                        <div class="lable-toggle inactive">Show Only Available Tutors</div>
                                                    </div>

                                                    <div class="frm-available-now">
                                                        <div class="row">
                                                            <div class="col-sm-12 col-md-12">
                                                                <label class="find-page-title">Tutor and Subject</label>
                                                                <img class="icon-about" src="<?php echo get_template_directory_uri(); ?>/library/images/icon_About.png"  alt="">
                                                            </div>
                                                        </div>
                                                        <div class="row">                                                           
                                                            <div class="col-sm-6 col-md-6 col-xs-6">
                                                                <div class="find-general-border">
                                                                <label class="find-label">Subject for tutoring</label>
                                                                <div class="form-group border-ras select-style">
                                                                    <select class="find-select select-box-it  form-control" name="available_subject" id="select-available-subject">
                                                                        <option class="boo" value="0" data-name="">Subject</option>
                                                                        <!-- <option value="all" data-name="Any Subjects">Any Subjects</option> -->
                                                                        <option value="english_subject|all" data-name="English Only">English Only</option>
                                                                        <option value="math_subject|all" data-name="Math Only">Math Only</option>
                                                                        <option value="science_subject|all" data-name="Science Only">Science Only </option>
                                                                        <option value="other_preference|others" data-name="Other Subjects Only">Other Subjects Only</option>
                                                                        <option value="english_subject|english_conversation" data-name="English: Conversation for Foreign Students">English: Conversation for Foreign Students</option>
                                                                        <option value="english_subject|english_grammar" data-name="Enlgish: Grammar">Enlgish: Grammar</option>
                                                                        <option value="english_subject|english_writting" data-name="English Writting">English Writing</option>
                                                                        <option value="english_subject|english_reading_comprehension" data-name="English: Reading Comprehension">English: Reading Comprehension</option>
                                                                        <option value="english_subject|others" data-name="English: Others">English: Others</option>
                                                                        <option value="math_subject|elemenatary_school_math" data-name="Math: Elementary">Math: Elementary</option>
                                                                        <option value="math_subject|middle_school_math" data-name="Math: Middle School">Math: Middle School</option>
                                                                        <option value="math_subject|high_school_math" data-name="Math: High School">Math: High School</option>
                                                                        <option value="math_subject|advanced_math" data-name="Math: Advanced">Math: Advanced</option>
                                                                        <option value="math_subject|others" data-name="Math: Others">Math: Others</option>
                                                                        <option value="science_subject|science_middle_school" data-name="Science: Elementary/Middle School">Science: Elementary/Middle School</option>
                                                                        <option value="science_subject|physics_high_school" data-name="Science: High School">Science: High School</option>
                                                                        <option value="science_subject|chemistry_high_school" data-name="Science: Chemistry for High School">Science: Chemistry for High School</option>
                                                                        <option value="science_subject|others" data-name="Science: Others">Science: Others</option>
                                                                    </select>
                                                                </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-sm-6 col-md-6 search-tutorname">
                                                            <div class="find-general-border">
                                                                <label class="find-label">Tutor Name:</label>
                                                                <div class="form-group">
                                                                    <input id="search-find-tutoring" name="search-ready-les" class="find-name form-control search-tit " placeholder="Type name here..." val="">
                                                                </div>
                                                                </div>
                                                            </div>
                                                            
                                                        </div>
                                                        <!-- <div class="row">
                                                        <div class="col-sm-3 col-md-3 col-xs-12 cb-type2">
                                                                <label>
                                                                    <input type="checkbox" class="radio_tutor_search class_cb_search option-input-2 radio" value="favorite" data-lang="en" name="type_search" /> Favorites
                                                                </label>
                                                                <label>
                                                                    <input type="checkbox" class="radio_tutor_search class_cb_search option-input-2 radio" value="rating" data-lang="en" name="type_search" /> Rating
                                                                </label>
                                                            </div>
                                                        </div> -->
                                                        <div class ="row">
                                                            <div class="col-sm-4 col-md-4">
                                                            <div class="find-general-border">
                                                                <label class="find-label">Tutoring Type</label>
                                                                <div class=" max-100 form-group select-style">
                                                            <select class="find-select select-box-it  form-control" name="available_subject" id="select-available-type">
                                                                <option class="boo" value="0" data-name="">Tutoring type</option>
                                                                <option value="one_tutoring" data-name="1 on 1 Tutoring">1 On 1 Tutoring</option>
                                                                <option value="group_tutoring" data-name="Group Tutoring">Group Tutoring</option>
                                                                <option value="0" data="">Both</option>
                                                            </select>
                                                            </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-4 col-md-4">
                                                            <div class="find-general-border">
                                                            <label class="find-label">Price</label>
                                                            <div class="max-100 form-group select-style">
                                                            <select class="find-select select-box-it  form-control" name="available_subject" id="select-available-price">
                                                                <option class="boo" value="0" data-name="">Any Price</option>
                                                                <option value="0-11" data-name="$1 - $10 (30 min)">1 - 10 Points (30 min)</option>
                                                                <option value="10-21" data-name="$11 - $20 (30 min)">11 - 20 Points (30 min)</option>
                                                                <option value="20-31" data-name="$21 - $30 (30 min)">21 - 30 Points (30 min)</option>
                                                                <option value="30-41" data-name="$31 - $40 (30 min)">31 - 40 Points (30 min)</option>
                                                                <option value="40-51" data-name="$41 - $50 (30 min)">41 - 50 Points (30 min)</option>
                                                                <option value="50" data-name="> $50 (30 min)">> 50 Points (30 min)</option>

                                                            </select>
                                                            </div>  
                                                            </div>
                                                        </div>
                                                        
                                                    
                                                    <div class="col-sm-4 col-md-4">
                                                        <div class="find-general-border">
                                                            <label class="find-label">Option :</label>
                                                            <div class="max-100 form-group select-style">
                                                            <select class="find-select select-box-it  form-control" name="available_subject" id="select-available-option">
                                                                <option class="boo" value="0" data-name="">None</option>
                                                                <option value="all" data-name="Rating & Favorite">Rating & Favorite</option>
                                                                <option value="rating" data-name="Rating">Rating</option>
                                                                <option value="favorite" data-name="Favorite">Favorite</option>
                                                            </select>
                                                            </div>                                                       
                                                        </div>
                                                    </div>
                                                </div>
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <label class="find-page-title">Set a Time</label>
                                                                <img class="icon-about" src="<?php echo get_template_directory_uri(); ?>/library/images/icon_About.png"  alt="">
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-sm-3 col-md-3 col-xs-3">
                                                                <div class="find-general-border">
                                                                <label class="find-label">Month</label>
                                                                <div class="form-group border-ras select-style">
                                                                    <select class="select-box-it form-control" name="available_month" id="select-available-month">
                                                                        
                                                                        <?php 
                                                                        for ($j = 1; $j < 13; $j++) {
                                                                            if($j < 10)
                                                                                $jt = '0'.$j;
                                                                            else
                                                                                $jt = $j;
                                                                        ?>
                                                                        <option value="<?php echo $jt; ?>"><?php echo date('M', mktime(0,0,0,$j)) ?></option>
                                                                        <?php
                                                                        }
                                                                        ?>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            </div>
                                                            <div class="col-sm-3 col-md-3 col-xs-3">
                                                                <div class="find-general-border">
                                                                <label class="find-label">Day:</label>
                                                                <div class="form-group border-ras select-style">
                                                                    <select class="select-box-it form-control" name="available_day" id="select-available-day">
                                                                       
                                                                        <?php 
                                                                        for ($i = 1; $i < 32; $i++) {
                                                                            if($i < 10)
                                                                                $it = '0'.$i;
                                                                            else
                                                                                $it = $i;
                                                                        ?>
                                                                        <option value="<?php echo $it; ?>"><?php echo $i ?></option>
                                                                        <?php
                                                                        }
                                                                        ?>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            </div>
                                                            <div class="col-sm-3 col-md-3 col-xs-3">
                                                                <div class="find-general-border">
                                                                <label class="find-label">Time:</label>
                                                                <div class="form-group border-ras select-style">
                                                                    <select class="select-box-it form-control" name="available_time" id="select-available-time">
                                                                        
                                                                        <?php 
                                                                        $dt_hour = (int)$dt->format('H');
                                                                        $type = 'am';
                                                                        for ($l = $dt_hour; $l < 24; $l++) {
                                                                            if ($l > 11){
                                                                                $k = $l - 12;                      
                                                                                $type = 'pm'; 
                                                                            }else{
                                                                                $k = $l;
                                                                            }
                                                                            $ks = $id = $k + 1;
                                                                            if($k == 0) $k = 12;
                                                                            $kl = $k;
                                                                            if($k < 10) $k = '0'.$k;
                                                                            if($id < 10) $id = '0'.$id;
                                                                        ?>
                                                                        <option data-time="<?php echo $kl.':00:'.$type.' ~ '.$kl.':30:'.$type ?>" data-time-view="<?php echo $kl.':00'.$type.'-'.$kl.':30'.$type ?>" value="<?php echo $kl.':00'.$type; ?>"><?php echo $k.':00'.' '.$type.' - '.$k.':30'.' '.$type ?></option>
                                                                        <option data-time="<?php echo $kl.':30:'.$type.' ~ '.$ks.':00:'.$type ?>" data-time-view="<?php echo $kl.':30'.$type.'-'.$ks.':00'.$type ?>" value="<?php echo $kl.':30'.$type; ?>"><?php echo $k.':30'.' '.$type.' - '.$id.':00'.' '.$type ?></option>
                                                                        <?php
                                                                        }
                                                                        ?>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            </div>
                                                            <div class="col-sm-3 col-md-3 col-xs-3">
                                                                <div class="find-general-border">
                                                                <label class="find-label">Year:</label>
                                                                <div class="form-group border-ras select-style">
                                                                    <input type="text" class="select-box-it form-control" name="available_year" value="<?php echo $dt->format('Y') ?>" id="available_year" >
                                                                </div>
                                                            </div>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                           
                                                            <div class="col-sm-6 col-md-6 col-xs-6">
                                                                <button class="btn-dark-blue border-btn btn-available-reset" type="button" name="available_reset">
                                                                    <?php _e('Reset', 'iii-dictionary') ?>
                                                                </button>
                                                            </div>
                                                            <div class="col-sm-6 col-md-6 col-xs-6 available-search-mb">
                                                                <button class="btn-dark-blue border-btn" id="btn-available-search" type="button" name="available_search">
                                                                    <?php _e('Search Now', 'iii-dictionary') ?>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="tutoring-table">
                                                        <label class="result_quick" style="display: none"></label>
                                                        <table id="table-detail-tutor">
                                                            <tbody>
                                                                <tr class="tr-detail">
                                                                    <td>
                                                                        
                                                                        <p class="subject-selected-detail">
                                                                            <span>Subject:</span>
                                                                            <span id="selected-subject" class="not-selected active">Not selected yet</span>
                                                                        </p>
                                                                        <p class="type-detail">
                                                                            <span>Type:</span>
                                                                            <span id="selected-type" class="not-selected">Not selected yet</span>
                                                                        </p>
                                                                        <p class="date-detail">
                                                                            <span>Date:</span>
                                                                            <span id="selected-date"></span>
                                                                        </p>
                                                                        <p class="tutor-detail">
                                                                            <span>Tutor:</span>
                                                                            <span id="selected-tutor" class="not-selected">Not selected yet</span>
                                                                        </p>
                                                                        <img class="close-detail" src="<?php echo get_template_directory_uri(); ?>/library/images/12_Close.png" alt="">
                                                                        <button class="btn-dark-blue border-btn btn-schedule-now" type="button" name="schedule_now" id="btn-schedule-now">Schedule This Tutor</button>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                        <table>
                                                            <tbody id="table-list-tutor">
                                                               
                                                            </tbody>
                                                        </table>
                                                        <div class="slide-resume">
                                                            
                                                        </div>
                                                    </div>                                                    
                                                </div>

                                                <div class="main-status-request" style="display: none;">
                                                    <div class="border-selectall color-border">
                                                        <button type="button" class="btn-sub-status active" name="list_status" id="btn-status-all" data-status="all">
                                                            Show All
                                                        </button>
                                                        <button type="button" class="btn-sub-status" name="list_waiting" id="btn-status-waiting" data-status="waiting">
                                                            Waiting
                                                        </button>
                                                        <button type="button" class="btn-sub-status" name="list_confirmed" id="btn-status-confirmed" data-status="confirmed">
                                                            Confirmed
                                                        </button>
                                                        <button type="button" class="btn-sub-status" name="list_canceled" id="btn-status-canceled" data-status="canceled">
                                                            Canceled
                                                        </button>
                                                        <button type="button" class="btn-sub-status" name="list_Finished" id="btn-status-finished" data-status="finished">
                                                            Finished
                                                        </button>
                                                    </div>
                                                    <div class="tutoring-table">
                                                        <table>
                                                            <tbody id="table-status-request">
                                                                
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                                <div class="main-view-request" style="display: none;">
                                                    <div class="row">
                                                        <div class="col-md-11">
                                                            <p class="name-request-vew">
                                                                <img class="img-new-request" src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Status_Confirmed.png">
                                                                <span>CONFIRMED</span>
                                                            </p>
                                                        </div>
                                                        <div class="col-md-1 no-padding text-right">
                                                            <img class="goto-main-schedule" src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Tutor_GoBack.png" data-type="schedule">
                                                        </div>
                                                    </div>
                                                    <p class="time-request">
                                                        <span class="current-view-day"><?php echo $dt->format('F d, Y')?></span>
                                                        <span class="stuff-view-day">(<?php echo $dt->format('D') ?>)</span>
                                                        <span class="time-current-view"></span>
                                                    </p>
                                                    <p class="location-request">
                                                        <img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Status_Location.png">
                                                        <span class="label-timezone">Time Zone:</span>
                                                        <span class="name-timezone">New Work</span>
                                                    </p>
                                                    <p class="tutor-request">
                                                        <img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Status_Tutor.png">
                                                        Tutor: <span>Vincent Burke</span>
                                                    </p>
                                                    <p class="subject-request">
                                                        <img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Status_Subject.png">
                                                        <span>English Conversation</span>
                                                    </p>
                                                    <p class="points-request">
                                                        <img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Status_Points.png">
                                                        <span>37 Points($) used</span>
                                                    </p>
                                                    <p class="message-sent">Message Sent:</p>
                                                    <p class="title-request">Test</p>
                                                    <p class="more-request clearfix">
                                                        <span class="by">by <span>Peter Chung</span></span>     
                                                        <img class="btn-edit-desc" src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Tutor_Edit.png">
                                                        <span class="create-time">Jan 26, 2018 (5:30)</span>
                                                    </p>
                                                    <p class="description-request">Test</p>
                                                    
                                                    <div class="row">
                                                        <div class="col-sm-6 col-md-6 col-xs-12">
                                                            <div class="form-group">
                                                                <button class="btn-dark-blue border-btn btn-view-request" type="button" name="send-tutor">
                                                                    <?php _e('Start Tutor!', 'iii-dictionary') ?>
                                                                </button>
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-6 col-md-6 col-xs-12">
                                                            <div class="form-group">
                                                                <button class="btn-dark-blue border-btn btn-reschedule-request" type="button" name="send-tutor">
                                                                    <?php _e('Cancel & Reschedule', 'iii-dictionary') ?>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <p class="time-current-request"></p>
                                                </div>

                                                <div class="writting-review" style="display: none;">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <p class="head-title-resum">Create Review</p>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <span style="font-size: 14px;color: #515151;">Tutor Rating &nbsp;</span>
                                                        <span>
                                                            <label>
                                                                <input id="star1" type="checkbox" class="star_buttons option-input-star radio" value="1" data-star="1" name="star">
                                                            </label>
                                                            <label>
                                                                <input id="star2" type="checkbox" class="star_buttons option-input-star radio" value="1" data-star="2" name="star">
                                                            </label>
                                                            <label>
                                                                <input id="star3" type="checkbox" class="star_buttons option-input-star radio" value="1" data-star="3" name="star">
                                                            </label>
                                                            <label>
                                                                <input id="star4" type="checkbox" class="star_buttons option-input-star radio" value="1" data-star="4" name="star">
                                                            </label>
                                                            <label>
                                                                <input id="star5" type="checkbox" class="star_buttons option-input-star radio" value="1" data-star="5" name="star">
                                                            </label>
                                                        </span>
                                                        <button id="cancel-writing">Cancel</button>
                                                    </div>

                                                    
                                                        <div class="find-general-border">
                                                        <span class="find-label">Headline:</span>
                                                            <div class="form-group">
                                                                <input type="text" class="form-control border-ras" name="subject" value="" id="write-review-subject" placeholder="What's most important to know?" style="padding-left: 0; border-radius: 0 !important; font-size: 15px;">
                                                                
                                                            </div>
                                                        </div>
                                                        <div id="desc-class2" class="mt-bottom-10">
                                                                <span class="editor-top-left"></span>
                                                                <span class="editor-top-right"></span>
                                                                <span class="editor-bottom-left"></span>
                                                                <span class="editor-bottom-right"></span>
                                                                <?php
                                                                $editor_settings = array(
                                                                    'wpautop' => false,
                                                                    'media_buttons' => false,
                                                                    'quicktags' => false,
                                                                    'editor_height' => 50,
                                                                    'textarea_rows' => 3,
                                                                    'tinymce' => array(
                                                                        'toolbar1' => ''
                                                                    )
                                                                );
                                                                ?>
                                                                <?php wp_editor('', 'message-review', $editor_settings); ?>
                                                                <div class="clear-both"></div>
                                                            </div>
                                                           
                                                       
                                                    <div class="row">
                                                        <div class="col-sm-6 col-md-6">
                                                            <button type="button" class="btn-orange2 btn-green border-ras" name="submit_review" id="btn-submit-review">Submit Review</button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="schedule-view" data-id="" style="display: none; margin-top: -400px;">
                                                    <p class="head-title-resum">Schedule</p>
                                                    <p style="font-size: 13px;color: #656565;">This time calender shows the current tutor's available time. The current time can be  adjusted to local timezone with the timezone switch.</p>
                                                    
                                                    <div style="height: 36px;background: #f5f6f8;">
                                                        <div class="current-stuff" style="padding: 4px;">
                                                                <span class="current-day"></span>
                                                                <span class="stuff-day"></span>
                                                        </div>
                                                    </div>
                                                    <div style=" position: absolute; width: 100%; z-index: 1; height: 79px; background: #fff;">
                                                    <div class="row" style=" height: 55px; border-bottom: 1px solid #e1e1e1;margin: 0 !important;">
                                                        
                                                            <div id="sandbox-calender-tutor" class="col-sm-6 col-md-6"></div>
                                                        
                                                    
                                                        <div class="col-sm-6 col-md-6">
                                                            <span style="float: right;padding-top: 14px;padding-left: 5px;">
                                                                <img id="back-w" src="<?php echo get_template_directory_uri(); ?>/library/images/Chalendar_icon_Left_circle.png">
                                                                <img id="next-w" src="<?php echo get_template_directory_uri(); ?>/library/images/Chalendar_icon_Rightt_circle.png" >
                                                            </span>
                                                            <span class="date-view-pick">
                                                                <span class="week-start"></span>
                                                                <span class="week-end"></span>
                                                                <span class="year-pick"></span>
                                                            </span>
                                                        </div>
                                                    </div>
                                                    </div>
                                                    <div style="padding-top: 75px;">
                                                        <table class="table table-condensed table-tutoring">
                                                                    <tbody id="list-schedule-tutor" class="table-list-schedule">
                                                                    </tbody>

                                                        </table>
                                                        <ul id="tutoring-scheduled-tutor" class=""></ul>
                                                    </div>
                                                    
                                                </div>

                                                <div class="row header-title-newschedule">
                                                    <div class="col-md-11">
                                                        <p class="name-request">
                                                            <img class="img-new-request" src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Request.png">
                                                            <span>REQUEST</span>
                                                        </p>
                                                    </div>
                                                    <div class="col-md-1 no-padding text-right">
                                                        <img class="goto-main-schedule" src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Tutor_GoBack.png" data-day="">
                                                    </div>
                                                </div>

                                                <div class="main-my-schedule" style="display: none;">
                                                    <div class="box-schedule-left">
                                                        <div class="border-datepicker">
                                                            <div id="sandbox-container-tutor"></div>
                                                            <div class="upcoming-schedule">
                                                                <h4>Upcoming Schedules</h4>
                                                                <div class="upcoming-main style-scrollbar">
                                                                    <ul id="upcoming-schedule">
                                                                        <li>
                                                                            <span class="time-upcoming">Dec 14, 2018 / 8:30am - 9:30am</span>
                                                                            <span>English business conversation</span>
                                                                        </li>
                                                                        <li>
                                                                            <span class="time-upcoming">Dec 14, 2018 / 8:30am - 9:30am</span>
                                                                            <span>English business conversation</span>
                                                                        </li>
                                                                        <li>
                                                                            <span class="time-upcoming">Dec 14, 2018 / 8:30am - 9:30am</span>
                                                                            <span>English business conversation</span>
                                                                        </li>
                                                                        <li>
                                                                            <span class="time-upcoming">Dec 14, 2018 / 8:30am - 9:30am</span>
                                                                            <span>English business conversation</span>
                                                                        </li>
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                            <button type="button" class="btn-open-upcoming" id="btn-open-upcoming">
                                                                <img src="<?php echo get_template_directory_uri(); ?>/library/images/tutoring_05_Open_Upcomings.png" alt="">
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div class="box-schedule-right">
                                                        <div class="header-schedule clearfix">
                                                            <div class="col-xs-2 col-sm-2 col-md-2 no-padding-l">
                                                                <img class="schedule-left-btn" src="<?php echo get_template_directory_uri(); ?>/library/images/Chalendar_icon_Left_circle.png" data-day="<?php echo $dt_yesterday->format('Y-m-d') ?>" data-type="schedule">
                                                                <img class="schedule-right-btn" src="<?php echo get_template_directory_uri(); ?>/library/images/Chalendar_icon_Rightt_circle.png"  data-day="<?php echo $dt_tomorrow->format('Y-m-d') ?>" data-type="schedule">
                                                            </div>
                                                            <div class="col-xs-7 col-sm-7 col-md-7 no-padding close-schedule">
                                                                <span class="current-stuff">
                                                                    <span class="current-day"><?php echo $dt->format('F d') ?></span>
                                                                    <span class="stuff-day">(<?php echo $dt->format('D') ?>)</span>
                                                                </span>
                                                            </div>                                                      
                                                            <div class="col-xs-3 col-sm-3 col-md-3 text-right no-padding-r">
                                                                <button type="button" class="btn-orange2 border-btn"  id="menu-schedule-btn" data-day="<?php echo $dt->format('Y-m-d') ?>" data-type="menu">
                                                                    <img src="<?php echo get_template_directory_uri(); ?>/library/images/TimeIcon_Menu_Dropdown.png">
                                                                </button>
                                                                <ul id="open-menu-schedule">
                                                                    <li>
                                                                        <button type="button" id="all-schedule-btn" data-status="all">
                                                                            <img src="<?php echo get_template_directory_uri(); ?>/library/images/TimeIcon_Summary.png">
                                                                            All
                                                                        </button>
                                                                    </li>
                                                                    <li>
                                                                        <button type="button" id="scheduled-btn" data-status="waiting">
                                                                            <img src="<?php echo get_template_directory_uri(); ?>/library/images/TimeIcon_Scheduled.png">
                                                                            Scheduled
                                                                        </button>
                                                                    </li>
                                                                    <li>
                                                                        <button type="button" id="completed-btn" data-status="confirmed">
                                                                            <img src="<?php echo get_template_directory_uri(); ?>/library/images/TimeIcon_Completed.png">
                                                                            Completed
                                                                        </button>
                                                                    </li>
                                                                    <li>
                                                                        <button type="button" id="expired-btn" data-status="canceled">
                                                                            <img src="<?php echo get_template_directory_uri(); ?>/library/images/TimeIcon_Expired.png">
                                                                            Canceled
                                                                        </button>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                        </div> 

                                                        <div id="list-schedule-status" class="border-selectall color-border">
                                                            <button type="button" class="list-schedule-status all-status-btn active" id="all-status-btn" data-status="all">
                                                                <img src="<?php echo get_template_directory_uri(); ?>/library/images/TimeIcon_Summary.png">
                                                                All
                                                            </button>
                                                            <span class="line-schedule-status">|</span>
                                                            <button type="button" class="list-schedule-status scheduled-status-btn" id="scheduled-status-btn" data-status="waiting">
                                                                <img src="<?php echo get_template_directory_uri(); ?>/library/images/TimeIcon_Scheduled_disable.png">
                                                                Scheduled
                                                            </button>
                                                            <span class="line-schedule-status">|</span>
                                                            <button type="button" class="list-schedule-status completed-status-btn" id="completed-status-btn" data-status="confirmed">
                                                                <img src="<?php echo get_template_directory_uri(); ?>/library/images/TimeIcon_Completed_disable.png">
                                                                Completed
                                                            </button>
                                                            <span class="line-schedule-status">|</span>
                                                            <button type="button" class="list-schedule-status expired-status-btn" id="expired-status-btn" data-status="canceled">
                                                                <img src="<?php echo get_template_directory_uri(); ?>/library/images/TimeIcon_Expired_disable.png">
                                                                Canceled
                                                            </button>
                                                        </div>  

                                                        <div class="body-my-scheduled style-scrollbar" id="body-my-scheduled">
                                                            <table class="table-status-schedule">
                                                                <tbody id="table-status-schedule">
                                                                    
                                                                </tbody>
                                                            </table>
                                                            <div class="main-view-status" style="display: none;">
                                                                <div class="row">
                                                                    <div class="col-md-11">
                                                                        <p class="name-status-schedule">
                                                                            <img id="icon-status-schedule" src="<?php echo get_template_directory_uri(); ?>/library/images/TimeIcon_Completed.png">
                                                                            <span></span>
                                                                        </p>
                                                                    </div>
                                                                    <div class="col-md-1 text-right">
                                                                        <img class="close-status-schedule" src="<?php echo get_template_directory_uri(); ?>/library/images/03_Close_Icon.png">
                                                                    </div>
                                                                </div>
                                                                <p class="date-status-schedule">
                                                                    <span class="label-status-schedule">Date:</span>
                                                                    <span id="date-schedule"></span>
                                                                </p>
                                                                <p class="current-status-schedule">
                                                                    <span class="label-status-schedule">Status:</span>
                                                                    <span id="current-status"></span>
                                                                </p>
                                                                <p class="name-tutor-schedule">
                                                                    <span class="label-status-schedule">Tutor:</span>
                                                                    <span id="name-tutor-detail"></span>
                                                                </p>
                                                                <p class="point-status-schedule">
                                                                    <span class="label-status-schedule">Points:</span>
                                                                    <span id="point-schedule"></span>
                                                                </p>
                                                                <p class="review-status-schedule">
                                                                    <span class="label-status-schedule">Review:</span>
                                                                    <span id="review-schedule" class="review-schedule"></span>
                                                                </p>
                                                                <p class="cancel-this-schedule">
                                                                    <span class="label-status-schedule">Cancel:</span>
                                                                    <span class="this-cancel">Cancel This Schedule?<span id="cancel-now">Cancel it now</span></span>
                                                                    <ul id="open-menu-cancel">
                                                                        <li>
                                                                            <button type="button" id="yes-cancel-it">
                                                                                <img src="<?php echo get_template_directory_uri(); ?>/library/images/00_Icon_Cancel_it.png">
                                                                                Yes. Cancel it
                                                                            </button>
                                                                        </li>
                                                                        <li>
                                                                            <button type="button" id="no-cancel-it">
                                                                                <img src="<?php echo get_template_directory_uri(); ?>/library/images/02_Icon_Dont_Cancel_it.png">
                                                                                No. Don’t Cancel it
                                                                            </button>
                                                                        </li>
                                                                    </ul>
                                                                </p>
                                                                
                                                                <div id="desc-class2" class="edit-description" style="display: none">
                                                                    <span class="editor-top-left"></span>
                                                                    <span class="editor-top-right"></span>
                                                                    <span class="editor-bottom-left"></span>
                                                                    <span class="editor-bottom-right"></span>
                                                                    <?php
                                                                    $editor_settings = array(
                                                                        'wpautop' => false,
                                                                        'media_buttons' => false,
                                                                        'quicktags' => false,
                                                                        'editor_height' => 50,
                                                                        'textarea_rows' => 3,
                                                                        'tinymce' => array(
                                                                            'toolbar1' => 'bold,italic,strikethrough,image,bullist,numlist,blockquote,hr,alignleft,aligncenter,alignright,spellchecker,fullscreen,wp_adv'
                                                                        )
                                                                    );
                                                                    ?>
                                                                    <?php wp_editor('', 'note_status_schedule', $editor_settings); ?>
                                                                    <div class="clear-both"></div>
                                                                </div>
                                                                <!--<div class="row">-->
                                                                <!--    <div class="col-sm-12 col-md-12 col-xs-12">-->
                                                                <!--        <div class="form-group">-->
                                                                <!--            <button class="btn-dark-blue border-btn btn-status-schedule" type="button" name="send-tutor">-->
                                                                                <!--Save Note-->
                                                                <!--            </button>-->
                                                                <!--        </div>-->
                                                                <!--    </div>-->
                                                                <!--</div>-->
                                                            </div>
                                                            <table class="table table-condensed table-tutoring">
                                                                <tbody id="table-list-schedule" class="table-list-schedule">
                                                                </tbody>
                                                            </table>
                                                            <ul id="tutoring-scheduled">
                                                                
                                                            </ul>
                                                        </div>
                                                        <div class="clearfix"></div>
                                                        <div class="boxshadow">
                                                                
                                                        </div>
                                                     </div>
                                                     <div class="clearfix"></div>
                                                </div>

                                                <div class="main-new-request" style="display: none;">
                                                    <p class="time-request">
                                                        <span class="current-request-day"><?php echo $dt->format('F d') ?></span>
                                                        <span class="stuff-request-day">(<?php echo $dt->format('D') ?>)</span>
                                                        <span class="time-current"></span>
                                                    </p>
                                                    <p class="step-1">
                                                        <img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_01.png">
                                                        Messages width Subject(s) you are looking for
                                                    </p>
                                                    <div class="row">                                                    
                                                        <div class="col-sm-6 col-md-6 col-xs-12">
                                                            <div class="form-group">
                                                                <input type="text" class="form-control" name="title" value="" id="search-title">
                                                                <span class="placeholder"><?php _e('Title', 'iii-dictionary') ?>:</span>
                                                            </div>
                                                        </div>

                                                        <div class="col-sm-6 col-md-6 col-xs-12 mt-top-mb-12">
                                                            <div class="form-group">
                                                                <input type="text" class="form-control" name="title" value="<?php echo convert_timezone_to_location($u_time_zone_index) ?>" id="request-time-zone" readonly="" data-index="<?php echo $u_time_zone_index ?>" data-value="<?php echo $u_time_zone ?>">
                                                                <span class="placeholder-timezone"><?php _e('Time Zone: ', 'iii-dictionary') ?></span>
                                                            </div>
                                                        </div>                                                        
                                                    </div>

                                                    <div class="row mt-top-14 mt-bottom-5">          
                                                        <div class="col-sm-12 col-md-12 col-xs-12">
                                                            <div id="desc-class2" class="mt-bottom-10">
                                                                <span class="editor-top-left"></span>
                                                                <span class="editor-top-right"></span>
                                                                <span class="editor-bottom-left"></span>
                                                                <span class="editor-bottom-right"></span>
                                                                <?php
                                                                $editor_settings = array(
                                                                    'wpautop' => false,
                                                                    'media_buttons' => false,
                                                                    'quicktags' => false,
                                                                    'editor_height' => 50,
                                                                    'textarea_rows' => 3,
                                                                    'tinymce' => array(
                                                                        'toolbar1' => 'bold,italic,strikethrough,image,bullist,numlist,blockquote,hr,alignleft,aligncenter,alignright,spellchecker,fullscreen,wp_adv'
                                                                    )
                                                                );
                                                                ?>
                                                                <?php wp_editor('', 'description_request', $editor_settings); ?>
                                                                <div class="clear-both"></div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row mt-bottom-13">
                                                        <div class="chk-subject-type mt-bottom-10 clearfix" id="checkBoxSearch" style="margin-top: 0">
                                                            <div class="cb-type3">
                                                                <label>
                                                                    <input type="radio" class="radio_buttons_search option-input-2 radio_buttons_request" value="english_writting" data-subject="english_writting" name="subject_type_search"/>
                                                                    English Writing
                                                                </label>
                                                            </div>
                                                            <div class="cb-type3">
                                                                <label>
                                                                    <input type="radio" class="radio_buttons_search required option-input-2 radio_buttons_request" value="english_conversation" data-subject="english_conversation" name="subject_type_search"/>
                                                                    English Conversation
                                                                </label>
                                                            </div>
                                                            <div class="cb-type3">
                                                                <label>
                                                                    <input type="radio"  class="radio_buttons_search required option-input-2 radio_buttons_request" value="math_elementary" data-subject="math_elementary" name="subject_type_search"/>
                                                                    Math (upto Elementary)
                                                                </label>
                                                            </div>
                                                            <div class="cb-type3">
                                                                <label>
                                                                    <input type="radio" class="radio_buttons_search required option-input-2 radio_buttons_request" value="math_any_level" data-subject="math_any_level" name="subject_type_search"/>
                                                                    Math (any level)
                                                                </label>
                                                            </div>
                                                            <div class="cb-type3">
                                                                <label>
                                                                    <input type="radio" class="radio_buttons_search required option-input-2 radio_buttons_request" value="other" data-subject="other" name="subject_type_search"/>
                                                                    Others
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-sm-6 col-md-6 col-xs-12">
                                                            <div class="form-group">
                                                                <button id="btn-find-tutor" class="btn-dark-blue border-btn" style="background: #58AEC7;" type="button" name="send-tutor">
                                                                    <?php _e('Search Tutors', 'iii-dictionary') ?>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <p class="step-2">
                                                        <img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_02.png">
                                                        Select a Tutor from the list and send a request
                                                    </p>
                                                    <div class="border-selectall color-border">
                                                        <button type="button" class="btn-sub-tab" name="list-tutoring" id="btn-search-tutoring">
                                                            <img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_L_list_Selected.png" alt="">List
                                                        </button>
                                                        <button type="button" class="btn-sub-tab" name="list-review" id="btn-search-review">
                                                            <img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_L_Review.png" alt="">Review
                                                        </button>
                                                        <button type="button" class="btn-sub-tab" name="list-favorite" id="btn-search-favorite">
                                                            <img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_L_Favorite.png" alt="">Favorites
                                                        </button>
                                                        <button type="button" class="btn-sub-tab" name="from-class" id="btn-search-fromclass">
                                                            <img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_L_FromClass.png" alt="">From Class
                                                        </button>
                                                    </div>

                                                    <div class="tutoring-table">
                                                        <table>
                                                            <tbody id="table-search-tutor">
                                                                <tr>
                                                                    <td colspan="3" class="no-list">
                                                                         <img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_No_Schedule.png" alt="">Currently, there are no list
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <p class="step-3">
                                                        <img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_03.png">
                                                        Credits required for this Tutoring
                                                    </p>
                                                    <p class="used-points">
                                                        <?php 
                                                            $pst = mw_get_option('price_schedule_tutoring');
                                                            $pst = $pst*30/100;
                                                        ?>
                                                        <img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Points_Used.png">
                                                        <span class="number-points"><?php echo $pst ?> Points($)</span>
                                                        will be used for this Tutoring
                                                    </p>
                                                    <p class="total-points">
                                                        <?php
                                                        if($is_user_logged_in){
                                                            $user_points = get_user_meta($current_user->ID, 'user_points', true);
                                                            $user_points = empty($user_points) ? 0 : $user_points;
                                                        }else{
                                                            $user_points = 0;
                                                        }
                                                        ?>
                                                        <img src="<?php echo get_template_directory_uri(); ?>/library/images/Icon_Total_Point.png">
                                                        You have total of <span class="total-num-points"><?php echo $user_points ?> Points($)</span> Remaining. To Purchase more Points, <a href="">Click here.</a>
                                                    </p>
                                                    <div class="row">
                                                        <div class="col-sm-6 col-md-6 col-xs-12">
                                                            <div class="form-group">
                                                                <button id="btn-sent-request" class="btn-dark-blue border-btn" style="background: #65C762;" type="button" name="send-tutor">
                                                                    <?php _e('Review and Send Request', 'iii-dictionary') ?>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>                                                
                                            </div>
                                            <div id="tab-myclass" class="tab-pane fade">
                                                
                                            </div>
                                            <div id="tab-mymessage" class="tab-pane fade">
                                                
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div> 
                            <div id="english-main" class="tab-pane fade">
                                <div class="student-center">
                                    <div class="row">
                                        <div class="col-sm-6 col-md-6 col-xs-6">
                                            <p class="mt-bottom-12 student-center-title">Student Information Center</p>
                                            <div class="new-request-list">ENGLISH</div>
                                        </div>
                                        <div class="col-sm-6 col-md-6 col-xs-6 text-right">
                                            <select class="select-box-it form-control">
                                                <option>English Main</option>
                                                <option>Spelling Practice</option>
                                                <option>Vocabulary & Grammar</option>
                                                <option>Reading Comprehension</option>
                                                <option>Writing Practice</option>
                                                <option>Vocabulary Builder</option>
                                                <option>SAT Preparation</option>
                                                <option>Conversation Practice</option>

                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>                
                        </div>
                    </div>

                    <div class="modal modal-red-brown" id="top-my-schedules" tabindex="-1" role="dialog" aria-hidden="true" style="display: none;z-index: 3000; top: 62px;">
                        <div class="modal-dialog">
                            <div class="modal-contents" style="margin-top: 0;">
                                <div class="modal-body">
                                    <div class="row color-border">
                                        <div class="col-sm-6 col-md-6 col-xs-6">
                                            <p class="name-request-vew">
                                                <span>SCHEDULES STARTER</span>
                                            </p>
                                        </div>
                                        <div class="col-sm-6 col-md-6 col-xs-6">
                                            <p class="go-to-calendar">
                                                <span class="goto-calendar">
                                                    <span>Go to Calendar</span>
                                                    <img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Goto-Schedule.png" alt="">
                                                </span>
                                            </p>
                                        </div>
                                    </div>

                                    <div class="slide-my-schedule">
                                        <?php 
                                        $schedules = MWDB::get_my_schedules();
                                        if(count($schedules) > 0){
                                            foreach ($schedules as $k1 => $value) {
                                        ?>
                                            <div class="item" data-fromhour="<?php echo $value['fromhour'] ?>" data-fromminute="<?php echo $value['fromminute'] ?>" data-tohour="<?php echo $value['tohour'] ?>" data-tominute="<?php echo $value['tominute'] ?>" data-day="<?php echo $value['day'] ?>" data-type="<?php echo $value['totype'] ?>">
                                                <div class="description-detail">
                                                    <p class="subject-detail">
                                                        <span class="name-subject"><?php echo $value['private_subject'] ?></span>
                                                    </p>
                                                    <p class="my-time-request">
                                                        <span class="label-timezone">Date:</span>
                                                        <span class="my-current-day"><?php echo $value['date'] ?></span>
                                                        <span class="my-stuff-day"><?php echo $value['stuff'] ?>/</span>
                                                        <span class="my-time-current"><?php echo $value['time_view'] ?></span>
                                                    </p>
                                                    <p class="name-detail">
                                                        <span class="label-tutor">Tutor:</span>
                                                        <span class="name-tutor"><?php echo $value['tutor_name'] ?></span>
                                                    </p>
                                                    <p class="points-detail">
                                                        <span class="label-points">Points:</span>
                                                        <span class="name-points"><?php echo $value['total'] ?> Points($)</span>
                                                    </p>
                                                </div>
                                                <?php if($value['type_slide'] == 'current'){ ?>
                                                    <button id="btn-start-now<?php echo $value['id'] ?>" class="btn-dark-blue btn-start-now active" type="button" name="start-now" data-id="<?php echo $value['id'] ?>" data-student-id="<?php echo $value['id_user'] ?>" data-teacher-id="<?php echo $value['tutor_id'] ?>">
                                                            <?php _e('Initiate Now!', 'iii-dictionary') ?>
                                                    </button>
                                                <?php }else{ ?>
                                                    <button id="btn-cancel-schedule<?php echo $value['id'] ?>" class="btn-dark-blue btn-cancel-schedule active" type="button" name="cancel-schedule" data-id="<?php echo $value['id'] ?>" data-student-id="<?php echo $value['id_user'] ?>" data-teacher-id="<?php echo $value['tutor_id'] ?>">
                                                        <img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_Most-Current-Arrow.png" alt=""> <?php _e('Most Current', 'iii-dictionary') ?>
                                                    </button>
                                                <?php } ?>
                                                <button class="cancel-now" id="cancel-now<?php echo $value['id'] ?>" data-id="<?php echo $value['id'] ?>">
                                                    <img src="<?php echo get_template_directory_uri(); ?>/library/images/close_white.png">
                                                </button>
                                            </div>
                                        <?php
                                            }
                                        }else{
                                        ?>
                                            <div class="item no-detail-schedule" data-tohour="" data-tominute="" data-day="" data-type="">
                                                <div class="description-detail">
                                                    <p class="subject-detail">
                                                        <span class="name-subject">Currently there's no schedules</span>
                                                    </p>
                                                    <p class="my-time-request">
                                                        <span class="label-timezone">Date:</span>
                                                        <span class="my-current-day">N/A</span>
                                                        <span class="my-stuff-day"></span>
                                                        <span class="my-time-current"></span>
                                                    </p>
                                                    <p class="name-detail">
                                                        <span class="label-tutor">Tutor:</span>
                                                        <span class="name-tutor">N/A</span>
                                                    </p>
                                                    <p class="points-detail">
                                                        <span class="label-points">Points:</span>
                                                        <span class="name-points">0 Points($)</span>
                                                    </p>
                                                </div>
                                                <button id="btn-cancel-schedule" class="btn-dark-blue btn-cancel-schedule no-active" type="button" name="cancel-schedule" data-id="" data-student-id="" data-teacher-id="" tabindex="0">
                                                    <?php _e('Initiate Now!', 'iii-dictionary') ?>
                                                </button>
                                            </div>
                                        <?php
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <ul id="open-menu-cancel0" class="open-menu-cancel" data-id="" style="display: none;">
                        <li>
                            <button type="button" class="yes-cancel-it" data-id="">
                                <img src="<?php echo get_template_directory_uri(); ?>/library/images/00_Icon_Cancel_it.png">
                                Yes. Cancel it
                            </button>
                        </li>
                        <li>
                            <button type="button" class="no-cancel-it" data-id="">
                                <img src="<?php echo get_template_directory_uri(); ?>/library/images/02_Icon_Dont_Cancel_it.png">
                                No. Don’t Cancel it
                            </button>
                        </li>
                    </ul>

                    <div class="warp-menu">
                        <div id="menu-account-nav" class="menu-mb">
                            <div class="slide-menu-bg"></div>
                           
                            <div class="section-left">
                                <ul id="menu-left-myaccount" class="nav nav-tabs">
                                    <li class="active" id="account"><a data-toggle="tab" href="#hom"><img src="<?php 
                                    if(!empty($user_avatar)){
                                        echo $user_avatar;
                                    }else{
                                         echo get_template_directory_uri().'/library/images/IconMenu_Profile.png';
                                    }
                                    
                                    ?>" alt="<?php echo $current_user->display_name ?>" class="" alt="setting my account" style="width: 24px;height: 24px;margin:26px 0px 20px; border-radius: 8px;"></a>
                                        <div id="account-show" style="display: none;">
                                            <div style="margin-top: 13px;"><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_ACCOUNT_profile.png" style="width: 15px"></div>
                                            <div style="margin-top: 13px;"><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_ACCOUNT_subscription_points.png" style="width: 15px"></div>
                                            <div style="margin-top: 13px;"><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_ACCOUNT_update_my_account.png" style="width: 15px"></div>
                                        </div>
                                        <div></div>
                                    </li>
                                    <li id="itutoring"><a data-toggle="tab" href="#"><img src="<?php echo get_template_directory_uri(); ?>/library/images/IconMenu_Tutoring.png" class="" alt="setting my account" style="width: 24px;margin:15px 0px"></a>
                                    </li>
                                    <li class="" id="ienglish"><a data-toggle="tab" href="#"><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_MAIN_english.png" class="" alt="setting my account" style="width: 24px;margin:15px 0px"></a>
                                    </li>
                                    <li class="" id="imath"><a data-toggle="tab" href="#"><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_MAIN_math.png" class="" alt="setting my account" style="width: 24px;margin:15px 0px"></a>
                                    </li>
                                    <li class="" id="isat"><a data-toggle="tab" href="#"><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_MAIN_sat.png" class="" alt="setting my account" style="width: 24px;margin:15px 0px"></a>
                                    </li>
                                    
                                    <li id="online-course"><a data-toggle="tab" href="#"><img src="<?php echo get_template_directory_uri(); ?>/library/images/IconMenu_ClassManager.png" class="" alt="setting my account" style="width: 24px;margin:15px 0px"></a>
                                    </li>
                                    <li><a data-toggle="tab" href="#"><img src="<?php echo get_template_directory_uri(); ?>/library/images/IconMenu_Message.png" class="" alt="setting my account" style="width: 24px;margin:15px 0px"></a>
                                    </li>
                                    <li><a data-toggle="tab" href="#"><img src="<?php echo get_template_directory_uri(); ?>/library/images/IconMenu_Download.png" class="" alt="setting my account" style="width: 24px;margin:15px 0px 0"></a>
                                    </li>
                                </ul>
                            </div>

                            <div id="mySidenav" class="sidenav">
                                <ul class="nav nav-tabs none-block">
                                    <li><a class="header-menu-left" data-toggle="tab" id="myacc"><?php
                                                    if ($is_user_logged_in) {
                                                        $display_name = get_user_meta($current_user->ID, 'display_name', true);
                                                        if (!empty($display_name) && $display_name != '')
                                                            echo $display_name;
                                                        else{
                                                            $ru_first_name = get_user_meta($current_user->ID, 'first_name', true);
                                                            $ru_last_name = get_user_meta($current_user->ID, 'last_name', true);
                                                            echo $ru_first_name.' '.$ru_last_name;
                                                    };} else
                                                        _e('N/A', 'iii-dictionary');
                                                    ?></a>
                                        <ul class="sub-menu-left" id="sub-myacc">
                                            <?php if (!$is_user_logged_in) { ?>
                                            <li id="sub-createacc" class="active"><a class="redirect-create" data-toggle="tab" href="#create-account">Create Basic Account</a></li>
                                            <?php } ?>
                                            <li id="sub-profile"><a class="redirect-create" data-toggle="tab" href="#profile">Profile</a></li>
                                            <li id="sub-update-info"><a class="redirect-create" data-toggle="tab" href="#updateinfo">Update My Account</a></li>
                                            <li><a class="redirect-create" data-toggle="tab" href="#subscription" id="status-history">Subscription & Points</a></li>
                                        </ul>
                                        <div></div>
                                    </li>
                                    <li><a class="header-menu-left padd-adjus redirect-create" id="mtutoring" data-toggle="tab">Tutoring</a>
                                        <ul class="sub-menu-left" id="sub-tutoring">
                                            <li id="getting-tutoring"><a class="redirect-create" data-toggle="tab" href="#tutoring-main">Getting Tutoring</a></li>
                                            <li id="sub-findingtutor" ><a class="redirect-create" data-toggle="tab" href="#tutoring-main">Find a Tutor</a></li>
                                            <li id="sub-schedule-li"><a class="redirect-create" data-toggle="tab" href="#tutoring-main">Schedule</a></li>
                                            <li id="sub-status"><a class="redirect-create" data-toggle="tab" href="#tutoring-main">Status</a></li>    
                                        </ul>
                                    </li>
                                    <li><a class="header-menu-left padd-adjus redirect-create" id="english-all" data-toggle="tab">English</a>
                                        <ul id="menglish" class="sub-menu-left" style="display: none;">
                                            <li><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_MAIN-SUB_normal_state.png" >&nbsp;&nbsp;<a data-toggle="tab">English Main</a></li>
                                            <li><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_MAIN-SUB_normal_state.png" >&nbsp;&nbsp;<a data-toggle="tab">Spelling Practice</a></li>
                                            <li><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_MAIN-SUB_normal_state.png" >&nbsp;&nbsp;<a data-toggle="tab">Vocabulary & Grammar</a></li>
                                            <li><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_MAIN-SUB_normal_state.png" >&nbsp;&nbsp;<a data-toggle="tab">Reading Comprehension</a></li>
                                            <li><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_MAIN-SUB_normal_state.png" >&nbsp;&nbsp;<a data-toggle="tab">Writing Practice</a></li>
                                            <li><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_MAIN-SUB_normal_state.png" >&nbsp;&nbsp;<a data-toggle="tab">Vocabulary Builder</a></li>
                                            <li><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_MAIN-SUB_normal_state.png" >&nbsp;&nbsp;<a data-toggle="tab">SAT Preparation</a></li>
                                            <li><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_MAIN-SUB_normal_state.png" >&nbsp;&nbsp;<a data-toggle="tab">Conversation Practice</a></li>

                                        </ul>
                                    </li>
                                    <li><a class="header-menu-left padd-adjus redirect-create" id="math-all" data-toggle="tab">Math</a>
                                        <ul id="mmath" class="sub-menu-left">
                                            <li><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_MAIN-SUB_normal_state.png" >&nbsp;&nbsp;<a data-toggle="tab">Math Main</a></li>
                                            <li><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_MAIN-SUB_normal_state.png" >&nbsp;&nbsp;<a data-toggle="tab">Elementary</a></li>
                                            <li><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_MAIN-SUB_normal_state.png" >&nbsp;&nbsp;<a data-toggle="tab">Algebra 1</a></li>
                                            <li><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_MAIN-SUB_normal_state.png" >&nbsp;&nbsp;<a data-toggle="tab">Algebra 2</a></li>
                                            <li><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_MAIN-SUB_normal_state.png" >&nbsp;&nbsp;<a data-toggle="tab">Geometry</a></li>
                                            <li><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_MAIN-SUB_normal_state.png" >&nbsp;&nbsp;<a data-toggle="tab">Calculus</a></li>
                                            <li><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_MAIN-SUB_normal_state.png" >&nbsp;&nbsp;<a data-toggle="tab">ikMath Courses</a></li>
                                            <li><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_MAIN-SUB_normal_state.png" >&nbsp;&nbsp;<a data-toggle="tab">SAT Preparation</a></li>
                                            <li><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_MAIN-SUB_normal_state.png" >&nbsp;&nbsp;<a data-toggle="tab">SAT 2 Preparation</a></li>

                                        </ul>
                                    </li>
                                    <li><a class="header-menu-left padd-adjus redirect-create" id="sat-all" data-toggle="tab">SAT</a>
                                        <ul class="sub-menu-left"  id="msat">
                                            <li><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_MAIN-SUB_normal_state.png" >&nbsp;&nbsp;<a data-toggle="tab">English</a></li>
                                            <ul class="sub-menu-left" style="padding-left: 14px">
                                                <li><img src="<?php echo get_template_directory_uri(); ?>/library/images/03_Icon_Sub-menu.png" style="width: 8px !important">&nbsp;&nbsp;<a data-toggle="tab">English Main</a></li>
                                                <li><img src="<?php echo get_template_directory_uri(); ?>/library/images/03_Icon_Sub-menu.png" style="width: 8px !important">&nbsp;&nbsp;<a data-toggle="tab">English SAT</a></li>
                                                <li><img src="<?php echo get_template_directory_uri(); ?>/library/images/03_Icon_Sub-menu.png" style="width: 8px !important">&nbsp;&nbsp;<a data-toggle="tab">Essay Writing</a></li>
                                                <li><img src="<?php echo get_template_directory_uri(); ?>/library/images/03_Icon_Sub-menu.png" style="width: 8px !important">&nbsp;&nbsp;<a data-toggle="tab">SAT Tutoring</a></li>
                                            </ul>
                                            
                                            <li><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_MAIN-SUB_normal_state.png" >&nbsp;&nbsp;<a data-toggle="tab">Math</a></li>
                                            <ul class="sub-menu-left" style="padding-left: 14px">
                                                <li><img src="<?php echo get_template_directory_uri(); ?>/library/images/03_Icon_Sub-menu.png" style="width: 8px !important">&nbsp;&nbsp;<a data-toggle="tab">Math Main</a></li>
                                                <li><img src="<?php echo get_template_directory_uri(); ?>/library/images/03_Icon_Sub-menu.png" style="width: 8px !important">&nbsp;&nbsp;<a data-toggle="tab">Math SAT 1</a></li>
                                                <li><img src="<?php echo get_template_directory_uri(); ?>/library/images/03_Icon_Sub-menu.png" style="width: 8px !important">&nbsp;&nbsp;<a data-toggle="tab">Math SAT 2</a></li>
                                                <li><img src="<?php echo get_template_directory_uri(); ?>/library/images/03_Icon_Sub-menu.png" style="width: 8px !important">&nbsp;&nbsp;<a data-toggle="tab">SAT Tutoring</a></li>
                                            </ul>
                                            
                                        </ul>
                                    </li>
                                    <li><a class="header-menu-left padd-adjus redirect-create" id="class-manager">Online Course</a>
                                        <ul class="sub-menu-left" id="sub-course">
                                            <li id="free-course-show"><img id="free-course-img" src="<?php echo get_template_directory_uri(); ?>/library/images/icon_MAIN-SUB_normal_state.png" >&nbsp;&nbsp;<a data-toggle="tab">Free Online Courses</a></li>
                                            <ul id="free-course">
                                                <li><img src="<?php echo get_template_directory_uri(); ?>/library/images/03_Icon_Sub-menu.png" style="width: 8px !important">&nbsp;&nbsp;<a  href="https://iktutor.com/iklearn/en/?r=spelling-practice" target="_bank">Spelling</a> </li>
                                                <li><img src="<?php echo get_template_directory_uri(); ?>/library/images/03_Icon_Sub-menu.png" style="width: 8px !important">&nbsp;&nbsp;<a  href="https://iktutor.com/iklearn/en/?r=vocabulary-practice" target="_bank">Vocab & Grammar</a></li>
                                                <li><img src="<?php echo get_template_directory_uri(); ?>/library/images/03_Icon_Sub-menu.png" style="width: 8px !important">&nbsp;&nbsp;<a  href="https://iktutor.com/iklearn/en/?r=reading-comprehension" target="_bank">Reading Comprehen</a></li>
                                                <li><img src="<?php echo get_template_directory_uri(); ?>/library/images/03_Icon_Sub-menu.png" style="width: 8px !important">&nbsp;&nbsp;<a href="https://iktutor.com/iklearn/en/?r=writing-practice" target="_bank">Writing</a></li>
                                                <li id="math-course-show"><img id="math-course-img" src="<?php echo get_template_directory_uri(); ?>/library/images/icon_MAIN-SUB_normal_state.png" >&nbsp;&nbsp;<a data-toggle="tab"  target="_bank" style="width: 80%">Math</a></li>
                                                <ul id="math-course">
                                                    <li><img src="<?php echo get_template_directory_uri(); ?>/library/images/03_Icon_Sub-menu.png" style="width: 8px !important">&nbsp;&nbsp;<a href="https://math.iktutor.com/iklearn/en/?r=arithmetics" target="_bank"style="width: 80%">Math Elementary </a></li>
                                                    <li><img src="<?php echo get_template_directory_uri(); ?>/library/images/03_Icon_Sub-menu.png" style="width: 8px !important">&nbsp;&nbsp;<a href="https://math.iktutor.com/iklearn/en/?r=algebra-i" target="_bank"style="width: 80%">Math Algebra 1 </a></li>
                                                    <li><img src="<?php echo get_template_directory_uri(); ?>/library/images/03_Icon_Sub-menu.png" style="width:8px !important">&nbsp;&nbsp;<a href="https://math.iktutor.com/iklearn/en/?r=algebra-ii" target="_bank"style="width: 80%">Math Algebra 2 </a></li>
                                                    <li><img src="<?php echo get_template_directory_uri(); ?>/library/images/03_Icon_Sub-menu.png" style="width: 8px !important">&nbsp;&nbsp;<a href="https://math.iktutor.com/iklearn/en/?r=geometry" target="_bank"style="width: 80%">Math Geometry </a></li>
                                                
                                                    <li><img src="<?php echo get_template_directory_uri(); ?>/library/images/03_Icon_Sub-menu.png" style="width: 8px !important">&nbsp;&nbsp;<a href="https://math.iktutor.com/iklearn/en/?r=calculus" target="_bank"style="width: 80%">Math Calculus </a></li>
                                                </ul>
                                            </ul>
                                            <li id="english-conver-show"><img id="english-conver-img" src="<?php echo get_template_directory_uri(); ?>/library/images/icon_MAIN-SUB_normal_state.png" >&nbsp;&nbsp;<a data-toggle="tab" >Free English Listening</a></li>
                                            <ul id="english-conver">
                                                <li><img src="<?php echo get_template_directory_uri(); ?>/library/images/03_Icon_Sub-menu.png" style="width: 8px !important">&nbsp;&nbsp;<a href="https://ael.iktutor.com/ael/index.php?r=exam/spelling&lang=en_US" target="_bank">Listening & Spell</a></li>
                                                <li><img src="<?php echo get_template_directory_uri(); ?>/library/images/03_Icon_Sub-menu.png" style="width: 8px !important">&nbsp;&nbsp;<a href="https://ael.iktutor.com/ael/index.php?r=exam/vocab&lang=en_US" target="_bank">Listening & Vocab</a></li>
                                            </ul>
                                            <li id=""><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_MAIN-SUB_normal_state.png" >&nbsp;&nbsp;<a href="https://iktutor.com/iklearn/en/?r=flash-cards" target="_bank">Vocabulary Builder </a></li>

                                            <li id=""><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_MAIN-SUB_normal_state.png" >&nbsp;&nbsp;<a href="https://ael.iktutor.com/ael/index.php" target="_bank">English Conversation </a></li>
                                            
                                            <li id="sat-tutoring"><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_MAIN-SUB_normal_state.png" >&nbsp;&nbsp;<a href="https://iktutor.com/iklearn/en/?r=sat-preparation"  target="_bank">English SAT </a></li>
                                            <li id="sat-english"><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_MAIN-SUB_normal_state.png" >&nbsp;&nbsp;<a href="https://math.iktutor.com/iklearn/en/?r=sat-preparation/emathk&client=math-emathk"  target="_bank">Math Tutoring Plan </a></li>
                                            <li id="sat-math1"><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_MAIN-SUB_normal_state.png" >&nbsp;&nbsp;<a href="https://math.iktutor.com/iklearn/en/?r=sat-preparation/sat1prep&client=math-sat1"  target="_bank">Math SAT 1 </a></li>
                                            <li id="sat-math2"><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_MAIN-SUB_normal_state.png" >&nbsp;&nbsp;<a href="https://math.iktutor.com/iklearn/en/?r=sat-preparation/sat2prep&client=math-sat2"  target="_bank">Math SAT 2 </a></li>
                                            
                                            <li id="onl-course"><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_MAIN-SUB_normal_state.png" style="margin-top: -36px;" >&nbsp;&nbsp;<a href="#" data-toggle="tab">English Conversation Practice with a Tutor</a></li>
                                            <li id="onl-math"><img src="<?php echo get_template_directory_uri(); ?>/library/images/icon_MAIN-SUB_normal_state.png" >&nbsp;&nbsp;<a href="#" data-toggle="tab">Math Tutoring Session</a></li>

                                        </ul>
                                    </li>
                                    <li><a class="header-menu-left padd-adjus redirect-create" href="#"> Message</a></li>
                                    <li><a class="header-menu-left padd-adjus redirect-create" href="#">Downloads</a></li>
                                    
                                </ul>
                            </div>
                           
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <!-- End Menu My Account -->
                </div>
            </div>
        </div>