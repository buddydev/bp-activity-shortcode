jQuery(document).ready(function( $ ){
    jQuery('div.bpas-shortcode-activities li.load-more').unbind('click');
    jQuery('div.bpas-shortcode-activities li.load-more').off('click');

    jQuery('div.bpas-shortcode-activities').on('click', 'li.load-more', function() {

        var $this = $(this);
        var $form = $this.parent().parent().find('form[name="bpas-activities-args"]');

        var data = $form.serialize();
        data += '&action=bpas_load_activities';
        var page = $form.find('bps-input-current-page').val();
        $.post( ajaxurl, data, function(resp){
            if (resp.success ) {
                page++;
                $form.find('bps-input-current-page').val(page);
                $this.hide();//prevAll('li').remove();
                $this.parents('ul.activity-list').append(resp.data);//.insertBefore( $this );
            }
        }, 'json' );

        return false;
    });
});