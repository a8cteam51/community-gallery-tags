/**
 * WordPress dependencies
 */
import { __ } from "@wordpress/i18n";
import { useBlockProps } from "@wordpress/block-editor";
import { Placeholder } from "@wordpress/components";

import "./editor.scss";

export default function Edit() {
	return (
		<div {...useBlockProps()}>
			<Placeholder label={__("Image or Video", "community-gallery-tags")} />
		</div>
	);
}
