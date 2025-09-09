<?php
namespace App\Services;

use App\Exceptions\ContactException;
use App\Models\Contact;
use Illuminate\Support\Facades\DB;
use Exception;

class ContactService {

    public function getAjaxSetup($query, $request)
    {
        return [
            $query->count(),
            $request->all(),
            $request['draw'],
            $request['start'],
            $request['length'],
            !$request['columns'][$request['order'][0]['column']]['name'] ? $request['columns'][$request['order'][0]['column']]['data'] : $request['columns'][$request['order'][0]['column']]['name'],
            $request['order'][0]['dir'],
            $request['search']['value']
        ];
    }

    public function getAjaxResponse($draw, $totalRecords, $totalRecordWithFilter, $data)
    {
        return array(
            "draw" => intval($draw),
            "iTotalRecords" => $totalRecords,
            "iTotalDisplayRecords" => $totalRecordWithFilter,
            "aaData" => $data
        );
    }

    /**
     * Get contact by id
     *
     * @param integer $id
     *
     * @return object
     */
    public function getContactById($id): object
    {
        $contact = Contact::find($id)->first();

        if ($contact) {
            return $contact;
        }

        throw new ContactException('No Contacts Found');
    }

    /**
     * Create or update contact
     *
     * @param App\Http\Requests\Request $request
     * @param integer $contactId
     *
     * @return void
     */
    public function createOrUpdateContact($request, $contactId = null): void
    {
        try {
            DB::beginTransaction();

            if ($contactId) {
                $contact = $this->getContactById($contactId);
            } else {
                $contact = new Contact;
            }
            $contact->name = $request->name;
            $contact->phone = $request->phone;
            $contact->save();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get filtered data by search
     *
     * @param Builder $query
     * @param string $searchValue
     *
     * @return Builder $query
     */
    private function getDataBySearch($query, $searchValue)
    {
        return $query->where(function($q) use ($searchValue) {
            $q->where('id', 'like', '%' . $searchValue . '%')
            ->orWhere('name', 'like', '%' . $searchValue . '%')
            ->orWhere('phone', 'like', '%' . $searchValue . '%');
        });
    }

    /**
     * Get contact in pagination
     *
     * @param App\Http\Requests\Request $request
     *
     * @return array $response
     */
    public function getContacts($request): array
    {
        $query = Contact::query();
        list(
            $totalRecords,
            $request,
            $draw,
            $row,
            $rowPerPage,
            $columnName,
            $columnSortOrder,
            $searchValue
        ) = $this->getAjaxSetup($query, $request);

        $query  = $searchValue != '' ? $this->getDataBySearch($query, $searchValue) : $query;

        // Total number of record with filtering
        $totalRecordwithFilter = $query->count();

        $contacts = $query->orderBy($columnName, $columnSortOrder)->offset($row)->limit($rowPerPage)->get();
        $data = $contacts->map(function ($contact) {
            return [
                'id'        => $contact->id,
                'name'      => $contact->name,
                'phone'     => $contact->phone,
            ];
        });


        return $this->getAjaxResponse(
            $draw,
            $totalRecords,
            $totalRecordwithFilter,
            $data
        );
    }

    /**
     * Delete contact by id
     *
     * @param int|string $id
     */
    public function deleteContact($id) {
        try {
            DB::beginTransaction();

            $contact = $this->getContactById($id);
            $contact->delete();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    
}
