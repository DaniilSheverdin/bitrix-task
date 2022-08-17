<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) {
    die();
}
global $APPLICATION;
?><!DOCTYPE html>
<html>
<head>
    
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title><?=$APPLICATION->ShowTitle()?></title>
    <?=$APPLICATION->ShowHead()?>
    <link rel="stylesheet" href="/local/templates/indicators/css/style.css"/>
</head>
<body>
<div class="header">
    <div class="wrapper">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col col-sm-9 col-md-10">
                    <nav class="header__navbar">
                        <ul class="navbar__list">
                            <li class="navbar__item">
                                <a class="navbar__link" href="/citto/indicators/">Показатели</a>
                            </li>
                            <li class="navbar__item">
                                <a class="navbar__link" href="/citto/indicators/?show=list&set_filter=y&arrFilter_2635=2448902325">Статистические данные</a>
                            </li>
                            <li class="navbar__item">
                                <a class="navbar__link" href="/citto/indicators/add/">Заполнить</a>
                            </li>
                        </ul>
                    </nav>

                    <button class="header__menu-button d-block d-sm-none" id="sidebarShow">
                        <svg width="50" height="38" viewBox="0 0 50 38" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect width="50" height="4" rx="2" fill="white"/>
                            <rect y="17" width="50" height="4" rx="2" fill="white"/>
                            <rect y="34" width="50" height="4" rx="2" fill="white"/>
                        </svg>
                    </button>
                </div>

                <div class="col col-sm-3 col-md-2">
                    <?
                    /*if($USER->IsAuthorized()){?>
                        <a href="/citto/services/requests/form_new.php" ><button class="header__login-button">Заполнить</button></a>
                    <?}else{?>
                        <a href="" ><button class="header__login-button">Войти</button></a>
                <?}*/
                    ?>
                    
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function () {
    // сайдбар
    var sidebarShowBtn = $('#sidebarShow'),
        sidebarCloseBtn = $('#sidebarHide'),
        sidebarOverlay = $('.sidebar-overlay'),
        sidebar = $('.sidebar');

    sidebarShowBtn.on('click', function () {
        sidebarOverlay.show();
        sidebar.animate({width: 'show'});
    })

    sidebarCloseBtn.on('click', function () {
        sidebarOverlay.hide();
        sidebar.animate({width: 'hide'});
    })
    var showAllBtn = $('.checkbox-group__show-btn');

    showAllBtn.on('click', function (e) {
        e.preventDefault();
        $(this).hide();

        var checkboxes = $(this).siblings('.form-check')

        $(checkboxes).each(function () {
            if ($(this).hasClass('d-none')) {
                $(this).removeClass('d-none');
            }
        })
    })
})
</script>
<main>