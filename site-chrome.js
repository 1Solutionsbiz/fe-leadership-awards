/* Shared site chrome behaviour: header scroll state, full-screen menu, footer share, sticky-reveal footer.
   Loaded by every page; page-specific scripts stay inline. */
(function(){
  var nav = document.getElementById('siteNav');

  // Home page only: the header starts transparent over the hero and the brand mark fades in with scroll.
  // Inner pages mark the header data-static, which is already in its final (scrolled) state.
  if(nav && !nav.hasAttribute('data-static')){
    var NAV_FADE_DISTANCE = 220;
    var onScroll = function(){
      var y = window.scrollY;
      var progress = Math.min(1, Math.max(0, y / NAV_FADE_DISTANCE));
      nav.style.setProperty('--nav-progress', progress);
      if(y > 30){ nav.classList.add('scrolled'); } else { nav.classList.remove('scrolled'); }
    };
    document.addEventListener('scroll', onScroll, {passive:true});
    onScroll();
  }

  // full-screen menu — circle-morph reveal from the trigger's position
  var menuTrigger = document.getElementById('menuTrigger');
  var menuClose = document.getElementById('menuClose');
  var menuOverlay = document.getElementById('menuOverlay');
  if(menuTrigger && menuClose && menuOverlay){
    var openMenu = function(){
      var r = menuTrigger.getBoundingClientRect();
      menuOverlay.style.setProperty('--ox', (r.left + r.width/2) + 'px');
      menuOverlay.style.setProperty('--oy', (r.top + r.height/2) + 'px');
      menuOverlay.classList.add('open');
      menuTrigger.setAttribute('aria-expanded','true');
      document.body.style.overflow = 'hidden';
    };
    var closeMenu = function(){
      menuOverlay.classList.remove('open');
      menuTrigger.setAttribute('aria-expanded','false');
      document.body.style.overflow = '';
    };
    menuTrigger.addEventListener('click', openMenu);
    menuClose.addEventListener('click', closeMenu);
    menuOverlay.querySelectorAll('.menu-list a').forEach(function(a){ a.addEventListener('click', closeMenu); });
    document.addEventListener('keydown', function(e){ if(e.key === 'Escape') closeMenu(); });
  }

  // links to "/" while already on the home page would reload it — scroll to the top instead
  var isHome = location.pathname === '/' || location.pathname === '/index.html' || location.pathname === '/index';
  if(isHome){
    document.querySelectorAll('a[href="/"]').forEach(function(a){
      a.addEventListener('click', function(e){
        e.preventDefault();
        window.scrollTo({top:0, behavior:'smooth'});
      });
    });
  }

  // footer share button — native share sheet where available, else copy the link
  var shareBtn = document.getElementById('shareBtn');
  if(shareBtn){
    shareBtn.addEventListener('click', function(){
      var shareData = { title: document.title, url: location.href };
      if(navigator.share){
        navigator.share(shareData).catch(function(){});
      } else if(navigator.clipboard){
        navigator.clipboard.writeText(location.href).then(function(){
          var original = shareBtn.textContent;
          shareBtn.textContent = 'Link Copied';
          setTimeout(function(){ shareBtn.textContent = original; }, 1800);
        });
      }
    });
  }

  // sticky reveal footer — footer sits fixed at the viewport bottom and fades/scales
  // in as the page scrolls into the spacer reserved for it, so it feels uncovered
  // rather than just appearing.
  var stickyFooter = document.getElementById('siteFooter');
  var footerSpacer = document.getElementById('footerSpacer');
  if(stickyFooter && footerSpacer && !window.matchMedia('(prefers-reduced-motion: reduce)').matches){
    var footerHeight = stickyFooter.offsetHeight;
    footerSpacer.style.height = footerHeight + 'px';
    stickyFooter.classList.add('footer--sticky');

    var updateFooterReveal = function(){
      var rect = footerSpacer.getBoundingClientRect();
      var progress = (window.innerHeight - rect.top) / footerHeight;
      progress = Math.max(0, Math.min(1, progress));
      stickyFooter.style.opacity = progress;
      stickyFooter.style.transform = 'scale(' + (0.94 + 0.06 * progress) + ')';
      stickyFooter.style.pointerEvents = progress > 0.98 ? 'auto' : 'none';
    };
    updateFooterReveal();

    var footerTicking = false;
    window.addEventListener('scroll', function(){
      if(!footerTicking){
        window.requestAnimationFrame(function(){ updateFooterReveal(); footerTicking = false; });
        footerTicking = true;
      }
    }, {passive:true});
    var remeasure = function(){
      footerHeight = stickyFooter.offsetHeight;
      footerSpacer.style.height = footerHeight + 'px';
      updateFooterReveal();
    };
    window.addEventListener('resize', remeasure);
    window.addEventListener('load', remeasure);
  }
})();
