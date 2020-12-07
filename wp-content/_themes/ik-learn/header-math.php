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
	
		<div id="container">

			<header class="header header-math" itemscope itemtype="http://schema.org/WPHeader">

				<div class="top-nav"></div>

				<div class="main-nav-block"></div>

				<div class="container" style="position: relative">		
				
					<div id="logo">
						<a href="<?php echo locale_home_url() ?>" rel="nofollow" title="<?php bloginfo('name'); ?>">
							<img src="<?php echo get_template_directory_uri(); ?>/library/images/logo-ik.png" alt="">
						</a>
					</div>

					<div id="sub-logo">
						<a href="<?php echo locale_home_url(); ?>/?r=about-us" rel="nofollow" title="Innovative Knowledge">
							<img src="<?php echo get_template_directory_uri(); ?>/library/images/ik-logo2.png" alt="">
						</a>
					</div>

					<?php if(defined('IK_TEST_SERVER')) : ?>
						<div style="position: absolute;left: 240px;top: 5px">
							<h2 style="margin: 0px;color: #fff;font-style: italic;text-shadow: 1px 1px #000">Test Site</h2>
						</div>
					<?php endif ?>

					<?php MWHtml::sel_lang_switcher() ?>

					<ul id="user-nav">						
						<?php if ($is_user_logged_in) : ?>
							<li><a class="display-name" href="<?php echo locale_home_url() ?>/?r=my-account">[<?php echo $current_user->display_name ?>]</a></li>
						<?php endif ?>
							<li><a class="shopping-cart" href="<?php echo home_url_ssl() ?>/?r=payments" title="<?php _e('Shopping Cart', 'iii-dictionary') ?>"><span class="icon-cart3"></span>(<?php echo count($cart_items) ?>)</a></li>
							<li><a href="http://www.ikteach.com/<?php echo $lang; ?>" title="<?php _e('Home', 'iii-dictionary') ?>"><?php _e('Home', 'iii-dictionary') ?><span class="home-icon"></span></a></li>
						<?php if (!$is_user_logged_in) : ?>
							<li><a href="<?php echo locale_home_url() ?>/?r=login" title="<?php _e('Login', 'iii-dictionary') ?>"><?php _e('Login', 'iii-dictionary') ?><span class="login-icon"></span></a></li>
							<li><a class="sign-up-link" href="<?php echo locale_home_url() ?>/?r=signup" title="<?php _e('Sign-up', 'iii-dictionary') ?>"><?php _e('Sign-up', 'iii-dictionary') ?><span class="signup-icon"></span></a></li>
						<?php else : ?>
							<li><a class="logout-link" href="<?php echo wp_logout_url(locale_home_url()) ?>" title="<?php _e('Logout', 'iii-dictionary') ?>"><?php _e('Logout', 'iii-dictionary') ?><span class="login-icon"></span></a></li>
						<?php endif ?>
					</ul>

					<div id="btn-main-menu" class="btn-menu-collapse"></div>

					<div id="main-nav" class="row menu_main_math_teacher">
						<nav class="navbar navbar-default" style="padding-top: 16px">
							<?php wp_nav_menu(array(
									 'container' => false,                           // remove nav container
									 'container_class' => '',                 // class of container (should you choose to use it)
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
					</div>

				</div>

			</header>
