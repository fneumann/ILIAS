/* */

il.Accordion = {

  duration: 100,

  data: {},

  initAll() {
    $;
  },

  /**
	 * Add accordion element
	 *
	 * Options:
	 * id: id,
	 * toggle_class: toggle_class,
	 * toggle_act_class: toggle_act_class,
	 * content_class: content_class,
	 * width: width,
	 * height: height,
	 * orientation: orientation,
	 * behaviour: behaviour,
	 * save_url: save_url,
	 * active_head_class: active_head_class,
	 * int_id: int_id,
	 * initial_opened: initial opened accordion tabs (nr, separated by ;)
	 * multi: multi
	 * show_all_element: ID of HTML element that triggers show all
	 * hide_all_element: ID of HTML element that triggers hide all
	 */
  add(options) {
    options.animating = false;
    options.clicked_acc = null;
    options.last_opened_acc = null;

    if (typeof options.reset_width === 'undefined') {
      options.reset_width = false;
    }

    if (typeof options.show_all_element === 'undefined') {
      options.show_all_element = null;
    }

    if (typeof options.hide_all_element === 'undefined') {
      options.hide_all_element = null;
    }

    if ((typeof options.initial_opened !== 'undefined') && options.initial_opened && options.initial_opened.length > 0) {
      options.initial_opened = options.initial_opened.split(';');
    } else {
      options.initial_opened = [];
    }

    il.Accordion.data[options.id] = options;
    il.Accordion.init(options.id);
  },

  init(id) {
    let t; let el; let next_el; let acc_el; const a = il.Accordion.data[id]; let apt; let
      sp;

    if (a.behaviour == 'Carousel') {
      apt = (a.auto_anim_wait > 100)
        ? a.auto_anim_wait
        : 5000;
      sp = (a.random_start)
        ? Math.floor(Math.random() * $(`#${id}`).children().length)
        : 0;

      $(`#${id}`).owlCarousel({
        items: 1, autoplay: true, loop: true, dots: false, autoplayTimeout: apt, startPosition: sp,
      });
      return;
    }

    // open the inital opened tabs
    if (a.initial_opened.length > 0) {
      for (let i = 0; i < a.initial_opened.length; i++) {
        acc_el = $(`#${id} div.${a.content_class}:eq(${parseInt(a.initial_opened[i]) - 1})`);
        acc_el.removeClass('ilAccHideContent');
        il.Accordion.addActiveHeadClass(id, acc_el[0]);
        a.last_opened_acc = acc_el.get(0);
      }
    } else if (a.behaviour == 'FirstOpen') {
      acc_el = $(`#${id} div.${a.content_class}:eq(0)`);
      acc_el.removeClass('ilAccHideContent');
      il.Accordion.addActiveHeadClass(id, acc_el[0]);
      a.last_opened_acc = acc_el.get(0);
    }

    // register click handler (if not all opened is forced)
    if (a.behaviour != 'ForceAllOpen') {
      $(`#${id}`).children().children(`.${a.toggle_class}`).each(function () {
        t = $(this);

        t.find('a').click((e) => {
          e.stopPropagation(); // enable links inside of accordion header
        });

        t.on('click', { id, el: t }, il.Accordion.clickHandler);
        t.on('keypress', function (e) {
          if (e.which === 13 || e.which === 32) {
            $(this).find("div[role='button']").trigger('click');
          }
        });
      });
    }

    if (a.show_all_element) {
      $(`#${a.show_all_element}`).prop('onclick', '').on('click', { id }, il.Accordion.showAll);
    }
    if (a.hide_all_element) {
      $(`#${a.hide_all_element}`).prop('onclick', '').on('click', { id }, il.Accordion.hideAll);
    }
  },

  isOpened(el) {
    return !$(el).hasClass('ilAccHideContent');
  },

  getAllOpenedNr(id) {
    let opened_str = '';
    let lim = '';
    let t = 1;
    const a = il.Accordion.data[id];

    $(`#${id}`).children().children(`.${a.content_class}`).each(function () {
      if (!$(this).hasClass('ilAccHideContent')) {
        opened_str = `${opened_str + lim}${t}`;
        lim = ';';
      }
      t++;
    });

    return opened_str;
  },

  getAllNr(id) {
    let all_str = '';
    let lim = '';
    let t = 1;
    const a = il.Accordion.data[id];

    $(`#${id}`).children().children(`.${a.content_class}`).each(() => {
      all_str = `${all_str + lim}${t}`;
      lim = ';';
      t++;
    });
    return all_str;
  },

  clickHandler(e) {
    let a; let el; let
      id;
    // console.log("clicked");
    id = e.data.id;
    a = il.Accordion.data[id];
    el = e.data.el;
    e.preventDefault();

    if (a.animating) {
      return false;
    }

    a.clicked_acc = el.next()[0];

    if (il.Accordion.isOpened(a.clicked_acc)) {
      il.Accordion.deactivate(id, el);
    } else {
      il.Accordion.handleAccordion(id, el);
    }
    return false;
  },

  initByIntId(int_id) {
    for (const a in il.Accordion.data) {
      if (a.int_id == int_id) {
        il.Accordion.init(a.id);
      }
    }
  },

  addActiveHeadClass(id, acc_el) {
    const a = il.Accordion.data[id];

    if (a.active_head_class && a.active_head_class != '' && acc_el) {
      const b = $(acc_el.parentNode).children(':first').children(':first');
      b.addClass(a.active_head_class);
      b.attr('aria-expanded', true);
    }
  },

  removeActiveHeadClass(id, acc_el) {
    const a = il.Accordion.data[id];

    if (a.active_head_class && a.active_head_class != '' && acc_el) {
      const b = $(acc_el.parentNode).children(':first').children(':first');
      b.removeClass(a.active_head_class);
      b.attr('aria-expanded', false);
    }
  },

  showAll(e) {
    let options; const
      { id } = e.data;
    const a = il.Accordion.data[id];
    e.preventDefault();
    e.stopPropagation();
    if (a.multi) {
      // console.log("deactivate");
      a.animating = true;

      $(`#${id}`).children().children(`.${a.content_class}`).each(function () {
        t = $(this);
        if (t.hasClass('ilAccHideContent')) {
          il.Accordion.addActiveHeadClass(id, this);

          // fade in the accordion (currentAccordion)
          options = il.Accordion.prepareShow(a, t);
          $(t).animate(options, il.Accordion.duration, () => {
            $(t).css('height', 'auto');

            // set the currently shown accordion
            a.last_opened_acc = t;
            il.Accordion.rerenderContent(t);

            a.animating = false;
          });
        }
      });

      il.Accordion.saveAllAsOpenedTabs(a, id);
    }

    return false;
  },

  preparePrint() {
    for (var id in il.Accordion.data) {
      var a = il.Accordion.data[id];

      $(`#${id}`).children().children(`.${a.content_class}`).each(function () {
        t = $(this);
        if (t.hasClass('ilAccHideContent')) {
          il.Accordion.addActiveHeadClass(id, this);

          // fade in the accordion (currentAccordion)
          options = il.Accordion.prepareShow(a, t);
          $(t).animate(options, 0, () => {
            $(t).css('height', 'auto');

            // set the currently shown accordion
            a.last_opened_acc = t;
            il.Accordion.rerenderContent(t);

            a.animating = false;
          });
        }
      });

      il.Accordion.saveAllAsOpenedTabs(a, id);
    }
  },

  hideAll(e) {
    const { id } = e.data;
    const a = il.Accordion.data[id];
    e.preventDefault();
    e.stopPropagation();
    if (a.multi) {
      //			console.log("hide all");

      // console.log("deactivate");
      a.animating = true;

      $(`#${id}`).children().children(`.${a.content_class}`).each(function () {
        t = $(this);
        if (!t.hasClass('ilAccHideContent')) {
          il.Accordion.removeActiveHeadClass(id, t);

          if (a.orientation == 'vertical') {
            options = { height: 0 };
          } else {
            options = { width: 0 };
          }

          t.animate(options, il.Accordion.duration, function () {
            //						console.log("adding hide to");
            //						console.log(this);
            $(this).addClass('ilAccHideContent');
            a.last_opened_acc = null;
            a.animating = false;
          });
        }
      });

      const save_url = il.Accordion.getSaveUrl(a);
      if (save_url != '') {
        il.Util.sendAjaxGetRequestToUrl(`${save_url}&act=clear&tab_nr=`, {}, {}, null);
      }
    }
    return false;
  },

  deactivate(id, el) {
    let options; let act; const
      a = il.Accordion.data[id];

    // console.log("deactivate");
    a.animating = true;

    // $(el).css("display", "block");

    il.Accordion.removeActiveHeadClass(id, a.clicked_acc);

    if (a.orientation == 'vertical') {
      options = { height: 0 };
    } else {
      options = { width: 0 };
    }

    $(a.clicked_acc).animate(options, il.Accordion.duration, () => {
      $(a.clicked_acc).addClass('ilAccHideContent');
      a.last_opened_acc = null;
      a.animating = false;
      const save_url = il.Accordion.getSaveUrl(a);
      if (save_url != '') {
        act = (a.multi)
          ? '&act=rem'
          : '&act=clear';
        tab_nr = il.Accordion.getTabNr(a.clicked_acc);
        il.Util.sendAjaxGetRequestToUrl(`${save_url + act}&tab_nr=${tab_nr}`, {}, {}, null);
      }
    });
  },

  getTabNr(acc_el) {
    let tab_nr = 1;
    let cel = acc_el.parentNode;
    while (cel = cel.previousSibling) {
      if (cel.nodeName.toUpperCase() == 'DIV') {
        tab_nr++;
      }
    }
    return tab_nr;
  },

  prepareShow(a, acc_el) {
    let options;
    if (a.orientation == 'vertical') {
      $(acc_el).css('position', 'relative')
        .css('left', '-10000px')
        .css('display', 'block');

      $(acc_el).removeClass('ilAccHideContent');

      const nh = a.height
        ? a.height
        : $(acc_el).prop('scrollHeight');

      $(acc_el).css('height', '0px')
        .css('position', '')
        .css('display', '')
        .css('left', '');

      options = {
        height: a.height
          ? a.height
          : $(acc_el).prop('scrollHeight'),
      };
    } else {
      $(acc_el).css('width', '0px');
      $(acc_el).removeClass('ilAccHideContent');
      options = {
        width: (a.width
          ? a.width
          : $(acc_el).prop('scrollWidth')),
      };
    }
    return options;
  },

  saveAllAsOpenedTabs(a, id) {
    const save_url = il.Accordion.getSaveUrl(a);
    if (save_url !== '') {
      tab_nr = il.Accordion.getAllNr(id);
      il.Util.sendAjaxGetRequestToUrl(`${save_url}&act=set&tab_nr=${tab_nr}`, {}, {}, null);
    }
  },

  getSaveUrl(a) {
    if (typeof a.save_url !== 'undefined' && a.save_url != '') {
      let { save_url } = a;
      if (!save_url.includes('accordion_id=')) {
        save_url = `${save_url}&accordion_id=${a.id}`;
      }
      return save_url;
    }
    return '';
  },

  saveOpenedTabs(a, id) {
    const save_url = il.Accordion.getSaveUrl(a);
    if (save_url != '') {
      if (a.multi) {
        tab_nr = il.Accordion.getAllOpenedNr(id);
      } else {
        tab_nr = il.Accordion.getTabNr(a.last_opened_acc);
      }
      act = '&act=set';
      il.Util.sendAjaxGetRequestToUrl(`${save_url + act}&tab_nr=${tab_nr}`, {}, {}, null);
    }
  },

  handleAccordion(id, el) {
    // console.log("handle");
    let options; let options2; let last_acc; let tab_nr; const
      a = il.Accordion.data[id];
    a.animating = true;

    // add active class to opened accordion
    if (a.active_head_class && a.active_head_class != '') {
      if (a.last_opened_acc && !a.multi) {
        il.Accordion.removeActiveHeadClass(id, a.last_opened_acc);
      }
      il.Accordion.addActiveHeadClass(id, a.clicked_acc);
    }

    // fade in the new accordion (currentAccordion)
    options = il.Accordion.prepareShow(a, a.clicked_acc);
    il.Accordion.afterStartOpening(a.clicked_acc);

    $(a.clicked_acc).animate(options, il.Accordion.duration, () => {
      $(a.clicked_acc).css('height', 'auto');
      if (a.reset_width) {
        $(a.clicked_acc).css('width', a.width);
      }

      // set the currently shown accordion
      a.last_opened_acc = a.clicked_acc;

      il.Accordion.afterOpening(a.clicked_acc);

      il.Accordion.saveOpenedTabs(a, id);

      a.animating = false;
    });

    // fade out the currently shown accordion (last_opened_acc)
    if ((last_acc = a.last_opened_acc) && !a.multi) {
      if (a.orientation == 'vertical') {
        options2 = { height: 0 };
      } else {
        options2 = { width: 0 };
      }
      $(last_acc).animate(options2, il.Accordion.duration, () => {
        $(last_acc).addClass('ilAccHideContent');
      });
    }
  },

  afterOpening(acc_el) {
    $(acc_el).trigger('il.accordion.opened', [acc_el]);
    il.Accordion.rerenderContent(acc_el);
  },

  afterStartOpening(acc_el) {
    $(acc_el).trigger('il.accordion.start-opening', [acc_el]);
  },

  rerenderContent(acc_el) {
    // rerender mathjax
    il.Util.renderMathJax([acc_el[0]]);

    // rerender google maps
    if (typeof ilMapRerender !== 'undefined') {
      ilMapRerender(acc_el);
    }

    // see https://mantis.ilias.de/view.php?id=25301
    // see https://mantis.ilias.de/view.php?id=34329
    // previously we removed/re-added the player
    // in ilCOPagePres which led to #34329
    window.dispatchEvent(new Event('resize'));
  },

};

(function ($, il) {
  $(() => {
    il.Accordion.initAll();
  });
}($, il));
