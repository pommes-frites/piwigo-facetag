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

  /**
   * Places mugshot elements on the page in their
   * corresponding positions
   * @param  object frames defined_mugshots
   * @return void
   */
  drawMugShots: (function (frames) {
    for (var frameIndex in frames) {
      if (frames.hasOwnProperty(frameIndex)) {
        this.createBoundingBox(frames[frameIndex]);
      }
    }

    this.refreshCapture();
    window.addEventListener('resize', refreshOnResize);
    window.addEventListener('scroll', refreshOnResize);
  }),

  /**
   * Create a frame for the mugshot
   * @param  object frame frame object from defined_mugshots
   * @return void
   */
  createBoundingBox: (function (frame) {
    this.cfi += 1;
    var id = 'frame_' + this.cfi;
    var box = document.createElement('div');
    box.className = 'mugshot-frame mugshot-mousetrap';
    box.id = id;
    box.style.height = frame.height + 'px';
    box.style.left = frame.lft + 'px';
    box.style.top = frame.top + 'px';
    box.style.width = frame.width + 'px';
    box.title = frame.name;
    if(frame.name) {
      var nameEl = document.createElement('a');
      nameEl.className = 'mugshot-frame-name';
      nameEl.href = frame.tag_url;
      nameEl.innerHTML = frame.name;
      box.append(nameEl);
    }
    this.img.parentNode.append(box);
    this.mugs[this.cfi] = {
      frame: {
        el: box,
        height: frame.height,
        id: id,
        imageHeight: (frame.image_height) ? frame.image_height : this.img.height,
        imageWidth: (frame.image_width) ? frame.image_width : this.img.width,
        left: frame.lft,
        name: (frame.name) ? frame.name : '',
        top: frame.top,
        width: frame.width,
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
