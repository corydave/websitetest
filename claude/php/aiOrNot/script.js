// Handle submit button visibility
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('surveyForm');
    const submitBtn = document.getElementById('submitBtn');
    const radioButtons = document.querySelectorAll('input[name="rating"]');
    
    if (submitBtn && radioButtons.length > 0) {
        radioButtons.forEach(radio => {
            radio.addEventListener('change', function() {
                submitBtn.disabled = false;
                submitBtn.classList.add('visible');
            });
        });
    }
    
    // Animate bars on results page
    const resultBars = document.querySelectorAll('.result-bar');
    if (resultBars.length > 0) {
        // Trigger animation after a short delay
        setTimeout(() => {
            resultBars.forEach(bar => {
                const width = bar.getAttribute('data-width');
                bar.style.width = width + '%';
            });
        }, 100);
    }
});
