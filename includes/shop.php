<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Pakete bezahlen über WooCommerce (Zahlung: Stripe-Gateway-Plugin).
 * Läuft nur, wenn WooCommerce aktiv ist.
 */

function ewent_shop_active() {
	return class_exists( 'WooCommerce' );
}

function ewent_pack_prices() {
	return array( 'free' => 0, 'plus' => 19, 'top' => 49, 'premium' => 89 );
}

function ewent_pack_rank( $t ) {
	$i = array_search( $t, array( 'free', 'plus', 'top', 'premium' ), true );
	return false === $i ? 0 : $i;
}

/** Legt die drei Paket-Produkte an (einmalig, versteckt im Shop). */
function ewent_ensure_products() {
	if ( ! ewent_shop_active() ) {
		return array();
	}
	$map = get_option( 'ewent_pack_products', array() );
	if ( ! is_array( $map ) ) {
		$map = array();
	}
	$names = array( 'plus' => 'Plus', 'top' => 'Top', 'premium' => 'Premium' );
	$price = ewent_pack_prices();
	foreach ( $names as $key => $name ) {
		if ( ! empty( $map[ $key ] ) && wc_get_product( $map[ $key ] ) ) {
			continue;
		}
		$p = new WC_Product_Simple();
		$p->set_name( 'ewent. Paket ' . $name );
		$p->set_status( 'publish' );
		$p->set_catalog_visibility( 'hidden' );
		$p->set_virtual( true );
		$p->set_sold_individually( true );
		$p->set_tax_status( 'taxable' );
		$p->set_regular_price( (string) $price[ $key ] );
		$p->set_sku( 'ewent-' . $key );
		$p->update_meta_data( '_ewent_pack', $key );
		$map[ $key ] = $p->save();
	}
	update_option( 'ewent_pack_products', $map );
	return $map;
}

add_action( 'admin_init', function () {
	if ( ewent_shop_active() && ! get_option( 'ewent_pack_products' ) ) {
		ewent_ensure_products();
	}
} );

/* ---------- Kaufen aus /konto/ ---------- */

add_action( 'template_redirect', function () {
	if ( ! ewent_shop_active() || ! get_query_var( 'ewent_konto' ) || empty( $_GET['ewent_buy'] ) ) {
		return;
	}
	$pack = sanitize_key( $_GET['ewent_buy'] );
	$id   = (int) ( $_GET['event'] ?? 0 );
	$mail = ewent_session_mail();
	$cur  = (string) ( get_post_meta( $id, '_ew_tier', true ) ?: 'free' );
	$map  = ewent_ensure_products();
	if ( ! $mail || ! ewent_owns( $id, $mail ) || empty( $map[ $pack ] ) || ewent_pack_rank( $pack ) <= ewent_pack_rank( $cur )
		|| ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'ewent_buy_' . $id ) ) {
		wp_safe_redirect( add_query_arg( 'msg', 'denied', ewent_account_url() ) );
		exit;
	}
	if ( function_exists( 'wc_load_cart' ) ) {
		wc_load_cart();
	}
	WC()->cart->add_to_cart( (int) $map[ $pack ], 1, 0, array(), array( 'ewent_event' => $id ) );
	wp_safe_redirect( wc_get_checkout_url() );
	exit;
} );

/* ---------- Preis bei Upgrade: Differenz zum bisherigen Paket ---------- */

add_action( 'woocommerce_before_calculate_totals', function ( $cart ) {
	if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
		return;
	}
	$price = ewent_pack_prices();
	foreach ( $cart->get_cart() as $item ) {
		if ( empty( $item['ewent_event'] ) ) {
			continue;
		}
		$pack = (string) $item['data']->get_meta( '_ewent_pack' );
		$cur  = (string) ( get_post_meta( (int) $item['ewent_event'], '_ew_tier', true ) ?: 'free' );
		if ( isset( $price[ $pack ] ) ) {
			$item['data']->set_price( max( 0, $price[ $pack ] - ( $price[ $cur ] ?? 0 ) ) );
		}
	}
}, 20 );

add_filter( 'woocommerce_get_item_data', function ( $data, $item ) {
	if ( ! empty( $item['ewent_event'] ) ) {
		$data[] = array( 'key' => 'Event', 'value' => get_the_title( (int) $item['ewent_event'] ) );
	}
	return $data;
}, 10, 2 );

add_action( 'woocommerce_checkout_create_order_line_item', function ( $item, $key, $values ) {
	if ( ! empty( $values['ewent_event'] ) ) {
		$item->add_meta_data( 'Event', get_the_title( (int) $values['ewent_event'] ) );
		$item->add_meta_data( '_ewent_event', (int) $values['ewent_event'] );
		$item->add_meta_data( '_ewent_pack', (string) $values['data']->get_meta( '_ewent_pack' ) );
	}
}, 10, 3 );

/* ---------- Bezahlt: Paket am Event setzen ---------- */

function ewent_apply_order( $order_id ) {
	$order = wc_get_order( $order_id );
	if ( ! $order || $order->get_meta( '_ewent_applied' ) ) {
		return;
	}
	$did = false;
	foreach ( $order->get_items() as $it ) {
		$eid  = (int) $it->get_meta( '_ewent_event' );
		$pack = (string) $it->get_meta( '_ewent_pack' );
		if ( ! $eid || ! in_array( $pack, array( 'plus', 'top', 'premium' ), true ) ) {
			continue;
		}
		$cur = (string) ( get_post_meta( $eid, '_ew_tier', true ) ?: 'free' );
		if ( ewent_pack_rank( $pack ) > ewent_pack_rank( $cur ) ) {
			update_post_meta( $eid, '_ew_tier', $pack );
		}
		update_post_meta( $eid, '_ew_paid_order', $order_id );
		$did = true;
		wp_mail(
			ewent_notify_email(),
			'Paket gebucht: ' . ucfirst( $pack ) . ' für ' . get_the_title( $eid ),
			"Bestellung #{$order_id}: Paket " . ucfirst( $pack ) . " für „" . get_the_title( $eid ) . "“.\n" . ( 'premium' === $pack ? "\nPremium: bitte beim Veranstalter melden und den Social-Post abstimmen.\n" : '' ) . "\nEvent: " . admin_url( 'post.php?post=' . $eid . '&action=edit' )
		);
	}
	if ( $did ) {
		$order->update_meta_data( '_ewent_applied', 1 );
		$order->save();
	}
}
add_action( 'woocommerce_payment_complete', 'ewent_apply_order' );
add_action( 'woocommerce_order_status_processing', 'ewent_apply_order' );
add_action( 'woocommerce_order_status_completed', 'ewent_apply_order' );

/** Storniert/zurückerstattet: Paket wieder entfernen. */
function ewent_revert_order( $order_id ) {
	$order = wc_get_order( $order_id );
	if ( ! $order || ! $order->get_meta( '_ewent_applied' ) ) {
		return;
	}
	foreach ( $order->get_items() as $it ) {
		$eid = (int) $it->get_meta( '_ewent_event' );
		if ( $eid && (int) get_post_meta( $eid, '_ew_paid_order', true ) === (int) $order_id ) {
			update_post_meta( $eid, '_ew_tier', 'free' );
			delete_post_meta( $eid, '_ew_paid_order' );
		}
	}
	$order->delete_meta_data( '_ewent_applied' );
	$order->save();
}
add_action( 'woocommerce_order_status_refunded', 'ewent_revert_order' );
add_action( 'woocommerce_order_status_cancelled', 'ewent_revert_order' );
