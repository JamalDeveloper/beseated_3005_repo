<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');

$app = JFactory::getApplication();
$Itemid = $app->input->get('Itemid',0,'int');
?>
<style type="text/css">
.login-field-label .star {
    display: none;
}
</style>
<div class="login<?php echo $this->pageclass_sfx?>">
	<?php if ($this->params->get('show_page_heading')) : ?>
	<div class="page-header">
		<h1>
			<?php echo $this->escape($this->params->get('page_heading')); ?>
		</h1>
	</div>
	<?php endif; ?>

	<?php if (($this->params->get('logindescription_show') == 1 && str_replace(' ', '', $this->params->get('login_description')) != '') || $this->params->get('login_image') != '') : ?>
	<div class="login-description">
	<?php endif; ?>

		<?php if ($this->params->get('logindescription_show') == 1) : ?>
			<?php echo $this->params->get('login_description'); ?>
		<?php endif; ?>

		<?php if (($this->params->get('login_image') != '')) :?>
			<img src="<?php echo $this->escape($this->params->get('login_image')); ?>" class="login-image" alt="<?php echo JTEXT::_('COM_USER_LOGIN_IMAGE_ALT')?>"/>
		<?php endif; ?>

	<?php if (($this->params->get('logindescription_show') == 1 && str_replace(' ', '', $this->params->get('login_description')) != '') || $this->params->get('login_image') != '') : ?>
	</div>
	<?php endif; ?>

	<form action="<?php echo JRoute::_('index.php?option=com_users&task=user.login'); ?>" method="post" class="form-validate form-horizontal well">

		<fieldset>
			<?php foreach ($this->form->getFieldset('credentials') as $field) : ?>
				<?php if (!$field->hidden) : ?>
					<div class="control-group">
						<div class="control-label login-field-label">
							<?php echo $field->label; ?>
						</div>
						<div class="controls">
							<?php echo $field->input; ?>
						</div>
					</div>
				<?php endif; ?>
			<?php endforeach; ?>

			<?php if ($this->tfa): ?>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getField('secretkey')->label; ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getField('secretkey')->input; ?>
					</div>
				</div>
			<?php endif; ?>

			<?php if (JPluginHelper::isEnabled('system', 'remember')) : ?>
			<div  class="control-group">
				<div class="control-label"><label><?php echo JText::_('COM_USERS_LOGIN_REMEMBER_ME') ?></label></div>
				<div class="controls"><input id="remember" type="checkbox" name="remember" class="inputbox remember-box" value="yes"/></div>
			</div>
			<?php endif; ?>

			<div class="control-group">
				<div class="controls">
					<button type="submit" class="btn btn-primary">
						<?php echo JText::_('JLOGIN'); ?>
					</button>
				</div>
			</div>

			<input type="hidden" name="return" value="<?php echo base64_encode($this->params->get('login_redirect_url', $this->form->getValue('return'))); ?>" />
			<?php echo JHtml::_('form.token'); ?>
		</fieldset>
	</form>
</div>
<div class="forg-lnk">
	<ul class="nav nav-tabs nav-stacked">
		<li>
			<?php /*<a href="<?php echo JRoute::_('index.php?option=com_bcted&view=forgotpassword&Itemid='.$Itemid); ?>">
			<?php echo JText::_('COM_USERS_LOGIN_RESET'); ?></a> */ ?>

			<a role="button" class="edit-ticket" data-target="#forgotPasswordModal" data-toggle="modal" href="<?php echo JRoute::_('index.php?option=com_beseated&view=forgotpassword&tmpl=component&Itemid='.$Itemid); ?>">
			<?php echo JText::_('COM_USERS_LOGIN_RESET'); ?></a>
		</li>
		<!--<li>
			<a href="<?php //echo JRoute::_('index.php?option=com_users&view=remind'); ?>">
			<?php //echo JText::_('COM_USERS_LOGIN_REMIND'); ?></a>
		</li>-->
		<?php
		$usersConfig = JComponentHelper::getParams('com_users');
		/*if ($usersConfig->get('allowUserRegistration')) : ?>
		<li>
			<a href="<?php echo JRoute::_('index.php?option=com_users&view=registration'); ?>">
				<?php echo JText::_('COM_USERS_LOGIN_REGISTER'); ?></a>
		</li>
		<?php endif;*/ ?>
		<!-- <a href="#" onclick="window.open('http://matiz.websitewelcome.com/~tasolglo/dev/bc-ted/index.php?option=com_fbconnct&task=login&format=raw','name','height=300,width=550');return false;" >Login with Facebook</a>-->
		<?php $fbReturnUrl = JRequest::getVar('return');?>
		<a href="#" onclick="window.open('index.php?option=com_fbconnct&task=login&format=raw&return=<?php echo $fbReturnUrl; ?>','name','height=300,width=550');return false;" >Login with Facebook</a>
	</ul>
</div>

<div id="forgotPasswordModal" class="modal hide fade modaldesign" tabindex="-1" role="dialog">
    <div class="modal-header">
        <button type="button" id="closebutton"class="close modal_close" data-dismiss="modal" aria-hidden="true">×</button>
         <h3>Forgot password ?</h3>
    </div>
    <div class="modal-body">
    </div>
</div>