document.addEventListener('DOMContentLoaded', () => {
    document.addEventListener('click', function (event) {
        // Проверяем клик по табу
        if (event.target.classList.contains('tab-button')) {
            // Удаляем активные классы у всех кнопок табов
            document.querySelectorAll('.tab-button').forEach(function (button) {
                button.classList.remove('active');
            });

            // Удаляем активный класс у всех списков
            document.querySelectorAll('.subcategories-list').forEach(function (list) {
                list.classList.remove('active');
            });

            // Добавляем активный класс к нажатому табу
            event.target.classList.add('active');

            // Находим и показываем соответствующий список подкатегорий
            const parentContainer = event.target.closest('.tab-container');
            const subcategories = parentContainer.querySelector('.subcategories-list');
            if (subcategories) {
                subcategories.classList.add('active');
            }
        }

        // Проверяем клик по кнопке подкатегории
        if (event.target.classList.contains('subcategory-button')) {
            const sublist = event.target.nextElementSibling;
            if (sublist && sublist.classList.contains('subcategories-list')) {
                sublist.classList.toggle('active'); // Переключаем видимость подкатегории
            }
        }
    });
});
