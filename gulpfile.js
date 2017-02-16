var gulp = require('gulp');
var babel = require('gulp-babel');
var sourcemaps = require('gulp-sourcemaps');
var uglify = require('gulp-uglify');
var concat = require('gulp-concat');
var git = require('gulp-git');
var sync = require('gulp-sync')(gulp);
var sass = require('gulp-sass');

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

var filesScss = {
  'web/front/src/gv-carto/gv-carto.scss': true,
  'web/front/src/gv-header/gv-header.scss': true,
  'web/admin/scss/menu.scss': true,
  'web/admin/scss/style.scss': true
};

function getFilesOptions(destFile, sourceFiles) {
  "use strict";
  // Get source from dest if not defined.
  if (sourceFiles === true) {
    sourceFiles = destFile + '.js';
  }

  var split = destFile.split('/');
  var destFileName = split.pop();
  var destFilePath = split.join('/') + '/';

  return {
    sourceFiles: sourceFiles,
    destFileName: destFileName,
    destFilePath: destFilePath
  };
}

function buildFiles(files, action) {
  // One task for each file separately.
  Object.keys(files).map((destFile) => {
    var fileData = getFilesOptions(destFile, files[destFile]);
    console.log('Building ' + fileData.destFilePath + fileData.destFileName + '.min.js ...');
    action(destFile, fileData);
  });
}

gulp.task('buildCoreJs', () => {

  buildFiles(filesJs, (destFile, fileData) => {
    // Create task.
    gulp.src(fileData.sourceFiles, {base: "./"})
      // Create ap file.
      .pipe(sourcemaps.init())
      // Transpile.
      .pipe(babel({
        presets: ['latest']
      }))
      // Set dest name.
      .pipe(concat(fileData.destFileName + '.min.js'))
      // Compress.
      .pipe(uglify())
      // Write map file.
      .pipe(sourcemaps.write('.'))
      // Write.
      .pipe(gulp.dest(fileData.destFilePath));
  });

  buildFiles(filesScss, (destFile, fileData) => {
    gulp.src(fileData.sourceFiles, {base: "./"})
      .pipe(sass().on('error', sass.logError))
      .pipe(gulp.dest(fileData.destFilePath));
  });
});

function getFiles(registery, sourceFiles) {
  "use strict";
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
}

// Define files to watch.
gulp.task('watch', () => {
  var sourceFiles = [];
  getFiles(filesJs, sourceFiles);
  getFiles(filesScss, sourceFiles);
  gulp.watch(sourceFiles, ['buildCoreJs']);
});

gulp.task('default', ['watch']);
