const gulp = require('gulp');
const amphtmlValidator = require('amphtml-validator');
const fetch = require('node-fetch');

// URLs to validate (replace with your local development URLs)
const urls = [
    'http://localhost:10003/', // Homepage
    'http://localhost:10003/recipe/sample-recipe/', // Single recipe
    'http://localhost:10003/recipes/', // Recipe archive
    'http://localhost:10003/category/uncategorized/', // Category archive
    'http://localhost:10003/search?s=test', // Search results
    'http://localhost:10003/404', // 404 page
];

// Validate a single URL
async function validateUrl(url) {
    try {
        const validator = await amphtmlValidator.getInstance();
        const response = await fetch(url);
        const html = await response.text();
        const result = validator.validateString(html);
        
        console.log('\nValidating:', url);
        if (result.status === 'PASS') {
            console.log('\x1b[32m✓ PASS\x1b[0m');
        } else {
            console.error('\x1b[31m✗ FAIL\x1b[0m');
        }

        result.errors.forEach((error) => {
            const color = error.severity === 'ERROR' ? '\x1b[31m' : '\x1b[33m';
            console.log(color + 'line ' + error.line + ', col ' + error.col + ': ' + error.message);
            if (error.specUrl) {
                console.log('    ' + error.specUrl);
            }
            console.log('\x1b[0m');
        });

        return result.status === 'PASS';
    } catch (error) {
        console.error('\x1b[31mError validating ' + url + ':', error.message + '\x1b[0m');
        return false;
    }
}

// Task to validate all URLs
gulp.task('amp:validate', async () => {
    let allPassed = true;

    for (const url of urls) {
        const passed = await validateUrl(url);
        if (!passed) allPassed = false;
    }

    if (!allPassed) {
        process.exit(1);
    }
});

// Task to validate a specific URL
gulp.task('amp:validate-url', async () => {
    const url = process.argv[3];
    if (!url) {
        console.error('\x1b[31mPlease specify a URL\x1b[0m');
        process.exit(1);
    }

    const passed = await validateUrl(url);
    if (!passed) {
        process.exit(1);
    }
});

// Watch task for development (using BrowserSync)
gulp.task('amp:watch', () => {
    const browserSync = require('browser-sync').create();
    
    browserSync.init({
        proxy: "localhost:10003",
        files: [
            '**/*.php',
            '**/*.css',
            '**/*.js'
        ],
        open: false
    });

    // Validate whenever files change
    browserSync.watch('**/*.php').on('change', async (file) => {
        console.log('\nFile changed:', file);
        await validateUrl('http://localhost:10003');
    });
});

// Default task
gulp.task('default', gulp.series('amp:validate')); 