<?

assert_options(ASSERT_ACTIVE, 1);
assert_options(ASSERT_WARNING, 0);
assert_options(ASSERT_QUIET_EVAL, 1);
assert_options(ASSERT_CALLBACK, 'assert_handler');

$testClasses = [];
global $testClasses;
scanCurDir(__DIR__);

/**
 * Обработка тестов в коде
 * @param  string  $file
 * @param  integer $line
 * @param  string  $code
 */
function assert_handler($file, $line, $code, $desc = null)
{
    $file = str_replace(__DIR__, '', $file);
    $backtrace = debug_backtrace();
    $method = implode(
        '',
        [
            str_replace('\\', '_', $backtrace[2]['class']),
            $backtrace[2]['type'],
            $backtrace[2]['function']
        ]
    );
    if (!empty($backtrace[2]['args'])) {
        $method .= '::' . crc32(serialize($backtrace[2]['args']));
    }

    global $DB;
    $arFields = [
        'METHOD'        => "'".$DB->ForSQL($method)."'",
        'DESCRIPTION'   => "'".$DB->ForSQL($desc)."'",
    ];
    $arFields['MD5'] = "'".md5(json_encode($arFields))."'";
    $query = 'INSERT IGNORE
                INTO i_system_tests_result ('.implode(',', array_keys($arFields)).')
                VALUES ('.implode(',', $arFields).')';
    $DB->Query($query);
}

function trimDoc($doc)
{
    $pattern = "#(@[a-zA-Z]+\s*[a-zA-Z0-9, ()_].*)#";
    $doc = preg_replace($pattern, '', $doc);
    $doc = trim(str_replace(['/**', '*/', '*', "\r\n", "\r", "\n", "\t"], ' ', $doc));
    $doc = trim(preg_replace('/[\s]{2,}/', ' ', $doc));
    return $doc;
}

function createTasks()
{
    $userId = 1;

    global $DB;
    $arRows = [];
    $res = $DB->Query('SELECT * FROM i_system_tests_result WHERE TASK_ID IS NULL');
    while ($row = $res->Fetch()) {
        $arRows[ $row['METHOD'] ]['OLD'] = [];
        $res2 = $DB->Query('SELECT * FROM i_system_tests_result WHERE METHOD LIKE "' . $DB->ForSQL($row['METHOD']) . '" AND TASK_ID IS NOT NULL');
        while ($row2 = $res2->Fetch()) {
            $arRows[ $row['METHOD'] ]['OLD'][ $row2['TASK_ID'] ] = $row2['TASK_ID'];
        }
        $arRows[ $row['METHOD'] ]['ID'][ $row['ID'] ] = $row['ID'];
        $arRows[ $row['METHOD'] ]['MESSAGE'][ $row['ID'] ] = $row['DESCRIPTION'];
    }

    global $testClasses;
    foreach ($testClasses as $class => $file) {
        if (!class_exists($class)) {
            require_once($_SERVER['DOCUMENT_ROOT'] . $file);
        }
    }

    CModule::IncludeModule("tasks");
    $prevOccurAsUserId = \Bitrix\Tasks\Util\User::getOccurAsId();
    \Bitrix\Tasks\Util\User::setOccurAsId($userId);

    $arCommentedTasks = [];

    $res = $DB->Query('SELECT * FROM i_system_tests_result r INNER JOIN i_system_tests t ON t.NAME = r.METHOD WHERE t.LAST_RESULT = ""');
    while ($row = $res->Fetch()) {
        if (in_array($row['TASK_ID'], $arCommentedTasks)) {
            continue;
        }
        try {
            $oTaskItem = CTaskItem::getInstance($row['TASK_ID'], 1);
            $arTask = $oTaskItem->getData();
            if ($arTask['REAL_STATUS'] >= 5) {
                unset($row['OLD'][ $key ]);
                throw new Exception('Delete');
            } else {
                $arFields = [
                    'AUTHOR_ID'     => $userId,
                    'POST_MESSAGE'  => 'Последний запуск теста прошёл без ошибок',
                ];
                $comm = CTaskCommentItem::add($oTaskItem, $arFields);
                $arCommentedTasks[] = $row['TASK_ID'];
                throw new Exception('Delete');
            }
        } catch (Exception $exc) {
            $DB->Query('DELETE FROM i_system_tests_result WHERE METHOD LIKE "' . $DB->ForSQL($row['METHOD']) . '" AND TASK_ID = "' . $row['TASK_ID'] . '"');
        }
    }

    $arUserToComment = [
        'CREATED_BY'        => '@created',
        'RESPONSIBLE_ID'    => '@responsible',
        'AUDITORS'          => '@auditor',
    ];

    foreach ($arRows as $method => $row) {
        if (!empty($row['OLD'])) {
            foreach ($row['OLD'] as $key => $value) {
                try {
                    $oTaskItem = CTaskItem::getInstance($value, 1);
                    $arTask = $oTaskItem->getData();
                    if ($arTask['REAL_STATUS'] >= 5) {
                        unset($row['OLD'][ $key ]);
                        $DB->Query('DELETE FROM i_system_tests_result WHERE METHOD LIKE "' . $DB->ForSQL($method) . '" AND TASK_ID = ' . $value);
                    }
                } catch (Exception $exc) {
                    $DB->Query('DELETE FROM i_system_tests_result WHERE TASK_ID = "' . $value . '"');
                    unset($row['OLD'][ $key ]);
                }
            }
        }

        if (empty($row['OLD'])) {
            $method = explode('::', $method);
            $method = $method[0] . '::' . $method[1];
            $method = str_replace('_', '\\', $method);
            $reflectionMethod = new ReflectionMethod($method);
            $reflectionClass  = new ReflectionClass(mb_substr($method, 0, mb_strpos($method, '::')));
            $classDoc = trimDoc($reflectionClass->getDocComment());
            $doc = $reflectionMethod->getDocComment();
            $methodDoc = trimDoc($doc);
            $pattern = "#(@[a-zA-Z]+\s*[a-zA-Z0-9, ()_].*)#";
            preg_match_all($pattern, $doc, $matches, PREG_PATTERN_ORDER);
            $arParams = $matches[0] ?? [];
            $arUsers = [
                'CREATED_BY'        => $userId,
                'RESPONSIBLE_ID'    => 570,
                'AUDITORS'          => [],
            ];
            foreach ($arParams as $str) {
                foreach ($arUserToComment as $field => $find) {
                    if (false !== mb_strpos($str, $find)) {
                        $value = trim(str_replace([$find . ' ', "\t", "  "], ['', ' ', ' '], $str));
                        if ((int)$value > 0) {
                            if (is_array($arUsers[ $field ])) {
                                $arUsers[ $field ][] = $value;
                            } else {
                                $arUsers[ $field ] = $value;
                            }
                        }
                    }
                }
            }

            $title = '[autotests] ' . (!empty($classDoc) ? '[' . $classDoc . '] ' : '') . (!empty($methodDoc) ? $methodDoc : $method);
            $text = '';
            if (!empty($methodDoc)) {
                $text .= '<i>' . $method . '</i><br/><br/>';
            }
            $text .= implode('<br/><br/>', $row['MESSAGE']);

            $arFields = [
                'SITE_ID'           => 'nh',
                'GROUP_ID'          => 168,
                'CREATED_BY'        => $arUsers['CREATED_BY'] ?? $userId,
                'RESPONSIBLE_ID'    => $arUsers['RESPONSIBLE_ID'] ?? 570,
                'TITLE'             => $title,
                'DESCRIPTION'       => $text,
                'AUDITORS'          => $arUsers['AUDITORS'] ?? [],
            ];
            $oTaskItem = CTaskItem::add($arFields, \Bitrix\Tasks\Util\User::getAdminId());
            $taskId = $oTaskItem->getId();
        } else {
            foreach ($row['OLD'] as $taskId) {
                try {
                    $oTaskItem = CTaskItem::getInstance($taskId, $userId);
                    $arFields = [
                        'AUTHOR_ID'     => $userId,
                        'POST_MESSAGE'  => implode('<br/><br/>', $row['MESSAGE']),
                    ];
                    $comm = CTaskCommentItem::add($oTaskItem, $arFields);
                } catch (Exception $exc) {
                    $DB->Query('DELETE FROM i_system_tests_result WHERE TASK_ID = "' . $taskId . '"');
                }
            }
        }
        if ($taskId > 0) {
            $DB->Query('UPDATE i_system_tests_result SET MESSAGE = "Y", TASK_ID = ' . $taskId . ' WHERE ID IN ('.implode(',', $row['ID']).');');
        }
    }

    \Bitrix\Tasks\Util\User::setOccurAsId($prevOccurAsUserId);
}

function scanCurDir($dir)
{
    global $testClasses;
    $ffs = array_diff(scandir($dir), ['.', '..']);

    if (count($ffs) < 1) {
        return [];
    }

    foreach ($ffs as $ff) {
        if ($ff == 'include.php') {
            continue;
        }
        if (is_dir($dir . '/' . $ff)) {
            scanCurDir($dir . '/' . $ff);
        } else {
            $path = '/local/tests' . str_replace(__DIR__, '', $dir) . '/' . $ff;
            $className = str_replace(['/local/', '/', '.php'], ['Citto\\', '\\', ''], $path);
            $className = explode('\\', $className);
            $className = array_map(function ($a) {
                global $mb_ucfirst;
                return $mb_ucfirst($a);
            }, $className);
            $className = implode('\\', $className);
            include($_SERVER['DOCUMENT_ROOT'] . $path);
            if (class_exists($className)) {
                $testClasses[ $className ] = $path;
            }
        }
    }
}

/**
 * Запустить тесты
 */
function testSystem($debug = false)
{
    $stats = [];
    @set_time_limit(0);
    global $testClasses;
    $arRunStats = [];
    $arRunTime = [];
    foreach ($testClasses as $class => $file) {
        if (!class_exists($class)) {
            require_once($_SERVER['DOCUMENT_ROOT'] . $file);
        }
        $reflection  = new ReflectionClass($class);
        $obj = new $class;
        $methods = $reflection->getMethods();
        if (!empty($methods)) {
            foreach ($methods as $method) {
                $testResult = false;
                $doc = $reflection->getMethod($method->name)->getDocComment();

                $pattern = "#(@[a-zA-Z]+\s*[a-zA-Z0-9, ()_].*)#";
                preg_match_all($pattern, $doc, $matches, PREG_PATTERN_ORDER);
                $methodName = $method->name;
                if (mb_substr($methodName, 0, 4) === 'test') {
                    $stats['tests']++;
                    $arParams = $matches[0] ?? [];
                    $run = 'hourly';
                    foreach ($arParams as $k => $str) {
                        if (false !== mb_strpos($str, '@run')) {
                            $run = trim(str_replace(['@run ', "\t", "  "], ['', ' ', ' '], $str));
                            unset($arParams[ $k ]);
                        }
                    }

                    $bSimpleRun = true;
                    if (!empty($arParams)) {
                        foreach ($arParams as $str) {
                            $params = [];
                            if (false !== mb_strpos($str, '@assert')) {
                                $bSimpleRun = false;
                                $string = str_replace(['@assert ', "\t", "  "], ['', ' ', ' '], $str);
                                $params = explode(' ', $string);
                                $runName = str_replace('\\', '_', $class) . '::' . $methodName;
                                if (time() > mayRun($runName, $run)) {
                                    $testResult = call_user_func_array([$class, $methodName], $params);
                                    if ($debug) {
                                        echo $testResult ? '.' : 'F';
                                    }
                                    $stats[$testResult ? 'pass' : 'fail']++;
                                    $stats['assert']++;
                                    $arRunStats[ $runName ] = $testResult ? $arRunStats[ $runName ] : 'fail';
                                    $arRunTime[ $runName ] = $run;
                                }
                            } elseif (false !== mb_strpos($str, '@dataProvider')) {
                                $bSimpleRun = false;
                                $string = str_replace(['@dataProvider ', "\r\n", "\r", "\n"], '', $str);
                                $params = $obj->$string();
                                foreach ($params as $param) {
                                    $runName = str_replace('\\', '_', $class) . '::' . $methodName . '::' . crc32(serialize($param));
                                    if (time() > mayRun($runName, $run)) {
                                        $testResult = call_user_func_array([$class, $methodName], $param);
                                        if ($debug) {
                                            echo $testResult ? '.' : 'F';
                                        }
                                        $stats[$testResult ? 'pass' : 'fail']++;
                                        $stats['assert']++;
                                        $arRunStats[ $runName ] = $testResult ? $arRunStats[ $runName ] : 'fail';
                                        $arRunTime[ $runName ] = $run;
                                    }
                                }
                            }
                        }
                    }

                    if ($bSimpleRun) {
                        $runName = str_replace('\\', '_', $class) . '::' . $methodName;
                        if (time() > mayRun($runName, $run)) {
                            $testResult = $obj->$methodName();
                            if ($debug) {
                                echo $testResult ? '.' : 'F';
                            }
                            $stats[$testResult ? 'pass' : 'fail']++;
                            $stats['assert']++;
                            $arRunStats[ $runName ] = $testResult ? $arRunStats[ $runName ] : 'fail';
                            $arRunTime[ $runName ] = $run;
                        }
                    }
                }
            }
        }
    }
    if ($debug) {
        echo "\r\n";
        echo date('d.m.Y H:i:s') . "\r\n";
        echo 'Было запущено '.(int)$stats['tests'].' тестов, '.(int)$stats['assert'].' итераций с данными'."\r\n";
        echo 'Из них: Пройдено '.(int)$stats['pass'].', провалено '.(int)$stats['fail'];
    }

    global $DB;
    foreach ($arRunStats as $method => $result) {
        $nextRunTime = mayRun($method, $arRunTime[ $method ] ?? 'hourly', false);
        $nextRun = date('Y-m-d H:i:s', $nextRunTime);
        $arFields = [
            'NAME'          => "'" . $DB->ForSQL($method) . "'",
            'LAST_START'    => "NOW()",
            'NEXT_START'    => "'" . $nextRun . "'",
            'LAST_RESULT'   => "'" . $DB->ForSQL($result) . "'",
        ];

        $query = 'INSERT
                    INTO i_system_tests ('.implode(',', array_keys($arFields)).')
                    VALUES ('.implode(',', $arFields).') ON DUPLICATE KEY UPDATE LAST_START = NOW(), NEXT_START = ' . $arFields['NEXT_START'] . ', LAST_RESULT = "' . $DB->ForSQL($result) . '"';
        $DB->Query($query);
        if ($nextRunTime < time()) {
            $nextRunTime = mayRun($method, $arRunTime[ $method ] ?? 'hourly', false);
            $nextRun = date('Y-m-d H:i:s', $nextRunTime);
            $query = 'INSERT
                    INTO i_system_tests ('.implode(',', array_keys($arFields)).')
                    VALUES ('.implode(',', $arFields).') ON DUPLICATE KEY UPDATE LAST_START = NOW(), NEXT_START = "' . $nextRun . '", LAST_RESULT = "' . $DB->ForSQL($result) . '"';
            $DB->Query($query);
        }
    }

    createTasks();

    return __METHOD__.'();';
}

function mayRun($method, $rule, $fromTable = true)
{
    $period = '1 HOUR';
    $rule = mb_strtoupper($rule);
    switch ($rule) {
        case 'HOURLY':
            $period = '1 HOUR';
            break;
        case 'DAILY':
            $period = '1 DAY';
            break;
        default:
            $period = str_replace(['EVERY(', ')'], '', $rule);
            break;
    }

    global $DB;
    $res = $DB->Query('SELECT * FROM i_system_tests WHERE NAME = "' . $DB->ForSQL($method) . '"');
    if ($row = $res->Fetch()) {
        if ($fromTable && !empty($row['NEXT_START'])) {
            return strtotime($row['NEXT_START']);
        }
        $lastStart = $row['LAST_START'];
        $nextStart = strtotime($lastStart . ' +' . $period);
        return $nextStart;
    }

    return time()-1;
}
