<?php

/**
 * WordPress settings API demo class
 *
 * @author Tareq Hasan
 */
if ( !class_exists('ATA_WC_Variation_Swatches_Options' ) ):

class ATA_WC_Variation_Swatches_Options {
	/**
	 * The single instance of the class
	 *
	 * @var ATA_WC_Variation_Swatches_Admin
	 */
	protected static $instance = null;
	
    private $settings_api;

	/**
	 * Main instance
	 *
	 * @return ATA_WC_Variation_Swatches_Admin
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
    function __construct() {
		require_once 'class.settings-api.php';
        $this->settings_api = new WeDevs_Settings_API_Swatches;

        add_action( 'admin_init', array($this, 'admin_init') );
        add_action( 'admin_menu', array($this, 'admin_menu') );
    }

    function admin_init() {

        //set the settings
        $this->settings_api->set_sections( $this->get_settings_sections() );
        $this->settings_api->set_fields( $this->get_settings_fields() );

        //initialize settings
        $this->settings_api->admin_init();
    }

    function admin_menu() {
       // add_options_page( 'Settings API', 'Settings API', 'woocommerce', 'settings_api_test', array($this, 'plugin_page') );
		   add_submenu_page( 'woocommerce', 'Variation Swatches ', 'Smart Swatches', 'manage_options', 'ata-variation-swatches', array($this, 'plugin_page') ); 
    }

    function get_settings_sections() {
		
        $sections = array(
			array(
                'id'    => 'general_settings',
                'title' => __( 'General Settings', 'variation-swatches-style' )
            ),
			
			 array(
                'id'    => 'atawc_label',
                'title' => __( 'Label Swatches  Settings', 'variation-swatches-style' )
            ),
            array(
                'id'    => 'atawc_color',
                'title' => __( 'Color Swatches  Settings', 'variation-swatches-style' )
            ),
            array(
                'id'    => 'atawc_images',
                'title' => __( 'Images Swatches Settings', 'variation-swatches-style' )
            ),
			array(
                'id'    => 'archive_settings',
                'title' => __( 'Shop / Archive', 'variation-swatches-style' )
            ),
			
			array(
                'id'    => 'atawc_tutorials',
                'title' => __( 'Tutorials', 'variation-swatches-style' )
            ),
            
			 
        );
        return $sections;
    }
	

    /**
     * Returns all the settings fields
     *
     * @return array settings fields
     */
    function get_settings_fields() {
        $settings_fields = array(
		 
		 
		'atawc_tutorials' => array(
			
			
		),
		 'archive_settings' => array(
				
		
				
				array(
                    'name'    => '__swatches_display_on_archive',
                    'label'   => __( 'Enable Swatches', 'variation-swatches-style' ),
                    'type'    => 'checkbox',
                   
					'desc'    => __( 'Show Swatches on archive / shop page', 'variation-swatches-style' ),
                ),
				
				array(
                    'name'    => '__swatches_archive_behavior',
                    'label'   => __( 'Swatches behavior', 'variation-swatches-style' ),
                    'desc'    => __( 'Swatches behavior on archive / shop page ', 'variation-swatches-style' ),
                    'type'    => 'radio',
					'default' => 'add_to_cart',
                    'options' => array(
                        'add_to_cart' =>  __( 'Add to Cart', 'variation-swatches-style' ),
                        'product_filter_by'  =>  __( 'Product filter by attribute ', 'variation-swatches-style' ),
                    )
                ),
				
				array(
                    'name'    => '__swatches_archive_label',
                    'label'   => __( 'Display Label', 'variation-swatches-style' ),
                    'type'    => 'checkbox',
                    'default' => 'on',
					'desc'    => __( 'Show Swatches Label or title on archive / shop page', 'variation-swatches-style' ),
                ),
				array(
                    'name'    => '__swatches_display_position_on_arch',
                   'label'   => __( 'Display Position', 'variation-swatches-style' ),
                    'type'    => 'radio',
                    'default' => 'before_add_to_cart',
                    'options' => array(
                        'before_add_to_cart' => __( 'Before add to cart ', 'variation-swatches-style' ),
                        'after_add_to_cart'  => __( 'After add to cart', 'variation-swatches-style' ),
                    )
                ),
				
				array(
                    'name'    => '__swatches_archive_tooltip',
                    'label'   => __( 'Enable Tooltip', 'variation-swatches-style' ),
                    'type'    => 'checkbox',
                    
					'desc'    => __( 'Enable Archive page tooltips', 'variation-swatches-style' ),
                ),
				
				 array(
                    'name'              => '__swatches_archive_width',
                    'label'             => __( 'Swatches width ', 'variation-swatches-style' ),
                    'default' 			=> 40,
                    'type'              => 'number',
                    'sanitize_callback' => 'number'
                ),
				array(
                    'name'              => '__swatches_archive_height',
                    'label'             => __( 'Swatches Height', 'variation-swatches-style' ),
                    'default' 			=> 40,
                    'type'              => 'number',
                    'sanitize_callback' => 'number'
                ),
				
				array(
                    'name'    => '__archive_variation_style',
                    'label'   => __( 'Swatches Style', 'variation-swatches-style' ),
                    'type'    => 'select',
                    'default' => 'round',
                    'options' => array(
                        'square' => __( 'Square', 'variation-swatches-style' ),
                        'round'  => __( 'Circle', 'variation-swatches-style' ),
						'round_corner'  => __( 'Round corner', 'variation-swatches-style' ),
                    ),
					'desc'    => __( ' Swatches Style on page Shop / Archive', 'variation-swatches-style' ),
                ),
				
				
			),
			
			
		 'general_settings' => array(
				array(
					'name'    => '__price_update_on',
					'label'   => __( 'Variable Price range Show', 'variation-swatches-style' ),
					'type'    => 'text',
					'default' => 'price',
					'desc'    => __( 'Replace the Variable Price range by the chosen css class', 'variation-swatches-style' ),
				),
				
				array(
                    'name'    => '__swatches_tooltip',
                    'label'   => __( 'Tooltip Color', 'variation-swatches-style' ),
                    'type'    => 'color',
                    'default' => '#000'
                ),
				array(
                    'name'    => '__swatches_bg',
                    'label'   => __( 'Tooltip Background', 'variation-swatches-style' ),
                    'type'    => 'color',
                    'default' => '#fff'
                ),
				
				array(
                    'name'    => '__swatches_tick_sing_color',
                    'label'   => __( 'Tick sign Color', 'variation-swatches-style' ),
                    'type'    => 'color',
                    'default' => '#000'
                ),
				
				
				
			),
			
			
            'atawc_label' => array(
				array(
                    'name'    => 'lebel_variation_style',
                    'label'   => __( 'Swatches Type', 'variation-swatches-style' ),
                    'type'    => 'select',
                    'default' => 'square',
                    'options' => array(
                        'square' => __( 'Square', 'variation-swatches-style' ),
                        'round'  => __( 'Circle', 'variation-swatches-style' ),
						'round_corner'  => __( 'Round corner', 'variation-swatches-style' ),
                    )
                ),
                array(
                    'name'              => 'lebel_variation_width',
                    'label'             => __( 'Button Width', 'variation-swatches-style' ),
                    'default' 			=> 40,
                    'type'              => 'number',
                    'sanitize_callback' => 'number'
                ),
				array(
                    'name'              => 'lebel_variation_height',
                    'label'             => __( 'Button Height', 'variation-swatches-style' ),
                    'default' 			=> 40,
                    'type'              => 'number',
                    'sanitize_callback' => 'number'
                ),
				array(
                    'name'              => 'lebel_variation_size',
                    'label'             => __( 'Font Size', 'variation-swatches-style' ),
                    'default' 			=> 13,
                    'type'              => 'number',
                    'sanitize_callback' => 'number',
					'desc'    => __( 'PX', 'variation-swatches-style' ),
                ),
				
				
               	array(
                    'name'    => 'lebel_variation_color',
                    'label'   => __( 'Button Color', 'variation-swatches-style' ),
                    'type'    => 'color',
                    'default' => '#fff'
                ),
				array(
                    'name'    => 'lebel_variation_background',
                    'label'   => __( 'Button Background', 'variation-swatches-style' ),
                    'type'    => 'color',
                    'default' => '#000'
                ),
				array(
                    'name'    => 'lebel_variation_border',
                    'label'   => __( 'border Color', 'variation-swatches-style' ),
                    'type'    => 'color',
                    'default' => '#000'
                ),
				
				array(
                    'name'    => 'swatches_hover_settings',
                    'label'   =>'',
                    'type'    => 'html',
                  
                ),
				array(
                    'name'    => 'lebel_variation_color_hover',
                    'label'   => __( 'Hover Color', 'variation-swatches-style' ),
                    'type'    => 'color',
                    'default' => '#000'
                ),
				
				array(
                    'name'    => 'lebel_variation_background_hover',
                    'label'   => __( 'Hover Background', 'variation-swatches-style' ),
                    'type'    => 'color',
                    'default' => '#c8c8c8'
                ),
				
				
				array(
                    'name'    => 'lebel_variation_border_hover',
                    'label'   => __( 'Hover border', 'variation-swatches-style' ),
                    'type'    => 'color',
                    'default' => '#c8c8c8'
                ),
				array(
                    'name'    => 'swatches_hover_settings_2',
                    'label'   =>'',
                    'type'    => 'html',
                  
                ),
				array(
                    'name'    => 'lebel_variation_tooltip',
                    'label'   => __( 'Color Swatches tooltip', 'variation-swatches-style' ),
                    'type'    => 'select',
                    'default' => 'no',
                    'options' => array(
                        'yes' => __( 'Yes', 'variation-swatches-style' ),
                        'no'  => __( 'No', 'variation-swatches-style' ),
                    )
                ),
				array(
                    'name'    => 'lebel_variation_ingredient',
                   'label'   => __( 'Active / Selected item ingredient', 'variation-swatches-style' ),
                    'type'    => 'select',
                    'default' => 'opacity',
                    'options' => array(
                        'tick_sign' => __( 'Tick sign', 'variation-swatches-style' ),
                        'opacity'  => __( 'Opacity', 'variation-swatches-style' ),
						'zoom_up'  => __( 'Zoom Up', 'variation-swatches-style' ),
						'zoom_down'  => __( 'Zoom Down', 'variation-swatches-style' ),
                    )
                ),
            ),
            'atawc_color' => array(
               array(
                    'name'    => 'color_variation_style',
                    'label'   => __( 'Swatches Type', 'variation-swatches-style' ),
                    'type'    => 'select',
                    'default' => 'round',
                    'options' => array(
                        'square' => __( 'Square', 'variation-swatches-style' ),
                        'round'  => __( 'Circle', 'variation-swatches-style' ),
						'round_corner'  => __( 'Round corner', 'variation-swatches-style' ),
                    )
                ),
                array(
                    'name'              => 'color_variation_width',
                    'label'             => __( 'Color Swatches Width', 'variation-swatches-style' ),
                    'default' 			=> 40,
                    'type'              => 'number',
                    'sanitize_callback' => 'number'
                ),
				array(
                    'name'              => 'color_variation_height',
                    'label'             => __( 'Color Swatches Height', 'variation-swatches-style' ),
                    'default' 			=> 40,
                    'type'              => 'number',
                    'sanitize_callback' => 'number'
                ),
				
				array(
                    'name'    => 'color_variation_tooltip',
                    'label'   => __( 'Color Swatches tooltip', 'variation-swatches-style' ),
                    'type'    => 'select',
                    'default' => 'no',
                    'options' => array(
                        'yes' => __( 'Yes', 'variation-swatches-style' ),
                        'no'  => __( 'No', 'variation-swatches-style' ),
                    )
                ),
				array(
                    'name'    => 'color_variation_ingredient',
                    'label'   => __( 'Active / Selected item ingredient', 'variation-swatches-style' ),
                    'type'    => 'select',
                    'default' => 'tick_sign',
                    'options' => array(
                        'tick_sign' => __( 'Tick sign', 'variation-swatches-style' ),
                        'opacity'  => __( 'Opacity', 'variation-swatches-style' ),
						'zoom_up'  => __( 'Zoom Up', 'variation-swatches-style' ),
						'zoom_down'  => __( 'Zoom Down', 'variation-swatches-style' ),
                    )
                ),
				
            ),
            'atawc_images' => array(
               array(
                    'name'    => 'image_variation_style',
                    'label'   => __( 'Image Swatches Style', 'variation-swatches-style' ),
                    'type'    => 'select',
                    'default' => 'round_corner',
                    'options' => array(
                        'square' => __( 'Square', 'variation-swatches-style' ),
                        'round'  => __( 'Circle', 'variation-swatches-style' ),
						'round_corner'  => __( 'Round corner', 'variation-swatches-style' ),
                    )
                ),
                array(
                    'name'              => 'image_variation_width',
                    'label'             => __( 'Image Swatches Width', 'variation-swatches-style' ),
                    'default' 			=> 44,
                    'type'              => 'number',
                    'sanitize_callback' => 'number'
                ),
				array(
                    'name'              => 'image_variation_height',
                    'label'             => __( 'Image Swatches Height', 'variation-swatches-style' ),
                    'default' 			=> 44,
                    'type'              => 'number',
                    'sanitize_callback' => 'number'
                ),
				
				array(
                    'name'    => 'image_variation_tooltip',
                    'label'   => __( 'Image Swatches tooltip', 'variation-swatches-style' ),
                    'type'    => 'select',
                    'default' => 'yes',
                    'options' => array(
                        'yes' => __( 'Yes', 'variation-swatches-style' ),
                        'no'  => __( 'No', 'variation-swatches-style' ),
                    )
                ),
				
				array(
                    'name'    => 'image_variation_ingredient',
                    'label'   => __( 'Active / Selected item ingredient', 'variation-swatches-style' ),
                    'type'    => 'select',
                    'default' => 'tick_sign',
                    'options' => array(
                        'tick_sign' => __( 'Tick sign', 'variation-swatches-style' ),
                        'opacity'  => __( 'Opacity', 'variation-swatches-style' ),
						'zoom_up'  => __( 'Zoom Up', 'variation-swatches-style' ),
						'zoom_down'  => __( 'Zoom Down', 'variation-swatches-style' ),
                    )
                ),
				
            )
        );

        return $settings_fields;
    }

    function plugin_page() {
        echo '<div class="wrap">';
        $this->settings_api->show_navigation();
        $this->settings_api->show_forms();
        echo '</div>';
    }


}


endif;
