<?php

require_once(plugin_dir_path(__FILE__).'core/articles_system/ArticleGeneration.php');

/**
 * Plugin Name: Escríbelo
 * Plugin URI: https://escribelo.ai/
 * Description: Plugin oficial de Escríbelo | Crea y sube contenidos de forma automática.
 * Version: 1.0.9
 * Requires at least: 5.6
 * Requires PHP: 7.2
 * Author: Escríbelo
 */

function add_escribelo_cooldown_interval($schedules) {
	$schedules['escribelo_cooldown'] = array(
		'interval' => (60 * 3.5),
		'display'  => __( 'Escribelo generation cooldown' ),
	);
	return $schedules;
}
add_filter('cron_schedules', 'add_escribelo_cooldown_interval');

add_action('article_generation', 'createArticleTask');
$task = wp_next_scheduled('article_generation');
if (!$task) {
	wp_schedule_event(time(), 'escribelo_cooldown', 'article_generation');
}

add_action('admin_menu', 'options_menu');
add_action('admin_enqueue_scripts', 'load_custom_css');

/*
 *  Load the custom css
 */
function load_custom_css() {
    wp_register_style('custom_wp_admin_css', plugin_dir_url( __FILE__ ) . 'css/style.css', false, '1.0.0');
    wp_enqueue_style('custom_wp_admin_css');
}

/*
 *  The menu located in wp-admin
 */

function options_menu() {
    add_menu_page(
        'Escríbelo',
        'Escríbelo',
        'manage_options',
        plugin_dir_path(__FILE__) . (get_option('escribelo_session') != null ? 'menus/main_menu.php' : 'menus/login_menu.php'),
        null,
        plugin_dir_url(__FILE__) . 'images/logo.png',
        20
    );
}