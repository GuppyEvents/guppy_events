window.onload = function () {


  'use strict';

  var Cropper = window.Cropper;
  var URL = window.URL || window.webkitURL;
  var container = document.querySelector('.img-container');
  var image = container.getElementsByTagName('img').item(0);
  var actions = document.getElementById('actions');
  var dataX = document.getElementById('dataX');
  var dataY = document.getElementById('dataY');
  var dataHeight = document.getElementById('dataHeight');
  var dataWidth = document.getElementById('dataWidth');
  var dataRotate = document.getElementById('dataRotate');
  var dataScaleX = document.getElementById('dataScaleX');
  var dataScaleY = document.getElementById('dataScaleY');
  var options = {
        aspectRatio: 920 / 275,
        checkCrossOrigin: false,
        autoCropArea: 1.0,
        preview: '.img-preview',
        viewMode: 2,
        responsive: false,
        ready: function (e) {
          console.log(e.type);
        },
        cropstart: function (e) {
          console.log(e.type, e.detail.action);
        },
        cropmove: function (e) {
          console.log(e.type, e.detail.action);
        },
        cropend: function (e) {
          console.log(e.type, e.detail.action);
        },
        crop: function (e) {
          var data = e.detail;

          console.log(e.type);
          dataX.value = Math.round(data.x);
          dataY.value = Math.round(data.y);
          dataHeight.value = Math.round(data.height);
          dataWidth.value = Math.round(data.width);
            console.log('__ ' + Math.round(data.x) + '__ ' + Math.round(data.y) + '__ ' + Math.round(data.height) )
        },
        zoom: function (e) {
          console.log(e.type, e.detail.ratio);
        }
      };
  var cropper = new Cropper(image, options);
  var originalImageURL = image.src;
  var uploadedImageURL;

  // Tooltip
  $('[data-toggle="tooltip"]').tooltip();


  // Buttons
  if (!document.createElement('canvas').getContext) {
    $('button[data-method="getCroppedCanvas"]').prop('disabled', true);
  }

  if (typeof document.createElement('cropper').style.transition === 'undefined') {
    $('button[data-method="rotate"]').prop('disabled', true);
    $('button[data-method="scale"]').prop('disabled', true);
  }

    // crop edilen resmin sağa sola hareket etmesini sağlar
  // document.body.onkeydown = function (event) {
  //   var e = event || window.event;
  //
  //   if (!cropper || this.scrollTop > 300) {
  //     return;
  //   }
  //
  //   switch (e.keyCode) {
  //     case 37:
  //       e.preventDefault();
  //       cropper.move(-1, 0);
  //       break;
  //
  //     case 38:
  //       e.preventDefault();
  //       cropper.move(0, -1);
  //       break;
  //
  //     case 39:
  //       e.preventDefault();
  //       cropper.move(1, 0);
  //       break;
  //
  //     case 40:
  //       e.preventDefault();
  //       cropper.move(0, 1);
  //       break;
  //   }
  // };

};
