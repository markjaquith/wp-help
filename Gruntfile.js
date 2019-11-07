const webpackConfig = require('./webpack.config.js');

module.exports = grunt => {
	const ignores = [
		'!assets/**',
		'!node_modules/**',
		'!vendor/**',
		'!release/**',
		'!.git/**',
		'!.sass-cache/**',
		'!.gitignore',
		'!.gitmodules',
		'!tests/**',
		'!bin/**',
		'!.travis.yml',
		'!phpunit.xml',
	];

	// Project configuration
	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),

		env: {
			prod: {
				NODE_ENV: 'production',
			},
			dev: {
				NODE_ENV: 'development',
			},
		},

		webpack: {
			options: {
				stats: !process.env.NODE_ENV || process.env.NODE_ENV === 'development',
			},
			prod: Object.assign({}, webpackConfig, { mode: 'production' }),
			dev: Object.assign({}, webpackConfig, { mode: 'development' }),
		},

		phpunit: {
			default: {},
		},

		watch: {
			php: {
				files: ['**/*.php', ...ignores],
				tasks: ['phpunit'],
				options: {
					debounceDelay: 5000,
				},
			},
			css: {
				files: ['src/**'],
				tasks: ['webpack:dev'],
			},
			package: {
				files: ['package.json'],
				tasks: ['replace'],
			},
		},

		clean: {
			release: [
				'release/<%= pkg.version %>/',
				'release/latest/',
				'release/svn/',
			],
			svn_readme_md: ['release/svn/readme.md'],
		},

		copy: {
			release: {
				src: ['**', ...ignores],
				dest: 'release/<%= pkg.version %>/',
			},
			latest: {
				cwd: 'release/<%= pkg.version %>/',
				src: ['**'],
				dest: 'release/latest/',
			},
			svn: {
				cwd: 'release/<%= pkg.version %>/',
				src: ['**'],
				dest: 'release/svn/',
			},
		},

		replace: {
			header: {
				src: ['<%= pkg.name %>.php'],
				overwrite: true,
				replacements: [
					{
						from: /Version:(\s*?)[a-zA-Z0-9.-]+$/m,
						to: 'Version:$1<%= pkg.version %>',
					},
					{
						from: /Copyright \(c\) 20[0-9]{2}-20[0-9]{2} .*$/m,
						to:
							'Copyright (c) <%= pkg.firstCopyright %>-' +
							new Date().getFullYear() +
							' <%= pkg.author.name %> (email: <%= pkg.author.email %>)',
					},
				],
			},
			plugin: {
				src: ['classes/plugin.php'],
				overwrite: true,
				replacements: [
					{
						from: /^(\s*?)const(\s+?)VERSION(\s*?)=(\s+?)'[^']+';/m,
						to: "$1const$2VERSION$3=$4'<%= pkg.version %>';",
					},
					{
						from: /^(\s*?)const(\s+?)CSS_JS_VERSION(\s*?)=(\s+?)'[^']+';/m,
						to: "$1const$2CSS_JS_VERSION$3=$4'<%= pkg.version %>';",
					},
				],
			},
			readme: {
				src: ['readme.md'],
				overwrite: true,
				replacements: [
					{
						from: /Stable tag:(\s*?)[a-zA-Z0-9.-]+\s*?$/im,
						to: 'Stable tag:$1<%= pkg.version %>  ',
					},
				],
			},
			svn_readme: {
				src: ['release/svn/readme.md'],
				dest: 'release/svn/readme.txt',
				replacements: [
					{
						from: /^# (.*?)( #+)?$/gm,
						to: '=== $1 ===',
					},
					{
						from: /^## (.*?)( #+)?$/gm,
						to: '== $1 ==',
					},
					{
						from: /^### (.*?)( #+)?$/gm,
						to: '= $1 =',
					},
					{
						from: /^Stable tag:\s*?[a-zA-Z0-9.-]+(\s*?)$/im,
						to: 'Stable tag: <%= pkg.version %>$1',
					},
				],
			},
		},

		compress: {
			default: {
				options: {
					mode: 'zip',
					archive: './release/<%= pkg.name %>.<%= pkg.version %>.zip',
				},
				expand: true,
				cwd: 'release/<%= pkg.version %>/',
				src: ['**/*'],
				dest: '<%= pkg.name %>/',
			},
		},

		notify_hooks: {
			options: {
				success: true,
			},
		},

		prettier: {
			options: {
				singleQuote: true,
				useTabs: true,
				trailingComma: 'es5',
			},
			default: {
				src: ['js/**/*.jsx'],
			},
		},

		wp_deploy: {
			deploy: {
				options: {
					plugin_slug: '<%= pkg.name %>',
					svn_user: '<%= pkg.name %>',
					build_dir: 'release/svn',
					assets_dir: 'assets',
				},
			},
		},
	});

	require('load-grunt-tasks')(grunt);

	grunt.task.run('notify_hooks');

	grunt.registerTask('default', [
		'env:dev',
		'replace',
		'prettier',
		'webpack:dev',
	]);

	grunt.registerTask('dev', [
		'default',
		'watch',
	]);

	grunt.registerTask('default:prod', [
		'env:prod',
		'replace',
		'webpack:prod',
	]);

	grunt.registerTask('build', [
		'default:prod',
		'clean',
		'copy:release',
		'copy:latest',
		'copy:svn',
	]);

	grunt.registerTask('release', ['build', 'wp_deploy']);

	grunt.util.linefeed = '\n';
};
