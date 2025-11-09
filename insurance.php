<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "insurance";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process contact form submission
if (isset($_POST['contact_submit'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $subject = $_POST['subject'];
    $message = $_POST['message'];
    
    $sql = "INSERT INTO contact_requests (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $name, $email, $phone, $subject, $message);
    
    if ($stmt->execute()) {
        $contact_success = "Your message has been sent. We'll get back to you shortly.";
    } else {
        $contact_error = "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Process newsletter subscription
if (isset($_POST['subscribe'])) {
    $email = $_POST['subscribe_email'];
    $name = isset($_POST['subscribe_name']) ? $_POST['subscribe_name'] : '';
    
    $sql = "INSERT INTO subscribers (email, name) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $name);
    
    if ($stmt->execute()) {
        $subscribe_success = "Thank you for subscribing to our newsletter!";
    } else {
        $subscribe_error = "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Process appointment form submission
if (isset($_POST['appointment_submit'])) {
    $fullName = $_POST['fullName'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $agreeTerms = isset($_POST['agreeTerms']) ? 1 : 0;
    
    $sql = "INSERT INTO appointments (full_name, phone, email, agree_terms, status) VALUES (?, ?, ?, ?, 'New')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $fullName, $phone, $email, $agreeTerms);
    
    if ($stmt->execute()) {
        $appointment_success = "Thank you! Your appointment request has been submitted. We will contact you shortly.";
    } else {
        $appointment_error = "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Get testimonials
$sql = "SELECT * FROM testimonials WHERE is_active = TRUE ORDER BY created_at DESC LIMIT 3";
$result = $conn->query($sql);
$testimonials = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $testimonials[] = $row;
    }
}

// Get insurance products
$sql = "SELECT * FROM insurance_products ORDER BY category, subcategory";
$result = $conn->query($sql);
$insurance_products = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $insurance_products[$row['category']][$row['subcategory']][] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mint Insure - Secure Your Future</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">   
    
    <!-- Slick Slider -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick-theme.min.css">
    <style>
        /* Reset & Base Styles */
        :root {
            --primary: #004d80;
            --primary-light: #0077c2;
            --secondary: #ffb703;
            --accent: #38b6ff;
            --text: #333333;
            --text-light: #666666;
            --light-bg: #f8f9fa;
            --white: #ffffff;
            --dark: #212529;
            --success: #28a745;
            --danger: #dc3545;
            --warning: #ffc107;
            --info: #17a2b8;
            --shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            --border-radius: 8px;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            color: var(--text);
            line-height: 1.6;
            background-color: var(--white);
            overflow-x: hidden;
        }
        
        h1, h2, h3, h4, h5 {
            font-family: 'Playfair Display', serif;
            margin-bottom: 1rem;
            line-height: 1.2;
            color: var(--dark);
        }
        
        h1 {
            font-size: 3.5rem;
            font-weight: 700;
        }
        
        h2 {
            font-size: 2.5rem;
            font-weight: 600;
        }
        
        h3 {
            font-size: 1.75rem;
            font-weight: 600;
        }
        
        p {
            margin-bottom: 1rem;
            color: var(--text-light);
        }
        
        a {
            text-decoration: none;
            color: var(--primary);
            transition: all 0.3s ease;
        }
        
        a:hover {
            color: var(--primary-light);
        }
        
        img {
            max-width: 100%;
            height: auto;
        }
        
        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            font-weight: 500;
            text-align: center;
            border-radius: var(--border-radius);
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: var(--white);
        }
        
        .btn-primary:hover {
            background-color: var(--primary-light);
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }
        
        .btn-secondary {
            background-color: var(--secondary);
            color: var(--dark);
        }
        
        .btn-secondary:hover {
            opacity: 0.9;
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }
        
        .btn-outline {
            background-color: transparent;
            color: var(--primary);
            border: 2px solid var(--primary);
        }
        
        .btn-outline:hover {
            background-color: var(--primary);
            color: var(--white);
            transform: translateY(-2px);
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            font-size: 1rem;
            border-radius: var(--border-radius);
            border: 1px solid #ced4da;
            transition: border-color 0.3s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(0, 77, 128, 0.1);
        }
        
        /* Header Styles */
        .header {
            background-color: var(--white);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }
        
        .header.scrolled {
            padding: 10px 0;
            background-color: rgba(255, 255, 255, 0.95);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 0;
        }
        
        .logo {
            display: flex;
            align-items: center;
        }
        
        .logo img {
            height: 40px;
            margin-right: 10px;
        }
        
        .logo-text {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary);
        }
        
        .logo-text span {
            color: var(--secondary);
        }
        
        .nav-links {
            display: flex;
            align-items: center;
            list-style: none;
        }
        
        .nav-links li {
            position: relative;
            margin-left: 2rem;
        }
        
        .nav-links a {
            color: var(--dark);
            font-weight: 500;
            font-size: 1rem;
            padding: 8px 0;
            position: relative;
        }
        
        .nav-links a:after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 0;
            background-color: var(--primary);
            transition: width 0.3s ease;
        }
        
        .nav-links a:hover:after {
            width: 100%;
        }
        
        .dropdown-menu {
            position: absolute;
            top: 100%;
            left: -20px;
            background-color: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            min-width: 220px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(10px);
            transition: all 0.3s ease;
            z-index: 100;
            padding: 15px 0;
        }
        
        .dropdown-menu li {
            margin: 0;
        }
        
        .dropdown-menu a {
            padding: 10px 20px;
            display: block;
            color: var(--text);
            font-weight: 400;
        }
        
        .dropdown-menu a:hover {
            background-color: var(--light-bg);
        }
        
        .nav-links li:hover .dropdown-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .has-dropdown:after {
            content: '▼';
            font-size: 0.7rem;
            margin-left: 5px;
            transition: transform 0.3s ease;
        }
        
        .nav-links li:hover .has-dropdown:after {
            transform: rotate(180deg);
        }
        
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--dark);
        }
        
        /* Hero Section */
        .hero {
            position: relative;
            height: 80vh;
            min-height: 600px;
            background: linear-gradient(to right, rgba(0, 77, 128, 0.8), rgba(0, 77, 128, 0.6)), url('https://i.ibb.co/fYqTW168/f56947c56bf3c608b6aaa2b4875bd71b-1200-80.webp" alt="f56947c56bf3c608b6aaa2b4875bd71b-1200-80') no-repeat center center/cover;
            display: flex;
            align-items: center;
            margin-top: 80px;
        }
        
        .hero-content {
            color: var(--white);
            max-width: 600px;
        }
        
        .hero h1 {
            color: var(--white);
            margin-bottom: 1.5rem;
            font-size: 3.5rem;
        }
        
        .hero p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 1.1rem;
            margin-bottom: 2rem;
        }
        
        .hero-btns {
            display: flex;
            gap: 15px;
        }
        
        /* Services Section */
        .services {
            padding: 80px 0;
            background-color: var(--light-bg);
        }
        
        .section-header {
            text-align: center;
            margin-bottom: 50px;
        }
        
        .section-header h2 {
            position: relative;
            display: inline-block;
            padding-bottom: 15px;
        }
        
        .section-header h2:after {
            content: '';
            position: absolute;
            width: 60px;
            height: 3px;
            background-color: var(--secondary);
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
        }
        
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }
        
        .service-card {
            background-color: var(--white);
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
        }
        
        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }
        
        .service-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .service-content {
            padding: 25px;
        }
        
        .service-content h3 {
            margin-bottom: 15px;
            color: var(--primary);
        }
        
        /* About Section */
        .about {
            padding: 80px 0;
        }
        
        .about-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
            align-items: center;
        }
        
        .about-image {
            position: relative;
        }
        
        .about-image:before {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            border: 5px solid var(--secondary);
            top: -20px;
            left: -20px;
            z-index: -1;
            border-radius: var(--border-radius);
        }
        
        .about-image img {
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
        }
        
        .about-content h2 {
            margin-bottom: 20px;
        }
        
        .about-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-top: 30px;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: var(--text-light);
            font-size: 0.9rem;
        }
        
        /* Features Section */
        .features {
            padding: 80px 0;
            background-color: var(--light-bg);
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
        }
        
        .feature-item {
            background-color: var(--white);
            padding: 30px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .feature-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }
        
        .feature-icon {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 20px;
        }
        
        /* Testimonials Section */
        .testimonials {
            padding: 80px 0;
            text-align: center;
        }
        
        .testimonial-slider {
            margin-top: 50px;
            position: relative;
        }
        
        .testimonial-item {
            padding: 30px;
            background-color: var(--white);
            box-shadow: var(--shadow);
            border-radius: var(--border-radius);
            margin: 20px;
            text-align: left;
        }
        
        .testimonial-content {
            font-style: italic;
            margin-bottom: 20px;
            position: relative;
            padding-left: 30px;
        }
        
        .testimonial-content:before {
            content: '"';
            font-size: 4rem;
            font-family: 'Georgia', serif;
            color: var(--primary);
            opacity: 0.2;
            position: absolute;
            top: -20px;
            left: -10px;
        }
        
        .testimonial-author {
            display: flex;
            align-items: center;
        }
        
        .author-image {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            overflow: hidden;
            margin-right: 15px;
        }
        
        .author-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .author-info h4 {
            margin-bottom: 5px;
        }
        
        .author-info p {
            font-size: 0.9rem;
            color: var(--text-light);
            margin: 0;
        }
        
        .testimonial-rating {
            color: var(--secondary);
            font-size: 1.2rem;
            margin-top: 10px;
        }
        
        /* CTA Section */
        .cta {
            padding: 80px 0;
            background: linear-gradient(to right, var(--primary), var(--primary-light));
            color: var(--white);
            text-align: center;
        }
        
        .cta h2 {
            color: var(--white);
            margin-bottom: 20px;
        }
        
        .cta p {
            color: rgba(255, 255, 255, 0.9);
            max-width: 700px;
            margin: 0 auto 30px;
        }
        
        .cta-form {
            max-width: 500px;
            margin: 0 auto;
            display: flex;
        }
        
        .cta-form input {
            flex: 1;
            padding: 15px;
            border: none;
            border-radius: var(--border-radius) 0 0 var(--border-radius);
            font-size: 1rem;
        }
        
        .cta-form button {
            background-color: var(--secondary);
            color: var(--dark);
            border: none;
            padding: 0 25px;
            border-radius: 0 var(--border-radius) var(--border-radius) 0;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .cta-form button:hover {
            background-color: #e6a503;
        }
        
        /* Footer */
        .footer {
            background-color: var(--dark);
            color: rgba(255, 255, 255, 0.7);
            padding: 70px 0 0;
        }
        
        .footer-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 40px;
            margin-bottom: 50px;
        }
        
        .footer-col h3 {
            color: var(--white);
            font-size: 1.5rem;
            margin-bottom: 20px;
            position: relative;
            padding-bottom: 10px;
        }
        
        .footer-col h3:after {
            content: '';
            position: absolute;
            width: 40px;
            height: 2px;
            background-color: var(--secondary);
            bottom: 0;
            left: 0;
        }
        
        .footer-links {
            list-style: none;
        }
        
        .footer-links li {
            margin-bottom: 10px;
        }
        
        .footer-links a {
            color: rgba(255, 255, 255, 0.7);
            transition: all 0.3s ease;
        }
        
        .footer-links a:hover {
            color: var(--white);
            padding-left: 5px;
        }
        
        .footer-social {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }
        
        .footer-social a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            color: var(--white);
            transition: all 0.3s ease;
        }
        
        .footer-social a:hover {
            background-color: var(--primary);
            transform: translateY(-3px);
        }
        
        .footer-bottom {
            text-align: center;
            padding: 20px 0;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        /* Contact Section */
        .contact {
            padding: 80px 0;
            background-color: var(--light-bg);
        }
        
        .contact-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
        }
        
        .contact-info h3 {
            margin-bottom: 25px;
        }
        
        .contact-detail {
            display: flex;
            align-items: flex-start;
            margin-bottom: 25px;
        }
        
        .contact-icon {
            font-size: 1.5rem;
            color: var(--primary);
            margin-right: 15px;
            width: 30px;
            text-align: center;
        }
        
        .contact-text h4 {
            margin-bottom: 5px;
            font-size: 1.1rem;
        }
        
        .contact-text p {
            margin: 0;
        }
        
        .contact-form {
            background-color: var(--white);
            padding: 30px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
        }
        
        .contact-form h3 {
            margin-bottom: 25px;
            text-align: center;
        }
        
        /* WhatsApp Float */
        .whatsapp-float {
            position: fixed;
            width: 60px;
            height: 60px;
            bottom: 40px;
            right: 40px;
            background-color: #25d366;
            color: var(--white);
            border-radius: 50px;
            text-align: center;
            font-size: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            z-index: 100;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        
        .whatsapp-float:hover {
            background-color: #20ba5a;
            transform: scale(1.1);
        }
        
        /* Alert Messages */
        .alert {
            padding: 15px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        
        .alert-success {
            background-color: rgba(40, 167, 69, 0.1);
            border: 1px solid rgba(40, 167, 69, 0.3);
            color: var(--success);
        }
        
        .alert-danger {
            background-color: rgba(220, 53, 69, 0.1);
            border: 1px solid rgba(220, 53, 69, 0.3);
            color: var(--danger);
        }
        
        .alert i {
            margin-right: 10px;
            font-size: 1.2rem;
        }
        
        /* Responsive Styles *
        @media (max-width: 992px) {
    .nav-links {
        position: fixed;
        top: 80px;
        left: -100%;
        width: 100%;
        height: calc(100vh - 80px);
        background-color: var(--white);
        flex-direction: column;
        align-items: center;
        justify-content: flex-start;
        padding-top: 30px;
        transition: all 0.3s ease;
        z-index: 999;
    }
    
    .nav-links.active {
        left: 0;
    }
    
    .nav-links li {
        margin: 15px 0;
    }
    
    .mobile-menu-btn {
        display: block;
    }
    
    .dropdown-menu {
        position: static;
        opacity: 1;
        visibility: visible;
        transform: none;
        box-shadow: none;
        display: none;
        padding: 10px 0 0 20px;
    }
    
    .nav-links li:hover .dropdown-menu {
        display: block;
    }
    
    .about-grid,
    .contact-grid {
        grid-template-columns: 1fr;
    }
    
    .about-image:before {
        display: none;
    }
    
    .features-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    h1 {
        font-size: 2.5rem;
    }
    
    h2 {
        font-size: 2rem;
    }
    
    .hero {
        height: auto;
        padding: 100px 0;
    }
    
    .hero-content {
        text-align: center;
    }
    
    .hero-btns {
        justify-content: center;
    }
    
    .features-grid {
        grid-template-columns: 1fr;
    }
    
    .about-stats {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .cta-form {
        flex-direction: column;
    }
    
    .cta-form input {
        border-radius: var(--border-radius);
        margin-bottom: 10px;
    }
    
    .cta-form button {
        border-radius: var(--border-radius);
        padding: 15px;
    }
}

@media (max-width: 576px) {
    .logo-text {
        font-size: 1.5rem;
    }
    
    .hero h1 {
        font-size: 2rem;
    }
    
    .hero-btns {
        flex-direction: column;
        gap: 10px;
    }
    
    .btn {
        width: 100%;
    }
    
    .about-stats {
        grid-template-columns: 1fr;
    }
    
    .whatsapp-float {
        width: 50px;
        height: 50px;
        bottom: 20px;
        right: 20px;
        font-size: 24px;
    }
}
/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.7);
}

.modal-content {
    background-color: #fff;
    margin: 5% auto;
    padding: 30px;
    border-radius: 8px;
    width: 90%;
    max-width: 500px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    position: relative;
}

.close-modal {
    position: absolute;
    top: 15px;
    right: 20px;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.modal h2 {
    color: #ff4500;
    margin-top: 0;
    margin-bottom: 30px;
    text-align: center;
    font-family: 'Poppins', sans-serif;
}

.modal .form-group {
    margin-bottom: 20px;
}

.modal label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
}

.modal .form-control {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 16px;
}

.modal .checkbox-group {
    display: flex;
    align-items: flex-start;
}

.modal .checkbox-group input {
    margin-top: 4px;
    margin-right: 10px;
}

.modal .btn-block {
    width: 100%;
    padding: 15px;
    font-size: 18px;
    font-weight: 600;
    text-transform: uppercase;
    margin-top: 20px;
    background-color: #ff8c00;
    border: none;
    color: white;
}

.modal .text-center {
    text-align: center;
}

.modal .form-footer {
    margin-top: 15px;
    font-weight: 500;
    font-size: 16px;
}

.modal .small {
    font-size: 14px;
    color: #666;
}

.terms-link {
    color: #ff4500;
    text-decoration: underline;
}
</style>
</head>

<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <nav class="navbar">
                <div class="logo">
                    <a href="https://imgbb.com/"><img src="https://i.ibb.co/XZR92p8f/921d08cdc74a9864a949bdf17517e586-1200-80.webp" alt="921d08cdc74a9864a949bdf17517e586-1200-80" border="0"></a>
                    <span class="logo-text">Mint<span>Insure</span></span>
                </div>
                
                <button class="mobile-menu-btn">
                    <i class="fas fa-bars"></i>
                </button>
                
                <ul class="nav-links">
                    <li><a href="#home">Home</a></li>
                    <li><a href="#about">About</a></li>
                    <li class="has-dropdown">
                        <a href="#insurance">Insurance</a>
                        <ul class="dropdown-menu">
                            <li><a href="#life-term">Life/Term</a></li>
                            <li><a href="#health">Health</a></li>
                            <li><a href="#general">General</a></li>
                        </ul>
                    </li>
                    <li class="has-dropdown">
                        <a href="#funds">Funds</a>
                        <ul class="dropdown-menu">
                            <li><a href="#child-education-fund">Child Education Fund</a></li>
                        </ul>
                    </li>
                    <li class="has-dropdown">
                        <a href="#investments">Investments</a>
                        <ul class="dropdown-menu">
                            <li><a href="#general-investment">General Investment</a></li>
                            <li><a href="#investment-plans">Investment Plans</a></li>
                        </ul>
                    </li>
                    <li class="has-dropdown">
                        <a href="#plans">Plans</a>
                        <ul class="dropdown-menu">
                            <li><a href="#ulip-plans">ULIP Plans</a></li>
                            <li><a href="#annuity-plans">Annuity Plans</a></li>
                            <li><a href="#retirement-plans">Retirement Plans</a></li>
                        </ul>
                    </li>
                    <li><a href="#contact">Contact</a></li>
                    <li><a href="#" class="btn btn-secondary appointment-trigger">Get a Quote</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="container">
            <div class="hero-content" data-aos="fade-up">
                <h1>Secure Your Future With Mint Insure</h1>
                <p>We provide comprehensive insurance solutions tailored to your unique needs, ensuring peace of mind for you and your loved ones.</p>
                <div class="hero-btns">
                    <a href="#services" class="btn btn-primary">Our Services</a>
                    <a href="#" class="btn btn-outline appointment-trigger">Contact Us</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="services" id="services">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <h2>Our Insurance Services</h2>
                <p>We offer a wide range of insurance products to protect what matters most to you.</p>
            </div>
            
            <div class="services-grid">
                <?php foreach ($insurance_products as $category => $subcategories): ?>
                    <?php foreach ($subcategories as $subcategory => $products): ?>
                        <?php foreach ($products as $product): ?>
                            <div class="service-card" data-aos="fade-up" data-aos-delay="100">
                                <img src="<?php echo $product['image_url']; ?>" alt="<?php echo $product['name']; ?>">
                                <div class="service-content">
                                    <h3><?php echo $product['name']; ?></h3>
                                    <p><?php echo $product['description']; ?></p>
                                    <a href="#" class="btn btn-outline appointment-trigger">Get Quote</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="about" id="about">
        <div class="container">
            <div class="about-grid">
                <div class="about-image" data-aos="fade-right">
                 <a href="https://ibb.co/yBQxMXx4"><img src="https://i.ibb.co/jZrjxTjJ/9f4874c40a5d5015c73d5530d59d5519-1200-80.webp" alt="9f4874c40a5d5015c73d5530d59d5519-1200-80" border="0"></a>
                </div>
                <div class="about-content" data-aos="fade-left">
                    <h2>Meet Your Expert</h2>
                    <h3>Rajesh Shah</h3>
                    <p class="expert-title">Insurance & Investment Specialist</p>
                    <p>With over 5 years of experience in the insurance and financial industry, Rajesh Shah has helped hundreds of clients secure their financial future through tailored insurance and investment solutions.</p>
                    <p>Rajesh specializes in creating personalized insurance portfolios that address each client's unique needs, ensuring comprehensive protection for families and businesses alike.</p>
                    
                    <div class="about-stats">
                        <div class="stat-item">
                            <div class="stat-value">5+</div>
                            <div class="stat-label">Years Experience</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">500+</div>
                            <div class="stat-label">Happy Clients</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">24/7</div>
                            <div class="stat-label">Support</div>
                        </div>
                    </div>
                    
                    <div class="expert-qualifications">
                        <h4>Certifications</h4>
                        <ul>
                            <li>Certified Financial Planner (CFP)</li>
                            <li>Licensed Insurance Advisor</li>
                            <li>Investment Advisory Certification</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <h2>Why Choose Us</h2>
                <p>Discover the Mint Insure difference with our customer-focused approach.</p>
            </div>
            
            <div class="features-grid">
                <div class="feature-item" data-aos="fade-up" data-aos-delay="100">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3>Comprehensive Coverage</h3>
                    <p>Our policies are designed to provide complete protection for all aspects of your life and business.</p>
                </div>
                
                <div class="feature-item" data-aos="fade-up" data-aos-delay="200">
                    <div class="feature-icon">
                        <i class="fas fa-hand-holding-usd"></i>
                    </div>
                    <h3>Competitive Rates</h3>
                    <p>We work with top insurers to bring you the best coverage at the most affordable prices.</p>
                </div>
                
                <div class="feature-item" data-aos="fade-up" data-aos-delay="300">
                    <div class="feature-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3>24/7 Support</h3>
                    <p>Our dedicated team is available round the clock to assist you with any questions or claims.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Insurance Categories Sections -->
    <!-- Life/Term Insurance Section -->
    <section id="life-term" class="insurance-category">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <h2>Life & Term Insurance</h2>
                <p>Secure your family's financial future with our comprehensive life insurance options.</p>
            </div>
            <div class="category-content" data-aos="fade-up">
                <p>Our life and term insurance plans provide financial protection for your loved ones in case of unfortunate events. Get personalized coverage that fits your budget and needs.</p>
                <a href="#" class="btn btn-primary appointment-trigger">Get a Quote</a>
            </div>
        </div>
    </section>

    <!-- Health Insurance Section -->
    <section id="health" class="insurance-category">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <h2>Health Insurance</h2>
                <p>Protect yourself and your family with comprehensive health coverage.</p>
            </div>
            <div class="category-content" data-aos="fade-up">
                <p>Our health insurance plans cover medical expenses, hospitalization, critical illness, and more. Find the right plan to safeguard your health and financial wellbeing.</p>
                <a href="#" class="btn btn-primary appointment-trigger">Get a Quote</a>
            </div>
        </div>
    </section>

    <!-- General Insurance Section -->
    <section id="general" class="insurance-category">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <h2>General Insurance</h2>
                <p>Protect your assets and belongings with our general insurance solutions.</p>
            </div>
            <div class="category-content" data-aos="fade-up">
                <p>From home and auto insurance to travel and liability coverage, our general insurance options provide protection against various risks and unforeseen events.</p>
                <a href="#" class="btn btn-primary appointment-trigger">Get a Quote</a>
            </div>
        </div>
    </section>

    <!-- Funds Section -->
    <section id="child-education-fund" class="insurance-category">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <h2>Child Education Fund</h2>
                <p>Secure your child's academic future with our specialized education funds.</p>
            </div>
            <div class="category-content" data-aos="fade-up">
                <p>Our child education funds are designed to help you save and invest systematically for your child's higher education, ensuring they have the resources they need when the time comes.</p>
                <a href="#" class="btn btn-primary appointment-trigger">Learn More</a>
            </div>
        </div>
    </section>

    <!-- Investment Sections -->
    <section id="general-investment" class="insurance-category">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <h2>General Investment</h2>
                <p>Grow your wealth with our diverse investment opportunities.</p>
            </div>
            <div class="category-content" data-aos="fade-up">
                <p>Our general investment options provide opportunities for capital appreciation and income generation through various asset classes, tailored to your risk appetite and financial goals.</p>
                <a href="#" class="btn btn-primary appointment-trigger">Get Started</a>
            </div>
        </div>
    </section>

    <section id="investment-plans" class="insurance-category">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <h2>Investment Plans</h2>
                <p>Strategic investment plans designed to meet your financial goals.</p>
            </div>
            <div class="category-content" data-aos="fade-up">
                <p>Our investment plans combine security with growth potential, offering structured approaches to wealth creation and preservation for your long-term financial objectives.</p>
                <a href="#" class="btn btn-primary appointment-trigger">Explore Plans</a>
            </div>
        </div>
    </section>

    <!-- Plans Sections -->
    <section id="ulip-plans" class="insurance-category">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <h2>ULIP Plans</h2>
                <p>Unit Linked Insurance Plans offering dual benefits of insurance and investment.</p>
            </div>
            <div class="category-content" data-aos="fade-up">
                <p>Our ULIPs provide life insurance coverage while investing a portion of your premium in market-linked funds, offering potential for wealth creation along with protection.</p>
                <a href="#" class="btn btn-primary appointment-trigger">Learn More</a>
            </div>
        </div>
    </section>

    <section id="annuity-plans" class="insurance-category">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <h2>Annuity Plans</h2>
                <p>Secure regular income during your retirement years.</p>
            </div>
            <div class="category-content" data-aos="fade-up">
                <p>Our annuity plans provide regular income after retirement, ensuring financial independence and peace of mind during your golden years.</p>
                <a href="#" class="btn btn-primary appointment-trigger">Get a Quote</a>
            </div>
        </div>
    </section>

    <section id="retirement-plans" class="insurance-category">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <h2>Retirement Plans</h2>
                <p>Plan for a comfortable and secure retirement lifestyle.</p>
            </div>
            <div class="category-content" data-aos="fade-up">
                <p>Our retirement plans help you build a substantial corpus over time, ensuring you maintain your standard of living and fulfill your dreams post-retirement.</p>
                <a href="#" class="btn btn-primary appointment-trigger">Plan Now</a>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <h2>What Our Clients Say</h2>
                <p>Hear from our satisfied customers about their experiences with Mint Insure.</p>
            </div>
            
            <div class="testimonial-slider" data-aos="fade-up">
                <?php foreach ($testimonials as $testimonial): ?>
                    <div class="testimonial-item">
                        <div class="testimonial-content">
                            <p><?php echo $testimonial['content']; ?></p>
                        </div>
                        <div class="testimonial-author">
                            <div class="author-image">
                                <img src="<?php echo $testimonial['image_url']; ?>" alt="<?php echo $testimonial['name']; ?>">
                            </div>
                            <div class="author-info">
                                <h4><?php echo $testimonial['name']; ?></h4>
                                <p><?php echo $testimonial['position']; ?></p>
                                <div class="testimonial-rating">
                                    <?php for ($i = 0; $i < $testimonial['rating']; $i++): ?>
                                        <i class="fas fa-star"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta">
        <div class="container">
            <div data-aos="fade-up">
                <h2>Ready to Protect What Matters Most?</h2>
                <p>Get a free consultation today and discover how Rajesh Shah can provide the perfect coverage for your needs.</p>
                
                <form class="cta-form" method="post">
                    <input type="email" name="subscribe_email" placeholder="Enter your email" required>
                    <button type="submit" name="subscribe" class="btn btn-secondary">Get Quote</button>
                </form>
                
                <?php if (isset($subscribe_success)): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?php echo $subscribe_success; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($subscribe_error)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo $subscribe_error; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="contact" id="contact">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <h2>Contact Us</h2>
                <p>Have questions or need assistance? Rajesh is here to help.</p>
            </div>
            
            <div class="contact-grid">
                <div class="contact-info" data-aos="fade-right">
                    <h3>Get In Touch</h3>
                    
                    <div class="contact-detail">
                        <div class="contact-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="contact-text">
                            <h4>Our Office</h4>
                            <p>123 Insurance Avenue, Financial District, City 10001</p>
                        </div>
                    </div>
                    
                    <div class="contact-detail">
                        <div class="contact-icon">
                            <i class="fas fa-phone-alt"></i>
                        </div>
                        <div class="contact-text">
                            <h4>Phone</h4>
                            <p>+1 (800) 123-4567</p>
                        </div>
                    </div>
                    
                    <div class="contact-detail">
                        <div class="contact-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="contact-text">
                            <h4>Email</h4>
                            <p>rajesh@mintinsure.com</p>
                        </div>
                    </div>
                    
                    <div class="contact-detail">
                        <div class="contact-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="contact-text">
                            <h4>Working Hours</h4>
                            <p>Monday - Friday: 9:00 AM - 6:00 PM</p>
                            <p>Saturday: 10:00 AM - 2:00 PM</p>
                        </div>
                    </div>
                </div>
                
                <div class="contact-form" data-aos="fade-left">
                    <h3>Send Us a Message</h3>
                    
                    <?php if (isset($contact_success)): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <?php echo $contact_success; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($contact_error)): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo $contact_error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="post">
                        <div class="form-group">
                            <input type="text" name="name" class="form-control" placeholder="Your Name" required>
                        </div>
                        <div class="form-group">
                            <input type="email" name="email" class="form-control" placeholder="Your Email" required>
                        </div>
                        <div class="form-group">
                            <input type="tel" name="phone" class="form-control" placeholder="Your Phone">
                        </div>
                        <div class="form-group">
                            <input type="text" name="subject" class="form-control" placeholder="Subject" required>
                        </div>
                        <div class="form-group">
                            <textarea name="message" class="form-control" rows="5" placeholder="Your Message" required></textarea>
                        </div>
                        <button type="submit" name="contact_submit" class="btn btn-primary">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <h3>Mint Insure</h3>
                    <p>Providing reliable insurance solutions. Rajesh Shah is committed to protecting what matters most to you.</p>
                    <div class="footer-social">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                
                <div class="footer-col">
                    <h3>Quick Links</h3>
                    <ul class="footer-links">
                        <li><a href="#home">Home</a></li>
                        <li><a href="#about">About Us</a></li>
                        <li><a href="#services">Services</a></li>
                        <li><a href="#contact">Contact</a></li>
                        <li><a href="#" class="appointment-trigger">Get a Quote</a></li>
                    </ul>
                </div>
                
                <div class="footer-col">
                    <h3>Services</h3>
                    <ul class="footer-links">
                        <li><a href="#life-term">Life/Term Insurance</a></li>
                        <li><a href="#health">Health Insurance</a></li>
                        <li><a href="#general">General Insurance</a></li>
                        <li><a href="#ulip-plans">ULIP Plans</a></li>
                        <li><a href="#retirement-plans">Retirement Plans</a></li>
                    </ul>
                </div>
                
                <div class="footer-col">
                    <h3>Newsletter</h3>
                    <p>Subscribe to our newsletter for the latest updates and insurance tips.</p>
                    <form method="post">
                        <div class="form-group">
                            <input type="text" name="subscribe_name" class="form-control" placeholder="Your Name">
                        </div>
                        <div class="form-group">
                            <input type="email" name="subscribe_email" class="form-control" placeholder="Your Email" required>
                        </div>
                        <button type="submit" name="subscribe" class="btn btn-secondary">Subscribe</button>
                    </form>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> Mint Insure. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Appointment Modal -->
    <div id="appointmentModal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2>Grab Your Free Appointment Now</h2>
            <form id="appointmentForm" method="post">
                <div class="form-group">
                    <label for="fullName">Full Name *</label>
                    <input type="text" id="fullName" name="fullName" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone *</label>
                    <input type="tel" id="phone" name="phone" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>
                <div class="form-group checkbox-group">
                    <input type="checkbox" id="agreeTerms" name="agreeTerms" required>
                    <label for="agreeTerms">I agree to <a href="#" class="terms-link">terms & conditions</a> provided by the company. By providing my phone number, I agree to receive text messages from the business.</label>
                </div>
                <button type="submit" name="appointment_submit" class="btn btn-primary btn-block">Apply Now & Start Your Journey</button>
                <p class="text-center form-footer">Take Steps to Financial Freedom</p>
                <p class="text-center small">Before Closing this Opportunity Get Your Finance Security In One Single Click.</p>
            </form>
        </div>
    </div>

    <!-- WhatsApp Float -->
    <a href="https://wa.me/919664660420" class="whatsapp-float" target="_blank">
        <i class="fab fa-whatsapp"></i>
    </a>

    <!-- AOS Animation Library -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    
   <!-- jQuery -->
   <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Slick Slider -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js"></script>
    
    <!-- AOS Animation Initialization -->
    <script>
        AOS.init({
            duration: 1000,
            once: true
        });
    </script>
    
    <!-- Custom Scripts -->
    <script>
        $(document).ready(function() {
            // Mobile Menu Toggle
            $('.mobile-menu-btn').click(function() {
                $('.nav-links').toggleClass('active');
                $(this).toggleClass('active');
            });
            
            // Testimonial Slider
            $('.testimonial-slider').slick({
                dots: true,
                arrows: false,
                infinite: true,
                speed: 500,
                slidesToShow: 1,
                slidesToScroll: 1,
                autoplay: true,
                autoplaySpeed: 5000,
                adaptiveHeight: true
            });
            
            // Smooth Scroll for Navigation Links
            $('a[href^="#"]').not('.appointment-trigger').click(function(e) {
                e.preventDefault();
                
                var target = $(this.hash);
                if (target.length) {
                    $('html, body').animate({
                        scrollTop: target.offset().top - 100
                    }, 1000);
                }
                
                // Close mobile menu if open
                $('.nav-links').removeClass('active');
                $('.mobile-menu-btn').removeClass('active');
            });
            
            // Appointment Modal
            $('.appointment-trigger').click(function(e) {
                e.preventDefault();
                $('#appointmentModal').fadeIn();
                $('body').addClass('modal-open');
            });
            
            $('.close-modal').click(function() {
                $('#appointmentModal').fadeOut();
                $('body').removeClass('modal-open');
            });
            
            $(window).click(function(e) {
                if ($(e.target).is('#appointmentModal')) {
                    $('#appointmentModal').fadeOut();
                    $('body').removeClass('modal-open');
                }
            });
            
            // Form Validation
            $('#appointmentForm').submit(function(e) {
                var valid = true;
                
                // Basic validation
                $(this).find('[required]').each(function() {
                    if ($(this).val() === '') {
                        $(this).addClass('error');
                        valid = false;
                    } else {
                        $(this).removeClass('error');
                    }
                });
                
                if (!valid) {
                    e.preventDefault();
                    alert('Please fill in all required fields.');
                }
            });
        });
    </script>
</body>
</html>