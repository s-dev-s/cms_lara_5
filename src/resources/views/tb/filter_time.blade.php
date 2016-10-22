
<div style="position: relative;">
<input type="text" 
       id="f-{{$name}}"
       value="{{$value}}" 
       name="filter[{{ $name }}]" 
       class="form-control input-small datepicker" >
       
<span class="input-group-addon form-input-icon form-input-filter-icon">
    <i class="fa fa-calendar"></i>
</span>
</div>

<script>
jQuery(document).ready(function() {
    jQuery("#f-{{$name}}").datetimepicker({
        prevText: '<i class="fa fa-chevron-left"></i>',
        nextText: '<i class="fa fa-chevron-right"></i>',
        dateFormat: "",
        timeOnly: true,
        timeFormat: 'HH:mm',
        //showButtonPanel: true,
        regional: ["ru"],
        onClose: function (selectedDate) {}
    });
});
</script>