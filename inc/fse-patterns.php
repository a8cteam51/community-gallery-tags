<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Register block patterns for the plugin.
 *
 * @return void
 */
function community_gallery_tags_fse_patterns() {

	register_block_pattern_category(
		'cgt_patterns',
		array(
			'label'       => __( 'Community Gallery Tags', 'community-gallery-tags' ),
			'description' => __( 'Patterns from the Community Gallery Tags Plugin', 'community-gallery-tags' ),
		)
	);

	register_block_pattern(
		'cgt/gallery-page',
		array(
			'title'      => __( 'Gallery Page Template', 'community-gallery-tags' ),
			'categories' => array( 'cgt_patterns' ),
			'keywords'   => array( 'gallery', 'people', 'tags' ),
			'content'    => '<!-- wp:community-gallery-tags/people-tag-list /-->
				<!-- wp:community-gallery-tags/upload {"style":{"spacing":{"margin":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|40"}}}} /-->
				<!-- wp:community-gallery-tags/gallery {"align":"wide"} -->
				<div class="wp-block-community-gallery-tags-gallery alignwide">[gallery]</div>
				<!-- /wp:community-gallery-tags/gallery -->',
		)
	);
}

add_action( 'init', 'community_gallery_tags_fse_patterns' );
