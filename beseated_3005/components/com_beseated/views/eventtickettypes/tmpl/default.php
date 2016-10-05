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
JHtml::_('bootstrap.tooltip');
JHtml::_('bootstrap.modal');
$input       = JFactory::getApplication()->input;
$Itemid      = $input->get('Itemid', 0, 'int');
$clubID      = $input->get('club_id', 0, 'int');
$app         = JFactory::getApplication();
$menu        = $app->getMenu();
$menuItem    = $menu->getItems( 'link', 'index.php?option=com_users&view=login', true );
$loginItemid = $menuItem->id;
$bctParams   = BeseatedHelper::getExtensionParam();
$accessLevel = BeseatedHelper::getGroupAccessLevel($bctParams->beseated_guest);
$access      = array('access','link');
$property    = array($accessLevel,'index.php?option=com_beseated&view=clubinformation');
$menuItem2   = $menu->getItems( $access, $property, true );
$link2       = 'index.php?option=com_beseated&view=clubinformation&club_id='.$clubID.'&Itemid='.$menuItem2->id;
$loginLink   = 'index.php?option=com_users&view=login&Itemid='.$loginItemid.'&return='.base64_encode($link2);
$document    = JFactory::getDocument();
$document->addStylesheet(JUri::root().'components/com_beseated/assets/css/bootstrap-toggle.min.css');
$document->addScript(JUri::root().'components/com_beseated/assets/confirm-js/bootstrap-toggle.min.js');
$document->addScript(JUri::root().'modules/mod_profileslider/media/js/html5gallery.js');

//echo "<pre>";print_r($this->ticketTypesDetail);echo "<pre/>";exit();

?>

<div class="eventtickettypes clb-infowrp">

	<div class="event-name">
			<?php echo $this->event_name; ?>
	</div>
	<?php foreach ($this->ticketTypesDetail as $key => $ticketType) : ?>

		<?php if($ticketType->available_tickets) : ?>
			<a href="<?php echo JRoute::_('index.php?option=com_beseated&view=eventsinformation&event_id='.$ticketType->event_id.'&ticket_type_id='.$ticketType->ticket_type_zone_id.'&Itemid='.$Itemid); ?>">
		<?php else : ?>
			<a href="#">
		<?php endif; ?>
		
			<?php if($ticketType->available_tickets): ?>
			<div class="event-image">
				<img src="<?php echo $ticketType->image;?>" />
				<h3><?php echo $ticketType->available_tickets  .' TICKETS AVAILABLE'; ?></h3>
			</div>
		    <?php else: ?>
		    <div class="event-image">
				<img src="<?php echo $ticketType->image;?>" />
				<h4><?php echo 'SOLD OUT'; ?></h4>
			</div>
		    <?php endif; ?>

			<div class="event-ticket-price">
				<div class="ticket-detail">
					<div class="ticket-type">
						<?php echo $ticketType->ticket_type;?>
					</div>
					<div class="ticket-type">
						<?php echo $ticketType->ticket_zone;?>
					</div>
					<div class="ticket-type">
						<?php echo BeseatedHelper::currencyFormat($ticketType->currency_code,$ticketType->currency_sign,$ticketType->ticket_price);?>
					</div>
				</div>
			</div>
		</a>

	<?php endforeach; ?>
</div>



