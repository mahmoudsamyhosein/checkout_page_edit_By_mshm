<?php
// بسم الله الرحمن الرحيم 
// checkout page هذة الأضافة تقوم بحذف الاقسام التالية من صفحة أتمام الدفع
// المحافظة, 2أسم الشركة , البريد الألكتروني ,الدولة , الرقم  البريدي ,المدينة ,العنوان
// تمت البرمجة بواسطة محمود سامي حسين متولي

/*
Plugin Name: تعديل صفحة أتمام الدفع
Description:   تمكنك هذه الأضافة من تعديل صفحة أتمام الدفع و أزالة أكثر الحقول الغير مستخدمة و لتوفير تجربة جيدة لمستخدم الموقع
Version: 1.0
Author: محمود سامي حسين متولي 
Author Url: https://test.com
License: Gpl2
Licence Url: https://test.com
Text domain: checkout page edit
Domain path: /languages
*/
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// only run if there's no other class with this name
if (!class_exists('checkout_page_edit')) {
    class checkout_page_edit
    {
        private static $instance = null;

        const PLUGIN_VERSION = '1.0.0';

        // Minimum PHP version required by this plugin.
        const MINIMUM_PHP_VERSION = '7.0.0';

        // Minimum WordPress version required by this plugin.
        const MINIMUM_WP_VERSION = '4.4';

        // Minimum WooCommerce version required by this plugin.
        const MINIMUM_WC_VERSION = '3.5.0';

        public static function instance()
        {
            if (null === self::$instance) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        protected function __construct()
        {
            register_activation_hook(__FILE__, array($this, 'activation_check'));
            add_action('admin_init', array($this, 'init_plugin'));
            add_filter( 'woocommerce_checkout_fields' , 'my_override_checkout_fields' );
            
        }

   

        public function activation_check()
        {
            if (!version_compare(PHP_VERSION, self::MINIMUM_PHP_VERSION, '>=')) {
                $this->deactivate_plugin();
                wp_die(__('checkout_page_edit could not be activated. The minimum required PHP version is ' . self::MINIMUM_PHP_VERSION, 'checkout_page_edit'));
            }
        }

        protected function deactivate_plugin()
        {
            deactivate_plugins(plugin_basename(__FILE__));
            if (isset($_GET['activate'])) {
                unset($_GET['activate']);
            }
        }

        public function init_plugin()
        {
            if (!$this->is_compatible()) {
                return;
            }
            
        }

        

        
        public function is_compatible()
        {
            // Check for the required WordPress version
            if (version_compare(get_bloginfo('version'), self::MINIMUM_WP_VERSION, '<')) {
                add_action('admin_notices', [$this, 'admin_notice_minimum_wordpress_version']);
                $this->deactivate_plugin();
                return false;
            }

            // Check if WooCommerce is activated
            if (!defined('WC_VERSION') || version_compare(WC_VERSION, self::MINIMUM_WC_VERSION, '<')) {
                add_action('admin_notices', [$this, 'admin_notice_missing_woocommerce']);
                $this->deactivate_plugin();
                return false;
            } else if (class_exists('woocommerce')) {
                return true;
            } else {
                add_action('admin_notices', [$this, 'admin_notice_missing_woocommerce']);
                $this->deactivate_plugin();
                return false;
            }
            return true;
        }

        public function admin_notice_missing_woocommerce()
        {
            $woocommerce = 'woocommerce/woocommerce.php';
            $pathpluginurl = WP_PLUGIN_DIR . '/' . $woocommerce;
            $isinstalled = file_exists($pathpluginurl);
            if ($isinstalled && !is_plugin_active($woocommerce)) {
                if (!current_user_can('activate_plugins')) {
                    return;
                }
                $activation_url = wp_nonce_url('plugins.php?action=activate&amp;plugin=' . $woocommerce . '&amp;plugin_status=all&amp;paged=1&amp;s', 'activate-plugin_' . $woocommerce);
                $message = sprintf(__('%1$scheckout_page_edit%2$s requires %1$s"WooCommerce"%2$s plugin to be active. Please activate WooCommerce to continue.', 'checkout_page_edit'), '<strong>', '</strong>');
                $button_text = esc_html__('Activate WooCommerce', 'woo-academy');
            } else {
                if (!current_user_can('activate_plugins')) {
                    return;
                }
                $activation_url = wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=woocommerce'), 'install-plugin_woocommerce');
                $message = sprintf(__('%1$scheckout_page_edit%2$s requires %1$s"WooCommerce"%2$s plugin to be installed and activated. Please install WooCommerce to continue.', 'checkout_page_edit'), '<strong>', '</strong>');
                $button_text = esc_html__('Install WooCommerce', 'woo-academy');
            }
            $button = '<p><a href="' . $activation_url . '" class="button-primary">' . $button_text . '</a></p>';
            printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p>%2$s</div>', $message, $button);
        }

        public function admin_notice_minimum_wordpress_version()
        {
            $message = sprintf(
                esc_html__('"%1$s" requires "%2$s" version %3$s or greater.', 'checkout_page_edit'),
                '<strong>' . esc_html__('checkout_page_edit', 'checkout_page_edit') . '</strong>',
                '<strong>' . esc_html__('WordPress', 'checkout_page_edit') . '</strong>',
                self::MINIMUM_WP_VERSION
            );
            printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
        }
    }

        // Our hooked in function - $fields is passed via the filter! 
        function my_override_checkout_fields( $fields ) { 
            unset($fields['billing']['billing_company']);
            unset($fields['billing']['billing_email']);
            unset($fields['billing']['billing_country']);
            unset($fields['billing']['billing_postcode']);
            unset($fields['billing']['billing_city']);
            unset($fields['billing']['billing_address_2']);
            unset($fields['billing']['billing_state']);
            
            return $fields;
        }

}
// fire it up!
checkout_page_edit::instance();

        