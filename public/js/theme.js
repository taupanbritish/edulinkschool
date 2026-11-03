(function () {
  'use strict';

  var root = document.documentElement;
  var MODE_KEY = 'sekolah-tema-mode';
  var ACCENT_KEY = 'sekolah-tema-aksen';
  var CUSTOM_KEY = 'sekolah-tema-kustom';
  var DEFAULT_CUSTOM = '#17496e';
  var ACCENTS = ['biru', 'botanical', 'karamel', 'ungu', 'toska', 'custom'];
  var CUSTOM_PROPS = [
    '--navy-950', '--navy-900', '--navy-800', '--navy-700', '--navy-600',
    '--teal-600', '--teal-500', '--amber-500', '--amber-600', '--amber-400',
    '--focus-ring', '--focus-ring-color', '--accent-bright', '--teal-text'
  ];
  var media = window.matchMedia ? window.matchMedia('(prefers-color-scheme: dark)') : null;

  function read(key, fallback) {
    try {
      return window.localStorage.getItem(key) || fallback;
    } catch (error) {
      return fallback;
    }
  }

  function write(key, value) {
    try {
      window.localStorage.setItem(key, value);
    } catch (error) {
      // Penyimpanan bisa diblokir mode privat; tema tetap berlaku selama sesi.
    }
  }

  function isHex(value) {
    return typeof value === 'string' && /^#[0-9a-f]{6}$/i.test(value);
  }

  function hexToHsl(hex) {
    var value = hex.replace('#', '');
    var r = parseInt(value.slice(0, 2), 16) / 255;
    var g = parseInt(value.slice(2, 4), 16) / 255;
    var b = parseInt(value.slice(4, 6), 16) / 255;
    var max = Math.max(r, g, b);
    var min = Math.min(r, g, b);
    var lightness = (max + min) / 2;
    var hue = 0;
    var saturation = 0;

    if (max !== min) {
      var delta = max - min;
      saturation = lightness > 0.5 ? delta / (2 - max - min) : delta / (max + min);

      if (max === r) {
        hue = (g - b) / delta + (g < b ? 6 : 0);
      } else if (max === g) {
        hue = (b - r) / delta + 2;
      } else {
        hue = (r - g) / delta + 4;
      }

      hue *= 60;
    }

    return { h: hue, s: saturation * 100, l: lightness * 100 };
  }

  function hsl(hue, saturation, lightness) {
    return 'hsl(' + (Math.round(hue * 10) / 10) + ', ' + (Math.round(saturation * 10) / 10) + '%, ' + lightness + '%)';
  }

  function clearCustom() {
    CUSTOM_PROPS.forEach(function (property) {
      root.style.removeProperty(property);
    });
  }

  function applyCustom(hex) {
    if (!isHex(hex)) {
      return;
    }

    var color = hexToHsl(hex);
    var hue = color.h;
    var saturation = Math.min(85, Math.max(38, color.s));

    clearCustom();
    root.style.setProperty('--navy-950', hsl(hue, saturation, 8));
    root.style.setProperty('--navy-900', hsl(hue, saturation, 12));
    root.style.setProperty('--navy-800', hsl(hue, saturation, 30));
    root.style.setProperty('--navy-700', hsl(hue, saturation, 38));
    root.style.setProperty('--navy-600', hsl(hue, saturation, 46));
    root.style.setProperty('--teal-600', hsl(hue + 22, saturation, 38));
    root.style.setProperty('--teal-500', hsl(hue + 22, saturation, 46));
    root.style.setProperty('--amber-500', hsl(hue + 180, 70, 55));
    root.style.setProperty('--amber-600', hsl(hue + 180, 70, 47));
    root.style.setProperty('--amber-400', hsl(hue + 180, 72, 63));
    root.style.setProperty('--focus-ring', 'hsla(' + hue.toFixed(1) + ', ' + Math.round(saturation) + '%, 40%, .22)');
    root.style.setProperty('--focus-ring-color', hsl(hue, saturation, 42));
    root.style.setProperty('--accent-bright', hsl(hue, Math.min(80, saturation + 12), 72));
    root.style.setProperty('--teal-text', hsl(hue + 22, Math.min(80, saturation + 12), 74));
  }

  function resolveMode(mode) {
    if (mode === 'system') {
      return media && media.matches ? 'dark' : 'light';
    }

    return mode === 'dark' ? 'dark' : 'light';
  }

  function syncSwatches(name) {
    Array.prototype.forEach.call(document.querySelectorAll('[data-accent-choice]'), function (button) {
      var active = button.getAttribute('data-accent-choice') === name;
      button.classList.toggle('is-active', active);
      button.setAttribute('aria-pressed', active ? 'true' : 'false');
    });
  }

  function syncModes(mode) {
    Array.prototype.forEach.call(document.querySelectorAll('[data-mode-choice]'), function (button) {
      var active = button.getAttribute('data-mode-choice') === mode;
      button.classList.toggle('is-active', active);
      button.setAttribute('aria-pressed', active ? 'true' : 'false');
    });
  }

  function setAccent(name, hex) {
    var accent = ACCENTS.indexOf(name) === -1 ? 'biru' : name;

    clearCustom();
    root.setAttribute('data-accent', accent);
    write(ACCENT_KEY, accent);

    if (accent === 'custom') {
      var color = isHex(hex) ? hex : read(CUSTOM_KEY, DEFAULT_CUSTOM);
      if (isHex(color)) {
        write(CUSTOM_KEY, color);
        applyCustom(color);
      }
    }

    syncSwatches(accent);
  }

  function setMode(mode) {
    var next = ['light', 'dark', 'system'].indexOf(mode) === -1 ? 'system' : mode;

    root.setAttribute('data-theme-mode', next);
    root.setAttribute('data-theme', resolveMode(next));
    write(MODE_KEY, next);
    syncModes(next);
  }

  var initialMode = read(MODE_KEY, 'system');
  initialMode = ['light', 'dark', 'system'].indexOf(initialMode) === -1 ? 'system' : initialMode;
  root.setAttribute('data-theme-mode', initialMode);
  root.setAttribute('data-theme', resolveMode(initialMode));

  var initialAccent = read(ACCENT_KEY, 'biru');
  initialAccent = ACCENTS.indexOf(initialAccent) === -1 ? 'biru' : initialAccent;
  root.setAttribute('data-accent', initialAccent);

  if (initialAccent === 'custom') {
    var savedColor = read(CUSTOM_KEY, DEFAULT_CUSTOM);
    if (isHex(savedColor)) {
      applyCustom(savedColor);
    }
  }

  if (media) {
    var onSchemeChange = function () {
      if (root.getAttribute('data-theme-mode') === 'system') {
        root.setAttribute('data-theme', resolveMode('system'));
      }
    };

    if (media.addEventListener) {
      media.addEventListener('change', onSchemeChange);
    } else if (media.addListener) {
      media.addListener(onSchemeChange);
    }
  }

  function initMobileNav() {
    var toggle = document.querySelector('.nav-toggle');
    var nav = document.getElementById('siteNav');
    if (!toggle || !nav) {
      return;
    }

    function isMobile() {
      return window.matchMedia && window.matchMedia('(max-width: 860px)').matches;
    }

    function fit() {
      if (!nav.classList.contains('open') || !isMobile()) {
        nav.style.maxHeight = '';
        return;
      }
      var top = nav.getBoundingClientRect().top;
      nav.style.maxHeight = Math.max(180, window.innerHeight - top - 12) + 'px';
    }

    function setOpen(open) {
      nav.classList.toggle('open', open);
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      fit();
    }

    toggle.addEventListener('click', function () {
      setOpen(!nav.classList.contains('open'));
    });

    nav.addEventListener('click', function (event) {
      if (event.target.closest('a')) {
        setOpen(false);
      }
    });

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape') {
        setOpen(false);
      }
    });

    window.addEventListener('resize', fit);
    window.addEventListener('scroll', fit, { passive: true });
  }

  function init() {
    initMobileNav();

    var picker = document.querySelector('[data-theme-picker]');
    if (!picker) {
      return;
    }

    var toggle = picker.querySelector('[data-theme-toggle]');
    var panel = picker.querySelector('[data-theme-panel]');
    var colorInput = picker.querySelector('[data-theme-color]');

    function setOpen(open) {
      panel.hidden = !open;
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      picker.classList.toggle('is-open', open);
    }

    toggle.addEventListener('click', function (event) {
      event.stopPropagation();
      setOpen(panel.hidden);
    });

    document.addEventListener('click', function (event) {
      if (!picker.contains(event.target)) {
        setOpen(false);
      }
    });

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape') {
        setOpen(false);
      }
    });

    Array.prototype.forEach.call(picker.querySelectorAll('[data-accent-choice]'), function (button) {
      button.addEventListener('click', function () {
        setAccent(button.getAttribute('data-accent-choice'), colorInput ? colorInput.value : null);
      });
    });

    if (colorInput) {
      var saved = read(CUSTOM_KEY, DEFAULT_CUSTOM);
      if (isHex(saved)) {
        colorInput.value = saved;
      }

      colorInput.addEventListener('input', function () {
        setAccent('custom', colorInput.value);
      });
    }

    Array.prototype.forEach.call(picker.querySelectorAll('[data-mode-choice]'), function (button) {
      button.addEventListener('click', function () {
        setMode(button.getAttribute('data-mode-choice'));
      });
    });

    setOpen(false);
    syncSwatches(root.getAttribute('data-accent') || 'biru');
    syncModes(root.getAttribute('data-theme-mode') || 'system');
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
