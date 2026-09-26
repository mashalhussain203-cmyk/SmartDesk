<style>
    .login-approval-prompt[hidden] {
        display: none !important;
    }

    .login-approval-prompt {
        position: fixed;
        inset: 0;
        z-index: 2147483000;
        padding: 18px;
        display: grid;
        place-items: center;
        background: rgba(0,0,0,.72);
        overscroll-behavior: contain;
    }

    .login-approval-prompt-card {
        width: min(100%, 430px);
        padding: 25px;
        border: 1px solid rgba(255,255,255,.13);
        border-radius: 24px;
        color: #f7f7f8;
        background:
            linear-gradient(145deg, rgba(255,255,255,.05), transparent 34%),
            #0e0f12;
        box-shadow: 0 32px 90px rgba(0,0,0,.58);
    }

    .login-approval-prompt-kicker {
        color: #d9bc63;
        font-size: 9px;
        font-weight: 900;
        letter-spacing: .16em;
        text-transform: uppercase;
    }

    .login-approval-prompt-title {
        margin: 12px 0 0;
        color: #fff;
        font-size: clamp(27px, 8vw, 36px);
        line-height: 1.02;
        letter-spacing: -.045em;
        font-weight: 900;
    }

    .login-approval-prompt-copy {
        margin: 12px 0 0;
        color: #929399;
        font-size: 11px;
        line-height: 1.7;
    }

    .login-approval-device {
        margin-top: 16px;
        padding: 11px 12px;
        border: 1px solid rgba(255,255,255,.075);
        border-radius: 11px;
        color: #c7c8cc;
        background: rgba(255,255,255,.025);
        font-size: 9px;
        line-height: 1.55;
    }

    .login-approval-options {
        margin-top: 21px;
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 8px;
    }

    .login-approval-number {
        min-height: 70px;
        border: 1px solid rgba(244,238,31,.18);
        border-radius: 14px;
        color: #f4ee1f;
        background: rgba(244,238,31,.035);
        font-size: 28px;
        font-weight: 950;
        cursor: pointer;
        -webkit-tap-highlight-color: transparent;
        transition:
            transform .16s ease,
            border-color .16s ease,
            background .16s ease;
    }

    .login-approval-number:hover,
    .login-approval-number:focus-visible {
        transform: translateY(-2px);
        border-color: rgba(244,238,31,.45);
        background: rgba(244,238,31,.065);
        outline: none;
    }

    .login-approval-reject {
        width: 100%;
        min-height: 42px;
        margin-top: 12px;
        border: 1px solid rgba(255,116,116,.16);
        border-radius: 11px;
        color: #ffaaaa;
        background: rgba(168,47,47,.065);
        font-size: 9px;
        font-weight: 900;
        cursor: pointer;
    }

    .login-approval-prompt-message {
        display: none;
        margin-top: 12px;
        padding: 10px 11px;
        border-radius: 10px;
        font-size: 9px;
        line-height: 1.5;
    }

    .login-approval-prompt-message.visible {
        display: block;
        border: 1px solid rgba(255,255,255,.08);
        color: #bfc0c4;
        background: rgba(255,255,255,.025);
    }

    @media (max-width: 380px) {
        .login-approval-prompt {
            padding: 12px;
        }

        .login-approval-prompt-card {
            padding: 21px 17px;
        }

        .login-approval-options {
            gap: 6px;
        }

        .login-approval-number {
            min-height: 62px;
        }
    }
</style>

<div
    class="login-approval-prompt"
    id="loginApprovalPrompt"
    role="dialog"
    aria-modal="true"
    aria-labelledby="loginApprovalPromptTitle"
    hidden
>
    <div class="login-approval-prompt-card">
        <div class="login-approval-prompt-kicker">
            Beveiligingscontrole
        </div>

        <h2
            class="login-approval-prompt-title"
            id="loginApprovalPromptTitle"
        >
            Nieuwe loginpoging
        </h2>

        <p class="login-approval-prompt-copy">
            Iemand heeft het juiste wachtwoord voor jouw account ingevoerd.
            Kijk naar het nummer op dat apparaat en kies hieronder hetzelfde
            nummer. Ben jij dit niet, wijs de poging dan af.
        </p>

        <div
            class="login-approval-device"
            id="loginApprovalDevice"
        ></div>

        <div
            class="login-approval-options"
            id="loginApprovalOptions"
        ></div>

        <button
            class="login-approval-reject"
            id="loginApprovalReject"
            type="button"
        >
            Nee, ik ben dit niet
        </button>

        <div
            class="login-approval-prompt-message"
            id="loginApprovalPromptMessage"
            role="status"
        ></div>
    </div>
</div>

<script>
(function () {
    const pendingUrl =
        @json(
            route(
                'login-approval.pending'
            )
        );

    const respondBaseUrl =
        @json(
            url(
                '/account/login-approval'
            )
        );

    const csrfToken =
        document
            .querySelector(
                'meta[name="csrf-token"]'
            )
            ?.getAttribute(
                'content'
            )
        || '';

    const prompt =
        document.getElementById(
            'loginApprovalPrompt'
        );

    const device =
        document.getElementById(
            'loginApprovalDevice'
        );

    const options =
        document.getElementById(
            'loginApprovalOptions'
        );

    const rejectButton =
        document.getElementById(
            'loginApprovalReject'
        );

    const message =
        document.getElementById(
            'loginApprovalPromptMessage'
        );

    if (
        !prompt
        || !device
        || !options
        || !rejectButton
        || !message
    ) {
        return;
    }

    let activeChallenge = null;
    let busy = false;
    let timer = null;

    function setMessage(text) {
        if (!text) {
            message.textContent = '';
            message.classList.remove(
                'visible'
            );
            return;
        }

        message.textContent = text;
        message.classList.add(
            'visible'
        );
    }

    function hidePrompt() {
        prompt.hidden = true;
        activeChallenge = null;
        options.innerHTML = '';
        setMessage('');
    }

    function showChallenge(challenge) {
        activeChallenge = challenge;

        device.textContent =
            challenge.device
            + ' · '
            + challenge.requested_at;

        options.innerHTML = '';

        (challenge.options || [])
            .forEach(
                function (number) {
                    const button =
                        document.createElement(
                            'button'
                        );

                    button.type =
                        'button';

                    button.className =
                        'login-approval-number';

                    button.textContent =
                        String(number);

                    button.addEventListener(
                        'click',
                        function () {
                            respond(
                                'approve',
                                Number(number)
                            );
                        }
                    );

                    options.appendChild(
                        button
                    );
                }
            );

        setMessage('');
        prompt.hidden = false;
    }

    async function respond(
        action,
        number
    ) {
        if (
            busy
            || !activeChallenge
        ) {
            return;
        }

        busy = true;
        setMessage(
            action === 'reject'
                ? 'Poging afwijzen…'
                : 'Nummer controleren…'
        );

        try {
            const response =
                await fetch(
                    respondBaseUrl
                        + '/'
                        + encodeURIComponent(
                            activeChallenge.id
                        )
                        + '/respond',
                    {
                        method:
                            'POST',

                        credentials:
                            'same-origin',

                        headers: {
                            'Accept':
                                'application/json',

                            'Content-Type':
                                'application/json',

                            'X-CSRF-TOKEN':
                                csrfToken,
                        },

                        body:
                            JSON.stringify({
                                action:
                                    action,

                                number:
                                    number || null,
                            }),
                    }
                );

            const data =
                await response.json();

            if (
                response.ok
                && data.status === 'approved'
            ) {
                setMessage(
                    'Login goedgekeurd.'
                );

                window.setTimeout(
                    hidePrompt,
                    700
                );

                return;
            }

            if (
                data.status === 'rejected'
            ) {
                setMessage(
                    'Loginpoging afgewezen.'
                );

                window.setTimeout(
                    hidePrompt,
                    700
                );

                return;
            }

            if (
                data.status
                === 'wrong_number'
            ) {
                setMessage(
                    'Dat nummer klopte niet. De loginpoging is voor de veiligheid afgewezen.'
                );

                window.setTimeout(
                    hidePrompt,
                    1600
                );

                return;
            }

            if (
                data.status === 'expired'
            ) {
                setMessage(
                    'Deze loginpoging is inmiddels verlopen.'
                );

                window.setTimeout(
                    hidePrompt,
                    1200
                );

                return;
            }

            setMessage(
                'De loginpoging kon niet worden verwerkt.'
            );
        } catch (error) {
            setMessage(
                'Tijdelijk verbindingsprobleem. Probeer opnieuw.'
            );
        } finally {
            busy = false;
        }
    }

    rejectButton.addEventListener(
        'click',
        function () {
            respond(
                'reject',
                null
            );
        }
    );

    async function poll() {
        if (
            document.hidden
            || busy
        ) {
            schedule();
            return;
        }

        try {
            const response =
                await fetch(
                    pendingUrl,
                    {
                        method:
                            'GET',

                        credentials:
                            'same-origin',

                        headers: {
                            'Accept':
                                'application/json',
                        },

                        cache:
                            'no-store',
                    }
                );

            if (!response.ok) {
                schedule();
                return;
            }

            const data =
                await response.json();

            if (
                data.challenge
                && (
                    !activeChallenge
                    || activeChallenge.id
                        !== data.challenge.id
                )
            ) {
                showChallenge(
                    data.challenge
                );
            } else if (
                !data.challenge
                && activeChallenge
            ) {
                hidePrompt();
            }
        } catch (error) {
            /*
             * Geen zichtbare foutmelding nodig tijdens achtergrondpolling.
             */
        }

        schedule();
    }

    function schedule() {
        if (timer) {
            window.clearTimeout(
                timer
            );
        }

        timer =
            window.setTimeout(
                poll,
                4000
            );
    }

    document.addEventListener(
        'visibilitychange',
        function () {
            if (
                !document.hidden
            ) {
                poll();
            }
        }
    );

    poll();
})();
</script>
