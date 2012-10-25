<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */
// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'atvirasalus');
/** MySQL database username */
define('DB_USER', 'atvirasalus');
/** MySQL database password */
define('DB_PASSWORD', 'sxFU9S5atLC4FrXx');
/** MySQL hostname */
define('DB_HOST', 'localhost');
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
define('AUTH_KEY',         'lk<m6{0kGCRqc9-g1lAD`+x[z18yOa_F(H|ql|H!$8d++ohNRm+S{)5Q*8R%@Qbz');
define('SECURE_AUTH_KEY',  'ss3N!8t,++7=q@&n+ha%(.bqXBR +5j{mh+dh>XUvz:|5.6?:&FT{.1.+(D,}GnK');
define('LOGGED_IN_KEY',    '+ OeL<_E-~1F/%)?@t}fEj;6o*h-fX0]7EEthU>Onx[Ht=+,-IR/hLs$Y1f+6yW*');
define('NONCE_KEY',        '}6b`6`_iUzG#LUCJMO0OU~1T$|)-QeLQ} )|zQ7iK&w?I]UjH%^b|GD_:%HD-<c5');
define('AUTH_SALT',        '|1:,d&jd+~N,qrl5A)N] [Nh`}f1{+zA^xVGJ5W[50?`UwAkc0(+sU~XnA$0fC |');
define('SECURE_AUTH_SALT', '-)/|n(P>+(CXXoE5UD4UL=.YHrgjj{|ME9ViF975g!H.t8Su]2or<*ge,]GWrjJ|');
define('LOGGED_IN_SALT',   'IPBT3DHg5cK?04u(lpI)+)nH65Bhl;ER78PQum[1V8oaugqX+RPwEo(`y!T|)e/n');
define('NONCE_SALT',       'z[j4k~674l<u5 =[v]C,q!&KngnNnL})n)SxsQ*?|pZ`K~:lS*:aMQ_j0[^s3V&l');
/**#@-*/
/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';
/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define ('WPLANG', 'lt_LT');
/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);
/* That's all, stop editing! Happy blogging. */
/** multisite */
define('WP_ALLOW_MULTISITE', true);
define( 'MULTISITE', true );
define( 'SUBDOMAIN_INSTALL', false );
$base = '/blog/';
define( 'DOMAIN_CURRENT_SITE', 'atvirasalus.lt' );
define( 'PATH_CURRENT_SITE', '/blog/' );
define( 'SITE_ID_CURRENT_SITE', 1 );
define( 'BLOG_ID_CURRENT_SITE', 1 );
/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');
/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
define('FS_METHOD', 'direct');
