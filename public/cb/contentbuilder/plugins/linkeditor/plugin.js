(function () {

  let isStandaloneLinkEditor = false;
  let isStandaloneLinkEditorText = "";

  window.updateLinkValue = (lk, txt, target) => {
    _cb.uo.saveForUndo();
    var link = _cb.activeLink;
    if (link) {
      var url = lk;
      var title = txt;
      var linktext = txt;
      if (linktext == "") linktext = url;
      if (url != "") {
        link.setAttribute("href", url);
        if (target) {
          link.setAttribute("target", "_blank");
        } else {
          link.removeAttribute("target");
        }
        if (_cb.activeIcon);
        else {
          link.innerHTML = linktext;
        }
        link.setAttribute("title", title);
      } else {
        var el = link;
        var range = document.createRange();
        range.selectNodeContents(el);
        var sel = window.getSelection();
        sel.removeAllRanges();
        sel.addRange(range);
        document.execCommand("unlink", false, null);
      }
      _cb.opts.onChange();
    } else {
      _cb.util.restoreSelection();
      var _url = lk;
      var _title = txt;
      var _linktext = txt;
      if (_linktext == "") _linktext = _url;
      if (_url != "") {
        _cb.uo.saveForUndo();
        var activeLink;
        if (_cb.activeIcon) {
          var iconhtml = _cb.activeIcon.outerHTML;
          _cb.activeIcon.outerHTML = '<a class="__dummy" href="'
            .concat(_url, '">')
            .concat(iconhtml, "</a>");
          activeLink = document.querySelector(".__dummy");
          activeLink.classList.remove("__dummy");
          if (target) {
            activeLink.setAttribute("target", "_blank");
          } else {
            activeLink.removeAttribute("target");
          }
          activeLink.setAttribute("title", _title);
          _cb.activeIcon = activeLink.childNodes[0];
          if (!_cb.util.appleMobile)
            var range = document.createRange();
          range.selectNodeContents(_cb.activeIcon);
          var sel = window.getSelection();
          sel.removeAllRanges();
          sel.addRange(range);
        } else {
          if (_cb.isIE) {
            util.hideModal(modal);
            return;
          }
          range = lastSelectionRange
          sel = window.getSelection();
          sel.removeAllRanges();
          sel.addRange(range);
          document.execCommand("createLink", false, "http://dummy");
          var _activeLink = document.querySelector(
            'a[href="http://dummy"]'
          );
          _activeLink.setAttribute("href", _url)
          _activeLink.classList.add('innerLink')
          _activeLink.innerHTML = _linktext;
          if (target) {
            _activeLink.setAttribute("target", "_blank");
          } else {
            _activeLink.removeAttribute("target");
          }
          _activeLink.setAttribute("title", _title);
          if (!_cb.util.appleMobile) {
            var range = document.createRange();
            range.selectNodeContents(_activeLink);
            var sel = window.getSelection();
            sel.removeAllRanges();
            sel.addRange(range);
          }
        }
      }else{

        window.ShowToast(_title + ' link has not been set in your account, it can not be added.','error','5000');
      }
      _cb.util.saveSelection();
      _cb.opts.onChange();
      _cb.opts.onRender();
    }
    const con = document.getElementById('link-editor-html').querySelector('.link-editor-main-container');
    con.querySelector('.input-url').focus();
  };

  const getLinkValue = () => {
    const link = _cb.activeLink;
    if (link) {
      return {
        url: link.getAttribute("href"),
        text: link.getAttribute('title'),
        target: !!link.hasAttribute("target")
      };
    } else {

      return {
        url: "",
        text: isStandaloneLinkEditorText,
        target: ""
      };
    }
  };

  const html1 = `
  <div id="link-editor-html" style="    display: flex;flex-direction: column;flex-grow: 2;">
    <div class="link-editor-main-container" callback="updateLinkValue">
    </div>
  </div>
  `;

  _cb.addHtmlToLeftPanel(html1);


  const processLinking = () => {
    const linkContainers = document.querySelectorAll('.link-editor-main-container');
    if (linkContainers.length !== 3) {
      setTimeout(processLinking, 0);
    }
    Array.prototype.forEach.call(linkContainers, linkContainer => {
      let popup = null;
      let targetInput = null;
      let targetDiv = null;
      let targetText = null;
      let targetTarget = null;

      const prepareItem = (title, icon) => {
        return `<div data-for="${title}" class="shopify-links-popup-item" style="display: flex;gap: 10px;padding: 10px;height: 30px;border-bottom: solid 1px #ccc; cursor: pointer;">
                <img src="/cb/assets/svg/${icon}.svg" style="width: 5%;filter: contrast(42%);"/>
                <div>${title}</div>
              </div>`;
      }

      const prepareLink = (title, link) => {
        return `<div data-for="${link}" data-title=${title} class="shopify-links-popup-item-link" style="display: flex;gap: 10px;padding: 10px;border-bottom: solid 1px #ccc; cursor: pointer;">
                <img src="/cb/assets/svg/LinkMinor.svg" style="width: 5%;filter: contrast(42%);"/>
                <div>${title}</div>
              </div>`;
      }

      function closePopUp() {
        popup.style.visibility = 'hidden';
      }

      const addCloseButton = () => {
        return `<div class="shopify-links-popup-close" style="display: 
                flex; flex-direction: row-reverse; position: absolute; top: 0; right: 5px; cursor: pointer;">
              <span id="shopify-links-popup-close" style="all: unset;"><i class="icon ion-close"> </i></span>
            </div>
    `;
      }

      const addCloseButtonEvent = () => {
        popup.querySelector('.shopify-links-popup-close').addEventListener('click', (e) => {
          closePopUp();
          e.stopPropagation();
        });
      }

      const addBackButton = () => {
        return `<div id="shopify-links-popup-backbutton" style="display: flex;gap: 10px;padding: 10px;height: 30px;border-bottom: solid 1px #ccc; cursor: pointer;">
                <span><i class="icon ion-android-arrow-back"> </i></span>
                <div>Go Back</div>
              </div>`;
      }
      var killLinks = false
      const addBackButtonEvent = () => {
        popup.querySelector('#shopify-links-popup-backbutton').addEventListener('click', (e) => {
          killLinks = true
          prepareDefaultMenu();
          e.stopPropagation();
        });
      }

      const prepareDefaultMenu = () => {
        processingLinks = true
        html = '';
        html += addCloseButton();
        html += prepareItem('Tags', 'TagsMajor');
        html += prepareItem('Collections', 'CollectionsMajor');
        html += prepareItem('Products', 'ProductsMajor');
        html += prepareItem('Pages', 'PageMajor');
        html += prepareItem('Blogs', 'BlogMajor');
        html += prepareItem('Articles', 'NoteMajor');

        popup.innerHTML = html;
        // targetDiv.appendChild(popup);

        isDefaultMenu = true;
        addCloseButtonEvent();
        addEventsToItems();
      }

      const prepareNewMenu = (data, target) => {
        processingLinks = false
        html = '';
        html += addCloseButton();
        html += addBackButton();
        if(target != "tags"){
          html += prepareLink('All', _cb.settings.storeDomain + "/" + target + "/all");
        }
        data.forEach(e => html += prepareLink(e.title, e.link));

        popup.innerHTML = html;

        addCloseButtonEvent();
        addBackButtonEvent();

        addEventsToLinks();
      }
      var target = ""
      var processingLinks = true
      const addEventsToItems = () => {
        const items = popup.querySelectorAll('.shopify-links-popup-item');
        Array.prototype.forEach.call(items, function (item) {
          item.addEventListener('click', (e) => {
            target = item.getAttribute('data-for').toLowerCase();
            hasNextPage = true
            getLinkItems()
            killLinks = false
            e.stopPropagation();
          });
        });
      }
      var hasNextPage = true
      const getLinkItems = () => {
        if(!hasNextPage){
          return
        }
        var myHeaders = new Headers();
            myHeaders.append("Content-Type", "application/json");
        var requestOptions = {
          method: 'GET',
          headers: myHeaders,
          redirect: 'follow'
        };
        
        let query = targetInput.value.trim();
            query = query === '#' ? '' : query;
            let cachedLinks = window.localStorage.getItem(target); // [{title, link}]
            let isCacheAvailable = false;
            let cursor = ""
            let page = 1
            if (cachedLinks) {
              cachedLinks = JSON.parse(cachedLinks);
              
              isCacheAvailable = true;
              cursor = cachedLinks[cachedLinks.length -1].cursor
              page = cachedLinks[cachedLinks.length -1].page
            } else {
              cachedLinks = [];
            }
            prepareNewMenu(cachedLinks, target);
            let url = _cb.settings.linksAPI + '?target=' + target + '&query=' + query + '&cursor=' + cursor;
            
            hasNextPage = true
            fetch(url, requestOptions)
              .then(response => response.json())
              .then(data => {
                if(target === "tags"){
                  if (data.length > 0) {
                    for (let index = 0; index < data.length; index++) {
                      const obj = data[index];
                      const title = obj.title;
                      const link = obj.link;
                      if (cachedLinks) {
                        if (!cachedLinks.find(c => c.title === title && c.link === link)) {
                          cachedLinks.push({ title, link });
                        }else{
                          break;
                        }
                      } else {
                        cachedLinks.push({ title, link });
                      }
                    }
                  }
                } else {
                  if (data.data) {
                    let shopUrl = data.data.shop
                    if(data.data[target]){
                      var edges = data.data[target].edges
                      for (let index = 0; index < edges.length; index++) {
                        if(killLinks){
                          killLinks = false
                          break
                        }
                        const title = edges[index].node.title;
                        const link = shopUrl + edges[index].node.handle;
                        const cursor = edges[index].cursor
                          if (!cachedLinks.find(c => c.cursor === cursor)) {
                            cachedLinks.push({ title, link, cursor });
                            document.querySelector('.shopify-links-popup').insertAdjacentHTML( 'beforeend', prepareLink(title, link) );
                          }
                      }
                    }
                    
                  }
                }
                if(target != "tags"){
                  if(!data.data[target] || !data.data[target].pageInfo.hasNextPage){
                    hasNextPage = false
                  }
                }
                
                if (cachedLinks.length > 0){
                  window.localStorage.setItem(target, JSON.stringify(cachedLinks));
                }
                if (!isCacheAvailable)
                  prepareNewMenu(cachedLinks, target);
              }).catch(error => console.log('error', error))
              .finally(() => {
                if(target != "tags"){
                  processingLinks = false;
                  triggerLinkItems()
                }
                
              });

            
      }
      const triggerLinkItems = () => {
        if(!processingLinks){
          totalHeight = popup.scrollHeight - (popup.clientHeight * 2);
          if(popup.scrollTop > totalHeight && totalHeight > -200){
            processingLinks = true
            getLinkItems();
          }
        }
      }
      const addEventsToLinks = () => {
        const items = popup.querySelectorAll('.shopify-links-popup-item-link');
        Array.prototype.forEach.call(items, function (item) {
          item.addEventListener('click', (e) => {
            processingLinks = false
            const link = item.getAttribute('data-for');
            targetInput.value = link;
            const linkText = linkContainer.getAttribute('link-text');
            targetText.value = !!linkText ? linkText : item.getAttribute('data-title');
            closePopUp();
            updateLinkValue();

            e.stopPropagation();
          });
        });
      }

      const updateLinkValue = () => {
        const callback = linkContainer.getAttribute('callback');
        if (!!callback) {
          window[callback](targetInput.value, targetText.value, targetTarget.checked);
        }
      };

      window.getData = (data) => {
        targetInput.value = data.url;
        targetText.value = data.text;
        targetTarget.checked = data.target;
      };

      linkContainer.setAttribute('sendData', "getData")

      if (linkContainer) {
        if (isStandaloneLinkEditor) {
          linkContainer.setAttribute('callback', "updateLinkValue");
          linkContainer.setAttribute('getData', "getLinkValue");
          linkContainer.setAttribute('link-text', isStandaloneLinkEditorText);
        }
        linkContainer.innerHTML = `
      <div style="background: #f9f9f9;padding: 5px 20px;">Link Settings</div>
        <div style="padding: 20px;">
            <div style="max-width:526px;">
                <div>URL</div>                    
                <div class="link-src dynamic-links-panel">
                    <input class="input-url" type="text" placeholder="Url" style="width: 100%;">
                </div>
                <div style="display:none;align-items: center;margin-top:14px;margin-bottom:10px;float:left;">
                  <div>Open New Window</div> 
                  <div style="display:flex;">
                    <input class="input-newwindow checkbox-switch-input" type="checkbox" id="input_newwindow">
                    <label class="checkbox-switch" for="input_newwindow">Toggle</label>
                  </div>
                </div>
                <div style="margin-top: 20px;">Display/Alternate Text:</div>
                <div>
                  <input class="input-text" type="text" placeholder="Text" style="width:100%;">
                </div>
            </div>
        </div>
      `;
        
        isStandaloneLinkEditor = false;
        setTimeout(() => {
          targetDiv = linkContainer.querySelector(".link-src");
          targetInput = linkContainer.querySelector(".input-url");
          targetText = linkContainer.querySelector(".input-text");
          targetTarget = linkContainer.querySelector("#input_newwindow");

          popup = document.createElement('div');
          // popup.id = 'shopify-links-popup';
          popup.className = 'shopify-links-popup';
          popup.style.backgroundColor = 'white';
          popup.style.border = 'solid 2px gainsboro';
          popup.style.zIndex = 1100;
          popup.style.width = '345px';
          popup.style.height = '200px';
          popup.style.position = 'absolute';
          popup.style.left = '0';
          popup.style.top = '50px';
          popup.style.overflow = 'auto';
          popup.style.display = 'flex';
          popup.style.flexDirection = 'column';
          popup.style.gap = '5px';
          popup.style.visibility = 'hidden';

          targetDiv.appendChild(popup);

          targetInput.addEventListener('focus', () => {
            prepareDefaultMenu();
            popup.style.visibility = 'visible';
          });
          popup.addEventListener('scroll', () =>{
            triggerLinkItems();
          })
          let targetInputTimer = null;
          targetInput.addEventListener('input', () => {
            if (targetInputTimer) {
              clearTimeout(targetInputTimer);
              targetInputTimer = null;
            }
            targetInputTimer = setTimeout(updateLinkValue, 1000);
          });

          let targetTextTimer = null;
          targetText.addEventListener('input', () => {
            if (targetTextTimer) {
              clearTimeout(targetTextTimer);
              targetTextTimer = null;
            }
            targetTextTimer = setTimeout(updateLinkValue, 1000);
          });

          targetTarget.addEventListener('change', updateLinkValue);

        }, 0);
      }
    });
  };

  const process = () => {
    if (!document.getElementById('link-editor-html')) {
      setTimeout(process, 0);
      return;
    }

    setTimeout(() => {
      document.querySelector("button.rte-link").addEventListener('click', e => {
        isStandaloneLinkEditor = true;
        isStandaloneLinkEditorText = "";
        if (window.getSelection) {
          isStandaloneLinkEditorText = window.getSelection().toString();
        } else if (document.selection && document.selection.type != "Control") {
          isStandaloneLinkEditorText = document.selection.createRange().text;
        }

        const con = document.getElementById('link-editor-html').querySelector('.link-editor-main-container');
        const data = getLinkValue();
        con.querySelector('.input-url').value = data.url;
        con.querySelector('.input-newwindow').value = data.target;
        con.querySelector('.input-text').value = data.text;

        
        document.getElementById('quick-settings-title').innerText = "Link Settings";
        _cb.showLeftSidePanel('link-editor-html');
      });
    }, 1000);
    setTimeout(processLinking, 0);
  }

  setTimeout(process, 0);

  window.onclick = function (event) {
    if (!event.target.classList.contains('input-url')) {
      const pps = document.querySelectorAll('.shopify-links-popup');
      Array.prototype.forEach.call(pps, p => p.style.visibility = 'hidden');
    }
  }

  var oldgetCC = _cb.opts.onClearingControls;
  _cb.opts.onClearingControls = function (e) {
    const pps = document.querySelectorAll('.shopify-links-popup');
    Array.prototype.forEach.call(pps, p => p.style.visibility = 'hidden');
    var ret = oldgetCC.apply(this, arguments);
    return ret;
  }
  var lastSelectionRange = ''
  document.querySelector('.container_bg').addEventListener('click',function(){
    lastSelectionRange = window.getSelection().getRangeAt(0)
  })
})();

