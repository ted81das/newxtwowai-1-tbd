<?php
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
define( 'DB_NAME', 'wowmattic2' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

define('DISALLOW_FILE_EDIT', true);

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

if ( !defined('WP_CLI') ) {
    define( 'WP_SITEURL', $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] );
    define( 'WP_HOME',    $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] );
}



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
define( 'AUTH_KEY',         'QpikeSyQDbZ5sCSUzLkGdUBEyPlaY5UqsRphCo4Tr5K5wVnluu9rSwAGMxwgPeQu' );
define( 'SECURE_AUTH_KEY',  'LJz7vCYBHG23udTthsHxSx8zB3dzxJ27E87DoLxz2S9kFeu34vrLjTVmd8pbTBbz' );
define( 'LOGGED_IN_KEY',    'aMnLo1TZTGLtbjb4LDeudbcOZ5CHRatbeAxJR7VjxYbr9iAPBWmvasfnJLnsYqkA' );
define( 'NONCE_KEY',        'yxLCDsXUK2GnMbHYeqTU6VwzxkdMdSBKC8hPzziO00ofv6PkeHoXhgVmQPyelEt5' );
define( 'AUTH_SALT',        'eMe6uJCI6h9Ei3kwKSxEhwa4APP5FpKPJ2RzQtpnMxhdEKYUi8fHClNyaMj7Sn6d' );
define( 'SECURE_AUTH_SALT', 'OwFm94JSgyp8udfbU8xh2u2X9U7CLlSJBsOLVzWAoQCsx7u8yYa1ckAFKqdCmWxu' );
define( 'LOGGED_IN_SALT',   '9Kof2U6LlXhPBtkWNPS5Eg8Ff5bIkPeYGlCVkKkvHQg6GRsMoO2RauvDOIXAmdN3' );
define( 'NONCE_SALT',       'c3O5amOzK6AnFXHUzczUYhucYW92eM6aa9GTE37fvoKsuEbsIMbHN8IKMwNw5TMV' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'wp_';

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
