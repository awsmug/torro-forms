import AjaxComponent from "./AjaxComponent";
import Textfield from "./Elements/Textfield";
import Textarea from "./Elements/Textarea";
import Content from "./Elements/Content";
import Dropdown from "./Elements/Dropdown";
import Onechoice from "./Elements/Onechoice";
import Imagechoice from "./Elements/Imagechoice";
import Multiplechoice from "./Elements/Multiplechoice";
import Range from "./Elements/Range";

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

		this.formId = this.props.formId;
		this.submissionId = null;

		if (props.submissionId !== null) {
			this.submissionId = props.submissionId;
		}
	}

	/**
	 * Doing things after component mounted.
	 *
	 * @since 1.2.0
	 */
	componentDidMount() {
		this.syncDownstream().catch( err => {
			console.error('Error on loading Container.', err );
		});
	}

	/**
	 * Go to previous container wrapper.
	 *
	 * @since 1.2.0
	 *
	 * @param event
	 */
	prev(event){
		event.preventDefault();
		event.persist();

		this.props.prevContainer(event);
	}

	/**
	 * Go to next container.
	 *
	 * @since 1.2.0
	 *
	 * @param event
	 */
	next(event){
		event.preventDefault();
		event.persist();

		this.syncUpstream()
			.then(_ => {
				this.props.nextContainer(event);
			})
			.catch(err => {
				console.error('Error on setting next container.', err);
			});
	}

	/**
	 * Finishing submission.
	 *
	 * @since 1.2.0
	 *
	 * @param event
	 */
	finish(event) {
		event.preventDefault();
		event.persist();

		const button = event.target;
		button.classList.add('torro-button-loading');

		this.syncUpstream()
			.then( _ => {
				this.props.completeSubmission();
				button.classList.remove('torro-button-loading');
			})
			.catch(err => {
				console.error('Error on setting finishing submission.', err);
			});
	}

	/**
	 * Getting Elements.
	 *
	 * @since 1.2.0
	 */
	syncDownstream() {
		const elementsGetUrl = this.getEndpointUrl("/elements?container_id=" + this.props.data.id);

		return new Promise( (resolve, reject ) => {
			axios.get(elementsGetUrl)
				.then(response => {
					this.setState({ elements: response.data });
					return resolve(response.data)
				})
				.catch(err => {
					return reject(err);
				});
		});
	}

	/**
	 * Saving container data.
	 *
	 * @since 1.2.0
	 *
	 * @param event
	 * @returns {Promise<any>}
	 */
	syncUpstream() {
		const self = this;

		return new Promise((resolve, reject) => {
			this.props.syncUpstream()
				.then( _ => {
					self.syncUpstreamElements().then( _ => {
						return resolve();
					}).catch(err => {
						console.error(err.response);
					});
				})
				.catch( err => {
					return reject(err);
				});
		});
	}

	/**
	 * Saving all current elements.
	 *
	 * @since 1.2.0
	 *
	 * @returns {Promise<any[]>}
	 */
	syncUpstreamElements(){
		const elements = this.state.elements;

		const responses = [];

		elements.forEach( element => {
			if( element.instance.has_input === false ) {
				return;
			}

			responses.push( this.syncUpstreamElement(element.id, element.value, element.valueId) );
		});

		return Promise.all(responses);
	}

	/**
	 * Saves a single element
	 *
	 * @since 1.2.0
	 *
	 * @param elementId
	 * @param value
	 * @param valueId
	 * @returns {Promise<any>}
	 */
	syncUpstreamElement(elementId, value, valueId = undefined) {
		return new Promise((resolve, reject) => {
			if ( this.submissionId === undefined) {
				return reject("Missing submission id for saving value.");
			}

			let submissionValuePostUrl = this.getEndpointUrl("/submission_values");
			let method = 'post';

			// If value already has an id
			if (valueId !== undefined && valueId !== null) {
				submissionValuePostUrl += '/' + valueId;
				method = 'put';
			}

			// If value is undefined set to empty string
			if (value === undefined) {
				value = "";
			}

			console.log( 'Dump Nonce: ' + this.props.getDumpNonce() );

			const params = {
				method: method,
				url: submissionValuePostUrl,
				data: {
					form_id: this.formId,
					submission_id: this.props.submissionId,
					element_id: elementId,
					value: value,
					torro_dump_nonce: this.props.getDumpNonce()
				}
			};

			axios(params)
				.then(response => {
					if (response.status === 200 || response.status === 201) {
						this.setElement(elementId, value, response.data.id);
						this.props.setDumpNonce(response.data.torro_dump_nonce);

						return resolve(response.data.id);
					} else if (response.status === 400) {
						this.setElement(elementId, value, null, response.data.message);
						return reject(response);
					}
				})
				.catch(err => {
					this.setElement(elementId, value, null, err.response.data.data.params.value);
					return reject(err);
				});
		});

	}

	/**
	 * Sets element values.
	 *
	 * @since 1.2.0
	 *
	 * @param elementId
	 * @param value
	 * @param valueId
	 * @param errorMessage
	 */
	setElement(elementId, value, valueId = null, errorMessage = null) {
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
		let cssClasses = ['slider-item'];
		cssClasses.push( this.props.data.instance.wrap_attrs.class )

		let containerTitle = null;
		let requiredFieldsText = null;

		if( this.props.showContainerTitle === true ) {
			containerTitle = <h3>{this.props.data.label}</h3>;
		}

		if( this.props.requiredFieldsText !== '' ) {
			requiredFieldsText = <p className="torro-required-text">{this.props.requiredFieldsText}</p>;
		}

		return (
			<div id={this.props.data.instance.wrap_attrs.id} className={cssClasses.join(' ')}>
				{containerTitle}
				{requiredFieldsText}
				<div className="torro-forms-elements">{this.renderElements(this.state.elements)}</div>
				<div className="torro-pager">
					{this.props.hasPrevContainer ? <div className="prev"><button onClick={this.prev.bind(this)}>{this.props.previousButtonLabel}</button></div>: null }
					{this.props.hasNextContainer ? <div className="next"><button onClick={this.next.bind(this)}>{this.props.nextButtonLabel}</button></div>: <div className="next"><button type="button" onClick={this.finish.bind(this)}>{this.props.submitButtonLabel}</button></div> }
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
				return <Textfield data={element} ajaxUrl={this.props.ajaxUrl} key={i} setElement={this.setElement.bind(this)} />;
			},
			textarea: element => {
				return <Textarea data={element} ajaxUrl={this.props.ajaxUrl} key={i} setElement={this.setElement.bind(this)} />;
			},
			content: element => {
				return <Content data={element} ajaxUrl={this.props.ajaxUrl} key={i} setElement={this.setElement.bind(this)} />;
			},
			dropdown: element => {
				return <Dropdown data={element} ajaxUrl={this.props.ajaxUrl} key={i} setElement={this.setElement.bind(this)} />;
			},
			onechoice: element => {
				return <Onechoice data={element} ajaxUrl={this.props.ajaxUrl} key={i} setElement={this.setElement.bind(this)} />;
			},
			imagechoice: element => {
				return <Imagechoice data={element} ajaxUrl={this.ajaxUrl} key={i} setElement={this.setElement.bind(this)} />;
			},
			multiplechoice: element => {
				return <Multiplechoice data={element} ajaxUrl={this.props.ajaxUrl} key={i} setElement={this.setElement.bind(this)} />;
			},
			range: element => {
				return <Range data={element} ajaxUrl={this.props.ajaxUrl} key={i} setElement={this.setElement.bind(this)} />;
			},
			default: element => {
				return <Textfield data={element} ajaxUrl={this.props.ajaxUrl} key={i} setElement={this.setElement.bind(this)} />;
			}
		};

		return (elements[element.type] || elements["default"])(element);
	}
}

export default Container;
