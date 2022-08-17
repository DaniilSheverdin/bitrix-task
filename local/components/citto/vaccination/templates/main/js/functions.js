function asyncGeneratorStep(gen, resolve, reject, _next, _throw, key, arg) { try { var info = gen[key](arg); var value = info.value; } catch (error) { reject(error); return; } if (info.done) { resolve(value); } else { Promise.resolve(value).then(_next, _throw); } }

function _asyncToGenerator(fn) { return function () { var self = this, args = arguments; return new Promise(function (resolve, reject) { var gen = fn.apply(self, args); function _next(value) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "next", value); } function _throw(err) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "throw", err); } _next(undefined); }); }; }

Date.prototype.addDays = function (days) {
  var date = new Date(this.valueOf());
  date.setDate(date.getDate() + days);
  return date;
};

var getUrlParameter = function getUrlParameter(sParam) {
  var sPageURL = decodeURIComponent(window.location.search.substring(1)),
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
  value = encodeURIComponent(value); // kvp looks like ['key1=value1', 'key2=value2', ...]

  var kvp = document.location.search.substr(1).split('&');
  var i = 0;

  for (; i < kvp.length; i++) {
    if (kvp[i].startsWith(key + '=')) {
      var pair = kvp[i].split('=');
      pair[1] = value;
      kvp[i] = pair.join('=');
      break;
    }
  }

  if (i >= kvp.length) {
    kvp[kvp.length] = [key, value].join('=');
  } // can return this or...


  var params = kvp.join('&'); // reload page with new params

  document.location.search = params;
}

function deleteParam(key) {
  key = encodeURIComponent(key);
  var kvp = document.location.search.substr(1).split('&');
  var i = 0;

  for (; i < kvp.length; i++) {
    if (kvp[i].startsWith(key + '=')) {
      var pair = kvp[i].split('=');
      console.log('pair', pair);

      if (pair[0] === key) {
        delete kvp[i];
      }

      break;
    }
  }

  var newKVP = [];
  kvp.forEach(function (el) {
    if (el || el === '') {
      newKVP.push(el);
    }
  });
  document.location.search = newKVP.join('&');
}

function request(_x) {
  return _request.apply(this, arguments);
}

function _request() {
  _request = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee(action) {
    var query,
        responseData,
        formData,
        queryControl,
        requestControl,
        _args = arguments;
    return regeneratorRuntime.wrap(function _callee$(_context) {
      while (1) {
        switch (_context.prev = _context.next) {
          case 0:
            query = _args.length > 1 && _args[1] !== undefined ? _args[1] : [];
            formData = new FormData();

            if (query.length) {
              query.forEach(function (el) {
                formData.append(el.name, el.value);
              });
            }

            queryControl = {
              c: 'citto:vaccination',
              action: action,
              mode: 'class'
            }, requestControl = $.ajax({
              url: '/bitrix/services/main/ajax.php?' + $.param(queryControl, true),
              type: 'POST',
              processData: false,
              contentType: false,
              data: formData
            });
            _context.next = 6;
            return requestControl.done(function (_ref) {
              var data = _ref.data;
              responseData = data;
            });

          case 6:
            requestControl.fail(function () {
              console.error('Can not get data');
            });
            return _context.abrupt("return", responseData);

          case 8:
          case "end":
            return _context.stop();
        }
      }
    }, _callee);
  }));
  return _request.apply(this, arguments);
}

function requestRows(_x2) {
  return _requestRows.apply(this, arguments);
}

function _requestRows() {
  _requestRows = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee2(action) {
    var query,
        responseData,
        formData,
        queryControl,
        requestControl,
        _args2 = arguments;
    return regeneratorRuntime.wrap(function _callee2$(_context2) {
      while (1) {
        switch (_context2.prev = _context2.next) {
          case 0:
            query = _args2.length > 1 && _args2[1] !== undefined ? _args2[1] : [];
            formData = new FormData();

            if (query.length) {
              query.forEach(function (el) {
                formData.append('data[]', JSON.stringify(el));
              });
            }

            queryControl = {
              c: 'citto:vaccination',
              action: action,
              mode: 'class'
            }, requestControl = $.ajax({
              url: '/bitrix/services/main/ajax.php?' + $.param(queryControl, true),
              type: 'POST',
              processData: false,
              contentType: false,
              data: formData
            });
            _context2.next = 6;
            return requestControl.done(function (_ref2) {
              var data = _ref2.data;
              responseData = data;
            });

          case 6:
            requestControl.fail(function () {
              console.error('Can not get data');
            });
            return _context2.abrupt("return", responseData);

          case 8:
          case "end":
            return _context2.stop();
        }
      }
    }, _callee2);
  }));
  return _requestRows.apply(this, arguments);
}

function changeYear(input) {
  var el = $('[id ^= "calendar_popup_year_"]'); //найдем div  с календарем

  var link = el.find(".bx-calendar-year-input");
  $(link[0]).attr({
    'onkeyup': 'changeCalendar(this);',
    'data-name': $(input).attr('data-name')
  }); //повесим событие на ввод года

  var links = el.find(".bx-calendar-year-number");

  for (var i = 0; i < links.length; i++) {
    $(links[i]).attr({
      'onclick': 'changeCalendar(this);',
      'data-name': $(input).attr('data-name')
    }); //повесим событие на выбор года
  }
}

function changeMonth(input) {
  var el = $('[id ^= "calendar_popup_month_"]'); //найдем div  с календарем

  var links = el.find(".bx-calendar-month");

  for (var i = 0; i < links.length; i++) {
    $(links[i]).attr({
      'onclick': 'changeCalendar(this);',
      'data-name': $(input).attr('data-name')
    }); //повесим событие на выбор месяца
  }
}

function changeCalendar(input) {
  console.log('input', input);
  setTimeout(function () {
    var el = $('[id ^= "calendar_popup_"]'); //найдем div  с календарем

    var links = el.find(".bx-calendar-cell"); //найдем элементы отображающие дни

    $('.bx-calendar-left-arrow').attr({
      'onclick': 'changeCalendar(this);',
      'data-name': $(input).attr('name')
    }); //вешаем функцию изменения  календаря на кнопку смещения календаря на месяц назад

    $('.bx-calendar-right-arrow').attr({
      'onclick': 'changeCalendar(this);',
      'data-name': $(input).attr('name')
    }); //вешаем функцию изменения  календаря на кнопку смещения календаря на месяц вперед

    $('.bx-calendar-top-month').attr({
      'onclick': 'changeMonth(this);',
      'data-name': $(input).attr('name')
    }); //вешаем функцию изменения  календаря на кнопку выбора месяца

    $('.bx-calendar-top-year').attr({
      'onclick': 'changeYear(this);',
      'data-name': $(input).attr('name')
    }); //вешаем функцию изменения  календаря на кнопку выбора года

    const date = new Date();
    const dateEnd = new Date('2022-12-01')

    var dateNow = new Date();
    var nextDays = 1;

    if (dateNow.getHours() > 14) {
      nextDays = 2;
    }

    date.setDate(date.getDate() + nextDays);

    for (var i = 0; i < links.length; i++) {
      var atrDate = links[i].attributes['data-date'].value;
      var name = $(input).attr('name');

      if (name === undefined) {
        name = $(input).attr('data-name');
      }


      if (name === 'vac_date') {
        if ((date - atrDate > 24 * 60 * 60 * 1000 || dateEnd - atrDate < 24 * 60 * 60 * 1000)) {
          $('[data-date="' + atrDate + '"]').addClass("bx-calendar-date-hidden disabled"); //меняем класс у элемента отображающего день, который меньше по дате чем текущий день
        }

        $('.bx-calendar-weekend').addClass("bx-calendar-date-hidden disabled");
      } else {
        if (date - atrDate > 24 * 60 * 60 * 1000 || dateEnd - atrDate < 24 * 60 * 60 * 1000) {
          $('[data-date="' + atrDate + '"]').removeClass("bx-calendar-date-hidden disabled"); //меняем класс у элемента отображающего день, который меньше по дате чем текущий день
        }
      }
    }
  }, 10);
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
    data: 'columns=' + JSON.stringify(data) + '&filter=' + JSON.stringify(filter)
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
  var result = [];
  var duplicatesIndices = []; // Перебираем каждый элемент в исходном массиве

  arr.forEach(function (current, index) {
    if (duplicatesIndices.includes(index)) return;
    result.push(current); // Сравниваем каждый элемент в массиве после текущего

    for (var comparisonIndex = index + 1; comparisonIndex < arr.length; comparisonIndex++) {
      var comparison = arr[comparisonIndex];
      var currentKeys = Object.keys(current);
      var comparisonKeys = Object.keys(comparison); // Проверяем длину массивов

      if (currentKeys.length !== comparisonKeys.length) continue; // Проверяем значение ключей

      var currentKeysString = currentKeys.sort().join("").toLowerCase();
      var comparisonKeysString = comparisonKeys.sort().join("").toLowerCase();
      if (currentKeysString !== comparisonKeysString) continue; // Проверяем индексы ключей

      var valuesEqual = true;

      for (var i = 0; i < currentKeys.length; i++) {
        var key = currentKeys[i];

        if (current[key] !== comparison[key]) {
          valuesEqual = false;
          break;
        }
      }

      if (valuesEqual) duplicatesIndices.push(comparisonIndex);
    } // Конец цикла

  });
  return result;
}
