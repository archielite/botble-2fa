(function ($) {
    'use strict';

    $(document).ready(function () {
        if (!$('#twofa-challenge-form').length) {
            return;
        }

        // Setup 6-box code input
        function setupCodeInput(type, hiddenInputId) {
            const inputs = $('[data-code-input="' + type + '"]');
            const hiddenInput = $(hiddenInputId);
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

        // Initialize 6-box input for challenge code
        setupCodeInput('challenge', '#code');

        // Toggle recovery code section
        $('#use-recovery-btn').on('click', function () {
            $('#twofa-challenge-form').addClass('d-none');
            $('#recovery-section').removeClass('d-none');
            $(this).addClass('d-none');
        });

        $('#back-to-code-btn').on('click', function () {
            $('#recovery-section').addClass('d-none');
            $('#twofa-challenge-form').removeClass('d-none');
            $('#use-recovery-btn').removeClass('d-none');
            // Refocus first input
            $('[data-code-input="challenge"]').first().focus();
        });

        // Handle TOTP code form submission
        $('#twofa-challenge-form').on('submit', function (e) {
            e.preventDefault();

            const $form = $(this);
            const $btn = $('#verify-btn');
            const $error = $('#error-alert');

            $btn.prop('disabled', true);
            $btn.find('.btn-text').addClass('d-none');
            $btn.find('.spinner-border').removeClass('d-none');
            $error.addClass('d-none');

            $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                data: $form.serialize(),
                success: function (response) {
                    if (response.error === false && response.data && response.data.next_url) {
                        window.location.href = response.data.next_url;
                    }
                },
                error: function (xhr) {
                    $btn.prop('disabled', false);
                    $btn.find('.btn-text').removeClass('d-none');
                    $btn.find('.spinner-border').addClass('d-none');

                    let message = $form.data('invalid-message') || 'Invalid code';

                    // Check for validation errors
                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        } else if (xhr.responseJSON.errors) {
                            // Laravel validation errors
                            const errors = xhr.responseJSON.errors;
                            if (errors.code && errors.code.length > 0) {
                                message = errors.code[0];
                            } else if (errors.recovery_code && errors.recovery_code.length > 0) {
                                message = errors.recovery_code[0];
                            }
                        }
                    }

                    $error.text(message).removeClass('d-none');
                },
            });
        });

        // Handle recovery code form submission
        $('#recovery-form').on('submit', function (e) {
            e.preventDefault();

            const $form = $(this);
            const $btn = $('#verify-recovery-btn');
            const $error = $('#recovery-error-alert');

            $btn.prop('disabled', true);
            $btn.find('.btn-text').addClass('d-none');
            $btn.find('.spinner-border').removeClass('d-none');
            $error.addClass('d-none');

            $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                data: $form.serialize(),
                success: function (response) {
                    if (response.error === false && response.data && response.data.next_url) {
                        window.location.href = response.data.next_url;
                    }
                },
                error: function (xhr) {
                    $btn.prop('disabled', false);
                    $btn.find('.btn-text').removeClass('d-none');
                    $btn.find('.spinner-border').addClass('d-none');

                    let message = $form.data('invalid-message') || 'Invalid recovery code';

                    // Check for validation errors
                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        } else if (xhr.responseJSON.errors) {
                            // Laravel validation errors
                            const errors = xhr.responseJSON.errors;
                            if (errors.recovery_code && errors.recovery_code.length > 0) {
                                message = errors.recovery_code[0];
                            } else if (errors.code && errors.code.length > 0) {
                                message = errors.code[0];
                            }
                        }
                    }

                    $error.text(message).removeClass('d-none');
                },
            });
        });
    });
})(jQuery);
