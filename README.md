# Chrono

[![Latest Version on Packagist][ico-version]][link-version]
[![Software License][ico-license]][link-license]
[![Build Status][ico-build]][link-build]
[![Coverage Status][ico-coverage]][link-coverage]
[![SensioLabsInsight][ico-security]][link-security]
[![StyleCI][ico-code-style]][link-code-style]

Version Control System wrapper for PHP.

## Installation using Composer

Run the following command to add the package to your composer.json:

``` bash
$ composer require accompli/chrono:^0.1
```

## Usage
The `Repository` class will detect which registered VCS adapter to use based on the URL of the repository.

```php
<?php

use Accompli\Chrono\Process\ProcessExecutor;
use Accompli\Chrono\Repository;

$repository = new Repository('https://github.com/accompli/chrono.git', '/vcs/checkout/directory', new ProcessExecutor());

$repository->getBranches(); // Returns an array with all available branches in the repository.

$repository->getTags(); // Returns an array with all available tags in the repository.

$repository->checkout('0.1.0'); // Creates or updates a checkout of a tag or branch in the repository directory.

```

#### Versioning
Chrono uses [Semantic Versioning 2][link-semver] for new versions.

## Credits and acknowledgements

- [Niels Nijens][link-author]
- [All Contributors][link-contributors]
- Inspired by the VCS drivers of Composer.

## License

Chrono is licensed under the MIT License. Please see the [LICENSE file][link-license] for details.

[ico-version]: https://img.shields.io/packagist/v/accompli/chrono.svg
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg
[ico-build]: https://travis-ci.org/accompli/chrono.svg?branch=master
[ico-coverage]: https://coveralls.io/repos/accompli/chrono/badge.svg?branch=master
[ico-security]: https://img.shields.io/sensiolabs/i/d0233585-0d0c-474c-ab9f-e35a7e5199c7.svg
[ico-code-style]: https://styleci.io/repos/45839394/shield?style=flat

[link-version]: https://packagist.org/packages/accompli/chrono
[link-license]: LICENSE.md
[link-build]: https://travis-ci.org/accompli/chrono
[link-coverage]: https://coveralls.io/r/accompli/chrono?branch=master
[link-security]: https://insight.sensiolabs.com/projects/d0233585-0d0c-474c-ab9f-e35a7e5199c7
[link-code-style]: https://styleci.io/repos/45839394
[link-semver]: http://semver.org/
[link-author]: https://github.com/niels-nijens
[link-contributors]: https://github.com/accompli/chrono/contributors
