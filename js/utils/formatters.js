export function formatTime(isoString) {
    if (!isoString) {
        return '';
    }
    const date = new Date(isoString);
    if (isNaN(date.getTime())) {
        return '';
    }
    return date.toLocaleTimeString('fr-FR', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: false
    });
}

export function formatDateToFrench(dateInput) {
    if (!dateInput) {
        return '';
    }
    const date = new Date(dateInput);
    if (isNaN(date.getTime())) {
        return '';
    }
    const formattedDate = date.toLocaleDateString('fr-FR', {
        weekday: 'long',
        day: 'numeric',
        month: 'long'
    });

    const [weekday, day, month] = formattedDate.split(' ');
    const capitalizedWeekday = weekday.charAt(0).toUpperCase() + weekday.slice(1);

    return [capitalizedWeekday, day, month].join(' ');
}