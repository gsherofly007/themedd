<?php

/**
 * Add the themedd_edd_download_info() function to the download details widget
 *
 * @since 1.0.0
 */
function themedd_edd_download_details_widget( $instance, $download_id ) {
	echo themedd_edd_download_info();
}
add_action( 'edd_product_details_widget_before_purchase_button', 'themedd_edd_download_details_widget', 10, 2 );

/**
 * Remove and deactivate all styling included with EDD
 *
 * @since 1.0.0
 */
remove_action( 'wp_enqueue_scripts', 'edd_register_styles' );

/**
 * Remove the purchase link at the bottom of the single download page.
 *
 * @since 1.0.0
 */
remove_action( 'edd_after_download_content', 'edd_append_purchase_link' );

/**
 * Add cart link to secondary menu
 *
 * @since 1.0.0
 */
function themedd_edd_secondary_menu_after() {

	if ( 'secondary_menu' !== themedd_edd_cart_link_position() ) {
		return;
	}

    echo themedd_edd_cart_link( array( 'list_item' => false ) );
}
add_action( 'themedd_secondary_menu_after', 'themedd_edd_secondary_menu_after' );

/**
 * Alter EDD download loops.
 * Affects archive-download.php, taxonomy-download-category.php and taxonomy-download-category.php
 *
 * @since  1.0.0
 *
 * @return void
 */
if ( ! function_exists( 'themedd_edd_pre_get_posts' ) ):
	function themedd_edd_pre_get_posts( $query ) {

		// Default the number of downloads to 9, like EDD's [downloads] shortcode
		$downloads_per_page = apply_filters( 'themedd_edd_downloads_per_page', 9 );

		// Bail if in the admin or we're not working with the main WP query.
		if ( is_admin() || ! $query->is_main_query() ) {
			return;
		}

		// Set the number of downloads to show.
		if (
			is_post_type_archive( 'download' ) || // archive-download.php page
			is_tax( 'download_category' ) ||      // taxonomy-download-category.php
			is_tax( 'download_tag' )              // taxonomy-download-category.php
		) {
			$query->set( 'posts_per_page', $downloads_per_page );
		}

	}
endif;
add_action( 'pre_get_posts', 'themedd_edd_pre_get_posts', 1 );

/**
 * Template redirect actions
 *
 * @since  1.0.0
 *
 * @return void
 */
if ( ! function_exists( 'themedd_edd_set_distraction_free_checkout' ) ):
function themedd_edd_set_distraction_free_checkout() {

	/**
	 * Distraction Free Checkout
	 * Removes various distractions from the EDD checkout page to improve the customer's buying experience.
	 */
	if ( edd_is_checkout() && themedd_edd_distraction_free_checkout() && ! empty( edd_get_cart_contents() ) ) {

		// Remove page header.
		add_filter( 'themedd_page_header', '__return_false' );

		// Remove the primary navigation.
		remove_action( 'themedd_site_header_main_end', 'themedd_primary_menu' );

		// Remove the primary navigation if moved to the themedd_site_header_main hook.
		remove_action( 'themedd_site_header_main', 'themedd_primary_menu' );

	    // Remove the mobile menu.
	    remove_action( 'themedd_site_header_main', 'themedd_menu_toggle' );

		// Remove the secondary menu.
		remove_action( 'themedd_site_header_main', 'themedd_secondary_menu' );

		// Remove the footer.
		remove_action( 'themedd_footer', 'themedd_footer_widgets' );

		// Remove the sidebar.
		add_filter( 'themedd_show_sidebar', '__return_false' );

		// Remove the custom header (if any)
		remove_action( 'themedd_masthead_after', 'themedd_header_image' );
	}

}
endif;
add_action( 'template_redirect', 'themedd_edd_set_distraction_free_checkout' );


/**
 * Load the vendor contact form at the bottom of the page if another page template is active
 *
 * @since 1.0.0
 */
function themedd_edd_fes_load_vendor_contact_form() {

	// Return early if FES is not active.
	if ( ! themedd_is_edd_fes_active() ) {
		return;
	}

	// Load the contact form at the bottom of the page if a page template is being used,
	// And the vendor contact form is enabled.
	if (
		is_page_template() &&
		themedd_edd_fes_vendor_contact_form() &&
		is_page( EDD_FES()->helper->get_option( 'fes-vendor-page', false ) )
	) {
		$vendor_id = absint( fes_get_vendor()->ID );
		echo (new FES_Forms)->render_vendor_contact_form( $vendor_id );
	}

}

add_action( 'themedd_entry_content_end', 'themedd_edd_fes_load_vendor_contact_form' );
