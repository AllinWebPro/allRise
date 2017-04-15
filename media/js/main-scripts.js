// Avoid `console` errors in browsers that lack a console.
(function() {
  var method;
  var noop = function () {};
  var methods = [
    'assert', 'clear', 'count', 'debug', 'dir', 'dirxml', 'error',
    'exception', 'group', 'groupCollapsed', 'groupEnd', 'info', 'log',
    'markTimeline', 'profile', 'profileEnd', 'table', 'time', 'timeEnd',
    'timeStamp', 'trace', 'warn'
  ];
  var length = methods.length;
  var console = (window.console = window.console || {});

  while(length--)
  {
    method = methods[length];
    if(!console[method]) { console[method] = noop; }
  }
}());

// ----------

(function($) {
  $.fn.replaceTagName = function(replaceWith)
  {
    var tags = [], i = this.length;
    while(i--)
    {
      var newElement = document.createElement(replaceWith), thisi = this[i], thisia = thisi.attributes;
      for(var a = thisia.length - 1; a >= 0; a--)
      {
        var attrib = thisia[a];
        newElement.setAttribute(attrib.name, attrib.value);
      };
      newElement.innerHTML = thisi.innerHTML;
      $(thisi).after(newElement).remove();
      tags[i - 1] = newElement;
    }
    return $(tags);
  };
})(window.jQuery);

// ----------

var $stream,
    $comments,
    $list,
    $item,
    $notice,
    $notices,
    columns,
    columnWidth,
    output = false,
    images = 0,
    resources = 0,
    lastPull,
    pull,
    success = 0,
    type,
    id,
    form_submit = false,
    browsers = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i;

var current_page = window.location.href;

$(function() {
  
  function split( val ) {
      return val.split( /,\s*/ );
    }
    function extractLast( term ) {
      return split( term ).pop();
    }
 
    $( "#item-tags" )
      // don't navigate away from the field on tab when selecting an item
      .bind( "keydown", function( event ) {
        if ( event.keyCode === $.ui.keyCode.TAB &&
            $( this ).autocomplete( "instance" ).menu.active ) {
          event.preventDefault();
        }
      })
      .autocomplete({
        source: function( request, response ) {
          $.getJSON( site_url + "ajax/autocomplete", {
            term: extractLast( request.term )
          }, response );
        },
        search: function() {
          // custom minLength
          var term = extractLast( this.value );
          if ( term.length < 3 ) {
            return false;
          }
        },
        focus: function() {
          // prevent value inserted on focus
          return false;
        },
        select: function( event, ui ) {
          var terms = split( this.value );
          // remove the current input
          terms.pop();
          // add the selected item
          terms.push( ui.item.value );
          // add placeholder to get the comma-and-space at the end
          terms.push( "" );
          this.value = terms.join( ", " );
          return false;
        }
      });
  
  $("a[rel^='prettyPhoto']").prettyPhoto();
  
  $("#add-comment, #comment-edit").triggeredAutocomplete({
    hidden: '#hidden_inputbox',
    source: "/ajax/users",
    trigger: "@" 
  });
  
  var $textareas = jQuery('textarea');  
  if($textareas)
  {
    $textareas.data('x', $textareas.outerWidth());
    $textareas.data('y', $textareas.outerHeight());
    
    $textareas.mouseup(function () {
      var $this = $(this);
      if($this.outerWidth() != $this.data('x') || $this.outerHeight() != $this.data('y')) {
        if($item = $('#item')) { itemMasonry(); }
      }
      // set new height/width
      $this.data('x', $this.outerWidth());
      $this.data('y', $this.outerHeight());
    });
  }
  
  CKEDITOR.on('instanceCreated', function(ev) {
    ev.editor.on('resize',function(reEvent) {
      if($item = $('#item')) { itemMasonry(); }
    });
  });
  
  CKEDITOR.on('dialogDefinition', function(ev) {
    var dialogName = ev.data.name;
    var dialogDefinition = ev.data.definition;

    if(dialogName == 'link') {
      var infoTab = dialogDefinition.getContents('target');

      var linkTargetType = infoTab.get('linkTargetType');
      linkTargetType['default'] = '_blank';
    }
  });
  
  $(this).on('mouseup', "#copy", function() {
    $(this).select();
  });

  $(this).on('click', ".deleteLink", function() {
    $parent = $(this).parent().parent('.pure-g');
    name = $("input", $parent).attr('name');
    if(name.indexOf('add') != -1) { $parent.remove(); }
    else {
      $("input", $parent).attr('name', 'remove-'+name);
      $parent.hide();
    }
    if($item = $('#item')) { itemMasonry(); }
  });

  if($('textarea[name="article"]').length)
  {
    $item = $("#item");
    $('textarea[name="article"]').ckeditor();
    var timer = setInterval(function() {
      if($("#cke_item-article").height()) {
        itemMasonry();
        clearInterval(timer);
      }
    }, 200);
  }

  // --- quick fix ---
  $(this).on('click', ".comingsoon", function() {
    $("#comingsoon").dialog({
      minHeight: 'auto',
      width:300,
      modal: true,
      buttons: {
        Ok: function() {
          $( this ).dialog( "close" );
        }
      }
    });
    return false;
  });

  //
  $.widget('ui.dialog', $.ui.dialog, {
    _allowInteraction: function( event ) {
      //This overrides the jQuery Dialog UI _allowInteraction to handle the sencha
      //extjs filter's not working.
      //this._super(event) passes onto the base jQuery UI
      return !!$( event.target ).closest( ".x-panel :input" ).length || this._super( event );
    }
  });

  /*
   * Alerts
   */
  if($notice = $("#notice-alert"))
  {
    updateNoticeAlert();
    setInterval(function() { updateNoticeAlert() }, 60 * 1000);
  }

  /*
   * Tool Tips
   */
  var tips = $("input[title]").tooltip();

  /*
   * Headline Notes
   */
  $(this).on('click', ".note-toggle", function(e) {
    e.preventDefault();
    $(this).parent(".grey").siblings(".note-text").toggle();
    if($item = $('#item')) { itemMasonry(); }
  });

  /*
   * Item Editor function (page)
   */
  $(this).on('click', "#add_image", function(e) {
    e.preventDefault();
    $form = $("#item_form");
    //$image = $('<input type="text" name="image[]" class="pure-input-1" placeholder="Image Link">');
    $image = $('<div class="pure-g"></div>');
    $input = $('<div class="pure-u-23-24"></div>');
    $input.append('<input type="text" value="" name="add-image[]" class="pure-input-1" placeholder="Image Link">');
    $mod = $('<div class="pure-u-1-24 center-text top-padding-xsmall"></div>');
    $link = $('<a class="deleteLink" href="javascript:void(0);"></a>');
    $link.append('<i class="fa fa-trash-o fa-7-5"></i>');
    $mod.append($link);
    $image.append($input);
    $image.append($mod);
    $(".images", $form).prepend($image).height();
    $(this).on('click', ".deleteLink", function() {
      $parent = $(this).parent().parent('.pure-g');
      name = $("input", $parent).attr('name');
      if(name.indexOf('add') != -1) { $parent.remove(); }
      else {
        $("input", $parent).attr('name', 'remove-'+name);
        $parent.hide();
      }
      if($item = $('#item')) { itemMasonry(); }
    });
    if($item = $('#item')) { itemMasonry(); }
  });
  $(this).on('click', "#add_resource", function(e) {
    e.preventDefault();
    $form = $("#item_form");
    //$resource = $('<input type="text" name="resource[]" class="pure-input-1" placeholder="Resource Link">');
    $resource = $('<div class="pure-g"></div>');
    $input = $('<div class="pure-u-23-24"></div>');
    $input.append('<input type="text" value="" name="add-resource[]" class="pure-input-1" placeholder="Resource Link">');
    $mod = $('<div class="pure-u-1-24 center-text top-padding-xsmall"></div>');
    $link = $('<a class="deleteLink" href="javascript:void(0);"></a>');
    $link.append('<i class="fa fa-trash-o fa-7-5"></i>');
    $mod.append($link);
    $resource.append($input);
    $resource.append($mod);
    $(".resources", $form).prepend($resource).height();
    $(this).on('click', ".deleteLink", function() {
      $parent = $(this).parent().parent('.pure-g');
      name = $("input", $parent).attr('name');
      if(name.indexOf('add') != -1) { $parent.remove(); }
      else {
        $("input", $parent).attr('name', 'remove-'+name);
        $parent.hide();
      }
      if($item = $('#item')) { itemMasonry(); }
    });
    if($item = $('#item')) { itemMasonry(); }
  });

  /*
   * Modal Windows
   */
  $(this).on('click', ".delete", function(e) {
    e.preventDefault();
    $link = $(this);
    $link.disabled = true;
    $("#delete-confirm").dialog({
      resizable: false,
      width:300,
      modal: true,
      minHeight: 'auto',
      maxHeight: ($(window).width() > 767)?($(window).height() - ($("#main-bar").height() * 2) - 80):(($(window).height() * 1) - 25),
      buttons: {
        "Destroy": function() {
          var $modal = $(this);
          if($link.attr('data-ajax'))
          {
            $.ajax({
              type: "GET",
              url: $link.attr('data-ajax'),
              success: function(data, textStatus, jqXHR)
              {
                if($link.attr('data-type') == 'comment') { $comments = $('#comments'); }
                if($link.attr('data-target')) { $("#"+$link.attr('data-target')).remove(); }
                else { window.location = window.location.href; }
                $modal.dialog( "close" );
              },
              error: function(jqXHR, textStatus, errorThrown) { return true; }
            });
          }
          else { window.location = $link.attr('href'); }
        },
        "Close": function() { $(this).dialog( "close" ); }
      }
    });
    return false;
  });

  $(this).on('click', "[data-modal='headline-create']", function(e) {
    e.preventDefault();
    $form = $("#headline-create form");
    $("#headline-create").dialog({
      beforeClose: function(event, ui) {
        $form.find("input[type=text], textarea").val("");
        $form.find("input[type=checkbox]").prop("checked", false);
      },
      buttons: {
        "Create": function() {
          $form.submit(); $(".ui-dialog").position({ my: "center", at: "center", of: window, collision: "fit" });
        },
        "Add Image": function() {
          if(!images) { $(".images", $form).html('<legend class="small">Images</legend>') }
          images++;
          $image = $('<input type="text" name="image[]" id="headline-image-'+images+'" class="pure-input-1" placeholder="Link '+images+'">');
          $(".images", $form).append($image);
          $(".ui-dialog").position({ my: "center", at: "center", of: window, collision: "fit" });
        },
        "Add Link": function() {
          if(!resources) { $(".resources", $form).html('<legend class="small">Resources</legend>') }
          resources++;
          $resource = $('<input type="text" name="resource[]" id="headline-resource-'+resources+'" class="pure-input-1" placeholder="Link '+resources+'">');
          $(".resources", $form).append($resource);
          $(".ui-dialog").position({ my: "center", at: "center", of: window, collision: "fit" });
        },
        "Close": function() { $(this).dialog("close"); }
      },
      fluid: true,
      maxHeight: ($(window).width() > 767)?($(window).height() - ($("#main-bar").height() * 2) - 80):(($(window).height() * 1) - 25),
      modal: true,
      position: { collision: "fit" },
      resizable: false,
      width: '95%'
    });
    return false;
  });

  $(this).on('click', "[data-modal='notice-page']", function(e) {
    e.preventDefault();
    $link = $(this);
    $page = $("#modal-page .page");
    $("#modal-page").attr('title', $link.attr('title'));
    $("#modal-page").dialog({
      beforeClose: function(event, ui) {
        $(".message").removeClass('hidden');
        $page.html('');
        $page.addClass('hidden');
      },
      buttons: {
        "See All Notices": function() {
          window.location.href = site_url+"notifications";
        },
        "Mark All Read": function() {
          $.ajax({
            type: "GET",
            url: site_url+'ajax/viewed',
            complete: function() {
              $.ajax({
                //async: false,
                type: "GET",
                url: $link.attr('data-ajax'),
                success: function(data, textStatus, jqXHR)
                {
                  $page.html(data);
                  setTimeout(function() { $(".ui-dialog").position({ my: "center", at: "center", of: window, collision: "fit" }); }, 500);
        
                  $("img").load(function() {
                    $comments = $("#comments");
                    if($stream = $('#stream')) { streamMasonry(); }
                    if($item = $('#item')) { itemMasonry(); }
                    if($notices = $('#notices')) { noticeMasonry(); }
                    if($list = $('#list')) { listMasonry(); }
                  });
                },
                error: function(jqXHR, textStatus, errorThrown) { return true; }
              });
            }
          });
        },
        "Close": function() { $(this).dialog("close"); }
      },
      fluid: true,
      maxHeight: ($(window).width() > 767)?($(window).height() - ($("#main-bar").height() * 2) - 80):(($(window).height() * 1) - 25),
      modal: true,
      position: { collision: "fit" },
      resizable: false,
      title: $link.attr('title'),
      width: '95%',
    });
    $.ajax({
      //async: false,
      type: "GET",
      url: $link.attr('data-ajax'),
      success: function(data, textStatus, jqXHR)
      {
        $(".message").addClass('hidden');
        $page.html(data);
        $page.removeClass('hidden');
        setTimeout(function() { $(".ui-dialog").position({ my: "center", at: "center", of: window, collision: "fit" }); }, 500);
      },
      error: function(jqXHR, textStatus, errorThrown) { return true; }
    });
    return false;
  });

  $(this).on('click', "[data-modal='modal-page']", function(e) {
    e.preventDefault();
    $link = $(this);
    $page = $("#modal-page .page");
    $("#modal-page").attr('title', $link.attr('title'));
    $("#modal-page").dialog({
      beforeClose: function(event, ui) {
        $(".message").removeClass('hidden');
        $page.html('');
        $page.addClass('hidden');
      },
      buttons: {
        "Close": function() { $(this).dialog("close"); }
      },
      fluid: true,
      maxHeight: ($(window).width() > 767)?($(window).height() - ($("#main-bar").height() * 2) - 80):(($(window).height() * 1) - 25),
      modal: true,
      position: { collision: "fit" },
      resizable: false,
      title: $link.attr('title'),
      width: '95%',
    });
    $.ajax({
      //async: false,
      type: "GET",
      url: $link.attr('data-ajax'),
      success: function(data, textStatus, jqXHR)
      {
        $(".message").addClass('hidden');
        $page.html(data);
        $page.removeClass('hidden');
        setTimeout(function() { $(".ui-dialog").position({ my: "center", at: "center", of: window, collision: "fit" }); }, 500);
        //setTimeout(function() {
        //}, 500);
      },
      error: function(jqXHR, textStatus, errorThrown) { return true; }
    });
    return false;
  });

  $(this).on('click', "[data-modal='item-modify']", function(e) {
    e.preventDefault();
    // Set Vars
    var title = $(this).attr('title');
    $form = $("#item-modify form");
    type = $("[name='type']", $form).val();
    id = $("[name='id']", $form).val();
    // Dialog
    $("#item-modify").dialog({
      beforeClose: function(event, ui) {
        $.ajax({
          async: false,
          type: "GET",
          url: site_url+'ajax/editable/'+type+'/'+id,
          complete: function() { location.reload(); }
        });
      },
      buttons: {
        "Modify": function() {
          $("[role='button']", $form).disabled = true;
          $form.submit(); $(this).dialog("close");
        },
        "Add Image": function() {
          if(!images) { $(".images", $form).html('<legend class="small">Images</legend>') }
          images++;
          $image = $('<input type="text" name="image[]" id="headline-image-'+images+'" class="pure-input-1" placeholder="Link '+images+'">');
          $(".images", $form).append($image);
          $(".ui-dialog").position({ my: "center", at: "center", of: window, collision: "fit" });
        },
        "Add Link": function() {
          if(!resources) { $(".resources", $form).html('<legend class="small">Resources</legend>') }
          resources++;
          $resource = $('<input type="text" name="resource[]" id="item-resource-'+resources+'" class="pure-input-1" placeholder="Link '+resources+'">');
          $(".resources", $form).append($resource);
          $(".ui-dialog").position({ my: "center", at: "center", of: window, collision: "fit" });
        },
        "Close": function() { $(this).dialog("close"); }
      },
      fluid: true,
      maxHeight: ($(window).width() > 767)?($(window).height() - ($("#main-bar").height() * 2) - 80):(($(window).height() * 1) - 25),
      modal: true,
      open: function( event, ui ) {
        if(!navigator.userAgent.match(browsers)) {
          $('textarea[name="article"]').ckeditor();
          var timer = setInterval(function() {
            if($("#cke_item-article").height()) {
              itemMasonry();
              clearInterval(timer);
            }
          }, 200);
        }
        setTimeout(function() {
          $.ajax({
            //async: false,
            type: "GET",
            url: site_url+'ajax/history/'+type+'/'+id,
            success: function(data, textStatus, jqXHR)
            {
              data = $.parseJSON(data);

              if(data.success == 1)
              {
                if(typeof data.item != 'undefined')
                {
                  if(data.item.headline !== $("[name='headline']", $form).val()) { $("[name='headline']", $form).val(data.item.headline); }
                  if(typeof data.item.article != 'undefined')
                  {
                    if(data.item.article !== $("[name='article']", $form).val()) { $("[name='article']", $form).val(data.item.article); }
                  }
                  if(data.item.tags !== $("[name='tags']", $form).val()) { $("[name='tags']", $form).val(data.item.tags); }
                }
                if(typeof data.place != 'undefined')
                {
                  $("[name='place']", $form).val(data.place.place);
                  $("[name='palceId']", $form).val(data.place.placeId);
                }
                if(typeof data.cats != 'undefined')
                {
                  $("[name='categoryId[]']", $form).prop('checked', false);
                  $.each(data.cats, function(index, value) {
                    $("#item-categoryId-"+value).prop('checked', true);
                  });
                }
                if(typeof data.image != 'undefined')
                {
                  $.each(data.image, function(index, value) {
                    if(!images) { $(".images", $form).html('<legend class="small">Images</legend>') }
                    images++;
                    $image = $('<input type="text" name="image[]" id="item-image-'+images+'" class="pure-input-1" placeholder="Link '+images+'">');
                    $image.val(value.image);
                    $(".images", $form).append($image);
                  });
                }
                if(typeof data.resources != 'undefined')
                {
                  $.each(data.resources, function(index, value) {
                    if(!resources) { $(".resources", $form).html('<legend class="small">Resources</legend>') }
                    resources++;
                    $resource = $('<input type="text" name="resource[]" id="item-resource-'+resources+'" class="pure-input-1" placeholder="Link '+resources+'">');
                    $resource.val(value.resource);
                    $(".resources", $form).append($resource);
                  });
                }
                $("[name='lastPull']", $form).val(data.time);
                $(".message").addClass('hidden');
                $form.removeClass('hidden');
                $(".ui-dialog").position({ my: "center", at: "center", of: window, collision: "fit" });
              }
              else
              {
                $(".message").text('This item is currently locked down. Please check back shortly.');
              }
        
              $("img").load(function() {
                $comments = $("#comments");
                if($stream = $('#stream')) { streamMasonry(); }
                if($item = $('#item')) { itemMasonry(); }
                if($notices = $('#notices')) { noticeMasonry(); }
                if($list = $('#list')) { listMasonry(); }
              });
            },
            error: function(jqXHR, textStatus, errorThrown) { return true; }
          });
        }, 500);
      },
      position: { collision: "fit" },
      resizable: false,
      width: '95%',
    });
    return false;
  });

  $(this).on('click', "[data-modal='article-create']", function(e) {
    e.preventDefault();
    // Set Vars
    var title = $(this).attr('title');
    var clusterId = $(this).attr('data-cluster');
    $form = $("#article-create form");
    // Dialog
    $("#article-create").dialog({
      buttons: {
        "Create": function() {
          $("[role='button']", $form).disabled = true;
          $form.submit();
        },
        "Add Image": function() {
          if(!images) { $(".images", $form).html('<legend class="small">Images</legend>') }
          images++;
          $image = $('<input type="text" name="image[]" id="headline-image-'+images+'" class="pure-input-1" placeholder="Link '+images+'">');
          $(".images", $form).append($image);
          $(".ui-dialog").position({ my: "center", at: "center", of: window, collision: "fit" });
        },
        "Add Link": function() {
          if(!resources) { $(".resources", $form).html('<legend class="small">Resources</legend>') }
          resources++;
          $resource = $('<input type="text" name="resource[]" id="article-resource-'+resources+'" class="pure-input-1" placeholder="Link '+resources+'">');
          $(".resources", $form).append($resource);
          $(".ui-dialog").position({ my: "center", at: "center", of: window, collision: "fit" });
        },
        "Close": function() { $(this).dialog("close"); }
      },
      fluid: true,
      maxHeight: ($(window).width() > 767)?($(window).height() - ($("#main-bar").height() * 2) - 80):(($(window).height() * 1) - 25),
      modal: true,
      open: function( event, ui ) {
        if(!navigator.userAgent.match(browsers)) {
          $('textarea[name="article"]').ckeditor();
          var timer = setInterval(function() {
            if($("#cke_item-article").height()) {
              itemMasonry();
              clearInterval(timer);
            }
          }, 200);
        }
        setTimeout(function() {
          $.ajax({
            type: "GET",
            url: site_url+'ajax/history/cluster/'+clusterId+'/1',
            success: function(data, textStatus, jqXHR)
            {
              data = $.parseJSON(data);
              // Load Item
              if(typeof data.item != 'undefined')
              {
                if(data.item.headline !== $("[name='headline']", $form).val()) { $("[name='headline']", $form).val(data.item.headline); }
                if(data.item.tags !== $("[name='tags']", $form).val()) { $("[name='tags']", $form).val(data.item.tags); }
              }
              if(typeof data.place != 'undefined')
              {
                $("[name='place']", $form).val(data.place.place);
                $("[name='palceId']", $form).val(data.place.placeId);
              }
              if(typeof data.cats != 'undefined')
              {
                $("[name='categoryId[]']", $form).prop('checked', false);
                $.each(data.cats, function(index, value) {
                  $("#article-categoryId-"+value).prop('checked', true);
                });
              }
              if(typeof data.resources != 'undefined')
              {
                $.each(data.resources, function(index, value) {
                  if(!resources) { $(".resources", $form).html('<legend class="small">Resources</legend>') }
                  resources++;
                  $resource = $('<input type="text" name="resource[]" id="headline-resource-'+resources+'" class="pure-input-1" placeholder="Link '+resources+'">');
                  $resource.val(value.resource);
                  $(".resources", $form).append($resource);
                });
              }
              if(typeof data.image != 'undefined')
              {
                $.each(data.image, function(index, value) {
                  if(!images) { $(".images", $form).html('<legend class="small">Images</legend>') }
                  images++;
                  $image = $('<input type="text" name="image[]" id="headline-image-'+images+'" class="pure-input-1" placeholder="Link '+images+'">');
                  $image.val(value.image);
                  $(".images", $form).append($image);
                });
              }
              if(success = data.success)
              {
                $(".message").addClass('hidden');
                $form.removeClass('hidden');
                $(".ui-dialog").position({ my: "center", at: "center", of: window, collision: "fit" });
              }
              else
              {
                $(".message").text('This item is currently locked down. Please check back shortly.');
              }
        
              $("img").load(function() {
                $comments = $("#comments");
                if($stream = $('#stream')) { streamMasonry(); }
                if($item = $('#item')) { itemMasonry(); }
                if($notices = $('#notices')) { noticeMasonry(); }
                if($list = $('#list')) { listMasonry(); }
              });
            },
            error: function(jqXHR, textStatus, errorThrown) { return true; }
          });
        }, 500);
      },
      position: { collision: "fit" },
      resizable: false,
      width: '95%',
    });
    return false;
  });

  $(this).on('click', "[data-modal='comment-edit']", function(e) {
    e.preventDefault();
    $link = $(this);
    var id = $link.attr('data-id');
    $form = $("#comment-edit form");
    $form.attr('action', $form.attr('action')+id);
    $("#edit-comment").val($("#comment"+id).children(".text").text().trim());
    $("#edit-commentId").val(id);
    $("#comment-edit").dialog({
      buttons: {
        "Modify Comment": function() {
          $form.submit();
          $(".ui-dialog").position({ my: "center", at: "center", of: window, collision: "fit" });
        },
        "Close": function() { $(this).dialog("close"); }
      },
      fluid: true,
      maxHeight: ($(window).width() > 767)?($(window).height() - ($("#main-bar").height() * 2) - 80):(($(window).height() * 1) - 25),
      modal: true,
      position: { collision: "fit" },
      resizable: false,
      width: '95%'
    });
    return false;
  });
  $(this).on('click', ".ui-widget-overlay", function(){ $(".ui-dialog-titlebar-close").trigger('click'); });

  $(this).on('click', "#draft", function() {
    console.log('test');
    $("#active").val('0');
    $("#item_form").submit();
  });

  /*
   * Ajax Forms
   */
  $(this).on('submit', "form.ajax", function(e) {
    e.preventDefault();
    $comments = $('#comments');
    var ajaxForm = $(this);
    $(".errors", ajaxForm).html('');
    var errors = new Array();
    $("[required]", ajaxForm).each(function() {
      if(!$(this).val()) { errors[errors.length] = $(this).attr('placeholder')+" is required."; }
    });
    if($notices = $('#notices')) { noticeMasonry(); }
    var post = ajaxForm.serialize();
    if($.isEmptyObject(errors))
    {
      var button = $('button', ajaxForm);
      var buttonText = button.html();
      button.html('<span class="inline-loader"></span>');
      $.ajax({
        type: "POST",
        url: $("[name='ajax']", ajaxForm).val(),
        dataType: 'json',
        data: post,
        success: function(data, textStatus, jqXHR) {
          form_submit = true;
          
          if(typeof data.errors != 'undefined') { errors[errors.length] = data.errors; }
          else if(typeof data.output != 'undefined') { errors[errors.length] = data.output; output = true; }
          else if(typeof data.redirect != 'undefined') { window.location = data.redirect; }
          else if(typeof data.prepend != 'undefined')
          {
            if($blank = $("#blank-comment")) { $blank.remove(); }
            $comments.prepend(data.prepend.html);
            $comments = $('#comments');
            $("#add-comment").val('');
            $subscribe = $("#subscribe");
            if($subscribe.text() == "Subscribe")
            {
              $subscribe.text("Unsubscribe");
              $subscribe.attr('href', $subscribe.attr('href').replace('subscribe=1', 'subscribe=0'));
            }
            else
            {
              $subscribe.text("Subscribe");
              $subscribe.attr('href', $subscribe.attr('href').replace('subscribe=0', 'subscribe=1'));
            }
            itemMasonry();
          }
          else if(typeof data.replace != 'undefined') {
            $form = $("#comment-edit form");
            $form.attr('action', $form.attr('action').replace('commentId='+id, 'commentId='));
            $("#edit-comment").val('');
            $("#edit-commentId").val('');
            $(".ui-dialog-titlebar-close").click();

            $("#"+data.replace.itemid).replaceWith(data.replace.html);
            itemMasonry();
          }
          if($(".ui-dialog-titlebar-close") && output) { $(".ui-dialog-titlebar-close").trigger('click'); resources = 0; }
          
          if(typeof FB !== 'undefined') { FB.XFBML.parse(); }
        
          $("img").load(function() {
            $comments = $("#comments");
            if($stream = $('#stream')) { streamMasonry(); }
            if($item = $('#item')) { itemMasonry(); }
            if($notices = $('#notices')) { noticeMasonry(); }
            if($list = $('#list')) { listMasonry(); }
          });
        },
        error: function(jqXHR, textStatus, errorThrown) {
          console.log(jqXHR);
          console.log(textStatus);
          console.log(errorThrown);
          errors[errors.length] = "An error has occurred, please try again.";
        },
        complete: function() { ajaxErrors(ajaxForm, errors); button.html(buttonText); $("a, button").disabled = false; }
      });
    }
    else { ajaxErrors(ajaxForm, errors); }
    return false;
  });
  $(this).on('submit', "form.ajax-list", function(e) {
    e.preventDefault();
    var ajaxForm = $(this);
    $(".errors", ajaxForm).html('');
    var errors = new Array();
    var button = $('button', ajaxForm);
    button.disabled = true;
    var buttonText = button.html();
    button.html('<span class="inline-loader"></span>');
    //
    var uri = '';
    $.each( ajaxForm.serializeArray(), function( key, item ) {
      if(item.value && item.name !== 'ajax')
      {
        if(uri) { uri += "&"; }
        uri += item.name+"="+item.value;
      }
    });
    //
    var link = $("[name='ajax']", ajaxForm).val();
    link += "?"+uri;
    //
    $(".ajax-loader-overlay, .ajax-loader").show();
    $.ajax({
      type: "GET",
      url: link,
      success: function(data, textStatus, jqXHR) {
        $("#content").html(data);
        $list = $('#list');
        listMasonry();

        var url = ajaxForm.attr('action');
        url += "?"+uri;
        var title = "Search ["+($("#filter-k").val())+"]" + site_title;
        var state = { "pageURL": url, "pageTitle": title, "isList": true };
        saveURL(state, title, url);
        
        $("img").load(function() {
          $comments = $("#comments");
          if($stream = $('#stream')) { streamMasonry(); }
          if($item = $('#item')) { itemMasonry(); }
          if($notices = $('#notices')) { noticeMasonry(); }
          if($list = $('#list')) { listMasonry(); }
        });
      },
      error: function(jqXHR, textStatus, errorThrown) {
        //console.log(jqXHR);
        //console.log(textStatus);
        //console.log(errorThrown);
        errors[errors.length] = "An error has occurred, please try again.";
      },
      complete: function() {
        ajaxErrors(ajaxForm, errors);
        button.html(buttonText);
        $(".ajax-loader-overlay, .ajax-loader").hide();
        $("a, button").disabled = false;
      }
    });
    //
    return false;
  });
  $(this).on('submit', "form.ajax-notice", function(e) {
    e.preventDefault();
    var ajaxForm = $(this);
    $(".errors", ajaxForm).html('');
    var errors = new Array();
    var button = $('button', ajaxForm);
    button.disabled = true;
    var buttonText = button.html();
    button.html('<span class="inline-loader"></span>');
    //
    var uri = '';
    $.each( ajaxForm.serializeArray(), function( key, item ) {
      if(item.value && item.name !== 'ajax')
      {
        if(uri) { uri += "&"; }
        uri += item.name+"="+item.value;
      }
    });
    //
    var link = $("[name='ajax']", ajaxForm).val();
    link += "?"+uri;
    //
    $(".ajax-loader-overlay, .ajax-loader").show();
    $.ajax({
      type: "GET",
      url: link,
      success: function(data, textStatus, jqXHR) {
        $("#content").html(data);
        $notices = $('#notices');
        noticeMasonry();

        var url = ajaxForm.attr('action');
        var title = "Search" + site_title;
        var state = { "pageURL": url, "pageTitle": title, "isList": true };
        saveURL(state, title, url);
        
        $("img").load(function() {
          $comments = $("#comments");
          if($stream = $('#stream')) { streamMasonry(); }
          if($item = $('#item')) { itemMasonry(); }
          if($notices = $('#notices')) { noticeMasonry(); }
          if($list = $('#list')) { listMasonry(); }
        });
      },
      error: function(jqXHR, textStatus, errorThrown) {
        //console.log(jqXHR);
        //console.log(textStatus);
        //console.log(errorThrown);
        errors[errors.length] = "An error has occurred, please try again.";
      },
      complete: function() {
        ajaxErrors(ajaxForm, errors);
        button.html(buttonText);
        $(".ajax-loader-overlay, .ajax-loader").hide();
        $("a, button").disabled = false;
      }
    });
    //
    return false;
  });
  $(this).on('submit', "form.ajax-join", function(e) {
    e.preventDefault();
    var ajaxForm = $(this);
    $(".errors", ajaxForm).html('');
    var errors = new Array();
    var button = $('button', ajaxForm);
    button.disabled = true;
    var buttonText = button.html();
    button.html('<span class="inline-loader"></span>');
    //
    $(".ajax-loader-overlay, .ajax-loader").show();
    var post = ajaxForm.serialize();
    $.ajax({
      type: "POST",
      data: post,
      url: $("[name='ajax']", ajaxForm).val(),
      success: function(data, textStatus, jqXHR) {
        location.reload();
      },
      error: function(jqXHR, textStatus, errorThrown) {
        //console.log(jqXHR);
        //console.log(textStatus);
        //console.log(errorThrown);
        errors[errors.length] = "An error has occurred, please try again.";
      },
      complete: function() {
        ajaxErrors(ajaxForm, errors);
        button.html(buttonText);
        $(".ajax-loader-overlay, .ajax-loader").hide();
        $("a, button").disabled = false;
      }
    });
    //
    return false;
  });

  /*
   * Ajax Links
   */
  $(this).on('click', "a.ajax", function(e) {
    e.preventDefault();
    $(".ajax-loader-overlay, .ajax-loader").show();
    var ajaxLink = $(this);
    if(ajaxLink.attr("data-ajax"))
    {
      var link = ajaxLink.attr("data-ajax");
    }
    else
    {
      var link = ajaxLink.attr('href');
      link = link + ((link.indexOf('?') >= 0) ? '&' : '?') + 'ajax=1';
    }
    $.ajax({
      type: "GET",
      url: link,
      success: function(data, textStatus, jqXHR)
      {
        var title = $("head title").text();
        current_page = ajaxLink.attr('href');
        if(ajaxLink.attr('title'))
        {
          title = ajaxLink.attr('title') + site_title;
          $("head title").text(title);
        }
        if(data)
        {
          $("#content").html(data);
          var timer = setInterval(function() {
            if($("#content").length) {
              if(ajaxLink.attr('data-type') == 'stream')
              {
                $stream = $('#stream');
                streamMasonry();
              }
              if(ajaxLink.attr('data-type') == 'list')
              {
                $list = $('#list');
                listMasonry();
              }
              if(ajaxLink.attr('data-type') == 'item')
              {
                $item = $('#item');
                itemMasonry();
              }
              if($('[name="form-page"]').length)
              {
                form_submit = false;
                $(window).on('beforeunload', function() {
                  if(!form_submit)
                  {
                    return 'The changes you made will be lost if you navigate away from this page.';
                  }
                });
                $('textarea[name="article"]').ckeditor();
                var timer2 = setIntervald(function() {
                  if($("#cke_item-article").height()) {
                    itemMasonry();
                    clearInterval(timer2);
                  }
                }, 200);
              }
              clearInterval(timer);
            }
          }, 200);
          
          if(typeof FB !== 'undefined') { FB.XFBML.parse(); }
          
          $("img").load(function() {
            $comments = $("#comments");
            if($stream = $('#stream')) { streamMasonry(); }
            if($item = $('#item')) { itemMasonry(); }
            if($notices = $('#notices')) { noticeMasonry(); }
            if($list = $('#list')) { listMasonry(); }
          });
          var state = { "pageURL": current_page, "pageTitle": title };
          if(ajaxLink.attr('data-type') == 'stream') { state.isStream = true; }
          if(ajaxLink.attr('data-type') == 'list') { state.isList = true; }
          if(ajaxLink.attr('data-type') == 'item') { state.isItem = true; }
        }
        if(ajaxLink.attr('data-type') == 'comment')
        {
          $comments = $('#comments');
        }
        if(ajaxLink.attr('data-type') == 'vote')
        {
          if(ajaxLink.attr('id') == 'importance-up') { var oposite = 'importance-down'; }
          else if(ajaxLink.attr('id') == 'importance-down') { var oposite = 'importance-up'; }
          else if(ajaxLink.attr('id') == 'quality-up') { var oposite = 'quality-down'; }
          else if(ajaxLink.attr('id') == 'quality-down') { var oposite = 'quality-up'; }
          $("#"+oposite).replaceTagName('a');
          $("#"+oposite).removeClass('pure-button-active');
          ajaxLink.addClass('pure-button-active');
          ajaxLink.replaceTagName('span');
        }
        if(typeof ajaxLink.attr('data-history') == 'undefined')
        {
          //console.log(state);
          saveURL(state, title, current_page);
        }
        $(".ajax-loader-overlay, .ajax-loader").hide();
      },
      error: function(jqXHR, textStatus, errorThrown) { return true; }
    });
    return false;
  });
  
  $(this).on('click', "a.ajax-submit", function(e) {
    e.preventDefault();
    $(".ajax-loader-overlay, .ajax-loader").show();
    var ajaxLink = $(this);
    var link = ajaxLink.attr('href');
    link = link + '?headline=' + $("#item-headline").val();
    window.location = link;
    return false;
  });
});

$(window).on('unload',function(){
  if(success == 1)
  {
    var time = new Date().getTime();
    time = time/1000;
    if(lastPull*(20*60) > time)
    {
      $.ajax({
        async: false,
        type: "GET",
        url: site_url+'ajax/editable/'+type+'/'+id
      });
    }
  }
});

$(window).load(function() {
  var url = window.location.href;
  var title = $("title").html();
  var isList = (url.indexOf("search")) ? true : false;
  var isItem = (url.indexOf("/h/") || url.indexOf("/c/") || url.indexOf("/a/")) ? true : false;
  var state = { "pageURL": url, "pageTitle": title, "isList": isList, "isItem": isItem };
  saveURL(state, title, url);
  
  if($('[name="form-page"]').length)
  {
    form_submit = false;
    $(window).on('beforeunload', function() {
      if(!form_submit)
      {
        return 'The changes you made will be lost if you navigate away from this page.';
      }
    });
  }

  $("img").load(function() {
    $comments = $("#comments");
    if($stream = $('#stream')) { streamMasonry(); }
    if($item = $('#item')) { itemMasonry(); }
    if($notices = $('#notices')) { noticeMasonry(); }
    if($list = $('#list')) { listMasonry(); }
  });

  /*
   * Handle AJAX History
   */
  $(window).on("popstate", function() {
    if(history.state)
    {
      $(".ajax-loader-overlay, .ajax-loader").show();
      var link = history.state.pageURL;
      link = link + ((link.indexOf('?') >= 0) ? '&' : '?') + 'ajax=1';
      $.ajax({
        type: "GET",
        url: link,
        success: function(data, textStatus, jqXHR)
        {
          current_page = history.state.pageURL;
          var title = history.state.pageTitle + " " + site_title;
          $("head title").text(title);
          $("#content").html(data);
          if(typeof history.state.isStream !== 'undefined' && history.state.isStream == true) {
            $stream = $('#stream');
            streamMasonry();
          }
          if(typeof history.state.isList !== 'undefined' && history.state.isList == true) {
            $list = $('#list');
            listMasonry();
          }
          if(typeof history.state.isItem !== 'undefined' && history.state.isItem == true) {
            $item = $('#item');
            itemMasonry();
          }
          $(".ajax-loader-overlay, .ajax-loader").hide();
        },
        error: function(jqXHR, textStatus, errorThrown) { return true; }
      });
    }
    else { window.history.back(); }
  });

  /*
   * Page Formating
   */
  $comments = $("#comments");
  if($stream = $('#stream')) { streamMasonry(); }
  if($item = $('#item')) { itemMasonry(); }
  if($notices = $('#notices')) { noticeMasonry(); }
  if($list = $('#list')) { listMasonry(); }
  $(this).on('scroll', function() {
    if($dialog = $(".ui-dialog")) { $dialog.position({ my: "center", at: "center", of: window, collision: "fit" }); }
  });
  $(this).on('resize', function() {
    if($dialog = $(".ui-dialog"))
    {
      if($(window).width() > 767) { $(".ui-dialog-content", $dialog).css('max-height', ($(window).height() - ($("#main-bar").height() * 2) - 160)); }
      else { $(".ui-dialog-content", $dialog).css('max-height', (($(window).height() * 1) - 120)); }
      $dialog.position({ my: "center", at: "center", of: window, collision: "fit" });
    }
    if($stream = $('#stream')) { streamMasonry(); }
    if($item = $('#item')) { itemMasonry(); }
    if($notices = $('#notices')) { noticeMasonry(); }
    if($list = $('#list')) { listMasonry(); }
  });
});

function ajaxErrors(ajaxForm, errors)
{
  $.each(errors, function(index1, value1) {
    if($.type(value1) == 'array' || $.type(value1) == 'object')
    {
      $.each(value1, function(index2, value2) { $(".errors", ajaxForm).append("<p"+(output?' class="output"':'')+">"+value2.replace('*', '')+"</p>"); });
    }
    else { $(".errors", ajaxForm).append("<p"+(output?' class="output"':'')+">"+value1.replace('*', '')+"</p>"); }
  });
  output = false;
  
  if($stream = $('#stream')) { streamMasonry(); }
  if($item = $('#item')) { itemMasonry(); }
  if($notices = $('#notices')) { noticeMasonry(); }
  if($list = $('#list')) { listMasonry(); }
}

function noticeMasonry()
{
  columns = Math.floor($("body").width() / 300);
  columnWidth = Math.floor(Math.floor($notices.width() / columns));
  $notices.children('.large').width(columnWidth * ((columns > 2) ? ((columns - 1)) : columns));
  $notices.children('.small').width(columnWidth* ((columns > 2) ? 1 : columns));
  $notices.masonry({ columnWidth: columnWidth, itemSelector: 'section' });
}

function itemMasonry()
{
  columns = Math.floor($("body").width() / 300);
  columnWidth = Math.floor(Math.floor($item.width() / columns));
  $item.children('.large').width(columnWidth * ((columns > 2) ? ((columns - 1)) : columns));
  $item.children('.small').width(columnWidth* ((columns > 2) ? 1 : columns));
  $item.masonry({ columnWidth: columnWidth, itemSelector: 'section' });
}

function listMasonry()
{
  columns = Math.floor($("body").width() / 300);
  columnWidth = Math.floor(Math.floor($list.width() / columns));
  $list.children('.large').width(columnWidth * ((columns > 2) ? ((columns - 1)) : columns));
  $list.children('.small').width(columnWidth* ((columns > 2) ? 1 : columns));
  $list.masonry({ columnWidth: columnWidth, itemSelector: 'section' });
  
  /*columns = Math.floor($("body").width() / 300);
  columnWidth = Math.floor($list.width() / columns);
  columnWidth--;
  if(columns <= 4)
  {
    $list.children('.large').width(columnWidth * ((columns < 2) ? 1 : 2));
  }
  else
  {
    $list.children('.large').width(columnWidth * (columns - 2));
  }
  $list.children('.small').width(columnWidth * ((columns == 2) ? 2 : 1));
  $list.masonry({ columnWidth: columnWidth, itemSelector: 'section' });*/
}

function streamMasonry()
{
  columns = Math.floor($("body").width() / 300);
  columnWidth = Math.floor($stream.width() / columns);
  $stream.children('.items').width(columnWidth);
  $stream.children(".doublewidth").width(columnWidth*2);
  $stream.masonry({ columnWidth: columnWidth, itemSelector: '.items' });
}

function saveURL(state, title, current_page)
{
  if(history.state == null || history.state.pageURL !== current_page)
  {
    history.pushState(state, title, current_page);
  }
}

function updateNoticeAlert()
{
  $.ajax({
    type: "GET",
    url: site_url+"ajax/alerts",
    success: function(data, textStatus, jqXHR)
    {
      $notice.text(data);
    }
  });
}