<?php
/**
 * Plugin Name:       Smart Variation Swatches for WooCommerce
 * Plugin URI:        https://athemeart.com/downloads/variation-swatches-style-woocommerce-pro/
 * Description:       Convert variable product dropdowns into beautiful color, image, and label swatches. Modern admin UI, compatible with the latest WordPress & WooCommerce.
 * Version:           2.0.1
 * Author:            aThemeArt
 * Author URI:        https://athemeart.com/
 * License:           GPLv2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       variation-swatches-style
 * Domain Path:       /languages
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Tested up to:      7.2
 * WC requires at least: 8.0
 * WC tested up to:   10.9
 *
 * @package Smart_Variation_Swatches
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
define( 'ATA_WCVS_VERSION', '2.0.0' );
define( 'ATA_WCVS_FILE', __FILE__ );
define( 'ATA_WCVS_PATH', plugin_dir_path( __FILE__ ) );
define( 'ATA_WCVS_URL', plugin_dir_url( __FILE__ ) );

/**
 * HPOS + Cart/Checkout Blocks compatibility
 */
if(!function_exists('ata_wc_variation_swatches_hpos_compatibility')){
function ata_wc_variation_swatches_hpos_compatibility() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, true );
	}
}
add_action( 'before_woocommerce_init', 'ata_wc_variation_swatches_hpos_compatibility' );
}else{
	exit;
}
/**
 * Main plugin class
 */
final class ATA_WC_Variation_Swatches {

	protected static $instance = null;
	public $types = array();

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __construct() {
		$this->types = array(
			'color'  => esc_html__( 'Color', 'variation-swatches-style' ),
			'image'  => esc_html__( 'Image', 'variation-swatches-style' ),
			'button' => esc_html__( 'Button / Label', 'variation-swatches-style' ),
			'select' => esc_html__( 'Select (Dropdown)', 'variation-swatches-style' ),
		);

		$this->includes();
		$this->init_hooks();
	}

	public function includes() {
		require_once ATA_WCVS_PATH . 'inc/default.php';
		require_once ATA_WCVS_PATH . 'inc/class-admin.php';
		require_once ATA_WCVS_PATH . 'inc/class-options.php';
		require_once ATA_WCVS_PATH . 'inc/class-frontend.php';
		require_once ATA_WCVS_PATH . 'inc/class-wc-ex-product-data-tab.php';
		require_once ATA_WCVS_PATH . 'inc/widgets-helper.php';
		require_once ATA_WCVS_PATH . 'inc/wc_widget_layered_nav_categories.php';
		require_once ATA_WCVS_PATH . 'inc/plugins-settings.php';
	}

	public function init_hooks() {
		add_action( 'init', array( $this, 'load_textdomain' ) );

		// Attribute type dropdown must work in admin
		add_filter( 'product_attributes_type_selector', array( $this, 'add_attribute_types' ) );

		if ( is_admin() ) {
			add_action( 'init', array( 'ATA_WC_Variation_Swatches_Admin', 'instance' ) );
			add_action( 'init', array( 'ATA_WC_Variation_Swatches_Options', 'instance' ) );
			add_action( 'init', array( 'WC_EX_Product_Data_Tab_Swatches', 'instance' ) );
		} else {
			add_action( 'init', array( 'ATA_WC_Variation_Swatches_Frontend', 'instance' ) );
			
		}

		add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
		add_action( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'settings_link' ) );
	}

	public function load_textdomain() {
		load_plugin_textdomain( 'variation-swatches-style', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Enabled types only (Settings → Swatch Types)
	 */
	public function get_enabled_types() {
		$enabled = array();
		foreach ( $this->types as $key => $label ) {
			if ( function_exists( 'atawcvs_is_type_enabled' ) && atawcvs_is_type_enabled( $key ) ) {
				$enabled[ $key ] = $label;
			} elseif ( ! function_exists( 'atawcvs_is_type_enabled' ) ) {
				$enabled[ $key ] = $label;
			}
		}
		return $enabled;
	}

	public function add_attribute_types( $types ) {
		return array_merge( $types, $this->get_enabled_types() );
	}

	public function get_tax_attribute( $taxonomy ) {
		global $wpdb;
		$attr_name = substr( $taxonomy, 3 );
		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}woocommerce_attribute_taxonomies WHERE attribute_name = %s",
				$attr_name
			)
		);
	}

	public function frontend() {
		return ATA_WC_Variation_Swatches_Frontend::instance();
	}

	public function settings_link( $links ) {
		array_unshift(
			$links,
			'<a href="' . esc_url( admin_url( 'admin.php?page=ata-variation-swatches' ) ) . '">' . esc_html__( 'Settings', 'variation-swatches-style' ) . '</a>'
		);
		return $links;
	}

	public function plugin_row_meta( $links, $file ) {
		if ( plugin_basename( __FILE__ ) !== $file ) {
			return $links;
		}
		$links['docs'] = '<a href="https://docs.athemeart.com/docs/smart-variation-swatches-plugins-documentation/" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Documentation', 'variation-swatches-style' ) . '</a>';
		$links['pro']  = '<a href="https://athemeart.com/downloads/smart-variation-swatches-woocommerce-pro/" target="_blank" rel="noopener noreferrer" style="color:#2271b1;font-weight:600;">' . esc_html__( 'Get Pro', 'variation-swatches-style' ) . '</a>';
		return $links;
	}
}

function ATA_WCVS() {
	return ATA_WC_Variation_Swatches::instance();
}

function ata_wc_variation_swatches_wc_notice() {
	echo '<div class="notice notice-error"><p>' . esc_html__( 'Smart Variation Swatches requires WooCommerce to be installed and active.', 'variation-swatches-style' ) . '</p></div>';
}

function ata_wc_variation_swatches_constructor() {
	if ( ! function_exists( 'WC' ) ) {
		add_action( 'admin_notices', 'ata_wc_variation_swatches_wc_notice' );
		return;
	}
	if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '8.0', '<' ) ) {
		add_action(
			'admin_notices',
			function () {
				echo '<div class="notice notice-error"><p>' . esc_html__( 'Smart Variation Swatches requires WooCommerce 8.0 or higher.', 'variation-swatches-style' ) . '</p></div>';
			}
		);
		return;
	}
	ATA_WCVS();
}
add_action( 'plugins_loaded', 'ata_wc_variation_swatches_constructor', 20 );

function ata_wc_swatches_activate() {
	add_option( 'activate-smart-variation-swatches', 'yes' );
}
register_activation_hook( __FILE__, 'ata_wc_swatches_activate' );

require_once ATA_WCVS_PATH . 'inc/plugins-settings.php';
