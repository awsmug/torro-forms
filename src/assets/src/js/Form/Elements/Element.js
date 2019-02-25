import { Component } from "@wordpress/element";
import ElementSettings from "./ElementSettings";
import ElementChoices from "./ElementChoices";

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

		this.hasChoices = false;

		this.state = {
			status: props.data.status,
			elementId: props.data.id,
			data: props.data
		};

		this.settings = null;
		this.choices = null;
	}

	/**
	 * Doing things after component mounted.
	 *
	 * @since 1.2.0
	 */
	componentDidMount() {
	}

	/**
	 * Renders an element
	 *
	 * @since 1.2.0
	 */
	render() {
		const data = this.state.data.instance;

		let html = {
			before: data.before,
			after: data. after
		}

		if( data.description !== '' ){
			html.description = () =>  { return (
				<div id={data.description_attrs.id} className={data.description_atts.class}>
					{data.description}
				</div>
			)};
		}

		if( data.errors.length > 0  ){
			html.errors = () => { return (
				<div id={data.errors_attrs.id} className={data.errors_attrs.class}>
					{data.errors}
				</div>
			)};
		}

		return this.renderElement(data, html);
	}

	/**
	 * Rendering element function. Should be overwritten by child elements.
	 *
	 * @since 1.2.0
	 *
	 * @param instance
	 */
	renderElement(instance, html) {


		throw new TypeError("Missing renderElement function in element class");
	}

	/**
	 * Transforms an array of attributes into an attribute string.
	 *
	 * @since 1.2.0
	 *
	 * @param {object} attrs Object of `key: value` pairs like { class: "class-test", title: "test" }.
	 * @return string Attribute string.
	 */
	attrs(attrs) {
		let pairs = Object.entries(values).map(value => {
			return value[0] + '="' + value[1] + '"';
		});

		return pairs.join(" ");
	}
}

export default Element;
