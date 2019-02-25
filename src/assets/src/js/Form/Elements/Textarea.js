import Element from "./Element";

/**
 * Textfield element.
 *
 * @since 1.1.0
 */
class Textarea extends Element {
	/**
	 * Rendering element.
	 *
	 * @since 1.1.0
	 */
	render() {
		return (
			<div className={"torro-element torro-element-" + this.elementId + " torro-textarea"}>
				<label htmlFor={"torro-element-" + this.elementId}>{this.state.data.label}</label>
				<textarea id={"torro-element-" + this.elementId}>{this.state.data.value}</textarea>
			</div>
		);
	}
}

export default Textarea;
