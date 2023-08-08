<?php
/* Copyright (C) 2011-2019 Regis Houssin  <regis.houssin@capnetworks.com>
 * Copyright (C) 2012-2019 Philippe Grand <philippe.grand@atoo-net.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 */

/**
 *		\file       /ultimatepdf/css/ultimatepdf.css.php
 *		\brief      Fichier de style CSS complementaire du module Ultimatepdf
 */

//if (! defined('NOREQUIREUSER')) define('NOREQUIREUSER','1');	// Not disabled cause need to load personalized language
//if (! defined('NOREQUIREDB'))   define('NOREQUIREDB','1');	// Not disabled to increase speed. Language code is found on url.
if (! defined('NOREQUIRESOC'))    define('NOREQUIRESOC','1');
//if (! defined('NOREQUIRETRAN')) define('NOREQUIRETRAN','1');	// Not disabled cause need to do translations
if (! defined('NOCSRFCHECK'))     define('NOCSRFCHECK',1);
if (! defined('NOTOKENRENEWAL'))  define('NOTOKENRENEWAL',1);
if (! defined('NOLOGIN'))         define('NOLOGIN',1);
if (! defined('NOREQUIREMENU'))   define('NOREQUIREMENU',1);
if (! defined('NOREQUIREHTML'))   define('NOREQUIREHTML',1);
if (! defined('NOREQUIREAJAX'))   define('NOREQUIREAJAX','1');
if (! defined('NOREQUIREHOOK'))   define('NOREQUIREHOOK','1');  // Disable "main.inc.php" hooks

define('ISLOADEDBYSTEELSHEET', '1');

$res=0;
$res=@include '../../main.inc.php';					// For "root" directory
if (! $res && file_exists($_SERVER['DOCUMENT_ROOT']."/main.inc.php"))
	$res=@include($_SERVER['DOCUMENT_ROOT']."/main.inc.php"); // Use on dev env only
if (! $res) $res=@include '../../../main.inc.php';	// For "custom" directory
if (! $res) @include("../../../../../PowerERP/htdocs/main.inc.php");	// Used on dev env only


// Define css type
header('Content-type: text/css');
// Important: Following code is to avoid page request by browser and PHP CPU at
// each PowerERP page access.
if (empty($powererp_nocache)) header('Cache-Control: max-age=3600, public, must-revalidate');
else header('Cache-Control: no-cache');


if (! empty($_GET["lang"])) $langs->setDefaultLang($_GET["lang"]);	// If language was forced on URL by the main.inc.php
$langs->load("main",0,1);
$right=($langs->direction=='rtl'?'left':'right');
$left=($langs->direction=='rtl'?'right':'left');
?>
div.info {
  background: #8db6c8;
}
.updficon {
	background-image: url('<?php echo dol_buildpath('/ultimatepdf/img/object_ultimatepdf.png', 1) ?>');
	background-repeat: no-repeat;
}
.updficon-large {
	background-image: url('<?php echo dol_buildpath('/ultimatepdf/img/swiss.png', 1) ?>');
	background-repeat: no-repeat;
}

.padding-left20 {
	padding-left: 20px!important;
}

#design {
	width: 200px;
}
#select2-design-container {
	color: #444 !important;
}

#switchdesign {
<?php  if (GETPOSTISSET('theme') && GETPOST('theme', 'aZ', 1) !== 'eldy') { ?>
	padding-top: 3px;
<?php } elseif ($conf->global->MAIN_THEME === 'eldy' && ! empty($conf->global->ULTIMATEPDF_DROPDOWN_MENU_DISABLED)) { ?>
	padding-top: 19px;
<?php } else { ?>
	padding-top: 3px;
<?php } ?>
}

img.switchdesign {
	cursor:pointer;
	/*padding: <?php echo ($conf->browser->phone?'0':'8')?>px 0px 0px 0px;*/
	/*margin: 0px 0px 0px 8px;*/
	text-decoration: none;
	color: white;
	font-weight: bold;
}
<!-- Set Logo height -->
.ui-widget-header {
	background:#b9cd6d;
	border: 1px solid #b9cd6d;
	color: #FFFFFF;
	font-weight: bold;
}
.ui-widget-content {
	background: #cedc98;
	border: 1px solid #DDDDDD;
	color: #333333;
}
.ui-state-active {
	border: 1px solid #fbd850;
	color: #eb8f00;
	font-weight: bold;
}
.ui-icon-gripsmall-diagonal-sw {
    background-image: url('<?php echo dol_buildpath("/ultimatepdf/img/ui-icons_sw_256x240.png",1); ?>')!important;
}
.ui-resizable-sw {
    bottom: 1px;
    left: 1px;
}
#container_logo, #container_otherlogo { width: 440px; height: 220px; }
#container2, #container3, #container4, #container5, #container6, #container7, #container8, #container9, #container10, #container11, #container12 { width: 208px; height: 295px; }
#container_desc { width: 210px; height: 295px; }
#container_desc h3 { text-align: center; margin: 0; margin-bottom: 10px; }
#container_AddressesBlocks { width: 210px; height: 160px; }
#resizable_desc, #container_desc { padding: 5px;}
#container_unit { width: 210px; height: 295px; }
#container_unit h3 { text-align: center; margin: 0; margin-bottom: 10px; }
#resizable_unit, #container_unit { padding: 5px;}
#resizable-1, #resizable-3 {background-position: top left;
width: 150px; height: 150px; }
#resizable-1, #resizable-3, #container_logo, #container_otherlogo { padding: 1em !important; }
#resizable-5 {
	left: 10px;
	right: 10px;
	top : 10px;
	bottom : 10px;
	width: 190px;
	height: 277px;
}
#resizable-7 {
	background-position: top left;
	width: 30px; height: 295px;
}
#resizable-9 {
	left: 100px;
	background-position: top 100px;
	width: 30px; height: 295px;
}
#resizable-11 {
	background-position: top 150px;
	width: 208px; height: 80px;
	position: relative;
}
#resizable-11 h3 { text-align: center; margin: 0; margin-bottom: 10px; }
#resizable-13 {
	background-position: top left;
	width: 30px;
	height: 295px;
}
#resizable-15 {
	left: 110px;
	background-position: top 110px;
	width: 30px;
	height: 295px;
}
#resizable-17 {
	left: 120px;
	background-position: top 120px;
	width: 30px;
	height: 295px;
}
#resizable-19 {
	left: 130px;
	background-position: top 130px;
	width: 30px;
	height: 295px;
}
#resizable-21 {
	left: 140px;
	background-position: top 140px;
	width: 30px;
	height: 295px;
}
#resizable-25 {
	left: 100px;
	background-position: top 100px;
	width: 30px; height: 295px;
}
#resizable-27 {
	left: 110px;
	background-position: top left;
	width: 30px; height: 295px;
}
#resizable-29 {
	left: 120px;
	background-position: top left;
	width: 30px; height: 295px;
}
#resizable_desc {
	background-position: top 40px;
	width: 110px;
	height: 295px;
}
#resizable_unit {
	left: 150px;
	background-position: top left;
	width: 10px;
	height: 295px;
}

#sender_frame {
    position:relative;
    float:left;
    height:100%;
    width:93px;
    background-color:IndianRed;
}
#recipient_frame {
    position:relative;
    float:left;
    height:100%;
    width:93px;
    background-color:BurlyWood;
}

::-webkit-input-placeholder {
   color: #003f7f;
}
:-moz-placeholder { /* Firefox 18- */
   color: #003f7f;
}
::-moz-placeholder {  /* Firefox 19+ */
   color: #003f7f;
}
:-ms-input-placeholder {
   color: #003f7f;
}

<!-- End set Logo height -->

<!-- Set introjs -->
.introjs-overlay {
  position: absolute;
  box-sizing: content-box;
  z-index: 999999;
  background-color: #000;
  opacity: 0;
  background: -moz-radial-gradient(center,ellipse farthest-corner,rgba(0,0,0,0.4) 0,rgba(0,0,0,0.9) 100%);
  background: -webkit-gradient(radial,center center,0px,center center,100%,color-stop(0%,rgba(0,0,0,0.4)),color-stop(100%,rgba(0,0,0,0.9)));
  background: -webkit-radial-gradient(center,ellipse farthest-corner,rgba(0,0,0,0.4) 0,rgba(0,0,0,0.9) 100%);
  background: -o-radial-gradient(center,ellipse farthest-corner,rgba(0,0,0,0.4) 0,rgba(0,0,0,0.9) 100%);
  background: -ms-radial-gradient(center,ellipse farthest-corner,rgba(0,0,0,0.4) 0,rgba(0,0,0,0.9) 100%);
  background: radial-gradient(center,ellipse farthest-corner,rgba(0,0,0,0.4) 0,rgba(0,0,0,0.9) 100%);
  filter: "progid:DXImageTransform.Microsoft.gradient(startColorstr='#66000000',endColorstr='#e6000000',GradientType=1)";
  -ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity=50)";
  filter: alpha(opacity=50);
  -webkit-transition: all 0.3s ease-out;
     -moz-transition: all 0.3s ease-out;
      -ms-transition: all 0.3s ease-out;
       -o-transition: all 0.3s ease-out;
          transition: all 0.3s ease-out;
}

.introjs-fixParent {
  z-index: auto !important;
  opacity: 1.0 !important;
  -webkit-transform: none !important;
     -moz-transform: none !important;
      -ms-transform: none !important;
       -o-transform: none !important;
          transform: none !important;
}

.introjs-showElement,
tr.introjs-showElement > td,
tr.introjs-showElement > th {
  z-index: 9999999 !important;
}

.introjs-disableInteraction {
  z-index: 99999999 !important;
  position: absolute;
  background-color: white;
  opacity: 0;
  filter: alpha(opacity=0);
}

.introjs-relativePosition,
tr.introjs-showElement > td,
tr.introjs-showElement > th {
  position: relative;
}

.introjs-helperLayer {
  box-sizing: content-box;
  position: absolute;
  z-index: 9999998;
  background-color: #FFF;
  background-color: rgba(255,255,255,.9);
  border: 1px solid #777;
  border: 1px solid rgba(0,0,0,.5);
  border-radius: 4px;
  box-shadow: 0 2px 15px rgba(0,0,0,.4);
  -webkit-transition: all 0.3s ease-out;
     -moz-transition: all 0.3s ease-out;
      -ms-transition: all 0.3s ease-out;
       -o-transition: all 0.3s ease-out;
          transition: all 0.3s ease-out;
}

.introjs-tooltipReferenceLayer {
  box-sizing: content-box;
  position: absolute;
  visibility: hidden;
  z-index: 100000000;
  background-color: transparent;
  -webkit-transition: all 0.3s ease-out;
     -moz-transition: all 0.3s ease-out;
      -ms-transition: all 0.3s ease-out;
       -o-transition: all 0.3s ease-out;
          transition: all 0.3s ease-out;
}

.introjs-helperLayer *,
.introjs-helperLayer *:before,
.introjs-helperLayer *:after {
  -webkit-box-sizing: content-box;
     -moz-box-sizing: content-box;
      -ms-box-sizing: content-box;
       -o-box-sizing: content-box;
          box-sizing: content-box;
}

.introjs-helperNumberLayer {
  box-sizing: content-box;
  position: absolute;
  visibility: visible;
  top: -16px;
  left: -16px;
  z-index: 9999999999 !important;
  padding: 2px;
  font-family: Arial, verdana, tahoma;
  font-size: 13px;
  font-weight: bold;
  color: white;
  text-align: center;
  text-shadow: 1px 1px 1px rgba(0,0,0,.3);
  background: #ff3019; /* Old browsers */
  background: -webkit-linear-gradient(top, #ff3019 0%, #cf0404 100%); /* Chrome10+,Safari5.1+ */
  background: -webkit-gradient(linear, left top, left bottom, color-stop(0%, #ff3019), color-stop(100%, #cf0404)); /* Chrome,Safari4+ */
  background:    -moz-linear-gradient(top, #ff3019 0%, #cf0404 100%); /* FF3.6+ */
  background:     -ms-linear-gradient(top, #ff3019 0%, #cf0404 100%); /* IE10+ */
  background:      -o-linear-gradient(top, #ff3019 0%, #cf0404 100%); /* Opera 11.10+ */
  background:         linear-gradient(to bottom, #ff3019 0%, #cf0404 100%);  /* W3C */
  width: 20px;
  height:20px;
  line-height: 20px;
  border: 3px solid white;
  border-radius: 50%;
  filter: "progid:DXImageTransform.Microsoft.gradient(startColorstr='#ff3019', endColorstr='#cf0404', GradientType=0)"; /* IE6-9 */
  filter: "progid:DXImageTransform.Microsoft.Shadow(direction=135, strength=2, color=ff0000)"; /* IE10 text shadows */
  box-shadow: 0 2px 5px rgba(0,0,0,.4);
}

.introjs-arrow {
  border: 5px solid transparent;
  content:'';
  position: absolute;
}
.introjs-arrow.top {
  top: -10px;
  border-bottom-color:white;
}
.introjs-arrow.top-right {
  top: -10px;
  right: 10px;
  border-bottom-color:white;
}
.introjs-arrow.top-middle {
  top: -10px;
  left: 50%;
  margin-left: -5px;
  border-bottom-color:white;
}
.introjs-arrow.right {
  right: -10px;
  top: 10px;
  border-left-color:white;
}
.introjs-arrow.right-bottom {
  bottom:10px;
  right: -10px;
  border-left-color:white;
}
.introjs-arrow.bottom {
  bottom: -10px;
  border-top-color:white;
}
.introjs-arrow.bottom-right {
  bottom: -10px;
  right: 10px;
  border-top-color:white;
}
.introjs-arrow.bottom-middle {
  bottom: -10px;
  left: 50%;
  margin-left: -5px;
  border-top-color:white;
}
.introjs-arrow.left {
  left: -10px;
  top: 10px;
  border-right-color:white;
}
.introjs-arrow.left-bottom {
  left: -10px;
  bottom:10px;
  border-right-color:white;
}

.introjs-tooltip {
  box-sizing: content-box;
  position: absolute;
  visibility: visible;
  padding: 10px;
  background-color: white;
  min-width: 200px;
  max-width: 300px;
  border-radius: 3px;
  box-shadow: 0 1px 10px rgba(0,0,0,.4);
  -webkit-transition: opacity 0.1s ease-out;
     -moz-transition: opacity 0.1s ease-out;
      -ms-transition: opacity 0.1s ease-out;
       -o-transition: opacity 0.1s ease-out;
          transition: opacity 0.1s ease-out;
}

.introjs-tooltipbuttons {
  text-align: right;
  white-space: nowrap;
}

/*
 Buttons style by http://nicolasgallagher.com/lab/css3-github-buttons/
 Changed by Afshin Mehrabani
*/
.introjs-button {
  box-sizing: content-box;
  position: relative;
  overflow: visible;
  display: inline-block;
  padding: 0.3em 0.8em;
  border: 1px solid #d4d4d4;
  margin: 0;
  text-decoration: none;
  text-shadow: 1px 1px 0 #fff;
  font: 11px/normal sans-serif;
  color: #333;
  white-space: nowrap;
  cursor: pointer;
  outline: none;
  background-color: #ececec;
  background-image: -webkit-gradient(linear, 0 0, 0 100%, from(#f4f4f4), to(#ececec));
  background-image: -moz-linear-gradient(#f4f4f4, #ececec);
  background-image: -o-linear-gradient(#f4f4f4, #ececec);
  background-image: linear-gradient(#f4f4f4, #ececec);
  -webkit-background-clip: padding;
  -moz-background-clip: padding;
  -o-background-clip: padding-box;
  /*background-clip: padding-box;*/ /* commented out due to Opera 11.10 bug */
  -webkit-border-radius: 0.2em;
  -moz-border-radius: 0.2em;
  border-radius: 0.2em;
  /* IE hacks */
  zoom: 1;
  *display: inline;
  margin-top: 10px;
}

.introjs-button:hover {
  border-color: #bcbcbc;
  text-decoration: none;
  box-shadow: 0px 1px 1px #e3e3e3;
}

.introjs-button:focus,
.introjs-button:active {
  background-image: -webkit-gradient(linear, 0 0, 0 100%, from(#ececec), to(#f4f4f4));
  background-image: -moz-linear-gradient(#ececec, #f4f4f4);
  background-image: -o-linear-gradient(#ececec, #f4f4f4);
  background-image: linear-gradient(#ececec, #f4f4f4);
}

/* overrides extra padding on button elements in Firefox */
.introjs-button::-moz-focus-inner {
  padding: 0;
  border: 0;
}

.introjs-skipbutton {
  box-sizing: content-box;
  margin-right: 5px;
  color: #7a7a7a;
}

.introjs-prevbutton {
  -webkit-border-radius: 0.2em 0 0 0.2em;
  -moz-border-radius: 0.2em 0 0 0.2em;
  border-radius: 0.2em 0 0 0.2em;
  border-right: none;
}

.introjs-prevbutton.introjs-fullbutton {
  border: 1px solid #d4d4d4;
  -webkit-border-radius: 0.2em;
  -moz-border-radius: 0.2em;
  border-radius: 0.2em;
}

.introjs-nextbutton {
  -webkit-border-radius: 0 0.2em 0.2em 0;
  -moz-border-radius: 0 0.2em 0.2em 0;
  border-radius: 0 0.2em 0.2em 0;
}

.introjs-nextbutton.introjs-fullbutton {
  -webkit-border-radius: 0.2em;
  -moz-border-radius: 0.2em;
  border-radius: 0.2em;
}

.introjs-disabled, .introjs-disabled:hover, .introjs-disabled:focus {
  color: #9a9a9a;
  border-color: #d4d4d4;
  box-shadow: none;
  cursor: default;
  background-color: #f4f4f4;
  background-image: none;
  text-decoration: none;
}

.introjs-hidden {
     display: none;
}

.introjs-bullets {
  text-align: center;
}
.introjs-bullets ul {
  box-sizing: content-box;
  clear: both;
  margin: 15px auto 0;
  padding: 0;
  display: inline-block;
}
.introjs-bullets ul li {
  box-sizing: content-box;
  list-style: none;
  float: left;
  margin: 0 2px;
}
.introjs-bullets ul li a {
  box-sizing: content-box;
  display: block;
  width: 6px;
  height: 6px;
  background: #ccc;
  border-radius: 10px;
  -moz-border-radius: 10px;
  -webkit-border-radius: 10px;
  text-decoration: none;
  cursor: pointer;
}
.introjs-bullets ul li a:hover {
  background: #999;
}
.introjs-bullets ul li a.active {
  background: #999;
}

.introjs-progress {
  box-sizing: content-box;
  overflow: hidden;
  height: 10px;
  margin: 10px 0 5px 0;
  border-radius: 4px;
  background-color: #ecf0f1
}
.introjs-progressbar {
  box-sizing: content-box;
  float: left;
  width: 0%;
  height: 100%;
  font-size: 10px;
  line-height: 10px;
  text-align: center;
  background-color: #08c;
}

.introjsFloatingElement {
  position: absolute;
  height: 0;
  width: 0;
  left: 50%;
  top: 50%;
}

.introjs-fixedTooltip {
  position: fixed;
}

.introjs-hint {
  box-sizing: content-box;
  position: absolute;
  background: transparent;
  width: 20px;
  height: 15px;
  cursor: pointer;
}
.introjs-hint:focus {
    border: 0;
    outline: 0;
}
.introjs-hidehint {
  display: none;
}

.introjs-fixedhint {
  position: fixed;
}

.introjs-hint:hover > .introjs-hint-pulse {
  border: 5px solid rgba(60, 60, 60, 0.57);
}

.introjs-hint-pulse {
  box-sizing: content-box;
  width: 10px;
  height: 10px;
  border: 5px solid rgba(60, 60, 60, 0.27);
  -webkit-border-radius: 30px;
  -moz-border-radius: 30px;
  border-radius: 30px;
  background-color: rgba(136, 136, 136, 0.24);
  z-index: 10;
  position: absolute;
  -webkit-transition: all 0.2s ease-out;
     -moz-transition: all 0.2s ease-out;
      -ms-transition: all 0.2s ease-out;
       -o-transition: all 0.2s ease-out;
          transition: all 0.2s ease-out;
}
.introjs-hint-no-anim .introjs-hint-dot {
  -webkit-animation: none;
  -moz-animation: none;
  animation: none;
}
.introjs-hint-dot {
  box-sizing: content-box;
  border: 10px solid rgba(146, 146, 146, 0.36);
  background: transparent;
  -webkit-border-radius: 60px;
  -moz-border-radius: 60px;
  border-radius: 60px;
  height: 50px;
  width: 50px;
  -webkit-animation: introjspulse 3s ease-out;
  -moz-animation: introjspulse 3s ease-out;
  animation: introjspulse 3s ease-out;
  -webkit-animation-iteration-count: infinite;
  -moz-animation-iteration-count: infinite;
  animation-iteration-count: infinite;
  position: absolute;
  top: -25px;
  left: -25px;
  z-index: 1;
  opacity: 0;
}

@-webkit-keyframes introjspulse {
    0% {
        -webkit-transform: scale(0);
        opacity: 0.0;
    }
    25% {
        -webkit-transform: scale(0);
        opacity: 0.1;
    }
    50% {
        -webkit-transform: scale(0.1);
        opacity: 0.3;
    }
    75% {
        -webkit-transform: scale(0.5);
        opacity: 0.5;
    }
    100% {
        -webkit-transform: scale(1);
        opacity: 0.0;
    }
}

@-moz-keyframes introjspulse {
    0% {
        -moz-transform: scale(0);
        opacity: 0.0;
    }
    25% {
        -moz-transform: scale(0);
        opacity: 0.1;
    }
    50% {
        -moz-transform: scale(0.1);
        opacity: 0.3;
    }
    75% {
        -moz-transform: scale(0.5);
        opacity: 0.5;
    }
    100% {
        -moz-transform: scale(1);
        opacity: 0.0;
    }
}

@keyframes introjspulse {
    0% {
        transform: scale(0);
        opacity: 0.0;
    }
    25% {
        transform: scale(0);
        opacity: 0.1;
    }
    50% {
        transform: scale(0.1);
        opacity: 0.3;
    }
    75% {
        transform: scale(0.5);
        opacity: 0.5;
    }
    100% {
        transform: scale(1);
        opacity: 0.0;
    }
}

<?php
if (($conf->global->MAIN_THEME === 'eldy' && empty($conf->global->ULTIMATEPDF_DROPDOWN_MENU_DISABLED) && ! GETPOSTISSET('theme')) || (GETPOSTISSET('theme') && GETPOST('theme', 'aZ', 1) === 'eldy')) {
	include dol_buildpath('/ultimatepdf/css/dropdown.inc.php');
}


