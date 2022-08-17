<?php

namespace Citto\Tests\Infra;

use idna_convert;
use DateTimeImmutable;
use Bitrix\Main\Loader;
use Sprint\Migration\Helpers\HlblockHelper;
use Bitrix\Highloadblock\HighloadBlockTable as HLTable;

require_once('idna_convert.class.php');

/**
 * SSL-сертификаты
 */
class Ssl
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
                'UF_CHECK_SSL' => 1,
            ],
        ]);
        $arDomains = [];
        while ($row = $res->fetch()) {
            $arDomains[ $row['UF_DOMAIN'] ] = $row['ID'];
        }

        foreach ($arDomains as $domain => $id) {
            if (substr_count($domain, '.') == 1) {
                $arDomains[ 'www.' . $domain ] = $id;
            }
        }

        return $arDomains;
    }

    /**
     * Истекают менее чем через 30 дней
     * @responsible 54
     * @run every(1 day)
     */
    public static function testExpire30()
    {
        $arDomains = self::getDomains();
        $arMessage = [];
        $entityDataClass = self::getEntity();
        foreach ($arDomains as $url => $id) {
            $url = 'http://' . $url . '/';
            $domain = parse_url($url, PHP_URL_HOST);
            $ts = self::check($url);
            if (!is_null($ts)) {
                $validTo = new DateTimeImmutable('@' . $ts);
                $now = new DateTimeImmutable('now');

                $diff = $now->diff($validTo);

                $days = str_replace('+', '', $diff->format('%R%a'));

                if (!is_null($days) && $days <= 30) {
                    if ($days <= 0) {
                        $arMessage[ $days . $domain ] = '[COLOR=#ff0000]SSL-сертификат для домена [b]' . $domain . '[/b] истек [b]' . abs($days) . '[/b] д. назад[/COLOR]';
                    } else {
                        $arMessage[ ($days < 10 ? '0'.$days : $days) . $domain ] = 'SSL-сертификат для домена [b]' . $domain . '[/b] истекает через [b]' . $days . '[/b] д.';
                    }
                }
                $entityDataClass::update($id, [
                    'UF_EXPIRE_SSL'   => $validTo->format('d.m.Y'),
                    'UF_LAST_SSL'     => $now->format('d.m.Y H:i:s'),
                ]);
            }
        }

        if (!empty($arMessage)) {
            ksort($arMessage);
            return assert(false, implode('<br/>', $arMessage));
        }

        return assert(true);
    }

    public static function check($url = '')
    {
        if (empty($url)) {
            return null;
        }

        $domain = parse_url($url, PHP_URL_HOST);

        $idn = new idna_convert(['idn_version' => 2008]);
        $domain = mb_stripos($domain, 'xn--') !== false ? $idn->decode($domain) : $idn->encode($domain);

        $get = stream_context_create([
            'ssl' => [
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true,
                'capture_peer_cert' => true,
            ]
        ]);

        $read = stream_socket_client(
            'ssl://'.$domain.':443',
            $errno,
            $errstr,
            10,
            STREAM_CLIENT_CONNECT,
            $get
        );
        $cert = stream_context_get_params($read);
        $certinfo = openssl_x509_parse($cert['options']['ssl']['peer_certificate']);

        if ($certinfo) {
            $validDomain = false;
            $findDomainArray = explode('.', $domain);
            $cnt = count($findDomainArray);
            $findDomain = $findDomainArray[ $cnt-2 ] . '.' . $findDomainArray[ $cnt-1 ];
            if (false !== mb_strpos($certinfo['name'], $findDomain)) {
                $validDomain = true;
            }
            if (false !== mb_strpos($certinfo['subject']['CN'], $findDomain)) {
                $validDomain = true;
            }
            if ($certinfo['subject']['CN'] == '*') {
                $validDomain = true;
            }
            if ($certinfo['subject']['CN'] == '*.'.$findDomain) {
                $validDomain = true;
            }
            $arAnotherDomains = isset($certinfo['extensions']['subjectAltName']) ? explode(',', $certinfo['extensions']['subjectAltName']) : [];
            $arAnotherDomains = array_map(function ($domain) {
                return trim(str_replace('DNS:', '', $domain));
            }, $arAnotherDomains);
            if (in_array($findDomain, $arAnotherDomains)) {
                $validDomain = true;
            }
            if ($validDomain) {
                return $certinfo['validTo_time_t'];
            }
        }

        return null;
    }
}
