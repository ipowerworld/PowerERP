<?php // BEGIN PHP
$websitekey=basename(__DIR__); if (empty($websitepagefile)) $websitepagefile=__FILE__;
if (! defined('USEPOWERERPSERVER') && ! defined('USEPOWERERPEDITOR')) {
	$pathdepth = count(explode('/', $_SERVER['SCRIPT_NAME'])) - 2;
	require_once ($pathdepth ? str_repeat('../', $pathdepth) : './').'master.inc.php';
} // Not already loaded
require_once DOL_DOCUMENT_ROOT.'/core/lib/website.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/website.inc.php';
ob_start();
// END PHP ?>
<html lang="en">
<head>
<title>FAQ</title>
<meta charset="utf-8">
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<meta name="robots" content="index, follow" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="keywords" content="faq" />
<meta name="title" content="FAQ" />
<meta name="description" content="Frequently Asked Questions" />
<meta name="generator" content="PowerERP 17.0.0-alpha (https://www.powererp.org)" />
<meta name="powererp:pageid" content="186" />
<?php if ($website->use_manifest) { print '<link rel="manifest" href="/manifest.json.php" />'."\n"; } ?>
<!-- Include link to CSS file -->
<link rel="stylesheet" href="/styles.css.php?website=<?php echo $websitekey; ?>" type="text/css" />
<!-- Include link to JS file -->
<script async src="/javascript.js.php"></script>
<!-- Include HTML header from common file -->
<?php if (file_exists(DOL_DATA_ROOT."/website/".$websitekey."/htmlheader.html")) include DOL_DATA_ROOT."/website/".$websitekey."/htmlheader.html"; ?>
<!-- Include HTML header from page header block -->

</head>
<!-- File generated by PowerERP website module editor -->
<body id="bodywebsite" class="bodywebsite bodywebpage-faq">
<div class="page">

    <?php includeContainer('header'); ?>

      <section id="sectionimage" contenteditable="true">
        <div class="">
          <div class="swiper-wrapper text-center" style="transform: translate3d(0px, 0px, 0px); transition-duration: 0ms;">
            <div class="swiper-slide swiper-slide-active" style="height: 200px; background-image: url('medias/image/template-corporate/background_sunset.webp'); background-size: cover;">
              <div class="swiper-slide-caption">
                <div class="container">
                  <div class="row justify-content-sm-center">
                    <div class="col-md-11 col-lg-10">
                      <div class="text-white text-uppercase jumbotron-custom border-modern fadeInUp animated" data-caption-animate="fadeInUp" data-caption-delay="0s">FAQs</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
        </div>
      </section>


        <section id="sectionfooterpowererp" contenteditable="true" class="footerpowererp">
            <div class="container">
<div class="text-align: center"><br><br><h2>Frequently Asked Questions</h2></div>
<br>
<br>
<br>
<h4>How can I contact you ?</h4><br>
You can contact us by using <a href="contact.php">this page</a>.
<br>
<br>
<br>
<h4>What is your privacy policy ?</h4><br>
You may find information about our privacy policy on <a href="privacy-policies.php">this page</a>.


<br><br><br><br>

            </div>
        </section>


    <br><br>

    <?php includeContainer('footer'); ?>

</div>
    


</body>
</html>
<?php // BEGIN PHP
$tmp = ob_get_contents(); ob_end_clean(); dolWebsiteOutput($tmp, "html", 186);
// END PHP ?>
