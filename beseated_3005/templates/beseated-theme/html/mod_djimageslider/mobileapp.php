<?php
/**
 * @version $Id: default.php 30 2015-11-04 11:15:22Z szymon $
 * @package DJ-ImageSlider
 * @subpackage DJ-ImageSlider Component
 * @copyright Copyright (C) 2012 DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 * @developer Szymon Woronowski - szymon.woronowski@design-joomla.eu
 *
 *
 * DJ-ImageSlider is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * DJ-ImageSlider is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with DJ-ImageSlider. If not, see <http://www.gnu.org/licenses/>.
 *
 */

// no direct access
defined('_JEXEC') or die ('Restricted access'); 

$wcag = $params->get('wcag', 1) ? ' tabindex="0"' : ''; ?>

<div class="beseated-app-slider" data-animation='<?php echo $animationOptions ?>' data-djslider='<?php echo $moduleSettings ?>'<?php echo $wcag; ?>>
  <h2 class="heading-1">
    Beseated App 
    <img src="<?php echo './templates/beseated-theme/images/android-logo.png' ?>" />
    <img src="<?php echo './templates/beseated-theme/images/apple-logo.png' ?>" />
  </h2>
  <div class="slider-container">
    <ul id="slider<?php echo $mid; ?>" >
  		<?php foreach ($slides as $slide) { ?>
  			<li class="slide">
            <h3 class="section-title"><?php echo $slide->title; ?></h3>
            <?php echo $slide->description; ?>
            <img class="image" src="<?php echo $slide->image; ?>" alt="<?php echo $slide->alt; ?>" style="<?php echo $style['image'] ?>"/>			    	
	    	</li>
	    <?php } ?>	
  	</ul>
    <?php if($show->arr || $show->btn) { ?>
      <div id="navigation<?php echo $mid; ?>" class="navigation-container">
        <?php if($show->arr) { ?>
        	<img id="prev<?php echo $mid; ?>" class="prev-button <?php echo $show->arr==1 ? 'showOnHover':'' ?>" src="<?php echo './templates/beseated-theme/images/arrow-left.png' ?>" alt="<?php echo $direction == 'rtl' ? JText::_('MOD_DJIMAGESLIDER_NEXT') : JText::_('MOD_DJIMAGESLIDER_PREVIOUS'); ?>"<?php echo $wcag; ?> />
			    <img id="next<?php echo $mid; ?>" class="next-button <?php echo $show->arr==1 ? 'showOnHover':'' ?>" src="<?php echo './templates/beseated-theme/images/arrow-right.png' ?>" alt="<?php echo $direction == 'rtl' ? JText::_('MOD_DJIMAGESLIDER_PREVIOUS') : JText::_('MOD_DJIMAGESLIDER_NEXT'); ?>"<?php echo $wcag; ?> />
			  <?php } ?>
			  <?php if($show->btn) { ?>
			    <img id="play<?php echo $mid; ?>" class="play-button <?php echo $show->btn==1 ? 'showOnHover':'' ?>" src="<?php echo $navigation->play; ?>" alt="<?php echo JText::_('MOD_DJIMAGESLIDER_PLAY'); ?>"<?php echo $wcag; ?> />
			    <img id="pause<?php echo $mid; ?>" class="pause-button <?php echo $show->btn==1 ? 'showOnHover':'' ?>" src="<?php echo $navigation->pause; ?>" alt="<?php echo JText::_('MOD_DJIMAGESLIDER_PAUSE'); ?>"<?php echo $wcag; ?> />
			 <?php } ?>
      </div>
    <?php } ?>
    <?php if($show->idx) { ?>
		  <div id="cust-navigation<?php echo $mid; ?>" class="<?php echo $params->get('idx_style', 0) ? 'navigation-numbers' : 'navigation-container-custom' ?> <?php echo $show->idx==2 ? 'showOnHover':'' ?>">
			  <?php $i = 0; foreach ($slides as $slide) { ?>
          <span class="load-button<?php if ($i == 0) echo ' load-button-active'; ?>"<?php echo $wcag; ?>><?php if($params->get('idx_style')) echo ($i+1) ?></span>
        <?php $i++; } ?>
      </div>
    <?php } ?>
  </div>
</div>