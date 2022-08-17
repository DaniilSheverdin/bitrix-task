{"version":3,"sources":["table.js"],"names":["BX","namespace","Timeman","Component","Schedule","ShiftPlan","Table","options","BaseComponent","apply","this","containerSelector","isSlider","scheduleId","gridId","useEmployeesTimezoneName","errorCodeOverlappingPlans","addEventHandlers","prototype","__proto__","constructor","addEventHandlersInsideGrid","addCustomEvent","bind","document","querySelector","addEventListener","e","detail","dayCellNodes","i","length","timeCells","querySelectorAll","timeIndex","selectOneByRole","delegate","onShiftplanMenuToggleClick","onAddShiftPlanClick","addShiftplanBtns","selectAllByRole","shiftplanMenuToggles","deleteUserBtns","onDeleteUserClick","useEmployeesTimezone","getCookie","event","popupDeleteUser","close","userId","currentTarget","dataset","PopupWindow","id","autoHide","draggable","bindOptions","forceBindPosition","closeByEsc","closeIcon","top","right","zIndex","titleBar","message","content","replace","userName","buttons","PopupWindowButton","text","className","events","click","Main","gridManager","getInstanceById","tableFade","ajax","runAction","data","then","response","reloadGrid","show","reload","stopPropagation","preventDefault","planMenuPopup","buildPlanMenuPopup","items","buildPlanMenuItems","PopupMenu","create","maxHeight","bindElement","angle","itemDelete","push","util","getRandomString","htmlspecialchars","onclick","form","createFormDataForShiftPlan","onSuccessShiftPlanDeleted","shiftPlan","itemAdd","btn","formWrapper","formData","FormData","inputs","append","name","value","absenceBlock","findParent","tag","title","force","isDisabled","onSuccessShiftPlanAdded","errors","code","UI","Dialogs","MessageBox","modal","MessageBoxButtons","YES_NO","popupOptions","onYes","messageBox","onNo","dispatchCellHtmlRedraw","getEventContainer","CustomEvent","html","cellHtml","dispatchEvent"],"mappings":"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","file":"table.map.js"}