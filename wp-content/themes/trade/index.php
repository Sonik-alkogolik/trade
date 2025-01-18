<?php
/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package trade
 */

get_header();

// Получаем только родительские категории товаров, исключая категорию с ID 15
$parent_categories = get_terms([
    'taxonomy'   => 'product_cat',
    'hide_empty' => false,
    'exclude'    => [15],
    'parent'     => 0, // Только родительские категории
]);

if (!empty($parent_categories)) :
?>


<?php
// Рекурсивная функция для вывода подкатегорий и их товаров
function render_subcategories($parent_id) {
    // Получаем подкатегории текущей категории
    $subcategories = get_terms([
        'taxonomy' => 'product_cat',
        'parent'   => $parent_id,
        'orderby'  => 'name',
        'order'    => 'ASC',
        'hide_empty' => false,  // Убедитесь, что показываются все подкатегории
    ]);

    if (!empty($subcategories)) {
        echo '<ul class="subcategories-list">';
        foreach ($subcategories as $subcategory) {
            // Выводим подкатегорию с нужными аттрибутами
            echo '<li class="subcategory-item">';
            echo '<button class="subcategory-button" 
            data-category="' . esc_attr($subcategory->slug) . '" 
            data-subcategory-slug="' . esc_attr($subcategory->slug) . '">
            ' . esc_html($subcategory->name) . '</button>';
            
            // Рекурсивный вызов для вывода вложенных подкатегорий
            render_subcategories($subcategory->term_id);

            echo '</li>';
        }
        echo '</ul>';
    }
}

// Пример вызова функции для главной категории
render_subcategories(0);  // 0 — это ID родительской категории
?>

<div class="tabs-header">
    <?php foreach ($parent_categories as $index => $parent_category) : ?>
        <div class="tab-container">
            <button class="tab-button <?php echo $index === 0 ? 'active' : ''; ?>" 
                    data-tab="tab-<?php echo esc_attr($parent_category->term_id); ?>"
                    data-category="<?php echo esc_attr($parent_category->slug); ?>">
                <?php echo esc_html($parent_category->name); ?>
            </button>

            <!-- Рекурсивный вызов для вывода всех подкатегорий -->
            <?php render_subcategories($parent_category->term_id); ?>
        </div>
    <?php endforeach; ?>
</div>
<div class="filters">
    <button class="filter-button" data-filter="all">Все товары</button>
    <button class="filter-button" data-filter="high-stock">Высокий остаток</button>
    <button class="filter-button" data-filter="medium-stock">Средний остаток</button>
    <button class="filter-button" data-filter="low-stock">Низкий остаток</button>
</div>

<div class="tabs-content">
    <?php foreach ($parent_categories as $parent_category) : ?>
        <div class="tab-content" id="tab-<?php echo esc_attr($parent_category->term_id); ?>">
            <?php
            // Получение товаров для родительской категории
            $products = wc_get_products([
                'limit' => -1, // Без ограничения
                'category' => [$parent_category->slug],
                'status' => 'publish', // Только опубликованные товары
            ]);

            // Проверяем, есть ли товары
            if (!empty($products)) :
                foreach ($products as $product) :
                    // Получаем количество товара на складе
                    $stock_quantity = $product->get_stock_quantity();
                    $stock_class = '';

                    // Определение класса по количеству товаров на складе
                    if ($stock_quantity === null || $stock_quantity <= 0) {
                        $stock_class = 'low-stock'; // Нет в наличии
                    } elseif ($stock_quantity <= 3) {
                        $stock_class = 'low-stock'; // Мало товаров
                    } elseif ($stock_quantity <= 5) {
                        $stock_class = 'medium-stock'; // Среднее количество
                    } elseif ($stock_quantity <= 10) {
                        $stock_class = 'medium-stock'; // Среднее количество
                    } else {
                        $stock_class = 'high-stock'; // Большое количество
                    }

                    // Получаем цену товара
                    $price = $product->get_price();

                    // Получаем категории товара
                    $categories = wp_get_post_terms($product->get_id(), 'product_cat', ['orderby' => 'parent', 'order' => 'ASC']);
                    $category_slugs = wp_list_pluck($categories, 'slug'); // Извлекаем только слаги
                    ?>
                    <div class="product-item <?php echo esc_attr($stock_class); ?>" 
                         data-product-category-subcategory="<?php echo esc_attr(implode(' > ', $category_slugs)); ?>">
                         <a href="<?php echo esc_url(get_permalink($product->get_id())); ?>" class="product-link">
        <h2><?php echo esc_html($product->get_name()); ?></h2>
    </a>
                        
                        <!-- Отображение цены товара -->
                        <span class="product-price">
                            <?php 
                            echo $price ? wc_price($price) : esc_html__('Цена не указана', 'trade'); 
                            ?>
                        </span>
                        
                        <!-- Отображение количества товара -->
                        <span class="product-stock">
                            <?php 
                            echo $stock_quantity !== null ? esc_html($stock_quantity) . ' ' . esc_html__('шт.', 'trade') : esc_html__('Не указано', 'trade'); 
                            ?>
                        </span>
                        
                        <!-- Кнопка добавления в корзину -->
                        <a href="<?php echo esc_url( wc_get_cart_url() . '?add-to-cart=' . $product->get_id() ); ?>" 
                           class="add-to-cart-button">
                            <?php esc_html_e('Добавить в корзину', 'trade'); ?>
                        </a>
                    </div>
                <?php
                endforeach;
            else :
                echo '<p>Товары не найдены.</p>';
            endif;
            ?>
        </div>
    <?php endforeach; ?>
</div>









<script>
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

</script>

<script>
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

</script>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const stockFilterButtons = document.querySelectorAll(".filter-button"); // Предположим, у вас есть кнопки с классом `filter-button`
    const productItems = document.querySelectorAll(".product-item");

    stockFilterButtons.forEach(button => {
        button.addEventListener("click", () => {
            const filterClass = button.dataset.filter; // Класс для фильтрации из атрибута `data-filter`

            productItems.forEach(item => {
                if (filterClass === "all" || item.classList.contains(filterClass)) {
                    item.style.display = "block";
                } else {
                    item.style.display = "none";
                }
            });
        });
    });
});


</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
    const sortSelect = document.getElementById('price_sort');

    sortSelect.addEventListener('change', function () {
        const sortOrder = this.value;
        const productLists = document.querySelectorAll('.product-list');

        productLists.forEach(productList => {
            const products = Array.from(productList.querySelectorAll('.product-item'));

            // Сортировка товаров
            products.sort((a, b) => {
                const priceA = parseFloat(a.getAttribute('data-price')) || 0;
                const priceB = parseFloat(b.getAttribute('data-price')) || 0;

                if (sortOrder === 'asc') {
                    return priceA - priceB;
                } else if (sortOrder === 'desc') {
                    return priceB - priceA;
                }
                return 0;
            });

            // Перемещение товаров в отсортированном порядке
            products.forEach(product => productList.appendChild(product));
        });
    });
});

</script>



<?php
endif;

get_footer();
