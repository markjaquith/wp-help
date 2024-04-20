const path = require('path');
const autoprefixer = require('autoprefixer');
const DependencyExtraction = require('@wordpress/dependency-extraction-webpack-plugin');

const postCssLoader = {
	loader: 'postcss-loader',
	options: {
		postcssOptions: {
			plugins: () => [autoprefixer],
		},
	},
};

const sassLoader = {
	loader: 'sass-loader',
	options: {
		sourceMap: true,
		implementation: require('sass'),
		sassOptions: {
			indentedSyntax: true,
		},
	},
};

const urlLoader = {
	loader: 'url-loader',
	options: {
		limit: 8192,
	},
};

const outputCssFiles = {
	loader: 'file-loader',
	options: {
		name: '[name].css',
	},
};

const jsRules = {
	test: /\.(m?js|ts)x?$/,
	exclude: /node_modules/,
	loader: 'babel-loader',
};

const imageRules = {
	test: /\.(png|jpe?g|gif)$/i,
	use: [urlLoader],
};

const cssRules = {
	test: /\.css$/,
	use: ['css-loader'],
};

const sassRules = {
	test: /\.sass$/,
	use: [outputCssFiles, postCssLoader, sassLoader],
};

module.exports = {
	context: path.resolve(__dirname),
	entry: {
		index: [
			'./src/index.js',
			'./src/sass/wp-help.sass',
			'./src/sass/dashboard.sass',
		],
		'block-editor': './src/block-editor.js',
	},
	output: {
		path: path.resolve(__dirname, 'dist'),
		publicPath: '/dist/',
	},
	module: {
		rules: [jsRules, imageRules, cssRules, sassRules],
	},
	resolve: {
		extensions: ['.ts', '.tsx', '.js', '.jsx', '.json'],
	},
	plugins: [
		new DependencyExtraction({
			injectPolyfill: true,
		}),
	],
};
