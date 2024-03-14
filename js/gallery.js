(function( $, wp ){

	const tmplCgtItem = wp.template( 'cgt-item' );
	const $gallery = $('ul.community-gallery-tags-gallery');

	$gallery.on( 'click', 'a.add-tag', function(e) {
		e.preventDefault();

		const $element = $(e.target);
		const attachmentId = $element.data('attachment-id');

		let newTag = window.prompt( "What new tag?" );

		// If it's empty, don't bother.
		if ( ! newTag ) {
			return;
		}

		wp.apiRequest({
			path: '/community-gallery-tags/v1/suggest-tag',
			data: {
				attachment_id: attachmentId,
				tag: newTag
			},
			method: 'POST',
			dataType: 'json'
		}).done( ( response ) => {
			console.log( response );

			$element.siblings('.term-list').append( '<li>' + newTag + '</li>' );
		});

		console.log( "Tag '" + newTag + "' suggested for attachment " + attachmentId );

	});

	document.body.addEventListener( 'uploadedGalleryImage', function(e) {
		console.log( "Listening to 'uploadedGalleryImage' event." );
		console.log( e )

		$gallery.append( tmplCgtItem( {
			id:        data.id,
			img_tag:   '<img src="" />',
		} ) );
	});

}( jQuery, wp ));
