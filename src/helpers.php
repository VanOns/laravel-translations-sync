<?php

use Illuminate\Support\Arr;

if (!function_exists('lts_array_dot')) {
    /**
     * Flatten a multi-dimensional associative array with dots.
     *
     * This function is a copy of the Laravel Arr::dot() function, except that it allows to set a custom separator.
     *
     * @param array  $array
     * @param string $prepend
     * @param string $separator
     *
     * @return array
     */
    function lts_array_dot(array $array, string $prepend = '', string $separator = '.'): array
    {
        $results = [];

        foreach ($array as $key => $value) {
            if (is_array($value) && !empty($value)) {
                $results = array_merge($results, lts_array_dot($value, $prepend . $key . $separator, $separator));
            } else {
                $results[$prepend . $key] = $value;
            }
        }

        return $results;
    }
}

if (!function_exists('lts_array_get')) {
    /**
     * Get an item from an array using "dot" notation.
     *
     * This function is a copy of the Laravel Arr::get() function, except that it allows to set a custom separator.
     *
     * @param ArrayAccess|array $array
     * @param int|string|null   $key
     * @param mixed|null        $default
     * @param string            $separator
     *
     * @return mixed
     */
    function lts_array_get(ArrayAccess|array $array, int|string|null $key, mixed $default = null, string $separator = '.'): mixed
    {
        if (! Arr::accessible($array)) {
            return value($default);
        }

        if (is_null($key)) {
            return $array;
        }

        if (Arr::exists($array, $key)) {
            return $array[$key];
        }

        if (! str_contains($key, $separator)) {
            return $array[$key] ?? value($default);
        }

        foreach (explode($separator, $key) as $segment) {
            if (Arr::accessible($array) && Arr::exists($array, $segment)) {
                $array = $array[$segment];
            } else {
                return value($default);
            }
        }

        return $array;
    }
}

if (!function_exists('lts_array_set')) {
    /**
     * Set an array item to a given value using "dot" notation.
     *
     * If no key is given to the method, the entire array will be replaced.
     *
     * This function is a copy of the Laravel Arr::set() function, except that it allows to set a custom separator.
     *
     * @param array|null      $array
     * @param int|string|null $key
     * @param mixed           $value
     * @param string          $separator
     *
     * @return array
     */
    function lts_array_set(array|null &$array, int|string|null $key, mixed $value, string $separator = '.'): array
    {
        if (is_null($key)) {
            return $array = $value;
        }

        $keys = explode($separator, $key);

        foreach ($keys as $i => $subKey) {
            if (count($keys) === 1) {
                break;
            }

            unset($keys[$i]);

            // If the key doesn't exist at this depth, we will just create an empty array
            // to hold the next value, allowing us to create the arrays to hold final
            // values at the correct depth. Then we'll keep digging into the array.
            if (! isset($array[$subKey]) || ! is_array($array[$subKey])) {
                $array[$subKey] = [];
            }

            $array = &$array[$subKey];
        }

        $array[array_shift($keys)] = $value;

        return $array;
    }
}
