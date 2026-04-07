# PHP Compatibility

This repository keeps separate maintenance branches for runtime
compatibility with different PHP lines.

## Branch map

| Branch | Supported PHP versions | Notes |
| --- | --- | --- |
| `main` | PHP 8.4+ | Latest development line. |
| `php8.3` | PHP 8.3+ | First downgraded runtime branch. |
| `php8.0` | PHP 8.0 to 8.2 | Covers `php8.2` and `php8.1`, which do not need separate runtime branches. |
| `php7.4` | PHP 7.4 | Dedicated branch because runtime syntax changes from PHP 8.x are required. |
| `php7.3` | PHP 7.3 | Dedicated branch because typed properties and arrow functions must be removed. |
| `php7.0` | PHP 7.0 to 7.2 | Covers `php7.2` and `php7.1`, which do not need separate runtime branches. |
| `php5.6` | PHP 5.6 | Lowest supported line. |

## Compatibility policy

- Each branch declares the PHP versions it supports in `composer.json`.
- A PHP version is considered supported only if the library runs there
  without `deprecated` warnings.
- When a lower PHP line needs runtime code changes in `src/`, a
  dedicated branch is created.
- When only tooling, tests, or Docker need changes, the intermediate
  PHP line is covered by the nearest lower real runtime branch instead
  of getting its own branch.

## Verification policy

Each real runtime branch is verified with Docker on its target PHP
version and must keep:

- passing unit and integration tests;
- passing coverage generation;
- passing style fixes;
- 100% coverage for the functional runtime code.

The `jsonSerialize()` helper is not treated as part of the functional
contract of the library and is excluded from compatibility decisions.
