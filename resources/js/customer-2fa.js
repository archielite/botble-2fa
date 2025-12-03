(function ($) {
    'use strict';

    $(document).ready(function () {
        // Check if 2FA section exists
        if (!$('#enable-2fa-btn').length && !$('#disable-2fa-btn').length) {
            return;
        }

        let generatedSecret = null;
        let setupModal = null;
        let disableModal = null;

        // Initialize modals
        const setupModalEl = document.getElementById('twofa-setup-modal');
        const disableModalEl = document.getElementById('twofa-disable-modal');

        if (setupModalEl) {
            setupModal = new bootstrap.Modal(setupModalEl);
        }

        if (disableModalEl) {
            disableModal = new bootstrap.Modal(disableModalEl);
        }

        // Setup 6-box code input for confirm modal
        function setupCodeInput(type, hiddenInputId) {
            const inputs = $('[data-code-input="' + type + '"]');
            const hiddenInput = $(hiddenInputId || '#' + type + '-code');
            const autofillInput = $('#autofill-' + type + '-code');

            // Handle autofill for password managers (1Password, LastPass, etc.)
            if (autofillInput.length) {
                // Initial check
                setTimeout(function () {
                    if (autofillInput.val()) {
                        handleAutofill(autofillInput.val(), inputs, hiddenInput);
                    }
                }, 100);

                // Listen for input events (direct typing or autofill)
                autofillInput.on('input change', function () {
                    if ($(this).val()) {
                        handleAutofill($(this).val(), inputs, hiddenInput);
                    }
                });

                // Watch for autofill changes via MutationObserver
                if (autofillInput[0]) {
                    new MutationObserver(function () {
                        if (autofillInput.val()) {
                            handleAutofill(autofillInput.val(), inputs, hiddenInput);
                        }
                    }).observe(autofillInput[0], {
                        attributes: true,
                        attributeFilter: ['value'],
                        childList: true,
                        characterData: true,
                        subtree: true
                    });
                }

                // Poll for changes (for password managers that don't trigger events)
                let pollCount = 0;
                const pollInterval = setInterval(function () {
                    pollCount++;
                    if (autofillInput.val()) {
                        handleAutofill(autofillInput.val(), inputs, hiddenInput);
                        clearInterval(pollInterval);
                    }
                    // Stop polling after 5 seconds
                    if (pollCount > 50) {
                        clearInterval(pollInterval);
                    }
                }, 100);
            }

            inputs.each(function (index) {
                const $input = $(this);

                // Handle paste
                $input.on('paste', function (e) {
                    e.preventDefault();
                    const pastedData = (e.originalEvent.clipboardData || window.clipboardData)
                        .getData('text')
                        .replace(/\D/g, '')
                        .slice(0, 6);

                    if (pastedData.length > 0) {
                        pastedData.split('').forEach(function (char, i) {
                            if (inputs[i]) {
                                inputs[i].value = char;
                            }
                        });

                        const focusIndex = Math.min(pastedData.length - 1, 5);
                        inputs[focusIndex].focus();
                        updateHiddenInput(inputs, hiddenInput);
                    }
                });

                // Handle input
                $input.on('input', function (e) {
                    e.target.value = e.target.value.replace(/\D/g, '');

                    if (e.target.value.length === e.target.maxLength && index < 5) {
                        inputs[index + 1].focus();
                    }

                    updateHiddenInput(inputs, hiddenInput);
                });

                // Handle keyboard navigation
                $input.on('keydown', function (e) {
                    if (e.target.value.length === 0 && e.key === 'Backspace' && index > 0) {
                        inputs[index - 1].focus();
                    }

                    if (e.key === 'ArrowLeft' && index > 0) {
                        inputs[index - 1].focus();
                    }

                    if (e.key === 'ArrowRight' && index < 5) {
                        inputs[index + 1].focus();
                    }
                });
            });

            // Focus first input
            if (inputs.length > 0) {
                inputs[0].focus();
            }
        }

        function handleAutofill(value, inputs, hiddenInput) {
            const digits = value.replace(/\D/g, '').slice(0, 6);
            if (digits.length > 0) {
                digits.split('').forEach(function (char, i) {
                    if (inputs[i]) {
                        inputs[i].value = char;
                    }
                });
                updateHiddenInput(inputs, hiddenInput);
                if (inputs[5]) {
                    inputs[5].focus();
                }
            }
        }

        function updateHiddenInput(inputs, hiddenInput) {
            let code = '';
            inputs.each(function () {
                code += $(this).val() || '';
            });
            hiddenInput.val(code);
        }

        // Enable 2FA button - show modal and immediately load QR
        $('#enable-2fa-btn').on('click', function () {
            if (setupModal) {
                setupModal.show();
                loadQrCode();
            }
        });

        // Load QR code
        function loadQrCode() {
            const qrCodeUrl = $('#enable-2fa-btn').data('qr-code-url') || window.twoFactorRoutes.qrCode;

            $.ajax({
                url: qrCodeUrl,
                method: 'GET',
                success: function (response) {
                    if (response.data) {
                        generatedSecret = response.data.secret;
                        $('#secret-key').val(generatedSecret);
                        $('#qr-code-container').html(response.data.svg);

                        $('#setup-step-1').addClass('d-none');
                        $('#setup-step-2').removeClass('d-none');

                        // Setup code input after step 2 is visible
                        setupCodeInput('confirm', '#confirmation-code');
                    }
                },
                error: function () {
                    alert($('#enable-2fa-btn').data('error-message') || 'An error occurred');
                    if (setupModal) {
                        setupModal.hide();
                    }
                },
            });
        }

        // Confirm 2FA setup
        $('#confirm-2fa-form').on('submit', function (e) {
            e.preventDefault();

            const $form = $(this);
            const $btn = $('#confirm-2fa-btn');
            const $error = $('#confirm-error-alert');
            const confirmUrl = $form.data('confirm-url') || window.twoFactorRoutes.confirm;

            $btn.prop('disabled', true);
            $btn.find('.btn-text').addClass('d-none');
            $btn.find('.spinner-border').removeClass('d-none');
            $error.addClass('d-none');

            $.ajax({
                url: confirmUrl,
                method: 'POST',
                data: $form.serialize(),
                success: function (response) {
                    // Load recovery codes
                    const recoveryCodesUrl =
                        $form.data('recovery-codes-url') || window.twoFactorRoutes.recoveryCodes;

                    $.ajax({
                        url: recoveryCodesUrl,
                        method: 'GET',
                        success: function (codesResponse) {
                            const codes = codesResponse.data.recovery_codes || [];
                            let codesList = '';
                            codes.forEach((code) => {
                                codesList += code + '<br>';
                            });
                            $('#recovery-codes-list').html(codesList);

                            $('#setup-step-2').addClass('d-none');
                            $('#setup-step-3').removeClass('d-none');
                        },
                    });
                },
                error: function (xhr) {
                    $btn.prop('disabled', false);
                    $btn.find('.btn-text').removeClass('d-none');
                    $btn.find('.spinner-border').addClass('d-none');

                    let message = $form.data('invalid-code-message') || 'Invalid code';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    $error.text(message).removeClass('d-none');
                },
            });
        });

        // Modal cleanup on close
        $('#twofa-setup-modal').on('hidden.bs.modal', function () {
            if (!$('#setup-step-3').hasClass('d-none')) {
                // Setup completed, reload page
                window.location.reload();
            } else {
                // Reset modal
                $('#setup-step-1').removeClass('d-none');
                $('#setup-step-2, #setup-step-3').addClass('d-none');
                $('#qr-code-container').empty();
                $('[data-code-input="confirm"]').val('');
                $('#confirmation-code').val('');
                $('#autofill-confirm-code').val('');
                $('#confirm-error-alert').addClass('d-none');
                generatedSecret = null;
            }
        });

        // Disable 2FA
        $('#disable-2fa-btn').on('click', function () {
            if (disableModal) {
                disableModal.show();
                // Setup code input after modal is shown
                setTimeout(function () {
                    setupCodeInput('disable');
                }, 100);
            }
        });

        $('#disable-2fa-form').on('submit', function (e) {
            e.preventDefault();

            const $form = $(this);
            const $btn = $('#confirm-disable-btn');
            const $error = $('#disable-error-alert');
            const disableUrl = $form.data('disable-url') || window.twoFactorRoutes.disable;

            $btn.prop('disabled', true);
            $btn.find('.btn-text').addClass('d-none');
            $btn.find('.spinner-border').removeClass('d-none');
            $error.addClass('d-none');

            $.ajax({
                url: disableUrl,
                method: 'POST',
                data: $form.serialize(),
                success: function () {
                    window.location.reload();
                },
                error: function (xhr) {
                    $btn.prop('disabled', false);
                    $btn.find('.btn-text').removeClass('d-none');
                    $btn.find('.spinner-border').addClass('d-none');

                    let message = $form.data('invalid-code-message') || 'Invalid code';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    $error.text(message).removeClass('d-none');
                },
            });
        });
    });
})(jQuery);
