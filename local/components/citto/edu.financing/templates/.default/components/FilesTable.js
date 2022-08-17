BX.Vue.component('files-table', {
    data() {
        return this.$parent._data;
    },
    methods: {
        add(event) {
            this.items.push({
                id: 'empty-' + Math.random(),
                empty: true,
                type: null,
                number: null,
                date: null,
                amount: null,
            });
            event.preventDefault();
            return false;
        }
    },
    computed: {
        isEdit() {
            return !!this.edit;
        }
    },
    template: `
        <div class="edu-financing-files-table">
            <table class="table table-bordered my-3">
                <thead>
                    <tr>
                        <th>Тип документа</th>
                        <th>№ документа</th>
                        <th>Дата документа</th>
                        <th>Сумма документа</th>
                        <th v-if="isEdit"></th>
                    </tr>
                </thead>
                <tbody>
                    <files-table-row
                        v-for="(item, key) in this.items"
                        :files-table-row="item"
                        :is-edit="isEdit"
                    ></files-table-row>
                    <tr v-if="isEdit">
                        <td colspan="5">
                            <button
                                class="ui-btn ui-btn-sm ui-btn-icon-add ui-btn-success"
                                @click="add"
                            ></button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    `
});


BX.Vue.component('files-table-row', {
    data () {
        return {
            ClassList: [
                'edu-financing-files-table-row',
            ],
            isDisabled: false,
            fileTypes: {
                'contract' : 'Договор',
                'bill'     : 'Счёт',
                'check'    : 'Кассовый, товарный чек или бланк строгой отчётности',
                'waybill'  : 'Накладная',
                'act'      : 'Акт оказания услуг или выполненных работ',
                'invoice'  : 'Счёт-фактура',
                'upd'      : 'УПД',
            },
        };
    },
    props: {
        filesTableRow: {
            type: Object,
            required: false,
        },
        isEdit: {
            type: Boolean,
            required: false,
        },
    },
    methods: {
        removeRow: function(event) {
            if (this.filesTableRow.empty === true) {
                this.$destroy();
                this.$el.parentNode.removeChild(this.$el);
            } else if (!this.isDisabled) {
                this.isDisabled = true;
            } else {
                this.isDisabled = false;
            }
            event.preventDefault();
            return false;
        },
        fileType(type) {
            return this.fileTypes[ type ] ?? '';
        },
    },
    computed: {
        buttonClasses: function() {
            if (this.isDisabled) {
                return 'ui-btn ui-btn-sm ui-btn-icon-business ui-btn-success';
            } else {
                return 'ui-btn ui-btn-sm ui-btn-icon-remove ui-btn-danger';
            }
        },
    },
    template: `
        <tr :class="ClassList">
            <td width="20%">
                <span v-if="isEdit">
                    <select
                        class="form-control"
                        name="FILES_DESC[type][]"
                        :value="filesTableRow.type"
                        :v-model="filesTableRow.type"
                        :disabled="isDisabled"
                    >
                        <option v-for="(typeName, type) in fileTypes" :value="type" :selected="type==filesTableRow.type">
                            {{ typeName }}
                        </option>
                    </select>
                </span>
                <span v-else>{{ fileType(filesTableRow.type) }}</span>
            </td>
            <td width="20%">
                <span v-if="isEdit">
                    <input
                        class="form-control"
                        name="FILES_DESC[number][]"
                        :value="filesTableRow.number"
                        :v-model="filesTableRow.number"
                        :disabled="isDisabled"
                    />
                </span>
                <span v-else>{{ filesTableRow.number }}</span>
            </td>
            <td width="20%">
                <span v-if="isEdit">
                    <input
                        class="form-control"
                        name="FILES_DESC[date][]"
                        onclick="BX.calendar({node: this, field: this, bTime: false});"
                        :value="filesTableRow.date"
                        :v-model="filesTableRow.date"
                        :disabled="isDisabled"
                    />
                </span>
                <span v-else>{{ filesTableRow.date }}</span>
            </td>
            <td width="20%">
                <span v-if="isEdit">
                    <input
                        class="form-control"
                        name="FILES_DESC[amount][]"
                        :value="filesTableRow.amount"
                        :v-model="filesTableRow.amount"
                        :disabled="isDisabled"
                    />
                </span>
                <span v-else>{{ filesTableRow.amount }}</span>
            </td>
            <td width="20%" v-if="isEdit">
                <button
                    :class="buttonClasses"
                    @click="removeRow"
                ></button>
            </td>
        </tr>
    `
});