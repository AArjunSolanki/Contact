@extends('layouts.app')

@section('content')
<h5>Select a contact to merge with: {{ $primary->name }}</h5>

<form method="POST">
    @csrf
    <input type="hidden" name="primary_id" id="primary_id" value="{{ $primary->id }}">

    <div class="row align-items-center mb-4">
        <div class="col-md-6">
            <div class="bg-light">
                <h6>Primary Contact:</h6>
                {{ $primary->name }}
            </div>
        </div>
        <div class="col-md-6">
            <label for="secondary_id" class="form-label">Select Contact to Merge With:</label>
            <select class="form-select" id="secondary_id" required>
                <option value="">-- Choose Contact --</option>
                @foreach($others as $contact)
                    <option value="{{ $contact->id }}">{{ $contact->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <button type="button" class="btn btn-primary" id="compareButton">Next</button>
</form>
<div class="modal fade" id="compareModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <form method="POST" action="{{ route('contacts.doMerge') }}">
        @csrf
        <input type="hidden" name="contactA" id="contactA_id">
        <input type="hidden" name="contactB" id="contactB_id">

        <div class="modal-header">
          <h5 class="modal-title">Compare & Choose Master Contact</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body" id="compare-body">
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.getElementById('compareButton').addEventListener('click', function () {
    const primaryId = document.getElementById('primary_id').value;
    const secondaryId = document.getElementById('secondary_id').value;

    if (!secondaryId) {
        alert("Please select a contact to merge with.");
        return;
    }

    document.getElementById('contactA_id').value = primaryId;
    document.getElementById('contactB_id').value = secondaryId;

    fetch(`/contacts/ajax-compare/${primaryId}/${secondaryId}`)
        .then(res => res.json())
        .then(data => {
             const customFieldsA = data.contactA.custom_field_values.map(fieldVal => {
                const label = fieldVal.custom_field ? fieldVal.custom_field.label : 'N/A';
                return `<li><strong>${label}:</strong> ${fieldVal.value}</li>`;
            }).join('');

            const customFieldsB = data.contactB.custom_field_values.map(fieldVal => {
                const label = fieldVal.custom_field ? fieldVal.custom_field.label : 'N/A';
                return `<li><strong>${label}:</strong> ${fieldVal.value}</li>`;
            }).join('');
           document.getElementById('compare-body').innerHTML = `
            <div class="row">
                <div class="col">
                <h5>${data.contactA.name}</h5>
                Email: ${data.contactA.email}<br>
                Phone: ${data.contactA.phone}<br>
                Custom Fields:<br>
                <ul>${customFieldsA || '<li>No fields</li>'}</ul>
                <button type="button" class="btn btn-success mt-2 choose-master" data-master="${data.contactA.id}">Make Master</button>
                </div>
                <div class="col">
                <h5>${data.contactB.name}</h5>
                Email: ${data.contactB.email}<br>
                Phone: ${data.contactB.phone}<br>
                Custom Fields:<br>
                <ul>${customFieldsB || '<li>No fields</li>'}</ul>
                <button type="button" class="btn btn-success mt-2 choose-master" data-master="${data.contactB.id}">Make Master</button>
                </div>
            </div>
            `;


            new bootstrap.Modal(document.getElementById('compareModal')).show();
        });
});
document.addEventListener('click', function (e) {
    if (e.target.classList.contains('choose-master')) {
        const masterId = e.target.getAttribute('data-master');
        const contactA = document.getElementById('contactA_id').value;
        const contactB = document.getElementById('contactB_id').value;

        const [primary, secondary] = masterId === contactA
            ? [contactA, contactB]
            : [contactB, contactA];

        fetch(`/contacts/ajax-merge-preview/${primary}/${secondary}`)
            .then(res => res.json())
            .then(data => {
        let previewHtml = `
            <h5 class="mb-3">Final Merge Preview</h5>
            
            <div class="row mb-4">
                <div class="col border p-3 bg-light">
                    <h6>Master Contact</h6>
                    <strong>${data.master.name}</strong><br>
                    Email: ${data.master.email} <br>
                    Phone: ${data.master.phone} <br>
                </div>
                <div class="col border p-3 bg-light">
                    <h6 >Primary Contact (Will be merged into master)</h6>
                    <strong>${data.secondary.name}</strong><br>
                    Email: ${data.secondary.email} <br>
                    Phone: ${data.secondary.phone} <br>
                </div>
            </div>

            <div class="card p-3 mb-3">
                <h6>Merged Fields Overview:</h6>
        `;

        for (const [key, val] of Object.entries(data.merged_fields)) {
                    if (val.conflict) {
                        previewHtml += `<div><b>${key}</b>: <span >${val.master} vs ${val.secondary}</span></div>`;
                    } else {
                        previewHtml += `<div><b>${key}</b>: ${val.master || val.secondary}</div>`;
                    }
                }


        previewHtml += `
            </div>
            <input type="hidden" name="master_id" value="${data.master.id}">
            <input type="hidden" name="contactA" value="${data.master.id}">
            <input type="hidden" name="contactB" value="${data.secondary.id}">
            <button type="submit" class="btn btn-primary">Confirm Merge</button>
        `;

        document.getElementById('compare-body').innerHTML = previewHtml;
    });

    }
});

</script>


@endsection
