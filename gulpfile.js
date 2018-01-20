var gulp = require('gulp');
var uglify = require('gulp-uglify');
var concat = require('gulp-concat');

var adminJs =
[
  'node_modules/jquery/dist/jquery.js',
  'node_modules/slideout/dist/slideout.js',
  'share/admin.js'
];
var outputDir = 'share';

gulp.task('adminjs', function() {
  gulp.src(adminJs)
  .pipe(uglify())
  .pipe(concat('admin-all-min.js'))
  .pipe(gulp.dest(outputDir));
});

gulp.task('watch', function() {
  gulp.watch(adminJs, ['adminjs']);
});

gulp.task('default', ['adminjs', 'watch']);
