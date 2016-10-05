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

$app    = JFactory::getApplication();
$Itemid = $app->input->getInt('Itemid');

?>

<section class="page-section page-rsvp-details">
  <div class="container">
    
    <div class="submenu">
      <?php include_once('templates/beseated-theme/partials/guest-profile-menu.php') ?>
    </div>

    <div class="row">
      <div class="col-md-8 col-md-offset-2">
        <h3 class="heading-3">Request details</h3>
        <div class="item-box">
          <h3 class="heading-1"><?php echo ucfirst($this->venueRsvpDetail->table_name);?></h3>
          <p class="date"><?php echo date('d-M-Y',strtotime($this->venueRsvpDetail->booking_date));?></p>
          <hr class="small">
          <p class="min-spend"><?php echo "Minimum Spend&nbsp;&nbsp;" . BeseatedHelper::currencyFormat($this->venueRsvpDetail->booking_currency_code,$this->venueRsvpDetail->booking_currency_sign,$this->venueRsvpDetail->min_price);?></p>
          <hr class="small">
          <p class="sumamry">
            <span class="all-count"><?php echo $this->venueRsvpDetail->total_guest;?> people</span> - 
            <span class="males-count"><?php echo $this->venueRsvpDetail->male_guest;?> MALE</span>
            <span class="females-count"><?php echo $this->venueRsvpDetail->female_guest;?> FEMALE</span>
          </p>
          <div class="actions">
            <?php if ($this->vanueHasBottle > 0):?>
              <div class="bottle-add">
                <p>Want to add Bottle Service?</p>
                <a href="index.php?option=com_beseated&view=clubbottles&club_id=<?php echo $this->venueRsvpDetail->venue_id;?>&table_id=<?php echo $this->venueRsvpDetail->table_id;?>&Itemid=<?php echo $itemId; ?>">
                  <button class="table-yes-btn button">Yes</button>
                </a>
                <button class="table-no-btn button">No</button>
              </div>
            <?php else:?>
              <form id="form_confirmbooking" class="form-horizontal" method="post" a action="<?php echo JRoute::_('index.php?option=com_beseated&view=guestrequestsdetail&Itemid='.$Itemid);?>">
                <button class="table-book-btn button">Beseated</button>
                <button class="table-cancel-btn button">Cancel</button>
                <input type="hidden" id="task" name="task" value="GuestRequestsDetail.confirmBooking">
                <input type="hidden" id="view" name="view" value="GuestRequestsDetail">
                <input type="hidden" id="Itemid" name="Itemid" value="<?php echo $Itemid; ?>">
                <input type="hidden" id="booking_id" name="booking_id" value="<?php echo $this->venueRsvpDetail->venue_table_booking_id; ?>">
              </form>
            <?php endif;?>
            <div class="bottle-book-btn">
              <form id="form_confirmbooking" class="form-horizontal" method="post" a action="<?php echo JRoute::_('index.php?option=com_beseated&view=guestrequestsdetail&Itemid='.$Itemid);?>">
                <button class="table-book-btn button">Beseated</button>
                <button class="table-cancel-btn button">Cancel</button>
                <input type="hidden" id="task" name="task" value="GuestRequestsDetail.confirmBooking">
                <input type="hidden" id="view" name="view" value="GuestRequestsDetail">
                <input type="hidden" id="Itemid" name="Itemid" value="<?php echo $Itemid; ?>">
                <input type="hidden" id="booking_id" name="booking_id" value="<?php echo $this->venueRsvpDetail->venue_table_booking_id; ?>">
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<script type="text/javascript">
 
  $('.bottle-book-btn').hide();

  $('.table-no-btn').click(function(event) {
    $('.bottle-book-btn').show();
    $('.bottle-add').hide();
  });

  $('.table-book-btn').click(function(event) {
    $('#form_confirmbooking').submit();
  });

</script>