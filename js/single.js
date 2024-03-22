(function( $, wp ){
	const $dialogEl = $('#cgt-dialog-form');

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
					$('.post-' + attachmentId + '.type-attachment').find( '.taxonomy-people.wp-block-post-terms' ).append( '<span class="wp-block-post-terms__separator">, </span>' + tag );
				});
				$dialog.dialog( 'close' );
			});
		});

		/**
		 * Set up the click event that will open the dialog.
		 */
		$('a.add-tag').on( 'click', function(e) {
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
}( jQuery, wp ));
