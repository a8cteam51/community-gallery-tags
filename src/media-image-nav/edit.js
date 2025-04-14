/**
 * WordPress dependencies
 */
import { __ } from "@wordpress/i18n";
import { useBlockProps } from "@wordpress/block-editor";

import "./editor.scss";

export default function Edit() {
	return (
		<div {...useBlockProps()}>
			<div class="media-attachment-navigation_prev">
				<a>{__("Previous Media", "masae-blocks")}</a>
			</div>
			<div class="media-attachment-navigation_next">
				<a>{__("Next Media", "masae-blocks")}</a>
			</div>
		</div>
	);
}
