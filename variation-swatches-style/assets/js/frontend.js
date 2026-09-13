/**
 * Smart Variation Swatches - Frontend
 * Improved: color/image/button/radio/checkbox support + accessibility
 */
(function ( $ ) {
	'use strict';

	/**
	 * Main swatches form handler
	 */
	$.fn.atawc_variation_swatches_form = function () {
		return this.each( function () {
			var $form = $( this );

			// Avoid double-binding
			if ( $form.data( 'atawc-bound' ) ) {
				return;
			}
			$form.data( 'atawc-bound', true );

			$form
				.addClass( 'swatches-support' )
				.on( 'click keydown', '.swatch, .swatch_radio, .swatch-radio, .swatch-checkbox', function ( e ) {
					// Keyboard support
					if ( e.type === 'keydown' && e.key !== 'Enter' && e.key !== ' ' ) {
						return;
					}

					var $el = $( this );

					// If the click originated from a native input inside the label, let it bubble normally first
					var $input = $el.is( 'input' ) ? $el : $el.find( 'input[type="radio"], input[type="checkbox"]' ).first();
					if ( $input.length && e.target === $input[0] && e.type === 'click' ) {
						// Native input was clicked – sync state after a tick
						setTimeout( function () {
							syncFromInput( $el, $input, $form );
						}, 10 );
						return;
					}

					e.preventDefault();

					var $select = findSelect( $el, $form );
					if ( ! $select.length ) {
						return;
					}

					var value = $el.data( 'value' ) || $input.val();

					// Availability check
					if ( ! $select.find( 'option[value="' + value + '"]' ).length ) {
						$el.closest( '.atawc-swatches' ).find( '.swatch, .swatch-radio, .swatch-checkbox' )
							.removeClass( 'selected' )
							.attr( 'aria-checked', 'false' );
						$select.val( '' ).trigger( 'change' );
						$form.trigger( 'atawc_no_matching_variations', [ $el ] );
						return;
					}

					var isCheckbox = $el.hasClass( 'swatch-checkbox' ) || $input.is( '[type="checkbox"]' );
					var isRadio    = $el.hasClass( 'swatch-radio' ) || $el.hasClass( 'swatch_radio' ) || $input.is( '[type="radio"]' );

					if ( $el.hasClass( 'selected' ) && ! isCheckbox ) {
						// Deselect (except we still allow toggle for visual consistency)
						$select.val( '' );
						$el.removeClass( 'selected' ).attr( 'aria-checked', 'false' );
						if ( $input.length ) {
							$input.prop( 'checked', false );
						}
					} else {
						// Select this, deselect siblings
						$el.closest( '.atawc-swatches' ).find( '.swatch, .swatch-radio, .swatch-checkbox' )
							.removeClass( 'selected' )
							.attr( 'aria-checked', 'false' );

						if ( isRadio || isCheckbox ) {
							$el.closest( '.atawc-swatches' ).find( 'input[type="radio"], input[type="checkbox"]' ).prop( 'checked', false );
						}

						$el.addClass( 'selected' ).attr( 'aria-checked', 'true' );
						if ( $input.length ) {
							$input.prop( 'checked', true );
						}
						$select.val( value );
					}

					$select.trigger( 'change' );
				} )
				.on( 'click', '.reset_variations', function () {
					$( this ).closest( '.variations_form' )
						.find( '.swatch, .swatch-radio, .swatch-checkbox' )
						.removeClass( 'selected' )
						.attr( 'aria-checked', 'false' )
						.find( 'input' ).prop( 'checked', false );
				} )
				.on( 'atawc_no_matching_variations', function () {
					if ( typeof wc_add_to_cart_variation_params !== 'undefined' ) {
						window.alert( wc_add_to_cart_variation_params.i18n_no_matching_variations_text );
					}
				} );
		} );
	};

	function findSelect( $el, $form ) {
		var $select = $el.closest( '.value' ).find( 'select' );
		if ( ! $select.length ) {
			var attrName = $el.closest( '.atawc-swatches' ).data( 'attribute_name' );
			if ( attrName ) {
				$select = $form.find( 'select[data-attribute_name="' + attrName + '"], select[name="' + attrName + '"]' );
			}
		}
		if ( ! $select.length ) {
			$select = $form.find( 'select' ).first();
		}
		return $select;
	}

	function syncFromInput( $el, $input, $form ) {
		var $select = findSelect( $el, $form );
		var value   = $input.val();
		var checked = $input.is( ':checked' );

		$el.closest( '.atawc-swatches' ).find( '.swatch, .swatch-radio, .swatch-checkbox' )
			.removeClass( 'selected' )
			.attr( 'aria-checked', 'false' );

		if ( checked ) {
			$el.addClass( 'selected' ).attr( 'aria-checked', 'true' );
			$select.val( value );
		} else {
			$select.val( '' );
		}
		$select.trigger( 'change' );
	}

	/**
	 * Tooltips
	 */
	function initTooltips() {
		$( document ).on( 'mouseenter focus', '.masterTooltip, .swatch[title]', function () {
			var $el   = $( this ),
				title = $el.attr( 'title' ) || $el.data( 'tipText' );
			if ( ! title ) {
				return;
			}
			$el.data( 'tipText', title ).removeAttr( 'title' );
			$( '.ed-tooltip' ).remove();
			$( '<span class="ed-tooltip" role="tooltip"></span>' )
				.text( title )
				.appendTo( $el )
				.fadeIn( 150 );
		} ).on( 'mouseleave blur', '.masterTooltip, .swatch', function () {
			var $el = $( this );
			if ( $el.data( 'tipText' ) ) {
				$el.attr( 'title', $el.data( 'tipText' ) );
			}
			$( '.ed-tooltip' ).remove();
		} );
	}

	/**
	 * Price update on variation change
	 */
	function initPriceUpdate() {
		$( document.body ).on( 'change', 'input.variation_id', function () {
			if ( typeof smart_variable === 'undefined' || ! smart_variable.__price_update_on ) {
				return;
			}
			var $input        = $( this ),
				$container    = $input.closest( 'li.product, .product, .summary' ),
				priceSelector = smart_variable.__price_update_on,
				$priceEl;

			if ( priceSelector.indexOf( '.' ) === 0 || priceSelector.indexOf( '#' ) === 0 ) {
				$priceEl = $container.find( priceSelector ).first();
			} else {
				$priceEl = $container.find( '.' + priceSelector ).first();
			}
			if ( ! $priceEl.length ) {
				return;
			}

			var $form = $container.find( '.variations_form' );
			if ( ! $form.length ) {
				$form = $( '.variations_form' );
			}
			var variationsData = $form.attr( 'data-product_variations' );
			if ( ! variationsData ) {
				return;
			}
			try {
				var product_attr = JSON.parse( variationsData ),
					variation_id = $input.val();
				$.each( product_attr, function ( index, variation ) {
					if ( String( variation.variation_id ) === String( variation_id ) && typeof variation.price_html !== 'undefined' ) {
						var html = variation.price_html;
						if ( variation.availability_html ) {
							html += variation.availability_html;
						}
						$priceEl.html( html );
						return false;
					}
				} );
			} catch ( e ) {}
		} );
	}

	// Bootstrap
	$( function () {
		$( '.variations_form' ).atawc_variation_swatches_form();
		$( document.body ).trigger( 'atawc_initialized' );
		initTooltips();
		initPriceUpdate();

		$( document.body ).on( 'updated_wc_div found_variation reset_data wc_variation_form', function () {
			$( '.variations_form' ).atawc_variation_swatches_form();
		} );
	} );

})( jQuery );
