<<<<<<< HEAD
<?

if(!$arResult['CAN_EDIT_USER']||$_REQUEST['debug']=='Y') return;

$pluralForm = function($n, $form1, $form2, $form5) {
    $n = abs(intval($n)) % 100;
    $n1 = $n % 10;
    if ($n > 10 && $n < 20) return $n.' '.$form5;
    if ($n1 > 1 && $n1 < 5) return $n.' '.$form2;
    if ($n1 == 1) return $n.' '.$form1;
    return $n.' '.$form5;
};
?>
<tr>
<td colspan="2">
	<div id="tabs">
		<ul>
			<li class="active"><a href="#info">Общая информация</a></li>
			<?if(!empty($arResult['PERSONAL_DATA'])):?>
				<li><a href="#personal">Личные Данные</a></li>
				<li><a href="#career">Моя карьера</a></li>
			<?endif;?>
			<li><a href="#process">Бизнес-процессы</a></li>
		</ul>
		<div class="tabs">
			<div id="info">
				<table class="user-profile-block" cellspacing="0">
				<tr>
					<td class="user-profile-block-title" colspan="2"><?=GetMessage("SONET_CONTACT_TITLE")?></td>
				</tr>
				<?
				if (is_array($arResult["UserFieldsContact"]["DATA"]))
				{
					foreach ($arResult["UserFieldsContact"]["DATA"] as $field => $arUserField)
					{
						if (
							is_array($arUserField["VALUE"]) && count($arUserField["VALUE"]) > 0
							|| !is_array($arUserField["VALUE"]) && $arUserField["VALUE"] != ''
						)
						{
							?><tr>
								<td class="user-profile-nowrap"><?=$arUserField["NAME"].":"?></td>
								<td class="user-profile-nowrap-second"><?
									switch ($field)
									{
										case "PERSONAL_MOBILE":
										case "WORK_PHONE":
										case "PERSONAL_PHONE":
											echo $arUserField["VALUE"];
											if (CModule::IncludeModule('voximplant'))
											{
												$userPermissions = \Bitrix\Voximplant\Security\Permissions::createWithCurrentUser();
												if(CVoxImplantMain::Enable($arResult["User"][$field]) &&
												$userPermissions->canPerform(
													\Bitrix\Voximplant\Security\Permissions::ENTITY_CALL,
													\Bitrix\Voximplant\Security\Permissions::ACTION_PERFORM,
													\Bitrix\Voximplant\Security\Permissions::PERMISSION_CALL_USERS
												))
												{
													?>
													<span class="sonet_call_btn" onclick="BXIM.phoneTo('<?=CUtil::JSEscape($arResult["User"][$field])?>');"></span>
													<?
												}
											}
											break;
										default:
											echo $arUserField["VALUE"];
									}
								?></td>
							</tr><?
						}
					}
				}

				if (is_array($arResult["UserPropertiesContact"]["DATA"]))
				{
					foreach ($arResult["UserPropertiesContact"]["DATA"] as $field => $arUserField)
					{
						if (
							is_array($arUserField["VALUE"]) && count($arUserField["VALUE"]) > 0
							|| !is_array($arUserField["VALUE"]) && $arUserField["VALUE"] != ''
						)
						{
							?><tr>
								<td class="user-profile-nowrap"><?=$arUserField["EDIT_FORM_LABEL"].":"?></td>
								<td class="user-profile-nowrap"><?
									$value = htmlspecialcharsbx($arUserField["VALUE"]);
									switch ($field)
									{
										case "UF_FACEBOOK":
										case "UF_LINKEDIN":
										case "UF_XING":
											$href = (!preg_match('#^https?://#i', trim($value)) ? 'http://' : '').trim($value); ?>
											<a href="<?=$href?>"><?=$value?></a>
											<?break;
										case "UF_TWITTER":?>
											<a href="http://twitter.com/<?=$value?>"><?=$value?></a><?
											break;
										case "UF_SKYPE":?>
											<a href="callto:<?=$value?>"><?=$value?></a><?
											break;
										default:
											$GLOBALS["APPLICATION"]->IncludeComponent(
												"bitrix:system.field.view",
												$arUserField["USER_TYPE"]["USER_TYPE_ID"],
												array("arUserField" => $arUserField, "inChain" => "N"),
												null,
												array("HIDE_ICONS"=>"Y")
											);
									}
								?></td>
							</tr><?
						}
					}
				}
				?>
	<!--otp-->
				<?
				if (
					$arResult["User"]["OTP"]["IS_ENABLED"] !== "N"
					&&
					(
						$USER->GetID() == $arResult["User"]["ID"]
						|| $USER->CanDoOperation('security_edit_user_otp')
					)
					&&
					(
						$arResult["User"]["OTP"]["IS_MANDATORY"]
						|| !$arResult["User"]["OTP"]["IS_MANDATORY"] && $arResult["User"]["OTP"]["IS_EXIST"]
					)
				)
				{
					?><tr>
						<td class="user-profile-block-title"><?=GetMessage("SONET_SECURITY")?></td>
					</tr>
					<tr>
						<td class="user-profile-nowrap"><?=GetMessage("SONET_OTP_AUTH")?></td>
						<td><?
							if ($arResult["User"]["OTP"]["IS_ACTIVE"])
							{
								?>
									<span class="user-profile-otp-on" style="margin-right: 15px"><?=GetMessage("SONET_OTP_ACTIVE")?></span>

									<?if ($USER->CanDoOperation('security_edit_user_otp') || !$arResult["User"]["OTP"]["IS_MANDATORY"]):?>
										<a class="user-profile-otp-link-blue" href="javascript:void(0)" onclick="socnetUserProfileObj.showOtpDaysPopup(this, '<?=CUtil::JSEscape($arResult["User"]["ID"])?>', 'deactivate')"><?=GetMessage("SONET_OTP_DEACTIVATE")?></a>
									<?endif?>

									<?if ($USER->GetID() == $arResult["User"]["ID"]):?>
										<a class="user-profile-otp-link-blue" href="<?=$arResult["Urls"]["Security"]?>"><?=GetMessage("SONET_OTP_CHANGE_PHONE")?></a>
									<?endif?>
								<?
							}
							elseif (
								!$arResult["User"]["OTP"]["IS_ACTIVE"]
								&& $arResult["User"]["OTP"]["IS_MANDATORY"]
							)
							{
								?><span class="user-profile-otp-off" style="margin-right: 15px"><?=($arResult["User"]["OTP"]["IS_EXIST"]) ? GetMessage("SONET_OTP_NOT_ACTIVE") : GetMessage("SONET_OTP_NOT_EXIST")?></span><?

								if ($arResult["User"]["OTP"]["IS_EXIST"])
								{
									?><a class="user-profile-otp-link-blue" href="javascript:void(0)" onclick="socnetUserProfileObj.activateUserOtp('<?=CUtil::JSEscape($arResult["User"]["ID"])?>')"><?=GetMessage("SONET_OTP_ACTIVATE")?></a><?
									if ($USER->GetID() == $arResult["User"]["ID"])
									{
										?><a class="user-profile-otp-link-blue" href="<?=$arResult["Urls"]["Security"]?>"><?=GetMessage("SONET_OTP_CHANGE_PHONE")?></a><?
									}
								}
								else
								{
									if ($USER->GetID() == $arResult["User"]["ID"])
									{
										?><a class="user-profile-otp-link-blue" href="<?=$arResult["Urls"]["Security"]?>"><?=GetMessage("SONET_OTP_SETUP")?></a><?
									}
									else
									{
										?><a class="user-profile-otp-link-blue" href="javascript:void(0)" onclick="socnetUserProfileObj.showOtpDaysPopup(this, '<?=CUtil::JSEscape($arResult["User"]["ID"])?>', 'defer')"><?
											?><?=GetMessage("SONET_OTP_PROROGUE")?><?
										?></a><?
									}
								}

								if ($arResult["User"]["OTP"]["NUM_LEFT_DAYS"])
								{
									?><span class="user-profile-otp-days"><?=GetMessage("SONET_OTP_LEFT_DAYS", array("#NUM#" => "<strong>".$arResult["User"]["OTP"]["NUM_LEFT_DAYS"]."</strong>"))?></span><?
								}
							}
							elseif (
								!$arResult["User"]["OTP"]["IS_ACTIVE"]
								&& $arResult["User"]["OTP"]["IS_EXIST"]
								&& !$arResult["User"]["OTP"]["IS_MANDATORY"]
							)
							{
								?><span class="user-profile-otp-off" style="margin-right: 15px"><?=GetMessage("SONET_OTP_NOT_ACTIVE")?></span>
								<a class="user-profile-otp-link-blue" href="javascript:void(0)" onclick="socnetUserProfileObj.activateUserOtp('<?=CUtil::JSEscape($arResult["User"]["ID"])?>')"><?=GetMessage("SONET_OTP_ACTIVATE")?></a><?
								if ($USER->GetID() == $arResult["User"]["ID"])
								{
									?><a class="user-profile-otp-link-blue" href="<?=$arResult["Urls"]["Security"]?>"><?=GetMessage("SONET_OTP_CHANGE_PHONE")?></a><?
								}

								if ($arResult["User"]["OTP"]["NUM_LEFT_DAYS"])
								{
									?><span class="user-profile-otp-days"><?=GetMessage("SONET_OTP_LEFT_DAYS", array("#NUM#" => "<strong>".$arResult["User"]["OTP"]["NUM_LEFT_DAYS"]."</strong>"))?></span><?
								}
							}
						?></td>
					</tr>
					<!-- passwords --><?
					if ($USER->GetID() == $arResult["User"]["ID"])
					{
						?><tr>
							<td class="user-profile-nowrap"><?=GetMessage("SONET_PASSWORDS")?></td>
							<td>
								<a href="<?=$arResult["Urls"]["Passwords"]?>"><?=GetMessage("SONET_PASSWORDS_SETTINGS")?></a>
							</td>
						</tr><?
					}
					?><!-- codes --><?
					if (
						$USER->GetID() == $arResult["User"]["ID"]
						&& $arResult["User"]["OTP"]["IS_ACTIVE"]
						&& $arResult["User"]["OTP"]["ARE_RECOVERY_CODES_ENABLED"]
					)
					{
						?><tr>
							<td class="user-profile-nowrap"><?=GetMessage("SONET_OTP_CODES")?></td>
							<td>
								<a href="<?=$arResult["Urls"]["Codes"]?>"><?=GetMessage("SONET_OTP_CODES_SHOW")?></a>
							</td>
						</tr><?
						?><tr><td><br/><br/></td></tr><?
					}
				}
				?>
	<!-- // otp -->
				<tr>
					<td class="user-profile-block-title" colspan="2"><?=GetMessage("SONET_COMMON_TITLE")?></td>
				</tr>
				<tr>
					<td class="user-profile-nowrap""><?=GetMessage("SONET_USER_STATUS").":"?></td>
					<td>
						<? $onlineStatus = CUser::GetOnlineStatus($arUser['ID'], MakeTimeStamp($arUser["LAST_ACTIVITY_DATE"], "YYYY-MM-DD HH-MI-SS")); ?>
						<span class="user-profile-status-icon user-profile-status-icon-<?=$onlineStatus['STATUS']?>"><?=$onlineStatus['STATUS_TEXT']?></span><?
						if($onlineStatus['STATUS'] == 'idle'):
							echo ($onlineStatus['LAST_SEEN_TEXT']? ", ".GetMessage('SONET_LAST_SEEN_IDLE_'.($arUser["PERSONAL_GENDER"] == 'F'? 'F': 'M'), Array('#LAST_SEEN#' => $onlineStatus['LAST_SEEN_TEXT'])): '');
						else:
							echo ($onlineStatus['LAST_SEEN_TEXT']? ", ".GetMessage('SONET_LAST_SEEN_'.($arUser["PERSONAL_GENDER"] == 'F'? 'F': 'M'), Array('#LAST_SEEN#' => $onlineStatus['LAST_SEEN_TEXT'])): '');
						endif;
						?>
						<?if (!in_array($arUser['ACTIVITY_STATUS'], array('active', 'email'))):?>
							<div class="user-activity-status">
								<span class="employee-dept-post employee-dept-<?=$arUser["ACTIVITY_STATUS"]?>"><?=GetMessage("SONET_USER_".$arUser["ACTIVITY_STATUS"])?></span>
							</div>
						<?endif;?>
					</td>
				</tr>
				<?
				if (is_array($arResult["UserFieldsMain"]["DATA"]))
				{
					foreach ($arResult["UserFieldsMain"]["DATA"] as $field => $arUserField)
					{
						if (in_array($field, Array('LAST_ACTIVITY_DATE', 'LAST_LOGIN')))
						{
							continue;
						}
						if (
							is_array($arUserField["VALUE"]) && count($arUserField["VALUE"]) > 0
							|| !is_array($arUserField["VALUE"]) && $arUserField["VALUE"] != ''
						)
						{
							?><tr>
								<td class="user-profile-nowrap"><?=$arUserField["NAME"].":"?></td>
								<td><?=$arUserField["VALUE"];?></td>
							</tr><?
						}
					}
				}

				if (is_array($arResult["UserPropertiesMain"]["DATA"]))
				{
					foreach ($arResult["UserPropertiesMain"]["DATA"] as $field => $arUserField){
						if (
							(
								is_array($arUserField["VALUE"]) && count($arUserField["VALUE"]) > 0
								|| !is_array($arUserField["VALUE"]) && $arUserField["VALUE"] != ''
							)
							&& (
								$field != "UF_DEPARTMENT"
								|| (
									is_array($arUserField["VALUE"])
									&& $arUserField["VALUE"][0] > 0
								)
							)
						)
						{
							?><tr>
								<td class="user-profile-nowrap"><?=$arUserField["EDIT_FORM_LABEL"].":"?></td>
								<td><?
									$bInChain = ($field == "UF_DEPARTMENT" ? "Y" : "N");
									$GLOBALS["APPLICATION"]->IncludeComponent(
										"bitrix:system.field.view",
										$arUserField["USER_TYPE"]["USER_TYPE_ID"],
										array("arUserField" => $arUserField, "inChain" => $bInChain),
										null,
										array("HIDE_ICONS"=>"Y")
									);
								?></td>
							</tr><?
						}
					}
				}

				if (is_array($arResult['MANAGERS']) && count($arResult['MANAGERS'])>0)
				{
					?><tr>
						<td class="user-profile-nowrap"><?=GetMessage("SONET_MANAGERS").":"?></td>
						<td><?
							$bFirst = true;
							foreach ($arResult['MANAGERS'] as $id => $sub_user)
							{
								if (!$bFirst) echo ', '; else $bFirst = false;
								$name = CUser::FormatName($arParams['NAME_TEMPLATE'], $sub_user, true, false);
								?><a class="user-profile-link" href="<?=CComponentEngine::MakePathFromTemplate($arParams['PATH_TO_USER'], array("user_id" => $sub_user["ID"]))?>"><?=$name?></a><?
								if ($sub_user["WORK_POSITION"] != '') echo " (".$sub_user["WORK_POSITION"].")";?><?
							}
						?></td>
					</tr><?
				}

				if (is_array($arResult['SUBORDINATE']) && count($arResult['SUBORDINATE'])>0)
				{
					?><tr>
						<td class="user-profile-nowrap"><?=GetMessage("SONET_SUBORDINATE").":"?></td>
						<td><?
							$bFirst = true;
							foreach ($arResult['SUBORDINATE'] as $id => $sub_user)
							{
								if (!$bFirst) echo ', '; else $bFirst = false;
								$name = CUser::FormatName($arParams['NAME_TEMPLATE'], $sub_user, true, false);
								?><a class="user-profile-link" href="<?=CComponentEngine::MakePathFromTemplate($arParams['PATH_TO_USER'], array("user_id" => $sub_user["ID"]))?>"><?=$name?></a><?
								if ($sub_user["WORK_POSITION"] != '') echo " (".$sub_user["WORK_POSITION"].")";?><?
							}
						?></td>
					</tr><?
				}?>
				<?
				if(!empty($arResult['PERSONAL_DATA_DateOfBirth'])): ?>
					<tr>
						<td class="user-profile-nowrap">Дата рождения:</td>
						<td>
							<div style="margin-bottom:5px;"><?=FormatDate("j F", MakeTimeStamp($arResult['PERSONAL_DATA_DateOfBirth']))?></div>
							<label style="line-height: 12px;">
								<input type="checkbox" onchange="jQuery.post(location.pathname, {DateOfBirthHide:this.checked?0:1})" style="margin: 0;vertical-align: top;" <?=(empty($arResult['User']['UF_DATEOFBIRTHHIDE'])?"checked":"")?>> <span style="vertical-align: top;">Отображать на странице <a target="_blank" href="<?=SITE_DIR?>company/birthdays.php">"Дни рождения"</a></span>
							</label>
						</td>
					</tr>
				<?endif;?>
				<?
				if (!empty($arResult["User"]["EMAIL_FORWARD_TO"]))
				{?>
					<tr>
						<td class="user-profile-block-title" colspan="2">
							<?=GetMessage("SONET_EMAIL_FORWARD_TO")?>
							<span class="user-profile-email-help" id="user-profile-email-help" data-text="<?=htmlspecialcharsbx(GetMessage("SONET_EMAIL_FORWARD_TO_HINT"))?>">?</span>
						</td>
					</tr><?
					if (!empty($arResult["User"]["EMAIL_FORWARD_TO"]['BLOG_POST'])){
						?><tr>
							<td class="user-profile-mail-link"><?=GetMessage("SONET_EMAIL_FORWARD_TO_BLOG_POST").":"?></td>
							<td class="user-profile-block-right user-profile-mail-link" >
								<div class="user-profile-mail-link-block">
									<span class="user-profile-short-link" data-link=""><?=$arResult["User"]["EMAIL_FORWARD_TO"]['BLOG_POST']?></span>
									<input type="text" class="user-profile-link-input" data-input="" value="<?=$arResult["User"]["EMAIL_FORWARD_TO"]['BLOG_POST']?>">
									<span onclick="socnetUserProfileObj.showLink(this);" class="user-profile-link user-profile-show-link-btn"><?=GetMessage("SONET_EMAIL_FORWARD_TO_SHOW")?></span>
								</div>
							</td>
						</tr><?
					}
					if (!empty($arResult["User"]["EMAIL_FORWARD_TO"]['TASKS_TASK'])){
						?><tr>
						<td class="user-profile-mail-link"><?=GetMessage("SONET_EMAIL_FORWARD_TO_TASK").":"?></td>
						<td class="user-profile-block-right user-profile-mail-link" >
							<div class="user-profile-mail-link-block">
								<span class="user-profile-short-link" data-link=""><?=$arResult["User"]["EMAIL_FORWARD_TO"]['TASKS_TASK']?></span>
								<input type="text" class="user-profile-link-input" data-input="" value="<?=$arResult["User"]["EMAIL_FORWARD_TO"]['TASKS_TASK']?>">
								<span onclick="socnetUserProfileObj.showLink(this);" class="user-profile-link user-profile-show-link-btn"><?=GetMessage("SONET_EMAIL_FORWARD_TO_SHOW")?></span>
							</div>
						</td>
						</tr><?
					}
				}

				$additional = "";

				if (is_array($arResult["UserFieldsPersonal"]["DATA"]))
				{
					foreach ($arResult["UserFieldsPersonal"]["DATA"] as $field => $arUserField)
					{
						if(in_array($field, ['PERSONAL_BIRTHDAY', 'PERSONAL_GENDER'])) continue;
						if (is_array($arUserField["VALUE"]) && count($arUserField["VALUE"]) > 0 || !is_array($arUserField["VALUE"]) && $arUserField["VALUE"] != '')
						{
							$additional .= '<tr>
								<td class="user-profile-nowrap">'.$arUserField["NAME"].':</td>
								<td>'.$arUserField["VALUE"].'</td></tr>';
						}
					}
				}

				if (is_array($arResult["UserFieldsPersonal"]["DATA"]))
				{
					foreach ($arResult["UserPropertiesPersonal"]["DATA"] as $field => $arUserField)
					{
						if (is_array($arUserField["VALUE"]) && count($arUserField["VALUE"]) > 0 || !is_array($arUserField["VALUE"]) && $arUserField["VALUE"] != '')
						{
							$additional .= '<tr><td class="user-profile-nowrap">'.$arUserField["EDIT_FORM_LABEL"].':</td><td>';

							ob_start();
							$GLOBALS["APPLICATION"]->IncludeComponent(
								"bitrix:system.field.view",
								$arUserField["USER_TYPE"]["USER_TYPE_ID"],
								array("arUserField" => $arUserField, "inChain" => $field == "UF_DEPARTMENT" ? "Y" : "N"),
								null,
								array("HIDE_ICONS"=>"Y")
							);
							$additional .= ob_get_contents();
							ob_end_clean();

							$additional .= '</td></tr>';
						}
					}
				}

				if (is_array($arResult["Groups"]["List"]) && count($arResult["Groups"]["List"]) > 0)
				{
					$additional .= '<tr><td class="user-profile-nowrap">'.GetMessage("SONET_GROUPS").':</td><td>';
					$bFirst = true;
					foreach ($arResult["Groups"]["List"] as $key => $group)
					{
							if (!$bFirst)
								$additional .= ', ';
							$bFirst = false;
							$additional .= '<a class="user-profile-link" href="'.$group["GROUP_URL"].'">'.$group["GROUP_NAME"].'</a>';
					}
					$additional .= '</td></tr>';
				}
				?>

				<?if ($additional != ''):?>
					<tr>
						<td class="user-profile-block-title" colspan="2"><?=GetMessage("SONET_ADDITIONAL_TITLE")?></td>
					</tr>
					<?=$additional?>
				<?endif;?>
				<?if($bNetwork && IsModuleInstalled('socialservices') && $USER->GetID() == $arUser["ID"] && \Bitrix\Main\Config\Option::get('socialservices', 'network_last_update_check', 0) > 0):?>
					<tr>
						<td class="user-profile-block-title" colspan="2"><?=GetMessage("SONET_SOCSERV_CONTACTS")?></td>
					</tr>
					<tr>
						<td colspan="2">
							<?
								$APPLICATION->IncludeComponent(
									'bitrix:socserv.contacts',
									'',
									array(
										'USER_ID' => $arUser["ID"],
									),
									$component,
									array("HIDE_ICONS" => "Y")
								);
							?>
						</td>
					</tr>
				<? endif; ?>
				
				</table>
			</div>
			<?if(!empty($arResult['PERSONAL_DATA'])){?>
				<div id="personal" style="display: none;">
					<table class="user-profile-block" cellspacing="0">
						<?if(is_array($arResult['PERSONAL_DATA']['Passport'])){?>
							<tr>
								<td class="user-profile-block-title" colspan="2">Паспортные данные</td>
							</tr>
							<tr>
								<td class="user-profile-nowrap">Серия:</td>
								<td><?=$arResult['PERSONAL_DATA']['Passport']['Series']?></td>
							</tr>
							<tr>
								<td class="user-profile-nowrap">Номер:</td>
								<td><?=$arResult['PERSONAL_DATA']['Passport']['Number']?></td>
							</tr>
							<tr>
								<td class="user-profile-nowrap">Кем выдан:</td>
								<td><?=$arResult['PERSONAL_DATA']['Passport']['IssuedBy']?></td>
							</tr>
							<tr>
								<td class="user-profile-nowrap">Дата выдачи:</td>
								<td><?=$arResult['PERSONAL_DATA']['Passport']['DateOfIssue']?></td>
							</tr>
							<tr>
								<td class="user-profile-nowrap">Код подразделения:</td>
								<td><?=$arResult['PERSONAL_DATA']['Passport']['DivisionCode']?></td>
							</tr>
						<?}?>
						<tr>
							<td class="user-profile-status-icon">Адрес по прописке:</td>
							<td><?=$arResult['PERSONAL_DATA']['AddressOfRegistration']?></td>
						</tr>
						<tr>
							<td class="user-profile-status-icon">Адрес места проживания:</td>
							<td><?=$arResult['PERSONAL_DATA']['AddressOfResidence']?></td>
						</tr>
						<?if(is_array($arResult['PERSONAL_DATA']['EducationList'])){?>
							<tr>
								<td class="user-profile-block-title" colspan="2">Образование</td>
							</tr>
							<?
							if(is_array($arResult['PERSONAL_DATA']['EducationList']['Education'][0])){
								$arEducationList=$arResult['PERSONAL_DATA']['EducationList']['Education'];
							}else{
								$arEducationList=$arResult['PERSONAL_DATA']['EducationList'];
							}

							foreach ($arEducationList as $arEducation) :?>
								<tr>
								<td  class="user-profile-nowrap">Уровень образования:</td>
								<td><?=$arEducation['Type']?></td>
							</tr>
							<tr>
								<td  class="user-profile-nowrap">Специальность по диплому:</td>
								<td><?=$arEducation['Speciality']?></td>
							</tr>
							<tr>
								<td  class="user-profile-nowrap">Образовательное учреждение:</td>
								<td><?=$arEducation['Iinstitution']?></td>
							</tr>
							<tr>
								<td  class="user-profile-nowrap">Год окончания обучения:</td>
								<td><?=$arEducation['YearOfEnd']?></td>
							</tr>
							<? endforeach; ?>
						<?}?>
						<?if(false && is_array($arResult['PERSONAL_DATA']['Relatives']['Relative'])){?>
							<?
							if(is_array($arResult['PERSONAL_DATA']['Relatives']['Relative'][0])){
								$arRelativeList=$arResult['PERSONAL_DATA']['Relatives']['Relative'];
							}else{
								$arRelativeList=$arResult['PERSONAL_DATA']['Relatives'];
							}
							foreach ($arRelativeList as $arRelative) :?>
								<tr>
									<td class="user-profile-block-title" colspan="2">Данные о родственнике сотрудника:</td>
								</tr>
								<tr>
									<td  class="user-profile-nowrap">Тип родственной связи:</td>
									<td><?=$arRelative['Kinship']?></td>
								</tr>
								<tr>
									<td  class="user-profile-nowrap">ФИО:</td>
									<td><?=$arRelative['Name']?></td>
								</tr>
								<tr>
									<td  class="user-profile-nowrap">Дата рождения:</td>
									<td><?=$arRelative['DateOfBirth']?></td>
								</tr>
								<?if($arRelative['DateOfMarriage']!=''):?>
									<tr>
										<td  class="user-profile-nowrap">Дата заключения брака:</td>
										<td><?=$arRelative['DateOfMarriage']?></td>
									</tr>
								<?endif;?>
								<?if($arRelative['PlaceOfWork']!=''):?>
									<tr>
										<td  class="user-profile-nowrap">Место работы:</td>
										<td><?=$arRelative['PlaceOfWork']?></td>
									</tr>
								<?endif;?>
								<?if($arRelative['AddressOfResidence']!=''):?>
									<tr>
										<td  class="user-profile-nowrap">Место проживания:</td>
										<td><?=$arRelative['AddressOfResidence']?></td>
									</tr>
								<?endif;?>
							<?endforeach;?>
						<?}?>
					</table>
					<div style="padding:25px 0;">
						<a href="<?=SITE_DIR?>bizproc/processes/?livefeed=y&list_id=530&element_id=0" class="bx-notifier-item-button bx-notifier-item-button-confirm bx-notifier-item-button-accept">Изменить данные</a>
					</div>
				</div>
				<div id="career" style="display: none;">
					<table class="user-profile-block" cellspacing="0">
						<tr>
							<td class="user-profile-block-title" colspan="2">Карьера</td>
						</tr>
						<?
						if(isset($arResult['PERSONAL_DATA']['OrderRank'])){
							$arResult['PERSONAL_DATA']['OrderRank'] = trim($arResult['PERSONAL_DATA']['OrderRank']);
							if($arResult['PERSONAL_DATA']['OrderRank']):
							?>
								<tr>
									<td class="user-profile-nowrap">Классный чин:</td>
									<td><?=$arResult['PERSONAL_DATA']['OrderRank']?></td>
								</tr>
							<?endif;?>
						<?}?>
						<?
						if(isset($arResult['PERSONAL_DATA']['Experience']['General']['Years'])){
							$arResult['PERSONAL_DATA']['Experience']['General']['Years'] = trim($arResult['PERSONAL_DATA']['Experience']['General']['Years']);
							if(!empty($arResult['PERSONAL_DATA']['Experience']['General']['Years'])):?>
								<tr>
									<td  class="user-profile-nowrap">Общий трудовой стаж:</td>
									<td><?=$pluralForm($arResult['PERSONAL_DATA']['Experience']['General']['Years'],'год','года', 'лет').' '.$pluralForm($arResult['PERSONAL_DATA']['Experience']['General']['Months'],'месяц','месяца', 'месяцев').' '.$pluralForm($arResult['PERSONAL_DATA']['Experience']['General']['Days'],'день','дня', 'дней')?></td>
								</tr>
							<?endif;?>
						<?}?>
						
						<?
						if(isset($arResult['PERSONAL_DATA']['Experience']['State']['Years'])){
							$arResult['PERSONAL_DATA']['Experience']['State']['Years'] = trim($arResult['PERSONAL_DATA']['Experience']['State']['Years']);
							if(!empty($arResult['PERSONAL_DATA']['Experience']['State']['Years'])):
						?>
							<tr>
								<td  class="user-profile-nowrap">Стаж за выслугу лет:</td>
								<td><?=$pluralForm($arResult['PERSONAL_DATA']['Experience']['State']['Years'],'год','года', 'лет').' '.$pluralForm($arResult['PERSONAL_DATA']['Experience']['State']['Months'],'месяц','месяца', 'месяцев').' '.$pluralForm($arResult['PERSONAL_DATA']['Experience']['State']['Days'],'день','дня', 'дней')?></td>
							</tr>
							<?endif;?>
						<?}?>
						<?if(!empty($arResult['HAS_PAYSLIP'])){?>
							<tr>
							<td class="user-profile-block-title">Расчетный листок:</td>

							<td class="user-profile-block-title">
								<button class="user-profile-link" onclick="raschetny_l_pp();">Запросить</button>
								<div style="display:none">
									<div id="raschetny_l_pp_wind_cont">
										<form method="GET" target="_blank" action="<?=SITE_DIR?>raschetnyy-listok/">
											<div style="margin-bottom:10px"><strong>Укажите период:</strong></div>
											<div style="margin-bottom:5px">Начало:<br/><input type="text" name="DateS" value="<?=date('d.m.Y', strtotime('first day of previous month'))?>" onclick="BX.calendar({node: this, field: this, bTime: false});" required></div>
											<div style="margin-bottom:5px">Конец:<br/><input type="text" name="DateE" value="<?=date('d.m.Y', strtotime('last day of previous month'))?>" onclick="BX.calendar({node: this, field: this, bTime: false});" required></div>
											<?=bitrix_sessid_post()?>
											</form>
										</div>
									</div>
								</div>
							</td>
						</tr>
						<?}?>
						<tr>
							<td class="user-profile-block-title" colspan="2">Информация об отпусках:</td>
						</tr>
						<?if(is_array($arResult['PERSONAL_DATA']['VacationList']['VacationScheduleRecord'])){?>
							<tr>
								<td class="user-profile-status-icon">График отпусков:</td>
								<td>
									<?
									if(is_array($arResult['PERSONAL_DATA']['VacationList']['VacationScheduleRecord'][0])){
										$arVacationList=$arResult['PERSONAL_DATA']['VacationList']['VacationScheduleRecord'];
									}else{
										$arVacationList=$arResult['PERSONAL_DATA']['VacationList'];
									}
									foreach ($arVacationList as $arVacation):?>
										<?=date('d.m.Y', strtotime($arVacation['DateStart']))?> / <?=$arVacation['DaysCount']?><br>
									<? endforeach; ?>
								</td>
							</tr>
						<?}?>

						<tr>
							<td  class="user-profile-status-icon">Количество положенных дней отпуска:</td>
							<td><?=$pluralForm($arResult['PERSONAL_DATA']['Vacation']['DaysCount'],'день','дня','дней');?></td>
						</tr>
						<tr>
							<td  class="user-profile-status-icon">Использовано:</td>
							<td><?=$pluralForm($arResult['PERSONAL_DATA']['Vacation']['DaysUsed'],'день','дня','дней');?> за период <?=date('d.m.Y', strtotime($arResult['PERSONAL_DATA']['Vacation']["WorkingPeriod"]['DateStart']))?> - <?=date('d.m.Y', strtotime($arResult['PERSONAL_DATA']['Vacation']["WorkingPeriod"]['DateEnd']))?></td>
						</tr>
						<tr>
							<td  class="user-profile-status-icon">Необходимо использовать:</td>
							<td><?=$pluralForm(intval($arResult['PERSONAL_DATA']['Vacation']['DaysCount']-$arResult['PERSONAL_DATA']['Vacation']['DaysUsed']),'день','дня','дней');?> до  <?=date('d.m.Y', strtotime($arResult['PERSONAL_DATA']['Vacation']["WorkingPeriod"]['DateEnd']))?></td>
						</tr>
						<?if(count($arResult['PERSONAL_DATA']['VacationLeftovers'])>0){?>
							<tr>
								<td class="user-profile-status-icon">Остатки неиспользованного отпуска:</td>
								<td>
									<?
									foreach ($arResult['PERSONAL_DATA']['VacationLeftovers'] as $arVacation):?>
										<?=$arVacation['WorkingPeriod']['Representation']?> / <?=$pluralForm(intval($arVacation['WorkingPeriod']['NotUsed']),'день','дня','дней');?><br>
									<? endforeach; ?>
								</td>
							</tr>
						<?}?>
						<?if(is_array($arResult['PERSONAL_DATA']['Awards']['Award'])){?>
							<tr>
								<td class="user-profile-block-title" colspan="2">Мои Награды:</td>
							</tr>
							<?
							if(is_array($arResult['PERSONAL_DATA']['Awards']['Award'][0])){
								$arAwardList=$arResult['PERSONAL_DATA']['Awards']['Award'];
							}else{
								$arAwardList=$arResult['PERSONAL_DATA']['Awards'];
							}
							foreach ($arAwardList as $arAward):?>
								<tr>
									<td class="user-profile-status-icon" colspan="2"><?=$arAward['Name']?></td>
								</tr>
								<?if($arAward['OrderNumber']!=''){?>
									<tr>
										<td  class="user-profile-nowrap">Номер приказа:</td>
										<td><?=$arAward['OrderNumber']?></td>
									</tr>
								<?}?>
								<?if($arAward['OrderDate']!=''){?>
									<tr>
										<td  class="user-profile-nowrap">Дата приказа:</td>
										<td><?=$arAward['OrderDate']?></td>
									</tr>
								<?}?>
								<?if($arAward['DocType']!=''){?>
									<tr>
										<td  class="user-profile-nowrap">Вид документа:</td>
										<td><?=$arAward['DocType']?></td>
									</tr>
								<?}?>
								<?if($arAward['CertificateNumber']!=''){?>
									<tr>
										<td  class="user-profile-nowrap">Номер удостоверения:</td>
										<td><?=$arAward['CertificateNumber']?></td>
									</tr>
								<?}?>
								<?if($arAward['AwardNumber']!=''){?>
									<tr>
										<td  class="user-profile-nowrap">Номер награды:</td>
										<td><?=$arAward['AwardNumber']?></td>
									</tr>
								<?}?>
							<?endforeach;?>
						<?}?>
					</table>
				</div>
			<?}?>
			<div id="process" style="display: none;">
				<?
					$APPLICATION->IncludeComponent("bitrix:lists.lists", "bp_users_page", array(
						"IBLOCK_TYPE_ID" => "bitrix_processes",
						"CACHE_TYPE" => "N",
						"SET_TITLE" => "N",
						'USER_ID'=>$arParams['ID'],
						"CACHE_TIME" => "3600",
						"LINE_ELEMENT_COUNT" => "3",
						'PROCEED_URL'=>SITE_DIR."bizproc/processes/?livefeed=y&list_id=#IBLOCK_ID#&element_id=0",
						'BP_S'=> []
						),
						$component,
						array("HIDE_ICONS" => "Y")
					);
				?>
			</div>
		</div>
	</div>
</td>
=======
<?

if(!$arResult['CAN_EDIT_USER']||$_REQUEST['debug']=='Y') return;

$pluralForm = function($n, $form1, $form2, $form5) {
    $n = abs(intval($n)) % 100;
    $n1 = $n % 10;
    if ($n > 10 && $n < 20) return $n.' '.$form5;
    if ($n1 > 1 && $n1 < 5) return $n.' '.$form2;
    if ($n1 == 1) return $n.' '.$form1;
    return $n.' '.$form5;
};
?>
<tr>
<td colspan="2">
	<div id="tabs">
		<ul>
			<li class="active"><a href="#info">Общая информация</a></li>
			<?if(!empty($arResult['PERSONAL_DATA'])):?>
				<li><a href="#personal">Личные Данные</a></li>
				<li><a href="#career">Моя карьера</a></li>
			<?endif;?>
			<li><a href="#process">Бизнес-процессы</a></li>
		</ul>
		<div class="tabs">
			<div id="info">
				<table class="user-profile-block" cellspacing="0">
				<tr>
					<td class="user-profile-block-title" colspan="2"><?=GetMessage("SONET_CONTACT_TITLE")?></td>
				</tr>
				<?
				if (is_array($arResult["UserFieldsContact"]["DATA"]))
				{
					foreach ($arResult["UserFieldsContact"]["DATA"] as $field => $arUserField)
					{
						if (
							is_array($arUserField["VALUE"]) && count($arUserField["VALUE"]) > 0
							|| !is_array($arUserField["VALUE"]) && $arUserField["VALUE"] != ''
						)
						{
							?><tr>
								<td class="user-profile-nowrap"><?=$arUserField["NAME"].":"?></td>
								<td class="user-profile-nowrap-second"><?
									switch ($field)
									{
										case "PERSONAL_MOBILE":
										case "WORK_PHONE":
										case "PERSONAL_PHONE":
											echo $arUserField["VALUE"];
											if (CModule::IncludeModule('voximplant'))
											{
												$userPermissions = \Bitrix\Voximplant\Security\Permissions::createWithCurrentUser();
												if(CVoxImplantMain::Enable($arResult["User"][$field]) &&
												$userPermissions->canPerform(
													\Bitrix\Voximplant\Security\Permissions::ENTITY_CALL,
													\Bitrix\Voximplant\Security\Permissions::ACTION_PERFORM,
													\Bitrix\Voximplant\Security\Permissions::PERMISSION_CALL_USERS
												))
												{
													?>
													<span class="sonet_call_btn" onclick="BXIM.phoneTo('<?=CUtil::JSEscape($arResult["User"][$field])?>');"></span>
													<?
												}
											}
											break;
										default:
											echo $arUserField["VALUE"];
									}
								?></td>
							</tr><?
						}
					}
				}

				if (is_array($arResult["UserPropertiesContact"]["DATA"]))
				{
					foreach ($arResult["UserPropertiesContact"]["DATA"] as $field => $arUserField)
					{
						if (
							is_array($arUserField["VALUE"]) && count($arUserField["VALUE"]) > 0
							|| !is_array($arUserField["VALUE"]) && $arUserField["VALUE"] != ''
						)
						{
							?><tr>
								<td class="user-profile-nowrap"><?=$arUserField["EDIT_FORM_LABEL"].":"?></td>
								<td class="user-profile-nowrap"><?
									$value = htmlspecialcharsbx($arUserField["VALUE"]);
									switch ($field)
									{
										case "UF_FACEBOOK":
										case "UF_LINKEDIN":
										case "UF_XING":
											$href = (!preg_match('#^https?://#i', trim($value)) ? 'http://' : '').trim($value); ?>
											<a href="<?=$href?>"><?=$value?></a>
											<?break;
										case "UF_TWITTER":?>
											<a href="http://twitter.com/<?=$value?>"><?=$value?></a><?
											break;
										case "UF_SKYPE":?>
											<a href="callto:<?=$value?>"><?=$value?></a><?
											break;
										default:
											$GLOBALS["APPLICATION"]->IncludeComponent(
												"bitrix:system.field.view",
												$arUserField["USER_TYPE"]["USER_TYPE_ID"],
												array("arUserField" => $arUserField, "inChain" => "N"),
												null,
												array("HIDE_ICONS"=>"Y")
											);
									}
								?></td>
							</tr><?
						}
					}
				}
				?>
	<!--otp-->
				<?
				if (
					$arResult["User"]["OTP"]["IS_ENABLED"] !== "N"
					&&
					(
						$USER->GetID() == $arResult["User"]["ID"]
						|| $USER->CanDoOperation('security_edit_user_otp')
					)
					&&
					(
						$arResult["User"]["OTP"]["IS_MANDATORY"]
						|| !$arResult["User"]["OTP"]["IS_MANDATORY"] && $arResult["User"]["OTP"]["IS_EXIST"]
					)
				)
				{
					?><tr>
						<td class="user-profile-block-title"><?=GetMessage("SONET_SECURITY")?></td>
					</tr>
					<tr>
						<td class="user-profile-nowrap"><?=GetMessage("SONET_OTP_AUTH")?></td>
						<td><?
							if ($arResult["User"]["OTP"]["IS_ACTIVE"])
							{
								?>
									<span class="user-profile-otp-on" style="margin-right: 15px"><?=GetMessage("SONET_OTP_ACTIVE")?></span>

									<?if ($USER->CanDoOperation('security_edit_user_otp') || !$arResult["User"]["OTP"]["IS_MANDATORY"]):?>
										<a class="user-profile-otp-link-blue" href="javascript:void(0)" onclick="socnetUserProfileObj.showOtpDaysPopup(this, '<?=CUtil::JSEscape($arResult["User"]["ID"])?>', 'deactivate')"><?=GetMessage("SONET_OTP_DEACTIVATE")?></a>
									<?endif?>

									<?if ($USER->GetID() == $arResult["User"]["ID"]):?>
										<a class="user-profile-otp-link-blue" href="<?=$arResult["Urls"]["Security"]?>"><?=GetMessage("SONET_OTP_CHANGE_PHONE")?></a>
									<?endif?>
								<?
							}
							elseif (
								!$arResult["User"]["OTP"]["IS_ACTIVE"]
								&& $arResult["User"]["OTP"]["IS_MANDATORY"]
							)
							{
								?><span class="user-profile-otp-off" style="margin-right: 15px"><?=($arResult["User"]["OTP"]["IS_EXIST"]) ? GetMessage("SONET_OTP_NOT_ACTIVE") : GetMessage("SONET_OTP_NOT_EXIST")?></span><?

								if ($arResult["User"]["OTP"]["IS_EXIST"])
								{
									?><a class="user-profile-otp-link-blue" href="javascript:void(0)" onclick="socnetUserProfileObj.activateUserOtp('<?=CUtil::JSEscape($arResult["User"]["ID"])?>')"><?=GetMessage("SONET_OTP_ACTIVATE")?></a><?
									if ($USER->GetID() == $arResult["User"]["ID"])
									{
										?><a class="user-profile-otp-link-blue" href="<?=$arResult["Urls"]["Security"]?>"><?=GetMessage("SONET_OTP_CHANGE_PHONE")?></a><?
									}
								}
								else
								{
									if ($USER->GetID() == $arResult["User"]["ID"])
									{
										?><a class="user-profile-otp-link-blue" href="<?=$arResult["Urls"]["Security"]?>"><?=GetMessage("SONET_OTP_SETUP")?></a><?
									}
									else
									{
										?><a class="user-profile-otp-link-blue" href="javascript:void(0)" onclick="socnetUserProfileObj.showOtpDaysPopup(this, '<?=CUtil::JSEscape($arResult["User"]["ID"])?>', 'defer')"><?
											?><?=GetMessage("SONET_OTP_PROROGUE")?><?
										?></a><?
									}
								}

								if ($arResult["User"]["OTP"]["NUM_LEFT_DAYS"])
								{
									?><span class="user-profile-otp-days"><?=GetMessage("SONET_OTP_LEFT_DAYS", array("#NUM#" => "<strong>".$arResult["User"]["OTP"]["NUM_LEFT_DAYS"]."</strong>"))?></span><?
								}
							}
							elseif (
								!$arResult["User"]["OTP"]["IS_ACTIVE"]
								&& $arResult["User"]["OTP"]["IS_EXIST"]
								&& !$arResult["User"]["OTP"]["IS_MANDATORY"]
							)
							{
								?><span class="user-profile-otp-off" style="margin-right: 15px"><?=GetMessage("SONET_OTP_NOT_ACTIVE")?></span>
								<a class="user-profile-otp-link-blue" href="javascript:void(0)" onclick="socnetUserProfileObj.activateUserOtp('<?=CUtil::JSEscape($arResult["User"]["ID"])?>')"><?=GetMessage("SONET_OTP_ACTIVATE")?></a><?
								if ($USER->GetID() == $arResult["User"]["ID"])
								{
									?><a class="user-profile-otp-link-blue" href="<?=$arResult["Urls"]["Security"]?>"><?=GetMessage("SONET_OTP_CHANGE_PHONE")?></a><?
								}

								if ($arResult["User"]["OTP"]["NUM_LEFT_DAYS"])
								{
									?><span class="user-profile-otp-days"><?=GetMessage("SONET_OTP_LEFT_DAYS", array("#NUM#" => "<strong>".$arResult["User"]["OTP"]["NUM_LEFT_DAYS"]."</strong>"))?></span><?
								}
							}
						?></td>
					</tr>
					<!-- passwords --><?
					if ($USER->GetID() == $arResult["User"]["ID"])
					{
						?><tr>
							<td class="user-profile-nowrap"><?=GetMessage("SONET_PASSWORDS")?></td>
							<td>
								<a href="<?=$arResult["Urls"]["Passwords"]?>"><?=GetMessage("SONET_PASSWORDS_SETTINGS")?></a>
							</td>
						</tr><?
					}
					?><!-- codes --><?
					if (
						$USER->GetID() == $arResult["User"]["ID"]
						&& $arResult["User"]["OTP"]["IS_ACTIVE"]
						&& $arResult["User"]["OTP"]["ARE_RECOVERY_CODES_ENABLED"]
					)
					{
						?><tr>
							<td class="user-profile-nowrap"><?=GetMessage("SONET_OTP_CODES")?></td>
							<td>
								<a href="<?=$arResult["Urls"]["Codes"]?>"><?=GetMessage("SONET_OTP_CODES_SHOW")?></a>
							</td>
						</tr><?
						?><tr><td><br/><br/></td></tr><?
					}
				}
				?>
	<!-- // otp -->
				<tr>
					<td class="user-profile-block-title" colspan="2"><?=GetMessage("SONET_COMMON_TITLE")?></td>
				</tr>
				<tr>
					<td class="user-profile-nowrap""><?=GetMessage("SONET_USER_STATUS").":"?></td>
					<td>
						<? $onlineStatus = CUser::GetOnlineStatus($arUser['ID'], MakeTimeStamp($arUser["LAST_ACTIVITY_DATE"], "YYYY-MM-DD HH-MI-SS")); ?>
						<span class="user-profile-status-icon user-profile-status-icon-<?=$onlineStatus['STATUS']?>"><?=$onlineStatus['STATUS_TEXT']?></span><?
						if($onlineStatus['STATUS'] == 'idle'):
							echo ($onlineStatus['LAST_SEEN_TEXT']? ", ".GetMessage('SONET_LAST_SEEN_IDLE_'.($arUser["PERSONAL_GENDER"] == 'F'? 'F': 'M'), Array('#LAST_SEEN#' => $onlineStatus['LAST_SEEN_TEXT'])): '');
						else:
							echo ($onlineStatus['LAST_SEEN_TEXT']? ", ".GetMessage('SONET_LAST_SEEN_'.($arUser["PERSONAL_GENDER"] == 'F'? 'F': 'M'), Array('#LAST_SEEN#' => $onlineStatus['LAST_SEEN_TEXT'])): '');
						endif;
						?>
						<?if (!in_array($arUser['ACTIVITY_STATUS'], array('active', 'email'))):?>
							<div class="user-activity-status">
								<span class="employee-dept-post employee-dept-<?=$arUser["ACTIVITY_STATUS"]?>"><?=GetMessage("SONET_USER_".$arUser["ACTIVITY_STATUS"])?></span>
							</div>
						<?endif;?>
					</td>
				</tr>
				<?
				if (is_array($arResult["UserFieldsMain"]["DATA"]))
				{
					foreach ($arResult["UserFieldsMain"]["DATA"] as $field => $arUserField)
					{
						if (in_array($field, Array('LAST_ACTIVITY_DATE', 'LAST_LOGIN')))
						{
							continue;
						}
						if (
							is_array($arUserField["VALUE"]) && count($arUserField["VALUE"]) > 0
							|| !is_array($arUserField["VALUE"]) && $arUserField["VALUE"] != ''
						)
						{
							?><tr>
								<td class="user-profile-nowrap"><?=$arUserField["NAME"].":"?></td>
								<td><?=$arUserField["VALUE"];?></td>
							</tr><?
						}
					}
				}

				if (is_array($arResult["UserPropertiesMain"]["DATA"]))
				{
					foreach ($arResult["UserPropertiesMain"]["DATA"] as $field => $arUserField){
						if (
							(
								is_array($arUserField["VALUE"]) && count($arUserField["VALUE"]) > 0
								|| !is_array($arUserField["VALUE"]) && $arUserField["VALUE"] != ''
							)
							&& (
								$field != "UF_DEPARTMENT"
								|| (
									is_array($arUserField["VALUE"])
									&& $arUserField["VALUE"][0] > 0
								)
							)
						)
						{
							?><tr>
								<td class="user-profile-nowrap"><?=$arUserField["EDIT_FORM_LABEL"].":"?></td>
								<td><?
									$bInChain = ($field == "UF_DEPARTMENT" ? "Y" : "N");
									$GLOBALS["APPLICATION"]->IncludeComponent(
										"bitrix:system.field.view",
										$arUserField["USER_TYPE"]["USER_TYPE_ID"],
										array("arUserField" => $arUserField, "inChain" => $bInChain),
										null,
										array("HIDE_ICONS"=>"Y")
									);
								?></td>
							</tr><?
						}
					}
				}

				if (is_array($arResult['MANAGERS']) && count($arResult['MANAGERS'])>0)
				{
					?><tr>
						<td class="user-profile-nowrap"><?=GetMessage("SONET_MANAGERS").":"?></td>
						<td><?
							$bFirst = true;
							foreach ($arResult['MANAGERS'] as $id => $sub_user)
							{
								if (!$bFirst) echo ', '; else $bFirst = false;
								$name = CUser::FormatName($arParams['NAME_TEMPLATE'], $sub_user, true, false);
								?><a class="user-profile-link" href="<?=CComponentEngine::MakePathFromTemplate($arParams['PATH_TO_USER'], array("user_id" => $sub_user["ID"]))?>"><?=$name?></a><?
								if ($sub_user["WORK_POSITION"] != '') echo " (".$sub_user["WORK_POSITION"].")";?><?
							}
						?></td>
					</tr><?
				}

				if (is_array($arResult['SUBORDINATE']) && count($arResult['SUBORDINATE'])>0)
				{
					?><tr>
						<td class="user-profile-nowrap"><?=GetMessage("SONET_SUBORDINATE").":"?></td>
						<td><?
							$bFirst = true;
							foreach ($arResult['SUBORDINATE'] as $id => $sub_user)
							{
								if (!$bFirst) echo ', '; else $bFirst = false;
								$name = CUser::FormatName($arParams['NAME_TEMPLATE'], $sub_user, true, false);
								?><a class="user-profile-link" href="<?=CComponentEngine::MakePathFromTemplate($arParams['PATH_TO_USER'], array("user_id" => $sub_user["ID"]))?>"><?=$name?></a><?
								if ($sub_user["WORK_POSITION"] != '') echo " (".$sub_user["WORK_POSITION"].")";?><?
							}
						?></td>
					</tr><?
				}?>
				<?
				if(!empty($arResult['PERSONAL_DATA_DateOfBirth'])): ?>
					<tr>
						<td class="user-profile-nowrap">Дата рождения:</td>
						<td>
							<div style="margin-bottom:5px;"><?=FormatDate("j F", MakeTimeStamp($arResult['PERSONAL_DATA_DateOfBirth']))?></div>
							<label style="line-height: 12px;">
								<input type="checkbox" onchange="jQuery.post(location.pathname, {DateOfBirthHide:this.checked?0:1})" style="margin: 0;vertical-align: top;" <?=(empty($arResult['User']['UF_DATEOFBIRTHHIDE'])?"checked":"")?>> <span style="vertical-align: top;">Отображать на странице <a target="_blank" href="<?=SITE_DIR?>company/birthdays.php">"Дни рождения"</a></span>
							</label>
						</td>
					</tr>
				<?endif;?>
				<?
				if (!empty($arResult["User"]["EMAIL_FORWARD_TO"]))
				{?>
					<tr>
						<td class="user-profile-block-title" colspan="2">
							<?=GetMessage("SONET_EMAIL_FORWARD_TO")?>
							<span class="user-profile-email-help" id="user-profile-email-help" data-text="<?=htmlspecialcharsbx(GetMessage("SONET_EMAIL_FORWARD_TO_HINT"))?>">?</span>
						</td>
					</tr><?
					if (!empty($arResult["User"]["EMAIL_FORWARD_TO"]['BLOG_POST'])){
						?><tr>
							<td class="user-profile-mail-link"><?=GetMessage("SONET_EMAIL_FORWARD_TO_BLOG_POST").":"?></td>
							<td class="user-profile-block-right user-profile-mail-link" >
								<div class="user-profile-mail-link-block">
									<span class="user-profile-short-link" data-link=""><?=$arResult["User"]["EMAIL_FORWARD_TO"]['BLOG_POST']?></span>
									<input type="text" class="user-profile-link-input" data-input="" value="<?=$arResult["User"]["EMAIL_FORWARD_TO"]['BLOG_POST']?>">
									<span onclick="socnetUserProfileObj.showLink(this);" class="user-profile-link user-profile-show-link-btn"><?=GetMessage("SONET_EMAIL_FORWARD_TO_SHOW")?></span>
								</div>
							</td>
						</tr><?
					}
					if (!empty($arResult["User"]["EMAIL_FORWARD_TO"]['TASKS_TASK'])){
						?><tr>
						<td class="user-profile-mail-link"><?=GetMessage("SONET_EMAIL_FORWARD_TO_TASK").":"?></td>
						<td class="user-profile-block-right user-profile-mail-link" >
							<div class="user-profile-mail-link-block">
								<span class="user-profile-short-link" data-link=""><?=$arResult["User"]["EMAIL_FORWARD_TO"]['TASKS_TASK']?></span>
								<input type="text" class="user-profile-link-input" data-input="" value="<?=$arResult["User"]["EMAIL_FORWARD_TO"]['TASKS_TASK']?>">
								<span onclick="socnetUserProfileObj.showLink(this);" class="user-profile-link user-profile-show-link-btn"><?=GetMessage("SONET_EMAIL_FORWARD_TO_SHOW")?></span>
							</div>
						</td>
						</tr><?
					}
				}

				$additional = "";

				if (is_array($arResult["UserFieldsPersonal"]["DATA"]))
				{
					foreach ($arResult["UserFieldsPersonal"]["DATA"] as $field => $arUserField)
					{
						if(in_array($field, ['PERSONAL_BIRTHDAY', 'PERSONAL_GENDER'])) continue;
						if (is_array($arUserField["VALUE"]) && count($arUserField["VALUE"]) > 0 || !is_array($arUserField["VALUE"]) && $arUserField["VALUE"] != '')
						{
							$additional .= '<tr>
								<td class="user-profile-nowrap">'.$arUserField["NAME"].':</td>
								<td>'.$arUserField["VALUE"].'</td></tr>';
						}
					}
				}

				if (is_array($arResult["UserFieldsPersonal"]["DATA"]))
				{
					foreach ($arResult["UserPropertiesPersonal"]["DATA"] as $field => $arUserField)
					{
						if (is_array($arUserField["VALUE"]) && count($arUserField["VALUE"]) > 0 || !is_array($arUserField["VALUE"]) && $arUserField["VALUE"] != '')
						{
							$additional .= '<tr><td class="user-profile-nowrap">'.$arUserField["EDIT_FORM_LABEL"].':</td><td>';

							ob_start();
							$GLOBALS["APPLICATION"]->IncludeComponent(
								"bitrix:system.field.view",
								$arUserField["USER_TYPE"]["USER_TYPE_ID"],
								array("arUserField" => $arUserField, "inChain" => $field == "UF_DEPARTMENT" ? "Y" : "N"),
								null,
								array("HIDE_ICONS"=>"Y")
							);
							$additional .= ob_get_contents();
							ob_end_clean();

							$additional .= '</td></tr>';
						}
					}
				}

				if (is_array($arResult["Groups"]["List"]) && count($arResult["Groups"]["List"]) > 0)
				{
					$additional .= '<tr><td class="user-profile-nowrap">'.GetMessage("SONET_GROUPS").':</td><td>';
					$bFirst = true;
					foreach ($arResult["Groups"]["List"] as $key => $group)
					{
							if (!$bFirst)
								$additional .= ', ';
							$bFirst = false;
							$additional .= '<a class="user-profile-link" href="'.$group["GROUP_URL"].'">'.$group["GROUP_NAME"].'</a>';
					}
					$additional .= '</td></tr>';
				}
				?>

				<?if ($additional != ''):?>
					<tr>
						<td class="user-profile-block-title" colspan="2"><?=GetMessage("SONET_ADDITIONAL_TITLE")?></td>
					</tr>
					<?=$additional?>
				<?endif;?>
				<?if($bNetwork && IsModuleInstalled('socialservices') && $USER->GetID() == $arUser["ID"] && \Bitrix\Main\Config\Option::get('socialservices', 'network_last_update_check', 0) > 0):?>
					<tr>
						<td class="user-profile-block-title" colspan="2"><?=GetMessage("SONET_SOCSERV_CONTACTS")?></td>
					</tr>
					<tr>
						<td colspan="2">
							<?
								$APPLICATION->IncludeComponent(
									'bitrix:socserv.contacts',
									'',
									array(
										'USER_ID' => $arUser["ID"],
									),
									$component,
									array("HIDE_ICONS" => "Y")
								);
							?>
						</td>
					</tr>
				<? endif; ?>
				
				</table>
			</div>
			<?if(!empty($arResult['PERSONAL_DATA'])){?>
				<div id="personal" style="display: none;">
					<table class="user-profile-block" cellspacing="0">
						<?if(is_array($arResult['PERSONAL_DATA']['Passport'])){?>
							<tr>
								<td class="user-profile-block-title" colspan="2">Паспортные данные</td>
							</tr>
							<tr>
								<td class="user-profile-nowrap">Серия:</td>
								<td><?=$arResult['PERSONAL_DATA']['Passport']['Series']?></td>
							</tr>
							<tr>
								<td class="user-profile-nowrap">Номер:</td>
								<td><?=$arResult['PERSONAL_DATA']['Passport']['Number']?></td>
							</tr>
							<tr>
								<td class="user-profile-nowrap">Кем выдан:</td>
								<td><?=$arResult['PERSONAL_DATA']['Passport']['IssuedBy']?></td>
							</tr>
							<tr>
								<td class="user-profile-nowrap">Дата выдачи:</td>
								<td><?=$arResult['PERSONAL_DATA']['Passport']['DateOfIssue']?></td>
							</tr>
							<tr>
								<td class="user-profile-nowrap">Код подразделения:</td>
								<td><?=$arResult['PERSONAL_DATA']['Passport']['DivisionCode']?></td>
							</tr>
						<?}?>
						<tr>
							<td class="user-profile-status-icon">Адрес по прописке:</td>
							<td><?=$arResult['PERSONAL_DATA']['AddressOfRegistration']?></td>
						</tr>
						<tr>
							<td class="user-profile-status-icon">Адрес места проживания:</td>
							<td><?=$arResult['PERSONAL_DATA']['AddressOfResidence']?></td>
						</tr>
						<?if(is_array($arResult['PERSONAL_DATA']['EducationList'])){?>
							<tr>
								<td class="user-profile-block-title" colspan="2">Образование</td>
							</tr>
							<?
							if(is_array($arResult['PERSONAL_DATA']['EducationList']['Education'][0])){
								$arEducationList=$arResult['PERSONAL_DATA']['EducationList']['Education'];
							}else{
								$arEducationList=$arResult['PERSONAL_DATA']['EducationList'];
							}

							foreach ($arEducationList as $arEducation) :?>
								<tr>
								<td  class="user-profile-nowrap">Уровень образования:</td>
								<td><?=$arEducation['Type']?></td>
							</tr>
							<tr>
								<td  class="user-profile-nowrap">Специальность по диплому:</td>
								<td><?=$arEducation['Speciality']?></td>
							</tr>
							<tr>
								<td  class="user-profile-nowrap">Образовательное учреждение:</td>
								<td><?=$arEducation['Iinstitution']?></td>
							</tr>
							<tr>
								<td  class="user-profile-nowrap">Год окончания обучения:</td>
								<td><?=$arEducation['YearOfEnd']?></td>
							</tr>
							<? endforeach; ?>
						<?}?>
						<?if(false && is_array($arResult['PERSONAL_DATA']['Relatives']['Relative'])){?>
							<?
							if(is_array($arResult['PERSONAL_DATA']['Relatives']['Relative'][0])){
								$arRelativeList=$arResult['PERSONAL_DATA']['Relatives']['Relative'];
							}else{
								$arRelativeList=$arResult['PERSONAL_DATA']['Relatives'];
							}
							foreach ($arRelativeList as $arRelative) :?>
								<tr>
									<td class="user-profile-block-title" colspan="2">Данные о родственнике сотрудника:</td>
								</tr>
								<tr>
									<td  class="user-profile-nowrap">Тип родственной связи:</td>
									<td><?=$arRelative['Kinship']?></td>
								</tr>
								<tr>
									<td  class="user-profile-nowrap">ФИО:</td>
									<td><?=$arRelative['Name']?></td>
								</tr>
								<tr>
									<td  class="user-profile-nowrap">Дата рождения:</td>
									<td><?=$arRelative['DateOfBirth']?></td>
								</tr>
								<?if($arRelative['DateOfMarriage']!=''):?>
									<tr>
										<td  class="user-profile-nowrap">Дата заключения брака:</td>
										<td><?=$arRelative['DateOfMarriage']?></td>
									</tr>
								<?endif;?>
								<?if($arRelative['PlaceOfWork']!=''):?>
									<tr>
										<td  class="user-profile-nowrap">Место работы:</td>
										<td><?=$arRelative['PlaceOfWork']?></td>
									</tr>
								<?endif;?>
								<?if($arRelative['AddressOfResidence']!=''):?>
									<tr>
										<td  class="user-profile-nowrap">Место проживания:</td>
										<td><?=$arRelative['AddressOfResidence']?></td>
									</tr>
								<?endif;?>
							<?endforeach;?>
						<?}?>
					</table>
					<div style="padding:25px 0;">
						<a href="<?=SITE_DIR?>bizproc/processes/?livefeed=y&list_id=530&element_id=0" class="bx-notifier-item-button bx-notifier-item-button-confirm bx-notifier-item-button-accept">Изменить данные</a>
					</div>
				</div>
				<div id="career" style="display: none;">
					<table class="user-profile-block" cellspacing="0">
						<tr>
							<td class="user-profile-block-title" colspan="2">Карьера</td>
						</tr>
						<?
						if(isset($arResult['PERSONAL_DATA']['OrderRank'])){
							$arResult['PERSONAL_DATA']['OrderRank'] = trim($arResult['PERSONAL_DATA']['OrderRank']);
							if($arResult['PERSONAL_DATA']['OrderRank']):
							?>
								<tr>
									<td class="user-profile-nowrap">Классный чин:</td>
									<td><?=$arResult['PERSONAL_DATA']['OrderRank']?></td>
								</tr>
							<?endif;?>
						<?}?>
						<?
						if(isset($arResult['PERSONAL_DATA']['Experience']['General']['Years'])){
							$arResult['PERSONAL_DATA']['Experience']['General']['Years'] = trim($arResult['PERSONAL_DATA']['Experience']['General']['Years']);
							if(!empty($arResult['PERSONAL_DATA']['Experience']['General']['Years'])):?>
								<tr>
									<td  class="user-profile-nowrap">Общий трудовой стаж:</td>
									<td><?=$pluralForm($arResult['PERSONAL_DATA']['Experience']['General']['Years'],'год','года', 'лет').' '.$pluralForm($arResult['PERSONAL_DATA']['Experience']['General']['Months'],'месяц','месяца', 'месяцев').' '.$pluralForm($arResult['PERSONAL_DATA']['Experience']['General']['Days'],'день','дня', 'дней')?></td>
								</tr>
							<?endif;?>
						<?}?>
						
						<?
						if(isset($arResult['PERSONAL_DATA']['Experience']['State']['Years'])){
							$arResult['PERSONAL_DATA']['Experience']['State']['Years'] = trim($arResult['PERSONAL_DATA']['Experience']['State']['Years']);
							if(!empty($arResult['PERSONAL_DATA']['Experience']['State']['Years'])):
						?>
							<tr>
								<td  class="user-profile-nowrap">Стаж за выслугу лет:</td>
								<td><?=$pluralForm($arResult['PERSONAL_DATA']['Experience']['State']['Years'],'год','года', 'лет').' '.$pluralForm($arResult['PERSONAL_DATA']['Experience']['State']['Months'],'месяц','месяца', 'месяцев').' '.$pluralForm($arResult['PERSONAL_DATA']['Experience']['State']['Days'],'день','дня', 'дней')?></td>
							</tr>
							<?endif;?>
						<?}?>
						<?if(!empty($arResult['HAS_PAYSLIP'])){?>
							<tr>
							<td class="user-profile-block-title">Расчетный листок:</td>

							<td class="user-profile-block-title">
								<button class="user-profile-link" onclick="raschetny_l_pp();">Запросить</button>
								<div style="display:none">
									<div id="raschetny_l_pp_wind_cont">
										<form method="GET" target="_blank" action="<?=SITE_DIR?>raschetnyy-listok/">
											<div style="margin-bottom:10px"><strong>Укажите период:</strong></div>
											<div style="margin-bottom:5px">Начало:<br/><input type="text" name="DateS" value="<?=date('d.m.Y', strtotime('first day of previous month'))?>" onclick="BX.calendar({node: this, field: this, bTime: false});" required></div>
											<div style="margin-bottom:5px">Конец:<br/><input type="text" name="DateE" value="<?=date('d.m.Y', strtotime('last day of previous month'))?>" onclick="BX.calendar({node: this, field: this, bTime: false});" required></div>
											<?=bitrix_sessid_post()?>
											</form>
										</div>
									</div>
								</div>
							</td>
						</tr>
						<?}?>
						<tr>
							<td class="user-profile-block-title" colspan="2">Информация об отпусках:</td>
						</tr>
						<?if(is_array($arResult['PERSONAL_DATA']['VacationList']['VacationScheduleRecord'])){?>
							<tr>
								<td class="user-profile-status-icon">График отпусков:</td>
								<td>
									<?
									if(is_array($arResult['PERSONAL_DATA']['VacationList']['VacationScheduleRecord'][0])){
										$arVacationList=$arResult['PERSONAL_DATA']['VacationList']['VacationScheduleRecord'];
									}else{
										$arVacationList=$arResult['PERSONAL_DATA']['VacationList'];
									}
									foreach ($arVacationList as $arVacation):?>
										<?=date('d.m.Y', strtotime($arVacation['DateStart']))?> / <?=$arVacation['DaysCount']?><br>
									<? endforeach; ?>
								</td>
							</tr>
						<?}?>

						<tr>
							<td  class="user-profile-status-icon">Количество положенных дней отпуска:</td>
							<td><?=$pluralForm($arResult['PERSONAL_DATA']['Vacation']['DaysCount'],'день','дня','дней');?></td>
						</tr>
						<tr>
							<td  class="user-profile-status-icon">Использовано:</td>
							<td><?=$pluralForm($arResult['PERSONAL_DATA']['Vacation']['DaysUsed'],'день','дня','дней');?> за период <?=date('d.m.Y', strtotime($arResult['PERSONAL_DATA']['Vacation']["WorkingPeriod"]['DateStart']))?> - <?=date('d.m.Y', strtotime($arResult['PERSONAL_DATA']['Vacation']["WorkingPeriod"]['DateEnd']))?></td>
						</tr>
						<tr>
							<td  class="user-profile-status-icon">Необходимо использовать:</td>
							<td><?=$pluralForm(intval($arResult['PERSONAL_DATA']['Vacation']['DaysCount']-$arResult['PERSONAL_DATA']['Vacation']['DaysUsed']),'день','дня','дней');?> до  <?=date('d.m.Y', strtotime($arResult['PERSONAL_DATA']['Vacation']["WorkingPeriod"]['DateEnd']))?></td>
						</tr>
						<?if(count($arResult['PERSONAL_DATA']['VacationLeftovers'])>0){?>
							<tr>
								<td class="user-profile-status-icon">Остатки неиспользованного отпуска:</td>
								<td>
									<?
									foreach ($arResult['PERSONAL_DATA']['VacationLeftovers'] as $arVacation):?>
										<?=$arVacation['WorkingPeriod']['Representation']?> / <?=$pluralForm(intval($arVacation['WorkingPeriod']['NotUsed']),'день','дня','дней');?><br>
									<? endforeach; ?>
								</td>
							</tr>
						<?}?>
						<?if(is_array($arResult['PERSONAL_DATA']['Awards']['Award'])){?>
							<tr>
								<td class="user-profile-block-title" colspan="2">Мои Награды:</td>
							</tr>
							<?
							if(is_array($arResult['PERSONAL_DATA']['Awards']['Award'][0])){
								$arAwardList=$arResult['PERSONAL_DATA']['Awards']['Award'];
							}else{
								$arAwardList=$arResult['PERSONAL_DATA']['Awards'];
							}
							foreach ($arAwardList as $arAward):?>
								<tr>
									<td class="user-profile-status-icon" colspan="2"><?=$arAward['Name']?></td>
								</tr>
								<?if($arAward['OrderNumber']!=''){?>
									<tr>
										<td  class="user-profile-nowrap">Номер приказа:</td>
										<td><?=$arAward['OrderNumber']?></td>
									</tr>
								<?}?>
								<?if($arAward['OrderDate']!=''){?>
									<tr>
										<td  class="user-profile-nowrap">Дата приказа:</td>
										<td><?=$arAward['OrderDate']?></td>
									</tr>
								<?}?>
								<?if($arAward['DocType']!=''){?>
									<tr>
										<td  class="user-profile-nowrap">Вид документа:</td>
										<td><?=$arAward['DocType']?></td>
									</tr>
								<?}?>
								<?if($arAward['CertificateNumber']!=''){?>
									<tr>
										<td  class="user-profile-nowrap">Номер удостоверения:</td>
										<td><?=$arAward['CertificateNumber']?></td>
									</tr>
								<?}?>
								<?if($arAward['AwardNumber']!=''){?>
									<tr>
										<td  class="user-profile-nowrap">Номер награды:</td>
										<td><?=$arAward['AwardNumber']?></td>
									</tr>
								<?}?>
							<?endforeach;?>
						<?}?>
					</table>
				</div>
			<?}?>
			<div id="process" style="display: none;">
				<?
					$APPLICATION->IncludeComponent("bitrix:lists.lists", "bp_users_page", array(
						"IBLOCK_TYPE_ID" => "bitrix_processes",
						"CACHE_TYPE" => "N",
						"SET_TITLE" => "N",
						'USER_ID'=>$arParams['ID'],
						"CACHE_TIME" => "3600",
						"LINE_ELEMENT_COUNT" => "3",
						'PROCEED_URL'=>SITE_DIR."bizproc/processes/?livefeed=y&list_id=#IBLOCK_ID#&element_id=0",
						'BP_S'=> []
						),
						$component,
						array("HIDE_ICONS" => "Y")
					);
				?>
			</div>
		</div>
	</div>
</td>
>>>>>>> e0a0eba79 (init)
</tr>