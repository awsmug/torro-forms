import Element from "./Element";

/**
 * Multiplechoice element.
 *
 * @since 1.2.0
 */
class Imagechoice extends Element {
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
	 * @since 1.2.0
	 *
	 * @param {object} Parameters for element.
	 */
	renderElement( params ) {
		let choices = null;

		if( this.state.choices !== null ) {
			choices = this.state.choices.map( ( choice, index )  => {
				let choice_class = this.state.element.choice_attrs.class;

				let label_id = this.state.element.label_attrs.id;
				let label_for = this.state.element.label_attrs.for;

				let input_name = this.state.element.input_attrs.name;
				let input_id = this.state.element.input_attrs.id;

				let image = this.state.element.images.img;


				input_id = input_id.replace( '%index%', index + 1 );
				label_id = label_id.replace( '%index%', index +1 );
				label_for = label_for.replace( '%index%', index + 1 );

				return (
					<div className={choice_class}>
						<div className="torro-imagechoice-image">
							<label id={label_id}
								   htmlFor={label_for}
								   className={this.state.element.label_attrs.class} >
									<input name={input_name} id={input_id} type="radio" value={choice.value} onChange={(event) => this.changeValue(event)} />
									{image}
							</label>
						</div>
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

export default Imagechoice;
