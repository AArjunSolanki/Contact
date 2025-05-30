@foreach($contacts as $contact)
<div class="card p-3 mb-2">
    <label>
        <input type="radio" name="master_contact" value="{{ $contact->id }}"> 
        <strong>{{ $contact->name }}</strong>
        <br>Email: {{ $contact->email }}
        <br>Phone: {{ $contact->phone }}
        <br>Custom Fields:
        <ul>
            @foreach($contact->customFieldValues as $cfv)
                <li>{{ $cfv->customField->name }}: {{ $cfv->value }}</li>
            @endforeach
        </ul>
    </label>
</div>
@endforeach
