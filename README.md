# Sync Engine Integration

[![Build Status](https://travis-ci.org/usedesk/sync-engine-integration.svg?branch=master)](https://travis-ci.org/usedesk/sync-engine-integration)
[![styleci](https://styleci.io/repos/CHANGEME/shield)](https://styleci.io/repos/CHANGEME)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/usedesk/sync-engine-integration/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/usedesk/sync-engine-integration/?branch=master)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/CHANGEME/mini.png)](https://insight.sensiolabs.com/projects/CHANGEME)
[![Coverage Status](https://coveralls.io/repos/github/usedesk/sync-engine-integration/badge.svg?branch=master)](https://coveralls.io/github/usedesk/sync-engine-integration?branch=master)

[![Packagist](https://img.shields.io/packagist/v/usedesk/sync-engine-integration.svg)](https://packagist.org/packages/usedesk/sync-engine-integration)
[![Packagist](https://poser.pugx.org/usedesk/sync-engine-integration/d/total.svg)](https://packagist.org/packages/usedesk/sync-engine-integration)
[![Packagist](https://img.shields.io/packagist/l/usedesk/sync-engine-integration.svg)](https://packagist.org/packages/usedesk/sync-engine-integration)

Package description: CHANGE ME

## Installation

Install via composer
```bash
composer require usedesk/sync-engine-integration
```

### Register Service Provider

**Note! This and next step are optional if you use laravel>=5.5 with package
auto discovery feature.**

Add service provider to `config/app.php` in `providers` section
```php
usedesk\SyncEngineIntegration\ServiceProvider::class,
```

### Register Facade

Register package facade in `config/app.php` in `aliases` section
```php
usedesk\SyncEngineIntegration\Facades\SyncEngineIntegration::class,
```

### Publish Configuration File

```bash
php artisan vendor:publish --provider="usedesk\SyncEngineIntegration\ServiceProvider" --tag="config"
```

## Usage

CHANGE ME

## Security

If you discover any security related issues, please email sourinjir@gmail.com
instead of using the issue tracker.

## Credits

- [Injir](https://github.com/usedesk/sync-engine-integration)
- [All contributors](https://github.com/usedesk/sync-engine-integration/graphs/contributors)

This package is bootstrapped with the help of
[melihovv/laravel-package-generator](https://github.com/melihovv/laravel-package-generator).
