<script>
export default {
    data() {
        return {
            loading: false,
            modal: null,
            code: '',
        }
    },
    mounted() {
        this.modal = new bootstrap.Modal(this.$refs.twoFactorRemoveModal)

        vueApp.eventBus.$on('show-two-factor-remove-modal', () => {
            this.show()
        })
    },
    methods: {
        async remove() {
            this.loading = true

            try {
                const response = await axios.post(route('two-factor.system.users.disable', {
                    '_method': 'DELETE',
                    code: this.code,
                }))

                const { error, message } = response.data

                if (error) {
                    Botble.showError(message)
                } else {
                    Botble.showSuccess(message)
                    this.hide()

                    setTimeout(() => location.reload(), 1000)
                }
            } catch (data) {
                const { error, message } = data.response.data
                Botble.showError(error || message)
            }

            this.loading = false
        },
        show() {
            this.modal.show()
        },
        hide() {
            this.modal.hide()
        },
    },
}
</script>

<template>
    <div class="modal fade" ref="twoFactorRemoveModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h4 class="modal-title">
                        <i class="til_img"></i>
                        <strong>{{ __('trans.confirm_disable_title') }}</strong>
                    </h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
                </div>
                <div class="modal-body">
                    <template v-if="!loading">
                        <p>{{ __('trans.confirm_disable_description') }}</p>

                        <p> {{ __('trans.enter_code_to_disable') }}</p>

                        <div class="form-group">
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
                    </template>
                    <template v-else>
                        <div class="text-center py-4">
                            <div class="lds-ring"><div></div><div></div><div></div><div></div></div>
                        </div>
                    </template>
                </div>
                <div v-if="!loading" class="modal-footer">
                    <div class="d-grid gap-2 w-100">
                        <button
                            class="btn btn-danger"
                            type="button"
                            @click="remove"
                            v-text="__('trans.turn_off')"
                        />
                        <button
                            class="btn btn-secondary ms-0"
                            type="button"
                            @click="hide"
                            v-text="__('trans.cancel')"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
