<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(false);


if (!empty($arResult["ERRORS"])):?>
	<?ShowError(implode("<br />", $arResult["ERRORS"]))?>
<?endif;
if ($arResult["MESSAGE"] <> ''):?>
	<?ShowNote($arResult["MESSAGE"])?>
<?endif?>

<form name="iblock_add" action="<?=POST_FORM_ACTION_URI?>" method="post" enctype="multipart/form-data">
	<?=bitrix_sessid_post()?>
	<table class="data-table" style="width: 90%">

		<tbody>
				<tr>
					<td colspan="2">
						<p><?= $arResult['TITLE_FOR_FORM']?>:</p>
								<input type="text" name="title"><br />
					</td>
				</tr>
		</tbody>
		<tfoot width="100%">
			<tr>
				<td width="20%" colspan="1">
					<input type="submit" name="iblock_submit" value="Добавить" />
				</td>
				<td colspan="1">
					<input type="submit" name="iblock_delete" value="Удалить" />
				</td>
			</tr>
		</tfoot>
	</table>
</form>
