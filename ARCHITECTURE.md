# EmailValidator Architecture

## Purpose

A strict, RFC-compliant PHP email address validator.  Supports RFC 5321/5322
syntax, DNS MX-record lookups, spoof detection (via PHP's `Spoofchecker`), and
a composable multi-validation pipeline.

## Directory Structure

```
src/
  Email_Validator.php          — main entry point; delegates to a validation strategy
  Email_Lexer.php              — tokenises the email string character by character
  Email_Parser.php             — abstract base parser
  Message_ID_Parser.php        — parser for RFC 2822 Message-ID addresses
  Parser.php                   — abstract parser base
  Parser/
    Comment.php                — handles RFC comments (…)
    Domain_Literal.php         — handles [domain literal] addresses
    Domain_Part.php            — parses the domain after @
    Double_Quote.php           — handles "quoted" local parts
    Folding_White_Space.php    — handles FWS / CFWS tokens
    Local_Part.php             — parses the local part before @
    Part_Parser.php            — shared parser utilities
    CommentStrategy/           — strategy objects for local vs domain comments
  Result/
    Result.php                 — interface
    Valid_Email.php            — success result
    Invalid_Email.php          — failure result (carries a Reason)
    Multiple_Errors.php        — aggregates errors for multi-validation
    Spoof_Email.php            — spoof detection failure
    Reason/                    — ~30 fine-grained reason classes (one per RFC violation)
  Validation/
    Email_Validation.php       — interface implemented by all strategies
    RFC_Validation.php         — full RFC 5321/5322 syntax check (default)
    No_RFC_Warnings_Validation.php — rejects deprecated-but-valid constructs
    DNS_Check_Validation.php   — verifies MX/A/AAAA records exist
    Message_ID_Validation.php  — validates RFC 2822 Message-ID format
    Multiple_Validation_With_And.php — runs several strategies; all must pass
    Extra/
      Spoof_Check_Validation.php   — uses PHP's intl Spoofchecker
  Warning/                     — ~15 warning classes (non-fatal RFC quirks)
```

## Key Design Decisions

- **Strategy pattern**: `Email_Validator::is_valid()` accepts any
  `Email_Validation` implementation, making it trivial to compose or replace
  validation logic without changing the validator itself.
- **Typed results**: failures carry a specific `Reason` subclass (e.g.
  `No_Domain_Part`, `Consecutive_Dot`) rather than generic strings, enabling
  precise error messaging.
- **Warnings vs errors**: non-fatal but deprecated email constructs (quoted
  strings, comments) emit `Warning` objects that callers can inspect separately.
- **Lexer reuse**: `Email_Lexer` is created once in the constructor and reset
  between calls, avoiding repeated object allocation.

## Extension Points

- Implement `Email_Validation` to add custom validation logic (e.g. domain
  blocklists, disposable address detection).
- Compose validations with `Multiple_Validation_With_And` so all strategies
  must pass.
- Implement a custom `Reason` for domain-specific invalidity messages.

## Dependency Flow

```
Consumer
  └── Email_Validator::is_valid($email, $strategy)
        ├── Email_Lexer          (tokenisation)
        └── Email_Validation     (strategy)
              ├── RFC_Validation → Email_Parser → Parser\{Local_Part, Domain_Part, …}
              ├── DNS_Check_Validation
              └── Multiple_Validation_With_And → [Email_Validation, …]
```
