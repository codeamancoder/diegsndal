<?php

class WCML_Exchange_Rates_Currencylayer extends WCML_Exchange_Rate_Service{

    private $id             = 'currencylayer';
    private $name           = 'currencylayer';
    private $url            = 'https://currencylayer.com/';
    private $api_url        = 'http://apilayer.net/api/live?access_key=%s&source=%s&currencies=%s&amount=1';

    protected $api_key      = '';
    protected $requires_key = true;

    const REQUIRES_KEY = true;

    function __construct() {
        parent::__construct( $this->id, $this->name, $this->api_url, $this->url );
    }

    /**
     * @param $from string
     * @param $to array
     * @return array
     * @throws Exception
     */
    public function get_rates( $from, $tos ){

        parent::clear_last_error( );
        $rates = array();

        $url = sprintf( $this->api_url, $this->api_key, $from, join(',', $tos) );

        $http = new WP_Http();
        $data = $http->request( $url );


        if( is_wp_error( $data ) ){

            $http_error = join("\n", $data->get_error_messages() );
            parent::save_last_error( $http_error );
            throw new Exception( $http_error );

        } else {

            $json = json_decode( $data['body'] );

            if( empty( $json->success ) ){
                if( !empty( $json->error->info ) ){
                    if( strpos( $json->error->info, 'You have not supplied an API Access Key' ) !== false ){
                        $error = __('You have entered an incorrect API Access Key', 'woocommerce-multilingual');
                    }else{
                        $error = $json->error->info;
                    }
                } else{
                    $error = __( 'Cannot get exchange rates. Connection failed.', 'woocommerce-multilingual' );
                }
                parent::save_last_error( $error );
                throw new Exception( $error );
            } else{

                if( isset( $json->quotes ) ){
                    foreach( $tos as $to ){
                        if( isset( $json->quotes->{$from.$to} ) ){
                            $rates[$to] = round( $json->quotes->{$from.$to}, 4 );
                        }
                    }
                }

            }
        }

        return $rates;

    }

}