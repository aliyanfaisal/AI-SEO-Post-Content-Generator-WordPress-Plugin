<?php
/**
 * Authenticated encryption for API keys stored in the database.
 *
 * Uses libsodium secretbox when available, otherwise AES-256-GCM. Both are
 * authenticated, so tampered ciphertext fails to decrypt instead of returning garbage.
 * The key is derived from the site's WordPress salts.
 *
 * Stored format: "s1:" . base64(nonce . ciphertext)           (sodium)
 *                "g1:" . base64(iv . tag . ciphertext)        (AES-256-GCM)
 *
 * @package AISCG
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AISCG_Crypto {

	const BACKEND_SODIUM = 'sodium';
	const BACKEND_GCM    = 'gcm';

	/**
	 * Best available backend, or '' if none.
	 *
	 * @return string
	 */
	public static function backend() {
		if ( function_exists( 'sodium_crypto_secretbox' ) && function_exists( 'random_bytes' ) ) {
			return self::BACKEND_SODIUM;
		}
		if ( function_exists( 'openssl_encrypt' ) && in_array( 'aes-256-gcm', openssl_get_cipher_methods(), true ) ) {
			return self::BACKEND_GCM;
		}
		return '';
	}

	/**
	 * 32-byte key derived from WordPress salts.
	 *
	 * @return string Raw binary.
	 */
	private static function key() {
		return hash( 'sha256', 'aiscg|' . wp_salt( 'auth' ) . '|' . wp_salt( 'secure_auth' ), true );
	}

	/**
	 * @param string      $plain   Plain text.
	 * @param string|null $backend Force a backend (used by tests). Defaults to the best available.
	 * @return string|false Encrypted string, '' for empty input, false if no cipher is available.
	 */
	public static function encrypt( $plain, $backend = null ) {
		$plain = (string) $plain;
		if ( '' === $plain ) {
			return '';
		}

		$backend = $backend ? $backend : self::backend();

		if ( self::BACKEND_SODIUM === $backend ) {
			$nonce = random_bytes( SODIUM_CRYPTO_SECRETBOX_NONCEBYTES );
			$box   = sodium_crypto_secretbox( $plain, $nonce, self::key() );
			return 's1:' . base64_encode( $nonce . $box );
		}

		if ( self::BACKEND_GCM === $backend ) {
			$iv     = random_bytes( 12 );
			$tag    = '';
			$cipher = openssl_encrypt( $plain, 'aes-256-gcm', self::key(), OPENSSL_RAW_DATA, $iv, $tag, '', 16 );
			if ( false === $cipher ) {
				return false;
			}
			return 'g1:' . base64_encode( $iv . $tag . $cipher );
		}

		return false;
	}

	/**
	 * @param string $stored Value produced by encrypt().
	 * @return string Plain text, or '' if empty, malformed, tampered, or the salts changed.
	 */
	public static function decrypt( $stored ) {
		$stored = (string) $stored;
		if ( strlen( $stored ) < 4 ) {
			return '';
		}

		$prefix = substr( $stored, 0, 3 );
		$raw    = base64_decode( substr( $stored, 3 ), true );
		if ( false === $raw ) {
			return '';
		}

		if ( 's1:' === $prefix && function_exists( 'sodium_crypto_secretbox_open' ) ) {
			$nonce_len = SODIUM_CRYPTO_SECRETBOX_NONCEBYTES;
			if ( strlen( $raw ) <= $nonce_len ) {
				return '';
			}
			$plain = sodium_crypto_secretbox_open( substr( $raw, $nonce_len ), substr( $raw, 0, $nonce_len ), self::key() );
			return false === $plain ? '' : $plain;
		}

		if ( 'g1:' === $prefix && function_exists( 'openssl_decrypt' ) ) {
			if ( strlen( $raw ) <= 28 ) {
				return '';
			}
			$plain = openssl_decrypt( substr( $raw, 28 ), 'aes-256-gcm', self::key(), OPENSSL_RAW_DATA, substr( $raw, 0, 12 ), substr( $raw, 12, 16 ) );
			return false === $plain ? '' : $plain;
		}

		return '';
	}

	/**
	 * Masked form for display: bullets plus the last 4 characters.
	 *
	 * @param string $plain Plain text.
	 * @return string
	 */
	public static function mask( $plain ) {
		$plain = (string) $plain;
		if ( '' === $plain ) {
			return '';
		}
		$tail = strlen( $plain ) > 12 ? substr( $plain, -4 ) : '';
		return str_repeat( '•', 8 ) . $tail;
	}
}
