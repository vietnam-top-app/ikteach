<?php
	$route = get_route();
	
	if(isset($route[1])) {
		switch($route[1]) {
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
?>
<!DOCTYPE html>

<!--[if lt IE 7]><html <?php language_attributes(); ?> class="no-js lt-ie9 lt-ie8 lt-ie7"><![endif]-->
<!--[if (IE 7)&!(IEMobile)]><html <?php language_attributes(); ?> class="no-js lt-ie9 lt-ie8"><![endif]-->
<!--[if (IE 8)&!(IEMobile)]><html <?php language_attributes(); ?> class="no-js lt-ie9"><![endif]-->
<!--[if gt IE 8]><!--> <html <?php language_attributes(); ?> class="no-js"><!--<![endif]-->

	<head>
		<meta charset="utf-8">

		<?php // force Internet Explorer to use the latest rendering engine available ?>
		<meta http-equiv="X-UA-Compatible" content="IE=edge">

		<title><?php wp_title(''); ?></title>

		<?php // <meta name="HandheldFriendly" content="True"> ?>
		<meta name="MobileOptimized" content="320">
		<meta name="viewport" content="width=device-width, initial-scale=1"/>

		<?php // icons & favicons (for more: http://www.jonathantneal.com/blog/understand-the-favicon/) ?>
		<link rel="apple-touch-icon" href="<?php echo get_template_directory_uri(); ?>/library/images/apple-touch-icon.png">
		<link rel="icon" href="<?php echo get_template_directory_uri(); ?>/favicon.png">
		<!--[if IE]>
			<link rel="shortcut icon" href="<?php echo get_template_directory_uri(); ?>/favicon.ico">
		<![endif]-->
		<?php // or, set /favicon.ico for IE10 win ?>
		<meta name="msapplication-TileColor" content="#f01d4f">
		<meta name="msapplication-TileImage" content="<?php echo get_template_directory_uri(); ?>/library/images/win8-tile-icon.png">
            <meta name="theme-color" content="#121212">

		<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>">

		<?php // wordpress head functions ?>
		<?php wp_head(); ?>
		<?php // end of wordpress head ?>

		<?php if(!is_admin_panel()) : ?>
			<script src="<?php echo get_template_directory_uri(); ?>/library/js/iklearn.js"></script>
		<?php endif ?>
		<script>var home_url = "<?php echo locale_home_url() ?>", LANG_CODE = "<?php echo $locale_code[0] ?>", isuserloggedin = <?php echo $is_user_logged_in ? 1 : 0 ?>;</script>

		<?php // drop Google Analytics Here ?>
		<?php // end analytics ?>		

		<?php if(is_admin_panel()) : ?>
			<style type="text/css">
				a.sign-up-link {
					pointer-events: none !important;
					cursor: default !important;
					color: #999 !important;
				}
			</style>
		<?php endif ?>

		<?php if(isset($active_menu)) : ?>
			<style type="text/css">
				#main-nav nav .main-menu li#menu-item-<?php echo $active_menu ?> a {
					color: #FFF;
				}
			</style>
		<?php endif ?>

	</head>

	<body <?php body_class('body-math'); ?> itemscope itemtype="http://schema.org/WebPage">
	
		<div id="container" style="
background: #ffba5a; /* Old browsers */
background: -moz-linear-gradient(left, #ffba5a 0%, #ffba5a 50%, #04c2ce 50%, #04c2ce 100%); /* FF3.6-15 */
background: -webkit-linear-gradient(left, #ffba5a 0%,#ffba5a 50%,#04c2ce 50%,#04c2ce 100%); /* Chrome10-25,Safari5.1-6 */
background: linear-gradient(to right, #ffba5a 0%,#ffba5a 50%,#04c2ce 50%,#04c2ce 100%); /* W3C, IE10+, FF16+, Chrome26+, Opera12+, Safari7+ */
filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#ffba5a', endColorstr='#04c2ce',GradientType=1 ); /* IE6-9 */">

			<header class="header header-math-new" itemscope itemtype="http://schema.org/WPHeader">

				<div class="top-nav"></div>

				<!-- <div class="main-nav-block"></div> -->

				<div class="container" style="position: relative">		
				
					<div id="logo">
						<a href="<?php echo locale_home_url() ?>" rel="nofollow" title="<?php bloginfo('name'); ?>">
							<img src="<?php echo get_template_directory_uri(); ?>/library/images/ikTeach_Logo.png" alt="">
						</a>
					</div>

					<div id="sub-logo">
						<a href="<?php echo locale_home_url(); ?>" rel="nofollow" title="Innovative Knowledge">
							<img src="<?php echo get_template_directory_uri(); ?>/library/images/ikTeach_Logo.png" alt="">
						</a>
					</div>

					<div class="dropdown" id="dropdown-account-custom">
					    <button class="dropdown-toggle text-uppercase" id="btn-account-custom" type="button" data-toggle="dropdown">Account
					    <!-- <span class="caret"></span> --></button>
					    <ul class="dropdown-menu" id="dropdown-menu-account-custom">
					      <li><a href="<?php echo locale_home_url() ?>/?r=my-account">My Account</a></li>
					      <li><a href="<?php echo locale_home_url() ?>/?r=manage-subscription">Manage Subscription</a></li>
					      <li><a href="<?php echo locale_home_url() ?>/?r=private-messages">Private Message</a></li>
					      <li><a href="<?php echo locale_home_url() ?>/?r=private-messages&view=newpm&type=feedback">Feedback to Support</a></li>
					    </ul>
					  </div>

					<?php if(defined('IK_TEST_SERVER')) : ?>
						<div style="position: absolute;left: 240px;top: 5px">
							<h2 style="margin: 0px;color: #fff;font-style: italic;text-shadow: 1px 1px #000">Test Site</h2>
						</div>
					<?php endif ?>

					<?php MWHtml::sel_lang_switcher() ?>
					<ul id="user-nav">
						<!-- <li><a href="#" class="user-name-custom">Peter Chung<span class=""></span></a></li>	 -->					
						<?php if ($is_user_logged_in) : ?>
							<li><a class="display-name" href="<?php echo locale_home_url() ?>/?r=my-account">[<?php echo $current_user->display_name ?>]</a></li>
						<?php endif ?>
							<li><a class="shopping-cart" href="<?php echo home_url_ssl() ?>/?r=payments" title="<?php _e('Shopping Cart', 'iii-dictionary') ?>"><span class="icon-cart4"></span>(<?php echo count($cart_items) ?>)</a></li>
							<!-- <li><a href="http://www.ikteach.com/<?php echo $lang; ?>" title="<?php _e('Home', 'iii-dictionary') ?>"><?php _e('Home', 'iii-dictionary') ?><span class="home-icon2"></span></a></li> -->
						<?php if (!$is_user_logged_in) : ?>
							<li><a href="<?php echo locale_home_url() ?>/?r=login" title="<?php _e('Login', 'iii-dictionary') ?>"><?php _e('Login', 'iii-dictionary') ?><span class="login-icon"></span></a></li>
							<li><a class="sign-up-link" href="<?php echo locale_home_url() ?>/?r=signup" title="<?php _e('Sign-up', 'iii-dictionary') ?>"><?php _e('Sign-up', 'iii-dictionary') ?><span class="signup-icon"></span></a></li>

							<li><a style="display: none;" class="home-icon2-custom" href="http://www.ikteach.com/<?php echo $lang; ?>" title="<?php _e('Home', 'iii-dictionary') ?>"><?php _e('Home', 'iii-dictionary') ?><span class="home-icon2"></span></a></li>

						<?php else : ?>
							<li><a class="logout-link" href="<?php echo wp_logout_url(locale_home_url()) ?>" title="<?php _e('Logout', 'iii-dictionary') ?>"><?php _e('Logout', 'iii-dictionary') ?><span class="login-icon"></span></a></li>
						<?php endif ?>
					</ul>

					<!-- <div id="btn-main-menu" class="btn-menu-collapse"></div> -->
					<div class="btn-menu-collapse" data-toggle="collapse" data-target="#navbarCollapse"></div>

					<!-- <div id="main-nav" class="row menu_main_math_teacher">
						
                                            <nav class="navbar navbar-default" style="padding-top: 16px">
							<?php wp_nav_menu(array(                           // remove nav container
									 'container_class' => 'menu_main_math_teacher_home',                 // class of container (should you choose to use it)
									 'menu' => 'Main Menu Math',  // nav name
									 'menu_class' => 'main-menu nav navbar-nav',               // adding custom nav class
									 'theme_location' => 'main-nav-math-teach-home',                 // where it's located in the theme
									 'before' => '',                                 // before the menu
									   'after' => '',                                  // after the menu
									   'link_before' => '',                            // before each link
									   'link_after' => '',                             // after each link
									   'depth' => 0,                                   // limit the depth of the nav
									 'fallback_cb' => ''                             // fallback function (if there is one)
							)); ?>
						</nav>

						<div id="btn-sub-menu" class="btn-menu-collapse"></div>

						<nav class="navbar navbar-default" id="sub-user-nav">
							<?php wp_nav_menu(array(
									 'container' => false,                           // remove nav container
									 'container_class' => '',                 // class of container (should you choose to use it)
									 'menu' => 'Function Menu',  // nav name
									 'menu_class' => 'user-nav nav navbar-nav math_teacher_nav',               // adding custom nav class
									 'theme_location' => 'math-user-nav-teach',                 // where it's located in the theme
									 'before' => '',                                 // before the menu
									   'after' => '',                                  // after the menu
									   'link_before' => '',                            // before each link
									   'link_after' => '',                             // after each link
									   'depth' => 0,                                   // limit the depth of the nav
									 'fallback_cb' => ''                             // fallback function (if there is one)
							)); ?>
						</nav>

						<nav class="navbar navbar-default visible-xs" id="lang-switcher-nav">
							<?php wp_nav_menu(array(
									 'container' => false,                           // remove nav container
									 'container_class' => '',                 // class of container (should you choose to use it)
									 'menu_class' => 'menu-lang-switcher nav navbar-nav',               // adding custom nav class
									 'theme_location' => 'lang-switcher-nav',                 // where it's located in the theme
									 'before' => '',                                 // before the menu
									   'after' => '',                                  // after the menu
									   'link_before' => '',                            // before each link
									   'link_after' => '',                             // after each link
									   'depth' => 0,                                   // limit the depth of the nav
									 'fallback_cb' => ''                             // fallback function (if there is one)
							)); ?>
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
									<li><a href="<?php echo locale_home_url() ?>/?r=teachers-box"><span class="glyphicon glyphicon-arrow-right"></span>Edit a Class</a></li>
									<li><a href="<?php echo locale_home_url() ?>/?r=homework-assignment"><span class="glyphicon glyphicon-arrow-right"></span>Assign Worksheets to a class</a></li>
									<li><a href="<?php echo locale_home_url() ?>/?r=teaching/teach-class/sell-worksheet"><span class="glyphicon glyphicon-arrow-right"></span>Buy/Sell Worksheets</a></li>
								</ul>
							</div>
							<div class="dropdown manage-tutoring-dropdown col-sm-6 col-md-6">
								<button style="height: 40px; border: none; font-size: 18px;" class="dropdown-toggle tutoring-button manage-tutoring-button" type="button" data-toggle="dropdown"><span style="float: left" class="tutoring-name-span">Tutoring</span>
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
							'en' 	=> 'English',
							'ja' 	=> 'Japaneses',
							'ko' 	=> 'Korean',
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
				          <?php foreach($langs as $code => $lang) : ?>
								<li><a href="<?php echo is_math_panel() ? str_replace('://', '://math.', site_url()) : site_url(); echo '/' . $code . '/' ?>"><?php echo $lang ?></a></li>
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
				          <li><a href="<?php echo locale_home_url() ?>/?r=my-account">My Account</a></li>
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
				    jQuery(function($) {
				     $( ".mobile-menu-button" ).each(function(index) {
				         $(this).on("click", function(){
				          var clicks = $(this).data('clicks');
				          if (clicks) {
				            var elements = document.getElementsByClassName('arrow-img'), i, len;
				             elements[index].src="<?php echo get_template_directory_uri(); ?>/library/images/00_Down_arrow.png";
				         } else {
				            var elements = document.getElementsByClassName('arrow-img'), i, len;
				             elements[index].src="<?php echo get_template_directory_uri(); ?>/library/images/01_Up_arrow.png";
				         }
				         $(this).data("clicks", !clicks);

				             
				         });
				     });   
				    });
				   </script>

			</header>
