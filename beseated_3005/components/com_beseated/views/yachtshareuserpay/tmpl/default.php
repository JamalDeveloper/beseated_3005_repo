<?php
/**
 * @package     The Beseated.Site
 * @subpackage  com_bcted
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
$templateDir = Juri::base().'templates/'.JFactory::getApplication()->getTemplate();

// No direct access to this file
defined('_JEXEC') or die;
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$input           = JFactory::getApplication()->input;
$Itemid          = $input->get('Itemid', 0, 'int');
$firstTimeShare   = $input->get('firstTimeShare', 0, 'int');
$viewByShareUser   = $input->get('viewByShareUser', 0, 'int');
$booking_split_id   = $input->get('yacht_booking_split_id', 0, 'int');

$document = JFactory::getDocument();
$document->addScript(Juri::root(true) . '/components/com_beseated/assets/confirm-js/jquery.confirm.js');
//$document->addStylesheet('//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.2/css/bootstrap-combined.no-icons.min.css');
$document->addStylesheet($templateDir.'/font-awesome/css/font-awesome.css');
$document->addStylesheet($templateDir.'/progressbar/css/bootstrap-progressbar-3.3.4.css');
$document->addStylesheet($templateDir.'/progressbar/css/bootstrap-progressbar-3.3.4.min.css');

$redirectURL = JUri::base().'index.php?option=com_beseated&view=guestrequests&Itemid='.$Itemid;

$user     = JFactory::getUser();
$userType = BeseatedHelper::getUserType($user->id);

$yachtBooking = $this->yachtBookingDetail;

//echo "<pre>";print_r($yachtBooking);echo "<pre/>";exit(); // booking_currency_sign

//$yachtBooking = $this->yachtBookingDetail;
$deposite_amount = $yachtBooking->total_price - $yachtBooking->each_person_pay;
$yachtShareUserDetail = $this->yachtShareUserDetail;


?>

<link rel="stylesheet" href="bootstrap-progressbar-3.3.4.min.css">
<link rel="stylesheet" href="bootstrap-progressbar-3.3.4.css">

<div class="row">
  <div class="span9 payment-request-detail">
    <h2><?php echo $yachtBooking->yacht_name; ?></h2>
  </div>
</div>
<div class="row">
    <div class="payment-request-detail">
      <div class="span6"><?php echo $yachtBooking->full_name; ?></div>
    </div>
</div>
<div class="row">
    <div class="payment-request-detail">
      <div class="span6"><?php echo $yachtBooking->service_name; ?></div>
    </div>
</div>
<div class="row">
    <div class="payment-request-detail">
      <div class="span6"><i class="fa fa-calendar" aria-hidden="true">&nbsp;&nbsp;</i><?php echo date('d M Y',strtotime($yachtBooking->booking_date)); ?></div>
      <div class="span3"> <i class="fa fa-clock-o" aria-hidden="true">&nbsp;&nbsp;</i><?php echo date('h:i',strtotime($yachtBooking->booking_time)); ?></div>
    </div>
</div>

<div class="row">
    <div class="payment-request-detail">
      <div class="span6">Bill Amount</div>
      <div class="span3"><?php echo $yachtBooking->booking_currency_sign.' '.number_format($yachtBooking->total_price); ?></div>
    </div>
</div>

<div class="row">
    <div class="payment-request-detail">
      <div class="span6">Deposite Amount</div>
      <div class="span3"><?php echo  $yachtBooking->booking_currency_sign.' '.number_format($deposite_amount); ?></div>
    </div>
</div>

<div class="row">
    <div class="payment-request-detail">
      <div class="span6">Shared By <?php echo count($yachtShareUserDetail); ?> Clients</div>
      <div class="span3"></div>
    </div>
</div>

<div class="row">
    <div class="payment-request-detail">
      <div class="span6">Amount To Pay</div>
      <div class="span3"><?php echo  $yachtBooking->booking_currency_sign.' '.number_format($yachtBooking->each_person_pay); ?></div>
    </div>
</div>

<div class="pay-share-user">
    <a href="<?php echo JUri::root().'index.php?option=com_beseated&task=payment.pay&booking_id='.$booking_split_id.'&booking_type=yacht.split'; ?>">
        <input type="button" class="btn btn-large" name="payment" value="Pay By Credit Card">
    </a>
</div>





