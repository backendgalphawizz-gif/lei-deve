document.querySelectorAll('.lei-pub-accordion-item').forEach(function (item) {
    item.addEventListener('toggle', function () {
        if (item.open) {
            item.parentElement.querySelectorAll('details[open]').forEach(function (other) {
                if (other !== item) other.open = false;
            });
        }
    });
});
