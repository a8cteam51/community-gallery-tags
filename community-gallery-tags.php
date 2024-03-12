<?php
/**
 * Plugin Name:       Community Gallery Tags
 * Description:       Example block scaffolded with Create Block tool.
 * Requires at least: 6.1
 * Requires PHP:      7.0
 * Version:           1.0.0
 * Author:            The WordPress Contributors
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       community-gallery-tags
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Register `post_tag` for use on attachments.
 */
function cgt_add_categories_to_attachments() {
	register_taxonomy_for_object_type( 'post_tag', 'attachment' );
}
add_action( 'init' , 'cgt_add_categories_to_attachments' );

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */
function community_gallery_tags_gallery_block_init() {
	register_block_type(
		__DIR__ . '/build',
		array(
			'render_callback' => 'community_gallery_tags_gallery__render_callback',
		)
	);
}
add_action( 'init', 'community_gallery_tags_gallery_block_init' );

/**
 * Render callback for the Community Gallery Tags - Gallery block.
 */
function community_gallery_tags_gallery__render_callback( $block_attributes, $content ) {
	$post_id = 0; // We can allow customizing this via block attributes if we'd like.  `0` will default to the global $post object on render.
	$attachments = get_attached_media( 'image', $post_id ); // can change the first argument to an empty string if we want everything including videos.

	$return = "<ul>\r\n"; // @todo: add support for adding classes to this `ul` in the block editor.

	foreach ( $attachments as $item ) {
		$return .= "\t<li class='attachment-{$item->ID}'>\r\n" .
			"\t\t" . wp_get_attachment_image( $item->ID ) . "\r\n" .
			"\t\t" . get_the_term_list( $post_id, 'post_tag' ) . "\r\n" .
			"\t\t" . sprintf( '<a class="add-tag" href="javascript:;" data-attachment-id="%d">%s</a>', $item->ID, __( '➕ Add a tag?', 'community-gallery-tags' ) ) . "\r\n" .
			"\t</li>\r\n";
	}

	$return .= "</ul>\r\n";

	return $return;
}
