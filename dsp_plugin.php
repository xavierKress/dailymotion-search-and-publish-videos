<?php
/**
 * Plugin Name: DailyMotion Search & Publish
 * Plugin URI: http://www.walihassan.com
 * Description: Searche Your Favourite Dailymotion Videos and Publish them as post just with one click
 * Version: 2.5.1
 * Author: Wali Hassan
 * Author URI: http://www.walihassan.com
 * License: GPL2
 */ 
define( 'DSP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'DSP_PLUGIN_JS_URL', plugins_url( 'js/', __FILE__ ) );
define( 'DSP_PLUGIN_CSS_URL', plugins_url( 'css/', __FILE__ ) );
define( 'DSP_PLUGIN_CLASS_DIR', DSP_PLUGIN_DIR.'classes/' );
define( 'DSP_PLUGIN_IMAGES_DIR',  plugins_url( 'images/', __FILE__ ) );
include ( DSP_PLUGIN_CLASS_DIR .'dsp_videos.php');
include ( DSP_PLUGIN_CLASS_DIR .'dsp_pagination.php');
include ( DSP_PLUGIN_CLASS_DIR .'dsp_user_settings.php');
include ( DSP_PLUGIN_CLASS_DIR .'dsp_ajax.php');
class DSP_Plugin_Class {
	public function __construct() {
		add_action( 'admin_init', array ($this, 'register_dsp_settings' ) );
		add_action( 'admin_enqueue_scripts', array ($this, 'dsp_plugin_js' ) );		
		add_action('admin_menu', array ($this, 'dsp_create_menu') );		
		add_action('wp_ajax_category_select_action', 'implement_ajax');
		add_action('wp_ajax_category_select_action2', 'implement_ajax_2');
		add_action('wp_ajax_publish_video', 'implement_ajax_video');
		add_action('wp_ajax_pagination', 'pagination_ajax');
		add_action('wp_ajax_nopriv_category_select_action', 'implement_ajax');
		add_action('wp_ajax_nopriv_category_select_action2', 'implement_ajax_2');
	}
	public function dsp_plugin_js() {
		wp_enqueue_script('jquery');  
		wp_enqueue_script( 'dsp_plugin_js', DSP_PLUGIN_JS_URL . 'dsp_plugin.js' ); 
		wp_enqueue_style( 'dsp_plugin_css', DSP_PLUGIN_CSS_URL . 'dsp_plugin.css' ); 
		wp_localize_script( 'dsp_plugin_js', 'dsp_plugin_vars', 
			array(
				'ajaxurl' =>  admin_url( 'admin-ajax.php' ),
				'pluginurl' => plugins_url ('dsp_vids.php')
				)
		);
	}
	public function register_dsp_settings() {
		register_setting( 'dsp-settings-group', 'parent_cat_id_hidden' );
		register_setting( 'dsp-settings-group', 'child_cat_id_hidden' );
		register_setting( 'dsp-settings-group', 'subchild_cat_id_hidden' );	
		register_setting( 'dsp-settings-new', 'video_source' );
		register_setting( 'dsp-settings-user', 'dsp_syndication_key' );
		register_setting( 'dsp-settings-user', 'dsp_custom_field_video_embed' );
		register_setting( 'dsp-settings-user', 'dsp_video_post_title' );
		register_setting( 'dsp-settings-user', 'dsp_video_post_status' );	
		register_setting( 'dsp-settings-user', 'dsp_video_post_format' );
		register_setting( 'dsp-settings-user', 'dsp_video_width' );
		register_setting( 'dsp-settings-user', 'dsp_video_height' );
		register_setting( 'dsp-settings-user', 'dsp_video_autoplay' );
	}
	public function dsp_create_menu() {
		add_menu_page('DailyMotion Videos Dashboard', 'DSP Videos', 'administrator', 'dsp_dashboard', 'dsp_dashboard_page', 'dashicons-video-alt3');
		add_submenu_page('dsp_dashboard', 'DailyMotion User Settings', 'User Settings', 'administrator', 'dsp_settings', 'dsp_user_settings');	
	}
}
$dsp_plugin_class = new DSP_Plugin_Class();