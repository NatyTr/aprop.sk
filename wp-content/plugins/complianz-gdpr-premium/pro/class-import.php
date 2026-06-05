<?php

defined('ABSPATH') or die("you do not have access to this page!");

if (!class_exists("cmplz_import_settings")) {
    class cmplz_import_settings
    {
        private static $_this;

        function __construct()
        {
            if (isset(self::$_this))
	            wp_die(sprintf('%s is a singleton class and you cannot create a second instance.', get_class($this)));

            self::$_this = $this;

            add_action('admin_init', array($this, 'process_import_action'),10, 1);
        }

        static function this()
        {
            return self::$_this;
        }

        public function process_import_action(){

            if (!isset($_POST['cmplz_import_settings'])) return;

            if (!current_user_can('manage_options')) return;
            if (!isset($_FILES)) return;

            if (!isset($_POST['cmplz_nonce']) || !wp_verify_nonce($_POST['cmplz_nonce'], 'complianz_save')) return;

            $error = false;
            $data = "";
            $accepted_pages = array(
                'cookie_settings', 'wizard', 'settings'
            );

            if (count($_FILES)>1) $error = __('You can only import one file at once','complianz-gdpr');

            if (!$error) {
                foreach ($_FILES as $file) {
                    if (strpos($file['name'], '.json') === FALSE) {
                        $error = __('This file does not have the correct format', 'complianz-gdpr');
                        continue;
                    }
                    $data = file_get_contents($file['tmp_name']);
                }
            }

            if (!$error && empty($data)){
                $error = __('Empty dataset','complianz-gdpr');
            }

            if (!$error) {
                $arr = explode('#--COMPLIANZ--#', $data);

                if (!isset($arr[0]) || !isset($arr[1])){
                    $error = __('Data integrity check failed', 'complianz-gdpr');
                }

                $length = $arr[1];
                $data = $arr[0];

                $data = json_decode($data, true);
            }

            if (!$error && !empty($data)) {
                foreach ($data as $page => $settings) {
                	if ( $page === 'errors' || $page === 'jquery' ) continue;
                    if ($page !=='banners' && !in_array($page, $accepted_pages)) {
                        COMPLIANZ::$admin->error_message = __('Data integrity check failed','complianz-gdpr');
                        return;
                    }
                    if ($page !== 'banners') {
                        update_option("complianz_options_$page", $settings);
                    } else {
                        //these are exported banners.
                        $banners = $settings;

                        foreach ($banners as $banner) {
                            unset($banner['ID']);
                            $cookiebanner = new CMPLZ_COOKIEBANNER();
                            foreach($banner as $property => $value) {
								if ( is_serialized($value)) {
									$value = unserialize($value);
								}

                                $cookiebanner->{$property} = $value;
                            }
                            $cookiebanner->save();
                        }
                    }
                }
            }

            if (!$error){
                //set wizard to completed??
                //COMPLIANZ::$wizard->set_wizard_completed_once();
                COMPLIANZ::$admin->success_message = __('Imports completed successfully', 'complianz-gdpr');
            } else{
                COMPLIANZ::$admin->error_message = $error;
            }

        }

    }
}
