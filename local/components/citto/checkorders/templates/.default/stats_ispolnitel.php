<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
        die();
}

if (!$GLOBALS['USER']->IsAdmin()) {
    LocalRedirect('/control-orders/');
}

$arStatsData = $this->__component->getWidgetStats(true);

$arLang = [
    'COLUMN_NAME_ispolnitel' => 'По исполнителю:',
    'COLUMN_NAME_status' => 'По статусу:',
    'COLUMN_NAME_rukovoditel' => 'По руководителю:',
];
?>

<div id="control-orders-stats-detail-ispolnitel"></div>

<script>
    BX.Vue.component('control-orders-stats-detail-ispolnitel', {
        data() {
            return {
                data: <?=json_encode($arStatsData, JSON_UNESCAPED_UNICODE)?>,
                lang: <?=json_encode($arLang, JSON_UNESCAPED_UNICODE)?>
            }
        },
        methods: {
            inArray: function(needle, haystack) {
                var length = haystack.length;
                for(var i = 0; i < length; i++) {
                    if(haystack[i] == needle) return true;
                }
                return false;
            },
            onChange: function(value, $event) {
                if (!this.data.settings) {
                    this.data.settings = [];
                }

                const index = this.data.settings.findIndex(v => v == value) 
                const checked = $event.target.checked

                if (checked && index < 0) {
                    if (this.data.settings.length < 5) {
                        this.data.settings.push(value);
                    }
                }
                if (!checked && index >= 0) {
                    this.data.settings.splice(index, 1)
                }

                BX.ajax.runComponentAction(
                    'citto:checkorders',
                    'setWidgetSettings',
                    {
                        mode: 'ajax',
                        json: {
                            action: 'setWidgetSettings',
                            data: this.data.settings
                        }
                    }
                );
            }
        },
        template: `
            <div class="control-orders-stats-detail-ispolnitel">
                <div
                    v-for="(executorName, executor) in data.executors"
                    v-if="data.count[executor] && (inArray('RUKOVODITEL', data.roles[executor]) || inArray('ZAMESTITEL', data.roles[executor]) || inArray('IMPLEMENTATION', data.roles[executor]))"
                    class="box box-primary"
                    >
                    <div class="box-header with-border">
                        <h3 class="box-title">{{executorName}}</h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div
                                v-for="(types, code) in data.table"
                                class="col-4"
                                >
                                <b class="d-block text-center my-2">
                                    {{ lang['COLUMN_NAME_' + code] }}
                                </b>

                                <table class="table table-bordered table-striped">
                                    <tbody>
                                        <tr
                                            v-for="type in types"
                                            v-if="!data.stats[ executor ][ type ].hide"
                                            >
                                            <td class="text-center">
                                                <input
                                                    type="checkbox"
                                                    title="Отображать в виджете"
                                                    :value="type"
                                                    :checked="inArray(type, data.settings)"
                                                    :disabled="!inArray(type, data.settings) && data.settings.length>=5"
                                                    @change="onChange(type, $event)"
                                                    />
                                            </td>
                                            <td>
                                                {{ data.stats[ executor ][ type ].title }}
                                            </td>
                                            <td class="text-center">
                                                <a :href="data.stats[ executor ][ type ].link">
                                                    {{ data.stats[ executor ][ type ].count }}
                                                </a>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `
    });

    BX.Vue.create({
        el: '#control-orders-stats-detail-ispolnitel',
        template: '<control-orders-stats-detail-ispolnitel />'
    });
</script>
