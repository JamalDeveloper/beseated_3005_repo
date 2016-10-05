<header class="home-header-slider">
  <jdoc:include type="modules" name="banner" style="none" />
</header>

<section class="home-our-mission">
  <span class="v-line top with-bullets"></span>
  <div class="container">
    <header>
      <h1 class="heading-1">Luxury Bookings <span>for the Elite</span></h1>
      <h2 class="section-title">Our mission</h2>
    </header>
    <div class="row">
      <div class="col-md-4">
        <div class="bordered-box">
          <span class="box-number">1</span>
          <p class="copy">We redefine the way the Elite do bookings. An online system that flawlessly performs the Reservation process.</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="bordered-box">
          <span class="box-number">2</span>
          <p class="copy">We allow direct requests by the Elite at their preferred Venues and simultaneously ascertain available Luxuries.</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="bordered-box">
          <span class="box-number">3</span>
          <p class="copy">We eliminate the middleman and help our Elite get reservations desired in unmatched time.</p>
        </div>
      </div>
    </div>      
  </div>
  <span class="v-line bottom with-bullets"></span>
</section>

<section class="home-beseated-app">
  <div class="container">    
    <jdoc:include type="modules" name="mobileapp-slider" style="none" />    
  </div>
</section>

<section class="home-venues">
  <span class="v-line top with-bullets"></span>
  <div class="container">
    <h2 class="section-title">What we offer</h2>
    <div class="row">
      <div class="col-md-6">
        <jdoc:include type="modules" name="venues-slider" style="none" />
      </div>
      <div class="col-md-6">
        <div class="box">
          <h4 class="heading-1">Choose the place <span>We'll find you space</span></h4>
          <p class="copy">Forget about phone reservations or confirmation calls. Our system connects directly with venues, showing your booking status in real-time.</p>
          <a href="#" class="button">More info</a>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="home-private-jets">
  <div class="container">
    <div class="row">
      <div class="col-md-8">
        <div class="box box-primary">
          <h4 class="heading-1">Choose your location <span>Fly any destination</span></h4>
          <p class="copy">Request a flight and our agents will contact you to give you the best options available.</p>        
        </div>
        <div class="box box-secondary">
          <h3 class="heading-3">Private Jets</h3>
          <p class="copy">Consectetur adippisicing elit sed do eiusmod tempor incididunt ut labore.</p>
          <a href="index.php?option=com_beseated&view=jetservicebooking&Itemid=<?php echo ((!$user->guest) ? '237' : '238'); ?>" class="button">Contact now</a>
        </div>
        <img class="image-1" src="<?php echo 'templates/' . $this->template . '/images/jets-1.jpg' ?>" alt="">
        <img class="image-2" src="<?php echo 'templates/' . $this->template . '/images/jets-2.jpg' ?>" alt="">
      </div>
    </div>
  </div>
</section>

<section class="home-yachts">
  <div class="container">
    <div class="row">
      <div class="col-md-8 col-md-offset-4">
        <div class="box">
          <h3 class="heading-3">Yachts</h3>
          <h4 class="heading-1">Be<em>sea</em>ted <span>Choose from a yacht fleet</span></h4>
          <p class="copy">Our numerous partners deliver a wide variety of yachts.</p>
          <a href="index.php?option=com_beseated&view=yachts&Itemid=<?php echo ((!$user->guest) ? '243' : '246'); ?>" class="button">Our partners</a>
        </div>
        <img class="image" src="<?php echo 'templates/' . $this->template . '/images/yachts.jpg' ?>" alt="">
      </div>
    </div>
  </div>
</section>

<section class="home-chauffeurs">
  <div class="container">
    <div class="row">
      <div class="col-md-8 col-sm-12">
        <div class="box">
          <h3 class="heading-3">Chauffeurs</h3>
          <h4 class="heading-1">Choose when and where <span>We'll take you there</span></h4>
          <p class="copy">Request ride and pay, with couple of taps away.</p>
          <?php 

          ?>
          <a href="index.php?option=com_beseated&view=chauffeurs&Itemid=<?php echo ((!$user->guest) ? '241' : '244'); ?>" class="button">Our Chauffeurs</a>
        </div>
        <img class="image" src="<?php echo 'templates/' . $this->template . '/images/chauffeurs.jpg' ?>" alt="">
      </div>
    </div>
  </div>
</section>

<section class="home-bodyguards">
  <div class="container">
    <div class="row">
      <div class="col-md-8 col-md-offset-2">
        <div class="box">
          <div class="row">
            <div class="col-sm-6">
              <h3 class="heading-3">Bodyguards</h3>
              <h4 class="heading-1">Be <span>Protected</span></h4>
            </div>
            <div class="col-sm-6">
              <p class="copy">Direct contact with the most reputable and experienced companies.</p>
              <a href="index.php?option=com_beseated&view=protections&Itemid=<?php echo ((!$user->guest) ? '242' : '245'); ?>" class="button">Our Bodyguards</a>
            </div>
          </div>
        </div>
        <img class="image" src="<?php echo 'templates/' . $this->template . '/images/beprotected.jpg' ?>" alt="">
      </div>
    </div>
  </div>
</section>

<section class="home-events">
  <div class="container">
    <div class="row">
      <div class="col-md-6">
        <div class="box">
          <h3 class="heading-3">Events</h3>
          <h4 class="heading-1">Choose desired event <span>We'll have your ticket sent</span></h4>
          <p class="copy">A phone in hand with a barcode to be scanned</p>
          <a href="index.php?option=com_beseated&view=events&Itemid=<?php echo ((!$user->guest) ? '179' : '152'); ?>" class="button">View calendar</a>
        </div>
      </div>
      <div class="col-md-6">
        <jdoc:include type="modules" name="events-slider" style="none" />
      </div>
    </div>
  </div>
  <span class="v-line bottom with-bullets"></span>
</section>

<section class="home-concierge">
  <span class="v-line with-bullets top"></span>
  <div class="container">
    <div class="row">
      <div class="col-md-6 col-md-offset-3">
        <h3 class="heading-3">Concierge</h3>
        <img src="<?php echo 'templates/' . $this->template . '/images/money-cash-icon.png' ?>" alt="" class="icon">
        <h4 class="heading-1">Lifestyle Managers <span>Convenient call for all</span></h4>
        <div class="bordered-box">
          <p class="copy">Dedicated around the clock, make bookings through our concierge call and pay securely on the phone... no fee, its free.</p>
        </div>
      </div>
    </div>
  </div>
</section>