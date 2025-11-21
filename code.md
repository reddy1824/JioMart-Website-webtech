# JioMart E-commerce Website

## Overview
A fully functional e-commerce website inspired by JioMart, built with HTML, CSS, and PHP. This website allows users to browse products, add items to their shopping cart, checkout, enter payment details, and receive order confirmation.

## Current State
The website is fully operational with all requested features implemented:
- Product catalog with search and category filtering
- Shopping cart functionality
- Checkout process with delivery information
- Payment details page
- Order confirmation page

## Features

### Product Catalog (index.php)
- Grid layout displaying 10 products across different categories
- Search functionality to find products by name
- Category filtering (Vegetables, Fruits, Snacks, Breakfast, Dairy)
- Add to cart functionality
- Real product images
- Responsive design

### Shopping Cart (cart.php)
- View all items in cart
- Update item quantities
- Remove items from cart
- Calculate subtotal and delivery charges
- Free delivery for orders above ₹500
- Proceed to checkout

### Checkout (checkout.php)
- User information form (name, email, phone)
- Delivery address collection
- Order summary sidebar
- Form validation

### Payment (payment.php)
- Multiple payment methods:
  - Credit/Debit Card
  - UPI
  - Cash on Delivery
- Card details form with validation
- JavaScript-powered dynamic form fields
- Review delivery address
- Order summary

### Order Confirmation (confirmation.php)
- Order confirmation with unique order ID
- Complete order summary
- Delivery information
- Print order functionality
- Continue shopping option

## Technology Stack

### Frontend
- HTML5
- CSS3 with responsive grid layout
- Vanilla JavaScript for interactivity
- JioMart-inspired color scheme (blue #0066cc and orange #ff6b00)

### Backend
- PHP 8.2
- Session-based cart management
- Form processing and validation

### Images
- Stock images for product catalog
- Responsive image handling

## Project Structure
```
/
├── index.php           # Main product catalog page
├── cart.php            # Shopping cart page
├── checkout.php        # Checkout and delivery information
├── payment.php         # Payment details
├── confirmation.php    # Order confirmation
├── css/
│   └── style.css       # Main stylesheet
├── js/
│   └── payment.js      # Payment form JavaScript
├── images/             # Product images
└── replit.md           # This file
```

## Recent Changes (Nov 20, 2025)
- Initial project setup
- Installed PHP 8.2
- Created all pages (index, cart, checkout, payment, confirmation)
- Implemented responsive CSS styling
- Added JavaScript for payment form validation
- Downloaded and integrated product images
- Configured PHP workflow to run on port 5000

## User Preferences
None specified yet.

## Running the Project
The project runs on PHP's built-in web server on port 5000. The workflow "JioMart Website" is configured to start automatically.

Command: `php -S 0.0.0.0:5000`

## Future Enhancements
- User account system with order history
- Product reviews and ratings
- Wishlist functionality
- Admin panel for product management
- Email notifications for order confirmations
- Integration with actual payment gateway (Stripe/Razorpay)
- Database integration for persistent data storage
- Mobile app version
