# Mobile-responsive checks for SmartDesk

This change adds `public/css/mobile-responsive.css` and loads it **after** the Blade page styles from the primary site layout, admin layout, and standalone Gmail inbox/message pages.

## Changes made

- 320–767px layouts: narrow navigation, Dutch / Urdu buttons, hamburger and mobile drawer get room to fit.
- Mobile drawer can scroll vertically on short screens, respecting the bottom safe-area inset.
- Main-page images, long copy and form controls are constrained to their container.
- iPhone focused text fields use a legible font size to avoid unwanted Safari zooming.
- Home page hero typography and upload area are scaled down for small devices.
- Image editor toolbars scroll horizontally without growing the full viewport; tool controls get finger-friendly targets.
- Account, login/registration, security, admin tables and mail layouts get conservative overflow protection.
- Urdu RTL documents retain the native writing direction; emails and URLs are typed left-to-right.

The new stylesheet uses width-based media queries so regular desktop views are not redesigned.

## Test matrix to run on an actual phone or with a browser viewport

| Width | Priority checks |
| --- | --- |
| 320px | Header, NL/اردو selector, hamburger, login/register form, editor toolbar |
| 360px | Homepage hero, account details, bottom safe areas, WhatsApp-sized screenshots |
| 375px | iPhone SE-sized menus, keyboard/focused inputs, password reset |
| 390px | iPhone UI with Urdu enabled and long translated labels |
| 414–430px | Image library, upload preview, AI chat, support chat overlay |
| 768px | Tablet/mobile boundary and tablet navigation |
| Desktop (>1120px) | Verify existing wide layout remains unchanged |

Also verify:
1. From the phone header and the menu, switching `NL` / `اردو` keeps the language selection on the next page.
2. Forms can be submitted with the keyboard open and no field or button is obscured.
3. The mobile drawer can scroll to its last item.
4. The editor's tools remain reachable by horizontal scrolling and the canvas is not cut off.
5. Admin data tables scroll **inside their container**, not by forcing the whole page to become wider.
6. Screen readers can identify the navbar controls and touch targets are at least about 44px.
7. Login, password recovery and security forms still submit normally.

## Automated check

`php artisan test --filter=MobileResponsiveStylesTest`

This static regression test checks the stylesheet path, breakpoints, and inclusion order. **No real-device or browser screenshot tests have been executed by this GitHub-only workflow**, and these should be completed before claiming pixel-perfect mobile support.

## Extra telefoonverbeteringen bovenop de 419-fix

- Contactformulier op schermen tot 767px: tekstvelden van minimaal 16px, zodat Safari niet automatisch inzoomt, en foutmeldingen die binnen het scherm afbreken.
- Op schermen tot 480px: compacte marges, leesbare titel en een verzendknop over de volledige breedte.
- De bestaande CSRF-/sessieoplossing uit commit `1589e0619fcce8460dda144bcca4bff03a1d75c9` is ongewijzigd.
- De versiestring van de mobiele CSS is opgehoogd, zodat browsers bij deployment het nieuwe bestand ophalen.

Controleer handmatig een verlopen contactformulier op een telefoon en test indien mogelijk ook:

`php artisan test --filter=ContactFormReliabilityTest`

`php artisan test --filter=MobileResponsiveStylesTest`
