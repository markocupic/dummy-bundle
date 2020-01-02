"use strict";

function _instanceof(left, right) { if (right != null && typeof Symbol !== "undefined" && right[Symbol.hasInstance]) { return !!right[Symbol.hasInstance](left); } else { return left instanceof right; } }

function _classCallCheck(instance, Constructor) { if (!_instanceof(instance, Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

/*
 * Copyright Marko Cupic <m.cupic@gmx.ch>, 2019
 * @author Marko Cupic
 * @link https://github.com/markocupic/dummy-bundle
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
var VuePixabayImageCollection = // Pixabay limit
    function VuePixabayImageCollection(options) {
        _classCallCheck(this, VuePixabayImageCollection);

        var VuePixabayImageCollectionOptions = options;
        /**
         * Create new Vue.js instance
         */

        new Vue({
            el: '#' + VuePixabayImageCollectionOptions.el,

            /**
             * Data
             */
            data: {
                options: null,
                items: [],
                select: null,
                activeCategory: '',
                url: '',
                busy: false,
                total: 0,
                itemsLoaded: 0,
                allImagesLoaded: false
            },

            /**
             * Created
             */
            created: function created() {
                var self = this;
                self.options = VuePixabayImageCollectionOptions;
                self.setSearchParam('page', 1); // Set first option in the select menu as the active category

                if (self.activeCategory === '') {
                    self.activeCategory = self.options.searchParams.categories[0];
                } // Trigger oncreated callback


                if (self.options.callbacks.oncreated && typeof self.options.callbacks.oncreated === "function") {
                    self.options.callbacks.oncreated(self);
                } // Fetch json from pixabay API


                self.getResource();
            },

            /**
             * Target select menu
             */
            mounted: function mounted() {
                var self = this; // You can not acces to this.$el before the vue instance is mounted,
                // see https://stackoverflow.com/questions/45402403/property-this-el-undefined-on-single-file-component-vue-js
                // and https://vuejs.org/v2/guide/instance.html#Instance-Lifecycle-Hooks

                self.options.select = document.querySelector('#' + self.$el.id + ' select[name="pixabayCategorySelect"]');
                self.select = self.options.select;
            },

            /**
             * Methods
             */
            methods: {
                /**
                 * Get search param
                 * @param index
                 * @param value
                 */
                setSearchParam: function setSearchParam(index, value) {
                    var self = this;
                    self.options.searchParams[index] = value;
                },

                /**
                 * Set search param
                 * @param index
                 * @returns {*}
                 */
                getSearchParam: function setSearchParam(index) {
                    var self = this;

                    if (self.options.searchParams[index]) {
                        return self.options.searchParams[index];
                    }

                    return null;
                },

                /**
                 * Fetch json from pixabay API
                 */
                getResource: function getResource() {
                    var self = this;
                    var pixabayApiKey = self.options.pixabayApiKey;
                    var url = 'https://pixabay.com/api/?key=' + pixabayApiKey + '&category=' + self.activeCategory;

                    if (self.options.searchParams) {
                        for (var prop in self.options.searchParams) {
                            if (prop === 'categories') {
                                continue;
                            }

                            url = url + '&' + prop + '=' + self.options.searchParams[prop];
                        }
                    }

                    self.url = url; // Trigger onbeforeload callback

                    if (self.options.callbacks.onbeforeload && typeof self.options.callbacks.onbeforeload === "function") {
                        self.options.callbacks.onbeforeload(self);
                    }

                    self.busy = true; // Fetch

                    fetch(self.url).then(function (res) {
                        return res.json();
                    }).then(function (json) {
                        if (json.totalHits > 0) {
                            self.total = json.total > VuePixabayImageCollection.API_LIMIT ? VuePixabayImageCollection.API_LIMIT : json.total;

                            for (var i in json.hits) {
                                if (self.items.length < VuePixabayImageCollection.API_LIMIT) {
                                    self.items.push(json.hits[i]);
                                    self.itemsLoaded++;
                                }
                            }
                        }

                        return json;
                    }).then(function (json) {
                        // Trigger onload callback
                        if (self.options.callbacks.onload && typeof self.options.callbacks.onload === "function") {
                            self.options.callbacks.onload(json, self);
                        }

                        return json;
                    }).then(function (json) {
                        self.busy = false;
                        return json;
                    }).then(function (json) {
                        if (self.total <= self.items.length) {
                            self.allImagesLoaded = true;
                        }

                        return json;
                    });
                },

                /**
                 * On change
                 */
                onChange: function onChange() {
                    var self = this; // Reset

                    self.reset(); // Trigger onchange callback

                    if (self.options.callbacks.onchange && typeof self.options.callbacks.onchange === "function") {
                        self.options.callbacks.onchange(self);
                    }

                    self.getResource();
                },

                /**
                 *
                 */
                onLoadMore: function onLoadMore() {
                    var self = this;
                    self.setSearchParam('page', self.getSearchParam('page') + 1);

                    // Trigger onchange callback
                    if (self.options.callbacks.onloadmore && typeof self.options.callbacks.onloadmore === "function") {
                        self.options.callbacks.onloadmore(self);
                    }

                    self.getResource();
                },

                /**
                 * Reset properties
                 */
                reset: function reset() {
                    var self = this;
                    self.items = [];
                    self.setSearchParam('page', 1);
                    self.busy = false;
                    self.itemsLoaded = 0;
                    self.allImagesLoaded = false;
                }
            }
        });
    };

_defineProperty(VuePixabayImageCollection, "API_LIMIT", 600);
