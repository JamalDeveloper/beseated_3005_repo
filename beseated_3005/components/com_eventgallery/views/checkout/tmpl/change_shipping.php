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
 * @var EventgalleryLibraryFactoryShippingmethod $shippingMethodFactory
 */

$shippingMethodFactory = EventgalleryLibraryFactoryShippingmethod::getInstance();

$methods = $shippingMethodFactory->getMethods(true);
$currentMethod
    = $this->cart->getShippingMethod() == NULL ? $shippingMethodFactory->getDefaultMethod()
    : $this->cart->getShippingMethod();


?>

<div class="control-group">
    <label for="shippingid"><?php echo JText::_('COM_EVENTGALLERY_CART_CHECKOUT_FORM_SHIPPINGMETHOD_LABEL') ?></label>
    <div class="controls">


        <select class="" name="shippingid" id="shippingid">
            <?php FOREACH ($methods as $method): ?>


                <?php
                /**
                 * @var EventgalleryLibraryMethodsShipping $method
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

