/*
    Hello World Plugin
*/

(function () {

    var html = `
    <div class="toast-container" id="toast-container">
        <p class="toast-message">this is simple toast</p>
        <i class="toast-close icon ion-android-close"></i>
    </div>
    `;
    _cb.addHtml(html);

    let timeoutId = null;
    const showToast = (message,type,ms) => {
        ms = ms > 0 ? ms : 2000
        const toast = document.getElementById('toast-container');
        if(timeoutId) {
            toast.style.display = 'none';
            clearTimeout(timeoutId);
        }
        toast.querySelector('p').innerText = message;
        toast.style.display = 'block';
        
        if(type != 'error'){
            toast.classList.remove('error') 
        }else{
            toast.classList.add('error')
        }
        timeoutId = setTimeout(() => toast.style.display = 'none', ms);
    }

    setTimeout(() => {
        document.getElementById('toast-container').querySelector('.toast-close').addEventListener('click', e => {
            document.getElementById('toast-container').style.display = 'none';
            clearTimeout(timeoutId);
        });
    }, 0);

    window.ShowToast = showToast;

})();