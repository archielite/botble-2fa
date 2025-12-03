@if (setting('2fa_enabled', false))
    <div class="bb-customer-card">
        <div class="bb-customer-card-header">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-primary bg-opacity-10 rounded-circle p-2">
                    <x-core::icon name="ti ti-shield-lock" class="text-primary" />
                </div>
                <div>
                    <h3 class="bb-customer-card-title h5 mb-1">{{ trans('plugins/2fa::2fa.name') }}</h3>
                    <p class="text-muted small mb-0">{{ trans('plugins/2fa::2fa.description') }}</p>
                </div>
            </div>
        </div>
        <div class="bb-customer-card-body">
            @php
                $customer = auth('customer')->user();
                $hasTwoFactor = \ArchiElite\TwoFactorAuthentication\TwoFactor::userHasEnabled($customer);
            @endphp

            @if(!$hasTwoFactor)
                <div id="twofa-setup-section">
                    <p class="mb-3">{{ trans('plugins/2fa::2fa.not_enabled') }}</p>
                    <button type="button" class="btn btn-primary" id="enable-2fa-btn">
                        <i class="ti ti-shield-lock"></i>
                        {{ trans('plugins/2fa::2fa.ask_enable_button') }}
                    </button>
                </div>

                <!-- Setup Modal -->
                <div class="modal fade" id="twofa-setup-modal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">{{ trans('plugins/2fa::2fa.setup_title') }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body" id="setup-modal-body">
                                <!-- Step 1: Loading QR Code -->
                                <div id="setup-step-1">
                                    <div class="text-center">
                                        <div class="spinner-border" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <p class="mt-2">{{ trans('plugins/2fa::2fa.generating_qr_code') }}</p>
                                    </div>
                                </div>

                                <!-- Step 2: Scan QR Code -->
                                <div id="setup-step-2" class="d-none">
                                    <div class="alert alert-info mb-3">
                                        <h6 class="mb-2"><i class="ti ti-info-circle"></i> {{ trans('plugins/2fa::2fa.setup.qrcode_title') }}</h6>
                                        <p class="mb-2 small">{{ trans('plugins/2fa::2fa.setup.scan_qrcode_tutorial') }}</p>
                                        <p class="mb-0 small"><strong>{{ trans('plugins/2fa::2fa.setup.popular_apps') }}</strong></p>
                                        <ul class="small mb-0">
                                            <li>Google Authenticator (iOS/Android)</li>
                                            <li>Microsoft Authenticator (iOS/Android)</li>
                                            <li>Authy (iOS/Android)</li>
                                            <li>1Password (iOS/Android/Desktop)</li>
                                            <li>Duo Mobile (iOS/Android)</li>
                                        </ul>
                                    </div>

                                    <div class="text-center mb-3">
                                        <div id="qr-code-container"></div>
                                    </div>
                                    <p class="text-muted small text-center">{{ trans('plugins/2fa::2fa.scan_qr_code') }}</p>

                                    <form id="confirm-2fa-form">
                                        @csrf
                                        <input type="hidden" id="secret-key" name="secret">

                                        <div class="mb-3">
                                            <label class="form-label">{{ trans('plugins/2fa::2fa.verification_code') }}</label>
                                            <input type="hidden" id="confirmation-code" name="code">
                                            <!-- Hidden input for autofill -->
                                            <input type="text" name="one-time-code-confirm" autocomplete="one-time-code"
                                                   style="position: absolute; left: -9999px; width: 1px; height: 1px;"
                                                   maxlength="6" id="autofill-confirm-code">

                                            <div class="row g-2">
                                                <div class="col">
                                                    <input type="text" class="form-control form-control-lg text-center py-3"
                                                           maxlength="1" inputmode="numeric" pattern="[0-9]*"
                                                           data-code-input="confirm" data-index="0" autocomplete="off">
                                                </div>
                                                <div class="col">
                                                    <input type="text" class="form-control form-control-lg text-center py-3"
                                                           maxlength="1" inputmode="numeric" pattern="[0-9]*"
                                                           data-code-input="confirm" data-index="1" autocomplete="off">
                                                </div>
                                                <div class="col">
                                                    <input type="text" class="form-control form-control-lg text-center py-3"
                                                           maxlength="1" inputmode="numeric" pattern="[0-9]*"
                                                           data-code-input="confirm" data-index="2" autocomplete="off">
                                                </div>
                                                <div class="col">
                                                    <input type="text" class="form-control form-control-lg text-center py-3"
                                                           maxlength="1" inputmode="numeric" pattern="[0-9]*"
                                                           data-code-input="confirm" data-index="3" autocomplete="off">
                                                </div>
                                                <div class="col">
                                                    <input type="text" class="form-control form-control-lg text-center py-3"
                                                           maxlength="1" inputmode="numeric" pattern="[0-9]*"
                                                           data-code-input="confirm" data-index="4" autocomplete="off">
                                                </div>
                                                <div class="col">
                                                    <input type="text" class="form-control form-control-lg text-center py-3"
                                                           maxlength="1" inputmode="numeric" pattern="[0-9]*"
                                                           data-code-input="confirm" data-index="5" autocomplete="off">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="alert alert-danger d-none" id="confirm-error-alert"></div>

                                        <button type="submit" class="btn btn-primary w-100" id="confirm-2fa-btn">
                                            <span class="btn-text">{{ trans('plugins/2fa::2fa.confirm_and_enable') }}</span>
                                            <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                                        </button>
                                    </form>
                                </div>

                                <!-- Step 3: Recovery Codes -->
                                <div id="setup-step-3" class="d-none">
                                    <div class="alert alert-success">
                                        {{ trans('plugins/2fa::2fa.enabled_successfully') }}
                                    </div>

                                    <p class="mb-2">{{ trans('plugins/2fa::2fa.recovery_codes_description') }}</p>
                                    <div class="alert alert-warning">
                                        <div id="recovery-codes-list" class="font-monospace small"></div>
                                    </div>

                                    <button type="button" class="btn btn-primary w-100" data-bs-dismiss="modal">
                                        {{ trans('plugins/2fa::2fa.done') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div id="twofa-enabled-section">
                    <div class="alert alert-success mb-3">
                        <i class="ti ti-shield-check"></i>
                        {{ trans('plugins/2fa::2fa.enabled') }}
                    </div>

                    <button type="button" class="btn btn-danger" id="disable-2fa-btn">
                        <i class="ti ti-shield-off"></i>
                        {{ trans('plugins/2fa::2fa.ask_disable_button') }}
                    </button>
                </div>

                <!-- Disable Modal -->
                <div class="modal fade" id="twofa-disable-modal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">{{ trans('plugins/2fa::2fa.disable_title') }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form id="disable-2fa-form">
                                    @csrf
                                    @method('DELETE')

                                    <p class="mb-3">{{ trans('plugins/2fa::2fa.disable_description') }}</p>

                                    <div class="mb-3">
                                        <label class="form-label">{{ trans('plugins/2fa::2fa.verification_code') }}</label>
                                        <input type="hidden" id="disable-code" name="code">
                                        <!-- Hidden input for autofill -->
                                        <input type="text" name="one-time-code-disable" autocomplete="one-time-code"
                                               style="position: absolute; left: -9999px; width: 1px; height: 1px;"
                                               maxlength="6" id="autofill-disable-code">

                                        <div class="row g-2">
                                            <div class="col">
                                                <input type="text" class="form-control form-control-lg text-center py-3"
                                                       maxlength="1" inputmode="numeric" pattern="[0-9]*"
                                                       data-code-input="disable" data-index="0" autocomplete="off">
                                            </div>
                                            <div class="col">
                                                <input type="text" class="form-control form-control-lg text-center py-3"
                                                       maxlength="1" inputmode="numeric" pattern="[0-9]*"
                                                       data-code-input="disable" data-index="1" autocomplete="off">
                                            </div>
                                            <div class="col">
                                                <input type="text" class="form-control form-control-lg text-center py-3"
                                                       maxlength="1" inputmode="numeric" pattern="[0-9]*"
                                                       data-code-input="disable" data-index="2" autocomplete="off">
                                            </div>
                                            <div class="col">
                                                <input type="text" class="form-control form-control-lg text-center py-3"
                                                       maxlength="1" inputmode="numeric" pattern="[0-9]*"
                                                       data-code-input="disable" data-index="3" autocomplete="off">
                                            </div>
                                            <div class="col">
                                                <input type="text" class="form-control form-control-lg text-center py-3"
                                                       maxlength="1" inputmode="numeric" pattern="[0-9]*"
                                                       data-code-input="disable" data-index="4" autocomplete="off">
                                            </div>
                                            <div class="col">
                                                <input type="text" class="form-control form-control-lg text-center py-3"
                                                       maxlength="1" inputmode="numeric" pattern="[0-9]*"
                                                       data-code-input="disable" data-index="5" autocomplete="off">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="alert alert-danger d-none" id="disable-error-alert"></div>

                                    <button type="submit" class="btn btn-danger w-100" id="confirm-disable-btn">
                                        <span class="btn-text">{{ trans('plugins/2fa::2fa.disable') }}</span>
                                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endif
