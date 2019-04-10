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

	changeValue(event) {
		const input = event.target;
		const choices = input.closest( '.torro-element-imagechoice' ).querySelectorAll( '.torro-imagechoice' );

		choices.forEach(choice => {
			choice.classList.remove( 'torro-imagechoice-checked' );
		});

		if ( input.checked ) {
			input.closest( '.torro-imagechoice' ).classList.add( 'torro-imagechoice-checked' );
		}

		super.changeValue(event);
	}

	/**
	 * Rendering element.
	 *
	 * @since 1.2.0
	 *
	 * @param {object} Parameters for element.
	 */
	renderElement( params ) {
		if( this.state.choices === null ) {
			return null;
		}

		const choices_class = this.state.element.choices_attrs.class;
		const choice_class = this.state.element.choice_attrs.class;

		const label_id_base = this.state.element.label_attrs.id;
		const label_for_base = this.state.element.label_attrs.for;

		const input_id_base = this.state.element.input_attrs.id;
		const input_name = this.state.element.input_attrs.name;

		const choices = this.state.choices.map( ( choice, index )  => {
			const img_src = this.state.element.images[choice.value].src[0];
			const img_title = this.state.element.images[choice.value].title;

			let input_id = input_id_base.replace( '%index%', index + 1 );
			let label_id = label_id_base.replace( '%index%', index +1 );
			let label_for = label_for_base.replace( '%index%', index + 1 );

			return (
					<div className={choice_class}>
						<label id={label_id} htmlFor={label_for} className={this.state.element.label_attrs.class} >
							<div className="torro-imagechoice-image">
								<input name={input_name} id={input_id} type="radio" value={choice.value} onChange={(event) => this.changeValue(event)} />
								<img src={img_src} />
							</div>
							{img_title!==''?<div className="torro-imagechoice-title">{img_title}</div>:''}
						</label>
					</div>
			);
		});

		return (
			<fieldset id={this.state.element.wrap_attrs.id} className={this.state.element.wrap_attrs.class}>
				<legend id={this.state.element.legend_attrs.id} className={this.state.element.legend_attrs.class} dangerouslySetInnerHTML={{__html:this.state.element.label + this.state.element.label_required}} />
				{this.state.element.before}
				<div className={choices_class}>
					{choices}
				</div>
				{params.element_hints}
				{this.state.element.after}
			</fieldset>
		);
	}
}

export default Imagechoice;
