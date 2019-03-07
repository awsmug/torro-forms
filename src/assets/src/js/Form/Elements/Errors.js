import { Component } from "@wordpress/element";

/**
 * Base form element class.
 *
 * @since 1.1.0
 */
class Errors extends Component {
	render () {
		let errors = null;

		if( this.props.errors.length > 0 ) {
			errors = this.props.errors.map( error => {
				return (<li>{error}</li>);
			});
		} else {
			return null;
		}

		return(
			<div id={this.props.id} className={this.props.class}>
				<ul>{errors}</ul>
			</div>
		);
	}
}

export default Errors;
