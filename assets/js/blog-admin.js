document.addEventListener('DOMContentLoaded', function() {
    
    // Initialize TinyMCE Rich Text Editor
    if (typeof tinymce !== 'undefined') {
        tinymce.init({
            selector: '.rich-editor',
            plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount checklist mediaembed casechange export formatpainter pageembed linkchecker a11ychecker tinymcespellchecker permanentpen powerpaste advtable advcode editimage advtemplate ai mentions tinycomments tableofcontents footnotes mergetags autocorrect typography inlinecss',
            toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table mergetags | addcomment showcomments | spellcheckdialog a11ycheck typography | align lineheight | checklist numlist bullist indent outdent | emoticons charmap | removeformat',
            tinycomments_mode: 'embedded',
            tinycomments_author: 'Author name',
            mergetags_list: [
                { value: 'First.Name', title: 'First Name' },
                { value: 'Email', title: 'Email' },
            ],
            ai_request: (request, respondWith) => respondWith.string(() => Promise.reject("See docs to implement AI Assistant")),
            height: 400,
            menubar: false,
            branding: false,
            language: 'fa',
            directionality: 'rtl',
            content_css: [
                '//fonts.googleapis.com/css?family=Lato:300,300i,400,400i',
                '//www.tiny.cloud/css/codepen.min.css'
            ],
            setup: function (editor) {
                editor.on('change', function () {
                    editor.save();
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
});

// Global variables for managing categories and tags
let selectedCategories = [];
let selectedTags = [];

// Initialize existing data for edit mode
function initializeExistingData() {
    const categoriesField = document.getElementById('categoriesField');
    const tagsField = document.getElementById('tagsField');

    if (categoriesField && categoriesField.value) {
        const categories = categoriesField.value.split(',').map(cat => cat.trim()).filter(cat => cat);
        categories.forEach(category => {
            if (!selectedCategories.includes(category)) {
                selectedCategories.push(category);
                createCategoryChip(category);
            }
        });
    }

    if (tagsField && tagsField.value) {
        const tags = tagsField.value.split(',').map(tag => tag.trim()).filter(tag => tag);
        tags.forEach(tag => {
            if (!selectedTags.includes(tag)) {
                selectedTags.push(tag);
                createTagChip(tag);
            }
        });
    }
}

// Function to create category chip
function createCategoryChip(category) {
    const categoryChips = document.getElementById('categoryChips');
    
    const chip = document.createElement('div');
    chip.className = 'flex items-center bg-blue-100 text-blue-800 rounded-full px-3 py-1 text-sm';
    chip.innerHTML = `
        <span>${category}</span>
        <button type="button" onclick="removeCategory('${category}')" class="mr-2 text-blue-600 hover:text-blue-800">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    `;
    
    categoryChips.appendChild(chip);
    updateCategoriesField();
}

// Function to create tag chip
function createTagChip(tag) {
    const tagChips = document.getElementById('tagChips');
    
    const chip = document.createElement('div');
    chip.className = 'flex items-center bg-green-100 text-green-800 rounded-full px-3 py-1 text-sm';
    chip.innerHTML = `
        <span>#${tag}</span>
        <button type="button" onclick="removeTag('${tag}')" class="mr-2 text-green-600 hover:text-green-800">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    `;
    
    tagChips.appendChild(chip);
    updateTagsField();
}

// Function to add predefined category
function addPredefinedCategory(category) {
    if (!selectedCategories.includes(category)) {
        selectedCategories.push(category);
        createCategoryChip(category);
    }
}

// Function to add category from input
function addCategory() {
    const categoryInput = document.getElementById('categoryInput');
    const category = categoryInput.value.trim();
    
    if (category && !selectedCategories.includes(category)) {
        selectedCategories.push(category);
        createCategoryChip(category);
        categoryInput.value = '';
    }
}

// Function to remove category
function removeCategory(category) {
    selectedCategories = selectedCategories.filter(cat => cat !== category);
    
    // Remove chip from DOM
    const categoryChips = document.getElementById('categoryChips');
    const chips = categoryChips.children;
    for (let i = 0; i < chips.length; i++) {
        if (chips[i].textContent.includes(category)) {
            chips[i].remove();
            break;
        }
    }
    
    updateCategoriesField();
}

// Function to add predefined tag
function addPredefinedTag(tag) {
    if (!selectedTags.includes(tag)) {
        selectedTags.push(tag);
        createTagChip(tag);
    }
}

// Function to add tag from input
function addTag() {
    const tagInput = document.getElementById('tagInput');
    const tag = tagInput.value.trim();
    
    if (tag && !selectedTags.includes(tag)) {
        selectedTags.push(tag);
        createTagChip(tag);
        tagInput.value = '';
    }
}

// Function to remove tag
function removeTag(tag) {
    selectedTags = selectedTags.filter(t => t !== tag);
    
    // Remove chip from DOM
    const tagChips = document.getElementById('tagChips');
    const chips = tagChips.children;
    for (let i = 0; i < chips.length; i++) {
        if (chips[i].textContent.includes(tag)) {
            chips[i].remove();
            break;
        }
    }
    
    updateTagsField();
}

// Function to update categories hidden field
function updateCategoriesField() {
    const categoriesField = document.getElementById('categoriesField');
    if (categoriesField) {
        categoriesField.value = selectedCategories.join(',');
    }
}

// Function to update tags hidden field
function updateTagsField() {
    const tagsField = document.getElementById('tagsField');
    if (tagsField) {
        tagsField.value = selectedTags.join(',');
    }
}

// Function to toggle form visibility
function toggleForm() {
    const blogForm = document.getElementById('blogForm');
    if (blogForm) {
        blogForm.classList.toggle('hidden');
    }
}

// Function to save draft
function saveDraft() {
    // Get form data
    const formData = new FormData(document.querySelector('form'));
    formData.append('draft', '1');
    
    // Show loading state
    const button = event.target;
    const originalText = button.textContent;
    button.textContent = 'در حال ذخیره...';
    button.disabled = true;
    
    // Here you would typically send an AJAX request to save the draft
    // For now, we'll just simulate it
    setTimeout(() => {
        button.textContent = originalText;
        button.disabled = false;
        
        // Show success message
        showNotification('پیش‌نویس با موفقیت ذخیره شد', 'success');
    }, 1000);
}

// Function to filter posts by category
function filterByCategory() {
    const categoryFilter = document.getElementById('categoryFilter');
    const selectedCategory = categoryFilter.value;
    const tableRows = document.querySelectorAll('#postsTableBody tr');
    
    tableRows.forEach(row => {
        const categories = row.getAttribute('data-categories') || '';
        
        if (!selectedCategory || categories.includes(selectedCategory)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Function to show notifications
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg transition-all duration-300 transform translate-x-full`;
    
    // Set colors based on type
    switch (type) {
        case 'success':
            notification.className += ' bg-green-500 text-white';
            break;
        case 'error':
            notification.className += ' bg-red-500 text-white';
            break;
        case 'warning':
            notification.className += ' bg-yellow-500 text-white';
            break;
        default:
            notification.className += ' bg-blue-500 text-white';
    }
    
    notification.innerHTML = `
        <div class="flex items-center">
            <span>${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="mr-4 text-white hover:text-gray-200">
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
        notification.classList.add('translate-x-full');
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 300);
    }, 5000);
}

// Function to preview post
function previewPost(postId) {
    window.open(`../post.php?id=${postId}`, '_blank');
}

// Function to confirm delete
function confirmDelete(postId, title) {
    if (confirm(`آیا مطمئن هستید که می‌خواهید مقاله "${title}" را حذف کنید؟`)) {
        window.location.href = `manage-blog.php?delete=${postId}`;
    }
}

// Auto-save functionality
let autoSaveTimer;
function setupAutoSave() {
    const form = document.querySelector('form');
    if (form) {
        const inputs = form.querySelectorAll('input, textarea');
        inputs.forEach(input => {
            input.addEventListener('input', () => {
                clearTimeout(autoSaveTimer);
                autoSaveTimer = setTimeout(() => {
                    saveDraft();
                }, 30000); // Auto-save after 30 seconds of inactivity
            });
        });
    }
}

// Initialize auto-save when page loads
document.addEventListener('DOMContentLoaded', setupAutoSave);
