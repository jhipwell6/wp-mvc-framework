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

if (! function_exists('data_set')) {
    /**
     * Set an item on an array or object using dot notation.
     *
     * @param mixed $target
     * @param string|array|null $key
     * @param mixed $value
     * @param bool $overwrite
     * @return mixed
     */
    function data_set(&$target, $key, $value, bool $overwrite = true)
    {
        $segments = is_array($key) ? $key : explode('.', (string) $key);

        if (($segment = array_shift($segments)) === null) {
            return $target;
        }

        if ($segments === []) {
            if (is_array($target)) {
                if ($overwrite || ! array_key_exists($segment, $target)) {
                    $target[$segment] = value($value);
                }

                return $target;
            }

            if (is_object($target)) {
                if ($overwrite || ! isset($target->{$segment})) {
                    $target->{$segment} = value($value);
                }
            }

            return $target;
        }

        if (is_array($target)) {
            if (! array_key_exists($segment, $target) || (! is_array($target[$segment]) && ! is_object($target[$segment]))) {
                $target[$segment] = [];
            }

            data_set($target[$segment], $segments, $value, $overwrite);
            return $target;
        }

        if (is_object($target)) {
            if (! isset($target->{$segment}) || (! is_array($target->{$segment}) && ! is_object($target->{$segment}))) {
                $target->{$segment} = [];
            }

            data_set($target->{$segment}, $segments, $value, $overwrite);
        }

        return $target;
    }
}

if (! function_exists('data_has')) {
    /**
     * Check if an item exists in an array or object using dot notation.
     *
     * @param mixed $target
     * @param string|array|null $key
     * @return bool
     */
    function data_has($target, $key): bool
    {
        $keys = is_array($key) ? $key : [$key];

        if ($keys === []) {
            return false;
        }

        foreach ($keys as $item) {
            $subTarget = $target;

            if ($item === null) {
                return false;
            }

            foreach (explode('.', (string) $item) as $segment) {
                if (is_array($subTarget) && array_key_exists($segment, $subTarget)) {
                    $subTarget = $subTarget[$segment];
                    continue;
                }

                if ($subTarget instanceof ArrayAccess && $subTarget->offsetExists($segment)) {
                    $subTarget = $subTarget[$segment];
                    continue;
                }

                if (is_object($subTarget) && (isset($subTarget->{$segment}) || property_exists($subTarget, $segment))) {
                    $subTarget = $subTarget->{$segment};
                    continue;
                }

                return false;
            }
        }

        return true;
    }
}

if (! function_exists('data_forget')) {
    /**
     * Remove one or many items from an array using dot notation.
     *
     * @param array $target
     * @param string|array $keys
     * @return void
     */
    function data_forget(array &$target, $keys): void
    {
        $keys = (array) $keys;

        foreach ($keys as $key) {
            $segments = explode('.', (string) $key);
            $subTarget =& $target;

            while (count($segments) > 1) {
                $segment = array_shift($segments);

                if (! isset($subTarget[$segment]) || ! is_array($subTarget[$segment])) {
                    continue 2;
                }

                $subTarget =& $subTarget[$segment];
            }

            unset($subTarget[array_shift($segments)]);
        }
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

if (! function_exists('array_last')) {
    /**
     * Return the last element in an array passing a given truth test.
     *
     * @param array $array
     * @param callable|null $callback
     * @param mixed $default
     * @return mixed
     */
    function array_last(array $array, callable $callback = null, $default = null)
    {
        if ($callback === null) {
            if ($array === []) {
                return value($default);
            }

            return end($array);
        }

        return array_first(array_reverse($array, true), $callback, $default);
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

if (! function_exists('array_where')) {
    /**
     * Filter the array using the given callback.
     *
     * @param array $array
     * @param callable $callback
     * @return array
     */
    function array_where(array $array, callable $callback): array
    {
        return array_filter($array, $callback, ARRAY_FILTER_USE_BOTH);
    }
}

if (! function_exists('array_map_with_keys')) {
    /**
     * Run a map over each of the items and flatten into a single array.
     *
     * @param array $array
     * @param callable $callback
     * @return array
     */
    function array_map_with_keys(array $array, callable $callback): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            $assoc = $callback($value, $key);

            foreach ($assoc as $mapKey => $mapValue) {
                $result[$mapKey] = $mapValue;
            }
        }

        return $result;
    }
}

if (! function_exists('array_pluck')) {
    /**
     * Pluck an array of values from an array.
     *
     * @param array $array
     * @param string|array $value
     * @param string|array|null $key
     * @return array
     */
    function array_pluck(array $array, $value, $key = null): array
    {
        $results = [];

        foreach ($array as $item) {
            $itemValue = data_get($item, $value);

            if ($key === null) {
                $results[] = $itemValue;
                continue;
            }

            $itemKey = data_get($item, $key);
            $results[$itemKey] = $itemValue;
        }

        return $results;
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
