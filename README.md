# iO Composer Git Hooks

> [!WARNING]
> This module is deprecated. 
> You can use [Captain Hook](https://github.com/captainhookphp/captainhook) as an alternative Git hooks tool.

Composer plugin to manage git hooks from `bin/git-hooks/[hook].d` folders.

How it works:
1. Include the package as a dev dependency
2. On execution of Composer's `install` and `update` commands, symlinks will be created from `.git/hooks/[hook]` to this module's `scripts/chain-hook` script for all non-remote [git hooks](https://git-scm.com/docs/githooks#_hooks).
3. The `chain-hook` script will run the scripts found in the project's `bin/git-hooks/[hook].d` folder.

## Prerequisites
- A composer-managed project

## Install

Add the package as a dev dependency.

```bash
composer require --dev iodigital-com/composer-git-hooks
```

Add the package to the [allow-plugins section](https://getcomposer.org/doc/06-config.md#allow-plugins) of your `composer.json` file:
```json
{
    "config": {
        "allow-plugins": {
            ...
            "iodigital-com/composer-git-hooks": true
            ...
        }
    }
}
```

### Running the script manually

This package is a composer plugin and will install the githooks automatically on `composer install` and `composer update`. Should you need it however, you can run the installer manually by adding it as a composer script and executing it.

Add the following to `composer.json`:
```json
"scripts": {
    ...
    "install-git-hooks": "IODigital\\ComposerGitHooks\\ComposerPlugin::process"
    ...
},
```

Run it:
```shell script
composer run-script install-git-hooks
```

## Usage

Add project specific git-hooks to `bin/git-hooks/[hook].d`. For example:
- `bin/git-hooks/pre-commit.d/phpstan`
- `bin/git-hooks/pre-commit.d/phpcs`

All scripts (for in this case `pre-commit`) should give a 0 exit code for the whole hook to succeed.

## Contribute

Create a pull request.
This package makes use of the `composer` plugin interface. See the [composer documentation](https://getcomposer.org/doc/articles/plugins.md).
