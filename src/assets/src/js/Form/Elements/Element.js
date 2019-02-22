import { Component } from "@wordpress/element";
import ElementSettings from "./ElementSettings";
import ElementChoices from "./ElementChoices";

/**
 * Base form element class.
 *
 * @since 1.1.0
 */
class Element extends Component {
	/**
	 * Constructor.
	 *
	 * @param {*} props
	 *
	 * @since 1.2.0
	 */
	constructor(props) {
		super(props);

		if (new.target === Element) {
			throw new TypeError("Cannot construct abstract instances directly");
		}

		this.ajaxUrl = props.ajaxUrl;

		this.elementId = props.data.id;
		this.hasChoices = false;

		this.state = {
			status: props.data.status,
			elementId: props.data.id,
			label: props.data.label
		};

		this.settings = null;
		this.choices = null;
	}

	/**
	 * Doing things after component mounted.
	 *
	 * @since 1.2.0
	 */
	componentDidMount() {
		this.syncDownSettings();

		if (this.hasChoices) {
			this.syncDownChoices();
		}
	}

	/**
	 * Syncing down Settings.
	 *
	 * @since 1.2.0
	 */
	syncDownSettings() {
		const settingsParams = {
			ajaxUrl: this.ajaxUrl,
			elementId: this.elementId
		};

		this.settings = new ElementSettings(settingsParams);
		this.settings
			.syncDownstream()
			.then(settings => {
				const elementSettings = {
					settings: settings.data
				};
				const state = Object.assign(this.state, elementSettings);
				this.setState(state);
			})
			.catch(error => {
				console.error(error);
			});
	}

	/**
	 * Syncing down Choices.
	 *
	 * @since 1.2.0
	 */
	syncDownChoices() {
		const choicesParams = {
			ajaxUrl: this.ajaxUrl,
			elementId: this.elementId
		};

		this.choices = new ElementChoices(choicesParams);
		this.choices
			.syncDownstream()
			.then(choices => {
				const elementChoices = {
					choices: choices.data
				};
				const state = Object.assign(this.state, elementChoices);
				this.setState(state);
				console.log(this);
			})
			.catch(error => {
				console.error(error);
			});
	}

	/**
	 * Transforms an array of attributes into an attribute string.
	 *
	 * @since 1.2.0
	 *
	 * @param {object} attrs Object of `key: value` pairs like { class: "class-test", title: "test" }.
	 * @return string Attribute string.
	 */
	attrs(attrs) {
		let pairs = Object.entries(values).map(value => {
			return value[0] + '="' + value[1] + '"';
		});

		return pairs.join(" ");
	}
}

export default Element;
