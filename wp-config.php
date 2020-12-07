<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, and ABSPATH. You can find more information by visiting
 * {@link http://codex.wordpress.org/Editing_wp-config.php Editing wp-config.php}
 * Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'iktutor');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', '');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

define('COOKIE_DOMAIN', '.ikteach.local');
define('COOKIEPATH', '');

define('WP_SITEURL', 'http://ikteach.local/');
define('WP_HOME', 'http://ikteach.local/');

//define('FORCE_SSL_ADMIN', true);
// define('IK_TEST_SERVER', 1);

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'Y6:slyw9w_2C-8$YWi+~g;9I,B8IONx+]y568x=Aeg]5yg8I3)N+Ic^zfPCBsUV:');
define('SECURE_AUTH_KEY',  '>@wc@koS-Tl{/VJn40pu3s+Y 7uFAXcPQVU:9zA^n!d?+V-5Ic-$2,-5T^3R?83n');
define('LOGGED_IN_KEY',    'm8yHe%p$|=C-0=4MK4A.BtyDOOUj;&[EU{:X$nBta{3|KU|8[-BwWg!QL!E|r=N=');
define('NONCE_KEY',        'zQ=#{{zdJuxiG~ g~?(@m#o-rCv;uJotCmvj7X5~OV8.)gO+hzQg7^N(NC|.rST3');
define('AUTH_SALT',        '-a:vpt1h;a!WoTG,xaX+cx*p+kTC58+#HH|+[/v9?dg&@uh]<H!f4K66)Mm7N b*');
define('SECURE_AUTH_SALT', '+(XPd I&s1=mQGr5fUb}N4Y9-+%TDfG%1u_ge79cT0;5~S!|)LG+/hkbg+>M`#_T');
define('LOGGED_IN_SALT',   'T|ma@>QsB),j?mJO2stl-4K<64;eBUlhs> 3sGqD6cX$Q&7c*Kw]j>=Nf^|6Nu -');
define('NONCE_SALT',       'zznZ-,EJ)GrsG{);9WO}Q|$}0nqImLL$jI*PYq/`?BWK>r/#U:~P}cIS-BB^1of,');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');