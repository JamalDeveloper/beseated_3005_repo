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

$app          = JFactory::getApplication();
$input        = JFactory::getApplication()->input;
$tableId      = $input->get('table_id', 0, 'int');
$clubID       = $input->get('club_id', 0, 'int');
$this->user   = JFactory::getUser();
$this->isRoot = $this->user->authorise('core.admin');
$menu         = $app->getMenu();
$menuItem     = $menu->getItems( 'link', 'index.php?option=com_beseated&view=userbookings', true );
$Itemid       = $menuItem->id;

?>

<section class="page-section page-venue-bottles">
  <div class="container">
    
    <div class="sub-menu">
      <?php include_once('templates/beseated-theme/partials/guest-profile-menu.php') ?>
    </div>

    <h3 class="heading-3">Pick up a bottle</h3>    

    <div class="row">
      <div class="col-md-4">        
        <div class="sidebar-box">
          <form method="post" action="<?php echo JRoute::_('index.php?option=com_beseated&view=userbookings&club_id='.$clubID.'&Itemid='.$Itemid);?>">
            <p class="heading-3"><?php echo $this->tableDetail->table_name; ?></p>
            <p class="pull-right"><?php echo $this->tableBooked->total_guest;?> Guests</p>
            <p>Date: <?php echo $this->tableBooked->booking_date;?></p>
            <hr>
            <div id="cart"></div>
            <p class="empty">Please add some bottles</p>
            <p class="not-reached">Table Minimum Not Reached</p>
            <hr>
            <p class="total total-price pull-right">AED 0</p>
            <p class="total">Total:</p>
            <button class="button pull-right place-order-button">Order</button>

            <input type="hidden" id="venue_table_booking_id" name="venue_table_booking_id" value="<?php echo $this->tableBooked->venue_table_booking_id; ?>">
            <input type="hidden" id="table_id" name="table_id" value="<?php echo $tableId; ?>">
            <input type="hidden" id="task" name="task" value="clubbottlebooking.bookVenueBottle">
            <input type="hidden" id="view" name="view" value="clubguestlist">
            <input type="hidden" id="clubId" name="club_id" value="<?php echo $clubID; ?>">
            <input type="hidden" id="Itemid" name="Itemid" value="<?php echo $Itemid; ?>">
            <input type="hidden" id="min_price" name="min_price" value="<?php echo $this->tableDetail->min_price;?>">
            <input type="hidden" id="bottle_id" name="bottle_id" value="">
            <input type="hidden" id="price" name="price" value="">
            <input type="hidden" id="qty" name="qty" value="">
            <input type="hidden" id="total_price" name="total_price" value="0">
          </form>
        </div>
      </div>
      <div class="col-md-8">
        <div class="row">
          <?php foreach ($this->items as $item):?>
            <div class="col-md-6">
              <div class="item-box">
                <div class="image" style="background-image: url(<?php echo empty($item->image) ? JUri::root() . 'images/beseated/default/banner.png' : JUri::root() . 'images/beseated/'.$item->image;?>);"></div>
                <div class="description">
                  <h3 class="heading-3"><?php echo ucfirst($item->brand_name);?></h3>
                  <p class="right"><?php echo BeseatedHelper::currencyFormat($item->currency_code,$item->currency_sign,$item->price); ?></p>
                  <p><?php echo ucfirst($item->size);?> ml</p>
                </div>
                <div class="actions">
                  <div class="counter"
                       data-id="<?php echo $item->bottle_id; ?>"
                       data-name="<?php echo ucfirst($item->brand_name);?>"
                       data-price="<?php echo $item->price;?>"
                       data-size="<?php echo $item->size;?>"
                       data-currency-sign="<?php echo $item->currency_sign;?>"
                       data-quantity="0"></div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>        
      </div>      
    </div>
  </div>
</section>

<script>
  var minPrice = <?php echo $this->tableDetail->min_price;?>;
  var cart = {};
  var totalPrice = 0;

  function checkMinPrice() {
    var reached = totalPrice >= minPrice;
    var orderButton = $('.place-order-button');
    var warning = $('.not-reached');

    reached ? orderButton.removeAttr('disabled') : orderButton.attr('disabled', true);
    reached ? warning.hide() : warning.show();
  }

  function summarize(counter, quantity) {
    var element = counter.element;
    var id = element.data('id');
    var items = []

    if(quantity == 0) {
      delete cart[id];
    } else {
      cart[id] = {
        id: id,
        size: element.data('size'),
        name: element.data('name'),
        price: parseFloat(element.data('price')),
        totalPrice: parseFloat(element.data('price')) * quantity,
        quantity: quantity,
        currencySign: element.data('currencySign'),
      }
    }

    $('#cart').children().remove();

    for(var key in cart) {
      if(!cart.hasOwnProperty(key))
        continue;
      
      var item = cart[key];
      items.push(item);

      $('#cart').append('<div class="item">' +
        '<p class="pull-right">' + item.currencySign + ' ' + item.totalPrice + '</p>' +
        '<p>' + item.quantity + 'x - ' + item.name + ' (' + item.size + 'ml)' + '</p>' +
      '</div>')
    }

    totalPrice = items.reduce(function(prev, curr) { return prev + curr.totalPrice; }, 0)

    $('#bottle_id').val(items.map(function(item) { return item.id; }).join(','));
    $('#qty').val(items.map(function(item) { return item.quantity; }).join(','));
    $('#price').val(items.map(function(item) { return item.price; }).join(','));
    $('#total_price').val(items.map(function(item) { return item.totalPrice; }).join(','));   
    $('#cart .item').length === 0 ? $('.empty').show() : $('.empty').hide();
    $('.total-price').html(element.data('currencySign') + ' ' + totalPrice);

    checkMinPrice();
  }

  checkMinPrice();

  $('.counter').counter({
    onChange: summarize,
    min: 0
  });
</script>
