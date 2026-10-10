<?php
use PHPUnit\Framework\TestCase;

class CryptoTest extends TestCase {

	public function backends() {
		$list = array();
		if ( function_exists( 'sodium_crypto_secretbox' ) ) {
			$list['sodium'] = array( AISCG_Crypto::BACKEND_SODIUM, 's1:' );
		}
		if ( in_array( 'aes-256-gcm', openssl_get_cipher_methods(), true ) ) {
			$list['gcm'] = array( AISCG_Crypto::BACKEND_GCM, 'g1:' );
		}
		return $list;
	}

	/** @dataProvider backends */
	public function test_round_trip( $backend, $prefix ) {
		$secret = 'sk-ant-api03-abcdefghijklmnopqrstuvwxyz0123456789';
		$stored = AISCG_Crypto::encrypt( $secret, $backend );

		$this->assertStringStartsWith( $prefix, $stored );
		$this->assertStringNotContainsString( $secret, $stored );
		$this->assertSame( $secret, AISCG_Crypto::decrypt( $stored ) );
	}

	/** @dataProvider backends */
	public function test_ciphertext_is_randomised( $backend ) {
		$this->assertNotSame(
			AISCG_Crypto::encrypt( 'same', $backend ),
			AISCG_Crypto::encrypt( 'same', $backend )
		);
	}

	/** @dataProvider backends */
	public function test_tampering_is_detected( $backend ) {
		$stored = AISCG_Crypto::encrypt( 'secret-value', $backend );
		$raw    = base64_decode( substr( $stored, 3 ) );
		$raw[ strlen( $raw ) - 1 ] = chr( ord( $raw[ strlen( $raw ) - 1 ] ) ^ 1 );
		$bad    = substr( $stored, 0, 3 ) . base64_encode( $raw );

		$this->assertSame( '', AISCG_Crypto::decrypt( $bad ) );
	}

	public function test_empty_and_garbage_input() {
		$this->assertSame( '', AISCG_Crypto::encrypt( '' ) );
		$this->assertSame( '', AISCG_Crypto::decrypt( '' ) );
		$this->assertSame( '', AISCG_Crypto::decrypt( 'not-encrypted' ) );
		$this->assertSame( '', AISCG_Crypto::decrypt( 's1:!!!' ) );
	}

	public function test_mask_never_reveals_the_key() {
		$key  = 'sk-ant-api03-abcdefghijklmnopqrstuvwxyz0123456789';
		$mask = AISCG_Crypto::mask( $key );

		$this->assertStringEndsWith( '6789', $mask );
		$this->assertStringNotContainsString( 'abcdefgh', $mask );
		$this->assertSame( str_repeat( '•', 8 ), AISCG_Crypto::mask( 'short' ) );
		$this->assertSame( '', AISCG_Crypto::mask( '' ) );
	}
}
