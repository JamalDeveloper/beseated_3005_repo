<?php
/**
	 * @package   AppImage Slider
	 * @version   1.0
	 * @author    Erwin Schro (http://www.joomla-labs.com)
	 * @author	  Based on BxSlider jQuery plugin script
	 * @copyright Copyright (C) 2013 J!Labs. All rights reserved.
	 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
	 *
	 * @copyright Joomla is Copyright (C) 2005-2013 Open Source Matters. All rights reserved.
	 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
	 */


defined('_JEXEC') or die('Restricted access');

$doc 	= JFactory::getDocument();


//$modbase 	= JURI::base(true) .'/modules/mod_bctcontactform'; /* juri::base(true) will not added full path and slash at the path end */
// add style
//$doc->addStyleSheet($modbase . '/assets/css/style.css');
// add javascript
/*$doc->addScript($modbase . '/assets/js/libs/prototype.js');
$doc->addScript($modbase . '/assets/js/libs/scriptaculous.js');
$doc->addScript($modbase . '/assets/js/libs/sizzle.js');
$doc->addScript($modbase . '/assets/js/loupe.js');*/
$user = JFactory::getUser(); ?>
?>

<?php /*if($user->id): ?>
	<style type="text/css">
		.ice-megamenu-toggle { float: right; }
	</style>
<?php else: ?>
	<style type="text/css">
		.ice-megamenu-toggle { float: left; }
	</style>
<?php endif; */?>
<div class="profile-logout-btn">
<form action="<?php echo JRoute::_('index.php?option=com_users&task=user.logout'); ?>" method="post" class="">
	<div class="control-group">
		<div class="controls">
			<button type="submit" class="btn btn-primary"><span class="icon-arrow-left icon-white"></span> <?php echo JText::_('JLOGOUT'); ?></button>
		</div>
	</div>
	<?php
		$app = JFactory::getApplication();
		$menu = $app->getMenu();
		$menuItem = $menu->getItems( 'link', 'index.php?option=com_users&view=login', true );
		$ItemId = $menuItem->id;
	?>
	<input type="hidden" name="return" id="return" value="<?php echo base64_encode('index.php?option=com_users&view=login&Itemid='.$ItemId); ?>">

	<?php echo JHtml::_('form.token'); ?>
</form>
</div>



