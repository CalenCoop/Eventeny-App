# QuickTix-Project

## Description

This is a full-stack event management system built for a product engineering interview. Event organizers can create, view, edit, and delete tickets with full CRUD capabilities, while ticket buyers can browse public events, add tickets to a cart, and complete mock checkouts - all with dynamic AJAX updates and zero page reloads.

## Features

### Event Organizers

- **Dashboard**: Create, view, edit, and delete tickets (soft delete)
- **Statistics**: View total, active, and expired event counts
- **Pagination & Sorting**: Efficient browsing of large ticket lists
- **Collapsible Form**: Clean UI with toggle functionality for ticket creation
- **Timezone Handling**: Proper UTC conversion for consistent dates across timezones

### Ticket Buyers

- **Public Browsing**: Filter and browse available public tickets
- **Shopping Cart Modal**: Add, view, update, and remove items via AJAX
- **Review Section**: Preview cart items before checkout
- **Mock Checkout**: Complete the purchase flow
- **Real-time Updates**: Cart count and content updates without page reloads

## Technologies Used

- **PHP** (Backend with PDO for secure database operations)
- **MySQL** (Database with indexing)
- **jQuery** (AJAX calls and dynamic UI updates)
- **Bootstrap 5** (Responsive, professional styling)
- **Sessions** (Server-side cart management)

## Key Technical Concepts

- **AJAX/JSON**: Dynamic updates without page reloads
- **Prepared Statements**: PDO prevents SQL injection
- **Session Management**: Server-side cart persistence
- **Event Delegation**: Handling dynamically loaded elements
- **Soft Delete Pattern**: Maintaining data integrity
- **UTC Timestamps**: Consistent timezone handling

## Lessons Learned

- Handling AJAX cart operations with jQuery and session management
- Designing a modal checkout flow that updates dynamically without navigation
- Balancing code quality with interview timeline constraints
- Using Bootstrap for rapid, professional UI development
- Implementing UTC conversion for consistent date storage across timezones

## Future Improvements

- Set up a User table & Auth
  - opens up many other features (public/private routes, favorite events, etc.)
    -ticket verification system
    -Move Cart to User db
- Better Error handling
- Create seperate page for each event
  - To show more detail about events
- Make UI/UX Mobile friendly
- Account for location
- Move Cart to DB
- Improve ticket creation logic
- Decide what to do with experied tickets/events
-

## Bugs

- Modal doesn't work in some browsers (Brave)
- Ticket creation system allows you to make events in the past

## Installation

**Prerequisites:** XAMPP (Apache + MySQL)

**1. Setup Database**

Start XAMPP services (Apache and MySQL).

Create the database and table:

```sql
CREATE DATABASE [yourDBName];

-- Or run setup.php in your browser after uploading
```

**2. Configure Database**

Update `database/database.php` with your credentials:

```php
private $servername = "localhost";
private $username = "root";
private $password = "";
private $dbname = "[yourDBName]";
```

**3. Run Setup (Optional)**

Visit `http://localhost/[yourDBName]/setup.php` to create the database schema.

**4. Access the Application**

- **Dashboard**: `http://localhost/[yourDBName]/dashboard.php`
- **Public View**: `http://localhost/[yourDBName]/index.php`
