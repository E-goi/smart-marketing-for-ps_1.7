/**
 * Smart Marketing
 *
 *  @author    E-goi
 *  @copyright 2018 E-goi
 *  @license   LICENSE.txt
 */

var _targettedModal,
    showOnce = document.querySelector('[data-modal-show]'),
    _dismiss = document.querySelectorAll('[data-modal-dismiss]'),
    modalActiveClass = "is-modal-active";

function showModal() {
    _targettedModal = document.querySelector('[data-modal-name="egoi-form"]');
    _targettedModal.classList.add(modalActiveClass);
}

function hideModal(event) {
    if(event === undefined || event.target.hasAttribute('data-modal-dismiss')) {
        _targettedModal.classList.remove(modalActiveClass);
    }
}

function bindEvents(el, callback) {
    for (i = 0; i < el.length; i++) {
        (function(i) {
            el[i].addEventListener('click', function(event) {
                callback(this, event);
            });
        })(i);
    }
}

function triggerModal() {
    showModal();
}

function dismissModal() {
    bindEvents(_dismiss, function(that) {
        hideModal(event);
    });
}

function initModal() {

    if (showOnce.innerText == 1) {
        var cookie = getCookie('show_popup');
        if (cookie == 0) {
            setCookie('show_popup', 1);
            triggerModal();
        }

    }else{
        setCookie('show_popup', 0);
        triggerModal();
    }

    dismissModal();
}

function setCookie(name, value) {
    var date = new Date();
    date.setTime(date.getTime() + (7*24*60*60*1000));
    var expires = "; expires=" + date.toUTCString();
    document.cookie = name + "=" + (value || "")  + expires + "; path=/";
}

function getCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');

    for(var i=0; i<ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) ===' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
}

initModal();