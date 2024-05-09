<?php
/**
 * Plugin Name: Shipping Rate By Cities
 * Plugin URI: https://wordpress.org/plugins/shipping-rate-by-cities
 * Description: Set Custom Shipping Rates For Different Cities On Woocommerce.
 * Version: 1.0.0
 *
 * @package     shipping_rate_by_cities
 * @author      Trident Technolabs
 * @copyright   https://tridenttechnolabs.com
 * @license     GPLv2 or later
 */

 
 if ( ! defined( 'WPINC' ) ) die;

 /** @class Wc City Fee */

   class  WShippingRateByCity {
    /**
     * Ship Rate By City Version.
     * @var string
     */
    public $version = '1.0.0';
 
     /**
      * Stores notices.
      * @var array
      */
     private static $notices = [];
 
     /**
      * Logger context.
      * @var array
      */
     public $context = ['source' => 'shiprate'];
 
     /** The single instance of the class. */
     protected static $_instance = null;
 
     /**
      * Returns the *Singleton* instance of this class.
      *
      * @return Singleton The *Singleton* instance.
      */
     public static function instance() {
         if ( is_null( self::$_instance ) ) {
             self::$_instance = new self();
         }
         return self::$_instance;
     }
 
     /**
      * Shipping Rate By City Constructor.
      */
     private function __construct()
     {
         $this->defineConstants();
         $this->init_hooks();
         $this->session();
     }
 
     private function init_hooks()
     {
         /**
          * Activation/Deactivation
          */
         register_activation_hook(SHIPRATE_PLUGIN_FILE, [$this, 'activation']);
         register_deactivation_hook(SHIPRATE_PLUGIN_FILE, [$this, 'deactivation']);
 
         /**
          * Enqueue Scripts
          */
         add_action('admin_enqueue_scripts', [$this, 'enqueueAdminScripts']);
         add_action('wp_enqueue_scripts', [$this, 'enqueueScripts']);
         
 
         /**
          * Check if WooCommerce is active
          */        
          if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
 
         /**
          * Shipping method init
          */
         add_action( 'woocommerce_shipping_init', [$this, 'shiprate_shipping_method'] );
         add_filter( 'woocommerce_shipping_methods', [$this, 'add_shiprate_shipping_method'] );
 
         // Change text box to select and set cities options
         add_filter( 'woocommerce_checkout_fields', array( $this, 'shiprate_city_options' ) );
 
         // add script to footer to update checkout on city select
         add_filter( 'wp_footer', array( $this, 'shiprate_city_wp_footer' ) );
 
         // add settings link to plugin list
         add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), [$this, 'shiprate_plugin_settings_link']);
 
 
         }
         
     }
 
     public function session()
     {
         if ( session_status() == PHP_SESSION_NONE ) {
             session_start();
         }
     }
 
     public function activation()
     {
         global $wpdb;
         require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
 
         $charset_collate = $wpdb->get_charset_collate();
         $table_name = $wpdb->prefix . "shiprate_cities";
         $query = "CREATE TABLE IF NOT EXISTS $table_name (
             `id` int(11) AUTO_INCREMENT,
             `city_name` VARCHAR(255) NOT NULL,
             `rate` VARCHAR(25) NOT NULL,
             `status` VARCHAR(25) NOT NULL DEFAULT 1,
             `create_date` DATETIME NOT NULL,
             PRIMARY KEY (id)
         ) AUTO_INCREMENT=1 $charset_collate;";
         dbDelta( $query );
     }
 
     public function deactivation() 
     {
         // deactivatation code
     }
 
     /**
      * Define Wc City Fee Constants.
      */
     private function defineConstants()
     {
         
         $this->define('SHIPRATE_PLUGIN_FILE', __FILE__);
         $this->define('SHIPRATE_VERSION', $this->version);
         $this->define('SHIPRATE', 'shiprate');
         
     }
 
     /**
      * Define constant if not already set.
      *
      * @param string      $name  Constant name.
      * @param string|bool $value Constant value.
      */
     private function define( $name, $value )
     {
         if (!defined($name)) {
             define($name, $value);
         }
     }

 
     /**
      * Enquene Scripts
      */
     public function enqueueScripts()
     {
         wp_enqueue_script('jquery');

     }
 
     /**
      * Enquene Admin Scripts
      */
     public function enqueueAdminScripts()
     {
         wp_enqueue_script('jquery');
         wp_enqueue_script(SHIPRATE, plugins_url('/assets/shiprate-admin.js', SHIPRATE_PLUGIN_FILE), ['jquery'], SHIPRATE_VERSION);
     }
     
 
     function shiprate_shipping_method() {
         include_once "shiprate-cities-method-class.php";
     }
 
     function shiprate_plugin_settings_link( $actions ) {
 
         $mylinks = array(
             '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=shipping&section=shiprate' ) . '">Settings</a>',
          );
 
          return array_merge($mylinks,  $actions  );
     }

     function add_shiprate_shipping_method( $methods ) {
         $methods[] = 'ShipRate_FlatShipRateCity_Method';
         return $methods;
     }
  
 
    //  function shiprate_city_options( $fields ) {
    //     global $wpdb;
    //     $table = $wpdb->prefix . "shiprate_cities";        
    //     $cities = $wpdb->get_results("SELECT city_name FROM $table ");
    //     $options[] = 'Select city';

    //     foreach($cities as $city){
    //         $options[$city->city_name] = $city->city_name;
    //     }

    //     $city_args = wp_parse_args(array(
    //         'type'    => 'select',
    //         'options' => $options,
    //         'autocomplete' => true
    //     ), $fields['shipping']['shipping_city']);


 
    //     $fields['shipping']['shipping_city'] = $city_args;
    //     $fields['billing']['billing_city']   = $city_args; // Also change for billing field

    //     return $fields;
  
    //  }
    function shiprate_city_options( $fields ) {
        global $wpdb;
        $table = $wpdb->prefix . "shiprate_cities";
        $cities = $wpdb->get_results("SELECT city_name FROM $table ");
        $options = array('Select city');
        foreach($cities as $city) {
            $options[$city->city_name] = $city->city_name;
        }
    
        // Get the current city value
        $city_value = isset($fields['shipping']['shipping_city']['default']) ? $fields['shipping']['shipping_city']['default'] : '';
    
        // Check if the current city value is "Other City"
        if ($city_value === 'Other City') {
            // Add a text box for entering the city name
             $city_args = wp_parse_args(array(
            'type'    => 'select',
            'options' => $options,
            'autocomplete' => true
        ), $fields['shipping']['shipping_city']);
            $city_args_text = array(
                'type' => 'text',
                'label' => 'Enter City',
                'required' => true,
                'autocomplete' => 'shipping city',
                'Placeholder' => 'Enter Other City Name',
            );
        } else {
            // Use the default select box
                $city_args = wp_parse_args(array(
            'type'    => 'select',
            'options' => $options,
            'autocomplete' => true
        ), $fields['shipping']['shipping_city']);
            $city_args_text = array();
        }
    
        // Set the shipping and billing city fields
        $fields['shipping']['shipping_city'] = $city_args;
        $fields['shipping']['shipping_city_text'] = $city_args_text;
    
        $fields['billing']['billing_city'] = $city_args;
        $fields['billing']['billing_city_text'] = $city_args_text;
    
        
    
        return $fields;
    }
    

     function shiprate_city_wp_footer(){
         if(is_checkout()){
            
         ?>
         <script>
             jQuery( function($) {
                 $('#billing_city').change(function(){
                     jQuery('body').trigger('update_checkout');
                 });
                 $('#shipping_city').change(function(){
                     jQuery('body').trigger('update_checkout');
                 });


                 $('#shipping_city').css('height','50px');
                 $('input[name="shipping_city_text"]').css('margin-top','10px');

                $('#billing_city').css('height','50px');
                $('input[name="billing_city_text"]').css('margin-top','10px');

                var select1 = $('select[name="shipping_city"]');
                var input1 = $('input[name="shipping_city_text"]');
                select1.change(function() {
                    if ($(this).val() == 'Other City') {
                        
                        input1.show();
                        $('#shipping_city_field').append(input1);
                       
                    } else {
                        input1.hide();
                    }
                });

                var select = $('select[name="billing_city"]');
                var input = $('input[name="billing_city_text"]');
                select.change(function() {
                    if ($(this).val() == 'Other City') {
                     
                        input.show();
                        $('#billing_city_field').append(input);
                    } else {
                        input.hide();
                    }
                });
             }); 
         </script>
         <?php
         }
     }
 }
 
 /**
  * Returns the main instance of WC.
  *
  * @since  2.1
  * @return WooCommerce
  */
 function shiprate_shipping() {
     return WShippingRateByCity::instance();
 }
 
 // Global for backwards compatibility.
 $GLOBALS['shiprate'] = shiprate_shipping();


 if(isset($_POST['submitexport'])){

 if (isset($_POST['export_csv'])) {
     global $wpdb;

 $table_name = $wpdb->prefix . 'shiprate_cities';
 $results = $wpdb->get_results("SELECT * FROM $table_name");

 $header_row = array('city_name', 'rate');
 $rows = array($header_row);

 foreach ($results as $result) {
     $row = array($result->city_name, $result->rate);
     array_push($rows, $row);
 }

 $csv_data = '';
 foreach ($rows as $row) {
     $csv_data .= implode(',', $row) . "\n";
 }

 
 
     header('Content-Type: text/csv');
     header('Content-Disposition: attachment; filename=export.csv');
     echo $csv_data;
     exit;
 }

}


 if (isset($_POST['importsubmit'])) {

    global $wpdb;
    // Check if a file is uploaded
    if (!empty($_FILES['import_file']['tmp_name'])) {
        // Get the uploaded file details
        $file = $_FILES['import_file']['tmp_name'];

        $handle = fopen($file, 'r');
if ($handle !== false) {
    // Skip the header row
    $header = fgetcsv($handle);

         // Loop through the CSV data
    while (($data = fgetcsv($handle)) !== false) {
        // Retrieve the necessary data from each row
        $cityName = $data[0];
        $rate = $data[1];

        // Check if the city already exists in the table
        $existingCity = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM wp_shiprate_cities WHERE city_name = %s", $cityName)
        );

        // Perform the insert or update operation based on whether the city exists or not
        if ($existingCity) {
            // City exists, perform an update
            $wpdb->update(
                'wp_shiprate_cities',
                array('rate' => $rate),
                array('city_name' => $cityName)
            );
        } else {
            // City does not exist, perform an insert
            $wpdb->insert(
                'wp_shiprate_cities',
                array('city_name' => $cityName, 'rate' => $rate)
            );
        }
    }
    fclose($handle);
                // Refresh the current page using JavaScript
                echo '<script> window.location.href = window.location.href; </script>';
                exit;
        } else {
           // Show an error message if the file cannot be opened
            echo 'Error: Unable to open the file.';
            exit;
        }
    } else {
        // Show an error message if the file cannot be opened
        echo 'Error: Unable to open the file.';
        exit;
    }
}

 