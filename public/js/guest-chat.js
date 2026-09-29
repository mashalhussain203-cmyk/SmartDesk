(() => {
    const chat = document.getElementById('guest-chat');
    if (!chat) return;

    const panel = chat.querySelector('.guest-chat__panel');
    const toggle = chat.querySelector('.guest-chat__toggle');
    const input = chat.querySelector('input');
    const messages = chat.querySelector('.guest-chat__messages');

    const answers = [
        { pattern: /wachtwoord|password|vergeten|reset/, text: 'Wachtwoord vergeten? Vraag via de herstelpagina een resetlink aan. Controleer ook je spammap. Deel je wachtwoord of verificatiecode nooit in de chat.', link: 'Wachtwoord herstellen', url: chat.dataset.passwordUrl },
        { pattern: /registr|account.*maken|account.*aanmaken|aanmelden|inschrijven/, text: 'Maak een account via de registratiepagina. Heb je al een account? Dan kun je direct inloggen.', link: 'Account maken', url: chat.dataset.registerUrl },
        { pattern: /inlog|login|log in|aanmeld|verificatie|magic link|passkey|2fa/, text: 'Op de inlogpagina vind je de beschikbare inlogmethoden, zoals je wachtwoord of een e-mailcode. Lukt inloggen niet? Gebruik wachtwoordherstel of neem contact op via de contactpagina.', link: 'Naar inloggen', url: chat.dataset.loginUrl },
        { pattern: /afbeeld|foto|upload|bewerk|resize|image|opslaan/, text: 'Je kunt op de startpagina beginnen met een JPG-, PNG- of WEBP-afbeelding. Log in om je persoonlijke werkruimte te gebruiken en afbeeldingen te beheren en op te slaan.', link: 'Naar de startpagina', url: chat.dataset.homeUrl },
        { pattern: /\bai\b|kunstmatige|chatbot|chatgpt/, text: 'Dit hulpchatje beantwoordt veelgestelde vragen zonder account. Voor de uitgebreidere Mashal AI-chat moet je eerst inloggen.', link: 'Inloggen voor Mashal AI', url: chat.dataset.loginUrl },
        { pattern: /contact|medewerker|support|hulp|probleem|werkt niet/, text: 'Kom je er niet uit? Op de contactpagina vind je hoe je contact kunt opnemen. Deze automatische chat stuurt geen berichten door naar een medewerker.', link: 'Contact opnemen', url: chat.dataset.contactUrl },
        { pattern: /^(hoi|hallo|hey|goedemorgen|goedemiddag|goedenavond)[!.\s]*$/, text: 'Hoi! Stel gerust een vraag over inloggen, een account maken of afbeeldingen bewerken.' },
        { pattern: /bedankt|dankjewel|dank je/, text: 'Graag gedaan! Kan ik je nog ergens mee helpen?' },
    ];

    function setOpen(open) {
        panel.hidden = !open;
        toggle.setAttribute('aria-expanded', String(open));
        if (open) input.focus();
        else toggle.focus();
    }

    function appendMessage(text, user = false, answer = null) {
        const message = document.createElement('p');
        message.className = 'guest-chat__message' + (user ? ' guest-chat__message--user' : '');
        message.textContent = text;
        if (answer?.url) {
            const link = document.createElement('a');
            link.href = answer.url;
            link.textContent = answer.link;
            message.append(link);
        }
        messages.append(message);
        while (messages.children.length > 40) messages.firstElementChild.remove();
        messages.scrollTop = messages.scrollHeight;
    }

    function ask(question) {
        const text = question.trim().slice(0, 500);
        if (!text) return;
        appendMessage(text, true);
        const answer = answers.find(item => item.pattern.test(text.toLocaleLowerCase('nl')))
            ?? { text: 'Daar heb ik geen vast antwoord op. Ik kan helpen met inloggen, registreren, wachtwoordherstel en afbeeldingen. Voor andere vragen kun je contact opnemen.', link: 'Naar contact', url: chat.dataset.contactUrl };
        appendMessage(answer.text, false, answer);
        input.value = '';
        input.focus();
    }

    toggle.addEventListener('click', () => setOpen(panel.hidden));
    chat.querySelector('.guest-chat__close').addEventListener('click', () => setOpen(false));
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
    chat.querySelectorAll('[data-question]').forEach(button => {
        button.addEventListener('click', () => ask(button.dataset.question));
    });
    chat.hidden = false;
})();
