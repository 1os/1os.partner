<!DOCTYPE html>
<html lang="ru">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Загрузка файлов вознаграждения по подписке</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<main>
    <div class="container py-4">
        <div class="p-5 mb-4 bg-light rounded-3">
            <div class="container-fluid py-5">
                <h1 class="display-5 fw-bold">Укажите файлы для загрузки</h1>
                <p class="col-md-10 fs-4">Нужны файлы начислений, которые выгружены из кабинета разработчика, со
                    страницы
                    "<a href="https://vendors.bitrix24.ru/sale/payout.php" target="_blank">Выплаты по подписке</a>"</p>
                <div class="mb-3">
                    <label for="payouts" class="form-label">Ежедневные начисления</label>
                    <input class="form-control" type="file" id="payouts">
                </div>
                <div class="progress d-none">
                    <div id="progressbar_payouts" class="progress-bar progress-bar-striped"
                         role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <div class="mb-3">
                    <label for="premium_payouts" class="form-label">Премиальные начисления</label>
                    <input class="form-control" type="file" id="premium_payouts">
                </div>
                <div class="progress d-none">
                    <div id="progressbar_premium_payouts" class="progress-bar progress-bar-striped"
                         role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <p class="fs-4">Файлы будут обработаны и загружены в браузере, данные не уйдут на сторонние сервера</p>
                <p class="fs-4">При загрузке данных нет возможности проверять на дубли, поэтому не загружайте один и тот
                    же период два раза</p>
                <button id="save" class="btn btn-primary btn-lg" type="button">Загрузить</button>
                <a id="finish" class="btn btn-link btn-lg disabled d-none">Обработка файлов завершена</a>
            </div>
        </div>
    </div>
</main>
<script src="https://api.bitrix24.com/api/v1/"></script>
<script src="js/xlsx.full.min.js"></script>
<script type="text/javascript">
    BX24.init(async _ => {
        if (BX24.placement.info().options.portal) {
            document.body.classList.add("d-none");
            let data = await new Promise(resolve => BX24.callMethod("crm.company.list", {
                filter: {
                    UF_CRM_CLIENT_NAME: BX24.placement.info().options.portal
                }
            }, resolve));
            if (data.total() === 1) {
                await new Promise(resolve => BX24.callMethod("crm." + BX24.placement.info().options.entity_type + ".update", {
                    id: BX24.placement.info().options.entity_id,
                    fields: {
                        COMPANY_ID: data.data()[0].ID
                    }
                }, resolve));
                try {
                    window.top.location.href = "https://" + BX24.getDomain() + "/crm/company/details/" + data.data()[0].ID + "/";
                } catch (e) {
                    BX24.openPath("/crm/company/details/" + data.data()[0].ID + "/");
                }
            } else {
                try {
                    window.top.location.href = "https://" + BX24.getDomain() + "/crm/company/details/0/?" + new URLSearchParams([
                        ["UF_CRM_CLIENT_NAME", BX24.placement.info().options.portal],
                    ]);
                } catch (e) {
                    BX24.openPath("/crm/company/details/0/?" + new URLSearchParams([
                        ["UF_CRM_CLIENT_NAME", BX24.placement.info().options.portal],
                    ]));
                }
            }
        }
        if (BX24.placement.info().options.find) {
            document.body.classList.add("d-none");
            let data = await new Promise(resolve => BX24.callMethod("crm." + BX24.placement.info().options.entity_type + ".get", {
                id: BX24.placement.info().options.entity_id,
            }, resolve));
            try {
                window.top.location.href = "https://" + BX24.getDomain() + "/crm/company/details/" + (data.data().COMPANY_ID || 0) + "/";
            } catch (e) {
                BX24.openPath("/crm/company/details/" + (data.data().COMPANY_ID || 0) + "/");
            }
        }
        let save = document.getElementById("save");
        let payouts = document.getElementById("payouts");
        let progressbar_payouts = document.getElementById("progressbar_payouts");
        let premium_payouts = document.getElementById("premium_payouts");
        let progressbar_premium_payouts = document.getElementById("progressbar_premium_payouts");
        let finish = document.getElementById("finish");
        save.onclick = async _ => {
            save.disabled = true
            progressbar_payouts.parentElement.classList.add("d-none");
            progressbar_premium_payouts.parentElement.classList.add("d-none");
            finish.classList.add("d-none");
            let config = await new Promise(resolve => BX24.callBatch([
                ["crm.deal.userfield.list", {filter: {FIELD_NAME: "UF_CRM_MODE_OF_USE"}}],
                ["crm.deal.userfield.list", {filter: {FIELD_NAME: "UF_CRM_PREMIUM_TYPE"}}]
            ], resolve, false));
            let batch = [];
            let companies = {};
            await new Promise(resolve => {
                let file = payouts.files[0];
                if (file) {
                    progressbar_payouts.parentElement.classList.remove("d-none");
                    progressbar_payouts.classList.add("progress-bar-animated");
                    BX24.fitWindow();
                    progressbar_payouts.setAttribute("aria-valuenow", "0");
                    progressbar_payouts.style.width = "0%";
                    progressbar_payouts.innerText = "0%";
                    let reader = new FileReader();
                    reader.onload = async function (e) {
                        let workbook = XLSX.read(e.target.result, {
                            raw: true,
                        });
                        let first_sheet_name = workbook.SheetNames[0];
                        let worksheet = workbook.Sheets[first_sheet_name];
                        const data = XLSX.utils.sheet_to_json(worksheet, {
                            header: 0,
                        });
                        progressbar_payouts.setAttribute("aria-valuemax", "" + data.length);
                        let count = 0;
                        companies = await new Promise(resolve => BX24.callBatch(data.slice(count, count + 50).map(item => {
                            return ["crm.company.list", {
                                select: ["ID"],
                                filter: {
                                    UF_CRM_MEMBER_ID: item.MEMBER_ID
                                }
                            }];
                        }).reduce((acc, cur) => {
                            acc[cur[1].filter.UF_CRM_MEMBER_ID] = cur;
                            return acc;
                        }, {}), resolve, false));
                        for await (let row of data) {
                            count++;
                            progressbar_payouts.setAttribute("aria-valuenow", "" + count);
                            progressbar_payouts.style.width = "" + parseInt("" + (count * 100 / data.length)) + "%";
                            progressbar_payouts.innerText = "" + parseInt("" + (count * 100 / data.length)) + "%";
                            let company_id = "0";
                            if (companies[row.MEMBER_ID].total() > 0) {
                                batch.push(["crm.company.update", {
                                    id: companies[row.MEMBER_ID].data()[0].ID,
                                    fields: {
                                        UF_CRM_CLIENT_NAME: row.CLIENT_NAME || "",
                                        UF_CRM_58919CA32F1B1: row.CLIENT_NAME || "",
                                    },
                                    params: {REGISTER_SONET_EVENT: "N"}
                                }]);
                                company_id = companies[row.MEMBER_ID].data()[0].ID;
                            } else {
                                company_id = "$result[" + batch.length + "]";
                                batch.push(["crm.company.add", {
                                    fields: {
                                        TITLE: row.CLIENT_NAME || row.MEMBER_ID,
                                        CURRENCY_ID: row.CURRENCY.replace("RUR", "RUB"),
                                        UF_CRM_58919CA32F1B1: row.CLIENT_NAME || "",
                                        UF_CRM_CLIENT_NAME: row.CLIENT_NAME || "",
                                        UF_CRM_MEMBER_ID: row.MEMBER_ID,
                                    },
                                    params: {REGISTER_SONET_EVENT: "N"}
                                }]);
                            }
                            batch.push(["crm.deal.add", {
                                fields: {
                                    CATEGORY_ID: BX24.appOption.get("payouts_cat"),
                                    TITLE: row.APP_CODE || row.APP_COE,
                                    COMPANY_ID: company_id,
                                    CURRENCY_ID: row.CURRENCY.replace("RUR", "RUB"),
                                    OPPORTUNITY: ("" + row.AMOUNT).replace(/\s/gi, "").replace(/,/gi, "."),
                                    STAGE_ID: "C" + BX24.appOption.get("payouts_cat") + ":WON",
                                    BEGINDATE: row.DATE_OF_USE,
                                    CLOSEDATE: row.DATE_OF_USE,
                                    UF_CRM_DATE_OF_USE: row.DATE_OF_USE,
                                    UF_CRM_APP_CODE: row.APP_CODE || row.APP_COE,
                                    UF_CRM_MODE_OF_USE: config[0].data()[0].LIST.filter(item => item.VALUE === row.MODE_OF_USE).map(item => item.ID)[0],
                                    UF_CRM_MODE_OF_USE_POINTS: row.MODE_OF_USE_POINTS,
                                    UF_CRM_APP_TYPE: row.APP_TYPE,
                                    UF_CRM_APP_TYPE_POINTS: row.APP_TYPE_POINTS,
                                    UF_CRM_POINTS: row.POINTS,
                                    UF_CRM_ALL_POINTS: row.ALL_POINTS,
                                    UF_CRM_AMOUNT: ("" + row.AMOUNT).replace(/\s/gi, "").replace(/,/gi, ".") + "|" + row.CURRENCY.replace("RUR", "RUB"),
                                    UF_CRM_ALL_AMOUNT: ("" + row.ALL_AMOUNT).replace(/\s/gi, "").replace(/,/gi, ".") + "|" + row.CURRENCY.replace("RUR", "RUB"),
                                    UF_CRM_CURRENCY: row.CURRENCY,
                                },
                                params: {REGISTER_SONET_EVENT: "N"}
                            }]);
                            if (batch.length === 50) {
                                await new Promise(resolve => BX24.callBatch(batch, resolve, false));
                                batch = [];
                            }
                            if (count % 50 === 0) {
                                companies = await new Promise(resolve => BX24.callBatch(data.slice(count, count + 50).map(item => {
                                    return ["crm.company.list", {
                                        select: ["ID"],
                                        filter: {
                                            UF_CRM_MEMBER_ID: item.MEMBER_ID
                                        }
                                    }];
                                }).reduce((acc, cur) => {
                                    acc[cur[1].filter.UF_CRM_MEMBER_ID] = cur;
                                    return acc;
                                }, {}), resolve, false));
                            }
                        }
                        console.log("payouts rows: " + data.length);
                        resolve();
                    }
                    reader.readAsArrayBuffer(file);
                } else {
                    resolve();
                }
            });
            progressbar_payouts.classList.remove("progress-bar-animated");
            await new Promise(resolve => {
                let file1 = premium_payouts.files[0];
                if (file1) {
                    progressbar_premium_payouts.parentElement.classList.remove("d-none");
                    progressbar_premium_payouts.classList.add("progress-bar-animated");
                    BX24.fitWindow();
                    progressbar_premium_payouts.setAttribute("aria-valuenow", "0");
                    progressbar_premium_payouts.style.width = "0%";
                    progressbar_premium_payouts.innerText = "0%";
                    let reader1 = new FileReader();
                    reader1.onload = async function (e) {
                        let workbook = XLSX.read(e.target.result, {
                            raw: true,
                        });
                        let first_sheet_name = workbook.SheetNames[0];
                        let worksheet = workbook.Sheets[first_sheet_name];
                        const data = XLSX.utils.sheet_to_json(worksheet, {
                            header: 0,
                        });
                        progressbar_premium_payouts.setAttribute("aria-valuemax", "" + data.length);
                        let count = 0;
                        companies = await new Promise(resolve => BX24.callBatch(data.slice(count, count + 50).map(item => {
                            return ["crm.company.list", {
                                select: ["ID"],
                                filter: {
                                    UF_CRM_MEMBER_ID: item.MEMBER_ID
                                }
                            }];
                        }).reduce((acc, cur) => {
                            acc[cur[1].filter.UF_CRM_MEMBER_ID] = cur;
                            return acc;
                        }, {}), resolve, false));
                        for await (let row of data) {
                            count++;
                            progressbar_premium_payouts.setAttribute("aria-valuenow", "" + count);
                            progressbar_premium_payouts.style.width = "" + parseInt("" + (count * 100 / data.length)) + "%";
                            progressbar_premium_payouts.innerText = "" + parseInt("" + (count * 100 / data.length)) + "%";
                            let company_id = "0";
                            if (companies[row.MEMBER_ID].total() > 0) {
                                batch.push(["crm.company.update", {
                                    id: companies[row.MEMBER_ID].data()[0].ID,
                                    fields: {
                                        UF_CRM_CLIENT_NAME: row.CLIENT_NAME || "",
                                        UF_CRM_58919CA32F1B1: row.CLIENT_NAME || "",
                                        UF_CRM_SUBSCRIPTION_START: row.SUBSCRIPTION_START,
                                        UF_CRM_SUBSCRIPTION_END: row.SUBSCRIPTION_END,
                                    },
                                    params: {REGISTER_SONET_EVENT: "N"}
                                }]);
                                company_id = companies[row.MEMBER_ID].data()[0].ID;
                            } else {
                                company_id = "$result[" + batch.length + "]";
                                batch.push(["crm.company.add", {
                                    fields: {
                                        TITLE: row.CLIENT_NAME || row.MEMBER_ID,
                                        CURRENCY_ID: row.CURRENCY.replace("RUR", "RUB"),
                                        UF_CRM_CLIENT_NAME: row.CLIENT_NAME || "",
                                        UF_CRM_58919CA32F1B1: row.CLIENT_NAME || "",
                                        UF_CRM_MEMBER_ID: row.MEMBER_ID,
                                        UF_CRM_SUBSCRIPTION_START: row.SUBSCRIPTION_START,
                                        UF_CRM_SUBSCRIPTION_END: row.SUBSCRIPTION_END,
                                    },
                                    params: {REGISTER_SONET_EVENT: "N"}
                                }]);
                            }
                            batch.push(["crm.deal.add", {
                                fields: {
                                    CATEGORY_ID: BX24.appOption.get("premium_payouts_cat"),
                                    TITLE: row.APP_CODE,
                                    COMPANY_ID: company_id,
                                    CURRENCY_ID: row.CURRENCY.replace("RUR", "RUB"),
                                    OPPORTUNITY: ("" + row.AMOUNT).replace(/\s/gi, "").replace(/,/gi, "."),
                                    STAGE_ID: "C" + BX24.appOption.get("premium_payouts_cat") + ":WON",
                                    BEGINDATE: row.SUBSCRIPTION_START,
                                    CLOSEDATE: row.SUBSCRIPTION_END,
                                    UF_CRM_PREMIUM_TYPE: config[1].data()[0].LIST.filter(item => item.VALUE === row.TYPE).map(item => item.ID)[0],
                                    UF_CRM_APP_CODE: row.APP_CODE,
                                    UF_CRM_APP_REMOVED: row.APP_REMOVED,
                                    UF_CRM_POINTS: row.POINTS,
                                    UF_CRM_ALL_POINTS: row.ALL_POINTS,
                                    UF_CRM_AMOUNT: ("" + row.AMOUNT).replace(/\s/gi, "").replace(/,/gi, ".") + "|" + row.CURRENCY.replace("RUR", "RUB"),
                                    UF_CRM_ALL_AMOUNT: ("" + row.ALL_AMOUNT).replace(/\s/gi, "").replace(/,/gi, ".") + "|" + row.CURRENCY.replace("RUR", "RUB"),
                                    UF_CRM_CURRENCY: row.CURRENCY,
                                },
                                params: {REGISTER_SONET_EVENT: "N"}
                            }]);
                            if (batch.length === 50) {
                                await new Promise(resolve => BX24.callBatch(batch, resolve, false));
                                batch = [];
                            }
                            if (count % 50 === 0) {
                                companies = await new Promise(resolve => BX24.callBatch(data.slice(count, count + 50).map(item => {
                                    return ["crm.company.list", {
                                        select: ["ID"],
                                        filter: {
                                            UF_CRM_MEMBER_ID: item.MEMBER_ID
                                        }
                                    }];
                                }).reduce((acc, cur) => {
                                    acc[cur[1].filter.UF_CRM_MEMBER_ID] = cur;
                                    return acc;
                                }, {}), resolve, false));
                            }
                        }
                        console.log("premium_payouts rows: " + data.length);
                        resolve();
                    }
                    reader1.readAsArrayBuffer(file1);
                } else {
                    resolve();
                }
            });
            progressbar_premium_payouts.classList.remove("progress-bar-animated");
            while (batch.length > 0) {
                let chunk = batch.splice(0, 50);
                await new Promise(resolve => BX24.callBatch(chunk, resolve, false));
            }
            save.disabled = false;
            finish.classList.remove("d-none");
        };
        BX24.fitWindow();
        setTimeout(_ => {
            BX24.fitWindow();
        }, 500);
        refreshToken();
    });

    function refreshToken() {
        setTimeout(_ => {
            BX24.refreshAuth();
            refreshToken();
        }, 1000 * 60 * 50);
    }
</script>
</body>
</html>