<?php

if ( ! isset( $block->context['postId'] ) ) {
	return '';
}

$community_gallery_tags_blocks_id   = $block->context['postId'];
$community_gallery_tags_blocks_type = get_post_mime_type( $community_gallery_tags_blocks_id );
$community_gallery_tags_blocks_name = str_contains( $community_gallery_tags_blocks_type, 'image' ) ? 'image' : 'video';

?>

<div <?php echo wp_kses_data( get_block_wrapper_attributes( array( 'class' => 'type-' . $community_gallery_tags_blocks_name ) ) ); ?> >
<?php
if ( str_contains( $community_gallery_tags_blocks_type, 'image' ) ) {
	if ( is_tax() || is_author() ) {
		printf(
			'<a href="%s">%s</a>',
			esc_url( get_attachment_link( $community_gallery_tags_blocks_id ) ),
			wp_get_attachment_image( $community_gallery_tags_blocks_id, 'full' )
		);
	} else {
		echo wp_get_attachment_image( $community_gallery_tags_blocks_id, 'full' );
	}
}

if ( str_contains( $community_gallery_tags_blocks_type, 'video' ) ) {
	printf(
		'<video controls><source src="%s" type="%s"></video>',
		esc_url( wp_get_attachment_url( $community_gallery_tags_blocks_id ) ),
		esc_attr( $community_gallery_tags_blocks_type )
	);
}
?>
</div>
