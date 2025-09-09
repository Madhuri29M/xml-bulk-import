<?php

namespace App\Http\Controllers;

use App\Exceptions\ContactException;
use App\Http\Requests\ContactCreateOrUpdateRequest;
use App\Models\Contact;
use App\Services\ContactService;
use Exception;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    private $contactService;

    public function __construct(ContactService $contactService)
    {
        $this->contactService = $contactService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('contact.index');
    }

    public function indexAjax(Request $request)
    {
        try {
            $response = $this->contactService->getContacts($request);
            return response()->json($response);
        } catch (ContactException $e) {
            return request()->json(['success' => false, 'message' => $e->getMessage()]);
        } catch (Exception $e) {
            return request()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ContactCreateOrUpdateRequest $request)
    {
        try {
            $this->contactService->createOrUpdateContact($request);
            return response()->json(['success' => true, 'message' => 'Contact has been created successfully']);
        } catch (ContactException $e) {
            return request()->json(['success' => false, 'message' => $e->getMessage()]);
        } catch (Exception $e) {
            return request()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    /**
     * Display the specified resource.
     */
    public function show(Contact $contact)
    {
        try {
            return response()->json(['success' => true, 'message' => 'Contact has been created successfully', 'data' => $contact]);
        } catch (ContactException $e) {
            return request()->json(['success' => false, 'message' => $e->getMessage()]);
        } catch (Exception $e) {
            return request()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ContactCreateOrUpdateRequest $request, Contact $contact)
    {
        try {
            $this->contactService->createOrUpdateContact($request, $contact);
            return response()->json(['success' => true, 'message' => 'Contact has been updated successfully']);
        } catch (ContactException $e) {
            return request()->json(['success' => false, 'message' => $e->getMessage()]);
        } catch (Exception $e) {
            return request()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contact $contact)
    {
        try {
            $this->contactService->deleteContact($contact);
            return response()->json(['success' => true, 'message' => 'Contact has been deleted successfully']);
        } catch (ContactException $e) {
            return request()->json(['success' => false, 'message' => $e->getMessage()]);
        } catch (Exception $e) {
            return request()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
