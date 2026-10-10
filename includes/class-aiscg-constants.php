<?php
/**
 * Central place for official API base URLs and the static fallback model lists.
 *
 * Model IDs live ONLY in this file. They are used when the provider's model-list
 * endpoint cannot be reached or no key is saved. Users can always type a custom
 * model ID in the settings.
 *
 * @package AISCG
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AISCG_Constants {

	const OPTION_PREFIX = 'aiscg_';

	/** Official provider API base URLs (the only hardcoded URLs in the plugin). */
	const ANTHROPIC_API_BASE    = 'https://api.anthropic.com';
	const OPENAI_API_BASE       = 'https://api.openai.com';
	const PEXELS_API_BASE       = 'https://api.pexels.com';
	const UNSPLASH_API_BASE     = 'https://api.unsplash.com';
	const SHUTTERSTOCK_API_BASE = 'https://api.shutterstock.com';

	/**
	 * Hosts images may be sideloaded from, per stock provider.
	 * Host suffix match: "pexels.com" also allows "images.pexels.com".
	 *
	 * @return array<string,string[]>
	 */
	public static function image_hosts() {
		return array(
			'pexels'       => array( 'pexels.com' ),
			'unsplash'     => array( 'unsplash.com' ),
			'shutterstock' => array( 'shutterstock.com' ),
		);
	}

	/**
	 * Static fallback model list for a provider: model ID => label.
	 *
	 * @param string $provider Provider slug.
	 * @return array<string,string>
	 */
	public static function fallback_models( $provider ) {
		$lists = array(
			'anthropic' => array(
				'claude-sonnet-5-5' => 'Claude Sonnet 5.5',
				'claude-opus-5-5'   => 'Claude Opus 5.5',
				'claude-haiku-5-5'  => 'Claude Haiku 5.5',
			),
			// To be checked against the OpenAI docs when the provider is implemented.
			'openai'    => array(
				'gpt-4o'      => 'GPT-4o',
				'gpt-4o-mini' => 'GPT-4o mini',
			),
		);

		return isset( $lists[ $provider ] ) ? $lists[ $provider ] : array();
	}

	/**
	 * Default model per provider (first entry of the fallback list).
	 *
	 * @param string $provider Provider slug.
	 * @return string
	 */
	public static function default_model( $provider ) {
		$models = self::fallback_models( $provider );
		return $models ? (string) key( $models ) : '';
	}

	/**
	 * Names of secrets and the wp-config.php constant that overrides each.
	 *
	 * @return array<string,string>
	 */
	public static function secret_constants() {
		return array(
			'anthropic_api_key'       => 'AISCG_ANTHROPIC_API_KEY',
			'openai_api_key'          => 'AISCG_OPENAI_API_KEY',
			'pexels_api_key'          => 'AISCG_PEXELS_API_KEY',
			'unsplash_api_key'        => 'AISCG_UNSPLASH_API_KEY',
			'shutterstock_api_key'    => 'AISCG_SHUTTERSTOCK_API_KEY',
			'shutterstock_api_secret' => 'AISCG_SHUTTERSTOCK_API_SECRET',
		);
	}
}
