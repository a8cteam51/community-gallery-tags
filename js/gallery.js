(function( $, wp ){

	const tmplCgtItem = wp.template( 'cgt-item' );
	const $gallery = $('ul.community-gallery-tags-gallery');
	const $dialogEl = $('#cgt-dialog-form');

	// Only bother with this stuff if we have the dialog form.  The dialog form will only render if the user can tag.
	if ( $dialogEl.length ) {
		const $dialogForm = $dialogEl.find('form');

		/**
		 * Set up the dialog modal.  It will be invoked later.
		 */
		const $dialog = $dialogEl.dialog({
			autoOpen: false,
			height: 400,
			width: 300,
			modal: true,
			buttons: {
				Add: function() {
					$dialogForm.submit()
				},
				Cancel: function() {
					$dialog.dialog( "close" );
				}
			},
			close: function() {
				$dialogForm[0].reset();
				$dialogForm.find('input[name=attachment_id]').val('');
				console.log( 'close' );
			},
			open: function() {
				$('.ui-widget-overlay').bind( 'click', function() {
					$dialog.dialog( 'close' );
				});
			}
		});

		/**
		 * Set up the submit handler for the form in the dialog modal.
		 */
		$dialogForm.on( "submit", function( event ) {
			event.preventDefault();
			console.log( 'submit' );

			const newTag = $dialogForm.find('input[name=tag]').val();
			const attachmentId = $dialogForm.find('input[name=attachment_id]').val();

			if ( ! newTag ) {
				return;
			}

			wp.apiRequest({
				path: '/community-gallery-tags/v1/suggest-tag',
				data: $dialogForm.serializeArray(),
				method: 'POST',
				dataType: 'json'
			}).done( ( response ) => {
				console.log( response );

				$gallery.children( 'li.attachment-' + attachmentId ).find('.term-list').append( '<li>' + newTag + '</li>' );

				$dialog.dialog( 'close' );
			});
		});

		/**
		 * Set up the click event that will open the dialog.
		 */
		$gallery.on( 'click', 'a.add-tag', function(e) {
			e.preventDefault();

			const $element = $(e.target);
			const attachmentId = $element.data('attachment-id');

			$dialog.dialog('open');

			$dialogForm.find('input[name=attachment_id]').val( attachmentId );
		});
	}

	/**
	 * Listen for notifications of image upload.  If one happened, toss it into the gallery.
	 */
	document.body.addEventListener( 'uploadedGalleryImage', function(e) {
		console.log( "Listening to 'uploadedGalleryImage' event." );
		console.log( e )

		$gallery.append( tmplCgtItem( {
			id:        data.id,
			img_tag:   '<img src="" />',
		} ) );
	});

}( jQuery, wp ));
