# MiniProgram Extension Pack

[![Latest Version on Packagist](https://img.shields.io/packagist/v/superzc/miniprogram.svg?style=flat-square)](https://packagist.org/packages/superzc/miniprogram)
[![Release Version](https://img.shields.io/badge/release-1.0.1-red.svg)](https://github.com/supermanzcj/miniprogram/releases)

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
use Superzc\Miniprogram\Exceptions\DefaultException as MPDefaultException;

try {
    $miniprogram = new Miniprogram();
    $result = $miniprogram->doSomething();
} catch (MPDefaultException $e) {
    return response()->json([
        'ret' => $e->getCode(),
        'msg' => $e->getMessage(),
    ]);
}
```

使用门面
```php
use Superzc\Miniprogram\Facades\Miniprogram;

try {
    $result = Miniprogram::doSomething();
} catch (MPDefaultException $e) {
    return response()->json([
        'ret' => $e->getCode(),
        'msg' => $e->getMessage(),
    ]);
}
```

## Change log
暂无