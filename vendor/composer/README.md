# ILIAS composer guidelines 

**New dependencies MUST be approved by the Jour Fixe of the ILIAS society.**

To propose a new dependency, create a Pull Request on GitHub that contains the
proposed changes to `composer.json`, name it like "Add library XYZ" and assign
the "jour fixe"-label.

## Composer dependency management
ILIAS uses composer https://getcomposer.org/ to manage PHP dependencies. Server administrators
who manage their installation via the Git repository will have to use composer to manage
dependencies. See https://getcomposer.org/doc/ for more information.

Composer distinguishes between two different category of dependencies the 
production and dev dependencies.

The production dependencies are saved in the *require* section of the composer.json
configuration. These are used by the production code of ILIAS. Tests and other development
logic don't use this libraries directly. 

The dev dependencies are saved in the *require-dev* section of the compser.json configuration.
These are libraries and tools dedicated to aid the development process. For example
PHPUnit to run and create unit tests.

### General file structure

- The `composer.json` file is in the ILIAS main directory.
- Third party libs will be installed to `libs/vendor`.
- The `composer` command needs to be executed in the ILIAS main directory.

### Quick Command Overview

These are the commands you will need most frequently during ILIAS development.

- `composer install`: Installs third party libs for an installation.
- `composer require`: Add new lib (see details below).
- `composer update`: Update lib to new version (see details below).
- `composer dump-autoload`: Updates autoload class-map and [ILIAS artifacts](../../src/Setup/README.md).
- `composer test-php`: Runs unit tests (without required ILIAS installation).
- `composer test-php-all`: Runs unit tests (with required ILIAS installation, currently broken).
- `composer list`: List all available commands.

### Dependencies for production
- Add a new library using composer, e.g. `composer require filp/whoops`
- Document the usage and your wrapper class in `composer.json`, e.g.:
```json
"filp/whoops" : {
  "source" : "github.com/filp/whoops",
  "used_version" : "v2.1.0",
  "wrapped_by" : null,
  "added_by" : "Denis Klöpfer <denis.kloepfer@concepts-and-training.de>",
  "last_update" : "2016-03-22",
  "last_update_by" : "Jörg Lützenkirchen <luetzenkirchen@leifos.com>",
  "approved-by": "Jour Fixe",
  "approved-date": "YYYY-MM-DD"
},
```

- Run "composer install --no-dev"
- Do **not add** any files of `/vendor/composer/vendor` to the ILIAS git-repository.
- Commit changes of `composer.json` and `composer.lock`

### Dependencies for development
- Add a new library using composer, e.g. `composer require --dev phpunit/phpunit` 
- Do **not add** any files of `/vendor/composer/vendor` to the ILIAS git-repository. 
- Commit changes of `composer.json` and `composer.lock` 

### Update a single dependency
- Search the name of dependency you like to update.
- Update by using `composer update --no-dev <DEPENDENCY_NAME>`
- Do **not add** any files of `/vendor/composer/vendor` to the ILIAS git-repository.
- Commit all changes in `composer.lock`, `composer.json`

### Remove a dependency
#### Production
A production dependency can be removed with the following command:
```bash
composer remove <DEPENDENCY_NAME>
```
Afterwards all changes should be committed.
 
#### Development
A development dependency can be removed with the following command:
```bash
composer remove --dev <DEPENDENCY_NAME>
```
Afterwards all changes should be committed.

# Populate the Vendor-Directory
The vendor-directory contains code for the dependencies managed by composer and
the autoloader that allows PHP to automatically find classes. These need to be
created by composer before ILIAS will work.

To populate the vendor-directory for a production environment call in `lib\composer`:

```bash
composer install --no-dev
composer dump-autoload
```

To populate the vendor-directory for a development environment, use

```bash
composer install --dev
composer dump-autoload
```

instead.

# Create library patch

**New patches in dependencies MUST be confirmed by the Technical Board of the ILIAS society.**

To propose a new patch, create a Pull Request on GitHub that contains the proposed
changes, name it like "Patch library XYZ" and assign the "technical board"-label.

The composer plugin cweagans/composer-patches provides a way to apply patches to dependencies.
- First a patch has to be created with git which contains all the required changes. After the 
has to be moved to the .vendor/composer/patches directory.
- Second create a patch entry in the composer.json. The patch section is located under extra->patches.

For example the patch entry for tcpdf looks like this:
```json
{
	"tecnickcom/tcpdf": {
				"ILIAS TCPDF Patches": "patches/tcpdf.patch"
			}
}
``` 
First the name of the library must be specified and as child all patches which should be 
applied to the library. The example shown above has only one patch with the description "ILIAS TCPDF Patches" and the
location "patches/tcpdf.patch".

Now composer applies the patch after the specified dependency is installed.
The output will look similar as the example shown bellow:
```
Gathering patches for root package.
Gathering patches for dependencies. This might take a minute.
  - Installing tecnickcom/tcpdf (6.2.12): Downloading (100%)         
  - Applying patches for tecnickcom/tcpdf
    patches/tcpdf.patch (ILIAS TCPDF Patches)

```
