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

  // Initialize menu togglers and bind click on each
  let menuToggler = document.querySelectorAll('.layout-menu-toggle');
  menuToggler.forEach(item => {
    item.addEventListener('click', event => {
      event.preventDefault();
      window.Helpers.toggleCollapsed();

      // Simpan pilihan collapse hanya pada desktop. Mobile tetap mengikuti overlay Sneat.
      if (!window.Helpers.isSmallScreen()) {
        window.setTimeout(function () {
          const isCollapsed = document.documentElement.classList.contains('layout-menu-collapsed');
          window.localStorage.setItem(MENU_STATE_KEY, isCollapsed ? '1' : '0');
        }, 350);
      }
    });
  });

  // Display menu toggle on hover with delay
  let delay = function (elem, callback) {
    let timeout = null;
    elem.onmouseenter = function () {
      if (!Helpers.isSmallScreen()) {
        timeout = setTimeout(callback, 300);
      } else {
        timeout = setTimeout(callback, 0);
      }
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

  // Display shadow when menu scrolls
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

  // Init helpers & misc
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

  const accordionTriggerList = [].slice.call(document.querySelectorAll('.accordion'));
  accordionTriggerList.map(function (accordionTriggerEl) {
    accordionTriggerEl.addEventListener('show.bs.collapse', accordionActiveFunction);
    accordionTriggerEl.addEventListener('hide.bs.collapse', accordionActiveFunction);
    return accordionTriggerEl;
  });

  window.Helpers.initPasswordToggle();
  window.Helpers.initSpeechToText();

  // Mobile menggunakan overlay. Desktop menyimpan pilihan expanded/collapsed user.
  if (window.Helpers.isSmallScreen()) {
    return;
  }

  const savedState = window.localStorage.getItem(MENU_STATE_KEY);
  const shouldCollapse = savedState === '1';
  window.Helpers.setCollapsed(shouldCollapse, false);
})();

function isMacOS() {
  return /Mac|iPod|iPhone|iPad/.test(navigator.userAgent);
}
