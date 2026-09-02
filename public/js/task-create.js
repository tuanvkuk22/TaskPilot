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
/******/ 	return __webpack_require__(__webpack_require__.s = 8);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */
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
/* 1 */
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

var	fixUrls = __webpack_require__(4);

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
var update = __webpack_require__(1)(content, options);
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

exports = module.exports = __webpack_require__(0)(false);
// imports
exports.push([module.i, "@import url(https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap);", ""]);

// module
exports.push([module.i, ".project-edit-form,\n.project-create-form {\n    max-width: 1200px;\n    max-height: 90vh;\n    overflow-y: auto;\n    font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;\n}\n\n.edit-grid {\n    display: grid;\n    grid-template-columns: 1fr 380px;\n    gap: 24px;\n    align-items: stretch;\n}\n\n.edit-col {\n    display: flex;\n    flex-direction: column;\n    gap: 20px;\n    height: 100%;\n}\n\n.edit-card {\n    background: #fff;\n    border: 1px solid #e2e8f0;\n    border-radius: 12px;\n    box-shadow: 0 1px 3px rgba(0,0,0,0.04);\n    overflow: hidden;\n    display: flex;\n    flex-direction: column;\n}\n\n.edit-col:first-child .edit-card {\n    flex: 1;\n}\n\n.edit-card-header {\n    padding: 20px 24px;\n    border-bottom: 1px solid #e2e8f0;\n    background: #f8fafc;\n    flex-shrink: 0;\n}\n\n.edit-card-title {\n    margin: 0;\n    font-size: 14px;\n    font-weight: 500;\n    color: #172033;\n    display: flex;\n    align-items: center;\n    gap: 8px;\n    font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;\n}\n\n.edit-card-title i {\n    color: #2563eb;\n    font-size: 14px;\n}\n\n.edit-card-desc {\n    margin: 4px 0 0;\n    font-size: 12px;\n    color: #64748b;\n    font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;\n}\n\n.edit-card-body {\n    padding: 24px;\n    flex: 1;\n}\n\n.edit-card-actions {\n    background: #fff;\n    flex-shrink: 0;\n}\n\n.edit-field {\n    display: flex;\n    flex-direction: column;\n    gap: 6px;\n    margin-bottom: 18px;\n}\n\n.edit-field:last-child {\n    margin-bottom: 0;\n}\n\n.edit-field label {\n    font-size: 12px;\n    font-weight: 500;\n    color: #334155;\n    letter-spacing: 0.01em;\n    font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;\n}\n\n.required {\n    color: #dc2626;\n    margin-left: 2px;\n}\n\n.edit-input {\n    height: 40px;\n    padding: 0 12px;\n    border: 1px solid #cbd5e1;\n    border-radius: 8px;\n    background: #fff;\n    color: #172033;\n    font-size: 13px;\n    font-weight: 500;\n    font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;\n    outline: none;\n    transition: all 0.15s ease;\n    width: 100%;\n    box-sizing: border-box;\n}\n\n.edit-textarea {\n    min-height: 72px;\n    height: auto !important;\n    padding: 8px 12px !important;\n    border: 1px solid #cbd5e1;\n    border-radius: 8px;\n    background: #fff;\n    color: #172033;\n    font-size: 13px;\n    font-weight: 500;\n    font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;\n    outline: none;\n    width: 100%;\n    box-sizing: border-box;\n    transition: all 0.15s ease;\n    resize: vertical;\n}\n\n.edit-input:focus {\n    border-color: #2563eb;\n    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);\n}\n\n.edit-textarea:focus {\n    border-color: #2563eb;\n    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);\n}\n\n.edit-input.is-invalid {\n    border-color: #dc2626;\n    background: #fef2f2;\n}\n\n.edit-input::placeholder {\n    color: #94a3b8;\n    font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;\n    font-size: 13px;\n}\n\n.edit-select-wrapper {\n    position: relative;\n    display: flex;\n    align-items: center;\n}\n\n.edit-select {\n    width: 100%;\n    height: 40px;\n    padding: 0 36px 0 12px;\n    border: 1px solid #cbd5e1;\n    border-radius: 8px;\n    background: #fff;\n    color: #172033;\n    font-size: 13px;\n    font-weight: 500;\n    font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;\n    outline: none;\n    appearance: none;\n    cursor: pointer;\n    transition: all 0.15s ease;\n}\n\n.edit-select:focus {\n    border-color: #2563eb;\n    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);\n}\n\n.edit-select.is-invalid {\n    border-color: #dc2626;\n    background: #fef2f2;\n}\n\n.edit-select-icon {\n    position: absolute;\n    right: 12px;\n    color: #64748b;\n    font-size: 12px;\n    pointer-events: none;\n}\n\n.edit-quick-add {\n    position: absolute;\n    right: 32px;\n    width: 26px;\n    height: 26px;\n    display: inline-flex;\n    align-items: center;\n    justify-content: center;\n    border: 1px solid #e2e8f0;\n    border-radius: 6px;\n    background: #fff;\n    color: #2563eb;\n    cursor: pointer;\n    font-size: 11px;\n    transition: all 0.15s ease;\n}\n\n.edit-quick-add:hover {\n    background: #2563eb;\n    border-color: #2563eb;\n    color: #fff;\n}\n\n.edit-input-group {\n    display: flex;\n    align-items: stretch;\n}\n\n.edit-input-group .edit-input {\n    border-top-right-radius: 0;\n    border-bottom-right-radius: 0;\n    flex: 1;\n}\n\n.edit-input-addon {\n    display: inline-flex;\n    align-items: center;\n    padding: 0 12px;\n    background: #f1f5f9;\n    border: 1px solid #cbd5e1;\n    border-left: none;\n    border-radius: 0 8px 8px 0;\n    color: #64748b;\n    font-size: 12px;\n    font-weight: 600;\n    font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;\n    white-space: nowrap;\n}\n\n.edit-row {\n    display: grid;\n    grid-template-columns: repeat(2, 1fr);\n    gap: 16px;\n}\n\n.edit-error {\n    font-size: 11px;\n    color: #dc2626;\n    font-weight: 500;\n    margin-top: 2px;\n    font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;\n}\n\n.edit-hint {\n    font-size: 11px;\n    color: #94a3b8;\n    margin-top: 4px;\n    font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;\n}\n\n.edit-btn {\n    display: inline-flex;\n    align-items: center;\n    justify-content: center;\n    gap: 8px;\n    padding: 8px 16px;\n    border: 1px solid transparent;\n    border-radius: 8px;\n    font-size: 12px;\n    font-weight: 500;\n    font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;\n    cursor: pointer;\n    transition: all 0.15s ease;\n    margin-bottom: 10px;\n}\n\n.edit-btn:last-child {\n    margin-bottom: 0;\n}\n\n.edit-btn-primary {\n    background: #2563eb;\n    border-color: #2563eb;\n    color: #fff;\n}\n\n.edit-btn-primary:hover {\n    background: #1d4ed8;\n    border-color: #1d4ed8;\n}\n\n.edit-btn-secondary {\n    background: #fff;\n    border-color: #e2e8f0;\n    color: #475569;\n}\n\n.edit-btn-secondary:hover {\n    background: #f8fafc;\n    border-color: #cbd5e1;\n    color: #172033;\n}\n\n.edit-modal-overlay {\n    position: fixed;\n    inset: 0;\n    background: rgba(15, 23, 42, 0.4);\n    backdrop-filter: blur(4px);\n    display: flex;\n    align-items: center;\n    justify-content: center;\n    z-index: 1050;\n    padding: 20px;\n}\n\n.edit-modal {\n    background: #fff;\n    border-radius: 12px;\n    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);\n    width: 100%;\n    max-width: 520px;\n    max-height: 90vh;\n    overflow-y: auto;\n}\n\n.edit-modal-header {\n    display: flex;\n    align-items: center;\n    justify-content: space-between;\n    padding: 20px 24px;\n    border-bottom: 1px solid #e2e8f0;\n    background: #f8fafc;\n}\n\n.edit-modal-header h3 {\n    margin: 0;\n    font-size: 15px;\n    font-weight: 500;\n    color: #172033;\n    font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;\n}\n\n.edit-modal-close {\n    width: 32px;\n    height: 32px;\n    display: inline-flex;\n    align-items: center;\n    justify-content: center;\n    border: none;\n    border-radius: 6px;\n    background: transparent;\n    color: #64748b;\n    font-size: 20px;\n    cursor: pointer;\n    transition: all 0.15s ease;\n}\n\n.edit-modal-close:hover {\n    background: #e2e8f0;\n    color: #172033;\n}\n\n.edit-modal-body {\n    padding: 24px;\n}\n\n.edit-modal-body label {\n    display: block;\n    font-size: 12px;\n    font-weight: 500;\n    color: #334155;\n    margin-bottom: 6px;\n    margin-top: 14px;\n    font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;\n}\n\n.edit-modal-body label:first-child {\n    margin-top: 0;\n}\n\n.edit-modal-body input::placeholder,\n.edit-modal-body textarea::placeholder {\n    color: #94a3b8;\n    font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;\n}\n\n.edit-modal-footer {\n    display: flex;\n    align-items: center;\n    justify-content: flex-end;\n    gap: 10px;\n    padding: 16px 24px;\n    border-top: 1px solid #e2e8f0;\n    background: #f8fafc;\n}\n\n.edit-modal-footer .edit-btn {\n    width: auto;\n    margin-bottom: 0;\n    min-width: 100px;\n}\n\n.edit-image-upload {\n    display: flex;\n    align-items: stretch;\n    gap: 12px;\n    width: fit-content;\n}\n\n.edit-image-placeholder,\n.edit-image-preview {\n    display: flex;\n    align-items: center;\n    justify-content: center;\n    width: 160px;\n    height: 80px;\n    border: 2px dashed #cbd5e1;\n    border-radius: 8px;\n    background: #f8fafc;\n    color: #94a3b8;\n    font-size: 11px;\n    font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;\n    overflow: hidden;\n    flex-shrink: 0;\n    position: relative;\n}\n\n.edit-image-placeholder i {\n    font-size: 20px;\n    display: block;\n}\n\n.edit-image-thumb {\n    width: 100%;\n    height: 100%;\n    object-fit: cover;\n}\n\n.edit-image-clear {\n    position: absolute;\n    top: 4px;\n    right: 4px;\n    width: 16px;\n    height: 16px;\n    border-radius: 4px;\n    background: rgba(0,0,0,0.5);\n    color: #fff;\n    display: flex;\n    align-items: center;\n    justify-content: center;\n    border: none;\n    cursor: pointer;\n    font-size: 10px;\n}\n\n.edit-image-btn {\n    display: inline-flex;\n    align-items: center;\n    gap: 6px;\n    padding: 0 14px;\n    background: #f1f5f9;\n    border: 1px solid #cbd5e1;\n    border-radius: 8px;\n    color: #475569;\n    font-size: 12px;\n    font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;\n    cursor: pointer;\n    transition: all 0.15s ease;\n    height: 40px;\n}\n\n.edit-image-btn:hover {\n    background: #e2e8f0;\n}\n\n.edit-image-input {\n    display: none;\n}\n\n.quill-editor {\n    min-height: 100px;\n    border: 1px solid #cbd5e1;\n    border-radius: 8px;\n    background: #fff;\n    padding: 8px 12px;\n    font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;\n}\n\n.quill-editor .ql-toolbar {\n    border: none;\n    border-bottom: 1px solid #e2e8f0;\n    background: #f8fafc;\n    padding: 4px 10px;\n}\n\n.quill-editor .ql-container {\n    border: none;\n    font-size: 13px;\n    font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;\n}\n\n.edit-member-input {\n    display: flex;\n    flex-wrap: wrap;\n    align-items: center;\n    gap: 6px;\n    min-height: 42px;\n    padding: 6px 12px;\n    border: 1px solid #cbd5e1;\n    border-radius: 8px;\n    background: #fff;\n    cursor: text;\n    transition: all 0.15s ease;\n}\n\n.edit-member-input:focus-within {\n    border-color: #2563eb;\n    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);\n}\n\n.edit-member-tag {\n    display: inline-flex;\n    align-items: center;\n    gap: 6px;\n    padding: 4px 10px;\n    background: #eff6ff;\n    border: 1px solid #bfdbfe;\n    border-radius: 6px;\n    color: #1d4ed8;\n    font-size: 12px;\n    font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;\n}\n\n.edit-member-tag i {\n    cursor: pointer;\n    color: #2563eb;\n}\n\n.edit-member-text {\n    border: none;\n    outline: none;\n    background: transparent;\n    flex: 1;\n    min-width: 120px;\n    padding: 4px 0;\n    font-size: 13px;\n    font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;\n    color: #172033;\n}\n\n.edit-member-text::placeholder {\n    color: #94a3b8;\n    font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;\n}\n\n@media (max-width: 1024px) {\n    .edit-grid {\n        grid-template-columns: 1fr;\n    }\n}\n\n@media (max-width: 768px) {\n    .edit-card-body {\n        padding: 16px;\n    }\n\n    .edit-row {\n        grid-template-columns: 1fr;\n    }\n}\n", ""]);

// exports


/***/ }),
/* 4 */
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
/* 5 */,
/* 6 */,
/* 7 */,
/* 8 */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(9);


/***/ }),
/* 9 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
throw new Error("Cannot find module \"vue\"");
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__css_edit_shared_css__ = __webpack_require__(2);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__css_edit_shared_css___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_1__css_edit_shared_css__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__css_task_shared_css__ = __webpack_require__(10);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__css_task_shared_css___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_2__css_task_shared_css__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3__components_tasks_TaskCreate_vue__ = __webpack_require__(12);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3__components_tasks_TaskCreate_vue___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_3__components_tasks_TaskCreate_vue__);





var d = window.taskCreateData || {};

var app = Object(__WEBPACK_IMPORTED_MODULE_0_vue__["createApp"])(__WEBPACK_IMPORTED_MODULE_3__components_tasks_TaskCreate_vue__["default"], {
    projects: d.projects || [],
    users: d.users || [],
    reporters: d.reporters || [],
    epics: d.epics || [],
    sprints: d.sprints || [],
    availableLabels: d.available_labels || [],
    csrfToken: d.csrf_token || '',
    storeUrl: d.store_url || '/tasks',
    tasksIndexUrl: d.tasks_index_url || '/tasks',
    oldLabels: d.old_labels || '',
    old: d.old || {},
    errors: d.errors || {},
    projectId: d.project_id || null
});

app.mount('#task-create-app');

/***/ }),
/* 10 */
/***/ (function(module, exports, __webpack_require__) {

// style-loader: Adds some css to the DOM by adding a <style> tag

// load the styles
var content = __webpack_require__(11);
if(typeof content === 'string') content = [[module.i, content, '']];
// Prepare cssTransformation
var transform;

var options = {}
options.transform = transform
// add the styles to the DOM
var update = __webpack_require__(1)(content, options);
if(content.locals) module.exports = content.locals;
// Hot Module Replacement
if(false) {
	// When the styles change, update the <style> tags
	if(!content.locals) {
		module.hot.accept("!!../../node_modules/css-loader/index.js!./task-shared.css", function() {
			var newContent = require("!!../../node_modules/css-loader/index.js!./task-shared.css");
			if(typeof newContent === 'string') newContent = [[module.id, newContent, '']];
			update(newContent);
		});
	}
	// When the module is disposed, remove the <style> tags
	module.hot.dispose(function() { update(); });
}

/***/ }),
/* 11 */
/***/ (function(module, exports, __webpack_require__) {

exports = module.exports = __webpack_require__(0)(false);
// imports
exports.push([module.i, "@import url(https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap);", ""]);

// module
exports.push([module.i, ":root {\n    --tp-font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;\n    --tp-primary: #2563eb;\n    --tp-primary-hover: #1d4ed8;\n    --tp-primary-light: #eff6ff;\n    --tp-success: #10b981;\n    --tp-warning: #f59e0b;\n    --tp-danger: #ef4444;\n    --tp-purple: #8b5cf6;\n    --tp-bg-subtle: #f8fafc;\n    --tp-border: #e2e8f0;\n    --tp-text-dark: #0f172a;\n    --tp-text-muted: #64748b;\n    --tp-shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.04);\n    --tp-shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.06), 0 2px 4px -1px rgba(0, 0, 0, 0.03);\n    --tp-shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.07), 0 4px 6px -2px rgba(0, 0, 0, 0.02);\n}\n\n/* Apply Inter font family */\n.task-create-vue,\n.task-index-vue,\n.task-create-form {\n    font-family: var(--tp-font-family) !important;\n    color: #1e293b;\n    font-size: 13.5px;\n    line-height: 1.5;\n    padding-bottom: 60px;\n}\n\n.task-create-vue p, .task-create-vue span, .task-create-vue label, .task-create-vue h1, .task-create-vue h2, .task-create-vue h3, .task-create-vue input, .task-create-vue select, .task-create-vue textarea, .task-create-vue button, .task-create-vue a,\n.task-index-vue p, .task-index-vue span, .task-index-vue label, .task-index-vue h1, .task-index-vue h2, .task-index-vue h3, .task-index-vue input, .task-index-vue select, .task-index-vue textarea, .task-index-vue button, .task-index-vue a {\n    font-family: var(--tp-font-family) !important;\n}\n\n/* Explicitly preserve FontAwesome icon font-family */\ni.fa, i.fas, i.far, i.fab, .fa, .fas, .far, .fab, [class^=\"fa-\"], [class*=\" fa-\"] {\n    font-family: FontAwesome, 'Font Awesome 5 Free', 'Font Awesome 5 Brands' !important;\n    font-style: normal;\n    font-weight: normal;\n    font-variant: normal;\n    text-transform: none;\n    line-height: 1;\n}\n\n/* Page Header Banner */\n.tp-header-banner {\n    display: flex;\n    justify-content: space-between;\n    align-items: center;\n    margin-bottom: 20px;\n    padding: 18px 24px;\n    background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);\n    border-radius: 14px;\n    color: #ffffff;\n    box-shadow: var(--tp-shadow-md);\n    position: relative;\n    overflow: hidden;\n}\n\n.tp-header-banner::before {\n    content: '';\n    position: absolute;\n    top: -50%;\n    right: -20%;\n    width: 320px;\n    height: 320px;\n    background: radial-gradient(circle, rgba(37,99,235,0.22) 0%, rgba(37,99,235,0) 70%);\n    border-radius: 50%;\n    pointer-events: none;\n}\n\n.tp-header-content .tp-eyebrow {\n    display: inline-flex;\n    align-items: center;\n    gap: 5px;\n    font-size: 11px;\n    font-weight: 700;\n    letter-spacing: 0.06em;\n    text-transform: uppercase;\n    color: #60a5fa;\n    background: rgba(96, 165, 250, 0.12);\n    padding: 3px 9px;\n    border-radius: 16px;\n    margin-bottom: 6px;\n}\n\n.tp-header-content h1 {\n    font-size: 22px;\n    font-weight: 700;\n    margin: 0 0 4px 0;\n    color: #ffffff;\n    letter-spacing: -0.02em;\n    line-height: 1.25;\n}\n\n.tp-header-content p {\n    font-size: 13px;\n    color: #94a3b8;\n    margin: 0;\n    line-height: 1.4;\n}\n\n.tp-header-actions {\n    display: flex;\n    gap: 10px;\n    align-items: center;\n    z-index: 2;\n}\n\n/* Responsive 2-Column Layout (70 / 30) */\n.edit-grid {\n    display: grid;\n    grid-template-columns: 1fr 340px;\n    gap: 18px;\n    align-items: start;\n}\n\n.task-create-vue .edit-col,\n.task-create-form .edit-col {\n    display: flex !important;\n    flex-direction: column !important;\n    gap: 16px !important;\n    height: auto !important;\n}\n\n/* Card Styling - Override flex: 1 stretching bug from edit-shared.css */\n.task-create-vue .edit-card,\n.task-create-form .edit-card,\n.edit-col:first-child .edit-card {\n    flex: none !important;\n    height: auto !important;\n    display: block !important;\n    background: #ffffff;\n    border: 1px solid #e2e8f0;\n    border-radius: 12px;\n    box-shadow: var(--tp-shadow-sm);\n    overflow: hidden;\n    transition: box-shadow 0.2s ease, border-color 0.2s ease;\n}\n\n.task-create-vue .edit-card:hover,\n.task-create-form .edit-card:hover {\n    box-shadow: var(--tp-shadow-md);\n    border-color: #cbd5e1;\n}\n\n.task-create-vue .edit-card-header,\n.task-create-form .edit-card-header {\n    padding: 12px 18px !important;\n    background: #f8fafc;\n    border-bottom: 1px solid #e2e8f0;\n    display: flex;\n    align-items: center;\n    justify-content: space-between;\n}\n\n.edit-card-title {\n    font-size: 14px !important;\n    font-weight: 600 !important;\n    color: #0f172a !important;\n    display: flex;\n    align-items: center;\n    gap: 8px;\n    margin: 0;\n}\n\n.edit-card-title i {\n    color: var(--tp-primary) !important;\n    font-size: 14px !important;\n}\n\n.edit-card-desc {\n    font-size: 12px !important;\n    color: #64748b !important;\n    margin: 2px 0 0 0;\n}\n\n.task-create-vue .edit-card-body,\n.task-create-form .edit-card-body {\n    padding: 16px 18px !important;\n    flex: none !important;\n    height: auto !important;\n}\n\n/* Compact Card Modifier (for Issue Type & Epic) */\n.task-create-vue .edit-card-compact .edit-card-header,\n.task-create-form .edit-card-compact .edit-card-header {\n    padding: 10px 16px !important;\n}\n\n.task-create-vue .edit-card-compact .edit-card-body,\n.task-create-form .edit-card-compact .edit-card-body {\n    padding: 12px 16px !important;\n}\n\n/* Compact Form Controls */\n.edit-field {\n    margin-bottom: 14px;\n    display: flex;\n    flex-direction: column;\n    gap: 5px;\n}\n\n.edit-field:last-child {\n    margin-bottom: 0;\n}\n\n.edit-field label {\n    font-size: 13px !important;\n    font-weight: 600 !important;\n    color: #1e293b !important;\n    display: flex;\n    align-items: center;\n    justify-content: space-between;\n}\n\n.required {\n    color: #ef4444;\n    font-weight: 700;\n    margin-left: 3px;\n}\n\n.edit-input, .edit-textarea, .edit-select {\n    width: 100%;\n    padding: 8.5px 12px !important;\n    font-size: 13.5px !important;\n    line-height: 1.45 !important;\n    color: #0f172a !important;\n    background-color: #ffffff !important;\n    border: 1px solid #cbd5e1 !important;\n    border-radius: 8px !important;\n    transition: all 0.15s ease;\n    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02);\n    box-sizing: border-box;\n}\n\n.edit-textarea {\n    min-height: 120px !important;\n    height: auto !important;\n    resize: vertical !important;\n}\n\n.edit-input:focus, .edit-textarea:focus, .edit-select:focus {\n    outline: none !important;\n    border-color: var(--tp-primary) !important;\n    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12) !important;\n}\n\n.edit-input.is-invalid, .edit-textarea.is-invalid, .edit-select.is-invalid {\n    border-color: #ef4444 !important;\n    background-color: #fef2f2 !important;\n}\n\n.edit-hint {\n    font-size: 11.5px !important;\n    color: #64748b !important;\n    margin-top: 2px;\n}\n\n.edit-error {\n    font-size: 11.5px !important;\n    color: #ef4444 !important;\n    font-weight: 500 !important;\n    margin-top: 2px;\n}\n\n/* Custom Select */\n.edit-select-wrapper {\n    position: relative;\n    display: flex;\n    align-items: center;\n}\n\n.edit-select {\n    appearance: none;\n    -webkit-appearance: none;\n    padding-right: 34px !important;\n    cursor: pointer;\n    height: 38px !important;\n}\n\n.edit-select-icon {\n    position: absolute;\n    right: 12px;\n    pointer-events: none;\n    color: #94a3b8;\n    font-size: 11px;\n}\n\n/* Compact & Modern Selectable Cards */\n.issue-type-selector, .status-selector, .priority-selector {\n    display: grid;\n    grid-template-columns: repeat(auto-fit, minmax(90px, 1fr));\n    gap: 8px;\n}\n\n.issue-type-option, .status-option, .priority-option {\n    cursor: pointer;\n    margin: 0;\n}\n\n.issue-type-option input, .status-option input, .priority-option input {\n    display: none;\n}\n\n.issue-type-card, .status-card, .priority-card {\n    display: flex;\n    align-items: center;\n    justify-content: center;\n    gap: 5px;\n    padding: 7px 10px;\n    border: 1px solid #e2e8f0;\n    border-radius: 8px;\n    background: #f8fafc;\n    color: #64748b;\n    font-size: 11.5px;\n    font-weight: 600;\n    text-align: center;\n    transition: all 0.15s ease;\n    user-select: none;\n}\n\n.issue-type-card i, .status-card i, .priority-card i {\n    font-size: 13.5px;\n}\n\n.issue-type-option:hover .issue-type-card,\n.status-option:hover .status-card,\n.priority-option:hover .priority-card {\n    border-color: #cbd5e1;\n    background: #ffffff;\n    color: #334155;\n}\n\n.issue-type-option input:checked + .issue-type-card,\n.status-option input:checked + .status-card,\n.priority-option input:checked + .priority-card {\n    background: #ffffff;\n    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.04);\n}\n\n/* Specific issue colors when checked */\n.issue-type-option input:checked + .issue-type-task { border-color: #2563eb; color: #2563eb; background: #eff6ff; }\n.issue-type-option input:checked + .issue-type-bug { border-color: #ef4444; color: #ef4444; background: #fef2f2; }\n.issue-type-option input:checked + .issue-type-story { border-color: #8b5cf6; color: #8b5cf6; background: #f5f3ff; }\n.issue-type-option input:checked + .issue-type-epic { border-color: #f59e0b; color: #d97706; background: #fffbeb; }\n\n/* Status card checked colors */\n.status-option input:checked + .status-todo { border-color: #64748b; color: #475569; background: #f1f5f9; }\n.status-option input:checked + .status-in_progress { border-color: #2563eb; color: #2563eb; background: #eff6ff; }\n.status-option input:checked + .status-done { border-color: #10b981; color: #10b981; background: #ecfdf5; }\n\n/* Priority card checked colors */\n.priority-option input:checked + .priority-low { border-color: #10b981; color: #059669; background: #ecfdf5; }\n.priority-option input:checked + .priority-medium { border-color: #f59e0b; color: #d97706; background: #fffbeb; }\n.priority-option input:checked + .priority-high { border-color: #ef4444; color: #dc2626; background: #fef2f2; }\n\n/* Buttons */\n.tp-btn {\n    display: inline-flex;\n    align-items: center;\n    justify-content: center;\n    gap: 7px;\n    padding: 8.5px 16px;\n    font-size: 13px;\n    font-weight: 600;\n    border-radius: 8px;\n    transition: all 0.15s ease;\n    cursor: pointer;\n    border: 1px solid transparent;\n    text-decoration: none !important;\n}\n\n.tp-btn-primary {\n    background: var(--tp-primary);\n    color: #ffffff !important;\n    box-shadow: 0 2px 8px rgba(37, 99, 235, 0.3);\n}\n\n.tp-btn-primary i,\n.tp-btn-primary .fa,\n.tp-btn-primary span {\n    color: #ffffff !important;\n}\n\n.tp-btn-primary:hover {\n    background: var(--tp-primary-hover);\n    transform: translateY(-1px);\n    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4);\n}\n\n.tp-btn-primary:hover i,\n.tp-btn-primary:hover .fa,\n.tp-btn-primary:hover span {\n    color: #ffffff !important;\n}\n\n.tp-btn-secondary {\n    background: rgba(255, 255, 255, 0.1);\n    color: #ffffff !important;\n    border: 1px solid rgba(255, 255, 255, 0.15);\n    backdrop-filter: blur(8px);\n}\n\n.tp-btn-secondary i,\n.tp-btn-secondary .fa,\n.tp-btn-secondary span {\n    color: #ffffff !important;\n}\n\n.tp-btn-secondary:hover {\n    background: rgba(255, 255, 255, 0.2);\n    border-color: rgba(255, 255, 255, 0.3);\n}\n\n.tp-btn-light {\n    background: #ffffff;\n    color: #334155 !important;\n    border: 1px solid #cbd5e1;\n    box-shadow: var(--tp-shadow-sm);\n}\n\n.tp-btn-light:hover {\n    background: #f8fafc;\n    border-color: #94a3b8;\n    color: #0f172a !important;\n}\n\n.btn-quick-add-icon {\n    width: 26px;\n    height: 26px;\n    border-radius: 50%;\n    border: 1px solid #cbd5e1;\n    background: #ffffff;\n    color: #2563eb;\n    display: inline-flex;\n    align-items: center;\n    justify-content: center;\n    cursor: pointer;\n    font-size: 11px;\n    transition: all 0.15s ease;\n}\n\n.btn-quick-add-icon:hover {\n    background: #2563eb;\n    color: #ffffff !important;\n    border-color: #2563eb;\n    transform: scale(1.08);\n}\n\n/* Tag / Labels component */\n.tag-input-container {\n    display: flex;\n    flex-wrap: wrap;\n    align-items: center;\n    gap: 6px;\n    padding: 6px 10px;\n    background: #ffffff;\n    border: 1px solid #cbd5e1;\n    border-radius: 8px;\n    min-height: 38px;\n    cursor: text;\n    transition: all 0.15s ease;\n}\n\n.tag-input-container:focus-within {\n    border-color: var(--tp-primary);\n    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);\n}\n\n.tag-item {\n    display: inline-flex;\n    align-items: center;\n    gap: 5px;\n    padding: 2px 8px;\n    background: #eff6ff;\n    border: 1px solid #bfdbfe;\n    border-radius: 16px;\n    color: #1d4ed8;\n    font-size: 11.5px;\n    font-weight: 600;\n}\n\n.tag-remove {\n    background: none;\n    border: none;\n    color: #3b82f6;\n    cursor: pointer;\n    font-size: 11px;\n    padding: 0;\n    display: flex;\n    align-items: center;\n}\n\n.tag-remove:hover {\n    color: #1d4ed8;\n}\n\n.tag-input {\n    flex: 1;\n    min-width: 100px;\n    border: none;\n    outline: none;\n    background: transparent;\n    font-size: 13px;\n    padding: 2px 0;\n}\n\n/* User Pill Badges */\n.user-pill {\n    display: inline-flex;\n    align-items: center;\n    gap: 6px;\n    padding: 3px 8px;\n    background: #f1f5f9;\n    border-radius: 16px;\n    font-size: 11.5px;\n    font-weight: 600;\n    color: #334155;\n}\n\n.user-avatar-sm {\n    width: 20px;\n    height: 20px;\n    border-radius: 50%;\n    background: var(--tp-primary);\n    color: #ffffff !important;\n    display: flex;\n    align-items: center;\n    justify-content: center;\n    font-size: 9.5px;\n    font-weight: 700;\n}\n\n/* Sticky Action Bar */\n.tp-sticky-actions {\n    position: sticky;\n    bottom: 16px;\n    margin-top: 20px;\n    padding: 14px 20px;\n    background: rgba(255, 255, 255, 0.95);\n    backdrop-filter: blur(12px);\n    border: 1px solid rgba(226, 232, 240, 0.9);\n    border-radius: 12px;\n    box-shadow: var(--tp-shadow-lg);\n    display: flex;\n    align-items: center;\n    justify-content: space-between;\n    z-index: 100;\n}\n\n/* Dashboard / Index Metrics */\n.tp-stats-grid {\n    display: grid;\n    grid-template-columns: repeat(4, 1fr);\n    gap: 14px;\n    margin-bottom: 20px;\n}\n\n.tp-stat-card {\n    background: #ffffff;\n    border: 1px solid #e2e8f0;\n    border-radius: 12px;\n    padding: 14px 16px;\n    display: flex;\n    align-items: center;\n    justify-content: space-between;\n    box-shadow: var(--tp-shadow-sm);\n    transition: all 0.15s ease;\n}\n\n.tp-stat-card:hover {\n    transform: translateY(-1px);\n    box-shadow: var(--tp-shadow-md);\n    border-color: #cbd5e1;\n}\n\n.tp-stat-label {\n    font-size: 11.5px;\n    font-weight: 600;\n    color: #64748b;\n    text-transform: uppercase;\n    letter-spacing: 0.04em;\n    margin-bottom: 2px;\n}\n\n.tp-stat-value {\n    font-size: 22px;\n    font-weight: 700;\n    color: #0f172a;\n    line-height: 1.2;\n}\n\n.tp-stat-icon {\n    width: 42px;\n    height: 42px;\n    border-radius: 10px;\n    display: flex;\n    align-items: center;\n    justify-content: center;\n    font-size: 18px;\n}\n\n.tp-stat-icon.total { background: #eff6ff; color: #2563eb; }\n.tp-stat-icon.pending { background: #fef3c7; color: #d97706; }\n.tp-stat-icon.in_progress { background: #f3e8ff; color: #7c3aed; }\n.tp-stat-icon.done { background: #d1fae5; color: #059669; }\n.tp-stat-icon.urgent { background: #fee2e2; color: #dc2626; }\n\n/* Filter Bar */\n.tp-filter-bar {\n    background: #ffffff;\n    border: 1px solid #e2e8f0;\n    border-radius: 12px;\n    padding: 12px 16px;\n    margin-bottom: 18px;\n    display: flex;\n    flex-wrap: wrap;\n    align-items: center;\n    justify-content: space-between;\n    gap: 10px;\n    box-shadow: var(--tp-shadow-sm);\n}\n\n.tp-filter-inputs {\n    display: flex;\n    flex-wrap: wrap;\n    align-items: center;\n    gap: 10px;\n    flex: 1;\n}\n\n.tp-search-box {\n    position: relative;\n    min-width: 220px;\n    flex: 1;\n}\n\n.tp-search-box i {\n    position: absolute;\n    left: 10px;\n    top: 50%;\n    transform: translateY(-50%);\n    color: #94a3b8;\n    font-size: 13px;\n}\n\n.tp-search-box input {\n    padding-left: 32px !important;\n}\n\n.tp-view-toggle {\n    display: inline-flex;\n    background: #f1f5f9;\n    padding: 2px;\n    border-radius: 8px;\n    gap: 2px;\n}\n\n.tp-view-btn {\n    border: none;\n    background: transparent;\n    padding: 5.5px 12px;\n    border-radius: 6px;\n    font-size: 12px;\n    font-weight: 600;\n    color: #64748b;\n    cursor: pointer;\n    display: inline-flex;\n    align-items: center;\n    gap: 5px;\n    transition: all 0.15s ease;\n}\n\n.tp-view-btn.active {\n    background: #ffffff;\n    color: #0f172a;\n    box-shadow: var(--tp-shadow-sm);\n}\n\n/* Data Table */\n.tp-table-card {\n    background: #ffffff;\n    border: 1px solid #e2e8f0;\n    border-radius: 12px;\n    box-shadow: var(--tp-shadow-sm);\n    overflow: hidden;\n}\n\n.tp-table {\n    width: 100%;\n    border-collapse: collapse;\n    font-size: 13px;\n}\n\n.tp-table th {\n    background: #f8fafc;\n    padding: 10px 14px;\n    font-size: 11.5px;\n    font-weight: 600;\n    color: #475569;\n    text-transform: uppercase;\n    letter-spacing: 0.04em;\n    border-bottom: 1px solid #e2e8f0;\n    text-align: left;\n}\n\n.tp-table td {\n    padding: 12px 14px;\n    border-bottom: 1px solid #f1f5f9;\n    color: #334155;\n    vertical-align: middle;\n}\n\n.tp-table tr:last-child td {\n    border-bottom: none;\n}\n\n.tp-table tr:hover td {\n    background-color: #f8fafc;\n}\n\n/* Badges */\n.tp-badge {\n    display: inline-flex;\n    align-items: center;\n    gap: 4px;\n    padding: 3px 8px;\n    border-radius: 16px;\n    font-size: 11px;\n    font-weight: 600;\n    line-height: 1.2;\n}\n\n.tp-badge-type-task { background: #eff6ff; color: #2563eb; }\n.tp-badge-type-bug { background: #fef2f2; color: #dc2626; }\n.tp-badge-type-story { background: #f5f3ff; color: #7c3aed; }\n.tp-badge-type-epic { background: #fffbeb; color: #d97706; }\n\n.tp-badge-status-todo { background: #f1f5f9; color: #475569; }\n.tp-badge-status-in_progress { background: #eff6ff; color: #2563eb; }\n.tp-badge-status-done { background: #ecfdf5; color: #059669; }\n\n.tp-badge-priority-low { background: #ecfdf5; color: #059669; }\n.tp-badge-priority-medium { background: #fffbeb; color: #d97706; }\n.tp-badge-priority-high { background: #fef2f2; color: #dc2626; }\n\n/* Kanban Board View */\n.tp-kanban-board {\n    display: grid;\n    grid-template-columns: repeat(3, 1fr);\n    gap: 16px;\n}\n\n.tp-kanban-column {\n    background: #f8fafc;\n    border: 1px solid #e2e8f0;\n    border-radius: 12px;\n    padding: 14px;\n    display: flex;\n    flex-direction: column;\n    gap: 10px;\n    min-height: 380px;\n}\n\n.tp-kanban-header {\n    display: flex;\n    align-items: center;\n    justify-content: space-between;\n    padding-bottom: 8px;\n    border-bottom: 2px solid #e2e8f0;\n}\n\n.tp-kanban-title {\n    font-size: 13.5px;\n    font-weight: 700;\n    color: #1e293b;\n    display: flex;\n    align-items: center;\n    gap: 6px;\n    margin: 0;\n}\n\n.tp-kanban-count {\n    background: #e2e8f0;\n    color: #475569;\n    font-size: 11px;\n    font-weight: 700;\n    padding: 1px 7px;\n    border-radius: 10px;\n}\n\n.tp-kanban-card {\n    background: #ffffff;\n    border: 1px solid #e2e8f0;\n    border-radius: 10px;\n    padding: 12px;\n    box-shadow: var(--tp-shadow-sm);\n    transition: all 0.15s ease;\n    cursor: pointer;\n}\n\n.tp-kanban-card:hover {\n    transform: translateY(-1px);\n    box-shadow: var(--tp-shadow-md);\n    border-color: #cbd5e1;\n}\n\n.tp-kanban-card-meta {\n    display: flex;\n    align-items: center;\n    justify-content: space-between;\n    margin-bottom: 6px;\n}\n\n.tp-kanban-card-title {\n    font-size: 13.5px;\n    font-weight: 600;\n    color: #0f172a;\n    margin: 0 0 8px 0;\n    line-height: 1.35;\n}\n\n.tp-kanban-card-footer {\n    display: flex;\n    align-items: center;\n    justify-content: space-between;\n    padding-top: 8px;\n    border-top: 1px solid #f1f5f9;\n}\n\n/* Empty State */\n.tp-empty-state {\n    text-align: center;\n    padding: 40px 20px;\n    background: #ffffff;\n    border: 2px dashed #cbd5e1;\n    border-radius: 14px;\n    margin: 16px 0;\n}\n\n.tp-empty-icon {\n    width: 56px;\n    height: 56px;\n    background: #eff6ff;\n    color: var(--tp-primary);\n    border-radius: 50%;\n    display: inline-flex;\n    align-items: center;\n    justify-content: center;\n    font-size: 24px;\n    margin-bottom: 12px;\n}\n\n.tp-empty-state h3 {\n    font-size: 16px;\n    font-weight: 700;\n    color: #0f172a;\n    margin: 0 0 4px 0;\n}\n\n.tp-empty-state p {\n    font-size: 13px;\n    color: #64748b;\n    margin: 0 0 16px 0;\n}\n\n@media (max-width: 1024px) {\n    .edit-grid {\n        grid-template-columns: 1fr;\n    }\n}\n\n@media (max-width: 992px) {\n    .tp-stats-grid {\n        grid-template-columns: repeat(2, 1fr);\n    }\n    .tp-kanban-board {\n        grid-template-columns: 1fr;\n    }\n}\n\n@media (max-width: 576px) {\n    .tp-stats-grid {\n        grid-template-columns: 1fr;\n    }\n    .tp-header-banner {\n        flex-direction: column;\n        align-items: flex-start;\n        gap: 14px;\n    }\n}\n\n/* Isolated Label Modals (Prevents Bootstrap 3 layout conflicts) */\n.tp-lbl-modal-overlay {\n    position: fixed !important;\n    top: 0 !important;\n    left: 0 !important;\n    right: 0 !important;\n    bottom: 0 !important;\n    width: 100vw !important;\n    height: 100vh !important;\n    background: rgba(15, 23, 42, 0.65) !important;\n    backdrop-filter: blur(6px) !important;\n    -webkit-backdrop-filter: blur(6px) !important;\n    z-index: 999999 !important;\n    display: flex !important;\n    align-items: center !important;\n    justify-content: center !important;\n    padding: 20px !important;\n    box-sizing: border-box !important;\n}\n\n.tp-lbl-modal-card {\n    background: #ffffff !important;\n    border-radius: 16px !important;\n    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4) !important;\n    width: 100% !important;\n    max-width: 500px !important;\n    overflow: hidden !important;\n    border: 1px solid #cbd5e1 !important;\n    display: block !important;\n    position: relative !important;\n    margin: auto !important;\n}\n\n.tp-lbl-select-modal { max-width: 500px !important; }\n.tp-lbl-create-modal { max-width: 440px !important; }\n\n.tp-lbl-modal-header {\n    padding: 16px 20px !important;\n    background: #f8fafc !important;\n    border-bottom: 1px solid #e2e8f0 !important;\n    display: flex !important;\n    align-items: center !important;\n    justify-content: space-between !important;\n}\n\n.tp-lbl-modal-title-group {\n    display: flex !important;\n    align-items: center !important;\n    gap: 10px !important;\n}\n\n.tp-lbl-modal-title {\n    margin: 0 !important;\n    font-size: 16px !important;\n    font-weight: 700 !important;\n    color: #0f172a !important;\n}\n\n.tp-lbl-badge-count {\n    font-size: 11px !important;\n    font-weight: 700 !important;\n    background: #eff6ff !important;\n    color: #2563eb !important;\n    padding: 2px 8px !important;\n    border-radius: 12px !important;\n    border: 1px solid #bfdbfe !important;\n}\n\n.tp-lbl-modal-close {\n    background: none !important;\n    border: none !important;\n    color: #94a3b8 !important;\n    font-size: 16px !important;\n    cursor: pointer !important;\n    padding: 4px 8px !important;\n    border-radius: 6px !important;\n}\n\n.tp-lbl-modal-close:hover {\n    color: #ef4444 !important;\n    background: #fef2f2 !important;\n}\n\n.tp-lbl-modal-body {\n    padding: 20px !important;\n    max-height: 380px !important;\n    overflow-y: auto !important;\n}\n\n.tp-lbl-modal-footer {\n    padding: 14px 20px !important;\n    background: #f8fafc !important;\n    border-top: 1px solid #e2e8f0 !important;\n    display: flex !important;\n    align-items: center !important;\n    justify-content: flex-end !important;\n    gap: 10px !important;\n}\n\n/* Bootstrap 5 Utility Compatibility for task detail & edit views */\n.d-flex { display: flex !important; }\n.d-block { display: block !important; }\n.d-inline { display: inline !important; }\n.d-inline-block { display: inline-block !important; }\n.justify-content-between { justify-content: space-between !important; }\n.justify-content-center { justify-content: center !important; }\n.justify-content-end { justify-content: flex-end !important; }\n.align-items-center { align-items: center !important; }\n.align-items-start { align-items: flex-start !important; }\n.flex-wrap { flex-wrap: wrap !important; }\n.flex-column { flex-direction: column !important; }\n\n.gap-1 { gap: 4px !important; }\n.gap-2 { gap: 8px !important; }\n.gap-3 { gap: 12px !important; }\n.gap-4 { gap: 16px !important; }\n\n.me-1 { margin-right: 4px !important; }\n.me-2 { margin-right: 8px !important; }\n.me-3 { margin-right: 12px !important; }\n.ms-1 { margin-left: 4px !important; }\n.ms-2 { margin-left: 8px !important; }\n.ms-auto { margin-left: auto !important; }\n.me-auto { margin-right: auto !important; }\n\n.mb-0 { margin-bottom: 0 !important; }\n.mb-1 { margin-bottom: 4px !important; }\n.mb-2 { margin-bottom: 8px !important; }\n.mb-3 { margin-bottom: 12px !important; }\n.mb-4 { margin-bottom: 16px !important; }\n.mt-1 { margin-top: 4px !important; }\n.mt-2 { margin-top: 8px !important; }\n.mt-3 { margin-top: 12px !important; }\n.mt-4 { margin-top: 16px !important; }\n\n.py-1 { padding-top: 4px !important; padding-bottom: 4px !important; }\n.py-2 { padding-top: 8px !important; padding-bottom: 8px !important; }\n.py-3 { padding-top: 12px !important; padding-bottom: 12px !important; }\n.py-4 { padding-top: 16px !important; padding-bottom: 16px !important; }\n.px-2 { padding-left: 8px !important; padding-right: 8px !important; }\n.px-3 { padding-left: 12px !important; padding-right: 12px !important; }\n.px-4 { padding-left: 16px !important; padding-right: 16px !important; }\n.p-4 { padding: 20px !important; }\n\n.fw-bold { font-weight: 700 !important; }\n.fw-semibold { font-weight: 600 !important; }\n.fw-medium { font-weight: 500 !important; }\n\n.bg-white { background-color: #ffffff !important; }\n.bg-light { background-color: #f8fafc !important; }\n.border-0 { border: 0 !important; }\n.shadow-sm { box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.08), 0 1px 2px 0 rgba(0, 0, 0, 0.04) !important; }\n.rounded-circle { border-radius: 50% !important; }\n\n/* Grid row gap support */\n.row.g-2 { display: flex; flex-wrap: wrap; margin-left: -4px; margin-right: -4px; }\n.row.g-2 > [class*=\"col-\"] { padding-left: 4px; padding-right: 4px; }\n.row.g-3 { display: flex; flex-wrap: wrap; margin-left: -6px; margin-right: -6px; }\n.row.g-3 > [class*=\"col-\"] { padding-left: 6px; padding-right: 6px; }\n.row.g-4 { display: flex; flex-wrap: wrap; margin-left: -8px; margin-right: -8px; }\n.row.g-4 > [class*=\"col-\"] { padding-left: 8px; padding-right: 8px; }\n\n/* Badges */\n.badge.bg-danger { background-color: #fef2f2 !important; color: #dc2626 !important; border: 1px solid #fecaca; }\n.badge.bg-warning { background-color: #fffbeb !important; color: #d97706 !important; border: 1px solid #fde68a; }\n.badge.bg-success { background-color: #ecfdf5 !important; color: #059669 !important; border: 1px solid #a7f3d0; }\n.badge.bg-secondary-subtle { background-color: #f1f5f9 !important; color: #475569 !important; border: 1px solid #cbd5e1; }\n\n/* Buttons & Cards for task show/edit */\n.btn-outline-secondary { background: #ffffff !important; color: #475569 !important; border: 1px solid #cbd5e1 !important; }\n.btn-outline-secondary:hover { background: #f8fafc !important; color: #0f172a !important; border-color: #94a3b8 !important; }\n.btn-warning { background: #f59e0b !important; color: #ffffff !important; border: none !important; }\n.btn-warning:hover { background: #d97706 !important; }\n\n.task-worklog-panel { padding: 16px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; margin-bottom: 20px; }\n.task-worklog-row { display: grid; grid-template-columns: 100px 60px 1fr 120px; gap: 10px; padding: 8px 0; border-top: 1px solid #e2e8f0; font-size: 12.5px; }\n\n/* Task Show View Premium Layout & Comments Feed */\n.task-show-wrapper {\n    font-family: var(--tp-font-family);\n}\n\n.tp-btn-danger {\n    background: #ef4444;\n    color: #ffffff !important;\n}\n\n.tp-btn-danger:hover {\n    background: #dc2626;\n    transform: translateY(-1px);\n}\n\n.tp-status-banner {\n    background: #f8fafc;\n    border: 1px solid #e2e8f0;\n    border-radius: 10px;\n    padding: 12px 16px;\n    display: flex;\n    align-items: center;\n    justify-content: space-between;\n    flex-wrap: wrap;\n    gap: 12px;\n}\n\n.status-label {\n    font-size: 12.5px;\n    font-weight: 600;\n    color: #64748b;\n}\n\n.tp-sub-title {\n    font-size: 14px;\n    font-weight: 700;\n    color: #0f172a;\n    margin: 0 0 10px 0;\n}\n\n.tp-description-box {\n    margin-top: 10px;\n}\n\n.tp-description-text {\n    font-size: 13.5px;\n    line-height: 1.65;\n    color: #334155;\n    background: #ffffff;\n    padding: 14px;\n    border: 1px solid #f1f5f9;\n    border-radius: 8px;\n    white-space: pre-wrap;\n}\n\n.tp-empty-desc {\n    padding: 24px;\n    text-align: center;\n    background: #f8fafc;\n    border: 1px dashed #cbd5e1;\n    border-radius: 8px;\n    color: #64748b;\n}\n\n/* Activity Tabs */\n.tp-activity-tabs {\n    margin: -12px -18px;\n    background: #f8fafc;\n    padding: 6px 14px 0 14px;\n    border-bottom: 1px solid #e2e8f0 !important;\n}\n\n.tp-activity-tabs .nav-link {\n    border: none !important;\n    border-bottom: 2px solid transparent !important;\n    color: #64748b !important;\n    font-weight: 600 !important;\n    font-size: 13px !important;\n    padding: 10px 16px !important;\n    background: transparent !important;\n    transition: all 0.15s ease;\n}\n\n.tp-activity-tabs .nav-link:hover,\n.tp-activity-tabs .nav-link.active,\n.tp-activity-tabs li.active a {\n    color: #2563eb !important;\n    border-bottom: 2px solid #2563eb !important;\n    background: transparent !important;\n}\n\n/* Comments Feed */\n.user-avatar-circle {\n    width: 36px;\n    height: 36px;\n    border-radius: 50%;\n    background: #2563eb;\n    color: #ffffff;\n    font-weight: 700;\n    font-size: 12px;\n    display: flex;\n    align-items: center;\n    justify-content: center;\n}\n\n.comment-textarea {\n    min-height: 80px !important;\n    border-radius: 10px !important;\n}\n\n.tp-comment-item {\n    display: flex;\n    gap: 12px;\n    padding: 14px 0;\n    border-bottom: 1px solid #f1f5f9;\n}\n\n.tp-comment-item:last-child {\n    border-bottom: none;\n}\n\n.comment-header {\n    display: flex;\n    align-items: center;\n    justify-content: space-between;\n    margin-bottom: 4px;\n}\n\n.comment-header .user-name {\n    font-size: 13px;\n    color: #0f172a;\n    font-weight: 700;\n}\n\n.comment-header .comment-time {\n    font-size: 11.5px;\n    color: #94a3b8;\n}\n\n.comment-body {\n    font-size: 13px;\n    color: #334155;\n    line-height: 1.55;\n}\n\n.tp-empty-activity {\n    padding: 30px 16px;\n    text-align: center;\n    color: #64748b;\n}\n\n/* Sidebar Meta Items */\n.sidebar-meta-group {\n    display: flex;\n    flex-direction: column;\n    gap: 14px;\n}\n\n.sidebar-meta-item {\n    display: flex;\n    flex-direction: column;\n    gap: 4px;\n}\n\n.sidebar-meta-item .meta-label {\n    font-size: 11.5px;\n    font-weight: 600;\n    color: #64748b;\n    text-transform: uppercase;\n    letter-spacing: 0.03em;\n}\n\n.sidebar-meta-item .meta-value {\n    font-size: 13px;\n    color: #0f172a;\n}\n\n.project-link {\n    color: #2563eb;\n    font-weight: 600;\n    text-decoration: none;\n}\n\n.project-link:hover {\n    text-decoration: underline;\n}\n\n.epic-badge {\n    display: inline-flex;\n    align-items: center;\n    padding: 3px 8px;\n    background: #fffbeb;\n    border: 1px solid #fde68a;\n    color: #d97706;\n    border-radius: 6px;\n    font-size: 12px;\n    font-weight: 600;\n}\n\n.sprint-pill {\n    display: inline-flex;\n    align-items: center;\n    padding: 3px 9px;\n    background: #eff6ff;\n    border: 1px solid #bfdbfe;\n    color: #1d4ed8;\n    border-radius: 12px;\n    font-size: 12px;\n    font-weight: 600;\n}\n\n.points-badge {\n    display: inline-flex;\n    align-items: center;\n    padding: 2px 8px;\n    background: #f1f5f9;\n    border: 1px solid #cbd5e1;\n    color: #334155;\n    border-radius: 12px;\n    font-weight: 700;\n    font-size: 12px;\n}\n\n.total-hours-badge {\n    font-size: 12.5px;\n    color: #2563eb;\n    background: #eff6ff;\n    padding: 3px 10px;\n    border-radius: 12px;\n    border: 1px solid #bfdbfe;\n}\n\n/* Quick Inline Edit Buttons & CKEditor Comment Styling */\n.btn-inline-edit {\n    background: rgba(255, 255, 255, 0.2);\n    border: 1px solid rgba(255, 255, 255, 0.3);\n    border-radius: 50%;\n    width: 26px;\n    height: 26px;\n    display: inline-flex;\n    align-items: center;\n    justify-content: center;\n    cursor: pointer;\n    margin-left: 8px;\n    transition: all 0.15s ease;\n}\n\n.btn-inline-edit:hover {\n    background: rgba(255, 255, 255, 0.35);\n    transform: scale(1.08);\n}\n\n.btn-link-edit {\n    background: none;\n    border: none;\n    color: #2563eb;\n    font-size: 11px;\n    font-weight: 600;\n    cursor: pointer;\n    padding: 0;\n}\n\n.btn-link-edit:hover {\n    text-decoration: underline;\n}\n\n.btn-quick-edit-icon {\n    background: #ffffff;\n    border: 1px solid #cbd5e1;\n    color: #2563eb;\n    width: 24px;\n    height: 24px;\n    border-radius: 50%;\n    display: inline-flex;\n    align-items: center;\n    justify-content: center;\n    cursor: pointer;\n    font-size: 10px;\n    transition: all 0.15s ease;\n}\n\n.btn-quick-edit-icon:hover {\n    background: #2563eb;\n    color: #ffffff !important;\n    border-color: #2563eb;\n}\n\n.ck-content-body {\n    background: #ffffff;\n    padding: 10px 12px;\n    border: 1px solid #f1f5f9;\n    border-radius: 8px;\n    margin-top: 4px;\n    font-size: 13px;\n    line-height: 1.6;\n}\n\n.ck-content-body img {\n    max-width: 100%;\n    height: auto;\n    border-radius: 6px;\n    margin: 8px 0;\n    border: 1px solid #e2e8f0;\n}\n\n.ck-content-body p {\n    margin: 0 0 6px 0;\n}\n\n.ck-content-body p:last-child {\n    margin-bottom: 0;\n}\n\n/* Jira-style Click-to-Edit Inline Experience */\n.jira-editable-field {\n    border: 1px solid transparent;\n    border-radius: 6px;\n    padding: 3px 6px;\n    margin: -3px -6px;\n    transition: all 0.15s ease-in-out;\n    display: inline-flex;\n    align-items: center;\n}\n\n.jira-editable-field.clickable {\n    cursor: pointer;\n}\n\n.jira-editable-field.clickable:hover {\n    background-color: #f1f5f9;\n    border-color: #cbd5e1;\n}\n\n.jira-editable-field .jira-hover-pencil {\n    opacity: 0;\n    transition: opacity 0.15s ease;\n}\n\n.jira-editable-field.clickable:hover .jira-hover-pencil {\n    opacity: 1;\n}\n\n.jira-inline-badge-wrap {\n    position: relative;\n    display: inline-block;\n}\n\n.jira-inline-select {\n    font-size: 12.5px !important;\n    padding: 4px 8px !important;\n    height: auto !important;\n    border-radius: 6px !important;\n    border: 2px solid #2563eb !important;\n    box-shadow: 0 2px 8px rgba(37, 99, 235, 0.15) !important;\n}\n\n.btn-xs {\n    padding: 2px 7px !important;\n    font-size: 11px !important;\n    border-radius: 4px !important;\n}\n\n.input-sm {\n    height: 30px !important;\n    padding: 3px 8px !important;\n    font-size: 12.5px !important;\n    border-radius: 6px !important;\n}\n\n/* Force 100% Full Width for Description Box & CKEditor Containers */\n#desc-view-mode.jira-editable-field,\n.tp-description-box,\n.tp-description-text,\n#desc-edit-mode,\n#input-description,\n.cke,\n.cke_wrapper,\n.cke_editor,\n.cke_chrome,\n.comment-textarea,\n#task-comment-form,\n.comment-content-wrap,\n.ck-content-body {\n    display: block !important;\n    width: 100% !important;\n    max-width: 100% !important;\n    box-sizing: border-box !important;\n}\n\n/* Tighten gap between Add Comment label and CKEditor box */\n.tp-comment-form label {\n    margin-top: 0 !important;\n    margin-bottom: 4px !important;\n    padding-bottom: 0 !important;\n    display: block !important;\n}\n\n.tp-comment-form .cke {\n    margin-top: 2px !important;\n    margin-bottom: 10px !important;\n}\n\n/* Modern Drag & Drop Kanban Board Styles */\n.tp-kanban-board {\n    display: grid;\n    grid-template-columns: repeat(3, 1fr);\n    gap: 20px;\n    align-items: start;\n    margin-top: 16px;\n}\n\n.tp-kanban-column {\n    background: #f8fafc;\n    border: 2px dashed transparent;\n    border-radius: 14px;\n    padding: 16px;\n    min-height: 520px;\n    display: flex;\n    flex-direction: column;\n    gap: 12px;\n    transition: all 0.2s ease;\n}\n\n.tp-kanban-column.is-drop-target {\n    background: #eff6ff;\n    border-color: #3b82f6;\n    box-shadow: 0 0 12px rgba(59, 130, 246, 0.15);\n}\n\n.tp-kanban-header {\n    display: flex;\n    align-items: center;\n    justify-content: space-between;\n    padding-bottom: 10px;\n    border-bottom: 1px solid #e2e8f0;\n}\n\n.tp-kanban-title {\n    font-size: 14px;\n    font-weight: 700;\n    color: #0f172a;\n    margin: 0;\n    display: flex;\n    align-items: center;\n    gap: 8px;\n}\n\n.tp-kanban-count {\n    font-size: 11.5px;\n    font-weight: 700;\n    padding: 2px 8px;\n    border-radius: 12px;\n}\n\n.kanban-add-btn {\n    width: 22px;\n    height: 22px;\n    border-radius: 50%;\n    background: #ffffff;\n    border: 1px solid #cbd5e1;\n    color: #64748b;\n    display: flex;\n    align-items: center;\n    justify-content: center;\n    font-size: 11px;\n    text-decoration: none;\n    transition: all 0.15s ease;\n}\n\n.kanban-add-btn:hover {\n    background: #2563eb;\n    color: #ffffff;\n    border-color: #2563eb;\n}\n\n.tp-kanban-cards-container {\n    display: flex;\n    flex-direction: column;\n    gap: 12px;\n    min-height: 440px;\n}\n\n.tp-kanban-card {\n    background: #ffffff;\n    border: 1px solid #e2e8f0;\n    border-radius: 10px;\n    padding: 14px;\n    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);\n    cursor: grab;\n    transition: all 0.2s ease;\n    user-select: none;\n}\n\n.tp-kanban-card:hover {\n    transform: translateY(-2px);\n    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);\n    border-color: #cbd5e1;\n}\n\n.tp-kanban-card:active {\n    cursor: grabbing;\n}\n\n.tp-kanban-card.is-dragging {\n    opacity: 0.4;\n    border: 2px dashed #2563eb;\n    background: #f1f5f9;\n}\n\n.tp-kanban-card-meta {\n    display: flex;\n    align-items: center;\n    justify-content: space-between;\n    margin-bottom: 8px;\n}\n\n.task-key-tag {\n    font-size: 10.5px;\n    font-weight: 700;\n    color: #64748b;\n    background: #f1f5f9;\n    padding: 1px 5px;\n    border-radius: 4px;\n}\n\n.tp-kanban-card-title {\n    font-size: 13.5px;\n    font-weight: 600;\n    color: #0f172a;\n    margin: 0 0 10px 0;\n    line-height: 1.4;\n}\n\n.kanban-card-labels {\n    display: flex;\n    gap: 4px;\n    flex-wrap: wrap;\n    margin-bottom: 10px;\n}\n\n.tp-kanban-card-footer {\n    display: flex;\n    align-items: center;\n    justify-content: space-between;\n    padding-top: 8px;\n    border-top: 1px solid #f1f5f9;\n}\n\n.project-name-text {\n    font-size: 11px;\n    color: #64748b;\n    font-weight: 500;\n}\n\n.tp-kanban-empty {\n    padding: 40px 16px;\n    text-align: center;\n    border: 2px dashed #e2e8f0;\n    border-radius: 10px;\n    color: #94a3b8;\n    font-size: 12px;\n    display: flex;\n    flex-direction: column;\n    align-items: center;\n    justify-content: center;\n    background: rgba(255, 255, 255, 0.5);\n}\n\n@media (max-width: 992px) {\n    .tp-kanban-board {\n        grid-template-columns: 1fr;\n    }\n}\n\n/* Project Dashboard Styles */\n.project-dashboard-vue {\n    width: 100%;\n    min-width: 0;\n}\n\n.pd-header {\n    justify-content: space-between;\n    align-items: flex-start;\n    gap: 24px;\n    margin-bottom: 20px;\n    display: flex;\n}\n\n.pd-breadcrumb {\n    color: #64748b;\n    align-items: center;\n    gap: 8px;\n    margin-bottom: 8px;\n    font-size: 12px;\n    display: flex;\n}\n\n.pd-breadcrumb a {\n    color: #2563eb;\n    text-decoration: none;\n}\n\n.pd-title {\n    color: #172033;\n    margin: 0;\n    font-size: 28px;\n    font-weight: 700;\n}\n\n.pd-title-row {\n    align-items: center;\n    gap: 10px;\n    margin-top: 4px;\n    display: flex;\n}\n\n.pd-project-key {\n    color: #475569;\n    background: #f1f5f9;\n    border-radius: 4px;\n    padding: 3px 8px;\n    font-size: 12px;\n    font-weight: 600;\n}\n\n.pd-project-status {\n    text-transform: uppercase;\n    color: #16a34a;\n    background: #dcfce7;\n    border-radius: 4px;\n    padding: 3px 8px;\n    font-size: 11px;\n    font-weight: 700;\n}\n\n.pd-settings-btn {\n    color: #64748b;\n    border-radius: 6px;\n    justify-content: center;\n    align-items: center;\n    width: 32px;\n    height: 32px;\n    text-decoration: none;\n    transition: all .15s;\n    display: inline-flex;\n}\n\n.pd-settings-btn:hover {\n    color: #2563eb;\n    background: #f1f5f9;\n}\n\n.pd-meta {\n    color: #64748b;\n    align-items: center;\n    gap: 16px;\n    margin-top: 8px;\n    font-size: 13px;\n    display: flex;\n}\n\n.pd-meta-item {\n    align-items: center;\n    gap: 6px;\n    display: inline-flex;\n}\n\n.pd-project-nav {\n    background: #ffffff;\n    border: 1px solid #e2e8f0;\n    border-radius: 8px;\n    align-items: center;\n    gap: 4px;\n    margin-bottom: 24px;\n    padding: 6px 8px;\n    display: flex;\n    overflow-x: auto;\n    flex-wrap: nowrap;\n    scrollbar-width: thin;\n}\n.pd-project-nav::-webkit-scrollbar { height: 6px; }\n.pd-project-nav::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }\n.pd-project-nav::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 3px; }\n\n.pd-nav-item {\n    color: #475569;\n    white-space: nowrap;\n    border-radius: 6px;\n    padding: 8px 14px;\n    font-size: 13px;\n    font-weight: 500;\n    text-decoration: none;\n    transition: all .15s;\n    cursor: pointer;\n}\n\n.pd-nav-item:hover {\n    color: #172033;\n    background: #f1f5f9;\n}\n\n.pd-nav-item.active {\n    color: #ffffff !important;\n    background: #2563eb !important;\n    font-weight: 600;\n}\n\n.pd-stats {\n    grid-template-columns: repeat(4, 1fr);\n    gap: 16px;\n    margin-bottom: 24px;\n    display: grid;\n}\n\n.pd-stat-card {\n    background: #ffffff;\n    border: 1px solid #e2e8f0;\n    border-radius: 10px;\n    padding: 16px 18px;\n    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);\n}\n\n.pd-stat-label {\n    color: #64748b;\n    margin-bottom: 4px;\n    font-size: 12px;\n    font-weight: 600;\n}\n\n.pd-stat-value {\n    color: #172033;\n    font-size: 24px;\n    font-weight: 700;\n    line-height: 1.2;\n}\n\n.pd-stat-percent {\n    color: #64748b;\n    margin-top: 2px;\n    font-size: 12px;\n}\n\n.pd-stat-done {\n    border-top: 3px solid #22c55e;\n}\n\n.pd-stat-progress {\n    border-top: 3px solid #2563eb;\n}\n\n.pd-stat-overdue {\n    border-top: 3px solid #ef4444;\n}\n\n.pd-grid {\n    grid-template-columns: 1.2fr .8fr;\n    gap: 24px;\n    display: grid;\n}\n\n.pd-main, .pd-sidebar {\n    flex-direction: column;\n    gap: 20px;\n    display: flex;\n}\n\n.pd-jira-card {\n    background: #ffffff;\n    border: 1px solid #e2e8f0;\n    border-radius: 10px;\n    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);\n}\n\n.pd-jira-card-header {\n    background: #f8fafc;\n    border-bottom: 1px solid #e2e8f0;\n    justify-content: space-between;\n    align-items: center;\n    padding: 14px 18px;\n    display: flex;\n}\n\n.pd-jira-card-title {\n    color: #172033;\n    margin: 0;\n    font-size: 14px;\n    font-weight: 600;\n}\n\n.pd-jira-card-body {\n    padding: 16px 18px;\n}\n\n.pd-progress-bar {\n    background: #e2e8f0;\n    border-radius: 6px;\n    width: 100%;\n    height: 12px;\n    margin-bottom: 8px;\n    overflow: hidden;\n}\n\n.pd-progress-fill {\n    background: #2563eb;\n    border-radius: 6px;\n    height: 100%;\n    transition: width .3s ease;\n}\n\n.pd-progress-labels {\n    color: #64748b;\n    justify-content: space-between;\n    margin-bottom: 4px;\n    font-size: 12px;\n    display: flex;\n}\n\n.pd-progress-percent {\n    color: #2563eb;\n    text-align: right;\n    font-size: 24px;\n    font-weight: 700;\n}\n\n.pd-task-list {\n    flex-direction: column;\n    gap: 10px;\n    display: flex;\n}\n\n.pd-task-row {\n    background: #ffffff;\n    border: 1px solid #e2e8f0;\n    border-radius: 8px;\n    grid-template-columns: 90px 1fr auto auto auto;\n    align-items: center;\n    gap: 12px;\n    padding: 10px 14px;\n    display: grid;\n}\n\n.pd-task-key {\n    color: #64748b;\n    font-size: 12px;\n    font-weight: 600;\n}\n\n.pd-task-name {\n    color: #172033;\n    text-overflow: ellipsis;\n    white-space: nowrap;\n    font-size: 13px;\n    font-weight: 500;\n    overflow: hidden;\n}\n\n.pd-priority-badge {\n    white-space: nowrap;\n    border-radius: 4px;\n    padding: 3px 8px;\n    font-size: 11px;\n    font-weight: 600;\n}\n\n.pd-priority-high {\n    color: #dc2626;\n    background: #fef2f2;\n}\n\n.pd-priority-medium {\n    color: #ca8a04;\n    background: #fefce8;\n}\n\n.pd-priority-low {\n    color: #16a34a;\n    background: #f0fdf4;\n}\n\n.pd-task-assignee {\n    color: #64748b;\n    white-space: nowrap;\n    font-size: 12px;\n}\n\n.pd-task-date {\n    color: #94a3b8;\n    white-space: nowrap;\n    font-size: 12px;\n}\n\n.pd-insight-item {\n    color: #172033;\n    align-items: center;\n    gap: 8px;\n    margin-bottom: 10px;\n    font-size: 13px;\n    font-weight: 600;\n    display: flex;\n}\n\n.pd-risk-list {\n    margin-bottom: 12px;\n}\n\n.pd-risk-item {\n    color: #475569;\n    padding: 4px 0;\n    font-size: 13px;\n}\n\n.pd-dist-list {\n    flex-direction: column;\n    gap: 12px;\n    display: flex;\n}\n\n.pd-dist-row {\n    grid-template-columns: 80px 40px 1fr;\n    align-items: center;\n    gap: 12px;\n    display: grid;\n}\n\n.pd-dist-label {\n    color: #334155;\n    font-size: 13px;\n    font-weight: 500;\n}\n\n.pd-dist-value {\n    color: #172033;\n    text-align: right;\n    font-size: 13px;\n    font-weight: 600;\n}\n\n.pd-dist-bar {\n    background: #e2e8f0;\n    border-radius: 4px;\n    height: 8px;\n    overflow: hidden;\n}\n\n.pd-dist-fill {\n    background: #2563eb;\n    border-radius: 4px;\n    height: 100%;\n    transition: width .3s ease;\n}\n\n.pd-empty, .pd-empty-small {\n    text-align: center;\n    color: #64748b;\n    padding: 16px;\n    font-size: 13px;\n}\n\n.pd-activity-section .pd-jira-card { margin-top: 12px; }\n.pd-activity-list {\n    list-style: none;\n    margin: 0;\n    padding: 0;\n    max-height: 640px;\n    overflow-y: auto;\n}\n.pd-activity-item {\n    display: flex;\n    gap: 12px;\n    padding: 12px 4px;\n    border-bottom: 1px solid #eef2f7;\n}\n.pd-activity-item:last-child { border-bottom: 0; }\n.pd-activity-icon {\n    flex: 0 0 36px;\n    height: 36px;\n    width: 36px;\n    border-radius: 50%;\n    background: #f1f5f9;\n    display: flex;\n    align-items: center;\n    justify-content: center;\n    color: #2563eb;\n}\n.pd-activity-icon.pd-activity-deleted,\n.pd-activity-icon.pd-activity-comment_deleted,\n.pd-activity-icon.pd-activity-removed { background: #fef2f2; color: #dc2626; }\n.pd-activity-icon.pd-activity-created,\n.pd-activity-icon.pd-activity-logged_time { background: #ecfdf5; color: #059669; }\n.pd-activity-icon.pd-activity-invited,\n.pd-activity-icon.pd-activity-restored { background: #eff6ff; color: #2563eb; }\n.pd-activity-body { flex: 1 1 auto; min-width: 0; }\n.pd-activity-text { font-size: 14px; color: #1f2937; line-height: 1.5; word-break: break-word; }\n.pd-activity-subject { font-weight: 600; color: #2563eb; }\n.pd-activity-meta { color: #6b7280; font-size: 13px; }\n.pd-activity-time { color: #94a3b8; font-size: 12px; margin-top: 4px; }\n.pd-activity-changes {\n    margin-top: 6px;\n    display: flex;\n    flex-wrap: wrap;\n    gap: 6px;\n}\n.pd-activity-change {\n    background: #f8fafc;\n    border: 1px solid #e2e8f0;\n    border-radius: 4px;\n    padding: 2px 8px;\n    font-size: 12px;\n    color: #334155;\n}\n.pd-activity-field { font-weight: 600; color: #1e293b; }\n.pd-activity-old { color: #dc2626; text-decoration: line-through; }\n.pd-activity-new { color: #059669; font-weight: 600; }\n\n@media (max-width: 1024px) {\n    .pd-grid {\n        grid-template-columns: 1fr;\n    }\n    .pd-stats {\n        grid-template-columns: repeat(2, 1fr);\n    }\n}\n", ""]);

// exports


/***/ }),
/* 12 */
/***/ (function(module, exports) {

throw new Error("Module build failed: Error: \n\nVue packages version mismatch:\n\n- vue@3.5.41 (/home/tuantn1807/project/TaskPilot/node_modules/vue/index.js)\n- vue-template-compiler@2.7.16 (/home/tuantn1807/project/TaskPilot/node_modules/vue-template-compiler/package.json)\n\nThis may cause things to work incorrectly. Make sure to use the same version for both.\nIf you are using vue-loader@>=10.0, simply update vue-template-compiler.\nIf you are using vue-loader@<10.0 or vueify, re-installing vue-loader/vueify should bump vue-template-compiler to the latest.\n\n    at Object.<anonymous> (/home/tuantn1807/project/TaskPilot/node_modules/vue-template-compiler/index.js:10:9)\n    at Module._compile (node:internal/modules/cjs/loader:1521:14)\n    at Module._extensions..js (node:internal/modules/cjs/loader:1623:10)\n    at Module.load (node:internal/modules/cjs/loader:1266:32)\n    at Module._load (node:internal/modules/cjs/loader:1091:12)\n    at Module.require (node:internal/modules/cjs/loader:1289:19)\n    at require (node:internal/modules/helpers:182:18)\n    at Object.<anonymous> (/home/tuantn1807/project/TaskPilot/node_modules/vue-loader/lib/parser.js:1:18)\n    at Module._compile (node:internal/modules/cjs/loader:1521:14)\n    at Module._extensions..js (node:internal/modules/cjs/loader:1623:10)\n    at Module.load (node:internal/modules/cjs/loader:1266:32)\n    at Module._load (node:internal/modules/cjs/loader:1091:12)\n    at Module.require (node:internal/modules/cjs/loader:1289:19)\n    at require (node:internal/modules/helpers:182:18)\n    at Object.<anonymous> (/home/tuantn1807/project/TaskPilot/node_modules/vue-loader/lib/loader.js:3:15)\n    at Module._compile (node:internal/modules/cjs/loader:1521:14)\n    at Module._extensions..js (node:internal/modules/cjs/loader:1623:10)\n    at Module.load (node:internal/modules/cjs/loader:1266:32)\n    at Module._load (node:internal/modules/cjs/loader:1091:12)\n    at Module.require (node:internal/modules/cjs/loader:1289:19)\n    at require (node:internal/modules/helpers:182:18)\n    at Object.<anonymous> (/home/tuantn1807/project/TaskPilot/node_modules/vue-loader/index.js:1:18)\n    at Module._compile (node:internal/modules/cjs/loader:1521:14)\n    at Module._extensions..js (node:internal/modules/cjs/loader:1623:10)\n    at Module.load (node:internal/modules/cjs/loader:1266:32)\n    at Module._load (node:internal/modules/cjs/loader:1091:12)\n    at Module.require (node:internal/modules/cjs/loader:1289:19)\n    at require (node:internal/modules/helpers:182:18)\n    at loadLoader (/home/tuantn1807/project/TaskPilot/node_modules/loader-runner/lib/loadLoader.js:13:17)\n    at iteratePitchingLoaders (/home/tuantn1807/project/TaskPilot/node_modules/loader-runner/lib/LoaderRunner.js:169:2)");

/***/ })
/******/ ]);