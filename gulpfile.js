var gulp = require('gulp');
var babel = require('gulp-babel');
var sourcemaps = require('gulp-sourcemaps');
var uglify = require('gulp-uglify');
var concat = require('gulp-concat');
var git = require('gulp-git');
var sync = require('gulp-sync')(gulp);
var sass = require('gulp-sass');
var fs = require('fs');

// For each file (with no extension),
// if value is "true", use the .js version to build .min.js version,
// if value is an array, aggregate the files to the .min.js version.
var filesJs = {
  'web/front/src/main': true,
  // Main admin script.
  'web/admin/js/dist/script': [
    'web/admin/js/src/class/lgvAdmin.js',
    'web/admin/js/src/class/lgvAdminPage.js',
    // Page specific scripts.
    'web/admin/js/src/class/lgvAdminPageTeam.js',
    'web/admin/js/src/class/lgvAdminPageUser.js',
    'web/admin/js/src/class/lgvAdminPageProfile.js',
    'web/admin/js/src/class/lgvAdminPageOrga.js',
    'web/admin/js/src/class/lgvAdminPageComponent.js',
    // Fields.
    'src/VirtualAssembly/SemanticFormsBundle/Resources/js/field.uri.js',
    'src/VirtualAssembly/SemanticFormsBundle/Resources/js/field.dbPedia.js',
    'src/VirtualAssembly/SemanticFormsBundle/Resources/js/semanticForms.js',
    // Launcher
    'web/admin/js/src/main.js'
  ],
  // Front
  'web/front/src/gv-avatar/gv-avatar': true,
  'web/front/src/gv-carto/gv-carto': true,
  'web/front/src/gv-header/gv-header': true,
  'web/front/src/gv-results/gv-results': true,
  'web/front/src/gv-results-tab/gv-results-tab': true,
  'web/front/src/gv-results-item/gv-results-item': true,
  'web/front/src/gv-logo-animated/gv-logo-animated': true,
  'web/front/src/gv-detail/gv-detail': true,
  'web/front/src/gv-ressource/gv-ressource': true,
  'web/front/src/gv-detail-organization/gv-detail-organization': true,
  'web/front/src/gv-detail-person/gv-detail-person': true,
  'web/front/src/gv-detail-projet/gv-detail-projet': true,
  'web/front/src/gv-detail-event/gv-detail-event': true,
  'web/front/src/gv-detail-proposition/gv-detail-proposition': true,
  'web/front/src/gv-map/gv-map': true,
  'web/front/src/gv-map-pin/gv-map-pin': true
};

var filesScss = {
  // Semantic Forms.
  //'src/VirtualAssembly/SemanticFormsBundle/Resources/css/semanticForms': true,
  // Admin
  'web/admin/css/menu': true,
  'web/admin/css/style': true,
  // Front
  'web/front/css/style': true,
  'web/front/src/gv-avatar/gv-avatar': true,
  'web/front/src/gv-carto/gv-carto': true,
  'web/front/src/gv-spinner/gv-spinner': true,
  'web/front/src/gv-results/gv-results': true,
  'web/front/src/gv-results-tab/gv-results-tab': true,
  'web/front/src/gv-results-item/gv-results-item': true,
  'web/front/src/gv-header/gv-header': true,
  'web/front/src/gv-detail/gv-detail': true,
  'web/front/src/gv-ressource/gv-ressource': true,
  'web/front/src/gv-detail/gv-detail-inner': true,
  'web/front/src/gv-detail-organization/gv-detail-organization': true,
  'web/front/src/gv-detail-person/gv-detail-person': true,
  'web/front/src/gv-detail-projet/gv-detail-projet': true,
  'web/front/src/gv-detail-event/gv-detail-event': true,
  'web/front/src/gv-detail-proposition/gv-detail-proposition': true,
  'web/front/src/gv-logo-animated/gv-logo-animated': true,
  'web/front/src/gv-map/gv-map': true,
  'web/front/src/gv-map-pin/gv-map-pin': true
};

function getFilesOptions(destFile, sourceFiles, sourceExt, destExt) {
  "use strict";
  // Get source from dest if not defined.
  if (sourceFiles === true) {
    sourceFiles = [destFile + '.' + sourceExt];
  }
  else if (typeof sourceFiles === 'string') {
    sourceFiles = [sourceFiles];
  }

  sourceFiles.map((file) => {
    if (!fs.existsSync(file)) {
      console.error('Missing ' + file);
    }
  });

  var split = destFile.split('/');
  var destFileName = split.pop();
  var destFilePath = split.join('/') + '/';

  return {
    sourceFiles: sourceFiles,
    destFileName: destFileName,
    destFilePath: destFilePath
  };
}

function buildFiles(files, action, sourceExt, destExt) {
  // One task for each file separately.
  Object.keys(files).map((destFile) => {
    var fileData = getFilesOptions(destFile, files[destFile], sourceExt);
    console.log('Building ' + fileData.destFilePath + fileData.destFileName + '.' + destExt + ' ...');
    action(destFile, fileData, sourceExt, destExt);
  });
}

var tasksCounter = 0;
var allTasks = [];

buildFiles(filesJs, (destFile, fileData, sourceExt, destExt) => {
  "use strict";
  let key = 'buildFileJs' + tasksCounter++;
  allTasks.push(key);
  gulp.task(key, () => {
    // Create task.
    gulp.src(fileData.sourceFiles, {base: "./"})
      // Create ap file.
      .pipe(sourcemaps.init())
      // Transpile.
      .pipe(babel({
        presets: ['latest']
      }))
      // Set dest name.
      .pipe(concat(fileData.destFileName + '.' + destExt))
      // Compress.
      .pipe(uglify())
      // Write map file.
      .pipe(sourcemaps.write('.'))
      // Write.
      .pipe(gulp.dest(fileData.destFilePath));
  });
}, 'js', 'min.js');

buildFiles(filesScss, (destFile, fileData, sourceExt, destExt) => {
  "use strict";
  let key = 'buildFileCss' + tasksCounter++;
  allTasks.push(key);
  gulp.task(key, () => {
    gulp.src(fileData.sourceFiles, {base: "./"})
      // Set dest name.
      .pipe(concat(fileData.destFileName + '.' + destExt))
      .pipe(sass({
        includePaths: [fileData.destFilePath]
      }).on('error', sass.logError))
      .pipe(gulp.dest(fileData.destFilePath));
  });
}, 'scss', 'css');

function getFiles(registery, ext, sourceFiles) {
  "use strict";
  Object.keys(registery).map((destFiles) => {
    "use strict";
    let source = registery[destFiles];
    if (source === true) {
      sourceFiles.push(destFiles + '.' + ext);
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
  getFiles(filesJs, 'js', sourceFiles);
  getFiles(filesScss, 'scss', sourceFiles);

  // Check
  sourceFiles.map((file) => {
    if (!fs.existsSync(file)) {
      console.error('Missing watched file : ' + file);
    }
  });

  gulp.watch(sourceFiles, [allTasks]);
});

gulp.task('default', allTasks.concat('watch'));
