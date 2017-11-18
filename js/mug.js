var MugShot = {
  id: 'theMainImage',

  img: '',

  map: '',

  offset: {},

  mugs: [],

  cfi: -1,

  init: (function (f) {
    this.img = document.getElementById(this.id);
    this.offset = this.img.getBoundingClientRect();

    if (f !== -1) {
      var derivatives = document.querySelectorAll('[id^="derivative"]');
      [].forEach.call(derivatives, refreshOnResize);
      this.drawMugShots(f);
    }
  }),

  drawMugShots: (function (frames) {
    for (var f in frames) {
      if (frames.hasOwnProperty(f)) {
        var left = frames[f].left;
        var top = frames[f].top;
        var height = frames[f].height;
        var width = frames[f].width;
        var imgW = frames[f].image_width;
        var imgH = frames[f].image_height;
        var name = frames[f].name;
        var tag = frames[f].tag_id;
        this.createBoundingBox(left, top, height, width, imgW, imgH, name, tag);
      }
    }

    this.refreshCapture();
    window.addEventListener('resize', refreshOnResize);
    window.addEventListener('scroll', refreshOnResize);
  }),

  createBoundingBox: (function (left, top, height, width, imgW, imgH, name, tag) {
    this.cfi += 1;
    var id = 'frame_' + this.cfi;
    var box = document.createElement('div');
    box.title = name;
    box.id = id;
    box.className = 'mugshot-frame mugshot-mousetrap';
    box.style.top = top + 'px';
    box.style.left = left + 'px';
    box.style.height = height + 'px';
    box.style.width = width + 'px';
    this.img.parentNode.append(box);
    this.mugs[this.cfi] = {
        frame: {
          el: box,
          id: id,
          name: (name) ? name : '',
          top: top,
          left: left,
          height: height,
          width: width,
          imageWidth: (imgW) ? imgW : this.img.width,
          imageHeight: (imgH) ? imgH : this.img.height,
        },
      };
  }),

  setBoundingBoxPosition: (function (x, y) {
    var t = this.mugs[this.cfi].frame.top;
    var l = this.mugs[this.cfi].frame.left;
    var top = (t < y) ? t : y;
    var left = (l < x) ? l : x;
    var height = Math.abs(y - t);
    var width = Math.abs(x - l);
    return [left, top, height, width];
  }),

  setText: (function (e) {
    var el = document.getElementById(MugShot.lastActiveInput);
    var i = parseInt(el.id.replace('name_', ''));
    el.value = e.innerHTML;
    e.parentNode.style.display = 'none';
    MugShot.toggleElementSet(i, 'off');
    MugShot.mugs[i].frame.name = el.value;
    MugShot.mugs[i].frame.el.title = el.value;
    MugShot.mugs[i].active = false;
  }),

  refreshBoundingBoxPosition: (function (left, top, height, width) {
    this.mugs[this.cfi].frame.el.style.top = top + 'px';
    this.mugs[this.cfi].frame.el.style.left = left  + 'px';
    this.mugs[this.cfi].frame.el.style.height = height + 'px';
    this.mugs[this.cfi].frame.el.style.width = width + 'px';
  }),

  refreshCapture: (function () {
    if (this.cfi !== -1) {

      var len = this.mugs.length;

      for (var i = 0; i < len; i++) {
        var name = document.getElementById('name_' + i);
        var frame = document.getElementById('frame_' + i);
        var scaleX = this.img.width / this.mugs[i].frame.imageWidth;
        var scaleY = this.img.height / this.mugs[i].frame.imageHeight;

        var left = parseInt(this.img.offsetLeft + this.mugs[i].frame.left * scaleX);
        var top = parseInt(this.img.offsetTop + this.mugs[i].frame.top * scaleY);
        var width = parseInt(this.mugs[i].frame.width * scaleX);
        var height = parseInt(this.mugs[i].frame.height * scaleY);

        this.mugs[i].frame.el.style.left = left + 'px';
        this.mugs[i].frame.el.style.top = top + 'px';
        this.mugs[i].frame.el.style.width = width + 'px';
        this.mugs[i].frame.el.style.height = height + 'px';
      }
    }
  }),
};

/*
 * Event Listener functions.
 * Listed here for easier removal
 */
function refreshOnResize(e) {
  if (e.type == 'resize') {
    MugShot.refreshCapture();
  } else if (e.type == 'scroll') {
    MugShot.init(-1);
    MugShot.refreshCapture();
  }else {
    MugShot.img.onload = function () {
      MugShot.init(-1);
      MugShot.refreshCapture();
    };
  }
}
