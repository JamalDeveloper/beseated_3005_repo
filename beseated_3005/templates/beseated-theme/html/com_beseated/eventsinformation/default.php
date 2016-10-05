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

?>


<section class="page-section page-event-information">
  <div class="container">
    
    <div class="row">
      <div class="col-md-8 col-md-offset-2">
        
        <h2 class="heading-4">Ticket request</h2>
        
        <h3 class="heading-1"><?php echo $this->event->event_name; ?></h3>

        <form action="<?php echo JRoute::_('index.php?option=com_beseated&view=eventsinformation'); ?>" method="post">
          <div class="bordered-box">

            <div class="image" style="background-image: url(<?php echo JURI::root().'/images/beseated/'.$this->event->image; ?>);"></div>

            <div class="row">
              <div class="col-md-4 info">
                <div class="location"><?php echo $this->event->location;?></div>
              </div>
              <div class="col-md-4 info">
                <div class="ticket-price"><?php echo BeseatedHelper::currencyFormat($this->event->currency_code,$this->event->currency_sign,$this->event->price_per_ticket);?></div>
              </div>
              <div class="col-md-4 info">
                <div class="date"><?php echo date('d M Y',strtotime($this->event->event_date));?></div>
                <div class="time">@ <?php echo $this->event->event_time;?></div>
              </div>
            </div>

            <?php if ($this->event->available_ticket): ?>
              <div class="remaining">
                Remaining x
                <span><?php echo $this->event->available_ticket - 1;?></span>              
              </div>
              
              <div class="field counter">
                <input type="hidden" name="total_ticket" value="1">
              </div>

              <hr>

              <div class="summary">
                <span class="tickets"><span>1</span> tickets - </span>
                <span class="price"><span><?php echo $this->event->price_per_ticket; ?></span> <?php echo $this->event->currency_sign; ?></span>
              </div>

              <button type="submit" class="button">Request Tickets</button>
            <?php else: ?>
              <hr>
              <p>All tickets sold</p>
            <?php endif ?>                                   
          
          </div>
          
          <input type="hidden" id="event_id" name="event_id" value="<?php echo $this->event->event_id; ?>">
          <input type="hidden" id="ticket_price" name="ticket_price" value="<?php echo $this->event->price_per_ticket; ?>">
          <input type="hidden" id="booking_currency_code" name="booking_currency_code" value="<?php echo $this->event->currency_code;?>">
          <input type="hidden" id="booking_currency_sign" name="booking_currency_sign" value="<?php echo $this->event->currency_sign; ?>">
          <input type="hidden" id="task" name="task" value="eventsinformation.bookEventTicket">
          <input type="hidden" id="view" name="view" value="eventsinformation">
          <input type="hidden" id="Itemid" name="Itemid" value="<?php echo $Itemid; ?>">
          <input type="hidden" id="total_price" name="total_price" value="<?php echo $this->event->price_per_ticket; ?>">
        </form>
      </div>
    </div>
  </div>
</section>

<script>
  var remaining = <?php echo $this->event->available_ticket;?>;
  var price = <?php echo $this->event->price_per_ticket; ?>;

  function ticketsNumberChange(counter, number) {
    $('#total_price').val(number * price);
    $('.tickets span').html(number);
    $('.price span').html(number * price);
    $('.remaining span').html(remaining - number);
  }

  $('.counter').counter({
    min: 1,
    max: remaining,
    onChange: ticketsNumberChange 
  })
</script>