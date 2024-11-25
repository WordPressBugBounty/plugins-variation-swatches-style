<?php

register_activation_hook(__FILE__, 'ata_wc_swatches_plugin_activate');
add_action('admin_init', 'ata_wc_swatches_plugin_redirect');

function ata_wc_swatches_plugin_activate() {
    add_option('activate-variation-swatches-style', 'yes');
}

function ata_wc_swatches_plugin_redirect() {
    if ( get_option('activate-variation-swatches-style') ==  'yes' ) {
        delete_option('activate-variation-swatches-style');
		wp_safe_redirect( add_query_arg( array(
		 'page' => 'ata-variation-swatches',
		 'tab'  => 'tutorial'
		), admin_url( 'admin.php' ) ) );
													 
													 
    }
}

