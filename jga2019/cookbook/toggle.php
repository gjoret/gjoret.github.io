<?php if (!defined('PmWiki')) exit();
/*  Copyright 2009 Hans Bracker. 
    This file is toggle.php; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published
    by the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.
    
    (:toggle id=divname :) creates a toggle link, which can show or hide 
    a division or other object on the page, for instance a div created with
    >>id=divisionname<< 
    text can be hidden/shown 
    >><< 
    Necessary parameters: (:toggle id=divname:) 
    Alternative: (:toggle divname:)
    Alternative with options: 
    (:toggle hide divname:) initial hide
    (:toggle hide divname button:) initial hide, button
    (:toggle name1 name2:) toggle between name1 and name2
    Optional parameters:
    init=hide  hides the division initially (default is show)
    show=labelname  label of link or button when div is hidden (default is Show)
    hide=labelname label of link or button when div is shown (default is Hide)
    label=labelname label of link or button for both toggle states
    id2=objname second object (div), for toggling betwen first and second object
    set=1 sets a cookie to remember toggle state
*/ 
# Version date
$RecipeInfo['Toggle']['Version'] = '2014-02-21';

# declare $Toggle for (:if enabled $Toggle:) recipe installation check
global $Toggle; $Toggle = 1;

Markup_e('toggle', 'directives',
  '/\\(:toggle\\s*(.*?):\\)/i',
  "ToggleMarkup(\$pagename, \$m[1])");
  
# all in one function
function ToggleMarkup($pagename, $opt) {
  # javascript for toggling and cookie setting
  global $HTMLFooterFmt, $HTMLStylesFmt, $ToggleConfig, $ToggleLinks, $UploadUrlFmt, $UploadPrefixFmt;

  SDVA($ToggleConfig, array(
    'init' => 'hide',       //show div 
    'show' => XL("Show"),  //link text 'Show'
    'hide' => XL("Hide"),  //link text 'Hide'
    'ttshow' => XL("Show"),  //tooltip text 'Show'
    'tthide' => XL("Hide"),  //tooltip text 'Hide'
    'id' => '',            //no default div name
    'id2' => '',           //no default div2 name
    'set' => false,        //set no cookie to remember toggle state
    'printhidden' => true, // hidden divs get printed
    'nojs' => false,       //in no jsbrowser links are not shown, initial hidden divs are shown
  ));   

  $HTMLStylesFmt['toggle'] = " @media print{.toggle{display:none;}} .toggle img{border:none;} ";
   
  $HTMLFooterFmt['toggleobj'] = "
  <script type=\"text/javascript\"><!--
    function toggleObj(obj, tog, show, hide, tts, tth, swap, set, cook, button, group) {
      var tspan = document.getElementById(obj + \"-tog\");
	    var el = document.getElementById(obj);
	    if (hide && swap != '') var e2 = document.getElementById(swap);
	    if (set == '1') document.cookie = cook+'='+tog+'; path=/';  
      if (group) {
	      	var allHTMLTags = document.getElementsByTagName('DIV');
	      	for (i=0; i<allHTMLTags.length; i++) {
						var ei = allHTMLTags[i];
						var tc = ei.getAttribute('class');
						if (tc == null) continue;
						if (!tc.match(group)) continue;
						ei.style.display = 'none';
						var eid = ei.getAttribute('id');
						var ispan = document.getElementById(eid + \"-tog\");
						setToggleLink(ispan, show, tts, eid, 'show', show, hide, tts, tth, swap, set, cook, button, group);
					}
			}	
      if (tog == 'show') {
	       	el.style.display = 'block';
	       	if(swap != '') e2.style.display = 'none';
	        var label = hide;
	        var ttip = tth;
	        tog = 'hide';        
	    } else { 
	       	el.style.display = 'none';
	     	  if (swap != '') e2.style.display = 'block';
	        var label = show;
	        var ttip = tts;
	        tog = 'show';
	    }
		  setToggleLink(tspan,  label, ttip, obj, tog, show, hide, tts, tth, swap, set, cook, button, group);
    }
    function setToggleLink(tspan, label, ttip, obj, tog, show, hide, tts, tth, swap, set, cook, button, group) {
	    var act = '\"javascript:toggleObj(\''+obj+'\',\''+tog+'\',\''+show+'\',\''+hide+'\',\''+tts+'\',\''+tth+'\',\''+swap+'\',\''+set+'\',\''+cook+'\',\''+button+'\',\''+group+'\');\"'; 
	    tspan.innerHTML = (button==1)
	     ? '<input type=\"button\" class=\"inputbutton togglebutton\" value=\"'+label+'\" onclick='+act+' />'
	     : '<a class=\"togglelink\" title=\"'+ttip+'\" href='+act+'>'+label+'</a>';
	     return '';   
		}
  --></script>";    
  $opt = ParseArgs($opt); 
  if ($opt['group'] && $opt['init']!='show')  $opt['init'] = 'hide'; 
  //get parameters without keys
  if(is_array($opt[''])) {
    while (count($opt['']) > 0) {
      $par = array_shift($opt['']);
      if($par == 'button') $opt['button'] = 1;
      elseif($par == 'hide') $opt['init'] = 'hide';
      elseif($par == 'show') $opt['init'] = 'show';
      elseif(!isset($opt['id'])) $opt['id'] = $par;
      elseif(!isset($opt['id2'])) $opt['id2'] = $par;     
    }
  }
  $opt = array_merge($ToggleConfig, $opt);

  $id = (isset($opt['div'])) ? $opt['div'] : $opt['id'];
  $id2 = (isset($opt['div2'])) ? $opt['div2'] : $opt['id2'];
  if ($id == '') return "//!Error:// no object id specified!"; 
  $ts = array();
  if(isset($opt['label'])) 
    $ts['show'] = $ts['hide'] = $opt['label'];
  else {
    $ts['show'] = (isset($opt['lshow'])) ? $opt['lshow'] : $opt['show'];
    $ts['hide'] = (isset($opt['lhide'])) ? $opt['lhide'] : $opt['hide'];  
  }
  $ipat = "/\.png|\.gif|\.jpg|\.jpeg|\.ico/";
  foreach($ts as $k => $val) {
    //check for image, make image tag
    if(preg_match($ipat, $val)) {
      $prefix = (strstr($val, '/')) ? '/' : $UploadPrefixFmt; 
      $path = FmtPageName($UploadUrlFmt.$prefix, $pagename);
      $ts[$k] = "<img src=$path/$val title={$opt['tt'.$k]}&nbsp;$id />";
      $opt['button'] = '';
    }
    //apostrophe encoding
    else $ts[$k] = str_replace("'","&rsquo;",$val);
  }  
  $show = $ts['show']; $hide = $ts['hide'];
	$tog = $opt['init'];

  //check cookie if set=1
  if($opt['set'] == 1) { 
    global $CookiePrefix, $SkinName;
    $cook = $CookiePrefix.$SkinName.'_toggle_'.$id;
    if (isset($_COOKIE[$cook])) $tog = $_COOKIE[$cook];
  }

  //toggle state 
  if($tog == 'show') { 
    $style = 'block';
    $altstyle = 'none';
    $label = $hide;
    $tooltip = $opt['tthide'];
    $tog = 'hide';
  } else {
    $style = 'none';
    $altstyle = 'block';
    $label = $show;
    $tooltip = $opt['ttshow'];
    $tog = 'show';
  }

  //set initial toggle link or button (later it is build with javascript)
  $act = "javascript:toggleObj('{$id}','{$tog}','{$show}','{$hide}','{$opt['ttshow']}','{$opt['tthide']}','{$id2}','{$opt['set']}','{$cook}','{$opt['button']}','{$opt['group']}')";
  $out = "<span id='{$id}-tog' class='toggle'>";
  if ($opt['button']==1) {
    $out .= '<script type="text/javascript">document.write("<input type=\'button\' class=\'inputbutton togglebutton\' value=\''.$label.'\' onclick=\"'.$act.'\" />")</script>';
 		if ($opt['nojs']>=1) $out .= '<noscript><input type=\'button\' class=\'inputbutton togglebutton\' value=\''.$label.'\' onclick="'.$act.'" /></noscript>';
  } else {
    $out .= '<script type="text/javascript">document.write("<a class=\'togglelink\' title=\''.$tooltip.'\' href=\"'.$act.'\">'.$label.'</a>")</script> ';
  	if ($opt['nojs']>=1) $out .= '<noscript><a class=\'togglelink\' >'.$label.'</a></noscript>';
  }
  $out .= "</span>";
	$HTMLFooterFmt[] = "<script type=\"text/javascript\">document.getElementById(\"{$id}\").style.display = '{$style}';</script>";
  if ($style=='none')
  		if ($id2 || $opt['nojs']>1) $HTMLStylesFmt[] = " #$id {display:none;}";
  if ($opt['printhidden']==1) $HTMLStylesFmt[] = " @media print{ #{$id}{ display:block; } } ";
  if ($id2) { 
    $HTMLStylesFmt[] = " #{$id2}{display:{$altstyle};} ";
		$HTMLFooterFmt[] = "<script type=\"text/javascript\">document.getElementById(\"{$id2}\").style.display = '{$altstyle}';</script>";
    if ($opt['printhidden'] == 1) $HTMLStylesFmt[] = " @media print { #{$id2}{ display:block; } } ";
  }
  return Keep($out);
}
#EOF
