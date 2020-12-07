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
	<script>var home_url = "<?php echo home_url() ?>", LANG_CODE = "<?php echo $locale_code[0] ?>", isuserloggedin = <?php echo $is_user_logged_in ? 1 : 0 ?>;</script>

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

<body <?php body_class(); ?> itemscope itemtype="http://schema.org/WebPage" style="background-color: #FFF;">
	<div id="container">
		<!--
		<header class="abu-header" itemscope itemtype="http://schema.org/WPHeader">
		</header> -->
		<div id="content">
			<main id="main">
				<div class="abu-logo">
					<div class="container">
						<div class="row abu-flex">
							<div class="col-md-4" style="padding-top: 50px;">
								<p style="color: #DFDFDF;"><?php _e("American English Live > ", 'iii-dictionary') ?></p>
								<h1 style="margin-top: 0px; color: #FFF;font-weight: normal;font-family: Myriad_light;"><?php _e("About Us & <br /> Contact", 'iii-dictionary') ?></h1>
							</div>
							<div class="col-md-8 abu-logo-img">
							</div>
						</div>
					</div>
				</div>
				
				<div class="abu-wwa omg_margin-tbt50 omg_padding-tb20">
					<div class="container">
						<div class="row abu-flex">
							<div class="col-md-4 abu-wwa-img">
							</div>
							<div class="col-md-8">
								<h2><?php _e("Who we are", 'iii-dictionary') ?></h2>
								<p><?php _e("Innovative Knowledge is located in the heart of the Silicon Valley, developing and has been developing educational software for almost 20 years.", 'iii-dictionary') ?></p>
								<p><?php _e("We have accumulated critical knowledge and know-how of how software assisted learning works. We do not create \"educational games\" for children because we do not believe playing games is the most effective way to learn a new concept.", 'iii-dictionary') ?></p>
								<p><?php _e("We have collaborated with San Joe State University for the K-12 content in English and math. All software development has been done in-house by engineers who have many years of experience in software development in the Silicon Valley.", 'iii-dictionary') ?></p>
							</div>
						</div>
					</div>
				</div>
				
				<div class="abu-wwo omg_margin-tbt50 omg_padding-tb20" style="background: #EAEAEA;">
					<div class="container">
						<div class="row abu-flex">
							<div class="col-md-4 abu-wwo-img">
							</div>
							<div class="col-md-8">
								<h2><?php _e("What we offer", 'iii-dictionary') ?></h2>
								<p><?php _e("Innovative Knowledge, Inc. currently offers two online programs: iklearn.com and Americanenglishlive.com.", 'iii-dictionary') ?></p>
								<p><?php _e("Americanenglishlive.com was developed for students in China, Japan, Korea and other countries. This site offers TOEIC, TOEFL, and other English learning programs.", 'iii-dictionary') ?></p>
								<p><?php _e("Iklearn.com provides Math and English learning programs, along with SAT preparation. This site offers teacher a \"classroom management tool\" to access a large database of ready-made homework and simplifies homework assigment and grading. Tutoring in English writing and math are also provide on this site.", 'iii-dictionary') ?></p>
							</div>
						</div>
					</div>
				</div>
				
				<div class="abu-cs omg_margin-tbt50 omg_padding-tb20">
					<div class="container">
						<div class="row abu-flex" style="min-height: 293px;">
							<div class="col-md-4 abu-cs-img">
							</div>
							<div class="col-md-8">
								<h2><?php _e("Customer Service", 'iii-dictionary') ?></h2>
								<div class="row">
									<div class="col-md-2">
										<?php _e("Tel:", 'iii-dictionary') ?>
									</div>
									<div class="col-md-10">
										<?php _e("(408) 274-4848", 'iii-dictionary') ?>
									</div>
									<div class="col-md-2">
										<?php _e("Email:", 'iii-dictionary') ?>
									</div>
									<div class="col-md-10">
										<?php _e("support@iklearn.com", 'iii-dictionary') ?>
										<p><?php _e("(Please note that telephone technical support requests may <br /> require lengthy hold period - email is usually a faster option.)", 'iii-dictionary')?></p>
									</div>
									<div class="col-md-2">
										<?php _e("Add:", 'iii-dictionary') ?>
									</div>
									<div class="col-md-10">
										<?php _e("Innovative Knowledge, Inc. <br/> 606 North First Street <br/> San Jose, CA 95112", 'iii-dictionary') ?>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</main>
		</div>
	</div>
</body>