import Element from "./Element";
import ElementChoices from "./ElementChoices";

/**
 * Textfield element.
 *
 * @since 1.2.0
 */
class Onechoice extends Element {
    /**
     * Comstructor
     * 
     * @since 1.2.0
     * 
     * @param {*} props 
     */
    constructor(props) {
        super(props);

        this.ajaxUrl = props.ajaxUrl;
        this.data = props.data;

        const params = {
            elementId: this.data.id,
            ajaxUrl: this.ajaxUrl
        };

        this.choices = new ElementChoices(params);

        this.state = {
            data: null,
            choices: null
        }
    }

    componentDidMount() {
        this.requestChoices();
    }

    requestChoices() {
        this.choices.request().then(data => {
            this.state.choices = data.data;
        }).catch(error => {
            console.error(error);
        });
    }

    renderChoices() {

    }

	/**
	 * Rendering element.
	 *
	 * @since 1.2.0
	 */
    render() {
        return (
            <div className={"torro-element torro-element-" + this.data.id + " torro-onechoice"}>
                <label htmlFor={"torro-element-" + this.data.id}>{this.data.label}</label>
                <input id={"torro-element-" + this.data.id} type="text" value={this.data.value} />
            </div>
        );
    }
}

export default Onechoice;
