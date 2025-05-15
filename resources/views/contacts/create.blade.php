@extends('layouts.app')

@section('content')
<h2 class="mb-4">📝 Create Contact</h2>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('contacts.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control" required />
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required />
                </div>

                <div class="col-md-6">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" />
                </div>
                <div class="col-md-6">
                    <label class="form-label">Gender</label>
                    <div class="form-check">
                        <input type="radio" name="gender" value="male" class="form-check-input" id="genderMale">
                        <label class="form-check-label" for="genderMale">Male</label>
                    </div>
                    <div class="form-check">
                        <input type="radio" name="gender" value="female" class="form-check-input" id="genderFemale">
                        <label class="form-check-label" for="genderFemale">Female</label>
                    </div>
                    <div class="form-check">
                        <input type="radio" name="gender" value="other" class="form-check-input" id="genderOther">
                        <label class="form-check-label" for="genderOther">Other</label>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Profile Image</label>
                    <input type="file" name="profile_image" class="form-control" />
                </div>
                <div class="col-md-6">
                    <label class="form-label">Additional File</label>
                    <input type="file" name="additional_file" class="form-control" />
                </div>
            </div>

            <hr class="my-4">

            <h5 class="mb-3">📌 Custom Fields</h5>
            <hr class="my-4">

            <h5 class="mb-3">➕ Add New Custom Field</h5>
            <div class="row g-2 align-items-center mb-3">
                <div class="col-md-5">
                    <input type="text" id="newCustomFieldLabel" class="form-control" placeholder="Label (e.g., Birthday)" />
                </div>
                <div class="col-md-3">
                    <select id="newCustomFieldType" class="form-select">
                        <option value="text">Text</option>
                        <option value="number">Number</option>
                        <option value="date">Date</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-outline-secondary w-100" id="addCustomFieldBtn">Add Field</button>
                </div>
            </div>

            <div id="newCustomFieldsContainer" class="mb-4"></div>

            <button type="submit" class="btn btn-success">💾 Save Contact</button>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<!-- ✅ Dynamic field logic -->
<script>
$(document).ready(function () {

        $('form').on('submit', function (e) {
        e.preventDefault();
        const form = this;
        const data = new FormData(form);

        $.ajax({
            url: form.action,
            method: form.method,
            data: data,
            processData: false,
            contentType: false,
            success: function () {
                alert('Contact created!');
                window.location.href = "{{ route('contacts.index') }}";
            },
            error: function () {
                alert('Failed to create contact.');
            }
        });
    });

    // add dynamic customField
    let customFieldIndex = 10000;

    $('#addCustomFieldBtn').click(function () {
        const label = $('#newCustomFieldLabel').val().trim();
        const type = $('#newCustomFieldType').val();

        if (label === '') {
            alert('Please enter a field label');
            return;
        }

        const fieldHtml = `
            <div class="form-group mb-3 d-flex align-items-center new-field-row" data-index="${customFieldIndex}">
                <div class="w-100">
                    <label>${label}</label>
                    <input type="${type}" name="custom_fields_new[${customFieldIndex}][value]" class="form-control mb-1" />
                    <input type="hidden" name="custom_fields_new[${customFieldIndex}][label]" value="${label}" />
                    <input type="hidden" name="custom_fields_new[${customFieldIndex}][type]" value="${type}" />
                </div>
                <button type="button" class="btn btn-danger ms-2 remove-field-btn">Remove</button>
            </div>
        `;

        $('#newCustomFieldsContainer').append(fieldHtml);

        // Reset inputs
        $('#newCustomFieldLabel').val('');
        $('#newCustomFieldType').val('text');

        customFieldIndex++;
    });

    $(document).on('click', '.remove-field-btn', function () {
        $(this).closest('.new-field-row').remove();
    });
});
</script>
@endsection
