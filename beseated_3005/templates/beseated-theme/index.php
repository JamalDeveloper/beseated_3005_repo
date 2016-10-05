<?php
defined('_JEXEC') or die;

$doc = JFactory::getDocument();
$user = JFactory::getUser();
$userToken = JSession::getFormToken();

$input = JFactory::getApplication()->input;
$Itemid = $input->get('Itemid', 0, 'int');

$doc->addStyleSheet('templates/' . $this->template . '/css/vendor.css');
$doc->addStyleSheet('templates/' . $this->template . '/css/app.css');
$doc->addScript('templates/' . $this->template . '/js/vendor.js', 'text/javascript');
$doc->addScript('templates/' . $this->template . '/js/app.js', 'text/javascript');
?>
<!DOCTYPE html>
<html>
<head>
  <jdoc:include type="head" />
  <script src="http://maps.google.com/maps/api/js?libraries=places"></script>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
  <nav>
    <div class="container">
      <a href="/"><h1 class="logo">Beseated</h1></a>
      <div class="topmenu">
        <div id="nav-icon">
          <span></span>
          <span></span>
          <span></span>
          <span></span>
        </div>
        <jdoc:include type="modules" name="position-1" style="none" />
      </div>
      <jdoc:include type="modules" name="search" style="none" />
    </div>
  </nav>

  <?php if ($this->countModules( 'banner' )) { 
          include_once('templates/'.$this->template.'/partials/home.php');
       } else { ?>
          <jdoc:include type="message" />
          <jdoc:include type="component" />            
  <?php }; ?>

  <footer>
    <?php if ($this->countModules( 'before-footer' )) : ?>
      <div class="before-footer">
        <jdoc:include type="modules" name="before-footer" style="none" />
      </div>
    <?php endif; ?>
    <div class="main-footer">
      <div class="container">
        <div class="row">
          <div class="col-md-2 col-sm-3 col-xs-4">
            <jdoc:include type="modules" name="footer-beseated" style="xhtml" />
          </div>
          <div class="col-md-2 col-sm-3 col-xs-4">
            <jdoc:include type="modules" name="footer-overview" style="xhtml" />
          </div>
          <div class="col-md-2 col-sm-3 col-xs-4">
            <jdoc:include type="modules" name="footer-partner" style="xhtml" />
          </div>
          <div class="col-md-2 col-sm-3 col-xs-12 footer-social-icons">
            <jdoc:include type="modules" name="footer-social" style="none" />
          </div>
          <div class="col-md-3 col-sm-12 footer-newsletter">
            <jdoc:include type="modules" name="footer-newsletter" style="xhtml" />
          </div>
        </div>
        <div class="row">
          <div class="col-md-12 footer-bottom">
            <div class="copyright">
              <p>&copy; <?php echo date("Y"); ?> Beseated. All Rights Reserved</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </footer>    
</body>
</html>
