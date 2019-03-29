import { Component } from '@wordpress/element';
import Elements from '../Elements/Elements';
import axios from "axios/index";

class Container extends Component {
	/**
	 * Constructor.
	 *
	 * @since 1.2.0
	 *
	 * @param {*} props Container properties.
	 */
	constructor(props) {
		super(props);

		this.formId = props.formId;
		this.containerId = props.containerId;
		this.submissionId = props.submissionId;

		this.ajaxUrl = props.ajaxUrl;

		this.label = props.data.label;

		this.state = {
			elements: []
		}
	}

	saveContainer() {
		if( this.submissionId === null ) {
			this.props.createSubmission();
		}

		this.state.elements.forEach( element => {
			this.saveElement( element.id, element.value, element.submissionValueId );
		});
	}

	saveElement(elementId, value, submissionValueId = null) {
		if( this.state.submissionId === null ) {
			console.error( 'Missing submission id for saving value.' );
			return;
		}

		const submissionValuePostUrl = this.getEndpointUrl( '/submission_values' );

		if( submissionValueId === null ) {
			axios.post(submissionValuePostUrl, {
				form_id: this.formId,
				submission_id: this.state.submissionId,
				element_id: elementId,
				value: value
			})
				.then(response => {
					this.updateElement(elementId, value, response.data.id );
				})
				.catch(function (error) {
					console.log( error.response );
					if( error.response.status === 400 ) {
						this.updateElement(elementId, value, null, error.response.data.data.params.value );
					}

				});
		} else {
			console.log( 'There was a submission on this field!' );
		}
	}

	updateElement( elementId, value, submissionValueId = null, errorMessage = null ) {
		let elements = [];

		if( this.state.elements.length !== 0 ) {
			elements = this.state.elements;
		}

		const element = {
			id: elementId,
			value: value,
			submissionValueId: submissionValueId,
			errorMessage: errorMessage
		}

		elements.find((element, index) => {
			if( element.id === elementId ) {
				elements.splice( index, 1 );
			}
		});

		elements.push(element);

		this.setState({elements: elements});
		this.updateContainer();
	}

	/**
	 * Rendering output.
	 *
	 * @since 1.2.0
	 */
	render() {
		return (
			<div className="torro_container">
				<h3>{this.label}</h3>
				<Elements
					containerId={this.containerId}
					ajaxUrl={this.ajaxUrl}
					elements={this.state.elements}
					updateElement={this.updateElement} />

				<input type="submit" value="Submit" onClick={this.saveContainer} />
			</div>
		);
	}
}

export default Container;
