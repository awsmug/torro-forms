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
			<div className={"torro-element torro-element-" + this.data.id + " torro-textfield"}>
				<label htmlFor={"torro-element-" + this.data.id}>{this.data.label}</label>
				<input id={"torro-element-" + this.data.id} type="text" value={this.data.value} />
			</div>
		);
	}
}

export default Textfield;
