import { Component } from '@wordpress/element';
import Elements from '../Elements/Elements';

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

		this.ajaxUrl = props.ajaxUrl;
		this.data = props.data;
	}

	handleUpdate(event) {
		event.preventDefault();
	}

	/**
	 * Rendering output.
	 *
	 * @since 1.2.0
	 */
	render() {
		return (
			<div className="torro_container">
				<h3>{this.data.label}</h3>
				<Elements containerId={this.data.id} ajaxUrl={this.ajaxUrl} setElementValue={this.props.setElementValue.bind(this)} />
				<input type="submit" value="Submit" onClick={event => this.handleUpdate(event)} />
			</div>
		);
	}
}

export default Container;
