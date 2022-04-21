# OpenEuropa Bootstrap base theme

Drupal 9 theme based on [Bootstrap 5](https://v5.getbootstrap.com/) and [UI Patterns](https://github.com/nuvoleweb/ui_patterns/).

## Requirements

This depends on the following software:

* [PHP 7.4](http://php.net/) or  [PHP 8.0](http://php.net/)

## Installation

The recommended way of installing the OpenEuropa Bootstrap Theme is via [Composer](https://www.drupal.org/docs/develop/using-composer/using-composer-to-manage-drupal-site-dependencies#managing-contributed).

```bash
composer config repositories.oe_bootstrap_theme vcs https://github.com/openeuropa/oe_bootstrap_theme
composer require openeuropa/oe_bootstrap_theme
```

### Enable the theme

In order to enable the theme in your project perform the following steps:

1. Enable the OpenEuropa Bootstrap Theme Helper module ```./vendor/bin/drush en oe_bootstrap_theme_helper```
2. Enable the OpenEuropa Bootstrap Theme and set it as default ```./vendor/bin/drush config-set system.theme default oe_bootstrap_theme```

## Development setup

You can build the development site by running the following steps:

To install required Node.js dependencies run:

```bash
npm install
```

To build the final artifacts run:

```bash
npm run build
```

This will compile all SASS and JavaScript files into self-contained assets that are exposed as [Drupal libraries][11].

In order to download all required PHP code run:

```bash
composer install
```

A post command hook (`drupal:site-setup`) is triggered automatically after `composer install`.
It will make sure that the necessary symlinks are properly setup in the development site.

* Install test site by running:

```bash
./vendor/bin/run drupal:site-install
```

Your test site will be available at `./build`.

**Please note:** project files and directories are symlinked within the test site by using the
[OpenEuropa Task Runner's Drupal project symlink](https://github.com/openeuropa/task-runner-drupal-project-symlink) command.

If you add a new file or directory in the root of the project, you need to re-run `drupal:site-setup` in order to make
sure they are be correctly symlinked.

If you don't want to re-run a full site setup for that, you can simply run:

```
$ ./vendor/bin/run drupal:symlink-project
```

### Using Docker Compose

Alternatively, you can build a development site using [Docker](https://www.docker.com/get-docker) and
[Docker Compose](https://docs.docker.com/compose/) with the provided configuration.

Docker provides the necessary services and tools such as a web server and a database server to get the site running,
regardless of your local host configuration.

#### Requirements:

- [Docker](https://www.docker.com/get-docker)
- [Docker Compose](https://docs.docker.com/compose/)

#### Configuration

By default, Docker Compose reads two files, a `docker-compose.yml` and an optional `docker-compose.override.yml` file.
By convention, the `docker-compose.yml` contains your base configuration and it's provided by default.
The override file, as its name implies, can contain configuration overrides for existing services or entirely new
services.
If a service is defined in both files, Docker Compose merges the configurations.

Find more information on Docker Compose extension mechanism on [the official Docker Compose documentation](https://docs.docker.com/compose/extends/).

#### Usage

To start, run:

```bash
docker-compose up
```

It's advised to not daemonize `docker-compose` so you can turn it off (`CTRL+C`) quickly when you're done working.
However, if you'd like to daemonize it, you have to add the flag `-d`:

```bash
docker-compose up -d
```

Then:

```bash
docker-compose exec -u node node npm install
docker-compose exec -u node node npm run build
docker-compose exec web composer install
docker-compose exec web ./vendor/bin/run drupal:site-install
```

Using default configuration, the development site files should be available in the `build` directory and the development site should be available at: [http://127.0.0.1:8080/build](http://127.0.0.1:8080/build).

#### Running the tests

To run the grumphp checks:

```bash
docker-compose exec web ./vendor/bin/grumphp run
```

To run the phpunit tests:

```bash
docker-compose exec web ./vendor/bin/phpunit
```

## Sub-theme

All the necessary files for sub-theme creation can be found in the `kits` folder,
read the related [documentation](kits/README.md)

## Patching BCL components

BCL components can be patched by using the [`patch-package`](https://www.npmjs.com/package/patch-package) NPM project.

To patch a component:

1. Modify its source files directly in `./node_modules/@openeuropa/bcl-theme-default`
2. Run:

```bash
docker-compose exec -u node node git config --global user.email "name@example.com"
docker-compose exec -u node node git config --global user.name "Name"
docker-compose exec -u node node npx patch-package @openeuropa/bcl-theme-default --patch-dir=patches/npm
```

Patches will be generated in `./patches/npm` and applied when running `npm install`.\
**Note:** generate patches **only** inside the docker container to use the same version of npm/npx.

## Contributing

Please read [the full documentation](https://github.com/openeuropa/openeuropa) for details on our code of conduct, and the process for submitting pull requests to us.

## Versioning

We use [SemVer](http://semver.org/) for versioning. For the available versions, see the [tags on this repository](https://github.com/openeuropa/oe_bootstrap_theme/tags).

## Upgrade from older versions

### Upgrade to 1.0.0-alpha8

#### Paragraphs migration

Paragraphs-related theming and functionality has been moved from the [OpenEuropa Bootstrap base theme](https://github.com/openeuropa/oe_bootstrap_theme) to [OpenEuropa Whitelabel](https://github.com/openeuropa/oe_whitelabel).

If you are using `openeuropa/oe_whitelabel`, and you want paragraphs functionality, you should upgrade it to `1.0.0-alpha6` or higher, and refer to the upgrade instructions found there.
