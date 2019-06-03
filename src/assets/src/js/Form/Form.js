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
		this.submissionId = null;

		this.setDumpNonce(torroFrontendI18n.torro_dump_nonce);

		this.sliderId = 'torro-slider-' + this.id;
		this.sliderMargin = 0;

		this.state = {
			'status': 'progressing',
			'curContainer': 0
		}
	}

	/**
	 * Doing things after component mounted.
	 *
	 * @since 1.2.0
	 */
	componentDidMount() {
		this.syncDownstream()
			.catch(err => {
				console.error(err);
			});
	}

	/**
	 * Checks is there is a next container.
	 *
	 * @since 1.2.0
	 *
	 * @param containerIndex
	 * @returns {boolean}
	 */
	hasNextContainer(containerIndex) {
		if((containerIndex +1) >= this.numContainer ) {
			return false;
		}

		return true;
	}

	/**
	 * Checks is there is a previous container.
	 *
	 * @since 1.2.0
	 *
	 * @param containerIndex
	 * @returns {boolean}
	 */
	hasPrevContainer(containerIndex) {
		if(containerIndex <= 0 ) {
			return false;
		}

		return true;
	}

	/**
	 * Sets to next container.
	 *
	 * @since 1.2.0
	 *
	 * @param event
	 */
	nextContainer(event) {
		if( this.hasNextContainer(this.state.curContainer) ) {
			let curContainer = this.state.curContainer;
			curContainer += 1;

			this.setState({curContainer: curContainer});

			const sliderContent = event.target.closest('.slider-content');
			this.setSlider(sliderContent, 1 );
		}
	}

	/**
	 * Sets to previous container.
	 *
	 * @since 1.2.0
	 *
	 * @param event
	 * @returns {boolean}
	 */
	prevContainer(event) {
		if( this.hasPrevContainer(this.state.curContainer) ) {
			let curContainer = this.state.curContainer;
			curContainer -= 1;

			this.setState({curContainer: curContainer});

			const sliderContent = event.target.closest('.slider-content');
			this.setSlider(sliderContent, -1 );
		}
	}

	/**
	 * Sets current slider.
	 *
	 * @since 1.2.0
	 *
	 * @param sliderContent
	 * @param steps
	 */
	setSlider(sliderContent, steps) {
		const margin = steps * 100;
		const margin_new = this.sliderMargin + margin;
		const sliderContentWidth = 100 * this.state.containers.length;

		if(margin_new < 0 || margin_new >= sliderContentWidth ) {
			return;
		}

		this.sliderMargin = margin_new;
		sliderContent.style.marginLeft = (-1 * this.sliderMargin.toString()) + '%';
	}



	/**
	 * Get Form Data.
	 *
	 * @since 1.2.0
	 */
	syncDownstream() {
		return new Promise((resolve, reject) => {
			const formGetUrl = this.getEndpointUrl("/forms/" + this.id);

			axios
				.get(formGetUrl)
				.then(response => {
					this.setState({form: response.data});
					return this.syncContainersDownstream();
				})
				.catch(err => {
					return reject(err);
				});
		});
	}

	/**
	 * Getting Containers.
	 *
	 * @since 1.2.0
	 */
	syncContainersDownstream() {
		return new Promise((resolve, reject) => {
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

					return resolve();
				})
				.catch(err => {
					return reject(err);
				});
		});
	}

	/**
	 * Saving data to rest API.
	 *
	 * @since 1.2.0
	 *
	 * @return Promise
	 */
	syncUpstream() {
		const self = this;

		return new Promise(function(resolve, reject) {
			let method = 'post';
			let submissionPostUrl = self.getEndpointUrl("/submissions");

			if (self.submissionId !== null) {
				method = 'put';
				submissionPostUrl += '/' + self.submissionId;
			}

			const params = {
				method: method,
				url: submissionPostUrl,
				data: {
					form_id: self.id,
					user_id: self.userId,
					status: self.state.status,
					torro_dump_nonce: self.getDumpNonce()
				}
			};

			axios(params).then(response => {
				self.setSubmissionId(response.data.id);
				self.setDumpNonce(response.data.torro_dump_nonce);

				return resolve( response.data.id );
			}).catch( err => {
				return reject( err );
			});
		});
	}

	/**
	 * Completing submission.
	 *
	 * @since 1.2.0
	 *
	 * @param event
	 */
	completeSubmission(){
		this.setState({status:'completed'});

		this.syncUpstream().catch(err => {
			this.setState({status:'progressing'});
			console.error(err);
		})
	}

	/**
	 * Setting submission id
	 *
	 * @since 1.2.0
	 *
	 * @param {*} id
	 */
	setSubmissionId(id) {
		this.submissionId = id;
	}

	/**
	 * Getting submission id
	 *
	 * @return {*} id
	 */
	getSubmissionId() {
		return this.submissionId;
	}

	setDumpNonce(dumpNonce) {
		this.dumpNonce = dumpNonce;
	}

	getDumpNonce() {
		return this.dumpNonce;
	}

	/**
	 * Rendering content.
	 *
	 * @since 1.2.0
	 */
	renderComponent() {
		if( this.state.status === 'completed' ) {
			return this.renderSuccessMessage();
		}

		return this.renderForm();
	}

	/**
	 * Rendering Form.
	 *
	 * @since 1.2.0
	 *
	 * @returns {*}
	 */
	renderForm() {
		if (this.state.form === undefined || this.state.containers === undefined) {
			return <div className="torro-loading">{this.textLoading}</div>;
		}

		const form = this.state.form.instance;

		let sliderContentWidth = this.state.containers.length * 100;
		let sliderContentStyle = {
			width: sliderContentWidth + '%'
		};

		let form_title = null;

		if( form.show_container_title !== false ) {
			form_title = <h2>{form.title}</h2>
		}

		return (
			<div className="torro-form">
				{form_title}
				<form id={form.id} className={form.form_attrs.class}>
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
	 * Prints out success message.
	 *
	 * @since 1.2.0
	 *
	 * @returns {*}
	 */
	renderSuccessMessage() {
		return (
			<div className="torro-notice torro-success-notice">
				<p>{this.state.form.instance.success_message}</p>
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
		const form = this.state.form.instance;

		return this.state.containers.map((container, i) => (
			<Container
				key={i}
				index={i}
				ajaxUrl={this.props.ajaxUrl}
				formId={this.id}
				getSubmissionId={this.getSubmissionId.bind(this)}
				setSubmissionId={this.setSubmissionId.bind(this)}
				setDumpNonce={this.setDumpNonce.bind(this)}
				getDumpNonce={this.getDumpNonce.bind(this)}
				showContainerTitle={form.show_container_title}
				requiredFieldsText={form.required_fields_text}
				previousButtonLabel={form.previous_button_label}
				nextButtonLabel={form.next_button_label}
				submitButtonLabel={form.submit_button_label}
				data={container}
				curContainer={this.state.curContainer}
				hasPrevContainer={this.hasPrevContainer(i)}
				hasNextContainer={this.hasNextContainer(i)}
				nextContainer={this.nextContainer.bind(this)}
				prevContainer={this.prevContainer.bind(this)}
				syncUpstream={this.syncUpstream.bind(this)}
				completeSubmission={this.completeSubmission.bind(this)}
			/>
		));
	}
}

export default Form;
