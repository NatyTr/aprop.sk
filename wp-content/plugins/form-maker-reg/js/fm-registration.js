jQuery(document).on("fm_tab_loaded", function () {
    onEnableChange("WD_FM_REG_fieldset", "reg_fieldset_options", jQuery("#WD_FM_REG_fieldset input[name=use_reg]:checked").val());
    jQuery("#show_other_params").change(function () {
      if (jQuery("#check_show_other_params").val() == 0) {
        jQuery("#div_user").css("display", "block");
        jQuery("#check_show_other_params").val(1);
      }
      else {
        jQuery("#div_user").css("display", "none");
        jQuery("#check_show_other_params").val(0);
      }
    });

    jQuery('html').click(function () {
      if (jQuery("#fieldlist").css('display') == "block") {
        jQuery("#fieldlist").hide();
      }
    });

    jQuery('#fieldlist').click(function (event) {
      event.stopPropagation();
    });

    jQuery("#div_user").load(fm_admin_url_profile + " #your-profile .form-table, #your-profile h2", function () {
      jQuery(".user-sessions-wrap.hide-if-no-js").parent().parent().prevAll('.form-table').prev('h2').remove();
      jQuery(".user-sessions-wrap.hide-if-no-js").parent().parent().prevAll('.form-table').remove();
      jQuery(".user-sessions-wrap.hide-if-no-js").parent().parent().prev('h2').remove();
      jQuery(".user-sessions-wrap.hide-if-no-js").parent().parent().remove();

      jQuery(".form-table select").each(function (index, element) {
        jQuery(element).after("<input autocomplete='off' type='text' id='" + jQuery(element).attr('id') + "' name='" + jQuery(element).attr('name') + "' />");
        jQuery(element).remove();
      });
      jQuery(".form-table label").each(function (index, element) {
        var label_text = jQuery(element).text();
        label_text = label_text.replace("(required)", "");
        jQuery(element).text(label_text);
      });

      jQuery(".form-table input").val('').each(function (i, input) {
        if (typeof fm_another_params[input.name] != undefined) {
          jQuery(input).val(fm_another_params[input.name]).removeAttr('required');
        }
      });

      if (jQuery('#div_user').is(':empty')) {
        jQuery("#div_show_other_params").css("display", "none");
        jQuery('#div_user').remove();
      }
      
      addPlaceholders();
    });

    jQuery('#manage_form').on('submit', function () {
      reg_conds = jQuery("#role").val() + "**conds**";
      jQuery(".conds").each(function () {
        reg_conds += jQuery(this).find(".cond_sel_field").val() + "****" + jQuery(this).find(".cond_if").val() + "****" + jQuery(this).find(".cond_role").val() + "**reg_conds**";
      });
      jQuery('input[name=role]').val(reg_conds);
      jQuery('#additional_data').val(JSON.stringify(jQuery('#WD_FM_REG_fieldset').serializeObject()));
    });
});

function addPlaceholders() {
  var data_params = '';
  if ( jQuery("#other_params").length > 0 && jQuery("#other_params").attr('data-params') != '') {
    data_params = JSON.parse(jQuery("#other_params").attr('data-params'));
  }
  var data_key = "";
  var data_val = "";
  jQuery("#div_user input").each( function() {
    data_key = jQuery(this).attr('id');
    data_val = data_params[data_key] ? data_params[data_key] : '';

    jQuery(this).val( data_val );
    var id = jQuery(this).attr('id');
    jQuery(this).parent().prepend('<div class="wd-group wd-has-placeholder"></div>');
    jQuery('<span class="dashicons dashicons-list-view" data-id="'+id+'" title="Add placeholder"></span>').prependTo(jQuery(this).parent().find('.wd-group.wd-has-placeholder'));
    jQuery(this).appendTo(jQuery(this).parent().find('.wd-group'));
  });
}

function delete_cond(x) {
  jQuery(x).parent().remove();
}

function add_role_cond() {
  jQuery("#role_params").append('<div class="conds">If <select class="cond_sel_field"><option value="">Select field</option>' + fm_reg_select + '</select> equals to <input type="text" class="cond_if"/> then set role <select class="cond_role">' + fm_reg_roles + '</select><span class="dashicons dashicons-trash" onclick="delete_cond(this)"></span></div>');
}

/* Check validation on form options save in form maker plugin.*/
function fm_register_validation_username(value, obj) {
  if ( jQuery("#use_regyes").is(':checked')
    && value === "" ) {
    return false;
  }
  return true;
}
