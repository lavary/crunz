# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Fixed

- Incompatibility with PHAR format - PR [#146](https://github.com/lavary/crunz/pull/146)

### Removed
- Removed `Crunz\Output\VerbosityAwareOutput` class - PR
[#103](https://github.com/lavary/crunz/pull/103), [@PabloKowalczyk](https://github.com/PabloKowalczyk)

## 1.10.1 - 2018-09-22

### Fixed

- Incompatibility for users without cURL extension but with enabled `allow_url_fopen` - PR [#139](https://github.com/lavary/crunz/pull/139)
by [@PabloKowalczyk](https://github.com/PabloKowalczyk)

## 1.10.0 - 2018-09-22

### Fixed

- Treat whole output of failed command as "error output", solves issue
[#135](https://github.com/lavary/crunz/issues/135),
[#134](https://github.com/lavary/crunz/issues/134) - PR [#137](https://github.com/lavary/crunz/pull/137)
by [@PabloKowalczyk](https://github.com/PabloKowalczyk)

### Removed

- Remove `guzzlehttp/guzzle` dependency and use in-house `CurlHttpClient` -
PR [#136](https://github.com/lavary/crunz/pull/136)
by [@PabloKowalczyk](https://github.com/PabloKowalczyk)

## 1.9.0 - 2018-08-18

### Changed

- Container cache directory is in `<OS-temp-dir>/.crunz/<current-user>/<crunz-version>` now,
[#128](https://github.com/lavary/crunz/issues/128) - PR [#132](https://github.com/lavary/crunz/pull/132)
by [@PabloKowalczyk](https://github.com/PabloKowalczyk) 

### Fixed

- Crunz can be used with `dragonmantank/cron-expression` package, solves issue
[#126](https://github.com/lavary/crunz/issues/126) - PR [#131](https://github.com/lavary/crunz/pull/131)
by [@PabloKowalczyk](https://github.com/PabloKowalczyk)

### Deprecated

- Passing more than five parts (e.g `* * * * * *`) to `Crunz\Event::cron` - PR [#131](https://github.com/lavary/crunz/pull/131)

## 1.8.0 - 2018-08-15

### Added

- `--force` option to `schedule:run` command.
This option allow to run all tasks regardless of configured run time,
part of issue [#11](https://github.com/lavary/crunz/issues/11) -
PR [#120](https://github.com/lavary/crunz/pull/120) by [@PabloKowalczyk](https://github.com/PabloKowalczyk)

- `--task=TASK-NUMBER` option to `schedule:run` command.
This option allow to run only one specific task,
part of issue [#11](https://github.com/lavary/crunz/issues/11) -
PR [#129](https://github.com/lavary/crunz/pull/129) by [@PabloKowalczyk](https://github.com/PabloKowalczyk)

### Changed
- Tasks in `schedule:list` is sorted by filename,
PR [#129](https://github.com/lavary/crunz/pull/129) by [@PabloKowalczyk](https://github.com/PabloKowalczyk)

## 1.7.3 - 2018-06-15

### Fixed

- Undefined index: `year` in vendor/lavary/crunz/src/Event.php on line 370, solves issue
[#41](https://github.com/lavary/crunz/issues/41) - PR [#118](https://github.com/lavary/crunz/pull/118) by [@mindcreations](https://github.com/mindcreations)

## 1.7.2 - 2018-06-13

### Fixed

- Stop `replace`ing Symfony's polyfills to avoid installation issues - PR
[#116](https://github.com/lavary/crunz/pull/116), [@PabloKowalczyk](https://github.com/PabloKowalczyk)

## 1.7.1 - 2018-06-01

### Fixed

- Project configuration file not loaded (issue [#108](https://github.com/lavary/crunz/issues/108)) - PR
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
