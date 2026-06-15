var UserTable = function () {

    return {

        init: function () {
            UserTable.loadTable();
            UserTable.handleSearch();
        },

        loadTable: function () {
            UserTable.table = $('.datatable-ajax').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                ordering: false,
                ajax: {
                    url: routeList,
                    data: function (d) {
                        d.keyword = $('#search-keyword').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'full_name' },
                    { data: 'email' },
                    { data: 'phone_no' },
                    { data: 'status', orderable: false, searchable: false },
                    { data: 'created_at' },
                    { data: 'action', orderable: false, searchable: false },
                ]
            });
        },

        handleSearch: function () {
            $('#search-btn').on('click', function (e) {
                e.preventDefault();
                UserTable.table.draw();
            });
        },
    };

}();