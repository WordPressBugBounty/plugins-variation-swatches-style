<?php

/**
 * Class ATA_WC_Variation_Swatches_Frontend
 */
class ATA_WC_Variation_Swatches_Frontend {
	/**
	 * The single instance of the class
	 *
	 * @var ATA_WC_Variation_Swatches_Frontend
	 */
	protected static $instance = null;

	/**
	 * Main instance
	 *
	 * @return ATA_WC_Variation_Swatches_Frontend
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
		// Master switch
		$enabled = atawcvs_get_setting( 'general_settings', '__enable_swatches', 'on' );
		if ( $enabled !== 'on' && $enabled !== '1' ) {
			return;
		}

		// Optional: disable on mobile
		$disable_mobile = atawcvs_get_setting( 'advanced_settings', 'disable_on_mobile', 'off' );
		if ( $disable_mobile === 'on' && wp_is_mobile() ) {
			return;
		}

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_filter( 'woocommerce_dropdown_variation_attribute_options_html', array( $this, 'get_swatch_html' ), 100, 2 );
		add_filter( 'atawc_swatch_html', array( $this, 'swatch_html' ), 5, 5 );
		add_filter( 'atawc_swatch_meta_html', array( $this, 'atawc_swatch_meta_html' ), 5, 5 );
		add_action( 'wp_head', array( $this, 'dynamic_style' ), 999 );
	}
	

	/**
	 * Shared style values from Settings → Style / Tooltip
	 *
	 * @return array{shape:string,width:int,height:int,tooltip:string}
	 */
	protected function get_style_vars() {
		$style   = atawcvs_get_option( 'style_settings' );
		$tooltip = atawcvs_get_option( 'tooltip_settings' );

		$tip_on = isset( $tooltip['enable_tooltip'] ) ? $tooltip['enable_tooltip'] : 'on';

		return array(
			'shape'   => ! empty( $style['swatch_shape'] ) ? $style['swatch_shape'] : 'round',
			'width'   => ! empty( $style['swatch_width'] ) ? absint( $style['swatch_width'] ) : 36,
			'height'  => ! empty( $style['swatch_height'] ) ? absint( $style['swatch_height'] ) : 36,
			'tooltip' => ( $tip_on === 'on' || $tip_on === '1' ) ? 'masterTooltip' : '',
		);
	}

	/**
	 * Dynamic stylesheets from Settings → Style / Tooltip / Availability
	 */
	public function dynamic_style() {
		$style   = atawcvs_get_option( 'style_settings' );
		$tooltip = atawcvs_get_option( 'tooltip_settings' );
		$avail   = atawcvs_get_option( 'availability_settings' );
		$vars    = atawcvs_get_option( 'variations_settings' );
		$adv     = atawcvs_get_option( 'advanced_settings' );

		$width  = isset( $style['swatch_width'] ) ? absint( $style['swatch_width'] ) : 36;
		$height = isset( $style['swatch_height'] ) ? absint( $style['swatch_height'] ) : 36;
		$gap    = isset( $style['swatch_gap'] ) ? absint( $style['swatch_gap'] ) : 8;
		$shape  = isset( $style['swatch_shape'] ) ? $style['swatch_shape'] : 'round';
		$sel_b  = isset( $style['selected_border_color'] ) ? $style['selected_border_color'] : '#333';
		$hov_b  = isset( $style['hover_border_color'] ) ? $style['hover_border_color'] : '#2271b1';
		$btn_bg = isset( $style['button_bg'] ) ? $style['button_bg'] : '#f1f1f1';
		$btn_tx = isset( $style['button_text'] ) ? $style['button_text'] : '#333';
		$btn_bgs= isset( $style['button_bg_selected'] ) ? $style['button_bg_selected'] : '#333';
		$btn_txs= isset( $style['button_text_selected'] ) ? $style['button_text_selected'] : '#fff';

		$tip_on  = isset( $tooltip['enable_tooltip'] ) ? $tooltip['enable_tooltip'] : 'on';
		$tip_c   = isset( $tooltip['tooltip_text_color'] ) ? $tooltip['tooltip_text_color'] : '#fff';
		$tip_bg  = isset( $tooltip['tooltip_bg'] ) ? $tooltip['tooltip_bg'] : '#222';

		$oos = isset( $avail['out_of_stock_style'] ) ? $avail['out_of_stock_style'] : 'cross';

		$radius = '4px';
		if ( $shape === 'round' ) {
			$radius = '50%';
		} elseif ( $shape === 'round_corner' ) {
			$radius = '6px';
		} elseif ( $shape === 'square' ) {
			$radius = '0';
		}

		$hide_select = isset( $vars['hide_original_select'] ) ? $vars['hide_original_select'] : 'on';
		?>
		<style id="atawc-dynamic-css" type="text/css">
			.atawc-swatches {
				display: flex;
				flex-wrap: wrap;
				gap: <?php echo (int) $gap; ?>px;
				align-items: center;
				padding: 8px 0;
			}
			.atawc-swatches .swatch {
				width: <?php echo (int) $width; ?>px;
				height: <?php echo (int) $height; ?>px;
				min-width: <?php echo (int) $width; ?>px;
				border-radius: <?php echo esc_attr( $radius ); ?>;
				border: 2px solid transparent;
				box-sizing: border-box;
				cursor: pointer;
				transition: all 0.2s ease;
				position: relative;
			}
			.atawc-swatches .swatch:hover {
				border-color: <?php echo esc_attr( $hov_b ); ?>;
			}
			.atawc-swatches .swatch.selected {
				border-color: <?php echo esc_attr( $sel_b ); ?>;
				box-shadow: 0 0 0 1px <?php echo esc_attr( $sel_b ); ?>;
			}
			.atawc-swatches .swatch-label,
			.atawc-swatches .swatch-button {
				width: auto;
				min-width: <?php echo (int) $width; ?>px;
				padding: 0 10px;
				line-height: <?php echo (int) $height; ?>px;
				background: <?php echo esc_attr( $btn_bg ); ?>;
				color: <?php echo esc_attr( $btn_tx ); ?>;
				font-size: 13px;
				text-align: center;
			}
			.atawc-swatches .swatch-label:hover,
			.atawc-swatches .swatch-label.selected,
			.atawc-swatches .swatch-button:hover,
			.atawc-swatches .swatch-button.selected {
				background: <?php echo esc_attr( $btn_bgs ); ?>;
				color: <?php echo esc_attr( $btn_txs ); ?>;
			}
			.atawc-swatches .swatch-image img {
				width: 100%;
				height: 100%;
				object-fit: cover;
				border-radius: inherit;
				display: block;
			}
			/* Tooltips */
			<?php if ( $tip_on === 'on' || $tip_on === '1' ) : ?>
			.ed-tooltip {
				color: <?php echo esc_attr( $tip_c ); ?>;
				background: <?php echo esc_attr( $tip_bg ); ?>;
			}
			.ed-tooltip::after {
				border-top-color: <?php echo esc_attr( $tip_bg ); ?>;
			}
			<?php else : ?>
			.ed-tooltip { display: none !important; }
			<?php endif; ?>
			/* Out of stock */
			<?php if ( $oos === 'hide' ) : ?>
			.atawc-swatches .swatch.out_of_stock { display: none !important; }
			<?php elseif ( $oos === 'blur' ) : ?>
			.atawc-swatches .swatch.out_of_stock { opacity: 0.35; pointer-events: none; }
			.atawc-swatches .swatch.out_of_stock::before,
			.atawc-swatches .swatch.out_of_stock::after { display: none; }
			<?php elseif ( $oos === 'disable' ) : ?>
			.atawc-swatches .swatch.out_of_stock { opacity: 0.5; pointer-events: none; cursor: not-allowed; }
			.atawc-swatches .swatch.out_of_stock::before,
			.atawc-swatches .swatch.out_of_stock::after { display: none; }
			<?php endif; /* cross = default CSS */ ?>
			/* Hide original select */
			<?php if ( $hide_select === 'on' || $hide_select === '1' ) : ?>
			.variations_form .variation-selector select,
			.variations_form .value select {
				/* keep accessible but visually hidden when swatches present */
			}
			.variations_form .swatches-support .value > select,
			.variations_form .variation-selector > select {
				position: absolute !important;
				clip: rect(1px, 1px, 1px, 1px);
				width: 1px !important;
				height: 1px !important;
				overflow: hidden;
			}
			<?php endif; ?>
			<?php
			if ( ! empty( $adv['custom_css'] ) ) {
				echo wp_strip_all_tags( $adv['custom_css'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			?>
		</style>
		<?php
	}

	/**
	 * Enqueue scripts and stylesheets
	 */
	public function enqueue_scripts() {
		$general = atawcvs_get_option( 'general_settings' );
		$footer  = atawcvs_get_setting( 'advanced_settings', 'load_scripts_footer', 'on' ) === 'on';

		wp_enqueue_style( 'atawc-frontend', ATA_WCVS_URL . 'assets/css/frontend.css', array(), ATA_WCVS_VERSION );
		wp_enqueue_script( 'atawc-frontend', ATA_WCVS_URL . 'assets/js/frontend.js', array( 'jquery' ), ATA_WCVS_VERSION, $footer );

		$price_sel = ! empty( $general['__price_update_on'] ) ? $general['__price_update_on'] : 'price';
		wp_localize_script(
			'atawc-frontend',
			'smart_variable',
			array(
				'__price_update_on'  => esc_attr( $price_sel ),
				'clear_on_reselect'  => atawcvs_get_setting( 'general_settings', '__clear_on_reselect', 'on' ),
				'enable_tooltip'     => atawcvs_get_setting( 'tooltip_settings', 'enable_tooltip', 'on' ),
			)
		);
	}

	/**
	 * Filter function to add swatches bellow the default selector
	 *
	 * @param $html
	 * @param $args
	 *
	 * @return string
	 */
	public function get_swatch_html( $html, $args ) {
		$swatch_types = ATA_WCVS()->types;
		$attr         = ATA_WCVS()->get_tax_attribute( $args['attribute'] );

		// Resolve effective type: product override first, then global attribute type
		$product   = isset( $args['product'] ) ? $args['product'] : null;
		$attribute = isset( $args['attribute'] ) ? $args['attribute'] : '';
		$effective_type = '';

		if ( $product && $attribute ) {
			$key = md5( sanitize_title( $attribute ) );
			$swatch_type_options = $product->get_meta( '_swatch_type_options', true );
			if ( is_array( $swatch_type_options ) && ! empty( $swatch_type_options[ $key ]['type'] ) ) {
				$effective_type = $swatch_type_options[ $key ]['type'];
			}
		}

		// Fall back to global attribute type
		if ( empty( $effective_type ) && $attr && ! empty( $attr->attribute_type ) ) {
			$effective_type = $attr->attribute_type;
		}

		// Normalize old labels
		if ( $effective_type === 'label' ) {
			$effective_type = 'button';
		}

		// If still no usable type, leave the original dropdown
		$our_types = array( 'color', 'image', 'button', 'radio', 'checkbox', 'select', 'select_2' );
		if ( empty( $effective_type ) || ! in_array( $effective_type, $our_types, true ) ) {
			return $html;
		}

		// Respect Settings → Swatch Types (hide disabled types)
		if ( function_exists( 'atawcvs_is_type_enabled' ) && ! atawcvs_is_type_enabled( $effective_type ) ) {
			return $html;
		}

		// Select type = keep native dropdown
		if ( in_array( $effective_type, array( 'select', 'select_2' ), true ) ) {
			return $html;
		}

	
		$options   = $args['options'];
		$product   = $args['product'];
		$attribute = $args['attribute'];
		$name      = $args['name'] ? $args['name'] : sanitize_title( $attribute );
		$id        = $args['id'] ? $args['id'] : sanitize_title( $attribute );
		$class     = isset($attr->attribute_type) ? "variation-selector variation-select-{$attr->attribute_type}" : '';
		$swatches  = '';
		$swatch_type_options = $product->get_meta('_swatch_type_options', true);
		if ( ! is_array( $swatch_type_options ) ) { $swatch_type_options = array(); }
		$meta_options = array();
	
	
		
		 
		
		
		if ( empty( $options ) && ! empty( $product ) && ! empty( $attribute ) ) {
			$attributes = $product->get_variation_attributes();
			$options    = $attributes[ $attribute ];
		}
		
		
		
		if ( ! empty( $options ) ) {
			
			$variations = $product->get_available_variations();
			$variations_id = wp_list_pluck( $variations, 'variation_id' );
			
				if ( ! empty( $options ) && $product && taxonomy_exists( $attribute ) ) {
					// Get terms if this is a taxonomy - ordered. We need the names too.
					$terms = wc_get_product_terms( $product->get_id(), $attribute, array( 'fields' => 'all' ) );
					
					/*if( $terms->is_in_stock() ) {
						echo 'hello world';
					 }*/
					$stock = $this->stock_check($product,$attribute);
					
					 
					$key = md5( sanitize_title( $attribute ) );
					
					$i = 0;
					foreach ( $terms as $term ) {
						
						if ( in_array( $term->slug, $options ) ) {
							
								$meta_key = md5( $term->slug );
								
								// Product-level override values (new simplified keys)
								// Guard against non-array / illegal string offset warnings
								if ( is_array( $swatch_type_options ) && isset( $swatch_type_options[ $key ] ) && is_array( $swatch_type_options[ $key ] ) && ! empty( $swatch_type_options[ $key ]['type'] ) ) {
									$ptype = $swatch_type_options[ $key ]['type'];
									$term_meta = ( isset( $swatch_type_options[ $key ][ $meta_key ] ) && is_array( $swatch_type_options[ $key ][ $meta_key ] ) )
										? $swatch_type_options[ $key ][ $meta_key ]
										: array();

									if ( $ptype === 'color' && ! empty( $term_meta['color'] ) ) {
										$meta_options['value'] = $term_meta['color'];
										$meta_options['type']  = 'color';
									} elseif ( $ptype === 'image' && ! empty( $term_meta['image'] ) ) {
										$meta_options['value'] = $term_meta['image'];
										$meta_options['type']  = 'image';
									} elseif ( in_array( $ptype, array( 'button', 'label', 'radio', 'checkbox', 'select' ), true ) ) {
										$meta_options['type']  = ( $ptype === 'label' ) ? 'button' : $ptype;
										$meta_options['value'] = '';
									}
								}
							//$stock['attribute_'.$attribute]	
							$meta_options['stock_checker'] = '';
							
							/*if( isset( $stock['attribute_'.$attribute] ) && $stock['attribute_'.$attribute] == $term->slug ){
								$meta_options['stock_checker'] = 'out_of_stock';
							}*/
							
							for($x = 0; $x < count($stock);$x++){
								if(isset($stock[$x]['attribute_'.$attribute]) && in_array($term->slug, $stock[$x])){
									$meta_options['stock_checker'] = 'out_of_stock';
									break;
								}
							}
							$meta_options['variations_id'] = isset( $variations_id[$i] ) ? $variations_id[$i] : '';
							
							$swatches .= apply_filters( 'atawc_swatch_html', '', $term, $attr, $args, $meta_options );
							$i ++;
						}
						
					}
				
		}else{
			$attributes = $product->get_variation_attributes();
			if ( isset($options) && $options != "" ) :
				
				$key = md5( sanitize_title( $attribute ) );
				$type = NULL;
				
				
				foreach ( $options as $option ) :
					$meta_key = ( md5( sanitize_title( strtolower( $option ) ) ) );
					
					$selected = ( sanitize_title( $args['selected'] ) === $args['selected'] &&  $args['selected'] ==  sanitize_title( $option ) ) ? 'selected' : '';
					
			
					
					if ( isset( $swatch_type_options[$key] ) && $swatch_type_options[$key]['type'] == 'product_color' ) {
						
						$meta_value =  ( isset (  $swatch_type_options[ $key ][ $meta_key ]['color'] ) && $swatch_type_options[ $key ][ $meta_key ]['color'] != "" ) ? $swatch_type_options[ $key ][ $meta_key ]['color'] :'';
						$type = 'color';
						
					}elseif(  isset( $swatch_type_options[$key] ) && $swatch_type_options[$key]['type'] == 'product_image'  ){
						
						$meta_value =  ( isset (  $swatch_type_options[ $key ][ $meta_key ]['image'] ) && $swatch_type_options[ $key ][ $meta_key ]['image'] != "" ) ? $swatch_type_options[ $key ][ $meta_key ]['image'] :'';
						$type = 'image';
					
						
					}elseif(  isset( $swatch_type_options[$key] ) && $swatch_type_options[$key]['type'] == 'product_label'  ){
						
						$meta_value = '';
						$type = 'label';
						
						
					}
					if( $type != "" )
					$swatches .= apply_filters( 'atawc_swatch_meta_html', '',$type, $option, $meta_value, $selected );
				endforeach;
				
			endif;	
			
		}
			
			if ( ! empty( $swatches ) ) {
				$class .= ' hidden';

				$swatches = '<div class="atawc-swatches" data-attribute_name="attribute_' . esc_attr( $attribute ) . '">' . $swatches . '</div>';
				$html     = '<div class="' . esc_attr( $class ) . '">' . $html . '</div>' . $swatches;
			}
		}
	
		return $html;
	}
	
	
	public function stock_check($product , $name ){
		$array = array();
		if( !empty( $product ) && !empty( $name ) ){
		$variations = $product->get_available_variations();
		
			foreach ($variations as $variation) {
				if( $variation['is_in_stock'] == false ){
					$array[] = $variation['attributes'];
				}
			}
		}
		return $array;
	}
		/**
	 * Print HTML of a single swatch
	 *
	 * @param $html
	 * @param $term
	 * @param $attr
	 * @param $args
	 *
	 * @return string
	 */
	public function atawc_swatch_meta_html( $html, $type, $meta_option, $meta_value , $selected = NULL ) {
		
	
		
	
		switch ( $type ) {
			
			case 'color':
			
				$options = atawcvs_get_option('atawc_color');
				$width = ( isset( $options['color_variation_width'] ) && $options['color_variation_width'] != "" ) ? $options['color_variation_width'] : 40 ;
				$height = ( isset( $options['color_variation_height'] ) && $options['color_variation_height'] != "" ) ? $options['color_variation_height'] : 40 ;
				$style = ( isset( $options['color_variation_style'] ) && $options['color_variation_style'] != "" ) ? $options['color_variation_style'] : 'round' ;
				$active = ( isset( $options['color_variation_ingredient'] ) && $options['color_variation_ingredient'] != "" ) ? $options['color_variation_ingredient'] : 'tick_sign' ;
				//masterTooltip 
				$tooltip = ( isset( $options['color_variation_tooltip'] ) && $options['color_variation_tooltip'] == "yes" ) ? 'masterTooltip' : '' ;
				
				
				$html = sprintf(
					'<span class="swatch swatch-color swatch-%s %s %s %s %s" style="background-color:%s; width:%spx; height:%spx;" title="%s" data-value="%s">%s</span>',
					esc_attr( $meta_option ),
					$selected,
					$style,
					$active,
					$tooltip,
					esc_attr( $meta_value ),
					$width,
					$height,
					esc_attr( $meta_option ),
					esc_attr( $meta_option ),
					$meta_option
				);
				break;
				case 'image':
				$options = atawcvs_get_option('atawc_images');
				$width = ( !empty( $options['image_variation_width'] ) ) ? 'width:'.esc_attr($options['image_variation_width']).'px' : '';
				$height = ( !empty( $options['image_variation_height'] ) ) ? 'height:'.esc_attr($options['image_variation_height']).'px' : '' ;

				$style = ( isset( $options['image_variation_style'] ) && $options['image_variation_style'] != "" ) ? $options['image_variation_style'] : 'round_corner' ;
				$active = ( isset( $options['image_variation_ingredient'] ) && $options['image_variation_ingredient'] != "" ) ? $options['image_variation_ingredient'] : 'tick_sign' ;
				
				$tooltip = ( isset( $options['image_variation_tooltip'] ) && $options['image_variation_tooltip'] == "yes" ) ? 'masterTooltip' : '' ;
				
				$image = $meta_value;
				$image = $image ? wp_get_attachment_image_src( $image ) : '';
				$image = $image ? $image[0] : WC()->plugin_url() . '/assets/images/placeholder.png';

				
				$html  = sprintf(
					'<span class="swatch swatch-image swatch-%s %s %s %s %s" title="%s" data-value="%s"  style="%s; %s;"><img src="%s" alt="%s">%s</span>',
					!empty($term->slug)? $term->slug:'',
					$selected,
					$style,
					$active,
					$tooltip,
					esc_attr( $meta_option ),
					esc_attr( $meta_option ),
					$width,
					$height,
					esc_url( $image ),
					esc_attr( $meta_option ),
					esc_attr( $meta_option )
				);
				break;
				
				case 'label':
				case 'button':
			
				$options = atawcvs_get_option('atawc_label');
				$width = ( isset( $options['lebel_variation_width'] ) && $options['lebel_variation_width'] != "" ) ? $options['lebel_variation_width'] : 44 ;
				$height = ( isset( $options['lebel_variation_height'] ) && $options['lebel_variation_height'] != "" ) ? $options['lebel_variation_height'] : 44 ;
				$style = ( isset( $options['lebel_variation_style'] ) && $options['lebel_variation_style'] != "" ) ? $options['lebel_variation_style'] : 'square' ;
				
				$active = ( isset( $options['lebel_variation_ingredient'] ) && $options['lebel_variation_ingredient'] != "" ) ? $options['lebel_variation_ingredient'] : 'opacity' ;
				
				
				$html  = sprintf(
					'<span class="swatch swatch-label swatch-%s %s %s %s" title="%s" data-value="%s"  style="min-width:%spx; height:%spx; line-height:%spx;">%s</span>',
					esc_attr( $meta_option ),
					$selected,
					$style,
					$active,
					esc_attr( $meta_option ),
					esc_attr( $meta_option ),
					$width,
					$height,
					$height,
					esc_html( $meta_option )
				);
				break;

				case 'radio':
					$html = sprintf(
						'<span class="swatch swatch-radio %s" title="%s" data-value="%s" role="radio" aria-checked="%s" tabindex="0"><input type="radio" name="ata_radio_%s" value="%s" %s data-value="%s" tabindex="-1" /> <span class="swatch-text">%s</span></span>',
						$selected,
						esc_attr( $meta_option ),
						esc_attr( $meta_option ),
						$selected ? 'true' : 'false',
						esc_attr( $meta_option ),
						esc_attr( $meta_option ),
						$selected ? 'checked="checked"' : '',
						esc_attr( $meta_option ),
						esc_html( $meta_option )
					);
					break;

				case 'checkbox':
					$html = sprintf(
						'<span class="swatch swatch-checkbox %s" title="%s" data-value="%s" role="checkbox" aria-checked="%s" tabindex="0"><input type="checkbox" value="%s" %s data-value="%s" tabindex="-1" /> <span class="swatch-text">%s</span></span>',
						$selected,
						esc_attr( $meta_option ),
						esc_attr( $meta_option ),
						$selected ? 'true' : 'false',
						esc_attr( $meta_option ),
						$selected ? 'checked="checked"' : '',
						esc_attr( $meta_option ),
						esc_html( $meta_option )
					);
					break;
				
		}

		return $html;
	}
	
	/**
	 * Print HTML of a single swatch
	 *
	 * @param $html
	 * @param $term
	 * @param $attr
	 * @param $args
	 *
	 * @return string
	 */
	public function swatch_html( $html, $term, $attr, $args, $swatch_meta_options = array() ) {
	
		
		$selected = sanitize_title( $args['selected'] ) == $term->slug ? 'selected  '.$swatch_meta_options['stock_checker'] : $swatch_meta_options['stock_checker'];
		$name     = esc_html( apply_filters( 'woocommerce_variation_option_name', $term->name ) );
	
		$variations_id =!empty( $swatch_meta_options['variations_id'] ) ? $swatch_meta_options['variations_id'] : '';
		
		$type =  isset( $swatch_meta_options['type'] ) ? $swatch_meta_options['type'] : $attr->attribute_type;
		
		if( $type == 'select' ){
			//$type = 'label';
		}
		
		switch ( $type ) {
			case 'color':
				$sv = $this->get_style_vars();
				// Archive can override size
				if ( is_shop() || is_product_category() ) {
					$arch = atawcvs_get_option( 'archive_settings' );
					if ( ! empty( $arch['__swatches_archive_width'] ) ) {
						$sv['width'] = absint( $arch['__swatches_archive_width'] );
					}
					if ( ! empty( $arch['__swatches_archive_height'] ) ) {
						$sv['height'] = absint( $arch['__swatches_archive_height'] );
					}
					if ( ! empty( $arch['__archive_variation_style'] ) ) {
						$sv['shape'] = $arch['__archive_variation_style'];
					}
					if ( isset( $arch['__swatches_archive_tooltip'] ) && $arch['__swatches_archive_tooltip'] !== 'on' ) {
						$sv['tooltip'] = '';
					}
				}
				$color = isset( $swatch_meta_options['value'] ) ? $swatch_meta_options['value'] : get_term_meta( $term->term_id, 'color', true );
				if ( empty( $color ) ) {
					$color = '#cccccc';
				}
				$html = sprintf(
					'<span class="swatch swatch-color swatch-%s %s %s %s" style="background-color:%s;width:%dpx;height:%dpx;" title="%s" data-value="%s" data-variations_id="%s"></span>',
					esc_attr( $term->slug ),
					esc_attr( $selected ),
					esc_attr( $sv['shape'] ),
					esc_attr( $sv['tooltip'] ),
					esc_attr( $color ),
					(int) $sv['width'],
					(int) $sv['height'],
					esc_attr( $name ),
					esc_attr( $term->slug ),
					esc_attr( $variations_id )
				);
				break;

			case 'image':
				$sv = $this->get_style_vars();
				if ( is_shop() || is_product_category() ) {
					$arch = atawcvs_get_option( 'archive_settings' );
					if ( ! empty( $arch['__swatches_archive_width'] ) ) {
						$sv['width'] = absint( $arch['__swatches_archive_width'] );
					}
					if ( ! empty( $arch['__swatches_archive_height'] ) ) {
						$sv['height'] = absint( $arch['__swatches_archive_height'] );
					}
					if ( ! empty( $arch['__archive_variation_style'] ) ) {
						$sv['shape'] = $arch['__archive_variation_style'];
					}
					if ( isset( $arch['__swatches_archive_tooltip'] ) && $arch['__swatches_archive_tooltip'] !== 'on' ) {
						$sv['tooltip'] = '';
					}
				}
				$image = isset( $swatch_meta_options['value'] ) ? $swatch_meta_options['value'] : get_term_meta( $term->term_id, 'image', true );
				$image = $image ? wp_get_attachment_image_src( $image ) : '';
				$image = $image ? $image[0] : WC()->plugin_url() . '/assets/images/placeholder.png';
				$html  = sprintf(
					'<span class="swatch swatch-image swatch-%s %s %s %s" title="%s" data-value="%s" style="width:%dpx;height:%dpx;"><img src="%s" alt="%s" data-variations_id="%s"></span>',
					esc_attr( $term->slug ),
					esc_attr( $selected ),
					esc_attr( $sv['shape'] ),
					esc_attr( $sv['tooltip'] ),
					esc_attr( $name ),
					esc_attr( $term->slug ),
					(int) $sv['width'],
					(int) $sv['height'],
					esc_url( $image ),
					esc_attr( $name ),
					esc_attr( $variations_id )
				);
				break;


			case 'label':
			case 'button':
				$sv = $this->get_style_vars();
				if ( is_shop() || is_product_category() ) {
					$arch = atawcvs_get_option( 'archive_settings' );
					if ( ! empty( $arch['__swatches_archive_width'] ) ) {
						$sv['width'] = absint( $arch['__swatches_archive_width'] );
					}
					if ( ! empty( $arch['__swatches_archive_height'] ) ) {
						$sv['height'] = absint( $arch['__swatches_archive_height'] );
					}
					if ( ! empty( $arch['__archive_variation_style'] ) ) {
						$sv['shape'] = $arch['__archive_variation_style'];
					}
					if ( isset( $arch['__swatches_archive_tooltip'] ) && $arch['__swatches_archive_tooltip'] !== 'on' ) {
						$sv['tooltip'] = '';
					}
				}
				$html  = sprintf(
					'<span class="swatch swatch-label swatch-%s %s %s %s" title="%s" data-value="%s" style="min-width:%dpx;height:%dpx;line-height:%dpx;" data-variations_id="%s">%s</span>',
					esc_attr( $term->slug ),
					esc_attr( $selected ),
					esc_attr( $sv['shape'] ),
					esc_attr( $sv['tooltip'] ),
					esc_attr( $name ),
					esc_attr( $term->slug ),
					(int) $sv['width'],
					(int) $sv['height'],
					(int) $sv['height'],
					esc_attr( $variations_id ),
					esc_html( $name )
				);
				break;

			case 'radio':
				$html = sprintf(
					'<span class="swatch swatch-radio %s" title="%s" data-value="%s" role="radio" aria-checked="%s" tabindex="0"><input type="radio" name="attribute_radio_%s" value="%s" %s data-value="%s" tabindex="-1" /> <span class="swatch-text">%s</span></span>',
					$selected,
					esc_attr( $name ),
					esc_attr( $term->slug ),
					$selected ? 'true' : 'false',
					esc_attr( $term->taxonomy ),
					esc_attr( $term->slug ),
					$selected ? 'checked="checked"' : '',
					esc_attr( $term->slug ),
					esc_html( $name )
				);
				break;

			case 'checkbox':
				$html = sprintf(
					'<span class="swatch swatch-checkbox %s" title="%s" data-value="%s" role="checkbox" aria-checked="%s" tabindex="0"><input type="checkbox" value="%s" %s data-value="%s" tabindex="-1" /> <span class="swatch-text">%s</span></span>',
					$selected,
					esc_attr( $name ),
					esc_attr( $term->slug ),
					$selected ? 'true' : 'false',
					esc_attr( $term->slug ),
					$selected ? 'checked="checked"' : '',
					esc_attr( $term->slug ),
					esc_html( $name )
				);
				break;
				
				default:
				
				/*$options = ( is_shop() || is_product_category() ) ? atawcvs_get_option('archive_settings') : atawcvs_get_option('atawc_label');
				$width = ( isset( $options['lebel_variation_width'] ) && $options['lebel_variation_width'] != "" ) ? $options['lebel_variation_width'] : 44 ;
				$height = ( isset( $options['lebel_variation_height'] ) && $options['lebel_variation_height'] != "" ) ? $options['lebel_variation_height'] : 44 ;
				$style = ( isset( $options['lebel_variation_style'] ) && $options['lebel_variation_style'] != "" ) ? $options['lebel_variation_style'] : 'square' ;
				
				$active = ( isset( $options['lebel_variation_ingredient'] ) && $options['lebel_variation_ingredient'] != "" ) ? $options['lebel_variation_ingredient'] : 'opacity' ;
				
				
				$tooltip = ( isset( $options['lebel_variation_tooltip'] ) && $options['lebel_variation_tooltip'] == "yes" ) ? 'masterTooltip' : '' ;
				
				$html  = sprintf(
					'<span class="swatch swatch-label swatch-%s %s %s %s %s" title="%s" data-value="%s"  style="min-width:%spx; height:%spx; line-height:%spx;">%s</span>',
					esc_attr( $term->slug ),
					$selected,
					$style,
					$active,
					$tooltip,
					esc_attr( $name ),
					esc_attr( $term->slug ),
					$width,
					$height,
					$height,
					esc_html( $name )
				);*/
				
		}

		return $html;
	}
}