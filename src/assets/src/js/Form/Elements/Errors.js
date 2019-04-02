import { Component } from "@wordpress/element";

/**
 * Base form element class.
 *
 * @since 1.1.0
 */
class Errors extends Component {
	renderMessage(text) {
		return <li>{text}</li>;
	}
	render() {
		if (this.props.errors === undefined) {
			return null;
		}

		return (
			<ul id={this.props.id} className={this.props.className}>
				{this.props.errors.map(text => this.renderMessage(text))}
			</ul>
		);
	}
}

export default Errors;
