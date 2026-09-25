                                index + 1
                            );
                        }
                    }
                );

                input.addEventListener(
                    'keydown',
                    function (event) {
                        if (
                            event.key === 'Backspace'
                            && input.value === ''
                            && index > 0
                        ) {
                            event.preventDefault();

                            digits[index - 1].value = '';

                            syncHiddenCode();

                            focusDigit(
                                index - 1
                            );

                            return;
                        }

                        if (
                            event.key === 'ArrowLeft'
                            && index > 0
                        ) {
                            event.preventDefault();

                            focusDigit(
                                index - 1
                            );

                            return;
                        }

                        if (
                            event.key === 'ArrowRight'
                            && index < digits.length - 1
                        ) {
                            event.preventDefault();

                            focusDigit(
                                index + 1
                            );
                        }
                    }
                );

                input.addEventListener(
                    'paste',
                    function (event) {
                        const pasted =
                            event.clipboardData
                                ? event.clipboardData.getData(
                                    'text'
                                )
                                : '';

                        const code =
                            cleanCode(
                                pasted
                            );

                        if (!code) {
                            return;
                        }

                        event.preventDefault();

                        fillCode(code);
                    }
                );
            }
        );

        const initialCode =
            cleanCode(
                codeInput.value
            );

        if (initialCode) {
            fillCode(initialCode);
        } else {
            digits[0].focus();
        }

        form.addEventListener(
            'submit',
            function (event) {
                const code =
                    syncHiddenCode();

                if (code.length !== 6) {
                    event.preventDefault();

                    focusDigit(
                        Math.min(
                            code.length,
                            5
                        )
                    );

                    return;
                }

                submit.disabled = true;
                submit.textContent =
                    'Controleren…';
            }
        );

        if (
            resendForm
            && resendButton
        ) {
            resendForm.addEventListener(
                'submit',
                function () {
                    resendButton.disabled = true;
                    resendButton.textContent =
                        'Versturen…';
                }
            );
        }
    }
);
</script>
@endpush
