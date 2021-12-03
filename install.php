<!DOCTYPE html>
<html lang="ru">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Установка...</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div id="intro" class="carousel slide carousel-dark" data-bs-ride="carousel" data-bs-wrap="false">
    <div class="carousel-indicators">
        <button type="button" data-bs-target="#intro" data-bs-slide-to="0" class="active"
                aria-current="true" aria-label="Slide 1"></button>
        <button type="button" data-bs-target="#intro" data-bs-slide-to="1"
                aria-label="Slide 2"></button>
        <button type="button" data-bs-target="#intro" data-bs-slide-to="2"
                aria-label="Slide 3"></button>
    </div>
    <div class="carousel-inner">
        <div class="carousel-item active">
            <img src="slide1.jpg" class="d-block mx-auto" alt="slide1" style="width: 50%;">
            <div class="carousel-caption d-none d-md-block" style="position:initial;">
                <h5>Загрузите файлы начислений для BI-аналитики</h5>
                <p>Загрузите файлы помесячно. Система не проверят на дубли, будьте внимательны</p>
            </div>
        </div>
        <div class="carousel-item">
            <img src="slide2.jpg" class="d-block mx-auto" alt="slide2" style="width: 50%;">
            <div class="carousel-caption d-none d-md-block" style="position:initial;">
                <h5>Система создаст дополнительные поля</h5>
                <p>Будут созданны два направления и поля, в которых будут храниться данные из выгрузки</p>
            </div>
        </div>
        <div class="carousel-item">
            <img src="slide3.jpg" class="d-block mx-auto" alt="slide3" style="width: 50%;">
            <div class="carousel-caption d-none d-md-block" style="position:initial;">
                <h5>В чате, при обращении клиента, вы быстро сможете добавить связь с порталом</h5>
                <p>Это действие произведет поиск компании по адресу портала и откроет ее карточку CRM</p>
            </div>
        </div>
    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#intro" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Назад</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#intro" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Вперед</span>
    </button>
</div>
<div class="text-center">
    <button id="skip" type="button" class="btn btn-light m-3">Перейти к приложению</button>
    <button id="next" type="button" class="btn btn-primary m-3">Далее</button>
</div>
<script src="https://api.bitrix24.com/api/v1/"></script>
<script src="js/bootstrap.bundle.min.js"></script>
<script type="text/javascript">
    BX24.init(() => {
        var intro = document.getElementById("intro");
        var next = document.getElementById("next");
        var skip = document.getElementById("skip");

        skip.onclick = (e) => {
            let batch = [];

            batch.push(["app.info", {}]);
            batch.push(["imbot.register", {
                EVENT_HANDLER: getBase() + "bot.php",
                PROPERTIES: {
                    EMAIL: "partner_bot@1os.su",
                    PERSONAL_BIRTHDAY: "1990-01-01",
                    NAME: "Чат-бот Партнерки",
                    PERSONAL_WWW: "https://1os.su",
                    WORK_POSITION: "Чат-бот",
                    PERSONAL_GENDER: "F",
                    COLOR: "LIME",
                },
                CODE: "fos.partner",
                OPENLINE: "N",
                TYPE: "B"
            }]);
            batch.push(["imbot.app.register", {
                BOT_ID: "$result[1]",
                CODE: "fos.partner",
                IFRAME: getBase() + "linkPortal.php?appId=$result[0][ID]",
                IFRAME_WIDTH: "350",
                IFRAME_HEIGHT: "150",
                HASH: "d1ab17948db0d5b349cd2",
                //   ICON_FILE: window.linkPortalIcon,
                CONTEXT: "LINES",
                HIDDEN: "N",
                EXTRANET_SUPPORT: "N",
                LIVECHAT_SUPPORT: "N",
                IFRAME_POPUP: "N",
                LANG: [
                    {
                        LANGUAGE_ID: "ru",
                        TITLE: "Привязать диалог к порталу",
                        DESCRIPTION: "Устанавливает связь диалога с конкретным порталом"
                    },
                    {
                        LANGUAGE_ID: "en",
                        TITLE: "link dialog to portal",
                        DESCRIPTION: "This will link current dialog to clients portal"
                    },
                ]
            }]);

            batch.push(["crm.deal.userfield.add", {
                fields: {
                    FIELD_NAME: "PREMIUM_TYPE",
                    EDIT_FORM_LABEL: "Тип премии",
                    LIST_COLUMN_LABEL: "Тип премии",
                    IS_SEARCHABLE: "Y",
                    SHOW_FILTER: "N",
                    USER_TYPE_ID: "enumeration",
                    LIST: [
                        {VALUE: "Покупка"},
                        {VALUE: "Продление"},
                        {VALUE: "После триала"}
                    ],
                }
            }]);
            batch.push(["crm.deal.userfield.add", {
                fields: {
                    FIELD_NAME: "DATE_OF_USE",
                    EDIT_FORM_LABEL: "Дата использования",
                    LIST_COLUMN_LABEL: "Дата использования",
                    IS_SEARCHABLE: "Y",
                    SHOW_FILTER: "N",
                    USER_TYPE_ID: "datetime",
                }
            }]);
            batch.push(["crm.deal.userfield.add", {
                fields: {
                    FIELD_NAME: "APP_CODE",
                    EDIT_FORM_LABEL: "Название ПО",
                    LIST_COLUMN_LABEL: "Название ПО",
                    IS_SEARCHABLE: "Y",
                    SHOW_FILTER: "N",
                    USER_TYPE_ID: "string",
                }
            }]);
            batch.push(["crm.deal.userfield.add", {
                fields: {
                    FIELD_NAME: "APP_TYPE",
                    EDIT_FORM_LABEL: "Тип приложения",
                    LIST_COLUMN_LABEL: "Тип приложения",
                    IS_SEARCHABLE: "Y",
                    SHOW_FILTER: "N",
                    USER_TYPE_ID: "integer",
                    SETTINGS: {DEFAULT_VALUE: 0}
                }
            }]);
            batch.push(["crm.deal.userfield.add", {
                fields: {
                    FIELD_NAME: "APP_REMOVED",
                    EDIT_FORM_LABEL: "Приложение удалено",
                    LIST_COLUMN_LABEL: "Приложение удалено",
                    IS_SEARCHABLE: "Y",
                    SHOW_FILTER: "N",
                    USER_TYPE_ID: "boolean",
                }
            }]);
            batch.push(["crm.deal.userfield.add", {
                fields: {
                    FIELD_NAME: "APP_TYPE_POINTS",
                    EDIT_FORM_LABEL: "Коэфициент типа приложения",
                    LIST_COLUMN_LABEL: "Коэфициент типа приложения",
                    IS_SEARCHABLE: "Y",
                    SHOW_FILTER: "N",
                    USER_TYPE_ID: "integer",
                    SETTINGS: {DEFAULT_VALUE: 0}
                }
            }]);
            batch.push(["crm.deal.userfield.add", {
                fields: {
                    FIELD_NAME: "MODE_OF_USE",
                    EDIT_FORM_LABEL: "Режим использования",
                    LIST_COLUMN_LABEL: "Режим использования",
                    IS_SEARCHABLE: "Y",
                    SHOW_FILTER: "N",
                    USER_TYPE_ID: "string",
                    USER_TYPE_ID: "enumeration",
                    LIST: [
                        {VALUE: "D"},
                        {VALUE: "W"},
                        {VALUE: "M"},
                        {VALUE: "Y"}
                    ],
                }
            }]);
            batch.push(["crm.deal.userfield.add", {
                fields: {
                    FIELD_NAME: "MODE_OF_USE_POINTS",
                    EDIT_FORM_LABEL: "Коэфициент режима использования",
                    LIST_COLUMN_LABEL: "Коэфициент режима использования",
                    IS_SEARCHABLE: "Y",
                    SHOW_FILTER: "N",
                    USER_TYPE_ID: "integer",
                    SETTINGS: {DEFAULT_VALUE: 0}
                }
            }]);
            batch.push(["crm.deal.userfield.add", {
                fields: {
                    FIELD_NAME: "POINTS",
                    EDIT_FORM_LABEL: "Баллов за факт использования",
                    LIST_COLUMN_LABEL: "Баллов за факт использования",
                    IS_SEARCHABLE: "Y",
                    SHOW_FILTER: "N",
                    USER_TYPE_ID: "integer",
                    SETTINGS: {DEFAULT_VALUE: 0}
                }
            }]);
            batch.push(["crm.deal.userfield.add", {
                fields: {
                    FIELD_NAME: "ALL_POINTS",
                    EDIT_FORM_LABEL: "Общее количество баллов за использование",
                    LIST_COLUMN_LABEL: "Общее количество баллов за использование",
                    IS_SEARCHABLE: "Y",
                    SHOW_FILTER: "N",
                    SHOW_IN_LIST: "N",
                    USER_TYPE_ID: "integer",
                    SETTINGS: {DEFAULT_VALUE: 0}
                }
            }]);
            batch.push(["crm.deal.userfield.add", {
                fields: {
                    FIELD_NAME: "AMOUNT",
                    EDIT_FORM_LABEL: "Индивидуальное дневное вознаграждение лицензиара",
                    LIST_COLUMN_LABEL: "Индивидуальное дневное вознаграждение лицензиара",
                    IS_SEARCHABLE: "Y",
                    SHOW_FILTER: "N",
                    SHOW_IN_LIST: "N",
                    USER_TYPE_ID: "money",
                    SETTINGS: {DEFAULT_VALUE: 0}
                }
            }]);
            batch.push(["crm.deal.userfield.add", {
                fields: {
                    FIELD_NAME: "ALL_AMOUNT",
                    EDIT_FORM_LABEL: "Общее дневное вознаграждение",
                    LIST_COLUMN_LABEL: "Общее дневное вознаграждение",
                    IS_SEARCHABLE: "Y",
                    SHOW_FILTER: "N",
                    SHOW_IN_LIST: "N",
                    USER_TYPE_ID: "money",
                    SETTINGS: {DEFAULT_VALUE: 0}
                }
            }]);
            batch.push(["crm.deal.userfield.add", {
                fields: {
                    FIELD_NAME: "CURRENCY",
                    EDIT_FORM_LABEL: "Валюта",
                    LIST_COLUMN_LABEL: "Валюта",
                    IS_SEARCHABLE: "Y",
                    SHOW_FILTER: "N",
                    SHOW_IN_LIST: "N",
                    USER_TYPE_ID: "string",
                }
            }]);

            batch.push(["crm.company.userfield.add", {
                fields: {
                    FIELD_NAME: "CLIENT_NAME",
                    EDIT_FORM_LABEL: "Адрес портала",
                    LIST_COLUMN_LABEL: "Адрес портала",
                    IS_SEARCHABLE: "Y",
                    SHOW_FILTER: "N",
                    SHOW_IN_LIST: "N",
                    USER_TYPE_ID: "string",
                }
            }]);
            batch.push(["crm.company.userfield.add", {
                fields: {
                    FIELD_NAME: "MEMBER_ID",
                    EDIT_FORM_LABEL: "member_id",
                    LIST_COLUMN_LABEL: "member_id",
                    IS_SEARCHABLE: "Y",
                    SHOW_FILTER: "N",
                    SHOW_IN_LIST: "N",
                    USER_TYPE_ID: "string",
                }
            }]);
            batch.push(["crm.company.userfield.add", {
                fields: {
                    FIELD_NAME: "SUBSCRIPTION_START",
                    EDIT_FORM_LABEL: "Начало подписки",
                    LIST_COLUMN_LABEL: "Начало подписки",
                    IS_SEARCHABLE: "Y",
                    SHOW_FILTER: "N",
                    SHOW_IN_LIST: "N",
                    USER_TYPE_ID: "datetime",
                }
            }]);
            batch.push(["crm.company.userfield.add", {
                fields: {
                    FIELD_NAME: "SUBSCRIPTION_END",
                    EDIT_FORM_LABEL: "Конец подписки",
                    LIST_COLUMN_LABEL: "Конец подписки",
                    IS_SEARCHABLE: "Y",
                    SHOW_FILTER: "N",
                    SHOW_IN_LIST: "N",
                    USER_TYPE_ID: "datetime",
                }
            }]);

            batch.push(["crm.category.add", {
                entityTypeId: "2",
                fields: {
                    name: "Ежедневные начисления по подписке"
                }
            }]);
            batch.push(["crm.category.add", {
                entityTypeId: "2",
                fields: {
                    name: "Премиальные начисления по подписке"
                }
            }]);

            BX24.callBatch(batch, data => {
                BX24.appOption.set("payouts_cat", data[20].data().category.id);
                BX24.appOption.set("premium_payouts_cat", data[21].data().category.id);
                BX24.installFinish();
            }, false);
        };

        next.onclick = (e) => {
            bootstrap.Carousel.getInstance(intro).next();
        };

        intro.addEventListener("slide.bs.carousel", function (e) {
            if (e.to === 2) {
                next.classList.add("d-none");

                skip.classList.add("btn-primary");
                skip.classList.remove("btn-light");
            } else {
                next.classList.remove("d-none");

                skip.classList.add("btn-light");
                skip.classList.remove("btn-primary");
            }
        });
        BX24.fitWindow();
    });

    function getBase() {
        return "https://" + window.location.host +
            (window.location.port ? ":" + window.location.port : "") + window.location.pathname.replace("install.php", "");
    }

    window.linkPortalIcon = "";
</script>
</body>
</html>