<?php
/**
 * @package     Beseated
 * @subpackage  com_beseated
 *
 * @copyright   Copyright (C) 2010 - 2014 Tailored Solutions PVT. Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * The Menu Item Controller
 *
 * @package     Beseated.Frontend
 * @subpackage  com_beseated
 * @since       1.0.0
 */
class Com_BeseatedInstallerScript
{
	/**
	 * Called on installation
	 *
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function install(JAdapterInstance $adapter)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Set default menu items if no menu present
		$query->select('count(*)')
			->from($db->qn('#__beseated_status'));

		// Set the query and load the result.
		$db->setQuery($query);

		$result = $db->loadResult();

		if ($result <= 0)
		{
			$query = "INSERT INTO `#__beseated_status` (`status_id`, `status_name`, `status_display`) VALUES
					(NULL, 'request', 'Request'),
					(NULL, 'pending', 'Pending'),
					(NULL, 'awaiting-payment', 'Awaiting Payment'),
					(NULL, 'available', 'Available')";
			$db->setQuery($query);
			$db->Query();
		}

		$this->displayMessage();
	}

	/**
	 * Called on update
	 *
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function update(JAdapterInstance $adapter)
	{
		$this->displayMessage();

		return true;
	}

	/**
	 * Display Beseated installation message
	 *
	 * @return  void
	 */
	public function displayMessage()
	{
		ob_start();
		?>
		<style type="text/css">
			.button-next {
				height: 45px;
				line-height: 45px;
				width: 250px;
				text-align: center;
				font-weight: bold;
				font-size: 12px;
				color: #000;
				border: solid 0px #690;
				border-radius: 5px;
				-moz-border-radius: 5px;
				-webkit-border-radius: 5px;
				cursor: pointer;
				padding-top: 9px;
				text-shadow: 1px 1px 1px #AAAAAA;
				box-shadow: 0px 0px 9px #000000;

				background: rgb(73, 72, 75); /* Old browsers */
				background: -moz-linear-gradient(top, rgba(73, 72, 75, 1) 0%, rgba(55, 55, 57, 1) 26%, rgba(0, 0, 0, 1) 27%, rgba(255, 152, 51, 1) 28%, rgba(255, 118, 2, 1) 100%); /* FF3.6+ */
				background: -webkit-gradient(linear, left top, left bottom, color-stop(0%, rgba(73, 72, 75, 1)), color-stop(26%, rgba(55, 55, 57, 1)), color-stop(27%, rgba(0, 0, 0, 1)), color-stop(28%, rgba(255, 152, 51, 1)), color-stop(100%, rgba(255, 118, 2, 1))); /* Chrome,Safari4+ */
				background: -webkit-linear-gradient(top, rgba(73, 72, 75, 1) 0%, rgba(55, 55, 57, 1) 26%, rgba(0, 0, 0, 1) 27%, rgba(255, 152, 51, 1) 28%, rgba(255, 118, 2, 1) 100%); /* Chrome10+,Safari5.1+ */
				background: -o-linear-gradient(top, rgba(73, 72, 75, 1) 0%, rgba(55, 55, 57, 1) 26%, rgba(0, 0, 0, 1) 27%, rgba(255, 152, 51, 1) 28%, rgba(255, 118, 2, 1) 100%); /* Opera 11.10+ */
				background: -ms-linear-gradient(top, rgba(73, 72, 75, 1) 0%, rgba(55, 55, 57, 1) 26%, rgba(0, 0, 0, 1) 27%, rgba(255, 152, 51, 1) 28%, rgba(255, 118, 2, 1) 100%); /* IE10+ */
				background: linear-gradient(to bottom, rgba(73, 72, 75, 1) 0%, rgba(55, 55, 57, 1) 26%, rgba(0, 0, 0, 1) 27%, rgba(255, 152, 51, 1) 28%, rgba(255, 118, 2, 1) 100%); /* W3C */
				filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#49484b', endColorstr='#ff7602', GradientType=0); /* IE6-9 */
			}

			.column {
				border-top: 0px;
				border-right: 1px solid #CCCCCC;
				border-bottom: 0px;
				border-left: 1px solid #AAAAAA;
				background-color: #FFFFDD;
				text-align: center;
				width: 50%;
				padding: 20px;
			}

		</style>

		<table width="81%" border="0" align="center" cellspacing="0px" cellpadding="10px">
			<tr>
				<td colspan="2">
					<div style="text-align:center;">
						<h1>Thank you for choosing,</h1>
						<?php $imgsrc = JURI::root() . 'media/com_beseated/images/beseated-component-logo.png'; ?>
						<img width="150" src="<?php echo $imgsrc; ?>" align="center">
						<br><font color="#105A8D" size="2"><b>Version 1.0.0</b></font></br>
					</div>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<div style="text-align:center;">
						<p>The Beseated is online service booking</p>

						<p>The Beseated is online service booking</p>
					</div>
				</td>
			</tr>
		</table>

		<?php
		$html = ob_get_contents();
		@ob_end_clean();
		echo $html;
	}

	/**
	 * Called on uninstallation
	 *
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 *
	 * @return  void
	 */
	public function uninstall(JAdapterInstance $adapter)
	{
		// Initialiase variables.

	}
}
