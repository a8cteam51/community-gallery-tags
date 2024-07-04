(function( $, wp ){
	const tmplCgtItem = wp.template( 'cgt-item' );
	const $gallery = $('ul.community-gallery-tags-gallery');
	const $dialogEl = $('#cgt-dialog-form');
	const $galleryFilters = $('#cgt-filters');
	const masonryOptions = {
		itemSelector: 'li.media:not(.hidden)',
		gutter: 24,
		percentPosition: true,
	};
	const $officialUserID = $gallery.data('event-author') ? $gallery.data('event-author') : 0;

	// Only bother with this stuff if we have the dialog form.  The dialog form will only render if the user can tag.
	if ( $dialogEl.length ) {
		const $dialogForm = $dialogEl.find('form');
		const $tagInput = $dialogForm.find('input[name=tag]');

		/**
		 * Set up the dialog modal.  It will be invoked later.
		 */
		const $dialog = $dialogEl.dialog({
			autoOpen: false,
			height: 400,
			width: 300,
			modal: true,
			resizable: false,
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
				$.each( response, function( meta_id, tag ) {
					$gallery.children( 'li.attachment-' + attachmentId ).find('.term-list').append( '<li data-meta-id="' + meta_id + '">' + tag + '</li>' );
				});

				$gallery.masonry(); // trigger a repositioning if needed.

				$dialog.dialog( 'close' );
			});
		});

		/**
		 * Set up the click event that will open the dialog.
		 */
		$gallery.on( 'click', 'a.add-tag', function(e) {
			e.preventDefault();

			const $element = $(e.target);
			const attachmentId = $element.data('media-id');

			$dialog.dialog('open');

			$dialogForm.find('input[name=attachment_id]').val( attachmentId );
		});

		/**
		 * Set up autosuggest for the non-hierarchical taxonomy.
		 */
		$tagInput.wpTagsSuggest({
			taxonomy: 'people'
		});
	}

	$gallery.masonry(masonryOptions);

	/**
	 * Listen for notifications of image upload.  If one happened, toss it into the gallery.
	 */
	document.body.addEventListener( 'imagesUploaded', function(e) {
		$.each( e.detail.images, function( index, image ) {
			if ( image.id ) {
				const $photo_type = $officialUserID === image.author ? 'official' : 'community';
				const $newItem = $( tmplCgtItem( {
					id:        image.id,
					uploader:  $photo_type,
					link:      image.link,
					img_tag:   image.description.rendered
				} ) );
				$gallery.prepend( $newItem ).masonry( 'prepended', $newItem );
			}
		});

		$gallery.masonry(); // trigger a repositioning if needed.
	});

	$galleryFilters.on( 'click', 'a', function(e){
		e.preventDefault();
		const $target = $( e.target );
		$target.addClass('selected').siblings('.selected').removeClass('selected');
		const uploaderId = $target.data('uploader-id');

		$gallery.children( 'li.hidden' ).removeClass('hidden');

		if ( uploaderId ) {
			$gallery.children( 'li.media').not( '.uploader-id-' + uploaderId ).addClass('hidden');
		}

		$gallery.masonry('destroy');
		$gallery.masonry(masonryOptions);
	});

}( jQuery, wp ));
