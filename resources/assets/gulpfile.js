var gulp = require('gulp'),
    util = require('gulp-util'),
    sass = require('gulp-sass'),
    // pug = require('gulp-pug'),
    concat = require('gulp-concat'),
    uglify = require('gulp-uglify'),
    // imagemin = require('gulp-imagemin'),
    rename = require('gulp-rename'),
    plumber = require('gulp-plumber'),
    browserSync = require('browser-sync').create(),
    reload = browserSync.reload,
    watch = require('gulp-watch');


var SASS_SRC = './sass';
var JS_SRC   = './js';
var CSS_DEST = '../../public/css';
var JS_DEST  = '../../public/js';

var errorHandler = function (error) {
  console.log(error.message);
  this.emit("end");
}

gulp.task('styles', function() {
  return gulp.src(SASS_SRC+'/application.sass')
    .pipe(plumber({ errorHandler: errorHandler }))
    .pipe(sass({ indentedSyntax: true }).on('error', util.log))
    .pipe(concat('application.css'))
    .pipe(gulp.dest(CSS_DEST))
    .pipe(browserSync.reload({ stream:true }))
});

gulp.task('scripts', function () {
  return gulp.src(JS_SRC+'/**/*.js')
    .pipe(plumber())
    .pipe(concat("application.js"))
    .pipe(gulp.dest(JS_DEST))
    .pipe(rename("application.min.js"))
    // .pipe(uglify())
    .pipe(gulp.dest(JS_DEST))
    .pipe(browserSync.reload({ stream:true }))
});

gulp.task('watch', function() {
  watch(SASS_SRC+'/**/*.sass', function(){ gulp.start('styles'); })
  watch(JS_SRC+'/**/*.js', function(){ gulp.start('scripts'); })
});


// -----------------------------------------

gulp.task('default', ['styles','scripts']);

