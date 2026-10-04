# IT Helpdesk Ticketing System

A web-based IT Helpdesk Ticketing System designed to manage and track technical support requests between employees and IT support agents.

The application provides a structured workflow for reporting IT issues, assigning tickets to support personnel, communicating with users, tracking ticket progress, and closing resolved incidents.

## Features

### Employee
- Secure account registration and login
- Create IT support tickets
- Categorize issues and assign priority levels
- View submitted tickets and their current status
- Communicate with support agents through ticket conversations
- Track tickets throughout their lifecycle
- View ticket statistics from the employee dashboard

### Support Agent
- Dedicated support dashboard
- View all submitted support tickets
- Filter tickets by status
- Assign tickets to themselves
- View personally assigned tickets
- Update ticket status
- Respond to employees through ticket conversations
- Resolve and close tickets
- View ticket statistics and recent support requests

## Ticket Workflow

Tickets progress through the following lifecycle:

`Open → In Progress → Resolved → Closed`

Once a ticket is closed, further replies are prevented to preserve the completed support record.

## Security Features

The system implements several security controls, including:

- Password hashing using PHP `password_hash()`
- Secure password verification using `password_verify()`
- Prepared SQL statements for user-controlled database queries
- Session-based authentication
- Session ID regeneration after successful login
- Role-based access control
- Employee ticket ownership validation
- Output escaping using `htmlspecialchars()`
- Server-side validation of ticket status changes
- Restricted replies on closed tickets
- Database credentials excluded from version control

## Technologies Used

- PHP
- MySQL
- HTML5
- CSS3
- Bootstrap 5
- JavaScript
- XAMPP
- phpMyAdmin
- Git & GitHub

## Project Structure

```text
Helpdesk/
├── Agent/                  # Support agent functionality
├── Assets/
│   └── CSS/                # Application styling
├── Auth/                   # Login, registration and logout
├── Config/                 # Database configuration
├── Tickets/                # Employee ticket functionality
├── database/
│   └── helpdesk_schema.sql # Database structure
├── Dashboard.php           # Employee dashboard
├── Index.php               # Application entry point
└── README.md
```

## Database Setup

1. Start Apache and MySQL using XAMPP.
2. Open phpMyAdmin.
3. Create a database named `helpdesk_db`.
4. Import `database/helpdesk_schema.sql`.
5. Copy `Config/Database.example.php` to `Config/Database.php`.
6. Update the database connection values in `Config/Database.php` for your environment.

## Running the Application Locally

Place the project folder inside the XAMPP `htdocs` directory.

For example:

```text
C:\xampp\htdocs\Helpdesk
```

Start Apache and MySQL, then access the application through your local web server.

## Purpose

This project was developed to demonstrate practical skills in IT support workflow design, PHP web development, relational database management, authentication, access control, troubleshooting processes, and secure application development practices.

## Future Improvements

Potential future enhancements include:

- File attachments for support tickets
- Email notifications
- Service Level Agreement (SLA) tracking
- Knowledge base integration
- Reporting and analytics
- Administrative user management
- CSRF protection
- Audit logging

## Author

Caroline Gichango