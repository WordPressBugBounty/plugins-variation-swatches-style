<?php
/**
 * Admin menu & settings – free version (Pro-style UI)
 */

if ( ! class_exists( 'ATA_WC_Variation_Swatches_Options' ) ) :

class ATA_WC_Variation_Swatches_Options {

	protected static $instance = null;
	private $settings_api;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __construct() {
		require_once ATA_WCVS_PATH . 'inc/class.settings-api.php';
		$this->settings_api = new WeDevs_Settings_API_Swatches();
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ) );
	}

	public function admin_menu() {
		add_menu_page(
			__( 'Variation Swatches', 'variation-swatches-style' ),
			__( 'Variation Swatches', 'variation-swatches-style' ),
			'manage_woocommerce',
			'ata-variation-swatches',
			array( $this, 'render_dashboard' ),
			'dashicons-admin-customizer',
			56
		);

		add_submenu_page( 'ata-variation-swatches', __( 'Dashboard', 'variation-swatches-style' ), __( 'Dashboard', 'variation-swatches-style' ), 'manage_woocommerce', 'ata-variation-swatches', array( $this, 'render_dashboard' ) );

		add_submenu_page( 'ata-variation-swatches', __( 'Swatches', 'variation-swatches-style' ), __( 'Swatches', 'variation-swatches-style' ), 'manage_woocommerce', 'edit.php?post_type=product&page=product_attributes' );

		add_submenu_page( 'ata-variation-swatches', __( 'Settings', 'variation-swatches-style' ), __( 'Settings', 'variation-swatches-style' ), 'manage_woocommerce', 'ata-swatches-settings', array( $this, 'render_settings_page' ) );

		add_submenu_page( 'ata-variation-swatches', __( 'Help & Support', 'variation-swatches-style' ), __( 'Help & Support', 'variation-swatches-style' ), 'manage_woocommerce', 'ata-swatches-help', array( $this, 'render_help_page' ) );
	}

	public function admin_init() {
		$this->settings_api->set_sections( $this->get_settings_sections() );
		$this->settings_api->set_fields( $this->get_settings_fields() );
		$this->settings_api->admin_init();
	}

	public function admin_assets( $hook ) {
		if ( strpos( $hook, 'ata-variation-swatches' ) === false && strpos( $hook, 'ata-swatches' ) === false ) {
			return;
		}
		wp_enqueue_style( 'atawc-admin', ATA_WCVS_URL . 'assets/css/admin.css', array( 'wp-color-picker' ), ATA_WCVS_VERSION );
		wp_enqueue_script( 'atawc-admin', ATA_WCVS_URL . 'assets/js/admin.js', array( 'jquery', 'wp-color-picker', 'wp-util' ), ATA_WCVS_VERSION, true );
		wp_add_inline_style( 'atawc-admin', $this->admin_css() );
	}

	private function admin_css() {
		return '
		.ata-swatches-wrap{max-width:1100px}
		.ata-card{background:#fff;border:1px solid #c3c4c7;border-radius:8px;padding:20px 24px;margin-bottom:20px;box-shadow:0 1px 3px rgba(0,0,0,.04)}
		.ata-card h2{margin-top:0;font-size:1.15em}
		.ata-guide{background:#f0f6fc;border-left:4px solid #2271b1;padding:12px 16px;margin:12px 0 20px;border-radius:0 6px 6px 0;font-size:13px;line-height:1.5}
		.ata-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:16px;margin:20px 0}
		.ata-stat{background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:16px;text-align:center}
		.ata-stat .number{font-size:28px;font-weight:600;color:#2271b1;display:block}
		.ata-stat .label{color:#646970;font-size:13px;margin-top:4px}
		.ata-quick-links{display:flex;flex-wrap:wrap;gap:10px;margin:16px 0}
		.ata-quick-links a{display:inline-flex;align-items:center;gap:6px;padding:8px 14px;background:#f6f7f7;border:1px solid #dcdcde;border-radius:6px;text-decoration:none;color:#1d2327;font-size:13px}
		.ata-quick-links a:hover{background:#fff;border-color:#2271b1;color:#2271b1}
		.ata-help-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px, 1fr));gap:16px}
		.ata-help-card{background:#fff;border:1px solid #c3c4c7;border-radius:8px;padding:20px}
		.ata-badge{display:inline-block;background:#2271b1;color:#fff;font-size:11px;padding:2px 8px;border-radius:10px;margin-left:6px;vertical-align:middle}
		.ata-pro-note{background:#fff8e5;border-left:4px solid #dba617;padding:12px 16px;margin:12px 0;border-radius:0 6px 6px 0;font-size:13px}
		';
	}

	public function get_settings_sections() {
		return array(
			array( 'id' => 'general_settings', 'title' => __( 'General', 'variation-swatches-style' ) ),
			array( 'id' => 'swatch_types', 'title' => __( 'Swatch Types', 'variation-swatches-style' ) ),
			array( 'id' => 'style_settings', 'title' => __( 'Style', 'variation-swatches-style' ) ),
			array( 'id' => 'tooltip_settings', 'title' => __( 'Tooltip', 'variation-swatches-style' ) ),
			array( 'id' => 'variations_settings', 'title' => __( 'Variations', 'variation-swatches-style' ) ),
			array( 'id' => 'availability_settings', 'title' => __( 'Availability', 'variation-swatches-style' ) ),
			array( 'id' => 'archive_settings', 'title' => __( 'Shop / Archive', 'variation-swatches-style' ) ),
			array( 'id' => 'advanced_settings', 'title' => __( 'Advanced', 'variation-swatches-style' ) ),
		);
	}

	public function get_settings_fields() {
		$fields = array();

		$fields['general_settings'] = array(
			array( 'name' => 'guide_general', 'label' => '', 'desc' => '<div class="ata-guide"><strong>Quick Guide:</strong> Control overall swatch behavior. Start here if you are new.</div>', 'type' => 'html' ),
			array( 'name' => '__enable_swatches', 'label' => __( 'Enable Swatches Globally', 'variation-swatches-style' ), 'desc' => __( 'Master switch. Turn off to temporarily disable all swatches.', 'variation-swatches-style' ), 'type' => 'checkbox', 'default' => 'on' ),
			array( 'name' => '__price_update_on', 'label' => __( 'Price Update Selector', 'variation-swatches-style' ), 'desc' => __( 'CSS class of the price element (e.g. <code>price</code>).', 'variation-swatches-style' ), 'type' => 'text', 'default' => 'price' ),
			array( 'name' => '__clear_on_reselect', 'label' => __( 'Clear on Reselect', 'variation-swatches-style' ), 'desc' => __( 'Clicking an already selected swatch deselects it.', 'variation-swatches-style' ), 'type' => 'checkbox', 'default' => 'on' ),
		);

		$fields['swatch_types'] = array(
			array( 'name' => 'guide_types', 'label' => '', 'desc' => '<div class="ata-guide"><strong>Quick Guide:</strong> Choose which attribute types appear under Products → Attributes. Disabled types will not show in the Type dropdown or on the frontend.</div>', 'type' => 'html' ),
			array( 'name' => 'enable_color', 'label' => __( 'Color Swatches', 'variation-swatches-style' ), 'desc' => __( 'Solid color circles/squares.', 'variation-swatches-style' ), 'type' => 'checkbox', 'default' => 'on' ),
			array( 'name' => 'enable_image', 'label' => __( 'Image Swatches', 'variation-swatches-style' ), 'desc' => __( 'Image / pattern thumbnails.', 'variation-swatches-style' ), 'type' => 'checkbox', 'default' => 'on' ),
			array( 'name' => 'enable_button', 'label' => __( 'Button / Label', 'variation-swatches-style' ), 'desc' => __( 'Text buttons (S, M, L…).', 'variation-swatches-style' ), 'type' => 'checkbox', 'default' => 'on' ),
			array( 'name' => 'enable_select', 'label' => __( 'Select Dropdown', 'variation-swatches-style' ), 'desc' => __( 'Keep native select as an option.', 'variation-swatches-style' ), 'type' => 'checkbox', 'default' => 'on' ),
			array( 'name' => 'pro_types', 'label' => '', 'desc' => '<div class="ata-pro-note"><strong>Pro:</strong> Radio & Checkbox types, dual-color swatches, and more are available in the <a href="https://athemeart.com/downloads/smart-variation-swatches-woocommerce-pro/" target="_blank">Pro version</a>.</div>', 'type' => 'html' ),
		);

		$fields['style_settings'] = array(
			array( 'name' => 'guide_style', 'label' => '', 'desc' => '<div class="ata-guide"><strong>Quick Guide:</strong> Global look of color, image, and button swatches.</div>', 'type' => 'html' ),
			array( 'name' => 'swatch_shape', 'label' => __( 'Default Shape', 'variation-swatches-style' ), 'type' => 'select', 'default' => 'round', 'options' => array( 'square' => __( 'Square', 'variation-swatches-style' ), 'round' => __( 'Circle', 'variation-swatches-style' ), 'round_corner' => __( 'Rounded Corners', 'variation-swatches-style' ) ) ),
			array( 'name' => 'swatch_width', 'label' => __( 'Width (px)', 'variation-swatches-style' ), 'type' => 'number', 'default' => 36 ),
			array( 'name' => 'swatch_height', 'label' => __( 'Height (px)', 'variation-swatches-style' ), 'type' => 'number', 'default' => 36 ),
			array( 'name' => 'swatch_gap', 'label' => __( 'Gap (px)', 'variation-swatches-style' ), 'type' => 'number', 'default' => 8 ),
			array( 'name' => 'selected_border_color', 'label' => __( 'Selected Border', 'variation-swatches-style' ), 'type' => 'color', 'default' => '#333333' ),
			array( 'name' => 'hover_border_color', 'label' => __( 'Hover Border', 'variation-swatches-style' ), 'type' => 'color', 'default' => '#2271b1' ),
			array( 'name' => 'button_bg', 'label' => __( 'Button Background', 'variation-swatches-style' ), 'type' => 'color', 'default' => '#f1f1f1' ),
			array( 'name' => 'button_text', 'label' => __( 'Button Text', 'variation-swatches-style' ), 'type' => 'color', 'default' => '#333333' ),
			array( 'name' => 'button_bg_selected', 'label' => __( 'Button Selected BG', 'variation-swatches-style' ), 'type' => 'color', 'default' => '#333333' ),
			array( 'name' => 'button_text_selected', 'label' => __( 'Button Selected Text', 'variation-swatches-style' ), 'type' => 'color', 'default' => '#ffffff' ),
		);

		$fields['tooltip_settings'] = array(
			array( 'name' => 'guide_tooltip', 'label' => '', 'desc' => '<div class="ata-guide"><strong>Quick Guide:</strong> Tooltips appear on hover/focus to show the term name.</div>', 'type' => 'html' ),
			array( 'name' => 'enable_tooltip', 'label' => __( 'Enable Tooltips', 'variation-swatches-style' ), 'type' => 'checkbox', 'default' => 'on' ),
			array( 'name' => 'tooltip_text_color', 'label' => __( 'Tooltip Text', 'variation-swatches-style' ), 'type' => 'color', 'default' => '#ffffff' ),
			array( 'name' => 'tooltip_bg', 'label' => __( 'Tooltip Background', 'variation-swatches-style' ), 'type' => 'color', 'default' => '#222222' ),
		);

		$fields['variations_settings'] = array(
			array( 'name' => 'guide_vars', 'label' => '', 'desc' => '<div class="ata-guide"><strong>Quick Guide:</strong> Single product page behavior.</div>', 'type' => 'html' ),
			array( 'name' => 'show_attribute_label', 'label' => __( 'Show Attribute Name', 'variation-swatches-style' ), 'type' => 'checkbox', 'default' => 'on' ),
			array( 'name' => 'hide_original_select', 'label' => __( 'Hide Original Dropdown', 'variation-swatches-style' ), 'desc' => __( 'Recommended when swatches are shown.', 'variation-swatches-style' ), 'type' => 'checkbox', 'default' => 'on' ),
		);

		$fields['availability_settings'] = array(
			array( 'name' => 'guide_avail', 'label' => '', 'desc' => '<div class="ata-guide"><strong>Quick Guide:</strong> How out-of-stock options look.</div>', 'type' => 'html' ),
			array( 'name' => 'out_of_stock_style', 'label' => __( 'Out of Stock Style', 'variation-swatches-style' ), 'type' => 'select', 'default' => 'cross', 'options' => array( 'cross' => __( 'Crossed out', 'variation-swatches-style' ), 'blur' => __( 'Blurred', 'variation-swatches-style' ), 'hide' => __( 'Hide', 'variation-swatches-style' ), 'disable' => __( 'Disabled', 'variation-swatches-style' ) ) ),
		);

		$fields['archive_settings'] = array(
			array( 'name' => 'guide_arch', 'label' => '', 'desc' => '<div class="ata-guide"><strong>Quick Guide:</strong> Swatches on shop and category pages.</div>', 'type' => 'html' ),
			array( 'name' => '__swatches_display_on_archive', 'label' => __( 'Enable on Shop / Archive', 'variation-swatches-style' ), 'type' => 'checkbox', 'default' => 'off' ),
			array( 'name' => '__swatches_archive_behavior', 'label' => __( 'Click Behavior', 'variation-swatches-style' ), 'type' => 'radio', 'default' => 'add_to_cart', 'options' => array( 'add_to_cart' => __( 'Select variation', 'variation-swatches-style' ), 'product_filter_by' => __( 'Link / filter', 'variation-swatches-style' ) ) ),
			array( 'name' => '__swatches_display_position_on_arch', 'label' => __( 'Position', 'variation-swatches-style' ), 'type' => 'select', 'default' => 'before_add_to_cart', 'options' => array( 'before_add_to_cart' => __( 'Before Add to Cart', 'variation-swatches-style' ), 'after_add_to_cart' => __( 'After Add to Cart', 'variation-swatches-style' ), 'after_title' => __( 'After title', 'variation-swatches-style' ), 'after_price' => __( 'After price', 'variation-swatches-style' ) ) ),
			array( 'name' => '__archive_variation_style', 'label' => __( 'Shape', 'variation-swatches-style' ), 'type' => 'select', 'default' => 'round', 'options' => array( 'square' => __( 'Square', 'variation-swatches-style' ), 'round' => __( 'Circle', 'variation-swatches-style' ), 'round_corner' => __( 'Rounded', 'variation-swatches-style' ) ) ),
			array( 'name' => '__swatches_archive_width', 'label' => __( 'Size (px)', 'variation-swatches-style' ), 'type' => 'number', 'default' => 28 ),
			array( 'name' => '__swatches_archive_tooltip', 'label' => __( 'Tooltips on Archive', 'variation-swatches-style' ), 'type' => 'checkbox', 'default' => 'on' ),
		);

		$fields['advanced_settings'] = array(
			array( 'name' => 'guide_adv', 'label' => '', 'desc' => '<div class="ata-guide"><strong>Quick Guide:</strong> Advanced options for developers.</div>', 'type' => 'html' ),
			array( 'name' => 'custom_css', 'label' => __( 'Custom CSS', 'variation-swatches-style' ), 'type' => 'textarea' ),
			array( 'name' => 'disable_on_mobile', 'label' => __( 'Disable on Mobile', 'variation-swatches-style' ), 'type' => 'checkbox', 'default' => 'off' ),
		);

		return $fields;
	}

	public function render_dashboard() {
		$taxes = function_exists( 'wc_get_attribute_taxonomies' ) ? wc_get_attribute_taxonomies() : array();
		$total = is_array( $taxes ) ? count( $taxes ) : 0;
		$color = $image = $label = 0;
		if ( $taxes ) {
			foreach ( $taxes as $t ) {
				if ( $t->attribute_type === 'color' ) { $color++; }
				elseif ( $t->attribute_type === 'image' ) { $image++; }
				elseif ( in_array( $t->attribute_type, array( 'label', 'button' ), true ) ) { $label++; }
			}
		}
		?>
		<div class="wrap ata-swatches-wrap">
			<h1><?php esc_html_e( 'Variation Swatches', 'variation-swatches-style' ); ?> <span class="ata-badge">v<?php echo esc_html( ATA_WCVS_VERSION ); ?> Free</span></h1>
			<div class="ata-guide">
				<strong><?php esc_html_e( 'Welcome!', 'variation-swatches-style' ); ?></strong>
				<?php esc_html_e( 'Use this dashboard for a quick overview. Configure attributes under Swatches, then fine-tune appearance under Settings.', 'variation-swatches-style' ); ?>
			</div>
			<div class="ata-stats">
				<div class="ata-stat"><span class="number"><?php echo (int) $total; ?></span><span class="label"><?php esc_html_e( 'Attributes', 'variation-swatches-style' ); ?></span></div>
				<div class="ata-stat"><span class="number"><?php echo (int) $color; ?></span><span class="label"><?php esc_html_e( 'Color', 'variation-swatches-style' ); ?></span></div>
				<div class="ata-stat"><span class="number"><?php echo (int) $image; ?></span><span class="label"><?php esc_html_e( 'Image', 'variation-swatches-style' ); ?></span></div>
				<div class="ata-stat"><span class="number"><?php echo (int) $label; ?></span><span class="label"><?php esc_html_e( 'Button / Label', 'variation-swatches-style' ); ?></span></div>
			</div>
			<div class="ata-card">
				<h2><?php esc_html_e( 'Quick Start', 'variation-swatches-style' ); ?></h2>
				<ol style="line-height:1.8">
					<li><?php esc_html_e( 'Go to Products → Attributes (or click Swatches in the menu).', 'variation-swatches-style' ); ?></li>
					<li><?php esc_html_e( 'Set Type to Color, Image, or Button / Label.', 'variation-swatches-style' ); ?></li>
					<li><?php esc_html_e( 'Configure terms with colors or images.', 'variation-swatches-style' ); ?></li>
					<li><?php esc_html_e( 'Assign the attribute to a variable product and create variations.', 'variation-swatches-style' ); ?></li>
					<li><?php esc_html_e( 'Optional: adjust Style & Tooltip under Settings.', 'variation-swatches-style' ); ?></li>
				</ol>
				<div class="ata-quick-links">
					<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=product&page=product_attributes' ) ); ?>"><span class="dashicons dashicons-tag"></span> <?php esc_html_e( 'Manage Attributes', 'variation-swatches-style' ); ?></a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=ata-swatches-settings' ) ); ?>"><span class="dashicons dashicons-admin-settings"></span> <?php esc_html_e( 'Settings', 'variation-swatches-style' ); ?></a>
					<a href="https://athemeart.com/downloads/smart-variation-swatches-woocommerce-pro/" target="_blank"><span class="dashicons dashicons-star-filled"></span> <?php esc_html_e( 'Upgrade to Pro', 'variation-swatches-style' ); ?></a>
				</div>
			</div>
		</div>
		<?php
	}

	public function render_settings_page() {
		?>
		<div class="wrap ata-swatches-wrap">
			<h1><?php esc_html_e( 'Swatches Settings', 'variation-swatches-style' ); ?></h1>
			<div class="ata-guide"><strong><?php esc_html_e( 'Tip:', 'variation-swatches-style' ); ?></strong> <?php esc_html_e( 'Each tab has a short guide. Save after changing a section.', 'variation-swatches-style' ); ?></div>
			<?php
			$this->settings_api->show_navigation();
			$this->settings_api->show_forms();
			?>
		</div>
		<?php
	}

	public function render_help_page() {
		?>
		<div class="wrap ata-swatches-wrap">
			<h1><?php esc_html_e( 'Help & Support', 'variation-swatches-style' ); ?></h1>
			<div class="ata-help-grid">
				<div class="ata-help-card">
					<h3><?php esc_html_e( 'Documentation', 'variation-swatches-style' ); ?></h3>
					<p><?php esc_html_e( 'Guides for attributes, styling, and troubleshooting.', 'variation-swatches-style' ); ?></p>
					<a class="button" href="https://docs.athemeart.com/docs/smart-variation-swatches-plugins-documentation/" target="_blank"><?php esc_html_e( 'Read Docs', 'variation-swatches-style' ); ?></a>
				</div>
				<div class="ata-help-card">
					<h3><?php esc_html_e( 'Pro Features', 'variation-swatches-style' ); ?></h3>
					<p><?php esc_html_e( 'Unlock more powerful features with Pro, including Radio & Checkbox attribute types, advanced archive options, priority support, and an enhanced filter widget for filtering products by color, image, and other attributes. Upgrade to Pro to unlock the full feature set and enjoy a more flexible product filtering experience.', 'variation-swatches-style' ); ?></p>
					<a class="button button-primary" href="https://athemeart.com/downloads/smart-variation-swatches-woocommerce-pro/" target="_blank"><?php esc_html_e( 'Get Pro', 'variation-swatches-style' ); ?></a>
				</div>
				<div class="ata-help-card">
					<h3><?php esc_html_e( 'Quick Tips', 'variation-swatches-style' ); ?></h3>
					<ul>
						<li><?php esc_html_e( 'Set attribute Type before configuring term colors/images.', 'variation-swatches-style' ); ?></li>
						<li><?php esc_html_e( 'Save Settings on each tab after changes.', 'variation-swatches-style' ); ?></li>
						<li><?php esc_html_e( 'Clear cache if the frontend does not update.', 'variation-swatches-style' ); ?></li>
					</ul>
				</div>
				<div class="ata-help-card">
					<h3><?php esc_html_e( 'Video / Tutorial', 'smart-variation-swatches' ); ?></h3>
					<p><?php esc_html_e( 'Check the original tutorial image and documentation for visual walkthroughs.', 'smart-variation-swatches' ); ?></p>
					<?php if ( file_exists( ATA_WCVS_PATH . 'assets/img/tutorial_1.jpg' ) ) : ?>
						<a href="https://www.youtube.com/watch?v=DX2Jfh-h2SA&list=PLea80-kMMhSQj38EegfB1neFv8szT2GQB" target="_blank" class="button">
						<img src="<?php echo esc_url( ATA_WCVS_URL . 'assets/img/tutorial_1.jpg' ); ?>" alt="Tutorial" style="max-width:100%; border-radius:6px; margin-top:8px;" />
						</a>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
	}
}

endif;
