(function( $, wp ){

	const tmplCgtItem = wp.template( 'cgt-item' );
	const $gallery = $('ul.community-gallery-tags-gallery');

	$gallery.on( 'click', 'a.add-tag', function(e) {
		e.preventDefault();

		const $element = $(e.target);
		const attachmentId = $element.data('attachment-id');

		let newTag = window.prompt( "What new tag?" );

		if ( ! newTag ) {
			return;
		}

		console.log( "Tag '" + newTag + "' suggested for attachment " + attachmentId );

		$element.siblings('.term-list').append( '<li>' + newTag + '</li>' );
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
