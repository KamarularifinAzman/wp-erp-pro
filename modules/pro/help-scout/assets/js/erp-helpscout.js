/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "./modules/pro/help-scout/assets/js/src/erp-helpscout.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./modules/pro/help-scout/assets/js/src/erp-helpscout.js":
/*!***************************************************************!*\
  !*** ./modules/pro/help-scout/assets/js/src/erp-helpscout.js ***!
  \***************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

eval("/**\n * ERP Help Scout - v0.1.0 - 2017-08-21\n * http://wperp.com\n *\n * Copyright (c) 2017;\n * Licensed GPLv2+\n */\n\n/*jslint browser: true */\n\n/*global jQuery:false */\nwindow.Erp_Help_Scout = function (window, document, $, undefined) {\n  'use strict';\n\n  var app = {};\n\n  app.init = function () {\n    var contact_id = null;\n\n    if (erpSC.contact_id !== null && erpSC.contact_id !== undefined) {\n      contact_id = erpSC.contact_id;\n      var data = {\n        action: 'erp_helpscout_contact_hc_activity',\n        contact_id: contact_id,\n        nonce: erpSC.nonce\n      };\n      $.post(erpSC.ajaxurl, data, function (resp) {\n        $('.erp-helpscout-activity-hs').removeClass('loading');\n        var contentArea = $('.erp-helpscout-activity-hs .inside');\n        console.log(resp);\n\n        if (resp.success) {\n          contentArea.html(resp.data.msg);\n        } else {\n          //error\n          if (resp.data.msg) {\n            contentArea.html('<p>' + resp.data.msg + '</p>');\n          }\n        }\n      });\n    }\n\n    $('#helpscout-send-message').on('click', function (e) {\n      e.preventDefault();\n      app.send_message();\n    });\n    $('#erp_helpscout_sync_form').on('submit', function (e) {\n      e.preventDefault();\n      app.helpscout_contact_sync();\n    });\n    $('#mailbox').on('change', function (e) {\n      var mailbox_id = $('#mailbox').val();\n      var userLoader = $('.user-loader');\n      userLoader.show();\n      var data = {\n        'action': 'get_helpscout_user',\n        'mailbox_id': mailbox_id\n      };\n      $.post(erpSC.ajaxurl, data, function (res) {\n        if (res.success) {\n          userLoader.hide();\n          $('#users').empty();\n          $.each(res.data, function (index, user) {\n            $('#users').append('<option value=\"' + user.id + '\">' + user.firstName + ' ' + user.lastName + '</option>');\n          });\n        }\n      });\n    });\n  };\n\n  app.send_message = function () {\n    var mailbox = $('#mailbox').val();\n    var user = $('#users').val();\n    var subject = $('#helpscout_subject').val();\n    var message = $('#helpscout-message').val();\n    var email = $('#customer-email').val();\n    var submit = $('input[name=submit-helpscout-message]');\n    var loader = $('.sync-loader');\n    var helpscoutResponse = $('#response_div');\n\n    if (mailbox == '' || user == '' || subject == '' || message == '') {\n      helpscoutResponse.html('<span> Please fill up all field ! </span>');\n      return;\n    }\n\n    loader.show();\n    submit.attr('disabled', 'disabled');\n    helpscoutResponse.html('');\n    var data = {\n      mailbox: mailbox,\n      user: user,\n      subject: subject,\n      message: message,\n      email: email,\n      action: 'helpscout_send_message'\n    };\n    $.post(erpSC.ajaxurl, data, function (res) {\n      helpscoutResponse.html('<span>' + res.data.msg + '</span>');\n      loader.hide();\n      submit.removeAttr('disabled');\n      $('#mailbox').val('');\n      $('#users').val('');\n      $('#helpscout_subject').val('');\n      $('#helpscout-message').val('');\n    });\n  };\n\n  app.helpscout_contact_sync = function () {\n    var form = $('#erp_helpscout_sync_form'),\n        mailboxIds = form.find(\"input[name=mailbox]:checked\").map(function () {\n      return $(this).val();\n    }).get(),\n        lifeStage = form.find(\"select[name=helpscout_life_stage]\").val(),\n        contact_group = form.find(\"input[name=group_ids]:checked\").map(function () {\n      return $(this).val();\n    }).get(),\n        submit = form.find(\"input[type=submit]\"),\n        loader = form.find('.sync-loader'),\n        response = form.find('#response_div'),\n        data = {\n      'action': 'helpscout_contact_sync',\n      'mailboxes': mailboxIds,\n      'contact_group': contact_group,\n      'life_stage': lifeStage\n    };\n    submit.attr('disabled', 'disabled');\n    loader.show();\n    $.post(erpSC.ajaxurl, data, function (res) {\n      if (res.success) {\n        response.html('<span>' + res.data.message + '</span>'), submit.removeAttr('disabled');\n        loader.hide();\n      }\n    });\n  };\n\n  $(document).ready(app.init);\n}(window, document, jQuery);\n\n//# sourceURL=webpack:///./modules/pro/help-scout/assets/js/src/erp-helpscout.js?");

/***/ })

/******/ });