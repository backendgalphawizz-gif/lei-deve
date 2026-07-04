document.querySelectorAll('.lei-pub-accordion-item').forEach(function (item) {
    item.addEventListener('toggle', function () {
        if (item.open) {
            item.parentElement.querySelectorAll('details[open]').forEach(function (other) {
                if (other !== item) other.open = false;
            });
        }
    });
});

document.querySelectorAll('[data-plan-select-grid]').forEach(function (grid) {
    grid.querySelectorAll('[data-plan-card]:not(.blocked)').forEach(function (card) {
        function selectCard() {
            grid.querySelectorAll('[data-plan-card]').forEach(function (c) {
                c.classList.remove('selected');
                c.setAttribute('aria-pressed', 'false');
            });
            card.classList.add('selected');
            card.setAttribute('aria-pressed', 'true');
        }

        card.addEventListener('click', function (e) {
            if (e.target.closest('a, button, select, input, label')) return;
            selectCard();
        });

        card.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                selectCard();
            }
        });

        if (card.classList.contains('selected')) {
            card.setAttribute('aria-pressed', 'true');
        }
    });
});
