<?php

namespace Citto\Tests\Infra;

use idna_convert;
use DateTimeImmutable;
use Bitrix\Main\Loader;
use Sprint\Migration\Helpers\HlblockHelper;
use Bitrix\Highloadblock\HighloadBlockTable as HLTable;

require_once('idna_convert.class.php');

/**
 * Регистрация доменов
 */
class Whois
{
    public static function getEntity()
    {
        Loader::includeModule('highloadblock');
        Loader::includeModule('sprint.migration');
        $helper = new HlblockHelper();
        $hlId = $helper->getHlblockId('Domains');
        $hlblock = HLTable::getById($hlId)->fetch();
        $entity = HLTable::compileEntity($hlblock);
        return $entity->getDataClass();
    }

    public static function getDomains()
    {
        $entityDataClass = self::getEntity();
        $res = $entityDataClass::getList([
            'filter' => [
                'UF_CHECK_WHOIS' => 1,
            ],
        ]);
        $arDomains = [];
        while ($row = $res->fetch()) {
            $arDomains[ $row['UF_DOMAIN'] ] = $row['ID'];
        }

        return $arDomains;
    }

    /**
     * Истекают менее чем через 60 дней
     * @responsible 54
     * @run every(1 day)
     */
    public static function testExpire60()
    {
        $arDomains = self::getDomains();
        $arMessage = [];
        $entityDataClass = self::getEntity();
        foreach ($arDomains as $domain => $id) {
            $paidTill = self::check($domain);
            if (!is_null($paidTill)) {
                $validTo = new DateTimeImmutable($paidTill);
                $now = new DateTimeImmutable('now');

                $diff = $now->diff($validTo);

                $days = str_replace('+', '', $diff->format('%R%a'));

                if (!is_null($days) && $days > 7 && $days <= 60) {
                    $arMessage[ md5(str_replace('www.', '', $domain) . $days) ] = 'Срок регистрации домена <b>' . $domain . '</b> истекает через <b>' . $days . '</b> д.';
                }
                $entityDataClass::update($id, [
                    'UF_EXPIRE_WHOIS'   => $validTo->format('d.m.Y'),
                    'UF_LAST_WHOIS'     => $now->format('d.m.Y H:i:s'),
                ]);
                sleep(1);
            }
        }

        if (!empty($arMessage)) {
            return assert(false, implode('<br/>', $arMessage));
        }

        return assert(true);
    }

    public static function check($url = '')
    {
        if (empty($url)) {
            return null;
        }

        $server = 'whois.tcinet.ru';
        if (false !== mb_strpos($url, '.com')) {
            $server = 'whois.crsnic.net';
        } elseif (false !== mb_strpos($url, '.net')) {
            $server = 'whois.crsnic.net';
        } elseif (false !== mb_strpos($url, '.org')) {
            $server = 'whois.publicinterestregistry.net';
        }

        $idn = new idna_convert(['idn_version' => 2008]);
        $url = mb_stripos($url, 'xn--') !== false ? $idn->decode($url) : $idn->encode($url);

        $socket = fsockopen($server, 43);
        if ($socket) {
            fputs($socket, $url . PHP_EOL);
            $whois = '';
            while (!feof($socket)) {
                $whois .= fgets($socket, 128);
            }

            fclose($socket);

            $arTmpWhois = explode("\n", $whois);
            $arTmpWhois = array_filter($arTmpWhois);
            $arWhois = [];
            foreach ($arTmpWhois as $row) {
                if (0 === mb_strpos($row, '%')) {
                    continue;
                }

                $arRow = explode(':', $row, 2);
                if (!empty($arRow)) {
                    $arRow = array_map('trim', $arRow);
                    $arWhois[ str_replace(':', '', $arRow[0]) ] = $arRow[1];
                }
            }

            if (array_key_exists('paid-till', $arWhois)) {
                return $arWhois['paid-till'];
            } elseif (array_key_exists('Registry Expiry Date', $arWhois)) {
                return $arWhois['Registry Expiry Date'];
            }
        }

        return null;
    }
}
