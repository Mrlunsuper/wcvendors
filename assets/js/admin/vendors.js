(function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
"use strict";

jQuery(document).ready(function ($) {
  $('.vendor_enable_disable').on('change', function (e) {
    e.preventDefault();
    var that = $(this);
    var vendor_id = that.val();
    var data = {
      action: 'enable_vendor',
      vendor_id: vendor_id,
      security: wcv_vendors_table_params.nonce
    };
    $.ajax({
      method: 'POST',
      url: ajaxurl,
      data: data,
      beforeSend: function beforeSend() {
        console.log('Sending ajax');
      },
      success: function success(response) {
        console.log(response);
      },
      error: function error(err) {
        console.log(err);
      }
    });
  });
  $('.delete_vendor').each(function (i, link) {
    $(link).on('click', function (e) {
      if (!window.confirm(wcv_vendors_table_params.confirm_delete)) {
        e.preventDefault();
      }
    });
  });
  $('#wcv-vendors-table').on('submit', function (e) {
    var action = document.getElementById('bulk-action-selector-top');
    var action_value = action.value;

    if ('delete' === action_value) {
      if (!window.confirm(wcv_vendors_table_params.confirm_bulk_delete)) {
        e.preventDefault();
      }
    }
  });
});

},{}]},{},[1])

//# sourceMappingURL=vendors.js.map
