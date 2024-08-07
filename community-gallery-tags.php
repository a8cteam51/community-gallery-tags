<?php
/**
 * Plugin Name:       Community Gallery Tags
 * Description:       Example block scaffolded with Create Block tool.
 * Requires at least: 6.1
 * Requires PHP:      7.0
 * Version:           1.1.0
 * Author:            The WordPress Contributors
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       community-gallery-tags
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Register `people` taxonomy for use on attachments.
 *
 * @return void
 */
function cgt_add_categories_to_attachments() {
	register_taxonomy(
		'people',
		'attachment',
		array(
			'label'             => __( 'People', 'community-gallery-tags' ),
			'sort'              => true,
			'show_in_rest'      => true,
			'public'            => true,
			'show_admin_column' => true,
			'rewrite'           => array(
				'slug' => 'person',
			),
		)
	);
}
add_action( 'init', 'cgt_add_categories_to_attachments' );

/**
 * Modify archive pages for the specified taxonomy to include attachments.
 *
 * @param WP_Query $query The query we're modifying.
 *
 * @return WP_Query
 */
function cgt_include_attachments_in_people_pages( $query ) {
	// Don't change anything admin-side.
	if ( is_admin() ) {
		return $query;
	}

	if ( $query->is_tax( 'people' ) ) {
		// Make sure we enable all post types that it the taxonomy is enabled for.
		$people = get_taxonomy( 'people' );
		$query->set( 'post_type', $people->object_type );

		// Attachments have the `inherit` post_status -- make sure we enable that.
		$post_statii   = (array) $query->get( 'post_status' );
		$post_statii[] = 'inherit';
		$query->set( 'post_status', array_unique( $post_statii ) );
	}

	return $query;
}
add_filter( 'pre_get_posts', 'cgt_include_attachments_in_people_pages' );

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 *
 * @return void
 */
function community_gallery_tags_gallery_block_init() {
	register_block_type(
		__DIR__ . '/build/gallery',
		array(
			'render_callback' => 'community_gallery_tags_gallery__render_callback',
		)
	);
	register_block_type(
		__DIR__ . '/build/single',
		array(
			'render_callback' => 'community_gallery_tags_single__render_callback',
		)
	);
}
add_action( 'init', 'community_gallery_tags_gallery_block_init' );

/**
 * Render callback for the Community Gallery Tags - Gallery block.
 *
 * @param mixed $block_attributes The attributes set on the block.
 *
 * @return string
 */
function community_gallery_tags_gallery__render_callback( $block_attributes ) {
	global $wpdb;

	add_action( 'wp_footer', 'community_gallery_tags_gallery__js_template' );

	/**
	 * This is normally only registered for wp-admin usage, so we have to do it manually.
	 */
	wp_register_script( 'tags-suggest', '/wp-admin/js/tags-suggest.min.js', array( 'jquery-ui-autocomplete', 'wp-a11y' ), $GLOBALS['wp_version'], true );
	wp_set_script_translations( 'tags-suggest' );

	wp_enqueue_script(
		'community-gallery-tags',
		plugins_url( 'js/gallery.js', __FILE__ ),
		array(
			'wp-util',
			'wp-api-request',
			'jquery',
			'jquery-ui-dialog',
			'tags-suggest',
			'masonry',
		),
		'27mar2024',
		true
	);
	wp_enqueue_style( 'wp-jquery-ui-dialog' );

	$post_id = 0; // We can allow customizing this via block attributes if we'd like.  `0` will default to the global $post object on render.

	add_filter( 'get_attached_media', 'array_reverse' );
	$attachments = get_attached_media( 'image', $post_id ); // can change the first argument to an empty string if we want everything including videos.
	remove_filter( 'get_attached_media', 'array_reverse' );

	// Get the user's unreviewed suggestions, so we can show them.
	$unreviewed = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT * FROM `{$wpdb->postmeta}` WHERE `meta_key` = '_cgt_suggested_tag' AND `meta_value` LIKE %s ORDER BY `post_id` ASC",
			'%\"' . $wpdb->esc_like( wp_get_current_user()->user_login ) . '\"%'
		)
	);

	if ( $unreviewed ) {
		$unreviewed_clustered = array();
		foreach ( $unreviewed as $unreviewed_suggestion ) {
			$unreviewed_clustered[ "post-{$unreviewed_suggestion->post_id}" ][] = $unreviewed_suggestion;
		}
	}

	$has_event_photos    = isset( $block_attributes['officialPhotosID'] ) && intval( $block_attributes['officialPhotosID'] ) > 0;
	$event_photo_user_id = $has_event_photos ? intval( $block_attributes['officialPhotosID'] ) : 0;

	$return = '<div ' . get_block_wrapper_attributes( array( 'class' => 'gallery' ) ) . ">\r\n";

	if ( $has_event_photos ) {
		$return .= '<p id="cgt-filters" class="gallery-caption">';
		$return .= '<a href="javascript:;" class="all-images selected" data-uploader-id="">' . esc_html__( 'All Photos', 'community-gallery-tags' ) . '</a> ';
		$return .= '<a href="javascript:;" class="my-images" data-uploader-id="official">' . esc_html__( 'Official Photos', 'community-gallery-tags' ) . '</a>';
		$return .= '<a href="javascript:;" class="my-images" data-uploader-id="community">' . esc_html__( 'Community Photos', 'community-gallery-tags' ) . '</a>';
		$return .= '</p>';
	}

	$return .= "<ul class='community-gallery-tags-gallery' data-event-author='$event_photo_user_id'>\r\n";

	foreach ( $attachments as $item ) {
		$photo_type = $has_event_photos && intval( $item->post_author ) === $event_photo_user_id ? 'official' : 'community';

		$return .= "\t<li class='media gallery-item attachment-{$item->ID} uploader-id-{$photo_type}'>\r\n" .
			"\t<a href='" . esc_url( get_attachment_link( $item->ID ) ) . "'>\r\n" .
			"\t\t" . wp_get_attachment_image( $item->ID, 'medium' ) . "\r\n" .
			"\t</a>\r\n" .
			"\t\t<ul class='term-list'>" .
				get_the_term_list( $item->ID, 'people', '<li class="gallery-caption">', '</li><li class="gallery-caption">', '</li>' );

		// Populate in the user's pending suggestions...
		if ( isset( $unreviewed_clustered[ "post-{$item->ID}" ] ) ) {
			foreach ( $unreviewed_clustered[ "post-{$item->ID}" ] as $unreviewed_suggestion ) {
				$meta_value = maybe_unserialize( $unreviewed_suggestion->meta_value );
				$return    .= '<li>' . esc_html( $meta_value['tag'] ) . '</li>';
			}
		}

		$return .= "</ul>\r\n";

		if ( current_user_can( 'cgt_tag_media' ) ) {
			$return .= "\t\t<span class='gallery-caption'>" . sprintf( '<a class="add-tag hide-if-no-js" href="javascript:;" data-media-id="%d">%s</a>', $item->ID, __( '＋&nbsp;Tag&nbsp;Name', 'community-gallery-tags' ) ) . "</span>\r\n";
		}

		$return .= "\t</li>\r\n";
	}

	$return .= "</ul>\r\n";
	$return .= "</div>\r\n";

	// Overrides to trick Jetpack Carousel into working --
	$block_attributes['blockName'] = 'core/gallery';

	// phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
	$return = apply_filters( 'render_block_core/gallery', $return, $block_attributes );

	$user = wp_get_current_user();

	if ( isset( $user->roles ) && in_array( 'administrator', $user->roles, true ) ) {
		$unreviewed_total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$wpdb->postmeta}` WHERE `meta_key` = '_cgt_suggested_tag'" );

		if ( $unreviewed_total > 0 ) {
			// translators: 1: link to admin page, 2: qty unreviewed submissions
			$return = '<p>' . sprintf( __( 'There are currently <a href="%1$s" target="_blank">%2$d tag submissions to review.</a>', 'community-gallery-tags' ), esc_url( admin_url( 'upload.php?page=cgt-management' ) ), $unreviewed_total ) . "</a></p>\r\n" . $return;
		}
	}

	return $return;
}

/**
 * Render callback for dynamic block -- just the "add tag" for archive / single attachment page.
 *
 * @return string
 */
function community_gallery_tags_single__render_callback() {
	// We're only tagging attachments.
	if ( 'attachment' !== get_post_type() ) {
		return null;
	}

	// Don't bother if the current user can't see it.
	if ( ! current_user_can( 'cgt_tag_media' ) ) {
		return null;
	}

	add_action( 'wp_footer', 'community_gallery_tags_gallery__js_template' );

	/**
	 * This is normally only registered for wp-admin usage, so we have to do it manually.
	 */
	wp_register_script( 'tags-suggest', '/wp-admin/js/tags-suggest.min.js', array( 'jquery-ui-autocomplete', 'wp-a11y' ), $GLOBALS['wp_version'], true );
	wp_set_script_translations( 'tags-suggest' );

	wp_enqueue_script(
		'community-gallery-tags-single',
		plugins_url( 'js/single.js', __FILE__ ),
		array(
			'wp-api-request',
			'jquery',
			'jquery-ui-dialog',
			'tags-suggest',
		),
		'27mar2024',
		true
	);
	wp_enqueue_style( 'wp-jquery-ui-dialog' );

	$return  = '<div ' . get_block_wrapper_attributes() . ">\r\n";
	$return .= "\t" . sprintf( '<a class="add-tag hide-if-no-js" href="javascript:;" data-media-id="%d">%s</a>', get_the_ID(), __( '＋&nbsp;Tag&nbsp;Name', 'community-gallery-tags' ) ) . "\r\n";
	$return .= '</div>';

	return $return;
}

/**
 * Make sure that if there are suggestions -- even if no attached terms -- that it will show *something*.
 *
 * @param string $content      The generated content for the terms block.
 * @param array  $parsed_block The properties on the block in the editor.
 *
 * @return string
 */
function cgt_filter_post_terms_block( $content, $parsed_block ) {
	if ( ! empty( $content ) ) {
		return $content;
	}

	if ( isset( $parsed_block['attrs']['term'] ) && 'people' === $parsed_block['attrs']['term'] ) {
		// the suggestions are already esc_html'd down below.
		// phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
		$term_link_suggestions = apply_filters( 'term_links-people', array() );
		if ( $term_link_suggestions ) {
			$content  = '<div class="taxonomy-people has-text-align-center wp-block-post-terms has-small-font-size">';
			$content .= implode( ', ', $term_link_suggestions );
			$content .= '</div>';
		}
	}

	return $content;
}
add_filter( 'render_block_core/post-terms', 'cgt_filter_post_terms_block', 20, 2 );

/**
 * Add the user's prior suggestions to the output.
 */
add_filter(
	'term_links-people',
	function ( $term_links ) {
		global $wpdb;

		// Only append if we're on a `people` taxonomy page or an attachment page for a post that has the tagging gallery.
		if ( is_tax( 'people' ) || ( is_attachment() && has_block( 'community-gallery-tags/gallery', get_post_parent() ) ) ) {
			// Get the user's unreviewed suggestions, so we can show them.
			$unreviewed = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM `{$wpdb->postmeta}` WHERE `meta_key` = '_cgt_suggested_tag' AND `meta_value` LIKE %s AND `post_id` = %d",
					'%\"' . $wpdb->esc_like( wp_get_current_user()->user_login ) . '\"%',
					get_the_ID()
				)
			);

			if ( $unreviewed ) {
				foreach ( $unreviewed as $unreviewed_suggestion ) {
					$meta_value   = maybe_unserialize( $unreviewed_suggestion->meta_value );
					$term_links[] = esc_html( $meta_value['tag'] );
				}
			}
		}

		return $term_links;
	}
);

/**
 * Output relevant js templates that be needed for our tag work.
 *
 * @return void
 */
function community_gallery_tags_gallery__js_template() {
	?>
	<script>
		var ajaxurl = '<?php echo esc_js( admin_url( 'admin-ajax.php', 'relative' ) ); ?>'
	</script>
	<script type="text/html" id="tmpl-cgt-item">
		<li class="media attachment-{{ data.id }} uploader-id-{{ data.uploader }}">
			<a href="{{ data.link }}">{{{ data.img_tag }}}</a>
			<ul class="term-list"></ul>
			<?php if ( current_user_can( 'cgt_tag_media' ) ) : ?>
			<a class="add-tag hide-if-no-js" href="javascript:;" data-media-id="{{ data.id }}"><?php esc_html_e( '＋&nbsp;Tag&nbsp;Name', 'community-gallery-tags' ); ?></a>
			<?php endif; ?>
		</div>
	</script>

	<?php if ( current_user_can( 'cgt_tag_media' ) ) : ?>
	<div id="cgt-dialog-form" title="Tag Photo" class="hide-if-no-js">
		<form>
			<input type="hidden" name="attachment_id" value="" />

			<label for="cgt-tag"><?php esc_html_e( 'Name to Tag:', 'community-gallery-tags' ); ?></label>
			<input type="text" name="tag" id="cgt-tag" value="" class="text ui-widget-content ui-corner-all">
			<label for="cgt-tag" class="helper-text"><?php esc_html_e( 'Multiple tags can be seperated by commas.', 'community-gallery-tags' ); ?></label>

			<!-- Allow form submission with keyboard without duplicating the dialog button -->
			<input type="submit" tabindex="-1" style="position:absolute; top:-1000px">
		</form>
	</div>
		<?php
	endif;
}

add_filter(
	'user_has_cap',
	function ( $allcaps, $caps, $args, $user ) {
		// If one of our checks for `cgt_tag_media` happens, always grant it if the user can assign terms to the people taxonomy already.
		if ( in_array( 'cgt_tag_media', $caps, true ) ) {
			if ( ! empty( $allcaps[ get_taxonomy( 'people' )->cap->assign_terms ] ) ) {
				$allcaps['cgt_tag_media'] = true;
			}
		}

		// Only override if we're doing the ajax tag search...
		if ( doing_action( 'wp_ajax_ajax-tag-search' ) ) {
			// If they don't need more caps than what they've got, return early.
			$needs_additional_caps = false;
			foreach ( $caps as $cap ) {
				if ( empty( $allcaps[ $cap ] ) ) {
					$needs_additional_caps = true;
				}
			}
			if ( ! $needs_additional_caps ) {
				return $allcaps;
			}

			$people = get_taxonomy( 'people' );

			// If the current check cares about whether the user can assign terms...
			if ( in_array( $people->cap->assign_terms, (array) $caps, true ) ) {
				// If we would ordinarily allow them to suggest tags (even though suggestion isn't directly adding) ...
				if ( $user->has_cap( 'cgt_tag_media' ) ) {
					// Then let's allow this permission check for the moment so they can query our taxonomy.
					$allcaps[ $people->cap->assign_terms ] = true;
				}
			}
		}

		return $allcaps;
	},
	10,
	4
);

add_action(
	'rest_api_init',
	function () {
		register_rest_route(
			'community-gallery-tags/v1',
			'/suggest-tag',
			array(
				'methods'             => 'POST',
				'callback'            => 'community_gallery_tags_endpoint__suggest_tag',
				'args'                => array(
					'attachment_id' => array(
						'validate_callback' => function ( $param ) {
							return is_numeric( $param );
						},
					),
					'tag'           => array(
						'type'     => 'string',
						'required' => true,
					),
				),
				'permission_callback' => function () {
					return current_user_can( 'cgt_tag_media' );
				},
			)
		);
	}
);

/**
 * REST API Endpoint -- take the suggestion, shove it into postmeta for the attachment.
 *
 * @param \WP_REST_Request $request The incoming REST API request.
 *
 * @return \WP_REST_Response
 */
function community_gallery_tags_endpoint__suggest_tag( WP_REST_Request $request ) {
	$comma         = _x( ',', 'tag delimiter', 'community-gallery-tags' );
	$raw_tag       = $request->get_param( 'tag' );
	$attachment_id = $request->get_param( 'attachment_id' );

	if ( 'attachment' !== get_post_type( $attachment_id ) ) {
		return new WP_Error( 'bad-attachment-id', __( 'The specified attachment ID does not seem to be valid.', 'community-gallery-tags' ) );
	}

	$tags = explode( $comma, $raw_tag );
	$tags = array_map( 'trim', $tags );
	$tags = array_filter( $tags );

	$meta_ids = array();

	foreach ( $tags as $tag ) {
		$meta_id = add_post_meta(
			$attachment_id,
			'_cgt_suggested_tag',
			array(
				'tag'  => $tag,
				'user' => wp_get_current_user()->user_login,
			)
		);

		if ( ! $meta_id || is_wp_error( $meta_id ) ) {
			return $meta_id;
		}

		$meta_ids[ $meta_id ] = $tag;
	}

	return new WP_REST_Response( $meta_ids, 200 );
}

// Add the admin page to view and manage the suggestions --

add_action(
	'admin_menu',
	function () {
		$hook_suffix = add_media_page(
			__( 'Community Gallery Tags Management', 'community-gallery-tags' ),
			__( 'CGT Admin', 'community-gallery-tags' ),
			get_taxonomy( 'people' )->cap->assign_terms,
			'cgt-management',
			'community_gallery_tags__admin_page'
		);
	}
);

/**
 * Admin page for CGT
 *
 * @return void
 */
function community_gallery_tags__admin_page() {
	global $wpdb;

	$_to_review = $wpdb->get_results( "SELECT * FROM `{$wpdb->postmeta}` WHERE `meta_key` = '_cgt_suggested_tag' ORDER BY `post_id` ASC" );

	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Custom Gallery Tags Management', 'community-gallery-tags' ); ?></h1>

		<?php if ( count( $_to_review ) ) : ?>
		<table>
			<thead>
				<tr>
					<th><?php esc_html_e( 'Attachment', 'community-gallery-tags' ); ?></th>
					<th><?php esc_html_e( 'Suggestion', 'community-gallery-tags' ); ?></th>
					<th><?php esc_html_e( 'Tags already on Media', 'community-gallery-tags' ); ?></th>
					<th><?php esc_html_e( 'Action', 'community-gallery-tags' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
				foreach ( $_to_review as $suggestion ) :
					$proposal = maybe_unserialize( $suggestion->meta_value );
					?>
					<tr>
						<th scope="row">
							<?php echo wp_get_attachment_image( $suggestion->post_id, 'medium' ); ?>
						</th>
						<td>
							<pre><?php echo esc_html( $proposal['tag'] ); ?></pre>
							<?php if ( $proposal['user'] ) : ?>
								<?php /* translators: %s: User login. */ ?>
								<p><?php printf( __( 'By User <strong>%s</strong>', 'community-gallery-tags' ), esc_html( $proposal['user'] ) ); ?></p>
							<?php endif; ?>
						</td>
						<td>
							<?php echo get_the_term_list( $suggestion->post_id, 'people', '', ', ', '' ); ?>
						</td>
						<td>
							<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
								<?php wp_nonce_field( 'cgt_moderate_tags-' . $suggestion->meta_id, '_cgtnonce' ); ?>
								<input name="action" type="hidden" value="cgt_moderate_tags" />
								<input name="attachment_id" type="hidden" value="<?php echo esc_attr( $suggestion->post_id ); ?>" />
								<input name="postmeta_id" type="hidden" value="<?php echo esc_attr( $suggestion->meta_id ); ?>" />
								<input name="tag" type="text" value="<?php echo esc_attr( $proposal['tag'] ); ?>" />
								<br />
								<?php
								submit_button(
									__( 'Add', 'community-gallery-tags' ),
									'primary small',
									'add-tag',
									false
								);
								echo '&nbsp;';
								submit_button(
									__( 'Delete', 'community-gallery-tags' ),
									'delete small',
									'delete-suggestion',
									false
								);
								?>
							</form>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php else : ?>
			<p><?php esc_html_e( 'There are no pending suggestions for tags on your media items.', 'community-gallery-tags' ); ?></p>
		<?php endif; ?>

	</div>
	<?php
}

/**
 * Fallback for sans-javascript-hijinks.
 */
add_action(
	'admin_post_cgt_moderate_tags',
	function () {
		check_admin_referer( 'cgt_moderate_tags-' . $_POST['postmeta_id'], '_cgtnonce' );

		$postmeta_id   = intval( $_POST['postmeta_id'] );
		$attachment_id = intval( $_POST['attachment_id'] );

		if ( 'attachment' !== get_post_type( $attachment_id ) ) {
			wp_die( new WP_Error( 'bad-attachment-id', esc_html__( 'The specified attachment ID does not seem to be valid.', 'community-gallery-tags' ) ) );
		}

		if ( isset( $_POST['add-tag'] ) ) {
			$new_tag = sanitize_text_field( $_POST['tag'] );

			$result = wp_set_post_terms(
				$attachment_id,
				$new_tag,
				'people',
				true // IMPORTANT! Don't replace, just append.
			);

			if ( $result ) {
				delete_metadata_by_mid( 'post', (int) $postmeta_id );
			}
		} elseif ( isset( $_POST['delete-suggestion'] ) ) {
			$result = delete_metadata_by_mid( 'post', (int) $postmeta_id );
		}

		wp_safe_redirect( admin_url( 'upload.php?page=cgt-management' ) );
	}
);

/**
 * Enables attachment pages for WP 6.4+.
 */
add_action( 'pre_option_wp_attachment_pages_enabled', '__return_true' );
