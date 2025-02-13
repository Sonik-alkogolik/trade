document.addEventListener("DOMContentLoaded", () => {
    const stockFilterButtons = document.querySelectorAll(".filter-button"); // Предположим, у вас есть кнопки с классом `filter-button`
    const productItems = document.querySelectorAll(".product-item");

    stockFilterButtons.forEach(button => {
        button.addEventListener("click", () => {
            const filterClass = button.dataset.filter; // Класс для фильтрации из атрибута `data-filter`

            productItems.forEach(item => {
                if (filterClass === "all" || item.classList.contains(filterClass)) {
                    item.style.display = "flex";
                    // Удаляем активный класс у всех списков
        document.querySelectorAll('.subcategories-list').forEach(function (list) {
            list.classList.remove('active');
        });
                } else {
                    item.style.display = "none";
                }
            });
        });
    });
});


document.addEventListener('DOMContentLoaded', () => {
    // Все кнопки категорий
    const categoryButtons = document.querySelectorAll('button[data-category]');
    // Все товары
    const productItems = document.querySelectorAll('.product-item');

    // Добавляем обработчик события на каждую кнопку
    categoryButtons.forEach(button => {
        button.addEventListener('click', () => {
            // Получаем категорию из атрибута data-category
            const category = button.getAttribute('data-category');

            // Удаляем класс active у всех кнопок
            categoryButtons.forEach(btn => btn.classList.remove('active'));

            // Добавляем класс active для текущей кнопки
            button.classList.add('active');

            // Скрываем все товары
            productItems.forEach(item => {
                item.style.display = 'none';

                // Проверяем, содержит ли товар соответствующую категорию
                const productCategory = item.getAttribute('data-product-category-subcategory');
                if (productCategory && productCategory.includes(category)) {
                    item.style.display = 'block';
                }
            });
        });
    });
});
