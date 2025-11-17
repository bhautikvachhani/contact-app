<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Models\Contact;
use App\Services\ContactService;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    protected $contactService;

    public function __construct(ContactService $contactService)
    {
        $this->contactService = $contactService;
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $filters = $request->only(['name', 'email', 'gender', 'custom_field', 'custom_value']);
            $perPage = $request->get('per_page', 5);
            $contacts = $this->contactService->getAllContacts($filters, $perPage);
            
            return response()->json([
                'success' => true,
                'data' => $contacts->items(),
                'pagination' => [
                    'current_page' => $contacts->currentPage(),
                    'last_page' => $contacts->lastPage(),
                    'per_page' => $contacts->perPage(),
                    'total' => $contacts->total(),
                    'from' => $contacts->firstItem(),
                    'to' => $contacts->lastItem()
                ],
                'html' => view('contacts.partials.contact_list', compact('contacts'))->render()
            ]);
        }

        $contacts = $this->contactService->getAllContacts();
        $customFields = $this->contactService->getCustomFieldNames();
        return view('contacts.index', compact('contacts', 'customFields'));
    }

    public function store(ContactRequest $request)
    {
        try {
            $contact = $this->contactService->createContact($request->validated());
            
            return response()->json([
                'success' => true,
                'message' => 'Contact created successfully',
                'data' => $contact
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating contact: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(Contact $contact)
    {
        return response()->json([
            'success' => true,
            'data' => $contact
        ]);
    }

    public function update(ContactRequest $request, Contact $contact)
    {
        try {
            $updatedContact = $this->contactService->updateContact($contact, $request->validated());
            
            return response()->json([
                'success' => true,
                'message' => 'Contact updated successfully',
                'data' => $updatedContact
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating contact: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Contact $contact)
    {
        try {
            $this->contactService->deleteContact($contact);
            
            return response()->json([
                'success' => true,
                'message' => 'Contact deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting contact: ' . $e->getMessage()
            ], 500);
        }
    }

    public function merge(Request $request)
    {
        $request->validate([
            'master_id' => 'required|exists:contacts,id',
            'secondary_id' => 'required|exists:contacts,id|different:master_id'
        ]);

        try {
            $masterContact = Contact::findOrFail($request->master_id);
            $secondaryContact = Contact::findOrFail($request->secondary_id);

            $mergedContact = $this->contactService->mergeContacts($masterContact, $secondaryContact);

            return response()->json([
                'success' => true,
                'message' => 'Contacts merged successfully. Secondary contact preserved with merge flag.',
                'data' => $mergedContact
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error merging contacts: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getActiveContacts()
    {
        $contacts = $this->contactService->getActiveContactsForMerge();
        
        return response()->json([
            'success' => true,
            'data' => $contacts
        ]);
    }

    public function getCustomFields()
    {
        $customFields = $this->contactService->getCustomFieldNames();
        
        return response()->json([
            'success' => true,
            'data' => $customFields
        ]);
    }

    public function getMergedData($masterId)
    {
        $mergedContacts = $this->contactService->getMergedContactsData($masterId);
        
        return response()->json([
            'success' => true,
            'data' => $mergedContacts
        ]);
    }
}
