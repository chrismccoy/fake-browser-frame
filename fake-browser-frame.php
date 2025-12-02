<?php
/**
 * Plugin Name: Fake Browser Frame
 * Description: Wrap featured images in a mock browser window.
 * Version: 1.0.0
 * Author: Chris McCoy
 * Text Domain: fake-browser-frame
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Fake_Browser_Frame {

	/**
	 * The single instance of the class.
	 */
	protected static $_instance = null;

	/**
	 * Main Instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * Hook into actions and filters.
	 */
	private function init_hooks() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_filter( 'post_thumbnail_html', array( $this, 'wrap_featured_image' ), 10, 5 );
	}

	/**
	 * Enqueue frontend styles.
	 */
	public function enqueue_assets() {
		// Only load assets if we are actually viewing a post/page that might use this.
		if ( ! is_singular() ) {
			return;
		}

		wp_enqueue_style(
			'fake-browser-frame-css',
			plugin_dir_url( __FILE__ ) . 'assets/css/frame.css',
			array(),
			'1.0.0'
		);
	}

	/**
	 * Filter the post thumbnail HTML.
	 */
	public function wrap_featured_image( $html, $post_id, $post_thumbnail_id, $size, $attr ) {
		// if no image, do nothing.
		if ( empty( $html ) ) {
			return $html;
		}

		$should_wrap = apply_filters( 'fbf_should_wrap_image', true, $post_id );

		if ( ! $should_wrap ) {
			return $html;
		}

		// Build the browser header
		$header_html = sprintf(
			'<div class="fbf-header">
				<div class="fbf-dots">
					<span class="fbf-dot fbf-red"></span>
					<span class="fbf-dot fbf-yellow"></span>
					<span class="fbf-dot fbf-green"></span>
				</div>
				<div class="fbf-address-bar"></div>
			</div>'
		);

		// Wrap the content
		$wrapper_classes = apply_filters( 'fbf_wrapper_classes', array( 'fbf-window' ) );
		$class_string    = implode( ' ', array_map( 'sanitize_html_class', $wrapper_classes ) );

		return sprintf(
			'<div class="%1$s">%2$s<div class="fbf-content">%3$s</div></div>',
			esc_attr( $class_string ),
			$header_html, // Safe markup generated above
			$html         // The image HTML
		);
	}
}

// Woot
Fake_Browser_Frame::instance();
