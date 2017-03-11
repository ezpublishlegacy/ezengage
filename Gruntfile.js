/**
npm install grunt --save-dev
npm install grunt-contrib-less --save-dev
npm install grunt-contrib-watch --save-dev
npm install grunt-notify --save-dev
npm install grunt-exec --save-dev
npm install grunt-contrib-concat --save-dev
npm install grunt-contrib-cssmin --save-dev
 */
module.exports = function (grunt) {
  'use strict';

  // Force use of Unix newlines
  grunt.util.linefeed = '\n';

  // Project configuration.
  grunt.initConfig({
      
    cssmin: {
        css_dist: {
            src: ['design/standard/stylesheets/frontend/ezengage.css'/*, 'design/standard/stylesheets/frontend/test.css'*/],
            dest: 'design/standard/stylesheets/frontend/ezengage.min.css',
        },
    },

    exec: {
        clear_cache: {
            command: 'php bin/php/ezcache.php --clear-id=global_ini,ini,classid,template,template-block,content,template-override,rss_cache,design_base',
            cwd: '../../'
        }
    },

    less: {
      compileCore: {
        options: {
          strictMath: true,
          outputSourceFiles: true
        },
        src:  'design/standard/less/frontend/ezengage.less',
        dest: 'design/standard/stylesheets/frontend/ezengage.css'
      }
/*
      compileCore2: {
        options: {
          strictMath: true,
          outputSourceFiles: true
        },
        src:  'design/standard/stylesheets/ezengage.less',
        dest: 'design/standard/stylesheets/ezengage.css'
      },
*/
    },

    notify: {
      less: {
        options: {
          title:      'LESS compile',
          message:    'LESS completed successfully'
        },
      },
    },

    jshint: {
      gruntfile: {
        src: [
          'Gruntfile.js',
        ],
        options: {
          // Rules
          curly       : true,
          newcap      : true,
          browser     : true,
          // warnings
          eqnull      : true,
          multistr    : true,
          loopfunc    : true,
        }
      }
    },

    watch: {
     /*
      gruntfile: {
        files: 'Gruntfile.js',
        tasks: ['jshint:gruntfile'],
      },
      */

      less: {
        files: 'design/standard/less/frontend/*.less',
        tasks: [
          'less-compile',
          'notify:less',
          'cssmin:css_dist',
          'exec:clear_cache',
        ]
      }
    }

  });

  grunt.loadNpmTasks('grunt-contrib-watch');
  grunt.loadNpmTasks('grunt-contrib-less');
  grunt.loadNpmTasks('grunt-notify');
  grunt.loadNpmTasks('grunt-exec');
  grunt.loadNpmTasks('grunt-contrib-concat');
  grunt.loadNpmTasks('grunt-contrib-cssmin');


  // CSS distribution task.
  grunt.registerTask('less-compile', ['less:compileCore'/*, 'less:compileCore2'*/]);

    // Default task.
  grunt.registerTask('default', ['watch']);

};

