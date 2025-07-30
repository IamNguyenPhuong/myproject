// Library Management System JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Search functionality
    const searchForm = document.getElementById('searchForm');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            const searchInput = document.getElementById('searchInput');
            if (searchInput.value.trim() === '') {
                e.preventDefault();
                showAlert('Vui lòng nhập từ khóa tìm kiếm', 'warning');
            }
        });
    }

    // Book borrowing confirmation
    const borrowButtons = document.querySelectorAll('.borrow-btn');
    borrowButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Bạn có chắc chắn muốn mượn cuốn sách này?')) {
                e.preventDefault();
            }
        });
    });

    // Book return confirmation
    const returnButtons = document.querySelectorAll('.return-btn');
    returnButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Bạn có chắc chắn muốn trả cuốn sách này?')) {
                e.preventDefault();
            }
        });
    });

    // Form validation
    const forms = document.querySelectorAll('.needs-validation');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });

    // Auto-hide alerts
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });

    // Image preview for file uploads
    const imageInputs = document.querySelectorAll('.image-input');
    imageInputs.forEach(input => {
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('imagePreview');
            
            if (file && preview) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
    });

    // Dynamic book availability check
    const bookCards = document.querySelectorAll('.book-card');
    bookCards.forEach(card => {
        const availableBadge = card.querySelector('.availability-badge');
        const borrowBtn = card.querySelector('.borrow-btn');
        
        if (availableBadge && borrowBtn) {
            const isAvailable = availableBadge.textContent.includes('Có sẵn');
            if (!isAvailable) {
                borrowBtn.disabled = true;
                borrowBtn.textContent = 'Đã mượn';
                borrowBtn.classList.remove('btn-primary');
                borrowBtn.classList.add('btn-secondary');
            }
        }
    });

    // Pagination enhancement
    const paginationLinks = document.querySelectorAll('.pagination .page-link');
    paginationLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Add loading state
            const loadingSpinner = document.createElement('span');
            loadingSpinner.className = 'loading me-2';
            this.appendChild(loadingSpinner);
        });
    });

    // Modal enhancements
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        modal.addEventListener('show.bs.modal', function() {
            // Add fade-in animation
            this.classList.add('fade-in');
        });
    });

    // Responsive table
    const tables = document.querySelectorAll('.table-responsive');
    tables.forEach(table => {
        if (table.scrollWidth > table.clientWidth) {
            table.classList.add('has-scroll');
        }
    });

    // Book filter functionality
    const filterSelects = document.querySelectorAll('.filter-select');
    filterSelects.forEach(select => {
        select.addEventListener('change', function() {
            const form = this.closest('form');
            if (form) {
                form.submit();
            }
        });
    });

    // Auto-complete search suggestions
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            
            if (query.length >= 2) {
                searchTimeout = setTimeout(() => {
                    fetchSearchSuggestions(query);
                }, 300);
            }
        });
    }

    // Book rating system
    const ratingStars = document.querySelectorAll('.rating-star');
    ratingStars.forEach(star => {
        star.addEventListener('click', function() {
            const rating = this.dataset.rating;
            const bookId = this.dataset.bookId;
            submitRating(bookId, rating);
        });
    });

    // Notification system
    checkNotifications();
});

// Utility functions
function showAlert(message, type = 'info') {
    const alertContainer = document.getElementById('alertContainer') || document.body;
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    alertContainer.appendChild(alertDiv);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}

function fetchSearchSuggestions(query) {
    fetch(`api/search_suggestions.php?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            displaySearchSuggestions(data);
        })
        .catch(error => {
            console.error('Error fetching suggestions:', error);
        });
}

function displaySearchSuggestions(suggestions) {
    const suggestionsContainer = document.getElementById('searchSuggestions');
    if (!suggestionsContainer) return;
    
    suggestionsContainer.innerHTML = '';
    
    suggestions.forEach(suggestion => {
        const div = document.createElement('div');
        div.className = 'suggestion-item';
        div.textContent = suggestion.title;
        div.addEventListener('click', () => {
            document.getElementById('searchInput').value = suggestion.title;
            suggestionsContainer.innerHTML = '';
        });
        suggestionsContainer.appendChild(div);
    });
}

function submitRating(bookId, rating) {
    fetch('api/submit_rating.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            book_id: bookId,
            rating: rating
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Cảm ơn bạn đã đánh giá!', 'success');
            updateRatingDisplay(bookId, data.average_rating);
        } else {
            showAlert('Có lỗi xảy ra khi đánh giá', 'danger');
        }
    })
    .catch(error => {
        console.error('Error submitting rating:', error);
        showAlert('Có lỗi xảy ra khi đánh giá', 'danger');
    });
}

function updateRatingDisplay(bookId, averageRating) {
    const ratingDisplay = document.querySelector(`[data-book-rating="${bookId}"]`);
    if (ratingDisplay) {
        ratingDisplay.textContent = averageRating.toFixed(1);
    }
}

function checkNotifications() {
    // Check for overdue books
    fetch('api/check_notifications.php')
        .then(response => response.json())
        .then(data => {
            if (data.overdue_books && data.overdue_books.length > 0) {
                showNotification('Bạn có sách quá hạn cần trả!', 'warning');
            }
        })
        .catch(error => {
            console.error('Error checking notifications:', error);
        });
}

function showNotification(message, type) {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-bell me-2"></i>
            ${message}
            <button type="button" class="btn-close btn-close-white ms-2" onclick="this.parentElement.parentElement.remove()"></button>
        </div>
    `;
    
    // Add to page
    document.body.appendChild(notification);
    
    // Auto-remove after 10 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 10000);
}

// Export functions for global use
window.LibrarySystem = {
    showAlert,
    showNotification,
    submitRating
}; 