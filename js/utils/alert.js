export function clearMessages(container) {
        if (container) {
            container.innerHTML = '';
        }
    }

export function displayMessage(container, message, type = 'error') {
        if (container) {
            container.innerHTML = `<p>${message}</p>`;
        }
    }