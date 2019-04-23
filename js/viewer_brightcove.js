jQuery(function ($) {
  var loaded = false;

  

  $('#translate-link').click(function (e) {
    var urlIndexPiece = '';
    var re;
    e.preventDefault();
    var toggleAvailability = "";
    if ($('#translate-link').attr('data-toggleAvailable') == 'hide') {
        toggleAvailability = "&t_available=1";
    }
    if ($('#search-type').val() == 'Index') {
      var activeIndexPanel = $('#accordionHolder').accordion('option', 'active');
      if (activeIndexPanel !== false) {
        urlIndexPiece = '&index=' + activeIndexPanel;
      }
    }
    if ($('#translate-link').attr('data-lang') == $('#translate-link').attr('data-linkto')) {
      re = /&translate=(.*)/g;
      location.href = location.href.replace(re, '') + '&time=' + Math.floor(modVP.getVideoPosition(false)) + toggleAvailability + '&panel=' + $('#search-type').val() + urlIndexPiece;
    } else {
      re = /&time=(.*)/g;
      location.href = location.href.replace(re, '') + '&translate=1&time=' + Math.floor(parent.modVP.getVideoPosition(false)) + toggleAvailability + '&panel=' + $('#search-type').val() + urlIndexPiece;
    }
  });

  $('body').on('click', 'a.jumpLink', function (e) {
    e.preventDefault();
    var target = $(e.target);
    goToAudioChunk(target.data('timestamp'), target.data('chunksize'));
  });
  $('body').on('click', 'a.jumpLink', function (e) {
    e.preventDefault();
    var target = $(e.target);
    goToSecond(target.data('timestamp'));
  });

});

//Brightcove code ======================
var bcExp;
var modVP;
var modExp;
var modCon;

function onTemplateLoaded(experienceID) {
  bcExp = brightcove.getExperience(experienceID);
  modVP = bcExp.getModule(APIModules.VIDEO_PLAYER);
  modExp = bcExp.getModule(APIModules.EXPERIENCE);
  modCon = bcExp.getModule(APIModules.CONTENT);
  modExp.addEventListener(BCExperienceEvent.TEMPLATE_READY, onTemplateReady);
  modExp.addEventListener(BCExperienceEvent.CONTENT_LOAD, onContentLoad);
  modCon.addEventListener(BCContentEvent.VIDEO_LOAD, onVideoLoad);
}

function onTemplateReady(evt) {
  //Empty
}

function onContentLoad(evt) {
  var currentVideo = modVP.getCurrentVideo();
  modCon.getMediaAsynch(currentVideo.id);
  if ('time' in vars) {
    goToSecond(vars.time * 1);
  }
}

function onVideoLoad(evt) {
  if (modVP !== undefined) {
    modVP.loadVideo(evt.video.id);
  }
}

function goToAudioChunk(key, chunksize) {
  if (modVP !== undefined) {
    modVP.seek(key * chunksize * 60);
  }
}

function goToSecond(key) {
  if (modVP !== undefined) {
    modVP.seek(key);
  }
}