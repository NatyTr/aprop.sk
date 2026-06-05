<?php
defined('ABSPATH') or die("you do not have access to this page!");

if (!class_exists("cmplz_processing")) {
    class cmplz_processing extends cmplz_document
    {
        private static $_this;
        public $position;
        public $cookies = array();
        public $total_steps;
        public $total_sections;
        public $page_url;

        function __construct()
        {
            if (isset(self::$_this))
	            wp_die(sprintf('%s is a singleton class and you cannot create a second instance.', get_class($this)));

            self::$_this = $this;

            //callback from settings
            add_action('cmplz_processing_last_step', array($this, 'processing_last_step_callback'), 10, 1);

	        //link action to custom hook
	        foreach (cmplz_get_regions() as $region => $label){
		        add_action("cmplz_wizard_processing-$region", array($this, 'processing_after_last_step'), 10, 1);
	        }
            add_action('admin_enqueue_scripts', array($this, 'enqueue_assets_processing'));
        }

        static function this()
        {
            return self::$_this;
        }



        public function enqueue_assets_processing($hook)
        {
            if (!isset($_GET['post_type']) || ($_GET['post_type'] !== 'cmplz-processing' && $_GET['post_type'] !== 'cmplz-dataleak') ) return;
            $min = (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) ? '' : '.min';
            $load_css = cmplz_get_value('use_document_css');
            if ($load_css) {
                wp_register_style('cmplz-document', cmplz_url . "assets/css/document$min.css", false, cmplz_version);
                wp_enqueue_style('cmplz-document');
            }

			wp_register_style('cmplz-posttypes', cmplz_url . "assets/css/posttypes$min.css", false, cmplz_version);
            wp_enqueue_style('cmplz-posttypes');

        }


        public function processing_agreements(){
            $args = array(
                    'post_type' => 'cmplz-processing',
	                'numberposts' => -1,
            );
            $posts = get_posts($args);
            return wp_list_pluck($posts, 'post_title','ID');
        }

        /**
         * Check if sharing with processors is enabled.
         *
         *
         * */

        public function has_processors(){
            if ( cmplz_get_value('share_data_other') ==2 ){
                return false;
            }

            return true;
        }

		/**
		 * Check if there are missing agreements for processors
		 *
		 * @return bool
		 */
        public function has_missing_agreements_for_processors(){

			if ( cmplz_get_value('share_data_other') !== '1' || !$this->has_processors()){
			      return false;
			}

            $count=0;

            $processors = cmplz_get_value('processor');
            if (!empty($processors)) {
                foreach ($processors as $processor) {
                    if (!isset($processor['processing_agreement']) || intval($processor['processing_agreement']) == 0) {
                        $count++;
                    }
                }
            }

            return $count>0;
        }

		/**
		 * Show the processing agreement page
		 * @param string $region
		 */

        public function processing_agreement_page($region)
        {
            $label = COMPLIANZ::$config->regions[$region]['label_full'];
            ?>
            <div class="wrap">
                <?php if (COMPLIANZ::$license->license_is_valid()) { ?>
                    <?php COMPLIANZ::$wizard->wizard("processing-$region", cmplz_sprintf(_x("Processing Agreement (%s)",'Title on page', 'complianz-gdpr'),$label)); ?>
                <?php } else {
					$link = '<a href="'.add_query_arg(array('page'=>'cmplz-settings#license'), admin_url('admin.php')).'">';
					cmplz_admin_notice( cmplz_sprintf(__( 'Your license needs to be %sactivated%s to unlock the wizard', 'complianz-gdpr' ), $link, '</a>' ));
                } ?>
            </div>
            <?php
        }

		/**
		 * Last step callback for processing agreements
		 */
        public function processing_last_step_callback()
        {
            $page = COMPLIANZ::$wizard->wizard_type();
            if (!COMPLIANZ::$wizard->all_required_fields_completed($page)) {
                cmplz_notice( __("Not all required fields are completed yet. Please check the steps to complete all required questions", 'complianz-gdpr'), 'notice');
            } else {
                cmplz_notice(
                		cmplz_sprintf( __( "Click %s to complete the configuration and create a Processing Agreement.", 'complianz-gdpr'),__("View document", 'complianz-gdpr') )
						.' '.
						__( "You can come back to change your configuration at any time.", 'complianz-gdpr') , 'notice');
            }
        }

		/**
		 * After last step
		 */
        public function processing_after_last_step()
        {
            if (!cmplz_user_can_manage()) return;
            $page = str_replace('cmplz-', '',sanitize_title($_GET['page']));

            if (isset($_POST['cmplz-finish']) || isset($_POST['cmplz-previous']) || isset($_POST['cmplz-save']) || isset($_POST['cmplz-next'])) {
                $date = cmplz_localize_date(date(get_option('date_format'), time()));
                $post_id = COMPLIANZ::$wizard->post_id();
                $title = cmplz_get_value('name_of_processor') . " " . $date;
                if (empty($title)) $title = cmplz_sprintf(__("Processing Agreement %s", 'complianz-gdpr'), $date);
                $status = isset($_POST['cmplz-finish']) ? 'publish' : 'draft';

                $args = array(
                    'post_status' => $status,
                    'post_title' => $title,
                    'post_type' => 'cmplz-processing',
                );
                if (!$post_id) {
                    //create new post type processing, and add all wizard data as meta fields.
                    $post_id = wp_insert_post($args);
                    $_POST['post_id'] = $post_id;
                } else {
                    $args['ID'] = $post_id;
                    wp_update_post($args);
                }

                $this->set_region($post_id);

                //get all fields for this page
                $fields = COMPLIANZ::$config->fields($page);
                foreach ($fields as $fieldname => $field) {
                    update_post_meta($post_id, $fieldname, cmplz_get_value($fieldname));
                }

                //redirect to posts overview
                if ($status == 'publish') {
                    wp_redirect(admin_url("post.php?post=$post_id&action=edit"));
                    delete_option('complianz_options_processing');
                    exit();
                }
            }
        }

    }
} //class closure
