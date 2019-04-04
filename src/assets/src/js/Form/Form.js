import ReactDOM from 'react-dom';
import { __ } from "@wordpress/i18n";
import AjaxComponent from "./AjaxComponent";
import Container from "./Container";
import axios from "axios";

/**
 * Class for handling forms.
 *
 * @since 1.2.0
 */
class Form extends AjaxComponent {
	/**
	 * Constructor.
	 *
	 * @since 1.2.0
	 *
	 * @param {*} props Form properties.
	 */
	constructor(props) {
		super(props);

		this.id = parseInt(props.id);
		this.userId = parseInt(props.userId);
		this.wpNonce = props.wpNonce;

		this.sliderId = 'torro-slider-' + this.id;
		this.sliderMargin = 0;

		this.key = this.createUserKey();

		this.status = "progressing";
	}

	/**
	 * Doing things after component mounted.
	 *
	 * @since 1.2.0
	 */
	componentDidMount() {
		this.getForm();
		this.getContainers();
		console.log( ReactDOM.findDOMNode(this) );
	}

	/**
	 * Get Form Data.
	 *
	 * @since 1.2.0
	 */
	getForm() {
		const formGetUrl = this.getEndpointUrl("/forms/" + this.id);

		axios
			.get(formGetUrl)
			.then(response => {
				this.setState({ form: response.data });
			})
			.catch(error => {
				console.error(error);
			});
	}

	/**
	 * Getting Containers.
	 *
	 * @since 1.2.0
	 */
	getContainers() {
		const containersGetUrl = this.getEndpointUrl("/containers?form_id=" + this.id);

		axios
			.get(containersGetUrl)
			.then(response => {
				let containers = response.data;

				containers.sort( function (a, b) {
					return a.sort-b.sort;
				});

				this.numContainer = containers.length;

				this.setState({ containers: containers });
			})
			.catch(error => {
				console.error(error);
			});
	}

	hasNextContainer() {
		if((this.state.curContainer +1) >= this.numContainer ) {
			return false;
		}

		return true;
	}

	hasPrevContainer() {
		if((this.state.curContainer) <= 0 ) {
			return false;
		}

		return true;
	}

	nextContainer(event) {
		if( this.hasNextContainer() ) {
			let curContainer = this.state.curContainer;
			curContainer += 1;

			this.setState({curContainer: curContainer});

			const sliderContent = event.target.closest('.slider-content');
			this.setSlider(sliderContent, 1 );
		}
	}

	prevContainer(event) {
		if( this.hasPrevContainer() ) {
			let curContainer = this.state.curContainer;
			curContainer -= 1;

			this.setState({curContainer: curContainer});

			const sliderContent = event.target.closest('.slider-content');
			this.setSlider(sliderContent, -1 );
		}
	}

	setSlider(sliderContent, steps) {
		const margin = steps * 100;
		const margin_new = this.sliderMargin + margin;
		const sliderContentWidth = 100 * this.state.containers.length;
		console.log( margin_new );
		console.log( sliderContentWidth );

		if(margin_new < 0 || margin_new >= sliderContentWidth ) {
			return;
		}

		this.sliderMargin = margin_new;
		const marginLeft = (-1 * this.sliderMargin.toString()) + '%';
		sliderContent.style.marginLeft= marginLeft;
	}

	/**
	 * Generating user key.
	 *
	 * @since 1.2.0
	 */
	createUserKey() {
		return Math.random()
			.toString(36)
			.substr(2, 9);
	}

	/**
	 * Saving data to rest API.
	 *
	 * @since 1.2.0
	 */
	createSubmission() {
		if (this.state.submissionId !== undefined) {
			return;
		}

		const submissionPostUrl = this.getEndpointUrl("/submissions");

		return axios.post(submissionPostUrl, {
			form_id: this.id,
			user_id: this.userId,
			key: this.createUserKey(),
			status: this.status
		});
	}

	/**
	 * Setting submission id
	 *
	 * @since 1.2.0
	 *
	 * @param {*} id
	 */
	setSubmissionId(id) {
		this.setState({ submissionId: id });
	}

	/**
	 * Rendering content.
	 *
	 * @since 1.2.0
	 */
	renderComponent() {
		if (this.state.form === undefined || this.state.containers === undefined) {
			return this.showTextLoading();
		}

		let sliderContentWidth = this.state.containers.length * 100;
		let sliderContentStyle = {
			width: sliderContentWidth + '%'
		};

		return (
			<div className="torro-form">
				<h2>{this.state.form.title}</h2>
				<form id={this.state.form.instance.id} className={this.state.form.instance.class}>
					<div className="torro-slider">
						<div id={this.sliderId} className="slider-content" style={sliderContentStyle}>
						{this.renderContainers()}
						</div>
					</div>
				</form>
			</div>
		);
	}

	/**
	 * Rendering containers.
	 *
	 * @since 1.2.0
	 *
	 * @param {*} containers
	 */
	renderContainers() {
		return this.state.containers.map((container, i) => (
			<Container
				key={i}
				index={i}
				ajaxUrl={this.props.ajaxUrl}
				formId={this.id}
				submissionId={this.state.submissionId}
				setSubmissionId={this.setSubmissionId.bind(this)}
				data={container}
				curContainer={this.state.curContainer}
				hasPrevContainer={this.hasPrevContainer.bind(this)}
				hasNextContainer={this.hasNextContainer.bind(this)}
				nextContainer={this.nextContainer.bind(this)}
				prevContainer={this.prevContainer.bind(this)}
				createSubmission={this.createSubmission.bind(this)}
			/>
		));
	}
}

export default Form;
