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
	renderElement( params ) {
		return (
			<div id={this.state.element.wrap_attrs.id} className={this.state.element.wrap_attrs.class}>
				{this.state.element.before}
				<label id={this.state.element.label_attrs.id}
					   htmlFor={this.state.element.label_attrs.for}
					   className={this.state.element.label_attrs.class}
					   dangerouslySetInnerHTML={{__html:this.state.element.label + this.state.element.label_required}}>
				</label>

				<div>
					<input name={this.state.element.input_attrs.name}
						   type="text"
						   data-element-id={this.state.element.id}
						   className={this.state.element.class}
						   aria-describedby={this.state.element.input_attrs["aria-describedby"]}
						   aria-required={this.state.element.input_attrs["aria-required"]}
						   defaultValue={this.state.element.value}
						   onBlur={(event) => this.changeValue(event)} />
					{params.element_hints}
				</div>
				{this.state.element.after}
			</div>
		);
	}
}

export default Textfield;
