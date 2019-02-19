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
            <div className={"torro-element torro-element-" + this.data.id + " torro-content"} dangerouslySetInnerHTML={{ __html: this.data.label }}></div>
        );
    }
}

export default Content;