var fs = require('fs');
var glob = require('glob');
var gulp = require('gulp');
var cache = require('gulp-cached');
var cleanCSS = require('gulp-clean-css');
var concat = require('gulp-concat');
var eslint = require('gulp-eslint');
var groupFiles = require('gulp-group-files');
var phpcs = require('gulp-phpcs');
var phplint = require('gulp-phplint');
var uglify = require('gulp-uglify');
var path = require('path');

var sitePath = 'osCommerce/OM/Custom/Site/Sites';
var sitePublicPath = 'public/sites/Sites';
var applicationPublicPath = sitePublicPath + '/Application';

gulp.task('php', function() {
    return gulp.src(sitePath + '/**/*.php')
           .pipe(cache('phplint'))
           .pipe(phplint('', {
               skipPassedFiles: true
           }))
// removed phpcs sniff PSR2.Namespaces.UseDeclaration due
// to phpcs v2.5.1 not supporting PHP7 group "use" feature
           .pipe(phpcs({
               bin: 'phpcs.bat', //'phpcs',
               standard: 'PSR2',
               warningSeverity: 0,
               encoding: 'utf-8'
           }))
           .pipe(phpcs.reporter('log'));
});

gulp.task('scripts', function() {
    var result = {};

    var files = glob.sync(sitePublicPath + '/javascript/*([a-z]).js');

    for (var i = 0; i < files.length; i++) {
        var group = path.basename(files[i], '.js');

        if (typeof result[group] === 'undefined') {
            result[group] = [];
        }

        result[group].push(files[i]);
    };

    for (var group in result) {
        var files = glob.sync(sitePublicPath + '/javascript/*(' + group + ')-*.js');

        for (var i = 0; i < files.length; i++) {
            result[group].push(files[i]);
        }
    };

    for (var group in result) {
        groupFiles(result[group], function (name, files) {
            gulp.src(files)
            .pipe(eslint())
            .pipe(eslint.format())
            .pipe(concat(group + '.min.js'))
            .pipe(uglify())
            .pipe(gulp.dest(sitePublicPath + '/javascript/'));

            return {
                pipe: function() {},
                on: function() {}
            };
        })();
    }
});

gulp.task('appScripts', function() {
    var result = {};

    var applications = glob.sync(applicationPublicPath + '/*');

    for (var i = 0; i < applications.length; i++) {
        if (fs.statSync(applications[i]).isDirectory()) {
            result[path.basename(applications[i])] = {};
        }
    }

    for (var application in result) {
        var files = glob.sync(applicationPublicPath + '/' + application + '/*([a-z]).js');

        for (var i = 0; i < files.length; i++) {
            var group = path.basename(files[i], '.js');

            if (typeof result[application][group] === 'undefined') {
                result[application][group] = [];
            }

            result[application][group].push(files[i]);
        };

        for (var group in result[application]) {
            var files = glob.sync(applicationPublicPath + '/' + application + '/*(' + group + ')-*.js');

            for (var i = 0; i < files.length; i++) {
                result[application][group].push(files[i]);
            }
        };
    };

    for (var application in result) {
        groupFiles(result[application], function (name, files) {
            gulp.src(files)
            .pipe(eslint())
            .pipe(eslint.format())
            .pipe(concat(name + '.min.js'))
            .pipe(uglify())
            .pipe(gulp.dest(applicationPublicPath + '/' + application));

            return {
                pipe: function() {},
                on: function() {}
            };
        })();
    }
});

gulp.task('css', function() {
    var result = {};

    var templates = glob.sync(sitePublicPath + '/templates/*');

    for (var i = 0; i < templates.length; i++) {
        if (fs.statSync(templates[i]).isDirectory()) {
            result[path.basename(templates[i])] = {};
        }
    }

    for (var template in result) {
        var files = glob.sync(sitePublicPath + '/templates/' + template + '/stylesheets/*([a-z]).css');

        for (var i = 0; i < files.length; i++) {
            var group = path.basename(files[i], '.css');

            if (typeof result[template][group] === 'undefined') {
                result[template][group] = [];
            }

            result[template][group].push(files[i]);
        };

        for (var group in result[template]) {
            var files = glob.sync(sitePublicPath + '/templates/' + template + '/stylesheets/*(' + group + ')-*.css');

            for (var i = 0; i < files.length; i++) {
                result[template][group].push(files[i]);
            }
        };
    };

    for (var template in result) {
        groupFiles(result[template], function (name, files) {
            gulp.src(files)
            .pipe(concat(name + '.min.css'))
            .pipe(cleanCSS())
            .pipe(gulp.dest(sitePublicPath + '/templates/' + template + '/stylesheets'));

            return {
                pipe: function() {},
                on: function() {}
            };
        })();
    }
});

gulp.task('watch', function() {
    gulp.watch(sitePath + '/**/*.php', ['php']);
    gulp.watch([sitePublicPath + '/javascript/*.js', '!' + sitePublicPath + '/javascript/*.min.js'], ['scripts']);
    gulp.watch([applicationPublicPath + '/**/*.js', '!' + applicationPublicPath + '/**/*.min.js'], ['appScripts']);
    gulp.watch([sitePublicPath + '/templates/**/stylesheets/*.css', '!' + sitePublicPath + '/templates/**/stylesheets/*.min.css'], ['css']);
});

gulp.task('default', ['watch', 'php', 'scripts', 'appScripts', 'css']);

gulp.task('x', ['php', 'scripts', 'appScripts', 'css']);
