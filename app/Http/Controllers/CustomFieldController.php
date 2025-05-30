<?php

namespace App\Http\Controllers;

use App\Models\ContactCustomField;
use Illuminate\Http\Request;

class CustomFieldController extends Controller
{
    public function index()
    {
        $fields = ContactCustomField::all();
        return view('contacts.custom_fields', compact('fields'));
    }

   public function storeAjax(Request $request)
    {
        $request->validate([
            'label' => 'required|string',
            'type' => 'required|in:text,date,number'
        ]);

        $field = ContactCustomField::create($request->only('label', 'type'));

        return response()->json(['success' => true, 'field' => $field]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'label' => 'required|string',
            'type' => 'required|in:text,date,number'
        ]);

        $field = ContactCustomField::findOrFail($id);
        $field->update($request->only('label', 'type'));

        return response()->json(['success' => true, 'field' => $field]);
    }

    public function destroy($id)
    {
        ContactCustomField::destroy($id);
        return redirect()->back()->with('success', 'Custom field deleted successfully!');
    }
}

