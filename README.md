# MiniProgram Extension Pack

[![Latest Version on Packagist](https://img.shields.io/packagist/v/superzc/miniprogram.svg?style=flat-square)](https://packagist.org/packages/superzc/miniprogram)
[![Release Version](https://img.shields.io/badge/release-1.0.0-red.svg)](https://github.com/supermanzcj/miniprogram/releases)

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