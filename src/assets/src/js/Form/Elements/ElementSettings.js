import AjaxManager from "../AjaxManager";

/**
 * Element settings.
 *
 * @since 1.2.0
 */
class ElementSettings extends AjaxManager {
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
		this.ajaxUrl = params.ajaxUrl;

		this.setParams({
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
		return "/element_settings?element_id=" + params.elementId;
	}

	getSetting(name) { }
}

export default ElementSettings;
