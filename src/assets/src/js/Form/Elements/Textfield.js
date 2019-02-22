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
	 *
	 * @since 1.2.0
	 */
	renderElement( data ) {
		let description;

		if( data.description !== '' ){
			description = () =>  { return (
				<div id={data.description_attrs.id} className={data.description_atts.class}>
					{data.description}
				</div>
			)};
		}

		let errors;

		if( data.errors.length > 0  ){
			errors = () => { return (
				<div id={data.errors_attrs.id} className={data.errors_attrs.class}>
					{data.errors}
				</div>
			)};
		}

		return (
			<div id={data.wrap_attrs.id} className={data.wrap_attrs.class}>
				{data.before}
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
				{description}
				{errors}
				{data.after}
			</div>
		);
	}
}

export default Textfield;
