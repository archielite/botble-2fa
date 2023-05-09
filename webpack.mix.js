const mix = require('laravel-mix')
const path = require('path')

const directory = path.basename(path.resolve(__dirname))
const source = 'platform/plugins/' + directory
const dist = 'public/vendor/core/plugins/' + directory

mix
    .vue()
    .js(source + '/resources/js/2fa.js', dist + '/js')

if (mix.inProduction()) {
    mix.copy(dist + '/js/analytics.js', source + '/public/js')
}
