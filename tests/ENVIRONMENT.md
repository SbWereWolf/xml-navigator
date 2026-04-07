# Test Environment for Standard PHP 7.3

This branch expects a standard PHP 7.3 CLI environment with the
dependencies required for XML processing, PHPUnit, PHPStan, style
fixing, and coverage.

## Required dependencies

At minimum, make sure the system provides:

- PHP 7.3 CLI
- XML / XMLReader support
- Composer
- Xdebug

Typical package names on Linux distributions look like:

- `php7.3-cli`
- `php7.3-xml`
- `php7.3-xdebug`
- `composer`

Exact package names can differ by platform or package manager, but the
runtime expectation stays the same: `php` must be a PHP 7.3 CLI binary
with `xmlreader` and `xdebug` available.

## How coverage works in this branch

Coverage is enabled only for the dedicated command:

```bash
composer test-coverage
```

That command uses:

```bash
php -d xdebug.mode=coverage ...
```

So the environment must satisfy two conditions:

1. Xdebug is already loaded by the normal CLI PHP configuration.
2. Coverage is enabled only at launch time through
   `-d xdebug.mode=coverage`.

## Quick verification

Check the loaded extensions:

```bash
php -m
php --ri xdebug
```

Then run:

```bash
composer test
composer test-coverage
composer phpstan-check
composer fix-style
```
