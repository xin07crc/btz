/**
 * Created by carlo on 02/09/2016.
 */
module.exports = function(grunt) {

    // project configuration.
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        nggettext_extract: {
            pot: {
                files: {
                    'traducciones/pot/traducciones.pot': ['trackers/index.html', 'squelettes/*.html', 'squelettes/inclure/*.html', 'squelettes/formulaires/*.html'], // You can add here more paths separated with commas
                }
            }
        },
        nggettext_compile: {
            all: {
                files: {
                    'traducciones/js/traducciones.js': ['traducciones/pot/*.po']
                }
            }
        }
    });

    grunt.loadNpmTasks('grunt-angular-gettext');

    grunt.registerTask('default', ['nggettext_extract', 'nggettext_compile']); // default task
};