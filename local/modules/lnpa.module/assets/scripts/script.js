const arReferences = [
    'TYPE',
    'EXTINT',
    'PARTNER',
    'MAIN'
];

function buildReference(arReferences) {
    arReferences.forEach(function (field) {
        $('#js-block-' + field).hide();

        let sElementsList = $('#js-node-' + field).val();
        let arElementsList = sElementsList.split("\n");

        let arNodeElementsList = arElementsList.map(function (name) {
            return `<div><span>${name}</span><a href="#" class="js-del del" data-js-ref="${field}">x</a></div>`;
        });

        arNodeElementsList = (sElementsList.length == 0) ? [] : arNodeElementsList;

        let sNodeElementsList = arNodeElementsList.join('');

        let sNewBlock = `
            <tr>
                <td valign="top" width="40%" class="adm-detail-content-cell-l"><a href="#" class="js-add" data-js-ref="${field}">Добавить</a></td>
                <td valign="top" nowrap="" class="adm-detail-content-cell-r">
                    <div class="flex">
                    <input type="text" id="js-new-ref-${field}" class="input-add">
                    </div>
                </td>
            </tr>
            <tr>
                <td></td>
                <td id="js-list-${field}">
                    ${sNodeElementsList}
                </td>
            </tr>
            `;
        $('#js-block-' + field).after(sNewBlock);
    });
}

$('document').ready(function () {
    buildReference(arReferences);

    $('body').on('click', '.js-add, .js-del', function (e) {
        e.preventDefault();
        let obThis = $(this);
        sRef = $(this).attr("data-js-ref");
        sNewRef = $('#js-new-ref-' + sRef).val();
        sValueRef = $('#js-node-' + sRef).val();
        arValuesRef = sValueRef.split("\n");

        if ($(this).hasClass('js-add')) {
            if (sNewRef.length > 0 && !arValuesRef.includes(sNewRef)) {
                arValuesRef = (sValueRef.length == 0) ? [] : arValuesRef;
                arValuesRef.push(sNewRef);
                $('#js-list-' + sRef).append(`<div><span>${sNewRef}</span><a href="#" class="js-del del" data-js-ref="${sRef}">x</a></div>`);
            }
        } else {
            let sText = $(this).parent().find('span').text();
            arValuesRef = arValuesRef.filter(function (ref) {
                if (ref == sText) {
                    obThis.parent().detach();
                }
                return (ref != sText);
            });
        }

        $('#js-node-' + sRef).val(arValuesRef.join("\n"));
    });

});