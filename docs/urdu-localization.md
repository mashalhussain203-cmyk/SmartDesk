# Nederlands / Urdu in SmartDesk

The global site header now offers two language buttons: **NL** and **اردو**.

## How it works

- The visible header buttons submit a CSRF-protected POST to `/language`.
- Supported values are strictly `nl` and `ur`.
- The choice is stored in the visitor's session as `site_locale` and applied on subsequent web requests by `App\Http\Middleware\SetSiteLocale`.
- Switching languages returns visitors to their original page, subject to a same-host check.
- When Urdu is active, the site layout sets `lang="ur"` and `dir="rtl"`.
- No JavaScript is required for the switch. The same buttons are visible in the mobile header.
- By default, the application uses the locale configured in `APP_LOCALE` (normally `nl`), until the visitor selects a language.

## Deployment

Do **not** set `APP_LOCALE=ur` merely to enable the language buttons. Visitors can select Urdu in the navigation themselves. To keep Dutch as the default, configure:

```dotenv
APP_LOCALE=nl
APP_FALLBACK_LOCALE=nl
```

Then clear the Laravel configuration cache if needed: `php artisan config:clear`.

## What's translated so far

- `lang/ur.json`: initial Urdu translations.
- Main landing page, login and registration forms, shared navigation, several account/shopping pages.
- About, Contact, Terms of Use and Privacy Policy pages.
- The footer's navigation and accessibility label.

Developer identifiers, route names, input names, brand names and programming logic stay unchanged.

## Tests and outstanding work

`tests/Feature/LanguageSwitchTest.php` covers:
- button visibility;
- switching to Urdu and back with the session preserved;
- rejecting unsupported locale values;
- blocking external referrer redirects.

**The test suite has not been executed in the GitHub connector environment.**

**This is still a partial translation, not the entire application.** Some other pages, emails, dynamic JavaScript messages and controller responses remain Dutch or English. Untranslated Laravel keys render their original text.

Before merging, check the UI on narrow mobile widths, validate the right-to-left layout, run the Laravel test suite, and have legal/privacy translations reviewed as appropriate.
