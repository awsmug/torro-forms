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
	renderElement( data ) {
		return (
			<div id={data.wrap_attrs.id} className={data.wrap_attrs.class}>
				{data.before}
				<label id={data.label_attrs.id}
					   htmlFor={data.label_attrs.for}
					   className={data.label_attrs.class}
					   dangerouslySetInnerHTML={{__html:data.label + data.label_required}}>
				</label>

				<div>
					<textarea id={data.id}
						   type="text"
						   className={data.class}
						   aria-describedby={data.input_attrs["aria-describedby"]}
						   aria-required={data.input_attrs["aria-required"]}
							  defaultValue={data.value} />
					{data.element_hints}
				</div>
				{data.after}
			</div>
		);
	}
}

export default Textarea;
