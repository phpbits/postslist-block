<?php
/**
 * Server-side rendering of the `wpengine-exercise/postslist` block.
 *
 */
/**
 * Renders the block on server.
 *
 * @param array $attributes The block attributes.
 *
 * @return string Returns the block content.
 */
function wpengine_render_postslist_block( $attributes ) {
	
	return 'test';
}
/**
 * Registers the block on server.
 */
function wpengine_register_postslist_block() {
	// Return early if this function does not exist.
	if ( ! function_exists( 'register_block_type' ) ) {
		return;
	}
	// Load attributes from block.json.
	ob_start();
	include POSTSLISTBLOCK_PLUGIN_DIR . 'src/blocks/posts-list/block.json';
	$metadata = json_decode( ob_get_clean(), true );
	register_block_type(
		$metadata['name'],
		array(
			'editor_script'   => 'postslist-block-editor',
			'editor_style'    => 'postslist-block-editor-css',
			'style'           => 'postslist-block-frontend',
			'attributes'      => $metadata['attributes'],
			'render_callback' => 'wpengine_render_postslist_block',
		)
	);
}
add_action( 'init', 'wpengine_register_postslist_block' );