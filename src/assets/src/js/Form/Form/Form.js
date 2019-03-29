import { __ } from '@wordpress/i18n';
import AjaxComponent from '../AjaxComponent';
import Containers from '../Container/Containers';
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

		this.id = props.id;
		this.key = this.createUserKey();

		this.status = 'progressing';

		this.state = {
			form: null,
			containerId: null,
			submissionId: null,
			userId: null,
		}
	}

	/**
	 * Doing things after component mounted.
	 *
	 * @since 1.2.0
	 */
	componentDidMount() {
		const formGetUrl = this.getEndpointUrl( '/forms/' + this.id )

		axios.get( formGetUrl )
			.then(response => {
				this.setState({form:response.data});
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
		return Math.random().toString(36).substr(2, 9);
	}

	/**
	 * Saving data to rest API.
	 *
	 * @since 1.2.0
	 */
	createSubmission() {
		if( this.state.submissionId !== null ) {
			return;
		}

		const submissionPostUrl = this.getEndpointUrl( '/submissions' );

		axios.post( submissionPostUrl, {
			form_id: this.id,
			user_id: this.userId,
			key: this.createUserKey(),
			status: this.status,
		})
			.then(response => {
				this.setState({submissionId:response.data.id})
				this.saveSubmissionValues();
			})
			.catch(function (error) {
				console.error(error);
			});
	}

	/**
	 * Rendering content.
	 *
	 * @since 1.1.0
	 */
	renderComponent() {
		if( this.state.form === null ) {
			return ( <div className="torro-form">{this.textLoading}</div> );
		}

		return (
			<div className="torro-form">
				<h2>{this.state.form.title}</h2>
				<form id={this.state.form.instance.id} className={this.state.form.instance.class}>
					<Containers
						ajaxUrl={this.ajaxUrl}
						formId={this.id}
						containerId={this.state.containerId}
						submissionId={this.state.submissionId}
						createSubmission={this.createSubmission} />
				</form>
			</div>
		);
	}
}

export default Form;
