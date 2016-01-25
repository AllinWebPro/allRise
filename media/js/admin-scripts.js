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

$(document).ready(function() {
  /*
   * Modal Windows
   */
  $(this).on('click', ".delete", function() {
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
          window.location = $link.attr('href');
        },
        "Close": function() { $(this).dialog( "close" ); }
      }
    });
    return false;
  });

  $(this).on('click', "[data-modal='blword-modify']", function() {
    // Set Vars
    $link = $(this);
    $form = $("#blword-modify form");
    id = $link.attr('data-id');
    // Dialog
    $("#blword-modify").dialog({
      beforeClose: function(event, ui) { location.reload(); },
      buttons: {
        "Modify": function() {
          $("[role='button']", $form).disabled = true;
          $form.submit(); $(this).dialog("close");
        },
        "Close": function() { $(this).dialog("close"); }
      },
      fluid: true,
      maxHeight: ($(window).width() > 767)?($(window).height() - ($("#main-bar").height() * 2) - 80):(($(window).height() * 1) - 25),
      modal: true,
      position: { collision: "fit" },
      resizable: false,
      width: '95%',
    });
    $.ajax({
      //async: false,
      type: "GET",
      url: site_url+'/ajax/blword/'+id,
      success: function(data, textStatus, jqXHR)
      {
        if(data.success == 1)
        {
          if(data.item.blwordId !== $("[name='blwordId']", $form).val()) { $("[name='blwordId']", $form).val(data.item.blwordId); }
          if(data.item.blword !== $("[name='blword']", $form).val()) { $("[name='blword']", $form).val(data.item.blword); }
          if(data.item.activated == 1)
          {
            $("#activated-yes").prop('checked', true);
            $("#activated-no").prop('checked', false);
          }
          else
          {
            $("#activated-yes").prop('checked', false);
            $("#activated-no").prop('checked', true);
          }
          //
          $(".message").addClass('hidden');
          $form.removeClass('hidden');
          $(".ui-dialog").position({ my: "center", at: "center", of: window, collision: "fit" });
        }
        else
        {
          $(".message").text('This item is currently locked down. Please check back shortly.');
        }
      },
      error: function(jqXHR, textStatus, errorThrown) { return true; }
    });
    return false;
  });

  /*
   * Ajax Forms
   */
  $(this).on('submit', "form.ajax", function() {
    var ajaxForm = $(this);
    $(".errors", ajaxForm).html('');
    var errors = new Array();
    $("[required]", ajaxForm).each(function() {
      if(!$(this).val()) { errors[errors.length] = $(this).attr('placeholder')+" is required."; }
    });
    var post = ajaxForm.serialize();
    console.log(post);
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
          if(!data.success && typeof data.errors != 'undefined') { errors[errors.length] = data.errors; }
          else if(typeof data.output != 'undefined') { errors[errors.length] = data.output; output = true; }
          //
          //if($(".ui-dialog-titlebar-close") && output) { $(".ui-dialog-titlebar-close").trigger('click'); }
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
});

$(window).load(function() {
  /*
   * Page Formating
   */
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
  });
});

function ajaxErrors(ajaxForm, errors)
{
  $.each(errors, function(index1, value1) {
    if($.type(value1) == 'array' || $.type(value1) == 'object')
    {
      $.each(value1, function(index2, value2) { $(".errors", ajaxForm).append("<p"+(output?' class="output"':'')+">"+value2+"</p>"); });
    }
    else { $(".errors", ajaxForm).append("<p"+(output?' class="output"':'')+">"+value1+"</p>"); }
  });
  output = false;
}