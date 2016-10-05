<?php
/**
 * @package     Joomla.Site
 * @subpackage  Templates.bcted
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;



$view = JFactory::getApplication()->input->get('view','','string');
$option = JFactory::getApplication()->input->get('option','','string');
$Itemid = JFactory::getApplication()->input->get('Itemid',0,'int');

$app             = JFactory::getApplication();
$doc             = JFactory::getDocument();
$user            = JFactory::getUser();
$this->language  = $doc->language;
$this->direction = $doc->direction;

//$app = JFactory::getApplication();


// Getting params from template
$params = $app->getTemplate(true)->params;

// Detecting Active Variables
$option   = $app->input->getCmd('option', '');
$view     = $app->input->getCmd('view', '');
$layout   = $app->input->getCmd('layout', '');
$task     = $app->input->getCmd('task', '');
$itemid   = $app->input->getCmd('Itemid', '');
$sitename = $app->get('sitename');

if($task == "edit" || $layout == "form" )
{
	$fullWidth = 1;
}
else
{
	$fullWidth = 0;
}

// Add JavaScript Frameworks
JHtml::_('bootstrap.framework');
$doc->addScript('templates/' . $this->template . '/js/template.js');

// Add Stylesheets
$doc->addStyleSheet('templates/' . $this->template . '/css/template.css');

// Load optional RTL Bootstrap CSS
JHtml::_('bootstrap.loadCss', false, $this->direction);

// Adjusting content width
if ($this->countModules('position-7') && $this->countModules('position-8'))
{
	$span = "span5";
}
elseif ($this->countModules('position-7') && !$this->countModules('position-8'))
{
	$span = "span8";
}
elseif (!$this->countModules('position-7') && $this->countModules('position-8'))
{
	$span = "span8";
}
else
{
	$span = "span12";
}

// Logo file or site title param
if ($this->params->get('logoFile'))
{
	$logo = '<img src="' . JUri::root() . $this->params->get('logoFile') . '" alt="' . $sitename . '" />';
}
elseif ($this->params->get('sitetitle'))
{
	$logo = '<span class="site-title" title="' . $sitename . '">' . htmlspecialchars($this->params->get('sitetitle')) . '</span>';
}
else
{
	$logo = '<span class="site-title" title="' . $sitename . '">' . $sitename . '</span>';
}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="IE=8" >
	<jdoc:include type="head" />
	<?php // Use of Google Font ?>
	<?php if ($this->params->get('googleFont')) : ?>
		<link href='//fonts.googleapis.com/css?family=<?php echo $this->params->get('googleFontName'); ?>' rel='stylesheet' type='text/css' />
   <link href='<?php echo $this->baseurl; ?>/templates/bcted/css/custom.css' rel='stylesheet' type='text/css' />   <link href='<?php echo $this->baseurl; ?>/templates/bcted/css/service.ddlist.jquery.css' rel='stylesheet' type='text/css' />
   <link href='<?php echo $this->baseurl; ?>/templates/bcted/css/animate.css' rel='stylesheet' type='text/css'/>

   <!--<link href='<?php echo $this->baseurl; ?>/templates/bcted/css/BeatPicker.min.css' rel='stylesheet' type='text/css' />-->
   <!--<link href='<?php echo $this->baseurl; ?>/templates/bcted/css/rangeslider.css' rel='stylesheet' type='text/css' />-->
    <!--[if IE 8]>
		<link href='<?php echo $this->baseurl; ?>/templates/bcted/css/ie8.css' rel='stylesheet' type='text/css'/>
	<![endif]-->
		<style type="text/css">
			h1,h2,h3,h4,h5,h6,.site-title{
				font-family: '<?php echo str_replace('+', ' ', $this->params->get('googleFontName')); ?>', sans-serif;
			}
		</style>
	<?php endif; ?>
	<?php // Template color ?>
	<?php if ($this->params->get('templateColor')) : ?>
	<style type="text/css">
		body.site
		{
			border-top: 3px solid <?php echo $this->params->get('templateColor'); ?>;
			background-color: <?php echo $this->params->get('templateBackgroundColor'); ?>
		}
		a
		{
			color: <?php echo $this->params->get('templateColor'); ?>;
		}
		.navbar-inner, .nav-list > .active > a, .nav-list > .active > a:hover, .dropdown-menu li > a:hover, .dropdown-menu .active > a, .dropdown-menu .active > a:hover, .nav-pills > .active > a, .nav-pills > .active > a:hover,
		.btn-primary
		{
			background: <?php echo $this->params->get('templateColor'); ?>;
		}
		.navbar-inner
		{
			-moz-box-shadow: 0 1px 3px rgba(0, 0, 0, .25), inset 0 -1px 0 rgba(0, 0, 0, .1), inset 0 30px 10px rgba(0, 0, 0, .2);
			-webkit-box-shadow: 0 1px 3px rgba(0, 0, 0, .25), inset 0 -1px 0 rgba(0, 0, 0, .1), inset 0 30px 10px rgba(0, 0, 0, .2);
			box-shadow: 0 1px 3px rgba(0, 0, 0, .25), inset 0 -1px 0 rgba(0, 0, 0, .1), inset 0 30px 10px rgba(0, 0, 0, .2);
		}
	</style>
	<?php endif; ?>
	<!--[if lt IE 9]>
		<script src="<?php echo $this->baseurl; ?>/media/jui/js/html5.js"></script>
	<![endif]-->
		<?php $menu = $app->getMenu();
		if ($menu->getActive() == $menu->getDefault()): ?>
			<script src="<?php echo $this->baseurl; ?>/templates/bcted/js/service.ddlist.jquery.js"></script>
		<?php endif; ?>

        <script src="<?php echo $this->baseurl; ?>/templates/bcted/js/modernizr.js"></script>
        <!-- <script src="<?php echo $this->baseurl; ?>/templates/bcted/js/fusionad.js"></script>-->
        <script src="<?php echo $this->baseurl; ?>/templates/bcted/js/wow.min.js"></script>
        <!--<script src="<?php echo $this->baseurl; ?>/templates/bcted/js/BeatPicker.min.js"></script>-->
		<!--<script src="<?php echo $this->baseurl; ?>/templates/bcted/js/jquery.min.js"></script>-->
		<!-- <script src="<?php echo $this->baseurl; ?>/templates/bcted/js/rangeslider.min.js"></script> -->
<!--<script>
jQuery('input[type="range"]').rangeslider({
		polyfill: true,
		// Default CSS classes
		rangeClass: 'rangeslider',
		fillClass: 'rangeslider__fill',
		handleClass: 'rangeslider__handle',
		// Callback function
		onInit: function() {},
		// Callback function
		onSlide: function(position, value) {},
		// Callback function
		onSlideEnd: function(position, value) {}
	});
</script>-->
<script type="text/javascript">
	// Execute on page load
	<?php $menu = $app->getMenu();
		if ($menu->getActive() == $menu->getDefault()): ?>
		  jQuery(function () {
		    // Initialize dropdown lists, checkboxes, ..
		    jQuery('#provincesList1').ddlist({
		      width: 270,
		      selectionIndex: 1,
		      onSelected: function (index, value, text) {
		        // Show selected province in status panel
		        jQuery('#provinceSelect1').text(text + ' (value: ' + value + ')');
		      }
		    });
		  });
		<?php endif; ?>
  function myFunc(){
				var hgt = window.innerHeight;
				var headerHeight = jQuery('.header').css('height');
				var footerHeight = jQuery('.footer').css('height');
				nhgt =  hgt - parseInt(footerHeight) ;
				fullhgt =  hgt - parseInt(headerHeight) - parseInt(footerHeight) ;

				jQuery(".body").css({"min-height":nhgt+'px'});
				jQuery(".background-wrapper").css({"min-height":fullhgt+'px'});

			}
		window.onload = myFunc;
</script>
<script>
jQuery(document).ready(function() {
    jQuery(".filter-result .venue_blck:nth-child(even)").addClass("even");
    jQuery(".filter-result .venue_blck:nth-child(odd)").addClass("odd");
});
</script>

<script>
    wow = new WOW(
      {
	  boxClass:     'wow',      // default
      animateClass: 'animated', // default
      offset:       0,          // default
      mobile:       true,       // default
      live:         true
       // animateClass: 'animated',
      //  offset:       100,
      //  callback:     function(box) {
       //   console.log("WOW: animating <" + box.tagName.toLowerCase() + ">")
       // }
      }
    );
    wow.init();
  </script>
</head>
<?php //echo $view; ?>
<body class="site <?php echo $option
	. ' view-' . $view
	. ($layout ? ' layout-' . $layout : ' no-layout')
	. ($task ? ' task-' . $task : ' no-task')
	. ($itemid ? ' itemid-' . $itemid : '')
	. ($params->get('fluidContainer') ? ' fluid' : '');
?>">

	<!-- Body -->
	<div class="body">
		<div class="container<?php echo ($params->get('fluidContainer') ? '-fluid' : ''); ?>">
			<!-- Header -->
			<header class="header" role="banner">
				<div class="header-inner clearfix" id="header-wrapper">
					<a class="brand pull-left" href="<?php echo $this->baseurl; ?>">
						<?php echo $logo; ?>
						<?php if ($this->params->get('sitedescription')) : ?>
							<?php echo '<div class="site-description">' . htmlspecialchars($this->params->get('sitedescription')) . '</div>'; ?>
						<?php endif; ?>
					</a>
					<div class="menuber">
						<jdoc:include type="modules" name="position-0" style="none" />
                        <?php if ($this->countModules('position-1')) : ?>
                            <nav class="navigation" role="navigation">
                                <jdoc:include type="modules" name="position-1" style="none" />
                            </nav>
                        <?php endif; ?>
					</div>
				</div>
			</header>
            	<div class="banner_wrp">
					<jdoc:include type="modules" name="banner" style="xhtml" />
                    <jdoc:include type="modules" name="position-9" style="xhtml" />
                </div>
                <?php if(trim($view) == 'registration' || trim($view) == 'login' || trim($view) == 'userprofile' || trim($view) == 'reset' || trim($view) == 'remind'): ?>
					<div class="background-wrapper">
				<?php endif; ?>
                <div class="<?php if(JRequest::getVar('view') == 'featured'){ echo '';} else { echo "wrapper"; }  ?> ">


			<div class="row-fluid">

				<?php if ($this->countModules('position-8')) : ?>
					<!-- Begin Sidebar -->
					<div id="sidebar" class="span4 leftmenu-wrp">
						<div class="sidebar-nav">
                            <jdoc:include type="modules" name="position-8" style="xhtml" />
						</div>
					</div>
					<!-- End Sidebar -->
				<?php endif; ?>
				<?php $menu = $app->getMenu(); ?>
				<?php $menuItem = $menu->getItems( 'link', 'index.php?option=com_content&view=article&id=8', true ); ?>
				<?php $contactItemID = $menuItem->id; ?>
				<?php $conatctPageClass = ""; ?>
				<?php if($Itemid == $contactItemID): ?>
					<?php $span = 'span4'; ?>
					<?php $conatctPageClass = ' contact-detail'; ?>
				<?php endif; ?>
				<main id="content" role="main" class="<?php echo $span; ?>">
					<!-- Begin Content -->

                    <div class="article_wrp<?php echo $conatctPageClass; ?>" id="wrapper">
                    <jdoc:include type="modules" name="position-3" style="xhtml" />
					<jdoc:include type="message" />
					<jdoc:include type="component" />
					<jdoc:include type="modules" name="position-2" style="none" />
					<!-- End Content -->
                    </div>

					<jdoc:include type="modules" name="position-10" style="xhtml" />
                    <jdoc:include type="modules" name="position-11" style="xhtml" />
                    <jdoc:include type="modules" name="position-12" style="xhtml" />
                    <jdoc:include type="modules" name="position-13" style="xhtml" />
				</main>
				<?php if ($this->countModules('position-7')) : ?>
					<?php $menu = $app->getMenu(); ?>
					<?php $menuItem = $menu->getItems( 'link', 'index.php?option=com_content&view=article&id=8', true ); ?>
					<?php $contactItemID = $menuItem->id; ?>
					<?php if($Itemid == $contactItemID): ?>
						<div id="aside" class="span8">
					<?php else: ?>
						<div id="aside" class="span3">
					<?php endif; ?>
						<!-- Begin Right Sidebar -->
						<jdoc:include type="modules" name="position-7" style="well" />
						<!-- End Right Sidebar -->
					</div>
				<?php endif; ?>
			</div>
            </div>
            <?php if(trim($view) == 'registration' || trim($view) == 'login' || trim($view) == 'userprofile' || trim($view) == 'reset' || trim($view) == 'remind'): ?>
					</div>
				<?php endif; ?>
		</div>
	</div>
	<!-- Footer -->
	<footer class="footer" role="contentinfo">
		<div class="wrapper">
	        <div class="container<?php echo ($params->get('fluidContainer') ? '-fluid' : ''); ?>">
			<jdoc:include type="modules" name="footer" style="xhtml" />
            <jdoc:include type="modules" name="position-14" style="xhtml" />

<?php /*?>			<p class="pull-right">
				<a href="#top" id="back-top">
					<?php echo JText::_('TPL_BCTED_BACKTOTOP'); ?>
				</a>
			</p>
<?php */?>			<p>
				&copy; <?php echo date('Y'); ?> <?php echo $sitename; ?>
			</p>
		</div>
        </div>
        <jdoc:include type="modules" name="debug" style="none" />
	</footer>

</body>
</html>
<script type="text/javascript">

</script>
