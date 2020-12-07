<?php

add_filter( 'authenticate', 'ik_check_user_state', 40, 3);
function ik_check_user_state( $user, $username, $password )
{
	if ( is_a($user, 'WP_User') ) 
	{
		if(!get_user_meta($user->ID, 'ik_disable_user', true)) {

			$error = new WP_Error();

			ik_enqueue_messages(__('This user is disabled', 'iii-dictionary'), 'error');

			return $error;
		}

		return $user; 
	}
}

add_action( 'user_register', 'ik_set_user_default_state', 90 );
function ik_set_user_default_state($user_id)
{	
	update_user_meta($user_id, 'ik_disable_user', 1);
}

function ik_toggle_block_user($userid)
{
	update_user_meta($userid, 'ik_disable_user', !get_user_meta($userid, 'ik_disable_user', true));
}

function is_user_enable($userid)
{
	return get_user_meta($userid, 'ik_disable_user', true);
}
