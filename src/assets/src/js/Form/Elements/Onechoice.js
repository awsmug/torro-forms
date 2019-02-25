import Element from "./Element";

/**
 * Textfield element.
 *
 * @since 1.2.0
 */
class Onechoice extends Element {
	/**
	 * Comstructor
	 *
	 * @since 1.2.0
	 *
	 * @param {*} props
	 */
	constructor(props) {
		super(props);

		this.hasChoices = true;
	}

	renderChoices() {}

	/**
	 * Rendering element.
	 *
	 * @since 1.2.0
	 */
	render() {
		return (
			<div className={"torro-element torro-element-" + this.elementId + " torro-onechoice"}>
				<label htmlFor={"torro-element-" + this.elementId}>{this.state.data.label}</label>
				<input id={"torro-element-" + this.elementId} type="text" value={this.state.data.value} />
			</div>
		);
	}
}

export default Onechoice;
