<?php

declare(strict_types=1);

if (! function_exists('value')) {
    /**
     * Return the default value of the given value.
     *
     * @param mixed $value
     * @return mixed
     */
    function value($value)
    {
        return $value instanceof Closure ? $value() : $value;
    }
}

if (! function_exists('data_get')) {
    /**
     * Get an item from an array or object using dot notation.
     *
     * @param mixed $target
     * @param string|array|null $key
     * @param mixed $default
     * @return mixed
     */
    function data_get($target, $key, $default = null)
    {
        if ($key === null) {
            return $target;
        }

        $segments = is_array($key) ? $key : explode('.', (string) $key);

        foreach ($segments as $segment) {
            if (is_array($target)) {
                if (! array_key_exists($segment, $target)) {
                    return value($default);
                }

                $target = $target[$segment];
                continue;
            }

            if ($target instanceof ArrayAccess) {
                if (! $target->offsetExists($segment)) {
                    return value($default);
                }

                $target = $target[$segment];
                continue;
            }

            if (is_object($target)) {
                if (! isset($target->{$segment}) && ! property_exists($target, (string) $segment)) {
                    return value($default);
                }

                $target = $target->{$segment};
                continue;
            }

            return value($default);
        }

        return $target;
    }
}

if (! function_exists('array_first')) {
    /**
     * Return the first element in an array passing a given truth test.
     *
     * @param array $array
     * @param callable|null $callback
     * @param mixed $default
     * @return mixed
     */
    function array_first(array $array, callable $callback = null, $default = null)
    {
        if ($callback === null) {
            foreach ($array as $value) {
                return $value;
            }

            return value($default);
        }

        foreach ($array as $key => $value) {
            if ($callback($value, $key)) {
                return $value;
            }
        }

        return value($default);
    }
}

if (! function_exists('array_get')) {
    /**
     * Get an item from an array using dot notation.
     *
     * @param array $array
     * @param string|int|null $key
     * @param mixed $default
     * @return mixed
     */
    function array_get(array $array, $key, $default = null)
    {
        if ($key === null) {
            return $array;
        }

        return data_get($array, (string) $key, $default);
    }
}

if (! function_exists('array_has')) {
    /**
     * Determine if the given key exists in the provided array.
     *
     * @param array $array
     * @param string|array $keys
     * @return bool
     */
    function array_has(array $array, $keys): bool
    {
        $keys = (array) $keys;

        if ($keys === []) {
            return false;
        }

        foreach ($keys as $key) {
            $subArray = $array;
            $segments = explode('.', (string) $key);

            foreach ($segments as $segment) {
                if (! is_array($subArray) || ! array_key_exists($segment, $subArray)) {
                    return false;
                }

                $subArray = $subArray[$segment];
            }
        }

        return true;
    }
}

if (! function_exists('blank')) {
    /**
     * Determine if a value is "blank".
     *
     * @param mixed $value
     * @return bool
     */
    function blank($value): bool
    {
        if ($value === null) {
            return true;
        }

        if (is_string($value)) {
            return trim($value) === '';
        }

        if (is_numeric($value) || is_bool($value)) {
            return false;
        }

        if (is_array($value)) {
            return $value === [];
        }

        return empty($value);
    }
}

if (! function_exists('filled')) {
    /**
     * Determine if a value is not "blank".
     *
     * @param mixed $value
     * @return bool
     */
    function filled($value): bool
    {
        return ! blank($value);
    }
}
