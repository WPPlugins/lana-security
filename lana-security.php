<?php
/**
 * Plugin Name: Lana Security
 * Plugin URI: http://wp.lanaprojekt.hu/blog/wordpress-plugins/lana-security/
 * Description: Simple and easy to use security plugin.
 * Version: 1.0.6
 * Author: Lana Design
 * Author URI: http://wp.lanaprojekt.hu/blog/
 */

defined( 'ABSPATH' ) or die();
define( 'LANA_SECURITY_VERSION', '1.0.6' );

/**
 * Language
 * load
 */
load_plugin_textdomain( 'lana-security', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

/**
 * Plugin Settings link
 *
 * @param $links
 *
 * @return mixed
 */
function lana_security_plugin_settings_link( $links ) {
	$settings_link = '<a href="admin.php?page=lana-security-settings">' . __( 'Settings', 'lana-security' ) . '</a>';
	array_unshift( $links, $settings_link );

	return $links;
}

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'lana_security_plugin_settings_link' );

/**
 * Install Lana Security
 * - create security log table
 * - create login log table
 */
function lana_security_install() {
	lana_security_create_security_logs_table();
	lana_security_create_login_logs_table();
}

register_activation_hook( __FILE__, 'lana_security_install' );

/**
 * Activate Lana Security
 * add security log
 */
function lana_security_activate_log() {
	$user = wp_get_current_user();
	lana_security_add_security_log_to_wpdb( $user->ID, __( 'Lana Security plugin activated', 'lana-security' ) );
}

register_activation_hook( __FILE__, 'lana_security_activate_log' );

/**
 * Deactivate Lana Security
 * add security log
 */
function lana_security_deactivate_log() {
	$user = wp_get_current_user();
	lana_security_add_security_log_to_wpdb( $user->ID, __( 'Lana Security plugin deactivated', 'lana-security' ) );
}

register_activation_hook( __FILE__, 'lana_security_deactivate_log' );

/**
 * Create security logs table
 */
function lana_security_create_security_logs_table() {
	global $wpdb;

	$charset_collate = $wpdb->get_charset_collate();
	$table_name      = $wpdb->prefix . 'lana_security_logs';

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

	$sql = "CREATE TABLE " . $table_name . " (
	  id bigint(20) NOT NULL auto_increment,
	  user_id bigint(20) DEFAULT NULL,
	  username varchar(255) DEFAULT NULL,
	  user_ip varchar(255) NOT NULL,
	  user_agent varchar(255) NOT NULL,
	  comment text NOT NULL,
	  date datetime DEFAULT NULL,
	  PRIMARY KEY (id)
	) " . $charset_collate . ";";

	dbDelta( $sql );
}

/**
 * Create login logs table
 */
function lana_security_create_login_logs_table() {
	global $wpdb;

	$charset_collate = $wpdb->get_charset_collate();
	$table_name      = $wpdb->prefix . 'lana_security_login_logs';

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

	$sql = "CREATE TABLE " . $table_name . " (
	  id bigint(20) NOT NULL auto_increment,
	  username varchar(255) DEFAULT NULL,
	  status int(1) NOT NULL,
	  comment text NOT NULL,
	  user_ip varchar(255) NOT NULL,
	  user_agent varchar(255) NOT NULL,
	  date datetime DEFAULT NULL,
	  PRIMARY KEY (id)
	) " . $charset_collate . ";";

	dbDelta( $sql );
}

/**
 * Lana Security
 * session start
 */
function lana_security_register_session() {
	if ( ! session_id() ) {
		session_start();
	}
}

/**
 * Add Lana Security
 * custom wp roles
 */
function lana_security_custom_wp_roles() {
	global $wp_roles;

	if ( class_exists( 'WP_Roles' ) && ! isset( $wp_roles ) ) {
		$wp_roles = new WP_Roles();
	}

	if ( is_object( $wp_roles ) ) {
		$wp_roles->add_cap( 'administrator', 'manage_lana_security_logs' );
		$wp_roles->add_cap( 'administrator', 'manage_lana_security_login_logs' );
	}
}

add_action( 'init', 'lana_security_custom_wp_roles' );

/**
 * Login styles
 */
function lana_security_login_styles() {
	wp_register_style( 'lana-security-login', plugin_dir_url( __FILE__ ) . '/assets/css/login.css', array(), LANA_SECURITY_VERSION );
	wp_enqueue_style( 'lana-security-login' );
}

add_action( 'login_enqueue_scripts', 'lana_security_login_styles' );

/**
 * Lana Security
 * Load admin styles
 */
function lana_security_admin_styles() {

	/** load admin css */
	wp_register_style( 'lana-security-admin', plugin_dir_url( __FILE__ ) . '/assets/css/lana-security-admin.css', array(), LANA_SECURITY_VERSION );
	wp_enqueue_style( 'lana-security-admin' );
}

add_action( 'admin_enqueue_scripts', 'lana_security_admin_styles' );

/**
 * Lana Security
 * add admin menus
 */
function lana_security_admin_menu() {

	/** Lana Security page */
	add_menu_page( __( 'Lana Security', 'lana-security' ), __( 'Lana Security', 'lana-security' ), 'manage_options', 'lana-security.php', 'lana_security_dashboard', 'dashicons-shield-alt', 81 );

	/** Security Logs page */
	add_submenu_page( 'lana-security.php', __( 'Security Logs', 'lana-security' ), __( 'Security Logs', 'lana-security' ), 'manage_lana_security_logs', 'lana-security-logs', 'lana_security_logs' );

	/** Login Logs page */
	add_submenu_page( 'lana-security.php', __( 'Login Logs', 'lana-security' ), __( 'Login Logs', 'lana-security' ), 'manage_lana_security_login_logs', 'lana-security-login-logs', 'lana_security_login_logs' );

	/** Settings page */
	add_submenu_page( 'lana-security.php', __( 'Settings', 'lana-security' ), __( 'Settings', 'lana-security' ), 'manage_options', 'lana-security-settings', 'lana_security_settings' );

	/** call register settings function */
	add_action( 'admin_init', 'lana_security_register_settings' );
}

add_action( 'admin_menu', 'lana_security_admin_menu', 12 );

/**
 * Register settings
 */
function lana_security_register_settings() {

	global $lana_security_settings;

	register_setting( 'lana-security-settings-group', 'lana_security_encrypt_version' );
	register_setting( 'lana-security-settings-group', 'lana_security_insecure_files' );
	register_setting( 'lana-security-settings-group', 'lana_security_login_captcha' );
	register_setting( 'lana-security-settings-group', 'lana_security_logs' );
	register_setting( 'lana-security-settings-group', 'lana_security_login_logs' );

	$lana_security_settings = array(
		'lana_security_encrypt_version' => array(
			'option'      => get_option( 'lana_security_encrypt_version', false ),
			'label'       => __( 'Encrypt Version', 'lana-security' ),
			'description' => __( 'Encrypt WordPress version in frontend scripts and styles, and remove generator', 'lana-security' )
		),
		'lana_security_insecure_files'  => array(
			'option'      => get_option( 'lana_security_insecure_files', false ),
			'label'       => __( 'Insecure Files', 'lana-security' ),
			'description' => __( 'Block insecure files (readme.html, license.txt) with htaccess', 'lana-security' )
		),
		'lana_security_login_captcha'   => array(
			'option'      => get_option( 'lana_security_login_captcha', false ),
			'label'       => __( 'Login Captcha', 'lana-security' ),
			'description' => __( 'Add simple number captcha in WordPress login form', 'lana-security' )
		),
		'lana_security_logs'            => array(
			'option'      => get_option( 'lana_security_logs', false ),
			'label'       => __( 'Security Logs', 'lana-security' ),
			'description' => __( 'Monitors: activate and deactivate Lana Security plugin, password change (roles: only administrator), delete user (roles: all)', 'lana-security' )
		),
		'lana_security_login_logs'      => array(
			'option'      => get_option( 'lana_security_login_logs', false ),
			'label'       => __( 'Login Logs', 'lana-security' ),
			'description' => __( 'Monitors: success and failed login with comment', 'lana-security' )
		)
	);
}

/**
 * Lana Security
 * dashboard page
 */
function lana_security_dashboard() {
	include_once 'views/lana-security-dashboard-page.php';
}

/**
 * Lana Security
 * logs page
 */
function lana_security_logs() {
	if ( ! get_option( 'lana_security_logs', false ) ):
		?>
        <div class="wrap">
            <h2><?php _e( 'Security Logs', 'lana-security' ); ?></h2>

            <p><?php printf( __( 'Logs is disabled. Go to the <a href="%s">Settings</a> page to enable.', 'lana-security' ), admin_url( 'admin.php?page=lana-security-settings' ) ); ?></p>
        </div>
		<?php

		return;
	endif;

	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
	require_once( 'includes/class-lana-security-logs-list-table.php' );

	$lana_security_logs_list_table = new Lana_Security_Logs_List_Table();
	$lana_security_logs_list_table->prepare_items();
	?>
    <div class="wrap">
        <h2>
			<?php _e( 'Security Logs', 'lana-security' ); ?>
            <a href="<?php echo wp_nonce_url( add_query_arg( 'lana_security_delete_security_logs', 'true', admin_url( 'admin.php?page=lana-security-logs' ) ), 'delete_logs' ); ?>"
               class="add-new-h2">
				<?php _e( 'Delete Logs', 'lana-security' ); ?>
            </a>
        </h2>
        <br/>

        <form id="lana_security_logs_form" method="post">
			<?php $lana_security_logs_list_table->display(); ?>
        </form>
    </div>
	<?php
}

/**
 * Lana Security
 * delete security logs from database
 */
function lana_security_delete_security_logs() {
	global $wpdb;

	if ( empty( $_GET['lana_security_delete_security_logs'] ) ) {
		return;
	}

	/**
	 * Only admin
	 * can delete security log
	 */
	if ( ! current_user_can( 'manage_lana_security_logs' ) ) {
		add_settings_error( 'delete_log', 'lana_security_delete_log_permission_error', __( 'You\'re not allowed to delete security logs!' ), 'error' );
		settings_errors();

		return;
	}

	check_admin_referer( 'delete_logs' );

	$table_name = $wpdb->prefix . 'lana_security_logs';
	$wpdb->query( "TRUNCATE TABLE " . $table_name . ";" );
}

add_action( 'admin_init', 'lana_security_delete_security_logs' );

/**
 * Lana Security
 * login logs page
 */
function lana_security_login_logs() {
	if ( ! get_option( 'lana_security_login_logs', false ) ):
		?>
        <div class="wrap">
            <h2><?php _e( 'Login Logs', 'lana-security' ); ?></h2>

            <p><?php printf( __( 'Logs is disabled. Go to the <a href="%s">Settings</a> page to enable.', 'lana-security' ), admin_url( 'admin.php?page=lana-security-settings' ) ); ?></p>
        </div>
		<?php

		return;
	endif;

	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
	require_once( 'includes/class-lana-security-login-logs-list-table.php' );

	$lana_security_logs_list_table = new Lana_Security_Login_Logs_List_Table();
	$lana_security_logs_list_table->prepare_items();
	?>
    <div class="wrap">
        <h2>
			<?php _e( 'Login Logs', 'lana-security' ); ?>
            <a href="<?php echo wp_nonce_url( add_query_arg( 'lana_security_delete_login_logs', 'true', admin_url( 'admin.php?page=lana-security-login-logs' ) ), 'delete_logs' ); ?>"
               class="add-new-h2">
				<?php _e( 'Delete Logs', 'lana-security' ); ?>
            </a>
        </h2>
        <br/>

        <form id="lana_security_login_logs_form" method="post">
			<?php $lana_security_logs_list_table->display(); ?>
        </form>
    </div>
	<?php
}

/**
 * Lana Security
 * delete login logs from database
 */
function lana_security_delete_login_logs() {
	global $wpdb;

	if ( empty( $_GET['lana_security_delete_login_logs'] ) ) {
		return;
	}

	/**
	 * Only admin
	 * can delete login log
	 */
	if ( ! current_user_can( 'manage_lana_security_login_logs' ) ) {
		add_settings_error( 'delete_log', 'lana_security_delete_login_log_permission_error', __( 'You\'re not allowed to delete login logs!' ), 'error' );
		settings_errors();

		return;
	}

	check_admin_referer( 'delete_logs' );

	$table_name = $wpdb->prefix . 'lana_security_login_logs';
	$wpdb->query( "TRUNCATE TABLE " . $table_name . ";" );
}

add_action( 'admin_init', 'lana_security_delete_login_logs' );

/**
 * Lana Security
 * settings page
 */
function lana_security_settings() {
	?>
    <div class="wrap">
        <h2><?php _e( 'Lana Security Settings', 'lana-security' ); ?></h2>

        <form method="post" action="options.php">
			<?php settings_fields( 'lana-security-settings-group' ); ?>

            <h2 class="title"><?php _e( 'Security Settings', 'lana-security' ); ?></h2>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">
                        <label for="lana_security_encrypt_version">
							<?php _e( 'Encrypt Version', 'lana-security' ); ?>
                        </label>
                    </th>
                    <td>
                        <select name="lana_security_encrypt_version" id="lana_security_encrypt_version">
                            <option value="0"
								<?php selected( get_option( 'lana_security_encrypt_version', false ), false ); ?>>
								<?php _e( 'Disabled', 'lana-security' ); ?>
                            </option>
                            <option value="1"
								<?php selected( get_option( 'lana_security_encrypt_version', false ), true ); ?>>
								<?php _e( 'Enabled', 'lana-security' ); ?>
                            </option>
                        </select>
                        <br/>
                        <span class="description">
							<?php _e( 'Encrypt WordPress version in frontend scripts and styles, and remove generator', 'lana-security' ); ?>
						</span>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label for="lana_security_insecure_files">
							<?php _e( 'Insecure Files', 'lana-security' ); ?>
                        </label>
                    </th>
                    <td>
                        <select name="lana_security_insecure_files" id="lana_security_insecure_files">
                            <option value="0"
								<?php selected( get_option( 'lana_security_insecure_files', true ), false ); ?>>
								<?php _e( 'Deny', 'lana-security' ); ?>
                            </option>
                            <option value="1"
								<?php selected( get_option( 'lana_security_insecure_files', true ), true ); ?>>
								<?php _e( 'Allow', 'lana-security' ); ?>
                            </option>
                        </select>
                        <br/>
                        <span class="description">
							<?php _e( 'Block insecure files (readme.html, license.txt) with htaccess', 'lana-security' ); ?>
						</span>
                    </td>
                </tr>
            </table>

            <h2 class="title"><?php _e( 'Login Settings', 'lana-security' ); ?></h2>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">
                        <label for="lana_security_login_captcha">
							<?php _e( 'Login Captcha', 'lana-security' ); ?>
                        </label>
                    </th>
                    <td>
                        <select name="lana_security_login_captcha" id="lana_security_login_captcha">
                            <option value="0"
								<?php selected( get_option( 'lana_security_login_captcha', false ), false ); ?>>
								<?php _e( 'Disabled', 'lana-security' ); ?>
                            </option>
                            <option value="1"
								<?php selected( get_option( 'lana_security_login_captcha', false ), true ); ?>>
								<?php _e( 'Enabled', 'lana-security' ); ?>
                            </option>
                        </select>
                    </td>
                </tr>
            </table>

            <h2 class="title"><?php _e( 'Log Settings', 'lana-security' ); ?></h2>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">
                        <label for="lana_security_logs">
							<?php _e( 'Security Logs', 'lana-security' ); ?>
                        </label>
                    </th>
                    <td>
                        <select name="lana_security_logs" id="lana_security_logs">
                            <option value="0"
								<?php selected( get_option( 'lana_security_logs', false ), false ); ?>>
								<?php _e( 'Disabled', 'lana-security' ); ?>
                            </option>
                            <option value="1"
								<?php selected( get_option( 'lana_security_logs', false ), true ); ?>>
								<?php _e( 'Enabled', 'lana-security' ); ?>
                            </option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label for="lana_security_login_logs">
							<?php _e( 'Login Logs', 'lana-security' ); ?>
                        </label>
                    </th>
                    <td>
                        <select name="lana_security_login_logs" id="lana_security_login_logs">
                            <option value="0"
								<?php selected( get_option( 'lana_security_login_logs', false ), false ); ?>>
								<?php _e( 'Disabled', 'lana-security' ); ?>
                            </option>
                            <option value="1"
								<?php selected( get_option( 'lana_security_login_logs', false ), true ); ?>>
								<?php _e( 'Enabled', 'lana-security' ); ?>
                            </option>
                        </select>
                    </td>
                </tr>
            </table>

            <p class="submit">
                <input type="submit" class="button-primary"
                       value="<?php esc_attr_e( 'Save Changes', 'lana-security' ); ?>"/>
            </p>

        </form>
    </div>
	<?php
}

/**
 * Replace WordPress version in script and style src
 *
 * @param $src
 *
 * @return string
 */
function lana_security_replace_wp_version_strings( $src ) {
	global $wp_version;
	global $wp_styles;

	if ( ! get_option( 'lana_security_encrypt_version', false ) ) {
		return $src;
	}

	if ( is_a( $wp_styles, 'WP_Styles' ) ) {
		$wp_styles->default_version = crc32( $wp_styles->default_version );
	}

	parse_str( parse_url( $src, PHP_URL_QUERY ), $query );

	if ( ! empty( $query['ver'] ) && $query['ver'] === $wp_version ) {
		$crypted_wp_version = crc32( $wp_version );
		$src                = add_query_arg( 'ver', $crypted_wp_version, $src );
	}

	return $src;
}

add_filter( 'script_loader_src', 'lana_security_replace_wp_version_strings', 1019 );
add_filter( 'style_loader_src', 'lana_security_replace_wp_version_strings', 1019 );

/**
 * Hide WordPress version strings from generator meta tag
 *
 * @param $type
 *
 * @return string
 */
function lana_security_remove_the_generator( $type ) {

	if ( ! get_option( 'lana_security_encrypt_version', false ) ) {
		return $type;
	}

	return '';
}

add_filter( 'the_generator', 'lana_security_remove_the_generator', 10, 1 );

/**
 * Clean up wp_head() from unused or unsecure stuff
 */
function lana_security_remove_unsecure_head() {

	if ( ! get_option( 'lana_security_encrypt_version', false ) ) {
		return;
	}

	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'index_rel_link' );
	remove_action( 'wp_head', 'feed_links', 2 );
	remove_action( 'wp_head', 'feed_links_extra', 3 );
	remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10 );
	remove_action( 'wp_head', 'wp_shortlink_wp_head', 10 );
}

add_filter( 'init', 'lana_security_remove_unsecure_head' );

/**
 * Lana Security
 * deny insecure files (readme.html, license.txt)
 *
 * @param $rules
 *
 * @return string
 */
function lana_security_deny_insecure_files( $rules ) {

	if ( ! get_option( 'lana_security_insecure_files', true ) ) {
		return $rules;
	}

	$htaccess = PHP_EOL;
	$htaccess .= '#BEGIN Lana Security' . PHP_EOL;

	/** block readme.html */
	$htaccess .= '<Files "readme.html">' . PHP_EOL;
	$htaccess .= '  order deny,allow' . PHP_EOL;
	$htaccess .= '  deny from all' . PHP_EOL;
	$htaccess .= '</Files>' . PHP_EOL;

	/** block license.txt */
	$htaccess .= '<Files "license.txt">' . PHP_EOL;
	$htaccess .= '  order deny,allow' . PHP_EOL;
	$htaccess .= '  deny from all' . PHP_EOL;
	$htaccess .= '</Files>' . PHP_EOL;

	$htaccess .= '#END Lana Security' . PHP_EOL;
	$htaccess .= PHP_EOL;

	return $htaccess . $rules;
}

add_filter( 'mod_rewrite_rules', 'lana_security_deny_insecure_files' );

/**
 * Lana Security
 * Flush rewrite rules after option update
 *
 * @param $option
 */
function lana_security_rewrite_rules_flush( $option ) {

	if ( $option != 'lana_security_insecure_files' ) {
		return;
	}

	global $wp_rewrite;
	$wp_rewrite->flush_rules();
	$wp_rewrite->init();
}

add_action( 'update_option', 'lana_security_rewrite_rules_flush', 100, 1 );

/**
 * Lana Security
 * get captcha
 * @return string
 */
function lana_security_get_captcha() {

	lana_security_register_session();

	$image = imagecreatetruecolor( 70, 30 );
	$white = imagecolorallocate( $image, 255, 255, 255 );
	$black = imagecolorallocate( $image, 0, 0, 0 );
	$num1  = rand( 1, 9 );
	$num2  = rand( 1, 9 );
	$str   = $num1 . ' + ' . $num2 . ' = ';
	$font  = dirname( __FILE__ ) . '/assets/fonts/bebas.ttf';

	imagefill( $image, 0, 0, $white );
	imagettftext( $image, 18, 0, 0, 24, $black, $font, $str );

	ob_start();
	imagepng( $image );
	$image_data = ob_get_clean();
	imagedestroy( $image );

	$_SESSION['lana_security']['captcha'] = $num1 + $num2;

	return $image_data;
}

/**
 * Lana Security
 * get base64 encoded captcha
 * @return string
 */
function lana_security_get_base64_captcha() {
	return base64_encode( lana_security_get_captcha() );
}

/**
 * Lana Security
 * Add captcha to login form
 */
function lana_security_add_captcha_to_login_form() {

	if ( ! get_option( 'lana_security_login_captcha', false ) ) {
		return;
	}

	?>
    <p class="lana-captcha">
        <label for="captcha">
			<?php _e( 'Captcha', 'lana-security' ); ?>
            <br>
            <img src="data:image/png;base64,<?php echo esc_attr( lana_security_get_base64_captcha() ); ?>"
                 class="captcha-img">
            <input type="number" name="lana_captcha" id="captcha" class="input captcha-input" size="2" min="0" max="20"
                   required>
        </label>
    </p>
	<?php
}

add_filter( 'login_form', 'lana_security_add_captcha_to_login_form' );

/**
 * Lana Security
 * Verify the captcha
 *
 * @param WP_user $user
 *
 * @return WP_Error|WP_user
 */
function lana_security_validate_captcha( $user ) {

	if ( ! get_option( 'lana_security_login_captcha', false ) ) {
		return $user;
	}

	if ( ! isset( $_POST['wp-submit'] ) ) {
		return $user;
	}

	lana_security_register_session();

	if ( empty( $_POST['lana_captcha'] ) ) {
		return new WP_Error( 'error_captcha', '<strong>' . __( 'ERROR:', 'lana-security' ) . '</strong>' . ' ' . __( 'The captcha field is empty.', 'lana-security' ) );
	}

	if ( $_POST['lana_captcha'] != $_SESSION['lana_security']['captcha'] ) {
		return new WP_Error( 'error_captcha', '<strong>' . __( 'ERROR:', 'lana-security' ) . '</strong>' . ' ' . __( 'The captcha is incorrect.', 'lana-security' ) );
	}

	return $user;
}

add_filter( 'authenticate', 'lana_security_validate_captcha', 100 );

/**
 * Lana Security
 * Add login log to database
 *
 * @param $user
 * @param $username
 * @param $password
 */
function lana_security_add_login_log( $user, $username, $password ) {

	if ( empty( $username ) || empty( $password ) ) {
		return $user;
	}

	if ( ! get_option( 'lana_security_login_logs', false ) ) {
		return $user;
	}

	if ( is_a( $user, 'WP_Error' ) ) {
		foreach ( $user->errors as $error => $description ) {
			lana_security_add_login_log_to_wpdb( $username, 0, $error );
		}
	}

	if ( is_a( $user, 'WP_User' ) ) {
		lana_security_add_login_log_to_wpdb( $username, 1 );
	}

	return $user;
}

add_action( 'authenticate', 'lana_security_add_login_log', 1000, 3 );

/**
 * Lana Security
 * Add admin change password log to database
 *
 * @param $user_id
 */
function lana_security_add_admin_change_password_log( $user_id ) {

	if ( ! isset( $_POST['pass1'] ) || '' == $_POST['pass1'] ) {
		return;
	}

	/** only administrator */
	if ( ! user_can( $user_id, 'administrator' ) ) {
		return;
	}

	$user = wp_get_current_user();
	lana_security_add_security_log_to_wpdb( $user_id, sprintf( __( 'admin password changed by %s', 'lana-security' ), $user->user_login ) );
}

add_action( 'profile_update', 'lana_security_add_admin_change_password_log', 100, 1 );

/**
 * Lana Security
 * Add user deleted log to database
 *
 * @param $user_id
 */
function lana_security_add_user_deleted_log( $user_id ) {
	$user = wp_get_current_user();
	lana_security_add_security_log_to_wpdb( $user_id, sprintf( __( 'user deleted by %s', 'lana-security' ), $user->user_login ) );
}

add_action( 'delete_user', 'lana_security_add_user_deleted_log', 100, 1 );

/**
 * Lana Security
 * add security log to database
 *
 * @param $user_id
 * @param $comment
 */
function lana_security_add_security_log_to_wpdb( $user_id, $comment = '' ) {
	global $wpdb;

	if ( get_option( 'lana_security_logs', false ) ) {

		$wpdb->hide_errors();

		$user     = get_userdata( $user_id );
		$username = $user->user_login;

		$wpdb->insert( $wpdb->prefix . 'lana_security_logs', array(
			'user_id'    => $user_id,
			'username'   => $username,
			'comment'    => $comment,
			'user_ip'    => sanitize_text_field( ! empty( $_SERVER['HTTP_X_FORWARD_FOR'] ) ? $_SERVER['HTTP_X_FORWARD_FOR'] : $_SERVER['REMOTE_ADDR'] ),
			'user_agent' => sanitize_text_field( isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '' ),
			'date'       => current_time( 'mysql' )
		), array( '%s', '%s', '%s', '%s' ) );
	}
}

/**
 * Lana Security
 * add login log to database
 *
 * @param $username
 * @param $status
 * @param $comment
 */
function lana_security_add_login_log_to_wpdb( $username, $status, $comment = '' ) {
	global $wpdb;

	if ( get_option( 'lana_security_login_logs', false ) ) {

		$wpdb->hide_errors();

		$wpdb->insert( $wpdb->prefix . 'lana_security_login_logs', array(
			'username'   => $username,
			'status'     => $status,
			'comment'    => $comment,
			'user_ip'    => sanitize_text_field( ! empty( $_SERVER['HTTP_X_FORWARD_FOR'] ) ? $_SERVER['HTTP_X_FORWARD_FOR'] : $_SERVER['REMOTE_ADDR'] ),
			'user_agent' => sanitize_text_field( isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '' ),
			'date'       => current_time( 'mysql' )
		), array( '%s', '%d', '%s', '%s', '%s' ) );
	}
}