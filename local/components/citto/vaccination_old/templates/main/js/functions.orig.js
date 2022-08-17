Date.prototype.addDays = function(days) {
  var date = new Date(this.valueOf());
  date.setDate(date.getDate() + days);
  return date;
}

const getUrlParameter = function getUrlParameter(sParam) {
  let sPageURL = decodeURIComponent(window.location.search.substring(1)),
    sURLVariables = sPageURL.split('&'),
    sParameterName,
    i;

  for (i = 0; i < sURLVariables.length; i++) {
    sParameterName = sURLVariables[i].split('=');

    if (sParameterName[0] === sParam) {
      return sParameterName[1] === undefined ? true : sParameterName[1];
    }
  }
};

function insertParam(key, value) {
  key = encodeURIComponent(key);
  value = encodeURIComponent(value);

  // kvp looks like ['key1=value1', 'key2=value2', ...]
  var kvp = document.location.search.substr(1).split('&');
  let i=0;

  for(; i<kvp.length; i++){
    if (kvp[i].startsWith(key + '=')) {
      let pair = kvp[i].split('=');
      pair[1] = value;
      kvp[i] = pair.join('=');
      break;
    }
  }

  if(i >= kvp.length){
    kvp[kvp.length] = [key,value].join('=');
  }

  // can return this or...
  let params = kvp.join('&');

  // reload page with new params
  document.location.search = params;
}

function deleteParam(key) {
  key = encodeURIComponent(key);

  var kvp = document.location.search.substr(1).split('&');

  let i = 0;

  for(; i<kvp.length; i++){
    if (kvp[i].startsWith(key + '=')) {
      let pair = kvp[i].split('=');
      console.log('pair', pair)
      if (pair[0] === key) {
        delete kvp[i]
      }
      break;
    }
  }

  const newKVP = []
  kvp.forEach(el => {
    if (el || el === '') {
      newKVP.push(el)
    }
  })

  document.location.search = newKVP.join('&');
}



async function request(action, query = []) {

  var responseData
  var formData = new FormData

  if (query.length) {
    query.forEach(function(el) {
      formData.append(el.name, el.value)
    })
  }


  const queryControl = {c: 'citto:vaccination', action, mode: 'class'},
    requestControl = $.ajax({
      url: '/bitrix/services/main/ajax.php?' + $.param(queryControl, true),
      type: 'POST',
      processData: false,
      contentType: false,
      data: formData
    });

  await requestControl.done(function ({data}) {
    responseData = data
  });

  requestControl.fail(function () {
    console.error('Can not get data');
  });

  return responseData
}




async function requestRows(action, query = []) {

  var responseData
  var formData = new FormData

  if (query.length) {
    query.forEach(function(el) {
      formData.append('data[]', JSON.stringify(el))
    })
  }


  const queryControl = {c: 'citto:vaccination', action, mode: 'class'},
    requestControl = $.ajax({
      url: '/bitrix/services/main/ajax.php?' + $.param(queryControl, true),
      type: 'POST',
      processData: false,
      contentType: false,
      data: formData
    });

  await requestControl.done(function ({data}) {
    responseData = data
  });

  requestControl.fail(function () {
    console.error('Can not get data');
  });

  return responseData
}

function changeYear(input) {
  var el = $('[id ^= "calendar_popup_year_"]'); //найдем div  с календарем
  var link = el.find(".bx-calendar-year-input");
  $(link[0]).attr({'onkeyup': 'changeCalendar(this);', 'data-name': $(input).attr('data-name')}); //повесим событие на ввод года
  var links = el.find(".bx-calendar-year-number");
  for (var i = 0; i < links.length; i++) {
    $(links[i]).attr({'onclick': 'changeCalendar(this);', 'data-name': $(input).attr('data-name')}); //повесим событие на выбор года
  }
}

function changeMonth(input) {
  var el = $('[id ^= "calendar_popup_month_"]'); //найдем div  с календарем
  var links = el.find(".bx-calendar-month");
  for (var i = 0; i < links.length; i++) {
    $(links[i]).attr({'onclick': 'changeCalendar(this);', 'data-name': $(input).attr('data-name')}); //повесим событие на выбор месяца
  }
}


function changeCalendar(input) {

  console.log('input', input)

  setTimeout(function () {
    var el = $('[id ^= "calendar_popup_"]'); //найдем div  с календарем
    var links = el.find(".bx-calendar-cell"); //найдем элементы отображающие дни
    $('.bx-calendar-left-arrow').attr({'onclick': 'changeCalendar(this);', 'data-name': $(input).attr('name')}); //вешаем функцию изменения  календаря на кнопку смещения календаря на месяц назад
    $('.bx-calendar-right-arrow').attr({'onclick': 'changeCalendar(this);', 'data-name': $(input).attr('name')}); //вешаем функцию изменения  календаря на кнопку смещения календаря на месяц вперед
    $('.bx-calendar-top-month').attr({'onclick': 'changeMonth(this);', 'data-name': $(input).attr('name')}); //вешаем функцию изменения  календаря на кнопку выбора месяца
    $('.bx-calendar-top-year').attr({'onclick': 'changeYear(this);', 'data-name': $(input).attr('name')}); //вешаем функцию изменения  календаря на кнопку выбора года
    const date = new Date();
    const dateEnd = new Date('2021-10-01')
    const dateJuneEnd = new Date('2021-06-28')
    const dateJuneStart = new Date('2021-06-27')
    const dateJuleEnd = new Date('2021-07-04')
    const dateJuleStart = new Date('2021-07-05')
    const dateJuleEnd2 = new Date('2021-07-11')
    const dateJuleStart2 = new Date('2021-07-12')

    const dateNow = new Date();
    let nextDays = 1
    console.log(dateNow.getHours());



    if (dateNow.getHours() > 14) {
      nextDays = 2
    }


    date.setDate(date.getDate() + nextDays);
    console.log(date)

    for (var i =0; i < links.length; i++)
    {
      const atrDate = links[i].attributes['data-date'].value;

      let name = $(input).attr('name');
      if (name === undefined) {
        name = $(input).attr('data-name');
      }
      console.log('name',name)

      if (name === 'vac_date') {
        if (date - atrDate > 24*60*60*1000
          || dateEnd - atrDate < 24*60*60*1000
          || dateJuneStart - atrDate == 24*60*60*1000
          || dateJuleStart - atrDate == 24*60*60*1000
          || dateJuleStart2 - atrDate == 24*60*60*1000
          || dateJuneEnd - atrDate == 24*60*60*1000
          || dateJuleEnd - atrDate == 24*60*60*1000
          || dateJuleEnd2 - atrDate == 24*60*60*1000

        ) {
          $('[data-date="' + atrDate +'"]').addClass("bx-calendar-date-hidden disabled"); //меняем класс у элемента отображающего день, который меньше по дате чем текущий день
        }
      } else {
        if (date - atrDate > 24*60*60*1000
          || dateEnd - atrDate < 24*60*60*1000
          || dateJuneStart - atrDate == 24*60*60*1000
          || dateJuleStart - atrDate == 24*60*60*1000
          || dateJuleStart2 - atrDate == 24*60*60*1000
          || dateJuneEnd - atrDate == 24*60*60*1000
          || dateJuleEnd - atrDate == 24*60*60*1000
          || dateJuleEnd2 - atrDate == 24*60*60*1000

        ) {
          $('[data-date="' + atrDate +'"]').removeClass("bx-calendar-date-hidden disabled"); //меняем класс у элемента отображающего день, который меньше по дате чем текущий день
        }
      }
    }
  },10)

}



function getTable(data, filter) {
  console.log('downloading xls...');
  console.log('get Table ', data);

  var queryControl = {
      c: 'citto:vaccination',
      action: 'getTable',
      mode: 'class'
    },
    requestControl = $.ajax({
      url: '/bitrix/services/main/ajax.php?' + $.param(queryControl, true),
      type: 'POST',
      data: 'columns=' + JSON.stringify(data)+'&filter='+JSON.stringify(filter),
    });

  requestControl.done(function (result) {
    console.log('result ', result);

    if (result.data.list.length) {
      var xlsData = [];
      result.data.list.forEach(function (row, i) {
        xlsData[i] = [];
        xlsData[i] = row;
      });

      var xls = new XlsExport(xlsData, 'Информация о записи на вакцинацию');
      isFetch = true;
      xls.exportToXLS('Информация о записи на вакцинацию.xls');
    }
    console.log('xlsData', xlsData);

  });

  requestControl.fail(function () {
    console.error('Can not get table');
  });
}



function removeDuplicates(arr) {

  const result = [];
  const duplicatesIndices = [];

  // Перебираем каждый элемент в исходном массиве
  arr.forEach((current, index) => {

    if (duplicatesIndices.includes(index)) return;

    result.push(current);

    // Сравниваем каждый элемент в массиве после текущего
    for (let comparisonIndex = index + 1; comparisonIndex < arr.length; comparisonIndex++) {

      const comparison = arr[comparisonIndex];
      const currentKeys = Object.keys(current);
      const comparisonKeys = Object.keys(comparison);

      // Проверяем длину массивов
      if (currentKeys.length !== comparisonKeys.length) continue;

      // Проверяем значение ключей
      const currentKeysString = currentKeys.sort().join("").toLowerCase();
      const comparisonKeysString = comparisonKeys.sort().join("").toLowerCase();
      if (currentKeysString !== comparisonKeysString) continue;

      // Проверяем индексы ключей
      let valuesEqual = true;
      for (let i = 0; i < currentKeys.length; i++) {
        const key = currentKeys[i];
        if ( current[key] !== comparison[key] ) {
          valuesEqual = false;
          break;
        }
      }
      if (valuesEqual) duplicatesIndices.push(comparisonIndex);

    } // Конец цикла
  });
  return result;
}
