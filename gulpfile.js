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
    'web/admin/js/src/class/cartoAdmin.js',
    'web/admin/js/src/class/cartoAdminPage.js',
    // Page specific scripts.
    'web/admin/js/src/class/cartoAdminPageTeam.js',
    'web/admin/js/src/class/cartoAdminPageUser.js',
    'web/admin/js/src/class/cartoAdminPageProfile.js',
    'web/admin/js/src/class/cartoAdminPageOrga.js',
    'web/admin/js/src/class/cartoAdminPageComponent.js',
    // Fields.
      'vendor/VirtualAssembly/SemanticFormsBundle/VirtualAssembly/SemanticFormsBundle/Resources/js/field.uri.js',
      'vendor/VirtualAssembly/SemanticFormsBundle/VirtualAssembly/SemanticFormsBundle/Resources/js/field.dbPedia.js',
      'vendor/VirtualAssembly/SemanticFormsBundle/VirtualAssembly/SemanticFormsBundle/Resources/js/field.Adresse.js',
      'vendor/VirtualAssembly/SemanticFormsBundle/VirtualAssembly/SemanticFormsBundle/Resources/js/semanticForms.js',
    // Launcher
    'web/admin/js/src/main.js'
  ],
  // Front
  'web/front/src/mm-avatar/mm-avatar': true,
  'web/front/src/mm-carto/mm-carto': true,
  'web/front/src/mm-header/mm-header': true,
  'web/front/src/mm-prez/mm-prez': true,
  'web/front/src/mm-results/mm-results': true,
  'web/front/src/mm-results-tab/mm-results-tab': true,
  'web/front/src/mm-results-item/mm-results-item': true,
  'web/front/src/mm-logo-animated/mm-logo-animated': true,
  'web/front/src/mm-detail/mm-detail': true,
  'web/front/src/mm-ressource/mm-ressource': true,
  'web/front/src/mm-codeSocial/mm-codeSocial': true,
  'web/front/src/mm-programme/mm-programme': true,
  'web/front/src/mm-infos/mm-infos': true,
  'web/front/src/mm-detail-organization/mm-detail-organization': true,
  'web/front/src/mm-detail-person/mm-detail-person': true,
  'web/front/src/mm-detail-projet/mm-detail-projet': true,
  'web/front/src/mm-detail-event/mm-detail-event': true,
  'web/front/src/mm-detail-proposition/mm-detail-proposition': true,
  'web/front/src/mm-detail-document/mm-detail-document': true,
  'web/front/src/mm-detail-documenttype/mm-detail-documenttype': true,
  'web/front/src/mm-map/mm-map': true,
  'web/front/src/mm-map-pin/mm-map-pin': true
};

var filesScss = {
  // Semantic Forms.
  //'src/VirtualAssembly/SemanticFormsBundle/Resources/css/semanticForms': true,
  // Admin
  'web/admin/css/menu': true,
  'web/admin/css/style': true,
  // Front
  'web/front/css/style': true,
  'web/front/src/mm-avatar/mm-avatar': true,
  'web/front/src/mm-carto/mm-carto': true,
  'web/front/src/mm-spinner/mm-spinner': true,
  'web/front/src/mm-results/mm-results': true,
  'web/front/src/mm-results-tab/mm-results-tab': true,
  'web/front/src/mm-results-item/mm-results-item': true,
  'web/front/src/mm-header/mm-header': true,
  'web/front/src/mm-prez/mm-prez': true,
  'web/front/src/mm-detail/mm-detail': true,
  'web/front/src/mm-ressource/mm-ressource': true,
  'web/front/src/mm-codeSocial/mm-codeSocial': true,
  'web/front/src/mm-programme/mm-programme': true,
  'web/front/src/mm-infos/mm-infos': true,
  'web/front/src/mm-detail/mm-detail-inner': true,
  'web/front/src/mm-detail-organization/mm-detail-organization': true,
  'web/front/src/mm-detail-person/mm-detail-person': true,
  'web/front/src/mm-detail-projet/mm-detail-projet': true,
  'web/front/src/mm-detail-event/mm-detail-event': true,
  'web/front/src/mm-detail-proposition/mm-detail-proposition': true,
  'web/front/src/mm-detail-document/mm-detail-document': true,
  'web/front/src/mm-detail-documenttype/mm-detail-documenttype': true,
  'web/front/src/mm-logo-animated/mm-logo-animated': true,
  'web/front/src/mm-map/mm-map': true,
  'web/front/src/mm-map-pin/mm-map-pin': true
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
