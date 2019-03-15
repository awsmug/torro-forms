import AjaxComponent from '../AjaxComponent';
import Container from './Container';
import axios from "axios/index";

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
		this.containerId = props.containerId;
	}

	/**
	 * Doing things after component mounted.
	 *
	 * @since 1.2.0
	 */
	componentDidMount() {
		this.getContainers();
	}

	/**
	 * Getting Containers.
	 *
	 * @since 1.2.0
	 */
	getContainers() {
		const containersGetUrl = this.getEndpointUrl( '/containers?form_id=' + this.formId )

		console.log( containersGetUrl );

		axios.get( containersGetUrl )
			.then(response => {
				this.setState( { containers: response.data } );
			})
			.catch(error => {
				console.error(error);
			});
	}

	/**
	 * Rendering containers.
	 *
	 * @since 1.2.0
	 *
	 * @param {*} containers
	 */
	renderContainers(containers) {
		return this.state.containers.map((container, i) => (
			<Container data={container} formId={this.formId} ajaxUrl={this.ajaxUrl} key={i} handleUpdate={(event) => this.props.handleUpdate(event)} setElementValue={(elementId, value) => this.props.setElementValue(elementId, value)} />
		));
	}

	/**
	 * Rendering output.
	 *
	 * @since 1.2.0
	 */
	renderComponent() {
		return <div className="torro-forms-containers">{this.renderContainers(this.state.containers.data )}</div>;
	}
}

export default Containers;
