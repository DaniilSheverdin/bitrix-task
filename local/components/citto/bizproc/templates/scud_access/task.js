$('body').ready(function() {
    let obField = $('[name="bpriact_DATA"]');

    if (obField.length > 0) {
        obField.parent().parent().hide();
        let sJsonData = obField.val();
        let obData = JSON.parse(sJsonData);
        let sTr = '';
        obData.forEach(function(item) {
            console.log(item);
            let sPosition = (item.POSITION == null)? '' : item.POSITION;
            sTr += `
            <tr>
              <th scope="row"><div class="form-check"><input class="check form-check-input" type="checkbox" checked></div></th>
              <td><div class="form-group"><input type="text" class="fio form-control" value="${item.FIO}" required></div></td>
<!--              <td><div class="form-group"><input type="text" class="passport form-control" value="" required></div></td>-->
              <td><div class="form-group"><input type="text" class="position form-control" value="${sPosition}" required></div></td>
<!--              <td><div class="form-group"><input type="text" class="order form-control" value="" required></div></td>-->
              <td><div class="form-group"><textarea type="text" class="devices form-control" required>${item.DEVICES.join('\n')}</textarea></div></td>
            </tr>`;
        });

        let sTable = `
        <table id="data-table" class="table">
          <thead>
            <tr>
              <th scope="col" width="20px">#</th>
              <th scope="col" width="300px">ФИО*</th>
<!--              <th scope="col" width="200px">Паспортные данные (серия, номер)*</th>-->
              <th scope="col" width="200px">Должность*</th>
<!--              <th scope="col" width="200px">Приказ о приеме на работу (дата, номер)*</th>-->
              <th scope="col">Турникеты*</th>
            </tr>
          </thead>
          <tbody>${sTr}</tbody>
        </table>
        `;

        $('.bizproc-table-main').before(sTable);

        $('body').on('click', '.check', function (e) {
            let bRequired = $(this).is(':checked');

            $(this).closest('tr').find('.form-control').each(function() {
                $(this).prop('required', bRequired);
                $(this).prop('disabled', !bRequired);
            });
        });

        $('body').on('click', '[name="approve"]', function (e) {
            let arResult = [];

            $('#data-table tbody tr').each(function() {
                if ($(this).find('.check').is(':checked')){
                    arResult.push({
                        'FIO' : $(this).find('.fio').val(),
                        // 'PASSPORT' : $(this).find('.passport').val(),
                        'POSITION' : $(this).find('.position').val(),
                        // 'ORDER' : $(this).find('.order').val(),
                        'DEVICES' : $(this).find('.devices').text().split('\n'),
                    });
                }
            });

            obField.val(JSON.stringify(arResult));
        });
    }
});
