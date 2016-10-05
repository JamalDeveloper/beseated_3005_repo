<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');
?>
<div class="page-login">
	<div class="container">
		<div class="login<?php echo $this->pageclass_sfx?>">
			<?php if ($this->params->get('show_page_heading')) : ?>
			<h2 class="heading-1">
				<?php echo $this->escape($this->params->get('page_heading')); ?>
			</h2>
			<?php endif; ?>
			<?php if (($this->params->get('logindescription_show') == 1 && str_replace(' ', '', $this->params->get('login_description')) != '') || $this->params->get('login_image') != '') : ?>
				<div class="login-description">
				<?php endif; ?>
					<?php if ($this->params->get('logindescription_show') == 1) : ?>
						<?php echo $this->params->get('login_description'); ?>
					<?php endif; ?>
					<?php if (($this->params->get('login_image') != '')) :?>
						<img src="<?php echo $this->escape($this->params->get('login_image')); ?>" class="login-image" alt="<?php echo JText::_('COM_USERS_LOGIN_IMAGE_ALT')?>"/>
					<?php endif; ?>
				<?php if (($this->params->get('logindescription_show') == 1 && str_replace(' ', '', $this->params->get('login_description')) != '') || $this->params->get('login_image') != '') : ?>
				</div>
			<?php endif; ?>
			<form action="<?php echo JRoute::_('index.php?option=com_users&task=user.login'); ?>" method="post" class="form-validate form-horizontal well">
				<fieldset>
					<?php foreach ($this->form->getFieldset('credentials') as $field) : ?>
						<?php if (!$field->hidden) : ?>
							<div class="control-group">
								<div class="controls">								
									<?php echo str_replace("/>", 'placeholder="' . $field->name . '" />', $field->input); ?>
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
						<div  class="remember">				    
							<input type="checkbox" name="remember" id="remember"> 
							<label for="email"><?php echo JText::_('COM_USERS_LOGIN_REMEMBER_ME') ?></label>
						</div>
					<?php endif; ?>
					<div class="control-group">
						<div class="controls">
							<button type="submit" class="button">
								SIGN IN
							</button>
						</div>
					</div>

					<input type="hidden" name="return" value="<?php echo base64_encode($this->params->get('login_redirect_url', $this->form->getValue('return'))); ?>" />
					<?php echo JHtml::_('form.token'); ?>
					<div class="forgot-password">
						<a href="<?php echo JRoute::_('index.php?option=com_users&view=reset'); ?>"><?php echo JText::_('COM_USERS_LOGIN_RESET'); ?></a>
					</div>
				</fieldset>
			</form>
			<div class="terms">
				<p>By Logging into Beseated you are accepting the <a href="index.php?option=com_content&view=article&id=2&Itemid=285">Terms and Conditions</a></p>
			</div>

			<?php 

			if(isset($_GET['test']))
			{

				$fbReturnUrl = JRequest::getVar('return');?>
				<a href="#" onclick="window.open('index.php?option=com_fbconnct&task=login&format=raw&return=<?php echo $fbReturnUrl; ?>','name','height=300,width=550');return false;" >Login with Facebook</a>
			<?php
			}
			?>
		</div>
	</div>
</div>	
<script>
	$('input').iCheck({
		checkboxClass: 'icheckbox_minimal',
		radioClass: 'iradio_minimal'
	});
</script>
