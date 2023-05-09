vueApp.booting(function (Vue) {
    Vue.component('two-factor-authentication-setup', () => import('./components/TwoFactorAuthenticationSetup.vue'))
    Vue.component('two-factor-challenge', () => import('./components/TwoFactorChallenge.vue'))
})
