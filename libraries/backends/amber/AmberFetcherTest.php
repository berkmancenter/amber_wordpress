<?php

require_once("AmberFetcher.php");
require_once("AmberStorage.php");

class AmberRobotsTest extends \PHPUnit_Framework_TestCase {

  public function testRobotsParse1()
  {
    $this->assertTrue(AmberRobots::url_permitted("","www.google.com"));
    $this->assertTrue(AmberRobots::url_permitted("Donuts","www.google.com"));
  }

  public function testRobotsParse2() {
    $this->assertFalse(AmberRobots::url_permitted(<<<EOD
User-agent: *
Disallow: /

EOD
,"/a_url"));
  }

  public function testRobotsParse3() {
    $this->assertTrue(AmberRobots::url_permitted(<<<EOD
User-agent: *
Disallow: /man

EOD
,"/a_url"));
  }

  public function testRobotsParse4() {
    $this->assertFalse(AmberRobots::url_permitted(<<<EOD
User-agent: *
Disallow: /man

EOD
,"/man/a_url"));
  }

  public function testRobotsParse5() {
    $this->assertTrue(AmberRobots::url_permitted(<<<EOD
User-agent: *
Disallow: /man

EOD
,"/foo"));
  }

  public function testRobotsParse6() {
    $this->assertTrue(AmberRobots::url_permitted(<<<EOD
User-agent: *
Disallow: 

EOD
,"/man/a_url"));
  }

  public function testRobotsParseHTMLInsteadOfRobotstxt() {
        $this->assertTrue(AmberRobots::url_permitted(<<<EOD
          <!DOCTYPEhtmlPUBLIC"-//W3C//DTDXHTML1.0Transitional//EN""http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><htmlxmlns="http://www.w3.org/1999/xhtml"lang="en"xml:lang="en"><head><scripttype="text/javascript">/*<![CDATA[*/varue_t0=ue_t0||+newDate();/*]]>*/</script><scripttype="text/javascript">/*<![CDATA[*/varue_id='1983VYSJSQ23ECVP5ED9',ue_csm=window,ue_url='http://shop.fruit.com/uedata/178-0385643-2431924',ue_pr='0',ue_md='/mn/uedata/?tp=',ue_navtiming=0,ue_mid='A3H9RROGOL2TZC',ue_sid='178-0385643-2431924',ue_furl='fls-na.amazon.com',ue_tags=0,ue_ifr=0;<script>/*<![CDATA[*/if(typeofws==="undefined"){varws={};};ws.WebstoreOverallPageProperties={'clientSidePreLoadingEnabled':"true"};/*]]>*/</script><divid='be'style="display:none;"><formname='ue_backdetect'action="get"><inputtype="hidden"name='ue_back'value='1'/></form><scripttype='text/javascript'>/*<![CDATA[*/(function(a){a._uess=function(){vard="";if(screen&&screen.width&&screen.height){d+="&sw="+screen.width+"&sh="+screen.height}varc=function(g){varf=document.documentElement["client"+g];returndocument.compatMode==="CSS1Compat"&&f||document.body["client"+g]||f};vare=c("Width"),b=c("Height");if(e&&b){d+="&vw="+e+"&vh="+b}returnd}})(ue_csm);(function(a){if(a.uet){a.uet("be")}if(a.onLdEnd){if(window.addEventListener){window.addEventListener("load",a.onLdEnd,false)}else{if(window.attachEvent){window.attachEvent("onload",a.onLdEnd)}}}if(a.ueh){a.ueh(0,window,"load",a.onLd,1)}if(a.ue_pr&&(a.ue_pr==3||a.ue_pr==4)){a.ue._uep()}})(ue_csm);/*]]>*/</script></div><noscript><imgheight="1"width="1"style='display:none;visibility:hidden;'src='http://shop.fruit.com/uedata/178-0385643-2431924?noscript&amp;id=1983VYSJSQ23ECVP5ED9'alt=""/></noscript></body></html><!DOCTYPEhtmlPUBLIC"-//W3C//DTDXHTML1.0Transitional//EN""http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><htmlxmlns="http://www.w3.org/1999/xhtml"lang="en"xml:lang="en"><head><scripttype="text/javascript">/*<![CDATA[*/varue_t0=ue_t0||+newDate();/*]]>*/</script><scripttype="text/javascript">/*<![CDATA[*/varue_id='1983VYSJSQ23ECVP5ED9',ue_csm=window,ue_url='http://shop.fruit.com/uedata/178-0385643-2431924',ue_pr='0',ue_md='/mn/uedata/?tp=',ue_navtiming=0,ue_mid='A3H9RROGOL2TZC',ue_sid='178-0385643-2431924',ue_furl='fls-na.amazon.com',ue_tags=0,ue_ifr=0;<script>/*<![CDATA[*/if(typeofws==="undefined"){varws={};};ws.WebstoreOverallPageProperties={'clientSidePreLoadingEnabled':"true"};/*]]>*/</script><divid='be'style="display:none;"><formname='ue_backdetect'action="get"><inputtype="hidden"name='ue_back'value='1'/></form><scripttype='text/javascript'>/*<![CDATA[*/(function(a){a._uess=function(){vard="";if(screen&&screen.width&&screen.height){d+="&sw="+screen.width+"&sh="+screen.height}varc=function(g){varf=document.documentElement["client"+g];returndocument.compatMode==="CSS1Compat"&&f||document.body["client"+g]||f};vare=c("Width"),b=c("Height");if(e&&b){d+="&vw="+e+"&vh="+b}returnd}})(ue_csm);(function(a){if(a.uet){a.uet("be")}if(a.onLdEnd){if(window.addEventListener){window.addEventListener("load",a.onLdEnd,false)}else{if(window.attachEvent){window.attachEvent("onload",a.onLdEnd)}}}if(a.ueh){a.ueh(0,window,"load",a.onLd,1)}if(a.ue_pr&&(a.ue_pr==3||a.ue_pr==4)){a.ue._uep()}})(ue_csm);/*]]>*/</script></div><noscript><imgheight="1"width="1"style='display:none;visibility:hidden;'src='http://shop.fruit.com/uedata/178-0385643-2431924?noscript&amp;id=1983VYSJSQ23ECVP5ED9'alt=""/></noscript></body></html><!DOCTYPEhtmlPUBLIC"-//W3C//DTDXHTML1.0Transitional//EN""http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><htmlxmlns="http://www.w3.org/1999/xhtml"lang="en"xml:lang="en"><head><scripttype="text/javascript">/*<![CDATA[*/varue_t0=ue_t0||+newDate();/*]]>*/</script><scripttype="text/javascript">/*<![CDATA[*/varue_id='1983VYSJSQ23ECVP5ED9',ue_csm=window,ue_url='http://shop.fruit.com/uedata/178-0385643-2431924',ue_pr='0',ue_md='/mn/uedata/?tp=',ue_navtiming=0,ue_mid='A3H9RROGOL2TZC',ue_sid='178-0385643-2431924',ue_furl='fls-na.amazon.com',ue_tags=0,ue_ifr=0;<script>/*<![CDATA[*/if(typeofws==="undefined"){varws={};};ws.WebstoreOverallPageProperties={'clientSidePreLoadingEnabled':"true"};/*]]>*/</script><divid='be'style="display:none;"><formname='ue_backdetect'action="get"><inputtype="hidden"name='ue_back'value='1'/></form><scripttype='text/javascript'>/*<![CDATA[*/(function(a){a._uess=function(){vard="";if(screen&&screen.width&&screen.height){d+="&sw="+screen.width+"&sh="+screen.height}varc=function(g){varf=document.documentElement["client"+g];returndocument.compatMode==="CSS1Compat"&&f||document.body["client"+g]||f};vare=c("Width"),b=c("Height");if(e&&b){d+="&vw="+e+"&vh="+b}returnd}})(ue_csm);(function(a){if(a.uet){a.uet("be")}if(a.onLdEnd){if(window.addEventListener){window.addEventListener("load",a.onLdEnd,false)}else{if(window.attachEvent){window.attachEvent("onload",a.onLdEnd)}}}if(a.ueh){a.ueh(0,window,"load",a.onLd,1)}if(a.ue_pr&&(a.ue_pr==3||a.ue_pr==4)){a.ue._uep()}})(ue_csm);/*]]>*/</script></div><noscript><imgheight="1"width="1"style='display:none;visibility:hidden;'src='http://shop.fruit.com/uedata/178-0385643-2431924?noscript&amp;id=1983VYSJSQ23ECVP5ED9'alt=""/></noscript></body></html><!DOCTYPEhtmlPUBLIC"-//W3C//DTDXHTML1.0Transitional//EN""http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><htmlxmlns="http://www.w3.org/1999/xhtml"lang="en"xml:lang="en"><head><scripttype="text/javascript">/*<![CDATA[*/varue_t0=ue_t0||+newDate();/*]]>*/</script><scripttype="text/javascript">/*<![CDATA[*/varue_id='1983VYSJSQ23ECVP5ED9',ue_csm=window,ue_url='http://shop.fruit.com/uedata/178-0385643-2431924',ue_pr='0',ue_md='/mn/uedata/?tp=',ue_navtiming=0,ue_mid='A3H9RROGOL2TZC',ue_sid='178-0385643-2431924',ue_furl='fls-na.amazon.com',ue_tags=0,ue_ifr=0;<script>/*<![CDATA[*/if(typeofws==="undefined"){varws={};};ws.WebstoreOverallPageProperties={'clientSidePreLoadingEnabled':"true"};/*]]>*/</script><divid='be'style="display:none;"><formname='ue_backdetect'action="get"><inputtype="hidden"name='ue_back'value='1'/></form><scripttype='text/javascript'>/*<![CDATA[*/(function(a){a._uess=function(){vard="";if(screen&&screen.width&&screen.height){d+="&sw="+screen.width+"&sh="+screen.height}varc=function(g){varf=document.documentElement["client"+g];returndocument.compatMode==="CSS1Compat"&&f||document.body["client"+g]||f};vare=c("Width"),b=c("Height");if(e&&b){d+="&vw="+e+"&vh="+b}returnd}})(ue_csm);(function(a){if(a.uet){a.uet("be")}if(a.onLdEnd){if(window.addEventListener){window.addEventListener("load",a.onLdEnd,false)}else{if(window.attachEvent){window.attachEvent("onload",a.onLdEnd)}}}if(a.ueh){a.ueh(0,window,"load",a.onLd,1)}if(a.ue_pr&&(a.ue_pr==3||a.ue_pr==4)){a.ue._uep()}})(ue_csm);/*]]>*/</script></div><noscript><imgheight="1"width="1"style='display:none;visibility:hidden;'src='http://shop.fruit.com/uedata/178-0385643-2431924?noscript&amp;id=1983VYSJSQ23ECVP5ED9'alt=""/></noscript></body></html><!DOCTYPEhtmlPUBLIC"-//W3C//DTDXHTML1.0Transitional//EN""http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><htmlxmlns="http://www.w3.org/1999/xhtml"lang="en"xml:lang="en"><head><scripttype="text/javascript">/*<![CDATA[*/varue_t0=ue_t0||+newDate();/*]]>*/</script><scripttype="text/javascript">/*<![CDATA[*/varue_id='1983VYSJSQ23ECVP5ED9',ue_csm=window,ue_url='http://shop.fruit.com/uedata/178-0385643-2431924',ue_pr='0',ue_md='/mn/uedata/?tp=',ue_navtiming=0,ue_mid='A3H9RROGOL2TZC',ue_sid='178-0385643-2431924',ue_furl='fls-na.amazon.com',ue_tags=0,ue_ifr=0;<script>/*<![CDATA[*/if(typeofws==="undefined"){varws={};};ws.WebstoreOverallPageProperties={'clientSidePreLoadingEnabled':"true"};/*]]>*/</script><divid='be'style="display:none;"><formname='ue_backdetect'action="get"><inputtype="hidden"name='ue_back'value='1'/></form><scripttype='text/javascript'>/*<![CDATA[*/(function(a){a._uess=function(){vard="";if(screen&&screen.width&&screen.height){d+="&sw="+screen.width+"&sh="+screen.height}varc=function(g){varf=document.documentElement["client"+g];returndocument.compatMode==="CSS1Compat"&&f||document.body["client"+g]||f};vare=c("Width"),b=c("Height");if(e&&b){d+="&vw="+e+"&vh="+b}returnd}})(ue_csm);(function(a){if(a.uet){a.uet("be")}if(a.onLdEnd){if(window.addEventListener){window.addEventListener("load",a.onLdEnd,false)}else{if(window.attachEvent){window.attachEvent("onload",a.onLdEnd)}}}if(a.ueh){a.ueh(0,window,"load",a.onLd,1)}if(a.ue_pr&&(a.ue_pr==3||a.ue_pr==4)){a.ue._uep()}})(ue_csm);/*]]>*/</script></div><noscript><imgheight="1"width="1"style='display:none;visibility:hidden;'src='http://shop.fruit.com/uedata/178-0385643-2431924?noscript&amp;id=1983VYSJSQ23ECVP5ED9'alt=""/></noscript></body></html><!DOCTYPEhtmlPUBLIC"-//W3C//DTDXHTML1.0Transitional//EN""http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><htmlxmlns="http://www.w3.org/1999/xhtml"lang="en"xml:lang="en"><head><scripttype="text/javascript">/*<![CDATA[*/varue_t0=ue_t0||+newDate();/*]]>*/</script><scripttype="text/javascript">/*<![CDATA[*/varue_id='1983VYSJSQ23ECVP5ED9',ue_csm=window,ue_url='http://shop.fruit.com/uedata/178-0385643-2431924',ue_pr='0',ue_md='/mn/uedata/?tp=',ue_navtiming=0,ue_mid='A3H9RROGOL2TZC',ue_sid='178-0385643-2431924',ue_furl='fls-na.amazon.com',ue_tags=0,ue_ifr=0;<script>/*<![CDATA[*/if(typeofws==="undefined"){varws={};};ws.WebstoreOverallPageProperties={'clientSidePreLoadingEnabled':"true"};/*]]>*/</script><divid='be'style="display:none;"><formname='ue_backdetect'action="get"><inputtype="hidden"name='ue_back'value='1'/></form><scripttype='text/javascript'>/*<![CDATA[*/(function(a){a._uess=function(){vard="";if(screen&&screen.width&&screen.height){d+="&sw="+screen.width+"&sh="+screen.height}varc=function(g){varf=document.documentElement["client"+g];returndocument.compatMode==="CSS1Compat"&&f||document.body["client"+g]||f};vare=c("Width"),b=c("Height");if(e&&b){d+="&vw="+e+"&vh="+b}returnd}})(ue_csm);(function(a){if(a.uet){a.uet("be")}if(a.onLdEnd){if(window.addEventListener){window.addEventListener("load",a.onLdEnd,false)}else{if(window.attachEvent){window.attachEvent("onload",a.onLdEnd)}}}if(a.ueh){a.ueh(0,window,"load",a.onLd,1)}if(a.ue_pr&&(a.ue_pr==3||a.ue_pr==4)){a.ue._uep()}})(ue_csm);/*]]>*/</script></div><noscript><imgheight="1"width="1"style='display:none;visibility:hidden;'src='http://shop.fruit.com/uedata/178-0385643-2431924?noscript&amp;id=1983VYSJSQ23ECVP5ED9'alt=""/></noscript></body></html><!DOCTYPEhtmlPUBLIC"-//W3C//DTDXHTML1.0Transitional//EN""http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><htmlxmlns="http://www.w3.org/1999/xhtml"lang="en"xml:lang="en"><head><scripttype="text/javascript">/*<![CDATA[*/varue_t0=ue_t0||+newDate();/*]]>*/</script><scripttype="text/javascript">/*<![CDATA[*/varue_id='1983VYSJSQ23ECVP5ED9',ue_csm=window,ue_url='http://shop.fruit.com/uedata/178-0385643-2431924',ue_pr='0',ue_md='/mn/uedata/?tp=',ue_navtiming=0,ue_mid='A3H9RROGOL2TZC',ue_sid='178-0385643-2431924',ue_furl='fls-na.amazon.com',ue_tags=0,ue_ifr=0;<script>/*<![CDATA[*/if(typeofws==="undefined"){varws={};};ws.WebstoreOverallPageProperties={'clientSidePreLoadingEnabled':"true"};/*]]>*/</script><divid='be'style="display:none;"><formname='ue_backdetect'action="get"><inputtype="hidden"name='ue_back'value='1'/></form><scripttype='text/javascript'>/*<![CDATA[*/(function(a){a._uess=function(){vard="";if(screen&&screen.width&&screen.height){d+="&sw="+screen.width+"&sh="+screen.height}varc=function(g){varf=document.documentElement["client"+g];returndocument.compatMode==="CSS1Compat"&&f||document.body["client"+g]||f};vare=c("Width"),b=c("Height");if(e&&b){d+="&vw="+e+"&vh="+b}returnd}})(ue_csm);(function(a){if(a.uet){a.uet("be")}if(a.onLdEnd){if(window.addEventListener){window.addEventListener("load",a.onLdEnd,false)}else{if(window.attachEvent){window.attachEvent("onload",a.onLdEnd)}}}if(a.ueh){a.ueh(0,window,"load",a.onLd,1)}if(a.ue_pr&&(a.ue_pr==3||a.ue_pr==4)){a.ue._uep()}})(ue_csm);/*]]>*/</script></div><noscript><imgheight="1"width="1"style='display:none;visibility:hidden;'src='http://shop.fruit.com/uedata/178-0385643-2431924?noscript&amp;id=1983VYSJSQ23ECVP5ED9'alt=""/></noscript></body></html><!DOCTYPEhtmlPUBLIC"-//W3C//DTDXHTML1.0Transitional//EN""http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><htmlxmlns="http://www.w3.org/1999/xhtml"lang="en"xml:lang="en"><head><scripttype="text/javascript">/*<![CDATA[*/varue_t0=ue_t0||+newDate();/*]]>*/</script><scripttype="text/javascript">/*<![CDATA[*/varue_id='1983VYSJSQ23ECVP5ED9',ue_csm=window,ue_url='http://shop.fruit.com/uedata/178-0385643-2431924',ue_pr='0',ue_md='/mn/uedata/?tp=',ue_navtiming=0,ue_mid='A3H9RROGOL2TZC',ue_sid='178-0385643-2431924',ue_furl='fls-na.amazon.com',ue_tags=0,ue_ifr=0;<script>/*<![CDATA[*/if(typeofws==="undefined"){varws={};};ws.WebstoreOverallPageProperties={'clientSidePreLoadingEnabled':"true"};/*]]>*/</script><divid='be'style="display:none;"><formname='ue_backdetect'action="get"><inputtype="hidden"name='ue_back'value='1'/></form><scripttype='text/javascript'>/*<![CDATA[*/(function(a){a._uess=function(){vard="";if(screen&&screen.width&&screen.height){d+="&sw="+screen.width+"&sh="+screen.height}varc=function(g){varf=document.documentElement["client"+g];returndocument.compatMode==="CSS1Compat"&&f||document.body["client"+g]||f};vare=c("Width"),b=c("Height");if(e&&b){d+="&vw="+e+"&vh="+b}returnd}})(ue_csm);(function(a){if(a.uet){a.uet("be")}if(a.onLdEnd){if(window.addEventListener){window.addEventListener("load",a.onLdEnd,false)}else{if(window.attachEvent){window.attachEvent("onload",a.onLdEnd)}}}if(a.ueh){a.ueh(0,window,"load",a.onLd,1)}if(a.ue_pr&&(a.ue_pr==3||a.ue_pr==4)){a.ue._uep()}})(ue_csm);/*]]>*/</script></div><noscript><imgheight="1"width="1"style='display:none;visibility:hidden;'src='http://shop.fruit.com/uedata/178-0385643-2431924?noscript&amp;id=1983VYSJSQ23ECVP5ED9'alt=""/></noscript></body></html><!DOCTYPEhtmlPUBLIC"-//W3C//DTDXHTML1.0Transitional//EN""http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><htmlxmlns="http://www.w3.org/1999/xhtml"lang="en"xml:lang="en"><head><scripttype="text/javascript">/*<![CDATA[*/varue_t0=ue_t0||+newDate();/*]]>*/</script><scripttype="text/javascript">/*<![CDATA[*/varue_id='1983VYSJSQ23ECVP5ED9',ue_csm=window,ue_url='http://shop.fruit.com/uedata/178-0385643-2431924',ue_pr='0',ue_md='/mn/uedata/?tp=',ue_navtiming=0,ue_mid='A3H9RROGOL2TZC',ue_sid='178-0385643-2431924',ue_furl='fls-na.amazon.com',ue_tags=0,ue_ifr=0;<script>/*<![CDATA[*/if(typeofws==="undefined"){varws={};};ws.WebstoreOverallPageProperties={'clientSidePreLoadingEnabled':"true"};/*]]>*/</script><divid='be'style="display:none;"><formname='ue_backdetect'action="get"><inputtype="hidden"name='ue_back'value='1'/></form><scripttype='text/javascript'>/*<![CDATA[*/(function(a){a._uess=function(){vard="";if(screen&&screen.width&&screen.height){d+="&sw="+screen.width+"&sh="+screen.height}varc=function(g){varf=document.documentElement["client"+g];returndocument.compatMode==="CSS1Compat"&&f||document.body["client"+g]||f};vare=c("Width"),b=c("Height");if(e&&b){d+="&vw="+e+"&vh="+b}returnd}})(ue_csm);(function(a){if(a.uet){a.uet("be")}if(a.onLdEnd){if(window.addEventListener){window.addEventListener("load",a.onLdEnd,false)}else{if(window.attachEvent){window.attachEvent("onload",a.onLdEnd)}}}if(a.ueh){a.ueh(0,window,"load",a.onLd,1)}if(a.ue_pr&&(a.ue_pr==3||a.ue_pr==4)){a.ue._uep()}})(ue_csm);/*]]>*/</script></div><noscript><imgheight="1"width="1"style='display:none;visibility:hidden;'src='http://shop.fruit.com/uedata/178-0385643-2431924?noscript&amp;id=1983VYSJSQ23ECVP5ED9'alt=""/></noscript></body></html><!DOCTYPEhtmlPUBLIC"-//W3C//DTDXHTML1.0Transitional//EN""http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><htmlxmlns="http://www.w3.org/1999/xhtml"lang="en"xml:lang="en"><head><scripttype="text/javascript">/*<![CDATA[*/varue_t0=ue_t0||+newDate();/*]]>*/</script><scripttype="text/javascript">/*<![CDATA[*/varue_id='1983VYSJSQ23ECVP5ED9',ue_csm=window,ue_url='http://shop.fruit.com/uedata/178-0385643-2431924',ue_pr='0',ue_md='/mn/uedata/?tp=',ue_navtiming=0,ue_mid='A3H9RROGOL2TZC',ue_sid='178-0385643-2431924',ue_furl='fls-na.amazon.com',ue_tags=0,ue_ifr=0;<script>/*<![CDATA[*/if(typeofws==="undefined"){varws={};};ws.WebstoreOverallPageProperties={'clientSidePreLoadingEnabled':"true"};/*]]>*/</script><divid='be'style="display:none;"><formname='ue_backdetect'action="get"><inputtype="hidden"name='ue_back'value='1'/></form><scripttype='text/javascript'>/*<![CDATA[*/(function(a){a._uess=function(){vard="";if(screen&&screen.width&&screen.height){d+="&sw="+screen.width+"&sh="+screen.height}varc=function(g){varf=document.documentElement["client"+g];returndocument.compatMode==="CSS1Compat"&&f||document.body["client"+g]||f};vare=c("Width"),b=c("Height");if(e&&b){d+="&vw="+e+"&vh="+b}returnd}})(ue_csm);(function(a){if(a.uet){a.uet("be")}if(a.onLdEnd){if(window.addEventListener){window.addEventListener("load",a.onLdEnd,false)}else{if(window.attachEvent){window.attachEvent("onload",a.onLdEnd)}}}if(a.ueh){a.ueh(0,window,"load",a.onLd,1)}if(a.ue_pr&&(a.ue_pr==3||a.ue_pr==4)){a.ue._uep()}})(ue_csm);/*]]>*/</script></div><noscript><imgheight="1"width="1"style='display:none;visibility:hidden;'src='http://shop.fruit.com/uedata/178-0385643-2431924?noscript&amp;id=1983VYSJSQ23ECVP5ED9'alt=""/></noscript></body></html><!DOCTYPEhtmlPUBLIC"-//W3C//DTDXHTML1.0Transitional//EN""http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><htmlxmlns="http://www.w3.org/1999/xhtml"lang="en"xml:lang="en"><head><scripttype="text/javascript">/*<![CDATA[*/varue_t0=ue_t0||+newDate();/*]]>*/</script><scripttype="text/javascript">/*<![CDATA[*/varue_id='1983VYSJSQ23ECVP5ED9',ue_csm=window,ue_url='http://shop.fruit.com/uedata/178-0385643-2431924',ue_pr='0',ue_md='/mn/uedata/?tp=',ue_navtiming=0,ue_mid='A3H9RROGOL2TZC',ue_sid='178-0385643-2431924',ue_furl='fls-na.amazon.com',ue_tags=0,ue_ifr=0;<script>/*<![CDATA[*/if(typeofws==="undefined"){varws={};};ws.WebstoreOverallPageProperties={'clientSidePreLoadingEnabled':"true"};/*]]>*/</script><divid='be'style="display:none;"><formname='ue_backdetect'action="get"><inputtype="hidden"name='ue_back'value='1'/></form><scripttype='text/javascript'>/*<![CDATA[*/(function(a){a._uess=function(){vard="";if(screen&&screen.width&&screen.height){d+="&sw="+screen.width+"&sh="+screen.height}varc=function(g){varf=document.documentElement["client"+g];returndocument.compatMode==="CSS1Compat"&&f||document.body["client"+g]||f};vare=c("Width"),b=c("Height");if(e&&b){d+="&vw="+e+"&vh="+b}returnd}})(ue_csm);(function(a){if(a.uet){a.uet("be")}if(a.onLdEnd){if(window.addEventListener){window.addEventListener("load",a.onLdEnd,false)}else{if(window.attachEvent){window.attachEvent("onload",a.onLdEnd)}}}if(a.ueh){a.ueh(0,window,"load",a.onLd,1)}if(a.ue_pr&&(a.ue_pr==3||a.ue_pr==4)){a.ue._uep()}})(ue_csm);/*]]>*/</script></div><noscript><imgheight="1"width="1"style='display:none;visibility:hidden;'src='http://shop.fruit.com/uedata/178-0385643-2431924?noscript&amp;id=1983VYSJSQ23ECVP5ED9'alt=""/></noscript></body></html><!DOCTYPEhtmlPUBLIC"-//W3C//DTDXHTML1.0Transitional//EN""http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><htmlxmlns="http://www.w3.org/1999/xhtml"lang="en"xml:lang="en"><head><scripttype="text/javascript">/*<![CDATA[*/varue_t0=ue_t0||+newDate();/*]]>*/</script><scripttype="text/javascript">/*<![CDATA[*/varue_id='1983VYSJSQ23ECVP5ED9',ue_csm=window,ue_url='http://shop.fruit.com/uedata/178-0385643-2431924',ue_pr='0',ue_md='/mn/uedata/?tp=',ue_navtiming=0,ue_mid='A3H9RROGOL2TZC',ue_sid='178-0385643-2431924',ue_furl='fls-na.amazon.com',ue_tags=0,ue_ifr=0;<script>/*<![CDATA[*/if(typeofws==="undefined"){varws={};};ws.WebstoreOverallPageProperties={'clientSidePreLoadingEnabled':"true"};/*]]>*/</script><divid='be'style="display:none;"><formname='ue_backdetect'action="get"><inputtype="hidden"name='ue_back'value='1'/></form><scripttype='text/javascript'>/*<![CDATA[*/(function(a){a._uess=function(){vard="";if(screen&&screen.width&&screen.height){d+="&sw="+screen.width+"&sh="+screen.height}varc=function(g){varf=document.documentElement["client"+g];returndocument.compatMode==="CSS1Compat"&&f||document.body["client"+g]||f};vare=c("Width"),b=c("Height");if(e&&b){d+="&vw="+e+"&vh="+b}returnd}})(ue_csm);(function(a){if(a.uet){a.uet("be")}if(a.onLdEnd){if(window.addEventListener){window.addEventListener("load",a.onLdEnd,false)}else{if(window.attachEvent){window.attachEvent("onload",a.onLdEnd)}}}if(a.ueh){a.ueh(0,window,"load",a.onLd,1)}if(a.ue_pr&&(a.ue_pr==3||a.ue_pr==4)){a.ue._uep()}})(ue_csm);/*]]>*/</script></div><noscript><imgheight="1"width="1"style='display:none;visibility:hidden;'src='http://shop.fruit.com/uedata/178-0385643-2431924?noscript&amp;id=1983VYSJSQ23ECVP5ED9'alt=""/></noscript></body></html><!DOCTYPEhtmlPUBLIC"-//W3C//DTDXHTML1.0Transitional//EN""http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><htmlxmlns="http://www.w3.org/1999/xhtml"lang="en"xml:lang="en"><head><scripttype="text/javascript">/*<![CDATA[*/varue_t0=ue_t0||+newDate();/*]]>*/</script><scripttype="text/javascript">/*<![CDATA[*/varue_id='1983VYSJSQ23ECVP5ED9',ue_csm=window,ue_url='http://shop.fruit.com/uedata/178-0385643-2431924',ue_pr='0',ue_md='/mn/uedata/?tp=',ue_navtiming=0,ue_mid='A3H9RROGOL2TZC',ue_sid='178-0385643-2431924',ue_furl='fls-na.amazon.com',ue_tags=0,ue_ifr=0;<script>/*<![CDATA[*/if(typeofws==="undefined"){varws={};};ws.WebstoreOverallPageProperties={'clientSidePreLoadingEnabled':"true"};/*]]>*/</script><divid='be'style="display:none;"><formname='ue_backdetect'action="get"><inputtype="hidden"name='ue_back'value='1'/></form><scripttype='text/javascript'>/*<![CDATA[*/(function(a){a._uess=function(){vard="";if(screen&&screen.width&&screen.height){d+="&sw="+screen.width+"&sh="+screen.height}varc=function(g){varf=document.documentElement["client"+g];returndocument.compatMode==="CSS1Compat"&&f||document.body["client"+g]||f};vare=c("Width"),b=c("Height");if(e&&b){d+="&vw="+e+"&vh="+b}returnd}})(ue_csm);(function(a){if(a.uet){a.uet("be")}if(a.onLdEnd){if(window.addEventListener){window.addEventListener("load",a.onLdEnd,false)}else{if(window.attachEvent){window.attachEvent("onload",a.onLdEnd)}}}if(a.ueh){a.ueh(0,window,"load",a.onLd,1)}if(a.ue_pr&&(a.ue_pr==3||a.ue_pr==4)){a.ue._uep()}})(ue_csm);/*]]>*/</script></div><noscript><imgheight="1"width="1"style='display:none;visibility:hidden;'src='http://shop.fruit.com/uedata/178-0385643-2431924?noscript&amp;id=1983VYSJSQ23ECVP5ED9'alt=""/></noscript></body></html><!DOCTYPEhtmlPUBLIC"-//W3C//DTDXHTML1.0Transitional//EN""http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><htmlxmlns="http://www.w3.org/1999/xhtml"lang="en"xml:lang="en"><head><scripttype="text/javascript">/*<![CDATA[*/varue_t0=ue_t0||+newDate();/*]]>*/</script><scripttype="text/javascript">/*<![CDATA[*/varue_id='1983VYSJSQ23ECVP5ED9',ue_csm=window,ue_url='http://shop.fruit.com/uedata/178-0385643-2431924',ue_pr='0',ue_md='/mn/uedata/?tp=',ue_navtiming=0,ue_mid='A3H9RROGOL2TZC',ue_sid='178-0385643-2431924',ue_furl='fls-na.amazon.com',ue_tags=0,ue_ifr=0;<script>/*<![CDATA[*/if(typeofws==="undefined"){varws={};};ws.WebstoreOverallPageProperties={'clientSidePreLoadingEnabled':"true"};/*]]>*/</script><divid='be'style="display:none;"><formname='ue_backdetect'action="get"><inputtype="hidden"name='ue_back'value='1'/></form><scripttype='text/javascript'>/*<![CDATA[*/(function(a){a._uess=function(){vard="";if(screen&&screen.width&&screen.height){d+="&sw="+screen.width+"&sh="+screen.height}varc=function(g){varf=document.documentElement["client"+g];returndocument.compatMode==="CSS1Compat"&&f||document.body["client"+g]||f};vare=c("Width"),b=c("Height");if(e&&b){d+="&vw="+e+"&vh="+b}returnd}})(ue_csm);(function(a){if(a.uet){a.uet("be")}if(a.onLdEnd){if(window.addEventListener){window.addEventListener("load",a.onLdEnd,false)}else{if(window.attachEvent){window.attachEvent("onload",a.onLdEnd)}}}if(a.ueh){a.ueh(0,window,"load",a.onLd,1)}if(a.ue_pr&&(a.ue_pr==3||a.ue_pr==4)){a.ue._uep()}})(ue_csm);/*]]>*/</script></div><noscript><imgheight="1"width="1"style='display:none;visibility:hidden;'src='http://shop.fruit.com/uedata/178-0385643-2431924?noscript&amp;id=1983VYSJSQ23ECVP5ED9'alt=""/></noscript></body></html><!DOCTYPEhtmlPUBLIC"-//W3C//DTDXHTML1.0Transitional//EN""http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><htmlxmlns="http://www.w3.org/1999/xhtml"lang="en"xml:lang="en"><head><scripttype="text/javascript">/*<![CDATA[*/varue_t0=ue_t0||+newDate();/*]]>*/</script><scripttype="text/javascript">/*<![CDATA[*/varue_id='1983VYSJSQ23ECVP5ED9',ue_csm=window,ue_url='http://shop.fruit.com/uedata/178-0385643-2431924',ue_pr='0',ue_md='/mn/uedata/?tp=',ue_navtiming=0,ue_mid='A3H9RROGOL2TZC',ue_sid='178-0385643-2431924',ue_furl='fls-na.amazon.com',ue_tags=0,ue_ifr=0;<script>/*<![CDATA[*/if(typeofws==="undefined"){varws={};};ws.WebstoreOverallPageProperties={'clientSidePreLoadingEnabled':"true"};/*]]>*/</script><divid='be'style="display:none;"><formname='ue_backdetect'action="get"><inputtype="hidden"name='ue_back'value='1'/></form><scripttype='text/javascript'>/*<![CDATA[*/(function(a){a._uess=function(){vard="";if(screen&&screen.width&&screen.height){d+="&sw="+screen.width+"&sh="+screen.height}varc=function(g){varf=document.documentElement["client"+g];returndocument.compatMode==="CSS1Compat"&&f||document.body["client"+g]||f};vare=c("Width"),b=c("Height");if(e&&b){d+="&vw="+e+"&vh="+b}returnd}})(ue_csm);(function(a){if(a.uet){a.uet("be")}if(a.onLdEnd){if(window.addEventListener){window.addEventListener("load",a.onLdEnd,false)}else{if(window.attachEvent){window.attachEvent("onload",a.onLdEnd)}}}if(a.ueh){a.ueh(0,window,"load",a.onLd,1)}if(a.ue_pr&&(a.ue_pr==3||a.ue_pr==4)){a.ue._uep()}})(ue_csm);/*]]>*/</script></div><noscript><imgheight="1"width="1"style='display:none;visibility:hidden;'src='http://shop.fruit.com/uedata/178-0385643-2431924?noscript&amp;id=1983VYSJSQ23ECVP5ED9'alt=""/></noscript></body></html><!DOCTYPEhtmlPUBLIC"-//W3C//DTDXHTML1.0Transitional//EN""http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><htmlxmlns="http://www.w3.org/1999/xhtml"lang="en"xml:lang="en"><head><scripttype="text/javascript">/*<![CDATA[*/varue_t0=ue_t0||+newDate();/*]]>*/</script><scripttype="text/javascript">/*<![CDATA[*/varue_id='1983VYSJSQ23ECVP5ED9',ue_csm=window,ue_url='http://shop.fruit.com/uedata/178-0385643-2431924',ue_pr='0',ue_md='/mn/uedata/?tp=',ue_navtiming=0,ue_mid='A3H9RROGOL2TZC',ue_sid='178-0385643-2431924',ue_furl='fls-na.amazon.com',ue_tags=0,ue_ifr=0;<script>/*<![CDATA[*/if(typeofws==="undefined"){varws={};};ws.WebstoreOverallPageProperties={'clientSidePreLoadingEnabled':"true"};/*]]>*/</script><divid='be'style="display:none;"><formname='ue_backdetect'action="get"><inputtype="hidden"name='ue_back'value='1'/></form><scripttype='text/javascript'>/*<![CDATA[*/(function(a){a._uess=function(){vard="";if(screen&&screen.width&&screen.height){d+="&sw="+screen.width+"&sh="+screen.height}varc=function(g){varf=document.documentElement["client"+g];returndocument.compatMode==="CSS1Compat"&&f||document.body["client"+g]||f};vare=c("Width"),b=c("Height");if(e&&b){d+="&vw="+e+"&vh="+b}returnd}})(ue_csm);(function(a){if(a.uet){a.uet("be")}if(a.onLdEnd){if(window.addEventListener){window.addEventListener("load",a.onLdEnd,false)}else{if(window.attachEvent){window.attachEvent("onload",a.onLdEnd)}}}if(a.ueh){a.ueh(0,window,"load",a.onLd,1)}if(a.ue_pr&&(a.ue_pr==3||a.ue_pr==4)){a.ue._uep()}})(ue_csm);/*]]>*/</script></div><noscript><imgheight="1"width="1"style='display:none;visibility:hidden;'src='http://shop.fruit.com/uedata/178-0385643-2431924?noscript&amp;id=1983VYSJSQ23ECVP5ED9'alt=""/></noscript></body></html><!DOCTYPEhtmlPUBLIC"-//W3C//DTDXHTML1.0Transitional//EN""http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><htmlxmlns="http://www.w3.org/1999/xhtml"lang="en"xml:lang="en"><head><scripttype="text/javascript">/*<![CDATA[*/varue_t0=ue_t0||+newDate();/*]]>*/</script><scripttype="text/javascript">/*<![CDATA[*/varue_id='1983VYSJSQ23ECVP5ED9',ue_csm=window,ue_url='http://shop.fruit.com/uedata/178-0385643-2431924',ue_pr='0',ue_md='/mn/uedata/?tp=',ue_navtiming=0,ue_mid='A3H9RROGOL2TZC',ue_sid='178-0385643-2431924',ue_furl='fls-na.amazon.com',ue_tags=0,ue_ifr=0;<script>/*<![CDATA[*/if(typeofws==="undefined"){varws={};};ws.WebstoreOverallPageProperties={'clientSidePreLoadingEnabled':"true"};/*]]>*/</script><divid='be'style="display:none;"><formname='ue_backdetect'action="get"><inputtype="hidden"name='ue_back'value='1'/></form><scripttype='text/javascript'>/*<![CDATA[*/(function(a){a._uess=function(){vard="";if(screen&&screen.width&&screen.height){d+="&sw="+screen.width+"&sh="+screen.height}varc=function(g){varf=document.documentElement["client"+g];returndocument.compatMode==="CSS1Compat"&&f||document.body["client"+g]||f};vare=c("Width"),b=c("Height");if(e&&b){d+="&vw="+e+"&vh="+b}returnd}})(ue_csm);(function(a){if(a.uet){a.uet("be")}if(a.onLdEnd){if(window.addEventListener){window.addEventListener("load",a.onLdEnd,false)}else{if(window.attachEvent){window.attachEvent("onload",a.onLdEnd)}}}if(a.ueh){a.ueh(0,window,"load",a.onLd,1)}if(a.ue_pr&&(a.ue_pr==3||a.ue_pr==4)){a.ue._uep()}})(ue_csm);/*]]>*/</script></div><noscript><imgheight="1"width="1"style='display:none;visibility:hidden;'src='http://shop.fruit.com/uedata/178-0385643-2431924?noscript&amp;id=1983VYSJSQ23ECVP5ED9'alt=""/></noscript></body></html><!DOCTYPEhtmlPUBLIC"-//W3C//DTDXHTML1.0Transitional//EN""http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><htmlxmlns="http://www.w3.org/1999/xhtml"lang="en"xml:lang="en"><head><scripttype="text/javascript">/*<![CDATA[*/varue_t0=ue_t0||+newDate();/*]]>*/</script><scripttype="text/javascript">/*<![CDATA[*/varue_id='1983VYSJSQ23ECVP5ED9',ue_csm=window,ue_url='http://shop.fruit.com/uedata/178-0385643-2431924',ue_pr='0',ue_md='/mn/uedata/?tp=',ue_navtiming=0,ue_mid='A3H9RROGOL2TZC',ue_sid='178-0385643-2431924',ue_furl='fls-na.amazon.com',ue_tags=0,ue_ifr=0;<script>/*<![CDATA[*/if(typeofws==="undefined"){varws={};};ws.WebstoreOverallPageProperties={'clientSidePreLoadingEnabled':"true"};/*]]>*/</script><divid='be'style="display:none;"><formname='ue_backdetect'action="get"><inputtype="hidden"name='ue_back'value='1'/></form><scripttype='text/javascript'>/*<![CDATA[*/(function(a){a._uess=function(){vard="";if(screen&&screen.width&&screen.height){d+="&sw="+screen.width+"&sh="+screen.height}varc=function(g){varf=document.documentElement["client"+g];returndocument.compatMode==="CSS1Compat"&&f||document.body["client"+g]||f};vare=c("Width"),b=c("Height");if(e&&b){d+="&vw="+e+"&vh="+b}returnd}})(ue_csm);(function(a){if(a.uet){a.uet("be")}if(a.onLdEnd){if(window.addEventListener){window.addEventListener("load",a.onLdEnd,false)}else{if(window.attachEvent){window.attachEvent("onload",a.onLdEnd)}}}if(a.ueh){a.ueh(0,window,"load",a.onLd,1)}if(a.ue_pr&&(a.ue_pr==3||a.ue_pr==4)){a.ue._uep()}})(ue_csm);/*]]>*/</script></div><noscript><imgheight="1"width="1"style='display:none;visibility:hidden;'src='http://shop.fruit.com/uedata/178-0385643-2431924?noscript&amp;id=1983VYSJSQ23ECVP5ED9'alt=""/></noscript></body></html><!DOCTYPEhtmlPUBLIC"-//W3C//DTDXHTML1.0Transitional//EN""http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><htmlxmlns="http://www.w3.org/1999/xhtml"lang="en"xml:lang="en"><head><scripttype="text/javascript">/*<![CDATA[*/varue_t0=ue_t0||+newDate();/*]]>*/</script><scripttype="text/javascript">/*<![CDATA[*/varue_id='1983VYSJSQ23ECVP5ED9',ue_csm=window,ue_url='http://shop.fruit.com/uedata/178-0385643-2431924',ue_pr='0',ue_md='/mn/uedata/?tp=',ue_navtiming=0,ue_mid='A3H9RROGOL2TZC',ue_sid='178-0385643-2431924',ue_furl='fls-na.amazon.com',ue_tags=0,ue_ifr=0;<script>/*<![CDATA[*/if(typeofws==="undefined"){varws={};};ws.WebstoreOverallPageProperties={'clientSidePreLoadingEnabled':"true"};/*]]>*/</script><divid='be'style="display:none;"><formname='ue_backdetect'action="get"><inputtype="hidden"name='ue_back'value='1'/></form><scripttype='text/javascript'>/*<![CDATA[*/(function(a){a._uess=function(){vard="";if(screen&&screen.width&&screen.height){d+="&sw="+screen.width+"&sh="+screen.height}varc=function(g){varf=document.documentElement["client"+g];returndocument.compatMode==="CSS1Compat"&&f||document.body["client"+g]||f};vare=c("Width"),b=c("Height");if(e&&b){d+="&vw="+e+"&vh="+b}returnd}})(ue_csm);(function(a){if(a.uet){a.uet("be")}if(a.onLdEnd){if(window.addEventListener){window.addEventListener("load",a.onLdEnd,false)}else{if(window.attachEvent){window.attachEvent("onload",a.onLdEnd)}}}if(a.ueh){a.ueh(0,window,"load",a.onLd,1)}if(a.ue_pr&&(a.ue_pr==3||a.ue_pr==4)){a.ue._uep()}})(ue_csm);/*]]>*/</script></div><noscript><imgheight="1"width="1"style='display:none;visibility:hidden;'src='http://shop.fruit.com/uedata/178-0385643-2431924?noscript&amp;id=1983VYSJSQ23ECVP5ED9'alt=""/></noscript></body></html><!DOCTYPEhtmlPUBLIC"-//W3C//DTDXHTML1.0Transitional//EN""http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><htmlxmlns="http://www.w3.org/1999/xhtml"lang="en"xml:lang="en"><head><scripttype="text/javascript">/*<![CDATA[*/varue_t0=ue_t0||+newDate();/*]]>*/</script><scripttype="text/javascript">/*<![CDATA[*/varue_id='1983VYSJSQ23ECVP5ED9',ue_csm=window,ue_url='http://shop.fruit.com/uedata/178-0385643-2431924',ue_pr='0',ue_md='/mn/uedata/?tp=',ue_navtiming=0,ue_mid='A3H9RROGOL2TZC',ue_sid='178-0385643-2431924',ue_furl='fls-na.amazon.com',ue_tags=0,ue_ifr=0;<script>/*<![CDATA[*/if(typeofws==="undefined"){varws={};};ws.WebstoreOverallPageProperties={'clientSidePreLoadingEnabled':"true"};/*]]>*/</script><divid='be'style="display:none;"><formname='ue_backdetect'action="get"><inputtype="hidden"name='ue_back'value='1'/></form><scripttype='text/javascript'>/*<![CDATA[*/(function(a){a._uess=function(){vard="";if(screen&&screen.width&&screen.height){d+="&sw="+screen.width+"&sh="+screen.height}varc=function(g){varf=document.documentElement["client"+g];returndocument.compatMode==="CSS1Compat"&&f||document.body["client"+g]||f};vare=c("Width"),b=c("Height");if(e&&b){d+="&vw="+e+"&vh="+b}returnd}})(ue_csm);(function(a){if(a.uet){a.uet("be")}if(a.onLdEnd){if(window.addEventListener){window.addEventListener("load",a.onLdEnd,false)}else{if(window.attachEvent){window.attachEvent("onload",a.onLdEnd)}}}if(a.ueh){a.ueh(0,window,"load",a.onLd,1)}if(a.ue_pr&&(a.ue_pr==3||a.ue_pr==4)){a.ue._uep()}})(ue_csm);/*]]>*/</script></div><noscript><imgheight="1"width="1"style='display:none;visibility:hidden;'src='http://shop.fruit.com/uedata/178-0385643-2431924?noscript&amp;id=1983VYSJSQ23ECVP5ED9'alt=""/></noscript></body></html><!DOCTYPEhtmlPUBLIC"-//W3C//DTDXHTML1.0Transitional//EN""http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><htmlxmlns="http://www.w3.org/1999/xhtml"lang="en"xml:lang="en"><head><scripttype="text/javascript">/*<![CDATA[*/varue_t0=ue_t0||+newDate();/*]]>*/</script><scripttype="text/javascript">/*<![CDATA[*/varue_id='1983VYSJSQ23ECVP5ED9',ue_csm=window,ue_url='http://shop.fruit.com/uedata/178-0385643-2431924',ue_pr='0',ue_md='/mn/uedata/?tp=',ue_navtiming=0,ue_mid='A3H9RROGOL2TZC',ue_sid='178-0385643-2431924',ue_furl='fls-na.amazon.com',ue_tags=0,ue_ifr=0;<script>/*<![CDATA[*/if(typeofws==="undefined"){varws={};};ws.WebstoreOverallPageProperties={'clientSidePreLoadingEnabled':"true"};/*]]>*/</script><divid='be'style="display:none;"><formname='ue_backdetect'action="get"><inputtype="hidden"name='ue_back'value='1'/></form><scripttype='text/javascript'>/*<![CDATA[*/(function(a){a._uess=function(){vard="";if(screen&&screen.width&&screen.height){d+="&sw="+screen.width+"&sh="+screen.height}varc=function(g){varf=document.documentElement["client"+g];returndocument.compatMode==="CSS1Compat"&&f||document.body["client"+g]||f};vare=c("Width"),b=c("Height");if(e&&b){d+="&vw="+e+"&vh="+b}returnd}})(ue_csm);(function(a){if(a.uet){a.uet("be")}if(a.onLdEnd){if(window.addEventListener){window.addEventListener("load",a.onLdEnd,false)}else{if(window.attachEvent){window.attachEvent("onload",a.onLdEnd)}}}if(a.ueh){a.ueh(0,window,"load",a.onLd,1)}if(a.ue_pr&&(a.ue_pr==3||a.ue_pr==4)){a.ue._uep()}})(ue_csm);/*]]>*/</script></div><noscript><imgheight="1"width="1"style='display:none;visibility:hidden;'src='http://shop.fruit.com/uedata/178-0385643-2431924?noscript&amp;id=1983VYSJSQ23ECVP5ED9'alt=""/></noscript></body></html><!DOCTYPEhtmlPUBLIC"-//W3C//DTDXHTML1.0Transitional//EN""http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><htmlxmlns="http://www.w3.org/1999/xhtml"lang="en"xml:lang="en"><head><scripttype="text/javascript">/*<![CDATA[*/varue_t0=ue_t0||+newDate();/*]]>*/</script><scripttype="text/javascript">/*<![CDATA[*/varue_id='1983VYSJSQ23ECVP5ED9',ue_csm=window,ue_url='http://shop.fruit.com/uedata/178-0385643-2431924',ue_pr='0',ue_md='/mn/uedata/?tp=',ue_navtiming=0,ue_mid='A3H9RROGOL2TZC',ue_sid='178-0385643-2431924',ue_furl='fls-na.amazon.com',ue_tags=0,ue_ifr=0;<script>/*<![CDATA[*/if(typeofws==="undefined"){varws={};};ws.WebstoreOverallPageProperties={'clientSidePreLoadingEnabled':"true"};/*]]>*/</script><divid='be'style="display:none;"><formname='ue_backdetect'action="get"><inputtype="hidden"name='ue_back'value='1'/></form><scripttype='text/javascript'>/*<![CDATA[*/(function(a){a._uess=function(){vard="";if(screen&&screen.width&&screen.height){d+="&sw="+screen.width+"&sh="+screen.height}varc=function(g){varf=document.documentElement["client"+g];returndocument.compatMode==="CSS1Compat"&&f||document.body["client"+g]||f};vare=c("Width"),b=c("Height");if(e&&b){d+="&vw="+e+"&vh="+b}returnd}})(ue_csm);(function(a){if(a.uet){a.uet("be")}if(a.onLdEnd){if(window.addEventListener){window.addEventListener("load",a.onLdEnd,false)}else{if(window.attachEvent){window.attachEvent("onload",a.onLdEnd)}}}if(a.ueh){a.ueh(0,window,"load",a.onLd,1)}if(a.ue_pr&&(a.ue_pr==3||a.ue_pr==4)){a.ue._uep()}})(ue_csm);/*]]>*/</script></div><noscript><imgheight="1"width="1"style='display:none;visibility:hidden;'src='http://shop.fruit.com/uedata/178-0385643-2431924?noscript&amp;id=1983VYSJSQ23ECVP5ED9'alt=""/></noscript></body></html><!DOCTYPEhtmlPUBLIC"-//W3C//DTDXHTML1.0Transitional//EN""http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><htmlxmlns="http://www.w3.org/1999/xhtml"lang="en"xml:lang="en"><head><scripttype="text/javascript">/*<![CDATA[*/varue_t0=ue_t0||+newDate();/*]]>*/</script><scripttype="text/javascript">/*<![CDATA[*/varue_id='1983VYSJSQ23ECVP5ED9',ue_csm=window,ue_url='http://shop.fruit.com/uedata/178-0385643-2431924',ue_pr='0',ue_md='/mn/uedata/?tp=',ue_navtiming=0,ue_mid='A3H9RROGOL2TZC',ue_sid='178-0385643-2431924',ue_furl='fls-na.amazon.com',ue_tags=0,ue_ifr=0;<script>/*<![CDATA[*/if(typeofws==="undefined"){varws={};};ws.WebstoreOverallPageProperties={'clientSidePreLoadingEnabled':"true"};/*]]>*/</script><divid='be'style="display:none;"><formname='ue_backdetect'action="get"><inputtype="hidden"name='ue_back'value='1'/></form><scripttype='text/javascript'>/*<![CDATA[*/(function(a){a._uess=function(){vard="";if(screen&&screen.width&&screen.height){d+="&sw="+screen.width+"&sh="+screen.height}varc=function(g){varf=document.documentElement["client"+g];returndocument.compatMode==="CSS1Compat"&&f||document.body["client"+g]||f};vare=c("Width"),b=c("Height");if(e&&b){d+="&vw="+e+"&vh="+b}returnd}})(ue_csm);(function(a){if(a.uet){a.uet("be")}if(a.onLdEnd){if(window.addEventListener){window.addEventListener("load",a.onLdEnd,false)}else{if(window.attachEvent){window.attachEvent("onload",a.onLdEnd)}}}if(a.ueh){a.ueh(0,window,"load",a.onLd,1)}if(a.ue_pr&&(a.ue_pr==3||a.ue_pr==4)){a.ue._uep()}})(ue_csm);/*]]>*/</script></div><noscript><imgheight="1"width="1"style='display:none;visibility:hidden;'src='http://shop.fruit.com/uedata/178-0385643-2431924?noscript&amp;id=1983VYSJSQ23ECVP5ED9'alt=""/></noscript></body></html><!DOCTYPEhtmlPUBLIC"-//W3C//DTDXHTML1.0Transitional//EN""http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><htmlxmlns="http://www.w3.org/1999/xhtml"lang="en"xml:lang="en"><head><scripttype="text/javascript">/*<![CDATA[*/varue_t0=ue_t0||+newDate();/*]]>*/</script><scripttype="text/javascript">/*<![CDATA[*/varue_id='1983VYSJSQ23ECVP5ED9',ue_csm=window,ue_url='http://shop.fruit.com/uedata/178-0385643-2431924',ue_pr='0',ue_md='/mn/uedata/?tp=',ue_navtiming=0,ue_mid='A3H9RROGOL2TZC',ue_sid='178-0385643-2431924',ue_furl='fls-na.amazon.com',ue_tags=0,ue_ifr=0;<script>/*<![CDATA[*/if(typeofws==="undefined"){varws={};};ws.WebstoreOverallPageProperties={'clientSidePreLoadingEnabled':"true"};/*]]>*/</script><divid='be'style="display:none;"><formname='ue_backdetect'action="get"><inputtype="hidden"name='ue_back'value='1'/></form><scripttype='text/javascript'>/*<![CDATA[*/(function(a){a._uess=function(){vard="";if(screen&&screen.width&&screen.height){d+="&sw="+screen.width+"&sh="+screen.height}varc=function(g){varf=document.documentElement["client"+g];returndocument.compatMode==="CSS1Compat"&&f||document.body["client"+g]||f};vare=c("Width"),b=c("Height");if(e&&b){d+="&vw="+e+"&vh="+b}returnd}})(ue_csm);(function(a){if(a.uet){a.uet("be")}if(a.onLdEnd){if(window.addEventListener){window.addEventListener("load",a.onLdEnd,false)}else{if(window.attachEvent){window.attachEvent("onload",a.onLdEnd)}}}if(a.ueh){a.ueh(0,window,"load",a.onLd,1)}if(a.ue_pr&&(a.ue_pr==3||a.ue_pr==4)){a.ue._uep()}})(ue_csm);/*]]>*/</script></div><noscript><imgheight="1"width="1"style='display:none;visibility:hidden;'src='http://shop.fruit.com/uedata/178-0385643-2431924?noscript&amp;id=1983VYSJSQ23ECVP5ED9'alt=""/></noscript></body></html><!DOCTYPEhtmlPUBLIC"-//W3C//DTDXHTML1.0Transitional//EN""http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><htmlxmlns="http://www.w3.org/1999/xhtml"lang="en"xml:lang="en"><head><scripttype="text/javascript">/*<![CDATA[*/varue_t0=ue_t0||+newDate();/*]]>*/</script><scripttype="text/javascript">/*<![CDATA[*/varue_id='1983VYSJSQ23ECVP5ED9',ue_csm=window,ue_url='http://shop.fruit.com/uedata/178-0385643-2431924',ue_pr='0',ue_md='/mn/uedata/?tp=',ue_navtiming=0,ue_mid='A3H9RROGOL2TZC',ue_sid='178-0385643-2431924',ue_furl='fls-na.amazon.com',ue_tags=0,ue_ifr=0;<script>/*<![CDATA[*/if(typeofws==="undefined"){varws={};};ws.WebstoreOverallPageProperties={'clientSidePreLoadingEnabled':"true"};/*]]>*/</script><divid='be'style="display:none;"><formname='ue_backdetect'action="get"><inputtype="hidden"name='ue_back'value='1'/></form><scripttype='text/javascript'>/*<![CDATA[*/(function(a){a._uess=function(){vard="";if(screen&&screen.width&&screen.height){d+="&sw="+screen.width+"&sh="+screen.height}varc=function(g){varf=document.documentElement["client"+g];returndocument.compatMode==="CSS1Compat"&&f||document.body["client"+g]||f};vare=c("Width"),b=c("Height");if(e&&b){d+="&vw="+e+"&vh="+b}returnd}})(ue_csm);(function(a){if(a.uet){a.uet("be")}if(a.onLdEnd){if(window.addEventListener){window.addEventListener("load",a.onLdEnd,false)}else{if(window.attachEvent){window.attachEvent("onload",a.onLdEnd)}}}if(a.ueh){a.ueh(0,window,"load",a.onLd,1)}if(a.ue_pr&&(a.ue_pr==3||a.ue_pr==4)){a.ue._uep()}})(ue_csm);/*]]>*/</script></div><noscript><imgheight="1"width="1"style='display:none;visibility:hidden;'src='http://shop.fruit.com/uedata/178-0385643-2431924?noscript&amp;id=1983VYSJSQ23ECVP5ED9'alt=""/></noscript></body></html>
EOD
, "/"));

  }

  public function testCyberRobotsParseNoBlankLineAtEnd() {
    $this->assertFalse(AmberRobots::url_permitted(<<<EOD
User-agent: *
Disallow: /zittrain/
EOD
, "/zittrain/"));
}
  
  public function testCyberRobotsParseMultipleUserAgents() {
    $this->assertFalse(AmberRobots::url_permitted(<<<EOD
User-agent: *
Disallow: /zittrain/
Disallow: /cite/
Disallow: /opengovernment
Disallow: /blogs

# Spiders + MediaWiki = Bad
User-agent: *
Disallow: /blogsupport
Disallow: /brooklaw
Disallow: /cyberlaw2005/wiki
EOD
, "/zittrain/"));
}

  public function testAmberOnlyExcluded() {
    $this->assertFalse(AmberRobots::url_permitted(<<<EOD
User-agent: Amber
Disallow: /zittrain/
EOD
, "/zittrain/"));
}

  public function testAmberOnlyIncluded() {
    $this->assertTrue(AmberRobots::url_permitted(<<<EOD
User-agent: Amber
Allow: /zittrain/

User-agent: *
Disallow: /zittrain/
EOD
, "/zittrain/"));
}

  public function testCyberRobotsParseSomethingElseNotWorking() {
    $this->assertFalse(AmberRobots::url_permitted(<<<EOD
User-agent: *
Disallow: /zittrain/
Disallow: /cite/
Disallow: /opengovernment
Disallow: /blogs

# Spiders + MediaWiki = Bad
User-agent: *
Disallow: /blogsupport
Disallow: /brooklaw
Disallow: /cyberlaw2005/wiki
Disallow: /cyberone/wiki
Disallow: /h2owiki
Disallow: /iptheory
Disallow: /jamaicavoices
Disallow: /netizenship
Disallow: /ocs_global
Disallow: /ocs_intranet
Disallow: /oni-RAs
Disallow: /practical_lawyering
Disallow: /publicmediaforge
Disallow: /techwiki

#allow cyber-search to get there
User-agent: htdig/3.1.5 (wendy@eon.law.harvard.edu)
Disallow: 
Disallow: /msdoj/discuss/
EOD
, "/zittrain/"));
  }

  public function testAmberExcluded_11622() {
    $this->assertFalse(AmberRobots::url_permitted(<<<EOD
user-agent: Amber
disallow: /
EOD
, "/Settings"));
}

}

class AmberAssetHelperTest extends \PHPUnit_Framework_TestCase {

  public function provider() {
    return array(array(new AmberAssetHelper(new AmberStorage())));
  }

  /**
   * @dataProvider provider
   */
  public function testNullParse(AmberAssetHelper $a)
  {
    $result = $a->extract_assets("");
    $this->assertTrue(empty($result));
  }

  /**
   * @dataProvider provider
   */
  public function testBogusHTMLParse(AmberAssetHelper $a)
  {
    $result = $a->extract_assets("<SDFSD>SDFfalsdhf>la<sasdfasdfasdf<DFSFd");
    $this->assertTrue(empty($result));
  }

  /**
   * @dataProvider provider
   */
  public function testBaseRewrite(AmberAssetHelper $a)
  {
    $result = $a->rewrite_base_tag('<head><base href="http://tinyurl.com"/></head><body><img src="../peacock.png">And the band played on....</body>');
    $this->assertEquals($result,'<head></head><body><img src="../peacock.png">And the band played on....</body>');
  }

  /**
   * @dataProvider provider
   */
  public function testBaseRewrite2(AmberAssetHelper $a)
  {
    $result = $a->rewrite_base_tag('<head><base    href=\'http://tinyurl.com\'></head><body><img src="../peacock.png">And the band played on....</body>');
    $this->assertEquals($result,'<head></head><body><img src="../peacock.png">And the band played on....</body>');
  }

  /**
   * @dataProvider provider
   */
  public function testBaseRewrite3(AmberAssetHelper $a)
  {
    $result = $a->rewrite_base_tag('<head><base    href=\'http://tinyurl.com\' ></head><body><img src="../peacock.png">And the band played on....</body>');
    $this->assertEquals($result,'<head></head><body><img src="../peacock.png">And the band played on....</body>');
  }

  /**
   * @dataProvider provider
   */
  public function testOneImage(AmberAssetHelper $a)
  {
    $s = <<<EOF
<body><img src="../peacock.png">And the band played on....</body>
EOF;

    $result = $a->extract_assets($s);
    $this->assertTrue(count($result) == 1);
    $this->assertEquals($result[0],"../peacock.png");
  }

  /**
   * @dataProvider provider
   */
  public function testTwoImages(AmberAssetHelper $a)
  {
    $s = <<<EOF
<body><img src="../peacock.png">And the band played on....And the <img src="http://band.com/band.jpg"/> said to the
<a href="leader.html">leader</a>.</body>
EOF;

    $result = $a->extract_assets($s);
    $this->assertEquals(count($result),2);
    $this->assertEquals($result[0],"../peacock.png");
    $this->assertEquals($result[1],"http://band.com/band.jpg");
  }

  /**
   * @dataProvider provider
   */
  public function testStylesheet(AmberAssetHelper $a)
  {
    $s = <<<EOF
<head><link href="banana.css" rel="stylesheet" type="text.css"></head>
<body>And the band played on....And the BAND said to the
<a href="leader.html">leader</a>.</body>
EOF;

    $result = $a->extract_assets($s);
    $this->assertTrue(count($result) == 1);
    $this->assertTrue($result[0] == "banana.css");
  }

  /**
   * @dataProvider provider
   */
  public function testStylesheetMixedCase(AmberAssetHelper $a)
  {
    $s = <<<EOF
<head><link href="banana.css" rel="Stylesheet" type="text.css"></head>
<body>And the band played on....And the BAND said to the
<a href="leader.html">leader</a>.</body>
EOF;

    $result = $a->extract_assets($s);
    $this->assertTrue(count($result) == 1);
    $this->assertTrue($result[0] == "banana.css");
  }

  /**
   * @dataProvider provider
   */
  public function testJavascript(AmberAssetHelper $a)
  {
    $s = <<<EOF
<head><script src="banana.js" ></head>
<body>And the band played on....And the BAND said to the
<a href="leader.html">leader</a>.</body>
EOF;

    $result = $a->extract_assets($s);
    $this->assertTrue(count($result) == 1);
    $this->assertTrue($result[0] == "banana.js");
  }

  /**
   * @dataProvider provider
   *
   */
  public function testJavascriptInBody(AmberAssetHelper $a)
  {
    $s = <<<EOF
<body>
<script src="/ScriptResource.axd?d=AYP1ocISkwrIb0vXvAfvYbki5H-fzUh4bOWz3-sTZaJ96EB3jl_ZhEsh0s6EQJgauvkZeuKp9yVuc1Q47t3srJNgYCHQB7-vIS8A-wvNqnarUNjJbCiThM06cNjdOqghx7Caf4pRKLTDKWeKPGNVAmms_hc1&t=ffffffffdb0d1ac2" type="text/javascript"></script>
And the band played on....And the BAND said to the
<a href="leader.html">leader</a>.</body>
EOF;

    $result = $a->extract_assets($s);
    $this->assertTrue(count($result) == 1);
    $this->assertEquals($result[0],"/ScriptResource.axd?d=AYP1ocISkwrIb0vXvAfvYbki5H-fzUh4bOWz3-sTZaJ96EB3jl_ZhEsh0s6EQJgauvkZeuKp9yVuc1Q47t3srJNgYCHQB7-vIS8A-wvNqnarUNjJbCiThM06cNjdOqghx7Caf4pRKLTDKWeKPGNVAmms_hc1&t=ffffffffdb0d1ac2");
  }

  /**
   * @dataProvider provider
   */
  public function testAsyncJavascript(AmberAssetHelper $a)
  {
    $s = <<<EOF
<head><script async src="banana.js" ></head>
<body>And the band played on....And the BAND said to the
<a href="leader.html">leader</a>.</body>
EOF;

    $result = $a->extract_assets($s);
    $this->assertTrue(count($result) == 1);
    $this->assertTrue($result[0] == "banana.js");
  }

  /**
   * @dataProvider provider
   */
  public function testMix(AmberAssetHelper $a)
  {
    $s = <<<EOF
<head><link href="banana.css" rel="stylesheet" type="text.css"><script src="banana.js" type="text/javascript"></head><body><img src="../peacock.png">And the band played on....And the <img src="http://band.com/band.jpg"/> said to the
<a href="leader.html">leader</a>.</body>
EOF;

    $result = $a->extract_assets($s);
    $this->assertEquals(count($result),4);
    sort($result);
    $this->assertTrue($result[0] == "../peacock.png");
    $this->assertTrue($result[1] == "banana.css");
    $this->assertTrue($result[2] == "banana.js");
    $this->assertEquals($result[3],"http://band.com/band.jpg");

  }

  /**
   * @dataProvider provider
   */
  public function testExpandReferencesSimple(AmberAssetHelper $a)
  {
    $url = "http://example.com";
    $assets = array("banana.jpg", 'scripts/ban.js');
    $result = $a->expand_asset_references($url,$assets);
    $this->assertEquals($result['banana.jpg']['url'],'http://example.com/banana.jpg');
    $this->assertEquals($result['scripts/ban.js']['url'],'http://example.com/scripts/ban.js');
  }

  /**
   * @dataProvider provider
   */
  public function testExpandReferencesExternal(AmberAssetHelper $a)
  {
    $url = "http://example.com";
    $assets = array("banana.jpg", 'scripts/ban.js', 'http://bananas.com/fruit');
    $result = $a->expand_asset_references($url,$assets);
    $this->assertEquals($result['banana.jpg']['url'],'http://example.com/banana.jpg');
    $this->assertEquals($result['scripts/ban.js']['url'],'http://example.com/scripts/ban.js');
    $this->assertEquals($result['http://bananas.com/fruit']['url'],'http://bananas.com/fruit');
  }


  /**
   * @dataProvider provider
   */
  public function testExpandReferencesMix(AmberAssetHelper $a)
  {
    $url = "http://example.com";
    $assets = array("banana.jpg", 'scripts/ban.js', 'http://example.com/example.jpg', 'http://othersite.org/frank/james.css', '//example.com/funky.jpg', '/abs.css');
    $result = $a->expand_asset_references($url,$assets);
    $this->assertEquals(count($result),6);
    $this->assertEquals('http://example.com/banana.jpg',$result['banana.jpg']['url']);
    $this->assertEquals('http://example.com/scripts/ban.js',$result['scripts/ban.js']['url']);
    $this->assertEquals('http://example.com/example.jpg', $result['http://example.com/example.jpg']['url']);
    $this->assertEquals('http://example.com/funky.jpg', $result['//example.com/funky.jpg']['url']);
    $this->assertEquals('http://example.com/abs.css', $result['/abs.css']['url']);
    $this->assertEquals('http://othersite.org/frank/james.css', $result['http://othersite.org/frank/james.css']['url']);
  }

  /**
   * @dataProvider provider
   */
  public function testExpandReferencesWithQuery(AmberAssetHelper $a)
  {
    $url = "http://example.com";
    $assets = array("banana.jpg", 'scripts/?h=x', 'http://example.com/data/?q=fruit', 'http://othersite.org/frank/james.css', '//example.com/funky.jpg', '/abs.css');
    $result = $a->expand_asset_references($url,$assets);
    $this->assertEquals(count($result),6);
    $this->assertEquals($result['banana.jpg']['url'],'http://example.com/banana.jpg');
    $this->assertEquals($result['scripts/?h=x']['url'],'http://example.com/scripts/?h=x');
    $this->assertEquals($result['http://example.com/data/?q=fruit']['url'],'http://example.com/data/?q=fruit');
    $this->assertEquals($result['//example.com/funky.jpg']['url'],'http://example.com/funky.jpg');
    $this->assertEquals($result['/abs.css']['url'],'http://example.com/abs.css');
    $this->assertEquals($result['http://othersite.org/frank/james.css']['url'],'http://othersite.org/frank/james.css');
  }

  /**
   * @dataProvider provider
   */
  public function testExpandReferencesWithAbsolutePaths(AmberAssetHelper $a)
  {
    $url = "http://example.com/fruit/cake";
    $assets = array("banana.jpg", 'scripts/?h=x', 'http://example.com/data/?q=fruit', 'http://othersite.org/frank/james.css', '//example.com/funky.jpg', '/abs.css');
    $result = $a->expand_asset_references($url,$assets);
    $this->assertEquals(count($result),6);
    $this->assertEquals($result['banana.jpg']['url'],'http://example.com/fruit/banana.jpg');
    $this->assertEquals($result['scripts/?h=x']['url'],'http://example.com/fruit/scripts/?h=x');
    $this->assertEquals($result['http://example.com/data/?q=fruit']['url'],'http://example.com/data/?q=fruit');
    $this->assertEquals($result['//example.com/funky.jpg']['url'],'http://example.com/funky.jpg');
    $this->assertEquals($result['/abs.css']['url'],'http://example.com/abs.css');
    $this->assertEquals($result['http://othersite.org/frank/james.css']['url'],'http://othersite.org/frank/james.css');
  }

  /**
   * @dataProvider provider
   */
  public function testExpandReferencesThatGoUp(AmberAssetHelper $a)
  {
    $url = "http://img.xuite.net/_v_1.0.32/personal/photo/fix.css";
    $assets = array("../common.css");
    $result = $a->expand_asset_references($url,$assets);
    $this->assertEquals(1,count($result));
    $this->assertEquals('http://img.xuite.net/_v_1.0.32/personal/common.css', $result['../common.css']['url']);
  }

  /**
   * @dataProvider provider
   */
  public function testExpandReferencesWithHTMLBaseTag(AmberAssetHelper $a)
  {
    $url = "http://example.com/fruit/cake";
    $assets = array("banana.jpg", 'scripts/?h=x', 'http://example.com/data/?q=fruit', 'http://othersite.org/frank/james.css', '//example.com/funky.jpg', '/abs.css');
    $result = $a->expand_asset_references($url,$assets,"http://example.com/somewhere/else/");
    $this->assertEquals(count($result),6);
    $this->assertEquals($result['banana.jpg']['url'],'http://example.com/somewhere/else/banana.jpg');
    $this->assertEquals($result['scripts/?h=x']['url'],'http://example.com/somewhere/else/scripts/?h=x');
    $this->assertEquals($result['http://example.com/data/?q=fruit']['url'],'http://example.com/data/?q=fruit');
    $this->assertEquals($result['//example.com/funky.jpg']['url'],'http://example.com/funky.jpg');
    $this->assertEquals($result['/abs.css']['url'],'http://example.com/abs.css');
    $this->assertEquals($result['http://othersite.org/frank/james.css']['url'],'http://othersite.org/frank/james.css');
  }

  public function testFullRelativePath()
  {
    $this->assertEquals(
      "dir1/common.css",
      AmberNetworkUtils::full_relative_path("dir1","common.css"));
    $this->assertEquals(
      "dir1/bananas/common.css",
      AmberNetworkUtils::full_relative_path("dir1","bananas/common.css"));
    $this->assertEquals(
      "dir1/common.css",
      AmberNetworkUtils::full_relative_path("dir1","bananas/../common.css"));
    $this->assertEquals(
      "dir1/common.css",
      AmberNetworkUtils::full_relative_path("dir1/dir2","../common.css"));
    $this->assertEquals(
      "_v_1.0.32/personal/common.css",
      AmberNetworkUtils::full_relative_path("_v_1.0.32/personal/photo","../common.css"));
    $this->assertEquals(
      "/persian/news/newsitem/article/typo3conf/ext/kj_imagelightbox2/res/lightbox2.04/js/prototype.js",
      AmberNetworkUtils::full_relative_path("/persian/news/newsitem/article","typo3conf/ext/kj_imagelightbox2/res/lightbox2.04/js/prototype.js"));

  }

  public function testCleanUpPathString()
  {
    $this->assertEquals(
      "common.css",
      AmberNetworkUtils::clean_up_path("../common.css"));
    $this->assertEquals(
      "_v_1.0.32/personal/common.css",
      AmberNetworkUtils::clean_up_path("_v_1.0.32/personal/common.css"));
    $this->assertEquals(
      "_v_1.0.32/personal/common.css",
      AmberNetworkUtils::clean_up_path("_v_1.0.32/personal/photo/../common.css"));
    $this->assertEquals(
      "_v_1.0.32/common.css",
      AmberNetworkUtils::clean_up_path("_v_1.0.32/personal/photo/../../common.css"));
    $this->assertEquals(
      "common.css",
      AmberNetworkUtils::clean_up_path("_v_1.0.32/personal/photo/../../../../common.css"));
  }


  /**
   * @dataProvider provider
   */
  public function testExpandReferencesThatGoAboveRoot(AmberAssetHelper $a)
  {
    $url = "http://example.com/fruit/cake";
    $assets = array("../../../banana.jpg", 'scripts/?h=x');
    $result = $a->expand_asset_references($url,$assets);
    $this->assertEquals(count($result),2);
    $this->assertEquals('http://example.com/banana.jpg', $result['../../../banana.jpg']['url']);
    $this->assertEquals('http://example.com/fruit/scripts/?h=x', $result['scripts/?h=x']['url']);
  }

  
  /**
   * @dataProvider provider
   */
  public function testRewriteLinks(AmberAssetHelper $a)
  {
    $body = "Now is the <script> .styling:  url('time'); </script> for all good <img src='men.jpg'/> to come to the aid of the party";
    $assets = array("time" => array("url" => 'http://example.com/time'), 'men.jpg' => array('url' => 'http://example.com/men.jpg', 'body' => 'somebinaerystuff'));
    $result = $a->rewrite_links($body,$assets);
    $a = md5('http://example.com/men.jpg') . ".jpg";
    $this->assertEquals("Now is the <script> .styling:  url('time'); </script> for all good <img src='assets/${a}'/> to come to the aid of the party",$result);  
  }

  /**
   * @dataProvider provider
   */
  public function testIgnoreImagesWithInlineData(AmberAssetHelper $a) {
    $s = <<<EOF
<head><link href="banana.css" rel="stylesheet" type="text.css"><script src="banana.js" type="text/javascript"></head><body><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEUAAAASCAMAAAA62ONUAAAB41BMVEVOapxPaJ1QaJ0AnuMAnuMAnuMAnuMAnuMtMnctMnctMnctMnctMncqL3AAnuMAnuMAnuMkRIgAnuMoP4QnOHpScaQtMnc+UY83VZFYeadAVJIrMHMtMncAnuMdTpAsMXQZV5goPYItMncAnuMtMncAmt4AnuMqL3EtMndOaJ4rMHItMncAmNoqL3AAneIAnuMtMncqL28AnuMtMncAltcAnuMtMncAnuMqL3EAlNUsMXYsMHQtMnctMnctMnctMncrMHItMncAAAAbVpgcVZguSYw8T40iXKB/l78YYKMFkdbKzN1MXZZjdaemvtlYcaZXcaZHW5YrSIyaosKIo8c3RYYzS40cWp9JXJZFWpaRnsFJZJ0vOn5gdKdPaJ4mTpJof69GW5acqsk+UI5CWZUDmN1BWZVtga8yVpYpV5pmfq4VZ6o0S4yz2e7h8vrw+f0nQIV9pswPeb4/WpciTZJEWpVVYJe/x9sxQ4W+x9uzu9Oyu9PK1OSnrsstOX5VaJ5acqYOfMHL1ORCUY4RdrvX2OU2TY0cW6A5To1LZJ0ZYaZ+j7gqOX4WaK0Iis+Im8AGkNUhVJm9v9R2jLcDl9wqL28XaK0Rdbrl5e4Ub7QLg8gqL3Dy8vYAldYtMnf///8AnuNA03DVAAAAQ3RSTlP8/v6ImHBkTF9vaoKX62pKkrCCiM3Wj5629o++iBDcs/6XoFA/tUDbcP7HUMjuoTB/74Ag3GBAkNzvobVgkBAwyIAAQngpRwAAAo9JREFUeNqV0mVv3EAQBuCTyswcZuWYjWd7pDIzp8wMYWzaMB3k0Ofx/NSu75KDNB/SV9burK19ZM/aBsXYtbqGhYWF83WNUgQ2HEVWCnNBiRxqWKTVXMge8G9U0TBUUqQPr8jKr1QqHbWKP40r7xMRVK1DFSPQwSEbQ+JuQRUVANnLlnZO5zCk6agqTDneNZnKUS5742rvxaXpWDqXy37sKjJC0pPEo+2ihJyMoh+de5Kedh3sKApYo+sCYo31RLXBsc9mPv7m5eij3sy1mX4zs5SYi+fNEwVG1UBQQUMZOVVHDSOogb8F/ILqRYHVMp5CBVS0hT6ZhbwYH7w9nLk+Zxbz5eSKohUVWWMpKiHoQFWoVo6sbHv6bCzPpsdjK+tvlYqEoqbKGoKTs3NOdkMTcRfqmq7vtL7Ia3s7s5QxK7L028ImEj+s7ssSSDJInWwfehU2K/Wd9QooXkQhInHotYOMyCm2hWj23fTz/ol8Eclnhl/H30/G0t+ZsuHYYkQUfTL6+2F3ImMp94bGR9LWaf+XcniZiNKjD8yBgS7THBrM30/0EctPqIw7QHwQwgYF3EGejGbwGcS7y4o0RSy5O7cy5qXuK/mem3OzUesPFKsUnsI87SNHmHyGwa5tFGBjWQFn1mIWZ2cum4lzPfGR2WVLnVeqFGqFIO11OQK0mcIQJhc1s7GtrCjJqeWCc/bM6em+1KJVZ1GCf5X95PDRppLiIndZYcx8ijnlpL+uRYCI9eQghVtpC2+4DNpKfJh4qFAg0oQYS+UKUi57dx47FVirBHg+WOsgw1XrDvABagvyrNGVCovf046rcTatc8bkK5WF7q496dUi0rLd4/HsaPHDenG0lcqgQUYQqvMXuNS61rudHXMAAAAASUVORK5CYII=">And the band played on....And the <img src="http://band.com/band.jpg"/> said to the
<a href="leader.html">leader</a>.</body>
EOF;

    $result = $a->extract_assets($s);
    $this->assertEquals(3,count($result));
    sort($result);
    $this->assertEquals($result[0],"banana.css");
    $this->assertEquals($result[1],"banana.js");
    $this->assertEquals($result[2],"http://band.com/band.jpg");

  }

  /**
   * @dataProvider provider
   */
//   public function testGetAssetsWithFunkyCharacters(AmberAssetHelper $a) {
//     $s = <<<EOF
// <head><link rel="stylesheet" href="/index.php?jat3action=gzip&amp;jat3type=css&amp;jat3file=t3-assets%2Fcss_792f1.css" type="text/css" /></head>
// <body>I said to the
// <a href="leader.html">leader</a>.</body>
// EOF;

//     $result = $a->extract_assets($s);
//     $this->assertEquals(1,count($result));
//     $this->assertEquals("/index.php?jat3action=gzip&amp;jat3type=css&amp;jat3file=t3-assets%2Fcss_792f1.css",$result[0]);
//   }

  /**
   * @dataProvider provider
   */
  public function testIgnoreExcludedAssetPaths(AmberAssetHelper $a) {
    $s = <<<EOF
<head><link href="banana.css" rel="stylesheet" type="text.css"><script src="banana.js" type="text/javascript"></head><body><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEUAAAASCAMAAAA62ONUAAAB41BMVEVOapxPaJ1QaJ0AnuMAnuMAnuMAnuMAnuMtMnctMnctMnctMnctMncqL3AAnuMAnuMAnuMkRIgAnuMoP4QnOHpScaQtMnc+UY83VZFYeadAVJIrMHMtMncAnuMdTpAsMXQZV5goPYItMncAnuMtMncAmt4AnuMqL3EtMndOaJ4rMHItMncAmNoqL3AAneIAnuMtMncqL28AnuMtMncAltcAnuMtMncAnuMqL3EAlNUsMXYsMHQtMnctMnctMnctMncrMHItMncAAAAbVpgcVZguSYw8T40iXKB/l78YYKMFkdbKzN1MXZZjdaemvtlYcaZXcaZHW5YrSIyaosKIo8c3RYYzS40cWp9JXJZFWpaRnsFJZJ0vOn5gdKdPaJ4mTpJof69GW5acqsk+UI5CWZUDmN1BWZVtga8yVpYpV5pmfq4VZ6o0S4yz2e7h8vrw+f0nQIV9pswPeb4/WpciTZJEWpVVYJe/x9sxQ4W+x9uzu9Oyu9PK1OSnrsstOX5VaJ5acqYOfMHL1ORCUY4RdrvX2OU2TY0cW6A5To1LZJ0ZYaZ+j7gqOX4WaK0Iis+Im8AGkNUhVJm9v9R2jLcDl9wqL28XaK0Rdbrl5e4Ub7QLg8gqL3Dy8vYAldYtMnf///8AnuNA03DVAAAAQ3RSTlP8/v6ImHBkTF9vaoKX62pKkrCCiM3Wj5629o++iBDcs/6XoFA/tUDbcP7HUMjuoTB/74Ag3GBAkNzvobVgkBAwyIAAQngpRwAAAo9JREFUeNqV0mVv3EAQBuCTyswcZuWYjWd7pDIzp8wMYWzaMB3k0Ofx/NSu75KDNB/SV9burK19ZM/aBsXYtbqGhYWF83WNUgQ2HEVWCnNBiRxqWKTVXMge8G9U0TBUUqQPr8jKr1QqHbWKP40r7xMRVK1DFSPQwSEbQ+JuQRUVANnLlnZO5zCk6agqTDneNZnKUS5742rvxaXpWDqXy37sKjJC0pPEo+2ihJyMoh+de5Kedh3sKApYo+sCYo31RLXBsc9mPv7m5eij3sy1mX4zs5SYi+fNEwVG1UBQQUMZOVVHDSOogb8F/ILqRYHVMp5CBVS0hT6ZhbwYH7w9nLk+Zxbz5eSKohUVWWMpKiHoQFWoVo6sbHv6bCzPpsdjK+tvlYqEoqbKGoKTs3NOdkMTcRfqmq7vtL7Ia3s7s5QxK7L028ImEj+s7ssSSDJInWwfehU2K/Wd9QooXkQhInHotYOMyCm2hWj23fTz/ol8Eclnhl/H30/G0t+ZsuHYYkQUfTL6+2F3ImMp94bGR9LWaf+XcniZiNKjD8yBgS7THBrM30/0EctPqIw7QHwQwgYF3EGejGbwGcS7y4o0RSy5O7cy5qXuK/mem3OzUesPFKsUnsI87SNHmHyGwa5tFGBjWQFn1mIWZ2cum4lzPfGR2WVLnVeqFGqFIO11OQK0mcIQJhc1s7GtrCjJqeWCc/bM6em+1KJVZ1GCf5X95PDRppLiIndZYcx8ijnlpL+uRYCI9eQghVtpC2+4DNpKfJh4qFAg0oQYS+UKUi57dx47FVirBHg+WOsgw1XrDvABagvyrNGVCovf046rcTatc8bkK5WF7q496dUi0rLd4/HsaPHDenG0lcqgQUYQqvMXuNS61rudHXMAAAAASUVORK5CYII=">And the band played on....And the <img src="http://band.com/band.jpg"/> said to the
<a href="leader.html">leader</a>.<script>function url(link); </script></body>
EOF;

    $result = $a->extract_assets($s);
    $this->assertEquals(3,count($result));
    sort($result);
    $this->assertEquals($result[0],"banana.css");
    $this->assertEquals($result[1],"banana.js");
    $this->assertEquals($result[2],"http://band.com/band.jpg");

  }



  /**
   * @dataProvider provider
   */
  public function testImagesWithSpacesInURL(AmberAssetHelper $a) {
    $s = <<<EOF
<head><link href=" banana.css" rel="stylesheet" type="text.css"><script src=" banana.js " type="text/javascript"></head><body><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEUAAAASCAMAAAA62ONUAAAB41BMVEVOapxPaJ1QaJ0AnuMAnuMAnuMAnuMAnuMtMnctMnctMnctMnctMncqL3AAnuMAnuMAnuMkRIgAnuMoP4QnOHpScaQtMnc+UY83VZFYeadAVJIrMHMtMncAnuMdTpAsMXQZV5goPYItMncAnuMtMncAmt4AnuMqL3EtMndOaJ4rMHItMncAmNoqL3AAneIAnuMtMncqL28AnuMtMncAltcAnuMtMncAnuMqL3EAlNUsMXYsMHQtMnctMnctMnctMncrMHItMncAAAAbVpgcVZguSYw8T40iXKB/l78YYKMFkdbKzN1MXZZjdaemvtlYcaZXcaZHW5YrSIyaosKIo8c3RYYzS40cWp9JXJZFWpaRnsFJZJ0vOn5gdKdPaJ4mTpJof69GW5acqsk+UI5CWZUDmN1BWZVtga8yVpYpV5pmfq4VZ6o0S4yz2e7h8vrw+f0nQIV9pswPeb4/WpciTZJEWpVVYJe/x9sxQ4W+x9uzu9Oyu9PK1OSnrsstOX5VaJ5acqYOfMHL1ORCUY4RdrvX2OU2TY0cW6A5To1LZJ0ZYaZ+j7gqOX4WaK0Iis+Im8AGkNUhVJm9v9R2jLcDl9wqL28XaK0Rdbrl5e4Ub7QLg8gqL3Dy8vYAldYtMnf///8AnuNA03DVAAAAQ3RSTlP8/v6ImHBkTF9vaoKX62pKkrCCiM3Wj5629o++iBDcs/6XoFA/tUDbcP7HUMjuoTB/74Ag3GBAkNzvobVgkBAwyIAAQngpRwAAAo9JREFUeNqV0mVv3EAQBuCTyswcZuWYjWd7pDIzp8wMYWzaMB3k0Ofx/NSu75KDNB/SV9burK19ZM/aBsXYtbqGhYWF83WNUgQ2HEVWCnNBiRxqWKTVXMge8G9U0TBUUqQPr8jKr1QqHbWKP40r7xMRVK1DFSPQwSEbQ+JuQRUVANnLlnZO5zCk6agqTDneNZnKUS5742rvxaXpWDqXy37sKjJC0pPEo+2ihJyMoh+de5Kedh3sKApYo+sCYo31RLXBsc9mPv7m5eij3sy1mX4zs5SYi+fNEwVG1UBQQUMZOVVHDSOogb8F/ILqRYHVMp5CBVS0hT6ZhbwYH7w9nLk+Zxbz5eSKohUVWWMpKiHoQFWoVo6sbHv6bCzPpsdjK+tvlYqEoqbKGoKTs3NOdkMTcRfqmq7vtL7Ia3s7s5QxK7L028ImEj+s7ssSSDJInWwfehU2K/Wd9QooXkQhInHotYOMyCm2hWj23fTz/ol8Eclnhl/H30/G0t+ZsuHYYkQUfTL6+2F3ImMp94bGR9LWaf+XcniZiNKjD8yBgS7THBrM30/0EctPqIw7QHwQwgYF3EGejGbwGcS7y4o0RSy5O7cy5qXuK/mem3OzUesPFKsUnsI87SNHmHyGwa5tFGBjWQFn1mIWZ2cum4lzPfGR2WVLnVeqFGqFIO11OQK0mcIQJhc1s7GtrCjJqeWCc/bM6em+1KJVZ1GCf5X95PDRppLiIndZYcx8ijnlpL+uRYCI9eQghVtpC2+4DNpKfJh4qFAg0oQYS+UKUi57dx47FVirBHg+WOsgw1XrDvABagvyrNGVCovf046rcTatc8bkK5WF7q496dUi0rLd4/HsaPHDenG0lcqgQUYQqvMXuNS61rudHXMAAAAASUVORK5CYII=">And the band played on....And the <img src="http://band.com/band.jpg"/> said to the
<a href="leader.html">leader</a>.</body>
EOF;

    $result = $a->extract_assets($s);
    $this->assertEquals(3,count($result));
    sort($result);
    $this->assertEquals($result[0],"banana.css");
    $this->assertEquals($result[1],"banana.js");
    $this->assertEquals($result[2],"http://band.com/band.jpg");
  }

  /**
   * @dataProvider provider
   */
  public function testImagesWithQuerystring(AmberAssetHelper $a) {
    $s = <<<EOF
<head><link href="banana.css" rel="stylesheet" type="text.css"><script src="banana.js?eatmyshorts=1" type="text/javascript"></head><body><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEUAAAASCAMAAAA62ONUAAAB41BMVEVOapxPaJ1QaJ0AnuMAnuMAnuMAnuMAnuMtMnctMnctMnctMnctMncqL3AAnuMAnuMAnuMkRIgAnuMoP4QnOHpScaQtMnc+UY83VZFYeadAVJIrMHMtMncAnuMdTpAsMXQZV5goPYItMncAnuMtMncAmt4AnuMqL3EtMndOaJ4rMHItMncAmNoqL3AAneIAnuMtMncqL28AnuMtMncAltcAnuMtMncAnuMqL3EAlNUsMXYsMHQtMnctMnctMnctMncrMHItMncAAAAbVpgcVZguSYw8T40iXKB/l78YYKMFkdbKzN1MXZZjdaemvtlYcaZXcaZHW5YrSIyaosKIo8c3RYYzS40cWp9JXJZFWpaRnsFJZJ0vOn5gdKdPaJ4mTpJof69GW5acqsk+UI5CWZUDmN1BWZVtga8yVpYpV5pmfq4VZ6o0S4yz2e7h8vrw+f0nQIV9pswPeb4/WpciTZJEWpVVYJe/x9sxQ4W+x9uzu9Oyu9PK1OSnrsstOX5VaJ5acqYOfMHL1ORCUY4RdrvX2OU2TY0cW6A5To1LZJ0ZYaZ+j7gqOX4WaK0Iis+Im8AGkNUhVJm9v9R2jLcDl9wqL28XaK0Rdbrl5e4Ub7QLg8gqL3Dy8vYAldYtMnf///8AnuNA03DVAAAAQ3RSTlP8/v6ImHBkTF9vaoKX62pKkrCCiM3Wj5629o++iBDcs/6XoFA/tUDbcP7HUMjuoTB/74Ag3GBAkNzvobVgkBAwyIAAQngpRwAAAo9JREFUeNqV0mVv3EAQBuCTyswcZuWYjWd7pDIzp8wMYWzaMB3k0Ofx/NSu75KDNB/SV9burK19ZM/aBsXYtbqGhYWF83WNUgQ2HEVWCnNBiRxqWKTVXMge8G9U0TBUUqQPr8jKr1QqHbWKP40r7xMRVK1DFSPQwSEbQ+JuQRUVANnLlnZO5zCk6agqTDneNZnKUS5742rvxaXpWDqXy37sKjJC0pPEo+2ihJyMoh+de5Kedh3sKApYo+sCYo31RLXBsc9mPv7m5eij3sy1mX4zs5SYi+fNEwVG1UBQQUMZOVVHDSOogb8F/ILqRYHVMp5CBVS0hT6ZhbwYH7w9nLk+Zxbz5eSKohUVWWMpKiHoQFWoVo6sbHv6bCzPpsdjK+tvlYqEoqbKGoKTs3NOdkMTcRfqmq7vtL7Ia3s7s5QxK7L028ImEj+s7ssSSDJInWwfehU2K/Wd9QooXkQhInHotYOMyCm2hWj23fTz/ol8Eclnhl/H30/G0t+ZsuHYYkQUfTL6+2F3ImMp94bGR9LWaf+XcniZiNKjD8yBgS7THBrM30/0EctPqIw7QHwQwgYF3EGejGbwGcS7y4o0RSy5O7cy5qXuK/mem3OzUesPFKsUnsI87SNHmHyGwa5tFGBjWQFn1mIWZ2cum4lzPfGR2WVLnVeqFGqFIO11OQK0mcIQJhc1s7GtrCjJqeWCc/bM6em+1KJVZ1GCf5X95PDRppLiIndZYcx8ijnlpL+uRYCI9eQghVtpC2+4DNpKfJh4qFAg0oQYS+UKUi57dx47FVirBHg+WOsgw1XrDvABagvyrNGVCovf046rcTatc8bkK5WF7q496dUi0rLd4/HsaPHDenG0lcqgQUYQqvMXuNS61rudHXMAAAAASUVORK5CYII=">And the band played on....And the <img src="http://band.com/band.jpg"/> said to the
<a href="leader.html">leader</a>.</body>
EOF;

    $result = $a->extract_assets($s);
    $this->assertEquals(3,count($result));
    sort($result);
    $this->assertEquals($result[0],"banana.css");
    $this->assertEquals($result[1],"banana.js?eatmyshorts=1");
    $this->assertEquals($result[2],"http://band.com/band.jpg");
  }

  /**
   * @dataProvider provider
   */
  public function testExtractBaseTag(AmberAssetHelper $a) {
    $s = <<<EOF
<head>  <base href="http://www.roozonline.com/" />
<link href="banana.css" rel="stylesheet" type="text.css"><script src="banana.js?eatmyshorts=1" type="text/javascript"></head><body><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEUAAAASCAMAAAA62ONUAAAB41BMVEVOapxPaJ1QaJ0AnuMAnuMAnuMAnuMAnuMtMnctMnctMnctMnctMncqL3AAnuMAnuMAnuMkRIgAnuMoP4QnOHpScaQtMnc+UY83VZFYeadAVJIrMHMtMncAnuMdTpAsMXQZV5goPYItMncAnuMtMncAmt4AnuMqL3EtMndOaJ4rMHItMncAmNoqL3AAneIAnuMtMncqL28AnuMtMncAltcAnuMtMncAnuMqL3EAlNUsMXYsMHQtMnctMnctMnctMncrMHItMncAAAAbVpgcVZguSYw8T40iXKB/l78YYKMFkdbKzN1MXZZjdaemvtlYcaZXcaZHW5YrSIyaosKIo8c3RYYzS40cWp9JXJZFWpaRnsFJZJ0vOn5gdKdPaJ4mTpJof69GW5acqsk+UI5CWZUDmN1BWZVtga8yVpYpV5pmfq4VZ6o0S4yz2e7h8vrw+f0nQIV9pswPeb4/WpciTZJEWpVVYJe/x9sxQ4W+x9uzu9Oyu9PK1OSnrsstOX5VaJ5acqYOfMHL1ORCUY4RdrvX2OU2TY0cW6A5To1LZJ0ZYaZ+j7gqOX4WaK0Iis+Im8AGkNUhVJm9v9R2jLcDl9wqL28XaK0Rdbrl5e4Ub7QLg8gqL3Dy8vYAldYtMnf///8AnuNA03DVAAAAQ3RSTlP8/v6ImHBkTF9vaoKX62pKkrCCiM3Wj5629o++iBDcs/6XoFA/tUDbcP7HUMjuoTB/74Ag3GBAkNzvobVgkBAwyIAAQngpRwAAAo9JREFUeNqV0mVv3EAQBuCTyswcZuWYjWd7pDIzp8wMYWzaMB3k0Ofx/NSu75KDNB/SV9burK19ZM/aBsXYtbqGhYWF83WNUgQ2HEVWCnNBiRxqWKTVXMge8G9U0TBUUqQPr8jKr1QqHbWKP40r7xMRVK1DFSPQwSEbQ+JuQRUVANnLlnZO5zCk6agqTDneNZnKUS5742rvxaXpWDqXy37sKjJC0pPEo+2ihJyMoh+de5Kedh3sKApYo+sCYo31RLXBsc9mPv7m5eij3sy1mX4zs5SYi+fNEwVG1UBQQUMZOVVHDSOogb8F/ILqRYHVMp5CBVS0hT6ZhbwYH7w9nLk+Zxbz5eSKohUVWWMpKiHoQFWoVo6sbHv6bCzPpsdjK+tvlYqEoqbKGoKTs3NOdkMTcRfqmq7vtL7Ia3s7s5QxK7L028ImEj+s7ssSSDJInWwfehU2K/Wd9QooXkQhInHotYOMyCm2hWj23fTz/ol8Eclnhl/H30/G0t+ZsuHYYkQUfTL6+2F3ImMp94bGR9LWaf+XcniZiNKjD8yBgS7THBrM30/0EctPqIw7QHwQwgYF3EGejGbwGcS7y4o0RSy5O7cy5qXuK/mem3OzUesPFKsUnsI87SNHmHyGwa5tFGBjWQFn1mIWZ2cum4lzPfGR2WVLnVeqFGqFIO11OQK0mcIQJhc1s7GtrCjJqeWCc/bM6em+1KJVZ1GCf5X95PDRppLiIndZYcx8ijnlpL+uRYCI9eQghVtpC2+4DNpKfJh4qFAg0oQYS+UKUi57dx47FVirBHg+WOsgw1XrDvABagvyrNGVCovf046rcTatc8bkK5WF7q496dUi0rLd4/HsaPHDenG0lcqgQUYQqvMXuNS61rudHXMAAAAASUVORK5CYII=">And the band played on....And the <img src="http://band.com/band.jpg"/> said to the
<a href="leader.html">leader</a>.</body>
EOF;

    $result = $a->extract_base_tag($s);
    $this->assertEquals("http://www.roozonline.com/",$result);
  }

  /**
   * @dataProvider provider
   */
  public function testCSSAssets(AmberAssetHelper $a) {
    $s = <<<EOF
@charset "utf-8";
@import "../common.css";
img {
    border: none
}

div.indentation {
    width: 20px;
    height: 1.7em;
    margin: -0.4em 0.2em -0.4em -0.4em;
    padding: 0.42em 0 0.42em 0.6em;
    float: left;
}

div.tree-child {
    background: url(/misc/tree.png) no-repeat 11px center;
}

div.tree-child-last {
    background: url('/misc/tree1-bottom.png') no-repeat 11px center;
}

div.tree-child-horizontal {
    background: url("/misc/tree1-one.png") no-repeat -11px center;
}
div.tree-child-vertical{
    background: url(  "/misc/tree2-one.png" ) no-repeat -11px center;
}

div.bogus {
    background: url("link") no-repeat -11px center;
}

EOF;

    $result = $a->extract_css_assets($s);
    $this->assertEquals(5,count($result));
    sort($result);
    $this->assertEquals("../common.css", $result[0]);
    $this->assertEquals("/misc/tree.png", $result[1]);
    $this->assertEquals("/misc/tree1-bottom.png", $result[2]);
    $this->assertEquals("/misc/tree1-one.png", $result[3]);
    $this->assertEquals("/misc/tree2-one.png", $result[4]);
  }

  /**
   * @dataProvider provider
   */
  public function testWatermarkBanner(AmberAssetHelper $a)
  {
    $s = <<<EOF
<html><head><script src="banana.js" ></head>
<body>And the band played on....And the BAND said to the
<a href="leader.html">leader</a>.</body></html>
EOF;
    $result = $a->insert_banner($s, "This is a cached page", array());
    $needle = ';">This is a cached page</span></div></body></html>';
    $this->asserttrue(substr($result, -strlen($needle)) === $needle);

  }

  /**
   * @dataProvider provider
   */
  public function testWatermarkBannerOtherCase(AmberAssetHelper $a)
  {
    $s = <<<EOF
<html><head><script src="banana.js" ></head>
<body>And the band played on....And the BAND said to the
<a href="leader.html">leader</a>.</Body></html>
EOF;
    $result = $a->insert_banner($s, "This is a cached page", array());
    $needle = ';">This is a cached page</span></div></body></html>';
    $this->asserttrue(substr($result, -strlen($needle)) === $needle);
  }

  /**
   * @dataProvider provider
   */
  public function testWatermarkBannerNoBody(AmberAssetHelper $a)
  {
    $s = <<<EOF
<html><head><script src="banana.js" ></head>
<body>And the band played on....And the BAND said to the
<a href="leader.html">leader</a>.</html>
EOF;
    $result = $a->insert_banner($s, "This is a cached page", array());
    /* In this case, we put the div at the very end of the document, so there're not closing body or html tags */
    $needle = ';">This is a cached page</span></div>'; 
    $this->asserttrue(substr($result, -strlen($needle)) === $needle);
  }



}
