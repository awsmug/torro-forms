import { Component } from "@wordpress/element";
import ElementSettings from "./ElementSettings";

/**
 * Base form element class.
 *
 * @since 1.1.0
 */
class Element extends Component {
	/**
	 * Constructor.
	 *
	 * @param {*} props
	 *
	 * @since 1.1.0
	 */
	constructor(props) {
		super(props);

		if (new.target === Element) {
			throw new TypeError("Cannot construct abstract instances directly");
		}

		this.ajaxUrl = props.ajaxUrl;
		this.data = props.data;

		const params = {
			elementId: this.data.id,
			ajaxUrl: this.ajaxUrl
		};

		this.elementSettings = new ElementSettings(params);
	}
}

export default Element;
