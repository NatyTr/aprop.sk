<?php

class WD_FM_MAILCHIMP_view {
  /**
   * Display.
   *
   * @param array $params
   */
	public function display( $params = array() ) {
		$mailchimp = $params['data'];
		$mcapi_lists = $params['mcapi_lists'];
		$correspondence_fields = $params['correspondence_fields'];
		$label_all = $params['label_all'];
		$filter_types = $params['filter_types'];
		$mch_address_fields = $params['address_fields'];
		$mch_address_required = $params['address_fields_required'];

		$mailchimp_ajax_url = $params['mailchimp_ajax_url'];

		$reg_select_option = array();
		$reg_select_option[0] = '<option value="">'. __('Select a field', WDFM()->prefix) .'</option>';
    foreach ( $label_all as $key => $label_each ) {
      $label_id_each = explode('#**id**#', $label_each);
      $label_order_each = explode('#**label**#', $label_id_each[1]);
      if ( !in_array($label_order_each[1], $filter_types) ) {
        $reg_select_option[++$key] = '<option value="' . $label_id_each[0] . '">' . htmlspecialchars( $label_order_each[0], ENT_QUOTES ) . '</option>';
      }
    }
		$reg_select_option_json = json_encode($reg_select_option);
		?>
		<div id="WD_FM_MAILCHIMP_fieldset" class="adminform fm_fieldset_deactive">
			<div class="wd-table">
				<div class="wd-table-col-100">
					<div class="wd-box-section">
						<div class="wd-box-content">
							<div class="wd-group">
								<label class="wd-label"><?php _e('Enable MailChimp Integration', WDFM()->prefix); ?></label>
								<input class="wd-label" type="radio" name="use_mailchimp" id="use_mailchimpyes" value="1" onchange="mailchimp_onEnableChange();" <?php echo ($mailchimp->use_mailchimp) ? "checked" : ""; ?> /><label class="wd-label-radio" for="use_mailchimpyes"><?php _e('Yes', WDFM()->prefix); ?></label>
								<input class="wd-label" type="radio" name="use_mailchimp" id="use_mailchimpno" value="0" onchange="mailchimp_onEnableChange();" <?php echo (!$mailchimp->use_mailchimp) ? "checked" : ""; ?> /><label class="wd-label-radio" for="use_mailchimpno"><?php _e('No', WDFM()->prefix); ?></label>
								<p class="description"><?php _e('Add contacts to MailChimp subscription lists from submitted forms.', WDFM()->prefix); ?></p>
							</div>	
						</div>
					</div>
				</div>
			</div>
			<div id="mailchimp_fieldset_options" class="admintable <?php echo (!$mailchimp->use_mailchimp) ? 'hidden' : ''; ?>">
				<div class="wd-table">
					<div class="wd-table-col-100">
						<div class="wd-box-section">
							<div class="wd-box-content">
								<div class="wd-group">
									<label class="wd-label"><?php _e('MailChimp API Key', WDFM()->prefix); ?></label>
									<input class="fm-validate" data-type="required" data-callback="fm_mailchimp_validation" data-tab-id="WD_FM_MAILCHIMP" data-content-id="WD_FM_MAILCHIMP_fieldset" type="text" id="mailchimp_apikey" name="mailchimp_apikey" value="<?php echo $mailchimp->mailchimp_apikey ?>" />
									<p class="description"><?php _e('As you sign in to MailChimp, click on your username from the top right corner, then navigate to Account page. Afterwards, open Extras > API Keys section. To generate an API key, simply click on Create a Key button.', WDFM()->prefix); ?></p>
                  <?php
                  if ( isset($mcapi_lists['status']) && $mcapi_lists['status'] == 'error' && isset($mcapi_lists['message']) ) {
                    ?>
                  <p class="description fm-validate-description"><?php echo $mcapi_lists['message']; ?></p>
                    <?php
                  }
                  ?>
									<p>
										<a href="https://admin.mailchimp.com/account/api" target="_blank"><?php _e('Get MailChimp API key and copy it to this input box.', WDFM()->prefix); ?></a>
										<button type="button" class="button button-primary mailchimp_confirm" onclick="mailchimp_int_confirm();"><?php _e('Confirm', WDFM()->prefix); ?></button>
									</p>
								</div>
								<div class="mailchimp-params" style="<?php if($mailchimp->use_mailchimp != 1 ||  $mailchimp->mailchimp_apikey=='') echo 'display:none'?>">
									<div class="wd-group">
										<label class="wd-label" for="mailchimp_action" title=""><?php _e('Action', WDFM()->prefix); ?></label>
										<select id="mailchimp_action" name="mailchimp_action">
											<option value="1" <?php if($mailchimp->mailchimp_action == 1 ) echo 'selected="selected"' ?>><?php _e('Subscribe', WDFM()->prefix); ?></option>
											<option value="0" <?php if($mailchimp->mailchimp_action == 0 ) echo 'selected="selected"' ?>><?php _e('Unsubscribe', WDFM()->prefix); ?></option>
										</select>
										<p class="description"><?php _e('Select the action, which will be completed as users submit your form.', WDFM()->prefix); ?></p>
									</div>
									<div class="wd-group">
										<label class="wd-label" for="mailchimp_email_type"><?php _e('Email Type', WDFM()->prefix); ?></label>
										<select id="mailchimp_email_type" name="mailchimp_email_type">
											<option value="html" <?php if($mailchimp->mailchimp_email_type == "html" ) echo 'selected="selected"' ?>><?php _e('HTML', WDFM()->prefix); ?></option>
											<option value="text" <?php if($mailchimp->mailchimp_email_type == "text" ) echo 'selected="selected"' ?>><?php _e('Text', WDFM()->prefix); ?></option>
										</select>
										<p class="description"><?php _e('Select the type of the content to be used in the email from your MailChimp form.', WDFM()->prefix); ?></p>
									</div>
									<div class="wd-group">
										<label class="wd-label" for="mailchimp_listid"><?php _e('MailChimp List', WDFM()->prefix); ?></label>
										<select id="mailchimp_listid"
											name="mailchimp_listid"
											class="fm-validate"
											data-type="required"
											data-callback="fm_mailchimp_validation"
											data-tab-id="WD_FM_MAILCHIMP"
											data-content-id="WD_FM_MAILCHIMP_fieldset">
										  <?php
										  if ( !empty($mcapi_lists) ) {
											  ?>
												<option value=""><?php _e('Select a List', WDFM()->prefix); ?></option>
											<?php
											foreach ($mcapi_lists as $list) {
											  ?>
												<option value="<?php echo $list->id ?>" <?php selected($mailchimp->mailchimp_listid, $list->id, TRUE); ?>><?php echo $list->name; ?></option>
											  <?php
											}
										  }
										  ?>
										</select>
										<p class="description"><?php _e('Select the list, which the users will be subscribed to (or unsubscribed from) by submitting your form.', WDFM()->prefix); ?></p>
									</div>
								    <div class="wd-group">
										<span class="wd-blue-msg"><?php _e('Configure the fields on your form to correspond your MailChimp list fields.', WDFM()->prefix); ?></span>
										<p class="description"><?php _e('For instance, you can select Email type field of your form to use as Email Address field on your MailChimp list.', WDFM()->prefix); ?></p>
										<span class="fm_mailchimp-list-loading spinner"></span>
								    </div>
									<div class="merge-vars">
									  <?php
										if ( !empty($correspondence_fields) ) {
										  foreach ( $correspondence_fields as $field ) {
										  ?>
											<div class="wd-group">
											  <label class="wd-label" for="mch_<?php echo $field->tag; ?>">
                          <?php echo $field->name; ?>
                          <?php
                          if ( $field->tag == 'EMAIL' || $field->required ) {
                            ?>
                          <span class="required" style="vertical-align: top;"> *</span>
                            <?php
                          }
                          ?>
                        </label>
												<select tag="<?php echo $field->tag; ?>" type="<?php echo $field->type; ?>" id="mch_<?php echo $field->tag; ?>" class="mch_vars<?php echo ($field->tag == 'EMAIL' || $field->required) ? ' fm-validate' : ''; ?>"
												  <?php echo ($field->tag == 'EMAIL' || $field->required) ? '
													  data-type="required"
													  data-callback="fm_mailchimp_validation"
													  data-tab-id="WD_FM_MAILCHIMP"
													  data-content-id="WD_FM_MAILCHIMP_fieldset"' : ''; ?>>
												  <?php
												  foreach ( $reg_select_option as $option ) {
													echo $option;
												  }
												  ?>
												</select>
											</div>
											<?php
											if ( !empty($field->type) && $field->type == 'address' ) {
											  foreach ( $mch_address_fields as $key => $val ) {
												$key = strtoupper($key);
												?>
											  <div class="wd-group">
												<label class="wd-label" for="mch_<?php echo $key; ?>">
                          <?php echo $val; ?>
                          <?php
                          if ( in_array(strtolower($key), $mch_address_required) && $field->required ) {
                            ?>
                            <span class="required" style="vertical-align: top;"> *</span>
                            <?php
                          }
                          ?>
                        </label>
												<select tag="<?php echo $key; ?>" type="<?php echo $field->type; ?>" id="mch_<?php echo $key; ?>" class="mch_vars<?php echo ( in_array( strtolower($key), $mch_address_required ) ) ? ' fm-validate' : ''; ?>"
												  <?php echo ( in_array(strtolower($key), $mch_address_required) && $field->required ) ? '
													data-type="required"
													data-callback="fm_mailchimp_validation"
													data-tab-id="WD_FM_MAILCHIMP"
													data-content-id="WD_FM_MAILCHIMP_fieldset"' : ''; ?>>
												  <?php
												  foreach ( $reg_select_option as $option ) {
													  echo $option;
												  }
												  ?>
												</select>
											  </div>
											  <?php
											  }
											}
										  }
										}
										?>
									</div>															
								</div>
							</div>
						</div>
					</div>
				</div>				
			</div>				
			<input type="hidden" id="mailchimp_mergevars" name="mailchimp_mergevars" value="<?php echo $mailchimp->mailchimp_mergevars; ?>" />
			<input type="hidden" id="mailchimp_ajax_url" name="mailchimp_ajax_url" value="<?php echo $mailchimp_ajax_url; ?>" />
		</div>
		<script>			
			var fm_reg_select_option = '<?php echo addslashes( $reg_select_option_json ); ?>';
			var fm_mch_address_fields = '<?php echo json_encode($mch_address_fields); ?>';
			var fm_mch_address_required = '<?php echo json_encode($mch_address_required); ?>';
		</script>
		<?php
	}
}
