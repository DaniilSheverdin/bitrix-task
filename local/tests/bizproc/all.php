<?

namespace Citto\Tests\Bizproc;

use ReflectionClass;
use ReflectionException;
use Bitrix\Main\Loader;
use Bitrix\Main\UserTable;
use CBPWorkflowTemplateLoader;

/**
 * Бизнес-процессы
 */
class All
{
    /**
     * В шаблоне указан неактивный пользователь
     * @run hourly
     * @responsible 54
     */
    public static function testInactiveUserInTemplate()
    {
        $arUsers = [];
        $orm = UserTable::getList([
            'select'    => ['ID', 'NAME', 'LAST_NAME'],
            'filter'    => ['ACTIVE' => 'Y'],
        ]);
        while ($arUser = $orm->fetch()) {
            $arUsers[ $arUser['ID'] ] = $arUser;
        }

        global $DB;

        $arTemplates = [];
        $res = $DB->Query('SELECT t.ID, t.NAME, t.DOCUMENT_TYPE, i.NAME as IBLOCK
            FROM b_bp_workflow_template t
            INNER JOIN b_iblock i ON i.ID = REPLACE(t.DOCUMENT_TYPE, "iblock_", "")
            WHERE t.MODULE_ID = "lists"
            ORDER BY t.ID');
        while ($row = $res->Fetch()) {
            $arTemplates[] = $row;
        }

        Loader::includeModule('bizproc');
        $res = CBPWorkflowTemplateLoader::GetLoader();

        foreach ($arTemplates as $id => $row) {
            $tpl = $res->LoadWorkflow($row['ID']);

            $self = new self();

            $return = [];
            $self->getProperties($tpl[0], $return);
            $arUserActivities = [];
            foreach ($return as $row) {
                $arInactive = [];
                foreach ($row as $val) {
                    if (is_array($val)) {
                        foreach ($val as $val2) {
							if (is_array($val2)) {
								foreach ($val2 as $val3) {
									if (is_array($val3)) {
										foreach ($val3 as $val4) {
											if (0 === mb_strpos($val4, 'user_')) {
												$userId = (int)str_replace('user_', '', $val4);
												if ($userId > 0 && !array_key_exists($userId, $arUsers)) {
													$arInactive[] = $userId;
												}
											}
										}
									} else {
										if (0 === mb_strpos($val3, 'user_')) {
											$userId = (int)str_replace('user_', '', $val3);
											if ($userId > 0 && !array_key_exists($userId, $arUsers)) {
												$arInactive[] = $userId;
											}
										}
									}
								}
							} else {
								if (0 === mb_strpos($val2, 'user_')) {
									$userId = (int)str_replace('user_', '', $val2);
									if ($userId > 0 && !array_key_exists($userId, $arUsers)) {
										$arInactive[] = $userId;
									}
								}
							}
                        }
                    } else {
                        if (0 === mb_strpos($val, 'user_')) {
                            $userId = (int)str_replace('user_', '', $val);
                            if ($userId > 0 && !array_key_exists($userId, $arUsers)) {
                                $arInactive[] = $userId;
                            }
                        }
                    }
                }

                if (!empty($arInactive)) {
                    $row['INACTIVE_USERS'] = $arInactive;
                    $arTemplates[ $id ]['INACTIVE'][] = $row;
                }
            }
        }

        $arAllUsers = [];
        $orm = UserTable::getList([
            'select'    => ['ID', 'NAME', 'LAST_NAME'],
        ]);
        while ($arUser = $orm->fetch()) {
            $arAllUsers[ $arUser['ID'] ] = $arUser['NAME'] . ' ' . $arUser['LAST_NAME'];
        }

        $arMessage = [];
        foreach ($arTemplates as $id => $row) {
            if (isset($row['INACTIVE'])) {
                $mess = '<b>' . $row['IBLOCK'] . '</b><br/>';
                $mess .= 'Шаблон БП <i>' . $row['NAME'] . '</i><br/>';
                foreach ($row['INACTIVE'] as $activity) {
                    $arTplUsers = [];
                    foreach ($activity['INACTIVE_USERS'] as $uId) {
                        $arTplUsers[] = $arAllUsers[ $uId ] . ' (' . $uId . ')';
                    }
                    $mess .= 'В активити <b>' . $activity['Title'] . '</b> есть неактивные пользователи: <b>' . implode(', ', $arTplUsers) . '</b><br/>';
                }

                $arMessage[] = $mess;
            }
        }

        if (!empty($arMessage)) {
            return assert(false, implode("<br/>", $arMessage));
        } else {
            return assert(true);
        }
    }

    private function getPrivateField($object, $property)
    {
        $refClass = new ReflectionClass(get_class($object));
        try {
            $refProp = $refClass->getProperty($property);
            $refProp->setAccessible(true);

            return $refProp->getValue($object);
        } catch (ReflectionException $exc) {
            return '';
        }
    }

    private function getProperties($obj, &$return = [])
    {
        if (!is_array($obj)) {
            $obj = [$obj];
        }
        foreach ($obj as $row) {
            $props = $this->getPrivateField($row, 'arProperties');
            $props['CLASS_NAME'] = get_class($row);
            $return[] = $props;
            $arActivities = $this->getPrivateField($row, 'arActivities');
            if (!empty($arActivities)) {
                $this->getProperties($arActivities, $return);
            }
        }
    }
}
