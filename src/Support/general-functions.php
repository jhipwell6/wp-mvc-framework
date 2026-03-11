<?php

/**
 * Get an item from an array or object using "dot" notation.
 *
 * @param  mixed   $target
 * @param  string  $key
 * @param  mixed   $default
 * @return mixed
 */
if ( ! function_exists('data_get') )
{
    function data_get( $target, $key, $default = null )
    {
        if ( is_null( $key ) ) return $target;
        foreach ( explode( '.', $key ) as $segment )
        {
            if ( is_array( $target ) )
            {
                if ( ! array_key_exists( $segment, $target ) )
                {
                    return $default;
                }
                $target = $target[$segment];
            }
            elseif ( $target instanceof ArrayAccess )
            {
                if ( ! isset( $target[$segment] ) )
                {
                    return $default;
                }
                $target = $target[$segment];
            }
            elseif ( is_object( $target ) )
            {
                if ( ! isset( $target->{$segment} ) )
                {
                    return $default;
                }
                $target = $target->{$segment};
            }
            else
            {
                return $default;
            }
        }
        return $target;
    }
}

/**
 * Return the first element in an array passing a given truth test.
 *
 * @param  array  $array
 * @param  callable  $callback
 * @param  mixed  $default
 * @return mixed
 */
if ( ! function_exists('array_first') )
{
    function array_first( $array, $callback = null, $default = null )
    {
        if ( is_null( $callback ) )
        {
            return count( $array ) > 0 ? reset( $array ) : null;
        }
		foreach ( $array as $key => $value )
        {
            if ( call_user_func( $callback, $key, $value ) ) return $value;
        }
        return value( $default );
    }
}

/**
 * Return the default value of the given value.
 *
 * @param  mixed  $value
 * @return mixed
 */
if ( ! function_exists('value') )
{
    function value( $value )
    {
        return $value instanceof Closure ? $value() : $value;
    }
}

/**
 * Check if an array is associative or has numeric keys
 *
 * @param  mixed  $value
 * @return mixed
 */
if ( ! function_exists('is_assoc') )
{
	function is_assoc( array $arr )
	{
		if ( array() === $arr ) return false;
		return array_keys( $arr ) !== range( 0, count( $arr ) - 1 );
	}
}

if ( ! function_exists('prepare_in') )
{
	function prepare_in( $values )
	{
		return implode( ',', array_map( function ( $value ) {
			global $wpdb;
			// Use the official prepare() function to sanitize the value.
			return $wpdb->prepare( '%s', $value );
		}, $values ) );
	}
}