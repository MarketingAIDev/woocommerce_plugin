(function () {
    var getStyle = function (win, element, property) {
        const val = element.style[property.replace(/-([a-z])/g, function (g) { return g[1].toUpperCase(); })];
        if (val && val !== "") {
            return val;
        }
        return win.getComputedStyle(element, null).getPropertyValue(property);
    }

    function getFontSizeAndUnit(fontSize) {
        let size = '';
        let unit = '';
        for (let i = 0; i < fontSize.length; i++) {
            const s = fontSize[i];
            if (!isNaN(s)) {
                size += s;
            } else {
                break;
            }
        }
        return { size, unit };
    }

    const rgb2hex = (rgb) => `#${rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/).slice(1).map(n => parseInt(n, 10).toString(16).padStart(2, '0')).join('')}`

    const parepareButtonPanel = (id, dataTitle, size, unit, image, currentColor, currentLink, iconColor, bgColor, noBG) => {
        let iconButton = '';
        if (!!iconColor) {
            iconButton = `
            <td  data-srcid="${id}" style="text-align: center;">
                <!-- <input  data-srcid="${id}" type="color" class="inpElmIconColor" value="${iconColor}"> -->
                <div data-srcid="${id}" class="inpElmIconColor" style="margin: 0 auto; border: 1px solid rgb(199, 199, 199); width:40px; height: 40px; background-color: ${iconColor}"></div>
            </td>`;
        }
        let bgButton = '';
        if (!!bgColor) {
            bgButton = `
            <td  data-srcid="${id}" style="text-align: center;">
                <!-- <input  data-srcid="${id}" type="color" class="inpElmBGColor" value="${bgColor}"> -->
                <div data-srcid="${id}" class="inpElmBGColor ${noBG}" style="margin: 0 auto; border: 1px solid rgb(199, 199, 199); width:40px; height: 40px; background-color: ${bgColor}"></div>
            </td>`;
        }
        return ''
            + `
            <tr style="text-align: center;" data-srcid="${id}">
                    <td data-srcid="${id}" style="text-align: center;">
                        <input style="width: 80px; font-size: 14px; height: 35px;"  data-srcid="${id}" value="${dataTitle}" class="name inpElmTitle" type="text" value="Twitter"></td>
                    <td data-srcid="${id}" style="text-align: center;"><div class="size-align is-settings interactive">
                        <input  data-srcid="${id}" type="number" class="inpElmSize" value="${size}">
                        <select style="display: none;" data-srcid="${id}" class="inpElmSizeUnit" value="${unit}">
                            <option value="px">px</option>
                            <option value="em">em</option>
                            <option value="vw">vw</option>
                            <option value="vh">vh</option>
                            <option value="%">%</option>
                        </select>
                    </div></td>
                    <td  data-srcid="${id}" style="text-align: center;"><button  data-srcid="${id}" class="inpElmIcon">
                    <img src="${image}"></button></td>
                    <!-- <td  data-srcid="${id}" style="text-align: center;"><input  data-srcid="${id}" type="color" class="inpElmColor" value="${rgb2hex(currentColor)}"></td> -->
                    ` +
                    iconButton + bgButton +
            `
                    <td  data-srcid="${id}" style="text-align: center;">
                        <input style="width: 80px !important; font-size: 14px; height: 35px;"  data-srcid="${id}" class="fullText inpElmLink" type="text" value="${currentLink}" placeholder="https://<<your link here>>">
                    </td>
                    <td  data-srcid="${id}" style="text-align: center;"><button  data-srcid="${id}" class="inpElmTrash" style="font-size: 24px;"><i class="ion ion-trash-a"></i></button></td>
            </tr>
            `;
    };

    let container = null;
    const addButtonController = () => {

    };

    _cb.addHtmlToLeftPanel(`
    <div id="social-media-html" style="    display: flex;flex-direction: column;flex-grow: 2;">
        <iframe data-width="1440" style="width:100%;height:99%;border:none;margin:0;box-sizing:border-box;background:#fff;" id="socialmediacontroller-frame" src="${_cb.getScriptPath()}plugins/socialmedia/render.html"></iframe>
    </div>
    `);

    const process = () => {
        if (!document.getElementById('socialmediacontroller-frame')) {
            setTimeout(process, 0);
            return;
        }

        const iframeWindow = document.getElementById('socialmediacontroller-frame').contentWindow;
        iframeWindow.addEventListener('load', () => {
            const iframeDocument = iframeWindow.document;
            const controller = iframeDocument.querySelector('#socialmediacontroller-container .body .table-body');

            iframeDocument.getElementById('addnew-button').addEventListener('click', () => {
                if (!container) {
                    return;
                }

                const newId = _cb.util.makeId();
                const chs = controller.querySelectorAll('tr');
                const newNode = chs[chs.length - 1].cloneNode(true);
                if (newNode.hasAttribute('data-srcid'))
                    newNode.setAttribute('data-srcid', newId);
                const allChilds = newNode.getElementsByTagName('*');

                for (let i = 0; i < allChilds.length; ++i) {
                    if (allChilds[i].hasAttribute('data-srcid'))
                        allChilds[i].setAttribute('data-srcid', newId);

                    if (allChilds[i].classList?.contains('inpElmTitle'))
                        allChilds[i].setAttribute('value', 'New Button');

                    if (allChilds[i].classList?.contains('inpElmLink'))
                        allChilds[i].setAttribute('value', '');
                }

                newNode.querySelectorAll('.inpElmTrash').forEach(c => {
                    c.addEventListener('click', function () {
                        const chs = controller.querySelectorAll('tr');
                        if (chs.length === 1)
                            return;
                        const src = this.getAttribute('data-srcid');
                        document.getElementById(src).remove();
                        this.parentNode.parentNode.remove();
                    });
                });

                controller.appendChild(newNode);

                const newButton = container.lastChild.cloneNode(true);
                if (newButton.tagName === 'A') {
                    newButton.id = newId;
                    newButton.setAttribute('data-title', 'New Button');
                    newButton.setAttribute('href', 'https://<<your link here>>');
                } else {
                    const atags = newButton.getElementsByTagName('A');
                    for (let i = 0; i < atags.length; i++) {
                        const element = atags[i];
                        element.id = newId;
                        element.setAttribute('data-title', 'New Button');
                        element.setAttribute('href', 'https://<<your link here>>');
                    }
                }

                container.appendChild(newButton);

                newNode.querySelectorAll('.inpElmTitle').forEach(c => {
                    c.addEventListener('keyup', function () {
                        const src = this.getAttribute('data-srcid');
                        document.getElementById(src).setAttribute('data-title', this.value);
                    });
                });

                newNode.querySelectorAll('.inpElmSize').forEach(c => {
                    c.addEventListener('input', function () {
                        console.log('change')
                        const src = this.getAttribute('data-srcid');
                        let unit = '';
                        this.parentNode.childNodes.forEach(n => {
                            if (n.classList?.contains('inpElmSizeUnit')) {
                                unit = n.value;
                            }
                        });
                        document.getElementById(src).childNodes.forEach(i => {
                            if (i.classList?.contains('social-button-icon')) {
                                console.log('social')
                                i.parentNode.style.width = this.value + unit;
                                i.parentNode.style.height = this.value + unit;
                            // const perc = (1 - i.getBoundingClientRect().height / i.parentNode.getBoundingClientRect().height);
                            // if (!!i.parentNode.style.backgroundColor) {
                            //     i.parentNode.style.width = this.value + unit;
                            //     i.parentNode.style.height = this.value + unit;

                            //     const newMargin = this.value * perc / 2;

                            //     i.style.marginTop = newMargin + 'px';
                            // }
                            }
                        });
                    });
                });

                newNode.querySelectorAll('.inpElmLink').forEach(c => {
                    c.addEventListener('keyup', function () {
                        const src = this.getAttribute('data-srcid');
                        document.getElementById(src).setAttribute('href', this.value);
                    });
                });

                newNode.querySelectorAll('.inpElmSizeUnit').forEach(c => {
                    c.addEventListener('change', function () {
                        const src = this.getAttribute('data-srcid');
                        let size = '';
                        this.parentNode.childNodes.forEach(n => {
                            if (n.classList?.contains('inpElmSize')) {
                                size = n.value;
                            }
                        });
                        document.getElementById(src).childNodes.forEach(i => {
                            i.style.width = size + this.value;
                            i.style.height = size + this.value;
                            if (!!i.parentNode.style.backgroundColor) {
                                const newSize = size * 1.875;
                                i.parentNode.style.width = newSize + unit;
                                i.parentNode.style.height = newSize + unit;
                                i.style.margin = (size * 0.42) + 'px'
                            }
                        });
                    });
                });

                newNode.querySelectorAll('.inpElmIcon').forEach(c => {
                    const src = c.getAttribute('data-srcid');
                    c.addEventListener('click', function (e) {
                        iframeDocument.showPopUp(e, c);
                    });
                    c.addEventListener('inpElmIconchange', function (name) {
                        document.getElementById(src).childNodes.forEach(i => {
                            if (i.classList?.contains('social-button-icon')) {
                                i.src = name.detail
                                i.dataset.img = ""
                                theSrc = c.querySelector('img').src
                                noBg = theSrc.includes('round') || theSrc.includes('square') ? false : true
                                c.parentNode.parentNode.querySelector('.inpElmBGColor').classList.remove('disabled')
                                if(noBg){
                                    c.parentNode.parentNode.querySelector('.inpElmBGColor').classList.add('disabled')
                                }
                                i.dataset.type = noBg ? 'no-bg' : ''
                                i.dataset.iconsrc = theSrc
                                prepareColChange(i)
                            }
                        });
                    });
                });

                newNode.querySelectorAll('.inpElmColor').forEach(c => {
                    c.addEventListener('input', function () {
                        const src = this.getAttribute('data-srcid');
                        document.getElementById(src).childNodes.forEach(i => {
                            if (i.classList?.contains('social-button-icon')) {
                                i.style.color = this.value;
                            }
                        });
                    });
                });

                newNode.querySelectorAll('.inpElmBGColor').forEach(c => {
                    c.addEventListener('click', e => {
                        if(c.classList.contains('disabled')){
                            return
                        }
                        _cb.uo.saveForUndo(true);
                        var elm = e.target;
                        _cb.colorPicker.open(function (color) {
                            
                            elm.style.backgroundColor = color; // preview
                            const src = c.getAttribute('data-srcid');
                            socialIcon = document.getElementById(src).querySelector('img')
                            socialIcon.dataset.bgnew = rgb2hex(color)
                            prepareColChange(socialIcon)
                            //document.getElementById(src).style.backgroundColor = color;
                        }, c.style.backgroundColor, e.currentTarget);
                    });
                });
                newNode.querySelectorAll('.inpElmIconColor').forEach(c => {
                    c.addEventListener('click', e => {
                        _cb.uo.saveForUndo(true);
                        var elm = e.target;
                        _cb.colorPicker.open(function (color) {
                            elm.style.backgroundColor = color; // preview
                            const src = c.getAttribute('data-srcid');
                            socialIcon = document.getElementById(src).querySelector('img')
                            console.log(color)
                            socialIcon.dataset.iconnew = rgb2hex(color)
                            prepareColChange(socialIcon)
                            //document.getElementById(src).style.backgroundColor = color;
                        }, c.style.backgroundColor, e.currentTarget);
                    });
                });
            });

        });
    };

    setTimeout(process, 0);

    var oldgetCC = _cb.opts.onClearingControls;
    _cb.opts.onClearingControls = function (e) {
        const iframeWindow = document.getElementById('socialmediacontroller-frame').contentWindow;
        const iframeDocument = iframeWindow.document;
        iframeDocument.hidePopUp();
        var ret = oldgetCC.apply(this, arguments);
        return ret;
    }
    //Extend onContentClick
    var oldget = _cb.opts.onContentClick;
    _cb.opts.onContentClick = function (e) {
        let elm = e.target;
        var ret = oldget.apply(this, arguments);

        container = elm;

        while (container && !container.classList?.contains('social-media-buttons-holder')) {
            container = container.parentElement;
        }

        if (!container) {
            const chs = elm.querySelectorAll('*');
            for (let i = 0; i < chs.length; i++) {
                const c = chs[i];
                if (c.classList?.contains('social-media-buttons-holder')) {
                    container = c;
                    break;
                }
            }
        }

        let html = '';

        if (!container) {
            return ret;
        }

        if (!container.classList?.contains('social-buttons-container')) {
            container = container.querySelector('.social-buttons-container');
        }

        if (!container) {
            return ret;
        }
        
        document.getElementById('quick-settings-title').innerText = "Social Media Settings";
        _cb.showLeftSidePanel('social-media-html');

        setTimeout(() => {
            const iframeWindow = document.getElementById('socialmediacontroller-frame').contentWindow;
            const iframeDocument = iframeWindow.document;
            const controller = iframeDocument.querySelector('#socialmediacontroller-container .body .table-body');

            container.querySelectorAll('.social-button-link').forEach(c => {
                
                if (c instanceof Element) {
                    if (c.id === "") {
                        c.id = _cb.util.makeId();
                    }
                    let icon = null;
                    c.childNodes.forEach(i => {
                        if (i.classList?.contains('social-button-icon')) {
                            icon = i;
                        }
                    });

                    const fullFontSize = getStyle(window, icon.parentNode, 'width');
                    const fontnSize = getFontSizeAndUnit(fullFontSize);
                    const currentColor = getStyle(window, icon, 'color');
                    const currentLink = c.getAttribute('href');
                    const bgColor = c.querySelector('img').dataset.bgnew;
                    const iconColor = c.querySelector('img').dataset.iconnew;
                    const iconImg = c.querySelector('img').dataset.iconsrc;
                    const noBg = c.querySelector('img').dataset.type == 'no-bg' ? 'disabled' : '';

                    // let iconName = icon.src.split('/');
                    // iconName = iconName[iconName.length - 1];
                    // iconName = 'icon ' + iconName;
                    // iconName = iconName.replaceAll('.png', '');
                    // iconName = iconName.replaceAll('-white', '');

                    if (!!bgColor) {
                        if (iframeDocument.getElementById('table-bg-header'))
                            iframeDocument.getElementById('table-bg-header').style.display = 'block';
                    } else {
                        if (iframeDocument.getElementById('table-bg-header'))
                            iframeDocument.getElementById('table-bg-header').style.display = 'none';
                    }
                    html += parepareButtonPanel(c.id, c.getAttribute('data-title'), fontnSize.size, fontnSize.unit, iconImg, currentColor, currentLink, iconColor, bgColor, noBg);
                }
            });
            controller.innerHTML = html;

            controller.querySelectorAll('.inpElmTitle').forEach(c => {
                c.addEventListener('keyup', function () {
                    const src = this.getAttribute('data-srcid');
                    document.getElementById(src).setAttribute('data-title', this.value);
                });
            });

            controller.querySelectorAll('.inpElmTrash').forEach(c => {
                c.addEventListener('click', function () {
                    const chs = controller.querySelectorAll('tr');
                    if (chs.length === 1)
                        return;
                    const src = this.getAttribute('data-srcid');
                    document.getElementById(src).remove();
                    this.parentNode.parentNode.remove();
                });
            });

            controller.querySelectorAll('.inpElmSize').forEach(c => {
                c.addEventListener('input', function () {
                    console.log('change')
                    const src = this.getAttribute('data-srcid');
                    let unit = '';
                    this.parentNode.childNodes.forEach(n => {
                        if (n.classList?.contains('inpElmSizeUnit')) {
                            unit = n.value;
                        }
                    });
                    document.getElementById(src).childNodes.forEach(i => {
                        if (i.classList?.contains('social-button-icon')) {
                            console.log('social')
                            i.parentNode.style.width = this.value + unit;
                            i.parentNode.style.height = this.value + unit;
                            // const perc = (1 - i.getBoundingClientRect().height / i.parentNode.getBoundingClientRect().height);
                            // if (!!i.parentNode.style.backgroundColor) {
                            //     i.parentNode.style.width = this.value + unit;
                            //     i.parentNode.style.height = this.value + unit;

                            //     const newMargin = this.value * perc / 2;

                            //     i.style.marginTop = newMargin + 'px';
                            // }
                        }
                    });
                });
            });

            controller.querySelectorAll('.inpElmLink').forEach(c => {
                c.addEventListener('keyup', function () {
                    const src = this.getAttribute('data-srcid');
                    document.getElementById(src).setAttribute('href', this.value);
                });
            });

            controller.querySelectorAll('.inpElmSizeUnit').forEach(c => {
                c.addEventListener('change', function () {
                    
                    const src = this.getAttribute('data-srcid');
                    let size = '';
                    this.parentNode.childNodes.forEach(n => {
                        if (n.classList?.contains('inpElmSize')) {
                            size = n.value;
                        }
                    });
                    document.getElementById(src).childNodes.forEach(i => {
                        if (i.classList?.contains('social-button-icon')) {
                            i.style.width = size + this.value;
                            i.style.height = size + this.value;
                            if (!!i.parentNode.style.backgroundColor) {
                                const newSize = size * 1.875;
                                i.parentNode.style.width = newSize + unit;
                                i.parentNode.style.height = newSize + unit;
                                i.style.margin = (size * 0.42) + 'px'
                            }
                        }
                    });
                });
            });

            controller.querySelectorAll('.inpElmIcon').forEach(c => {
                
                const src = c.getAttribute('data-srcid');
                c.addEventListener('click', function (e) {
                    iframeDocument.showPopUp(e, c);
                });
                c.addEventListener('inpElmIconchange', function (name) {
                    document.getElementById(src).childNodes.forEach(i => {
                        if (i.classList?.contains('social-button-icon')) {
                            i.src = name.detail
                            i.dataset.img = ""
                            theSrc = c.querySelector('img').src
                            noBg = theSrc.includes('round') || theSrc.includes('square') ? false : true
                            c.parentNode.parentNode.querySelector('.inpElmBGColor').classList.remove('disabled')
                            if(noBg){
                                c.parentNode.parentNode.querySelector('.inpElmBGColor').classList.add('disabled')
                            }
                            i.dataset.type = noBg ? 'no-bg' : ''
                            i.dataset.iconsrc = theSrc
                            prepareColChange(i)
                        }
                    });
                });
            });

            controller.querySelectorAll('.inpElmColor').forEach(c => {
                c.addEventListener('input', function () {
                    const src = this.getAttribute('data-srcid');
                    document.getElementById(src).childNodes.forEach(i => {
                        if (i.classList?.contains('social-button-icon')) {
                            i.style.color = this.value;
                        }
                    });
                });
            });
            controller.querySelectorAll('.inpElmBGColor').forEach(c => {
                c.addEventListener('click', e => {
                    if(c.classList.contains('disabled')){
                        return
                    }
                    _cb.uo.saveForUndo(true);
                    var elm = e.target;
                    _cb.colorPicker.open(function (color) {
                        
                        elm.style.backgroundColor = color; // preview
                        const src = c.getAttribute('data-srcid');
                        socialIcon = document.getElementById(src).querySelector('img')
                        socialIcon.dataset.bgnew = rgb2hex(color)
                        prepareColChange(socialIcon)
                        //document.getElementById(src).style.backgroundColor = color;
                    }, c.style.backgroundColor, e.currentTarget);
                });
            });
            controller.querySelectorAll('.inpElmIconColor').forEach(c => {
                c.addEventListener('click', e => {
                    _cb.uo.saveForUndo(true);
                    var elm = e.target;
                    _cb.colorPicker.open(function (color) {
                        elm.style.backgroundColor = color; // preview
                        const src = c.getAttribute('data-srcid');
                        socialIcon = document.getElementById(src).querySelector('img')
                        socialIcon.dataset.iconnew = rgb2hex(color)
                        prepareColChange(socialIcon)
                        //document.getElementById(src).style.backgroundColor = color;
                    }, c.style.backgroundColor, e.currentTarget);
                });
            });
        }, 0);
        return ret;
    };

})();
function imgToData(url, callback) {
    var xhr = new XMLHttpRequest()
    xhr.onload = function() {
      var reader = new FileReader()
      reader.onloadend = function() {
        callback(reader.result)
      }
      reader.readAsDataURL(xhr.response);
    };
    xhr.open('GET', url)
    xhr.responseType = 'blob'
    xhr.send();
  }
function prepareColChange(img){
    //convert src png image to data image
    //var img = document.getElementById(id);
    if(!img.dataset.img){
        imgToData(img.src, function(dataUrl) {
                setImage(img, dataUrl,function(){
                    changeColInUri(img)
                })
            })
            
    }else{
        console.log('changge')
        changeColInUri(img)
    }
}
function setImage(img, data, callback){
    //add data image to img both data-img and src, for color change
    img.dataset.img = data
    img.src = data
    callback('success')
}
function tempImg(img2,callback){
    setTimeout(() => {
        img2.src = data
        img2.style.visibility = "hidden"
        document.body.appendChild(img2)
        callback('success')
    }, 50);
    
}
function changeColInUri(img) {
    data = img.dataset.img
    // add temp image inorder to get full height/width
    var img2 = document.createElement("img")
    img2.src = data
    img2.style.visibility = "hidden"
    document.body.appendChild(img2)
    tempImg(img2,function(){
        var canvas = document.createElement("canvas")
        canvas.width = img2.offsetWidth
        canvas.height = img2.offsetHeight

        var ctx = canvas.getContext("2d")
        ctx.drawImage(img2,0,0)

        // remove temp image
        img2.parentNode.removeChild(img2)
        
        // do color replacement
        var imageData = ctx.getImageData(0,0,canvas.width,canvas.height)
        var data = imageData.data
        
        var rgbbgfrom = toRGB(img.dataset.bg)
        var rgbbgto = toRGB(img.dataset.bgnew)
        var rgbiconfrom = toRGB(img.dataset.icon)
        var rgbiconto = toRGB(img.dataset.iconnew)
        var r,g,b;
        if(img.dataset.type == 'no-bg'){
            rgbbgto = rgbiconto
        }
        for(var x = 0, len = data.length; x < len; x+=4) {
            r = data[x];
            g = data[x+1];
            b = data[x+2];
            var theCol = {r,g,b}
            if(nearColorMatch(theCol, rgbbgfrom)) {
                data[x] = rgbbgto.r
                data[x+1] = rgbbgto.g
                data[x+2] = rgbbgto.b
            }
            if(img.dataset.type != 'no-bg'){
                if(nearColorMatch(theCol, rgbiconfrom)) {
                    data[x] = rgbiconto.r
                    data[x+1] = rgbiconto.g
                    data[x+2] = rgbiconto.b
                }
            }
        }
        ctx.putImageData(imageData,0,0);
        img.src = canvas.toDataURL()
        return
    })
    
}

function toRGB(hexStr) {
    var col = {};
    col.r = parseInt(hexStr.substr(1,2),16)
    col.g = parseInt(hexStr.substr(3,2),16)
    col.b = parseInt(hexStr.substr(5,2),16)
    return col;
}
function nearColorMatch(color1, color2) {
    tolerance = 120;
    return Math.abs(color1.r - color2.r) <= tolerance
        && Math.abs(color1.g - color2.g) <= tolerance
        && Math.abs(color1.b - color2.b) <= tolerance;
}