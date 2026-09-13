<?php
/**
 * Defaults + option helpers (free)
 */

if ( ! function_exists( 'atawcvs_get_default_theme_options' ) ) :

	function atawcvs_get_default_theme_options() {
		$defaults = array();

		$defaults['atawc_color'] = array(
			'color_variation_style'   => 'round',
			'color_variation_width'   => 36,
			'color_variation_height'  => 36,
			'color_variation_tooltip' => 'yes',
		);
		$defaults['atawc_images'] = array(
			'image_variation_style'   => 'round',
			'image_variation_width'   => 36,
			'image_variation_height'  => 36,
			'image_variation_tooltip' => 'yes',
		);
		$defaults['atawc_label'] = array(
			'lebel_variation_style'            => 'square',
			'lebel_variation_width'            => 40,
			'lebel_variation_height'           => 30,
			'lebel_variation_size'             => '13',
			'lebel_variation_color'            => '#333333',
			'lebel_variation_color_hover'      => '#ffffff',
			'lebel_variation_background'       => '#f1f1f1',
			'lebel_variation_background_hover' => '#333333',
			'lebel_variation_border'           => '#dddddd',
			'lebel_variation_border_hover'     => '#333333',
			'lebel_variation_ingredient'       => 'opacity',
			'lebel_variation_tooltip'          => 'yes',
		);

		$defaults['general_settings'] = array(
			'__enable_swatches'   => 'on',
			'__price_update_on'   => 'price',
			'__clear_on_reselect' => 'on',
		);

		$defaults['swatch_types'] = array(
			'enable_color'  => 'on',
			'enable_image'  => 'on',
			'enable_button' => 'on',
			'enable_select' => 'on',
		);

		$defaults['style_settings'] = array(
			'swatch_shape'          => 'round',
			'swatch_width'          => 36,
			'swatch_height'         => 36,
			'swatch_gap'            => 8,
			'selected_border_color' => '#333333',
			'hover_border_color'    => '#2271b1',
			'button_bg'             => '#f1f1f1',
			'button_text'           => '#333333',
			'button_bg_selected'    => '#333333',
			'button_text_selected'  => '#ffffff',
		);

		$defaults['tooltip_settings'] = array(
			'enable_tooltip'     => 'on',
			'tooltip_text_color' => '#ffffff',
			'tooltip_bg'         => '#222222',
		);

		$defaults['variations_settings'] = array(
			'show_attribute_label' => 'on',
			'hide_original_select' => 'on',
		);

		$defaults['availability_settings'] = array(
			'out_of_stock_style' => 'cross',
		);

		$defaults['archive_settings'] = array(
			'__swatches_display_on_archive'       => 'off',
			'__swatches_archive_behavior'         => 'add_to_cart',
			'__swatches_archive_label'            => 'off',
			'__swatches_display_position_on_arch' => 'before_add_to_cart',
			'__swatches_archive_tooltip'          => 'on',
			'__swatches_archive_width'            => '28',
			'__archive_variation_style'           => 'round',
			'__swatches_archive_height'           => '28',
		);

		$defaults['advanced_settings'] = array(
			'custom_css'        => '',
			'disable_on_mobile' => 'off',
		);

		return $defaults;
	}

endif;

if ( ! function_exists( 'atawcvs_get_option' ) ) :

	function atawcvs_get_option( $key ) {
		if ( empty( $key ) ) {
			return array();
		}
		$defaults = atawcvs_get_default_theme_options();
		$default  = isset( $defaults[ $key ] ) ? $defaults[ $key ] : array();
		$value    = get_option( $key, array() );
		if ( ! is_array( $value ) ) {
			$value = array();
		}
		if ( is_array( $default ) ) {
			return array_merge( $default, $value );
		}
		return ! empty( $value ) ? $value : $default;
	}

endif;

if ( ! function_exists( 'atawcvs_get_setting' ) ) :

	function atawcvs_get_setting( $group, $name, $fallback = '' ) {
		$opts = atawcvs_get_option( $group );
		if ( is_array( $opts ) && array_key_exists( $name, $opts ) && $opts[ $name ] !== '' ) {
			return $opts[ $name ];
		}
		return $fallback;
	}

endif;

if ( ! function_exists( 'atawcvs_is_type_enabled' ) ) :

	function atawcvs_is_type_enabled( $type ) {
		$map = array(
			'color'    => 'enable_color',
			'image'    => 'enable_image',
			'button'   => 'enable_button',
			'label'    => 'enable_button',
			'select'   => 'enable_select',
			'select_2' => 'enable_select',
		);
		// Free version: only mapped types are available
		if ( ! isset( $map[ $type ] ) ) {
			return false;
		}
		$val = atawcvs_get_setting( 'swatch_types', $map[ $type ], 'on' );
		return ( $val === 'on' || $val === '1' || $val === true );
	}

endif;
