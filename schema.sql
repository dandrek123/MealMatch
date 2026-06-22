-- MealMatch schema
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100),
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('user','admin') DEFAULT 'user',
    active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS meals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    ingredients TEXT,
    diet_type VARCHAR(50),
    image_url VARCHAR(255),
    tags VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO meals (name, ingredients, diet_type, image_url, tags) VALUES
('Grilled Chicken Salad', 'chicken, lettuce, tomato, olive oil', 'high-protein', 'chicken_salad.jpg', 'high-protein,gluten-free'),
('Vegan Buddha Bowl', 'quinoa, chickpeas, avocado, spinach', 'vegan', 'buddha_bowl.jpg', 'vegan,plant-based'),
('Pasta Primavera', 'pasta, bell peppers, mushrooms, cream sauce', 'vegetarian', 'pasta_primavera.jpg', 'vegetarian,quick');
