import { Component } from "@wordpress/element";
import Elements from "../Elements/Elements";

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
				<Elements containerId={this.data.id} ajaxUrl={this.ajaxUrl} />
			</div>
		);
	}
}

export default Container;
