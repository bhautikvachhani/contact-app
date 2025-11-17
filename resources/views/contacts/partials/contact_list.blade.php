<div class="table-responsive">
    <table class="table table-hover">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Profile</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Gender</th>
                <th>Custom Fields</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($contacts as $contact)
                <tr class="{{ $contact->is_merged ? 'table-warning' : ($contact->mergedContacts->count() > 0 ? 'table-info' : '') }}">
                    <td class="fw-bold">#{{ $contact->id }}</td>
                    <td>
                        @if($contact->profile_image)
                            <img src="{{ asset($contact->profile_image) }}" alt="Profile" class="rounded-circle" width="40" height="40" style="object-fit: cover;">
                        @else
                            <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fas fa-user text-white"></i>
                            </div>
                        @endif
                    </td>
                    <td class="fw-semibold">{{ $contact->name }}</td>
                    <td>
                        {{ $contact->email }}
                        @if($contact->custom_fields)
                            @foreach($contact->custom_fields as $key => $value)
                                @if(substr($key, 0, 6) === 'email_')
                                    <br><small class="text-muted">+ {{ $value }}</small>
                                @endif
                            @endforeach
                        @endif
                    </td>
                    <td>
                        {{ $contact->phone }}
                        @if($contact->custom_fields)
                            @foreach($contact->custom_fields as $key => $value)
                                @if(substr($key, 0, 6) === 'phone_')
                                    <br><small class="text-muted">+ {{ $value }}</small>
                                @endif
                            @endforeach
                        @endif
                    </td>
                    <td>
                        <span class="badge bg-{{ $contact->gender === 'male' ? 'primary' : ($contact->gender === 'female' ? 'danger' : 'secondary') }}">
                            {{ ucfirst($contact->gender) }}
                        </span>
                    </td>
                    <td style="max-width: 250px;">
                        @if($contact->custom_fields && count($contact->custom_fields) > 0)
                            @php $fieldCount = count($contact->custom_fields); @endphp
                            <div class="d-flex flex-wrap gap-1">
                                @if($fieldCount <= 3)
                                    @foreach($contact->custom_fields as $field => $value)
                                        <span class="badge bg-info custom-field-badge" title="{{ $field }}: {{ $value }}">
                                            {{ $field }}: {{ Str::limit($value, 15) }}
                                        </span>
                                    @endforeach
                                @else
                                    @foreach(array_slice($contact->custom_fields, 0, 2, true) as $field => $value)
                                        <span class="badge bg-info custom-field-badge" title="{{ $field }}: {{ $value }}">
                                            {{ $field }}: {{ Str::limit($value, 15) }}
                                        </span>
                                    @endforeach
                                    <button class="btn btn-sm btn-outline-info" onclick="viewAllCustomFields({{ $contact->id }})" title="View all custom fields">
                                        +{{ $fieldCount - 2 }} more
                                    </button>
                                @endif
                            </div>
                        @else
                            <span class="text-muted">None</span>
                        @endif
                        
                        @if($contact->is_merged)
                            <div class="mt-1">
                                <span class="badge bg-warning text-dark">
                                    <i class="fas fa-code-branch me-1"></i>Merged into #{{ $contact->merged_into }}
                                </span>
                            </div>
                        @endif
                        
                        @php
                            $mergedCount = $contact->mergedContacts()->where('is_merged', true)->count();
                        @endphp
                        @if($mergedCount > 0)
                            <div class="mt-1">
                                <span class="badge bg-success">
                                    <i class="fas fa-users me-1"></i>{{ $mergedCount }} merged here
                                </span>
                            </div>
                        @endif
                    </td>
                    <td>
                        <div class="btn-group" role="group">
                            <button class="btn btn-sm btn-outline-primary" onclick="editContact({{ $contact->id }})" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteContact({{ $contact->id }})" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                            @if(!$contact->is_merged)
                                <button class="btn btn-sm btn-outline-warning" onclick="initiateMerge({{ $contact->id }})" title="Merge with another contact">
                                    <i class="fas fa-code-branch"></i>
                                </button>
                            @endif
                            @if($mergedCount > 0)
                                <button class="btn btn-sm btn-info" onclick="viewMergedContacts({{ $contact->id }})" title="View merged contacts">
                                    <i class="fas fa-eye me-1"></i>{{ $mergedCount }}
                                </button>
                                <button class="btn btn-sm btn-success" onclick="viewPreservedData({{ $contact->id }})" title="View preserved secondary data">
                                    <i class="fas fa-database me-1"></i>Data
                                </button>
                            @endif
                            @if($contact->is_merged)
                                <button class="btn btn-sm btn-warning" onclick="viewMasterContact({{ $contact->merged_into }})" title="View master contact details">
                                    <i class="fas fa-crown me-1"></i>Master
                                </button>
                            @endif
                        </div>
                        @if($contact->additional_file)
                            <div class="mt-1">
                                <a href="{{ asset($contact->additional_file) }}" target="_blank" class="btn btn-sm btn-outline-info">
                                    <i class="fas fa-file me-1"></i>File
                                </a>
                            </div>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">
                        <i class="fas fa-users fa-2x mb-2 d-block"></i>
                        No contacts found
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($contacts->hasPages())
<div class="d-flex justify-content-between align-items-center mt-2">
    <small class="text-muted">
        {{ $contacts->firstItem() }}-{{ $contacts->lastItem() }} of {{ $contacts->total() }}
    </small>
    <nav>
        <ul class="pagination pagination-sm mb-0" style="--bs-pagination-padding-y: 0.25rem; --bs-pagination-padding-x: 0.5rem; --bs-pagination-font-size: 0.75rem;">
            @if($contacts->onFirstPage())
                <li class="page-item disabled"><span class="page-link">‹</span></li>
            @else
                <li class="page-item"><a class="page-link" href="#" onclick="loadPage({{ $contacts->currentPage() - 1 }})">‹</a></li>
            @endif
            
            @foreach($contacts->getUrlRange(1, $contacts->lastPage()) as $page => $url)
                @if($page == $contacts->currentPage())
                    <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                @else
                    <li class="page-item"><a class="page-link" href="#" onclick="loadPage({{ $page }})">{{ $page }}</a></li>
                @endif
            @endforeach
            
            @if($contacts->hasMorePages())
                <li class="page-item"><a class="page-link" href="#" onclick="loadPage({{ $contacts->currentPage() + 1 }})">›</a></li>
            @else
                <li class="page-item disabled"><span class="page-link">›</span></li>
            @endif
        </ul>
    </nav>
</div>
@endif