import AjaxComponent from '../AjaxComponent';
import Textfield from '../Elements/Textfield';
import Textarea from '../Elements/Textarea';
import Content from '../Elements/Content';
import Dropdown from '../Elements/Dropdown';
import Onechoice from '../Elements/Onechoice';
import Multiplechoice from '../Elements/Multiplechoice';

import axios from "axios/index";

class Container extends AjaxComponent {
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

		this.label = props.label;

		this.state = {
			elements: []
		}
	}

	/**
	 * Doing things after component mounted.
	 *
	 * @since 1.2.0
	 */
	componentDidMount() {
		this.getElements();
	}

	/**
	 * Getting Elements.
	 *
	 * @since 1.2.0
	 */
	getElements() {
		const elementsGetUrl = this.getEndpointUrl( '/elements?container_id=' + this.containerId )

		axios.get( elementsGetUrl )
			.then(response => {
				this.setState( { elements: response.data } );
			})
			.catch(error => {
				console.error(error);
			});
	}

	saveContainer(event) {
		event.preventDefault();

		if( this.submissionId === null ) {
			this.props.createSubmission();
		}

		this.state.elements.forEach( element => {
			this.saveElement( element.id, element.value, element.valueId );
		});
	}

	saveElement(elementId, value, valueId = null) {		
		if( this.state.submissionId === null ) {
			console.error( 'Missing submission id for saving value.' );
			return;
		}

		const submissionValuePostUrl = this.getEndpointUrl( '/submission_values' );

		if( valueId === null ) {

			axios.post(submissionValuePostUrl, {
				form_id: this.formId,
				submission_id: this.state.submissionId,
				element_id: elementId,
				value: value
			})
				.then(response => {
					this.updateElement( elementId, value, response.data.id  );
				})
				.catch( error => {
					console.log( error );
				});
		} else {
			console.log( 'There was a submission on this field!' );
		}
	}

	updateElement( elementId, value, valueId = null, errorMessage = null ) {
		let elements = this.state.elements;
		
		elements.forEach((element, index) => {
			if( element.id === elementId ) {
				elements[index].value = value;
				elements[index].valueId = valueId;
				elements[index].errors = [ errorMessage ];
			}
		});

		this.setState( elements );
	}

	/**
	 * Rendering output.
	 *
	 * @since 1.2.0
	 */
	renderComponent() {
		return (
			<div className="torro_container">
				<h3>{this.label}</h3>
				<div className="torro-forms-elements">{this.renderElements(this.state.elements)}</div>
				<input type="submit" value="Submit" onClick={this.saveContainer.bind(this)} />
			</div>
		);
	}

	/**
	 * Rendering containers.
	 *
	 * @since 1.2.0
	 *
	 * @param {*} elements
	 */
	renderElements(elements) {
		return elements.map((element, i) => {
			return this.renderElement(element, i);
		});
	}

	/**
	 * Rendering an element.
	 *
	 * @param element
	 * @param i
	 * @returns {*}
	 */
	renderElement(element, i) {
		let elements = {
			textfield: element => {
				return <Textfield data={element} ajaxUrl={this.ajaxUrl} key={i} updateElement={this.updateElement.bind(this)} />;
			},
			textarea: element => {
				return <Textarea data={element} ajaxUrl={this.ajaxUrl} key={i} updateElement={this.updateElement.bind(this)} />;
			},
			content: element => {
				return <Content data={element} ajaxUrl={this.ajaxUrl} key={i} updateElement={this.updateElement.bind(this)} />;
			},
			dropdown: element => {
				return <Dropdown data={element} ajaxUrl={this.ajaxUrl} key={i} updateElement={this.updateElement.bind(this)} />;
			},
			onechoice: element => {
				return <Onechoice data={element} ajaxUrl={this.ajaxUrl} key={i} updateElement={this.updateElement.bind(this)} />;
			},
			multiplechoice: element => {
				return <Multiplechoice data={element} ajaxUrl={this.ajaxUrl} key={i} updateElement={this.updateElement.bind(this)} />;
			},
			default: element => {
				return <Textfield data={element} ajaxUrl={this.ajaxUrl} key={i} updateElement={this.updateElement.bind(this)} />;
			}
		};

		return (elements[element.type] || elements['default'])(element);
	}
}

export default Container;
