document.addEventListener('DOMContentLoaded', function() {
    // Элементы DOM
    const filtersForm = document.getElementById('filters-form');
    const resetBtn = document.getElementById('reset-filters');
    const checkboxes = document.querySelectorAll('input[type="checkbox"]');
    const productCards = document.querySelectorAll('.product-card');
    const filterGroups = document.querySelectorAll('.filter-group');
    const sortBy = document.getElementById('sort-by');
    const sortOrder = document.getElementById('sort-order');

    // 1. Обработчики событий для фильтров
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', applyFiltersAndSorting);
    });

    // 2. Сброс фильтров
    resetBtn.addEventListener('click', function() {
        checkboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
        applyFiltersAndSorting();
    });

    // 3. Сворачивание/разворачивание групп фильтров
    filterGroups.forEach(group => {
        const heading = group.querySelector('h3');
        const options = group.querySelector('.filter-options');

        heading.addEventListener('click', function() {
            options.style.display = options.style.display === 'none' ? 'flex' : 'none';
            const arrow = this.querySelector('.arrow');
            arrow.textContent = options.style.display === 'none' ? '→' : '↓';
        });
    });

    // 4. Обработчики для сортировки
    sortBy.addEventListener('change', applyFiltersAndSorting);
    sortOrder.addEventListener('change', applyFiltersAndSorting);

    // 5. Основная функция фильтрации и сортировки
    function applyFiltersAndSorting() {
        // Фильтрация
        const selectedTypes = getSelectedValues('types[]');
        const selectedColors = getSelectedValues('colors[]');
        const selectedShapes = getSelectedValues('shapes[]');

        const visibleCards = [];

        productCards.forEach(card => {
            const isVisible = checkCardVisibility(
                card,
                selectedTypes,
                selectedColors,
                selectedShapes
            );

            if (isVisible) {
                card.style.display = 'block';
                visibleCards.push(card);
            } else {
                card.style.display = 'none';
            }
        });

        // Сортировка
        sortCards(visibleCards);

        // Обновление DOM
        updateProductsGrid(visibleCards);
    }

    // 6. Вспомогательные функции
    function getSelectedValues(name) {
        return Array.from(document.querySelectorAll(`input[name="${name}"]:checked`))
            .map(el => el.value);
    }

    function checkCardVisibility(card, types, colors, shapes) {
        const cardType = card.dataset.type;
        const cardColor = card.dataset.color;
        const cardShape = card.dataset.shape;

        const typeMatch = types.length === 0 || types.includes(cardType);
        const colorMatch = colors.length === 0 || colors.includes(cardColor);
        const shapeMatch = shapes.length === 0 || (cardShape && shapes.includes(cardShape));

        return typeMatch && colorMatch && shapeMatch;
    }

    function sortCards(cards) {
        const sortField = sortBy.value;
        const sortDir = sortOrder.value;

        cards.sort((a, b) => {
            const aValue = parseFloat(a.dataset[sortField]) || 0;
            const bValue = parseFloat(b.dataset[sortField]) || 0;
            return sortDir === 'ASC' ? aValue - bValue : bValue - aValue;
        });
    }

    function updateProductsGrid(cards) {
        const container = document.querySelector('.products-grid');
        // Сначала очищаем (для правильного порядка)
        container.innerHTML = '';
        // Затем добавляем отсортированные карточки
        cards.forEach(card => {
            container.appendChild(card);
        });
    }

    // Инициализация
    applyFiltersAndSorting();
});



