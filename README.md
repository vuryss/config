# Config

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

## Supported cache

PSR-16

## License
[MIT](https://choosealicense.com/licenses/mit/)
