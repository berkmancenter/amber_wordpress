var amber = {

  hovering_on_popup : false,
  locale : 'en',
  country : '',
  rtl : false,
  translations : {
    en : {
      interstitial_html_up :
      '<div class="amber-interstitial amber-up"><a href="#" class="amber-close"></a><div class="amber-body"><div class="amber-status-text">This page should be available</div><div class="amber-cache-text">{{NAME}} has a cache from {{DATE}}</div>' +
      '<a class="amber-focus" href="{{CACHE}}">View the cache</a><div class="amber-iframe-container"><iframe src="{{LINK}}"/></div><a class="amber-original-link" href="{{LINK}}">Continue to the page</a></div><a class="amber-info" href="http://brk.mn/robustness" target="_blank">i</a></div>',
      interstitial_html_down :
      '<div class="amber-interstitial amber-down"><a href="#" class="amber-close"></a><div class="amber-body"><div class="amber-status-text">This page may not be available</div><div class="amber-cache-text">{{NAME}} has a cache from {{DATE}}</div>' +
      '<a class="amber-focus" href="{{CACHE}}">View the cache</a><div class="amber-iframe-container"><iframe src="{{LINK}}"/></div><a class="amber-original-link" href="{{LINK}}">Continue to the page</a></div><a class="amber-info" href="http://brk.mn/robustness" target="_blank">i</a></div>',
      hover_html_up   : '<div class="amber-hover amber-up"><a class="amber-info" href="http://brk.mn/robustness" target="_blank">i</a><div class="amber-text"><div class="amber-status-text">This page should be available</div><div class="amber-cache-text">{{NAME}} has a cache from {{DATE}}</div></div><div class="amber-links"><a href="{{CACHE}}">View the cache</a><a href="{{LINK}}" class="amber-focus">Continue to the page</a></div><div class="amber-arrow"></div></div>',
      hover_html_down : '<div class="amber-hover amber-down"><a class="amber-info" href="http://brk.mn/robustness" target="_blank">i</a><div class="amber-text"><div class="amber-status-text">This page may not be available</div><div class="amber-cache-text">{{NAME}} has a cache from {{DATE}}</div></div><div class="amber-links"><a href="{{CACHE}}" class="amber-focus">View the cache</a><a href="{{LINK}}">Continue to the page</a></div><div class="amber-arrow"></div></div>',
      this_site: "This site"     
    },
    fa : {    
        interstitial_html_up :
        '<div class="amber-interstitial"><a href="#" class="amber-close"></a><div class="amber-body"><div class="amber-status-text">این سایت ممکن است در دسترس</div><div class="amber-cache-text">{{NAME}} یک کش از {{DATE}}</div>' +
        '<a class="amber-focus" href="{{CACHE}}">نمایش لینک های cache شده</a><div class="amber-iframe-container"><iframe src="{{LINK}}"/></div><a class="amber-original-link" href="{{LINK}}">نمایش لینک های فعال</a></div><a class="amber-info" href="http://brk.mn/robustness" target="_blank">i</a></div>',
        interstitial_html_down :
        '<div class="amber-interstitial"><a href="#" class="amber-close"></a><div class="amber-body"><div class="amber-status-text">این سایت ممکن است در دسترس</div><div class="amber-cache-text">{{NAME}} یک کش از {{DATE}}</div>' +
        '<a class="amber-focus" href="{{CACHE}}">نمایش لینک های cache شده</a><div class="amber-iframe-container"><iframe src="{{LINK}}"/></div><a class="amber-original-link" href="{{LINK}}">نمایش لینک های فعال</a></div><a class="amber-info" href="http://brk.mn/robustness" target="_blank">i</a></div>',
        hover_html_up   : '<div class="amber-hover amber-up"><a class="amber-info" href="http://brk.mn/robustness" target="_blank">i</a><div class="amber-text"><div class="amber-status-text">این سایت باید در دسترس باشد</div><div class="amber-cache-text">{{NAME}} یک کش از {{DATE}}</div></div><div class="amber-links"><a href="{{CACHE}}">دیدن لینک خرید پستی</a><a href="{{LINK}}" class="amber-focus">دیدن لینک زنده</a></div><div class="amber-arrow"></div></div>',
        hover_html_down : '<div class="amber-hover amber-down"><a class="amber-info" href="http://brk.mn/robustness" target="_blank">i</a><div class="amber-text"><div class="amber-status-text">این سایت ممکن است در دسترس</div><div class="amber-cache-text">{{NAME}} یک کش از {{DATE}}</div></div><div class="amber-links"><a href="{{CACHE}}" class="amber-focus">دیدن لینک خرید پستی</a><a href="{{LINK}}">دیدن لینک زنده</a></div><div class="amber-arrow"></div></div>',
        this_site: "این وب سایت"     
      }
    },

  set_locale : function(locale) {
    amber.locale = locale;
    amber.rtl = (locale == 'fa');
  },

  country_specific_behavior_exists : function() {
    return (document.querySelectorAll("a[data-cache][data-amber-behavior*=\\,]").length > 0);
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

  parse_cache_source : function(s) {
    var result = {};
    var x = s.split(" ");
    if (x.length != 2) {
      return false;
    } else {
      result.cache = x[0];
      result.date = x[1];
    }
    return result;
  },

  parse_cache : function(s) {
    var result = {};
    /* Split by cache source */
    var sources = s.split(",");
    result.default = amber.parse_cache_source(sources[0]);
    if (sources.length > 1) {
      for (i = 0; i < sources.length; i++) {
        // Logic for additional cache sources will go here
      }
    }
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
    if (amber.country && (behavior[amber.country].action == action)) {
      return true;
    }
    return false;
  },

  show_cache : function(e) {
    var behavior = amber.parse_behavior(this.getAttribute("data-amber-behavior"));
    var cache = amber.parse_cache(this.getAttribute("data-cache"));
    if (amber.execute_action(behavior,"cache") && cache.default) {
      window.location.href = cache.default.cache;
      e.preventDefault();
    }
  },

  show_interstitial : function (e) {
    var behavior = amber.parse_behavior(this.getAttribute("data-amber-behavior"));
    var cache = amber.parse_cache(this.getAttribute("data-cache"));

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
    var behavior = amber.parse_behavior(this.getAttribute("data-amber-behavior"));
    if (amber.execute_action(behavior,"hover")) {
      var cache = amber.parse_cache(this.getAttribute("data-cache"));
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
      }, delay * 1000);
      this.setAttribute("amber-timer",timer);
    }
  },

  end_link_hover : function (e) {
    var behavior = amber.parse_behavior(this.getAttribute("data-amber-behavior"));
    if (amber.execute_action(behavior,"hover")) {
      clearTimeout(this.getAttribute("amber-timer"));

      /* Give them some time, and then check if they've moved over the popup before closing popup.
         Add some special handling if the hover display is 0, to avoid wierdness */
      var delay = behavior[amber.country] ? behavior[amber.country].delay : behavior.default.delay;
      setTimeout(function() {
        if (!amber.hovering_on_popup) {
          amber.clear_hover();
        }
      }, Math.min(100,delay * 1000));
    }
  },

  clear_hover : function (e) {
    var hover = document.querySelectorAll(".amber-hover")[0];
    if (typeof hover != typeof undefined)
      hover.parentNode.removeChild(hover);
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

    amber.util_forEachElement("a[data-cache][data-amber-behavior*=cache]", function(e, i) {
      amber.util_addEventListener(e, 'click', amber.show_cache);
    });
    amber.util_forEachElement("a[data-cache][data-amber-behavior*=popup]", function(e, i) {
      amber.util_addEventListener(e, 'click', amber.show_interstitial);
    });
    amber.util_forEachElement("a[data-cache][data-amber-behavior*=hover]", function(e, i) {
      amber.util_addEventListener(e, 'mouseover', amber.start_link_hover);
      amber.util_addEventListener(e, 'mouseout', amber.end_link_hover);
      amber.util_addEventListener(e, 'click', amber.clear_hover);
    });

    if (amber.country_specific_behavior_exists()) {
      amber.get_country();
    }

    /* Drupal-specific code */
    if (typeof Drupal != 'undefined') {
      amber.name = Drupal.settings.amber.name;
    }

    /* Set the locale, based on global variable */
    if (typeof amber_locale != 'undefined') {
      amber.set_locale(amber_locale);
    }

});
