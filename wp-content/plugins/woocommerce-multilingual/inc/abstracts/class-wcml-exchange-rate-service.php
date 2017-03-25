<?php

abstract class WCML_Exchange_Rate_Service{

    private $id;
    private $name;
    private $url;
    private $api_url;

    private $settings = array();

    protected $api_key      = '';

    const REQUIRES_KEY = false;

    public function __construct( $id, $name, $api_url, $url = '' ) {

        $this->id           = $id;
        $this->name         = $name;
        $this->api_url      = $api_url;
        $this->url          = $url;

        $this->settings = get_option('wcml_exchange_rate_service_' . $this->id, array() );

        if( $this->is_key_required() ){
            $this->api_key = $this->get_setting( 'api-key' );
        }

    }

    public function get_name(){
        return $this->name;
    }

    public function get_url(){
        return $this->url;
    }

    /**
     * @param $from
     * @param $to
     * @return mixed
     */
    public abstract function get_rates( $from, $to );

    /**
     * @return array
     */
    public function get_settings(){
        return $this->settings;
    }

    /**
     *
     */
    private function save_settings(){
        update_option('wcml_exchange_rate_service_' . $this->id, $this->settings);
    }

    /**
     * @param $key string
     * @return mixed|null
     */
    public function get_setting( $key ){
        return isset( $this->settings[$key] ) ? $this->settings[$key] : null;
    }

    /**
     * @param $key string
     * @param $value mixed
     */
    public function save_setting( $key, $value ){
        $this->settings[$key] = $value;
        $this->save_settings();
    }

    /**
     * @return bool
     */
    public function is_key_required(){
        return static::REQUIRES_KEY;
    }

    /**
     * @param $error_message string
     */
    public function save_last_error( $error_message ){
        $this->save_setting( 'last_error',
            array(
                'text' => $error_message,
                'time' => date_i18n( 'F j, Y g:i a', current_time( 'timestamp' ) )
            )
        );
    }

    /**
     *
     */
    public function clear_last_error(){
        $this->save_setting( 'last_error', false );
    }

    /**
     * @return mixed
     */
    public function get_last_error(){
        return isset( $this->settings['last_error'] ) ? $this->settings['last_error'] : false;
    }


}