jQuery(document).ready(function( $ ){

    jQuery('div.bpas-shortcode-activities li.load-more').click(function() {

        var $this = $(this);
        var data = $this.parent().parent().find('form[name="bpas-activities-args"]').serialize();
        data += '&action=bpas_load_activities';

        $.post( ajaxurl, data, function(resp){
            if (resp.success ) {
                $this.prevAll('li').remove();
                $(resp.data).insertBefore( $this );
            }
        }, 'json' );

        return false;
    });
});