import Element from "./Element";

/**
 * Textfield element.
 *
 * @since 1.1.0
 */
class Content extends Element {
	/**
	 * Rendering element.
	 *
	 * @since 1.1.0
	 */
	render() {
		return (
			<div className={this.state.element.wrap_attrs.class}>
			{this.state.element.before}
			<div dangerouslySetInnerHTML={{ __html: this.state.element.label }} />
			{this.state.element.after}
			</div>
		);
	}
}

export default Content;
