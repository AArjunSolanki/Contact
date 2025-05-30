@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card mb-4">
        <div class="card-header"><h4 class="mb-0">Manage Custom Fields</h4></div>
        <div class="card-body">
            <form id="field-form" class="row g-3">
                @csrf
                <input type="hidden" name="field_id" id="field-id">
                <div class="col-md-6">
                    <input type="text" name="label" id="field-label" class="form-control" placeholder="Field Label" >
                    <span id="label-error" class="text-danger error-text"></span>
                </div>
                <div class="col-md-4">
                    <select name="type" id="field-type" class="form-select" >
                        <option value="">Select Field Type</option>
                        <option value="text">Text</option>
                        <option value="date">Date</option>
                        <option value="number">Number</option>
                    </select>
                    <span id="type-error" class="text-danger error-text"></span>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-success w-100">Save</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h5 class="mb-0">Existing Fields</h5></div>
        <div class="card-body">
            <table class="table table-bordered table-striped" id="fields-table">
                <thead class="table-light">
                    <tr>
                        <th>Label</th>
                        <th>Type</th>
                        <th style="width: 180px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($fields as $field)
                    <tr data-id="{{ $field->id }}">
                        <td class="label">{{ $field->label }}</td>
                        <td class="type">{{ ucfirst($field->type) }}</td>
                        <td>
                            <button class="btn btn-sm btn-primary edit-btn">Edit</button>
                            <form method="POST" action="{{ route('custom-fields.destroy', $field->id) }}" style="display:inline-block">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger"
                                    onclick="return confirm('Delete this field?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {
   $('#field-form').on('submit', function (e) {
    e.preventDefault();
    const id = $('#field-id').val();
    const url = id ? `/custom-fields/${id}` : `{{ route('custom-fields.ajax-store') }}`;
    const method = id ? 'PUT' : 'POST';

    // Clear previous errors
    $('.error-text').text('');

    $.ajax({
        url: url,
        method: method,
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            _method: method,
            label: $('#field-label').val(),
            type: $('#field-type').val()
        },
        success: function (response) {
            location.reload();
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;
                if (errors.label) {
                    $('#label-error').text(errors.label[0]);
                }
                if (errors.type) {
                    $('#type-error').text(errors.type[0]);
                }
            } else {
                toastr.error('Something went wrong!');
            }
        }
    });
});


    $('.edit-btn').on('click', function () {
        const row = $(this).closest('tr');
        $('#field-id').val(row.data('id'));
        $('#field-label').val(row.find('.label').text().trim());
        $('#field-type').val(row.find('.type').text().trim().toLowerCase());
    });
});
</script>
@endsection
