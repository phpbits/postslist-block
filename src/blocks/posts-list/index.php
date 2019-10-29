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
	global $post;

	/* Setup the query */
	$postsListQuery = new WP_Query(
		array(
			'posts_per_page'      => $attributes['postsToShow'],
			'post_status'         => 'publish',
			'ignore_sticky_posts' => 1,
			'post__not_in'        => array( $post->ID ), // Exclude the current post from the grid.
		)
	);

	$html = '<section class="'. ( isset( $attributes['className'] ) ? $attributes['className'] : '' ) .'">';

	/* Start the loop */
	if ( $postsListQuery->have_posts() ) {
		while ( $postsListQuery->have_posts() ) {
			$postsListQuery->the_post();

			/* Setup the post ID */
			$post_id = get_the_ID();

			/* Setup the featured image ID */
			$post_thumb_id = get_post_thumbnail_id( $post_id );

			/* Get the post title */
			$title = get_the_title( $post_id );

			if ( ! $title ) {
				$title = __( 'Untitled', 'postslist-block' );
			}

			$post_classes = '';

			/* Add sticky class */
			if ( is_sticky( $post_id ) ) {
				$post_classes .= ' sticky';
			} else {
				$post_classes .= null;
			}
			/* Join classes together */
			$post_classes = join( ' ', get_post_class( $post_classes, $post_id ) );

			$html .= sprintf(
				'<article id="post-%1$s" class="%2$s">',
				esc_attr( $post_id ),
				esc_attr( $post_classes )
			);

			$html .= '<header class="entry-header">';
				$html .= sprintf(
					'<h2><a href="%1$s" rel="bookmark">%2$s</a></h2>',
					esc_url( get_permalink( $post_id ) ),
					esc_html( $title )
				);
			$html .= '</header>';

			if( $post_thumb_id ){
				/* Display the featured image */
				$html .= sprintf(
					'<div class="entry-media"><a href="%1$s" class="post-thumbnail" rel="bookmark" aria-hidden="true" tabindex="-1">%2$s</a></div>',
					esc_url( get_permalink( $post_id ) ),
					wp_get_attachment_image( $post_thumb_id, 'full' )
				);
			}
			

			$html .= '</article>';
		}
		/* Restore original post data */
		wp_reset_postdata();
	}

	$html .= '</section>';
	
	return $html;
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