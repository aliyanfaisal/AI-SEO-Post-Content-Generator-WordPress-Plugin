<?php
use PHPUnit\Framework\TestCase;

class MigratorTest extends TestCase {

	protected function setUp(): void {
		aiscg_test_reset();
	}

	public function test_old_options_are_copied_and_transformed() {
		update_option( 'aiscp_target_keywords', "seo\nwordpress" );
		update_option( 'aiscp_publish_mode', 'publish' );
		update_option( 'aiscp_content_language', 'he' );
		update_option( 'aiscp_tone_examples', 'Warm and direct' );
		update_option( 'aiscp_cron_posts_per_run', '5' );
		update_option( 'aiscp_posts_per_month', '12' );
		update_option( 'aiscp_reference_urls', array( 'https://example.com/a' ) );

		$count = AISCG_Migrator::run();

		$this->assertSame( 7, $count );
		$this->assertSame( "seo\nwordpress", get_option( 'aiscg_target_keywords' ) );
		$this->assertSame( 'publish', get_option( 'aiscg_post_status' ) );
		$this->assertSame( 'Hebrew', get_option( 'aiscg_language' ) );
		$this->assertSame( 'Warm and direct', get_option( 'aiscg_brand_voice' ) );
		$this->assertSame( 5, get_option( 'aiscg_posts_per_run' ) );
		$this->assertSame( 12, get_option( 'aiscg_monthly_cap' ) );
		$this->assertSame( array( 'https://example.com/a' ), get_option( 'aiscg_reference_urls' ) );
	}

	public function test_invalid_legacy_values_fall_back_safely() {
		update_option( 'aiscp_publish_mode', 'something-odd' );
		update_option( 'aiscp_cron_interval', 'hourly' );
		update_option( 'aiscp_content_language', 'fr' );

		AISCG_Migrator::run();

		$this->assertSame( 'draft', get_option( 'aiscg_post_status' ) );
		$this->assertSame( 'weekly', get_option( 'aiscg_cron_interval' ) );
		$this->assertSame( 'fr', get_option( 'aiscg_language' ) );
	}

	public function test_existing_new_settings_are_not_overwritten() {
		update_option( 'aiscg_tone', 'bold' );
		update_option( 'aiscp_tone', 'informative' );

		AISCG_Migrator::run();

		$this->assertSame( 'bold', get_option( 'aiscg_tone' ) );
	}

	public function test_runs_only_once() {
		update_option( 'aiscp_tone', 'calm' );
		$this->assertSame( 1, AISCG_Migrator::run() );

		update_option( 'aiscp_writing_style', 'casual' );
		$this->assertSame( 0, AISCG_Migrator::run() );
		$this->assertFalse( get_option( 'aiscg_writing_style' ) );
	}

	public function test_rekey_notice_and_old_cron_cleared_only_when_something_migrated() {
		AISCG_Migrator::run();
		$this->assertFalse( get_option( AISCG_Migrator::REKEY_FLAG ) );
		$this->assertSame( array(), $GLOBALS['aiscg_test_cleared'] );

		aiscg_test_reset();
		update_option( 'aiscp_tone', 'calm' );
		AISCG_Migrator::run();

		$this->assertSame( '1', get_option( AISCG_Migrator::REKEY_FLAG ) );
		$this->assertContains( 'aiscp_cron_main', $GLOBALS['aiscg_test_cleared'] );
	}

	public function test_defaults_do_not_clobber_migrated_values() {
		update_option( 'aiscp_tone', 'calm' );
		AISCG_Migrator::run();
		AISCG_Settings::ensure_defaults();

		$this->assertSame( 'calm', get_option( 'aiscg_tone' ) );
		$this->assertSame( 'anthropic', get_option( 'aiscg_provider' ) );
	}
}
