/**
 * The EN (E-NOISE) Javascript object
 *
 * @author  Lupo Montero <lupo@e-noise.com>
 * @version 0.0.1
 */

"use strict";

(function() {

var state = {
    id: '__state',
    debug: false,
    debugOpen: false,
    tabOpen: 'log',
    debugAjax: true
};

var checkDeps = function() {
    var libs = [ 'jQuery', 'JSON' ];
    var browserFeatures = [ 'localStorage', 'sessionStorage' ];

    for (var i=0; i<libs.length; i++) {
        if (typeof window[libs[i]] === 'undefined') {
            throw 'EN depends on ' + libs[i] + ' and it isn\'t loaded yet!';
        }
    }

    for (var j=0; j<browserFeatures.length; j++) {
        try {
            if (typeof window[browserFeatures[j]] === 'undefined') {
                throw 'EN depends on ' + browserFeatures[j] + ' and your browser doesn\'t support it!';
            }
        } catch (e) {
            throw e;
        }
    }
};

var storageStats = function() {
    var str = '';
    var types = ['local', 'session'];
    var s;

    for (var i=0; i<types.length; i++) {
        s = window[types[i] + 'Storage'];

        str += '<p><strong>' + types[i] + ' (' + s.length + ')</strong></p>';
        str += '<ul>';

        for (var j=0; j<s.length; j++) {
            var key = s.key(j);
            var value = { length: 0 };

            try { value = JSON.parse(s[key]); } catch(e) {}

            str += '<li><p>' + key + ' (' + value.length + ')';
            if (value.length) {
                str += ' - <a href="#" onclick="jQuery(\'#EN-debug-console-storage-'+types[i]+'-'+key+'\').toggle();" style="color: #CCC;">view contents</a>';
                str += ' - <a href="#" onclick="EN.mapper({ type: \'' + types[i] + '\', store: \'' + key + '\' }).empty();" style="color: #CCC;">empty</a>';
            }
            str += ' - <a href="#" onclick="EN.mapper({ type: \'' + types[i] + '\', store: \'' + key + '\' }).remove();" style="color: #CCC;">remove</a>';
            str += '</p><p><textarea id="EN-debug-console-storage-'+types[i]+'-'+key+'" style="display: none; width: 100%; height: 50px; font-size: 9px; padding: 3px; background: #999; overflow: auto;">' + JSON.stringify(value, null, '  ') + '</textarea></li></p>';
        }

        str += '</ul>';
    }

    return str;
};

var EN = function(options) {
    checkDeps();

    var storedState = EN.mapper().findOne('__state');
    if (typeof storedState !== 'undefined') {
        jQuery.extend(state, storedState);
    }

    if (typeof options === 'object') {
        for (var key in options) {
            if (options.hasOwnProperty(key) && state.hasOwnProperty(key) && key !== 'id') {
                state[key] = options[key];
            }
        }
    }

    EN.debug(state.debug);

    jQuery('#EN-debug-console-options-debugAjax').attr('checked', state.debugAjax);

    EN.log('Loaded!');

    return this;
};

EN.version = function() {
    return "0.0.1";
};

EN.option = function(k, v) {
    if (arguments.length > 1) {
        switch (k) {
        case 'debugAjax' :
            state[k] = Boolean(v);
            EN.mapper().insert(state);
            break;
        default :
            throw 'Trying to set unknown option!';
        }
    }

    return state[k];
};

EN.parseString = function(str)
{
    var pairs = str.split('&');
    var params = {};

    for (var key in pairs) {
        var param = pairs[key].split('=');
        params[param[0]] = param[1];
    }

    return params;
};

var consoleHtml = '<style>' +
    '#EN-debug-console-wrapper { position: fixed; bottom: 0px; left: 0px; width: 100%; background: transparent; }' +
    '#EN-debug-console { background: rgba(0,0,0,0.85); color: #999; padding: 0; margin: 0; border: 1px solid #999; }' +
    '#EN-debug-console-header { height: 13px; margin:0; padding: 5px 4px 4px; background: #444; font-size: 13px; font-family: "helvetica", arial, clean, sans-serif; letter-spacing: 0.05em }' +
    '#EN-debug-console-close-button { display: block; position: absolute; right: 6px; margin-top: -19px; color: #FFF; text-decoration:none; }' +
    '#EN-debug-console-inner-wrapper { padding: 0; margin: 0; }' +
    '#EN-debug-console-tabs { margin: 0; padding: 3px; background: transparent; border-bottom:1px solid #999999; border-top:1px solid #999999;}' +
    '#EN-debug-console-tabs li { margin: 0; padding: 0; display: inline; }' +
    '#EN-debug-console-tabs li a { display: inline-block; padding: 3px 5px 2px; margin: 0 3px 0 0; -moz-border-radius: 3px; -webkit-border-radius: 3px; -o-border-radius: 3px; background: red; text-decoration: none; color: #e2e2e2; font-size: 10px; font-family: "helvetica", arial, clean, sans-serif; border:1px solid #666666; outline:none; }' +
    '.EN-debug-console-body { padding: 10px; height: 200px; overflow: auto; font-size: 10px; font-family: Helvetica; }' +
    '.EN-debug-console-body p { padding: 0; margin: 5px 0; }' +
    '</style>' +
    '<div id="EN-debug-console-wrapper">' +
    '<div id="EN-debug-console">' +
    '<h4 id="EN-debug-console-header">E-NOISE Javascript debug console</h4>' +
    '<a id="EN-debug-console-close-button" href="#">X</a>' +
    '<div id="EN-debug-console-inner-wrapper">' +
    '<ul id="EN-debug-console-tabs">' +
    '<li><a href="log">Log</a></li>' +
    '<li><a href="storage">Storage</a></li>' +
    '<li><a href="browser-features">Browser features</a></li>' +
    '<li><a href="options">Options</a></li>' +
    '</ul>' +
    '<div id="EN-debug-console-log" class="EN-debug-console-body">' +
    '<p><strong>EN Javascript framework version ' + EN.version() + '</strong></p>' +
    '</div>' +
    '<div id="EN-debug-console-storage" class="EN-debug-console-body">' +
    storageStats() +
    '</div>' +
    '<div id="EN-debug-console-browser-features" class="EN-debug-console-body">' +
    'Here we show browser features (based on modernizr?)...' +
    '</div>' +
    '<div id="EN-debug-console-options" class="EN-debug-console-body">' +
    'Log AJAX events: <input type="checkbox" id="EN-debug-console-options-debugAjax" onchange="EN.option(\'debugAjax\', (!EN.option(\'debugAjax\')));" />' +
    '</div>' +
    '</div><!-- #EN-debug-console-inner-wrapper -->' +
    '</div><!-- #EN-debug-console -->' +
    '</div><!-- #EN-debug-console-wrapper -->',
    toggleConsole = function() {
        var old_debugOpen = state.debugOpen;

        if (jQuery('#EN-debug-console-wrapper').length === 0) {
            jQuery('body').append(consoleHtml);
        }

        jQuery('#EN-debug-console-close-button').click(function(e) {
            e.preventDefault();

            if (jQuery('#EN-debug-console-inner-wrapper').css('display') === 'none') {
                state.debugOpen = true;
            } else {
                state.debugOpen = false;
            }

            if (old_debugOpen !== state.debugOpen) {
                EN.mapper().insert(state);
            }

            jQuery('#EN-debug-console-inner-wrapper').slideToggle('slow');
        });

        jQuery('#EN-debug-console-tabs a').click(function(e) {
            var selectedHref = jQuery(this).attr('href');

            e.preventDefault();

            jQuery('#EN-debug-console-tabs a').each(function(k, v) {
                var href = jQuery(v).attr('href');
                if (selectedHref !== href) {
                    jQuery(v).css('color', '#666').css('background', '#CCC');
                    jQuery('#EN-debug-console-' + href).hide();
                } else {
                    state.tabOpen = href;
                    EN.mapper().insert(state);
                    jQuery(v).css('color', '#e2e2e2').css('background', '#444');
                    jQuery('#EN-debug-console-' + href).show();
                }
            });
        });

        jQuery('#EN-debug-console-tabs a[href='+state.tabOpen+']').click();
    };

EN.debug = function(bool) {
    var logAjax = function(msg) {
        if (state.debugAjax) {
            EN.log(msg);
        }
    };

    if (typeof bool === 'boolean') {
        state.debug = (bool) ? true : false;

        if (state.debug) {
            toggleConsole();
        }

        if (!state.debug || !state.debugOpen) {
            jQuery('#EN-debug-console-inner-wrapper').hide();
        }

        jQuery('body')
            .ajaxStart(function() {
                logAjax('jQuery triggered the ajaxStart event!');
            })
            .ajaxSend(function(event, XMLHttpRequest, ajaxOptions) {
                logAjax('jQuery.ajax() is sendind ' + ((ajaxOptions.async) ? 'a' : '') + 'synchronous request:<br />' + ajaxOptions.type + ((ajaxOptions.type!=='GET') ? ' --data ' + ajaxOptions.data : '') + ' ' + ajaxOptions.url);
            })
            .ajaxSuccess(function(event, XMLHttpRequest, ajaxOptions) {
                logAjax('jQuery.ajax() request completed successfully!<br />Response Headers:<br />' + XMLHttpRequest.getAllResponseHeaders() + '<br />Response body:<br />' + XMLHttpRequest.responseText);
            })
            .ajaxError(function(event, XMLHttpRequest, ajaxOptions, thrownError) {
                logAjax('ajaxError!');
            })
            .ajaxComplete(function(event, XMLHttpRequest, ajaxOptions) {
                // logAjax('ajaxComplete!');
            })
            .ajaxStop(function() {
                logAjax('jQuery triggered the ajaxStop event!');
            });
    }

    return state.debug;
};

EN.log = function() {
    var o = jQuery('#EN-debug-console-log');

    // Send updates to firebug's console
    for (var i=0; i<arguments.length; i++) {
        var arg = arguments[i];
        if (typeof arg === 'object') {
            arg = JSON.stringify(arg);
        }

        o.append('<br />&gt; ' + arg);

        if (typeof console !== 'undefined') {
            console.log(arguments[i]);
        }
    }
};

EN.mapper = function(opts) {
    var options = { type: 'local', store: '__EN_data' };
    var storage = {};
    var data = [];
    var encoded = '';
    var log = function(store, msg) {
        // hide private data from log
        if (store !== '__EN_data') {
            EN.log(msg);
        }
    };

    if (typeof opts === 'object' && typeof opts.type === 'string') {
        jQuery.extend(options, opts);
    }

    switch (options.type) {
    case 'local' :
        if (typeof localStorage !== 'object') {
            throw 'HTML5 local storage not supported by browser!';
        }
        storage = localStorage;
        break;
    case 'session' :
        if (typeof sessionStorage !== 'object') {
            throw 'HTML5 session storage not supported by browser!';
        }
        storage = sessionStorage;
        break;
    default :
        throw 'Unknown storage type \'' + options.type + '\' passed to EN.mapper!';
    }

    encoded = storage.getItem(options.store);

    if (encoded !== '') {
        log(options.store, 'Loading store \'' + options.store + '\' from ' + options.type + ' storage ...');

        data = JSON.parse(encoded);

        if (data === null) {
            data = [];
        }
    }

    return {
        find: function(where, limit, page) {
            if (data === null) {
                data = [];
            }

            return data;
        },
        findOne: function(id) {
            for (var i in data) {
                if (data[i].id === id) {
                    return data[i];
                }
            }
        },
        insert: function(obj) {
            if (typeof obj.id === 'undefined') {
                obj.id = data.length;
            }

            this['delete'](obj.id);
            data.push(obj);
            log(options.store, 'Writing object id \'' + obj.id + '\' to store \'' + options.store + '\' in ' + options.type + ' storage ...');
            storage.setItem(options.store, JSON.stringify(data));
            jQuery('#EN-debug-console-storage').html(storageStats());

            return true;
        },
        "delete": function(id) {
            for (var i in data) {
                if (data.hasOwnProperty(i) && data[i].id === id) {
                    log(options.store, 'Deleting obj id \'' + id + '\' from store \'' + options.store + '\' in ' + options.type + ' storage ...');
                    data.splice(i, 1);
                    storage.setItem(options.store, JSON.stringify(data));
                    jQuery('#EN-debug-console-storage').html(storageStats());
                }
            }

            return true;
        },
        empty: function() {
            log(options.store, 'Emptying store \'' + options.store + '\' in ' + options.type + ' storage ...');
            data = [];
            storage.setItem(options.store, data);
            jQuery('#EN-debug-console-storage').html(storageStats());
        },
        remove: function() {
            log(options.store, 'Removing store \'' + options.store + '\' from ' + options.type + ' storage ...');
            data = [];
            storage.removeItem(options.store);
            jQuery('#EN-debug-console-storage').html(storageStats());
        }
    };
};

window.EN = EN;

})();
