document.addEventListener("DOMContentLoaded", () => {
        BX.Vue.component('c-form', {
            props: {
                action: 'action',
                bformid: 'bformid',
                sessid: 'sessid',
                backlink: 'backlink',
                fields: 'fields',
                users: 'users',
                jquery: 'jquery'
            },
            data() {
                return {
                    autocomplete: 'Off',
                    method: 'POST',
                    post_detector: '',
                    loading: false,
                    error: false,
                    error_message: '',
                    success_message: ''
                }
            },
            created() {
                this.post_detector = this.bformid.replace('js-', '').replace('-', '_');
                this.setFormElementId();
            },
            mounted() {
                this.selectPickered();
                this.userSelectorHandler();
            },
            methods: {
                async onSubmit() {
                    this.error = false;
                    this.error_message = '';
                    this.loading = true;

                    const jsonBody = JSON.stringify(Object.fromEntries(new URLSearchParams(new FormData(document.getElementById(this.bformid)))));

                    try {
                        console.info(this.action);
                        const response = await fetch(this.action, {
                            method: 'POST',
                            mode: 'cors',
                            cache: 'default',
                            headers: {
                                'Content-Type': 'application/json;charset=utf-8',
                                'HTTP_X_REQUESTED_WITH': 'XMLHttpRequest',
                                'X-Bitrix-Csrf-Token': this.sessid
                            },
                            body: jsonBody
                        });

                        const dataRes = await response.json();

                        if(dataRes.code == 'ERROR') {
                            this.error = true;
                            this.error_message = dataRes.message;
                        } else {
                            this.success_message = dataRes.message;
                        }

                        this.loading = false;
                        await this.delay(500);
                        this.userSelectorHandler();

                    } catch (e) {
                        console.error(e);
                        this.loading = false;
                        await this.delay(500);
                    }
                },
                setFormElementId() {
                    let obTmp = this.fields;
                    let newTmp = {};
                    Object.keys(obTmp).forEach(
                        function(key) {
                            obTmp[key].elementid = 'bp_' + obTmp[key].name;
                        }
                    );
                    this.fields = obTmp;
                },
                selectPickered() {
                    this.jquery('.selectpicker').selectpicker();

                    let selects = [];
                    this.jquery('.selectpicker').each(function() {
                        let name = $(this).attr('name');
                        selects.push(name);
                    });

                    for (let item of selects) {
                        this.jquery('.selectpicker[name="'+ item +'"]').val(this.fields[item].value).trigger('change');
                    }
                },
                userSelectorHandler() {
                    const tmp = this.fields;
                    Object.values(tmp).forEach((item) => {
                        if(item.type == 'user_select') {
                            const c = document.getElementById(item.elementid);
                            if (c) {
                                const config = BX.parseJSON(item.config.replaceAll('&quot;', '"'));
                                BX.Bizproc.UserSelector.decorateNode(c, config);
                            }
                        }
                    });

                },
                delay(ms) {
                    return new Promise(resolve => setTimeout(resolve, ms));
                },
                onRenewUser() {
                    this.error = false;
                }
            },
            computed: {
            },
            watch: {
                fields: {
                    deep: true,
                    handler(newValue, oldValue) {
                        let data = [newValue.POLZOVATEL.value, newValue.DATA_NACHALA.value, newValue.KOLICHESTVO_DNEY.value];
                        this.error = false;

                        let pval = document.querySelector("input[name=\"POLZOVATEL\"]").value;
                        if(pval != '') {
                            newValue.POLZOVATEL.value = pval;
                        }
                        console.info(data);
                    }
                }
            },
            template: `
            <form class="needs-validation _vue d-block py-3" :class="bformid" novalidate="novalidate" :id="bformid" :action="action" :method="method" :autocomplete="autocomplete" v-on:submit.prevent="onSubmit">
                 <div v-if="error === true" class="alert alert-danger mb-0 mx-3" v-html="error_message"></div>
                 <div v-if="success_message.length > 0" class="alert alert-success mb-0 mx-3" v-html="success_message"></div>
                 <div v-else id="js--form-action-content" class="p-3">
                     <input type="hidden" :name="post_detector" value="add">
                     <input type="hidden" name="sessid" id="sessid" :value="sessid" />
                     <div class="w-100 my-2 col-12 overflow-hidden">
                         <a :href="backlink" class="float-right">&larr; К списку</a>
                     </div>
                     <div class="form-group row py-3">
                         <div v-for="field in fields" :key="field.idnum" class="col-4 col-lg-4 col-md-6 col-sm-12">
                            <div v-if="field.type == 'user_select' && field.data_type == 'employee'" class="position-relative">
                                <label class="col-form-label" :for="field.elementid">{{field.label}}<span v-if="field.required" class="text-danger">*</span>:</label>
                                <div :id="field.elementid" data-role="user-selector" :data-config="field.config" class="form-control" v-on:click="onRenewUser"></div>
                            </div>
                            <div v-else class="position-relative" :class="field.type == 'date' ? '_inner' : ''">
                                <label class="col-form-label" :for="field.elementid">{{field.label}}<span v-if="field.required" class="text-danger">*</span>:</label>
                                <input class="form-control" :type="field.type" :name="field.name" v-model="field.value" :id="field.elementid" :required="field.required" />
                            </div>
                         </div>
                     </div>
                     <div class="w-100 hint mb-3">
                          <span class="text-danger">*</span> <span class="text-default">Поля обязательные для заполнения</span>.
                     </div>
                     <div class="w-100">
                        <div class="text-right">
                            <button class="btn btn-success btn-block" type="submit">Далее</button>
                        </div>
                     </div>
                 </div>
                 <div v-if="loading" class="loader-background"><div class="loader-ajax"></div></div>
            </form>
            `
        });

        BX.Vue.create({
            el: '#ja-bp-container',
            template: '<c-form :action="action" :bformid="id" :sessid="sessid" :fields="fields" :users="users" :jquery="jquery" :backlink="backlink" />',
            data: () => {
                return {
                    action: BPInput.action,
                    id:     BPInput.id,
                    sessid: BPInput.sessid,
                    backlink: BPInput.backlink,
                    fields: BPInput.fields,
                    users:  BPInput.users,
                    jquery: BPInput.jquery
                }
            },
        });
    }
);

/*
<select class="form-control selectpicker" :name="field.name" v-model="field.value" :id="field.elementid"  data-dropup-auto="false" data-size="auto" title="Выбрать" data-live-search="true"  data-live-search-placeholder="Найти"  required="required">
                                    <option value="0" disabled="disabled">-</option>
                                    <option v-for="user in users" :value="user.ID">{{user.USER_INFO}}</option>
                                </select>
 */
/*$(function () {
    $('.js-otpusk_czn').on('submit', function (e) {
        e.preventDefault();
        e.stopPropagation();

        var _this = $(this);
        _this.find('>.alert').hide();

        if (_this.get(0).checkValidity() !== false) {
            _this.find('button[type=submit]').prop('disabled', true);

            $.ajax({
                url: _this.attr('action'),
                data: new FormData(_this.get(0)),
                processData: false,
                contentType: false,
                dataType: 'json',
                type: 'POST',
                success: function (resp) {
                    if (resp.ajaxid) {
                        $("#wait_comp_" + resp.ajaxid).remove();
                    }

                    if (resp.code == "ReadySign") {
                        var obFrom = $("#workarea-content").find('form#js-otpusk_czn');
                        obFrom.prepend('<input type="hidden" name="documentid" value="' + resp.documentid + '" />');
                        obFrom.find('input[name="uved_inaya_rabota"]').val('signed');

                        var popup = new BX.PopupWindow("popup-iframe2", null, {
                            closeIcon: {right: "12px", top: "10px"},
                            width: "100%",
                            height: "100%"
                        });

                        $('#popup-iframe2').css({'width': '100%', 'height': '100%'}).html('');
                        $('<iframe>', {
                            src: '/podpis-fayla/?FILES[]=' + resp.file_id + '&CHECK_SIGN=N&sessid=' + resp.sessid,
                            id: 'popup-iframe',
                            frameborder: 0,
                            scrolling: 'no',
                            width: '100%',
                            height: '100%'
                        }).appendTo('#popup-iframe2');
                        $('#popup-iframe2').show();

                    } else if (resp.code != "OK") {
                        _this.find('>.alert').attr('class', 'alert alert-danger d-block').html(resp.message)
                    } else {
                        _this.find('#js--form-action-content').addClass('d-none');
                        _this.find('>.alert').attr('class', 'alert alert-info d-block').html(resp.message);
                        _this.get(0).reset();
                    }
                    return;
                }
            }).fail(function () {
                _this.find('>.alert').attr('class', 'alert alert-danger').html("Произошла ошибка, попробуйте позже").show();
            }).always(function () {
                _this.find('button[type=submit]').prop('disabled', false);
                $('html, body').scrollTop(0);
            })
        } else {
            $("[id^=wait_comp_]").remove();
        }

        _this.addClass('was-validated');
        return false;
    });

    $(window).on("message onmessage", function (e) {
        var data = e.originalEvent.data;
        if (data == 'filesigner_signed') {
            $('#popup-iframe2').hide();

            $('.js-uved_inaya_rabota').trigger('submit');

        } else if (data == "filesigner_hiden") {
            $('#popup-iframe2').hide();
        }
    });
});
*/