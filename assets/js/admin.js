jQuery(document).ready(function($) {
    $('.shcp_product').draggable({
        scope: 'shcp',
        containment: '#shcproducts_related .inside',
        cursor: 'move',
        revert: true
    });
    $('#shcp_related_tank').droppable({
        scope: 'shcp',
        drop: function(e, ui) {
            var curHeight = $(this).height(),
                addHeight = $(ui.draggable).height();
            $(this)
                .height(curHeight+addHeight)
                .append(ui.draggable.attr('style', null).clone());
        },
    });
});

