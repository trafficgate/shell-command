# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [2.1.2] - 2022-01-07
### Changed
- Allowed versions of psr/log to account for PHP 8.

## [2.1.1] - 2021-11-17
### Fixed
- Type from `int` to `?float` for `setCommandTimeout()` to match `Symfony\Component\Process\Process::setTimeout(?float)`.

## [2.1.0] - 2021-10-29

### Added
- Type hinting for both function parameters and returns.
- Support for symfony/process ~5.0.

### Changed
- Minimum supported PHP version to 7.1.3.
- PHPUnit to v8.0.

## [2.0.0] - 2018-05-23

Please update me.

## [1.0.1] - 2017-09-01

Please update me.

## [1.0.0] - 2016-10-03

Please update me.

[Unreleased]: https://github.com/trafficgate/shell-command/compare/v2.1.2...HEAD
[2.1.2]: https://github.com/trafficgate/shell-command/compare/v2.1.1...v2.1.2
[2.1.1]: https://github.com/trafficgate/shell-command/compare/v2.1.0...v2.1.1
[2.1.0]: https://github.com/trafficgate/shell-command/compare/v2.0.0...v2.1.0
[2.0.0]: https://github.com/trafficgate/shell-command/compare/v1.0.1...v2.0.0
[1.0.1]: https://github.com/trafficgate/shell-command/compare/v1.0.0...v1.0.1
[1.0.0]: https://github.com/trafficgate/shell-command/releases/tag/v1.0.0

