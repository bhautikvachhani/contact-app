<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Contact Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css" rel="stylesheet">
    <style>
        .gradient-bg { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .card { box-shadow: 0 10px 30px rgba(0,0,0,0.1); border: none; border-radius: 15px; }
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
        .search-section { background: #f8f9fa; border-radius: 10px; padding: 15px; margin-bottom: 15px; }
        .table th { background: #f8f9fa; font-weight: 600; }
        .custom-field-row { background: #f8f9fa; padding: 15px; border-radius: 10px; margin-bottom: 10px; }
        .table-warning { background-color: #fff3cd !important; }
        .table-info { background-color: #d1ecf1 !important; }
        .table-primary { background-color: #cfe2ff !important; }
        .badge { font-size: 0.75em; }
        .custom-field-badge { max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; display: inline-block; }
        .btn-group .btn { margin-right: 2px; }
        .modal-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .modal-header .btn-close { filter: invert(1); }
        .is-invalid { border-color: #dc3545 !important; box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important; }
        .preview-container { margin-top: 10px; padding: 10px; border: 1px solid #ddd; border-radius: 5px; background: #f8f9fa; }
        .preview-image { max-width: 150px; max-height: 150px; border-radius: 5px; }
        .file-info { font-size: 0.9em; color: #666; }
        #searchCustomField { max-height: 200px; overflow-y: auto; }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header gradient-bg text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-0">
                                    <i class="fas fa-address-book me-2"></i>Contact Management System
                                    <button class="btn btn-sm btn-outline-light ms-2" onclick="showMergeGuide()" title="How Merge Works">
                                        <i class="fas fa-question-circle"></i>
                                    </button>
                                </h3>
                                <small class="opacity-75">Professional CRM with Advanced Merging</small>
                            </div>
                            <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#contactModal" onclick="openCreateModal()">
                                <i class="fas fa-plus me-2"></i>Add Contact
                            </button>
                        </div>
                    </div>
                    <div class="card-body py-3">
                        <div class="search-section">
                            <h5 class="mb-3"><i class="fas fa-filter me-2"></i>Search & Filter Contacts</h5>
                            <div class="row align-items-end mb-3">
                                <div class="col-md-2">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                                        <input type="text" id="searchName" class="form-control" placeholder="Name">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                        <input type="text" id="searchEmail" class="form-control" placeholder="Email">
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text"><i class="fas fa-venus-mars"></i></span>
                                        <select id="searchGender" class="form-control">
                                            <option value="">Gender</option>
                                            <option value="male">Male</option>
                                            <option value="female">Female</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text"><i class="fas fa-tags"></i></span>
                                        <select id="searchCustomField" class="form-control">
                                            <option value="">Custom Field</option>
                                            @if(isset($customFields))
                                                @foreach($customFields as $field)
                                                    <option value="{{ $field }}">{{ ucfirst($field) }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                                        <input type="text" id="searchCustomValue" class="form-control" placeholder="Field Value" disabled>
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text"><i class="fas fa-list"></i></span>
                                        <select id="perPage" class="form-select" onchange="searchContacts()" style="min-width: 60px;">
                                            <option value="5" selected>5</option>
                                            <option value="10">10</option>
                                            <option value="25">25</option>
                                            <option value="50">50</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button class="btn btn-primary" onclick="searchContacts()" title="Search">
                                            <i class="fas fa-search"></i>
                                        </button>
                                        <button class="btn btn-outline-secondary" onclick="clearSearch()" title="Clear">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end">
                                <small class="text-muted">
                                    Total: <span id="contactCount">{{ isset($contacts) ? $contacts->total() : 0 }}</span> | 
                                    Active: {{ isset($contacts) ? $contacts->where('is_merged', false)->count() : 0 }} | 
                                    Merged: {{ isset($contacts) ? $contacts->where('is_merged', true)->count() : 0 }}
                                </small>
                            </div>
                        </div>

                        <div id="contactsContainer">
                            @if(isset($contacts))
                                @include('contacts.partials.contact_list', ['contacts' => $contacts])
                            @else
                                <div class="text-center py-5 text-muted">
                                    <i class="fas fa-users fa-3x mb-3"></i>
                                    <p>No contacts available</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact Modal -->
    <div class="modal fade" id="contactModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Contact</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="contactForm" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" id="contactId" name="contact_id">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Name *</label>
                                    <input type="text" class="form-control" id="name" name="name">
                                    <div class="invalid-feedback" id="name-error"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email *</label>
                                    <input type="email" class="form-control" id="email" name="email">
                                    <div class="invalid-feedback" id="email-error"></div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone *</label>
                                    <input type="text" class="form-control" id="phone" name="phone">
                                    <div class="invalid-feedback" id="phone-error"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Gender *</label>
                                    <div class="mt-2">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="gender" id="male" value="male">
                                            <label class="form-check-label" for="male">Male</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="gender" id="female" value="female">
                                            <label class="form-check-label" for="female">Female</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="gender" id="other" value="other">
                                            <label class="form-check-label" for="other">Other</label>
                                        </div>
                                    </div>
                                    <div class="invalid-feedback" id="gender-error"></div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="profile_image" class="form-label">Profile Image *</label>
                                    <input type="file" class="form-control" id="profile_image" name="profile_image" accept=".png,.jpg,.jpeg" onchange="previewImage(this)">
                                    <small class="text-muted">Supported formats: PNG, JPG, JPEG (Max: 2MB)</small>
                                    <div class="invalid-feedback" id="profile_image-error"></div>
                                    <div id="imagePreview" class="preview-container" style="display: none;">
                                        <img id="previewImg" class="preview-image" alt="Preview">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="additional_file" class="form-label">Additional File *</label>
                                    <input type="file" class="form-control" id="additional_file" name="additional_file" accept=".pdf" onchange="previewFile(this)">
                                    <small class="text-muted">Supported format: PDF only (Max: 2MB)</small>
                                    <div class="invalid-feedback" id="additional_file-error"></div>
                                    <div id="filePreview" class="preview-container" style="display: none;">
                                        <div class="file-info">
                                            <i class="fas fa-file me-2"></i>
                                            <span id="fileName"></span>
                                            <br><small id="fileSize"></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Custom Fields Section -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label">Custom Fields</label>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addCustomField()">
                                    <i class="fas fa-plus me-1"></i>Add Field
                                </button>
                            </div>
                            <div id="customFieldsContainer"></div>
                            <div class="invalid-feedback" id="custom_fields-error"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Contact</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Merge Modal -->
    <div class="modal fade" id="mergeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Merge Contacts</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Important:</strong> The secondary contact will be preserved with a merge flag. No data will be lost.
                    </div>
                    <p>Select which contact should be the master (primary) contact:</p>
                    <div id="mergeContactsContainer"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-warning" onclick="confirmMerge()">
                        <i class="fas fa-code-branch me-2"></i>Merge Contacts (Preserve Both)
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script src="{{ asset('js/contacts.js') }}"></script>
</body>
</html>