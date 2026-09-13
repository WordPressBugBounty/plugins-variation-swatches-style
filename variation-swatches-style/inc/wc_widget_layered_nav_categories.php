<?php
/**
 * Smart Swatches Filter Products Widget
 *
 * Compatible with classic widgets AND the block-based (Gutenberg) widget editor.
 * Uses a plain attribute slug (not JSON) so REST / block editor can save settings reliably.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Smart_Filter_Widget' ) ) {

	/**
	 * Filter products by attribute swatches (color, image, button, etc.)
	 */
	class Smart_Filter_Widget extends WP_Widget {

		/**
		 * Constructor
		 */
		public function __construct() {
			parent::__construct(
				'smart_swatches_filter', // id_base – must be stable for block editor
				__( 'Smart Swatches Filter Products', 'variation-swatches-style' ),
				array(
					'description'           => __( 'Filter shop products by color, image, or label swatches.', 'variation-swatches-style' ),
					'classname'             => 'widget_smart_swatches_filter',
					'show_instance_in_rest' => true, // critical for block widget editor
				)
			);
		}

		/**
		 * Available product attributes as name => label
		 *
		 * @return array
		 */
		protected function get_attribute_options() {
			$options = array( '' => __( '— Select attribute —', 'variation-swatches-style' ) );

			if ( ! function_exists( 'wc_get_attribute_taxonomies' ) ) {
				return $options;
			}

			$taxonomies = wc_get_attribute_taxonomies();
			if ( empty( $taxonomies ) ) {
				return $options;
			}

			foreach ( $taxonomies as $tax ) {
				// Only show attributes that have a swatch type (or all – safer to show all)
				$key = $tax->attribute_name; // e.g. "color"
				$label = ! empty( $tax->attribute_label ) ? $tax->attribute_label : $tax->attribute_name;
				$type  = ! empty( $tax->attribute_type ) ? $tax->attribute_type : 'select';
				$options[ $key ] = sprintf( '%s (%s)', $label, $type );
			}

			return $options;
		}

		/**
		 * Get attribute object by name
		 *
		 * @param string $attribute_name e.g. "color"
		 * @return object|null
		 */
		protected function get_attribute( $attribute_name ) {
			if ( empty( $attribute_name ) || ! function_exists( 'wc_get_attribute_taxonomies' ) ) {
				return null;
			}
			foreach ( wc_get_attribute_taxonomies() as $tax ) {
				if ( $tax->attribute_name === $attribute_name ) {
					return $tax;
				}
			}
			return null;
		}

		/**
		 * Backend form (classic + block editor)
		 *
		 * @param array $instance
		 */
		public function form( $instance ) {
			$title          = isset( $instance['title'] ) ? $instance['title'] : __( 'Filter Products by', 'variation-swatches-style' );
			$attribute_name = isset( $instance['attribute_name'] ) ? $instance['attribute_name'] : '';

			// Backward compat: old versions stored JSON in attribute_id
			if ( empty( $attribute_name ) && ! empty( $instance['attribute_id'] ) ) {
				$decoded = json_decode( $instance['attribute_id'] );
				if ( $decoded && ! empty( $decoded->attribute_name ) ) {
					$attribute_name = $decoded->attribute_name;
				}
			}

			$options = $this->get_attribute_options();
			?>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
					<?php esc_html_e( 'Title:', 'variation-swatches-style' ); ?>
				</label>
				<input class="widefat"
					id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
					name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
					type="text"
					value="<?php echo esc_attr( $title ); ?>" />
			</p>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'attribute_name' ) ); ?>">
					<?php esc_html_e( 'Attribute:', 'variation-swatches-style' ); ?>
				</label>
				<select class="widefat"
					id="<?php echo esc_attr( $this->get_field_id( 'attribute_name' ) ); ?>"
					name="<?php echo esc_attr( $this->get_field_name( 'attribute_name' ) ); ?>">
					<?php foreach ( $options as $value => $label ) : ?>
						<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $attribute_name, $value ); ?>>
							<?php echo esc_html( $label ); ?>
						</option>
					<?php endforeach; ?>
				</select>
				<small style="display:block;margin-top:4px;color:#666;">
					<?php esc_html_e( 'Choose a product attribute that uses Color, Image, or Button swatches.', 'variation-swatches-style' ); ?>
				</small>
			</p>
			<?php
		}

		/**
		 * Save settings
		 *
		 * @param array $new_instance
		 * @param array $old_instance
		 * @return array
		 */
		public function update( $new_instance, $old_instance ) {
			$instance = array();
			$instance['title']          = isset( $new_instance['title'] ) ? sanitize_text_field( $new_instance['title'] ) : '';
			$instance['attribute_name'] = isset( $new_instance['attribute_name'] ) ? sanitize_title( $new_instance['attribute_name'] ) : '';
			return $instance;
		}

		/**
		 * Render a single swatch link
		 */
		protected function swatch_html( $type, $url, $label, $meta_value = '' ) {
			$selected = '';
			$html     = '';

			switch ( $type ) {
				case 'color':
					$color  = $meta_value ? $meta_value : '#ccc';
					$options = function_exists( 'atawcvs_get_option' ) ? atawcvs_get_option( 'atawc_color' ) : array();
					$width  = ! empty( $options['color_variation_width'] ) ? absint( $options['color_variation_width'] ) : 30;
					$height = ! empty( $options['color_variation_height'] ) ? absint( $options['color_variation_height'] ) : 30;
					$style  = ! empty( $options['color_variation_style'] ) ? $options['color_variation_style'] : 'round';
					$html   = sprintf(
						'<li><a href="%s" class="swatch swatch-color %s" title="%s" style="background-color:%s;width:%dpx;height:%dpx;"></a></li>',
						esc_url( $url ),
						esc_attr( $style ),
						esc_attr( $label ),
						esc_attr( $color ),
						$width,
						$height
					);
					break;

				case 'image':
					$image_id = absint( $meta_value );
					$image    = $image_id ? wp_get_attachment_image_url( $image_id, 'thumbnail' ) : '';
					if ( ! $image && function_exists( 'wc_placeholder_img_src' ) ) {
						$image = wc_placeholder_img_src( 'thumbnail' );
					}
					$html = sprintf(
						'<li><a href="%s" class="swatch swatch-image" title="%s"><img src="%s" alt="%s" width="40" height="40" style="object-fit:cover;border-radius:4px;" /></a></li>',
						esc_url( $url ),
						esc_attr( $label ),
						esc_url( $image ),
						esc_attr( $label )
					);
					break;

				case 'button':
				case 'label':
				default:
					$html = sprintf(
						'<li><a href="%s" class="swatch swatch-label" title="%s">%s</a></li>',
						esc_url( $url ),
						esc_attr( $label ),
						esc_html( $label )
					);
					break;
			}

			return $html;
		}

		/**
		 * Frontend output
		 *
		 * @param array $args
		 * @param array $instance
		 */
		public function widget( $args, $instance ) {
			$title          = ! empty( $instance['title'] ) ? $instance['title'] : '';
			$attribute_name = ! empty( $instance['attribute_name'] ) ? $instance['attribute_name'] : '';

			// Backward compat with old JSON storage
			if ( empty( $attribute_name ) && ! empty( $instance['attribute_id'] ) ) {
				$decoded = json_decode( $instance['attribute_id'] );
				if ( $decoded && ! empty( $decoded->attribute_name ) ) {
					$attribute_name = $decoded->attribute_name;
				}
			}

			if ( empty( $attribute_name ) ) {
				return;
			}

			$attr = $this->get_attribute( $attribute_name );
			if ( ! $attr ) {
				return;
			}

			$taxonomy = 'pa_' . $attribute_name;
			$terms    = get_terms(
				array(
					'taxonomy'   => $taxonomy,
					'hide_empty' => true,
				)
			);

			if ( is_wp_error( $terms ) || empty( $terms ) ) {
				return;
			}

			$type = ! empty( $attr->attribute_type ) ? $attr->attribute_type : 'select';

			// Base shop URL
			$shop_url = get_permalink( wc_get_page_id( 'shop' ) );
			if ( ! $shop_url ) {
				$shop_url = home_url( '/' );
			}

			echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

			if ( $title ) {
				echo $args['before_title'] . esc_html( apply_filters( 'widget_title', $title ) ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}

			echo esc_html__('This filter widget is available only in the Pro version. Please upgrade to Pro to unlock all features.','variation-swatches-style');

			echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}
}

/**
 * Register widget (classic + block editor)
 */
function smart_filter_register_widget() {
	register_widget( 'Smart_Filter_Widget' );
}
add_action( 'widgets_init', 'smart_filter_register_widget', 20 );

/**
 * Ensure this widget is available in the Legacy Widget block (block editor).
 * Some setups hide custom widgets; we force it to stay visible.
 */
add_filter(
	'widget_types_to_hide_from_legacy_widget_block',
	function ( $widget_types ) {
		// Remove our id_base from the hide list if present
		$widget_types = array_diff( (array) $widget_types, array( 'smart_swatches_filter', 'smart-swatches-filter-products' ) );
		return array_values( $widget_types );
	}
);

/**
 * Product filter query (shop / category)
 */
function smart_swatches_shop_filter_cat( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( empty( $_GET['smart_taxonomy'] ) || empty( $_GET['slug_terms'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}

	if ( ! ( is_shop() || is_product_taxonomy() || is_product_category() ) ) {
		return;
	}

	$taxonomy = sanitize_text_field( wp_unslash( $_GET['smart_taxonomy'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$term     = sanitize_title( wp_unslash( $_GET['slug_terms'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	if ( ! taxonomy_exists( $taxonomy ) ) {
		return;
	}

	$tax_query   = (array) $query->get( 'tax_query' );
	$tax_query[] = array(
		'taxonomy' => $taxonomy,
		'field'    => 'slug',
		'terms'    => $term,
	);
	$query->set( 'tax_query', $tax_query );
}
add_action( 'pre_get_posts', 'smart_swatches_shop_filter_cat' );
