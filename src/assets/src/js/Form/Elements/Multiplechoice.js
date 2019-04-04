import Element from "./Element";

/**
 * Multiplechoice element.
 *
 * @since 1.2.0
 */
class Multiplechoice extends Element {
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
	 * Changing value.
	 *
	 * @since 1.2.0
	 *
	 * @param event
	 */
	changeValue(event) {
		let elements = document.getElementsByName(this.state.element.input_attrs.name);
		let values = [];

		elements.forEach( (element) => {
			if(element.checked) {
				values.push(element.value);
			}
		});

		this.props.changeElementValue(this.id, values);
	}

	/**
	 * Rendering element.
	 *
	 * @since 1.2.0
	 *
	 * @param {object} Parameters for element.
	 */
	renderElement( params ) {
		let choices = null;

		if( this.state.choices !== null ) {
			choices = this.state.choices.map( ( choice, index )  => {
				let input_name = this.state.element.input_attrs.name;
				let input_id = this.state.element.input_attrs.id;
				let label_id = this.state.element.label_attrs.id;
				let label_for = this.state.element.label_attrs.for;

				input_id = input_id.replace( '%index%', index + 1 );
				label_id = label_id.replace( '%index%', index + 1 );
				label_for = label_for.replace( '%index%', index + 1 );

				return (
					<div className="torro-toggle">
						<input name={input_name} id={input_id} type="checkbox" value={choice.id} onChange={(event) => this.changeValue(event)} />
						<label id={label_id}
							   htmlFor={label_for}
							   className={this.state.element.label_attrs.class}
						>{choice.value}</label>
					</div>
				);
			});
		}

		return (
			<div id={this.state.element.wrap_attrs.id} className={this.state.element.wrap_attrs.class}>
				<legend id={this.state.element.legend_attrs.id} className={this.state.element.legend_attrs.class} dangerouslySetInnerHTML={{__html:this.state.element.label + this.state.element.label_required}} />
				{this.state.element.before}
				{choices}
				{params.element_hints}
				{this.state.element.after}
			</div>
		);
	}
}

export default Multiplechoice;
