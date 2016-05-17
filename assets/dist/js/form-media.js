/*!
 * Torro Forms Version 1.0.0-beta.1 (http://torro-forms.com)
 * Licensed under GNU General Public License v3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
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
