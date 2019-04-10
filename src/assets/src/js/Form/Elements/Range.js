import Element from "./Element";

/**
 * Textarea element.
 *
 * @since 1.1.0
 */
class Range extends Element {
	/**
	 * Rendering element.
	 *
	 * @since 1.1.0
	 */
	renderElement( params ) {
		const element = this.state.element;

		let helper_before = null;
		let helper_after = null;

		console.log( element );

		if( element.helper_input === 'before' ) {
			helper_before = (
				<div className="torro-helper-input">
					<input type="text" className={element.helper_input_attrs.class} size={element.helper_input_attrs.size} maxlength={element.helper_input_attrs.maxlength} />
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
							<input type="range" min={element.input_attrs.min} max={element.input_attrs.max} step={element.input_attrs.step} onChange={} />
						</div>
					{helper_after}
				</div>
				{element.after}
			</div>
		);
	}
}

export default Range;
