<?php
/**
 * @package     iJoomerAdv.Site
 * @subpackage  com_beseated
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
jimport('joomla.application.component.helper');

class BeseatedEmailHelper
{
	private $IJUserID;

	private $mainframe;

	private $db;

	private $my;

	private $config;

	private $helper;

	public $imageLink;

	function __construct()
	{
		$this->db        = JFactory::getDbo();
		$this->my        = JFactory::getUser();
		$this->IJUserID  = $this->my->id;
		$this->imageLink = '<img alt="The Beseated" src="'.JUri::base().'images/beseated/email-footer.jpg'.'"/>';
		require_once JPATH_SITE . '/components/com_beseated/helpers/beseated.php';
		$beseatedParams = BeseatedHelper::getExtensionParam();

		$this->logo_link           = $beseatedParams->logo_link;
		$this->support_link        = $beseatedParams->support_link;
		$this->privacy_policy_link = $beseatedParams->privacy_policy_link;
		$this->faq_link            = $beseatedParams->faq_link;
		$this->contact_phone_no    = $beseatedParams->contact_phone_no;
		$this->app_store_link      = $beseatedParams->app_store_link;
		$this->google_play_link    = $beseatedParams->google_play_link;
		$this->site_link           = $beseatedParams->site_link;
		$this->blank_img           = Juri::base().'images/edms/images/blank_img.png';
	}

	public function contactAdmin($contactSubject, $conatctMessage)
	{
		$lang = JFactory::getLanguage();
		$extension = 'com_beseated';
		$base_dir = JPATH_SITE;
		$language_tag = 'en-GB';
		$reload = true;
		$lang->load($extension, $base_dir, $language_tag, $reload);
		$beseatedConfig    = BeseatedHelper::getExtensionParam();
		$userType          = BeseatedHelper::getUserType($this->my->id);
		$contactEmail      = $this->my->email;
		$bctedContactEmail = $beseatedConfig->contact_email;
		$subject           = $contactSubject; //JText::_('COM_BCTED_CONATACT_EMAIL_SUBJECT');
		$body              = $conatctMessage; //JText::sprintf('COM_BCTED_CONATACT_EMAIL_BODY', $contactName, $contactName, $contactEmail, $contactMobile, $conatctMessage);
		$this->sendEmail($bctedContactEmail,$subject,$body);
	}

	public function contactThankYouEmail()
	{
		$lang = JFactory::getLanguage();
		$extension = 'com_beseated';
		$base_dir = JPATH_SITE;
		$language_tag = 'en-GB';
		$reload = true;
		$lang->load($extension, $base_dir, $language_tag, $reload);
		$userType    = BeseatedHelper::getUserType($this->my->id);
		/*chauffeurUserDetail
		protectionUserDetail
		venueUserDetail
		yachtUserDetail*/
		$username = "";

		if($userType == 'Protection')
		{
			$protection = BeseatedHelper::protectionUserDetail($this->my->id);
			$username = $protection->protection_name;
		}
		else if($userType == 'Venue')
		{
			$venue = BeseatedHelper::venueUserDetail($this->my->id);
			$username = $venue->venue_name;
		}
		else if($userType == 'Guest')
		{
			$guest = BeseatedHelper::getBeseatedUserProfile($this->my->id);
			$username = $guest->full_name;
		}
		else if($userType == 'Yacht')
		{
			$yacht = BeseatedHelper::yachtUserDetail($this->my->id);
			$username = $yacht->full_name;
		}
		else if($userType == 'Chauffeur')
		{
			$chauffeur = BeseatedHelper::chauffeurUserDetail($this->my->id);
			$username = $chauffeur->full_name;
		}

		$contactEmail = $this->my->email;
		$subject      = JText::_('COM_BESEATED_THANK_YOU_CONTACT_EMAIL_SUBJECT');
		$body         = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		    <html>
			<head>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			<title>Beseated</title>
			<style type="text/css" media="screen">
				p{margin: 0px !important; color: #000;}
				.ExternalClass p { color: #000; margin: 0px !important;}
				br{line-height: 12px;}
				.HOEnZb im p{margin:0;}
			</style>
			</head>
			<body>
			<table border="0" bgcolor="#FFFFFF" cellpadding="0" cellspacing="0" width="650" align="center" style="font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:20px;">
				<tbody>
			    	<tr>
			        	<td height="10"></td>
			        </tr>
			        <tr>
			        	<td align="center">
			            	<table width="630" border="0" cellpadding="0" cellspacing="0">
			                	<tr>
			                        <td align="right" colspan="2">
			                        	<table width="100%" border="0" cellpadding="0" cellspacing="0">
			                            	<tr>
			                                	<td align="left" width="90"><a href="'.$this->logo_link.'"><img src="'.JUri::base().'images/edms/images/beseated-logo.png"></a></td>
			                                  <td align="center" style="font-family:Arial, Helvetica, sans-serif;"></td>
			                                </tr>
			                            </table>
			                        </td>
			                    </tr>
			                    <tr>
			                    	<td colspan="2" height="20"></td>
			                    </tr>
			                    <tr>
			                        <td colspan="2" style="color:#000;">
			                        	<p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top: 0; Margin-bottom: 0;">Dear '.$username.',<br><br>
			                            Thank you for your feedback, one of our agents will contact you shortly.</p><br>
			                            <p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top: 0; Margin-bottom: 0;">This is an automated message; please do not reply to this email.</p><br>
			                            <p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top: 0; Margin-bottom: 0;">For further assistance, kindly contact us at <a href="mailto:contact@beseatedapp.com" style="color:#b18839; font-family:Arial, Helvetica, sans-serif; Margin-top: 0; Margin-bottom: 0; text-decoration:none;">contact@beseatedapp.com</a></p><br>
			                            <p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top: 0; Margin-bottom: 0;">Beseated Support.</p>
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
			                        	<a href="#" style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif; font-size:14px;">My Beseated ID</a>&nbsp;|&nbsp;<a style="text-decoration:none;font-size:14px; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="'.$this->support_link.'">Support</a>&nbsp;|&nbsp;<a style="text-decoration:none; font-size:14px; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="'.$this->privacy_policy_link.'">Privacy Policy</a>
			                        </td>
			                    </tr>
			                    <tr>
			                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000; font-size:14px;">Copyright © 2016 <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>. All rights reserved.</td>
			                    </tr>
			                    <tr>
			                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000; font-size:14px;">This email was sent by <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>, THUB, Dubai Silicon Oasis, Dubai, United Arab Emirates</td>
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
			</html>';

		$this->sendEmail($contactEmail,$subject,$body);
	}

	// Used in Private Jet booking
	public function jetBookingThankYouEmail($privateJetID,$personName,$email,$phone,$flight_date,$fromLocation,$toLocation,$totalGuest,$extraInformation,$returnFlightDate,$contactVia)
	{

		$lang         = JFactory::getLanguage();
		$extension    = 'com_beseated';
		$base_dir     = JPATH_SITE;
		$language_tag = 'en-GB';
		$reload       = true;
		$lang->load($extension, $base_dir, $language_tag, $reload);

		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');
		$tblPrivateJet          = JTable::getInstance('PrivateJet', 'BeseatedTable');

		$tblPrivateJet->load($privateJetID);
		$user = JFactory::getUser();
		$contactEmail = $email;
		$subject      = JText::_('COM_BESEATED_THANK_YOU_FOR_REQUEST_QUOTE_EMAIL_SUBJECT');


		$body         = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
						<html>
						<head>
						<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
						<title>Beseated</title>
						<style type="text/css" media="screen">
							p{margin: 0px !important; color: #000;}
							.ExternalClass p { color: #000; margin: 0px !important;}
							br{line-height: 12px;}
							.HOEnZb im p{margin:0;}
						</style>
						</head>
						<body>
						<table border="0" bgcolor="#FFFFFF" cellpadding="0" cellspacing="0" width="650" align="center" style="font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:20px;">
							<tbody>
						    	<tr>
						        	<td height="10"></td>
						        </tr>
						        <tr>
						        	<td align="center">
						            	<table width="630" border="0" cellpadding="0" cellspacing="0">
						                	<tr>
						                        <td align="right" colspan="2">
						                        	<table width="100%" border="0" cellpadding="0" cellspacing="0">
						                            	<tr>
						                                	<td align="left" width="90"><a href="'.$this->logo_link.'"><img src="'.JUri::base().'images/edms/images/beseated-logo.png"></a></td>
						                                    <td align="center" style="font-family:Arial, Helvetica, sans-serif;"></td>
						</td>
						                                </tr>
						                            </table>
						                        </td>
						                    </tr>
						                    <tr>
						                    	<td colspan="2" height="30"></td>
						                    </tr>
						                    <tr>
						                        <td colspan="2" style="color:#000;">
						                        	<p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top: 0; Margin-bottom: 0;">Dear '.$tblPrivateJet->company_name.',<br><br>
						                            '.$personName.' has requested a quote for the following query:
						</p><br>
						                        </td>
						                    </tr>
						                    <tr>
						                    	<td colspan="2" height="20">
						                        </td>
						                    </tr>
						                    <tr>
						                    	<td colspan="2">
						                        	<table width="100%" cellpadding="0" cellspacing="0" border="0" style="font-family:Arial, Helvetica, sans-serif; font-size:14px;">
						                            	<tr>
						                                	<td width="150" style="font-family:Arial, Helvetica, sans-serif; font-size:14px;">Name:</td>
						                                    <td style="font-family:Arial, Helvetica, sans-serif; font-size:14px;">'.$personName.'</td>
						                                </tr>
						                                <tr>
						                                	<td style="font-family:Arial, Helvetica, sans-serif; font-size:14px;">Email:</td>
						                                    <td style="font-family:Arial, Helvetica, sans-serif; font-size:14px;">'.$email.'</td>
						                                </tr>
						                                <tr>
						                                	<td style="font-family:Arial, Helvetica, sans-serif; font-size:14px;">Phone:</td>
						                                    <td style="font-family:Arial, Helvetica, sans-serif; font-size:14px;">'.$phone.'</td>
						                                </tr>
						                                <tr>
						                                	<td colspan="2" style="font-family:Arial, Helvetica, sans-serif; font-size:14px;" height="20"></td>
						                                </tr>
						                                <tr>
						                                	<td style="font-family:Arial, Helvetica, sans-serif; font-size:14px;">Date of Flight:</td>
						                                    <td style="font-family:Arial, Helvetica, sans-serif; font-size:14px;">'.$flight_date.' </td>
						                                </tr>
						                                <tr>
						                                	<td style="font-family:Arial, Helvetica, sans-serif; font-size:14px;">Return Flight Date:</td>
						                                    <td style="font-family:Arial, Helvetica, sans-serif; font-size:14px;">'.$returnFlightDate.' </td>
						                                </tr>
						                                <tr>
						                                	<td style="font-family:Arial, Helvetica, sans-serif; font-size:14px;">Flying from:</td>
						                                    <td style="font-family:Arial, Helvetica, sans-serif; font-size:14px;">'.$fromLocation.'</td>
						                                </tr>
						                                <tr>
						                                	<td style="font-family:Arial, Helvetica, sans-serif; font-size:14px;">Flying to:</td>
						                                    <td style="font-family:Arial, Helvetica, sans-serif; font-size:14px;">'.$toLocation.'</td>
						                                </tr>
						                                <tr>
						                                	<td style="font-family:Arial, Helvetica, sans-serif; font-size:14px;">Passengers:</td>
						                                    <td style="font-family:Arial, Helvetica, sans-serif; font-size:14px;">'.$totalGuest.' Pax</td>
						                                </tr>
						                                <tr>
						                                	<td style="font-family:Arial, Helvetica, sans-serif; font-size:14px;">Additional Info:</td>
						                                    <td style="font-family:Arial, Helvetica, sans-serif; font-size:14px;">'.$extraInformation.'</td>
						                                </tr>
						                                <tr>
						                                	<td style="font-family:Arial, Helvetica, sans-serif; font-size:14px;">Contact Via:</td>
						                                    <td style="font-family:Arial, Helvetica, sans-serif; font-size:14px;">'.$contactVia.'</td>
						                                </tr>
						                            </table>
						                        </td>
						                    </tr>
						                    <tr>
						                    	<td colspan="2" height="10">
						                        </td>
						                    </tr>
						                    <tr>
						                    	<td colspan="2">
						                        	<p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top: 0; Margin-bottom: 0;">Kindly contact '.$personName.' on our behalf and proceed with your usual procedures.</p><br>
						                            <p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top: 0; Margin-bottom: 0;"> Keep us updated on this booking, so that we can reward '.$personName.' with coins.</p><br>
						                        </td>
						                    </tr>

						                    <tr>
						                    	<td colspan="2"><p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top: 0; Margin-bottom: 0;">Beseated Support.</p></td>
						                    </tr>
						                    <tr>
						                    	<td colspan="2" height="30">
						                        </td>
						                    </tr>
						                    <tr>
						                    	<td colspan="2" height="8" style=" border-top:1px solid #c09439;"></td>
						                    </tr>
						                    <tr>
						                    	<td colspan="2" align="center" style="color:#5f5f5f;">
						                        	<a href="#" style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif; font-size:14px;">My Beseated ID</a>&nbsp;|&nbsp;<a style="text-decoration:none; font-size:14px; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="'.$this->support_link.'">Support</a>&nbsp;|&nbsp;<a style="text-decoration:none; font-size:14px; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="'.$this->privacy_policy_link.'">Privacy Policy</a>
						                        </td>
						                    </tr>
						                    <tr>
						                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000; font-size:14px;">Copyright © 2016 <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>. All rights reserved.</td>
						                    </tr>
						                    <tr>
						                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000; font-size:14px;">This email was sent by <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>, THUB, Dubai Silicon Oasis, Dubai, United Arab Emirates</td>
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

		self::sendEmail($tblPrivateJet->owner_email,$subject,$body);

		/*for ($i = 0; $i < count($adminEmails); $i++)
		{
			self::sendEmail($adminEmails[$i],$subject,$body);
		}*/

	}

	public function forgotPasswordEmail($userID,$newPassword)
	{
		$lang = JFactory::getLanguage();
		$extension = 'com_beseated';
		$base_dir = JPATH_SITE;
		$language_tag = 'en-GB';
		$reload = true;
		$lang->load($extension, $base_dir, $language_tag, $reload);

		$app           = JFactory::getApplication();
		$menu          = $app->getMenu();
		$menuItem      = $menu->getItems( 'link', 'index.php?option=com_users&view=login', true );
		$loginLink     = JUri::base()."index.php?option=com_users&view=login&Itemid=".$menuItem->id;
		$userDetail    = JFactory::getUser($userID);
		$emailSubject  = JText::sprintf(
								'COM_BESEATED_FORGOT_PASSWORD_EMAIL_SUBJECT'
							);

		$emailBody ='<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
					<html>
					<head>
					<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
					<title>Beseated</title>
					<style type="text/css" media="screen">
						p{margin: 0px !important; color: #000;}
						.ExternalClass p { color: #000; margin: 0px !important;}
						br{line-height: 12px;}
						.HOEnZb im p{margin:0;}
					</style>
					</head>
					<body>
					<table border="0" bgcolor="#FFFFFF" cellpadding="0" cellspacing="0" width="650" align="center" style="font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:20px;">
						<tbody>
					    	<tr>
					        	<td height="10"></td>
					        </tr>
					        <tr>
					        	<td align="center">
					            	<table width="630" border="0" cellpadding="0" cellspacing="0">
					                	<tr>
					                        <td align="right" colspan="2">
					                        	<table width="100%" border="0" cellpadding="0" cellspacing="0">
					                            	<tr>
					                                	<td align="left" width="90"><a href="'.$this->logo_link.'"><img src="'.JUri::base().'images/edms/images/beseated-logo.png"></a></td>
					                                  <td align="center" style="font-family:Arial, Helvetica, sans-serif;"></td>
					                                </tr>
					                            </table>
					                        </td>
					                    </tr>
					                    <tr>
					                    	<td colspan="2" height="20"></td>
					                    </tr>
					                    <tr>
					                        <td colspan="2" style="color:#000;">
					                        	<p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top:0;Margin-bottom:0;">Dear '.$userDetail->name.',<br><br>
					                            The password for your Beseated ID '.$userDetail->email.' has been successfully reset.</p><br>
					                            <p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top:0;Margin-bottom:0;">Your new password: '.$newPassword.'</p><br>
					                            <p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top:0;Margin-bottom:0;">Please login using this password, then go to your profile, and set a new password.</p><br>
					                            <p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top:0;Margin-bottom:0;">If you believe you have received this email in error, or that an unauthorized person has accessed your account, please <a href="'.$loginLink.'">click Here </a>to reset your password immediately.</p><br>
					                            <p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top:0;Margin-bottom:0;">Questions? There are lots of answers on our <a href="'.$this->faq_link.'" style="text-decoration:none; color:#b18839;">FAQ</a>&rsquo;s, your question might be already answered.</p><br>
					                            <p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top:0;Margin-bottom:0;">If you are still having problems, please contact us at <a href="mailto:contact@beseatedapp.com" style="text-decoration:none; color:#b18839;">contact@beseatedapp.com</a></p><br>
					                            <p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top:0;Margin-bottom:0;">Beseated Support.</p>
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
					                        	<a href="#" style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif; font-size:14px;">My Beseated ID</a>&nbsp;|&nbsp;<a style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif; font-size:14px;" href="'.$this->support_link.'">Support</a>&nbsp;|&nbsp;<a style="text-decoration:none; font-size:14px; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="'.$this->privacy_policy_link.'">Privacy Policy</a>
					                        </td>
					                    </tr>
					                    <tr>
					                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000; font-size:14px;">Copyright © 2016 <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>. All rights reserved.</td>
					                    </tr>
					                    <tr>
					                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000; font-size:14px;">This email was sent by <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>, THUB, Dubai Silicon Oasis, Dubai, United Arab Emirates</td>
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
					</html>';

		$this->sendEmail($userDetail->email,$emailSubject, $emailBody);
	}

	public function venueBookingStatusChangedEmail($status,$bookingID)
	{
		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_bcted/tables');
		$tblVenuebooking      = JTable::getInstance('Venuebooking', 'BctedTable');
		$tblVenue             = JTable::getInstance('Venue', 'BctedTable');
		$tblTable             = JTable::getInstance('Table', 'BctedTable');
		$tblVenuebooking->load($bookingID);
		$tblVenue->load($tblVenuebooking->venue_id);
		$tblTable->load($tblVenuebooking->venue_table_id);

		$userProfile = $this->helper->getUserProfile($tblVenuebooking->user_id);
		$joomlaUser  = JFactory::getUser($tblVenuebooking->user_id);
		$email       = $joomlaUser->email;
		$venueName   = $tblVenue->venue_name;

		if($tblTable->premium_table_id)
			$tableName = $tblTable->venue_table_name;
		else
			$tableName = $tblTable->custom_table_name;

		$bookingDate = date('d-m-Y',strtotime($tblVenuebooking->venue_booking_datetime));
		$bookingTime = $this->helper->convertToHM($tblVenuebooking->booking_from_time);

		if($status == 'ok')
		{
			$subject = JText::_('COM_BESEATED_VENUE_BOOKING_STATUS_CHANGED_ACCEPTED_EMAIL_SUBJECT');
			$body    = JText::sprintf('COM_BESEATED_VENUE_BOOKING_STATUS_CHANGED_ACCEPTED_EMAIL_BODY',$joomlaUser->name, $venueName, $tableName, $bookingDate, $bookingTime, $this->imageLink);

			$this->sendEmail($email,$subject,$body);
		}
		else if($status == 'cancel')
		{
			$subject     = JText::_('COM_BESEATED_VENUE_BOOKING_STATUS_CHANGED_DECLINED_EMAIL_SUBJECT');
			$body        = JText::sprintf('COM_BESEATED_VENUE_BOOKING_STATUS_CHANGED_DECLINED_EMAIL_BODY',$joomlaUser->name, $venueName, $tableName, $bookingDate, $bookingTime, $this->imageLink);

			$this->sendEmail($email,$subject,$body);
		}
		else if($status == 'waiting')
		{
			$subject     = JText::_('COM_BESEATED_VENUE_BOOKING_STATUS_CHANGED_WAITINGLIST_EMAIL_SUBJECT');
			$body        = JText::sprintf('COM_BESEATED_VENUE_BOOKING_STATUS_CHANGED_WAITINGLIST_EMAIL_BODY',$joomlaUser->name, $tableName, $venueName, $this->imageLink);

			$this->sendEmail($email,$subject,$body);
		}
	}

	public function packageBookingStatusChangedEmail($status,$bookingID)
	{
		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_bcted/tables');
		$tblPackagePurchased = JTable::getInstance('PackagePurchased', 'BctedTable');
		$tblPackagePurchased->load($bookingID);
		$tblVenue             = JTable::getInstance('Venue', 'BctedTable');
		$tblVenue->load($tblPackagePurchased->venue_id);
		$tblPackage             = JTable::getInstance('Package', 'BctedTable');
		$tblPackage->load($tblPackagePurchased->package_id);

		if($status == 'Accept')
		{
			$joomlaUser  = JFactory::getUser($tblPackagePurchased->user_id);
			$email       = $joomlaUser->email;
			$subject     = JText::_('COM_BESEATED_VENUE_PACKAGE_BOOKING_STATUS_CHANGED_ACCEPTED_EMAIL_SUBJECT');
			$venueName   = $tblVenue->venue_name;
			$packageName = $tblPackage->package_name;
			$bookingDate = date('d-m-Y',strtotime($tblPackagePurchased->package_datetime));
			$bookingTime =  $this->helper->convertToHM($tblPackagePurchased->package_time);
			$body        = JText::sprintf('COM_BESEATED_VENUE_PACKAGE_BOOKING_STATUS_CHANGED_ACCEPTED_EMAIL_BODY',$joomlaUser->name, $packageName, $packageName, $bookingDate, $bookingTime, $this->imageLink);
		}
		else
		{
			$joomlaUser  = JFactory::getUser($tblPackagePurchased->user_id);
			$email       = $joomlaUser->email;
			$subject     = JText::_('COM_BESEATED_VENUE_PACKAGE_BOOKING_STATUS_CHANGED_DECLINED_EMAIL_SUBJECT');
			$venueName   = $tblVenue->venue_name;
			$packageName = $tblPackage->package_name;
			$bookingDate = date('d-m-Y',strtotime($tblPackagePurchased->package_datetime));
			$bookingTime =  $this->helper->convertToHM($tblPackagePurchased->package_time);
			$body        = JText::sprintf('COM_BESEATED_VENUE_PACKAGE_BOOKING_STATUS_CHANGED_DECLINED_EMAIL_BODY',$joomlaUser->name, $packageName, $packageName, $bookingDate, $bookingTime, $this->imageLink);
		}

		$this->sendEmail($email,$subject,$body);
	}

	public function companyBookingStatusChangedEmail($status,$bookingID)
	{
		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_bcted/tables');
		$tblServiceBooking = JTable::getInstance('Servicebooking','BctedTable',array());
		$tblCompany        = JTable::getInstance('Company','BctedTable',array());
		$tblService        = JTable::getInstance('Service','BctedTable',array());
		$tblServiceBooking->load($bookingID);
		$tblCompany->load($tblServiceBooking->company_id);
		$tblService->load($tblServiceBooking->service_id);

		if($status == 'Accept')
		{
			$joomlaUser  = JFactory::getUser($tblServiceBooking->user_id);
			$email       = $joomlaUser->email;
			$subject     = JText::_('COM_BESEATED_COMPANY_BOOKING_STATUS_CHANGED_ACCEPTED_EMAIL_SUBJECT');
			$companyName = $tblCompany->company_name;
			$serviceName = $tblService->service_name;
			$bookingDate = date('d-m-Y',strtotime($tblServiceBooking->service_booking_datetime));
			$bookingFromTime = $this->helper->convertToHM($tblServiceBooking->booking_from_time);
			$bookingToTime = $this->helper->convertToHM($tblServiceBooking->booking_to_time);
			$body        = JText::sprintf('COM_BESEATED_COMPANY_BOOKING_STATUS_CHANGED_ACCEPTED_EMAIL_BODY',$joomlaUser->name, $companyName, $bookingDate, $bookingFromTime,$bookingToTime, $this->imageLink);
		}
		else
		{
			$joomlaUser  = JFactory::getUser($tblServiceBooking->user_id);
			$email       = $joomlaUser->email;
			$subject     = JText::_('COM_BESEATED_COMPANY_BOOKING_STATUS_CHANGED_DECLINED_EMAIL_SUBJECT');
			$companyName = $tblCompany->company_name;
			$serviceName = $tblService->service_name;
			$bookingDate = date('d-m-Y',strtotime($tblServiceBooking->service_booking_datetime));
			$bookingFromTime = $this->helper->convertToHM($tblServiceBooking->booking_from_time);
			$bookingToTime = $this->helper->convertToHM($tblServiceBooking->booking_to_time);
			$imgPath     = JUri::base().'images/email-footer-logo.png';
			$imageLink   = '<img src="'.$imgPath.'" alt="Beesated"/>';
			$body        = JText::sprintf('COM_BESEATED_COMPANY_BOOKING_STATUS_CHANGED_DECLINED_EMAIL_BODY',$joomlaUser->name, $companyName, $bookingDate, $bookingFromTime,$bookingToTime, $this->imageLink);
		}

		$this->sendEmail($email,$subject,$body);
	}

	public function requestToJoinTableEmail($inviteID)
	{
		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_bcted/tables');
		$tblVenuetableinvite  = JTable::getInstance('Venuetableinvite','BctedTable');
		$tblVenuebooking      = JTable::getInstance('Venuebooking', 'BctedTable');
		$tblVenue             = JTable::getInstance('Venue', 'BctedTable');
		$tblTable             = JTable::getInstance('Table', 'BctedTable');


		$tblVenuetableinvite->load($inviteID);
		$tblVenuebooking->load($tblVenuetableinvite->booking_id);
		$tblVenue->load($tblVenuebooking->venue_id);
		$tblTable->load($tblVenuebooking->venue_table_id);

		$loginUserProfile = $this->helper->getUserProfile($tblVenuetableinvite->user_id);
		$userFLName       = $loginUserProfile->first_name . ' ' . $loginUserProfile->last_name;

		if(empty($userFLName))
		{
			$userFLName = $user->name;
		}

		$emailSubject       = JText::sprintf('COM_BESEATED_VENUE_ADD_ME_REQUEST_EMAIL_SUBJECT',$userFLName);
		$bookingJUser       = JFactory::getUser($tblVenuebooking->user_id);
		$bookingUserProfile = $this->helper->getUserProfile($tblVenuebooking->user_id);
		$bookingUserName    = $bookingUserProfile->first_name . ' ' . $bookingUserProfile->last_name;
		$venueName          = $tblVenue->venue_name;
		$bookingDate        = date('d-m-Y',strtotime($tblVenuebooking->venue_booking_datetime));
		$bookingTime        = $this->helper->convertToHM($tblVenuebooking->booking_from_time);


		$emailBody = JText::sprintf('COM_BESEATED_VENUE_ADD_ME_REQUEST_EMAIL_BODY',$bookingUserName,$userFLName,$tableName,$venueName,$bookingDate,$bookingTime,$this->imageLink);

		$emailID = $bookingJUser->email;

		 $this->sendEmail($emailID,$emailSubject,$emailBody);
	}

	public function actionOnAddMeRequest($action, $inviteID)
	{
		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_bcted/tables');
		$tblVenuetableinvite  = JTable::getInstance('Venuetableinvite','BctedTable');
		$tblVenuebooking      = JTable::getInstance('Venuebooking', 'BctedTable');
		$tblVenue             = JTable::getInstance('Venue', 'BctedTable');
		$tblTable             = JTable::getInstance('Table', 'BctedTable');

		$tblVenuetableinvite->load($inviteID);
		$tblVenuebooking->load($tblVenuetableinvite->booking_id);
		$tblVenue->load($tblVenuebooking->venue_id);
		$tblTable->load($tblVenuebooking->venue_table_id);

		if($tblTable->premium_table_id)
		{
			$tableName = $tblTable->venue_table_name;
		}
		else
		{
			$tableName = $tblTable->custom_table_name;
		}

		$bookingUser        = JFactory::getUser($tblVenuebooking->user_id);
		$bookingUserProfile = $this->helper->getUserProfile($bookingUser->id);
		$bookingUserFLName  = $bookingUserProfile->first_name . ' ' . $bookingUserProfile->last_name;
		if(empty($bookingUserFLName))
		{
			$bookingUserFLName = $bookingUser->name;
		}

		$inviteUser = JFactory::getUser($tblVenuetableinvite->user_id);
		$inviteUserProfile = $this->helper->getUserProfile($inviteUser->id);
		$inviteUserFLName = $inviteUserProfile->first_name . ' ' . $inviteUserProfile->last_name;

		if(empty($inviteUserFLName))
		{
			$inviteUserFLName = $inviteUser->name;
		}


		$venueName          = $tblVenue->venue_name;
		$bookingDate        = date('d-m-Y',strtotime($tblVenuebooking->venue_booking_datetime));
		$bookingTime        = $this->helper->convertToHM($tblVenuebooking->booking_from_time);

		if($action == 'accept')
		{
			$emailSubject = JText::sprintf('COM_BESEATED_VENUE_TABLE_ADDME_REQUEST_ACCEPTED_EMAIL_SUBJECT',$bookingUserFLName);
			$emailBody = JText::sprintf('COM_BESEATED_VENUE_TABLE_ADDME_REQUEST_ACCEPTED_EMAIL_BODY',$inviteUserFLName,$bookingUserFLName,$tableName,$bookingDate,$bookingTime,$this->imageLink);
		}
		else if($action == 'reject')
		{
			$emailSubject = JText::sprintf('COM_BESEATED_VENUE_TABLE_ADDME_REQUEST_REJECTED_EMAIL_SUBJECT',$bookingUserFLName);
			$emailBody = JText::sprintf('COM_BESEATED_VENUE_TABLE_ADDME_REQUEST_REJECTED_EMAIL_BODY',$inviteUserFLName,$bookingUserFLName,$tableName,$bookingDate,$bookingTime,$this->imageLink);
		}

		$emailID = $inviteUser->email;
		$this->sendEmail($emailID,$emailSubject,$emailBody);

	}


	public function sendEmail($email,$subject,$body)
	{
		//$email = 'protection.new@hotmail.com';
		//$email = 'web-m6y6xi@mail-tester.com';

		// Initialise variables.
		$app     = JFactory::getApplication();
		$config  = JFactory::getConfig();

		$site    = $config->get('sitename');
		$from    = $config->get('mailfrom');
		$sender  = $config->get('fromname');

		// Clean the email data.
		$sender  = JMailHelper::cleanAddress($sender);
		$subject = JMailHelper::cleanSubject($subject);
		$body    = JMailHelper::cleanBody($body);

		// Send the email.
		$return = JFactory::getMailer()->sendMail($from, $sender, $email, $subject, $body,true);

		//echo "<pre>";print_r($return);echo "</pre>";exit;

		//$return = 1;
		// Check for an error.
		if ($return !== true)
		{
			return new JException(JText::_('COM__SEND_MAIL_FAILED'), 500);
		}
	}

	public function updatePassword($userID,$newPassword)
	{
		$lang = JFactory::getLanguage();
		$extension = 'com_beseated';
		$base_dir = JPATH_SITE;
		$language_tag = 'en-GB';
		$reload = true;
		$lang->load($extension, $base_dir, $language_tag, $reload);


		$app           = JFactory::getApplication();
		$menu          = $app->getMenu();
		$menuItem      = $menu->getItems( 'link', 'index.php?option=com_users&view=login', true );
		$loginLink     = JUri::base()."index.php?option=com_users&view=login&Itemid=".$menuItem->id;
		$userDetail    = JFactory::getUser($userID);
		$emailSubject  = JText::sprintf(
								'COM_BESEATED_UPDATE_PASSWORD_EMAIL_SUBJECT'
							);

		$emailBody ='<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
					<html>
					<head>
					<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
					<title>Beseated</title>
					<style type="text/css" media="screen">
						p{margin: 0px !important; color: #000;}
						.ExternalClass p { color: #000; margin: 0px !important;}
						br{line-height: 12px;}
						.HOEnZb im p{margin:0;}
					</style>
					</head>
					<body>
					<table border="0" bgcolor="#FFFFFF" cellpadding="0" cellspacing="0" width="650" align="center" style="font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:20px;">
						<tbody>
					    	<tr>
					        	<td height="10"></td>
					        </tr>
					        <tr>
					        	<td align="center">
					            	<table width="630" border="0" cellpadding="0" cellspacing="0">
					                	<tr>
					                        <td align="right" colspan="2">
					                        	<table width="100%" border="0" cellpadding="0" cellspacing="0">
					                            	<tr>
					                                	<td align="left" width="90"><a href="'.$this->logo_link.'"><img src="'.JUri::base().'images/edms/images/beseated-logo.png"></a></td>
					                                    <td align="center" style="font-family:Arial, Helvetica, sans-serif;"></td>
					                                </tr>
					                            </table>
					                        </td>
					                    </tr>
					                    <tr>
					                    	<td colspan="2" height="20"></td>
					                    </tr>
					                    <tr>
					                        <td colspan="2" style="color:#000;">
					                        	<p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top: 0; Margin-bottom: 0;">Dear '.$userDetail->name.',<br><br>
					                            Your Beseated password has been successfully changed.</p><br>
					                            <p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top: 0; Margin-bottom: 0;">If you do not recognize this action and that an unauthorized person has accessed your account,<br>please click <a href="'.$loginLink.'" style="color:#b58b39; text-decoration:none;">here</a> to reset your password immediately.</p><br>
					                            <p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top: 0; Margin-bottom: 0;">Beseated Support.</p>
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
					                        	<a href="#" style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif; font-size:14px;">My Beseated ID</a>&nbsp;|&nbsp;<a style="text-decoration:none; color:#c09439; font-size:14px; font-family:Arial, Helvetica, sans-serif;" href="'.$this->support_link.'">Support</a>&nbsp;|&nbsp;<a style="text-decoration:none; font-size:14px; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="'.$this->privacy_policy_link.'">Privacy Policy</a>
					                        </td>
					                    </tr>
					                    <tr>
					                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000; font-size:14px;">Copyright © 2016 <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>. All rights reserved.</td>
					                    </tr>
					                    <tr>
					                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000; font-size:14px;">This email was sent by <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>, THUB, Dubai Silicon Oasis, Dubai, United Arab Emirates</td>
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

		$this->sendEmail($userDetail->email,$emailSubject, $emailBody);
	}

	public function registration($userName,$email,$activationLink)
	{

		$lang = JFactory::getLanguage();
		$extension = 'com_beseated';
		$base_dir = JPATH_SITE;
		$language_tag = 'en-GB';
		$reload = true;
		$lang->load($extension, $base_dir, $language_tag, $reload);


		$app           = JFactory::getApplication();
		$menu          = $app->getMenu();
		$menuItem      = $menu->getItems( 'link', 'index.php?option=com_users&view=login', true );
		$loginLink     = JUri::base()."index.php?option=com_users&view=login&Itemid=".$menuItem->id;
		//$userDetail    = JFactory::getUser($userID);
		$emailSubject  = JText::sprintf(
								'COM_BESEATED_REGISTRATION_EMAIL_SUBJECT'
							);

		$emailBody ='<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
					<html>
					<head>
					<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
					<title>Beseated</title>
					<style type="text/css" media="screen">
						p{margin: 0px !important; color: #000;}
						.ExternalClass p { color: #000; margin: 0px !important;}
						br{line-height: 12px;}
						.HOEnZb im p{margin:0;}
					</style>
					</head>
					<body>
					<table border="0" bgcolor="#FFFFFF" cellpadding="0" cellspacing="0" width="650" align="center" style="font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:20px;">
						<tbody>
					    	<tr>
					        	<td height="10"></td>
					        </tr>
					        <tr>
					        	<td align="center">
					            	<table width="630" border="0" cellpadding="0" cellspacing="0">
					                	<tr>
					                        <td align="right" colspan="2">
					                        	<table width="100%" border="0" cellpadding="0" cellspacing="0">
					                            	<tr>
					                                	<td align="left" width="90"><a href="'.$this->logo_link.'"><img src="'.JUri::base().'images/edms/images/beseated-logo.png"></a></td>
					                                    <td align="center" style="font-family:Arial, Helvetica, sans-serif;"></td>
					                                </tr>
					                            </table>
					                        </td>
					                    </tr>
					                    <tr>
					                    	<td colspan="2" height="20"></td>
					                    </tr>
					                    <tr>
					                        <td colspan="2" style="color:#000;">

					                        	<p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top: 0; Margin-bottom: 0;">Dear '.$userName.',<br><br>
					                            Welcome to Beseated, Luxury bookings for the Elite.</p><br>
					                            <p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top: 0; Margin-bottom: 0;"><a href="'.$activationLink.'" style="color:#b18839; font-family:Arial, Helvetica, sans-serif; Margin-top: 0; Margin-bottom: 0; text-decoration:none;">Activate Now</a></p><br>

					                            <p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top: 0; Margin-bottom: 0;">Why you received this email?</p><br>

					                            <p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top: 0; Margin-bottom: 0;">Beseated requests verification whenever an Elite joins Beseated.</p><br>
					                            <p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top: 0; Margin-bottom: 0;">Your Beseated account cannot be used until you verify it.</p><br><br>

					                            <p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top: 0; Margin-bottom: 0;">Beseated Support.</p>
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
					                    	<td colspan="2" align="center" style="color:#5f5f5f; font-size:14px;">
					                        	<a href="#" style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif; font-size:14px;">My Beseated ID</a>&nbsp;|&nbsp;<a style="text-decoration:none; color:#c09439;font-size:14px;font-family:Arial, Helvetica, sans-serif;" href="'.$this->support_link.'">Support</a>&nbsp;|&nbsp;<a style="text-decoration:none; font-size:14px; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="'.$this->privacy_policy_link.'">Privacy Policy</a>
					                        </td>
					                    </tr>
					                    <tr>
					                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000;font-size:14px;">Copyright © 2016 <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>. All rights reserved.</td>
					                    </tr>
					                    <tr>
					                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000;font-size:14px;">This email was sent by <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>, THUB, Dubai Silicon Oasis, Dubai, United Arab Emirates</td>
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

		$this->sendEmail($email,$emailSubject, $emailBody);
	}

	public function rewardBookingMail($userName,$email)
	{
		//echo "<pre/>";print_r($email);exit;

		$lang = JFactory::getLanguage();
		$extension = 'com_beseated';
		$base_dir = JPATH_SITE;
		$language_tag = 'en-GB';
		$reload = true;
		$lang->load($extension, $base_dir, $language_tag, $reload);


		$app           = JFactory::getApplication();
		$menu          = $app->getMenu();
		$menuItem      = $menu->getItems( 'link', 'index.php?option=com_users&view=login', true );
		$loginLink     = JUri::base()."index.php?option=com_users&view=login&Itemid=".$menuItem->id;
		//$userDetail    = JFactory::getUser($userID);
		$emailSubject  = JText::sprintf(
								'COM_BESEATED_REWARD_BOOKING_EMAIL_SUBJECT'
							);

		$emailBody ='<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
					<html>
					<head>
					<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
					<title>Beseated</title>
					<style type="text/css" media="screen">
						p{margin: 0px !important; color: #000;}
						.ExternalClass p { color: #000; margin: 0px !important;}
						br{line-height: 12px;}
						.HOEnZb im p{margin:0;}
					</style>
					</head>
					<body>
					<table border="0" bgcolor="#FFFFFF" cellpadding="0" cellspacing="0" width="650" align="center" style="font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:20px;">
						<tbody>
					    	<tr>
					        	<td height="10"></td>
					        </tr>
					        <tr>
					        	<td align="center">
					            	<table width="630" border="0" cellpadding="0" cellspacing="0">
					                	<tr>
					                        <td align="right" colspan="2">
					                        	<table width="100%" border="0" cellpadding="0" cellspacing="0">
					                            	<tr>
					                                	<td align="left" width="90"><a href="'.$this->logo_link.'"><img src="'.JUri::base().'images/edms/images/beseated-logo.png"></a></td>
					                                  <td align="center" style="font-family:Arial, Helvetica, sans-serif;"></td>
					</td>
					                                </tr>
					                            </table>
					                        </td>
					                    </tr>
					                    <tr>
					                    	<td colspan="2" height="20"></td>
					                    </tr>
					                    <tr>
					                        <td colspan="2" style="color:#000;">
					                        	<p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top: 0; Margin-bottom: 0;">Dear '.$userName.',<br><br>
					                            Congratulations on redeeming your reward at the Beseated shop.</p><br>
					                            <p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top: 0; Margin-bottom: 0;">We are constantly changing the rewards, so make sure you always check our new rewards.</p><br>
					                            <p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top: 0; Margin-bottom: 0;">Hope to see you soon again.</p><br>
					                            <p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top: 0; Margin-bottom: 0;">Beseated Support.</p>
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
					                    	<td colspan="2" align="center" style="color:#5f5f5f; font-size:14px;">
					                        	<a href="#" style="text-decoration:none; color:#c09439;font-size:14px; font-family:Arial, Helvetica, sans-serif;">My Beseated ID</a>&nbsp;|&nbsp;<a style="text-decoration:none; color:#c09439;font-size:14px; font-family:Arial, Helvetica, sans-serif;" href="'.$this->support_link.'">Support</a>&nbsp;|&nbsp;<a style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif; font-size:14px;" href="'.$this->privacy_policy_link.'">Privacy Policy</a>
					                        </td>
					                    </tr>
					                    <tr>
					                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000; font-size:14px;">Copyright © 2016 <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>. All rights reserved.</td>
					                    </tr>
					                    <tr>
					                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000; font-size:14px;">This email was sent by <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>, THUB, Dubai Silicon Oasis, Dubai, United Arab Emirates</td>
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
					</html>';

		$this->sendEmail($email,$emailSubject, $emailBody);
	}

	public function referedFriendMail($refByUserName,$email,$points,$referedUser)
	{

		//echo "<pre/>";print_r(JUri::base());exit;

		$lang = JFactory::getLanguage();
		$extension = 'com_beseated';
		$base_dir = JPATH_SITE;
		$language_tag = 'en-GB';
		$reload = true;
		$lang->load($extension, $base_dir, $language_tag, $reload);


		$app           = JFactory::getApplication();
		$menu          = $app->getMenu();
		$menuItem      = $menu->getItems( 'link', 'index.php?option=com_users&view=login', true );
		$loginLink     = JUri::base()."index.php?option=com_users&view=login&Itemid=".$menuItem->id;
		//$userDetail    = JFactory::getUser($userID);
		$emailSubject  = JText::sprintf(
								'COM_BESEATED_REFERRAL_POINT_USER_FIRST_PURCHASE_EMAIL_SUBJECT'
							);

		$emailBody        = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
							 <html>
									<head>
									<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
									<title>Beseated</title>
									<style type="text/css" media="screen">
										p{margin: 0px !important; color: #000;}
										.ExternalClass p { color: #000; margin: 0px !important;}
										br{line-height: 12px;}
										.HOEnZb im p{margin:0;}
									</style>
									</head>
									<body>
									<table border="0" bgcolor="#FFFFFF" cellpadding="0" cellspacing="0" width="650" align="center" style="font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:20px;">
										<tbody>
									    	<tr>
									        	<td height="10"></td>
									        </tr>
									        <tr>
									        	<td align="center">
									            	<table width="630" border="0" cellpadding="0" cellspacing="0">
									                	<tr>
									                        <td align="right" colspan="2">
									                        	<table width="100%" border="0" cellpadding="0" cellspacing="0">
									                            	<tr>
									                                	<td align="left" width="90"><a href="'.$this->logo_link.'"><img src="'.JUri::base().'images/edms/images/beseated-logo.png"></a></td>
									                                  <td align="center" style="font-family:Arial, Helvetica, sans-serif;"></td>
									                                </tr>
									                            </table>
									                        </td>
									                    </tr>
									                    <tr>
									                    	<td colspan="2" height="20"></td>
									                    </tr>
									                    <tr>
									                        <td colspan="2" style="color:#000;">
									                        	<p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top: 0; Margin-bottom: 0;">Dear '.$refByUserName.',<br><br>
									                            CONGRATULATIONS</p><br>
									                            <p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top: 0; Margin-bottom: 0;">You have earned &nbsp;<img src="'.JUri::base().'images/edms/images/money-icn.png" width="21" height="21" style="vertical-align:middle;">&nbsp; '.$points.' for referring '.$referedUser.'.</p><br>
									                            <p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top: 0; Margin-bottom: 0;">Don’t we all love to Be Treated! </p><br>
									                            <p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top: 0; Margin-bottom: 0;">Beseated Support.</p>
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
									                    	<td colspan="2" align="center" style="color:#5f5f5f; font-size:14px;">
									                        	<a href="#" style="text-decoration:none; color:#c09439; font-size:14px; font-family:Arial, Helvetica, sans-serif;">My Beseated ID</a>&nbsp;|&nbsp;<a style="text-decoration:none; color:#c09439; font-size:14px; font-family:Arial, Helvetica, sans-serif;" href="'.$this->support_link.'">Support</a>&nbsp;|&nbsp;<a style="text-decoration:none; font-size:14px; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="'.$this->privacy_policy_link.'">Privacy Policy</a>
									                        </td>
									                    </tr>
									                    <tr>
									                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000; font-size:14px;">Copyright © 2016 <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>. All rights reserved.</td>
									                    </tr>
									                    <tr>
									                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000; font-size:14px;">This email was sent by <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>, THUB, Dubai Silicon Oasis, Dubai, United Arab Emirates</td>
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

		self::sendEmail($email,$emailSubject, $emailBody);
	}


	public function beseatedInvitationMail($email,$inviteename)
	{
		$lang         = JFactory::getLanguage();
		$extension    = 'com_beseated';
		$base_dir     = JPATH_SITE;
		$language_tag = 'en-GB';
		$reload       = true;
		$lang->load($extension, $base_dir, $language_tag, $reload);

		$app                = JFactory::getApplication();
		$menu               = $app->getMenu();
		$menuItem           = $menu->getItems( 'link', 'index.php?option=com_beseated&view=registration', true );
		$registrationItemid = $menuItem->id;
		$link               = JUri::base().'index.php?option=com_users&view=registration&Itemid='.$registrationItemid;
		//$userDetail    = JFactory::getUser($userID);
		$emailSubject  = JText::sprintf(
								'COM_BESEATED_INVITED_TO_JOIN_BESEATED_EMAIL_SUBJECT'
							);

		$emailBody        = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
							 <html>
							<head>
							<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
							<title>Beseated</title>
							<style type="text/css" media="screen">
								p{margin: 0px !important; color: #000;}
								.ExternalClass p { color: #000; margin: 0px !important;}
								br{line-height: 12px;}
								.HOEnZb im p{margin:0;}
							</style>
							</head>
							<body>
							<table border="0" bgcolor="#FFFFFF" cellpadding="0" cellspacing="0" width="650" align="center" style="font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:20px;">
								<tbody>
							    	<tr>
							        	<td height="10"></td>
							        </tr>
							        <tr>
							        	<td align="center">
							            	<table width="630" border="0" cellpadding="0" cellspacing="0">
							                	<tr>
							                        <td align="right" colspan="2">
							                        	<table width="100%" border="0" cellpadding="0" cellspacing="0">
							                            	<tr>
							                                	<td align="left" width="90"><a href="'.$this->logo_link.'"><img src="'.JUri::base().'images/edms/images/beseated-logo.png"></a></td>
							                                    <td align="center" style="font-family:Arial, Helvetica, sans-serif;"></td>
							                                </tr>
							                            </table>
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20"></td>
							                    </tr>
							                    <tr>
							                        <td colspan="2" style="color:#000;">
							                        	<p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top: 0; Margin-bottom: 0;">Dear '.$email.',<br><br>
							                            '.$inviteename.' has invited you to join Beseated.</p><br>
							                            <p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top: 0; Margin-bottom: 0;">Beseated, Luxury Bookings for the Elite.</p><br>
							                            <p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top: 0; Margin-bottom: 0;">Click <a href="'.$link.'" style="color:#b18839; font-family:Arial, Helvetica, sans-serif; Margin-top: 0; Margin-bottom: 0; text-decoration:none;">here</a> to sign up</p><br>
							                            <p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top: 0; Margin-bottom: 0;">Beseated Support.</p>
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
							                    	<td colspan="2" align="center" style="color:#5f5f5f; font-size:14px;">
							                        	<a href="#" style="text-decoration:none; color:#c09439; font-size:14px; font-family:Arial, Helvetica, sans-serif;">My Beseated ID</a>&nbsp;|&nbsp;<a style="text-decoration:none; color:#c09439;font-size:14px; font-family:Arial, Helvetica, sans-serif;" href="'.$this->support_link.'">Support</a>&nbsp;|&nbsp;<a style="text-decoration:none; font-size:14px; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="'.$this->privacy_policy_link.'">Privacy Policy</a>
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000; font-size:14px;">Copyright © 2016 <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>. All rights reserved.</td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000; font-size:14px;">This email was sent by <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>, THUB, Dubai Silicon Oasis, Dubai, United Arab Emirates</td>
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
							</html>';


		self::sendEmail($email,$emailSubject, $emailBody);

	}

	public function bookingUserReviewRatingMail($userName,$email)
	{
		$lang = JFactory::getLanguage();
		$extension = 'com_beseated';
		$base_dir = JPATH_SITE;
		$language_tag = 'en-GB';
		$reload = true;
		$lang->load($extension, $base_dir, $language_tag, $reload);


		$app           = JFactory::getApplication();
		$menu          = $app->getMenu();
		$menuItem      = $menu->getItems( 'link', 'index.php?option=com_users&view=login', true );
		$loginLink     = JUri::base()."index.php?option=com_users&view=login&Itemid=".$menuItem->id;
		//$userDetail    = JFactory::getUser($userID);
		$emailSubject  = JText::sprintf(
								'COM_BESEATED_BOOKING_REVIEW_RATING_EMAIL_SUBJECT'
							);

		$emailBody        = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
							 <html>
								<head>
								<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
								<title>Beseated</title>
								<style type="text/css" media="screen">
									p{margin: 0px !important; color: #000;}
									.ExternalClass p { color: #000; margin: 0px !important;}
									br{line-height: 12px;}
									.HOEnZb im p{margin:0;}
								</style>
								</head>
								<body>
								<table border="0" bgcolor="#FFFFFF" cellpadding="0" cellspacing="0" width="650" align="center" style="font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:20px;">
									<tbody>
								    	<tr>
								        	<td height="10"></td>
								        </tr>
								        <tr>
								        	<td align="center">
								            	<table width="630" border="0" cellpadding="0" cellspacing="0">
								                	<tr>
								                        <td align="right" colspan="2">
								                        	<table width="100%" border="0" cellpadding="0" cellspacing="0">
								                            	<tr>
								                                	<td align="left" width="90"><a href="'.$this->logo_link.'"><img src="'.JUri::base().'images/edms/images/beseated-logo.png"></a></td>
								                                  <td align="center" style="font-family:Arial, Helvetica, sans-serif;"></td>
								                                </tr>
								                            </table>
								                        </td>
								                    </tr>
								                    <tr>
								                    	<td colspan="2" height="20"></td>
								                    </tr>
								                    <tr>
								                        <td colspan="2" style="color:#000;">
								                        	<p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top: 0; Margin-bottom: 0;">Dear '.$userName.',<br><br>
								                            Thank you for your recent review.</p><br>
								                            <p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top: 0; Margin-bottom: 0;">Your voice is always heard.</p><br>
								                            <p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top: 0; Margin-bottom: 0;">We will forward your review to the concerned party.</p><br>
								                            <p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top: 0; Margin-bottom: 0;">Beseated Support.</p>
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
								                    	<td colspan="2" align="center" style="color:#5f5f5f; font-size:14px;">
								                        	<a href="#" style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif; font-size:14px;">My Beseated ID</a>&nbsp;|&nbsp;<a style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif; font-size:14px;" href="'.$this->support_link.'">Support</a>&nbsp;|&nbsp;<a style="text-decoration:none; color:#c09439; font-size:14px; font-family:Arial, Helvetica, sans-serif;" href="'.$this->privacy_policy_link.'">Privacy Policy</a>
								                        </td>
								                    </tr>
								                    <tr>
								                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000;font-size:14px;">Copyright © 2016 <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>. All rights reserved.</td>
								                    </tr>
								                    <tr>
								                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000;font-size:14px;">This email was sent by <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>, THUB, Dubai Silicon Oasis, Dubai, United Arab Emirates</td>
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
								</html>';

		$this->sendEmail($email,$emailSubject, $emailBody);
	}

	public function managerReviewRatingMail($userName,$company_name,$email)
	{
		$lang = JFactory::getLanguage();
		$extension = 'com_beseated';
		$base_dir = JPATH_SITE;
		$language_tag = 'en-GB';
		$reload = true;
		$lang->load($extension, $base_dir, $language_tag, $reload);

		$app           = JFactory::getApplication();
		$menu          = $app->getMenu();
		$menuItem      = $menu->getItems( 'link', 'index.php?option=com_users&view=login', true );
		$loginLink     = JUri::base()."index.php?option=com_users&view=login&Itemid=".$menuItem->id;
		//$userDetail    = JFactory::getUser($userID);
		$emailSubject  = JText::sprintf(
								'COM_BESEATED_MANAGER_REVIEW_RATING_EMAIL_SUBJECT'
							);

		$emailBody        = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
							<html>
							<head>
							<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
							<title>Beseated</title>
							<style type="text/css" media="screen">
								p{margin: 0px !important; color: #000;}
								.ExternalClass p { color: #000; margin: 0px !important;}
								br{line-height: 12px;}
								.HOEnZb im p{margin:0;}
							</style>
							</head>
							<body>
							<table border="0" bgcolor="#FFFFFF" cellpadding="0" cellspacing="0" width="650" align="center" style="font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:20px;">
								<tbody>
							    	<tr>
							        	<td height="10"></td>
							        </tr>
							        <tr>
							        	<td align="center">
							            	<table width="630" border="0" cellpadding="0" cellspacing="0">
							                	<tr>
							                        <td align="right" colspan="2">
							                        	<table width="100%" border="0" cellpadding="0" cellspacing="0">
							                            	<tr>
							                                	<td align="left" width="90"><a href="'.$this->logo_link.'"><img src="'.JUri::base().'images/edms/images/beseated-logo.png"></a></td>
							                                  <td align="center" style="font-family:Arial, Helvetica, sans-serif;"></td>
							                                </tr>
							                            </table>
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20"></td>
							                    </tr>
							                    <tr>
							                        <td colspan="2" style="color:#000;">
							                        	<p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top: 0; Margin-bottom: 0;">Dear '.$company_name.',<br><br>
							                            '.ucfirst($userName).' has rated you!
							</p><br>
							                            <p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top: 0; Margin-bottom: 0;">You can view this rating within your Beseated Ratings Page on your account.
							</p>
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="4">
							                        </td>
							                    </tr>

							                    <tr>
							                    	<td colspan="2"><p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top: 0; Margin-bottom: 0;"><br>Beseated Support.</p></td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="30">
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20" style=" border-top:1px solid #c09439;"></td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="color:#5f5f5f; font-size:14px;">
							                        	<a href="#" style="text-decoration:none; color:#c09439; font-size:14px; font-family:Arial, Helvetica, sans-serif;">My Beseated ID</a>&nbsp;|&nbsp;<a style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif; font-size:14px;" href="'.$this->support_link.'">Support</a>&nbsp;|&nbsp;<a style="text-decoration:none; color:#c09439; font-size:14px; font-family:Arial, Helvetica, sans-serif;" href="'.$this->privacy_policy_link.'">Privacy Policy</a>
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000; font-size:14px;">Copyright © 2016 <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>. All rights reserved.</td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000; font-size:14px;">This email was sent by <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>, THUB, Dubai Silicon Oasis, Dubai, United Arab Emirates</td>
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

		$this->sendEmail($email,$emailSubject, $emailBody);
	}

	public function userNewMessageMail($userName,$company_name,$message,$subject,$email)
	{
		$lang = JFactory::getLanguage();
		$extension = 'com_beseated';
		$base_dir = JPATH_SITE;
		$language_tag = 'en-GB';
		$reload = true;
		$lang->load($extension, $base_dir, $language_tag, $reload);


		$app           = JFactory::getApplication();
		$menu          = $app->getMenu();
		$menuItem      = $menu->getItems( 'link', 'index.php?option=com_users&view=login', true );
		$loginLink     = JUri::base()."index.php?option=com_users&view=login&Itemid=".$menuItem->id;
		//$userDetail    = JFactory::getUser($userID);
		$emailSubject  = JText::sprintf(
								'COM_BESEATED_USER_NEW_MESSAGE_EMAIL_SUBJECT'
							);

		$emailBody        = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
							<html>
							<head>
							<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
							<title>Beseated</title>
							<style type="text/css" media="screen">
								p{margin: 0px !important; color: #000;}
								.ExternalClass p { color: #000; margin: 0px !important;}
								br{line-height: 12px;}
								.HOEnZb im p{margin:0;}
							</style>
							</head>
							<body>
							<table border="0" bgcolor="#FFFFFF" cellpadding="0" cellspacing="0" width="650" align="center" style="font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:20px;">
								<tbody>
							    	<tr>
							        	<td height="10"></td>
							        </tr>
							        <tr>
							        	<td align="center">
							            	<table width="630" border="0" cellpadding="0" cellspacing="0">
							                	<tr>
							                        <td align="right" colspan="2">
							                        	<table width="100%" border="0" cellpadding="0" cellspacing="0">
							                            	<tr>
							                                	<td align="left" width="90"><a href="'.$this->logo_link.'"><img src="'.JUri::base().'images/edms/images/beseated-logo.png"></a></td>
							                                    <td align="center" style="font-family:Arial, Helvetica, sans-serif;"></td>
							                                </tr>
							                            </table>
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20"></td>
							                    </tr>
							                    <tr>
							                        <td colspan="2" style="color:#000;">
							                        	<p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top: 0; Margin-bottom: 0;">Dear '.$userName.',<br><br>
							                            You have received a new message from '.$company_name.'.<br><br>';

							                            if(!empty($subject))
						                            	{
						                            			$emailBody .= $subject.' :';
						                            	}

						                            	$emailBody .=  ' </p><p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top: 0; Margin-bottom: 0;">“'.$message.'” </p><br>
							                            <p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top: 0; Margin-bottom: 0;">You can also view messages within your Messages tab.</p><br>
							                            <p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top: 0; Margin-bottom: 0;">Beseated Support.</p>
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
							                    	<td colspan="2" align="center" style="color:#5f5f5f; font-size:14px;">
							                        	<a href="#" style="text-decoration:none; color:#c09439; font-size:14px; font-family:Arial, Helvetica, sans-serif;">My Beseated ID</a>&nbsp;|&nbsp;<a style="text-decoration:none; font-size:14px; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="'.$this->support_link.'">Support</a>&nbsp;|&nbsp;<a style="text-decoration:none; color:#c09439; font-size:14px; font-family:Arial, Helvetica, sans-serif;" href="'.$this->privacy_policy_link.'">Privacy Policy</a>
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000; font-size:14px;">Copyright © 2016 <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>. All rights reserved.</td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000; font-size:14px;">This email was sent by <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>, THUB, Dubai Silicon Oasis, Dubai, United Arab Emirates</td>
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

		$this->sendEmail($email,$emailSubject, $emailBody);
	}

	public function biggestSpenderMail($userName,$company_name,$email)
	{
		$lang = JFactory::getLanguage();
		$extension = 'com_beseated';
		$base_dir = JPATH_SITE;
		$language_tag = 'en-GB';
		$reload = true;
		$lang->load($extension, $base_dir, $language_tag, $reload);

		$app           = JFactory::getApplication();
		$menu          = $app->getMenu();
		$menuItem      = $menu->getItems( 'link', 'index.php?option=com_users&view=login', true );
		$loginLink     = JUri::base()."index.php?option=com_users&view=login&Itemid=".$menuItem->id;
		//$userDetail    = JFactory::getUser($userID);
		$emailSubject  = JText::sprintf(
								'COM_BESEATED_USER_BIGGEST_SPENDER_EMAIL_SUBJECT'
							);

		$emailBody        = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
							<html>
							<head>
							<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
							<title>Beseated</title>
							<style type="text/css" media="screen">
								p{margin: 0px !important; color: #000;}
								.ExternalClass p { color: #000; margin: 0px !important;}
								br{line-height: 12px;}
								.HOEnZb im p{margin:0;}
							</style>
							</head>
							<body>
							<table border="0" bgcolor="#FFFFFF" cellpadding="0" cellspacing="0" width="650" align="center" style="font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:20px;">
								<tbody>
							    	<tr>
							        	<td height="10"></td>
							        </tr>
							        <tr>
							        	<td align="center">
							            	<table width="630" border="0" cellpadding="0" cellspacing="0">
							                	<tr>
							                        <td align="right" colspan="2">
							                        	<table width="100%" border="0" cellpadding="0" cellspacing="0">
							                            	<tr>
							                                	<td align="left" width="90"><a href="'.$this->logo_link.'"><img src="'.JUri::base().'images/edms/images/beseated-logo.png"></a></td>
							                                  <td align="center" style="font-family:Arial, Helvetica, sans-serif;"></td>
							                                </tr>
							                            </table>
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20"></td>
							                    </tr>
							                    <tr>
							                        <td colspan="2" style="color:#000;">
							                        	<p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top: 0; Margin-bottom: 0;">Dear '.$userName.',<br><br>
							                            CONGRATULATIONS!</p><br>
							                            <p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top: 0; Margin-bottom: 0;">You are now listed as one of '.$company_name.'’s biggest spenders on Beseated.</p><br>
							                            <p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top: 0; Margin-bottom: 0;">Your proﬁle image can be shown on the venue info page only if the Biggest spender toggle in your proﬁle screen is switched on.
							</p><br>
							                            <p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top: 0; Margin-bottom: 0;">Beseated Support.</p>
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
							                    	<td colspan="2" align="center" style="color:#5f5f5f; font-size:14px;">
							                        	<a href="#" style="text-decoration:none; color:#c09439; font-size:14px; font-family:Arial, Helvetica, sans-serif;">My Beseated ID</a>&nbsp;|&nbsp;<a style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif; font-size:14px;" href="'.$this->support_link.'">Support</a>&nbsp;|&nbsp;<a style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif; font-size:14px;" href="'.$this->privacy_policy_link.'">Privacy Policy</a>
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000; font-size:14px;">Copyright © 2016 <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>. All rights reserved.</td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000; font-size:14px;">This email was sent by <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>, THUB, Dubai Silicon Oasis, Dubai, United Arab Emirates</td>
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

		self::sendEmail($email,$emailSubject, $emailBody);
	}

	public function invitationMailUser($invitedUserName,$inviteeName,$venue_name,$table_name,$booking_date,$booking_time,$isNightVenue = 0,$email)
	{
		$lang = JFactory::getLanguage();
		$extension = 'com_beseated';
		$base_dir = JPATH_SITE;
		$language_tag = 'en-GB';
		$reload = true;
		$lang->load($extension, $base_dir, $language_tag, $reload);

		$app           = JFactory::getApplication();
		$menu          = $app->getMenu();
		$menuItem      = $menu->getItems( 'link', 'index.php?option=com_users&view=login', true );
		$loginLink     = JUri::base()."index.php?option=com_users&view=login&Itemid=".$menuItem->id;
		//$userDetail    = JFactory::getUser($userID);
		$emailSubject  = JText::sprintf(
								'COM_BESEATED_VENUE_INVITATION_EMAIL_SUBJECT'
							);

		$emailBody        = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
							<html>
							<head>
							<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
							<title>Beseated</title>
							<style type="text/css" media="screen">
								p{margin: 0px !important; color: #000;}
								.ExternalClass p { color: #000; margin: 0px !important;}
								br{line-height: 12px;}
								.HOEnZb im p{margin:0;}
							</style>
							</head>
							<body>
							<table border="0" bgcolor="#FFFFFF" cellpadding="0" cellspacing="0" width="650" align="center" style="font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:20px;">
								<tbody>
							    	<tr>
							        	<td height="10"></td>
							        </tr>
							        <tr>
							        	<td align="center">
							            	<table width="630" border="0" cellpadding="0" cellspacing="0">
							                	<tr>
							                        <td align="right" colspan="2">
							                        	<table width="100%" border="0" cellpadding="0" cellspacing="0">
							                            	<tr>
							                                	<td align="left" width="90"><a href="'.$this->logo_link.'"><img src="'.JUri::base().'images/edms/images/beseated-logo.png"></a></td>
							                                  <td align="center" style="font-family:Arial, Helvetica, sans-serif;"></td>
							                                </tr>
							                            </table>
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20"></td>
							                    </tr>
							                    <tr>
							                        <td colspan="2" style="color:#000;">
							                        	<p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top:0;Margin-bottom:0;">Dear '.$invitedUserName.',<br><br>
							                            '.$inviteeName.' has invited you to join them at '.$venue_name.' for their '.$table_name.' on '.$booking_date.'';

						                            	if($isNightVenue == 0)
						                            	{
						                            			$emailBody .=  ' at '.$booking_time;
						                            	}

							                             $emailBody .= '.</p><br>
							                            <p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top: 0; Margin-bottom: 0;">You can view this invite in your RSVP tab, and if you do not have an account yet, you can register at our Website or Mobile application using this email address and the booking information will be waiting for you.
							</p><br>
							                            <p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top: 0; Margin-bottom: 0;">Beseated Support.</p>
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
							                    	<td colspan="2" align="center" style="color:#5f5f5f; font-size:14px;">
							                        	<a href="#" style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif; font-size:14px;">My Beseated ID</a>&nbsp;|&nbsp;<a style="text-decoration:none; color:#c09439; font-size:14px; font-family:Arial, Helvetica, sans-serif;" href="'.$this->support_link.'">Support</a>&nbsp;|&nbsp;<a style="text-decoration:none; color:#c09439; font-size:14px; font-family:Arial, Helvetica, sans-serif;" href="'.$this->privacy_policy_link.'">Privacy Policy</a>
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000; font-size:14px;">Copyright © 2016 <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>. All rights reserved.</td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000; font-size:14px;">This email was sent by <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>, THUB, Dubai Silicon Oasis, Dubai, United Arab Emirates</td>
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
							</html>';

							//echo "<pre/>";print_r($emailBody);exit;

		self::sendEmail($email,$emailSubject, $emailBody);
	}

	public function friendRequestJoinTableMail($bookingUserName,$requestedUserName,$table_name,$venue_name,$booking_date,$booking_time,$isNightVenue = 0,$email)
	{
		$lang = JFactory::getLanguage();
		$extension = 'com_beseated';
		$base_dir = JPATH_SITE;
		$language_tag = 'en-GB';
		$reload = true;
		$lang->load($extension, $base_dir, $language_tag, $reload);

		$app           = JFactory::getApplication();
		$menu          = $app->getMenu();
		$menuItem      = $menu->getItems( 'link', 'index.php?option=com_users&view=login', true );
		$loginLink     = JUri::base()."index.php?option=com_users&view=login&Itemid=".$menuItem->id;
		//$userDetail    = JFactory::getUser($userID);
		$emailSubject  = JText::sprintf(
								'COM_BESEATED_VENUE_FRIEND_REQUEST_JOIN_TABLE_EMAIL_SUBJECT'
							);

		$emailBody        = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
							<html>
							<head>
							<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
							<title>Beseated</title>
							<style type="text/css" media="screen">
								p{margin: 0px !important; color: #000;}
								.ExternalClass p { color: #000; margin: 0px !important;}
								br{line-height: 12px;}
								.HOEnZb im p{margin:0;}
							</style>
							</head>
							<body>
							<table border="0" bgcolor="#FFFFFF" cellpadding="0" cellspacing="0" width="650" align="center" style="font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:20px;">
								<tbody>
							    	<tr>
							        	<td height="10"></td>
							        </tr>
							        <tr>
							        	<td align="center">
							            	<table width="630" border="0" cellpadding="0" cellspacing="0">
							                	<tr>
							                        <td align="right" colspan="2">
							                        	<table width="100%" border="0" cellpadding="0" cellspacing="0">
							                            	<tr>
							                                	<td align="left" width="90"><a href="'.$this->logo_link.'"><img src="'.JUri::base().'images/edms/images/beseated-logo.png"></a></td>
							                                  <td align="center" style="font-family:Arial, Helvetica, sans-serif;"></td>
							                                </tr>
							                            </table>
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20"></td>
							                    </tr>
							                    <tr>
							                        <td colspan="2" style="color:#000;">
							                        	<p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top:0;Margin-bottom:0;">Dear '.$bookingUserName.',<br><br>
							                            '.$requestedUserName.' has requested to join you on '.$table_name.' at '.$venue_name.', on '.$booking_date.'';

							                            /*if($isNightVenue == 0)
							                            {
							                            	$emailBody .=' at '.$booking_time.'';
							                            }*/

							                            $emailBody .= '. Kindly conﬁrm interest as soon as possible.</p><br>
							                            <p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top: 0; Margin-bottom: 0;">You can view this request in your RSVP tab.</p><br>
							                            <p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top: 0; Margin-bottom: 0;">Beseated Support.</p>
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
							                    	<td colspan="2" align="center" style="color:#5f5f5f; font-size:14px;">
							                        	<a href="#" style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif; font-size:14px;">My Beseated ID</a>&nbsp;|&nbsp;<a style="text-decoration:none; color:#c09439; font-size:14px; font-family:Arial, Helvetica, sans-serif;" href="'.$this->support_link.'">Support</a>&nbsp;|&nbsp;<a style="text-decoration:none; color:#c09439; font-size:14px; font-family:Arial, Helvetica, sans-serif;" href="'.$this->privacy_policy_link.'">Privacy Policy</a>
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000; font-size:14px;">Copyright © 2016 <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>. All rights reserved.</td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000; font-size:14px;">This email was sent by <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>, THUB, Dubai Silicon Oasis, Dubai, United Arab Emirates</td>
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

		self::sendEmail($email,$emailSubject, $emailBody);
	}

	public function friendRequestAcceptedDeclinedJoinTableMail($attending_username,$booking_ownername,$table_name,$booking_date,$booking_time,$statusName,$isNightVenue,$email)
	{
		//$email = "jamal@tasolglobal.com";

		$lang = JFactory::getLanguage();
		$extension = 'com_beseated';
		$base_dir = JPATH_SITE;
		$language_tag = 'en-GB';
		$reload = true;
		$lang->load($extension, $base_dir, $language_tag, $reload);

		$app           = JFactory::getApplication();
		$menu          = $app->getMenu();
		$menuItem      = $menu->getItems( 'link', 'index.php?option=com_users&view=login', true );
		$loginLink     = JUri::base()."index.php?option=com_users&view=login&Itemid=".$menuItem->id;
		//$userDetail    = JFactory::getUser($userID);
		$emailSubject  = JText::sprintf(
								'COM_BESEATED_VENUE_FRIEND_REQUEST_ACCEPTED_JOIN_TABLE_EMAIL_SUBJECT'
							);

		$emailBody        = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
							<html>
							<head>
							<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
							<title>Beseated</title>
							<style type="text/css" media="screen">
								p{margin: 0px !important; color: #000;}
								.ExternalClass p { color: #000; margin: 0px !important;}
								br{line-height: 12px;}
								.HOEnZb im p{margin:0;}
							</style>
							</head>
							<body>
							<table border="0" bgcolor="#FFFFFF" cellpadding="0" cellspacing="0" width="650" align="center" style="font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:20px;">
								<tbody>
							    	<tr>
							        	<td height="10"></td>
							        </tr>
							        <tr>
							        	<td align="center">
							            	<table width="630" border="0" cellpadding="0" cellspacing="0">
							                	<tr>
							                        <td align="right" colspan="2">
							                        	<table width="100%" border="0" cellpadding="0" cellspacing="0">
							                            	<tr>
							                                	<td align="left" width="90"><a href="'.$this->logo_link.'"><img src="'.JUri::base().'images/edms/images/beseated-logo.png"></a></td>
							                                  <td align="center" style="font-family:Arial, Helvetica, sans-serif;"></td>
							                                </tr>
							                            </table>
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20"></td>
							                    </tr>
							                    <tr>
							                        <td colspan="2" style="color:#000;">
							                        	<p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top:0;Margin-bottom:0;">Dear '.$attending_username.',<br><br>
							                            '.$booking_ownername.' has '.$statusName.' to add you to their booking for their '.$table_name.' on '.$booking_date.'';

							                            if($isNightVenue == 0)
							                            {
							                            	$emailBody .=' at '.$booking_time.'';
							                            }

							                            $emailBody.= '.</p><br><p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top: 0; Margin-bottom: 0;">You can view this booking in your Bookings tab.</p><br>
							                            <p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top: 0; Margin-bottom: 0;">Beseated Support.</p>
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
							                        	<a href="#" style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif;">My Beseated ID</a>&nbsp;|&nbsp;<a style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="'.$this->support_link.'">Support</a>&nbsp;|&nbsp;<a style="text-decoration:none; font-size:14px; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="'.$this->privacy_policy_link.'">Privacy Policy</a>
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000;">Copyright © 2016 <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>. All rights reserved.</td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000;">This email was sent by <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>, THUB, Dubai Silicon Oasis, Dubai, United Arab Emirates</td>
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

		self::sendEmail($email,$emailSubject, $emailBody);
	}

	public function NoShowVenueUserMail($booking_owner_name,$venue_name,$booking_date,$booking_time,$is_day_club,$email)
	{
		//$email = "jamal@tasolglobal.com";
		$lang = JFactory::getLanguage();
		$extension = 'com_beseated';
		$base_dir = JPATH_SITE;
		$language_tag = 'en-GB';
		$reload = true;
		$lang->load($extension, $base_dir, $language_tag, $reload);

		$emailSubject  = JText::sprintf(
								'COM_BESEATED_VENUE_NO_SHOW_BOOKING_EMAIL_SUBJECT',
								 $venue_name
							);

		$emailBody        = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
							<html>
							<head>
							<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
							<title>Beseated</title>
							<style type="text/css" media="screen">
								p{margin: 0px !important; color: #000;}
								.ExternalClass p { color: #000; margin: 0px !important;}
								br{line-height: 12px;}
								.HOEnZb im p{margin:0;}
							</style>
							</head>
							<body>
							<table border="0" bgcolor="#FFFFFF" cellpadding="0" cellspacing="0" width="650" align="center" style="font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:20px;">
								<tbody>
							    	<tr>
							        	<td height="10"></td>
							        </tr>
							        <tr>
							        	<td align="center">
							            	<table width="630" border="0" cellpadding="0" cellspacing="0">
							                	<tr>
							                        <td align="right" colspan="2">
							                        	<table width="100%" border="0" cellpadding="0" cellspacing="0">
							                            	<tr>
							                                	<td align="left" width="90"><a href="'.$this->logo_link.'"><img src="'.JUri::base().'images/edms/images/beseated-logo.png"></a></td>
							                                  <td align="center" style="font-family:Arial, Helvetica, sans-serif;"></td>
							                                </tr>
							                            </table>
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20"></td>
							                    </tr>
							                    <tr>
							                        <td colspan="2" style="color:#000;">
							                        	<p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top:0;Margin-bottom:0;">Dear '.$booking_owner_name.',<br><br>
							                            I sincerely hope that you are well. Thank you for selecting Beseated as your means to connect with your favorite Venues and Premium .</p><br>
							                            <p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top: 0; Margin-bottom: 0;">We noticed you were unable to attend your booking made at '.$venue_name.' on '.$booking_date.'';

					                            		if($is_day_club == 1)
					                            		{
					                            			 $emailBody.=' at '.$booking_time.'';
					                            		}

							                            $emailBody.= '.</p><br>
							                            <p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top: 0; Margin-bottom: 0;">As you are aware, when you book with Beseated, other people miss the opportunity to book the same table at their favorite Venue.  We understand that there are some occasions when the customer must miss a booking due to unforeseen circumstances beyond his or her control. In this event, we ask that you call the Venue or cancel your booking on our Beseated Platforms prior to 24 hours of a booking.  If you fail to do so, the next time you are considered a No Show, the venue has the right to block you from booking at their venue  again.
							</p><br>
							                            <p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top: 0; Margin-bottom: 0;">Beseated Support.</p>
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
							                        	<a href="#" style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif;">My Beseated ID</a>&nbsp;|&nbsp;<a style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="'.$this->support_link.'">Support</a>&nbsp;|&nbsp;<a style="text-decoration:none; font-size:14px; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="'.$this->privacy_policy_link.'">Privacy Policy</a>
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000;">Copyright © 2016 <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>. All rights reserved.</td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000;">This email was sent by <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>, THUB, Dubai Silicon Oasis, Dubai, United Arab Emirates</td>
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

		self::sendEmail($email,$emailSubject, $emailBody);
	}

	public function NoShowLuxuryUserMail($booking_owner_name,$element_name,$booking_date,$booking_time,$email)
	{
		$lang = JFactory::getLanguage();
		$extension = 'com_beseated';
		$base_dir = JPATH_SITE;
		$language_tag = 'en-GB';
		$reload = true;
		$lang->load($extension, $base_dir, $language_tag, $reload);

		$emailSubject  = JText::sprintf(
								'COM_BESEATED_LUXURY_NO_SHOW_BOOKING_EMAIL_SUBJECT',
								 $element_name
							);

		$emailBody        = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
							<html>
							<head>
							<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
							<title>Beseated</title>
							<style type="text/css" media="screen">
								p{margin: 0px !important; color: #000;}
								.ExternalClass p { color: #000; margin: 0px !important;}
								br{line-height: 12px;}
								.HOEnZb im p{margin:0;}
							</style>
							</head>
							<body>
							<table border="0" bgcolor="#FFFFFF" cellpadding="0" cellspacing="0" width="650" align="center" style="font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:20px;">
								<tbody>
							    	<tr>
							        	<td height="10"></td>
							        </tr>
							        <tr>
							        	<td align="center">
							            	<table width="630" border="0" cellpadding="0" cellspacing="0">
							                	<tr>
							                        <td align="right" colspan="2">
							                        	<table width="100%" border="0" cellpadding="0" cellspacing="0">
							                            	<tr>
							                                	<td align="left" width="90"><a href="'.$this->logo_link.'"><img src="'.JUri::base().'images/edms/images/beseated-logo.png"></a></td>
							                                  <td align="center" style="font-family:Arial, Helvetica, sans-serif;"></td>
							                                </tr>
							                            </table>
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20"></td>
							                    </tr>
							                    <tr>
							                        <td colspan="2" style="color:#000;">
							                        	<p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top:0;Margin-bottom:0;">Dear '.$booking_owner_name.',<br><br>
							                            I sincerely hope that you are well. Thank you for selecting Beseated as your means to connect with your favorite Venues and Premium Luxuries.</p><br>
							                            <p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top: 0; Margin-bottom: 0;">We noticed you were unable to attend your booking made at '.$element_name.' on '.$booking_date.' at '.$booking_time.'. Since this is your ﬁrst No Show, we will refund your payment.
							</p><br>
							<p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top: 0; Margin-bottom: 0;">As you are aware, when you book with Beseated, other people miss the opportunity to book the same luxury.  We understand that there are some occasions when the customer must miss a booking due to unforeseen circumstances beyond his or her control.  In this event, we ask that you cancel your booking on our Beseated Platforms prior to 24 hours of a booking.  If you fail to do so, the next time you are considered a No Show, we will not be able to refund your payment as per our Policies.

							</p><br>
							                            <p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top: 0; Margin-bottom: 0;">Beseated Support.</p>
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
							                        	<a href="#" style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif;">My Beseated ID</a>&nbsp;|&nbsp;<a style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="'.$this->support_link.'">Support</a>&nbsp;|&nbsp;<a style="text-decoration:none; font-size:14px; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="'.$this->privacy_policy_link.'">Privacy Policy</a>
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000;">Copyright © 2016 <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>. All rights reserved.</td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000;">This email was sent by <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>, THUB, Dubai Silicon Oasis, Dubai, United Arab Emirates</td>
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

		self::sendEmail($email,$emailSubject, $emailBody);
	}

	public function cancelBookingByUserMail($userName,$element_name,$service_name,$booking_date,$booking_time,$isDayVenue = 1,$email)
	{
		$lang = JFactory::getLanguage();
		$extension = 'com_beseated';
		$base_dir = JPATH_SITE;
		$language_tag = 'en-GB';
		$reload = true;
		$lang->load($extension, $base_dir, $language_tag, $reload);

		$emailSubject  = JText::sprintf('COM_BESEATED_CANCELED_BOOKING_EMAIL_SUBJECT');

		$emailBody        = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
							<html>
							<head>
							<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
							<title>Beseated</title>
							<style type="text/css" media="screen">
								p{margin: 0px !important; color: #000;}
								.ExternalClass p { color: #000; margin: 0px !important;}
								br{line-height: 12px;}
								.HOEnZb im p{margin:0;}
							</style>
							</head>
							<body>
							<table border="0" bgcolor="#FFFFFF" cellpadding="0" cellspacing="0" width="650" align="center" style="font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:20px;">
								<tbody>
							    	<tr>
							        	<td height="10"></td>
							        </tr>
							        <tr>
							        	<td align="center">
							            	<table width="630" border="0" cellpadding="0" cellspacing="0">
							                	<tr>
							                        <td align="right" colspan="2">
							                        	<table width="100%" border="0" cellpadding="0" cellspacing="0">
							                            	<tr>
							                                	<td align="left" width="90"><a href="'.$this->logo_link.'"><img src="'.JUri::base().'images/edms/images/beseated-logo.png"></a></td>
							                                  <td align="center" style="font-family:Arial, Helvetica, sans-serif;"></td>
							                                </tr>
							                            </table>
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20"></td>
							                    </tr>
							                    <tr>
							                        <td colspan="2" style="color:#000;">
							                        	<p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top:0;Margin-bottom:0;">Dear '.$userName.',<br><br>
							                            It is an unfortunate that you have cancelled your booking at '.$element_name.' for '.$service_name.' on '.$booking_date.'';

							                            if(!empty($isDayVenue))
						                            	{
						                            			$emailBody .=  ' at '.$booking_time;
						                            	}

						                            $emailBody .='. </p><br>
							                            <p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top: 0; Margin-bottom: 0;">Hopefully it will work out next time.</p><br>
							                            <p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top: 0; Margin-bottom: 0;">Beseated Support.</p>
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
							                        	<a href="#" style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif;">My Beseated ID</a>&nbsp;|&nbsp;<a style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="'.$this->support_link.'">Support</a>&nbsp;|&nbsp;<a style="text-decoration:none; font-size:14px; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="'.$this->privacy_policy_link.'">Privacy Policy</a>
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000;">Copyright © 2016 <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>. All rights reserved.</td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000;">This email was sent by <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>, THUB, Dubai Silicon Oasis, Dubai, United Arab Emirates</td>
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

		self::sendEmail($email,$emailSubject, $emailBody);
	}

	public function cancelBookingToManagerMail($company_name,$emailContent,$email)
	{
		$lang = JFactory::getLanguage();
		$extension = 'com_beseated';
		$base_dir = JPATH_SITE;
		$language_tag = 'en-GB';
		$reload = true;
		$lang->load($extension, $base_dir, $language_tag, $reload);

		$emailSubject  = JText::sprintf('COM_BESEATED_TO_MANAGER_CANCELED_BOOKING_EMAIL_SUBJECT');

		$emailBody        = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
							<html>
						<head>
						<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
						<title>Beseated</title>
						<style type="text/css" media="screen">
							p{margin: 0px !important; color: #000;}
							.ExternalClass p { color: #000; margin: 0px !important;}
							br{line-height: 12px;}
							.HOEnZb im p{margin:0;}
						</style>
						</head>
						<body>
						<table border="0" bgcolor="#FFFFFF" cellpadding="0" cellspacing="0" width="650" align="center" style="font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:20px;">
							<tbody>
						    	<tr>
						        	<td height="10"></td>
						        </tr>
						        <tr>
						        	<td align="center">
						            	<table width="630" border="0" cellpadding="0" cellspacing="0">
						                	<tr>
						                        <td align="right" colspan="2">
						                        	<table width="100%" border="0" cellpadding="0" cellspacing="0">
						                            	<tr>
						                                	<td align="left" width="90"><a href="'.$this->logo_link.'"><img src="'.JUri::base().'images/edms/images/beseated-logo.png"></a></td>
						                                  <td align="center" style="font-family:Arial, Helvetica, sans-serif;"></td>
						                                </tr>
						                            </table>
						                        </td>
						                    </tr>
						                    <tr>
						                    	<td colspan="2" height="20"></td>
						                    </tr>
						                    <tr>
						                        <td colspan="2" style="color:#000;">
						                        	<p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top:0;Margin-bottom:0;">Dear '.$company_name.',<br><br>
						                            '.$emailContent.'
						</p><br>
						                            <p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top: 0; Margin-bottom: 0;">We understand that there are some occasions when the customer must cancel a booking due to unforeseen circumstances beyond his or her control.
						</p><br>
						<p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top: 0; Margin-bottom: 0;">Appreciate your understanding.</p><br>
						                        </td>
						                    </tr>
						                    <tr>
						                    	<td colspan="2" height="4">
						                        </td>
						                    </tr>
						                    <tr>
						                    	<td colspan="2"><p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top: 0; Margin-bottom: 0;">Beseated Support.</p></td>
						                    </tr>
						                    <tr>
						                    	<td colspan="2" height="30">
						                        </td>
						                    </tr>
						                    <tr>
						                    	<td colspan="2" height="20" style=" border-top:1px solid #c09439;"></td>
						                    </tr>
						                    <tr>
						                    	<td colspan="2" align="center" style="color:#5f5f5f;">
						                        	<a href="#" style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif;">My Beseated ID</a>&nbsp;|&nbsp;<a style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="'.$this->support_link.'">Support</a>&nbsp;|&nbsp;<a style="text-decoration:none; font-size:14px; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="'.$this->privacy_policy_link.'">Privacy Policy</a>
						                        </td>
						                    </tr>
						                    <tr>
						                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000;">Copyright © 2016 <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>. All rights reserved.</td>
						                    </tr>
						                    <tr>
						                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000;">This email was sent by <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>, THUB, Dubai Silicon Oasis, Dubai, United Arab Emirates</td>
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

		self::sendEmail($email,$emailSubject, $emailBody);
	}


	public function guestlistRequestAcceptedMail($userName,$venue_name,$booking_date,$email)
	{


		$lang = JFactory::getLanguage();
		$extension = 'com_beseated';
		$base_dir = JPATH_SITE;
		$language_tag = 'en-GB';
		$reload = true;
		$lang->load($extension, $base_dir, $language_tag, $reload);

		$emailSubject  = JText::sprintf('COM_BESEATED_VENUE_GUEST_LIST_REQUEST_ACCEPTED_EMAIL_SUBJECT');

		$emailBody        = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
							<html>
							<head>
							<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
							<title>Beseated</title>
							<style type="text/css" media="screen">
								p{margin: 0px !important; color: #000;}
								.ExternalClass p { color: #000; margin: 0px !important;}
								br{line-height: 12px;}
								.HOEnZb im p{margin:0;}
							</style>
							</head>
							<body>
							<table border="0" bgcolor="#FFFFFF" cellpadding="0" cellspacing="0" width="650" align="center" style="font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:20px;">
								<tbody>
							    	<tr>
							        	<td height="10"></td>
							        </tr>
							        <tr>
							        	<td align="center">
							            	<table width="630" border="0" cellpadding="0" cellspacing="0">
							                	<tr>
							                        <td align="right" colspan="2">
							                        	<table width="100%" border="0" cellpadding="0" cellspacing="0">
							                            	<tr>
							                                	<td align="left" width="90"><a href="'.$this->logo_link.'"><img src="'.JUri::base().'images/edms/images/beseated-logo.png"></a></td>
							                                  <td align="center" style="font-family:Arial, Helvetica, sans-serif;"></td>
							                                </tr>
							                            </table>
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20"></td>
							                    </tr>
							                    <tr>
							                        <td colspan="2" style="color:#000;">
							                        	<p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top:0;Margin-bottom:0;">Dear '.$userName.',<br><br>
							                            You have been successfully added to '.$venue_name.'\'s Guestlist on '.$booking_date.'. </p><br>
							                            <p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top: 0; Margin-bottom: 0;">Beseated Support.</p>
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
							                        	<a href="#" style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif;">My Beseated ID</a>&nbsp;|&nbsp;<a style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="'.$this->support_link.'">Support</a>&nbsp;|&nbsp;<a style="text-decoration:none; font-size:14px; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="'.$this->privacy_policy_link.'">Privacy Policy</a>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000;">Copyright © 2016 <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>. All rights reserved.</td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000;">This email was sent by <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>, THUB, Dubai Silicon Oasis, Dubai, United Arab Emirates</td>
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

		self::sendEmail($email,$emailSubject, $emailBody);
	}

	public function guestlistRequestDeclinedMail($userName,$venue_name,$booking_date,$email)
	{
		$lang = JFactory::getLanguage();
		$extension = 'com_beseated';
		$base_dir = JPATH_SITE;
		$language_tag = 'en-GB';
		$reload = true;
		$lang->load($extension, $base_dir, $language_tag, $reload);

		$emailSubject  = JText::sprintf('COM_BESEATED_VENUE_GUEST_LIST_REQUEST_DECLINED_EMAIL_SUBJECT');

		$emailBody        = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
							<html>
							<head>
							<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
							<title>Beseated</title>
							<style type="text/css" media="screen">
								p{margin: 0px !important; color: #000;}
								.ExternalClass p { color: #000; margin: 0px !important;}
								br{line-height: 12px;}
								.HOEnZb im p{margin:0;}
							</style>
							</head>
							<body>
							<table border="0" bgcolor="#FFFFFF" cellpadding="0" cellspacing="0" width="650" align="center" style="font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:20px;">
								<tbody>
							    	<tr>
							        	<td height="10"></td>
							        </tr>
							        <tr>
							        	<td align="center">
							            	<table width="630" border="0" cellpadding="0" cellspacing="0">
							                	<tr>
							                        <td align="right" colspan="2">
							                        	<table width="100%" border="0" cellpadding="0" cellspacing="0">
							                            	<tr>
							                                	<td align="left" width="90"><a href="'.$this->logo_link.'"><img src="'.JUri::base().'images/edms/images/beseated-logo.png"></a></td>
							                                  <td align="center" style="font-family:Arial, Helvetica, sans-serif;"></td>
							                                </tr>
							                            </table>
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20"></td>
							                    </tr>
							                    <tr>
							                        <td colspan="2" style="color:#000;">
							                        	<p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top:0;Margin-bottom:0;">Dear '.$userName.',<br><br>
							                            Unfortunately, '.$venue_name.'\'s guestlist on '.$booking_date.' is currently full. Please try another date or venue.</p><br>
							                            <p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top: 0; Margin-bottom: 0;">If there is a will, there is a way. Booking a table always gives you a better chance in getting in and having a more exclusive night.
							</p><br>
							                            <p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top: 0; Margin-bottom: 0;">Beseated Support.</p>
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
							                        	<a href="#" style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif;">My Beseated ID</a>&nbsp;|&nbsp;<a style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="'.$this->support_link.'">Support</a>&nbsp;|&nbsp;<a style="text-decoration:none; font-size:14px; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="'.$this->privacy_policy_link.'">Privacy Policy</a>
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000;">Copyright © 2016 <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>. All rights reserved.</td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000;">This email was sent by <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>, THUB, Dubai Silicon Oasis, Dubai, United Arab Emirates</td>
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

		self::sendEmail($email,$emailSubject, $emailBody);
	}

	public function guestlistRequestMail($venue_name,$userName,$booking_date,$total_guest,$email)
	{
		$lang = JFactory::getLanguage();
		$extension = 'com_beseated';
		$base_dir = JPATH_SITE;
		$language_tag = 'en-GB';
		$reload = true;
		$lang->load($extension, $base_dir, $language_tag, $reload);

		$emailSubject  = JText::sprintf('COM_BESEATED_VENUE_GUEST_LIST_REQUEST_EMAIL_SUBJECT');

		$emailBody        = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
							<html>
						<head>
						<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
						<title>Beseated</title>
						<style type="text/css" media="screen">
							p{margin: 0px !important; color: #000;}
							.ExternalClass p { color: #000; margin: 0px !important;}
							br{line-height: 12px;}
							.HOEnZb im p{margin:0;}
						</style>
						</head>
						<body>
						<table border="0" bgcolor="#FFFFFF" cellpadding="0" cellspacing="0" width="650" align="center" style="font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:20px;">
							<tbody>
						    	<tr>
						        	<td height="10"></td>
						        </tr>
						        <tr>
						        	<td align="center">
						            	<table width="630" border="0" cellpadding="0" cellspacing="0">
						                	<tr>
						                        <td align="right" colspan="2">
						                        	<table width="100%" border="0" cellpadding="0" cellspacing="0">
						                            	<tr>
						                                	<td align="left" width="90"><a href="'.$this->logo_link.'"><img src="'.JUri::base().'images/edms/images/beseated-logo.png"></a></td>
						                                  <td align="center" style="font-family:Arial, Helvetica, sans-serif;"></td>
						                                </tr>
						                            </table>
						                        </td>
						                    </tr>
						                    <tr>
						                    	<td colspan="2" height="20"></td>
						                    </tr>
						                    <tr>
						                        <td colspan="2" style="color:#000;">
						                        	<p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top:0;Margin-bottom:0;">Dear '.$venue_name.',<br><br>
						                            '.$userName.' has requested to be added to the guestlist on '.$booking_date.' for '.$total_guest.' people.
						</p><br>
						                            <p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top: 0; Margin-bottom: 0;">Kindly conﬁrm availability as soon as possible.
						</p><br>
						                            <p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top: 0; Margin-bottom: 0;">Beseated Support.</p>
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
						                        	<a href="#" style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif;">My Beseated ID</a>&nbsp;|&nbsp;<a style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="'.$this->support_link.'">Support</a>&nbsp;|&nbsp;<a style="text-decoration:none; font-size:14px; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="'.$this->privacy_policy_link.'">Privacy Policy</a>
						                        </td>
						                    </tr>
						                    <tr>
						                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000;">Copyright © 2016 <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>. All rights reserved.</td>
						                    </tr>
						                    <tr>
						                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000;">This email was sent by <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>, THUB, Dubai Silicon Oasis, Dubai, United Arab Emirates</td>
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

		self::sendEmail($email,$emailSubject, $emailBody);
	}

	public function NoShowLuxuryManagerMail($booking_owner_name,$element_name,$email,$elementType,$service_name)
	{
		$lang = JFactory::getLanguage();
		$extension = 'com_beseated';
		$base_dir = JPATH_SITE;
		$language_tag = 'en-GB';
		$reload = true;
		$lang->load($extension, $base_dir, $language_tag, $reload);

		if($elementType == 'Venue')
		{
				$emailSubject  = JText::sprintf(
								'COM_BESEATED_VENUE_NO_SHOW_BOOKING_MANAGER_EMAIL_SUBJECT',
								 $booking_owner_name,
								 $service_name
							);
		}
		else
		{

				$emailSubject  = JText::sprintf(
								'COM_BESEATED_LUXURY_NO_SHOW_BOOKING_MANAGER_EMAIL_SUBJECT',
								 $booking_owner_name,
								 $service_name
							);
		}


		$emailBody        = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
							<html>
							<head>
							<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
							<title>Beseated</title>
							<style type="text/css" media="screen">
								p{margin: 0px !important; color: #000;}
								.ExternalClass p { color: #000; margin: 0px !important;}
								br{line-height: 12px;}
								.HOEnZb im p{margin:0;}
							</style>
							</head>
							<body>
							<table border="0" bgcolor="#FFFFFF" cellpadding="0" cellspacing="0" width="650" align="center" style="font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:20px;">
								<tbody>
							    	<tr>
							        	<td height="10"></td>
							        </tr>
							        <tr>
							        	<td align="center">
							            	<table width="630" border="0" cellpadding="0" cellspacing="0">
							                	<tr>
							                        <td align="right" colspan="2">
							                        	<table width="100%" border="0" cellpadding="0" cellspacing="0">
							                            	<tr>
							                                	<td align="left" width="90"><a href="'.$this->logo_link.'"><img src="'.JUri::base().'images/edms/images/beseated-logo.png"></a></td>
							                                  <td align="center" style="font-family:Arial, Helvetica, sans-serif;"></td>
							                                </tr>
							                            </table>
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20"></td>
							                    </tr>
							                    <tr>
							                        <td colspan="2" style="color:#000;">
							                        	<p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top:0;Margin-bottom:0;">Dear '.$element_name.',<br><br>
							                            We are truly unfortunate of the situation that has occurred today with '.$booking_owner_name.' not committing to their reservation.
							</p><br>
							                            <p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top: 0; Margin-bottom: 0;">We understand that there are some occasions when the customer must miss a booking due to unforeseen circumstances beyond his or her control.
							</p><br>
							                    <p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top: 0; Margin-bottom: 0;">However, we do apologize on behalf of '.$booking_owner_name.', hoping it wont happen again.
							</p><br>
							<p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top: 0; Margin-bottom: 0;">Appreciate your understanding.</p><br>
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="4">
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2"><p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top: 0; Margin-bottom: 0;">Beseated Support.</p></td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="30">
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20" style=" border-top:1px solid #c09439;"></td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="color:#5f5f5f;">
							                        	<a href="#" style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif;">My Beseated ID</a>&nbsp;|&nbsp;<a style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="'.$this->support_link.'">Support</a>&nbsp;|&nbsp;<a style="text-decoration:none; font-size:14px; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="'.$this->privacy_policy_link.'">Privacy Policy</a>
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000;">Copyright © 2016 <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>. All rights reserved.</td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000;">This email was sent by <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>, THUB, Dubai Silicon Oasis, Dubai, United Arab Emirates</td>
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

		self::sendEmail($email,$emailSubject, $emailBody);
	}

	public function PaymentReceivedManagerMail($element_name,$username,$service_name,$booking_date,$booking_time,$isDayVenue = 1,$email)
	{
		$lang = JFactory::getLanguage();
		$extension = 'com_beseated';
		$base_dir = JPATH_SITE;
		$language_tag = 'en-GB';
		$reload = true;
		$lang->load($extension, $base_dir, $language_tag, $reload);

		$emailSubject  = JText::sprintf('COM_BESEATED_MANAGER_PAYMENT_RECEIVED_EMAIL_SUBJECT');


		$emailBody        = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
							<html>
							<head>
							<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
							<title>Beseated</title>
							<style type="text/css" media="screen">
								p{margin: 0px !important; color: #000;}
								.ExternalClass p { color: #000; margin: 0px !important;}
								br{line-height: 12px;}
								.HOEnZb im p{margin:0;}
							</style>
							</head>
							<body>
							<table border="0" bgcolor="#FFFFFF" cellpadding="0" cellspacing="0" width="650" align="center" style="font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:20px;">
								<tbody>
							    	<tr>
							        	<td height="10"></td>
							        </tr>
							        <tr>
							        	<td align="center">
							            	<table width="630" border="0" cellpadding="0" cellspacing="0">
							                	<tr>
							                        <td align="right" colspan="2">
							                        	<table width="100%" border="0" cellpadding="0" cellspacing="0">
							                            	<tr>
							                                	<td align="left" width="90"><a href="'.$this->logo_link.'"><img src="'.JUri::base().'images/edms/images/beseated-logo.png"></a></td>
							                                  <td align="center" style="font-family:Arial, Helvetica, sans-serif;"></td>
							                                </tr>
							                            </table>
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20"></td>
							                    </tr>
							                    <tr>
							                        <td colspan="2" style="color:#000;">
							                        	<p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top:0;Margin-bottom:0;">Dear '.$element_name.',<br><br>
							                            '.$username.'\'s reservation for '.$service_name.' on '.$booking_date.'';

							                            if(!empty($isDayVenue))
						                            	{
						                            			$emailBody .=  ' at '.$booking_time;
						                            	}

							                            $emailBody.=' has been successfully made.</p><br><p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top: 0; Margin-bottom: 0;">You can view this booking within your Beseated Booking Tab.
							</p><br>
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="3">
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2"><p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; Margin-top: 0; Margin-bottom: 0;">Beseated Support.</p></td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="30">
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20" style=" border-top:1px solid #c09439;"></td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="color:#5f5f5f;">
							                        	<a href="#" style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif;">My Beseated ID</a>&nbsp;|&nbsp;<a style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="'.$this->support_link.'">Support</a>&nbsp;|&nbsp;<a style="text-decoration:none; font-size:14px; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="'.$this->privacy_policy_link.'">Privacy Policy</a>
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000;">Copyright © 2016 <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>. All rights reserved.</td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000;">This email was sent by <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>, THUB, Dubai Silicon Oasis, Dubai, United Arab Emirates</td>
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

		self::sendEmail($email,$emailSubject, $emailBody);
	}

	public function venueBookingRequestUserMail($companyName,$venueThumb,$bookingDate,$bookingTime,$table_name,$currency_code,$total_price,$maleGuest,$femaleGuest,$username,$userEmail,$email)
	{
		//$email =  "jamal@tasolglobal.com";
		$venueThumb = (getimagesize($venueThumb)) ? $venueThumb : $this->blank_img;

		$lang = JFactory::getLanguage();
		$extension = 'com_beseated';
		$base_dir = JPATH_SITE;
		$language_tag = 'en-GB';
		$reload = true;
		$lang->load($extension, $base_dir, $language_tag, $reload);

		$emailSubject  = JText::sprintf('COM_BESEATED_TO_VENUE_BOOKING_REQUEST_EMAIL_SUBJECT',$companyName);

		$emailBody        = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
							<html>
							<head>
							<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
							<title>Beseated</title>
							<style type="text/css" media="screen">
								p{margin: 0 !important; color: #000;}
								.ExternalClass p { color: #000; margin: 0px;}
							</style>
							</head>
							<body>
							<table border="0" bgcolor="#FFFFFF" cellpadding="0" cellspacing="0" width="650" align="center" style="font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:20px;">
								<tbody>
							    	<tr>
							        	<td height="10"></td>
							        </tr>
							        <tr>
							        	<td align="center">
							            	<table width="630" border="0" cellpadding="0" cellspacing="0">
							                	<tr>
							                        <td align="center" colspan="2">
							                        	<table width="100%" cellpadding="0" cellspacing="0">
							                            	<tr>
							                                	<td align="left" width="100"><a href="'.$this->logo_link.'"><img src="'.JUri::base().'images/edms/images/beseated-logo.png"></a></td>
							                                    <td align="center"></td>
							                                </tr>
							                            </table>
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20"></td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2"><h3 style="margin:0; line-height:normal; color:#000; font-family:Arial, Helvetica, sans-serif; font-size:16px;">Dear '.$username.',</h3></td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20"></td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2">
							                        	<table width="100%" border="0" cellpadding="10" cellspacing="0" bgcolor="e3d1b0">
							                            	<tr>
							                                	<td colspan="2" style="padding-bottom:0px;"><h4 style="margin:0; line-height:normal; color:#000; font-family:Arial, Helvetica, sans-serif; font-weight:normal; font-size:20px;"><b>'.$companyName.'</b></h4></td>
							                                </tr>
							                                <tr>
							                                	<td width="320">
							                                    	<table width="130" cellpadding="0" cellspacing="0">
							                                        	<tr>
							                                            	<td style="border:1px solid #000;" height="80"><img src ='.$venueThumb.' height="80" width="130"></td>
							                                            </tr>
							                                        </table>
							                                    </td>
							                                    <td  style="margin:0; line-height:normal; color:#000; font-family:Arial, Helvetica, sans-serif; font-weight:normal; font-size:18px;">Status: Requested</td>
							                                </tr>
							                                <tr>
										                    	<td colspan="2"></td>
										                    </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40; font-family:Arial, Helvetica, sans-serif;color:#000;">Reservation Date</td>
							                                    <td style="border-bottom:1px solid #b78f40; font-family:Arial, Helvetica, sans-serif;color:#000;">'.$bookingDate.'</td>
							                                </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40; font-family:Arial, Helvetica, sans-serif; color:#000;">Reservation Time</td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$bookingTime.'</td>
							                                </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Your reservation</td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$table_name.' - Minimum '.$currency_code.' '.$total_price.'</td>
							                                </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Number of People </td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.($maleGuest+$femaleGuest).' Clients - '.$maleGuest.' Males / '.$femaleGuest.' Females</td>
							                                </tr>
							                                <tr>
							                                    <td style="font-family:Arial, Helvetica, sans-serif; color:#000;">Requested by</td>
							                                    <td style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$username.' (<a href="mailto:'.$userEmail.'" style="color:#000; text-decoration:none;">'.$userEmail.'</a>)</td>
							                                </tr>
							                            </table>
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20">
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20">
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" style="font-size:14px; margin:0; font-family:Arial, Helvetica, sans-serif; color:#000;">
							                        	We will notify you as soon as we hear a response.
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20">
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" style="font-size:14px; margin:0; font-family:Arial, Helvetica, sans-serif; color:#000;">
							                        	You can view your booking request status within your <a href="#" style="color:#b48b3b;">RSVP tab</a>.
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20">
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" style="font-size:14px; margin:0; font-family:Arial, Helvetica, sans-serif; color:#000;">
							                        	Beseated Support
							                        </td>
							                    </tr>

							                    <tr>
							                    	<td colspan="2" height="10">
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
							                        	<a href="#" style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif;">My Beseated ID</a>&nbsp;|&nbsp;<a style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="'.$this->support_link.'">Support</a>&nbsp;|&nbsp;<a style="text-decoration:none; font-size:14px; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="'.$this->privacy_policy_link.'">Privacy Policy</a>
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000;">Copyright © 2016 <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>. All rights reserved.</td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000;">This email was sent by <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>, THUB, Dubai Silicon Oasis, Dubai, United Arab Emirates</td>
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

		self::sendEmail($email,$emailSubject, $emailBody);
	}

	public function venueBookingRequestManagerMail($companyName,$venueThumb,$bookingDate,$bookingTime,$table_name,$currency_code,$total_price,$maleGuest,$femaleGuest,$username,$userEmail,$email)
	{
		//$email =  "jamal@tasolglobal.com";
		$venueThumb = (getimagesize($venueThumb)) ? $venueThumb : $this->blank_img;


		$lang = JFactory::getLanguage();
		$extension = 'com_beseated';
		$base_dir = JPATH_SITE;
		$language_tag = 'en-GB';
		$reload = true;
		$lang->load($extension, $base_dir, $language_tag, $reload);

		$emailSubject  = JText::sprintf('COM_BESEATED_TO_VENUE_BOOKING_REQUEST_EMAIL_SUBJECT',$companyName);

		$emailBody        = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
							<html>
							<head>
							<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
							<title>Beseated</title>
							<style type="text/css" media="screen">
								p{margin: 0 !important; color: #000;}
								.ExternalClass p { color: #000; margin: 0px;}
							</style>
							</head>
							<body>
							<table border="0" bgcolor="#FFFFFF" cellpadding="0" cellspacing="0" width="650" align="center" style="font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:20px;">
								<tbody>
							    	<tr>
							        	<td height="10"></td>
							        </tr>
							        <tr>
							        	<td align="center">
							            	<table width="630" border="0" cellpadding="0" cellspacing="0">
							                	<tr>
							                        <td align="center" colspan="2">
							                        	<table width="100%" cellpadding="0" cellspacing="0">
							                            	<tr>
							                                	<td align="left" width="100"><a href="'.$this->logo_link.'"><img src="'.JUri::base().'images/edms/images/beseated-logo.png"></a></td>
							                                    <td align="center"></td>
							                                </tr>
							                            </table>
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20"></td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2"><h3 style="margin:0; line-height:normal; color:#000; font-family:Arial, Helvetica, sans-serif; font-size:16px;">Dear '.$companyName.',</h3></td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20"></td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2">
							                        	<table width="100%" border="0" cellpadding="10" cellspacing="0" bgcolor="e3d1b0">
							                            	<tr>
							                                	<td colspan="2" style="padding-bottom:0px;"><h4 style="margin:0; line-height:normal; color:#000; font-family:Arial, Helvetica, sans-serif; font-weight:normal; font-size:20px;"><b>'.$companyName.'</b></h4></td>
							                                </tr>
							                                <tr>
							                                	<td width="320">
							                                    	<table width="130" cellpadding="0" cellspacing="0">
							                                        	<tr>
							                                            	<td style="border:1px solid #000;" height="80"><img src ='.$venueThumb.' height="80" width="130"></td>
							                                            </tr>
							                                        </table>
							                                    </td>
							                                    <td  style="margin:0; line-height:normal; color:#000; font-family:Arial, Helvetica, sans-serif; font-weight:normal; font-size:18px;">Status: Requested</td>
							                                </tr>
							                                <tr>
										                    	<td colspan="2"></td>
										                    </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40; font-family:Arial, Helvetica, sans-serif;color:#000;">Reservation Date</td>
							                                    <td style="border-bottom:1px solid #b78f40; font-family:Arial, Helvetica, sans-serif;color:#000;">'.$bookingDate.'</td>
							                                </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40; font-family:Arial, Helvetica, sans-serif; color:#000;">Reservation Time</td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$bookingTime.'</td>
							                                </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Your reservation</td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$table_name.' - Minimum '.$currency_code.' '.$total_price.'</td>
							                                </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Number of People </td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.($maleGuest+$femaleGuest).' Clients - '.$maleGuest.' Males / '.$femaleGuest.' Females</td>
							                                </tr>
							                                <tr>
							                                    <td style="font-family:Arial, Helvetica, sans-serif; color:#000;">Requested by</td>
							                                    <td style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$username.' (<a href="mailto:'.$userEmail.'" style="color:#000; text-decoration:none;">'.$userEmail.'</a>)</td>
							                                </tr>
							                            </table>
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20">
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20">
							                        </td>
							                    </tr>
							                   <tr>
							                    	<td colspan="2" style="font-size:14px; margin:0; font-family:Arial, Helvetica, sans-serif; color:#000;">
							                        	Please view your booking request within your RSVP tab to conﬁrm availability.
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20">
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" style="font-size:14px; margin:0; font-family:Arial, Helvetica, sans-serif; color:#000;">
							                        	Kindly conﬁrm availability as soon as possible.
							                        </td>
							                    </tr>

							                    <tr>
							                    	<td colspan="2" height="20">
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" style="font-size:14px; margin:0; font-family:Arial, Helvetica, sans-serif; color:#000;">
							                        	Beseated Support
							                        </td>
							                    </tr>

							                    <tr>
							                    	<td colspan="2" height="30">
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20" style=" border-top:1px solid #c09439;"></td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="color:#5f5f5f;">
							                        	<a href="#" style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif;">My Beseated ID</a>&nbsp;|&nbsp;<a style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="'.$this->support_link.'">Support</a>&nbsp;|&nbsp;<a style="text-decoration:none; font-size:14px; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="'.$this->privacy_policy_link.'">Privacy Policy</a>
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000;">Copyright © 2016 <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>. All rights reserved.</td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000;">This email was sent by <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>, THUB, Dubai Silicon Oasis, Dubai, United Arab Emirates</td>
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

		self::sendEmail($email,$emailSubject, $emailBody);
	}


	public function venueBookingAvailableUserMail($companyName,$venueThumb,$bookingDate,$bookingTime,$table_name,$currency_code,$total_price,$maleGuest,$femaleGuest,$username,$userEmail,$email)
	{
		$venueThumb = (getimagesize($venueThumb)) ? $venueThumb : $this->blank_img;
		//$email =  "jamal@tasolglobal.com";

		$lang = JFactory::getLanguage();
		$extension = 'com_beseated';
		$base_dir = JPATH_SITE;
		$language_tag = 'en-GB';
		$reload = true;
		$lang->load($extension, $base_dir, $language_tag, $reload);

		$emailSubject  = JText::sprintf('COM_BESEATED_TO_VENUE_BOOKING_AVAILABLE_EMAIL_SUBJECT',$companyName);

		$emailBody        = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
							<html>
							<head>
							<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
							<title>Beseated</title>
							<style type="text/css" media="screen">
								p{margin: 0 !important; color: #000;}
								.ExternalClass p { color: #000; margin: 0px;}
							</style>
							</head>
							<body>
							<table border="0" bgcolor="#FFFFFF" cellpadding="0" cellspacing="0" width="650" align="center" style="font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:20px;">
								<tbody>
							    	<tr>
							        	<td height="10"></td>
							        </tr>
							        <tr>
							        	<td align="center">
							            	<table width="630" border="0" cellpadding="0" cellspacing="0">
							                	<tr>
							                        <td align="center" colspan="2">
							                        	<table width="100%" cellpadding="0" cellspacing="0">
							                            	<tr>
							                                	<td align="left" width="100"><a href="'.$this->logo_link.'"><img src="'.JUri::base().'images/edms/images/beseated-logo.png"></a></td>
							                                    <td align="center"></td>
							                                </tr>
							                            </table>
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20"></td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2"><h3 style="margin:0; line-height:normal; color:#000; font-family:Arial, Helvetica, sans-serif; font-size:16px;">Dear '.$username.',</h3></td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20"></td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2">
							                        	<table width="100%" border="0" cellpadding="10" cellspacing="0" bgcolor="e3d1b0">
							                            	<tr>
							                                	<td colspan="2" style="padding-bottom:0px;"><h4 style="margin:0; line-height:normal; color:#000; font-family:Arial, Helvetica, sans-serif; font-weight:normal; font-size:20px;"><b>'.$companyName.'</b></h4></td>
							                                </tr>
							                                <tr>
							                                	<td width="320">
							                                    	<table width="130" cellpadding="0" cellspacing="0">
							                                        	<tr>
							                                            	<td style="border:1px solid #000;" height="80"><img src ='.$venueThumb.' height="80" width="130"></td>
							                                            </tr>
							                                        </table>
							                                    </td>
							                                    <td  style="margin:0; line-height:normal; color:#000; font-family:Arial, Helvetica, sans-serif; font-weight:normal; font-size:18px;">Status: Available</td>
							                                </tr>
							                                <tr>
										                    	<td colspan="2"></td>
										                    </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40; font-family:Arial, Helvetica, sans-serif;color:#000;">Reservation Date</td>
							                                    <td style="border-bottom:1px solid #b78f40; font-family:Arial, Helvetica, sans-serif;color:#000;">'.$bookingDate.'</td>
							                                </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40; font-family:Arial, Helvetica, sans-serif; color:#000;">Reservation Time</td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$bookingTime.'</td>
							                                </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Your reservation</td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$table_name.' - Minimum '.$currency_code.' '.$total_price.'</td>
							                                </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Number of People </td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.($maleGuest+$femaleGuest).' Clients - '.$maleGuest.' Males / '.$femaleGuest.' Females</td>
							                                </tr>
							                                <tr>
							                                    <td style="font-family:Arial, Helvetica, sans-serif; color:#000;">Requested by</td>
							                                    <td style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$username.' (<a href="mailto:'.$userEmail.'" style="color:#000; text-decoration:none;">'.$userEmail.'</a>)</td>
							                                </tr>
							                            </table>
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20">
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20">
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" style="font-size:14px; margin:0; font-family:Arial, Helvetica, sans-serif; color:#000;">
							                        	Please view your booking request within your RSVP tab to conﬁrm.
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20">
							                        </td>
							                    </tr>
		
							                    <tr>
							                    	<td colspan="2" height="20">
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" style="font-size:14px; margin:0; font-family:Arial, Helvetica, sans-serif; color:#000;">
							                        	Beseated Support
							                        </td>
							                    </tr>

							                    <tr>
							                    	<td colspan="2" height="10">
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
							                        	<a href="#" style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif;">My Beseated ID</a>&nbsp;|&nbsp;<a style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="'.$this->support_link.'">Support</a>&nbsp;|&nbsp;<a style="text-decoration:none; font-size:14px; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="'.$this->privacy_policy_link.'">Privacy Policy</a>
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000;">Copyright © 2016 <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>. All rights reserved.</td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000;">This email was sent by <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>, THUB, Dubai Silicon Oasis, Dubai, United Arab Emirates</td>
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
							</html>';

		self::sendEmail($email,$emailSubject, $emailBody);
	}

	public function venueBookingNotAvailableUserMail($companyName,$venueThumb,$bookingDate,$bookingTime,$table_name,$currency_code,$total_price,$maleGuest,$femaleGuest,$username,$userEmail,$email)
	{
		//$email =  "jamal@tasolglobal.com";
		$venueThumb = (getimagesize($venueThumb)) ? $venueThumb : $this->blank_img;

		$lang = JFactory::getLanguage();
		$extension = 'com_beseated';
		$base_dir = JPATH_SITE;
		$language_tag = 'en-GB';
		$reload = true;
		$lang->load($extension, $base_dir, $language_tag, $reload);

		$emailSubject  = JText::sprintf('COM_BESEATED_TO_VENUE_BOOKING_AVAILABLE_EMAIL_SUBJECT',$companyName);

		$emailBody        = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
							<html>
							<head>
							<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
							<title>Beseated</title>
							<style type="text/css" media="screen">
								p{margin: 0 !important; color: #000;}
								.ExternalClass p { color: #000; margin: 0px;}
							</style>
							</head>
							<body>
							<table border="0" bgcolor="#FFFFFF" cellpadding="0" cellspacing="0" width="650" align="center" style="font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:20px;">
								<tbody>
							    	<tr>
							        	<td height="10"></td>
							        </tr>
							        <tr>
							        	<td align="center">
							            	<table width="630" border="0" cellpadding="0" cellspacing="0">
							                	<tr>
							                        <td align="center" colspan="2">
							                        	<table width="100%" cellpadding="0" cellspacing="0">
							                            	<tr>
							                                	<td align="left" width="100"><a href="'.$this->logo_link.'"><img src="'.JUri::base().'images/edms/images/beseated-logo.png"></a></td>
							                                    <td align="center"></td>
							                                </tr>
							                            </table>
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20"></td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2"><h3 style="margin:0; line-height:normal; color:#000; font-family:Arial, Helvetica, sans-serif; font-size:16px;">Dear '.$username.',</h3></td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20"></td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2">
							                        	<table width="100%" border="0" cellpadding="10" cellspacing="0" bgcolor="e3d1b0">
							                            	<tr>
							                                	<td colspan="2" style="padding-bottom:0px;"><h4 style="margin:0; line-height:normal; color:#000; font-family:Arial, Helvetica, sans-serif; font-weight:normal; font-size:20px;"><b>'.$companyName.'</b></h4></td>
							                                </tr>
							                                <tr>
							                                	<td width="320">
							                                    	<table width="130" cellpadding="0" cellspacing="0">
							                                        	<tr>
							                                            	<td style="border:1px solid #000;" height="80"><img src ='.$venueThumb.' height="80" width="130"></td>
							                                            </tr>
							                                        </table>
							                                    </td>
							                                    <td  style="margin:0; line-height:normal; color:#000; font-family:Arial, Helvetica, sans-serif; font-weight:normal; font-size:18px;">Status: Not Available</td>
							                                </tr>
							                                <tr>
										                    	<td colspan="2"></td>
										                    </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40; font-family:Arial, Helvetica, sans-serif;color:#000;">Reservation Date</td>
							                                    <td style="border-bottom:1px solid #b78f40; font-family:Arial, Helvetica, sans-serif;color:#000;">'.$bookingDate.'</td>
							                                </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40; font-family:Arial, Helvetica, sans-serif; color:#000;">Reservation Time</td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$bookingTime.'</td>
							                                </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Your reservation</td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$table_name.' - Minimum '.$currency_code.' '.$total_price.'</td>
							                                </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Number of People </td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.($maleGuest+$femaleGuest).' Clients - '.$maleGuest.' Males / '.$femaleGuest.' Females</td>
							                                </tr>
							                                <tr>
							                                    <td style="font-family:Arial, Helvetica, sans-serif; color:#000;">Requested by</td>
							                                    <td style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$username.' (<a href="mailto:'.$userEmail.'" style="color:#000; text-decoration:none;">'.$userEmail.'</a>)</td>
							                                </tr>
							                            </table>
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20">
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20">
							                        </td>
							                    </tr>
							                    <tr>
		                                    	<td colspan="2" style="font-size:14px; margin:0; font-family:Arial, Helvetica, sans-serif; color:#000;">
		                                        	We are sorry, but it seems that the table you have requested is not available.
		                                        </td>
			                                    </tr>
			                                    <tr>
			                                    	<td colspan="2" height="20">
			                                        </td>
			                                    </tr>
			                                    <tr>
			                                    	<td colspan="2" style="font-size:14px; margin:0; font-family:Arial, Helvetica, sans-serif; color:#000;">
			                                        	Please try a different time or date, or you can try booking at another one of our exclusive partners.
			                                        </td>
			                                    </tr>
			                                    <tr>
			                                    	<td colspan="2" height="20">
			                                        </td>
			                                    </tr>
			                                    <tr>
			                                    	<td colspan="2" style="font-size:14px; margin:0; font-family:Arial, Helvetica, sans-serif; color:#000;">
			                                        	Beseated Support
			                                        </td>
			                                    </tr>

			                                    <tr>
			                                    	<td colspan="2" height="10">
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
			                                        	<a href="#" style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif;">My Beseated ID</a>&nbsp;|&nbsp;<a style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="'.$this->support_link.'">Support</a>&nbsp;|&nbsp;<a style="text-decoration:none; font-size:14px; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="'.$this->privacy_policy_link.'">Privacy Policy</a>
			                                        </td>
			                                    </tr>
			                                    <tr>
			                                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000;">Copyright © 2016 <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>. All rights reserved.</td>
			                                    </tr>
			                                    <tr>
			                                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000;">This email was sent by <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>, THUB, Dubai Silicon Oasis, Dubai, United Arab Emirates</td>
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
			                </html>';

		self::sendEmail($email,$emailSubject, $emailBody);
	}


	public function chauffeurBookingRequestUserMail($companyName,$thumb,$bookingDate,$bookingTime,$service_name,$pickupLocation,$dropLocation,$capacity,$username,$userEmail,$email)
	{
		//$email =  "jamal@tasolglobal.com";
		$thumb = (getimagesize($thumb)) ? $thumb : $this->blank_img;

		$lang = JFactory::getLanguage();
		$extension = 'com_beseated';
		$base_dir = JPATH_SITE;
		$language_tag = 'en-GB';
		$reload = true;
		$lang->load($extension, $base_dir, $language_tag, $reload);

		$emailSubject  = JText::sprintf('COM_BESEATED_TO_VENUE_BOOKING_AVAILABLE_EMAIL_SUBJECT',$companyName);

		$emailBody        = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
							<html>
							<head>
							<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
							<title>Beseated</title>
							<style type="text/css" media="screen">
								p{margin: 0 !important; color: #000;}
								.ExternalClass p { color: #000; margin: 0px;}
							</style>
							</head>
							<body>
							<table border="0" bgcolor="#FFFFFF" cellpadding="0" cellspacing="0" width="650" align="center" style="font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:20px;">
								<tbody>
							    	<tr>
							        	<td height="10"></td>
							        </tr>
							        <tr>
							        	<td align="center">
							            	<table width="630" border="0" cellpadding="0" cellspacing="0">
							                	<tr>
							                        <td align="center" colspan="2">
							                        	<table width="100%" cellpadding="0" cellspacing="0">
							                            	<tr>
							                                	<td align="left" width="100"><a href="'.$this->logo_link.'"><img src="'.JUri::base().'images/edms/images/beseated-logo.png"></a></td>
							                                    <td align="center"></td>
							                                </tr>
							                            </table>
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20"></td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2"><h3 style="margin:0; line-height:normal; color:#000; font-family:Arial, Helvetica, sans-serif; font-size:16px;">Dear '.$username.',</h3></td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20"></td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2">
							                        	<table width="100%" border="0" cellpadding="10" cellspacing="0" bgcolor="e3d1b0">
							                            	<tr>
							                                	<td colspan="2" style="padding-bottom:0px;"><h4 style="margin:0; line-height:normal; color:#000; font-family:Arial, Helvetica, sans-serif; font-weight:normal; font-size:20px;"><b>'.$companyName.'</b></h4></td>
							                                </tr>
							                                <tr>
							                                	<td width="320">
							                                    	<table width="130" cellpadding="0" cellspacing="0">
							                                        	<tr>
							                                            	<td style="border:1px solid #000;" height="80"><img src ='.$thumb.' height="80" width="130"></td>
							                                            </tr>
							                                        </table>
							                                    </td>
							                                    <td  style="margin:0; line-height:normal; color:#000; font-family:Arial, Helvetica, sans-serif; font-weight:normal; font-size:18px;">Status: Requested</td>
							                                </tr>
							                                <tr>
										                    	<td colspan="2"></td>
										                    </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40; font-family:Arial, Helvetica, sans-serif;color:#000;">Reservation Date</td>
							                                    <td style="border-bottom:1px solid #b78f40; font-family:Arial, Helvetica, sans-serif;color:#000;">'.$bookingDate.'</td>
							                                </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40; font-family:Arial, Helvetica, sans-serif; color:#000;">Pickup Time </td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$bookingTime.'</td>
							                                </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Chauffeur Name</td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$companyName.'</td>
							                                </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Chauffeur Type</td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$service_name.'</td>
							                                </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Pickup Location</td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$pickupLocation.'</td>
							                                </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Dropoff Location</td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$dropLocation.'</td>
							                                </tr>
							                                 <tr>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Capacity</td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$capacity.' People</td>
							                                </tr>
							                                <tr>
							                                    <td style="font-family:Arial, Helvetica, sans-serif; color:#000;">Booked by</td>
							                                    <td style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$username.' (<a href="mailto:'.$userEmail.'" style="color:#000; text-decoration:none;">'.$userEmail.'</a>)</td>
							                                </tr>
							                            </table>
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20">
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" style="font-size:14px; margin:0; font-family:Arial, Helvetica, sans-serif; color:#000;">
							                        	We will notify you as soon as we hear a response.
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="10">
							                        </td>
							                    </tr>
							                       <tr>
							                    	<td colspan="2" style="font-size:14px; margin:0; font-family:Arial, Helvetica, sans-serif; color:#000;">
							                        	You can view your booking request status within your <a href="#" style="color:#b48b3b;">RSVP tab</a>.
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20">
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" style="font-size:14px; margin:0; font-family:Arial, Helvetica, sans-serif; color:#000;">
							                        	Beseated Support
							                        </td>
							                    </tr>

							                    <tr>
							                    	<td colspan="2" height="30">
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20" style=" border-top:1px solid #c09439;"></td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="color:#5f5f5f;">
							                        	<a href="#" style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif;">My Beseated ID</a>&nbsp;|&nbsp;<a style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="'.$this->support_link.'">Support</a>&nbsp;|&nbsp;<a style="text-decoration:none; font-size:14px; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="'.$this->privacy_policy_link.'">Privacy Policy</a>
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000;">Copyright © 2016 <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>. All rights reserved.</td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000;">This email was sent by <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>, THUB, Dubai Silicon Oasis, Dubai, United Arab Emirates</td>
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

		self::sendEmail($email,$emailSubject, $emailBody);
	}

	public function chauffeurBookingRequestManagerMail($companyName,$thumb,$bookingDate,$bookingTime,$service_name,$pickupLocation,$dropLocation,$capacity,$username,$userEmail,$email)
	{
		//$email =  "jamal@tasolglobal.com";
		$thumb = (getimagesize($thumb)) ? $thumb : $this->blank_img;

		$lang = JFactory::getLanguage();
		$extension = 'com_beseated';
		$base_dir = JPATH_SITE;
		$language_tag = 'en-GB';
		$reload = true;
		$lang->load($extension, $base_dir, $language_tag, $reload);

		$emailSubject  = JText::sprintf('COM_BESEATED_TO_VENUE_BOOKING_AVAILABLE_EMAIL_SUBJECT',$companyName);

		$emailBody        = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
							<html>
							<head>
							<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
							<title>Beseated</title>
							<style type="text/css" media="screen">
								p{margin: 0 !important; color: #000;}
								.ExternalClass p { color: #000; margin: 0px;}
							</style>
							</head>
							<body>
							<table border="0" bgcolor="#FFFFFF" cellpadding="0" cellspacing="0" width="650" align="center" style="font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:20px;">
								<tbody>
							    	<tr>
							        	<td height="10"></td>
							        </tr>
							        <tr>
							        	<td align="center">
							            	<table width="630" border="0" cellpadding="0" cellspacing="0">
							                	<tr>
							                        <td align="center" colspan="2">
							                        	<table width="100%" cellpadding="0" cellspacing="0">
							                            	<tr>
							                                	<td align="left" width="100"><a href="'.$this->logo_link.'"><img src="'.JUri::base().'images/edms/images/beseated-logo.png"></a></td>
							                                    <td align="center"></td>
							                                </tr>
							                            </table>
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20"></td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2"><h3 style="margin:0; line-height:normal; color:#000; font-family:Arial, Helvetica, sans-serif; font-size:16px;">Dear '.$companyName.',</h3></td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20"></td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2">
							                        	<table width="100%" border="0" cellpadding="10" cellspacing="0" bgcolor="e3d1b0">
							                            	<tr>
							                                	<td colspan="2" style="padding-bottom:0px;"><h4 style="margin:0; line-height:normal; color:#000; font-family:Arial, Helvetica, sans-serif; font-weight:normal; font-size:20px;"><b>'.$companyName.'</b></h4></td>
							                                </tr>
							                                <tr>
							                                	<td width="320">
							                                    	<table width="130" cellpadding="0" cellspacing="0">
							                                        	<tr>
							                                            	<td style="border:1px solid #000;" height="80"><img src ='.$thumb.' height="80" width="130"></td>
							                                            </tr>
							                                        </table>
							                                    </td>
							                                    <td  style="margin:0; line-height:normal; color:#000; font-family:Arial, Helvetica, sans-serif; font-weight:normal; font-size:18px;">Status: Requested</td>
							                                </tr>
							                                <tr>
										                    	<td colspan="2"></td>
										                    </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40; font-family:Arial, Helvetica, sans-serif;color:#000;">Reservation Date</td>
							                                    <td style="border-bottom:1px solid #b78f40; font-family:Arial, Helvetica, sans-serif;color:#000;">'.$bookingDate.'</td>
							                                </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40; font-family:Arial, Helvetica, sans-serif; color:#000;">Pickup Time </td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$bookingTime.'</td>
							                                </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Chauffeur Name</td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$companyName.'</td>
							                                </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Chauffeur Type</td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$service_name.'</td>
							                                </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Pickup Location</td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$pickupLocation.'</td>
							                                </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Dropoff Location</td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$dropLocation.'</td>
							                                </tr>
							                                 <tr>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Capacity</td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$capacity.' People</td>
							                                </tr>
							                                <tr>
							                                    <td style="font-family:Arial, Helvetica, sans-serif; color:#000;">Booked by</td>
							                                    <td style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$username.' (<a href="mailto:'.$userEmail.'" style="color:#000; text-decoration:none;">'.$userEmail.'</a>)</td>
							                                </tr>
							                            </table>
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20">
							                        </td>
							                    </tr>
							                  <tr>
							                    	<td colspan="2" style="font-size:14px; margin:0; font-family:Arial, Helvetica, sans-serif; color:#000;">
							                        	Please view your booking request within your RSVP tab to conﬁrm availability.
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20">
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" style="font-size:14px; margin:0; font-family:Arial, Helvetica, sans-serif; color:#000;">
							                        	Kindly conﬁrm availability as soon as possible.
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20">
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" style="font-size:14px; margin:0; font-family:Arial, Helvetica, sans-serif; color:#000;">
							                        	Beseated Support
							                        </td>
							                    </tr>

							                    <tr>
							                    	<td colspan="2" height="30">
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20" style=" border-top:1px solid #c09439;"></td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="color:#5f5f5f;">
							                        	<a href="#" style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif;">My Beseated ID</a>&nbsp;|&nbsp;<a style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="'.$this->support_link.'">Support</a>&nbsp;|&nbsp;<a style="text-decoration:none; font-size:14px; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="'.$this->privacy_policy_link.'">Privacy Policy</a>
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000;">Copyright © 2016 <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>. All rights reserved.</td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000;">This email was sent by <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>, THUB, Dubai Silicon Oasis, Dubai, United Arab Emirates</td>
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

		self::sendEmail($email,$emailSubject, $emailBody);
	}

	public function chauffeurBookingNotAvailableUserMail($companyName,$thumb,$bookingDate,$bookingTime,$service_name,$pickupLocation,$dropLocation,$capacity,$username,$userEmail,$email)
	{
		//$email =  "jamal@tasolglobal.com";
		$thumb = (getimagesize($thumb)) ? $thumb : $this->blank_img;

		$lang = JFactory::getLanguage();
		$extension = 'com_beseated';
		$base_dir = JPATH_SITE;
		$language_tag = 'en-GB';
		$reload = true;
		$lang->load($extension, $base_dir, $language_tag, $reload);

		$emailSubject  = JText::sprintf('COM_BESEATED_TO_VENUE_BOOKING_AVAILABLE_EMAIL_SUBJECT',$companyName);

		$emailBody        = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
							<html>
							<head>
							<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
							<title>Beseated</title>
							<style type="text/css" media="screen">
								p{margin: 0 !important; color: #000;}
								.ExternalClass p { color: #000; margin: 0px;}
							</style>
							</head>
							<body>
							<table border="0" bgcolor="#FFFFFF" cellpadding="0" cellspacing="0" width="650" align="center" style="font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:20px;">
								<tbody>
							    	<tr>
							        	<td height="10"></td>
							        </tr>
							        <tr>
							        	<td align="center">
							            	<table width="630" border="0" cellpadding="0" cellspacing="0">
							                	<tr>
							                        <td align="center" colspan="2">
							                        	<table width="100%" cellpadding="0" cellspacing="0">
							                            	<tr>
							                                	<td align="left" width="100"><a href="'.$this->logo_link.'"><img src="'.JUri::base().'images/edms/images/beseated-logo.png"></a></td>
							                                    <td align="center"></td>
							                                </tr>
							                            </table>
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20"></td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2"><h3 style="margin:0; line-height:normal; color:#000; font-family:Arial, Helvetica, sans-serif; font-size:16px;">Dear '.$username.',</h3></td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20"></td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2">
							                        	<table width="100%" border="0" cellpadding="10" cellspacing="0" bgcolor="e3d1b0">
							                            	<tr>
							                                	<td colspan="2" style="padding-bottom:0px;"><h4 style="margin:0; line-height:normal; color:#000; font-family:Arial, Helvetica, sans-serif; font-weight:normal; font-size:20px;"><b>'.$companyName.'</b></h4></td>
							                                </tr>
							                                <tr>
							                                	<td width="320">
							                                    	<table width="130" cellpadding="0" cellspacing="0">
							                                        	<tr>
							                                            	<td style="border:1px solid #000;" height="80"><img src ='.$thumb.' height="80" width="130"></td>
							                                            </tr>
							                                        </table>
							                                    </td>
							                                    <td  style="margin:0; line-height:normal; color:#000; font-family:Arial, Helvetica, sans-serif; font-weight:normal; font-size:18px;">Status: Not Available</td>
							                                </tr>
							                                <tr>
										                    	<td colspan="2"></td>
										                    </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40; font-family:Arial, Helvetica, sans-serif;color:#000;">Reservation Date</td>
							                                    <td style="border-bottom:1px solid #b78f40; font-family:Arial, Helvetica, sans-serif;color:#000;">'.$bookingDate.'</td>
							                                </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40; font-family:Arial, Helvetica, sans-serif; color:#000;">Pickup Time </td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$bookingTime.'</td>
							                                </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Chauffeur Name</td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$companyName.'</td>
							                                </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Chauffeur Type</td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$service_name.'</td>
							                                </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Pickup Location</td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$pickupLocation.'</td>
							                                </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Dropoff Location</td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$dropLocation.'</td>
							                                </tr>
							                                 <tr>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Capacity</td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$capacity.' People</td>
							                                </tr>
							                                <tr>
							                                    <td style="font-family:Arial, Helvetica, sans-serif; color:#000;">Booked by</td>
							                                    <td style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$username.' (<a href="mailto:'.$userEmail.'" style="color:#000; text-decoration:none;">'.$userEmail.'</a>)</td>
							                                </tr>
							                            </table>
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20">
							                        </td>
							                    </tr>
							                    <tr>
						                    	<td colspan="2" style="font-size:14px; margin:0; font-family:Arial, Helvetica, sans-serif; color:#000;">
						                        	We are sorry, but it seems that the chauffeur you have requested is not available.
						                        </td>
						                        </tr>
							                    <tr>
							                    	<td colspan="2" height="10">
							                        </td>
							                    </tr>
							                       <tr>
							                    	<td colspan="2" style="font-size:14px; margin:0; font-family:Arial, Helvetica, sans-serif; color:#000;">
							                        	Please try a different time or date, or you can try booking at another one of our exclusive partners.
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20">
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" style="font-size:14px; margin:0; font-family:Arial, Helvetica, sans-serif; color:#000;">
							                        	Beseated Support
							                        </td>
							                    </tr>

							                    <tr>
							                    	<td colspan="2" height="30">
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20" style=" border-top:1px solid #c09439;"></td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="color:#5f5f5f;">
							                        	<a href="#" style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif;">My Beseated ID</a>&nbsp;|&nbsp;<a style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="'.$this->support_link.'">Support</a>&nbsp;|&nbsp;<a style="text-decoration:none; font-size:14px; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="'.$this->privacy_policy_link.'">Privacy Policy</a>
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000;">Copyright © 2016 <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>. All rights reserved.</td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000;">This email was sent by <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>, THUB, Dubai Silicon Oasis, Dubai, United Arab Emirates</td>
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

		self::sendEmail($email,$emailSubject, $emailBody);
	}

	public function chauffeurBookingAvailableUserMail($companyName,$thumb,$bookingDate,$bookingTime,$service_name,$pickupLocation,$dropLocation,$capacity,$username,$userEmail,$email)
	{
		//$email =  "jamal@tasolglobal.com";
		$thumb = (getimagesize($thumb)) ? $thumb : $this->blank_img;

		$lang = JFactory::getLanguage();
		$extension = 'com_beseated';
		$base_dir = JPATH_SITE;
		$language_tag = 'en-GB';
		$reload = true;
		$lang->load($extension, $base_dir, $language_tag, $reload);

		$emailSubject  = JText::sprintf('COM_BESEATED_TO_VENUE_BOOKING_AVAILABLE_EMAIL_SUBJECT',$companyName);

		$emailBody        = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
							<html>
							<head>
							<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
							<title>Beseated</title>
							<style type="text/css" media="screen">
								p{margin: 0 !important; color: #000;}
								.ExternalClass p { color: #000; margin: 0px;}
							</style>
							</head>
							<body>
							<table border="0" bgcolor="#FFFFFF" cellpadding="0" cellspacing="0" width="650" align="center" style="font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:20px;">
								<tbody>
							    	<tr>
							        	<td height="10"></td>
							        </tr>
							        <tr>
							        	<td align="center">
							            	<table width="630" border="0" cellpadding="0" cellspacing="0">
							                	<tr>
							                        <td align="center" colspan="2">
							                        	<table width="100%" cellpadding="0" cellspacing="0">
							                            	<tr>
							                                	<td align="left" width="100"><a href="'.$this->logo_link.'"><img src="'.JUri::base().'images/edms/images/beseated-logo.png"></a></td>
							                                    <td align="center"></td>
							                                </tr>
							                            </table>
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20"></td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2"><h3 style="margin:0; line-height:normal; color:#000; font-family:Arial, Helvetica, sans-serif; font-size:16px;">Dear '.$username.',</h3></td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20"></td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2">
							                        	<table width="100%" border="0" cellpadding="10" cellspacing="0" bgcolor="e3d1b0">
							                            	<tr>
							                                	<td colspan="2" style="padding-bottom:0px;"><h4 style="margin:0; line-height:normal; color:#000; font-family:Arial, Helvetica, sans-serif; font-weight:normal; font-size:20px;"><b>'.$companyName.'</b></h4></td>
							                                </tr>
							                                <tr>
							                                	<td width="320">
							                                    	<table width="130" cellpadding="0" cellspacing="0">
							                                        	<tr>
							                                            	<td style="border:1px solid #000;" height="80"><img src ='.$thumb.' height="80" width="130"></td>
							                                            </tr>
							                                        </table>
							                                    </td>
							                                    <td  style="margin:0; line-height:normal; color:#000; font-family:Arial, Helvetica, sans-serif; font-weight:normal; font-size:18px;">Status: Available</td>
							                                </tr>
							                                <tr>
										                    	<td colspan="2"></td>
										                    </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40; font-family:Arial, Helvetica, sans-serif;color:#000;">Reservation Date</td>
							                                    <td style="border-bottom:1px solid #b78f40; font-family:Arial, Helvetica, sans-serif;color:#000;">'.$bookingDate.'</td>
							                                </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40; font-family:Arial, Helvetica, sans-serif; color:#000;">Pickup Time </td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$bookingTime.'</td>
							                                </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Chauffeur Name</td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$companyName.'</td>
							                                </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Chauffeur Type</td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$service_name.'</td>
							                                </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Pickup Location</td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$pickupLocation.'</td>
							                                </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Dropoff Location</td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$dropLocation.'</td>
							                                </tr>
							                                 <tr>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Capacity</td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$capacity.' People</td>
							                                </tr>
							                                <tr>
							                                    <td style="font-family:Arial, Helvetica, sans-serif; color:#000;">Booked by</td>
							                                    <td style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$username.' (<a href="mailto:'.$userEmail.'" style="color:#000; text-decoration:none;">'.$userEmail.'</a>)</td>
							                                </tr>
							                            </table>
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20">
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" style="font-size:14px; margin:0; font-family:Arial, Helvetica, sans-serif; color:#000;">
							                        	Please view your booking request within your RSVP tab to pay.
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20">
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" style="font-size:14px; margin:0; font-family:Arial, Helvetica, sans-serif; color:#000;">
							                        	Beseated Support
							                        </td>
							                    </tr>

							                    <tr>
							                    	<td colspan="2" height="30">
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20" style=" border-top:1px solid #c09439;"></td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="color:#5f5f5f;">
							                        	<a href="#" style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif;">My Beseated ID</a>&nbsp;|&nbsp;<a style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="'.$this->support_link.'">Support</a>&nbsp;|&nbsp;<a style="text-decoration:none; font-size:14px; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="'.$this->privacy_policy_link.'">Privacy Policy</a>
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000;">Copyright © 2016 <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>. All rights reserved.</td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000;">This email was sent by <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>, THUB, Dubai Silicon Oasis, Dubai, United Arab Emirates</td>
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

		self::sendEmail($email,$emailSubject, $emailBody);
	}


	public function protectionBookingRequestUserMail($companyName,$thumb,$bookingDate,$bookingTime,$totalHours,$totalGuard,$username,$userEmail,$currency_code,$price_per_hours,$total_price,$email)
	{
		$thumb = (getimagesize($thumb)) ? $thumb : $this->blank_img;

		$lang = JFactory::getLanguage();
		$extension = 'com_beseated';
		$base_dir = JPATH_SITE;
		$language_tag = 'en-GB';
		$reload = true;
		$lang->load($extension, $base_dir, $language_tag, $reload);

		$emailSubject  = JText::sprintf('COM_BESEATED_TO_VENUE_BOOKING_AVAILABLE_EMAIL_SUBJECT',$companyName);

		$emailBody        = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
							<html>
							<head>
							<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
							<title>Beseated</title>
							<style type="text/css" media="screen">
								p{margin: 0 !important; color: #000;}
								.ExternalClass p { color: #000; margin: 0px;}
							</style>
							</head>
							<body>
							<table border="0" bgcolor="#FFFFFF" cellpadding="0" cellspacing="0" width="650" align="center" style="font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:20px;">
								<tbody>
							    	<tr>
							        	<td height="10"></td>
							        </tr>
							        <tr>
							        	<td align="center">
							            	<table width="630" border="0" cellpadding="0" cellspacing="0">
							                	<tr>
							                        <td align="center" colspan="2">
							                        	<table width="100%" cellpadding="0" cellspacing="0">
							                            	<tr>
							                                	<td align="left" width="100"><a href="'.$this->logo_link.'"><img src="'.JUri::base().'images/edms/images/beseated-logo.png"></a></td>
							                                    <td align="center"></td>
							                                </tr>
							                            </table>
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20"></td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2"><h3 style="margin:0; line-height:normal; color:#000; font-family:Arial, Helvetica, sans-serif; font-size:16px;">Dear '.$username.',</h3></td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20"></td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2">
							                        	<table width="100%" border="0" cellpadding="10" cellspacing="0" bgcolor="e3d1b0">
							                            	<tr>
							                                	<td colspan="2" style="padding-bottom:0px;"><h4 style="margin:0; line-height:normal; color:#000; font-family:Arial, Helvetica, sans-serif; font-weight:normal; font-size:20px;"><b>'.$companyName.'</b></h4></td>
							                                </tr>
							                                <tr>
							                                	<td width="320">
							                                    	<table width="130" cellpadding="0" cellspacing="0">
							                                        	<tr>
							                                            	<td style="border:1px solid #000;" height="80"><img src ='.$thumb.' height="80" width="130"></td>
							                                            </tr>
							                                        </table>
							                                    </td>
							                                    <td  style="margin:0; line-height:normal; color:#000; font-family:Arial, Helvetica, sans-serif; font-weight:normal; font-size:18px;">Status: Requested</td>
							                                </tr>
							                                <tr>
										                    	<td colspan="2"></td>
										                    </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40; font-family:Arial, Helvetica, sans-serif;color:#000;">Reservation Date</td>
							                                    <td style="border-bottom:1px solid #b78f40; font-family:Arial, Helvetica, sans-serif;color:#000;">'.$bookingDate.'</td>
							                                </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40; font-family:Arial, Helvetica, sans-serif; color:#000;">Reservation Time </td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$bookingTime.'</td>
							                                </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Bodyguard Name</td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$companyName.'</td>
							                                </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Bodyguard Quantity</td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$totalGuard.' Bodyguards</td>
							                                </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Hours</td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$totalHours.' Hours</td>
							                                </tr>
							                                <tr>
							                                    <td style="font-family:Arial, Helvetica, sans-serif; color:#000;">Booked by</td>
							                                    <td style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$username.' (<a href="mailto:'.$userEmail.'" style="color:#000; text-decoration:none;">'.$userEmail.'</a>)</td>
							                                </tr>
							                                <tr>
							                                	<td colspan="2">
							                                    	<table width="100%" border="0" cellpadding="0" cellspacing="0">
							                                        	<tr>
							                                            	<td width="1%"></td>
							                                                <td width="98%">
																				<table width="100%" border="0" cellpadding="6" cellspacing="0">
							                                                        <tr>
							                                                        	<td width="180" style="font-size:18px; color:#000; font-family:Arial, Helvetica, sans-serif;">LUXURY</td>
							                                                            <td width="157" style="font-size:18px; color:#000; font-family:Arial, Helvetica, sans-serif;">PRICE/HOUR </td>
							                                                            <td width="100" style="font-size:18px; color:#000; font-family:Arial, Helvetica, sans-serif;">HOURS</td>
							                                                            <td style="font-size:18px; color:#000; font-family:Arial, Helvetica, sans-serif;">TOTAL</td>
																					<tr>
																						<td colspan="4">
																							<table width="100%" cellpadding="0" cellspacing="0">
										                                                        <tr>
										                                                        	<td width="190" style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$companyName.'</td>
										                                                            <td width="170" style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$currency_code.' '.$price_per_hours.' </td>
										                                                            <td width="115" style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$totalHours.' Hours</td>
							                                                                        <td style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$currency_code.' '.$total_price.'</td>
										                                                        </tr>
																							</table>
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
							                    	<td colspan="2" style="font-size:14px; margin:0; font-family:Arial, Helvetica, sans-serif; color:#000;">
							                        	 We will notify you as soon as we hear a response.
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20">
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td style="font-size:14px; margin:0; font-family:Arial, Helvetica, sans-serif; color:#000;" colspan="2">You can view your booking request status within your <a style="color:#b48b3b;" href="#">RSVP tab</a>.
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20">
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" style="color:#000;">
							                        	Beseated Support
							                        </td>
							                    </tr>

							                    <tr>
							                    	<td colspan="2" height="30">
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20" style=" border-top:1px solid #c09439;"></td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="color:#5f5f5f;">
							                        	<a href="#" style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif;">My Beseated ID</a>&nbsp;|&nbsp;<a style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="'.$this->support_link.'">Support</a>&nbsp;|&nbsp;<a style="text-decoration:none; font-size:14px; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="'.$this->privacy_policy_link.'">Privacy Policy</a>
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000;">Copyright © 2016 <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>. All rights reserved.</td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000;">This email was sent by <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>, THUB, Dubai Silicon Oasis, Dubai, United Arab Emirates</td>
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

		self::sendEmail($email,$emailSubject, $emailBody);
	}

	public function protectionBookingRequestManagerMail($companyName,$thumb,$bookingDate,$bookingTime,$totalHours,$totalGuard,$username,$userEmail,$currency_code,$price_per_hours,$total_price,$email)
	{
		//$email =  "tasol.developer@gmail.com";
		//$email =  "jamal@tasolglobal.com";
		$thumb = (getimagesize($thumb)) ? $thumb : $this->blank_img;

		$lang = JFactory::getLanguage();
		$extension = 'com_beseated';
		$base_dir = JPATH_SITE;
		$language_tag = 'en-GB';
		$reload = true;
		$lang->load($extension, $base_dir, $language_tag, $reload);

		$emailSubject  = JText::sprintf('COM_BESEATED_TO_VENUE_BOOKING_AVAILABLE_EMAIL_SUBJECT',$companyName);

		$emailBody        = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
							<html>
							<head>
							<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
							<title>Beseated</title>
							<style type="text/css" media="screen">
								p{margin: 0 !important; color: #000;}
								.ExternalClass p { color: #000; margin: 0px;}
							</style>
							</head>
							<body>
							<table border="0" bgcolor="#FFFFFF" cellpadding="0" cellspacing="0" width="650" align="center" style="font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:20px;">
								<tbody>
							    	<tr>
							        	<td height="10"></td>
							        </tr>
							        <tr>
							        	<td align="center">
							            	<table width="630" border="0" cellpadding="0" cellspacing="0">
							                	<tr>
							                        <td align="center" colspan="2">
							                        	<table width="100%" cellpadding="0" cellspacing="0">
							                            	<tr>
							                                	<td align="left" width="100"><a href="'.$this->logo_link.'"><img src="'.JUri::base().'images/edms/images/beseated-logo.png"></a></td>
							                                    <td align="center"></td>
							                                </tr>
							                            </table>
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20"></td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2"><h3 style="margin:0; line-height:normal; color:#000; font-family:Arial, Helvetica, sans-serif; font-size:16px;">Dear '.$companyName.',</h3></td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20"></td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2">
							                        	<table width="100%" border="0" cellpadding="10" cellspacing="0" bgcolor="e3d1b0">
							                            	<tr>
							                                	<td colspan="2" style="padding-bottom:0px;"><h4 style="margin:0; line-height:normal; color:#000; font-family:Arial, Helvetica, sans-serif; font-weight:normal; font-size:20px;"><b>'.$companyName.'</b></h4></td>
							                                </tr>
							                                <tr>
							                                	<td width="320">
							                                    	<table width="130" cellpadding="0" cellspacing="0">
							                                        	<tr>
							                                            	<td style="border:1px solid #000;" height="80"><img src ='.$thumb.' height="80" width="130"></td>
							                                            </tr>
							                                        </table>
							                                    </td>
							                                    <td  style="margin:0; line-height:normal; color:#000; font-family:Arial, Helvetica, sans-serif; font-weight:normal; font-size:18px;">Status: Requested</td>
							                                </tr>
							                                <tr>
										                    	<td colspan="2"></td>
										                    </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40; font-family:Arial, Helvetica, sans-serif;color:#000;">Reservation Date</td>
							                                    <td style="border-bottom:1px solid #b78f40; font-family:Arial, Helvetica, sans-serif;color:#000;">'.$bookingDate.'</td>
							                                </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40; font-family:Arial, Helvetica, sans-serif; color:#000;">Reservation Time </td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$bookingTime.'</td>
							                                </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Bodyguard Name</td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$companyName.'</td>
							                                </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Number of Bodyguards</td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$totalGuard.' Bodyguards</td>
							                                </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Hours</td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$totalHours.' Hours</td>
							                                </tr>
							                                <tr>
							                                    <td style="font-family:Arial, Helvetica, sans-serif; color:#000;">Booked by</td>
							                                    <td style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$username.' (<a href="mailto:'.$userEmail.'" style="color:#000; text-decoration:none;">'.$userEmail.'</a>)</td>
							                                </tr>
							                                <tr>
							                                	<td colspan="2">
							                                    	<table width="100%" border="0" cellpadding="0" cellspacing="0">
							                                        	<tr>
							                                            	<td width="1%"></td>
							                                                <td width="98%">
																				<table width="100%" border="0" cellpadding="6" cellspacing="0">
							                                                        <tr>
							                                                        	<td width="180" style="font-size:18px; color:#000; font-family:Arial, Helvetica, sans-serif;">LUXURY</td>
							                                                            <td width="157" style="font-size:18px; color:#000; font-family:Arial, Helvetica, sans-serif;">PRICE/HOUR </td>
							                                                            <td width="100" style="font-size:18px; color:#000; font-family:Arial, Helvetica, sans-serif;">HOURS</td>
							                                                            <td style="font-size:18px; color:#000; font-family:Arial, Helvetica, sans-serif;">TOTAL</td>
																					<tr>
																						<td colspan="4">
																							<table width="100%" cellpadding="0" cellspacing="0">
										                                                        <tr>
										                                                        	<td width="190" style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$companyName.'</td>
										                                                            <td width="170" style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$currency_code.' '.$price_per_hours.' </td>
										                                                            <td width="115" style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$totalHours.' Hours</td>
							                                                                        <td style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$currency_code.' '.$total_price.'</td>
										                                                        </tr>
																							</table>
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
							                    	<td colspan="2" style="font-size:14px; margin:0; font-family:Arial, Helvetica, sans-serif; color:#000;">
							                        	Please view your booking request within your RSVP tab to conﬁrm availability.
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20">
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" style="font-size:14px; margin:0; font-family:Arial, Helvetica, sans-serif; color:#000;">
							                        	Kindly conﬁrm availability as soon as possible.
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20">
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" style="font-size:14px; margin:0; font-family:Arial, Helvetica, sans-serif; color:#000;">
							                        	Beseated Support
							                        </td>
							                    </tr>

							                    <tr>
							                    	<td colspan="2" height="30">
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20" style=" border-top:1px solid #c09439;"></td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="color:#5f5f5f;">
							                        	<a href="#" style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif;">My Beseated ID</a>&nbsp;|&nbsp;<a style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="'.$this->support_link.'">Support</a>&nbsp;|&nbsp;<a style="text-decoration:none; font-size:14px; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="'.$this->privacy_policy_link.'">Privacy Policy</a>
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000;">Copyright © 2016 <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>. All rights reserved.</td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000;">This email was sent by <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>, THUB, Dubai Silicon Oasis, Dubai, United Arab Emirates</td>
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

		self::sendEmail($email,$emailSubject, $emailBody);
	}


	public function protectionBookingNotAvailableUserMail($username,$companyName,$thumb,$bookingDate,$bookingTime,$totalHours,$totalGuard,$userEmail,$currency_code,$price_per_hours,$total_price,$email)
	{
		$thumb = (getimagesize($thumb)) ? $thumb : $this->blank_img;

		$lang = JFactory::getLanguage();
		$extension = 'com_beseated';
		$base_dir = JPATH_SITE;
		$language_tag = 'en-GB';
		$reload = true;
		$lang->load($extension, $base_dir, $language_tag, $reload);

		$emailSubject  = JText::sprintf('COM_BESEATED_TO_VENUE_BOOKING_AVAILABLE_EMAIL_SUBJECT',$companyName);

		$emailBody        = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
							<html>
							<head>
							<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
							<title>Beseated</title>
							<style type="text/css" media="screen">
								p{margin: 0 !important; color: #000;}
								.ExternalClass p { color: #000; margin: 0px;}
							</style>
							</head>
							<body>
							<table border="0" bgcolor="#FFFFFF" cellpadding="0" cellspacing="0" width="650" align="center" style="font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:20px;">
								<tbody>
							    	<tr>
							        	<td height="10"></td>
							        </tr>
							        <tr>
							        	<td align="center">
							            	<table width="630" border="0" cellpadding="0" cellspacing="0">
							                	<tr>
							                        <td align="center" colspan="2">
							                        	<table width="100%" cellpadding="0" cellspacing="0">
							                            	<tr>
							                                	<td align="left" width="100"><a href="'.$this->logo_link.'"><img src="'.JUri::base().'images/edms/images/beseated-logo.png"></a></td>
							                                    <td align="center"></td>
							                                </tr>
							                            </table>
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="10"></td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2"><h3 style="margin:0; line-height:normal; color:#000; font-family:Arial, Helvetica, sans-serif; font-size:16px;">Dear '.$username.',</h3></td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20"></td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2">
							                        	<table width="100%" border="0" cellpadding="10" cellspacing="0" bgcolor="e3d1b0">
							                            	<tr>
							                                	<td colspan="2" style="padding-bottom:0px;"><h4 style="margin:0; line-height:normal; color:#000; font-family:Arial, Helvetica, sans-serif; font-weight:normal; font-size:20px;"><b>'.$companyName.'</b></h4></td>
							                                </tr>
							                                <tr>
							                                	<td width="320">
							                                    	<table width="130" cellpadding="0" cellspacing="0">
							                                        	<tr>
							                                            	<td style="border:1px solid #000;" height="80"><img src ='.$thumb.' height="80" width="130"></td>
							                                            </tr>
							                                        </table>
							                                    </td>
							                                    <td  style="margin:0; line-height:normal; color:#000; font-family:Arial, Helvetica, sans-serif; font-weight:normal; font-size:18px;">Status: Not Available</td>
							                                </tr>
							                                <tr>
										                    	<td colspan="2"></td>
										                    </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40; font-family:Arial, Helvetica, sans-serif;color:#000;">Reservation Date</td>
							                                    <td style="border-bottom:1px solid #b78f40; font-family:Arial, Helvetica, sans-serif;color:#000;">'.$bookingDate.'</td>
							                                </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40; font-family:Arial, Helvetica, sans-serif; color:#000;">Reservation Time </td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$bookingTime.'</td>
							                                </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Bodyguard Name</td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$companyName.'</td>
							                                </tr>
							                                <tr>
															    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Bodyguard Quantity</td>
															    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$totalGuard.' Bodyguards</td>
															</tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Hours</td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$totalHours.' Hours</td>
							                                </tr>
							                                <tr>
							                                    <td style="font-family:Arial, Helvetica, sans-serif; color:#000;">Booked by</td>
							                                    <td style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$username.' (<a href="mailto:'.$userEmail.'" style="color:#000; text-decoration:none;">'.$userEmail.'</a>)</td>
							                                </tr>
							                                <tr>
							                                	<td colspan="2">
							                                    	<table width="100%" border="0" cellpadding="0" cellspacing="0">
							                                        	<tr>
							                                            	<td width="1%"></td>
							                                                <td width="98%">
																				<table width="100%" border="0" cellpadding="6" cellspacing="0">
							                                                        <tr>
							                                                        	<td width="180" style="font-size:18px; color:#000; font-family:Arial, Helvetica, sans-serif;">LUXURY</td>
							                                                            <td width="157" style="font-size:18px; color:#000; font-family:Arial, Helvetica, sans-serif;">PRICE/HOUR </td>
							                                                            <td width="100" style="font-size:18px; color:#000; font-family:Arial, Helvetica, sans-serif;">HOURS</td>
							                                                            <td style="font-size:18px; color:#000; font-family:Arial, Helvetica, sans-serif;">TOTAL</td>
																					<tr>
																						<td colspan="4">
																							<table width="100%" cellpadding="0" cellspacing="0">
										                                                        <tr>
										                                                        	<td width="190" style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$companyName.'</td>
										                                                            <td width="170" style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$currency_code.' '.$price_per_hours.' </td>
										                                                            <td width="115" style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$totalHours.' Hours</td>
							                                                                        <td style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$currency_code.' '.$total_price.'</td>
										                                                        </tr>
																							</table>
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
							                    	<td colspan="2" style="font-size:14px; margin:0; font-family:Arial, Helvetica, sans-serif; color:#000;">
							                        	We are sorry, but it seems that the bodyguard you have requested is not available.
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20">
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td style="font-size:14px; margin:0; font-family:Arial, Helvetica, sans-serif; color:#000;" colspan="2">                   	Please try a different time or date, or you can try booking at another one of our exclusive partners.
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20">
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" style="color:#000;">
							                        	Beseated Support
							                        </td>
							                    </tr>

							                    <tr>
							                    	<td colspan="2" height="30">
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20" style=" border-top:1px solid #c09439;"></td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="color:#5f5f5f;">
							                        	<a href="#" style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif;">My Beseated ID</a>&nbsp;|&nbsp;<a style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="'.$this->support_link.'">Support</a>&nbsp;|&nbsp;<a style="text-decoration:none; font-size:14px; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="'.$this->privacy_policy_link.'">Privacy Policy</a>
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000;">Copyright © 2016 <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>. All rights reserved.</td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000;">This email was sent by <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>, THUB, Dubai Silicon Oasis, Dubai, United Arab Emirates</td>
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

		self::sendEmail($email,$emailSubject, $emailBody);
	}

	public function protectionBookingAvailableUserMail($username,$companyName,$thumb,$bookingDate,$bookingTime,$totalHours,$totalGuard,$userEmail,$currency_code,$price_per_hours,$total_price,$email)
	{
		$thumb = (getimagesize($thumb)) ? $thumb : $this->blank_img;

		$lang = JFactory::getLanguage();
		$extension = 'com_beseated';
		$base_dir = JPATH_SITE;
		$language_tag = 'en-GB';
		$reload = true;
		$lang->load($extension, $base_dir, $language_tag, $reload);

		$emailSubject  = JText::sprintf('COM_BESEATED_TO_VENUE_BOOKING_AVAILABLE_EMAIL_SUBJECT',$companyName);

		$emailBody        = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
							<html>
							<head>
							<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
							<title>Beseated</title>
							<style type="text/css" media="screen">
								p{margin: 0 !important; color: #000;}
								.ExternalClass p { color: #000; margin: 0px;}
							</style>
							</head>
							<body>
							<table border="0" bgcolor="#FFFFFF" cellpadding="0" cellspacing="0" width="650" align="center" style="font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:20px;">
								<tbody>
							    	<tr>
							        	<td height="10"></td>
							        </tr>
							        <tr>
							        	<td align="center">
							            	<table width="630" border="0" cellpadding="0" cellspacing="0">
							                	<tr>
							                        <td align="center" colspan="2">
							                        	<table width="100%" cellpadding="0" cellspacing="0">
							                            	<tr>
							                                	<td align="left" width="100"><a href="'.$this->logo_link.'"><img src="'.JUri::base().'images/edms/images/beseated-logo.png"></a></td>
							                                    <td align="center"></td>
							                                </tr>
							                            </table>
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="10"></td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2"><h3 style="margin:0; line-height:normal; color:#000; font-family:Arial, Helvetica, sans-serif; font-size:16px;">Dear '.$username.',</h3></td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20"></td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2">
							                        	<table width="100%" border="0" cellpadding="10" cellspacing="0" bgcolor="e3d1b0">
							                            	<tr>
							                                	<td colspan="2" style="padding-bottom:0px;"><h4 style="margin:0; line-height:normal; color:#000; font-family:Arial, Helvetica, sans-serif; font-weight:normal; font-size:20px;"><b>'.$companyName.'</b></h4></td>
							                                </tr>
							                                <tr>
							                                	<td width="320">
							                                    	<table width="130" cellpadding="0" cellspacing="0">
							                                        	<tr>
							                                            	<td style="border:1px solid #000;" height="80"><img src ='.$thumb.' height="80" width="130"></td>
							                                            </tr>
							                                        </table>
							                                    </td>
							                                    <td  style="margin:0; line-height:normal; color:#000; font-family:Arial, Helvetica, sans-serif; font-weight:normal; font-size:18px;">Status: Available</td>
							                                </tr>
							                                <tr>
										                    	<td colspan="2"></td>
										                    </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40; font-family:Arial, Helvetica, sans-serif;color:#000;">Reservation Date</td>
							                                    <td style="border-bottom:1px solid #b78f40; font-family:Arial, Helvetica, sans-serif;color:#000;">'.$bookingDate.'</td>
							                                </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40; font-family:Arial, Helvetica, sans-serif; color:#000;">Reservation Time </td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$bookingTime.'</td>
							                                </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Bodyguard Name</td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$companyName.'</td>
							                                </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Bodyguard Quantity</td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$totalGuard.' Bodyguards</td>
							                                </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Hours</td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$totalHours.' Hours</td>
							                                </tr>
							                                <tr>
							                                    <td style="font-family:Arial, Helvetica, sans-serif; color:#000;">Booked by</td>
							                                    <td style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$username.' (<a href="mailto:'.$userEmail.'" style="color:#000; text-decoration:none;">'.$userEmail.'</a>)</td>
							                                </tr>
							                                <tr>
							                                	<td colspan="2">
							                                    	<table width="100%" border="0" cellpadding="0" cellspacing="0">
							                                        	<tr>
							                                            	<td width="1%"></td>
							                                                <td width="98%">
																				<table width="100%" border="0" cellpadding="6" cellspacing="0">
							                                                        <tr>
							                                                        	<td width="180" style="font-size:18px; color:#000; font-family:Arial, Helvetica, sans-serif;">LUXURY</td>
							                                                            <td width="157" style="font-size:18px; color:#000; font-family:Arial, Helvetica, sans-serif;">PRICE/HOUR </td>
							                                                            <td width="100" style="font-size:18px; color:#000; font-family:Arial, Helvetica, sans-serif;">HOURS</td>
							                                                            <td style="font-size:18px; color:#000; font-family:Arial, Helvetica, sans-serif;">TOTAL</td>
																					<tr>
																						<td colspan="4">
																							<table width="100%" cellpadding="0" cellspacing="0">
										                                                        <tr>
										                                                        	<td width="190" style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$companyName.'</td>
										                                                            <td width="170" style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$currency_code.' '.$price_per_hours.' </td>
										                                                            <td width="115" style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$totalHours.' Hours</td>
							                                                                        <td style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$currency_code.' '.$total_price.'</td>
										                                                        </tr>
																							</table>
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
							                    	<td colspan="2" style="font-size:14px; margin:0; font-family:Arial, Helvetica, sans-serif; color:#000;">
							                        	Please view your booking request within your RSVP tab to pay.
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20">
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" style="color:#000;">
							                        	Beseated Support
							                        </td>
							                    </tr>

							                    <tr>
							                    	<td colspan="2" height="30">
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20" style=" border-top:1px solid #c09439;"></td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="color:#5f5f5f;">
							                        	<a href="#" style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif;">My Beseated ID</a>&nbsp;|&nbsp;<a style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="'.$this->support_link.'">Support</a>&nbsp;|&nbsp;<a style="text-decoration:none; font-size:14px; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="'.$this->privacy_policy_link.'">Privacy Policy</a>
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000;">Copyright © 2016 <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>. All rights reserved.</td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000;">This email was sent by <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>, THUB, Dubai Silicon Oasis, Dubai, United Arab Emirates</td>
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

		self::sendEmail($email,$emailSubject, $emailBody);
	}

	public function yachtBookingRequestUserMail($companyName,$thumb,$bookingDate,$bookingTime,$service_name,$dock,$capacity,$totalHours,$username,$userEmail,$currency_code,$price_per_hours,$total_price,$email)
	{
		$thumb = (getimagesize($thumb)) ? $thumb : $this->blank_img;


		$lang = JFactory::getLanguage();
		$extension = 'com_beseated';
		$base_dir = JPATH_SITE;
		$language_tag = 'en-GB';
		$reload = true;
		$lang->load($extension, $base_dir, $language_tag, $reload);

		$emailSubject  = JText::sprintf('COM_BESEATED_TO_VENUE_BOOKING_AVAILABLE_EMAIL_SUBJECT',$companyName);

		$emailBody        = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
							<html>
								<head>
								<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
								<title>Beseated</title>
								<style type="text/css" media="screen">
									p{margin: 0 !important; color: #000;}
									.ExternalClass p { color: #000; margin: 0px;}
								</style>
								</head>
								<body>
								<table border="0" bgcolor="#FFFFFF" cellpadding="0" cellspacing="0" width="650" align="center" style="font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:20px;">
									<tbody>
								    	<tr>
								        	<td height="10"></td>
								        </tr>
								        <tr>
								        	<td align="center">
								            	<table width="630" border="0" cellpadding="0" cellspacing="0">
								                	<tr>
								                        <td align="center" colspan="2">
								                        	<table width="100%" cellpadding="0" cellspacing="0">
								                            	<tr>
								                                	<td align="left" width="100"><a href="'.$this->logo_link.'"><img src="'.JUri::base().'images/edms/images/beseated-logo.png"></a></td>
								                                    <td align="center"></td>
								                                </tr>
								                            </table>
								                        </td>
								                    </tr>
								                    <tr>
								                    	<td colspan="2" height="10"></td>
								                    </tr>
								                    <tr>
								                    	<td colspan="2"><h3 style="margin:0; line-height:normal; color:#000; font-family:Arial, Helvetica, sans-serif; font-size:16px;">Dear '.$username.',</h3></td>
								                    </tr>
								                    <tr>
								                    	<td colspan="2" height="20"></td>
								                    </tr>
								                    <tr>
								                    	<td colspan="2">
								                        	<table width="100%" border="0" cellpadding="10" cellspacing="0" bgcolor="e3d1b0">
								                            	<tr>
								                                	<td colspan="2" style="padding-bottom:0px;"><h4 style="margin:0; line-height:normal; color:#000; font-family:Arial, Helvetica, sans-serif; font-weight:normal; font-size:20px;"><b>'.$companyName.'</b></h4></td>
								                                </tr>
								                                <tr>
								                                	<td width="320">
								                                    	<table width="130" cellpadding="0" cellspacing="0">
								                                        	<tr>
								                                            	<td style="border:1px solid #000;" height="80"><img src ='.$thumb.' height="80" width="130"></td>
								                                            </tr>
								                                        </table>
								                                    </td>
								                                    <td  style="margin:0; line-height:normal; color:#000; font-family:Arial, Helvetica, sans-serif; font-weight:normal; font-size:18px;">Status: Requested</td>
								                                </tr>
								                                <tr>
											                    	<td colspan="2"></td>
											                    </tr>
								                                <tr>
								                                    <td style="border-bottom:1px solid #b78f40; font-family:Arial, Helvetica, sans-serif;color:#000;">Reservation Date</td>
								                                    <td style="border-bottom:1px solid #b78f40; font-family:Arial, Helvetica, sans-serif;color:#000;">'.$bookingDate.'</td>
								                                </tr>
								                                <tr>
								                                    <td style="border-bottom:1px solid #b78f40; font-family:Arial, Helvetica, sans-serif; color:#000;">Reservation Time </td>
								                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$bookingTime.'</td>
								                                </tr>
								                                <tr>
								                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Yacht Name</td>
								                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$companyName.'</td>
								                                </tr>
								                                <tr>
								                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Yacht Type</td>
								                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$service_name.'</td>
								                                </tr>
								                                <tr>
								                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Dock Location </td>
								                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$dock.'</td>
								                                </tr>
								                                 <tr>
								                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Capacity</td>
								                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$capacity.' People</td>
								                                </tr>
								                                <tr>
								                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Hours</td>
								                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$totalHours.' Hours</td>
								                                </tr>
								                                <tr>
								                                    <td style="font-family:Arial, Helvetica, sans-serif; color:#000;">Booked by</td>
								                                    <td style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$username.' (<a href="mailto:'.$userEmail.'" style="color:#000; text-decoration:none;">'.$userEmail.'</a>)</td>
								                                </tr>
								                                <tr>
								                                	<td colspan="2">
								                                    	<table width="100%" border="0" cellpadding="0" cellspacing="0">
								                                        	<tr>
								                                            	<td width="1%"></td>
								                                                <td width="98%">
																					<table width="100%" border="0" cellpadding="6" cellspacing="0">
								                                                        <tr>
								                                                        	<td width="180" style="font-size:18px; color:#000; font-family:Arial, Helvetica, sans-serif;">LUXURY</td>
								                                                            <td width="157" style="font-size:18px; color:#000; font-family:Arial, Helvetica, sans-serif;">PRICE/HOUR </td>
								                                                            <td width="100" style="font-size:18px; color:#000; font-family:Arial, Helvetica, sans-serif;">HOURS</td>
								                                                            <td style="font-size:18px; color:#000; font-family:Arial, Helvetica, sans-serif;">TOTAL</td>
																						<tr>
																							<td colspan="4">
																								<table width="100%" cellpadding="0" cellspacing="0">
											                                                        <tr>
											                                                        	<td width="190" style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$companyName.'</td>
											                                                            <td width="170" style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$currency_code.' '.$price_per_hours.' </td>
											                                                            <td width="115" style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$totalHours.' Hours</td>
								                                                                        <td style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$currency_code.' '.$total_price.' </td>
											                                                        </tr>
																								</table>
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
								                    	<td colspan="2" style="font-size:14px; margin:0; font-family:Arial, Helvetica, sans-serif; color:#000;">
								                        	We will notify you as soon as we hear a response.
								                        </td>
								                    </tr>
								                    <tr>
								                    	<td colspan="2" height="10">
								                        </td>
								                    </tr>
								                       <tr>
								                    	<td colspan="2" style="font-size:14px; margin:0; font-family:Arial, Helvetica, sans-serif; color:#000;">
								                        	You can view your booking request status within your <a href="#" style="color:#b48b3b;">RSVP tab</a>.
								                        </td>
								                    </tr>
								                    <tr>
								                    	<td colspan="2" height="20">
								                        </td>
								                    </tr>
								                    <tr>
								                    	<td colspan="2" style="font-size:14px; margin:0; font-family:Arial, Helvetica, sans-serif; color:#000;">
								                        	Beseated Support
								                        </td>
								                    </tr>

								                    <tr>
								                    	<td colspan="2" height="30">
								                        </td>
								                    </tr>
								                    <tr>
								                    	<td colspan="2" height="20" style=" border-top:1px solid #c09439;"></td>
								                    </tr>
								                    <tr>
								                    	<td colspan="2" align="center" style="color:#5f5f5f;">
								                        	<a href="#" style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif;">My Beseated ID</a>&nbsp;|&nbsp;<a style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="'.$this->support_link.'">Support</a>&nbsp;|&nbsp;<a style="text-decoration:none; font-size:14px; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="'.$this->privacy_policy_link.'">Privacy Policy</a>
								                        </td>
								                    </tr>
								                    <tr>
								                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000;">Copyright © 2016 <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>. All rights reserved.</td>
								                    </tr>
								                    <tr>
								                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000;">This email was sent by <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>, THUB, Dubai Silicon Oasis, Dubai, United Arab Emirates</td>
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

		self::sendEmail($email,$emailSubject, $emailBody);
	}

	public function yachtBookingRequestManagerMail($companyName,$thumb,$bookingDate,$bookingTime,$service_name,$dock,$capacity,$totalHours,$username,$userEmail,$currency_code,$price_per_hours,$total_price,$email)
	{
		$thumb = (getimagesize($thumb)) ? $thumb : $this->blank_img;

		//$email =  "jamal@tasolglobal.com";

		$lang = JFactory::getLanguage();
		$extension = 'com_beseated';
		$base_dir = JPATH_SITE;
		$language_tag = 'en-GB';
		$reload = true;
		$lang->load($extension, $base_dir, $language_tag, $reload);

		$emailSubject  = JText::sprintf('COM_BESEATED_TO_VENUE_BOOKING_AVAILABLE_EMAIL_SUBJECT',$companyName);

		$emailBody        = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
							<html>
								<head>
								<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
								<title>Beseated</title>
								<style type="text/css" media="screen">
									p{margin: 0 !important; color: #000;}
									.ExternalClass p { color: #000; margin: 0px;}
								</style>
								</head>
								<body>
								<table border="0" bgcolor="#FFFFFF" cellpadding="0" cellspacing="0" width="650" align="center" style="font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:20px;">
									<tbody>
								    	<tr>
								        	<td height="10"></td>
								        </tr>
								        <tr>
								        	<td align="center">
								            	<table width="630" border="0" cellpadding="0" cellspacing="0">
								                	<tr>
								                        <td align="center" colspan="2">
								                        	<table width="100%" cellpadding="0" cellspacing="0">
								                            	<tr>
								                                	<td align="left" width="100"><a href="'.$this->logo_link.'"><img src="'.JUri::base().'images/edms/images/beseated-logo.png"></a></td>
								                                    <td align="center"></td>
								                                </tr>
								                            </table>
								                        </td>
								                    </tr>
								                    <tr>
								                    	<td colspan="2" height="10"></td>
								                    </tr>
								                    <tr>
								                    	<td colspan="2"><h3 style="margin:0; line-height:normal; color:#000; font-family:Arial, Helvetica, sans-serif; font-size:16px;">Dear '.$companyName.',</h3></td>
								                    </tr>
								                    <tr>
								                    	<td colspan="2" height="20"></td>
								                    </tr>
								                    <tr>
								                    	<td colspan="2">
								                        	<table width="100%" border="0" cellpadding="10" cellspacing="0" bgcolor="e3d1b0">
								                            	<tr>
								                                	<td colspan="2" style="padding-bottom:0px;"><h4 style="margin:0; line-height:normal; color:#000; font-family:Arial, Helvetica, sans-serif; font-weight:normal; font-size:20px;"><b>'.$companyName.'</b></h4></td>
								                                </tr>
								                                <tr>
								                                	<td width="320">
								                                    	<table width="130" cellpadding="0" cellspacing="0">
								                                        	<tr>
								                                            	<td style="border:1px solid #000;" height="80"><img src ='.$thumb.' height="80" width="130"></td>
								                                            </tr>
								                                        </table>
								                                    </td>
								                                    <td  style="margin:0; line-height:normal; color:#000; font-family:Arial, Helvetica, sans-serif; font-weight:normal; font-size:18px;">Status: Requested</td>
								                                </tr>
								                                <tr>
											                    	<td colspan="2"></td>
											                    </tr>
								                                <tr>
								                                    <td style="border-bottom:1px solid #b78f40; font-family:Arial, Helvetica, sans-serif;color:#000;">Reservation Date</td>
								                                    <td style="border-bottom:1px solid #b78f40; font-family:Arial, Helvetica, sans-serif;color:#000;">'.$bookingDate.'</td>
								                                </tr>
								                                <tr>
								                                    <td style="border-bottom:1px solid #b78f40; font-family:Arial, Helvetica, sans-serif; color:#000;">Reservation Time </td>
								                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$bookingTime.'</td>
								                                </tr>
								                                <tr>
								                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Yacht Name</td>
								                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$companyName.'</td>
								                                </tr>
								                                <tr>
								                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Yacht Type</td>
								                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$service_name.'</td>
								                                </tr>
								                                <tr>
								                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Dock Location </td>
								                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$dock.'</td>
								                                </tr>
								                                 <tr>
								                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Capacity</td>
								                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$capacity.' People</td>
								                                </tr>
								                                <tr>
								                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Hours</td>
								                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$totalHours.' Hours</td>
								                                </tr>
								                                <tr>
								                                    <td style="font-family:Arial, Helvetica, sans-serif; color:#000;">Booked by</td>
								                                    <td style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$username.' (<a href="mailto:'.$userEmail.'" style="color:#000; text-decoration:none;">'.$userEmail.'</a>)</td>
								                                </tr>
								                                <tr>
								                                	<td colspan="2">
								                                    	<table width="100%" border="0" cellpadding="0" cellspacing="0">
								                                        	<tr>
								                                            	<td width="1%"></td>
								                                                <td width="98%">
																					<table width="100%" border="0" cellpadding="6" cellspacing="0">
								                                                        <tr>
								                                                        	<td width="180" style="font-size:18px; color:#000; font-family:Arial, Helvetica, sans-serif;">LUXURY</td>
								                                                            <td width="157" style="font-size:18px; color:#000; font-family:Arial, Helvetica, sans-serif;">PRICE/HOUR </td>
								                                                            <td width="100" style="font-size:18px; color:#000; font-family:Arial, Helvetica, sans-serif;">HOURS</td>
								                                                            <td style="font-size:18px; color:#000; font-family:Arial, Helvetica, sans-serif;">TOTAL</td>
																						<tr>
																							<td colspan="4">
																								<table width="100%" cellpadding="0" cellspacing="0">
											                                                        <tr>
											                                                        	<td width="190" style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$companyName.'</td>
											                                                            <td width="170" style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$currency_code.' '.$price_per_hours.' </td>
											                                                            <td width="115" style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$totalHours.' Hours</td>
								                                                                        <td style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$currency_code.' '.$total_price.' </td>
											                                                        </tr>
																								</table>
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
								                    	<td colspan="2" style="font-size:14px; margin:0; font-family:Arial, Helvetica, sans-serif; color:#000;">
								                        	Please view your booking request within your RSVP tab to conﬁrm availability.
								                        </td>
								                    </tr>
								                    <tr>
								                    	<td colspan="2" height="20">
								                        </td>
								                    </tr>
								                    <tr>
								                    	<td colspan="2" style="font-size:14px; margin:0; font-family:Arial, Helvetica, sans-serif; color:#000;">
								                        	Kindly conﬁrm availability as soon as possible.
								                        </td>
								                    </tr>
								                    <tr>
								                    	<td colspan="2" height="20">
								                        </td>
								                    </tr>
								                    <tr>
								                    	<td colspan="2" style="font-size:14px; margin:0; font-family:Arial, Helvetica, sans-serif; color:#000;">
								                        	Beseated Support
								                        </td>
								                    </tr>

								                    <tr>
								                    	<td colspan="2" height="30">
								                        </td>
								                    </tr>
								                    <tr>
								                    	<td colspan="2" height="20" style=" border-top:1px solid #c09439;"></td>
								                    </tr>
								                    <tr>
								                    	<td colspan="2" align="center" style="color:#5f5f5f;">
								                        	<a href="#" style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif;">My Beseated ID</a>&nbsp;|&nbsp;<a style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="'.$this->support_link.'">Support</a>&nbsp;|&nbsp;<a style="text-decoration:none; font-size:14px; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="'.$this->privacy_policy_link.'">Privacy Policy</a>
								                        </td>
								                    </tr>
								                    <tr>
								                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000;">Copyright © 2016 <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>. All rights reserved.</td>
								                    </tr>
								                    <tr>
								                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000;">This email was sent by <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>, THUB, Dubai Silicon Oasis, Dubai, United Arab Emirates</td>
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

		self::sendEmail($email,$emailSubject, $emailBody);
	}

	public function yachtBookingNotAvailableUserMail($userName,$companyName,$thumb,$bookingDate,$bookingTime,$service_name,$dock,$capacity,$totalHours,$username,$userEmail,$currency_code,$price_per_hours,$total_price,$email)
	{
		$thumb = (getimagesize($thumb)) ? $thumb : $this->blank_img;

		//$email =  "jamal@tasolglobal.com";

		$lang = JFactory::getLanguage();
		$extension = 'com_beseated';
		$base_dir = JPATH_SITE;
		$language_tag = 'en-GB';
		$reload = true;
		$lang->load($extension, $base_dir, $language_tag, $reload);

		$emailSubject  = JText::sprintf('COM_BESEATED_TO_VENUE_BOOKING_AVAILABLE_EMAIL_SUBJECT',$companyName);

		$emailBody        = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
							<html>
								<head>
								<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
								<title>Beseated</title>
								<style type="text/css" media="screen">
									p{margin: 0 !important; color: #000;}
									.ExternalClass p { color: #000; margin: 0px;}
								</style>
								</head>
								<body>
								<table border="0" bgcolor="#FFFFFF" cellpadding="0" cellspacing="0" width="650" align="center" style="font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:20px;">
									<tbody>
								    	<tr>
								        	<td height="10"></td>
								        </tr>
								        <tr>
								        	<td align="center">
								            	<table width="630" border="0" cellpadding="0" cellspacing="0">
								                	<tr>
								                        <td align="center" colspan="2">
								                        	<table width="100%" cellpadding="0" cellspacing="0">
								                            	<tr>
								                                	<td align="left" width="100"><a href="'.$this->logo_link.'"><img src="'.JUri::base().'images/edms/images/beseated-logo.png"></a></td>
								                                    <td align="center"></td>
								                                </tr>
								                            </table>
								                        </td>
								                    </tr>
								                    <tr>
								                    	<td colspan="2" height="10"></td>
								                    </tr>
								                    <tr>
								                    	<td colspan="2"><h3 style="margin:0; line-height:normal; color:#000; font-family:Arial, Helvetica, sans-serif; font-size:16px;">Dear '.$username.',</h3></td>
								                    </tr>
								                    <tr>
								                    	<td colspan="2" height="20"></td>
								                    </tr>
								                    <tr>
								                    	<td colspan="2">
								                        	<table width="100%" border="0" cellpadding="10" cellspacing="0" bgcolor="e3d1b0">
								                            	<tr>
								                                	<td colspan="2" style="padding-bottom:0px;"><h4 style="margin:0; line-height:normal; color:#000; font-family:Arial, Helvetica, sans-serif; font-weight:normal; font-size:20px;"><b>'.$companyName.'</b></h4></td>
								                                </tr>
								                                <tr>
								                                	<td width="320">
								                                    	<table width="130" cellpadding="0" cellspacing="0">
								                                        	<tr>
								                                            	<td style="border:1px solid #000;" height="80"><img src ='.$thumb.' height="80" width="130"></td>
								                                            </tr>
								                                        </table>
								                                    </td>
								                                    <td  style="margin:0; line-height:normal; color:#000; font-family:Arial, Helvetica, sans-serif; font-weight:normal; font-size:18px;">Status: Not Available</td>
								                                </tr>
								                                <tr>
											                    	<td colspan="2"></td>
											                    </tr>
								                                <tr>
								                                    <td style="border-bottom:1px solid #b78f40; font-family:Arial, Helvetica, sans-serif;color:#000;">Reservation Date</td>
								                                    <td style="border-bottom:1px solid #b78f40; font-family:Arial, Helvetica, sans-serif;color:#000;">'.$bookingDate.'</td>
								                                </tr>
								                                <tr>
								                                    <td style="border-bottom:1px solid #b78f40; font-family:Arial, Helvetica, sans-serif; color:#000;">Reservation Time </td>
								                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$bookingTime.'</td>
								                                </tr>
								                                <tr>
								                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Yacht Name</td>
								                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$companyName.'</td>
								                                </tr>
								                                <tr>
								                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Yacht Type</td>
								                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$service_name.'</td>
								                                </tr>
								                                <tr>
								                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Dock Location </td>
								                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$dock.'</td>
								                                </tr>
								                                 <tr>
								                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Capacity</td>
								                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$capacity.' People</td>
								                                </tr>
								                                <tr>
								                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Hours</td>
								                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$totalHours.' Hours</td>
								                                </tr>
								                                <tr>
								                                    <td style="font-family:Arial, Helvetica, sans-serif; color:#000;">Booked by</td>
								                                    <td style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$username.' (<a href="mailto:'.$userEmail.'" style="color:#000; text-decoration:none;">'.$userEmail.'</a>)</td>
								                                </tr>
								                                <tr>
								                                	<td colspan="2">
								                                    	<table width="100%" border="0" cellpadding="0" cellspacing="0">
								                                        	<tr>
								                                            	<td width="1%"></td>
								                                                <td width="98%">
																					<table width="100%" border="0" cellpadding="6" cellspacing="0">
								                                                        <tr>
								                                                        	<td width="180" style="font-size:18px; color:#000; font-family:Arial, Helvetica, sans-serif;">LUXURY</td>
								                                                            <td width="157" style="font-size:18px; color:#000; font-family:Arial, Helvetica, sans-serif;">PRICE/HOUR </td>
								                                                            <td width="100" style="font-size:18px; color:#000; font-family:Arial, Helvetica, sans-serif;">HOURS</td>
								                                                            <td style="font-size:18px; color:#000; font-family:Arial, Helvetica, sans-serif;">TOTAL</td>
																						<tr>
																							<td colspan="4">
																								<table width="100%" cellpadding="0" cellspacing="0">
											                                                        <tr>
											                                                        	<td width="190" style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$companyName.'</td>
											                                                            <td width="170" style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$currency_code.' '.$price_per_hours.' </td>
											                                                            <td width="115" style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$totalHours.' Hours</td>
								                                                                        <td style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$currency_code.' '.$total_price.' </td>
											                                                        </tr>
																								</table>
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
								                    	<td colspan="2" style="font-size:14px; margin:0; font-family:Arial, Helvetica, sans-serif; color:#000;">
								                        	We are sorry, but it seems that the yacht you have requested is not available.
								                        </td>
								                    </tr>
								                    <tr>
								                    	<td colspan="2" height="10">
								                        </td>
								                    </tr>
								                       <tr>
								                    	<td colspan="2" style="font-size:14px; margin:0; font-family:Arial, Helvetica, sans-serif; color:#000;">
								                        	Please try a different time or date, or you can try booking at another one of our exclusive partners.

								                        </td>
								                    </tr>
								                    <tr>
								                    	<td colspan="2" height="20">
								                        </td>
								                    </tr>
								                    <tr>
								                    	<td colspan="2" style="font-size:14px; margin:0; font-family:Arial, Helvetica, sans-serif; color:#000;">
								                        	Beseated Support
								                        </td>
								                    </tr>

								                    <tr>
								                    	<td colspan="2" height="30">
								                        </td>
								                    </tr>
								                    <tr>
								                    	<td colspan="2" height="20" style=" border-top:1px solid #c09439;"></td>
								                    </tr>
								                    <tr>
								                    	<td colspan="2" align="center" style="color:#5f5f5f;">
								                        	<a href="#" style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif;">My Beseated ID</a>&nbsp;|&nbsp;<a style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="'.$this->support_link.'">Support</a>&nbsp;|&nbsp;<a style="text-decoration:none; font-size:14px; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="'.$this->privacy_policy_link.'">Privacy Policy</a>
								                        </td>
								                    </tr>
								                    <tr>
								                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000;">Copyright © 2016 <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>. All rights reserved.</td>
								                    </tr>
								                    <tr>
								                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000;">This email was sent by <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>, THUB, Dubai Silicon Oasis, Dubai, United Arab Emirates</td>
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

		self::sendEmail($email,$emailSubject, $emailBody);
	}

	public function yachtBookingAvailableUserMail($userName,$companyName,$thumb,$bookingDate,$bookingTime,$service_name,$dock,$capacity,$totalHours,$username,$userEmail,$currency_code,$price_per_hours,$total_price,$userEmail,$email)
	{
		$thumb = (getimagesize($thumb)) ? $thumb : $this->blank_img;

		//echo "<pre/>";print_r($email);exit;
		//$email =  "jamal@tasolglobal.com";

		$lang = JFactory::getLanguage();
		$extension = 'com_beseated';
		$base_dir = JPATH_SITE;
		$language_tag = 'en-GB';
		$reload = true;
		$lang->load($extension, $base_dir, $language_tag, $reload);

		$emailSubject  = JText::sprintf('COM_BESEATED_TO_VENUE_BOOKING_AVAILABLE_EMAIL_SUBJECT',$companyName);

		$emailBody        = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
							<html>
								<head>
								<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
								<title>Beseated</title>
								<style type="text/css" media="screen">
									p{margin: 0 !important; color: #000;}
									.ExternalClass p { color: #000; margin: 0px;}
								</style>
								</head>
								<body>
								<table border="0" bgcolor="#FFFFFF" cellpadding="0" cellspacing="0" width="650" align="center" style="font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:20px;">
									<tbody>
								    	<tr>
								        	<td height="10"></td>
								        </tr>
								        <tr>
								        	<td align="center">
								            	<table width="630" border="0" cellpadding="0" cellspacing="0">
								                	<tr>
								                        <td align="center" colspan="2">
								                        	<table width="100%" cellpadding="0" cellspacing="0">
								                            	<tr>
								                                	<td align="left" width="100"><a href="'.$this->logo_link.'"><img src="'.JUri::base().'images/edms/images/beseated-logo.png"></a></td>
								                                    <td align="center"></td>
								                                </tr>
								                            </table>
								                        </td>
								                    </tr>
								                    <tr>
								                    	<td colspan="2" height="10"></td>
								                    </tr>
								                    <tr>
								                    	<td colspan="2"><h3 style="margin:0; line-height:normal; color:#000; font-family:Arial, Helvetica, sans-serif; font-size:16px;">Dear '.$username.',</h3></td>
								                    </tr>
								                    <tr>
								                    	<td colspan="2" height="20"></td>
								                    </tr>
								                    <tr>
								                    	<td colspan="2">
								                        	<table width="100%" border="0" cellpadding="10" cellspacing="0" bgcolor="e3d1b0">
								                            	<tr>
								                                	<td colspan="2" style="padding-bottom:0px;"><h4 style="margin:0; line-height:normal; color:#000; font-family:Arial, Helvetica, sans-serif; font-weight:normal; font-size:20px;"><b>'.$companyName.'</b></h4></td>
								                                </tr>
								                                <tr>
								                                	<td width="320">
								                                    	<table width="130" cellpadding="0" cellspacing="0">
								                                        	<tr>
								                                            	<td style="border:1px solid #000;" height="80"><img src ='.$thumb.' height="80" width="130"></td>
								                                            </tr>
								                                        </table>
								                                    </td>
								                                    <td  style="margin:0; line-height:normal; color:#000; font-family:Arial, Helvetica, sans-serif; font-weight:normal; font-size:18px;">Status: Available</td>
								                                </tr>
								                                <tr>
											                    	<td colspan="2"></td>
											                    </tr>
								                                <tr>
								                                    <td style="border-bottom:1px solid #b78f40; font-family:Arial, Helvetica, sans-serif;color:#000;">Reservation Date</td>
								                                    <td style="border-bottom:1px solid #b78f40; font-family:Arial, Helvetica, sans-serif;color:#000;">'.$bookingDate.'</td>
								                                </tr>
								                                <tr>
								                                    <td style="border-bottom:1px solid #b78f40; font-family:Arial, Helvetica, sans-serif; color:#000;">Reservation Time </td>
								                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$bookingTime.'</td>
								                                </tr>
								                                <tr>
								                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Yacht Name</td>
								                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$companyName.'</td>
								                                </tr>
								                                <tr>
								                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Yacht Type</td>
								                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$service_name.'</td>
								                                </tr>
								                                <tr>
								                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Dock Location </td>
								                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$dock.'</td>
								                                </tr>
								                                 <tr>
								                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Capacity</td>
								                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$capacity.' People</td>
								                                </tr>
								                                <tr>
								                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Hours</td>
								                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$totalHours.' Hours</td>
								                                </tr>
								                                <tr>
								                                    <td style="font-family:Arial, Helvetica, sans-serif; color:#000;">Booked by</td>
								                                    <td style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$username.' (<a href="mailto:'.$userEmail.'" style="color:#000; text-decoration:none;">'.$userEmail.'</a>)</td>
								                                </tr>
								                                <tr>
								                                	<td colspan="2">
								                                    	<table width="100%" border="0" cellpadding="0" cellspacing="0">
								                                        	<tr>
								                                            	<td width="1%"></td>
								                                                <td width="98%">
																					<table width="100%" border="0" cellpadding="6" cellspacing="0">
								                                                        <tr>
								                                                        	<td width="180" style="font-size:18px; color:#000; font-family:Arial, Helvetica, sans-serif;">LUXURY</td>
								                                                            <td width="157" style="font-size:18px; color:#000; font-family:Arial, Helvetica, sans-serif;">PRICE/HOUR </td>
								                                                            <td width="100" style="font-size:18px; color:#000; font-family:Arial, Helvetica, sans-serif;">HOURS</td>
								                                                            <td style="font-size:18px; color:#000; font-family:Arial, Helvetica, sans-serif;">TOTAL</td>
																						<tr>
																							<td colspan="4">
																								<table width="100%" cellpadding="0" cellspacing="0">
											                                                        <tr>
											                                                        	<td width="190" style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$companyName.'</td>
											                                                            <td width="170" style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$currency_code.' '.$price_per_hours.' </td>
											                                                            <td width="115" style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$totalHours.' Hours</td>
								                                                                        <td style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$currency_code.' '.$total_price.' </td>
											                                                        </tr>
																								</table>
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
								                    	<td colspan="2" style="font-size:14px; margin:0; font-family:Arial, Helvetica, sans-serif; color:#000;">
								                        	Please view your booking request within your RSVP tab to pay.
								                        </td>
								                    </tr>
								                    <tr>
								                    	<td colspan="2" height="20">
								                        </td>
								                    </tr>
								                    <tr>
								                    	<td colspan="2" style="font-size:14px; margin:0; font-family:Arial, Helvetica, sans-serif; color:#000;">
								                        	Beseated Support
								                        </td>
								                    </tr>

								                    <tr>
								                    	<td colspan="2" height="30">
								                        </td>
								                    </tr>
								                    <tr>
								                    	<td colspan="2" height="20" style=" border-top:1px solid #c09439;"></td>
								                    </tr>
								                    <tr>
								                    	<td colspan="2" align="center" style="color:#5f5f5f;">
								                        	<a href="#" style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif;">My Beseated ID</a>&nbsp;|&nbsp;<a style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="'.$this->support_link.'">Support</a>&nbsp;|&nbsp;<a style="text-decoration:none; font-size:14px; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="'.$this->privacy_policy_link.'">Privacy Policy</a>
								                        </td>
								                    </tr>
								                    <tr>
								                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000;">Copyright © 2016 <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>. All rights reserved.</td>
								                    </tr>
								                    <tr>
								                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000;">This email was sent by <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>, THUB, Dubai Silicon Oasis, Dubai, United Arab Emirates</td>
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

		self::sendEmail($email,$emailSubject, $emailBody);
	}

	public function venueBookingconfirmedUserMail($username,$companyName,$venueThumb,$location,$userPhoneNo,$showDirection,$bookingID,$bookingDate,$bookingTime,$table_name,$currency_code,$min_price,$male_guest,$female_guest,$passkey,$username,$userEmail,$bottleRow,$totalBottlePrice,$refund_policy,$email)
	{
		$venueThumb = (getimagesize($venueThumb)) ? $venueThumb : $this->blank_img;

		$lang = JFactory::getLanguage();
		$extension = 'com_beseated';
		$base_dir = JPATH_SITE;
		$language_tag = 'en-GB';
		$reload = true;
		$lang->load($extension, $base_dir, $language_tag, $reload);

		$emailSubject  = JText::sprintf('COM_BESEATED_TO_BOOKING_CONFIRMED_EMAIL_SUBJECT',$username);

		$emailBody        = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
							<html>
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
							                    	<td align="left"><a href="'.$this->logo_link.'"><img src="'.JUri::base().'images/edms/images/beseated-logo.png"></a></td>
							                        <td align="right"><a href="'.JUri::base().'index.php?option=com_beseated&view=venuemail&bookingID='.$bookingID.'"><img src="'.JUri::base().'images/edms/images/print-btn.png"></a></td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="10"></td>
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
							                                	<td colspan="2"  height="20" style="padding-bottom:0px;"><h4 style="margin:0; line-height:normal; color:#000; font-family:Arial, Helvetica, sans-serif; font-weight:normal; font-size:20px;"><b>'.$companyName.'</h4></td>
							                                </tr>
							                                <tr>
							                                	<td colspan="2">
							                                    	<table  width="100%" border="0" cellpadding="0" cellspacing="0">
							                                        	<tr>
							                                                <td>
							                                                    <table width="100%" border="0" cellpadding="0" cellspacing="0">
							                                                        <tr>
							                                                            <td height="60" width="100" >
							                                                            <img src ='.$venueThumb.' height="80" width="130" style="border:2px solid #000;">
							                                                            </td>
							                                                            <td>
							                                                                <table width="100%" border="0" cellpadding="0" cellspacing="0" style="font-family:Arial, Helvetica, sans-serif; font-size:14px; color:#000; margin-left:15px; padding-right:10px;">
							                                                                    <tr>
							                                                                        <td valign="top" width="20%">&nbsp;&nbsp;Address:</td>
							                                                                        <td valign="top" width="60%">'.$location.'</td>
							                                                                    </tr>
							                                                                    <tr>
							                                                                        <td>&nbsp;&nbsp;Phone:</td>
							                                                                        <td>'.$userPhoneNo.'</td>
							                                                                    </tr>
							                                                                    <tr>
							                                                                        <td valign="top" width="35%">&nbsp;&nbsp;Getting There:</td>
							                                                                        <td valign="top"><a href="https://www.google.com/maps/dir/Current+Location/'.$location.'" target="_blank">'.$showDirection.'</a></td>
							                                                                    </tr>
							                                                                </table>
							                                                            </td>
							                                                        </tr>
							                                                    </table>
							                                                </td>
							                                                <td valign="middle">
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
							                                    <td width="50%" style="border-bottom:1px solid #b78f40; font-family:Arial, Helvetica, sans-serif;color:#000;">Reservation number</td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif;color:#000;">'.$bookingID.'</td>
							                                </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40; font-family:Arial, Helvetica, sans-serif;color:#000;">Reservation Date</td>
							                                    <td style="border-bottom:1px solid #b78f40; font-family:Arial, Helvetica, sans-serif;color:#000;">'.$bookingDate.'</td>
							                                </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40; font-family:Arial, Helvetica, sans-serif; color:#000;">Reservation Time</td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$bookingTime.'</td>
							                                </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Your Reservation</td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$table_name.'-Minimum '.$currency_code.' '.$min_price.'</td>
							                                </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Number of People</td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.($male_guest+$female_guest).' Clients - '.$male_guest.' Males / '.$female_guest.' Females</td>
							                                </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Passkey</td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$passkey.'</td>
							                                </tr>
							                                <tr>
							                                    <td style="font-family:Arial, Helvetica, sans-serif; color:#000;">Booked by</td>
							                                    <td style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$username.' (<a href="mailto:'.$userEmail.'" style="color:#000; text-decoration:none;">'.$userEmail.'</a>)</td>
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
																				<table width="100%" border="0" cellpadding="6" cellspacing="0">';

															if(!empty($bottleRow))
															{

							                                   $emailBody        .= '<tr>
							                                                        	<td colspan="3"><h4 style="margin:0; line-height:normal; font-family:Arial, Helvetica, sans-serif; color:#000; font-weight:normal;">Bottle Service Added</h4></td>		 														</tr>
							                                                        <tr>
							                                                        	<td width="250" style="font-size:18px; color:#000; font-family:Arial, Helvetica, sans-serif;">ITEM</td>
							                                                            <td width="205" style="font-size:18px; color:#000; font-family:Arial, Helvetica, sans-serif;">PRICE</td>
							                                                            <td style="font-size:18px; color:#000; font-family:Arial, Helvetica, sans-serif;">TOTAL</td>
																					<tr>
																						<td colspan="3">
																							<table width="100%" cellpadding="0" cellspacing="0">
										                                                        '.$bottleRow.'
																							</table>
																						</td>
																					</tr>
							                                                        <tr>
							                                                        	<td colspan="2" style="font-family:Arial, Helvetica, sans-serif;border-top:1px dashed #000; color:#000;" align="right">TOTAL AMOUNT &nbsp;&nbsp;&nbsp;&nbsp;</td>
							                                                            <td style="border-top:1px dashed #000;font-family:Arial, Helvetica, sans-serif;color:#000;">'.$currency_code.' '.$totalBottlePrice.'</td>
							                                                        </tr>';
							                                }

							                                   $emailBody       .= '<tr>
							                                                        	<td colspan="3" align="right"><img style="vertical-align:middle;" src="'.JUri::base().'images/edms/images/right-icn.png">&nbsp;&nbsp;<a href="#" style="color:#000;font-family:Arial, Helvetica, sans-serif;">Best Price Guaranteed</a></td>
							                                                        </tr>
							                                                        <tr>
							                                                        	<td colspan="3">
							                                                            	<span style="margin:0; text-decoration:underline;font-family:Arial, Helvetica, sans-serif; color:#000;">Please Note:</span>
							                                                            	<p style="margin:0px; font-family:Arial, Helvetica, sans-serif; color:#000;">- Any extra items requested at venue, are subject to addtional charges<br>
							                                                                <span style="margin:0px; font-family:Arial, Helvetica, sans-serif;color:#000;">- The total price shown is the amount you will pay at the venue. BESEATED does not charge any reservation, administration or other fees.</span>
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
							                    	<td colspan="2" height="10">
							                        </td>
							                    </tr>

							                    <tr>
							                    	<td colspan="2">
							                        <h3 style="font-weight:normal; margin:0; font-family:Arial, Helvetica, sans-serif; color:#000;">Loyalty Coins &nbsp;&nbsp;<img style="vertical-align:middle;" src="'.JUri::base().'images/edms/images/money-icn.png"></h3>
							<p style="margin:0;font-family:Arial, Helvetica, sans-serif; color:#000;">You will be awarded loyalty coins after payment is made at venue.</p>
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="10">
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td width="30%" valign="top" style="font-family:Arial, Helvetica, sans-serif; color:#000;">Cancellation Policy</td>
							                        <td style="font-family:Arial, Helvetica, sans-serif; color:#000;">You have the right to cancel '.$refund_policy.' hours prior to date of reservation. <br><br>If canceled later or in case of no show, the venue has the right to block you from future reservations at their venue.</td>

							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="10">
							                        </td>
							                    </tr>
							                    <tr>
							                        <td colspan="2" style="color:#000;">
							                        <h4 style="font-size:18px; margin:0; font-family:Arial, Helvetica, sans-serif; font-weight:normal; height:30px; color:#000;">Payment</h4>
							                        You have now conﬁrmed and guaranteed your reservation.<br>All payments are to be made at the venue, unless otherwise stated in the policies.
							</td>

							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20">
							                        </td>
							                    </tr>
							                    <tr>
							                        <td colspan="2" style="color:#000;">
							                        <h4 style="font-size:18px; margin:0; font-weight:normal; height:30px; font-family:Arial, Helvetica, sans-serif; color:#000;">Venue Policy</h4>
													</td>
							                    </tr>
							                    <tr>
							                    	<td style="font-family:Arial, Helvetica, sans-serif;color:#000;color:#000;">Valet Parking</td>
							                        <td style="font-family:Arial, Helvetica, sans-serif;color:#000;">Depending on the venue, this may come at an additional charge.</td>
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
							                        <td style="font-family:Arial, Helvetica, sans-serif;color:#000;">'.$this->contact_phone_no.'</td>
							                    </tr>
							                    <tr>
							                    	<td style="font-family:Arial, Helvetica, sans-serif;color:#000;">Contact us by email</td>
							                        <td style="font-family:Arial, Helvetica, sans-serif;color:#000;"><a href="mailto:contact@beseatedapp.com" style="color:#000; text-decoration:none">contact@beseatedapp.com</a></td>
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
							                                	<td align="center"><a href="'.$this->app_store_link.'"><img src="'.JUri::base().'images/edms/images/iphonapp-btn.png"></a></td>
							                                    <td align="center"><a href="'.$this->google_play_link.'"><img src="'.JUri::base().'images/edms/images/google-play-btn.png"></a></td>
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
							                        	<a href="#" style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif;">My Beseated ID</a>&nbsp;|&nbsp;<a style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="'.$this->support_link.'">Support</a>&nbsp;|&nbsp;<a style="text-decoration:none; font-size:14px; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="'.$this->privacy_policy_link.'">Privacy Policy</a>
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000;">Copyright © 2016 <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>. All rights reserved.</td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000;">This email was sent by <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>, THUB, Dubai Silicon Oasis, Dubai, United Arab Emirates</td>
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

		self::sendEmail($email,$emailSubject, $emailBody);
	}

	public function yachtBookingconfirmedUserMail($username,$companyName,$thumb,$yacht_booking_id,$bookingDate,$bookingTime,$companyName,$service_name,$dock,$capacity,$total_hours,$username,$userEmail,$currency_code,$price_per_hours,$total_hours,$total_price,$loyaltyPoint,$refund_policy,$email)
	{
		$thumb = (getimagesize($thumb)) ? $thumb : $this->blank_img;
		//$email =  "jamal@tasolglobal.com";

		$lang = JFactory::getLanguage();
		$extension = 'com_beseated';
		$base_dir = JPATH_SITE;
		$language_tag = 'en-GB';
		$reload = true;
		$lang->load($extension, $base_dir, $language_tag, $reload);

		$emailSubject  = JText::sprintf('COM_BESEATED_TO_BOOKING_CONFIRMED_EMAIL_SUBJECT',$username);

		$emailBody        = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
							<html>
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
							                    	<td align="left"><a href="'.$this->logo_link.'"><img src="'.JUri::base().'images/edms/images/beseated-logo.png"></a></td>
							                        <td align="right"><a href="'.JUri::base().'index.php?option=com_beseated&view=yachtmail&loyaltyPoint='.$loyaltyPoint.'&bookingID='.$yacht_booking_id.'"><img src="'.JUri::base().'images/edms/images/print-btn.png"></a></td>
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
							                                	<td colspan="2" style="padding-bottom:0px;"><h4 style="margin:0; line-height:normal; color:#000; font-family:Arial, Helvetica, sans-serif; font-weight:normal; font-size:20px;"><b>'.$companyName.'</b></h4></td>
							                                </tr>
							                                <tr>
							                                	<td colspan="2">
							                                    	<table  width="100%" border="0" cellpadding="0" cellspacing="0">
							                                        	<tr>
							                                                <td>
							                                                    <table width="100%" border="0" cellpadding="0" cellspacing="0">
																					<tr>
																					    <td height="60" align="left">
																					     <img src ='.$thumb.' height="80" width="130"  style="border:2px solid #000;">
																					    </td>
																					    <td width="250">
																					    </td>

																					 </tr>
																				</table>
							                                                </td>
							                                                <td valign="middle">
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
							                                    <td width="50%" style="border-bottom:1px solid #b78f40; font-family:Arial, Helvetica, sans-serif;color:#000;">Reservation number</td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif;color:#000;">'.$yacht_booking_id.'</td>
							                                </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40; font-family:Arial, Helvetica, sans-serif;color:#000;">Reservation Date</td>
							                                    <td style="border-bottom:1px solid #b78f40; font-family:Arial, Helvetica, sans-serif;color:#000;">'.$bookingDate.'</td>
							                                </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40; font-family:Arial, Helvetica, sans-serif; color:#000;">Reservation Time</td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$bookingTime.'</td>
							                                </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Yacht Name</td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$companyName.'</td>
							                                </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Yacht Type</td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$service_name.'</td>
							                                </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Dock Location </td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$dock.'</td>
							                                </tr>
							                                 <tr>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Capacity</td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$capacity.' People</td>
							                                </tr>
							                                 <tr>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Hours</td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$total_hours.' Hours</td>
							                                </tr>
							                                <tr>
							                                    <td style="font-family:Arial, Helvetica, sans-serif; color:#000;">Booked by</td>
							                                    <td style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$username.' (<a href="mailto:'.$userEmail.'" style="color:#000; text-decoration:none;">'.$userEmail.'</a>)</td>
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
							                                                        	<td width="180" style="font-size:18px; color:#000; font-family:Arial, Helvetica, sans-serif;">LUXURY</td>
							                                                            <td width="160" style="font-size:18px; color:#000; font-family:Arial, Helvetica, sans-serif;">PRICE/HOUR </td>
							                                                            <td width="100" style="font-size:18px; color:#000; font-family:Arial, Helvetica, sans-serif;">HOURS</td>
							                                                            <td style="font-size:18px; color:#000; font-family:Arial, Helvetica, sans-serif;">TOTAL</td>
																					<tr>
																						<td colspan="4">
																							<table width="100%" cellpadding="0" cellspacing="0">
										                                                        <tr>
										                                                        	<td width="190" style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$companyName.'</td>
										                                                            <td width="170" style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$currency_code.' '.$price_per_hours.' </td>
										                                                            <td width="115" style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$total_hours.' Hours</td>
							                                                                        <td style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$currency_code.' '.$total_price.'</td>
										                                                        </tr>
																							</table>
																						</td>
																					</tr>
							                                                        <tr>
							                                                        	<td colspan="4">
							                                                            	<span style="margin:0; text-decoration:underline;font-family:Arial, Helvetica, sans-serif; color:#000;">Please Note:</span>
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
							                    	<td width="30%" valign="top" style="font-family:Arial, Helvetica, sans-serif; color:#000;">Cancellation Policy</td>
							                        <td style="font-family:Arial, Helvetica, sans-serif; color:#000;">You have the right to cancel '.$refund_policy.' hours prior to date of reservation.<br><br>If canceled later or in case of no show, the company has the right to charge you 100% of the booking amount.</td>

							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="10">
							                        </td>
							                    </tr>
							                    <tr>
							                        <td colspan="2" style="color:#000;">
							                        <h4 style="font-size:18px; margin:0; font-family:Arial, Helvetica, sans-serif; font-weight:normal; height:30px; color:#000;">Payment</h4>
							                        You have now conﬁrmed and guaranteed your reservation.<br>All payments are made on the BESEATED platform.
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
							                        <td style="font-family:Arial, Helvetica, sans-serif;color:#000;">'.$this->contact_phone_no.'</td>
							                    </tr>
							                    <tr>
							                    	<td style="font-family:Arial, Helvetica, sans-serif;color:#000;">Contact us by email</td>
							                        <td style="font-family:Arial, Helvetica, sans-serif;color:#000;"><a href="mailto:contact@beseatedapp.com" style="color:#000; text-decoration:none">contact@beseatedapp.com</a></td>
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
							                                	<td align="center"><a href="'.$this->app_store_link.'"><img src="'.JUri::base().'images/edms/images/iphonapp-btn.png"></a></td>
							                                    <td align="center"><a href="'.$this->google_play_link.'"><img src="'.JUri::base().'images/edms/images/google-play-btn.png"></a></td>
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
							                        	<a href="#" style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif;">My Beseated ID</a>&nbsp;|&nbsp;<a style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="'.$this->support_link.'">Support</a>&nbsp;|&nbsp;<a style="text-decoration:none; font-size:14px; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="'.$this->privacy_policy_link.'">Privacy Policy</a>
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000;">Copyright © 2016 <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>. All rights reserved.</td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000;">This email was sent by <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>, THUB, Dubai Silicon Oasis, Dubai, United Arab Emirates</td>
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
							</html>';

		self::sendEmail($email,$emailSubject, $emailBody);
	}

	public function protectionBookingconfirmedUserMail($username,$companyName,$thumb,$protection_booking_id,$bookingDate,$bookingTime,$companyName,$total_hours,$totalGuard,$username,$userEmail,$currency_code,$price_per_hours,$total_price,$loyaltyPoint,$refund_policy,$email)
	{
		$thumb = (getimagesize($thumb)) ? $thumb : $this->blank_img;

		$lang = JFactory::getLanguage();
		$extension = 'com_beseated';
		$base_dir = JPATH_SITE;
		$language_tag = 'en-GB';
		$reload = true;
		$lang->load($extension, $base_dir, $language_tag, $reload);

		$emailSubject  = JText::sprintf('COM_BESEATED_TO_BOOKING_CONFIRMED_EMAIL_SUBJECT',$username);

		$emailBody        = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
							<html>
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
							                    	<td align="left"><a href="'.$this->logo_link.'"><img src="'.JUri::base().'images/edms/images/beseated-logo.png"></a></td>
							                        <td align="right"><a href="'.JUri::base().'index.php?option=com_beseated&view=protectionmail&loyaltyPoint='.$loyaltyPoint.'&bookingID='.$protection_booking_id.'"><img src="'.JUri::base().'images/edms/images/print-btn.png"></a></td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="10"></td>
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
							                                	<td colspan="2" style="padding-bottom:0px;"><h4 style="margin:0px;line-height:normal; color:#000; font-family:Arial, Helvetica, sans-serif; font-weight:normal; font-size:20px;"><b>'.$companyName.'</b></h4></td>
							                                </tr>
							                                <tr>
							                                	<td colspan="2">
							                                    	<table  width="100%" border="0" cellpadding="0" cellspacing="0">
							                                        	<tr>
							                                                <td>
							                                                    <table width="100%" border="0" cellpadding="0" cellspacing="0">
							                                                        <tr>
							                                                            <td height="60" align="left">
							                                                             <img src ='.$thumb.' height="80" width="130"  style="border:2px solid #000;">
							                                                            </td>
							                                                            <td width="250">
							                                                            </td>

							                                                        </tr>
							                                                    </table>
							                                                </td>
							                                                <td valign="middle">
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
							                                    <td width="50%" style="border-bottom:1px solid #b78f40; font-family:Arial, Helvetica, sans-serif;color:#000;">Reservation number</td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif;color:#000;">'.$protection_booking_id.'</td>
							                                </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40; font-family:Arial, Helvetica, sans-serif;color:#000;">Reservation Date</td>
							                                    <td style="border-bottom:1px solid #b78f40; font-family:Arial, Helvetica, sans-serif;color:#000;">'.$bookingDate.'</td>
							                                </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40; font-family:Arial, Helvetica, sans-serif; color:#000;">Reservation Time</td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$bookingTime.'</td>
							                                </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Bodyguard Name </td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$companyName.'</td>
							                                </tr>
							                                 <tr>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Bodyguard Quantity</td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$totalGuard.'</td>
							                                </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Hours</td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$total_hours.' Hours</td>
							                                </tr>
							                                <tr>
							                                    <td style="font-family:Arial, Helvetica, sans-serif; color:#000;">Booked by</td>
							                                    <td style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$username.'(<a href="mailto:'.$userEmail.'" style="color:#000; text-decoration:none;">'.$userEmail.'</a>)</td>
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
							                                                        	<td width="180" style="font-size:18px; color:#000; font-family:Arial, Helvetica, sans-serif;">LUXURY</td>
							                                                            <td width="160" style="font-size:18px; color:#000; font-family:Arial, Helvetica, sans-serif;">PRICE/HOUR </td>
							                                                            <td width="100" style="font-size:18px; color:#000; font-family:Arial, Helvetica, sans-serif;">HOURS</td>
							                                                            <td style="font-size:18px; color:#000; font-family:Arial, Helvetica, sans-serif;">TOTAL</td>
																					<tr>
																						<td colspan="4">
																							<table width="100%" cellpadding="0" cellspacing="0">
										                                                        <tr>
										                                                        	<td width="190" style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$companyName.'</td>
										                                                            <td width="170" style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$currency_code.' '.$price_per_hours.' </td>
										                                                            <td width="115" style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$total_hours.' Hours</td>
							                                                                        <td style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$currency_code.' '.$total_price.' </td>
										                                                        </tr>
																							</table>
																						</td>
																					</tr>
							                                                        <tr>
							                                                        	<td colspan="4">
							                                                            	<span style="margin:0; text-decoration:underline;font-family:Arial, Helvetica, sans-serif; color:#000;">Please Note:</span>
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
							                        <h3 style="font-weight:normal; margin:0; font-size:24px; font-family:Arial, Helvetica, sans-serif; color:#000;">You have been awarded  &nbsp;&nbsp;<img style="vertical-align:middle;" src="'.JUri::base().'images/edms/images/money-icn-big.png">&nbsp;'.$loyaltyPoint.' </h3>
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20">
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td width="30%" valign="top" style="font-family:Arial, Helvetica, sans-serif; color:#000;">Cancellation Policy</td>
							                        <td style="font-family:Arial, Helvetica, sans-serif; color:#000;">You have the right to cancel '.$refund_policy.' hours prior to date of reservation.<br><br>If canceled later or in case of no show, the company has the right to charge you 100% of the booking amount.</td>

							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="10">
							                        </td>
							                    </tr>
							                    <tr>
							                        <td colspan="2" style="color:#000;">
							                        <h4 style="font-size:18px; margin:0; font-family:Arial, Helvetica, sans-serif; font-weight:normal; height:30px; color:#000;">Payment</h4>
							                        You have now conﬁrmed and guaranteed your reservation. <br>All payments are made on the BESEATED platform.</td>

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
							                        <td style="font-family:Arial, Helvetica, sans-serif;color:#000;">'.$this->contact_phone_no.'</td>
							                    </tr>
							                    <tr>
							                    	<td style="font-family:Arial, Helvetica, sans-serif;color:#000;">Contact us by email</td>
							                        <td style="font-family:Arial, Helvetica, sans-serif;color:#000;"><a href="mailto:contact@beseatedapp.com" style="color:#000; text-decoration:none">contact@beseatedapp.com</a></td>
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
							                                	<td align="center"><a href="'.$this->app_store_link.'"><img src="'.JUri::base().'images/edms/images/iphonapp-btn.png"></a></td>
							                                    <td align="center"><a href="'.$this->google_play_link.'"><img src="'.JUri::base().'images/edms/images/google-play-btn.png"></a></td>
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
							                        	<a href="#" style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif;">My Beseated ID</a>&nbsp;|&nbsp;<a style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="'.$this->support_link.'">Support</a>&nbsp;|&nbsp;<a style="text-decoration:none; font-size:14px; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="'.$this->privacy_policy_link.'">Privacy Policy</a>
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000;">Copyright © 2016 <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>. All rights reserved.</td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000;">This email was sent by <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>, THUB, Dubai Silicon Oasis, Dubai, United Arab Emirates</td>
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


		self::sendEmail($email,$emailSubject, $emailBody);
	}

	public function chauffeurBookingconfirmedUserMail($username,$companyName,$thumb,$chauffeur_booking_id,$bookingDate,$bookingTime,$companyName,$service_name,$pickup_location,$dropoff_location,$capacity,$username,$currency_code,$total_price,$userEmail,$loyaltyPoint,$refund_policy,$email)
	{
		$thumb = (getimagesize($thumb)) ? $thumb : $this->blank_img;

		$lang = JFactory::getLanguage();
		$extension = 'com_beseated';
		$base_dir = JPATH_SITE;
		$language_tag = 'en-GB';
		$reload = true;
		$lang->load($extension, $base_dir, $language_tag, $reload);

		$emailSubject  = JText::sprintf('COM_BESEATED_TO_BOOKING_CONFIRMED_EMAIL_SUBJECT',$username);

		$emailBody        = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
							<html>
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
							                    	<td align="left"><a href="'.$this->logo_link.'"><img src="'.JUri::base().'images/edms/images/beseated-logo.png"></a></td>
							                        <td align="right"><a href="'.JUri::base().'index.php?option=com_beseated&view=chauffeurmail&loyaltyPoint='.$loyaltyPoint.'&bookingID='.$chauffeur_booking_id.'"><img src="'.JUri::base().'images/edms/images/print-btn.png"></a></td>
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
							                                	<td colspan="2" style="padding-bottom:0px;"><h4 style="margin:0; line-height:normal; color:#000; font-family:Arial, Helvetica, sans-serif; font-weight:normal; font-size:20px;"><b>'.$companyName.'</b></h4></td>
							                                </tr>
							                                <tr>
							                                	<td colspan="2">
							                                    	<table  width="100%" border="0" cellpadding="0" cellspacing="0">
							                                        	<tr>
							                                                <td>
							                                                    <table width="100%" border="0" cellpadding="0" cellspacing="0">
																					<tr>
																					    <td height="60" align="left">
																					     <img src ='.$thumb.' height="80" width="130"  style="border:2px solid #000;">
																					    </td>
																					    <td width="250">
																					    </td>

																					</tr>
																				</table>
							                                                </td>
							                                                <td valign="middle">
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
							                                    <td width="50%" style="border-bottom:1px solid #b78f40; font-family:Arial, Helvetica, sans-serif;color:#000;">Reservation number</td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif;color:#000;">'.$chauffeur_booking_id.'</td>
							                                </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40; font-family:Arial, Helvetica, sans-serif;color:#000;">Reservation Date</td>
							                                    <td style="border-bottom:1px solid #b78f40; font-family:Arial, Helvetica, sans-serif;color:#000;">'.$bookingDate.'</td>
							                                </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40; font-family:Arial, Helvetica, sans-serif; color:#000;">Pickup Time</td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$bookingTime.'</td>
							                                </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Chauffeur Name </td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$companyName.'</td>
							                                </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Chauffeur Type </td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$service_name.'</td>
							                                </tr>

							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Pickup Location</td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$pickup_location.'</td>
							                                </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Dropoff Location </td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$dropoff_location.'</td>
							                                </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Capacity</td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$capacity.' People</td>
							                                </tr>
							                                <tr>
							                                    <td style="font-family:Arial, Helvetica, sans-serif; color:#000;">Booked by</td>
							                                    <td style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$username.' (<a href="mailto:'.$userEmail.'" style="color:#000; text-decoration:none;">'.$userEmail.'</a>)</td>
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
							                                                        	<td width="80" style="font-size:18px; color:#000; font-family:Arial, Helvetica, sans-serif;">FARE</td>
							                                                            <td style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$currency_code.' '.$total_price.' </td></tr>

							                                                        <tr>
							                                                        	<td colspan="2">
							                                                            	<span style="margin:0; text-decoration:underline;font-family:Arial, Helvetica, sans-serif; color:#000;">Please Note:</span>
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
							                        <h3 style="font-weight:normal; margin:0; font-size:24px; font-family:Arial, Helvetica, sans-serif; color:#000;">You have been awarded  &nbsp;&nbsp;<img style="vertical-align:middle;" src="'.JUri::base().'images/edms/images/money-icn-big.png">&nbsp;'.$loyaltyPoint.' </h3>
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20">
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td width="30%" valign="top" style="font-family:Arial, Helvetica, sans-serif; color:#000;">Cancellation Policy</td>
							                        <td style="font-family:Arial, Helvetica, sans-serif; color:#000;">You have the right to cancel '.$refund_policy.' hours prior to date of pickup. <br><br>If canceled later or in case of no show, the company has the right to charge you 100% of the booking amount.</td>

							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="10">
							                        </td>
							                    </tr>
							                    <tr>
							                        <td colspan="2" style="color:#000;">
							                        <h4 style="font-size:18px; margin:0; font-family:Arial, Helvetica, sans-serif; font-weight:normal; height:30px; color:#000;">Payment</h4>
							                        You have now conﬁrmed and guaranteed your reservation. <br>All payments are made on the BESEATED platform.</td>

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
							                        <td style="font-family:Arial, Helvetica, sans-serif;color:#000;">'.$this->contact_phone_no.'</td>
							                    </tr>
							                    <tr>
							                    	<td style="font-family:Arial, Helvetica, sans-serif;color:#000;">Contact us by email</td>
							                        <td style="font-family:Arial, Helvetica, sans-serif;color:#000;"><a href="mailto:contact@beseatedapp.com" style="color:#000; text-decoration:none">contact@beseatedapp.com</a></td>
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
							                                	<td align="center"><a href="'.$this->app_store_link.'"><img src="'.JUri::base().'images/edms/images/iphonapp-btn.png"></a></td>
							                                    <td align="center"><a href="'.$this->google_play_link.'"><img src="'.JUri::base().'images/edms/images/google-play-btn.png"></a></td>
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
							                        	<a href="#" style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif;">My Beseated ID</a>&nbsp;|&nbsp;<a style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="'.$this->support_link.'">Support</a>&nbsp;|&nbsp;<a style="text-decoration:none; font-size:14px; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="'.$this->privacy_policy_link.'">Privacy Policy</a>
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000;">Copyright © 2016 <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>. All rights reserved.</td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000;">This email was sent by <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>, THUB, Dubai Silicon Oasis, Dubai, United Arab Emirates</td>
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
							</html>';

		self::sendEmail($email,$emailSubject, $emailBody);
	}


	public function orderConfirmRefundFailedMail($emailSubject,$payment_id,$orderreferenceNo,$reason,$amount,$cust_email,$orderDate)
	{
		$lang = JFactory::getLanguage();
		$extension = 'com_beseated';
		$base_dir = JPATH_SITE;
		$language_tag = 'en-GB';
		$reload = true;
		$lang->load($extension, $base_dir, $language_tag, $reload);

		$emailBody        = '<html>
							<body>
								<table border="3" bgcolor="#FFFFFF" align="center" style="font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:20px;">
								<tr>
								<th>Order #</th>
								<th>CCAvenue Reference #</th>
								<th>Order Date</th>
								<th>Customer Email id</th>
								<th>reason</th>
								<th>amount</th>
								</tr>
								<tr>
								<td>'.$payment_id.'</td>
								<td>'.$orderreferenceNo.'</td>
								<td>'.date('d/m/Y H:i:s',strtotime($orderDate)).'</td>
								<td>'.$cust_email.'</td>
								<td>'.$reason.'</td>
								<td>'.$amount.'</td>
								</tr>
								</table>
							</body>
							</html>';

			$query = "SELECT user_id FROM `#__user_usergroup_map` WHERE group_id = 8";
			$db = JFactory::getDbo();
			$db->setQuery($query);
			$adminsIds = $db->loadColumn();

			$query_users = "SELECT email FROM `#__users` WHERE id IN (".implode(",", $adminsIds).")";
			$db->setQuery($query_users);
			$adminEmails = $db->loadColumn();


			/*for ($i = 0; $i < count($adminEmails); $i++)
			{*/
				//self::sendEmail($adminEmails[$i],$subject,$body);
				self::sendEmail("jamal@tasolglobal.com",$emailSubject,$emailBody);
			//}

	}

	public function rewardBookingAdminMail($username,$userEmail,$phone,$reward_name,$reward_desc,$image,$reward_desc,$reward_coin,$totalLoyaltyPoint)
	{
		$image = JUri::base().$image;
		//$email =  "jamal@tasolglobal.com";
		$image = (getimagesize($image)) ? $image : $this->blank_img;

		$lang = JFactory::getLanguage();
		$extension = 'com_beseated';
		$base_dir = JPATH_SITE;
		$language_tag = 'en-GB';
		$reload = true;
		$lang->load($extension, $base_dir, $language_tag, $reload);

		$emailSubject  = JText::sprintf('COM_BESEATED_TO_REWARD_BOOKING_EMAIL_SUBJECT');

		$emailBody        = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
							<html>
							<head>
							<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
							<title>Beseated</title>
							<style type="text/css" media="screen">
								p{margin: 0 !important; color: #000;}
								.ExternalClass p { color: #000; margin: 0px;}
							</style>
							</head>
							<body>
							<table border="0" bgcolor="#FFFFFF" cellpadding="0" cellspacing="0" width="650" align="center" style="font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:20px;">
								<tbody>
							    	<tr>
							        	<td height="10"></td>
							        </tr>
							        <tr>
							        	<td align="center">
							            	<table width="630" border="0" cellpadding="0" cellspacing="0">
							                	<tr>
							                        <td align="center" colspan="2">
							                        	<table width="100%" cellpadding="0" cellspacing="0">
							                            	<tr>
							                                	<td align="left" width="100"><a href="'.$this->logo_link.'"><img src="'.JUri::base().'images/edms/images/beseated-logo.png"></a></td>
							                                    <td align="center"></td>
							                                </tr>
							                            </table>
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20"></td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2"><h3 style="margin:0; line-height:normal; color:#000; font-family:Arial, Helvetica, sans-serif; font-weight:normal;">Dear Admin,<br><br>Following is the detail for reward booking</h3></td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20"></td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2">
							                        	<table width="100%" border="0" cellpadding="10" cellspacing="0" bgcolor="e3d1b0">
							                            	<tr>
							                                	<td colspan="2" style="padding-bottom:0px;"><h4 style="margin:0; line-height:normal; color:#000; font-family:Arial, Helvetica, sans-serif; font-weight:normal; font-size:20px;"><b>'.$reward_name.'</b></h4></td>
							                                </tr>
							                                <tr>
							                                	<td width="320">
							                                    	<table width="130" cellpadding="0" cellspacing="0">
							                                        	<tr>
							                                            	<td style="border:1px solid #000;" height="80"><img src ="'.$image.'" height="80" width="130"></td>
							                                            </tr>
							                                        </table>
							                                    </td>
							                                    <td  style="margin:0; line-height:normal; color:#000; font-family:Arial, Helvetica, sans-serif; font-weight:normal; font-size:18px;"></td>
							                                </tr>
							                                <tr>
										                    	<td colspan="2"></td>
										                    </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40; font-family:Arial, Helvetica, sans-serif;color:#000;">Name</td>
							                                    <td style="border-bottom:1px solid #b78f40; font-family:Arial, Helvetica, sans-serif;color:#000;">'.$username.'</td>
							                                </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40; font-family:Arial, Helvetica, sans-serif; color:#000;">Email </td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$userEmail.'</td>
							                                </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Phone No</td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$phone.'</td>
							                                </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Reward Description</td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$reward_desc.'</td>
							                                </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Loyalty Points Used</td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$reward_coin.'</td>
							                                </tr>
							                                <tr>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">Total Loyalty Points</td>
							                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif; color:#000;">'.$totalLoyaltyPoint.'</td>
							                                </tr>
							                            </table>
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20">
							                        </td>
							                    </tr>

							                    <tr>
							                    	<td colspan="2" height="30">
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" height="20" style=" border-top:1px solid #c09439;"></td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="color:#5f5f5f;">
							                        	<a href="#" style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif;">My Beseated ID</a>&nbsp;|&nbsp;<a style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="'.$this->support_link.'">Support</a>&nbsp;|&nbsp;<a style="text-decoration:none; font-size:14px; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="'.$this->privacy_policy_link.'">Privacy Policy</a>
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000;">Copyright © 2016 <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>. All rights reserved.</td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000;">This email was sent by <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>, THUB, Dubai Silicon Oasis, Dubai, United Arab Emirates</td>
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

			$query = "SELECT user_id FROM `#__user_usergroup_map` WHERE group_id = 8";
			$db = JFactory::getDbo();
			$db->setQuery($query);
			$adminsIds = $db->loadColumn();

			$query_users = "SELECT email FROM `#__users` WHERE id IN (".implode(",", $adminsIds).")";
			$db->setQuery($query_users);
			$adminEmails = $db->loadColumn();


			for ($i = 0; $i < count($adminEmails); $i++)
			{
				self::sendEmail($adminEmails[$i],$emailSubject,$emailBody);
				//self::sendEmail("jamal@tasolglobal.com",$emailSubject,$emailBody);
			}
	}

	public function eventBookingconfirmedUserMail($username,$event_name,$bookingID ,$eventImage,$event_date,$location,$total_ticket,$ticketsRow,$userEmail,$booking_currency_code,$ticket_price,$total_price,$loyaltyPoint,$email)
	{
		$eventImage = JUri::base().'images/beseated/'.$eventImage;

		$eventImage = (getimagesize($eventImage)) ? $eventImage : $this->blank_img;

		$lang = JFactory::getLanguage();
		$extension = 'com_beseated';
		$base_dir = JPATH_SITE;
		$language_tag = 'en-GB';
		$reload = true;
		$lang->load($extension, $base_dir, $language_tag, $reload);

		$emailSubject  = JText::sprintf('COM_BESEATED_TO_BOOKING_CONFIRMED_EMAIL_SUBJECT',$username);

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
							                    	<td align="left" width="100"><a href="'.$this->logo_link.'"><img src="'.JUri::base().'images/edms/images/beseated-logo.png"></a></td>
							                        <td align="right"><a href="'.JUri::base().'index.php?option=com_beseated&view=eventmail&loyaltyPoint='.$loyaltyPoint.'&bookingID='.$bookingID.'"><img src="'.JUri::base().'images/edms/images/print-btn.png" width="200" height="39"></a></td>
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

							                                                            <td><img style="border:1px solid #000;" src ="'.$eventImage.'" height="80" width="130"></td>

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
							                                    <td style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$username.' (<a href="mailto:'.$userEmail.'" style="color:#000; text-decoration:none;">'.$userEmail.'</a>)</td>
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
							                        <td style="font-family:Arial, Helvetica, sans-serif;color:#000;">'.$this->contact_phone_no.'</td>
							                    </tr>
							                    <tr>
							                    	<td style="font-family:Arial, Helvetica, sans-serif;color:#000;">Contact us by email</td>
							                        <td style="font-family:Arial, Helvetica, sans-serif;color:#000;"><a href="mailto:contact@beseatedapp.com" style="color:#000; text-decoration:none">contact@thebeseated.com</a></td>
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
							                                	<td align="center"><a href="'.$this->app_store_link.'"><img src="'.JUri::base().'images/edms/images/iphonapp-btn.png"></a></td>
							                                    <td align="center"><a href="'.$this->google_play_link.'"><img src="'.JUri::base().'images/edms/images/google-play-btn.png"></a></td>
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
							                        	<a href="#" style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif;">My Beseated ID</a>&nbsp;|&nbsp;<a style="text-decoration:none; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="'.$this->support_link.'">Support</a>&nbsp;|&nbsp;<a style="text-decoration:none; font-size:14px; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="'.$this->privacy_policy_link.'">Privacy Policy</a>
							                        </td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000;">Copyright © 2016 <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>. All rights reserved.</td>
							                    </tr>
							                    <tr>
							                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000;">This email was sent by <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>, THUB, Dubai Silicon Oasis, Dubai, United Arab Emirates</td>
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

				self::sendEmail($email,$emailSubject, $emailBody);

	}

	public function eventInvitationMailUser($eventImage,$event_name,$event_date,$event_time,$eventLocation,$bookingOwnerName,$bookingOwnerEmail,$booking_currency_code,$ticket_price,$ticketImage,$email)
	{
		$eventImage = JUri::base().'images/beseated/'.$eventImage;

		$eventImage = (getimagesize($eventImage)) ? $eventImage : $this->blank_img;

		$lang = JFactory::getLanguage();
		$extension = 'com_beseated';
		$base_dir = JPATH_SITE;
		$language_tag = 'en-GB';
		$reload = true;
		$lang->load($extension, $base_dir, $language_tag, $reload);

		$emailSubject  = JText::sprintf('COM_BESEATED_TO_EVENT_INVITATION_EMAIL_SUBJECT');

		$emailBody = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
						<html xmlns="http://www.w3.org/1999/xhtml">
						<head>
						<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
						<title>Beseated</title>
						<style type="text/css" media="screen">
							p{margin: 0 !important; color: #000;}
							.ExternalClass p { color: #000; margin: 0px;}
						</style>
						</head>
						<body>
						<table border="0" bgcolor="#FFFFFF" cellpadding="0" cellspacing="0" width="650" align="center" style="font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:20px;">
							<tbody>
						    	<tr>
						        	<td height="10"></td>
						        </tr>
						        <tr>
						        	<td align="center">
						            	<table width="630" border="0" cellpadding="0" cellspacing="0">
						                	<tr>
						                    	<td align="left" width="100"><a href="'.$this->logo_link.'"><img src="'.JUri::base().'images/edms/images/beseated-logo.png"></a></td>
						                        <td align="right"></a></td>
						                    </tr>
						                    <tr>
						                    	<td colspan="2" height="20"></td>
						                    </tr>
						                    <tr>
						                    	<td colspan="2" height="20"></td>
						                    </tr>
						                    <tr>
						                    	<td colspan="2">
						                        	<table width="100%" border="0" cellpadding="10" cellspacing="0" bgcolor="e3d1b0">
						                            	<tr>
						                                	<td colspan="2"><h4 style="margin:0; line-height:normal; color:#000; font-family:Arial, Helvetica, sans-serif; font-weight:normal; font-size:18px;">'.$event_name.'</h4></td>
						                                </tr>
						                                <tr>
						                                	<td colspan="2">
						                                    	<table  width="100%" border="0" cellpadding="0" cellspacing="0">
						                                        	<tr>
						                                                <td width="50%">
						                                                    <table width="100%" border="0" cellpadding="10" cellspacing="0">
						                                                        <tr>
						                                                            <td><img style="border:1px solid #000;" src ="'.$eventImage.'" height="80" width="130"></td>
						                                                            <td>&nbsp;
						                                                            </td>
						                                                        </tr>
						                                                    </table>
						                                                </td>
						                                                <td valign="middle" width="50%" align="right">

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
						                                    <td width="50%" style="border-bottom:1px solid #b78f40; font-family:Arial, Helvetica, sans-serif;color:#000;">Event Time</td>
						                                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif;color:#000;">'.$event_time.'</td>
						                                </tr>
						                                <tr>
						                                    <td style="border-bottom:1px solid #b78f40; font-family:Arial, Helvetica, sans-serif;color:#000;">Location</td>
						                                    <td style="border-bottom:1px solid #b78f40; font-family:Arial, Helvetica, sans-serif;color:#000;">'.$eventLocation.'</td>
						                                </tr>
						                                <tr>
						                                    <td style="font-family:Arial, Helvetica, sans-serif; color:#000;">Booked by</td>
						                                    <td style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$bookingOwnerName.' (<a href="mailto:'.$bookingOwnerEmail.'" style="color:#000; text-decoration:none;">'.$bookingOwnerEmail.'</a>)</td>
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
																				<tr>
																					<td colspan="2">
																						<table width="100%" cellpadding="0" cellspacing="0">
									                                                        <tr>
									                                                        	<td width="190" style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$event_name.'</td>
									                                                            <td width="170" style="font-family:Arial, Helvetica, sans-serif; color:#000;">&nbsp;'.$booking_currency_code.' '.$ticket_price.' </td>
									                                                        </tr>
																						</table>
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
							                    <td width="100px" style="font-family:Arial, Helvetica, sans-serif;color:#000;">'.$this->contact_phone_no.'</td>
							                </tr>
						                    <tr>
						                    	<td style="font-family:Arial, Helvetica, sans-serif;color:#000;">Contact us by email</td>
						                        <td style="font-family:Arial, Helvetica, sans-serif;color:#000;"><a href="mailto:contact@beseatedapp.com" style="color:#000; text-decoration:none">contact@thebeseated.com</a></td>
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
							                                <td align="center"><a href="'.$this->app_store_link.'"><img src="'.JUri::base().'images/edms/images/iphonapp-btn.png"></a></td>
							                                <td align="center"><a href="'.$this->google_play_link.'"><img src="'.JUri::base().'images/edms/images/google-play-btn.png"></a></td>
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
						                        	<a href="#" style="text-decoration:none; font-size:14px; color:#c09439; font-family:Arial, Helvetica, sans-serif;">My Beseated ID</a>&nbsp;|&nbsp;<a style="text-decoration:none; font-size:14px; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="'.$this->support_link.'">Support</a>&nbsp;|&nbsp;<a style="text-decoration:none; font-size:14px; color:#c09439; font-family:Arial, Helvetica, sans-serif;" href="'.$this->privacy_policy_link.'">Privacy Policy</a>
						                        </td>
						                    </tr>
						                    <tr>
						                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000;">Copyright © 2016 <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>. All rights reserved.</td>
						                    </tr>
						                    <tr>
						                    	<td colspan="2" align="center" style="font-family:Arial, Helvetica, sans-serif;color:#000;">This email was sent by <a href='.$this->site_link.' style="color:#000; text-decoration:none;">Beseatedapp.com</a>, THUB, Dubai Silicon Oasis, Dubai, United Arab Emirates</td>
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

	        self::sendTicketEmail($email,$emailSubject,$emailBody,$ticketImage);

	}

	public function sendTicketEmail($email,$subject,$body,$ticketImage)
	{
		require_once("tcpdf/tcpdf.php");
		$pdf = new TCPDF();

		error_reporting ( E_ALL );

		// add a page
		$pdf->AddPage();

		$pdf->SetFont("", "b", 16);
		$pdf->Write(16, "Event Ticket \n", "", 0, 'C');

		$txt = "";
		$pdf->Write ( 0, $txt );
		//$pdf->setImageScale ( PDF_IMAGE_SCALE_RATIO );
		//$pdf->setJPEGQuality ( 90 );
		$pdf->Image ($ticketImage);
		$pdf->WriteHTML ( $txt );

		$storePDF = JPATH_BASE.'/images/beseated/Ticket/EventTicket.pdf';

		//$pdf->Output('/var/www/sendMail/filename.pdf', 'I');
		$pdf->Output($storePDF, 'F');

		// Initialise variables.
		$app     = JFactory::getApplication();
		$config  = JFactory::getConfig();

		$site    = $config->get('sitename');
		$from    = $config->get('mailfrom');
		$sender  = $config->get('fromname');

		// Clean the email data.
		$sender  = JMailHelper::cleanAddress($sender);
		$subject = JMailHelper::cleanSubject($subject);
		$body    = JMailHelper::cleanBody($body);

		// Send the email.
		$return = JFactory::getMailer()->sendMail($from, $sender, $email, $subject, $body,true,$cc = null, $bcc = null, $storePDF);

		// Check for an error.
		if ($return !== true)
		{
			return new JException(JText::_('COM__SEND_MAIL_FAILED'), 500);
		}

		unlink($storePDF);
	}




}
