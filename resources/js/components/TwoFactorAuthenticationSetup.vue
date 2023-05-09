<script>
export default {
    data() {
        return {
            modal: null,
            step: 1,
            data: null,
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
        },
        reset() {
            this.step = 1
            this.data = null
            this.secret = null
            this.loading = false
            this.troubleshooting = false
            this.recoveryCodes = []
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
                    code: this.secret,
                })

                const { error, message } = response.data

                if (error) {
                    Botble.showError(message)
                } else {
                    Botble.showSuccess(message)

                    await this.enableTwoFactor()

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
                    Botble.showSuccess(message)
                    this.recoveryCodes = data.recovery_codes
                }
            } catch (error) {
                Botble.showError(error.response.data.error)
            }

            this.loading = false
        },
        async enableTwoFactor() {
            try {
                const response = await axios({
                    method: 'post',
                    url: route('two-factor.system.users.enable'),
                })

                const { error, message } = response.data

                if (error) {
                    Botble.showError(message)
                    return
                }

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
        isConfirm() {
            return this.step === 3
        },
        isDone() {
            return this.step === 4
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
                        <strong v-if="isWelcome">{{ __('Protect your account in just two steps') }}</strong>
                        <strong v-if="isQrCode">{{ __('Link the app to your account') }}</strong>
                        <strong v-if="isConfirm">{{ __('Enter the confirmation code') }}</strong>
                        <strong v-if="isDone">{{ __('You’re all set') }}</strong>
                    </h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
                </div>
                <div class="modal-body">
                    <template v-if="!loading">
                        <div v-if="isWelcome">
                            <img src="/vendor/core/plugins/2fa/images/shield.png" class="w-100" alt="shield">
                            <ol class="mt-4 ms-0 ps-0 list-group-flush list-group-numbered">
                                <li class="list-group-item">
                                    <strong>Link an authentication app to your account</strong>
                                    <p>
                                        Use a compatible authentication app (like Google Authenticator, Authy, Duo
                                        Mobile, 1Password, etc.) We’ll generate a QR code for you to scan.
                                    </p>
                                </li>
                                <li class="list-group-item">
                                    <strong>Enter the confirmation code</strong>
                                    <p>
                                        Two-factor authentication will then be turned on for authentication app, which
                                        you can turn off at any time.
                                    </p>
                                </li>
                            </ol>
                        </div>
                        <div v-else-if="isQrCode">
                            <div v-if="!troubleshooting">
                                <p>
                                    {{
                                        __('Use your authentication app to scan this QR code. If you don’t have an authentication app on your device, you’ll need to install one now.')
                                    }}
                                </p>

                                <div class="my-4 text-center">
                                    <component class="d-block mb-2" v-html="data.svg" />
                                    <button type="button" @click="troubleshooting = true" class="btn btn-link">{{ __('Can’t scan the QR code?') }}</button>
                                </div>

                            </div>
                            <div v-else>
                                <p>
                                    {{
                                        __('If you can’t scan the QR code with your camera, enter the following code into the authentication app to link it to your account.')
                                    }}
                                </p>

                                <div class="text-center">
                                    <pre v-text="data.secret" />
                                    <button type="button" @click="troubleshooting = false" class="btn btn-link">{{ __('Try to scan the QR code again') }}</button>
                                </div>
                            </div>
                        </div>
                        <div v-else-if="isConfirm">
                            <p>
                                {{ __('Follow the instructions on the authentication app to link your account. Once the authentication app generates a confirmation code, enter it here. ') }}
                            </p>

                            <div class="form-group">
                                <input
                                    type="text"
                                    inputmode="numeric"
                                    pattern="[0-9]*"
                                    autocomplete="one-time-code"
                                    v-model="secret"
                                    class="form-control form-control-lg"
                                    placeholder="XXXXXX"
                                />
                            </div>
                        </div>
                        <div v-else-if="isDone">
                            <p class="text-center my-4">
                                <i class="fas fa-check fs-1 text-success"></i>
                            </p>
                            <p>
                                {{ __('Now you can use the mobile authentication app to get an authentication code any time you log in to your account.') }}
                            </p>

                            <div>
                                <p class="mb-0">
                                    {{ __('You can also use the following backup codes to log in if you lose access to your authentication app.') }}
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
                            @click="isConfirm ? confirm() : isDone ? hide() : step++"
                            v-text="isConfirm ? __('Confirm') : isDone ? __('Done') : __('Next')"
                        />
                        <button
                            v-if="recoveryCodes.length === 0 && isDone"
                            @click="getRecoveryCodes"
                            :disabled="loading"
                            type="button"
                            class="m-0 btn btn-secondary"
                            v-text="__('Get Backup Code')"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
pre {
    background: #e0e0e0;
    border-radius: 4px;
    padding: 10px;
    font-size: 20px;
}

.lds-ring {
    display: inline-block;
    position: relative;
    width: 80px;
    height: 80px;
}

.lds-ring div {
    box-sizing: border-box;
    display: block;
    position: absolute;
    width: 64px;
    height: 64px;
    margin: 8px;
    border: 4px solid #fff;
    border-radius: 50%;
    animation: lds-ring 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
    border-color: var(--bs-secondary) transparent transparent transparent;
}

.lds-ring div:nth-child(1) {
    animation-delay: -0.45s;
}

.lds-ring div:nth-child(2) {
    animation-delay: -0.3s;
}

.lds-ring div:nth-child(3) {
    animation-delay: -0.15s;
}

@keyframes lds-ring {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}
</style>
