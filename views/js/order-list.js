/**
 * 2015 Mediafinanz
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@easymarketing.de so we can send you a copy immediately.
 *
 * @author    silbersaiten www.silbersaiten.de <info@silbersaiten.de>
 * @copyright 2015 Mediafinanz
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
(function($){
  $.fn.serializeObject = function () {
    "use strict";

    var result = {};
    var extend = function (i, element) {
    var node = result[element.name];
    
      if ('undefined' !== typeof node && node !== null) {
        if ($.isArray(node)) {
          node.push(element.value);
        } else {
          result[element.name] = [node, element.value];
        }
      } else {
        result[element.name] = element.value;
      }
    };

    $.each(this.serializeArray(), extend);
    return result;
  };
})(jQuery);

var mediafinanz_list = {
  init: function() {
      mediafinanz_list.createButton();
    
    $('#inkasso').click(function(evt){
      evt.preventDefault();

        mediafinanz_list.processButtonClick();
      
      return false;
    });
    
    $('a.requestLabelData').click(function(evt){
      evt.preventDefault();

       mediafinanz_list.processDetailsClick($(this));
      
      return false;
    });
  },
  
  createButton: function() {
    $(document.createElement('a')).addClass('btn btn-warning bulk-actions').attr({'href': '#','id': 'inkasso'}).text(mediafinanz_list.translate('inkasso')).insertAfter($('.bulk-actions'));
  },
  
  processButtonClick: function() {
    list = mediafinanz_list.collectSelectedBoxes();
    
    if (list) {
      var link = request_path;
      
      for (var i in list) {
        link += '&order_list[]=' + list[i];
      }
      
      window.location = link;
    }
  },
  
  processDetailsClick: function(link) {
    $.fancybox.open({
      href: link.attr('href'),
      type: 'ajax',
      afterShow: function() {
        $('a#generateMultipleLabels').click(function(evt){
          evt.preventDefault();
          
          var frm = $('form#multipleLabelsForm');
          
          console.log(frm.serialize());
          
          //$.post(frm.attr('action'), {})
          
          return false;
        });
      }
    });
  },
  
  collectSelectedBoxes: function() {
    var collection = $('input:checkbox[name^=orderBox]:checked'),
        list = [];
    
    if (collection.length) {
      collection.each(function(){
        list.push($(this).attr('value'));
      });
      
      return list;
    }
    
    return false;
  },
  
  translate: function(str) {
    if (typeof(mediafinanz_translation) != 'undefined' && str in mediafinanz_translation) {
      return mediafinanz_translation[str];
    }
    
    return str;
  }
}

$(function(){
  mediafinanz_list.init();
})