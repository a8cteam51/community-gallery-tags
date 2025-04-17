<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Change user capabilities for the upload media block.
 *
 * @param array  $allcaps Array of key/value pairs where keys represent a capability name and boolean values represent whether the user has that capability.
 * @param array  $caps    Array of capabilities to check.
 * @param array  $args    Array of arguments passed to the capability check.
 * @param object $user    WP_User The user object.
 *
 * @return bool[] The modified array of capabilities.
 */
function community_gallery_tags_upload_media_capabilities( $allcaps, $caps, $args, $user ) {
	// If one of our checks for `to51_upload_files` happens, always grant it if the user has `upload_files` already.
	if ( in_array( 'to51_upload_files', $caps ) && ! empty( $allcaps['upload_files'] ) ) {
		$allcaps['to51_upload_files'] = true;
	}

	// Only bother digging if it's a REST API endpoint.
	if ( did_action( 'parse_request' ) && wp_is_rest_endpoint() ) {
		$needs_additional_caps = false;
		foreach ( $caps as $cap ) {
			if ( empty( $allcaps[ $cap ] ) ) {
				$needs_additional_caps = true;
			}
		}
		// If they don't need more caps than what they've got, return early.
		if ( ! $needs_additional_caps ) {
			return $allcaps;
		}

		// If it's a request to the media endpoint ...
		$rest_route = $GLOBALS['wp']->query_vars['rest_route'];
		if ( '/wp/v2/media' === $rest_route ) {
			// Make sure that we're only allowing it if the image is being uploaded to a post that contains our block.
			$for_post_id = (int) $_REQUEST['post'];
			if ( $for_post_id && has_block( 'masae-blocks/upload', $for_post_id ) ) {
				// And make sure we're only allowing it for users that have our upload capability...
				// Make sure that whatever this meta cap is, that it doesn't recursion back to what we're looking at here!!!
				if ( $user->has_cap( 'to51_upload_files' ) ) {
					// If the current check cares about whether the user can upload files...
					if ( in_array( 'upload_files', (array) $caps ) ) {
						// Then let's allow them the capability for this request..
						$allcaps['upload_files'] = true;
					}

					// If the check cares about the user can edit the post it's being attached to ... allow that for attaching the media to it.
					if ( ( 'edit_post' === $args[0] ) && ( $args[2] === $for_post_id ) ) {
						$for_post = get_post( $for_post_id );
						$ptobject = get_post_type_object( $for_post->post_type );
						// Note that here we're checking the cap passed in originally, not the caps it could match to -- which could be `edit_others_pages` and `edit_published_pages` or a dozen others.
						// This may be better eventually to re-map on the `map_meta_cap` filter for this request?  Unlikely though.
						// Also worth nothing this extra bit of code is to account for the block being on different post types -- so we can override only the right caps.
						foreach ( $caps as $cap ) {
							if ( in_array( $cap, array( $ptobject->cap->edit_others_posts, $ptobject->cap->edit_published_posts ) ) ) {
								$allcaps[ $cap ] = true;
							}
						}
					}
				}
			}
		}
	}

	return $allcaps;
}

add_filter( 'user_has_cap', 'community_gallery_tags_upload_media_capabilities', 10, 4 );
