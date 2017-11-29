<?php
 
/**
 * Plugin Name: C&I Marketing Shipping
 * Plugin URI: none
 * Description: Custom Casepack Shipping for WooCommerce
 * Version: 0.0.1
 * Author: Justin Coberly
 * Author URI: http://www.cimarketingsolutions.com
 * License: Copyright C&I Marketing Solutions
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Domain Path: /lang
 * Text Domain: C&I Marketing Solutions
 */

if ( ! defined( 'WPINC' ) ) {
 
    die;
 
}
 
/*
 * Check if WooCommerce is active
 */
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
 
    function cimarketing_shipping_method() {
        if ( ! class_exists( 'CIMarketing_Shipping_Method' ) ) {
            class CIMarketing_Shipping_Method extends WC_Shipping_Method {
                /**
                 * Constructor for shipping class
                 *
                 * @access public
                 * @return void
                 */
                public function __construct() {
                    $this->id                 = 'cimarketing'; 
                    $this->method_title       = __( 'C&I Marketing Shipping', 'cimarketing' );  
                    $this->method_description = __( 'Custom Shipping Method for C&I Marketing', 'cimarketing' ); 
 
                    // Availability & Countries
                    $this->availability = 'including';
                    $this->countries = array('US');
 
                    $this->init();
 
                    $this->enabled = isset( $this->settings['enabled'] ) ? $this->settings['enabled'] : 'yes';
                    $this->title = isset( $this->settings['title'] ) ? $this->settings['title'] : __( 'C&I Marketing Shipping', 'cimarketing' );
                }
 
                /**
                 * Init your settings
                 *
                 * @access public
                 * @return void
                 */
                function init() {
                    // Load the settings API
                    $this->init_form_fields(); 
                    $this->init_settings(); 
 
                    // Save settings in admin if you have any defined
                    add_action( "woocommerce_update_options_shipping_{$this->id}", array( $this, 'process_admin_options' ) );
                }
 
                /**
                 * Define settings field for this shipping
                 * @return void 
                 */
                function init_form_fields() { 
 
                    $this->form_fields = array(
 
                     'enabled' => array(
                          'title' => __( 'Enable', 'cimarketing' ),
                          'type' => 'checkbox',
                          'description' => __( 'Enable this shipping.', 'cimarketing' ),
                          'default' => 'yes'
                          ),
 
                     'title' => array(
                        'title' => __( 'Title', 'cimarketing' ),
                          'type' => 'text',
                          'description' => __( 'Title to be display on site', 'cimarketing' ),
                          'default' => __( 'C&I Marketing Shipping', 'cimarketing' )
                          ),

                     'street' => array(
                        'title' => __( 'Shipping Address', 'cimarketing' ),
                          'type' => 'text',
                          'description' => __( 'Street', 'cimarketing' ),
                          'default' => __( '6505 South Pecos', 'cimarketing' )
                          ),

                     'city' => array(
                        'title' => __( ' ', 'cimarketing' ),
                          'type' => 'text',
                          'description' => __( 'City', 'cimarketing' ),
                          'default' => __( 'Las Vegas', 'cimarketing' )
                          ),
                    'state' => array(
                        'title' => __( ' ', 'cimarketing' ),
                        'type' => 'text',
                        'description' => __( 'State', 'cimarketing' ),
                        'default' => __( 'NV', 'cimarketing' )
                        ),

                    'zipcode' => array(
                        'title' => __( ' ', 'cimarketing' ),
                          'type' => 'text',
                          'description' => __( 'Zipcode', 'cimarketing' ),
                          'default' => 92374
                          ),

                     'weight' => array(
                        'title' => __( 'Weight (lbs)', 'cimarketing' ),
                          'type' => 'number',
                          'description' => __( 'Maximum allowed weight', 'cimarketing' ),
                          'default' => 100
                          )

 
                     );
 
                }
 
                /**
                 * This function is used to calculate the shipping cost.
                 *
                 * @access public
                 * @param mixed $package
                 * @return void
                 */
                public function calculate_shipping( $package = array() ) {
                    
                    $parcels = array();

                    foreach ( $package['contents'] as $item_id => $values ) 
                    { 
                        $_product = $values['data']; 
                        $product_id = $_product->get_id(); 
                        $product_name = $_product->get_name();
                        $casepack = get_post_meta( $product_id, '_ci_casepack', true );
                        
                        $weight = $_product->get_weight();
                        
                        //print_r($product_id);
                        //print_r($casepack);
                        $length = $_product->get_length();
                        $height = $_product->get_height();
                        $width = $_product->get_width();
                        $quantity = $values['quantity'];
                        $casepack_amt = $quantity/$casepack;
                        
                        for ($i=0; $i < $casepack_amt; $i++) {
                            array_push($parcels, '{
                                "description": "Casepack: '.$i.'",
                                "box_type": "custom",
                                "weight": {
                                    "value": '.$weight.',
                                    "unit": "lb"
                                },
                                "dimension": {
                                    "width": '.$width.',
                                    "height": '.$height.',
                                    "depth": '.$length.',
                                    "unit": "in"
                                },
                                "items": [
                                    {
                                    "description": "'.$product_name.'",
                                    "origin_country": "USA",
                                    "quantity": 2,
                                    "price": {
                                        "amount": 3,
                                        "currency": "USD"
                                    },
                                    "weight": {
                                        "value": 0.6,
                                        "unit": "lb"
                                    }
                                }
                                ]
                                }');
                        }
                    }
                    //print_r($parcels);
                    
                    /*******************************************************************************************/
                    $CIMarketing_Shipping_Method = new CIMarketing_Shipping_Method();    
                    $from_street = $CIMarketing_Shipping_Method->settings['street'];                
                    $from_city = $CIMarketing_Shipping_Method->settings['city'];
                    $from_state = $CIMarketing_Shipping_Method->settings['state'];
                    $from_zip = $CIMarketing_Shipping_Method->settings['zipcode'];

                    $to_country = $package["destination"]["country"];
                    $to_state = $package["destination"]["state"];
                    $to_zip = $package["destination"]["postcode"];

                    
                    //print_r($package);

                    //print_r($quantity);
                    if( $to_zip != null ) {
                        $url = 'https://production-api.postmen.com/v3/rates';
                        $method = 'POST';
                        $headers = array(
                            "content-type: application/json",
                            "postmen-api-key: b8abb251-8923-4b22-a448-16903b83fca2"
                        );
                        $body = '{
                            "async": false,
                            "shipper_accounts": [
                            {
                                "id": "8c546a19-1aff-43d1-bf76-316571fc7313"
                            }
                            ],
                            "is_document": false,
                            "shipment": {
                            "ship_from": {
                                "contact_name": "Justin Coberly",
                                "company_name": "The Premium Connection",
                                "street1": "'.$from_street.'",
                                "country": "USA",
                                "type": "business",
                                "postal_code": "'.$from_zip.'",
                                "city":"'.$from_city.'",
                                "phone": null,
                                "street2": null,
                                "tax_id": null,
                                "street3": null,
                                "state": "'.$from_state.'",
                                "email": null,
                                "fax": null
                            },
                            "ship_to": {
                                "postal_code": "'.$to_zip.'",
                                "state": "'.$to_state.'",
                                "country": "'.$to_country.'",
                                "type": "business"
                            },
                            "parcels": [
                                '.implode($parcels, ',').'
                            ]
                            }
                        }';

                        //print_r($body);
                        $curl = curl_init();
                    
                        curl_setopt_array($curl, array(
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_URL => $url,
                            CURLOPT_CUSTOMREQUEST => $method,
                            CURLOPT_HTTPHEADER => $headers,
                            CURLOPT_POSTFIELDS => $body,
                            CURLOPT_SSL_VERIFYPEER => false
                        ));
                    
                        $response = curl_exec($curl);
                        $err = curl_error($curl);
                    
                        curl_close($curl);
                    
                        if ($err) {
                           // echo "cURL Error #:" . $err;
                        } else {
                            $json = json_decode($response, true); 
                        }
                        
                        $fedex_ground = $json['data']['rates'][0]['total_charge']['amount'];
                        $fedex_express = $json['data']['rates'][1]['total_charge']['amount'];
                        $fedex_2day = $json['data']['rates'][2]['total_charge']['amount'];
                        $fedex_am = $json['data']['rates'][3]['total_charge']['amount'];
                        $fedex_pri_over_night = $json['data']['rates'][4]['total_charge']['amount'];
                        $fedex_std_over_night = $json['data']['rates'][5]['total_charge']['amount'];
                        /*************************************************************************************/
                    
                        if($fedex_ground > 0 ){
                            $this->add_rate(array(
                                'id' => "ground",
                                'label' => "FedEx Ground",
                                'cost' => $fedex_ground
                            ));
                        }
                        if($fedex_express > 0) {
                            $this->add_rate(array(
                                'id' => "Express",
                                'label' => "FedEx Express Saver",
                                'cost' => $fedex_express
                            ));
                        }
                        if($fedex_2day > 0) {                        
                            $this->add_rate(array(
                            'id' => 'twoDay',
                            'label' => "FedEx 2Day",
                            'cost' => $fedex_2day
                        ));
                        }
                        if($fedex_am > 0) {    
                            $this->add_rate(array(
                                'id' => 'am',
                                'label' => "FedEx 2Day A.M.",
                                'cost' => $fedex_am
                            ));
                        }
                        if($fedex_pri_over_night > 0) {
                            $this->add_rate(array(
                                'id' => 'priority',
                                'label' => "FedEx Priority Overnight",
                                'cost' => $fedex_pri_over_night
                            ));
                        }
                        if($fedex_std_over_night > 0) {
                            $this->add_rate(array(
                                'id' => 'standard',
                                'label' => "FedEx Standard Overnight",
                                'cost' => $fedex_std_over_night
                            ));
                        }
                    }
                }
            }
        }
    }
 
    add_action( 'woocommerce_shipping_init', 'cimarketing_shipping_method' );
 
    function add_cimarketing_shipping_method( $methods ) {
        $methods[] = 'CIMarketing_Shipping_Method';
        return $methods;
    }
 
    add_filter( 'woocommerce_shipping_methods', 'add_cimarketing_shipping_method' );
 
    function cimarketing_validate_order( $posted )   {
 
        $packages = WC()->shipping->get_packages();
 
        $chosen_methods = WC()->session->get( 'chosen_shipping_methods' );
         
        if( is_array( $chosen_methods ) && in_array( 'cimarketing', $chosen_methods ) ) {
             
            foreach ( $packages as $i => $package ) {
 
                if ( $chosen_methods[ $i ] != "cimarketing" )
                             
                    continue;
 
                $CIMarketing_Shipping_Method = new CIMarketing_Shipping_Method();
                $weightLimit = (int) $CIMarketing_Shipping_Method->settings['weight'];
                $weight = 0;
 
                foreach ( $package['contents'] as $item_id => $values ) 
                { 
                    $_product = $values['data']; 
                }  
            }       
        } 
    }
 
    add_action( 'woocommerce_review_order_before_cart_contents', 'cimarketing_validate_order' , 10 );
    add_action( 'woocommerce_after_checkout_validation', 'cimarketing_validate_order' , 10 );
    add_action( 'woocommerce_product_options_general_product_data', 'cimarketing_casepack_field' );
    add_action( 'woocommerce_product_options_general_product_data', 'cimarketing_casepack_field' );
    function cimarketing_casepack_field() {
        // Print a custom text field
        woocommerce_wp_text_input( array(
            'id' => '_ci_casepack',
            'label' => 'Case Pack Size',
            'description' => 'How many products per case pack?',
            'desc_tip' => 'true',
            'placeholder' => 'eg: 15'
        ) );
    }
    add_action( 'woocommerce_process_product_meta', 'cimarketing_save_casepack_fields' );
    function cimarketing_save_casepack_fields( $post_id ) {
        if ( ! empty( $_POST['_ci_casepack'] ) ) {
            update_post_meta( $post_id, '_ci_casepack', esc_attr( $_POST['_ci_casepack'] ) );
        }
    }
    
}