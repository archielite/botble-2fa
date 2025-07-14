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
        handleAutofill(event) {
            const value = event.target.value.replace(/\D/g, '').slice(0, 6)
            if (value.length > 0) {
                const codeInputs = document.querySelectorAll('[data-code-input]')
                value.split('').forEach((digit, i) => {
                    if (codeInputs[i]) {
                        codeInputs[i].value = digit
                    }
                })
                this.code = value
                // Focus the last input
                if (codeInputs[5]) {
                    codeInputs[5].focus()
                }
            }
        },
    },

    mounted() {
        const codeInputs = document.querySelectorAll('[data-code-input]')
        const hiddenInput = document.querySelector('input[name="one-time-code"]')
        
        // Monitor the hidden input for autofill
        if (hiddenInput) {
            // Check for autofill on page load
            setTimeout(() => {
                if (hiddenInput.value) {
                    this.handleAutofill({ target: hiddenInput })
                }
            }, 100)
            
            // Monitor for changes
            const observer = new MutationObserver(() => {
                if (hiddenInput.value) {
                    this.handleAutofill({ target: hiddenInput })
                }
            })
            
            observer.observe(hiddenInput, { attributes: true, attributeFilter: ['value'] })
        }
        
        codeInputs.forEach((input, index) => {
            // Handle paste event
            input.addEventListener('paste', (event) => {
                event.preventDefault()
                const pastedText = (event.clipboardData || window.clipboardData).getData('text')
                const digits = pastedText.replace(/\D/g, '').slice(0, 6)
                
                if (digits.length > 0) {
                    // Fill all inputs with pasted digits
                    digits.split('').forEach((digit, i) => {
                        if (codeInputs[i]) {
                            codeInputs[i].value = digit
                        }
                    })
                    
                    // Focus the last filled input or the last input if all are filled
                    const lastFilledIndex = Math.min(digits.length - 1, 5)
                    codeInputs[lastFilledIndex].focus()
                    
                    // Update the code value
                    this.code = Array.from(codeInputs).reduce((acc, input) => acc + input.value, '')
                }
            })
            
            input.addEventListener('input', (event) => {
                // Remove non-numeric characters
                event.target.value = event.target.value.replace(/\D/g, '')
                
                if (event.target.value.length === event.target.maxLength && index < 5) {
                    codeInputs[index + 1].focus()
                }

                // Always update the code value
                this.code = Array.from(codeInputs).reduce((acc, input) => acc + input.value, '')
            })

            input.addEventListener('keydown', (event) => {
                if (event.target.value.length === 0 && event.key === 'Backspace' && index === 0) {
                    codeInputs[index].focus()
                }

                if (event.target.value.length === 0 && event.key === 'Backspace' && index > 0) {
                    codeInputs[index - 1].focus()
                }

                if (event.key === 'ArrowLeft' && index > 0) {
                    codeInputs[index - 1].focus()
                }

                if (event.key === 'ArrowRight' && index < 5) {
                    codeInputs[index + 1].focus()
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
                        <!-- Hidden input for 1Password autofill -->
                        <input
                            type="text"
                            name="one-time-code"
                            autocomplete="one-time-code"
                            style="position: absolute; left: -9999px; width: 1px; height: 1px;"
                            maxlength="6"
                            @input="handleAutofill"
                        />
                        
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
                                        autocomplete="off"
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
                                        autocomplete="off"
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
                                        autocomplete="off"
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
                                        autocomplete="off"
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
                                        autocomplete="off"
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
                                        autocomplete="off"
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
