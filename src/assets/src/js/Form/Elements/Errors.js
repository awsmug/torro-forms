import { Component } from "@wordpress/element";

/**
 * Base form element class.
 *
 * @since 1.1.0
 */
class Errors extends Component {
	render () {
		if( this.props.errorMessage === undefined ) {
			return null;
		}

		return (<ul><li>{this.props.errorMessage}</li></ul>);			
	}
}

export default Errors;
