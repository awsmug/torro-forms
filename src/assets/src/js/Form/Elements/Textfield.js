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
	 * @param {object} instance
	 * @param {string} html
	 *
	 * @since 1.2.0
	 */
	renderElement( data, html ) {
		return (
			<div id={data.wrap_attrs.id} className={data.wrap_attrs.class}>
				{html.before}
				<label id={data.label_attrs.id}
					   htmlFor={data.label_attrs.for}
					   className={data.label_attrs.class}
					   dangerouslySetInnerHTML={{__html:data.label + data.label_required}}>
				</label>

				<div>
					<input id={data.id}
						   type="text"
						   className={data.class}
						   aria-describedby={data.input_attrs["aria-describedby"]}
						   aria-required={data.input_attrs["aria-required"]}
						   value={data.value} />
				</div>
				{html.description}
				{html.errors}
				{html.after}
			</div>
		);
	}
}

export default Textfield;
