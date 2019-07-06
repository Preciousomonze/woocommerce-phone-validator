<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly.

final class WC_PV{

    /**
     * The single instance of the class.
     *
     * @var WC_PV
     * @since 1.0.0
     */
    protected static $_instance = null;

    /**
     * Main instance
     * @return class object
     */
    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Class constructor
     */
    public function __construct() {
        if (WC_PV_Dependencies::is_woocommerce_active()) {
            $this->define_constants();//define the constants
            $this->includes();//include relevant files
            $this->init_hooks();
        } else {
            add_action('admin_notices', array($this, 'admin_notices'), 15);
        }
    }

    /**
     * Constants define
     */
    private function define_constants() {
        $this->define('WC_PV_ABSPATH', dirname(WC_PV_PLUGIN_FILE) . '/');
        $this->define('WC_PV_PLUGIN_FILE', plugin_basename(WC_PV_PLUGIN_FILE));
        $this->define('WC_PV_ASSETS_PATH', plugins_url('assets/',__FILE__));
        if(trim(strtolower(WC_PV_ENVIRONMENT)) == 'production')
            $this->define('WC_PV_MIN_SUFFIX', '.min');
        else
            $this->define('WC_PV_MIN_SUFFIX', '');    
    }

    /**
     * 
     * @param string $name
     * @param mixed $value
     */
    private function define($name, $value) {
        if (!defined($name)) {
            define($name, $value);
        }
    }

    /**
     * Check request
     * @param string $type
     * @return bool
     */
    private function is_request($type) {
        switch ($type) {
            case 'admin' :
                return is_admin();
            case 'ajax' :
                return defined('DOING_AJAX');
            case 'cron' :
                return defined('DOING_CRON');
            case 'frontend' :
                return (!is_admin() || defined('DOING_AJAX') ) && !defined('DOING_CRON');
        }
    }

    /**
     * load plugin files
     */
    public function includes() {
        //if ($this->is_request('admin')) {}
        if ($this->is_request('frontend')) {
            include_once( WC_PV_ABSPATH . 'public/class-woocommerce-checkout.php' );
        }
        //if ($this->is_request('ajax')) {}
    }

    /**
     * Plugin url
     * @return string path
     */
    public function plugin_url() {
        return untrailingslashit(plugins_url('/', WC_PV_PLUGIN_FILE));
    }

    /**
     * Display admin notice
     */
    public function admin_notices() {
        echo '<div class="error"><p>';
        _e('<strong>Woocommerce Phone Validator</strong> plugin requires <a href="https://wordpress.org/plugins/woocommerce/" target="_blank">WooCommerce</a> plugin to be active!', WC_PV_TEXT_DOMAIN);
        echo '</p></div>';
    }

}
