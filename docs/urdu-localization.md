# Urdu localization (initial translation pass)

This branch introduces an **initial**, opt-in Dutch-to-Urdu translation for the SmartDesk / Mashal Studio Laravel app.

## Enable Urdu

Set these environment variables on your Laravel installation:

```dotenv
APP_LOCALE=ur
APP_FALLBACK_LOCALE=nl
```

Then clear cached configuration using `php artisan config:clear` (or redeploy with the updated environment).

The main site layout sets `dir="rtl"` when the locale is `ur`. The application keeps the current language settings until you explicitly change them.

## What's included

- `lang/ur.json`: translated interface messages and public information text.
- Primary landing page, login and registration forms, shared navigation, several account/shopping pages.
- About, Contact, Terms of Use and Privacy Policy pages.
- The footer's navigation and accessibility label.

Literal developer-facing identifiers, route names, input names, brand names and programming logic are intentionally not translated.

## Current limitations

**This is not a complete translation of all 399 repository files.** Some pages, email templates, JavaScript messages, controller responses, validation errors and other dynamic strings remain in Dutch or English. Untranslated Laravel keys will display their original wording.

Before deploying the entire app in Urdu, finish extracting and translating the remaining interface strings, verify right-to-left spacing and layout on mobile and desktop, and have any legal/privacy copy reviewed by a qualified Urdu speaker and legal reviewer.

The translation is offered as a separate branch / pull request for review. Do not merge until its coverage and visual behavior suit your deployment.
