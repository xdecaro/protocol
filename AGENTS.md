# Protocol — Codex Repository Rules

## Product scope

Protocol by xdecaro is the Joomla component responsible for administrative protocol identity and workflow: registers, progressive numbering, protocol date/year, incoming/outgoing/internal direction, subjects, classification/fascicles when implemented, assignments, relations, rectifications, cancellations and audit.

Keep protocol-domain logic in this repository. Do not move it to Xdecaro Core merely because it can technically be reused.

## Xdecaro Core

Use Xdecaro Core only for genuinely shared, domain-neutral infrastructure such as common design tokens, administrator UI primitives, asset registration, generic diagnostics, extension registry, dependency/version checks and safe generic helpers.

Protocol may depend on a stable Core public API; Core must never depend on Protocol. Never invent a Core API that has not been verified.

## Documents integration

Documents is optional and remains a separate product.

Protocol owns administrative metadata and protocol identity. Documents owns document storage, versions, protected download, document-level ACL and storage security.

Protocol must continue to load and operate when Documents is not installed. Integrations must use explicit services/events/contracts and must not write directly into another component's private tables.

## Forms and other integrations

Forms, Courses, Competitions, Membership, Events, Bookings, Finance and future products may request protocol operations only through a stable Protocol service/event/API boundary.

Do not create mandatory circular dependencies. A component without Protocol installed must fail gracefully or simply disable its optional protocol integration.

## Numbering invariants

Protocol numbering is critical data.

- Never calculate the next number only in PHP from `MAX(number) + 1`.
- Use a database transaction and a row locked per register/year counter.
- The uniqueness boundary is at least `(register_id, protocol_year, protocol_number)`.
- A protocol number, once assigned, is never reused.
- Cancellation never releases a number.
- Draft records have no definitive protocol number.
- The protocol year follows the configured Joomla/site timezone, while timestamps are stored consistently in the database.
- A failure during numbering must roll back the entire protocol operation.

Any change to numbering requires explicit concurrency and rollback review.

## Immutability and corrections

After Draft → Protocol, number, year, register and protocol timestamp are not ordinary editable fields.

Normal edit operations must not silently rewrite a protocolled record. Significant corrections must later use dedicated rectification/cancellation workflows with audit history. Do not implement destructive shortcuts.

## Joomla architecture

Target Joomla 4, 5 and 6 when technically possible. Prefer modern Joomla APIs: namespaces, MVC, service provider, dependency injection, Web Asset Manager, DatabaseInterface, Form API, Language and ACL.

Keep administrator models, controllers, views, tables and services separated. Business-critical numbering belongs in a service, not in a template or JavaScript handler.

## Security

Enforce server-side ACL for every state-changing action. Use Joomla CSRF tokens, validated/filtered input, escaped output and bound database queries for user-controlled values. Never authorize an action only in JavaScript.

Uploads, when introduced, must validate file name, MIME type, size and traversal risks. Sensitive documents must never be exposed by predictable paths.

## Database

Use `#__` for all Joomla tables. Use InnoDB and safe indexes. Updates must preserve data and configuration. Avoid destructive table recreation during normal updates.

Keep protocol entities in Protocol. Do not store protocol numbering state in Core or Documents.

## UI/UX

Use the shared xdecaro visual language and Joomla administrator toolbar when it improves clarity. Keep critical actions such as Protocol, Rectify and Cancel visually and semantically distinct from a normal Save.

Verify desktop, tablet, smartphone, light mode and dark mode. Avoid hardcoded light-only colors. Long tables must remain usable on small screens, using responsive alternatives where appropriate.

## Language and accessibility

All user-facing strings must use Joomla Language files. Preserve keyboard navigation, visible focus and sufficient contrast. Do not encode meaning using color alone.

## Packaging and releases

Use Semantic Versioning. Component and package versions must match. Installable ZIPs must contain no extra parent directory. Do not commit temporary files, IDE metadata, logs, backups or arbitrary ZIPs.

A release that changes schema must include a non-destructive SQL update marker for the same version. Do not distribute different code under the same version number.

## Regression checks

Before considering a change complete, verify as applicable:

- clean installation;
- update installation;
- Joomla 4/5/6 compatibility;
- database schema and indexes;
- duplicate-number prevention;
- transaction rollback;
- Draft → Protocol;
- ACL and CSRF;
- PHP errors/warnings;
- JavaScript Console;
- desktop/tablet/smartphone;
- light/dark mode;
- package ZIP structure.

## Working rule

When the user says “procedi”, execute directly after inspecting relevant code and dependencies. Preserve working behavior, avoid unrelated refactors and choose the safer maintainable implementation when alternatives differ materially in robustness.
