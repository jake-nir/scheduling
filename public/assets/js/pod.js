document.addEventListener('DOMContentLoaded', function () {
    var printLinks = document.querySelectorAll('[href*="route=pod/print"]');
    printLinks.forEach(function (link) {
        link.addEventListener('click', function (event) {
            event.preventDefault();
            var url = link.getAttribute('href');
            var target = window.open(url, '_blank', 'noopener');
            if (target) {
                target.focus();
            }
        });
    });
});
