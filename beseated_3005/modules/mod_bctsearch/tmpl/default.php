<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_search
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>

<style type="text/css">
.bs-element-contain {
    float: left;
    margin-right: 2%;
}


.search.bct-search {
    float: left;
    width: 99%;
}
</style>
<?php $app = JFactory::getApplication();
$menu     = $app->getMenu();
$menuItem = $menu->getItems( 'link', 'index.php?option=com_beseated&view=search', true );
?>
<?php $city = $app->input->cookie->get('search_in_city', '','RAW'); ?>
<div class="search<?php echo $moduleclass_sfx ?>">
	<form action="<?php echo JRoute::_('index.php?option=com_beseated&view=search&Itemid='.$menuItem->id);?>" method="post" class="form-inline" id="search_form">
		<div class="service_wrp">
			<div class="country_dd">
				<select id="provincesList1" name="country">
					<option value="" selected="selected">Select your City</option>
					<?php foreach ($country as $key => $value): ?>
						<?php if(!in_array(strtolower($value), $processCountry)) : ?>
							<?php $processCountry[] = strtolower($value); ?>
							<?php if(strtolower($city) == strtolower($value)): ?>
								<option selected="selected" value="<?php echo $value; ?>"> <?php echo ucfirst($value); ?></option>
							<?php else: ?>
								<option value="<?php echo $value; ?>"> <?php echo ucfirst($value); ?></option>
							<?php endif; ?>

						<?php endif; ?>

					<?php endforeach; ?>

				</select>
			</div>
			<!--<div class="club_btn"><button type="submit" name="club" value="club"><i class="club-icn">  </i>Venues  </button></div>
			<div class="service_btn"><button type="submit" name="service" value="service"><i class="service-icn"> </i>Services </button></div>-->
		</div>
	</form>
</div>
