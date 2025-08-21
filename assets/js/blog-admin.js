document.addEventListener('DOMContentLoaded', function() {
    
    // Initialize TinyMCE Rich Text Editor
    if (typeof tinymce !== 'undefined') {
        tinymce.init({
            selector: '.rich-editor',
            plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
            toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
            height: 400,
            menubar: false,
            branding: false,
            language: 'fa',
            directionality: 'rtl',
            content_css: [
                '//fonts.googleapis.com/css?family=Vazir:300,400,700'
            ],
            setup: function (editor) {
                editor.on('change', function () {
                    editor.save();
                });
                
                // Set RTL direction for Persian content
                editor.on('init', function () {
                    if (editor.id === 'content_fa') {
                        editor.getBody().style.direction = 'rtl';
                        editor.getBody().style.textAlign = 'right';
                    }
                });
            },
            // Image upload handling
            images_upload_handler: function (blobInfo, success, failure) {
                const formData = new FormData();
                formData.append('file', blobInfo.blob(), blobInfo.filename());
                
                fetch('upload_image.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        success(result.location);
                    } else {
                        failure('آپلود تصویر با خطا مواجه شد');
                    }
                })
                .catch(() => {
                    failure('خطا در آپلود تصویر');
                });
            }
        });
    }

    // Initialize existing categories and tags if in edit mode
    initializeExistingData();

    // Add event listeners for Enter key on inputs
    const categoryInput = document.getElementById('categoryInput');
    const tagInput = document.getElementById('tagInput');

    if (categoryInput) {
        categoryInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                addCategory();
            }
        });
    }

    if (tagInput) {
        tagInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                addTag();
            }
        });
    }

    // Form submission validation
    const form = document.querySelector('#blogForm form');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!validateForm()) {
                e.preventDefault();
                return false;
            }
            
            // Show loading state
            const submitButton = e.submitter;
            if (submitButton) {
                showButtonLoading(submitButton);
            }
        });
    }
});

// Global variables for managing categories and tags
let selectedCategories = [];
let selectedTags = [];

// Initialize existing data for edit mode
function initializeExistingData() {
    const categoriesField = document.getElementById('categoriesField');
    const tagsField = document.getElementById('tagsField');

    // Initialize categories
    if (categoriesField && categoriesField.value) {
        const categories = categoriesField.value.split(',').map(cat => cat.trim()).filter(cat => cat);
        selectedCategories = [...categories];
        updateCategoryChips();
    }

    // Initialize tags
    if (tagsField && tagsField.value) {
        const tags = tagsField.value.split(',').map(tag => tag.trim()).filter(tag => tag);
        selectedTags = [...tags];
        updateTagChips();
    }
}

// Function to create category chip
function createCategoryChip(category) {
    const chip = document.createElement('div');
    chip.className = 'flex items-center bg-blue-100 text-blue-800 rounded-full px-3 py-1 text-sm font-medium chip-enter';
    chip.innerHTML = `
        <span>${escapeHtml(category)}</span>
        <button type="button" onclick="removeCategory('${escapeHtml(category)}')" class="mr-2 text-blue-600 hover:text-blue-800 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    `;
    return chip;
}

// Function to create tag chip
function createTagChip(tag) {
    const chip = document.createElement('div');
    chip.className = 'flex items-center bg-green-100 text-green-800 rounded-full px-3 py-1 text-sm font-medium chip-enter';
    chip.innerHTML = `
        <span>#${escapeHtml(tag)}</span>
        <button type="button" onclick="removeTag('${escapeHtml(tag)}')" class="mr-2 text-green-600 hover:text-green-800 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    `;
    return chip;
}

// Update category chips display
function updateCategoryChips() {
    const categoryChips = document.getElementById('categoryChips');
    if (!categoryChips) return;
    
    categoryChips.innerHTML = '';
    selectedCategories.forEach(category => {
        const chip = createCategoryChip(category);
        categoryChips.appendChild(chip);
    });
    updateCategoriesField();
}

// Update tag chips display
function updateTagChips() {
    const tagChips = document.getElementById('tagChips');
    if (!tagChips) return;
    
    tagChips.innerHTML = '';
    selectedTags.forEach(tag => {
        const chip = createTagChip(tag);
        tagChips.appendChild(chip);
    });
    updateTagsField();
}

// Function to add predefined category
function addPredefinedCategory(category) {
    if (!selectedCategories.includes(category)) {
        selectedCategories.push(category);
        updateCategoryChips();
    }
}

// Function to add category from input
function addCategory() {
    const categoryInput = document.getElementById('categoryInput');
    const category = categoryInput.value.trim();
    
    if (category && !selectedCategories.includes(category)) {
        selectedCategories.push(category);
        updateCategoryChips();
        categoryInput.value = '';
        
        // Show feedback
        showNotification(`دسته‌بندی "${category}" اضافه شد`, 'success');
    } else if (selectedCategories.includes(category)) {
        showNotification('این دسته‌بندی قبلاً اضافه شده است', 'warning');
        categoryInput.value = '';
    }
}

// Function to remove category
function removeCategory(category) {
    selectedCategories = selectedCategories.filter(cat => cat !== category);
    updateCategoryChips();
    showNotification(`دسته‌بندی "${category}" حذف شد`, 'info');
}

// Function to add predefined tag
function addPredefinedTag(tag) {
    if (!selectedTags.includes(tag)) {
        selectedTags.push(tag);
        updateTagChips();
    }
}

// Function to add tag from input
function addTag() {
    const tagInput = document.getElementById('tagInput');
    const tag = tagInput.value.trim();
    
    if (tag && !selectedTags.includes(tag)) {
        selectedTags.push(tag);
        updateTagChips();
        tagInput.value = '';
        
        // Show feedback
        showNotification(`برچسب "${tag}" اضافه شد`, 'success');
    } else if (selectedTags.includes(tag)) {
        showNotification('این برچسب قبلاً اضافه شده است', 'warning');
        tagInput.value = '';
    }
}

// Function to remove tag
function removeTag(tag) {
    selectedTags = selectedTags.filter(t => t !== tag);
    updateTagChips();
    showNotification(`برچسب "${tag}" حذف شد`, 'info');
}

// Function to update categories hidden field
function updateCategoriesField() {
    const categoriesField = document.getElementById('categoriesField');
    if (categoriesField) {
        categoriesField.value = selectedCategories.join(', ');
    }
}

// Function to update tags hidden field
function updateTagsField() {
    const tagsField = document.getElementById('tagsField');
    if (tagsField) {
        tagsField.value = selectedTags.join(', ');
    }
}

// Function to toggle form visibility
function toggleForm() {
    const blogForm = document.getElementById('blogForm');
    if (blogForm) {
        blogForm.classList.toggle('hidden');
        
        // Focus on first input when form opens
        if (!blogForm.classList.contains('hidden')) {
            setTimeout(() => {
                const firstInput = blogForm.querySelector('input[type="file"]') || 
                                 blogForm.querySelector('input[type="text"]');
                if (firstInput) {
                    firstInput.focus();
                }
            }, 100);
        }
    }
}

// Form validation
function validateForm() {
    const persianTitle = document.querySelector('input[name="translations[fa][title]"]');
    const persianSummary = document.querySelector('textarea[name="translations[fa][summary]"]');
    const imageInput = document.getElementById('image');
    const existingImage = document.querySelector('input[name="existing_image"]');
    const isEditMode = document.querySelector('input[name="post_id"]');
    
    // Check Persian title
    if (!persianTitle || !persianTitle.value.trim()) {
        showNotification('عنوان فارسی الزامی است', 'error');
        persianTitle.focus();
        return false;
    }
    
    // Check Persian summary
    if (!persianSummary || !persianSummary.value.trim()) {
        showNotification('خلاصه فارسی الزامی است', 'error');
        persianSummary.focus();
        return false;
    }
    
    // Check Persian content
    const persianEditor = tinymce.get('content_fa');
    if (!persianEditor || !persianEditor.getContent().trim()) {
        showNotification('محتوای فارسی الزامی است', 'error');
        persianEditor.focus();
        return false;
    }
    
    // Check image (required for new posts)
    if (!isEditMode && (!imageInput.files || imageInput.files.length === 0)) {
        showNotification('تصویر شاخص الزامی است', 'error');
        imageInput.focus();
        return false;
    }
    
    // Validate image file type if uploaded
    if (imageInput.files && imageInput.files.length > 0) {
        const file = imageInput.files[0];
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        
        if (!allowedTypes.includes(file.type)) {
            showNotification('نوع فایل تصویر مجاز نیست. فقط JPG, PNG, GIF, WEBP مجاز است', 'error');
            imageInput.focus();
            return false;
        }
        
        // Check file size (max 5MB)
        if (file.size > 5 * 1024 * 1024) {
            showNotification('حجم فایل تصویر نباید بیش از 5 مگابایت باشد', 'error');
            imageInput.focus();
            return false;
        }
    }
    
    return true;
}

// Function to show button loading state
function showButtonLoading(button) {
    const originalText = button.textContent;
    button.disabled = true;
    button.classList.add('btn-loading');
    
    // Create loading spinner
    button.innerHTML = `
        <div class="flex items-center justify-center">
            <div class="animate-spin rounded-full h-4 w-4 border-2 border-white border-t-transparent ml-2"></div>
            <span>در حال پردازش...</span>
        </div>
    `;
}

// Function to filter posts by category
function filterByCategory() {
    const categoryFilter = document.getElementById('categoryFilter');
    const selectedCategory = categoryFilter.value;
    const tableRows = document.querySelectorAll('#postsTableBody tr');
    
    let visibleCount = 0;
    
    tableRows.forEach(row => {
        const categories = row.getAttribute('data-categories') || '';
        
        if (!selectedCategory || categories.includes(selectedCategory)) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    // Show message if no posts found
    if (visibleCount === 0 && selectedCategory) {
        showNotification(`هیچ مقاله‌ای در دسته‌بندی "${selectedCategory}" یافت نشد`, 'info');
    }
}

// Function to show notifications
function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(notif => notif.remove());
    
    const notification = document.createElement('div');
    notification.className = `notification fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg transition-all duration-300 transform translate-x-full max-w-md`;
    
    // Set colors based on type
    switch (type) {
        case 'success':
            notification.classList.add('bg-green-500', 'text-white');
            break;
        case 'error':
            notification.classList.add('bg-red-500', 'text-white');
            break;
        case 'warning':
            notification.classList.add('bg-yellow-500', 'text-white');
            break;
        default:
            notification.classList.add('bg-blue-500', 'text-white');
    }
    
    notification.innerHTML = `
        <div class="flex items-center justify-between">
            <span class="text-sm font-medium">${escapeHtml(message)}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="mr-4 text-white hover:text-gray-200 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 100);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentElement) {
            notification.classList.add('translate-x-full');
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.remove();
                }
            }, 300);
        }
    }, 5000);
}

// Function to preview post
function previewPost(postId) {
    const previewWindow = window.open(`../blog-single.php?id=${postId}`, '_blank', 'width=1200,height=800,scrollbars=yes,resizable=yes');
    if (!previewWindow) {
        showNotification('لطفاً popup blocker را غیرفعال کنید', 'warning');
    }
}

// Function to confirm delete
function confirmDelete(postId, title) {
    if (confirm(`آیا مطمئن هستید که می‌خواهید مقاله "${title}" را حذف کنید؟\nاین عمل قابل بازگشت نیست.`)) {
        
        // Show loading overlay
        const loadingOverlay = document.createElement('div');
        loadingOverlay.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
        loadingOverlay.innerHTML = `
            <div class="bg-white p-6 rounded-lg shadow-lg">
                <div class="flex items-center">
                    <div class="animate-spin rounded-full h-8 w-8 border-4 border-red-500 border-t-transparent ml-3"></div>
                    <span class="text-lg font-medium">در حال حذف مقاله...</span>
                </div>
            </div>
        `;
        document.body.appendChild(loadingOverlay);
        
        // Redirect after a short delay to show loading
        setTimeout(() => {
            window.location.href = `manage-blog.php?delete=${postId}`;
        }, 500);
    }
}

// Auto-save functionality
let autoSaveTimer;
let hasUnsavedChanges = false;

function setupAutoSave() {
    const form = document.querySelector('#blogForm form');
    if (!form) return;
    
    const inputs = form.querySelectorAll('input:not([type="hidden"]), textarea');
    
    inputs.forEach(input => {
        input.addEventListener('input', () => {
            hasUnsavedChanges = true;
            clearTimeout(autoSaveTimer);
            autoSaveTimer = setTimeout(() => {
                if (hasUnsavedChanges) {
                    autoSaveDraft();
                }
            }, 30000); // Auto-save after 30 seconds of inactivity
        });
    });
    
    // TinyMCE editors
    if (typeof tinymce !== 'undefined') {
        tinymce.get('content_fa') && tinymce.get('content_fa').on('input', () => {
            hasUnsavedChanges = true;
            clearTimeout(autoSaveTimer);
            autoSaveTimer = setTimeout(() => {
                if (hasUnsavedChanges) {
                    autoSaveDraft();
                }
            }, 30000);
        });
    }
}

// Auto-save draft function
function autoSaveDraft() {
    if (!hasUnsavedChanges) return;
    
    const form = document.querySelector('#blogForm form');
    if (!form) return;
    
    // Only auto-save if we're editing an existing post
    const postId = form.querySelector('input[name="post_id"]');
    if (!postId || !postId.value) return;
    
    const formData = new FormData(form);
    formData.set('save_draft', '1');
    
    // Show auto-save indicator
    showAutoSaveIndicator('در حال ذخیره خودکار...');
    
    fetch('manage-blog.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (response.ok) {
            hasUnsavedChanges = false;
            showAutoSaveIndicator('ذخیره خودکار انجام شد', 'success');
        } else {
            showAutoSaveIndicator('خطا در ذخیره خودکار', 'error');
        }
    })
    .catch(() => {
        showAutoSaveIndicator('خطا در ذخیره خودکار', 'error');
    });
}

// Show auto-save indicator
function showAutoSaveIndicator(message, type = 'info') {
    // Remove existing auto-save indicators
    const existingIndicators = document.querySelectorAll('.auto-save-indicator');
    existingIndicators.forEach(indicator => indicator.remove());
    
    const indicator = document.createElement('div');
    indicator.className = `auto-save-indicator fixed bottom-4 left-4 px-4 py-2 rounded-lg shadow-lg text-sm font-medium z-50 transition-all duration-300`;
    
    switch (type) {
        case 'success':
            indicator.classList.add('bg-green-500', 'text-white');
            break;
        case 'error':
            indicator.classList.add('bg-red-500', 'text-white');
            break;
        default:
            indicator.classList.add('bg-gray-800', 'text-white');
    }
    
    indicator.textContent = message;
    document.body.appendChild(indicator);
    
    // Auto-remove after 3 seconds
    setTimeout(() => {
        if (indicator.parentElement) {
            indicator.style.opacity = '0';
            setTimeout(() => {
                indicator.remove();
            }, 300);
        }
    }, 3000);
}

// Utility function to escape HTML
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}

// Initialize auto-save when page loads
document.addEventListener('DOMContentLoaded', function() {
    setupAutoSave();
    
    // Warn user about unsaved changes
    window.addEventListener('beforeunload', function(e) {
        if (hasUnsavedChanges) {
            e.preventDefault();
            e.returnValue = 'شما تغییرات ذخیره نشده‌ای دارید. آیا مطمئن هستید که می‌خواهید صفحه را ترک کنید؟';
        }
    });
});

// Image preview functionality
function setupImagePreview() {
    const imageInput = document.getElementById('image');
    if (!imageInput) return;
    
    imageInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file && file.type.startsWith('image/')) {
            // Validate file size
            if (file.size > 5 * 1024 * 1024) {
                showNotification('حجم فایل تصویر نباید بیش از 5 مگابایت باشد', 'error');
                this.value = '';
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                // Remove existing preview
                const existingPreview = document.getElementById('imagePreview');
                if (existingPreview) {
                    existingPreview.remove();
                }
                
                // Create new preview
                const preview = document.createElement('div');
                preview.id = 'imagePreview';
                preview.className = 'mt-4 p-4 bg-gray-50 rounded-lg border border-gray-200';
                preview.innerHTML = `
                    <p class="text-sm text-gray-600 mb-2">پیش‌نمایش تصویر جدید:</p>
                    <div class="relative">
                        <img src="${e.target.result}" 
                             class="w-48 h-32 object-cover rounded-lg shadow-sm" 
                             alt="Preview">
                        <div class="mt-2 text-xs text-gray-500">
                            <span>نام فایل: ${file.name}</span>
                            <span class="mr-4">حجم: ${(file.size / 1024 / 1024).toFixed(2)} MB</span>
                        </div>
                    </div>
                `;
                
                imageInput.parentNode.appendChild(preview);
            };
            reader.readAsDataURL(file);
        }
    });
}

// Initialize image preview on page load
document.addEventListener('DOMContentLoaded', setupImagePreview);