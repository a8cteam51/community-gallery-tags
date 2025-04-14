<?php

if ( ! isset( $block->context['postId'] ) ) {
	return '';
}

?>
<div <?php echo wp_kses_data( get_block_wrapper_attributes() ); ?> >
	<div class="media-attachment-navigation_prev">
		<?php echo wp_kses_post( get_adjacent_image_link( true, false, false ) ); ?>
	</div>
	<div class="media-attachment-navigation_next">
		<?php echo wp_kses_post( get_adjacent_image_link( false, false, false ) ); ?>
	</div>
</div>
