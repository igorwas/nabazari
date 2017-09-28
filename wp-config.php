<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'ihorkit_v2');

/** MySQL database username */
define('DB_USER', 'ihorkit_v2');

/** MySQL database password */
define('DB_PASSWORD', '834mb4xc');

/** MySQL hostname */
define('DB_HOST', 'ihorkit.mysql.ukraine.com.ua');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '9175i^FA@TQ@k85Fv3WCEjmuByZG%#W7^U8DndSM&E5YXRp6nF)nP814na9Lq2qr');
define('SECURE_AUTH_KEY',  '@MP2X&JvKsuYn*Na9Qk@DBHD9V^FC1C)OWdASmtPt^^oEVlj3gLQMFd65hDoO10e');
define('LOGGED_IN_KEY',    ')13wdfF*O(vAGLkP8xQdr@a&qEgfWX^540G9T5)8G^E0ThVzwB5xaa416gfg1KFe');
define('NONCE_KEY',        'dl*@X!FTyMDe%@%Gz8l(fnBxx!Hpwe@kVg&CD0cK5AV8@cWfPytMWskn@jvZcRzd');
define('AUTH_SALT',        '2Zx70ApsRAu9jWS3QnCM3OtsouX8*^vdTaVCLeGtw^@ht!A3tiP2FAXfRoxn@0vM');
define('SECURE_AUTH_SALT', 'P^04Tj2ipASehqIQBvHbar9x3CkT4gt@aB#MG2^*12uKS9(q(WW3z@9NlDrdH6Hw');
define('LOGGED_IN_SALT',   '9K#0i8xgPtr8O*GNmG353yTB(YYNt!e)zlCpuYghKbOuI()FwO%NE)efAdNQgcFd');
define('NONCE_SALT',       'QrJpaFzOY^Zo9I(w@rAKiWH(xYN4#EIt)pT289xDbgGfdQJy@0ZG^dXOZR(@&&Cg');
/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');

define( 'WP_ALLOW_MULTISITE', true );

define ('FS_METHOD', 'direct');

define( 'WPLANG', 'uk_UA' );
?>