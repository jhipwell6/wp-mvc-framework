<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Infrastructure\WordPress;

use Snowberry\WpMvc\Contracts\HttpClientInterface;
use Snowberry\WpMvc\Contracts\HttpResponse;

final class WpHttpClient implements HttpClientInterface
{

	public function get( string $url, array $args = [] ): HttpResponse
	{
		$response = wp_remote_get( $url, $args );

		if ( is_wp_error( $response ) ) {
			return new HttpResponse( 500, '', [] );
		}

		return new HttpResponse(
			status: wp_remote_retrieve_response_code( $response ),
			body: wp_remote_retrieve_body( $response ),
			headers: wp_remote_retrieve_headers( $response )->getAll()
		);
	}
}
