<?php

namespace App\Services;

use App\Models\Contact;
use Illuminate\Http\UploadedFile;

class ContactService
{
    public function getAllContacts($filters = [], $perPage = 5)
    {
        $query = Contact::query(); // Only show non-soft-deleted contacts in main list

        if (!empty($filters['name'])) {
            $query->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($filters['name']) . '%']);
        }

        if (!empty($filters['email'])) {
            $query->where(function($q) use ($filters) {
                $q->whereRaw('LOWER(email) LIKE ?', ['%' . strtolower($filters['email']) . '%'])
                  ->orWhereRaw('LOWER(JSON_EXTRACT(custom_fields, "$")) LIKE ?', ['%email_%' . strtolower($filters['email']) . '%']);
            });
        }

        if (!empty($filters['gender'])) {
            $query->where('gender', $filters['gender']);
        }

        if (!empty($filters['custom_field']) && !empty($filters['custom_value'])) {
            $query->whereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(custom_fields, CONCAT("$.", ?)))) LIKE ?', [$filters['custom_field'], '%' . strtolower($filters['custom_value']) . '%']);
        } elseif (!empty($filters['custom_value'])) {
            // Search in all custom field values if no specific field selected
            $query->whereRaw('LOWER(JSON_EXTRACT(custom_fields, "$")) LIKE ?', ['%' . strtolower($filters['custom_value']) . '%']);
        }

        return $query->orderByRaw('CASE WHEN is_merged = 1 OR (SELECT COUNT(*) FROM contacts c2 WHERE c2.merged_into = contacts.id) > 0 THEN 0 ELSE 1 END, CASE WHEN is_merged = 0 THEN id ELSE merged_into END, is_merged ASC, created_at DESC')->paginate($perPage);
    }

    public function getActiveContactsForMerge()
    {
        return Contact::where('is_merged', false)
                     ->orderBy('name')
                     ->get(['id', 'name', 'email']);
    }

    public function getMergedContactsData($masterId)
    {
        return Contact::withTrashed()
                     ->where('merged_into', $masterId)
                     ->where('is_merged', true)
                     ->get(['id', 'name', 'email', 'phone', 'gender', 'merged_data', 'updated_at']);
    }

    public function getCustomFieldNames()
    {
        return Contact::withTrashed()
            ->whereNotNull('custom_fields')
            ->get()
            ->pluck('custom_fields')
            ->filter()
            ->flatMap(function ($fields) {
                return array_keys($fields);
            })
            ->map(function($field) {
                return (string) $field;
            })
            ->filter(function($field) {
                return !empty(trim($field));
            })
            ->unique()
            ->values();
    }

    public function createContact(array $data)
    {
        try {
            if (isset($data['profile_image'])) {
                $data['profile_image'] = $this->uploadFile($data['profile_image'], 'profile_images');
            }

            if (isset($data['additional_file'])) {
                $data['additional_file'] = $this->uploadFile($data['additional_file'], 'additional_files');
            }

            return Contact::create($data);
        } catch (\Exception $e) {
            throw new \Exception('Upload failed: ' . $e->getMessage());
        }
    }

    public function updateContact(Contact $contact, array $data)
    {
        if (isset($data['profile_image']) && $data['profile_image']) {
            if ($contact->profile_image) {
                $this->deleteFile($contact->profile_image);
            }
            $data['profile_image'] = $this->uploadFile($data['profile_image'], 'profile_images');
        } else {
            // Keep existing profile image if no new one uploaded
            unset($data['profile_image']);
        }

        if (isset($data['additional_file']) && $data['additional_file']) {
            if ($contact->additional_file) {
                $this->deleteFile($contact->additional_file);
            }
            $data['additional_file'] = $this->uploadFile($data['additional_file'], 'additional_files');
        } else {
            // Keep existing additional file if no new one uploaded
            unset($data['additional_file']);
        }

        $contact->update($data);
        return $contact;
    }

    public function deleteContact(Contact $contact)
    {
        return $contact->delete(); // Soft delete - files preserved
    }

    public function mergeContacts(Contact $masterContact, Contact $secondaryContact)
    {
        // Prevent merging already merged contacts
        if ($masterContact->is_merged || $secondaryContact->is_merged) {
            throw new \Exception('Cannot merge contacts that are already merged. Only active contacts can be merged.');
        }
        
        // Store original data of secondary contact before merge
        $originalSecondaryData = $secondaryContact->toArray();
        
        // Merge custom fields intelligently
        $masterCustomFields = $masterContact->custom_fields ?? [];
        $secondaryCustomFields = $secondaryContact->custom_fields ?? [];

        foreach ($secondaryCustomFields as $field => $value) {
            if (!isset($masterCustomFields[$field])) {
                // Add new field from secondary contact
                $masterCustomFields[$field] = $value;
            } elseif ($masterCustomFields[$field] !== $value) {
                // Merge different values with separator
                $masterCustomFields[$field] = $masterCustomFields[$field] . ' | ' . $value;
            }
            // If values are same, keep master's value (no change needed)
        }

        // Handle additional phone numbers
        if ($masterContact->phone !== $secondaryContact->phone) {
            // Find next available phone slot
            $phoneIndex = 1;
            while (isset($masterCustomFields['phone_' . $phoneIndex])) {
                $phoneIndex++;
            }
            $masterCustomFields['phone_' . $phoneIndex] = $secondaryContact->phone;
        }

        // Handle additional email addresses
        if ($masterContact->email !== $secondaryContact->email) {
            // Find next available email slot
            $emailIndex = 1;
            while (isset($masterCustomFields['email_' . $emailIndex])) {
                $emailIndex++;
            }
            $masterCustomFields['email_' . $emailIndex] = $secondaryContact->email;
        }

        // Update master contact with merged data
        $masterContact->update([
            'custom_fields' => $masterCustomFields
        ]);

        // Mark secondary contact as merged but DON'T DELETE
        // Keep all original data for audit trail
        $secondaryContact->update([
            'is_merged' => true,
            'merged_into' => $masterContact->id,
            'merged_data' => $originalSecondaryData // Store complete original record
        ]);

        return $masterContact;
    }

    private function uploadFile(UploadedFile $file, string $directory): string
    {
        try {
            $uploadPath = public_path('uploads/' . $directory);
            
            // Create directory if it doesn't exist
            if (!file_exists($uploadPath)) {
                if (!mkdir($uploadPath, 0755, true)) {
                    throw new \Exception('Failed to create upload directory: ' . $uploadPath);
                }
            }
            
            // Check if directory is writable
            if (!is_writable($uploadPath)) {
                throw new \Exception('Upload directory is not writable: ' . $uploadPath);
            }
            
            $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file->getClientOriginalName());
            
            if (!$file->move($uploadPath, $filename)) {
                throw new \Exception('Failed to move uploaded file to: ' . $uploadPath . '/' . $filename);
            }
            
            return 'uploads/' . $directory . '/' . $filename;
        } catch (\Exception $e) {
            throw new \Exception('File upload error: ' . $e->getMessage());
        }
    }

    private function deleteFile(string $filePath): void
    {
        $fullPath = public_path($filePath);
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
    }
}