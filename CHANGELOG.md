# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## Unreleased

## [v2.0.1] - 2019-05-10

### Fixed

- [#229] Fix recursive tasks scan

## [v1.12.1] - 2019-05-01

### Fixed

- [#229] Fix recursive tasks scan

## [v2.0.0] - 2019-04-24

### Changed

- [#101] Throw exception on empty timezone
- [#204] More than five parts cron expressions will throw exception
- [#221] Throw `Crunz\Task\WrongTaskInstanceException` when task is not `Schedule` instance
- [#222] Make `\Crunz\Event::setProcess` private
- [#225] Bump dependencies

### Removed

- [#103] Removed `Crunz\Output\VerbosityAwareOutput` class
- [#206] Remove legacy paths recognition
- [#224] Remove `mail` transport

## [v1.12.0] - 2019-04-07

### Added

- [#178], [#217] `timezone_log` configuration option to decide whether
configured `timezone` should be used for logs, thanks to [@SadeghPM]

### Deprecated

- Using `\Crunz\Event::setProcess` is deprecated, this method was intended to be `private`,
but for some reason is `public`.
In `v2.0` this method will became private and result in exception if you call it.
- [#199] Not returning `\Crunz\Schedule` instance from your task is deprecated.
In `v2` this will result in exception.

## [v1.11.2] - 2019-03-16

### Fixed

- [#209], [#210] Composer installs crunz executable to vendor/bin instead of symlink

## [v1.11.1] - 2019-01-27

### Fixed

- [#190] Fix Crunz bin path when running closures

## [v1.11.0] - 2019-01-24

### Fixed

- [#181] Fix missing tasks source
- [#180] Fix deprecation messages not showing

### Deprecated

- Relying on tasks' source/config file recognition related to Crunz bin 

## [v1.11.0-rc.1] - 2018-12-22

### Fixed

- [#171] Fix lock storage bug
- [#173] Remove Symfony 4.2 deprecations
- [#166] Improve task collection debugging

## [v1.11.0-beta.2] - 2018-11-10

### Fixed

- [#162] Fix command error output [closes [#161]]

## [v1.11.0-beta.1] - 2018-10-23

### Added

- [#153] Add support for `symfony/lock`, Thanks to [@digilist]

### Fixed

- [#146] Make paths relative to current working directory - "cwd".
- [#158] Accept only string task number.

## [v1.10.1] - 2018-09-22

### Fixed

- [#139] Do not require `cURL` extension

## [v1.10.0] - 2018-09-22

### Fixed

- [#137] Treat whole output of failed command as "error output".

### Removed

- [#136] Remove guzzle

## [v1.9.0] - 2018-08-18

### Changed

- [#132] Improved container caching in shared servers

### Fixed

- [#131] Crunz can be used with `dragonmantank/cron-expression` package

### Deprecated

- Passing more than five parts (e.g `* * * * * *`) to `Crunz\Event::cron()`

## [v1.8.0] - 2018-08-15

### Added

- [#120] Added `--force` option to `schedule:run` command
- [#129] Add `--task` option for `schedule:run` command

### Fixed

- [#123] Spellfix: `comand` -> `command`, Thanks to [@FallDi]

## [v1.7.3] - 2018-06-15

- [#118] Undefined index: year in `vendor/lavary/crunz/src/Event.php` on line 370, Thanks to [@mindcreations]

## [v1.7.2] - 2018-06-13

### Fixed

- [#116] Do not replace Symfony's polyfills.

## [v1.7.1] - 2018-06-01

### Fixed

- [#110] Fixed config file path guessing.

## [v1.7.0] - 2018-05-27

### Added

- [#94] Added timezone option

### Deprecated
- `timezone` option in config file is now required, lack of it will result in Exception in version `2.0`

### Removed

- [#104] Remove splitCamel helper.

## [v1.6.1] - 2018-05-13

### Fixed

- [#90] Send output by email only if it is not empty.

## [v1.6.0] - 2018-04-22

### Added

- [#69] Option for allowing line breaks in logs, Thanks to [@TomasDuda]
- [#79] Introduce DI container

### Fixed

- [#43] Typos stopping email transport of 'mail', Thanks to [@m-hume]
- [#46] sendOutputTo and appendOutputTo fix, Thanks to [@m-hume]
- [#80] Fixed prevent overlapping on windows
- [#81] Fix Event::in on windows
- [#84] Make comparing date segments strict.
- [#86] Fix closure running on windows
- [#85] Fix changing user
- [#87] Remove error handler

## [v1.5.1] - 2018-04-12

### Added

- [#76] Introduce editorconfig
- [#75] Added changelog file.

### Fixed

- [#77] Fix high cpu usage


[#229]: https://github.com/lavary/crunz/pull/229
[#225]: https://github.com/lavary/crunz/pull/225
[#224]: https://github.com/lavary/crunz/pull/224
[#222]: https://github.com/lavary/crunz/pull/222
[#221]: https://github.com/lavary/crunz/pull/221
[#217]: https://github.com/lavary/crunz/pull/217
[#210]: https://github.com/lavary/crunz/pull/210
[#209]: https://github.com/lavary/crunz/pull/209
[#206]: https://github.com/lavary/crunz/pull/206
[#204]: https://github.com/lavary/crunz/pull/204
[#199]: https://github.com/lavary/crunz/pull/199
[#190]: https://github.com/lavary/crunz/pull/190
[#181]: https://github.com/lavary/crunz/pull/181
[#180]: https://github.com/lavary/crunz/pull/180
[#178]: https://github.com/lavary/crunz/pull/178
[#173]: https://github.com/lavary/crunz/pull/173  
[#171]: https://github.com/lavary/crunz/pull/171
[#166]: https://github.com/lavary/crunz/pull/166
[#164]: https://github.com/lavary/crunz/pull/164
[#163]: https://github.com/lavary/crunz/pull/163
[#162]: https://github.com/lavary/crunz/pull/162
[#161]: https://github.com/lavary/crunz/pull/161
[#159]: https://github.com/lavary/crunz/pull/159
[#158]: https://github.com/lavary/crunz/pull/158
[#157]: https://github.com/lavary/crunz/pull/157
[#155]: https://github.com/lavary/crunz/pull/155
[#154]: https://github.com/lavary/crunz/pull/154
[#153]: https://github.com/lavary/crunz/pull/153
[#151]: https://github.com/lavary/crunz/pull/151
[#150]: https://github.com/lavary/crunz/pull/150
[#149]: https://github.com/lavary/crunz/pull/149
[#148]: https://github.com/lavary/crunz/pull/148
[#147]: https://github.com/lavary/crunz/pull/147
[#146]: https://github.com/lavary/crunz/pull/146
[#142]: https://github.com/lavary/crunz/pull/142
[#141]: https://github.com/lavary/crunz/pull/141
[#140]: https://github.com/lavary/crunz/pull/140
[#139]: https://github.com/lavary/crunz/pull/139
[#138]: https://github.com/lavary/crunz/pull/138
[#137]: https://github.com/lavary/crunz/pull/137
[#136]: https://github.com/lavary/crunz/pull/136
[#133]: https://github.com/lavary/crunz/pull/133
[#132]: https://github.com/lavary/crunz/pull/132
[#131]: https://github.com/lavary/crunz/pull/131
[#130]: https://github.com/lavary/crunz/pull/130
[#129]: https://github.com/lavary/crunz/pull/129
[#123]: https://github.com/lavary/crunz/pull/123
[#120]: https://github.com/lavary/crunz/pull/120
[#119]: https://github.com/lavary/crunz/pull/119
[#118]: https://github.com/lavary/crunz/pull/118
[#117]: https://github.com/lavary/crunz/pull/117
[#116]: https://github.com/lavary/crunz/pull/116
[#113]: https://github.com/lavary/crunz/pull/113
[#112]: https://github.com/lavary/crunz/pull/112
[#111]: https://github.com/lavary/crunz/pull/111
[#110]: https://github.com/lavary/crunz/pull/110
[#109]: https://github.com/lavary/crunz/pull/109
[#107]: https://github.com/lavary/crunz/pull/107
[#105]: https://github.com/lavary/crunz/pull/105
[#104]: https://github.com/lavary/crunz/pull/104
[#103]: https://github.com/lavary/crunz/pull/103
[#102]: https://github.com/lavary/crunz/pull/102
[#101]: https://github.com/lavary/crunz/pull/101
[#100]: https://github.com/lavary/crunz/pull/100
[#98]: https://github.com/lavary/crunz/pull/98
[#97]: https://github.com/lavary/crunz/pull/97
[#96]: https://github.com/lavary/crunz/pull/96
[#95]: https://github.com/lavary/crunz/pull/95
[#94]: https://github.com/lavary/crunz/pull/94
[#92]: https://github.com/lavary/crunz/pull/92
[#90]: https://github.com/lavary/crunz/pull/90
[#89]: https://github.com/lavary/crunz/pull/89
[#88]: https://github.com/lavary/crunz/pull/88
[#87]: https://github.com/lavary/crunz/pull/87
[#86]: https://github.com/lavary/crunz/pull/86
[#85]: https://github.com/lavary/crunz/pull/85
[#84]: https://github.com/lavary/crunz/pull/84
[#82]: https://github.com/lavary/crunz/pull/82
[#81]: https://github.com/lavary/crunz/pull/81
[#80]: https://github.com/lavary/crunz/pull/80
[#79]: https://github.com/lavary/crunz/pull/79
[#77]: https://github.com/lavary/crunz/pull/77
[#76]: https://github.com/lavary/crunz/pull/76
[#75]: https://github.com/lavary/crunz/pull/75
[#74]: https://github.com/lavary/crunz/pull/74
[#73]: https://github.com/lavary/crunz/pull/73
[#72]: https://github.com/lavary/crunz/pull/72
[#69]: https://github.com/lavary/crunz/pull/69
[#50]: https://github.com/lavary/crunz/pull/50
[#46]: https://github.com/lavary/crunz/pull/46
[#43]: https://github.com/lavary/crunz/pull/43
[#36]: https://github.com/lavary/crunz/pull/36
[#25]: https://github.com/lavary/crunz/pull/25
[#24]: https://github.com/lavary/crunz/pull/24
[#23]: https://github.com/lavary/crunz/pull/23
[#17]: https://github.com/lavary/crunz/pull/17
[#16]: https://github.com/lavary/crunz/pull/16
[v1.9.0]: https://github.com/lavary/crunz/compare/v1.8.0...v1.9.0
[v1.8.0]: https://github.com/lavary/crunz/compare/v1.7.3...v1.8.0
[v1.7.3]: https://github.com/lavary/crunz/compare/v1.7.2...v1.7.3
[v1.7.2]: https://github.com/lavary/crunz/compare/v1.7.1...v1.7.2
[v1.7.1]: https://github.com/lavary/crunz/compare/v1.7.0...v1.7.1
[v1.7.0]: https://github.com/lavary/crunz/compare/v1.6.1...v1.7.0
[v1.6.1]: https://github.com/lavary/crunz/compare/v1.6.0...v1.6.1
[v1.6.0]: https://github.com/lavary/crunz/compare/v1.5.1...v1.6.0
[v1.5.1]: https://github.com/lavary/crunz/compare/v1.5.0...v1.5.1
[v1.11.2]: https://github.com/lavary/crunz/compare/v1.11.1...v1.11.2
[v1.11.1]: https://github.com/lavary/crunz/compare/v1.11.0...v1.11.1
[v1.11.0]: https://github.com/lavary/crunz/compare/v1.11.0-rc.1...v1.11.0
[v1.11.0-rc.1]: https://github.com/lavary/crunz/compare/v1.11.0-beta.2...v1.11.0-rc.1
[v1.11.0-beta.2]: https://github.com/lavary/crunz/compare/v1.11.0-beta.1...v1.11.0-beta.2
[v1.11.0-beta.1]: https://github.com/lavary/crunz/compare/v1.10.1...v1.11.0-beta.1
[v1.10.1]: https://github.com/lavary/crunz/compare/v1.10.0...v1.10.1
[v1.10.0]: https://github.com/lavary/crunz/compare/v1.9.0...v1.10.0
[v1.12.0]: https://github.com/lavary/crunz/compare/v1.11.2...v1.12.0
[v1.12.1]: https://github.com/lavary/crunz/compare/v1.12.0...v1.12.1
[v2.0.0]: https://github.com/lavary/crunz/compare/v1.12.0...v2.0.0
[v2.0.1]: https://github.com/lavary/crunz/compare/v2.0.0...v2.0.1
[@vinkla]: https://github.com/vinkla
[@timurbakarov]: https://github.com/timurbakarov
[@radarhere]: https://github.com/radarhere
[@mindcreations]: https://github.com/mindcreations
[@m-hume]: https://github.com/m-hume
[@jhoughtelin]: https://github.com/jhoughtelin
[@erfan723]: https://github.com/erfan723
[@digilist]: https://github.com/digilist
[@codermarcel]: https://github.com/codermarcel
[@arthurbarros]: https://github.com/arthurbarros
[@andrewmy]: https://github.com/andrewmy
[@TomasDuda]: https://github.com/TomasDuda
[@PhilETaylor]: https://github.com/PhilETaylor
[@FallDi]: https://github.com/FallDi
[@SadeghPM]: https://github.com/SadeghPM
