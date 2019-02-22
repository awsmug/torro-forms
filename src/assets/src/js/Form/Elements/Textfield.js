import Element from "./Element";

/**
 * Textfield element.
 *
 * @since 1.2.0
 */
class Textfield extends Element {
	/**
	 * Rendering element.
	 *
	 * @since 1.2.0
	 */
	render() {
		return (
			<div className={"torro-element torro-element-" + this.elementId + " torro-textfield"}>
				<label htmlFor={"torro-element-" + this.elementId}>{this.state.label}</label>
				<input id={"torro-element-" + this.elementId} type="text" value={this.state.value} />
			</div>
		);
	}
}

export default Textfield;
