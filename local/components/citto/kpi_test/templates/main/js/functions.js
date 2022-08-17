

function changeInput(propName) {
  var $input = $('input[name='+propName+']')
  $input.on('keyup', function(e) {
    console.log(e.target.value)
  })
}

function manInputChange(bool) {
  $('input[name=ATT_MANUAL_INPUT]').attr('disabled', bool).attr('checked', bool)
}
function manInputDisable() {
  $('input[name=ATT_MANUAL_INPUT]').attr('disabled', true).attr('checked', false)
}

function customAddElement() { //TODO добавление элемента внутри таблицы
  
  
  
  var gridInstance = BX.Main.gridManager.getById("kpi_rules_change").instance;
  var actionPanel = gridInstance.actionPanel;
  console.log('gridInstance', gridInstance)
  console.log('actionPanel', actionPanel)
  var templateRow = gridInstance.getTemplateRow();
  templateRow.show();
  templateRow.select();
  templateRow.edit()
  manInputChange(true)
  
  $('div[name=ATT_DATA_SOURCE]').attrchange({
    trackValues: true,
    callback: function (event) {
      
      if (event.attributeName === 'data-value') {
        console.log(event.newValue)
        request('getEnumFieldByID', [{name: 'id', value: event.newValue}]).then(function(enumField) {
          if (enumField.value !== 'Ручной ввод') {
            manInputChange(false)
          } else {
            manInputChange(true)
          }
          
        })
      }
      
    }
  });
  
  $('#row_save').show()
  $('#row_cancel').show()
  $('#row_cancel').on('click', function() {
    gridInstance.reloadTable()
    $('#row_save').hide()
    $(this).hide()
  })
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
  
  
  const queryControl = {c: 'citto:kpi_test', action, mode: 'class'},
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
  
  
  const queryControl = {c: 'citto:kpi_test', action, mode: 'class'},
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




function getKPIs(control) {
  var requestData = [
    {name: 'work_position', value: getUrlParameter('work_position')}
  ]
  
  const indicators = [
    'ВЕС',
    'ФАКТ',
    'ПЛАН'
  ]
  
  request('getKPIs', requestData).then(function(result) {
    console.log('RESULT getKPIs', result)
    
    
    result.forEach(function(kpi) {
  
      indicators.forEach(function(indicator) {
        let id = `${indicator}_${kpi}`
        let title = `${indicator}_${kpi}`
  
        control.addOption({
          id,
          title,
        });
      })
      
    })
  })
}

function parseFormula(arValues) {
  
  var comp = [
    {id: '1', value: "("},
    {id: '2', value: ")"},
    {id: '3', value: "+"},
    {id: '4', value: "-"},
    {id: '5', value: "*"},
    {id: '6', value: "/"},
    
  ]

  return arValues.map(function(el) {
    if (el.indexOf('-') !== -1) {
      var needID = el.split('-')[0]
      let {value} = comp.find((el) => el.id === needID)
      return value
    } else return el
  })
  
}

