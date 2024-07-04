/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';

import {
	ComboboxControl,
	PanelBody,
} from '@wordpress/components';

import { withSelect } from '@wordpress/data';

import { useState, useEffect } from '@wordpress/element';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './editor.scss';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {Element} Element to render.
 */
const GalleryEdit = withSelect( ( select ) => {
		const { isResolving } = select( 'core/data' );
		const query = { capabilities: 'edit_posts, to51_upload_files', per_page: -1};

		return {
			users: select( 'core' ).getEntityRecords( 'root', 'user', query ),
			isRequesting: isResolving( 'core', 'getEntityRecords', [ 'root', 'user', query ] ),
		};
	} )( ( props ) => {
	
	const { attributes, setAttributes, users, isRequesting } = props;
	const { officialPhotosID } = attributes;

	const [ usersList, setUsersList ] = useState( [] );
	const [ filteredOptions, setFilteredOptions ] = useState( [] );

	useEffect( () => {
		if ( users !== null ) {
			const userMap = users.map( ( user ) => {
				return {
					label: user.name,
					value: user.id,
				};
			});

			setFilteredOptions( userMap );
			setUsersList( userMap );
		}
	}, [ users, isRequesting ] );

	return (
		<>
		<InspectorControls>
			<PanelBody title={ __( 'Community Gallery Tags Settings', 'community-gallery-tags' ) }>
			<ComboboxControl
				label={ __( 'Select Official Authors', 'community-gallery-tags' ) }
				value={ officialPhotosID }
				onChange={ (value) => setAttributes({ officialPhotosID: Number( value ) }) }
				options={ usersList }
				onFilterValueChange={ ( inputValue ) =>
					setFilteredOptions(
						usersList.filter( ( option ) =>
							option.label === inputValue
						)
					)
				}
				help={ __( 'Select the authors whose images will appear in the official tab or leave blank', 'community-gallery-tags' ) }
			/>
			</PanelBody>
		</InspectorControls>
		<p { ...useBlockProps() }>
			{ __(
				'Community Gallery Tags – hello from the editor!',
				'community-gallery-tags'
			) }
		</p>
		</>
	);
});

export default GalleryEdit;