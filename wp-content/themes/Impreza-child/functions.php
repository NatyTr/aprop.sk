<?php
/* Custom functions code goes here. */

////////////////////////////
//Adam Gluch (adamgluch.sk)
////////////////////////////

add_action( 'wp_head', 'scrate_meta_setup' );
function scrate_meta_setup() {
?>

    <meta name="facebook-domain-verification" content="ftl0lz03o79nuxt6z46pj308f9edem" />
    <meta name="google-site-verification" content="o7c9qQrdl9Jdz9oPG0GM4-VVzQrDoPI9o3StH7_avII" />

<?php
}

function repeater_subfield( $group_name, $repeater, $subfield ){
    global $post, $product;

    $repeater_meta_key = $group_name.'_'.$repeater;
    $rows = get_post_meta( $post->ID, $repeater_meta_key, true );
    for($i = 0; $i < $rows; $i++){
        $subfield_meta_key = $repeater_meta_key.'_'.$i.'_'.$subfield;
        $output[] = get_post_meta( $post->ID, $subfield_meta_key, true );
    }
    if( count($rows) > 0 ) return $output;
    else return;
}

add_filter( 'woocommerce_before_add_to_cart_button', 'dl_custom_product_designer_tab', 1 );

// The custom product tab content
function dl_custom_product_designer_tab() {
    global $post, $product;

    $group_name = 'kurz';

    $kurz = get_field( $group_name );

    $datumy_kurzu = repeater_subfield( $group_name, 'datumy_kurzu', 'datum' );

if ( is_product() && !$product->is_type( 'variable' ) ) {
    // check if the repeater field has rows of data
    if( count($datumy_kurzu) > 0 ):

        $todayDate = date('d.m.Y H:i', time());

        echo '<select name="datumKurzu" id="datumKurzu">';

        // loop through the rows of data
        foreach( $kurz['datumy_kurzu'] as $i ){
            if (strtotime($i['datum']) > strtotime($todayDate) AND $i['kapacita'] > 0){
                // Format start date
                $dateObj = DateTime::createFromFormat('d.m.Y H:i', $i['datum']);
                $dateWithoutTime = $dateObj ? $dateObj->format('d.m.Y') : $i['datum'];

                // Format end date without time
                $endDate = '';
                if (!empty($i['ukoncenie'])) {
                    $endObj = DateTime::createFromFormat('d.m.Y H:i', $i['ukoncenie']);
                    $endDate = $endObj ? $endObj->format('d.m.Y') : $i['ukoncenie'];
                }

                echo '<option value="'. $i['datum'] .'"><span class="datum">'. $dateWithoutTime;

                if ($endDate) {
                    echo ' do ' . $endDate;
                }

                //echo ' | Voľná kapacita: '. $i['kapacita'];

                if (isset($i['informacie']) && !empty($i['informacie'])) {
                    echo ' | Čas: '. $i['informacie'];
                }

                echo '</span></option>';
            }
        }


        echo '<option value="Darčekový poukaz"><span class="datum">Darčekový poukaz</span></option>';
        echo '</select><br><br>';
    else:

        // "no rows found" optional message
        echo '<p><em>Aktuálne žiadne termíny</em></p>';

    endif;
}
}

// Add as custom cart item data
add_filter( 'woocommerce_add_cart_item_data', 'save_acf_as_cart_item_data', 10, 2 );
function save_acf_as_cart_item_data( $cart_item_data, $cart_item ) {
    if ( isset($_POST['datumKurzu']) ) {
        if ( isset($_POST['datumKurzu']) ) {
            $cart_item_data['datumKurzu'] = esc_attr($_POST['datumKurzu']);
        }
        $cart_item_data['unique_key'] = md5( microtime().rand() );
    }
    return $cart_item_data;
}

// Display on cart and checkout
add_filter( 'woocommerce_get_item_data', 'display_acf_on_cart_and_checkout', 10, 2 );
function display_acf_on_cart_and_checkout( $cart_data, $cart_item ) {

    if ( isset($cart_item['datumKurzu']) ) {
        $custom_items[] = array( 'name' => __("Dátum", "woocommerce"), 'value' => $cart_item['datumKurzu'] );
    }
    return $custom_items;
}

// Display on orders and email notifications (save as custom order item meta data)
add_action( 'woocommerce_checkout_create_order_line_item', 'display_acf_on_orders_and_emails', 10, 4 );
function display_acf_on_orders_and_emails( $item, $cart_item_key, $values, $order ) {

    if ( isset($values['datumKurzu']) ) {
        $item->add_meta_data( 'DK', $values['datumKurzu'] );
    }
}

add_action( 'woocommerce_order_status_complete', 'update_product_custom_field', 20, 2 );
add_action( 'woocommerce_order_status_on-hold', 'update_product_custom_field', 20, 2 );
add_action( 'woocommerce_payment_processing', 'update_product_custom_field', 20, 2 );
add_action( 'woocommerce_order_status_completed', 'update_product_custom_field', 20, 2 );

function update_product_custom_field( $order_id, $order = '' ) {

    if( ! $order || ! is_a( $order, 'WC_Order') ) {
        $order = wc_get_order( $order_id ); // Get the WC_Order object if it's empty
    }

    $items = $order->get_items();
    foreach ( $order->get_items() as $item ) {

        $product_id = version_compare( WC_VERSION, '3.0', '<' ) ? $item['product_id'] : $item->get_product_id();

        $pocet = $item->get_quantity();
        $meta = $item->get_meta('DK');
        $nN = date("Y-m-d H:i:s", strtotime($meta));

            global $wpdb;

            $results = $wpdb->get_row("SELECT `meta_key` FROM `" . $wpdb->prefix . "postmeta` WHERE `post_id` = '$product_id' AND `meta_value` = '$nN'", OBJECT );

            foreach ($results as $r){
                preg_match('!\d+!', $r, $matches);

                $results2 = $wpdb->get_row("SELECT `meta_value` FROM `" . $wpdb->prefix . "postmeta` WHERE `post_id` = '$product_id' AND `meta_key` = 'kurz_datumy_kurzu_".$matches[0]."_kapacita'", OBJECT );

                foreach ($results2 as $r2){
                    $kV = $r2 - $pocet;

                    $wpdb->query("UPDATE `" . $wpdb->prefix . "postmeta` SET `meta_value` = '$kV' WHERE `post_id` = '$product_id' AND `meta_key` = 'kurz_datumy_kurzu_".$matches[0]."_kapacita' ");

                    //update_post_meta($product_id, '_hovno', $kV );
                }

            }


    }
}

function widget_kurz(){
    $current_post = get_queried_object();
    $post_id = $current_post ? $current_post->ID : null;
    $kurzik = get_field( 'produkt_lava_strana', $post_id );

    if ($kurzik){
    return do_shortcode('[us_grid no_items_page_block="8586" post_type="ids" items_quantity="1" items_layout="8965" columns="1" exclude_items="out_of_stock" ids="'.$kurzik.'"]');
    }
}

add_shortcode('widgetKurz', 'widget_kurz');

function vypocet_datum_kurzu(){
    global $post, $product;

    $group_name = 'kurz';

    $kurz = get_field( $group_name );

    $datumy_kurzu = repeater_subfield( $group_name, 'datumy_kurzu', 'datum' );
    $zobrazenie_datumu = get_field( $group_name, $post_id );



    // check if the repeater field has rows of data
    if( count($datumy_kurzu) > 0 AND $zobrazenie_datumu['zobrazenie_datumu'] == true){

            usort($datumy_kurzu, function ($a, $b) {
                return strtotime($a) - strtotime($b);
            });

            setlocale(LC_ALL, 'sk_SK');

         $todayDate = date('d.m.Y H:i', time());
         $kurzDate = $datumy_kurzu[0];

        //echo $kurzDate.'<br>';

        $now = time(); // or your date as well
        $your_date = strtotime($kurzDate);
        $datediff = $now - $your_date;
        $vy = round($datediff / (60 * 60 * 24));
        $vy2 = str_replace("-","",strval($vy));
        if ($vy2 == "1"){
            echo "<div id='dK'><small>Najbližší dátum je zajtra</small></div>";
        }
        elseif ($vy2 != "0"){
            echo "<div id='dK'><small>Najbližší dátum za ".$vy2." " . ngettext('deň', 'dní', $vy2)."</small></div>";
        }
        else{
            echo "<div id='dK'><small>Najbližší dátum je dnes</small></div>";
        }
    }
    else{
            echo "<div id='dK'><small>Informujte sa</small></div>";
    }
}
add_shortcode('vypocet_datum', 'vypocet_datum_kurzu');



add_filter( 'woocommerce_get_price_html', 'bbloomer_price_free_zero', 9999, 2 );

function bbloomer_price_free_zero( $price, $product ) {
    if ( $product->is_type( 'variable' ) ) {
        $prices = $product->get_variation_prices( true );
        $min_price = current( $prices['price'] );
        if ( 0 == $min_price ) {
            $max_price = end( $prices['price'] );
            $min_reg_price = current( $prices['regular_price'] );
            $max_reg_price = end( $prices['regular_price'] );
            if ( $min_price !== $max_price ) {
                $price = wc_format_price_range( 'Informujte sa', $max_price );
                $price .= $product->get_price_suffix();
            } elseif ( $product->is_on_sale() && $min_reg_price === $max_reg_price ) {
                $price = wc_format_sale_price( wc_price( $max_reg_price ), 'Informujte sa' );
                $price .= $product->get_price_suffix();
            } else {
                $price = 'Informujte sa';
            }
        }
    } elseif ( 0 == $product->get_price() ) {
        $price = '<span class="woocommerce-Price-amount amount">Informujte sa</span>';
    }
    return $price;
}


////////////////////////////
//Adam Gluch (adamgluch.sk)
////////////////////////////

function variableProd(){
    global $product;
    if ( is_product() && $product->is_type( 'variable' )) {
        ?>
        <style>
            #cenaProduktu{
                display: none !important;
            }
        </style>
        <?php
    }
}

add_filter( 'woocommerce_after_add_to_cart_button', 'variableProd', 2 );

////////////////////////////
//Adam Gluch (adamgluch.sk)
////////////////////////////

//
// Disable Guttenberg
//

add_filter('use_block_editor_for_post', '__return_false', 10);
add_filter('use_block_editor_for_post_type', '__return_false', 10);


add_action( 'wp_enqueue_scripts', 'remove_block_css', 100 );

function remove_block_css() {

wp_dequeue_style( 'wp-block-library' );
wp_dequeue_style( 'wp-block-library-theme' );
wp_dequeue_style( 'wc-block-style' );

    if (!is_admin_bar_showing() && !is_customize_preview()) {
        wp_dequeue_style('dashicons');
        wp_deregister_style('dashicons');
    }

wp_enqueue_script('jquery');
wp_enqueue_script('custom-gtmko', get_stylesheet_directory_uri() . '/js/gtmko.js', array( 'jquery' ));
}
