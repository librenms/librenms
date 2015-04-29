/*jshint node:true*/
module.exports = function (grunt)
{
    "use strict";

    /* Hint: Using grunt-strip-code to remove comments from the release file */

    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        banner: '/*! <%= "\\r\\n * " + pkg.title %> v<%= pkg.version %> - <%= grunt.template.today("mm/dd/yyyy") + "\\r\\n" %>' +
                ' * Copyright (c) <%= grunt.template.today("yyyy") %> <%= pkg.author.name %> <%= (pkg.homepage ? "(" + pkg.homepage + ")" : "") + "\\r\\n" %>' +
                ' * Licensed under <%= pkg.licenses[0].type + " " + pkg.licenses[0].url + "\\r\\n */\\r\\n" %>',
        folders: {
            dist: "dist",
            docs: "docs",
            src: "src"
        },

        clean: {
            api: ["<%= folders.docs %>"],
            build: ["<%= folders.dist %>"]
        },
        yuidoc: {
            compile: {
                name: '<%= pkg.name %>',
                description: '<%= pkg.description %>',
                version: '<%= pkg.version %>',
                url: '<%= pkg.homepage %>',
                options: {
                    paths: '<%= folders.dist %>',
                    outdir: '<%= folders.docs %>/'
                }
            }
        },

        less: {
            default: {
                files: {
                    "<%= folders.dist %>/<%= pkg.namespace %>.css": "<%= folders.src %>/<%= pkg.namespace %>.less"
                }
            }
        },

        concat: {
            scripts: {
                options: {
                    separator: '\r\n\r\n',
                    banner: '<%= banner %>;(function ($, window, undefined)\r\n{\r\n    /*jshint validthis: true */\r\n    "use strict";\r\n\r\n',
                    footer: '\r\n})(jQuery, window);',
                    process: function(src, filepath)
                    {
                        var result = src.trim().replace(/(.+?\r\n)/gm, '    $1'),
                            end = [0, ""],
                            lastChar = result[result.length - 1];

                        if (lastChar === ";")
                        {
                            end = (result[result.length - 2] === ")") ?
                            (result[result.length - 2] === "}") ?
                            [3, "    });"] : [2, ");"] : [2, "    };"];
                        }
                        else if (lastChar === "}")
                        {
                            end = [1, "    }"];
                        }

                        return result.substr(0, result.length - end[0]) + end[1];
                    }
                },
                files: {
                    '<%= folders.dist %>/<%= pkg.namespace %>.js': [
                        '<%= folders.src %>/internal.js',
                        '<%= folders.src %>/public.js',
                        '<%= folders.src %>/extensions.js',
                        '<%= folders.src %>/plugin.js'
                    ]
                }
            },
            styles: {
                options: {
                    separator: '\r\n\r\n',
                    banner: '<%= banner %>'
                },
                files: {
                    '<%= folders.dist %>/<%= pkg.namespace %>.css': [
                        '<%= folders.dist %>/<%= pkg.namespace %>.css'
                    ]
                }
            }
        },

        csslint: {
            default: {
                options: {
                    'adjoining-classes': false,
                    'important': false,
                    'outline-none': false,
                    'overqualified-elements': false
                },
                src: '<%= folders.dist %>/<%= pkg.namespace %>.css'
            }
        },
        jshint: {
            options: {
                curly: true,
                eqeqeq: true,
                immed: true,
                latedef: true,
                newcap: true,
                noarg: true,
                sub: true,
                undef: true,
                eqnull: true,
                browser: true,
                globals: {
                    jQuery: true,
                    $: true,
                    console: true
                }
            },
            files: ['<%= folders.dist %>/<%= pkg.namespace %>.js'],
            test: {
                options: {
                    globals: {
                        jQuery: true,
                        $: true,
                        QUnit: true,
                        module: true,
                        test: true,
                        start: true,
                        stop: true,
                        expect: true,
                        ok: true,
                        equal: true,
                        deepEqual: true,
                        strictEqual: true
                    }
                },
                files: {
                    src: [
                        'test/tests-internal.js',
                        'test/tests-rendering.js',
                        'test/tests-extensions.js'
                    ]
                }
            },
            grunt: {
                files: {
                    src: [
                        'Gruntfile.js'
                    ]
                }
            }
        },

        cssmin: {
            default: {
                options: {
                    report: 'gzip'
                },
                files: {
                    '<%= folders.dist %>/<%= pkg.namespace %>.min.css': ['<%= folders.dist %>/<%= pkg.namespace %>.css']
                }
            }
        },
        uglify: {
            default: {
                options: {
                    preserveComments: 'some',
                    report: 'gzip'
                },
                files: {
                    '<%= folders.dist %>/<%= pkg.namespace %>.min.js': ['<%= folders.dist %>/<%= pkg.namespace %>.js']
                }
            }
        },

        nugetpack: {
            default: {
                src: '<%= pkg.namespace %>.nuspec',
                dest: '<%= folders.dist %>',
                options: {
                    version: '<%= pkg.version %>'
                }
            }
        },
        compress: {
            default: {
                options: {
                    archive: '<%= folders.dist %>/<%= pkg.namespace %>-<%= pkg.version %>.zip'
                },
                files: [
                    {
                        flatten: true,
                        expand: true, 
                        src: ['<%= folders.dist %>/*.js', '<%= folders.dist %>/*.css'], dest: '/'
                    }
                ]
            }
        },

        qunit: {
            files: ['test/index.html']
        },

        exec: {
            publish: {
                cmd: 'npm publish .'
            }
        },
        nugetpush: {
            default: {
                src: '<%= folders.dist %>/*.nupkg'
            }
        }
    });

    grunt.loadNpmTasks('grunt-contrib-jshint');
    grunt.loadNpmTasks('grunt-contrib-less');
    grunt.loadNpmTasks('grunt-contrib-csslint');
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    grunt.loadNpmTasks('grunt-contrib-qunit');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-yuidoc');
    grunt.loadNpmTasks('grunt-contrib-clean');
    grunt.loadNpmTasks('grunt-contrib-compress');
    grunt.loadNpmTasks('grunt-exec');
    grunt.loadNpmTasks('grunt-nuget');
    grunt.loadNpmTasks('grunt-regex-replace');

    grunt.registerTask('default', ['build']);
    grunt.registerTask('api', ['clean:api', 'yuidoc']);
    grunt.registerTask('build', ['clean:build', 'less', 'concat', 'csslint', 'jshint', 'qunit']);
    grunt.registerTask('release', ['build', 'api', 'cssmin', 'uglify', 'compress', 'nugetpack']);
    grunt.registerTask('publish', ['nugetpush', 'exec:publish']);
};