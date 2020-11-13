const gulp = require('gulp');
const sass = require('gulp-sass');
const autoprefixer = require('gulp-autoprefixer');
const pump = require('pump');
const wpPot = require('gulp-wp-pot');

gulp.task('styles', function() {
    pump([
		gulp.src(['scss/main.scss', 'scss/admin.scss']),
        sass({outputStyle: 'expanded'}),
		autoprefixer(),
		gulp.dest('css/')
	]);	
});

// Watch task
gulp.task('default', function() {
    gulp.watch('scss/**/*.scss', ['styles']);
});

gulp.task('translate', () => {
	pump([
		gulp.src('./**/*.php'),
		wpPot({
			domain: 'sphere-egcf',
			package: 'gdpr-consent-forms'
		}),
		gulp.dest('languages/gdpr-consent-forms.pot')
	]);
});

gulp.task('build', ['styles', 'translate']);
