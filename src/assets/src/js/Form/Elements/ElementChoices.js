import AjaxModel from "../AjaxModel";

/**
 * Element settings.
 *
 * @since 1.2.0
 */
class ElementChoices extends AjaxModel {
	/**
	 * Constructor.
	 *
	 * @since 1.2.0
	 *
	 * @param {*} params
	 */
	constructor(params) {
		super(params);

		this.elementId = params.elementId;

		this.setParams({
			ajaxUrl: this.ajaxUrl,
			elementId: this.elementId
		});
	}

	/**
	 * Creating query string for get request.
	 *
	 * @since 1.2.0
	 *
	 * @param {object} params Parameters for API query.
	 *
	 * @return {null}
	 */
	getQueryString(params) {
		return "/element_choices?element_id=" + params.elementId;
	}
}

export default ElementChoices;
