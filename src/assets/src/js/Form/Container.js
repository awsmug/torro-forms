import AjaxComponent from "./AjaxComponent";
import Textfield from "./Elements/Textfield";
import Textarea from "./Elements/Textarea";
import Content from "./Elements/Content";
import Dropdown from "./Elements/Dropdown";
import Onechoice from "./Elements/Onechoice";
import Multiplechoice from "./Elements/Multiplechoice";

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
		const elementsGetUrl = this.getEndpointUrl("/elements?container_id=" + this.props.containerId);

		axios
			.get(elementsGetUrl)
			.then(response => {
				this.setState({ elements: response.data });
			})
			.catch(error => {
				console.error(error);
			});
	}

	saveContainer(event) {
		event.preventDefault();

		if (this.props.submissionId === undefined) {
			this.props
				.createSubmission()
				.then(response => {
					this.props.setSubmissionId(response.data.id);

					this.state.elements.forEach(element => {
						this.saveElement(element.id, element.value, element.valueId);
					});
				})
				.catch(function(error) {
					console.error(error);
				});
		} else {
			this.state.elements.forEach(element => {
				this.saveElement(element.id, element.value, element.valueId);
			});
		}
	}

	saveElement(elementId, value, valueId = null) {
		if (this.props.submissionId === undefined) {
			console.error("Missing submission id for saving value.");
			return;
		}

		if (valueId === null) {
			if (value === undefined) {
				value = "";
			}

			const submissionValuePostUrl = this.getEndpointUrl("/submission_values");

			const params = {
				form_id: this.props.formId,
				submission_id: this.props.submissionId,
				element_id: elementId,
				value: value
			};

			axios
				.post(submissionValuePostUrl, params)
				.then(response => {
					if (response.status === 201) {
						this.updateElement(elementId, value, response.data.id);
					} else if (response.status === 400) {
						this.updateElement(elementId, value, null, response.data.message);
					}
				})
				.catch(error => {
					if (error.response.status === 400) {
						this.updateElement(elementId, value, null, error.response.data.data.params.value);
					} else {
						console.error(error);
					}
				});
		} else {
			const submissionValuePutUrl = this.getEndpointUrl("/submission_values/" + valueId);
			console.log(submissionValuePutUrl);

			const params = {
				id: valueId,
				element_id: elementId,
				value: value
			};

			axios
				.put(submissionValuePutUrl, params)
				.then(response => {
					if (response.status === 201) {
						this.updateElement(elementId, value, response.data.id);
					} else if (response.status === 400) {
						this.updateElement(elementId, value, null, response.data.message);
					}
				})
				.catch(error => {
					if (error.response.status === 400) {
						this.updateElement(elementId, value, null, error.response.data.data.params.value);
					} else {
						console.error(error);
					}
				});
		}
	}

	updateElement(elementId, value, valueId = null, errorMessage = null) {
		let elements = this.state.elements;

		elements.forEach((element, index) => {
			if (element.id === elementId) {
				elements[index].value = value;
				elements[index].valueId = valueId;
				elements[index].errors = undefined;

				if (errorMessage !== null) {
					elements[index].errors = [errorMessage];
				}
			}
		});

		this.setState(elements);
	}

	/**
	 * Rendering output.
	 *
	 * @since 1.2.0
	 */
	renderComponent() {
		return (
			<div className="torro_container">
				<h3>{this.props.label}</h3>
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
				return <Textfield data={element} ajaxUrl={this.props.ajaxUrl} key={i} updateElement={this.updateElement.bind(this)} />;
			},
			textarea: element => {
				return <Textarea data={element} ajaxUrl={this.props.ajaxUrl} key={i} updateElement={this.updateElement.bind(this)} />;
			},
			content: element => {
				return <Content data={element} ajaxUrl={this.props.ajaxUrl} key={i} updateElement={this.updateElement.bind(this)} />;
			},
			dropdown: element => {
				return <Dropdown data={element} ajaxUrl={this.props.ajaxUrl} key={i} updateElement={this.updateElement.bind(this)} />;
			},
			onechoice: element => {
				return <Onechoice data={element} ajaxUrl={this.props.ajaxUrl} key={i} updateElement={this.updateElement.bind(this)} />;
			},
			multiplechoice: element => {
				return <Multiplechoice data={element} ajaxUrl={this.props.ajaxUrl} key={i} updateElement={this.updateElement.bind(this)} />;
			},
			default: element => {
				return <Textfield data={element} ajaxUrl={this.props.ajaxUrl} key={i} updateElement={this.updateElement.bind(this)} />;
			}
		};

		return (elements[element.type] || elements["default"])(element);
	}
}

export default Container;
