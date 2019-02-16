import { __ } from "@wordpress/i18n";
import AjaxComponent from "../AjaxComponent";
import Containers from "../Container/Containers";

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

		this.setPath("/forms");
		this.updateParams({
			id: this.formId
		});
	}

	/**
	 * Rendering content.
	 *
	 * @since 1.1.0
	 */
	renderComponent() {
		return (
			<div className="torro-form">
				<h2>{this.state.data.title}</h2>
				<form>
					<Containers formId={this.formId} ajaxUrl={this.ajaxUrl} />
				</form>
			</div>
		);
	}
}

export default Form;
