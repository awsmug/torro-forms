import Element from "./Element";

/**
 * Textfield element.
 *
 * @since 1.1.0
 */
class Textfield extends Element {
	/**
	 * Constructor.
	 *
	 * @param {*} props
	 *
	 * @since 1.1.0
	 */
	constructor(props) {
		super(props);
	}
	/**
	 * Rendering element.
	 *
	 * @since 1.1.0
	 */
	render() {
		return (
			<div className="torro-element torro-textfield">
				<label for={"torro-element-" + this.data.id}>{this.data.label}</label>
				<input id={"torro-element-" + this.data.id} type="text" />
			</div>
		);
	}
}

export default Textfield;
