/*!
 * x.js JavaScript library v0.2
 *
 * This library adds handy functionality to built-in objects.
 *
 * Copyright 2010, Lupo Montero
 * Dual licensed under the MIT or GPL Version 2 licenses.
 */

/*jslint eqeqeq: true */

/**
 * Convert a number of bytes to a human friendly string.
 *
 * @return String
 */
Number.prototype.bytesToHuman = function () {
  return (this/(1000*1000)).toFixed(2) + 'Mb';
};

/**
 * Simple function to parse URL-like strings to objects (taken from
 * mshn.core.js)
 *
 * @return Object
 */
String.prototype.parse = function (str) {
  var
    str = str || this,
    pairs = str.split('&'),
    params = {},
    le = pairs.length,
    i,
    current;

  for (i=0; i<le; i++) {
    current = pairs[i].split('=');
    params[current[0]] = current[1];
  }

  return params;
};

/**
 * Limit string to a certain number of characters and add trailing dots '...'
 * when trimming.
 *
 * @return String
 */
String.prototype.limitChars = function (max) {
  var len = this.length;

  if (len <= max) {
    return this;
  }

  return this.substr(0, max-3) + '...';
};

/**
 * Convert a date object to a nice human readable string.
 *
 * @return String
 */
Date.prototype.toNiceString = function () {
  var seconds = (+(new Date()) - +this)/1000, time;

  if (seconds < 60) {
    return 'just now';
  } else if (seconds < 60*60) {
    time = Math.round(seconds/60);
    return (time>1) ? time + ' minutes ago' : '1 minute ago';
  } else if (seconds < 60*60*24) {
    return 'Today, ' + this.getHours() + ':' + this.getSeconds();
  } else if (seconds < 60*60*24*7) {
    time = Math.round(seconds/(60*60*24));
    if (time>1) {
      return this.toLocaleString();
    } else {
      return 'Yesterday' + this.getHours() + ':' + this.getSeconds();
    }
  } else {
    return this.toLocaleString();
  }
};
