import { Component } from '@wordpress/element';
import ElementSettings from './ElementSettings';
import ElementChoices from './ElementChoices';

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
			throw new TypeError('Cannot construct abstract instances directly');
		}

		this.ajaxUrl = props.ajaxUrl;
		this.data = props.data;

		const params = {
			elementId: this.data.id,
			ajaxUrl: this.ajaxUrl
		};

		this.settings = new ElementSettings(params);
		this.settings.request();
	}

	componentDidMount() {
		if (this.hastChoices) {
		}
	}
}

export default Element;
