/**
 * WordPress dependencies
 */
import { __ } from "@wordpress/i18n";
import { useBlockProps, InspectorControls } from "@wordpress/block-editor";
import {
	Disabled,
	SelectControl,
	PanelBody,
	TextControl,
} from "@wordpress/components";

/**
 * Internal dependencies
 */
import MediaUploads from "./MediaUploads";

import "./editor.scss";

export default function Edit({ attributes, setAttributes }) {
	const { buttonText } = attributes;

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( "Settings", "community-gallery-tags" ) } initialOpen={true}>
					<TextControl
						label={ __( "Button Text", "community-gallery-tags" ) }
						value={ buttonText }
						onChange={ ( newText ) => setAttributes( { buttonText: newText } ) }
					/>
				</PanelBody>
			</InspectorControls>

			<div {...useBlockProps()}>
				<Disabled>
					<div class="community-gallery-tags-uploads">
						<MediaUploads id={0} buttonText={buttonText} />
					</div>
				</Disabled>
			</div>
		</>
	);
}
