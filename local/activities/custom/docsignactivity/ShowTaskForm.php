<?
ob_start();
?>
<tr>
    <td class="bizproc-field-value" colspan="2">
        <p><?=nl2br($activity->Descr)?></p>
        <div class="docsign-form">
        <div class="docsign-form__status"><span></span></div>
<?
try {
    if (!$files) {
        throw new Exception("Файлы не найдены");
    }

    echo '<div class="docsign-actions">
            <button disabled type="button" id="docsign__sign-files" class="bp-button bp-button bp-button-accept">Подписать документы</button> <label><input type="checkbox" onchange="$(\'#docsign__sign-files\').prop(\'disabled\',!this.checked);"> Документы мною просмотрены перед их подписанием</label>
            <script>docsignInit();</script>
        </div>';
    echo '
        <div class="docsign-cryptoplugin">
            <div class="docsign-cryptoplugin__certs">
                <h3>Выберите сертификат:</h3>
                <div><select size="5"></select></div>
                <div><button type="button" class="bp-button bp-button-transparent">Обновить</button></div>
            </div>
        </div>
    ';
    echo '<div class="docsign-files">
            <ul>';
        foreach ($files as $prop_code => $prop_files) {
            foreach ($prop_files as $file) {
                $data_source = base64_encode(file_get_contents($file['source_path']));
                $value = file_get_contents($file['path']);
                if ($file['extension'] != "p7s") {
                    $value = base64_encode($value);
                }

                echo '<li>';
                echo '<input type="hidden" class="'.($file['signed']?"signed":"").'" data-type="'.$file['extension'].'" data-id="'.$file['id'].'" name="'.$prop_code.'[]" data-source="'.$data_source.'" value="'.$value.'">';
                echo '<a href="'.$file['url'].'" target="_blank" download="'.$file['name'].'">'.$file['name'].'</a>';
                echo '</li>';
                unset($data_source, $value);
            }
        }
    echo '</ul>
        </div>';
} catch (Exception $e) {
    echo '<div class="docsign-error">'.$e->getMessage().'</div>';
}
?>
        <style>
            .docsign-form__status{
                margin-bottom: 15px;
            }
        .bizproc-item-buttons .bp-button-transparent{
            display: none;
        }
        </style>
    </td>
</tr>
<?
$form = ob_get_clean();