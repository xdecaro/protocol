# Changelog

## 1.3.0 - 2026-09-09

### Added
- Optional Documents 1.2.0+ integration through the public Joomla component facade and Documents relation API.
- Public Protocol cross-product entity type `record` for document relations.
- `DocumentsIntegrationService` with attach, detach and query operations using Core `EntityReference` / `RelationReference`.
- Protocol-side ACL and record-existence validation before delegating to Documents.

### Changed
- Corrected Competitions detection in Information diagnostics to `com_xdecarocompetitions`.
- Added CI boundaries that forbid direct Protocol access to `#__decarodocuments_*` tables and require the provider-owned Documents facade.
- Preserved optional Core/Documents behavior: numbering, registers, records and audit continue to work without Documents.
- No database schema changes; `1.3.0.sql` is a Joomla schema-version marker only.

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
