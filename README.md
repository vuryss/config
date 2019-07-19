# Config

![GitHub top language](https://img.shields.io/github/languages/top/vuryss/config.svg)
[![Build Status](https://travis-ci.org/vuryss/config.png?branch=master)](https://travis-ci.org/vuryss/config)
![Codecov](https://img.shields.io/codecov/c/gh/vuryss/config.svg)
[![CodeFactor](https://www.codefactor.io/repository/github/vuryss/config/badge)](https://www.codefactor.io/repository/github/vuryss/config)
[![Maintainability](https://api.codeclimate.com/v1/badges/ba8dd54ef4fac817498f/maintainability)](https://codeclimate.com/github/vuryss/config/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/ba8dd54ef4fac817498f/test_coverage)](https://codeclimate.com/github/vuryss/config/test_coverage)
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/51aa295b49ba4b6e9808bb8c58451c0b)](https://www.codacy.com/app/vuryss/config?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=vuryss/config&amp;utm_campaign=Badge_Grade)
![GitHub issues](https://img.shields.io/github/issues/vuryss/config.svg)
![GitHub](https://img.shields.io/github/license/vuryss/config.svg)

Very simple, compact and highly opinionated file-based configuration management library.

## The idea

The most common use case that I've used in all my projects is based on the following concepts:
1. Configuration in easy to read format, which supports nesting and can be changed by a non-technical guy.
    * For example YAML format suits the best and it's widely used for configuration
2. Configuration should be able to be extended. For example you can have the following config files, each extending the 
previous one, adding or overwriting values in it.
    * core.yaml
    * application.yaml
    * config.\<env\>.yaml
3. The whole configuration should be cached and not be parsed on each request. When application is deployed it's highly
unlikely for it's configuration to change, so parsing config files is completely unnecessary. For development it should
provide a way to parse the files on change so the development is not slowed down by the caching.

## Supported file formats

Yaml

## Based on standards
[PSR-2](https://www.php-fig.org/psr/psr-2), [PSR-4](https://www.php-fig.org/psr/psr-4), [PSR-16](https://www.php-fig.org/psr/psr-16)

## Supported cache

[PSR-16](https://www.php-fig.org/psr/psr-16)

## License
[MIT](https://choosealicense.com/licenses/mit/)
