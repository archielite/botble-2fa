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
    methods: {
        async submit() {
            this.loading = true

            try {
                const data = this.recovery ? { recovery_code: this.recovery_code } : { code: this.code }
                const response = await axios.post('/admin/two-factor/challenge', data)

                const { error, message, next_url } = response.data

                if (next_url) {
                    window.location.href = next_url
                } else if (error) {
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
            {{ __('Please confirm access to your account by entering the authentication code provided by your authenticator application.') }}
        </p>

        <p v-if="recovery">
            {{ __('Please confirm access to your account by entering one of your emergency recovery codes.') }}
        </p>

        <form @submit.prevent="submit" class="login-form">
            <div class="form-group" v-if="!recovery">
                <label for="code" class="form-label">{{ __('Code') }}</label>
                <input type="text" id="code" class="form-control" inputmode="numeric" v-model="code" autofocus autocomplete="one-time-code">
            </div>

            <div class="form-group" v-else>
                <label for="recovery_code" class="form-label">{{ __('Recovery Code') }}</label>
                <input type="text" id="recovery_code" class="form-control" v-model="recovery_code" autofocus autocomplete="one-time-code">
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-block login-button">
                    <span class="signin">{{ __('Log in') }}</span>
                </button>

                <button
                    type="button"
                    class="btn btn-block login-button bg-secondary"
                    @click="toggleRecovery"
                >
                    <span class="signin" v-text="recovery ? __('Use a recovery code') : __('Use an authentication code')" />
                </button>
            </div>
        </form>
    </div>
</template>
