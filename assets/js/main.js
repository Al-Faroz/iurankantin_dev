/**
 * Main
 */

'use strict';

let menu,
  animate;

document.addEventListener('DOMContentLoaded', function () {
  if (navigator.userAgent.match(/iPhone|iPad|iPod/i)) {
    document.body.classList.add('ios');
  }
});

(function () {
  const MENU_STATE_KEY = 'iuran-kantin-menu-collapsed';
  const root = document.documentElement;

  function isDesktop() {
    return window.matchMedia('(min-width: 1200px)').matches;
  }

  function setDesktopCollapsed(collapsed, persist = true) {
    if (!isDesktop()) {
      return;
    }

    root.classList.remove('layout-menu-hover');
    root.classList.add('layout-transitioning');
    root.classList.toggle('layout-menu-collapsed', collapsed);

    if (persist) {
      window.localStorage.setItem(MENU_STATE_KEY, collapsed ? '1' : '0');
    }

    window.setTimeout(function () {
      root.classList.remove('layout-transitioning');
      window.dispatchEvent(new Event('resize'));
    }, 350);
  }

  function toggleMenu() {
    if (isDesktop()) {
      setDesktopCollapsed(!root.classList.contains('layout-menu-collapsed'));
      return;
    }

    // Mobile tetap menggunakan mekanisme overlay bawaan Sneat.
    window.Helpers.toggleCollapsed();
  }

  // Terapkan state desktop sebelum interaksi agar perpindahan halaman tidak mengubah lebar sidebar.
  if (isDesktop()) {
    setDesktopCollapsed(window.localStorage.getItem(MENU_STATE_KEY) === '1', false);
  }

  // Initialize menu
  let layoutMenuEl = document.querySelectorAll('#layout-menu');
  layoutMenuEl.forEach(function (element) {
    menu = new Menu(element, {
      orientation: 'vertical',
      closeChildren: false
    });
    window.Helpers.scrollToActive((animate = false));
    window.Helpers.mainMenu = menu;
  });

  // Initialize menu togglers and bind click on each.
  document.querySelectorAll('.layout-menu-toggle').forEach(function (item) {
    item.addEventListener('click', function (event) {
      event.preventDefault();
      toggleMenu();
    });
  });

  // Display menu toggle on hover with delay.
  let delay = function (elem, callback) {
    let timeout = null;
    elem.onmouseenter = function () {
      timeout = setTimeout(callback, Helpers.isSmallScreen() ? 0 : 300);
    };

    elem.onmouseleave = function () {
      const toggler = document.querySelector('#layout-menu .layout-menu-toggle');
      if (toggler) {
        toggler.classList.remove('d-block');
      }
      clearTimeout(timeout);
    };
  };

  if (document.getElementById('layout-menu')) {
    delay(document.getElementById('layout-menu'), function () {
      if (!Helpers.isSmallScreen()) {
        const toggler = document.querySelector('#layout-menu .layout-menu-toggle');
        if (toggler) {
          toggler.classList.add('d-block');
        }
      }
    });
  }

  // Display shadow when menu scrolls.
  let menuInnerContainer = document.getElementsByClassName('menu-inner'),
    menuInnerShadow = document.getElementsByClassName('menu-inner-shadow')[0];
  if (menuInnerContainer.length > 0 && menuInnerShadow) {
    menuInnerContainer[0].addEventListener('ps-scroll-y', function () {
      const thumb = this.querySelector('.ps__thumb-y');
      if (thumb && thumb.offsetTop) {
        menuInnerShadow.style.display = 'block';
      } else {
        menuInnerShadow.style.display = 'none';
      }
    });
  }

  // Init helpers & misc.
  window.Helpers.setAutoUpdate(true);

  const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });

  const accordionActiveFunction = function (e) {
    if (e.type === 'show.bs.collapse') {
      e.target.closest('.accordion-item').classList.add('active');
    } else {
      e.target.closest('.accordion-item').classList.remove('active');
    }
  };

  [].slice.call(document.querySelectorAll('.accordion')).map(function (accordionTriggerEl) {
    accordionTriggerEl.addEventListener('show.bs.collapse', accordionActiveFunction);
    accordionTriggerEl.addEventListener('hide.bs.collapse', accordionActiveFunction);
    return accordionTriggerEl;
  });

  window.Helpers.initPasswordToggle();
  window.Helpers.initSpeechToText();

  // Saat breakpoint berubah, desktop mengembalikan state tersimpan; mobile dibersihkan dari class desktop.
  window.addEventListener('resize', function () {
    if (isDesktop()) {
      const collapsed = window.localStorage.getItem(MENU_STATE_KEY) === '1';
      root.classList.toggle('layout-menu-collapsed', collapsed);
    } else {
      root.classList.remove('layout-menu-collapsed', 'layout-transitioning', 'layout-menu-hover');
    }
  });
})();

function isMacOS() {
  return /Mac|iPod|iPhone|iPad/.test(navigator.userAgent);
}
