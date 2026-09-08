# Changelog

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
