# Laravel Extension Pack

[![Latest Version on Packagist](https://img.shields.io/packagist/v/vendor/package.svg?style=flat-square)](https://packagist.org/packages/superzc/miniprogram)
<!-- [![Build Status](https://img.shields.io/travis/vendor/package/master.svg?style=flat-square)](https://travis-ci.org/vendor/package)
[![Quality Score](https://img.shields.io/scrutinizer/g/vendor/package.svg?style=flat-square)](https://scrutinizer-ci.com/g/vendor/package) -->
[![Total Downloads](https://img.shields.io/packagist/dt/vendor/package.svg?style=flat-square)](https://packagist.org/packages/superzc/miniprogram)

This package provides additional features to the Laravel framework.


## Installation

You can install the package via composer:

```bash
composer require superzc/miniprogram
```

## Usage

调用类方法
```php
use Superzc\Miniprogram\Miniprogram;

$miniprogram = new Miniprogram();
$result = $miniprogram->doSomething();
```

使用门面
```php
use Superzc\Miniprogram\Facades\Miniprogram;

$result = Miniprogram::doSomething();
```


## Change log
暂无