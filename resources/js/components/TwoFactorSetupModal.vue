<script>
export default {
    data() {
        return {
            modal: null,
            step: 1,
            data: null,
            code: null,
            secret: null,
            loading: false,
            troubleshooting: false,
            recoveryCodes: [],
        }
    },
    mounted() {
        this.modal = new bootstrap.Modal(this.$refs.twoFactorModal)

        window.vueApp.eventBus.$on('show-two-factor-setup-modal', () => {
            this.show()
        })
    },
    watch: {
        step(value) {
            if (value === 2) {
                this.getQrCode()
            }
        }
    },
    methods: {
        show() {
            this.reset()
            this.modal.show()
        },
        hide() {
            this.modal.hide()
            setTimeout(() => window.location.reload(), 1000)
        },
        reset() {
            this.step = 1
            this.data = null
            this.secret = null
            this.loading = false
            this.troubleshooting = false
            this.recoveryCodes = []
            this.code = null
        },
        async getQrCode() {
            this.loading = true

            try {
                const response = await axios.get(route('two-factor.system.users.qr-code'))

                const {error, message, data} = response.data

                if (error) {
                    Botble.showError(message)
                } else {
                    this.data = data
                    this.secret = data.secret
                }
            } catch (error) {
                Botble.showError(error.response.data.error)
            }

            this.loading = false
        },
        async confirm() {
            this.loading = true

            try {
                const response = await axios.post(route('two-factor.system.users.confirm'), {
                    code: this.code,
                    secret: this.secret
                })

                const { error, message } = response.data

                if (error) {
                    Botble.showError(message)
                } else {
                    await this.enableTwoFactor(this.secret)

                    this.step++
                }
            } catch (data) {
                const { error, message } = data.response.data
                Botble.showError(error || message)
            }

            this.loading = false
        },
        async getRecoveryCodes() {
            this.loading = true

            try {
                const response = await axios.get(route('two-factor.system.users.recovery-codes'))

                const { error, message, data } = response.data

                if (error) {
                    Botble.showError(message)
                } else {
                    this.recoveryCodes = data.recovery_codes
                }
            } catch (error) {
                Botble.showError(error.response.data.error)
            }

            this.loading = false
        },
        async enableTwoFactor(secret) {
            try {
                const response = await axios({
                    method: 'post',
                    url: route('two-factor.system.users.enable'),
                    data: {
                        secret,
                    },
                })

                const { error, message } = response.data

                if (error) {
                    Botble.showError(message)
                    return
                }

                await this.getRecoveryCodes()

                Botble.showSuccess(message)
            } catch (error) {
                Botble.showError(error.response.data.error)
            }
        }
    },
    computed: {
        isWelcome() {
            return this.step === 1
        },
        isQrCode() {
            return this.step === 2
        },
        isDone() {
            return this.step === 3
        },
    },
}
</script>

<template>
    <div class="modal fade" ref="twoFactorModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">
                        <i class="til_img"></i>
                        <strong v-if="isWelcome">{{ __('trans.setup.welcome_title') }}</strong>
                        <strong v-if="isQrCode">{{ __('trans.setup.qrcode_title') }}</strong>
                        <strong v-if="isDone">{{ __('trans.setup.done_title') }}</strong>
                    </h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
                </div>
                <div class="modal-body">
                    <template v-if="!loading">
                        <div v-if="isWelcome">
                            <img src="/vendor/core/plugins/2fa/images/shield.png" class="w-100" alt="shield">
                            <ol class="mt-4 ms-0 ps-0 list-group-flush list-group-numbered">
                                <li class="list-group-item">
                                    <strong>{{ __('trans.setup.welcome_tutorial_step_1') }}</strong>
                                    <p>{{ __('trans.setup.welcome_tutorial_step_1_description') }}</p>
                                </li>
                                <li class="list-group-item">
                                    <strong>{{ __('trans.setup.welcome_tutorial_step_2') }}</strong>
                                    <p>{{ __('trans.setup.welcome_tutorial_step_2_description') }}</p>
                                </li>
                            </ol>
                        </div>

                        <ol v-else-if="isQrCode" class="mt-4 ms-0 ps-0 list-group-flush list-group-numbered">
                            <li class="list-group-item">
                                <template v-if="!troubleshooting">
                                    {{__('trans.setup.scan_qrcode_tutorial') }}
                                    <div class="my-4 text-center">
                                        <component class="d-block mb-2" v-html="data.svg" />
                                        <button type="button" @click="troubleshooting = true" class="btn btn-link position-static">{{ __('trans.setup.cannot_scan_qrcode') }}</button>
                                    </div>
                                </template>
                                <template v-else>
                                    <p>{{ __('trans.setup.enter_code_manually_tutorial') }}</p>
                                    <div class="text-center">
                                        <pre v-text="data.secret" />
                                        <button type="button" @click="troubleshooting = false" class="btn btn-link position-static">{{ __('trans.setup.try_scan_qrcode_again') }}</button>
                                    </div>
                                </template>
                            </li>
                            <li class="list-group-item">
                                {{ __('trans.setup.enter_code_tutorial') }}

                                <div class="form-group mt-3">
                                    <input
                                        type="text"
                                        inputmode="numeric"
                                        pattern="[0-9]*"
                                        autocomplete="one-time-code"
                                        v-model="code"
                                        class="form-control form-control-lg"
                                        placeholder="XXXXXX"
                                    />
                                </div>
                            </li>
                        </ol>

                        <div v-else-if="isDone">
                            <p class="text-center my-4">
                                <i class="fas fa-check fs-1 text-success"></i>
                            </p>
                            <p>
                                {{ __('trans.setup.done_message') }}
                            </p>

                            <div>
                                <p class="mb-0">
                                    {{ __('trans.setup.backup_codes_tutorial') }}
                                </p>
                                <div class="mt-2">
                                    <div v-for="(code, index) in recoveryCodes" :key="index" class="d-inline-block me-2">
                                        <div class="bg-light px-2 py-1">
                                            <code v-text="code" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                    <template v-else>
                        <div class="text-center py-4">
                            <div class="lds-ring"><div></div><div></div><div></div><div></div></div>
                        </div>
                    </template>
                </div>
                <div class="modal-footer">
                    <div class="d-grid gap-2 w-100">
                        <button
                            class="btn btn-primary"
                            :disabled="loading"
                            type="button"
                            @click="isQrCode ? confirm() : isDone ? hide() : step++"
                            v-text="isQrCode ? __('trans.confirm') : isDone ? __('trans.done') : __('trans.next')"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
