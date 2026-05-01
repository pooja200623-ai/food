<?php
header('Content-Type: application/json');

require_once 'config.php';

    $action = isset($_GET['action']) ? $_GET['action'] : 'all';
    
    if ($action === 'category') {
        $cat = isset($_GET['category']) ? $_GET['category'] : '';
        $stmt = $conn->prepare("SELECT * FROM foods WHERE category = ? ORDER BY rating DESC");
        $stmt->execute([$cat]);
        $foods = $stmt->fetchAll();
    } else {
        // action = all
        $stmt = $conn->query("SELECT * FROM foods ORDER BY rating DESC");
        $foods = $stmt->fetchAll();
    }
    
    echo json_encode(['success' => true, 'data' => $foods]);

} catch(PDOException $e) {
    // Fallback static data if MySQL is not running
    $fallbackFoods = [
        ['id'=>1, 'name'=>'Chicken Biryani', 'category'=>'Indian', 'price'=>320, 'rating'=>4.9, 'image_url'=>'https://images.unsplash.com/photo-1563379091339-03b21ab4a4f8?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'30-40 min', 'description'=>"Aromatic basmati rice cooked with tender chicken and authentic spices."],
        ['id'=>2, 'name'=>'Paneer Butter Masala', 'category'=>'Indian', 'price'=>280, 'rating'=>4.7, 'image_url'=>'https://images.unsplash.com/photo-1631452180519-c014fe946bc0?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'25-35 min', 'description'=>"Cottage cheese cubes cooked in a rich, creamy tomato gravy."],
        ['id'=>3, 'name'=>'Garlic Naan', 'category'=>'Indian', 'price'=>60, 'rating'=>4.8, 'image_url'=>'https://images.unsplash.com/photo-1601050690597-df0568f70950?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'15-20 min', 'description'=>"Soft and fluffy flatbread baked in a tandoor with garlic butter."],
        ['id'=>4, 'name'=>'Mutton Rogan Josh', 'category'=>'Indian', 'price'=>450, 'rating'=>4.9, 'image_url'=>'https://images.unsplash.com/photo-1585937421612-70a008356fbe?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'35-45 min', 'description'=>"Classic Kashmiri slow-cooked lamb curry with aromatic spices."],
        ['id'=>5, 'name'=>'Palak Paneer', 'category'=>'Indian', 'price'=>250, 'rating'=>4.6, 'image_url'=>'https://images.unsplash.com/photo-1601050690597-df0568f70950?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'20-30 min', 'description'=>"Fresh spinach pureed and cooked with cottage cheese chunks."],
        ['id'=>6, 'name'=>'Chole Bhature', 'category'=>'Indian', 'price'=>180, 'rating'=>4.8, 'image_url'=>'https://images.unsplash.com/photo-1589301760014-d929f39ce9b1?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'20-25 min', 'description'=>"Spicy chickpea curry served with deep-fried fluffy bread."],
        ['id'=>7, 'name'=>'Tandoori Chicken', 'category'=>'Indian', 'price'=>350, 'rating'=>4.9, 'image_url'=>'https://images.unsplash.com/photo-1599487405609-0d481f215d2a?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'30-40 min', 'description'=>"Whole chicken marinated in yogurt and spices, roasted in a clay oven."],
        ['id'=>8, 'name'=>'Dal Makhani', 'category'=>'Indian', 'price'=>220, 'rating'=>4.7, 'image_url'=>'https://images.unsplash.com/photo-1546833999-b9f581a1996d?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'25-35 min', 'description'=>"Black lentils and kidney beans slow-cooked with butter and cream."],
        ['id'=>9, 'name'=>'Steamed Momos', 'category'=>'Chinese', 'price'=>149, 'rating'=>4.7, 'image_url'=>'https://images.unsplash.com/photo-1625220194771-7ebdea0b70b9?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'20-25 min', 'description'=>"Delicate dumplings filled with minced chicken and herbs."],
        ['id'=>10, 'name'=>'Hakka Noodles', 'category'=>'Chinese', 'price'=>199, 'rating'=>4.5, 'image_url'=>'https://images.unsplash.com/photo-1585032226651-759b368d7246?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'20-25 min', 'description'=>"Wok-tossed noodles with shredded vegetables and soy sauce."],
        ['id'=>11, 'name'=>'Chicken Fried Rice', 'category'=>'Chinese', 'price'=>220, 'rating'=>4.6, 'image_url'=>'https://images.unsplash.com/photo-1603133872878-684f208fb84b?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'20-25 min', 'description'=>"Classic fried rice mixed with egg, tender chicken, and veggies."],
        ['id'=>12, 'name'=>'Kung Pao Chicken', 'category'=>'Chinese', 'price'=>350, 'rating'=>4.8, 'image_url'=>'https://images.unsplash.com/photo-1525755662778-989d0524087e?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'25-30 min', 'description'=>"Spicy stir-fry chicken with peanuts, vegetables, and chili peppers."],
        ['id'=>13, 'name'=>'Sweet and Sour Pork', 'category'=>'Chinese', 'price'=>380, 'rating'=>4.7, 'image_url'=>'https://images.unsplash.com/photo-1525755662778-989d0524087e?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'30-35 min', 'description'=>"Crispy pork chunks tossed in a tangy sweet and sour sauce."],
        ['id'=>14, 'name'=>'Spring Rolls', 'category'=>'Chinese', 'price'=>120, 'rating'=>4.5, 'image_url'=>'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'15-20 min', 'description'=>"Crispy deep-fried rolls stuffed with mixed vegetables."],
        ['id'=>15, 'name'=>'Manchow Soup', 'category'=>'Chinese', 'price'=>150, 'rating'=>4.6, 'image_url'=>'https://images.unsplash.com/photo-1547592180-85f173990554?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'15-20 min', 'description'=>"Spicy and tangy dark brown soup topped with crispy fried noodles."],
        ['id'=>16, 'name'=>'Chilli Paneer', 'category'=>'Chinese', 'price'=>260, 'rating'=>4.8, 'image_url'=>'https://images.unsplash.com/photo-1564834724105-918b73d1b9e0?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'20-25 min', 'description'=>"Batter-fried paneer cubes tossed in a spicy chili garlic sauce."],
        ['id'=>17, 'name'=>'Bibimbap Bowl', 'category'=>'Korean', 'price'=>420, 'rating'=>4.8, 'image_url'=>'https://images.unsplash.com/photo-1553163147-622ab57be1c7?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'25-30 min', 'description'=>"Warm rice topped with sauteed vegetables, chili paste, and egg."],
        ['id'=>18, 'name'=>'Korean Fried Chicken', 'category'=>'Korean', 'price'=>399, 'rating'=>4.9, 'image_url'=>'https://images.unsplash.com/photo-1588675459345-d41cfa2fcb6c?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'30-35 min', 'description'=>"Double-fried chicken coated in a sweet and spicy sticky sauce."],
        ['id'=>19, 'name'=>'Tteokbokki', 'category'=>'Korean', 'price'=>350, 'rating'=>4.7, 'image_url'=>'https://images.unsplash.com/photo-1580651315530-69c8e0026377?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'20-25 min', 'description'=>"Chewy rice cakes simmered in a spicy gochujang chili paste."],
        ['id'=>20, 'name'=>'Kimchi Fried Rice', 'category'=>'Korean', 'price'=>280, 'rating'=>4.6, 'image_url'=>'https://images.unsplash.com/photo-1553163147-622ab57be1c7?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'20-25 min', 'description'=>"Fried rice made with fermented kimchi, topped with a fried egg."],
        ['id'=>21, 'name'=>'Bulgogi Beef', 'category'=>'Korean', 'price'=>550, 'rating'=>4.9, 'image_url'=>'https://images.unsplash.com/photo-1550547660-d9450f859349?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'30-40 min', 'description'=>"Thinly sliced marinated beef grilled to tender perfection."],
        ['id'=>22, 'name'=>'Jajangmyeon', 'category'=>'Korean', 'price'=>380, 'rating'=>4.8, 'image_url'=>'https://images.unsplash.com/photo-1552611052-33e04de081de?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'25-35 min', 'description'=>"Thick noodles covered in a savory, slightly sweet black bean sauce."],
        ['id'=>23, 'name'=>'Gimbap', 'category'=>'Korean', 'price'=>250, 'rating'=>4.7, 'image_url'=>'https://images.unsplash.com/photo-1579871494447-9811cf80d66c?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'15-20 min', 'description'=>"Seaweed rice rolls filled with vegetables and pickled radish."],
        ['id'=>24, 'name'=>'Sundubu Jjigae', 'category'=>'Korean', 'price'=>400, 'rating'=>4.8, 'image_url'=>'https://images.unsplash.com/photo-1547592180-85f173990554?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'25-30 min', 'description'=>"Spicy soft tofu stew with seafood, vegetables, and a cracked egg."],
        ['id'=>25, 'name'=>'Spicy Sushi Roll', 'category'=>'Japanese', 'price'=>399, 'rating'=>4.8, 'image_url'=>'https://images.unsplash.com/photo-1579871494447-9811cf80d66c?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'40-45 min', 'description'=>"Fresh tuna mixed with spicy mayo, wrapped in seaweed."],
        ['id'=>26, 'name'=>'Tonkotsu Ramen', 'category'=>'Japanese', 'price'=>450, 'rating'=>4.9, 'image_url'=>'https://images.unsplash.com/photo-1557872943-16a5ac26437e?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'30-40 min', 'description'=>"Rich pork broth noodles topped with sliced pork belly and soft egg."],
        ['id'=>27, 'name'=>'Chicken Teriyaki', 'category'=>'Japanese', 'price'=>420, 'rating'=>4.7, 'image_url'=>'https://images.unsplash.com/photo-1580651315530-69c8e0026377?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'25-30 min', 'description'=>"Grilled chicken glazed with sweet soy teriyaki sauce over rice."],
        ['id'=>28, 'name'=>'Shrimp Tempura', 'category'=>'Japanese', 'price'=>380, 'rating'=>4.8, 'image_url'=>'https://images.unsplash.com/photo-1615361200141-f45040f367be?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'20-25 min', 'description'=>"Lightly battered and deep-fried crispy shrimp."],
        ['id'=>29, 'name'=>'Miso Soup', 'category'=>'Japanese', 'price'=>150, 'rating'=>4.5, 'image_url'=>'https://images.unsplash.com/photo-1547592180-85f173990554?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'10-15 min', 'description'=>"Traditional Japanese soup made with dashi stock and miso paste."],
        ['id'=>30, 'name'=>'Salmon Sashimi', 'category'=>'Japanese', 'price'=>550, 'rating'=>4.9, 'image_url'=>'https://images.unsplash.com/photo-1534482421-64566f976cfa?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'15-20 min', 'description'=>"Premium thick-cut slices of fresh, raw Atlantic salmon."],
        ['id'=>31, 'name'=>'Pork Katsu Curry', 'category'=>'Japanese', 'price'=>480, 'rating'=>4.8, 'image_url'=>'https://images.unsplash.com/photo-1601050690597-df0568f70950?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'30-35 min', 'description'=>"Breaded, deep-fried pork cutlet served with rich Japanese curry."],
        ['id'=>32, 'name'=>'Takoyaki', 'category'=>'Japanese', 'price'=>280, 'rating'=>4.6, 'image_url'=>'https://images.unsplash.com/photo-1579871494447-9811cf80d66c?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'20-25 min', 'description'=>"Ball-shaped Japanese snack made of wheat flour and diced octopus."],
        ['id'=>33, 'name'=>'Classic Cheeseburger', 'category'=>'American', 'price'=>199, 'rating'=>4.6, 'image_url'=>'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'20-25 min', 'description'=>"Juicy beef patty with melted cheddar, lettuce, and tomato."],
        ['id'=>34, 'name'=>'BBQ Pork Ribs', 'category'=>'American', 'price'=>549, 'rating'=>4.8, 'image_url'=>'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'35-40 min', 'description'=>"Slow-cooked ribs glazed with smoky barbecue sauce."],
        ['id'=>35, 'name'=>'Mac and Cheese', 'category'=>'American', 'price'=>250, 'rating'=>4.7, 'image_url'=>'https://images.unsplash.com/photo-1543339308-43e59d6b73a6?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'20-25 min', 'description'=>"Creamy, cheesy macaroni baked to a golden brown perfection."],
        ['id'=>36, 'name'=>'Buffalo Wings', 'category'=>'American', 'price'=>320, 'rating'=>4.8, 'image_url'=>'https://images.unsplash.com/photo-1569058242253-92a9c755a0ec?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'25-30 min', 'description'=>"Spicy, deep-fried chicken wings served with blue cheese dip."],
        ['id'=>37, 'name'=>'Philly Cheesesteak', 'category'=>'American', 'price'=>450, 'rating'=>4.9, 'image_url'=>'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'25-35 min', 'description'=>"Sliced beefsteak and melted cheese in a long hoagie roll."],
        ['id'=>38, 'name'=>'Clam Chowder', 'category'=>'American', 'price'=>280, 'rating'=>4.6, 'image_url'=>'https://images.unsplash.com/photo-1547592180-85f173990554?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'20-25 min', 'description'=>"Rich, creamy soup containing clams, diced potatoes, and onions."],
        ['id'=>39, 'name'=>'Hot Dog with Fries', 'category'=>'American', 'price'=>180, 'rating'=>4.5, 'image_url'=>'https://images.unsplash.com/photo-1541214113241-212e8d2ce6b4?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'15-20 min', 'description'=>"Classic grilled hot dog served with a generous side of crispy fries."],
        ['id'=>40, 'name'=>'Texas Brisket', 'category'=>'American', 'price'=>650, 'rating'=>4.9, 'image_url'=>'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'40-50 min', 'description'=>"Beef brisket slow-smoked for 12 hours until melt-in-your-mouth tender."],
    ];
    
    $action = isset($_GET['action']) ? $_GET['action'] : 'all';
    if ($action === 'category') {
        $cat = isset($_GET['category']) ? $_GET['category'] : '';
        $filtered = array_values(array_filter($fallbackFoods, function($f) use ($cat) { return $f['category'] === $cat; }));
        echo json_encode(['success' => true, 'data' => $filtered, 'fallback' => true]);
    } else {
        echo json_encode(['success' => true, 'data' => $fallbackFoods, 'fallback' => true]);
    }
}
?>
