<?php

namespace Citto\Filesigner;

use CFile;
use Exception;
use CIBlockElement;
use Bitrix\Main\Loader;
use Monolog\Handler\RotatingFileHandler;
use Sprint\Migration\Helpers\IblockHelper;

defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

require_once $_SERVER["DOCUMENT_ROOT"] . "/local/vendor/autoload.php";

if (\CModule::IncludeModule("nkhost.phpexcel")) {
    global $PHPEXCELPATH;
    require_once($PHPEXCELPATH . '/PHPExcel/IOFactory.php');
}

class File
{
    const VERSIONS_PATT     = "FILEV_#FILE_ID#_#VERSION#";

    const CONTENT_PATT      = "FILEC_#FILE_ID#";

    const CONVERTER_URL     = "https://corp.tularegion.local/nombstring/pdf.php";

    const CONVERTER_URL_DEV = "https://corp_ss.dev.tularegion.ru/nombstring/pdf.php";

    const CONVERTER_URL_LOCAL = "http://localhost/nombstring/pdf.php";

    const CONVERTER_AUTH    = "press:k37WtGpijcoc";

    protected $file             = [];

    protected $versions         = [];

    public $clearf              = [];

    private $isGeneratedFile    = false;

    function __construct(int $file_id = null)
    {
        $this->init($file_id);
    }

    function setClearf(array $clearf)
    {
        $this->clearf = $clearf;
    }

    function setName($name)
    {
        $this->file['ORIGINAL_NAME'] = mb_substr($name, 0, 80);
    }

    function getName()
    {
        return $this->file['ORIGINAL_NAME'] ?? "file.pdf";
    }

    function getDescription()
    {
        return $this->file['DESCRIPTION'] ?? null;
    }

    function getPath()
    {
        return $this->file['PATH'] ?? null;
    }

    function getSrc()
    {
        return $this->file['SRC']  ?? null;
    }

    function getId()
    {
        return $this->file['ID']  ?? null;
    }

    function isGenerated()
    {
        return $this->isGeneratedFile;
    }

    function init($file_id)
    {
        if ($this->loadFile($file_id)) {
            $this->loadVersions();
        }
    }

    function loadFile($file_id)
    {
        if ($file_id) {
            $file = current($this->getDBFiles(['ID'=>$file_id], 1));
            if ($file) {
                $this->file = $file;
                $this->isGeneratedFile = PdfilegeneratedTable::exists($file_id);
                return true;
            }
        }
        return false;
    }

    function saveVersion()
    {
        if ($this->getId()) {
            $a_file = \CFile::MakeFileArray($this->getId());
            $a_file['tmp_name']     = tempnam("/tmp", "pdf_file");
            $a_file['MODULE_ID']    = "pdf_file";
            $a_file['external_id']  = str_replace(['#FILE_ID#', '#VERSION#',], [$this->getId(), count($this->versions) + 1], self::VERSIONS_PATT);

            copy($this->getPath(), $a_file['tmp_name']);

            if ((int)\CFile::SaveFile($a_file, "pdfile", true)) {
                unlink($a_file['tmp_name']);
                $this->loadVersions();
                return true;
            }
        }

        return false;
    }

    function loadVersions()
    {
        $this->versions = [];
        $versions_patt  = str_replace(['#FILE_ID#', '#VERSION#',], [$this->getId(), ""], self::VERSIONS_PATT);
        $files          = $this->getDBFiles(['%=EXTERNAL_ID' => $versions_patt.'%']);

        foreach ($files as $version) {
            $version_indx = (int)str_replace($versions_patt, "", $version['EXTERNAL_ID']);
            if (!$version_indx) {
                continue;
            }

            $this->versions[$version_indx] = $version;
        }
        ksort($this->versions);
    }

    function clearCache()
    {
        CFile::CleanCache($this->getId());
    }

    function save()
    {
        if (!$this->getId()) {
            $file_id = \CFile::SaveFile([
                'name'          => $this->getName(),
                'type'          => "application/pdf",
                'content'       => "",
                'MODULE_ID'     => "pdf_file",
                'description'   => "PDFILEGENERATED_V3"
            ], "pdfile", true);
            if (!$file_id) {
                throw new Exception("Не удалось сохранить файл");
            }

            PdfilegeneratedTable::add($file_id);

            if (!$this->loadFile($file_id)) {
                throw new Exception("Не удалось загрузить файл");
            }
        }

        $this->saveVersion();
    }

    protected function getDBFiles($filter, $limit = 100)
    {
        $files = [];
        $res = \Bitrix\Main\FileTable::getList([
            'select' => [
                'ID',
                'TIMESTAMP_X',
                'MODULE_ID',
                'FILE_SIZE',
                'CONTENT_TYPE',
                'FILE_NAME',
                'ORIGINAL_NAME',
                'DESCRIPTION',
                'EXTERNAL_ID',
            ],
            'limit'  => $limit,
            'order'  => ['ID'=>'DESC'],
            'filter' => $filter,
        ]);
        while ($file = $res->fetch()) {
            $file['ID']  = (int)$file['ID'];
            $file['SRC'] = \CFile::getPath($file['ID']);
            $file['PATH']= $_SERVER['DOCUMENT_ROOT'].$file['SRC'];
            if ($file['SRC'] && file_exists($file['PATH'])) {
                $files[] = $file;
            }
        }
        return $files;
    }
}

class PDFile extends File
{
    protected $content = "";

    public $mpdf_params = [];

    function setName($name)
    {
        parent::setName($name);
        if (strtolower(pathinfo($name, PATHINFO_EXTENSION)) != "pdf") {
            $this->file['ORIGINAL_NAME'] .= ".pdf";
        }
    }

    function getContent()
    {
        return $this->content;
    }

    function init($file_id)
    {
        parent::init($file_id);
        $this->loadContent();
    }

    function save()
    {
        parent::save();
        $this->saveContent();
        if (!$this->modifyDBFiles($this->getId(), null, $this->genPDF())) {
            throw new Exception("Не удалось записать файл");
        }
        parent::clearCache();
    }

    function insert(string $data, ?string $pos = null)
    {
        if ($pos && mb_strpos($this->content, $pos) !== false) {
            $this->content = str_replace($pos, $data, $this->content);
        } elseif (mb_strpos($this->content, '</body>') !== false) {
            $this->content = str_replace('</body>', $data.'</body>', $this->content);
        } else {
            $this->content = $this->content.$data;
        }
    }

    function loadContent()
    {
        if ($this->getId() && $this->isGenerated()) {
            $file = current($this->getDBFiles(['=EXTERNAL_ID' => str_replace('#FILE_ID#', $this->getId(), self::CONTENT_PATT)], 1));
            if ($file) {
                $this->content = file_get_contents($file['PATH']);
                return true;
            }
        }
        return false;
    }

    function saveContent()
    {
        if ($this->getId()) {
            if ($this->isGenerated()) {
                $file = current($this->getDBFiles(['=EXTERNAL_ID' => str_replace('#FILE_ID#', $this->getId(), self::CONTENT_PATT)], 1));
                if ($file) {
                    $arch = \CFile::MakeFileArray($file['ID']);
                    $arch['tmp_name']     = tempnam("/tmp", "pdf_file");
                    $arch['MODULE_ID']    = "pdf_file";
                    $arch['external_id']  = str_replace(['#FILE_ID#', '#VERSION#',], [$file['ID'], count($this->versions) + 1], self::VERSIONS_PATT);

                    copy($file['PATH'], $arch['tmp_name']);
                    CFile::SaveFile($arch, "pdfile", true);
                    return $this->modifyDBFiles($file['ID'], $this->getContent());
                } else {
                    return (bool)\CFile::SaveFile([
                        'name'          => $this->getName().".html",
                        'type'          => "text/html",
                        'content'       => $this->getContent(),
                        'MODULE_ID'     => "pdf_file",
                        'external_id'   => str_replace('#FILE_ID#', $this->getId(), self::CONTENT_PATT),
                    ], "pdfile", true);
                }
            }
        }

        return false;
    }

    protected function modifyDBFiles(int $file_id, $content, $filename = null)
    {
        $file = current($this->getDBFiles(['ID'=>$file_id], 1));
        if ($file) {
            if ($filename) {
                copy($filename, $file['PATH']);
            } else {
                file_put_contents($file['PATH'], $content);
            }

            \Bitrix\Main\Application::getConnection()->queryExecute('UPDATE b_file SET FILE_SIZE="'.filesize($file['PATH']).'" WHERE ID='.$file_id);
            return true;
        }

        return false;
    }

    protected function genPDF()
    {
        $resp_file_name = tempnam("/tmp", "pdf_file");
        $resp_file = fopen($resp_file_name, 'w+');
        $resp_code = null;

        $ch = curl_init(
            !defined('DEV_SERVER')
                ? self::CONVERTER_URL
                : self::CONVERTER_URL_DEV
        );
        curl_setopt($ch, CURLOPT_USERPWD, self::CONVERTER_AUTH);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_FILE, $resp_file);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'html'          => str_replace($this->clearf, "", $this->getContent()),
            'src'           => !$this->isGenerated()?$this->getPath():null,
            'mpdf_params'   => $this->mpdf_params,
        ]));
        curl_exec($ch);
        $resp_code = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);
        fclose($resp_file);
        if ($resp_code !== 200) {
            throw new Exception("Не удалось создать PDF ($resp_code): " . file_get_contents($resp_file_name, false, null, 0, 200));
        }
        return $resp_file_name;
    }
}

class XLSXFile extends File
{
    protected $xls = null;

    function setName($name)
    {
        parent::setName($name);
        if (strtolower(pathinfo($name, PATHINFO_EXTENSION)) != "xlsx") {
            $this->file['ORIGINAL_NAME'] .= ".xlsx";
        }
    }

    function init($file_id)
    {
        parent::init($file_id);

        if (!\Bitrix\Main\Loader::includeModule('nkhost.phpexcel') || empty($GLOBALS['PHPEXCELPATH'])) {
            throw new Exception("Не найден модуль nkhost.phpexcel");
        }

        include_once ($GLOBALS['PHPEXCELPATH'] . '/PHPExcel/IOFactory.php');

        if ($this->getId()) {
            if (!$this->setTemplate($this->getPath())) {
                throw new Exception("Не удалось открыть файл");
            }
        } else {
            $this->xls = new \PHPExcel();
        }
    }

    function save()
    {
        parent::save();
        if ($this->getId()) {
            $writer = \PHPExcel_IOFactory::createWriter($this->xls, 'Excel2007');
            $writer->save($this->getPath());
            \Bitrix\Main\Application::getConnection()->queryExecute('UPDATE b_file SET FILE_SIZE="'.filesize($this->getPath()).'" WHERE ID='.$this->getId());
        }
        parent::clearCache();
    }

    function setTemplate($templatePath)
    {
        $xls = \PHPExcel_IOFactory::load($templatePath);
        if ($xls) {
            $this->xls = $xls;
            return true;
        }
        return false;
    }

    function __call($name, $arguments)
    {
        if (!method_exists($this->xls, $name)) {
            return null;
        }
        return $this->xls->$name(...$arguments);
    }
}

class Signer
{
    const SIGN_PATT = "FSIGNER_#FILE_ID#_#USER_ID#";

    const SERVER_CRIPTOPRO = "172.21.254.50";

    public static function getFiles(array $files_id, int $curUserId = 0): array
    {
        $files = [];

        if (empty($files_id)) {
            throw new Exception("Файлы не указаны");
        }

        if ($curUserId <= 0) {
            $curUserId = $GLOBALS['USER']->GetId();
        }

        foreach ($files_id as $file_id) {
            $file_id = (int)$file_id;

            if (empty($file_id)) {
                throw new Exception("Файл [".$file_id."] не найден");
            }

            $source_file = \CFile::GetFileArray($file_id);
            if (empty($source_file)) {
                throw new Exception("Файл [".$file_id."] не найден");
            }

            $source_file['SIGNS']       = self::getSigns($source_file['ID']);
            $source_file['SIGNED']      = isset($source_file['SIGNS'][ $curUserId ]);
            $source_file['PATH']        = $_SERVER['DOCUMENT_ROOT'].$source_file['SRC'];
            $source_file['EXTENSION']   = mb_strtolower(pathinfo($source_file['PATH'], PATHINFO_EXTENSION));
            $files[$file_id] = $source_file;
        }

        return $files;
    }

    protected static function signDescr(array $sign_infos): array
    {
        $sign_description = [];
        foreach ($sign_infos as $sign_info) {
            $sign_description[] = 'Дата: '.($sign_info['signingTime']?:date('d.m.Y H:i:s'));

            if (!empty($sign_info['cert']['subjectName'])) {
                $subjectName = array_unique(array_diff_key($sign_info['cert']['subjectName'], ['C'=>"C",'L'=>"L",'S'=>"S",'OGRN'=>"OGRN",'INN'=>"INN",'SNILS'=>"SNILS",'STREET'=>"STREET"]));
                $sign_description[] = "Владелец: ".implode(", ", $subjectName);
            }

            if (!empty($sign_info['cert']['validFromDate'])) {
                $sign_description[] = "Действителен: с ".$sign_info['cert']['validFromDate'];
                if (!empty($sign_info['cert']['validToDate'])) {
                    $sign_description[] .= " по ".$sign_info['cert']['validToDate'];
                }
            }
        }

        return $sign_description;
    }

    public static function setSign(
        int $file_id,
        string $sign_file,
        ?string $pos = null,
        ?array $clearf = null,
        ?int $signerId = null,
        ?bool $bCheckSign = false
    ): bool {
        $file               = current(self::getFiles([$file_id]));
        $arSign             = self::decodeSigFile($sign_file);

        if (is_null($signerId)) {
            $signerId = $GLOBALS['USER']->GetId();
        }

        if ($bCheckSign === true) {
            $arEmails = [];
            $arNames = [];
            foreach ($arSign as $signRow) {
                $arEmails[] = mb_strtolower($signRow['cert']['subjectName']['E']);
                $arNames[] = mb_strtolower($signRow['cert']['subjectName']['SN'] . ' ' . $signRow['cert']['subjectName']['G']);
            }

            $arSignerUser = \Bitrix\Main\UserTable::getList([
                'select' => [
                    'ID', 'EMAIL', 'NAME', 'LAST_NAME', 'SECOND_NAME'
                ],
                'filter' => [
                    'ID' => $signerId
                ]
            ])->Fetch();
            $arSignerUser['EMAIL'] = mb_strtolower($arSignerUser['EMAIL']);
            $arSignerUser['FULL_NAME'] = mb_strtolower(
                implode(
                    ' ',
                    [
                        trim($arSignerUser['LAST_NAME']),
                        trim($arSignerUser['NAME']),
                        trim($arSignerUser['SECOND_NAME']),
                    ]
                )
            );
            if (!in_array($arSignerUser['EMAIL'], $arEmails)) {
                if (!in_array($arSignerUser['FULL_NAME'], $arNames)) {
                    $msgIncorrect = PHP_EOL . 'ЭП: ' . implode(', ', $arNames) . ' ';
                    $msgIncorrect .= PHP_EOL . 'Портал: ' . $arSignerUser['FULL_NAME'];
                    Logger::info('Данные авторизованного пользователя не совпадают с данными из ЭП. ' . $msgIncorrect);
                    Logger::info('Подпись: ', $arSign);
                    throw new Exception('Данные авторизованного пользователя не совпадают с данными из ЭП');
                }
            }
        }

        $sign_description = self::signDescr($arSign);

        if (!$sign_description) {
            throw new Exception("Не удалось обработать подписанный файл");
        }

        if ($file['EXTENSION'] == "pdf") {
            $pdfile = new PDFile($file_id);
            $pdfile->setClearf($clearf?:[]);

            /**
             * @crunch [mlyamin 25.06.2020] Настройки pdf вынести в активити подписи
             * @see https://corp.tularegion.local/company/personal/user/2440/tasks/task/view/45922/
             */
            Loader::includeModule('iblock');
            Loader::includeModule('sprint.migration');
            $helper = new IblockHelper();
            $iblockId = $helper->getIblockId('bizproc_etk', 'bitrix_processes');
            $arFilter = [
                'IBLOCK_TYPE' => 'bitrix_processes',
                'IBLOCK_ID' => $iblockId,
                '%PROPERTY_FILES' => ':' . $file_id . ':',
                'ACTIVE'    => 'Y'
            ];
            $res = CIBlockElement::GetList(
                [],
                $arFilter,
                false,
                false,
                ['ID']
            );
            if ($row = $res->GetNext()) {
                $pdfile->mpdf_params = [
                    'margin_left'   => 15,
                    'margin_right'  => 15,
                    'margin_top'    => 15,
                    'margin_bottom' => 15,
                ];
            } else {
                $pdfile->mpdf_params = [];
            }

            $sign_html = '<table border="0" width="250" style="text-align: center;page-break-inside:avoid">
                <tbody>
                    <tr>
                        <td style="border:1px solid #222;text-align: center;"><img width="240" src="'.$_SERVER['DOCUMENT_ROOT'].'/upload/docsign.jpg" alt="ПОДПИСАНО ЭЛЕКТРОННОЙ ПОДПИСЬЮ"/></td>
                    </tr>
                    <tr>
                        <td style="border:1px solid #222;background: #222;color: #fff;text-align: center;padding: 5px 5px;font-size: 10px;">СВЕДЕНИЯ О СЕРТИФИКАТЕ ЭП</td>
                    </tr>
                    <tr>
                        <td style="border:1px solid #222;font-size: 10px;padding: 5px 5px;text-align: center;">'.implode('<br/>'.PHP_EOL, $sign_description).'</td>
                    </tr>
                </tbody>
                </table>';

            $pdfile->insert($sign_html, $pos);
            $pdfile->save();
        }

        if ($file['EXTENSION'] == "xls") {
            $xls = new \PHPExcel();
            $xls = \PHPExcel_IOFactory::load($file['PATH']);
            $xls->setActiveSheetIndex(0);
            $sheetData = $xls->getActiveSheet()->toArray(null, true, true, true);
            $iCount = count($sheetData);
            $xls->getActiveSheet()->setCellValue('A1', 'test');


            $iRow = $iCount + 5;
            $border = array(
                'borders'=>array(
                    'outline' => array(
                        'style' => \PHPExcel_Style_Border::BORDER_THICK,
                        'color' => array('rgb' => '000000')
                    ),
                )
            );
            $endBord = $iRow+5;
            $xls->getActiveSheet()->getStyle("D$iRow:H$endBord")->applyFromArray($border);

            $xls->getActiveSheet()->mergeCells("D$iRow:H$iRow");
            $xls->getActiveSheet()->setCellValue("D$iRow", 'ПОДПИСАНО ЭЛЕКТРОННОЙ ПОДПИСЬЮ');
            $iRow++;
            $xls->getActiveSheet()->mergeCells("D$iRow:H$iRow");
            $xls->getActiveSheet()->setCellValue("D$iRow", 'СВЕДЕНИЯ О СЕРТИФИКАТЕ ЭП');
            $iRow++;
            foreach ($sign_description as $info) {
                $xls->getActiveSheet()->mergeCells("D$iRow:H$iRow");
                $xls->getActiveSheet()->setCellValue("D$iRow", $info);
                $iRow++;
            }
            $objWriter = \PHPExcel_IOFactory::createWriter($xls, 'Excel5');
            $objWriter->save($file['PATH']);
        }

        $a_file = \CFile::MakeFileArray($sign_file);
        $a_file['MODULE_ID']    = "pdf_file";
        $a_file['name']         = $file['ORIGINAL_NAME'].".sig";
        $a_file['type']         = "application/x-pkcs7-certreqresp";
        $a_file['description']  = implode(PHP_EOL, $sign_description);
        $a_file['external_id']  = str_replace(['#FILE_ID#', '#USER_ID#',], [$file['ID'], $signerId], self::SIGN_PATT);

        if (empty(\CFile::SaveFile($a_file, "pdfile", true))) {
            throw new Exception("Не удалось сохранить подписанный файл");
        }

        return true;
    }

    protected static function getSigns(int $file_id): array
    {
        $signers    = [];
        $signs_patt = str_replace(['#FILE_ID#', '#USER_ID#',], [$file_id, ""], self::SIGN_PATT);
        $res = \Bitrix\Main\FileTable::getList([
            'select' => ['ID', 'EXTERNAL_ID', 'TIMESTAMP_X', 'ORIGINAL_NAME'],
            'filter' => [
                '%=EXTERNAL_ID' => $signs_patt.'%'
            ]
        ]);
        while ($item = $res->Fetch()) {
            $signer_id  = (int)str_replace($signs_patt, "", $item['EXTERNAL_ID'])?:"0";

            if (!isset($signers[$signer_id])) {
                $signers[$signer_id] = [
                    'SIGNER_ID'     => $signer_id,
                    'SIGNER_NAME'   => "ID ".$signer_id,
                    'SIGNS'         => []
                ];
            }
            $signers[$signer_id]['SIGNS'][] = $item;
        }
        if ($signers) {
            foreach ($signers as &$signer) {
                foreach ($signer['SIGNS'] as &$sign) {
                    $sign['SRC'] = CFile::GetPath($sign['ID']);
                }
                unset($sign);
            }
            unset($signer);

            $res = \Bitrix\Main\UserTable::getList([
                    'filter' => [
                        '@ID' => array_keys($signers)
                    ],
                    'select' => [
                        'ID',
                        'FIO',
                    ],
                    'runtime' => [
                        new \Bitrix\Main\Entity\ExpressionField('FIO', 'CONCAT_WS(" ", LAST_NAME, NAME, SECOND_NAME)')
                    ]
                ]);
            while ($usr = $res->fetch()) {
                $signers[$usr['ID']]['SIGNER_NAME'] = trim($usr['FIO']);
            }
        }

        return $signers;
    }

    /**
     * Расшифровка файла подписи
     * @param string $filename путь к файлу с подписью
     * @return Array поля подписи
     * @throws \Exception если:нет файла;ошибка на сервере
     */
    public static function decodeSigFile(string $filename): array
    {
        $data = null;
        if (!file_exists($filename)) {
            throw new Exception("File not found");
        }

        return self::decodeSig(file_get_contents($filename));
    }

    /**
     * Расшифровка подписи
     * @param string $data данные для расшифровки
     * @return Array поля подписи
     * @throws \Exception если ошибка на сервере
     */
    public static function decodeSig(string $data): array
    {
        /**
         * Проверка подписи на двойное base64
         */
        $strDoubleCoding = base64_decode($data);
        if (false !== mb_strpos($strDoubleCoding, '----- BEGIN')) {
            $data = trim(str_replace(
                ['----- BEGIN PKCS7 SIGNED -----', '----- END PKCS7 SIGNED -----'],
                '',
                $strDoubleCoding
            ));
        }

        $ch = curl_init("http://" . self::SERVER_CRIPTOPRO . "/uverify.php");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, ['sign' => $data]);
        $respString = curl_exec($ch);
        curl_close($ch);

        if (empty($respString)) {
            Logger::error("Ошибка сервера проверки подписи", ['response' => $respString, 'curl_getinfo' => curl_getinfo($ch)]);
            throw new Exception("Ошибка сервера проверки подписи");
        }

        $resp = json_decode($respString, true);
        if (empty($resp)) {
            Logger::error("Ошибка сервера проверки подписи", ['response' => $respString, 'json_last_error' => Logger::json_last_error(), 'curl_getinfo' => curl_getinfo($ch)]);
            throw new Exception("Ошибка сервера проверки подписи");
        }

        if (empty($resp['data']) || empty($resp['data']['signers']) || !is_array($resp['data']['signers'])) {
            $fId = crc32(date('d.m.Y H:i:s') . $data);
            $dir = $_SERVER['DOCUMENT_ROOT'] . '/local/logs/sign/' . date('Y-m-d') . '/';
            if (!is_dir($dir)) {
                mkdir($dir, 0775, true);
            }
            $fName = $dir . $fId . '.sig';
            file_put_contents($fName, $data);
            Logger::error("Не удалось проверить подпись КриптоПро, начинаем проверку VipNet", ['response' => $resp, 'respString' => $respString, 'curl_getinfo' => curl_getinfo($ch), 'data' => mb_strlen($data), 'path' => $fName]);
            // Пока криптопро не умеет распознавать PKCS7, идём обходным путём
            $ch = curl_init("http://" . self::SERVER_CRIPTOPRO . "/uverify_vipnet.php");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, ['sign' => $data]);
            $respString = curl_exec($ch);
            curl_close($ch);

            if (empty($respString)) {
                Logger::error("Ошибка сервера проверки подписи", ['response' => $respString, 'curl_getinfo' => curl_getinfo($ch)]);
                throw new Exception("Ошибка сервера проверки подписи");
            }

            $resp = json_decode($respString, true);
            if (empty($resp)) {
                Logger::error("Ошибка сервера проверки подписи", ['response' => $respString, 'json_last_error' => Logger::json_last_error(), 'curl_getinfo' => curl_getinfo($ch)]);
                throw new Exception("Ошибка сервера проверки подписи");
            }

            if (empty($resp['data']) || empty($resp['data']['signers']) || !is_array($resp['data']['signers'])) {
                Logger::error("Не удалось проверить подпись VipNet", ['response' => $resp, 'respString' => $respString, 'curl_getinfo' => curl_getinfo($ch), 'data' => mb_strlen($data), 'path' => $dir . $fId . '.sig']);
                throw new Exception("Не удалось проверить подпись");
            }
        }

        return $resp['data']['signers'];
    }
}

class Logger
{
    private static $logger = null;

    private static function init()
    {
        if (self::$logger !== null) {
            return;
        }

        self::$logger = new \Monolog\Logger('filesigner');
        self::$logger->pushHandler(
            new RotatingFileHandler(
                $_SERVER['DOCUMENT_ROOT'] . '/local/logs/filesigner/filesigner.log',
                30
            )
        );
    }

    public static function json_last_error()
    {
        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                return 'Ошибок нет';
            case JSON_ERROR_DEPTH:
                return 'Достигнута максимальная глубина стека';
            case JSON_ERROR_STATE_MISMATCH:
                return 'Некорректные разряды или несоответствие режимов';
            case JSON_ERROR_CTRL_CHAR:
                return 'Некорректный управляющий символ';
            case JSON_ERROR_SYNTAX:
                return 'Синтаксическая ошибка, некорректный JSON';
            case JSON_ERROR_UTF8:
                return 'Некорректные символы UTF-8, возможно неверно закодирован';
        }
        return json_last_error().' - Неизвестная ошибка';
    }

    public function __callStatic($name, $arguments)
    {
        self::init();
        self::$logger->$name(...$arguments);
    }
}

\Bitrix\Main\Loader::registerAutoLoadClasses('citto.filesigner', array(
    '\Citto\Filesigner\ShablonyTable' => '/lib/shablony.php',
    '\Citto\Filesigner\PdfilegeneratedTable' => '/lib/pdfilegenerated.php',
));
