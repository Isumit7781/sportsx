function toggleEventSummary(eventId) {
    const summaryElement = document.getElementById(`eventSummary${eventId}`);
    if (summaryElement) {
        if (summaryElement.classList.contains('hidden')) {
            summaryElement.classList.remove('hidden');
            summaryElement.classList.add('animate-fadeIn');
        } else {
            summaryElement.classList.add('hidden');
            summaryElement.classList.remove('animate-fadeIn');
        }
    }
}