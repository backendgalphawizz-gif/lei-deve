(function () {
    'use strict';

    var form = document.querySelector('[data-lei-home-content-form]');
    if (!form) return;

    var itemList = form.querySelector('[data-lei-item-list]');
    var benefitList = form.querySelector('[data-lei-benefit-list]');

    function nextIndex(list) {
        var rows = list.querySelectorAll('[data-lei-repeat-row]');
        return rows.length;
    }

    form.querySelector('[data-lei-add-item]')?.addEventListener('click', function () {
        if (!itemList) return;
        var i = nextIndex(itemList);
        var row = document.createElement('div');
        row.className = 'lei-wm-repeat-row';
        row.setAttribute('data-lei-repeat-row', '');
        row.innerHTML =
            '<input type="text" name="items[' + i + ']" value="" placeholder="List item">' +
            '<button type="button" class="lei-wm-btn-danger lei-wm-btn-sm" data-lei-remove-row aria-label="Remove">&times;</button>';
        itemList.appendChild(row);
    });

    form.querySelector('[data-lei-add-benefit]')?.addEventListener('click', function () {
        if (!benefitList) return;
        var i = nextIndex(benefitList);
        var row = document.createElement('div');
        row.className = 'lei-wm-repeat-row lei-wm-repeat-row--benefit';
        row.setAttribute('data-lei-repeat-row', '');
        row.innerHTML =
            '<input type="text" name="benefit_items[' + i + '][title]" value="" placeholder="Benefit title">' +
            '<textarea name="benefit_items[' + i + '][text]" rows="2" placeholder="Description"></textarea>' +
            '<button type="button" class="lei-wm-btn-danger lei-wm-btn-sm" data-lei-remove-row aria-label="Remove">&times;</button>';
        benefitList.appendChild(row);
    });

    form.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-lei-remove-row]');
        if (!btn) return;
        var row = btn.closest('[data-lei-repeat-row]');
        var list = row?.parentElement;
        if (!row || !list) return;
        var rows = list.querySelectorAll('[data-lei-repeat-row]');
        if (rows.length <= 1) {
            row.querySelector('input, textarea')?.value && (row.querySelector('input, textarea').value = '');
            return;
        }
        row.remove();
    });
})();
