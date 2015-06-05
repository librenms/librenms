/*global module:false*/
module.exports = function(grunt) {

    'use strict';
	// Project configuration.
	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),
		meta: {
			banner: '/*! <%= pkg.title || pkg.name %> - v<%= pkg.version %> - ' +
			'<%= grunt.template.today("yyyy-mm-dd") %>\n' +
			'<%= pkg.homepage ? "* " + pkg.homepage : "" %>\n' +
			'* Copyright (c) <%= grunt.template.today("yyyy") %> <%= pkg.author.name %>;' +
			' Licensed <%= _.pluck(pkg.licenses, "type").join(", ") %> */\n\n',

			minibanner: '/*! <%= pkg.title || pkg.name %> - v<%= pkg.version %> - ' +
			'<%= grunt.template.today("yyyy-mm-dd") %> - ' +
			'<%= pkg.homepage ? "* " + pkg.homepage + " - " : "" %>' +
			'Copyright (c) <%= grunt.template.today("yyyy") %> <%= pkg.author.name %>;' +
			' Licensed <%= _.pluck(pkg.licenses, "type").join(", ") %> */ '
		},
		concat: {
			options: {
				stripBanners: true,
				banner: '<%= meta.banner %>'
			},
			dist_js: {
				src: ['src/jquery.coords.js', 'src/jquery.collision.js', 'src/utils.js', 'src/jquery.draggable.js', 'src/jquery.<%= pkg.name %>.js'],
				dest: 'dist/jquery.<%= pkg.name %>.js'
			},

			dist_extras_js: {
				src: ['src/jquery.coords.js', 'src/jquery.collision.js', 'src/utils.js', 'src/jquery.draggable.js', 'src/jquery.<%= pkg.name %>.js', 'src/jquery.<%= pkg.name %>.extras.js'],
				dest: 'dist/jquery.<%= pkg.name %>.with-extras.js'
			}
		},
		uglify: {
			options: {
				banner: '<%= meta.minibanner %>'
			},
			dist: {
				files: {
					'dist/jquery.<%= pkg.name %>.min.js': ['<%= concat.dist_js.dest %>']
				}
			},

			dist_extras: {
				files: {
					'dist/jquery.<%= pkg.name %>.with-extras.min.js': ['<%= concat.dist_extras_js.dest %>']
				}
			}
		},
		cssmin: {
			compress: {
				options: {
					keepSpecialComments: 0,
					banner: '<%= meta.minibanner %>'
				},
				files: {
					'dist/jquery.<%= pkg.name %>.min.css': ['dist/jquery.<%= pkg.name %>.css']
				}
			}
		},
		jshint: {
			options: {
				verbose: true,
				reporter: require('jshint-stylish'),
				jshintrc: '.jshintrc'
			},
		files: ['GruntFile.js', 'src/**/*.js','sample/**/*.js', 'test/**/*.js']
		},
		yuidoc: {
			compile: {
				'name': 'gridster.js',
				'description': 'gridster.js, a drag-and-drop multi-column jQuery grid plugin',
				'version': 'v<%= pkg.version %>',
				'url': 'http://gridster.net/',
				'logo': 'https://ducksboard.com/static/images/svg/logo-ducksboard-black-small.svg',
				options: {
					paths: 'src/',
					outdir: 'gh-pages/docs/'
				}
			}
		},
		replace: {
			'rails-version': {
				src: ['lib/gridster.js-rails/version.rb'],
				dest: 'lib/gridster.js-rails/version.rb',
				replacements: [{
					from: /(\S*)(VERSION = ).*/g,
					to: '$1$2"<%= pkg.version %>"'
				}]
			}
		},
		copy: {
			dist: {
				files: [{
					expand: true,
					dest: 'gh-pages/',
					src: ['dist/*', 'demos/**']
				},{
					expand: true,
					dest: 'dist',
					src: ['src/jquery.gridster.less']
				}]
			},
			rails: {
				files: [{
					expand: true,
					flatten: true,
					dest: 'vendor/assets/javascripts/',
					src: ['dist/*.js']
				}, {
					expand: true,
					flatten: true,
					dest: 'vendor/assets/stylesheets/',
					src: ['dist/*.css']
				}]
			}
		},
		shell: {
			'build-rails-gem': {
				command: 'gem build gridster.js-rails.gemspec'
			},
			'publish-rails-gem': {
				command: 'gem push gridster.js-rails-<%= pkg.version %>.gem'
			}
		},
		'gh-pages': {
			target: {
				options: {
					message: 'update docs for changes from v<%= pkg.version %> ',
					base: 'gh-pages',
					add: true,
					push: true
				},
				src: '**'
			}
		},
		bump: {
			options: {
				files: ['package.json', 'bower.json'],
				updateConfigs: ['pkg'],
				commit: true,
				commitMessage: 'Release v%VERSION%',
				commitFiles: ['package.json', 'bower.json', 'CHANGELOG.md', 'dist/'], // '-a' for all files
				createTag: true,
				tagName: 'v%VERSION%',
				tagMessage: 'Version %VERSION%',
				push: false,
				pushTo: 'origin',
				gitDescribeOptions: '--tags --always --abbrev=1 --dirty=-d' // options to use with '$ git describe'
			}
		},
		clean: {
			dist: [
				'gridster.js-rails*.gem',
				'.grunt',
				'gh-pages',
				'dist',
				'vendor'
			]
		},
		changelog: {
			options: {
				dest: 'CHANGELOG.md'
			}
		},
		watch: {
			files: ['libs/*.js', 'src/*.js', 'src/*.less', 'Gruntfile.js'],
			tasks: ['concat', 'uglify', 'less', 'cssmin']
		},
		qunit: {
			files: [
				'test/jquery.gridster.html'
			]
		},
		less: {
			default: {
				options: {
					sourceMap: true,
					sourceMapFilename: 'dist/jquery.gridster.css.map'
				},
				files: {
					'dist/jquery.gridster.css': 'src/jquery.gridster.less'
				}
			},
			demo: {
				options: {
					sourceMap: true,
					sourceMapFilename: 'demos/assets/css/demo.css.map'
				},
				files: {
					'demos/assets/css/demo.css': 'demos/assets/less/demo.less'
				}
			}
		}
	});

	grunt.loadNpmTasks('grunt-contrib-clean');
	grunt.loadNpmTasks('grunt-contrib-copy');
	grunt.loadNpmTasks('grunt-contrib-watch');
	grunt.loadNpmTasks('grunt-contrib-jshint');
	grunt.loadNpmTasks('grunt-contrib-concat');
	grunt.loadNpmTasks('grunt-contrib-less');
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-cssmin');
	grunt.loadNpmTasks('grunt-contrib-qunit');
	grunt.loadNpmTasks('grunt-contrib-yuidoc');
	grunt.loadNpmTasks('grunt-bump');
	grunt.loadNpmTasks('grunt-conventional-changelog');
	grunt.loadNpmTasks('grunt-gh-pages');
	grunt.loadNpmTasks('grunt-text-replace');
	grunt.loadNpmTasks('grunt-shell');

	// Default task.
	grunt.registerTask('default', ['jshint', 'concat', 'less', 'uglify', 'cssmin', 'replace:rails-version', 'copy:rails']);
	grunt.registerTask('build', ['default', 'qunit', 'shell:build-rails-gem']);
	grunt.registerTask('test', ['jshint','qunit']);
	grunt.registerTask('docs', ['clean', 'build', 'yuidoc', 'copy:dist', 'gh-pages']);

	//builds and releases the gem files
	grunt.registerTask('rails:publish', ['clean', 'build', 'shell:publish-rails-gem']);

	//use one of the four release tasks to build the artifacts for the release (it will push the docs pages only)
	grunt.registerTask('release:patch', ['build', 'bump-only:patch', 'build', 'docs', 'changelog']);
	grunt.registerTask('release:minor', ['build', 'bump-only:minor', 'build', 'docs', 'changelog']);
	grunt.registerTask('release:major', ['build', 'bump-only:major', 'build', 'docs', 'changelog']);
	grunt.registerTask('release:git',   ['build', 'bump-only:git', 'build', 'docs', 'changelog']);

	//use this task to publish the release artifacts
	grunt.registerTask('release:commit', ['bump-commit', 'shell:publish-rails-gem']);

};
