// navbar.js - Controlador del navbar mejorado (BS5; jQuery opcional)
class NavbarManager {
  constructor() {
    // Elementos base
    this.nav = document.querySelector('.navbar');
    this.navElevable = document.querySelector('.nav-elevable');
    this.$nav = (window.jQuery && window.jQuery('.nav-elevable')) || null;
    this.logo = document.querySelector('.brand-logo');

    // Olas (parallax)
    this.wavesWrap = document.querySelector('.waves-wrap');
    this.parallaxFactor = 0.15;

    // Control de performance
    this.ticking = false;
    this.scrollTicking = false;

    this.init();
  }

  init() {
    this.setNavHeight();
    this.bindEvents();
    this.setupScrollEffect();
    this.parallaxTick();
    
    // Funcionalidades adicionales
    this.setupMobileNavClose();
    this.setupSmoothScroll();
    this.animateAlerts();
    this.setupAlertAutoDismiss();
    this.setupScrollToTop();
    this.setupDropdownAccessibility();
    this.setupScrollAnimations();
    this.setupFormLoadingStates();
    this.markExternalLinks();
    this.optimizeWaveAnimations();
    
    // Parallax en cards solo en desktop sin reduced motion
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (window.innerWidth > 992 && !prefersReducedMotion) {
      this.setupCardParallax();
    }
  }

  // Lee altura real del nav y la guarda en --nav-h
  setNavHeight() {
    if (!this.nav) return;
    const h = Math.round(this.nav.getBoundingClientRect().height) || 76;
    document.documentElement.style.setProperty('--nav-h', `${h}px`);
  }

  bindEvents() {
    const recalc = () => this.setNavHeight();

    // DOM y viewport
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', recalc, { once: true });
    } else {
      recalc();
    }
    
    window.addEventListener('load', recalc);
    window.addEventListener('resize', recalc);

    // Eventos del colapso BS5
    document.addEventListener('shown.bs.collapse', recalc);
    document.addEventListener('hidden.bs.collapse', recalc);

    // Logo
    if (this.logo) {
      if (this.logo.complete) recalc();
      else this.logo.addEventListener('load', recalc, { once: true });
    }

    // Scroll con throttling
    window.addEventListener('scroll', () => {
      if (!this.scrollTicking) {
        window.requestAnimationFrame(() => {
          this.parallaxTick();
          this.scrollTicking = false;
        });
        this.scrollTicking = true;
      }
    }, { passive: true });
  }

  setupScrollEffect() {
    const toggleElevate = () => {
      if (!this.ticking) {
        window.requestAnimationFrame(() => {
          const active = window.scrollY > 50;
          if (this.$nav) {
            if (active) this.$nav.addClass('elevated');
            else this.$nav.removeClass('elevated');
          } else if (this.navElevable) {
            this.navElevable.classList.toggle('elevated', active);
          }
          this.ticking = false;
        });
        this.ticking = true;
      }
    };

    toggleElevate();
    window.addEventListener('scroll', toggleElevate, { passive: true });
  }

  // Parallax suave de las olas
  parallaxTick() {
    if (!this.wavesWrap) return;
    const y = Math.round(window.scrollY * this.parallaxFactor);
    this.wavesWrap.style.transform = `translateY(${y}px)`;
  }

  // Cerrar navbar mobile al hacer click en un link
  setupMobileNavClose() {
    const navLinks = document.querySelectorAll('.navbar-nav .nav-link');
    const navCollapse = document.querySelector('.navbar-collapse');
    
    navLinks.forEach(link => {
      link.addEventListener('click', () => {
        if (window.innerWidth < 992 && navCollapse && navCollapse.classList.contains('show')) {
          const bsCollapse = bootstrap.Collapse.getInstance(navCollapse);
          if (bsCollapse) {
            bsCollapse.hide();
          }
        }
      });
    });
  }

  // Smooth scroll para anchors
  setupSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function (e) {
        const href = this.getAttribute('href');
        if (href === '#' || href === '#main-content') return;
        
        const target = document.querySelector(href);
        if (target) {
          e.preventDefault();
          target.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
          });
        }
      });
    });
  }

  // Animación de entrada para alerts
  animateAlerts() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach((alert, index) => {
      alert.style.opacity = '0';
      alert.style.transform = 'translateY(-20px)';
      setTimeout(() => {
        alert.style.transition = 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)';
        alert.style.opacity = '1';
        alert.style.transform = 'translateY(0)';
      }, 100 * index);
    });
  }

  // Auto-dismiss para alerts después de 5 segundos
  setupAlertAutoDismiss() {
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(alert => {
      setTimeout(() => {
        const bsAlert = bootstrap.Alert.getInstance(alert);
        if (bsAlert) {
          bsAlert.close();
        } else {
          const closeBtn = alert.querySelector('.btn-close');
          if (closeBtn) closeBtn.click();
        }
      }, 5000);
    });
  }

  // Scroll to top button
  setupScrollToTop() {
    let scrollBtn = document.querySelector('.scroll-to-top');
    if (!scrollBtn) {
      scrollBtn = document.createElement('button');
      scrollBtn.className = 'scroll-to-top';
      scrollBtn.innerHTML = '↑';
      scrollBtn.setAttribute('aria-label', 'Volver arriba');
      scrollBtn.title = 'Volver arriba';
      document.body.appendChild(scrollBtn);
    }

    let btnTicking = false;
    window.addEventListener('scroll', () => {
      if (!btnTicking) {
        window.requestAnimationFrame(() => {
          if (window.scrollY > 400) {
            scrollBtn.classList.add('visible');
          } else {
            scrollBtn.classList.remove('visible');
          }
          btnTicking = false;
        });
        btnTicking = true;
      }
    }, { passive: true });

    scrollBtn.addEventListener('click', () => {
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });
    });
  }

  // Mejorar accesibilidad del dropdown
  setupDropdownAccessibility() {
    const dropdownToggles = document.querySelectorAll('[data-bs-toggle="dropdown"]');
    
    dropdownToggles.forEach(toggle => {
      toggle.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          toggle.click();
        }
      });

      const dropdown = toggle.nextElementSibling;
      if (dropdown && dropdown.classList.contains('dropdown-menu')) {
        dropdown.addEventListener('keydown', (e) => {
          if (e.key === 'Escape') {
            const bsDropdown = bootstrap.Dropdown.getInstance(toggle);
            if (bsDropdown) {
              bsDropdown.hide();
              toggle.focus();
            }
          }
        });
      }
    });
  }

  // Animación de aparición para elementos con clase .animate-on-scroll
  setupScrollAnimations() {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('animate-in');
          observer.unobserve(entry.target);
        }
      });
    }, {
      threshold: 0.1,
      rootMargin: '0px 0px -50px 0px'
    });

    document.querySelectorAll('.animate-on-scroll').forEach(el => {
      el.style.opacity = '0';
      el.style.transform = 'translateY(30px)';
      el.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
      observer.observe(el);
    });

    const style = document.createElement('style');
    style.textContent = `.animate-in { opacity: 1 !important; transform: translateY(0) !important; }`;
    document.head.appendChild(style);
  }

  // Detectar y marcar enlaces externos
  markExternalLinks() {
    const links = document.querySelectorAll('a[href^="http"]');
    links.forEach(link => {
      if (!link.href.includes(window.location.hostname)) {
        link.setAttribute('target', '_blank');
        link.setAttribute('rel', 'noopener noreferrer');
        
        if (!link.querySelector('.external-icon')) {
          const icon = document.createElement('span');
          icon.className = 'external-icon ms-1';
          icon.innerHTML = '↗';
          icon.setAttribute('aria-hidden', 'true');
          link.appendChild(icon);
        }
      }
    });
  }

  // Mejorar rendimiento de las olas animadas
  optimizeWaveAnimations() {
    const waves = document.querySelector('.masthead-waves');
    if (!waves) return;

    const waveObserver = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        const wavePaths = entry.target.querySelectorAll('path');
        wavePaths.forEach(path => {
          if (entry.isIntersecting) {
            path.style.animationPlayState = 'running';
          } else {
            path.style.animationPlayState = 'paused';
          }
        });
      });
    });

    waveObserver.observe(waves);

    if (window.innerWidth < 768) {
      const wavePaths = waves.querySelectorAll('path');
      wavePaths.forEach(path => {
        const animation = path.style.animation;
        if (animation) {
          path.style.animation = animation.replace(/\d+s/, (match) => {
            return (parseInt(match) * 1.5) + 's';
          });
        }
      });
    }
  }

  // Agregar indicador de carga para formularios
  setupFormLoadingStates() {
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
      form.addEventListener('submit', function(e) {
        const submitBtn = this.querySelector('[type="submit"]');
        if (submitBtn && !submitBtn.disabled) {
          submitBtn.disabled = true;
          submitBtn.dataset.originalText = submitBtn.textContent;
          submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Enviando...';
          
          setTimeout(() => {
            submitBtn.disabled = false;
            submitBtn.textContent = submitBtn.dataset.originalText;
          }, 10000);
        }
      });
    });
  }

  // Efecto parallax sutil en cards hover
  setupCardParallax() {
    const cards = document.querySelectorAll('.hover-lift');
    
    cards.forEach(card => {
      card.addEventListener('mousemove', (e) => {
        const rect = card.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        
        const centerX = rect.width / 2;
        const centerY = rect.height / 2;
        
        const rotateX = (y - centerY) / 20;
        const rotateY = (centerX - x) / 20;
        
        card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-8px) scale(1.02)`;
      });
      
      card.addEventListener('mouseleave', () => {
        card.style.transform = '';
      });
    });
  }
}

// Inicialización
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => new NavbarManager());
} else {
  new NavbarManager();
}

// Exponer para uso externo si es necesario
window.NavbarManager = NavbarManager;