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
            <div className={"torro-element torro-element-" + this.data.id + " torro-textarea"}>
                <label htmlFor={"torro-element-" + this.data.id}>{this.data.label}</label>
                <textarea id={"torro-element-" + this.data.id}>{this.data.value}</textarea>
            </div>
        );
    }
}

export default Textarea;
