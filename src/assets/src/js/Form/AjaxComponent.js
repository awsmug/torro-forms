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

		this.textLoading = null;
		this.textFailing = null;
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
	 * Rendering content.
	 *
	 * @since 1.1.0
	 */
	render() {
		if (this.state === null) {
			return <div className="torro-loading">{this.textLoading}</div>;
		} else if (this.state !== null) {
			return this.renderComponent();
		} else {
			return <div className="torro-error">{this.textFailing}</div>;
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
