@foreach($contacts as $contact)
<div class="form-check">
    <input class="form-check-input" type="radio" name="master_contact" value="{{ $contact->id }}">
    <label class="form-check-label">
        {{ $contact->name }} ({{ $contact->email }})
    </label>
</div>
@endforeach
