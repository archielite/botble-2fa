<script>
export default {
    props: {
        disableUrl: {
            type: String,
            required: true,
        },
    },

    data() {
        return {
            loading: false,
            modal: null,
            code: '',
        }
    },

    mounted() {
        this.modal = new bootstrap.Modal(this.$refs.twoFactorRemoveModal)

        $event.on('show-two-factor-remove-modal', () => {
            this.show()
        })
    },

    methods: {
        remove() {
            this.loading = true

            $httpClient.make().delete(this.disableUrl, {
                code: this.code,
            })
            .then(({error, message}) => {
                if (error) {
                    Botble.showError(message)
                } else {
                    this.hide()
                    setTimeout(() => location.reload(), 1000)
                }
            })
            .finally(() => this.loading = false)
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
    <div class="modal modal-blur fade" ref="twoFactorRemoveModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">{{ __('trans.confirm_disable_title') }}</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
                </div>
                <div class="modal-status bg-warning"></div>
                <div class="modal-body">
                    <p>{{ __('trans.confirm_disable_description') }}</p>

                    <p>{{ __('trans.enter_code_to_disable') }}</p>

                    <div class="form-group">
                        <input
                            type="text"
                            inputmode="numeric"
                            pattern="[0-9]*"
                            autocomplete="one-time-code"
                            v-model="code"
                            class="form-control form-control-lg"
                            placeholder="XXXXXX"
                            :disabled="loading"
                        />
                    </div>
                    <div class="loading-spinner" v-if="loading"></div>
                </div>
                <div class="modal-footer">
                    <div class="w-100 row">
                        <div class="col">
                            <button
                                class="btn btn-warning w-100"
                                type="button"
                                @click="remove"
                                v-text="__('trans.turn_off')"
                                :disabled="loading"
                            />
                        </div>
                        <div class="col">
                            <button
                                class="btn w-100"
                                type="button"
                                @click="hide"
                                :disabled="loading"
                                v-text="__('trans.cancel')"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
