<?php
/**
 * @package     The Beseated.Site
 * @subpackage  com_bcted
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die;

$input        = JFactory::getApplication()->input;
$Itemid       = $input->get('Itemid', 0, 'int');
$app          = JFactory::getApplication();
$menu         = $app->getMenu();
$menuItem     = $menu->getItems( 'link', 'index.php?option=com_beseated&view=events', true );
$this->user   = JFactory::getUser();
$this->isRoot = $this->user->authorise('core.admin');
?>

<section class="page-section page-chauffeurs">
  <div class="container">
    <h2 class="heading-1">Our Events</h2>
 
    <div class="row">
      <?php $menuItem = $menu->getItems( 'link', 'index.php?option=com_beseated&view=eventsinformation', true );?>
      <?php foreach ($this->items as $key => $result):?>
        <?php $image = $result->thumb_image ?: $result->image; ?>
        <div class="col-md-4">
          <div class="item-box">
            <a class="image"
               href="<?php echo JRoute::_('index.php?option=com_beseated&view=eventsinformation&event_id='.$result->event_id.'&Itemid='.$menuItem->id ) ?>"
               style="background-image: url(<?php echo $image ? JUri::base().'images/beseated/'.$image : 'images/bcted/default/banner.png' ?>)">
            </a>
            <h3 class="heading-3">
              <span class="text">
                <?php echo $result->event_name; ?>
              </span>
            </h3>
            <p class="description">
              <?php echo date('d M Y',strtotime($result->event_date));?>
              <span class="city right"><?php echo $result->location; ?></span>
            </p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <?php echo $this->pagination->getListFooter(); ?>
  </div>
</section>

<?php echo $this->pagination->getListFooter(); ?>

