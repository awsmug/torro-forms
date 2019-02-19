import AjaxComponent from "../AjaxComponent";
import Textfield from "./Textfield";
import Textarea from "./Textarea";
import Content from "./Content";
import Onechoice from "./Onechoice";

/**
 * Class for handling Elements.
 *
 * @since 1.2.0
 */
class Elements extends AjaxComponent {
	/**
	 * Constructor.
	 *
	 * @since 1.2.0
	 *
	 * @param {*} props Element properties.
	 */
	constructor(props) {
		super(props);
		this.containerId = props.containerId;
	}

	/**
	 * Doing things after component mounted.
	 * 
	 * @since 1.1.0
	 */
	componentDidMount() {
		this.request({
			containerId: this.containerId
		});
	}

	/**
	 * Creating query string for get request.
	 *
	 * @param {object} params Parameters for API query.
	 *
	 * @returns {string} Query string.
	 */
	getQueryString(params) {
		return "/elements?container_id=" + params.containerId;
	}

	renderElement(element) {
		let elements = {
			'textfield': element => {
				return (<Textfield data={element} ajaxUrl={this.ajaxUrl} />);
			},
			'textarea': element => {
				return (<Textarea data={element} ajaxUrl={this.ajaxUrl} />);
			},
			'content': element => {
				return (<Content data={element} ajaxUrl={this.ajaxUrl} />);
			},
			'onechoice': element => {
				return (<Onechoice data={element} ajaxUrl={this.ajaxUrl} />);
			},
			'default': element => {

				return (<Textfield data={element} ajaxUrl={this.ajaxUrl} />);
			},
		}

		return (elements[element.type] || elements['default'])(element);
	}

	/**
	 * Rendering containers.
	 *
	 * @since 1.2.0
	 *
	 * @param {*} elements
	 */
	renderElements(elements) {
		return elements.map(element => {
			return this.renderElement(element)
		});
	}

	/**
	 * Rendering output.
	 *
	 * @since 1.2.0
	 */
	renderComponent() {
		return <div className="torro-forms-elements">{this.renderElements(this.state.data)}</div>;
	}
}

export default Elements;
