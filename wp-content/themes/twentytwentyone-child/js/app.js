jQuery(document).ready(function ($) {
  // === Mobile menu toggle ===
  $('#menu-toggle').on('click', function () {
    $('#site-navigation').toggleClass('is-open');
    $('body').toggleClass('no-scroll');
  });





  // === All Posts Slider + Progress Bar ===
  (function ($) {
  const $postSlider = $('.all-posts-slider');

  function updateProgressBar(slick, currentSlide) {
        let slidesToShow = slick.options.slidesToShow;

        if (typeof slidesToShow === 'function') {
            slidesToShow = slidesToShow();
        }

        const totalSlides = slick.slideCount;
        const current = currentSlide || 0;
        const maxSteps = Math.max(totalSlides - Math.floor(slidesToShow), 1);

        let progress = (current / maxSteps) * 100;

        // Ak chceme mať vždy aspoň trochu viditeľný progress
        progress = Math.max(progress, 5);

        $('.slider-progress-bar').css('width', progress + '%');
    }



  console.log('Inicializujem slick...');

  $postSlider.slick({
    slidesToShow: 2.9,
    slidesToScroll: 1,
    infinite: false,
    arrows: false,
    dots: false,
    responsive: [
      {
        breakpoint: 768,
        settings: {
          slidesToShow: 1.2
        }
      }
    ]
  });

  $postSlider.on('afterChange', function (event, slick, currentSlide) {
    console.log('afterChange fired');
    updateProgressBar(slick, currentSlide);
  });

  // Fallback: čakáme, kým slick bude hotový
  const waitForSlider = setInterval(function () {
    if ($postSlider.hasClass('slick-initialized')) {
      console.log('Slider is initialized ✅');
      const slick = $postSlider.slick('getSlick');
      updateProgressBar(slick, slick.currentSlide);
      clearInterval(waitForSlider);
    } else {
      console.log('⏳ Waiting for slider to initialize...');
    }
  }, 100);
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

  // === Benefits Slider (desktop only) ===
  function isMobile() {
    return window.innerWidth <= 768;
  }

  let isCentered = false;
  let slickInitialized = false;

  function updateActiveSlide(index) {
    $('.benefits-slider .benefit-slide').each(function (i) {
      const $slide = $(this);
      const thumb = $slide.data('thumb');
      const detail = $slide.data('detail');

      $slide.toggleClass('is-active', i === index);
      $slide.css('background-image', `url(${i === index ? detail : thumb})`);
    });
  }

  function activateClickMode() {
    $('.benefit-slide').off('click').on('click', function () {
      const $this = $(this);
      const thumb = $this.data('thumb');
      const detail = $this.data('detail');

      $('.benefit-slide').removeClass('is-active');
      $this.addClass('is-active');

      if (detail) {
        $this.css('background-image', `url(${detail})`);
      }

      $('.benefit-slide').not($this).each(function () {
        const $s = $(this);
        const thumbUrl = $s.data('thumb');
        if (thumbUrl) {
          $s.css('background-image', `url(${thumbUrl})`);
        }
      });
    });
  }

  function initBenefitSlider() {
    const $benefitSlider = $('.benefits-slider');

    if (isMobile()) {
      if (slickInitialized) {
        $benefitSlider.slick('unslick');
        slickInitialized = false;
      }

      activateClickMode();
      updateActiveSlide(0);

    } else {
      if (!slickInitialized) {
        $benefitSlider.slick({
          arrows: false,
          dots: false,
          slidesToScroll: 1,
          infinite: false,
          variableWidth: true,
          initialSlide: 0,
          centerMode: false
        });

        slickInitialized = true;
        isCentered = false;

        $benefitSlider.on('beforeChange', function () {
          $benefitSlider.addClass('is-animating');
        });

        $benefitSlider.on('afterChange', function (event, slick, currentSlide) {
          updateActiveSlide(currentSlide);

          if (currentSlide > 0 && !isCentered) {
            isCentered = true;
            $benefitSlider.slick('slickSetOption', 'centerMode', true, true);
          }

          setTimeout(() => {
            $benefitSlider.removeClass('is-animating');
          }, 300);
        });

        setTimeout(() => {
          updateActiveSlide(0);
        }, 100);
      }
    }
  }

  initBenefitSlider();
  $(window).on('resize', initBenefitSlider);



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


  (function ($) {
    const $productSlider = $('.all-related-products .products-grid');

    function updateProgressBar(slick, currentSlide) {
      let slidesToShow = slick.options.slidesToShow;

      if (typeof slidesToShow === 'function') {
        slidesToShow = slidesToShow();
      }

      const totalSlides = slick.slideCount;
      const current = currentSlide || 0;
      const maxSteps = Math.max(totalSlides - Math.floor(slidesToShow), 1);
      let progress = (current / maxSteps) * 100;
      progress = Math.max(progress, 5);

      $('.slider-progress-bar').css('width', progress + '%');
    }

    if ($productSlider.length && typeof $.fn.slick === 'function') {
      $productSlider.slick({
        slidesToShow: 4.3,
        slidesToScroll: 1,
        infinite: false,
        arrows: false,
        dots: false,
        responsive: [
          {
            breakpoint: 1200, // do 1199px
            settings: {
              slidesToShow: 3.3
            }
          },
          {
            breakpoint: 991, // do 1199px
            settings: {
              slidesToShow: 2.3
            }
          },
          {
            breakpoint: 768,
            settings: {
              slidesToShow: 1.2
            }
          }
        ]
      });

      $productSlider.on('afterChange', function (event, slick, currentSlide) {
        updateProgressBar(slick, currentSlide);
      });

      const waitForSlider = setInterval(function () {
        if ($productSlider.hasClass('slick-initialized')) {
          const slick = $productSlider.slick('getSlick');
          updateProgressBar(slick, slick.currentSlide);
          clearInterval(waitForSlider);
        }
      }, 100);
    } else {
      console.warn('Slick slider nebol inicializovaný – skontroluj, či je načítaný slick.js a máš správne HTML.');
    }
  })(jQuery);


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
