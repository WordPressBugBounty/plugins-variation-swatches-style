<?php
/*
Plugin Name: Smart Variation Swatches and Attribute Filters for WooCommerce
Plugin URI: https://athemeart.net/downloads/variation-swatches-style-woocommerce-pro/
Description: An extension of WooCommerce that make variable products be more beauty and friendly with customers.
Version: 1.4.0
Author: aThemeArt
Author URI: http://athemeart.net/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Text Domain: variation-swatches-style 
Domain Path: /languages/
Tested up to: 7.6.2
WC requires at least:7
WC tested up to: 10.9.3
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * The main plugin class
 */
final class ATA_WC_Variation_Swatches {
	/**
	 * The single instance of the class
	 *
	 * @var ATA_WC_Variation_Swatches
	 */
	protected static $instance = null;

	/**
	 * Extra attribute types
	 *
	 * @var array
	 */
	public $types = array();

	/**
	 * Main instance
	 *
	 * @return ATA_WC_Variation_Swatches
	 */
	public static function instance() {
		if ( null == self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Class constructor.
	 */
	public function __construct() {
		$this->types = array(
			'image' => esc_html__( 'Image', 'variation-swatches-style' ),
			'color' => esc_html__( 'Color', 'variation-swatches-style' ),
			'label' => esc_html__( 'Label', 'variation-swatches-style' ),
			'select_2' => esc_html__( 'Select box', 'variation-swatches-style' ),
		);

		$this->includes();
		$this->init_hooks();
		
	}

	/**
	 * Include required core files used in admin and on the frontend.
	 */
	public function includes() {
		require_once 'inc/class-admin.php';
		require_once 'inc/class-options.php';
		require_once 'inc/default.php';
		require_once 'inc/class-frontend.php';
		require_once 'inc/class-wc-ex-product-data-tab.php';
		
		require_once 'inc/widgets-helper.php';
		require_once 'inc/wc_widget_layered_nav_categories.php';
		require_once 'inc/plugins-settings.php';
	}

	/**
	 * Initialize hooks
	 */
	public function init_hooks() {
		add_action( 'init', array( $this, 'load_textdomain' ) );
		
		if( isset( $_GET['page'] ) && $_GET['page'] == 'product_attributes' ){
			add_filter( 'product_attributes_type_selector', array( $this, 'add_attribute_types' ) );
		}
		

		if ( is_admin() ) {
			add_action( 'init', array( 'ATA_WC_Variation_Swatches_Admin', 'instance' ) );
			add_action( 'init', array( 'ATA_WC_Variation_Swatches_Options', 'instance' ) );
			add_action( 'init', array( 'WC_EX_Product_Data_Tab_Swatches', 'instance' ) );
			
		} else {
			add_filter( 'product_attributes_type_selector', array( $this, 'add_attribute_types' ) );
			add_action( 'init', array( 'ATA_WC_Variation_Swatches_Frontend', 'instance' ) );
			
		}
		
		
		add_filter( 'plugin_action_links', array( $this, 'swatches_action_links' ), 999, 2 );
		add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
		
		add_action( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'swatches_settings_action_links' )  );
		
	}
	

	/**
	 * Load plugin text domain
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'variation-swatches-style', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Add extra attribute types
	 * Add color, image and label type
	 *
	 * @param array $types
	 *
	 * @return array
	 */
	public function add_attribute_types( $types ) {
		$types = array_merge( $types, $this->types );

		return $this->types;
	}

	/**
	 * Get attribute's properties
	 *
	 * @param string $taxonomy
	 *
	 * @return object
	 */
	public function get_tax_attribute( $taxonomy ) {
		global $wpdb;

		$attr = substr( $taxonomy, 3 );
		$attr = $wpdb->get_row( "SELECT * FROM " . $wpdb->prefix . "woocommerce_attribute_taxonomies WHERE attribute_name = '$attr'" );

		return $attr;
	}

	

	/**
	 * Instance of frontend
	 *
	 * @return ATA_WC_Variation_Swatches_Frontend
	 */
	public function frontend() {
		return ATA_WC_Variation_Swatches_Frontend::instance();
	}
	
	function swatches_settings_action_links( $links ) {
	
		$links = array_merge( array(
			'<a href="' . esc_url( admin_url( '/admin.php?page=ata-variation-swatches' ) ) . '">' . __( 'Settings', 'variation-swatches-style' ) . '</a>'
		), $links );
		
		return $links;
	
	}


	public function swatches_action_links( $actions, $file ) {
		if ( $file == plugin_basename( __FILE__ )) {
			
			$actions['apsw_go_pro'] = '<a href="https://athemeart.com/downloads/smart-variation-swatches-woocommerce-pro/" target="_blank" style="color: #45b450; font-weight: bold">Go Pro!</a>';
			
		}
		return $actions;
	}
	public function plugin_row_meta( $links, $file ){
		if ( $file == plugin_basename( __FILE__ )) {
			$report_url = esc_url( add_query_arg( array(
											  'utm_source'   => 'wp-admin-plugins',
											  'utm_medium'   => 'row-meta-link',
											  'utm_campaign' => 'variation-swatches-style',
										  ), 'https://support.athemeart.com/' ) );
			
			$documentation_url = esc_url( add_query_arg( array(
													 'utm_source'   => 'wp-admin-plugins',
													 'utm_medium'   => 'row-meta-link',
													 'utm_campaign' => 'variation-swatches-style',
												 ), 'https://docs.athemeart.com/docs/smart-variation-swatches-plugins-documentation/' ) );
			
			
			$links[ 'documentation' ] = '<a target="_blank" href="' . esc_url( $documentation_url ) . '" title="' . esc_attr( esc_html__( 'Read Documentation', 'variation-swatches-style' ) ) . '">' . esc_html__( 'Read Documentation', 'variation-swatches-style' ) . '</a>';
			
			$links[ 'issues' ] = sprintf( '%2$s <a target="_blank" href="%1$s">%3$s</a>', esc_url( $report_url ), esc_html__( 'Facing issue?', 'variation-swatches-style' ), '<span style="color: red">' . esc_html__( 'Please open a ticket.', 'variation-swatches-style' ) . '</span>' );	
			
		}
		return $links;
	
	}
	
	
}



/**
 * Main instance of plugin
 *
 * @return ATA_WC_Variation_Swatches
 */
function ATA_WCVS() {
	return ATA_WC_Variation_Swatches::instance();
}

/**
 * Display notice in case of WooCommerce plugin is not activated
 */
function ata_wc_variation_swatches_wc_notice() {
	?>

	<div class="error">
		<p><?php esc_html_e( 'Smart Variation Wwatches for WooCommerce-pro is enabled but not effective. It requires WooCommerce in order to work.', 'variation-swatches-style' ); ?></p>
	</div>

	<?php
}

/**
 * Construct plugin when plugins loaded in order to make sure WooCommerce API is fully loaded
 * Check if WooCommerce is not activated then show an admin notice
 * or create the main instance of plugin
 */
function ata_wc_variation_swatches_constructor() {
	if ( ! function_exists( 'WC' ) ) {
		add_action( 'admin_notices', 'ata_wc_variation_swatches_wc_notice' );
	} else {
		ATA_WCVS();
	}
}

add_action( 'plugins_loaded', 'ata_wc_variation_swatches_constructor' );

function ata_wc_variation_hpos_compatibility() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
}
add_action( 'before_woocommerce_init', 'ata_wc_variation_hpos_compatibility' );

require_once 'inc/plugins-settings.php';