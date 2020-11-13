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
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'grubster_wordpress' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', '' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'rXaZ50C4(]BRO]]h6l_OOQHIPo~8w,Wp3Q0iOCpZOv&X`nWvqA.CqW2i6F<-,1i`' );
define( 'SECURE_AUTH_KEY',  'SkH2}k5t6e&2^H4bqE=V3RlF3?oHK0}9:=%idqUWHw.fsLqg@t_?x&Vk<[k5&kK8' );
define( 'LOGGED_IN_KEY',    '$t:*u[b$61-Rj ByFZihd^ AG$bOXWg[P&6Y.lo:]Xv7} ^XX4PPcq!EwDh*ZOM]' );
define( 'NONCE_KEY',        'FX0 D5)UNotFivf22~qn]7k5==UV;}wb-XRo<mB0ceA,1Xt*@jQ wUl<dbCDYVQ^' );
define( 'AUTH_SALT',        '<{d/gH0K>d-Yjd%JaJ6Ok6^2)rQQ8oZ=A:%J^/t^!Bz?B*X(2[I$L$Q_]58F[KFi' );
define( 'SECURE_AUTH_SALT', '?x/WM&cbqRQQsj1nj^b7OWNH$4meLL>Kl(8pB+a.d(qOs,g|PijcPdyyVOL;#sl+' );
define( 'LOGGED_IN_SALT',   'F|?v+;M<mv](z;.1t,H2BA&(HW)2Sa8.agI=Wa>OUl`x_PHTC E/G {_-a/VBi8x' );
define( 'NONCE_SALT',       '`tOsIQnwTd{91N>k907M}lP%Pt--eFU=f)?802OhmigVpBS*C Cv4x/Q20(=+D&M' );

/**#@-*/

/**
 * WordPress Database Table prefix.
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
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
