<?

namespace Citto\Integration;

use RuntimeException;
use Bitrix\DocumentGenerator;
use Bitrix\Main\{Grid, IO, Loader, LoaderException};

class Docx
{
	public function generateDocument($sFileName, $arFields, $sTemplate)
    {
        Loader::includeModule('documentgenerator');

        $file = new IO\File($_SERVER['DOCUMENT_ROOT'].'/local/templates_docx/'.$sTemplate.'.docx');
        $body = new DocumentGenerator\Body\Docx($file->getContents());
        $body->normalizeContent();
        $arFileName = [];
        $body->setValues($arFields);
        $body->process();
        $strContent = $body->getContent();
        $docPath = '/upload/docx_generated/';
        $strFileName = $sFileName.'.docx';
        $path = $_SERVER['DOCUMENT_ROOT'] . $docPath;
        if (!mkdir($path, 0775, true) && !is_dir($path)) {
            throw new RuntimeException('Directory "' . $docPath . '" was not created');
        }
        file_put_contents($path . $strFileName, $strContent);

        return $docPath . $strFileName;
    }
}
