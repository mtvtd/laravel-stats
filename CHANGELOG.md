# Changelog

All notable changes to `manage-laravel-stats` will be documented in this file.

## Unreleased

- Report the NPM packages from `package.json` (with the versions pinned in `package-lock.json`) as `npm-packages`, so status.mtvtd.nl can show them.

## 2.2.0 - 2026-11-05

- Also report the installed packages that are not in `composer.json` (the ones that come along with them), with the `composer.json` packages that pull them in, so status.mtvtd.nl can find vulnerabilities in those as well.

## 2.1.1 - 2026-11-04

- Fix the CI: the tests no longer run in separate processes and the fake backup destination is reset between tests.

## 2.1.0 - 2026-11-04

- Report spatie/laravel-backup backups (v8–v10) to status.mtvtd.nl: with `mtvtd:laravel-stats`, and after every backup run (also failed ones, with the error). Turn off with `LARAVEL_STATS_BACKUP_REPORTING_ENABLED=false`.

## 1.0.0 - 202X-XX-XX

- initial release
