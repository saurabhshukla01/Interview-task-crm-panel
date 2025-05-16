@extends('layouts.app')

@section('content')
<h2 class="mb-3">📇 Contacts</h2>

<!-- Filter Form -->
<!-- 🔍 Filter Section -->
<div class="card mb-4 shadow-sm">
  <div class="card-body">
    <div class="row g-3 align-items-end">
      <div class="col-md-3">
        <label for="filter-name" class="form-label fw-semibold">Name</label>
        <input type="text" id="filter-name" class="form-control" placeholder="Search by name">
      </div>
      <div class="col-md-3">
        <label for="filter-email" class="form-label fw-semibold">Email</label>
        <input type="text" id="filter-email" class="form-control" placeholder="Search by email">
      </div>
      <div class="col-md-3">
        <label for="filter-gender" class="form-label fw-semibold">Gender</label>
        <select id="filter-gender" class="form-select">
          <option value="">All Genders</option>
          <option value="male">Male ♂️</option>
          <option value="female">Female ♀️</option>
          <option value="other">Other ⚧️</option>
        </select>
      </div>
      <div class="col-md-3 d-grid">
        <label class="invisible">Search</label>
        <button id="search-btn" class="btn btn-primary">🔍 Search</button>
      </div>
    </div>
  </div>
</div>


<a href="{{ route('contacts.create') }}" class="btn btn-success mb-2">Add New Contact</a>

<!-- Contacts Table -->
<!-- Contacts Table -->
<div id="contacts-table" class="table-responsive">
  <table class="table table-striped table-hover align-middle">
    <thead class="table-dark sticky-top">
      <tr>
        <th>Name</th>
        <th>Email</th>
        <th>Phone</th>
        <th>Gender</th>
        <th>Profile</th>
        <th class="text-end">Actions</th>
      </tr>
    </thead>
    <tbody>
      @forelse($contacts as $contact)
      @if($contact->is_merged == 0)
      <tr>
        <td class="fw-semibold">
          {{ $contact->name }}
          @if($contact->is_merged == 1)
          <small class="text-muted">(Merged)</small>
          @endif
        </td>
        <td>{{ $contact->email }}</td>
        <td>{{ $contact->phone ?? '-' }}</td>
        <td>
          <span class="badge bg-{{ $contact->gender === 'male' ? 'primary' : ($contact->gender === 'female' ? 'danger' : 'warning') }}">
            {{ ucfirst($contact->gender) }}
          </span>
        </td>
        <td>
          @if($contact->profile_image)
          <img src="{{ asset('storage/' . $contact->profile_image) }}" width="40" height="40" class="rounded-circle shadow-sm" alt="Profile">
          @else
          <span class="text-muted">N/A</span>
          @endif
        </td>
        <td class="text-end">
          <button type="button" class="btn btn-sm btn-info me-1 edit-btn"
            data-id="{{ $contact->id }}"
            data-name="{{ $contact->name }}"
            data-email="{{ $contact->email }}"
            data-phone="{{ $contact->phone }}"
            data-gender="{{ $contact->gender }}">
            ✏️
          </button>

          <form method="POST" action="{{ route('contacts.destroy', $contact->id) }}" class="d-inline ajax-delete-form">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-danger">🗑️</button>
          </form>

          <button type="button"
            class="btn btn-sm btn-outline-dark view-btn me-1"
            data-id="{{ $contact->id }}">
            👁️
          </button>
          @if($contact->is_merged == 0)
          <button type="button" class="btn btn-sm btn-secondary me-1 merge-btn"
            data-id="{{ $contact->id }}"
            data-name="{{ $contact->name }}">
            🔗
          </button>
          @endif
        </td>
      </tr>
      @endif
      @empty
      <tr>
        <td colspan="6" class="text-center text-muted py-3">No contacts found.</td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>


<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="editContactForm">
      @csrf
      @method('PUT')
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Contact</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="edit-contact-id">

          <div class="mb-3">
            <label>Name</label>
            <input type="text" id="edit-name" class="form-control" name="name" required>
          </div>
          <div class="mb-3">
            <label>Email</label>
            <input type="email" id="edit-email" class="form-control" name="email" required>
          </div>
          <div class="mb-3">
            <label>Phone</label>
            <input type="text" id="edit-phone" class="form-control" name="phone">
          </div>
          <div class="mb-3">
            <label>Gender</label>
            <select id="edit-gender" class="form-control" name="gender">
              <option value="male">Male</option>
              <option value="female">Female</option>
              <option value="other">Other</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Update</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- View Contact Modal -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Contact Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="view-contact-body">
          <div class="text-center py-4">
            <div class="spinner-border" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Merge Modal -->
<div class="modal fade" id="mergeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="mergeForm">
      @csrf
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Merge Contacts</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p class="text-muted">You're about to merge two contacts. Select the master contact to retain.</p>

          <input type="hidden" id="merge-secondary-id" name="secondary_id">

          <div class="mb-3">
            <label for="merge-master-id" class="form-label">Select Master Contact</label>
            <select name="master_id" id="merge-master-id" class="form-select" required>
              @foreach($contacts as $contact)
              @if($contact->is_merged == 0)
              <option value="{{ $contact->id }}">{{ $contact->name }} ({{ $contact->email }})</option>
              @endif
              @endforeach
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Confirm Merge</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </div>
    </form>
  </div>
</div>

@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  $(document).ready(function() {
    const viewModal = new bootstrap.Modal(document.getElementById('viewModal'));
    const editModal = new bootstrap.Modal(document.getElementById('editModal'));

    function loadContacts() {
      const name = $('#filter-name').val();
      const email = $('#filter-email').val();
      const gender = $('#filter-gender').val();

      $.get("{{ route('contacts.index') }}", {
        name,
        email,
        gender
      }, function(data) {
        $('#contacts-table').html(data);
      });
    }

    // Filter
    $('#search-btn').on('click', function() {
      loadContacts();
    });

    // Delete Contact
    $(document).on('submit', '.ajax-delete-form', function(e) {
      e.preventDefault();
      const form = $(this);
      if (!confirm('Are you sure?')) return;

      $.ajax({
        url: form.attr('action'),
        type: 'POST',
        data: form.serialize(),
        success: function() {
          alert('Deleted successfully');
          loadContacts();
        },
        error: function() {
          alert('Failed to delete');
        }
      });
    });

    // Open Edit Modal
    $(document).on('click', '.edit-btn', function() {
      const btn = $(this);
      $('#edit-contact-id').val(btn.data('id'));
      $('#edit-name').val(btn.data('name'));
      $('#edit-email').val(btn.data('email'));
      $('#edit-phone').val(btn.data('phone'));
      $('#edit-gender').val(btn.data('gender'));
      editModal.show();
    });

    // Submit Edit Form
    $('#editContactForm').on('submit', function(e) {
      e.preventDefault();
      const id = $('#edit-contact-id').val();
      const url = `/contacts/${id}`;
      const data = {
        _token: $('input[name="_token"]').val(),
        _method: 'PUT',
        name: $('#edit-name').val(),
        email: $('#edit-email').val(),
        phone: $('#edit-phone').val(),
        gender: $('#edit-gender').val()
      };

      $.post(url, data, function() {
        alert('Updated successfully');
        editModal.hide();
        loadContacts();
      }).fail(function() {
        alert('Failed to update');
      });
    });

    // Open Merge Model
    $(document).on('click', '.merge-btn', function() {
      const id = $(this).data('id');
      $('#merge-secondary-id').val(id);
      $('#mergeModal').modal('show');
    });

    // Submit Merge Form
    $('#mergeForm').on('submit', function(e) {
      e.preventDefault();
      const formData = $(this).serialize();

      $.post("{{ route('contacts.merge') }}", formData, function(res) {
        alert(res.message || 'Contacts merged successfully');
        $('#mergeModal').modal('hide');
        location.reload(); // or use loadContacts() if you're using AJAX
      }).fail(function() {
        alert('Failed to merge contacts');
      });
    });

    // View All Data In Show Modal
    $(document).on('click', '.view-btn', function() {
      const contactId = $(this).data('id');
      $('#view-contact-body').html('<div class="text-center py-4"><div class="spinner-border"></div></div>');
      viewModal.show();

      $.get(`/contacts/${contactId}`, function(data) {
        let html = `
                <div class="row mb-3">
                    <div class="col-md-6"><strong>Name:</strong> ${data.name}</div>
                    <div class="col-md-6"><strong>Email:</strong> ${data.email}</div>
                    <div class="col-md-6"><strong>Phone:</strong> ${data.phone || '-'}</div>
                    <div class="col-md-6"><strong>Gender:</strong> ${data.gender}</div>
                    <div class="col-md-12 my-2">
                        <strong>Custom Fields:</strong>
                        <ul class="list-group mt-2">`;

        if (data.custom_fields.length > 0) {
          data.custom_fields.forEach(field => {
            html += `<li class="list-group-item d-flex justify-content-between">
                                <span>${field.label}</span>
                                <span>${field.value || '<i class="text-muted">-</i>'}</span>
                            </li>`;
          });
        } else {
          html += `<li class="list-group-item text-muted">No custom fields available</li>`;
        }

        html += `</ul></div></div>`;

        $('#view-contact-body').html(html);
      });
    });

  });
</script>
@endsection