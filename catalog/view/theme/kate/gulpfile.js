const   gulp          = require('gulp'),
        scss          = require('gulp-sass'),
        svgSprite     = require('gulp-svg-sprite'),
        cheerio       = require('gulp-cheerio'),
        svgo          = require('gulp-svgo'),
        replace       = require('gulp-replace'),
        minify        = require('gulp-minify');
        concat        = require('gulp-concat'),
        autoprefixer  = require('gulp-autoprefixer'),
        sourcemaps    = require('gulp-sourcemaps'),
        cleanCSS      = require('gulp-clean-css'),
        plumber       = require('gulp-plumber');

const paths = {
    scss:   './src/styles/',
    js:     './src/js/',
    images: './src/images/',
    svg:    './src/svg/',
    fonts:  './src/fonts/',
    dest: {
        root: './'
    }
};


const sources = {
    scssSrc: function () {
        return gulp.src([paths.scss + 'main.scss'])
    },
    jsSrc   : function() { return gulp.src([
        paths.js + 'script.js'
    ])},
    imgSrc      : function() { return gulp.src([
        paths.images + '**/*.png',
        paths.images + '**/*.jpg',
        paths.images + '**/*.gif',
        paths.images + '**/*.jpeg',
        paths.images + '**/*.svg',
        paths.images + '**/*.ico',

    ])},
    fontsSrc      : function() { return gulp.src([
        paths.fonts + '**/*.woff',
        paths.fonts + '**/*.woff2',
        paths.fonts + '**/*.ttf',
        paths.fonts + '**/*.eot'
    ])},
};

gulp.task('js', function() {
    sources.jsSrc()
        .pipe(concat('script.js'))
        .on('error', console.log)
        .pipe(minify())
        .pipe(gulp.dest(paths.dest.root + 'js'));
});

gulp.task('svg', function() {
    return gulp.src(paths.svg + '*.svg')
        .pipe(plumber())
        .pipe(svgo({
            js2svg: {
                indent: 2,
                pretty: true
            }
        }))
        .pipe(cheerio({
            run:           function($) {
                $('[fill]').removeAttr('fill');
                $('[stroke]').removeAttr('stroke');
                $('[style]').removeAttr('style');
            },
            parserOptions: {xmlMode: true}
        }))
        .pipe(replace('&gt;', '>'))
        .pipe(svgSprite({
            mode: {
                symbol: {
                    sprite:  '../sprite.svg',
                    render:  {
                        scss: {
                            dest:     '../../styles/blocks/_svg-sprite.scss',
                            template: paths.scss + 'helpers/_sprite_template.scss'
                        }
                    },
                    example: true
                }
            }
        }))
        .pipe(replace('<?xml version="1.0" encoding="utf-8"?>', ''))
        .pipe(cheerio({
            run:           function($) {
                $('[xmlns]').removeAttr('xmlns');
                $('svg').css('display', 'none');
            },
            parserOptions: {xmlMode: true}
        }))
        .pipe(plumber.stop())
        .pipe(gulp.dest(paths.images));
});

gulp.task('images', function() {
    sources.imgSrc()
        .on('error', console.log)
        .pipe(gulp.dest(paths.dest.root + 'images'));
});

gulp.task('fonts', function() {
    sources.fontsSrc()
        .on('error', console.log)
        .pipe(gulp.dest(paths.dest.root + 'fonts'));
});

gulp.task('scss', function () {
    sources.scssSrc()
        .pipe(sourcemaps.init())
        .pipe(scss())
        .pipe(autoprefixer({
            browsers: ['last 2 versions']
        }))
        .pipe(cleanCSS())
        .pipe(sourcemaps.write('.'))
        .pipe(gulp.dest(paths.dest.root + 'css'));
});

gulp.task('build', ['scss', 'js','images', 'fonts']);

gulp.task('default', ['scss', 'js']);
