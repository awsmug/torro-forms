wp.media.view.AttachmentFilters.All = wp.media.view.AttachmentFilters.All.extend({
	createFilters: function() {
		wp.media.view.AttachmentFilters.All.__super__.createFilters.apply( this, []);

		this.filters.torroFormUploads = {
			text: torro_media.l10n.name,
			props: {
				status: torro_media.status,
				type: null,
				uploadedTo: null,
				orderby: 'date',
				order: 'DESC'
			},
			priority: 50
		};
	}
});
