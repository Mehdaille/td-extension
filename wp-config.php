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
define( 'DB_NAME', 'td-extension' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

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
define( 'AUTH_KEY',         ',;fKHKe0$[[Or/~MNj5d-?^.#|G +lSG=vIFNQH/d@++5vu{A_$}*#]O(,+o|3YM' );
define( 'SECURE_AUTH_KEY',  '*n&@uyGNm|~oq&5iB>.VR|<vpMn5y=mQ3|Qt;uaf#wF8sIJ*{ve6[5vnYiOR}%l{' );
define( 'LOGGED_IN_KEY',    '%-vR)eRqmth8A-Ejp#_B3@Y7lohD,cHCK7~}Q4?<V.r:]iVS=%O7.:<[V(fT|EDb' );
define( 'NONCE_KEY',        '5rseL}p:bqALWvb;_#m=#FHl,|?WhJQs.Q8Te1SxL,o_yBm*rtC @yFdZRo9wiGK' );
define( 'AUTH_SALT',        'hX32St:[@n8qQ8YJ<m:#]>O?hB[Lg#:+&[R[LRYPg>|CPzQqeiC+Q>n:kHCgr6f:' );
define( 'SECURE_AUTH_SALT', 'vrJ}8F9,b]y{h.paa(5%a8m8{ChJ{GS_=U+0BtI!3vPy!*aAP(?^X5(~)Ns2{=t`' );
define( 'LOGGED_IN_SALT',   '5a{n5P%oJ xRb+B0>wE|`fzbSi}7#:rpkJ5kt~gu!TB/c;@d&5|E1OaVs0k1%/hV' );
define( 'NONCE_SALT',       'b!:G{l%trPY@~yH.LF`Wl:U$S`)X0rPb-j*Pw|KIU!5T(>Vg!HM3q2:O&?Us3xgy' );

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
