jQuery(document).ready(function ($) {
  // === Mobile menu toggle ===
  $('#menu-toggle').on('click', function () {
    $('#site-navigation').toggleClass('is-open');
    $('body').toggleClass('no-scroll');
  });

  $('.mobile-menu-close, #site-navigation .menu a, .mobile-menu-panel a').on('click', function () {
    $('#site-navigation').removeClass('is-open');
    $('body').removeClass('no-scroll');
  });

  // === Header search toggle ===
  const $headerSearchForm = $('.header-search-form');
  const $headerSearchToggle = $('.header-search-form .header-search');

  function closeHeaderSearch() {
    $headerSearchForm.removeClass('is-open');
    $headerSearchToggle.attr('aria-expanded', 'false');
  }

  $headerSearchToggle.on('click', function (e) {
    e.preventDefault();

    const $form = $(this).closest('.header-search-form');
    const $input = $form.find('input[name="s"]');
    const shouldAutoFocus = !window.matchMedia('(max-width: 1099px)').matches;

    if (!$form.hasClass('is-open')) {
      $form.addClass('is-open');
      $(this).attr('aria-expanded', 'true');
      if (shouldAutoFocus) {
        setTimeout(function () {
          $input.trigger('focus');
        }, 20);
      }
      return;
    }

    if ($input.val().trim()) {
      $form.trigger('submit');
      return;
    }

    closeHeaderSearch();
  });

  $headerSearchForm.on('submit', function (e) {
    const $form = $(this);
    const $input = $form.find('input[name="s"]');
    const shouldAutoFocus = !window.matchMedia('(max-width: 1099px)').matches;

    if (!$input.val().trim()) {
      e.preventDefault();
      $form.addClass('is-open');
      $form.find('.header-search').attr('aria-expanded', 'true');
      if (shouldAutoFocus) {
        $input.trigger('focus');
      }
    }
  });

  $(document).on('click', function (e) {
    if (!$(e.target).closest('.header-search-form').length) {
      closeHeaderSearch();
    }
  });

  $(document).on('keydown', function (e) {
    if (e.key === 'Escape') {
      closeHeaderSearch();
    }
  });





  // === All Posts Slider ===
  (function ($) {
    const $postSlider = $('.all-posts-slider');
    const $postPrev = $('.posts-slider-prev');
    const $postNext = $('.posts-slider-next');
    const $postProgress = $('.posts-slider-footer__progress span');

    if (!$postSlider.length) {
      return;
    }

    function updatePostProgress(slick, currentSlide) {
      const current = typeof currentSlide === 'number' ? currentSlide : slick.currentSlide || 0;
      const visibleSlides = Math.max(1, Math.ceil(slick.options.slidesToShow || 1));
      const maxSteps = Math.max(slick.slideCount - visibleSlides, 0);
      const progress = maxSteps === 0 ? 100 : ((current + visibleSlides) / slick.slideCount) * 100;

      $postProgress.css('width', `${Math.min(progress, 100)}%`);
    }

    $postSlider.on('init', function (event, slick) {
      updatePostProgress(slick, 0);
    });

    $postSlider.slick({
      slidesToShow: 2.85,
      slidesToScroll: 1,
      infinite: false,
      arrows: false,
      dots: false,
      adaptiveHeight: false,
      responsive: [
        {
          breakpoint: 1100,
          settings: {
            slidesToShow: 2.2
          }
        },
        {
          breakpoint: 768,
          settings: {
            slidesToShow: 1.15
          }
        }
      ]
    });

    $postSlider.on('afterChange', function (event, slick, currentSlide) {
      updatePostProgress(slick, currentSlide);
    });

    $postPrev.on('click', function () {
      $postSlider.slick('slickPrev');
    });

    $postNext.on('click', function () {
      $postSlider.slick('slickNext');
    });
  })(jQuery);









  // === Pillar Slider with Navigation ===
  const $pillarSlider = $('.pillar-slider');
  const $navItems = $('.pillar-nav-item');

  function updateCounter(current, total) {
    $('.pillar-counter .current').text(current);
    $('.pillar-counter .total').text(total);
  }

  $pillarSlider.on('init reInit', function (event, slick) {
    updateCounter(slick.currentSlide + 1, slick.slideCount);
  });

  $pillarSlider.on('afterChange', function (event, slick, currentSlide) {
    updateCounter(currentSlide + 1, slick.slideCount);
    $navItems.removeClass('active');
    $navItems.eq(currentSlide).addClass('active');
  });

  $pillarSlider.slick({
    arrows: true,
    dots: false,
    slidesToShow: 1,
    slidesToScroll: 1,
    adaptiveHeight: true
  });

  $navItems.on('click', function () {
    const index = $(this).data('slide');
    $pillarSlider.slick('slickGoTo', index);
  });

  // === Mobile Product Slider ===
  function initProductSlider() {
    const $productSlider = $('.products-grid');

    if (window.innerWidth <= 768) {
      if (!$productSlider.hasClass('slick-initialized')) {
        $productSlider.slick({
          slidesToShow: 1.5,
          slidesToScroll: 1,
          infinite: false,
          arrows: false,
          dots: false
        });
      }
    } else {
      if ($productSlider.hasClass('slick-initialized')) {
        $productSlider.slick('unslick');
      }
    }
  }

  initProductSlider();
  $(window).on('resize', initProductSlider);

  $('#slider-prev').on('click', function () {
    $('.products-grid').slick('slickPrev');
  });

  $('#slider-next').on('click', function () {
    $('.products-grid').slick('slickNext');
  });

  // === Benefits Slider ===
  (function () {
    const $benefitSlider = $('.benefits-slider');

    if (!$benefitSlider.length) {
      return;
    }

    const $benefitPrev = $('.benefits-slider-prev');
    const $benefitNext = $('.benefits-slider-next');
    const $benefitProgress = $('.benefits-slider-footer__progress span');

    function isBenefitMobile() {
      return window.innerWidth <= 767;
    }

    function updateBenefitProgress(currentSlide, totalSlides) {
      if (!$benefitProgress.length || !totalSlides) {
        return;
      }

      const progress = ((currentSlide + 1) / totalSlides) * 100;
      $benefitProgress.css('width', `${progress}%`);
    }

    function updateBenefitSliderState(index) {
      const $slides = $benefitSlider.find('.benefit-slide');
      const totalSlides = $slides.length;

      $slides.each(function (i) {
        const $slide = $(this);
        $slide.find('.benefit-slide__counter').css({
          display: 'flex',
          opacity: 1,
          visibility: 'visible'
        });
        $slide.find('.benefit-slide__counter-current').text(String(i + 1).padStart(2, '0'));
        $slide.find('.benefit-slide__counter-total').text(String(totalSlides).padStart(2, '0'));
      });

      updateBenefitProgress(index, totalSlides);
    }

    function bindBenefitClicks() {
      $benefitSlider.find('.benefit-slide').off('click').on('click', function () {
        const $slide = $(this);
        const slideIndex = parseInt($slide.attr('data-slick-index'), 10);

        if ($benefitSlider.hasClass('slick-initialized') && !Number.isNaN(slideIndex)) {
          $benefitSlider.slick('slickGoTo', slideIndex);
          return;
        }

        updateActiveBenefitSlide($slide.index());
      });
    }

    function initBenefitSlider() {
      if ($benefitSlider.hasClass('slick-initialized')) {
        $benefitSlider.slick('unslick');
      }

      $benefitSlider.off('init afterChange');
      $benefitPrev.off('click');
      $benefitNext.off('click');

      $benefitSlider.on('init', function (event, slick) {
        updateBenefitSliderState(slick.currentSlide || 0);
      });

      $benefitSlider.on('afterChange', function (event, slick, currentSlide) {
        updateBenefitSliderState(currentSlide);
      });

      $benefitSlider.slick({
        arrows: false,
        dots: false,
        infinite: false,
        slidesToScroll: 1,
        variableWidth: true,
        swipeToSlide: true,
        touchThreshold: 12,
        initialSlide: 0
      });

      $benefitPrev.on('click', function () {
        $benefitSlider.slick('slickPrev');
      });

      $benefitNext.on('click', function () {
        $benefitSlider.slick('slickNext');
      });

      bindBenefitClicks();
      updateBenefitSliderState(0);
    }

    initBenefitSlider();
    $(window).on('resize', function () {
      clearTimeout(window.__benefitResizeTimer);
      window.__benefitResizeTimer = setTimeout(initBenefitSlider, isBenefitMobile() ? 50 : 120);
    });
  })();



  // Otvoriť prvý FAQ po načítaní
  const $firstItem = $('.faq-item').first();
  $firstItem.addClass('open');
  $firstItem.find('.faq-answer').show(); // show namiesto slideDown pri načítaní

  // Toggle funkcia
  $('.faq-question').on('click', function () {
    const $item = $(this).closest('.faq-item');
    const $answer = $item.find('.faq-answer');

    if ($item.hasClass('open')) {
      $answer.stop(true, true).slideUp();
      $item.removeClass('open');
    } else {
      $('.faq-item.open').removeClass('open').find('.faq-answer').stop(true, true).slideUp();
      $answer.stop(true, true).slideDown();
      $item.addClass('open');
    }
  });


  $('.related-products').slick({
    slidesToShow: 3,
    slidesToScroll: 1,
    arrows: true,
    dots: false,
    prevArrow: $('#slider-prev'),
    nextArrow: $('#slider-next'),
    responsive: [
      {
        breakpoint: 1024,
        settings: { slidesToShow: 2 }
      },
      {
        breakpoint: 600,
        settings: { slidesToShow: 1 }
      }
    ]
  });

  // blog filter
  $('#open-filter').on('click', function() {
    $('#filter-box').slideDown();
    $('.close-filter').fadeIn();
    $('.filter-toggle').fadeOut();
    $('.hero-filter').addClass('filter-open'); // ✅ pridáme classu
  });

  $('#close-filter').on('click', function() {
    $('#filter-box').slideUp();
    $('.close-filter').fadeOut();
    $('.filter-toggle').fadeIn();
    $('.hero-filter').removeClass('filter-open'); // ✅ odstránime classu
  });

  $('.filter-option').on('click', function(e) {
    e.preventDefault();
    var catSlug = $(this).data('category');
    var catName = $(this).text().trim();

    $.ajax({
      url: ajaxurl,
      type: 'POST',
      data: {
        action: 'filter_blog_posts',
        category: catSlug
      },
      success: function(response) {
        $('#post-results').html(response);
        $('#filter-box').slideUp();
        $('.close-filter').fadeOut();
        $('.filter-toggle').fadeIn();
        $('.selected-category').text(catName);
        $('.hero-filter').removeClass('filter-open'); // ✅ zatvorenie resetuje stav
      }
    });
  });



});
