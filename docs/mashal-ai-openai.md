# Mashal AI: eigen chatinterface met OpenAI als enige AI-provider

Mashal AI op `/ai-chat` gebruikt vanaf deze wijziging **geen Groq voor chat-, audio- of afbeeldingsanalyse**. De bestaande koppeling met OpenAI wordt hergebruikt in een originele lichtgekleurde interface, gebaseerd op algemene chat-UX (zijbalk, chatgeschiedenis, centraal invoerveld, plus-menu). Er wordt geen ChatGPT-code, account, sessie, iframe of private website-inhoud gekopieerd.

## Hoe het werkt

`Browser op SmartDesk -> Laravel /ai-chat/message -> OpenAI Responses API -> antwoord in Mashal AI`

- Gesprekken staan nog steeds lokaal in de browser en worden voor ingelogde gebruikers waar mogelijk gesynchroniseerd via het bestaande AI Workspace.
- Bestanden worden op de server verwerkt; afbeeldingen via OpenAI vision; spraak via OpenAI transcription; de bestaande tekst-naar-spraak-functie via Azure/browser blijft onafhankelijk.
- Modes `auto`, `web`, `research`, `code`, `plain` volgen de bestaande route.
- De plusknop toont upload, camera, spraak en moduskeuze.
- Een mislukte aanvraag verwijdert het niet-opgeslagen conceptbericht uit het transcript en herstelt de invoertekst en bestaande uploadselectie.
- Bij een verlopen CSRF-sessie (HTTP 419) toont de interface een expliciete instructie in plaats van te doen alsof het bericht verstuurd is.

## Nodig om ChatGPT-achtige GPT-antwoorden te laten werken

In Railway > SmartDesk-service > Variables:

```dotenv
OPENAI_API_KEY=<zet hier ZELF de geheime API-sleutel via Railway>
OPENAI_MODEL=gpt-5-mini
OPENAI_VISION_MODEL=gpt-4.1-mini
OPENAI_TRANSCRIPTION_MODEL=gpt-4o-mini-transcribe
```

OpenAI API-sleutels komen van https://platform.openai.com/api-keys. De API wordt apart gefactureerd. **Zonder deze serverconfiguratie kan niemand echte ChatGPT/GPT-antwoorden via SmartDesk ontvangen.** Een publieke chatgpt.com-webpagina bevat niet de server-authenticatie om antwoorden vanaf een andere website door te sturen. Kopiëren van de HTML is daarvoor niet voldoende.

`MASHAL_AI_PROVIDER` is niet meer nodig voor de chat: Mashal AI kiest altijd OpenAI. De oude Groq-klassen blijven voor eventuele andere delen van de applicatie bestaan, maar zijn uit de Mashal AI-controller en de bestandslezer losgekoppeld. Laat eventuele bestaande `GROQ_API_KEY` alleen staan als andere tools die nog gebruiken; die sleutel maakt Mashal AI niet actief.

## Validatie

De featuretests `MashalOpenAiProviderTest` en `MashalAiMediaProviderTest` gebruiken HTTP-fakes. Uitvoeren na deploy of in CI:

```bash
php artisan test --filter='Mashal(OpenAiProvider|AiMediaProvider)Test'
php artisan optimize:clear
```

De fakes bewijzen de PHP-verwerking, niet of een echte API-key aanwezig is, of het gekozen model voor dit OpenAI-account toegankelijk is. Test dat apart op Railway met een echt bericht, een upload en een spraakopname. Controleer `/ai-chat` op telefoon en desktop; wijzigingen aan de livechat en 419-oplossing voor de **andere** website-livechat zijn niet nodig.
