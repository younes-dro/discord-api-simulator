<?php
/**
 * Plugin Name: Discord API Simulator
 * Description: A plugin to simulate Discord API rate limits.
 * Version: 1.0
 * Author: Your Name
 */

define( 'DISCORD_SIMULATOR_RATE_LIMIT', 10 ); // 10 requests per second
define( 'DISCORD_SIMULATOR_RESET_TIME', 1 ); // reset time in seconds

add_action( 'rest_api_init', 'register_discord_simulator_route' );

function register_discord_simulator_route() {
	register_rest_route(
		'discord-simulator/v1',
		'/rate-limit',
		array(
			'methods'  => 'GET',
			'callback' => 'simulate_discord_rate_limit',
		)
	);
}

function simulate_discord_rate_limit() {

	$transient_key = 'discord_simulator_request_count';
	$request_count = get_transient( $transient_key );

	update_option( 'test_referar-' . microtime(), $_SERVER );

	if ( $request_count === false ) {
		set_transient( $transient_key, 1, DISCORD_SIMULATOR_RESET_TIME );
		$response = new WP_REST_Response( 'Success', 200 );
		$response->header( 'x-ratelimit-limit', DISCORD_SIMULATOR_RATE_LIMIT );
		$response->header( 'x-ratelimit-remaining', 10 );
		$response->header( 'x-ratelimit-reset', time() + DISCORD_SIMULATOR_RESET_TIME );
		$response->header( 'x-ratelimit-reset-after', mt_rand( 1, 10 ) );
		return $response;
	} elseif ( $request_count < DISCORD_SIMULATOR_RATE_LIMIT ) {
		set_transient( $transient_key, $request_count + 1, DISCORD_SIMULATOR_RESET_TIME );
		$request_count = get_transient( $transient_key ) - 1; // minus 1 to remove recent update.
		$response      = new WP_REST_Response( 'Success', 200 );
		$response->header( 'x-ratelimit-limit', DISCORD_SIMULATOR_RATE_LIMIT );
		$response->header( 'x-ratelimit-remaining', 10 - $request_count );
		$response->header( 'x-ratelimit-reset', time() + DISCORD_SIMULATOR_RESET_TIME );
		$response->header( 'x-ratelimit-reset-after', mt_rand( 1, 10 ) );
		return $response;
	} else {
		$response = new WP_REST_Response( 'Rate Limit Exceeded', 429 );
		$response->header( 'x-ratelimit-limit', DISCORD_SIMULATOR_RATE_LIMIT );
		$response->header( 'x-ratelimit-remaining', 0 );
		$response->header( 'x-ratelimit-reset', time() + DISCORD_SIMULATOR_RESET_TIME );
		$response->header( 'x-ratelimit-reset-after', mt_rand( 1, 10 ) );
		return $response;
	}
}
