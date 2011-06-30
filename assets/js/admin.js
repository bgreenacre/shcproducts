jQuery(document).ready(function($) {
    $('.shcp_product').draggable({
        scope: 'shcp',
        containment: '#shcproducts_related .inside',
        cursor: 'move',
        revert: true,
        connectToSortable: '#shcp_related_tank',
        helper: 'clone'
    });
    $('#shcp_related_tank').droppable({
        scope: 'shcp',
        over: function(e, ui) {
            var $el = $(this),
                $sender = $(ui.draggable),
                curHeight = $el.height(),
                addHeight = $sender.height();
            $el.height(curHeight+addHeight);
        }
    }).sortable({
        receive: function(e, ui) {
        },
        change: function() {console.log('change');},
        create: function() {console.log('create');},
        out: function() {console.log('out');}
    });
});

