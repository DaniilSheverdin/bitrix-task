<<<<<<< HEAD
<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
global $APPLICATION;

$APPLICATION->AddViewContent('inside_pagetitle', '<div class="pagetitle pagetitle2">
    <span class="pagetitle-item">'.(isset($_GET['uvedomlenie'])?'Уведомление об отпуске':'Заявление на отпуск ').'</span>
    <span class="pagetitle-star" id="pagetitle-star"></span>
    </div>'
=======
<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
global $APPLICATION;

$APPLICATION->AddViewContent('inside_pagetitle', '<div class="pagetitle pagetitle2">
    <span class="pagetitle-item">'.(isset($_GET['uvedomlenie'])?'Уведомление об отпуске':'Заявление на отпуск ').'</span>
    <span class="pagetitle-star" id="pagetitle-star"></span>
    </div>'
>>>>>>> e0a0eba79 (init)
);