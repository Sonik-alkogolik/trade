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
    <div class="tab-content">
        <?php
        // Получаем все товары
        $products = wc_get_products([
            'limit' => -1, // Без ограничения
            'status' => 'publish', // Только опубликованные товары
            'orderby' => 'title', // Сортировка по имени
            'order' => 'ASC', // По возрастанию (по алфавиту)
        ]);

        // Проверяем, есть ли товары
        if (!empty($products)) {
            foreach ($products as $product) {
                // Получаем цену товара
                $price = $product->get_price();

                // Получаем категории товара
                $categories = wp_get_post_terms($product->get_id(), 'product_cat', ['orderby' => 'parent', 'order' => 'ASC']);
                $category_slugs = wp_list_pluck($categories, 'slug');

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

                // Выводим товар в обёртке div
                echo '<div class="product-item ' . esc_attr($stock_class) . '" data-product-category-subcategory="' . esc_attr(implode(' > ', $category_slugs)) . '">';

                // Название товара с ссылкой
                echo '<a href="' . esc_url(get_permalink($product->get_id())) . '" class="product-link">';
                echo '<h2>' . esc_html($product->get_name()) . '</h2>';
                echo '</a>';

                // Отображение цены
                echo '<span class="product-price">';
                echo $price ? wc_price($price) : esc_html__('Цена не указана', 'trade');
                echo '</span>';

                // Отображение категорий
                echo '<span class="product-categories">';
                echo esc_html(implode(', ', $category_slugs));
                echo '</span>';

                // Отображение количества товара
                echo '<span class="product-stock">';
                echo $stock_quantity !== null ? esc_html($stock_quantity) . ' ' . esc_html__('шт.', 'trade') : esc_html__('Не указано', 'trade');
                echo '</span>';

                // Кнопка "Добавить в корзину"
                echo '<a href="' . esc_url(wc_get_cart_url() . '?add-to-cart=' . $product->get_id()) . '" class="add-to-cart-button">';
                esc_html_e('Добавить в корзину', 'trade');
                echo '</a>';

                echo '</div>'; // Закрываем div
            }
        } else {
            echo '<p>Нет товаров для отображения.</p>';
        }
        ?>
    </div>
</div>






<?php
endif;

get_footer();
