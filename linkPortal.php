<!DOCTYPE html>
<html lang="ru">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Загрузка файлов вознаграждения по подписке</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <style>
        .dark-theme {
            color: #c3c3c3 !important;
            background: #3d3d3d !important;
        }
    </style>
</head>
<body>
<div class="mx-3 mt-2">
    <label for="domain" class="form-label">Адрес портала</label>
    <input class="form-control" type="text" id="domain">
</div>
<button id="save" class="btn btn-primary mx-3 mt-1" type="button">Связать</button>
<button id="goto" class="btn btn-secondary mx-3 mt-1 float-end" type="button">Найти портал</button>
<script type="text/javascript">

    frameCommunicationInit();

    let DARK_MODE = getParameterByName("DARK_MODE");
    let DOMAIN = getParameterByName("DOMAIN");
    let appId = getParameterByName("appId");
    let DIALOG_ENTITY_DATA_1 = getParameterByName("DIALOG_ENTITY_DATA_1").split(/\|/gi);
    let portal = document.getElementById("domain");
    let save = document.getElementById("save");

    if (DARK_MODE === "Y") {
        document.body.classList.add("dark-theme");
        portal.classList.add("dark-theme");
    }

    if (DIALOG_ENTITY_DATA_1[0] === "Y") {
        save.onclick = _ => {
            let entity_type = DIALOG_ENTITY_DATA_1[1];
            let entity_id = DIALOG_ENTITY_DATA_1[2];
            window.open(DOMAIN + "/marketplace/app/" + appId + "/?" + new URLSearchParams([
                ["entity_type", entity_type],
                ["entity_id", entity_id],
                ["portal", portal.value],
            ]), "_blank");
            frameCommunicationSend({'action': 'close'});
        };
        goto.onclick = _ => {
            let entity_type = DIALOG_ENTITY_DATA_1[1];
            let entity_id = DIALOG_ENTITY_DATA_1[2];
            window.open(DOMAIN + "/marketplace/app/" + appId + "/?" + new URLSearchParams([
                ["entity_type", entity_type],
                ["entity_id", entity_id],
                ["find", "Y"],
            ]), "_blank");
            frameCommunicationSend({'action': 'close'});
        };
    } else {
        frameCommunicationSend({'action': 'put', 'message': 'Сообщите, пожалуйста, ваш контактный email или телефон'});
        frameCommunicationSend({'action': 'close'});
    }


    // функция инициализации коммуникации с основным окном
    function frameCommunicationInit() {
        if (!window.frameCommunication) {
            window.frameCommunication = {timeout: {}};
        }
        if (typeof window.postMessage === 'function') {
            window.addEventListener('message', function (event) {
                var data = {};
                try {
                    data = JSON.parse(event.data);
                } catch (err) {
                }

                if (data.action === 'init') {
                    frameCommunication.uniqueLoadId = data.uniqueLoadId;
                    frameCommunication.postMessageSource = event.source;
                    frameCommunication.postMessageOrigin = event.origin;
                }
            });
        }
    }

    // функция отправки данных в основное окно
    function frameCommunicationSend(data) {
        data['uniqueLoadId'] = frameCommunication.uniqueLoadId;
        var encodedData = JSON.stringify(data);
        if (!frameCommunication.postMessageOrigin) {
            clearTimeout(frameCommunication.timeout[encodedData]);
            frameCommunication.timeout[encodedData] = setTimeout(function () {
                frameCommunicationSend(data);
            }, 10);
            return true;
        }

        if (typeof window.postMessage === 'function') {
            if (frameCommunication.postMessageSource) {
                frameCommunication.postMessageSource.postMessage(
                    encodedData,
                    frameCommunication.postMessageOrigin
                );
            }
        }
    }

    function getParameterByName(name) {
        var match = RegExp('[?&]' + name + '=([^&]*)').exec(window.location.search);
        return match && decodeURIComponent(match[1].replace(/\+/g, ' '));
    }
</script>
</body>
</html>