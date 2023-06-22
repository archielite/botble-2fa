import '../sass/2fa.scss'
import TwoFactorSetupModal from './components/TwoFactorSetupModal.vue'
import TwoFactorRemoveModal from './components/TwoFactorRemoveModal.vue'
import TwoFactorChallenge from './components/TwoFactorChallenge.vue'


if (typeof vueApp !== 'undefined') {
    if (vueApp.eventBus === undefined) {
        vueApp.eventBus = {
            $on: (...args) => window.$event.on(...args),
            $emit: (...args) => window.$event.emit(...args),
        }
    }

    vueApp.booting(function (app) {
        app.component('two-factor-setup-modal', TwoFactorSetupModal)
        app.component('two-factor-remove-modal', TwoFactorRemoveModal)
        app.component('two-factor-challenge', TwoFactorChallenge)
    })
}
