 Monostyle E-Commerce Backend API
A secure, high-performance, and concurrency-safe backend e-commerce system built with Laravel. It features robust transactional order management, a multi-tier product moderation workflow, dynamic image storage, asynchronous email notifications (WelcomeMail), flexible polymorphic authentication (supporting registered users and guests), and fine-grained authorization policies.

 Key Technical Features
Concurrency & Race Condition Mitigation:

Uses database transactions (DB::transaction) combined with pessimistic locking (lockForUpdate()) on inventory rows during checkout, updates, and cancellations to completely prevent overselling.

Historical Data Integrity:

Caches product names and prices directly inside the order_items table at the exact time of purchase, preventing historical discrepancies if original products are modified or soft-deleted later.

Product Moderation Workflow:

Newly submitted products default to a pending status and remain hidden from public consumers until explicitly reviewed and approved (approved) or rejected (rejected) by an administrator.

Polymorphic / Guest Checkout Support:

Dynamically handles orders for both authenticated Laravel Sanctum users and guest shoppers (collecting customer name and email via conditional validation rules).

Advanced Soft Deletes & Auditing:

Traverses deep relationships using withTrashed() to generate comprehensive audit reports tracking active vs. archived statuses across orders, items, and products.

Strict RBAC & Policy Authorization:

Enforces role-based access control using custom middlewares (RoleMiddleware, IsAdmin) alongside model policies (OrderPolicy, ProductPolicy, UserPolicy).

Automated Notifications:

Dispatches asynchronous welcome emails (WelcomeMail) upon user registration.

 Architecture & Core Components Breakdown
1. Controllers
OrderController: Manages atomic checkouts, stock locks, updates, soft-deletes, restorations, and deep-audit listing (getAllOrdersInDifferentSituations).

ProductController: Handles multi-attribute creation, image uploads with asset URL mapping, many-to-many category syncing (extra_categories), public filtering, and admin moderation endpoints.

UserController: Manages registration (with secure Bcrypt hashing and dispatching WelcomeMail), token-based authentication (Login/Logout), user profile modifications, and hierarchical category traversal.

2. Middlewares & Policies
RoleMiddleware / IsAdmin: Secures administrative routes by validating user roles against incoming requests.

OrderPolicy / ProductPolicy / UserPolicy: Implements resource-level authorization ensuring users can only view, update, or delete their own data unless acting as an administrator.

3. Form Requests & Mailables
OrderStoreRequest / OrderUpdateRequest: Validates multi-item structures with conditional guest rules.

ProductStoreRequest / ProductUpdateRequest: Validates pricing, stock, images, and extra categories.

UserStoreRequest / UserUpdateRequest: Enforces strict password complexity rules (RulesPassword) and email uniqueness checks.

WelcomeMail: Mailable class handling the welcome email template (emails.welcomeUser) sent upon successful user registration.
