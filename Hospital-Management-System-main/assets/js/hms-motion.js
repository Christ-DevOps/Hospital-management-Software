/**
 * HMS Motion — shared by dashboards + public landing
 * Counters, scroll-reveal, hero stagger, mock UI helpers
 * Uses IntersectionObserver only (WOW.js not required on landing)
 * Respects prefers-reduced-motion. Trigger-once per element.
 */
(function (window, document) {
  'use strict';

  var HMS = window.HMS || {};
  var started = false;

  function prefersReduced() {
    return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  }

  function animateCounter(el, target, duration, decimals) {
    duration = duration || 1200;
    decimals = decimals || 0;
    if (prefersReduced()) {
      el.textContent = decimals ? target.toFixed(decimals) : String(Math.round(target));
      return;
    }
    var start = performance.now();
    function frame(now) {
      var t = Math.min((now - start) / duration, 1);
      var eased = 1 - Math.pow(1 - t, 3);
      var value = target * eased;
      el.textContent = decimals ? value.toFixed(decimals) : String(Math.floor(value));
      if (t < 1) {
        requestAnimationFrame(frame);
      } else {
        el.textContent = decimals ? target.toFixed(decimals) : String(Math.round(target));
      }
    }
    requestAnimationFrame(frame);
  }

  function initCounters() {
    var nodes = document.querySelectorAll('[data-plugin="counterup"], [data-hms-counter]');
    if (!nodes.length) return;

    var run = function (el) {
      if (el.getAttribute('data-hms-counted') === '1') return;
      var raw = (el.getAttribute('data-hms-counter') || el.textContent || '').replace(/,/g, '').trim();
      var decimalsAttr = el.getAttribute('data-hms-decimals');
      var decimals = decimalsAttr ? parseInt(decimalsAttr, 10) : (raw.indexOf('.') >= 0 ? 1 : 0);
      var target = parseFloat(raw);
      if (isNaN(target)) return;
      el.setAttribute('data-hms-counted', '1');
      el.textContent = decimals ? (0).toFixed(decimals) : '0';
      animateCounter(el, target, 1200, decimals);
    };

    if (!('IntersectionObserver' in window)) {
      Array.prototype.forEach.call(nodes, run);
      return;
    }

    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          run(entry.target);
          io.unobserve(entry.target);
        }
      });
    }, { threshold: 0.35 });

    Array.prototype.forEach.call(nodes, function (el) { io.observe(el); });
  }

  function initHeroStagger() {
    var hero = document.querySelector('.hms-hero');
    if (!hero) return;

    if (prefersReduced()) {
      hero.classList.add('is-ready');
      var items = hero.querySelectorAll('[data-hms-stagger]');
      Array.prototype.forEach.call(items, function (el) {
        el.style.opacity = '1';
        el.style.transform = 'none';
        el.style.animation = 'none';
      });
      return;
    }

    // Next frame so CSS initial state paints first
    requestAnimationFrame(function () {
      hero.classList.add('is-ready');
    });
  }

  function initBarCharts() {
    var bars = document.querySelectorAll('.hms-bar-chart .bar[data-height]');
    Array.prototype.forEach.call(bars, function (bar, i) {
      var h = bar.getAttribute('data-height');
      if (!h) return;
      if (prefersReduced()) {
        bar.style.height = h;
        bar.style.animation = 'none';
        return;
      }
      bar.style.height = h;
      bar.style.animationDelay = (i * 40) + 'ms';
    });
  }

  function initDeptBars() {
    var fills = document.querySelectorAll('.hms-dept-bar > span[data-width]');
    Array.prototype.forEach.call(fills, function (el) {
      var w = el.getAttribute('data-width');
      if (!w) return;
      el.style.width = '0%';
      requestAnimationFrame(function () {
        el.style.width = w;
        if (prefersReduced()) el.style.transition = 'none';
      });
    });
  }

  function initReveal() {
    var nodes = document.querySelectorAll('.hms-reveal');
    if (!nodes.length) return;

    if (prefersReduced()) {
      Array.prototype.forEach.call(nodes, function (el) {
        el.classList.add('is-visible');
      });
      return;
    }

    if (!('IntersectionObserver' in window)) {
      Array.prototype.forEach.call(nodes, function (el) { el.classList.add('is-visible'); });
      return;
    }

    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          io.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

    Array.prototype.forEach.call(nodes, function (el) { io.observe(el); });
  }

  function clearSkeletons() {
    var loading = document.querySelectorAll('.hms-skeleton-loading');
    Array.prototype.forEach.call(loading, function (el) {
      el.classList.remove('hms-skeleton-loading');
    });
  }

  function bindAjaxSkeleton() {
    if (typeof window.jQuery === 'undefined') return;
    var $ = window.jQuery;
    $(document).on('preXhr.dt', function (e) {
      var wrap = e.target && e.target.closest ? e.target.closest('.card-box, .card, .dataTables_wrapper') : null;
      if (wrap) wrap.classList.add('hms-skeleton-loading');
    });
    $(document).on('draw.dt xhr.dt', function (e) {
      var wrap = e.target && e.target.closest ? e.target.closest('.card-box, .card, .dataTables_wrapper') : null;
      if (wrap) wrap.classList.remove('hms-skeleton-loading');
    });
  }

  function markActiveNav() {
    var path = window.location.pathname.split('/').pop() || '';
    if (!path) return;
    var links = document.querySelectorAll('#sidebar-menu a[href]');
    Array.prototype.forEach.call(links, function (a) {
      var href = a.getAttribute('href') || '';
      if (href === path || href.indexOf(path) !== -1) {
        a.classList.add('active');
        var li = a.closest('li');
        if (li) {
          var parent = li.parentElement && li.parentElement.closest('li');
          if (parent) parent.classList.add('mm-active');
        }
      }
    });
  }

  function initHeaderScroll() {
    var header = document.getElementById('header');
    if (!header) return;
    var onScroll = function () {
      if (window.scrollY > 12) {
        header.classList.add('is-scrolled');
        header.classList.add('header-scrolled');
      } else {
        header.classList.remove('is-scrolled');
      }
    };
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
  }

  function initAuthCard() {
    var cards = document.querySelectorAll('[data-hms-auth-card]');
    if (!cards.length) return;

    Array.prototype.forEach.call(cards, function (card) {
      if (prefersReduced()) {
        card.classList.add('is-visible');
        return;
      }
      requestAnimationFrame(function () {
        card.classList.add('is-visible');
      });
    });
  }

  function initPasswordToggles() {
    var toggles = document.querySelectorAll('[data-hms-pass-toggle]');
    Array.prototype.forEach.call(toggles, function (btn) {
      btn.addEventListener('click', function () {
        var wrap = btn.closest('.hms-auth-pass');
        var input = wrap ? wrap.querySelector('input') : null;
        var icon = btn.querySelector('i');
        if (!input) return;
        var show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
        if (icon) {
          icon.className = show ? 'mdi mdi-eye-off-outline' : 'mdi mdi-eye-outline';
        }
      });
    });
  }

  HMS.init = function () {
    if (started) return;
    started = true;
    initHeroStagger();
    initCounters();
    initBarCharts();
    initDeptBars();
    initReveal();
    clearSkeletons();
    bindAjaxSkeleton();
    markActiveNav();
    initHeaderScroll();
    initAuthCard();
    initPasswordToggles();
  };

  HMS.animateCounter = animateCounter;
  HMS.showSkeleton = function (el) { if (el) el.classList.add('hms-skeleton-loading'); };
  HMS.hideSkeleton = function (el) { if (el) el.classList.remove('hms-skeleton-loading'); };

  window.HMS = HMS;

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', HMS.init);
  } else {
    HMS.init();
  }
})(window, document);
