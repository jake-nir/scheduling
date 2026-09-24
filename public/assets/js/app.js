document.addEventListener('DOMContentLoaded', function () {
    const buttons = document.querySelectorAll('[data-action]');
    buttons.forEach(function (button) {
        button.addEventListener('click', function () {
            console.info('UI action triggered:', button.dataset.action);
        });
    });
});
