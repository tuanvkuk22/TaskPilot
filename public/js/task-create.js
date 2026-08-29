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
/******/ ({

/***/ 10:
/***/ (function(module, exports) {

throw new Error("Module build failed: Error: \n\nVue packages version mismatch:\n\n- vue@3.5.41 (/home/tuantn1807/project/TaskPilot/node_modules/vue/index.js)\n- vue-template-compiler@2.7.16 (/home/tuantn1807/project/TaskPilot/node_modules/vue-template-compiler/package.json)\n\nThis may cause things to work incorrectly. Make sure to use the same version for both.\nIf you are using vue-loader@>=10.0, simply update vue-template-compiler.\nIf you are using vue-loader@<10.0 or vueify, re-installing vue-loader/vueify should bump vue-template-compiler to the latest.\n\n    at Object.<anonymous> (/home/tuantn1807/project/TaskPilot/node_modules/vue-template-compiler/index.js:10:9)\n    at Module._compile (node:internal/modules/cjs/loader:1521:14)\n    at Module._extensions..js (node:internal/modules/cjs/loader:1623:10)\n    at Module.load (node:internal/modules/cjs/loader:1266:32)\n    at Module._load (node:internal/modules/cjs/loader:1091:12)\n    at Module.require (node:internal/modules/cjs/loader:1289:19)\n    at require (node:internal/modules/helpers:182:18)\n    at Object.<anonymous> (/home/tuantn1807/project/TaskPilot/node_modules/vue-loader/lib/parser.js:1:18)\n    at Module._compile (node:internal/modules/cjs/loader:1521:14)\n    at Module._extensions..js (node:internal/modules/cjs/loader:1623:10)\n    at Module.load (node:internal/modules/cjs/loader:1266:32)\n    at Module._load (node:internal/modules/cjs/loader:1091:12)\n    at Module.require (node:internal/modules/cjs/loader:1289:19)\n    at require (node:internal/modules/helpers:182:18)\n    at Object.<anonymous> (/home/tuantn1807/project/TaskPilot/node_modules/vue-loader/lib/loader.js:3:15)\n    at Module._compile (node:internal/modules/cjs/loader:1521:14)\n    at Module._extensions..js (node:internal/modules/cjs/loader:1623:10)\n    at Module.load (node:internal/modules/cjs/loader:1266:32)\n    at Module._load (node:internal/modules/cjs/loader:1091:12)\n    at Module.require (node:internal/modules/cjs/loader:1289:19)\n    at require (node:internal/modules/helpers:182:18)\n    at Object.<anonymous> (/home/tuantn1807/project/TaskPilot/node_modules/vue-loader/index.js:1:18)\n    at Module._compile (node:internal/modules/cjs/loader:1521:14)\n    at Module._extensions..js (node:internal/modules/cjs/loader:1623:10)\n    at Module.load (node:internal/modules/cjs/loader:1266:32)\n    at Module._load (node:internal/modules/cjs/loader:1091:12)\n    at Module.require (node:internal/modules/cjs/loader:1289:19)\n    at require (node:internal/modules/helpers:182:18)\n    at loadLoader (/home/tuantn1807/project/TaskPilot/node_modules/loader-runner/lib/loadLoader.js:13:17)\n    at iteratePitchingLoaders (/home/tuantn1807/project/TaskPilot/node_modules/loader-runner/lib/LoaderRunner.js:169:2)");

/***/ }),

/***/ 8:
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(9);


/***/ }),

/***/ 9:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
throw new Error("Cannot find module \"vue\"");
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__components_TaskCreate_vue__ = __webpack_require__(10);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__components_TaskCreate_vue___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_1__components_TaskCreate_vue__);



var d = window.taskCreateData || {};

var app = Object(__WEBPACK_IMPORTED_MODULE_0_vue__["createApp"])(__WEBPACK_IMPORTED_MODULE_1__components_TaskCreate_vue__["default"], {
    projects: d.projects || [],
    users: d.users || [],
    reporters: d.reporters || [],
    epics: d.epics || [],
    sprints: d.sprints || [],
    csrfToken: d.csrf_token || '',
    storeUrl: d.store_url || '/tasks',
    tasksIndexUrl: d.tasks_index_url || '/tasks',
    oldLabels: d.old_labels || ''
});

app.mount('#task-create-app');

/***/ })

/******/ });