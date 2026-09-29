(() => {
    const chat = document.getElementById('guest-chat');
    if (!chat) return;

    const panel = chat.querySelector('.guest-chat__panel');
    const toggle = chat.querySelector('.guest-chat__toggle');
    const input = chat.querySelector('input');
    const messages = chat.querySelector('.guest-chat__messages');
    const send = chat.querySelector('.guest-chat__send');
    const suggestions = chat.querySelectorAll('[data-question]');

    let history = [];
    let busy = false;

    function setOpen(open) {
        panel.hidden = !open;
        toggle.setAttribute('aria-expanded', String(open));

        if (open) {
            input.focus();
        } else {
            toggle.focus();
        }
    }

    function appendMessage(text, user = false) {
        const message = document.createElement('p');

        message.className = 'guest-chat__message'
            + (user ? ' guest-chat__message--user' : '');

        message.textContent = text;
        messages.append(message);

        while (messages.children.length > 40) {
            messages.firstElementChild.remove();
        }

        messages.scrollTop = messages.scrollHeight;
    }

    async function ask(question) {
        const text = question.trim().slice(0, 2000);
        if (!text || busy) return;

        const csrf = document.querySelector(
            'meta[name="csrf-token"]'
        )?.content;

        if (!csrf || !chat.dataset.endpoint) {
            appendMessage(
                'De chat kon niet starten. Vernieuw de pagina. '
                + 'Controleer ook of de nieuwste guest-chat.blade.php is geplaatst.'
            );
            return;
        }

        busy = true;
        send.disabled = true;
        input.disabled = true;

        suggestions.forEach(button => {
            button.disabled = true;
        });

        appendMessage(text, true);
        input.value = '';

        const pending = document.createElement('p');
        pending.className = 'guest-chat__message';
        pending.textContent = 'Mashal AI denkt na…';
        messages.append(pending);
        messages.scrollTop = messages.scrollHeight;

        const controller = new AbortController();
        const timeout = window.setTimeout(
            () => controller.abort(),
            150000
        );

        try {
            const response = await fetch(chat.dataset.endpoint, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify({
                    message: text,
                    history: history,
                }),
                signal: controller.signal,
            });

            if (!response.ok) {
                const errors = {
                    404: 'De chatroute ontbreekt. Controleer routes/web.php.',
                    419: 'Je sessie is verlopen. Vernieuw de pagina.',
                    422: 'Je bericht kon niet worden verwerkt. Maak het korter.',
                    429: 'De limiet is bereikt. Wacht een minuut en probeer opnieuw.',
                    503: 'De AI is tijdelijk niet beschikbaar. Probeer het later opnieuw.',
                };

                throw new Error(
                    errors[response.status]
                    || 'De AI kon geen antwoord geven. Probeer het later opnieuw.'
                );
            }

            const result = await response.json();

            if (
                typeof result.message !== 'string'
                || !result.message.trim()
            ) {
                throw new Error(
                    'De AI gaf geen antwoord. Probeer je vraag opnieuw.'
                );
            }

            pending.remove();
            appendMessage(result.message);

            history.push(
                { role: 'user', content: text },
                {
                    role: 'assistant',
                    content: result.message.slice(0, 4000),
                }
            );

            history = history.slice(-10);
        } catch (error) {
            pending.remove();

            let message = error.message;

            if (error.name === 'AbortError') {
                message = 'Het antwoord duurde te lang. Probeer opnieuw.';
            } else if (error instanceof TypeError) {
                message = 'Geen verbinding met de chat. Controleer je internet.';
            } else if (error instanceof SyntaxError) {
                message = 'De server stuurde een ongeldig antwoord.';
            }

            appendMessage(message);
            input.value = text;
        } finally {
            window.clearTimeout(timeout);
            busy = false;
            send.disabled = false;
            input.disabled = false;

            suggestions.forEach(button => {
                button.disabled = false;
            });

            if (!panel.hidden && chat.contains(document.activeElement)) {
                input.focus();
            }
        }
    }

    toggle.addEventListener('click', () => {
        setOpen(panel.hidden);
    });

    chat.querySelector('.guest-chat__close')
        .addEventListener('click', () => {
            setOpen(false);
        });

    chat.addEventListener('keydown', event => {
        if (event.key === 'Escape' && !panel.hidden) {
            event.preventDefault();
            setOpen(false);
        }
    });

    chat.querySelector('form').addEventListener('submit', event => {
        event.preventDefault();
        ask(input.value);
    });

    suggestions.forEach(button => {
        button.addEventListener('click', () => {
            ask(button.dataset.question);
        });
    });

    chat.hidden = false;
})();