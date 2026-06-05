<?php
defined('ABSPATH') or die("you do not have access to this page!");

if (!class_exists("cmplz_dataleak")) {
    class cmplz_dataleak extends cmplz_document
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
            add_action('cmplz_dataleak_last_step', array($this, 'wizard_last_step_callback'), 10, 1);

            //link action to custom hook
            foreach (cmplz_get_regions() as $region => $label){
	            add_action("cmplz_wizard_dataleak-$region", array($this, 'dataleak_after_last_step'), 10, 1);
            }

            //dataleaks:
            add_action('cmplz_dataleak_conclusion', array($this, 'dataleak_conclusion'));
            add_action('admin_enqueue_scripts', array($this, 'enqueue_assets_dataleaks'));
            add_action('wp_ajax_get_email_batch_progress', array($this, 'ajax_get_email_batch_progress'));
        }

        static function this()
        {
            return self::$_this;
        }

		/**
		 * Enqueue some assets
		 *
		 * @param $hook
		 */

        public function enqueue_assets_dataleaks($hook)
        {
            global $post;

            if ((!$post || get_post_type($post) !== 'cmplz-dataleak') && (!isset($_GET['post_type']) || $_GET['post_type'] !== 'cmplz-dataleak')) return;

            if ($post) {

                $min = (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) ? '' : '.min';
				wp_register_style( 'cmplz-wizard', cmplz_url . "assets/css/wizard$min.css", false, cmplz_version );
				wp_enqueue_style( 'cmplz-wizard' );

                $load_css = cmplz_get_value('use_document_css');
                if ($load_css) {
                    wp_register_style('cmplz-document', cmplz_url . "assets/css/document$min.css", false, cmplz_version);
                    wp_enqueue_style('cmplz-document');
                }

				wp_register_style('cmplz-posttypes', cmplz_url . "assets/css/posttypes$min.css", false, cmplz_version);
				wp_enqueue_style('cmplz-posttypes');
                wp_enqueue_script('cmplz_dataleak', cmplz_url . "pro/assets/js/dataleak.js", array('jquery'), cmplz_version, true);

                wp_localize_script(
                    'cmplz_dataleak',
                    'cmplz_dataleak',
                    array(
                        'admin_url' => admin_url('admin-ajax.php'),
                        'progress' => $this->get_email_batch_progress($post->ID),
                        'post_id' => $post->ID,
                        'complete_string' => __('Email sending complete', 'complianz-gdpr'),
                    )
                );
            }
        }

		/**
		 * Get mail batch progress
		 *
		 * @param int $post_id
		 *
		 * @return float|int
		 */
        public function get_email_batch_progress($post_id)
        {

            $args = array(
                'fields' => array('ID', 'user_email'),
            );
            $total_users = get_users($args);
            $total_count = count($total_users);

            //for each user, get email
            $args = array(
                'meta_query' => array(
                    array(
                        'key' => '_cmplz_dataleak_report_sent',
                        'value' => $post_id,
                        'compare' => '==',
                    )
                ),
                'fields' => array('ID', 'user_email'),
            );
            $sent_users = get_users($args);
            $sent_count = count($sent_users);
            if ($sent_count >= $total_count) return 100;

            return ($sent_count / $total_count) * 100;
        }

		/**
		 * Start sending a mail batch
		 *
		 * @param int $post_id
		 */
        public function send_email_batch($post_id)
        {

            if (!current_user_can('manage_options')) return;

            //for each user, get email
            $args = array(
                'meta_query' => array(
                    'relation' => 'OR',
                    array(
                        'key' => '_cmplz_dataleak_report_sent',
                        'value' => $post_id,
                        'compare' => '!=',
                    ),
                    array(
                        'key' => '_cmplz_dataleak_report_sent',
                        'compare' => 'NOT EXISTS',
                    )
                ),
                'number' => 10,
                'fields' => array('ID', 'user_email'),
            );
            $users = get_users($args);

            foreach ($users as $user) {
                update_user_meta($user->ID, '_cmplz_dataleak_report_sent', $post_id);
                $this->send_mail($user->user_email, $post_id);
            }
        }

		/**
		 * Send mail
		 *
		 * @param string $to
		 * @param int $post_id
		 *
		 * @return bool|mixed|void
		 */

        public function send_mail($to, $post_id)
        {
            if (!is_email($to)) return;

            $headers = array();
            $subject = get_post_meta($post_id, 'cmplz_subject', true);
            $sender = get_post_meta($post_id, 'cmplz_sender', true);
            if (empty($sender)) $sender = get_bloginfo('name');
            if (empty($subject)) $subject = __('Notification of dataleak', 'complianz-gdpr');

            $message = COMPLIANZ::$document->get_document_html(COMPLIANZ::$wizard->get_type($post_id), $this->get_region($post_id), $post_id);

            add_filter('wp_mail_content_type', function ($content_type) {
                return 'text/html';
            });

            //$attachments = array(WP_CONTENT_DIR . '/uploads/file_to_attach.zip');
            $success = wp_mail($to, $subject, $message, $headers);

            // Reset content-type to avoid conflicts -- http://core.trac.wordpress.org/ticket/23578
            remove_filter('wp_mail_content_type', 'set_html_content_type');
            return $success;
        }


		/**
		 * Ajax get mail batch progress
		 * @return int
		 */
        public function ajax_get_email_batch_progress()
        {
            if (!isset($_GET['post_id'])) return 0;
            $subject = (isset($_GET['subject'])) ? sanitize_text_field($_GET['subject']) : '';
            $sender = (isset($_GET['sender'])) ? sanitize_text_field($_GET['sender']) : '';


            $post_id = intval($_GET['post_id']);
            update_post_meta($post_id, 'cmplz_subject', $subject);
            update_post_meta($post_id, 'cmplz_sender', $sender);
            $this->send_email_batch($post_id);
            $output = array(
                "progress" => $this->get_email_batch_progress($post_id),
            );

            $obj = new stdClass();
            $obj = $output;
            echo json_encode($obj);
            wp_die();
        }

		/**
		 * Generate a conclusion
		 * @param int|false $post_id
		 */
		public function dataleak_conclusion($post_id=false)
		{
			//if the function is called from a hook, the post_id is not a post_id, but a field
			$region = $this->get_region($post_id);
			$dataleak_type = $this->get_dataleak_type($post_id);
			$html = "";

			$dpo = array(
					'eu' => array(
							'label'     => __( 'data protection authority', 'complianz-gdpr' ),
					),
					'uk' => array(
							'label'     => __( "Information Commissioner's Office", 'complianz-gdpr' ),
							'url' 		=> 'https://ico.org.uk/for-organisations/report-a-breach/',
					),
					'us' => array(
							'label'     => __( 'Attorney General', 'complianz-gdpr' ),
					),
					'ca' => array(
							'label'     => __( 'data protection authority', 'complianz-gdpr' ),
							'url' 		=> 'https://www.priv.gc.ca/en/report-a-concern/report-a-privacy-breach-at-your-organization/report-a-privacy-breach-at-your-business/',
					),
					'au' => array(
							'label'     => __( 'Australian Information Commissioner', 'complianz-gdpr' ),
							'url' 		=> 'https://forms.business.gov.au/smartforms/servlet/SmartForm.html?formCode=OAIC-NDB&tmFormVersion=10.0',
							'time'		=> __( '72 hours', 'complianz-gdpr' ),
					),
					'za' => array(
							'label'     => __( 'Information Regulator', 'complianz-gdpr' ),
							'url' 		=> 'https://www.justice.gov.za/inforeg/',
					),
					'br' => array(
							'label'		=> __( 'National Data Protection Authority', 'complianz-gdpr' ),
							'url'		=> 'https://www.gov.br/secretariageral/pt-br/sei-peticionamento-eletronico',
							'time'		=> __( '48 hours', 'complianz-gdpr' ),
					),
			);
			$report_dpo = '';
			if (isset($dpo[$region]['label'])){
				$dpo_text = isset($dpo[$region]['url']) ? "<a target='_blank' href='". $dpo[$region]['url'] ."'>". $dpo[$region]['label'] ."</a>" : $dpo[$region]['label'];
				$report_dpo = __( "Please report this incident to the", 'complianz-gdpr' ) . ' ' . $dpo_text;
				$report_dpo .= isset($dpo[$region]['time']) ? ' ' . cmplz_sprintf(__("within %s after the incident occurred", 'complianz-gdpr'), $dpo[$region]['time']) . '.' : '.';
			}


			// Defaults for the dataleak conclusions
			$conclusions = array(
					'report' => array(
							'check_text' 	=> __( 'Checking if you should report to the', 'complianz-gdpr' ) . ' ' . $dpo[$region]['label'] . '.',
							'report_text' 	=>  __( 'The security incident does not have to be reported to the', 'complianz-gdpr' ) . ' ' . $dpo[$region]['label'] . '.',
							'report_status' => 'success',
					),
					'report_to_involved' => array(
							'check_text' 	=> __( 'Checking if you should report to those involved', 'complianz-gdpr' ) . '.',
							'report_text' 	=> __( 'It is not necessary to inform those involved', 'complianz-gdpr' ) . '.',
							'report_status' => 'success',
					),
			);

			// Dataleak type specific specific URLs and text to help report the incident
			if ($this->dataleak_has_to_be_reported($post_id)) {
				$conclusions['report']['report_text'] = $report_dpo;
				$conclusions['report']['report_status'] = 'error';

				if ($this->dataleak_has_to_be_reported_to_involved($post_id)) {
					$conclusions['report_to_involved']['report_status'] = 'error';
					$conclusions['report_to_involved']['report_text'] = __("You should report this incident to those involved.", 'complianz-gdpr');
					$conclusions['report_to_involved']['report_text'] .= ' ' . __("You can use the generated report to inform those involved.", 'complianz-gdpr');
					if (!$post_id) $conclusions['report_to_involved']['report_text'] .= ' ' . __("Click view document to save and view this report.", 'complianz-gdpr');
				}

				// Can reduse risk for CA
				if (cmplz_get_value('can-reduce-risk-'. $region)==='yes' && $region === 'ca'){
					$conclusions['can_reduce_risk']['report_text'] = __("You should make a notice to the organizations that may be able to reduce the risk of harm from the breach or to mitigate that harm.", 'complianz-gdpr');
					$conclusions['can_reduce_risk']['report_status'] = 'warning';
				}

				if ( $dataleak_type == '2' ) {
					$reach_large = cmplz_get_value('reach-of-dataloss-large-'. $region, $post_id ) == 'yes';
					$california_visitors = cmplz_get_value('california-visitors', $post_id )  == 'yes';
					$login_credentials = cmplz_get_value('what-information-was-involved-'. $region, $post_id) == 'username-email';
					if ( $login_credentials ) {
						$conclusions['login_credentials']['report_text'] = __("In this particular case where login credentials of an email account are involved, it is not allowed to send the security breach notification to that email address.", 'complianz-gdpr');
						$conclusions['login_credentials']['report_status'] = 'error';
					}
					if ( $california_visitors ) {
						$conclusions['california']['report_text'] = __("The databreach concerns California residents, which means the databreach has to be reported to the Attorney General.", 'complianz-gdpr');
						$conclusions['california']['report_status'] = 'error';
					}
					if ( $reach_large ) {
						$conclusions['reach_of_data']['report_text'] = __("Considering the scale of the databreach, it is recommended to get legal counsel regarding this databreach.", 'complianz-gdpr');
						$conclusions['reach_of_data']['report_status'] = 'warning';
					}

				}
			}
			// add check texts if they are empty
			foreach($conclusions as $key => $conclusion){
				if (!isset($conclusion['check_text'])){
					$conclusions[$key]['check_text'] = __("Checking databreach laws matching your setup", 'complianz-gdpr') . '.';
				}
			}

			$title = __( "Your dataleak report:", 'complianz-gdpr' );
			// don't animate if post isset
			$animate = !isset($_GET['post']) ? true : false;
			cmplz_conclusion( $title, $conclusions, $animate );
			if ( $this->dataleak_has_to_be_reported($post_id) ){
				cmplz_notice(__("This wizard is intended to provide a general guide to a possible data breach.","complianz-gdpr").'&nbsp;'.__("Specialist legal advice should be sought about your specific circumstances.","complianz-gdpr"), 'warning' );
			}

		}

		/**
		 * Wrap a string in a li item
		 * @param string $msg
		 *
		 * @return string
		 */
        public function wrap_line($msg){
            return '<li>'.$msg.'</li>';
        }


		/**
		 * Check if dataleak has to be reported to those involved.
		 *
		 * @param int|false $post_id
		 *
		 * @return bool
		 */
        public function dataleak_has_to_be_reported_to_involved($post_id = false)
        {
			if ( !$post_id && isset($_GET['page']) ) {
				$region = $this->get_region(sanitize_title( $_GET['page'] ));
				$dataleak_type = $this->get_dataleak_type(sanitize_title( $_GET['page'] ));
			} else {
				$region = $this->get_region($post_id);
				$dataleak_type = $this->get_dataleak_type($post_id);
			}

            if ( $dataleak_type == '1' ) {
				return (cmplz_get_value('risk-of-data-loss-'. $region, $post_id) == '3');
            }

			if ( $dataleak_type == '2' ) {
				if ( cmplz_get_value('type-of-dataloss-'. $region) == 3 ) return false;
				$what_information_was_involved = cmplz_get_value('what-information-was-involved-'. $region, $post_id);
				if ( $what_information_was_involved == 'none' ) return false;
			}

			if ( $dataleak_type == '3' ) {
				if ( cmplz_get_value('type-of-dataloss-'. $region) == 3 ) return false;
				return (cmplz_get_value('risk-of-data-loss-'. $region, $post_id) !== '3' && cmplz_get_value('risk-of-data-loss-'. $region, $post_id) !== '4');

			}

            return true;
        }

		/**
		 * Check if a dataleak has to be reported
		 *
		 * @param int|bool $post_id
		 *
		 * @return bool
		 *
		 * Databreach type 1: EU, UK
		 * Databreach type 2: US
		 * Databreach type 3: CA, AU, ZA
		 *
		 */
        public function dataleak_has_to_be_reported($post_id=false)
        {
			if ( !$post_id && isset($_GET['page']) ) {
				$region = $this->get_region(sanitize_title( $_GET['page'] ));
				$dataleak_type = $this->get_dataleak_type(sanitize_title( $_GET['page'] ));
			} else {
				$region = $this->get_region($post_id);
				$dataleak_type = $this->get_dataleak_type($post_id);
			}

            if ( $dataleak_type == '1' ) {
                $type_of_dataloss_not_serious = cmplz_get_value('type-of-dataloss-'. $region, $post_id) == '3' ? true : false;
                $reach_of_dataloss_minor = cmplz_get_value('reach-of-dataloss-'. $region, $post_id) == '3' ? true : false;
                if ( $type_of_dataloss_not_serious ) {
                	return false;
				}
                if ( $reach_of_dataloss_minor ) return false;
            }

            if ( $dataleak_type == '2'){
				if ( cmplz_get_value('type-of-dataloss-'. $region) == 3 ) return false;
                return cmplz_get_value('what-information-was-involved-'. $region, $post_id)!=='none';
            }

	        if ( $dataleak_type == '3' ) {
	        	if ( cmplz_get_value('type-of-dataloss-'. $region) == 3 ) return false;
				$riskofabuse = cmplz_get_value('risk-of-data-loss-'. $region)==='1';
				$sensitive = cmplz_get_value('risk-of-data-loss-'. $region)==='2';
				if ( !$riskofabuse && !$sensitive ) return false;
	        }

			return true;
        }

		/**
		 * Generate the dataleak page
		 * @param $region
		 */
        public function dataleak_page($region)
        {
			$label = COMPLIANZ::$config->regions[$region]['label_full'];
            ?>
            <div class="wrap">
                <?php if (COMPLIANZ::$license->license_is_valid()) { ?>
                    <?php
                    COMPLIANZ::$wizard->wizard("dataleak-".$region, cmplz_sprintf(__("Dataleak (%s)", 'complianz-gdpr'),$label) ); ?>
                <?php } else {
					$link = '<a href="'.add_query_arg(array('page'=>'cmplz-settings#license'), admin_url('admin.php')).'">';
					cmplz_admin_notice( cmplz_sprintf(__( 'Your license needs to be %sactivated%s to unlock the wizard', 'complianz-gdpr' ), $link, '</a>' ));
                } ?>
            </div>
            <?php
        }

        public function dataleak_after_last_step()
        {
            if (!cmplz_user_can_manage()) return;

            //check if this is an already existing post
            $post_id = COMPLIANZ::$wizard->post_id();

            //only start saving after second step
            $page = COMPLIANZ::$wizard->get_type($post_id);

            if (COMPLIANZ::$wizard->step($page) <= 2) {
                return;
            }

            if (!$this->dataleak_has_to_be_reported(false)) return;

            if (isset($_POST['cmplz-finish']) || isset($_POST['cmplz-previous']) || isset($_POST['cmplz-save']) || isset($_POST['cmplz-next'])) {
                $date = cmplz_localize_date(date(get_option('date_format'), time()));

                $status = isset($_POST['cmplz-finish']) ? 'publish' : 'draft';
                $args = array(
                    'post_status' => $status,
                    'post_title' => cmplz_sprintf(__("Dataleak %s", 'complianz-gdpr'), $date),
                    'post_type' => 'cmplz-dataleak',
                );
                //create new post type, and add all wizard data as meta fields.
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
                    delete_option('complianz_options_dataleak');
                    exit();
                }
            }
        }


		/**
		 * Add send mail button to databreach pages
		 */
        public function send_mail_button()
        {
            add_thickbox();
            global $post;
            $complete = $this->get_email_batch_progress($post->ID) >= 100 ? true : false;
            ?>

            <input class="button thickbox" title="" type="button"
                <?php echo $complete ? 'disabled' : ''; ?>
                   alt="#TB_inline?height=400&width=800&inlineId=cmplz_email_users"
                   value="<?php _e('Email your users', 'complianz-gdpr') ?>"/>

            <div id="cmplz_email_users" style="display: none;">
                <h1><?php _e("Inform your users about a data leak", 'complianz-gdpr') ?></h1>
                <p><?php _e('You can send the notification of a data leak to your website users.','complianz-gdpr')?></p>
                <div id="cmplz-send-data">
                    <p>
                        <label><?php _e('Sender name', 'complianz-gdpr') ?></label><br>
                        <input id="cmplz_sender" type="text" placeholder="<?php _e('Sender name', 'complianz-gdpr') ?>"
                               value="<?php echo esc_html(get_bloginfo('name')) ?>">
                    </p>
                    <p>
                        <label><?php _e('Email subject', 'complianz-gdpr') ?></label><br>
                        <input id="cmplz_subject" type="text" placeholder="<?php _e('Email subject', 'complianz-gdpr') ?>"
                               value="<?php _e('Notification of dataleak', 'complianz-gdpr'); ?>"></p>
                </div>
                <div id="cmplz-scan-progress">
                    <div class="cmplz-progress-bar"></div>
                </div>

                <button class="button" id="cmplz-start-mail">
                    <?php _e("Start sending", 'complianz-gdpr') ?>
                </button>
                <button class="button"  id="cmplz_close_tb_window"><?php _e("Pause sending", 'complianz-gdpr') ?></button>
            </div>
            <p>
                <?php if ($complete) _e('You already have sent this notification to your users.', 'complianz-gdpr') ?>
            </p>

            <?php
        }


    }
} //class closure
