let selectedContactsForMerge = [];
let customFieldCounter = 0;

$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

function openCreateModal() {
    $('#modalTitle').text('Add Contact');
    $('#contactForm')[0].reset();
    $('#contactId').val('');
    $('#customFieldsContainer').empty();
    customFieldCounter = 0;
}

function editContact(id) {
    $.get(`/contacts/${id}`, function(response) {
        if (response.success) {
            const contact = response.data;
            $('#modalTitle').text('Edit Contact');
            $('#contactId').val(contact.id);
            $('#name').val(contact.name);
            $('#email').val(contact.email);
            $('#phone').val(contact.phone);
            $(`input[name="gender"][value="${contact.gender}"]`).prop('checked', true);
            
            // Show existing files
            if (contact.profile_image) {
                $('#imagePreview').show();
                $('#previewImg').attr('src', '/' + contact.profile_image);
            }
            
            if (contact.additional_file) {
                $('#filePreview').show();
                const fileName = contact.additional_file.split('/').pop();
                $('#fileName').text(fileName);
                $('#fileSize').text('Existing file');
            }
            
            $('#customFieldsContainer').empty();
            customFieldCounter = 0;
            if (contact.custom_fields) {
                Object.keys(contact.custom_fields).forEach(field => {
                    addCustomField(field, contact.custom_fields[field]);
                });
            }
            
            $('.form-control, .form-check-input').removeClass('is-invalid');
            $('.invalid-feedback').text('').hide();
            
            $('#contactModal').modal('show');
        }
    });
}

function deleteContact(id) {
    if (confirm('Are you sure you want to delete this contact?')) {
        $.ajax({
            url: `/contacts/${id}`,
            type: 'DELETE',
            success: function(response) {
                if (response.success) {
                    showAlert('success', response.message);
                    searchContacts();
                } else {
                    showAlert('danger', response.message);
                }
            }
        });
    }
}

$('#contactForm').on('submit', function(e) {
    e.preventDefault();
    
    // Clear previous validation errors
    $('.form-control, .form-check-input').removeClass('is-invalid');
    $('.invalid-feedback').text('').hide();
    
    const formData = new FormData(this);
    const contactId = $('#contactId').val();
    
    const customFields = {};
    let hasEmptyFields = false;
    
    $('.custom-field-row').each(function() {
        const fieldName = $(this).find('.custom-field-name').val().trim();
        const fieldValue = $(this).find('.custom-field-value').val().trim();
        
        if (fieldName || fieldValue) {
            if (!fieldName || !fieldValue) {
                hasEmptyFields = true;
                $(this).find('.custom-field-name, .custom-field-value').addClass('is-invalid');
            } else {
                customFields[fieldName] = fieldValue;
            }
        }
    });
    
    if (hasEmptyFields) {
        $('#custom_fields-error').text('Both field name and field value are required for all custom fields').show();
        return;
    }
    
    if (Object.keys(customFields).length > 0) {
        Object.keys(customFields).forEach(key => {
            formData.append(`custom_fields[${key}]`, customFields[key]);
        });
    }
    
    const url = contactId ? `/contacts/${contactId}` : '/contacts';
    const method = contactId ? 'PUT' : 'POST';
    
    if (method === 'PUT') {
        formData.append('_method', 'PUT');
    }
    
    $.ajax({
        url: url,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                $('#contactModal').modal('hide');
                showAlert('success', response.message);
                searchContacts();
                refreshCustomFieldDropdown();
            }
        },
        error: function(xhr) {
            const errors = xhr.responseJSON?.errors;
            if (errors) {
                // Clear previous errors
                $('.invalid-feedback').text('').hide();
                $('.form-control, .form-check-input').removeClass('is-invalid');
                
                // Show errors below each field
                Object.keys(errors).forEach(field => {
                    const errorMessage = errors[field][0]; // Get first error message
                    
                    if (field.includes('custom_fields')) {
                        $('.custom-field-name, .custom-field-value').addClass('is-invalid');
                        $('#custom_fields-error').text(errorMessage).show();
                    } else if (field === 'gender') {
                        $('input[name="gender"]').addClass('is-invalid');
                        $('#gender-error').text(errorMessage).show();
                    } else {
                        $(`#${field}`).addClass('is-invalid');
                        $(`#${field}-error`).text(errorMessage).show();
                    }
                });
            }
        }
    });
});

function addCustomField(fieldName = '', fieldValue = '') {
    customFieldCounter++;
    const html = `
        <div class="row mb-2 custom-field-row">
            <div class="col-md-4">
                <input type="text" class="form-control custom-field-name" placeholder="Field name" value="${fieldName}">
            </div>
            <div class="col-md-6">
                <input type="text" class="form-control custom-field-value" placeholder="Field value" value="${fieldValue}">
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeCustomField(this)">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    `;
    $('#customFieldsContainer').append(html);
    
    // Add at least one custom field by default
    if (customFieldCounter === 1 && !fieldName && !fieldValue) {
        // This is the first field, make it required
    }
}

function removeCustomField(button) {
    $(button).closest('.custom-field-row').remove();
}

let currentPage = 1;

function searchContacts(page = 1) {
    currentPage = page;
    const filters = {
        name: $('#searchName').val(),
        email: $('#searchEmail').val(),
        gender: $('#searchGender').val(),
        custom_field: $('#searchCustomField').val(),
        custom_value: $('#searchCustomValue').val(),
        per_page: $('#perPage').val() || 5,
        page: page
    };
    
    $.get('/contacts', filters, function(response) {
        if (response.success) {
            $('#contactsContainer').html(response.html);
            if (response.pagination) {
                const total = response.pagination.total;
                const active = response.data.filter(c => !c.is_merged).length;
                const merged = response.data.filter(c => c.is_merged).length;
                $('#contactCount').text(total);
                $('.text-muted').last().html(`Total: ${total} | Active: ${active} | Merged: ${merged}`);
            }
        }
    });
}

function loadPage(page) {
    searchContacts(page);
}

function clearSearch() {
    $('#searchName').val('');
    $('#searchEmail').val('');
    $('#searchGender').val('');
    $('#searchCustomField').val('');
    $('#searchCustomValue').val('').prop('disabled', true);
    currentPage = 1;
    searchContacts(1);
}

function initiateMerge(contactId) {
    // Get all active contacts for merge dropdown
    $.get('/contacts-active', function(response) {
        if (response.success) {
            const contacts = response.data.filter(c => c.id !== contactId);
            let html = `
                <div class="mb-3">
                    <label class="form-label">Select contact to merge with:</label>
                    <select class="form-control" id="mergeTargetContact">
                        <option value="">Choose a contact...</option>
            `;
            
            contacts.forEach(contact => {
                html += `<option value="${contact.id}">${contact.name} (${contact.email})</option>`;
            });
            
            html += `
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Which contact should be the master?</label>
                    <div class="alert alert-info">
                        <small><i class="fas fa-info-circle me-1"></i>The master contact will retain its ID and receive merged data. The secondary contact will be flagged as merged but preserved.</small>
                    </div>
                    <div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="masterContact" id="master1" value="${contactId}">
                            <label class="form-check-label" for="master1">
                                Current contact (will be loaded)
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="masterContact" id="master2" value="">
                            <label class="form-check-label" for="master2">
                                Target contact (will be loaded)
                            </label>
                        </div>
                    </div>
                </div>
            `;
            
            $('#mergeContactsContainer').html(html);
            selectedContactsForMerge = [contactId];
            
            $('#mergeTargetContact').on('change', function() {
                const targetId = $(this).val();
                if (targetId) {
                    selectedContactsForMerge = [contactId, parseInt(targetId)];
                    $('#master2').val(targetId);
                    
                    const targetContact = contacts.find(c => c.id == targetId);
                    $('label[for="master2"]').text(`${targetContact.name} (${targetContact.email})`);
                    
                    $.get(`/contacts/${contactId}`, function(resp) {
                        if (resp.success) {
                            $('label[for="master1"]').text(`${resp.data.name} (${resp.data.email})`);
                        }
                    });
                }
            });
            
            $('#mergeModal').modal('show');
        }
    });
}

function confirmMerge() {
    const masterId = $('input[name="masterContact"]:checked').val();
    const targetId = $('#mergeTargetContact').val();
    
    if (!masterId || !targetId) {
        showAlert('danger', 'Please select both contacts and choose a master contact');
        return;
    }
    
    const secondaryId = selectedContactsForMerge.find(id => id != masterId);
    
    if (confirm('Are you sure you want to merge these contacts? The secondary contact will be preserved with merge flag.')) {
        $.ajax({
            url: '/contacts/merge',
            type: 'POST',
            data: {
                master_id: masterId,
                secondary_id: secondaryId
            },
            success: function(response) {
                if (response.success) {
                    $('#mergeModal').modal('hide');
                    showAlert('success', response.message);
                    refreshCustomFieldDropdown();
                    searchContacts();
                }
            },
            error: function(xhr) {
                const errorMsg = xhr.responseJSON?.message || 'Unknown error';
                showAlert('danger', 'Error merging contacts: ' + errorMsg);
            }
        });
    }
}

function viewAllCustomFields(contactId) {
    $.get(`/contacts/${contactId}`, function(response) {
        if (response.success && response.data.custom_fields) {
            const contact = response.data;
            let html = `<div class="row">`;
            
            Object.entries(contact.custom_fields).forEach(([field, value]) => {
                html += `
                    <div class="col-md-6 mb-2">
                        <div class="card">
                            <div class="card-body p-2">
                                <strong>${field}:</strong><br>
                                <span class="text-muted">${value}</span>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            html += `</div>`;
            showModal(`All Custom Fields - ${contact.name}`, html);
        }
    });
}

function viewMergedContacts(masterId) {
    $.get(`/contacts/${masterId}/merged-data`, function(response) {
        if (response.success) {
            const mergedContacts = response.data;
            
            if (mergedContacts.length === 0) {
                showAlert('info', 'No merged contacts found');
                return;
            }
            
            let html = `<div class="alert alert-info"><i class="fas fa-info-circle me-2"></i>${mergedContacts.length} contact(s) merged into this record</div>`;
            
            mergedContacts.forEach(contact => {
                const customFields = contact.custom_fields ? Object.entries(contact.custom_fields).map(([key, value]) => `<span class="badge bg-secondary me-1">${key}: ${value}</span>`).join('') : 'None';
                
                html += `
                    <div class="card mb-3 border-warning">
                        <div class="card-header bg-warning bg-opacity-25">
                            <h6 class="mb-0"><i class="fas fa-user me-2"></i>${contact.name}</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Email:</strong> ${contact.email}</p>
                                    <p class="mb-1"><strong>Phone:</strong> ${contact.phone}</p>
                                    <p class="mb-1"><strong>Gender:</strong> ${contact.gender}</p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Custom Fields:</strong></p>
                                    <div>${customFields}</div>
                                </div>
                            </div>
                            <small class="text-muted"><i class="fas fa-clock me-1"></i>Merged on: ${new Date(contact.updated_at).toLocaleString()}</small>
                        </div>
                    </div>
                `;
            });
            
            showModal('Merged Contacts', html);
        }
    });
}

function viewMasterContact(masterId) {
    $.get(`/contacts/${masterId}`, function(response) {
        if (response.success) {
            const master = response.data;
            const customFields = master.custom_fields ? Object.entries(master.custom_fields).map(([key, value]) => `<span class="badge bg-primary me-1">${key}: ${value}</span>`).join('') : 'None';
            
            const html = `
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>This is the master contact where the merged data is stored.
                </div>
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-crown me-2"></i>Master Contact Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Name:</strong> ${master.name}</p>
                                <p><strong>Email:</strong> ${master.email}</p>
                                <p><strong>Phone:</strong> ${master.phone}</p>
                                <p><strong>Gender:</strong> ${master.gender}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Custom Fields:</strong></p>
                                <div>${customFields}</div>
                                <p class="mt-2"><strong>Created:</strong> ${new Date(master.created_at).toLocaleString()}</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            showModal('Master Contact Information', html);
        }
    }).fail(function() {
        showAlert('danger', 'Could not load master contact details');
    });
}

function viewPreservedData(masterId) {
    $.get(`/contacts/${masterId}/merged-data`, function(response) {
        if (response.success) {
            const mergedContacts = response.data.filter(c => c.merged_data);
            
            if (mergedContacts.length === 0) {
                showAlert('info', 'No preserved secondary contact data found');
                return;
            }
            
            let html = `
                <div class="alert alert-success">
                    <i class="fas fa-database me-2"></i>Original data from ${mergedContacts.length} secondary contact(s) before merge
                </div>
            `;
            
            mergedContacts.forEach((contact, index) => {
                const originalData = contact.merged_data;
                const customFields = originalData.custom_fields ? 
                    Object.entries(originalData.custom_fields).map(([key, value]) => 
                        `<span class="badge bg-secondary me-1">${key}: ${value}</span>`
                    ).join('') : 'None';
                
                html += `
                    <div class="card mb-3 border-success">
                        <div class="card-header bg-success bg-opacity-25">
                            <h6 class="mb-0">
                                <i class="fas fa-archive me-2"></i>Preserved Data #${index + 1}
                                <small class="text-muted">(Original ID: #${originalData.id})</small>
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Original Name:</strong> ${originalData.name}</p>
                                    <p class="mb-1"><strong>Original Email:</strong> ${originalData.email}</p>
                                    <p class="mb-1"><strong>Original Phone:</strong> ${originalData.phone}</p>
                                    <p class="mb-1"><strong>Original Gender:</strong> ${originalData.gender}</p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Original Custom Fields:</strong></p>
                                    <div class="mb-2">${customFields}</div>
                                    <p class="mb-1"><strong>Original Created:</strong> ${new Date(originalData.created_at).toLocaleString()}</p>
                                </div>
                            </div>
                            <hr>
                            <small class="text-muted">
                                <i class="fas fa-clock me-1"></i>Merged on: ${new Date(contact.updated_at).toLocaleString()}
                            </small>
                        </div>
                    </div>
                `;
            });
            
            showModal('Preserved Secondary Contact Data', html);
        }
    });
}

function showModal(title, content) {
    const modalHtml = `
        <div class="modal fade" id="dynamicModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">${title}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">${content}</div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    $('#dynamicModal').remove();
    $('body').append(modalHtml);
    $('#dynamicModal').modal('show');
    $('#dynamicModal').on('hidden.bs.modal', function() {
        $(this).remove();
    });
}

function showAlert(type, message) {
    const bgColor = type === 'success' ? '#28a745' : type === 'danger' ? '#dc3545' : '#17a2b8';
    
    Toastify({
        text: message,
        duration: 5000,
        gravity: "top",
        position: "right",
        backgroundColor: bgColor,
        stopOnFocus: true,
        onClick: function(){}
    }).showToast();
}

$(document).ready(function() {
    // Refresh custom field dropdown on page load
    refreshCustomFieldDropdown();
    
    let searchTimeout;
    $('#searchName, #searchEmail, #searchGender, #searchCustomField, #searchCustomValue').on('input change', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(searchContacts, 500);
    });
    
    $('#searchCustomField').on('change', function() {
        const customValue = $('#searchCustomValue');
        if ($(this).val()) {
            customValue.prop('disabled', false).attr('placeholder', 'Enter value for ' + $(this).val());
        } else {
            customValue.prop('disabled', true).attr('placeholder', 'Select field first').val('');
        }
    });
    
    $('#searchCustomValue').prop('disabled', true);
    
    // Clear validation errors when modal is closed
    $('#contactModal').on('hidden.bs.modal', function() {
        $('.form-control, .form-check-input').removeClass('is-invalid');
        $('.invalid-feedback').text('').hide();
    });
});

function showMergeGuide() {
    const guideContent = `
        <div class="alert alert-info">
            <i class="fas fa-lightbulb me-2"></i><strong>Contact Merge System Guide</strong>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <h5><i class="fas fa-play-circle text-primary me-2"></i>How to Merge Contacts</h5>
                <ol class="list-group list-group-numbered">
                    <li class="list-group-item">Click the <span class="badge bg-warning"><i class="fas fa-code-branch"></i></span> merge button on any active contact</li>
                    <li class="list-group-item">Select another contact to merge with from dropdown</li>
                    <li class="list-group-item">Choose which contact should be the <strong>Master</strong></li>
                    <li class="list-group-item">Click "Merge Contacts" to complete</li>
                </ol>
            </div>
            <div class="col-md-6">
                <h5><i class="fas fa-shield-alt text-success me-2"></i>Data Safety</h5>
                <ul class="list-group">
                    <li class="list-group-item"><i class="fas fa-check text-success me-2"></i>No data is ever deleted</li>
                    <li class="list-group-item"><i class="fas fa-check text-success me-2"></i>Secondary contact is preserved</li>
                    <li class="list-group-item"><i class="fas fa-check text-success me-2"></i>Original data stored in database</li>
                    <li class="list-group-item"><i class="fas fa-check text-success me-2"></i>Complete audit trail maintained</li>
                </ul>
            </div>
        </div>
        
        <hr>
        
        <div class="row">
            <div class="col-md-4">
                <h6><i class="fas fa-crown text-warning me-2"></i>Master Contact</h6>
                <ul class="small">
                    <li>Keeps its original ID</li>
                    <li>Receives merged custom fields</li>
                    <li>Shows <span class="badge bg-info">X merged here</span></li>
                    <li>Has <span class="badge bg-success"><i class="fas fa-database"></i> Data</span> button</li>
                </ul>
            </div>
            <div class="col-md-4">
                <h6><i class="fas fa-archive text-secondary me-2"></i>Secondary Contact</h6>
                <ul class="small">
                    <li>Marked as merged (yellow row)</li>
                    <li>Shows <span class="badge bg-warning">Merged into #X</span></li>
                    <li>Original data preserved in database</li>
                    <li>Can view master with <span class="badge bg-warning"><i class="fas fa-crown"></i> Master</span></li>
                </ul>
            </div>
            <div class="col-md-4">
                <h6><i class="fas fa-magic text-primary me-2"></i>Smart Merging</h6>
                <ul class="small">
                    <li>Custom fields are intelligently combined</li>
                    <li>Different values joined with " | "</li>
                    <li>Phone numbers merged if different</li>
                    <li>Duplicate values automatically handled</li>
                </ul>
            </div>
        </div>
        
        <hr>
        
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Important:</strong> Only active contacts can be merged. Already merged contacts cannot be merged again to prevent complex chains.
        </div>
        
        <div class="text-center">
            <h6><i class="fas fa-list text-info me-2"></i>Table Display Order</h6>
            <p class="small text-muted">
                1. Master contacts with their merged contacts grouped together<br>
                2. Independent contacts (not involved in any merge) at the end
            </p>
        </div>
    `;
    
    showModal('Contact Merge System - User Guide', guideContent);
}

function refreshCustomFieldDropdown() {
    $.get('/contacts-custom-fields', function(response) {
        if (response.success) {
            const dropdown = $('#searchCustomField');
            const currentValue = dropdown.val();
            
            // Clear existing options except first one
            dropdown.find('option:not(:first)').remove();
            
            // Add updated options
            response.data.forEach(field => {
                dropdown.append(`<option value="${field}">${field.charAt(0).toUpperCase() + field.slice(1)}</option>`);
            });
            
            // Restore selected value if it still exists
            dropdown.val(currentValue);
        }
    });
}

// File preview functions
function previewImage(input) {
    const preview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');
    
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const maxSize = 2 * 1024 * 1024; // 2MB
        
        if (file.size > maxSize) {
            showAlert('danger', 'Profile image must be less than 2MB');
            input.value = '';
            preview.style.display = 'none';
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        preview.style.display = 'none';
    }
}

function previewFile(input) {
    const preview = document.getElementById('filePreview');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');
    
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const maxSize = 2 * 1024 * 1024; // 2MB
        
        if (file.size > maxSize) {
            showAlert('danger', 'PDF file must be less than 2MB');
            input.value = '';
            preview.style.display = 'none';
            return;
        }
        
        fileName.textContent = file.name;
        fileSize.textContent = `Size: ${(file.size / 1024).toFixed(2)} KB`;
        preview.style.display = 'block';
    } else {
        preview.style.display = 'none';
    }
}

// Initialize form with at least one custom field
function openCreateModal() {
    $('#modalTitle').text('Add Contact');
    $('#contactForm')[0].reset();
    $('#contactId').val('');
    $('#customFieldsContainer').empty();
    $('#imagePreview').hide();
    $('#filePreview').hide();
    $('.form-control, .form-check-input').removeClass('is-invalid');
    $('.invalid-feedback').text('').hide();
    customFieldCounter = 0;
    addCustomField(); // Add one required custom field by default
}