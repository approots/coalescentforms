(function() {
    'use strict';

     $("#tablesorter").tablesorter({
         widgets: ["filter"],
         widgetOptions : {
             filter_columnFilters : false,
             filter_childRows : false
         }
     });

    $('#formTypeDropDown').on('change', function() {
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

    // TODO form name vs form type
    var updateFormDropDowns = function() {
        var formTypes = [];

        // Loop through all form type columns and create an array of unique form names
        $('.formType').each(function() {
            var formType = $(this).text();//('formType');
            formTypes.push(formType);
        });

        // If no form types, then no data! Don't display the form fields and table.
        if (formTypes.length) {
            $('div.formsDataTrue').show();
            $('div.formsDataFalse').hide();
        } else {
            $('div.formsDataTrue').hide();
            $('div.formsDataFalse').show();
        }

        formTypes = arrayUnique(formTypes);
        formTypes = formTypes.sort();

        $('#formTypeDropDown')
            .find('option')
            .remove()
            .end();

        $('#formTypeDropDown').append($("<option></option>")
            .attr("value","")
            .text("View All Forms"));

        $('#csvDropDown')
            .find('option')
            .remove()
            .end();

        $.each(formTypes, function(key, value) {
            $('#formTypeDropDown').append($("<option></option>")
                    .attr("value",value)
                    .text(value));
            $('#csvDropDown')
                .append($("<option></option>")
                    .attr("value",value)
                    .text(value));
        });

    };

    var arrayUnique = function(a) {
        return a.reduce(function(p, c) {
            if (p.indexOf(c) < 0) p.push(c);
            return p;
        }, []);
    };

    // initialize the form type dropdowns
    updateFormDropDowns();
}());


