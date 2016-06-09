var vars = [], hash;
var q = document.URL.split('?')[1];
if (q != undefined) {
  q = q.split('&');
  for(var i = 0; i < q.length; i++){
    hash = q[i].split('=');
    vars.push(hash[1]);
    vars[hash[0]] = hash[1];
  }
}

jQuery(function ($) {
  var loaded = false;

  function activateContentPanel() {
    var searchType = $('#search-type').val();
    if (searchType == 'Transcript') {
      $('#search-legend').html('Search this Transcript');
      $('#submit-btn').off('click').on('click', getSearchResults);
      $('#kw').off('keypress').on('keypress', getSearchResults);
      $('#index-panel').fadeOut();
    } else if (searchType == 'Index') {
      $('#search-legend').html('Search this Index');
      $('#submit-btn').off('click').on('click', getIndexResults);
      $('#kw').off('keypress').on('keypress', getIndexResults);
      $('#index-panel').fadeIn();
    }
  }

  $('#search-type').toggleSwitch({
    change: function (e) {
      if (loaded) {
        activateContentPanel();
      }
    }
  });

  $('#translate-link').click(function (e) {
    var urlIndexPiece = '';
    e.preventDefault();
    if($('#search-type').val() == 'Index') {
      var activeIndexPanel = $('#accordionHolder').accordion('option', 'active');
      if(activeIndexPanel !== false) {
        urlIndexPiece = '&index=' + activeIndexPanel;
      }
    }
    if ($('#translate-link').attr('data-lang') == $('#translate-link').attr('data-linkto')) {
      var re = /&translate=(.*)/g;
      location.href = location.href.replace(re, '') + '&time=' + Math.floor(jQuery('#subjectPlayer').data("jPlayer").status.currentTime) + '&panel=' + $('#search-type').val() + urlIndexPiece;
    } else {
      var re = /&time=(.*)/g;
      location.href = location.href.replace(re, '') + '&translate=1&time=' + Math.floor(jQuery('#subjectPlayer').data("jPlayer").status.currentTime) + '&panel=' + $('#search-type').val() + urlIndexPiece;
    }
  });

  $('#kw').on('focus', function (e) {
    if ($('#kw').val() == 'Keyword') {
      $('#kw').toggleClass('kw-entry');
      $('#kw').val('');
    }
  });
  $('#kw').on('blur', function (e) {
    if ($('#kw').val() == '') {
      $('#kw').toggleClass('kw-entry');
      $('#kw').val('Keyword');
    }
  });

  $('#kw').focus();

  if ($('#subjectPlayer')[0]) {
    jQuery.jPlayer.timeFormat.showHour = true;
    jQuery("#subjectPlayer").jPlayer({
      ready: function () {
        playerData = {};
        playerData['title'] = "Player";
        playerData[jQuery('#subjectPlayer').attr('rel')] = jQuery('#subjectPlayer').attr('href');
        if ('time' in vars) {
          jQuery(this).jPlayer("setMedia", playerData).jPlayer("play", vars['time'] * 1);
        } else {
          jQuery(this).jPlayer("setMedia", playerData).jPlayer("play");
        }
      },
      loadstart: function () {
        jQuery('#jp-loading-graphic').show();
      },
      playing: function () {
        jQuery('#jp-loading-graphic').hide();
      },
      swfPath: "swf",
      supplied: jQuery('#subjectPlayer').attr('rel')
    });
  }

  // Bugfix...refresh player links after HTML replacement. Move player link hookups into function so it can be called elsewhere in the program. - SD @ Artifex 2013-01-13
  function playerControl() {
    $('a.jumpLink').on('click', function (e) {
      e.preventDefault();
      jQuery('#subjectPlayer').jPlayer("play", $(e.target).data('timestamp') * 60);
    });
    $('a.indexJumpLink').on('click', function (e) {
      e.preventDefault();
      jQuery('#subjectPlayer').jPlayer("play", $(e.target).data('timestamp'));
    });
  }

  playerControl();

  var prevSearch = {keyword: '', highLines: []};

  var preg_quote = function (str) {
    return (str+'').replace(/([\\\.\+\*\?\[\^\]\$\(\)\{\}\=\!\<\>\|\:])/gi, "\\$1");
  };

  var clearSearchResults = function (e) {
    if ((e.type == "keypress" && e.which == 13) || e.type == "click") {
      e.preventDefault();
      $('#search-results').empty();
      $('#kw').val('');
      $('span.highlight').removeClass('highlight');
      $("input").prop('disabled', false);
      $("#submit-btn").css("display", "inline-block");
      $("#clear-btn").css("display", "none");
    }
  };

  var getSearchResults = function (e) {
    var isTranslate = false;

    if ((e.type == "keypress" && e.which == 13) || e.type == "click") {
      e.preventDefault();
      var kw = $('#kw').val();
      if (kw != '') {
        if (prevSearch.highLines.length != 0) {
          $.each(prevSearch.highLines, function (key, val) {
            var line = $('#line_' + val);
            var lineText = line.html();
            line.find('.highlight').contents().unwrap();
          });
        }
        if(parent.document.URL.search('translate=1') != -1) {
          isTranslate = true;
        }
        $.getJSON('viewer.php?action=search&cachefile=' + cachefile + '&kw=' + kw + (isTranslate ? '&translate=1' : ''), function(data) {
          var matches = [];
          $('#search-results').empty();
          if (data.matches.length == 0) {
            $('<ul/>').addClass('error-msg').html('<li>No results found.</li>').appendTo('#search-results');
          } else {
            $("#kw").prop('disabled', true);
            $("#submit-btn").css("display", "none");
            $("#clear-btn").css("display", "inline-block");
            prevSearch.keyword = data.keyword;
            $.each(data.matches, function (key, val) {
              matches.push('<li><a class="search-result" href="#" data-linenum="' + val.linenum + '">' + val.shortline + '</a></li>');
              prevSearch.highLines.push(val.linenum);
              var line = $('#line_' + val.linenum)
              var lineText = line.html();
              var re = new RegExp('(' + preg_quote(data.keyword) + ')', 'gi');
              line.html(lineText.replace(re, "<span class=\"highlight\">$1</span>"));
              // Bugfix...refresh player links after HTML replacement. - SD @ Artifex 2013-01-13
              playerControl();
            });
            $('<ol/>').addClass('nline').html(matches.join('')).appendTo('#search-results');
            $('a.search-result').on('click', function (e) {
              e.preventDefault();
              var linenum;
              if (e.target.tagName == 'SPAN') {
                linenum = $(e.target).parent().data("linenum");
              } else {
                linenum = $(e.target).data("linenum");
              }
              var line = $('#line_' + linenum);
              $('#transcript-panel').scrollTo(line, 800, {easing:'easeInSine'});
              $('#transcript-panel-alt').scrollTo(line, 800, {easing:'easeInSine'});
            });
          }
        });
      }
    }
  };

  prevIndex = {keyword: '', matches: []};

  var getIndexResults = function (e) {
    var isTranslate = false;

    if ((e.type == "keypress" && e.which == 13) || e.type == "click") {
      e.preventDefault();
      var kw = $('#kw').val();
      $('span.highlight').removeClass('highlight');
      if (kw != '') {
        if (prevIndex.matches.length != 0) {
          $.each(prevSearch.highLines, function (key, val) {
            var section = $('#link' + val);
            var synopsis = $('#tp_' + val).parent();
            section.find('.highlight').contents().unwrap();
            synopsis.find('.highlight').contents().unwrap();
          });
        }
        if(parent.document.URL.search('translate=1') != -1) {
          isTranslate = true;
        }
        $.getJSON('viewer.php?action=index&cachefile=' + cachefile + '&kw=' + kw + (isTranslate ? '&translate=1' : ''), function(data) {
          var matches = [];
          $('#search-results').empty();
          if (data.matches.length == 0) {
            $('<ul/>').addClass('error-msg').html('<li>No results found.</li>').appendTo('#search-results');
          } else {
            $("#kw").prop('disabled', true);
            $("#submit-btn").css("display", "none");
            $("#clear-btn").css("display", "inline-block");
            prevSearch.keyword = data.keyword;
            $.each(data.matches, function (key, val) {
              matches.push('<li><a class="search-result" href="#" data-linenum="' + val.time + '">' + val.shortline + '</a></li>');
              prevIndex.matches.push(val.linenum);
              var section = $('#link' + val.time);
              var synopsis = $('a[name="tp_' + val.time + '"]').parent();
              var re = new RegExp('(' + preg_quote(data.keyword) + ')', 'gi');
              section.html(section.text().replace(re, "<span class=\"highlight\">$1</span>"));
              synopsis.find('span').each(function () {
                $(this).html($(this).text().replace(re, "<span class=\"highlight\">$1</span>"));
              });
            });
            $('<ol/>').addClass('nline').html(matches.join('')).appendTo('#search-results');
            $('a.search-result').on('click', function (e) {
              e.preventDefault();
              var linenum;
              var lineTarget;
              lineTarget = $(e.target);
              linenum = lineTarget.data("linenum");
              var line = $('#link' + linenum);
              $('#link' + linenum).click();
              $('#index-panel').scrollTo(line, 800, {easing:'easeInSine'});
            });
          }
        });
      }
    }
  };

  $('#submit-btn').on('click', getSearchResults);
  $('#clear-btn').on('click', clearSearchResults);
  $('#kw').on('keypress', getSearchResults);

  var activeIndex = false;

  if('index' in vars) {
    activeIndex = parseInt(vars['index']);
    if(isNaN(activeIndex)) {
      activeIndex = false;
    }
  }

  $('#accordionHolder').accordion({
    autoHeight: false,
    collapsible: true,
    active: activeIndex,
    fillSpace: false,
    change: function (e, ui) {
      $('#index-panel').scrollTo($('.ui-state-active'), 800, {easing:'easeInOutCubic'});
    }
  });

  $(document).ready(function () {
    loaded = true;
    activateContentPanel();
    if(activeIndex !== false) {
      $('#index-panel').scrollTo($('.ui-state-active'), 800, {easing: 'easeInOutCubic'});
    }
  });
});
