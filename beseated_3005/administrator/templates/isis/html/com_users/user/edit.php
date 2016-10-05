<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('behavior.formvalidator');
JHtml::_('formbehavior.chosen', 'select');

JFactory::getDocument()->addScriptDeclaration("
	Joomla.submitbutton = function(task)
	{
		if (task == 'user.cancel' || document.formvalidator.isValid(document.getElementById('user-form')))
		{
			Joomla.submitform(task, document.getElementById('user-form'));
		}
	};

	Joomla.twoFactorMethodChange = function(e)
	{
		var selectedPane = 'com_users_twofactor_' + jQuery('#jform_twofactor_method').val();

		jQuery.each(jQuery('#com_users_twofactor_forms_container>div'), function(i, el) {
			if (el.id != selectedPane)
			{
				jQuery('#' + el.id).hide(0);
			}
			else
			{
				jQuery('#' + el.id).show(0);
			}
		});
	};
");

// Get the form fieldsets.
$fieldsets = $this->form->getFieldsets();
?>
<script type="text/javascript">
jQuery(function(){
jQuery('#jform_email').keyup(function() {
    var value = jQuery('#jform_email').val();
    jQuery('#jform_username').val(value);
 });
});
</script>
<script type="text/javascript">
jQuery(function()
{
    jQuery("#groups input[name='jform[groups][]']").click(function()
    { 
       	var selected = jQuery('input[id^=1group_]:checked').val();
       	//alert("Radio Change : " + selected);
       	jQuery('#jform_city').attr("aria-invalid","true");
		jQuery('#jform_city').attr("aria-required","true");
		jQuery('#jform_city').attr("required","true");
		jQuery('#jform_city').removeAttr('class');
		jQuery('jform_city').addClass(' required invalid');
		if(selected == 11 || selected == 12 || selected == 13 || selected == 10)
		{
			jQuery('#jform_name-lbl').html(' Company Name<span class="star"> *</span>');

			jQuery('#group_mobile').hide();
			jQuery('#group_birthdate').hide();
			//jQuery('#group_city').hide();

			jQuery('#jform_mobile').val();
			jQuery('#jform_birthdate').val();
			//jQuery('#jform_city').val();

			if(selected == 11){
				jQuery('#group_is_day_club').show();
			}else{
				jQuery('#group_is_day_club').hide();
			}

			jQuery('#jform_mobile').attr("aria-invalid","false");
			jQuery('#jform_mobile').attr("aria-required","false");
			jQuery('#jform_mobile').removeAttr('required');
			jQuery('#jform_mobile').removeAttr('class');

			jQuery('#jform_birthdate').attr("aria-invalid","false");
			jQuery('#jform_birthdate').attr("aria-required","false");
			jQuery('#jform_birthdate').removeAttr('required');
			jQuery('#jform_birthdate').removeAttr('class');

			jQuery('#jform_city').attr("aria-invalid","false");
			jQuery('#jform_city').attr("aria-required","false");
			jQuery('#jform_city').removeAttr('required');
			jQuery('#jform_city').removeAttr('class');
		}
		else if(selected == 14) // Beseated Guest
		{
			jQuery('#jform_name-lbl').html(' Full Name<span class="star"> *</span>');

			jQuery('#group_is_day_club').hide();

			jQuery('#group_mobile').show();
			jQuery('#jform_mobile').attr("aria-invalid","true");
			jQuery('#jform_mobile').attr("aria-required","true");
			jQuery('#jform_mobile').attr("required","true");
			jQuery('#jform_mobile').removeAttr('class');
			jQuery('#jform_mobile').addClass(' required invalid');

			jQuery('#group_birthdate').show();
			jQuery('#jform_birthdate').attr("aria-invalid","true");
			jQuery('#jform_birthdate').attr("aria-required","true");
			jQuery('#jform_birthdate').attr("required","true");
			jQuery('#jform_birthdate').removeAttr('class');
			jQuery('#jform_birthdate').addClass(' required invalid');

			jQuery('#group_city').show();
			jQuery('#jform_city').attr("aria-invalid","true");
			jQuery('#jform_city').attr("aria-required","true");
			jQuery('#jform_city').attr("required","true");
			jQuery('#jform_city').removeAttr('class');
			jQuery('#jform_city').addClass(' required invalid');
		}
		else
		{
			jQuery('#jform_name-lbl').html(' Name<span class="star"> *</span>');
			jQuery('#group_mobile').hide();
			jQuery('#group_birthdate').hide();
			jQuery('#group_city').hide();
			jQuery('#group_is_day_club').hide();

			jQuery('#jform_mobile').val();
			jQuery('#jform_birthdate').val();
			jQuery('#jform_city').val();

			jQuery('#jform_mobile').attr("aria-invalid","false");
			jQuery('#jform_mobile').attr("aria-required","false");
			jQuery('#jform_mobile').removeAttr('required');
			jQuery('#jform_mobile').removeAttr('class');

			jQuery('#jform_birthdate').attr("aria-invalid","false");
			jQuery('#jform_birthdate').attr("aria-required","false");
			jQuery('#jform_birthdate').removeAttr('required');
			jQuery('#jform_birthdate').removeAttr('class');

			jQuery('#jform_city').attr("aria-invalid","false");
			jQuery('#jform_city').attr("aria-required","false");
			jQuery('#jform_city').removeAttr('required');
			jQuery('#jform_city').removeAttr('class');
		}
    });
});
</script>
<script type="text/javascript">
jQuery( document ).ready(function() 
{
	//$("p:first").replaceWith("Hello world!");
	//jQuery("#1group_1").prop("type", "radio");
	//jQuery("#1group_1").prop("type", "radio");
	//
	jQuery('#user-form').find('input:checkbox').attr({type:"radio"});

   	var selected = jQuery('input[id^=1group_]:checked').val();

	if(selected == 11 || selected == 12 || selected == 13 || selected == 10)
	{
		jQuery('#jform_name-lbl').html(' Company Name<span class="star"> *</span>');

		jQuery('#group_mobile').hide();
		jQuery('#group_birthdate').hide();

		if(selected == 11){
			jQuery('#group_is_day_club').show();
		}else{
			jQuery('#group_is_day_club').hide();
		}

		jQuery('#jform_mobile').val();
		jQuery('#jform_birthdate').val();

		jQuery('#jform_mobile').attr("aria-invalid","false");
		jQuery('#jform_mobile').attr("aria-required","false");
		jQuery('#jform_mobile').removeAttr('required');
		jQuery('#jform_mobile').removeAttr('class');

		jQuery('#jform_birthdate').attr("aria-invalid","false");
		jQuery('#jform_birthdate').attr("aria-required","false");
		jQuery('#jform_birthdate').removeAttr('required');
		jQuery('#jform_birthdate').removeAttr('class');

		//alert(" In Default case : " + selected);

		/*jQuery('#jform_city').attr("aria-invalid","false");
		jQuery('#jform_city').attr("aria-required","false");
		jQuery('#jform_city').removeAttr('required');
		jQuery('#jform_city').removeAttr('class');*/
	}
	else if(selected == 14) // Beseated Guest
	{
		jQuery('#jform_name-lbl').html(' Full Name<span class="star"> *</span>');

		jQuery('#group_is_day_club').hide();

		jQuery('#group_mobile').show();
		jQuery('#jform_mobile').attr("aria-invalid","true");
		jQuery('#jform_mobile').attr("aria-required","true");
		jQuery('#jform_mobile').attr("required","true");
		jQuery('#jform_mobile').removeAttr('class');
		jQuery('#jform_mobile').addClass(' required invalid');

		jQuery('#group_birthdate').show();
		jQuery('#jform_birthdate').attr("aria-invalid","true");
		jQuery('#jform_birthdate').attr("aria-required","true");
		jQuery('#jform_birthdate').attr("required","true");
		jQuery('#jform_birthdate').removeAttr('class');
		jQuery('#jform_birthdate').addClass(' required invalid');

		jQuery('#group_city').show();
		jQuery('#jform_city').attr("aria-invalid","true");
		jQuery('#jform_city').attr("aria-required","true");
		jQuery('#jform_city').attr("required","true");
		jQuery('#jform_city').removeAttr('class');
		jQuery('#jform_city').addClass(' required invalid');
	}
	else
	{
		jQuery('#jform_name-lbl').html(' Name<span class="star"> *</span>');
		jQuery('#group_mobile').hide();
		jQuery('#group_birthdate').hide();
		jQuery('#group_city').hide();
		jQuery('#group_is_day_club').hide();

		jQuery('#jform_mobile').val();
		jQuery('#jform_birthdate').val();
		jQuery('#jform_city').val();

		jQuery('#jform_mobile').attr("aria-invalid","false");
		jQuery('#jform_mobile').attr("aria-required","false");
		jQuery('#jform_mobile').removeAttr('required');
		jQuery('#jform_mobile').removeAttr('class');

		jQuery('#jform_birthdate').attr("aria-invalid","false");
		jQuery('#jform_birthdate').attr("aria-required","false");
		jQuery('#jform_birthdate').removeAttr('required');
		jQuery('#jform_birthdate').removeAttr('class');

		jQuery('#jform_city').attr("aria-invalid","false");
		jQuery('#jform_city').attr("aria-required","false");
		jQuery('#jform_city').removeAttr('required');
		jQuery('#jform_city').removeAttr('class');
	}
});
</script>


<script src="http://maps.googleapis.com/maps/api/js?sensor=true&amp;libraries=places&amp;key=AIzaSyAKf4wfPXxaajH5eB3mH4Bsc7G-jSAhKKI" type="text/javascript"></script>
<script type="text/javascript">

	function initialize() {
		var input = document.getElementById('jform_city');
		var autocomplete = new google.maps.places.Autocomplete(input);
		google.maps.event.addListener(autocomplete, 'place_changed', function() {
            var place = autocomplete.getPlace();
            if (!place.geometry) {
              return;
            }

            console.log(place.address_components);

			city_name    = '';
			country_name = '';

            for (var i = 0; i < place.address_components.length; i++) {
				var addressType = place.address_components[i].types[0];
				if (addressType == 'locality') {
					city_name = place.address_components[i]['long_name'];
				}
				if (addressType == 'country') {
					country_name = place.address_components[i]['long_name'];
				}

				//alert(place.address_components[i]['long_name'] + " : Addrsss Type : " + addressType);

			}
			//alert(city_name);

			jQuery('#jform_only_city').val(city_name);
			//jQuery('#jform_only_country').val(country_name);
            jQuery('#jform_latitude').val(place.geometry.location.lat());
            jQuery('#jform_longitude').val(place.geometry.location.lng());
        });
    }
	google.maps.event.addDomListener(window, 'load', initialize);
</script>

<script type="text/javascript">
jQuery( document ).ready(function() {
    jQuery('#jform_mobile').attr("aria-invalid","true");
	jQuery('#jform_mobile').attr("aria-required","true");
	jQuery('#jform_mobile').attr("required","true");
	jQuery('#jform_mobile').removeAttr('class');
	jQuery('#jform_mobile').addClass(' required invalid');

	jQuery('#jform_birthdate').attr("aria-invalid","true");
	jQuery('#jform_birthdate').attr("aria-required","true");
	jQuery('#jform_birthdate').attr("required","true");
	jQuery('#jform_birthdate').removeAttr('class');
	jQuery('#jform_birthdate').addClass(' required invalid');

	jQuery('#jform_city').attr("aria-invalid","true");
	jQuery('#jform_city').attr("aria-required","true");
	jQuery('#jform_city').attr("required","true");
	jQuery('#jform_city').removeAttr('class');
	jQuery('#jform_city').addClass(' required invalid');

	var selected = jQuery('input[id^=1group_]:checked').val();
	// 14 Beseated Guest
	// 13 Chauffeur
	// 12 Protection
	// 11 Venue
	// 10 Yacht
	if(selected == 11 || selected == 12 || selected == 13 || selected == 10)
	{
		jQuery('#jform_name-lbl').html(' Company Name<span class="star"> *</span>');

		jQuery('#group_mobile').hide();
		jQuery('#group_birthdate').hide();
		jQuery('#group_city').show();

		if(selected == 11){
			jQuery('#group_is_day_club').show();
		}else{
			jQuery('#group_is_day_club').hide();
		}

		jQuery('#jform_mobile').val();
		jQuery('#jform_birthdate').val();
		jQuery('#jform_city').val();

		jQuery('#jform_mobile').attr("aria-invalid","false");
		jQuery('#jform_mobile').attr("aria-required","false");
		jQuery('#jform_mobile').removeAttr('required');
		jQuery('#jform_mobile').removeAttr('class');

		jQuery('#jform_birthdate').attr("aria-invalid","false");
		jQuery('#jform_birthdate').attr("aria-required","false");
		jQuery('#jform_birthdate').removeAttr('required');
		jQuery('#jform_birthdate').removeAttr('class');

		jQuery('#jform_city').attr("aria-invalid","false");
		jQuery('#jform_city').attr("aria-required","false");
		jQuery('#jform_city').removeAttr('required');
		jQuery('#jform_city').removeAttr('class');
	}
	else if(selected == 14) // Beseated Guest
	{
		jQuery('#jform_name-lbl').html(' Full Name<span class="star"> *</span>');

		jQuery('#group_mobile').show();
		jQuery('#jform_mobile').attr("aria-invalid","true");
		jQuery('#jform_mobile').attr("aria-required","true");
		jQuery('#jform_mobile').attr("required","true");
		jQuery('#jform_mobile').removeAttr('class');
		jQuery('#jform_mobile').addClass(' required invalid');

		jQuery('#group_is_day_club').hide();

		jQuery('#group_birthdate').show();
		jQuery('#jform_birthdate').attr("aria-invalid","true");
		jQuery('#jform_birthdate').attr("aria-required","true");
		jQuery('#jform_birthdate').attr("required","true");
		jQuery('#jform_birthdate').removeAttr('class');
		jQuery('#jform_birthdate').addClass(' required invalid');

		jQuery('#group_city').show();
		jQuery('#jform_city').attr("aria-invalid","true");
		jQuery('#jform_city').attr("aria-required","true");
		jQuery('#jform_city').attr("required","true");
		jQuery('#jform_city').removeAttr('class');
		jQuery('#jform_city').addClass(' required invalid');
	}
	else
	{
		jQuery('#jform_name-lbl').html(' Name<span class="star"> *</span>');
		jQuery('#group_mobile').hide();
		jQuery('#group_birthdate').hide();
		jQuery('#group_city').hide();

		jQuery('#jform_mobile').val();
		jQuery('#jform_birthdate').val();
		jQuery('#jform_city').val();

		jQuery('#group_is_day_club').hide();

		jQuery('#jform_mobile').attr("aria-invalid","false");
		jQuery('#jform_mobile').attr("aria-required","false");
		jQuery('#jform_mobile').removeAttr('required');
		jQuery('#jform_mobile').removeAttr('class');

		jQuery('#jform_birthdate').attr("aria-invalid","false");
		jQuery('#jform_birthdate').attr("aria-required","false");
		jQuery('#jform_birthdate').removeAttr('required');
		jQuery('#jform_birthdate').removeAttr('class');

		jQuery('#jform_city').attr("aria-invalid","false");
		jQuery('#jform_city').attr("aria-required","false");
		jQuery('#jform_city').removeAttr('required');
		jQuery('#jform_city').removeAttr('class');
	}
});
</script>

<form action="<?php echo JRoute::_('index.php?option=com_users&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="user-form" class="form-validate form-horizontal" enctype="multipart/form-data">

	<?php echo JLayoutHelper::render('joomla.edit.item_title', $this); ?>

	<fieldset>
		<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'groups')); ?>
			<?php if ($this->grouplist) : ?>
				<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'groups', JText::_('COM_USERS_ASSIGNED_GROUPS', true)); ?>
					<?php echo $this->loadTemplate('groups'); ?>
				<?php echo JHtml::_('bootstrap.endTab'); ?>
			<?php endif; ?>
			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'details', JText::_('COM_USERS_USER_ACCOUNT_DETAILS', true)); ?>
				<?php foreach ($this->form->getFieldset('user_details') as $field) : ?>
					<?php if($field->name == 'jform[mobile]'): ?>
						<div class="control-group" id="group_mobile">
					<?php elseif($field->name == 'jform[city]'): ?>
						<div class="control-group" id="group_city">
					<?php elseif($field->name == 'jform[birthdate]'): ?>
						<div class="control-group" id="group_birthdate">
					<?php elseif($field->name == 'jform[is_day_club]'): ?>
						<div class="control-group" id="group_is_day_club">
					<?php else: ?>
						<div class="control-group">
					<?php endif; ?>
						<div class="control-label">
							<?php echo $field->label; ?>
						</div>
						<div class="controls">
							<?php if ($field->fieldname == 'password') : ?>
								<?php // Disables autocomplete ?> <input type="text" style="display:none">
							<?php endif; ?>
							<?php echo $field->input; ?>
						</div>
					</div>
				<?php endforeach; ?>
			<?php echo JHtml::_('bootstrap.endTab'); ?>



			<?php
			foreach ($fieldsets as $fieldset) :
				if ($fieldset->name == 'user_details') :
					continue;
				endif;
			?>
			<?php echo JHtml::_('bootstrap.addTab', 'myTab', $fieldset->name, JText::_($fieldset->label, true)); ?>
				<?php foreach ($this->form->getFieldset($fieldset->name) as $field) : ?>
					<?php if ($field->hidden) : ?>
						<div class="control-group">
							<div class="controls">
								<?php echo $field->input; ?>
							</div>
						</div>
					<?php else: ?>
						<div class="control-group">
							<div class="control-label">
								<?php echo $field->label; ?>
							</div>
							<div class="controls">
								<?php echo $field->input; ?>
							</div>
						</div>
					<?php endif; ?>
				<?php endforeach; ?>
		<?php echo JHtml::_('bootstrap.endTab'); ?>
		<?php endforeach; ?>

		<?php if (!empty($this->tfaform) && $this->item->id): ?>
		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'twofactorauth', JText::_('COM_USERS_USER_TWO_FACTOR_AUTH', true)); ?>
		<div class="control-group">
			<div class="control-label">
				<label id="jform_twofactor_method-lbl" for="jform_twofactor_method" class="hasTooltip"
						title="<?php echo '<strong>' . JText::_('COM_USERS_USER_FIELD_TWOFACTOR_LABEL') . '</strong><br />' . JText::_('COM_USERS_USER_FIELD_TWOFACTOR_DESC'); ?>">
					<?php echo JText::_('COM_USERS_USER_FIELD_TWOFACTOR_LABEL'); ?>
				</label>
			</div>
			<div class="controls">
				<?php echo JHtml::_('select.genericlist', Usershelper::getTwoFactorMethods(), 'jform[twofactor][method]', array('onchange' => 'Joomla.twoFactorMethodChange()'), 'value', 'text', $this->otpConfig->method, 'jform_twofactor_method', false) ?>
			</div>
		</div>
		<div id="com_users_twofactor_forms_container">
			<?php foreach($this->tfaform as $form): ?>
			<?php $style = $form['method'] == $this->otpConfig->method ? 'display: block' : 'display: none'; ?>
			<div id="com_users_twofactor_<?php echo $form['method'] ?>" style="<?php echo $style; ?>">
				<?php echo $form['form'] ?>
			</div>
			<?php endforeach; ?>
		</div>

		<fieldset>
			<legend>
				<?php echo JText::_('COM_USERS_USER_OTEPS') ?>
			</legend>
			<div class="alert alert-info">
				<?php echo JText::_('COM_USERS_USER_OTEPS_DESC') ?>
			</div>
			<?php if (empty($this->otpConfig->otep)): ?>
			<div class="alert alert-warning">
				<?php echo JText::_('COM_USERS_USER_OTEPS_WAIT_DESC') ?>
			</div>
			<?php else: ?>
			<?php foreach ($this->otpConfig->otep as $otep): ?>
			<span class="span3">
				<?php echo substr($otep, 0, 4) ?>-<?php echo substr($otep, 4, 4) ?>-<?php echo substr($otep, 8, 4) ?>-<?php echo substr($otep, 12, 4) ?>
			</span>
			<?php endforeach; ?>
			<div class="clearfix"></div>
			<?php endif; ?>
		</fieldset>

		<?php echo JHtml::_('bootstrap.endTab'); ?>
		<?php endif; ?>

		<?php echo JHtml::_('bootstrap.endTabSet'); ?>
	</fieldset>

	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>