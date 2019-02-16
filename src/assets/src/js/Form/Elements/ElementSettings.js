import AjaxComponent from "../AjaxComponent";

/**
 * Element settings.
 *
 * @since 1.2.0
 */
class ElementSettings extends AjaxComponent {
	/**
	 * Constructor.
	 *
	 * @since 1.2.0
	 *
	 * @param {*} props
	 */
	constructor(props) {
		super(props);

		this.elementId = props.elementId;
		this.ajaxUrl = props.ajaxUrl;
		this.textLoading = null;
	}

	componentDidMount() {
		this.setPath("/element_settings");

		this.updateParams({
			elementId: this.elementId
		});
	}

	/**
	 * Creating query string for get request.
	 *
	 * @since 1.1.0
	 *
	 * @param {object} params Parameters for API query.
	 *
	 * @return {null}
	 */
	getQueryString(params) {
		let queryString = "?";

		if (params.elementId !== undefined) {
			queryString += "element_id=" + params.elementId;
		}

		return queryString;
	}

	getSetting(name) {}

	renderComponent() {
		console.log(this.state);
	}
}

export default ElementSettings;
