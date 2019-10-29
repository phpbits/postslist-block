<?php
/**
 * Load assets for our blocks.
 *
 * @package PostsListBlockMain
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load general assets for our blocks.
 *
 * @since 1.0.0
 */
class PostsList_Block_Assets {


	/**
	 * This plugin's instance.
	 *
	 * @var PostsList_Block_Assets
	 */
	private static $instance;

	/**
	 * Registers the plugin.
	 */
	public static function register() {
		if ( null === self::$instance ) {
			self::$instance = new PostsList_Block_Assets();
		}
	}

	/**
	 * The base URL path (without trailing slash).
	 *
	 * @var string $url
	 */
	private $url;

	/**
	 * The plugin version.
	 *
	 * @var string $slug
	 */
	private $slug;

	/**
	 * The Constructor.
	 */
	public function __construct() {
		$this->slug = 'postslist-block';
		$this->url  = untrailingslashit( plugins_url( '/', dirname( __FILE__ ) ) );

		add_action( 'enqueue_block_assets', array( $this, 'block_assets' ) );
		add_action( 'init', array( $this, 'editor_assets' ) );
	}

	/**
	 * Enqueue block assets for use within Gutenberg.
	 *
	 * @access public
	 */
	public function block_assets() {

		// Styles.
		wp_enqueue_style(
			$this->slug . '-frontend',
			$this->url . '/build/style.build.css',
			array(),
			POSTSLISTBLOCK_VERSION
		);
	}

	/**
	 * Enqueue block assets for use within Gutenberg.
	 *
	 * @access public
	 */
	public function editor_assets() {

		if ( ! is_admin() ) {
			return;
		}
		if ( ! $this->is_edit_or_new_admin_page() ) {
			return;
		}

		// Styles.
		wp_register_style(
			$this->slug . '-editor',
			$this->url . '/build/editor.build.css',
			array(),
			POSTSLISTBLOCK_VERSION
		);

		// Scripts.
		wp_register_script(
			$this->slug . '-editor',
			$this->url . '/build/index.js',
			array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-plugins', 'wp-components', 'wp-edit-post', 'wp-api', 'wp-rich-text', 'wp-editor' ),
			POSTSLISTBLOCK_VERSION,
			false
		);

	}

	/**
	 * Checks if admin page is the 'edit' or 'new-post' screen.
	 *
	 * @return bool true or false
	 */
	public function is_edit_or_new_admin_page() {
		global $pagenow;
		// phpcs:ignore
		return ( is_admin() && ( $pagenow === 'post.php' || $pagenow === 'post-new.php' ) );
	}

}

PostsList_Block_Assets::register();
