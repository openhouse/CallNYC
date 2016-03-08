<?php

  //phpinfo();
  //exit();


  //var_dump(pathinfo($_SERVER['REQUEST_URI']));
  // super simple router

  $pathInfo = pathinfo($_SERVER['REQUEST_URI']);
  $active['category'] = substr($pathInfo["dirname"],1);
  $active['subCategory'] = $pathInfo["filename"];

  //var_dump($active);

  include_once('functions.php');
  include_once('phonenumbers.php');

  //open db
  include_once('library/dbinfo.php');
  include_once('library/opendb.php');



  include_once('components/category-tree/category-tree.php');
  $categoryTree = getCategoryTree($db);
  //var_dump($categoryTree);


  $activeCategory = $categoryTree[$active['category']];
  $activeSubCategory = $categoryTree[$active['category']]['subCategories'][$active['subCategory']];
  //var_dump($activeCategory);
  //var_dump($activeSubCategory);


  // get member data
  $contents = substr(file_get_contents('data/districts-data.js'), 20);

  $contents = utf8_encode($contents);
  $results = json_decode($contents,true);

  foreach( $results['features'] as &$feature ) {
    $member['name'] = $feature["properties"]["description"];
    $member['districtFull'] = $feature["properties"]["title"];
    $member['district'] = intval( $feature["properties"]["number"] );
    $allMembers[$member['district']] = $member;
  }


  if($activeCategory && $activeSubCategory){


    $query = "SELECT `ACCOUNT`, COUNT(*) FROM `cases` WHERE `COMPLAINT_TYPE` LIKE '".$activeCategory["COMPLAINT_TYPE"]."' AND `DESCRIPTOR` LIKE '".$activeSubCategory["DESCRIPTOR"]."' AND `BOROUGH` != '' GROUP BY `ACCOUNT` ORDER BY COUNT(*) DESC LIMIT 10";
    foreach( $db->query($query) as $row ) {
      $member['ACCOUNT'] = $row['ACCOUNT'];
      $member['count'] = $row['COUNT(*)'];
      $member['monthly'] = ceil($member['count'] / 428 * (365.25/12)); //TODO: get number of days in data
      $member['annual'] = ceil($member['count'] / 428 * (365.25)); //TODO: get number of days in data

      $member['district'] = intval(trim(substr(trim($member['ACCOUNT']),4)));
      $member['name'] = $allMembers[$member['district']]['name'];
      $member['districtFull'] = $allMembers[$member['district']]['districtFull'];
      $member['categories'] = [];

      $query2 = "SELECT `COMPLAINT_TYPE`, `DESCRIPTOR`, COUNT(*) FROM `cases` WHERE `ACCOUNT` LIKE '".$member['ACCOUNT']."' AND `BOROUGH` != '' GROUP BY `DESCRIPTOR` ORDER BY COUNT(*) DESC LIMIT 7";
      foreach( $db->query($query2) as $row2 ) {
        $memberCategory['name'] = trim($row2["DESCRIPTOR"], ' /');
        $memberCategory['DESCRIPTOR'] = $row2["DESCRIPTOR"];
        $memberCategory['slug'] = slugify($memberCategory['name']);
        $memberCategory['count'] = $row2["COUNT(*)"];

        $memberCategory['parent']['name'] = trim($row2["COMPLAINT_TYPE"], ' /');
        $memberCategory['parent']['COMPLAINT_TYPE'] = $row2["COMPLAINT_TYPE"];
        $memberCategory['parent']['slug'] = slugify($memberCategory['parent']['name']);
        $memberCategory['url']="/".$memberCategory['parent']['slug'].'/'.$memberCategory['slug'].'.html';

        if(
          $memberCategory['parent']['slug'] != 'n-a'
          && $memberCategory['parent']['slug'] != 'select-one'
          && $memberCategory['parent']['slug'] != 'other'
          && $memberCategory['slug'] != 'n-a'
          && $memberCategory['slug'] != 'select'
        ){
          $member['categories'][]=$memberCategory;
        }
      }
      $members[$row['ACCOUNT']]=$member;
    }
    $frontPage=false;

  } else {
    //nocategory

    $frontPage=true;

    $query = "SELECT `ACCOUNT`, COUNT(*) FROM `cases` WHERE `BOROUGH` != '' GROUP BY `ACCOUNT` ORDER BY COUNT(*) DESC";
    foreach( $db->query($query) as $row ) {
      $member['ACCOUNT'] = $row['ACCOUNT'];
      $member['count'] = $row['COUNT(*)'];
      $member['monthly'] = ceil($member['count'] / 428 * (365.25/12)); //TODO: get number of days in data
      $member['annual'] = ceil($member['count'] / 428 * (365.25)); //TODO: get number of days in data

      $member['district'] = intval(trim(substr(trim($member['ACCOUNT']),4)));
      $member['name'] = $allMembers[$member['district']]['name'];
      $member['districtFull'] = $allMembers[$member['district']]['districtFull'];
      $member['categories'] = [];

      $query2 = "SELECT `COMPLAINT_TYPE`, `DESCRIPTOR`, COUNT(*) FROM `cases` WHERE `ACCOUNT` LIKE '".$member['ACCOUNT']."' AND `BOROUGH` != '' GROUP BY `DESCRIPTOR` ORDER BY COUNT(*) DESC LIMIT 7";
      foreach( $db->query($query2) as $row2 ) {
        $memberCategory['name'] = trim($row2["DESCRIPTOR"], ' /');
        $memberCategory['DESCRIPTOR'] = $row2["DESCRIPTOR"];
        $memberCategory['slug'] = slugify($memberCategory['name']);
        $memberCategory['count'] = $row2["COUNT(*)"];

        $memberCategory['parent']['name'] = trim($row2["COMPLAINT_TYPE"], ' /');
        $memberCategory['parent']['COMPLAINT_TYPE'] = $row2["COMPLAINT_TYPE"];
        $memberCategory['parent']['slug'] = slugify($memberCategory['parent']['name']);
        $memberCategory['url']="/".$memberCategory['parent']['slug'].'/'.$memberCategory['slug'].'.html';

        if(
          $memberCategory['parent']['slug'] != 'n-a'
          && $memberCategory['parent']['slug'] != 'select-one'
          && $memberCategory['parent']['slug'] != 'other'
          && $memberCategory['slug'] != 'n-a'
          && $memberCategory['slug'] != 'select'
        ){
          $member['categories'][]=$memberCategory;
        }
      }
      $members[$row['ACCOUNT']]=$member;
    }




  }

  include_once('library/closedb.php');

?><!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!-- <meta name="viewport" content="width=device-width, initial-scale=1"> -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="msapplication-tap-highlight" content="no">
    <meta name="description" content="#CallNYC <?php echo $activeSubCategory['name'] ?> Help: Free <?php echo ucwords($activeCategory['name']) ?> Assistance from Top New York City Council Members">
    <?php
      if($frontPage){
        ?>
        <title>Call NYC - Free Assistance from Top NYC Council Members</title>
        <link rel="canonical" href="http://callnyc.org" />
        <meta name="apple-mobile-web-app-title"
              content="Call NYC">

        <?php
      } else {
        ?>
          <title><?php echo ucwords($activeSubCategory['name']) ?> Assistance Top <?php echo count($members);?> - CallNYC.org | Free <?php echo ucwords($activeCategory['name']) ?> Services from New York City Council</title>
          <link rel="canonical" href="http://callnyc.org/<?php echo $activeCategory['slug'];?>/<?php echo $activeSubCategory['slug'];?>.html" />
          <meta name="apple-mobile-web-app-title"
                content="<?php echo ucwords($activeSubCategory['name']) ?>">

        <?php
      }
    ?>

    <!-- Favicons-->

    <!--  Android 5 Chrome Color-->
    <meta name="theme-color" content="#EE6E73">
    <link rel="apple-touch-icon" sizes="57x57" href="/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192"  href="/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/manifest.json">
    <meta name="msapplication-TileColor" content="#EE6E73">
    <meta name="msapplication-TileImage" content="/ms-icon-144x144.png">


    <meta name="apple-mobile-web-app-capable"
          content="yes">




    <!-- CSS-->
    <!-- <link href="/css/prism.css" rel="stylesheet"> -->
    <link href="/css/ghpages-materialize.css" type="text/css" rel="stylesheet" media="screen,projection">

    <link href="http://fonts.googleapis.com/css?family=Inconsolata" rel="stylesheet" type="text/css">
    <link href="http://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <style>
      ul#nav-mobile.side-nav.fixed {
        overflow-x: hidden !important;
      }
      .side-nav {
        overflow: hidden;

        overflow-y: scroll; /* has to be scroll, not auto */
        -webkit-overflow-scrolling: touch;

      }
      html, body {
        width: 100%;
        overflow-x: hidden;
      }

      .chip {
        overflow: hidden;
      }
      ul.side-nav.fixed li a {
        overflow: hidden;
      }

      nav.top-nav {
        overflow: hidden;
      }

      .card.large .card-image {
        max-height: 68%;
      }

      .card .card-image .card-title {
        text-shadow: 0 2px 1px rgba(0,0,0,0.48), 0 2px 2px rgba(0,0,0,0.40), 0 2px 5px rgba(0,0,0,0.32), 0 2px 10px rgba(0,0,0,0.24);
        //background-color: rgba(0,0,0,0.32);
      }
    </style>
  </head>
  <body>
    <header>
      <div class="container"><a href="#" data-activates="nav-mobile" class="button-collapse top-nav full hide-on-large-only"><i class="mdi-navigation-menu"></i></a></div>
      <ul id="nav-mobile" class="side-nav fixed">
        <li class="logo"><a id="logo-container" href="http://callnyc.org/" class="brand-logo">
            <object id="front-page-logo" type="image/svg+xml" data="/call-nyc-logo.svg">Your browser does not support SVG</object></a></li>
        <li class="search">
          <div class="search-wrapper card">
            <input id="search"><i class="material-icons">search</i>
            <div class="search-results"></div>
          </div>
        </li>
        <li class="bold" style="display:none;"><a href="about.html" class="waves-effect waves-teal">About</a></li>
        <li class="no-padding">
          <ul class="collapsible collapsible-accordion">
            <?php
              foreach($categoryTree as &$topCategory){
                ?>
                  <li class="bold"><a class="collapsible-header <?php if($topCategory['slug'] === $activeCategory['slug']){echo 'active';}?> waves-effect waves-teal"><?php echo $topCategory['name'];?></a>
                    <div class="collapsible-body">
                      <ul>
                        <?php foreach($topCategory['subCategories'] as &$subCategory ){
                          ?>
                            <li class="<?php if($subCategory['slug'] === $activeSubCategory['slug']){echo 'active';}?>"><a href="/<?php echo $topCategory['slug'];?>/<?php echo $subCategory['slug'];?>.html"><?php echo $subCategory['name'];?></a></li>
                          <?php
                        }?>
                      </ul>
                    </div>
                  </li>
                <?php
              }
            ?>
          </ul>
        </li>
      </ul>
    </header>
    <main>
      <div class="section" id="index-banner">
  <div class="container">
    <div class="row">
      <div class="col s12 m12 flow-text">
        <h1 class="header center-on-small-only" style="font-size: 2.5em;"><?php
            if($frontPage){
              ?>
              Call NYC
              <?php
            } else {
              echo $activeSubCategory['name'];
            }
        ?></h1>
        <h4 class="light red-text text-lighten-4 center-on-small-only " style="font-size: 1.35714285714286em;">
          <?php
            if($frontPage) {
              ?>
                New York City Council offers <b>free personal assistance</b> on hundreds of topics to New Yorkers like you every day.
                <b>Find a New York City Council Member</b> who specializes in your issue and CALL NYC.
              <?php
            } else {
              ?>
                The <?php if(count($members) > 1){echo count($members);}?> most active New York City Council member<?php if(count($members)>1){echo 's';}?> providing free personal <b> <span style="text-transform: uppercase;"><?php echo $activeSubCategory['name'];?></span> assistance</b> to New Yorkers like you.
              <?php
            }
          ?>
        </h4>
      </div>
    </div>
  </div>
</div>


      <div class="container">
  <div class="row">
    <div class="col s12 m9 l10">
      <?php if ($frontPage) {
        ?>
          <p class="caption hide">
             New York City Council offers <b>free personal assistance</b> on hundreds of topics to New Yorkers like you every day.
             <b>Find a council member</b> who specializes in your issue and <i>CALL NYC</i>.
          </p>
        <?php
      } else {
        ?>
          <p class="caption hide">
            Need <?php echo $activeSubCategory['name'];?> assistance?  Call a New York City Council Member for <b>free personal help</b>.
          </p>

        <?php
      }
      ?>
    </div>
    <div class="col s12 m5 l5">

      <?php
      $n = 1;

      foreach($members as &$member){
        if($member['district'] > 0) {

          ?>

            <div id="<?php echo trim($member['ACCOUNT'])?>" class="section scrollspy">

              <div class="card">
                <a  target="_blank" href="http://labs.council.nyc/districts/<?php echo $member['district'];?>/">
                  <div class="card-image">
                    <img src="http://labs.council.nyc/images/councilmember-<?php echo $member['district']?>.jpg">
                    <span class="card-title">
                      <?php if($n){
                        /*
                        <span style="float:left;font-size: 0.75em; padding-right: 0.5em;background-color: #ffcc4c;border-radius: 2.5em;width: 2.5em;height: 2.5em;m;text-align: center;text-shadow: none;color: black;font-weight: bold;padding-top: 0.5em;padding-right: 0;padding-left: 0;margin-right: 0.5em;margin-top: 0.75em;box-shadow: 0.25em 0px rgba(255,204,76,0.68), 0.5em 0px rgba(255,204,76,0.4624), 0.75em 0px rgba(255,204,76,0.314432);" class="">TOP</span>

                        */
                        ?>
                        <span style="float:left;font-size: 0.75em; padding-right: 0.5em;background-color: white;border-radius: 2em;width: 2em;height: 2em;m;text-align: center;text-shadow: none;color: black;font-weight: bold;pa;padding-top: 0.25em;padding-right: 0;padding-left: 0;margin-right: 0.5em;margin-top: 1em;box-shadow: 0 0 0px 3px black;" class=""><?php if($n<10){echo '<span style="font-weight: 300;">#</span>';}?><?Php echo $n; ?></span>

                        <?php
                      }?>
                      <span style="display:inline-block;">
                        <?php echo $member['name']?><br/><small><?php echo $member['districtFull']?></small>
                      </span>
                    </span>
                  </div>
                </a>
                <div class="card-content">
                  <p>
                    <?php if(!$frontPage){
                      ?>
                      <b><?php echo $activeSubCategory['name'];?></b> </br>
                      <?php
                    } else {
                      ?>
                      <b>Overall</b> </br>
                      <?php
                    }?>
                    <?php echo number_format($member['annual']); ?> case<?php if ($member['annual'] > 1) {echo 's';}?> in the past year
                  </p>
                  <p>
                    <b>Top Active Services</b> </br>



                    <?php
                      foreach($member['categories'] as $cat){
                        echo '<a style="" href="'.$cat['url'].'"> <span class="chip" style="margin-bottom:4px;">'.$cat['name']."</span></a> ";
                      }
                    ?>
                  </p>


                </div>
                <div class="card-action">
                  <a  href="tel:<?php echo $phoneNumbers[$member['district']];?>">Call <span style="float:right;"><?php echo $phoneNumbers[$member['district']];?></span></a>
                </div>
              </div>

            </div>


          <?php
          //var_dump($member);
          $n++;
        }
      }
      ?>







    </div>

    <div class="col hide-on-small-only m3 l2 offset-m4 offset-l4">
      <div class="toc-wrapper">
        <div style="height: 1px;">
          <ul class="section table-of-contents">
            <?php
              foreach($members as &$member){
                ?>
                <li><a href="#<?php echo trim($member['ACCOUNT'])?>"><?php echo $member['name']?></a></li>
                <?php
              }
            ?>
          </ul>
        </div>
      </div>
    </div>

  </div>
</div>

    </main>    <footer class="page-footer">
      <div class="container">
        <div class="row">
          <div class="col l8 s12">
            <h5 class="white-text">Powered by NYCC Constituent Services Data</h5>
            <p class="grey-text text-lighten-4">Every year tens of thousands of New Yorkers call their New York City Council members seeking assistance.  <a target="_blank" class="grey-text text-lighten-5" style="text-decoration: underline;" href="http://labs.council.nyc/districts/">All 51 City Council district offices</a> have staff dedicated to personally solving these often complex cases.</p>
            <p class="grey-text text-lighten-4">In 2016 New York City Council began publishing anonymized daily records of this casework.  This is the data which powers CallNYC.org.  It gives an up-to-today picture of work happening in New York City Council district offices.</p>
            <p class="grey-text text-lighten-4"> Not all City Council members opt to publish their service data and different members use the system in different ways.  As such CallNYC.org can only offer a picture of members who use the system.  Explore the Constituent Services Data yourself on the New York City Council's web site.</p>
            <a class="btn waves-effect waves-light red lighten-3" target="_blank" href="http://labs.council.nyc/districts/data/">Explore the Data</a>

          </div>


          <?php /*
          <!--
          <div class="col l4 s12 hide">
            <h5 class="white-text">Join the Discussion</h5>
            <p class="grey-text text-lighten-4">We have a Gitter chat room set up where you can talk directly with us. Come in and discuss new features, future goals, general problems or questions, or anything else you can think of.</p>
            <a class="btn waves-effect waves-light red lighten-3" target="_blank" href="https://gitter.im/Dogfalo/materialize">Chat</a>
          </div>
          -->
          */ ?>
          <div class="col l4 s12" style="overflow: hidden;">
            <h5 class="white-text">Connect</h5>

            <a href="https://twitter.com/CallNYCApp" class="twitter-follow-button" data-show-count="false" data-size="large" data-dnt="true">Follow @CallNYCApp</a>
            <a target="_blank" class="waves-effect waves-light btn" href="mailto:contact@callnyc.org" >
              Contact@CallNYC.org
            </a>
            <br/>
            <br/>

            <br/>
            <div class="g-follow" data-annotation="bubble" data-height="24" data-href="https://plus.google.com/108619793845925798422" data-rel="publisher"></div>
          </div>
        </div>
      </div>
      <div class="footer-copyright">
        <div class="container">
        2016 Open House Projects
        <?php /*
        <!--<a class="grey-text text-lighten-4 right" href="https://github.com/Dogfalo/materialize/blob/master/LICENSE">MIT License</a>-->
        */ ?>
        </div>
      </div>
    </footer>
    <!--  Scripts-->
    <script src="https://code.jquery.com/jquery-2.1.4.min.js"></script>
    <script>if (!window.jQuery) { document.write('<script src="/bin/jquery-2.2.1.min.js"><\/script>'); }
    </script>
    <script src="/js/jquery.timeago.min.js"></script>
    <script src="/jade/lunr.min.js"></script>
    <script src="/search.php"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.97.5/js/materialize.min.js"></script>
    <script src="/js/init.js"></script>
    <!-- Twitter Button -->
    <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>


    <!-- Google Analytics -->

    <script>
      (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
      (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
      m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
      })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

      ga('create', 'UA-74711372-1', 'auto');
      ga('send', 'pageview');

    </script>


  </body>
</html>
