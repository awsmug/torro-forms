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

		this.setTextLoading("Loading...");
		this.setTextFailing("Failed loading form!");
	}

	/**
	 * Setting up text on loading component.
	 *
	 * @since 1.2.0
	 *
	 * @param {string} Text to display on loading component.
	 */
	setTextLoading(text) {
		this.textLoading = text;
	}

	/**
	 * Setting up text on failing component.
	 *
	 * @since 1.2.0
	 *
	 * @param {string} Text to display on a component which failed to load.
	 */
	setTextFailing(text) {
		this.textFailing = text;
	}

	/**
	 * Updating parameters for request.
	 *
	 * @since 1.1.0
	 *
	 * @param {object} params Parameters for API query.
	 */
	request(params) {
		return this.get(params)
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
	 * @since 1.1.0
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

	post() { }

	update() { }

	delete() { }
}

export default AjaxComponent;
