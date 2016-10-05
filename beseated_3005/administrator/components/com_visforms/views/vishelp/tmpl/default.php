<?php
/**
 * Vishelp default view for Visforms
 *
 * @author       Aicha Vack
 * @package      Joomla.Administrator
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de 
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2012 vi-solutions
 * @since        Joomla 1.6 
 */

//no direct access
 defined('_JEXEC') or die('Restricted access');
 
 if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif;
 
 echo '<h1>' . JText::_('COM_VISFORMS_HELP_HEADER') . '</h1>';
 echo '<h2 style="margin-left: 10px;">' . JText::_('COM_VISFORMS_HELP_GETTING_STARTED_HEADER') . '</h2>';
 echo '<div>' . JText::_('COM_VISFORMS_GETTING_STARTED') . '</div>';
 echo '<h2 style="margin-left: 10px;">' . JText::_('COM_VISFORMS_FAQ_HEADER') . '</h2>';
 echo '<h3 style="margin-left: 10px;">' . JText::_('COM_VISFORMS_FAQ_SHOW_IN_CONTENT_QUEST') . '</h3>';
 echo '<div>' . JText::_('COM_VISFORMS_FAQ_SHOW_IN_CONTENT_ANS') . '</div>';
 echo '<h2 style="margin-left: 10px;">' . JText::_('COM_VISFORMS_FORUM_HEADER') . '</h2>';
 echo '<div>' . JText::_('COM_VISFORMS_FORUM') . '</div>';
 echo '<h2 style="margin-left: 10px;">' . JText::_('COM_VISFORMS_DOKUMENTATION_HEADER') . '</h2>';
 echo '<div>' . JText::_('COM_VISFORMS_DOKUMENTATION') . '</div>';
 echo '<h2 style="margin-left: 10px;">' . JText::_('COM_VISFORMS_CREDIT_HEADER') . '</h2>';
 echo '<div>' . JText::_('COM_VISFORMS_CREDIT') . '</div>';
 ?>
    </div>
    </div>
 
 <?php JHtml::_('visforms.creditsBackend'); ?>
