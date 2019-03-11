import { __ } from '@wordpress/i18n';
import AjaxComponent from '../AjaxComponent';
import Containers from '../Container/Containers';

/**
 * Class for handling forms.
 *
 * @since 1.1.0
 */
class Form extends AjaxComponent {
	/**
	 * Constructor.
	 *
	 * @since 1.1.0
	 *
	 * @param {*} props Form properties.
	 */
	constructor(props) {
		super(props);
		this.formId = props.id;

		this.setParams({
			id: this.formId
		});
	}

	/**
	 * Creating query string for get request.
	 *
	 * @param {object} params Parameters for API query.
	 *
	 * @returns {string} Query string.
	 */
	getQueryString(params) {
		return '/forms/' + params.id;
	}

	/**
	 * Rendering content.
	 *
	 * @since 1.1.0
	 */
	renderComponent() {
		let htmlId = null;
		let cssClasses = null;

		if( this.state !== null ) {
			htmlId = this.state.data.instance.id;
			cssClasses = this.state.data.instance.class;
		}

		return (
			<div className="torro-form">
				<h2>{this.state.data.title}</h2>
				<form id={htmlId} className={cssClasses}>
					<Containers formId={this.formId} ajaxUrl={this.ajaxUrl} />
				</form>
			</div>
		);
	}
}

export default Form;
