# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Fixed

- Project configuration file not loaded,
solves issue [#108](https://github.com/lavary/crunz/issues/108) - PR
[#110](https://github.com/lavary/crunz/pull/110), [@PabloKowalczyk](https://github.com/PabloKowalczyk)

## 1.7.0 - 2018-05-27

### Added
- `timezone` option to config file - PR [#94](https://github.com/lavary/crunz/pull/94),
[@PabloKowalczyk](https://github.com/PabloKowalczyk)

### Deprecated
- `timezone` option in config file is now required,
lack of it will result in Exception in version `2.0`

### Removed
- `\Crunz\Utils::splitCamel()` method - PR [#104](https://github.com/lavary/crunz/pull/104),
[@PabloKowalczyk](https://github.com/PabloKowalczyk)

## 1.6.1 - 2018-05-13

### Fixed
- Crunz sends output email even if the output is empty,
solves issue [#64](https://github.com/lavary/crunz/issues/64) - PR
[#90](https://github.com/lavary/crunz/pull/90), [@PabloKowalczyk](https://github.com/PabloKowalczyk)

## 1.6.0 - 2018-04-22

### Added
- Option for allowing line breaks in logs - PR [#69](https://github.com/lavary/crunz/pull/69),
thanks to [@TomasDuda](https://github.com/TomasDuda)
- Dependency injection container - PR [#79](https://github.com/lavary/crunz/pull/79),
[@PabloKowalczyk](https://github.com/PabloKowalczyk)

### Fixed
- Typos stopping email transport of 'mail' - PR [#43](https://github.com/lavary/crunz/pull/43),
thanks to [@m-hume](https://github.com/m-hume)
- sendOutputTo and appendOutputTo, solved issues [#12](https://github.com/lavary/crunz/issues/12)
and [#38](https://github.com/lavary/crunz/issues/38) - PR [#46](https://github.com/lavary/crunz/pull/46),
thanks to [@m-hume](https://github.com/m-hume) 
- preventOverlapping on Windows - PR [#80](https://github.com/lavary/crunz/pull/80),
[@PabloKowalczyk](https://github.com/PabloKowalczyk)
- Problem with `->in(dirname)` on Windows - PR [#81](https://github.com/lavary/crunz/pull/81),
[@PabloKowalczyk](https://github.com/PabloKowalczyk)
- Task runs every minute of hour with `on()`, solves issue
[#83](https://github.com/lavary/crunz/issues/83) - PR [#84](https://github.com/lavary/crunz/pull/84)
- Closure on Windows, solved issue [#60](https://github.com/lavary/crunz/issues/60) - PR
[#86](https://github.com/lavary/crunz/pull/86), [@PabloKowalczyk](https://github.com/PabloKowalczyk)
- Handling errors by removing `Crunz\ErrorHandler`, solved issue [#65](https://github.com/lavary/crunz/issues/65) -
PR [#87](https://github.com/lavary/crunz/pull/87), [@PabloKowalczyk](https://github.com/PabloKowalczyk)

## 1.5.1 - 2018-04-12

### Added
- This changelog file
- .editorconfig file

### Fixed
- issue [#61](https://github.com/lavary/crunz/issues/61) - High CPU usage 
