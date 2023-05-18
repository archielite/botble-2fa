<script>
export default {
    data() {
        return {
            recovery: null,
            recovery_code: '',
            code: '',
            loading: false,
        }
    },
    props: {
        url: {
            type: String,
            default: () => null,
            required: true,
        },
    },
    methods: {
        async submit() {
            this.loading = true

            try {
                const body = this.recovery ? { recovery_code: this.recovery_code } : { code: this.code }
                const response = await axios.post(this.url, body)

                const { error, message, data } = response.data

                if (!error) {
                    window.location.href = data.next_url
                } else {
                    Botble.showError(message)
                }
            } catch (data) {
                const { error, message } = data.response.data
                Botble.showError(error || message)
            }

            this.loading = false
        },
        toggleRecovery() {
            this.recovery = !this.recovery
            this.code = null
            this.recovery_code = null
        },
    },
}
</script>

<template>
    <div>
        <p v-if="!recovery">
            {{ __('trans.challenge_code_tutorial') }}
        </p>

        <p v-if="recovery">
            {{ __('trans.challenge_recovery_code_tutorial') }}
        </p>

        <form @submit.prevent="submit" class="login-form">
            <div class="form-group" v-if="!recovery">
                <label for="code" class="form-label">{{ __('trans.code') }}</label>
                <input type="text" id="code" class="form-control" inputmode="numeric" v-model="code" autofocus autocomplete="one-time-code">
            </div>

            <div class="form-group" v-else>
                <label for="recovery_code" class="form-label">{{ __('trans.recovery_code') }}</label>
                <input type="text" id="recovery_code" class="form-control" v-model="recovery_code" autofocus autocomplete="one-time-code">
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-block login-button">
                    <span class="signin">{{ __('trans.login') }}</span>
                </button>

                <button
                    type="button"
                    class="btn btn-block login-button bg-secondary"
                    @click="toggleRecovery"
                >
                    <span class="signin" v-text="recovery ? __('trans.use_recovery_code') : __('trans.use_code')" />
                </button>
            </div>
        </form>
    </div>
</template>
