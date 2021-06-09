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
define( 'DB_NAME', 'demo-tmdt' );

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
define( 'AUTH_KEY',         '-s{Kh$AgN(nfL>~G`YA;m;U]8SvH<LoBN+fo&*k4)@Z.>Vql$9;9Y:ht.:p.t3MC' );
define( 'SECURE_AUTH_KEY',  'PjACzQ<3^Blgw]! #+|b.Y|N/?DFPE%xA0urCiXK.z&l[~JS:7;y9wYMQ4sa`R&:' );
define( 'LOGGED_IN_KEY',    'J6&U>z1tITi@ ){/]guncZ;0%?y~sHr=2cX:xtq5O`].nai*_R5$]czPNR+vTOuk' );
define( 'NONCE_KEY',        'Tb):2V6#h)aUrUldYj[swz%V5bM11Ar4<gYW6?@&y`.F]8Em(_DyX<q0.`TFs:>6' );
define( 'AUTH_SALT',        '$GZ6tu>qJ$#,C@}ha}C6nqsqcxvy].WfGl7j,D+G8=L$D-u=ee[743D,&1#lxQ6w' );
define( 'SECURE_AUTH_SALT', '[Aha.l2V0H:gO{<j~}M(gZ90sVl7?f39@QWb&S^k2.LbK`dDb1,cVeuX3R-tn<Z|' );
define( 'LOGGED_IN_SALT',   '2,vUJ]dmVdcba`%]TF}9HFvzMP-E9zLXhH+::>8MG(HM@Nq#9CcJ:tu]|7%v46SP' );
define( 'NONCE_SALT',       'FFsqvO^aK6r3x9duC<I6`85~o=8$.Fw)I;C2sjR.DU,jdcoB,Pdp@ao/(^|:`LzT' );

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
