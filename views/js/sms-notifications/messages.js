var smsMessages = {};

function insertText(text) {
    if (smsMessages.lastFocused == undefined) {
        return;
    }

    var input = smsMessages.lastFocused;
    var scrollPos = input.scrollTop;
    var pos = 0;
    var browser = ((input.selectionStart || input.selectionStart == "0")
        ? "ff"
        : (document.selection ? "ie" : false ) );

    if (browser == "ie") {
        input.focus();
        var range = document.selection.createRange();
        range.moveStart ("character", -input.value.length);
        pos = range.text.length;
    } else if (browser == "ff") {
        pos = input.selectionStart
    }

    var front = (input.value).substring(0, pos);
    var back = (input.value).substring(pos, input.value.length);
    input.value = front+text+back;
    pos = pos + text.length;

    if (browser == "ie") {
        input.focus();
        var range = document.selection.createRange();
        range.moveStart ("character", -input.value.length);
        range.moveStart ("character", pos);
        range.moveEnd ("character", 0);
        range.select();
    } else if (browser == "ff") {
        input.selectionStart = pos;
        input.selectionEnd = pos;
        input.focus();
    }

    input.scrollTop = scrollPos;
}

$(document).ready(function() {
    $("textarea").focus(function () {
        smsMessages.lastFocused = document.activeElement;
    });
});
