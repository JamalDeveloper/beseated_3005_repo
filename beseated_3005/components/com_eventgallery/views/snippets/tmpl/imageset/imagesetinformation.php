<?php // no direct access
/**
 * @package     Sven.Bluege
 * @subpackage  com_eventgallery
 *
 * @copyright   Copyright (C) 2005 - 2013 Sven Bluege All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access'); 

if ($this->imageset != null) {
?>

<style type="text/css">
    .imageset {

    }

    .imageset input[type="radio"] {
        margin-top: 1px;       
        vertical-align:text-top;
    }

    .imageset .price {
        font-weight: bold;
    }
</style>

<div class="imageset">
    <p>
        <?PHP IF (strlen($this->imageset->getDescription())>0):?>
            <span class="imageset-description"><?php echo $this->imageset->getDescription(); ?></span>
        <?PHP ELSE: ?>
            <span class="imageset-description"><?php echo JText::_('COM_EVENTGALLERY_IMAGESET_PRICES') ?></span>
        <?PHP ENDIF; ?>
    </p>
    <table class="table">
        <?php FOREACH($this->imageset->getImageTypes() as $imageType): ?>            
            <?php
            /**
             * @var EventgalleryLibraryImagetype $imageType
             */
            $checked = "";
                if ($imageType->getId() == $this->imageset->getDefaultImageType()->getId()) {
                    $checked = 'checked="checked"';
                }
            ?>
            <tr><td class="displayname">
                <?php echo $imageType->getDisplayName(); ?>
            </td>
            <td>
                <input title="<?php echo JText::_('COM_EVENTGALLERY_LINEITEM_IMAGETYPE')?>" name="currentimagetype" value="<?php echo $imageType->getId(); ?>" type="radio" <?php echo $checked ?> >
            </td>
            <td class="price"><?php echo $imageType->getPrice(); ?> <strong>*</strong></td>
            <td class="description"><?php echo $imageType->getDescription(); ?></td>
        </tr>
        <?php ENDFOREACH ?>    
    </table>
    <small><strong>*</strong> <?php echo JText::_('COM_EVENTGALLERY_PRODUCT_VAT_HINT') ?></small>
</div>
<?php 
// end if ($this->imageset != null)
}