<?php
/**
 * Safe outbound fetching for user-influenced URLs (sitemaps, reference pages, stock images).
 *
 * Guards against SSRF: http(s) only, no credentials in the URL, only ports 80/443,
 * the host must resolve to public IPs only, and redirects are re-validated by
 * wp_safe_remote_get(). Size and time are capped.
 *
 * Provider API calls (fixed official base URLs) do not go through this class.
 *
 * @package AISCG
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AISCG_Http {

	const DEFAULT_TIMEOUT   = 10;
	const DEFAULT_MAX_BYTES = 2097152; // 2 MB.

	/**
	 * True when the IP is a public, routable address.
	 *
	 * @param string $ip IPv4 or IPv6 literal.
	 * @return bool
	 */
	public static function is_public_ip( $ip ) {
		$packed = @inet_pton( (string) $ip ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
		if ( false === $packed ) {
			return false;
		}

		// IPv4-mapped IPv6 (::ffff:a.b.c.d): judge the embedded IPv4 address.
		if ( 16 === strlen( $packed ) && 0 === strncmp( $packed, str_repeat( "\0", 10 ) . "\xff\xff", 12 ) ) {
			$ip = inet_ntop( substr( $packed, 12 ) );
		}

		return false !== filter_var(
			$ip,
			FILTER_VALIDATE_IP,
			FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
		);
	}

	/**
	 * Resolve a host to all its IPs (A and AAAA). An IP literal returns itself.
	 *
	 * @param string $host Hostname or IP literal.
	 * @return string[]
	 */
	private static function resolve( $host ) {
		$host = trim( $host, '[]' );
		if ( false !== filter_var( $host, FILTER_VALIDATE_IP ) ) {
			return array( $host );
		}

		$ips = array();
		$v4  = gethostbynamel( $host );
		if ( is_array( $v4 ) ) {
			$ips = $v4;
		}
		if ( function_exists( 'dns_get_record' ) ) {
			$v6 = @dns_get_record( $host, DNS_AAAA ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
			if ( is_array( $v6 ) ) {
				foreach ( $v6 as $record ) {
					if ( ! empty( $record['ipv6'] ) ) {
						$ips[] = $record['ipv6'];
					}
				}
			}
		}
		return $ips;
	}

	/**
	 * Validate a URL for server-side fetching.
	 *
	 * @param string   $url           URL to check.
	 * @param string[] $allowed_hosts Optional host allowlist (suffix match). Empty = any public host.
	 * @return true|WP_Error
	 */
	public static function validate_url( $url, array $allowed_hosts = array() ) {
		$parts = wp_parse_url( (string) $url );

		if ( empty( $parts['scheme'] ) || empty( $parts['host'] ) ) {
			return new WP_Error( 'aiscg_bad_url', 'Invalid URL.' );
		}
		if ( ! in_array( strtolower( $parts['scheme'] ), array( 'http', 'https' ), true ) ) {
			return new WP_Error( 'aiscg_bad_scheme', 'Only http and https URLs are allowed.' );
		}
		if ( isset( $parts['user'] ) || isset( $parts['pass'] ) ) {
			return new WP_Error( 'aiscg_bad_url', 'URLs with credentials are not allowed.' );
		}
		if ( isset( $parts['port'] ) && ! in_array( (int) $parts['port'], array( 80, 443 ), true ) ) {
			return new WP_Error( 'aiscg_bad_port', 'Only ports 80 and 443 are allowed.' );
		}

		$host = strtolower( trim( $parts['host'], '[]' ) );

		if ( $allowed_hosts ) {
			$ok = false;
			foreach ( $allowed_hosts as $allowed ) {
				$allowed = strtolower( $allowed );
				if ( $host === $allowed || substr( $host, -strlen( $allowed ) - 1 ) === '.' . $allowed ) {
					$ok = true;
					break;
				}
			}
			if ( ! $ok ) {
				return new WP_Error( 'aiscg_host_not_allowed', 'Host is not on the allowlist.' );
			}
		}

		$ips = self::resolve( $host );
		if ( ! $ips ) {
			return new WP_Error( 'aiscg_dns', 'Host could not be resolved.' );
		}
		foreach ( $ips as $ip ) {
			if ( ! self::is_public_ip( $ip ) ) {
				return new WP_Error( 'aiscg_private_ip', 'Host resolves to a private or reserved address.' );
			}
		}

		return true;
	}

	/**
	 * Safely GET a URL.
	 *
	 * @param string   $url           URL.
	 * @param array    $args          Overrides: timeout, max_bytes, headers.
	 * @param string[] $allowed_hosts Optional host allowlist.
	 * @return array|WP_Error Response array on HTTP 2xx.
	 */
	public static function get( $url, array $args = array(), array $allowed_hosts = array() ) {
		$valid = self::validate_url( $url, $allowed_hosts );
		if ( is_wp_error( $valid ) ) {
			return $valid;
		}

		$response = wp_safe_remote_get(
			$url,
			array(
				'timeout'             => isset( $args['timeout'] ) ? (int) $args['timeout'] : self::DEFAULT_TIMEOUT,
				'redirection'         => 3,
				'limit_response_size' => isset( $args['max_bytes'] ) ? (int) $args['max_bytes'] : self::DEFAULT_MAX_BYTES,
				'headers'             => isset( $args['headers'] ) ? $args['headers'] : array(),
				'user-agent'          => 'AI-SEO-Content-Generator/' . AISCG_VERSION . '; ' . home_url( '/' ),
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code = (int) wp_remote_retrieve_response_code( $response );
		if ( $code < 200 || $code >= 300 ) {
			return new WP_Error( 'aiscg_http_status', sprintf( 'HTTP %d', $code ) );
		}

		return $response;
	}
}
