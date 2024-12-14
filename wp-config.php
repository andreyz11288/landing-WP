<?php
 // Added by SpeedyCache

/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'andreyz11288_forworkf_wp987' );

/** Database username */
define( 'DB_USER', 'forworkf_wp987' );

/** Database password */
define( 'DB_PASSWORD', 'VS2a1.Xp.5' );

/** Database hostname */
define( 'DB_HOST', 'localhost:3306' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'wj8sp05cige5gp9hsxwvremmfyd553ynlfec1nfstm7owbaxowjsellae4q3cxmu' );
define( 'SECURE_AUTH_KEY',  '6yeikssfleor5666y44p0eji8wmnmh7pqdrm4egclh5nzltawhbe4whzed5iavnp' );
define( 'LOGGED_IN_KEY',    'dqjnnpr9jot0hvdcsjvgkcuieea8rcyb9rydexwvtmggghiy7rweavj4leappljg' );
define( 'NONCE_KEY',        '6ycwdnqrz2xau9rkg6xruwv9ka5ojhbnrdalwzhc4y7g6ymmvlg4xosyytgtiqkl' );
define( 'AUTH_SALT',        'nwhmunm5hnqm8iw5pjxpmn5io1pfucb5k2rovf7f5gwwznkjdifhscoatnyb3zgg' );
define( 'SECURE_AUTH_SALT', 'sgktu9xpiitrcm1he3ry3bltsyg6iswuwkb7sjfya0tlkynmmjllmixd6m7gl47q' );
define( 'LOGGED_IN_SALT',   '67lcfoykkxq8ww0y17ga81pkxggcjuh05xaumehxdb5xl9w4lto5f5brfvmxauaq' );
define( 'NONCE_SALT',       'npp43razbyc8bpntuvpnsl1nizxhvhm4gefepfpozjiw53xuubjlswgfow1jjxxj' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wpil_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
