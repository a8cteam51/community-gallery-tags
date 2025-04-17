<?php

if ( ! current_user_can( 'to51_upload_files' ) ) {
	return;
}

$community_gallery_tags_upload_button_text = isset( $attributes['buttonText'] ) ? $attributes['buttonText'] : __( 'Upload Files', 'community-gallery-tags' );

?>
<div <?php echo wp_kses_data( get_block_wrapper_attributes() ); ?> >
	<div
		class="community-gallery-tags-uploads"
		data-id="<?php echo intval( get_the_id() ); ?>"
		data-buttonText="<?php echo esc_html( $community_gallery_tags_upload_button_text ); ?>"
	></div>
</div>
