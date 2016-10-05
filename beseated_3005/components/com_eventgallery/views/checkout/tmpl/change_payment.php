<?php // no direct access

/**
 * @package     Sven.Bluege
 * @subpackage  com_eventgallery
 *
 * @copyright   Copyright (C) 2005 - 2013 Sven Bluege All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

/**
 * @var EventgalleryLibraryFactoryPaymentmethod $paymentMethodFactory
 */

$paymentMethodFactory = EventgalleryLibraryFactoryPaymentmethod::getInstance();
$methods = $paymentMethodFactory->getMethods(true);
$currentMethod
    = $this->cart->getPaymentMethod() == NULL ? $paymentMethodFactory->getDefaultMethod()
    : $this->cart->getPaymentMethod();


?>

<div class="control-group">
    <label for="paymentid"><?php echo JText::_('COM_EVENTGALLERY_CART_CHECKOUT_FORM_PAYMENTMETHOD_LABEL') ?></label>
    <div class="controls">


        <select class="" name="paymentid" id="paymentid">
            <?php FOREACH ($methods as $method): ?>
                <?php

                /**
                 * @var EventgalleryLibraryMethodsPayment $method
                 */
                $selected = "";

                if ($method->getId() == $currentMethod->getId()) {
                    $selected = 'selected = "selected"';
                }

                $disabled = "";

                if ($method->isEligible($this->cart)==false ) {
                    $disabled = 'disabled="disabled"';
                    $selected = "";
                }

                ?>
                <option <?php echo $selected; ?> <?php echo $disabled; ?>
                    value="<?php echo $method->getId(); ?>"><?php echo $method->getDisplayName(); ?>
                    (<?php echo $method->getPrice($this->cart); ?>)
                </option>
            <?php ENDFOREACH ?>
        </select>
    </div>
</div>




