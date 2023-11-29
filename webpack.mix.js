const mix = require('laravel-mix')
const path = require('path')

const directory = path.basename(path.resolve(__dirname))
const source = `platform/plugins/${directory}`
const dist = 'public/vendor/core/plugins/2fa'

mix.js(`${source}/resources/js/2fa.js`, `${dist}/js/2fa-vue3.js`).vue()

if (mix.inProduction()) {
    mix.copy(`${dist}/js/2fa-vue3.js`, `${source}/public/js`)
}
