import axios from "axios";

/**
 * Requests layer.
 *
 * @since 1.1.0
 */
class AjaxRequest {
	/**
	 * Constructor.
	 *
	 * @since 1.1.0
	 *
	 * @param {*} string Url for requests.
	 */
	constructor(endpoint) {
		this.endpoint = endpoint;
	}

	/**
	 * Getting data.
	 *
	 * @since 1.1.0
	 *
	 * @param params
	 *
	 * @return {*} json Json response data from url.
	 */
	get() {
		return axios.get(this.endpoint);
	}
}

export default AjaxRequest;
