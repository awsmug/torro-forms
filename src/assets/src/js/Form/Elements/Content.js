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
		return <div className={"torro-element torro-element-" + this.elementId + " torro-content"} dangerouslySetInnerHTML={{ __html: this.state.data.label }} />;
	}
}

export default Content;
