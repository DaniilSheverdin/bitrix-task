// BX.showWait = function() {
//   var loader = '<div class="lds-dual-ring"></div>';
//   $('.kpi').addClass('disable')
//   $('.kpi-wrapper').append(loader)
// }
// BX.closeWait = function() {
//   $('.kpi').removeClass('disable')
//   $('.kpi-wrapper').find('.lds-dual-ring').remove();
// }

function customEdit(self) {
  var gridInstance = self.parent

  gridInstance.editSelected()

  function manInputChange(bool) {
    $('input[name=ATT_MANUAL_INPUT]').attr('disabled', bool).attr('checked', bool)
  }


  manInputChange(true)

  $('div[name=ATT_DATA_SOURCE]').attrchange({
    trackValues: true,
    callback: function (event) {

      if (event.attributeName === 'data-value') {

        request('getEnumFieldByID', [{name: 'id', value: event.newValue}]).then(function(enumField) {
          console.log('enumField',enumField)
          if (enumField.value === 'Интегральный показатель') {
            manInputDisable()
          } else {
            if (enumField.value !== 'Ручной ввод') {
              manInputChange(false)
            } else {
              manInputChange(true)
            }
          }


        })
      }

    }
  });
}

function customSave(gridInstance) {

  var _this = gridInstance;

  var value = gridInstance.getRowEditorValue(true);

  var data = {
    'FIELDS': gridInstance.getRows().getEditSelectedValues(true)
  };

  data[gridInstance.getActionKey()] = 'edit';
  gridInstance.reloadTable('POST', data);

  console.log('value', value)
  console.log('P data',data)


}


function customEditDepartment(self) {
  var gridInstance = self.parent

  function isDate(dt){
    var reGoodDate = /^((0|1|2)\d{1})[/.]((0|1)\d{1})[/.]((19|20)\d{2})/;
    return reGoodDate.test(dt);
  }

  $('.main-grid-row-body.main-grid-row-checked').each(function() {
    $(this).children().each(function() {
      if ($(this).hasClass('main-grid-cell-left')) {
        const text = $(this).find('.main-grid-cell-content').text()



        if (isDate(text)) {

          const dateNow = new Date();
          dateNow.setHours(3,0,0,0)
          const formatDate = text.split('.').reverse().join('-')
          const dateText = new Date(formatDate);

          if (dateNow.getTime() < dateText.getTime()) {
            console.log(text)
            const $dateParent = $(this)
            $(this).parent().each(function() {
              $(this).find('.main-grid-cell-left').each(function() {
                $(this).attr('data-editable', false)

              })
            })
            $dateParent.attr('data-editable', true)

          }



        }
      }
    })
  })

  gridInstance.editSelected()

  const $probationInput = $('input[name=ATT_PROBATION_END]');
  const $salaryInput = $('input[name=ATT_SALARY]');

  $salaryInput.attr('step', '0.1')

  $probationInput.on('change, keyup', function(e) {
    console.log(e.target.value)

  })

  $salaryInput.on('keydown', function(e){
    var input = $(this);
    var oldVal = input.val();
    const regex = new RegExp('^\\d*(\\.\\d{0,1})?$', 'g');

    setTimeout(function(){
      var newVal = input.val();
      if(!regex.test(newVal)){
        input.val(oldVal);
      }
    }, 0);
  });

  // $salaryInput.on('change, keyup', function(e) {
  //   const value = e.target.value
  //   console.log('$salaryInput', value)
  //
  //
  // })

  // console.log($('input[name=ATT_PROBATION_END]').val())

  //
  // function manInputChange(bool) {
  //   $('input[name=ATT_MANUAL_INPUT]').attr('disabled', bool).attr('checked', bool)
  // }
  //
  // manInputChange(true)
  //
  // $('div[name=ATT_DATA_SOURCE]').attrchange({
  //   trackValues: true,
  //   callback: function (event) {
  //
  //     if (event.attributeName === 'data-value') {
  //       console.log(event.newValue)
  //       request('getEnumFieldByID', [{name: 'id', value: event.newValue}]).then(function(enumField) {
  //         if (enumField.value !== 'Ручной ввод') {
  //           manInputChange(false)
  //         } else {
  //           manInputChange(true)
  //         }
  //
  //       })
  //     }
  //
  //   }
  // });
}

function customSaveDepartment(gridInstance) {

  var _this = gridInstance;

  var value = gridInstance.getRowEditorValue(true);

  var data = {
    'FIELDS': gridInstance.getRows().getEditSelectedValues(true)
  };

  data[gridInstance.getActionKey()] = 'edit';
  gridInstance.reloadTable('POST', data);

  console.log('value', value)
  console.log('P data',data)


}

const siteDir = BX.message('SITE_DIR');


$(function() {

  var contButtonActionsAllStaff = document.getElementById("actions-all-staff"),
      contButtonAddWorkPosition = document.getElementById("add_work_position"),
      contButtonAddUserToDepartment = document.getElementById("cont_btn_add_user"),
      contButtonWorkPositions = document.getElementById("actions-work-positions"),
      contButtonWorkPositionsSalary = document.getElementById("actions-work-positions-salary"),
      contButtonNotify = document.getElementById("notifies");
      contButtonAccess = document.getElementById("access");
      contButtonSetKP = document.getElementById("set-kp");


  if ($('#select-user').length) {
    var $selectUser = $('#select-user').selectize({
      create: false,
      valueField: 'value',
      sortField: 'text',
      onItemAdd: (value) => {
        if (parseInt(value) > 0) {
          buttonAddUserToDepartment.setState(BX.UI.Button.State.ACTIVE)
        }
        console.log('select value', value)
      },
    });
  }

  if ($('#select-projects').length) {

    var $buttonAddProject = $('.js-add-project')
    var $buttonAddTopManager = $('.js-add-top-manager')
    var val = 0;
    var name = ''

    var $selectProjects = $('#select-projects').selectize({
      create: false,
      valueField: 'value',
      sortField: 'text',
      onItemAdd: (value, data) => {

        if (parseInt(value) > 0) {
          val = value
          name = $(data[0]).text()
          $buttonAddProject.removeClass('ui-btn-disabled')
        }
        console.log('select value', value)
      },
    });

    var $selectAllUsers = $('#select-all-users').selectize({
      create: false,
      valueField: 'value',
      sortField: 'text',
      onItemAdd: (value, data) => {
        if (parseInt(value) > 0) {

          val = value
          name = $(data[0]).text()
          $buttonAddTopManager.removeClass('ui-btn-disabled')
        }
        console.log('select value', value)
      },
    });

    $buttonAddProject.on('click', async function () {

      var requestData = [
        {name: 'project_id', value: val},
        {name: 'project_name', value: name},
      ]
      console.log('request add project', requestData)
      var resultSaveProject = await request('addProjectInSetKP', requestData).then(function(result) {
        return result
      })

      if (resultSaveProject.create_id > 0) {
        var $copyLabel = $('.deleted-label.projects:last-child').clone()

        $copyLabel.removeClass('d-none')
          .find('.name').text(resultSaveProject.project_name).next().attr('data-id', resultSaveProject.create_id)

        $('.js-parent-pp').append($copyLabel)

        val = 0
        name = ''

      } else {
        BX.UI.Notification.Center.notify({
          content: "Данная группа уже добавлена"
        });
      }


      console.log('resultSaveProject', resultSaveProject)


    })


    $buttonAddTopManager.on('click', async function () {

      var requestData = [
        {name: 'user_id', value: val},
        {name: 'user_name', value: name},
      ]
      console.log('request add user', requestData)
      var resultSaveTopManager = await request('addTopManagerInSetKP', requestData).then(function(result) {
        return result
      })

      if (resultSaveTopManager.create_id > 0) {
        var $copyLabel = $('.deleted-label.users:last-child').clone()

        $copyLabel.removeClass('d-none')
          .find('.name').text(resultSaveTopManager.user_name).next().attr('data-id', resultSaveTopManager.user_id)

        $('.js-parent-us').append($copyLabel)

        val = 0
        name = ''

      } else {
        BX.UI.Notification.Center.notify({
          content: "Данный топ-менеджер уже назначен"
        });
      }


      console.log('resultSaveTopManager', resultSaveTopManager)


    })



  }

  $('.js-delete-pp').on('click', async function () {
    var id = $(this).attr('data-id');

    var requestData = [
      {name: 'id', value: id},
    ]
    console.log('request delete project', requestData)
    var resultDelete = await request('deleteInSetKP', requestData).then(function(result) {
      return result
    })

    if (resultDelete.delete_id === id) {
      $(this).closest('.deleted-label').remove()
    } else {
      BX.UI.Notification.Center.notify({
        content: resultDelete.message
      });
    }

  })

  var buttonAddUserToDepartment = new BX.UI.Button({
    id: "button-add-user-to-department",
    text: "Добавить",
    noCaps: false,
    round: false,
    className: "outline-none",
    onclick: async function(btn, event) {

      const userID = $selectUser.val()

      if (parseInt(userID) > 0) {

        const departmentID = $('#cont_btn_add_user').attr('data-department-id')

        var requestData = [
          {name: 'user_id', value: userID},
          {name: 'department_id', value: departmentID},
        ]
        console.log('request add user to department', requestData)
        var resultSave = await request('addUserToDepartment', requestData).then(function(result) {
          return result
        })

        console.log(resultSave)
        if (parseInt(resultSave.id) > 0) {
          window.location.reload()
        }
      }


    },
    size: BX.UI.Button.Size.MEDIUM,
    color: BX.UI.Button.Color.PRIMARY,
    tag: BX.UI.Button.Tag.BUTTON,
    state: BX.UI.Button.State.DISABLED
  });

  buttonAddUserToDepartment.renderTo(contButtonAddUserToDepartment);


  var idPrefix = Date.now();

  function createLetter(idSelected, title) {

    var id = idSelected.split('-')[0]

    if (parseInt(id) <= 6) {
      var idPrefix = Date.now();
      var newIdPrefix = id + '-' + idPrefix

      controlFormula.addOption({
        id: newIdPrefix,
        title: title,
      });
    }
  }

  function removeLetter(id) {
    var clearID = parseInt(id.split('-')[0])
    if (clearID <= 6) {
      controlFormula.removeOption(id);
    }

  }

  var dataComputing = [
    {id: '1-' + idPrefix, title: "("},
    {id: '2-' + idPrefix, title: ")"},
    {id: '3-' + idPrefix, title: "+"},
    {id: '4-' + idPrefix, title: "-"},
    {id: '5-' + idPrefix, title: "*"},
    {id: '6-' + idPrefix, title: "/"},

    // {id: '7-' + idPrefix, title: "("},
    // {id: '8-' + idPrefix, title: ")"},
    // {id: '9-' + idPrefix, title: "+"},

  ]

  if ($('#formula').length) {


    var $selectFormula = $('#formula').selectize({
      maxItems: null,
      valueField: 'id',
      labelField: 'title',
      searchField: 'title',
      options: dataComputing,
      create: false,
      scrollDuration: 3000,
      onItemAdd: (value, $item) => {
        createLetter(value, $item[0].innerHTML)
      },
      onItemRemove: (value) => {
        removeLetter(value)
      }
    });
    var controlFormula = $selectFormula[0].selectize;

    getKPIs(controlFormula)
  }

  if ($('#formula_indicators').length) {


    var $selectFormulaIndicators = $('#formula_indicators').selectize({
      maxItems: null,
      valueField: 'id',
      selectedField: 'selected',
      labelField: 'title',
      searchField: 'title',
      options: dataComputing,
      create: false,
      scrollDuration: 3000,

    });
    var controlFormulaIndicators = $selectFormulaIndicators[0].selectize;

    var indicators = [
      {id: 'W', title: "ВЕС"},
      {id: 'F', title: "ФАКТ"},
      {id: 'P', title: "ПЛАН"},
    ]

    indicators.forEach(function(indicator) {
      controlFormulaIndicators.addOption({
        id: indicator.id,
        title: indicator.title,
      });
    })


  }


  const makeSelect = (nodeID, fields) => {

    const $node = $('#'+nodeID)

    if ($node.length) {

      let selectFormula = $node.selectize({
        maxItems: null,
        valueField: 'id',
        labelField: 'title',
        options: dataComputing,
        create: false,
        scrollDuration: 3000,
      });

      let controlFormula = selectFormula[0].selectize;

      fields.forEach(function(el) {
        controlFormula.addOption({
          id: el.id,
          title: el.title,
        });
      })


      return [selectFormula, controlFormula]
    } else return []
  }



  var critical = [
    {id: 'B', title: "БАЗОВЫЙ_KPI"},
    {id: 'N', title: "КРИТ_КОЭФ_N"},
  ]
  const [selectCritical, controlCritical] = makeSelect('formula_critical', critical)


  var progress = [
    {id: 'K', title: "КРИТ_KPI"},
    {id: 'R', title: "РАЗВ_КОЭФ"},
  ]
  const [selectProgress, controlProgress] = makeSelect('formula_progress', progress)










  if ($('#select-department').length) {
    var $selectDepartment = $('#select-department').selectize({
      create: false,
      valueField: 'value',
      sortField: 'text',
      onItemAdd: (value) => {
        insertParam('department', value)

      },
    });
  }

  if ($('#select-show-kpi-gov').length) {
    var $selectShowKPIGovernment = $('#select-show-kpi-gov').selectize({
      create: false,
      valueField: 'value',
      sortField: 'text',
      onItemAdd: (value) => {
        insertParam('government', value)
        if (parseInt(getUrlParameter('department')) > 0) {
          deleteParam('department');
        }
      },
    });
  }

  if ($('#select-date').length) {
    var $selectMonth = $('#select-date').selectize({
      create: false,
      valueField: 'value',
      sortField: 'text',
      onItemAdd: (value) => {
        insertParam('date', value)
      },
    });
  }

  if ($('select[name=UF_KPI_ACCESS_TO_DEPARTMENT]').length) {
    var $selectAccess = $('select[name=UF_KPI_ACCESS_TO_DEPARTMENT]').selectize({
      create: false,
      valueField: 'value',
      sortField: 'text',
      onItemAdd: (value) => {

      },
    });
  }

  if ($('select[name=UF_KPI_ASSISTANT_TO_DEPARTMENT]').length) {
    var $selectAccess = $('select[name=UF_KPI_ASSISTANT_TO_DEPARTMENT]').selectize({
      create: false,
      valueField: 'value',
      sortField: 'text',
      onItemAdd: (value) => {

      },
    });
  }

  if ($('#change_department_access').length) {
    var $selectDepartmentAccess = $('#change_department_access').selectize({
      create: false,
      valueField: 'value',
      sortField: 'text',
      onItemAdd: (value) => {
        insertParam('department', value)
      },
    });
  }




  if (parseInt(getUrlParameter('work_position')) > 0) {
    var requestData = [
      {name: 'work_position', value: getUrlParameter('work_position')}
    ]
    request('getFormulas', requestData).then(function(result) {
      console.log('getFormulas',result)
      if (result?.kpi) { // && result?.indicators
        // var idsInsertIndicators = []
        var idsInsertKPI = []

        // result.indicators.split(',').forEach(function(value) {
        //
        //   var computed = dataComputing.find(el => el.title === value)
        //   if (computed) idsInsertIndicators.push(computed.id)
        //   else idsInsertIndicators.push(value)
        //
        // })

        result.kpi.split(',').forEach(function(value) {

          var idPrefix = Date.now();
          var random = Math.floor(Math.random() * 100)

          var computed = dataComputing.find(el => el.title === value)
          if (computed) {
            // console.log('computed', computed.id)
            idsInsertKPI.push(computed.id + idPrefix + random)


            controlFormula.addOption({
              id: computed.id + idPrefix + random,
              title: computed.title,
            });

          }
          else idsInsertKPI.push(value)

        })

        // console.log('idsInsertKPI', idsInsertKPI)

        // controlFormulaIndicators.setValue(idsInsertIndicators, true) //Возможно не пригодится
        console.log('idsInsertKPI', idsInsertKPI)
        setTimeout(function() {
          controlFormula.setValue(idsInsertKPI, true)
        }, 100)


      }

    })
  }


  const fetchFormulas = (action, controls, names) => {

    request(action).then(function(result) {
      console.log('getFormulas',result)

      controls.forEach((control, i) => {
        const ids = []

        if (result[names[i]]) {
          result[names[i]].split(',').forEach(function(value) {

            const computed = dataComputing.find(el => el.title === value)
            if (computed) ids.push(computed.id)
            else ids.push(value)

          })

          control.setValue(ids, true)
        } else {
          console.info('Нет данных')
        }
      })

    })

  }



  if ($('#formula_critical').length) {

    fetchFormulas(
      'getFormulasExt',
      [controlCritical, controlProgress],
      ['critical', 'progress']
    )

  }






  $('.kpi_menu .item.disabled').on('click', function(e) {
    e.preventDefault()
  })


  $('body').on('change, keypress' , 'form input, textarea', function () {

    if ($(this).parent().hasClass('ui-ctl-danger')) {
      $(this).parent().removeClass('ui-ctl-danger')
    }

  })

  var instanceKPIAdd
  var instanceCriticalAdd
  var instanceWPAdd

  var buttonAddKPI = new BX.UI.Button({
    id: "button-add-kpi",
    text: "Добавить показатель",
    noCaps: true,
    round: false,
    className: "outline-none",
    onclick: function(btn, event) {

      manInputChange(true)

      $('select[name=ATT_DATA_SOURCE]').on('change', function(e) {

        request('getEnumFieldByID', [{name: 'id', value: e.target.value}]).then(function(enumField) {
          console.log('enumField.value', enumField.value)
          if (enumField.value === 'Интегральный показатель') {
            manInputDisable()
          } else {
            if (enumField.value !== 'Ручной ввод') {
              manInputChange(false)
            } else {
              manInputChange(true)
            }
          }


        })
      })

      var requestData = [
        {name: 'work_position', value: getUrlParameter('work_position')}
      ]

      request('getNextLabel', requestData).then(function(nextLabel) {
        $('#next_kpi').html(nextLabel)
        $('#input_label').val(nextLabel)
      })

      instanceKPIAdd = $.fancybox.open({
        src  : '#add-kpi-content',
        type : 'inline',
        opts : {
          clickSlide : false,
          touch: false,


        }
      });
    },
    size: BX.UI.Button.Size.MEDIUM,
    color: BX.UI.Button.Color.PRIMARY,
    tag: BX.UI.Button.Tag.BUTTON,
    state: BX.UI.Button.State
  });
  var buttonSaveRulesChange = new BX.UI.Button({
    id: "button-save-change-rules",
    text: "Подтвердить",
    noCaps: false,
    round: false,
    className: "outline-none",
    onclick: async function(btn, event) {
      var validationError = false;
      // var salary = $('#salary').val()
      //
      //
      // console.log(salary)

      // var formulaIndicatorsArray = parseFormula(controlFormulaIndicators.items)
      var formulaKPIArray = parseFormula(controlFormula.items)

      // console.log(parseFormula(controlFormulaIndicators.items))
      // console.log(parseFormula(controlFormula.items))

      var requestData = [
        {name: 'work_position', value: getUrlParameter('work_position')}
      ]

      var indicatorsSum = await request('checkIndicatorsWeightSum', requestData).then(function(indicatorsSum) {
        return indicatorsSum
      })

      if (indicatorsSum === 0) {
        validationError = true
        BX.UI.Notification.Center.notify({
          content: 'Добавьте показатели'
        });
      } else if (indicatorsSum !== 100) {
        validationError = true
        BX.UI.Notification.Center.notify({
          content: 'Сумма веса показателей должна быть равна 100%. Текущее значение: ' + indicatorsSum + '%'
        });
      }

      console.log('indicatorsSum', indicatorsSum)

      // if (isNaN(parseInt(salary))) {
      //   validationError = true
      //   BX.UI.Notification.Center.notify({
      //     content: 'Введите текущий оклад'
      //   });
      // }

      // if (!formulaIndicatorsArray.length || !formulaKPIArray.length) {
      if (!formulaKPIArray.length) {
        validationError = true
        BX.UI.Notification.Center.notify({
          content: 'Заполните формулы'
        });
      }

      const nameWP = $('input[name=WP_NAME_CHANGE]').val()

      if (!nameWP.length) {
        validationError = true
      }


      if (!validationError) {

        var requestDataSave = [
          {name: 'work_position', value: getUrlParameter('work_position')},
          {name: 'wp_name', value: nameWP},
          {name: 'department_id', value: getUrlParameter('department')},
          // {name: 'salary', value: salary},
          // {name: 'formula_indicators', value: formulaIndicatorsArray.join()},
          {name: 'formula_kpi', value: formulaKPIArray.join()},
        ]
        console.log('requestDataSave',requestDataSave)
        var resultSave = await request('saveWorkPositionValues', requestDataSave).then(function(result) {
          return result
        })
        if (resultSave) {
          $('.actions-messages').animate({opacity: 1}, 300)
          setTimeout(function() {
            $('.actions-messages').animate({opacity: 0}, 500)

          }, 3000)
        }
      }






    },
    size: BX.UI.Button.Size.MEDIUM,
    color: BX.UI.Button.Color.SUCCESS,
    tag: BX.UI.Button.Tag.BUTTON,
    state: BX.UI.Button.State
  });
  var buttonCancelRulesChange = new BX.UI.Button({
    id: "button-cancel-change-rules",
    text: "Отменить",
    noCaps: false,
    round: false,
    className: "outline-none",
    onclick: function(btn, event) {

      var requestData = [
        {name: 'work_position', value: getUrlParameter('work_position')}
      ]
      request('returnSavedKPI', requestData).then(function(response) {
        console.log(response)
      })
      window.location = `${siteDir}kpi/computed_rules?department=${getUrlParameter('department')}`
    },
    size: BX.UI.Button.Size.MEDIUM,
    color: BX.UI.Button.Color.LIGHT_BORDER,
    tag: BX.UI.Button.Tag.BUTTON,
    state: BX.UI.Button.State
  });

  var buttonKPISave = new BX.UI.Button({
    id: "button-kpi-save",
    text: "Сохранить",
    noCaps: false,
    round: false,
    className: "outline-none",
    props: {
      'data-fancybox': 'name'
    },
    onclick: function(btn, event) {
      event.preventDefault()

      var $form = $('#form-kpi-add')

      var values = $form.serializeArray()

      console.log(values)

      var emptyValues = false
      var $input
      values.forEach(function(input) {
        if (!input.value.length) {
          $input = $form.find('input[name='+input.name+']')
          console.log($input)
          $input.parent().addClass('ui-ctl-danger')
          emptyValues = true
        }
        // else if (input.name === 'ATT_TARGET_VALUE' && parseInt(input.value) > 100) {
        //   $input = $form.find('input[name='+input.name+']')
        //   $input.parent().addClass('ui-ctl-danger')
        //   emptyValues = true
        // }
      })



      if (!emptyValues) {

        request('addKPI', values).then(function(response) {
          if (response) {
            instanceKPIAdd.close()
            BX.Main.gridManager.reload('kpi_rules_change')
            getKPIs(controlFormula)
          }
        })
      }


    },
    size: BX.UI.Button.Size.MEDIUM,
    color: BX.UI.Button.Color.SUCCESS,
    tag: BX.UI.Button.Tag.BUTTON,
    state: BX.UI.Button.State
  });

  var buttonKPICancel = new BX.UI.Button({
    id: "button-kpi-cancel",
    text: "Отменить",
    noCaps: false,
    round: false,
    className: "outline-none",
    onclick: function(btn, event) {
      event.preventDefault()
      instanceKPIAdd.close()
    },
    size: BX.UI.Button.Size.MEDIUM,
    color: BX.UI.Button.Color.LIGHT_BORDER,
    tag: BX.UI.Button.Tag.BUTTON,
    state: BX.UI.Button.State
  });


  var buttonAddCritical = new BX.UI.Button({
    id: "button-add-critical",
    text: "Добавить факт",
    noCaps: true,
    round: false,
    className: "outline-none",
    onclick: function(btn, event) {

      instanceCriticalAdd = $.fancybox.open({
        src  : '#add-critical-content',
        type : 'inline',
        opts : {
          clickSlide : false,
          touch: false,
        }
      });
    },
    size: BX.UI.Button.Size.MEDIUM,
    color: BX.UI.Button.Color.PRIMARY,
    tag: BX.UI.Button.Tag.BUTTON,
    state: BX.UI.Button.State
  });

  var buttonCriticalSave = new BX.UI.Button({
    id: "button-critical-save",
    text: "Сохранить",
    noCaps: false,
    round: false,
    className: "outline-none",
    props: {
      'data-fancybox': 'name'
    },
    onclick: function(btn, event) {
      event.preventDefault()

      var $form = $('#form-critical-add')

      var values = $form.serializeArray()

      console.log(values)

      var emptyValues = false
      var $input
      var count = 1
      $('.js-count').each(function() {
        count++
      })

      values.forEach(function(input) {
        console.log(input)

        if (!input.value.length) {
          $input = $form.find('input[name='+input.name+']')
          console.log($input)
          $input.parent().addClass('ui-ctl-danger')
          emptyValues = true
        }
      })
      values.push({name: 'ATT_LABEL', value: count})

      if (!emptyValues) {

        console.log('request', values)

        request('addRuleExtra', values).then(function(response) {
          console.log('response',response)
          if (response.NAME) {

            var $table = $('.critical_table')


            var template = `<div class="row align-items-center mt-2">
                              <div class="col-1 js-count">${count}</div>
                              <div class="col-6">${response.NAME}</div>
                              <div class="col-2"><input type="number" value="${response.VALUE}"></div>
                            </div>`

            instanceCriticalAdd.close()
            $form[0].reset()
            $('.actions-messages').animate({opacity: 1}, 300)
            setTimeout(function() {
              $('.actions-messages').animate({opacity: 0}, 500)

            }, 3000)
            $table.append(template)
            // getKPIs(controlFormula)
          }
        })
      }


    },
    size: BX.UI.Button.Size.MEDIUM,
    color: BX.UI.Button.Color.SUCCESS,
    tag: BX.UI.Button.Tag.BUTTON,
    state: BX.UI.Button.State
  });

  var buttonCriticalCancel = new BX.UI.Button({
    id: "button-critical-cancel",
    text: "Отменить",
    noCaps: false,
    round: false,
    className: "outline-none",
    onclick: function(btn, event) {
      event.preventDefault()
      instanceCriticalAdd.close()
    },
    size: BX.UI.Button.Size.MEDIUM,
    color: BX.UI.Button.Color.LIGHT_BORDER,
    tag: BX.UI.Button.Tag.BUTTON,
    state: BX.UI.Button.State
  });


  //actions extra

  var buttonSaveRulesExtra = new BX.UI.Button({
    id: "button-save-extra-rules",
    text: "Подтвердить",
    noCaps: false,
    round: false,
    className: "outline-none",
    onclick: async function(btn, event) {
      var validationErrorFormula = false;
      var validationErrorCritical = false;
      var validationErrorProgress = false;
      var progressValue = 0;
      const allData = []

      console.log('buttonSaveRulesExtra')


      $('.js-critical-value').each(function(e) {


        const id = $(this).attr('data-id')
        const value = $(this).val()

        allData.push({id, value})




         if (!$(this).val()) validationErrorCritical = true
      })
      $('.js-progress-value').each(function(e) {
        if (!$(this).val()) validationErrorProgress = true
        else progressValue = parseFloat($(this).val())
      })

      if (validationErrorCritical) {
        BX.UI.Notification.Center.notify({
          content: 'Заполните все значения критического KPI'
        });
      }

      if (validationErrorProgress) {
        BX.UI.Notification.Center.notify({
          content: 'Заполните значение коэффициента'
        });
      }


      var formulaCriticalArray = parseFormula(controlCritical.items)
      var formulaProgressArray = parseFormula(controlProgress.items)

      console.log('formulaCriticalArray',formulaCriticalArray)
      console.log('formulaProgressArray', formulaProgressArray)


      if (!formulaCriticalArray.length || !formulaProgressArray.length) {
        validationErrorFormula = true
        BX.UI.Notification.Center.notify({
          content: 'Заполните формулы'
        });
      }

      if (!validationErrorFormula && !validationErrorCritical && !validationErrorProgress) {

        let rowData = {}

        rowData.formula_critical = formulaCriticalArray.join()
        rowData.progress_value = progressValue
        rowData.formula_progress = formulaProgressArray.join()

        allData.push(rowData)



        console.log('allData', allData)

        requestRows('saveRulesExtra', allData).then(function(response) {
          console.log('response', response)
          $('.actions-messages').animate({opacity: 1}, 300)
          setTimeout(function() {
            $('.actions-messages').animate({opacity: 0}, 500)

          }, 3000)

        })





      }






    },
    size: BX.UI.Button.Size.MEDIUM,
    color: BX.UI.Button.Color.SUCCESS,
    tag: BX.UI.Button.Tag.BUTTON,
    state: BX.UI.Button.State
  });
  var buttonCancelRulesExtra = new BX.UI.Button({
    id: "button-cancel-change-rules",
    text: "Отменить",
    noCaps: false,
    round: false,
    className: "outline-none",
    onclick: function(btn, event) {


      request('returnSavedKPIExt').then(function(response) {
        console.log(response)
      })
      window.location = `${siteDir}kpi/computed_rules`
    },
    size: BX.UI.Button.Size.MEDIUM,
    color: BX.UI.Button.Color.LIGHT_BORDER,
    tag: BX.UI.Button.Tag.BUTTON,
    state: BX.UI.Button.State
  });



  var contButtonAddRulesChange = document.getElementById("add-button-rc");
  buttonAddKPI.renderTo(contButtonAddRulesChange);
  var contButtonActionsRulesChange = document.getElementById("actions-button-rc");
  buttonSaveRulesChange.renderTo(contButtonActionsRulesChange);
  buttonCancelRulesChange.renderTo(contButtonActionsRulesChange);
  var contButtonActionsKPI = document.getElementById("kpi_actions");
  buttonKPISave.renderTo(contButtonActionsKPI)
  buttonKPICancel.renderTo(contButtonActionsKPI)
  var contButtonAddCritical = document.getElementById("critical_add");
  buttonAddCritical.renderTo(contButtonAddCritical)
  var contButtonActionsCritical = document.getElementById("critical_actions");
  buttonCriticalSave.renderTo(contButtonActionsCritical)
  buttonCriticalCancel.renderTo(contButtonActionsCritical)

  var contButtonActionsRulesExtra = document.getElementById("actions-button-re");

  buttonSaveRulesExtra.renderTo(contButtonActionsRulesExtra);
  buttonCancelRulesExtra.renderTo(contButtonActionsRulesExtra);


  let initialDataValuesStaff = []
  let stateEditRows = false

  $('.js-staff-form').on('keyup', 'input', function(e) {
    if (e.target.value !== '') {
      $(this).removeClass('ui-ctl-danger')
    }
  })
  $('.js-staff-form').on('change', '.select-critical', function(e) {
    $(this).parent().parent().attr('data-value', e.target.value)
  })




  var buttonCancelKPIStaff = new BX.UI.Button({
    id: "button-cancel-kpi-staff",
    text: "Отменить",
    noCaps: false,
    round: false,
    className: "outline-none",
    onclick: async function(btn, event) {

      const actionsID = btn.getDataSet().id
      const optionsCritical = await request('getCriticalKPIs').then((res) => {
        return res
      })

      $(`form[data-actions-id=${actionsID}]`).each(function() {

        const userID = $(this).attr('id')
        const userData = initialDataValuesStaff.find(el => el.id === userID)

        console.log(userData)

        if (userData) {

          $(this).find('div[data-editable=Y]').each(function(index) {
            if ($(this).attr('data-name') === 'ATT_KPI_CRITICAL') {
              let html = ''
              if (parseInt(userData.data[index].value) > 0) {
                html = `<label class="ui-ctl ui-ctl-checkbox">
                    <input disabled name="ATT_KPI_CRITICAL" type="checkbox" class="ui-ctl-element" checked>
                    <div class="ui-ctl-label-text">Активирован ${userData.data[index].value}/${optionsCritical.length}</div>
                  </label>`
                $(this).attr('data-value', userData.data[index].value)
              }
              $(this).html(html)
            } else {
              $(this).html(userData.data[index].value)
            }
          })

        }

      })


      $('input[data-select=user]').each(function() {
        if ($(this).prop('checked')) {
          $(this).trigger('click')
        }


      })


      buttonEditKPIStaff.setText('Редактировать')
      $('.actions-row').html('')
      initialDataValuesStaff = []

    },
    size: BX.UI.Button.Size.MEDIUM,
    color: BX.UI.Button.Color.LIGHT_BORDER,
    tag: BX.UI.Button.Tag.BUTTON,
    state: BX.UI.Button.State
  });



  var buttonEditKPIStaff = new BX.UI.Button({
    id: "button-edit-kpi-staff",
    text: "Редактировать",
    noCaps: false,
    round: false,
    className: "outline-none js-edit",
    onclick: async function(btn, event) {



      if (btn.getText() === 'Редактировать') {

        stateEditRows = true

        const optionsCritical = await request('getCriticalKPIs').then((res) => {
          return res
        })

        const $criticalSelect = (selected) => {

          const optionsTemplate = optionsCritical.map(el => {
            return `<option ${selected === el.value ? 'selected' : ''} value="${el.value}">${el.label}</option>`
          })

          return `<select name="ATT_KPI_EXT" class="select-critical">
                ${optionsTemplate}
               </select>`
        }


        const actionsID = btn.getDataSet().id
        const contButtonActionsStaff = document.getElementById(actionsID);

        buttonCancelKPIStaff.setProps({'data-id': actionsID})
        buttonCancelKPIStaff.renderTo(contButtonActionsStaff);

        $('.selected-row').each(function() {

          const userID = $(this).attr('id')

          let user = {id: userID, data: []}


          $(this).find('div[data-editable=Y]').each(function() {

            let init = {}
            init.value = $(this).text().trim()
            if ($(this).attr('data-name') === 'ATT_KPI_CRITICAL') init.value = $(this).attr('data-value')


            init.name = $(this).attr('data-name')
            init.id = $(this).attr('data-id')
            const type = $(this).attr('data-type')
            let html = `<div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
                    <input type="number" name="${init.name}" data-user-id="${userID}" value="${init.value}">
                  </div>`
            if (type === 'checkbox') {
              const dataValue = $(this).attr('data-value')
              let checked = ''
              if (dataValue === 'Y' || parseInt(dataValue) > 0) checked = 'checked'
              html = `<label class="ui-ctl ui-ctl-checkbox">
                    <input name="${init.name}" data-user-id="${userID}" type="checkbox" class="ui-ctl-element" ${checked}>
                    <div class="ui-ctl-label-text">${checked === 'checked' ? 'Отменить' : 'Активировать'}</div>
                    ${parseInt(dataValue) > 0 ? $criticalSelect(dataValue) : ''}
                </label>`
            } else if (type === 'array') {
              html = `<div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
                  <input type="number" data-user-id="${userID}" name="${init.name}_${init.id}" value="${init.value}">
                </div>`
            } else if (type === 'text') {
              html = `<div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
                  <input type="text" data-user-id="${userID}" name="${init.name}" value="${init.value}">
                </div>`
            }

            user.data.push(init)
            $(this).html(html)

          })

          initialDataValuesStaff.push(user)



          $('input[type=checkbox]').on('change', function() {
            const $label = $(this).next()
            if ($(this).prop('checked')) {
              $label.html('Отменить')

              if ($(this).attr('name') === 'ATT_KPI_CRITICAL') {
                $(this).parent().attr('data-value', '1')
                $(this).append($criticalSelect('1'))
              }

            }
            else {
              $label.html('Активировать')
              if ($(this).attr('name') === 'ATT_KPI_CRITICAL') {
                $(this).find('.select-critical').remove()
              }
            }
          })

        })

        $('input[type=checkbox]').on('change', function() {
          const $label = $(this).next()
          if ($(this).prop('checked')) {
            $label.html('Отменить')

            if ($(this).attr('name') === 'ATT_KPI_CRITICAL') {
              $(this).parent().parent().attr('data-value', '1')
              $(this).parent().append($criticalSelect('1'))
            }

          }
          else {
            $label.html('Активировать')
            if ($(this).attr('name') === 'ATT_KPI_CRITICAL') {
              $(this).parent().find('.select-critical').remove()
            }
          }
        })


        btn.setText('Сохранить')
        console.log(initialDataValuesStaff)
      } else if (btn.getText() === 'Сохранить') {


        stateEditRows = false


        // СОХРАНЕНИЕ ДОЛЖНОСТЕЙ-----------------------------------------------------------------//////////////////////////////////////////

        let errorEmpty = false

        const optionsCritical = await request('getCriticalKPIs').then((res) => {
          return res
        })

        // const actionsID = btn.getDataSet().id


        $('.js-staff-form.selected-row').each(function() {

          const newValues = $(this).serializeArray()
          const rowID = $(this).attr('id')

          console.log('newValues', newValues)

          let issetKPI = false

          newValues.forEach(function(el) {
            if (el.name === 'ATT_KPI_CRITICAL' || el.name === 'ATT_KPI_PROGRESS') {
              issetKPI = true
            }

          })


          newValues.forEach((el) => {
            if (el.name === 'ATT_COMMENT' && el.value === '') {
              if (issetKPI) {
                errorEmpty = true
                $(`input[name=${el.name}][data-user-id=${rowID}]`).addClass('ui-ctl-danger')
              } else {
                $(`input[name=${el.name}][data-user-id=${rowID}]`).removeClass('ui-ctl-danger')
              }

            } else {
              if (el.value === '') {
                errorEmpty = true
                $(`input[name=${el.name}][data-user-id=${rowID}]`).addClass('ui-ctl-danger')
              }
            }

          })


        })


        $('.js-staff-form.selected-row').each(function() {

          if (!errorEmpty) {

            const newValues = $(this).serializeArray()

            console.log('newValuesS', newValues)

            const issetCritical = newValues.find(
              el => el.name === 'ATT_KPI_CRITICAL')
            const issetProgress = newValues.find(
              el => el.name === 'ATT_KPI_PROGRESS')
            const commentText = newValues.find(el => {
              if (el.name === 'ATT_COMMENT') {
                return el.value
              }
            })

            console.log('commentText', commentText)

            $(this).find('div[data-editable=Y]').each(function(index) {

              let html = newValues[index]?.value

              if (!newValues[index]) html = ''

              if ($(this).attr('data-name') === 'ATT_KPI_CRITICAL') {
                if (!issetCritical) {
                  $(this).attr('data-value', 'N')
                  html = ''
                } else {
                  html = `<label class="ui-ctl ui-ctl-checkbox">
                  <input disabled name="ATT_KPI_CRITICAL" type="checkbox" class="ui-ctl-element" checked>
                  <div class="ui-ctl-label-text">Активирован ${$(this).
                    attr('data-value')}/${optionsCritical.length}</div>
                </label>`
                }
              }

              if ($(this).attr('data-name') === 'ATT_KPI_PROGRESS') {
                if (!issetProgress) {
                  $(this).attr('data-value', 'N')
                  html = ''
                } else {
                  html = `<label class="ui-ctl ui-ctl-checkbox">
                  <input disabled name="ATT_KPI_PROGRESS" type="checkbox" class="ui-ctl-element" checked>
                  <div class="ui-ctl-label-text">Активирован</div>
                </label>`
                  $(this).attr('data-value', 'Y')
                }
              }

              if (commentText) {
                if ($(this).attr('data-name') === 'ATT_COMMENT') {
                  $(this).attr('data-value', commentText.value)
                  html = commentText.value

                }
              }




              $(this).html(html)
            })





          }

        })

        if (!errorEmpty) {
          $('input[data-select=all]').each(function() {
            if ($(this).prop('checked')) {
              $(this).trigger('click')
            }

          })

          $('input[data-select=user]').each(function() {
            if ($(this).prop('checked')) {
              $(this).trigger('click')
            }

          })



          btn.setText('Редактировать')
          initialDataValuesStaff = []


          var requestData = [
            {name: 'wpid', value: btn.button.dataset.id}
          ]
          request('saveDateLastChanges', requestData).then(function(response) {
            console.log(response)
          })

        } else {
          BX.UI.Notification.Center.notify({
            content: 'Заполните все поля'
          });
        }

      }

    },
    size: BX.UI.Button.Size.MEDIUM,
    color: BX.UI.Button.Color.SUCCESS,
    tag: BX.UI.Button.Tag.BUTTON,
    state: BX.UI.Button.State
  });




  $('.js-switch-icon').on('click', function() {
    const $icon =  $(this).next().find('img')

    let iconSource = $icon.attr('src')
    if ($(this).hasClass('collapsed')) {
      iconSource = iconSource.replace('angle-down-solid', 'angle-up-solid')
      $icon.attr('src', iconSource)
    } else {
      iconSource = iconSource.replace('angle-up-solid', 'angle-down-solid')
      $icon.attr('src', iconSource)
    }
  })


  // SELECT ALL CHECKBOXES
  $('input[data-select=all]').on('change', function() {


    const dataID = $(this).attr('data-select-id');


    if ($(this).prop('checked')) {
      console.log('asd111')
      $(`input[data-select=user][data-select-id=${dataID}]`).each(function(index) {

        if (!$(this).prop('checked')) {
          $(this).trigger('click')
        }


      })
    } else {

      $(`input[data-select=user][data-select-id=${dataID}]`).each(function(index) {

        if ($(this).prop('checked')) {
          $(this).trigger('click')
        }


      })

    }

  })


  //MAIN
  $(`input[data-select=user]`).on('change', function() {

    const actionsID = $(this).parent().attr('data-actions-id')
    const contButtonActionsStaff = document.getElementById(actionsID);
    let activeCheckbox = false

    if ($(this).prop('checked')) {
      $(this).parent().parent().addClass('selected-row')
      buttonEditKPIStaff.renderTo(contButtonActionsStaff);
      buttonEditKPIStaff.setProps({'data-id': actionsID})
      //TODO проверить есть ли активные чекбоксы

      $('input[data-select=user]').each(function() {
        if ($(this).attr('data-select-id') !== actionsID) {
          $(this).attr('disabled', true)
        }
      })
      $('input[data-select=all]').each(function() {
        if ($(this).attr('data-select-id') !== actionsID) {
          $(this).attr('disabled', true)
        }
      })

    } else {
      $(this).parent().parent().parent().find('input[data-select=user]').each(function() {
        if ($(this).prop('checked')) {
          activeCheckbox = true
        }
      })
      if (!activeCheckbox) {
        $('.actions-row').html('')
        $('input[data-select=user]').each(function() {
          if ($(this).attr('data-select-id') !== actionsID) {
            $(this).attr('disabled', false)
          }
        })
        $('input[data-select=all]').each(function() {
          if ($(this).attr('data-select-id') !== actionsID) {
            $(this).attr('disabled', false)
          }
        })
      } else {
        $(`input[data-select=all][data-select-id=${actionsID}]`).each(function() {

          $(this).attr('checked', false)

        })
      }
      $(this).parent().parent().removeClass('selected-row')

    }

  })



  var buttonSaveKPIAllStaff = new BX.UI.Button({
    id: "button-save-kpi-all-staff",
    text: "Сохранить",
    noCaps: false,
    round: false,
    className: "outline-none",
    onclick: async function(btn, event) {

      const allData = []

      $('.js-staff-form').each(function() {

        const userID = parseInt($(this).attr('id'))

        if (userID > 0) {

          let rowData = {id: userID}
          const valueKPI = []
          let valueCritical = 0
          let valueProgress = ''
          let valueComment = ''

          $(this).find('div[data-editable]').each(function() {

            let id, value
            switch ($(this).attr('data-name')) {
              case 'ATT_VALUE_KPI':
                id = parseInt($(this).attr('data-id'))
                value = $(this).text()
                valueKPI.push({id, value})
                break

              case 'ATT_KPI_CRITICAL':
                value = parseInt($(this).attr('data-value'))
                if (!isNaN(value)) {
                  valueCritical = value
                }
                break

              case 'ATT_KPI_PROGRESS':
                value = $(this).attr('data-value')
                valueProgress = value
                break

              case 'ATT_COMMENT':
                value = $(this).text()
                valueComment = value
                break
            }
          })

          rowData.ATT_VALUE_KPI = valueKPI
          rowData.ATT_KPI_CRITICAL = valueCritical
          rowData.ATT_KPI_PROGRESS = valueProgress
          rowData.ATT_COMMENT = valueComment
          allData.push(rowData)

        }

      })

      console.log('allData', allData)

      requestRows('updateUsersKPI', allData).then(function(response) {
        console.log(response)
        let search = ''
        if (window.location.search.length) search = window.location.search
        $('.actions-messages').animate({opacity: 1}, 300)
        window.location = `${siteDir}kpi/insert_data_dep${search}`
        setTimeout(function() {
          $('.actions-messages').animate({opacity: 0}, 500)

        }, 3000)

      })

    },
    size: BX.UI.Button.Size.MEDIUM,
    color: BX.UI.Button.Color.SUCCESS,
    tag: BX.UI.Button.Tag.BUTTON,
    state: BX.UI.Button.State
  });
  var buttonCancelKPIAllStaff = new BX.UI.Button({
    id: "button-cancel-kpi-all-staff",
    text: "Отменить",
    noCaps: false,
    round: false,
    className: "outline-none",
    onclick: function(btn, event) {
      window.location = `${siteDir}kpi`
    },
    size: BX.UI.Button.Size.MEDIUM,
    color: BX.UI.Button.Color.LIGHT_BORDER,
    tag: BX.UI.Button.Tag.BUTTON,
    state: BX.UI.Button.State
  });



  var sendData = new BX.UI.Button({
    id: "button-send-data",
    text: "Отправить в отдел кадров",
    noCaps: false,
    round: false,
    className: "outline-none ml-5",
    onclick: async function(btn, event) {

      btn.setState(BX.UI.Button.State.WAITING)
      // const debugMode = true;
      const debugMode = false;

      const debug = [
        {name: 'debug', value: debugMode},
      ]

      console.log('Отправить в отдел кадров')
      const sendDataResult = await request('dataToHRD', debug).then((res) => {
        return res
      })
      btn.setState(BX.UI.Button.State.DISABLED)
      console.log(sendDataResult);
      if (sendDataResult['excel'] && !debugMode) {
        document.location = sendDataResult['excel'];
      }
      if (sendDataResult['docx'] && !debugMode) {
        setTimeout(() => {
          document.location = sendDataResult['docx'];
        }, 500)

      }


    },
    size: BX.UI.Button.Size.LARGE,
    color: BX.UI.Button.Color.PRIMARY,
    tag: BX.UI.Button.Tag.BUTTON,
    state: BX.UI.Button.State
  });

  buttonSaveKPIAllStaff.renderTo(contButtonActionsAllStaff);
  buttonCancelKPIAllStaff.renderTo(contButtonActionsAllStaff);

  if ($('.js-send-data-gov').length) {
    sendData.renderTo(contButtonActionsAllStaff);

  }

  let addWPInputValue = ''



  var addWorkPosition = new BX.UI.Button({
    id: "button-add-work-position",
    text: "Добавить",
    noCaps: false,
    round: false,
    className: "outline-none",
    onclick: async function(btn, event) {


      if (addWPInputValue.length) {

        var requestData = [
          {name: 'department', value: getUrlParameter('department')},
          {name: 'new_work_position', value: addWPInputValue},
        ]

        console.log('button-add-work-position')
        const [name, department, id] = await request('createWorkPosition', requestData).then((res) => {
          return res
        })
        if (id) {
          $('input[name=WP_NAME]').val('')
          btn.setState(BX.UI.Button.State.DISABLED)
          addWPInputValue = ''
          document.location.reload()
        } else {
          BX.UI.Notification.Center.notify({
            content: 'Функциональная единица стаким названием уже существует'
          });
        }




        console.log([name, department, id]);

      }







    },
    size: BX.UI.Button.Size.SMALL,
    color: BX.UI.Button.Color.SUCCESS ,
    tag: BX.UI.Button.Tag.BUTTON,
    state: BX.UI.Button.State.DISABLED,
  });

  addWorkPosition.renderTo(contButtonAddWorkPosition);

  if ($('input[name=WP_NAME]').length) {
    $('input[name=WP_NAME]').val(addWPInputValue)
  }
  $('input[name=WP_NAME]').on('keyup', function(e) {

    addWPInputValue = e.target.value
    if (addWPInputValue.length) {
      addWorkPosition.setState(BX.UI.Button.State.ACTIVE)
    } else {
      addWorkPosition.setState(BX.UI.Button.State.DISABLED)
    }
    console.log(e.target.value)

  })



  var buttonSaveDepartmentUsers = new BX.UI.Button({
    id: "button-save-department-users",
    text: "Подтвердить",
    noCaps: false,
    round: false,
    className: "outline-none",
    onclick: async function(btn, event) {

      console.log(this.id)

      const departmentID = $('#cont_btn_add_user').attr('data-department-id')

      const requestData = [
        {name: 'department_id', value: departmentID},
      ]

      const result = await request('saveDepartmentChanges', requestData).then((res) => {
        return res
      })

      const errors = []

      result.forEach(el => {
        if (el.error) {
          errors.push(el.fio)
        }
      })

      console.log(errors)

      if (errors.length) {
        errors.forEach(el => {
          BX.UI.Notification.Center.notify({
            autoHideDelay: 10000,
            closeButton: true,
            content: `Испытательный срок сотрудника<br> ${el} истек.<br><br>
             Чтобы сохранить изменения на странице определите функциональную единицу сотрудника, чтобы он появился в отчетности по KPI за текущий период, либо продлите продолжительность испытательного срока.`
          });
        })
      } else {
        $('.actions-messages').animate({opacity: 1}, 300)
        setTimeout(function() {
          $('.actions-messages').animate({opacity: 0}, 500)

        }, 3000)
      }


    },
    size: BX.UI.Button.Size.MEDIUM,
    color: BX.UI.Button.Color.SUCCESS,
    tag: BX.UI.Button.Tag.BUTTON,
    state: BX.UI.Button.State
  });
  var buttonCancelDepartmentUsers = new BX.UI.Button({
    id: "button-cancel-department-users",
    text: "Отменить",
    noCaps: false,
    round: false,
    className: "outline-none",
    onclick: async function(btn, event) {
      console.log(this.id)

      const departmentID = $('#cont_btn_add_user').attr('data-department-id')

      const requestData = [
        {name: 'department_id', value: departmentID},
      ]

      const result = await request('cancelDepartmentChanges', requestData).then((res) => {
        return res
      })


      window.location = `${siteDir}kpi`
    },
    size: BX.UI.Button.Size.MEDIUM,
    color: BX.UI.Button.Color.LIGHT_BORDER,
    tag: BX.UI.Button.Tag.BUTTON,
    state: BX.UI.Button.State
  });


  buttonSaveDepartmentUsers.renderTo(contButtonWorkPositions);
  buttonCancelDepartmentUsers.renderTo(contButtonWorkPositions);

  var buttonSaveWPSalary = new BX.UI.Button({
    id: "button-save-wp-salary",
    text: "Подтвердить",
    noCaps: false,
    round: false,
    className: "outline-none",
    onclick: async function(btn, event) {


      const allData = []


      $('input[name=WP_SALARY]').each(function() {

        const id = $(this).attr('data-id')
        const value = $(this).val()

        allData.push({ID: id, ATT_SALARY: value})

      })

      console.log(allData)



      requestRows('saveDepartmentWPSalary', allData).then(function(response) {
        console.log(response)
        $('.actions-messages').animate({opacity: 1}, 300)
        setTimeout(function() {
          $('.actions-messages').animate({opacity: 0}, 500)

        }, 3000)

      })


    },
    size: BX.UI.Button.Size.MEDIUM,
    color: BX.UI.Button.Color.SUCCESS,
    tag: BX.UI.Button.Tag.BUTTON,
    state: BX.UI.Button.State
  });
  var buttonCancelWPSalary = new BX.UI.Button({
    id: "button-cancel-wp-salary",
    text: "Отменить",
    noCaps: false,
    round: false,
    className: "outline-none",
    onclick: function(btn, event) {

      const departmentID = getUrlParameter('department')

      window.location = `${siteDir}kpi/computed_rules?department=${departmentID}`

    },
    size: BX.UI.Button.Size.MEDIUM,
    color: BX.UI.Button.Color.LIGHT_BORDER,
    tag: BX.UI.Button.Tag.BUTTON,
    state: BX.UI.Button.State
  });


  buttonSaveWPSalary.renderTo(contButtonWorkPositionsSalary);
  buttonCancelWPSalary.renderTo(contButtonWorkPositionsSalary);




  $('.js-btn-delete-wp').on('click', async function() {

    const wpID = $(this).attr('data-id')
    const wpText = $(this).attr('data-name')

    if(confirm(`Вы действительно хотите удалить функциональную единицу ${wpText}`)) {
      console.log('wpID', wpID)

      const requestData = [
        {name: 'wp_id', value: wpID},
      ]

      const result = await request('deleteWorkPosition', requestData).then((res) => {
        return res
      })

      if (!result.error) {
        window.location.reload()
      } else {
        BX.UI.Notification.Center.notify({
          content: "Ошибка удаления функциональной единицы"
        });
      }

    }



  })

  const curPage = document.location.pathname

  $('.main-buttons-item').each(function() {

    const url = $(this).attr('data-url')

    if (!$(this).hasClass('main-buttons-item-active') && curPage === url) {
      $(this).addClass('main-buttons-item-active')
    }
  })

  if ($('.js-add-select').length) {
    $('.js-add-select').on('click', function() {
      const $prevSelect = $(this).prev()
      let countSelect = parseInt($prevSelect.find('select').attr('data-count'))
      const $parent = $(this).parent()

      const $cloneSelect = $prevSelect.clone()

      $cloneSelect.find('select').attr('data-count', countSelect + 1)
      $cloneSelect.appendTo($parent)

      $(this).appendTo($(this).parent())

    })
  }




  var buttonSaveNotify = new BX.UI.Button({
    id: "button-save-notify",
    text: "Сохранить",
    noCaps: false,
    round: false,
    className: "outline-none",
    onclick: async function(btn, event) {

      const allData = []




      $('select').each(function() {

        const id = $(this).attr('data-id')
        const count = $(this).attr('data-count')
        const property = $(this).attr('name')
        const value = $(this).val()
        if (id) allData.push({ID: id, PROPERTY: property, VALUE: value, COUNT: count })



      })

      console.log(allData)



      requestRows('saveNotifies', allData).then(function(response) {
        console.log(response)
        $('.actions-messages').animate({opacity: 1}, 300)
        setTimeout(function() {
          $('.actions-messages').animate({opacity: 0}, 500)

        }, 3000)

      })


    },
    size: BX.UI.Button.Size.MEDIUM,
    color: BX.UI.Button.Color.SUCCESS,
    tag: BX.UI.Button.Tag.BUTTON,
    state: BX.UI.Button.State
  });
  var buttonCancelNotify = new BX.UI.Button({
    id: "button-cancel-notify",
    text: "Отменить",
    noCaps: false,
    round: false,
    className: "outline-none",
    onclick: function(btn, event) {

      window.location = `${siteDir}kpi`

    },
    size: BX.UI.Button.Size.MEDIUM,
    color: BX.UI.Button.Color.LIGHT_BORDER,
    tag: BX.UI.Button.Tag.BUTTON,
    state: BX.UI.Button.State
  });


  buttonSaveNotify.renderTo(contButtonNotify);
  buttonCancelNotify.renderTo(contButtonNotify);





  var buttonSaveAccess = new BX.UI.Button({
    id: "button-save-access",
    text: "Сохранить",
    noCaps: false,
    round: false,
    className: "outline-none",
    onclick: async function(btn, event) {

      const allData = []

      $('select[name=UF_KPI_ACCESS_TO_DEPARTMENT]').each(function() {

        const department_id = $(this).attr('data-department-id')
        const uf_field = $(this).attr('name')
        const value = $(this).val()
        if (value) allData.push({ USER_ID: value, DEPARTMENT_ID: department_id, UF_FIELD: uf_field })

      })


      $('select[name=UF_KPI_ASSISTANT_TO_DEPARTMENT]').each(function() {

        const department_id = $(this).attr('data-department-id')
        const uf_field = $(this).attr('name')
        const value = $(this).val()
        if (value) allData.push({ USER_ID: value, DEPARTMENT_ID: department_id, UF_FIELD: uf_field })

      })

      console.log(allData)



      requestRows('addAccessToUsers', allData).then(function(response) {
        console.log('response', response)
        $('.actions-messages').animate({opacity: 1}, 300)
        setTimeout(function() {
          $('.actions-messages').animate({opacity: 0}, 500)

        }, 3000)

      })


    },
    size: BX.UI.Button.Size.MEDIUM,
    color: BX.UI.Button.Color.SUCCESS,
    tag: BX.UI.Button.Tag.BUTTON,
    state: BX.UI.Button.State
  });
  var buttonCancelAccess = new BX.UI.Button({
    id: "button-cancel-access",
    text: "Отменить",
    noCaps: false,
    round: false,
    className: "outline-none",
    onclick: function(btn, event) {

      window.location = `${siteDir}kpi`

    },
    size: BX.UI.Button.Size.MEDIUM,
    color: BX.UI.Button.Color.LIGHT_BORDER,
    tag: BX.UI.Button.Tag.BUTTON,
    state: BX.UI.Button.State
  });


  buttonSaveAccess.renderTo(contButtonAccess);
  buttonCancelAccess.renderTo(contButtonAccess);




  var buttonSaveSetKP = new BX.UI.Button({
    id: "button-save-set-kp",
    text: "Сохранить",
    noCaps: false,
    round: false,
    className: "outline-none",
    onclick: async function(btn, event) {

      console.log('asd')

      var pp = $('#priority-projects-weight').val()
      var tm = $('#top-managers-weight').val()
      var bt = $('#base-tasks-weight').val()

      const requestData = [
        {name: 'section_projects', value: pp},
        {name: 'section_top_managers', value: tm},
        {name: 'section_base_tasks', value: bt},
      ]

      console.log('buttonSaveSetKP', requestData)
      const result = await request('saveSetKPSections', requestData).then((res) => {
        return res
      })

      if (result.status) {
        $('.actions-messages').animate({opacity: 1}, 300)
        setTimeout(function() {
          $('.actions-messages').animate({opacity: 0}, 500)

        }, 3000)
      } else {
        BX.UI.Notification.Center.notify({
          content: result.message
        });
      }



      result.message

      console.log('result',result)


    },
    size: BX.UI.Button.Size.MEDIUM,
    color: BX.UI.Button.Color.SUCCESS,
    tag: BX.UI.Button.Tag.BUTTON,
    state: BX.UI.Button.State
  });

  var buttonCancelSetKP = new BX.UI.Button({
    id: "button-cancel-set-kp",
    text: "Отменить",
    noCaps: false,
    round: false,
    className: "outline-none",
    onclick: function(btn, event) {

      window.location = `${siteDir}kpi`

    },
    size: BX.UI.Button.Size.MEDIUM,
    color: BX.UI.Button.Color.LIGHT_BORDER,
    tag: BX.UI.Button.Tag.BUTTON,
    state: BX.UI.Button.State
  });


  buttonSaveSetKP.renderTo(contButtonSetKP);
  buttonCancelSetKP.renderTo(contButtonSetKP);


})






