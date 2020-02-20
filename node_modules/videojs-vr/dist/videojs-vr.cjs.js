/*! @name videojs-vr @version 1.7.1 @license Apache-2.0 */
'use strict';

function _interopDefault (ex) { return (ex && (typeof ex === 'object') && 'default' in ex) ? ex['default'] : ex; }

var _assertThisInitialized = _interopDefault(require('@babel/runtime/helpers/assertThisInitialized'));
var _inheritsLoose = _interopDefault(require('@babel/runtime/helpers/inheritsLoose'));
var window = _interopDefault(require('global/window'));
var document = _interopDefault(require('global/document'));
var WebVRPolyfill = _interopDefault(require('webvr-polyfill'));
var videojs = _interopDefault(require('video.js'));
var THREE = require('three');
var VRControls = _interopDefault(require('three/examples/js/controls/VRControls.js'));
var VREffect = _interopDefault(require('three/examples/js/effects/VREffect.js'));
var OrbitControls = _interopDefault(require('three/examples/js/controls/OrbitControls.js'));
var DeviceOrientationControls = _interopDefault(require('three/examples/js/controls/DeviceOrientationControls.js'));

var version = "1.7.1";

/**
 * Convert a quaternion to an angle
 *
 * Taken from https://stackoverflow.com/a/35448946
 * Thanks P. Ellul
 */

function Quat2Angle(x, y, z, w) {
  var test = x * y + z * w; // singularity at north pole

  if (test > 0.499) {
    var _yaw = 2 * Math.atan2(x, w);

    var _pitch = Math.PI / 2;

    var _roll = 0;
    return new THREE.Vector3(_pitch, _roll, _yaw);
  } // singularity at south pole


  if (test < -0.499) {
    var _yaw2 = -2 * Math.atan2(x, w);

    var _pitch2 = -Math.PI / 2;

    var _roll2 = 0;
    return new THREE.Vector3(_pitch2, _roll2, _yaw2);
  }

  var sqx = x * x;
  var sqy = y * y;
  var sqz = z * z;
  var yaw = Math.atan2(2 * y * w - 2 * x * z, 1 - 2 * sqy - 2 * sqz);
  var pitch = Math.asin(2 * test);
  var roll = Math.atan2(2 * x * w - 2 * y * z, 1 - 2 * sqx - 2 * sqz);
  return new THREE.Vector3(pitch, roll, yaw);
}

var OrbitOrientationControls =
/*#__PURE__*/
function () {
  function OrbitOrientationControls(options) {
    this.object = options.camera;
    this.domElement = options.canvas;
    this.orbit = new OrbitControls(this.object, this.domElement);
    this.speed = 0.5;
    this.orbit.target.set(0, 0, -1);
    this.orbit.enableZoom = false;
    this.orbit.enablePan = false;
    this.orbit.rotateSpeed = -this.speed; // if orientation is supported

    if (options.orientation) {
      this.orientation = new DeviceOrientationControls(this.object);
    } // if projection is not full view
    // limit the rotation angle in order to not display back half view


    if (options.halfView) {
      this.orbit.minAzimuthAngle = -Math.PI / 4;
      this.orbit.maxAzimuthAngle = Math.PI / 4;
    }
  }

  var _proto = OrbitOrientationControls.prototype;

  _proto.update = function update() {
    // orientation updates the camera using quaternions and
    // orbit updates the camera using angles. They are incompatible
    // and one update overrides the other. So before
    // orbit overrides orientation we convert our quaternion changes to
    // an angle change. Then save the angle into orbit so that
    // it will take those into account when it updates the camera and overrides
    // our changes
    if (this.orientation) {
      this.orientation.update();
      var quat = this.orientation.object.quaternion;
      var currentAngle = Quat2Angle(quat.x, quat.y, quat.z, quat.w); // we also have to store the last angle since quaternions are b

      if (typeof this.lastAngle_ === 'undefined') {
        this.lastAngle_ = currentAngle;
      }

      this.orbit.rotateLeft((this.lastAngle_.z - currentAngle.z) * (1 + this.speed));
      this.orbit.rotateUp((this.lastAngle_.y - currentAngle.y) * (1 + this.speed));
      this.lastAngle_ = currentAngle;
    }

    this.orbit.update();
  };

  _proto.dispose = function dispose() {
    this.orbit.dispose();

    if (this.orientation) {
      this.orientation.dispose();
    }
  };

  return OrbitOrientationControls;
}();

var corsSupport = function () {
  var video = document.createElement('video');
  video.crossOrigin = 'anonymous';
  return video.hasAttribute('crossorigin');
}();
var validProjections = ['360', '360_LR', '360_TB', '360_CUBE', 'EAC', 'EAC_LR', 'NONE', 'AUTO', 'Sphere', 'Cube', 'equirectangular', '180'];
var getInternalProjectionName = function getInternalProjectionName(projection) {
  if (!projection) {
    return;
  }

  projection = projection.toString().trim();

  if (/sphere/i.test(projection)) {
    return '360';
  }

  if (/cube/i.test(projection)) {
    return '360_CUBE';
  }

  if (/equirectangular/i.test(projection)) {
    return '360';
  }

  for (var i = 0; i < validProjections.length; i++) {
    if (new RegExp('^' + validProjections[i] + '$', 'i').test(projection)) {
      return validProjections[i];
    }
  }
};

/**
 * This class reacts to interactions with the canvas and
 * triggers appropriate functionality on the player. Right now
 * it does two things:
 *
 * 1. A `mousedown`/`touchstart` followed by `touchend`/`mouseup` without any
 *    `touchmove` or `mousemove` toggles play/pause on the player
 * 2. Only moving on/clicking the control bar or toggling play/pause should
 *    show the control bar. Moving around the scene in the canvas should not.
 */

var CanvasPlayerControls =
/*#__PURE__*/
function (_videojs$EventTarget) {
  _inheritsLoose(CanvasPlayerControls, _videojs$EventTarget);

  function CanvasPlayerControls(player, canvas) {
    var _this;

    _this = _videojs$EventTarget.call(this) || this;
    _this.player = player;
    _this.canvas = canvas;
    _this.onMoveEnd = videojs.bind(_assertThisInitialized(_this), _this.onMoveEnd);
    _this.onMoveStart = videojs.bind(_assertThisInitialized(_this), _this.onMoveStart);
    _this.onMove = videojs.bind(_assertThisInitialized(_this), _this.onMove);
    _this.onControlBarMove = videojs.bind(_assertThisInitialized(_this), _this.onControlBarMove);

    _this.player.controlBar.on(['mousedown', 'mousemove', 'mouseup', 'touchstart', 'touchmove', 'touchend'], _this.onControlBarMove); // we have to override these here because
    // video.js listens for user activity on the video element
    // and makes the user active when the mouse moves.
    // We don't want that for 3d videos


    _this.oldReportUserActivity = _this.player.reportUserActivity;

    _this.player.reportUserActivity = function () {}; // canvas movements


    _this.canvas.addEventListener('mousedown', _this.onMoveStart);

    _this.canvas.addEventListener('touchstart', _this.onMoveStart);

    _this.canvas.addEventListener('mousemove', _this.onMove);

    _this.canvas.addEventListener('touchmove', _this.onMove);

    _this.canvas.addEventListener('mouseup', _this.onMoveEnd);

    _this.canvas.addEventListener('touchend', _this.onMoveEnd);

    _this.shouldTogglePlay = false;
    return _this;
  }

  var _proto = CanvasPlayerControls.prototype;

  _proto.togglePlay = function togglePlay() {
    if (this.player.paused()) {
      this.player.play();
    } else {
      this.player.pause();
    }
  };

  _proto.onMoveStart = function onMoveStart(e) {
    // if the player does not have a controlbar or
    // the move was a mouse click but not left click do not
    // toggle play.
    if (!this.player.controls() || e.type === 'mousedown' && !videojs.dom.isSingleLeftClick(e)) {
      this.shouldTogglePlay = false;
      return;
    }

    this.shouldTogglePlay = true;
    this.touchMoveCount_ = 0;
  };

  _proto.onMoveEnd = function onMoveEnd(e) {
    // We want to have the same behavior in VR360 Player and standar player.
    // in touchend we want to know if was a touch click, for a click we show the bar,
    // otherwise continue with the mouse logic.
    //
    // Maximum movement allowed during a touch event to still be considered a tap
    // Other popular libs use anywhere from 2 (hammer.js) to 15,
    // so 10 seems like a nice, round number.
    if (e.type === 'touchend' && this.touchMoveCount_ < 10) {
      if (this.player.userActive() === false) {
        this.player.userActive(true);
        return;
      }

      this.player.userActive(false);
      return;
    }

    if (!this.shouldTogglePlay) {
      return;
    } // We want the same behavior in Desktop for VR360  and standar player


    if (e.type == 'mouseup') {
      this.togglePlay();
    }
  };

  _proto.onMove = function onMove(e) {
    // Increase touchMoveCount_ since Android detects 1 - 6 touches when user click normaly
    this.touchMoveCount_++;
    this.shouldTogglePlay = false;
  };

  _proto.onControlBarMove = function onControlBarMove(e) {
    this.player.userActive(true);
  };

  _proto.dispose = function dispose() {
    this.canvas.removeEventListener('mousedown', this.onMoveStart);
    this.canvas.removeEventListener('touchstart', this.onMoveStart);
    this.canvas.removeEventListener('mousemove', this.onMove);
    this.canvas.removeEventListener('touchmove', this.onMove);
    this.canvas.removeEventListener('mouseup', this.onMoveEnd);
    this.canvas.removeEventListener('touchend', this.onMoveEnd);
    this.player.controlBar.off(['mousedown', 'mousemove', 'mouseup', 'touchstart', 'touchmove', 'touchend'], this.onControlBarMove);
    this.player.reportUserActivity = this.oldReportUserActivity;
  };

  return CanvasPlayerControls;
}(videojs.EventTarget);

/**
 * This class manages ambisonic decoding and binaural rendering via Omnitone library.
 */

var OmnitoneController =
/*#__PURE__*/
function (_videojs$EventTarget) {
  _inheritsLoose(OmnitoneController, _videojs$EventTarget);

  /**
   * Omnitone controller class.
   *
   * @class
   * @param {AudioContext} audioContext - associated AudioContext.
   * @param {Omnitone library} omnitone - Omnitone library element.
   * @param {HTMLVideoElement} video - vidoe tag element.
   * @param {Object} options - omnitone options.
   */
  function OmnitoneController(audioContext, omnitone, video, options) {
    var _this;

    _this = _videojs$EventTarget.call(this) || this;
    var settings = videojs.mergeOptions({
      // Safari uses the different AAC decoder than FFMPEG. The channel order is
      // The default 4ch AAC channel layout for FFMPEG AAC channel ordering.
      channelMap: videojs.browser.IS_SAFARI ? [2, 0, 1, 3] : [0, 1, 2, 3],
      ambisonicOrder: 1
    }, options);
    _this.videoElementSource = audioContext.createMediaElementSource(video);
    _this.foaRenderer = omnitone.createFOARenderer(audioContext, settings);

    _this.foaRenderer.initialize().then(function () {
      if (audioContext.state === 'suspended') {
        _this.trigger({
          type: 'audiocontext-suspended'
        });
      }

      _this.videoElementSource.connect(_this.foaRenderer.input);

      _this.foaRenderer.output.connect(audioContext.destination);

      _this.initialized = true;

      _this.trigger({
        type: 'omnitone-ready'
      });
    }, function (error) {
      videojs.log.warn("videojs-vr: Omnitone initializes failed with the following error: " + error + ")");
    });

    return _this;
  }
  /**
   * Updates the rotation of the Omnitone decoder based on three.js camera matrix.
   *
   * @param {Camera} camera Three.js camera object
   */


  var _proto = OmnitoneController.prototype;

  _proto.update = function update(camera) {
    if (!this.initialized) {
      return;
    }

    this.foaRenderer.setRotationMatrixFromCamera(camera.matrix);
  }
  /**
   * Destroys the controller and does any necessary cleanup.
   */
  ;

  _proto.dispose = function dispose() {
    this.initialized = false;
    this.foaRenderer.setRenderingMode('bypass');
    this.foaRenderer = null;
  };

  return OmnitoneController;
}(videojs.EventTarget);

var Button = videojs.getComponent('Button');

var CardboardButton =
/*#__PURE__*/
function (_Button) {
  _inheritsLoose(CardboardButton, _Button);

  function CardboardButton(player, options) {
    var _this;

    _this = _Button.call(this, player, options) || this;
    _this.handleVrDisplayActivate_ = videojs.bind(_assertThisInitialized(_this), _this.handleVrDisplayActivate_);
    _this.handleVrDisplayDeactivate_ = videojs.bind(_assertThisInitialized(_this), _this.handleVrDisplayDeactivate_);
    _this.handleVrDisplayPresentChange_ = videojs.bind(_assertThisInitialized(_this), _this.handleVrDisplayPresentChange_);
    _this.handleOrientationChange_ = videojs.bind(_assertThisInitialized(_this), _this.handleOrientationChange_);
    window.addEventListener('orientationchange', _this.handleOrientationChange_);
    window.addEventListener('vrdisplayactivate', _this.handleVrDisplayActivate_);
    window.addEventListener('vrdisplaydeactivate', _this.handleVrDisplayDeactivate_); // vrdisplaypresentchange does not fire activate or deactivate
    // and happens when hitting the back button during cardboard mode
    // so we need to make sure we stay in the correct state by
    // listening to it and checking if we are presenting it or not

    window.addEventListener('vrdisplaypresentchange', _this.handleVrDisplayPresentChange_); // we cannot show the cardboard button in fullscreen on
    // android as it breaks the controls, and makes it impossible
    // to exit cardboard mode

    if (videojs.browser.IS_ANDROID) {
      _this.on(player, 'fullscreenchange', function () {
        if (player.isFullscreen()) {
          _this.hide();
        } else {
          _this.show();
        }
      });
    }

    return _this;
  }

  var _proto = CardboardButton.prototype;

  _proto.buildCSSClass = function buildCSSClass() {
    return "vjs-button-vr " + _Button.prototype.buildCSSClass.call(this);
  };

  _proto.handleVrDisplayPresentChange_ = function handleVrDisplayPresentChange_() {
    if (!this.player_.vr().vrDisplay.isPresenting && this.active_) {
      this.handleVrDisplayDeactivate_();
    }

    if (this.player_.vr().vrDisplay.isPresenting && !this.active_) {
      this.handleVrDisplayActivate_();
    }
  };

  _proto.handleOrientationChange_ = function handleOrientationChange_() {
    if (this.active_ && videojs.browser.IS_IOS) {
      this.changeSize_();
    }
  };

  _proto.changeSize_ = function changeSize_() {
    this.player_.width(window.innerWidth);
    this.player_.height(window.innerHeight);
    window.dispatchEvent(new window.Event('resize'));
  };

  _proto.handleVrDisplayActivate_ = function handleVrDisplayActivate_() {
    // we mimic fullscreen on IOS
    if (videojs.browser.IS_IOS) {
      this.oldWidth_ = this.player_.currentWidth();
      this.oldHeight_ = this.player_.currentHeight();
      this.player_.enterFullWindow();
      this.changeSize_();
    }

    this.active_ = true;
  };

  _proto.handleVrDisplayDeactivate_ = function handleVrDisplayDeactivate_() {
    // un-mimic fullscreen on iOS
    if (videojs.browser.IS_IOS) {
      if (this.oldWidth_) {
        this.player_.width(this.oldWidth_);
      }

      if (this.oldHeight_) {
        this.player_.height(this.oldHeight_);
      }

      this.player_.exitFullWindow();
    }

    this.active_ = false;
  };

  _proto.handleClick = function handleClick(event) {
    // if cardboard mode display is not active, activate it
    // otherwise deactivate it
    if (!this.active_) {
      // This starts playback mode when the cardboard button
      // is clicked on Andriod. We need to do this as the controls
      // disappear
      if (!this.player_.hasStarted() && videojs.browser.IS_ANDROID) {
        this.player_.play();
      }

      window.dispatchEvent(new window.Event('vrdisplayactivate'));
    } else {
      window.dispatchEvent(new window.Event('vrdisplaydeactivate'));
    }
  };

  _proto.dispose = function dispose() {
    _Button.prototype.dispose.call(this);

    window.removeEventListener('vrdisplayactivate', this.handleVrDisplayActivate_);
    window.removeEventListener('vrdisplaydeactivate', this.handleVrDisplayDeactivate_);
    window.removeEventListener('vrdisplaypresentchange', this.handleVrDisplayPresentChange_);
  };

  return CardboardButton;
}(Button);

videojs.registerComponent('CardboardButton', CardboardButton);

var BigPlayButton = videojs.getComponent('BigPlayButton');

var BigVrPlayButton =
/*#__PURE__*/
function (_BigPlayButton) {
  _inheritsLoose(BigVrPlayButton, _BigPlayButton);

  function BigVrPlayButton() {
    return _BigPlayButton.apply(this, arguments) || this;
  }

  var _proto = BigVrPlayButton.prototype;

  _proto.buildCSSClass = function buildCSSClass() {
    return "vjs-big-vr-play-button " + _BigPlayButton.prototype.buildCSSClass.call(this);
  };

  return BigVrPlayButton;
}(BigPlayButton);

videojs.registerComponent('BigVrPlayButton', BigVrPlayButton);

var defaults = {
  debug: false,
  omnitone: false,
  forceCardboard: false,
  omnitoneOptions: {},
  projection: 'AUTO'
};
var errors = {
  'web-vr-out-of-date': {
    headline: '360 is out of date',
    type: '360_OUT_OF_DATE',
    message: "Your browser supports 360 but not the latest version. See <a href='http://webvr.info'>http://webvr.info</a> for more info."
  },
  'web-vr-not-supported': {
    headline: '360 not supported on this device',
    type: '360_NOT_SUPPORTED',
    message: "Your browser does not support 360. See <a href='http://webvr.info'>http://webvr.info</a> for assistance."
  },
  'web-vr-hls-cors-not-supported': {
    headline: '360 HLS video not supported on this device',
    type: '360_NOT_SUPPORTED',
    message: "Your browser/device does not support HLS 360 video. See <a href='http://webvr.info'>http://webvr.info</a> for assistance."
  }
};
var Plugin = videojs.getPlugin('plugin');
var Component = videojs.getComponent('Component');

var VR =
/*#__PURE__*/
function (_Plugin) {
  _inheritsLoose(VR, _Plugin);

  function VR(player, options) {
    var _this;

    var settings = videojs.mergeOptions(defaults, options);
    _this = _Plugin.call(this, player, settings) || this;
    _this.options_ = settings;
    _this.player_ = player;
    _this.bigPlayButtonIndex_ = player.children().indexOf(player.getChild('BigPlayButton')) || 0; // custom videojs-errors integration boolean

    _this.videojsErrorsSupport_ = !!videojs.errors;

    if (_this.videojsErrorsSupport_) {
      player.errors({
        errors: errors
      });
    } // IE 11 does not support enough webgl to be supported
    // older safari does not support cors, so it wont work


    if (videojs.browser.IE_VERSION || !corsSupport) {
      // if a player triggers error before 'loadstart' is fired
      // video.js will reset the error overlay
      _this.player_.on('loadstart', function () {
        _this.triggerError_({
          code: 'web-vr-not-supported',
          dismiss: false
        });
      });

      return _assertThisInitialized(_this);
    }

    _this.polyfill_ = new WebVRPolyfill({
      // do not show rotate instructions
      ROTATE_INSTRUCTIONS_DISABLED: true
    });
    _this.polyfill_ = new WebVRPolyfill();
    _this.handleVrDisplayActivate_ = videojs.bind(_assertThisInitialized(_this), _this.handleVrDisplayActivate_);
    _this.handleVrDisplayDeactivate_ = videojs.bind(_assertThisInitialized(_this), _this.handleVrDisplayDeactivate_);
    _this.handleResize_ = videojs.bind(_assertThisInitialized(_this), _this.handleResize_);
    _this.animate_ = videojs.bind(_assertThisInitialized(_this), _this.animate_);

    _this.setProjection(_this.options_.projection); // any time the video element is recycled for ads
    // we have to reset the vr state and re-init after ad


    _this.on(player, 'adstart', function () {
      return player.setTimeout(function () {
        // if the video element was recycled for this ad
        if (!player.ads || !player.ads.videoElementRecycled()) {
          _this.log('video element not recycled for this ad, no need to reset');

          return;
        }

        _this.log('video element recycled for this ad, reseting');

        _this.reset();

        _this.one(player, 'playing', _this.init);
      });
    }, 1);

    _this.on(player, 'loadedmetadata', _this.init);

    return _this;
  }

  var _proto = VR.prototype;

  _proto.changeProjection_ = function changeProjection_(projection) {
    var _this2 = this;

    projection = getInternalProjectionName(projection); // don't change to an invalid projection

    if (!projection) {
      projection = 'NONE';
    }

    var position = {
      x: 0,
      y: 0,
      z: 0
    };

    if (this.scene) {
      this.scene.remove(this.movieScreen);
    }

    if (projection === 'AUTO') {
      // mediainfo cannot be set to auto or we would infinite loop here
      // each source should know wether they are 360 or not, if using AUTO
      if (this.player_.mediainfo && this.player_.mediainfo.projection && this.player_.mediainfo.projection !== 'AUTO') {
        var autoProjection = getInternalProjectionName(this.player_.mediainfo.projection);
        return this.changeProjection_(autoProjection);
      }

      return this.changeProjection_('NONE');
    } else if (projection === '360') {
      this.movieGeometry = new THREE.SphereBufferGeometry(256, 32, 32);
      this.movieMaterial = new THREE.MeshBasicMaterial({
        map: this.videoTexture,
        overdraw: true,
        side: THREE.BackSide
      });
      this.movieScreen = new THREE.Mesh(this.movieGeometry, this.movieMaterial);
      this.movieScreen.position.set(position.x, position.y, position.z);
      this.movieScreen.scale.x = -1;
      this.movieScreen.quaternion.setFromAxisAngle({
        x: 0,
        y: 1,
        z: 0
      }, -Math.PI / 2);
      this.scene.add(this.movieScreen);
    } else if (projection === '360_LR' || projection === '360_TB') {
      // Left eye view
      var geometry = new THREE.SphereGeometry(256, 32, 32);
      var uvs = geometry.faceVertexUvs[0];

      for (var i = 0; i < uvs.length; i++) {
        for (var j = 0; j < 3; j++) {
          if (projection === '360_LR') {
            uvs[i][j].x *= 0.5;
          } else {
            uvs[i][j].y *= 0.5;
            uvs[i][j].y += 0.5;
          }
        }
      }

      this.movieGeometry = new THREE.BufferGeometry().fromGeometry(geometry);
      this.movieMaterial = new THREE.MeshBasicMaterial({
        map: this.videoTexture,
        overdraw: true,
        side: THREE.BackSide
      });
      this.movieScreen = new THREE.Mesh(this.movieGeometry, this.movieMaterial);
      this.movieScreen.scale.x = -1;
      this.movieScreen.quaternion.setFromAxisAngle({
        x: 0,
        y: 1,
        z: 0
      }, -Math.PI / 2); // display in left eye only

      this.movieScreen.layers.set(1);
      this.scene.add(this.movieScreen); // Right eye view

      geometry = new THREE.SphereGeometry(256, 32, 32);
      uvs = geometry.faceVertexUvs[0];

      for (var _i = 0; _i < uvs.length; _i++) {
        for (var _j = 0; _j < 3; _j++) {
          if (projection === '360_LR') {
            uvs[_i][_j].x *= 0.5;
            uvs[_i][_j].x += 0.5;
          } else {
            uvs[_i][_j].y *= 0.5;
          }
        }
      }

      this.movieGeometry = new THREE.BufferGeometry().fromGeometry(geometry);
      this.movieMaterial = new THREE.MeshBasicMaterial({
        map: this.videoTexture,
        overdraw: true,
        side: THREE.BackSide
      });
      this.movieScreen = new THREE.Mesh(this.movieGeometry, this.movieMaterial);
      this.movieScreen.scale.x = -1;
      this.movieScreen.quaternion.setFromAxisAngle({
        x: 0,
        y: 1,
        z: 0
      }, -Math.PI / 2); // display in right eye only

      this.movieScreen.layers.set(2);
      this.scene.add(this.movieScreen);
    } else if (projection === '360_CUBE') {
      this.movieGeometry = new THREE.BoxGeometry(256, 256, 256);
      this.movieMaterial = new THREE.MeshBasicMaterial({
        map: this.videoTexture,
        overdraw: true,
        side: THREE.BackSide
      });
      var left = [new THREE.Vector2(0, 0.5), new THREE.Vector2(0.333, 0.5), new THREE.Vector2(0.333, 1), new THREE.Vector2(0, 1)];
      var right = [new THREE.Vector2(0.333, 0.5), new THREE.Vector2(0.666, 0.5), new THREE.Vector2(0.666, 1), new THREE.Vector2(0.333, 1)];
      var top = [new THREE.Vector2(0.666, 0.5), new THREE.Vector2(1, 0.5), new THREE.Vector2(1, 1), new THREE.Vector2(0.666, 1)];
      var bottom = [new THREE.Vector2(0, 0), new THREE.Vector2(0.333, 0), new THREE.Vector2(0.333, 0.5), new THREE.Vector2(0, 0.5)];
      var front = [new THREE.Vector2(0.333, 0), new THREE.Vector2(0.666, 0), new THREE.Vector2(0.666, 0.5), new THREE.Vector2(0.333, 0.5)];
      var back = [new THREE.Vector2(0.666, 0), new THREE.Vector2(1, 0), new THREE.Vector2(1, 0.5), new THREE.Vector2(0.666, 0.5)];
      this.movieGeometry.faceVertexUvs[0] = [];
      this.movieGeometry.faceVertexUvs[0][0] = [right[2], right[1], right[3]];
      this.movieGeometry.faceVertexUvs[0][1] = [right[1], right[0], right[3]];
      this.movieGeometry.faceVertexUvs[0][2] = [left[2], left[1], left[3]];
      this.movieGeometry.faceVertexUvs[0][3] = [left[1], left[0], left[3]];
      this.movieGeometry.faceVertexUvs[0][4] = [top[2], top[1], top[3]];
      this.movieGeometry.faceVertexUvs[0][5] = [top[1], top[0], top[3]];
      this.movieGeometry.faceVertexUvs[0][6] = [bottom[2], bottom[1], bottom[3]];
      this.movieGeometry.faceVertexUvs[0][7] = [bottom[1], bottom[0], bottom[3]];
      this.movieGeometry.faceVertexUvs[0][8] = [front[2], front[1], front[3]];
      this.movieGeometry.faceVertexUvs[0][9] = [front[1], front[0], front[3]];
      this.movieGeometry.faceVertexUvs[0][10] = [back[2], back[1], back[3]];
      this.movieGeometry.faceVertexUvs[0][11] = [back[1], back[0], back[3]];
      this.movieScreen = new THREE.Mesh(this.movieGeometry, this.movieMaterial);
      this.movieScreen.position.set(position.x, position.y, position.z);
      this.movieScreen.rotation.y = -Math.PI;
      this.scene.add(this.movieScreen);
    } else if (projection === '180') {
      var _geometry = new THREE.SphereGeometry(256, 32, 32, Math.PI, Math.PI); // Left eye view


      _geometry.scale(-1, 1, 1);

      var _uvs = _geometry.faceVertexUvs[0];

      for (var _i2 = 0; _i2 < _uvs.length; _i2++) {
        for (var _j2 = 0; _j2 < 3; _j2++) {
          _uvs[_i2][_j2].x *= 0.5;
        }
      }

      this.movieGeometry = new THREE.BufferGeometry().fromGeometry(_geometry);
      this.movieMaterial = new THREE.MeshBasicMaterial({
        map: this.videoTexture,
        overdraw: true
      });
      this.movieScreen = new THREE.Mesh(this.movieGeometry, this.movieMaterial); // display in left eye only

      this.movieScreen.layers.set(1);
      this.scene.add(this.movieScreen); // Right eye view

      _geometry = new THREE.SphereGeometry(256, 32, 32, Math.PI, Math.PI);

      _geometry.scale(-1, 1, 1);

      _uvs = _geometry.faceVertexUvs[0];

      for (var _i3 = 0; _i3 < _uvs.length; _i3++) {
        for (var _j3 = 0; _j3 < 3; _j3++) {
          _uvs[_i3][_j3].x *= 0.5;
          _uvs[_i3][_j3].x += 0.5;
        }
      }

      this.movieGeometry = new THREE.BufferGeometry().fromGeometry(_geometry);
      this.movieMaterial = new THREE.MeshBasicMaterial({
        map: this.videoTexture,
        overdraw: true
      });
      this.movieScreen = new THREE.Mesh(this.movieGeometry, this.movieMaterial); // display in right eye only

      this.movieScreen.layers.set(2);
      this.scene.add(this.movieScreen);
    } else if (projection === 'EAC' || projection === 'EAC_LR') {
      var makeScreen = function makeScreen(mapMatrix, scaleMatrix) {
        // "Continuity correction?": because of discontinuous faces and aliasing,
        // we truncate the 2-pixel-wide strips on all discontinuous edges,
        var contCorrect = 2;
        _this2.movieGeometry = new THREE.BoxGeometry(256, 256, 256);
        _this2.movieMaterial = new THREE.ShaderMaterial({
          overdraw: true,
          side: THREE.BackSide,
          uniforms: {
            mapped: {
              value: _this2.videoTexture
            },
            mapMatrix: {
              value: mapMatrix
            },
            contCorrect: {
              value: contCorrect
            },
            faceWH: {
              value: new THREE.Vector2(1 / 3, 1 / 2).applyMatrix3(scaleMatrix)
            },
            vidWH: {
              value: new THREE.Vector2(_this2.videoTexture.image.videoWidth, _this2.videoTexture.image.videoHeight).applyMatrix3(scaleMatrix)
            }
          },
          vertexShader: "\nvarying vec2 vUv;\nuniform mat3 mapMatrix;\n\nvoid main() {\n  vUv = (mapMatrix * vec3(uv, 1.)).xy;\n  gl_Position = projectionMatrix * modelViewMatrix * vec4(position, 1.);\n}",
          fragmentShader: "\nvarying vec2 vUv;\nuniform sampler2D mapped;\nuniform vec2 faceWH;\nuniform vec2 vidWH;\nuniform float contCorrect;\n\nconst float PI = 3.1415926535897932384626433832795;\n\nvoid main() {\n  vec2 corner = vUv - mod(vUv, faceWH) + vec2(0, contCorrect / vidWH.y);\n\n  vec2 faceWHadj = faceWH - vec2(0, contCorrect * 2. / vidWH.y);\n\n  vec2 p = (vUv - corner) / faceWHadj - .5;\n  vec2 q = 2. / PI * atan(2. * p) + .5;\n\n  vec2 eUv = corner + q * faceWHadj;\n\n  gl_FragColor = texture2D(mapped, eUv);\n}"
        });
        var right = [new THREE.Vector2(0, 1 / 2), new THREE.Vector2(1 / 3, 1 / 2), new THREE.Vector2(1 / 3, 1), new THREE.Vector2(0, 1)];
        var front = [new THREE.Vector2(1 / 3, 1 / 2), new THREE.Vector2(2 / 3, 1 / 2), new THREE.Vector2(2 / 3, 1), new THREE.Vector2(1 / 3, 1)];
        var left = [new THREE.Vector2(2 / 3, 1 / 2), new THREE.Vector2(1, 1 / 2), new THREE.Vector2(1, 1), new THREE.Vector2(2 / 3, 1)];
        var bottom = [new THREE.Vector2(1 / 3, 0), new THREE.Vector2(1 / 3, 1 / 2), new THREE.Vector2(0, 1 / 2), new THREE.Vector2(0, 0)];
        var back = [new THREE.Vector2(1 / 3, 1 / 2), new THREE.Vector2(1 / 3, 0), new THREE.Vector2(2 / 3, 0), new THREE.Vector2(2 / 3, 1 / 2)];
        var top = [new THREE.Vector2(1, 0), new THREE.Vector2(1, 1 / 2), new THREE.Vector2(2 / 3, 1 / 2), new THREE.Vector2(2 / 3, 0)];

        for (var _i4 = 0, _arr = [right, front, left, bottom, back, top]; _i4 < _arr.length; _i4++) {
          var face = _arr[_i4];
          var height = _this2.videoTexture.image.videoHeight;
          var lowY = 1;
          var highY = 0;

          for (var _iterator = face, _isArray = Array.isArray(_iterator), _i5 = 0, _iterator = _isArray ? _iterator : _iterator[Symbol.iterator]();;) {
            var _ref;

            if (_isArray) {
              if (_i5 >= _iterator.length) break;
              _ref = _iterator[_i5++];
            } else {
              _i5 = _iterator.next();
              if (_i5.done) break;
              _ref = _i5.value;
            }

            var vector = _ref;

            if (vector.y < lowY) {
              lowY = vector.y;
            }

            if (vector.y > highY) {
              highY = vector.y;
            }
          }

          for (var _iterator2 = face, _isArray2 = Array.isArray(_iterator2), _i6 = 0, _iterator2 = _isArray2 ? _iterator2 : _iterator2[Symbol.iterator]();;) {
            var _ref2;

            if (_isArray2) {
              if (_i6 >= _iterator2.length) break;
              _ref2 = _iterator2[_i6++];
            } else {
              _i6 = _iterator2.next();
              if (_i6.done) break;
              _ref2 = _i6.value;
            }

            var _vector = _ref2;

            if (Math.abs(_vector.y - lowY) < Number.EPSILON) {
              _vector.y += contCorrect / height;
            }

            if (Math.abs(_vector.y - highY) < Number.EPSILON) {
              _vector.y -= contCorrect / height;
            }

            _vector.x = _vector.x / height * (height - contCorrect * 2) + contCorrect / height;
          }
        }

        _this2.movieGeometry.faceVertexUvs[0] = [];
        _this2.movieGeometry.faceVertexUvs[0][0] = [right[2], right[1], right[3]];
        _this2.movieGeometry.faceVertexUvs[0][1] = [right[1], right[0], right[3]];
        _this2.movieGeometry.faceVertexUvs[0][2] = [left[2], left[1], left[3]];
        _this2.movieGeometry.faceVertexUvs[0][3] = [left[1], left[0], left[3]];
        _this2.movieGeometry.faceVertexUvs[0][4] = [top[2], top[1], top[3]];
        _this2.movieGeometry.faceVertexUvs[0][5] = [top[1], top[0], top[3]];
        _this2.movieGeometry.faceVertexUvs[0][6] = [bottom[2], bottom[1], bottom[3]];
        _this2.movieGeometry.faceVertexUvs[0][7] = [bottom[1], bottom[0], bottom[3]];
        _this2.movieGeometry.faceVertexUvs[0][8] = [front[2], front[1], front[3]];
        _this2.movieGeometry.faceVertexUvs[0][9] = [front[1], front[0], front[3]];
        _this2.movieGeometry.faceVertexUvs[0][10] = [back[2], back[1], back[3]];
        _this2.movieGeometry.faceVertexUvs[0][11] = [back[1], back[0], back[3]];
        _this2.movieScreen = new THREE.Mesh(_this2.movieGeometry, _this2.movieMaterial);

        _this2.movieScreen.position.set(position.x, position.y, position.z);

        _this2.movieScreen.rotation.y = -Math.PI;
        return _this2.movieScreen;
      };

      if (projection === 'EAC') {
        this.scene.add(makeScreen(new THREE.Matrix3(), new THREE.Matrix3()));
      } else {
        var scaleMatrix = new THREE.Matrix3().set(0, 0.5, 0, 1, 0, 0, 0, 0, 1);
        makeScreen(new THREE.Matrix3().set(0, -0.5, 0.5, 1, 0, 0, 0, 0, 1), scaleMatrix); // display in left eye only

        this.movieScreen.layers.set(1);
        this.scene.add(this.movieScreen);
        makeScreen(new THREE.Matrix3().set(0, -0.5, 1, 1, 0, 0, 0, 0, 1), scaleMatrix); // display in right eye only

        this.movieScreen.layers.set(2);
        this.scene.add(this.movieScreen);
      }
    }

    this.currentProjection_ = projection;
  };

  _proto.triggerError_ = function triggerError_(errorObj) {
    // if we have videojs-errors use it
    if (this.videojsErrorsSupport_) {
      this.player_.error(errorObj); // if we don't have videojs-errors just use a normal player error
    } else {
      // strip any html content from the error message
      // as it is not supported outside of videojs-errors
      var div = document.createElement('div');
      div.innerHTML = errors[errorObj.code].message;
      var message = div.textContent || div.innerText || '';
      this.player_.error({
        code: errorObj.code,
        message: message
      });
    }
  };

  _proto.log = function log() {
    if (!this.options_.debug) {
      return;
    }

    for (var _len = arguments.length, msgs = new Array(_len), _key = 0; _key < _len; _key++) {
      msgs[_key] = arguments[_key];
    }

    msgs.forEach(function (msg) {
      videojs.log('VR: ', msg);
    });
  };

  _proto.handleVrDisplayActivate_ = function handleVrDisplayActivate_() {
    var _this3 = this;

    if (!this.vrDisplay) {
      return;
    }

    this.vrDisplay.requestPresent([{
      source: this.renderedCanvas
    }]).then(function () {
      if (!_this3.vrDisplay.cardboardUI_ || !videojs.browser.IS_IOS) {
        return;
      } // webvr-polyfill/cardboard ui only watches for click events
      // to tell that the back arrow button is pressed during cardboard vr.
      // but somewhere along the line these events are silenced with preventDefault
      // but only on iOS, so we translate them ourselves here


      var touches = [];

      var iosCardboardTouchStart_ = function iosCardboardTouchStart_(e) {
        for (var i = 0; i < e.touches.length; i++) {
          touches.push(e.touches[i]);
        }
      };

      var iosCardboardTouchEnd_ = function iosCardboardTouchEnd_(e) {
        if (!touches.length) {
          return;
        }

        touches.forEach(function (t) {
          var simulatedClick = new window.MouseEvent('click', {
            screenX: t.screenX,
            screenY: t.screenY,
            clientX: t.clientX,
            clientY: t.clientY
          });

          _this3.renderedCanvas.dispatchEvent(simulatedClick);
        });
        touches = [];
      };

      _this3.renderedCanvas.addEventListener('touchstart', iosCardboardTouchStart_);

      _this3.renderedCanvas.addEventListener('touchend', iosCardboardTouchEnd_);

      _this3.iosRevertTouchToClick_ = function () {
        _this3.renderedCanvas.removeEventListener('touchstart', iosCardboardTouchStart_);

        _this3.renderedCanvas.removeEventListener('touchend', iosCardboardTouchEnd_);

        _this3.iosRevertTouchToClick_ = null;
      };
    });
  };

  _proto.handleVrDisplayDeactivate_ = function handleVrDisplayDeactivate_() {
    if (!this.vrDisplay || !this.vrDisplay.isPresenting) {
      return;
    }

    if (this.iosRevertTouchToClick_) {
      this.iosRevertTouchToClick_();
    }

    this.vrDisplay.exitPresent();
  };

  _proto.requestAnimationFrame = function requestAnimationFrame(fn) {
    if (this.vrDisplay) {
      return this.vrDisplay.requestAnimationFrame(fn);
    }

    return this.player_.requestAnimationFrame(fn);
  };

  _proto.cancelAnimationFrame = function cancelAnimationFrame(id) {
    if (this.vrDisplay) {
      return this.vrDisplay.cancelAnimationFrame(id);
    }

    return this.player_.cancelAnimationFrame(id);
  };

  _proto.togglePlay_ = function togglePlay_() {
    if (this.player_.paused()) {
      this.player_.play();
    } else {
      this.player_.pause();
    }
  };

  _proto.animate_ = function animate_() {
    if (!this.initialized_) {
      return;
    }

    if (this.getVideoEl_().readyState === this.getVideoEl_().HAVE_ENOUGH_DATA) {
      if (this.videoTexture) {
        this.videoTexture.needsUpdate = true;
      }
    }

    this.controls3d.update();

    if (this.omniController) {
      this.omniController.update(this.camera);
    }

    this.effect.render(this.scene, this.camera);

    if (window.navigator.getGamepads) {
      // Grab all gamepads
      var gamepads = window.navigator.getGamepads();

      for (var i = 0; i < gamepads.length; ++i) {
        var gamepad = gamepads[i]; // Make sure gamepad is defined
        // Only take input if state has changed since we checked last

        if (!gamepad || !gamepad.timestamp || gamepad.timestamp === this.prevTimestamps_[i]) {
          continue;
        }

        for (var j = 0; j < gamepad.buttons.length; ++j) {
          if (gamepad.buttons[j].pressed) {
            this.togglePlay_();
            this.prevTimestamps_[i] = gamepad.timestamp;
            break;
          }
        }
      }
    }

    this.camera.getWorldDirection(this.cameraVector);
    this.animationFrameId_ = this.requestAnimationFrame(this.animate_);
  };

  _proto.handleResize_ = function handleResize_() {
    var width = this.player_.currentWidth();
    var height = this.player_.currentHeight();
    this.effect.setSize(width, height, false);
    this.camera.aspect = width / height;
    this.camera.updateProjectionMatrix();
  };

  _proto.setProjection = function setProjection(projection) {
    if (!getInternalProjectionName(projection)) {
      videojs.log.error('videojs-vr: please pass a valid projection ' + validProjections.join(', '));
      return;
    }

    this.currentProjection_ = projection;
    this.defaultProjection_ = projection;
  };

  _proto.init = function init() {
    var _this4 = this;

    this.reset();
    this.camera = new THREE.PerspectiveCamera(75, this.player_.currentWidth() / this.player_.currentHeight(), 1, 1000); // Store vector representing the direction in which the camera is looking, in world space.

    this.cameraVector = new THREE.Vector3();

    if (this.currentProjection_ === '360_LR' || this.currentProjection_ === '360_TB' || this.currentProjection_ === '180' || this.currentProjection_ === 'EAC_LR') {
      // Render left eye when not in VR mode
      this.camera.layers.enable(1);
    }

    this.scene = new THREE.Scene();
    this.videoTexture = new THREE.VideoTexture(this.getVideoEl_()); // shared regardless of wether VideoTexture is used or
    // an image canvas is used

    this.videoTexture.generateMipmaps = false;
    this.videoTexture.minFilter = THREE.LinearFilter;
    this.videoTexture.magFilter = THREE.LinearFilter;
    this.videoTexture.format = THREE.RGBFormat;
    this.changeProjection_(this.currentProjection_);

    if (this.currentProjection_ === 'NONE') {
      this.log('Projection is NONE, dont init');
      this.reset();
      return;
    }

    this.player_.removeChild('BigPlayButton');
    this.player_.addChild('BigVrPlayButton', {}, this.bigPlayButtonIndex_);
    this.player_.bigPlayButton = this.player_.getChild('BigVrPlayButton'); // mobile devices, or cardboard forced to on

    if (this.options_.forceCardboard || videojs.browser.IS_ANDROID || videojs.browser.IS_IOS) {
      this.addCardboardButton_();
    } // if ios remove full screen toggle


    if (videojs.browser.IS_IOS) {
      this.player_.controlBar.fullscreenToggle.hide();
    }

    this.camera.position.set(0, 0, 0);
    this.renderer = new THREE.WebGLRenderer({
      devicePixelRatio: window.devicePixelRatio,
      alpha: false,
      clearColor: 0xffffff,
      antialias: true
    });
    var webglContext = this.renderer.getContext('webgl');
    var oldTexImage2D = webglContext.texImage2D;
    /* this is a workaround since threejs uses try catch */

    webglContext.texImage2D = function () {
      try {
        for (var _len2 = arguments.length, args = new Array(_len2), _key2 = 0; _key2 < _len2; _key2++) {
          args[_key2] = arguments[_key2];
        }

        return oldTexImage2D.apply(webglContext, args);
      } catch (e) {
        _this4.reset();

        _this4.player_.pause();

        _this4.triggerError_({
          code: 'web-vr-hls-cors-not-supported',
          dismiss: false
        });

        throw new Error(e);
      }
    };

    this.renderer.setSize(this.player_.currentWidth(), this.player_.currentHeight(), false);
    this.effect = new VREffect(this.renderer);
    this.effect.setSize(this.player_.currentWidth(), this.player_.currentHeight(), false);
    this.vrDisplay = null; // Previous timestamps for gamepad updates

    this.prevTimestamps_ = [];
    this.renderedCanvas = this.renderer.domElement;
    this.renderedCanvas.setAttribute('style', 'width: 100%; height: 100%; position: absolute; top:0;');
    var videoElStyle = this.getVideoEl_().style;
    this.player_.el().insertBefore(this.renderedCanvas, this.player_.el().firstChild);
    videoElStyle.zIndex = '-1';
    videoElStyle.opacity = '0';

    if (window.navigator.getVRDisplays) {
      this.log('is supported, getting vr displays');
      window.navigator.getVRDisplays().then(function (displays) {
        if (displays.length > 0) {
          _this4.log('Displays found', displays);

          _this4.vrDisplay = displays[0]; // Native WebVR Head Mounted Displays (HMDs) like the HTC Vive
          // also need the cardboard button to enter fully immersive mode
          // so, we want to add the button if we're not polyfilled.

          if (!_this4.vrDisplay.isPolyfilled) {
            _this4.log('Real HMD found using VRControls', _this4.vrDisplay);

            _this4.addCardboardButton_(); // We use VRControls here since we are working with an HMD
            // and we only want orientation controls.


            _this4.controls3d = new VRControls(_this4.camera);
          }
        }

        if (!_this4.controls3d) {
          _this4.log('no HMD found Using Orbit & Orientation Controls');

          var options = {
            camera: _this4.camera,
            canvas: _this4.renderedCanvas,
            // check if its a half sphere view projection
            halfView: _this4.currentProjection_ === '180',
            orientation: videojs.browser.IS_IOS || videojs.browser.IS_ANDROID || false
          };

          if (_this4.options_.motionControls === false) {
            options.orientation = false;
          }

          _this4.controls3d = new OrbitOrientationControls(options);
          _this4.canvasPlayerControls = new CanvasPlayerControls(_this4.player_, _this4.renderedCanvas);
        }

        _this4.animationFrameId_ = _this4.requestAnimationFrame(_this4.animate_);
      });
    } else if (window.navigator.getVRDevices) {
      this.triggerError_({
        code: 'web-vr-out-of-date',
        dismiss: false
      });
    } else {
      this.triggerError_({
        code: 'web-vr-not-supported',
        dismiss: false
      });
    }

    if (this.options_.omnitone) {
      var audiocontext = THREE.AudioContext.getContext();
      this.omniController = new OmnitoneController(audiocontext, this.options_.omnitone, this.getVideoEl_(), this.options_.omnitoneOptions);
      this.omniController.one('audiocontext-suspended', function () {
        _this4.player.pause();

        _this4.player.one('playing', function () {
          audiocontext.resume();
        });
      });
    }

    this.on(this.player_, 'fullscreenchange', this.handleResize_);
    window.addEventListener('vrdisplaypresentchange', this.handleResize_, true);
    window.addEventListener('resize', this.handleResize_, true);
    window.addEventListener('vrdisplayactivate', this.handleVrDisplayActivate_, true);
    window.addEventListener('vrdisplaydeactivate', this.handleVrDisplayDeactivate_, true);
    this.initialized_ = true;
    this.trigger('initialized');
  };

  _proto.addCardboardButton_ = function addCardboardButton_() {
    if (!this.player_.controlBar.getChild('CardboardButton')) {
      this.player_.controlBar.addChild('CardboardButton', {});
    }
  };

  _proto.getVideoEl_ = function getVideoEl_() {
    return this.player_.el().getElementsByTagName('video')[0];
  };

  _proto.reset = function reset() {
    if (!this.initialized_) {
      return;
    }

    if (this.omniController) {
      this.omniController.off('audiocontext-suspended');
      this.omniController.dispose();
      this.omniController = undefined;
    }

    if (this.controls3d) {
      this.controls3d.dispose();
      this.controls3d = null;
    }

    if (this.canvasPlayerControls) {
      this.canvasPlayerControls.dispose();
      this.canvasPlayerControls = null;
    }

    if (this.effect) {
      this.effect.dispose();
      this.effect = null;
    }

    window.removeEventListener('resize', this.handleResize_, true);
    window.removeEventListener('vrdisplaypresentchange', this.handleResize_, true);
    window.removeEventListener('vrdisplayactivate', this.handleVrDisplayActivate_, true);
    window.removeEventListener('vrdisplaydeactivate', this.handleVrDisplayDeactivate_, true); // re-add the big play button to player

    if (!this.player_.getChild('BigPlayButton')) {
      this.player_.addChild('BigPlayButton', {}, this.bigPlayButtonIndex_);
    }

    if (this.player_.getChild('BigVrPlayButton')) {
      this.player_.removeChild('BigVrPlayButton');
    } // remove the cardboard button


    if (this.player_.getChild('CardboardButton')) {
      this.player_.controlBar.removeChild('CardboardButton');
    } // show the fullscreen again


    if (videojs.browser.IS_IOS) {
      this.player_.controlBar.fullscreenToggle.show();
    } // reset the video element style so that it will be displayed


    var videoElStyle = this.getVideoEl_().style;
    videoElStyle.zIndex = '';
    videoElStyle.opacity = ''; // set the current projection to the default

    this.currentProjection_ = this.defaultProjection_; // reset the ios touch to click workaround

    if (this.iosRevertTouchToClick_) {
      this.iosRevertTouchToClick_();
    } // remove the old canvas


    if (this.renderedCanvas) {
      this.renderedCanvas.parentNode.removeChild(this.renderedCanvas);
    }

    if (this.animationFrameId_) {
      this.cancelAnimationFrame(this.animationFrameId_);
    }

    this.initialized_ = false;
  };

  _proto.dispose = function dispose() {
    _Plugin.prototype.dispose.call(this);

    this.reset();
  };

  _proto.polyfillVersion = function polyfillVersion() {
    return WebVRPolyfill.version;
  };

  return VR;
}(Plugin);

VR.prototype.setTimeout = Component.prototype.setTimeout;
VR.prototype.clearTimeout = Component.prototype.clearTimeout;
VR.VERSION = version;
videojs.registerPlugin('vr', VR);

module.exports = VR;
