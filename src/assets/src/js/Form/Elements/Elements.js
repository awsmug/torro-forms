import AjaxComponent from "../AjaxComponent";
import Textfield from "./Textfield";

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
		this.ajaxUrl = props.ajaxUrl;

		this.setPath("/Elements");
		this.updateParams({
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
		let queryString = "?";

		if (params.containerId !== undefined) {
			queryString += "container_id=" + params.containerId;
		}

		return queryString;
	}

	/**
	 * Rendering containers.
	 *
	 * @since 1.2.0
	 *
	 * @param {*} elements
	 */
	renderElements(elements) {
		return this.state.data.map(element => <Textfield data={element} ajaxUrl={this.ajaxUrl} />);
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
