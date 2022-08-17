<?

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
global $APPLICATION;

$APPLICATION->SetTitle('Редактирование списков - Тематики');
?>
<div class="js-classificator"></div>
<script type="text/javascript">
    $(document).ready(function(){
        $('.js-classificator').jstree({
            'plugins': [
                'changed',
                'search',
                'state',
                'contextmenu',
                'wholerow',
                'types',
                'dnd',
                'crrm'
            ],
            'types': {
                'default': {
                    'max_depth': 2,
                },
            },
            'crrm' : { 
                'move' : {
                    'check_move' : function (m) { 
                        var p = this._get_parent(m.o);
                        if (!p) {
                            return false;
                        }
                        p = p == -1 ? this.get_container() : p;
                        if (p === m.np) {
                            return true;
                        }
                        if (p[0] && m.np[0] && p[0] === m.np[0]) {
                            return true;
                        }
                        return false;
                    }
                }
            },
            'dnd' : {
                'drop_target' : false,
                'drag_target' : false
            },
            'contextmenu': {
                'items': {
                    'create' : {
                        'separator_before'  : false,
                        'separator_after'   : true,
                        '_disabled'         : function (data) {
                            var inst = $.jstree.reference(data.reference);
                            return !(inst.check('create_node', data.reference, inst.get_parent(data.reference), ''));
                        },
                        'label'             : 'Добавить',
                        'action'            : function (data) {
                            var inst = $.jstree.reference(data.reference),
                                obj = inst.get_node(data.reference);
                            inst.create_node(obj, {}, 'last', function (new_node) {
                                try {
                                    inst.edit(new_node);
                                } catch (ex) {
                                    setTimeout(function () { inst.edit(new_node); },0);
                                }
                            });
                        }
                    },
                    'rename' : {
                        'separator_before'  : false,
                        'separator_after'   : false,
                        '_disabled'         : function (data) {
                            var inst = $.jstree.reference(data.reference);
                            return !(inst.check('rename_node', data.reference, inst.get_parent(data.reference), ''));
                        },
                        'label'             : 'Изменить',
                        'action'            : function (data) {
                            var inst = $.jstree.reference(data.reference),
                                obj = inst.get_node(data.reference);
                            inst.edit(obj);
                        }
                    },
                    'remove' : {
                        'separator_before'  : false,
                        'icon'              : false,
                        'separator_after'   : false,
                        '_disabled'         : function (data) {
                            var inst = $.jstree.reference(data.reference);
                            return !(inst.check('delete_node', data.reference, inst.get_parent(data.reference), ''));
                        },
                        'label'             : 'Удалить',
                        'action'            : function (data) {
                            if (confirm('Вы уверены что хотите удалить данный пункт?')) {
                                var inst = $.jstree.reference(data.reference),
                                    obj = inst.get_node(data.reference);
                                if(inst.is_selected(obj)) {
                                    inst.delete_node(inst.get_selected());
                                }
                                else {
                                    inst.delete_node(obj);
                                }
                            }
                        }
                    },
                },
            },
            'core': {
                'data': function (obj, cb) {
                    let request = BX.ajax.runComponentAction('citto:checkorders', 'classificatorTree', {
                        mode: 'ajax',
                        json: {
                            action: 'classificatorTree'
                        }
                    });

                    request.then(function (ret) {
                        if (ret.status === 'success') {
                            cb.call(this, ret.data);
                        }
                    });
                },
                'check_callback' : function (operation, node, node_parent, node_position, more) {
                    if (operation === 'delete_node' && node_parent.id === '#') {
                        return false;
                    }
                    if (operation === 'delete_node' && node.original.delete_node === false) {
                        return false;
                    }
                    
                    return true;
                }
            },
        }).on('create_node.jstree', function (e, data) {
            let request = BX.ajax.runComponentAction('citto:checkorders', 'classificatorCreate', {
                mode: 'ajax',
                json: {
                    action: 'classificatorCreate',
                    id: data.node.id,
                    parent: data.node.parent,
                    name: data.node.original.text
                }
            });
        }).on('rename_node.jstree', function (e, data) {
            let request = BX.ajax.runComponentAction('citto:checkorders', 'classificatorRename', {
                mode: 'ajax',
                json: {
                    action: 'classificatorRename',
                    id: data.node.id,
                    name: data.text
                }
            });
        }).on('delete_node.jstree', function (e, data) {
            let request = BX.ajax.runComponentAction('citto:checkorders', 'classificatorDelete', {
                mode: 'ajax',
                json: {
                    action: 'classificatorDelete',
                    id: data.node.id
                }
            });
        }).on('context_show.vakata.jstree', function (e, data) {
            console.log(e, data);
        }).on('move_node.jstree', function (e, data) {
            let sort = (data.position * 10) - 5;
            if (data.position > data.old_position) {
                sort = (data.position * 10) + 5;
            }
            let request = BX.ajax.runComponentAction('citto:checkorders', 'classificatorSort', {
                mode: 'ajax',
                json: {
                    action: 'classificatorSort',
                    id: data.node.id,
                    sort: sort,
                    section: data.parent
                }
            });
        });
    });
</script>
