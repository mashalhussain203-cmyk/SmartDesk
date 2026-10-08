# Mashal AI — echte GPT-antwoorden via OpenAI

De AI op `/ai-chat` gebruikt hetzelfde SmartDesk-patroon als de publieke TikTok- en YouTube-tools: **browser → Laravel-route → server-side provider → JSON terug naar browser**.

**Belangrijk verschil:** TikTok/YouTube-tellers zijn openbare statistieken die browserhelpers kunnen ophalen. Privéberichten en antwoorden van ChatGPT zijn niet betrouwbaar of toegestaan als publieke scrapingbron. Gebruik voor AI-antwoorden de officiële [OpenAI Responses API](https://platform.openai.com/docs/api-reference/responses).

## Aan-/uitzetten op Railway

Voeg onder de Variables van de SmartDesk-service toe:

```dotenv
MASHAL_AI_PROVIDER=openai
OPENAI_API_KEY=<eigen sleutel uitsluitend in Railway invullen>
OPENAI_MODEL=gpt-5-mini
```

Zet **nooit** een echte sleutel in GitHub, JavaScript, Blade of een chatbericht. Maak een API-key op https://platform.openai.com/api-keys en controleer vooraf het API-budget; API-verbruik wordt apart gefactureerd.

Deploy/restart de Railway-service zodat Laravel de nieuwe variabelen inleest. Bij een gecachte Laravel-configuratie moet de deployment opnieuw `config:cache` uitvoeren. Als er geen sleutel is, toont Mashal AI een duidelijke configuratiefout en verstuurt het **geen** verzoek naar ChatGPT.com.

Zonder `MASHAL_AI_PROVIDER=openai` blijft de bestaande Groq-chat werken. Schakel terug met `MASHAL_AI_PROVIDER=groq`.

## Wat blijft werken?

- De bestaande `/ai-chat`-pagina met gesprekshistorie en verzendknop.
- Het verzenden van teksten en tekstextracties uit uploads via Laravel.
- Accountgesprekken en workspace-opslag op de bestaande database.
- Voice: bestaande Groq Whisper-spraakherkenning + gekozen tekstprovider. Azure- of browser-TTS blijft behouden.
- Auto/plain/chat, web zoeken en research via ondersteunde Responses API tools; code-stand gebruikt de officiële Code Interpreter tool. Toolcalls zijn extra kosten en vereisen model-/accounttoegang.

OpenAI wordt uitsluitend server-side aangeroepen. De API-key wordt niet aan de bezoeker gegeven. OpenAI-requests worden verstuurd met `store: false`, zodat de API-respons niet standaard als opgeslagen OpenAI-response wordt aangelegd; SmartDesk blijft zelf verantwoordelijk voor eigen gesprekopslag.

## Testen

`php artisan test --filter=MashalOpenAiProviderTest`

De tests gebruiken HTTP-fakes en vereisen geen echte sleutel. Test daarna met een eigen key op Railway het versturen, een langere chat en accountgeschiedenis; de test-fakes bewijzen geen live API-toegang.
