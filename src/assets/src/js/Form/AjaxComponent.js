import { Component } from "@wordpress/element";
import AjaxRequest from "./AjaxRequest";

/**
 * Component to use in for components using data from DB.
 *
 * @since 1.1.0
 */
class AjaxComponent extends Component {
	/**
	 * Constructor.
	 *
	 * @since 1.1.0
	 *
	 * @param {object} Properties React properties.
	 */
	constructor(props) {
		super(props);

		if (new.target === AjaxComponent) {
			throw new TypeError("Cannot construct abstract instances directly");
		}

		this.id = props.id;
		this.ajaxUrl = props.ajaxUrl;

		this.namespace = "/torro/v1";
		this.textLoading = "Loading...";
		this.textFailing = "Failed loading form!";
	}

	/**
	 * Setting path which results in endpoint url. Have to be called in child constructor.
	 *
	 * @since 1.1.0
	 *
	 * @param {string} path Path for object.
	 */
	setPath(path) {
		this.endpointUrl = this.ajaxUrl + this.namespace + path;
	}

	/**
	 * Updating parameters for request.
	 *
	 * @since 1.1.0
	 *
	 * @param {object} params Parameters for API query.
	 */
	updateParams(params) {
		this.get(params)
			.then(response => {
				this.setState(response);
			})
			.catch(error => {
				console.log(error);
			});
	}

	/**
	 * Rendering content.
	 *
	 * @since 1.1.0
	 */
	render() {
		if (this.state === null) {
			if (this.textLoading !== null) {
				return <div className="torro-loading">{this.textLoading}</div>;
			}
		} else if (this.state.status === 200) {
			return this.renderComponent();
		} else {
			return <div className="torro-error">{this.textFailing}</div>;
		}
	}

	/**
	 * Rendering component content. Should be overwritten by childs.
	 *
	 * @since 1.1.0
	 *
	 * @return {null}
	 */
	renderComponent() {
		return null;
	}

	/**
	 * Returning query string for url. Should be overwritten by childs.
	 *
	 * @since 1.1.0
	 *
	 * @return {null}
	 */
	getQueryString() {
		return null;
	}

	/**
	 * Getting Data from Rest API.
	 *
	 * @since 1.1.0
	 *
	 * @param {object} params
	 *
	 * @promise Getting Data from Rest API.
	 *
	 * @returns Promise
	 */
	get(params) {
		let path;

		if (params.id !== undefined) {
			path = "/" + params.id;
		} else {
			path = this.getQueryString(params);
		}

		let url = this.endpointUrl + path;
		let request = new AjaxRequest(url);
		return request.get();
	}

	post() {}

	update() {}

	delete() {}
}

export default AjaxComponent;
