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
/******/ 			Object.defineProperty(exports, name, {
/******/ 				configurable: false,
/******/ 				enumerable: true,
/******/ 				get: getter
/******/ 			});
/******/ 		}
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
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 0);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(1);
(function webpackMissingModule() { throw new Error("Cannot find module \"/home/tuantn1807/project/TaskPilot/resources/sass/app.scss\""); }());


/***/ }),
/* 1 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
throw new Error("Cannot find module \"vue\"");
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__css_edit_shared_css__ = __webpack_require__(2);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__css_edit_shared_css___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_1__css_edit_shared_css__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__components_ProjectCreate_vue__ = __webpack_require__(7);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__components_ProjectCreate_vue___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_2__components_ProjectCreate_vue__);




var d = window.projectCreateData || {};

var app = Object(__WEBPACK_IMPORTED_MODULE_0_vue__["createApp"])(__WEBPACK_IMPORTED_MODULE_2__components_ProjectCreate_vue__["default"], {
    companies: d.companies || [],
    categories: d.categories || [],
    teams: d.teams || [],
    projectCodePreview: d.project_code_preview || '',
    companyId: d.company_id || null,
    errors: d.errors || {},
    old: d.old || {},
    storeUrl: d.store_url || '/projects',
    categoryStoreUrl: d.category_store_url || '',
    companyStoreUrl: d.company_store_url || '',
    csrfToken: d.csrf_token || '',
    projectsIndexUrl: d.projects_index_url || '/projects'
});

app.mount('#project-create-app');

/***/ }),
/* 2 */
/***/ (function(module, exports, __webpack_require__) {

// style-loader: Adds some css to the DOM by adding a <style> tag

// load the styles
var content = __webpack_require__(3);
if(typeof content === 'string') content = [[module.i, content, '']];
// Prepare cssTransformation
var transform;

var options = {}
options.transform = transform
// add the styles to the DOM
var update = __webpack_require__(5)(content, options);
if(content.locals) module.exports = content.locals;
// Hot Module Replacement
if(false) {
	// When the styles change, update the <style> tags
	if(!content.locals) {
		module.hot.accept("!!../../node_modules/css-loader/index.js!./edit-shared.css", function() {
			var newContent = require("!!../../node_modules/css-loader/index.js!./edit-shared.css");
			if(typeof newContent === 'string') newContent = [[module.id, newContent, '']];
			update(newContent);
		});
	}
	// When the module is disposed, remove the <style> tags
	module.hot.dispose(function() { update(); });
}

/***/ }),
/* 3 */
/***/ (function(module, exports, __webpack_require__) {

exports = module.exports = __webpack_require__(4)(false);
// imports
exports.push([module.i, "@import url(https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap);", ""]);

// module
exports.push([module.i, ".project-edit-form,\n.project-create-form {\n    max-width: 1200px;\n    max-height: 90vh;\n    overflow-y: auto;\n    font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;\n}\n\n.edit-grid {\n    display: grid;\n    grid-template-columns: 1fr 380px;\n    gap: 24px;\n    align-items: stretch;\n}\n\n.edit-col {\n    display: flex;\n    flex-direction: column;\n    gap: 20px;\n    height: 100%;\n}\n\n.edit-card {\n    background: #fff;\n    border: 1px solid #e2e8f0;\n    border-radius: 12px;\n    box-shadow: 0 1px 3px rgba(0,0,0,0.04);\n    overflow: hidden;\n    display: flex;\n    flex-direction: column;\n}\n\n.edit-col:first-child .edit-card {\n    flex: 1;\n}\n\n.edit-card-header {\n    padding: 20px 24px;\n    border-bottom: 1px solid #e2e8f0;\n    background: #f8fafc;\n    flex-shrink: 0;\n}\n\n.edit-card-title {\n    margin: 0;\n    font-size: 14px;\n    font-weight: 500;\n    color: #172033;\n    display: flex;\n    align-items: center;\n    gap: 8px;\n    font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;\n}\n\n.edit-card-title i {\n    color: #2563eb;\n    font-size: 14px;\n}\n\n.edit-card-desc {\n    margin: 4px 0 0;\n    font-size: 12px;\n    color: #64748b;\n    font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;\n}\n\n.edit-card-body {\n    padding: 24px;\n    flex: 1;\n}\n\n.edit-card-actions {\n    background: #fff;\n    flex-shrink: 0;\n}\n\n.edit-field {\n    display: flex;\n    flex-direction: column;\n    gap: 6px;\n    margin-bottom: 18px;\n}\n\n.edit-field:last-child {\n    margin-bottom: 0;\n}\n\n.edit-field label {\n    font-size: 12px;\n    font-weight: 500;\n    color: #334155;\n    letter-spacing: 0.01em;\n    font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;\n}\n\n.required {\n    color: #dc2626;\n    margin-left: 2px;\n}\n\n.edit-input {\n    height: 40px;\n    padding: 0 12px;\n    border: 1px solid #cbd5e1;\n    border-radius: 8px;\n    background: #fff;\n    color: #172033;\n    font-size: 13px;\n    font-weight: 500;\n    font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;\n    outline: none;\n    transition: all 0.15s ease;\n    width: 100%;\n    box-sizing: border-box;\n}\n\n.edit-textarea {\n    min-height: 72px;\n    height: auto !important;\n    padding: 8px 12px !important;\n    border: 1px solid #cbd5e1;\n    border-radius: 8px;\n    background: #fff;\n    color: #172033;\n    font-size: 13px;\n    font-weight: 500;\n    font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;\n    outline: none;\n    width: 100%;\n    box-sizing: border-box;\n    transition: all 0.15s ease;\n    resize: vertical;\n}\n\n.edit-input:focus {\n    border-color: #2563eb;\n    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);\n}\n\n.edit-textarea:focus {\n    border-color: #2563eb;\n    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);\n}\n\n.edit-input.is-invalid {\n    border-color: #dc2626;\n    background: #fef2f2;\n}\n\n.edit-input::placeholder {\n    color: #94a3b8;\n    font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;\n    font-size: 13px;\n}\n\n.edit-select-wrapper {\n    position: relative;\n    display: flex;\n    align-items: center;\n}\n\n.edit-select {\n    width: 100%;\n    height: 40px;\n    padding: 0 36px 0 12px;\n    border: 1px solid #cbd5e1;\n    border-radius: 8px;\n    background: #fff;\n    color: #172033;\n    font-size: 13px;\n    font-weight: 500;\n    font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;\n    outline: none;\n    appearance: none;\n    cursor: pointer;\n    transition: all 0.15s ease;\n}\n\n.edit-select:focus {\n    border-color: #2563eb;\n    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);\n}\n\n.edit-select.is-invalid {\n    border-color: #dc2626;\n    background: #fef2f2;\n}\n\n.edit-select-icon {\n    position: absolute;\n    right: 12px;\n    color: #64748b;\n    font-size: 12px;\n    pointer-events: none;\n}\n\n.edit-quick-add {\n    position: absolute;\n    right: 32px;\n    width: 26px;\n    height: 26px;\n    display: inline-flex;\n    align-items: center;\n    justify-content: center;\n    border: 1px solid #e2e8f0;\n    border-radius: 6px;\n    background: #fff;\n    color: #2563eb;\n    cursor: pointer;\n    font-size: 11px;\n    transition: all 0.15s ease;\n}\n\n.edit-quick-add:hover {\n    background: #2563eb;\n    border-color: #2563eb;\n    color: #fff;\n}\n\n.edit-input-group {\n    display: flex;\n    align-items: stretch;\n}\n\n.edit-input-group .edit-input {\n    border-top-right-radius: 0;\n    border-bottom-right-radius: 0;\n    flex: 1;\n}\n\n.edit-input-addon {\n    display: inline-flex;\n    align-items: center;\n    padding: 0 12px;\n    background: #f1f5f9;\n    border: 1px solid #cbd5e1;\n    border-left: none;\n    border-radius: 0 8px 8px 0;\n    color: #64748b;\n    font-size: 12px;\n    font-weight: 600;\n    font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;\n    white-space: nowrap;\n}\n\n.edit-row {\n    display: grid;\n    grid-template-columns: repeat(2, 1fr);\n    gap: 16px;\n}\n\n.edit-error {\n    font-size: 11px;\n    color: #dc2626;\n    font-weight: 500;\n    margin-top: 2px;\n    font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;\n}\n\n.edit-hint {\n    font-size: 11px;\n    color: #94a3b8;\n    margin-top: 4px;\n    font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;\n}\n\n.edit-btn {\n    display: inline-flex;\n    align-items: center;\n    justify-content: center;\n    gap: 8px;\n    padding: 8px 16px;\n    border: 1px solid transparent;\n    border-radius: 8px;\n    font-size: 12px;\n    font-weight: 500;\n    font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;\n    cursor: pointer;\n    transition: all 0.15s ease;\n    margin-bottom: 10px;\n}\n\n.edit-btn:last-child {\n    margin-bottom: 0;\n}\n\n.edit-btn-primary {\n    background: #2563eb;\n    border-color: #2563eb;\n    color: #fff;\n}\n\n.edit-btn-primary:hover {\n    background: #1d4ed8;\n    border-color: #1d4ed8;\n}\n\n.edit-btn-secondary {\n    background: #fff;\n    border-color: #e2e8f0;\n    color: #475569;\n}\n\n.edit-btn-secondary:hover {\n    background: #f8fafc;\n    border-color: #cbd5e1;\n    color: #172033;\n}\n\n.edit-modal-overlay {\n    position: fixed;\n    inset: 0;\n    background: rgba(15, 23, 42, 0.4);\n    backdrop-filter: blur(4px);\n    display: flex;\n    align-items: center;\n    justify-content: center;\n    z-index: 1050;\n    padding: 20px;\n}\n\n.edit-modal {\n    background: #fff;\n    border-radius: 12px;\n    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);\n    width: 100%;\n    max-width: 520px;\n    max-height: 90vh;\n    overflow-y: auto;\n}\n\n.edit-modal-header {\n    display: flex;\n    align-items: center;\n    justify-content: space-between;\n    padding: 20px 24px;\n    border-bottom: 1px solid #e2e8f0;\n    background: #f8fafc;\n}\n\n.edit-modal-header h3 {\n    margin: 0;\n    font-size: 15px;\n    font-weight: 500;\n    color: #172033;\n    font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;\n}\n\n.edit-modal-close {\n    width: 32px;\n    height: 32px;\n    display: inline-flex;\n    align-items: center;\n    justify-content: center;\n    border: none;\n    border-radius: 6px;\n    background: transparent;\n    color: #64748b;\n    font-size: 20px;\n    cursor: pointer;\n    transition: all 0.15s ease;\n}\n\n.edit-modal-close:hover {\n    background: #e2e8f0;\n    color: #172033;\n}\n\n.edit-modal-body {\n    padding: 24px;\n}\n\n.edit-modal-body label {\n    display: block;\n    font-size: 12px;\n    font-weight: 500;\n    color: #334155;\n    margin-bottom: 6px;\n    margin-top: 14px;\n    font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;\n}\n\n.edit-modal-body label:first-child {\n    margin-top: 0;\n}\n\n.edit-modal-body input::placeholder,\n.edit-modal-body textarea::placeholder {\n    color: #94a3b8;\n    font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;\n}\n\n.edit-modal-footer {\n    display: flex;\n    align-items: center;\n    justify-content: flex-end;\n    gap: 10px;\n    padding: 16px 24px;\n    border-top: 1px solid #e2e8f0;\n    background: #f8fafc;\n}\n\n.edit-modal-footer .edit-btn {\n    width: auto;\n    margin-bottom: 0;\n    min-width: 100px;\n}\n\n.edit-image-upload {\n    display: flex;\n    align-items: stretch;\n    gap: 12px;\n    width: fit-content;\n}\n\n.edit-image-placeholder,\n.edit-image-preview {\n    display: flex;\n    align-items: center;\n    justify-content: center;\n    width: 160px;\n    height: 80px;\n    border: 2px dashed #cbd5e1;\n    border-radius: 8px;\n    background: #f8fafc;\n    color: #94a3b8;\n    font-size: 11px;\n    font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;\n    overflow: hidden;\n    flex-shrink: 0;\n    position: relative;\n}\n\n.edit-image-placeholder i {\n    font-size: 20px;\n    display: block;\n}\n\n.edit-image-thumb {\n    width: 100%;\n    height: 100%;\n    object-fit: cover;\n}\n\n.edit-image-clear {\n    position: absolute;\n    top: 4px;\n    right: 4px;\n    width: 16px;\n    height: 16px;\n    border-radius: 4px;\n    background: rgba(0,0,0,0.5);\n    color: #fff;\n    display: flex;\n    align-items: center;\n    justify-content: center;\n    border: none;\n    cursor: pointer;\n    font-size: 10px;\n}\n\n.edit-image-btn {\n    display: inline-flex;\n    align-items: center;\n    gap: 6px;\n    padding: 0 14px;\n    background: #f1f5f9;\n    border: 1px solid #cbd5e1;\n    border-radius: 8px;\n    color: #475569;\n    font-size: 12px;\n    font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;\n    cursor: pointer;\n    transition: all 0.15s ease;\n    height: 40px;\n}\n\n.edit-image-btn:hover {\n    background: #e2e8f0;\n}\n\n.edit-image-input {\n    display: none;\n}\n\n.quill-editor {\n    min-height: 100px;\n    border: 1px solid #cbd5e1;\n    border-radius: 8px;\n    background: #fff;\n    padding: 8px 12px;\n    font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;\n}\n\n.quill-editor .ql-toolbar {\n    border: none;\n    border-bottom: 1px solid #e2e8f0;\n    background: #f8fafc;\n    padding: 4px 10px;\n}\n\n.quill-editor .ql-container {\n    border: none;\n    font-size: 13px;\n    font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;\n}\n\n.edit-member-input {\n    display: flex;\n    flex-wrap: wrap;\n    align-items: center;\n    gap: 6px;\n    min-height: 42px;\n    padding: 6px 12px;\n    border: 1px solid #cbd5e1;\n    border-radius: 8px;\n    background: #fff;\n    cursor: text;\n    transition: all 0.15s ease;\n}\n\n.edit-member-input:focus-within {\n    border-color: #2563eb;\n    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);\n}\n\n.edit-member-tag {\n    display: inline-flex;\n    align-items: center;\n    gap: 6px;\n    padding: 4px 10px;\n    background: #eff6ff;\n    border: 1px solid #bfdbfe;\n    border-radius: 6px;\n    color: #1d4ed8;\n    font-size: 12px;\n    font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;\n}\n\n.edit-member-tag i {\n    cursor: pointer;\n    color: #2563eb;\n}\n\n.edit-member-text {\n    border: none;\n    outline: none;\n    background: transparent;\n    flex: 1;\n    min-width: 120px;\n    padding: 4px 0;\n    font-size: 13px;\n    font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;\n    color: #172033;\n}\n\n.edit-member-text::placeholder {\n    color: #94a3b8;\n    font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;\n}\n\n@media (max-width: 1024px) {\n    .edit-grid {\n        grid-template-columns: 1fr;\n    }\n}\n\n@media (max-width: 768px) {\n    .edit-card-body {\n        padding: 16px;\n    }\n\n    .edit-row {\n        grid-template-columns: 1fr;\n    }\n}\n", ""]);

// exports


/***/ }),
/* 4 */
/***/ (function(module, exports) {

/*
	MIT License http://www.opensource.org/licenses/mit-license.php
	Author Tobias Koppers @sokra
*/
// css base code, injected by the css-loader
module.exports = function(useSourceMap) {
	var list = [];

	// return the list of modules as css string
	list.toString = function toString() {
		return this.map(function (item) {
			var content = cssWithMappingToString(item, useSourceMap);
			if(item[2]) {
				return "@media " + item[2] + "{" + content + "}";
			} else {
				return content;
			}
		}).join("");
	};

	// import a list of modules into the list
	list.i = function(modules, mediaQuery) {
		if(typeof modules === "string")
			modules = [[null, modules, ""]];
		var alreadyImportedModules = {};
		for(var i = 0; i < this.length; i++) {
			var id = this[i][0];
			if(typeof id === "number")
				alreadyImportedModules[id] = true;
		}
		for(i = 0; i < modules.length; i++) {
			var item = modules[i];
			// skip already imported module
			// this implementation is not 100% perfect for weird media query combinations
			//  when a module is imported multiple times with different media queries.
			//  I hope this will never occur (Hey this way we have smaller bundles)
			if(typeof item[0] !== "number" || !alreadyImportedModules[item[0]]) {
				if(mediaQuery && !item[2]) {
					item[2] = mediaQuery;
				} else if(mediaQuery) {
					item[2] = "(" + item[2] + ") and (" + mediaQuery + ")";
				}
				list.push(item);
			}
		}
	};
	return list;
};

function cssWithMappingToString(item, useSourceMap) {
	var content = item[1] || '';
	var cssMapping = item[3];
	if (!cssMapping) {
		return content;
	}

	if (useSourceMap && typeof btoa === 'function') {
		var sourceMapping = toComment(cssMapping);
		var sourceURLs = cssMapping.sources.map(function (source) {
			return '/*# sourceURL=' + cssMapping.sourceRoot + source + ' */'
		});

		return [content].concat(sourceURLs).concat([sourceMapping]).join('\n');
	}

	return [content].join('\n');
}

// Adapted from convert-source-map (MIT)
function toComment(sourceMap) {
	// eslint-disable-next-line no-undef
	var base64 = btoa(unescape(encodeURIComponent(JSON.stringify(sourceMap))));
	var data = 'sourceMappingURL=data:application/json;charset=utf-8;base64,' + base64;

	return '/*# ' + data + ' */';
}


/***/ }),
/* 5 */
/***/ (function(module, exports, __webpack_require__) {

/*
	MIT License http://www.opensource.org/licenses/mit-license.php
	Author Tobias Koppers @sokra
*/

var stylesInDom = {};

var	memoize = function (fn) {
	var memo;

	return function () {
		if (typeof memo === "undefined") memo = fn.apply(this, arguments);
		return memo;
	};
};

var isOldIE = memoize(function () {
	// Test for IE <= 9 as proposed by Browserhacks
	// @see http://browserhacks.com/#hack-e71d8692f65334173fee715c222cb805
	// Tests for existence of standard globals is to allow style-loader
	// to operate correctly into non-standard environments
	// @see https://github.com/webpack-contrib/style-loader/issues/177
	return window && document && document.all && !window.atob;
});

var getElement = (function (fn) {
	var memo = {};

	return function(selector) {
		if (typeof memo[selector] === "undefined") {
			memo[selector] = fn.call(this, selector);
		}

		return memo[selector]
	};
})(function (target) {
	return document.querySelector(target)
});

var singleton = null;
var	singletonCounter = 0;
var	stylesInsertedAtTop = [];

var	fixUrls = __webpack_require__(6);

module.exports = function(list, options) {
	if (typeof DEBUG !== "undefined" && DEBUG) {
		if (typeof document !== "object") throw new Error("The style-loader cannot be used in a non-browser environment");
	}

	options = options || {};

	options.attrs = typeof options.attrs === "object" ? options.attrs : {};

	// Force single-tag solution on IE6-9, which has a hard limit on the # of <style>
	// tags it will allow on a page
	if (!options.singleton) options.singleton = isOldIE();

	// By default, add <style> tags to the <head> element
	if (!options.insertInto) options.insertInto = "head";

	// By default, add <style> tags to the bottom of the target
	if (!options.insertAt) options.insertAt = "bottom";

	var styles = listToStyles(list, options);

	addStylesToDom(styles, options);

	return function update (newList) {
		var mayRemove = [];

		for (var i = 0; i < styles.length; i++) {
			var item = styles[i];
			var domStyle = stylesInDom[item.id];

			domStyle.refs--;
			mayRemove.push(domStyle);
		}

		if(newList) {
			var newStyles = listToStyles(newList, options);
			addStylesToDom(newStyles, options);
		}

		for (var i = 0; i < mayRemove.length; i++) {
			var domStyle = mayRemove[i];

			if(domStyle.refs === 0) {
				for (var j = 0; j < domStyle.parts.length; j++) domStyle.parts[j]();

				delete stylesInDom[domStyle.id];
			}
		}
	};
};

function addStylesToDom (styles, options) {
	for (var i = 0; i < styles.length; i++) {
		var item = styles[i];
		var domStyle = stylesInDom[item.id];

		if(domStyle) {
			domStyle.refs++;

			for(var j = 0; j < domStyle.parts.length; j++) {
				domStyle.parts[j](item.parts[j]);
			}

			for(; j < item.parts.length; j++) {
				domStyle.parts.push(addStyle(item.parts[j], options));
			}
		} else {
			var parts = [];

			for(var j = 0; j < item.parts.length; j++) {
				parts.push(addStyle(item.parts[j], options));
			}

			stylesInDom[item.id] = {id: item.id, refs: 1, parts: parts};
		}
	}
}

function listToStyles (list, options) {
	var styles = [];
	var newStyles = {};

	for (var i = 0; i < list.length; i++) {
		var item = list[i];
		var id = options.base ? item[0] + options.base : item[0];
		var css = item[1];
		var media = item[2];
		var sourceMap = item[3];
		var part = {css: css, media: media, sourceMap: sourceMap};

		if(!newStyles[id]) styles.push(newStyles[id] = {id: id, parts: [part]});
		else newStyles[id].parts.push(part);
	}

	return styles;
}

function insertStyleElement (options, style) {
	var target = getElement(options.insertInto)

	if (!target) {
		throw new Error("Couldn't find a style target. This probably means that the value for the 'insertInto' parameter is invalid.");
	}

	var lastStyleElementInsertedAtTop = stylesInsertedAtTop[stylesInsertedAtTop.length - 1];

	if (options.insertAt === "top") {
		if (!lastStyleElementInsertedAtTop) {
			target.insertBefore(style, target.firstChild);
		} else if (lastStyleElementInsertedAtTop.nextSibling) {
			target.insertBefore(style, lastStyleElementInsertedAtTop.nextSibling);
		} else {
			target.appendChild(style);
		}
		stylesInsertedAtTop.push(style);
	} else if (options.insertAt === "bottom") {
		target.appendChild(style);
	} else {
		throw new Error("Invalid value for parameter 'insertAt'. Must be 'top' or 'bottom'.");
	}
}

function removeStyleElement (style) {
	if (style.parentNode === null) return false;
	style.parentNode.removeChild(style);

	var idx = stylesInsertedAtTop.indexOf(style);
	if(idx >= 0) {
		stylesInsertedAtTop.splice(idx, 1);
	}
}

function createStyleElement (options) {
	var style = document.createElement("style");

	options.attrs.type = "text/css";

	addAttrs(style, options.attrs);
	insertStyleElement(options, style);

	return style;
}

function createLinkElement (options) {
	var link = document.createElement("link");

	options.attrs.type = "text/css";
	options.attrs.rel = "stylesheet";

	addAttrs(link, options.attrs);
	insertStyleElement(options, link);

	return link;
}

function addAttrs (el, attrs) {
	Object.keys(attrs).forEach(function (key) {
		el.setAttribute(key, attrs[key]);
	});
}

function addStyle (obj, options) {
	var style, update, remove, result;

	// If a transform function was defined, run it on the css
	if (options.transform && obj.css) {
	    result = options.transform(obj.css);

	    if (result) {
	    	// If transform returns a value, use that instead of the original css.
	    	// This allows running runtime transformations on the css.
	    	obj.css = result;
	    } else {
	    	// If the transform function returns a falsy value, don't add this css.
	    	// This allows conditional loading of css
	    	return function() {
	    		// noop
	    	};
	    }
	}

	if (options.singleton) {
		var styleIndex = singletonCounter++;

		style = singleton || (singleton = createStyleElement(options));

		update = applyToSingletonTag.bind(null, style, styleIndex, false);
		remove = applyToSingletonTag.bind(null, style, styleIndex, true);

	} else if (
		obj.sourceMap &&
		typeof URL === "function" &&
		typeof URL.createObjectURL === "function" &&
		typeof URL.revokeObjectURL === "function" &&
		typeof Blob === "function" &&
		typeof btoa === "function"
	) {
		style = createLinkElement(options);
		update = updateLink.bind(null, style, options);
		remove = function () {
			removeStyleElement(style);

			if(style.href) URL.revokeObjectURL(style.href);
		};
	} else {
		style = createStyleElement(options);
		update = applyToTag.bind(null, style);
		remove = function () {
			removeStyleElement(style);
		};
	}

	update(obj);

	return function updateStyle (newObj) {
		if (newObj) {
			if (
				newObj.css === obj.css &&
				newObj.media === obj.media &&
				newObj.sourceMap === obj.sourceMap
			) {
				return;
			}

			update(obj = newObj);
		} else {
			remove();
		}
	};
}

var replaceText = (function () {
	var textStore = [];

	return function (index, replacement) {
		textStore[index] = replacement;

		return textStore.filter(Boolean).join('\n');
	};
})();

function applyToSingletonTag (style, index, remove, obj) {
	var css = remove ? "" : obj.css;

	if (style.styleSheet) {
		style.styleSheet.cssText = replaceText(index, css);
	} else {
		var cssNode = document.createTextNode(css);
		var childNodes = style.childNodes;

		if (childNodes[index]) style.removeChild(childNodes[index]);

		if (childNodes.length) {
			style.insertBefore(cssNode, childNodes[index]);
		} else {
			style.appendChild(cssNode);
		}
	}
}

function applyToTag (style, obj) {
	var css = obj.css;
	var media = obj.media;

	if(media) {
		style.setAttribute("media", media)
	}

	if(style.styleSheet) {
		style.styleSheet.cssText = css;
	} else {
		while(style.firstChild) {
			style.removeChild(style.firstChild);
		}

		style.appendChild(document.createTextNode(css));
	}
}

function updateLink (link, options, obj) {
	var css = obj.css;
	var sourceMap = obj.sourceMap;

	/*
		If convertToAbsoluteUrls isn't defined, but sourcemaps are enabled
		and there is no publicPath defined then lets turn convertToAbsoluteUrls
		on by default.  Otherwise default to the convertToAbsoluteUrls option
		directly
	*/
	var autoFixUrls = options.convertToAbsoluteUrls === undefined && sourceMap;

	if (options.convertToAbsoluteUrls || autoFixUrls) {
		css = fixUrls(css);
	}

	if (sourceMap) {
		// http://stackoverflow.com/a/26603875
		css += "\n/*# sourceMappingURL=data:application/json;base64," + btoa(unescape(encodeURIComponent(JSON.stringify(sourceMap)))) + " */";
	}

	var blob = new Blob([css], { type: "text/css" });

	var oldSrc = link.href;

	link.href = URL.createObjectURL(blob);

	if(oldSrc) URL.revokeObjectURL(oldSrc);
}


/***/ }),
/* 6 */
/***/ (function(module, exports) {


/**
 * When source maps are enabled, `style-loader` uses a link element with a data-uri to
 * embed the css on the page. This breaks all relative urls because now they are relative to a
 * bundle instead of the current page.
 *
 * One solution is to only use full urls, but that may be impossible.
 *
 * Instead, this function "fixes" the relative urls to be absolute according to the current page location.
 *
 * A rudimentary test suite is located at `test/fixUrls.js` and can be run via the `npm test` command.
 *
 */

module.exports = function (css) {
  // get current location
  var location = typeof window !== "undefined" && window.location;

  if (!location) {
    throw new Error("fixUrls requires window.location");
  }

	// blank or null?
	if (!css || typeof css !== "string") {
	  return css;
  }

  var baseUrl = location.protocol + "//" + location.host;
  var currentDir = baseUrl + location.pathname.replace(/\/[^\/]*$/, "/");

	// convert each url(...)
	/*
	This regular expression is just a way to recursively match brackets within
	a string.

	 /url\s*\(  = Match on the word "url" with any whitespace after it and then a parens
	   (  = Start a capturing group
	     (?:  = Start a non-capturing group
	         [^)(]  = Match anything that isn't a parentheses
	         |  = OR
	         \(  = Match a start parentheses
	             (?:  = Start another non-capturing groups
	                 [^)(]+  = Match anything that isn't a parentheses
	                 |  = OR
	                 \(  = Match a start parentheses
	                     [^)(]*  = Match anything that isn't a parentheses
	                 \)  = Match a end parentheses
	             )  = End Group
              *\) = Match anything and then a close parens
          )  = Close non-capturing group
          *  = Match anything
       )  = Close capturing group
	 \)  = Match a close parens

	 /gi  = Get all matches, not the first.  Be case insensitive.
	 */
	var fixedCss = css.replace(/url\s*\(((?:[^)(]|\((?:[^)(]+|\([^)(]*\))*\))*)\)/gi, function(fullMatch, origUrl) {
		// strip quotes (if they exist)
		var unquotedOrigUrl = origUrl
			.trim()
			.replace(/^"(.*)"$/, function(o, $1){ return $1; })
			.replace(/^'(.*)'$/, function(o, $1){ return $1; });

		// already a full url? no change
		if (/^(#|data:|http:\/\/|https:\/\/|file:\/\/\/)/i.test(unquotedOrigUrl)) {
		  return fullMatch;
		}

		// convert the url to a full url
		var newUrl;

		if (unquotedOrigUrl.indexOf("//") === 0) {
		  	//TODO: should we add protocol?
			newUrl = unquotedOrigUrl;
		} else if (unquotedOrigUrl.indexOf("/") === 0) {
			// path should be relative to the base url
			newUrl = baseUrl + unquotedOrigUrl; // already starts with '/'
		} else {
			// path should be relative to current directory
			newUrl = currentDir + unquotedOrigUrl.replace(/^\.\//, ""); // Strip leading './'
		}

		// send back the fixed url(...)
		return "url(" + JSON.stringify(newUrl) + ")";
	});

	// send back the fixed css
	return fixedCss;
};


/***/ }),
/* 7 */
/***/ (function(module, exports) {

throw new Error("Module build failed: Error: \n\nVue packages version mismatch:\n\n- vue@3.5.41 (/home/tuantn1807/project/TaskPilot/node_modules/vue/index.js)\n- vue-template-compiler@2.7.16 (/home/tuantn1807/project/TaskPilot/node_modules/vue-template-compiler/package.json)\n\nThis may cause things to work incorrectly. Make sure to use the same version for both.\nIf you are using vue-loader@>=10.0, simply update vue-template-compiler.\nIf you are using vue-loader@<10.0 or vueify, re-installing vue-loader/vueify should bump vue-template-compiler to the latest.\n\n    at Object.<anonymous> (/home/tuantn1807/project/TaskPilot/node_modules/vue-template-compiler/index.js:10:9)\n    at Module._compile (node:internal/modules/cjs/loader:1521:14)\n    at Module._extensions..js (node:internal/modules/cjs/loader:1623:10)\n    at Module.load (node:internal/modules/cjs/loader:1266:32)\n    at Module._load (node:internal/modules/cjs/loader:1091:12)\n    at Module.require (node:internal/modules/cjs/loader:1289:19)\n    at require (node:internal/modules/helpers:182:18)\n    at Object.<anonymous> (/home/tuantn1807/project/TaskPilot/node_modules/vue-loader/lib/parser.js:1:18)\n    at Module._compile (node:internal/modules/cjs/loader:1521:14)\n    at Module._extensions..js (node:internal/modules/cjs/loader:1623:10)\n    at Module.load (node:internal/modules/cjs/loader:1266:32)\n    at Module._load (node:internal/modules/cjs/loader:1091:12)\n    at Module.require (node:internal/modules/cjs/loader:1289:19)\n    at require (node:internal/modules/helpers:182:18)\n    at Object.<anonymous> (/home/tuantn1807/project/TaskPilot/node_modules/vue-loader/lib/loader.js:3:15)\n    at Module._compile (node:internal/modules/cjs/loader:1521:14)\n    at Module._extensions..js (node:internal/modules/cjs/loader:1623:10)\n    at Module.load (node:internal/modules/cjs/loader:1266:32)\n    at Module._load (node:internal/modules/cjs/loader:1091:12)\n    at Module.require (node:internal/modules/cjs/loader:1289:19)\n    at require (node:internal/modules/helpers:182:18)\n    at Object.<anonymous> (/home/tuantn1807/project/TaskPilot/node_modules/vue-loader/index.js:1:18)\n    at Module._compile (node:internal/modules/cjs/loader:1521:14)\n    at Module._extensions..js (node:internal/modules/cjs/loader:1623:10)\n    at Module.load (node:internal/modules/cjs/loader:1266:32)\n    at Module._load (node:internal/modules/cjs/loader:1091:12)\n    at Module.require (node:internal/modules/cjs/loader:1289:19)\n    at require (node:internal/modules/helpers:182:18)\n    at loadLoader (/home/tuantn1807/project/TaskPilot/node_modules/loader-runner/lib/loadLoader.js:13:17)\n    at iteratePitchingLoaders (/home/tuantn1807/project/TaskPilot/node_modules/loader-runner/lib/LoaderRunner.js:169:2)");

/***/ })
/******/ ]);