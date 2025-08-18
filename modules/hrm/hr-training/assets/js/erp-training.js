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
/******/ 	return __webpack_require__(__webpack_require__.s = "./modules/hrm/hr-training/assets/js/src/erp-training.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./modules/hrm/hr-training/assets/js/src/erp-training.js":
/*!***************************************************************!*\
  !*** ./modules/hrm/hr-training/assets/js/src/erp-training.js ***!
  \***************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

eval(";\n\n(function ($) {\n  var ERP_Training = {\n    /**\n     * Initialize the events\n     * \n     * @return void\n     */\n    initialize: function () {\n      $('#erp-training-selected-employee').on('click', this.selectEmployee);\n      $('#erp-add-new-employee-training').on('click', this.assignEmployee);\n      $('#erp-add-new-employee-training').on('click', this.assignEmployee);\n      $('.erp-edit-employee-training').on('click', this.edit);\n      $('.erp-delete-employee-training').on('click', this.delete);\n      $('.check-incompleted-training').on('click', this.checkIncomplete);\n      $('.training_participant').on('click', this.getParticipant); // $('.editinline').on( 'click', this.getPostId );     \n    },\n    selectEmployee: function () {\n      $.erpPopup({\n        title: 'Add Employees',\n        button: 'Done',\n        id: 'employee-training',\n        content: wperp.template('employee-training'),\n        extraClass: 'smaller',\n        onReady: function () {\n          $('.erp-select2').select2();\n        },\n        onSubmit: function (modal) {\n          var designations = $('input[name=\"designations\"]:checked').map(function () {\n            return $(this).val();\n          }).get();\n          var departments = $('input[name=\"departments\"]:checked').map(function () {\n            return $(this).val();\n          }).get();\n          var employees = $('#selected_employee').val();\n          console.log(employees);\n          $('#erp-training-department').val(departments);\n          $('#erp-training-designation').val(designations);\n          $('#erp-training-selected-employee').val(employees);\n          var data = {\n            'action': 'erp_training_employee_count',\n            'departments': departments,\n            'designations': designations,\n            'employees': employees\n          };\n          wp.ajax.send({\n            data: data,\n            success: function (res) {\n              $('#erp-training-selected-employee').val('Selected Employee(' + res.count + ')');\n              console.log(res);\n            },\n            error: function (error) {\n              modal.showError(error);\n            }\n          });\n          modal.closeModal();\n        }\n      });\n    },\n    assignEmployee: function (e) {\n      e.preventDefault();\n      $.erpPopup({\n        title: 'Assign new training',\n        button: 'Assign Training',\n        id: 'assign-new-training',\n        content: wperp.template('employee-assign-new-training'),\n        extraClass: 'smaller',\n        onReady: function () {\n          $('#training-completed-date').datepicker({\n            dateFormat: 'yy-mm-dd',\n            yearRange: \"-100:+0\",\n            changeMonth: true,\n            changeYear: true\n          });\n        },\n        onSubmit: function (modal) {\n          var data = $(this).serialize();\n          wp.ajax.send({\n            data: data,\n            success: function (res) {\n              document.location.reload(true);\n            },\n            erro: function (error) {}\n          });\n          modal.closeModal();\n        }\n      });\n    },\n    edit: function (e) {\n      e.preventDefault();\n      var dataId = $(this).attr('data-id');\n      var profileId = $(this).attr('id');\n      var url = window.location.href;\n      $.erpPopup({\n        title: 'Assign new training',\n        button: 'Update Training',\n        id: 'assign-edit-training',\n        content: wperp.template('employee-assign-new-training'),\n        extraClass: 'smaller',\n        onReady: function () {\n          $('#training-completed-date').datepicker({\n            dateFormat: 'yy-mm-dd',\n            yearRange: \"-100:+0\",\n            changeMonth: true,\n            changeYear: true\n          });\n          $('#training-rate').keyup(function (e) {\n            var max = parseInt(e.target.max);\n            var min = parseInt(e.target.min);\n\n            if (parseInt(e.target.value) > max) {\n              e.target.value = max;\n            }\n\n            if (parseInt(e.target.value) < min) {\n              e.target.value = min;\n            }\n          });\n          wp.ajax.send('erp_get_training', {\n            data: {\n              post_id: dataId,\n              user_id: profileId\n            },\n            success: function (res) {\n              $('#training-id').val(res.post_id);\n              $('#training-completed-date').val(res.erp_training_completed_date);\n              $('#training-trainer').val(res.erp_training_trainer);\n              $('#trainer-phone').val(res.erp_trainer_phone);\n              $('#training-cost').val(res.erp_training_cost);\n              $('#training-credit').val(res.erp_training_credit);\n              $('#training-hours').val(res.erp_training_hours);\n              $('#training-notes').val(res.erp_training_note);\n              $('#training-rate').val(res.erp_training_rate);\n            },\n            erro: function (error) {}\n          });\n        },\n        onSubmit: function (modal) {\n          var data = $(this).serialize();\n          wp.ajax.send('erp_get_training', {\n            data: $(this).serialize(),\n            success: function (res) {\n              document.location.reload(true);\n            },\n            erro: function (error) {}\n          });\n          modal.closeModal();\n        }\n      });\n    },\n    delete: function (e) {\n      e.preventDefault();\n\n      if (confirm('Are sure want to incompleted this ?')) {\n        wp.ajax.send('erp_delete_training', {\n          data: {\n            post_id: $(this).attr('data-id'),\n            user_id: $(this).attr('id')\n          },\n          success: function (res) {\n            document.location.reload(true);\n          }\n        });\n      }\n    },\n    checkIncomplete: function (e) {\n      e.preventDefault();\n\n      if (confirm('Are sure incompleted this ?')) {\n        var user_id = $(this).attr('user_id');\n        var post_id = $(this).attr('post_id');\n        var data = {\n          action: 'erp_training_incompleted',\n          post_id: post_id,\n          user_id: user_id\n        };\n        wp.ajax.send({\n          data: data,\n          success: function (res) {\n            document.location.reload(true);\n          }\n        });\n      }\n    },\n    getPostId: function () {\n      $(this).attr('id');\n      console.log('Hello');\n    },\n    getParticipant: function (e) {\n      e.preventDefault();\n      var pr_id = e.target.id.split(\"_\")[1];\n      $.erpPopup({\n        title: \"View Participants\",\n        id: \"pr_id\" + pr_id,\n        extraClass: 'training_participant_popup',\n        content: $('#training_participant_' + pr_id).html()\n      });\n    }\n  };\n  $(function () {\n    ERP_Training.initialize();\n  });\n})(jQuery);\n\n//# sourceURL=webpack:///./modules/hrm/hr-training/assets/js/src/erp-training.js?");

/***/ })

/******/ });