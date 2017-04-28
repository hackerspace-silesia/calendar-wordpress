jQuery(document).ready(function(){
    jQuery.datetimepicker.setLocale('pl');
    jQuery('.datetimepicker').datetimepicker({
        dateFormat : 'dd-mm-yy H:i'
    });
});