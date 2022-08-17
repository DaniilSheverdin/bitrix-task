<?

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) {
	die();
}

/** @var array $arCurrentValues */

?>
<?/*
<tr>
    <td align="right" width="40%">Отправлять на подпись в АСЭД Дело:</td>
    <td width="60%">
        <select name="send_to_ased">
            <option value="N"<?= $arCurrentValues["send_to_ased"] == "N" ? " selected" : "" ?>><?= GetMessage("BPAA_PD_NO") ?></option>
            <option value="Y"<?= $arCurrentValues["send_to_ased"] == "Y" ? " selected" : "" ?>><?= GetMessage("BPAA_PD_YES") ?></option>
        </select>
    </td>
</tr>
*/?>
<tr>
	<td align="right" width="40%" valign="top"><span class="adm-required-field">USERS:</span></td>
	<td width="60%">
		<?=CBPDocument::ShowParameterField("user", 'docsign_users', $arCurrentValues['docsign_users'], array('rows'=>'2'))?>
	</td>
</tr>
<tr>
	<td align="right" width="40%"><span class="adm-required-field">NAME:</span></td>
	<td width="60%">
		<?=CBPDocument::ShowParameterField("string", 'docsign_name', $arCurrentValues['docsign_name'], array('size'=>'50'))?>
	</td>
</tr>
<tr>
	<td align="right" width="40%"><span class="adm-required-field">PROPS:</span></td>
	<td width="60%">
		<?=CBPDocument::ShowParameterField("string", 'docsign_props', $arCurrentValues['docsign_props'], array('size'=>'50'))?>
	</td>
</tr>
<tr>
	<td align="right" width="40%"><span class="adm-required-field">DESCR:</span></td>
	<td width="60%">
		<?=CBPDocument::ShowParameterField("text", 'docsign_descr', $arCurrentValues['docsign_descr'], array('size'=>'500'))?>
	</td>
</tr>