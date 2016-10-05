<?php

require_once JPATH_SITE . '/components/com_ijoomeradv/extensions/beseated/helper.php';
JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_bcted/tables');
require_once JPATH_SITE . '/components/com_beseated/controllers/payment.php';

$this->payment = new BeseatedControllerPayment;
$this->helper = new beseatedAppHelper;

$app          = JFactory::getApplication();
$input        = $app->input;
$bookingID    = $input->get('bookingID',0,'int');
$loyaltyPoint = $input->get('loyaltyPoint','','string');

$tblTicketBooking = JTable::getInstance('TicketBooking', 'BeseatedTable');
$tblTicketBooking->load($bookingID);

$tblEvent = JTable::getInstance('Event', 'BeseatedTable');
$tblEvent->load($tblTicketBooking->event_id);

$guestUserDetail       = JFactory::getUser($tblTicketBooking->user_id);
$event_date            = date('d F Y',strtotime($tblEvent->event_date));
$event_time            = $this->helper->convertToHM($tblEvent->event_time);

$event_name            = $tblEvent->event_name;
$username              = $guestUserDetail->name;
$eventImage            = $tblEvent->image;
$location              = $tblEvent->location;
$booking_currency_code = $tblTicketBooking->booking_currency_code;
$total_price           = number_format($tblTicketBooking->total_price,0);
$total_ticket          = $tblTicketBooking->total_ticket;
$ticket_price          = number_format($tblTicketBooking->ticket_price,0);
$userEmail             = $guestUserDetail->email;
$tickets               = json_decode($tblTicketBooking->tickets_id);
$ticketsRow            = $this->payment->getTicketsImages($tickets);

$eventImage = JUri::base().'images/beseated/'.$eventImage;
$blank_img  = Juri::base().'images/edms/images/blank_img.png';
$eventImage = (getimagesize($eventImage)) ? $eventImage : $blank_img;

$emailBody        = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
							<html xmlns="http://www.w3.org/1999/xhtml">
							<head>
							<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
							<title>Beseated</title>
							<style type="text/css" media="screen">
								p{margin: 0 !important; color: #000;}
								.ExternalClass p { color: #000; margin: 0px;}
							</style>
							</head>
							<body onload="window.print()">
							<table border="0" bgcolor="#FFFFFF" cellpadding="0" cellspacing="0" width="650" align="center" style="font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:20px;">
								<tbody>
							    	<tr>
							        	<td height="10"></td>
							        </tr>
							        <tr>
							        	<td align="center">
							            	<table width="630" border="0" cellpadding="0" cellspacing="0">
							                	<tr>
							                    	<td align="left" width="100"><a href="#"><img src="'.JUri::base().'images/edms/images/beseated-logo.png"></a></td>
							                        <td align="right"></td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20"></td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2"><h3 style="margin:0; line-height:normal; color:#b58a39; font-family:Arial, Helvetica, sans-serif; font-weight:normal;">Thanks, '.$username.'! Your reservation is now confirmed.</h3></td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20"></td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2">
							                        	<table width="100%" border="0" cellpadding="10" cellspacing="0" bgcolor="e3d1b0">
							                            	<tr>
							                                	<td colspan="2" style="padding-bottom:0px;"><h4 style="margin:0; line-height:normal; color:#000; font-family:Arial, Helvetica, sans-serif; font-weight:normal; font-size:20px;"><b>'.$event_name.'</b></h4></td>
							                                </tr>
							                                <tr>
							                                	<td colspan="2">
							                                    	<table  width="100%" border="0" cellpadding="0" cellspacing="0">
							                                        	<tr>
							                                                <td width="50%">
							                                                    <table width="100%" border="0" cellpadding="10" cellspacing="0">
							                                                        <tr>

							                                                            <td><img src ="'.$eventImage.'" height="80" width="130"></td>

							                                                            <td>&nbsp;
							                                                            </td>
							                                                        </tr>
							                                                    </table>
							                                                </td>
							                                                <td valign="middle" width="50%" align="right">
							                                                    <a href="#" style="color:#b58a39; font-family:Arial, Helvetica, sans-serif; font-size:14px; text-decoration:none"><img style="vertical-align:middle;" src="'.JUri::base().'images/edms/images/setting-icn.png">&nbsp;&nbsp; <b>Manage your booking</b></a>
							                                                </td>
							                                            </tr>
							                                        </table>
							                                    </td>
							                                </tr>
							                                <tr>
										                    	<td colspan="2"></td>
										                    </tr>
							                                <tr>
							                                    <td width="50%" style="border-bottom:1px solid #b78f40; font-family:Arial, Helvetica, sans-serif;color:#000;">Event Date</td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif;color:#000;">'.$event_date.'</td>
							                                </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40; font-family:Arial, Helvetica, sans-serif;color:#000;">Location</td>
							                                    <td style="border-bottom:1px solid #b78f40; font-family:Arial, Helvetica, sans-serif;color:#000;">'.$location.'</td>
							                                </tr>
							                                <tr>
							                                    <td style="font-family:Arial, Helvetica, sans-serif; color:#000;">Tickets Purchased</td>
							                                    <td style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$total_ticket.' Tickets</td>
							                                </tr>
							                                '.$ticketsRow.'
							                                <tr>
							                                    <td style="font-family:Arial, Helvetica, sans-serif; color:#000;">Booked by</td>
							                                    <td style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$username.' (<a href="#" style="color:#000; text-decoration:none;">'.$userEmail.'</a>)</td>
							                                </tr>
							                                <tr>
										                    	<td colspan="2"></td>
										                    </tr>
							                                <tr>
							                                	<td colspan="2">
							                                    	<table width="100%" border="0" cellpadding="0" cellspacing="0">
							                                        	<tr>
							                                            	<td width="1%"></td>
							                                                <td width="98%">
																				<table width="100%" border="0" cellpadding="6" cellspacing="0">
							                                                        <tr>
							                                                        	<td width="180" style="font-size:18px; color:#000; font-family:Arial, Helvetica, sans-serif;">EVENT</td>
							                                                            <td width="160" style="font-size:18px; color:#000; font-family:Arial, Helvetica, sans-serif;">PRICE/TICKET</td>
							                                                            <td width="100" style="font-size:18px; color:#000; font-family:Arial, Helvetica, sans-serif;">TICKETS</td>
							                                                            <td style="font-size:18px; color:#000; font-family:Arial, Helvetica, sans-serif;">TOTAL</td>
																					<tr>
																						<td colspan="4">
																							<table width="100%" cellpadding="0" cellspacing="0">
										                                                        <tr>
										                                                        	<td width="190" style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$event_name.'</td>
										                                                            <td width="170" style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$booking_currency_code.' '.$ticket_price.' </td>
										                                                            <td width="115" style="font-family:Arial, Helvetica, sans-serif; color:#000;">&nbsp; '.$total_ticket.'</td>
							                                                                        <td style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$booking_currency_code.' '.$total_price.'</td>
										                                                        </tr>
																							</table>
																						</td>
																					</tr>
							                                                        <tr>
							                                                        	<td colspan="4">
							                                                            	<span style="margin:0px text-decoration:underline;font-family:Arial, Helvetica, sans-serif; color:#000;">Please Note:</span>
							                                                            	<p style="margin:0px; font-family:Arial, Helvetica, sans-serif; color:#000;">- The total price shown is the amount you have paid to the company. BESEATED does not charge any booking, administration or other fees.</p>
							                                                            </td>
							                                                        </tr>
							                                                    </table>
							                                                </td>
							                                                <td width="1%"></td>
							                                            </tr>
							                                        </table>
							                                    </td>
							                                </tr>
							                            </table>
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20">
							                        </td>
							                    </tr>

							                    <tr>
							                    	<td colspan="2" align="center">
							                        <h3 style="font-weight:normal; margin:0; font-size:24px; font-family:Arial, Helvetica, sans-serif; color:#000;">You have been awarded  &nbsp;&nbsp;<img style="vertical-align:middle;" src="'.JUri::base().'images/edms/images/money-icn-big.png">&nbsp;'.$loyaltyPoint.'</h3>
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20">
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td width="23%" valign="top" style="font-family:Arial, Helvetica, sans-serif; color:#000;">Cancellation Policy</td>
							                        <td style="font-family:Arial, Helvetica, sans-serif; color:#000;">You have the right to cancel 24 hours prior to date of reservation.<br><br>If canceled later or in case of no show, the company has the right to charge you 100% of the booking amount.</td>

							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="10">
							                        </td>
							                    </tr>
							                    <tr>
							                        <td colspan="2" style="color:#000;">
							                        <h4 style="font-size:18px; margin:0; font-family:Arial, Helvetica, sans-serif; font-weight:normal; height:30px; color:#000;">Payment</h4>
							                        You have now purchased the tickets.<br>All payments are made on the BESEATED platform.
							</td>

							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20">
							                        </td>
							                    </tr>
							                    <tr>
							                        <td colspan="2">
							                        <h4 style="font-size:18px; margin:0; font-weight:normal; height:30px; font-family:Arial, Helvetica, sans-serif;color:#000;">For further enquiries</h4>
													</td>
							                    </tr>
							                    <tr>
							                    	<td style="font-family:Arial, Helvetica, sans-serif;color:#000;">Contact us by phone </td>
							                        <td style="font-family:Arial, Helvetica, sans-serif;color:#000;">+971-xxx-xxxxxx</td>
							                    </tr>
							                    <tr>
							                    	<td style="font-family:Arial, Helvetica, sans-serif;color:#000;">Contact us by email</td>
							                        <td style="font-family:Arial, Helvetica, sans-serif;color:#000;"><a href="#" style="color:#000; text-decoration:none">contact@thebeseated.com</a></td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20">
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center">
							                        	<table width="60%" cellpadding="10" cellspacing="0" border="0" align="center" bgcolor="e3d1b0">
							                            	<tr>
							                                	<td colspan="2" style="font-size:20px; font-family:Arial, Helvetica, sans-serif;color:#000;" align="center">Get the free BESEATED app</td>
							                                </tr>
							                                <tr>
							                                	<td align="center"><a href="#"><img src="'.JUri::base().'images/edms/images/iphonapp-btn.png"></a></td>
							                                    <td align="center"><a href="#"><img src="'.JUri::base().'images/edms/images/google-play-btn.png"></a></td>
							                                </tr>
							                            </table>
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20">
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20" style=" border-top:1px solid #c09439;"></td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="color:#5f5f5f;">
							                        	<a href="#" style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif;">My Beseated ID</a>&nbsp;|&nbsp;<a style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="#">Support</a>&nbsp;|&nbsp;<a style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="#">Privacy Policy</a>
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000;">Copyright © 2015 Beseatedapp.com. All rights reserved.</td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000;">This email was sent by Beseatedapp.com, THUB, Dubai Silicon Oasis, Dubai, United Arab Emirates</td>
							                    </tr>
							                </table>
							            </td>
							        </tr>
							        <tr>
							        	<td height="10"></td>
							        </tr>
							    </tbody>
							</table>
							</body>
							</html>

							';



echo $emailBody;exit;

?>

