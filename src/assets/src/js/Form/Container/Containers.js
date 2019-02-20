import AjaxComponent from '../AjaxComponent';
import Container from './Container';

/**
 * Class for handling containers.
 *
 * @since 1.2.0
 */
class Containers extends AjaxComponent {
	/**
	 * Constructor.
	 *
	 * @since 1.2.0
	 *
	 * @param {*} props Containers properties.
	 */
	constructor(props) {
		super(props);
		this.formId = props.formId;

		this.setParams({
			formId: this.formId
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
		return '/containers?form_id=' + params.formId;
	}

	/**
	 * Rendering containers.
	 *
	 * @since 1.2.0
	 *
	 * @param {*} containers
	 */
	renderContainers(containers) {
		return this.state.data.map((container, i) => (
			<Container data={container} formId={this.formId} ajaxUrl={this.ajaxUrl} key={i} />
		));
	}

	/**
	 * Rendering output.
	 *
	 * @since 1.2.0
	 */
	renderComponent() {
		return <div className="torro-forms-containers">{this.renderContainers(this.state.data)}</div>;
	}
}

export default Containers;
