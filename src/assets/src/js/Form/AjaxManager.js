import AjaxRequest from './AjaxRequest';

/**
 * Component to use in for components using data from DB.
 *
 * @since 1.1.0
 */
class AjaxModel {
	/**
	 * Constructor.
	 *
	 * @since 1.1.0
	 *
	 * @param {object} Properties React properties.
	 */
	constructor(ajaxUrl) {
		if (new.target === AjaxModel) {
			throw new TypeError('Cannot construct abstract instances directly');
		}

		this.ajaxUrl = ajaxUrl;
		this.params = null;
	}

	/**
	 * Parameters for query.
	 *
	 * @since 1.2.0
	 *
	 * @param {object} params
	 */
	setParams(params) {
		this.params = params;
	}

	/**
	 * Do the request.
	 *
	 * @since 1.1.0
	 *
	 * @returns Promise
	 */
	request() {
		return this.get(this.params);
	}

	/**
	 * Returning query string for url. Should be overwritten by childs.
	 *
	 * @since 1.2.0
	 *
	 * @param {array} params
	 *
	 * @return {null}
	 */
	getQueryString(params) {
		return null;
	}

	/**
	 * Getting Data from Rest API.
	 *
	 * @since 1.2.0
	 *
	 * @param {object} params
	 *
	 * @promise Getting Data from Rest API.
	 *
	 * @returns Promise
	 */
	get(params) {
		let query = this.getQueryString(params);
		let request = new AjaxRequest(this.ajaxUrl, query);

		return request.get();
	}

	post() {}

	update() {}

	delete() {}
}

export default AjaxModel;
