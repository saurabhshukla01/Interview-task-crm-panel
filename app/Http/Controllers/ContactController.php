<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\ContactCustomFieldValue;
use App\Models\CustomField;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        $contacts = Contact::query();

        if ($request->name) $contacts->where('name', 'like', "%{$request->name}%");
        if ($request->email) $contacts->where('email', 'like', "%{$request->email}%");
        if ($request->gender) $contacts->where('gender', $request->gender);

        $contacts = $contacts->get();

        // AJAX response for filters
        if ($request->ajax()) {
            return view('contacts.partials.table', compact('contacts'))->render();
        }

        return view('contacts.index', compact('contacts'));
    }

    public function show($id)
    {
        $contact = Contact::with(['customFields.customField'])->findOrFail($id);

        return response()->json([
            'id' => $contact->id,
            'name' => $contact->name,
            'email' => $contact->email,
            'phone' => $contact->phone,
            'gender' => ucfirst($contact->gender),
            'custom_fields' => $contact->customFields->map(function ($field) {
                return [
                    'label' => $field->customField->label ?? '',
                    'value' => $field->value,
                ];
            })
        ]);
    }


    public function create()
    {
        $customFields = CustomField::all();
        return view('contacts.create', compact('customFields'));
    }


    public function store(Request $request)
    {
        $data = $request->only(['name', 'email', 'phone', 'gender']);

        // Handle file uploads
        if ($request->hasFile('profile_image')) {
            $data['profile_image'] = $request->file('profile_image')->store('images', 'public');
        }
        if ($request->hasFile('additional_file')) {
            $data['additional_file'] = $request->file('additional_file')->store('documents', 'public');
        }

        // Create contact
        $contact = Contact::create($data);

        // ✅ Save predefined custom field values (optional, if coming from form)
        if ($request->has('custom_fields')) {
            foreach ($request->custom_fields as $fieldId => $value) {
                ContactCustomFieldValue::create([
                    'contact_id' => $contact->id,
                    'custom_field_id' => $fieldId,
                    'value' => $value
                ]);
            }
        }

        // ✅ Save dynamic new custom fields
        if ($request->has('custom_fields_new')) {
            foreach ($request->custom_fields_new as $index => $field) {
                // Check if custom field with same label and type exists
                $existingField = CustomField::where('label', $field['label'])
                    ->where('type', $field['type'])
                    ->first();

                if ($existingField) {
                    $customFieldId = $existingField->id;
                } else {
                    $newField = CustomField::create([
                        'label' => $field['label'],
                        'type' => $field['type']
                    ]);
                    $customFieldId = $newField->id;
                }

                ContactCustomFieldValue::create([
                    'contact_id' => $contact->id,
                    'custom_field_id' => $customFieldId,
                    'value' => $field['value']
                ]);
            }
        }

        return redirect()->route('contacts.index');
    }


    public function update(Request $request, $id)
    {
        $contact = Contact::findOrFail($id);
        $contact->update($request->only(['name', 'email', 'phone', 'gender']));

        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        $contact = Contact::with('customFields')->findOrFail($id);

        // Delete related custom field values
        $contact->customFields()->delete();

        // Delete contact
        $contact->delete();

        return redirect()->route('contacts.index')->with('success', 'Contact deleted successfully.');
    }

    public function merge(Request $request)
    {
        $request->validate([
            'master_id' => 'required|exists:contacts,id',
            'secondary_id' => 'required|exists:contacts,id|different:master_id',
        ]);

        $master = Contact::with('customFields')->findOrFail($request->master_id);
        $secondary = Contact::with('customFields')->findOrFail($request->secondary_id);

        // 1. Merge email
        if ($master->email !== $secondary->email && !str_contains($master->email, $secondary->email)) {
            $master->email .= ',' . $secondary->email;
        }

        // 2. Merge phone
        if ($master->phone !== $secondary->phone && !str_contains($master->phone, $secondary->phone)) {
            $master->phone .= ',' . $secondary->phone;
        }

        // 3. Merge custom fields
        $masterFields = $master->customFields->keyBy('custom_field_id');
        $secondaryFields = $secondary->customFields->keyBy('custom_field_id');

        foreach ($secondaryFields as $fieldId => $secondaryField) {
            $masterField = $masterFields[$fieldId] ?? null;

            if (!$masterField) {
                // 🔹 Field doesn't exist in master — create it
                ContactCustomFieldValue::create([
                    'contact_id' => $master->id,
                    'custom_field_id' => $fieldId,
                    'value' => $secondaryField->value,
                ]);
            } else {
                // 🔸 Field exists — check for differing values
                if (
                    $masterField->value !== $secondaryField->value &&
                    !str_contains($masterField->value, $secondaryField->value)
                ) {
                    // Append value, preserving both
                    $masterField->value .= ' | ' . $secondaryField->value;
                    $masterField->save();
                }
            }
        }

        // 4. Save master updates
        $master->save();

        // 5. Mark secondary contact as merged (inactive)
        $secondary->is_merged = true;
        $secondary->save();

        return response()->json([
            'success' => true,
            'message' => 'Contacts merged successfully.',
        ]);
    }
}
