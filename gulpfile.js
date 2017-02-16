var gulp = require('gulp');
var babel = require('gulp-babel');
var sourcemaps = require('gulp-sourcemaps');
var uglify = require('gulp-uglify');
var concat = require('gulp-concat');
var git = require('gulp-git');
var sync = require('gulp-sync')(gulp);

// For each file (with no extension),
// if value is "true", use the .js version to build .min.js version,
// if value is an array, aggregate the files to the .min.js version.
var filesJs = {
  // Main admin script.
  'web/admin/js/dist/script': [
    'web/admin/js/src/class/lgvAdmin.js',
    'web/admin/js/src/class/lgvAdminPage.js',
    // Page specific scripts.
    'web/admin/js/src/class/lgvAdminPageTeam.js',
    // Launcher
    'web/admin/js/src/main.js'
  ]
};

gulp.task('buildCoreJs', () => {
  // One task for each file separately.
  Object.keys(filesJs).map((destFile) => {
    var sourceFiles = filesJs[destFile];
    // Get source from dest if not defined.
    if (sourceFiles === true) {
      sourceFiles = destFile + '.js';
    }
    else if (typeof sourceFiles === 'string') {
      sourceFiles = sourceFiles;
    }

    var split = destFile.split('/');
    var destFileName = split.pop();
    var destFilePath = split.join('/') + '/';

    console.log('Building ' + destFilePath + destFileName + '.min.js ...');

    // Create task.
    gulp.src(sourceFiles, {base: "./"})
      // Create ap file.
      .pipe(sourcemaps.init())
      // Transpile.
      .pipe(babel({
        presets: ['latest']
      }))
      // Set dest name.
      .pipe(concat(destFileName + '.min.js'))
      // Compress.
      .pipe(uglify())
      // Write map file.
      .pipe(sourcemaps.write('.'))
      // Write.
      .pipe(gulp.dest(destFilePath));
  });
});

// Define files to watch.
gulp.task('watch', () => {
  var sourceFiles = [];
  Object.keys(filesJs).map((destFiles) => {
    "use strict";
    let source = filesJs[destFiles];
    if (source === true) {
      sourceFiles.push(destFiles + '.js');
    }
    else if (typeof source === 'string') {
      sourceFiles.push(source);
    }
    else {
      for (let i = 0; i < source.length; i++) {
        sourceFiles.push(source[i]);
      }
    }
  });
  gulp.watch(sourceFiles, ['buildCoreJs']);
});

gulp.task('default', ['watch']);
