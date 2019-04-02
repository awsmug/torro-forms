import { __ } from "@wordpress/i18n";
import AjaxComponent from "./AjaxComponent";
import Container from "./Container";
import axios from "axios";

/**
 * Class for handling forms.
 *
 * @since 1.2.0
 */
class Form extends AjaxComponent {
	/**
	 * Constructor.
	 *
	 * @since 1.2.0
	 *
	 * @param {*} props Form properties.
	 */
	constructor(props) {
		super(props);

		this.id = parseInt(props.id);
		this.userId = parseInt(props.userId);
		this.wpNonce = props.wpNonce;

		this.key = this.createUserKey();

		this.status = "progressing";
	}

	/**
	 * Doing things after component mounted.
	 *
	 * @since 1.2.0
	 */
	componentDidMount() {
		this.getForm();
		this.getContainers();
	}

	/**
	 * Get Form Data.
	 *
	 * @since 1.2.0
	 */
	getForm() {
		const formGetUrl = this.getEndpointUrl("/forms/" + this.id);

		axios
			.get(formGetUrl)
			.then(response => {
				this.setState({ form: response.data });
			})
			.catch(error => {
				console.error(error);
			});
	}

	/**
	 * Getting Containers.
	 *
	 * @since 1.2.0
	 */
	getContainers() {
		const containersGetUrl = this.getEndpointUrl("/containers?form_id=" + this.id);

		axios
			.get(containersGetUrl)
			.then(response => {
				this.setState({ containers: response.data });
			})
			.catch(error => {
				console.error(error);
			});
	}

	/**
	 * Generating user key.
	 *
	 * @since 1.2.0
	 */
	createUserKey() {
		return Math.random()
			.toString(36)
			.substr(2, 9);
	}

	/**
	 * Saving data to rest API.
	 *
	 * @since 1.2.0
	 */
	createSubmission() {
		if (this.state.submissionId !== undefined) {
			return;
		}

		const submissionPostUrl = this.getEndpointUrl("/submissions");

		return axios.post(submissionPostUrl, {
			form_id: this.id,
			user_id: this.userId,
			key: this.createUserKey(),
			status: this.status
		});
	}

	/**
	 * Setting submission id
	 *
	 * @param {*} id
	 */
	setSubmissionId(id) {
		this.setState({ submissionId: id });
	}

	/**
	 * Rendering content.
	 *
	 * @since 1.1.0
	 */
	renderComponent() {
		if (this.state.form === undefined || this.state.containers === undefined) {
			return this.showTextLoading();
		}

		return (
			<div className="torro-form">
				<h2>{this.state.form.title}</h2>
				<form id={this.state.form.instance.id} className={this.state.form.instance.class}>
					{this.renderContainers()}
				</form>
			</div>
		);
	}

	/**
	 * Rendering containers.
	 *
	 * @since 1.2.0
	 *
	 * @param {*} containers
	 */
	renderContainers() {
		return this.state.containers.map((container, i) => (
			<Container
				ajaxUrl={this.props.ajaxUrl}
				formId={this.id}
				containerId={container.id}
				containerLabel={container.label}
				submissionId={this.state.submissionId}
				createSubmission={this.createSubmission.bind(this)}
				setSubmissionId={this.setSubmissionId.bind(this)}
				key={i}
			/>
		));
	}
}

export default Form;
