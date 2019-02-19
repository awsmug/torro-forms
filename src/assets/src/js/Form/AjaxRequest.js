import axios from "axios";

/**
 * Requests layer.
 *
 * @since 1.2.0
 */
class AjaxRequest {
	/**
	 * Constructor.
	 *
	 * @since 1.2.0
	 *
	 * @param {*} string Url for requests.
	 */
	constructor(ajaxUrl, query) {
		this.setEndpoint(ajaxUrl, "/torro/v1", query);
	}

	/**
	 * Setting endpoint url.
	 * 
	 * @since 1.2.0
	 * 
	 * @param {*} url 
	 */
	setEndpoint(ajaxUrl, namespace, query) {
		this.endpoint = ajaxUrl + namespace + query;
	}

	/**
	 * Getting data.
	 *
	 * @since 1.2.0
	 *
	 * @param params
	 *
	 * @return {*} json Json response data from url.
	 */
	get() {
		console.log(this.endpoint);
		return axios.get(this.endpoint);
	}
}

export default AjaxRequest;
