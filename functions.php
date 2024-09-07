<?php
include 'db.php';
function test_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// check if property_id exists and is valid
function isValidPropertyId($property_id, $type, $conn)
{
    $property_id = (int) $property_id;
    // security
    $type = $conn->real_escape_string($type);
    $check_sql = "SELECT COUNT(*) as count FROM property WHERE id = $property_id AND type_ = '$type'";
    $result = $conn->query($check_sql);
    $row = $result->fetch_assoc();
    return $row['count'] > 0;
}

function updateProductProperties($product_id, $properties, $type, $conn)
{
    // Validate properties
    $valid_properties = [];
    foreach ($properties as $property_id) {
        $property_id = (int) $property_id;
        $sql_validate_property = "SELECT id FROM property WHERE id = $property_id AND type_ = '$type'";
        $result = $conn->query($sql_validate_property);
        if ($result->num_rows > 0) {
            $valid_properties[] = $property_id;
        }
    }

    // Clear existing properties for the product
    $sql_delete_old = "DELETE FROM product_property WHERE product_id = $product_id AND property_id IN (SELECT id FROM property WHERE type_ = '$type')";
    $conn->query($sql_delete_old);

    // Insert new properties
    foreach ($valid_properties as $property_id) {
        $sql_insert_new = "INSERT INTO product_property (product_id, property_id) VALUES ('$product_id', '$property_id')";
        $conn->query($sql_insert_new);
    }
}




?>