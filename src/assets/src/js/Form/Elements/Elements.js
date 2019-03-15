import AjaxComponent from '../AjaxComponent';
import Textfield from './Textfield';
import Textarea from './Textarea';
import Content from './Content';
import Dropdown from './Dropdown';
import Onechoice from './Onechoice';
import Multiplechoice from './Multiplechoice';
import axios from "axios/index";

/**
 * Class for handling Elements.
 *
 * @since 1.2.0
 */
class Elements extends AjaxComponent {
	/**
	 * Constructor.
	 *
	 * @since 1.2.0
	 *
	 * @param {*} props Element properties.
	 */
	constructor(props) {
		super(props);
		this.containerId = props.containerId;
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

		console.log( containersGetUrl );

		axios.get( containersGetUrl )
			.then(response => {
				this.setState( { elements: response.data } );
			})
			.catch(error => {
				console.error(error);
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
				return <Textfield data={element} ajaxUrl={this.ajaxUrl} key={i} changeElementValue={this.props.changeElementValue} />;
			},
			textarea: element => {
				return <Textarea data={element} ajaxUrl={this.ajaxUrl} key={i} changeElementValue={this.props.changeElementValue} />;
			},
			content: element => {
				return <Content data={element} ajaxUrl={this.ajaxUrl} key={i} changeElementValue={this.props.changeElementValue} />;
			},
			dropdown: element => {
				return <Dropdown data={element} ajaxUrl={this.ajaxUrl} key={i} changeElementValue={this.props.changeElementValue} />;
			},
			onechoice: element => {
				return <Onechoice data={element} ajaxUrl={this.ajaxUrl} key={i} changeElementValue={this.props.changeElementValue} />;
			},
			multiplechoice: element => {
				return <Multiplechoice data={element} ajaxUrl={this.ajaxUrl} key={i} changeElementValue={this.props.changeElementValue} />;
			},
			default: element => {
				return <Textfield data={element} ajaxUrl={this.ajaxUrl} key={i} changeElementValue={this.props.changeElementValue} />;
			}
		};

		return (elements[element.type] || elements['default'])(element);
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
	 * Rendering output.
	 *
	 * @since 1.2.0
	 */
	renderComponent() {
		return <div className="torro-forms-elements">{this.renderElements(this.state.elements)}</div>;
	}
}

export default Elements;
