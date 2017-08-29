<?php
/**
 * Plugin Name: WooCommerce Bookings Order Item Meta
 * Version: 1.0.0
 * Plugin URI: https://github.com/jessepearson/woocommerce-bookings-order-item-meta
 * Description: This addon is a WooCommerce Bookings helper which will make it so future booking orders have meta data saved within the order as they did with version 1.9.12 and below.
 * Author: Jesse Pearson
 * Author URI: https://github.com/jessepearson/
 * Requires at least: 4.7.0
 * Tested up to: 4.8.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Bookings_Order_Meta_Data' ) ) {
	/**
	 * Main class.
	 *
	 * @package Bookings_Order_Meta_Data
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	class Bookings_Order_Meta_Data {
		public $notice;
		public static $self;

		/**
		 * Initialize.
		 *
		 * @since   1.0.0
		 * @version 1.0.0
		 */
		public static function init() {
			self::$self = new self();

			add_filter( 'woocommerce_new_order_item', array( self::$self, 'order_item_meta' ), 99, 3 );
			add_filter( 'woocommerce_display_item_meta', array( self::$self, 'filter_order_item_meta' ), 99, 3 );
		}

		/**
		 * Returns the current class object.
		 *
		 * @since   1.0.0
		 * @version 1.0.0
		 */
		public static function get_instance() {
			return self::$self;
		}

		/**
		 * Adds the meta data to the order item. 
		 *
		 * @since   1.0.0
		 * @version 1.0.0
		 * @param   mixed $item_id
		 * @param   mixed $values
		 */
		public function order_item_meta( $item_id, $values, $order_id ) {

			// Check to make sure we have what we need to proceed. 
			if ( ! empty( $values->legacy_values ) && is_array( $values->legacy_values ) && ! empty( $values->legacy_values['booking'] ) ) {
				$product      = $values->legacy_values['data'];
				$booking_id   = $values->legacy_values['booking']['_booking_id'];
				$booking_data = $values->legacy_values['booking'];
			}

			// If we have a booking. 
			if ( isset( $booking_id ) ) {

				// Add summary of details to line item. 
				foreach ( $booking_data as $key => $value ) {
					if ( strpos( $key, '_' ) !== 0 ) {
						wc_add_order_item_meta( $item_id, get_wc_booking_data_label( $key, $product ), $value );
					}
				}

				// Add the Booking ID, as well.
				wc_add_order_item_meta( $item_id, __( 'Booking ID', 'woocommerce-bookings' ), $booking_id );
			}
		}

		/**
		 * Filters what is seen to the customer on the checkout page, etc.
		 *
		 * @since   1.0.0
		 * @version 1.0.0
		 * @param   mixed $item_id
		 * @param   mixed $values
		 */
		public function filter_order_item_meta( $html, $item, $args ) {

			// Get the booking id(s).
			$booking_ids = WC_Booking_Data_Store::get_booking_ids_from_order_item_id( $item->get_id() );
			
			// If we have id(s), return nothing.
			if ( ! empty( $booking_ids ) ) {
				return '';
			}

			return $html;
		}
	}

	Bookings_Order_Meta_Data::init();
}