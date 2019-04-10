import Element from "./Element";

/**
 * Textarea element.
 *
 * @since 1.2.0
 */
class Range extends Element {

	/**
	 * Syncing Change in range to helper input.
	 *
	 * @since 1.2.0
	 *
	 * @param event
	 */
	rangeChange( event ) {
		const input = event.target;
		const value = input.value;

		const helper_input = input.closest( '.torro-element-range' ).querySelector('.torro-helper-input input' );
		helper_input.value = value;

		this.changeValue( event );
	}

	/**
	 * Syncing Change in helper input to range.
	 *
	 * @since 1.2.0
	 *
	 * @param event
	 */
	rangeChangeBack( event ) {
		const helper_input = event.target;
		const value = helper_input.value;

		const input = helper_input.closest( '.torro-element-range' ).querySelector('.torro-input input' );

		input.value = value;

		this.changeValue( event );
	}

	/**
	 * Rendering element.
	 *
	 * @since 1.2.0
	 */
	renderElement( params ) {
		const element = this.state.element;

		let helper_before = null;
		let helper_after = null;

		if( element.helper_input === 'before' ) {
			helper_before = (
				<div className="torro-helper-input">
					<input type="text" className={element.helper_input_attrs.class} size={element.helper_input_attrs.size} maxLength={element.helper_input_attrs.maxlength} onChange={event => this.rangeChangeBack(event)} />
				</div>
			);
		};

		if( element.helper_input === 'after' ) {
			helper_after = (
				<div className="torro-helper-input">
					<input type="text" className={element.helper_input_attrs.class} size={element.helper_input_attrs.size} maxLength={element.helper_input_attrs.maxlength}/>
				</div>
			);
		};

		return (
			<div id={element.wrap_attrs.id} className={element.wrap_attrs.class}>
				{element.before}
				<label id={element.label_attrs.id}
					   htmlFor={element.label_attrs.for}
					   className={element.label_attrs.class}
					   dangerouslySetInnerHTML={{__html:element.label + element.label_required}}>
				</label>

				<div>
					{helper_before}
						<div className="torro-input">
							<input
								type="range"
								name={element.input_attrs.name}
								data-element-id={element.id}
								min={element.input_attrs.min}
								max={element.input_attrs.max}
								step={element.input_attrs.step}
								aria-describedby={element.input_attrs["aria-describedby"]}
								aria-required={element.input_attrs["aria-required"]}
								defaultValue={element.value}
								onChange={event => this.rangeChange(event)}
							/>
						</div>
					{helper_after}
				</div>
				{element.after}
			</div>
		);
	}
}

export default Range;
