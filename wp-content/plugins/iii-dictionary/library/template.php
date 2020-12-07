<?php
/*
 * Template's functions
 *
 */

add_action('wp_enqueue_scripts', 'load_dashicons_front_end');
function load_dashicons_front_end() {
	wp_enqueue_style('dashicons');
}

/*
 * this variable store page title value. It's value can be set in get_dict_header()
 * @scope global
 */
$page_title_tag = '';

/*
 * this function generate content of the <title> tag
 */
function dict_page_title()
{
	$route = get_route();
	$page_name = $route[0];

	if(!$page_name)
	{
		return get_bloginfo( 'name' );
	}
	
	if(isset($route[1]) && $route[0] == 'dictionary'){
		return get_dictionary_name($route[1]) . ' :: ' . get_bloginfo( 'name' );;
	}

	global $page_title_tag;
	if(isset($page_title_tag)) {
		return $page_title_tag . ' :: ' . get_bloginfo( 'name' );;
	}
	else {
		return get_bloginfo( 'name' );
	}
}

/*
 * this function generate header html
 */
function get_dict_header($page_title = '', $class = 'red-brown')
{
	global $page_title_tag;
	$page_title_tag = $page_title;
	add_filter( 'wp_title', 'dict_page_title', 11 );

	$route = get_route();
	get_header(); ?>
	<div id="content">
		<main id="main" class="container <?php echo $class ?>">
			<article id="<?php echo $route[0] ?>" <?php post_class('row main-article'); ?>>
<?php
}

/*
 * this function generate content header html
 * 
 * @param string $title
 * @param string $class
 * @param string $subtitle
 * @param array $tabs		Array of tabs list
 *
 */
function get_dict_page_title($title = '', $class = '', $subtitle = '', $tabs = array(), $info_tab_urls = array(), $is_change = false, $is_link = array(),$tabs_extras = array() )
{
	$class 	= $class != '' ? ' ' . $class : '';
	$class .= $is_change ? CSS_CHANGE_POS : '';
?>
				<header class="col-xs-12 article-header<?php echo $class ?>">
					<?php if(!empty($tabs_extras)) : ?>
						<div class="earn_money_tab">
							<div class="group-left-column">
								<?php foreach($tabs_extras['items'] as $k => $tabs_extra) : ?>
									<div class="block-left-column <?php if($k == 1) echo 'block-goto_math_teacher'; if($k == 2) echo 'block-goto_english_teacher'; ?> ">
								        <a href="<?php echo $tabs_extra['url'] ?>"><span class="bold"><?php echo $tabs_extra['text']; ?></span> </a>
								    </div>
								<?php endforeach ?>
							</div>
						</div>
					<?php endif ?>
					<div class="row">
						<?php if(!empty($is_link)) : ?>
							<div class="col-xs-12 col-sm-10 col-sm-offset-1 <?php echo $is_link['class'] ?>">
								<div>
									<label for="omg_sat-link-a"><?php echo $is_link['prefix'] ?></label>
									<a id="omg_sat-link-a" href="<?php echo $is_link['goto_url'] ?>"><?php echo $is_link['name'] ?></a>
								</div>
							</div>
						<?php endif ?>
						<?php if(!empty($info_tab_urls)) :
							$info_tab_urls = is_array($info_tab_urls) ? $info_tab_urls : array($info_tab_urls) ?>
							<div class="col-xs-12 col-sm-10 col-sm-offset-1" id="page-info-tab-container">
								<div id="page-info-tab"><span class="icon-information"></span> info</div>
								<div id="page-info-tab-dialog" class="modal fade modal-white" aria-hidden="true">
									<div class="modal-dialog">
									  <div class="modal-content">
										<div class="modal-body">
											<a href="#" data-dismiss="modal" aria-hidden="true" class="close2"></a>
											<?php foreach($info_tab_urls as $key => $url) : ?>
												<img id="t-<?php echo $key ?>" class="info-tab-img" src="<?php echo $url ?>" alt="About <?php echo $title ?>" title="About <?php echo $title ?>">
											<?php endforeach ?>
										</div>
									  </div>
									</div>
								</div>
							</div>
						<?php endif ?>
						<div class="">
							<div class="container">
								<div class="page-title-responsive">
									<h1 class="page-title<?php echo $class ?>" itemprop="headline"><?php echo $title ?></h1>
								</div>
							</div>
							<?php if($subtitle != '') : ?>
								<h4><?php echo $subtitle ?></h4>
							<?php endif ?>
							<?php if($is_change) : ?>
								<div class="clearfix"></div>
							<?php endif ?>
						</div>
					</div>
				</header>
				<div class="col-xs-12" id="page-tabs-container">
					<div class="row">
						<div class="col-xs-12 col-sm-10 col-sm-offset-1 hidden-xs hidden-sm">
							<?php if(!empty($tabs)) : ?>
								<ul id="page-tabs">
									<?php foreach($tabs['items'] as $key => $tab) : ?>
										<li class="page-tab<?php echo $tabs['active'] == $key ? ' active' : '' ?>"><a href="<?php echo $tab['url'] ?>"><?php echo $tab['text'] ?></a></li>
									<?php endforeach ?>
								</ul>
							<?php endif ?>
						</div>
						<div class="col-xs-12 col-sm-10 col-sm-offset-1 visible-xs visible-sm" style="margin-bottom: 15px">
							<?php if(!empty($tabs)) : ?>
								<select class="select-box-it" id="page-tabs-mobile">
									<?php foreach($tabs['items'] as $key => $tab) : ?>
										<option value="<?php echo $tab['url'] ?>"<?php echo $tabs['active'] == $key ? ' selected' : '' ?>><?php echo $tab['text'] ?></option>
									<?php endforeach ?>
								</select>
							<?php endif ?>
						</div>
					</div>
				</div>
				<div class="container"> <!-- add div container only content -->
				<section class="col-xs-12 entry-content">
					<div class="row">
						<div class="content-page-responsive">
<?php
}

/*
 * this function generate footer html
 */
function get_dict_footer()
{
	// display site messages
	MWHtml::ik_site_messages();
?>						
						</div>
					</div>

<?php MWHtml::ik_lockpage_dialog() ?>

				</section>
			<?php 
				$route = get_route();
				if($route[0] == 'dictionary' && !isset($route[2])) : ?>
					<div class="english-quiz-tab hidden-xs hidden-sm"></div>
					<div class="science-quiz-tab hidden-xs hidden-sm"></div>
					<div class="history-quiz-tab hidden-xs hidden-sm"></div>
					<div class="general-quiz-tab hidden-xs hidden-sm"></div>
				<?php endif ?>
			</article>
		</main>
	</div>

<?php get_footer();
}


/*
 * this function generate header for Math site
 */


function get_math_header($page_title = '', $id='main', $class = 'dark-green')
{
	global $page_title_tag;
	$page_title_tag = $page_title;
	add_filter( 'wp_title', 'dict_page_title', 11 );

	$route = get_route();

	get_header('math'); ?>

<div id="content" class="content-math" >
		<main id="<?php echo $id ?>" class="<?php echo $class ?>">
			<article id="<?php echo $route[0] ?>" <?php post_class('row main-article'); ?>>
<?php
}


/*
 * this function generate footer html
 */
function get_math_footer()
{
	// display site messages
	MWHtml::ik_site_messages();
?>						
						</div>
					</div>

<?php MWHtml::ik_lockpage_dialog() ?>

				</section>
				</div> <!-- end div container each content -->
			</article>
		</main>
	</div>

<?php get_footer('math');
}

/*
 * Routing function, hook to home_template filter
 * this function will check for user permissions, referer
 * and return path to the template file.
 */
function ik_set_route($home_template)
{
	$current_user_id = get_current_user_id();

	// check if we recevice a IPN from paypal
	// auto refresh to get new subscription
	if(get_user_meta($current_user_id, 'ik-paypal-refresh', true)) {
		update_user_meta($current_user_id, 'ik-paypal-refresh', 0);
		update_user_subscription($current_user_id);
		/* header('Refresh:0');
		exit; */
	}

	$layouts_dir = IK_PLUGIN_DIR . '/layouts/';

	$route = get_route();
	$layout = str_replace('-', '_', $route[0]);
	$layout = empty($layout) ? 'home' : $layout;
	
	// store current url as referer
	if(!empty($layout) && in_array($layout, array('login', 'signup', 'ajax', 'admin_login')) === false) {
		$_SESSION['mw_referer'] = home_url() . $_SERVER['REQUEST_URI'];
	}

	/// check if user have permission to view admin page
	if(is_admin_panel())
	{
		if($layout != 'ajax') {
			$layouts_dir .= 'admin/';
		}

		$file = $layouts_dir . $layout . '.php';

		if(!is_user_logged_in()) {
			// user hasn't logged in
			return $layouts_dir . 'admin_login.php';
		}

		$is_mw_super_admin = is_mw_super_admin();
		$is_mw_admin = is_mw_admin();

		if(!$is_mw_super_admin && !$is_mw_admin) {
			// user is not admins

			/* global $wpdb;
			$user = wp_get_current_user();
			$resutl = $wpdb->get_col('SELECT client FROM ' . $wpdb->prefix . 'dict_user_session WHERE userid = ' . $user->data->ID);
			if($resutl[0] == 0) {
				wp_redirect( site_url() );
				exit;
			} */

			ik_enqueue_messages(__('Wrong site for Login.', 'iii-dictionary'), 'error');
			wp_logout();
		}

		if(!file_exists($file)) {
			$layout = 'admin_manager';
			$file = $layouts_dir . $layout . '.php';
		}

		if(!$is_mw_super_admin) {
			// normal admin
			// check access permissions
			$admin_cap = get_user_meta($current_user_id, 'ik_admin_capabilities', true);

			if(!$admin_cap[str_replace('_', '-', $layout)]) {
				// current admin don't have permission to access this page
				ik_enqueue_messages(__('You don\'t have permission to access this page.', 'iii-dictionary'), 'error');
				wp_redirect(site_home_url());
				exit;
			}
		}

		wp_register_script('page-js', get_stylesheet_directory_uri() . '/library/js/pages/admin-' . str_replace('_', '-', $layout) . '.js', array('jquery'), '', true );		
		wp_enqueue_script('page-js');

		return $file;
	}

	$page_sub = $layout;

	if(!empty($route[1]) && in_array($page_sub, array('ajax', 'api')) === false) {
		$page_sub .= '/' . $route[1];
	}

	// check user permissions
	if($layout && !ik_user_can_view($page_sub))
	{
		if(is_user_logged_in()) {
			wp_redirect(locale_home_url());
			exit;
		}
		else {
			//wp_redirect(locale_home_url() . '/?r=login');
			wp_redirect(locale_home_url());
			exit;
		}
	}

	$file = $layouts_dir . $layout . '.php';

	if(is_math_panel()) {
		if($layout != 'ajax') {
			// search in math layout directory
			if(file_exists($layouts_dir . 'math/' . $layout . '.php')) {
				$file = $layouts_dir . 'math/' . $layout . '.php';
			}
		}
	}

    if(!file_exists($file)) {
		$layout = 'home';
        $file = $layouts_dir . $layout . '.php';
    }

	// include specific js for each page.
	$jsfile = '/library/js/pages/' . str_replace('_', '-', $layout) . '.js';
	if(file_exists(get_template_directory() . $jsfile)) {
		wp_register_script('page-js', get_stylesheet_directory_uri() . $jsfile, array('jquery'), '', true );		
		wp_enqueue_script('page-js');
	}

    return $file;
}
add_action('template_include', 'ik_set_route');

