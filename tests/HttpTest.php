<?php
use PHPUnit\Framework\TestCase;

class HttpTest extends TestCase {

	public function private_ips() {
		return array(
			'loopback v4'     => array( '127.0.0.1' ),
			'private 10'      => array( '10.0.0.5' ),
			'private 192'     => array( '192.168.1.1' ),
			'private 172'     => array( '172.16.0.1' ),
			'link-local/meta' => array( '169.254.169.254' ),
			'loopback v6'     => array( '::1' ),
			'mapped loopback' => array( '::ffff:127.0.0.1' ),
			'unique local v6' => array( 'fd00::1' ),
			'garbage'         => array( 'not-an-ip' ),
		);
	}

	/** @dataProvider private_ips */
	public function test_non_public_ips_are_rejected( $ip ) {
		$this->assertFalse( AISCG_Http::is_public_ip( $ip ) );
	}

	public function test_public_ips_are_accepted() {
		$this->assertTrue( AISCG_Http::is_public_ip( '8.8.8.8' ) );
		$this->assertTrue( AISCG_Http::is_public_ip( '2606:4700:4700::1111' ) );
	}

	public function bad_urls() {
		return array(
			'ftp'         => array( 'ftp://example.com/file', 'aiscg_bad_scheme' ),
			'file'        => array( 'file:///etc/passwd', 'aiscg_bad_url' ),
			'credentials' => array( 'http://user:pass@8.8.8.8/', 'aiscg_bad_url' ),
			'odd port'    => array( 'http://8.8.8.8:6379/', 'aiscg_bad_port' ),
			'loopback'    => array( 'http://127.0.0.1/admin', 'aiscg_private_ip' ),
			'metadata'    => array( 'http://169.254.169.254/latest/meta-data/', 'aiscg_private_ip' ),
			'v6 loopback' => array( 'http://[::1]/', 'aiscg_private_ip' ),
			'no host'     => array( 'http:///path', 'aiscg_bad_url' ),
		);
	}

	/** @dataProvider bad_urls */
	public function test_unsafe_urls_are_rejected( $url, $code ) {
		$result = AISCG_Http::validate_url( $url );
		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( $code, $result->get_error_code() );
	}

	public function test_public_ip_url_is_accepted() {
		$this->assertTrue( AISCG_Http::validate_url( 'https://8.8.8.8/sitemap.xml' ) );
	}

	public function test_host_allowlist_uses_suffix_match() {
		$allowed = array( 'pexels.com' );

		$this->assertSame( 'aiscg_host_not_allowed', AISCG_Http::validate_url( 'https://evilpexels.com/x.jpg', $allowed )->get_error_code() );
		$this->assertSame( 'aiscg_host_not_allowed', AISCG_Http::validate_url( 'https://pexels.com.evil.test/x.jpg', $allowed )->get_error_code() );
	}
}
