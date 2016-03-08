<?php
/*
Copyright 2016 Thick Arts LLC

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.

*/

 ?>(function ($) {
  $(document).ready(function() {
    window.index = lunr(function () {
      this.field('title', {boost: 10});
      this.field('body');
      this.ref('href');
    });
    window.index.pipeline.reset();
    <?php
      include_once('functions.php');

      //open db
      include_once('library/dbinfo.php');
      include_once('library/opendb.php');



      include_once('components/category-tree/category-tree.php');
      $categoryTree = getCategoryTree($db);






      foreach($categoryTree as &$topCategory){
        foreach($topCategory['subCategories'] as &$subCategory ){
          $lilCat = $subCategory;
          $lilCat['topCatSlug'] = $topCategory['slug'];
          $lilCats[]=$lilCat;
        }
      }
      function cmpCatCount($a, $b) {
        if ($a['count'] == $b['count']) {
          return 0;
        }
        return ($a['count'] > $b['count']) ? -1 : 1;
      }

      usort($lilCats, "cmpCatCount");

      //var_dump($lilCats);



      foreach($lilCats as &$cat){
        ?>
          window.index.add({
            href: 'http://callnyc.org/<?php echo $cat['topCatSlug'];?>/<?php echo $cat['slug'];?>.html',
            title: '<?php echo $cat['name'];?>',
            body: '<?php echo $cat['name'];?>'
          });
        <?php
      }


      include_once('library/closedb.php');
    ?>


    // icon click
    $('ul#nav-mobile li.search .search-wrapper i.material-icons').click(function() {
      if ($('.search-results .focused').length) {
        $('.search-results .focused').first()[0].click();
      } else if ($('.search-results').children().length) {
        $('.search-results').children().first()[0].click();
      }
    });

    var renderResults = function(results) {
      var resultsContainer = $('.search-results');
      resultsContainer.empty();
      Array.prototype.forEach.call(results, function(result) {
        var resultDiv = $('<a href=' + result[1] + '>' + result[0] + '</a>');
        resultsContainer.append(resultDiv);
      });
    };

    var debounce = function (fn) {
      var timeout;
      return function () {
        var args = Array.prototype.slice.call(arguments),
            ctx = this;

        clearTimeout(timeout);
        timeout = setTimeout(function () {
          fn.apply(ctx, args);
        }, 100);
      };
    };

    $('input#search').focus(function() { $(this).parent().addClass('focused'); });
    $('input#search').blur(function() {
      if (!$(this).val()) {
        $(this).parent().removeClass('focused');
      }
    });

    $('input#search').bind('keyup', debounce(function (e) {
      if ($(this).val() < 2) {
        renderResults([]);
        return;
      }

      if (e.which === 38 || e.which === 40 || e.keyCode === 13) return;

      var query = $(this).val();
      var results = window.index.search(query).slice(0, 6).map(function (result) {
        var href = result.ref.split('http://callnyc.org/')[1];
        return [href.charAt(0).toUpperCase() + href.slice(1), result.ref];
      });
      renderResults(results);
    }));


    $('input#search').bind('keydown', debounce(function (e) {
      // Escape.
      if (e.keyCode === 27) {
        $(this).val('');
        $(this).blur();
        renderResults([]);
        return;
      } else if (e.keyCode === 13) {
        // enter
        if ($('.search-results .focused').length) {
          $('.search-results .focused').first()[0].click();
        } else if ($('.search-results').children().length) {
          $('.search-results').children().first()[0].click();
        }
        return;
      }

      // Arrow keys.
      var focused;
      switch(e.which) {
        case 38: // up
          if ($('.search-results .focused').length) {
            focused = $('.search-results .focused');
            focused.removeClass('focused');
            focused.prev().addClass('focused');
          }
          break;

        case 40: // down
          if (!$('.search-results .focused').length) {
            focused = $('.search-results').children().first();
            focused.addClass('focused');
          } else {
            focused = $('.search-results .focused');
            if (focused.next().length) {
              focused.removeClass('focused');
              focused.next().addClass('focused');
            }
          }
          break;

        default: return; // exit this handler for other keys
      }
      e.preventDefault();
    }));



  });
}( jQuery ));
