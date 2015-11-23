var amber = {

  hovering_on_popup : false,
  hovering_on_link : false,
  locale : 'en',
  country : '',
  rtl : false,
  translations : {
    en : {
      interstitial_html_up :
'<div class="amber-interstitial amber-up"><a href="#" class="amber-close"></a><div class="amber-body">\
<div class="amber-status-text">\This page should be available</div><div class="amber-cache-text">{{NAME}} has a snapshot from {{DATE}}</div>\
<a class="amber-focus amber-cache-link" href="{{CACHE}}">View the snapshot</a><a class="amber-memento-link" href="#">\
{{MEMENTO_MESSAGE}}</a><div class="amber-iframe-container"><a href="{{LINK}}"></a>\
<iframe sandbox="" src="{{LINK}}"/></div><a class="amber-original-link" href="{{LINK}}">Continue to the page</a></div>\
<a class="amber-info" href="http://amberlink.org" target="_blank">i</a></div>',
      interstitial_html_down :
'<div class="amber-interstitial amber-down"><a href="#" class="amber-close"></a><div class="amber-body">\
<div class="amber-status-text">This page may not be available</div><div class="amber-cache-text">{{NAME}} has a snapshot from {{DATE}}</div>\
<a class="amber-focus amber-cache-link" href="{{CACHE}}">View the snapshot</a><a class="amber-memento-link" href="#">\
{{MEMENTO_MESSAGE}}</a><div class="amber-iframe-container"><a href="{{LINK}}"></a>\
<iframe sandbox="" src="{{LINK}}"/></div><a class="amber-original-link" href="{{LINK}}">Continue to the page</a></div>\
<a class="amber-info" href="http://amberlink.org" target="_blank">i</a></div>',
      hover_html_up   : 
'<div class="amber-hover amber-up"><a class="amber-info" href="http://amberlink.org" target="_blank">i</a>\
<div class="amber-text"><div class="amber-status-text">This page should be available</div>\
<div class="amber-cache-text">{{NAME}} has a snapshot from {{DATE}}</div></div>\
<a class="amber-memento-link" href="#">{{MEMENTO_MESSAGE}}</a>\
<div class="amber-links"><a class="amber-cache-link" href="{{CACHE}}">View the snapshot</a>\
<a href="{{LINK}}" class="amber-focus">Continue to the page</a></div><div class="amber-arrow"></div>\
</div>',
      hover_html_down : 
'<div class="amber-hover amber-down"><a class="amber-info" href="http://amberlink.org" target="_blank">i</a>\
<div class="amber-text"><div class="amber-status-text">This page may not be available</div>\
<div class="amber-cache-text">{{NAME}} has a snapshot from {{DATE}}</div></div>\
<a class="amber-memento-link" href="#">{{MEMENTO_MESSAGE}}</a>\
<div class="amber-links"><a class="amber-cache-link amber-focus" href="{{CACHE}}">View the snapshot</a>\
<a href="{{LINK}}">Continue to the page</a></div>\
<div class="amber-arrow"></div></div>',
      this_site: "This site",
      timegate_with_date: 'Another archive has an alternate snapshot from {{MEMENTO_DATE}}',
      timegate_without_date: 'Another archive has an alternate snapshot'
    },
    fa : {
      interstitial_html_up :
      '<div class="amber-interstitial"><a href="#" class="amber-close"></a><div class="amber-body"><div class="amber-status-text">این سایت باید در دسترس باشد</div><div class="amber-cache-text"> {{NAME}} یک نسخه ذخیره از {{DATE}} دارد</div>' +
      '<a class="amber-focus amber-cache-link" href="{{CACHE}}">دیدن نسخه ذخیره</a><div class="amber-iframe-container"><a href="{{LINK}}"></a><iframe sandbox="" src="{{LINK}}"/></div><a class="amber-original-link" href="{{LINK}}">دیدن لینک زنده</a></div><a class="amber-info" href="http://amberlink.org" target="_blank">i</a></div>',
      interstitial_html_down :
      '<div class="amber-interstitial"><a href="#" class="amber-close"></a><div class="amber-body"><div class="amber-status-text">این وب سایت ممکن است در دسترس نباشد</div><div class="amber-cache-text"> {{NAME}} یک نسخه ذخیره از {{DATE}} دارد</div>' +
      '<a class="amber-focus amber-cache-link" href="{{CACHE}}">دیدن نسخه ذخیره</a><div class="amber-iframe-container"><a href="{{LINK}}"></a><iframe sandbox="" src="{{LINK}}"/></div><a class="amber-original-link" href="{{LINK}}">دیدن لینک زنده</a></div><a class="amber-info" href="http://amberlink.org" target="_blank">i</a></div>',
      hover_html_up   : '<div class="amber-hover amber-up"><a class="amber-info" href="http://amberlink.org" target="_blank">i</a><div class="amber-text"><div class="amber-status-text">این سایت باید در دسترس باشد</div><div class="amber-cache-text"> {{NAME}} یک نسخه ذخیره از {{DATE}} دارد</div></div><div class="amber-links"><a class="amber-cache-link" href="{{CACHE}}">دیدن نسخه ذخیره</a><a href="{{LINK}}" class="amber-focus">دیدن لینک زنده</a></div><div class="amber-arrow"></div></div>',
      hover_html_down : '<div class="amber-hover amber-down"><a class="amber-info" href="http://amberlink.org" target="_blank">i</a><div class="amber-text"><div class="amber-status-text">این وب سایت ممکن است در دسترس نباشد</div><div class="amber-cache-text"> {{NAME}} یک نسخه ذخیره از {{DATE}} دارد</div></div><div class="amber-links"><a class="amber-cache-link amber-focus" href="{{CACHE}}">دیدن نسخه ذخیره</a><a href="{{LINK}}">دیدن لینک زنده</a></div><div class="amber-arrow"></div></div>',
      this_site: "این وب سایت"
      }
    },

  set_locale : function(locale) {
    amber.locale = locale;
    amber.rtl = (locale == 'fa');
  },

  country_specific_behavior_exists : function() {
    return (document.querySelectorAll("a[data-versionurl][data-amber-behavior*=\\,]").length > 0);
  },

  callback : function(json) {
    try {
      amber.country = json.country_code;
      if (amber.country) {
        localStorage.setItem('country',amber.country);
      }
    } catch (e) { /* Not supported */ }
  },

  get_country : function() {
    try {
      if (!amber.country) {
        amber.country = localStorage.getItem('country');
      }
    } catch (e) { /* Not supported */ }
    if (!amber.country) {
      var script = document.createElement('script');
      script.src = '//freegeoip.net/json/?callback=amber.callback';
      document.getElementsByTagName('head')[0].appendChild(script);
    }
  },

  get_text : function(key) {
    return amber.translations[amber.locale][key];
  },

  replace_args : function (s, args) {
    for (var key in args) {
      s = s.replace(new RegExp(key,"g"), args[key]);
    }
    return s;
  },

  parse_country_behavior : function(s) {
    var result = {};
    var x = s.split(" ");
    if (x.length != 2) {
      return false;
    } else {
      result.status = x[0];
      y = x[1].split(":");
      switch (y.length) {
        case 1:
          result.action = y[0];
          break;
        case 2:
          result.action = y[0];
          result.delay = y[1];
          break;
      }
    }
    return result;
  },

  parse_behavior : function(s) {
    var result = {};
    /* Split by country */
    var countries = s.split(",");
    result.default = amber.parse_country_behavior(countries[0]);
    if (countries.length > 1) {
      for (i = 1; i < countries.length; i++) {
        var x = countries[i].split(' ');
        var c = x.shift();
        if (x.length == 2) {
          result[c.toUpperCase()] = amber.parse_country_behavior(x.join(' '));
        }
      }
    }
    return result;
  },

  parse_cache : function(cache_url, cache_date) {
    var result = {};
    result.default = {};
    result.default.cache = cache_url;
    result.default.date = cache_date;
    return result;
  },

  format_date_from_string : function(s) {
      var a = s.split(/[^0-9]/);
      return new Date (a[0],a[1]-1,a[2],a[3],a[4],a[5]).toLocaleDateString();
  },

  execute_action: function (behavior, action) {
    if (!amber.country && behavior.default.action == action) {
      return true;
    }
    if (amber.country && !(amber.country in behavior) && (behavior.default.action == action)) {
      return true;
    }
    if (amber.country && behavior[amber.country] && (behavior[amber.country].action == action)) {
      return true;
    }
    return false;
  },

  show_cache : function(e) {
    var behavior = amber.parse_behavior(this.getAttribute("data-amber-behavior"));
    var cache = amber.parse_cache(this.getAttribute("data-versionurl"), this.getAttribute("data-versiondate"));
    if (amber.execute_action(behavior,"cache") && cache.default) {
      window.location.href = cache.default.cache;
      e.preventDefault();
    }
  },

  show_interstitial : function (e) {
    var behavior = amber.parse_behavior(this.getAttribute("data-amber-behavior"));
    var cache = amber.parse_cache(this.getAttribute("data-versionurl"), this.getAttribute("data-versiondate"));

    if (amber.execute_action(behavior,"popup") && cache.default) {

      /* Add the window to the DOM */
      var element = document.createElement('div');
      element.className = "amber-overlay";
      document.body.appendChild(element);

      /* Substitute dynamic text */
      var replacements = {
        '{{DATE}}' : amber.format_date_from_string(cache.default.date),
        '{{NAME}}' : (amber.name == undefined) ? amber.get_text('this_site') : amber.name,
        '{{CACHE}}' : cache.default.cache,
        '{{LINK}}' : this.getAttribute("href")
      }

      var amberElement = document.createElement('div');
      amberElement.innerHTML = amber.replace_args(behavior.default.status == "up" ? amber.get_text('interstitial_html_up') : amber.get_text('interstitial_html_down'), replacements);
      document.body.appendChild(amberElement.firstChild);

      /* Center the window */
      var w = window;
      var d = document;
      var el = d.documentElement;
      var g = d.getElementsByTagName('body')[0];
      var windowWidth = w.innerWidth || el.clientWidth || g.clientWidth;
      var windowHeight = w.innerHeight|| el.clientHeight|| g.clientHeight;
      var interstitial = document.querySelectorAll(".amber-interstitial")[0];

      var left = windowWidth/2 - interstitial.offsetWidth/2;
      var top =  windowHeight/2 - interstitial.offsetHeight/2;
      interstitial.style.left = left + "px";
      interstitial.style.top = top + "px";

      /* Clicking on the overlay or close button closes the window */
      var closeEls = document.querySelectorAll(".amber-overlay, .amber-close");
      for (var i = 0; i < closeEls.length; i++) {
        amber.util_addEventListener(closeEls[i],'click',function(e) {
          var els = document.querySelectorAll(".amber-overlay, .amber-interstitial");
          for (var i = 0; i < els.length; i++) {
              els[i].parentNode.removeChild(els[i]);
          }
          e.preventDefault();
        });
      }
      e.preventDefault();
      amber.attach_cache_view_event();

      /* Start looking for mementos */
      amber.get_memento(this.getAttribute('href'), this.getAttribute('data-versiondate'),
        function(response) {
          if (response['url']) {
            var cachelink = document.querySelectorAll(".amber-interstitial .amber-memento-link")[0];
            var linktext;
            cachelink.setAttribute('href', response['url']);
            if (response['date']) {
              linktext = amber.replace_args(
                amber.get_text("timegate_with_date"), 
                {'{{MEMENTO_DATE}}' : amber.format_date_from_string(response['date'])});
            } else {
              linktext = amber.get_text("timegate_without_date");
            }
            cachelink.innerHTML = amber.replace_args(cachelink.innerHTML, {
                '{{MEMENTO_MESSAGE}}' : linktext,
              });

            cachelink.className = cachelink.className + " found";
          }
        });
    }
  },

  start_popup_hover : function (e) {
    amber.hovering_on_popup = true;
  },

  end_popup_hover_function : function (hover) {
    // Need to make sure that we're not hovering over one of the child elements.
    // Return a function that captures the original node's descendants
    var descendants = hover.querySelectorAll('*');
    return function (e) {
      var el = e.toElement || e.relatedTarget;
      for (var i = 0; i < descendants.length; i++) {
        if (el == descendants[i]) {
          return;
        }
      }
      amber.hovering_on_popup = false;
      var hover = document.querySelectorAll(".amber-hover")[0];
      hover.parentNode.removeChild(hover);
    }
  },

  calculate_hover_position : function (target, status) {
    var edge_buffer = 15;
    var offset = amber.util_offset(target);
    var result = {"left" : offset.left - 30, "top" : offset.top - 100}
    if (amber.rtl) {
      var hover = document.querySelectorAll(".amber-hover")[0];
      result.left = result.left + target.offsetWidth - hover.offsetWidth;
    }
    if (result.left < edge_buffer) {
      result.left = edge_buffer;
    }
    if (result.top < edge_buffer) {
      result.top = edge_buffer;
    }
    return result;
  },

  start_link_hover : function (e) {
    amber.hovering_on_link = true;    
    var behavior = amber.parse_behavior(this.getAttribute("data-amber-behavior"));
    if (amber.execute_action(behavior,"hover") && !amber.hover_up()) {
      var cache = amber.parse_cache(this.getAttribute("data-versionurl"), this.getAttribute("data-versiondate"));
      var args = {
        '{{DATE}}' : amber.format_date_from_string(cache.default.date),
        '{{NAME}}' : (amber.name == undefined) ? amber.get_text('this_site') : amber.name,
        '{{CACHE}}' : cache.default.cache,
        '{{LINK}}' : this.getAttribute("href")
      };
      t = this;
      var delay = behavior[amber.country] ? behavior[amber.country].delay : behavior.default.delay;
      var timer = setTimeout(function() {

        var amberElement = document.createElement('div');
        amberElement.innerHTML = amber.replace_args(behavior.default.status == "up" ? amber.get_text('hover_html_up') : amber.get_text('hover_html_down'), args);
        document.body.appendChild(amberElement.firstChild);

        /* Position the hover text */
        var hover = document.querySelectorAll(".amber-hover")[0];
        var pos = amber.calculate_hover_position(t, behavior.default.status);
        hover.style.left = pos.left + "px";
        hover.style.top = pos.top + "px";
        amber.util_addEventListener(hover, 'mouseover', amber.start_popup_hover);
        amber.util_addEventListener(hover, 'mouseout', amber.end_popup_hover_function(hover));
  
        amber.attach_cache_view_event();

        amber.get_memento(t.getAttribute('href'), t.getAttribute('data-versiondate'),
          function(response) {
            if (response['url']) {
              /* Set URL */
              var cachelink = document.querySelectorAll(".amber-hover .amber-memento-link")[0];
              if (cachelink == undefined) {
                return; /* The hover may have gone away */
              }
              var linktext;
              cachelink.setAttribute('href', response['url']);
              if (response['date']) {
                linktext = amber.replace_args(
                  amber.get_text("timegate_with_date"), 
                  {'{{MEMENTO_DATE}}' : amber.format_date_from_string(response['date'])});
              } else {
                linktext = amber.get_text("timegate_without_date");
              }
              cachelink.innerHTML = amber.replace_args(cachelink.innerHTML, {
                  '{{MEMENTO_MESSAGE}}' : linktext,
                });
              /* Update hover div */
              var hover = document.querySelectorAll(".amber-hover")[0];
              hover.className = hover.className + " memento-found";
              hover.style.top = (pos.top - 20) + "px";
            }
          });

      }, delay * 1000);
      this.setAttribute("amber-timer",timer);
    }
  },

  end_link_hover : function (e) {
    amber.hovering_on_link = false;
    var behavior = amber.parse_behavior(this.getAttribute("data-amber-behavior"));
    if (amber.execute_action(behavior,"hover")) {
      clearTimeout(this.getAttribute("amber-timer"));

      /* Give them some time, and then check if they've moved over the popup before closing popup.
         Add some special handling if the hover display is 0, to avoid wierdness */
      var delay = behavior[amber.country] ? behavior[amber.country].delay : behavior.default.delay;
      setTimeout(function() {
        if (!amber.hovering_on_popup && !amber.hovering_on_link) {
          amber.clear_hover();
        }
      }, 1000);
    }
  },

  attach_cache_view_event : function() {
    /* Clicking on the cache link will log an event */
    amber.util_forEachElement(".amber-cache-link", function(el, i) {
      var href = encodeURIComponent(el.getAttribute("href"));
      amber.util_addEventListener(el, 'click', function(e) {
        var request = new XMLHttpRequest();
        request.onload = function() {
          if (request.readyState === 4) {
            window.location = decodeURIComponent(href);
          }
        };
        // Send synchronous notification, to ensure it's sent completely before the page unloads
        // This would be a good place to use navigator.sendBeacon(), once it has more support
        request.open('GET', '/amber/logcacheview?cache=' + href + '&t=' + new Date().getTime(), false);
        request.send();
      });
    });    
  },

  hover_up : function(e) {
    var hover = document.querySelectorAll(".amber-hover")[0];
    return (typeof hover != typeof undefined);
  },

  clear_hover : function (e) {
    var hover = document.querySelectorAll(".amber-hover")[0];
    if (typeof hover != typeof undefined)
      hover.parentNode.removeChild(hover);
  },

  /* Add event listeners for amber-annotated links */
  update_link_event_listeners : function() {
    /* First, clear any pre-existing listeners */
    amber.util_forEachElement("a[data-versionurl]", function(e, i) {
      amber.util_clearEventListener(e, 'click', amber.show_cache);
      amber.util_clearEventListener(e, 'click', amber.show_interstitial);
      amber.util_clearEventListener(e, 'mouseover', amber.start_link_hover);
      amber.util_clearEventListener(e, 'mouseout', amber.end_link_hover);
      amber.util_clearEventListener(e, 'click', amber.clear_hover);
    });

    /* Now add new listeners, based on the behavior desired */
    amber.util_forEachElement("a[data-versionurl][data-amber-behavior*=cache]", function(e, i) {
      amber.util_addEventListener(e, 'click', amber.show_cache);
    });
    amber.util_forEachElement("a[data-versionurl][data-amber-behavior*=popup]", function(e, i) {
      amber.util_addEventListener(e, 'click', amber.show_interstitial);
    });
    amber.util_forEachElement("a[data-versionurl][data-amber-behavior*=hover]", function(e, i) {
      amber.util_addEventListener(e, 'mouseover', amber.start_link_hover);
      amber.util_addEventListener(e, 'mouseout', amber.end_link_hover);
      amber.util_addEventListener(e, 'click', amber.clear_hover);
    });
  },

  /* Update data-* attributes based on updated availability information */
  update_availability : function(availability) {
    var data = availability.data;
    if (data) {
      for (var i = 0; i < data.length; i++) {
        amber.util_forEachElement("a[href='" + data[i].url + "']", function(e, index) {
          e.setAttribute('data-amber-behavior', data[i].behavior);
        });
      };
      amber.update_link_event_listeners();
    }
  },

  /* Call the server to see if there's updated availability information for cached URLs */
  get_availability : function() {
    var request, params = "", urls = [];
    amber.util_forEachElement("a[data-versionurl]", function(e, i) {
      params && (params += "&");
      params += "url[]=" + encodeURIComponent(e.href);
    });

    if (params && (amber.country != undefined)) {
      params += "&country=" + amber.country;

      request = new XMLHttpRequest();
      request.open('POST', "/amber/status");
      request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
      request.onload = function() {
        if (request.readyState === 4) {
          amber.update_availability(JSON.parse(request.responseText));
        }
      };
      request.send(params);
    } 
  },

  /* Get memento URL for a given URL and date, and execute a function on the result */
  get_memento : function(href, date, callback) {
    if (!amber.memento_enabled) {
      return;
    }
    var request = new XMLHttpRequest();
    request.onload = function() {
      if ((request.readyState === 4) && (request.status === 200)) {
        callback(JSON.parse(request.responseText));
      }
    };
    request.open('GET', '/amber/memento?date=' + date + '&url=' + href);
    request.send();
  },

  /* Utility functions to provide support for IE8+ */
  util_addEventListener : function (el, eventName, handler) {
    if (el.addEventListener) {
      el.addEventListener(eventName, handler);
    } else {
      el.attachEvent('on' + eventName, function(){
        handler.call(el);
      });
    }
  },

  util_clearEventListener : function (el, eventName, handler) {
    if (el.removeEventListener) {
      el.removeEventListener(eventName, handler);
    } else {
      // el.detachEvent('on' + eventName);
      // TODO: Clear the event for IE7-9. See http://ejohn.org/blog/flexible-javascript-events/
    }
  },

  util_forEachElement : function (selector, fn) {
    var elements = document.querySelectorAll(selector);
    for (var i = 0; i < elements.length; i++)
       fn(elements[i], i);
  },

  util_ready : function (fn) {
    if (document.addEventListener) {
      document.addEventListener('DOMContentLoaded', fn);
    } else {
      document.attachEvent('onreadystatechange', function() {
        if (document.readyState === 'interactive')
          fn();
      });
    }
  },

  util_offset : function(elem) {
      var box = { top: 0, left: 0 };
      var doc = elem && elem.ownerDocument;
      var docElem = doc.documentElement;
      if (typeof elem.getBoundingClientRect !== typeof undefined ) {
          box = elem.getBoundingClientRect();
      }
      var win = (doc != null && doc=== doc.window) ? doc: doc.nodeType === 9 && doc.defaultView;
      return {
          top: box.top + win.pageYOffset - docElem.clientTop,
          left: box.left + win.pageXOffset - docElem.clientLeft
      };
  }

};

amber.util_ready(function($) {

    amber.update_link_event_listeners();
    amber.util_addEventListener(window, 'unload', amber.clear_hover);
    amber.get_country();

    /* Drupal-specific configuration */
    if ((typeof Drupal != 'undefined') && (typeof Drupal.settings.amber != 'undefined')) {
      amber.name = Drupal.settings.amber.name;
      amber.lookup_availability = Drupal.settings.amber.lookup_availability;
      amber.memento_enabled = true;
    }

    /* Wordpress-specific configuration */
    if (typeof amber_config != 'undefined') {
      amber.name = amber_config.site_name;
      amber.lookup_availability = amber_config.lookup_availability;
      amber.memento_enabled = true;
    }

    /* Set the locale, based on global variable */
    if (typeof amber_locale != 'undefined') {
      amber.set_locale(amber_locale);
    }

    /* Get availability information from NetClerk */
    if (amber.lookup_availability != 'undefined' && amber.lookup_availability) {
      amber.get_availability();
    }

});
