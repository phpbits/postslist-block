<?php
/**
 * Server-side rendering of the `wpengine-exercise/postslist` block.
 *
 * @package PostsListBlockMain
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
	$paged            = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
	$posts_list_query = new WP_Query(
		array(
			'posts_per_page'      => $attributes['postsToShow'],
			'paged'               => $paged,
			'post_status'         => 'publish',
			'ignore_sticky_posts' => 1,
			'post__not_in'        => array( $post->ID ), // Exclude the current post from the grid.
		)
	);

	$html = '<section class="postslist-block ' . ( isset( $attributes['className'] ) ? $attributes['className'] : '' ) . ' ' . ( isset( $attributes['align'] ) ? 'align' . $attributes['align'] : '' ) . '">';

	/* Start the loop */
	if ( $posts_list_query->have_posts() ) {
		while ( $posts_list_query->have_posts() ) {
			$posts_list_query->the_post();

			/* Setup the post ID */
			$post_id = get_the_ID();

			/* Setup the featured image ID */
			$post_thumb_id = get_post_thumbnail_id( $post_id );

			/* Get the post title */
			$title = get_the_title( $post_id );

			if ( ! $title ) {
				$title = __( 'Untitled', 'postslist-block' );
			}

			/* Get the post excerpy */
			$excerpt = apply_filters(
				'the_excerpt',
				get_post_field(
					'post_excerpt',
					$post_id,
					'display'
				)
			);

			if ( empty( $excerpt ) ) {
				$excerpt = apply_filters(
					'the_excerpt',
					wp_trim_words(
						preg_replace(
							array(
								'/\<figcaption>.*\<\/figcaption>/',
								'/\[caption.*\[\/caption\]/',
							),
							'',
							get_the_content()
						),
						55
					)
				);
			}

			if ( ! $excerpt ) {
				$excerpt = null;
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
			$html .= '<div class="entry-meta">';
			$html .= sprintf(
				'<span class="post-author" itemprop="author" itemtype="https://schema.org/Person"><a href="%2$s" itemprop="url" rel="author"><span itemprop="name">%1$s</span></a></span>',
				esc_html( get_the_author_meta( 'display_name', get_the_author_meta( 'ID' ) ) ),
				esc_html( get_author_posts_url( get_the_author_meta( 'ID' ) ) )
			);
			$html .= sprintf(
				'<time datetime="%1$s" class="entry-date published" itemprop="datePublished"><a href="%2$s" itemprop="url">%3$s</a></time>',
				esc_attr( get_the_date( 'c', $post_id ) ),
				esc_url( get_permalink( $post_id ) ),
				esc_html( get_the_date( '', $post_id ) )
			);
			$html .= '</div>';

			$html .= '</header>';

			if ( $post_thumb_id ) {

				/* Display the featured image */
				$html .= sprintf(
					'<div class="entry-media"><a href="%1$s" class="post-thumbnail" rel="bookmark" aria-hidden="true" tabindex="-1">%2$s</a></div>',
					esc_url( get_permalink( $post_id ) ),
					wp_get_attachment_image( $post_thumb_id, 'full' )
				);
			}

			$html .= '<div class="entry-summary">';
			$html .= wp_kses_post( $excerpt );
			$html .= '</div>';
			$html .= '</article>';
		}

		/*
		 * The posts pagination outputs a set of page numbers with links to the previous and next pages of posts.
		 *
		 * @link https://codex.wordpress.org/Function_Reference/the_posts_pagination
		 */
		$html .= '<nav class="navigation pagination" role="navigation">';
		$html .= '<h2 class="screen-reader-text">' . __( 'Posts navigation', 'postslist-block' ) . '</h2>';
		$html .= '<div class="nav-links">';
		$html .= paginate_links(
			array(
				'base'         => str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) ),
				'total'        => $posts_list_query->max_num_pages,
				'current'      => max( 1, get_query_var( 'paged' ) ),
				'format'       => '?paged=%#%',
				'show_all'     => false,
				'type'         => 'plain',
				'end_size'     => 1,
				'mid_size'     => 1,
				'prev_next'    => true,
				'prev_text'    => sprintf( '<i></i> %1$s', __( '&laquo; Newer Posts', 'postslist-block' ) ),
				'next_text'    => sprintf( '%1$s <i></i>', __( 'Older Posts &raquo;', 'postslist-block' ) ),
				'add_args'     => false,
				'add_fragment' => '',
			)
		);
		$html .= '</div>';
		$html .= '</nav>';
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

if ( ! function_exists( 'wpengine_blocks_register_rest_fields' ) ) {
	add_action( 'rest_api_init', 'wpengine_blocks_register_rest_fields' );
	/**
	 * Create API fields for additional info
	 */
	function wpengine_blocks_register_rest_fields() {
		register_rest_field(
			array( 'post' ),
			'featured_image_src_full',
			array(
				'get_callback'    => 'blocks_get_image_src_full',
				'update_callback' => null,
				'schema'          => null,
			)
		);

		/* Add author info */
		register_rest_field(
			'post',
			'author_info',
			array(
				'get_callback'    => 'blocks_get_author_info',
				'update_callback' => null,
				'schema'          => null,
			)
		);
	}

	/**
	 * Get full image source for the rest field
	 *
	 * @param String $object  The object type.
	 * @param String $field_name  Name of the field to retrieve.
	 * @param String $request  The current request object.
	 */
	function blocks_get_image_src_full( $object, $field_name, $request ) {
		$feat_img_array = wp_get_attachment_image_src(
			$object['featured_media'],
			'full',
			false
		);
		return $feat_img_array[0];
	}

	/**
	 * Get author info for the rest field
	 *
	 * @param String $object  The object type.
	 * @param String $field_name  Name of the field to retrieve.
	 * @param String $request  The current request object.
	 */
	function blocks_get_author_info( $object, $field_name, $request ) {
		/* Get the author name */
		$author_data['display_name'] = get_the_author_meta( 'display_name', $object['author'] );
		/* Get the author link */
		$author_data['author_link'] = get_author_posts_url( $object['author'] );
		/* Return the author data */
		return $author_data;
	}
}
