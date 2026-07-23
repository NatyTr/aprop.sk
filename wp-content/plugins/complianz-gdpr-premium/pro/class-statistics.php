<?php defined('ABSPATH') or die("you do not have access to this page!");
if (!class_exists('cmplz_statistics')) {
    class cmplz_statistics
    {
        private static $_this;
        public $prefix;

        function __construct()
        {
            if (isset(self::$_this))
	            wp_die(sprintf('%s is a singleton class and you cannot create a second instance.', get_class($this)));
            self::$_this = $this;
            add_action('admin_init', array($this, 'update_db_check'), 1);
            add_action("admin_enqueue_scripts", array($this, "enqueue_scripts"));
            add_filter('cmplz_user_banner_id', array($this, 'get_user_banner_id'));
            add_filter('cmplz_user_data', array($this, 'get_user_data'));
            add_action('cmplz_store_consent', array($this, 'store_consent'), 10, 3);
            add_action('complianz_before_save_settings_option', array($this, 'init_statistics_on_settings_change'), 10, 4);
            add_action('wp_ajax_cmplz_archive_cookiebanner', array($this, 'archive_cookiebanner'));
            add_action('wp_ajax_cmplz_restore_cookiebanner', array($this, 'restore_cookiebanner'));
            add_action('wp_ajax_cmplz_get_graph', array($this, 'get_graph'));
            add_action('cmplz_before_cookiebanner_list', array($this, 'notices'));
            add_action('cmplz_before_cookiebanner_list', array($this, 'graph'));
            add_action('cmplz_after_cookiebanner_title', array($this, 'after_cookiebanner_title'));
            add_filter('cmplz_ab_testing_enabled', array($this, 'ab_testing_enabled'));
            add_filter('cmplz_cookiebanner_name', array($this, 'cookiebanner_name'));
            add_filter('cmplz_show_cookiebanner_list_view', array($this,'show_cookiebanner_list_view'));
        }

        static function this()
        {
            return self::$_this;
        }

        /**
         * Override the showing of the list view for banners
         * @hooked cmplz_show_cookiebanner_list_view
         * @param $show
         * @return bool
         */

        public function show_cookiebanner_list_view($show){
            return true;
        }

        public function cookiebanner_name($name){
            if ($this->best_performer_enabled()) {
                $name .= '<br>'.__('Because this variation was determined to get the best results, this cookie warning was enabled as your default cookie warning', 'complianz-gdpr');
            }
            return $name;
        }

        /**
         * Override free function to enable a/b testing when active
         * @param $enabled
         * @return bool $enabled
         *
         */

        public function ab_testing_enabled($enabled)
        {
            return cmplz_get_value('a_b_testing');
        }

        /**
         * In free, we have only one cookie banner, so we don't show the add banner and reset stats options
         * In premium we show all banners, the user can click to a subpage.
         */
        public function after_cookiebanner_title()
        {
            //if geoip is not used, we only have one cookie banner, so go straight to the edit page.
            if (cmplz_ab_testing_enabled()) { ?>
                <a href="<?php echo admin_url('admin.php?page=cmplz-cookiebanner&action=new'); ?>"
                   class="button button-primary"><?php _e('Add banner', 'complianz-gdpr') ?></a>
                <a href="<?php echo admin_url('admin.php?page=cmplz-cookiebanner&action=reset_statistics'); ?>"
                   class="button button-primary cmplz-reset"><?php _e('Restart a/b testing', 'complianz-gdpr') ?></a>
            <?php }
        }

        /**
         * Restore a cookiebanner
         * @hooked wp_ajax_cmplz_restore_cookiebanner
         */

        public function restore_cookiebanner()
        {

            if (!current_user_can('manage_options')) return;

            if (isset($_POST['banner_id'])) {

                $banner = new CMPLZ_COOKIEBANNER(intval($_POST['banner_id']));
                $banner->restore();

                $response = json_encode(array(
                    'success' => true,
                ));

                header("Content-Type: application/json");
                echo $response;
                exit;
            }
        }


		/**
		 * Initialize the statistics if the ab setting is changed
		 * This ensures the data is cleared on disabling, and sets the start time on enabling.
		 * @param string $fieldname
		 * @param mixed $fieldvalue
		 * @param mixed $prev_value
		 * @param string $type
		 */

        public function init_statistics_on_settings_change($fieldname, $fieldvalue, $prev_value, $type)
        {
            if ($fieldvalue === $prev_value) return;

            if ($fieldname == 'a_b_testing') {
                $this->init_statistics();

            }
        }

		/**
		 * Restart or init statistics
		 */
        public function init_statistics()
        {
            if (!current_user_can('manage_options')) return;
			cmplz_update_all_banners();
            update_option('cmplz_tracking_ab_started', time());
            update_option('cmplz_enabled_best_performer', false);
        }

        /**
         * If ab testing is enabled, and the plugin has been tracking for more than a month, the best performing banner will get selected as default banner.
         *
         *
         * */


        public function cron_maybe_enable_best_performer()
        {
            if (!cmplz_ab_testing_enabled()) return;

            if ($this->seconds_left_ab_tracking()>0) {
                return;
            }

            //testing is currently enabled, and we have been testing more than a month. Time to set the best performing one, and disable tracking.
            $best_performer = $this->best_performing_cookiebanner();
            if ($best_performer) {
                $banner = new CMPLZ_COOKIEBANNER($best_performer);
                $banner->default = true;
                $banner->save();
                $this->init_statistics();
            }

            //disable tracking
            $cookie_settings = get_option('complianz_options_settings');
            $cookie_settings['a_b_testing'] = false;
            update_option('complianz_options_settings', $cookie_settings);


            //store this change
            update_option('cmplz_enabled_best_performer', true);

        }


        /**
         * In case of ab testing, the user data called through ajax overrides the default cookie setting data.
         * This way, even with caching, the data can be loaded dynamically.
         *
         * */

        public function get_user_data()
        {
            if (!cmplz_ab_testing_enabled()) return array();
			$banner = new CMPLZ_COOKIEBANNER( $this->get_user_banner_id() );
			return $banner->get_front_end_settings();
        }

        /**
         *
         * For a/b testing, get a random banner id. If the user visited before, get that same banner id.
         * @return int banner_id
         */

        public function get_user_banner_id()
        {

            if (!cmplz_ab_testing_enabled()) {
                return cmplz_get_default_banner_id();
            }
            $banners = wp_list_pluck(cmplz_get_cookiebanners(), 'ID');
            $random_key = array_rand($banners);
            $random = $banners[$random_key];
            $user_banner_id = 0;
            global $wpdb;
            if (isset($_COOKIE['cmplz_id']) && $_COOKIE['cmplz_id']>0 ) {
                $visitor_id = intval($_COOKIE['cmplz_id']);
				$user_banner_id = wp_cache_get('cmplz_user_banner_id_'.$visitor_id, 'complianz');
				if ( !$user_banner_id ) {
					$user_banner_id = $wpdb->get_var($wpdb->prepare("SELECT cookiebanner_id from {$wpdb->prefix}cmplz_statistics WHERE ID = %s", $visitor_id));
					wp_cache_set('cmplz_user_banner_id_'.$visitor_id, $user_banner_id, 'complianz', HOUR_IN_SECONDS);
				}
                //check if this variation still exists
                if (!in_array($user_banner_id, $banners)) {
                    $user_banner_id = $random;
                    $success = $wpdb->update($wpdb->prefix . 'cmplz_statistics',
                        array('cookiebanner_id' => $user_banner_id),
                        array('ID' => $visitor_id)
                    );
                    //if the update failed, the user wasn't found in the database, so we insert it fresh
                    if ($success === 0) {
                        $user_banner_id = 0;
                    }
                }
            }

            if ($user_banner_id == 0) {
				$user_banner_id = $random;
				$wpdb->insert($wpdb->prefix . 'cmplz_statistics',
						array('cookiebanner_id' => $user_banner_id)
				);
				$visitor_id = $wpdb->insert_id;
				$this->setcookie($visitor_id);
            }

            return $user_banner_id;
        }

		/**
		 * Set User cookie for ab testing or records of consent
		 * @param $visitor_id
		 */
        public function setcookie($visitor_id) {
			$path = COMPLIANZ::$cookie_admin->get_cookie_path();
			$prefix = COMPLIANZ::$cookie_admin->get_cookie_prefix();
			$options = array (
				'expires' => time() + (DAY_IN_SECONDS * 365),
				'path' => $path,
				'secure' => is_ssl(),
				'samesite' => 'Lax' // None || Lax  || Strict
			);

			if (cmplz_get_value( 'set_cookies_on_root' )) {
				$options['domain'] = COMPLIANZ::$cookie_admin->get_cookie_domain();
			}

			if (version_compare(PHP_VERSION, '7.3', '<')) {
				$domain = isset($options['domain']) ? $options['domain'] : '';
				setcookie(
					$prefix.'id',
					$visitor_id,
					time() + (DAY_IN_SECONDS * 365),
					$path,
					$domain,
					is_ssl(),
					false
				);
			} else {
				setcookie( $prefix.'id', $visitor_id, $options );
			}
		}

		/**
		 * Run database upgrade if necessary
		 */
        public function update_db_check()
        {
			if (get_option('cmplz_statsdb_version') != cmplz_version) {

                require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
                global $wpdb;
                $charset_collate = $wpdb->get_charset_collate();

                $table_name = $wpdb->prefix . 'cmplz_statistics';
                $sql = "CREATE TABLE $table_name (
                  `ID` int(11) NOT NULL AUTO_INCREMENT,
                  `region` varchar(255) NOT NULL,
                  `pageviews` int(11) NOT NULL,
                  `consenttype` varchar(255) NOT NULL,
                  `ip` varchar(255) NOT NULL,
                  `time` varchar(255) NOT NULL,
                  `do_not_track` int(11) NOT NULL,
                  `no_choice` int(11) NOT NULL,
                  `no_warning` int(11) NOT NULL,
                  `functional` int(11) NOT NULL,
                  `preferences` int(11) NOT NULL,
                  `statistics` int(11) NOT NULL,
                  `marketing` int(11) NOT NULL,
                  `services` text NOT NULL,
                  `poc_url` text NOT NULL,
                  `cookiebanner_id` int(11) NOT NULL,
                  PRIMARY KEY  (ID)
                ) $charset_collate;";

                dbDelta($sql);

                update_option('cmplz_statsdb_version', cmplz_version);
            }
        }

        /**
         * Archive a cookiebanner
         * @hooked wp_ajax_cmplz_archive_cookiebanner
         */

        public function archive_cookiebanner()
        {
            if (!current_user_can('manage_options')) return;

            if (isset($_POST['banner_id'])) {
                $banner_id = intval($_POST['banner_id']);

                $banner = new CMPLZ_COOKIEBANNER($banner_id);
                $banner->archive();

                $response = json_encode(array(
                    'success' => true,
                ));

                header("Content-Type: application/json");
                echo $response;
                exit;
            }
        }


		/**
		 * Each page page_view, we check if this user was already listed
		 * By checking the cookie. No usage data is stored, so we don't need to have a cookie warning for this
		 * If user was not listed before, we add a new entry
		 * @param array $consented_categories
		 * @param array $consented_services
		 * @param string $consenttype
		 */

        public function store_consent( $consented_categories, $consented_services, $consenttype )
        {
			$time = time() + ( 60 * 60 * get_option( 'gmt_offset' ) );
            $visitor_is_registered = true;
            $user_ip = COMPLIANZ::$geoip->get_current_ip();
            $region = COMPLIANZ::$geoip->region();
			if ( strlen( $user_ip ) >0 ) {
				$user_ip = apply_filters('cmplz_records_of_consent_user_ip', substr( $user_ip, 0, -3 ).'***' , $user_ip);
			}

	        $args = array(
					'pageviews'    => 1,
					'consenttype'  => $consenttype,
					'region'       => $region,
					'ip'           => $user_ip,
					'time'         => $time,
					'no_warning'   => false,
					'do_not_track' => false,
					'no_choice'    => false,
					'functional'   => false,
					'preferences'  => false,
					'statistics'   => false,
					'marketing'    => false,
	        );

	        foreach ( $consented_categories as $consented_category ) {
	        	if ( isset($args[$consented_category]) ) {
					$args[$consented_category] = true;
				}
	        }
			//only consented on true
			$consented_services = array_filter($consented_services, function($val){ return $val==1;});
			$consented_services = array_keys($consented_services);
			$args['services'] = implode(',',array_map('sanitize_title', $consented_services));

	        //if records of consent is enabled, add the last pdf as poc.
			//if a new cookie policy generation is enabled, don't add, this will get handled after pdf generation.
			if ( cmplz_get_value('records_of_consent') === 'yes' && !get_option( 'cmplz_generate_new_cookiepolicy_snapshot')) {
				//get last poc pdf file, counting back from now.
				$file = COMPLIANZ::$records_of_consent->get_poc_for_record( $time, $region );
				//file has path, url, file, time
				if ( $file ) {
					$args['poc_url'] = $file['url'];
				}
			}

            global $wpdb;
            if ( isset($_COOKIE['cmplz_id']) && intval($_COOKIE['cmplz_id'])>0 ) {
                $visitor_id = intval($_COOKIE['cmplz_id']);
                //we increase pageviews, as a way to make sure the data is changed even when the category has not changed.
                //if we do not do this, the user will be added twice, as success will return 0
                $pageviews = intval($wpdb->get_var($wpdb->prepare("select pageviews from {$wpdb->prefix}cmplz_statistics where ID = %s", $visitor_id)));
                $pageviews++;
                $args['pageviews'] = $pageviews;

                $success = $wpdb->update($wpdb->prefix . 'cmplz_statistics',
                    $args,
                    array('ID' => $visitor_id)
                );

                //check if any rows were affected. If not, this entry might have been deleted.
                if ($success === 0) {
                    $visitor_is_registered = false;
                }

            } else {
				$visitor_is_registered = false;
            }

            if ( !$visitor_is_registered ) {
                $wpdb->insert($wpdb->prefix . 'cmplz_statistics', $args );
                $visitor_id = $wpdb->insert_id;
				$this->setcookie($visitor_id);
            }
        }

        /**
         * Get the best performing cookiebanner
         * @return bool
         */

        public function best_performing_cookiebanner()
        {
			$banners = cmplz_get_cookiebanners();
            $best_performer_percentage = 0;
            $best_performer = false;
            foreach ($banners as $banner) {
                $banner = new CMPLZ_COOKIEBANNER($banner->ID);
                $p = $banner->conversion_percentage('all');

                if ($p > $best_performer_percentage) {
                    $best_performer_percentage = $p;
                    $best_performer = $banner->id;
                }

            }

            return $best_performer;
        }

        /**
         * Get the total number of seconds still left in this ab tracking test
         * @return int
         * @since 2.0.0
         *
         */

        public function seconds_left_ab_tracking()
        {
            if ($this->best_performer_enabled()) {
                return 0;
            }

            $start_date = get_option('cmplz_tracking_ab_started');

            $testing_duration = apply_filters('cmplz_ab_testing_duration', cmplz_get_value('a_b_testing_duration')) * DAY_IN_SECONDS;
            $now = time();
            $time_since = $now - $start_date;

            $seconds_left = $testing_duration - $time_since;
            if ($seconds_left <0 ) $seconds_left = 0;

            return $seconds_left;
        }

        /**
         * Get the time left in the current A/B test, human readable format.
         * @since 2.0
         * @return array|int
         */

        public function time_left_ab_tracking()
        {
            if (get_option('cmplz_enabled_best_performer')) {
                return 0;
            }

            $start_date = get_option('cmplz_tracking_ab_started');


            $testing_duration = apply_filters('cmplz_ab_testing_duration', cmplz_get_value('a_b_testing_duration'));
            $now = time();

            $current_duration_days = round(($now - $start_date) / DAY_IN_SECONDS, 2);
            $days_left = $testing_duration - $current_duration_days;

            $days = round($days_left - 0.499);

            $hours = (($days_left - $days) * DAY_IN_SECONDS) / HOUR_IN_SECONDS;
            $time_left = array('days' => $days, "hours" => $hours);

            return $time_left;
        }

        /**
         * Check if the best performer is enabled
         *
         * @since 2.0
         * @return bool $enabled;
         */

        public function best_performer_enabled()
        {
            return get_option('cmplz_enabled_best_performer');
        }


        /**
         * Notices about statistics
         */

        public function notices()
        {
            //create a dataset which includes each variation, and all labels stacked.
            if (!COMPLIANZ::$cookie_admin->site_needs_cookie_warning()) return;

            if ( !cmplz_ab_testing_enabled() ) return;
            ?>

            <p>
                <?php
                $notice = __('The conversion graph shows the ratio for the different choices users have. When a user has made a choice, this will be counted as either a converted user, or a not converted. If no choice is made, the user will be listed in the "No choice" category.', 'complianz-gdpr');
                $notice .= '&nbsp;';
                if (cmplz_geoip_enabled()) {
                    $enabled_regions = implode(", ", cmplz_get_regions());
                    $notice .= cmplz_sprintf(__('As you have enabled geoip, there are several regions in which a banner is shown, in different ways. In regions apart from %s no banner is shown at all.', 'complianz-gdpr'), $enabled_regions);
                }

                cmplz_notice($notice);
                ?>
            </p>
            <p>
                <?php
                //it's no use showing this message when only one banner is available.
				$banners = cmplz_get_cookiebanners();
                if (count($banners)>1 && !$this->best_performer_enabled()) {
                    if ($this->seconds_left_ab_tracking() > 0) {
                        $time = $this->time_left_ab_tracking();

                        $days_string = cmplz_sprintf(_n('%s day', '%s days', $time['days'], 'complianz-gdpr'), number_format_i18n($time['days']));
                        $hours_string = cmplz_sprintf(_n('%s hour', '%s hours', $time['hours'], 'complianz-gdpr'), number_format_i18n($time['hours']));

                        cmplz_notice( cmplz_sprintf(__('A/B tracking is still in progress, in approximately %s and %s the application will automatically enable the best performing banner.', 'complianz-gdpr'), $days_string, $hours_string), 'warning');
                    } else {
                        cmplz_notice(__('The A/B tracking period has ended, the best performer will be enabled on the next scheduled check.', 'complianz-gdpr'));
                    }
                }

                ?>
            </p>
            <?php
        }

        /**
         * enqueue the scripts for the backend
         * @param $hook
         */

        public function enqueue_scripts($hook)
        {
            if ((strpos($hook, 'complianz') === FALSE) && strpos($hook, 'cmplz') === FALSE) return;
            if (
					isset($_GET['id']) ||
					( isset($_GET['action']) && $_GET['action']==='new')
			) return;
			/*Graphs*/
			wp_enqueue_script('chartjs', cmplz_url . 'assets/chartjs/chart.min.js', array(), cmplz_version, false);
			wp_enqueue_style('chartjs', cmplz_url . 'assets/chartjs/chart.min.css', array(), cmplz_version );
			$min = (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) ? '' : '.min';
			wp_enqueue_script('cmplz_statistics', cmplz_url . "pro/assets/js/statistics$min.js", array('jquery'), cmplz_version, true);
			wp_localize_script(
					'cmplz_statistics',
					'cmplz_statistics',
					array(
							'admin_url' => admin_url('admin-ajax.php'),
							'translations' => array(
									'delete' => __('Delete', 'complianz-gdpr'),
									'loading...' => __('loading...', 'complianz-gdpr'),
									'category' => __('Category', 'complianz-gdpr'),
									'conversions' => __('Conversions', 'complianz-gdpr'),
							)
					)
			);
        }

		/**
		 * Get graph selector
		 */

		/**
		 * Get graph selector
		 */
		public function get_graph_selector(){
			//if a/b testing is disabled, hide the graph
			if (!cmplz_ab_testing_enabled() && cmplz_get_value( 'records_of_consent') !== 'yes' ) return;

			//if no cookie warning is needed, no a/b testing is needed either
			if (!COMPLIANZ::$cookie_admin->site_needs_cookie_warning()) return;

			$consenttypes['all'] = "all";
			$consenttypes = $consenttypes + cmplz_get_used_consenttypes();
			ob_start();
			?>
			<select name="cmplz_consenttype">
				<?php foreach ($consenttypes as $consenttype) { ?>
					<option value="<?php echo esc_html($consenttype) ?>"><?php echo esc_html(cmplz_consenttype_nicename($consenttype)) ?></option>
				<?php } ?>
			</select>
			<?php
			return ob_get_clean();
		}

        /**
         * Show statistics in a graph
         */

        public function graph()
        {
            //if no cookie warning is needed, no a/b testing is needed either
            if (!COMPLIANZ::$cookie_admin->site_needs_cookie_warning()) return;

			if (!cmplz_ab_testing_enabled() && cmplz_get_value( 'records_of_consent') !== 'yes' ) return;
            ?>
            <table class="cmplz-graph-container">
                    <tr class="row">
                        <th>&nbsp;</th>
                        <td class="column">
                            <div>
								<?php echo $this->get_graph_selector();?>
                            </div>
							<div class='cmplz-graph-container'>
								<canvas class="cmplz-graph" ></canvas>
							</div>
						</td>
					</tr>
            </table>
            <?php
		}

		/**
		 * Get color for a graph
		 * @param int     $index
		 * @param string $type
		 *
		 * @return string
		 */

        public function get_graph_color( $index , $type = 'default' ) {
        	$o = $type = 'background' ? '1' : '1';
        	switch ($index) {
				case 0:
					return "rgba(255, 99, 132, $o)";
				case 1:
					return "rgba(255, 159, 64, $o)";
				case 2:
					return "rgba(255, 205, 86, $o)";
				case 3:
					return "rgba(75, 192, 192, $o)";
				case 4:
					return "rgba(54, 162, 235, $o)";
				case 5:
					return "rgba(153, 102, 255, $o)";
				case 6:
					return "rgba(201, 203, 207, $o)";
				default:
					return "rgba(238, 126, 35, $o)";

			}
		}

		/**
		 * Get graph data
		 * @return array
		 */
		/**
		 * Get graph data
		 * @return array
		 */
		public function get_graph(){
			$error = false;
			if ( ! current_user_can( 'manage_options' ) ) {
				$error = true;
			}
			$data = array();
			if ( !isset($_GET['consenttype']) ) {
				$error = true;
			}

			if ( !$error ) {
				$consenttype = sanitize_title($_GET['consenttype']);
				$range = apply_filters('cmplz_ab_testing_duration', cmplz_get_value('a_b_testing_duration')) * DAY_IN_SECONDS;

				//for each day, counting back from "now" to the first day, get the date.
				$now = time();
				$start_time = $now - $range;

				//generate a dataset for each category
				$cookiebanners = cmplz_get_cookiebanners();
				$i=0;
				$ab_testing_enabled = cmplz_ab_testing_enabled();
				$data['labels'] = array();
				$category_keys = array();//we make sure the indexes of keys and labels are the same
				foreach ($cookiebanners as $cookiebanner ) {

					//when not ab testing, show only default banner.
					if ( !$ab_testing_enabled && !$cookiebanner->default ) continue;

					$cookiebanner = new CMPLZ_COOKIEBANNER( $cookiebanner->ID);
					$categories = $cookiebanner->get_available_categories(true);

					foreach ($categories as $key => $label ) {
						if (!in_array($label,  $data['labels'] )) {
							$data['labels'][] = $label;
							$category_keys[] = $key;
						}
					}

					$borderDash = array(0,0);
					$title = empty($cookiebanner->title) ? 'banner_'.$cookiebanner->position.'_'.$i : $cookiebanner->title;

					if (!$cookiebanner->default) {
						$borderDash = array(10,10);
					} else {
						$title .= " (".__("default", "complianz-gdpr").")";
					}

					//get hits grouped per timeslot. default day
					$hits = $this->get_consent_per_category($cookiebanner->id, $category_keys, $consenttype, $start_time );
					$data['datasets'][] = array(
							'data' => $hits,
							'backgroundColor' => $this->get_graph_color($i, 'background'),
							'borderColor' => $this->get_graph_color($i),
							'label' => $title,
							'fill' => 'false',
							'borderDash' => $borderDash,
					);
					$i++;
				}

			}

			if (isset($data['datasets'])) {
				//get highest hit count for max value
				$max = max(array_map('max',array_column( $data['datasets'], 'data' )));
				$data['max'] = $max > 5 ? $max : 5;
			} else {
				$data['datasets'][] = array(
						'data' => array(0),
						'backgroundColor' => $this->get_graph_color(0, 'background'),
						'borderColor' => $this->get_graph_color(0),
						'label' => __("No data for this selection", "complianz-gdpr"),
						'fill' => 'false',
				);
				$data['max'] = 5;
			}

			$return  = array(
					'success' => !$error,
					'message' => 'success',
					'data'    => $data,
					'title'    => __('A/B testing graph', "complianz-gdpr"),
			);
			echo json_encode( $return );
			die;
		}

		/**
		 * @param string $period
		 * @param int $start_time
		 *
		 * @return float
		 */

		public function get_nr_of_periods($period, $start_time ){
			$range_in_seconds = time() - $start_time;
			$period_in_seconds = constant(strtoupper($period).'_IN_SECONDS' );
			return ROUND($range_in_seconds/$period_in_seconds);
		}

		/**
		 * @param int $cookie_banner_id
		 * @param array $categories
		 * @param string $consenttype
		 * @param int $start_time
		 * @param int $range
		 *
		 * @return array
		 */

		public function get_consent_per_category( $cookie_banner_id, $categories, $consenttype, $start_time ) {
			global $wpdb;
			$consenttype_sql = '';
			if ($consenttype !== 'all' ) {
				$consenttype = in_array($consenttype, cmplz_get_used_consenttypes() ) ? $consenttype : 'optin';
				$consenttype_sql = $wpdb->prepare(" AND consenttype = %s", $consenttype);
			}

			$cookie_banner_id = intval($cookie_banner_id);
			$start_time = intval($start_time);
			$data = array();

			foreach ($categories as $category ) {
				$sql = "SELECT COUNT(*) as hit_count
					FROM {$wpdb->prefix}cmplz_statistics where $category=1 AND cookiebanner_id = $cookie_banner_id AND time>$start_time $consenttype_sql";

				$data[] = $wpdb->get_var($sql);
			}

			return $data;
		}

    } //class closure
}
