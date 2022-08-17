<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>
<?php
$APPLICATION->SetTitle("ЦИТ • 10 лет онлайн");
?>

<main class="w-100 mw-em-80 m-auto bg-white rounded">
    <div class="px-4 px-sm-5 py-5">
        <?$APPLICATION->IncludeComponent(
            "bitrix:player",
            "",
            Array(
                "ADVANCED_MODE_SETTINGS" => "N",
                "AUTOSTART" => "N",
                "AUTOSTART_ON_SCROLL" => "N",
                "HEIGHT" => "500",
                "MUTE" => "N",
                "PATH" => "/upload/medialibrary/f1a/10let.mp4",
                "PLAYBACK_RATE" => "1",
                "PLAYER_ID" => "",
                "PLAYER_TYPE" => "auto",
                "PRELOAD" => "N",
                "REPEAT" => "none",
                "SHOW_CONTROLS" => "Y",
                "SIZE_TYPE" => "absolute",
                "SKIN" => "",
                "SKIN_PATH" => "/bitrix/js/fileman/player/videojs/skins",
                "START_TIME" => "0",
                "VOLUME" => "90",
                "WIDTH" => "1000"
            )
        );?>
    </div>
</main>
