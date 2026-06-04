![Screenshot](https://raw.githubusercontent.com/auxfin/mfi/master/art/screenshot.jpg)

# Mfi

[![Latest Stable Version](https://poser.pugx.org/auxfin/mfi/version.svg)](https://packagist.org/packages/auxfin/mfi)
[![License](https://poser.pugx.org/auxfin/mfi/license.svg)](https://packagist.org/packages/auxfin/mfi)
[![Downloads](https://poser.pugx.org/auxfin/mfi/d/total.svg)](https://packagist.org/packages/auxfin/mfi)

mfi and wallet of umva

## Installation

```bash
composer require auxfin/mfi
```
after install your package please run this command

```bash
php artisan mfi:install
```



## Publish Assets

you can publish config file by use this command

```bash
php artisan vendor:publish --tag="mfi-config"
```

you can publish views file by use this command

```bash
php artisan vendor:publish --tag="mfi-views"
```

you can publish languages file by use this command

```bash
php artisan vendor:publish --tag="mfi-lang"
```

you can publish migrations file by use this command

```bash
php artisan vendor:publish --tag="mfi-migrations"
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Security

Please see [SECURITY](SECURITY.md) for more information about security.

## Credits

- [Sabin Maharjan](mailto:sabin.maharjan@auxfin.com)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
