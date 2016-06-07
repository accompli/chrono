# CHANGELOG
All notable changes to this project will be documented in this file.

This project adheres to [Semantic Versioning 2](http://semver.org/).

## [Unreleased]


## [0.3.1] - 2016-06-07
### Fixed
* Only executing a `git pull` on a branch, which allows usage of git versions lower than 1.8. (#32)
* Parsing of Subversion branches / tags. (#30)


## [0.3.0] - 2016-05-15
### Added
* Retrieval of last `ProcessExecutionResult` from the `ProcessExecutor`. (#23)
* Enforced non-interactive mode when executing Git commands. (#24)

### Removed
* Support for PHP 5.4. (#26)


## [0.2.1] - 2016-03-25
### Fixed
* Updating the current branch of a git clone with the checkout command.


## [0.2.0] - 2016-03-21
### Added
* Composer dependency compatibility with version 3 of the Symfony Process component.


## 0.1.0 - 2015-11-25
Initial Chrono VCS wrapper release with checkout support for Git and Subversion.

### Added

* Repository wrapper that is able to detect which VCS adapter to use.
* Shell process abstraction with ProcessExecutor intermediate.
* Git(Hub) Repository adapter.
* Subversion Repository adapter.


[Unreleased]: https://github.com/accompli/chrono/compare/0.3.1...HEAD
[0.3.1]: https://github.com/accompli/chrono/compare/0.3.0...0.3.1
[0.3.0]: https://github.com/accompli/chrono/compare/0.2.1...0.3.0
[0.2.1]: https://github.com/accompli/chrono/compare/0.2.0...0.2.1
[0.2.0]: https://github.com/accompli/chrono/compare/0.1.0...0.2.0
