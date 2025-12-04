/**
 * Common JavaScript Utilities
 */

// Toast Notification System
const Toast = {
    show: function(message, type = 'info', duration = 3000) {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.textContent = message;
        toast.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 1rem 1.5rem;
            background-color: ${this.getColor(type)};
            color: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            z-index: 9999;
            animation: slideIn 0.3s ease;
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, duration);
    },
    
    getColor: function(type) {
        const colors = {
            success: '#388e3c',
            error: '#d32f2f',
            warning: '#f57c00',
            info: '#0288d1'
        };
        return colors[type] || colors.info;
    }
};

// Add toast animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// AJAX Helper
const Ajax = {
    post: async function(url, data) {
        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            return await response.json();
        } catch (error) {
            console.error('AJAX Error:', error);
            throw error;
        }
    },
    
    get: async function(url) {
        try {
            const response = await fetch(url);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            return await response.json();
        } catch (error) {
            console.error('AJAX Error:', error);
            throw error;
        }
    }
};

// Form Validation Helper
const FormValidator = {
    validate: function(formElement) {
        let isValid = true;
        const inputs = formElement.querySelectorAll('input[required], select[required], textarea[required]');
        
        inputs.forEach(input => {
            if (!input.value.trim()) {
                this.showError(input, 'This field is required');
                isValid = false;
            } else {
                this.clearError(input);
            }
        });
        
        return isValid;
    },
    
    showError: function(input, message) {
        input.style.borderColor = '#d32f2f';
        let errorElement = input.nextElementSibling;
        
        if (!errorElement || !errorElement.classList.contains('form-error')) {
            errorElement = document.createElement('div');
            errorElement.className = 'form-error';
            input.parentNode.insertBefore(errorElement, input.nextSibling);
        }
        
        errorElement.textContent = message;
        errorElement.classList.add('show');
    },
    
    clearError: function(input) {
        input.style.borderColor = '';
        const errorElement = input.nextElementSibling;
        
        if (errorElement && errorElement.classList.contains('form-error')) {
            errorElement.classList.remove('show');
        }
    }
};

// Modal Helper
const Modal = {
    show: function(title, content, buttons = []) {
        const modal = document.createElement('div');
        modal.className = 'modal-overlay';
        modal.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 10000;
        `;
        
        const modalContent = document.createElement('div');
        modalContent.className = 'modal-content';
        modalContent.style.cssText = `
            background-color: white;
            padding: 2rem;
            border-radius: 12px;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 8px 16px rgba(0,0,0,0.3);
        `;
        
        modalContent.innerHTML = `
            <h3 style="margin-bottom: 1rem;">${title}</h3>
            <div style="margin-bottom: 1.5rem;">${content}</div>
            <div class="modal-buttons" style="display: flex; gap: 0.5rem; justify-content: flex-end;"></div>
        `;
        
        const buttonContainer = modalContent.querySelector('.modal-buttons');
        
        if (buttons.length === 0) {
            buttons = [{
                text: 'Close',
                class: 'btn-primary',
                onClick: () => this.close(modal)
            }];
        }
        
        buttons.forEach(btn => {
            const button = document.createElement('button');
            button.className = `btn ${btn.class || 'btn-primary'}`;
            button.textContent = btn.text;
            button.onclick = () => {
                if (btn.onClick) btn.onClick();
                this.close(modal);
            };
            buttonContainer.appendChild(button);
        });
        
        modal.appendChild(modalContent);
        document.body.appendChild(modal);
        
        modal.onclick = (e) => {
            if (e.target === modal) {
                this.close(modal);
            }
        };
    },
    
    close: function(modal) {
        modal.remove();
    }
};

// Confirm Dialog
function confirm(message, onConfirm, onCancel) {
    Modal.show('Confirm', message, [
        {
            text: 'Cancel',
            class: 'btn-outline',
            onClick: onCancel
        },
        {
            text: 'Confirm',
            class: 'btn-primary',
            onClick: onConfirm
        }
    ]);
}

// Loading Indicator
const Loading = {
    show: function() {
        const loader = document.createElement('div');
        loader.id = 'global-loader';
        loader.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255,255,255,0.9);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9998;
        `;
        loader.innerHTML = '<div class="spinner"></div>';
        document.body.appendChild(loader);
    },
    
    hide: function() {
        const loader = document.getElementById('global-loader');
        if (loader) {
            loader.remove();
        }
    }
};

// Format date
function formatDate(dateString) {
    const date = new Date(dateString);
    const options = { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    };
    return date.toLocaleDateString('en-US', options);
}

// Debounce function
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}
