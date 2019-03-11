import { Component } from "@wordpress/element";

/**
 * Description Element.
 *
 * @since 1.2.0
 */
class Description extends Component {
	render () {
		if( this.props.text === '' ) {
			return null;
		}

		return(
			<div id={this.props.id} className={this.props.class}  dangerouslySetInnerHTML={{__html:this.props.text}} />
		);
	}
}

export default Description;
