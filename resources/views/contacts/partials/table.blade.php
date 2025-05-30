<table class="table table-bordered table-striped">
    <thead class="table-dark">
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Gender</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
       @forelse ($contacts as $contact)
    <tr class="{{ $contact->is_merged ? 'table-warning' : '' }}">
        <td>
            {{ $contact->name }}
            
        </td>
        <td>{{ $contact->email }}</td>
        <td>{{ $contact->phone }}</td>
        <td>{{ $contact->gender }}</td>
        <td>
            @if ($contact->is_merged)
                <button class="btn btn-secondary btn-sm" disabled>Merged</button>
            @else
                <form method="POST" action="{{ route('contacts.mergeInit') }}" style="display:inline-block;">
                    @csrf
                    <input type="hidden" name="contact_id" value="{{ $contact->id }}">
                    <button type="submit" class="btn btn-warning btn-sm">Merge</button>
                </form>
            @endif
            <button class="btn btn-sm btn-info edit-contact"
                data-id="{{ $contact->id }}"
                data-name="{{ $contact->name }}"
                data-email="{{ $contact->email }}"
                data-phone="{{ $contact->phone }}"
                data-gender="{{ $contact->gender }}"
            > Edit </button>
            <button class="btn btn-sm btn-danger delete-btn" data-id="{{ $contact->id }}">Delete</button>
        </td>
    </tr>
@empty
 <tr>
        <td colspan="5" class="text-center">No contacts found.</td>
    </tr>
@endforelse

    </tbody>
</table>
