@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Contacts</h2>
        <div>
            <button class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#contactModal" id="add-new">
            Add Contact
            </button>
            <button class="btn btn-primary" id="import-via-xml">
            Import via XML
            </button>        

            <form id="xml-import-form" action="{{ route('contacts.imports.store') }}" method="POST" enctype="multipart/form-data"
                class="d-none">
                @csrf
                <input type="file" id="xml-file" name="file" accept=".xml,text/xml">
            </form>

            <div id="import-progress" class="mt-3" style="display:none;">
                <div class="progress">
                    <div id="import-bar" class="progress-bar" role="progressbar" style="width: 0%">0%</div>
                </div>
                <div id="import-status" class="small text-muted mt-1"></div>
            </div>
        </div>
    </div>
    <table id="contact-table" class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Phone</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>

        </tbody>
    </table>

    <!-- Contact Modal -->
    <div class="modal fade" id="contactModal" tabindex="-1" aria-labelledby="contactModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCenterTitle">Add New Contact</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="create-edit-contact" method="POST">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" id="contactId" name="id">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="phone" name="phone" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="submit-btn">Save Contact</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        var contactId;
        $(document).ready(function() {
            $('#contact-table').DataTable({
                lengthMenu: [
                    [10, 25, 50, 100],
                    [10, 25, 50, 100]
                ],
                language: {
                    emptyTable: "No Contacts Found",
                    zeroRecords: "No Contacts Found",
                },
                processing: true,
                serverSide: true,
                bDestroy: true,
                serverMethod: 'POST',
                searchDelay: 1000,
                ajax: {
                    url: "{{ route('contact.list') }}",
                    data: {
                        "_token": "{{ csrf_token() }}",
                    }
                },
                order: [
                    [0, 'desc'] // Now refers to the hidden ID column
                ],
                columnDefs: [{
                    targets: 0,
                    visible: false,
                    searchable: false,
                    orderable: true
                }, {
                    targets: "_all",
                    searchable: false
                }, {
                    targets: [0, 1],
                    orderable: true
                }, {
                    targets: '_all',
                    orderable: false
                }],
                columns: [{
                        data: 'id'
                    },
                    {
                        data: 'name',
                        orderable: true,
                    },
                    {
                        data: 'phone',
                        orderable: true,
                    },
                    {
                        data: null,
                        render: function(data, type, row) {
                            return `
                                <button class="btn btn-sm btn-primary editBtn" data-bs-toggle="modal" data-bs-target="#contactModal" data-id="${row.id}">Edit</button>
                                <button class="btn btn-sm btn-danger deleteBtn" data-id="${row.id}">Delete</button>
                            `;
                        }
                    }
                ]
            });

            $("#add-new").click(function(e) {
                contactId = null;
                $("#modalCenterTitle").text('Add Contact');
                $('#submit-btn').text('Submit');
                $('#create-edit-contact')[0].reset();
            });


            $('#contact-table tbody').on('click', 'button[data-bs-toggle="modal"]', function() {

                contactId = $(this).data('id'); // Get the id from the data-id attribute
                $("#modalCenterTitle").text('Edit Contact');
                $('#submit-btn').text('Save changes');
                // Make AJAX call to get the data for this contact
                $.ajax({
                    type: 'GET',
                    url: "{{ route('contact.show', ['contact' => 'contactId']) }}".replace(
                        'contactId', contactId),
                    success: function(response) {
                        if (response.success) {
                            // Prefill modal with data
                            $('#create-edit-contact').find('input[name="name"]').val(response
                                .data.name);
                            $('#create-edit-contact').find('input[name="phone"]').val(response
                                .data.phone);
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function(error) {
                        toastr.error(error.responseJSON.message);
                    }
                });
            });

            $(document).on('submit', '#create-edit-contact', function(e) {
                e.preventDefault();
                $("#submit-btn").attr('disabled', true);
                var formData = new FormData(this);

                var url, type;
                if (contactId) {
                    url = "{{ route('contact.update', ['contact' => 'contactId']) }}".replace('contactId',
                        contactId);
                    formData.append('_method', 'PUT');
                } else {
                    url = "{{ route('contact.store') }}";
                }

                $.ajax({
                    type: "POST",
                    url: url,
                    data: formData,
                    processData: false, // Prevent jQuery from automatically processing the data
                    contentType: false,
                    success: function(response) {
                        console.log(response)
                        if (response.success) {
                            toastr.success(response.message);
                            $('#create-edit-contact')[0].reset();
                            $('#contact-table').DataTable().ajax.reload();

                            // Hide modal
                            var contactModal = bootstrap.Modal.getInstance(document
                                .getElementById('contactModal'));
                            contactModal.hide();
                        } else {
                            console.log('ssss')
                            toastr.error(response.message);
                        }
                        $("#submit-btn").attr('disabled', false);
                    },
                    error: function(error) {
                        console.log('err')
                        toastr.error(error.responseJSON.message ||
                            "An error occurred.");
                        $("#submit-btn").attr('disabled', false);
                    }
                });
            });

            // Delete Contact
            $(document).on('click', '.deleteBtn', function() {
                var contactId = $(this).data('id');
                if (confirm('Are you sure you want to delete this contact?')) {
                    url = "{{ route('contact.destroy', ['contact' => 'contactId']) }}".replace('contactId',
                        contactId);
                    $.ajax({
                        url: url,
                        method: 'POST',
                        data: {
                            _method: 'DELETE',
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            toastr.success(response.message);
                            $('#contact-table').DataTable().ajax.reload();
                        }
                    });
                }
            });
        });

        $('#import-via-xml').on('click', function() {
            $('#xml-file').click();
        });

        $('#xml-file').on('change', function() {
            const $form = $('#xml-import-form');
            const data = new FormData(this.form);

            // Show the progress bar immediately (optional)
            $('#import-progress').show();

            // Initiate the file upload request via AJAX
            $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                data: data,
                processData: false,
                contentType: false,
                success: function(json) {
                    const import_id = json.import_id;

                    const poll = setInterval(function() {
                        $.getJSON(`{{ url('/contacts/imports') }}/${import_id}`, function(s) {
                            console.log(s)
                            const pct = s.progress || 0;
                            $('#import-bar')
                                .css('width', pct + '%')
                                .text(pct + '%');

                            const info = s.import;
                            $('#import-status').text(
                                `${info.status} — ${info.processed}/${info.total} processed (ok: ${info.succeeded}, failed: ${info.failed})`
                            );

                            if (info.status === 'completed' || info.status ===
                                'failed') {
                                clearInterval(poll);

                                toastr.success('Contacts has been imported successfully.');
                                $('#contact-table').DataTable().ajax.reload();
                            }
                        });
                    }, 1500);
                },
                error: function(xhr, status, error) {
                    console.error('Upload failed:', status, error);
                }
            });
        });
    </script>
@endpush
