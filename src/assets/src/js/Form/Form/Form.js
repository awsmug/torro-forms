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

		this.submissionId = null;
		this.containerId = null;
		this.userId = 0;

		this.status = 'progressing';
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
	 * Doing things after component mounted.
	 *
	 * @since 1.2.0
	 */
	componentDidMount() {
		this.getForm();
	}

	/**
	 * Getting form data.
	 *
	 * @since 1.2.0
	 */
	getForm() {
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
	 * Saving data to rest API.
	 *
	 * @since 1.2.0
	 */
	saveSubmission() {
		if( this.submissionId === null ) {
			const submissionPostUrl = this.getEndpointUrl( '/submissions' );

			axios.post( submissionPostUrl, {
				form_id: this.id,
				user_id: this.userId,
				key: this.key,
				status: this.status,
			})
				.then(response => {
					this.submissionId = response.id;
					console.log(response);
				})
				.catch(function (error) {
					console.error(error);
				});
		}

		this.saveSubmissionValues();
	}

	/**
	 * Saving submisson value.
	 *
	 * @since 1.2.0
	 */
	saveSubmissionValues() {
		if( this.submissionId === null ) {
			console.error( 'Missinng submission id for savinng values.' );
			return;
		}

		this.status.elements.forEach( element => {
			this.saveSumbissionValue( element.id, element.value )
		});
	}

	/**
	 * Saving a submissionn value
	 *
	 * @since 1.2.0
	 *
	 * @param elementId
	 * @param value
	 * @param field
	 */
	saveSumbissionValue( elementId, value, field = '' ) {
		const submissionValuePostUrl = this.getEndpointUrl( '/submission_values' );

		axios.post( submissionValuePostUrl, {
			form_id: this.id,
			submission_id: this.submissionId,
			element_id: elementId,
			value: value,
			flield: field,
		})
			.then(response => {
				let submissionvalueId = response.id;
				console.log(submissionvalueId);
				console.log(response);
			})
			.catch(function (error) {
				console.error(error);
			});
	}

	/**
	 * Changing the element value and updating state
	 *
	 * @since 1.2.0
	 *
	 * @param elementId
	 * @param value
	 */
	setElementValue( elementId, value ) {
		let elements = [];

		if( this.state.elements !== undefined ) {
			elements = this.state.elements;
		}

		const element = {
			id: elementId,
			value: value
		}

		elements.find((element, index) => {
			if( element.id === elementId ) {
				elements.splice( index, 1 );
			}
		});

		elements.push(element);

		this.setState({elements: elements});
	}

	/**
	 * Handling update.
	 *
	 * @param event
	 */
	handleUpdate( event ) {
		this.saveSubmission();
	}

	/**
	 * Rendering content.
	 *
	 * @since 1.1.0
	 */
	renderComponent() {
		return (
			<div className="torro-form">
				<h2>{this.state.form.title}</h2>
				<form id={this.state.form.instance.id} className={this.state.form.instance.class}>
					<Containers ajaxUrl={this.ajaxUrl} formId={this.id} containerId={this.containerId} handleUpdate={(event) => this.handleUpdate(event)} setElementValue={(elementId, value) => this.setElementValue(elementId, value)} />
				</form>
			</div>
		);
	}
}

export default Form;
