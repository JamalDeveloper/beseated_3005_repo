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
$viewmode	= $params->get( 'viewmode', 0);
$show_desc	= $params->get( 'show_desc', 0);
$desc		= $params->get( 'desc', '');
$img_small	= $params->get( 'img_small', '');
$img_big	= $params->get( 'img_big', '');

$modbase 	= JURI::base(true) .'/modules/mod_bctedintro'; /* juri::base(true) will not added full path and slash at the path end */
// add style
$doc->addStyleSheet($modbase . '/assets/css/style.css');
// add javascript
$doc->addScript($modbase . '/assets/js/libs/prototype.js');
$doc->addScript($modbase . '/assets/js/libs/scriptaculous.js');
$doc->addScript($modbase . '/assets/js/libs/sizzle.js');
$doc->addScript($modbase . '/assets/js/loupe.js');

?>

<?php if ($show_desc) {
	echo $desc;
?>
<br class="clear" />
<?php } ?>

<!-- Loupe -->
<div class="loupe-gallery">
	<div class="loupe-container">
		<figure class="loupe-figure">
			<div class="loupe" data-initplacement="-40,-100" data-boundingbox="-30,-20,410,180" data-scale-ratio="2" data-src="<?php echo $img_big; ?>" data-displacementmap="<?php echo $modbase; ?>/assets/images/loupedisplacementmap.png">
				<img class="loupe-image" src="<?php echo $modbase; ?>/assets/images/loupe.png" width="245" height="257" alt="" />

				<div class="tooltip click"><?php echo ('Click and drag'); ?></div>
				<div class="tooltip touch"><?php echo ('Touch and move'); ?></div>
			</div>
			<div id="gallery-loupe">
				<img class="gallery-content content loupeView" src="<?php echo $img_small; ?>" width="559" height="316" alt="" />
			</div>
		</figure>
		<?php if (!$viewmode) { ?>
		<img class="loupe-still" src="<?php echo $modbase; ?>/assets/images/placeholder.png" width="940" height="415" alt="" />
		<?php } else { ?>
		<img class="loupe-still" src="<?php echo $modbase; ?>/assets/images/android/placeholder.png" width="940" height="415" alt="" />
		<?php } ?>

	</div>
</div>



