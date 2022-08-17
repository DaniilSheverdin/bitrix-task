'use strict';

var _slicedToArray = function () {
    function sliceIterator(arr, i) {
        var _arr = [];
        var _n = true;
        var _d = false;
        var _e = undefined;
        try {
            for (var _i = arr[Symbol.iterator](), _s; !(_n = (_s = _i.next()).done); _n = true) {
                _arr.push(_s.value);
                if (i && _arr.length === i) break;
            }
        } catch (err) {
            _d = true;
            _e = err;
        } finally {
            try {
                if (!_n && _i["return"]) _i["return"]();
            } finally {
                if (_d) throw _e;
            }
        }
        return _arr;
    }

    return function (arr, i) {
        if (Array.isArray(arr)) {
            return arr;
        } else if (Symbol.iterator in Object(arr)) {
            return sliceIterator(arr, i);
        } else {
            throw new TypeError("Invalid attempt to destructure non-iterable instance");
        }
    };
}();

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) {
    return typeof obj;
} : function (obj) {
    return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj;
};

var _createClass = function () {
    function defineProperties(target, props) {
        for (var i = 0; i < props.length; i++) {
            var descriptor = props[i];
            descriptor.enumerable = descriptor.enumerable || false;
            descriptor.configurable = true;
            if ("value" in descriptor) descriptor.writable = true;
            Object.defineProperty(target, descriptor.key, descriptor);
        }
    }

    return function (Constructor, protoProps, staticProps) {
        if (protoProps) defineProperties(Constructor.prototype, protoProps);
        if (staticProps) defineProperties(Constructor, staticProps);
        return Constructor;
    };
}();

function _toConsumableArray(arr) {
    if (Array.isArray(arr)) {
        for (var i = 0, arr2 = Array(arr.length); i < arr.length; i++) {
            arr2[i] = arr[i];
        }
        return arr2;
    } else {
        return Array.from(arr);
    }
}

function _defineProperty(obj, key, value) {
    if (key in obj) {
        Object.defineProperty(obj, key, {value: value, enumerable: true, configurable: true, writable: true});
    } else {
        obj[key] = value;
    }
    return obj;
}

function _classCallCheck(instance, Constructor) {
    if (!(instance instanceof Constructor)) {
        throw new TypeError("Cannot call a class as a function");
    }
}

function _possibleConstructorReturn(self, call) {
    if (!self) {
        throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
    }
    return call && (typeof call === "object" || typeof call === "function") ? call : self;
}

function _inherits(subClass, superClass) {
    if (typeof superClass !== "function" && superClass !== null) {
        throw new TypeError("Super expression must either be null or a function, not " + typeof superClass);
    }
    subClass.prototype = Object.create(superClass && superClass.prototype, {
        constructor: {
            value: subClass,
            enumerable: false,
            writable: true,
            configurable: true
        }
    });
    if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass;
}

function _requestByubject(subject, providerData, __Callback) {
    ccadesPluginInit().then(function () {
        var pkcs10 = null;
        var canPromise = !!window.Promise;

        const providerNameCryptopro = "Crypto-Pro GOST R 34.10-2012 Cryptographic Service Provider";
        const providerNameVipnet = "Infotecs GOST 2012/512 Cryptographic Service Provider";

        let providerName = providerNameVipnet;
        let providerType = 0;

        if(providerData == '2020a743468b034d93a9c66807e814b9') {
            providerName = providerNameVipnet;
            providerType = 77;
        } else if(providerData == 'dfb8b42f99eb5dffdcf5981ec91988a8') {
            providerName = providerNameCryptopro;
            providerType = 80;
        }

        cadesplugin.async_spawn(function* (args) {
            try {
                const AT_KEYEXCHANGE = 1;
                const AT_SIGNATURE = 2;

                const XCN_CRYPT_STRING_BASE64HEADER = 0;
                const XCN_CRYPT_STRING_BASE64 = 0x1;
                const XCN_CRYPT_STRING_BINARY = 0x2;
                const XCN_CRYPT_STRING_BASE64REQUESTHEADER = 0x3;
                const XCN_CRYPT_STRING_HEX = 0x4;
                const XCN_CRYPT_STRING_HEXASCII = 0x5;
                const XCN_CRYPT_STRING_BASE64_ANY = 0x6;
                const XCN_CRYPT_STRING_ANY = 0x7;
                const XCN_CRYPT_STRING_HEX_ANY = 0x8;
                const XCN_CRYPT_STRING_BASE64X509CRLHEADER = 0x9;
                const XCN_CRYPT_STRING_HEXADDR = 0xa;
                const XCN_CRYPT_STRING_HEXASCIIADDR = 0xb;
                const XCN_CRYPT_STRING_HEXRAW = 0xc;
                const XCN_CRYPT_STRING_BASE64URI = 0xd;
                const XCN_CRYPT_STRING_ENCODEMASK = 0xff;
                const XCN_CRYPT_STRING_CHAIN = 0x100;
                const XCN_CRYPT_STRING_TEXT = 0x200;
                const XCN_CRYPT_STRING_PERCENTESCAPE = 0x8000000;
                const XCN_CRYPT_STRING_HASHDATA = 0x10000000;
                const XCN_CRYPT_STRING_STRICT = 0x20000000;
                const XCN_CRYPT_STRING_NOCRLF = 0x40000000;
                const XCN_CRYPT_STRING_NOCR = 0x80000000;

                const keyExchange = AT_KEYEXCHANGE;
                const encodingTypeNum = XCN_CRYPT_STRING_BASE64REQUESTHEADER;

                const set_providerType = providerType;
                const set_providerName = providerName;

                let objCSP = yield cadesplugin.CreateObjectAsync("X509Enrollment.CCspInformation");
                let objCSPs = yield cadesplugin.CreateObjectAsync("X509Enrollment.CCspInformations");
                let objPrivateKey = yield cadesplugin.CreateObjectAsync("X509Enrollment.CX509PrivateKey");
                let objCertificateRequestPkcs10 = yield cadesplugin.CreateObjectAsync("X509Enrollment.CX509CertificateRequestPkcs10");
                let objObjectIds = yield cadesplugin.CreateObjectAsync("X509Enrollment.CObjectIds");
                // let objObjectId = yield cadesplugin.CreateObjectAsync("X509Enrollment.CObjectId");
                let objX509ExtensionEnhancedKeyUsage = yield cadesplugin.CreateObjectAsync("X509Enrollment.CX509ExtensionEnhancedKeyUsage");
                // let objExtensionTemplate = yield cadesplugin.CreateObjectAsync("X509Enrollment.CX509ExtensionTemplate");
                let objDn = yield cadesplugin.CreateObjectAsync("X509Enrollment.CX500DistinguishedName");
                let objEnroll = yield cadesplugin.CreateObjectAsync("X509Enrollment.CX509Enrollment");
                let objExtensionKeyUsage = yield cadesplugin.CreateObjectAsync("X509Enrollment.CX509ExtensionKeyUsage");
                let objExtensionDesc = yield cadesplugin.CreateObjectAsync("X509Enrollment.CX509Extension");
                let objObjectIdDesc = yield cadesplugin.CreateObjectAsync("X509Enrollment.CObjectId");
                let objVersion = yield cadesplugin.CreateObjectAsync("CAdESCOM.About");

                yield objPrivateKey.propset_Length(512);
                yield objPrivateKey.propset_KeySpec(keyExchange);
                yield objPrivateKey.propset_ProviderType(set_providerType);
                yield objPrivateKey.propset_ExportPolicy(0);
                yield objPrivateKey.propset_Existing(false);
                yield objPrivateKey.propset_ProviderName(set_providerName);
                yield objPrivateKey.propset_Pin('');
                //objPrivateKey.ContainerName = "sgn-5cd8-7b41-363f-9880";

                yield objCSP.InitializeFromName(providerName);
                yield objCSPs.Add(objCSP);
                yield objCertificateRequestPkcs10.InitializeFromPrivateKey(0x1, objPrivateKey, "");

                let temp1 = yield cadesplugin.CreateObjectAsync("X509Enrollment.CObjectId");
                yield temp1.InitializeFromValue('1.3.6.1.5.5.7.3.2');
                yield objObjectIds.Add(temp1);

                let temp2 = yield cadesplugin.CreateObjectAsync("X509Enrollment.CObjectId");
                yield temp2.InitializeFromValue('1.3.6.1.5.5.7.3.4');
                yield objObjectIds.Add(temp2);

                let oVersion = yield objVersion.CSPVersion(providerName, parseInt(set_providerType, 10));
                let sVer = yield oVersion.toString();

                yield objObjectIdDesc.InitializeFromValue('1.2.643.100.111');

                let ver = parseInt(sVer);
                let cr = 0;

                let strSubjectSignTool;

                if(providerName == providerNameCryptopro) {
                    if (ver >= 4 && ver < 5) {
                        strSubjectSignTool = '"КриптоПро CSP" версия 4.0 (исполнение 2-Base)';
                    } else if (ver >= 5) {
                        strSubjectSignTool = '"КриптоПро CSP" версия 5.0 (исполнение 2-Base)';
                    }
                    cr = strSubjectSignTool.length * 2;
                } else {
                    strSubjectSignTool = '"ViPNet CSP" версия 4.0 (исполнение 2-Base)';
                    cr = (strSubjectSignTool.length * 2) - 27;
                }

                var b64 = window.btoa(unescape(encodeURIComponent(String.fromCharCode(12) + String.fromCharCode(cr) + strSubjectSignTool)));
                yield objExtensionDesc.Initialize(objObjectIdDesc, XCN_CRYPT_STRING_BASE64, b64);
                yield objX509ExtensionEnhancedKeyUsage.InitializeEncode(objObjectIds);

                console.info(subject, strSubjectSignTool, cr, b64);
                yield objDn.Encode(subject);
                yield objCertificateRequestPkcs10.propset_Subject(objDn);
                let X509Extensions = yield objCertificateRequestPkcs10.X509Extensions;
                yield X509Extensions.Add(objX509ExtensionEnhancedKeyUsage);

                yield X509Extensions.Add(objExtensionDesc);

                //objCertificateRequestPkcs10.SmimeCapabilities = true;
                const CERT_DATA_ENCIPHERMENT_KEY_USAGE = 0x10;
                const CERT_KEY_ENCIPHERMENT_KEY_USAGE = 0x20;
                const CERT_DIGITAL_SIGNATURE_KEY_USAGE = 0x80;
                const CERT_NON_REPUDIATION_KEY_USAGE = 0x40;
                yield objExtensionKeyUsage.InitializeEncode(CERT_DIGITAL_SIGNATURE_KEY_USAGE|CERT_NON_REPUDIATION_KEY_USAGE|CERT_KEY_ENCIPHERMENT_KEY_USAGE|CERT_DATA_ENCIPHERMENT_KEY_USAGE);
                yield X509Extensions.Add(objExtensionKeyUsage);
                yield objEnroll.InitializeFromRequest(objCertificateRequestPkcs10);
                pkcs10 = yield objEnroll.CreateRequest(encodingTypeNum);

                console.info(pkcs10);

                __Callback(pkcs10);
            } catch (err) {
                console.log(err);
            }
        });
    });

    return true;
}

var WindowMessageWrapper = function (_React$Component) {
    _inherits(WindowMessageWrapper, _React$Component);

    function WindowMessageWrapper(props) {
        _classCallCheck(this, WindowMessageWrapper);

        var _this = _possibleConstructorReturn(this, (WindowMessageWrapper.__proto__ || Object.getPrototypeOf(WindowMessageWrapper)).call(this, props));

        _this.handleMessage = _this.handleMessage.bind(_this);
        return _this;
    }

    _createClass(WindowMessageWrapper, [{
        key: "handleMessage",
        value: function handleMessage(event) {
            if (this.props.onWindowMessage) this.props.onWindowMessage(event);
        }
    }, {
        key: "render",
        value: function render() {
            return this.props.children;
        }
    }, {
        key: "componentDidMount",
        value: function componentDidMount() {
            window.addEventListener("message", this.handleMessage);
        }
    }, {
        key: "componentWillUnmount",
        value: function componentWillUnmount() {
            window.removeEventListener("message", this.handleMessage);
        }
    }]);

    return WindowMessageWrapper;
}(React.Component);


var BPForm = function (_React$Component2) {
    _inherits(BPForm, _React$Component2);

    function BPForm(props) {
        _classCallCheck(this, BPForm);

        var _this2 = _possibleConstructorReturn(this, (BPForm.__proto__ || Object.getPrototypeOf(BPForm)).call(this, props));

        _this2.state = {};
        _this2.handleChange = _this2.handleChange.bind(_this2);
        _this2.handleFileChange = _this2.handleFileChange.bind(_this2);
        _this2.handleSubmit = _this2.handleSubmit.bind(_this2);
        _this2.handleWindowMessage = _this2.handleWindowMessage.bind(_this2);
        _this2.form = React.createRef();
        _this2.btnSubmit = React.createRef();
        _this2.state.iframeSrc = null;
        _this2.state.canSubmitForm = false;
        _this2.state.filesSigned = false;
        _this2.state.sertRequest = false;

        var fields = _this2.props.fields;
        Object.keys(fields).map(function (fieldId, i) {
            _this2.state[fieldId] = fields[fieldId].value;
        });

        _this2.pp = BX.PopupWindowManager.create("popup-message", null, {
            content: "",
            darkMode: true,
            autoHide: true
        });
        return _this2;
    }

    _createClass(BPForm, [{
        key: "render",
        value: function render() {
            var _this3 = this;

            var textblocks = Object.keys(this.props.fields).map(function (fieldId) {
                return _this3.props.fields[fieldId];
            }).filter(function (field) {
                return field.type == "textblock";
            });

            return React.createElement(
                WindowMessageWrapper,
                {onWindowMessage: this.handleWindowMessage},
                React.createElement(
                    "form",
                    {
                        name: this.props.formName,
                        onSubmit: this.handleSubmit,
                        action: this.props.formAction,
                        method: "POST",
                        encType: "multipart/form-data",
                        ref: this.form
                    },
                    Object.keys(this.props.fields).map(function (fieldId, i) {
                        var field = Object.assign({}, _this3.props.fields[fieldId], {
                            value: _this3.state[fieldId]
                        });
                        field.show = _this3.showField(fieldId);

                        if(typeof _this3.props.fields[fieldId].setval == 'function' && document.querySelector('[data-id="'+fieldId+'"]') !== null) {
                            let cval = document.querySelector('[data-id="'+fieldId+'"]').value;
                            if(cval != '') {
                                if(_this3.state[fieldId] != cval) {
                                    _this3.state[fieldId] = cval;
                                }
                                field.value = cval;
                            }
                        }

                        if (field.type == "hidden") {
                            return React.createElement("input", {
                                type: "hidden",
                                key: field.id,
                                "data-id": field.id,
                                name: field.name,
                                value: field.value,
                                hidden: !field.show
                            });
                        }
                        if (field.type == "treelist") {
                            return React.createElement(FormControlTreeSelect, Object.assign({key: field.id}, field, {handleChange: _this3.handleChange}));
                        }
                        if (field.type == "list") {
                            return React.createElement(FormControlSelect, Object.assign({key: field.id}, field, {handleChange: _this3.handleChange}));
                        }
                        if (field.type == "file") {
                            return React.createElement(FormControlFile, Object.assign({key: field.id}, field, {handleChange: _this3.handleFileChange}));
                        }
                        if (field.type == "filemultiple") {
                            return React.createElement(FormControlFileMultiple, Object.assign({key: field.id}, field, {handleChange: _this3.handleFileChange}));
                        }
                        if (field.type == "date") {
                            return React.createElement(FormControlDate, Object.assign({key: field.id}, field, {handleChange: _this3.handleChange}));
                        }
                        if (field.type == "datetime") {
                            return React.createElement(FormControlDateTime, Object.assign({key: field.id}, field, {handleChange: _this3.handleChange}));
                        }
                        if (field.type == "datetimemultiple") {
                            return React.createElement(FormControlDateTimeMultiple, Object.assign({key: field.id}, field, {handleChange: _this3.handleChange}));
                        }
                        if (field.type == "user") {
                            return React.createElement(FormControlUser, Object.assign({key: field.id}, field, {handleChange: _this3.handleChange}));
                        }
                        if (field.type == "textarea") {
                            return React.createElement(FormControlTextarea, Object.assign({key: field.id}, field, {handleChange: _this3.handleChange}));
                        }
                        if (field.type == "bool") {
                            return React.createElement(FormControlBool, Object.assign({key: field.id}, field, {handleChange: _this3.handleChange}));
                        }
                        if (field.type == "table") {
                            return React.createElement(FormControlTable, Object.assign({key: field.id}, field, {handleChange: _this3.handleChange}));
                        }
                        if (field.type == "textblock") {
                            return null;
                        }
                        return React.createElement(FormControlText, Object.assign({key: field.id}, field, {handleChange: _this3.handleChange}));
                    }),
                    textblocks.length ? React.createElement(
                        "div",
                        {className: "alert alert-warning"},
                        textblocks.map(function (field) {
                            return React.createElement("div", {
                                key: field.id,
                                hidden: !_this3.showField(field.id),
                                dangerouslySetInnerHTML: {__html: field.description}
                            });
                        })
                    ) : null,
                    React.createElement(
                        "button",
                        {type: "submit", className: "btn btn-success submit_btn", ref: this.btnSubmit},
                        React.createElement("span", {dangerouslySetInnerHTML: {__html: this.props.submitText ? this.props.submitText : "Продолжить &rarr;"}})
                    )
                ),
                this.state.iframeSrc ? React.createElement("iframe", {
                    src: this.state.iframeSrc,
                    className: "popup-iframe"
                }) : null
            );
        }
    }, {
        key: "showField",
        value: function showField(fieldId) {
            var field = this.props.fields[fieldId];
            return typeof field.show == "function" ? field.show.call(this) : field.show;
        }
    }, {
        key: "onErrror",
        value: function onErrror(message) {
            this.pp.setContent(message);
            this.pp.show();
        }
    }, {
        key: "getValue",
        value: function getValue(fieldId) {
            return this.state[fieldId];
        }
    }, {
        key: "setValue",
        value: function setValue(fieldId, value) {
            this.state[fieldId] = value;
            return this.state[fieldId];
        }
    }, {
        key: "getValueXmlId",
        value: function getValueXmlId(fieldId) {
            var value = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;

            var field = this.props.fields[fieldId];
            value = value || this.state[fieldId];

            return value ? field.values[value].XML_ID : null;
        }
    }, {
        key: "handleFileChange",
        value: function handleFileChange(_ref) {
            var target = _ref.target;

            var fieldId = target.getAttribute('data-id');
            var fieldVal = target.value;
            this.setState(_defineProperty({}, fieldId, fieldVal ? 1 : 0));
        }
    }, {
        key: "handleChange",
        value: function handleChange(_ref2) {
            var target = _ref2.target;
            var fieldId = target.getAttribute('data-id');
            var fieldVal = target.value;
            this.props.fields[fieldId].value = fieldVal;

            this.setState(_defineProperty({}, fieldId, fieldVal));
        }
    }, {
        key: "handleSubmit",
        value: function handleSubmit(e) {
            var _this4 = this;

            if (this.state.canSubmitForm) return;
            e.preventDefault();
            this.btnSubmit.current.setAttribute('disabled', "disabled");

            var request = null;
            var user_blocks = Object.keys(this.props.fields).filter(function (fieldId) {
                return _this4.props.fields[fieldId].type == "user";
            });
            if (typeof this.props.submitDataType == "undefined" || this.props.submitDataType == "json") {
                var request_vals = Object.assign({}, this.state);
                user_blocks.forEach(function (fieldId) {
                    if (!request_vals[fieldId]) return;
                    request_vals[fieldId] = (request_vals[fieldId].match(/(\d+)/ig) || []).join(',');
                });
                request = JSON.stringify(request_vals);
            } else {
                request = new FormData();

                e.target.querySelectorAll('select, textarea, input[type=text], input[type=date], input[type=number], input[type=email], input[type=hidden], input[type=checkbox]:checked').forEach(function (field) {
                    var val = field.value;
                    var name = field.getAttribute('data-id') || field.getAttribute('name');

                    if(typeof _this4.props.fields[name] != "undefined" && _this4.props.fields[name].type == 'list' && Array.isArray(_this4.props.fields[name].value)) {
                        val = _this4.props.fields[name].value.join(',')
                    }

                    if (!name) return;
                    if (~user_blocks.indexOf(name)) {
                        val = (val.match(/(\d+)/ig) || []).join(',');
                    }
                    request.set(name, val);
                });
                e.target.querySelectorAll('input[type=file]').forEach(function (field) {
                    if (!field.files.length) return;
                    request.append(field.getAttribute('data-id') || field.getAttribute('name'), field.files[0], field.files[0].name);
                });
            }

            fetch(this.props.formAjax, {method: 'POST', body: request, credentials: 'include'})
                .then(function (resp) {
                return resp.json();
                })
                .then(function (resp) {
                    if (resp.status == "ERROR") {
                        _this4.onErrror(resp.status_message);
                        return;
                    }

                    _this4.setState(resp.data.fields, function () {
                        if (resp.status == "OK") {
                            if (typeof resp.alert != "undefined" && resp.alert) {
                                _this4.onErrror(resp.alert);
                            }
                            _this4.setState({canSubmitForm: true}, function () {
                                setTimeout(function () {
                                    _this4.form.current.submit(); //dispatchEvent(new Event('submit'))
                                }, 3000);
                            });
                        } else if (resp.status == "REDIRECT") {
                            if(resp.needRequest) {
                                let keysList = Object.keys(resp.data.fields);
                                _this4.setState({
                                    sertRequest: resp.data.fields[keysList[0]]
                                });
                            }
                            _this4.setState({
                                iframeSrc: resp.data.location
                            });
                        }
                    });
                }).catch(function (error) {
                console.error(error);
                _this4.onErrror(error);
                }).finally(function () {
                    _this4.btnSubmit.current.removeAttribute('disabled');
                });
        }
    }, {
        key: "handleWindowMessage",
        value: function handleWindowMessage(event) {
            var _this5 = this;

            if (!window.location.origin) {
                window.location.origin = window.location.protocol + "//" + window.location.hostname + (window.location.port ? ':' + window.location.port : '');
            }
            if (event.origin !== window.location.origin) {
                return;
            }
            if (event.data == "filesigner_hiden") {
                this.setState({
                    iframeSrc: null
                });
            }
            if (event.data == "filesigner_signed") {
                this.setState({
                    filesSigned: true,
                    iframeSrc: null
                }, function () {
                    if(_this5.state.sertRequest) {
                        $("#workarea-content").find(".ajax-background").show();
                        let areq = new FormData();
                        areq.set('fileid', _this5.state.sertRequest);
                        fetch(
                            _this5.props.formSignRequestAjax,
                            {method: 'POST', body: areq, credentials: 'include'})
                            .then(function (resp) {
                                return resp.json();
                            })
                            .then(function (resp) {
                                if(resp.status == '1') {
                                    let sertData = resp.data[0].cert.subjectName;
                                    let sertStr = '';

                                    Object.entries(sertData).forEach(
                                        function (val) {
                                            if(typeof val == 'object') {
                                                if(val[0] == 'SNILS') {
                                                    sertStr += "OID.1.2.643.100.3=" + val[1] + ";";
                                                } else if(val[0] == 'OGRN') {
                                                    sertStr += "OID.1.2.643.100.1=" + val[1] + ";";
                                                } else if(val[0] == 'INN') {
                                                    sertStr += "OID.1.2.643.3.131.1.1=" + val[1] + ";";
                                                } else if(val[0] == 'O' || val[0] == 'CN') {
                                                    sertStr += val[0] + "=" + val[1] + ";";
                                                } else {
                                                    sertStr += val[0] + "=\"" + val[1].replace(/"/gi, '') + "\";";
                                                }
                                            }
                                        }
                                    );

                                    let idProvid = _this5.props.fields['ep_na_rabochem_meste_polzovatelya_ustanovleno'].value;
                                    let providerData = _this5.props.fields['ep_na_rabochem_meste_polzovatelya_ustanovleno'].values[idProvid].EXTERNAL_ID;

                                    _requestByubject (sertStr, providerData, function(request) {
                                        areq = new FormData();
                                        areq.set('reqConent', request);
                                        areq.set('iblock_id', _this5.props.fields['iblock_id'].value);
                                        fetch(
                                            _this5.props.formSignRequestAjax,
                                            {method: 'POST', body: areq, credentials: 'include'})
                                            .then(function (resp) {
                                                return resp.json();
                                            })
                                            .then(function (resp) {
                                                if(resp.data.id) {
                                                    _this5.setState(_defineProperty({}, 'ep_zapros_na_sertifikat', resp.data.id));
                                                    $("#workarea-content").find(".ajax-background").hide();
                                                    _this5.form.current.submit();
                                                } else {
                                                    console.error('No ID file .req');
                                                }
                                            })
                                            .catch(function (error) {
                                                console.error(error);
                                                _this5.onErrror(error);
                                            });
                                    });
                                }
                            })
                            .catch(function (error) {
                                console.error(error);
                                _this5.onErrror(error);
                            });
                    } else {
                        _this5.form.current.submit();
                    }

                }); //dispatchEvent(new Event('submit'))
            }
            if (event.data == "filesigner_error") {
                this.onErrror("Ошибка. Попробуйте позже");
            }
        }
    }]);

    return BPForm;
}(React.Component);

var FormControlUser = function (_React$Component3) {
    _inherits(FormControlUser, _React$Component3);

    function FormControlUser(props) {
        _classCallCheck(this, FormControlUser);

        var _this6 = _possibleConstructorReturn(this, (FormControlUser.__proto__ || Object.getPrototypeOf(FormControlUser)).call(this, props));

        _this6.form_group__input = React.createRef();
        _this6.form_group__ussel = React.createRef();
        _this6.onChange = _this6.onChange.bind(_this6);
        _this6.userSelector = null;
        return _this6;
    }

    _createClass(FormControlUser, [{
        key: "onChange",
        value: function onChange(employees) {
            var val = [];
            for (var key in employees) {
                var employee = employees[key];
                val.push(employee.name + " [" + employee.id + "]");
            }
            this.form_group__input.current.value = val.join(', ');
            this.props.handleChange({target: this.form_group__input.current});
        }
    }, {
        key: "componentWillUnmount",
        value: function componentWillUnmount() {
            if (!this.userSelector) return;
            BX.removeCustomEvent(this.userSelector, 'on-change', this.onChange);
        }
    }, {
        key: "componentDidMount",
        value: function componentDidMount() {
            var _this7 = this;

            this.form_group__ussel.current.querySelectorAll('script').forEach(function (node) {
                var script = document.createElement('script');
                var varname = null;
                script.innerHTML = node.innerHTML;

                if (node.getAttribute('src')) {
                    script.setAttribute('src', node.getAttribute('src'));
                } else {
                    varname = node.innerHTML.match(/window\[\'(.*?)\'\] = new IntranetUsers/);
                }
                document.body.appendChild(script);
                if (!!varname) {
                    setTimeout(function () {
                        _this7.userSelector = window[varname.pop().trim()];
                        BX.addCustomEvent(_this7.userSelector, 'on-change', _this7.onChange);
                    }, 500);
                }
            });
        }
    }, {
        key: "render",
        value: function render() {
            var _props = this.props,
                id = _props.id,
                show = _props.show,
                title = _props.title,
                value = _props.value,
                description = _props.description,
                custom = _props.custom;

            return React.createElement(
                "div",
                {className: "form-group form-group-userselector", hidden: !show},
                React.createElement(
                    "label",
                    null,
                    title
                ),
                React.createElement("input", {
                    type: "text",
                    className: "form-control",
                    "data-id": id,
                    name: id + "_val",
                    value: value,
                    ref: this.form_group__input
                }),
                React.createElement("div", {
                    dangerouslySetInnerHTML: {__html: custom},
                    className: "userselector",
                    ref: this.form_group__ussel
                }),
                React.createElement("div", {
                    className: "form-text alert alert-secondary py-1 px-3",
                    hidden: description == "",
                    dangerouslySetInnerHTML: {__html: description}
                })
            );
        }
    }]);

    return FormControlUser;
}(React.Component);

var FormControlDate = function (_React$Component4) {
    _inherits(FormControlDate, _React$Component4);

    function FormControlDate(props) {
        _classCallCheck(this, FormControlDate);

        var _this8 = _possibleConstructorReturn(this, (FormControlDate.__proto__ || Object.getPrototypeOf(FormControlDate)).call(this, props));

        _this8.onClick = _this8.onClick.bind(_this8);
        _this8.input = React.createRef();
        return _this8;
    }

    _createClass(FormControlDate, [{
        key: "onClick",
        value: function onClick(_ref3) {
            var target = _ref3.target;

            BX.calendar({node: target, field: target, bTime: false, callback: this.props.callback || null});
        }
    }, {
        key: "componentWillUnmount",
        value: function componentWillUnmount() {
            BX.unbind(BX(this.input.current), 'change', this.props.handleChange);
        }
    }, {
        key: "componentDidMount",
        value: function componentDidMount() {
            BX.bind(BX(this.input.current), 'change', this.props.handleChange);
        }
    }, {
        key: "render",
        value: function render() {
            var _props2 = this.props,
                id = _props2.id,
                show = _props2.show,
                title = _props2.title,
                name = _props2.name,
                value = _props2.value,
                placeholder = _props2.placeholder,
                description = _props2.description,
                handleChange = _props2.handleChange;

            return React.createElement(
                "div",
                {className: "form-group", hidden: !show},
                React.createElement(
                    "label",
                    null,
                    title
                ),
                React.createElement("input", {
                    className: "form-control",
                    name: name,
                    type: "text",
                    "data-id": id,
                    value: value,
                    placeholder: placeholder,
                    onChange: handleChange,
                    onClick: this.onClick,
                    ref: this.input
                }),
                React.createElement("div", {
                    className: "form-text alert alert-secondary py-1 px-3",
                    hidden: description == "",
                    dangerouslySetInnerHTML: {__html: description}
                })
            );
        }
    }]);

    return FormControlDate;
}(React.Component);

var FormControlDateTime = function (_FormControlDate) {
    _inherits(FormControlDateTime, _FormControlDate);

    function FormControlDateTime(props) {
        _classCallCheck(this, FormControlDateTime);

        var _this9 = _possibleConstructorReturn(this, (FormControlDateTime.__proto__ || Object.getPrototypeOf(FormControlDateTime)).call(this, props));

        _this9.onClick = _this9.onClick.bind(_this9);
        return _this9;
    }

    _createClass(FormControlDateTime, [{
        key: "onClick",
        value: function onClick(_ref4) {
            var target = _ref4.target;

            BX.calendar({node: target, field: target, bTime: true, callback: this.props.callback || null});
        }
    }]);

    return FormControlDateTime;
}(FormControlDate);

var FormControlTextarea = function (_React$Component5) {
    _inherits(FormControlTextarea, _React$Component5);

    function FormControlTextarea() {
        _classCallCheck(this, FormControlTextarea);

        return _possibleConstructorReturn(this, (FormControlTextarea.__proto__ || Object.getPrototypeOf(FormControlTextarea)).apply(this, arguments));
    }

    _createClass(FormControlTextarea, [{
        key: "render",
        value: function render() {
            var _props3 = this.props,
                id = _props3.id,
                show = _props3.show,
                type = _props3.type,
                title = _props3.title,
                name = _props3.name,
                value = _props3.value,
                placeholder = _props3.placeholder,
                description = _props3.description,
                handleChange = _props3.handleChange;

            return React.createElement(
                "div",
                {className: "form-group", hidden: !show},
                React.createElement(
                    "label",
                    null,
                    title
                ),
                React.createElement("textarea", {
                    className: "form-control",
                    name: name,
                    type: type,
                    "data-id": id,
                    value: value,
                    placeholder: placeholder,
                    onChange: handleChange,
                    readOnly: type == "readonly"
                }),
                React.createElement("div", {
                    className: "form-text alert alert-secondary py-1 px-3",
                    hidden: description == "",
                    dangerouslySetInnerHTML: {__html: description}
                })
            );
        }
    }]);

    return FormControlTextarea;
}(React.Component);

var FormControlFile = function (_React$Component6) {
    _inherits(FormControlFile, _React$Component6);

    function FormControlFile() {
        _classCallCheck(this, FormControlFile);

        return _possibleConstructorReturn(this, (FormControlFile.__proto__ || Object.getPrototypeOf(FormControlFile)).apply(this, arguments));
    }

    _createClass(FormControlFile, [{
        key: "render",
        value: function render() {
            var _props4 = this.props,
                id = _props4.id,
                show = _props4.show,
                title = _props4.title,
                name = _props4.name,
                placeholder = _props4.placeholder,
                description = _props4.description,
                handleChange = _props4.handleChange;

            return React.createElement(
                "div",
                {className: "form-group", hidden: !show},
                React.createElement(
                    "label",
                    null,
                    title
                ),
                React.createElement("input", {
                    className: "form-control",
                    name: name,
                    type: this.props.type,
                    "data-id": id,
                    placeholder: placeholder,
                    onChange: handleChange
                }),
                React.createElement("div", {
                    className: "form-text alert alert-secondary py-1 px-3",
                    hidden: description == "",
                    dangerouslySetInnerHTML: {__html: description}
                })
            );
        }
    }]);

    return FormControlFile;
}(React.Component);

var FormControlText = function (_React$Component7) {
    _inherits(FormControlText, _React$Component7);

    function FormControlText() {
        _classCallCheck(this, FormControlText);

        return _possibleConstructorReturn(this, (FormControlText.__proto__ || Object.getPrototypeOf(FormControlText)).apply(this, arguments));
    }

    _createClass(FormControlText, [{
        key: "render",
        value: function render() {
            var _props5 = this.props,
                id = _props5.id,
                show = _props5.show,
                type = _props5.type,
                title = _props5.title,
                name = _props5.name,
                value = _props5.value,
                placeholder = _props5.placeholder,
                description = _props5.description,
                handleChange = _props5.handleChange;

            return React.createElement(
                "div",
                {className: "form-group", hidden: !show},
                React.createElement(
                    "label",
                    null,
                    title
                ),
                React.createElement("input", {
                    className: "form-control",
                    name: name,
                    type: type,
                    "data-id": id,
                    value: value,
                    placeholder: placeholder,
                    onChange: handleChange,
                    readOnly: type == "readonly"
                }),
                React.createElement("div", {
                    className: "form-text alert alert-secondary py-1 px-3",
                    hidden: description == "",
                    dangerouslySetInnerHTML: {__html: description}
                })
            );
        }
    }]);

    return FormControlText;
}(React.Component);

var FormControlSelect = function (_React$Component8) {
    _inherits(FormControlSelect, _React$Component8);

    function FormControlSelect(props) {
        _classCallCheck(this, FormControlSelect);

        var _this13 = _possibleConstructorReturn(this, (FormControlSelect.__proto__ || Object.getPrototypeOf(FormControlSelect)).call(this, props));

        _this13.isMultiple = ~_this13.props.name.indexOf('[]');
        _this13.onChange = _this13.onChange.bind(_this13);
        return _this13;
    }

    _createClass(FormControlSelect, [{
        key: "onChange",
        value: function onChange(e) {
            var options = e.target.querySelectorAll('option:checked');

            this.props.handleChange({
                target: {
                    getAttribute: function getAttribute(name) {
                        return e.target.getAttribute(name);
                    },
                    value: this.isMultiple ? [].concat(_toConsumableArray(options)).map(function (option) {
                        return option.value;
                    }) : e.target.value
                }
            });
        }
    }, {
        key: "render",
        value: function render() {
            var _props6 = this.props,
                id = _props6.id,
                show = _props6.show,
                title = _props6.title,
                name = _props6.name,
                value = _props6.value,
                values = _props6.values,
                description = _props6.description,
                handleChange = _props6.handleChange;

            var vals = Object.keys(values).map(function (key) {
                return values[key];
            }).sort(function (a, b) {
                return a.SORT > b.SORT;
            });

            var curValue = value;
            if (this.isMultiple && !!curValue && (typeof curValue === "undefined" ? "undefined" : _typeof(curValue)) != "object") {
                curValue = [curValue];
            }
            return React.createElement(
                "div",
                {className: "form-group", hidden: !show},
                React.createElement(
                    "label",
                    null,
                    title
                ),
                React.createElement(
                    "select",
                    {
                        className: "form-control",
                        name: name,
                        "data-id": id,
                        value: curValue,
                        onChange: this.onChange,
                        multiple: this.isMultiple
                    },
                    vals.map(function (val) {
                        return React.createElement(
                            "option",
                            {key: val.XML_ID, "data-id": val.XML_ID, value: val.ID},
                            val.VALUE
                        );
                    })
                ),
                React.createElement("div", {
                    className: "form-text alert alert-secondary py-1 px-3",
                    hidden: description == "",
                    dangerouslySetInnerHTML: {__html: description}
                })
            );
        }
    }]);

    return FormControlSelect;
}(React.Component);

var FormControlFileMultiple = function FormControlFileMultiple(props) {
    var id = props.id,
        show = props.show,
        title = props.title,
        name = props.name,
        placeholder = props.placeholder,
        description = props.description,
        handleChange = props.handleChange;

    var _React$useState = React.useState(1),
        _React$useState2 = _slicedToArray(_React$useState, 2),
        fieldsCount = _React$useState2[0],
        setFieldsCount = _React$useState2[1];

    var fields = [];
    for (var i = 0; i < fieldsCount; i++) {
        fields.push(React.createElement("input", {
            key: id + "_" + i,
            className: "form-control mb-2",
            name: name + "[n" + i + "][VALUE]",
            type: "file",
            "data-id": id + "_" + i,
            placeholder: placeholder
        }));
    }
    return React.createElement(
        "div",
        {className: "form-group", hidden: !show},
        React.createElement(
            "label",
            null,
            title
        ),
        fields,
        React.createElement(
            "div",
            {className: "mb-3"},
            React.createElement(
                "button",
                {
                    className: "btn btn-sm btn-secondary", type: "button", onClick: function onClick() {
                        return setFieldsCount(fieldsCount + 1);
                    }
                },
                "\u0414\u043E\u0431\u0430\u0432\u0438\u0442\u044C \u0444\u0430\u0439\u043B"
            )
        ),
        React.createElement("div", {
            className: "form-text alert alert-secondary py-1 px-3",
            hidden: description == "",
            dangerouslySetInnerHTML: {__html: description}
        })
    );
};
var FormControlTreeSelect = function FormControlTreeSelect(props) {
    var id = props.id,
        show = props.show,
        title = props.title,
        name = props.name,
        value = props.value,
        values = props.values,
        description = props.description,
        handleChange = props.handleChange;


    var getParent = function getParent(curValue) {
        var items = [];
        if (curValue && values[curValue].PARENT) {
            items.push(+values[curValue].PARENT);
            items = items.concat(getParent(values[curValue].PARENT));
        }

        return items;
    };
    var renderOptions = function renderOptions(items) {
        return items.map(function (val) {
            return React.createElement(
                "option",
                {key: val.XML_ID, "data-id": val.XML_ID, value: val.ID},
                val.VALUE
            );
        });
    };

    var onChage = function onChage(e) {
        if (+e.target.value) {
            handleChange(e);
        }
    };

    var valueParent = value ? values[value].PARENT : null;
    var sortedValues = Object.keys(values).map(function (key) {
        return values[key];
    }).sort(function (a, b) {
        return a.SORT > b.SORT;
    });

    var childOptions = sortedValues.filter(function (val) {
        return val.PARENT == value;
    });
    var parents = getParent(value).reverse();

    return React.createElement(
        "div",
        {className: "form-group", hidden: !show},
        React.createElement(
            "label",
            null,
            title
        ),
        parents.map(function (item) {
            var itemOb = values[item];
            return React.createElement(
                "select",
                {
                    key: "select_parent_" + itemOb.ID,
                    className: "form-control mb-3",
                    "data-id": id,
                    value: itemOb.ID,
                    onChange: onChage
                },
                renderOptions(sortedValues.filter(function (val) {
                    return val.PARENT == itemOb.PARENT;
                }))
            );
        }),
        React.createElement(
            "select",
            {
                key: "select_" + id,
                className: "form-control mb-3",
                name: name,
                "data-id": id,
                value: value,
                onChange: onChage
            },
            React.createElement(
                "option",
                {value: "0"},
                "\u0412\u044B\u0431\u0440\u0430\u0442\u044C"
            ),
            renderOptions(sortedValues.filter(function (val) {
                return val.PARENT == valueParent;
            }))
        ),
        childOptions.length ? React.createElement(
            "select",
            {key: "select_child_" + id, className: "form-control", "data-id": id, value: "0", onChange: onChage},
            React.createElement(
                "option",
                {value: "0"},
                "\u0412\u044B\u0431\u0440\u0430\u0442\u044C"
            ),
            renderOptions(childOptions)
        ) : null,
        React.createElement("div", {
            className: "form-text alert alert-secondary py-1 px-3",
            hidden: description == "",
            dangerouslySetInnerHTML: {__html: description}
        })
    );
};

var FormControlBool = function FormControlBool(props) {
    var id = props.id,
        value = props.value,
        values = props.values,
        show = props.show,
        title = props.title,
        description = props.description,
        handleChange = props.handleChange;

    var input = React.useRef(value);
    var onClick = function onClick(e) {
        input.current.value = +value ? 0 : 1;
        handleChange(Object.assign({}, e, {target: input.current}));
    };
    return React.createElement(
        "div",
        {className: "form-group", hidden: !show},
        React.createElement("input", {type: "hidden", "data-id": id, ref: input}),
        React.createElement(
            "button",
            {type: "button", className: "btn btn-primary btn-sm", onClick: onClick},
            values[value]
        ),
        React.createElement("div", {
            className: "form-text alert alert-secondary py-1 px-3",
            hidden: description == "",
            dangerouslySetInnerHTML: {__html: description}
        })
    );
};
var FormControlDateTimeMultiple = function FormControlDateTimeMultiple(props) {
    var id = props.id,
        value = props.value,
        title = props.title,
        show = props.show,
        name = props.name,
        handleChange = props.handleChange;

    var _React$useState3 = React.useState(1),
        _React$useState4 = _slicedToArray(_React$useState3, 2),
        fieldsCount = _React$useState4[0],
        setFieldsCount = _React$useState4[1];

    var fields = [];
    var curValue = Array.isArray(value) && value || [];

    var setValue = function setValue(i, value) {
        curValue[i] = value;
        handleChange({
            target: {
                getAttribute: function getAttribute(name) {
                    return name == 'data-id' ? id : null;
                },
                value: curValue
            }
        });
    };
    var deleteValue = function deleteValue(i) {
        curValue.splice(i, 1);
        setFieldsCount(fieldsCount - 1);
        handleChange({
            target: {
                getAttribute: function getAttribute(name) {
                    return name == 'data-id' ? id : null;
                },
                value: curValue
            }
        });
    };

    var _loop = function _loop(i) {
        fields.push(React.createElement(
            "div",
            {key: props.id + "_" + i, className: "row"},
            React.createElement(
                "div",
                {className: "col-md-11"},
                React.createElement(FormControlDateTime, Object.assign({}, props, {
                    value: curValue[i] || '',
                    title: title + " #" + (i + 1),
                    id: id + "[" + i + "]",
                    name: name + "[n" + i + "][VALUE]",
                    handleChange: function handleChange(e) {
                        setValue(i, e.target.value);
                    }
                }))
            ),
            React.createElement(
                "div",
                {className: "col-md-1"},
                React.createElement(
                    "label",
                    {style: {opacity: 0}},
                    "\u0423\u0434\u0430\u043B\u0438\u0442\u044C"
                ),
                React.createElement(
                    "button",
                    {
                        type: "button", className: "btn btn-sm btn-block btn-danger", onClick: function onClick(e) {
                            deleteValue(i);
                        }
                    },
                    "\xD7"
                )
            )
        ));
    };

    for (var i = 0; i < fieldsCount; i++) {
        _loop(i);
    }
    return React.createElement(
        "div",
        {className: "form-group", hidden: !show},
        React.createElement(
            "label",
            null,
            title
        ),
        React.createElement(
            "div",
            {className: "card"},
            React.createElement(
                "div",
                {className: "card-body"},
                fields
            ),
            React.createElement(
                "div",
                {className: "card-footer"},
                React.createElement(
                    "button",
                    {
                        type: "button", className: "btn btn-primary btn-sm", onClick: function onClick(e) {
                            setFieldsCount(fieldsCount + 1);
                        }
                    },
                    "\u0414\u043E\u0431\u0430\u0432\u0438\u0442\u044C"
                )
            )
        )
    );
};
var FormControlTable = function FormControlTable(props) {
    var id = props.id,
        name = props.name,
        value = props.value,
        title = props.title,
        table = props.table,
        show = props.show,
        handleChange = props.handleChange;

    var _React$useState5 = React.useState(1),
        _React$useState6 = _slicedToArray(_React$useState5, 2),
        rowsCount = _React$useState6[0],
        setRowsCount = _React$useState6[1];

    var mapValueToCurvValue = props.mapValueToCurvValue || function (value) {
        var vals = [];
        if (Array.isArray(value)) {
            value.forEach(function (val) {
                vals.push(val.split(', '));
            });
        }
        return vals;
    };
    var mapCurValueToValue = props.mapCurValueToValue || function (values) {
        var vals = [];
        values.forEach(function (val) {
            vals.push(val.join(', '));
        });
        return vals;
    };
    var setCurValueItem = function setCurValueItem(i, j, value) {
        if (typeof curValue[i] === 'undefined') {
            curValue[i] = [];
        }
        curValue[i][j] = value;
        handleChange({
            target: {
                getAttribute: function getAttribute(name) {
                    return name == 'data-id' ? id : null;
                },
                value: mapCurValueToValue(curValue)
            }
        });
    };
    var removCurValueItem = function removCurValueItem(i) {
        if (rowsCount == 1) return;
        curValue.splice(i, 1);
        setRowsCount(rowsCount - 1);
        handleChange({
            target: {
                getAttribute: function getAttribute(name) {
                    return name == 'data-id' ? id : null;
                },
                value: mapCurValueToValue(curValue)
            }
        });
    };
    var curValue = mapValueToCurvValue(value);
    var rows = [];

    var _loop2 = function _loop2(i) {
        rows.push(React.createElement(
            "div",
            {key: id + "_row_" + i, className: "row mb-2"},
            React.createElement(
                "div",
                {className: "col-md-11"},
                React.createElement(
                    "div",
                    {className: "row"},
                    table.columns.map(function (item, j) {
                        return React.createElement(
                            "div",
                            {key: id + "_row_" + i + "_col_" + j, className: 'col-md-' + 12 / table.columns.length},
                            React.createElement("input", {
                                type: "text",
                                className: "form-control",
                                value: curValue[i] && curValue[i][j] || '',
                                onChange: function onChange(e) {
                                    return setCurValueItem(i, j, e.target.value);
                                }
                            })
                        );
                    })
                )
            ),
            React.createElement(
                "div",
                {className: "col-md-1"},
                React.createElement(
                    "button",
                    {
                        type: "button", className: "btn btn-sm btn-block btn-danger", onClick: function onClick(e) {
                            removCurValueItem(i);
                        }
                    },
                    "\xD7"
                )
            )
        ));
    };

    for (var i = 0; i < rowsCount; i++) {
        _loop2(i);
    }
    return React.createElement(
        "div",
        {className: "form-group", hidden: !show},
        React.createElement(
            "label",
            null,
            title
        ),
        React.createElement(
            "div",
            {className: "card"},
            React.createElement(
                "div",
                {className: "card-body"},
                React.createElement(
                    "div",
                    {className: "row mb-3"},
                    React.createElement(
                        "div",
                        {className: "col-md-11"},
                        React.createElement(
                            "div",
                            {className: "row"},
                            table.columns.map(function (item, i) {
                                return React.createElement(
                                    "div",
                                    {key: id + "_header_" + item.id, className: 'col-md-' + 12 / table.columns.length},
                                    React.createElement(
                                        "strong",
                                        null,
                                        item.title
                                    )
                                );
                            })
                        )
                    ),
                    React.createElement("div", {className: "col-md-1"})
                ),
                rows,
                Array.isArray(value) && value.map(function (valueItem, i) {
                    return React.createElement("input", {
                        key: id + "_value_" + i,
                        type: "hidden",
                        "data-id": id + "[" + i + "]",
                        name: name + "[n" + i + "][VALUE]",
                        value: valueItem
                    });
                })
            ),
            React.createElement(
                "div",
                {className: "card-footer"},
                React.createElement(
                    "button",
                    {
                        type: "button", className: "btn btn-primary btn-sm", onClick: function onClick(e) {
                            setRowsCount(rowsCount + 1);
                        }
                    },
                    "\u0414\u043E\u0431\u0430\u0432\u0438\u0442\u044C"
                )
            )
        )
    );
};