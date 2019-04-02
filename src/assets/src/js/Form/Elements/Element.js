import { Component } from "@wordpress/element";
import Description from "./Description";
import Errors from "./Errors";
import ElementChoices from "./ElementChoices";
import { POINT_CONVERSION_COMPRESSED } from "constants";

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
	 * @since 1.2.0
	 */
	constructor(props) {
		super(props);

		if (new.target === Element) {
			throw new TypeError("Cannot construct abstract instances directly");
		}

		this.ajaxUrl = props.ajaxUrl;

		this.id = props.data.id;

		this.hasChoices = false;
		this.choices = null;

		this.state = {
			element: this.props.data.instance,
			choices: null
		};
	}

	/**
	 * Component did mount.
	 *
	 * @since 1.2.0
	 */
	componentDidMount() {
		if (this.hasChoices) {
			let choices = new ElementChoices({ ajaxUrl: this.ajaxUrl, elementId: this.id });

			choices
				.syncDownstream()
				.then(response => {
					this.setState({ choices: response.data });
				})
				.catch(error => {
					console.error(error);
				});
		}
	}

	/**
	 * Changing value.
	 *
	 * @since 1.2.0
	 *
	 * @param event
	 */
	changeValue(event) {
		this.props.updateElement(this.id, event.target.value, this.props.data.valueId);
	}

	/**
	 * Renders an element
	 *
	 * @since 1.2.0
	 */
	render() {
		const element_hints = (
			<div>
				<Description id={this.state.element.id} className={this.state.element.description_attrs.class} text={this.state.element.description} />
				<Errors id={this.state.element.errors_attrs.id} className={this.state.element.errors_attrs.class} errors={this.props.data.errors} />
			</div>
		);

		const params = { element_hints };

		return this.renderElement(params);
	}

	/**
	 * Rendering element function. Should be overwritten by child elements.
	 *
	 * @si^nce 1.2.0
	 *
	 * @param data
	 */
	renderElement() {
		throw new TypeError("Missing renderElement function in element class");
	}
}

export default Element;
