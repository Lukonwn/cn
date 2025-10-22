$("body").focus();

/*Troca de painel de senha - tela Login
$( ".bt-seguinte" ).click(function() {
  $( ".painel-login-login" ).fadeToggle("fast", function() {
    $( ".painel-login-password" ).fadeToggle("fast", "linear");
      $('.painel-cadastro').css('height', '325');
      alert(alturaBox);
    $( "#campo-senha" ).focus();
  });
});*/

/*Controle do alto contraste*/


function toggleAltoContraste() {
    var className = document.body.className;
    if (className == 'login') {
        document.cookie = "Gov_Br_Contrast_Active=true; path=/";
    } else {
        document.cookie = "Gov_Br_Contrast_Active=false; path=/";
    }

    $('body').toggleClass('alto-contraste');
}

function updateContrastPage() {
    var value = "; " + document.cookie;
    var parts = value.split("; Gov_Br_Contrast_Active=");
    if (parts.length == 2)
        var cookieValue = parts.pop().split(";").shift();
    if (cookieValue !== undefined && cookieValue == 'true') {
        toggleAltoContraste();
    }
}

function cpfMaskOnInput(event) {
    var value = event.target.value;
    var caret = event.target.selectionStart;
    var previousValue = event.target.previousValue;

    //    if(value.length === 13) {
    if (value + "-" === previousValue || value + "." === previousValue) {
        if (caret === 3 && value.substring(3, 4) !== ".") {
            var prefix = value.substring(0, 2);
            var suffix = value.substring(3);
            value = prefix + suffix;
            caret--;
        } else if (caret === 7 && value.substring(7, 8) !== ".") {
            var prefix = value.substring(0, 6);
            var suffix = value.substring(7);
            value = prefix + suffix;
            caret--;
        } else if (caret === 11 && value.substring(11, 12) !== "-") {
            var prefix = value.substring(0, 10);
            var suffix = value.substring(11);
            value = prefix + suffix;
            caret--;
        }
    }

    for (var i = value.length - 1; i >= 0; i--) {
        var char = value[i];
        if (char >= "0" && char <= "9") {
            continue;
        }

        var prefix = value.substring(0, i);
        var suffix = value.substring(i + 1);
        value = prefix + suffix;
        if (caret > i) {
            caret--;
        }
    }

    if (value.length > 11) {
        value = value.substring(0, 11);
    }


    //    for (var i = value.length; i < 11; i++) {
    //        value += "_";
    //    }


    if (value.length >= 3) {
        var prefix = value.substring(0, 3);
        var suffix = value.substring(3);
        value = prefix + "." + suffix;
        if (caret >= 3) {
            caret++;
        }
    }

    if (value.length >= 7) {
        var prefix = value.substring(0, 7);
        var suffix = value.substring(7);
        value = prefix + "." + suffix;
        if (caret >= 7) {
            caret++;
        }
    }

    if (value.length >= 11) {
        var prefix = value.substring(0, 11);
        var suffix = value.substring(11);
        value = prefix + "-" + suffix;
        if (caret >= 11) {
            caret++;
        }
    }

    event.target.value = value;
    event.target.selectionStart = caret;
    event.target.selectionEnd = caret;
    event.target.previousValue = value;
}

function cpfMaskOnFocus(event) {
    var textInput = event.target;
    if (textInput.value === "") {
        //        textInput.value = "___.___.___-__";
    }
    for (var i = textInput.value.length - 1; i >= 0; i--) {
        var char = textInput.value[i];
        if (char >= "0" && char <= "9") {
            textInput.selectionStart = i + 1;
            textInput.selectionEnd = i + 1;
            return;
        }
    }
    textInput.selectionStart = 0;
    textInput.selectionEnd = 0;
}

function cpfMaskOnBlur(event) {
    var textInput = event.target;
    if (textInput.value === "___.___.___-__") {
        //        textInput.value = "";
    }
}

function cpfMask(inputName) {
    var textInput = document.getElementById(inputName);
    textInput.addEventListener("input", cpfMaskOnInput);
    textInput.addEventListener("focus", cpfMaskOnFocus);
    textInput.addEventListener("blur", cpfMaskOnBlur);
}

function phoneNumberMask(inputName) {
    var textInput = document.getElementById(inputName);
    textInput.addEventListener("input", phoneNumberMaskOnInput);
}

function phoneNumberMaskOnInput(event) {
    event.target.value = event.target.value.replace(/\D/g, '').replace(/(\d{0,2})(\d{0,5})(\d{0,4})/, "($1) $2-$3").replace(/(.*\d)?\D*/, "$1");
}

function numberMask(inputName) {
    var textInput = document.getElementById(inputName);
    textInput.addEventListener("input", numberMaskOnInput);
}

function numberMaskOnInput(event) {
    event.target.value = event.target.value.replace(/\D/g, '');
}


function disable(button, mitreApprovedFlag) {
    button.disabled = true;
    if (mitreApprovedFlag === undefined) {
        document.getElementById('loginData').submit();
        return;
    }
    $('#user_oauth_approval').attr('value', mitreApprovedFlag);
    document.getElementById('confirmationFormId').submit();
}

$('.link-alto-contraste').click(function() {
    toggleAltoContraste();
});

/*Visualizar senha*/
$(".toggle-password").click(function() {

    $(this).toggleClass("fa-eye fa-eye-slash");
    var input = $($(this).attr("toggle"));
    if (input.attr("type") == "password") {
        input.attr("type", "text");
    } else {
        input.attr("type", "password");
    }
});

function validateCertificate() {
    var nrOpt = $('#sltProvider > option').length;
    if (nrOpt > 1) {
        var vl = $("#sltProvider").val();
        if (vl == '') {
            event.preventDefault();
            $('#msgScr').text('Selecione um provedor');
            $('#msgScr').addClass('alert-warning')
            return false;
        }
    }
    return;
}

$(document).ready(function() {
    updateContrastPage();
});

function callLink(e, url) {
    e.preventDefault();
    window.location.assign(url);
}

function focusOnLoad(elementId) {
    window.addEventListener("load", function() {
        document.getElementById(elementId).focus();
    });
}

function submitOnEnterKeyPress(submitButtonId) {
    this.addEventListener('keypress', function(e) {
        var enterAllowed = e.ctrlKey == false && e.shiftKey == false && e.altKey == false && e.metaKey == false;
        if (enterAllowed && e.key == 'Enter') {
            e.preventDefault();
            document.getElementById(submitButtonId).click();
        }
    });
}

function focusOnEnterKeyPress(elementId) {
    this.addEventListener('keypress', function(e) {
        var enterAllowed = e.ctrlKey == false && e.shiftKey == false && e.altKey == false && e.metaKey == false;
        if (enterAllowed && e.key == 'Enter') {
            e.preventDefault();
            document.getElementById(elementId).focus();
        }
    });
}