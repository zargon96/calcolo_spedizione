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
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'costi_spedizione' );

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
define( 'AUTH_KEY',         'rf48<z(Y4 ]z!6MH/!vy|d N<<uEeT5k9m!0NfbtS_l CJQ2E,|D^Z;J?;EC]Ck`' );
define( 'SECURE_AUTH_KEY',  'Ep)#a}A&C|Nk#B,oT6YT``mCAaK%|0z!!e]ef=ybRKWB{< mQXGZ`#il*@WXuwrC' );
define( 'LOGGED_IN_KEY',    'U%mRv9_RQSUz5R3Zi[&oIcc*;jY{N.1<)1WX8mZLtMs4FuS-q$jB fN`?]?*>w!*' );
define( 'NONCE_KEY',        'Q/?^FEpc>J3ci@n:)g!(/Pp`}I^f|Mq!%P_cB <9awDuoc8e:NFx&=2/Qc>VK1Oy' );
define( 'AUTH_SALT',        'C;G{8-x narxap;Z:Djaw&1H^Z,D*f7&H{GSAi7>=xom#KJ.fuhXtuj`z?o<ffE;' );
define( 'SECURE_AUTH_SALT', 'O88R$ h7ZlRoVpnTfgb!Iq19syFJ ifQ=2%*>c/4KQUQ%D|BzGnoQmdx&)=NlP_t' );
define( 'LOGGED_IN_SALT',   'aT?;2Jv?4]$)w^,_}N1:,SJT(uxz}IXn)vUt-ppO.=:a]RV[+Q=85Z%G1!xOonfC' );
define( 'NONCE_SALT',       ')W.a}iLM0:Ng78o}h(r}Wv:>S/L|;/kGin~)]dr_I{@pEY}-HADo0?:YQ5rsv#%f' );

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
// define('WP_DEBUG_LOG', true);
// define('WP_DEBUG_DISPLAY', false);

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
