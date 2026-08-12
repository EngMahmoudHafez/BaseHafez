<script>
    // One handler drives every add/remove repeatable list on the page.
    document.addEventListener('click', function (event) {
        const removeButton = event.target.closest('.section-repeatable-remove');
        if (removeButton) {
            removeButton.closest('.section-repeatable-item').remove();
            return;
        }

        const addButton = event.target.closest('.section-repeatable-add');
        if (!addButton) {
            return;
        }

        const wrapper = addButton.closest('.section-repeatable');
        const list = wrapper.querySelector('.section-repeatable-list');
        const max = Number(wrapper.dataset.max || 0);
        if (max && list.children.length >= max) {
            return;
        }

        const item = wrapper.querySelector('template').content.firstElementChild.cloneNode(true);
        const index = list.children.length;
        const itemPrefix = wrapper.dataset.itemPrefix;
        const keyBase = wrapper.dataset.keyBase || 'item';

        item.querySelectorAll('[data-name]').forEach(function (input) {
            const dataName = input.dataset.name;
            input.name = `${itemPrefix}[${index}][${dataName}]`;
            if (dataName === 'key') {
                input.value = `${keyBase}-${Date.now()}`;
            } else if (dataName === 'sort_order') {
                input.value = index + 1;
            }
        });

        list.appendChild(item);
    });
</script>
