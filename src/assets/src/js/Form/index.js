import React from "react";
import ReactDOM from "react-dom";
import Form from "./Form.js";

let canvas = document.getElementById("torro-forms-react-canvas");
ReactDOM.render(<Form {...canvas.dataset} />, canvas);
