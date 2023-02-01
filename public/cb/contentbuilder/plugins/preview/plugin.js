/*
Preview Plugin
*/

(function () {

  var _screenwidth = window.innerWidth;
  // if (_screenwidth <= 640) return;

  var html =
    `<div class="is-modal previewcontent" style="z-index:10004">
      <div style="width:100%;height:100%;background:#fff;position: relative;display: flex;flex-direction: column;align-items: center;padding: 0px;background:#f8f8f8;">
        <div class="is-modal-bar" style="position: absolute;top: 0;left: 0;width: 100%;z-index:1;line-height:1.5;height:32px;padding:0; background: transparent;">
          <div style="display: flex; justify-content: end;" class="is-settings">
            <button  data-width="1440" data-name="Desktop" class="size-control align-buttons-active" style="border: 1px solid #dadada; width: 50px; height: 50px; padding: 5px;background: #f7f7f7;"><i class="icon ion-android-desktop"> </i></button>
            <button data-width="768" data-name="Tablet" class="size-control" style="border: 1px solid #dadada; width: 50px; height: 50px; padding: 5px;background: #f7f7f7;"><i class="icon ion-android-phone-landscape"> </i></button>
            <button data-width="425" data-name="Mobile" class="size-control" style="border: 1px solid #dadada; width: 50px; height: 50px; padding: 5px;background: #f7f7f7;"><i class="icon ion-android-phone-portrait"> </i></button>
            <button class="close-button" style="margin-left: 15px;background: transparent;margin-right: 15px;"><i class="icon ion-close"> </i></button>
          </div>
          <div style="width:100%;height:100%;display:none;justify-content:center;">
            <div class="size-control" data-width="1440" data-name="Desktop" style="width:1440px;">
              <div class="size-control" data-width="768" data-name="Tablet" style="width:768px;">
                <div class="size-control" data-width="425" data-name="Mobile" style="width:320px;">
                  <div class="size-control-info" style="line-height:32px;">Desktop</div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <iframe data-width="1440" data-name="Desktop" style="width:100%;height:98%;max-width:1440px;border:none;margin:0;box-sizing:border-box;background:#fff;" src="about:blank"></iframe>
      </div>
    </div>
    <svg width="0" height="0" style="position:absolute;display:none;">
      <defs>
        <symbol viewBox="0 0 512 512" id="ion-ios-close-empty"><path d="M340.2 160l-84.4 84.3-84-83.9-11.8 11.8 84 83.8-84 83.9 11.8 11.7 84-83.8 84.4 84.2 11.8-11.7-84.4-84.3 84.4-84.2z"></path></symbol>
        <symbol viewBox="0 0 512 512" id="ion-ios-search-strong"><path d="M344.5 298c15-23.6 23.8-51.6 23.8-81.7 0-84.1-68.1-152.3-152.1-152.3C132.1 64 64 132.2 64 216.3c0 84.1 68.1 152.3 152.1 152.3 30.5 0 58.9-9 82.7-24.4l6.9-4.8L414.3 448l33.7-34.3-108.5-108.6 5-7.1zm-43.1-166.8c22.7 22.7 35.2 52.9 35.2 85s-12.5 62.3-35.2 85c-22.7 22.7-52.9 35.2-85 35.2s-62.3-12.5-85-35.2c-22.7-22.7-35.2-52.9-35.2-85s12.5-62.3 35.2-85c22.7-22.7 52.9-35.2 85-35.2s62.3 12.5 85 35.2z"></path></symbol>
      </defs>
    </svg>`;

  _cb.addHtml(html);

  var css =
    "<style>" +
    ".size-control {cursor:pointer;background:#ddd;border-left:#fff 2px solid;border-right:#fff 2px solid;height:100%;display:flex;justify-content:center;}" +
    ".size-control-info {text-align:center;color:#000;}" +
    "</style>";

  _cb.addCss(css);

  var modal = document.querySelector(".is-modal.previewcontent");

  const process = () => {
    if(!document.getElementById('previewcontent-button')) {
      setTimeout(process, 0);
      return;
    }
    document.getElementById('previewcontent-button').addEventListener('click', function (e) {

      _cb.showModal(modal);


      //check if builder is inside iframe
      if (window.frameElement) {
        var c = getFramedWindow(window.frameElement);
        var doc = c.document;
      } else {
        var doc = parent.document;
      }

      var basehref = "";
      var base = doc.querySelectorAll("base[href]");
      if (base.length > 0) {
        basehref = '<base href="' + base[0].href + '" />';
      }

      var csslinks = "";
      var styles = doc.querySelectorAll("link[href]");
      for (var i = 0; i < styles.length; i++) {
        if (
          styles[i].href.indexOf(".css") != -1 &&
          styles[i].href.indexOf("contentbox.css") == -1 &&
          styles[i].href.indexOf("contentbuilder.css") == -1
        ) {
          csslinks +=
            '<link href="' +
            styles[i].href +
            '" rel="stylesheet" type="text/css" />';
        }
      }
      csslinks += '<link href="/cb/assets/minimalist-blocks/contentmedia2.css" rel="stylesheet" type="text/css" />';

      var jsincludes = "";
      var scripts = doc.querySelectorAll("script[src]");
      for (var i = 0; i < scripts.length; i++) {
        if (
          scripts[i].src.indexOf(".js") != -1 &&
          scripts[i].src.indexOf("index.js") == -1 &&
          scripts[i].src.indexOf("contentbox.js") == -1 &&
          scripts[i].src.indexOf("contentbox.min.js") == -1 &&
          scripts[i].src.indexOf("contentbuilder.js") == -1 &&
          scripts[i].src.indexOf("contentbuilder.min.js") == -1 &&
          scripts[i].src.indexOf("plugin.js") == -1 &&
          scripts[i].src.indexOf("config.js") == -1 &&
          scripts[i].src.indexOf("en.js") == -1 &&
          scripts[i].src.indexOf("minimalist-blocks") == -1
        ) {
          jsincludes +=
            '<script src="' +
            scripts[i].src +
            '" type="text/javascript"></script>';
        }
      }

      // No script
      jsincludes = '';
      const clr = document.querySelector('.container_bg').style.backgroundColor;
      
      /* Get Page */
      if (!document.querySelector(".is-wrapper")) {
        var maxwidth = "800px";
        var maxw = window.getComputedStyle(document.querySelector(".is-builder")).getPropertyValue('max-width');
        if (!isNaN(parseInt(maxw))) maxwidth = maxw;

        

        var content = _cb.html();

        var doc = modal.querySelector('iframe').contentWindow.document;
        doc.open();
        doc.write(
          "<html>" +
          "<head>" +
          basehref +
          '<meta charset="utf-8">' +
          "<title></title>" +
          csslinks +
          "<style>" +
          ".slider-image { display:block !important; }" +
          ".container {margin:0 auto 0; max-width: " +
          maxwidth +
          "; width:100%; padding:0 35px; box-sizing: border-box;}" +
          "</style>" +
          '<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>' +
          "</head>" +
          "<body>" +
          '<div class="container_bg" style="background-color:' + clr + ';">' +
          '<div class="container">' +
          content +
          "</div>" +
          "</div>" +
          jsincludes +
          "</body>" +
          "</html>"
        );
        doc.close();

      } else {
        // ContentBox
        var content = jQuery(".is-wrapper")
          .data("contentbox")
          .html();

        var doc = modal.querySelector('iframe').contentWindow.document;
        doc.open();
        doc.write(
          "<html>" +
          "<head>" +
          basehref +
          '<meta charset="utf-8">' +
          "<title></title>" +
          csslinks +
          "<style>" +
          ".slider-image { display:block !important; }" +
          "</style>" +
          '<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>' +
          "</head>" +
          "<body>" +
          '<div  class="container_bg" style="background-color:' + clr + ';">' +
          '<div class="is-wrapper">' +
          content +
          "</div>" +
          "</div>" +
          jsincludes +
          "</body>" +
          "</html>"
        );
        doc.close();

      }
      //Or you can specify your custom preview page:
      //modal.find('iframe').attr('src','preview.html');

      e.preventDefault();

    });
  };

  setTimeout(process, 0);

  var btnClose = modal.querySelector('.close-button');
  btnClose.addEventListener('click', function (e) {
    _cb.hideModal(modal);
  });


  var sizeControls = modal.querySelectorAll(".size-control");
  Array.prototype.forEach.call(sizeControls, (sizeControl) => {

    sizeControl.addEventListener('mouseenter', function (e) {
      // var elms = modal.querySelectorAll(".size-control");
      // Array.prototype.forEach.call(elms, function (elm) {
      //   elm.style.background = "#ddd";
      // });
      sizeControl.style.background = "#aaa";

      // elms = sizeControl.querySelectorAll(".size-control");
      // Array.prototype.forEach.call(elms, function (elm) {
      //   elm.style.background = "#aaa";
      // });

      modal.querySelector(".size-control-info").style.color = '#fff';

      var w = sizeControl.getAttribute('data-name');
      modal.querySelector(".size-control-info").innerHTML = w;
      e.preventDefault();
      e.stopImmediatePropagation();

    });

    sizeControl.addEventListener('mouseleave', function (e) {
      sizeControl.style.background = '#f7f7f7';
      // var elms = modal.querySelectorAll(".size-control");
      // Array.prototype.forEach.call(elms, function (elm) {
      //   elm.style.background = "#ddd";
      // });
      modal.querySelector(".size-control-info").style.color = '#000';

      var currW = modal.querySelector("iframe").getAttribute('data-name');
      modal.querySelector(".size-control-info").innerText = currW;

    });

    sizeControl.addEventListener('click', function (e) {

      sizeControl.classList.add('align-buttons-active');
      Array.prototype.forEach.call(sizeControls,  (ctr) => {
        if(sizeControl !== ctr) {
          ctr.classList.remove('align-buttons-active');
        }
      });
      var w = sizeControl.getAttribute('data-width');
      var wn = sizeControl.getAttribute('data-name');

      modal.querySelector("iframe").style.maxWidth = w + 'px';
      modal.querySelector("iframe").setAttribute('data-width', w);
      modal.querySelector("iframe").setAttribute('data-name', wn);

      e.preventDefault();
      e.stopImmediatePropagation();

    });

  });

})();


function getFramedWindow(f) {
  if (f.parentNode == null)
    f = document.body.appendChild(f);
  var w = (f.contentWindow || f.contentDocument);
  if (w && w.nodeType && w.nodeType == 9)
    w = (w.defaultView || w.parentWindow);
  return w;
}
