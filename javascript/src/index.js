import 'promise-polyfill';
import 'whatwg-fetch';

import Vue from 'vue';

// IE support
if (!window.Promise) {
  window.Promise = Promise;
}

/**
 * Debounce function which prevents users from spamming requests on search
 * field.
 * 
 * @param {Function} func Function for the debounce function to be applied to
 * @param {Number} wait Time taken before function is triggered
 * @param {Boolean} immediate Boolean if set to true will trigger the function immediately
 * 
 * @return {void}
 */
const debounce = function(func, wait, immediate) {
  let timeout;
  let context = this;
  let args = arguments;

  clearTimeout(timeout);

  timeout = setTimeout(function() {
    timeout = null;

    if (!immediate) {
      func.apply(context, args);
    }
  }, wait);

  if (immediate && !timeout) {
    func.apply(context, args);
  }
};

// target searchfield
const fieldEl = document.querySelector('.js-searchfield');

if (fieldEl) {

  // construct new Vue object
  const SearchField = new Vue({
    el: fieldEl,

    data() {
      return {
        suggestions: [],
      };
    },

    methods: {
      /**
       * When the onBlur state is triggered we initiate the getSuggestions 
       * method to get search results from Solr. We also ensure that this call
       * is debounced, preventing the ability to consistently spam the request.
       * 
       * @param {Event} e
       * 
       * @return {void}
       */
      onBlur(e) {
        debounce(function() {
          this.getSuggestions(e);
        }.bind(this), 500);
      },

      /**
       * Gets the search suggestions from Solr based on the keyword entered by
       * the user. It uses the fetchAPI to make this call.
       * 
       * @param {Event} e
       * 
       * @return void
       */
      getSuggestions(e) {
        const url = `${window.location.href}/searchFieldAction`;
        let data = {
          keyword: e.target.value
        };

        // execute the fetch call with 'body' set as keyword input
        // populate suggestions array with data from response
        fetch(url, {
          method: 'POST',
          credentials: 'same-origin',
          headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/x-www-form-urlencoded; charset=utf-8',
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: JSON.stringify(data)
        })
        .then(response => {
          response
            .json()
            .then(data => { 
              this.suggestions = (Object.keys(data).length > 0)
                ? data
                : [];
            });
        })
        .catch(error => {
          // we will leave error handling for now
        });
      },

    },
  });
}