<?php   
/*
Plugin Name: Side Slide Menu
Plugin URI: http://codecanyon.net/user/wp_workshop
Description: Note: Core files was modified for compatibility with IK Learn. DO NOT UPDATE THIS PLUGIN. Modern and easy to use support helpdesk solution
Author: WP Workshop
Version: 1.0
Author URI: http://codecanyon.net/user/wp_workshop
Licence: GPLv2
*/

require dirname( __FILE__ ) . '/SideSlideMenu.class.php';

add_action( 'init', 'ik_check_admins' );

function ik_check_admins() {
	if(is_mw_super_admin() || is_mw_admin()) {
		SideSlideMenu::init(__FILE__);
		wp_enqueue_style( 'overflowfix', plugins_url() . '/side-slide-responsive-menu/css/overflowfix.css');
	}
}


?>