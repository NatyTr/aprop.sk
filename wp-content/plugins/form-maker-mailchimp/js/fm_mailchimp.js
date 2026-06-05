jQuery(document).on("fm_tab_loaded", function () {
  mailchimp_onEnableChange();
  selected_correspondence_fields();
  jQuery('#mailchimp_listid').on('change', function (e) {
    if (jQuery(this).attr('value') != 0) {
      get_correspondence_fields();
    }
    else {
      jQuery('.merge-vars').html('');
    }
  });
  // Show params div if api key confirmed.
  jQuery('input[ type=radio ][ name=use_mailchimp ]').on('change', function () {
    if (jQuery('#use_mailchimpyes').is(':checked') && jQuery('#mailchimp_listid option').length > 0) {
      jQuery('.mailchimp-params').css('display', 'block');
    }
  });
});
jQuery('#manage_form').on('submit', function (event) {
  if (jQuery('#use_mailchimpyes').is(':checked')) {
    if (jQuery('.mch_vars').length) {
      mergevars = '';
      jQuery('.mch_vars').each(function () {
        mergevars += this.getAttribute('tag') + '***tag***' + this.getAttribute('type') + '***type***' + this.value + '***var***';
      });
      jQuery('#mailchimp_mergevars').val(mergevars);
    }
  }
});

function mailchimp_onEnableChange() {
  var display_options = false;
  var radioEnable = jQuery("#WD_FM_MAILCHIMP_fieldset input[name=use_mailchimp]:checked").val();
  if (radioEnable == 1) {
    display_options = true;
  }
  var mailchimp_options = jQuery("#WD_FM_MAILCHIMP_fieldset #mailchimp_fieldset_options");
  display_options == true ? mailchimp_options.removeClass("hidden") : mailchimp_options.addClass("hidden");
}

function mailchimp_int_confirm() {
  if ( jQuery('#use_mailchimpyes').is(":checked") ) {
    var mailchimp_apikey = jQuery('#mailchimp_apikey').val();
    if ( mailchimp_apikey ) {
      jQuery.ajax({
        url: jQuery("#mailchimp_ajax_url").val() + '&addon_task=connecting&mailchimp_apikey=' + mailchimp_apikey,
        dataType: "json",
        beforeSend: function () {
          window.parent.fm_loading_show();
        },
        success: function (response) {
          if (response.length) {
            jQuery('.mailchimp-params').show();
            jQuery('#mailchimp_listid').html('<option value="" selected="selected">Select a List</option>');
            jQuery('.merge-vars').html('');
            for (var i = 0; i < response.length; i++) {
              jQuery('#mailchimp_listid').append('<option  value="' + response[i]["id"] + '">' + response[i]["name"] + '</option>');
            }
            jQuery(".fm-validate").parent().find(".fm-validate-description").remove();
            jQuery(".fm-validate").removeClass("fm-validate-field");
          }
          else {
            mailchimp_msg(form_maker_manage.not_valid_value);
            jQuery(".mailchimp-params").hide();
          }
          window.parent.fm_loading_hide();
        }
      });
    }
    else {
      mailchimp_msg(form_maker_manage.required_field);
    }
  }
}

function mailchimp_msg(message) {
  jQuery("#mailchimp_apikey").parent().find(".fm-validate-description").remove();
  message = "<p class='description fm-validate-description'>" + message + "</p>";
  var description_container = jQuery("#mailchimp_apikey").parent().find(".description");
  if ( description_container.length ) {
    /* Show error message before description, if description container exist.*/
    description_container.before(message);
  }
  else {
    jQuery("#mailchimp_apikey").parent().append(message);
  }

  jQuery("#mailchimp_apikey").addClass("fm-validate-field");
  jQuery('html, body').animate({
    scrollTop: jQuery("#mailchimp_apikey").offset().top - 200
  }, 500);
}

function selected_correspondence_fields() {
  var mailchimp_mergevars = jQuery('#mailchimp_mergevars').val();
  if ( mailchimp_mergevars ) {
    var selected_fields = {};
    var fields_name = mailchimp_mergevars.split('***var***');
    for ( var i = 0; i < fields_name.length; i++ ) {
      var tag = fields_name[i].split('***tag***');
      if ( tag[0] ) {
        var type = tag[1].split('***type***');
        selected_fields[tag[0]] = type[1];
        var options = jQuery('#mch_' + tag[0] + ' option');
        jQuery.each(options, function () {
          if (jQuery(this).val() == type[1]) {
            jQuery(this).attr("selected", "selected");
          }
        });
      }
    }

    return selected_fields;
  }
}

/**
 * Get merge fields.
 */
function get_correspondence_fields() {
  var listid = jQuery('#mailchimp_listid option:selected').val();
  var mailchimp_list_loading = jQuery('#mailchimp_fieldset_options .fm_mailchimp-list-loading');
  mailchimp_list_loading.addClass('is-active');
  var mch_address_fields = jQuery.parseJSON(fm_mch_address_fields);
  var mch_address_required = jQuery.parseJSON(fm_mch_address_required);
  jQuery.ajax({
    url: jQuery("#mailchimp_ajax_url").val() + '&addon_task=merge_vars&api_key=' + jQuery("#mailchimp_apikey").val() + '&listid=' + listid,
    dataType: "json",
    success: function (response) {
      jQuery(".merge-vars").html("");
      for (var i = 0; i < response.length; i++) {
        var html = '';
        var tag_class = '';
        var tag_attributes = '';
        if ( response[i]['tag'] == 'EMAIL' || response[i]['required'] ) {
          tag_class = ' fm-validate';
          tag_attributes = 'data-type="required" data-callback="fm_mailchimp_validation" data-tab-id="WD_FM_MAILCHIMP" data-content-id="WD_FM_MAILCHIMP_fieldset"';
        }
        html += '<div class="wd-group">';
        html += '<label class="wd-label" for="mch_' + response[i]['tag'] + '">' + response[i]['name'];
        if ( response[i]['tag'] == 'EMAIL' || response[i]['required'] ) {
          html += '<span class="required" style="vertical-align: top;"> *</span>';
        }
        html += '</label>';
        html += '<select tag="' + response[i]['tag'] + '" type="' + response[i]['type'] + '" id="mch_' + response[i]['tag'] + '" class="mch_vars' + tag_class +'" "' + tag_attributes + '">';
					for (var o = 0; o < fm_reg_select_option.length; o++) {
					  html += fm_reg_select_option[o];
					}
        html += '</select>';
        html += '</div>';
        if ( response[i]['type'] == 'address' ) {
          jQuery.each( mch_address_fields, function( key, val ) {
            var tag_class = '';
            var tag_attributes = '';
            if ( jQuery.inArray(key, mch_address_required) !== -1 && response[i]['required'] ) {
              tag_class = ' fm-validate';
              tag_attributes = 'data-type="required" data-callback="fm_mailchimp_validation" data-tab-id="WD_FM_MAILCHIMP" data-content-id="WD_FM_MAILCHIMP_fieldset"';
            }
            var mch_key = 'mch_' + key.toUpperCase();
            html += '<div class="wd-group">';
            html += '<label class="wd-label" for="' + mch_key + '">' + val;
            if ( jQuery.inArray(key, mch_address_required) !== -1 && response[i]['required'] ) {
              html += '<span class="required" style="vertical-align: top;"> *</span>';
            }
            html += '</label>';
            html += '<select tag="' +  key.toUpperCase() + '" type="' + response[i]['type'] + '" id="' + mch_key + '" class="mch_vars' + tag_class + '" "' + tag_attributes + '">';
                  for (var o = 0; o < fm_reg_select_option.length; o++) {
                    html += fm_reg_select_option[o];
                  }
            html += '</select>';
            html += '</div>';
          });
        }
        jQuery(".merge-vars").append(html);
      }
      mailchimp_list_loading.removeClass('is-active');
	  if ( jQuery.isFunction(fm_remove_validate_error_message()) ) {
		  fm_remove_validate_error_message();
	  }
    }
  });
}

/* Check validation on form options save in form maker plugin.*/
function fm_mailchimp_validation(value, obj) {
  if (jQuery("#use_mailchimpyes").is(':checked')
    && value === "") {
    return false;
  }
  return true;
}