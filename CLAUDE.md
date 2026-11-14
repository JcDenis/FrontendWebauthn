# FrontendWebauthn

Notes for AI assistants working on the Dotclear plugin FrontendWebauthn.

**Read first** the shared conventions of the author's Dotclear modules:
https://raw.githubusercontent.com/JcDenis/gh2da/master/CONVENTIONS.md
(if a web tool refuses the address, use `curl -s` on it from the shell).
The rules below take precedence for this module.

## Module

Use passkey on fronend.

No admin page. Public side: behaviors.

Required Dotclear and PHP versions: see `_define.php`.

## Structure

* `src/Frontend.php`: public side: behaviors, template tags and URL handlers registration.
* `src/FrontendBehaviors.php`: public behaviors.
* `src/My.php`: module helpers (`My::id()`, settings, permissions).
* `src/WebAuthn.php`: this class set up webauthn options.
* `src/WebAuthnStore.php`: plugin FrontendWebauthn WebAuthn store class.
* `js/`: `frontend-webauthn.js`.
* Behaviors used: `publicHeadContent`.
* Requires FrontendSession (`requires` in `_define.php`).

## Pitfalls

* CI installs FrontendSession from the author's repositories, then from franck-paul's.

## Testing

* CI browses the common admin and public pages; extra pages can be listed in `.github/dotclear-test.txt`.
