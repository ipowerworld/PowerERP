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
<title>index</title>
<meta charset="utf-8">
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<meta name="robots" content="index, follow" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="keywords" content="" />
<meta name="title" content="index" />
<meta name="description" content="" />
<meta name="generator" content="PowerERP 17.0.0-beta (https://www.powererp.org)" />
<meta name="powererp:pageid" content="250" />
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
<body id="bodywebsite" class="bodywebsite bodywebpage-index">
<!-- Enter here your HTML content. Add a section with an id tag and tag contenteditable="true" if you want to use the inline editor for the content  -->

<?php includeContainer('header'); ?>


<section id="mysection1" contenteditable="true">
        <main>
            <section class="hero">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-5 col-12 m-auto">
                            <div class="heroText">
                                <h1 class="text-white mb-lg-5 mb-3">
                                    Delicious Steaks
                                </h1>

                                <div class="c-reviews my-3 d-flex flex-wrap align-items-center">
                                    <div
                                        class="d-flex flex-wrap align-items-center">
                                        <div class="reviews-stars">
                                            <i
                                                class="bi-star-fill reviews-icon"
                                            ></i>
                                            <i
                                                class="bi-star-fill reviews-icon"
                                            ></i>
                                            <i
                                                class="bi-star-fill reviews-icon"
                                            ></i>
                                            <i
                                                class="bi-star-fill reviews-icon"
                                            ></i>
                                            <i class="bi-star reviews-icon"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-7 col-12">
                            <div
                                id="carouselExampleCaptions"
                                class="carousel carousel-fade hero-carousel slide"
                                data-bs-ride="carousel"
                            >
                                <div class="carousel-inner">
                                    <div class="carousel-item active">
                                        <div class="carousel-image-wrap">
                                            <img
                                                src="image/aaab/slide/jay-wennington-N_Y88TWmGwA-unsplash.jpg"
                                                class="img-fluid carousel-image"
                                                alt=""
                                            />
                                        </div>

                                        <div class="carousel-caption">
                                            <h4 class="hero-text">
                                                Fine Dining Restaurant
                                            </h4>
                                        </div>
                                    </div>

                                    <div class="carousel-item">
                                        <div class="carousel-image-wrap">
                                            <img
                                                src="image/aaab/slide/jason-leung-O67LZfeyYBk-unsplash.jpg"
                                                class="img-fluid carousel-image"
                                                alt=""
                                            />
                                        </div>

                                        <div class="carousel-caption">
                                            <div
                                                class="d-flex align-items-center"
                                            >
                                                <h4 class="hero-text">Steak</h4>

                                                <span class="price-tag ms-4"
                                                    >26.50<small>€</small></span
                                                >
                                            </div>
                                        </div>
                                    </div>

                                    <div class="carousel-item">
                                        <div class="carousel-image-wrap">
                                            <img
                                                src="image/aaab/slide/ivan-torres-MQUqbmszGGM-unsplash.jpg"
                                                class="img-fluid carousel-image"
                                                alt=""
                                            />
                                        </div>

                                        <div class="carousel-caption">
                                            <div
                                                class="d-flex align-items-center"
                                            >
                                                <h4 class="hero-text">
                                                    Sausage Pasta
                                                </h4>

                                                <span class="price-tag ms-4"
                                                    >18.25<small>€</small></span
                                                >
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <button
                                    class="carousel-control-prev"
                                    type="button"
                                    data-bs-target="#carouselExampleCaptions"
                                    data-bs-slide="prev"
                                >
                                    <span
                                        class="carousel-control-prev-icon"
                                        aria-hidden="true"
                                    ></span>
                                    <span class="visually-hidden"
                                        >Previous</span
                                    >
                                </button>

                                <button
                                    class="carousel-control-next"
                                    type="button"
                                    data-bs-target="#carouselExampleCaptions"
                                    data-bs-slide="next"
                                >
                                    <span
                                        class="carousel-control-next-icon"
                                        aria-hidden="true"
                                    ></span>
                                    <span class="visually-hidden">Next</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overlay"></div>
            </section>

            <section class="menu section-padding">
                <div class="container">
                    <div class="row">
                        <div class="col-12">
                            <h2 class="text-center mb-lg-5 mb-4">
                                Special Menus
                            </h2>
                        </div>

                        <div class="col-lg-4 col-md-6 col-12">
                            <div class="menu-thumb">
                                <div class="menu-image-wrap">
                                    <img
                                        src="image/aaab/breakfast/brett-jordan-8xt8-HIFqc8-unsplash.jpg"
                                        class="img-fluid menu-image"
                                        alt=""
                                    />

                                    <span class="menu-tag bg-warning"
                                        >Breakfast</span
                                    >
                                </div>

                                <div
                                    class="menu-info d-flex flex-wrap align-items-center"
                                >
                                    <h4 class="mb-0">Morning Fresh</h4>

                                    <span
                                        class="price-tag bg-white shadow-lg ms-4"
                                        >12.50<small>€</small></span
                                    >

                                    <div
                                        class="d-flex flex-wrap align-items-center w-100 mt-2"
                                    >
                                        <div class="reviews-stars">
                                            <i
                                                class="bi-star-fill reviews-icon"
                                            ></i>
                                            <i
                                                class="bi-star-fill reviews-icon"
                                            ></i>
                                            <i
                                                class="bi-star-fill reviews-icon"
                                            ></i>
                                            <i
                                                class="bi-star-fill reviews-icon"
                                            ></i>
                                            <i class="bi-star reviews-icon"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-6 col-12">
                            <div class="menu-thumb">
                                <div class="menu-image-wrap">
                                    <img
                                        src="image/aaab/lunch/farhad-ibrahimzade-MGKqxm6u2bc-unsplash.jpg"
                                        class="img-fluid menu-image"
                                        alt=""
                                    />

                                    <span class="menu-tag bg-warning"
                                        >Lunch</span
                                    >
                                </div>

                                <div
                                    class="menu-info d-flex flex-wrap align-items-center"
                                >
                                    <h4 class="mb-0">DoliCloud Soup</h4>

                                    <span
                                        class="price-tag bg-white shadow-lg ms-4"
                                        >24.50<small>€</small></span
                                    >

                                    <div
                                        class="d-flex flex-wrap align-items-center w-100 mt-2"
                                    >
                                        <div class="reviews-stars">
                                            <i
                                                class="bi-star-fill reviews-icon"
                                            ></i>
                                            <i
                                                class="bi-star-fill reviews-icon"
                                            ></i>
                                            <i
                                                class="bi-star-fill reviews-icon"
                                            ></i>
                                            <i class="bi-star reviews-icon"></i>
                                            <i class="bi-star reviews-icon"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-6 col-12">
                            <div class="menu-thumb">
                                <div class="menu-image-wrap">
                                    <img
                                        src="image/aaab/dinner/keriliwi-c3mFafsFz2w-unsplash.jpg"
                                        class="img-fluid menu-image"
                                        alt=""
                                    />

                                    <span class="menu-tag bg-warning"
                                        >Dinner</span
                                    >
                                </div>

                                <div
                                    class="menu-info d-flex flex-wrap align-items-center"
                                >
                                    <h4 class="mb-0">Premium Steak</h4>

                                    <span
                                        class="price-tag bg-white shadow-lg ms-4"
                                        >45<small>€</small></span
                                    >

                                    <div
                                        class="d-flex flex-wrap align-items-center w-100 mt-2"
                                    >
                                        <div class="reviews-stars">
                                            <i
                                                class="bi-star-fill reviews-icon"
                                            ></i>
                                            <i
                                                class="bi-star-fill reviews-icon"
                                            ></i>
                                            <i
                                                class="bi-star-fill reviews-icon"
                                            ></i>
                                            <i class="bi-star reviews-icon"></i>
                                            <i class="bi-star reviews-icon"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-6 col-12">
                            <div class="menu-thumb">
                                <div class="menu-image-wrap">
                                    <img
                                        src="image/aaab/dinner/farhad-ibrahimzade-ZipYER3NLhY-unsplash.jpg"
                                        class="img-fluid menu-image"
                                        alt=""
                                    />

                                    <span class="menu-tag bg-warning"
                                        >Dinner</span
                                    >
                                </div>

                                <div
                                    class="menu-info d-flex flex-wrap align-items-center"
                                >
                                    <h4 class="mb-0">Seafood Set</h4>

                                    <span
                                        class="price-tag bg-white shadow-lg ms-4"
                                        >86<small>€</small></span
                                    >

                                    <div
                                        class="d-flex flex-wrap align-items-center w-100 mt-2"
                                    >
                                        <div class="reviews-stars">
                                            <i
                                                class="bi-star-fill reviews-icon"
                                            ></i>
                                            <i
                                                class="bi-star-fill reviews-icon"
                                            ></i>
                                            <i
                                                class="bi-star-fill reviews-icon"
                                            ></i>
                                            <i class="bi-star reviews-icon"></i>
                                            <i class="bi-star reviews-icon"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-6 col-12">
                            <div class="menu-thumb">
                                <div class="menu-image-wrap">
                                    <img
                                        src="image/aaab/breakfast/louis-hansel-dphM2U1xq0U-unsplash.jpg"
                                        class="img-fluid menu-image"
                                        alt=""
                                    />

                                    <span class="menu-tag bg-warning"
                                        >Breakfast</span
                                    >
                                </div>

                                <div
                                    class="menu-info d-flex flex-wrap align-items-center"
                                >
                                    <h4 class="mb-0">Burger Set</h4>

                                    <span
                                        class="price-tag bg-white shadow-lg ms-4"
                                        >20.50<small>€</small></span
                                    >

                                    <div
                                        class="d-flex flex-wrap align-items-center w-100 mt-2"
                                    >
                                        <div class="reviews-stars">
                                            <i
                                                class="bi-star-fill reviews-icon"
                                            ></i>
                                            <i
                                                class="bi-star-fill reviews-icon"
                                            ></i>
                                            <i
                                                class="bi-star-fill reviews-icon"
                                            ></i>
                                            <i
                                                class="bi-star-fill reviews-icon"
                                            ></i>
                                            <i class="bi-star reviews-icon"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-6 col-12">
                            <div class="menu-thumb">
                                <div class="menu-image-wrap">
                                    <img
                                        src="image/aaab/lunch/farhad-ibrahimzade-D5c9ZciQy_I-unsplash.jpg"
                                        class="img-fluid menu-image"
                                        alt=""
                                    />

                                    <span class="menu-tag bg-warning"
                                        >Lunch</span
                                    >
                                </div>

                                <div
                                    class="menu-info d-flex flex-wrap align-items-center"
                                >
                                    <h4 class="mb-0">Healthy Soup</h4>

                                    <span
                                        class="price-tag bg-white shadow-lg ms-4"
                                        >34.20<small>€</small></span
                                    >

                                    <div
                                        class="d-flex flex-wrap align-items-center w-100 mt-2"
                                    >
                                        <div class="reviews-stars">
                                            <i
                                                class="bi-star-fill reviews-icon"
                                            ></i>
                                            <i
                                                class="bi-star-fill reviews-icon"
                                            ></i>
                                            <i
                                                class="bi-star-fill reviews-icon"
                                            ></i>
                                            <i class="bi-star reviews-icon"></i>
                                            <i class="bi-star reviews-icon"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>

</section>


<?php includeContainer('footer'); ?>

</body>
</html>
<?php // BEGIN PHP
$tmp = ob_get_contents(); ob_end_clean(); dolWebsiteOutput($tmp, "html", 250);
// END PHP ?>
