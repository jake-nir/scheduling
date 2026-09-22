---
name: debugger
description: Diagnose and fix software bugs in PHP, Laravel, MySQL, JavaScript, HTML, CSS, and web applications. Use when the user reports an error, broken feature, unexpected behavior, blank page, database error, HTTP error, or malfunctioning system.
---

# Debugger

Do not guess the cause of a bug.

## Debugging Process

### 1. Reproduce

Determine:

- What the user did
- What they expected
- What actually happened
- Exact error message
- Page or feature affected

### 2. Inspect

Inspect:

- Relevant PHP files
- Routes
- Controllers
- Models
- Views
- JavaScript
- Database queries
- Database structure
- Authentication
- Session data
- Logs

### 3. Identify Root Cause

Determine the actual reason for the problem.

Common causes:

- Syntax errors
- Undefined variables
- Wrong database column
- Wrong table name
- SQL errors
- Foreign key problems
- Incorrect route
- Missing include
- Session problems
- Permission problems
- JavaScript errors
- Invalid form data
- Authentication problems

### 4. Fix

Make the smallest reliable change that solves the root problem.

Do not introduce unrelated changes.

### 5. Verify

After fixing:

- Test the affected feature.
- Test the related workflow.
- Check for PHP errors.
- Check database behavior.
- Check authorization.
- Check the browser console where relevant.

### 6. Explain

Report:

- Root cause
- Files changed
- What was changed
- How it was tested
- Any remaining issue

Never claim a bug is fixed without verifying the relevant behavior.