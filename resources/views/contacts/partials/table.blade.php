<table class="table table-striped table-hover align-middle mb-0">
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
        @empty
        <tr>
            <td colspan="6" class="text-center text-muted py-3">No contacts found.</td>
        </tr>
        @endforelse
    </tbody>
</table>
