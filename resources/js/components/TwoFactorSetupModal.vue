<script>
export default {
    props: {
        qrCodeUrl: {
            type: String,
            default: null,
        },
        confirmUrl: {
            type: String,
            default: null,
        },
        recoveryCodesUrl: {
            type: String,
            default: null,
        },
        enableUrl: {
            type: String,
            default: null,
        },
    },

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

        $event.on('show-two-factor-setup-modal', () => {
            this.show()
        })
    },

    watch: {
        step(value) {
            if (value === 2) {
                this.getQrCode()
            }
        },
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
        getQrCode() {
            this.loading = true

            $httpClient
                .make()
                .get(this.qrCodeUrl)
                .then(({ error, message, data }) => {
                    if (error) {
                        Botble.showError(message)
                    } else {
                        this.data = data.data
                        this.secret = data.data.secret
                    }
                })
                .finally(() => (this.loading = false))
        },
        confirm() {
            this.loading = true

            $httpClient
                .make()
                .post(this.confirmUrl, {
                    code: this.code,
                    secret: this.secret,
                })
                .then(({ error, message }) => {
                    if (error) {
                        Botble.showError(message)
                    } else {
                        this.enableTwoFactor(this.secret)

                        this.step++
                    }
                })
                .finally(() => (this.loading = false))
        },
        getRecoveryCodes() {
            this.loading = true

            $httpClient
                .make()
                .get(this.recoveryCodesUrl)
                .then(({ error, message, data }) => {
                    if (error) {
                        Botble.showError(message)
                    } else {
                        this.recoveryCodes = data.data.recovery_codes
                    }
                })
                .finally(() => (this.loading = false))
        },
        enableTwoFactor(secret) {
            $httpClient
                .make()
                .post(this.enableUrl, {
                    secret,
                })
                .then(({ error, message }) => {
                    if (error) {
                        Botble.showError(message)
                    } else {
                        this.getRecoveryCodes()
                    }
                })
                .finally(() => (this.loading = false))
        },
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
    <div class="modal modal-blur fade" ref="twoFactorModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">
                        <strong v-if="isWelcome">{{ __('trans.setup.welcome_title') }}</strong>
                        <strong v-if="isQrCode">{{ __('trans.setup.qrcode_title') }}</strong>
                        <strong v-if="isDone">{{ __('trans.setup.done_title') }}</strong>
                    </h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
                </div>
                <div class="modal-body">
                    <div v-if="isWelcome">
                        <img src="/vendor/core/plugins/2fa/images/shield.png" class="w-100" alt="shield" />
                        <ol class="mt-4 ms-0">
                            <li>
                                <strong>{{ __('trans.setup.welcome_tutorial_step_1') }}</strong>
                                <p>{{ __('trans.setup.welcome_tutorial_step_1_description') }}</p>
                            </li>
                            <li>
                                <strong>{{ __('trans.setup.welcome_tutorial_step_2') }}</strong>
                                <p>{{ __('trans.setup.welcome_tutorial_step_2_description') }}</p>
                            </li>
                        </ol>
                    </div>

                    <ol v-else-if="isQrCode && data" class="mt-4 ms-0">
                        <li>
                            <template v-if="!troubleshooting">
                                {{ __('trans.setup.scan_qrcode_tutorial') }}
                                <div class="my-4 text-center">
                                    <div class="d-block mb-2" v-html="data.svg"></div>
                                    <button
                                        type="button"
                                        @click="troubleshooting = true"
                                        class="btn btn-link position-static"
                                    >
                                        {{ __('trans.setup.cannot_scan_qrcode') }}
                                    </button>
                                </div>
                            </template>
                            <template v-else>
                                <p>{{ __('trans.setup.enter_code_manually_tutorial') }}</p>
                                <div class="text-center">
                                    <pre v-text="data.secret" />
                                    <button
                                        type="button"
                                        @click="troubleshooting = false"
                                        class="btn btn-link position-static"
                                    >
                                        {{ __('trans.setup.try_scan_qrcode_again') }}
                                    </button>
                                </div>
                            </template>
                        </li>
                        <li>
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
                        <div class="text-center my-4">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="icon text-success icon-lg"
                                width="24"
                                height="24"
                                viewBox="0 0 24 24"
                                stroke-width="2"
                                stroke="currentColor"
                                fill="none"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M5 12l5 5l10 -10" />
                            </svg>
                        </div>
                        <p>
                            {{ __('trans.setup.done_message') }}
                        </p>

                        <div>
                            <p>
                                {{ __('trans.setup.backup_codes_tutorial') }}
                            </p>
                            <div class="d-flex flex-wrap gap-2">
                                <div v-for="(code, index) in recoveryCodes" :key="index">
                                    <code v-text="code" class="px-2 py-1" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="loading-spinner" v-if="loading"></div>
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
