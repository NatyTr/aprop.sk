<?php
/**
 * @package   Toret Comaptibility
 * @author    toret.cz
 * @license   GPL-2.0+
 * @link      http://toret.cz
 * @copyright 2017 Toret.cz
 */



if( !class_exists( 'Toret_Product_Compatibility' ) ){

    class Toret_Product_Compatibility{


        /**
         * Return product id
         *
         */
        static public function get_id( $_product ){

            $version = toret_check_wc_version();

            if( $version === false ){

                $id = $_product->id;
                    
            }else{

                $id = $_product->get_id();
                            
            }

            return $id;

        }

        /**
         * Return product tax status
         *
         */
        static public function get_tax_status( $_product ){

            $version = toret_check_wc_version();

            if( $version === false ){

                $tax_status = $_product->tax_status;
                    
            }else{

                $tax_status = $_product->get_tax_status();
                            
            }

            return $tax_status;

        }

        /**
         * Return product tax calss
         *
         */
        static public function get_tax_class( $_product ){

            $version = toret_check_wc_version();

            if( $version === false ){

                $tax_class = $_product->get_tax_class();
                    
            }else{

                $tax_class = $_product->get_tax_class();
                            
            }

            return $tax_class;

        }

        /**
         * Return product type
         *
         */
        static public function get_type( $_product ){

            $version = toret_check_wc_version();

            if( $version === false ){

                $type = $_product->product_type;
                    
            }else{

                $type = $_product->get_type();
                            
            }

            return $type;

        }

        /**
         * Return product price include tax
         *
         */
        static public function get_price_inc_tax( $_product ){

            $version = toret_check_wc_version();

            if( $version === false ){

                $price = $_product->get_price_including_tax();
                    
            }else{

                $price = wc_get_price_including_tax( $_product );
                            
            }

            return $price;

        }


        


    }

}