jQuery(function($) {
  var loaded = false;

  function activateContentPanel() {
	var searchType = $('#search-type').val()
	if(searchType == 'Transcript') {
	  $('#search-legend').html('Search this Transcript');
	  $('#submit-btn').off('click').on('click', getSearchResults);
	  $('#kw').off('keypress').on('keypress', getSearchResults);
	  $('#index-panel').fadeOut();
	}else if(searchType == 'Index') {
	  $('#search-legend').html('Search this Index');
	  $('#submit-btn').off('click').on('click', getIndexResults);
	  $('#kw').off('keypress').on('keypress', getIndexResults);
	  $('#index-panel').fadeIn();
	}
  }

  $('#search-type').toggleSwitch({
    change: function(e) {
      if(loaded) {
        activateContentPanel();
      }
    }
  });

	$('#translate-link').click(function(e) {
		e.preventDefault();
		if($('#translate-link').attr('data-lang') == $('#translate-link').attr('data-linkto'))
		{
			location.href = location.href.replace('&translate=1', '');
		}
		else
		{
			location.href = location.href + '&translate=1';
		}
	});

	$('#kw').on('focus', function(e) {
    if($('#kw').val() == 'Keyword') {
      $('#kw').toggleClass('kw-entry');
      $('#kw').val('');
    }
  });
  $('#kw').on('blur', function(e) {
    if($('#kw').val() == '') {
      $('#kw').toggleClass('kw-entry');
      $('#kw').val('Keyword');
    }
  });

  $('#kw').focus();

	// Bugfix...refresh youtube links after HTML replacement. Move youtube link hookups into function so it can be called elsewhere in the program. - SD @ Artifex 2013-01-13
	function youtubeControl() {
               //Youtube control (use Kaltura player ID value in code below)

               $('a.jumpLink').on('click', function(e) {
                 e.preventDefault();
                 var target = $(e.target);
				 if(player !== undefined && player.playVideo !== undefined)
				 {
					 player.playVideo();
					 player.seekTo(target.data('timestamp') * 60);
				 }
               });
               $('a.indexJumpLink').on('click', function(e) {
                 e.preventDefault();
                 var target = $(e.target);
				 
				 if(player !== undefined && player.playVideo !== undefined)
				 {
					 player.playVideo();
					 player.seekTo(target.data('timestamp'));
				  }
               });
     }
	
	youtubeControl();

  var prevSearch = { keyword:'', highLines:[] };

  var preg_quote = function(str) {
    return (str+'').replace(/([\\\.\+\*\?\[\^\]\$\(\)\{\}\=\!\<\>\|\:])/gi, "\\$1");
  };
  //clear search
    var clearSearchResults = function (e) {
          if((e.type == "keypress" && e.which == 13) || e.type == "click") {
              e.preventDefault();
               $('#search-results').empty();
               $('#kw').val('');
      
               $('span.highlight').removeClass('highlight');
               $("#kw").prop('disabled', false);
               $("#submit-btn").css("display", "inline-block");
               $("#clear-btn").css("display", "none");
               
         }
      }
//clear search end

  var getSearchResults = function(e) {
    if((e.type == "keypress" && e.which == 13) || e.type == "click") {
      e.preventDefault();
      var kw = $('#kw').val();
      
      if(kw != '') {
	if(prevSearch.highLines.length != 0) {
	  $.each(prevSearch.highLines, function(key, val) {
	    var line = $('#line_' + val)
	    var lineText = line.html();
        line.find('.highlight').contents().unwrap();
	  });
	}

	$.getJSON('viewer.php?action=search&cachefile=' + cachefile + '&kw=' + kw, function(data) {
	  var matches = [];
	  $('#search-results').empty();
	  if(data.matches.length == 0) {
	    $('<ul/>').addClass('error-msg').html('<li>No results found.</li>').appendTo('#search-results');

	  }else{
	  $("#kw").prop('disabled', true);
	$("#submit-btn").css("display", "none");
	  $("#clear-btn").css("display", "inline-block");
	    prevSearch.keyword = data.keyword;
	    $.each(data.matches, function(key, val) {
	      matches.push('<li><a class="search-result" href="#" data-linenum="' + val.linenum + '">' + val.shortline + '</a></li>');
	      prevSearch.highLines.push(val.linenum);
	      var line = $('#line_' + val.linenum)
	      var lineText = line.html();
          var re = new RegExp('(' + preg_quote(data.keyword) + ')', 'gi');
          line.html(lineText.replace(re, "<span class=\"highlight\">$1</span>"));
		  // Bugfix...refresh youtube links after HTML replacement. - SD @ Artifex 2013-01-13
		  youtubeControl();
	    });
	    $('<ol/>').addClass('nline').html(matches.join('')).appendTo('#search-results');
	    $('a.search-result').on('click', function(e) {
	      e.preventDefault();
	      var linenum;
	      if(e.target.tagName == 'SPAN') {
		linenum = $(e.target).parent().data("linenum");
	      }else{
		linenum = $(e.target).data("linenum");
	      }
	      var line = $('#line_' + linenum);
	      $('#transcript-panel').scrollTo(line, 800, {easing:'easeInSine'});
	    });
	  }
	});
      }
    }
  }

  prevIndex = { keyword: '', matches: [] };

  var getIndexResults = function(e) {
    if((e.type == "keypress" && e.which == 13) || e.type == "click") {
      e.preventDefault();
      var kw = $('#kw').val();
      $('span.highlight').removeClass('highlight');
      if(kw != '') {
	if(prevIndex.matches.length != 0) {
	  $.each(prevSearch.highLines, function(key, val) {
	    var section = $('#link' + val);
        var synopsis = $('#tp_' + val).parent();
        section.find('.highlight').contents().unwrap();
        synopsis.find('.highlight').contents().unwrap();
	  });
	}
	
$.getJSON('viewer.php?action=index&cachefile=' + cachefile + '&kw=' + kw, function(data) {
	  var matches = [];
	  $('#search-results').empty();

	  if(data.matches.length == 0) {
	    $('<ul/>').addClass('error-msg').html('<li>No results found.</li>').appendTo('#search-results');

	  }else{
	  $("#kw").prop('disabled', true);
	$("#submit-btn").css("display", "none");
	  $("#clear-btn").css("display", "inline-block");
	    prevSearch.keyword = data.keyword;
	    $.each(data.matches, function(key, val) {
	      matches.push('<li><a class="search-result" href="#" data-linenum="' + val.time + '">' + val.shortline + '</a></li>');
	      prevIndex.matches.push(val.linenum);
          var section = $('#link' + val.time);
          var synopsis = $('a[name="tp_' + val.time + '"]').parent();
          var re = new RegExp('(' + preg_quote(data.keyword) + ')', 'gi');
          section.html(section.text().replace(re, "<span class=\"highlight\">$1</span>"));
          synopsis.find('span').each(function() {
            $(this).html($(this).text().replace(re, "<span class=\"highlight\">$1</span>"));
          });
	    });
	    $('<ol/>').addClass('nline').html(matches.join('')).appendTo('#search-results');
	    $('a.search-result').on('click', function(e) {
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
  
}
  $('#submit-btn').on('click', getSearchResults);
  $('#clear-btn').on('click', clearSearchResults);
  $('#kw').on('keypress', getSearchResults);

  $('#accordionHolder').accordion({
	autoHeight: false,
    collapsible: true,
    active: false,
    fillSpace: false,
    change: function(e, ui) {
      $('#index-panel').scrollTo($('.ui-state-active'), 800, {easing:'easeInOutCubic'});
    }
  });

  $(document).ready(function() {
    loaded = true;
    activateContentPanel();
  });
});