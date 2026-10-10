<?php
use PHPUnit\Framework\TestCase;

class SettingsTest extends TestCase {

	protected function setUp(): void {
		aiscg_test_reset();
	}

	public function test_sanitize_clamps_and_validates() {
		$this->assertSame( 500, AISCG_Settings::sanitize( 'max_tokens', '10' ) );
		$this->assertSame( 64000, AISCG_Settings::sanitize( 'max_tokens', '999999' ) );
		$this->assertSame( 6000, AISCG_Settings::sanitize( 'max_tokens', 'abc' ) );
		$this->assertSame( '', AISCG_Settings::sanitize( 'temperature', '' ) );
		$this->assertSame( '2', AISCG_Settings::sanitize( 'temperature', '5' ) );
		$this->assertSame( 'draft', AISCG_Settings::sanitize( 'post_status', 'private' ) );
		$this->assertSame( '1', AISCG_Settings::sanitize( 'debug', 'on' ) );
		$this->assertSame( '08:00', AISCG_Settings::sanitize( 'cron_start_time', '25:99' ) );
		$this->assertSame( array( 'https://a.test/x' ), AISCG_Settings::sanitize( 'reference_urls', "https://a.test/x\njavascript:alert(1)\nhttps://a.test/x" ) );
	}

	public function test_save_only_touches_submitted_keys_and_unchecks_booleans() {
		AISCG_Settings::ensure_defaults();
		AISCG_Settings::save( array( 'tone' => 'bold' ), array( 'fact_checking' ) );

		$this->assertSame( 'bold', AISCG_Settings::get( 'tone' ) );
		$this->assertSame( 'professional', AISCG_Settings::get( 'writing_style' ) );
		$this->assertSame( '0', AISCG_Settings::get( 'fact_checking' ) );
	}

	public function test_secret_is_stored_encrypted_and_never_in_plain_text() {
		$this->assertTrue( AISCG_Settings::save_secret( 'openai_api_key', 'sk-test-1234567890abcdef' ) );

		$stored = get_option( 'aiscg_secret_openai_api_key' );
		$this->assertStringNotContainsString( 'sk-test', $stored );
		$this->assertSame( 'sk-test-1234567890abcdef', AISCG_Settings::get_secret( 'openai_api_key' ) );
		$this->assertSame( 'stored', AISCG_Settings::secret_source( 'openai_api_key' ) );
	}

	public function test_masked_or_empty_input_does_not_overwrite_a_secret() {
		AISCG_Settings::save_secret( 'pexels_api_key', 'pexels-real-key-123456' );

		$this->assertFalse( AISCG_Settings::save_secret( 'pexels_api_key', AISCG_Settings::secret_hint( 'pexels_api_key' ) ) );
		$this->assertFalse( AISCG_Settings::save_secret( 'pexels_api_key', '' ) );
		$this->assertSame( 'pexels-real-key-123456', AISCG_Settings::get_secret( 'pexels_api_key' ) );
	}

	public function test_unknown_secret_names_are_rejected() {
		$this->assertFalse( AISCG_Settings::save_secret( 'nope', 'value' ) );
		$this->assertSame( '', AISCG_Settings::get_secret( 'nope' ) );
	}

	public function test_model_resolution_prefers_custom_then_selected_then_default() {
		AISCG_Settings::ensure_defaults();
		$this->assertSame( AISCG_Constants::default_model( 'anthropic' ), AISCG_Settings::get_model() );

		update_option( 'aiscg_model', 'some-model' );
		$this->assertSame( 'some-model', AISCG_Settings::get_model() );

		update_option( 'aiscg_custom_model', 'brand-new-model' );
		$this->assertSame( 'brand-new-model', AISCG_Settings::get_model() );
	}
}
