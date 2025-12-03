<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-7">
            <div class="card shadow-sm border-0 mt-5 mb-5">
                <div class="card-header bg-white border-0 pt-4">
                    <div class="text-center mb-3">
                        <i class="ti ti-shield-lock" style="font-size: 48px; color: var(--bs-primary);"></i>
                    </div>
                    <h4 class="text-center mb-2">{{ trans('plugins/2fa::2fa.challenge_title') }}</h4>
                    <p class="text-center text-muted small">{{ trans('plugins/2fa::2fa.challenge_description') }}</p>
                </div>
                <div class="card-body px-4 pb-4">

                        <form id="twofa-challenge-form"
                              action="{{ route('customer.two-factor.challenge') }}"
                              method="POST"
                              data-invalid-message="{{ trans('plugins/2fa::2fa.invalid_code') }}">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label">{{ trans('plugins/2fa::2fa.code') }}</label>
                                <input type="hidden" id="code" name="code">
                                <!-- Hidden input for autofill -->
                                <input type="text" name="one-time-code-challenge" autocomplete="one-time-code"
                                       style="position: absolute; left: -9999px; width: 1px; height: 1px;"
                                       maxlength="6" id="autofill-challenge-code">

                                <div class="row g-2 mb-2">
                                    <div class="col">
                                        <input type="text" class="form-control form-control-lg text-center py-3"
                                               maxlength="1" inputmode="numeric" pattern="[0-9]*"
                                               data-code-input="challenge" data-index="0" autocomplete="off">
                                    </div>
                                    <div class="col">
                                        <input type="text" class="form-control form-control-lg text-center py-3"
                                               maxlength="1" inputmode="numeric" pattern="[0-9]*"
                                               data-code-input="challenge" data-index="1" autocomplete="off">
                                    </div>
                                    <div class="col">
                                        <input type="text" class="form-control form-control-lg text-center py-3"
                                               maxlength="1" inputmode="numeric" pattern="[0-9]*"
                                               data-code-input="challenge" data-index="2" autocomplete="off">
                                    </div>
                                    <div class="col">
                                        <input type="text" class="form-control form-control-lg text-center py-3"
                                               maxlength="1" inputmode="numeric" pattern="[0-9]*"
                                               data-code-input="challenge" data-index="3" autocomplete="off">
                                    </div>
                                    <div class="col">
                                        <input type="text" class="form-control form-control-lg text-center py-3"
                                               maxlength="1" inputmode="numeric" pattern="[0-9]*"
                                               data-code-input="challenge" data-index="4" autocomplete="off">
                                    </div>
                                    <div class="col">
                                        <input type="text" class="form-control form-control-lg text-center py-3"
                                               maxlength="1" inputmode="numeric" pattern="[0-9]*"
                                               data-code-input="challenge" data-index="5" autocomplete="off">
                                    </div>
                                </div>
                                <small class="form-text text-muted">{{ trans('plugins/2fa::2fa.enter_code_from_app') }}</small>
                            </div>

                            <div class="alert alert-danger d-none" id="error-alert"></div>

                            <button type="submit" class="btn btn-primary w-100" id="verify-btn">
                                <span class="btn-text">{{ trans('plugins/2fa::2fa.verify') }}</span>
                                <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                            </button>
                        </form>

                        <hr class="my-4">

                        <div class="text-center">
                            <button type="button" class="btn btn-link" id="use-recovery-btn">
                                {{ trans('plugins/2fa::2fa.use_recovery_code') }}
                            </button>
                        </div>

                        <div id="recovery-section" class="d-none">
                            <form id="recovery-form"
                                  action="{{ route('customer.two-factor.challenge') }}"
                                  method="POST"
                                  data-invalid-message="{{ trans('plugins/2fa::2fa.invalid_recovery_code') }}">
                                @csrf

                                <div class="mb-3">
                                    <label for="recovery_code" class="form-label">{{ trans('plugins/2fa::2fa.recovery_code') }}</label>
                                    <input type="text"
                                        class="form-control"
                                        id="recovery_code"
                                        name="recovery_code"
                                        placeholder="XXXX-XXXX-XXXX">
                                    <small class="form-text text-muted">{{ trans('plugins/2fa::2fa.enter_recovery_code') }}</small>
                                </div>

                                <div class="alert alert-danger d-none" id="recovery-error-alert"></div>

                                <button type="submit" class="btn btn-primary w-100" id="verify-recovery-btn">
                                    <span class="btn-text">{{ trans('plugins/2fa::2fa.verify') }}</span>
                                    <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                                </button>

                                <button type="button" class="btn btn-link w-100 mt-2" id="back-to-code-btn">
                                    {{ trans('plugins/2fa::2fa.back_to_code') }}
                                </button>
                            </form>
                </div>
            </div>
        </div>
    </div>
</div>
