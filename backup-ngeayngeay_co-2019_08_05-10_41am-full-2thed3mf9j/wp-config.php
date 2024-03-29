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
define('WP_CACHE', true);
define( 'WPCACHEHOME', '/home8/ngeaynge/public_html/wp-content/plugins/wp-super-cache/' );
define( 'DB_NAME', 'ngeaynge_website' );

/** MySQL database username */
define( 'DB_USER', 'ngeaynge_sql' );

/** MySQL database password */
define( 'DB_PASSWORD', 'ngeay2019#168' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

set_time_limit(600);
/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '[!ds:fk^t9.IbBm$NQAOUmC4).vg#OF?jUc(lg#9xqUso:/Yfl*5O.?p=iH&Dtfk' );
define( 'SECURE_AUTH_KEY',  '$Ht;<,jWuF@HDb!B_N6Ru(Ql%Xf^](69 AdZs;XGhP0z,7%3aF_D:iwV9Y9YgPvw' );
define( 'LOGGED_IN_KEY',    'C][wG2N^$wyZ%x8T_E2;=;H`eDQTM!xAydFN9K|4WbbM>(e>Yzw|3/[;`@#jbzQG' );
define( 'NONCE_KEY',        'e]jwEa$C,i`){8iz!<VmJ!Y@QuNhcshNcD%y[!)bG{6,fqk6>z<==N0@Y,0IHOZT' );
define( 'AUTH_SALT',        'pNRSQV,^.[c|cWis:!iKtGY%3z9u[)-A9w31} 3)*siU8w0Y^8>`c9BnHxDs$=C>' );
define( 'SECURE_AUTH_SALT', '@Uv3]I,^CovK$c,b,5J^^aJZVnVY[)T5FZ-LdURbu$.bScv~RQfH[$k}^LxU7Jj@' );
define( 'LOGGED_IN_SALT',   '+iV~ZV6nf$jS,?jXp;/zyPB?X#ki6lEOL-f14B3OxvLI!R(2/X0#Sy,(/Ryptq&<' );
define( 'NONCE_SALT',       'CPN&n3-CXj~FcdMez` Bav6iSb[2?1pqt$8bVU<s<AeeBb,A~8Pjw@QVdFPrm@od' );

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'nge_';

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
define( 'WP_DEBUG', false );

define( 'AUTOSAVE_INTERVAL', 300 );
define( 'WP_POST_REVISIONS', 5 );
define( 'EMPTY_TRASH_DAYS', 7 );
define( 'WP_CRON_LOCK_TIMEOUT', 120 );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once( ABSPATH . 'wp-settings.php' );
