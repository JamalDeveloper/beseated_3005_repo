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
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$input    = JFactory::getApplication()->input;
$Itemid        = $input->get('Itemid', 0, 'int');

$document = JFactory::getDocument();
$document->addScript(Juri::root(true) . '/components/com_beseated/assets/confirm-js/jquery.confirm.js');

$chauffeurBookingDetail = $this->chauffeurBookingDetail;


$total_price        = $chauffeurBookingDetail->total_price;
$booking_cur_sign   = $chauffeurBookingDetail->booking_currency_sign;
$capacity   = $chauffeurBookingDetail->capacity;

$redirectURL = JUri::base().'index.php?option=com_beseated&view=guestrequests&Itemid='.$Itemid;

//echo "<pre>";print_r($luxuryBookingDetail);echo "<pre/>";exit();

?>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script type="text/javascript">
jQuery(document).ready(function($) 
{
    var total_price = '<?php echo $total_price; ?>';
    var book_cur_sign = '<?php echo $booking_cur_sign; ?>';
    var capacity = '<?php echo $capacity; ?>';

    var eachpersonpay = book_cur_sign+' 0 Each';

    jQuery('#eachpersonpay').val(eachpersonpay);

    setTimeout(function() 
    {
          jQuery("#firstActive").trigger('mouseenter');
          jQuery("#firstActive").trigger('click');
    },10);

  
    jQuery('#next').click(function(event) 
    {
      jQuery("#previous").css({'pointer-events': ''});

      jQuery('.number').each(function(index, el) 
      {
        if (jQuery(jQuery('.number')[index]).text() == capacity - 1)
        {
           jQuery("#next").css({'pointer-events': 'none'}); 

        }

        var a  = parseInt(jQuery(jQuery('.number')[index]).text()) + parseInt(1);
        jQuery(jQuery('.number')[index]).text(a);
        jQuery(jQuery('.number')[index]).val(a);
      });
    });

    jQuery('#previous').click(function(event) 
    {
      jQuery("#next").css({'pointer-events': ''});

      jQuery('.number').each(function(index, el) 
      {
        if (jQuery(jQuery('.number')[index]).text() == 1)
        {
           jQuery("#previous").css({'pointer-events': 'none'});
           return false;
        }

        var a  = parseInt(jQuery(jQuery('.number')[index]).text()) - parseInt(1);
        jQuery(jQuery('.number')[index]).text(a);
        jQuery(jQuery('.number')[index]).val(a);
      });
    });

    jQuery('.number').click(function(event) 
    {
        var eachpersonpay = book_cur_sign+' '+Math.round(parseInt(total_price) / parseInt(jQuery(this).text())) + ' Each';

        jQuery('#eachpersonpay').val(eachpersonpay);
        jQuery('#sharedPeopleCount').val(jQuery(this).text());
        jQuery('#eachPersonPayAmount').val(Math.round(parseInt(total_price) / parseInt(jQuery(this).text())));
        
    });
});
</script>

<div class="share-booking-amount">
        <div class="controls">
            <label>Total Amount To Share</label>
            <input type="text" id="subject" value="<?php echo $chauffeurBookingDetail->booking_currency_sign.' '.number_format($chauffeurBookingDetail->total_price); ?>" readonly>
           
            <label>Indicate Number Of Guests</label>
            <nav aria-label="Page navigation">
              <ul class="pagination">
                <li class="page-item">
                  <a  id="previous" class="page-link" href="#" aria-label="Previous">
                    <span aria-hidden="true">&laquo;</span>
                  </a>
                </li>
                <li class="page-item numberli"><a class="page-link number" value="" href="#" id="firstActive">1</a></li>
                <li class="page-item numberli"><a class="page-link number" value="" href="#">2</a></li>
                <li class="page-item numberli"><a class="page-link number" value="" href="#">3</a></li>
                <li class="page-item numberli"><a class="page-link number" value="" href="#">4</a></li>
                <li class="page-item numberli"><a class="page-link number" value="" href="#">5</a></li>
                <li class="page-item">
                  <a id="next" class="page-link" href="#" aria-label="Next">
                    <span aria-hidden="true">&raquo;</span>
                  </a>
                </li>
              </ul>
            </nav>

            <label>Price Per Guest</label>
            <input type="text" id="eachpersonpay" value="" readonly>
            <form name="share" method="post" action="<?php echo JRoute::_('index.php?option=com_beseated');?>">
            
                <button type="submit" class="btn btn-large span" >Share</button>
                <input type="hidden" id="sharedPeopleCount" name="sharedPeopleCount" value="">
                <input type="hidden" id="eachPersonPayAmount" name="eachPersonPayAmount" value="">
                <input type="hidden" id="task" name="task" value="chauffeurshareamount.shareGuestCountUpdate">
                <input type="hidden" id="booking_id" name="booking_id" value="<?php echo $chauffeurBookingDetail->chauffeur_booking_id; ?>">
            </form>
        </div>
</div>





