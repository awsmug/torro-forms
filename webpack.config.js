const path = require('path');

const assets = {
	src: path.resolve(__dirname, 'src/assets/src/js/Form'),
	dist: path.resolve(__dirname, 'src/assets/dist/js')
};

const webpackConfig = {
	entry: [
		assets.src + '/App.js'
	],
	output: {
		filename: 'frontend-bundle.js',
		path: assets.dist
	},
    module: {
		rules: [
			{
				test: /\.js$/,
				include: [
					assets.src
				],
				use: {
					loader: "babel-loader"
				}
			}
		]
	}
};

module.exports = webpackConfig;
