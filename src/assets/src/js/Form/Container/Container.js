import { Component } from '@wordpress/element';
import Elements from '../Elements/Elements';

class Container extends Component {
	/**
	 * Constructor.
	 *
	 * @since 1.1.0
	 *
	 * @param {*} props Container properties.
	 */
	constructor(props) {
		super(props);

		this.ajaxUrl = props.ajaxUrl;
		this.data = props.data;

		this.state = { elements: [] };
	}

	/**
	 * Changing the element value and updating state
	 *
	 * @since 1.2.0
	 *
	 * @param elementId
	 * @param value
	 */
	changeElementValue( elementId, value ) {
		let elements = this.state.elements;

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

		console.log( elements );

		this.setState({elements: elements});
	}

	handleSubmit(e) {
		event.preventDefault();
	}

	/**
	 * Rendering output.
	 *
	 * @since 1.1.0
	 */
	render() {
		return (
			<div className="torro_container">
				<h3>{this.data.label}</h3>
				<Elements containerId={this.data.id} ajaxUrl={this.ajaxUrl} changeElementValue={this.changeElementValue.bind(this)} />
				<input type="submit" value="Submit" onClick={e => this.handleSubmit(e)} />
			</div>
		);
	}
}

export default Container;
