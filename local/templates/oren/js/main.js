window.addEventListener('DOMContentLoaded', () => { // Ждём загрузки DOM дерева 
  'use strict';
  const
    body = document.body,
    width = body.offsetWidth;

  // let modal = document.getElementById('quick-view');

  // if (modal) {
  //   let myModal = new bootstrap.Modal(document.getElementById('quick-view'), {
  //     keyboard: false
  //   })
  //   myModal.show()
  // }

  // Плавный скролл до якоря
  const anchors = () => {
    const anchors = document.querySelectorAll('a[href*="#"]');

    for (let anchor of anchors) {
      anchor.addEventListener('click', (e) => {
        e.preventDefault();

        const blockID = anchor.getAttribute('href').substr(1);

        document.getElementById(blockID).scrollIntoView({
          behavior: 'smooth',
          block: 'start'
        });

      });
    }
  };
  anchors();

  const anchors2 = () => {
    const anchors = document.querySelectorAll('.look-point');

    for (let anchor of anchors) {
      anchor.addEventListener('click', (e) => {
        e.preventDefault();

        console.log(anchor);
        const blockID = anchor.getAttribute('data-anchor');

        document.getElementById(blockID).scrollIntoView({
          behavior: 'smooth',
          block: 'start'
        });

      });
    }
  };
  if(width <= 992) {
    anchors2();
  }

  // Маска телефона
  $(document).ready(function () {
    $('.phone').mask('+7(000) 000-00-00');
    $('.date').mask('00.00.0000');
  });

  // Soc
  const soc = () => {
    let socBox = document.querySelector('.soc');

    if (socBox) {
      body.addEventListener('click', (e) => {
        let target = e.target;

        if (target.closest('.soc-button')) {

          if (!socBox.classList.contains('_show')) {
            socBox.classList.remove('_animat');
            socBox.classList.add('_show');

          } else {
            socBox.classList.add('_animat');
            socBox.classList.remove('_show');
          }
        }
        if (target.closest('.soc-button') || target.closest('.soc-container')) {
        } else {
          socBox.classList.add('_animat');
          socBox.classList.remove('_show');
        }

      })

      if (width > 992) {
        socBox.addEventListener('mouseout', (e) => {
          let
            target = e.target;

          if (target.closest('.soc-button')) {
            socBox.classList.add('_animat');
            socBox.classList.remove('_show');
          }
        })
      }
    }
  }
  soc()

  // scroll
  const scrollWindow = () => {
    let
      headerDesctop = document.querySelector('.header-desctop'),
      headerMob = document.querySelector('.header-mob'),
      soc = document.querySelector('.soc'),
      socBox = document.querySelector('.soc-box');
    window.addEventListener('scroll', () => {
      if (body.getBoundingClientRect().top < -50) {
        headerDesctop.classList.add('_scroll')
        headerMob.classList.add('_scroll')
        socBox.classList.add('_scroll')
      } else {
        socBox.classList.remove('_scroll')
        headerDesctop.classList.remove('_scroll')
        headerMob.classList.remove('_scroll')

      }
    })
  }
  scrollWindow();

  // search
  const search = () => {
    let
      input = document.querySelector('.header-desctop__input'),
      search = document.querySelector('.header-desctop__search');

    body.addEventListener('click', (e) => {
      let target = e.target;

      if (target.closest('.header-desctop__btn')) {
        if (!search.classList.contains('_show')) {
          search.classList.add('_show');
        } else {
          search.classList.remove('_show');
          setTimeout(() => {
            input.value = ``;
          }, 400);
        }
      } else {
        search.classList.remove('_show');
        setTimeout(() => {
          input.value = ``;
        }, 400);
      }

      if (target.closest('.header-desctop__input')) {
        search.classList.add('_show');
      }
    })
  }
  search();

  // burger
  const burger = () => {
    let
      headerCatalog = document.querySelector('.header-catalog'),
      headerMob = document.querySelector('.header-mob'),
      burger = document.querySelector('.burger');

    burger.addEventListener('click', () => {

      if (!burger.classList.contains('open')) {
        burger.classList.add('open');
        headerMob.classList.add('_show');
        body.classList.add('modal-open');
        headerCatalog.classList.add('_show');

      } else {
        burger.classList.remove('open');
        headerMob.classList.remove('_show');
        body.classList.remove('modal-open');
        headerCatalog.classList.remove('_show');
        setTimeout(() => {
          headerCatalog.scrollTop = 0;
        }, 400);

      }

    })
  }
  burger()

  // menu-desctop
  const menuDesctop = () => {
    let
      catalogBtn = document.getElementById('catalog'),
      desctop = document.querySelector('.header-desctop'),
      catalog = document.querySelector('.header-catalog');

    body.addEventListener('mouseover', (e) => {
      let target = e.target;

      if (target.closest('#catalog') || target.closest('.header-catalog')) {
        desctop.classList.add('_show');
        catalog.classList.add('_show');
        catalogBtn.classList.add('_active');
      } else {
        desctop.classList.remove('_show');
        catalog.classList.remove('_show');
        catalogBtn.classList.remove('_active');
      }

    })
  }
  if (width > 992) {
    menuDesctop();
  }

  // menu-mob
  const menuMob = () => {
    let
      headerCatalogItem = document.querySelector('.header-catalog__item'),
      headerCatalogArrow = document.querySelector('.header-catalog__arrow'),
      headerCatalogRow = document.querySelector('.header-catalog__row'),
      headerCatalogBox = document.querySelector('.header-catalog__box'),
      menuItemAll = document.querySelectorAll('.menu-item');

    menuItemAll.forEach(menuItem => {
      let
        arrow = menuItem.querySelector('.menu-head__arrow'),
        container = menuItem.querySelector('.menu-container'),
        box = menuItem.querySelector('.menu-box');

      if (menuItem.classList.contains('_show')) {
        menuItem.classList.add('_show');
        container.style.cssText = `
          height: ${box.clientHeight}px
        `
      }

      if (!container) {
        arrow.classList.add('d-none')
      }

      menuItem.addEventListener('click', (e) => {
        let target = e.target;

        if (target.closest('.menu-head__arrow')) {
          if (!menuItem.classList.contains('_show')) {
            menuItem.classList.add('_show');
            container.style.cssText = `
              height: ${box.clientHeight}px
            `
          } else {
            menuItem.classList.remove('_show');
            container.removeAttribute('style')
          }
        }
      })
    })

    if (headerCatalogItem.classList.contains('_show')) {
      headerCatalogBox.style.cssText =
        `
        height: auto
      `
    }

    headerCatalogArrow.addEventListener('click', () => {
      if (!headerCatalogItem.classList.contains('_show')) {
        headerCatalogItem.classList.add('_show');
        headerCatalogBox.style.cssText =
          `
          height: ${headerCatalogRow.clientHeight}px
        `
        setTimeout(() => {
          headerCatalogBox.style.cssText =
            `
          height: auto
        `
        }, 400);
      } else {
        headerCatalogItem.classList.remove('_show');

        headerCatalogBox.style.cssText =
          `
          height: ${headerCatalogRow.clientHeight}px
        `
        setTimeout(() => {
          headerCatalogBox.removeAttribute('style');
        }, 100);
      }
    })
  }
  menuMob();

  //История начислений
  const accrualHistory = () => {
    let menuItemAll = document.querySelectorAll('.account-history');

    menuItemAll.forEach(menuItem => {
      let
        arrow = menuItem.querySelector('.account-history__toggle'),
        container = menuItem.querySelector('.account-history__container'),
        box = menuItem.querySelector('.account-history__table');

      if (menuItem.classList.contains('_show')) {
        menuItem.classList.add('_show');
        container.style.cssText = `
          height: ${box.clientHeight}px
        `
      }

      if (!container) {
        arrow.classList.add('d-none')
      }

      menuItem.addEventListener('click', (e) => {
        let target = e.target;

        if (target.closest('.account-history__toggle')) {
          if (!menuItem.classList.contains('_show')) {
            menuItem.classList.add('_show');
            container.style.cssText = `
              height: ${box.clientHeight + 2}px
            `
          } else {
            menuItem.classList.remove('_show');
            container.removeAttribute('style')
          }
        }
      })
    })
  }
  accrualHistory()

  // like
  /*
  const like = () => {
    let bestsellerBoxAll = document.querySelectorAll('.bestseller-box, .gallery-top__info, .product-button__like, .goods-item__left');

    bestsellerBoxAll.forEach(bestsellerBox => {
      let likeBtn = bestsellerBox.querySelector('.bestseller-head__like');

      if (likeBtn) {
        likeBtn.addEventListener('click', () => {
          likeBtn.classList.toggle('_add')
        })
      }
    })

  }
  like();
   */

  // 2 Ползунка
  const rangeSlide = () => {
    const rangeSlider = document.getElementById('range-slider');

    if (rangeSlider) {
      noUiSlider.create(rangeSlider, {
        start: [1500, 85500],
        connect: true,
        step: 1,
        range: {
          'min': [0],
          'max': [100000]
        }
      });

      const input0 = document.getElementById('input-0');
      const input1 = document.getElementById('input-1');
      const inputs = [input0, input1];

      rangeSlider.noUiSlider.on('update', function (values, handle) {

        inputs[handle].value = Math.round(values[handle]);
      });

      const setRangeSlider = (i, value) => {
        let arr = [null, null];
        arr[i] = value;

        rangeSlider.noUiSlider.set(arr);
      };

      inputs.forEach((el, index) => {
        el.addEventListener('change', (e) => {
          setRangeSlider(index, e.currentTarget.value);
        });
      });
    }
  }
  rangeSlide();

  // quiz
  const quiz = () => {
    const quizBlock = document.querySelector('.quiz');

    if (quizBlock) {
      const main = document.querySelector('.quiz-main');
      const final = document.querySelector('.quiz-final');
      const count = main.querySelector('.quiz-count');
      const restart = final.querySelector('.quiz-final__button');
      const furtherButtons = document.querySelectorAll('.quiz-button__further');
      const questions = document.querySelectorAll('.quiz-question');
      const { noUiSlider } = document.getElementById('range-slider');

      const quiz = {
        count: 0,
        data: {
          name1: {
            name: 'вид платка',
            value: '',
          },
          name2: {
            name: 'форма платка',
            value: '',
          },
          name3: {
            name: 'размер',
            value: '',
          },
          name4: {
            name: 'цвет',
            value: '',
          },
          'input-0': {
            name: 'цена: от',
            value: '',
          },
          'input-1': {
            name: 'до',
            value: '',
          }
        },

        init() {
          restart.addEventListener('click', () => this.restart());
          main.addEventListener('change', () => this.active());
          main.addEventListener('click', ({ target }) => this.change(target));
          noUiSlider.on('change.quiz', () => this.active());
        },

        restart() {
          final.classList.add('d-none');
          main.classList.remove('d-none');

          count.textContent = this.count + 1;
          questions[this.count].classList.add('_active');
        },

        active() {
          const button = furtherButtons[this.count];

          if (!button?.classList.contains('_disable')) return;

          button.classList.remove('_disable');
        },

        change(target) {
          if (!target?.closest('button')) return;

          if (target?.closest('.quiz-button__further')) {
            this.save();
            questions[this.count++].classList.remove('_active');
          }

          if (target?.closest('.quiz-button__back'))
            questions[this.count--].classList.remove('_active');

          if (this.count < questions.length - 1) {
            count.textContent = this.count + 1;
            questions[this.count].classList.add('_active');
          } else this.final();
        },

        save() {
          questions[this.count].querySelectorAll('input').forEach(input => {
            switch (input.type) {
              case 'radio':
                input.checked && (this.data[input.name].value = input.value);
                return;
              case 'number':
                this.data[input.id].value = input.value;
                return;
            }
          });
        },

        final() {
          this.count = 0;
          noUiSlider.reset();
          final.classList.remove('d-none');
          main.classList.add('d-none');

          furtherButtons.forEach(button => button.classList.add('_disable'));

          questions.forEach(question =>
            question.querySelectorAll('input').forEach(input =>
              input.checked = false));

          this.log();
        },

        log() {
          console.log(`${this.data.name1.name}: ${this.data.name1.value}
        ${this.data.name2.name}: ${this.data.name2.value}
        ${this.data.name3.name}: ${this.data.name3.value}
        ${this.data.name4.name}: ${this.data.name4.value}
        ${this.data['input-0'].name} ${this.data['input-0'].value} ${this.data['input-1'].name} ${this.data['input-1'].value}
        `);
        }
      };

      quiz.init();

    }
  };
  quiz();

  // Обрезание текста
  const textLong = (classSelector, lengthy) => {
    const truncate = (str, maxlength) => {

      if (str.length > maxlength) {
        str = str.substring(0, maxlength - 3); // итоговая длина равна maxlength
        return str + "...";
        // В кодировке Unicode существует специальный символ «троеточие»: … (HTML: &hellip;)
      } else {
        return str;
      }
    }

    let textAll = document.querySelectorAll(classSelector);
    textAll.forEach(elem => {
      let text = elem.textContent.trim();
      elem.textContent = truncate(text, lengthy)
    })

  }
  textLong('.collections-description', 104)
  textLong('.blog-description', 90)

  // добавление фона не на главной
  const addBg = () => {
    let
      mainSlider = document.querySelector('.main-slider'),
      desctop = document.querySelector('.header-desctop'),
      mob = document.querySelector('.header-mob');

    if (!mainSlider) {
      desctop.classList.add('_bg');
      mob.classList.add('_bg');
    } else {
      desctop.classList.remove('_bg');
      mob.classList.remove('_bg');
    }
  }
  addBg()

  // Каталог - фильтр
  const catalogFilter = () => {
    let
      burger = document.querySelector('.burger');
    const
      sidebar = document.querySelector('.sidebar'),
      catalogBtn = document.getElementById('sidebarCatalog'),
      catalogBox = document.querySelector('.sidebar-catalog'),
      filterBox = document.querySelector('.sidebar-filter'),
      filterBtn = document.getElementById('sidebarFilter');


    if (sidebar) {
      catalogBtn.addEventListener('click', () => {

        catalogBox.style.cssText = `
          height: calc( 100vh - ${catalogBox.getBoundingClientRect().top + 20}px)
        `
        if (!catalogBtn.classList.contains('_show')) {
          sidebar.classList.add('_show');
          catalogBtn.classList.add('_show');
          catalogBox.classList.add('_show');
          body.classList.add('modal-open');
          filterBtn.classList.remove('_show');
          filterBox.classList.remove('_show');
        } else {
          sidebar.classList.remove('_show');
          catalogBtn.classList.remove('_show');
          catalogBox.classList.remove('_show');
          body.classList.remove('modal-open');
          setTimeout(() => {
            catalogBox.scrollTop = 0;
            filterBox.scrollTop = 0;
          }, 400);
        }

      })

      filterBtn.addEventListener('click', () => {
        filterBox.style.cssText = `
          height: calc( 100vh - ${catalogBox.getBoundingClientRect().top + 20}px)
        `
        if (!filterBtn.classList.contains('_show')) {
          sidebar.classList.add('_show');
          filterBtn.classList.add('_show');
          filterBox.classList.add('_show');
          body.classList.add('modal-open');
          catalogBtn.classList.remove('_show');
          catalogBox.classList.remove('_show');
        } else {
          sidebar.classList.remove('_show');
          filterBtn.classList.remove('_show');
          filterBox.classList.remove('_show');
          body.classList.remove('modal-open');
          setTimeout(() => {
            catalogBox.scrollTop = 0;
            filterBox.scrollTop = 0;
          }, 400);
        }
      })

      burger.addEventListener('click', () => {
        setTimeout(() => {
          sidebar.classList.remove('_show');
          catalogBtn.classList.remove('_show');
          catalogBox.classList.remove('_show');
          filterBtn.classList.remove('_show');
          filterBox.classList.remove('_show');
          catalogBox.scrollTop = 0;
          filterBox.scrollTop = 0;
        }, 400);
      })

    }
  }
  catalogFilter()

  // Фильтры
  const filter = () => {
    let filterAll = document.querySelectorAll('.filter');

    filterAll.forEach(filterElem => {
      let
        head = filterElem.querySelector('.filter-head'),
        container = filterElem.querySelector('.filter-container'),
        box = filterElem.querySelector('.filter-box');
      head.addEventListener('click', () => {
        if (!head.classList.contains('_show')) {
          filterAll.forEach(elem => {
            let
              elemHead = elem.querySelector('.filter-head'),
              elemContainer = elem.querySelector('.filter-container');

            elemHead.classList.remove('_show');
            elemContainer.classList.remove('_show');
            elemContainer.removeAttribute('style')
          })

          head.classList.add('_show');
          container.classList.add('_show');
          container.style.cssText = `
          height: ${box.clientHeight + 36}px
          `
        } else {
          head.classList.remove('_show');
          container.classList.remove('_show');
          container.removeAttribute('style')

        }
      })

      body.addEventListener('click', (e) => {
        let target = e.target;

        if (target.closest('.filter-head') || target.closest('.filter-container')) {
        } else {
          head.classList.remove('_show');
          container.classList.remove('_show');
          container.removeAttribute('style')
        }
      })
    })
  }
  filter()

  const election = () => {
    let
      more = document.querySelector('.election-more'),
      container = document.querySelector('.election-container'),
      box = document.querySelector('.election-box');

    if (more) {
      more.addEventListener('click', () => {
        if (!more.classList.contains('_show')) {
          more.classList.add('_show');
          more.textContent = 'Скрыть';
          container.classList.add('_show');
          container.style.cssText = `
            height: ${box.clientHeight}px
          `
        } else {
          more.classList.remove('_show');
          more.textContent = 'Читать полностью';
          container.classList.remove('_show');
          container.removeAttribute('style')
        }
      })

    }
  }
  election()

  // Banner
  const banner = () => {
    let
      sidebarCatalog = document.getElementById('sidebarCatalog'),
      sidebarFilter = document.getElementById('sidebarFilter'),
      bannerBox = document.querySelector('.banner'),
      close = document.querySelector('.banner-close');

    if (bannerBox) {

      if (width > 992) {
        setTimeout(() => {
          bannerBox.classList.add('_show');
        }, 3000);

      } else {
        setTimeout(() => {
          bannerBox.classList.add('_show');
          body.classList.add('modal-open');
        }, 3000);

      }

      close.addEventListener('click', () => {
        if (sidebarCatalog || sidebarFilter) {
          if (sidebarCatalog.classList.contains('_show') || sidebarFilter.classList.contains('_show')) {
            bannerBox.classList.remove('_show');
          } else {
            bannerBox.classList.remove('_show');
            body.classList.remove('modal-open')
          }
        } else {
          bannerBox.classList.remove('_show');
          body.classList.remove('modal-open')
        }
      })
    }
  }
  banner()

  // Цвет в карточке товара
  /*
  const colorProduct = () => {
    const productItemAll = document.querySelectorAll('.product-box__item');

    productItemAll.forEach(productItem => {

      const checkBox = productItem.querySelector('.product-box__check');

      if (checkBox) {
        let
          name = productItem.querySelector('.product-box__name'),
          inputAll = checkBox.querySelectorAll('.radio__input');

        inputAll.forEach(input => {
          if (input.checked === true) {
            name.textContent = input.value.toLowerCase();
          }

          input.addEventListener('click', () => {
            if (input.checked === true) {
              name.textContent = input.value.toLowerCase();
            }
          })

        })
      }
    })
  }
  colorProduct();
   */

  // Счётчик количиства
  /*
  const countNum = () => {
    let
      boxAll = document.querySelectorAll('.price-box');

    boxAll.forEach((box) => {

      box.addEventListener('click', (e) => {
        let
          input = box.querySelector('.price-input'),
          inputValue = +input.getAttribute('value'),
          target = e.target;

        if (target.closest('.price-plus')) {
          input.setAttribute('value', inputValue + 1)
        }

        if (target.closest('.price-minus')) {

          if (inputValue <= 1) {
            input.setAttribute('value', 1)

          } else {
            input.setAttribute('value', inputValue - 1)

          }
        }

      })
    })

  };
  countNum();

   */

  // Выбор города в продукте
  const city = () => {
    let productCityAll = document.querySelectorAll('.product-city');

    productCityAll.forEach(productCity => {
      if (productCity) {
        let
          name = productCity.querySelector('.product-city__name'),
          box = productCity.querySelector('.product-city__box'),
          listBox = productCity.querySelector('.product-city__list'),
          listAll = productCity.querySelectorAll('.product-city__list li');

        listAll.forEach(list => {
          list.addEventListener('click', () => {
            listAll.forEach(elem => {
              elem.classList.remove('_active');
            })
            name.textContent = list.textContent;
            list.classList.add('_active');
          })
        })

        body.addEventListener('click', (e) => {
          let target = e.target;

          if (target.closest('.product-city__name')) {
            if (!box.classList.contains('_show')) {
              box.classList.add('_show')
            } else {
              box.classList.remove('_show');
              setTimeout(() => {
                listBox.scrollTop = 0
              }, 400);

            }
          } else {
            box.classList.remove('_show');
          }

          if (!target.closest('.product-city')) {
            setTimeout(() => {
              listBox.scrollTop = 0
            }, 400);
          }
          if (target.closest('.product-city__box')) {
            box.classList.add('_show')
          }
          if (target.closest('.product-city__list li')) {
            box.classList.remove('_show');
            setTimeout(() => {
              listBox.scrollTop = 0
            }, 400);
          }
        })
      }
    })
  }
  city();

  // Аккордион
  const accordeon = () => {
    const accordeon = document.getElementById('accordeon');

    if (accordeon) {
      let itemAll = document.querySelectorAll('.accordeon-item');

      itemAll.forEach(elem => {

        let
          container = elem.querySelector('.accordeon-body');

        if (elem.classList.contains('_show')) {
          container.style.cssText = `
            height: auto
          `

        }
      })

      itemAll.forEach(item => {
        item.addEventListener('click', () => {
          if (!item.classList.contains('_show')) {
            itemAll.forEach(elem => {
              if (elem.classList.contains('_show')) {
                let elemContainer = elem.querySelector('.accordeon-body');

                elemContainer.style.cssText = `
                  height: ${elemContainer.clientHeight}px;                
                `
                setTimeout(() => {
                  elemContainer.removeAttribute('style');
                  elem.classList.remove('_show')
                }, 50);
              }
            })

            let
              itemBody = item.querySelector('.accordeon-body'),
              itemContent = item.querySelector('.accordeon-content');

            item.classList.add('_show');
            itemBody.style.cssText = `
              height: ${itemContent.clientHeight}px;
            `
            setTimeout(() => {
              itemBody.style.cssText = `
                height: auto;
              `

            }, 1000);

          }
        })
      })

      let
        accordionItemAll = document.querySelectorAll('.accordion-item');

      accordionItemAll.forEach((accordionItem, accordionId) => {
        if (accordionItem) {
          let
            accordionHeader = accordionItem.querySelector('.accordion-header'),
            accordionCollapse = accordionItem.querySelector('.accordion-collapse');

          accordionHeader.setAttribute('id', `heading-${accordionId}`)
          accordionHeader.setAttribute('data-bs-target', `#collapse-${accordionId}`)
          accordionHeader.setAttribute('aria-controls', `collapse-${accordionId}`)
          accordionCollapse.setAttribute('id', `collapse-${accordionId}`)
          accordionCollapse.setAttribute('aria-labelledby', `heading-${accordionId}`)
        }
      })
    }
  };
  accordeon();

  // load photo
  const photoLoad = () => {
    const userPhoto = document.getElementById('account-block');

    if (userPhoto) {
      function getBase64(file, fn) {
        const reader = new FileReader();

        reader.readAsDataURL(file);
        reader.onload = function () {
          fn(reader.result);
        };
        reader.onerror = function (error) {
          console.log('Error: ', error);
        };
      }

      document.getElementById('user-photo-input')
        .addEventListener('change', ({ target }) =>
          getBase64(target.files[0], (result) => target.previousElementSibling.setAttribute('src', result)));

    }

  }
  photoLoad()

  // увеличение изображения в катрочке товара
  /*
  const enlarge = () => {
    const block = document.getElementById('gallery-top-wrap');

    if (block) {
      let
        zoomContainer = document.querySelector('.zoom-container'),
        slideAll = block.querySelectorAll('.swiper-slide');

      slideAll.forEach(slide => {
        slide.addEventListener('mousemove', (e) => {
          let
            x = e.offsetX == undefined ? e.layerX : e.offsetX,
            y = e.offsetY == undefined ? e.layerY : e.offsetY,
            img = slide.querySelector('img');

          zoomContainer.style.cssText = `
            background-image: url('${img.getAttribute('src')}');
            background-position: ${Math.round((x / slide.offsetWidth * 100))}% ${y / slide.offsetHeight * 100}%;
            visibility: visible;
            opacity: 1
          `
        })

        slide.addEventListener('mouseout', () => {
          zoomContainer.style.cssText = `
            visibility: hidden;
            opacity: 0;
          `
        })
      })
    }
  }
  enlarge();
   */

  // корзина на мобильном
  const mobBasket = () => {
    const
      footer = document.querySelector('.footer'),
      socBox = document.querySelector('.soc-box'),
      productButton = document.querySelector('#product-button, #design-button');

    if (productButton) {
      footer.classList.add('_product');
      socBox.classList.add('_product');
    }
  }
  mobBasket()

  // Уведомление о добавлении товара
  /*
  const productAdd = () => {
    const
      productAdded = document.querySelector('.product-added'),
      productButtonAll = document.querySelectorAll('.product-button__add');

    productButtonAll.forEach(productButton => {
      if (productButton) {
        productButton.addEventListener('click', () => {
          productButton.textContent = `Перейти в корзину`;
          productAdded.classList.add('_show');
          setTimeout(() => {
            productAdded.classList.remove('_show');
          }, 1000);
        })
      }

    })
  }
  productAdd()
   */

  // Покупаете подарок?
  /*
  const present = () => {
    const presentContainer = document.querySelector('.present');

    if (presentContainer) {
      let
        textareaBox = presentContainer.querySelector('.present-box'),
        textarea = presentContainer.querySelector('.present-textarea');

      presentContainer.addEventListener('click', (e) => {
        let target = e.target;
        if (target.closest('.present-button')) {
          presentContainer.classList.add('_add');
          textareaBox.style.cssText = `
            height: ${textarea.clientHeight}px;
          `
          setTimeout(() => {
            textareaBox.style.cssText = `
            height: auto;
          `
          }, 400);
        }
      })
    }
  }
  present();
   */

  // фиксирование блока в  Вопросы и ответы
  const fixedMenu = () => {
    const basketContainer = document.querySelector('.basket-container');

    if (basketContainer) {

      jQuery(document).ready(function () {
        jQuery('.goods, .design').theiaStickySidebar({
          // Настройки
          additionalMarginTop: 90,
          minWidth: 992
        });
      })

    }
  }
  fixedMenu();

  // фиксирование блока в Блог. Статья.
  const fixedMenu2 = () => {
    const basketContainer = document.querySelector('.article-body');

    if (basketContainer) {

      jQuery(document).ready(function () {
        jQuery('.article-content, .article-sidebar').theiaStickySidebar({
          // Настройки
          additionalMarginTop: 90,
          minWidth: 992
        });
      })

    }
  }
  fixedMenu2();

  // фиксирование блока Уровни начислений
  const fixedMenu3 = () => {
    const basketContainer = document.querySelector('.rewards-container');

    if (basketContainer) {

      jQuery(document).ready(function () {
        jQuery('.rewards-col_level, .rewards-col_rules').theiaStickySidebar({
          // Настройки
          additionalMarginTop: 90,
          minWidth: 992
        });
      })

    }
  }
  fixedMenu3();

  // Select
  const select = () => {
    const selectBlockAll = document.querySelectorAll('.select');

    selectBlockAll.forEach(selectBlock => {
      const
        popular = document.getElementById('popular'),
        header = selectBlock.querySelector('.select-head'),
        input = header.querySelector('.select-head__input'),
        container = selectBlock.querySelector('.select-container'),
        list = selectBlock.querySelector('.select-list'),
        liAll = list.querySelectorAll('li');

      header.addEventListener('click', () => {
        if (!header.classList.contains('_show')) {

          header.classList.add('_show');
          header.classList.add('_label');
          container.style.cssText = `
            height: ${list.clientHeight + 36}px
          `;
          selectBlock.style.cssText = `
            z-index: 5
          `
        } else {
          header.classList.remove('_show');
          if (!header.classList.contains('_label2')) {
            header.classList.remove('_label');
          }
          container.removeAttribute('style');
          setTimeout(() => {
            selectBlock.removeAttribute('style')
          }, 400);
        }
      })

      liAll.forEach(liElem => {
        liElem.addEventListener('click', () => {

          if (popular) {
            popular.innerHTML = liElem.innerHTML.trim();
            header.classList.remove('_show');
            container.removeAttribute('style');
            console.log(23);
          } else {
            input.setAttribute('value', `${liElem.textContent.trim()}`);
            header.classList.remove('_show');
            container.removeAttribute('style')
            header.classList.add('_label2');;
            console.log(12);
          }
        })
      })

      body.addEventListener('click', (e) => {
        let target = e.target;

        if (!target.closest('.select')) {
          header.classList.remove('_show');
          container.removeAttribute('style');
          console.log(45);
          if (!header.classList.contains('_label2')) {
            header.classList.remove('_label');
          }
        }
      })
    })
  }
  select();

  // look
  const lookFunction = () => {
    let lookMainContainer = document.querySelector('.look-container');

    if (lookMainContainer) {
      lookMainContainer.addEventListener('mouseover', (e) => {
        let
          target = e.target;

        if (target.closest('.look-point')) {
          let lookDescriptionPointAll = lookMainContainer.querySelectorAll('.look-description__point');
          let point = target.closest('.look-point').getAttribute('data-id-look');

          lookDescriptionPointAll.forEach(lookDescriptionPoint => {
            let point2 = lookDescriptionPoint.getAttribute('data-id');

            if (point === point2) {
              lookDescriptionPoint.classList.add('_active')
            }

          })
        }
      })
      lookMainContainer.addEventListener('mouseout', (e) => {
        let
          target = e.target;

        if (target.closest('.look-point')) {
          let lookDescriptionPointAll = lookMainContainer.querySelectorAll('.look-description__point');
          let point = target.closest('.look-point').getAttribute('data-id-look');

          lookDescriptionPointAll.forEach(lookDescriptionPoint => {
            let point2 = lookDescriptionPoint.getAttribute('data-id');

            if (point === point2) {
              lookDescriptionPoint.classList.remove('_active')
            }

          })
        }
      })
    }
  }
  lookFunction()

  // Выберите цвет
  const color = () => {
    let calculationColor = document.querySelector('.calculation-color');

    if (calculationColor) {
      let
        text = document.querySelector('.calculation-color__name'),
        colorAll = calculationColor.querySelectorAll('.radio__input');

      colorAll.forEach(elem => {
        elem.addEventListener('input', () => {
          text.textContent = elem.getAttribute('value');
        })
      })
    }
  }
  color();

  // Подсказка
  const hint = () => {
    const block = document.querySelector('.calculation-price');

    if (block) {
      const
        icon = block.querySelector('.calculation-description__icon'),
        text = block.querySelector('.calculation-description__text');

      icon.addEventListener('mouseover', () => {
        text.classList.add('_show')
      })
      icon.addEventListener('mouseout', () => {
        text.classList.remove('_show')
      })
    }
  }
  hint()

  // Прикрепить файл
  const fileLoad = () => {
    const inputFile = document.querySelector('.input-file');

    if (inputFile) {
      $('.input-file input[type=file]').on('change', function () {
        let file = this.files[0];
        $(this).closest('.input-file').find('.input-file__text').html(file.name);
      });
    }
  }
  fileLoad()

  // Появление label
  const labelShow = () => {
    let inputsBlockAll = document.querySelectorAll('.placement-inputs__col, .placement-textarea');

    inputsBlockAll.forEach(inputsBlock => {
      if (inputsBlock) {
        let
          label = inputsBlock.querySelector('.placement-inputs__label, .placement-textarea__label'),
          input = inputsBlock.querySelector('.input, textarea');

        input.addEventListener('click', () => {

          inputsBlockAll.forEach(elem => {
            let
              elemLabel = elem.querySelector('.placement-inputs__label, .placement-textarea__label'),
              elemInput = elem.querySelector('.input, textarea');

            if (elemInput.value.trim() === '') {
              elemLabel.classList.remove('_show');

            }
          })
          label.classList.add('_show');
        })

        body.addEventListener('click', (e) => {
          let target = e.target;

          if (!target.closest('.input') && !target.closest('textarea')) {
            inputsBlockAll.forEach(elem2 => {
              let
                elemLabel2 = elem2.querySelector('.placement-inputs__label, .placement-textarea__label'),
                elemInput2 = elem2.querySelector('.input, textarea');

              if (elemInput2.value.trim() === '') {
                elemLabel2.classList.remove('_show');

              }
            })
          }
          
        })

      }
    })
  }
  labelShow()

  // Использовать бонусы ORENSHAL CLUB 
  const useBonuses = () => {
    const bonusContainer = document.querySelector('.placement-bonus__container');

    if (bonusContainer) {
      const input = document.getElementById('placementBonus');
      let box = document.querySelector('.placement-bonus__box');
      input.addEventListener('change', () => {
        if (input.checked) {
          bonusContainer.style.cssText = `
            height: ${box.clientHeight}px
          `
        } else {
          bonusContainer.removeAttribute('style')
        }
      });
    }
  }
  useBonuses()

  // Год в истории
  const yearsHistory = () => {
    let
      swiperYear = document.querySelector('.swiper-year'),
      swiperHistory = document.querySelector('.swiper-history');

    if (swiperYear && swiperHistory) {
      let
        yearSlideAll = swiperYear.querySelectorAll('.swiper-slide'),
        historySlideAll = swiperHistory.querySelectorAll('.history__slide');

      historySlideAll.forEach((historySlide, historyId) => {
        let
          historyCount = historySlide.querySelector('.swiper-history__count'),
          historyImgAll = historySlide.querySelectorAll('.history-img'),
          historySubyear = historySlide.querySelector('.swiper-history__subyear');

        if (historyCount) {
          historySubyear.textContent = historyCount.textContent;
        }
        historyImgAll.forEach(historyImg => {
          let img = historyImg.querySelector('img');

          img.setAttribute('data-fancybox', `${historyCount.textContent}`)
        })

        yearSlideAll.forEach((yearSlide, yearId) => {
          let yearCount = yearSlide.querySelector('.swiper-year__count');
          if (yearId === historyId) {
            yearCount.textContent = historyCount.textContent;
          }
        })
      })
    }
  }
  yearsHistory()

  // Добавление класса
  const classAdd = () => {
    const beautiful = document.querySelector('.beautiful');

    if (beautiful) {

      let mainBlock = document.querySelector('.main-block');

      mainBlock.classList.add('_other')
    }
  }
  classAdd()

  // Видео в истории
  const videoHistory = () => {
    const videoBlock = document.querySelector('.history-video__block');

    if (videoBlock) {
      let play = videoBlock.querySelector('.history-video__play');

      play.addEventListener('click', () => {
        videoBlock.classList.add('_show')
      })
    }
  }
  videoHistory()

  /////////////// Слайдеры  //////////////////


  const mainSlider = () => {
    const swiper = new Swiper('.main-swiper', {
      speed: 400,
      spaceBetween: 20,
      loop: true,
      navigation: {
        nextEl: '.main-slider__next',
        prevEl: '.main-slider__prev',
      },
      autoplay: {
        delay: 500000,
      },
      breakpoints: {
        320: {
          autoplay: {
            delay: 5000,
          },
        },
        576: {
          autoplay: {
            delay: 500000,
          },
        },

      }
    });
  }
  mainSlider()


  // Бестселлеры
/*
  const bestseller = () => {
    $(document).ready(function () {
      $(".bestseller-link").brazzersCarousel();
    });
  }

  const bestsellerSlider = () => {
    const swiper = new Swiper('.bestseller-swiper', {
      speed: 400,
      slidesPerView: 5,
      spaceBetween: 15,
      loop: true,
      navigation: {
        nextEl: '.bestseller-next',
        prevEl: '.bestseller-prev',
      },
      breakpoints: {
        // when window width is >= 320px
        320: {
          spaceBetween: 10,
          slidesPerView: "auto",
        },
        992: {
          slidesPerView: 4,
          spaceBetween: 10,
        },
        // when window width is >= 480px
        1200: {
          slidesPerView: 4,
        },
        // when window width is >= 640px
        1400: {
          slidesPerView: 5,
        }
      }
    });
  }
  bestsellerSlider()

  if (width > 992) {
    bestseller();
  }
*/

  // Ваши образы
  const collectionsSlider = () => {
    const swiper = new Swiper('.collections-swiper', {
      speed: 400,
      spaceBetween: 15,
      navigation: {
        nextEl: '.collections-next',
        prevEl: '.collections-prev',
      },
      breakpoints: {
        // when window width is >= 320px
        320: {
          spaceBetween: 10,
          slidesPerView: "auto",
        },
        992: {
          slidesPerView: 3,
          spaceBetween: 10,
        },
        // when window width is >= 480px
        1200: {
          slidesPerView: 4,
        },

      }
    });
  }
  collectionsSlider()

  // Quiz final
  const quizSwiper = () => {
    const swiper = new Swiper('.quiz-final__swiper', {
      speed: 400,
      slidesPerView: "auto",
      scrollbar: {
        el: '.quiz-final__scrollbar',
        draggable: true,
      },
      breakpoints: {
        // when window width is >= 320px
        320: {
          spaceBetween: 10,
        },
        992: {
          spaceBetween: 13,
        }
      }
    });
  }
  quizSwiper()

  // Слайдер в карточке товара
  /*
  const cardSlider = () => {
    const swiper = new Swiper('.gallery-thumbs-swiper', {
      direction: 'vertical', // вертикальная прокрутка
      slidesPerView: 5, // показывать по 3 превью
      spaceBetween: 16, // расстояние между слайдами
      navigation: { // задаем кнопки навигации
        nextEl: '.gallery-next', // кнопка Next
        prevEl: '.gallery-prev' // кнопка Prev
      },
      //freeMode: true,
      watchSlidesProgress: true,
      breakpoints: {
        // when window width is >= 320px
        320: {
          direction: 'horizontal',
          slidesPerView: 4,
          spaceBetween: 6,
        },
        576: {
          direction: 'horizontal',
          slidesPerView: 5,
          spaceBetween: 6,
        },
        // when window width is >= 480px
        1200: {
          direction: 'vertical',
          slidesPerView: 5,
          spaceBetween: 16,
        }
      }
    });
    const swiper2 = new Swiper('.gallery-top-swiper', {
      direction: 'horizontal', // вертикальная прокрутка
      slidesPerView: 1, // показывать по 1 изображению
      spaceBetween: 16, // расстояние между слайдами
      thumbs: {
        swiper: swiper,
        autoScrollOffset: 1,
      },
    });

  }
  cardSlider()
   */

  // Слайдер в Подарочный сертификат
  const giftSlider = () => {
    const swiper = new Swiper('.giftCerf-thumbs-swiper', {
      direction: 'vertical', // вертикальная прокрутка
      slidesPerView: 5, // показывать по 3 превью
      spaceBetween: 16, // расстояние между слайдами
      navigation: { // задаем кнопки навигации
        nextEl: '.giftCerf-next', // кнопка Next
        prevEl: '.giftCerf-prev' // кнопка Prev
      },
      //freeMode: true,
      watchSlidesProgress: true,
      breakpoints: {
        // when window width is >= 320px
        320: {
          direction: 'horizontal',
          slidesPerView: 4,
          spaceBetween: 6,
        },
        576: {
          direction: 'horizontal',
          slidesPerView: 5,
          spaceBetween: 6,
        },
        // when window width is >= 480px
        1200: {
          direction: 'vertical',
          slidesPerView: 3,
          spaceBetween: 16,
        }
      }
    });
    const swiper2 = new Swiper('.giftCerf-top-swiper', {
      direction: 'horizontal', // вертикальная прокрутка
      slidesPerView: 1, // показывать по 1 изображению
      spaceBetween: 16, // расстояние между слайдами
      thumbs: {
        swiper: swiper,
        autoScrollOffset: 1,
      },
    });

  }
  giftSlider()

  const cardSlider2 = () => {
    const swiper = new Swiper('.gallery-thumbs-modal', {
      direction: 'vertical', // вертикальная прокрутка
      slidesPerView: 5, // показывать по 3 превью
      spaceBetween: 14, // расстояние между слайдами
      navigation: { // задаем кнопки навигации
        nextEl: '.gallery-modal-next', // кнопка Next
        prevEl: '.gallery-modal-prev' // кнопка Prev
      },
      //freeMode: true,
      watchSlidesProgress: true,
      breakpoints: {
        320: {
          direction: 'horizontal',
          slidesPerView: 4,
          spaceBetween: 6,
        },
        576: {
          direction: 'horizontal',
          slidesPerView: 5,
          spaceBetween: 6,
        },
        // when window width is >= 480px
        992: {
          direction: 'vertical',
          slidesPerView: 5,
          spaceBetween: 14,
        }
      }
    });
    const swiper2 = new Swiper('.gallery-top-modal', {
      direction: 'horizontal', // вертикальная прокрутка
      slidesPerView: 1, // показывать по 1 изображению
      spaceBetween: 16, // расстояние между слайдами
      thumbs: {
        swiper: swiper,
        autoScrollOffset: 1,
      },
    });

  }
  cardSlider2()

  // Платок с индивидуальным дизайном
  const scarf = () => {
    const swiper = new Swiper('.scarf-swiper', {
      speed: 400,
      spaceBetween: 15,
      loop: true,
      navigation: {
        nextEl: '.scarf-next',
        prevEl: '.scarf-prev',
      },
      scrollbar: {
        el: '.scarf-scrollbar',
        draggable: true,
      },
      breakpoints: {
        // when window width is >= 320px
        320: {
          spaceBetween: 10,
          slidesPerView: 1,

        },
        370: {
          spaceBetween: 10,
          slidesPerView: 2,

        },
        768: {
          slidesPerView: 3,
          spaceBetween: 10,
        },
        // when window width is >= 480px
        1200: {
          slidesPerView: 4,
        },

      }
    });
  }
  scarf()

  // Слайдер года
  const yearSlider = () => {
    const swiperYear = new Swiper('.swiper-year', {
      speed: 400,
      spaceBetween: 40,
      slidesPerView: 7,
      freeMode: true,
      rewind: true,
      watchSlidesProgress: true,
      breakpoints: {
        // when window width is >= 320px
        320: {
          slidesPerView: 4,
          spaceBetween: 20
        },
        // when window width is >= 480px
        480: {
          slidesPerView: 4,
          spaceBetween: 20
        },
        // when window width is >= 640px
        992: {
          slidesPerView: 6,
          spaceBetween: 40
        },
        // when window width is >= 640px
        1200: {
          slidesPerView: 6,
          spaceBetween: 40
        }
      }
    });

    const swiperHistory = new Swiper('.swiper-history', {
      autoHeight: true,
      rewind: true,
      allowTouchMove: false,
      thumbs: {
        swiper: swiperYear,
      },
      navigation: {
        nextEl: ".swiper-history-next",
        prevEl: ".swiper-history-prev",
      },
    });
  }
  yearSlider()

  // Слайдер в карточке года
  const cardYearSlider = (swiperOne, swiperTwo) => {
    let swiper = new Swiper(swiperOne, {
      loop: true,
      spaceBetween: 16,
      slidesPerView: 5,
      freeMode: true,
      watchSlidesProgress: true,
      breakpoints: {
        // when window width is >= 320px
        320: {
          spaceBetween: 9
        },
        // when window width is >= 480px
        576: {
          spaceBetween: 14
        },
        // when window width is >= 640px
        992: {
          spaceBetween: 16
        },
        // when window width is >= 640px
        1200: {
          spaceBetween: 16
        }
      }
    });

    let swiper2 = new Swiper(swiperTwo, {
      loop: true,
      spaceBetween: 10,
      thumbs: {
        swiper: swiper,
      },
    });
  }
  let
    swiperHistoryColAll = document.querySelectorAll('.swiper-history__col');

  swiperHistoryColAll.forEach((swiperHistoryCol, id) => {
    if (swiperHistoryCol) {
      let
        swiperOne = swiperHistoryCol.querySelector('.mySwiper'),
        swiperTwo = swiperHistoryCol.querySelector('.mySwiper2');

      if (swiperOne && swiperTwo) {
        swiperOne.classList.add(`one-${id}`);
        swiperTwo.classList.add(`two-${id}`);
        setTimeout(() => {
          cardYearSlider(`.one-${id}`, `.two-${id}`);

        }, 100);
      }

    }
  })
});

