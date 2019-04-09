import AjaxComponent from "./AjaxComponent";
import Textfield from "./Elements/Textfield";
import Textarea from "./Elements/Textarea";
import Content from "./Elements/Content";
import Dropdown from "./Elements/Dropdown";
import Onechoice from "./Elements/Onechoice";
import Imagechoice from "./Elements/Imagechoice";
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
		const elementsGetUrl = this.getEndpointUrl("/elements?container_id=" + this.props.data.id);

		axios
			.get(elementsGetUrl)
			.then(response => {
				this.setState({ elements: response.data });
			})
			.catch(error => {
				console.error(error);
			});
	}

	prevContainer(event){
		event.preventDefault();

		this.props.prevContainer(event);
	}

	nextContainer(event){
		event.preventDefault();

		this.saveContainer(event)
			.then(response => {
				this.props.nextContainer( event );
			})
			.catch(error => {
				console.error(error);
			});
	}

	completeSubmission(event) {
		event.preventDefault();

		this.saveContainer(event)
			.then(response => {
				this.props.completeSubmission( event );
			})
			.catch(error => {
				console.error(error);
			});
	}

	saveContainer(event) {
		event.preventDefault();
		event.persist();

		event.target.classList.add('torro-button-loading');

		const save = new Promise((resolve, reject) => {
			if (this.props.submissionId === undefined) {
				this.props.createSubmission()
					.then(response => {
						this.props.setSubmissionId(response.data.id);

						this.state.elements.forEach(element => {
							if( element.instance.has_input === false ) {
								return;
							}
							this.saveElement(element.id, element.value )
								.then(response => {
									event.target.classList.remove('torro-button-loading');
									return resolve(element.valueId);
								})
								.catch(error => {
									event.target.classList.remove('torro-button-loading');
									return reject(error);
								});
						});
					})
					.catch(function (error) {
						return reject(error);
					});
			} else {
				this.state.elements.forEach(element => {
					if( element.instance.has_input === false ) {
						return;
					}
					this.saveElement(element.id, element.value, element.valueId)
						.then(response => {
							event.target.classList.remove('torro-button-loading');
							return resolve(element.valueId);
						})
						.catch(error => {
							event.target.classList.remove('torro-button-loading');
							return reject( error );
						});
				});
			}
		});

		return save;
	}

	saveElement(elementId, value, valueId = undefined) {
		const save = new Promise((resolve, reject) => {
			if (this.props.submissionId === undefined) {
				return reject("Missing submission id for saving value.");
			}

			let submissionValuePostUrl = this.getEndpointUrl("/submission_values");
			let method = 'post';

			if (valueId !== undefined && valueId !== null) {
				submissionValuePostUrl += '/' + valueId;
				method = 'put';
			}

			if (value === undefined) {
				value = "";
			}

			const params = {
				form_id: this.props.formId,
				submission_id: this.props.submissionId,
				element_id: elementId,
				value: value,
				_wpnonce: this.props.wpNonce
			};

			axios({
				method: method,
				url: submissionValuePostUrl,
				data: params
			}).then(response => {
				if (response.status === 200 || response.status === 201) {
					this.updateElement(elementId, value, response.data.id);
					return resolve(response.data.id);
				} else if (response.status === 400) {
					this.updateElement(elementId, value, null, response.data.message);
					return reject(response);
				}
			}).catch(error => {
				if (error.response.status === 400) {
					this.updateElement(elementId, value, null, error.response.data.data.params.value);
					return reject(error.response);
				} else {
					return reject(error.response);
				}
			});
		});

		return save;
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
		let cssClasses = ['torro-container', 'slider-item'];

		return (
			<div className={cssClasses.join(' ')}>
				<h3>{this.props.label}</h3>
				<div className="torro-forms-elements">{this.renderElements(this.state.elements)}</div>
				<div className="torro-pager">
					{this.props.hasPrevContainer ? <div className="prev"><button onClick={this.prevContainer.bind(this)}>Previous</button></div>: null }
					{this.props.hasNextContainer ? <div className="next"><button onClick={this.nextContainer.bind(this)}>Next</button></div>: <div className="next"><button type="button" onClick={this.completeSubmission.bind(this)}>Submit</button></div> }
				</div>
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
			imagechoice: element => {
				return <Imagechoice data={element} ajaxUrl={this.ajaxUrl} key={i} updateElement={this.props.updateElement} />;
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
