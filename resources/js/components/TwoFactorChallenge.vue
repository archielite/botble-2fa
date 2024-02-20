<script>
export default {
    props: {
        url: {
            type: String,
            default: () => null,
            required: true,
        },
    },

    data() {
        return {
            recovery: null,
            recovery_code: '',
            loading: false,
            code: null,
        }
    },

    methods: {
        async submit(e) {
            this.loading = true

            const body = this.recovery ? { recovery_code: this.recovery_code } : { code: this.code }

            $httpClient
                .make()
                .withButtonLoading(e.currentTarget.querySelector('button[type="submit"]'))
                .post(this.url, body)
                .then(({ data }) => {
                    if (!data.error) {
                        window.location.href = data.data.next_url
                    }
                })
                .finally(() => this.loading = false)
        },
        toggleRecovery() {
            this.recovery = !this.recovery
            this.code = null
            this.recovery_code = null
        },
    },

    mounted() {
        document.querySelectorAll('[data-code-input]').forEach((input, index) => {
            input.addEventListener('input', (event) => {
                if (event.target.value.length === event.target.maxLength && index < 5) {
                    document.querySelectorAll('[data-code-input]')[index + 1].focus()
                }

                if (event.target.value.length === event.target.maxLength && index === 5) {
                    this.code = Array.from(document.querySelectorAll('[data-code-input]')).reduce(
                        (acc, input) => acc + input.value,
                        ''
                    )
                }
            })

            input.addEventListener('keydown', (event) => {
                if (event.target.value.length === 0 && event.key === 'Backspace' && index === 0) {
                    document.querySelectorAll('[data-code-input]')[index].focus()
                }

                if (event.target.value.length === 0 && event.key === 'Backspace' && index > 0) {
                    document.querySelectorAll('[data-code-input]')[index - 1].focus()
                }

                if (event.key === 'ArrowLeft' && index > 0) {
                    document.querySelectorAll('[data-code-input]')[index - 1].focus()
                }

                if (event.key === 'ArrowRight' && index < 5) {
                    document.querySelectorAll('[data-code-input]')[index + 1].focus()
                }
            })
        })

        document.querySelector('[data-code-input]').focus()
    },
}
</script>

<template>
    <div>
        <div class="card-body">
            <h2 class="card-title card-title-lg text-center mb-4">Authenticate Your Account</h2>
            <p
                class="my-4 text-center"
                v-text="recovery ? __('trans.challenge_recovery_code_tutorial') : __('trans.challenge_code_tutorial')"
            />

            <form @submit.prevent="submit">
                <div class="my-5">
                    <div class="row g-4" v-if="!recovery">
                        <div class="col">
                            <div class="row g-2">
                                <div class="col">
                                    <input
                                        type="text"
                                        class="form-control form-control-lg text-center py-3"
                                        maxlength="1"
                                        inputmode="numeric"
                                        pattern="[0-9]*"
                                        data-code-input
                                        :disabled="loading"
                                    />
                                </div>
                                <div class="col">
                                    <input
                                        type="text"
                                        class="form-control form-control-lg text-center py-3"
                                        maxlength="1"
                                        inputmode="numeric"
                                        pattern="[0-9]*"
                                        data-code-input
                                        :disabled="loading"
                                    />
                                </div>
                                <div class="col">
                                    <input
                                        type="text"
                                        class="form-control form-control-lg text-center py-3"
                                        maxlength="1"
                                        inputmode="numeric"
                                        pattern="[0-9]*"
                                        data-code-input
                                        :disabled="loading"
                                    />
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="row g-2">
                                <div class="col">
                                    <input
                                        type="text"
                                        class="form-control form-control-lg text-center py-3"
                                        maxlength="1"
                                        inputmode="numeric"
                                        pattern="[0-9]*"
                                        data-code-input
                                        :disabled="loading"
                                    />
                                </div>
                                <div class="col">
                                    <input
                                        type="text"
                                        class="form-control form-control-lg text-center py-3"
                                        maxlength="1"
                                        inputmode="numeric"
                                        pattern="[0-9]*"
                                        data-code-input
                                        :disabled="loading"
                                    />
                                </div>
                                <div class="col">
                                    <input
                                        type="text"
                                        class="form-control form-control-lg text-center py-3"
                                        maxlength="1"
                                        inputmode="numeric"
                                        pattern="[0-9]*"
                                        data-code-input
                                        :disabled="loading"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else>
                        <label for="recovery_code" class="form-label">{{ __('trans.recovery_code') }}</label>
                        <input
                            type="text"
                            id="recovery_code"
                            class="form-control form-control-lg"
                            v-model="recovery_code"
                            autofocus
                            autocomplete="one-time-code"
                            :disabled="loading"
                        />
                    </div>
                </div>
                <div class="form-footer">
                    <div class="btn-list flex-nowrap">
                        <button
                            type="button"
                            class="btn w-100"
                            v-text="recovery ? __('trans.use_code') : __('trans.use_recovery_code')"
                            @click="toggleRecovery"
                        />
                        <button type="submit" class="btn btn-primary w-100">{{ __('trans.login') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>
