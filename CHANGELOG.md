# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## v2.0.0 - 2026-11-21

- Require PHP 8.4 or newer and Laravel 13.
- Rebuild around `PreferenceRepository` and a smaller `PreferenceDriver` contract.
- Add per-model caching, eager loading (`loadPreferences()`), and single-query upserts.
- Add casts, strict mode, `wherePreference()`, events, and `Preferences::fake()`.
- Lock `column` store writes to prevent overwrites, and keep preferences on soft-deleted models until `forceDelete()`.
- Update `model-preferences:table` to accept a model class and add a cascading foreign key.

See [docs/upgrading.md](docs/upgrading.md) for upgrade instructions.

## v1.1.0 - 2026-03-28

- Add Laravel 13 support.

## v1.0.0 - 2026-02-01

- Introduce a driver-based preferences system (column, table, shared).

## v0.1.0 - 2025-07-22

- Initial release.
