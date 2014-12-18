(function() {
    'use strict';

     $("#tablesorter").tablesorter({
         widgets: ["filter"],
         widgetOptions : {
             filter_columnFilters : false,
             filter_childRows : false,
             filter_functions : { 0: true } // necessary to get exact matching for form name select dropdown on the first column
         }
     });

    $('#formNameDropDown').on('change', function() {
        var filter = [];
        var selected = $(this).val();

        // Currently hiding all extra field rows when formType changed for simplicity.
        // Ideally, we just hide the extra fields of the formTypes not displayed.
        $('tr.extraFields').hide();

        if (selected) {
            filter[0] = selected;
            $('#tablesorter').trigger('search', [ filter ]);
        } else {
            $("#tablesorter").trigger('filterReset');
        }

        return false;
    });

    $("a.toggle-extraFields").on("click", function(e) {
        e.preventDefault();
        $(this).closest("tr").next().toggle();
    });

    $( "#csvForm" ).submit(function( event ) {
        var $selectedOption = $('#csvDropDown').find(":selected");
        $('#csvFormType').val($selectedOption.data('formtype'));
        return;
    });

    $('a.deleteForm').on('click', function(e) {
        e.preventDefault();

        var $self = $(this);
        var url = Craft.getActionUrl('coalescentForms/deleteForm');
        var id = $self.data('id');

        $.ajax(url, {
            data: {id:id},
            dataType: 'json',
            method: 'post'
        }).done(function(data) {

            var $row = $self.closest('tr');
            var $childRow = $row.next();
            // remove this row
            $row.remove();
             // remove child row
            if ($childRow && $childRow.hasClass('extraFields')) {
                $childRow.remove();
            }

             // update the tablesorter to account for removed row
             var $table = $("#tablesorter");
             $table.trigger("update")
                 .trigger("sorton", $table.get(0).config.sortList)
                 .trigger("appendCache")
                 .trigger("applyWidgets");

             updateFormDropDowns();
         });
    });

    var updateFormDropDowns = function() {
        var formNames = [];
        var unique = [];

        // Loop through all form type columns and create an array of unique form names
        $('.formType').each(function() {
            var formName = $(this).text();
            var formType = $(this).data('formtype');
            var key = formName + '::' + formType; // unique key

            // If this unique form key hasn't been added yet...
            if (unique.indexOf(key) === -1) {
                unique.push(key);
                formNames.push({formName:formName,formType:formType});
            }
        });

        // If no form types, then no data! Don't display the form fields and table.
        if (formNames.length) {
            $('div.formsDataTrue').show();
            $('div.formsDataFalse').hide();
        } else {
            $('div.formsDataTrue').hide();
            $('div.formsDataFalse').show();
        }

        // Sort by form name
        formNames.sort(function(a,b) {
            return (a.formName > b.formName) ? 1 : ((b.formName > a.formName) ? -1 : 0);
        });

        // Remove all options before rebuilding
        var $formNameDropDown = $('#formNameDropDown');
        var $csvDropDown = $('#csvDropDown');
        $formNameDropDown
            .find('option')
            .remove()
            .end();
        $csvDropDown
            .find('option')
            .remove()
            .end();

        $formNameDropDown.append($("<option></option>")
            .attr("value","")
            .text("View All Forms"));

        $.each(formNames, function(key, value) {
            var option = '<option data-formtype="' + value.formType + '" value="' + value.formName + '">' + value.formName + '</option>';
            $formNameDropDown.append(option);
            $csvDropDown.append(option);
        });

    };

    // initialize the form name/type dropdowns
    updateFormDropDowns();
}());


