    @extends('layouts.app')

    @section('content')
    <div class="container">

       <div id="editFormContainer" class="card mb-4 shadow-sm rounded">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">Add / Edit Contact</h5>
    </div>
    <div class="card-body">
        <form id="contact-form" enctype="multipart/form-data">
            @csrf
            <input type="hidden" id="contact-id" name="contact_id">

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control" placeholder="Full Name">
                    <span class="text-danger error-text name_error"></span>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" placeholder="Email">
                    <span class="text-danger error-text email_error"></span>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" placeholder="Phone">
                     <span class="text-danger error-text phone_error"></span>

                </div>

                <div class="col-md-4">
                    <label class="form-label d-block">Gender</label>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="gender" value="Male">
                        <label class="form-check-label">Male</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="gender" value="Female">
                        <label class="form-check-label">Female</label>
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Profile Image</label>
                    <input type="file" name="profile_image" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Additional File</label>
                    <input type="file" name="additional_file" class="form-control">
                </div>

                <div class="col-12">
                    <hr>
                    <h6>Custom Fields</h6>
                </div>

                @foreach($fields as $field)
                    <div class="col-md-6">
                        <label class="form-label">{{ $field->label }}</label>
                        <input type="{{ $field->type }}" name="custom_fields[{{ $field->id }}]" class="form-control">
                    </div>
                @endforeach

                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-success mt-3">
                        <i class="bi bi-save me-1"></i> Save Contact
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>


        <div class="card mb-4">
            <div class="card-header">Filter Contacts</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <input type="text" id="filter-name" class="form-control" placeholder="Filter by Name">
                    </div>
                    <div class="col-md-4">
                        <input type="text" id="filter-email" class="form-control" placeholder="Filter by Email">
                    </div>
                    <div class="col-md-4">
                        <select id="filter-gender" class="form-select">
                            <option value="">All</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div id="contacts-table">
            @include('contacts.partials.table')
        </div>
      

    </div>

    <script>
    $(document).ready(function() {
        $('#contact-form').on('submit', function(e) {
            e.preventDefault();
            $('.error-text').text('');
            let formData = new FormData(this);
            let contactId = $('#contact-id').val();
            let url = contactId
                ? `/contacts/${contactId}`
                : `{{ route('contacts.store') }}`;
            let method = contactId ? 'POST' : 'POST';
            formData.append('_method', contactId ? 'PUT' : 'POST');

            $.ajax({
                url: url,
                method: method,
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    toastr.success(response.message || 'Contact saved!');
                    $('#contact-form')[0].reset();
                    $('#contact-id').val('');
                    $('#form-errors').addClass('d-none').empty();
                    loadContacts();
                },
                error: function(xhr) {
                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    $.each(errors, function(key, val) {
                        $(`.${key}_error`).text(val[0]);
                    });
                } else {
                    toastr.error('Something went wrong!');
                }
            }

            });
        });
        $(document).on('click', '.edit-contact', function() {
            $('#contact-id').val($(this).data('id'));
            $('[name="name"]').val($(this).data('name'));
            $('[name="email"]').val($(this).data('email'));
            $('[name="phone"]').val($(this).data('phone'));
            let gender = $(this).data('gender');
            $(`[name="gender"][value="${gender}"]`).prop('checked', true);

            let contactId = $(this).data('id');

            $('input[name^="custom_fields"]').val('');

            $.get(`/contacts/${contactId}/custom-fields`, function(response) {
                let customFields = response.custom_fields;

                $.each(customFields, function(fieldId, value) {
                    $(`input[name="custom_fields[${fieldId}]"]`).val(value);
                });
            });
        });
        $(document).on('click', '.delete-btn', function() {
            if(!confirm('Are you sure to delete this contact?')) return;

            let contactId = $(this).data('id');

            $.ajax({
                url: `/contacts/${contactId}`,
                type: 'DELETE',
                success: function(response) {
                    toastr.success('Contact deleted successfully!');
                    loadContacts();
                },
                error: function() {
                    toastr.error('Failed to delete contact!');
                }
            });
        });

   $(document).on('click', '.edit-contact', function () {
    let isMerged = $(this).closest('tr').hasClass('table-warning');
    if (isMerged) {
        toastr.warning('This contact has been merged and cannot be edited.');
        return;
    }

    let contactId = $(this).data('id');
    $('#contact-id').val(contactId);
    $('[name="name"]').val($(this).data('name'));
    $('[name="email"]').val($(this).data('email'));
    $('[name="phone"]').val($(this).data('phone'));
    let gender = $(this).data('gender');
    $(`[name="gender"][value="${gender}"]`).prop('checked', true);

    $.ajax({
        url: `/contacts/${contactId}/custom-fields`,
        method: 'GET',
        success: function (data) {
            $.each(data, function (fieldId, value) {
                $(`input[name="custom_fields[${fieldId}]"]`).val(value);
            });
             document.getElementById('editFormContainer').scrollIntoView({
                behavior: 'smooth'
            });
        },
        error: function () {
            toastr.error('Failed to load custom field values.');
        }
    });
});



        $('#filter-name, #filter-email, #filter-gender').on('input change', function() {
            loadContacts();
        });

        function loadContacts() {
            $.ajax({
                url: "{{ route('contacts.filter') }}", 
                method: 'GET',
                data: {
                    name: $('#filter-name').val(),
                    email: $('#filter-email').val(),
                    gender: $('#filter-gender').val()
                },
                success: function(data) {
                    $('#contacts-table').html(data);
                },
                error: function() {
                    toastr.error('Failed to load contacts!');
                }
            });
        }

    });
   
    </script>
    @endsection
