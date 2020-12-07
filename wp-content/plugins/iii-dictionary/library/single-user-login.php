<?php
/*
 * limit user login to one at a time.
 */

// store login session

add_filter( 'authenticate', 'dict_create_user_session', 100, 3);
function dict_create_user_session( $user, $username, $password )
{
	$route = get_route();
	if ( is_a($user, 'WP_User') || isset($route[1]) && $route[1] != 'logged' ) 
	{
		global $wpdb, $is_admin;

		$client = $is_admin ? 1 : 0;

		$wpdb->insert( 
			$wpdb->prefix . 'dict_user_session', 
			array( 
				'userid' => $user->data->ID, 
				'session_id' => session_id(),
				'time' => time(),
				'client' => $client
			)
		);

		return $user; 
	}
}

// delete login session
add_action('wp_logout','dict_delete_user_session');
function dict_delete_user_session() {
	global $wpdb;
	$user = wp_get_current_user();

	$query = 'DELETE FROM ' . $wpdb->prefix . 'dict_user_session 
				WHERE userid = %d AND session_id = %s';
	$result = $wpdb->query( $wpdb->prepare(
			$query, 
			array(
				$user->ID,
				session_id()
			) 
		) );
}

// check login session
//add_action('init','dict_single_user_login_check');
function dict_single_user_login_check()
{
	if(is_user_logged_in())
	{
		global $wpdb;
		$user = wp_get_current_user();
		
		// check if a user have more than one session
		$results = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . 'dict_user_session WHERE userid = ' . $user->ID);

		if(!empty($results) && count($results) > 1)
		{
			$maxtime = $wpdb->get_col('SELECT MAX(time) FROM ' . $wpdb->prefix . 'dict_user_session WHERE userid = ' . $user->ID);

			$result = $wpdb->query(
				$wpdb->prepare(
					'DELETE FROM ' . $wpdb->prefix . 'dict_user_session WHERE userid = %d AND time <> %d', 
					array(
						$user->ID,
						$maxtime[0]
					)
				)
			);
		}

		// update last activity
		$wpdb->update(
			$wpdb->prefix . 'dict_user_session',
			array('last_activity' => time()),
			array('userid' => $user->ID)
		);

		// logout the user if their session is not in the database
		$result = $wpdb->query(
			$wpdb->prepare(
				'SELECT * FROM ' . $wpdb->prefix . 'dict_user_session WHERE session_id = %s',
				array(session_id())
			)
		);

		if(!$result)
		{
			wp_logout();
		}
	}
}