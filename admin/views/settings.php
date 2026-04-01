<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="aiscp-wrap">

	<?php include __DIR__ . '/partials/header.php'; ?>

	<div class="aiscp-page-content">
		<div id="aiscp-save-notice" class="aiscp-notice" style="display:none;"></div>

		<form id="aiscp-settings-form">

			<!-- Section: Content & Keywords -->
			<div class="aiscp-card">
				<div class="aiscp-card-header">
					<div>
						<h2><?php _e( 'Keywords & Targeting', 'ai-seo-content-plugin' ); ?></h2>
						<p><?php _e( 'Define what topics the AI should write about.', 'ai-seo-content-plugin' ); ?></p>
					</div>
				</div>
				<div class="aiscp-card-body">
					<div class="aiscp-field-row">
						<div class="aiscp-field">
							<label for="target_keywords"><?php _e( 'Target Keywords', 'ai-seo-content-plugin' ); ?></label>
							<textarea id="target_keywords" name="target_keywords" rows="5" placeholder="<?php esc_attr_e( "SEO marketing\nWordPress tips\nContent strategy", 'ai-seo-content-plugin' ); ?>"><?php echo esc_textarea( AISCP_Settings::get( 'target_keywords' ) ); ?></textarea>
							<span class="aiscp-field-hint"><?php _e( 'Enter one keyword or phrase per line. The AI will write posts targeting these terms.', 'ai-seo-content-plugin' ); ?></span>
						</div>
						<div class="aiscp-field">
							<label for="negative_keywords"><?php _e( 'Negative Keywords', 'ai-seo-content-plugin' ); ?></label>
							<textarea id="negative_keywords" name="negative_keywords" rows="5" placeholder="<?php esc_attr_e( "competitor brand\nunrelated topic", 'ai-seo-content-plugin' ); ?>"><?php echo esc_textarea( AISCP_Settings::get( 'negative_keywords' ) ); ?></textarea>
							<span class="aiscp-field-hint"><?php _e( 'Keywords to avoid. The AI will exclude these topics from content.', 'ai-seo-content-plugin' ); ?></span>
						</div>
					</div>

					<div class="aiscp-field-row">
						<div class="aiscp-field">
							<label for="internal_links"><?php _e( 'Internal Links for Auto-Linking', 'ai-seo-content-plugin' ); ?></label>
							<textarea id="internal_links" name="internal_links" rows="4" placeholder="<?php esc_attr_e( "https://yoursite.com/services\nhttps://yoursite.com/about", 'ai-seo-content-plugin' ); ?>"><?php echo esc_textarea( AISCP_Settings::get( 'internal_links' ) ); ?></textarea>
							<span class="aiscp-field-hint"><?php _e( 'Provide URLs for the AI to link to contextually within articles.', 'ai-seo-content-plugin' ); ?></span>
						</div>
						<div class="aiscp-field">
							<label for="sitemap_url"><?php _e( 'Sitemap URL', 'ai-seo-content-plugin' ); ?></label>
							<input type="url" id="sitemap_url" name="sitemap_url"
								placeholder="https://yoursite.com/sitemap.xml"
								value="<?php echo esc_attr( AISCP_Settings::get( 'sitemap_url' ) ); ?>">
							<span class="aiscp-field-hint"><?php _e( 'The AI will extract relevant URLs from your sitemap for internal linking.', 'ai-seo-content-plugin' ); ?></span>
						</div>
					</div>

					<div class="aiscp-field">
						<label for="competitor_domains"><?php _e( 'Competitor / News Domains for Inspiration', 'ai-seo-content-plugin' ); ?></label>
						<textarea id="competitor_domains" name="competitor_domains" rows="4" placeholder="<?php esc_attr_e( "competitor.com\nnewssite.co.il", 'ai-seo-content-plugin' ); ?>"><?php echo esc_textarea( AISCP_Settings::get( 'competitor_domains' ) ); ?></textarea>
						<span class="aiscp-field-hint"><?php _e( 'These domains will be crawled to identify trending topics and keyword opportunities.', 'ai-seo-content-plugin' ); ?></span>
					</div>
				</div>
			</div>

			<!-- Section: Writing Style -->
			<div class="aiscp-card">
				<div class="aiscp-card-header">
					<div>
						<h2><?php _e( 'Writing Style & Tone', 'ai-seo-content-plugin' ); ?></h2>
						<p><?php _e( 'Control how the AI writes your content.', 'ai-seo-content-plugin' ); ?></p>
					</div>
				</div>
				<div class="aiscp-card-body">
					<div class="aiscp-field-row">
						<div class="aiscp-field">
							<label for="writing_style"><?php _e( 'Writing Style', 'ai-seo-content-plugin' ); ?></label>
							<select id="writing_style" name="writing_style">
								<?php
								$styles = array(
									'professional' => __( 'Professional', 'ai-seo-content-plugin' ),
									'conversational' => __( 'Conversational', 'ai-seo-content-plugin' ),
									'storytelling' => __( 'Storytelling / Narrative', 'ai-seo-content-plugin' ),
									'journalistic' => __( 'Journalistic', 'ai-seo-content-plugin' ),
									'academic' => __( 'Academic', 'ai-seo-content-plugin' ),
									'casual' => __( 'Casual / Friendly', 'ai-seo-content-plugin' ),
								);
								$current = AISCP_Settings::get( 'writing_style', 'professional' );
								foreach ( $styles as $val => $label ) :
								?>
								<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $current, $val ); ?>><?php echo esc_html( $label ); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="aiscp-field">
							<label for="tone"><?php _e( 'Content Tone', 'ai-seo-content-plugin' ); ?></label>
							<select id="tone" name="tone">
								<?php
								$tones = array(
									'informative'  => __( 'Informative', 'ai-seo-content-plugin' ),
									'persuasive'   => __( 'Persuasive', 'ai-seo-content-plugin' ),
									'inspirational'=> __( 'Inspirational', 'ai-seo-content-plugin' ),
									'entertaining' => __( 'Entertaining', 'ai-seo-content-plugin' ),
									'authoritative'=> __( 'Authoritative', 'ai-seo-content-plugin' ),
									'empathetic'   => __( 'Empathetic', 'ai-seo-content-plugin' ),
								);
								$current = AISCP_Settings::get( 'tone', 'informative' );
								foreach ( $tones as $val => $label ) :
								?>
								<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $current, $val ); ?>><?php echo esc_html( $label ); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="aiscp-field">
							<label for="content_language"><?php _e( 'Primary Content Language', 'ai-seo-content-plugin' ); ?></label>
							<select id="content_language" name="content_language">
								<option value="he" <?php selected( AISCP_Settings::get( 'content_language', 'he' ), 'he' ); ?>><?php _e( 'Hebrew (עברית) — Default', 'ai-seo-content-plugin' ); ?></option>
								<option value="en" <?php selected( AISCP_Settings::get( 'content_language', 'he' ), 'en' ); ?>><?php _e( 'English', 'ai-seo-content-plugin' ); ?></option>
								<option value="ar" <?php selected( AISCP_Settings::get( 'content_language', 'he' ), 'ar' ); ?>><?php _e( 'Arabic (عربي)', 'ai-seo-content-plugin' ); ?></option>
							</select>
						</div>
					</div>

					<div class="aiscp-field-row">
						<div class="aiscp-field">
							<label for="tone_examples"><?php _e( 'Tone & Voice Examples', 'ai-seo-content-plugin' ); ?></label>
							<textarea id="tone_examples" name="tone_examples" rows="6" placeholder="<?php esc_attr_e( 'Paste 1-3 example paragraphs that represent your brand voice. The AI will match this style when generating content.', 'ai-seo-content-plugin' ); ?>"><?php echo esc_textarea( AISCP_Settings::get( 'tone_examples' ) ); ?></textarea>
							<span class="aiscp-field-hint"><?php _e( 'Provide writing samples so the AI can replicate your exact brand voice.', 'ai-seo-content-plugin' ); ?></span>
						</div>
						<div class="aiscp-field">
							<label for="content_restrictions"><?php _e( 'Content Restrictions', 'ai-seo-content-plugin' ); ?></label>
							<textarea id="content_restrictions" name="content_restrictions" rows="6" placeholder="<?php esc_attr_e( "Do not mention competitor products or brand names.
Avoid making specific pricing claims.
Do not include medical or legal advice.
Never use aggressive sales language.", 'ai-seo-content-plugin' ); ?>"><?php echo esc_textarea( AISCP_Settings::get( 'content_restrictions' ) ); ?></textarea>
							<span class="aiscp-field-hint"><?php _e( 'Specify topics, phrases, claims, or content types the AI must never include in any generated post.', 'ai-seo-content-plugin' ); ?></span>
						</div>
					</div>
				</div>
			</div>

			<!-- Section: Publishing -->
			<div class="aiscp-card">
				<div class="aiscp-card-header">
					<div>
						<h2><?php _e( 'Publishing & Generation', 'ai-seo-content-plugin' ); ?></h2>
						<p><?php _e( 'Control how and when content gets published.', 'ai-seo-content-plugin' ); ?></p>
					</div>
				</div>
				<div class="aiscp-card-body">
					<div class="aiscp-field-row">
						<div class="aiscp-field">
							<label for="publish_mode"><?php _e( 'Publishing Mode', 'ai-seo-content-plugin' ); ?></label>
							<select id="publish_mode" name="publish_mode">
								<option value="pending" <?php selected( AISCP_Settings::get( 'publish_mode', 'pending' ), 'pending' ); ?>><?php _e( 'Pending Review (Manual Approval)', 'ai-seo-content-plugin' ); ?></option>
								<option value="publish" <?php selected( AISCP_Settings::get( 'publish_mode', 'pending' ), 'publish' ); ?>><?php _e( 'Auto-Publish Immediately', 'ai-seo-content-plugin' ); ?></option>
								<option value="draft" <?php selected( AISCP_Settings::get( 'publish_mode', 'pending' ), 'draft' ); ?>><?php _e( 'Save as Draft', 'ai-seo-content-plugin' ); ?></option>
							</select>
						</div>
						<div class="aiscp-field">
							<label for="ai_model"><?php _e( 'AI Model', 'ai-seo-content-plugin' ); ?></label>
							<select id="ai_model" name="ai_model">
								<option value="openai" <?php selected( AISCP_Settings::get( 'ai_model', 'openai' ), 'openai' ); ?>><?php _e( 'OpenAI (GPT-4)', 'ai-seo-content-plugin' ); ?></option>
								<option value="claude" <?php selected( AISCP_Settings::get( 'ai_model', 'openai' ), 'claude' ); ?>><?php _e( 'Anthropic (Claude)', 'ai-seo-content-plugin' ); ?></option>
							</select>
						</div>
					</div>

					<!-- Toggles -->
					<div class="aiscp-toggles-grid">
						<label class="aiscp-toggle-label">
							<div class="toggle-info">
								<strong><?php _e( 'Auto-Generate Thumbnails', 'ai-seo-content-plugin' ); ?></strong>
								<span><?php _e( 'Automatically create AI-generated featured images for posts.', 'ai-seo-content-plugin' ); ?></span>
							</div>
							<div class="aiscp-toggle">
								<input type="checkbox" id="enable_thumbnails" name="enable_thumbnails" value="1" <?php checked( AISCP_Settings::get( 'enable_thumbnails', '1' ), '1' ); ?>>
								<span class="toggle-slider"></span>
							</div>
						</label>

						<label class="aiscp-toggle-label">
							<div class="toggle-info">
								<strong><?php _e( 'Insert Stock Images', 'ai-seo-content-plugin' ); ?></strong>
								<span><?php _e( 'Automatically insert relevant stock photos inside article body.', 'ai-seo-content-plugin' ); ?></span>
							</div>
							<div class="aiscp-toggle">
								<input type="checkbox" id="enable_stock_images" name="enable_stock_images" value="1" <?php checked( AISCP_Settings::get( 'enable_stock_images', '1' ), '1' ); ?>>
								<span class="toggle-slider"></span>
							</div>
						</label>

						<label class="aiscp-toggle-label">
							<div class="toggle-info">
								<strong><?php _e( 'Auto-Categorize Posts', 'ai-seo-content-plugin' ); ?></strong>
								<span><?php _e( 'AI will match generated posts to the most relevant blog category automatically.', 'ai-seo-content-plugin' ); ?></span>
							</div>
							<div class="aiscp-toggle">
								<input type="checkbox" id="auto_categorize" name="auto_categorize" value="1" <?php checked( AISCP_Settings::get( 'auto_categorize', '1' ), '1' ); ?>>
								<span class="toggle-slider"></span>
							</div>
						</label>

						<label class="aiscp-toggle-label">
							<div class="toggle-info">
								<strong><?php _e( 'AI Fact-Checking Layer', 'ai-seo-content-plugin' ); ?></strong>
								<span><?php _e( 'Runs a secondary check to reduce false or inaccurate information in articles.', 'ai-seo-content-plugin' ); ?></span>
							</div>
							<div class="aiscp-toggle">
								<input type="checkbox" id="fact_checking" name="fact_checking" value="1" <?php checked( AISCP_Settings::get( 'fact_checking', '1' ), '1' ); ?>>
								<span class="toggle-slider"></span>
							</div>
						</label>
					</div>

				</div>
			</div>

			<!-- Save Button -->
			<div class="aiscp-form-footer">
				<button type="submit" id="aiscp-save-btn" class="aiscp-btn aiscp-btn-primary">
					<span class="btn-text"><?php _e( 'Save Settings', 'ai-seo-content-plugin' ); ?></span>
					<span class="btn-spinner" style="display:none;">⏳</span>
				</button>
			</div>

		</form>
	</div><!-- .aiscp-page-content -->
</div><!-- .aiscp-wrap -->
