<?php

/**
 * Hestia functions and definitions
 *
 * @package Hestia
 * @since   Hestia 1.0
 */
if (!session_id()) {
	session_start();
}

define('HESTIA_VERSION', '3.0.8');
define('HESTIA_VENDOR_VERSION', '1.0.2');
define('HESTIA_PHP_INCLUDE', trailingslashit(get_template_directory()) . 'inc/');
define('HESTIA_CORE_DIR', HESTIA_PHP_INCLUDE . 'core/');

if (!defined('HESTIA_DEBUG')) {
	define('HESTIA_DEBUG', false);
}

// Load hooks
require_once(HESTIA_PHP_INCLUDE . 'hooks/hooks.php');

// Load Helper Globally Scoped Functions
require_once(HESTIA_PHP_INCLUDE . 'helpers/sanitize-functions.php');
require_once(HESTIA_PHP_INCLUDE . 'helpers/layout-functions.php');

if (class_exists('WooCommerce', false)) {
	require_once(HESTIA_PHP_INCLUDE . 'compatibility/woocommerce/functions.php');
}

if (function_exists('max_mega_menu_is_enabled')) {
	require_once(HESTIA_PHP_INCLUDE . 'compatibility/max-mega-menu/functions.php');
}

/**
 * Adds notice for PHP < 5.3.29 hosts.
 */
function hestia_no_support_5_3()
{
	$message = __('Hey, we\'ve noticed that you\'re running an outdated version of PHP which is no longer supported. Make sure your site is fast and secure, by upgrading PHP to the latest version.', 'hestia');

	printf('<div class="error"><p>%1$s</p></div>', esc_html($message));
}


if (version_compare(PHP_VERSION, '5.3.29') < 0) {
	/**
	 * Add notice for PHP upgrade.
	 */
	add_filter('template_include', '__return_null', 99);
	switch_theme(WP_DEFAULT_THEME);
	unset($_GET['activated']);
	add_action('admin_notices', 'hestia_no_support_5_3');

	return;
}

/**
 * Begins execution of the theme core.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function hestia_run()
{

	require_once HESTIA_CORE_DIR . 'class-hestia-autoloader.php';
	$autoloader = new Hestia_Autoloader();

	spl_autoload_register(array($autoloader, 'loader'));

	new Hestia_Core();

	$vendor_file = trailingslashit(get_template_directory()) . 'vendor/composer/autoload_files.php';
	if (is_readable($vendor_file)) {
		$files = require_once $vendor_file;
		foreach ($files as $file) {
			if (is_readable($file)) {
				include_once $file;
			}
		}
	}
	add_filter('themeisle_sdk_products', 'hestia_load_sdk');

	if (class_exists('Ti_White_Label', false)) {
		Ti_White_Label::instance(get_template_directory() . '/style.css');
	}
}

/**
 * Loads products array.
 *
 * @param array $products All products.
 *
 * @return array Products array.
 */
function hestia_load_sdk($products)
{
	$products[] = get_template_directory() . '/style.css';

	return $products;
}

require_once(HESTIA_CORE_DIR . 'class-hestia-autoloader.php');

/**
 * The start of the app.
 *
 * @since   1.0.0
 */
hestia_run();

/**
 * Append theme name to the upgrade link
 * If the active theme is child theme of Hestia
 *
 * @param string $link - Current link.
 *
 * @return string $link - New upgrade link.
 * @package hestia
 * @since   1.1.75
 */
function hestia_upgrade_link($link)
{

	$theme_name = wp_get_theme()->get_stylesheet();

	$hestia_child_themes = array(
		'orfeo',
		'fagri',
		'tiny-hestia',
		'christmas-hestia',
		'jinsy-magazine',
	);

	if ($theme_name === 'hestia') {
		return $link;
	}

	if (!in_array($theme_name, $hestia_child_themes, true)) {
		return $link;
	}

	$link = add_query_arg(
		array(
			'theme' => $theme_name,
		),
		$link
	);

	return $link;
}

add_filter('hestia_upgrade_link_from_child_theme_filter', 'hestia_upgrade_link');

/**
 * Check if $no_seconds have passed since theme was activated.
 * Used to perform certain actions, like displaying upsells or add a new recommended action in About Hestia page.
 *
 * @param integer $no_seconds number of seconds.
 *
 * @return bool
 * @since  1.1.45
 * @access public
 */
function hestia_check_passed_time($no_seconds)
{
	$activation_time = get_option('hestia_time_activated');
	if (!empty($activation_time)) {
		$current_time    = time();
		$time_difference = (int) $no_seconds;
		if ($current_time >= $activation_time + $time_difference) {
			return true;
		} else {
			return false;
		}
	}

	return true;
}

/**
 * Legacy code function.
 */
function hestia_setup_theme()
{
	return;
}

add_action('qsm_after_all_section', 'qsm_after_all_section_func');
function qsm_after_all_section_func()
{

	$ds_get_quiz = get_posts(array(
		'post_type'      => 'qsm_quiz',
		'post_status'    => 'publish'
	));

	$quiz_title = '';
	if ($ds_get_quiz) {
		$quiz_titles = [];
		foreach ($ds_get_quiz as $quiz_title) {
			$quiz_titles[] = $quiz_title->post_name;
		}
	}

	$_SESSION['skip_title'][] = basename($_SERVER['REQUEST_URI']);
	$new_array = array_diff($quiz_titles, $_SESSION['skip_title']);

	if (empty($new_array)) {
		unset($_SESSION['skip_title']);
		$skip_post_title = $quiz_titles[0];
	} else {
		$skip_post_title = $new_array[array_key_first($new_array)];
	}

	$skip_post_title = site_url() . '/quiz/' . $skip_post_title;
	echo '<hr/>';
	echo "<a href='{$skip_post_title}' class='btn ds-btn-danger'>Skip this Quiz</a>&nbsp;";
}


add_action('hestia_before_single_post_content', 'hestia_before_single_post_content_func');
function hestia_before_single_post_content_func()
{
	global $wpdb;
	$post_meta = get_post_meta(get_the_ID());
	$quiz_id   = isset($post_meta['quiz_id'][0]) ? $post_meta['quiz_id'][0] : '';

	if ($quiz_id) {
		$db_table = $wpdb->prefix . 'mlw_quizzes';
		$quiz_settings = $wpdb->get_col($wpdb->prepare("SELECT quiz_settings FROM $db_table WHERE quiz_id =  %d ", $quiz_id));
		$quiz_options = unserialize($quiz_settings[0]);
		$quiz_options = unserialize($quiz_options['quiz_options']);
		$scheduled_time_start = $quiz_options['scheduled_time_start'];
		$scheduled_time_end = $quiz_options['scheduled_time_end'];
		$scheduled_time_end_str = date('M d, Y H:i:s', strtotime($scheduled_time_end));
		wp_localize_script(
			'ds-quiz-counter',
			'ds',
			array(
				'ajaxurl' => admin_url('admin-ajax.php'),
				'scheduled_time_end' => $scheduled_time_end_str,
			)
		);
		if (!empty($scheduled_time_end)) {
			echo "<div class='quiz-time-wrapper'><h6><span id='quiz-counter'></span></h6></div>";
		}
	}
}

add_action('wp_enqueue_scripts', 'ds_script');
function ds_script()
{
	wp_enqueue_script('ds-quiz-counter', get_template_directory_uri() . '/assets/js/quiz-counter.js', array(), '1.0.0', true);
}
