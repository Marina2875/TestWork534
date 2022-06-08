<?php
/**
 * Storefront engine room
 *
 * @package storefront
 */

/**
 * Assign the Storefront version to a var
 */

wp_enqueue_script( 'my-script', get_template_directory_uri(). '/js/my-script.js', array(), true );

$theme              = wp_get_theme( 'storefront' );
$storefront_version = $theme['Version'];

/**
 * Set the content width based on the theme's design and stylesheet.
 */
if ( ! isset( $content_width ) ) {
	$content_width = 980; /* pixels */
}

$storefront = (object) array(
	'version'    => $storefront_version,

	/**
	 * Initialize all the things.
	 */
	'main'       => require 'inc/class-storefront.php',
	'customizer' => require 'inc/customizer/class-storefront-customizer.php',
);

require 'inc/storefront-functions.php';
require 'inc/storefront-template-hooks.php';
require 'inc/storefront-template-functions.php';
require 'inc/wordpress-shims.php';

if ( class_exists( 'Jetpack' ) ) {
	$storefront->jetpack = require 'inc/jetpack/class-storefront-jetpack.php';
}

if ( storefront_is_woocommerce_activated() ) {
	$storefront->woocommerce            = require 'inc/woocommerce/class-storefront-woocommerce.php';
	$storefront->woocommerce_customizer = require 'inc/woocommerce/class-storefront-woocommerce-customizer.php';

	require 'inc/woocommerce/class-storefront-woocommerce-adjacent-products.php';

	require 'inc/woocommerce/storefront-woocommerce-template-hooks.php';
	require 'inc/woocommerce/storefront-woocommerce-template-functions.php';
	require 'inc/woocommerce/storefront-woocommerce-functions.php';
}

if ( is_admin() ) {
	$storefront->admin = require 'inc/admin/class-storefront-admin.php';

	require 'inc/admin/class-storefront-plugin-install.php';
}


if ( version_compare( get_bloginfo( 'version' ), '4.7.3', '>=' ) && ( is_admin() || is_customize_preview() ) ) {
	require 'inc/nux/class-storefront-nux-admin.php';
	require 'inc/nux/class-storefront-nux-guided-tour.php';
	require 'inc/nux/class-storefront-nux-starter-content.php';
}


add_action( 'woocommerce_product_options_general_product_data', 'art_woo_add_custom_fields' );
function art_woo_add_custom_fields() {
	global $product, $post;
	echo '<div class="options_group">';
	
woocommerce_wp_text_input(
array(
	'name' => 'Test File',
	'label'   => 'Выберете изображение товара',
	'desc' => 'Upload an image or enter an URL.',
	'id' => $prefix . 'test_image',
	'type' => 'file',
	'allow' => array( 'url', 'attachment' ) 
)

);
woocommerce_wp_text_input(
	[
		'id'                => '_date_field',
		'label'             => __( 'Дата', 'woocommerce' ),
		'placeholder'       => 'Выбор даты',
		'description'       => __( 'Выбор даты', 'woocommerce' ),
		'type'              => 'date',
	]
);
woocommerce_wp_select( array(
   'id'      => '_select',
   'label'   => 'Выбором типа продукта',
   'options' => array(
      'one'   => __( 'rare', 'woocommerce' ),
      'two'   => __( 'frequent', 'woocommerce' ),
      'three' => __( 'unusual', 'woocommerce' ),
   ),
) );


	echo '</div>';
}
function art_woo_custom_fields_save( $post_id ) {
	$woocommerce_text_field = $_POST['_date_field'];
	if ( ! empty( $woocommerce_text_field ) ) {
		update_post_meta( $post_id, '_date_field', esc_attr( $woocommerce_text_field ) );
	}
	$woocommerce_select = $_POST['_select'];
	if ( ! empty( $woocommerce_select ) ) {
		update_post_meta( $post_id, '_select', esc_attr( $woocommerce_select ) );
	}
	$woocommerce_wp_text_input = $_POST['test_image'];
	if ( ! empty( $woocommerce_wp_text_input) ) {
		update_post_meta( $post_id, $prefix . 'test_image', esc_attr( $woocommerce_wp_text_input));
	}
}
add_action( 'woocommerce_process_product_meta', 'art_woo_custom_fields_save', 10 );
add_action( 'woocommerce_before_add_to_cart_form', 'woocust_custom_action', 5 );
function woocust_custom_action() {
	$product = wc_get_product();
	echo'<span>Date of creation:  </span>';
    echo $product->get_meta('_date_field', true );
	
}
	
    
								

add_action( 'woocommerce_product_options_general_product_data', function() { print '<form id="form">
	<script>
	document.getElementById("clearButton").onclick = function(e) {
		document.getElementById("_date_field").value = "";
		document.getElementById("test_image").value = "";
		document.getElementById("_select").value = "";
	  }
	  function refreshPage(){
		window.location.reload();
	</script>
<button id="clearButton">Clear</button>
<button type="submit"  onClick="refreshPage()">Refresh Button</button>
  </form>'; });
