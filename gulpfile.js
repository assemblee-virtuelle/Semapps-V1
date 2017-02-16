var gulp = require('gulp');
var babel = require('gulp-babel');
var sourcemaps = require('gulp-sourcemaps');
var uglify = require('gulp-uglify');
var concat = require('gulp-concat');
var git = require('gulp-git');
var sync = require('gulp-sync')(gulp);
var cache = require('gulp-cached');
var filesJs = [
  'web/admin/js/script.js'
];

gulp.task('buildCoreJs', () => {
  // One task for each file separately.
  filesJs.map((filePath) => {
    console.log('Building ' + filePath + ' ...');
    // Find base path.
    var path = filePath.split('/');
    // Get filename without extension.
    var fileName = path.pop().split('.');
    fileName.pop();
    fileName = fileName.join('.');
    // Build path.
    path = path.join('/');

    // Create task.
    gulp.src(path + '/' + fileName + '.js', {base: "./"})
      // If file has not changed, stops here.
      .pipe(cache(filePath))
      // Create ap file.
      .pipe(sourcemaps.init())
      // Transpile.
      .pipe(babel({
        presets: ['latest']
      }))
      // Set dest name.
      .pipe(concat(fileName + '.min.js'))
      // Compress.
      .pipe(uglify())
      // Write map file.
      .pipe(sourcemaps.write('.'))
      // Write.
      .pipe(gulp.dest(path));
  });
});

// Define files to watch.
gulp.task('watch', () => {
  gulp.watch(filesJs, ['buildCoreJs']);
});

gulp.task('default', ['watch']);
