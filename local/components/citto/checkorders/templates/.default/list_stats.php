<?php

use Bitrix\Main\UI\Extension;
use Citto\Controlorders\Orders;
use Citto\Controlorders\Settings;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

if (!$GLOBALS['USER']->IsAdmin()) {
    return;
}

// $arStatsData = $this->__component->getWidgetStats();

// if (empty($arStatsData['stats']) || empty($arStatsData['settings'])) {
//     return;
// }

?>
<div id="control-orders-stats-widget-ispolnitel"></div>
<script>
    BX.Vue.component('control-orders-stats-widget-ispolnitel', {
        data() {
            return {
                openWidget: localStorage.getItem('control-orders-stats-widget-ispolnitel-toggle')=='true',
                isLoading: true,
                data: {},
            };
        },
        created: function() {
            if (this.openWidget) {
                this.load();
            }
        },
        methods: {
            load: function() {
                let $this = this;
                let request = BX.ajax.runComponentAction(
                    'citto:checkorders',
                    'getWidgetStats',
                    {
                        mode: 'ajax',
                        json: {
                            action: 'getWidgetStats',
                        }
                    }
                );
                request.then(function(ret) {
                    $this._data.isLoading = false;
                    $this._data.data = ret.data;
                });
            },
            inArray: function(needle, haystack) {
                var length = haystack.length;
                for(var i = 0; i < length; i++) {
                    if(haystack[i] == needle) return true;
                }
                return false;
            },
            onClick: function($event) {
                if (this.isLoading) {
                    this.load();
                }
                this.openWidget = !this.openWidget;
                localStorage.setItem('control-orders-stats-widget-ispolnitel-toggle', this.openWidget);
            }
        },
        template: `
            <div class="sidebar-widget sidebar-widget-tasks">
                <div class="sidebar-widget-top d-flex">
                    <div class="sidebar-widget-top-title w-100">Основная статистика</div>
                    <div
                        :class="(openWidget ? '' : 'closed') + ' sidebar-widget-top-btn'"
                        :title="openWidget ? 'Свернуть' : 'Развернуть'"
                        @click="onClick($event)"
                        ></div>
                </div>
                <div :class="openWidget ? 'stats-right-block' : 'd-none'">
                    <img
                        src="/local/images/preloader/rolling.svg"
                        class="d-block m-auto"
                        v-if="isLoading"
                        />
                    <div
                        v-for="(executorName, executor) in data.executors"
                        v-if="!isLoading && data.count[executor]"
                        class="stats-executor"
                        >
                        <span class="stats-executor-title">
                            {{executorName}}
                        </span>
                        <ul>
                            <li
                                v-for="stat in data.settings"
                                v-if="data.stats[executor][stat].count && data.stats[executor][stat].link"
                                >
                                <b>{{data.stats[executor][stat].title}}:</b> <a :href="data.stats[executor][stat].link" target="_blank">{{data.stats[executor][stat].count}}</a>
                            </li>
                        </ul>
                        <a
                            :v-if="inArray('RUKOVODITEL', data.roles[executor]) || inArray('ZAMESTITEL', data.roles[executor]) || inArray('IMPLEMENTATION', data.roles[executor])"
                            class="stats-executor-detail"
                            :href="'/control-orders/?stats=ispolnitel#' + executor"
                            target="_blank"
                            ><span>Подробнее</span>&nbsp;&rarr;</a>
                    </div>
                </div>
            </div>
        `
    });

    BX.Vue.create({
        el: '#control-orders-stats-widget-ispolnitel',
        template: '<control-orders-stats-widget-ispolnitel/>'
    });
</script>
