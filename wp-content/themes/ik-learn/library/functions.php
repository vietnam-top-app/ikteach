<?php

/*

Author: Eddie Machado

URL: http://themble.com/bones/



This is where you can drop your custom functions or

just edit things like thumbnail sizes, header images,

sidebars, comments, ect.

*/



// LOAD BONES CORE (if you remove this, the theme will break)

require_once( 'library/bones.php' );



// CUSTOMIZE THE WORDPRESS ADMIN (off by default)

// require_once( 'library/admin.php' );



/*********************

LAUNCH BONES

Let's get everything up and running.

*********************/



function bones_ahoy() {



  //Allow editor style.

  add_editor_style( get_stylesheet_directory_uri() . '/library/css/editor-style.css' );



  // let's get language support going, if you need it

  load_theme_textdomain( 'bonestheme', get_template_directory() . '/library/translation' );



  // USE THIS TEMPLATE TO CREATE CUSTOM POST TYPES EASILY

  require_once( 'library/custom-post-type.php' );



  // launching operation cleanup

  add_action( 'init', 'bones_head_cleanup' );

  // A better title

  add_filter( 'wp_title', 'rw_title', 10, 3 );

  // remove WP version from RSS

  add_filter( 'the_generator', 'bones_rss_version' );

  // remove pesky injected css for recent comments widget

  add_filter( 'wp_head', 'bones_remove_wp_widget_recent_comments_style', 1 );

  // clean up comment styles in the head

  add_action( 'wp_head', 'bones_remove_recent_comments_style', 1 );

  // clean up gallery output in wp

  add_filter( 'gallery_style', 'bones_gallery_style' );



  // enqueue base scripts and styles

  add_action( 'wp_enqueue_scripts', 'bones_scripts_and_styles', 999 );

  // ie conditional wrapper



  // launching this stuff after theme setup

  bones_theme_support();



  // adding sidebars to Wordpress (these are created in functions.php)

  add_action( 'widgets_init', 'bones_register_sidebars' );



  // cleaning up random code around images

  add_filter( 'the_content', 'bones_filter_ptags_on_images' );

  // cleaning up excerpt

  add_filter( 'excerpt_more', 'bones_excerpt_more' );



} /* end bones ahoy */



// let's get this party started

add_action( 'after_setup_theme', 'bones_ahoy' );





/************* OEMBED SIZE OPTIONS *************/



if ( ! isset( $content_width ) ) {

	$content_width = 640;

}



/************* THUMBNAIL SIZE OPTIONS *************/



// Thumbnail sizes

add_image_size( 'bones-thumb-600', 600, 150, true );

add_image_size( 'bones-thumb-300', 300, 100, true );



/*

to add more sizes, simply copy a line from above

and change the dimensions & name. As long as you

upload a "featured image" as large as the biggest

set width or height, all the other sizes will be

auto-cropped.



To call a different size, simply change the text

inside the thumbnail function.



For example, to call the 300 x 100 sized image,

we would use the function:

<?php the_post_thumbnail( 'bones-thumb-300' ); ?>

for the 600 x 150 image:

<?php the_post_thumbnail( 'bones-thumb-600' ); ?>



You can change the names and dimensions to whatever

you like. Enjoy!

*/



add_filter( 'image_size_names_choose', 'bones_custom_image_sizes' );



function bones_custom_image_sizes( $sizes ) {

    return array_merge( $sizes, array(

        'bones-thumb-600' => __('600px by 150px'),

        'bones-thumb-300' => __('300px by 100px'),

    ) );

}



/*

The function above adds the ability to use the dropdown menu to select

the new images sizes you have just created from within the media manager

when you add media to your content blocks. If you add more image sizes,

duplicate one of the lines in the array and name it according to your

new image size.

*/



/************* THEME CUSTOMIZE *********************/



/* 

  A good tutorial for creating your own Sections, Controls and Settings:

  http://code.tutsplus.com/series/a-guide-to-the-wordpress-theme-customizer--wp-33722

  

  Good articles on modifying the default options:

  http://natko.com/changing-default-wordpress-theme-customization-api-sections/

  http://code.tutsplus.com/tutorials/digging-into-the-theme-customizer-components--wp-27162

  

  To do:

  - Create a js for the postmessage transport method

  - Create some sanitize functions to sanitize inputs

  - Create some boilerplate Sections, Controls and Settings

*/



function bones_theme_customizer($wp_customize) {

  // $wp_customize calls go here.

  //

  // Uncomment the below lines to remove the default customize sections 



  // $wp_customize->remove_section('title_tagline');

  // $wp_customize->remove_section('colors');

  // $wp_customize->remove_section('background_image');

  // $wp_customize->remove_section('static_front_page');

  // $wp_customize->remove_section('nav');



  // Uncomment the below lines to remove the default controls

  // $wp_customize->remove_control('blogdescription');

  

  // Uncomment the following to change the default section titles

  // $wp_customize->get_section('colors')->title = __( 'Theme Colors' );

  // $wp_customize->get_section('background_image')->title = __( 'Images' );

}



add_action( 'customize_register', 'bones_theme_customizer' );



/************* ACTIVE SIDEBARS ********************/



// Sidebars & Widgetizes Areas

function bones_register_sidebars() {

	register_sidebar(array(

		'id' => 'sidebar1',

		'name' => __( 'Sidebar 1', 'bonestheme' ),

		'description' => __( 'The first (primary) sidebar.', 'bonestheme' ),

		'before_widget' => '<div id="%1$s" class="widget %2$s">',

		'after_widget' => '</div>',

		'before_title' => '<h4 class="widgettitle">',

		'after_title' => '</h4>',

	));



	/*

	to add more sidebars or widgetized areas, just copy

	and edit the above sidebar code. In order to call

	your new sidebar just use the following code:



	Just change the name to whatever your new

	sidebar's id is, for example:



	register_sidebar(array(

		'id' => 'sidebar2',

		'name' => __( 'Sidebar 2', 'bonestheme' ),

		'description' => __( 'The second (secondary) sidebar.', 'bonestheme' ),

		'before_widget' => '<div id="%1$s" class="widget %2$s">',

		'after_widget' => '</div>',

		'before_title' => '<h4 class="widgettitle">',

		'after_title' => '</h4>',

	));



	To call the sidebar in your template, you can just copy

	the sidebar.php file and rename it to your sidebar's name.

	So using the above example, it would be:

	sidebar-sidebar2.php



	*/

} // don't remove this bracket!





/************* COMMENT LAYOUT *********************/



// Comment Layout

function bones_comments( $comment, $args, $depth ) {

   $GLOBALS['comment'] = $comment; ?>

  <div id="comment-<?php comment_ID(); ?>" <?php comment_class('cf'); ?>>

    <article  class="cf">

      <header class="comment-author vcard">

        <?php

        /*

          this is the new responsive optimized comment image. It used the new HTML5 data-attribute to display comment gravatars on larger screens only. What this means is that on larger posts, mobile sites don't have a ton of requests for comment images. This makes load time incredibly fast! If you'd like to change it back, just replace it with the regular wordpress gravatar call:

          echo get_avatar($comment,$size='32',$default='<path_to_url>' );

        */

        ?>

        <?php // custom gravatar call ?>

        <?php

          // create variable

          $bgauthemail = get_comment_author_email();

        ?>

        <img data-gravatar="http://www.gravatar.com/avatar/<?php echo md5( $bgauthemail ); ?>?s=40" class="load-gravatar avatar avatar-48 photo" height="40" width="40" src="<?php echo get_template_directory_uri(); ?>/library/images/nothing.gif" />

        <?php // end custom gravatar call ?>

        <?php printf(__( '<cite class="fn">%1$s</cite> %2$s', 'bonestheme' ), get_comment_author_link(), edit_comment_link(__( '(Edit)', 'bonestheme' ),'  ','') ) ?>

        <time datetime="<?php echo comment_time('Y-m-j'); ?>"><a href="<?php echo htmlspecialchars( get_comment_link( $comment->comment_ID ) ) ?>"><?php comment_time(__( 'F jS, Y', 'bonestheme' )); ?> </a></time>



      </header>

      <?php if ($comment->comment_approved == '0') : ?>

        <div class="alert alert-info">

          <p><?php _e( 'Your comment is awaiting moderation.', 'bonestheme' ) ?></p>

        </div>

      <?php endif; ?>

      <section class="comment_content cf">

        <?php comment_text() ?>

      </section>

      <?php comment_reply_link(array_merge( $args, array('depth' => $depth, 'max_depth' => $args['max_depth']))) ?>

    </article>

  <?php // </li> is added by WordPress automatically ?>

<?php

} // don't remove this bracket!





/*

This is a modification of a function found in the

twentythirteen theme where we can declare some

external fonts. If you're using Google Fonts, you

can replace these fonts, change it in your scss files

and be up and running in seconds.

*/

function bones_fonts() {

  wp_enqueue_style('googleFonts', 'http://fonts.googleapis.com/css?family=Lato:400,700,400italic,700italic');

}



//add_action('wp_enqueue_scripts', 'bones_fonts');



// Enable support for HTML5 markup.

	add_theme_support( 'html5', array(

		'comment-list',

		'search-form',

		'comment-form'

	) );



// remove auto insert <p>, <br> fillter



remove_filter( 'the_content', 'wpautop' );

remove_filter( 'the_excerpt', 'wpautop' );



register_nav_menus( array(

	'dictionary-nav' => 'Dictionary Menu',

	'user-nav' => 'User Menu',

	'admin-side-nav' => 'Admin Side Menu',

	'main-nav-math' => 'The Main Menu Math',

	'math-user-nav' => 'Math User Menu',

	'lang-switcher-nav' => 'Lang Switcher Nav',

	'main-nav-math-teach' => 'The Main Menu Math Teacher',

	'math-user-nav-teach' => 'Math Teacher Menu',

	'main-nav-math-teach-home' => 'The Main Menu Math Teacher Home'

) );





//Update user online status

add_action('init', 'gearside_users_status_init');

add_action('admin_init', 'gearside_users_status_init');

function gearside_users_status_init(){

	$logged_in_users = get_transient('users_status'); //Get the active users from the transient.

	$user = wp_get_current_user(); //Get the current user's data

	//Update the user if they are not on the list, or if they have not been online in the last 900 seconds (15 minutes)

	if ( !isset($logged_in_users[$user->ID]['last']) || $logged_in_users[$user->ID]['last'] <= time()-300 ){

		$logged_in_users[$user->ID] = array(

			'id' => $user->ID,

			'username' => $user->user_login,

			'last' => time(),

		);

		set_transient('users_status', $logged_in_users, 300); //Set this transient to expire 15 minutes after it is created.

	}

}

//Check if a user has been online in the last 15 minutes

function gearside_is_user_online($id){	

	$logged_in_users = get_transient('users_status'); //Get the active users from the transient.

	

	return isset($logged_in_users[$id]['last']) && $logged_in_users[$id]['last'] > time()-300; //Return boolean if the user has been online in the last 900 seconds (15 minutes).

}

//Check when a user was last online.

function gearside_user_last_online($id){

	$logged_in_users = get_transient('users_status'); //Get the active users from the transient.

	

	//Determine if the user has ever been logged in (and return their last active date if so).

	if ( isset($logged_in_users[$id]['last']) ){

		return $logged_in_users[$id]['last'];

	} else {

		return false;

	}

}



function plugin_mce_css( $mce_css ) {

	if ( ! empty( $mce_css ) )

		$mce_css .= ',';



	$mce_css .= get_stylesheet_directory_uri() . '/library/css/mce-customize.css';



	return $mce_css;

}

add_filter( 'mce_css', 'plugin_mce_css' );

add_filter( 'mce_external_plugins', 'add_mce_placeholder_plugin' );

function add_mce_placeholder_plugin( $plugins ){

	$plugins['placeholder'] = get_stylesheet_directory_uri() . '/library/js/mce.placeholder.js';

	// You can also specify the exact path if you want:
	// $plugins[ 'placeholder' ] = '//domain.com/full/path/to/mce.placeholder.js';

	return $plugins;
}

/* DON'T DELETE THIS CLOSING TAG */ 

add_filter('wp_mail_content_type','set_html_content_type');

function set_html_content_type(){

	return 'text/html';

}

function add_ajaxurl_cdata_to_front(){ 

	?>

    <script type="text/javascript"> //<![CDATA[

        ajaxurl = '<?php echo admin_url( 'admin-ajax.php'); ?>';

    //]]> </script>

<?php }



add_action( 'wp_head', 'add_ajaxurl_cdata_to_front', 1);



add_action('show_user_profile','add_extra_profile_field');

add_action('edit_user_profile','add_extra_profile_field');



function add_extra_profile_field(){

	global $wpdb;

	

	if(isset($_GET['user_id'])){

		$id_user = $_GET['user_id'];

	}

	else $id_user = $user->ID;

	$language_type = get_user_meta($id_user,'language_type',true);

	if(!$language_type)	$language_type = 'en';



	?>

	<table class="form-table">

		<tr>

			<th scope="row">

				<label><?php _e('Language Type','iii-dictionary'); ?></label>

			</th>

			<td>

				<?php

				$langs = array(

					'en' 	=> 'English',

					'ja' 	=> '日本語',

					'ko' 	=> '한국어',

					'vi' => 'Tiếng Việt',

					'zh' 	=> '中文',

					'zh-tw' => '中國'

				);



				//$cur_lang = get_short_lang_code();

				?>

				<select name="language_type" class="form-control language_type">

					<?php foreach($langs as $code => $lang) : ?>

						<option value="<?php echo $code; ?>"<?php echo $language_type == $code ? ' selected' : '' ?>><?php echo $lang ?></option>

					<?php endforeach ?>

				</select>

			</td>

		</tr>

	</table>

	<?php

}

add_action('user_new_form','add_extra_profile_field1');

function add_extra_profile_field1(){

	?>

	<table class="form-table">

    	<tr >

            <th scope="row"><label><?php _e('Language Type','iii-dictionary'); ?></label></th>

            <td>

            	<?php

				$langs = array(

					'en' 	=> 'English',

					'ja' 	=> '日本語',

					'ko' 	=> '한국어',

					'vi' => 'Tiếng Việt',

					'zh' 	=> '中文',

					'zh-tw' => '中國'

				);



				//$cur_lang = get_short_lang_code();

				?>

				<select name="language_type" class="form-control language_type">

					<?php foreach($langs as $code => $lang) : ?>

						<option value="<?php echo $code; ?>"><?php echo $lang ?></option>

					<?php endforeach ?>

				</select>

            </td>

        </tr>

    </table>

	<?php

}



add_action('edit_user_profile', 'save_extra_profile_fields');

add_action('profile_update', 'save_extra_profile_fields');

function save_extra_profile_fields(){

	$language_type = $_POST['language_type'];

	$user_id = $_POST['user_id'];

	update_user_meta( $user_id, 'language_type', $language_type );

}



add_action('user_register','save_extra_profile_fields1');

function save_extra_profile_fields1($user_id){

	$language_type = $_POST['language_type'];

	update_user_meta( $user_id, 'language_type', $language_type );

}



?>

