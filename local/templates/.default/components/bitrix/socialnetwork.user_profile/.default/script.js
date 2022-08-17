BX.namespace("BX.Socialnetwork.User");

BX.Socialnetwork.User.Profile = (function()
{
	var Profile = function(arParams)
	{
		this.ajaxPath = "";
		this.siteId = "";
		this.languageId = "";
		this.otpDays = {};
		this.showOtpPopup = false;
		this.otpRecoveryCodes = false;
		this.profileUrl = "";
		this.passwordsUrl = "";
		this.popupHint = {};

		if (typeof arParams === "object")
		{
			this.ajaxPath = arParams.ajaxPath;
			this.siteId = arParams.siteId;
			this.languageId = arParams.languageId;
			this.otpDays = arParams.otpDays;
			this.showOtpPopup = arParams.showOtpPopup == "Y" ? true : false;
			this.otpRecoveryCodes = arParams.otpRecoveryCodes == "Y" ? true : false;
			this.profileUrl = arParams.profileUrl;
			this.passwordsUrl = arParams.passwordsUrl;
			this.codesUrl = arParams.codesUrl;
		}

		this.init();
	};

	Profile.prototype.init = function()
	{
		if (this.showOtpPopup)
		{
			var buttons = [];

			if (this.otpRecoveryCodes)
			{
				buttons.push(new BX.PopupWindowButton({
					text : BX.message('SONET_OTP_CODES'),
					className : "popup-window-button-accept",
					events : { click : BX.proxy(function()
					{
						location.href = this.codesUrl;
					}, this)}
				}));
			}
			buttons.push(new BX.PopupWindowButton({
					text : BX.message('SONET_OTP_SUCCESS_POPUP_PASSWORDS'),
					className : "popup-window-button-accept",
					events : { click : BX.proxy(function()
					{
						location.href = this.passwordsUrl;
					}, this)}
				}),
				new BX.PopupWindowButtonLink({
					text: BX.message('SONET_OTP_SUCCESS_POPUP_CLOSE'),
					className: "popup-window-button-link-cancel",
					events: { click :  BX.proxy(function()
					{
						location.href = this.profileUrl;
					}, this)}
				})
			);

			BX.PopupWindowManager.create("securityOtpSuccessPopup", null, {
				autoHide: false,
				offsetLeft: 0,
				offsetTop: 0,
				overlay : true,
				draggable: {restrict:true},
				closeByEsc: false,
				content: '<div style="width:450px;min-height:100px; padding:15px;font-size:14px;">' + BX.message('SONET_OTP_SUCCESS_POPUP_TEXT') + (this.otpRecoveryCodes ? BX.message("SONET_OTP_SUCCESS_POPUP_TEXT_RES_CODE") : '') + '<div style="background-color: #fdfaea;padding: 10px;border-color: #e5e0c4 #f1edd7 #f9f6e4;border-style: solid;border-width: 1px;border-radius: 2px;">' + BX.message('SONET_OTP_SUCCESS_POPUP_TEXT2') + '</div></div>',
				buttons: buttons
			}).show();
		}

		BX.ready(BX.delegate(function(){
			this.initHint('user-profile-email-help');
		}, this));
	};

	Profile.prototype.confirm = function()
	{
		if (confirm(BX.message("USER_PROFILE_CONFIRM")))
			return true;
		else
			return false;
	};

	Profile.prototype.changeUserActivity = function(userId, userActive)
	{
		if (!this.confirm())
			return false;

		if (!parseInt(userId) || !userActive)
			return false;

		BX.ajax({
			url: this.ajaxPath,
			method: 'POST',
			dataType: 'json',
			data: {
				user_id :userId,
				active : userActive,
				sessid: BX.bitrix_sessid(),
				site_id: this.siteId,
				json: 1
			},
			onsuccess: function (json)
			{
				if (json.result == 'success')
				{
					window.location.reload();
				}
				else
				{
					var DeleteErrorPopup = BX.PopupWindowManager.create('delete_error', this, {
						content: '<p>'+json.error+'</p>',
						offsetLeft:27,
						offsetTop:7,
						autoHide:true
					});

					DeleteErrorPopup.show();
				}
			}
		});
	}

	Profile.prototype.showExtranet2IntranetForm = function(userId, email)
	{
		var email = email ? true : false;

		window.Bitrix24Extranet2IntranetForm = BX.PopupWindowManager.create("BXExtranet2Intranet", null, {
			autoHide: false,
			zIndex: 0,
			offsetLeft: 0,
			offsetTop: 0,
			overlay : true,
			draggable: {restrict:true},
			closeByEsc: true,
			titleBar: BX.message(email ? 'BX24_TITLE_EMAIL' : 'BX24_TITLE'),
			closeIcon: { right : "12px", top : "10px"},
			buttons: [
				new BX.PopupWindowButton({
					text : BX.message('BX24_BUTTON'),
					className : "popup-window-button-accept",
					events : { click : function()
					{
						var popup = this;
						var form = BX('EXTRANET2INTRANET_FORM');

						if(form)
							BX.ajax.submit(form, BX.delegate(function(result) {
								popup.popupWindow.setContent(result);


							}));
					}}
				}),

				new BX.PopupWindowButtonLink({
					text: BX.message('BX24_CLOSE_BUTTON'),
					className: "popup-window-button-link-cancel",
					events: { click : function()
					{
						this.popupWindow.close();
					}}
				})
			],
			content: '<div style="width:450px;height:230px"></div>',
			events: {
				onAfterPopupShow: function()
				{
					this.setContent('<div style="width:450px;height:230px">'+BX.message('BX24_LOADING')+'</div>');
					BX.ajax.post(
						'/bitrix/tools/b24_extranet2intranet.php',
						{
							lang: BX.message('LANGUAGE_ID'),
							site_id: BX.message('SITE_ID') || '',
							USER_ID: userId,
							IS_EMAIL: email ? 'Y' : 'N'
						},
						BX.delegate(function(result)
							{
								this.setContent(result);
							},
							this)
					);
				}
			}
		});

		Bitrix24Extranet2IntranetForm.show();
	};

	Profile.prototype.reinvite = function(userId, isExtranet, bindObj)
	{
		if (!parseInt(userId))
			return false;

		bindObj = bindObj || null;

		var reinvite = "reinvite_user_id_" + (isExtranet == "Y" ? "extranet_" : "") + userId;

		BX.ajax.post(
			'/bitrix/tools/intranet_invite_dialog.php',
			{
				lang: this.languageId,
				site_id: this.siteId,
				reinvite: reinvite,
				sessid: BX.bitrix_sessid()
			},
			BX.delegate(function(result)
			{
				var InviteAccessPopup = BX.PopupWindowManager.create('invite_access', bindObj, {
					content: '<p>'+BX.message("SONET_REINVITE_ACCESS")+'</p>',
					offsetLeft:27,
					offsetTop:7,
					autoHide:true
				});

				InviteAccessPopup.show();
			}, this)
		);
	}


	Profile.prototype.deactivateUserOtp = function(userId, numDays)
	{
		if (!parseInt(userId))
			return false;

		BX.ajax({
			method: 'POST',
			dataType: 'json',
			url: this.ajaxPath,
			data:
			{
				userId: userId,
				sessid: BX.bitrix_sessid(),
				numDays: numDays,
				action: "deactivate"
			},
			onsuccess: function(json)
			{
				if (json.error)
				{

				}
				else
				{
					location.reload();
				}
			}
		});
	};

	Profile.prototype.deferUserOtp = function(userId, numDays)
	{
		if (!parseInt(userId))
			return false;

		BX.ajax({
			method: 'POST',
			dataType: 'json',
			url: this.ajaxPath,
			data:
			{
				userId: userId,
				sessid: BX.bitrix_sessid(),
				numDays: numDays,
				action: "defer"
			},
			onsuccess: function(json)
			{
				if (json.error)
				{

				}
				else
				{
					location.reload();
				}
			}
		});
	};

	Profile.prototype.activateUserOtp = function(userId)
	{
		if (!parseInt(userId))
			return false;

		BX.ajax({
			method: 'POST',
			dataType: 'json',
			url: this.ajaxPath,
			data:
			{
				userId: userId,
				sessid: BX.bitrix_sessid(),
				action: "activate"
			},
			onsuccess: function(json)
			{
				if (json.error)
				{

				}
				else
				{
					location.reload();
				}
			}
		});
	};

	Profile.prototype.showOtpDaysPopup = function(bind, userId, handler)
	{
		if (!parseInt(userId))
			return false;

		handler = (handler == "defer") ? "defer" : "deactivate";
		var self = this;

		var daysObj = [];
		for (var i in this.otpDays)
		{
			daysObj.push({
				text: this.otpDays[i],
				numDays: i,
				onclick: function(event, item)
				{
					this.popupWindow.close();

					if (handler == "deactivate")
						self.deactivateUserOtp(userId, item.numDays);
					else
						self.deferUserOtp(userId, item.numDays);
				}
			});
		}

		BX.PopupMenu.show('securityOtpDaysPopup', bind, daysObj,
			{   offsetTop:10,
				offsetLeft:0
			}
		);
	};

	Profile.prototype.showLink = function(btn)
	{
		var wrapper = btn.parentNode;
		var input = wrapper.querySelector('[data-input]');
		var link = wrapper.querySelector('[data-link]');
		var inpWidth, linkWidth;

		input.style.width = 'auto';
		BX.addClass(wrapper, 'user-profile-show-input');
		inpWidth = input.offsetWidth;
		linkWidth = link.offsetWidth;
		btn.style.display = 'none';

		setTimeout(function()
		{
			link.style.display = 'none';
			input.style.width = linkWidth + 'px';
		}, 50);

		setTimeout(function()
		{
			input.style.opacity = 1;
			input.style.width = inpWidth + 'px';
		}, 100);

		BX.bind(input, 'transitionend', function()
		{
			input.select();
		})
	}

	Profile.prototype.initHint = function(nodeId)
	{
		var node = BX(nodeId);
		if (node)
		{
			node.setAttribute('data-id', node)
			BX.bind(node, 'mouseover', BX.proxy(function(){
				var id = BX.proxy_context.getAttribute('data-id');
				var text = BX.proxy_context.getAttribute('data-text');
				this.showHint(id, BX.proxy_context, text);
			}, this));
			BX.bind(node, 'mouseout',  BX.proxy(function(){
				var id = BX.proxy_context.getAttribute('data-id');
				this.hideHint(id);
			}, this));
		}
	}

	Profile.prototype.showHint = function(id, bind, text)
	{
		if (this.popupHint[id])
		{
			this.popupHint[id].close();
		}

		this.popupHint[id] = new BX.PopupWindow('user-profile-email-hint', bind, {
			lightShadow: true,
			autoHide: false,
			darkMode: true,
			offsetLeft: 0,
			offsetTop: 2,
			bindOptions: {position: "top"},
			zIndex: 200,
			events : {
				onPopupClose : function() {this.destroy()}
			},
			content : BX.create("div", { attrs : { style : "padding-right: 5px; width: 250px;" }, html: text})
		});
		this.popupHint[id].setAngle({offset:13, position: 'bottom'});
		this.popupHint[id].show();

		return true;
	}

	Profile.prototype.hideHint = function(id)
	{
		this.popupHint[id].close();
		this.popupHint[id] = null;
	}

	return Profile;
})();

(function($){
    jQuery.fn.lightTabs = function(options){

        var createTabs = function(){
            tabs = this;
            i = 0;

            showPage = function(i){
                $(tabs).children("div").children("div").hide();
                $(tabs).children("div").children("div").eq(i).show();
                $(tabs).children("ul").children("li").removeClass("active");
                $(tabs).children("ul").children("li").eq(i).addClass("active");
            }

            showPage(0);

            $(tabs).children("ul").children("li").each(function(index, element){
                $(element).attr("data-page", i);
                i++;
            });

            $(tabs).children("ul").children("li").click(function(){
				showPage(parseInt($(this).attr("data-page")));
				location.hash = this.querySelector('a').getAttribute('href');
                return false;
            });
        };
        return this.each(createTabs);
    };
})(jQuery);
$(document).ready(function(){
	$("#tabs").lightTabs();
	if(location.hash.match(/#tabs-/)){
		$('#tabs a[href="'+location.hash+'"]').parent().click();
	}

	$(document).on('click','.bp-interface-toolbar .bp-context-button', function(e){
		if(~this.getAttribute('href').indexOf('type=my_processes')){
			e.preventDefault();
			e.stopPropagation();
			$('.processes').hide();
			$('.my_processes').show();
			$(this).addClass('active').parent().siblings().find('a').removeClass('active');
			return false;
		}else if(~this.getAttribute('href').indexOf('type=processes')){
			e.preventDefault();
			e.stopPropagation();
			$('.processes').show();
			$('.my_processes').hide();
			$(this).addClass('active').parent().siblings().find('a').removeClass('active');
			return false;
		}
	});
	$(document).on('click','#bizproc_task_list .bp-button', function(e){
		e.preventDefault();
		e.stopPropagation();
		var _this = $(this);
		var _this__href = _this.attr('href');
		if(~_this__href.indexOf('/company/personal/user/')){
			var matches = _this__href.match(/\&ID=([0-9]+)/);
			if(matches){
				location.href = "/company/personal/bizproc/"+matches[1]+"/?back_url="+location.pathname;
			}
		}
		return false;
	})
});

window.raschetny_l_pp_wind = null;
function raschetny_l_pp(){
	if(!window.raschetny_l_pp_wind){

		window.raschetny_l_pp_wind = new BX.PopupWindow("raschetny_l_pp_wind", null, {
			   content: BX('raschetny_l_pp_wind_cont'),
			   closeIcon: {right: "20px", top: "10px" },
			   titleBar: {content: BX.create("span", {html: "Расчетный листок", 'props': {'style': 'line-height:50px'}})},
			   zIndex: 0,
			   offsetLeft: 0,
			   offsetTop: 0,
			   draggable: {restrict: false},
			   buttons: [
				  new BX.PopupWindowButton({
					 text: "Загрузить" ,
					 className: "popup-window-button-accept" ,
					 events: {click: function(){
						$('#raschetny_l_pp_wind_cont form').submit();
					 }}
				  }),
				  new BX.PopupWindowButton({
					 text: "Закрыть" ,
					 className: "webform-button-link-cancel" ,
					 events: {click: function(){
						this.popupWindow.close();
					 }}
				  })
			   ]
			});
	}
	window.raschetny_l_pp_wind.show();
}

$(function () {
	var $authSelect = $("#auth_select");
	$authSelect.select2({width: '100%'});
});
