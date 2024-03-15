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
 * Register `people` taxonomy for use on attachments.
 */
function cgt_add_categories_to_attachments() {
	register_taxonomy(
		'people',
		'attachment',
		array(
			'label'             => __( 'People' ),
			'sort'              => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'rewrite'           => array(
				'slug' => 'person'
			),
		)
	);
}
add_action( 'init' , 'cgt_add_categories_to_attachments' );

/**
 * Modify archive pages for the specified taxonomy to include attachments.
 *
 * @param WP_Query $query
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
		$post_statii = (array) $query->get( 'post_status' );
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
	global $wpdb;

	add_action( 'wp_footer', 'community_gallery_tags_gallery__js_template' );
	wp_enqueue_script(
		'community-gallery-tags',
		plugins_url( 'js/gallery.js', __FILE__ ),
		array(
			'wp-util',
			'wp-api-request',
			'jquery',
			'jquery-ui-dialog',
		),
		false,
		true
	);
	wp_enqueue_style( 'wp-jquery-ui-dialog' );

	$post_id = 0; // We can allow customizing this via block attributes if we'd like.  `0` will default to the global $post object on render.
	$attachments = get_attached_media( 'image', $post_id ); // can change the first argument to an empty string if we want everything including videos.

	$return = "<ul class='community-gallery-tags-gallery'>\r\n"; // @todo: add support for adding classes to this `ul` in the block editor.

	// Get the user's unreviewed suggestions, so we can show them.
	$unreviewed = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT * FROM `{$wpdb->postmeta}` WHERE `meta_key` = '_cgt_suggested_tag' AND `meta_value` LIKE '%%\"%s\"%%' ORDER BY `post_id` ASC",
			wp_get_current_user()->user_login
		)
	);

	if ( $unreviewed ) {
		$unreviewed_clustered = array();
		foreach ( $unreviewed as $unreviewed_suggestion ) {
			$unreviewed_clustered[ "post-{$unreviewed_suggestion->post_id}" ][] = $unreviewed_suggestion;
		}
	}

	foreach ( $attachments as $item ) {
		$return .= "\t<li class='attachment-{$item->ID}'>\r\n" .
			"\t\t" . wp_get_attachment_image( $item->ID ) . "\r\n" .
			"\t\t<ul class='term-list'>" .
				get_the_term_list( $item->ID, 'people', '<li>', '</li><li>', '</li>' );

		// Populate in the user's pending suggestions...
		if ( isset( $unreviewed_clustered[ "post-{$item->ID}" ] ) ) {
			foreach ( $unreviewed_clustered[ "post-{$item->ID}" ] as $unreviewed_suggestion ) {
				$meta_value = maybe_unserialize( $unreviewed_suggestion->meta_value );
				$return .= "<li>" . esc_html( $meta_value['tag'] ) . "</li>";
			}
		}

		$return .= "</ul>\r\n";

		if ( current_user_can( 'cgt_tag_media' ) ) {
			$return .= "\t\t" . sprintf( '<a class="add-tag hide-if-no-js" href="javascript:;" data-attachment-id="%d">%s</a>', $item->ID, __( '➕ Tag?', 'community-gallery-tags' ) ) . "\r\n";
		}

		$return .= "\t</li>\r\n";
	}

	$return .= "</ul>\r\n";

	return $return;
}

function community_gallery_tags_gallery__js_template() {
	?>
	<script type="text/html" id="tmpl-cgt-item">
		<li class="attachment-{{ data.id }}">
			{{{ data.img_tag }}}
			<ul class="term-list"></ul>
			<?php if ( current_user_can( 'cgt_tag_media' ) ) : ?>
			<a class="add-tag hide-if-no-js" href="javascript:;" data-attachment-id="{{ data.id }}"><?php _e( '➕ Tag?', 'community-gallery-tags' ) ?></a>
			<?php endif; ?>
		</div>
	</script>

	<?php if ( current_user_can( 'cgt_tag_media' ) ) : ?>
	<div id="cgt-dialog-form" title="Tag Photo" class="hide-if-no-js">
		<form>
			<fieldset>
				<input type="hidden" name="attachment_id" value="" />

				<label for="cgt-tag">Person to Tag:</label><br />
				<input type="text" name="tag" id="cgt-tag" value="" class="text ui-widget-content ui-corner-all">

				<!-- Allow form submission with keyboard without duplicating the dialog button -->
				<input type="submit" tabindex="-1" style="position:absolute; top:-1000px">
			</fieldset>
		</form>
	</div>
	<?php endif;
}

/**
 * If you'd like to override this to check other caps, use a later priority!
 *
 * `exist` will work for even unauthenticated users.
 * `read` will require login.
 */
add_filter( 'map_meta_cap', function ( array $caps, string $cap ) {
	switch ( $cap ) {
		case 'cgt_tag_media':
			$caps = array( 'exist' );
			break;
	}

	return $caps;
}, 1, 2 );

add_action( 'rest_api_init', function() {
	register_rest_route(
		'community-gallery-tags/v1',
		'/suggest-tag',
		array(
			'methods'             => 'POST',
			'callback'            => 'community_gallery_tags_endpoint__suggest_tag',
			'permission_callback' => function() {
				return current_user_can( 'cgt_tag_media' );
			}
		)
	);
});

/**
 * REST API Endpoint -- take the suggestion, shove it into postmeta for the attachment.
 */
function community_gallery_tags_endpoint__suggest_tag( WP_REST_Request $request ) {
	$tag           = $request->get_param( 'tag' );
	$attachment_id = $request->get_param( 'attachment_id' );

	if ( 'attachment' !== get_post_type( $attachment_id ) ) {
		return new WP_Error( 'bad-attachment-id', __( 'The specified attachment ID does not seem to be valid.' ) );
	}

	$meta_id = add_post_meta(
		$attachment_id,
		'_cgt_suggested_tag',
		array(
			'tag' => $tag,
			'user' => wp_get_current_user()->user_login
		)
	);

	if ( ! $meta_id || is_wp_error( $meta_id ) ) {
		return $meta_id;
	}

	return new WP_REST_Response( $meta_id, 200 );
}

// Add the admin page to view and manage the suggestions --

add_action( 'admin_menu', function() {
	$hook_suffix = add_media_page(
		__( 'Custom Gallery Tags Management' ),
		__( 'CGT Admin' ),
		'upload_files',
		'cgt-management',
		'custom_gallery_tags__admin_page'
	);
});

function custom_gallery_tags__admin_page() {
	global $wpdb;

	$_to_review = $wpdb->get_results( "SELECT * FROM `{$wpdb->postmeta}` WHERE `meta_key` = '_cgt_suggested_tag' ORDER BY `post_id` ASC" );

	?>
	<div class="wrap">
		<h1><?php _e( 'Custom Gallery Tags Management' ); ?></h1>

		<?php if ( count( $_to_review ) ) : ?>
		<table>
			<thead>
				<tr>
					<th>Attachment</th>
					<th>Suggestion</th>
					<th>Tags already on Media</th>
					<th>Action</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $_to_review as $suggestion ) :
					$proposal = maybe_unserialize( $suggestion->meta_value );
					?>
					<tr>
						<th scope="row">
							<?php echo wp_get_attachment_image( $suggestion->post_id, 'medium' ); ?>
						</th>
						<td>
							<pre><?php echo esc_html( $proposal['tag'] ); ?></pre>
							<?php if ( $proposal['user'] ) : ?>
								<p>By User <strong><?php echo esc_html( $proposal['user'] ); ?></strong></p>
							<?php endif; ?>
						</td>
						<td>
							<?php echo get_the_term_list( $suggestion->post_id, 'people', '', ', ', '' ); ?>
						</td>
						<td>
							<form action="<?php echo admin_url( 'admin-post.php' ); ?>" method="post">
								<input name="action" type="hidden" value="cgt_moderate_tags" />
								<input name="attachment_id" type="hidden" value="<?php echo esc_attr( $suggestion->post_id ); ?>" />
								<input name="postmeta_id" type="hidden" value="<?php echo esc_attr( $suggestion->meta_id ); ?>" />
								<input name="tag" type="text" value="<?php echo esc_attr( $proposal['tag'] ); ?>" />
								<br />
								<?php
								submit_button(
									'Add',
									'primary small',
									'add-tag',
									false
								);
								echo "&nbsp;";
								submit_button(
									'Delete',
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
			<p><?php esc_html_e( 'There are no pending suggestions for tags on your media items.' ); ?></p>
		<?php endif; ?>

	</div>
	<?php
}

/**
 * Fallback for sans-javascript-hijinks.
 */
add_action( 'admin_post_cgt_moderate_tags', function() {
	$postmeta_id = intval( $_POST['postmeta_id'] );
	$attachment_id = intval( $_POST['attachment_id'] );

	if ( 'attachment' !== get_post_type( $attachment_id ) ) {
		wp_die( new WP_Error( 'bad-attachment-id', __( 'The specified attachment ID does not seem to be valid.' ) ) );
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
	} else {
		// No recognized action.  Do nothing.
	}

	wp_safe_redirect( admin_url( 'upload.php?page=cgt-management' ) );
} );
