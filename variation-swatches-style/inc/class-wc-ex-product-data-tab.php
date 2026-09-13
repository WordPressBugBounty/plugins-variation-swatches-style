<?php
/**
 * Product Data Tab – Custom Swatches
 *
 * - Global type exists → show "Using Global Settings" only
 * - No global type → allow product-level type + term config
 * - Color type → show only color pickers
 * - Image type → show only image uploads
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_EX_Product_Data_Tab_Swatches {

	protected static $instance = null;
	public $meta_name = '_swatch_type_options';

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __construct() {
		add_filter( 'woocommerce_product_data_tabs', array( $this, 'add_product_data_tab' ), 99, 1 );
		add_action( 'woocommerce_product_data_panels', array( $this, 'product_data_panel' ) );
		add_action( 'woocommerce_process_product_meta', array( $this, 'save_product_meta' ), 10, 2 );
	}

	public function add_product_data_tab( $tabs ) {
		$tabs['woo-variations-style'] = array(
			'label'  => __( 'Custom Swatches', 'variation-swatches-style' ),
			'target' => 'woo_variations_style',
			'class'  => array( 'show_if_variable' ),
		);
		return $tabs;
	}

	public function product_data_panel() {
		?>
		<div id="woo_variations_style" class="panel woocommerce_options_panel hidden">
			<?php $this->render_tab_content(); ?>
		</div>
		<?php
	}

	private function get_type_label( $type ) {
		$labels = array(
			'color'    => __( 'Color', 'variation-swatches-style' ),
			'image'    => __( 'Image', 'variation-swatches-style' ),
			'button'   => __( 'Button / Label', 'variation-swatches-style' ),
			'label'    => __( 'Button / Label', 'variation-swatches-style' ),
			'radio'    => __( 'Radio', 'variation-swatches-style' ),
			'checkbox' => __( 'Checkbox', 'variation-swatches-style' ),
			'select'   => __( 'Select Box', 'variation-swatches-style' ),
			'select_2' => __( 'Select Box', 'variation-swatches-style' ),
		);
		return isset( $labels[ $type ] ) ? $labels[ $type ] : ucfirst( $type );
	}

	private function get_global_attribute_type( $taxonomy ) {
		if ( ! taxonomy_exists( $taxonomy ) ) {
			return '';
		}
		$attr = ATA_WCVS()->get_tax_attribute( $taxonomy );
		if ( $attr && ! empty( $attr->attribute_type ) ) {
			$our_types = array( 'color', 'image', 'button', 'label', 'radio', 'checkbox', 'select', 'select_2' );
			if ( in_array( $attr->attribute_type, $our_types, true ) ) {
				return $attr->attribute_type;
			}
		}
		return '';
	}

	public function render_tab_content() {
		global $post;

		$product = wc_get_product( $post->ID );
		if ( ! $product || ! $product->is_type( array( 'variable', 'variable-subscription' ) ) ) {
			echo '<div class="options_group"><p>' . esc_html__( 'This tab is only available for Variable products.', 'variation-swatches-style' ) . '</p></div>';
			return;
		}

		$attributes = $product->get_variation_attributes();
		if ( empty( $attributes ) ) {
			echo '<div class="options_group"><p>' . esc_html__( 'No variation attributes found. Add attributes and create variations first.', 'variation-swatches-style' ) . '</p></div>';
			return;
		}

		$swatch_type_options = $product->get_meta( $this->meta_name, true );
		if ( ! is_array( $swatch_type_options ) ) {
			$swatch_type_options = array();
		}

		$tax_infos = array();
		foreach ( wc_get_attribute_taxonomies() as $tax ) {
			$tax_infos[ wc_attribute_taxonomy_name( $tax->attribute_name ) ] = $tax;
		}
		?>
		<div class="options_group" style="padding:12px 16px;">
			<div style="background:#f0f6fc;border-left:4px solid #2271b1;padding:12px 16px;margin-bottom:16px;border-radius:0 6px 6px 0;font-size:13px;">
				<strong><?php esc_html_e( 'How this works', 'variation-swatches-style' ); ?></strong><br>
				<?php esc_html_e( 'If an attribute already has a type under Products → Attributes, this product uses it automatically. Configure here only when there is no global type.', 'variation-swatches-style' ); ?>
			</div>

			<?php foreach ( $attributes as $attribute_name => $term_slugs ) :
				$key         = md5( sanitize_title( $attribute_name ) );
				$is_taxonomy = taxonomy_exists( $attribute_name );
				$global_type = $is_taxonomy ? $this->get_global_attribute_type( $attribute_name ) : '';
				$has_global  = ! empty( $global_type );

				$label = $attribute_name;
				if ( $is_taxonomy && isset( $tax_infos[ $attribute_name ] ) ) {
					$tax_obj = $tax_infos[ $attribute_name ];
					$label   = ! empty( $tax_obj->attribute_label ) ? $tax_obj->attribute_label : $tax_obj->attribute_name;
				}

				$product_type = '';
				if ( isset( $swatch_type_options[ $key ] ) && is_array( $swatch_type_options[ $key ] ) && ! empty( $swatch_type_options[ $key ]['type'] ) ) {
					$product_type = $swatch_type_options[ $key ]['type'];
				}
				?>
				<div class="ata-attr-block" style="border:1px solid #c3c4c7;border-radius:6px;margin-bottom:16px;background:#fff;" data-key="<?php echo esc_attr( $key ); ?>">
					<div style="padding:12px 16px;border-bottom:1px solid #eee;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
						<div>
							<strong style="font-size:14px;"><?php echo esc_html( $label ); ?></strong>
							<?php if ( $is_taxonomy ) : ?>
								<code style="font-size:11px;color:#666;margin-left:6px;"><?php echo esc_html( $attribute_name ); ?></code>
							<?php endif; ?>
						</div>
						<?php if ( $has_global ) : ?>
							<span style="background:#edfaef;color:#1e7e34;padding:4px 10px;border-radius:4px;font-size:12px;">
								<span class="dashicons dashicons-yes-alt" style="font-size:14px;width:14px;height:14px;vertical-align:middle;"></span>
								<?php
								printf(
									esc_html__( 'Using Global Settings: %s', 'variation-swatches-style' ),
									'<strong>' . esc_html( $this->get_type_label( $global_type ) ) . '</strong>'
								);
								?>
							</span>
						<?php endif; ?>
					</div>

					<div style="padding:12px 16px;">
						<?php if ( $has_global ) : ?>
							<p style="margin:0;color:#666;font-size:13px;">
								<?php esc_html_e( 'This attribute is configured globally under Products → Attributes. No product-level changes are needed.', 'variation-swatches-style' ); ?>
							</p>
						<?php else : ?>
							<p style="margin:0 0 12px;">
								<label style="margin:0px;">
									<strong><?php esc_html_e( 'Swatch Type', 'variation-swatches-style' ); ?></strong><br>
									<select name="_swatch_type_options[<?php echo esc_attr( $key ); ?>][type]"
										class="ata-product-swatch-type"
										data-key="<?php echo esc_attr( $key ); ?>"
										style="min-width:200px;margin-top:4px;">
										<option value=""><?php esc_html_e( '— Select type —', 'variation-swatches-style' ); ?></option>
										<?php if ( ! function_exists( 'atawcvs_is_type_enabled' ) || atawcvs_is_type_enabled( 'color' ) ) : ?>
										<option value="color"    <?php selected( $product_type, 'color' ); ?>><?php esc_html_e( 'Color', 'variation-swatches-style' ); ?></option>
										<?php endif; ?>
										<?php if ( ! function_exists( 'atawcvs_is_type_enabled' ) || atawcvs_is_type_enabled( 'image' ) ) : ?>
										<option value="image"    <?php selected( $product_type, 'image' ); ?>><?php esc_html_e( 'Image', 'variation-swatches-style' ); ?></option>
										<?php endif; ?>
										<?php if ( ! function_exists( 'atawcvs_is_type_enabled' ) || atawcvs_is_type_enabled( 'button' ) ) : ?>
										<option value="button"   <?php selected( $product_type, 'button' ); ?>><?php esc_html_e( 'Button / Label', 'variation-swatches-style' ); ?></option>
										<?php endif; ?>
										<?php if ( ! function_exists( 'atawcvs_is_type_enabled' ) || atawcvs_is_type_enabled( 'radio' ) ) : ?>
										<option value="radio"    <?php selected( $product_type, 'radio' ); ?>><?php esc_html_e( 'Radio', 'variation-swatches-style' ); ?></option>
										<?php endif; ?>
										<?php if ( ! function_exists( 'atawcvs_is_type_enabled' ) || atawcvs_is_type_enabled( 'checkbox' ) ) : ?>
										<option value="checkbox" <?php selected( $product_type, 'checkbox' ); ?>><?php esc_html_e( 'Checkbox', 'variation-swatches-style' ); ?></option>
										<?php endif; ?>
										<?php if ( ! function_exists( 'atawcvs_is_type_enabled' ) || atawcvs_is_type_enabled( 'select' ) ) : ?>
										<option value="select"   <?php selected( $product_type, 'select' ); ?>><?php esc_html_e( 'Select Box', 'variation-swatches-style' ); ?></option>
										<?php endif; ?>
									</select>
								</label>
							</p>

							<?php
							// Build term list
							$terms = array();
							if ( $is_taxonomy ) {
								$all_terms = get_terms( array( 'taxonomy' => $attribute_name, 'hide_empty' => false ) );
								if ( ! is_wp_error( $all_terms ) ) {
									foreach ( $all_terms as $term ) {
										if ( in_array( $term->slug, $term_slugs, true ) ) {
											$terms[] = $term;
										}
									}
								}
							} else {
								foreach ( $term_slugs as $slug ) {
									$terms[] = (object) array( 'slug' => $slug, 'name' => $slug, 'term_id' => 0 );
								}
							}

							if ( ! empty( $terms ) ) :
								$show_color = ( $product_type === 'color' );
								$show_image = ( $product_type === 'image' );
								?>
								<div class="ata-term-config" data-key="<?php echo esc_attr( $key ); ?>" style="margin-top:8px;">
									<table class="widefat striped ata-term-table" style="max-width:640px;">
										<thead>
											<tr>
												<th style="width:30%;"><?php esc_html_e( 'Term', 'variation-swatches-style' ); ?></th>
												<th class="ata-col-color" style="<?php echo $show_color ? '' : 'display:none;'; ?>"><?php esc_html_e( 'Color', 'variation-swatches-style' ); ?></th>
												<th class="ata-col-image" style="<?php echo $show_image ? '' : 'display:none;'; ?>"><?php esc_html_e( 'Image', 'variation-swatches-style' ); ?></th>
											</tr>
										</thead>
										<tbody>
										<?php foreach ( $terms as $term ) :
											$term_key    = md5( $term->slug );
											$saved_color = '';
											$saved_image = '';
											if ( isset( $swatch_type_options[ $key ][ $term_key ] ) && is_array( $swatch_type_options[ $key ][ $term_key ] ) ) {
												$saved_color = isset( $swatch_type_options[ $key ][ $term_key ]['color'] ) ? $swatch_type_options[ $key ][ $term_key ]['color'] : '';
												$saved_image = isset( $swatch_type_options[ $key ][ $term_key ]['image'] ) ? $swatch_type_options[ $key ][ $term_key ]['image'] : '';
											}
											$image_url = $saved_image ? wp_get_attachment_image_url( $saved_image, 'thumbnail' ) : '';
											?>
											<tr>
												<td><strong><?php echo esc_html( $term->name ); ?></strong></td>
												<td class="ata-col-color" style="<?php echo $show_color ? '' : 'display:none;'; ?>">
													<input type="text"
														class="ata-color-field"
														name="_swatch_type_options[<?php echo esc_attr( $key ); ?>][<?php echo esc_attr( $term_key ); ?>][color]"
														value="<?php echo esc_attr( $saved_color ); ?>"
														data-default-color="" />
												</td>
												<td class="ata-col-image" style="<?php echo $show_image ? '' : 'display:none;'; ?>">
													<input type="hidden"
														class="ata-image-id"
														name="_swatch_type_options[<?php echo esc_attr( $key ); ?>][<?php echo esc_attr( $term_key ); ?>][image]"
														value="<?php echo esc_attr( $saved_image ); ?>" />
													<button type="button" class="button ata-upload-image"><?php esc_html_e( 'Upload', 'variation-swatches-style' ); ?></button>
													<button type="button" class="button ata-remove-image" style="<?php echo $saved_image ? '' : 'display:none;'; ?>"><?php esc_html_e( 'Remove', 'variation-swatches-style' ); ?></button>
													<span class="ata-image-preview" style="display:inline-block;margin-left:8px;vertical-align:middle;">
														<?php if ( $image_url ) : ?>
															<img src="<?php echo esc_url( $image_url ); ?>" style="width:32px;height:32px;object-fit:cover;border-radius:3px;" />
														<?php endif; ?>
													</span>
												</td>
											</tr>
										<?php endforeach; ?>
										</tbody>
									</table>
									<p class="description ata-type-hint" style="margin-top:6px;">
										<span class="ata-hint-color" style="<?php echo $show_color ? '' : 'display:none;'; ?>">
											<?php esc_html_e( 'Set a color for each term. These colors will be used as swatches on the product page.', 'variation-swatches-style' ); ?>
										</span>
										<span class="ata-hint-image" style="<?php echo $show_image ? '' : 'display:none;'; ?>">
											<?php esc_html_e( 'Upload an image for each term. These images will be used as swatches on the product page.', 'variation-swatches-style' ); ?>
										</span>
										<span class="ata-hint-other" style="<?php echo ( $show_color || $show_image || empty( $product_type ) ) ? 'display:none;' : ''; ?>">
											<?php esc_html_e( 'Button, Radio, Checkbox and Select Box only need the type selection above. No extra term values required.', 'variation-swatches-style' ); ?>
										</span>
									</p>
								</div>
							<?php endif; ?>

							<p class="description" style="margin-top:10px;">
								<?php
								printf(
									esc_html__( 'Tip: For store-wide consistency, set the type under %s instead of on every product.', 'variation-swatches-style' ),
									'<a href="' . esc_url( admin_url( 'edit.php?post_type=product&page=product_attributes' ) ) . '">' . esc_html__( 'Products → Attributes', 'variation-swatches-style' ) . '</a>'
								);
								?>
							</p>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<script>
		jQuery(function($){
			// Color pickers
			function initColorPickers(context) {
				if ($.fn.wpColorPicker) {
					$(context).find('.ata-color-field').each(function(){
						if (!$(this).hasClass('wp-color-picker')) {
							$(this).wpColorPicker();
						}
					});
				}
			}
			initColorPickers(document);

			// Toggle columns when type changes
			$(document).on('change', '.ata-product-swatch-type', function(){
				var type = $(this).val();
				var $block = $(this).closest('.ata-attr-block');
				var $table = $block.find('.ata-term-table');

				// Hide all term columns first
				$table.find('.ata-col-color, .ata-col-image').hide();
				$block.find('.ata-hint-color, .ata-hint-image, .ata-hint-other').hide();

				if (type === 'color') {
					$table.find('.ata-col-color').show();
					$block.find('.ata-hint-color').show();
				} else if (type === 'image') {
					$table.find('.ata-col-image').show();
					$block.find('.ata-hint-image').show();
				} else if (type) {
					$block.find('.ata-hint-other').show();
				}
			});

			// Image upload
			var frame;
			$(document).on('click', '.ata-upload-image', function(e){
				e.preventDefault();
				var $row = $(this).closest('td');
				frame = wp.media({
					title: '<?php echo esc_js( __( 'Select Swatch Image', 'variation-swatches-style' ) ); ?>',
					button: { text: '<?php echo esc_js( __( 'Use this image', 'variation-swatches-style' ) ); ?>' },
					multiple: false
				});
				frame.on('select', function(){
					var attachment = frame.state().get('selection').first().toJSON();
					$row.find('.ata-image-id').val(attachment.id);
					var url = (attachment.sizes && attachment.sizes.thumbnail) ? attachment.sizes.thumbnail.url : attachment.url;
					$row.find('.ata-image-preview').html('<img src="'+url+'" style="width:32px;height:32px;object-fit:cover;border-radius:3px;" />');
					$row.find('.ata-remove-image').show();
				});
				frame.open();
			});
			$(document).on('click', '.ata-remove-image', function(e){
				e.preventDefault();
				var $row = $(this).closest('td');
				$row.find('.ata-image-id').val('');
				$row.find('.ata-image-preview').empty();
				$(this).hide();
			});
		});
		</script>
		<?php
	}

	public function save_product_meta( $post_id, $post = null ) {
		if ( empty( $_POST['_swatch_type_options'] ) || ! is_array( $_POST['_swatch_type_options'] ) ) {
			delete_post_meta( $post_id, $this->meta_name );
			return;
		}

		$raw     = wp_unslash( $_POST['_swatch_type_options'] );
		$cleaned = array();
		$allowed = array( 'color', 'image', 'button', 'label', 'radio', 'checkbox', 'select', 'select_2' );

		foreach ( $raw as $key => $data ) {
			$key = sanitize_key( $key );
			if ( empty( $data['type'] ) || ! in_array( $data['type'], $allowed, true ) ) {
				continue;
			}

			$entry = array( 'type' => sanitize_text_field( $data['type'] ) );

			foreach ( $data as $term_key => $term_data ) {
				if ( $term_key === 'type' || ! is_array( $term_data ) ) {
					continue;
				}
				$term_key = sanitize_key( $term_key );
				$entry[ $term_key ] = array();
				if ( ! empty( $term_data['color'] ) ) {
					$entry[ $term_key ]['color'] = sanitize_hex_color( $term_data['color'] );
				}
				if ( ! empty( $term_data['image'] ) ) {
					$entry[ $term_key ]['image'] = absint( $term_data['image'] );
				}
			}

			$cleaned[ $key ] = $entry;
		}

		if ( empty( $cleaned ) ) {
			delete_post_meta( $post_id, $this->meta_name );
		} else {
			update_post_meta( $post_id, $this->meta_name, $cleaned );
		}
	}
}
