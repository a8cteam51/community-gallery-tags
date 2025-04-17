<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Add some block templates for the `people` taxonomy and attachment pages.
 *
 * @return void
 */
function community_gallery_tags_people_fse_template() {
	$taxonomy_people_template = array(
		'title'       => __( 'People Archive', 'community-gallery-tags' ),
		'description' => __( 'A template to display all images taged with a person tag', 'community-gallery-tags' ),
		'content'     => '<!-- wp:template-part {"slug":"header","tagName":"header"} /-->

		<!-- wp:query {"queryId":2,"query":{"perPage":6,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":true,"taxQuery":null,"parents":[]},"tagName":"main","className":"people-taxomomy-archive","layout":{"contentSize":null,"type":"constrained"}} -->
		<main class="wp-block-query people-taxomomy-archive"><!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|20"}},"layout":{"type":"constrained"}} -->
		<div class="wp-block-group"><!-- wp:paragraph {"align":"center","style":{"elements":{"link":{"color":{"text":"var:preset|color|tertiary"}}}},"textColor":"tertiary","fontSize":"small","fontFamily":"space-mono-bold"} -->
		<p class="has-text-align-center has-tertiary-color has-text-color has-link-color has-space-mono-bold-font-family has-small-font-size">'
			. esc_html__( 'Tagged:', 'community-gallery-tags' ) .
		'</p>
		<!-- /wp:paragraph -->

		<!-- wp:query-title {"type":"archive","textAlign":"center","showPrefix":false,"style":{"spacing":{"margin":{"bottom":"100px"}}},"fontSize":"huge"} /--></div>
		<!-- /wp:group -->

		<!-- wp:post-template {"align":"wide","layout":{"type":"grid","columnCount":3}} -->
		<!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|20","margin":{"bottom":"var:preset|spacing|30"}}}} -->
		<div class="wp-block-group" style="margin-bottom:var(--wp--preset--spacing--30)"><!-- wp:community-gallery-tags/media-attachment /-->

		<!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|10"}},"layout":{"type":"flex","orientation":"vertical"}} -->
		<div class="wp-block-group"><!-- wp:post-terms {"term":"people","fontSize":"tiny"} /-->

		<!-- wp:community-gallery-tags/single {"fontSize":"small"} /--></div>
		<!-- /wp:group --></div>
		<!-- /wp:group -->
		<!-- /wp:post-template -->

		<!-- wp:group {"layout":{"inherit":true,"type":"constrained"}} -->
		<div class="wp-block-group"><!-- wp:query-pagination {"layout":{"type":"flex","justifyContent":"center"}} -->
		<!-- wp:query-pagination-previous /-->

		<!-- wp:query-pagination-numbers /-->

		<!-- wp:query-pagination-next /-->
		<!-- /wp:query-pagination --></div>
		<!-- /wp:group --></main>
		<!-- /wp:query -->

		<!-- wp:template-part {"slug":"footer","tagName":"footer"} /-->',
	);

	$attachment_template = array(
		'title'       => __( 'Attachment Pages', 'community-gallery-tags' ),
		'description' => __( 'A template to display the single attachments', 'community-gallery-tags' ),
		'content'     => '<!-- wp:template-part {"slug":"header","area":"header"} /-->

			<!-- wp:group {"tagName":"main","lock":{"move":false,"remove":false},"metadata":{"name":"Main"},"layout":{"type":"constrained"}} -->
			<main class="wp-block-group"><!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|40"}},"layout":{"inherit":true,"type":"constrained","contentSize":""}} -->
			<div class="wp-block-group"><!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|20","margin":{"top":"var:preset|spacing|40"}}},"fontSize":"small","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"center"}} -->
			<div class="wp-block-group has-small-font-size" style="margin-top:var(--wp--preset--spacing--40)"><!-- wp:post-date /-->

			<!-- wp:paragraph -->
			<p>—</p>
			<!-- /wp:paragraph -->

			<!-- wp:post-author-name {"isLink":true,"style":{"elements":{"link":{"color":{"text":"var:preset|color|secondary"}}}},"textColor":"secondary"} /--></div>
			<!-- /wp:group --></div>
			<!-- /wp:group -->

			<!-- wp:group {"style":{"spacing":{"margin":{"top":"var:preset|spacing|30"}}},"layout":{"type":"constrained"}} -->
			<div class="wp-block-group" style="margin-top:var(--wp--preset--spacing--30)"><!-- wp:community-gallery-tags/media-attachment /--></div>
			<!-- /wp:group -->

			<!-- wp:group {"className":"image-single-meta","style":{"spacing":{"blockGap":"var:preset|spacing|20"}},"fontSize":"small","layout":{"type":"constrained"}} -->
			<div class="wp-block-group image-single-meta has-small-font-size"><!-- wp:group {"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"center"}} -->
			<div class="wp-block-group"><!-- wp:post-terms {"term":"people","textAlign":"center","fontSize":"small"} /-->

			<!-- wp:community-gallery-tags/single /--></div>
			<!-- /wp:group -->

			<!-- wp:community-gallery-tags/media-attachment-navigation {"align":"full"} /--></div>
			<!-- /wp:group --></main>
			<!-- /wp:group -->

			<!-- wp:template-part {"slug":"footer","area":"footer"} /-->',
	);

	if ( function_exists( 'register_block_template' ) ) {
		register_block_template( 'community-gallery-tags//taxonomy-people', $taxonomy_people_template );
		register_block_template( 'community-gallery-tags//attachment', $attachment_template );
	}
}

add_action( 'init', 'community_gallery_tags_people_fse_template' );
