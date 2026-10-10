<?php
/**
 * Settings storage: schema, defaults, sanitization and encrypted secrets.
 *
 * Each setting is its own option named "aiscg_{key}". Secrets are stored encrypted
 * as "aiscg_secret_{name}" and can be overridden by wp-config.php constants.
 *
 * @package AISCG
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AISCG_Settings {

	/**
	 * Setting schema: key => [ type, default, ...extras ].
	 * Types: text, textarea, key, int (min,max), float (optional, empty allowed), bool,
	 * enum (values), url, urls, sections, time.
	 *
	 * @return array<string,array>
	 */
	public static function schema() {
		return array(
			// AI provider.
			'provider'            => array( 'key', 'anthropic' ),
			'model'               => array( 'text', '' ),
			'custom_model'        => array( 'text', '' ),
			'max_tokens'          => array( 'int', 6000, 'min' => 500, 'max' => 64000 ),
			'temperature'         => array( 'float', '', 'min' => 0, 'max' => 2 ),
			'timeout'             => array( 'int', 120, 'min' => 15, 'max' => 600 ),
			'debug'               => array( 'bool', '0' ),

			// Content.
			'target_keywords'     => array( 'textarea', '' ),
			'negative_keywords'   => array( 'textarea', '' ),
			'tone'                => array( 'text', 'informative' ),
			'writing_style'       => array( 'text', 'professional' ),
			'language'            => array( 'text', 'English' ),
			'brand_voice'         => array( 'textarea', '' ),
			'content_restrictions' => array( 'textarea', '' ),
			'sections'            => array( 'sections', array() ),
			'global_instructions' => array( 'textarea', '' ),
			'global_prompt'       => array( 'textarea', '' ),
			'max_content_words'   => array( 'int', 1000, 'min' => 100, 'max' => 8000 ),
			'use_reference_posts' => array( 'bool', '0' ),
			'reference_prompt'    => array( 'textarea', '' ),
			'reference_urls'      => array( 'urls', array() ),
			'internal_links'      => array( 'textarea', '' ),
			'sitemap_url'         => array( 'url', '' ),
			'competitor_domains'  => array( 'textarea', '' ),
			'fact_checking'       => array( 'bool', '0' ),

			// Images.
			'image_provider'      => array( 'enum', 'pexels', 'values' => array( 'pexels', 'unsplash', 'shutterstock' ) ),
			'enable_thumbnails'   => array( 'bool', '1' ),
			'enable_stock_images' => array( 'bool', '1' ),

			// Publishing and schedule.
			'post_status'         => array( 'enum', 'draft', 'values' => array( 'draft', 'pending', 'publish' ) ),
			'post_author'         => array( 'int', 0, 'min' => 0, 'max' => PHP_INT_MAX ),
			'create_categories'   => array( 'bool', '1' ),
			'create_tags'         => array( 'bool', '1' ),
			'auto_categorize'     => array( 'bool', '1' ),
			'cron_enabled'        => array( 'bool', '0' ),
			'cron_interval'       => array( 'enum', 'weekly', 'values' => array( 'daily', 'every_3_days', 'weekly', 'biweekly', 'monthly' ) ),
			'cron_start_time'     => array( 'time', '08:00' ),
			'posts_per_run'       => array( 'int', 1, 'min' => 1, 'max' => 50 ),
			'monthly_cap'         => array( 'int', 30, 'min' => 0, 'max' => 10000 ),
		);
	}

	/**
	 * @param string $key Setting key.
	 * @return mixed Default value, or null for an unknown key.
	 */
	public static function default_for( $key ) {
		$schema = self::schema();
		return isset( $schema[ $key ] ) ? $schema[ $key ][1] : null;
	}

	/**
	 * @param string $key     Setting key.
	 * @param mixed  $default Used instead of the schema default when given.
	 * @return mixed
	 */
	public static function get( $key, $default = null ) {
		if ( null === $default ) {
			$default = self::default_for( $key );
		}
		return get_option( AISCG_Constants::OPTION_PREFIX . $key, $default );
	}

	/**
	 * @param string $key Setting key.
	 * @return bool
	 */
	public static function get_bool( $key ) {
		return '1' === (string) self::get( $key );
	}

	/**
	 * @param string $key Setting key.
	 * @return int
	 */
	public static function get_int( $key ) {
		return (int) self::get( $key );
	}

	/**
	 * Sections repeater, empty rows removed.
	 *
	 * @return array<int,array{name:string,prompt:string}>
	 */
	public static function get_sections() {
		$sections = self::get( 'sections' );
		if ( ! is_array( $sections ) ) {
			return array();
		}
		return array_values(
			array_filter(
				$sections,
				function ( $s ) {
					return ! empty( $s['name'] ) || ! empty( $s['prompt'] );
				}
			)
		);
	}

	/**
	 * Model to use: custom override, else the selected one, else the provider's default.
	 *
	 * @return string
	 */
	public static function get_model() {
		$custom = trim( (string) self::get( 'custom_model' ) );
		if ( '' !== $custom ) {
			return $custom;
		}
		$model = trim( (string) self::get( 'model' ) );
		return '' !== $model ? $model : AISCG_Constants::default_model( (string) self::get( 'provider' ) );
	}

	/**
	 * Author for generated posts: the saved user if it still exists, else the first administrator.
	 *
	 * @return int
	 */
	public static function get_author_id() {
		$id = self::get_int( 'post_author' );
		if ( $id > 0 && get_userdata( $id ) ) {
			return $id;
		}
		$admins = get_users(
			array(
				'role'    => 'administrator',
				'number'  => 1,
				'orderby' => 'ID',
				'fields'  => 'ID',
			)
		);
		return $admins ? (int) $admins[0] : 0;
	}

	// ---------------------------------------------------------------
	// Saving
	// ---------------------------------------------------------------

	/**
	 * Sanitize one value according to its schema entry.
	 *
	 * @param string $key   Setting key.
	 * @param mixed  $value Raw input.
	 * @return mixed Sanitized value.
	 */
	public static function sanitize( $key, $value ) {
		$schema = self::schema();
		if ( ! isset( $schema[ $key ] ) ) {
			return null;
		}
		$def  = $schema[ $key ];
		$type = $def[0];

		switch ( $type ) {
			case 'text':
				return sanitize_text_field( (string) $value );
			case 'textarea':
				return sanitize_textarea_field( (string) $value );
			case 'key':
				return sanitize_key( (string) $value );
			case 'int':
				$n = is_numeric( $value ) ? (int) $value : (int) $def[1];
				return max( $def['min'], min( $def['max'], $n ) );
			case 'float':
				if ( '' === trim( (string) $value ) || ! is_numeric( $value ) ) {
					return '';
				}
				return (string) max( $def['min'], min( $def['max'], (float) $value ) );
			case 'bool':
				return in_array( (string) $value, array( '1', 'true', 'on', 'yes' ), true ) ? '1' : '0';
			case 'enum':
				return in_array( (string) $value, $def['values'], true ) ? (string) $value : $def[1];
			case 'url':
				return esc_url_raw( trim( (string) $value ), array( 'http', 'https' ) );
			case 'urls':
				$list = is_array( $value ) ? $value : preg_split( '/\R/', (string) $value );
				$out  = array();
				foreach ( $list as $u ) {
					$u = esc_url_raw( trim( (string) $u ), array( 'http', 'https' ) );
					if ( '' !== $u ) {
						$out[] = $u;
					}
				}
				return array_values( array_unique( $out ) );
			case 'sections':
				$out = array();
				foreach ( (array) $value as $row ) {
					$name   = sanitize_text_field( isset( $row['name'] ) ? $row['name'] : '' );
					$prompt = sanitize_textarea_field( isset( $row['prompt'] ) ? $row['prompt'] : '' );
					if ( '' !== $name || '' !== $prompt ) {
						$out[] = array(
							'name'   => $name,
							'prompt' => $prompt,
						);
					}
				}
				return $out;
			case 'time':
				return preg_match( '/^([01]\d|2[0-3]):[0-5]\d$/', (string) $value ) ? (string) $value : $def[1];
		}

		return null;
	}

	/**
	 * Save settings from a form submission. Only keys present in $input are touched,
	 * except booleans, where an absent key means "unchecked" for keys listed in $bool_keys.
	 *
	 * @param array    $input     Raw input (already unslashed).
	 * @param string[] $bool_keys Boolean keys that belong to the submitted form.
	 * @return string[] Keys that were saved.
	 */
	public static function save( array $input, array $bool_keys = array() ) {
		$schema = self::schema();
		$saved  = array();

		foreach ( $bool_keys as $key ) {
			if ( isset( $schema[ $key ] ) && 'bool' === $schema[ $key ][0] && ! isset( $input[ $key ] ) ) {
				$input[ $key ] = '0';
			}
		}

		foreach ( $schema as $key => $def ) {
			if ( ! array_key_exists( $key, $input ) ) {
				continue;
			}
			update_option( AISCG_Constants::OPTION_PREFIX . $key, self::sanitize( $key, $input[ $key ] ) );
			$saved[] = $key;
		}

		// The author defaults to the user who saves the settings.
		if ( self::get_int( 'post_author' ) < 1 && function_exists( 'get_current_user_id' ) && get_current_user_id() > 0 ) {
			update_option( AISCG_Constants::OPTION_PREFIX . 'post_author', get_current_user_id() );
		}

		return $saved;
	}

	/**
	 * Write defaults for settings that have never been stored.
	 */
	public static function ensure_defaults() {
		foreach ( self::schema() as $key => $def ) {
			$name = AISCG_Constants::OPTION_PREFIX . $key;
			if ( false === get_option( $name, false ) ) {
				add_option( $name, $def[1] );
			}
		}
	}

	// ---------------------------------------------------------------
	// Secrets (API keys)
	// ---------------------------------------------------------------

	/**
	 * @param string $name One of AISCG_Constants::secret_constants() keys.
	 * @return string Plain-text secret, '' if none. A wp-config constant wins over the stored value.
	 */
	public static function get_secret( $name ) {
		$consts = AISCG_Constants::secret_constants();
		if ( ! isset( $consts[ $name ] ) ) {
			return '';
		}
		if ( defined( $consts[ $name ] ) && '' !== (string) constant( $consts[ $name ] ) ) {
			return (string) constant( $consts[ $name ] );
		}
		return AISCG_Crypto::decrypt( (string) get_option( AISCG_Constants::OPTION_PREFIX . 'secret_' . $name, '' ) );
	}

	/**
	 * @param string $name Secret name.
	 * @return string 'constant' | 'stored' | 'none'
	 */
	public static function secret_source( $name ) {
		$consts = AISCG_Constants::secret_constants();
		if ( isset( $consts[ $name ] ) && defined( $consts[ $name ] ) && '' !== (string) constant( $consts[ $name ] ) ) {
			return 'constant';
		}
		return '' !== AISCG_Crypto::decrypt( (string) get_option( AISCG_Constants::OPTION_PREFIX . 'secret_' . $name, '' ) ) ? 'stored' : 'none';
	}

	/**
	 * Masked hint for the UI. Never returns the real key.
	 *
	 * @param string $name Secret name.
	 * @return string
	 */
	public static function secret_hint( $name ) {
		return AISCG_Crypto::mask( self::get_secret( $name ) );
	}

	/**
	 * Store a secret encrypted. Ignored for empty input, masked placeholders, and
	 * secrets defined in wp-config.php.
	 *
	 * @param string $name  Secret name.
	 * @param string $value Plain-text value from the form.
	 * @return bool True if stored.
	 */
	public static function save_secret( $name, $value ) {
		$consts = AISCG_Constants::secret_constants();
		$value  = trim( (string) $value );

		if ( ! isset( $consts[ $name ] ) || '' === $value || false !== strpos( $value, '•' ) ) {
			return false;
		}
		if ( 'constant' === self::secret_source( $name ) ) {
			return false;
		}

		$encrypted = AISCG_Crypto::encrypt( sanitize_text_field( $value ) );
		if ( ! $encrypted ) {
			return false;
		}

		update_option( AISCG_Constants::OPTION_PREFIX . 'secret_' . $name, $encrypted );
		delete_option( AISCG_Constants::OPTION_PREFIX . 'notice_rekey' );
		return true;
	}

	/**
	 * @param string $name Secret name.
	 */
	public static function delete_secret( $name ) {
		delete_option( AISCG_Constants::OPTION_PREFIX . 'secret_' . $name );
	}
}
