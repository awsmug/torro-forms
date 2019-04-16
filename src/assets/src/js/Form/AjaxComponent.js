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
		this.namespace = "torro/v1";

		this.state = null;

		this.setTextLoading("Loading...");
		this.setTextFailing("Failed loading form!");
	}

	/**
	 * Endpoint.
	 *
	 * @param endpoint
	 */
	getEndpointUrl(endpoint) {
		return this.ajaxUrl + this.namespace + endpoint;
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

	showTextLoading() {
		return <div className="torro-loading">{this.textLoading}</div>;
	}

	showTextFailing() {
		return <div className="torro-error">{this.textFailing}</div>;
	}

	/**
	 * Rendering content.
	 *
	 * @since 1.1.0
	 */
	render() {
		if (this.state === null) {
			if (this.textLoading !== null) {
				return this.showTextLoading();
			}
		} else if (this.state !== null) {
			return this.renderComponent();
		} else {
			return this.showTextFailing();
		}
	}

	/**
	 * Rendering component content. Should be overwritten by childs.
	 *
	 * @since 1.2.0
	 *
	 * @return {null}
	 */
	renderComponent() {
		return null;
	}
}

export default AjaxComponent;
