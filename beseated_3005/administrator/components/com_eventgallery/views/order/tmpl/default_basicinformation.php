<?php // no direct access

/**
 * @package     Sven.Bluege
 * @subpackage  com_eventgallery
 *
 * @copyright   Copyright (C) 2005 - 2013 Sven Bluege All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

?>

<div class="basic-information">
    <p><strong><?php echo JText::_('COM_EVENTGALLERY_ORDER_USERDATA_EMAIL_LABEL') ?></strong><br />
    <a href="mailto:<?php echo $this->escape($this->lineitemcontainer->getEMail()) ?>"><?php echo $this->escape($this->lineitemcontainer->getEMail()) ?></a></p>

    <?php IF (strlen($this->lineitemcontainer->getPhone())>0):?>
        <p><strong><?php echo JText::_('COM_EVENTGALLERY_ORDER_USERDATA_PHONE_LABEL') ?></strong><br />
        <a href="tel:<?php echo $this->escape($this->lineitemcontainer->getPhone()) ?>"><?php echo $this->escape($this->lineitemcontainer->getPhone()) ?></a></p>
    <?php ENDIF; ?>

    <?php IF (strlen($this->lineitemcontainer->getMessage())>0):?>
        <p><strong><?php echo JText::_('COM_EVENTGALLERY_ORDER_USERDATA_MESSAGE_LABEL') ?></strong><br />
        <?php echo $this->escape($this->lineitemcontainer->getMessage()) ?></p>
    <?php ENDIF; ?>

    <?php IF (null != $this->lineitemcontainer->getBillingAddress() && strlen($this->lineitemcontainer->getBillingAddress()->getTaxId())>0 ):?>
        <p class="basic-message"><strong><?php echo JText::_('COM_EVENTGALLERY_ORDER_USERDATA_TAXID_LABEL') ?></strong><br />
        <?php echo $this->escape($this->lineitemcontainer->getBillingAddress()->getTaxId()); ?><br/>
    <?php ENDIF; ?>
</div>
