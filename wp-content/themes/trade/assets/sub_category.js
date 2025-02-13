document.addEventListener('DOMContentLoaded', () => {
    // Слушаем клики по кнопкам подкатегорий
    const subcategoryButtons = document.querySelectorAll('.subcategory-button');
    
    subcategoryButtons.forEach(button => {
        button.addEventListener('click', () => {
            const selectedCategory = button.getAttribute('data-category');
            const selectedSubcategory = button.getAttribute('data-subcategory-slug');
            
            // Показываем или скрываем товары
            const products = document.querySelectorAll('.product-item');
            products.forEach(product => {
                const productCategories = product.getAttribute('data-product-category-subcategory');
                if (productCategories && productCategories.includes(selectedCategory)) {
                    product.style.display = ''; // Показываем товар
                } else {
                    product.style.display = 'none'; // Скрываем товар
                }
            });
        });
    });
});
