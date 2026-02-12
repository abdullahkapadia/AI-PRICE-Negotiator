<?php

set_time_limit(600);
ini_set('memory_limit', '512M');

require_once("../config/db_connect.php");

echo "<pre style='font-family:monospace; background:#1e1e2e; color:#a6e3a1; padding:20px; border-radius:10px;'>\n";
echo "====================================\n";
echo "  AI Price Negotiator - DB Seeder\n";
echo "====================================\n\n";

$first_names = ['Aarav','Vivaan','Aditya','Vihaan','Arjun','Sai','Reyansh','Ayaan','Krishna','Ishaan',
    'Ananya','Diya','Saanvi','Aadhya','Isha','Riya','Priya','Neha','Pooja','Meera',
    'Rohan','Karan','Rahul','Amit','Raj','Vikram','Sanjay','Deepak','Nikhil','Manish',
    'Kavita','Sunita','Anjali','Divya','Shruti','Sneha','Tanvi','Nisha','Swati','Pallavi',
    'Suresh','Mahesh','Ramesh','Ganesh','Naresh','Dinesh','Rajesh','Yogesh','Hitesh','Ritesh',
    'Lakshmi','Sarita','Geeta','Seema','Rekha','Usha','Sushma','Poonam','Archana','Vandana',
    'Mohit','Nitin','Sachin','Tushar','Vinay','Ajay','Vijay','Manoj','Anil','Sunil'];

$last_names = ['Sharma','Patel','Singh','Kumar','Gupta','Shah','Joshi','Desai','Modi','Mehta',
    'Verma','Chauhan','Yadav','Thakur','Reddy','Nair','Das','Bose','Iyer','Rao',
    'Agarwal','Jain','Malhotra','Kapoor','Chopra','Khanna','Sinha','Mishra','Tiwari','Pandey',
    'Kulkarni','Patil','Deshpande','More','Pawar','Shinde','Jadhav','Bhatt','Trivedi','Saxena'];

$cities = ['Mumbai','Delhi','Bangalore','Hyderabad','Ahmedabad','Chennai','Kolkata','Pune','Jaipur','Lucknow',
    'Surat','Nagpur','Indore','Bhopal','Vadodara','Chandigarh','Coimbatore','Kochi','Visakhapatnam','Noida'];

$states = ['Maharashtra','Delhi','Karnataka','Telangana','Gujarat','Tamil Nadu','West Bengal','Maharashtra','Rajasthan','Uttar Pradesh',
    'Gujarat','Maharashtra','Madhya Pradesh','Madhya Pradesh','Gujarat','Punjab','Tamil Nadu','Kerala','Andhra Pradesh','Uttar Pradesh'];

$streets = ['MG Road','Station Road','Ring Road','Gandhi Nagar','Nehru Street','Patel Nagar','Rajiv Chowk',
    'Subhash Marg','Tilak Road','Ambedkar Lane','Shastri Nagar','Vikas Puri','Sector 15','Phase 2'];

$business_types = ['Electronics','Traders','Enterprises','Solutions','Mart','Hub','Store','Gallery','Point','World','Plaza','Emporium','Collection'];

$product_data = [
    [1, ['Wireless Earbuds','Bluetooth Speaker','Smart Watch','Power Bank 10000mAh','USB-C Hub','Laptop Stand','Webcam HD','Mechanical Keyboard','Gaming Mouse','Portable Monitor',
         'Tablet 10 inch','Wireless Charger','LED Desk Lamp','Smart Plug','Action Camera','Drone Mini','VR Headset','Smart Ring','Portable SSD 1TB','WiFi Router',
         'Noise Cancelling Headphones','Smart Display','Security Camera','Car Dash Cam','Electric Toothbrush','Smart Thermostat','Fitness Tracker','E-Reader',
         'Portable Projector','Digital Photo Frame'], 500, 30000],
    [2, ['Cotton T-Shirt','Denim Jeans','Formal Shirt','Kurta Set','Saree Silk','Leather Jacket','Sneakers','Sunglasses','Wrist Watch','Backpack',
         'Hoodie Pullover','Chinos Pants','Polo T-Shirt','Ethnic Dress','Sports Shoes','Handbag Leather','Belt Genuine Leather','Scarf Pashmina','Cap Baseball',
         'Wallet RFID','Track Pants','Blazer Slim Fit','Sweatshirt','Cargo Pants','Flip Flops','Dupatta Silk','Sherwani','Lehenga','Palazzo Pants','Denim Jacket'], 299, 8000],
    [3, ['Pressure Cooker 5L','Non-Stick Pan Set','Mixer Grinder','Air Fryer','Water Purifier','Induction Cooktop','Microwave Oven','Vacuum Cleaner','Iron Steam',
         'Ceiling Fan','Table Fan','Room Heater','Air Cooler','Bed Sheet Set','Pillow Memory Foam','Curtains Blackout','Door Mat','Wall Clock','Dinner Set 24pc',
         'Glass Set 6pc','Knife Set','Storage Containers','Lunch Box Steel','Water Bottle 1L','Chopping Board','Towel Set','Bathroom Organizer',
         'Shoe Rack','Bookshelf','Study Table'], 199, 15000],
    [4, ['Python Programming','Data Science Handbook','Machine Learning Basics','Web Development Guide','Java Complete Reference','C++ Primer','Algorithm Design',
         'Database Systems','Operating System Concepts','Computer Networks','Artificial Intelligence','Deep Learning','Cloud Computing','Cyber Security',
         'Digital Marketing','Business Analytics','Financial Accounting','Economics 101','Physics Fundamentals','Mathematics Advanced',
         'UPSC Preparation','GATE Study Material','CAT Prep Guide','GRE Vocabulary','IELTS Practice','TOEFL Guide','MBA Entrance','Engineering Mechanics',
         'Organic Chemistry','Biology NCERT'], 99, 2500],
    [5, ['Cricket Bat English Willow','Football Adidas','Badminton Racket','Tennis Racket','Yoga Mat Premium','Dumbbells Set 10kg','Resistance Bands','Skipping Rope',
         'Swimming Goggles','Running Shoes','Cycling Helmet','Gym Gloves','Boxing Gloves','Karate Belt','Table Tennis Set','Basketball Official',
         'Volleyball Mikasa','Hockey Stick','Golf Club Set','Skateboard','Treadmill Foldable','Exercise Bike','Pull Up Bar','Ab Roller','Protein Shaker',
         'Sports Bag','Knee Support','Ankle Weights','Wrist Band','Sports Water Bottle'], 149, 25000]
];

$review_texts = [
    'Great product! Highly recommend.','Good quality for the price.','Decent product, meets expectations.',
    'Average quality, could be better.','Not bad, but not great either.','Excellent value for money!',
    'Perfect! Exactly what I was looking for.','Fast delivery, good packaging.','Works well, no complaints.',
    'Slightly disappointed with the quality.','Amazing product, will buy again!','Best purchase I made this year.',
    'Fair price after negotiation.','The negotiation feature is awesome!','Got a great deal through negotiation.',
    'Product matches the description.','Could improve durability.','Very satisfied with my purchase.',
    'Love the build quality!','Arrived on time, works perfectly.'
];

$order_statuses = ['Order Confirmed','Processing','Shipped','Out for Delivery','Delivered','Cancelled'];
$payment_methods = ['COD','UPI','Card','Net Banking'];

function rand_phone() {
    return '9' . str_pad(rand(100000000, 999999999), 9, '0', STR_PAD_LEFT);
}

function rand_date($start, $end) {
    $ts = rand(strtotime($start), strtotime($end));
    return date('Y-m-d H:i:s', $ts);
}

$password_hash = password_hash('password123', PASSWORD_DEFAULT);

echo "Creating 60 vendors...\n";
$vendor_ids = [];

for ($i = 1; $i <= 60; $i++) {
    $fn = $first_names[array_rand($first_names)];
    $ln = $last_names[array_rand($last_names)];
    $btype = $business_types[array_rand($business_types)];
    $username = strtolower($fn . $ln . $i);
    $email = strtolower($fn . '.' . $ln . $i . '@vendor.com');
    $phone = rand_phone();
    $business = $fn . ' ' . $btype;
    $gst = strtoupper(substr(md5($username), 0, 15));
    $created = rand_date('2024-01-01', '2025-06-01');

    $q = "INSERT INTO users (username, email, phone, password_hash, role, business_name, gst_number, approval_status, active_status, created_at)
          VALUES ('$username', '$email', '$phone', '$password_hash', 'vendor', '$business', '$gst', 'Approved', 1, '$created')";
    
    if (mysqli_query($conn, $q)) {
        $vendor_ids[] = mysqli_insert_id($conn);
    } else {
        $i--;
    }
}
echo "  ✓ Created " . count($vendor_ids) . " vendors\n";

echo "Creating 1000 customers...\n";
$customer_ids = [];

for ($i = 1; $i <= 1000; $i++) {
    $fn = $first_names[array_rand($first_names)];
    $ln = $last_names[array_rand($last_names)];
    $username = strtolower($fn . $ln . rand(100, 9999));
    $email = strtolower($fn . $ln . rand(100, 9999) . '@gmail.com');
    $phone = rand_phone();
    $created = rand_date('2024-01-01', '2026-01-01');

    $q = "INSERT INTO users (username, email, phone, password_hash, role, approval_status, active_status, created_at)
          VALUES ('$username', '$email', '$phone', '$password_hash', 'customer', 'Approved', 1, '$created')";
    
    if (mysqli_query($conn, $q)) {
        $cid = mysqli_insert_id($conn);
        $customer_ids[] = $cid;

        $ci = rand(0, count($cities) - 1);
        $street_num = rand(1, 500);
        $addr = $street_num . ', ' . $streets[array_rand($streets)];
        $postal = rand(100000, 999999);
        mysqli_query($conn, "INSERT INTO customer_addresses (user_id, address_line1, city, state, postal_code, country) 
                             VALUES ('$cid', '$addr', '{$cities[$ci]}', '{$states[$ci]}', '$postal', 'India')");
    }
    
    if ($i % 200 == 0) echo "  ... $i customers\n";
}
echo "  ✓ Created " . count($customer_ids) . " customers with addresses\n";

echo "Creating products...\n";
$product_ids = [];
$product_vendor_map = [];
$product_price_map = [];

foreach ($product_data as $cat) {
    $cat_id = $cat[0];
    $names = $cat[1];
    $min_p = $cat[2];
    $max_p = $cat[3];

    foreach ($names as $name) {
        $num_vendors = rand(2, 4);
        $selected_vendors = array_rand(array_flip($vendor_ids), $num_vendors);
        if (!is_array($selected_vendors)) $selected_vendors = [$selected_vendors];

        foreach ($selected_vendors as $vid) {
            $price = round(rand($min_p * 100, $max_p * 100) / 100, 2);
            $min_neg = round($price * (rand(60, 85) / 100), 2);
            $stock = rand(1, 200);
            $created = rand_date('2024-06-01', '2025-12-01');
            
            $safe_name = mysqli_real_escape_string($conn, $name);
            $desc = mysqli_real_escape_string($conn, "High quality $name. Best price guaranteed. Negotiable!");
            
            $q = "INSERT INTO products (vendor_id, category_id, product_name, product_description, price, min_negotiation_price, stock_quantity, product_status, created_at)
                  VALUES ('$vid', '$cat_id', '$safe_name', '$desc', '$price', '$min_neg', '$stock', 'Active', '$created')";
            
            if (mysqli_query($conn, $q)) {
                $pid = mysqli_insert_id($conn);
                $product_ids[] = $pid;
                $product_vendor_map[$pid] = $vid;
                $product_price_map[$pid] = [$price, $min_neg];
            }
        }
    }
}
echo "  ✓ Created " . count($product_ids) . " products across 5 categories\n";

echo "Creating negotiation sessions...\n";
$session_count = 0;
$log_count = 0;
$accepted_sessions = [];

for ($i = 0; $i < 3500; $i++) {
    $cid = $customer_ids[array_rand($customer_ids)];
    $pid = $product_ids[array_rand($product_ids)];
    $vid = $product_vendor_map[$pid];
    $orig_price = $product_price_map[$pid][0];
    $min_price = $product_price_map[$pid][1];

    $statuses = ['Accepted','Accepted','Accepted','Rejected','Expired','Active'];
    $status = $statuses[array_rand($statuses)];

    $created = rand_date('2024-08-01', '2026-01-15');
    $expiry = date('Y-m-d H:i:s', strtotime($created) + 3600);

    $final_price = 'NULL';
    if ($status == 'Accepted') {
        $fp = round($orig_price * (rand(70, 95) / 100), 2);
        if ($fp < $min_price) $fp = $min_price;
        $final_price = "'$fp'";
    }

    $q = "INSERT INTO negotiations_sessions (customer_id, vendor_id, product_id, original_price, final_price, status, created_at, expiry_time)
          VALUES ('$cid', '$vid', '$pid', '$orig_price', $final_price, '$status', '$created', '$expiry')";
    
    if (mysqli_query($conn, $q)) {
        $sid = mysqli_insert_id($conn);
        $session_count++;

        if ($status == 'Accepted') {
            $accepted_sessions[$sid] = ['cid' => $cid, 'vid' => $vid, 'pid' => $pid, 'price' => floatval(str_replace("'", "", $final_price))];
        }

        $rounds = rand(1, 5);
        if ($status == 'Accepted') $rounds = rand(1, 4);
        if ($status == 'Rejected') $rounds = rand(1, 3);

        for ($r = 1; $r <= $rounds; $r++) {
            $offer_pct = rand(55, 98);
            $cust_offer = round($orig_price * $offer_pct / 100, 2);
            
            $log_time = date('Y-m-d H:i:s', strtotime($created) + ($r * 120));
            
            $cust_decision = 'Pending';
            mysqli_query($conn, "INSERT INTO negotiation_logs (session_id, sender_id, offer_source, offered_price, round_number, decision_status, created_at)
                                VALUES ('$sid', '$cid', 'Customer', '$cust_offer', '$r', '$cust_decision', '$log_time')");
            $log_count++;

            $ai_time = date('Y-m-d H:i:s', strtotime($log_time) + 5);
            
            if ($r == $rounds && $status == 'Accepted') {
                $ai_decision = 'Accepted';
                $ai_price = $cust_offer;
            } elseif ($r == $rounds && $status == 'Rejected') {
                $ai_decision = 'Rejected';
                $ai_price = $orig_price;
            } else {
                $ai_decision = 'Counter';
                $ai_price = round($orig_price * (rand(82, 95) / 100), 2);
                if ($ai_price < $min_price) $ai_price = $min_price;
            }

            mysqli_query($conn, "INSERT INTO negotiation_logs (session_id, sender_id, offer_source, offered_price, round_number, decision_status, created_at)
                                VALUES ('$sid', NULL, 'AI', '$ai_price', '$r', '$ai_decision', '$ai_time')");
            $log_count++;
        }
    }

    if ($i % 500 == 0 && $i > 0) echo "  ... $i sessions\n";
}
echo "  ✓ Created $session_count negotiation sessions with $log_count logs\n";

echo "Creating orders...\n";
$order_count = 0;
$item_count = 0;

for ($i = 0; $i < 10500; $i++) {
    $cid = $customer_ids[array_rand($customer_ids)];
    
    $num_items = rand(1, 4);
    $order_products = [];
    $total = 0;
    $vid = null;
    
    for ($j = 0; $j < $num_items; $j++) {
        $pid = $product_ids[array_rand($product_ids)];
        if ($vid === null) $vid = $product_vendor_map[$pid];
        
        $qty = rand(1, 3);
        $price = $product_price_map[$pid][0];
        
        if (rand(1, 100) <= 40) {
            $price = round($price * (rand(75, 95) / 100), 2);
        }
        
        $line_total = round($price * $qty, 2);
        $total += $line_total;
        $order_products[] = ['pid' => $pid, 'qty' => $qty, 'price' => $price, 'total' => $line_total];
    }

    $commission = round($total * 0.05, 2);
    $tracking = 'ORD' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
    $created = rand_date('2024-08-01', '2026-02-01');
    
    $os = $order_statuses[array_rand($order_statuses)];
    $pm = $payment_methods[array_rand($payment_methods)];
    $ps = ($os == 'Cancelled') ? 'Refunded' : (($os == 'Delivered') ? 'Paid' : (($pm == 'COD') ? 'Pending' : 'Paid'));

    $q = "INSERT INTO orders (customer_id, vendor_id, total_amount, admin_commission, order_status, payment_status, payment_method, tracking_id, created_at)
          VALUES ('$cid', '$vid', '$total', '$commission', '$os', '$ps', '$pm', '$tracking', '$created')";

    if (mysqli_query($conn, $q)) {
        $oid = mysqli_insert_id($conn);
        $order_count++;

        foreach ($order_products as $op) {
            mysqli_query($conn, "INSERT INTO order_items (order_id, product_id, quantity, price_per_unit, total_price) 
                                 VALUES ('$oid', '{$op['pid']}', '{$op['qty']}', '{$op['price']}', '{$op['total']}')");
            $item_count++;
        }

        if ($ps == 'Paid') {
            $txn = 'TXN' . strtoupper(substr(md5(rand()), 0, 12));
            mysqli_query($conn, "INSERT INTO payments (order_id, payment_method, transaction_id, amount, payment_status, paid_at)
                                 VALUES ('$oid', '$pm', '$txn', '$total', 'Completed', '$created')");
        }

        if (in_array($os, ['Shipped', 'Out for Delivery', 'Delivered'])) {
            $track_statuses = ['Order Confirmed', 'Packed', 'Shipped'];
            if ($os == 'Out for Delivery' || $os == 'Delivered') $track_statuses[] = 'Out for Delivery';
            if ($os == 'Delivered') $track_statuses[] = 'Delivered';
            
            $track_time = strtotime($created);
            foreach ($track_statuses as $ts) {
                $track_time += rand(3600, 86400);
                $tt = date('Y-m-d H:i:s', $track_time);
                $loc = $cities[array_rand($cities)] . ' Hub';
                $safe_ts = mysqli_real_escape_string($conn, $ts);
                mysqli_query($conn, "INSERT INTO order_tracking (order_id, tracking_status, location, updated_at)
                                     VALUES ('$oid', '$safe_ts', '$loc', '$tt')");
            }
        }
    }

    if ($i % 2000 == 0 && $i > 0) echo "  ... $i orders\n";
}
echo "  ✓ Created $order_count orders with $item_count items\n";

echo "Creating reviews...\n";
$review_count = 0;

for ($i = 0; $i < 2000; $i++) {
    $cid = $customer_ids[array_rand($customer_ids)];
    $pid = $product_ids[array_rand($product_ids)];
    $rating = rand(1, 5);
    if (rand(1, 100) <= 70) $rating = rand(3, 5);
    $text = mysqli_real_escape_string($conn, $review_texts[array_rand($review_texts)]);
    $created = rand_date('2024-10-01', '2026-01-30');

    $q = "INSERT INTO reviews (customer_id, product_id, rating, review_text, created_at)
          VALUES ('$cid', '$pid', '$rating', '$text', '$created')";
    if (mysqli_query($conn, $q)) $review_count++;
}
echo "  ✓ Created $review_count reviews\n";

echo "Creating wishlists...\n";
$wishlist_count = 0;

for ($i = 0; $i < 500; $i++) {
    $cid = $customer_ids[array_rand($customer_ids)];
    $pid = $product_ids[array_rand($product_ids)];
    
    $q = "INSERT INTO wishlists (customer_id, product_id) VALUES ('$cid', '$pid')";
    if (mysqli_query($conn, $q)) $wishlist_count++;
}
echo "  ✓ Created $wishlist_count wishlist items\n";

echo "\n====================================\n";
echo "  SEEDING COMPLETE!\n";
echo "====================================\n\n";

$stats = [
    'Vendors' => count($vendor_ids),
    'Customers' => count($customer_ids),
    'Products' => count($product_ids),
    'Negotiation Sessions' => $session_count,
    'Negotiation Logs' => $log_count,
    'Orders' => $order_count,
    'Order Items' => $item_count,
    'Reviews' => $review_count,
    'Wishlists' => $wishlist_count
];

foreach ($stats as $label => $count) {
    echo "  $label: " . number_format($count) . "\n";
}

echo "\n  All passwords are: password123\n";
echo "  Vendor emails: [firstname].[lastname][N]@vendor.com\n";
echo "  Customer emails: [firstname][lastname][N]@gmail.com\n";

echo "\n====================================\n";
echo "  Now go to Admin > ML Training to\n";
echo "  export data and train the model!\n";
echo "====================================\n";
echo "</pre>";
?>
