# Test Environment for Standard PHP 8.3

This repository expects a standard PHP 8.3 CLI environment with the
dependencies required for XML processing, PHPUnit, style fixing, and
coverage.

## Required dependencies

At minimum, make sure the system provides:

- PHP 8.3 CLI
- XML / XMLReader support
- Composer
- Xdebug

Typical package names on Linux distributions look like:

- `php8.3-cli`
- `php8.3-xml`
- `php8.3-xdebug`
- `composer`

Exact package names can differ by platform or package manager, but the
runtime expectation stays the same: `php` must be a PHP 8.3 CLI binary
with `xmlreader` and `xdebug` available.

## How coverage works in this repository

The repository does not require a global coverage mode in `ini` files.
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


## Platform-specific preparation

Any platform-specific work to make CLI PHP load Xdebug should be done
outside the repository commands.

### Linux

- install the PHP 8.3 CLI, XML, and Xdebug packages provided by your
  distribution or PPA;
- enable the Xdebug extension for CLI PHP using the standard mechanism
  of that distribution;
- verify that `php --ri xdebug` works before running coverage.

### macOS

- install PHP 8.3 and Xdebug using your package manager or the standard
  PHP distribution you use locally;
- make sure the CLI `php` command loads Xdebug in its default config;
- verify with `php --ri xdebug`.

### Windows

- use a PHP 8.3 build that includes or supports Xdebug;
- enable Xdebug in the normal CLI PHP configuration;
- verify with `php --ri xdebug`.

The repository assumes that by the time you run tests, these steps are
already complete.

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
composer fix-style
```

If `composer test-coverage` fails because Xdebug is missing, fix the
platform configuration first instead of changing the test command.
