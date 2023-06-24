<?php

$requestMethod = $_SERVER['REQUEST_METHOD'];

if($requestMethod == 'POST') {
	if(isset($_POST['session'])) {
		setSession($_POST['session']);
        die();
	}
}

function setSession($session) {
	if(get_option('escribelo_session') == null) add_option('escribelo_session', $session);
	else update_option('escribelo_session', $session, true);
}

?>


<div class="wrap" style="color: #1d2327;">
	<h1 style="font-weight: 700;">Inicia sesión en tu cuenta de Escríbelo</h1>
    <p>Inicia sesión y comienza a generar artículos de calidad optimizado para SEO dentro de tu página web. Los artículos generados se subirán automáticamente a tu web.</p>
    <br><br>
	<form onsubmit="event.preventDefault();" id="escribelo-login" action="/" method="POST">
		<label style="color: #1d2327;font-weight: 600;">Correo electrónico</label><br>
		<input id="email" type="email" placeholder="Tu correo" required><br><br>
		<label style="color: #1d2327;font-weight: 600;">Contraseña</label><br>
		<input id="password" type="password" placeholder="Tu contraseña" required>
		<br><br><br>
		<button id="login" class="button button-primary" type="submit">Iniciar sesión</button>
        <br>
        <p id="result"></p>
	</form>
</div>

<script>
    document.addEventListener("keyup", function(event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            login();
        }
    });

    document.getElementById('login').addEventListener('click', function(e) {
        login();
    });

    function login() {
        document.getElementById('login').setAttribute('disabled', true);

        var http = new XMLHttpRequest();
        http.open("POST", "https://app.escribelo.ai/core/authentication.php", true);
        http.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        let email = document.getElementById('email').value;
        let password = document.getElementById('password').value;

        if(email == '' || password == '') return;

        let params = 'Mail=' + email + '&Password=' + password + '&rememberLogin=' + true;

        http.onreadystatechange = function() {
            if(http.readyState == 4 && http.status == 200) {
                let data = JSON.parse(this.responseText);
                let status = data.success;
                if(status == "false") {
                    jQuery('#result').html('<p class="error" id="result">Correo o contraseña incorrecto</p>');
                } else {
                    let accessToken = data.accessToken;
                    let wordsLimit = data.wordsLimit;
                    if(wordsLimit == 0) {
                        document.getElementById('result').outerHTML = '<p style="padding: 20px;" class="error" id="result">Necesitas un plan para poder acceder a esta función. Puedes adquirirlo en <a href="https://escribelo.ai/precios/" target="_blank">https://escribelo.ai/precios/</a></p>';
                        document.getElementById('login').removeAttribute('disabled');
                        return;
                    } else if(wordsLimit <= 20000 || data.plan.includes('Infinity') || data.plan.includes('Premium20k')) {
                        document.getElementById('result').outerHTML = '<p style="padding: 20px;" class="error" id="result">Necesitas un plan superior para poder acceder a esta función. Puedes subir de plan en <a href="https://app.escribelo.ai/settings/subscription" target="_blank">https://app.escribelo.ai/settings/subscription</a></p>';
                        document.getElementById('login').removeAttribute('disabled');
                        return;
                    }

                    let http = new XMLHttpRequest();
                    http.open("POST", "", true);
                    http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

                    http.onreadystatechange = function() {
                        if (this.readyState == 4 && this.status == 200) {
                            window.location.href = window.location.href.replace('login_menu.php', 'main_menu.php');
                        }
                    };
                    http.send('session=' + accessToken);
                }
            }
        }
        http.send(params);
    }
</script>

<style>
    .error {
        border: 1px solid rgb(158, 10, 5);
        background-color: #ee371794;
        color: white;
        text-align: center;
        font-size: 14px;
        padding: 2%;
        display: inline-block;
        border-radius: 0.65rem;
    }
</style>