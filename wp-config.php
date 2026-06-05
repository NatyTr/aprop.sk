<?php
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
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'j81p676svhcf2' );
/** MySQL database username */
define( 'DB_USER', 'j81p676svhcf2' );
/** MySQL database password */
define( 'DB_PASSWORD', 'j81p676svhcfA' );
/** MySQL hostname */
define( 'DB_HOST', 'mariadb103.websupport.sk:3313' );
/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );
/** The Database Collate type. Don't change this if in doubt. */
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
define( 'AUTH_KEY',          'ho[^?)K@R8;mU`tZdHQ2<aj{XFS<K2D8nB#&rATt gKF/c^qLQHyT7=u.Ye8`,Cj' );
define( 'SECURE_AUTH_KEY',   ':5!~@tYMgnq*$$0.!pJG,S1Tg[+p0krhz7BcA|He[`+{`NX.bM8=4DAt6h:j^*Bn' );
define( 'LOGGED_IN_KEY',     'V!||zQ?j?^[[(=VZbtYf(Y,G&gB3&$Nn%?VEdR32j|v:D^![ejiyn}I#:w]P{,Pl' );
define( 'NONCE_KEY',         'U zgK%J rNW}K81:1^.{pEqFEpM-)zrzh~T4<2wE(0_;RhnCtyX]~jOhN BD4/%O' );
define( 'AUTH_SALT',         '.Enh2u5mO5u5AY+T IhP1heLF+I|Ex)H{y5%cf3,2Jk/LIOlN$5HlC=(WmpKO:Y.' );
define( 'SECURE_AUTH_SALT',  '^HrVGnZFNQXS,Cl8[9McAEAwoE}#h?pb96cm64N-XG,ZahhFY]ONx@|sQsn p!Wq' );
define( 'LOGGED_IN_SALT',    '?3ZFLsWXNyI#!=}~rj :t&PxW~HN~x{,&A?bvHhD,OnTZ{ZIxMI 5t0&O Q;4<]J' );
define( 'NONCE_SALT',        'N2)2-m_dH?.sd:@l!zz`/!xv9ZJbF[[^3lx;+:Gw6Z .FNoq&(u[h $HW-^5WT2s' );
define( 'WP_CACHE_KEY_SALT', '*te*G8xHG4pfCDl3O]e|0su*7x|~qxorz]|raC|asXKQf (a:z_C9`;;CL}$Ub!F' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'szbnbs';


/* Add any custom values between this line and the "stop editing" line. */



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
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}

define( 'WP_DEBUG_LOG', false );
define( 'WP_DEBUG_DISPLAY', false );

define( 'WP_ENVIRONMENT_TYPE', 'production' );
define( 'DISABLE_WP_CRON', true );   
define( 'DUPLICATOR_AUTH_KEY', '7q:Qo<+vt)5:)Ci~^),I<]7c#GUThF6vDhS>`}X<l3KX$.4#c@zuR+H{[*OqJnU;' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
