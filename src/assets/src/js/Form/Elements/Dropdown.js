import Element from "./Element";

/**
 * Dropdown element.
 *
 * @since 1.2.0
 */
class Dropdown extends Element {
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

	/**
	 * Rendering element.
	 *
	 * @param {object} data Element object this.state.element.
	 *
	 * @since 1.2.0
	 */
	renderElement( params ) {
		let options = null;
		let options_placeholder = null;

		if( this.state.choices !== null ) {
			options = this.state.choices.map(choice => {
				return <option value={choice.id}>{choice.value}</option>
			});

			if( this.state.element.placeholder !== '' ) {
				options_placeholder = ( <option>{this.state.element.placeholder}</option> );
			}

			if( options_placeholder !== null ) {
				options.unshift(options_placeholder);
			}
		}

		return (
			<div id={this.state.element.wrap_attrs.id} className={this.state.element.wrap_attrs.class}>
				{this.state.element.before}
				<label id={this.state.element.label_attrs.id}
					   htmlFor={this.state.element.label_attrs.for}
					   className={this.state.element.label_attrs.class}
					   dangerouslySetInnerHTML={{__html:this.state.element.label + this.state.element.label_required}} />

				<div>
					<select name={this.state.element.input_attrs.name}
						   id={this.state.element.id}
						   type="text"
						   className={this.state.element.class}
						   aria-describedby={this.state.element.input_attrs["aria-describedby"]}
						   aria-required={this.state.element.input_attrs["aria-required"]}
						   defaultValue={this.state.element.value}
						   onChange={(event) => this.changeValue(event)} >
						{options}
					</select>
					{params.element_hints}
				</div>
				{this.state.element.after}
			</div>
		);
	}
}

export default Dropdown;
