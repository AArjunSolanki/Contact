<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\ContactCustomField;
use App\Models\ContactFieldValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ContactController extends Controller
{
    public function index()
    {
        $contacts = Contact::with('customFieldValues.customField')->get();
        $fields = ContactCustomField::all();
        return view('contacts.index', compact('contacts', 'fields'));
    }
    public function getCustomFields($id)
    {
        $contact = Contact::with('customFieldValues')->findOrFail($id);

        $values = [];
        foreach ($contact->customFieldValues as $fieldValue) {
            $values[$fieldValue->custom_field_id] = $fieldValue->value;
        }

        return response()->json($values);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
        'name' => 'required',
        'email' => [
            'required',
            'email',
            'unique:contacts,email', 
        ],
        'phone' => 'required',
        'gender' => 'nullable',
        'profile_image' => 'nullable|image',
        'additional_file' => 'nullable|file',
        ], [
            'email.email' => 'The email format is invalid.',
            'email.unique' => 'This email is already in use.',
        ]);

        if ($request->hasFile('profile_image')) {
            $data['profile_image'] = $request->file('profile_image')->store('profiles', 'public');
        }

        if ($request->hasFile('additional_file')) {
            $data['additional_file'] = $request->file('additional_file')->store('files', 'public');
        }

        $contact = Contact::create($data);

        foreach ($request->custom_fields ?? [] as $field_id => $value) {
            ContactFieldValue::create([
                'contact_id' => $contact->id,
                'custom_field_id' => $field_id,
                'value' => $value
            ]);
        }

        return redirect()->route('contacts.index')->with('success', 'Contact created successfully.');
    }
    public function ajaxMergePreview($masterId, $secondaryId)
    {
        $master = Contact::findOrFail($masterId);
        $secondary = Contact::findOrFail($secondaryId);

        $mergedFields = [];

        $fields = ['email', 'phone'];

        foreach ($fields as $field) {
            $masterVal = $master->$field;
            $secondaryVal = $secondary->$field;
            $mergedFields[$field] = [
                'master' => $masterVal,
                'secondary' => $secondaryVal,
                'conflict' => $masterVal && $secondaryVal && $masterVal !== $secondaryVal
            ];
        }

        $masterCustom = json_decode($master->custom_fields, true) ?? [];
        $secondaryCustom = json_decode($secondary->custom_fields, true) ?? [];


        foreach ($secondaryCustom as $key => $value) {
            $masterValue = $masterCustom[$key] ?? null;
            $mergedFields["custom_field: $key"] = [
                'master' => $masterValue,
                'secondary' => $value,
                'conflict' => $masterValue && $value && $masterValue !== $value
            ];
        }

        return response()->json([
            'master' => $master,
            'secondary' => $secondary,
            'merged_fields' => $mergedFields
        ]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'phone' => 'required',
            'gender' => 'nullable',
            'profile_image' => 'nullable|image',
            'additional_file' => 'nullable|file',
        ]);

        $contact = Contact::findOrFail($id);

        if ($request->hasFile('profile_image')) {
            $data['profile_image'] = $request->file('profile_image')->store('profiles', 'public');
        }

        if ($request->hasFile('additional_file')) {
            $data['additional_file'] = $request->file('additional_file')->store('files', 'public');
        }

        $contact->update($data);

        foreach ($request->custom_fields ?? [] as $field_id => $value) {
            ContactFieldValue::updateOrCreate(
                ['contact_id' => $contact->id, 'custom_field_id' => $field_id],
                ['value' => $value]
            );
        }

        return response()->json(['success' => true, 'message' => 'Contact updated successfully.']);
    }

    public function destroy($id)
    {
        Contact::destroy($id);
        return response()->json(['success' => true, 'message' => 'Contact deleted.']);
    }
    public function mergeInit(Request $request)
    {
        $primaryId = $request->contact_id;
        $primary = Contact::findOrFail($primaryId);

        $others = Contact::where('id', '!=', $primaryId)->whereNull('merged_into')->get();

        return view('contacts.merge_select', compact('primary', 'others'));
    }
  
    public function doMerge(Request $request)
    {
        $request->validate([
            'master_id' => 'required|exists:contacts,id',
            'contactB' => 'required|exists:contacts,id'
        ]);

        $master = Contact::findOrFail($request->master_id);
        $secondary = Contact::findOrFail($request->contactB);

        $emails = collect([$master->email])
            ->merge($master->alternate_emails ?? [])
            ->merge([$secondary->email])
            ->merge($secondary->alternate_emails ?? [])
            ->unique()
            ->values();

        $master->alternate_emails = $emails->reject(fn($email) => $email === $master->email)->values();

        $phones = collect([$master->phone])
            ->merge($master->alternate_phones ?? [])
            ->merge([$secondary->phone])
            ->merge($secondary->alternate_phones ?? [])
            ->unique()
            ->values();

        $master->alternate_phones = $phones->reject(fn($phone) => $phone === $master->phone)->values();

         $secondaryFieldValues = ContactFieldValue::where('contact_id', $secondary->id)->get();
        foreach ($secondaryFieldValues as $fieldValue) {
            $existsForMaster = ContactFieldValue::where('contact_id', $master->id)
                ->where('custom_field_id', $fieldValue->custom_field_id)
                ->exists();

            if ($existsForMaster) {
                $fieldValue->contact_id = $master->id;
                $fieldValue->save();
            }
        }

        $master->save();

        $secondary->is_merged = true;
        $secondary->merged_into = $master->id;
        $secondary->save();

        return redirect()->route('contacts.index')->with('success', 'Contacts successfully merged.');
    }

    public function ajaxCompare($idA, $idB)
    {
        $contactA = Contact::with('customFieldValues.customField')->findOrFail($idA);
        $contactB = Contact::with('customFieldValues.customField')->findOrFail($idB);

        return response()->json([
            'contactA' => $contactA,
            'contactB' => $contactB,
        ]);
    }


    public function filter(Request $request)
    {
        $query = Contact::query();

        if ($request->name) $query->where('name', 'like', "%{$request->name}%");
        if ($request->email) $query->where('email', 'like', "%{$request->email}%");
        if ($request->gender) $query->where('gender', $request->gender);

        $contacts = $query->with('customFieldValues.customField')->get();

        return view('contacts.partials.table', compact('contacts'))->render();
    }
}

