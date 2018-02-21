let mix = require('laravel-mix');

mix.js('javascript/src/index.js', 'javascript/dist/UserFormSearchField.js');

mix.options({
  processCssUrls: false,
});
