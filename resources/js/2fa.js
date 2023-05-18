import '../sass/2fa.scss'
import TwoFactorSetupModal from './components/TwoFactorSetupModal.vue'
import TwoFactorRemoveModal from './components/TwoFactorRemoveModal.vue'
import TwoFactorChallenge from './components/TwoFactorChallenge.vue'

vueApp.booting(function (Vue) {
    Vue.component('two-factor-setup-modal', TwoFactorSetupModal)
    Vue.component('two-factor-remove-modal', TwoFactorRemoveModal)
    Vue.component('two-factor-challenge', TwoFactorChallenge)
})
