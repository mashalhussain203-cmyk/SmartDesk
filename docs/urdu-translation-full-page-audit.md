# SmartDesk Dutch → Urdu: full Blade page review

## Scope and result

- All **78** Blade view templates currently tracked by SmartDesk were inspected for existing translation usage, accessible labels, placeholders and visible text. The complete file-by-file checklist is below.
- **2,427 literal `__('...')` references** in these files were checked against `lang/ur.json`. No missing literal keys were found in that static scan.
- Added many new Urdu translation messages in `lang/ur.json`, and connected additional labels, legal/about text, forms, authentication, emails, support, admin and AI pages to Laravel translation functions.
- Important dynamic JavaScript texts on the image editor, AI chat and homepage now use Blade's JSON-safe translation directive; six external support/live-chat/passkey scripts use `public/js/smartdesk-urdu.js`.
- Gmail and admin headers now have their own language switches, and standalone Gmail pages expose `lang` and `dir` correctly.

## Why Dutch may still appear on a phone

1. The live hosting platform must deploy the latest `main` commit. A GitHub commit alone does not update an unrelated hosting service.
2. Tap **اردو** in the site's header. With an active session, the site should render `lang="ur"` and `dir="rtl"`. On Gmail/admin pages there is also a language switch.
3. If the deployment uses cached views/configuration, use `php artisan optimize:clear` on the server; redeploy/restart workers if applicable.
4. Some **runtime controller responses, error/validation messages, JS template strings with variables, queued mail defaults, third-party text, and partial Blade expressions still require separate auditing and translation**. This review does not claim the entire application is 100% Urdu.
5. The Urdu legal text should be reviewed by a fluent reviewer before production publication.

## Test checklist

- `php artisan test --filter=LanguageSwitchTest`
- `php artisan test --filter=UrduTranslationCoverageTest`
- Test phone widths 320/360/375/390/414/430px, and both Dutch / Urdu across home, Over ons, Contact, Privacy, login, registration, editor, AI, Gmail and admin.
- Open the Over ons page with Urdu selected; headings and body should display Urdu. The test `test_about_page_shows_urdu_translations_instead_of_dutch` exercises that rendering path.
- Exercise upload errors, editor save/error messages, AI voice and support-chat messages, since those appear dynamically.

**Automated PHP/Laravel runtime tests and real-device browser tests have not been executed through this GitHub connector workflow.**

## File-by-file review checklist

### account (1)
- `resources/views/account/two-factor.blade.php`

### admin (3)
- `resources/views/admin/dashboard.blade.php`
- `resources/views/admin/live-chat-transcript.blade.php`
- `resources/views/admin/live-chat.blade.php`

### ai (3)
- `resources/views/ai/chat.blade.php`
- `resources/views/ai/dashboard.blade.php`
- `resources/views/ai/shared.blade.php`

### auth (6)
- `resources/views/auth/email-login.blade.php`
- `resources/views/auth/magic-link-confirm.blade.php`
- `resources/views/auth/telegram-complete.blade.php`
- `resources/views/auth/telegram-mini-app.blade.php`
- `resources/views/auth/tiktok-complete.blade.php`
- `resources/views/auth/two-factor-challenge.blade.php`

### emails (18)
- `resources/views/emails/account-created.blade.php`
- `resources/views/emails/account-deleted.blade.php`
- `resources/views/emails/account-updated.blade.php`
- `resources/views/emails/admin-account-created.blade.php`
- `resources/views/emails/admin-account-updated.blade.php`
- `resources/views/emails/contact-confirmation.blade.php`
- `resources/views/emails/contact-received.blade.php`
- `resources/views/emails/email-changed.blade.php`
- `resources/views/emails/email-verified.blade.php`
- `resources/views/emails/login-alert.blade.php`
- `resources/views/emails/login-code.blade.php`
- `resources/views/emails/magic-login-link.blade.php`
- `resources/views/emails/order-confirmation.blade.php`
- `resources/views/emails/password-changed.blade.php`
- `resources/views/emails/password-reset.blade.php`
- `resources/views/emails/recovery-code.blade.php`
- `resources/views/emails/recovery-email-updated.blade.php`
- `resources/views/emails/verification-code.blade.php`

### gmail (2)
- `resources/views/gmail/inbox.blade.php`
- `resources/views/gmail/show.blade.php`

### images (2)
- `resources/views/images/editor.blade.php`
- `resources/views/images/index.blade.php`

### layouts (3)
- `resources/views/layouts/admin-layout.blade.php`
- `resources/views/layouts/app.blade.php`
- `resources/views/layouts/site-layout.blade.php`

### mail (1)
- `resources/views/mail/thread.blade.php`

### partials (3)
- `resources/views/partials/auth-success-overlay.blade.php`
- `resources/views/partials/language-switcher.blade.php`
- `resources/views/partials/passkeys.blade.php`

### site (25)
- `resources/views/site/about.blade.php`
- `resources/views/site/account.blade.php`
- `resources/views/site/cart.blade.php`
- `resources/views/site/catalog.blade.php`
- `resources/views/site/checkout.blade.php`
- `resources/views/site/contact.blade.php`
- `resources/views/site/favorites.blade.php`
- `resources/views/site/footer-links-snippet.blade.php`
- `resources/views/site/forgot-email-result.blade.php`
- `resources/views/site/forgot-email-verify.blade.php`
- `resources/views/site/forgot-email.blade.php`
- `resources/views/site/forgot-password.blade.php`
- `resources/views/site/home.blade.php`
- `resources/views/site/login-approval.blade.php`
- `resources/views/site/login.blade.php`
- `resources/views/site/partials/guest-chat.blade.php`
- `resources/views/site/partials/login-approval-prompt.blade.php`
- `resources/views/site/privacy.blade.php`
- `resources/views/site/product.blade.php`
- `resources/views/site/recovery-email-verify.blade.php`
- `resources/views/site/register.blade.php`
- `resources/views/site/reset-password.blade.php`
- `resources/views/site/security.blade.php`
- `resources/views/site/terms.blade.php`
- `resources/views/site/verify.blade.php`

### tools (7)
- `resources/views/tools/live-counts.blade.php`
- `resources/views/tools/tiktok-counter.blade.php`
- `resources/views/tools/tiktok-follower-counter.blade.php`
- `resources/views/tools/youtube-subscribers-embed.blade.php`
- `resources/views/tools/youtube-subscribers.blade.php`
- `resources/views/tools/youtube-views-embed.blade.php`
- `resources/views/tools/youtube-views.blade.php`

### users (3)
- `resources/views/users/create.blade.php`
- `resources/views/users/edit.blade.php`
- `resources/views/users/index.blade.php`

### root (1)
- `resources/views/welcome.blade.php`
