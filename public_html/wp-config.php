<?php
define( 'WP_CACHE', true );

//Begin Really Simple SSL session cookie settings
@ini_set('session.cookie_httponly', true);
@ini_set('session.cookie_secure', true);
@ini_set('session.use_only_cookies', true);
//END Really Simple SSL cookie settings
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */
// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'kbgjgazo_wp1' );
/** Database username */
define( 'DB_USER', 'kbgjgazo_wp1' );
/** Database password */
define( 'DB_PASSWORD', 'S.Gm6eaGE5vrt82BKO227' );
/** Database hostname */
define( 'DB_HOST', 'localhost' );
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
define('AUTH_KEY',         'Hx2mHIov8XpVErYtb4Qp09QpERQLh76p1xpUdt1HM0vpCIIWAbJAFCzWbf6SIkRU');
define('SECURE_AUTH_KEY',  '1fXihNOFckdY6Uhy1j2aYNA4e5jeHXevqSZsL0123F6VuyLBSk2cj7RU8b7F1CM5');
define('LOGGED_IN_KEY',    'RVkZvUO9SR7VOijSt1cAmYo9uonhfawv25aIIpwyVhGNChIFFgxl1GZiGz7myhsV');
define('NONCE_KEY',        'JFz7yrWdrDcLjlhrhwIozoMC5akHP3bJ1WSyVKy4lhPb1xpklKoU6FKpWH6cCZEs');
define('AUTH_SALT',        '3QtNXNxy0iyQ8BktNHAhew5oTWl2Ii4fj7ToTmbdWoycI6G6lDsb4NXTpVSO7MZc');
define('SECURE_AUTH_SALT', 'kXzOCmV1Ayb9dwAXNE5JL90Zu04hBmFGYBp7cz1SiAVjKIm0YC77y5yQmJJPMA37');
define('LOGGED_IN_SALT',   'Mo0w5fKuGAy8nfQH8ziNS8bl3TaKP5BnVlcOYoJAU97hnwoxSYWZgl7i5UjRqeN9');
define('NONCE_SALT',       'VKm4T91bdIXSyjJt6galCmLSNhS8bibjj9L6nM6ld0I2sRKyZ2D4e5DJnDqClkdS');
/**
 * Other customizations.
 */
define('WP_TEMP_DIR',dirname(__FILE__).'/wp-content/uploads');
/**#@-*/
/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
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
 * @link https://wordpress.org/documentation/article/debugging-in-wordpress/
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