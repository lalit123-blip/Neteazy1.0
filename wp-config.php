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
define( 'DB_NAME', 'atestwj9_wp48' );

/** MySQL database username */
define( 'DB_USER', 'atestwj9_wp48' );

/** MySQL database password */
define( 'DB_PASSWORD', '()7SLcN36p' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

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
define( 'AUTH_KEY',         'ydlbmfhiclfjqeazyo2cwx7rir5ipuddlbaf6u0nliuegftocwxo2hmk4gxo9z43' );
define( 'SECURE_AUTH_KEY',  '9jntakqzxextibcihudci9vucsu5kser3tkwmipdansrpt4awq07xwcnjozs4lhk' );
define( 'LOGGED_IN_KEY',    'iiugu5zzududtnt8kqiphynrqcxjtjqkvxcqpgafpw9dgub8drs8jkah4lgxpegy' );
define( 'NONCE_KEY',        'h2bapxnsy5j3elef2piyuxvmtyxqyny214hbs9bsgxfvlnnkvxilepvmbcuexapl' );
define( 'AUTH_SALT',        'kp9mch9j7sadnzjpkvtr8pvqsnvdalztdiphu9h5fwi9jhp8bzvamxslxnjxukrk' );
define( 'SECURE_AUTH_SALT', 'fgr4mwegal3c5j2hstypgjc81izyqojskqrxamsvscmn5whdzemwoiiym4hiupcy' );
define( 'LOGGED_IN_SALT',   'ybt7yzlocpegxhoq1eiyumfahlgjam540oifkjn3dtpeusox3nk7on7wx1qa0ycf' );
define( 'NONCE_SALT',       'z8n9dlhpyx6d0q9isbmdixhk3ua8y3gh7hwiydopmtycdt1xso5m3vr8qh2r1qfq' );

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wpeq_';

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
