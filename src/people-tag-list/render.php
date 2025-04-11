<div <?php echo wp_kses_data( get_block_wrapper_attributes() ); ?> >
	<p class="people-tax-list__title">
		<?php esc_html_e( 'Tagged', 'community-gallery-tags' ); ?>
	</p>

	<div class="people-tax-list__links">
	<?php
	wp_list_categories(
		array(
			'taxonomy'         => 'people',
			'hide_empty'       => true,
			'style'            => 'span',
			'separator'        => ', ',
			'show_option_none' => __( 'No people found', 'community-gallery-tags' ),
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
</div>
