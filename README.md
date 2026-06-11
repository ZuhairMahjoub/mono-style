E-commerce Management System 
A robust and secure backend API for an e-commerce platform, built with Laravel. This project focuses on high-performance stock management, secure authentication, and fine-grained authorization.
 Tech Stack
Framework: Laravel (PHP 8.x)

Database: MySQL

Authentication: Laravel Sanctum (Token-based API authentication)

Mail Services: SMTP Integration for automated transactional emails (Welcome/Notifications)

Security: Database Transactions, Policies (Authorization), and Request Validation

Storage: Local Disk Storage for media management

 Engineering Highlights
Concurrency Control: Implemented lockForUpdate() to prevent race conditions and ensure data integrity during inventory updates.

Transaction Management: Used DB::transaction to ensure atomic operations, preventing partial updates during order processing.

Authorization: Implemented custom Policies for Products, Orders, and Users to maintain strict Role-Based Access Control (RBAC).

Notification System: Integrated SMTP to handle automated email communications, ensuring reliable delivery of user-related transactional emails.

Code Maintenance: Followed SOLID principles and centralized validation through dedicated FormRequests.

 Key Features
Inventory Management: Intelligent stock decrementing with real-time stock validation and status-based product approvals (pending, approved, rejected).

Order System: Support for both registered users and guests with seamless transition between different user roles.

Secure User Lifecycle: automated welcome emails via SMTP upon registration and robust profile management.

Secure Deletions: Implemented soft-delete checks and cascading logic to ensure data integrity.

Image Handling: Automated image management including secure storage and dynamic URL generation via Models (Accessors).
