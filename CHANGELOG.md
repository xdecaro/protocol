# Changelog

## 1.4.0 - 2026-09-09

### Added
- Functional Documents attachment panel in the Protocol record editor.
- Saved drafts can link and unlink existing Documents records through the public Documents relation API.
- Linked document metadata and protected Documents downloads are available directly from the Protocol record view.
- Explicit UI states for unsaved records, unavailable optional integration and insufficient document access.

### Changed
- Document relations become read-only once a Protocol record is definitive; attach/detach is rejected server-side when the record is no longer `draft`.
- Document relation write tasks require a Joomla POST CSRF token and preserve the double ACL boundary: Protocol authorizes its record and Documents authorizes document relations.
- Protocol now enforces the documented Documents `1.2.1+` minimum using Joomla extension metadata before booting the provider service.
- Runtime integration CI now pins the repaired Documents 1.2.2 distribution on Joomla 5.4.8 and 6.1.3.
- Extended the cross-product contract guard to cover controller, view and template behavior while continuing to reject direct access to `#__decarodocuments_*`.
- No changes to Protocol numbering transactions, record numbering, existing audit semantics or Core public APIs.

## 1.3.0 - 2026-09-09

### Added
- Optional Documents 1.2.1+ integration through the public Joomla component facade and Documents relation API.
- Public Protocol cross-product entity type `record` for document relations.
- `DocumentsIntegrationService` with attach, detach and query operations using Core `EntityReference` / `RelationReference`.
- Protocol-side ACL and record-existence validation before delegating to Documents.
- Real Joomla 5.4.8/6.1.3 integration tests with Core, Documents and Protocol installed together.

### Changed
- Corrected Competitions detection in Information diagnostics to `com_xdecarocompetitions`.
- Added CI boundaries that forbid direct Protocol access to `#__decarodocuments_*` tables and require the provider-owned Documents facade.
- Corrected Joomla SQL manifest charset declarations to `utf8` while keeping Protocol tables defined as `utf8mb4`.
- `1.3.0.sql` now performs a non-destructive `CREATE TABLE IF NOT EXISTS` repair for Protocol-owned tables, covering affected older installations without deleting existing data.
- Preserved optional Core/Documents behavior: numbering, registers, records and audit continue to work without Documents.
- No changes to the Protocol numbering transaction, record state machine or audit semantics.

## 1.2.0 - 2026-09-09

### Changed
- Migrated optional Core consumption to the canonical `xdecaro\Core` namespace.
- Core-backed reference and shared UI features now require Core by xdecaro 1.3.0+.
- Updated Information diagnostics to detect Core 1.3.0+ through the canonical namespace.
- Added CI guards against runtime use of the deprecated `Xdecaro\Core` namespace.
- Preserved `com_decaroprotocol`, `pkg_decaroprotocol`, `Xdecaro\Component\Decaroprotocol` and all Protocol database tables.
- No changes to numbering, registers, protocol records, audit data or Draft → Protocol behavior.

## 1.1.0 - 2026-09-08

### Added
- Optional Core by xdecaro 1.1+ integration using public Core contracts only.
- Core Web Asset Manager UI primitives with safe local fallback.
- Cross-product `EntityReference` and `RelationReference` adapter for future integrations.
- Standard Information page with product, environment, connected components and diagnostics.
- Detection of Core, Forms, Documents, Courses, Competitions and Membership without mandatory dependencies.

### Changed
- Protocol administrator UI can inherit Core design tokens when Core is available.
- Version advanced to 1.1.0 to avoid distributing different 1.0.0 artifacts.

## 1.0.0 - 2026-09-08

### Added
- Initial Joomla component and package structure.
- Configurable protocol registers.
- Year-specific counters per register.
- Draft protocol records.
- Atomic Draft → Protocol workflow with transaction and row locking.
- Immutable normal editing after protocol assignment.
- Protocol audit events.
- Administrator dashboard, records list and register management foundation.
- Joomla ACL, language files, responsive/dark-mode-aware administrator styling.
- Reproducible component/package ZIP build workflow.
