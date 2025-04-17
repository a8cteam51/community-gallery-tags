<?php
/**
 * Render the people tag list block.
 *
 * @package Community_Gallery_Tags
 */

$cgt_attachments    = get_attached_media( 'image', get_the_ID() );
$cgt_attachment_ids = array_keys( $cgt_attachments );

?>
<div <?php echo wp_kses_data( get_block_wrapper_attributes() ); ?> >
	<?php if ( empty( $cgt_attachment_ids ) ) { ?>
		<p><?php esc_html_e( 'No images found, add some photos to the gallery and then tag your friends', 'community-gallery-tags' ); ?></p>
	<?php } else { ?>
		<p class="people-tax-list__title">
			<?php esc_html_e( 'Tagged', 'community-gallery-tags' ); ?>
		</p>

		<div class="people-tax-list__links">
			<?php
			wp_list_categories(
				array(
					'taxonomy'         => 'people',
					'hide_empty'       => true,
					'object_ids'       => $cgt_attachment_ids,
					'style'            => 'span',
					'separator'        => ', ',
					'show_option_none' => __( 'No tags found', 'community-gallery-tags' ),
				)
			);
			?>
		</div>
		<?php
		printf(
		/* translators: %s: number of people */
			'<button class="people-tax-list__show-all hidden">... <span class="people-tax">%s</span></button>',
			esc_html__( 'See All', 'community-gallery-tags' )
		);
		?>
	<?php } ?>
</div>
