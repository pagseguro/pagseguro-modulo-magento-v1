/**
************************************************************************
Copyright [2015] [PagSeguro Internet Ltda.]

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
************************************************************************
*/

/* ************************************* */
/* *********** MESSAGES **************** */
/* ************************************* */
var Messages = new function() {
    var wrapper = jQuery("#pagseguro-module-contents");
    var getHtml = function(options) {
        return '<div id="'+ options.id +'" class="pagseguro-msg pagseguro-msg-'+options.type+' pagseguro-msg-'+options.size+'"><'+options.tag+'>' + options.message + '</'+options.tag+'></div>';
    }
    
    var getConfirmHtml = function(options) {
        return '<div id="'+ options.id +'" class="pagseguro-msg pagseguro-msg-'+options.type+' pagseguro-msg-'+options.size+'"><'+options.tag+'>' + options.message + '</'+options.tag+'><hr /><div class="confirm"><button type="button" class="pagseguro-button pagseguro-confirm-button" id="accept">Sim</button><button type="button" class="pagseguro-button pagseguro-confirm-button" id="reject">Não</button></div></div>'; 
    }

    var remove = function() {
        wrapper.find('.pagseguro-msg-error, .pagseguro-msg-success').remove();
    };

    var add = function(message, type) {
        var html = getHtml({
            id: 'pagseguro-main-message',
            message: message,
            type: type,
            size: 'small',
            tag: 'p'
        });
        remove();
        wrapper.prepend(html);
    };
    
    return {
        addError: function(message) {
            add(message, 'error');
        },
        addSuccess: function(message) {
            add(message, 'success');
        },
        remove: function() {
            remove();
        },
        getHtml: function(options) {
            return getHtml(options);
        },
        getConfirmHtml: function(options) {
            return getConfirmHtml(options);
        }
    };
};

/* ************************************* */
/* *********** MODAL **************** */
/* ************************************* */
var Modal = new function(){
    var opened = false;
    var defaults = {
        transition:"none",speed:300,initialWidth:"600",innerWidth:"525",initialHeight:"450",title:!1,opacity:.65,close:"fechar <strong>x</strong>",fixed:true
    };

    var _bindEvents = function(elements,o){
        var options = jQuery.extend({},defaults,o || {});
        $(elements).colorbox(options);
    };

    var open = function(o) {
        var options = jQuery.extend({},defaults,o || {});
        if( options.inline && options.avoidDefault ){
            if( !options.width && !options.innerHeight ){
                options.innerWidth = parseInt(jQuery( options.href ).css('width').replace('px','')) + parseInt(jQuery( options.href ).css( 'padding-left' ).replace('px','')) + parseInt(jQuery( options.href ).css( 'padding-right' ).replace('px',''))
            }
            if( !options.height && !options.innerHeight  ){
                options.innerHeight = parseInt(jQuery( options.href ).css('height').replace('px','')) + parseInt(jQuery( options.href ).css( 'padding-top' ).replace('px','')) + parseInt(jQuery( options.href ).css( 'padding-bottom' ).replace('px',''));
            }
        }
        jQuery.colorbox(options);
    };

    var showLoading = function(msg) {
        if (jQuery('#pagseguro-loading-message:visible').length > 0) {
            return false;
        }

        var html = Messages.getHtml({
            id: 'pagseguro-loading-message',
            type: 'loading',
            size: 'medium',
            message: msg,
            tag: 'h3'
        });

        Messages.remove();
        open({
            html: html,
            width:  330,
            height: 600,
            overlayClose: false,
            escKey: false,
            close: false
        });

        jQuery('#cboxClose').hide();
        resize();
    };

    var hideLoading = function(callback) {
        close(callback);
    };

    var message = function(type, message) {
        var html = Messages.getHtml({
            type: type,
            size: 'small',
            message: message,
            tag: 'h3'
        });

        open({
            html: html,
            width:  400,
            height: 400
        });
        resize();
    };
    
    var showConfirm = function(type, message) {
        var html = Messages.getConfirmHtml({
            type: type,
            size: 'small',
            message: message,
            tag: 'span'
        });
        
        open({
            html: html,
            width:  400,
            height: 400
        });
        resize();
    };

    var alertConciliation = function(message) {
        this.message('alert', message)
    };
    
    var confirm = function(message) {
        this.showConfirm('warning', message)
    };

    var resize = function() {
        jQuery.colorbox.resize();
    };

    var close = function(callback) {
        jQuery.colorbox.close(callback);
    };

    var remove = function() {
        jQuery.colorbox.remove();
    };

    return {
        close : close,
        remove : remove,
        open : open,
        resize : resize,
        showLoading: showLoading,
        hideLoading: hideLoading,
        showConfirm: showConfirm,
        message: message,
        alertConciliation:alertConciliation,
        confirm: confirm
    }
};

/* ************************************* */
/* *********** MENU **************** */
/* ************************************* */
var Menu = new function() {

    var wrapper  = jQuery("#pagseguro-module-menu");
    var saveForm = $("#pagseguro-save-wrapper");
    var body = $("html, body");
    var windowSel  = jQuery(window);
    var animating = false;

    var applyMenu = function() {
        var selectedClass = "selected";
        var allItems = wrapper.find(".menu-item");

        allItems.click(function(e){
            e.preventDefault();
            e.stopPropagation();

            if (!animating) {
                animating = true;

                var item = jQuery(this);
                var id = item.attr("data-page-id");
                var hasForm = item.attr("data-has-form");

                allItems.removeClass(selectedClass);
                item.addClass(selectedClass);

                var showNewPage = function() {
                    Messages.remove();

                    jQuery(".pagseguro-module-content").removeClass(selectedClass);
                    jQuery("#pagseguro-module-content-" + id).addClass(selectedClass);

                    if (hasForm) {
                        saveForm.show();
                    } else {
                        saveForm.hide();
                    }

                    jQuery("#current-page-id").val(id);
                    animating = false;
                };

                if (windowSel.scrollTop() > 100) {
                    body.animate({scrollTop:0}, 800, 'swing', function(){
                        setTimeout(showNewPage, 100);
                    });
                } else {
                    showNewPage();
                }
            };
            return false;
        });
    };

    var applyFixedPostion = function() {
        var initialPos      = wrapper.offset().top;
        var initialLeft     = wrapper.offset().left;
        var initialWidth    = wrapper.width();
        var fixedClass      = 'fixed';

        var resetFixed = function() {
            wrapper.css('width', '');
            wrapper.css('top', '');
            wrapper.removeClass(fixedClass);
        };

        var applyFixed = function(top) {
            if (!wrapper.hasClass('fixed')) {
                wrapper.addClass(fixedClass);
            }

            wrapper.css('top', parseInt(top - initialPos, 10) + 'px');
            wrapper.width(initialWidth);
        };

        var getWindowTop = function() {
            var aditionalSum = jQuery(".page-head").length > 0 ? 100 : 0;
            return windowSel.scrollTop() + aditionalSum;
        };

        windowSel.scroll(function(e){
            var top = getWindowTop();

            if (top >= initialPos) {
                applyFixed(top);
            } else {
                resetFixed();
            }
        });

        windowSel.resize(function(){
            var wasFixed = wrapper.hasClass(fixedClass);

            resetFixed();
            initialWidth = wrapper.width();

            if (wasFixed){
                applyFixed(getWindowTop());
            }
        });
    };

    var applyGotoConfig = function() {
        jQuery(".pagseguro-goto-configuration").click(function(){
            jQuery("#menu-item-1").trigger('click');
            jQuery("#pagseguro-email-input").focus();
        });
    };

    var retractableMenu = function() {
        jQuery("#pagseguro-module-menu .children").click(function(){

            if (jQuery(this).closest("li").hasClass("open")) {
                jQuery(this).closest("li").removeClass("open");
                sessionStorage.setItem('hasOpen', false);
            } else {
                jQuery(this).closest("li").addClass("open");
                sessionStorage.setItem('hasOpen', true);
            }

        });
    };

    var sessionRetractable = function(){

        if (sessionStorage.getItem('hasOpen') == 'true') {
            jQuery("#pagseguro-module-menu .children").closest("li").addClass("open");
        }
    };

    this.init = function(){
        applyFixedPostion();
        applyMenu();
        applyGotoConfig();
        retractableMenu();
        sessionRetractable();
    };
};

/* ************************************* */
/* *********** CHECKBOX **************** */
/* ************************************* */
jQuery(document).ready(function () {
    jQuery('#pagseguro-check-all').click(function(event) {  //on click 

        if (this.checked) { // check select status
            jQuery('.checkbox').each(function () { //loop through each checkbox
                this.checked = true;  //select all checkboxes with class "checkbox1"               
            });
        } else {
            jQuery('.checkbox').each(function () { //loop through each checkbox
                this.checked = false; //deselect all checkboxes with class "checkbox1"                       
            });
        }

        var aCount = 0;
        jQuery('.checkbox').each(function (e) {
            if (jQuery(this).prop('checked')) {
                aCount++;
            }
        });

        if (aCount > 0) {
            jQuery('#conciliation-button').prop("disabled", false);
            jQuery('#send-email-button').prop("disabled", false);
        } else {
            jQuery('#conciliation-button').prop("disabled", true);
            jQuery('#send-email-button').prop("disabled", true);
        }
      
    });
});
/* ************************************* */
/* *************** MASK **************** */
/* ************************************* */
function maskConfig(o, f) {
    v_obj = o
    v_fun = f
    setTimeout('mask()', 1)
}

function mask() {
    v_obj.value = v_fun(v_obj.value)
}

function maskDiscount(v) {
    v = v.replace(/\D/g, "");
    v = v.substr(0, 4);

    if (v.replace('.', '').length == 4) {
        v = v.replace(/^(\d{2})(\d)/g, "$1.$2");
    } else {
        v = v.replace(/^(\d{1})(\d)/g, "$1.$2");
    }

    return v;
}

/**
 * List payments methods enabled for account
 */
jQuery(document).ready(function () {
  var open = false
  jQuery('#payment_pagseguro_payments_enabled-head').click(function () {
    open = true
    if (open) {
      var body = jQuery('#payment_pagseguro_payments_enabled')
      var loading = '<p>carregando...</p>'
      body.append(loading)
      PagSeguroDirectPayment.getPaymentMethods({
        success: function (res) {
          if (!res['error']) {
            body.empty()
            jQuery.each(res['paymentMethods'], function (i, items) {
              if (i !== 'BALANCE' && i !== 'DEPOSIT') {
                if (i === 'ONLINE_DEBIT') {
                  body.append('<ul id="' + i + '">' +
                    '<li style="display: inline-block; font-weight: bold; width: 100%;">Débito On-Line:</li>' +
                    '<li style="display: inline-block; padding: 5px 15px;" class="none">nenhuma opção disponível</li>' +
                    '</ul>')
                } else if (i === 'CREDIT_CARD') {
                  body.append('<ul id="' + i + '">' +
                    '<li style="display: inline-block; font-weight: bold; width: 100%;">Cartão de Crédito:</li>' +
                    '<li style="display: inline-block; padding: 5px 15px;" class="none">nenhuma opção disponível</li>' +
                    '</ul>')
                } else if (i === 'BOLETO') {
                  body.append('<ul id="' + i + '">' +
                    '<li style="display: inline-block; font-weight: bold; width: 100%;">Boleto:</li>' +
                    '<li style="display: inline-block; padding: 5px 15px;" class="none">nenhuma opção disponível</li>' +
                    '</ul>')
                } else {
                  body.append('<ul id="' + i + '">' +
                    '<li style="display: inline-block; font-weight: bold; width: 100%;">' + i + ':</li>' +
                    '<li style="display: inline-block; padding: 5px 15px;" class="none">nenhuma opção disponível</li>' +
                    '</ul>')
                }
                jQuery.each(items['options'], function (k, item) {
                  if (item['status'] === 'AVAILABLE') {
                    body.find('ul#' + i).find('.none').hide()
                    body.find('ul#' + i).append('<li style="display: inline-block; padding: 5px 15px;">' + item['displayName'] + '</li>')
                  }
                })
              }
            })
          } else {
            body.empty()
            body.append('<p>Erro</p>')
          }
        },
        error: function () {
          body.empty()
          body.append('<p>Erro</p>')
        }
      })
    }
  })
})
