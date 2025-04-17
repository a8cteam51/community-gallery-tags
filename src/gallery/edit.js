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
import { useBlockProps, InspectorControls, RichText } from '@wordpress/block-editor';

import {
	ComboboxControl,
	PanelBody,
} from '@wordpress/components';

import { withSelect, select } from '@wordpress/data';

import { useState, useEffect } from '@wordpress/element';

/**
 * External Dependencies
 */
import Masonry from 'masonry-layout';

import clsx from 'clsx';

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
		const postID = select("core/editor").getCurrentPostId();

		return {
			users: select( 'core' ).getEntityRecords( 'root', 'user', query ),
			isRequesting: isResolving( 'core', 'getEntityRecords', [ 'root', 'user', query ] ),
			media: select( 'core' ).getEntityRecords( 'root', 'media', { per_page: -1, parent: postID } ),
			peopleTaxonomy: select( 'core' ).getEntityRecords( 'taxonomy', 'people', { per_page: -1 } ),
		};
	} )( ( props ) => {
	
	const { attributes, setAttributes, users, isRequesting, media, peopleTaxonomy } = props;
	const { officialPhotosID, allPhotosText, officalPhotosText, communityPhotosText } = attributes;

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

	new Masonry( '.community-gallery-tags-gallery',
		{
			itemSelector: 'li.media:not(.hidden)',
			columnWidth: 300,
			gutter: 24,
			percentPosition: true,
		}
	);

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
		<style> {`
			.wp-block-community-gallery-tags-gallery .community-gallery-tags-gallery > li.media {
				width: 300px;
			}
		`}</style>
		<div { ...useBlockProps() }>
			
			{
				officialPhotosID ? (
					<p
						id="cgt-filters"
						className="gallery-caption"
					>
						<RichText
							tagName="a"
							value={ allPhotosText }
							allowedFormats={ [] }
							onChange={ ( content ) => setAttributes( { allPhotosText: content } ) }
							className="all-images selected"
							data-uploader-id=""
						/>
						<RichText
							tagName="a"
							value={ officalPhotosText }
							allowedFormats={ [] }
							onChange={ ( content ) => setAttributes( { officalPhotosText: content } ) }
							className="my-images"
							data-uploader-id="official"
						/>
						<RichText
							tagName="a"
							value={ communityPhotosText }
							allowedFormats={ [] }
							onChange={ ( content ) => setAttributes( { communityPhotosText: content } ) }
							className="my-images"
							data-uploader-id="community"
						/>
					</p>
				) : (
					<p
						id="cgt-filters"
						className="gallery-caption"
					>
						{ __( 'Choose an official photographer in the sidebar to view the tabs.', 'community-gallery-tags' ) }
					</p>
				)
			}
				
			{
				media && media.length > 0 ? (
					<ul className="community-gallery-tags-gallery">
						{ media && media.map( ( image ) => {
							return (
								<li className='media gallery-item' key={ image.id }>
									<img src={ image.source_url } />
									{
										image?.people && (
											<ul className="term-list">
												{
													image?.people.map( ( person ) => {
														const user = peopleTaxonomy?.find( ( item ) => {
															return item.id === person;
														});
					
														return (
															<li className="gallery-caption" key={ user?.id } >
																{ user?.name }
															</li>
														);
													})
												}
											</ul>
										)
									}
								</li>
							);
						} )}
					</ul>
				):(
					<p className="gallery-caption has-text-align-center">
						{ __( 'Images attached to this post will appear here.', 'community-gallery-tags' ) }
					</p>
				)
			}
		</div>
		</>
	);
});

export default GalleryEdit;