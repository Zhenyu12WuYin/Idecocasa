<?php

require_once(plugin_dir_path(__FILE__).'../core/utils.php');
require_once(plugin_dir_path(__FILE__).'../core/articles_system/ArticleGeneration.php');

$requestMethod = $_SERVER['REQUEST_METHOD'];

if($requestMethod == 'POST') {
    $body = file_get_contents('php://input');
	if(isJson($body)) {
		$data = json_decode($body, true);
        $items = [];
        foreach($data['items'] as $d) {
            $items[] = ['article' => new Article($d['title'], $d['keywords'], $data['config']['type']), 'config' => new ArticleConfig(
	            get_cat_ID($data['config']['categoryId']), $data['config']['language'], $data['config']['status'], $d['time'], $data['config']['bankImagesType'], $data['config']['api_key'])];
        }
	    startGenerationTasks($items);
    }
    if(isset($_POST['action'])) {
	    update_option('escribelo_tasks', []);
    }
    return;
}

$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, "https://app.escribelo.ai/api/v1/accounts/");
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HTTPHEADER, [
	"Authorization: Bearer ".get_option('escribelo_session')
]);

$response = curl_exec($curl);
curl_close($curl);

$json = json_decode($response, true);

if($json['error'] || strpos($json['plan'], 'Infinity') || strpos($json['plan'], 'Premium20k') || strpos($json['plan'], 'LifeTime')) {
	delete_option('escribelo_session');
	header('Location: ' . str_replace('main_menu.php', 'login_menu.php', "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']));
	die('<script> window.location.href = "'.str_replace('main_menu.php', 'login_menu.php', "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']).'"</script>');
}



?>
<div class="wrap" style="color: #1d2327;">
    <?php
        $notif = retrieveNotifications('1.0.8');
        if($notif != null) {
            echo '<div style="display: flex;justify-content: center;">
                        <div style="margin-bottom: 20px;margin-top: 10PX;background: #00657c47;padding: 0.5% 1.5% 0.5% 1.5%;width: 70%;border: 2px solid #00657C;">
                            <h3>'.$notif['title'].'</h3>
                            <p>'.$notif['description'].'</p>
                        </div>
                    </div>';
        }
    ?>
    <h1 style="font-weight: 700;">Generar artículos</h1>
    <p>Rellena las siguientes casillas y comienza a generar artículos de calidad optimizado para SEO dentro de tu página web. Los artículos generados se subirán automáticamente.</p>
    <form id="escribelo_form" action="/" method="POST">
        <label style="color: #1d2327;font-weight: 600;">Escribe los títulos de los artículos*</label><br>
        <textarea style="width: 60%;" id="titles" name="titles" rows="5" placeholder="Título del artículo
Título del artículo,[palabra clave, palabra clave]
Título del artículo,[palabra clave]
Título del artículo
..." required></textarea>
        <p><i class="fas fa-circle-info" style="margin-right: 5px;"></i> El formato para cada línea dentro de la caja es el título del artículo que quieres generar (Ejemplo: Título del artículo), en caso de que necesites poner palabras clave que es totalmente opcional, sería por ejemplo: Título del artículo,[palabra clave, palabra clave] o Título del artículo,[palabra clave].</p>
        <label style="color: #1d2327;font-weight: 600;">Asigna una categoría a la que se publiquen los artículos generados</label><br>
        <select id="category" name="category">
			<?php
			foreach(get_categories(array('hide_empty' => false)) as $category) {
				echo '<option value="'.$category->name.'">'.$category->name.'</option>';
			}
			?>
        </select>
        <br><br>
        <label style="color: #1d2327;font-weight: 600;">Selecciona el idioma que se generarán los artículos</label><br>
        <select id="language" name="language">
            <option value="ES">🇪🇸  Español</option>
            <option value="EN">🇺🇸  Inglés</option>
            <option value="SK">🇸🇰  Eslovaco</option>
            <option value="BG">🇧🇬  Búlgaro</option>
            <option value="CS">🇨🇿  Checo</option>
            <option value="DA">🇩🇰  Danés</option>
            <option value="DE">🇩🇪  Alemán</option>
            <option value="EL">🇬🇷  Griego</option>
            <option value="ET">🇪🇪  Estoniano</option>
            <option value="FI">🇫🇮  Finlandés</option>
            <option value="FR">🇫🇷  Francés</option>
            <option value="HU">🇭🇺  Húngaro</option>
            <option value="ID">🇮🇩  Indonesio</option>
            <option value="IT">🇮🇹  Italiano</option>
            <option value="JA">🇯🇵  Japonés</option>
            <option value="LT">🇱🇹  Lituano</option>
            <option value="LV">🇱🇻  Letón</option>
            <option value="NL">🇳🇱  Holandés</option>
            <option value="PL">🇵🇱  Polaco</option>
            <option value="RO">🇷🇴  Rumano</option>
            <option value="RU">🇷🇺  Ruso</option>
            <option value="PT">🇵🇹  Portugués</option>
            <option value="SL">🇸🇮  Esloveno</option>
            <option value="SV">🇸🇪  Sueco</option>
            <option value="TR">🇹🇷  Turco</option>
            <option value="UK">🇺🇦  Ucraniano</option>
            <option value="ZH">🇨🇳  Chino</option>
        </select>
        <br><br>
        <label style="color: #1d2327;font-weight: 600;">¿Cómo quieres que sea la publicación del contenido?</label><br>
        <select id="time">
            <option value="1">Publicar todos los contenidos directamente</option>
            <option value="0">Publicar todos los contenidos progresivamente</option>
            <option value="2">Poner como borrador todos los contenidos</option>
        </select>
        <br><br>
        <label style="color: #1d2327;font-weight: 600;">Longitud del artículo</label><br>
        <div class="buttonSelect">
            <button class="btn btn-primary buttonSelect-button-selected" data-value="Largo">Largo</button>
            <button class="btn btn-primary buttonSelect-button" data-value="Mediano">Mediano</button>
        </div>
        <br>
        <p id="time-selector" style="color: #1d2327; display: none;">Publicar <input id="publicate-count-articles" min="1" type="number" value="30" style="max-width: 89px;"> artículos cada <input id="publicate-days-articles" type="number" value="1" style="max-width: 89px;"> días</p>
        <label style="color: #1d2327;font-weight: 600;"><select id="bankImagesType"><option>Pexels</option><option>Pixabay</option><option>Unsplash</option></select> API key (opcional)</label><br>
        <p>En caso de que necesites que tus artículos tengan una imagen destacada, puedes obtener una api key <a id="bank_image_link" href="https://www.pexels.com/api/new/" target="_blank">aquí</a> totalmente gratis para que el plugin extraiga una imagen desde <span id="bank_image_name">Pexels</span>.
            <br><span id="bank_image_ratelimit">Puedes obtener 200 imágenes cada hora y 20.000 cada mes de forma gratuita</span>.</p>
        <input type="text" id="pexels" name="pexels" placeholder="La clave de acceso">
        <br><br>
        <button id="request" class="button button-primary" type="submit"><span style="padding-right: 8px;"><i class="fas fa-pencil-alt"></i></span> Escríbelo</button>
        <p style="color: grey;line-height: 0.5rem; display: none;">Se ha generado <span style="font-weight: 600;" id="articles_count">0/30</span> artículos</p>
        <br>
        <br>
        <?php
            $tasks = get_option('escribelo_tasks');
            if(!is_array($tasks)) {
	            $tasks = array();
	            update_option('escribelo_tasks', array());
            }
            if(count($tasks) != 0) {
                echo '<div style="display: flex;"><h2>Artículos en progreso ('.count($tasks).')</h2><button id="cancel_all_articles" onclick="cancelAllArticles(); event.preventDefault();" style="margin-left: 20px; margin-top: 10px; margin-bottom: 10px;" class="button" type="submit"><span style="padding-right: 8px;"><i class="fas fa-stop"></i></span> Cancelar todos los artículos</button></div>';
            }
            foreach($tasks as $item) {
                echo "<p>· <b>Título:</b> ".$item['article']->getTitle()."  | <b>Tipo:</b> ".$item['article']->getType()." | <b>Publicación:</b> ".($item['config']->getTime() < time() ? 'En cola' : date('d/m/Y H:i', $item['config']->getTime()))."</p>";
            }
        ?>
        <br>
        <br>
    </form>

    <link rel="stylesheet" href="https://app.escribelo.ai/vendor/tippy.js/dist/tippy.css" />
    <script src="https://app.escribelo.ai/vendor/@popperjs/core/dist/umd/popper.min.js"></script>
    <script src="https://app.escribelo.ai/vendor/tippy.js/dist/tippy-bundle.umd.js"></script>

    <script>
        document.getElementById('bankImagesType').addEventListener('change', function(e) {
            let value = e.target.value;
            switch(value) {
                case 'Pexels': {
                    jQuery('#bank_image_name').text('Pexels');
                    jQuery('#bank_image_ratelimit').text('Puedes obtener 200 imágenes cada hora y 20.000 cada mes de forma gratuita');
                    jQuery('#bank_image_link').attr('href', 'https://www.pexels.com/api/new/');
                    break;
                }
                case 'Pixabay': {
                    jQuery('#bank_image_name').text('Pixabay');
                    jQuery('#bank_image_ratelimit').text('Puedes hacer hasta 100 imágenes cada minuto de forma gratuita');
                    jQuery('#bank_image_link').attr('href', 'https://pixabay.com/accounts/login/?next=/api/docs/');
                    break;
                }
                case 'Unsplash': {
                    jQuery('#bank_image_name').text('Unsplash');
                    jQuery('#bank_image_ratelimit').text('Puedes hacer hasta 50 imágenes cada hora de forma gratuita sin aplicar a una verificación.');
                    jQuery('#bank_image_link').attr('href', 'https://unsplash.com/developers');

                }
            }
        });

        tippy('[data-value="Largo"]', {
            allowHTML: true,
            content: '<span style="display: block; text-align: center;">Es recomendable si son temas de alta competencia.<br>Palabras: 1000-2500</span>'
        });
        tippy('[data-value="Mediano"]', {
            allowHTML: true,
            content: '<span style="display: block; text-align: center;">Es recomendable si son preguntas concretas.<br>Palabras: 500-1000</span>'
        });

        function sleep(ms) {
            return new Promise(resolve => setTimeout(resolve, ms));
        }

        let count = 0;
        let daysCount = 0;
        let articles = 0;
        let articlesCreated = 0;

        let started = false;
        let inProcess = [];
        let uploaded = [];

        document.getElementById('escribelo_form').addEventListener('submit', async function(e) {
            if(started) {
                e.preventDefault();
                return;
            }
            started = true;
            e.preventDefault();

            let numDays = parseInt(document.getElementById('publicate-days-articles').value);
            let numArticles = parseInt(document.getElementById('publicate-count-articles').value);

            count = 0;
            articles = 0;
            articlesCreated = 0;

            document.getElementById('request').setAttribute("disabled", true);
            let titles = document.getElementById('titles').value;
            let titlesList = titles.split('\n');
            titlesList = [...new Set(titlesList)];

            articles = titlesList.length;

            //Data
            let type = buttonSelectVal;
            let categoryId = jQuery('#category').val();
            let language = jQuery('#language').val();
            let status = (jQuery('#time').val() == 2 ? 'draft' : 'publish');
            let time = Math.floor(Date.now() / 1000);
            let bankImagesType = jQuery('#bankImagesType').val();
            let bankImagesApiKey = jQuery('#pexels').val();

            let articleItems = [];
            for (let title of titlesList) {
                let keywords = null;

                if(title.includes('[') && title.includes(']')) {
                    let str = title.replaceAll(', [', ',[');
                    let start = str.indexOf("[") + 1;
                    let end = str.indexOf("]");
                    keywords = str.substring(start, end);
                    title = str.substring(0, str.indexOf("[")).trim().replaceAll(',', '');
                }

                if(count >= numArticles) {
                    daysCount += numDays;
                    count = 0;
                }

                let date = new Date();
                date.setDate(date.getDate() + (daysCount));
                if(jQuery('#time').val() === '0') time = Math.floor(date / 1000);

                articleItems.push({'title': title, 'keywords': keywords == null ? '' : keywords, 'time': time});

                count++;
            }

            uploadArticles(articleItems, type, categoryId, language, status, bankImagesType, bankImagesApiKey.length === 0 ? null : bankImagesApiKey);
        });

        function sendData(title, keywords, numArticles, numDays) {
            if(inProcess.includes(title) || uploaded.includes(title)) return;
            if(title.length === 0) return;
            inProcess.push(title);
            document.getElementById('request').setAttribute("disabled", true);
            document.getElementById('request').innerHTML = '<span style="padding-right: 14px;"><i class="fas fa-circle-notch fa-spin"></i></span>Escríbelo';

            var http = new XMLHttpRequest();
            http.open("POST", "https://app.escribelo.ai/api/v1/tools/?origin=plugin&v=1.0.7", true);
            http.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            http.setRequestHeader('Authorization', 'Bearer <?=get_option('escribelo_session')?>');

            let id = (buttonSelectVal == 'Mediano' ? '10c9bfc4-6dab-4f17-a426-ee684a8c8b96' : 'd6244efc-a545-4611-8acc-e46ccc7a8543');
            let params = 'id=' + id + '&tone=Informal' + '&q=1&value1=' + title + '&value2=&fromLanguage=ES&toLanguage=' + jQuery('#language').val()
            if(keywords != null) params = params.replace('&value2=', '&value2=' + keywords) ;
            let category = jQuery('#category').val();

            http.onreadystatechange = function() {
                if(http.readyState == 4 && http.status == 200) {
                    let data = JSON.parse(this.responseText);
                    let error = data.error;
                    if(!error) {
                        let result = data.choices[0].text;

                        if(count >= numArticles) {
                            daysCount += numDays;
                            count = 0;
                        }

                        let date = new Date();
                        date.setDate(date.getDate() + (daysCount));
                        let dateFormatted = date.toISOString().slice(0, 19).replace('T', ' ');

                        if(parseInt(jQuery('#time').val()) === 0) uploadArticle(title, category, result, dateFormatted);
                        else uploadArticle(title, category, result);

                        count++;
                    } else {
                        let error = data.message;
                        let errorMessage;
                        switch(error) {
                            case 'Must update': {
                                errorMessage = 'Para poder continuar, tienes que actualizar el plugin. Puedes encontrar la nueva versión en el apartado de integraciones.'
                                break;
                            }
                            case('Your request is missing params in the POST body'): {
                                errorMessage = 'Por favor complete todos los campos a rellenar para continuar.';
                                break;
                            }
                            case("You didn't provide your session token"): {
                                errorMessage = 'Debes de iniciar sesión de nuevo para hacer esto, recarga la página.';
                                break;
                            }
                            case('Tool not found'): {
                                errorMessage = 'Esta herramienta no fue encontrada.';
                                break;
                            }
                            case("You've reached the words of your plan"): {
                                errorMessage = 'Has superado la cantidad de palabras ofertado por el plan. Puedes comprar palabras adicionales en tu perfil.';
                                break;
                            }
                            case('The server is currently overloaded with other requests. Sorry about that! You can retry your request'): {
                                inProcess = inProcess.filter(t => t != title);
                                sendData(title, keywords, numArticles, numDays);
                                return;
                            }
                            case('The values was flagged as unsafe (content +18)'): {
                                errorMessage = 'No se pudo completar la tarea debido a que se ha detectado contenido inapropiado en los datos proporcionados.';
                                break;
                            }
                            default: {
                                errorMessage = error;
                            }
                        }

                        alert(errorMessage);
                        //ERROR
                    }
                }
                if(http.status == 0 || http.status == 524) {
                    console.log('Start tracking...');
                    trackResult(title, keywords, numArticles, numDays, category);
                }
            }
            http.send(params);
        }

        function uploadArticles(articles, type, categoryId, language, status, bankImagesType, bankImagesApiKey) {
            let http = new XMLHttpRequest();
            http.open("POST", "", true);
            http.setRequestHeader("Content-type", "application/json");

            let params = {
                'items': articles, 'config': {
                    'type': type,
                    'categoryId': categoryId,
                    'language' : language,
                    'status': status,
                    'bankImagesType': bankImagesType,
                    'api_key': bankImagesApiKey
                }
            };

            http.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    window.location.reload();
                }
            };

            http.send(JSON.stringify(params));
        }

        function restartButton() {
            daysCount = 0;
            count = 0;
            articlesCreated = 0;
            articles = 0;
            started = false;

            if(document.getElementById('request') == null) return;
            document.getElementById('request').removeAttribute("disabled");
            document.getElementById('request').innerHTML = '<span style="padding-right: 14px;"><i class="fas fa-pencil-alt"></i></span>Escríbelo';
        }

        document.getElementById('time').addEventListener('change', function(e) {
            let value = e.target.value;
            if(value == '0') jQuery('#time-selector').css('display', '');
            else jQuery('#time-selector').css('display', 'none');
        });

        function cancelAllArticles() {
            document.getElementById('cancel_all_articles').setAttribute("disabled", true);
            let http = new XMLHttpRequest();
            http.open("POST", "", true);
            http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

            let params = "action=cancel";

            http.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    window.location.reload();
                }
            };

            http.send(params);
        }

    </script>

    <style>
        .notice-warning {
            display: none;
        }
        .fas{-moz-osx-font-smoothing:grayscale;-webkit-font-smoothing:antialiased;display:inline-block;display:var(--fa-display,inline-block);font-style:normal;font-variant:normal;text-rendering:auto;}
        .fa-scissors:before{content:"\f0c4";}
        .fa-circle-plus:before{content:"\f055";}
        .fa-align-left:before{content:"\f036";}
        .fa-pencil-alt:before{content:"\f040";}
        .fa-sign-out-alt:before{content:"\f2f5";}
        .fa-circle-notch:before{content:"\f1ce";}
        .fa-down:before{content:"\f354";}
        .fa-copy:before{content:"\f0c5";}
        .fa-info-circle:before{content:"\f05a";}
        .fa-thumbs-down:before{content:"\f165";}
        .fa-thumbs-up:before{content:"\f164";}
        .fa-stop:before{content:"\f04d";}
        .fa-circle-info:before{content:"\f05a";}
        .fas{font-weight:900;}
        .fas{font-family:"Font Awesome 6 Pro";}
        .fas{-moz-osx-font-smoothing:grayscale;-webkit-font-smoothing:antialiased;display:inline-block;display:var(--fa-display,inline-block);font-style:normal;font-variant:normal;text-rendering:auto;}
        .fa-spin{-webkit-animation-name:fa-spin;animation-name:fa-spin;-webkit-animation-delay:0;animation-delay:0;-webkit-animation-delay:var(--fa-animation-delay,0);animation-delay:var(--fa-animation-delay,0);-webkit-animation-direction:normal;animation-direction:normal;-webkit-animation-direction:var(--fa-animation-direction,normal);animation-direction:var(--fa-animation-direction,normal);-webkit-animation-duration:2s;animation-duration:2s;-webkit-animation-duration:var(--fa-animation-duration,2s);animation-duration:var(--fa-animation-duration,2s);-webkit-animation-iteration-count:infinite;animation-iteration-count:infinite;-webkit-animation-iteration-count:var(--fa-animation-iteration-count,infinite);animation-iteration-count:var(--fa-animation-iteration-count,infinite);-webkit-animation-timing-function:linear;animation-timing-function:linear;-webkit-animation-timing-function:var(--fa-animation-timing,linear);animation-timing-function:var(--fa-animation-timing,linear);}
        .fa-spin {
            -webkit-animation: fa-spin 2s infinite linear;
            animation: fa-spin 2s infinite linear;
        }
        @media (prefers-reduced-motion:reduce){
            .fa-spin{-webkit-animation-delay:-1ms;animation-delay:-1ms;-webkit-animation-duration:1ms;animation-duration:1ms;-webkit-animation-iteration-count:1;animation-iteration-count:1;-webkit-transition-delay:0s;transition-delay:0s;-webkit-transition-duration:0s;transition-duration:0s;}
        }
        .fa-circle-notch:before{content:"\f1ce";}
        .fas{font-weight:900;}
        .fas{font-family:"Font Awesome 6 Pro";}
        /*! end @import */
        /*! CSS Used keyframes */
        @-webkit-keyframes fa-spin{0%{-webkit-transform:rotate(0deg);transform:rotate(0deg);}to{-webkit-transform:rotate(1turn);transform:rotate(1turn);}}
        @keyframes fa-spin{0%{-webkit-transform:rotate(0deg);transform:rotate(0deg);}to{-webkit-transform:rotate(1turn);transform:rotate(1turn);}}
        /*! CSS Used fontfaces */
        @font-face{font-family:"Font Awesome 6 Pro";font-style:normal;font-weight:300;font-display:block;src:url(https://app.escribelo.ai/icons/font-awesome/webfonts/fa-light-300.woff2) format("woff2"),url(https://app.escribelo.ai/icons/font-awesome/webfonts/fa-light-300.woff) format("woff"),url(https://app.escribelo.ai/icons/font-awesome/webfonts/fa-light-300.ttf) format("truetype");}
        @font-face{font-family:"Font Awesome 6 Pro";font-style:normal;font-weight:400;font-display:block;src:url(https://app.escribelo.ai/icons/font-awesome/webfonts/fa-regular-400.woff2) format("woff2"),url(https://app.escribelo.ai/icons/font-awesome/webfonts/fa-regular-400.woff) format("woff"),url(https://app.escribelo.ai/icons/font-awesome/webfonts/fa-regular-400.ttf) format("truetype");}
        @font-face{font-family:"Font Awesome 6 Pro";font-style:normal;font-weight:900;font-display:block;src:url(https://app.escribelo.ai/icons/font-awesome/webfonts/fa-solid-900.woff2) format("woff2"),url(https://app.escribelo.ai/icons/font-awesome/webfonts/fa-solid-900.woff) format("woff"),url(https://app.escribelo.ai/icons/font-awesome/webfonts/fa-solid-900.ttf) format("truetype");}
        @font-face{font-family:"Font Awesome 6 Pro";font-style:normal;font-weight:100;font-display:block;src:url(https://app.escribelo.ai/icons/font-awesome/webfonts/fa-thin-100.woff2) format("woff2"),url(https://app.escribelo.ai/icons/font-awesome/webfonts/fa-thin-100.woff) format("woff"),url(https://app.escribelo.ai/icons/font-awesome/webfonts/fa-thin-100.ttf) format("truetype");}
    </style>

    <script>
        let buttonSelectVal = document.getElementsByClassName('buttonSelect-button-selected')[0].getAttribute('data-value');
        document.addEventListener('click', function(e) {
            let element = e.target;

            if(element.getAttribute('class') == null || !element.getAttribute('class').includes('buttonSelect') || element.tagName != 'BUTTON') return false;
            e.preventDefault();

            jQuery('.buttonSelect-button-selected').removeClass('buttonSelect-button-selected').addClass('buttonSelect-button');
            jQuery(element).addClass('buttonSelect-button-selected').removeClass('buttonSelect-button');
            buttonSelectVal = element.getAttribute('data-value');
        });
    </script>

</div>
