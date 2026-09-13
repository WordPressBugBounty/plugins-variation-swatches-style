<?php
/**
 * Activation redirect to Dashboard
 */
add_action( 'admin_init', 'ata_wc_swatches_plugin_redirect' );

function ata_wc_swatches_plugin_redirect() {
	if ( get_option( 'activate-smart-variation-swatches' ) === 'yes' ) {
		delete_option( 'activate-smart-variation-swatches' );
		wp_safe_redirect( admin_url( 'admin.php?page=ata-variation-swatches' ) );
		exit;
	}
}
