<?php

namespace Kevinpurwito\PhpConstant;

use ReflectionClass;

class PhpConstant
{
    /*
    |--------------------------------------------------------------------------
    | Framework Agnostic Functions
    |--------------------------------------------------------------------------
    |
    | Simple functions not dependent to any framework and can be used in any PHP project.
    |
    */
    public static function all(): array
    {
        $ref = new ReflectionClass(static::class);
        $constants = $ref->getConstants();
        $arr = [];
        foreach ($constants as $constant) {
            $arr[] = $constant;
        }

        return $arr;
    }

    public static function options(): array
    {
        $arr = [];
        $constants = static::all();
        foreach ($constants as $constant) {
            $arr[$constant] = ucwords(str_replace('_', ' ', $constant));
        }

        return $arr;
    }

    public static function asString($glue = ','): string
    {
        return implode($glue, static::all());
    }

    /*
    |--------------------------------------------------------------------------
    | Laravel Specific Functions
    |--------------------------------------------------------------------------
    |
    | Functions to support [Laravel Collection](https://laravel.com/docs/collections) class.
    | Laravel Collection is a class that provides a fluent, convenient wrapper for working with arrays of data.
    |
    */

    /** @return \Illuminate\Support\Collection */
    public static function collect()
    {
        return collect(static::all());
    }

    /** @return \Illuminate\Support\Collection */
    public static function collectOptions()
    {
        return collect(static::options());
    }
}
