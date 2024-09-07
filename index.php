<?php
include 'db.php';

// Pagination settings
$per_page_record = 5;
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$start_from = ($page - 1) * $per_page_record;

// Initialize search term
$search_term = isset($_GET['search']) ? $_GET['search'] : '';
$search_term = trim($search_term);

// Start the session to store search term
session_start();

// If search button is clicked, store search term in session
if (isset($_GET['search_btn'])) {
    $_SESSION['search_term'] = $search_term;
}

// Retrieve search term from session if available
if (isset($_SESSION['search_term'])) {
    $search_term = $_SESSION['search_term'];
}

// Filter settings
$date_filter = isset($_GET['date_filter']) ? $_GET['date_filter'] : 'date';
$sort_order = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'ASC';
$cat_filter = isset($_GET['cat_filter']) ? $_GET['cat_filter'] : '';
$tag_filter = isset($_GET['tag_filter']) ? $_GET['tag_filter'] : '';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$price_start = isset($_GET['price_start']) ? $_GET['price_start'] : '';
$price_end = isset($_GET['price_end']) ? $_GET['price_end'] : '';

// Build SQL query with sorting and filters
$query = "
SELECT 
    p.*, 
    GROUP_CONCAT(DISTINCT g.name_ SEPARATOR ',') as galleries,
    GROUP_CONCAT(DISTINCT c.name_ SEPARATOR ', ') as categories,
    GROUP_CONCAT(DISTINCT t.name_ SEPARATOR ', ') as tags
FROM 
    products p
LEFT JOIN 
    product_property pg ON p.id = pg.product_id AND pg.property_id IN (SELECT id FROM property WHERE type_ = 'gallery')
LEFT JOIN 
    property g ON pg.property_id = g.id
LEFT JOIN 
    product_property pc ON p.id = pc.product_id AND pc.property_id IN (SELECT id FROM property WHERE type_ = 'category')
LEFT JOIN 
    property c ON pc.property_id = c.id
LEFT JOIN 
    product_property pt ON p.id = pt.product_id AND pt.property_id IN (SELECT id FROM property WHERE type_ = 'tag')
LEFT JOIN 
    property t ON pt.property_id = t.id
WHERE 
    p.title LIKE '%$search_term%'";

if (!empty($cat_filter)) {
    $query .= " AND p.id IN (SELECT product_id FROM product_property WHERE property_id = $cat_filter)";
}

if (!empty($tag_filter)) {
    $query .= " AND p.id IN (SELECT product_id FROM product_property WHERE property_id = $tag_filter)";
}

if (!empty($start_date)) {
    $query .= " AND p.date >= '$start_date'";
}

if (!empty($end_date)) {
    $query .= " AND p.date <= '$end_date'";
}

if (!empty($price_start)) {
    $query .= " AND p.price >= $price_start";
}

if (!empty($price_end)) {
    $query .= " AND p.price <= $price_end";
}

$query .= "
GROUP BY 
    p.id
ORDER BY 
    CASE
        WHEN '$date_filter' = 'product_name' THEN p.title 
    END $sort_order,
    CASE
        WHEN '$date_filter' = 'price' THEN p.price 
    END $sort_order,
    CASE
        WHEN '$date_filter' = 'date' THEN p.date 
    END $sort_order
LIMIT 
    $start_from, $per_page_record";

$rs_result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <!-- semantic ui -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.5.0/semantic.min.css" integrity="sha512-KXol4x3sVoO+8ZsWPFI/r5KBVB/ssCGB5tsv2nVOKwLg33wTFP3fmnXa47FdSVIshVTgsYk/1734xSk9aFIa4A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- css -->
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <h1>PHP1</h1>
    <header>
        <form action="" method="get">
            <div class="ui secondary menu">
                <a href="add_product.php" class="ui button openModal">Add product</a>
                <a href="add_property.php" class="ui button">Add property</a>
                <a class="ui button">Sync from VillaTheme</a>
                <div class="right menu">
                    <div class="item">
                        <div class="ui icon input">
                            <input name="search" class="search_input" type="text" placeholder="Search product..." value="<?php echo htmlspecialchars($search_term); ?>">
                            <button name="search_btn" class="search_btn">
                                <i class="search link icon"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="filter_box">
                <div>
                    <select name="date_filter" class="ui dropdown">
                        <option value="date" <?php if ($date_filter == 'date') echo 'selected'; ?>>Date</option>
                        <option value="product_name" <?php if ($date_filter == 'product_name') echo 'selected'; ?>>Product name</option>
                        <option value="price" <?php if ($date_filter == 'price') echo 'selected'; ?>>Price</option>
                    </select>
                </div>
                <div>
                    <select name="sort_order" class="asc ui dropdown">
                        <option value="ASC" <?php if ($sort_order == 'ASC') echo 'selected'; ?>>ASC</option>
                        <option value="DESC" <?php if ($sort_order == 'DESC') echo 'selected'; ?>>DESC</option>
                    </select>
                </div>
                <div>
                    <select name="cat_filter" class="cate ui dropdown">
                        <option value="" <?php if ($cat_filter == '') echo 'selected'; ?>>Category</option>
                        <!-- Populate category options from the database -->
                        <?php
                        $cat_query = "SELECT id, name_ FROM property WHERE type_ = 'category'";
                        $cat_result = $conn->query($cat_query);
                        while ($cat_row = $cat_result->fetch_assoc()) {
                            echo "<option value='" . $cat_row['id'] . "' " . ($cat_filter == $cat_row['id'] ? 'selected' : '') . ">" . $cat_row['name_'] . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div>
                    <select name="tag_filter" class="tag ui dropdown">
                        <option value="" <?php if ($tag_filter == '') echo 'selected'; ?>>Select tag</option>
                        <!-- Populate tag options from the database -->
                        <?php
                        $tag_query = "SELECT id, name_ FROM property WHERE type_ = 'tag'";
                        $tag_result = $conn->query($tag_query);
                        while ($tag_row = $tag_result->fetch_assoc()) {
                            echo "<option value='" . $tag_row['id'] . "' " . ($tag_filter == $tag_row['id'] ? 'selected' : '') . ">" . $tag_row['name_'] . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="ui input">
                    <input type="date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>">
                </div>
                <div class="ui input">
                    <input type="date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>">
                </div>
                <div class="ui input">
                    <input type="text" name="price_start" placeholder="price from" value="<?php echo htmlspecialchars($price_start); ?>">
                </div>
                <div class="ui input">
                    <input type="text" name="price_end" placeholder="price to" value="<?php echo htmlspecialchars($price_end); ?>">
                </div>

                <button name="search_btn" class="filter ui button">Filter</button>
            </div>
        </form>
    </header>

    <section class="table_box">
        <table class="ui celled table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Product name</th>
                    <th>SKU</th>
                    <th>Price</th>
                    <th>Feature Image</th>
                    <th>Gallery</th>
                    <th>Categories</th>
                    <th>Tags</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($rs_result->num_rows > 0) {
                    while ($row = $rs_result->fetch_assoc()) {
                        $date = $row['date'];
                        $title = $row['title'];
                        $sku = $row['sku'];
                        $price = $row['price'];
                        $featured_image = $row['featured_image'];
                        $galleries = explode(',', $row['galleries']);
                        $categories = $row['categories'];
                        $tags = $row['tags'];
                ?>
                        <tr>
                            <td><?php echo $date ?></td>
                            <td><?php echo $title ?></td>
                            <td><?php echo $sku ?></td>
                            <td><?php echo $price ?></td>
                            <td><img src="./uploads/<?php echo $featured_image ?>" alt="" width="30px"></td>
                            <td>
                                <?php foreach ($galleries as $gallery_image) : ?>
                                    <img src="./uploads/<?php echo $gallery_image ?>" alt="" width="30px">
                                <?php endforeach; ?>
                            </td>
                            <td><?php echo $categories ?></td>
                            <td><?php echo $tags ?></td>
                            <td data-label="Job">
                                <a href="edit.php?editid=<?php echo $row['id']; ?>" class="btn_edit openModal_edit">
                                    <i class="edit icon"></i>

                                </a>
                                <a onclick="return confirm('sure to delete !'); " href="delete.php?deleteid=<?php echo $row['id']; ?>" class="btn_delete">
                                    <i class="trash icon"></i>

                                </a>
                            </td>
                        </tr>
                <?php
                    }
                } else {
                    echo "<tr><td colspan='9' style='text-align:center;'>No data found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </section>

    <?php
    if($rs_result->num_rows ==0) {

    
     ?>

    <div class="box_pagination">
        <div aria-label="Pagination Navigation" role="navigation" class="ui pagination menu">
            <?php
            // Count the total number of records for pagination
            $query = "
            SELECT COUNT(DISTINCT p.id) 
            FROM products p
            LEFT JOIN product_property pg ON p.id = pg.product_id AND pg.property_id IN (SELECT id FROM property WHERE type_ = 'gallery')
            LEFT JOIN property g ON pg.property_id = g.id
            LEFT JOIN product_property pc ON p.id = pc.product_id AND pc.property_id IN (SELECT id FROM property WHERE type_ = 'category')
            LEFT JOIN property c ON pc.property_id = c.id
            LEFT JOIN product_property pt ON p.id = pt.product_id AND pt.property_id IN (SELECT id FROM property WHERE type_ = 'tag')
            LEFT JOIN property t ON pt.property_id = t.id
            WHERE p.title LIKE '%$search_term%'";

            if (!empty($cat_filter)) {
                $query .= " AND p.id IN (SELECT product_id FROM product_property WHERE property_id = $cat_filter)";
            }

            if (!empty($tag_filter)) {
                $query .= " AND p.id IN (SELECT product_id FROM product_property WHERE property_id = $tag_filter)";
            }

            if (!empty($start_date)) {
                $query .= " AND p.date >= '$start_date'";
            }

            if (!empty($end_date)) {
                $query .= " AND p.date <= '$end_date'";
            }

            if (!empty($price_start)) {
                $query .= " AND p.price >= $price_start";
            }

            if (!empty($price_end)) {
                $query .= " AND p.price <= $price_end";
            }

            $rs_result = $conn->query($query);
            $row = $rs_result->fetch_row();
            $total_records = $row[0];
            $total_pages = ceil($total_records / $per_page_record);

            if ($page >= 2) {
            ?>
                <a href="index.php?page=<?php echo $page - 1; ?>&search=<?php echo htmlspecialchars($search_term); ?>&date_filter=<?php echo $date_filter; ?>&sort_order=<?php echo $sort_order; ?>&cat_filter=<?php echo $cat_filter; ?>&tag_filter=<?php echo $tag_filter; ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>&price_start=<?php echo $price_start; ?>&price_end=<?php echo $price_end; ?>" class="item">
                    <i class="arrow left icon"></i>
                </a>
                <?php
            }
            for ($i = 1; $i <= $total_pages; $i++) {
                if ($i == $page) {
                ?>
                    <a href="index.php?page=<?php echo $i; ?>&search=<?php echo htmlspecialchars($search_term); ?>&date_filter=<?php echo $date_filter; ?>&sort_order=<?php echo $sort_order; ?>&cat_filter=<?php echo $cat_filter; ?>&tag_filter=<?php echo $tag_filter; ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>&price_start=<?php echo $price_start; ?>&price_end=<?php echo $price_end; ?>" class="active item"><?php echo $i; ?></a>
                <?php
                } else {
                ?>
                    <a href="index.php?page=<?php echo $i; ?>&search=<?php echo htmlspecialchars($search_term); ?>&date_filter=<?php echo $date_filter; ?>&sort_order=<?php echo $sort_order; ?>&cat_filter=<?php echo $cat_filter; ?>&tag_filter=<?php echo $tag_filter; ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>&price_start=<?php echo $price_start; ?>&price_end=<?php echo $price_end; ?>" class="item"><?php echo $i; ?></a>
                <?php
                }
            }
            if ($page < $total_pages) {
                ?>
                <a href="index.php?page=<?php echo $page + 1; ?>&search=<?php echo htmlspecialchars($search_term); ?>&date_filter=<?php echo $date_filter; ?>&sort_order=<?php echo $sort_order; ?>&cat_filter=<?php echo $cat_filter; ?>&tag_filter=<?php echo $tag_filter; ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>&price_start=<?php echo $price_start; ?>&price_end=<?php echo $price_end; ?>" class="item">
                    <i class="arrow right icon"></i>
                </a>
            <?php
            }
            ?>
        </div>
    </div>
    <?php
    }
    ?>
</body>

</html>