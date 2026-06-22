(function () {

    var table;
    var $table = $('table.assessment-list-table');

    if (!$table.length) return;

    table = $table.DataTable({
        processing: true,
        serverSide: true,
        searching:  false,
        ordering:   false,
        dom: "<'row'<'col-12'tr>><'row datatable-footer'<'col-auto'l><'col-12 col-md' i><'col-12 col-md-auto'p>>",
        ajax: {
            url: $table.data('ajax-url'),
            data: function (d) {
                d.keyword    = $('#search-keyword').val();
                d.status     = $('#filter-status').val();
                d.start_date = $('#start-date').val();
                d.end_date   = $('#end-date').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex',     orderable: false, searchable: false, width: '60px' },
            { data: 'project_name',    orderable: false, searchable: false },
            { data: 'assessment_name', orderable: false, searchable: false },
            { data: 'created_at',      orderable: false, searchable: false },
            { data: 'submitted_at',    orderable: false, searchable: false },
            { data: 'status',          orderable: false, searchable: false },
        ]
    });

    $('#search-btn').on('click', function (e) {
        e.preventDefault();
        var startDate = $('#start-date').val();
        var endDate   = $('#end-date').val();
        if (startDate && !endDate) {
            if (typeof toastr !== 'undefined') toastr.error('Please fill End Date.');
            return;
        }
        if (!startDate && endDate) {
            if (typeof toastr !== 'undefined') toastr.error('Please fill Start Date.');
            return;
        }
        table.ajax.reload();
    });

    $('#reset-btn').on('click', function () {
        $('#search-keyword').val('');
        $('#filter-status').val('');
        $('#start-date').val('');
        $('#end-date').val('');
        table.ajax.reload();
    });

    $('#search-keyword').on('keydown', function (e) {
        if (e.key === 'Enter') table.ajax.reload();
    });

})();
