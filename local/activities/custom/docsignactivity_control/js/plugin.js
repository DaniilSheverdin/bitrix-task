function GetErrorMessage(e) {
    let err = e.message;
    if (!err) {
        err = e;
    } else if (e.number) {
        err += " (" + e.number + ")";
    }
    return err;
}

function SignCreate(certSubjectName, dataToSign, cosign, source) {
    cosign = cosign || false;
    return new Promise(function(resolve, reject){
        GetCertificateBySubjectName(certSubjectName).then(function(oCertificate){
            cadesplugin.async_spawn(function *(args) {
                try {    
                    let oStore = yield cadesplugin.CreateObjectAsync("CAPICOM.Store");
                    yield oStore.Open(CAPICOM_CURRENT_USER_STORE, CAPICOM_MY_STORE,CAPICOM_STORE_OPEN_MAXIMUM_ALLOWED);

                    let oSigner = yield cadesplugin.CreateObjectAsync("CAdESCOM.CPSigner");
                    yield oSigner.propset_Certificate(oCertificate);

                    let oSignedData = yield cadesplugin.CreateObjectAsync("CAdESCOM.CadesSignedData");
                    
                    
                    let sSignedMessage;
                    if(cosign){
                        yield oSignedData.propset_ContentEncoding(CADESCOM_BASE64_TO_BINARY);
                        yield oSignedData.propset_Content(source);
                        oSignedData.VerifyCades(dataToSign, CADESCOM_CADES_BES, false);
                        
                        sSignedMessage = yield oSignedData.CoSignCades(oSigner, CADESCOM_CADES_BES, 0);
                    }else{
                        yield oSignedData.propset_ContentEncoding(CADESCOM_BASE64_TO_BINARY);
                        yield oSignedData.propset_Content(dataToSign);
                        sSignedMessage = yield oSignedData.SignCades(oSigner, CADESCOM_CADES_BES, false);
                    }
                    
                    yield oStore.Close();
                    args[2](sSignedMessage);
                    resolve(true);
                } catch (err) {
                    reject(err);
                }
            }, oCertificate, dataToSign, resolve, reject);
        });
    });
}

/**
 * Формирование строчки сертификата
 */
function CertificateAdjuster() {

    this.extract = function(from, what) {
        certName = "";

        let begin = from.indexOf(what);

        if(begin>=0)
        {
            let end = from.indexOf(', ', begin);
            certName = (end<0) ? from.substr(begin) : from.substr(begin, end - begin);
        }

        return certName;
    };

    this.Print2Digit = function(digit){
        return (digit<10) ? "0"+digit : digit;
    };

    this.GetCertDate = function(paramDate) {
        let certDate = new Date(paramDate);
        return this.Print2Digit(certDate.getUTCDate())+"."+this.Print2Digit(certDate.getMonth()+1)+"."+certDate.getFullYear() + " " +
                 this.Print2Digit(certDate.getUTCHours()) + ":" + this.Print2Digit(certDate.getUTCMinutes()) + ":" + this.Print2Digit(certDate.getUTCSeconds());
    };

    this.GetCertName = function(certSubjectName){
        return this.extract(certSubjectName, 'CN=');
    };

    this.GetIssuer = function(certIssuerName){
        return this.extract(certIssuerName, 'CN=');
    };

    this.GetCertInfoString = function(certSubjectName, certFromDate, issuedBy){
        return this.extract(certSubjectName,'CN=') + "; Выдан: " + this.GetCertDate(certFromDate) + " " + issuedBy;
    };

}

function FillCertList_NPAPI() {
   let certList = {};

   return new Promise(function(resolve, reject){
       cadesplugin.async_spawn(function *(args) {
               try {

                    let oStore = yield cadesplugin.CreateObjectAsync("CAPICOM.Store");
                    yield oStore.Open(CAPICOM_CURRENT_USER_STORE, CAPICOM_MY_STORE,CAPICOM_STORE_OPEN_MAXIMUM_ALLOWED);

                    let CertificatesObj = yield oStore.Certificates;
                    let Count = yield CertificatesObj.Count;

                    if (Count == 0) {
                        certList.globalCountCertificate = 0;
                        reject({certListNotFound: true});
                        return;
                    }

                    if (!Array.isArray(certList.globalOptionList)) {
                        certList.globalOptionList = [];
                    }

                    let text;
                    let dateObj = new Date();
                    let count = 0;

                      for (let i = 1; i <= Count; i++) {
                           let cert = yield CertificatesObj.Item(i);

                           let ValidToDate = new Date((yield cert.ValidToDate));
                           let ValidFromDate = new Date((yield cert.ValidFromDate));
                           let HasPrivateKey = yield cert.HasPrivateKey();
                           let Validator = yield cert.IsValid();
                           let IsValid = yield Validator.Result;

                           if( dateObj < new Date(ValidToDate) && IsValid && HasPrivateKey) {
                            let issuedBy = yield cert.GetInfo(1);
                            issuedBy = issuedBy || "";

                            text = new CertificateAdjuster().GetCertInfoString(yield cert.SubjectName, ValidFromDate, issuedBy);
                            certList.globalOptionList.push({
                                'value': text.replace(/^cn=([^;]+);.+/i, '$1'),
                                'text' : text.replace("CN=", "")
                            });

                            count++;

                           }

                        }

                        certList.globalCountCertificate = count;
                        resolve(certList.globalOptionList, certList);

                } catch (err) {
                   reject(err);
                }
            });


    });

}

function GetCertificateBySubjectName(certSubjectName) {
    return new Promise(function(resolve, reject){
        cadesplugin.CreateObjectAsync("CAdESCOM.Store").then(function(oStore){
            oStore.Open(CAPICOM_CURRENT_USER_STORE, CAPICOM_MY_STORE, CAPICOM_STORE_OPEN_MAXIMUM_ALLOWED);
            
            oStore.Certificates.then(function(oCertificatesSearch){
                oCertificatesSearch.Find(CAPICOM_CERTIFICATE_FIND_SUBJECT_NAME, certSubjectName).then(function(oCertificates){
                   
                    oCertificates.Item(1).then(function(oCertificate){
                        resolve(oCertificate);
                    });
                })
            });
        });
    });
}
