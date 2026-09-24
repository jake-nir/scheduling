I need you to debug my PHP Duty Scheduling System.

PROBLEM:
When I open:

(http://localhost/duty-scheduling/public/?route=personnel/index)

the application displays my custom 404 page instead of showing the actual error or loading the dashboard.

I need you to diagnose the REAL cause and fix it properly.

IMPORTANT:
Do NOT blindly modify files.
First inspect the existing project architecture and determine how routing works.

DEBUGGING TASK:

1. Inspect the entire project structure, especially:

   * public/index.php
   * routing files
   * controllers
   * DashboardController
   * dashboard/index route
   * middleware/authentication
   * configuration files
   * .htaccess
   * database/configuration files
   * views/dashboard files
   * any custom error/404 handling

2. Determine exactly how this URL is processed:

   ?route=dashboard/index

Trace the request from:

Browser
→ public/index.php
→ route parser
→ router
→ controller
→ method
→ view

3. Find out why:

   dashboard/index

is being treated as a 404.

Check for issues such as:

* incorrect route syntax
* route not registered
* controller does not exist
* controller namespace/class mismatch
* controller method does not exist
* incorrect capitalization
* incorrect file path
* incorrect include/require path
* authentication middleware redirect
* session problem
* incorrect base URL
* incorrect document root
* .htaccess rewrite problem
* route parser problem
* route whitelist problem
* controller naming convention mismatch

4. VERY IMPORTANT:
   Temporarily improve the development error handling so that PHP errors are visible instead of being hidden behind the custom 404 page.

For LOCAL DEVELOPMENT ONLY, configure appropriate PHP error reporting such as:

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

If the project already has an error handler, modify it carefully so that:

* PHP fatal errors are visible during development
* exceptions show useful debugging information
* routing errors show the requested route
* controller-not-found errors show the controller name
* method-not-found errors show the method name
* missing view errors show the expected view path

Do NOT expose these debugging details in production.

5. If the custom 404 handler is incorrectly catching PHP/controller errors, fix the error-handling flow so that:

404 = route genuinely does not exist

500 = application/PHP/server error

Authentication failure = appropriate authentication response

Do not incorrectly convert application errors into 404 responses.

6. Add temporary/debug logging if necessary.

For example, log or display information similar to:

REQUEST ROUTE:
dashboard/index

PARSED CONTROLLER:
DashboardController

PARSED METHOD:
index

CONTROLLER FILE:
[path]

CONTROLLER EXISTS:
YES/NO

METHOD EXISTS:
YES/NO

VIEW:
[path]

Then identify exactly where the request fails.

7. Test the dashboard route after fixing it.

Test:

http://localhost/duty-scheduling/public/?route=dashboard/index

Also test:

http://localhost/duty-scheduling/public/

and any existing dashboard route definitions.

8. Check whether the DashboardController is actually named something different, such as:

DashboardController.php

Dashboard.php

dashboardController.php

or whether the router expects another naming convention.

Do not rename files unless necessary. If you change naming, update all dependent references consistently.

9. Check the case sensitivity and path construction even though Windows is less strict about filename case. Make the code consistent and portable.

10. Check PHP syntax across the relevant files.

Use PHP linting where appropriate, for example:

php -l <filename>

Do this before concluding that the routing is the only problem.

11. DO NOT destroy or rewrite the existing architecture.

Preserve:

* existing UI
* existing database structure
* existing authentication
* existing scheduling functionality
* existing routes
* existing CSS/Bootstrap/JavaScript
* existing business logic

Only make changes required to fix the problem.

12. After identifying the problem, explain:

ROOT CAUSE:
[exact reason]

FILE(S) RESPONSIBLE:
[list]

FIX APPLIED:
[list]

WHY THE 404 PAGE APPEARED:
[explanation]

HOW THE REQUEST NOW FLOWS:
Browser
→ index.php
→ router
→ controller
→ method
→ view

13. Finally, retest the route and confirm that the dashboard works.

If another error appears after fixing the 404, DO NOT hide it behind another 404 page. Continue debugging the next actual error.

IMPORTANT DEBUGGING RULE:
Never assume the 404 page means the route itself is missing. Trace the request through the application first.

Start by inspecting the project. Do not ask me to manually identify files that you can inspect yourself.
