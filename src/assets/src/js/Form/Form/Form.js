import React, { Component } from "react";
// import Container from "./Container/Container.js";

class Form extends Component {

  constructor() {
  	super();
  	this.formID = 123;
	  this.activeContainer = 1;
  }

  render() {
    return (
      <div>
        <h1>Torro Forms is going react!</h1>
        <p>Let see what we can get!</p>
      </div>
    );
  }
};

export default Form;
