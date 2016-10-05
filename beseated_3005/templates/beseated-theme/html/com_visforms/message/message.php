<?php 
/**
 * Visforms message view for Visforms
 *
 * @author       Aicha Vack
 * @package      Joomla.Site
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de 
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2012 vi-solutions
 * @since        Joomla 1.6 
 */


// no direct access
defined('_JEXEC') or die('Restricted access'); ?>

<?php if (isset($this->message) && ($this->message != "")) { ?>
<div class="visforms-form confirmation" id="visformcontainer">
	<h2 class="heading-1">Partner With Us</h2>
	<?php 
		JPluginHelper::importPlugin('content');
		echo JHtml::_('content.prepare', $this->message);
	?>
</div>
<?php } ?>
