var MugShot = {
  id: 'theMainImage',

  id2: 'MugShotDiv',

  id3: 'theImage',

  imageId: false,

  postAction: false,

  lai: false,

  submitBtn: '',

  tagList: '',

  img: '',

  map: '',

  offset: {},

  mugs: [],

  cfi: -1,

  active: false,

  selecting: false,

  init: (function (f, imageId, action) {
    this.refreshImgData();
    this.makeWrapper();
    this.imageId = imageId;
    this.drawMugShots(f);
    this.createSubmitButton();
    this.assignEventListeners();
    this.postAction = action;
    this.tagList = document.getElementById('mugshot-tags');
    document.getElementById('mugshot-tags').remove();
    document.getElementById(this.id2).append(this.tagList);
    this.imageId = imageId;
    this.postAction = action;
  }),

  frame: (function () {
    if (this.active === false) {
      this.refreshImgData()
      this.active = true;
      this.img.draggable = false;
      this.map = this.img.useMap;
      this.img.useMap = '#';
      this.img.style.cursor = 'crosshair';
      this.img.addEventListener('mousedown', beginCapture);
      window.addEventListener('keydown', reverseCapture);
    }
  }),

  makeWrapper: (function () {
    var w = document.createElement('div');
    w.id = this.id2;
    w.style.left = this.offset.left - this.poffset.left + 'px';
    w.style.width = this.offset.width + 'px';
    w.style.height = this.offset.height + 'px';
    document.getElementById('theImage').append(w)
  }),

  updateWrapper: (function () {
    var w = document.getElementById(this.id2)
    w.style.left = this.offset.left - this.poffset.left + 'px';
    w.style.top = '0px';
    w.style.width = this.offset.width + 'px';
    w.style.height = this.offset.height + 'px';
  }),

  assignEventListeners: (function () {
    try {
      document.getElementById('navbar-contextual').addEventListener('click', refreshOnResize);
    } catch (err) {
      if (err.name == 'TypeError') {
        try {
          document.querySelector('nav').addEventListener('click', refreshOnResize);
        } catch(err) {
          if (err.name == 'TypeError') {
            document.getElementById('imageToolBar').addEventListener('click', refreshOnResize);
          } else {
            console.log(err);
          }
        }
      } else {
        console.log(err);
      }
    }
    window.addEventListener('resize', refreshOnResize);
  }),

  toggleElementSet: (function (i, x) {
    if (x == 'off') {
      if (this.mugs[i].frame.el.classList.contains('mugshot-active')) {
        this.mugs[i].frame.el.classList.toggle('mugshot-active');
      }

      if (this.mugs[i].name.el.classList.contains('mugshot-active')) {
        this.mugs[i].name.el.classList.toggle('mugshot-active');
      }

      if (this.mugs[i].remove.el.classList.contains('mugshot-active')) {
        this.mugs[i].remove.el.classList.toggle('mugshot-active');
      }
    } else if (x == 'on') {
      if (!this.mugs[i].frame.el.classList.contains('mugshot-active')) {
        this.mugs[i].frame.el.classList.toggle('mugshot-active');
      }

      if (!this.mugs[i].name.el.classList.contains('mugshot-active')) {
        this.mugs[i].name.el.classList.toggle('mugshot-active');
      }

      if (!this.mugs[i].remove.el.classList.contains('mugshot-active')) {
        this.mugs[i].remove.el.classList.toggle('mugshot-active');
      }
    }
  }),

  toggleSubmitBtn: (function (x) {
    if (x == 'off') {
      if (this.submitBtn.classList.contains('mugshot-active')) {
        this.submitBtn.classList.toggle('mugshot-active');
      }
    } else if (x == 'on') {
      if (!this.submitBtn.classList.contains('mugshot-active')) {
        this.submitBtn.classList.toggle('mugshot-active');
      }
    }
  }),

  drawMugShots: (function (frames) {
    for (var f in frames) {
      if (frames.hasOwnProperty(f)) {
        var left = frames[f].lft;
        var top = frames[f].top;
        var height = frames[f].height;
        var width = frames[f].width;
        var imgW = frames[f].image_width;
        var imgH = frames[f].image_height;
        var name = frames[f].name;
        var tag = frames[f].tag_id;
        this.createBoundingBox(left, top, height, width, imgW, imgH, name, tag);
        this.createTextBox();
        this.createDeleteButton();
        this.mugs[this.cfi].frame.el.ondblclick = updateBoundingBox;
        this.mugs[this.cfi].active = false;
        this.mugs[this.cfi].frame.el.classList.toggle('mugshot-mousetrap');
      }
    }

    this.refreshCapture();
  }),

  createBoundingBox: (function (left, top, height, width, imgW, imgH, name, tag) {
    this.cfi += 1;
    var id = 'frame_' + this.cfi;
    var box = document.createElement('div');
    box.title = id;
    box.id = id;
    box.className = 'mugshot-frame mugshot-mousetrap';
    box.style.top = top + 'px';
    box.style.left = left + 'px';
    box.style.height = height + 'px';
    box.style.width = width + 'px';
    document.getElementById(this.id2).append(box);

    this.mugs[this.cfi] = {
        imageId: this.imageId,
        active: true,
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
          tagId: (tag) ? tag : -1,
          removeThis: 0,
        },
        name: {
          el: '',
          id: 'name_' + this.cfi,
          left: 0,
          top: 0,
        },
        remove: {
          el: '',
          id: 'remove_' + this.cfi,
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
    var el = document.getElementById(MugShot.lai);
    var i = parseInt(el.id.replace('name_', ''));
    el.value = e.innerHTML;
    e.parentNode.style.display = 'none';
    MugShot.toggleElementSet(i, 'off');
    MugShot.mugs[i].frame.name = el.value;
    MugShot.mugs[i].frame.el.title = el.value;
    MugShot.mugs[i].active = false;
  }),

  refreshBoundingBoxPosition: (function (left, top, height, width, index) {
    index = (index !== false) ? index : this.cfi;
    this.mugs[index].frame.el.style.top = top + 'px';
    this.mugs[index].frame.el.style.left = left + 'px';
    this.mugs[index].frame.el.style.height = height + 'px';
    this.mugs[index].frame.el.style.width = width + 'px';
  }),

  refreshTagListPosition: (function (index) {
    if (this.lai !== false) {
      index = (index === true) ? parseInt(this.lai.replace('name_', '')) : index;
      var o = this.mugs[index].name.el.getBoundingClientRect();
      var t = o.height + o.top - MugShot.offset.top;
      this.tagList.style.left = this.mugs[index].name.el.style.left;
      this.tagList.style.width = o.width + 'px';
      this.tagList.style.top = t + 'px';
    }
  }),

  refreshCapture: (function () {
    if (this.cfi !== -1) {

      var len = this.mugs.length;

      for (var i = 0; i < len; i++) {
        var scaleX = this.img.width / this.mugs[i].frame.imageWidth;
        var scaleY = this.img.height / this.mugs[i].frame.imageHeight;

        if (scaleX != 1) {
          var mug = this.mugs[i].frame;
          var left = Math.floor(parseInt(mug.left) * scaleX);
          var top = Math.floor(parseInt(mug.top) * scaleY);
          var width = Math.floor(parseInt(mug.width) * scaleX);
          var height = Math.floor(parseInt(mug.height) * scaleY);
          this.refreshBoundingBoxPosition(left, top, height, width, i);
          this.mugs[i].name.el.style.left = this.mugs[i].frame.el.style.left;
          this.mugs[i].name.el.style.top = top + height + 'px';
        }
      }
    }
  }),

  refreshImgData: (function () {
    this.img = document.getElementById(this.id);
    this.offset = this.img.getBoundingClientRect();
    this.poffset = document.getElementById(this.id3).getBoundingClientRect();
  }),

  createTextBox: (function () {
    var mug = this.mugs[this.cfi].frame;
    var name = document.createElement('input');
    var tagName = mug.name;
    name.addEventListener('keyup', doneWithText);
    name.id = this.mugs[this.cfi].name.id;
    name.value = (tagName) ? tagName : '';
    name.className = 'mugshot-textbox';
    name.style.top = parseInt(mug.top) + parseInt(mug.height) + 'px';
    name.style.left = mug.el.style.left;
    document.getElementById(this.id2).append(name);
    this.mugs[this.cfi].name.el = name;
    this.mugs[this.cfi].frame.el.title = name.value;
  }),

  createDeleteButton: (function () {
    var btn = document.createElement('span');
    btn.className = 'mugshot-delete mugshot-icon mugshot-icon-cross';
    btn.title = 'Delete Tag';
    btn.id = 'remove_' + this.cfi;
    btn.onclick = this.deleteMugShot.bind(this);
    this.mugs[this.cfi].remove.el = btn;
    this.mugs[this.cfi].frame.el.append(btn);
  }),

  createSubmitButton: (function () {
    var btn = document.createElement('button');
    btn.className = 'mugshot-done-button';
    btn.id = 'mugShotSubmit';
    btn.style.left = '0px';
    btn.style.top = '0px';
    btn.onclick = this.submitMugShots.bind(this);
    this.submitBtn = btn;
    document.getElementById(this.id2).append(btn);
  }),

  submitMugShots: (function () {
    var data = [];

    this.toggleSubmitBtn('off');
    this.tagList.style.display = 'none';

    if (this.mugs.length != 0) {

      data.imageId = this.mugs[0].imageId;

      for (var i = 0; i < this.mugs.length; i++) {
        if (this.mugs[i].frame.tag != '') {
          data['mug_' + i] = this.mugs[i].frame;
        }

        this.mugs[i].active = false;
        this.toggleElementSet(i, 'off');
      }

      this.sendToServer(data);
    }
  }),

  urlEncodeData: (function (obj, prefix) {
    var str = [];
    var p;
    var k;

    for (p in obj) {
      if (obj.hasOwnProperty(p)) {
        k = prefix ? prefix + '[' + p + ']' : p, v = obj[p];
        str.push((v !== null && typeof v === 'object') ?
          this.urlEncodeData(v, k) :
          encodeURIComponent(k) + '=' + encodeURIComponent(v));
      }
    }

    return str.join('&');
  }),

  sendToServer: (function (data) {
    this.xhr = new XMLHttpRequest();
    this.xhr.onload = this.parseFromServer;
    this.xhr.open('POST', this.postAction, true);
    this.xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
    this.xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    this.xhr.responseType = 'json';
    this.xhr.send(this.urlEncodeData(data));
  }),

  parseFromServer: (function (e) {
    if (e.target.status == 200) {

      MugShot.resetMugShot();

      if (e.target.response) {
        console.log(JSON.parse(e.target.response.result));
      }
    } else {
      console.log('Error: Unsuccessfully updated Database');
    }
  }),

  resetMugShot: (function () {
    this.img.useMap = this.map;
    this.img.draggable = true;
    this.img.style.cursor = 'auto';
    this.img.removeEventListener('mousedown', beginCapture);
    document.removeEventListener('keydown', reverseCapture);
  }),

  deleteMugShot: (function (e) {
    var index = parseInt(e.target.id.replace('remove_', ''));
    this.mugs[index].frame.el.remove();
    this.mugs[index].name.el.remove();
    this.mugs[index].remove.el.remove();
    this.mugs[index].frame.removeThis = 1;
    this.toggleElementSet(index, 'off');
    this.toggleSubmitBtn('on');
    this.tagList.style.display = 'none';
  }),
};

/*
 * Event Listener functions.
 * Listed here for easier removal
 */
function beginCapture(e) {
  if (e.which == 1) {
    MugShot.selecting = true;
    MugShot.img.addEventListener('mousemove', updateCapture);
    MugShot.img.addEventListener('mouseup', haltCapture);
    MugShot.createBoundingBox(e.pageX - MugShot.offset.left, e.pageY - MugShot.offset.top, 5, 5);
    MugShot.mugs[MugShot.cfi].frame.el.classList.toggle('mugshot-active');
    MugShot.toggleSubmitBtn('on');
  }
}

function updateCapture(e) {
  if (MugShot.selecting) {
    var pos = MugShot.setBoundingBoxPosition(e.pageX - MugShot.offset.left, e.pageY - MugShot.offset.top);
    MugShot.refreshBoundingBoxPosition(pos[0], pos[1], pos[2], pos[3], false);
  }
}

function haltCapture(e) {
  MugShot.selecting = false;
  MugShot.img.removeEventListener('mousemove', updateCapture, false);
  MugShot.img.removeEventListener('mouseup', haltCapture, false);
  MugShot.mugs[MugShot.cfi].frame.el.ondblclick = updateBoundingBox;
  MugShot.mugs[MugShot.cfi].frame.el.classList.toggle('mugshot-mousetrap');
  var pos = MugShot.setBoundingBoxPosition(e.pageX - MugShot.offset.left, e.pageY - MugShot.offset.top);
  MugShot.mugs[MugShot.cfi].frame.left = pos[0];
  MugShot.mugs[MugShot.cfi].frame.top = pos[1];
  MugShot.mugs[MugShot.cfi].frame.height = pos[2];
  MugShot.mugs[MugShot.cfi].frame.width = pos[3];
  MugShot.mugs[MugShot.cfi].name.top = pos[1] + pos[2];
  MugShot.mugs[MugShot.cfi].name.left = pos[0];
  MugShot.createTextBox();
  MugShot.createDeleteButton();
  MugShot.toggleElementSet(MugShot.cfi, 'on');
  MugShot.mugs[MugShot.cfi].name.el.focus();
}

function updateBoundingBox(e) {
  var index = parseInt(e.target.id.replace('frame_', ''));

  if (!MugShot.mugs[index].active) {
    MugShot.toggleSubmitBtn('on');
    MugShot.toggleElementSet(index, 'on');
    MugShot.mugs[index].active = true;
  }
}

function reverseCapture(e) {
  if (e.keyCode == 90 && e.ctrlKey && MugShot.cfi > -1) {
    MugShot.deleteMugShot();
  }
}

function doneWithText(e) {
  var index = parseInt(e.target.id.replace('name_', ''));
  MugShot.mugs[index].frame.name = e.target.value;
  MugShot.mugs[index].frame.el.title = e.target.value;

  if (e.keyCode == 13) {
    MugShot.toggleElementSet(index, 'off');
    var vis = MugShot.tagList.querySelectorAll('.mugshot-tag-list-show');
    var v = (vis.length == 1) ? vis[0].innerHTML : e.target.value;
    MugShot.mugs[index].frame.name = v;
    MugShot.mugs[index].frame.el.title = v;
    MugShot.mugs[index].active = false;
    MugShot.tagList.style.display = 'none';
  } else {
    var filter = e.target.value.toUpperCase();
    var list = MugShot.tagList.querySelectorAll('li');
    var i = 0;
    var j = 0;

    MugShot.lai = e.target.id;
    MugShot.refreshTagListPosition(index);

    for (i = 0; i < list.length; i++) {
      if (list[i].innerHTML.toUpperCase().indexOf(filter) > -1) {
        list[i].className = 'mugshot-tag-list-show';
        j += 1;
      } else {
        list[i].className = '';
      }
    }

    MugShot.tagList.style.display = (j < 10 && j != 0) ? 'block' : 'none';
  }
}

function refreshOnResize(e) {

  if(e.type == 'click') {
    setTimeout(function () {
      MugShot.refreshImgData();
      MugShot.updateWrapper();
      MugShot.refreshCapture();
    }, 250);
  } else {
      MugShot.refreshImgData();
      MugShot.updateWrapper();
      MugShot.refreshCapture();
  }
}
