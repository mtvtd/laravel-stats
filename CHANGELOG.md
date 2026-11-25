# Changelog

All notable changes to `manage-laravel-stats` will be documented in this file.

## 2.5.0 - 2026-11-25

- Also support Guzzle 8.x (`guzzlehttp/guzzle ^8.0`) alongside 7.x, so the package installs on Laravel 13 projects that ship Guzzle 8. Laravel 9–12 keep resolving Guzzle 7 (they cap it at `^7.8.2`).

## 2.4.0 - 2026-11-06

- Also report the NPM packages in `package-lock.json` that are not in `package.json` (the ones that come along with them), as `npm-dependencies`, with the `package.json` packages that pull them in, so status.mtvtd.nl can find vulnerabilities in those as well. Needs a v2 or v3 lockfile (npm 7 and newer).

## 2.3.0 - 2026-11-05

- Report the NPM packages from `package.json` (with the versions pinned in `package-lock.json`) as `npm-packages`, so status.mtvtd.nl can show them.

## 2.2.0 - 2026-11-05

- Also report the installed packages that are not in `composer.json` (the ones that come along with them), with the `composer.json` packages that pull them in, so status.mtvtd.nl can find vulnerabilities in those as well.

## 2.1.1 - 2026-11-04

- Fix the CI: the tests no longer run in separate processes and the fake backup destination is reset between tests.

## 2.1.0 - 2026-11-04

- Report spatie/laravel-backup backups (v8–v10) to status.mtvtd.nl: with `mtvtd:laravel-stats`, and after every backup run (also failed ones, with the error). Turn off with `LARAVEL_STATS_BACKUP_REPORTING_ENABLED=false`.

## 1.0.0 - 202X-XX-XX

- initial release
