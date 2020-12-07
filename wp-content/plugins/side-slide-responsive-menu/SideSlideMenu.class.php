<?php
/**
* Side SLide Menu Class, all methods are static.
*/
class SideSlideMenu
{
	/**
	 * initialize the plugin by adding wp actions and hooks
	 */
	public static function init($file) {
		register_activation_hook($file, array('SideSlideMenu','ssmActivate') );

		add_action( 'wp_head', array('SideSlideMenu','ssmPrintCss') );
		add_action( 'admin_menu', array('SideSlideMenu','sideSlideAdmin') );
		add_action( 'wp_enqueue_scripts', array('SideSlideMenu','addSideSlideScripts') );
		add_action( 'wp_footer', array('SideSlideMenu','addSideSlideCode') );
		add_action( 'wp_ajax_ssm_load_icons', array('SideSlideMenu', 'ssmLoadajaxIcons') );

		add_shortcode( 'ssmenu_show', array('SideSlideMenu', 'showMenuShortcode') );
		add_shortcode( 'ssmenu_hide', array('SideSlideMenu', 'hideMenuShortcode') );
		add_shortcode( 'ssmenu_toggle', array('SideSlideMenu', 'toggleMenuShortcode') );
	}


	/**
	 * Activation fucntion, adds default options
	 */
	public static function ssmActivate() {
	    if (!get_option('ssmenu_settings')) {
			$sets['menu_id']    = '-1';
			$sets['logourl']    = '';
			$sets['bgcolor']    = '2C807F';
			$sets['bordcolor']  = '0D5453';
			$sets['txtcolor']   = 'BCFFF2';
			$sets['txthovcol']  = 'FFFFFF';
			$sets['fontfamily'] = 'def';
			$sets['fontstyle']  = 'regular';
			$sets['icons']      = ',';
			$sets['footertxt']  = '';
			
	        add_option('ssmenu_settings', serialize($sets));
	    }	
	}

	/**
	 * Saves SS Menu options to DB
	 */
	public static function saveOptions() {
		$sets['menu_id']    = $_POST['ssMenuID'];
		$sets['logourl']    = $_POST['ssLogoURL'];
		$sets['bgcolor']    = $_POST['ssBGCol'];
		$sets['bordcolor']  = $_POST['ssbordCol'];
		$sets['txtcolor']   = $_POST['sstextCol'];
		$sets['txthovcol']  = $_POST['sstextHovCol'];
		$sets['fontfamily'] = $_POST['ssmfont_family'];
		$sets['fontstyle']  = $_POST['ssmfont_style'];
		$sets['icons']      = $_POST['ssmAllIconsString'];
		$sets['footertxt']  = stripslashes($_POST['ssmFoot']);

	    update_option('ssmenu_settings', serialize($sets));
	}

	/**
	 * loads SS Menu options from the DB
	 */
	public static function loadOptions() {
		$s = unserialize(get_option('ssmenu_settings'));

		return $s;
	}

	/**
	 * shortcode that loads link to show the menu
	 */
	public static function showMenuShortcode( $atts, $content = null ) {
		return '<a href="#" onclick="ssMenuInstance.showSSMenu();return false;">'.$content.'</a>';
	}

	/**
	 * shortcode that loads link to hide the menu
	 */
	public static function hideMenuShortcode( $atts, $content = null ) {
		return '<a href="#" onclick="ssMenuInstance.hideSSMenu();return false;">'.$content.'</a>';
	}

	/**
	 * shortcode that loads link to toggle the visibility the menu
	 */
	public static function toggleMenuShortcode( $atts, $content = null ) {
		return '<a href="#" onclick="ssMenuInstance.toggleSSMenu();return false;">'.$content.'</a>';
	}


	/**
	 * inserts menu HTML on every page
	 */
	public static function addSideSlideCode() {
		$settings = self::loadOptions();
		if ($settings['menu_id'] == '-1')
			return;
		$menuitems = wp_get_nav_menu_items( $settings['menu_id'] );

		include dirname( __FILE__ ) . '/sideslide_html.php';
	}

	/**
	 * renders admin view
	 */
	public static function sideSlideAdminInterface() {
		if ($_SERVER['REQUEST_METHOD'] == 'POST')
			self::saveOptions();

		$existingMenus = wp_get_nav_menus();
		$settings = self::loadOptions();

		$gFonts = self::ssmGetGoogleFonts(false, false);
		$googleFonts = $gFonts['family'];

		include dirname( __FILE__ ) . '/sideslide_admin.php';
	}

	/**
	 * prints settings as css (colors and stuff)
	 */
	public static function ssmPrintCss() {
		$settings = self::loadOptions();

		include dirname( __FILE__ ) . '/sideslide_css.php';
	}

	/**
	 * enqueue frontend scripts&styles
	 */
	public static function addSideSlideScripts() {
		wp_enqueue_style( 'fontawesome', 'http://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css');
		wp_enqueue_style( 'sideslidemenucss', plugins_url() . '/side-slide-responsive-menu/css/sideslidemenu.css');
		wp_enqueue_script( 'sideslidemenujs', plugins_url() . '/side-slide-responsive-menu/js/sideslidemenu.min.js' , array(), '1.0', true );
		wp_enqueue_script( 'hammer', plugins_url() . '/side-slide-responsive-menu/js/hammer.min.js' , array(), '1.0', true );
	}

	/**
	 * adding admin menu page
	 */
	public static function sideSlideAdmin() {
		$submenu = add_options_page('Side Slide Menu', 'Side Slide Menu', 'manage_options', 'side-slide-menu', array('SideSlideMenu', 'sideSlideAdminInterface') );
		add_action('load-'.$submenu, array('SideSlideMenu', 'ssmAdminScript')); 
	}

	/**
	 * enqueue admin menu page scripts
	 */
	public static function ssmAdminScript() {
		wp_enqueue_script( 'jquery' );
		wp_enqueue_style( 'fontawesome', 'http://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css');
		wp_enqueue_script( 'sideslidemenuAdmin', plugins_url() . '/side-slide-responsive-menu/js/sideslideadmin.js' , array(), '1.0', true );
		wp_enqueue_script( 'jscolor', plugins_url() . '/side-slide-responsive-menu/jscolor/jscolor.js' , array(), '1.0', true );
	}

	public static function ssmLoadajaxIcons() {
	    $mitems = wp_get_nav_menu_items( $_POST['data'] );
	    $settings = self::loadOptions();
	   	$icons = explode(',',  $settings['icons']);
	   	$rtn[0] = $mitems;
	   	$rtn[1] = $icons;

        echo json_encode($rtn);

		die();
	}

	/**
	 * gets all google fonts from a local txt list
	 */
	public static function ssmGetGoogleFonts($json = false, $full = false) {
		$current_date = getdate(date("U"));
		
		$current_date = $current_date['weekday'] . $current_date['month'] . $current_date['mday'] . $current_date['year'];
		
		if (!get_option('ssmenu_admin_webfonts')) {
		    $file_get = file_get_contents(dirname( __FILE__ ) . '/googlewebfonts.txt');
		    if (strlen($file_get) > 100) {
		        add_option('ssmenu_menu_admin_webfonts', $file_get);
		        add_option('ssmenu_menu_admin_webfonts_date', $current_date);
		    }
		}
		
		if (get_option('radial_menu_admin_webfonts_date') != $current_date || get_option('ssmenu_menu_admin_webfonts_date') == '') {
		    $file_get = file_get_contents(dirname( __FILE__ ) . '/googlewebfonts.txt');
		    if (strlen($file_get) > 100) {
		        update_option('ssmenu_menu_admin_webfonts', $file_get);
		        update_option('ssmenu_menu_admin_webfonts_date', $current_date);
		    }
		}
		
		$fontsjson = get_option('ssmenu_menu_admin_webfonts');

		if ($json)
		    return $fontsjson;
			
		$decode = json_decode($fontsjson, true);
		
		if ($full)
			return $decode;
			
		$webfonts = array();
		$webfonts['family']['default'] = 'Default' . '|' . 'def';
		$webfonts['variants']['default'] = 'regular';
		foreach ($decode['items'] as $key => $value) {
		    $item_family = $decode['items'][$key]['family'];
		    $item_family_trunc = str_replace(' ', '+', $item_family);
			
			$item_variants = $decode['items'][$key]['variants'];
			
		    $webfonts['family'][$item_family_trunc] = $item_family . '|' . $item_family_trunc;
			$webfonts['variants'][$item_family_trunc] = $item_variants[0];
			
			for ($i = 1; $i < count($item_variants); $i++)
				$webfonts['variants'][$item_family_trunc] .= '|' . $item_variants[$i];
		}

		return $webfonts;
	}

}
?>