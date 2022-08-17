<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) {
    die();
}
?><!DOCTYPE html>
<html>
<head>
    <title>Звонки губернатору ТО</title>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>

    <link rel="stylesheet" href="/bitrix/css/main/bootstrap_v4/bootstrap-4.3.1.min.css"/>
    <link rel="stylesheet" href="/local/templates/gubernator_calllist/css/mdb/mdb.min.css"/>
    <link rel="stylesheet" href="/local/templates/gubernator_calllist/css/style.css"/>
    <link rel="stylesheet" href="/local/components/citto/gubernator.view/templates/call/style.css"/>
    <link rel="stylesheet" href="/local/templates/gubernator_calllist/css/font-awesome/all.min.css"/>
    <link rel="stylesheet" href="/local/templates/gubernator_calllist/css/mdb/picker.default.css"/>
    <link rel="stylesheet" href="/local/templates/gubernator_calllist/css/mdb/piker.default.date.css"/>
    <link rel="stylesheet" href="/local/templates/gubernator_calllist/css/mdb/piker.default.time.css"/>
</head>
<body>
<div class="container-fluid">
    <header class="d-block position-fixed w-100 bg-white">
        <div class="row inner-head">
            <div class="col-xl-12">
                <div class="social_links position-fixed w-100">
                    <div class="social_links_wrap d-flex flex-nowrap justify-content-center">
                        <div class="social_links_item">
                            <a href="https://www.facebook.com/tularegion/" target="_blank">
                                <svg style="fill:#0071bd;" version="1.1" id="Layer_1"
                                     xmlns="https://www.w3.org/2000/svg" xmlns:xlink="https://www.w3.org/1999/xlink"
                                     x="0px" y="0px" width="30px" height="30px" viewbox="0 0 48 48"
                                     enable-background="new 0 0 48 48" xml:space="preserve">
						  <path d="M47.761,24c0,13.121-10.638,23.76-23.758,23.76C10.877,47.76,0.239,37.121,0.239,24c0-13.124,10.638-23.76,23.764-23.76
							  C37.123,0.24,47.761,10.876,47.761,24 M20.033,38.85H26.2V24.01h4.163l0.539-5.242H26.2v-3.083c0-1.156,0.769-1.427,1.308-1.427
							  h3.318V9.168L26.258,9.15c-5.072,0-6.225,3.796-6.225,6.224v3.394H17.1v5.242h2.933V38.85z"/>
						  </svg>
                            </a>
                        </div>
                        <div class="social_links_item">
                            <a href="https://twitter.com/Tularegion71" target="_blank">
                                <svg version="1.1" id="Layer_1" xmlns="https://www.w3.org/2000/svg"
                                     xmlns:xlink="https://www.w3.org/1999/xlink" x="0px" y="0px" width="30px"
                                     height="30px" viewbox="0 0 48 48" enable-background="new 0 0 48 48"
                                     xml:space="preserve">
						  <path style="fill:#0071bd;" d="M47.762,24c0,13.121-10.639,23.76-23.761,23.76S0.24,37.121,0.24,24c0-13.124,10.639-23.76,23.761-23.76
							  S47.762,10.876,47.762,24 M38.031,12.375c-1.177,0.7-2.481,1.208-3.87,1.481c-1.11-1.186-2.694-1.926-4.447-1.926
							  c-3.364,0-6.093,2.729-6.093,6.095c0,0.478,0.054,0.941,0.156,1.388c-5.063-0.255-9.554-2.68-12.559-6.367
							  c-0.524,0.898-0.825,1.947-0.825,3.064c0,2.113,1.076,3.978,2.711,5.07c-0.998-0.031-1.939-0.306-2.761-0.762v0.077
							  c0,2.951,2.1,5.414,4.889,5.975c-0.512,0.14-1.05,0.215-1.606,0.215c-0.393,0-0.775-0.039-1.146-0.109
							  c0.777,2.42,3.026,4.182,5.692,4.232c-2.086,1.634-4.712,2.607-7.567,2.607c-0.492,0-0.977-0.027-1.453-0.084
							  c2.696,1.729,5.899,2.736,9.34,2.736c11.209,0,17.337-9.283,17.337-17.337c0-0.263-0.004-0.527-0.017-0.789
							  c1.19-0.858,2.224-1.932,3.039-3.152c-1.091,0.485-2.266,0.811-3.498,0.958C36.609,14.994,37.576,13.8,38.031,12.375"/>
						  </svg>
                            </a>
                        </div>
                        <div class="social_links_item">
                            <a href="https://vk.com/tularegion71" target="_blank">
                                <svg version="1.1" id="Layer_1" xmlns="https://www.w3.org/2000/svg"
                                     xmlns:xlink="https://www.w3.org/1999/xlink" x="0px" y="0px" width="30px"
                                     height="30px" viewbox="0 0 48 48" enable-background="new 0 0 48 48"
                                     xml:space="preserve">
						  <path style="fill:#0071bd;" d="M47.761,24c0,13.121-10.639,23.76-23.76,23.76C10.878,47.76,0.239,37.121,0.239,24c0-13.123,10.639-23.76,23.762-23.76
							  C37.122,0.24,47.761,10.877,47.761,24 M35.259,28.999c-2.621-2.433-2.271-2.041,0.89-6.25c1.923-2.562,2.696-4.126,2.45-4.796
							  c-0.227-0.639-1.64-0.469-1.64-0.469l-4.71,0.029c0,0-0.351-0.048-0.609,0.106c-0.249,0.151-0.414,0.505-0.414,0.505
							  s-0.742,1.982-1.734,3.669c-2.094,3.559-2.935,3.747-3.277,3.524c-0.796-0.516-0.597-2.068-0.597-3.171
							  c0-3.449,0.522-4.887-1.02-5.259c-0.511-0.124-0.887-0.205-2.195-0.219c-1.678-0.016-3.101,0.007-3.904,0.398
							  c-0.536,0.263-0.949,0.847-0.697,0.88c0.31,0.041,1.016,0.192,1.388,0.699c0.484,0.656,0.464,2.131,0.464,2.131
							  s0.282,4.056-0.646,4.561c-0.632,0.347-1.503-0.36-3.37-3.588c-0.958-1.652-1.68-3.481-1.68-3.481s-0.14-0.344-0.392-0.527
							  c-0.299-0.222-0.722-0.298-0.722-0.298l-4.469,0.018c0,0-0.674-0.003-0.919,0.289c-0.219,0.259-0.018,0.752-0.018,0.752
							  s3.499,8.104,7.463,12.23c3.638,3.784,7.764,3.36,7.764,3.36h1.867c0,0,0.566,0.113,0.854-0.189
							  c0.265-0.288,0.256-0.646,0.256-0.646s-0.034-2.512,1.129-2.883c1.15-0.36,2.624,2.429,4.188,3.497
							  c1.182,0.812,2.079,0.633,2.079,0.633l4.181-0.056c0,0,2.186-0.136,1.149-1.858C38.281,32.451,37.763,31.321,35.259,28.999"/>
						  </svg>
                            </a>
                        </div>
                        <div class="social_links_item">
                            <a href="https://www.youtube.com/user/tulapravvideo" target="_blank">
                                <svg version="1.1" id="Layer_1" xmlns="https://www.w3.org/2000/svg"
                                     xmlns:xlink="https://www.w3.org/1999/xlink" x="0px" y="0px" width="30px"
                                     height="30px" viewbox="-455 257 48 48" enable-background="new -455 257 48 48"
                                     xml:space="preserve">
						  <path style="fill:#0071bd;" d="M-431,257.013c13.248,0,23.987,10.74,23.987,23.987s-10.74,23.987-23.987,23.987s-23.987-10.74-23.987-23.987
						  S-444.248,257.013-431,257.013z M-419.185,275.093c-0.25-1.337-1.363-2.335-2.642-2.458c-3.054-0.196-6.119-0.355-9.178-0.357
						  c-3.059-0.002-6.113,0.154-9.167,0.347c-1.284,0.124-2.397,1.117-2.646,2.459c-0.284,1.933-0.426,3.885-0.426,5.836
						  s0.142,3.903,0.426,5.836c0.249,1.342,1.362,2.454,2.646,2.577c3.055,0.193,6.107,0.39,9.167,0.39c3.058,0,6.126-0.172,9.178-0.37
						  c1.279-0.124,2.392-1.269,2.642-2.606c0.286-1.93,0.429-3.879,0.429-5.828C-418.756,278.971-418.899,277.023-419.185,275.093z
						   M-433.776,284.435v-7.115l6.627,3.558L-433.776,284.435z"/>
						  </svg>
                            </a>
                        </div>
                        <div class="social_links_item">
                            <a href="https://www.instagram.com/tularegion71/" target="_blank">
                                <svg version="1.1" id="Layer_1" xmlns="https://www.w3.org/2000/svg"
                                     xmlns:xlink="https://www.w3.org/1999/xlink" x="0px" y="0px" width="30px"
                                     height="30px" viewbox="-455 257 48 48" enable-background="new -455 257 48 48"
                                     xml:space="preserve">
						  <path style="fill:#0071bd;" d="M-430.938,256.987c13.227,0,23.95,10.723,23.95,23.95c0,13.227-10.723,23.95-23.95,23.95
							  c-13.227,0-23.95-10.723-23.95-23.95C-454.888,267.71-444.165,256.987-430.938,256.987z M-421.407,268.713h-19.06
							  c-1.484,0-2.688,1.204-2.688,2.69v19.07c0,1.485,1.203,2.689,2.688,2.689h19.06c1.484,0,2.688-1.204,2.688-2.689v-19.07
							  C-418.72,269.917-419.923,268.713-421.407,268.713z M-430.951,276.243c2.584,0,4.678,2.096,4.678,4.681
							  c0,2.585-2.095,4.68-4.678,4.68c-2.584,0-4.678-2.096-4.678-4.68C-435.629,278.339-433.535,276.243-430.951,276.243z
							   M-421.579,289.324c0,0.54-0.437,0.978-0.977,0.978h-16.779c-0.54,0-0.977-0.438-0.977-0.978V279.08h2.123
							  c-0.147,0.586-0.226,1.199-0.226,1.831c0,4.144,3.358,7.504,7.5,7.504c4.142,0,7.5-3.359,7.5-7.504c0-0.632-0.079-1.245-0.226-1.831
							  h2.061V289.324L-421.579,289.324z M-421.516,275.23c0,0.54-0.438,0.978-0.977,0.978h-2.775c-0.54,0-0.977-0.438-0.977-0.978v-2.777
							  c0-0.54,0.438-0.978,0.977-0.978h2.775c0.54,0,0.977,0.438,0.977,0.978V275.23z"/>
						  </svg>
                            </a>
                        </div>
                        <div class="social_links_item governor-helpline position-relative">
                            <img src="/local/templates/gubernator_calllist/images/helpline.png"/>
                            <div class="position-absolute helpline-poper">
                                <p>Телефон доверия<br>губернатора Тульской области:</p>
                                <p><a class="_phone" href='tel:+78002007102'>
                                        <span>8 (800) <span>200-71-02</span></span>
                                    </a>
                                </p>
                            </div>
                        </div>
                        <div class="social_links_item ep-helpline position-relative">
                            <a href="https://tularegion.ru/live/ask/otprobr/greeting/" target="_blank">
                                <img src="/local/templates/gubernator_calllist/images/ep.png"/>
                            </a>
                            <div class="position-absolute helpline-poper">
                                <a href="https://tularegion.ru/live/ask/otprobr/greeting/" target="_blank">
                                    Направить обращение
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
