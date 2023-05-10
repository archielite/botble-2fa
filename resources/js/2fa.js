import '../sass/2fa.scss'

vueApp.booting(function (Vue) {
    Vue.component('two-factor-setup-modal', () => import('./components/TwoFactorSetupModal.vue'))
    Vue.component('two-factor-remove-modal', () => import('./components/TwoFactorRemoveModal.vue'))
    Vue.component('two-factor-challenge', () => import('./components/TwoFactorChallenge.vue'))
})
