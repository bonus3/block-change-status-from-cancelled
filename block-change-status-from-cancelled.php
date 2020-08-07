<?php
/**
 * Plugin Name: Block Change Status From Cancelled for WooCommerce
 * Description: Block order change status from cancelled
 * Version: 1.0.0
 * Requires PHP: 5.6
 * Author: Anderson SG <contato@andersonsg.com.br>
 * Author URI: http://tec.andersonsg.com.br
 * Licence: GPLv2 or later”
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: block-change-status-from-cancelled
 * Domain Path: /languages
 */

if (!defined('WPINC')) {
	die;
}

define('BCSFC_VERSION', '1.0.0');
define('BCSFC_PATH', dirname(plugin_basename(__FILE__)));

 if (!class_exists('WC_Block_Status_From_Cancelled')) {
    class WC_Block_Status_From_Cancelled {
        
        static $instance = false;
        private $order_last_status = [];

        private function __construct() {
            add_action('plugins_loaded', [$this, 'load_textdomain']);
            add_filter('woocommerce_order_get_status', [$this, 'set_order_last_status'], 10, 2);
            add_action('woocommerce_before_order_object_save', [$this, 'block_change_status_from_cancelled'], 10, 1);
            add_action('admin_notices', [$this, 'admin_notices']);
        }

        public static function get_instance() {
            if ( !self::$instance )
                self::$instance = new self;
            return self::$instance;
        }

        public function load_textdomain() {
            load_plugin_textdomain( 'block-change-status-from-cancelled', false, BCSFC_PATH . '/languages/');
        }

        public function set_order_last_status($status, $order) {
            $order_id = $order->get_id();
            if (!isset($this->order_last_status[$order_id]) || empty($this->order_last_status[$order_id])) {
                $this->order_last_status[$order_id] = $status;
            }
            return $status;
        }

        public function block_change_status_from_cancelled($order) {
            $order_id = $order->get_id();
            if (isset($this->order_last_status[$order_id]) 
                && $this->order_last_status[$order_id] != $order->get_status() 
                && $this->order_last_status[$order_id] == 'cancelled') {
                wp_redirect(admin_url('/post.php?post=' . $order_id . '&action=edit&bcsfc=1'));
                die;
            }
        }

        public function admin_notices() {
            if (!isset($_GET['bcsfc'])) return;
            $class = 'notice notice-error';
            $message = __( 'Canceled orders cannot change status.', 'block-change-status-from-cancelled' );
            printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) ); 
        }
    }
 }

 WC_Block_Status_From_Cancelled::get_instance();