<?php
/* Template Name: Tracking page */
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

if (!isset($_GET['tracking']) || empty('tracking')) {
    wp_redirect(home_url());
    exit();
}
$trackingHash = $_GET['tracking'];
$booking_id = encrypt_decrypt($trackingHash, 'decrypt');
if (!$booking_id) {
    wp_redirect(home_url());
    exit();
}
$booking = MPHB()->getBookingRepository()->findById( $booking_id, true );
if (!$booking) {
    wp_redirect(home_url());
    exit();
}
?>


<?php get_header(); ?>
    <div id="primary" class="content-area">
        <main id="main" class="site-main ffff" role="main">
            <article>
                <div class="entry-content" style="text-align: center;">
                    <?php switch ($booking->getStatus()) {
                        case 'confirmed':
                            ?>
							
                            <div class="vce-row-container">
   <div class="vce-row vce-row--col-gap-30 vce-row-content--top" data-vce-full-width="true" data-vce-stretch-content="true" id="el-c5ee1d9d" data-vce-do-apply="all el-c5ee1d9d" style="width: 1423px; left: -202.297px;">
      <div class="vce-row-content" data-vce-element-content="true">
         <div class="vce-col vce-col--md-auto vce-col--xs-1 vce-col--xs-last vce-col--xs-first vce-col--sm-last vce-col--sm-first vce-col--md-last vce-col--lg-last vce-col--xl-last vce-col--md-first vce-col--lg-first vce-col--xl-first" id="el-70a93a24">
            <div class="vce-col-inner" data-vce-do-apply="border margin background  el-70a93a24">
               <div class="vce-col-content" data-vce-element-content="true" data-vce-do-apply="padding el-70a93a24">
                  <div class="vce-single-image-container vce-single-image--align-center">
                     <div class="vce vce-single-image-wrapper" id="el-ca90cb33" data-vce-do-apply="all el-ca90cb33">
                        <figure>
                           <div class="vce-single-image-inner vce-single-image--absolute">
                              <img loading="lazy" class="vce-single-image vcv-lozad" data-src="https://arienzobeachclub.innova.menu/wp-content/uploads/2020/06/arienzo_logo.png" width="100" height="100" src="https://arienzobeachclub.innova.menu/wp-content/uploads/2020/06/arienzo_logo.png" data-img-src="https://arienzobeachclub.innova.menu/wp-content/uploads/2020/06/arienzo_logo.png" alt="" title="arienzo_logo" data-loaded="true">
                              <noscript>
                                 <img loading="lazy" class="vce-single-image" src="https://arienzobeachclub.innova.menu/wp-content/uploads/2020/06/arienzo_logo.png" width="100" height="100" alt="" title="arienzo_logo" />
                              </noscript>
                           </div>
                           <figcaption hidden=""></figcaption>
                        </figure>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<h3 class="vce-google-fonts-heading-inner" style="font-size: 60px; font-weight: 400;">Arienzo Beach Club</h3>
<div class="vce-row-container" style="    padding-left: 30px;
    padding-right: 30px;">
   <div class="vce-row vce-row--col-gap-30 vce-row-content--top" data-vce-full-width="true" data-vce-stretch-content="true" id="el-f835dc64" data-vce-do-apply="all el-f835dc64" >
      <div class="vce-row-content" data-vce-element-content="true" style="display:flex;">
         <div class="vce-col vce-col--md-auto vce-col--xs-1 vce-col--xs-last vce-col--xs-first vce-col--sm-last vce-col--sm-first vce-col--md-first vce-col--lg-first vce-col--xl-first" id="el-b9500402" style="    margin-right: 30px;">
            <div class="vce-col-inner" data-vce-do-apply="border margin background  el-b9500402">
               <div class="vce-col-content" data-vce-element-content="true" data-vce-do-apply="padding el-b9500402">
                  <div class="vce-single-image-container vce-single-image--align-left">
                     <div class="vce vce-single-image-wrapper" id="el-99e4b533" data-vce-do-apply="all el-99e4b533">
                        <figure>
                           <div class="vce-single-image-inner vce-single-image--absolute">
                              <img loading="lazy" class="vce-single-image vcv-lozad" data-src="https://booking.arienzobeachclub.com/wp-content/uploads/2021/07/WhatsApp-Image-2020-12-21-at-11.48.38-1.jpeg" width="819" height="1024" src="https://booking.arienzobeachclub.com/wp-content/uploads/2021/07/WhatsApp-Image-2020-12-21-at-11.48.38-1.jpeg" data-img-src="https://booking.arienzobeachclub.com/wp-content/uploads/2021/07/WhatsApp-Image-2020-12-21-at-11.48.38-1.jpeg" alt="" title="107419205_1527755684076161_5740148725919922361_o" data-loaded="true">
                              <noscript>
                                 <img loading="lazy" class="vce-single-image" src="https://booking.arienzobeachclub.com/wp-content/uploads/2021/07/WhatsApp-Image-2020-12-21-at-11.48.38-1.jpeg" width="819" height="1024" alt="" title="107419205_1527755684076161_5740148725919922361_o" />
                              </noscript>
                           </div>
                           <figcaption hidden=""></figcaption>
                        </figure>
                     </div>
                  </div>
               </div>
            </div>
         </div>
         <div class="vce-col vce-col--md-auto vce-col--xs-1 vce-col--xs-last vce-col--xs-first vce-col--sm-last vce-col--sm-first vce-col--md-last vce-col--lg-last vce-col--xl-last" id="el-31df402c">
            <p> Ciao, finalmente sei all'Arienzo Beach Club! <br>
               Adesso pensa solo a rilassarti e divertirti e se vuoi dai uno sguardo al nostro menu qui:
            </p>
            <a href="https://booking.arienzobeachclub.com/menu-2021/" class="menu">Menu</a>
         </div>
      </div>
   </div>
</div>
                            <?php
                            break;
                        default:
                            ?>
                        Booking status: <?php echo $booking->getStatus();
                    } ?>
                </div><!-- .entry-content -->

            </article>
        </main><!-- .site-main -->
    </div><!-- .content-area -->
    <style>
        a.menu {
            border: none;
            color: #fff;
            text-decoration: none;
            transition: background .5s ease;
            -moz-transition: background .5s ease;
            -webkit-transition: background .5s ease;
            -o-transition: background .5s ease;
            display: inline-block;
            cursor: pointer;
            outline: 0;
            text-align: center;
            background: #2abfaa;
            position: relative;
            font-size: 14px;
            font-size: .875rem;
            font-weight: 600;
            -webkit-border-radius: 3px;
            -moz-border-radius: 3px;
            -ms-border-radius: 3px;
            border-radius: 3px;
            line-height: 1;
            font-size: 14px;
            padding: 12px 30px;
        }
    </style>
<?php get_footer(); ?>